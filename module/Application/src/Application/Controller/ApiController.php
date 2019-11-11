<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Application\Models\Api;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;
use Application\Models\Songs;

use Zend\Db\Sql\Sql;//doc : https://framework.zend.com/manual/2.1/en/modules/zend.db.sql.html

/*
$this->params()->fromPost('paramname');   // From POST
$this->params()->fromQuery('paramname');  // From GET
$this->params()->fromRoute('paramname');  // From RouteMatch
$this->params()->fromHeader('paramname'); // From header
$this->params()->fromFiles('paramname');
*/
class ApiController extends AbstractActionController
{
################################################################################ 
    public function __construct()
    { 
        $this->cacheTime = 36000;
        $this->now = date("Y-m-d H:i:s");
        $this->eth = 'คุณไม่สามารถเข้าถึง API ได้ค่ะ!';
        $this->een = "Sorry, we can't process this time please try again later!";
        $this->config = include __DIR__ . '../../../../config/module.config.php';
        $this->adapter = new Adapter($this->config['Db']);
        
        $host_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'];
        $this->mangopayAPI = $host_url.'/mangopay/t.php?'; 
        $this->webURL = $host_url;   
        /*
        $this->mangopayAPI = 'http://dev.wezenit.com/mangopay/t.php?';
        $this->webURL = 'http://dev.wezenit.com'; */
    }
################################################################################   
    public function basic()
    {
        $view = new ViewModel();
        //Route
        $view->lang = $this->params()->fromRoute('lang', 'fr');
        $view->action = $this->params()->fromRoute('action', 'index');
        $view->id = $this->params()->fromRoute('id', '');
        $view->username = $this->params()->fromQuery('username', '');
        $view->password = $this->params()->fromQuery('password', '');
        $view->page = $this->params()->fromQuery('page', 1);
        $view->act = $this->params()->fromQuery('act', 'detail');
        $view->for = $this->params()->fromQuery('for', 'hot');
        $view->nocache = $this->params()->fromQuery('nocache', 0);
        if($view->lang=='fr'){
            $this->een = "Désolé, une erreur est survenue s'il vous plaît réessayer plus tard!";
        }  
        return $view;
    }
################################################################################   
    public function indexAction()
    {
        $view = $this->basic();
        return $view;
    }
################################################################################   
    public function regisAction()
    {
        $email = $this->params()->fromPost('email', '');
        $name = $this->params()->fromPost('name', '');
        $upassword = $this->params()->fromPost('upassword', '');
        $facebook_id = $this->params()->fromPost('facebook_id', '');
        $buyer_id = $this->params()->fromPost('buyer_id', '');
        $status = 404;
        $view = $this->basic();
        $items = $this->een;
        if($this->emailfromat($email) != 1)
        {
            $items = 'Please check your email fromat!';
            if($view->lang=='fr'){
                $items = "S'il vous plaît vérifier votre format e-mail!";
            }
        }
        else
        {
            if($email && $name) 
            {
                $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
                $this->login = $feedses->getLogin($view->username, $view->password);
                
                if($this->login > 0)
                {
                    //echo $facebook_id; exit;   
                    if($facebook_id){
                        $fAccount = $feedses->checkAccount(0, $email);
                        if($fAccount>=1){ 
                            $sql_str = "UPDATE users SET facebook_id = '".$facebook_id."', name = '".$name."', last_update = NOW() WHERE email = '".$email."'";
                            $sql = $this->adapter->query($sql_str); 
                            if($sql->execute())  
                            { 
                                $status = 200; 
                                $items = 'Registration was successful.';
                                if($view->lang=='fr'){
                                    $items = "L'enregistrement fut un succès.";
                                }
                                $data = array( 
                                                'status' => $status,
                                                'items' => $items
                                            );
                                echo $this->makeJSON($data);
                                $view->setTerminal(true);
                                return $view;   
                            } 
                        }
                    }else{
                        $fAccount = $feedses->checkAccount($facebook_id, $email);
                    }
                    
                    // register buyer status inactive from contract page
                    if($fAccount==1 && !empty($buyer_id) && !empty($email) && !empty($name)  && !empty($upassword))    
                    {
                        //echo "SELECT id FROM users WHERE id= '".$buyer_id."' AND email = '".$email."' AND active = '0' LIMIT 1";
                        $sql = $this->adapter->query("SELECT id FROM users WHERE id= '".$buyer_id."' AND email = '".$email."' LIMIT 1"); 
                        $results = $sql->execute();  
                        $row = $results->current();     
                          
                        if(!empty($row['id'])){ 
                            
                            $sql_str = "UPDATE users SET password = '".$upassword."', name = '".$name."', active = '1', last_update = NOW() WHERE email = '".$email."'";
                            $sql = $this->adapter->query($sql_str);   
                            if($sql->execute())      
                            {   
                                $status = 200; 
                                $items = 'Registration was successful.';
                                if($view->lang=='fr'){
                                    $items = "L'enregistrement fut un succès.";
                                }
                                $data = array( 
                                                'status' => $status,
                                                'items' => $items
                                            );
                                echo $this->makeJSON($data);
                                $view->setTerminal(true);
                                return $view;    
                            } 
                        }
                    }
                    
                    if($fAccount <= 0)
                    {
                        if($facebook_id)
                        {
                            $sql = $this->adapter->query("INSERT INTO users (facebook_id, name, email, password, added_date, last_update) VALUES ('$facebook_id', '$name', '$email', '$upassword', NOW(), NOW());");
                        }
                        else
                        {
                            if(strlen($upassword) > 3)
                            {
                                $sql = $this->adapter->query("INSERT INTO users (facebook_id, name, email, password, added_date, last_update) VALUES ('$facebook_id', '$name', '$email', '$upassword', NOW(), NOW());");
                            }
                            else
                            {
                                $items = 'Password at least 4 characters!';
                                if($view->lang=='fr'){
                                    $items = "Le nouveau mot de passe doit être de  6 à 15 caractères";
                                }
                            }
                        } 
                        
                        if(!empty($sql))
                        {
                            try 
                            {
                                $sql->execute();
                                //print_r($sql);  
                    			$status = 200;
                                $items = 'Registration was successful.';
                                if($view->lang=='fr'){
                                    $items = "L'enregistrement fut un succès.";
                                }
                                //$feedses->rcEmail($name, $email);
                            }catch (\Exception $e) {
                                //print_r(htmlentities($e->getMessage())); exit;
                            }
                        }
                    }
                    else
                    {
                        $items = 'Sorry! This account already exit.';
                        if($view->lang=='fr'){
                            $items = "Pardon! Ce compte existe déjà.";
                        }
                    }
                }
            }
        }
        $data = array(
                        'status' => $status,
                        'items' => $items
                    );
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
################################################################################   
    public function profileAction()
    {
        $status = 404;
        $view = $this->basic();
        $items = $this->een;
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $this->login = $feedses->getLogin($view->username, $view->password);
        if($this->login > 0)
        {
            $act = $this->params()->fromQuery('act', 'profile');
            if($act == 'edit')
            {
                $email = $this->params()->fromPost('email', '');
                $name = $this->params()->fromPost('name', ''); 
                
                $phone = $this->params()->fromPost('phone', '');
                $facebook_id = $this->params()->fromPost('facebook_id', '');
                $gender = $this->params()->fromPost('gender', '');
                $birth_day = $this->params()->fromPost('birth_day', '');
                $address = $this->params()->fromPost('address', '');
                
                $lastname = $this->params()->fromPost('lastname', '');
                $nationality = $this->params()->fromPost('nationality', 'FR');
                $country = $this->params()->fromPost('country', 'FR');
                
                $city = $this->params()->fromPost('City', '');
                $region = $this->params()->fromPost('Region', '');
                $postcode = $this->params()->fromPost('PostalCode', '');
                
                if($email && $name)
                {
                    $user = $feedses->userEdit($view->id, $email, $name, $phone, $facebook_id, $gender, $birth_day, $address, $lastname, $nationality, $country, $city, $region, $postcode);
                    // echo $user; exit; 
                    if($user)
                    {
                        $status = 200;
                        $items = $user;
                    }
                }
            }
            else if($act == 'changePassword')
            {
                $udpassword = $this->params()->fromQuery('udpassword', '');
                $upassword = $this->params()->fromQuery('upassword', '');
                if($udpassword && $upassword && $view->id)
                {
                    $user = $feedses->userCpassword($view->id, $udpassword, $upassword);
                    if($user)
                    {
                        $status = 200;
                        $items = 'Changed password was successful.';
                        if($view->lang=='fr'){
                            $items = "Mot de passe changé avec succès.";
                        }
                    }
                    else
                    {
                        $items = 'Sorry! We were not able to change your password.';
                        if($view->lang=='fr'){
                            $items = "Pardon! Nous ne sommes pas en mesure de changer votre mot de passe s'il vous plaît essayer à nouveau.";
                        }
                    }
                }
            }
            else if($act == 'changePasswordByEmail')
            {
                $email = $this->params()->fromQuery('email', '');
                $upassword = $this->params()->fromQuery('upassword', '');
                if($email && $upassword)
                {
                    $user = $feedses->userCpasswordByEmail($email, $upassword);
                    if($user)
                    {
                        $status = 200;
                        $items = 'Changed password was successful.';
                        if($view->lang=='fr'){
                            $items = "Mot de passe changé avec succès.";
                        }
                    }
                    else
                    {
                        $items = 'Sorry! We were not able to change your password.';
                        if($view->lang=='fr'){
                            $items = "Pardon! Nous ne sommes pas en mesure de changer votre mot de passe s'il vous plaît essayer à nouveau.";
                        }
                    }
                }
            }
            else if($act == 'forgotPassword')
            {
                $view->email = $this->params()->fromQuery('email', '');
                if($view->email)
                {
                    if($this->emailfromat($view->email) != 1)
                    {
                        $items = 'Please check your email fromat!';
                        if($view->lang=='fr'){
                            $items = "S'il vous plaît vérifier votre format e-mail!";
                        }
                    }
                    else
                    {
                        $user = $feedses->forgotPassword($view->email);
                        $status = $user['status'];
                        $items = $user['rs'];
                    }
                }
            }
            else if($act == 'imgPF')
            {
                $img = $this->params()->fromPost('img', '');
                if($view->id && $img)
                {
                    $user = $feedses->profilePIC($view->id, $img);
                    if($user)
                    {
                        $status = 200;
                        $items = 'Changed your picture was successful.';
                    }
                    else
                    {
                        $items = 'Sorry! We were not able to upload your picture.';
                    }
                }
            }
            else if($act == 'companyUpdate')
            {
                $company_name = $this->params()->fromPost('company_name', '');
                $company_id = $this->params()->fromPost('company_id', '');
                $company_country = $this->params()->fromPost('company_country', '');
                $company_address = $this->params()->fromPost('company_address', '');
                $company_mobile_phone = $this->params()->fromPost('company_mobile_phone', '');
                $company_phone = $this->params()->fromPost('company_phone', '');
                $company_email = $this->params()->fromPost('company_email', '');
                
                $city = $this->params()->fromPost('company_city', '');
                $region = $this->params()->fromPost('company_region', '');
                $postcode = $this->params()->fromPost('company_postcode', ''); 
                   
                $utype = 1;
                if($company_name && $company_address && $company_phone && $company_email) $utype = 2;
                
                //print_r($_POST); exit;  
                
                $user = $feedses->userCedit($view->id, $utype, $company_name, $company_address, $company_mobile_phone, $company_phone, $company_email, $company_id, $company_country, $city, $region, $postcode);
                //print_r($user); exit;
                if($user)
                {
                    $status = 200;
                    $items = $user;
                }
            }
            else if($act == 'new')
            {
                $email = $this->params()->fromPost('email', '');
                $name = $this->params()->fromPost('name', '');
                $phone_number = $this->params()->fromPost('phone_number', '');
                $above_position = $this->params()->fromPost('above_position', '');
                $above_company_name = $this->params()->fromPost('above_company_name', '');
                $above_company_address = $this->params()->fromPost('above_company_address', '');
                $above_company_website = $this->params()->fromPost('above_company_website', '');
                
                if($email && $name) 
                { 
                    $user = $feedses->newUser($email, $name, $phone_number, $above_position, $above_company_name, $above_company_address, $above_company_website);
                    if($user)
                    {  
                        $status = 200;
                        $items = $user;
                    }
                }
            }
            else if($act == 'editCustomer')
            {
                $email = $this->params()->fromPost('email', '');
                $name = $this->params()->fromPost('name', '');
                $phone = $this->params()->fromPost('phone', '');
                $above_position = $this->params()->fromPost('above_position', '');
                $above_company_name = $this->params()->fromPost('above_company_name', '');
                $above_company_address = $this->params()->fromPost('above_company_address', '');
                $above_company_website = $this->params()->fromPost('above_company_website', '');
                $customer_id = $this->params()->fromPost('customer_id', 0);
                
                if($name && $customer_id)  
                {  
                    $data = array(   
                        'name'=>$name,   
                        'phone'=>$phone,  
                        'last_update'=>date("Y-m-d H:i:s")
                    );   
                     
                    if(!empty($above_position)){
                        $data['above_position']=$above_position;
                        $data['above_company_name']=$above_company_name;
                        $data['above_company_address']=$above_company_address;
                        $data['above_company_website']=$above_company_website;
                    } 
                    $user = $feedses->editCustomer($data, $customer_id);
                    if($user) 
                    {  
                        $status = 200;
                        $items = $user;
                    }
                }
            }
            else if($act == 'uploadKYC')
            {
                $file = $this->params()->fromPost('file',  ''); 
                $type = $this->params()->fromPost('type',  'IDENTITY_PROOF');
                $mangopay_id = $this->params()->fromPost('mangopay_id', 0);
                //echo $file; exit;      
                if(!empty($type) && !empty($mangopay_id) && !empty($view->id) && !empty($file))  
                {   
                    $uploadKYC = $feedses->uploadKYC($mangopay_id, $type, $file); 
                    //print_r($uploadKYC); exit;        
                    $dataKYC = json_decode($uploadKYC); 
                    if($dataKYC->Status==200 && !empty($dataKYC->result->Id)){  
                        $data = array(     
                            'KYCDocumentId'=>$dataKYC->result->Id,  
                            'last_update'=>date("Y-m-d H:i:s") 
                        );  
                        $user = $feedses->editCustomer($data, $view->id);
                    }  
                     
                    $status = $dataKYC->Status;
                    $items = $dataKYC->result; 
                } 
            } 
            else if($act == 'wallet')
            {
                $user = $feedses->getUserWallets($view->id);
                if(!empty($user))  
                {   
                    $status = 200; 
                    $items = $user; 
                }
            }
            else
            {
                $user = $feedses->getUser($view->id, 1);
                if($user)
                {
                    $status = 200;
                    $items = $user;
                }
            }
        }
        
        $data = array(
                        'status' => $status,
                        'items' => $items
                    );
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
################################################################################   
    public function loginAction()
    {
        $status = 404;
        $view = $this->basic();
        $items = $this->een; 
        $email = $this->params()->fromPost('email', ''); 
        $upassword = $this->params()->fromPost('upassword', '');
        $facebook_id = $this->params()->fromPost('facebook_id', ''); 
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $this->login = $feedses->getLogin($view->username, $view->password); 
        if($this->login > 0) 
        {
            //echo $email; exit;
            $user = $feedses->getUlogin($facebook_id, $email, $upassword); //exit;
            if($user)
            {
                $status = 200; 
                $items = $user;
            }
        }
        $data = array(
                        'status' => $status,
                        'items' => $items
                    );
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
################################################################################   
    public function contentAction()
    {
        $status = 404;
        $view = $this->basic();
        $items = '';
        $total = 0;
        if($view->lang == 'th')
        {
            $item = $this->eth;
        }
        else
        {
            $item = $this->een;
        }
        
        
        if(!empty($view->username) && !empty($view->password))
        {
            $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
            $this->login = $feedses->getLogin($view->username, $view->password);
            if($this->login > 0)
            {
                if($view->id)
                {
                    if($view->for == 'view')
                    {
                        $data = $feedses->cView();
                        if($data) $total = 1;
                    }
                    else
                    {
                        $data = $feedses->getDetail();
                        if($data) $total = 1;
                    }
                }
                else if($view->act=='all') 
                {
                    $total = $feedses->getTotal();
                    if(!empty($total))
                    {
                        $data = $feedses->getListAll();
                    }
                }
                else
                {
                    $total = $feedses->getTotal();
                    if($total > 0)
                    {
                        $data = $feedses->getList();
                    }
                }
                
                if($data)
                {
                    $status = 200;
                    $item = $data;
                }
            }
        }
        
        $data = array(
                        'status' => $status,
                        'total' => $total,
                        'items' => $item
                    );
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
################################################################################   
    public function makeContractAction()
    {
        $status = 404;
        $view = $this->basic();
        $items = $this->een;
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $this->login = $feedses->getLogin($view->username, $view->password);
        if($this->login > 0)
        {
            $act = $this->params()->fromQuery('act', 1);
            $user_id = $this->params()->fromPost('user_id', '');
            if($act == 1)
            { //client
                $supplier_id = $this->params()->fromPost('supplier_id', '');
                $total_price = $this->params()->fromPost('total_price', '');
                $start_date = $this->params()->fromPost('start_date', '');
                $end_date = $this->params()->fromPost('end_date', '');
                $serial_number = $this->params()->fromPost('serial_number', '');
                $contract_name = $this->params()->fromPost('contract_name', '');
                $contract_company = $this->params()->fromPost('contract_company', '');
                $company_address = $this->params()->fromPost('company_address', '');
                $contract_phone = $this->params()->fromPost('contract_phone', '');
                $contract_landline_phone = $this->params()->fromPost('contract_landline_phone', '');
                $contract_email = $this->params()->fromPost('contract_email', '');
                $contract_img = $this->params()->fromPost('contract_img', '');
                $project_name = $this->params()->fromPost('project_name', '');
                if($user_id)
                {
                    $data = $feedses->makeContract1($user_id, $supplier_id, $total_price, $start_date, $end_date, $serial_number, $contract_name, $contract_company, $contract_phone, $contract_landline_phone, $contract_email, $contract_img, $company_address, '1', $project_name, '', '');
                    if($data)
                    {
                        $status = 200;
                        $items = 'Contract successful'; 
                        if($view->lang=='fr'){ 
                            $items = "Vous avez réussi à signer le contrat de transaction.";
                        } 
                    }
                }
                else
                {
                    $items = 'Please check user_id';
                    if($view->lang=='fr'){ 
                        $items = "S'il vous plaît vérifier l'ID utilisateur";
                    }
                }
            }
            else if($act == 2)
            { //supplier 
                $supplier_id = $this->params()->fromPost('supplier_id', '');
                $total_price = $this->params()->fromPost('total_price', '');
                $start_date = $this->params()->fromPost('start_date', '');
                $end_date = $this->params()->fromPost('end_date', '');
                $serial_number = $this->params()->fromPost('serial_number', '');
                $contract_name = $this->params()->fromPost('contract_name', '');
                $contract_company = $this->params()->fromPost('contract_company', '');
                $company_address = $this->params()->fromPost('company_address', '');
                $contract_phone = $this->params()->fromPost('contract_phone', '');
                $contract_landline_phone = $this->params()->fromPost('contract_landline_phone', '');
                $contract_email = $this->params()->fromPost('contract_email', '');
                $contract_img = $this->params()->fromPost('contract_img', '');
                $project_name = $this->params()->fromPost('project_name', '');
                $subject = $this->params()->fromPost('subject', '');
                $body = $this->params()->fromPost('body', '');
                if($supplier_id)
                {
                    $data = $feedses->makeContract1($user_id, $supplier_id, $total_price, $start_date, $end_date, $serial_number, $contract_name, $contract_company, $contract_phone, $contract_landline_phone, $contract_email, $contract_img, $company_address, '2', $project_name, $subject, $body);
                    if($data)
                    {
                        $status = 200;
                        $items = 'Contract successful'; 
                        if($view->lang=='fr'){ 
                            $items = "Vous avez réussi à signer le contrat de transaction.";
                        }
                    }
                }
                else
                {
                    $items = 'Please check supplier_id';
                    if($view->lang=='fr'){ 
                        $items = "S'il vous plaît vérifier l'ID utilisateur";
                    }
                }
            }
            else if($act == 3)
            { //client looking for supplier
                $total_price = $this->params()->fromPost('total_price', '');
                $start_date = $this->params()->fromPost('start_date', '');
                $end_date = $this->params()->fromPost('end_date', '');
                $project_name = $this->params()->fromPost('project_name', '');
                $contract_cover = $this->params()->fromPost('contract_cover', '');
                if($user_id)
                {
                    $data = $feedses->makeContract1($user_id, '0', $total_price, $start_date, $end_date, '', $project_name, '', '', '', '', $contract_cover, '', '3', '', '', '');
                    if($data)
                    {
                        $status = 200;
                        $items = 'Contract successful'; 
                        if($view->lang=='fr'){ 
                            $items = "Vous avez réussi à signer le contrat de transaction.";
                        }
                    }
                }
                else
                {
                    $items = 'Please check user_id';
                    if($view->lang=='fr'){  
                        $items = "S'il vous plaît vérifier l'ID utilisateur";
                    }
                }
            }
            else
            { //new flow - for all goods
                $request = $this->params()->fromPost('request', '1');
                $buyer_id = $this->params()->fromPost('buyer_id', '0');
                $seller_id = $this->params()->fromPost('seller_id', '0');
                $total_price = $this->params()->fromPost('total_price', '');
                $project_name = $this->params()->fromPost('project_name', '');
                $start_date = $this->params()->fromPost('start_date', '');
                $end_date = $this->params()->fromPost('end_date', '');
                $note = $this->params()->fromPost('note', '');
                $contract_number = $this->params()->fromPost('contract_number', '');
                $buyer_name = $this->params()->fromPost('buyer_name', '');
                $buyer_email = $this->params()->fromPost('buyer_email', '');
                $buyer_number = $this->params()->fromPost('buyer_number', '');
                $seller_name = $this->params()->fromPost('seller_name', '');
                $seller_email = $this->params()->fromPost('seller_email', '');
                $seller_number = $this->params()->fromPost('seller_number', '');
                $who_pay_fee = $this->params()->fromPost('who_pay_fee', '0');
                $email_subject = $this->params()->fromPost('email_subject', '');
                $email_body = $this->params()->fromPost('email_body', '');
                $zenovly_type = $this->params()->fromPost('zenovly_type', '1');
                $contract_img = $this->params()->fromPost('contract_img', '');
                $contract_img2 = $this->params()->fromPost('contract_img2', '');
                $contract_img3 = $this->params()->fromPost('contract_img3', '');
                $contract_img4 = $this->params()->fromPost('contract_img4', '');
                $contract_img5 = $this->params()->fromPost('contract_img5', '');
                $contract_img6 = $this->params()->fromPost('contract_img6', '');
                $contract_img7 = $this->params()->fromPost('contract_img7', '');
                $contract_img8 = $this->params()->fromPost('contract_img8', '');
                $contract_img9 = $this->params()->fromPost('contract_img9', '');
                
                $above_name = $this->params()->fromPost('above_name', '1');
                $company = $this->params()->fromPost('company', 'no');
                //$above_name = $this->params()->fromPost('above_name', '');
                
                if($total_price) $data = $feedses->zenovlyContract($zenovly_type, $request, $buyer_id, $seller_id, $total_price, $project_name, $start_date, $end_date, $contract_number, $buyer_name, $buyer_email, $buyer_number, $seller_name, $seller_email, $seller_number, $who_pay_fee, $email_subject, $email_body, $note, $contract_img, $contract_img2, $contract_img3, $contract_img4, $contract_img5, $contract_img6, $contract_img7, $contract_img8, $contract_img9, $above_name, $company);
                if(@$data)
                {
                    $status = 200;
                    $items = 'Contract successful'; 
                    if($view->lang=='fr'){ 
                        $items = "Vous avez réussi à signer le contrat de transaction.";
                    } 
                }
            }
        }
        $data = array(
                        'status' => $status,
                        'items' => $items
                    );
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
################################################################################   
    public function contractAction()
    {
        $status = 404;
        $view = $this->basic();
        $items = '';
        $total = 1;
        if($view->lang == 'th')
        {
            $item = $this->eth;
        }
        else
        {
            $item = $this->een;
        }
        $astatus = $this->params()->fromQuery('status', 'all');
        if(!empty($view->username) && !empty($view->password))
        {
            $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
            $this->login = $feedses->getLogin($view->username, $view->password);
            if($this->login > 0)
            {
                $act = $this->params()->fromQuery('act', 'detail');
                if($act == 'detail')
                {
                    if($view->id)
                    {
                        $data = $feedses->getCdetail('0');
                        if($data)
                        {
                            $status = 200;
                            $item = $data;
                        }
                    }
                }
                else if($act == 'consumer')
                {
                    $item = "Sorry! seem you don't have any contract right now.";
                    if($view->lang=='fr'){ 
                        $item = "Pardon ! Il ne nous semble que vous avez un contrat actif en ce moment";
                    } 
                    if($view->id)
                    {
                        $total = $feedses->getTCL('c', $astatus);
                        $data = $feedses->getCL('c', $astatus);
                        if($data)
                        {
                            $status = 200;
                            $item = $data;
                        }
                    }
                }
                else if($act == 'supplier')
                {
                    $item = "Sorry! seem you don't have any contract right now.";
                    if($view->lang=='fr'){ 
                        $item = "Pardon ! Il ne nous semble que vous avez un contrat actif en ce moment";
                    }
                    if($view->id)
                    {
                        $total = $feedses->getTCL('s', $astatus);
                        $data = $feedses->getCL('s', $astatus);
                        if($data)
                        {
                            $status = 200;
                            $item = $data;
                        }
                    }
                }
                else if($act == 'accept')
                {
                    $buyer_id = $this->params()->fromPost('buyer_id', '');
                    $seller_id = $this->params()->fromPost('seller_id', '');
                    if(!empty($buyer_id) && !empty($seller_id))
                    {
                        $data = $feedses->editZdetail($buyer_id, $seller_id);
                        $status = $data['status'];
                        $item = $data['item'];
                    }
                    else
                    {
                        $item = 'Please check supplier_id!';
                        if($view->lang=='fr'){ 
                            $item = "S'il vous plaît vérifier carte d'identité professionnelle";
                        }
                    }
                }
                else if($act == 'zdetail')
                {
                    if($view->id)
                    {
                        $data = $feedses->getZdetail('0');
                        if($data)
                        {
                            $status = 200;
                            $item = $data;
                        }
                    }
                }
                else if($act == 'seller')
                {
                    $item = "Sorry! seem you don't have any contract right now.";
                    if($view->lang=='fr'){ 
                        $item = "Pardon ! Il ne nous semble que vous avez un contrat actif en ce moment";
                    }
                    if($view->id)
                    {
                        $total = $feedses->getTZC('s', $astatus);
                        $data = $feedses->getZC('s', $astatus);
                        if($data)
                        {
                            $status = 200;
                            $item = $data;
                        }
                    }
                }
                else if($act == 'buyer')
                {
                    $item = "Sorry! seem you don't have any contract right now.";
                    if($view->lang=='fr'){ 
                        $item = "Pardon ! Il ne nous semble que vous avez un contrat actif en ce moment";
                    }
                    if($view->id)
                    {
                        $total = $feedses->getTZC('b', $astatus);
                        $data = $feedses->getZC('b', $astatus);
                        if($data)
                        {
                            $status = 200;
                            $item = $data;
                        }
                    }
                }
                else if($act == 'addTracking')
                {
                    $shipping_tracking_number = $this->params()->fromQuery('shipping_tracking_number', '');
                    $user_id = $this->params()->fromQuery('user_id', '');
                    if($view->id && $shipping_tracking_number && $user_id)
                    {
                        $data = $feedses->getZdetail('0');
                        $seller_id = $data['seller_id'];
                        if($user_id == $seller_id)
                        {
                            $sql = $this->adapter->query("UPDATE zenovly SET shipping_tracking_number = '$shipping_tracking_number', last_update = NOW() WHERE id = '$view->id'");
                            if($sql->execute())
                            {
                                $feedses->sendAddTrackingEmail($view->id);
                                $status = 200;
                                $item = $feedses->getZdetail('1');
                            }
                        }
                        else
                        {
                            $status = 404;
                            $total = 0;
                            $item = 'user_id != seller_id';
                        }
                    }
                }
                else
                {
                    $supplier_id = $this->params()->fromQuery('supplier_id', 'supplier_id');
                    if(!empty($supplier_id))
                    {
                        $data = $feedses->editCdetail($supplier_id);
                        $status = $data['status'];
                        $item = $data['item'];
                    }
                    else
                    {
                        $item = 'Please check supplier_id!';
                        if($view->lang=='fr'){ 
                            $item = "S'il vous plaît vérifier carte d'identité professionnelle";
                        }
                    }
                }
            }
        }
        
        $data = array(
                        'status' => $status,
                        'total' => $total,
                        'items' => $item
                    );
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
################################################################################   
    public function payAction()
    {
        $status = 404;
        $view = $this->basic();
        $items = $this->een;
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $this->login = $feedses->getLogin($view->username, $view->password);
        
        $wid = $this->params()->fromQuery('wid', '');
        $amount = $this->params()->fromQuery('amount', '');
        $returnURL = $this->params()->fromQuery('returnURL', '');
        $zenovly_id = $this->params()->fromQuery('zenovly_id', '');
        $payInType = $this->params()->fromQuery('payInType', 'card');
        $fee = $this->params()->fromQuery('fee', 0);
        
        $data = ['status'=>400, 'items'=>$this->een,'RedirectURL'=>$returnURL.'&error='.$this->een];  
         
        if($this->login > 0) 
        { 
            if($wid && $amount && $view->id && $view->lang && $returnURL && $zenovly_id)
            { 
                $urlRD = $this->mangopayAPI.'act=payInCW&wid='.$wid.'&id='.$view->id.'&zenovly_id='.$zenovly_id.'&amount='.$amount.'&returnURL='.$returnURL;
                $mangopay_url = $this->mangopayAPI.'act=payInCW&wid='.$wid.'&id='.$view->id.'&zenovly_id='.$zenovly_id.'&amount='.$amount.'&payInType='.$payInType.'&fee='.$fee.'&lang='.$view->lang.'&returnURL='.$returnURL;
                //echo $mangopay_url; exit; 
                $data_mangopay = $feedses->getService($mangopay_url); 
                //print_r($data_mangopay); exit;
                $result = json_decode($data_mangopay);   
                if($result->Status=='CREATED'){    
                    //echo "UPDATE zenovly SET payIn_id = ".$result->Id.", last_update = NOW() WHERE id = ".$zenovly_id; 
                    $sql = $this->adapter->query("UPDATE zenovly SET payIn_id = ".$result->Id.", last_update = NOW() WHERE id = ".$zenovly_id);
                    if($sql->execute())
                    { 
                        $data = ['status'=>200, 'items'=>$result,'RedirectURL'=>$result->ExecutionDetails->RedirectURL];    
                    }
                }else{ 
                    $data = ['status'=>$result->ResultCode, 'items'=>$result,'RedirectURL'=>$returnURL.'&error='.$result->ResultMessage];
                }/*
                echo "<pre>";
                print_r($data);  
                echo "</pre>";
                exit;     */        
                //header('Location: '.$this->mangopayAPI.'act=payInCW&wid='.$wid.'&id='.$view->id.'&zenovly_id='.$zenovly_id.'&amount='.$amount.'&payInType='.$payInType.'&returnURL='.$returnURL);
                //exit();     
            }
        }  
        /*
        $data = array(
                        'status' => $status,
                        'items' => $items
                    ); */
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
################################################################################   
    public function payrsAction()
    {
        
        $view = $this->basic();
        $status = 404;
        $items = $this->een; 
        
        $rs = $this->params()->fromQuery('rs', '');
        if($view->id && ($rs == 'Success'))
        { 
            $sql = $this->adapter->query("UPDATE zenovly SET status = '5', last_update = NOW() WHERE id = '$view->id'");
            if($sql->execute())
            {
                $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
                $rs = $feedses->sendPaidEmail($view->id);
                if($rs){    
                    $status = 200;  
                    $items = 'done'; 
                    if($view->lang=='fr'){  
                        $items = "Terminé";
                    }
                }
               
            }
        }
        $data = array(
                        'status' => $status,
                        'items' => $items
                    );
        echo $this->makeJSON($data);
        $view->setTerminal(true);  
        return $view;
    }
    
################################################################################   
    public function doneAction()
    {
        $status = 404;
        $view = $this->basic();
        $items = $this->een;
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $this->login = $feedses->getLogin($view->username, $view->password);
        if($this->login > 0)
        {
            $detail = $feedses->getZdetail('1'); 
            //echo $detail['buyer_id']; exit;
            
            $zstatus = $detail['status'];
            if($zstatus == 5)
            {
                $sstatus = $this->params()->fromQuery('sstatus', '');
                if( ($sstatus == 3) && $view->id)
                {
                    $buyer = $feedses->getUser($detail['buyer_id'], 0); 
                    $seller = $feedses->getUser($detail['seller_id'], 0);
                    $detail['buyer_detail'] = $buyer;
                    $detail['seller_detail'] = $seller;
                    //$items = $detail;  
                    //0=buyer,1=seller,2=5050
                    if($detail['who_pay_fee']==2){
                        $fee = $detail['pay_price']-$detail['total_price'];
                        $fee = ($fee/2); 
                        $amount = $detail['total_price']-$fee;  
                    }else if($detail['who_pay_fee']==1){ // seller
                        $fee = $detail['pay_price']-$detail['transfer_price']; 
                        $amount = $detail['total_price']-$fee;  
                    }else{ // buyer 
                        $fee = $detail['pay_price']-$detail['total_price'];
                        $amount = $detail['total_price'];  
                    }    
                    
                    $AuthorId = $buyer['mangopay_id'];
                    $CreditedUserId = $seller['mangopay_id'];
                    $Amount = $amount;
                    $wallet = $Amount;
                    $Fee = 0; 
                    
                    /*
                    if($detail['who_pay_fee']==2){
                        $Fee = $fee;  
                        $wallet = $wallet - $Fee; 
                    }  
                    */   
                    $DebitedWalletId = $buyer['mangopay_wallet'];
                    $CreditedWalletId = $seller['mangopay_wallet'];
                     
                    $Tag = 'Contract No. '.$view->id;
                    
                    $seller_id = $detail['seller_id'];
                    $Wallet_log['user_id'] = $seller_id;
                    $Wallet_log['contract_id'] = $view->id;
                    $Wallet_log['amount'] = $wallet; 
                    
                    /*  
                    print_r([$AuthorId, $CreditedUserId, $Amount, $Fee, $DebitedWalletId, $CreditedWalletId, $Tag]); 
                    exit;   
                    */     
                    $transfers_json =$feedses->transfers($AuthorId, $CreditedUserId, $Amount, $Fee, $DebitedWalletId, $CreditedWalletId, $Tag);
                    $transfers = json_decode($transfers_json);
                      
                    if($transfers->Status=='SUCCEEDED'){
                           
                        $view_transfer_json = $feedses->viewTransfers($transfers->Id);
                        $view_transfer = json_decode($view_transfer_json);
                          
                        if($view_transfer->Status=='SUCCEEDED'){
                            
                            $sql = $this->adapter->query("UPDATE zenovly SET status = '3', transfer_id = ".$transfers->Id.", last_update = NOW() WHERE id = '$view->id'");
                            if($sql->execute())
                            {        
                                //done
                                $status = 200;
                                $items = 'done';
                                if($view->lang=='fr'){  
                                    $items = "Terminé";
                                }  
                                
                                $Wallet_log['result_id'] = $transfers->Id; 
                                 
                                //$result = $view_transfer->ResultCode.'-'.$view_transfer->ResultCode
                                
                                $feedses->setWalletsLog($Wallet_log, $seller_id, $wallet, 1, $view_transfer->Status); 
                                $feedses->sendTZmail($view->id);
                            } 
                            
                        }else{
                            
                            $status = $view_transfer->ResultCode;  
                            $items = $view_transfer->ResultMessage;
                             
                        }
                    }else{ 
                        
                        $status = $transfers->ResultCode;  
                        $items = $transfers->ResultMessage;
                        
                    }
                   
                }
                else if( ($sstatus == 4) && $view->id)
                {//did not get item(or service)
                    $sql = $this->adapter->query("UPDATE zenovly SET status = '4', last_update = NOW() WHERE id = '$view->id'");
                    if($sql->execute())
                    {
                        $status = 200;
                        $items = 'done'; 
                        if($view->lang=='fr'){  
                            $items = "Terminé";
                        }
                    }
                }
            }
        }
        $data = array(
                        'status' => $status,
                        'items' => $items
                    );
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
################################################################################   
    public function addbankAction()
    {
        $status = 404;
        $items = $this->een;
        try
        {
            $view = $this->basic();
            $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
            $this->login = $feedses->getLogin($view->username, $view->password);
            if($this->login > 0)
            { 
                $mangopay_id = $this->params()->fromQuery('mangopay_id', '');
                $data = $this->params()->fromPost('data', []); 
                //print_r([$mangopay_id,$data]);exit;
                /*
                $iban = $this->params()->fromQuery('iban', '');
                $bic = $this->params()->fromQuery('bic', '');//OPTIONAL
                $uname = $this->params()->fromQuery('uname', '');
                $address = $this->params()->fromQuery('address', ''); //OPTIONAL 
                */
                
                if(!empty($mangopay_id) && !empty($data))  
                {
                    $id = $feedses->addBank($mangopay_id, $data); 
                   
                    if(!empty($id['id']) && $id['status']==200)
                    { 
                        $status = 200;
                        $items = 'Added successfully';
                        if($view->lang=='fr'){  
                            $items = "Ajouté avec succè";
                        }
                    }else{ 
                        $status = $id['status']; 
                        $items = $id['result']; 
                    }
                }  
            }
        }
       	catch (Zend_Exception $e)
    	{
       		$cu = 0;
    	}
        $data = array(
                        'status' => $status,
                        'items' => $items
                    );
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
################################################################################   
    public function payoutAction()
    {
        $status = 404;
        $view = $this->basic();
        $items = $this->een;
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $this->login = $feedses->getLogin($view->username, $view->password);
        if($this->login > 0)
        {
            $cid = $this->params()->fromQuery('contract_id', ''); 
            $mangopay_id = $this->params()->fromQuery('mangopay_id', '');
            $walletId = $this->params()->fromQuery('walletId', '');
            $amount = $this->params()->fromQuery('amount', '');
            $bankID = $this->params()->fromQuery('bankID', ''); 
            $name = $this->params()->fromQuery('name', '');
            $email = $this->params()->fromQuery('email', '');
            $user_id = $this->params()->fromQuery('user_id', 0);  
            //echo $mangopay_id; exit; 
            if($view->act=='payoutByuser' && !empty($mangopay_id) && !empty($walletId) && !empty($amount) &!empty($bankID) &!empty($name) && !empty($email) && !empty($user_id)){
                
                $user_info = $feedses->getUserInfoByMID($mangopay_id); 
                
                if($amount<=$user_info['wallet']){     
                    
                    
                    $result = $feedses->payOut($mangopay_id, $walletId, $amount, $bankID);
                    $payout = json_decode($result); 
                    
                    //print_r($payout->Id); exit; 
                    //$payout = json_decode(json_encode(array("Id"=>0))); 
                   // $payout->Id = 59873637; 
                    
                    sleep(1);   
                    
                    if(!empty($payout->Id)) 
                    {   
                        
                        $viewPayout_json = $feedses->viewPayout($payout->Id);
                        //print_r($viewPayout_json); exit;  
                         
                        $viewPayout = json_decode($viewPayout_json); 
                       
                        
                        if(!empty($viewPayout->Id)){    
                             
                            //print_r($viewPayout); exit;     
                             
                            $seller_id = $user_info['id'];
                            $Wallet_log['user_id'] = $seller_id; 
                            $Wallet_log['amount'] = $amount; 
                            $Wallet_log['contract_id'] = 0;  
                            $Wallet_log['result_id'] = $payout->Id;  
                            $feedses->setWalletsLog($Wallet_log, $seller_id, $amount, 2, $viewPayout->Status); 
                            $status = 200;       
                            $items = 'Payout created';
                            if($view->lang=='fr'){   
                                $items = "Paiement créé";
                            } 
                            $feedses->sendPayOutEmailByUser($name, $email, $items); 
                        
                        }else{  
                            
                            $status = $viewPayout->ResultCode; 
                            $items = $viewPayout->ResultMessage;
                            if(empty($items)){ 
                                $status = 404;  
                                $items = $this->een;
                            }   
                        } 
                        //$feedses->sendPayOutEmailByUser($name, $email); 
                         
                    }else{ 
                        
                        $status = $payout->ResultCode; 
                        $items = $payout->ResultMessage; 
                        if(empty($items)){
                            $status = 404;
                            $items = $this->een;
                        }
                    } 
                }else{  
                    $status = 404; 
                    $items = 'Unsufficient wallet balance';
                    if($view->lang=='fr'){     
                        $items = "Solde de portefeuille insuffisant"; 
                    }  
                }
            } 
            else if($mangopay_id && $walletId && $amount && $bankID && $cid)
            {
                $result = $feedses->payOut($mangopay_id, $walletId, $amount, $bankID);
                //exit;
                $payout = json_decode($result);
                if($payout->Status=='CREATED')
                {  
                    $sql = $this->adapter->query("UPDATE zenovly SET status = '8', payOut_id=".$payout->Id.", last_update = NOW() WHERE id = ".$cid);  
                    $sql->execute();   
                    $status = 200; 
                    $items = 'payout successfully'; 
                    if($view->lang=='fr'){   
                        $items = "PayOut avec succès";
                    } 
                    $feedses->sendPayOutEmail($cid);  
                }else{ 
                    $status = $payout->ResultCode; 
                    $items = $payout->ResultMessage; 
                } 
                
            }else if(!empty($user_id) && $view->act=='payoutList'){
                
                $result = $feedses->getPayoutList($user_id);
                $data['status'] = 200;
                $data['items'] = $result['results'];
                $data['total'] = $result['total'];
                echo $this->makeJSON($data); 
                exit;
            }
        }
        $data = array(
                        'status' => $status,
                        'items' => $items
                    );
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
    
    ################################################################################   
    public function refundAction()
    {
        $status = 404;
        $view = $this->basic();
        $items = $this->een; 
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $this->login = $feedses->getLogin($view->username, $view->password);
        if($this->login > 0)
        {
            $cid = $this->params()->fromQuery('contract_id', ''); 
            $mangopay_id = $this->params()->fromQuery('mangopay_id', '');
            $payin_id = $this->params()->fromQuery('payin_id', ''); 
            $amount = $this->params()->fromQuery('amount', 0);
            $fee = $this->params()->fromQuery('fee', 0);
            if($mangopay_id && $payin_id && $amount && $cid)
            {
                $result = $feedses->refund($mangopay_id, $payin_id, $amount, $fee);
                //print_r($result);exit;  
                
                $Refund = json_decode($result);  
                  
                if($Refund->Status=='CREATED')
                {     
                    $sql = $this->adapter->query("UPDATE zenovly SET status = '7', refund_id=".$Refund->Id.", last_update = NOW() WHERE id = ".$cid);  
                    $sql->execute();    
                    $status = 200;  
                    $items = $Refund->Message;
                    /*
                    $items = 'Refund successfully';
                    if($view->lang=='fr'){  
                        $items = "Remboursement avec succès";
                    }     */ 
                    //$feedses->sendRefundEmail($cid);  
                }else{ 
                    $items = $Refund->Message;
                } 
            }
        }
         
        $data = array(
                        'status' => $status,
                        'items' => $items
                    ); 
                  
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
    
    public function refundemailAction()
    {
        $status = 404;
        $view = $this->basic();
        $items = $this->een; 
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $this->login = $feedses->getLogin($view->username, $view->password);
        
        if($this->login > 0)
        {
            $cid = $this->params()->fromQuery('contract_id', ''); 
            $buyer_id = $this->params()->fromQuery('buyer_id', '');
            if($cid && $buyer_id)
            {    
                $feedses->sendRefundEmail($cid, $buyer_id);
                $status = 200; 
                $items = 'Send email to buyer.';
            }     
        } 
         
        $data = array(
                        'status' => $status,
                        'items' => $items
                    ); 
                  
        echo $this->makeJSON($data);//exit;
        $view->setTerminal(true);
        return $view;
    }
    
################################################################################   
    public function cancelledAction()
    {
        $view = $this->basic();
        $sql = $this->adapter->query("SELECT id, start_date FROM `zenovly` WHERE status = '0' ORDER BY last_update ASC LIMIT 0, 10");
        $results = $sql->execute();
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results);
		$data = $data->toArray(); //print_r($data);
		
		$today = date('Y-m-d');
        foreach($data AS $item)
        {
            $id = $item['id'];
            $start_date = $item['start_date'];
            $day7 = date('Y-m-d', strtotime($start_date . "+7 days"));
            if(strtotime($day7) <= strtotime($today))
            {
                $sql2 = $this->adapter->query("UPDATE zenovly SET status = '1', last_update = NOW() WHERE id = '$id'");
                if($sql2->execute())
                {
                    echo $start_date; echo '<br>';
                }
            }
        }
        return $view;
    }
################################################################################   
    public function buyerreminderAction()
    {
        $view = $this->basic();
        $sql = $this->adapter->query("SELECT buyer_name, buyer_email, start_date FROM `zenovly` WHERE status IN('0', '6') AND project_type = '1' ORDER BY last_update ASC LIMIT 0, 100");
        $results = $sql->execute();
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results);
		$data = $data->toArray(); //print_r($data);
		
	    $txt = file_get_contents($this->webURL.'/email/buyerreminder.html');
		$today = date('Y-m-d');
        foreach($data AS $item)
        {
            if($item['buyer_name'] && $item['buyer_email'])
            {
                $day10 = date('Y-m-d', strtotime('-10 days', strtotime($item['start_date'])));
                $day5 = date('Y-m-d', strtotime('-5 days', strtotime($item['start_date'])));
                $day3 = date('Y-m-d', strtotime('-3 days', strtotime($item['start_date'])));
                $day2 = date('Y-m-d', strtotime('-2 days', strtotime($item['start_date'])));
                $day1 = date('Y-m-d', strtotime('-1 days', strtotime($item['start_date'])));
                
                if( ($today == $day10) || ($today == $day5) || ($today == $day3) || ($today == $day2) || ($today == $day1) )
                {
                    $token = base64_encode('zenovly'.$item['id']);
                    $token = str_replace ( '=', 'gpsn', $token);
                    $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/';
    			    $link = '<a href="'.$link.'" target="_blank">Click here</a>';
                    $txt = preg_replace(array('/{name}/', '/{link}/'), array($item['buyer_name'], $link), $txt);
                    $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
                    $feedses->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', $item['buyer_name'], $item['buyer_email'], $txt, '', '');
                    echo $item['buyer_name'];
                }
            }
        }
        
    }
################################################################################   
    public function sellerreminderAction()
    {
        $view = $this->basic();
        $sql = $this->adapter->query("SELECT seller_name, seller_email, start_date FROM `zenovly` WHERE status = '5' AND project_type = '1' AND shipping_tracking_number = '' ORDER BY last_update ASC LIMIT 0, 100");
        $results = $sql->execute();
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results);
		$data = $data->toArray(); //print_r($data);
		
	    $txt = file_get_contents($this->webURL.'/email/sellerreminder.html');
		$today = date('Y-m-d');
        foreach($data AS $item)
        {
            if($item['seller_name'] && $item['seller_email'])
            {
                $day10 = date('Y-m-d', strtotime('-10 days', strtotime($item['start_date'])));
                $day5 = date('Y-m-d', strtotime('-5 days', strtotime($item['start_date'])));
                $day3 = date('Y-m-d', strtotime('-3 days', strtotime($item['start_date'])));
                $day2 = date('Y-m-d', strtotime('-2 days', strtotime($item['start_date'])));
                $day1 = date('Y-m-d', strtotime('-1 days', strtotime($item['start_date'])));
                
                if( ($today == $day10) || ($today == $day5) || ($today == $day3) || ($today == $day2) || ($today == $day1) )
                {
                    $token = base64_encode('zenovly'.$item['id']);
                    $token = str_replace ( '=', 'gpsn', $token);
                    $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/';
    			    $link = '<a href="'.$link.'" target="_blank">"I ship the good/product"</a>';
                    $txt = preg_replace(array('/{name}/', '/{link}/'), array($item['seller_name'], $link), $txt);
                    $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
                    $feedses->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', $item['seller_name'], $item['seller_email'], $txt, '', '');
                    echo $item['seller_name'];
                }
            }
        }
        
    }
################################################################################   
    public function mailAction()
    {
        $email = $this->params()->fromPost('email', '');
        $name = $this->params()->fromPost('name', '');
        $subject = $this->params()->fromPost('subject', '');
        $msg = $this->params()->fromPost('msg', '');
        $status = 404;
        $view = $this->basic();
        $items = $this->een;
        if($this->emailfromat($email) != 1)
        {
            $items = 'Please check your email fromat!';
            if($view->lang=='fr'){  
                $items = "S'il vous plaît vérifier votre format e-mail!";
            }
        }
        else 
        {
            if($email && $name && $subject && $msg)
            {
                $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
                $this->login = $feedses->getLogin($view->username, $view->password);
                if($this->login > 0)
                {
                    try
                    {
                        $feedses->sendMail($subject, $name, 'contact@wezenit.com', 'Wezenit SAS', 'contact@wezenit.com', $msg, '', '');
                        $status = 200;
                        $items = 'Email sent successfully.';
                        if($view->lang=='fr'){  
                            $items = "E-mail envoyé avec succès.!";
                        }
                    }catch (\Exception $e) {}
                }
            }
        }
        $data = array(
                        'status' => $status,
                        'items' => $items
                    );
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
##########################################################################
	public function emailfromat($email)
 	{	
    	try
        {
			$cu = 0;
			if (!filter_var($email, FILTER_VALIDATE_EMAIL))
			{
  				$cu = 0; 
			}
			else
			{
				$cu = 1;
			}
			return($cu);
       	}
       	catch (Zend_Exception $e)
    	{
       		$cu = 0;
    	}
	}
	
################################################################################   
    public function searchAction()
    {
        $status = 404;
        $total = 0;
        $view = $this->basic();
        $items = $this->een;
        $keyword = $this->params()->fromQuery('keyword', '');
        $keyword2 = $this->params()->fromPost('keyword', '');
        if(!empty($keyword2)){
            $keyword = $keyword2;
        } 
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $this->login = $feedses->getLogin($view->username, $view->password);
        if($this->login > 0)
        { 
            if(!empty($keyword))  
            {  
                $total = (int)$feedses->getSearchTotal($keyword);
                if($total<=0){    
                   $items = 'No result'; 
                   $status = 400; 
                }else{ 
                   $items = $feedses->getSearch($keyword);
                   $status = 200; 
                }
            }else{
                $items = 'No keyword';
            } 
        } 
         
        $data = array(
                        'status' => $status,
                        'total' => $total,
                        'items' => $items
                    );
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }  
    
    
################################################################################   
    public function makeJSON($data)
    {
        $json = json_encode($data);
        return ($json);
    }
################################################################################  


    public function radioAction()    
    { 
        $status = 404;
        $rs = [];
        $total = 0;
        $view = $this->basic();
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $act = $this->params()->fromQuery('act', '');
        $station = $this->params()->fromQuery('ch', 7);
        $limit = $this->params()->fromQuery('limit', 20); 
        $sortby = $this->params()->fromQuery('sortby', 1);
        $genre = $this->params()->fromQuery('genre', 'all');
        $year = $this->params()->fromQuery('year', 'all');
        $aid = $this->params()->fromQuery('aid', 'all');
        
        $view->perpage = $limit;  
        $view->pageStart = 0;  
        if($view->page>1){ 
            $view->pageStart = ($view->perpage*($view->page-1)); 
        }
        $Songs = new Songs($this->adapter, $view->lang, $view->action, $view->id, $view->pageStart, $view->perpage); 
         //echo $act; exit;   
        if(empty($act))$act='songs';   
        if(empty($station))$station=7; 
        
        if(!empty($view->id)){ 
            $station=$view->id;   
        }
        
        try 
         { 
            //echo $act; exit; 
            if($act=='songs'){  
                 
                $items = [];
                $dataList = $Songs->getList('', $station, $sortby, $genre, $year, $aid);  
                if(!empty($dataList['total'])){
                    $rs = $dataList['data'];  
                    $total = (int)$dataList['total'];
                    $status = 200;  
                }
                
            }else if($act=='genreList'){  
                
                $status = 200; 
                $items = []; 
                $items = $Songs->getGenreList(); 
                $rs = $items;   
                $total = count($items);
                
            }else if($act=='yearList'){  
                
                $status = 200; 
                $items = []; 
                $items = $Songs->getYearList();  
                $rs = $items;    
                $total = count($items);
                
            }else if($act=='add'){   
                 
                $url_api = 'https://www.fip.fr/livemeta/'.$station; 
                $body = $feedses->getService($url_api); 
                $data = json_decode($body); 
                
                if(!empty($data->steps)){
                    
                    foreach($data->steps as $key=>$val){
                        
                        if(!empty($val->songId) && !empty($val->embedType) && $val->embedType=='song'){
                            
                            
                            $json_data = json_encode($val);
                            $sql = $this->adapter->query("SELECT COUNT(id) as C FROM `songs` WHERE songId = '".$val->songId."' LIMIT 1"); 
                            $result = $sql->execute();  
                            $row = $result->current();  
                             
                            if(!empty($row['C'])){ 
                                
                                $data = array(  
                                     'uuid' => $val->uuid, 
                                     'songs' => $json_data, 
                                     'title' => $val->title,
                                     'station_id'=>$station,
                                     'stepId'=>$val->stepId, 
                                     'songId'=>$val->songId
                                );
                                $Songs->edit($data, $val->songId); 
                                $rs[]='Update Song ID :'.$val->songId; 
                                
                            }else{ 
                                 
                                $author_id = 0;
                                if($val->authors){
                                    $author_id = $Songs->checkNameAuthor($val->authors);
                                    if($author_id==0){
                                        $author_id = $Songs->getNextAuthorId();
                                        $insert1 = array(  
                                             'id' => $author_id,
                                             'author'=> $val->authors,
                                             'createdate'=>date("Y-m-d H:i:s")
                                        );
                                        $Songs->addAuthor($insert1);
                                        
                                        /*
                                        $genre_id = $Songs->getNextGenreId();
                                        $insert2 = array(  
                                             'id' => $genre_id,
                                             'genres'=> $val->authors,
                                             'createdate'=>date("Y-m-d H:i:s")
                                        );
                                        $Songs->addGenre($insert2);
                                        */
                                    } 
                                } 
                                
                                  
                                $insert = array(  
                                     'uuid' => $val->uuid, 
                                     'songs' => $json_data, 
                                     'title' => $val->title,
                                     'station_id'=>$station,
                                     'stepId'=>$val->stepId, 
                                     'songId'=>$val->songId,
                                     'author_id'=>$author_id,
                                     'anneeEditionMusique'=>!empty($val->anneeEditionMusique)?$val->anneeEditionMusique:'',
                                     'createdate'=>date("Y-m-d H:i:s")
                                ); 
                                $Songs->add($insert);
                                $rs[]='Insert Song ID :'.$val->songId;
                            }
                        }
                    }  
                    
                    $this->addGenre(); 
                    $status = 200;
                    $total = count($rs);  
                    
                }else{  
                    $rs ='Not data station ID :'.$i; 
                }
            }
        }catch (\Exception $e) {  
            $status = 400;
            $rs = $e->getMessage(); 
        }
        
        $data = array(
                        'status' => $status,
                        'items' => $rs,
                        'total'=>$total
                    ); 
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
    
    
    public function authorAllAction()    
    {  
        
        $status = 404;
        $rs = [];
        $total = 0;
        $view = $this->basic();
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $act = $this->params()->fromQuery('act', '');
        $station = $this->params()->fromQuery('ch', 7);  
        $view->perpage = 20;  
        $view->pageStart = 0;  
        if($view->page>1){ 
            $view->pageStart = ($view->perpage*($view->page-1)); 
        }
        $Songs = new Songs($this->adapter, $view->lang, $view->action, $view->id, $view->pageStart, $view->perpage); 
         
        $sql = "SELECT songs, id
                FROM `songs` 
                WHERE author_id IS NULL OR author_id='' OR author_id=0
                ORDER BY id ASC 
                LIMIT 100";     
        //echo $sql; exit;  
        $query = $this->adapter->query($sql);
        $results = $query->execute(); 
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray();
        
        foreach($data as $key=>$val){
            
            $song = json_decode($val['songs']);
            
            echo "<pre>";   
            print_r($val['id'].' : '.$song->authors);   
            echo "</pre>"; 
            
            $author_id = 0;
            if($song->authors){
                
                $author_id = $Songs->checkNameAuthor($song->authors);
                if($author_id==0){  
                    $author_id = $Songs->getNextAuthorId();
                    $insert1 = array(  
                         'id' => $author_id, 
                         'author'=> $song->authors,
                         'createdate'=>date("Y-m-d H:i:s")
                    ); 
                    $Songs->addAuthor($insert1); 
                } 
                
                if($author_id && !empty($song->songId)){
                    
                    $data = array(  
                         'author_id'=>$author_id
                    );
                    
                    $Songs->edit($data, $song->songId);
                }
            }  
            
        }
        exit;
    }
    
    
    public function yearmusicAllAction()    
    {  
        
        $status = 404;
        $rs = [];
        $total = 0;
        $view = $this->basic();
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $act = $this->params()->fromQuery('act', '');
        $station = $this->params()->fromQuery('ch', 7);  
        $view->perpage = 20;  
        $view->pageStart = 0;  
        if($view->page>1){ 
            $view->pageStart = ($view->perpage*($view->page-1)); 
        }
        $Songs = new Songs($this->adapter, $view->lang, $view->action, $view->id, $view->pageStart, $view->perpage); 
         
        $sql = "SELECT songs, id
                FROM `songs` 
                WHERE anneeEditionMusique IS NULL OR anneeEditionMusique='' OR anneeEditionMusique=0
                ORDER BY id ASC 
                LIMIT 100";      
        //echo $sql; exit;      
        $query = $this->adapter->query($sql);
        $results = $query->execute(); 
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray();
        foreach($data as $key=>$val){
            
            $song = json_decode($val['songs']);
            
            $year = !empty($song->anneeEditionMusique)?$song->anneeEditionMusique:'';
            
            echo "<pre>";   
            print_r($val['id'].' : '.$year);   
            echo "</pre>"; 
             
            if($song->authors){ 
                 $data = array(  
                     'anneeEditionMusique'=>$year
                );
                
                $Songs->edit($data, $song->songId);
            }  
        }
        exit;
    }
    
    
    public function addGenreAction()    
    {  
        $this->addGenre();
        exit; 
    }
    
    public function addGenre()    
    {  
        
        $status = 404;
        $rs = [];
        $total = 0;
        $view = $this->basic();
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $act = $this->params()->fromQuery('act', '');
        $station = $this->params()->fromQuery('ch', 7);  
        $view->perpage = 20;  
        $view->pageStart = 0;  
        if($view->page>1){  
            $view->pageStart = ($view->perpage*($view->page-1)); 
        }
        $Songs = new Songs($this->adapter, $view->lang, $view->action, $view->id, $view->pageStart, $view->perpage); 
        $min = $Songs->getMinAuthorId();
        $sql = "SELECT author, id
                FROM `author` 
                WHERE status=0
                ORDER BY id ASC 
                LIMIT  1000";        
        //echo 'Start : '.($min-1); //exit;      
        $query = $this->adapter->query($sql);
        $results = $query->execute(); 
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray();
       
        //exit; 
        /*
        $url_api = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'];
        $body = $feedses->getService($url_api); 
        $data = json_decode($body); 
        */ 
       
        $spotify = $Songs->getSpotify(1);
        $refresh_token = !empty($spotify['refresh_token'])?$spotify['refresh_token']:'';
        
        foreach($data as $key=>$val){
            
            
            $url_api = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].'/spotify/search.php?q='.$val['author'].'&token='.$refresh_token;
            
            $body = $feedses->getService($url_api); 
            $data2 = json_decode($body); 
            if($data2->status==200){
                 
                if(!empty($data2->items[0]->genres)){ 
                     
                    $genres = trim(implode(",",$data2->items[0]->genres));
                    
                    
                    foreach($data2->items[0]->genres as $k=>$value){
                        
                        $genre = $value;
                        $author_id = $val['id']; 
                        $ch = $Songs->checkGenre($genre); 
                        
                        echo "<pre>";    
                        print_r($author_id.' : '.$genre);    
                        echo "</pre>"; 
                        
                        
                        if(empty($ch)){   
                            
                            $id = $Songs->getNextGenreId();
                            
                            $insert1 = array(  
                                 'id' => $id, 
                                 'genre'=>$genre, 
                                 //'author_id'=> $author_id,
                                 'createdate'=>date("Y-m-d H:i:s")
                            );  
                             
                            $Songs->addGenre($insert1);  
                             
                            $insert2 = array( 
                                 'genre_id'=>$id,
                                 'author_id'=> $author_id
                            );  
                            $Songs->addGenreAuthor($insert2);
                            
                        }else{ 
                             
                             $insert2 = array(  
                                 'genre_id'=>$ch,
                                 'author_id'=> $author_id
                            );  
                            $Songs->addGenreAuthor($insert2); 
                        } 
                        
                    } 
                    // Ok
                    $data3 = array(  
                         'status'=>1
                    ); 
                    $Songs->editAuthor($data3, $val['id']);
                    
                }else{
                    
                    // genre no data
                    $data3 = array(  
                         'status'=>2
                    ); 
                    $Songs->editAuthor($data3, $val['id']);
                    
                } 
                
                if($refresh_token!=$data2->refreshToken){
                    $Songs->editSpotify(array('refreshToken'=>$data2->refreshToken,'lastupdate'=>date("Y-m-d H:i:s")),1); 
                }
                
            }else{ 
                 
                // search no data
                $data3 = array(  
                     'status'=>3
                ); 
                $Songs->editAuthor($data3, $val['id']);
                
            } 
            
        }
        exit;
    }
    
    
    
    public function SpotifyByAuthorAction()    
    {  
        
        $status = 404;
        $rs = [];
        $total = 0;
        $view = $this->basic();
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $act = $this->params()->fromQuery('act', '');
        $station = $this->params()->fromQuery('ch', 7);  
        $view->perpage = 20;  
        $view->pageStart = 0;  
        if($view->page>1){  
            $view->pageStart = ($view->perpage*($view->page-1)); 
        }
        $Songs = new Songs($this->adapter, $view->lang, $view->action, $view->id, $view->pageStart, $view->perpage); 
        $min = $Songs->getMinAuthorId();
        $sql = "SELECT author, id
                FROM `author` 
                WHERE status2=0
                ORDER BY id ASC 
                LIMIT  500";              
        //echo 'Start : '.($min-1); //exit;      
        $query = $this->adapter->query($sql);
        $results = $query->execute(); 
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray();
        
        /*
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        exit;  */  
        /*
        $url_api = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'];
        $body = $feedses->getService($url_api); 
        $data = json_decode($body); 
        */ 
       
        $spotify = $Songs->getSpotify(1);
        $refresh_token = !empty($spotify['refresh_token'])?$spotify['refresh_token']:'';
        
        foreach($data as $key=>$val){
            
            
            $url_api = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].'/spotify/search.php?q='.$val['author'].'&token='.$refresh_token;
            
            $body = $feedses->getService($url_api); 
            $data2 = json_decode($body); 
            if($data2->status==200){
                
                $genre = !empty($data2->items[0]->genres)?$data2->items[0]->genres:'';
                $genres = !empty($genre)?trim(implode(",",$genre)):'';  
                $name = !empty($data2->items[0]->name)?$data2->items[0]->name:'';
                $popularity = !empty($data2->items[0]->popularity)?$data2->items[0]->popularity:0;
                $type = !empty($data2->items[0]->type)?$data2->items[0]->type:'';
                $uri = !empty($data2->items[0]->name)?$data2->items[0]->name:'';
                $spotify_id = !empty($data2->items[0]->id)?$data2->items[0]->id:'';
                $href = !empty($data2->items[0]->href)?$data2->items[0]->href:'';
                $external_urls = !empty($data2->items[0]->external_urls->spotify)?$data2->items[0]->external_urls->spotify:'';
                $followers = !empty($data2->items[0]->followers->total)?$data2->items[0]->followers->total:0;
                $image1 = !empty($data2->items[0]->images[0]->url)?$data2->items[0]->images[0]->url:'';
                $image2 = !empty($data2->items[0]->images[1]->url)?$data2->items[0]->images[1]->url:'';
                $image3 = !empty($data2->items[0]->images[2]->url)?$data2->items[0]->images[2]->url:'';
                 
                $data3 = array( 
                    'name'=>$name,
                    'popularity'=>$popularity,
                    'type'=>$type,
                    'uri'=>$uri, 
                    'spotify_id'=>$spotify_id,
                    'href'=>$href,
                    'genres'=>$genres,
                    'followers'=>$followers,
                    'external_urls'=>$external_urls,
                    'image1'=>$image1,
                    'image2'=>$image2, 
                    'image3'=>$image3, 
                    'data_json'=>json_encode($data2->items), 
                    'status2'=>1  
                ); 
                
                echo "<pre>"; 
                print_r($data3); 
                echo "</pre>";
                 
                $Songs->editAuthor($data3, $val['id']);
                if($refresh_token!=$data2->refreshToken){
                    $Songs->editSpotify(array('refreshToken'=>$data2->refreshToken,'lastupdate'=>date("Y-m-d H:i:s")),1); 
                }
                
            }else{ 
                $data3 = array(  
                    'status2'=>2  
                );
                $Songs->editAuthor($data3, $val['id']);
            } 
            
        } 
        exit;
    }
    
    
    public function walletsAction()
    {
        $status = 404;
        $view = $this->basic();
        $items = $this->een;
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $this->login = $feedses->getLogin($view->username, $view->password);
        if($this->login > 0)
        {
            $mangopay_id = $this->params()->fromQuery('mangopay_id', '');
            $mangopay_wallet = $this->params()->fromQuery('mangopay_wallet', '');
            
            if(!empty($walletId) &&  !empty($mangopay_wallet))
            { 
                echo 'ttt';exit;
                $result = $feedses->getWallets($mangopay_id, $mangopay_wallet); 
                exit; 
                //$items = json_decode($result); 
                
            }
        }
        
        $data = array(
                        'status' => $status,
                        'items' => $items
                    );
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
    
    
    public function checkpayoutAction()
    {
        $status = 404;
        $view = $this->basic();
        $items = $this->een;
        $feedses = new Api($this->adapter, $view->lang, $view->action, $view->id, $view->page, $view->for, $view->nocache);
        $this->login = $feedses->getLogin($view->username, $view->password);
        
        $limit = $this->params()->fromQuery('limit', 10);
        
        if($this->login > 0)
        {
            $payoutList = $feedses->getPayoutListType2($limit);
            $payout_results = [];
            if(!empty($payoutList)){
                foreach($payoutList as $key=>$val){
                    if(!empty($val['result_id'])){
                        $pay_rs = $feedses->viewPayout($val['result_id']);
                        $results = json_decode($pay_rs);
                        $val['results'] = $results; 
                        
                        
                        // if test 
                          
                        /*
                        if($val['amount']==1){ 
                            $results->Status = 'SUCCEEDED';  
                            $results->ResultMessage = 'Test';
                            $results->ResultCode = '0000000';
                        }  
                        */
                        
                        $str_rs = $results->ResultMessage.' : '.$results->ResultCode;
                        $text = '';
                        $subject = 'PayOut '.$results->ResultMessage;
                         
                        if($results->Status=='SUCCEEDED'){
                            
                            $feedses->updateStatusWalletsLog($val['id'], $val['user_id'], $results->Status, $val['amount'], 2, $str_rs);
                            
                        }  
                        
                        if($results->Status!='CREATED'){ 
                            
                            $text = 'Plaese create payout try againt !'; 
                            $feedses->updateStatusWalletsLog($val['id'], $val['user_id'], $results->Status, $val['amount'], 1, $str_rs);
                        } 
                        
                        if($results->Status!='CREATED'){
                            $mail = $feedses->sendEmailPayOutToBankSeller($val['user_id'], $subject, $text);
                            $payout_results[] = $val;
                        }
                    } 
                    
                } 
                  
                $status = 200;
                $items = $payout_results; 
                //exit; 
            }
        }
        
        $data = array(
                        'status' => $status,
                        'items' => $items
                    );
        echo $this->makeJSON($data);
        $view->setTerminal(true);
        return $view;
    }
    
    
    
}
