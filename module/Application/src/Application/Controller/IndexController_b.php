<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Application\Models\Feeds;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;
use Application\Models\Pagination; 

use Zend\Mvc\MvcEvent;

use Zend\Session\Container;
use Zend\Session\SessionManager;

#mail#
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
##AWS##
require 'vendor/aws/aws-autoloader.php';
use Aws\Ses\SesClient;
/*--s3--*/
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;

/*
$this->params()->fromPost('paramname');   // From POST
$this->params()->fromQuery('paramname');  // From GET
$this->params()->fromRoute('paramname');  // From RouteMatch
$this->params()->fromHeader('paramname'); // From header
$this->params()->fromFiles('paramname');
*/

/*  Setting SEO  
$view->SEO = [
                'title'=>'xxx',
                'keywords'=>'Wezenit,transactions',
                'description'=>'Wezenit, votre fournisseur de confiance pour toutes vos transactions',
                'url'=>'https://renovly.co',
                'image'=>'https://renovly.co/img/xxx.jpg',
                'domain'=>'renovly.co',
                'fb_app_id'=>'128202497713838', //fb:app_id  
                'locale'=>'fr_FR', //og:locale
                'creator'=>'@Renovly', //twitter:creator
             ];  
*/


class IndexController extends AbstractActionController
{
################################################################################ 
    public function __construct()
    {
        $this->cacheTime = 36000;
        $this->now = date("Y-m-d H:i:s");
        $this->eth = 'คุณไม่สามารถเข้าถึง API ได้ค่ะ!';
        $this->een = 'Sorry, you can not to access API!';
        $this->config = include __DIR__ . '../../../../config/module.config.php'; 
    }
    
################################################################################   
    public function basic()
    { 
        $request = $this->getRequest();
        $cookieData = $request->getCookie('someCookie', 'default'); //print_r($cookieData['ck_lang']);
        $view = new ViewModel();
        //Route
        $view->lang = $this->params()->fromRoute('lang', @$cookieData['ck_lang']);
        if(empty($view->lang)) $view->lang = @$cookieData['ck_lang'];
	    if(empty($view->lang)) $view->lang = 'fr';
	    //echo $view->lang;
        $view->action = $this->params()->fromRoute('action', 'index');
        $view->id = $this->params()->fromRoute('id', ''); 
        $view->page = $this->params()->fromQuery('page', 1);
        $view->act = $this->params()->fromQuery('act', 'detail');
        $view->nocache = $this->params()->fromQuery('nocache', 0);
        $view->q = $this->params()->fromQuery('q', ''); 
        $view->action_array = [ 
                                    'index', 'new', 'blog', 'login', 'register','profile','dashboard','newpassword',  
                                    'form','indexpro','forgotpassword','contract','forgotpass','terms', 'mail','blogdetail',
                                    'consumer','supplier','supplierform','missioncomplete','contractdetail', 'what-is-zenovly',
                                    // New site 
                                    'projectform','account','contractinfo', 'zenovly-privacy-policy','seller','buyer'     
                              ];    
        $view->full_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $view->recaptcha = $this->config['google_recaptcha'];   
        $view->urlFile = $this->config['amazon_s3']['urlFile'];      
        $view->ar_status = $this->config['contract_status']; 
        $view->Api_url = $this->config['Api_url'].'/'.$view->lang.'/';
        $view->Api_username = $this->config['Api_username'];  
        $view->Api_password = $this->config['Api_password']; 
        $view->service_feee = $this->config['service_feee']; 
        
        return $view;  
    }
################################################################################   
    public function indexAction()
    {  
        $view = $this->basic();    
        return $this->redirect()->toRoute('main',['lang'=>$view->lang],['query'=>['rd'=>rand(100,1000)]]);  
        //return $this->redirect()->toRoute('main',['query'=>['makeid'=>rand(100,1000)]]);  
        //return $this->redirect()->toRoute('admin',['action'=>'setting'],['query'=>['langID'=>$view->langID]]);   
        //return $this->redirect()->toRoute('main').$view->lang.'/makeid='.rand(100,1000);  
          
		//$feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        $view->SEO = [
                        'title'=>'Wezenit, votre fournisseur de confiance pour toutes vos transactions',
                        'keywords'=>'Wezenit,transactions',
                        'description'=>'Wezenit, votre fournisseur de confiance pour toutes vos transactions',
                        //'image'=>'https://renovly.co/img/xxx.jpg'
                     ];
        return $view;
    }
