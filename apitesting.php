<?php

header('Content-type: application/xml');


$authdigest = base64_encode('marceloziliotto@hotmail.com:123456');

$headers = array(
    'HTTP_AUTHORIZATION:basic '.$authdigest
);
 
$url = "http://pichler.dev-iis.com/getProgrammedNotification";
$url .= '?' . 'userPhoneNumber=4899182829';
$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $url);
curl_setopt($handle, CURLOPT_URL, $url);
curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($handle);
?>