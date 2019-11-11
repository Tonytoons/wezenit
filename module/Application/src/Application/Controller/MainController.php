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
                'keywords'=>'Wezenit, votre fournisseur de confiance pour toutes vos transactions',
                'description'=>'Wezenit, votre fournisseur de confiance pour toutes vos transactions',
                'url'=>'https://renovly.co',
                'image'=>'https://renovly.co/img/xxx.jpg',
                'domain'=>'renovly.co',
                'fb_app_id'=>'128202497713838', //fb:app_id  
                'locale'=>'fr_FR', //og:locale
                'creator'=>'@Renovly', //twitter:creator
             ];  
*/


class MainController extends AbstractActionController
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
                                    'consumer','supplier','supplierform','missioncomplete','contractdetail', 'what-is-wezenit',
                                    // New site 
                                    'projectform','account','contractinfo', 'wezenit-privacy-policy','seller','buyer','faq','contact-us',
                                    'termes-et-condition', 'notre-equipe', 'search','radio'
                              ];        
        $view->full_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $view->host_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST']; 
        $view->recaptcha = $this->config['google_recaptcha'];    
        $view->urlFile = $this->config['amazon_s3']['urlFile'];       
        $view->ar_status = $this->config['contract_status'];
        $view->Api_url = $this->config['Api_url'].'/'.$view->lang.'/';
        $view->Api_username = $this->config['Api_username'];  
        $view->Api_password = $this->config['Api_password']; 
        $view->service_feee = $this->config['service_feee']; 
        if(!empty($_COOKIE['uid']))$view->uid = @$_COOKIE['uid'];  
        $view->SEO = [ 
                            'title'=>'Wezenit, votre fournisseur de confiance pour toutes vos transactions',
                            'keywords'=>'Wezenit, votre fournisseur de confiance pour toutes vos transactions',
                            'description'=>'Wezenit, votre fournisseur de confiance pour toutes vos transactions',
                            //'image'=>'https://renovly.co/img/xxx.jpg'
                         ]; 
        if($view->lang=='en'){
            $view->SEO = [
                            'title'=>'Wezenit, your trusted supplier for all your transactions',
                            'keywords'=>'Wezenit, your trusted supplier for all your transactions',
                            'description'=>'Wezenit, your trusted supplier for all your transactions'
                         ];
        }
        //exit;
        return $view;  
    }
