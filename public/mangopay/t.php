<?php
// include MangoPay SDK
require_once './vendor/autoload.php';
/*
define('MangoPayDemo_BaseUrl', 'https://api.sandbox.mangopay.com');
define('MangoPayDemo_ClientId', 'zenovlyprod');
define('MangoPayDemo_ClientPassword', 'eLctgkF5gLphvTAZDW0T0cLosJ85Rb20rHuM5diXy5ZswKs46J');
*/

define('MangoPayDemo_ClientId', 'zenovlyprod');
 
if($_SERVER['HTTP_HOST']=='safe-tonytoons.c9users.io:80' || $_SERVER['HTTP_HOST']=='safe-tonytoons.c9users.io'){
    define('MangoPayDemo_BaseUrl', 'https://api.sandbox.mangopay.com');
    define('MangoPayDemo_ClientPassword', 'eLctgkF5gLphvTAZDW0T0cLosJ85Rb20rHuM5diXy5ZswKs46J'); // Dev key
}else{
    define('MangoPayDemo_BaseUrl', 'https://api.mangopay.com');  
    define('MangoPayDemo_ClientPassword', '3XUKnuaS2qX9LZkeq6jQ9FmJKUYXwCbyCkcVz28kw7ABGr28iV'); // Pro Key
}

define('MangoPayDemo_TemporaryFolder', __dir__); 

// Initialize MangoPay SDK
$mangoPayApi = new \MangoPay\MangoPayApi();
$mangoPayApi->Config->BaseUrl = MangoPayDemo_BaseUrl;
$mangoPayApi->Config->ClientId = MangoPayDemo_ClientId;
$mangoPayApi->Config->ClientPassword = MangoPayDemo_ClientPassword;
$mangoPayApi->Config->TemporaryFolder = MangoPayDemo_TemporaryFolder;
$act = $_GET['act'];
$p = 100; 



if(empty($act)) $act = 'list';

