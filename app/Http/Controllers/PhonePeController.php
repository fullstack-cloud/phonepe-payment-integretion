<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;

class PhonePeController extends Controller
{
    
    public function payment_init()
    {
      // Replace these with your actual PhonePe API credentials
      $merchantId = 'PGTESTPAYUAT'; // sandbox or test merchantId
      $apiKey="099eb0cd-02cf-4e2a-8aca-3e6c6aff0399"; // sandbox or test APIKEY
      $redirectUrl = route('verify-payment');
      
      // Set transaction details
      $order_id = uniqid(); 
      $name="Tutorials Website";
      $email="info@tutorialswebsite.com";
      $mobile=9999999999;
      $amount = 10; // amount in INR
      $description = 'Payment for Product/Service';

      $paymentData = array(
        'merchantId' => $merchantId,
        'merchantTransactionId' => "MT7850590068188104", // test transactionID
        "merchantUserId"=>"MUID123",
        'amount' => $amount*100,
        'redirectUrl'=>$redirectUrl,
        'redirectMode'=>"POST",
        'callbackUrl'=>$redirectUrl,
        "merchantOrderId"=>$order_id,
        "mobileNumber"=>$mobile,
        "message"=>$description,
        "email"=>$email,
        "shortName"=>$name,
        "paymentInstrument"=> array(    
          "type"=> "PAY_PAGE",
        )
      );
      $jsonencode = json_encode($paymentData);
      $payloadMain = base64_encode($jsonencode);
      $salt_index = 1; //key index 1
      $payload = $payloadMain . "/pg/v1/pay" . $apiKey;
      $sha256 = hash("sha256", $payload);
      $final_x_header = $sha256 . '###' . $salt_index;
      $request = json_encode(array('request'=>$payloadMain));
      $response = Curl::to('https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay')
                ->withHeader('Content-Type: application/json')
                ->withHeader('X-VERIFY:'.$final_x_header)
                ->withHeader('accept: application/json')
                ->withData($request)
                ->post();

      $rData = json_decode($response);
      return redirect()->to($rData->data->instrumentResponse->redirectInfo->url);
    }

    public function verify(Request $request)
    {
        $input = $request->all();

        $saltKey = '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399';
        $saltIndex = 1;

        $finalXHeader = hash('sha256','/pg/v1/status/'.$input['merchantId'].'/'.$input['transactionId'].$saltKey).'###'.$saltIndex;

        $response = Curl::to('https://api-preprod.phonepe.com/apis/merchant-simulator/pg/v1/status/'.$input['merchantId'].'/'.$input['transactionId'])
                ->withHeader('Content-Type:application/json')
                ->withHeader('accept:application/json')
                ->withHeader('X-VERIFY:'.$finalXHeader)
                ->withHeader('X-MERCHANT-ID:'.$input['transactionId'])
                ->get();

        dd(json_decode($response));
    }
}
