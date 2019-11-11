<?php
namespace Application\Models;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Memcached;
use Zend\Cache\Storage\StorageInterface;
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

use Zend\Http\Client; 
use Zend\Http\Request;

class Api
{
    protected $apies;
################################################################################ 
	function __construct($adapter, $inLang, $inAction, $inID, $inPage, $inFor, $nocache)
    {
        $this->cacheTime = 3600;
        $this->lang = $inLang;
        $this->action = $inAction;
        $this->id = $inID;
        $this->adapter = $adapter;
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
		
		$this->for = $inFor;
		$this->noCache = $nocache;
		//$this->webURL = 'https://safe-tonytoons.c9users.io/public';
		//$this->webURL = 'https://dev.zenovly.com'; 
		
		$this->config = include __DIR__ . '../../../../config/module.config.php';
		$this->coml = ['0-100-10', '101-1000-5', '1001-10000-3.5', '10001-10000000000000000000-2.5'];
		$host_name = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'];
		$this->webURL = $host_name;     
		$this->mangopayAPI = $host_name.'/mangopay/t.php?';  
    }
################################################################################ 
    function getList()
    {
        $data = array();
		if( ($this->for == 'hot') || ($this->for == 'new') )
		{
        	$key_txt = md5('list_lang_'.$this->lang.'_page_'.$this->page.'_for_'.$this->for);
		}
		else
		{
			$key_txt = md5('list_lang_'.$this->lang.'_page_'.$this->page.'_for_'.$this->for.'_id_'.$this->id);
		}
        $cache = $this->maMemCache($this->cacheTime, $key_txt);
        $data = $cache->getItem($key_txt, $success);
		if( empty($data) || ($this->noCache == 1) )
		{
			if($this->for == 'hot')
			{
	        	$sql = $this->adapter->query("SELECT id, name, detail_short, view, img, last_update FROM `blog` WHERE active = '1' ORDER BY view DESC LIMIT $this->pageStart, $this->perpage");
			}//AND (last_update BETWEEN CURDATE() - INTERVAL 100 DAY AND CURDATE() + INTERVAL 1 DAY) 
			else if($this->for == 'new')
			{
				$sql = $this->adapter->query("SELECT id, name, detail_short, view, img, last_update FROM `blog` WHERE active = '1' ORDER BY last_update DESC LIMIT $this->pageStart, $this->perpage");
			}
            $results = $sql->execute();
            $resultSet = new ResultSet;
            $data = $resultSet->initialize($results);
			$data = $data->toArray();
			$cache->setItem($key_txt, $data);
		}
		return($data);
    }
################################################################################ 
    function getTotal()
    {
        $data = 0;
        $key_txt = md5('total_lang_'.$this->lang.'_for_'.$this->for);
        $cache = $this->maMemCache($this->cacheTime, $key_txt);
        $data = $cache->getItem($key_txt, $success);
		if( empty($data) || ($this->noCache == 1) )
		{
			if($this->for == 'hot')
			{
				$sql = $this->adapter->query("SELECT COUNT(id) AS c FROM `blog` WHERE active = '1' LIMIT 1");
			}
			else if($this->for == 'new')
			{
	        	$sql = $this->adapter->query("SELECT COUNT(id) AS c FROM `blog` WHERE active = '1' LIMIT 1");
			}
            $results = $sql->execute();
            $row = $results->current();
            if(@$row)
			{
			    $cache->setItem($key_txt, $row['c']);
				$data = $row['c'];
			}
		}
		return($data);
    }
################################################################################ 
    function getDetail()
    {
        $data = array();
        $key_txt = md5('detail_lang_'.$this->lang.'_id_'.$this->id);
        $cache = $this->maMemCache($this->cacheTime, $key_txt);
        $data = $cache->getItem($key_txt, $success);
		if( empty($data) || ($this->noCache == 1) )
		{
	        $sql = $this->adapter->query("SELECT id, name, detail, view, img, last_update FROM `blog` WHERE active = '1' AND id = '$this->id' LIMIT 1");
            $results = $sql->execute();
            $row = $results->current();
            if(@$row) 
			{
				$img = $this->config['amazon_s3']['urlFile'].'/blog/'.$row['img'];
				$id = $row['id'];
				$galleries = $this->getCgallery($id);
				$data = array(
									'id' => $id,
									'name' => $row['name'],
									'detail' => $row['detail'],
									'view' => $row['view'],
									'img' => $img,
									'last_update' => $row['last_update'],
									'gallery' => $galleries
							);
				$cache->setItem($key_txt, $data);
			}
		}
		return($data);
    }
################################################################################ 
    function getCgallery($id)
    {
        $data = array();
        $sql = $this->adapter->query("SELECT image FROM `blog_image` WHERE blog_id = '$id' ORDER BY createdate DESC");
		$results = $sql->execute();
        $resultSet = new ResultSet;
        $rs = $resultSet->initialize($results);
		$rsa = $rs->toArray();
		if($rsa)
		{
    		foreach ($rsa as $key => $value)
    		{
                $data[$key] = $this->config['amazon_s3']['urlFile'].'/blog/'.$value['image'];
            }
        }
		return($data);
    }
################################################################################ 
    function cView()
    {
        $data = 0;
        $sql = $this->adapter->query("SELECT view FROM `blog` WHERE id = '$this->id' LIMIT 1");
        $results = $sql->execute();
        $row = $results->current();
        if(@$row) 
		{
		    $view = $row['view']+1;
            try
            {
                $sqlU = $this->adapter->query("UPDATE blog SET view = '$view' WHERE id = '$this->id'");
                if($sqlU->execute()) $data = $view;
            }
            catch (Zend_Exception $e){}
		}
		return ($data);
    }
################################################################################ 
    function editCdetail($supplier_id)
    {
        $status = 404;
        $item = "Sorry! we couldn't process, because some supplier already accepted this contract.";
        $detail = $this->getCdetail('0');
        $o_supplier_id = $detail['supplier_id'];
        $user_id = $detail['user_id'];
        if( ($o_supplier_id != 0) || ($o_supplier_id != NULL) || ($o_supplier_id != $user_id) )
        {
            try
            {
                $sql = $this->adapter->query("UPDATE zenovly_contract SET supplier_id = '$supplier_id', status = '1', last_update = NOW() WHERE id = '$this->id'");
                if($sql->execute()) 
                {
                    $item = $this->getCdetail('1');
                    $status = 200;
                    $user_id = $item['user_id']; 
                    $user = $this->getUserINFO($user_id);
                    $supplier = $this->getUserINFO($supplier_id);
                    $token = base64_encode('zenovly'.$this->id);
                    $token = str_replace ( '=', 'gpsn', $token);
                    $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?rd='.date("YmdHis");
    			    $link = '<a href="'.$link.'" target="_blank">Click here</a>';
    			    $txt = file_get_contents($this->webURL.'/email/c2user.html');
    			    $txt = preg_replace(array('/{name}/', '/{cname}/', '/{contract_name}/', '/{link}/'), array($user['name'], $supplier['name'], $detail['serial_number'], $link), $txt);
    			    $subject = 'You supplier has accepted the online contract - Wezenit';
    			    $this->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($user['name']), $user['email'], $txt, '', '');
    			    $txt2 = file_get_contents($this->webURL.'/email/c2s.html');
    			    $txt2 = preg_replace(array('/{name}/', '/{contract_name}/', '/{link}/'), array($supplier['name'], $detail['serial_number'], $link), $txt2);
    			    $subject2 = 'Thanks for accepting the online contract - Wezenit';
    			    $this->sendMail($subject2, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($supplier['name']), $supplier['email'], $txt2, '', '');
                }
            }
            catch (Zend_Exception $e){}
        }
        $data = array(
                            'status' => $status,
                            'item' => $item
                        );
        return ($data);
    }