################################################################################
    public function projectformAction()
    {
        $view = $this->basic(); 
        $ptype = $this->params()->fromQuery('type');      
        $pname = $this->params()->fromQuery('name');    
        $pprice = $this->params()->fromQuery('price');
        $zenovly_type = $this->params()->fromQuery('zenovly_type');
        
        $view->uid = @$_COOKIE['uid'];
        $view->id = $view->uid; 
		$feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
	
        $view->SEO = [ 
                        'title'=>'Wezenit, votre fournisseur de confiance pour toutes vos transactions',
                        'keywords'=>'Wezenit,transactions',
                        'description'=>'Wezenit, votre fournisseur de confiance pour toutes vos transactions',
                        //'image'=>'https://renovly.co/img/xxx.jpg'
                     ]; 
        if(empty($pprice)){ //empty($pname) ||  
            return $this->redirect()->toRoute('index'); 
        }
        $view->ptype = $ptype;   
        $view->pname = $pname;  
        $view->pprice = $pprice; 
        $view->zenovly_type = $zenovly_type;
        
        
        if(!empty($view->uid)){ 
           $userProfile = $feedses->getProfile();	//echo "test"; exit;
           $view->userProfile = $userProfile->items; 
        }
    
        return $view;   
    }
################################################################################   
    public function accountAction()
    {
        $view = $this->basic(); 
        $view->SEO = [ 
                        'title'=>'Account', 
                        'keywords'=>'Account',
                        'description'=>'Account With Site',
                        //'image'=>'https://renovly.co/img/xxx.jpg'
                     ];
		return $view;
    } 
################################################################################   
    public function registerAction() 
    {
        $view = $this->basic();
        echo "register".$view->id;
        exit;
        $view->SEO = [
                        'title'=>'Register', 
                        'keywords'=>'Register',
                        'description'=>'Register With Site',
                        //'image'=>'https://renovly.co/img/xxx.jpg'
                     ];
		return $view;
    }
    
################################################################################ 
    public function contractAction()     
    {
        $view = $this->basic(); 
        $view->task = $this->params()->fromQuery('task', '');  
        $view->from = $this->params()->fromQuery('to', '');  
        $view->eid = $view->id;
         
        $time_now = strtotime(date("Y-m-d H:i:s"));
        
          
        
         
        $view->id = str_replace('gpsn','=', $view->id); 
        $view->id = (int)str_replace('zenovly','',base64_decode($view->id));  
        
        
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache); 
        $contract = $feedses->getContract(); 
        
        if($contract->items->request==0){  
            $profile = $feedses->getProfile($contract->items->seller_id);  
        }else{ 
            $profile = $feedses->getProfile($contract->items->buyer_id);
        }
        
        $time_now = strtotime(date("Y-m-d H:i:s"));  
        
        
        if(!empty($_COOKIE['uid'])){                
            return $this->redirect()->toRoute('main',['lang'=>$view->lang,'action'=>'contract','id'=>$view->eid],['query'=>['rd'=>$time_now,'from'=>$view->from]]); 
        }else if($profile->items->status_id ==0){  
            return $this->redirect()->toRoute('main',['lang'=>$view->lang,'action'=>'contract','id'=>$view->eid],['query'=>['task'=>'register','rd'=>$time_now,'from'=>$view->from]]);  
        }else{ 
            return $this->redirect()->toRoute('main',['lang'=>$view->lang,'action'=>'contract','id'=>$view->eid],['query'=>['task'=>'login','rd'=>$time_now,'from'=>$view->from]]); 
        }  
           
        /*
        print_r($contract);
        exit; 
         */
        if($contract->status!=200) return $this->redirect()->toRoute('index'); 
        $view->contract = $contract->items; //print_r($view->contract);
        $view->uid = 0;
       
        $view->SEO = [
                        'title'=>'Contract', 
                        'keywords'=>'Contract', 
                        'description'=>'Contract'
                     ];
        //print_r($view->contract->status); 
        //exit;   
    	if( ($view->contract->request == 1) && ($view->contract->status != 0) && ($view->contract->status!=6))
    	{
    	    return $this->redirect()->toRoute('index', ['action'=>'contractinfo','lang'=>$view->lang,'id'=>$view->id]); 
    	}
    	
        if( (!empty($view->contract->buyer_id)) && ($view->contract->request == 1) )
        {
            $userProfile = $feedses->getProfile($view->contract->buyer_id);  
            $view->userProfile = $userProfile->items;   
        }
        else if( (!empty($view->contract->seller_id) ) && ($view->contract->request==0) )
        {    
            $userProfile = $feedses->getProfile($view->contract->seller_id);    
            $view->userProfile = $userProfile->items; 
        }
        
        if(!empty($_COOKIE['uid'])){            
          $view->uid = @$_COOKIE['uid'];
        }
        //exit;0=pending, 1=accepted(supplier accepted), 2=start, 3=done, 4=looking for supplier, 5=paid, 6=waiting for money, 7=refund
        $view->id = base64_encode('zenovly'.$view->id);   
        $view->id = str_replace('=','gpsn', $view->id);
		return $view;
    }   
