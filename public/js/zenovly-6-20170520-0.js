$("#form-supplier").validate({   
    rules: {
        attachfile: {   
           required: true,  
           extension: "pdf|jpg|jpeg|png|gif"
        },
        price:"required", 
        start_date:{
            required:true
        }, 
         end_date:{ 
            required:true,
            greaterThan: "#start_date"              
        },
        sireal_number:{
            required: true, 
            sirealRegex: true,
        },
        email: { 
          required: true,
          email: true  
        },
        full_name:"required", 
        project_name:"required", 
        company_name:"required",
        mobile_number:{
            required: true, 
            phoneNumber: true, 
        },
    },
    messages: {
        full_name:txt_lang.alert_full_name,
        attachfile:txt_lang.alert_image_type,
        price:txt_lang.alert_price,  
        company_name:txt_lang.alert_company, 
        start_date:txt_lang.alert_start_date, 
        end_date:  
        { 
            required:txt_lang.alert_end_date, 
            greaterThan:txt_lang.alert_date_match
        },
        sireal_number: { 
            required: txt_lang.alert_enter_sireal,   
            sirealRegex: txt_lang.alert_sireal_format
        },
        email: {   
          required: txt_lang.alert_email,
          email: txt_lang.alert_email_format 
        },
    }, 
    onfocusout: false,
    invalidHandler: function(form, validator) {
        var errors = validator.numberOfInvalids();
        if (errors) {                    
            validator.errorList[0].element.focus();
            setTimeout(function () {  
                $(btn_loader).buttonLoader('stop');
            }, 100);     
        }
    } 
});  
 
$('#attachfile').change(function(){ 
    $(this).valid();   
});

$('#mobile_number').keyup(function(){ 
    $(this).valid();    
}); 

$("#form-profile").validate({  
    rules: {
        'profile-email': { 
          required: true,
          email: true  
        }, 
        'profile-name':"required",  
        'profile-phone':{
            required: true, 
            phoneNumber: true, 
        },    
    },
    messages: {  
        'profile-name':txt_lang.alert_full_name,   
        'profile-email': {   
          required: txt_lang.alert_email,
          email: txt_lang.alert_email_format  
        },
    }, 
    onfocusout: false, 
    invalidHandler: function(form, validator) {
        var errors = validator.numberOfInvalids();
        if (errors) {                    
            validator.errorList[0].element.focus();
            setTimeout(function () {  
                $(btn_loader).buttonLoader('stop');
            }, 100);    
        }   
    }  
}); 

$("#form-company").validate({  
    rules: { 
        company_name:"required",
        company_address:'required',
        company_mobile_number:{ 
            required: true,
            phoneNumber: true,
        },
        company_landline_number:{ 
            required: true,
            phoneLandline: true,
        },
        company_email_supplier: { 
          required: true,
          email: true    
        } 
    },
    messages: { 
        company_name:txt_lang.alert_company,
        company_email_supplier: {     
          required: txt_lang.alert_email,
          email: txt_lang.alert_email_format 
        },
    }, 
    onfocusout: false,  
    invalidHandler: function(form, validator) {
        var errors = validator.numberOfInvalids();
        if (errors) {                    
            validator.errorList[0].element.focus();
            setTimeout(function () {  
                $(btn_loader).buttonLoader('stop');
            }, 100);    
        }   
    }  
}); 
 
var imgW = 0;
var imgH = 0;
var imgOri = '';
var btn_loader='';  
$(document).ready(function ()
{   
    $('.btn-loader').click(function ()
    { 
        btn_loader = $(this);     
        $(btn_loader).buttonLoader('start');
    });

    if(action=='profile' || action=='newpassword' || action=='dashboard'){
        $('#upload').on('change', function () {
    		if(imgW==0 && imgH==0){
    			imgW = document.getElementById('img-profile').offsetWidth;  
    			imgH = document.getElementById('img-profile').offsetHeight;
    			imgOri = document.getElementById('img-profile').src; 
    		}   
    		document.getElementById('profile-img-preview').innerHTML='';
    		$('#btn-NewUpload').hide();
    		$('#btn-UploadImg').show(); 
    		$('#btn-CancelImg').show();  
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
    	    	$uploadCrop.croppie('bind', { 
    	    		url: e.target.result,  
    	    	}).then(function(){ 
    	    		console.log('jQuery bind complete');
    	    	}); 
    	    }    
    	    reader.readAsDataURL(this.files[0]);
    	}); 
    }
});

function uploadIMG(){      
	$('#upload').val('');  
	$('#btn-UploadImg,#btn-CancelImg').attr('disabled',true); 
	$uploadCrop.croppie('result', {   
		type: 'canvas', 
		size: 'viewport'
	}).then(function (resp) {  
		document.getElementById('profile-img-preview').innerHTML=''; 
		var img = '<img src="'+resp+'" id="img-profile" alt="" class="img-responsive">'; 
		document.getElementById('profile-img-preview').innerHTML=img;
		imgProfile(resp);  
	});  
} 
 
function uploadCal(){ 
    $('#upload').val('');
	var img = '<img src="'+imgOri+'" id="img-profile" alt="" class="img-responsive">';
	document.getElementById('profile-img-preview').innerHTML=img; 
	$('#btn-NewUpload').show();  
	$('#btn-UploadImg').hide(); 
	$('#btn-CancelImg').hide();
	$('#btn-UploadImg,#btn-CancelImg').attr('disabled',false);
}

$("#supplierform").validate({   
    rules: {
        attachfile: {   
           required: true,  
           extension: "pdf|jpg|jpeg|png|gif"
        },
        price:"required", 
        start_date:{
            required:true
        }, 
         end_date:{ 
            required:true,
            greaterThan: "#start_date"              
        },
        sireal_number:{
            required: true, 
            sirealRegex: true,
        },
        email: { 
          required: true,
          email: true  
        },
        full_name:"required", 
        company_name:"required",
        mobile_number:{
            required: true, 
            phoneNumber: true, 
        }, 
         
    },
    messages: {
        full_name:txt_lang.alert_full_name,
        attachfile:txt_lang.alert_image_type,
        price:txt_lang.alert_price,  
        company_name:txt_lang.alert_company, 
        start_date:txt_lang.alert_start_date, 
        end_date:  
        { 
            required:txt_lang.alert_end_date, 
            greaterThan:txt_lang.alert_date_match
        },
        sireal_number: { 
            required: txt_lang.alert_enter_sireal,   
            sirealRegex: txt_lang.alert_sireal_format
        },
        email: {   
          required: txt_lang.alert_email,
          email: txt_lang.alert_email_format 
        },
    }, 
    onfocusout: false,
    invalidHandler: function(form, validator) {
        var errors = validator.numberOfInvalids();
        if (errors) {                      
            validator.errorList[0].element.focus();
            setTimeout(function () {   
                $(btn_loader).buttonLoader('stop');
            }, 100);     
        }
    } 
});  

function supplierConfirm()
{
    $("#supplierform").valid();   
	if($("#supplierform").valid()){
	    
	}
}