################################################################################ 
    function getLogin($username, $password)
    {
        $sql = $this->adapter->query("SELECT COUNT(id) as c FROM `api` WHERE username = '$username' AND password = '$password' LIMIT 1");
        $results = $sql->execute();
        $row = $results->current();
        $c = $row['c'];
        if($c == NULL) $c = 0;
		return($c);
    }
################################################################################ 
    function getUlogin($facebook_id, $email, $upassword)
    {
        if($facebook_id)
        {
            $sql = $this->adapter->query("SELECT id FROM users WHERE facebook_id = '$facebook_id' LIMIT 1");
        }
        else
        {
            if($email && $upassword)
            {
                $sql = $this->adapter->query("SELECT id FROM users WHERE email = '$email' AND password = '$upassword' LIMIT 1");
            }
        }
        
        if(!empty($sql))
        {
            $results = $sql->execute();
            $row = $results->current();
            if(@$row)
    		{
    			$items = $this->getUser($row['id'], 0);
    			return $items;
    		}
        }
    }
################################################################################ 
    function getUser($id, $inCache)
    {
        $data = '';
        $key_txt = md5('user_id_'.$id);
        $cache = $this->maMemCache($this->cacheTime, $key_txt);
        $data = $cache->getItem($key_txt, $success);
		if( empty($data) || ($this->noCache == 1) || ($inCache == 1) )
		{
            $sql = $this->adapter->query("SELECT facebook_id, mangopay_id, mangopay_wallet, mangopay_bank_id, email, name, phone, gender, birth_day, address, last_update, image, company_id, company_name, company_country, company_address, company_mobile_phone, company_phone, company_email, active FROM users WHERE id = '$id' LIMIT 1");
            $results = $sql->execute();
            $row = $results->current();
            if(@$row)
        	{
        	    $company_name = $row['company_name'];
        	    if(empty($company_name)) $company_name = '';
        	    $company_id = $row['company_id'];
        	    if(empty($company_id)) $company_id = '';
        	    $company_country = $row['company_country'];
        	    if(empty($company_country)) $company_country = '';
        	    $company_address = $row['company_address'];
        	    if(empty($company_address)) $company_address = '';
        	    $company_mobile_phone = $row['company_mobile_phone'];
        	    if(empty($company_mobile_phone)) $company_mobile_phone = '';
        	    $company_phone = $row['company_phone'];
        	    if(empty($company_phone)) $company_phone = '';
        	    $company_email = $row['company_email'];
        	    if(empty($company_email)) $company_email = '';
        	    $type = 'user';
        	    //if($company_name && $company_address && $company_phone && $company_email) $type = 'supplier';
        	    $image = $row['image'];
        	    $image_url = '';
        	    $this->fbID = $row['facebook_id'];
        	    if($this->fbID == NULL) $this->fbID = '';
        	    if(!empty($row['image']))
				{
					$image_url = $this->config['amazon_s3']['urlFile'].'/users/'.$image;
				}
				else
				{
					if(!empty($this->fbID))
					{
						$image_url = 'https://graph.facebook.com/'.$this->fbID.'/picture?width=320&height=320';
					}
				}
                $clients = array();
                //if($type == 'supplier') $clients = $this->getMyClients($id);
                $clients = $this->getMyClients($id);
                if(count($clients) > 0) $type = 'seller';
                $active = $row['active'];
                if($active == 1)
                {
                    $status = 'Active';
                }
                else if($active == 2)
                {
                    $status = 'Banned';
                }
                else
                {
                    $status = 'Pending';
                }
                $phone = $row['phone'];
                $email = $row['email'];
        	    if(empty($email)) $email = '';
        	    $name = $row['name'];
        	    if(empty($name)) $name = '';
                $mangopay_id = $row['mangopay_id'];
                $mangopay_wallet = $row['mangopay_wallet'];
        	    if(empty($mangopay_id))
        	    {
        	        $mangopay_id = '';
        	        $mangopay_wallet = '';
        	        $mangopayA = $this->getMangopayID($email, $name, $id);
        	        $mangopay_id = $mangopayA['id'];
        	        $mangopay_wallet = $mangopayA['wallet'];
        	    }
        	    $mangopay_bank_id = $row['mangopay_bank_id'];
        	    if($mangopay_bank_id == NULL) $mangopay_bank_id = '';
    			$data = array(
    								'id' => $id,
    								'mangopay_id' => $mangopay_id,
    								'mangopay_wallet' => $mangopay_wallet,
    								'mangopay_bank_id' => $mangopay_bank_id,
    								'image' => $image,
    								'image_url' => $image_url,
    								'facebook_id' => $this->fbID,
    								'email' => $email,
    								'name' => $name,
    								'phone' => $phone,
    								'gender' => $row['gender'],
    								'birth_day' => $row['birth_day'],
    								'address' => $row['address'],
    								'type' => $type,
    								'company_id' => $company_id,
    								'company_name' => $company_name,
    								'company_country' => $company_country,
    								'company_address' => $company_address,
    								'company_mobile_phone' => $company_mobile_phone,
    								'company_phone' => $company_phone,
    								'company_email' => $company_email,
    								'clients' => $clients,
    								'status_id' => $active,
    								'status' => $status,
    								'last_update' => $row['last_update']
    							);
    			$cache->setItem($key_txt, $data); //print_r($data);
        	}
    	}
    	return $data;
    }
################################################################################ 
    function getMangopayID($email, $name, $uID)
    {
        $id = '';
        $wallet = '';
        $name = explode(" ", $name);
        $firstName = $name[0];
        $lastName = $name[count($name)-1];
        $id = file_get_contents($this->mangopayAPI.'act=newUser&email='.$email.'&firstName='.$firstName.'&lastName='.$lastName);
        if($id)
        {
            $wallet = file_get_contents($this->mangopayAPI.'act=wallet&id='.$id);
            $sql = $this->adapter->query("UPDATE users SET mangopay_id = '$id', mangopay_wallet = '$wallet', last_update = NOW() WHERE id = '$uID'");
            $sql->execute();
        }
        $data = array(
                            'id' => $id,
                            'wallet' => $wallet
                        );
        return $data;
    }
################################################################################ 
    function getMyClients($id)
    {
        $data = [];
        $sql = $this->adapter->query("SELECT DISTINCT(buyer_id) AS user_id FROM zenovly WHERE seller_id = '$id' ORDER BY last_update DESC");
        $results = $sql->execute();
        if($results)
        {
            $resultSet = new ResultSet;
            $ldata = $resultSet->initialize($results);
		    $adata = $ldata->toArray();
		    foreach ($adata as $key => $value)
    		{
    		    $user_id = $value['user_id'];
                $data[$key] = $this->getUserINFO($user_id);
            }
        }
        return($data);
    }
################################################################################ 
    function userEdit($id, $email, $name, $phone, $facebook_id, $gender, $birth_day, $address)
    {
        $user = 0;
        try
        {
            $sql = $this->adapter->query("UPDATE users SET email = '$email', name = '$name', phone = '$phone', facebook_id = '$facebook_id', gender = '$gender', birth_day = '$birth_day', address = '$address', last_update = NOW() WHERE id = '$id'");
            if($sql->execute()) $user = $this->getUser($id, 1);
        }
        catch (Zend_Exception $e){}
        return($user);
    }
################################################################################ 
    function userCedit($id, $utype, $company_name, $company_address, $company_mobile_phone, $company_phone, $company_email, $company_id, $company_country)
    {
        $user = 0;
        try
        {
            $sql = $this->adapter->query("UPDATE users SET type = '$utype', company_name = '$company_name', company_address = '$company_address', company_phone = '$company_phone', company_email = '$company_email', company_mobile_phone = '$company_mobile_phone', company_id = '$company_id', company_country = '$company_country', last_update = NOW() WHERE id = '$id'");
            if($sql->execute()) $user = $this->getUser($id, 1);
        }
        catch (Zend_Exception $e){}
        return($user);
    }
