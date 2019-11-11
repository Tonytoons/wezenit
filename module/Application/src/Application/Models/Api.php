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

use Zend\Db\Sql\Sql;

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
        $this->now = date('Y-m-d H:i:s');
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
		$host_name = (isset($_SERVER['HTTPS']) ? "https" : "https") . "://".$_SERVER['HTTP_HOST'];
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
		    $sql_str = 'name, detail_short';
		    if($this->lang=='en'){
		        $sql_str = 'name_en AS name, detail_short_en AS detail_short';
		    } 
			if($this->for == 'hot')
			{
	        	$sql = $this->adapter->query("SELECT id, ".$sql_str.", view, img, last_update FROM `blog` WHERE active = '1' ORDER BY view DESC LIMIT $this->pageStart, $this->perpage");
			}//AND (last_update BETWEEN CURDATE() - INTERVAL 100 DAY AND CURDATE() + INTERVAL 1 DAY) 
			else if($this->for == 'new')
			{
				$sql = $this->adapter->query("SELECT id, ".$sql_str.", view, img, last_update FROM `blog` WHERE active = '1' ORDER BY last_update DESC LIMIT $this->pageStart, $this->perpage");
			}
            $results = $sql->execute();
            $resultSet = new ResultSet;
            $data = $resultSet->initialize($results);
			$data = $data->toArray();
			$cache->setItem($key_txt, $data);
		}
		return($data);
    } 
    
    
    function getListAll() 
    {
        $data = array();
		$key_txt = md5('list_all_'.$this->lang.'_page_'.$this->page.'_for_'.$this->for);
        $cache = $this->maMemCache($this->cacheTime, $key_txt);
        $data = $cache->getItem($key_txt, $success);
		if( empty($data) || ($this->noCache == 1) )
		{
		    $sql_str = 'name'; 
		    if($this->lang=='en'){
		        $sql_str = 'name_en AS name';
		    }  
			$sql = $this->adapter->query("SELECT id, ".$sql_str.", last_update FROM `blog` WHERE active = '1'");
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
		    $sql_str = 'name, detail_short, detail';
		    if($this->lang=='en'){
		        $sql_str = 'name_en AS name, detail_short_en AS detail_short, detail_en AS detail';
		    }
		    
	        $sql = $this->adapter->query("SELECT id, ".$sql_str.", view, img, last_update FROM `blog` WHERE active = '1' AND id = '$this->id' LIMIT 1");
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
									'detail_short' => $row['detail_short'],
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
    function getUlogin($facebook_id='', $email='', $upassword='')
    {
        //echo $email; exit;
        if($facebook_id)
        {
            $sql = $this->adapter->query("SELECT id FROM users WHERE facebook_id = '$facebook_id' LIMIT 1");
        }
        else
        {
            if($email && $upassword)
            {
                //echo "SELECT id FROM users WHERE email = '$email' AND password = '$upassword' LIMIT 1";exit;
                $sql = $this->adapter->query("SELECT id FROM users WHERE email = '$email' AND password = '$upassword' AND active = '1' LIMIT 1");
            } 
        } 
        
        if(!empty($sql))
        { 
            $results = $sql->execute();
            $row = $results->current(); 
            //print_r($row); exit;
            if(!empty($row))
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
            $sql = $this->adapter->query("SELECT company_city, company_region, company_postal_code, lastname, nationality, country, city, region, postal_code, facebook_id, mangopay_id, mangopay_wallet, mangopay_bank_id, email, name, phone, gender, birth_day, address, last_update, image, company_id, company_name, company_country, company_address, company_mobile_phone, company_phone, company_email, active, above_position, above_company_name, above_company_address, above_company_website FROM users WHERE id = '$id' LIMIT 1");
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
    								'lastname' => $row['lastname'],
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
    								'company_region' => $row['company_region'],  
    								'company_postal_code' => $row['company_postal_code'],
    								'company_city' => $row['company_city'],
    								'clients' => $clients, 
    								'status_id' => $active,
    								'status' => $status, 
    								'nationality' => $row['nationality'], 
    								'country' => $row['country'], 
    								'postal_code' => $row['postal_code'], 
    								'region' => $row['region'],  
    								'city' => $row['city'],  
    								'last_update' => $row['last_update'],
    								'above_position' => $row['above_position'],
    								'above_company_name' => $row['above_company_name'],
    								'above_company_address' => $row['above_company_address'],
    								'above_company_website' => $row['above_company_website']
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
        $wallet_user_name = $name;
        $name = explode(" ", $name);
        $firstName = $name[0];
        $lastName = $name[count($name)-1];
        $id = $this->getService($this->mangopayAPI.'act=newUser&email='.$email.'&firstName='.$firstName.'&lastName='.$lastName);  
        if($id)  
        {   
            $wallet = $this->getService($this->mangopayAPI.'act=wallet&id='.$id.'&name='.$wallet_user_name);  
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
    function userEdit($id, $email, $name, $phone, $facebook_id, $gender, $birth_day, $address, $lastname='', $nationality='FR', $country='FR', $city='', $region='', $postcode='')  
    {  
        $user = 0;
        try{ 
            $dataUpdate = array(   
               'email'=>$email,
               'name'=>$name,
               'phone'=>$phone,
               'facebook_id'=>$facebook_id,
               'gender'=>$gender,
               'birth_day'=>$birth_day,
               'address'=>$address,
               'lastname'=>$lastname,
               'nationality'=>$nationality,
               'country'=>$country, 
               'city'=>$city, 
               'region'=>$region,
               'postal_code'=>$postcode,
               'last_update'=>$this->now
            );  
            //print_r($dataUpdate); exit;
            $adapter = $this->adapter; 
            $sql = new Sql($adapter);  
            $update = $sql->update('users');   
            $update->set($dataUpdate);   
            $update->where(array('id' => $id));  
            $statement = $sql->prepareStatementForSqlObject($update); 
            $result = $statement->execute(); 
            $user = $this->getUser($id, 1); 
        }catch (\Exception $e) {  
            //$result = $e->getMessage(); 
            //print_r($result);  
            //exit;  
            $user = $this->getUser($id, 1); 
        }
        return($user);
    } 
    
################################################################################ 
    function userCedit($id, $utype, $company_name, $company_address, $company_mobile_phone, $company_phone='', $company_email, $company_id='', $company_country='', $city='', $region='', $postcode='')
    {
        
        $user = 0;
        try{ 
            $dataUpdate = array(    
               'type'=>$utype,
               'company_name'=>$company_name,
               'company_address'=>$company_address,  
               'company_id'=>$company_id,  
               'company_country'=>$company_country, 
               'last_update'=>$this->now
            );     
            
            if(!empty($company_email)) $dataUpdate['company_email'] = $company_email;
            if(!empty($postcode)) $dataUpdate['company_postal_code'] = $postcode;
            if(!empty($region)) $dataUpdate['company_region'] = $region;
            if(!empty($city)) $dataUpdate['company_city'] = $city; 
            if(!empty($company_phone)) $dataUpdate['company_phone'] = $company_phone;
            if(!empty($company_mobile_phone)) $dataUpdate['company_mobile_phone'] = $company_mobile_phone;
             
            
            //print_r($dataUpdate);exit;
            $adapter = $this->adapter; 
            $sql = new Sql($adapter);  
            $update = $sql->update('users');   
            $update->set($dataUpdate);   
            $update->where(array('id' => $id));  
            $statement = $sql->prepareStatementForSqlObject($update); 
            $result = $statement->execute(); 
            $user = $this->getUser($id, 1);    
        }catch (\Exception $e) { 
            /*
            $result = $e->getMessage(); 
            print_r($result);  
            exit;  */ 
            $user = $this->getUser($id, 1); 
        }  
        /*
        try
        {
            $sql = $this->adapter->query("UPDATE users SET type = '$utype', company_name = '$company_name', company_address = '$company_address', company_phone = '$company_phone', company_email = '$company_email', company_mobile_phone = '$company_mobile_phone', company_id = '$company_id', company_country = '$company_country', last_update = NOW() WHERE id = '$id'");
            if($sql->execute()) $user = $this->getUser($id, 1);
        }
        catch (Zend_Exception $e){} */ 
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
					$link = $this->webURL.'/'.$this->lang.'/forgotpassword/?token='.$token.'&rd='.date("YmdHis");
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
                    ->addBcc("contact@wezenit.com")
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
            /*
            $options   = new SmtpOptions(array(
                'name'              => 'wezenit.com',
                'host'              => 'smtp.sendgrid.net',
                'port'              => 587,
                'connection_class'  => 'login',
                'connection_config' => array( 
                    'username' => 'boygpsn', 
                    'password' => '123qwe123', 
                    'ssl'      => 'tls',
                ),
            ));
            */
            
            $options   = new SmtpOptions(array(
                'name'              => 'wezenit.com',
                'host'              => 'smtp.mandrillapp.com',
                'port'              => 587,
                'connection_class'  => 'login',
                'connection_config' => array( 
                    'username' => 'sylvain.demuynck@timeal.com', 
                    'password' => 'L-6yRw-WxAPaj_Mb2xOeLQ', 
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
                    $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?to=1&rd='.date("YmdHis").'&email='.$contract_email;   
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
                    $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?to=2&date='.date("YmdHis").'&email='.$contract_email;     
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
        $sql = $this->adapter->query("SELECT name, lastname, email, phone, image FROM users WHERE id = '$user_id' LIMIT 1");
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
            $lastname = $row['lastname'];
            $email = $row['email'];
            $phone = $row['phone'];
            $data = array(  
                            'id' => $user_id,
                            'name' => $name, 
                            'fullname' => $name.' '.$lastname,
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
	        $sql = $this->adapter->query("SELECT project_name, user_id, supplier_id, total_price, start_date, end_date, serial_number, contract_name, contract_company, company_address, contract_phone, contract_landline_phone, contract_email, contract_img, contract_cover, status, last_update, above_name, company FROM `zenovly_contract` WHERE id = '$this->id' LIMIT 1");
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
									'img' => $img, 
									'above_name'=>$row['above_name'],
									'company'=>$row['company']
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
    function newUser($email, $name, $phone_number, $above_position, $above_company_name, $above_company_address, $above_company_website)
    {
        $id = 0;
        $sql = $this->adapter->query("SELECT id FROM `users` WHERE email = '$email' LIMIT 1");
        $results = $sql->execute();
        $row = $results->current(); 
        if(!empty($row['id'])) $id = $row['id']; 
		
        if( ($id == 0) || empty($id))
        {
            /*
            $sql2 = $this->adapter->query("INSERT INTO users (facebook_id, name, email, phone, password, active, gender, birth_day, type, last_update) VALUES ('', '$name', '$email', '', '', '0', '0', '', 1, NOW());");
           */
           
            $dataInsert = array(
                'facebook_id'=>'', 
                'name'=>$name,
                'email'=>$email,
                'phone'=>$phone_number,
                'password'=>'',
                'active'=>'',
                'gender'=>0,
                'birth_day'=>0, 
                'type'=>1,   
                'last_update'=>$this->now,
                'above_position'=>$above_position,
                'above_company_name'=>$above_company_name,
                'above_company_address'=>$above_company_address,
                'above_company_website'=>$above_company_website 
            );   
            
            $adapter = $this->adapter;  
            $sql = new Sql($adapter);  
            $insert = $sql->insert('users');     
            $insert->values($dataInsert);          
            $statement = $sql->prepareStatementForSqlObject($insert); 
            
            if($statement->execute()){
                $sql3 = $this->adapter->query("SELECT id FROM `users` WHERE email = '$email' LIMIT 1");
                $results3 = $sql3->execute();
                $row3 = $results3->current();
                if(@$row3) $id = $row3['id'];
            } 
            
        }else{  
            
            $dataUpdate = array(  
                'name'=>$name,   
                'phone'=>$phone_number,
                'type'=>1,   
                'last_update'=>$this->now,
                'above_position'=>$above_position,
                'above_company_name'=>$above_company_name,
                'above_company_address'=>$above_company_address,
                'above_company_website'=>$above_company_website 
            );   
            
            $adapter = $this->adapter;  
            $sql = new Sql($adapter);   
            $update = $sql->update('users');   
            $update->set($dataUpdate);
            $update->where(array('id' => $id));    
            $statement = $sql->prepareStatementForSqlObject($update); 
            $result = $statement->execute(); 
        } 
        return ($id);
    } 
     
    function editCustomer($dataUpdate, $id)
    {
        try{
            if(!empty($id)){   
                $adapter = $this->adapter;  
                $sql = new Sql($adapter);   
                $update = $sql->update('users');   
                $update->set($dataUpdate);
                $update->where(array('id' => $id));    
                $statement = $sql->prepareStatementForSqlObject($update); 
                $result = $statement->execute(); 
            } 
            $result = true; 
        }catch (\Exception $e) { 
            $result = false;//$e->getMessage();
        } 
        return($result); 
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
    function zenovlyContract($zenovly_type, $request, $buyer_id, $seller_id, $total_price, $project_name, $start_date, $end_date, $contract_number, $buyer_name, $buyer_email, $buyer_number, $seller_name, $seller_email, $seller_number, $who_pay_fee, $email_subject, $email_body, $note, $contract_img, $contract_img2, $contract_img3, $contract_img4, $contract_img5, $contract_img6, $contract_img7, $contract_img8, $contract_img9, $above_name='1', $company='no')
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
            /*
            $sql = $this->adapter->query("INSERT INTO zenovly (
                id, 
                project_type, 
                request, 
                buyer_id, 
                seller_id, 
                total_price, 
                project_name, 
                start_date, 
                end_date, 
                contract_number, 
                buyer_name, 
                buyer_email, 
                buyer_number, 
                seller_name, 
                seller_email, 
                seller_number, 
                who_pay_fee, 
                contract_img, 
                contract_img2, 
                contract_img3, 
                contract_img4, 
                contract_img5, 
                contract_img6, 
                contract_img7, 
                contract_img8, 
                contract_img9, 
                note, 
                added_date, 
                last_update, 
                above_name
                ) VALUES 
            ('$id', '$zenovly_type', '$request', '$buyer_id', '$seller_id', '$total_price', '$project_name',
            '$start_date', '$end_date', '$contract_number', '$buyer_name', '$buyer_email', '$buyer_number', 
            '$seller_name', '$seller_email', '$seller_number', '$who_pay_fee', '$img', '$img2', '$img3', '$img4', '$img5', '$img6', '$img7', '$img8', '$img9', '$note', NOW(), NOW(), '".$above_name."');"); 
            */ 
            $dataInsert = array(  
                'id'=>$id, 
                'project_type'=>$zenovly_type, 
                'request'=>$request, 
                'buyer_id'=>$buyer_id, 
                'seller_id'=>$seller_id, 
                'total_price'=>$total_price, 
                'project_name'=>$project_name, 
                'start_date'=>$start_date, 
                'end_date'=>$end_date, 
                'contract_number'=>$contract_number, 
                'buyer_name'=>$buyer_name, 
                'buyer_email'=>$buyer_email, 
                'buyer_number'=>$buyer_number, 
                'seller_name'=>$seller_name, 
                'seller_email'=>$seller_email, 
                'seller_number'=>$seller_number, 
                'who_pay_fee'=>$who_pay_fee, 
                'contract_img'=>$img, 
                'contract_img2'=>$img2, 
                'contract_img3'=>$img3, 
                'contract_img4'=>$img4, 
                'contract_img5'=>$img5, 
                'contract_img6'=>$img6, 
                'contract_img7'=>$img7, 
                'contract_img8'=>$img8, 
                'contract_img9'=>$img9, 
                'note'=>$note,   
                'added_date'=>$this->now, 
                'last_update'=>$this->now, 
                'above_name'=>$above_name, 
                'company'=>$company
            );  
            /*
            print_r($dataInsert);
            exit;*/
            $adapter = $this->adapter;  
            $sql = new Sql($adapter); 
            $insert = $sql->insert('zenovly');    
            $insert->values($dataInsert);         
            $statement = $sql->prepareStatementForSqlObject($insert); 
            //$result = $statement->execute();
             
            if($statement->execute())
            { 
                $token = base64_encode('zenovly'.$id); 
                $token = str_replace ( '=', 'gpsn', $token); 
                
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
        	    
        	    $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?to='.$request.'&rd='.date("YmdHis").'&email='.$toemail; 
        		$link = '<a href="'.$link.'" target="_blank">Click here</a>'; 
        		
        		$txt = preg_replace(array('/{name}/', '/{cname}/', '/{link}/', '/{utext}/'), array($toname, $fname, $link, $email_body), $txt);
        		if(empty($email_subject))
        		{
        		    if($request == 1)
        		    {
        		        $email_subject = 'Some buyer has requested you to sign the contract online - Wezenit';
        		    }
        		    else
        		    {
        		        $email_subject = 'Some seller has requested you to sign the contract online - Wezenit';
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
                            $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?to='.$request.'&rd='.date("YmdHis").'&email='.$seller['email'];
        			        $txt = file_get_contents($this->webURL.'/email/c2user.html');
        			        $txt = preg_replace(array('/{name}/', '/{cname}/', '/{link}/'), array($seller['name'], $buyer['name'], $link), $txt);
        			        $subject = 'Buyer has accepted the online contract - Wezenit';
        			        $this->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($seller['name']), $seller['email'], $txt, '', '');
        			        
        			        $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?to='.$request.'&rd='.date("YmdHis").'&email='.$buyer['email']; 
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
                            $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?to='.$request.'&rd='.date("YmdHis").'&email='.$buyer['email'];
        			        $link = '<a href="'.$link.'" target="_blank">Click here</a>';
        			        $txt = file_get_contents($this->webURL.'/email/c2user.html');
        			        $txt = preg_replace(array('/{name}/', '/{cname}/', '/{link}/'), array($buyer['name'], $seller['name'], $link), $txt);
        			        $subject = 'Seller has accepted the online contract - Wezenit';
        			        $this->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($buyer['name']), $buyer['email'], $txt, '', '');
        			        
        			        $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?to='.$request.'&rd='.date("YmdHis").'&email='.$seller['email'];
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
	        $sql = $this->adapter->query("SELECT payIn_id, refund_id, payOut_id, project_name, project_type, request, buyer_id, seller_id, total_price, start_date, end_date, contract_img, contract_img2, contract_img3, contract_img4, contract_img5, contract_img6, contract_img7, contract_img8, contract_img9, contract_number, buyer_name, buyer_email, buyer_number, seller_name, seller_email, seller_number, who_pay_fee, status, note, shipping_tracking_number, last_update, above_name, company FROM `zenovly` WHERE id = '$this->id' LIMIT 1");
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
    		        /*
    		        $transfer_price = $total_price-($com/2);
			        $pay_price = $total_price+($com/2);
			        */  
			        $pay_price = $total_price+$com; 
			        $transfer_price = $pay_price-($com/2); 
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
									'img9' => $img9,
									'above_name'=>$row['above_name'],
									'company'=>$row['company'],
									'payOut_id'=>$row['payOut_id'],
									'refund_id'=>$row['refund_id'],
									'payIn_id'=>$row['payIn_id']  
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
            if(!empty($buyer) && !empty($seller)){
                $token = base64_encode('zenovly'.$this->id);
                $token = str_replace ( '=', 'gpsn', $token); 
                $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?rd='.date("YmdHis");
            	$link = '<a href="'.$link.'" target="_blank">Click here</a>'; 
            	$txt = file_get_contents($this->webURL.'/email/btm.html');
            	$txt = preg_replace(array('/{name}/', '/{cname}/', '/{link}/'), array($seller['name'], $buyer['name'], $link), $txt);
            	$subject = 'The buyer as paid, you can now get ready to ship your item - Wezenit';
            	$this->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($seller['name']), $seller['email'], $txt, '', '');
            	  
            	$link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?rd='.date("YmdHis");
            	$txt2 = file_get_contents($this->webURL.'/email/ttm.html');
            	$txt2 = preg_replace(array('/{name}/', '/{link}/'), array($buyer['name'], $link), $txt2);
            	$subject2 = 'Thanks for transferring money - Wezenit';
            	$this->sendMail($subject2, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($buyer['name']), $buyer['email'], $txt2, '', '');
            	return TRUE;
            }else{ 
                return FALSE;
            } 
        } 
        return FALSE;
    }
    
    
    function sendPayOutEmail($cid)
    { 
        if(!empty($cid)){
            $seller = $this->getUserINFO($this->id); 
            if( !empty($seller)){       
            	$token = base64_encode('zenovly'.$cid); 
                $token = str_replace ( '=', 'gpsn', $token); 
                $link = $this->webURL.'/'.$this->lang.'/contractinfo/'.$cid.'/?rd='.date("YmdHis");
            	$txt2 = file_get_contents($this->webURL.'/email/payOut.html');
            	$txt2 = preg_replace(array('/{name}/', '/{link}/'), array($seller['name'], $link), $txt2);
            	$subject2 = 'Transfer money to your. - Wezenit'; 
            	if($this->lang=='en'){
            	    $subject2 = "Transférer de l'argent vers votre.";
            	} 
            	$this->sendMail($subject2, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($seller['name']), $seller['email'], $txt2, '', '');
            	return TRUE;
            }else{  
                return FALSE;
            }  
        }else{
            return FALSE;
        }
    } 
    
    function sendPayOutEmailByUser($name, $email, $subject='Payout created')
    { 
    	$txt2 = file_get_contents($this->webURL.'/email/payOutByuser.html');
    	$txt2 = preg_replace(array('/{name}/'), array($name), $txt2); 
    	$subject2 = $subject.' - Wezenit';   
    	if($this->lang=='en'){  
    	    $subject2 = $subject." - Wezenit";  
    	} 
    	$this->sendMail($subject2, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($name), $email, $txt2, '', '');
    }
    
    
    function sendRefundEmail($cid, $buyer_id)
    { 
        if(!empty($cid)){   
            $buyer = $this->getUserINFO($buyer_id); 
             
            if( !empty($buyer)){        
            	$token = base64_encode('zenovly'.$cid); 
                $token = str_replace ( '=', 'gpsn', $token); 
                $link = $this->webURL.'/'.$this->lang.'/contractinfo/'.$cid.'/?rd='.date("YmdHis");
            	$txt2 = file_get_contents($this->webURL.'/email/refund.html');
            	$txt2 = preg_replace(array('/{name}/', '/{link}/'), array($buyer['name'], $link), $txt2);
            	
            	$subject2 = "Remboursement à votre.";
            	if($this->lang=='en'){ 
            	    $subject2 = 'Refund to your. - Wezenit'; 
            	}  
            	$this->sendMail($subject2, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($buyer['name']), $buyer['email'], $txt2, '', '');
            	
            }
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
            $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?rd='.date("YmdHis").'&email='.$buyer['email'];
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
            $link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?rd='.date("YmdHis").'&email='.$seller['email']; 
        	$link = '<a href="'.$link.'" target="_blank">Click here</a>';
        	$txt = file_get_contents($this->webURL.'/email/tz.html');
        	$txt = preg_replace(array('/{name}/', '/{cname}/', '/{link}/'), array($seller['name'], $buyer['name'], $link), $txt);
        	$subject = 'Thanks for using Wezenit. Your service is done - Wezenit';
        	$this->sendMail($subject, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($seller['name']), $seller['email'], $txt, '', '');
        	
        	$link = $this->webURL.'/'.$this->lang.'/contract/'.$token.'/?rd='.date("YmdHis").'&email='.$buyer['email'];
        	$txt2 = file_get_contents($this->webURL.'/email/tz.html');
        	$txt2 = preg_replace(array('/{name}/', '/{link}/'), array($buyer['name'], $link), $txt2); 
        	$subject2 = 'Thanks for using Wezenit. Your service is done - Wezenit';
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
        $url = $this->mangopayAPI.'act=payOut&userId='.$mangopay_id.'&walletId='.$walletId.'&amount='.$amount.'&bankID='.$bankID;
        /*
        print($url); 
        exit; */ 
        $result = $this->getService($url);  
        return $result; 
    } 

################################################################################ 
    function refund($mangopay_id, $payInId, $amount, $fee)
    {
        $status = '';  
        
        //echo $this->mangopayAPI.'act=refund&userId='.$mangopay_id.'&PayInId='.$payInId.'&amount='.$amount.'&fee='.$fee;exit;
          
        $result = $this->getService($this->mangopayAPI.'act=refund&userId='.$mangopay_id.'&PayInId='.$payInId.'&amount='.$amount.'&fee='.$fee); 
        //print_r($result); exit; 
        return $result;    
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
    function getWallets($mangopay_id, $mangopay_wallet)
    {
        $status = ''; 
        $url = $this->mangopayAPI.'act=wallets&mangopay_id='.$mangopay_id.'&mangopay_wallet='.$mangopay_wallet;
        $result = $this->getService($url);  
        return $result; 
    }  
     
    
    ################################################################################ 
    function transfers($buyer_mangopay_id, $seller_mangopay_id, $Amount, $Fee, $DebitedWalletId, $CreditedWalletId, $Tag)
    {
        $status = '';
        $url = $this->mangopayAPI.'act=transfers&AuthorId='.$buyer_mangopay_id.'&CreditedUserId='.$seller_mangopay_id.'&Amount='.$Amount.'&Fee='.$Fee.'&DebitedWalletId='.$DebitedWalletId.'&CreditedWalletId='.$CreditedWalletId.'&Tag='.$Tag;
        //echo $url; exit;   
        $result = $this->getService($url);  
        return $result;     
    } 
	 
	 
	function getUserWallets($id=0) 
    { 
        $sql = "SELECT wallet FROM `users` WHERE id=".$id." LIMIT 1";  
        $statement = $this->adapter->query($sql);       
        $results = $statement->execute();
        $row = $results->current();   
        return !empty($row['wallet'])?$row['wallet']:0;    
    }
    
    
    function getUserInfoByMID($mid=0) 
    { 
        $sql = "SELECT id, name, email, wallet  
                FROM `users` WHERE mangopay_id=".$mid." LIMIT 1";  
        $statement = $this->adapter->query($sql);       
        $results = $statement->execute();
        $row = $results->current();   
        return $row;     
    }
     
     
    function updateUserWallets($id=0, $amount=0, $type=1) 
    { 
        //Type = 1=transfer, 2=payout  
        $wallet = $this->getUserWallets($id);
        
        if($type==1){
           $wallet = $wallet+$amount;
        }else{
           $wallet = $wallet-$amount;
           if($wallet<0)$wallet=0;
        }   
        //print_r($wallet); exit;     
        try{ 
            $dataUpdate = array(   
               'wallet'=>$wallet,
               //'last_update'=>$this->now
            );   
            $adapter = $this->adapter; 
            $sql = new Sql($adapter);  
            $update = $sql->update('users');   
            $update->set($dataUpdate);   
            $update->where(array('id' => $id));  
            $statement = $sql->prepareStatementForSqlObject($update); 
            $result = $statement->execute(); 
              
        }catch (\Exception $e) { 
            $result = $e->getMessage(); 
            //print_r($result);  
            //exit; 
        }  
        return($result);
    }
    
    function setWalletsLog($dataInsert, $id=0, $amount=0, $type=1, $status='CREATED', $result='') 
    {  
        try{     
            // Type = 1=transfer, 2=payout
            $this->updateUserWallets($id, $amount, $type);
            $dataInsert['type'] = $type; 
            $dataInsert['status'] = $status; 
            $dataInsert['result'] = $result; 
            $dataInsert['createdate'] = $this->now;
            $dataInsert['lastupdate'] = $this->now;
            $adapter = $this->adapter;  
            $sql = new Sql($adapter); 
            $insert = $sql->insert('wallets_log');    
            $insert->values($dataInsert);         
            $statement = $sql->prepareStatementForSqlObject($insert); 
            $result = $statement->execute();  
        }catch (\Exception $e) {
            $result = $e->getMessage(); 
            //print_r($result);  
            //exit;
        }  
        return $result; 
    }
    
    function updateStatusWalletsLog($id, $user_id, $status='CREATED', $amount=0, $type=0, $str_result='') 
    {  
        try{
            
            $result = []; 
            
            if(!empty($id)  && !empty($status) && !empty($str_result)){   
                 
                if($type==1){
                  $this->updateUserWallets($user_id, $amount, $type);
                } 
                $dataUpdate = array(  
                   'result'=> $str_result,
                   'status'=>$status,
                   'lastupdate'=>$this->now
                ); 
               
                $adapter = $this->adapter; 
                $sql = new Sql($adapter);  
                $update = $sql->update('wallets_log');   
                $update->set($dataUpdate);    
                $update->where(array('id' => $id));  
                $statement = $sql->prepareStatementForSqlObject($update); 
                $result = $statement->execute();  
            }
        }catch (\Exception $e) {
            $result = $e->getMessage(); 
        }  
        return $result; 
    }
    
    
    function viewTransfers($TransferId)
    {
        $status = '';  
        $url = $this->mangopayAPI.'act=viewTransfer&TransferId='.$TransferId;
        //echo $url; exit;       
        $result = $this->getService($url);   
        return $result;     
    } 
    
    function viewPayout($PayOutId)
    {
        $status = '';  
        $url = $this->mangopayAPI.'act=viewPayout&PayOutId='.$PayOutId;
        //echo $url; exit;        
        $result = $this->getService($url);   
        return $result;     
    } 
    
    function uploadKYC($UserId, $Type, $File='')          
    {     
		try    
        {        
            $url = $this->mangopayAPI.'act=uploadKYC&Type='.$Type.'&UserId='.$UserId.'&rm'.strtotime(date("YmdHis"));
            //echo $url;exit;   
            $postData = ['File'=>$File];  
            $output = $this->getService($url, $postData, 'post'); 
        } 
        catch (\Exception $e)   
        {   
            $output = htmlentities($e->getMessage());          
        } 
		return($output);         
    }
    
    
    function getPayoutList($user_id=0) 
    {
        //$this->perpage = 2;
        $str_sql = "SELECT * FROM `wallets_log` WHERE type = 2 AND user_id = ".$user_id." AND status IS NOT NULL ORDER BY id DESC LIMIT ".$this->pageStart.", ".$this->perpage;
		//echo $str_sql;exit;
		$sql = $this->adapter->query($str_sql);
        $results = $sql->execute(); 
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results);
		$data = $data->toArray();
	 
		$items = [];
		foreach($data as $key=>$val){ 
		    $val['createdate'] = date("m/d/Y H:i" ,strtotime($val['createdate'])); 
		    $val['lastupdate'] = date("m/d/Y H:i" ,strtotime($val['lastupdate']));
		    if($val['status']=='CREATED'){   
		       $val['status'] = 'Pending'; 
		    }
		    $val['status'] = ucfirst(strtolower($val['status']));
		     
		    $items[] = $val;
		}   
		
		$sql2 = $this->adapter->query("SELECT COUNT(id) AS C FROM `wallets_log` WHERE type = 2 AND user_id = ".$user_id." AND status IS NOT NULL LIMIT 1"); 
        $results2 = $sql2->execute(); 
        $row = $results2->current(); 
          
        $results = array("results"=>$items, "total"=>(int)$row['C']);  
		return($results); 
    } 
    
     
    
    function getPayoutListType2($limit=20)  
    { 
        $str_sql = "SELECT id, user_id, amount, result_id FROM `wallets_log` WHERE type = 2 AND status = 'CREATED'  ORDER BY id ASC LIMIT ".$limit;
		$sql = $this->adapter->query($str_sql);
        $results = $sql->execute(); 
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results);
		$data = $data->toArray(); 
		return($data);  
    } 
    
    
    function sendEmailPayOutToBankSeller($user_id, $subject='Payout', $text='')
    { 
    	$user = $this->getUserINFO($user_id); 
    	$email = $user['email']; 
    	$name = $user['name'];  
    	$txt2 = file_get_contents($this->webURL.'/email/payOutToBankSeller.html');
    	$txt2 = preg_replace(array('/{name}/'), array($name), $txt2); 
    	$txt2 = preg_replace(array('/{subject}/'), array($subject), $txt2);
    	$txt2 = preg_replace(array('/{txt}/'), array($text), $txt2); 
    	$subject2 = $subject.' - Wezenit';    
    	if($this->lang=='en'){   
    	    $subject2 = $subject." - Wezenit";  
    	}   
    	$this->sendMail($subject2, 'Wezenit SAS', 'contact@wezenit.com', ucfirst($name), $email, $txt2, '', ''); 
    	 
    }
	
################################################################################ 
}
 