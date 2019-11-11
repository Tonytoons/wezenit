<?php
namespace Application\Models;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Memcached;
use Zend\Cache\Storage\StorageInterface;

use Zend\Db\Sql\Sql;
   
class Blog
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
                    ,view
                    ,img
                    ,active
                    ,name
                    ,last_update
                FROM `blog` 
                WHERE 1 AND name LIKE '%".$search."%' OR detail LIKE '%".$search."%'
                ORDER BY id DESC 
                LIMIT ".$this->pageStart.", ".$this->perpage;
        $query = $this->adapter->query($sql);
        $results = $query->execute(); 
        $resultSet = new ResultSet;
        $data = $resultSet->initialize($results); 
        $data = $data->toArray(); 
       
        $sql2 = "SELECT COUNT(id) AS C FROM `blog` WHERE 1 AND name LIKE '%".$search."%' OR detail LIKE '%".$search."%'"; 
        $statement = $this->adapter->query($sql2);     
        $results = $statement->execute();
        $row = $results->current();    
        $output = array('data'=>$data,'total'=>$row['C']);
        return $output; 
    } 
 
    function add($data)   
    { 
        /*
        $sql = $this->adapter->query("INSERT INTO `blog`  
                                        (id, name, detail, detail_short, active, view, last_update)  
                                       VALUES   
                                        ('".$data['id']."',  
                                         '".$data['name']."', 
                                         '".$data['detail']."',
                                         '".$data['detail_short']."',
                                         '".$data['active']."',
                                         '0',  
                                         '".$this->now."'
                                         );");
        return($sql->execute());
        */
        try{
            $dataInsert = array(   
               'id'=>$data['id'],
               'name'=>$data['name'],
               'detail'=>$data['detail'],
               'detail_short'=>$data['detail_short'],
               'name_en'=>$data['name_en'],
               'detail_en'=>$data['detail_en'],
               'detail_short_en'=>$data['detail_short_en'],
               'active'=>$data['active'], 
               'view'=>0, 
               'last_update'=>$this->now
            ); 
            $adapter = $this->adapter;  
            $sql = new Sql($adapter); 
            $insert = $sql->insert('blog');    
            $insert->values($dataInsert);         
            $statement = $sql->prepareStatementForSqlObject($insert); 
            $result = $statement->execute();  
        }catch (\Exception $e) {
            $result = $e->getMessage(); 
        } 
        return $result;  
    } 

    function edit($data) 
    { 
        /*
        $sql = "UPDATE `blog`  
                SET name='".$data['name']."',
                    detail='".$data['detail']."',
                    detail_short='".$data['detail_short']."', 
                    active='".$data['active']."',
                    last_update='".$this->now."' 
                WHERE id=".$this->id;    
        $sql = $this->adapter->query($sql);
        return($sql->execute()); */ 
        try{
            $dataUpdate = array(  
               'name'=>$data['name'],
               'detail'=>$data['detail'],
               'detail_short'=>$data['detail_short'],
               'name_en'=>$data['name_en'],
               'detail_en'=>$data['detail_en'],
               'detail_short_en'=>$data['detail_short_en'], 
               'active'=>$data['active'], 
               'last_update'=>$this->now
            );  
            $adapter = $this->adapter; 
            $sql = new Sql($adapter);  
            $update = $sql->update('blog');   
            $update->set($dataUpdate);  
            ///print_r($dataUpdate);exit;
            $update->where(array('id' => $this->id));  
            $statement = $sql->prepareStatementForSqlObject($update); 
            $result = $statement->execute(); 
        }catch (\Exception $e) { 
            $result = $e->getMessage(); 
            //print_r($result);  
            //exit; 
        } 
        return($result); 
    }
     
   
    function del()
    { 
       /*  
       $detail = $this->getDetail($this->id);
       if(!empty($detail['img'])){ 
         $pathDelete = 'public/img/blog/'.$detail['img'];      
         @unlink($pathDelete);  
       }  */
       $sql    = "DELETE FROM `blog` WHERE id=".$this->id." LIMIT 1";
   	   $statement = $this->adapter->query($sql);
   	   //$this->delImgBlogAll();      
       return $statement->execute();    
    }
    
    
    public function getNextId() 
    { 
		$sql    = "SELECT MAX(id) + 1 AS id FROM `blog` LIMIT 1";
   		$statement = $this->adapter->query($sql);     
        $results = $statement->execute();
        $row = $results->current();  
		$id     = $row['id'];   
		if($id == NULL) $id = '1';
		return ( $id ); 
	}
	
	
	
	function updateIMG($id, $imgName) 
    { 
        $sql = "UPDATE `blog` 
                SET img='".$imgName."'  
                WHERE id=".$id;    
        $sql = $this->adapter->query($sql); 
        return($sql->execute());
    }
    
    

    function getDetail($id=0) 
    { 
        $sql = "SELECT * FROM `blog` WHERE id=".$id." LIMIT 1";  
        $statement = $this->adapter->query($sql);       
        $results = $statement->execute();
        $row = $results->current();
        return $row; 
    }