################################################################################ 
    function userCpassword($id, $udpassword, $upassword)
    {
        $user = 0;
        try
        {
            $sql = $this->adapter->query("SELECT id FROM users WHERE id = '$id' AND password = '$udpassword' LIMIT 1");
            $results = $sql->execute();
            $row = $results->current();
            if(@$row)
        	{
        	    $id = $row['id'];
                $sql2 = $this->adapter->query("UPDATE users SET password = '$upassword', last_update = NOW() WHERE id = '$id'");
                if($sql2->execute()) $user = 1;
        	}
        }
        catch (Zend_Exception $e){}
        return($user);
    }
################################################################################ 
    function userCpasswordByEmail($email, $upassword)
    {
        $user = 0;
        try
        {
            $sql = $this->adapter->query("UPDATE users SET password = '$upassword', active = '1', last_update = NOW() WHERE email = '$email'");
            if($sql->execute()) $user = 1;
        }
        catch (Zend_Exception $e){}
        return($user);
    }
################################################################################ 
    function forgotPassword($email)
    {
        $rs = 'No this email in the system, please check your email!'; 
        $status = 404; 
        try 
        {
            $sql = $this->adapter->query("SELECT id, name FROM users WHERE email = '$email' AND active = '1' LIMIT 1");
            $results = $sql->execute();
            $row = $results->current();
            if(@$row)
        	{
        	    $name = $row['name']; 
        	    if(!empty($name))
        	    { 
        	        $date = date("Y-m-d h:i:s", strtotime("+1 day"));
					$token = base64_encode($date.'&'.$email);
					$token = str_replace ( '=', 'gpsn', $token);
					$link = $this->webURL.'/'.$this->lang.'/forgotpassword/?token='.$token;
					$link = '<a href="'.$link.'" target="_blank">Click here</a>';
					$txt = file_get_contents($this->webURL.'/email/forgotPassword.html');
					$txt = preg_replace(array('/{name}/', '/{link}/'), array($name, $link), $txt);
					$subject = 'You requested a password reset for the Wezenit.';
					$rs = $this->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($name), $email, $txt, '', '');
					if($rs==200){ 
    					$status = 200; 
    					$rs = 'Your new password has been sent to your email.';
    					if($this->lang=='fr'){
    					    $rs = 'un email vous a été envoyé avec un lien pour ré-initialiser votre mot de passe';
    					} 
					}else{ 
					    $status = 400; 
					}
        	    }
        	}
        }
        catch (Zend_Exception $e){}
        $user = array(
                        'status' => $status,
                        'rs' => $rs
                    );
        return($user);
    }
    
################################################################
    function sendMail($subject, $fromName, $fromEmail, $toName, $toEmail, $body, $bccName, $bccEmail)
    {
        try
        {
            $message = new Message();
            $html = new MimePart($body);
            $html->type = "text/html";
            
            $body = new MimeMessage();
            $body->setParts(array($html));
            
            $message = new Message();
            $message->setBody($body);
            
            $message->addTo($toEmail, $toName)
                    ->addFrom($fromEmail, $fromName)
                    ->setSubject($subject);
            
            // Setup SMTP transport using LOGIN authentication
            $transport = new SmtpTransport();
            /*
            $options   = new SmtpOptions(array(
                'name'              => 'zenovly.com',
                'host'              => 'smtp.sendgrid.net',
                'port'              => 587,
                'connection_class'  => 'login',
                'connection_config' => array(
                    'username' => 'Tonytoons',
                    'password' => 'RockThai69',
                    'ssl'      => 'tls',
                ),
            ));
               
            $options   = new SmtpOptions(array(
                'name'              => 'ses-smtp-user.20180905-145020',
                'host'              => 'email-smtp.us-east-1.amazonaws.com',
                'port'              => 587,
                'connection_class'  => 'login',
                'connection_config' => array(
                    'username' => 'AKIAIZZNXSCUZCGXCYWQ',
                    'password' => 'ApIL+d7U2xp+GBMIaOJGENkE4hS5vjxUoDZ/pRpwFx8j',
                    'ssl'      => 'tls',
                ),
            ));
            */
            //boy
            $options   = new SmtpOptions(array(
                'name'              => 'wezenit.com',
                'host'              => 'smtp.sendgrid.net',
                'port'              => 587,
                'connection_class'  => 'login',
                'connection_config' => array( 
                    'username' => 'boyatomic32', 
                    'password' => '123qwe123', 
                    'ssl'      => 'tls',
                ),
            ));
            
            $transport->setOptions($options);
            $transport->send($message);
            
            return 200;
        }
        catch (\Exception $e) 
        {  
            return htmlentities($e->getMessage());         
        }
    }
################################################################################ 
    function makeContract1($user_id, $supplier_id, $total_price, $start_date, $end_date, $serial_number, $contract_name, $contract_company, $contract_phone, $contract_landline_phone, $contract_email, $contract_img, $company_address, $act, $project_name, $subject, $body)
    {
        $data = 0;
        $id = $this->getNextCid();
        if($id)
        {
            if($act == 1)
            {
                $fd = 'contract';
                $img = $this->uploadContractIMG($contract_img, $fd);
                $sql = $this->adapter->query("INSERT INTO zenovly_contract (id, user_id, supplier_id, total_price, start_date, end_date, serial_number, contract_name, contract_company, contract_phone, contract_landline_phone, contract_email, contract_img, company_address, status, project_name, added_date, last_update) VALUES ('$id', '$user_id', '0', '$total_price', '$start_date', '$end_date', '$serial_number', '$contract_name', '$contract_company', '$contract_phone', '$contract_landline_phone', '$contract_email', '$img', '$company_address', '0', '$project_name', NOW(), NOW());");
                if($sql->execute())
                {
                    $user = $this->getUserINFO($user_id);
                    $token = base64_encode('zenovly'.$id);
                    $token = str_replace ( '=', 'gpsn', $token); 
                    $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?to=1&rd='.date("YmdHis");   
        			$link = '<a href="'.$link.'" target="_blank">Click here</a>';
        			$txt = file_get_contents($this->webURL.'/email/tosp.html');
        			$txt = preg_replace(array('/{name}/', '/{cname}/', '/{contract_name}/', '/{link}/'), array($contract_name, $user['name'], $serial_number, $link), $txt);
        			$subject = 'Your client has requested you to sign the contract online - Wezenit';
        			$this->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($contract_name), $contract_email, $txt, '', '');
        			$data = 1;
                }
            } 
            else if($act == 2)
            {
                $fd = 'contract';
                $img = $this->uploadContractIMG($contract_img, $fd);
                $sql = $this->adapter->query("INSERT INTO zenovly_contract (id, user_id, supplier_id, total_price, start_date, end_date, serial_number, contract_name, contract_company, contract_phone, contract_landline_phone, contract_email, contract_img, company_address, status, project_name, added_date, last_update) VALUES ('$id', '$user_id', '$supplier_id', '$total_price', '$start_date', '$end_date', '$serial_number', '$contract_name', '$contract_company', '$contract_phone', '$contract_landline_phone', '$contract_email', '$img', '$company_address', '6', '$project_name', NOW(), NOW());");
                if($sql->execute())
                {
                    $user = $this->getUserINFO($supplier_id);
                    $token = base64_encode('zenovly'.$id);
                    $token = str_replace ( '=', 'gpsn', $token);  
                    $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?to=2&date='.date("YmdHis");    
        			$link = '<a href="'.$link.'" target="_blank">Click here</a>';
        			$txt = file_get_contents($this->webURL.'/email/spupc.html'); 
        			$txt = preg_replace(array('/{name}/', '/{cname}/', '/{contract_name}/', '/{link}/', '/{utext}/'), array($contract_name, $user['name'], $serial_number, $link, $body), $txt);
        			if(empty($subject))
        			{
        			    $subject = 'Your supplier has requested you to sign the contract online - Wezenit';
        			}
        			else
        			{
        			    $subject = $subject.' - Wezenit'; 
        			}
        			$this->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($contract_name), $contract_email, $txt, '', '');
        			$data = 1;
                }
            }
            else
            {
                $fd = 'contract_cover';
                $img = $this->uploadContractIMG($contract_img, $fd);
                $sql = $this->adapter->query("INSERT INTO zenovly_contract (id, user_id, supplier_id, total_price, start_date, end_date, project_name, contract_img, contract_cover, status, added_date, last_update) VALUES ('$id', '$user_id', '0', '$total_price', '$start_date', '$end_date', '$project_name', '', '$contract_img', '4',  NOW(), NOW());");
                if($sql->execute())
                {
                    //$user = $this->getUserINFO($user_id);
                    $data = 1;
                }
            }
        }
        return ($data);
    }
