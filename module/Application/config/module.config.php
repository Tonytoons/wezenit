<?php
$api_url = ($_SERVER['HTTP_HOST']=='safe-tonytoons.c9users.io:80' || $_SERVER['HTTP_HOST']=='safe-tonytoons.c9users.io')?'https://safe-tonytoons.c9users.io/public/api':'https://www.wezenit.com/api';
$db_name = ($_SERVER['HTTP_HOST']=='safe-tonytoons.c9users.io:80' || $_SERVER['HTTP_HOST']=='safe-tonytoons.c9users.io')?'rockstar_dev':'rockstar';

return array( 
    'router' => array(
        'routes' => array(
            
            'index' => array(  
                'type' => 'Segment',  
                'options' => array(
                    'route'    => '[/][:lang/[:action[/][:id/]]]', 
                    'constraints' => array( 
                        'lang'   => '[a-zA-Z]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9_-]*[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'index',
                        'id' => '',
                        //'lang' => 'fr',
                    ),
                ), 
            ),   /*
            'main' => array(
                'type' => 'Segment',
                'options' => array( 
                    'route'    => '/main[/][:lang/[:action[/][:id/]]]',
                    'constraints' => array(
                        'lang'   => '[a-zA-Z]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9_-]*[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Main',
                        'action' => 'index',
                        'id' => '',
                        //'lang' => 'fr',
                    ), 
                ), 
            ),   */
            /*
            'new' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/new/[:lang/[:action[/][:id/]]]',
                    'constraints' => array(
                        'lang'   => '[a-zA-Z]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9_-]*[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\New',
                        'action' => 'index',
                        'id' => '',
                        //'lang' => 'fr',
                    ), 
                ),
            ), 
            */
            'api' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/api[/][:lang[/][:action/[:id/]]]',
                    'constraints' => array(
                        'lang'   => '[a-zA-Z]*',
						'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9_-]*[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Api',
                        'action' => 'index',
                        'lang' => 'en',
                        'id' => '',
                    ),
                ),
            ), 
            'admin' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/admin[/][:action[/][:id]]',
                    'constraints' => array(
                        //'lang'   => '[a-zA-Z]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9_-]*[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Admin', 
                        'action' => 'index',  
                        //'lang' => 'th',
                        'id' => '',
                    ),
                ),
            ), 
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            //add controller
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Api' => 'Application\Controller\ApiController',
            'Application\Controller\Admin' => 'Application\Controller\AdminController',
            //'Application\Controller\New' => 'Application\Controller\NewController',
            //'Application\Controller\Main' => 'Application\Controller\MainController',
        ),
    ),
     
    'view_manager' => array(
        'base_path' => '',
        'doctype' => 'HTML5',
        'template_map' => array(
            #API
            'application/api/index' => __DIR__ . '/../view/api/index.phtml',
            'application/api/content' => __DIR__ . '/../view/api/b.phtml',
			'application/api/regis' => __DIR__ . '/../view/api/b.phtml',
			'application/api/login' => __DIR__ . '/../view/api/b.phtml',
			'application/api/profile' => __DIR__ . '/../view/api/b.phtml',
			'application/api/mail' => __DIR__ . '/../view/api/b.phtml',
			'application/api/makecontract' => __DIR__ . '/../view/api/b.phtml',
			'application/api/contract' => __DIR__ . '/../view/api/b.phtml',
			'application/api/pay' => __DIR__ . '/../view/api/b.phtml',
			'application/api/payrs' => __DIR__ . '/../view/api/b.phtml',
			'application/api/done' => __DIR__ . '/../view/api/b.phtml',
			'application/api/addbank' => __DIR__ . '/../view/api/b.phtml',
			'application/api/payout' => __DIR__ . '/../view/api/b.phtml',
			'application/api/cancelled' => __DIR__ . '/../view/api/b.phtml',
			'application/api/buyerreminder' => __DIR__ . '/../view/api/b.phtml',
			'application/api/sellerreminder' => __DIR__ . '/../view/api/b.phtml',
			'application/api/refund' => __DIR__ . '/../view/api/b.phtml', 
			'application/api/refundemail' => __DIR__ . '/../view/api/b.phtml',  
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/api/search' => __DIR__ . '/../view/api/b.phtml', 
            'application/api/radio' => __DIR__ . '/../view/api/b.phtml', 
            'application/api/checkpayout' => __DIR__ . '/../view/api/b.phtml',   
             
            /* 
            #WEB 
            'application/index/index' => __DIR__ . '/../view/index/index.phtml',
			'application/index/new' => __DIR__ . '/../view/index/index.phtml',
			'application/index/channels' => __DIR__ . '/../view/index/index.phtml',
			'application/index/login' => __DIR__ . '/../view/index/login.phtml',   
			'application/index/register' => __DIR__ . '/../view/index/register.phtml',  
			'application/index/profile' => __DIR__ . '/../view/index/profile.phtml',  
			'application/index/dashboard' => __DIR__ . '/../view/index/profile.phtml',  
			'application/index/newpassword' => __DIR__ . '/../view/index/profile.phtml', 
			'application/index/mail' => __DIR__ . '/../view/api/b.phtml',
			
			'application/index/form' => __DIR__ . '/../view/index/form.phtml',
			'application/index/indexpro' => __DIR__ . '/../view/index/indexpro.phtml',   
			'application/index/forgotpassword' => __DIR__ . '/../view/index/forgotpassword.phtml',
			'application/index/forgotpass' => __DIR__ . '/../view/index/forgotpass.phtml',
			'application/index/contract' => __DIR__ . '/../view/index/contract.phtml',
			'application/index/terms' => __DIR__ . '/../view/index/terms.phtml',
			'application/index/blog' => __DIR__ . '/../view/index/blog.phtml',    
			'application/index/blogdetail' => __DIR__ . '/../view/index/blogdetail.phtml',
			'application/index/consumer' => __DIR__ . '/../view/index/profile.phtml', 
			'application/index/supplier' => __DIR__ . '/../view/index/profile.phtml', 
			'application/index/supplierform' => __DIR__ . '/../view/index/supplierform.phtml', 
			'application/index/contractdetail' => __DIR__ . '/../view/index/contract_detail.phtml', 
			*/ 
			 
			#New Site 25-05-2017
			/*
			'application/index/index' => __DIR__ . '/../view/new/index.phtml',   
			'application/index/projectform' => __DIR__ . '/../view/new/project_form.phtml',
			'application/index/account' => __DIR__ . '/../view/new/account_form.phtml',     
			'application/index/contract' => __DIR__ . '/../view/new/contract.phtml',
			'application/index/contractinfo' => __DIR__ . '/../view/new/contract_info.phtml',  
			'application/index/blog' => __DIR__ . '/../view/new/blog.phtml',
			'application/index/blogdetail' => __DIR__ . '/../view/new/blogdetail.phtml',
			'application/index/what-is-zenovly' => __DIR__ . '/../view/new/whatiszenovly.phtml',
			'application/index/zenovly-privacy-policy' => __DIR__ . '/../view/new/terms.phtml',
			'application/index/profile' => __DIR__ . '/../view/new/profile.phtml', 
			'application/index/forgotpassword' => __DIR__ . '/../view/new/forgotpassword.phtml',
			'application/index/forgotpass' => __DIR__ . '/../view/new/forgotpass.phtml', 
			'application/index/supplier' => __DIR__ . '/../view/new/profile.phtml',  
			'application/index/supplierform' => __DIR__ . '/../view/new/supplierform.phtml',
			'application/index/profile' => __DIR__ . '/../view/new/profile.phtml',     
			'application/index/dashboard' => __DIR__ . '/../view/new/profile.phtml',    
			'application/index/newpassword' => __DIR__ . '/../view/new/profile.phtml',     
			'application/index/seller' => __DIR__ . '/../view/new/profile.phtml',
			'application/index/buyer' => __DIR__ . '/../view/new/profile.phtml',     
			*/ 
			  
			#Main Site 14-08-2018  
			/*
			'application/main/index' => __DIR__ . '/../view/main/index.phtml',       
			'application/main/account' => __DIR__ . '/../view/main/index.phtml',         
			'application/main/what-is-wezenit' => __DIR__ . '/../view/main/index.phtml',
			'application/main/faq' => __DIR__ . '/../view/main/index.phtml',
			'application/main/contact-us' => __DIR__ . '/../view/main/index.phtml',
			'application/main/blog' => __DIR__ . '/../view/main/index.phtml',
			'application/main/blogdetail' => __DIR__ . '/../view/main/index.phtml',
			'application/main/termes-et-condition' => __DIR__ . '/../view/main/index.phtml',
			'application/main/notre-equipe' => __DIR__ . '/../view/main/index.phtml', 
			'application/main/forgotpass' => __DIR__ . '/../view/main/index.phtml',
			'application/main/profile' => __DIR__ . '/../view/main/index.phtml', 
			'application/main/dashboard' => __DIR__ . '/../view/main/index.phtml',    
			'application/main/newpassword' => __DIR__ . '/../view/main/index.phtml',     
			'application/main/seller' => __DIR__ . '/../view/main/index.phtml',
			'application/main/buyer' => __DIR__ . '/../view/main/index.phtml',
			'application/main/mail' => __DIR__ . '/../view/api/b.phtml',
			'application/main/contractinfo' => __DIR__ . '/../view/main/index.phtml',
			'application/main/forgotpassword' => __DIR__ . '/../view/main/index.phtml', 
			'application/main/projectform' => __DIR__ . '/../view/main/index.phtml', 
			'application/main/contract' => __DIR__ . '/../view/main/index.phtml',
			'application/main/search' => __DIR__ . '/../view/main/index.phtml',  
			'application/main/supplier' => __DIR__ . '/../view/main/index.phtml', 
			'application/main/consumer' => __DIR__ . '/../view/main/index.phtml', 
			*/ 
			
			  
			#Index Site 26-10-2018  
			'application/index/index' => __DIR__ . '/../view/main/index.phtml',       
			'application/index/account' => __DIR__ . '/../view/main/index.phtml',         
			'application/index/what-is-wezenit' => __DIR__ . '/../view/main/index.phtml',
			'application/index/faq' => __DIR__ . '/../view/main/index.phtml',
			'application/index/contact-us' => __DIR__ . '/../view/main/index.phtml',
			'application/index/blog' => __DIR__ . '/../view/main/index.phtml',
			'application/index/blogdetail' => __DIR__ . '/../view/main/index.phtml',
			'application/index/termes-et-condition' => __DIR__ . '/../view/main/index.phtml',
			'application/index/notre-equipe' => __DIR__ . '/../view/main/index.phtml', 
			'application/index/forgotpass' => __DIR__ . '/../view/main/index.phtml',
			'application/index/profile' => __DIR__ . '/../view/main/index.phtml', 
			'application/index/dashboard' => __DIR__ . '/../view/main/index.phtml',    
			'application/index/newpassword' => __DIR__ . '/../view/main/index.phtml',     
			'application/index/seller' => __DIR__ . '/../view/main/index.phtml',
			'application/index/buyer' => __DIR__ . '/../view/main/index.phtml',
			'application/index/mail' => __DIR__ . '/../view/api/b.phtml',
			'application/index/contractinfo' => __DIR__ . '/../view/main/index.phtml',
			'application/index/forgotpassword' => __DIR__ . '/../view/main/index.phtml', 
			'application/index/projectform' => __DIR__ . '/../view/main/index.phtml', 
			'application/index/contract' => __DIR__ . '/../view/main/index.phtml',
			'application/index/search' => __DIR__ . '/../view/main/index.phtml',  
			'application/index/supplier' => __DIR__ . '/../view/main/index.phtml', 
			'application/index/consumer' => __DIR__ . '/../view/main/index.phtml',
			'application/index/sitemap' => __DIR__ . '/../view/main/index.phtml',
			'application/index/mywallets' => __DIR__ . '/../view/main/index.phtml',
			'application/index/newproject' => __DIR__ . '/../view/main/index.phtml',
			
			
			
			#ADMIN
			'application/admin/index' => __DIR__ . '/../view/admin/index.phtml',  
			'application/admin/login' => __DIR__ . '/../view/admin/login.phtml',   
			'application/admin/admin' => __DIR__ . '/../view/admin/admin.phtml',
			'application/admin/blog' => __DIR__ . '/../view/admin/blog.phtml',
			'application/admin/setting' => __DIR__ . '/../view/admin/setting.phtml',
			'application/admin/test' => __DIR__ . '/../view/admin/index.phtml', 
			'application/admin/users' => __DIR__ . '/../view/admin/users.phtml',  
			'application/admin/contract' => __DIR__ . '/../view/admin/contract.phtml',
			'application/admin/paysupplier' => __DIR__ . '/../view/admin/payment.phtml',
			'application/admin/paybuyer' => __DIR__ . '/../view/admin/payment.phtml',
			'application/admin/payrefund' => __DIR__ . '/../view/admin/payment.phtml',
			
			
			
			#404
			'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            //__DIR__ . '/../view',
            //'Application' => __DIR__ . '/../view',
            __NAMESPACE__ => __DIR__ . '/../view',
        ),
        'display_not_found_reason' => true,  
        'display_exceptions'       => true,
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        /*  
        'strategies' => array(
            'ViewJsonStrategy', // register JSON renderer strategy
            'ViewFeedStrategy', // register Feed renderer strategy
        ),*/
    ),
    
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
    //DB
    //'Zend\Db',
    'Db' => array(
        'driver' => 'Pdo',
        'dsn' => 'mysql:dbname='.$db_name.';host=rockstardb.cin9ds8s68f4.eu-central-1.rds.amazonaws.com',   
        'driver_options' => array( 
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
        'username' => 'rockstardbmaster',
        'password' => 'RockStarDB2017',  
    ),
    'service_manager' => array( 
        'factories' => array(
            'translator' => 'Zend\\I18n\\Translator\\TranslatorServiceFactory',
            'Zend\\Db\\Adapter\\Adapter' => 'Zend\\Db\\Adapter\\AdapterServiceFactory',
        ),
        'abstract_factories' => [
        \Zend\Db\Adapter\AdapterAbstractServiceFactory::class,
        ],
    ),
    /*
    'admin_level'=>[
        'super_admin'=>[''],
        'admin'=>[''],
    ], */   
    'language' => array(     
        '1' =>['code'=>'en','name'=>'English','label'=>'English'],   
        '2' =>['code'=>'th','name'=>'Thai','label'=>'ภาษาไทย'],         
        //'xx' =>['id'=>2,'name'=>'Thai','label'=>'ภาษาไทย'],
    ),      
    //'Api_url' =>'https://dev.wezenit.com/api',          
    'Api_url' =>$api_url,       
    'Api_username'=>'RockStar',    
    'Api_password'=>'Um9ja1N0YXI=',   
    'contract_status' => ['Pending','Cancelled','Start','Done','Did not get item(or service)','Paid','Waiting for money','Refund','Finished'], 
    'color_status' => ['primary','secondary','success','info','dark','success','warning','danger','success'],    
    'amazon_s3' => [     
                    //'s3URL'=>'https://s3.eu-central-1.amazonaws.com/starter-kit-rockstar',
                    //'s3URL'=>'https://files.renovly.com',
                    //'urlFile'=>'https://files.renovly.com',
                    'urlFile'=>'https://s3.eu-central-1.amazonaws.com/starter-kit-rockstar',
                    'bucket'=>'starter-kit-rockstar',  
                    'config'=>[  
                                'version' => 'latest', 
                                'region' => 'eu-central-1',
                                'credentials' => [
                                        'key' => 'AKIAJ54LBTADWKGQQGMQ',
                                        'secret' => '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'
                                    ]
                               ]
                    ],
    'google_recaptcha' => [    
                            'Site_key'=>'6LfeCx4UAAAAALNOjV30mU9KMakOzTCKSV0kA6FB',  
                            'Secret_key'=>'6LfeCx4UAAAAANaT4sbHHKGWwuJnuiZvvFpFNXb1'
                          ],
    'service_feee'=>[ 
            'fee99999999999'=>'0.025',
            'fee10000'=>'0.035',
            'fee1000'=>'0.05',
            'fee100'=>'10',   
        ]     
        
        /*
        0 to 100: 10 EUR
        101 to 1000: 5% flat
        1001 to 10000: 3.5%
        10001 ++ : 2.5%
        */
); 