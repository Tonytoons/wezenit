var apiURL = 'https://dev.zenovly.com/api'; 
var uid = getCookie("uid");         
var facebook_id = getCookie("fid");      
var txt_error = txt_lang.oops_somting;
var click_goform=false;   
var page = 1;
var perpage = 21;
if(getCookie('ck_lang'))lang=getCookie('ck_lang'); 

function validateEmail(email)
{
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}  

function init()
{ 
	if( (document.URL == 'https://safe-tonytoons.c9users.io/public/') || (document.URL == 'https://www.zenovly.com/') || (document.URL == 'https://www.zenovly.com') )
	{
		if(lang == '') lang = 'fr';
		window.location.replace(document.URL+lang+"/");
	}
	
    if(action=='profile'){ 
    	setCookie('uimg',uimg);  
    	setMenu('logout');      	
    }else if(uid){ 
        setMenu('logout');    
    } 
    
    if(action=='form' && uid==''){ 
        //alert('Please log in.'); 
        window.location = baseURL+lang+'/';   
    }  
    
    if(action=='login' && getCookie('loginEmail')!=''){  
    	$('#login-email').val(getCookie('loginEmail')); 
    }
     
    if(action=='consumer' || action=='supplier'){ 
    	getByStatus(0); 
    }
}

function getUserData()
{  
	FB.api('/me', {fields: 'id,name,email'}, function(response)
	{
	    console.log(response);
		var apiLink = apiURL+'/'+lang+"/regis/?username=RockStar&password=Um9ja1N0YXI="; 
		facebook_id = response.id;
		setCookie('fid',facebook_id);
		if(response.email){  
			setCookie('email',response.email); 
			userLoginFB(response);
		}else{      
			response.email = response.id+'@facebook.com'; 
		    userLoginFB(response);  
		} 
	}); 
} 

function connectFB()
{
	FB.login(function(response) {  
		if (response.authResponse) {
			FB.api('/me', {fields: 'id,name,email'}, function(resp) {
				facebook_id = resp.id;  
				setCookie("fid", resp.id);  
				connectUserFB();   
			});
		}  
	}, {scope: 'email,public_profile', return_scopes: true});
}

function loginFB()
{ 
	FB.login(function(response) {
		if (response.authResponse) {
			getUserData();
		}  
	}, {scope: 'email,public_profile', return_scopes: true});
}

function getCookie(cname) 
{ 
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function setCookie(cname, cvalue, exdays, add) 
{
    if(add == 1)
    {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires="+d.toUTCString();
    }
    else
    {
        var today = new Date();
        var yesterday = new Date(today);
        var expires = "expires="+yesterday.setDate(today.getDate() - 1);
    }
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
 
function logOut()
{
    $( "#logout-confirm" ).dialog({
      resizable: false,
      height: "auto",
      width: 400,
      modal: true,
      buttons: {
        "OK": function() {
          $( this ).dialog( "close" ); 
          var cookies = document.cookie.split(";");
	        for (var i = 0; i < cookies.length; i++) 
	        {
	            var cookie = cookies[i];
	            var eqPos = cookie.indexOf("=");
	            var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
	            setCookie(name, '', '', 0);
	        }  
	    	window.location = baseURL+lang+'/';  
        },
        Cancel: function() {
          $( this ).dialog( "close" ); 
        }
      }
    });
}

function userLogin() 
{
	$('#login-error').hide();
	var email = document.getElementById('login-email').value;  
	var pass = document.getElementById('login-password').value;  
	var apiLink = apiURL+'/'+lang+"/login/?username=RockStar&password=Um9ja1N0YXI="; 
	if(!validateEmail(email)){
	    setError('#login-error',txt_lang.alert_valid_email);  
		document.getElementById("login-email").focus();
	}else if(pass.length<5){ 
	    setError('#login-error',txt_lang.alert_password_long); 
		document.getElementById("login-password").focus();  
	}else{  
	    apiLink +='&email='+email+"&upassword="+pass;     
	    axios.get(apiLink).then(function (response) { 
    		var result = response.data; 
    		if(result.status == 200)
            {
                uid = result.items.id;
                setCookie("uid",result.items.id);  
                setCookie("uname",result.items.name);  
                if(result.items.image!='' && result.items.image!=null){      
	                setCookie("uimg", result.items.image_url); 
	            }else{   
	            	setCookie("uimg",'https://files.renovly.com/setting/avatar.jpg'); 
	            }  
                setCookie("utype", result.items.type);     
                
                if(action=='contract'){    
                	setTimeout(function(){     
				 	    window.location.reload();   
				 	},1000);  
                }else if(getCookie('gotopage')!=''){    
                	window.location = getCookie('gotopage'); 
                }else{   
                	window.location = baseURL+lang+'/';    
                }
            }else{
                setError('#login-error', txt_lang.alert_auth_failed);
            } 
            $(btn_loader).buttonLoader('stop'); 
	    }).catch(function (error) {
	    	$(btn_loader).buttonLoader('stop');
			setError('#login-error',error);  
		}); 
	} 
	return false; 
}

function gotoForm()
{
    if(uid==''){ 
    	setCookie('gotopage', baseURL+lang+'/form');
    	setTimeout(function(){ 
    		window.location = baseURL+lang+'/login'; 
    	},200); 
    }else{
        window.location = baseURL+lang+'/form'; 
    }
}
 
function gotoFormSup()
{  
    if(uid==''){   
    	setCookie('gotopage', baseURL+lang+'/supplierform');
    	setTimeout(function(){ 
    		window.location = baseURL+lang+'/login'; 
    	},200); 
    }else{ 
        window.location = baseURL+lang+'/supplierform'; 
    }
}

function setError(thiss,str)
{  
	setTimeout(function ()
	{   
        $(btn_loader).buttonLoader('stop');
    }, 100);
    $(thiss).html(''); 
    var html = '<div class="alert alert-danger alert-dismissable fade in">'; 
    html += '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
    html += str+'</div>'; 
    $(thiss).html(html); 
    $(thiss).slideDown();      
    setTimeout(function(){ 
        $(thiss).slideUp('slow');    
    },3000);    
}

function setMenu(mn)
{  
    if(mn=='logout'){
      var html = '<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="img-profile" style="background: url('+getCookie("uimg")+') center no-repeat;background-size: contain;"></i> '+getCookie("uname")+' <span class="caret"></span></a>';    
          html += '<ul id="login-dp" class="dropdown-menu">';
          html += '<li>';
          html += '<a href="'+baseURL+lang+'/profile"><i class="img-profile-submenu" style="background: url('+getCookie("uimg")+') center no-repeat;background-size: contain;"></i> Profile</a>';   
          html += '</li>';
          html += '<li>';
          html += '<a href="javascript:logOut();"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a>';  
          html += '</li>';
          html += '</ul>';
      $('#user-register').hide();   
      $('#user-login').html(html).show();  
    }
}