################################################################
    function getNextCid()
    {
        $sql = $this->adapter->query("SELECT MAX(id) + 1 AS id FROM zenovly_contract LIMIT 1");
        $results = $sql->execute();
        $row = $results->current();
        if(@$row)
        {
            $id = $row['id'];
            if($id == NULL) $id = 1;
            return($id);
        }
    }
################################################################
    function getUserINFO($user_id)
    {
        $data = array();
        $sql = $this->adapter->query("SELECT name, email, phone, image FROM users WHERE id = '$user_id' LIMIT 1");
        $results = $sql->execute();
        $row = $results->current();
        if(@$row)
        {
            $image = '';
            $image_url = '';
            if(!empty($row['image']))
            {
                $image = $row['image'];
                $image_url = $this->config['amazon_s3']['urlFile'].'/users/'.$image;
            }
            $name = $row['name'];
            $email = $row['email'];
            $phone = $row['phone'];
            $data = array(
                            'id' => $user_id,
                            'name' => $name,
                            'email' => $email,
                            'phone' => $phone,
                            'image' => $image,
                            'image_url' => $image_url
                        );
        }
        return($data);
    }
################################################################
    function uploadContractIMG($contract_img, $fd)
    {
        try   
        {
            if (!preg_match('/data:([^;]*);base64,(.*)/', $contract_img, $matches)) {
                die("error");
            }
    
            $content = str_replace('data:image/', '', $matches[0]);
            $content = str_replace('data:application/', '', $content);
            $content = explode(";", $content);
            $content = $content[0];
            $pname = $this->id.gmdate('YmdHis').rand(0000, 9999);
            $img_name = $pname.'.'.$content;
            $filenameext = $content;
            if($img_name)
            {
                $s3 = new S3Client($this->config['amazon_s3']['config']);    
                $bucket = $this->config['amazon_s3']['bucket'];
                $result = $s3->putObject(array(
                    'Bucket' => $bucket, 
                    'Key' => $fd.'/'.$img_name,  
                    'ACL' => 'public-read',     
                    'SourceFile' => $contract_img,    
                    'Expires'=> (string)(1000+(int)date("Y")),                       
                    'ContentType'=>'image/'.$filenameext,      
                )); 
            }
            return($img_name);
        } catch (S3Exception $e) {    
            // Catch an S3 specific exception.
            echo "<pre>";
            echo $e->getMessage();
            echo "</pre>";
            exit;    
        } 
    }
################################################################################ 
    function profilePIC($id, $img)
    {
        $data = 0;
        $fd = 'users'; 
        $this->unlinkPFpic($id);
        $img = $this->uploadContractIMG($img, $fd);
        $sql = $this->adapter->query("UPDATE users SET image = '$img', last_update = NOW() WHERE id = '$id'");
        if($sql->execute()) $data = 1;
        return ($data);
    }
################################################################################ 
    function unlinkPFpic($id)
    {
        try
        {
            $user = $this->getUserINFO($id); 
            $userPIC = $user['image'];
            if(!empty($userPIC))
            {
                $s3 = new S3Client($this->config['amazon_s3']['config']);    
                $bucket = $this->config['amazon_s3']['bucket'];
                $result = $s3->deleteObject(array(
                    'Bucket' => $bucket,   
                    'Key'    => 'users/'.$userPIC     
                ));  
            }
        } catch (S3Exception $e) {
            // Catch an S3 specific exception.  
            echo "<pre>";
            echo $e->getMessage();
            echo "</pre>";  
        } 
    }
################################################################################ 
    function getCdetail($noCache)
    {
        $data = array();
        $key_txt = md5('cdetail_lang_'.$this->lang.'_id_'.$this->id);
        $cache = $this->maMemCache($this->cacheTime, $key_txt);
        $data = $cache->getItem($key_txt, $success);
		if( empty($data) || ($this->noCache == 1) || ($noCache == 1) )
		{
	        $sql = $this->adapter->query("SELECT project_name, user_id, supplier_id, total_price, start_date, end_date, serial_number, contract_name, contract_company, company_address, contract_phone, contract_landline_phone, contract_email, contract_img, contract_cover, status, last_update FROM `zenovly_contract` WHERE id = '$this->id' LIMIT 1");
            $results = $sql->execute();
            $row = $results->current();
            if(@$row) 
			{
			    $img = $this->config['amazon_s3']['urlFile'].'/contract/'.$row['contract_img'];
			    $contract_cover = $this->config['amazon_s3']['urlFile'].'/contract/'.$row['contract_cover'];
				$data = array(
									'id' => $this->id,
									'project_name' => $row['project_name'],
									'user_id' => $row['user_id'],
									'supplier_id' => $row['supplier_id'],
									'total_price' => $row['total_price'],
									'start_date' => $row['start_date'],
									'end_date' => $row['end_date'],
									'serial_number' => $row['serial_number'],
									'contract_name' => $row['contract_name'],
									'contract_company' => $row['contract_company'],
									'company_address' => $row['company_address'],
									'contract_phone' => $row['contract_phone'],
									'contract_landline_phone' => $row['contract_landline_phone'],
									'contract_email' => $row['contract_email'],
									'contract_img' => $row['contract_img'],
									'status' => $row['status'],
									'last_update' => $row['last_update'],
									'contract_cover' => $contract_cover,
									'img' => $img
							);
				$cache->setItem($key_txt, $data);
			}
		}
		return($data);
    }
