var today = new Date();
var dd = today.getDate();
var mm = today.getMonth()+1;
var yyyy = today.getFullYear();
if(mm<10)mm='0'+mm; 
   
function getUserProfile(id)
{
	app.loader=true;
	var apiLink = apiURL+'/'+lang+"/profile/"+id+"/?username=RockStar&password=Um9ja1N0YXI=&rd="+makeid(); 
	var data = [];  
	if(id){ 
		axios.get(apiLink).then(function (response) {
			var result = response.data;
			if(result.status == 200)
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
            else 
            { 
                alert('failed.'); 
            }  
            app.loader=false;
		}).catch(function (error) {
			console.log(error);
			app.loader=false;
		});
		/*
		new Vue({  
	        created: function ()    
	        {
	            this.fetchData()
	        },
	        methods:  
	        { 
	            fetchData: function () 
	            {   
	                var xhr = new XMLHttpRequest() 
	                var self = this   
	                xhr.open('GET', apiLink) 
	                xhr.onload = function () 
	                {  
	                    var result = JSON.parse(xhr.responseText);
	                    console.log(result.items);      
	                    if(result.status == 200)
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
							//document.getElementById('profile-name').value = result.items.name;    
	                    }
	                    else 
	                    {
	                        alert('failed.'); 
	                        app.loader=false;
	                    } 
	                    app.loader=false;
	                } 
	                xhr.send();  
	            }
	        }
	    });  
	    */
	} 
}  


var imgW = 0;
var imgH = 0;
var imgOri = '';
function profile() 
{  
	menuActive(); 
	app.loader=false;  
	app.detail=false;
	app.topMenu=false;  
	app.hotPage=false;
	app.login=false;  
	app.register=false; 
	app.profile=true; 
	app.topHome='#b8000c';
	app.topNew='#626262';
	app.topMbar='#626262'; 
	app.footHome='#fff'; 
	app.footNew='#162a36';
	app.footMbar='#162a36'; 
	 
	app.profileContent=false;   
	app.dashboardContent=false; 
	app.newpassContent=false;  
	
	app.btnUploadImg=false;
	app.btnCancelImg=false;
	app.btnNewUpload=true;  
	
	
	if(action=='dashboard'){
		action = 'dashboard';
		app.dashboardContent=true;
	}else if(action=='newpassword'){
		action = 'newpassword';
		app.newpassContent=true;
	}else{ 
		profileContent('profile'); 
		app.profileContent=true;  
		window.history.pushState(txt_lang.Profile, "Title", baseURL+lang+"/profile/");
		setSeo(txt_lang.Profile, txt_lang.Profile, txt_lang.Profile); 
	}
	  
	
	$('#upload').on('change', function () {
		if(imgW==0 && imgH==0){
			imgW = document.getElementById('img-profile').offsetWidth;  
			imgH = document.getElementById('img-profile').offsetHeight;
			imgOri = document.getElementById('img-profile').src; 
		}   
		document.getElementById('profile-img-preview').innerHTML='';
		app.btnNewUpload=false;
		app.btnUploadImg=true;
		app.btnCancelImg=true; 
		$uploadCrop = $('#profile-img-preview').croppie({
		    enableExif: true, 
		    viewport: { 
		        width: 200,  
		        height:200 
		    },  
		    boundary: {   
		        width: imgW,
		        height: imgH
		    }, 
		    showZoomer: false,
	    	enableOrientation: true   
		}); 
		var reader = new FileReader();
	    reader.onload = function (e) { 
	    	console.log(e.target.result);
	    	$uploadCrop.croppie('bind', {
	    		url: e.target.result,  
	    		//points: [77,469,280,739]
	    	}).then(function(){
	    		console.log('jQuery bind complete');
	    	}); 
	    }    
	    reader.readAsDataURL(this.files[0]);
	});
}

