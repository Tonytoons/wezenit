<?php
namespace Application\Models;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Memcached;
use Zend\Cache\Storage\StorageInterface;
   
class Setting
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
                        *
                FROM `setting` 
                WHERE 1 AND name LIKE '%".$search."%'
                ORDER BY id DESC 
                LIMIT ".$this->pageStart.", ".$this->perpage;
        $query = $this->adapter->query($sql);
        $results = $query->execute(); 
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray(); 
       
        $sql2 = "SELECT COUNT(id) AS C FROM `setting` WHERE 1 AND name LIKE '%".$search."%' OR detail LIKE '%".$search."%'"; 
        $statement = $this->adapter->query($sql2);     
        $results = $statement->execute();
        $row = $results->current();    
        $output = array('data'=>$data,'total'=>$row['C']);
        return $output; 
    }
 
    function add($data)  
    { 
        $sql = $this->adapter->query("INSERT INTO `setting`  
                                        (id, lang_id, name, description_short,
                                         youtobe, fax, email, phone, facebook,
                                         twitter, instagram, copyright, address,
                                         latitude, longitude, google_analytic,
                                         captcha_secret_key, captcha_site_key,
                                         createdate
                                        )  
                                       VALUES  
                                        ('".$data['id']."', 
                                         '".$data['lang_id']."',
                                         '".$data['name']."',
                                         '".$data['description_short']."',
                                         '".$data['youtobe']."',
                                         '".$data['fax']."',
                                         '".$data['email']."',
                                         '".$data['phone']."',
                                         '".$data['facebook']."',
                                         '".$data['twitter']."',
                                         '".$data['instagram']."',
                                         '".$data['copyright']."',
                                         '".$data['address']."',
                                         '".$data['latitude']."',
                                         '".$data['longitude']."',
                                         '".$data['google_analytic']."',
                                         '".$data['captcha_secret_key']."',
                                         '".$data['captcha_site_key']."', 
                                         '".$this->now."'
                                         );");
        return($sql->execute());
    } 

    function edit($data) 
    { 
        $sql = "UPDATE `setting`  
                SET  
                    name='".$data['name']."', 
                    description_short='".$data['description_short']."',
                    youtobe='".$data['youtobe']."', 
                    fax='".$data['fax']."', 
                    email='".$data['email']."', 
                    phone='".$data['phone']."', 
                    facebook='".$data['facebook']."',
                    twitter='".$data['twitter']."', 
                    instagram='".$data['instagram']."', 
                    copyright='".$data['copyright']."', 
                    address='".$data['address']."',
                    latitude='".$data['latitude']."', 
                    longitude='".$data['longitude']."', 
                    google_analytic='".$data['google_analytic']."',
                    captcha_secret_key='".$data['captcha_secret_key']."', 
                    captcha_site_key='".$data['captcha_site_key']."' 
                WHERE id=".$this->id;     
        $sql = $this->adapter->query($sql);
        return($sql->execute());
    }
     
   
    function del()
    { 
       $detail = $this->getDetail($this->id);
       if(!empty($detail['img'])){ 
         $pathDelete = 'public/img/blog/'.$detail['img'];      
         @unlink($pathDelete);  
       } 
       $sql    = "DELETE FROM `setting` WHERE id=".$this->id." LIMIT 1";
   	   $statement = $this->adapter->query($sql);      
       return $statement->execute();    
    }  
    
    
    public function getNextId() 
    { 
		$sql    = "SELECT MAX(id) + 1 AS id FROM `setting` LIMIT 1";
   		$statement = $this->adapter->query($sql);     
        $results = $statement->execute();
        $row = $results->current();  
		$id     = $row['id'];   
		if($id == NULL) $id = '1';
		return ( $id ); 
	}
	
    function updateIMG($id, $imgName) 
    { 
        $sql = "UPDATE `setting` 
                SET logo='".$imgName."'   
                WHERE id=".$id;    
        $sql = $this->adapter->query($sql); 
        return($sql->execute()); 
    }
 
    function getDetail($lang=1) 
    { 
        $sql = "SELECT * FROM `setting` WHERE 1 AND lang_id=".$lang." LIMIT 1";  
        $statement = $this->adapter->query($sql);        
        $results = $statement->execute();
        $row = $results->current();  
        return $row; 
    }
















################################################################################ 
}
    