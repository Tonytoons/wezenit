function userLoginFB(rs) 
{
	var apiLink = apiURL+'/'+lang+"/login/?username=RockStar&password=Um9ja1N0YXI=&rd="+makeid(); 
	var pass=''; 
	if(rs.id){ 
        apiLink +='&email='+rs.email+"&upassword="+pass+'&facebook_id='+rs.id; 
        
	    axios.get(apiLink).then(function (response) {
    		var result = response.data; 
    		if(result.status == 200)  
            {   
                uid = result.items.id; 
                setCookie("uid",result.items.id);  
                setCookie("uname",result.items.name);
                if(result.items.image!=''){     
	                setCookie("uimg",result.items.image_url); 
	            }else{  
	            	setCookie("uimg",'https://files.renovly.com/setting/avatar.jpg'); 
	            }  
                setCookie("utype", result.items.type);   
                setMenu('logout');  
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
            	userRegisterFB(rs); 
            } 
            $(btn_loader).buttonLoader('stop');  
	    }).catch(function (error) {
	    	if(action=='login')setError('#login-error', error);  
            if(action=='register')setError('#register-display', error); 
			$(btn_loader).buttonLoader('stop');
		});           
	} 
}

function getSupplier() 
{ 
	var apiLink = apiURL+'/'+lang+"/profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&rd="+makeid();
	var data = [];   
	if(uid){  
		axios.get(apiLink).then(function (response) {
			var result = response.data;
			if(result.status == 200 && action == 'profile')
            {  
			 	if(!result.items.birth_day) result.items.birth_day = yyyy+'-'+mm+'-'+dd;
			 	document.getElementById('profile-name').value = result.items.name; 
				document.getElementById('profile-email').value = result.items.email;
				document.getElementById('profile-birthday').value = result.items.birth_day;
				document.getElementById('profile-gender').value = result.items.gender;
				document.getElementById('profile-phone').value = result.items.phone;
				document.getElementById('profile-address').value = result.items.address;
				document.getElementById('usertitle').innerHTML = result.items.name;  
				$('#profile-birthday').datepicker({   
				    format: 'yyyy-mm-dd' 
				});   
            } 
            else if(result.status == 200 && action == 'form')
            {
                inputSTEP = 3; 
                document.getElementById('full_name').value = result.items.name;
                document.getElementById('mobile_number').value = result.items.phone; 
                document.getElementById('company_address').value = result.items.address;
                document.getElementById('email').value = result.items.email; 
            }    
            else 
            { 
                setError('#login-error', txt_error);
            }  
            $(btn_loader).buttonLoader('stop');
		}).catch(function (error) { 
			setError('#login-error',error);
			$(btn_loader).buttonLoader('stop');
		});
		
    }  
} 

function userRegisterFB(rs)
{
	if(rs.email!=='')
	{
        var apiLink = apiURL+'/'+lang+"/regis/?username=RockStar&password=Um9ja1N0YXI=";
        var formData = new FormData();
		formData.append('email', rs.email);
		formData.append('upassword', '');
		formData.append('name', rs.name);
		formData.append('facebook_id', rs.id);  
    	axios.post(apiLink, formData).then(function (response) {
            var result = response.data;
            if(result.status == 200)
            {
			 	userLoginFB(rs); 
            }
            else     
            {
               	if(action=='login')setError('#login-error', txt_error);  
                if(action=='register')setError('#register-display', txt_error); 
            }
            $(btn_loader).buttonLoader('stop'); 
        }).catch(function (error) {     
			if(action=='login')setError('#login-error', error);  
            if(action=='register')setError('#register-display', error);  
			$(btn_loader).buttonLoader('stop');
		});
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

function setsSuccess(thiss,str)
{   
	setTimeout(function () {   
        $(btn_loader).buttonLoader('stop');
    }, 100);   
    $(thiss).html(''); 
    var html = '<div class="alert alert-success alert-dismissable fade in">';  
    html += '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
    html += str+'</div>'; 
    $(thiss).html(html); 
    $(thiss).slideDown();      
    setTimeout(function(){ 
        $(thiss).slideUp('slow');    
    },3000);  
}

function newPassword()
{
	var error = 0;
	$('#result').html(''); 
	var apiLink = apiURL+'/'+lang+"/profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=changePassword";  
	var oldpass = document.getElementById('profile-oldpassword').value;  
	var newpass = document.getElementById('profile-newpass').value;  
	var confpass = document.getElementById('profile-confnewpass').value;   
	
	if(oldpass.length < 5)
	{      
		setError('#result',txt_lang.alert_password_long);   
		document.getElementById('profile-oldpassword').focus();	
		error=1;
	}else if(newpass.length < 5)
	{         
		setError('#result',txt_lang.alert_new_password_long);       
		document.getElementById('profile-newpass').focus();	
		error=1; 
	}else if(newpass != confpass)
	{        
		setError('#result',txt_lang.alert_confirm_password);   
		document.getElementById('profile-confnewpass').focus();	
		error=1;
	}else{        
		apiLink +='&udpassword='+oldpass+"&upassword="+newpass;
	    axios.get(apiLink).then(function (response) {   
    		var result = response.data;  
    		if(result.status == 200) 
            {      
			 	document.getElementById('profile-oldpassword').value='';  
				document.getElementById('profile-newpass').value='';   
				document.getElementById('profile-confnewpass').value=''; 
			 	setsSuccess('#result',txt_lang.alert_password_successfuly);    
            }  
            else 
            {  
                setError('#result', txt_error); 
            }  
            $(btn_loader).buttonLoader('stop');
		}).catch(function (error) {    
			setError('#result',error);  
			$(btn_loader).buttonLoader('stop'); 
		}); 
		
	} 
	
	if(error){
		 setTimeout(function () {  
	        $(btn_loader).buttonLoader('stop');
	    }, 100); 
	}
	return false; 
}

function confirmContract()
{
	var cid = $('#contract').val();     
	var apiLink = apiURL+'/'+lang+"/contract/"+cid+"/?username=RockStar&password=Um9ja1N0YXI=&act=accept";  
	if(cid){   
		apiLink += '&supplier_id='+uid;    
	    axios.get(apiLink).then(function (response) {    
    		var result = response.data;      
    		console.log(result); 
    		if(result.status==200)    
            {        
			 	setsSuccess('#confirm-result',txt_lang.alert_confirm_contract_successfuly);    
			 	$('#txt-status').html($('#txt-status').attr('data-status'));  
			 	$('#confirm-box').remove();       
			 	setTimeout(function(){     
			 		window.location = baseURL+lang+'/supplier/';   
			 	},3200);  
            }   
            else   
            { 
                setError('#confirm-result', result.items);   
            } 
            $(btn_loader).buttonLoader('stop');
		}).catch(function (error) { 
			setError('#confirm-result',error);    
			$(btn_loader).buttonLoader('stop'); 
		}); 
	} 
}