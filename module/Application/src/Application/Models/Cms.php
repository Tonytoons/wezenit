<?php
namespace Application\Models;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Memcached;
use Zend\Cache\Storage\StorageInterface;
  
class Cms
{ 
    protected $admins;
    
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
                    ,image 
                    ,active 
                    ,name
                    ,createdate
                    ,lastupdate
                FROM `cmspage` 
                WHERE 1 AND name LIKE '%".$search."%' OR content LIKE '%".$search."%'
                ORDER BY id DESC  
                LIMIT ".$this->pageStart.", ".$this->perpage;
        $query = $this->adapter->query($sql);
        $results = $query->execute(); 
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray(); 
       
        $sql2 = "SELECT COUNT(id) AS C FROM `cmspage` WHERE 1 AND name LIKE '%".$search."%' OR content LIKE '%".$search."%'"; 
        $statement = $this->adapter->query($sql2);     
        $results = $statement->execute();
        $row = $results->current();    
        $output = array('data'=>$data,'total'=>$row['C']);
        return $output; 
    }
 
    function add($data)  
    { 
        $sql = $this->adapter->query("INSERT INTO `cmspage`  
                                        (id, name, content, active, createdate)  
                                       VALUES  
                                        ('".$data['id']."', 
                                         '".$data['name']."',
                                         '".$data['content']."',
                                         '".$data['active']."',
                                         '0',   
                                         '".$this->now."'
                                         );");
        return($sql->execute());
    } 

    function edit($data) 
    { 
        $sql = "UPDATE `cmspage`  
                SET name='".$data['name']."', 
                    content='".$data['detail']."', 
                    active='".$data['active']."',
                    createdate='".$this->now."' 
                WHERE id=".$this->id;    
        $sql = $this->adapter->query($sql);
        return($sql->execute()); 
    }
     
   
    function del()
    { 
       $detail = $this->getDetail($this->id);
       if(!empty($detail['image'])){ 
         $pathDelete = 'public/img/cms/'.$detail['image'];      
         @unlink($pathDelete);  
       }  
       $sql    = "DELETE FROM `cmspage` WHERE id=".$this->id." LIMIT 1";
   	   $statement = $this->adapter->query($sql);
   	   $this->delImgBlogAll();   
       return $statement->execute();    
    }
    
    
    public function getNextId() 
    { 
		$sql    = "SELECT MAX(id) + 1 AS id FROM `cmspage` LIMIT 1";
   		$statement = $this->adapter->query($sql);     
        $results = $statement->execute();
        $row = $results->current();  
		$id     = $row['id'];   
		if($id == NULL) $id = '1';
		return ( $id ); 
	}
	
	
	
	function updateIMG($id, $imgName) 
    { 
        $sql = "UPDATE `cmspage` 
                SET image='".$imgName."'  
                WHERE id=".$id;    
        $sql = $this->adapter->query($sql); 
        return($sql->execute());
    }
    
    

    function getDetail($id=0) 
    { 
        $sql = "SELECT * FROM `cmspage` WHERE id=".$id." LIMIT 1";  
        $statement = $this->adapter->query($sql);       
        $results = $statement->execute();
        $row = $results->current();
        return $row; 
    }

################################################################################ 
}
    