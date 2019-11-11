function connectUserFB()   
{   
    $('#result-social').html('');     
	var apiLink = apiURL+'/'+lang+"/profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=edit";  
	var name = document.getElementById('profile-name').value;  
	var email = document.getElementById('profile-email').value;
	var birthday = document.getElementById('profile-birthday').value;
	var gender = document.getElementById('profile-gender').value;
	var phone = document.getElementById('profile-phone').value;    
	var address = document.getElementById('profile-address').value;
	$("#form-profile").valid();  
	if($("#form-profile").valid()){     
	    birthday = $.date(birthday);    
		var formData = new FormData();   
		formData.append('name', name); 
		formData.append('email',email);
		formData.append('phone',phone);
		formData.append('gender',gender);
		formData.append('birth_day',birthday); 
		formData.append('phone',phone); 
		formData.append('address',address); 
		formData.append('facebook_id', facebook_id);  
        
	    axios.post(apiLink, formData).then(function (response) {  
    		var result = response.data;   
    		if(result.status == 200)     
            {     
			 	setsSuccess('#result-social', txt_lang.alert_connected_facebook_successfuly);
			 	setCookie("uname",result.items.name);
			 	$('#btn-connect-fb').addClass('disabled');
			 	setTimeout(function(){ 
			 		$('#btn-connect-fb').html('<i class="fa fa-facebook" aria-hidden="true"></i> '+txt_lang.connected_facebook); 
			 	},3200);   
            }  
            else  
            { 
                setError('#result-social', txt_error); 
            }  
            $(btn_loader).buttonLoader('stop'); 
		}).catch(function (error) {    
			setError('#result-social', error);  
			$(btn_loader).buttonLoader('stop'); 
		}); 
	} 
	return false; 
}

function editProfile() 
{   
    $('#result').html('');    
	var apiLink = apiURL+'/'+lang+"/profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=edit";  
	var name = document.getElementById('profile-name').value; 
	var email = document.getElementById('profile-email').value;
	var birthday = document.getElementById('profile-birthday').value;
	var gender = document.getElementById('profile-gender').value;
	var phone = document.getElementById('profile-phone').value;    
	var address = document.getElementById('profile-address').value;
	$("#form-profile").valid();  
	if($("#form-profile").valid()){     
	    birthday = $.date(birthday);   
		var formData = new FormData();   
		formData.append('name', name); 
		formData.append('email',email);
		formData.append('phone',phone);
		formData.append('gender',gender);
		formData.append('birth_day',birthday); 
		formData.append('phone',phone); 
		formData.append('address',address); 
		formData.append('facebook_id', facebook_id);  
        
	    axios.post(apiLink, formData).then(function (response) {
    		var result = response.data;  
    		if(result.status == 200) 
            {    
			 	setsSuccess('#result',txt_lang.alert_profile_successfuly);
			 	setCookie("uname",result.items.name);
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
	return false;
}

function imgProfile(img)
{   
	$('#imgP-result').html(''); 
	var apiLink = apiURL+'/'+lang+"/profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=imgPF";
	if(img){        
		var formData = new FormData();   
		formData.append('img', img); 
	    axios.post(apiLink, formData).then(function (response) {  
    		var result = response.data;  
    		if(result.status == 200)    
            {    
			 	setsSuccess('#imgP-result',txt_lang.alert_upload_profile_successfully);
			 	setTimeout(function(){    
			 	    window.location.reload();   
			 	},3100);
            }  
            else 
            { 
                setError('#imgP-result', txt_error); 
            }   
            $('#btn-NewUpload').show();    
			$('#btn-UploadImg').hide(); 
			$('#btn-CancelImg').hide(); 
			$('#btn-UploadImg,#btn-CancelImg').attr('disabled',false);
            $(btn_loader).buttonLoader('stop'); 
		}).catch(function (error) { 
			$('#btn-NewUpload').show();   
			$('#btn-UploadImg').hide();  
			$('#btn-CancelImg').hide();  
			setError('#imgP-result',error);  
			$('#btn-UploadImg,#btn-CancelImg').attr('disabled',false);
			$(btn_loader).buttonLoader('stop'); 
		}); 
	}else{    
		setError('#imgP-result','Note base64 file.');
		return false;
	}
}

function contactUs()
{
	var name = $('#contactus-name').val();
	var email = $('#contactus-email').val();
	var subject = $('#contactus-subject').val();
	var message = $('#contactus-message').val();
	$('#contact-result').html('');  
	var apiLink = apiURL+'/'+lang+"/mail/?username=RockStar&password=Um9ja1N0YXI="; 
	if(!name){
		setError('#contact-result', txt_lang.alert_full_name);
		$('#contactus-name').focus();
	}else if(!validateEmail(email)){
		setError('#contact-result', txt_lang.alert_email_format); 
		$('#contactus-email').focus();
	}else if(!subject){
		setError('#contact-result', txt_lang.alert_plese_subject);
		$('#contactus-subject').focus();
	}else if(!message){   
		setError('#contact-result', txt_lang.alert_plese_message); 
		$('#contactus-message').focus();
	}else{    
		var formData = new FormData();   
		formData.append('name', name);
		formData.append('email', email); 
		formData.append('subject', subject);
		formData.append('msg', message); 
	    axios.post(apiLink, formData).then(function (response) {  
    		var result = response.data;   
    		if(result.status == 200)    
            {       
			 	setsSuccess('#contact-result', txt_lang.alert_send_msg_successfuly); 
			 	$('#contactus-name').val(''); 
				$('#contactus-email').val('');
				$('#contactus-subject').val('');
				$('#contactus-message').val('');
            }  
            else    
            { 
                setError('#contact-result', txt_error);   
            }
		}).catch(function (error) { 
			setError('#contact-result', error); 
		}); 
	}
}

function editCompanyInfo() 
{   
    $('#result-company').html('');
	var apiLink = apiURL+'/'+lang+"/profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=companyUpdate";  
	var company_name = document.getElementById('company_name').value; 
	var company_address = document.getElementById('company_address').value;
	var company_mobile_phone = document.getElementById('company_mobile_number').value;
	var company_phone = document.getElementById('company_landline_number').value; 
	var company_email = document.getElementById('company_email_supplier').value; 
	 
	$("#form-company").valid();   
	if($("#form-company").valid()){  
		var formData = new FormData();   
		formData.append('company_name', company_name); 
		formData.append('company_address',company_address);
		formData.append('company_mobile_phone',company_mobile_phone);
		formData.append('company_phone',company_phone);
		formData.append('company_email',company_email);   
	    axios.post(apiLink, formData).then(function (response) {  
    		var result = response.data;  
    		if(result.status == 200)  
            {     
			 	setsSuccess('#result-company','Successfuly');
			 	if(action=='contract'){ 
				 	setTimeout(function(){     
				 	    window.location.reload();   
				 	},3200);  
			 	} 
            }  
            else 
            { 
                setError('#result-company', txt_error); 
            }  
            $(btn_loader).buttonLoader('stop');
		}).catch(function (error) {  
			setError('#result-company',error);  
			$(btn_loader).buttonLoader('stop'); 
		}); 
	}  
	return false;  
} 
 
function switchLang(lang)
{
	setCookie('ck_lang',lang); 
	var url=window.location.href;    
	if(lang=='fr'){ 
		url = url.replace("/en/", "/fr/"); 
	}else{     
		url = url.replace("/fr/", "/en/"); 
	}
	window.location = url; 
}