################################################################################   
    public function indexAction()
    {
        $view = $this->basic(); 
		//$feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
		//echo "Main";
		//exit; 
		  
        if(!empty($_COOKIE['uid'])){ 
            $view->uid = @$_COOKIE['uid'];    //echo $view->id;
            //$feedses = new Feeds($view->lang, $view->uid, $view->page, $view->nocache);
            //$userProfile = $feedses->getProfile();   
            //$view->userProfile = $userProfile->items;    
        }
        
        return $view; 
    }
    
    ################################################################################   
    public function searchAction()
    {
        $view = $this->basic(); 
		//$feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
		//echo "Main";
		//exit; 
		$view->search = $this->params()->fromQuery('q','');   
		//$view->page = $this->params()->fromQuery('page','');  
		if(empty($view->search)) return $this->redirect()->toRoute('index'); 
		
        if(!empty($_COOKIE['uid'])){  
            $view->uid = @$_COOKIE['uid'];    //echo $view->id;
            //$feedses = new Feeds($view->lang, $view->uid, $view->page, $view->nocache);
            //$userProfile = $feedses->getProfile();   
            //$view->userProfile = $userProfile->items;     
        } 
        
        
        
        if(!empty($view->search)){ 
            
            $feedses = new Feeds($view->lang, $view->uid, $view->page, $view->nocache);
            $view->dataSearch = $feedses->getSearch($view->search); 
            //print_r($view->dataSearch);
            //exit;     
            if(!empty($view->dataSearch->total)){    
                $pagination = new Pagination();    
                $pagination->setCurrent($view->page); 
                $pagination->setTotal($view->dataSearch->total); 
                //$pagination->setTotal(200);   //test page 
                $view->pagination = $pagination->parse(); 
            } 
            
        }             
        $view->content = 'search'; 
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
        $zenovly_type = !empty($zenovly_type)?$zenovly_type:2;  
         
        $view->uid = @$_COOKIE['uid'];
        $view->id = $view->uid; 
		$feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
		//echo "Text";
	    
	    $act = $this->params()->fromQuery('act', ''); 
        $data = $this->params()->fromPost('data', []);
        $user_id = $this->params()->fromQuery('uid', 0); 
        
        if(!empty($data) && $act=='companyUpdate'){ 
            $rs = $feedses->companyUpdate($data);
            print_r($rs);   
            exit; 
        }else if(!empty($data) && $act=='newCustomer'){ 
            $rs = $feedses->newCustomer($data);
            print_r($rs);   
            exit; 
        }else if(!empty($data) && $act=='editCustomer'){  
            $rs = $feedses->editCustomer($data);
            print_r($rs);   
            exit; 
        }else if(!empty($data) && $act=='makeContracttomer'){  
            $rs = $feedses->makeContracttomer($data, 4);
            print_r($rs);   
            exit; 
        }else if(!empty($user_id) && $act=='UserCompany'){  
            $rs = $feedses->getProfile($user_id);
            print_r(json_encode($rs));       
            exit;  
        }
        
	    
        if(empty($pprice)){ 
            return $this->redirect()->toRoute('index'); 
        }
        $view->ptype = $ptype;   
        $view->pname = $pname;  
        $view->pprice = $pprice;   
        $view->zenovly_type = $zenovly_type; 
        $view->task = 'login';  
        if(!empty($view->uid)){ 
           $userProfile = $feedses->getProfile();	//echo "test"; exit;
           $view->userProfile = $userProfile->items;  
        }
        //exit;
         
        $view->content = 'project_form'; 
        return $view;   
    }
################################################################################   
    public function accountAction()
    {
        
        $view = $this->basic(); 
        
        $view->SEO = [ 
                        'title'=>'Compte'
                     ];
                     
        if($view->lang=='en'){ 
            $view->SEO = [ 
                        'title'=>'Account'
                     ];
        } 
        $view->email = $this->params()->fromQuery('email'); 
        $task = $this->params()->fromQuery('task');
        $name = $this->params()->fromPost('name');   
        $email = $this->params()->fromPost('email'); 
        $password = $this->params()->fromPost('password');
        $facebook_id = $this->params()->fromPost('facebook_id');
        $buyer_id = $this->params()->fromPost('buyer_id'); 
        $act = $this->params()->fromQuery('act'); 
        
        if(empty($facebook_id)) $facebook_id = 0;
        
        $Feeds = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        
        if($act=='login'){ 
            $data = ['email'=>$email, 'upassword'=>$password, 'facebook_id'=>$facebook_id];
            //if(!empty($data['facebook_id']))$data = ['email'=>$email, 'facebook_id'=>$facebook_id];
            $response = $Feeds->Apiservice('login', $data, 'post');       
            print_r($response);     
            exit;  
        }else if($act=='register'){   
            $data = ['name'=>$name,'email'=>$email, 'upassword'=>$password, 'facebook_id'=>$facebook_id]; 
            //if(!empty($data['facebook_id']))$data = ['name'=>$name,'email'=>$email, 'facebook_id'=>$facebook_id]; 
            if(!empty($buyer_id)) $data['buyer_id'] =  $buyer_id;   
            $response = $Feeds->Apiservice('regis', $data, 'post');   
            print_r($response);      
            exit; 
        }
        $view->task = $task;  
        $view->content = 'account_form';
        
        $request = $this->getRequest();  
        $cookie = $request->getCookie('uid', '');
        
        if(!empty($cookie['uid'])){   
            $view->uid = $cookie['uid'];  
            //return $this->redirect()->toRoute('index'); 
        }
		return $view;
    } 
