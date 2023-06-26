<?php

include 'PayTR.php';

$paytr = new PayTR(
    'merchant_id',
    'merchant)key',
    'merchant_salt',
    'https://orneksite/basarili.php',
    'https://orneksite/basarisiz.php'
);

if (isset($_GET['create'])) {
    $url = $paytr->create(
        'email',
        'fiyat',
        'order id',
        'isim soyisim',
        'adres',
        'telefon numarasi',
        [
            array("Örnek ürün 1", "18.00", 1), // 1. ürün (Ürün Ad - Birim Fiyat - Adet )
            array("Örnek ürün 2", "33.25", 2), // 2. ürün (Ürün Ad - Birim Fiyat - Adet )
            array("Örnek ürün 3", "45.42", 1)  // 3. ürün (Ürün Ad - Birim Fiyat - Adet )
        ],

    );

    header("Location: " . $url);
}

if(isset($_GET['callback']))
{
    $success = function ($post) {
        //islem basarili ise
    };

    $error = function ($post){
        // islem basarisiz ise
    };

    $paytr->callback($success, $error);
}

if(isset($_GET['merchant_status']))
{

    $response = $paytr->merchant_status('order id');

    print_r($response);

}


if(isset($_GET['refund'])){
    $response = $paytr->refund(
        'order id',
    'fiyat',
    'opsiyonel yoksa bu paremetreyi hic girme'
    );

    print_r($response);
}