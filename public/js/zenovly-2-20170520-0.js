function userRegister()
{
	var name = document.getElementById('regis-name').value;
	var email = document.getElementById('regis-email').value;
	var pass = document.getElementById('regis-password').value;
	var confirmpass = document.getElementById('regis-confirmpass').value; 
	var apiLink = apiURL+'/'+lang+"/regis/?username=RockStar&password=Um9ja1N0YXI=";
	$('#register-display').html(''); 
	if(!name){
		setError('#register-display', txt_lang.alert_full_name);
		document.getElementById("regis-name").focus();
	}else if(!validateEmail(email)){
		setError('#register-display', txt_lang.alert_email_format); 
		document.getElementById("regis-email").focus(); 
	}else if(pass.length<5){
		setError('#register-display', txt_lang.alert_password_long);
		document.getElementById("regis-password").focus();
	}else if(pass!=confirmpass){
		setError('#register-display', txt_lang.alert_confirm_password);
		document.getElementById('regis-confirmpass').value= '';
		document.getElementById("regis-confirmpass").focus(); 
	}else if($('#check_terms').is(':checked')==false){  
		setError('#register-display', txt_lang.alert_terms_of_service);   
	}else{  
		var formData1 = new FormData();
		var captcha = grecaptcha.getResponse(); 
		formData1.append('captcha', captcha); 
		axios.post(baseURL+lang+'/recaptcha', formData1).then(function (rs) {
		  	  var result = rs.data;  
			  console.log(result);   
			 if(result.success==true || captcha !=''){ 
		        var formData = new FormData(); 
				formData.append('email',email);  
				formData.append('upassword', pass);
				formData.append('name', name);
				formData.append('facebook_id', facebook_id); 
		    	axios.post(apiLink, formData).then(function (response) {
		            var result = response.data;
		            console.log(result);    
		            if(result.status == 200)
		            {  
					 	$('#login-email').val('');  
					 	$('#login-password').val(''); 
					 	$('#regis-name').val('');   
		            	$('#regis-email').val('');
		            	$('#regis-password').val('');   
		            	$('#regis-confirmpass').val(''); 
					 	setsSuccess('#register-display',txt_lang.alert_registered); 
					 	setCookie('loginEmail',email); 
		                $('.btn-sigin').buttonLoader('stop');
		                var apiLlogin = apiURL+'/'+lang+"/login/?username=RockStar&password=Um9ja1N0YXI=";
		                apiLlogin +='&email='+email+"&upassword="+pass;     
					    axios.get(apiLlogin).then(function (response2) {
				    		var result2 = response2.data;   
				    		if(result2.status == 200)    
				            {
				                uid = result2.items.id;
				                setCookie("uid",result2.items.id);  
				                setCookie("uname",result2.items.name); 
				                if(result2.items.image!='' && result2.items.image!=null){      
					                setCookie("uimg", result2.items.image_url);  
					            }else{   
					            	setCookie("uimg",'https://files.renovly.com/setting/avatar.jpg'); 
					            }     
				                setCookie("utype", result2.items.type);     
				                
				                if(action=='contract'){     
				                	setTimeout(function(){    
								 	    window.location.reload();   
								 	},3200);  
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
		            else    
		            { 
		               	setError('#register-display', txt_error); 
		            }
		    	}).catch(function (error) {   
					setError('#register-display',error);  
					$(btn_loader).buttonLoader('stop');
				});   
				
	    	}else{   
	    		setError('#register-display', 'This user was not verified by recaptcha.');   
	    	}
	    	
            $(btn_loader).buttonLoader('stop');
            
        }).catch(function (error) {   
			setError('#register-display',error);  
			$(btn_loader).buttonLoader('stop');
		});
	}
}

function forgotnewpassword()
{ 
	$('#forgot-newpass-display').html(''); 
	var forgot_email = document.getElementById('forgot-newemail').value; 
	var forgot_pass = document.getElementById('forgot-newpass').value;  
	var apiLink = apiURL+'/'+lang+"/profile/?username=RockStar&password=Um9ja1N0YXI=&act=changePasswordByEmail";  
	if(forgot_pass.length >= 5){      
		apiLink += '&email='+forgot_email+'&upassword='+forgot_pass;  
	    axios.get(apiLink).then(function (response) {    
    		var result = response.data;  
    		if(result.status == 200)   
            {       
			 	setsSuccess('#forgot-newpass-result', txt_lang.alert_password_successfuly); 
			 	$('#user-login .dropdown-toggle').click(); 
			 	$('#login-email').val(forgot_email);
			 	$('#login-password').val('');   
			 	$('#forgot-newpass').val('');
		        $('#login-password').focus();      
            }  
            else  
            { 
                setError('#forgot-newpass-result', txt_error); 
                $('#forgot-pass').focus();      
            }  
            $(btn_loader).buttonLoader('stop');
		}).catch(function (error) {   
			setError('#forgot-newpass-result',error);  
			$(btn_loader).buttonLoader('stop');   
		});   
	}else{   
		setError('#forgot-newpass-result', txt_lang.alert_password_long);
		$('#forgot-pass').focus();   
	} 
	return false; 
}  

function forgotpassword(){    
	$('#forgot-email-result').html('');   
	var email = document.getElementById('forgot-email').value;   
	var apiLink = apiURL+'/'+lang+"/profile/?username=RockStar&password=Um9ja1N0YXI=&act=forgotPassword";  
	if(validateEmail(email)){      
		apiLink += '&email='+email; 
	    axios.get(apiLink).then(function (response) {    
    		var result = response.data;  
    		if(result.status == 200)    
            {     
			 	$('#forgot-email').val('');
			 	setsSuccess('#forgot-email-result', result.items);
			 	setTimeout(function(){     
			 	  window.location = baseURL+lang+'/login'; 
			 	},6000);       
            }else{  
                setError('#forgot-email-result', txt_error); 
                $('#forgot-email').focus();     
            }   
            $(btn_loader).buttonLoader('stop'); 
		}).catch(function (error) {   
			setError('#forgot-email-result',error);  
			$(btn_loader).buttonLoader('stop');   
		}); 
	}else{     
		setError('#forgot-email-result',txt_lang.alert_valid_email); 
		$('#forgot-email').focus();   
	} 
	return false; 
}

function sendContact()
{	
	$('#contact-result').html(''); 
	$('.prev-step').attr('disabled',true);
	var price = $("#price").val();
	var start_date = $('#start_date').val();
	var end_date = $('#end_date').val();
	var name = $('#full_name').val(); 
	var serial = $("#sireal_number").val(); 
	var project_name = $('#project_name').val();
	var company = $('#company_name').val();
	var address = $('#company_address').val();
	var phone1 = $('#mobile_number').val();
	var phone2 = $('#landline_number').val();
	var email = $('#email').val();  
	var apiLink = apiURL+'/'+lang+"/makecontract/?username=RockStar&password=Um9ja1N0YXI=&act=1";  
	if(base64_file && uid){         
		var formData = new FormData(); 
		start_date = $.date(start_date); 
		end_date = $.date(end_date);  
		formData.append('user_id',uid);     
		formData.append('supplier_id', '');  
		formData.append('total_price', price);
		formData.append('start_date', start_date);
		formData.append('end_date', end_date);
		formData.append('serial_number', serial); 
		formData.append('project_name', project_name);
		formData.append('contract_name', name);
		formData.append('contract_company', company);
		formData.append('company_address', address);
		formData.append('contract_phone', phone1);
		formData.append('contract_landline_phone', phone2);
		formData.append('contract_email', email);   
		formData.append('contract_img', base64_file); 
    	axios.post(apiLink, formData).then(function (response) {
    		var result = response.data;  
    		if(result.status == 200)     
            {     
			 	inputSTEP = 5;    
                var $active = $('.wizard .nav-tabs li.active');
                $active.next().removeClass('disabled');
                nextTab($active);    
                setTimeout(function () {   
                    $(btn_loader).buttonLoader('stop');
                }, 100);   
			 	console.log(result.items); 
			 	return true;      
            }else{   
                setError('#contact-result', txt_error); 
                $('.prev-step').attr('disabled',false);
                return false;
            }   
            $(btn_loader).buttonLoader('stop');  
            
		}).catch(function (error) { 
			setError('#contact-result',error);  
			$('.prev-step').attr('disabled',false);
			return false; 
		}); 
	}else{    
		setError('#contact-result','Note base64 file.');
		$('.prev-step').attr('disabled',false); 
		return false;
	} 

}