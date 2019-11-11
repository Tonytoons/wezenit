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
    } 

################################################################################ 
    function getList($search='%')
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
                    AND serial_number LIKE '%".$search."%' 
                    OR contract_name LIKE '%".$search."%'
                    OR contract_company LIKE '%".$search."%'
                    OR contract_email LIKE '%".$search."%'
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
            $value['service_percent'] = $this->service_percent;   
            $service_price = ($value['total_price']*$this->service_percent);   
            $value['service_price'] = number_format($service_price,2); 
            $value['total_price'] = number_format(($value['total_price']-$service_price),2);   
            $value['text_status'] = $this->ar_status[$value['status']];   
            $result[] = $value;  
        }
        $sql2 = "SELECT COUNT(id) AS C FROM `zenovly_contract` WHERE 1 
                AND serial_number LIKE '%".$search."%' 
                OR contract_name LIKE '%".$search."%'
                OR contract_company LIKE '%".$search."%'
                OR contract_email LIKE '%".$search."%'"; 
        $statement = $this->adapter->query($sql2);     
        $results = $statement->execute();
        $row = $results->current();     
        $output = array('data'=>$result,'total'=>$row['C']);
        return $output; 
    }
 
    function add($data)   
    { 
        $sql = $this->adapter->query("INSERT INTO `zenovly_contract`  
                                        (id,
                                            user_id,
                                            supplier_id,
                                            total_price,
                                            start_date, 
                                            end_date,
                                            serial_number,
                                            project_name,  
                                            contract_name,
                                            contract_company,
                                            company_address,
                                            contract_phone,
                                            contract_landline_phone,
                                            contract_email,
                                            status, 
                                            added_date,  
                                            last_update
                                        )  
                                       VALUES   
                                        ('".$data['id']."', 
                                         '0',
                                         '0',
                                         '".$data['total_price']."',
                                         '".$data['start_date']."',
                                         '".$data['end_date']."',
                                         '".$data['serial_number']."',
                                         '".$data['project_name']."',
                                         '".$data['contract_name']."',
                                         '".$data['contract_company']."',
                                         '".$data['company_address']."',
                                         '".$data['contract_phone']."',
                                         '".$data['contract_landline_phone']."',
                                         '".$data['contract_email']."',
                                         '".$data['status']."',
                                         NOW(),
                                         NOW()
                                         );");
        return($sql->execute());
    } 

    function edit($data) 
    {  
        $sql = "UPDATE `zenovly_contract`  
                SET total_price='".$data['total_price']."',
                    start_date='".$data['start_date']."',   
                    end_date='".$data['end_date']."',  
                    project_name='".$data['project_name']."',
                    serial_number='".$data['serial_number']."',
                    contract_name='".$data['contract_name']."',
                    contract_company='".$data['contract_company']."',
                    company_address='".$data['company_address']."',
                    contract_phone='".$data['contract_phone']."',
                    contract_landline_phone='".$data['contract_landline_phone']."',
                    contract_email='".$data['contract_email']."',
                    status='".$data['status']."', 
                    last_update= NOW()    
                WHERE id=".$this->id;    
        $sql = $this->adapter->query($sql);
        return($sql->execute());
    }
     
   
    function del()
    { 
       $sql    = "DELETE FROM `zenovly_contract` WHERE id=".$this->id." LIMIT 1";
   	   $statement = $this->adapter->query($sql); 
       return $statement->execute();    
    }
    
    
    public function getNextId() 
    { 
		$sql    = "SELECT MAX(id) + 1 AS id FROM `zenovly_contract` LIMIT 1";
   		$statement = $this->adapter->query($sql);     
        $results = $statement->execute();
        $row = $results->current();  
		$id     = $row['id'];   
		if($id == NULL) $id = '1';
		return ( $id ); 
	}
	
	
	
	function updateIMG($id, $imgName) 
    { 
        $sql = "UPDATE `zenovly_contract` 
                SET contract_img='".$imgName."'   
                WHERE id=".$id;     
        $sql = $this->adapter->query($sql); 
        return($sql->execute());
    }
    
    

    function getDetail($id=0) 
    { 
        $sql = "SELECT * FROM `zenovly_contract` WHERE id=".$id." LIMIT 1";  
        $statement = $this->adapter->query($sql);       
        $results = $statement->execute();
        $row = $results->current();
        return $row; 
    } 


    

################################################################################ 
}
    