//var FB;  
var app = new Vue({ 
	         			el: '#app',
	                	data: {
									detail:false,
									closeMenu:false,
									channels:false,
									topMenu:false,
	                                hotPage:false,
									footer:false,
									login:false,
									register:false,
									profile:false,
	                      			loader:true,
									topBar:true,
									topBtnRegiter:true,
									topBtnLogin:true,
									topBtnLogout:false,
									topBtnProfile:false,
									profileContent:false,
									dashboardContent:false,
									newpassContent:false,
									btnNewUpload:false,
									btnUploadImg:false,
									btnCancelImg:false,
									topHome:'#b8000c',
									topNew:'#626262',
									topMbar:'#626262',
									footHome:'#fff',
									footNew:'#162a36',
									footMbar:'#162a36'
	                   		}
	    		});
var homeType = 'hot';
var apiURL = 'https://www.renovly.com/api';
var channel = '';
var id = getCookie("id");
var facebook_id = '';
function init()
{
    app.profileContent=false;
	app.dashboardContent=false;
	app.newpassContent=false; 
	app.btnNewUpload=false; 
	app.btnUploadImg=false;
	app.btnCancelImg=false;
	
    if(action == 'new')
	{
		homeType = 'new';
		app.loader=false;
		app.closeMenu=false;
		app.detail=false;
		app.channels=false; 
		app.login=false;
		app.register=false;
		app.profile=false;
		app.topBtnProfile=false;
		app.topHome='#626262';
		app.topNew='#b8000c';
		app.topMbar='#626262';
		app.footHome='#162a36';
		app.footNew='#fff';
		app.footMbar='#162a36';
		app.topMenu=true;
		app.hotPage=true;
		
		window.history.pushState("New", "Title", baseURL+lang+"/new/");
		setSeo("New", "New", "New");  
		
	}
	else if(action == 'blog')
	{
		//loadFBapi(); 
		homeType = 'blog';
		app.loader=false;
		app.detail=true;
		app.closeMenu=true;
		app.topMenu=false;
		app.channels=false;
		app.hotPage=false;
		app.login=false;
		app.register=false;
		app.profile=false;
		app.topBtnProfile=false;
		app.topHome='#b8000c';
		app.topNew='#626262';
		app.topMbar='#626262';
		app.footHome='#fff';
		app.footNew='#162a36'; 
		app.footMbar='#162a36';
	}
	else if(action == 'login')
	{
		homeType = 'login';
		app.loader=false;
		app.detail=false;
		app.topMenu=false;
		app.hotPage=false;
		app.register=false;
		app.profile=false;
		app.topHome='#b8000c';
		app.topNew='#626262';
		app.topMbar='#626262'; 
		app.footHome='#fff';
		app.footNew='#162a36';
		app.footMbar='#162a36';
		app.login=true;
		window.history.pushState("Login", "Title", baseURL+lang+"/login/");
		setSeo("Login", "Login", "Login");
		
		if(id) moreContent('hot', 1, '');
	}
	else if(action == 'register')
	{
		homeType = 'register';
		app.loader=false;
		app.detail=false;
		app.topMenu=false;
		app.hotPage=false;
		app.login=false;
		app.register=true;
		app.profile=false;
		app.topHome='#b8000c';
		app.topNew='#626262';
		app.topMbar='#626262';
		app.footHome='#fff';
		app.footNew='#162a36';
		app.footMbar='#162a36';
		window.history.pushState("Register", "Title", baseURL+lang+"/register/"); 
		setSeo("Register", "Register", "Register");
		if(id) moreContent('hot', 1, '');
		 
	}
	else if(action == 'profile' || action == 'newpassword' || action == 'dashboard')
	{
		if(id)
		{    
			profile();
		}
		else
		{
			window.location = baseURL+lang+"/login/"; 
		} 
	} 
	else
	{ 
		homeType = 'hot';
		app.loader=false;
		app.topMenu=true;
		app.hotPage=true;
		app.closeMenu=false;
		app.channels=false;
		app.detail=false;
		app.login=false;
		app.register=false;
		app.profile=false; 
		app.topBtnProfile=false;
		app.topHome='#b8000c';
		app.topNew='#626262';
		app.topMbar='#626262';
		app.footHome='#fff';
		app.footNew='#162a36';
		app.footMbar='#162a36';
		window.history.pushState("Home", "Title", baseURL+lang+'/');
		//setSeo("Home - Hot", "Home hot", "Home,hot");
	}
	app.loader=false;
	if(id){  
		app.topBtnRegiter=false;
		app.topBtnLogin=false; 
		app.topBtnLogout=true;  
		app.topBtnProfile=true; 
	}
}

function makeid()
{
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}

