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
   
class Songs
{ 
    protected $admins;   
################################################################################ 
    function __construct($adapter, $inLang, $inAction, $inID, $pageStart, $perpage) 
    {
        $this->cacheTime = 36000; 
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
    function getList($search='',$station=7, $sortby=1, $genre='all', $anneeEditionMusique='all', $aid='all')
    {
        $key_txt = md5('get_song_list_'.$station.'_'.$sortby.'_'.$genre.'_'.$anneeEditionMusique.'_'.$aid.'_'.$this->pageStart.'_'.$this->perpage.'_'.date("YmdH"));  
        $cache = $this->maMemCache($this->cacheTime, $key_txt);
        $output = $cache->getItem($key_txt); 
        //echo $_SERVER['HTTP_HOST'];
        if($_SERVER['HTTP_HOST'] == 'safe-tonytoons.c9users.io' || $_SERVER['HTTP_HOST'] == 'safe-tonytoons.c9users.io:80'){
            $output = [];
        }  
        $output = [];
		if(empty($output))      
		{  
            $orderby = 's.createdate DESC'; 
            if($sortby==2){
                $orderby = 's.title ASC';  
            }
              
            //$where = 'AND s.station_id = '.$station; 
            $where = ''; 
            $join = '';
            if($genre!='all'){ 
                $where .= " AND g.genre_id IN (".$genre.")"; 
                $join  .= 'RIGHT JOIN `genre_author` g ON s.author_id = g.author_id';
            }
             
            if($anneeEditionMusique=='all'){  
                 
            }else if($anneeEditionMusique=='2000-'.date("Y")){
                $where .= ' AND s.anneeEditionMusique >= 2000 AND  s.anneeEditionMusique <='.date("Y");
            }else if($anneeEditionMusique=='1990-2000'){
                $where .= ' AND s.anneeEditionMusique >= 1990 AND  s.anneeEditionMusique <=2000'; 
            }else if($anneeEditionMusique=='1990'){
                $where .= ' AND s.anneeEditionMusique <= 1990';  
            }else{ 
                $where .= ' AND s.anneeEditionMusique = '.$anneeEditionMusique;
            } 
            
            if($aid!='all'){  
                $where .= ' AND s.author_id = '.$aid; 
            }  
            
            $sql = "SELECT DISTINCT(s.id) AS id, songs, title, anneeEditionMusique, songId, uuid, s.author_id 
                    FROM songs s ".$join." 
                    WHERE 1 ".$where." 
                    ORDER BY ".$orderby."
                    LIMIT ".$this->pageStart.", ".$this->perpage;   
            //echo $sql; exit;       
            $query = $this->adapter->query($sql);
            $results = $query->execute(); 
            $resultSet = new ResultSet;
            $data = $resultSet->initialize($results); 
            $data = $data->toArray();
            $items = [];
             
            foreach($data as $key=>$item){    
                $item['genres'] = $this->getGenres_aid($item['author_id']);
                $item['songs'] = json_decode($item['songs']); 
                $items[] = $item;           
            }    
             
            //$sql2 = "SELECT COUNT(id) AS C FROM `songs` WHERE 1 AND title LIKE '%".$search."%' AND station_id = ".$station;
            $sql2 = "SELECT COUNT(DISTINCT s.id) AS C  
                     FROM songs s ".$join." 
                     WHERE 1 ".$where."
                     ORDER BY ".$orderby;       
             
            $statement = $this->adapter->query($sql2);     
            $results = $statement->execute(); 
            $row = $results->current();        
            $output = array('data'=>$items,'total'=>$row['C']);    
            //if(!empty($output))$cache->setItem($key_txt, $output); 
		}    
        return $output; 
    } 
    
    
    function add($dataInsert)   
    { 
        try{
            $adapter = $this->adapter;  
            $sql = new Sql($adapter); 
            $insert = $sql->insert('songs');    
            $insert->values($dataInsert);         
            $statement = $sql->prepareStatementForSqlObject($insert); 
            $result = $statement->execute();  
        }catch (\Exception $e) {
            $result = $e->getMessage(); 
        } 
        return $result;  
    } 

    function edit($dataUpdate, $sid=0) 
    { 
        try{
            if(!empty($sid)){
                $adapter = $this->adapter; 
                $sql = new Sql($adapter);  
                $update = $sql->update('songs');   
                $update->set($dataUpdate);     
                ///print_r($dataUpdate);exit; 
                $update->where(array('songId' => $sid));    
                $statement = $sql->prepareStatementForSqlObject($update); 
                $result = $statement->execute(); 
            }
            $result = false; 
        }catch (\Exception $e) { 
            $result = $e->getMessage();
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
       $sql    = "DELETE FROM `songs` WHERE id=".$this->id." LIMIT 1";
   	   $statement = $this->adapter->query($sql);
   	   //$this->delImgBlogAll();      
       return $statement->execute();    
    }
    
    
    public function getNextId() 
    { 
		$sql    = "SELECT MAX(id) + 1 AS id FROM `songs` LIMIT 1";
   		$statement = $this->adapter->query($sql);     
        $results = $statement->execute();
        $row = $results->current();  
		$id     = $row['id'];   
		if($id == NULL) $id = '1';
		return ( $id ); 
	}
	
	
	
	function updateIMG($id, $imgName) 
    { 
        $sql = "UPDATE `songs` 
                SET img='".$imgName."'  
                WHERE id=".$id;    
        $sql = $this->adapter->query($sql); 
        return($sql->execute());
    }
    
    

    function getDetail($id=0) 
    { 
        $sql = "SELECT * FROM `songs` WHERE id=".$id." LIMIT 1";  
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
       $return = "false";
       if(!empty($name)){
            $sql = "SELECT COUNT(id) AS C FROM `blog` WHERE 1 AND name LIKE '".$name."'"; 
            $statement = $this->adapter->query($sql);     
            $results = $statement->execute();
            $row = $results->current();
            if($row['C']==0)$return = "true"; 
       } 
       return $return; 
    }
    
    /********************************* Author ******************************************/
    
    public function getNextAuthorId() 
    { 
		$sql    = "SELECT MAX(id) + 1 AS id FROM `author` LIMIT 1";
   		$statement = $this->adapter->query($sql);     
        $results = $statement->execute();
        $row = $results->current();  
		$id     = $row['id'];   
		if($id == NULL) $id = 1;
		return ( $id );  
	}
	
	
	function checkNameAuthor($name='')  
    {   
       $id = 0;  
       if(!empty($name)){
            $sql = "SELECT id FROM `author` WHERE 1 AND author = '".trim($name)."' LIMIT 1"; 
            $statement = $this->adapter->query($sql);     
            $results = $statement->execute();
            $row = $results->current();
            //if($row['C']==0)$return = true; 
            $id = $row['id'];
       }  
       return $id;   
    }
	
	
	function addAuthor($dataInsert)   
    { 
        try{
            $adapter = $this->adapter;  
            $sql = new Sql($adapter); 
            $insert = $sql->insert('author');    
            $insert->values($dataInsert);         
            $statement = $sql->prepareStatementForSqlObject($insert); 
            $result = $statement->execute();  
        }catch (\Exception $e) {
            $result = $e->getMessage(); 
        } 
        return $result;   
    } 
	
	/********************************* genres ******************************************/
    
    public function getMinAuthorId() 
    { 
		$sql    = "SELECT MAX(id) + 1 AS id FROM `author` WHERE status=1 LIMIT 1";
		//echo $sql;exit;
   		$statement = $this->adapter->query($sql);      
        $results = $statement->execute();
        $row = $results->current();  
		$id     = $row['id'];   
		if($id == NULL) $id = 1;
		return ( $id );  
	}
    
    public function getNextGenreId() 
    { 
		$sql    = "SELECT MAX(id) + 1 AS id FROM `genres` LIMIT 1";
   		$statement = $this->adapter->query($sql);     
        $results = $statement->execute();
        $row = $results->current();  
		$id     = $row['id'];   
		if($id == NULL) $id = 1;
		return ( $id );  
	}
	
	
	function addGenre($dataInsert)   
    { 
        try{
            $adapter = $this->adapter;  
            $sql = new Sql($adapter);  
            $insert = $sql->insert('genres');    
            $insert->values($dataInsert);         
            $statement = $sql->prepareStatementForSqlObject($insert); 
            $result = $statement->execute();  
        }catch (\Exception $e) {
            $result = $e->getMessage(); 
        } 
        return $result;   
    } 
    
    function editAuthor($dataUpdate, $sid=1) 
    { 
        try{
            if(!empty($sid)){
                $adapter = $this->adapter; 
                $sql = new Sql($adapter);  
                $update = $sql->update('author');   
                $update->set($dataUpdate);      
                ///print_r($dataUpdate);exit; 
                $update->where(array('id' => $sid));    
                $statement = $sql->prepareStatementForSqlObject($update); 
                $result = $statement->execute(); 
            } 
            $result = true; 
        }catch (\Exception $e) { 
            $result = $e->getMessage();
        } 
        return($result); 
    }
    
    function checkGenre($name='')  
    {   
       $id = 0;  
       if(!empty($name)){
           
            $adapter = $this->adapter; 
            $sql = new Sql($adapter);
            $select = $sql->select();
            $select->from('genres');   
            $select->where(array('genre' => trim($name)));
            
            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute(); 
            /*
            $sql = "SELECT id FROM `genres` WHERE 1 AND genre = '".trim($name)."' LIMIT 1";
            $statement = $this->adapter->query($sql);     
            $results = $statement->execute();*/
            $row = $results->current();
            $id = $row['id'];
       }  
       return $id;   
    }
    
    
	
	function getSpotify($id=1) 
    { 
        $sql = "SELECT * FROM `spotify` WHERE id=".$id." LIMIT 1";  
        $statement = $this->adapter->query($sql);       
        $results = $statement->execute();
        $row = $results->current();
        return $row; 
    }
    
    
    function editSpotify($dataUpdate, $sid=1) 
    { 
        try{
            if(!empty($sid)){
                $adapter = $this->adapter; 
                $sql = new Sql($adapter);  
                $update = $sql->update('spotify');   
                $update->set($dataUpdate);      
                ///print_r($dataUpdate);exit; 
                $update->where(array('id' => $sid));    
                $statement = $sql->prepareStatementForSqlObject($update); 
                $result = $statement->execute(); 
            }
            $result = false; 
        }catch (\Exception $e) { 
            $result = $e->getMessage();
        } 
        return($result); 
    }
     
    function getGenres_aid($id=0) 
    { 
        $key_txt = md5('get_genre_aid_'.$id.'_'.date("Ymd")); 
        $cache = $this->maMemCache($this->cacheTime, $key_txt);
        $output = $cache->getItem($key_txt);  
		if(empty($output))         
		{ 
            $sql = "SELECT genres.id as gid, genre from genre_author
                    RIGHT JOIN genres on genre_author.genre_id = genres.id
                    WHERE 1 AND genre_author.author_id = ".$id."
                    ORDER BY RAND()
                    LIMIT 3
                    ";    
            //$sql = "SELECT * FROM `genres` WHERE author_id=".$id." LIMIT 1";  
            $query = $this->adapter->query($sql); 
            $results = $query->execute();   
            $resultSet = new ResultSet;  
            $data = $resultSet->initialize($results); 
            $output = $data->toArray();   
            /*
            $output = []; 
            foreach($output2 as $key=>$val){ 
                $val['total'] = $this->countGenre($val['genre']);
                $output[] = $val; 
            } */
            
            if(!empty($output))$cache->setItem($key_txt, $output);
		}
        return $output;  
        /* 
        $items = [];
        foreach($output as $key=>$val){ 
            $val['total'] = 0;//$this->countGenre($val['genre']);
            $items[] = $val; 
        }
        return $items;*/
    }
    
    
    
    
    function addGenreAuthor($dataInsert)   
    { 
        try{
            
            $adapter = $this->adapter;  
            $sql = new Sql($adapter); 
            $insert = $sql->insert('genre_author');     
            $insert->values($dataInsert);         
            $statement = $sql->prepareStatementForSqlObject($insert); 
            $result = $statement->execute();  
        }catch (\Exception $e) {
            $result = $e->getMessage(); 
        } 
        return $result;   
    } 
    
    
    function getGenreList()    
    { 
        try{
            
            $key_txt = md5('get_genre_list_'.date("YmdH"));
            $cache = $this->maMemCache($this->cacheTime, $key_txt);
            $output = $cache->getItem($key_txt);  
    		if( empty($output))    
    		{
                $sql = "SELECT * FROM genres ORDER BY genre ASC"; 
                $query = $this->adapter->query($sql); 
                $results = $query->execute();   
                $resultSet = new ResultSet; 
                $data = $resultSet->initialize($results); 
                $output = $data->toArray();  
                if(!empty($output))$cache->setItem($key_txt, $output);
    		}  
        }catch (\Exception $e) {
            $output = $e->getMessage(); 
        }  
        return $output;   
    } 
    
    function getYearList()    
    { 
        try{ 
            $key_txt = md5('get_year_list_'.date("Ymd")); 
            $cache = $this->maMemCache($this->cacheTime, $key_txt);
            $output = $cache->getItem($key_txt);    
    		if( empty($output))    
    		{ 
                $sql = "
                SELECT DISTINCT(anneeEditionMusique) AS year 
                FROM songs  
                WHERE anneeEditionMusique >= 2016
                ORDER BY anneeEditionMusique DESC";      
                
                $query = $this->adapter->query($sql);  
                $results = $query->execute();   
                $resultSet = new ResultSet; 
                $data = $resultSet->initialize($results); 
                $output = $data->toArray();
                if(!empty($output))$cache->setItem($key_txt, $output);
    		}
        }catch (\Exception $e) { 
            $output = $e->getMessage();  
        } 
        return $output;   
    } 
    
    function countGenre($name='') 
    {    
       
        $key_txt = md5('get_count_genre_'.str_replace(' ','_',$name).'_'.date("Ymd"));  
        $cache = $this->maMemCache($this->cacheTime, $key_txt); 
        $output = $cache->getItem($key_txt);    
		if(empty($output))     
		{ 
            $sql = "SELECT count(s.id) as c
                    FROM songs s 
                    LEFT JOIN genre_author ag ON s.author_id = ag.author_id
                    LEFT JOIN genres g on ag.genre_id = g.id
                    WHERE 1 AND  g.`genre` = '".$name."'";  
            $statement = $this->adapter->query($sql);        
            $results = $statement->execute();
            $row = $results->current();
            $output = $row['c'];  
            if(!empty($output))$cache->setItem($key_txt, $output);
		}
        return $output;
        return 0;
        
    }
    
    
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
    