################################################################################ 
    public function contractinfoAction()      
    {    
        $view = $this->basic(); 
        $time_now = strtotime(date("Y-m-d H:i:s")); 
        
        return $this->redirect()->toRoute('main',['lang'=>$view->lang,'action'=>'contractinfo','id'=>$view->id],['query'=>['rd'=>$time_now]]);    
         
        $uid = $_COOKIE['uid']; 
        if(empty($uid)){   
            return $this->redirect()->toRoute('index');
        } 
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        $contract = $feedses->getContract(); 
        if($contract->status!=200) return $this->redirect()->toRoute('index'); 
        $view->contract  = $contract->items;  
        $view->SEO = [    
                        'title'=>'Contract info', 
                        'keywords'=>'Contract info', 
                        'description'=>'Contract info' 
                     ]; 
        $view->uid  = $uid; 
		return $view;  
    }   
################################################################################   
    public function blogdetailAction() 
    {
        $view = $this->basic();
        $view->id = explode("-", $view->id);
        $view->id = $view->id[0];  
		$feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        $view->blogDetail = $feedses->getDetail(); //print_r($view->dataDetail); 
        $view->recomment = $feedses->getBlogRecomment();
        $feedses->updateViewBlog();   
        $view->SEO = [ 
                        'title'=>$view->blogDetail->name,   
                        'keywords'=>$view->blogDetail->name.', Renovly, test',
                        'description'=>trim($view->blogDetail->detail),
                        'image'=>$view->blogDetail->img  
                     ];
        return $view;
    }
################################################################################  
    public function blogAction()    
    {
        $view = $this->basic();
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        $view->blog = $feedses->getBlogList();       
        if($view->blog->status!=200) return $this->redirect()->toRoute('index');
        $view->recomment = $feedses->getBlogRecomment();
        if($view->blog->total){  
            $pagination = new Pagination();   
            $pagination->setCurrent($view->page); 
            $pagination->setTotal($view->blog->total); 
            //$pagination->setTotal(200);   //test page 
            $view->pagination = $pagination->parse(); 
        } 
        $view->SEO = [
                        'title'=>'Blog',
                        'keywords'=>'Blog',
                        'description'=>'Blog'
                     ];
        return $view;
    }
################################################################################   
    public function whatIsZenovlyAction()
    {
        $view = $this->basic();
        $view->SEO = [
                        'title'=>'What is Zenovly?',
                        'keywords'=>'Wezenit,transactions',
                        'description'=>'Wezenit, votre fournisseur de confiance pour toutes vos transactions',
                        //'image'=>'https://renovly.co/img/xxx.jpg'
                     ];
        return $view;
    }
