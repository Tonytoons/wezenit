<?php
namespace Application\Models;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Memcached;
use Zend\Cache\Storage\StorageInterface;
    
class Contract 
{ 
    protected $admins;  
    public $ar_status = array('Pending','Accepted','Start','Done','Looking for Supplier','Paid','Waiting for money','Refund');
    public $service_percent = 0.05; 
    
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
        
        $this->apiURL = 'https://dev.zenovly.com/api';  
		if($_SERVER['HTTP_HOST']=='safe-tonytoons.c9users.io'){ 
		    $this->apiURL = 'https://safe-tonytoons.c9users.io/public/api';
		}  
    } 

################################################################################ 
    function getList($search='%')
    {
        $sql = "SELECT  
                    zenovly.*
                    ,DATE_FORMAT(start_date,'%m/%d/%Y') AS start_date
                    ,DATE_FORMAT(end_date,'%m/%d/%Y') AS end_date   
                FROM `zenovly`   
                WHERE 1  
                    AND project_name LIKE '%".$search."%'
                    OR contract_number LIKE '%".$search."%'
                    OR buyer_name LIKE '%".$search."%'
                    OR buyer_email LIKE '%".$search."%' 
                    OR seller_name LIKE '%".$search."%'
                    OR seller_email LIKE '%".$search."%'
                ORDER BY id DESC 
                LIMIT ".$this->pageStart.", ".$this->perpage; 
                //echo $sql;  
        $query = $this->adapter->query($sql);
        $results = $query->execute(); 
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray();  
        $result = array(); 
        
        foreach ($data as $key=>$value) {     
            $value['price'] = number_format($value['total_price'],2); 
            $value['og_price'] = $value['total_price'];  
            if($value['total_price']>1000 && $value['total_price']<=10000){   
                $this->service_percent = 0.035;
            }else if($value['total_price']>10000 && $value['total_price']<=10000000000000000){
                $this->service_percent = 0.035;
            }
            
            //$value['buyer'] = $this->getUserDetail($value['buyer_id']);
            $value['seller'] = $this->getUserDetail($value['seller_id']);
            $value['buyer'] = $this->getUserDetail($value['buyer_id']);
            
            $value['service_percent'] = $this->service_percent;   
            $service_price = ($value['total_price']*$this->service_percent);   
            $value['service_price'] = number_format($service_price,2); 
            $value['total_price'] = number_format(($value['total_price']-$service_price),2);   
            $value['text_status'] = $this->ar_status[$value['status']];   
            $result[] = $value;  
        }
        /* 
        echo "<pre>";
        print_r($result);
        echo "</pre>";
        exit;
        */ 
        $sql2 = "SELECT COUNT(id) AS C FROM `zenovly` WHERE 1 
                AND project_name LIKE '%".$search."%' 
                OR contract_number LIKE '%".$search."%'
                OR buyer_name LIKE '%".$search."%'
                OR buyer_email LIKE '%".$search."%' 
                OR seller_name LIKE '%".$search."%'
                OR seller_email LIKE '%".$search."%'"; 
        $statement = $this->adapter->query($sql2);     
        $results = $statement->execute();
        $row = $results->current();     
        $output = array('data'=>$result,'total'=>$row['C']);
        return $output; 
    } 
    
    
    function getUserDetail($id=0){ 
        $sql = "SELECT * FROM users WHERE id=".$id." LIMIT 1";  
        $statement = $this->adapter->query($sql);       
        $results = $statement->execute();
        $row = $results->current();
        return $row;
    }
 
    function add($insData)    
    { 
        $columns = implode(", ",array_keys($insData));
        $escaped_values = array_map('mysql_real_escape_string', array_values($insData));
        $values  = implode(", ", $escaped_values); 
        $sql = $this->adapter->query("INSERT INTO `zenovly` ($columns) VALUES ($values)");
        return($sql->execute()); 
    } 

    function edit($data)    
    {  
        $sql = 'UPDATE `zenovly` SET last_update=NOW()';
        foreach($data as $key=>$value){ 
            $sql .= ','.$key."='".$value."'";
        } 
        $sql .= " WHERE id=".$this->id;     
        $sql = $this->adapter->query($sql);
        return($sql->execute());
    }
     
   
    function del()
    { 
       $sql    = "DELETE FROM `zenovly` WHERE id=".$this->id." LIMIT 1";
   	   $statement = $this->adapter->query($sql); 
       return $statement->execute();    
    } 
    
    
    public function getNextId() 
    { 
		$sql    = "SELECT MAX(id) + 1 AS id FROM `zenovly` LIMIT 1";
   		$statement = $this->adapter->query($sql);     
        $results = $statement->execute();
        $row = $results->current();  
		$id     = $row['id'];   
		if($id == NULL) $id = '1';
		return ( $id ); 
	}
	
	
	
	function updateIMG($id, $imgName, $newname='') 
    { 
        $sql = "UPDATE `zenovly` 
                SET contract_img".$newname."='".$imgName."'   
                WHERE id=".$id;     
        $sql = $this->adapter->query($sql); 
        return($sql->execute());
    }
    
    

    function getDetail($id=0)  
    { 
        $sql = "SELECT * FROM `zenovly` WHERE id=".$id." LIMIT 1";  
        $statement = $this->adapter->query($sql);       
        $results = $statement->execute();
        $row = $results->current();
        return $row; 
    }  
     

    

################################################################################ 
}
    