if($act == 'newUser')
{
    $newUserId = '';
    $email = $_GET['email'];
    $firstName = $_GET['firstName'];
    $lastName = $_GET['lastName'];
    if(empty($lastName)) $lastName = 'Wezenit';
    $email = str_replace(' ', '+', $email);
    if($email && $firstName && $lastName)
    {
        $User = new MangoPay\UserLegal();
        $User->Name = $firstName;
        $User->LegalPersonType = "BUSINESS";
        $User->Email = $email;
        $User->LegalRepresentativeFirstName = $firstName;
        $User->LegalRepresentativeLastName = $lastName;
        $User->LegalRepresentativeBirthday = strtotime(date("Y-m-d H:i:s")); 
        $User->LegalRepresentativeNationality = "FR";
        $User->LegalRepresentativeCountryOfResidence = "FR";
        $newUser = $mangoPayApi->Users->Create($User);
        if($newUser) $newUserId = $newUser->Id;
    }
    echo $newUserId;
}
else if($act == 'updateUser')
{
    $result = ['Status'=>400, 'result'=>'']; 
    
    $UserId = $_GET['UserId']; 
    $firstName = $_POST['name'];
    $lastName = $_POST['lastname'];
    $email = $_POST['email'];  
    //$bd = explode("-",$_POST['birth_day']); 
    $Birthday = strtotime($_POST['birth_day']);  
    $Nationality = !empty($_POST['nationality'])?$_POST['nationality']:'FR';  
    $Country = !empty($_POST['country'])?$_POST['country']:'FR';  
     
    $Address = $_POST['address']; 
    $fullname = $firstName.' '.$lastName;
    
    $Cname = $_POST['company_name'];  
    if(empty($Cname)) $Cname = $fullname; 
      
    $cEmail = $_POST['company_email']; 
    if(empty($cEmail)) $cEmail = $email;  
    
    $Caddress = $_POST['company_address']; 
    if(empty($Caddress)) $Caddress = $Address;
    
    $CCountry = $_POST['company_country'];  
    if(empty($CCountry)) $CCountry = $Country; 
    
     
    if(empty($lastName)) $lastName = 'Wezenit';
    $email = str_replace(' ', '+', $email);  
    
    if(!empty($UserId) && !empty($email) && !empty($firstName) && !empty($lastName))
    { 
        try{
            
            $User = new MangoPay\UserLegal(); 
             
            $User->LegalPersonType = \MangoPay\LegalPersonType::Business;
            
            $User->Email = $cEmail;     
            $User->Name = $Cname; 
            
            //print_r($_POST); exit; 
            
            if(!empty($Caddress)){
                $User->CompanyNumber = $_POST['company_id'];
                $User->HeadquartersAddress = new MangoPay\Address();  
                $User->HeadquartersAddress->AddressLine1 = $Caddress;       
                $User->HeadquartersAddress->AddressLine2 = '';      
                $User->HeadquartersAddress->City = !empty($_POST['company_city'])?$_POST['company_city']:'Paris'; 
                $User->HeadquartersAddress->Country = $CCountry;    
                $User->HeadquartersAddress->PostalCode = !empty($_POST['company_postal_code'])?$_POST['company_postal_code']:'75001'; 
                $User->HeadquartersAddress->Region = !empty($_POST['company_region'])?$_POST['company_region']:'Ile de France'; 
            }
            
            $User->LegalRepresentativeFirstName = $firstName;
            $User->LegalRepresentativeLastName = $lastName; 
            $User->LegalRepresentativeBirthday = $Birthday;
            $User->LegalRepresentativeNationality = $Nationality;
            $User->LegalRepresentativeCountryOfResidence =$Country;
            $User->LegalRepresentativeEmail = $email;       
             
            if(!empty($Address)){     
                $User->LegalRepresentativeAddress = new MangoPay\Address();  
                $User->LegalRepresentativeAddress->AddressLine1 = $Address;     
                $User->LegalRepresentativeAddress->AddressLine2 = '';    
                $User->LegalRepresentativeAddress->City = !empty($_POST['City'])?$_POST['City']:'Paris';    
                $User->LegalRepresentativeAddress->Country = $Country;     
                $User->LegalRepresentativeAddress->PostalCode = !empty($_POST['PostalCode'])?$_POST['PostalCode']:'75001';
                $User->LegalRepresentativeAddress->Region = !empty($_POST['Region'])?$_POST['Region']:'Ile de France';  
            } 
             
            $User->Id = $UserId;      
            
            
            $Result = $mangoPayApi->Users->Update($User); 
            $result = ['status'=>200, 'items'=>$Result];   
            
        }catch(MangoPay\Libraries\ResponseException $e) {
            
            $status = $e->GetCode();
            $rs = $e->GetMessage();  
            $result = ['status'=>$status, 'items'=>$rs];   
             
        }catch(MangoPay\Libraries\Exception $e) { 
             
            $status = $e->GetCode(); 
            $rs = $e->GetMessage(); 
            $result = ['status'=>$status, 'items'=>$rs]; 
            
        }   
        //if($newUser) $newUserId = $newUser->Id;
    }
    echo json_encode($result);
    //echo $newUserId;
}
else if($act == 'wallet')
{
    $wallet_id = '';
    $id = $_GET['id']; 
    $name = @$_GET['name'];
    if($id)
    {
        $name = !empty($name)?$name:$id;
        $Wallet = new \MangoPay\Wallet();
        $Wallet->Owners = array($id);   
        $Wallet->Description = "Wezenit wallet of ".$name;   
        $Wallet->Currency = "EUR";
        $result = $mangoPayApi->Wallets->Create($Wallet);
        if($result) $wallet_id = $result->Id;
    }
    echo $wallet_id;
}
else if($act == 'payInCW')
{
    $wid = $_GET['wid'];
    $id = $_GET['id'];
    $amount = $_GET['amount'];
    $fee = !empty($_GET['fee'])?$_GET['fee']:0;
    $lang = $_GET['lang'];
    $returnURL = $_GET['returnURL'];
    $zenovly_id = $_GET['zenovly_id'];
    $payInType = @$_GET['payInType'];
    $lang = strtoupper($lang);
    
    if(empty($payInType)) $payInType='card';
    
    if( ($lang != 'EN') && ($lang != 'FR') ) $lang = 'FR';
    
    
    
    if($wid && $id && $amount && $payInType=='card') 
    {
        try{ 
            $PayIn = new \MangoPay\PayIn();
            $PayIn->CreditedWalletId = $wid;
            $PayIn->AuthorId = $id;
            $PayIn->PaymentType = "CARD";
            $PayIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsCard();
            $PayIn->PaymentDetails->CardType = "CB_VISA_MASTERCARD"; 
            $PayIn->DebitedFunds = new \MangoPay\Money();
            $PayIn->DebitedFunds->Currency = "EUR";
            $PayIn->DebitedFunds->Amount = $amount*$p;
            $PayIn->Fees = new \MangoPay\Money();
            $PayIn->Fees->Currency = "EUR";
            $PayIn->Fees->Amount = $fee*$p;
            $PayIn->ExecutionType = "WEB";
            $PayIn->ExecutionDetails = new \MangoPay\PayInExecutionDetailsWeb();
            $PayIn->ExecutionDetails->ReturnURL = "http".(isset($_SERVER['HTTPS']) ? "s" : null)."://".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]."?act=pc&zenovly_id=".$zenovly_id."&returnURL=".$returnURL;
            $PayIn->ExecutionDetails->Culture = $lang;//"EN"; 
            $result = $mangoPayApi->PayIns->Create($PayIn); 
            print_r(json_encode($result));
            exit;
        }catch(MangoPay\Libraries\ResponseException $e) {   
            $status = 404; 
            $rs = $e->GetMessage(); 
            echo json_encode(['Status'=>$status, 'ResultMessage'=>$rs, 'ResultCode'=>$status]);
        } 
        exit();
        /*
        if($result->Status=='CREATED'){ 
            header("Location: ".$result->ExecutionDetails->RedirectURL);
        }else{ 
            header("Location: ".$returnURL.'&error='.$result->ResultMessage);
        }     
        exit();
        */
    }else if(!empty($wid) && !empty($id) && !empty($amount) && $payInType=='directdebit'){
        
        try{ 
            
            $PayIn = new \MangoPay\PayIn();
            $PayIn->PaymentType = 'DIRECTDEBIT';
            $PayIn->AuthorId = $id; 
            $PayIn->CreditedWalletId = $wid; 
            $PayIn->PaymentDetails = new \MangoPay\PayInPaymentDetailsDirectDebit();
            $PayIn->PaymentDetails->DirectDebitType = "Giropay";  
            $PayIn->DebitedFunds = new \MangoPay\Money();
            $PayIn->DebitedFunds->Currency = "EUR";
            $PayIn->DebitedFunds->Amount = $amount*$p; 
            $PayIn->Fees = new \MangoPay\Money();
            $PayIn->Fees->Currency = "EUR";  
            $PayIn->Fees->Amount = $fee*$p;   
            $PayIn->ExecutionType = "WEB"; 
            $PayIn->ExecutionDetails = new \MangoPay\PayInExecutionDetailsWeb();
            $PayIn->ExecutionDetails->ReturnURL = "http".(isset($_SERVER['HTTPS']) ? "s" : null)."://".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]."?act=pc&zenovly_id=".$zenovly_id."&returnURL=".$returnURL;
            $PayIn->ExecutionDetails->Culture = $lang;//"EN"; 
            /*
            echo "<pre>";
            print_r(json_encode($PayIn));
            echo "</pre>";
            exit; */  
            $result = $mangoPayApi->PayIns->Create($PayIn); 
            print_r(json_encode($result));
            exit; 
            if($result->Status=='CREATED'){ 
                header("Location: ".$result->ExecutionDetails->RedirectURL);
            }else{  
                header("Location: ".$returnURL.'&error='.$result->ResultMessage);
            } 
        }catch(MangoPay\Libraries\ResponseException $e) {
            $status = 404; 
            $rs = $e->GetMessage(); 
            echo json_encode(['Status'=>$status, 'ResultMessage'=>$rs, 'ResultCode'=>$status]);
        } 
        exit();
        
        
    }
}
/*else if($act == 'card')
{
    $id = $_GET['id'];
    if($id)
    {
        $cardRegister = new \MangoPay\CardRegistration();
        $cardRegister->UserId = $id;
        $cardRegister->Currency = "EUR";
        $result = $mangoPayApi->CardRegistrations->Create($cardRegister); //print_r($result);
        if($result->Id)
        {
            //$CardRegistration = new \MangoPay\CardRegistration();
            $CardRegistration->Tag = "custom meta";
            
            $CardRegistration->UserId = $id;
            $CardRegistration->CardType = "CB_VISA_MASTERCARD";
            $CardRegistration->AccessKey = $result->AccessKey;
            $CardRegistration->PreregistrationData = $result->PreregistrationData;
            $CardRegistration->CardRegistrationURL = $result->CardRegistrationURL;
            //$CardRegistration->CardId = '';
            //$CardRegistration->RegistrationData = '';
            //$CardRegistration->ResultCode = '';
            //$CardRegistration->ResultMessage = '';
            $CardRegistration->Currency = $result->Currency;
            //$CardRegistration->Status = '';
            $CardRegistration->Id = $result->Id;
            $CardRegistration->CreationDate = $result->CreationDate;
            
            
            //$CardRegistration->cards->Id = $result->Id;
            //$CardRegistration->cards->reationDate = $result->CreationDate;
            $CardRegistration->cards->Tag = 1019;
            $CardRegistration->cards->Alias = 970101122334414;
            $CardRegistration->cards->CardProvider = "Mangopay Ltd";
            $CardRegistration->cards->CardType = "CB_VISA_MASTERCARD";
            $CardRegistration->cards->Country = "FR";
            $CardRegistration->cards->Product = "G";
            $CardRegistration->cards->BankCode = "00152";
            $CardRegistration->cards->Active = true;
            $CardRegistration->cards->Currency = "EUR";
            $CardRegistration->cards->Validity = "VALID"; print_r($CardRegistration);
            $rs = $mangoPayApi->CardRegistrations->Update($CardRegistration); print_r($rs);
        }
    }
}*/
else if($act == 'pc')
{
    $rs = '';
    $transactionId = $_GET['transactionId'];
    $returnURL = $_GET['returnURL'];
    $zenovly_id = $_GET['zenovly_id'];
    if($transactionId && $returnURL && $zenovly_id)
    {
        $PayIn = $mangoPayApi->PayIns->Get($transactionId); 
        $rs = $PayIn->ResultMessage;
        if($rs == 'Success')
        {
            $host_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'];
            $txt = file_get_contents($host_url.'/api/en/payrs/'.$zenovly_id.'/?rs='.$rs);  
        } 
        header("Location: ".$returnURL);
        exit();
    }
}
else if($act == 'addBank')
{
    
    $status = 404; 
    $rs = '';
    $bankID = '';
    $type = !empty($_GET['Type'])?$_GET['Type']:'IBAN';
    $UserId = $_GET['Mangopayid'];
    $OwnerName = $_GET['OwnerName'];
    $OwnerAddress = $_GET['Address']; 
    if(empty($OwnerAddress)) $OwnerAddress = 'Address line 1'; 
    
    $BankAccount = new \MangoPay\BankAccount();
    $BankAccount->Type = $type;  
    
    if($type == 'IBAN' && !empty($_GET['IBAN']) && !empty($_GET['BIC'])){ 
        
        $BankAccount->Details = new MangoPay\BankAccountDetailsIBAN();
        $BankAccount->Details->IBAN = $_GET['IBAN']; //"FR7618829754160173622224154";
        $BankAccount->Details->BIC = $_GET['BIC'];//"CMBRFR2BCME";
         
    }else if($type == 'US'  && !empty($_GET['AccountNumber']) && !empty($_GET['ABA'])){ 
        
        $BankAccount->Details = new MangoPay\BankAccountDetailsUS();
        $BankAccount->Details->AccountNumber = $_GET['AccountNumber']; 
        $BankAccount->Details->ABA = $_GET['ABA'];
        $BankAccount->Details->DepositAccountType = $_GET['DepositAccountType'];
        
    }else if($type == 'CA' && !empty($_GET['AccountNumber']) && !empty($_GET['BranchCode']) && !empty($_GET['InstitutionNumber']) && !empty($_GET['BankName'])){ 
        
        $BankAccount->Details = new MangoPay\BankAccountDetailsCA(); 
        $BankAccount->Details->AccountNumber = $_GET['AccountNumber'];
        $BankAccount->Details->BranchCode = $_GET['BranchCode'];
        $BankAccount->Details->InstitutionNumber = $_GET['InstitutionNumber'];
        $BankAccount->Details->BankName = $_GET['BankName'];   
        
    }else if($type == 'GB' && !empty($_GET['AccountNumber']) && !empty($_GET['SortCode'])){ 
        
        $BankAccount->Details = new MangoPay\BankAccountDetailsGB();
        $BankAccount->Details->AccountNumber = $_GET['AccountNumber'];
        $BankAccount->Details->SortCode = $_GET['SortCode'];
        
        
    }else if($type == 'OTHER' && !empty($_GET['AccountNumber']) && !empty($_GET['BIC']) && !empty($_GET['BIC'])){
        
        $BankAccount->Details = new MangoPay\BankAccountDetailsOTHER();
        $BankAccount->Details->AccountNumber = $_GET['AccountNumber'];
        $BankAccount->Details->BIC = $_GET['BIC'];
        $BankAccount->Details->Country = !empty($_GET['Country'])?$_GET['Country']:'FR';
        
    }else{
        
        echo json_encode(array('status'=>404,'result'=>'Please check parameter.!'));  
        exit;
    }
    
    if(!empty($OwnerName)){
        
        $BankAccount->OwnerName = $OwnerName;//"Joe Bloggs"; 
        $BankAccount->OwnerAddress = new \MangoPay\Address();
        $BankAccount->OwnerAddress->AddressLine1 = $OwnerAddress;
        $BankAccount->OwnerAddress->AddressLine2 = '';  
        $BankAccount->OwnerAddress->City = !empty($_GET['City'])?$_GET['City']:'Paris'; 
        $BankAccount->OwnerAddress->Country = !empty($_GET['Country'])?$_GET['Country']:'FR';
        $BankAccount->OwnerAddress->PostalCode = !empty($_GET['PostalCode'])?$_GET['PostalCode']:'75001';
        $BankAccount->OwnerAddress->Region = !empty($_GET['Region'])?$_GET['Region']:'Ile de France'; 
        
    }else{
        
        $status = 404; 
        $rs = 'Not Owner Name'; 
        
    }
    
    try {
       if(!empty($UserId)){ 
           /*
            echo "<pre>"; 
            print_r($BankAccount);
            echo "</pre>";
            */
            //exit;
            $result = $mangoPayApi->Users->CreateBankAccount($UserId, $BankAccount); 
            //print_r($result); exit;
            $status = 200;
       }else{ 
           $status = 404;  
           $rs = 'Not User ID!';  
       }
    }catch(MangoPay\Libraries\ResponseException $e) {   
       $status = 404;  
       $rs = $e->GetMessage(); 
    } 
    
    if(!empty($result)) $rs = $result->Id;
    echo json_encode(array('status'=>$status,'result'=>$rs)); 
    exit;
}
else if($act == 'payOut')
{
    $status = '';
    $UserId = $_GET['userId'];
    $walletId = $_GET['walletId'];
    $amount = $_GET['amount'];
    $bankID = $_GET['bankID']; 
    $fee = !empty($_GET['fee'])?$_GET['fee']:0;
    $result = []; 
   
    try{
        
        if($UserId && $walletId && $amount && $bankID)
        {
            $PayOut = new \MangoPay\PayOut();
            $PayOut->AuthorId = $UserId;//'27612736';
            $PayOut->DebitedWalletId = $walletId;//'27387716';
            $PayOut->DebitedFunds = new \MangoPay\Money();
            $PayOut->DebitedFunds->Currency = "EUR";
            $PayOut->DebitedFunds->Amount = $amount*$p;//25;
            $PayOut->Fees = new \MangoPay\Money();
            $PayOut->Fees->Currency = "EUR"; 
            $PayOut->Fees->Amount = $fee*$p;     
            $PayOut->PaymentType = \MangoPay\PayOutPaymentType::BankWire;
            $PayOut->MeanOfPaymentDetails = new \MangoPay\PayOutPaymentDetailsBankWire();
            $PayOut->MeanOfPaymentDetails->BankAccountId = $bankID;//'27613087';
            $result = $mangoPayApi->PayOuts->Create($PayOut);
            echo json_encode($result);exit;   
            //if($result) $status =$result->Status;
        }     
      
    }catch(MangoPay\Libraries\Exception $e) {
        $status = $e->GetCode();  
        $rs = $e->GetMessage();  
        $result = ['ResultCode'=>$status, 'ResultMessage'=>$rs];  
    }
    echo json_encode($result); 
    exit; 
} 
else if($act == 'refund')
{
    $status = '';
    $UserId = $_GET['userId']; 
    $PayInId = $_GET['PayInId'];
    $amount = $_GET['amount'];
    $fee = !empty($_GET['fee'])?$_GET['fee']:0;
    $result = []; 
     
    if($UserId && $PayInId && $amount)
    {  
        try { 
            
            /*
            $Refund = new \MangoPay\Refund();
            $Refund->AuthorId = "'".$UserId."'";  
            $Refund->DebitedFunds = new \MangoPay\Money();
            $Refund->DebitedFunds->Currency = "EUR";
            $Refund->DebitedFunds->Amount = 30;  
            $Refund->Fees = new \MangoPay\Money(); 
            $Refund->Fees->Currency = "EUR";   
            $Refund->Fees->Amount = 10;          
            //print_r(json_encode($Refund)); exit;
            */
            
            $Refund = new \MangoPay\Refund();
            $Refund->AuthorId = $UserId; 
            $Refund->DebitedFunds = new \MangoPay\Money();
            $Refund->DebitedFunds->Currency = "EUR";
            $Refund->DebitedFunds->Amount = $amount*$p;
            $Refund->Fees = new \MangoPay\Money();
            $Refund->Fees->Currency = "EUR"; 
            $Refund->Fees->Amount = 0; 
            $result = $mangoPayApi->PayIns->CreateRefund($PayInId, $Refund);
            $result = ['Status'=>$result->Status, 'Message'=>$result->ResultMessage, 'Id'=>$result->Id]; 
            //echo "<pre>";        
            //print_r(json_encode($result)); exit;   
            //echo "</pre>"; 
        }catch(MangoPay\Libraries\ResponseException $e) {
            
            $status = $e->GetCode();
            $rs = $e->GetMessage();
            $result = ['Status'=>$status, 'Message'=>$rs];     
        }   
        
        //$result = $Api->PayIns->CreateRefund($PayInId, $Refund);
    }   
    echo json_encode($result);  
    exit; 
}
else if($act == 'transfers')
{
    $status = '';
    $AuthorId = $_GET['AuthorId']; //user id
    $CreditedUserId = $_GET['CreditedUserId']; // user id seller
    $DebitedWalletId = $_GET['DebitedWalletId']; // WalletId buyer 
    $CreditedWalletId = $_GET['CreditedWalletId']; // WalletId seller
    $amount = $_GET['Amount'];   
    $fee = !empty($_GET['Fee'])?$_GET['Fee']:0;
    $Tag = $_GET['Tag'];  
    $result = []; 
     
    if($AuthorId && $CreditedUserId && $DebitedWalletId && $CreditedWalletId && $amount)
    {  
        try {   
            
            $Transfer = new \MangoPay\Transfer();
            $Transfer->Tag = $Tag;
            $Transfer->AuthorId = $AuthorId;
            $Transfer->CreditedUserId = $CreditedUserId;
            $Transfer->DebitedFunds = new \MangoPay\Money();
            $Transfer->DebitedFunds->Currency = "EUR";
            $Transfer->DebitedFunds->Amount = $amount*$p;
            $Transfer->Fees = new \MangoPay\Money();
            $Transfer->Fees->Currency = "EUR";
            $Transfer->Fees->Amount = $fee*$p;
            $Transfer->DebitedWalletId = $DebitedWalletId;
            $Transfer->CreditedWalletId = $CreditedWalletId; 
            $result = $mangoPayApi->Transfers->Create($Transfer);  
            //$result = ['Status'=>$result->Status, 'Message'=>$result->ResultMessage, 'Result'=>$Result, 'Id'=>$result->Id]; 
               
        }catch(MangoPay\Libraries\ResponseException $e) {
            
            $status = $e->GetCode();
            $rs = $e->GetMessage();
            $result = ['ResultCode'=>$status, 'ResultMessage'=>$rs];     
        }    
        
        //$result = $Api->PayIns->CreateRefund($PayInId, $Refund);
    }   
    echo json_encode($result);  
    exit; 
}
else if($act == 'wallets')
{
    $status = '';
    $mangopay_id = $_GET['mangopay_id'];
    $mangopay_wallet = $_GET['mangopay_wallet'];  
    $result = [];  
     
    if(!empty($mangopay_id) && !empty($mangopay_wallet))
    {  
        try {   
            
            $WalletId = $mangopay_wallet; 

            $result = $mangoPayApi->Wallets->Get($WalletId);  
                
            $result = ['Status'=>200, 'result'=>$result, 'Id'=>$result->Id]; 
              
        }catch(MangoPay\Libraries\ResponseException $e) {
            
            $status = $e->GetCode();
            $rs = $e->GetMessage();
            $result = ['Status'=>$status, 'Message'=>$rs];     
        }   
        
    }   
    echo json_encode($result);  
    exit; 
}
else if($act == 'bankaccounts')
{
    $status = '';
    $UserId = $_GET['mangopay_id'];
    $BankAccountId = $_GET['bank_id'];
    $result = [];   
     
    if(!empty($UserId))
    {  
        try {  
             
            //$UserId = 56784777; 
            //$BankAccountId = 56787982;  
            
            $filter = new \MangoPay\BankAccount(); 
            $filter->Active = 1;  
            
            $pagination = new MangoPay\Pagination(1, 20);  
            $sorting = new \MangoPay\Sorting();
            $sorting->AddField("CreationDate", \MangoPay\SortDirection::DESC); 
                
            $BankAccount = $mangoPayApi->Users->GetBankAccounts($UserId, $pagination, $sorting, $filter); 
                
            $result = ['Status'=>200, 'result'=>$BankAccount];  
              
        }catch(MangoPay\Libraries\ResponseException $e) {
            
            $status = $e->GetCode();
            $rs = $e->GetMessage();
            $result = ['Status'=>$status, 'Message'=>$rs];     
        }   
        
    }   
    echo json_encode($result);  
    exit; 
}
else if($act == 'viewTransfer')
{ 
    $status = '';
    $TransferId = $_GET['TransferId'];
    $result = ['Status'=>404, 'result'=>''];   
     
    if(!empty($TransferId))
    {  
        try {  
            
            $result = $mangoPayApi->Transfers->Get($TransferId); 
            
            //$result = ['Status'=>200, 'result'=>$Transfer];  
              
        }catch(MangoPay\Libraries\ResponseException $e) {
            
            $status = $e->GetCode(); 
            $rs = $e->GetMessage();
            $result = ['Status'=>$status, 'Message'=>$rs];     
        }   
        
    }   
    echo json_encode($result);  
    exit; 
}
else if($act == 'viewPayout')
{
    $status = '';
    $PayOutId = $_GET['PayOutId'];
    $result = ['Status'=>404, 'result'=>''];   
     
    if(!empty($PayOutId))
    {   
        try { 
            
            $result = $mangoPayApi->PayOuts->Get($PayOutId);
            //$result = ['Status'=>200, 'result'=>$PayOut]; 
               
        }catch(MangoPay\Libraries\ResponseException $e) {
            
            $status = $e->GetCode();
            $rs = $e->GetMessage();
            $result = ['Status'=>$status, 'Message'=>$rs];     
        } 
    }   
    echo json_encode($result);  
    exit; 
}
else if($act == 'deactivate_bank')
{
    $status = '';
    $BankAccountId = $_GET['BankAccountId'];
    $UserId = $_GET['UserId'];
    $result = ['Status'=>404, 'result'=>''];   
     
    if(!empty($BankAccountId) && !empty($UserId))
    {   
        try { 
            $bankAccount = $mangoPayApi->Users->GetBankAccount($UserId, $BankAccountId);
            $bankAccount->Active = 0;
            $result = $mangoPayApi->Users->UpdateBankAccount($UserId, $bankAccount);  
            $result = ['Status'=>200, 'result'=>$result];  
            
        }catch(MangoPay\Libraries\ResponseException $e) {
             
            $status = $e->GetCode();
            $rs = $e->GetMessage(); 
            $result = ['Status'=>$status, 'result'=>$rs];     
        } 
    }   
    echo json_encode($result);  
    exit; 
}
else if($act == 'uploadKYC')
{
     
    $status = '';
    $Type = $_GET['Type'];
    $UserId = $_GET['UserId']; 
    $File = $_POST['File'];
    $result = ['Status'=>404, 'result'=>''];   
    
    if(!empty($Type) && !empty($UserId)) 
    {   
        try {  
            
            //echo $File;exit; 
            $fname = upload_file($File, $UserId);  
            if(!empty($File)){  
                $File = $fname;
            } 
            
            //$File = 'logo.png';  
           
            $KycDocument = new \MangoPay\KycDocument();
            $KycDocument->Tag = '';
            $KycDocument->Type = $Type;
            $result = $mangoPayApi->Users->CreateKycDocument($UserId, $KycDocument);
            $KycDocumentId = $result->Id; 
            /* 
            $UserId = 59491447;  
            $KycDocumentId = 59546667;  
            */ 
            /* 
            $UserId = 59491447; 
            $KycDocumentId = 59546206;   
            */
             
            //add a page to this doc
            $result2 = $mangoPayApi->Users->CreateKycPageFromFile($UserId, $KycDocumentId, 'files/'.$File); 
            /*
            $KycPage = new \MangoPay\KycPage();
            $KycPage->File = $File;   
            $result2 = $mangoPayApi->Users->CreateKycPageFromFile($UserId, $KYCDocumentId, $KycPage);  
            */
            
            //submit the doc for validation
           
            $KycDocument = new MangoPay\KycDocument();  
            $KycDocument->Id = $KycDocumentId;
            $KycDocument->Status = \MangoPay\KycDocumentStatus::ValidationAsked;
            $result3 = $mangoPayApi->Users->UpdateKycDocument($UserId, $KycDocument);  
            
            /*
            $KycDocument = new \MangoPay\KycDocument();
            $KycDocument->Tag = "";
            $KycDocument->Status = "VALIDATION_ASKED";
            $KycDocument->UserId = $UserId;
            $KycDocument->Id = $KycDocumentId;
            $Result = $mangoPayApi->Users->UpdateKycDocument($KycDocument);
            */
            $result = ['Status'=>200, 'result'=>$result3];  
            @unlink('files/'.$fname);  
            
        }catch(MangoPay\Libraries\ResponseException $e) {
            $status = $e->GetCode();
            $rs = $e->GetMessage(); 
            $result = ['Status'=>$status, 'result'=>$rs];     
        }catch(MangoPay\Libraries\Exception $e) {
            $status = $e->GetCode(); 
            $rs = $e->GetMessage(); 
            $result = ['Status'=>$status, 'result'=>$rs];  
        }
    }   
    echo json_encode($result);  
    exit; 
}
else if($act == 'KycList')
{
    
    $status = '';
    $UserId = $_GET['UserId'];
    $result = [];   
     
    if(!empty($UserId))
    {  
        try {  
            
            $KycDocument = new \MangoPay\KycDocument();
            //$KycDocument->Id = $KycDocumentId; 
            //$Result = $mangoPayApi->Users->GetKycDocument($KycDocument);
            $pagination = new MangoPay\Pagination(1, 20);  
            $sorting = new \MangoPay\Sorting();
            $sorting->AddField("CreationDate", \MangoPay\SortDirection::DESC);
            $Result = $mangoPayApi->Users->GetKycDocuments($UserId, $pagination, $sorting);
            $result = ['Status'=>200, 'result'=>$Result];   
              
        }catch(MangoPay\Libraries\ResponseException $e) {
            $status = $e->GetCode();
            $rs = $e->GetMessage(); 
            $result = ['Status'=>$status, 'Message'=>$rs];     
        }catch(MangoPay\Libraries\Exception $e) {
            $status = $e->GetCode(); 
            $rs = $e->GetMessage(); 
            $result = ['Status'=>$status, 'result'=>$rs];  
        }   
        
    }   
    echo json_encode($result);  
    exit; 
}
else
{
   echo 'user'; 
   exit;
}


