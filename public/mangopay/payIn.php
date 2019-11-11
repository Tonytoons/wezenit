<?php
$act = $_GET['act'];
if(empty($act)) $act = 'list';

$username = 'zenovlyprod';
//$password = 'ACi3OgRkZnKUA24kjiURFXpFx9VYbkOmMQKBtaRFbi7RDkOPbg';
//$password = 'eLctgkF5gLphvTAZDW0T0cLosJ85Rb20rHuM5diXy5ZswKs46J'; // dev key
$password = '3XUKnuaS2qX9LZkeq6jQ9FmJKUYXwCbyCkcVz28kw7ABGr28iV'; // pro key

if($act == 'payin')
{
    $ch = curl_init();
    $URL='https://api.sandbox.mangopay.com/v2.01/zenovlyapi/preauthorizations/card/direct';
    
    $data = array("Tag" => "test", "AuthorId" => "26183441", "SecureMode" => "FORCE", "CardId" => "26183443", "SecureModeReturnURL" => "https://dev.zenovly.com/", "DebitedFunds" => ["Currency" => "EUR", "Amount" => 12]);                                                                    
    $data_string = json_encode($data);
    $ch = curl_init($URL);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string))
    );
    $result = curl_exec($ch); 
    print_r($result);
}
else if($act == 'newUser')
{
    $email = $_GET['email'];
    $firstName = $_GET['firstName'];
    $lastName = $_GET['lastName'];
    if($email && $firstName)
    {
        //$username = 'zenovlyapi';
        //$password = 'ACi3OgRkZnKUA24kjiURFXpFx9VYbkOmMQKBtaRFbi7RDkOPbg';
        $URL='https://api.sandbox.mangopay.com/v2.01/zenovlyapi/users/natural/';
        $data = array("Tag" => "custom meta", "FirstName" => "$firstName", "LastName" => "$lastName", "Email" => "$email", "Birthday" => 121271, "Nationality" => "GB", "CountryOfResidence" => "FR");                                                                    
        $data_string = json_encode($data); //print_r($data_string);
        $ch = curl_init($URL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json', 
            'Content-Length: ' . strlen($data_string))
        );
        $result = curl_exec($ch);
        print_r($result);
    }
}
else if($act == 'editUser')
{
    $email = $_GET['email'];
    $firstName = $_GET['firstName'];
    $lastName = $_GET['lastName'];
    $id = $_GET['id'];
    if($email && $firstName && $id)
    {
        //$username = 'zenovlyapi';
        //$password = 'ACi3OgRkZnKUA24kjiURFXpFx9VYbkOmMQKBtaRFbi7RDkOPbg';
        $URL='https://api.sandbox.mangopay.com/v2.01/zenovlyapi/users/natural/'.$id.'/';
        $data = array("Tag" => "custom meta", "FirstName" => "$firstName", "LastName" => "$lastName", "Email" => "$email", "Birthday" => 1463496101, "Nationality" => "GB", "CountryOfResidence" => "FR");                                                                    
        $data_string = json_encode($data); //print_r($data_string);
        $ch = curl_init($URL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json', 
            'Content-Length: ' . strlen($data_string))
        );
        $result = curl_exec($ch);
        print_r($result);
    }
}
else
{
    //$username = 'zenovlyapi';
    //$password = 'ACi3OgRkZnKUA24kjiURFXpFx9VYbkOmMQKBtaRFbi7RDkOPbg';
    $URL = 'https://api.sandbox.mangopay.com/v2.01/zenovlyprod/users/';
    $ch = curl_init($URL);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    $result = curl_exec($ch); 
    print_r($result);
}
?>