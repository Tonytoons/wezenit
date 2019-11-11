<?php
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("Asia/Bangkok"); 
mb_internal_encoding("UTF-8");

function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}
/**
 * get access token from header
 * */
function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

 
//$_POST = json_decode(file_get_contents("php://input"),true);
$from_name = $_POST['from_name'];
$from_email = $_POST['from_email']; 
$message = $_POST['message'];
$phone_number = !empty($_POST['phone_number'])?$_POST['phone_number']:'-';
$subject = !empty($_POST['subject'])?'GPSN Contact Us : '.$_POST['subject']:'GPSN Contact Us';
$to_email = !empty($_POST['to_email'])?$_POST['to_email']:'boy@gpsn.co.th';

function sendMail($from_name, $from_email, $message, $subject, $to_email, $phone_number){
   
    $url = 'https://api.sendgrid.com/';
    $user = 'boygpsn'; 
    $pass = '123qwe123';   
       
    if(!empty($from_name) && !empty($from_email) && !empty($message)){
       
       $json_string = array(  
          'to' => array(  
            'sylvain@gpsn.co.th',             
            'tony@gpsn.co.th',
            'boy@gpsn.co.th', 
            'contact@gpsn.co.th',
          )
        );  
        
       //print_r(json_encode($params)); exit;
        $params = array(
            'api_user'  => $user,
            'api_key'   => $pass,
            'x-smtpapi' => json_encode($json_string),
            'to'        => $to_email,
            'toname'    => 'GPSN Contact Us',
            'subject'   => $subject,
            'html'      => $message."<br><br>".$from_name."<br> tel:".$phone_number,
            'text'      => $message."<br><br>".$from_name."<br> tel:".$phone_number, 
            'from'      => $from_email, 
        );
                
        $request =  $url.'api/mail.send.json';
        
        // Generate curl request
        $session = curl_init($request);
        // Tell curl to use HTTP POST
        curl_setopt ($session, CURLOPT_POST, true);
        // Tell curl that this is the body of the POST
        curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
        // Tell curl not to return headers, but do return the response
        curl_setopt($session, CURLOPT_HEADER, false);
        // Tell PHP not to use SSLv3 (instead opting for TLS)
        curl_setopt($session, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        
        // obtain response
        $response = curl_exec($session);
        curl_close($session);
        //$rs = json_decode($response);
        // print everything out
        //print_r($response);  
        return $response;
    }
}

  
$send = sendMail($from_name, $from_email, $message, $subject, $to_email, $phone_number);
print_r($send);
exit; 
?>