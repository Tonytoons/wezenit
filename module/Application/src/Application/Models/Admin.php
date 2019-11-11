<?php
namespace Application\Models;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Memcached;
use Zend\Cache\Storage\StorageInterface;
 
class Admin
{ 
    protected $admins; 
################################################################################ 
	function __construct($adapter, $inLang, $inAction, $inID, $pageStart, $perpage) 
    {
        $this->cacheTime = 360;
        $this->lang = $inLang; 
        $this->action = $inAction;
        $this->id = $inID; 
        $this->adapter = $adapter;
        //$this->page = $inPage;
        $this->perpage = $perpage;  
        $this->pageStart = $pageStart;//($this->perpage*($this->page-1));
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
    } 

################################################################################ 
    function getList($search='')
    {
        $sql = "SELECT 
                    id
                    ,level
                    ,image
                    ,active
                    ,name
                    ,email
                    ,createdate
                FROM admin 
                WHERE 1 AND name LIKE '%".$search."%' OR email LIKE '%".$search."%'
                ORDER BY id DESC 
                LIMIT ".$this->pageStart.", ".$this->perpage;
        $query = $this->adapter->query($sql);
        $results = $query->execute(); 
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray(); 
       
        $sql2 = "SELECT COUNT(id) AS C FROM admin WHERE 1 AND name LIKE '%".$search."%' OR email LIKE '%".$search."%'"; 
        $statement = $this->adapter->query($sql2);     
        $results = $statement->execute();
        $row = $results->current();   
        $output = array('data'=>$data,'total'=>$row['C']);
        return $output; 
    }
 
    function add($data)  
    { 
        $sql = $this->adapter->query("INSERT INTO `admin`  
                                        (id, level, active, name, email, password, phone, createdate)  
                                       VALUES  
                                        ('".$data['id']."', 
                                         '".$data['level']."',
                                         '".$data['active']."',
                                         '".$data['name']."',
                                         '".$data['email']."',
                                         '".md5($data['email'].$data['password'])."',
                                         '".$data['phone']."',
                                         '".$this->now."'
                                         );");
        return($sql->execute());
    } 

    function edit($data) 
    { 
        $sql = "UPDATE `admin` 
                SET level='".$data['level']."',
                    active='".$data['active']."',
                    name='".$data['name']."',
                    email='".$data['email']."',
                    phone='".$data['phone']."'
                WHERE id=".$this->id;  
        $sql = $this->adapter->query($sql);
        return($sql->execute());
    }
     
    function updatePassword($password) 
    { 
        $detail = $this->getDetail($this->id);  
        $sql = "UPDATE `admin` 
                SET password='".md5($detail['email'].$password)."' 
                WHERE id=".$this->id;  
        $sql = $this->adapter->query($sql); 
        return($sql->execute());
    }
    
    function del()
    { 
        /*
       $detail = $this->getDetail($this->id);
       if(!empty($detail['image'])){ 
         $pathDelete = 'public/img/admin/'.$detail['image'];    
         @unlink($pathDelete);  
       } */ 
       $sql    = "DELETE FROM `admin` WHERE id=".$this->id." LIMIT 1";
   	   $statement = $this->adapter->query($sql);      
       return $statement->execute();
    }
    
    
    public function getNextId()
    { 
		$sql    = "SELECT MAX(id) + 1 AS id FROM `admin` LIMIT 1";
   		$statement = $this->adapter->query($sql);     
        $results = $statement->execute();
        $row = $results->current();  
		$id     = $row['id'];   
		if($id == NULL) $id = '1';
		return ( $id ); 
	}
    
    function updateIMG($id, $imgName) 
    { 
        $sql = "UPDATE `admin` 
                SET image='".$imgName."'  
                WHERE id=".$id;    
        $sql = $this->adapter->query($sql); 
        return($sql->execute());
    }

    function getDetail($id=0)
    { 
        $sql = "SELECT * FROM admin WHERE id=".$id." LIMIT 1";  
        $statement = $this->adapter->query($sql);       
        $results = $statement->execute();
        $row = $results->current();
        return $row;
    } 
    
    function checkEmail($email='')    
    {    
       $return = "false";  
       if(!empty($email)){  
            $sql = "SELECT COUNT(id) AS C FROM `admin` WHERE 1 AND email ='".$email."'";  
            $statement = $this->adapter->query($sql);     
            $results = $statement->execute();
            $row = $results->current(); 
            if($row['C']==0)$return = "true"; 
       }  
       return $return; 
    }














################################################################################ 
}
    