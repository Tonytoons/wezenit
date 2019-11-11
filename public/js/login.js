function login()
{
	document.title = 'Login';
	app.loader=false;
	app.detail=false; 
	app.hotPage=false;  
	app.register=false; 
	app.topMenu=true;
	app.login=true;
	window.history.pushState("Login", "Title", baseURL+lang+"/login/");
	app.loader=false; 
	var html = ''; 
    html += '<div class="container">'; 
    html += '<div class="row">';
    html += '<div class="col-md-6 col-md-offset-3">';      
    html += '<div class="panel panel-default">';
    html += '<div class="panel-heading">';
    html += '<h3 class="panel-title"><i class="fa fa-sign-in" aria-hidden="true"></i> '+txt_lang.Login_Title+'</h3>';
    html += '</div>';  
    html += '<div class="panel-body">';  
    html += '<form id="login-form">'; 
    html += '<fieldset>'; 
    html += '<div class="form-group input-group">';
    html += '<span class="input-group-addon"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>';
    html += '<input class="form-control" placeholder="yourmail@example.com" id="login-email" name="email" type="email">';
    html += '</div>';
    html += '<div class="form-group input-group">';
    html += '<span class="input-group-addon"><i class="fa fa-key" aria-hidden="true"></i></span>';
    html += '<input class="form-control" placeholder="'+txt_lang.Password+'" id="login-password"  name="password" type="password" value="">';
    html += '</div>';
    html += '<div class="checkbox">';
    html += '<label>';
    html += '<input name="remember" id="remember" type="checkbox" value="Remember Me"> '+txt_lang.Remember_Me;
    html += '</label>';
    html += '</div>'; 
    html += '<button type="button" onclick="userLogin();"  class="btn btn-lg btn-success btn-block"><i class="fa fa-sign-in" aria-hidden="true"></i> '+txt_lang.Login+'</button>';
    html += '</fieldset>';
    html += '</form>'; 
    html += '<hr/>';    
    html += '<center><h4>'+txt_lang.OR+'</h4></center>';
    html += '<button class="btn btn-lg btn-facebook btn-block" id="loginBtn" type="button" onclick="loginFB();"><i class="fa fa-facebook" aria-hidden="true"></i> '+txt_lang.Login_With_Facebook+'</button>';
    html += '</div>';  
    html += '</div>'; 
    html += '</div>';
    html += '</div>'; 
    html += '</div>';
	window.scrollTo(0, 0);
	document.getElementById("login").innerHTML = html;
}

function userLogin()
{
	var email = document.getElementById('login-email').value; 
	var pass = document.getElementById('login-password').value; 
	var remember = document.getElementById("remember").checked;  
	
	var apiLink = apiURL+'/'+lang+"/login/?username=RockStar&password=Um9ja1N0YXI=";
	if(!validateEmail(email)){ 
		alert('Please enter a valid email address.'); 
		document.getElementById("login-email").focus();
	}else if(pass.length<5){
		alert('Your password must be at least 5 characters long.');
		document.getElementById("login-password").focus();
	}else{  
		new Vue({  
	        created: function ()    
	        {
	            this.fetchData()
	        },
	        methods: 
	        {
	            fetchData: function () 
	            {  
	                apiLink +='&email='+email+"&upassword="+pass; 
	                var xhr = new XMLHttpRequest() 
	                var self = this  
	                xhr.open('GET', apiLink, true) 
	                xhr.onload = function () 
	                {  
	                    var result = JSON.parse(xhr.responseText);
	                    console.log(result);      
	                    if(result.status == 200) 
	                    {  
						 	app.topBtnRegiter=false;
							app.topBtnLogin=false; 
							app.topBtnLogout=true;   
							app.topBtnProfile=true;  
							setCookie("id",result.items.id);
							moreContent('hot', 1, '');   
							if(remember){  
								setCookie('email',email);  
								setCookie('password',pass);
								setCookie('remember',1); 
							}
	                    }
	                    else  
	                    {
	                        alert('Authentication failed.');
	                        document.getElementById('login-password').value='';
	                        app.loader=false;
	                    }
	                    app.loader=false;
	                }
	                xhr.send(); 
	            }
	        }
	    });
	}
}

function userLoginFB(rs)
{
	var apiLink = apiURL+'/'+lang+"/login/?username=RockStar&password=Um9ja1N0YXI=";
	var pass='';
	if(rs.id){  
		new Vue({  
	        created: function ()    
	        {
	            this.fetchData()
	        },
	        methods:  
	        { 
	            fetchData: function () 
	            {  
	                apiLink +='&email='+rs.email+"&upassword="+pass+'&facebook_id='+rs.id;  
	                var xhr = new XMLHttpRequest() 
	                var self = this   
	                xhr.open('GET', apiLink) 
	                xhr.onload = function () 
	                {  
	                    var result = JSON.parse(xhr.responseText);
	                    console.log(result);       
	                    if(result.status == 200)
	                    {  
						 	app.topBtnRegiter=false;
							app.topBtnLogin=false; 
							app.topBtnLogout=true;   
							app.topBtnProfile=true; 
							setCookie("id",result.items.id); 
							/*
							$.each(result.items, function(i, item) { 
								setCookie('user_'+i, item);          	 
							}); 
							*/ 
							moreContent('hot', 1, '');   
	                    }
	                    else 
	                    {
	                        alert('Authentication failed.');    
	                        //document.getElementById('login-password').value='';
	                        app.loader=false;
	                    }
	                    app.loader=false;
	                }
	                xhr.send();  
	            }
	        }
	    });
	}
}