################################################################################   
    public function registerAction() 
    {
        $view = $this->basic();
        $view->SEO = [
                        'title'=>'registre'
                     ];
        if($view->lang=='en'){ 
            $view->SEO = [ 
                        'title'=>'Register'
                     ];
        } 
		return $view;
    }
################################################################################ 
    public function contractAction()     
    {
        $view = $this->basic(); 
        $view->task = $this->params()->fromQuery('task', ''); 
        $view->from = $this->params()->fromQuery('from', ''); 
        $view->error = $this->params()->fromQuery('error', ''); 
        $view->eid = $view->id;
        $view->id = str_replace('gpsn','=', $view->id); 
        $view->id = (int)str_replace('zenovly','',base64_decode($view->id)); 
        
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        $contract = $feedses->getContract(); //print_r($contract->status); exit;
        
        $act = $this->params()->fromQuery('act', '');  
        $data = $this->params()->fromPost('data', []);
        
        if(!empty($data) && $act=='accept'){
            $response = $feedses->acceptContract($data);     
            print_r($response);
            exit; 
        }
        /*
        echo "<pre>";  
        print_r($contract);  
        echo "</pre>"; 
        exit;
        */ 
        
        if($contract->status!=200) return $this->redirect()->toRoute('index');   
        
         /*
        $buyer_profile = $feedses->getProfile($contract->items->buyer_id);
        $time_now = strtotime(date("Y-m-d H:i:s")); 
          
        if($buyer_profile->items->status_id !=1){ 
            return $this->redirect()->toRoute('main',['lang'=>'fr','action'=>'contract','id'=>$view->id],['query'=>['task'=>'register','rd'=>$time_now]]); 
        }  
        */
        /*
        echo "<pre>";  
        print_r($buyer_profile);  
        echo "</pre>"; 
        exit;
        */
        
        $view->contract = $contract->items; 
        
        
        
        $view->uid = 0;
        
       
        /*
        print_r($view->contract);  
        exit;   
        */ 
         
    	if( ($view->contract->request == 1) && ($view->contract->status != 0) && ($view->contract->status!=6)) 
    	{
    	    return $this->redirect()->toRoute('main', ['action'=>'contractinfo','lang'=>$view->lang,'id'=>$view->id]); 
    	}
    	
        if( (!empty($view->contract->buyer_id)) && ($view->contract->request == 1) )
        {
            $userProfile = $feedses->getProfile($view->contract->buyer_id);  
            $view->userProfile = $userProfile->items;  
            
            $userProfile2 = $feedses->getProfile($view->contract->seller_id);    
            $view->userProfile2 = $userProfile2->items;
        }
        else if( (!empty($view->contract->seller_id) ) && ($view->contract->request==0) )
        {    
            $userProfile = $feedses->getProfile($view->contract->seller_id);    
            $view->userProfile = $userProfile->items; 
            
            $userProfile2 = $feedses->getProfile($view->contract->buyer_id);    
            $view->userProfile2 = $userProfile2->items; 
        }
        
        if($act=='goPayment' && !empty($view->eid)){ 
            
            //https://safe-tonytoons.c9users.io/public/api/fr/pay/27370818/?username=RockStar&password=Um9ja1N0YXI=&wid=27387716&amount=300&zenovly_id=94&
            
            //return $this->redirect()->toRoute('api',['lang'=>'fr','action'=>'pay','id'=>$view->contract->id],['query'=>['task'=>'register','rd'=>$time_now]]);
            $url_api = $view->Api_url.'pay/'.$view->userProfile->mangopay_id.'/?username='.$view->Api_username.'&password='.$view->Api_password.'&wid='.$view->userProfile->mangopay_wallet.'&amount='.($view->contract->pay_price).'&zenovly_id='.$view->contract->id.'&fee='.($view->contract->pay_price-$view->contract->transfer_price).'&payInType='.$view->task.'&returnURL='.$view->host_url.'/main/'.$view->lang.'/contract/'.$view->eid.'/?rd='.date("YmdHis");
            $data_api = $feedses->getService($url_api);
            $result = json_decode($data_api);
            header('Location: '.$result->RedirectURL);
            /* 
            echo "<pre>";
            print_r($result);  
            echo "</pre>";*/ 
            exit;  
            /*
            echo "<pre>";
            print_r($view->contract); 
            echo "</pre>"; 
            
            echo "<pre>";
            print_r($view->userProfile);
            echo "</pre>";
            exit; */
        }
        
        if(!empty($_COOKIE['uid'])){             
          $view->uid = @$_COOKIE['uid'];
          $uck = 0;
          if($view->uid==$view->contract->buyer_id){
              $uck = 1; 
          }
          if($view->uid==$view->contract->seller_id){
              $uck = 1;
          } 
          if($uck==0){ 
             return $this->redirect()->toRoute('index'); 
          }  
        }
            
        if($view->from==1){ 
            $view->email = $view->contract->buyer_email;  
            $view->name = $view->contract->buyer_name; 
            $view->buyer_id = $view->contract->buyer_id; 
        }else{ 
            $view->email = $view->contract->seller_email;  
            $view->name = $view->contract->seller_name; 
            $view->buyer_id = $view->contract->seller_id; 
        }
      
        
        //exit;0=pending, 1=accepted(supplier accepted), 2=start, 3=done, 4=looking for supplier, 5=paid, 6=waiting for money, 7=refund , 8=Finished
        $view->id = base64_encode('zenovly'.$view->id); 
        $view->id = str_replace('=','gpsn', $view->id);
         
        if($view->contract->status==3 || $view->contract->status==8){         
           return $this->redirect()->toRoute('index', ['action'=>'contractinfo','lang'=>$view->lang,'id'=>$view->contract->id]);  
        } 
        
        $view->content = 'contract';  
		return $view; 
    }   