function uploadIMG(){  
	// 
	$uploadCrop.croppie('result', {  
		type: 'canvas',
		size: 'viewport'
	}).then(function (resp) {  
		document.getElementById('profile-img-preview').innerHTML=''; 
		var img = '<img src="'+resp+'" id="img-profile" alt="" class="img-responsive">'; 
		document.getElementById('profile-img-preview').innerHTML=img;    
		console.log(resp);     
	}); 
	app.btnUploadImg=false;
	app.btnCancelImg=false;
	app.btnNewUpload=true;  
} 

function uploadCal(){ 
	var img = '<img src="'+imgOri+'" id="img-profile" alt="" class="img-responsive">';
	document.getElementById('profile-img-preview').innerHTML=img;  
	app.btnUploadImg=false;
	app.btnCancelImg=false;
	app.btnNewUpload=true;  
}  

function editProfile()
{   
	app.loader=true; 
	var apiLink = apiURL+'/'+lang+"/profile/"+id+"/?username=RockStar&password=Um9ja1N0YXI=&act=edit";  
	var name = document.getElementById('profile-name').value; 
	var email = document.getElementById('profile-email').value;
	var birthday = document.getElementById('profile-birthday').value;
	var gender = document.getElementById('profile-gender').value;
	var phone = document.getElementById('profile-phone').value;    
	var address = document.getElementById('profile-address').value; 
	if(!name){ 
		alert('Please enter name.');
		document.getElementById('profile-name').focus();	
	}else if(!validateEmail(email)){     
		alert('Please enter a valid email address.');   
		document.getElementById('profile-email').focus();	
	}else if(!birthday){    
		alert('Please enter birthday.');  
		document.getElementById('profile-birthday').focus();	
	}else if(!birthday){    
		alert('Please enter phone.');   
		document.getElementById('profile-phone').focus();	
	}else{   
		//email, name, phone, facebook_id, gender, birth_day, address
		var formData = new FormData();
		formData.append('name', name); 
		formData.append('email',email);
		formData.append('phone',phone);
		formData.append('gender',gender);
		formData.append('birth_day',birthday); 
		formData.append('phone',phone); 
		formData.append('address',address); 
		formData.append('facebook_id', facebook_id);  
        var xhr = new XMLHttpRequest()
        var self = this  
        xhr.open('POST', apiLink, true) 
        xhr.onload = function () 
        {  
            var result = JSON.parse(xhr.responseText);
            app.loader=false;       
            if(result.status == 200)
            {   
			 	alert('edit profile successfuly');  
			 	profile(); 
            } 
            else    
            {
                alert(result.items); 
            }
            app.loader=false;
        }
        xhr.send(formData); 		
	}
}

function newPassword(){    
	
	var apiLink = apiURL+'/'+lang+"/profile/"+id+"/?username=RockStar&password=Um9ja1N0YXI=&act=changePassword";  
	var oldpass = document.getElementById('profile-oldpassword').value;  
	var newpass = document.getElementById('profile-newpass').value;  
	var confpass = document.getElementById('profile-confnewpass').value;   
	
	if(oldpass.length < 5){      
		alert('Your password must be at least 5 characters long.');   
		document.getElementById('profile-oldpassword').focus();	
	}else if(newpass.length < 5){         
		alert('Your new password must be at least 5 characters long.');     
		document.getElementById('profile-newpass').focus();	
	}else if(newpass != confpass){        
		alert('Your new password does not match the confirm password.');   
		document.getElementById('profile-confnewpass').focus();	
	}else{      
		apiLink +='&udpassword='+oldpass+"&upassword="+newpass;   
        var xhr = new XMLHttpRequest() 
        var self = this    
        xhr.open('GET', apiLink)  
        xhr.onload = function () 
        {  
            var result = JSON.parse(xhr.responseText);
            app.loader=false;       
            if(result.status == 200) 
            {     
			 	document.getElementById('profile-oldpassword').value='';  
				document.getElementById('profile-newpass').value='';   
				document.getElementById('profile-confnewpass').value=''; 
			 	alert('new password successfuly'); 
            } 
            else   
            {
                alert(result.items);  
            }
            app.loader=false;
        }
        xhr.send();  		
	}
	
	setSeo(txt_lang.New_Password, txt_lang.New_Password, txt_lang.New_Password); 
}
 
