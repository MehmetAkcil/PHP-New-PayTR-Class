<?php


class PayTR
{

    public string $merchant_id;
    public string $merchant_key;
    public string $merchant_salt;
    public string $merchant_ok_url;
    public string $merchant_fail_url;

    public function __construct($merchant_id, $merchant_key, $merchant_salt, $merchant_ok_url, $merchant_fail_url)
    {
        $this->merchant_id = $merchant_id;
        $this->merchant_key = $merchant_key;
        $this->merchant_salt = $merchant_salt;

        $this->merchant_ok_url = $merchant_ok_url;
        $this->merchant_fail_url = $merchant_fail_url;
    }

    public function create($email, $payment_amount, $merchant_oid, $user_name, $user_address, $user_phone, $user_basket)
    {
        $merchant_id = $this->merchant_id;
        $merchant_key = $this->merchant_key;
        $merchant_salt = $this->merchant_salt;
        $merchant_ok_url = $this->merchant_ok_url;
        $merchant_fail_url = $this->merchant_fail_url;
        $user_basket = base64_encode(json_encode($user_basket));

        ## Kullanıcının IP adresi
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }

        $user_ip = $ip;
        $timeout_limit = "30";
        $debug_on = 0;
        $test_mode = 0;
        $no_installment = 0;
        $max_installment = 0;

        $currency = "TL";
        $hash_str = $merchant_id . $user_ip . $merchant_oid . $email . $payment_amount . $user_basket . $no_installment . $max_installment . $currency . $test_mode;
        $paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));
        $post_vals = array(
            'merchant_id' => $merchant_id,
            'user_ip' => $user_ip,
            'merchant_oid' => $merchant_oid,
            'email' => $email,
            'payment_amount' => $payment_amount,
            'paytr_token' => $paytr_token,
            'user_basket' => $user_basket,
            'debug_on' => $debug_on,
            'no_installment' => $no_installment,
            'max_installment' => $max_installment,
            'user_name' => $user_name,
            'user_address' => $user_address,
            'user_phone' => $user_phone,
            'merchant_ok_url' => $merchant_ok_url,
            'merchant_fail_url' => $merchant_fail_url,
            'timeout_limit' => $timeout_limit,
            'currency' => $currency,
            'test_mode' => $test_mode
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $result = @curl_exec($ch);

        if (curl_errno($ch))
            die("PAYTR IFRAME connection error. err:" . curl_error($ch));

        curl_close($ch);

        $result = json_decode($result, 1);

        if ($result['status'] == 'success')
            return 'https://www.paytr.com/odeme/guvenli/' . $result['token'];
        else
            return false;
    }

    public function callback($success_callback, $error_callback)
    {
        $post = $_POST;
        $merchant_key = $this->merchant_key;
        $merchant_salt = $this->merchant_salt;


        $hash = base64_encode(hash_hmac('sha256', $post['merchant_oid'] . $merchant_salt . $post['status'] . $post['total_amount'], $merchant_key, true));

        if ($hash != $post['hash'])
            return false;

        if ($post['status'] == 'success') {
            $success_callback($post);

        } else {
            $error_callback($post);
        }

        echo "OK";
        exit;
    }

    public function merchant_status($merchant_oid)
    {
        $merchant_id = $this->merchant_id;
        $merchant_key = $this->merchant_key;
        $merchant_salt = $this->merchant_salt;

        $paytr_token = base64_encode(hash_hmac('sha256', $merchant_id . $merchant_oid . $merchant_salt, $merchant_key, true));

        $post_vals = array('merchant_id' => $merchant_id,
            'merchant_oid' => $merchant_oid,
            'paytr_token' => $paytr_token);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/durum-sorgu");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 90);

        $result = @curl_exec($ch);

        if (curl_errno($ch)) {
            $res = curl_error($ch);
            curl_close($ch);
            return $res;
        }
        curl_close($ch);

        $result = json_decode($result, 1);

        if ($result[status] != 'success') {
            return $result[err_no] . " - " . $result[err_msg];
        }

        $res = [
            $result[payment_amount] . " " . $result[currency],
            $result[payment_total] . " " . $result[currency]
        ];

        foreach ($result[returns] as $return_success)
            $res['refund'][] = $return_success;

        return $res;


    }

    public function refund($merchant_oid, $return_amount, $reference_no = false)
    {
        $merchant_id 	= $this->merchant_id;
        $merchant_key 	= $this->merchant_key;
        $merchant_salt	= $this->merchant_salt;

        $paytr_token=base64_encode(hash_hmac('sha256',$merchant_id.$merchant_oid.$return_amount.$merchant_salt,$merchant_key,true));

        $post_vals = [
            'merchant_id'=>$merchant_id,
            'merchant_oid'=>$merchant_oid,
            'return_amount'=>$return_amount,
            'paytr_token'=>$paytr_token
        ];
        if($reference_no !== false){
            $post_vals['reference_no'] = $reference_no;
        }


        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/iade");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1) ;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 90);
        $result = @curl_exec($ch);

        if(curl_errno($ch))
        {
            $err = curl_error($ch);
            curl_close($ch);
            return $err;
        }

        curl_close($ch);

        $result=json_decode($result,1);

        if($result[status]=='success')
        {
            return $result;
        }
        else
        {
            return $result[err_no]." - ".$result[err_msg];
        }
    }

}