################################################################################   
    public function zenovlyPrivacyPolicyAction()
    {
        $view = $this->basic();
        $view->SEO = [
                        'title'=>'Zenovly Privacy Policy?',
                        'keywords'=>'Wezenit,transactions',
                        'description'=>'Wezenit, votre fournisseur de confiance pour toutes vos transactions',
                        //'image'=>'https://renovly.co/img/xxx.jpg'
                     ];
        return $view;
    }
################################################################################   
    public function profileAction()
    {
        $view = $this->basic();
        $login = $this->getLogin();
        if(empty($login)){    
            return $this->redirect()->toRoute('index'); 
        } 
        //$container = new Container('user');  
        $view->id = @$_COOKIE['uid'];    //echo $view->id;  
       
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        $userProfile = $feedses->getProfile();
        $view->userProfile = $userProfile->items;   
        $view->SEO = [ 
                        'title'=>$view->userProfile->name,   
                        'keywords'=>$view->userProfile->name,
                        'description'=>$view->userProfile->name,
                        'image'=>$view->userProfile->image_url
                     ];         
        //setcookie('uimg', $view->userProfile->image_url);         
        //setcookie('uimg', 'https://files.renovly.com/users/'.$view->userProfile->image, time() + (86400 * 30), "/");   
		return $view;
    }   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
################################################################################   
    public function mailAction()
    { echo 'eeee';
        try 
        {/*
            $body = '<b>ok test<b>';
            $toEmail = 'tony@gpsn.co.th';
            $fromEmail = 'contact@wezenit.com';
            $subject = 'test SES na ja tony gpsn - wezenit';
            $message = new Message();
            $html = new MimePart($body);
            $html->type = "text/html";
            
            $body = new MimeMessage();
            $body->setParts(array($html));
            
            $message = new Message();
            $message->setBody($body);
            
            $message->addTo($toEmail, 'Tonytoons gpsn mail')
                    ->addFrom($fromEmail, 'Wezenit team')
                    ->setSubject($subject);
            
            // Setup SMTP transport using LOGIN authentication
            $transport = new SmtpTransport();
            $options   = new SmtpOptions(array(
                'name'              => 'ses-smtp-user.20170421-140044',
                'host'              => 'email-smtp.eu-west-1.amazonaws.com',
                'port'              => 587,
                'connection_class'  => 'login',
                'connection_config' => array(
                    'username' => 'AKIAITNCQXJMNWI36GEA',
                    'password' => 'Au7IxQkXfB5fDZmgfYFJe9SndoaKuAFz38QfBG6w78aZ',
                    'ssl'      => 'tls',
                ),
            ));
            $transport->setOptions($options);
            $transport->send($message);
            echo 'sent';*/
        }
        catch (\Exception $e)
        {
            print_r($e);
        }
    }
################################################################################   
    public function formAction() 
    {
        $view = $this->basic();
        $login = $this->getLogin();
        if(empty($login)){     
            return $this->redirect()->toRoute('index'); 
        }  
        $view->id = @$_COOKIE['uid'];
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        $userProfile = $feedses->getProfile();
        $view->userProfile = $userProfile->items; 
        
        $view->SEO = [ 
                        'title'=>'Zenovly - Form '.$view->id, 
                        'keywords'=>'Zenovly,Form '.$view->id,
                        'description'=>'Zenovly Form'.$view->id 
                     ];
        $view->dateNow = date("m/d/Y");  
		return $view;
    }
################################################################################   
    public function indexproAction()  
    {
        $view = $this->basic();   
        $view->SEO = [ 
                        'title'=>'Zenovly - Index Pro ',  
                        'keywords'=>'Zenovly - Index Pro ',
                        'description'=>'Zenovly - Index Pro' 
                     ];
		return $view;
    }
################################################################################   
    public function newAction()
    { 
        $view = $this->basic();
		$feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        $view->data = $feedses->getContent('new');
        $view->SEO = [
                        'title'=>'New', 
                        'keywords'=>'Wezenit,transactions',
                        'description'=>'Wezenit, votre fournisseur de confiance pour toutes vos transactions', 
                        //'image'=>'https://renovly.co/img/xxx.jpg'
                     ];
        return $view;
    }