################################################################################ 
    function getCL($t, $status)
    {
        $data = array();
		if($t == 'c')
		{
		    if($status == 'all')
		    {
	            $sql = $this->adapter->query("SELECT id, project_name, user_id, supplier_id, total_price, start_date, end_date, serial_number, contract_name, contract_company, company_address, contract_phone, contract_landline_phone, contract_email, contract_img, contract_cover, status, last_update FROM `zenovly_contract` WHERE user_id = '$this->id' ORDER BY end_date DESC LIMIT $this->pageStart, $this->perpage");
		    }
		    else
		    {
		        $sql = $this->adapter->query("SELECT id, project_name, user_id, supplier_id, total_price, start_date, end_date, serial_number, contract_name, contract_company, company_address, contract_phone, contract_landline_phone, contract_email, contract_img, contract_cover, status, last_update FROM `zenovly_contract` WHERE user_id = '$this->id' AND status = '$status' ORDER BY end_date DESC LIMIT $this->pageStart, $this->perpage");
		    }
	    }
		else
		{
		    if($status == 'all')
		    {
			    $sql = $this->adapter->query("SELECT id, project_name, user_id, supplier_id, total_price, start_date, end_date, serial_number, contract_name, contract_company, company_address, contract_phone, contract_landline_phone, contract_email, contract_img, contract_cover, status, last_update FROM `zenovly_contract` WHERE supplier_id = '$this->id' ORDER BY end_date DESC LIMIT $this->pageStart, $this->perpage");
		    }
		    else
		    {
		        $sql = $this->adapter->query("SELECT id, project_name, user_id, supplier_id, total_price, start_date, end_date, serial_number, contract_name, contract_company, company_address, contract_phone, contract_landline_phone, contract_email, contract_img, contract_cover, status, last_update FROM `zenovly_contract` WHERE supplier_id = '$this->id' AND status = '$status' ORDER BY end_date DESC LIMIT $this->pageStart, $this->perpage");
		    }
		}
        $results = @$sql->execute();
        $resultSet = new ResultSet;
        if($resultSet)
        {
            $rs = $resultSet->initialize($results);
    		$rsa = $rs->toArray();
    		foreach ($rsa as $key => $value)
    		{
    		    $project_name = $value['project_name'];
    		    if(empty($project_name)) $project_name = '';
    		    $contract_name = $value['contract_name'];
    		    if(empty($contract_name)) $contract_name = '';
    		    $contract_company = $value['contract_company'];
    		    if(empty($contract_company)) $contract_company = '';
    		    $company_address = $value['company_address'];
    		    if(empty($company_address)) $company_address = '';
    		    $contract_phone = $value['contract_phone'];
    		    if(empty($contract_phone)) $contract_phone = '';
    		    $contract_landline_phone = $value['contract_landline_phone'];
    		    if(empty($contract_landline_phone)) $contract_landline_phone = '';
    		    $contract_email = $value['contract_email'];
    		    if(empty($contract_email)) $contract_email = '';
    		    
    		    $contract_img = $value['contract_img'];
    		    if(empty($contract_img))
    		    {
    		        $contract_img = '';
    		    }
    		    else
    		    {
    		        $contract_img = $this->config['amazon_s3']['urlFile'].'/contract/'.$contract_img;
    		    }
    		    $contract_cover = $value['contract_cover'];
    		    if(empty($contract_cover))
    		    {
    		        $contract_cover = '';
    		    }
    		    else
    		    {
    		        $contract_cover = $this->config['amazon_s3']['urlFile'].'/contract/'.$contract_cover;
    		    }
                $data[$key] = array(
                                        'id' => $value['id'],
                                        'project_name' => $project_name,
                                        'user' => $this->getUserINFO($value['user_id']),
                                        'supplier' => $this->getUserINFO($value['supplier_id']),
                                        'total_price' => $value['total_price'],
                                        'start_date' => $value['start_date'],
                                        'end_date' => $value['end_date'],
                                        'serial_number' => $value['serial_number'],
                                        'contract_name' => $contract_name,
                                        'contract_company' => $contract_company,
                                        'company_address' => $company_address,
                                        'contract_phone' => $contract_phone,
                                        'contract_landline_phone' => $contract_landline_phone,
                                        'contract_email' => $contract_email,
                                        'contract_img' => $contract_img,
                                        'contract_cover' => $contract_cover,
                                        'status' => $value['status'],
                                        'last_update' => $value['last_update'],
                                    );
            }
        }
		return($data);
    }
################################################################################ 
    function getTCL($t, $status)
    {
        $data = 0;
		if($t == 'c')
		{
		    if($status == 'all')
		    {
	            $sql = $this->adapter->query("SELECT COUNT(id) AS c FROM `zenovly_contract` WHERE user_id = '$this->id' LIMIT 1");
		    }
		    else
		    {
		        $sql = $this->adapter->query("SELECT COUNT(id) AS c FROM `zenovly_contract` WHERE user_id = '$this->id' AND status = '$status' LIMIT 1");
		    }
		}
		else
		{
		    if($status == 'all')
		    {
			    $sql = $this->adapter->query("SELECT COUNT(id) AS c FROM `zenovly_contract` WHERE supplier_id = '$this->id' LIMIT 1");
		    }
		    else
		    {
		        $sql = $this->adapter->query("SELECT COUNT(id) AS c FROM `zenovly_contract` WHERE supplier_id = '$this->id' AND status = '$status' LIMIT 1");
		    }
		}
        $results = $sql->execute();
        $row = $results->current();
        if(@$row)
		{
		    $data = $row['c'];
		}
		return($data);
    }
################################################################################ 
    function checkAccount($facebook_id, $email)
    {
        $data = 0;
		if(!empty($facebook_id))
		{
	        $sql = $this->adapter->query("SELECT COUNT(id) AS c FROM `users` WHERE facebook_id = '$facebook_id' LIMIT 1");
		}
		else
		{
			$sql = $this->adapter->query("SELECT COUNT(id) AS c FROM `users` WHERE email = '$email' LIMIT 1");
		}
        $results = $sql->execute();
        $row = $results->current();
        if(@$row)
		{
		    $data = $row['c'];
		}
		return($data);
    }
################################################################################ 
    function newUser($email, $name)
    {
        $id = 0;
        $sql = $this->adapter->query("SELECT id FROM `users` WHERE email = '$email' LIMIT 1");
        $results = $sql->execute();
        $row = $results->current();
        if(@$row) $id = $row['id'];
		
        if( ($id == 0) || empty($id))
        {
            $sql2 = $this->adapter->query("INSERT INTO users (facebook_id, name, email, phone, password, active, gender, birth_day, type, last_update) VALUES ('', '$name', '$email', '', '', '0', '0', '', 1, NOW());");
            if($sql2->execute())
            {
                $sql3 = $this->adapter->query("SELECT id FROM `users` WHERE email = '$email' LIMIT 1");
                $results3 = $sql3->execute();
                $row3 = $results3->current();
                if(@$row3) $id = $row3['id'];
            }
        }
        return ($id);
    }
################################################################
    function getNextZid()
    {
        $sql = $this->adapter->query("SELECT MAX(id) + 1 AS id FROM zenovly LIMIT 1");
        $results = $sql->execute();
        $row = $results->current();
        if(@$row)
        {
            $id = $row['id'];
            if($id == NULL) $id = 1;
            return($id);
        }
    }