################################################################################ 
    public function contractinfoAction()     
    {    
        $view = $this->basic(); 
        $uid = $_COOKIE['uid'];
        if(empty($uid)){   
            return $this->redirect()->toRoute('index');
        } 
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        
        $act = $this->params()->fromQuery('act', '');  
        $code = $this->params()->fromQuery('code', '');
        $ck = $this->params()->fromQuery('ck', '');
          
        $token = base64_encode('zenovly'.$view->id);
        $view->eid = str_replace ( '=', 'gpsn', $token); 
        
        //var url = apiUrl+'contract/'+cid+'/?'+user_api+'&'+password_api+'&act=addTracking&user_id='+uid+'&shipping_tracking_number='+tackingcode; 
        if(!empty($code) && $act=='addCode'){ 
            $data['act'] = 'addTracking';
            $data['user_id'] = $uid;
            $data['shipping_tracking_number'] = $code;
            //echo $view->id; exit;
            echo $feedses->addTrackingCode($view->id, $data);   
            exit;
        }
        
        $contract = $feedses->getContract();  
        if($contract->status!=200) return $this->redirect()->toRoute('index'); 
        
        $view->contract  = $contract->items;  
        
        if( (!empty($view->contract->buyer_id)) && ($view->contract->request == 1) )
        {
            $userProfile = $feedses->getProfile($view->contract->buyer_id);  
            $view->userProfile = $userProfile->items;
            
            $userProfile2 = $feedses->getProfile($view->contract->seller_id);    
            $view->userProfile2 = $userProfile2->items;
        }
        else if( (!empty($view->contract->seller_id) ) && ($view->contract->request==0) )
        {    
            $userProfile = $feedses->getProfile($view->contract->seller_id);    
            $view->userProfile = $userProfile->items;
            
            $userProfile2 = $feedses->getProfile($view->contract->buyer_id);    
            $view->userProfile2 = $userProfile2->items;
        } 
        
        $from = $this->params()->fromQuery('from', '');
        $uck = 0; 
        
        if(!empty($_COOKIE['uid'])){                 
          $view->uid = @$_COOKIE['uid'];
          
          if($view->uid==$view->contract->buyer_id){
              $uck = 1; 
          }
          if($view->uid==$view->contract->seller_id){
              $uck = 1;
          } 
        }
        if($from=='admin'){
            $uck = 1;
        } 
        
        if($uck==0){    
            return $this->redirect()->toRoute('main',['lang'=>$view->lang],['query'=>['rd'=>rand(1000,10000)]]); 
        }   
        
        $view->uid  = $uid;   
        $view->content = 'contract_info'; 
        $view->ck  = $ck; 
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
                        'keywords'=>$view->blogDetail->name,
                        'description'=>trim($view->blogDetail->detail),
                        'image'=>$view->blogDetail->img  
                     ];
        $view->content = 'blogdetail'; 
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
        $view->content = 'blog'; 
        return $view;
    }