################################################################################   
    public function contactAction()
    {
        $view = $this->basic(); 
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        $name = $this->params()->fromPost('name');  
        $email = $this->params()->fromPost('email'); 
        $subject = $this->params()->fromPost('subject'); 
        $msg = $this->params()->fromPost('msg'); 
        $data = array('email'=>$email,'name'=>$name,'subject'=>$subject,'msg'=>$msg);
        $sendMail = $feedses->sendContactUs($data);
        print_r($sendMail); 
        exit;
    }
################################################################################   
    public function forgotpassAction()   
    {
        $view = $this->basic();
        $view->SEO = [ 
                        'title'=>'Forget the password ?',     
                        'keywords'=>'Forget the password ?',
                        'description'=>'Forget the password ?',
                        //'image'=>'https://renovly.co/img/xxx.jpg'
                     ];
		return $view;
    }
    
################################################################################   
    public function dashboardAction() 
    { 
        $view = $this->basic();
        $login = $this->getLogin();
        if(empty($login)){   
            return $this->redirect()->toRoute('index'); 
        } 
        $view->id = @$_COOKIE['uid'];  
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        $userProfile = $feedses->getProfile();
        $view->userProfile = $userProfile->items;
         
        $contract = $feedses->getContractPro();   
        $view->contract = $contract;  
        
        $supplier = $feedses->getSupplierPro();  
        $view->supplier = $supplier;    
        
        $view->SEO = [
                        'title'=>'Dashboard', 
                        'keywords'=>'Dashboard, User, profile',
                        'description'=>'Dashboard', 
                        'image'=>$view->userProfile->image_url
                     ]; //exit;
		return $view;
    }
################################################################################  
    public function newpasswordAction() 
    {
        $view = $this->basic(); 
        $login = $this->getLogin(); 
        if(empty($login)){   
            return $this->redirect()->toRoute('index'); 
        }   
        $view->id = @$_COOKIE['uid'];   
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        $userProfile = $feedses->getProfile();
        $view->userProfile = $userProfile->items;
        $view->SEO = [
                        'title'=>'New Password', 
                        'keywords'=>'New Password, User, profile',
                        'description'=>'New Password',
                        'image'=>$view->userProfile->image_url
                     ];
		return $view; 
    }  
    
################################################################################  

    public function forgotpasswordAction()  
    {
        $view = $this->basic();  
        $login = $this->getLogin();  
          
        
         
        //if(!empty($login)) return $this->redirect()->toRoute('index');  
        $time_now = strtotime(date("Y-m-d H:i:s"));   
        $token = $this->params()->fromQuery('token', '');  
        $view->token = $token;    
        return $this->redirect()->toRoute('main',['lang'=>$view->lang,'action'=>'forgotpassword'],['query'=>['token'=>$token,'rd'=>$time_now]]); 
        //echo $token; exit;  
        
            
        if(empty($token)) return $this->redirect()->toRoute('index'); 
          
        $token = str_replace('gpsn','=', $token);   
        $token = base64_decode($token);     
        $token = explode('&',$token);     
          
        //$token_time = strtotime("2017-04-15 05:00:00"); // time test
        $token_time = strtotime($token[0]);     
        $token_email = trim($token[1]); 
        $time_expire = false;  
        
        if($time_now < $token_time) $time_expire = true; 
        $view->token_email = $token_email;    
        $view->time_expire = $time_expire;  
        $view->SEO = [ 
                        'title'=>'Forgot Password',  
                        'keywords'=>'Forgot Password',
                        'description'=>'Forgot Password' 
                     ];
		return $view;  
    }  
    
   

    
    /*  back 18-05-2017 boy
    public function contractAction()    
    {    
        $view = $this->basic(); 
        $view->task = $this->params()->fromQuery('task', ''); 
        $view->eid = $view->id;  
        $view->id = str_replace('gpsn','=', $view->id); 
        $view->id = (int)str_replace('Wezenit, votre fournisseur de confiance pour toutes vos transactions','',base64_decode($view->id)); 
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        $contract = $feedses->getContract();
        if($contract->status!=200) return $this->redirect()->toRoute('index'); 
        $view->contract  = $contract->items;    
        $view->uid = 0;      
        if(!empty($view->contract->supplier_id) && !empty($_COOKIE['uid'])){  
            $view->uid = $view->contract->supplier_id;   
            $userProfile = $feedses->getProfile($view->uid);  
            $view->userProfile = $userProfile->items;  
        }else if(!empty($_COOKIE['uid'])){           
          $view->uid = @$_COOKIE['uid'];    
          $userProfile = $feedses->getProfile($view->uid); 
          $view->userProfile = $userProfile->items; 
        }
        
        $view->id = base64_encode('Wezenit, votre fournisseur de confiance pour toutes vos transactions'.$view->id); 
        $view->SEO = [
                        'title'=>'Contract', 
                        'keywords'=>'Contract', 
                        'description'=>'Contract' 
                     ];
		return $view;
    }
*/   
    
    
################################################################################  
    public function testAction()    
    { 
        $view = $this->basic(); 
        //$request = $this->getRequest();
        //$cookieData = $request->getCookie('uid'); 
        print_r($_COOKIE['uid']);  
        exit;//return $view; 
    }