################################################################################ 
    function zenovlyContract($zenovly_type, $request, $buyer_id, $seller_id, $total_price, $project_name, $start_date, $end_date, $contract_number, $buyer_name, $buyer_email, $buyer_number, $seller_name, $seller_email, $seller_number, $who_pay_fee, $email_subject, $email_body, $note, $contract_img, $contract_img2, $contract_img3, $contract_img4, $contract_img5, $contract_img6, $contract_img7, $contract_img8, $contract_img9)
    {
        $data = 0;
        $id = $this->getNextZid();
        if($id)
        {
            $fd = 'contract';
            $img = '';
            $img2 = '';
            $img3 = '';
            $img4 = '';
            $img5 = '';
            $img6 = '';
            $img7 = '';
            $img8 = '';
            $img9 = '';
            if($contract_img) $img = $this->uploadContractIMG($contract_img, $fd);
            if($contract_img2) $img2 = $this->uploadContractIMG($contract_img2, $fd);
            if($contract_img3) $img3 = $this->uploadContractIMG($contract_img3, $fd);
            if($contract_img4) $img4 = $this->uploadContractIMG($contract_img4, $fd);
            if($contract_img5) $img5 = $this->uploadContractIMG($contract_img5, $fd);
            if($contract_img6) $img6 = $this->uploadContractIMG($contract_img6, $fd);
            if($contract_img7) $img7 = $this->uploadContractIMG($contract_img7, $fd);
            if($contract_img8) $img8 = $this->uploadContractIMG($contract_img8, $fd);
            if($contract_img9) $img9 = $this->uploadContractIMG($contract_img9, $fd);
            $sql = $this->adapter->query("INSERT INTO zenovly (id, project_type, request, buyer_id, seller_id, total_price, project_name, start_date, end_date, contract_number, buyer_name, buyer_email, buyer_number, seller_name, seller_email, seller_number, who_pay_fee, contract_img, contract_img2, contract_img3, contract_img4, contract_img5, contract_img6, contract_img7, contract_img8, contract_img9, note, added_date, last_update) VALUES 
            ('$id', '$zenovly_type', '$request', '$buyer_id', '$seller_id', '$total_price', '$project_name', '$start_date', '$end_date', '$contract_number', '$buyer_name', '$buyer_email', '$buyer_number', '$seller_name', '$seller_email', '$seller_number', '$who_pay_fee', '$img', '$img2', '$img3', '$img4', '$img5', '$img6', '$img7', '$img8', '$img9', '$note', NOW(), NOW());");
            if($sql->execute())
            {
                $token = base64_encode('zenovly'.$id);
                $token = str_replace ( '=', 'gpsn', $token); 
                $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?to='.$request.'&rd='.date("YmdHis"); 
        		$link = '<a href="'.$link.'" target="_blank">Click here</a>'; 
        		$txt = file_get_contents($this->webURL.'/email/spupc.html');
        		if($request == 1)
        		{
        	        $toname = $buyer_name;
        	        $toemail = $buyer_email;
        	        $fname = $seller_name;
        	    }
        	    else
        	    {
        	        $toname = $seller_name;
        	        $toemail = $seller_email;
        	        $fname = $seller_name;
        	    }
        		$txt = preg_replace(array('/{name}/', '/{cname}/', '/{link}/', '/{utext}/'), array($toname, $fname, $link, $email_body), $txt);
        		if(empty($email_subject))
        		{
        		    if($request == 1)
        		    {
        		        $email_subject = 'Un acheteur vous demande de signer le contrat en ligne - Wezenit';
        		    }
        		    else
        		    {
        		        $email_subject = 'Un vendeur vous demande de signer le contract en ligne - Wezenit';
        		    }
        		}
        		else
        		{
        		    $email_subject = $email_subject.' - Wezenit';
        		}
        		if($toemail) $this->sendMail($email_subject, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($toname), $toemail, $txt, '', '');
        		$data = 1;
            }
        }
        return ($data);
    }
################################################################################ 
    function editZdetail($buyer_id, $seller_id)
    {
        $status = 404;
        $item = "Sorry! we couldn't process, because contract already accepted.";
        $detail = $this->getZdetail('0');
        $buyer_id = $detail['buyer_id'];
        $seller_id = $detail['seller_id'];
        $request = $detail['request'];
        if( ($seller_id == 0) && ($buyer_id == 0) )
        {
            $item = "Sorry! we couldn't process, because contract already accepted.";
        }
        else
        {
            if($buyer_id != $seller_id)
            {
                try
                {
                    if($request == 1)
                    {
                        $status = 6;
                    }
                    else
                    {
                        //pay process
                        $status = 6;
                    }
                    $sql = $this->adapter->query("UPDATE zenovly SET buyer_id = '$buyer_id', seller_id = '$seller_id', status = '$status', last_update = NOW() WHERE id = '$this->id'");
                    if($sql->execute()) 
                    {
                        $item = $detail;
                        $status = 200;
                        if($request == 1)
                        {//from seller
                            $buyer = $this->getUserINFO($buyer_id);
                            $seller = $this->getUserINFO($seller_id);
                            $token = base64_encode('zenovly'.$this->id);
                            $token = str_replace ( '=', 'gpsn', $token);
                            $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?to='.$request.'&rd='.date("YmdHis");
        			        $txt = file_get_contents($this->webURL.'/email/c2user.html');
        			        $txt = preg_replace(array('/{name}/', '/{cname}/', '/{link}/'), array($seller['name'], $buyer['name'], $link), $txt);
        			        $subject = 'Buyer has accepted the online contract - Wezenit';
        			        $this->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($seller['name']), $seller['email'], $txt, '', '');
        			        $txt2 = file_get_contents($this->webURL.'/email/c2s.html');
        			        $txt2 = preg_replace(array('/{name}/', '/{link}/'), array($buyer['name'], $link), $txt2);
        			        $subject2 = 'Thanks for accepting the online contract - Wezenit';
        			        $this->sendMail($subject2, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($buyer['name']), $buyer['email'], $txt2, '', '');
                        }
                        else
                        {
                            $buyer = $this->getUserINFO($buyer_id);
                            $seller = $this->getUserINFO($seller_id);
                            $token = base64_encode('zenovly'.$this->id);
                            $token = str_replace ( '=', 'gpsn', $token);
                            $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?to='.$request.'&rd='.date("YmdHis");
        			        $link = '<a href="'.$link.'" target="_blank">Click here</a>';
        			        $txt = file_get_contents($this->webURL.'/email/c2user.html');
        			        $txt = preg_replace(array('/{name}/', '/{cname}/', '/{link}/'), array($buyer['name'], $seller['name'], $link), $txt);
        			        $subject = 'Seller has accepted the online contract - Wezenit';
        			        $this->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($buyer['name']), $buyer['email'], $txt, '', '');
        			        $txt2 = file_get_contents($this->webURL.'/email/c2s.html');
        			        $txt2 = preg_replace(array('/{name}/', '/{link}/'), array($seller['name'], $link), $txt2);
        			        $subject2 = 'Thanks for accepting the online contract - Wezenit';
        			        $this->sendMail($subject2, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($seller['name']), $seller['email'], $txt2, '', '');
                        }
                    }
                }
                catch (Zend_Exception $e){}
            }
        }
        $data = array(
                            'status' => $status,
                            'item' => $item
                        );
        return ($data);
    }
