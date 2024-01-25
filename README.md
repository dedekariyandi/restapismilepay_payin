##                            membuat payment gateway smilepay dengan laravel 10 (Payin)

##  Jika error seperti ini  Blacklisted. Ip silahkan masukkan IP anda ke setting api di smilepay 

      /GuzzleHttp\Exception\ClientException: Client error: `POST https://sandbox-gateway.smilepay.id/v1.0/transaction/pay-in` resulted in a `403 Forbidden` response:
       {&quot;responseCode&quot;:&quot;4039019&quot;,&quot;responseMessage&quot;:&quot;Merchant Blacklisted. Ip not allow. MerchantID: sandbox-123456. Current ip:  (truncated...)
      in file 

## jika benar maka hasil nya akan seprti ini
```
{
    "tradeNo": "T101sandbox-10039240126042833650",
    "orderNo": "T_1706218110",
    "status": "PROCESSING",
    "merchant": {
        "merchantId": "sandbox-10039",
        "merchantName": "Nama Va nanti",
        "accountNo": "656756757657657657"
    },
    "transactionTime": "2024-01-26T04:28:33+07:00",
    "money": {
        "currency": "IDR",
        "amount": 10000
    },
    "channel": {
        "paymentMethod": "BCA",
        "vaNumber": "316313380858920",
        "qrString": "To be completed",
        "paymentUrl": "https://sandbox-gateway.smilepay.id/cashier/#/loading?tradeNo=T101sandbox-10039240126042833650",
        "additionalInfo": null
    },
    "responseCode": "2009000",
    "responseMessage": "Successful"
}
```
## Selanjut nya akan saya buat untuk bagian payouts di smilepay