################################################################################  
    public function txtlangAction()
    {
        $view = $this->basic(); 
        return $view;   
    }
################################################################################   
    public function setSession($session=array())  
    {
        ini_set('session.gc_maxlifetime', 60*60*24*30);
        $container = new Container('user'); 
        foreach( $session as $key=>$value )
        {
            $container->$key = $value;
        }
    }
################################################################################   
    public function getLogin()  
    {   /*
        $container = new Container('user');
        $login = FALSE;
        $sid = $container->id;
        */   
        $login = FALSE;   
        $sid = @$_COOKIE['uid'];     
        if(!empty($sid)) $login=TRUE;
        return $login;   
    }
################################################################################   
    public function logoutAction()
    { 
        /*
        $container = new Container('user');
        unset($container->id);
        unset($container->name);
        $container->id = ''; 
        $container->name = '';
        */    
        /*
        foreach ($_COOKIE as $key => $value) {
            unset($_COOKIE[$key]);
            setcookie($key, '', time() - 3600); 
        }*/ 
        session_destroy();
        return $this->redirect()->toRoute('index', ['action'=>'index']);
    } 
    
################################################################################     
    public function onDispatch(MvcEvent $e) 
    {
        $response = parent::onDispatch($e);        
	    $view = $this->basic();
	    if (!in_array($view->action, $view->action_array)) 
        {
            $this->layout()->setTemplate('error/error');    
        }
        return $response;
    }
    
################################################################################   
    public function termsAction()  
    {  
        $view = $this->basic(); 
        $view->SEO = [ 
                        'title'=>'Terms and service',  
                        'keywords'=>'Terms and service',
                        'description'=>'Terms and service',
                        //'image'=>'https://renovly.co/img/xxx.jpg'
                     ];
		return $view;
    }
################################################################################   
    public function recaptchaAction()  
    { 
        $view = $this->basic(); 
        $secret=$view->recaptcha['Secret_key'];    
        $response=$this->params()->fromPost('captcha');   
        $url = "https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$response}";
        //echo $url;  
        $verify=file_get_contents($url); 
        echo $verify;    
        exit;
    }