/************************ Blog Image *******************************************************/
    
    function getListImgBlog()
    {
        $sql = "SELECT id, image FROM `blog_image` WHERE 1 AND blog_id=".$this->id." ORDER BY id ASC"; 
        $query = $this->adapter->query($sql);
        $results = $query->execute();  
        $resultSet = new ResultSet; 
        $data = $resultSet->initialize($results); 
        $output = $data->toArray(); 
        return $output; 
    }
    
    function getDetailImgBlog($id=0)  
    { 
        $sql = "SELECT * FROM `blog_image` WHERE id=".$id." LIMIT 1"; 
        //echo $sql; 
        $statement = $this->adapter->query($sql);       
        $results = $statement->execute();
        $row = $results->current(); 
        return $row; 
    }
    
    public function getNextIdImgBlog() 
    { 
		$sql    = "SELECT MAX(id) + 1 AS id FROM `blog_image` LIMIT 1";
   		$statement = $this->adapter->query($sql);      
        $results = $statement->execute();
        $row = $results->current();  
		$id     = $row['id'];   
		if($id == NULL) $id = '1';
		return ( $id ); 
	}
 
 
    function addImgBlog($data)  
    { 
        $sql = $this->adapter->query("INSERT INTO `blog_image`  
                                        (id, blog_id, createdate)  
                                       VALUES  
                                        ('".$data['id']."', 
                                         '".$data['blog_id']."', 
                                         '".$this->now."'
                                         );");
        return($sql->execute()); 
    } 
    
    function updateIMGBlog($id, $imgName) 
    { 
        $sql = "UPDATE `blog_image`  
                SET image='".$imgName."'  
                WHERE id=".$id;     
        $sql = $this->adapter->query($sql); 
        return($sql->execute());
    }
    
    function delImgBlog($id)  
    {   
       $detail = $this->getDetailImgBlog($id); 
       if(!empty($detail['image'])){  
           $pathDelete = 'public/img/blog/gallery/'.$detail['image'];      
           @unlink($pathDelete);     
         
           $sql    = "DELETE FROM `blog_image` WHERE id=".$id." LIMIT 1";
       	   $statement = $this->adapter->query($sql);       
           return $statement->execute();  
       }
    }
    
    
    function delImgBlogAll()  
    {   
        /*
        $sql = "SELECT id, image FROM `blog_image` WHERE 1 AND blog_id=".$this->id." ORDER BY id ASC"; 
        $query = $this->adapter->query($sql);
        $results = $query->execute();  
        $resultSet = new ResultSet; 
        $data = $resultSet->initialize($results); 
        $output = $data->toArray();
        foreach($output as $key=>$value){
           if(!empty($value['image'])){  
               $pathDelete = 'public/img/blog/gallery/'.$value['image'];      
               @unlink($pathDelete);
           } 
        } 
        */ 
        $sql = "DELETE FROM `blog_image` WHERE blog_id=".$this->id." LIMIT 1";
   	    $statement = $this->adapter->query($sql);       
        return $statement->execute(); 
    }
    
    
    function checkName($name='')  
    {  
        try{
           $return = 'false';
           if(!empty($name)){  
                $sql = "SELECT COUNT(id) AS C FROM `blog` WHERE 1 AND name LIKE '".$name."'";
                //echo $sql;exit; 
                $statement = $this->adapter->query($sql);     
                $results = $statement->execute();
                $row = $results->current();
                if($row['C']==0)$return = 'true'; 
           }  
        }catch (\Exception $e) {  
            $result = $e->getMessage();
        } 
       
       return $return; 
    }
    
################################################################################ 
}
    