function loadFBapi()
{
	var js = document.createElement('script');
    js.src = '//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=353878298092406&version=v2.0';
	document.getElementById("detail").appendChild(js);
	try 
	{
    	FB.XFBML.parse();
   	} catch (ex) { }
}

function doNothing(){}

function isValidEmail(str)
{
	var filter = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i
	return (filter.test(str));
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
    if (confirm("Press ok to logout!")) 
    {
    	setCookie("id",''); 
        app.loader=false;
        var cookies = document.cookie.split(";");
        for (var i = 0; i < cookies.length; i++) 
        {
            var cookie = cookies[i];
            var eqPos = cookie.indexOf("=");
            var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
            setCookie(name, '', '', 0);
        }
    	location.reload();
    }
}

/**************** FB LOGIN ***************/ 

function getUserData() {  
	FB.api('/me', {fields: 'id,name,email'}, function(response) {
		var apiLink = apiURL+'/'+lang+"/regis/?username=RockStar&password=Um9ja1N0YXI="; 
		facebook_id = response.id;   
		if(response.email){ 
			new Vue({  
		        created: function ()    
		        {
		            this.fetchData() 
		        },  
		        methods: 
		        {
		            fetchData: function () 
		            {  
		                var formData = new FormData(); 
						formData.append('email',response.email);   
						formData.append('upassword', '');
						formData.append('name', response.name); 
						formData.append('facebook_id', facebook_id);
		                var xhr = new XMLHttpRequest()
		                var self = this  
		                xhr.open('POST', apiLink, true) 
		                xhr.onload = function ()  
		                {  
		                    var result = JSON.parse(xhr.responseText);
		                    //console.log(result);      
		                    if(result.status == 200)  
		                    {  
							 	userLoginFB(response);     
		                    }
		                    else    
		                    { 
		                        userLoginFB(response); 
		                    }
		                    app.loader=false;
		                }
		                xhr.send(formData); 
		            }
		        }
		    }); 
		}else{
			document.getElementById("topBtnRegiter").click(); 
			document.getElementById('regis-name').value= response.name;
			document.getElementById('regis-email').value= '';
			
		}
	}); 
} 

window.fbAsyncInit = function() {
	FB.init({  
		appId      : '128202497713838',
		xfbml      : true,
		version    : 'v2.8'
	}); 
	if(id){  
		app.topBtnRegiter=false;
		app.topBtnLogin=false; 
		app.topBtnLogout=true;
		app.topBtnProfile=true;
		console.log('user is authorized');   
	}else{
		FB.getLoginStatus(function(response) { 
			if (response.status === 'connected') {
				console.log('user is authorized');
				//getUserData();  
			} else {
				console.log('user is not authorized'); 
			}   
		});
	} 
	 
	if(getCookie('remember')==1){  
		$('#login-email').val(getCookie('email'));    
		$('#login-password').val(getCookie('password'));
		$('#remember').attr('checked', true);   
	}
};
//load the JavaScript SDK
(function(d, s, id){
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) {return;}
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.com/en_US/sdk.js";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

function loginFB() {
	menuActive(); 
	FB.login(function(response) {
		if (response.authResponse) {
			getUserData();
		} 
	}, {scope: 'email,public_profile', return_scopes: true});
}

function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}   

function menuActive(){ 
	if($('#navbar').height()>55){  
		$(".navbar-toggle").click();
	}
}


window.onpopstate = function(event)
{
	if(history.state == 'New')
	{
		moreContent('new', 1, '');
	}
	else if(history.state == 'Home')
	{
		moreContent('hot', 1, '');
	}
	else if(history.state == 'Profile')
	{
		profileContent('profile');
	}
	else if(history.state == 'Dashboard')
	{
		profileContent('dashboard');
	}
	else if(history.state == 'New Password')
	{
		profileContent('password');    
	}
	else if(history.state == 'Register')
	{
		register();
	}
	else if(history.state == 'Login')
	{
		login();
	}
	else
	{
		getDetail(history.state);
	}
}; 
  
function setMetaTag(metaName, name, value) { 
        $("meta["+metaName+"='"+name+"']").attr("content", value);
}
 
function switchLang(lang){
	var url=window.location.href;   
	if(lang=='fr'){ 
		url = url.replace("/en/", "/fr/"); 
	}else{     
		url = url.replace("/fr/", "/en/"); 
	}
	window.location = url; 
}

function setSeo(title, desc, keywords){  
	document.title = title; 
	setMetaTag('property', 'keywords', title+','+keywords); 
	setMetaTag('property', 'description', desc);
	setMetaTag('property', 'og:title', title);  
	setMetaTag('property', 'og:description', desc); 
	setMetaTag('property', 'og:url', window.location.href);
	setMetaTag('property', 'twitter:title', title);  
	setMetaTag('property', 'twitter:description', desc); 
	setMetaTag('property', 'twitter:site', window.location.href);
}