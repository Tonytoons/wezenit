function register()
{
	document.title = txt_lang.Register;
	app.loader=true;
	app.topMenu=true;
	app.detail=false;
	app.hotPage=false;    
	app.login=false;  
	app.register=true;  
	window.history.pushState(txt_lang.Register, "Title", baseURL+lang+"/register/");
	app.loader=false;
	var html = '';
    html += '<div class="container">';   
    html += '<div class="row">'; 
    html += '<div class="col-md-6 col-md-offset-3">';      
    html += '<div class="panel panel-default">';
    html += '<div class="panel-heading">'; 
    html += '<h3 class="panel-title"><i class="fa fa-user-plus" aria-hidden="true"></i> '+txt_lang.Register_Title+'</h3>';
    html += '</div>';   
    html += '<div class="panel-body">'; 
    html += '<form id="regis-form">';  
    html += '<fieldset>'; 
    html += '<div class="form-group input-group">'; 
    html += '<span class="input-group-addon"><i class="fa fa-user-o" aria-hidden="true"></i></span>';
    html += '<input id="regis-name" name="fullname" type="text" placeholder="'+txt_lang.Full_name+'" class="form-control input-md" required="">';
    html += '</div>'; 
    html += '<div class="form-group input-group">';
    html += '<span class="input-group-addon"><i class="fa fa-envelope-o" aria-hidden="true"></i></span>';
    html += '<input class="form-control" placeholder="yourmail@example.com" id="regis-email" name="email" type="email">';
    html += '</div>'; 
    html += '<div class="form-group input-group">';
    html += '<span class="input-group-addon"><i class="fa fa-key" aria-hidden="true"></i></span>'; 
    html += '<input class="form-control" placeholder="'+txt_lang.Password+'" id="regis-password"  name="password" type="password" value="">';
    html += '</div>';
    html += '<div class="form-group input-group">';
    html += '<span class="input-group-addon"><i class="fa fa-key" aria-hidden="true"></i></span>';
    html += '<input id="regis-confirmpass" name="confirmpassword" type="password" placeholder="'+txt_lang.Confirm_Password+'" class="form-control input-md">';
    html += '</div>'; 
    html += '<button type="button" onclick="userRegister();" class="btn btn-lg btn-success btn-block"><i class="fa fa-floppy-o" aria-hidden="true"></i> '+txt_lang.Register+'</button>';
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
	document.getElementById("register").innerHTML = html;
}

function userRegister()
{   
	var name = document.getElementById('regis-name').value;
	var email = document.getElementById('regis-email').value;
	var pass = document.getElementById('regis-password').value;
	var confirmpass = document.getElementById('regis-confirmpass').value; 
	var apiLink = apiURL+'/'+lang+"/regis/?username=RockStar&password=Um9ja1N0YXI=";
	
	if(!name){
		alert('Please enter your name.');
		document.getElementById("regis-name").focus();
	}else if(!validateEmail(email)){
		alert('Please enter a valid email address.'); 
		document.getElementById("regis-email").focus();
	}else if(pass.length<5){
		alert('Your password must be at least 5 characters long.');
		document.getElementById("regis-password").focus();
	}else if(pass!=confirmpass){
		alert('Password does not match the confirm password');
		document.getElementById('regis-confirmpass').value= '';
		document.getElementById("regis-confirmpass").focus(); 
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
	                var formData = new FormData();
					formData.append('email',email);  
					formData.append('upassword', pass);
					formData.append('name', name);
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
						 	alert(result.items);    
						 	document.getElementById('login-email').value=email;
						 	document.getElementById("topBtnLogin").click();
	                    } 
	                    else  
	                    {
	                        alert(result.items); 
	                        app.loader=false;
	                    }
	                    app.loader=false;
	                }
	                xhr.send(formData); 
	            }
	        }
	    });
	}
}