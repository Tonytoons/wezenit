<?php 
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Application\Models\Feeds;
use Zend\Json\Json;
use Zend\View\Model\JsonModel; 
 
use Zend\Session\Container;
use Zend\Session\SessionManager; 
  
/********** Models ********/
use Application\Models\Admin;  
use Application\Models\Upload;  
use Application\Models\Blog;  
use Application\Models\Setting;
use Application\Models\Cms;
use Application\Models\Users;
use Application\Models\Contract;
use Application\Models\Payment; 

/*--s3--*/
require 'vendor/aws/aws-autoloader.php';
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;

use Zend\Db\Sql\Sql; 

use Zend\Mvc\MvcEvent;
//use Zend\Session\SessionManager;
/*
$this->params()->fromPost('paramname');   // From POST
$this->params()->fromQuery('paramname');  // From GET
$this->params()->fromRoute('paramname');  // From RouteMatch
$this->params()->fromHeader('paramname'); // From header
$this->params()->fromFiles('paramname'); 
*/
class AdminController extends AbstractActionController
{
################################################################################ 
    public function __construct()
    {
        $this->cacheTime = 36000;
        $this->now = date("Y-m-d H:i:s");
        $this->eth = 'คุณไม่สามารถเข้าถึง API ได้ค่ะ!';
        $this->een = 'Sorry, you can not to access API!';
        $this->config = include __DIR__ . '../../../../config/module.config.php';
        $this->adapter = new Adapter($this->config['Db']);
    }
################################################################################   
    public function basic() 
    {
        $view = new ViewModel();  
        //Route
        $view->lang = $this->params()->fromRoute('lang', 'th');
        $view->action = $this->params()->fromRoute('action', 'index');
        $view->id = $this->params()->fromRoute('id', '');
        $view->page = $this->params()->fromQuery('page', 1);
        $view->act = $this->params()->fromQuery('act', 'detail');  
        $view->action = $this->getEvent()->getRouteMatch()->getParam('action', 'NA');
        $view->langID = $this->params()->fromQuery('langID', 1);  
        $session = new Container('admin');
        $view->admin = $session;  
        $view->Config = $this->config;        
        $view->urlFile = $this->config['amazon_s3']['urlFile'];
        $view->ar_status = $this->config['contract_status'];   
        //$session = new Contract(); 
        return $view;       
    } 
    
    
    ################################################################################   
    public function testAction() 
    {  
        $view = $this->basic();  
        
        //$container = new Container('namespace');
        //$container->item = 'foo';
        
        $container = new Container('ok');
        $container->key = 'ok';
        
        if(!empty($container->key)){ 
           echo "Test OK";  
        }else{
            $container->key = 'foo';  
        }
        
        echo $container->key;
        
        /*
        $sessionManager = new SessionManager(); 
        $sessionManager->rememberMe($time);
        
        // i want to keep track of my user id too
        $populateStorage = array('user_id' => 555);
        $storage = new ArrayStorage($populateStorage); 
        $sessionManager->setStorage($storage); 
         */
         //$_SESSION['user']['username'] = 2000;
        //var_dump($_SESSION); 
        //exit;
        //return $view; 
    }
    
################################################################################   
    public function indexAction() 
    {  
        $view = $this->basic(); 
        $login = $this->getLogin(); 
        if(empty($login)){     
            return $this->redirect()->toRoute('admin', ['action'=>'login']); 
        }
        $container = new Container('admin');
        //echo $container->id;
        return $view; 
    }
  

################################################################################   
    public function loginAction()  
    {    
        $view = $this->basic();   
        $email = $this->params()->fromPost('email', '');   
        $password = $this->params()->fromPost('password', '');
        $view->error=0; 
        if(!empty($email) && !empty($password)){ //exit; 
            $adapter = $this->adapter;   
            $sql = "SELECT id, name, level FROM admin WHERE email='".$email."' AND password ='".md5($email.$password)."' AND active='1' LIMIT 1";
            $statement = $adapter->query($sql);     
            $results = $statement->execute();
            $row = $results->current();  
            if($row['id'] && $row['name']){    
                $this->setSession($row);    
                return $this->redirect()->toRoute('admin', ['action'=>'index']);
            }else{
                $view->error=1;  
            }  
        }   
        $login = $this->getLogin(); 
        if(!empty($login)){  
            return $this->redirect()->toRoute('admin', ['action'=>'index']);
        }  
        $view->email=$email;      
        return $view;  
    }
    

################################################################################   
    public function getLogin()  
    {   
        $container = new Container('admin');
        $login = false;
        $sid = $container->id;
        if(!empty($sid))
        {
            /*if (!preg_match('/^[a-z0-9]{32}$/', $sid))
            {
                $login = false;
            }
            else
            {*/
                $login = true;
            //}
        }
        return $login;   
    }
    
################################################################################   
    public function setSession($session=array())  
    {  
        $container = new Container('admin'); 
        foreach( $session as $key=>$value )
        {
            $container->$key = $value;
            //$container->$key = preg_match('/^[a-z0-9]{32}$/', $container->$key);
        }  
    } 
    
   
################################################################################   
    public function logoutAction() 
    {  
        $container = new Container('admin'); 
        unset($container->id); 
        unset($container->name);  
        $container->id = ''; 
        $container->name = '';  
        session_destroy(); 
        return $this->redirect()->toRoute('admin', ['action'=>'login']);
    }    
    
################################################################################   
    public function adminAction() 
    {  
        $view = $this->basic(); 
        $task = $this->params()->fromQuery('task', '');
        $draw = $this->params()->fromQuery('draw', 0); 
		$pagestart = $this->params()->fromQuery('start', 0);
		$pageshow = $this->params()->fromQuery('length', 50); 
		$search = $this->params()->fromQuery('search', '%'); 
		$admin = $this->params()->fromPost('admin', []); 
		$active = $this->params()->fromPost('active', 0); 
		$admin['active']=$active; 
		$email = $this->params()->fromPost('email', ''); 
		 
		if(is_array($search))$search=$search=$search['value'];
        $view->task = $task;
        
        $login = $this->getLogin(); 
        if(empty($login)){ 
            return $this->redirect()->toRoute('admin', ['action'=>'login']); 
        }  
        
        $adapter = $this->adapter; 
        $Admin = new Admin($adapter, $view->lang, $view->action, $view->id, $pagestart, $pageshow);
             
        if($task=='list'){  
            $data = $Admin->getList($search);  
    		$ar_data = array('draw'=>$draw,'recordsTotal'=>$data['total'],'recordsFiltered'=>$data['total'],"data"=>$data['data']); 
    		echo $this->makeJSON($ar_data); 
            exit;  
        }elseif($task=='add' && count($admin)>0 && !empty($admin['name']) && !empty($admin['email'])){ 
            $id = $Admin->getNextId();  
            $view->id = $id;  
            $admin['id'] = $id;
            $Admin->add($admin);  
            
            if(!empty($_FILES['pic']['name'])){ 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic'], 'admin'); 
                if(!empty($filename)){ 
                    $Admin->updateIMG($view->id, $filename);  
                } 
			} 
			return $this->redirect()->toRoute('admin', ['action'=>'admin']);
            //exit; 
        }elseif($task=='edit' && !empty($view->id) && count($admin)>0 && !empty($admin['name']) && !empty($admin['email'])){
            $Admin->edit($admin); 
            if(!empty($admin['password'])) $Admin->updatePassword($admin['password']);
            
            if(!empty($_FILES['pic']['name'])){ 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic'], 'admin');  
                if(!empty($filename)){
                    $detail = $Admin->getDetail($view->id);   
                    if(!empty($detail['image'])){  
                        $this->DeleteS3($view->Config['amazon_s3'], 'admin/'.$detail['image']);  
                    }      
                    $Admin->updateIMG($view->id, $filename); 
                } 
			} 
            $view->detail = $Admin->getDetail($view->id); 
            return $this->redirect()->toRoute('admin',['action'=>'admin'],['query'=>['task'=>'edit']],['id'=>$view->id]);      
        }elseif($task=='edit' && !empty($view->id)){ 
            $view->detail = $Admin->getDetail($view->id);   
        }elseif($task=='del' && !empty($view->id)){
            $detail = $Admin->getDetail($view->id);    
            if(!empty($detail['image'])){    
                $this->DeleteS3($view->Config['amazon_s3'], 'admin/'.$detail['image']); 
            } 
            $Admin->del();
            return $this->redirect()->toRoute('admin',['action'=>'admin']); 
        }elseif($task=='checkEmail' && !empty($email)){
            echo $Admin->checkEmail($email);       
            exit;  
        } 
        return $view;
    } 
    

################################################################################   
    public function usersAction()  
    {  
        $view = $this->basic(); 
        $task = $this->params()->fromQuery('task', '');
        $draw = $this->params()->fromQuery('draw', 0); 
		$pagestart = $this->params()->fromQuery('start', 0);
		$pageshow = $this->params()->fromQuery('length', 50); 
		$search = $this->params()->fromQuery('search', '%'); 
		$user = $this->params()->fromPost('user', []); 
		$active = $this->params()->fromPost('active', 0); 
		$user['active']=$active; 
		$email = $this->params()->fromPost('email', '');  
		 
		if(is_array($search))$search=$search=$search['value'];
        $view->task = $task;
        
        $login = $this->getLogin(); 
        if(empty($login)){ 
            return $this->redirect()->toRoute('admin', ['action'=>'login']); 
        }  
        
        $adapter = $this->adapter; 
        $Users = new Users($adapter, $view->lang, $view->action, $view->id, $pagestart, $pageshow);
             
        if($task=='list'){  
            $data = $Users->getList($search);
    		$ar_data = array('draw'=>$draw,'recordsTotal'=>$data['total'],'recordsFiltered'=>$data['total'],"data"=>$data['data']); 
    		echo $this->makeJSON($ar_data);  
            exit;  
        }elseif($task=='add' && count($user)>0 && !empty($user['name']) && !empty($user['email'])){ 
            $id = $Users->getNextId();  
            $view->id = $id;  
            $user['id'] = $id;   
            $Users->add($user);    
            
            if(!empty($_FILES['pic']['name'])){ 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic'], 'users');  
                if(!empty($filename)){   
                    $Users->updateIMG($view->id, $filename); 
                } 
                 
			}
			return $this->redirect()->toRoute('admin', ['action'=>'users']);
            //exit;
        }elseif($task=='edit' && !empty($view->id) && count($user)>0 && !empty($user['name']) && !empty($user['email'])){ 
            $Users->edit($user); 
            if(!empty($user['password'])) $Users->updatePassword($user['password']);
            
            if(!empty($_FILES['pic']['name'])){ 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic'], 'users');  
                if(!empty($filename)){
                    $detail = $Users->getDetail($view->id);   
                    if(!empty($detail['image'])){  
                        $this->DeleteS3($view->Config['amazon_s3'], 'users/'.$detail['image']);  
                    }      
                    $Users->updateIMG($view->id, $filename); 
                } 
			}  
            $view->detail = $Users->getDetail($view->id); 
            return $this->redirect()->toRoute('admin',['action'=>'users'],['query'=>['task'=>'edit']],['id'=>$view->id]);      
        }elseif($task=='edit' && !empty($view->id)){ 
            $view->detail = $Users->getDetail($view->id);   
        }elseif($task=='del' && !empty($view->id)){
            $detail = $Users->getDetail($view->id);     
            if(!empty($detail['image'])){    
                $this->DeleteS3($view->Config['amazon_s3'], 'users/'.$detail['image']); 
            } 
            $Users->del();
            return $this->redirect()->toRoute('admin',['action'=>'users']); 
        }elseif($task=='checkEmail' && !empty($email)){
            echo $Users->checkEmail($email);       
            exit;  
        }elseif($task=='company' && !empty($view->id)){  
            $view->detail = $Users->getDetail($view->id); 
            if(!empty($user['company_name']) && !empty($user['company_email'])){
                $Users->editCompany($user); 
                return $this->redirect()->toRoute('admin', ['action'=>'users'],['query'=>['task'=>'company']],['id'=>$view->id]); 
            }  
        } 
        return $view;
    } 

    
    
################################################################################   
    public function blogAction() 
    {  
        $view = $this->basic(); 
        $task = $this->params()->fromQuery('task', '');
        $draw = $this->params()->fromQuery('draw', 0); 
		$pagestart = $this->params()->fromQuery('start', 0);
		$pageshow = $this->params()->fromQuery('length', 50); 
		$search = $this->params()->fromQuery('search', '%'); 
		$blog = $this->params()->fromPost('blog', []); 
		$active = $this->params()->fromPost('active', 0);
		$imgID = $this->params()->fromQuery('imgID', '');   
		$blog['active']=$active;  
		$name = $this->params()->fromPost('name', '');  
		
		
		if(is_array($search))$search=$search=$search['value'];
        $view->task = $task;
        
        $login = $this->getLogin(); 
        if(empty($login)){     
            return $this->redirect()->toRoute('admin',['action'=>'login']);   
        }  
        
        $adapter = $this->adapter; 
        $Models = new Blog($adapter, $view->lang, $view->action, $view->id, $pagestart, $pageshow);
        if($task=='checkName' && !empty($name)){ 
            $name = str_replace("'",'%', $name);
            echo $Models->checkName($name);  
            exit;  
        }else if($task=='list'){    
            $data = $Models->getList($search);  
    		$ar_data = array('draw'=>$draw,'recordsTotal'=>$data['total'],'recordsFiltered'=>$data['total'],"result"=>$data['data']); 
    		echo $this->makeJSON($ar_data); 
            exit;   
        }elseif($task=='add' && count($blog)>0 && !empty($blog['name'])){
            try{
                $id = $Models->getNextId(); 
                $view->id = $id;   
                $blog['id'] = $id;  
                $Models->add($blog); 
            }catch( Exception $e ){
                return $this->redirect()->toRoute('admin',['action'=>'blog'],['query'=>['task'=>'add']]); 
            } 
            if(!empty($_FILES['pic']['name']))
            { 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic'], 'blog'); 
                if(!empty($filename)){
                    $Models->updateIMG($view->id, $filename); 
                }
    		} 
    		return $this->redirect()->toRoute('admin',['action'=>'blog']);
             
        }
        elseif($task=='edit' && !empty($view->id) && count($blog)>0 && !empty($blog['name']))
        {
            try{
                $Models->edit($blog);  
            }catch( Exception $e ){
                return $this->redirect()->toRoute('admin',['action'=>'blog'],['query'=>['task'=>'edit']],['id'=>$view->id]);
            }
            if(!empty($_FILES['pic']['name']))
            { 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic'], 'blog'); 
                if(!empty($filename)){
                    $detail = $Models->getDetail($view->id);
                    if(!empty($detail['img'])){  
                        $this->DeleteS3($view->Config['amazon_s3'], 'blog/'.$detail['img']); 
                    }  
                    $Models->updateIMG($view->id, $filename); 
                }  
			}  
            $view->detail = $Models->getDetail($view->id); 
            return $this->redirect()->toRoute('admin',['action'=>'blog'],['query'=>['task'=>'edit']],['id'=>$view->id]);
        }elseif($task=='edit' && !empty($view->id)){ 
            $view->detail = $Models->getDetail($view->id);    
        }elseif($task=='del' && !empty($view->id)){ 
            $detail = $Models->getDetail($view->id);
            if(!empty($detail['img'])){    
                $this->DeleteS3($view->Config['amazon_s3'], 'blog/'.$detail['img']);
            } 
            $ListImgBlog = $Models->getListImgBlog(); 
            foreach($ListImgBlog as $key=>$value){ 
               if(!empty($value['image'])){     
                   $this->DeleteS3($view->Config['amazon_s3'], 'blog/'.$value['image']); 
               } 
            }
            $Models->del(); 
            $Models->delImgBlogAll(); 
            return $this->redirect()->toRoute('admin',['action'=>'blog']);
        }else if($task=='image' && !empty($view->id)){ 
            if(!empty($_FILES["file"])){
                $id = $Models->getNextIdImgBlog();  
                $imgblog['id'] = $id;  
                $imgblog['blog_id'] = $view->id;  
                $Models->addImgBlog($imgblog);  
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['file'], 'blog'); 
                if(!empty($filename)){  
                    $Models->updateIMGBlog($id, $filename);
                    echo $filename; 
                }  
                exit;     
            } 
            $view->detail = $Models->getDetail($view->id); 
            $view->imageList = $Models->getListImgBlog();     
            //exit;  
        }else if($task=='delimage' && !empty($imgID)){ 
            $detail = $Models->getDetailImgBlog($imgID);
            if(!empty($detail['image'])){   
                $this->DeleteS3($view->Config['amazon_s3'], 'blog/'.$detail['image']); 
                $Models->delImgBlog($imgID);         
                echo "delete";     
            }   
            exit;  
        }
        return $view;
    }
    
    
