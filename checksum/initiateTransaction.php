<?php
/**
* import checksum generation utility
* You can get this utility from https://developer.paytm.com/docs/checksum/
*/
require_once("./lib/config_paytm.php");
require_once("./lib/encdec_paytm.php");
/* initialize an array */
$json = file_get_contents('php://input');
$json_data = json_decode($json);
if ($json == "" || empty($json_data->MID) || empty($json_data->ORDER_ID))
{
    $response = array('status' => false, 'message' => 'Invalid Method');    
    echo json_encode($response);
}else{
$paytmParams = array();

// echo $_POST["MID"];
// echo $_POST["ORDER_ID"];

/* body parameters */
$paytmParams["body"] = array(

    /* for custom checkout value is 'Payment' and for intelligent router is 'UNI_PAY' */
    "requestType" => "Payment",

    "mid" => $json_data->MID,

    /* Find your Website Name in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys */
    "websiteName" => $json_data->WEBSITE,

    /* Enter your unique order id */
    "orderId" => $json_data->ORDER_ID,

    /* on completion of transaction, we will send you the response on this URL */
    "callbackUrl" => $json_data->CALLBACK_URL,

    /* Order Transaction Amount here */
    "txnAmount" => array(

        /* Transaction Amount Value */
        "value" => $json_data->TXN_AMOUNT,

        /* Transaction Amount Currency */
        "currency" => "INR",
    ),

    /* Customer Infomation here */
    "userInfo" => array(

        /* unique id that belongs to your customer */
        "custId" => $json_data->CUST_ID,
    ),
);


/**
* Generate checksum by parameters we have in body
* Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
*/
$checksum = getChecksumFromString(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), PAYTM_MERCHANT_KEY);

/* head parameters */
$paytmParams["head"] = array(

    "signature"	=> $checksum,
);

/* prepare JSON string for request */
$post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

/* for Staging */
$url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=".$json_data->MID."&orderId=".$json_data->ORDER_ID;

/* for Production */
//$url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid=".$json_data->MID."&orderId=".$json_data->ORDER_ID;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); 
$response = curl_exec($ch);
echo $response;
}
?>