################################################################################   
    public function whatIsWezenitAction() 
    { 
        $view = $this->basic();  
        $view->SEO = [ 
                        'title'=>"WeZenIt c'est quoi ?"
                     ]; 
        if($view->lang=='en'){ 
            $view->SEO = [ 
                        'title'=>'What is Wezenit?'
                     ];
        } 
        //echo "Test"; exit;  
        $view->content = 'whatiswezenit';       
        return $view; 
    }
    
    ################################################################################   
    public function faqAction() 
    { 
        $view = $this->basic();    
        $view->SEO = [
                        'title'=>'FAQ'
                     ];
        //echo "Test"; exit;  
        $view->content = 'faq';         
        return $view; 
    }
    
    ################################################################################   
    public function contactusAction() 
    { 
        $view = $this->basic();     
        $view->SEO = [ 
                        'title'=>'Contactez-nous'
                     ];
        if($view->lang=='en'){ 
            $view->SEO = [ 
                        'title'=>'Contact Us'
                     ];
        } 
        //echo "Test"; exit; 
        //$adapter, $inLang, $inAction, $inID, $inPage, $inFor, $nocache  
        $act = $this->params()->fromQuery('act', '');
        $name = $this->params()->fromPost('name', '');
        $email = $this->params()->fromPost('email', '');
        $message = $this->params()->fromPost('message', '');
        
        if($act=='sendMail' && !empty($name) && !empty($email) && !empty($message)){
            
            $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
            $data = array('email'=>$email,'name'=>$name,'subject'=>'Contactez-nous','msg'=>$message);
            try
            {
                $sendMail = $feedses->sendContactUs($data);      
            } 
            catch (Zend_Exception $e){
                $sendMail = $e; 
            }   
            print_r(json_encode($sendMail));      
            exit; 
        }else if($act=='sendMail'){
            print_r(json_encode(['status'=>400,'ms'=>'parameter not set'])); 
            exit;
        }
        $view->content = 'contactus';           
        return $view; 
    }
    
    
################################################################################   
    public function zenovlyPrivacyPolicyAction()
    {
        $view = $this->basic(); 
        $view->SEO = [
                        'title'=>'Politique de confidentialité de Wezenit?'
                     ]; 
        if($view->lang=='en'){ 
            $view->SEO = [ 
                        'title'=>'Wezenit Privacy Policy?'
                     ];
        } 
        return $view;    
    }
    