################################################################################   
    public function consumerAction()
    {
        $view = $this->basic();
        $login = $this->getLogin();
        if(empty($login)){    
            return $this->redirect()->toRoute('index'); 
        } 
        
        $view->id = @$_COOKIE['uid'];
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        
        
        $task = $this->params()->fromRoute('task', '');
        $status = $this->params()->fromRoute('status', 0); 
        
        if(!empty($task)){ 
            $contract = $feedses->getContractPro($status);    
            $view->contract = $contract; 
            $view->perpage = 21; 
            $view->pageStart = ($view->perpage*($view->page-1));
            
            if($contract->total){  
                $pagination = new Pagination();   
                $pagination->setCurrent($view->page); 
                $pagination->setTotal($contract->total);  
                $view->pagination = $pagination->parse(); 
            } 
    		echo json_encode($ar_data);   
            exit; 
        }
         
        $userProfile = $feedses->getProfile();
        $view->userProfile = $userProfile->items;
         
        /*
        $contract = $feedses->getContractPro();   
        $view->contract = $contract;  
        
        $view->perpage = 21; 
        $view->pageStart = ($view->perpage*($view->page-1));
        
        if($contract->total){  
            $pagination = new Pagination();   
            $pagination->setCurrent($view->page); 
            $pagination->setTotal($contract->total); 
            //$pagination->setTotal(200);   //test page 
            $view->pagination = $pagination->parse(); 
        }    
        */
        
        $view->SEO = [ 
                        'title'=>$view->userProfile->name,   
                        'keywords'=>$view->userProfile->name,
                        'description'=>$view->userProfile->name,
                        'image'=>$view->userProfile->image_url
                     ];
		return $view; 
    }   
    
################################################################################   
    public function supplierAction()
    {
        $view = $this->basic();
        $login = $this->getLogin();
        if(empty($login)){    
            return $this->redirect()->toRoute('index'); 
        } 
        
        $view->id = @$_COOKIE['uid'];    //echo $view->id;  
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        
        $userProfile = $feedses->getProfile();
        $view->userProfile = $userProfile->items;
          
        $supplier = $feedses->getSupplierPro(); 
        $view->supplier = $supplier;  
        //print_r($supplier);exit;
        $view->perpage = 21;  
        $view->pageStart = ($view->perpage*($view->page-1));
        
        if($supplier->total){   
            $pagination = new Pagination();    
            $pagination->setCurrent($view->page); 
            $pagination->setTotal($supplier->total);  
            //$pagination->setTotal(200);   //test page 
            $view->pagination = $pagination->parse(); 
        }
         
        $view->SEO = [ 
                        'title'=>$view->userProfile->name,   
                        'keywords'=>$view->userProfile->name,
                        'description'=>$view->userProfile->name,
                        'image'=>$view->userProfile->image_url
                     ];
		return $view;
    }
    
    