################################################################################   
    public function contractAction()   
    {  
        $view = $this->basic(); 
        $task = $this->params()->fromQuery('task', '');
        $draw = $this->params()->fromQuery('draw', 0); 
		$pagestart = $this->params()->fromQuery('start', 0);
		$pageshow = $this->params()->fromQuery('length', 50); 
		$search = $this->params()->fromQuery('search', '%'); 
		$contract = $this->params()->fromPost('contract', []); 
		$active = $this->params()->fromPost('active', 0);
		$imgID = $this->params()->fromQuery('imgID', '');   
		$view->result = $this->params()->fromQuery('result', ''); 
		
		if(is_array($search))$search=$search=$search['value']; 
        $view->task = $task; 
        
        $login = $this->getLogin(); 
        if(empty($login)){     
            return $this->redirect()->toRoute('admin',['action'=>'login']);   
        } 
        
        $adapter = $this->adapter;  
        $Models = new Contract($adapter, $view->lang, $view->action, $view->id, $pagestart, $pageshow);
        $Feeds = new Feeds($view->lang, $view->id, $pagestart, $pageshow);
        $Models->ar_status = $view->ar_status; 
        $percent = 5;    
        $Models->service_percent = ($percent/100); 
        //print_r($view->ar_status);  //exit;   
        $view->status = $view->ar_status;
           
        if($task=='list'){    
            $data = $Models->getList($search);  
    		$ar_data = array('draw'=>$draw,'recordsTotal'=>$data['total'],'recordsFiltered'=>$data['total'],"data"=>$data['data']); 
    		echo $this->makeJSON($ar_data); 
            exit;      
        }else if($task=='payoutDone'){ 
        
            $cid = $this->params()->fromQuery('cid', 0);
            $data['sstatus'] = 3; 
            $rs = $Feeds->statusDone($cid, $data);
            print_r($rs);     
            exit;     
        }else if($task=='payout'){ //exit; 
        
            $contract_id = $this->params()->fromQuery('cid', '');
            $user_id = $this->params()->fromQuery('uid', ''); 
            $mangopay_id = $this->params()->fromQuery('mangopay_id', '');
            $walletId = $this->params()->fromQuery('walletId', ''); 
            $bankID = $this->params()->fromQuery('bankID', ''); 
            
            $contract = $Feeds->getContract($contract_id);
            $items = $contract->items;  
            
            /*
            echo 'who_pay_fee :'.$items->who_pay_fee."<br>";  
            echo 'pay_price :'.$items->pay_price."<br>"; 
            echo 'total_price :'.$items->total_price."<br>";
            echo 'transfer_price :'.$items->transfer_price."<br>"; 
            */
            
            //0=buyer,1=seller,2=5050
            if($items->who_pay_fee==2){
                $fee = $items->pay_price-$items->total_price;
                $fee = ($fee/2);
                $amount = $items->total_price-$fee; 
            }else if($items->who_pay_fee==1){
                $fee = $items->pay_price-$items->transfer_price; 
                $amount = $items->total_price-$fee;  
            }else{ 
                $fee = $items->pay_price-$items->total_price;
                $amount = $items->total_price; 
            }   
            //echo $walletId; exit;
            /*  
            echo '<br>Fee :'.$fee."<br>";
            echo 'Amount :'.$amount."<br>";
            exit; 
            print_r(json_encode($items));         
            exit;   */    
            $rs = $Feeds->payout($contract_id, $user_id, $mangopay_id, $walletId, $amount, $bankID); 
            print_r(json_encode($rs));    
            exit;     
        }else if($task=='add' && count($contract)>0 && !empty($contract['total_price'])){ 
            try{
                $id = $Models->getNextId();  
                $view->id = $id;   
                $contract['id'] = $id;  
                $Models->add($contract);  
            }catch( Exception $e ){
                return $this->redirect()->toRoute('admin',['action'=>'contract'],['query'=>['task'=>'add']]); 
            } 
            if(!empty($_FILES['pic']['name']))
            { 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic'], 'contract'); 
                if(!empty($filename)){
                    $Models->updateIMG($view->id, $filename); 
                }
    		}  
    		return $this->redirect()->toRoute('admin',['action'=>'contract']);
             
        }
        elseif($task=='edit' && !empty($view->id) && count($contract)>0 && !empty($contract['total_price']))
        {
            try{
                $Models->edit($contract);  
            }catch( Exception $e ){
                return $this->redirect()->toRoute('admin',['action'=>'contract'],['query'=>['task'=>'edit']],['id'=>$view->id]);
            }
            if(!empty($_FILES['pic']['name']))
            { 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic'], 'contract'); 
                if(!empty($filename)){
                    $detail = $Models->getDetail($view->id);
                    if(!empty($detail['contract_img'])){  
                        $this->DeleteS3($view->Config['amazon_s3'], 'contract/'.$detail['contract_img']);   
                    }  
                    $Models->updateIMG($view->id, $filename); 
                }  
			} 
			if(!empty($_FILES['pic2']['name']))
            { 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic2'], 'contract'); 
                if(!empty($filename)){
                    $detail = $Models->getDetail($view->id);
                    if(!empty($detail['contract_img2'])){  
                        $this->DeleteS3($view->Config['amazon_s3'], 'contract/'.$detail['contract_img2']);   
                    }  
                    $Models->updateIMG($view->id, $filename, 2); 
                }  
			} 
			if(!empty($_FILES['pic3']['name']))
            { 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic3'], 'contract'); 
                if(!empty($filename)){
                    $detail = $Models->getDetail($view->id);
                    if(!empty($detail['contract_img3'])){  
                        $this->DeleteS3($view->Config['amazon_s3'], 'contract/'.$detail['contract_img3']);   
                    }  
                    $Models->updateIMG($view->id, $filename, 3); 
                }  
			} 
			if(!empty($_FILES['pic4']['name']))
            { 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic4'], 'contract'); 
                if(!empty($filename)){
                    $detail = $Models->getDetail($view->id);
                    if(!empty($detail['contract_img4'])){  
                        $this->DeleteS3($view->Config['amazon_s3'], 'contract/'.$detail['contract_img4']);   
                    }  
                    $Models->updateIMG($view->id, $filename, 4); 
                }  
			} 
			if(!empty($_FILES['pic5']['name']))
            { 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic5'], 'contract'); 
                if(!empty($filename)){
                    $detail = $Models->getDetail($view->id);
                    if(!empty($detail['contract_img5'])){  
                        $this->DeleteS3($view->Config['amazon_s5'], 'contract/'.$detail['contract_img5']);   
                    }  
                    $Models->updateIMG($view->id, $filename, 5); 
                }  
			} 
			if(!empty($_FILES['pic6']['name']))
            { 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic6'], 'contract'); 
                if(!empty($filename)){
                    $detail = $Models->getDetail($view->id);
                    if(!empty($detail['contract_img6'])){  
                        $this->DeleteS3($view->Config['amazon_s3'], 'contract/'.$detail['contract_img6']);   
                    }  
                    $Models->updateIMG($view->id, $filename, 6); 
                }  
			} 
			if(!empty($_FILES['pic7']['name']))
            { 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic7'], 'contract'); 
                if(!empty($filename)){
                    $detail = $Models->getDetail($view->id);
                    if(!empty($detail['contract_img7'])){  
                        $this->DeleteS3($view->Config['amazon_s3'], 'contract/'.$detail['contract_img7']);   
                    }  
                    $Models->updateIMG($view->id, $filename, 7); 
                }  
			} 
			if(!empty($_FILES['pic8']['name']))
            { 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic8'], 'contract'); 
                if(!empty($filename)){
                    $detail = $Models->getDetail($view->id);
                    if(!empty($detail['contract_img8'])){  
                        $this->DeleteS3($view->Config['amazon_s3'], 'contract/'.$detail['contract_img8']);   
                    }  
                    $Models->updateIMG($view->id, $filename, 8); 
                }  
			} 
			if(!empty($_FILES['pic9']['name']))
            { 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic9'], 'contract'); 
                if(!empty($filename)){
                    $detail = $Models->getDetail($view->id);
                    if(!empty($detail['contract_img9'])){  
                        $this->DeleteS3($view->Config['amazon_s3'], 'contract/'.$detail['contract_img9']);   
                    }  
                    $Models->updateIMG($view->id, $filename, 9); 
                }    
			} 
            $view->detail = $Models->getDetail($view->id); 
            //return $this->redirect()->toRoute('admin',['action'=>'contract'],['query'=>['task'=>'edit','result'=>'success']],['id'=>$view->id]);
        }elseif($task=='edit' && !empty($view->id)){ 
            $view->detail = $Models->getDetail($view->id);    
        }elseif($task=='del' && !empty($view->id)){   
            $detail = $Models->getDetail($view->id);
            if(!empty($detail['contract_img'])){    
                $this->DeleteS3($view->Config['amazon_s3'], 'contract/'.$detail['contract_img']);
            }    
            $Models->del();   
            return $this->redirect()->toRoute('admin',['action'=>'contract']);
        }  
        return $view;
    }
    