function upload_file($encoded_string, $fname){ 
    $target_dir = 'files/'; // add the specific path to save the file
    @chmod($target_dir, 0777);  
    $decoded_file = base64_decode($encoded_string); // decode the file
    $mime_type = finfo_buffer(finfo_open(), $decoded_file, FILEINFO_MIME_TYPE); // extract mime type
    $extension = mime2ext($mime_type); // extract extension from mime type
    $file = $fname.'_'.uniqid().'.'. $extension; // rename file as a unique name
    $file_dir = $target_dir . $file;     
    try {   
        file_put_contents($file_dir, $decoded_file); // save
        return $file;  
    } catch (Exception $e) {
        return false; 
    }

}
/*
to take mime type as a parameter and return the equivalent extension
*/
function mime2ext($mime){
    $all_mimes = '{"png":["image\/png","image\/x-png"],"bmp":["image\/bmp","image\/x-bmp",
    "image\/x-bitmap","image\/x-xbitmap","image\/x-win-bitmap","image\/x-windows-bmp",
    "image\/ms-bmp","image\/x-ms-bmp","application\/bmp","application\/x-bmp",
    "application\/x-win-bitmap"],"gif":["image\/gif"],"jpeg":["image\/jpeg",
    "image\/pjpeg"],"xspf":["application\/xspf+xml"],"vlc":["application\/videolan"],
    "wmv":["video\/x-ms-wmv","video\/x-ms-asf"],"au":["audio\/x-au"],
    "ac3":["audio\/ac3"],"flac":["audio\/x-flac"],"ogg":["audio\/ogg",
    "video\/ogg","application\/ogg"],"kmz":["application\/vnd.google-earth.kmz"],
    "kml":["application\/vnd.google-earth.kml+xml"],"rtx":["text\/richtext"],
    "rtf":["text\/rtf"],"jar":["application\/java-archive","application\/x-java-application",
    "application\/x-jar"],"zip":["application\/x-zip","application\/zip",
    "application\/x-zip-compressed","application\/s-compressed","multipart\/x-zip"],
    "7zip":["application\/x-compressed"],"xml":["application\/xml","text\/xml"],
    "svg":["image\/svg+xml"],"3g2":["video\/3gpp2"],"3gp":["video\/3gp","video\/3gpp"],
    "mp4":["video\/mp4"],"m4a":["audio\/x-m4a"],"f4v":["video\/x-f4v"],"flv":["video\/x-flv"],
    "webm":["video\/webm"],"aac":["audio\/x-acc"],"m4u":["application\/vnd.mpegurl"],
    "pdf":["application\/pdf","application\/octet-stream"],
    "pptx":["application\/vnd.openxmlformats-officedocument.presentationml.presentation"],
    "ppt":["application\/powerpoint","application\/vnd.ms-powerpoint","application\/vnd.ms-office",
    "application\/msword"],"docx":["application\/vnd.openxmlformats-officedocument.wordprocessingml.document"],
    "xlsx":["application\/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application\/vnd.ms-excel"],
    "xl":["application\/excel"],"xls":["application\/msexcel","application\/x-msexcel","application\/x-ms-excel",
    "application\/x-excel","application\/x-dos_ms_excel","application\/xls","application\/x-xls"],
    "xsl":["text\/xsl"],"mpeg":["video\/mpeg"],"mov":["video\/quicktime"],"avi":["video\/x-msvideo",
    "video\/msvideo","video\/avi","application\/x-troff-msvideo"],"movie":["video\/x-sgi-movie"],
    "log":["text\/x-log"],"txt":["text\/plain"],"css":["text\/css"],"html":["text\/html"],
    "wav":["audio\/x-wav","audio\/wave","audio\/wav"],"xhtml":["application\/xhtml+xml"],
    "tar":["application\/x-tar"],"tgz":["application\/x-gzip-compressed"],"psd":["application\/x-photoshop",
    "image\/vnd.adobe.photoshop"],"exe":["application\/x-msdownload"],"js":["application\/x-javascript"],
    "mp3":["audio\/mpeg","audio\/mpg","audio\/mpeg3","audio\/mp3"],"rar":["application\/x-rar","application\/rar",
    "application\/x-rar-compressed"],"gzip":["application\/x-gzip"],"hqx":["application\/mac-binhex40",
    "application\/mac-binhex","application\/x-binhex40","application\/x-mac-binhex40"],
    "cpt":["application\/mac-compactpro"],"bin":["application\/macbinary","application\/mac-binary",
    "application\/x-binary","application\/x-macbinary"],"oda":["application\/oda"],
    "ai":["application\/postscript"],"smil":["application\/smil"],"mif":["application\/vnd.mif"],
    "wbxml":["application\/wbxml"],"wmlc":["application\/wmlc"],"dcr":["application\/x-director"],
    "dvi":["application\/x-dvi"],"gtar":["application\/x-gtar"],"php":["application\/x-httpd-php",
    "application\/php","application\/x-php","text\/php","text\/x-php","application\/x-httpd-php-source"],
    "swf":["application\/x-shockwave-flash"],"sit":["application\/x-stuffit"],"z":["application\/x-compress"],
    "mid":["audio\/midi"],"aif":["audio\/x-aiff","audio\/aiff"],"ram":["audio\/x-pn-realaudio"],
    "rpm":["audio\/x-pn-realaudio-plugin"],"ra":["audio\/x-realaudio"],"rv":["video\/vnd.rn-realvideo"],
    "jp2":["image\/jp2","video\/mj2","image\/jpx","image\/jpm"],"tiff":["image\/tiff"],
    "eml":["message\/rfc822"],"pem":["application\/x-x509-user-cert","application\/x-pem-file"],
    "p10":["application\/x-pkcs10","application\/pkcs10"],"p12":["application\/x-pkcs12"],
    "p7a":["application\/x-pkcs7-signature"],"p7c":["application\/pkcs7-mime","application\/x-pkcs7-mime"],"p7r":["application\/x-pkcs7-certreqresp"],"p7s":["application\/pkcs7-signature"],"crt":["application\/x-x509-ca-cert","application\/pkix-cert"],"crl":["application\/pkix-crl","application\/pkcs-crl"],"pgp":["application\/pgp"],"gpg":["application\/gpg-keys"],"rsa":["application\/x-pkcs7"],"ics":["text\/calendar"],"zsh":["text\/x-scriptzsh"],"cdr":["application\/cdr","application\/coreldraw","application\/x-cdr","application\/x-coreldraw","image\/cdr","image\/x-cdr","zz-application\/zz-winassoc-cdr"],"wma":["audio\/x-ms-wma"],"vcf":["text\/x-vcard"],"srt":["text\/srt"],"vtt":["text\/vtt"],"ico":["image\/x-icon","image\/x-ico","image\/vnd.microsoft.icon"],"csv":["text\/x-comma-separated-values","text\/comma-separated-values","application\/vnd.msexcel"],"json":["application\/json","text\/json"]}';
    $all_mimes = json_decode($all_mimes,true);
    foreach ($all_mimes as $key => $value) {
        if(array_search($mime,$value) !== false) return $key;
    }
    return false;
}
