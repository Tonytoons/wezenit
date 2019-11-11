<?php
namespace Application\Models;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Memcached;
use Zend\Cache\Storage\StorageInterface;
    
class Payment
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
    } 

################################################################################ 
    function getPay_Sup_List($search='%') 
    {
        $sql = "SELECT  
                    id
                    ,user_id
                    #,total_price 
                    ,DATE_FORMAT(start_date,'%m/%d/%Y') AS start_date
                    ,DATE_FORMAT(end_date,'%m/%d/%Y') AS end_date
                    #,FORMAT(total_price,2) AS total_price 
                    ,total_price 
                    ,serial_number
                    ,contract_name
                    ,contract_company
                    ,contract_phone
                    ,contract_email
                    ,added_date 
                    ,last_update  
                    ,contract_img
                    ,status  
                FROM `zenovly_contract`   
                WHERE 1  
                    AND status IN ('5','3') 
                    AND serial_number LIKE '%".$search."%'  
                ORDER BY status DESC, id DESC   
                LIMIT ".$this->pageStart.", ".$this->perpage; 
               // echo $sql;
        $query = $this->adapter->query($sql);
        $results = $query->execute(); 
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray();  
        $result = array();
        foreach ($data as $key=>$value) {     
            $value['price'] = number_format($value['total_price'],2); 
            $value['service_percent'] = $this->service_percent;   
            $service_price = ($value['total_price']*$this->service_percent);   
            $value['service_price'] = number_format($service_price,2); 
            $value['total_price'] = number_format(($value['total_price']-$service_price),2);   
            $value['text_status'] = $this->ar_status[$value['status']];   
            $result[] = $value;  
        } 
        $sql2 = "SELECT COUNT(id) AS C FROM `zenovly_contract` WHERE 1  AND status IN ('5','3') 
                AND serial_number LIKE '%".$search."%'"; 
        $statement = $this->adapter->query($sql2);     
        $results = $statement->execute();
        $row = $results->current();     
        $output = array('data'=>$result,'total'=>$row['C']);
        return $output; 
    }
 
    function editStatus($status='0')  
    {  
        $sql = "UPDATE `zenovly_contract`  
                SET status='".$status."',last_update= NOW()    
                WHERE id=".$this->id;      
        $sql = $this->adapter->query($sql);
        return($sql->execute());
    }

################################################################################ 
    function getRefund_List($search='%') 
    {
        $sql = "SELECT  
                    id
                    ,user_id
                    #,total_price 
                    ,DATE_FORMAT(start_date,'%m/%d/%Y') AS start_date
                    ,DATE_FORMAT(end_date,'%m/%d/%Y') AS end_date
                    #,FORMAT(total_price,2) AS total_price 
                    ,total_price 
                    ,serial_number
                    ,contract_name
                    ,contract_company
                    ,contract_phone
                    ,contract_email
                    ,added_date 
                    ,last_update  
                    ,contract_img
                    ,status  
                FROM `zenovly_contract`   
                WHERE 1  
                    AND status IN ('7','5','3') 
                    AND serial_number LIKE '%".$search."%'  
                ORDER BY status ASC, id DESC   
                LIMIT ".$this->pageStart.", ".$this->perpage; 
               // echo $sql;
        $query = $this->adapter->query($sql);
        $results = $query->execute(); 
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray();  
        $result = array();
        foreach ($data as $key=>$value) {     
            $value['price'] = number_format($value['total_price'],2); 
            $value['service_percent'] = $this->service_percent;   
            $service_price = ($value['total_price']*$this->service_percent);   
            $value['service_price'] = number_format($service_price,2); 
            $value['total_price'] = number_format(($value['total_price']-$service_price),2);   
            $value['text_status'] = $this->ar_status[$value['status']];   
            $result[] = $value;   
        } 
        $sql2 = "SELECT COUNT(id) AS C FROM `zenovly_contract` WHERE 1  AND status IN ('7','5','3') 
                AND serial_number LIKE '%".$search."%'"; 
        $statement = $this->adapter->query($sql2);     
        $results = $statement->execute();
        $row = $results->current();     
        $output = array('data'=>$result,'total'=>$row['C']);
        return $output; 
    }    

################################################################################ 
}
    