################################################################################   
    public function notreEquipeAction() 
    {
        $view = $this->basic(); 
        $view->SEO = [
                        'title'=>'Wezenit Notre équipe'
                     ]; 
        if($view->lang=='en'){ 
            $view->SEO = [ 
                        'title'=>'Wezenit Our team'
                     ];
        } 
        $view->content = 'notre-equipe';
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
        
        $act = $this->params()->fromQuery('act', ''); 
        $data = $this->params()->fromPost('data', []);
        
        $view->id = @$_COOKIE['uid'];    //echo $view->id;  
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        
        if(!empty($data) && $act=='editProfile'){ 
            $rs = $feedses->editProfile($data);
            print_r($rs);   
            exit; 
        }else if(!empty($data) && $act=='editCompanyInfo'){ 
            $rs = $feedses->editCompanyInfo($data); 
            print_r($rs);     
            exit;  
        }else if(!empty($data) && $act=='addBank'){   
            $rs = $feedses->addBank($data);  
            print_r($rs);     
            exit;    
        }else if(!empty($data) && $act=='changePassword'){ 
            $rs = $feedses->changePassword($data);    
            print_r($rs);     
            exit; 
        }else if(!empty($data) && ($act=='seller' || $act=='buyer')){ 
            $rs = $feedses->getBystatus($data);      
            print_r($rs);        
            exit; 
        }else if(!empty($data) && $act=='done'){
            $cid = $data['cid']; 
            unset($data['cid']); 
            $sstatus = $data['sstatus'];  
            $rs = $feedses->statusDone($cid, $data);      
            print_r($rs);          
            exit; 
        }else if(!empty($data) && $act=='imgPF'){  
            $rs = $feedses->uploadIMGPF($data);      
            print_r($rs);          
            exit; 
        }   
        
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
         
        $view->content = 'profile'; 
		return $view;
    }   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
################################################################################   
    public function mailAction()
    {
        try 
        {
            $body = '<b>ok test<b>';
            $toEmail = 'tony@gpsn.co.th';
            $fromEmail = 'contact@wezenit.com';
            $subject = 'test SES na ja tony gpsn - Wezenit';
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
                'name'              => 'ses-smtp-user.20180929-114946',
                'host'              => 'email-smtp.us-east-1.amazonaws.com',
                'port'              => 587,
                'connection_class'  => 'login',
                'connection_config' => array(
                    'username' => 'AKIAJXMT53VZ6W3SNC2A',
                    'password' => 'AkUC7l3IaA4CHFD7pjN7Df/c1jvdZ7bUusNlIhxVvSOW',
                    'ssl'      => 'tls',
                ),
            ));
            $transport->setOptions($options);
            $transport->send($message);
            echo 'sent';
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
                        'title'=>'Forget the password ?'
                     ]; 
        if($view->lang=='en'){  
            $view->SEO = [  
                        'title'=>'Oublier le mot de passe?'
                     ];
        }              
        $act = $this->params()->fromPost('act'); 
        $email = $this->params()->fromPost('email'); 
        if($act=='forgot' && !empty($email)){  
            $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
            $rs = $feedses->forgotPassword($email);      
            echo $rs;      
            exit;  
        }
        $view->content = 'forgot-pass';  
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
                        'title'=>'Tableau de bord',
                        'image'=>$view->userProfile->image_url
                     ]; //exit;
        if($view->lang=='en'){  
            $view->SEO = [  
                        'title'=>'Dashboard'
                     ];
        } 
        $view->content = 'dashboard';
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
                        'title'=>'nouveau mot de passe',
                        'image'=>$view->userProfile->image_url
                     ];
        if($view->lang=='en'){   
            $view->SEO = [  
                        'title'=>'New Password'
                     ];
        } 
        $view->content = 'newpassword';
		return $view; 
    }  
    