################################################################################ 
    function getZdetail($noCache)
    {
        $data = array();
        $key_txt = md5('zdetail_lang_'.$this->lang.'_id_'.$this->id);
        $cache = $this->maMemCache($this->cacheTime, $key_txt);
        $data = $cache->getItem($key_txt, $success);
		if( empty($data) || ($this->noCache == 1) || ($noCache == 1) )
		{
	        $sql = $this->adapter->query("SELECT project_name, project_type, request, buyer_id, seller_id, total_price, start_date, end_date, contract_img, contract_img2, contract_img3, contract_img4, contract_img5, contract_img6, contract_img7, contract_img8, contract_img9, contract_number, buyer_name, buyer_email, buyer_number, seller_name, seller_email, seller_number, who_pay_fee, status, note, shipping_tracking_number, last_update FROM `zenovly` WHERE id = '$this->id' LIMIT 1");
            $results = $sql->execute();
            $row = $results->current();
            if(@$row) 
			{
			    $img = '';
			    $img2 = '';
			    $img3 = '';
			    $img4 = '';
			    $img5 = '';
			    $img6 = '';
			    $img7 = '';
			    $img8 = '';
			    $img9 = '';
			    if($row['contract_img']) $img = $this->config['amazon_s3']['urlFile'].'/contract/'.$row['contract_img'];
			    if($row['contract_img2']) $img2 = $this->config['amazon_s3']['urlFile'].'/contract/'.$row['contract_img2'];
			    if($row['contract_img3']) $img3 = $this->config['amazon_s3']['urlFile'].'/contract/'.$row['contract_img3'];
			    if($row['contract_img4']) $img4 = $this->config['amazon_s3']['urlFile'].'/contract/'.$row['contract_img4'];
			    if($row['contract_img5']) $img5 = $this->config['amazon_s3']['urlFile'].'/contract/'.$row['contract_img5'];
			    if($row['contract_img6']) $img6 = $this->config['amazon_s3']['urlFile'].'/contract/'.$row['contract_img6'];
			    if($row['contract_img7']) $img7 = $this->config['amazon_s3']['urlFile'].'/contract/'.$row['contract_img7'];
			    if($row['contract_img8']) $img8 = $this->config['amazon_s3']['urlFile'].'/contract/'.$row['contract_img8'];
			    if($row['contract_img9']) $img9 = $this->config['amazon_s3']['urlFile'].'/contract/'.$row['contract_img9'];
			    
			    $note = '';
			    if($row['note'] != NULL) $note = $row['note'];
			    $contract_number = $row['contract_number'];
			    $buyer_name = $row['buyer_name'];
			    $buyer_email = $row['buyer_email'];
			    $buyer_number = $row['buyer_number'];
			    $seller_name = $row['seller_name'];
			    $seller_email = $row['seller_email'];
			    $seller_number = $row['seller_number'];
			    $who_pay_fee = $row['who_pay_fee'];
			    $total_price = $row['total_price'];
			    $shipping_tracking_number = $row['shipping_tracking_number'];
			    if($shipping_tracking_number == NULL) $shipping_tracking_number = '';
			    $transfer_price = $total_price;
			    $pay_price = $total_price;
			    $pl = 5;
			    foreach ($this->coml as $key => $value)
    		    {
    		        $lp[$key] = explode("-", $value);
    		        if( ($total_price > $lp[$key][0]) && ($total_price < $lp[$key][1]) )
    		        {
    		            $pl = $lp[$key][2];
    		        }
    		    }
    		    if($pl == 10)
    		    {
    		        $com = 10;
    		    }
    		    else
    		    {
    		        $com = ($pl*$total_price)/100;
    		    }
    		    if($who_pay_fee == 1)
    		    {//seller
    		        $transfer_price = $total_price-$com;
			        $pay_price = $total_price;
    		    }
    		    else if($who_pay_fee == 2)
    		    {
    		        $transfer_price = $total_price-($com/2);
			        $pay_price = $total_price+($com/2);
    		    }
    		    else
    		    {//buyer
    		        $transfer_price = $total_price;
			        $pay_price = $total_price+$com;
    		    }
    		    $transfer_price = ceil($transfer_price);
			    $pay_price = ceil($pay_price);
				$data = array(
									'id' => $this->id,
									'request' => $row['request'],
									'project_type' => $row['project_type'],
									'project_name' => $row['project_name'],
									'buyer_id' => $row['buyer_id'],
									'seller_id' => $row['seller_id'],
									'start_date' => $row['start_date'],
									'end_date' => $row['end_date'],
									'note' => $note,
									'contract_number' => $contract_number,
									'buyer_name' => $buyer_name,
									'buyer_email' => $buyer_email,
									'buyer_number' => $buyer_number,
									'seller_name' => $seller_name,
									'seller_email' => $seller_email,
									'seller_number' => $seller_number,
									'who_pay_fee' => $who_pay_fee,
									'total_price' => $total_price,
									'transfer_price' => $transfer_price,
									'pay_price' => $pay_price,
									'status' => $row['status'],
									'shipping_tracking_number' => $shipping_tracking_number,
									'last_update' => $row['last_update'],
									'img' => $img,
									'img2' => $img2,
									'img3' => $img3,
									'img4' => $img4,
									'img5' => $img5,
									'img6' => $img6,
									'img7' => $img7,
									'img8' => $img8,
									'img9' => $img9
							);
				$cache->setItem($key_txt, $data);
			}
		}
		return($data);
    }
################################################################################ 
    function getTZC($t, $status)
    {
        $data = 0;
		if($t == 's')
		{
		    if($status == 'all')
		    {
	            $sql = $this->adapter->query("SELECT COUNT(id) AS c FROM `zenovly` WHERE seller_id = '$this->id' LIMIT 1");
		    }
		    else
		    {
		        $sql = $this->adapter->query("SELECT COUNT(id) AS c FROM `zenovly` WHERE seller_id = '$this->id' AND status = '$status' LIMIT 1");
		    }
		}
		else
		{
		    if($status == 'all')
		    {
			    $sql = $this->adapter->query("SELECT COUNT(id) AS c FROM `zenovly` WHERE buyer_id = '$this->id' LIMIT 1");
		    }
		    else
		    {
		        $sql = $this->adapter->query("SELECT COUNT(id) AS c FROM `zenovly` WHERE buyer_id = '$this->id' AND status = '$status' LIMIT 1");
		    }
		}
        $results = $sql->execute();
        $row = $results->current();
        if(@$row)
		{
		    $data = $row['c'];
		}
		return($data);
    }
################################################################################ 
    function getZC($t, $status)
    {
        $data = array();
		if($t == 's')
		{
		    if($status == 'all')
		    {
	            $sql = $this->adapter->query("SELECT id, project_type, request, project_name, buyer_id, seller_id, total_price, start_date, end_date, contract_img, status, last_update FROM `zenovly` WHERE seller_id = '$this->id' ORDER BY end_date DESC LIMIT $this->pageStart, $this->perpage");
		    }
		    else
		    {
		        $sql = $this->adapter->query("SELECT id, project_type, request, project_name, buyer_id, seller_id, total_price, start_date, end_date, contract_img, status, last_update FROM `zenovly` WHERE seller_id = '$this->id' AND status = '$status' ORDER BY end_date DESC LIMIT $this->pageStart, $this->perpage");
		    }
	    }
		else
		{
		    if($status == 'all')
		    {
			    $sql = $this->adapter->query("SELECT id, project_type, request, project_name, buyer_id, seller_id, total_price, start_date, end_date, contract_img, status, last_update FROM `zenovly` WHERE buyer_id = '$this->id' ORDER BY end_date DESC LIMIT $this->pageStart, $this->perpage");
		    }
		    else
		    {
		        $sql = $this->adapter->query("SELECT id, project_type, request, project_name, buyer_id, seller_id, total_price, start_date, end_date, contract_img, status, last_update FROM `zenovly` WHERE buyer_id = '$this->id' AND status = '$status' ORDER BY end_date DESC LIMIT $this->pageStart, $this->perpage");
		    }
		}
        $results = @$sql->execute();
        $resultSet = new ResultSet;
        if($resultSet)
        {
            $rs = $resultSet->initialize($results);
    		$rsa = $rs->toArray();
    		foreach ($rsa as $key => $value)
    		{
    		    $project_name = $value['project_name'];
    		    if(empty($project_name)) $project_name = '';

    		    $contract_img = $value['contract_img'];
    		    if(empty($contract_img))
    		    {
    		        $contract_img = '';
    		    }
    		    else
    		    {
    		        $contract_img = $this->config['amazon_s3']['urlFile'].'/contract/'.$contract_img;
    		    }
    		    $status = $value['status'];
    		    $end_date = $value['end_date'];
    		    $clickNotGet = 0;
    		    if($status == 5)
    		    {
    		        $today = date('Y-m-d');
                    $day14 = date('Y-m-d', strtotime($end_date . "+14 days"));
                    if(strtotime($day14) <= strtotime($today))
                    {
                        $clickNotGet = 1;
                    }
    		    }
                $data[$key] = array(
                                        'id' => $value['id'],
                                        'project_type' => $value['project_type'],
                                        'project_name' => $project_name,
                                        'buyer' => $this->getUserINFO($value['buyer_id']),
                                        'seller' => $this->getUserINFO($value['seller_id']),
                                        'total_price' => $value['total_price'],
                                        'start_date' => $value['start_date'],
                                        'end_date' => $end_date,
                                        'img' => $contract_img,
                                        'status' => $status,
                                        'clickNotGet' => $clickNotGet,
                                        'last_update' => $value['last_update'],
                                    );
            }
        }
		return($data);
    }
