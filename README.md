# # PHP-New-PayTR-Class

**PHP-New-PayTR-Class**, PayTR ödeme sistemini PHP ile entegre etmek için kullanılan bir PHP sınıfıdır. Bu sınıf, PayTR API'sini kullanarak ödeme işlemlerini yönetmenize olanak tanır.


## Özellikler

-   PayTR ödeme sistemine kolay entegrasyon sağlar.
-   Ödeme işlemlerini güvenli bir şekilde yönetmenizi sağlar.
## Gereksinimler

-   PHP 5.4 veya üzeri sürüm

## Kurulum

1.  Bu kütüphaneyi indirin veya kopyalayın:
    
    bashCopy code
    
    `git clone https://github.com/MehmetAkcil/PHP-New-PayTR-Class.git` 
    
2.  Projenize dahil edin:
    
    phpCopy code
    
    `require_once('/path/to/PHP-New-PayTR-Class/PayTR.php');` 
    

## Kullanım

    // PayTR sınıfını dahil edin
    require_once('/path/to/PHP-New-PayTR-Class/PayTR.php');
    
    // PayTR ayarlarını yapın
    $merchant_key = 'MERCHANT_KEY';
    $merchant_salt = 'MERCHANT_SALT';
    
    // PayTR nesnesini oluşturun
    $paytr = new PayTR($merchant_key, $merchant_salt);
    
    // Ödeme talebini oluşturun
    $payment_request = array(
        'merchant_oid' => 'ORDER_ID',
        'email' => 'customer@example.com',
        'total_amount' => 100.00,
        // Diğer ödeme parametrelerini ekleyin
    );
    
    // Ödeme talebini PayTR'ye gönderin
    $payment_response = $paytr->createPayment($payment_request);
    
    // Ödeme işlemi başarılıysa devam edin
    if ($payment_response['status'] == 'success') {
        // Ödeme onaylandı
        // Müşteriye bilgilendirme yapabilir veya siparişi onaylayabilirsiniz
    } else {
        // Ödeme başarısız veya iptal edildi
        // İlgili işlemleri gerçekleştirebilirsiniz
    }



## Lisans

[MIT Lisansı](https://opensource.org/licenses/MIT) altında lisanslanmıştır.

