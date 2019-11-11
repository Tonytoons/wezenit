<?php
namespace Application\Models;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Memcached;
use Zend\Cache\Storage\StorageInterface;
use Zend\Json\Json;

use Zend\Http\Client; 
use Zend\Http\Request;

class Feeds
{
    protected $feedses;
    public $lang;
    public $id; 
    public $apiURL;
    private $config;
    
################################################################################ 
	function __construct($inLang='fr', $inID, $inPage=1, $noCache=0)
    {  
        $this->cacheTime = 3600;
        $this->lang = $inLang;
        $this->id = $inID;
        $this->page = $inPage;
        $this->perpage = 21;
        $this->pageStart = ($this->perpage*($this->page-1));
        $this->now = date('Y-m-d H:i');
        $this->ip = '';
        if (getenv('HTTP_CLIENT_IP'))
        {
            $this->ip = getenv('HTTP_CLIENT_IP');
        }
        else if(getenv('HTTP_X_FORWARDED_FOR'))
        {
            $this->ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        else if(getenv('HTTP_X_FORWARDED'))
        {
            $this->ip = getenv('HTTP_X_FORWARDED');
        }
        else if(getenv('HTTP_FORWARDED_FOR'))
        {
            $this->ip = getenv('HTTP_FORWARDED_FOR');
        }
        else if(getenv('HTTP_FORWARDED'))
        {
            $this->ip = getenv('HTTP_FORWARDED'); 
        }
        else if(getenv('REMOTE_ADDR'))
        {
            $this->ip = getenv('REMOTE_ADDR');
        }
        else
        {
            $this->ip = 'UNKNOWN';
        }    
         
        //$this->apiURL = 'https://safe-tonytoons.c9users.io/public/api';  
		$this->config = include __DIR__ . '../../../../config/module.config.php'; 
		$this->apiURL = $this->config['Api_url'];   
		$this->noCache = $noCache; 
		
		$host_name = (isset($_SERVER['HTTPS']) ? "https" : "https") . "://".$_SERVER['HTTP_HOST'];
		$this->webURL = $host_name;
		$this->mangopayAPI = $host_name.'/mangopay/t.php?';
    }
    
    
    function Apiservice($service='', $parameter = [], $type='get', $para_get='')
    {   
        $para = '';
        if($type=='get'){
            foreach ($parameter as $key=>$value) {
                $para .= '&'.$key.'='.$value; 
            }  
        }  
        
        $url = $this->apiURL.'/'.$this->lang.'/'.$service.'/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].$para.$para_get.'&page='.$this->page.'&nocache='.$this->noCache.'&rd='.strtotime($this->now); 
        //echo $url; exit;     
        $client = new Client($url, array(  
           'adapter' => 'Zend\Http\Client\Adapter\Curl',
           'sslcapath' => '/etc/ssl/certs'
        )); 
        if($type=='post'){  
           //print_r($parameter); exit;
           $client->setMethod(Request::METHOD_POST);
           $client->setParameterPost($parameter); 
        }
        $response = $client->send();  
        $body = $response->getBody();
        return $body; 
        
    }
    
    
################################################################################ 
    function getContent($type)
    { 
        $data = array(); 
        $key_txt = md5('content_type_'.$type.'_lang_'.$this->lang.'_page_'.$this->page);
        $cache = $this->maMemCache($this->cacheTime, $key_txt);
        $data = $cache->getItem($key_txt, $success);
		if( empty($data) || ($this->noCache == 1) )
		{
	        $url = $this->apiURL.'/'.$this->lang.'/content/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&for='.$type.'&page='.$this->page.'&nocache='.$this->noCache;
			$result = $this->curl($url); 
			$dataJ = Json::decode($result);
			if(@$dataJ->status == 200) 
			{
				$data = $dataJ->items;
				$cache->setItem($key_txt, $data);
			}
		}
		return($data);
    }
################################################################################ 
    function getDetail()
    {
        $data = array();
        $key_txt = md5('detail_lang_'.$this->lang.'_'.$this->id);
        $cache = $this->maMemCache($this->cacheTime, $key_txt);
        $data = $cache->getItem($key_txt, $success);
		if( empty($data) || ($this->noCache == 1) )
		{
	        $url = $this->apiURL.'/'.$this->lang.'/content/'.$this->id.'/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].''.'&nocache='.$this->noCache;
			$result = $this->curl($url); 
			$dataJ = Json::decode($result); //print_r($dataJ);
			if(@$dataJ->status == 200) 
			{
				$data = $dataJ->items;
				$cache->setItem($key_txt, $data);
			}
		}  //print_r($data);
		return($data);
    }
    
    function getLogin($email='', $password='', $fbID='')
    {  
		//$url = $this->apiURL.'/'.$this->lang.'/login/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&email='.$email.'&upassword='.$password.'&facebook_id='.$fbID.'&nocache=1&rd='.strtotime($this->now); 
		//echo $url;exit;
		$data['email']=$email; 
		$data['upassword']=$password;
		$data['facebook_id']=$fbID; 
		$result = $this->Apiservice('login/', $data, 'post'); 
		//$result = $this->curl($url); 
		$data = Json::decode($result); 
		return($data);   
    }
    
    
    function setForgotpass($email='', $password=''){ 
        if(!empty($email) && !empty($password)) {
            $sql = "UPDATE `users` 
                    SET password='".$password."'
                    WHERE email='".$email."'";
                    echo $sql;   
            //$sql = $this->adapter->query($sql);
            //return($sql->execute()); 
        }else{
           echo "error";  
        }
    }
    
    function getProfile($id='')     
    {     
		if(!empty($id)) $this->id = $id; 
		$url = $this->apiURL.'/'.$this->lang.'/profile/'.$this->id.'/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&nocache=0&rd='.strtotime($this->now);  
		
		$result = $this->curl($url);  
		$data = Json::decode($result);  
		return($data); 
    }
    
    function forgotPassword($email='')     
    {     
		$url = $this->apiURL.'/'.$this->lang.'/profile/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&act=forgotPassword&email='.$email.'&nocache=1&rd='.strtotime($this->now);  
		$result = $this->curl($url);     
	    //$data = Json::decode($result);  
		return($result);  
    }
    
    
    function getContract($id='')        
    {     
		//$url = $this->apiURL.'/'.$this->lang.'/contract/'.$this->id.'/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&nocache=1&rd='.strtotime($this->now);
		//echo $url;  
		if(!empty($id)) $this->id = $id;
		
		$url = $this->apiURL.'/'.$this->lang.'/contract/'.$this->id.'/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&nocache=1&act=zdetail&rd='.strtotime($this->now);     
		//echo $url;   
		//exit;  
		$result = $this->curl($url);   
		$data = Json::decode($result);   
		return($data);  
    }
    
    
    function sendContactUs($data)        
    {     
		//$url = $this->apiURL.'/'.$this->lang.'/mail/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&nocache=1&rd='.strtotime($this->now);  
		try
        {
           $result = $this->Apiservice('mail', $data, 'post');  
		   $data = Json::decode($result);      
        } 
        catch (Zend_Exception $e){
            $data = $e;
        } 
		return($data);         
    } 
    
    
    function getBlogList()              
    {     
		$url = $this->apiURL.'/'.$this->lang.'/content/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&nocache=1&for=new&page='.$this->page; 
		$result = $this->curl($url);      
		$data = Json::decode($result);    
		return($data);      
    }
     
    
    function getBlogListAll()              
    {     
		$url = $this->apiURL.'/'.$this->lang.'/content/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&nocache=1&act=all&for=new&page='.$this->page; 
		$result = $this->curl($url);      
		$data = Json::decode($result);    
		return($data);      
    }
    
    function getBlogRecomment()               
    {     
		$url = $this->apiURL.'/'.$this->lang.'/content/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&nocache=1&for=hot&page='.$this->page; 
		$result = $this->curl($url);      
		$data = Json::decode($result);     
		return($data);      
    } 
    
    function updateViewBlog()
    {
        $url = $this->apiURL.'/'.$this->lang.'/content/'.$this->id.'/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&for=view&rd='.strtotime($this->now);   
		$result = $this->curl($url);      
		$data = Json::decode($result);      
		return($data); 
    }
    
    
    function getSupplierPro($status=0)                   
    {     
		//$url = $this->apiURL.'/'.$this->lang.'/contract/'.$this->id.'/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&nocache=1&act=supplier&page='.$this->page.'&rd='.strtotime($this->now); 
		$url = '&act=supplier&page='.$this->page.'&rd='.strtotime($this->now); 
		if($status!='')$url .= '&status='.$status;
		try
        {    //echo $url; exit;
           $result = $this->Apiservice('contract/'.$this->id, [], 'get', $url);   
		   $data = Json::decode($result);        
        }  
        catch (\Exception $e)  
        {  
            $data = htmlentities($e->getMessage());          
        }  
		return($data);    
    }  
    
    function getContractPro($status=0)                       
    {    
        $url = '&act=consumer&status='.$status.'&page='.$this->page.'&rd='.strtotime($this->now);  
	     
		if($status!='')$url .= '&status='.$status; 
		try
        {    
           $result = $this->Apiservice('contract/'.$this->id, [], 'get', $url);   
		   $data = Json::decode($result);        
        }   
        catch (\Exception $e)  
        {  
            $data = htmlentities($e->getMessage());          
        }  
		return($data);     
    }
    
    function getContractList($status='', $act='buyer')                         
    {   
        $url = $this->apiURL.'/'.$this->lang.'/contract/'.$this->id.'/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&nocache=1&act='.$act.'&status='.$status.'&page='.$this->page.'&rd='.strtotime($this->now); 
	    
		if($status!='')$url .= '&status='.$status; 
		//echo $url; 
		$result = $this->curl($url);         
		$data = Json::decode($result);      
		return($data);         
    }     
      
    function payout($cid, $uid='', $mangopay_id='', $walletId='', $price='', $bankID='')                          
    {       
        $url = $this->apiURL.'/'.$this->lang.'/payout/'.$uid.'/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&contract_id='.$cid.'&mangopay_id='.$mangopay_id.'&walletId='.$walletId.'&amount='.$price.'&bankID='.$bankID.'&rd='.strtotime($this->now); 
	    //echo $url; exit;  
	    $result = $this->curl($url);             
		$data = Json::decode($result);         
		return($data);                      
    } 
    
    
    function refund($cid, $mangopay_id='', $payinID='', $price='', $fee=0)                          
    {      
        
        $url = $this->mangopayAPI.'act=refund&userId='.$mangopay_id.'&PayInId='.$payinID.'&amount='.$price.'&fee='.$fee.'&rd='.strtotime($this->now);
        //echo $url; exit; 
        $result = $this->getService($url); 
        //$data = Json::decode($result);   
		return($result);     
        /*
        $url = $this->apiURL.'/en/refund/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&contract_id='.$cid.'&mangopay_id='.$mangopay_id.'&payin_id='.$payinID.'&amount='.$price.'&fee='.$fee; 
	    
	    echo $url; exit; 
	    $result = $this->curl($url);
	    print_r($result); 
	    exit;   
		$data = Json::decode($result); 
		return($data);   */                    
    } 
    
    function sendEmailRefund($cid, $buyer_id='')                          
    {       
       
        $url = $this->apiURL.'/'.$this->lang.'/refundemail/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&contract_id='.$cid.'&buyer_id='.$buyer_id.'&rd='.strtotime($this->now); 
	    $result = $this->curl($url);
		//$data = Json::decode($result);  
		return($result);                     
    } 
    
  
  
    function httpPost($url, $params)
    { 
        /*
        $postData = '';  
        foreach($params as $k => $v)  
        {  
          $postData .= $k . '='.$v.'&'; 
        }
        $postData = rtrim($postData, '&');
        $ch = curl_init();   
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER, false); 
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
        $output=curl_exec($ch);  
        curl_close($ch);
        return $output;
        */ 
        return $this->getService($url, $params, 'post');
    }  
    
    
################################################################################ 
    function curl($url)
    {
        /*
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);
		
		return $data;
		*/
		return $this->getService($url);
    }
     
     
################################################################
    function maMemCache($time, $namespace)
    {
    	$cache = StorageFactory::factory([
										    'adapter' => [
										        'name' => 'filesystem',
										        'options' => [
										            'namespace' => $namespace,
										            'ttl' => $time,
										        ],
										    ],
										    'plugins' => [
										        // Don't throw exceptions on cache errors
										        'exception_handler' => [
										            'throw_exceptions' => true
										        ],
										        'Serializer',
										    ],
										]);
		return($cache);
	}
################################################################################ 



    function editProfile($data)        
    {     
		try
        {
           $data = $this->Apiservice('profile/'.$this->id, $data, 'post',  '&act=edit');  
		   //$data = Json::decode($result);       
        } 
        catch (\Exception $e)  
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    } 
    
     
    function editCompanyInfo($data)         
    {     
		try 
        { 
           $data = $this->Apiservice('profile/'.$this->id, $data, 'post', '&act=companyUpdate');   
		   //$data = Json::decode($result);       
        } 
        catch (\Exception $e)  
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
    function addBank($data)          
    {  
		try 
        {  
            $data = $this->Apiservice('addbank/'.$this->id, ['data'=>$data], 'post', '&mangopay_id='.$data['Mangopayid']);      
        } 
        catch (\Exception $e)    
        {  
            $data = htmlentities($e->getMessage());          
        }  
		return($data);     
    }
    
     
    function getBystatus($data, $act='')         
    {     
		try
        {   
           $data = $this->Apiservice('contract/'.$this->id, $data, 'get');   
        } 
        catch (\Exception $e)  
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
    function statusDone($cid, $data)         
    {     
		try 
        {       
           $data = $this->Apiservice('done/'.$cid, $data, 'get');  
           
        } 
        catch (\Exception $e)  
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
    
    function addTrackingCode($cid, $data)         
    {     
		try 
        {    
           $data = $this->Apiservice('contract/'.$cid, $data, 'get');   
        } 
        catch (\Exception $e)   
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
    
    function forgotNewPass($data)          
    {     
		try 
        {      
           $data = $this->Apiservice('profile', $data, 'get');    
        } 
        catch (\Exception $e)   
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
    function uploadIMGPF($data)          
    {     
		try
        { 
           $data = $this->Apiservice('profile/'.$this->id, $data, 'post', '&act=imgPF');   
		   //$data = Json::decode($result);       
        } 
        catch (\Exception $e)  
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
    
    function companyUpdate($data)          
    {     
		try
        {  
           $data = $this->Apiservice('profile/'.$this->id, $data, 'post', '&act=companyUpdate');   
		   //$data = Json::decode($result);       
        } 
        catch (\Exception $e)  
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
     
    function newCustomer($data)          
    {     
		try
        {   
           $data = $this->Apiservice('profile/'.$this->id, $data, 'post', '&act=new');   
		   //$data = Json::decode($result);        
        } 
        catch (\Exception $e)  
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
    function editCustomer($data)          
    {     
		try
        {   
           $data = $this->Apiservice('profile/'.$this->id, $data, 'post', '&act=editCustomer');   
		   //$data = Json::decode($result);        
        } 
        catch (\Exception $e)   
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
    
    function getCompanyByUser($id)          
    {     
		try
        {   
           $data = $this->Apiservice('profile/'.$id, [], 'post', '&act=abovCompany');  
		   //$data = Json::decode($result);         
        } 
        catch (\Exception $e)  
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
    
    function makeContracttomer($data, $act=4)          
    {     
		try
        {    
           $data = $this->Apiservice('makecontract', $data, 'post', '&act='.$act);   
		   //$data = Json::decode($result);        
        } 
        catch (\Exception $e)  
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
    
    function acceptContract($data)           
    {     
		try
        {    
           $data = $this->Apiservice('contract/'.$data['id'], $data, 'post', '&act=accept');    
		   //$data = Json::decode($result);         
        } 
        catch (\Exception $e)  
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
    
    function getSearch($keyword='')          
    {      
		try
        {   
           $data['keyword'] = $keyword; 
           $result = $this->Apiservice('search', $data, 'get');   
           //print_r($result);exit; 
		   $data = Json::decode($result);        
        }  
        catch (\Exception $e)  
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
    
    function getService($url='', $parameter = [], $type='get')
    {    
        $para = ''; 
        if(!empty($url)){
            if($type=='get'){ 
                foreach ($parameter as $key=>$value) {
                    $para .= '&'.$key.'='.$value; 
                }  
            }
            //echo $url; exit;     
            $client = new Client($url, array(  
               'adapter' => 'Zend\Http\Client\Adapter\Curl',
               'sslcapath' => '/etc/ssl/certs'
            )); 
            if($type=='post'){  
               //print_r($parameter); exit;
               $client->setMethod(Request::METHOD_POST);
               $client->setParameterPost($parameter); 
            }
            $response = $client->send();  
            $body = $response->getBody();
        }else{ 
            $body = []; 
        }
        return $body; 
        
    } 
    
    function getByStatusPro($data)          
    {     
		try 
        {      
           $data = $this->Apiservice('contract/'.$this->id, $data, 'get');     
        } 
        catch (\Exception $e)   
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
    function paybuyer($data)          
    {     
		try 
        {       
           $data = $this->Apiservice('payrs/'.$this->id, $data, 'get');     
        } 
        catch (\Exception $e)   
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    }
    
    
    function getWallets($data)          
    {     
		try 
        {        
          // $data = $this->Apiservice('wallets/', $data, 'get');  
           
           $mangopay_id = $data['mangopay_id'];
           $mangopay_wallet = $data['mangopay_wallet'];
           $url = $this->mangopayAPI.'act=wallets&mangopay_id='.$mangopay_id.'&mangopay_wallet='.$mangopay_wallet;
           //print($data); exit;  
            /* 
            print($url);  
            exit; */     
            $output = $this->getService($url); 
            $output = Json::decode($output); 
        } 
        catch (\Exception $e)   
        {   
            $output = htmlentities($e->getMessage());          
        } 
		return($output);         
    }
    
    
    function getBankList($mangopay_id='')          
    {     
		try 
        {        
           // $data = $this->Apiservice('wallets/', $data, 'get');  
           $url = $this->mangopayAPI.'act=bankaccounts&mangopay_id='.$mangopay_id;
            $output = $this->getService($url); 
            $output = Json::decode($output); 
        } 
        catch (\Exception $e)   
        {   
            $output = htmlentities($e->getMessage());          
        } 
		return($output);         
    }
    
    function payoutByUser($mangopay_id='', $walletId='', $price='', $bankID='', $name='', $email='', $user_id=0)                          
    {       
        $url = $this->apiURL.'/'.$this->lang.'/payout/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&act=payoutByuser&mangopay_id='.$mangopay_id.'&walletId='.$walletId.'&amount='.$price.'&bankID='.$bankID.'&name='.$name.'&email='.$email.'&user_id='.$user_id.'&rd='.strtotime($this->now); 
	    //echo $url; exit;   
	    $result = $this->curl($url);                 
		//$data = Json::decode($result);         
		return($result);                        
    } 
    
    
    function getUserWallet($id='')        
    {     
		try
        { 
           if(!empty($id)){ 
               $this->id = $id;
           }  
           $result = $this->Apiservice('profile/'.$this->id, [], 'post',  '&act=wallet');  
		   $data = Json::decode($result);          
        } 
        catch (\Exception $e)  
        {  
            $data = htmlentities($e->getMessage());          
        } 
		return($data);         
    } 
    
    
    function BankDeactivate($BankAccountId, $UserId)          
    {     
		try  
        {        
           $url = $this->mangopayAPI.'act=deactivate_bank&BankAccountId='.$BankAccountId.'&UserId='.$UserId;
            $output = $this->getService($url); 
            $output = Json::decode($output); 
        } 
        catch (\Exception $e)   
        {   
            $output = htmlentities($e->getMessage());          
        } 
		return($output);         
    }
    
     
    //************ KYC ***********//
    
    function uploadKYC($uid, $mangopay_id, $Type, $File='')          
    {     
		try  
        {   
            $data['mangopay_id'] = $mangopay_id;
            $data['type'] = $Type;
            $data['file'] = $File;  
            $output = $this->Apiservice('profile/'.$uid, $data, 'post', '&act=uploadKYC');   
            
            /*
            $url = $this->mangopayAPI.'act=uploadKYC&Type='.$Type.'&UserId='.$UserId;
            //echo $url;exit;
            $postData = ['File'=>$File]; 
            $output = $this->getService($url, $postData, 'post'); 
            $output = Json::decode($output);  
            */
        } 
        catch (\Exception $e)   
        {   
            $output = htmlentities($e->getMessage());          
        } 
		return($output);         
    }
    
    function getKYCList($UserId)          
    {     
		try  
        {         
           $url = $this->mangopayAPI.'act=KycList&UserId='.$UserId; 
           $output = $this->getService($url);    
           $output = Json::decode($output); 
           $results = [];
           if(!empty($output->result)){ 
                $items = ['Status'=>$output->Status, 'result'=>$output->result];
                foreach($output->result as $key => $value){
                    $value->createdate = date("m/d/Y H:i:s", $value->CreationDate); 
                    $results[] = $value; 
                }
                $items = ['Status'=>$output->Status, 'result'=>$results];
                $output = $items;
           }
        } 
        catch (\Exception $e)   
        {   
            $output = htmlentities($e->getMessage());          
        } 
		return($output);         
    }
    
    function editProfileMangopay($UserId, $data)          
    {     
		try  
        {        
            $url = $this->mangopayAPI.'act=updateUser&UserId='.$UserId;
            $output = $this->getService($url, $data, 'post');    
            print_r($output); exit; 
            $output = Json::decode($output); 
        } 
        catch (\Exception $e)   
        {   
            $output = htmlentities($e->getMessage());          
        } 
		return($output);         
    }
    
     function getPayoutList($user_id=0)                          
    {       
        $url = $this->apiURL.'/'.$this->lang.'/payout/?username='.$this->config['Api_username'].'&password='.$this->config['Api_password'].'&act=payoutList&user_id='.$user_id.'&rd='.strtotime($this->now).'&page='.$this->page; 
	    //echo $url; exit;    
	    $result = $this->curl($url);                  
		//$data = Json::decode($result);         
		return($result);                         
    } 
    
}
      