################################################################################ 
    function sendPaidEmail($id)
    {
        $detail = $this->getZdetail('0');
        $buyer_id = $detail['buyer_id'];
        $seller_id = $detail['seller_id'];
        $request = $detail['request'];
        if($seller_id && $buyer_id)
        {
            $buyer = $this->getUserINFO($buyer_id);
            $seller = $this->getUserINFO($seller_id);
            $token = base64_encode('zenovly'.$this->id);
            $token = str_replace ( '=', 'gpsn', $token); 
            $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?rd='.date("YmdHis");
        	$link = '<a href="'.$link.'" target="_blank">Click here</a>';
        	$txt = file_get_contents($this->webURL.'/email/btm.html');
        	$txt = preg_replace(array('/{name}/', '/{cname}/', '/{link}/'), array($seller['name'], $buyer['name'], $link), $txt);
        	$subject = 'The buyer as paid, you can now get ready to ship your item - Wezenit';
        	$this->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($seller['name']), $seller['email'], $txt, '', '');
        	$txt2 = file_get_contents($this->webURL.'/email/ttm.html');
        	$txt2 = preg_replace(array('/{name}/', '/{link}/'), array($buyer['name'], $link), $txt2);
        	$subject2 = 'Thanks for transferring money - Wezenit';
        	$this->sendMail($subject2, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($buyer['name']), $buyer['email'], $txt2, '', '');
        }
    }
################################################################################ 
    function sendAddTrackingEmail($id)
    {
        $detail = $this->getZdetail('0');
        $buyer_id = $detail['buyer_id'];
        $seller_id = $detail['seller_id'];
        $request = $detail['request'];
        if($seller_id && $buyer_id)
        {
            $buyer = $this->getUserINFO($buyer_id);
            $seller = $this->getUserINFO($seller_id);
            $token = base64_encode('zenovly'.$this->id);
            $token = str_replace ( '=', 'gpsn', $token);
            $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?rd='.date("YmdHis");
        	$link = '<a href="'.$link.'" target="_blank">Click here</a>';
        	//$txt = file_get_contents($this->webURL.'/email/atm.html');
        	//$txt = preg_replace(array('/{name}/', '/{cname}/', '/{link}/'), array($seller['name'], $buyer['name'], $link), $txt);
        	//$subject = 'The buyer as paid, you can now get ready to ship your item - Wezenit';
        	//$this->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($seller['name']), $seller['email'], $txt, '', '');
        	$txt2 = file_get_contents($this->webURL.'/email/atm.html');
        	$txt2 = preg_replace(array('/{name}/', '/{link}/'), array($buyer['name'], $link), $txt2);
        	$subject2 = 'Seller shipped item to you already - Wezenit';
        	$this->sendMail($subject2, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($buyer['name']), $buyer['email'], $txt2, '', '');
        }
    }
################################################################################ 
    function sendTZmail($id)
    {
        $detail = $this->getZdetail('0');
        $buyer_id = $detail['buyer_id'];
        $seller_id = $detail['seller_id'];
        $request = $detail['request'];
        if($seller_id && $buyer_id)
        {
            $buyer = $this->getUserINFO($buyer_id);
            $seller = $this->getUserINFO($seller_id);
            $token = base64_encode('zenovly'.$this->id);
            $token = str_replace ( '=', 'gpsn', $token);
            $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?rd='.date("YmdHis");
        	$link = '<a href="'.$link.'" target="_blank">Click here</a>';
        	$txt = file_get_contents($this->webURL.'/email/tz.html');
        	$txt = preg_replace(array('/{name}/', '/{cname}/', '/{link}/'), array($seller['name'], $buyer['name'], $link), $txt);
        	$subject = 'Thanks for using Zenovly. Your service is done - Wezenit';
        	$this->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($seller['name']), $seller['email'], $txt, '', '');
        	$txt2 = file_get_contents($this->webURL.'/email/tz.html');
        	$txt2 = preg_replace(array('/{name}/', '/{link}/'), array($buyer['name'], $link), $txt2); 
        	$subject2 = 'Thanks for using Zenovly. Your service is done - Wezenit';
        	$this->sendMail($subject2, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($buyer['name']), $buyer['email'], $txt2, '', '');
        }
    }
################################################################################ 
    function addBank($mangopay_id, $parameter=[])
    {
        $url_mangopay = $this->mangopayAPI.'act=addBank&userId='.$mangopay_id; 
        
        foreach ($parameter as $key=>$value) {
            $url_mangopay .= '&'.$key.'='.$value; 
        }
        //echo $url_mangopay; exit;
        $data = $this->getService($url_mangopay); 
        
        $rs = json_decode($data); 
        $id = $rs->result; 
       
        if($rs->status==200)  
        { 
            $mangopay_bank_id = (int)$rs->result;
            $sql = $this->adapter->query("UPDATE users SET mangopay_bank_id = ".$mangopay_bank_id.", last_update = NOW() WHERE mangopay_id = '$mangopay_id'");
            $sql->execute();
        }else{  
            $id = 0; 
        }    
        $data = array(   
                        'status'=>$rs->status,
                        'result'=>$rs->result, 
                        'id' => $id, 
                    );
        return $data; 
    }
################################################################################ 
    function payOut($mangopay_id, $walletId, $amount, $bankID)
    {
        $status = '';
        $status = file_get_contents($this->mangopayAPI.'act=payOut&userId='.$mangopay_id.'&walletId='.$walletId.'&amount='.$amount.'&bankID='.$bankID);
        return $status;
    }
    
    
    function getSearch($keyword='')
    {
        $data = array();
		
		$sql = $this->adapter->query("SELECT id, project_type, request, project_name, buyer_id, seller_id, total_price, start_date, end_date, contract_img, status, last_update FROM `zenovly` WHERE project_name LIKE '%".$keyword."%' ORDER BY end_date DESC LIMIT $this->pageStart, $this->perpage");
		
        $results = @$sql->execute();
        $resultSet = new ResultSet;
        if($resultSet)
        {
            $rs = $resultSet->initialize($results);
    		$rsa = $rs->toArray();
    		foreach ($rsa as $key => $value)
    		{
    		    $project_name = $value['project_name'];
    		    if(empty($project_name)) $project_name = '';

    		    $contract_img = $value['contract_img'];
    		    if(empty($contract_img))
    		    {
    		        $contract_img = '';
    		    }
    		    else
    		    {
    		        $contract_img = $this->config['amazon_s3']['urlFile'].'/contract/'.$contract_img;
    		    }
    		    $status = $value['status'];
    		    $end_date = $value['end_date'];
    		    $clickNotGet = 0;
    		    if($status == 5)
    		    {
    		        $today = date('Y-m-d');
                    $day14 = date('Y-m-d', strtotime($end_date . "+14 days"));
                    if(strtotime($day14) <= strtotime($today))
                    {
                        $clickNotGet = 1;
                    }
    		    } 
                $data[$key] = array(
                                        'id' => $value['id'], 
                                        'project_type' => $value['project_type'],
                                        'project_name' => $project_name,
                                        //'buyer' => $this->getUserINFO($value['buyer_id']),
                                        //'seller' => $this->getUserINFO($value['seller_id']),
                                        'total_price' => $value['total_price'],
                                        'start_date' => $value['start_date'],
                                        'end_date' => $end_date,
                                        'img' => $contract_img,
                                        'status' => $status,
                                        'clickNotGet' => $clickNotGet,
                                        'last_update' => $value['last_update'],
                                    );
            }
        }
		return($data);
    }
    
    function getSearchTotal($keyword='')
    {
        $data = 0;
        $key_txt = md5('total_lang_'.$this->lang.'_for_'.$this->for.'_keyword_'.$keyword);
        $cache = $this->maMemCache($this->cacheTime, $key_txt);
        $data = $cache->getItem($key_txt, $success);
		if( empty($data) || ($this->noCache == 1) )
		{
		    $sql = $this->adapter->query("SELECT COUNT(id) AS c FROM `zenovly` WHERE project_name LIKE '%".$keyword."%' LIMIT 1"); 
            $results = $sql->execute(); 
            $row = $results->current();
            if(@$row)
			{
			    $cache->setItem($key_txt, $row['c']);
				$data = $row['c'];
			}
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
}
