<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SmilePayPayin extends Controller
{
   
    public function payIn(Request $request)
    {
       
       $token = $this->createAccessToken();
       Log::info($token);
       //get akses token dari createAccessToken scroll kebawah
        $accessToken =  $token;

        // URL
        $endPointUlr = '/v1.0/transaction/pay-in';
        $url = 'https://sandbox-gateway.smilepay.id' . $endPointUlr; //endpoint ubah ke production jika website kamu siap untuk public https://gateway.smilepay.id

        $timestamp = Carbon::now()->toIso8601String();
        $partnerId =  'sandbox-123456'; // masukkan merchant id smilepay kalian
        $merchantScreet = '473fd2ab39903377687d06cb85ab4c003625b0b6b7ebe449afcb264123456789' ; // masukkan merchant screet smilepay kalian
        // generate parameters
        $merchantOrderNo = "T_" . time();
        $purpose = "Purpose For Transaction from PHP SDK";
        $paymentMethod = "BCA"; //pilih methode pembayaran nya

        // moneyReq
        $moneyReq = [
            'currency' => 'IDR',
            'amount' => 10000 
        ];

        // merchantReq
        $merchantReq = [
            'merchantId' => $partnerId,
            // jika sudah production silahkan atur merchantName dan accountNo
            'merchantName' => "Nama Va nanti", //untuk nama pembayaran di virtual account
            'accountNo' => '12345678901122334455', //didapatkan dari akun production smilepay yaitu nomor akun/kartu
        ];

        // payerReq
        $payerReq = [
            'name' => "Jef-fer",
            'phone' => "82-3473829260",
            'address' => "Jalan Pantai Mutiara TG6, Pluit, Jakarta",
            'email' => "jef.gt@gmail.com",
        ];

        // receiverReq
        $receiverReq = [
            'name' => "Viva in",
            'phone' => "82-3473233732",
            'address' => "Jl. Pluit Karang Ayu 1 No.B1 Pluit",
            'email' => "Viva@mir.com",
        ];

        // itemDetailReq
        $itemDetailReq = [
            'name' => "mac A1",
            'quantity' => 1,
            'price' => 100000
        ];

        // billingAddress (optional)
        $billingAddress = [
            'countryCode' => "Indonesia",
            'city' => "jakarta",
            'address' => "Jl. Pluit Karang Ayu 1 No.B1 Pluit",
            'phone' => "82-3473233732",
            'postalCode' => "14450"
        ];

        // shippingAddress (optional)
        $shippingAddress = [
            'countryCode' => "Indonesia",
            'city' => "jakarta",
            'address' => "Jl. Pluit Karang Ayu 1 No.B1 Pluit",
            'phone' => "82-3473233732",
            'postalCode' => "14450"
        ];

        // payinReq
        $payinReq = [
            'orderNo' => $merchantOrderNo,
            'purpose' => $purpose,
            'productDetail' => "Product details", //optional
            'additionalParam' => "other descriptions",//optional
            'itemDetailList' => [$itemDetailReq],
            'billingAddress' => $billingAddress,
            'shippingAddress' => $shippingAddress,
            'money' => $moneyReq,
            'merchant' => $merchantReq,
            'paymentMethod' => $paymentMethod,
            'payer' => $payerReq,
            'receiver' => $receiverReq,
        ];

        $jsonString = json_encode($payinReq);
        $hash = hash('sha256', $jsonString, true);
        $hex = bin2hex($hash);
        $lowercase = strtolower($hex);
        $stringToSign = "POST" . ":" . $endPointUlr . ":" . $accessToken . ":" . $lowercase . ":" . $timestamp;
        $sha512 = hash_hmac("sha512", $stringToSign,  $merchantScreet, true);
        $signature = base64_encode($sha512);
        $client = new Client();
        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
                'X-TIMESTAMP' => $timestamp,
                'X-SIGNATURE' => $signature,
                'ORIGIN' => 'www.yourDomain.com',
                'X-PARTNER-ID' => $partnerId,
                'X-EXTERNAL-ID' => '123729342472347234236', //bisa pakai rand(10,1000) tergantung selera hehe
                'CHANNEL-ID' => '95221', //bisa pakai rand(10,1000) tergantung selera hehe
            ],
            'json' => json_decode($jsonString, true),
           
        ]);
        $responseData = json_decode($response->getBody(), true);
        Log::info( $responseData);
        return  $responseData;
        
        // Jika error seperti ini  Blacklisted. Ip silahkan masukkan IP anda ke setting api di smilepay 

        //    /GuzzleHttp\Exception\ClientException: Client error: `POST https://sandbox-gateway.smilepay.id/v1.0/transaction/pay-in` resulted in a `403 Forbidden` response:
        //    {&quot;responseCode&quot;:&quot;4039019&quot;,&quot;responseMessage&quot;:&quot;Merchant Blacklisted. Ip not allow. MerchantID: sandbox-123456. Current ip:  (truncated...)
        //     in file 

        // jika benar maka hasil nya akan seprti ini

        // "tradeNo": "T101sandbox-10039240126042833650",
        // "orderNo": "T_1706218110",
        // "status": "PROCESSING",
        // "merchant": {
        //     "merchantId": "sandbox-10039",
        //     "merchantName": "Nama Va nanti",
        //     "accountNo": "656756757657657657"
        // },
        // "transactionTime": "2024-01-26T04:28:33+07:00",
        // "money": {
        //     "currency": "IDR",
        //     "amount": 10000
        // },
        // "channel": {
        //     "paymentMethod": "BCA",
        //     "vaNumber": "316313380858920",
        //     "qrString": "To be completed",
        //     "paymentUrl": "https://sandbox-gateway.smilepay.id/cashier/#/loading?tradeNo=T101sandbox-10039240126042833650",
        //     "additionalInfo": null
        // },
        // "responseCode": "2009000",
        // "responseMessage": "Successful"
    }

    public function createAccessToken()
    {
        $endPointUlr = '/v1.0/access-token/b2b';
        $url = 'https://sandbox-gateway.smilepay.id'; //endpoint ubah ke production jika website kamu siap untuk public https://gateway.smilepay.id
        $privateKeyStr = $this->privateRsa();  //private RSA didapat dari https://www.devglan.com/online-tools/rsa-encryption-decryption kunjungi website tersebut dan buat rsa 2048bit, untuk publik rsa dimasukkan ke api setting pada akun smmilepay kalian 
        $timestamp = now()->format('Y-m-d\TH:i:sP');
        $clientKey = 'sandbox-10039'; // masukkan merchant id smilepay kalian
        $stringToSign = $clientKey . '|' . $timestamp;
        $privateKeyPem = chunk_split($privateKeyStr, 64);
        $privateKeyPem = "-----BEGIN PRIVATE KEY-----\n" . $privateKeyPem . "-----END PRIVATE KEY-----\n";
        $postData = [
            'grantType' => 'client_credentials'
        ];
        
        // Convert data to JSON format
        $jsonData = json_encode($postData);
        $client = new Client();
        $response = $client->post($url . $endPointUlr, [
            'json' => $postData,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-TIMESTAMP' => $timestamp,
                'X-CLIENT-KEY' => $clientKey,
                'X-SIGNATURE' => $this->signMessage($stringToSign, $privateKeyPem),
            ],
        ]);
        $responseData = json_decode($response->getBody(), true);
         Log::info($responseData);
        return $responseData['accessToken'];
       
    }

    private function signMessage($message, $privateKeyPem)
    {
        $r = openssl_sign($message, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);
        $base64Sign = base64_encode($signature);
        return $base64Sign;
    }

    private function privateRsa()
    {
        $privatRsa = 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCa9uPI/paQY1DN4cyCCXUkDv6H6w1LwDAfMsRkJ+gP6MjtWch9HSjrPpd7NpU2YxmudSGNAklA4/sVJqwfj+K5wgUap/dSqYcOBmBNcL+fxqtvDDQSNEOKsNqeEU54btV9Cxdc1h6hzU/KYexulD55hSX1oJXKKMb5RmjRalzccsAgPU72NYrRtoASeAe7DcEw9xnBwErkHJIm+2bOV/zm8aBCVkogHqAWohgg2qYkcAgM5+JjwKYUm0Tfsn+FU613NWez0/beiT6lsr7CmC8RwO+Gd1MuVY/bmJiDs5teysX1gY3i3xmjUsiCplehkxz8GAJnZVEh7kOMNwss9r9JAgMBAAECggEAM137i7i4eAwz0Ms0WzZ/WgCOZseHlMCUMwNFDl4cEon7cJH+X1q3IaOR2wnw1CJtdOLmyDMlzRjFNfGnxJ6RbTnfL0LWPNRtLDz41zK0kmXsldbZu98PNH4DObpK5Kj4o4Z1rBRd+wxvkop7MPx66RU1zAH7JRP3QNISFLjJJ1lqLJlsPnAeeLZvccL5oiIeFV41LUXcrLBML4NWcFvDG3pwV2tb8UkwOzN2+KqJslVbaDtzzVKObA/y+ofZONAHp5LAnSvPWJiLWtQqdu7CGGJ8YP7wg6i40EHEDqBEusQsp+6qLcMRqNfD93DK8ngwXslmDtkcw7HiVzsEKSjxwQKBgQD/Q9EpmWJR+s/aHcfOp6kkW/KWgigLNXOd98VGMjChomohf0Nu8SX8Amyb7lsPQay8DUevwsCF8xG0IW3C9w7fqGgkOL4zJmqLpXf2+u4B8obd/GoO5lROw72VewYJbPyD54ewayDUr3k+BJZLh5KbUSuGYAWvr9CP0EQJTKKSvQKBgQCbaSFsloQpsuC1CYqdar2OpmVZTYLrg1RtYBkRb30+/ptvqAVJzlPVMN8m7NwLQZ0AU0KJWfwTIzTTITj0i+WtJY1kGbIEVsigxC7W6+YHI8rRD+2q0gHEZ6CzLfWaTJSd1CWtbijmTYjJPao0ISE7rnw95ksVZI55+nvaR3+NfQKBgQDiEHVUmfpxD+a0DFu/sMwi3ytgD7TW+GeEJsIXAKwA+Y10TPizb+1r2dnF3rnWMRuBD2HFTnyiJDoxhMloONR7TvGr4nTPZ95D/i7YFDJklxzJy5lByMoxVzi3AVS/VD3ts0Z//t/8e8qsGfPgwBgeZSxevPtMNDBIrPEUK0JvIQKBgQCS+wTvjBMG1PSMg6+jXbZVWa30ncg6MYUBxKcwxD6x+17KZSBLQ2Yp9QS37b/WrYFtM1F0UbSD7QIixAL31e+sqB1nFJ42WFV7JtAd2BE/o2xH+UaQDfo55MApdkgYXGEaO/A+bDcmSSIbIcT9dG/X4BgE+u78FpRhbLMCKCjMiQKBgG7kPLbPMAaxDKjRDdWIckq/UfGXuJAdFJ9kwj1sj5prTKgA/7amX28+A3DGRT3xJamQGLePbEMHoTCpZNpqijcFWNF5NLEyD0Nrx1jqamr0pqi6dl5fs2OZAzEJ0NLSpY4Ti3b2HdaUcVbq7r8oH/wnqxi0w08K/x4BbfNuD/oK';
        return $privatRsa;
    }
}