################################################################################   
    public function supplierformAction()   
    {
        $view = $this->basic();
        $login = $this->getLogin();
        if(empty($login)){     
            return $this->redirect()->toRoute('index'); 
        }  
        $view->id = @$_COOKIE['uid'];
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        $userProfile = $feedses->getProfile();
        $view->userProfile = $userProfile->items;   
         
        $view->SEO = [ 
                        'title'=>'Zenovly - Supplier Form ',  
                        'keywords'=>'Zenovly,Supplier, Form ',
                        'description'=>'Zenovly Supplier Form' 
                     ];
        $view->dateNow = date("m/d/Y");  
		return $view;
    }
    
    
    ################################################################################   
    public function missioncompleteAction()  
    {  
        $view = $this->basic();  
        $view->SEO = [   
                        'title'=>'Mission complete',  
                        'keywords'=>'Mission complete', 
                        'description'=>'Mission complete',
                        //'image'=>'https://renovly.co/img/xxx.jpg'
                     ];
                   
		return $view;
    }
    
    
    
    ################################################################################ 

    public function contractdetailAction()     
    {    
        $view = $this->basic();  
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        $contract = $feedses->getContract();
        if($contract->status!=200) return $this->redirect()->toRoute('index'); 
        $view->contract  = $contract->items;          
        $view->uid = 0;       
        if(!empty($_COOKIE['uid'])){            
            $view->uid = @$_COOKIE['uid'];
        }else{
            return $this->redirect()->toRoute('index', ['action'=>'index']);    
        }
        //print_r([$view->uid.'='.$view->contract->user_id,$view->uid.'='.$view->contract->supplier_id]);
        //exit;  
        if($view->uid==$view->contract->user_id || $view->uid==$view->contract->supplier_id){  
             
            if(!empty($view->contract->user_id)){  
                $userProfile = $feedses->getProfile($view->contract->user_id);  
                $view->userProfile = $userProfile->items; 
            } 
            $view->SEO = [  
                            'title'=>'Contract Detail', 
                            'keywords'=>'Contract Detail', 
                            'description'=>'Contract Detail' 
                         ];
    		return $view;
        }else{   
            return $this->redirect()->toRoute('index', ['action'=>'index']);
        }
        
    }
    
    
    public function sellerAction()       
    {
        $view = $this->basic();
        $login = $this->getLogin();
        if(empty($login)){    
            return $this->redirect()->toRoute('index'); 
        }  
        
        $view->id = @$_COOKIE['uid'];
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        
        $act = $this->params()->fromRoute('act', 'buyer'); 
        $task = $this->params()->fromRoute('task', '');
        $status = $this->params()->fromRoute('status', 0); 
        
        if(!empty($task)){  
            $contract = $feedses->getContractList($status,$act);       
            $view->contract = $contract;  
            $view->perpage = 21; 
            $view->pageStart = ($view->perpage*($view->page-1));
            $ar_data = array(); 
            if($contract->total){  
                $pagination = new Pagination();   
                $pagination->setCurrent($view->page); 
                $pagination->setTotal($contract->total);  
                $view->pagination = $pagination->parse(); 
            } 
    		echo json_encode($ar_data);   
            exit; 
        }
         
        $userProfile = $feedses->getProfile();
        $view->userProfile = $userProfile->items;
        $view->act = $act;  
        /*
        $contract = $feedses->getContractPro();   
        $view->contract = $contract;  
        
        $view->perpage = 21; 
        $view->pageStart = ($view->perpage*($view->page-1));
        
        if($contract->total){  
            $pagination = new Pagination();   
            $pagination->setCurrent($view->page); 
            $pagination->setTotal($contract->total); 
            //$pagination->setTotal(200);   //test page 
            $view->pagination = $pagination->parse(); 
        }    
        */
        
        $view->SEO = [ 
                        'title'=>$view->userProfile->name,   
                        'keywords'=>$view->userProfile->name,
                        'description'=>$view->userProfile->name,
                        'image'=>$view->userProfile->image_url
                     ];
		return $view; 
    }  
    
    public function buyerAction()        
    {
        $view = $this->basic();
        $login = $this->getLogin();
        if(empty($login)){    
            return $this->redirect()->toRoute('index'); 
        }  
        
        $view->id = @$_COOKIE['uid'];
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        
        $act = $this->params()->fromRoute('act', 'buyer'); 
        $task = $this->params()->fromRoute('task', '');
        $status = $this->params()->fromRoute('status', 0); 
        
        if(!empty($task)){  
            $contract = $feedses->getContractList($status,$act);       
            $view->contract = $contract;  
            $view->perpage = 21; 
            $view->pageStart = ($view->perpage*($view->page-1));
            $ar_data = array(); 
            if($contract->total){  
                $pagination = new Pagination();   
                $pagination->setCurrent($view->page); 
                $pagination->setTotal($contract->total);  
                $view->pagination = $pagination->parse(); 
            } 
    		echo json_encode($ar_data);   
            exit; 
        }
         
        $userProfile = $feedses->getProfile();
        $view->userProfile = $userProfile->items;
        $view->act = $act;  
        /*
        $contract = $feedses->getContractPro();   
        $view->contract = $contract;  
        
        $view->perpage = 21; 
        $view->pageStart = ($view->perpage*($view->page-1));
        
        if($contract->total){  
            $pagination = new Pagination();   
            $pagination->setCurrent($view->page); 
            $pagination->setTotal($contract->total); 
            //$pagination->setTotal(200);   //test page 
            $view->pagination = $pagination->parse(); 
        }    
        */
        
        $view->SEO = [ 
                        'title'=>$view->userProfile->name,   
                        'keywords'=>$view->userProfile->name,
                        'description'=>$view->userProfile->name,
                        'image'=>$view->userProfile->image_url
                     ];
		return $view; 
    } 
   
    
    
    
    
    
    
    
    
    
} 