################################################################################  

    public function forgotpasswordAction()  
    {
        $view = $this->basic();  
        $login = $this->getLogin();  
          
        if(!empty($login)) return $this->redirect()->toRoute('index'); 
        $time_now = strtotime(date("Y-m-d H:i:s"));  
        $token = $this->params()->fromQuery('token', '');  
        $view->token = $token;   
        if(empty($token)) return $this->redirect()->toRoute('index'); 
          
        $token = str_replace('gpsn','=', $token);   
        $token = base64_decode($token);     
        $token = explode('&',$token);     
          
        //$token_time = strtotime("2017-04-15 05:00:00"); // time test
        $token_time = strtotime($token[0]);     
        $token_email = trim($token[1]); 
        $time_expire = false;  
        
        $act = $this->params()->fromQuery('act', ''); 
        $data = $this->params()->fromPost('data', '');  
        $view->id = @$_COOKIE['uid']; 
        //echo $view->id; exit;   
        if($act=='newpass' && !empty($data['upassword']) && !empty($data['email'])){  
            $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
            $rs = $feedses->forgotNewPass($data);        
            echo $rs;  
            exit;
        }
        
        if($time_now < $token_time) $time_expire = true; 
        $view->token_email = $token_email;    
        $view->time_expire = $time_expire;  
        $view->SEO = [ 
                        'title'=>'Mot de passe oublié'
                     ];
        if($view->lang=='en'){   
            $view->SEO = [  
                        'title'=>'Forgot Password'
                     ];
        } 
        $view->content = 'forgot-pass';  
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
    public function radioAction()    
    { 
        $view = $this->basic(); 
        $feedses = new Feeds($view->lang, $view->id, $view->page, $view->nocache);
        
        //print_r($_COOKIE['uid']);  
        $url_api = 'https://www.fip.fr/livemeta/75';  
        $contract = $feedses->getService($url_api); 
        echo $contract; 
        exit;//return $view;  
    }
    
################################################################################  
    public function testAction()    
    { 
        $view = $this->basic(); 
        //$request = $this->getRequest();
        //$cookieData = $request->getCookie('uid'); 
        print_r('sss');  
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
        //session_destroy();    
        return $this->redirect()->toRoute('main',['lang'=>$view->lang],['query'=>['makeid'=>rand(100,1000)]]);
        //return $this->redirect()->toRoute('index', ['action'=>'index']);
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
    public function termesEtConditionAction()  
    {  
        $view = $this->basic(); 
        $view->SEO = [ 
                        'title'=>'Mentions légales & conditions',  
                        'keywords'=>'Mentions légales & conditions',
                        'description'=>'Mentions légales & conditions',
                        //'image'=>'https://renovly.co/img/xxx.jpg'
                     ]; 
        $view->content = 'termes-et-condition'; 
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
        
        
        $act = $this->params()->fromQuery('act', ''); 
        $data = $this->params()->fromPost('data', []); 
        
        if($act=='getData' && !empty($data)){  
            $contract = $feedses->getByStatusPro($data);
    		print_r($contract);     
            exit; 
        }
         
        $userProfile = $feedses->getProfile();
        $view->userProfile = $userProfile->items;
        /*
        $contract = $feedses->getContractPro($status); 
        */
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
        //exit;
        $view->SEO = [ 
                        'title'=>$view->userProfile->name,   
                        'keywords'=>$view->userProfile->name,
                        'description'=>$view->userProfile->name,
                        'image'=>$view->userProfile->image_url
                     ];
        $view->content = 'supplier'; 
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
        
         $act = $this->params()->fromQuery('act', ''); 
        $data = $this->params()->fromPost('data', []); 
        
        if($act=='getData' && !empty($data)){  
            $contract = $feedses->getByStatusPro($data); 
    		print_r($contract);  
            exit; 
        } 
        
        $userProfile = $feedses->getProfile();
        $view->userProfile = $userProfile->items;
        /*  
        $supplier = $feedses->getSupplierPro(); 
        $view->supplier = $supplier;  
        //print_r($supplier);
        $view->perpage = 21;  
        $view->pageStart = ($view->perpage*($view->page-1));
        
        if($supplier->total){   
            $pagination = new Pagination();    
            $pagination->setCurrent($view->page); 
            $pagination->setTotal($supplier->total);  
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
        $view->content = 'supplier';              
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
        $view->content = 'seller';
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
        $view->content = 'buyer';
		return $view; 
    }  
  
 
} 