<?php

include("../drop-files/lib/common.php");
include ("../drop-files/config/redis.php");
include ("../drop-files/lang/rider/langs.php");

if(isset($_GET['hub_mode']) && $_GET['hub_mode'] == "subscribe"){
	//webhook url verification
	echo $_GET['hub_challenge'];
	http_response_code(200);
	exit;
}

$data = file_get_contents("php://input");


http_response_code(200);


//file_put_contents("wts_res.txt", $data);

$data_arr = json_decode($data, true);

if(json_last_error())exit;



@$from = $data_arr['entry'][0]['changes'][0]['value']['messages'][0]['from'];
@$message = $data_arr['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];
$response_message = "";


if(empty($from) || empty($message))exit;



if(!preg_match("/(##[a-zA-z0-9]+##)/", $message, $matches)){
	$response_message = __("Hi, We are unable to verify your phone number. Authentication data is invalid",[],"r|{$lang}");
	sendWhatsappMsg($from, $response_message);
	exit;
};

$auth_code = str_replace("#","",$matches[0]);

$lang = substr($auth_code,-2);
$lang_found = false;
foreach($available_langs as $lang_obj){
	if($lang_obj['code'] == $lang){
		$lang_found = true;
	}
}

if(!$lang_found)$lang = "en";

$auth_code = substr($auth_code,0,strlen($auth_code) - 2);

$redis = connectRedis();

if(!$redis)exit;


$auth_data = $redis->get("wtsauthkey:{$auth_code}");

if(empty($auth_data)){
	$response_message = __("Hi, We are unable to verify your phone number. Authentication data is invalid",[],"r|{$lang}");
	sendWhatsappMsg($from, $response_message);
	exit;
}

$auth_data_arr = unserialize($auth_data);

if(substr($from,-1,5) != substr($auth_data_arr['valid_phone_int'],-1,5)){
	$response_message = __("Hi, We are unable to verify your phone number. Phone number does not match",[],"r|{$lang}");
	sendWhatsappMsg($from, $response_message);
	exit;
}

//if(strstr($auth_data_arr['valid_phone_int'],$from) === false)exit;

$auth_data_arr['validated'] = 1;

$redis->setEx("wtsauthkey:{$auth_code}", 3600 ,serialize($auth_data_arr)); //store in redis

$response_message = __("Hi, your phone number has been validated. Here is your OTP code {---1}. Thank you for using {---2}",[$auth_data_arr['user_otp_code'], WEBSITE_NAME],"r|{$lang}");

sendWhatsappMsg($from, $response_message);

exit;


function sendWhatsappMsg($to,$message){

	$post_data = [
        "messaging_product" => "whatsapp",
        "recipient_type" => "individual",
        "to" => $to,
        "type" => "text",
        "text" => [
            "preview_url" => false,
            "body" => $message
        ]
    ];

	$content = json_encode($post_data);


	$access_token = WHATSAPP_AUTH_ACCESS_TOKEN;
	$phone_number_id = WHATSAPP_AUTH_PHONENUM_ID;    

	$curl = curl_init("https://graph.facebook.com/v23.0/{$phone_number_id}/messages");
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Authorization: Bearer {$access_token}"));
	//curl_setopt($curl, CURLOPT_HTTPGET, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);


}