################################################################################   
    public function uploadImageAction()  
    {  
        $view = $this->basic();
        $login = $this->getLogin(); 
        if(empty($login)){
            return $this->redirect()->toRoute('admin',['action'=>'login']); 
        } 
        $url_file = $this->params()->fromPost('urlFile', '');
        if(!empty($_FILES['image']['name'])){ 
            $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['image'], 'general'); 
            $pathImg = $view->Config['amazon_s3']['urlFile'].'/general/'.$filename;    
    		print_r($pathImg); 
    	}
    	if($view->act=='delimg'){
    	    $pathImg = pathinfo($url_file, PATHINFO_BASENAME);
    	    $this->DeleteS3($view->Config['amazon_s3'], 'general/'.$pathImg); 
    	    print_r('ok'); 
    	}
    	exit; 
    }       
    
    
################################################################################   
    public function settingAction() 
    {  
        $view = $this->basic();  
        $pagestart = $this->params()->fromQuery('start', 0);
		$pageshow = $this->params()->fromQuery('length', 50);
		$setting = $this->params()->fromPost('setting', []); 
		
		$login = $this->getLogin(); 
        if(empty($login)){   
            return $this->redirect()->toRoute('admin',['action'=>'login']);
        }  
        //Config
        $Config = $this->config;
        $view->language = $Config['language']; 
        
        $adapter = $this->adapter; 
        $Models = new Setting($adapter, $view->lang, $view->action, $view->id, $pagestart, $pageshow);
        if(empty($view->id))$view->id=1;
        $settingData = $Models->getDetail($view->langID);    
        
        if(count($setting)>0 && !empty($setting['name']) && !empty($view->id) && !empty($view->langID)){ 
           
            if(!empty($settingData['name']) && !empty($settingData['lang_id'])){
                $Models->edit($setting);    
            }else{
                $id = $Models->getNextId();  
                $view->id = $id;    
                $setting['id'] = $id;  
                $setting['lang_id'] = $view->langID; 
                $Models->add($setting);  
            }
             
            if(!empty($_FILES['pic']['name'])){ 
                $filename = $this->UploadS3($view->Config['amazon_s3'], $_FILES['pic'], 'setting');   
                if(!empty($filename)){   
                    if(!empty($settingData['logo'])){   
                        $this->DeleteS3($view->Config['amazon_s3'], 'setting/'.$settingData['logo']);  
                    }       
                    $Models->updateIMG($view->id, $filename);   
                }   
			} 
			return $this->redirect()->toRoute('admin',['action'=>'setting'],['query'=>['langID'=>$view->langID]]); 
        }  
        $view->id = $settingData['id']; 
        $view->setting = $settingData;
        return $view;
    }    
       

    public function UploadS3($Config, $FILES, $folder='blog', $resize=480){      
        $filename = '';
        $s3 = new S3Client($Config['config']);    
        // Upload an object to Amazon S3 
        $bucket = $Config['bucket'];//'starter-kit-rockstar';    
        try   
        {  
            $filename = explode(".", $FILES["name"]);  
            $filenameext = strtolower($filename[count($filename)-1]);
            $filename = 'img_' . time().rand(1000,10000); 
            if($folder=='contract') $filename = 'file_' . time().rand(1000,10000);    
            $SourceFile = $FILES['tmp_name']; 
            $pathUpload = 'public/temp'; 
            if(!empty($FILES["name"])){  
        		$upload = new Upload($FILES); 
        		if ($upload->uploaded) {      
        		   $upload->file_new_name_body   = $filename; 
        		   $upload->dir_auto_create 	 = true;
        		   $upload->dir_auto_chmod		 = true;
        		   $upload->file_overwrite		 = true;    
        		   $upload->file_new_name_ext	 = $filenameext;   
        		   $upload->image_resize         = true;
        		   $upload->image_x              = $resize;   
        		   $upload->image_ratio_y        = true;     
        		   $upload->process($pathUpload); 
        		   if ($upload->processed) {      
        		      $upload->clean();       
        		      $SourceFile = $pathUpload."/".$filename."." . $filenameext;
        		   }else{ 
				     //print_r($upload->error);
				     //exit(0);     
				   } 
        		}     
        	}       
            
            $result = $s3->putObject(array(
                'Bucket' => $bucket, 
                'Key' => $folder.'/'.$filename."." .$filenameext,  
                'ACL' => 'public-read',     
                'SourceFile' => $SourceFile,    
                //'CacheControl'=>'max-age=3600',         
                'Expires'=> (string)(1000+(int)date("Y")),                       
                'ContentType'=>'image/'.$filenameext,      
            ));       
            @unlink($SourceFile);     
        } catch (S3Exception $e) {    
            // Catch an S3 specific exception.
            echo "<pre>";
            echo $e->getMessage();
            echo "</pre>";
            exit;    
        } 
        return $filename."." .$filenameext;  
    }
     
    
    public function DeleteS3($Config, $keyname=''){   
        if(!empty($keyname)){
            $s3 = new S3Client($Config['config']);   
            // Upload an object to Amazon S3 
            $bucket = $Config['bucket'];//'starter-kit-rockstar';
            
            $result = $s3->deleteObject(array( 
                'Bucket' => $bucket,
                'Key'    => $keyname
            ));
        } 
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
     
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
################################################################################   
    public function newAction()
    { 
        $view = $this->basic();
		$feedses = new Feeds($view->lang, $view->id, $view->page);
        $view->data = $feedses->getContent('new');
        return $view;
    }
################################################################################   
    public function detailAction()
    { 
        $view = $this->basic();
		$feedses = new Feeds($view->lang, $view->id, $view->page);
        $view->dataDetail = $feedses->getDetail(); //print_r($view->dataDetail);
        return $view;
    }
################################################################################   
    public function channelsAction()
    {
        $view = $this->basic();
		$feedses = new Feeds($view->lang, $view->id, $view->page);
		if($view->id)
		{
			$view->dataChannel = $feedses->getChannel();
		}
		else
		{
        	$view->dataChannels = $feedses->getChannels();
		}
        return $view;
    }
################################################################################   
    public function makeJSON($data)
    {
        $json = json_encode($data);
        return ($json);
    }
################################################################################   
    public function fabsquadAction()
    { 
        $adapter = $this->adapter;
        
        $statement = $adapter->query('SELECT * FROM user where id = 1');
        $results = $statement->execute();
        $row = $results->current();  print_r($row); 
        $name = $row['name'];
        
        
        $statement = $adapter->query('SELECT * FROM user LIMIT 0, 10');
        $result = $statement->execute(); //print_r($result);
        //$row = $result->current();  print_r($row);
        $resultSet = new ResultSet;
        $ok = $resultSet->initialize($result);
        print_r($ok->toArray());
    }
    


################################################################################   
    public function paysupplierAction()   
    {  
        $view = $this->basic(); 
        $task = $this->params()->fromQuery('task', '');
        $draw = $this->params()->fromQuery('draw', 0); 
		$pagestart = $this->params()->fromQuery('start', 0);
		$pageshow = $this->params()->fromQuery('length', 50); 
		$search = $this->params()->fromQuery('search', '%'); 
		$contract = $this->params()->fromPost('contract', []); 
		$active = $this->params()->fromPost('active', 0);
		$imgID = $this->params()->fromQuery('imgID', '');   
		$view->result = $this->params()->fromQuery('result', ''); 
		
		$view->title = 'Pay the supplier';   
		
		if(is_array($search))$search=$search=$search['value']; 
        $view->task = $task;  
        
        $login = $this->getLogin();  
        if(empty($login)){     
            return $this->redirect()->toRoute('admin',['action'=>'login']);   
        }  
        
        $adapter = $this->adapter;   
        $Models = new Payment($adapter, $view->lang, $view->action, $view->id, $pagestart, $pageshow);
        $Models->ar_status = $view->ar_status;
        $percent = 5;    
        $Models->service_percent = ($percent/100); 
        $view->status = $view->ar_status;
        
        if($task=='list'){     
            $data = $Models->getPay_Sup_List($search);        
    		$ar_data = array('draw'=>$draw,'recordsTotal'=>$data['total'],'recordsFiltered'=>$data['total'],"data"=>$data['data']); 
    		echo $this->makeJSON($ar_data);   
            exit;       
        }elseif($task=='pay' && !empty($view->id)){ 
            // 3 = done
            echo $Models->editStatus(3);        
            exit;  
        }   
        return $view;
    }
    
################################################################################   
    public function paybuyerAction()   
    {  
        $view = $this->basic(); 
        $task = $this->params()->fromQuery('task', '');
        $draw = $this->params()->fromQuery('draw', 0); 
		$pagestart = $this->params()->fromQuery('start', 0);
		$pageshow = $this->params()->fromQuery('length', 50); 
		$search = $this->params()->fromQuery('search', '%'); 
		$contract = $this->params()->fromPost('contract', []); 
		$active = $this->params()->fromPost('active', 0);
		$imgID = $this->params()->fromQuery('imgID', '');   
		$view->result = $this->params()->fromQuery('result', ''); 
		
		$view->title = 'Pay the buyer';   
		
		if(is_array($search))$search=$search=$search['value']; 
        $view->task = $task;  
        
        $login = $this->getLogin();  
        if(empty($login)){     
            return $this->redirect()->toRoute('admin',['action'=>'login']);   
        }  
        
        $adapter = $this->adapter;   
        $Models = new Payment($adapter, $view->lang, $view->action, $view->id, $pagestart, $pageshow);
        $Feeds = new Feeds($view->lang, $view->id, $view->page);
        $Models->ar_status = $view->ar_status;  
        $percent = 5;     
        $Models->service_percent = ($percent/100); 
        $view->status = $view->ar_status;
        
        if($task=='list'){     
            $data = $Models->getPay_Sup_List($search);        
    		$ar_data = array('draw'=>$draw,'recordsTotal'=>$data['total'],'recordsFiltered'=>$data['total'],"data"=>$data['data']); 
    		echo $this->makeJSON($ar_data);   
            exit;       
        }elseif($task=='pay' && !empty($view->id)){ 
            // 5 = paid
            
            $rs = $Feeds->paybuyer(['rs'=>'Success']); 
            print_r($rs);       
            exit;   
        }   
        return $view;
    }


################################################################################   
    public function payrefundAction()   
    {  
        $view = $this->basic(); 
        $task = $this->params()->fromQuery('task', '');
        $draw = $this->params()->fromQuery('draw', 0); 
		$pagestart = $this->params()->fromQuery('start', 0);
		$pageshow = $this->params()->fromQuery('length', 50); 
		$search = $this->params()->fromQuery('search', '%'); 
		$contract = $this->params()->fromPost('contract', []); 
		$active = $this->params()->fromPost('active', 0);
		$imgID = $this->params()->fromQuery('imgID', '');   
		$view->result = $this->params()->fromQuery('result', ''); 
		$view->id = $this->params()->fromQuery('contract_id', 0); 
		//$view->id; exit; 
		$view->title = 'Refund';    
		
		if(is_array($search))$search=$search=$search['value']; 
        $view->task = $task;  
        
        $login = $this->getLogin();   
        if(empty($login)){     
            return $this->redirect()->toRoute('admin',['action'=>'login']);   
        }  
        
        $adapter = $this->adapter;   
        $Models = new Payment($adapter, $view->lang, $view->action, $view->id, $pagestart, $pageshow);
        $Feeds = new Feeds($view->lang, $view->id, $pagestart, $pageshow); 
        $Models->ar_status = $view->ar_status;
        $percent = 5;    
        $Models->service_percent = ($percent/100); 
        $view->status = $view->ar_status;
        
        if($task=='list'){      
            $data = $Models->getRefund_List($search);         
    		$ar_data = array('draw'=>$draw,'recordsTotal'=>$data['total'],'recordsFiltered'=>$data['total'],"data"=>$data['data']); 
    		echo $this->makeJSON($ar_data);   
            exit;        
        }elseif($task=='refund' && !empty($view->id)){ 
            
            $contract = $Feeds->getContract($view->id);
            $items = $contract->items;
            
            $profile = $Feeds->getProfile($items->buyer_id);
            $itemPro = $profile->items;   
            //print_r(json_encode($items)); exit;
              
            $mangopay_id = $itemPro->mangopay_id;
            $amount = $items->transfer_price;
            $payIn_id = $items->payIn_id; 
            
            /* 
            echo $amount; 
            exit; */  
            $rs = $Feeds->refund($view->id, $mangopay_id, $payIn_id, $amount, 0); 
            $Refund = Json::decode($rs); 
            if($Refund->Status=='SUCCEEDED') 
            {      
                $sql = $this->adapter->query("UPDATE zenovly SET status = '7', refund_id=".$Refund->Id.", last_update = NOW() WHERE id = ".$view->id);  
                if($sql->execute()){   
                    $Feeds->sendEmailRefund($view->id, $items->buyer_id); 
                } 
            } 
             
            print_r($rs); 
            
            //$rs = $Feeds->getService();
            //print_r($rs);               
            // 7 = refund      
            //echo $Models->editStatus(7);        
            exit;  
        }   
        return $view;
    }


################################################################################   
}
