//var user_api = 'username=RockStar';
//var password_api = 'password=Um9ja1N0YXI=';       
//var apiUrl = 'https://safe-tonytoons.c9users.io/public/api/'+lang+'/';    
var btn_loader = ''; 
var uid = 0; 
var txt_btn = '';  
var base_url = baseURL+''+lang+'/'; 
var base64_file = '';
var base64_file1 = '';
var base64_file2 = '';
var base64_file3 = '';
var base64_file4 = '';
var base64_file5 = '';
var base64_file6 = '';
var base64_file7 = '';
var base64_file8 = '';
var base64_file9 = ''; 

var facebook_id=0; 
var imgW = 0;
var imgH = 0;
var imgOri = '';
var base64_array = []; 
 
if(getCookie('uid')!=''){
    uid = getCookie('uid'); 
} 
jQuery(document).ready(function($) {   
    $(window).load(function(){
    	$('#preloader').fadeOut('slow',function(){$(this).remove();});
    });
});

$(function()
{
    
    
    init(); 
    
    if(action=='seller' || action=='buyer'){
        getByStatus(0); 
    } 
    
    $('.datepicker').datepicker({
        format: 'mm/dd/yyyy'
    });
    
    $('#profile-birthday').datepicker({
        maxDate: 0,
        changeMonth: true,
        changeYear: true,
        format: 'mm/dd/yyyy'
    });
    
    /***************** button Loader *****************/ 
    $.fn.buttonLoader = function (action)
    {
        var self = $(this);  
        $(self).attr("disabled", false); 
        
        if (action == 'start') {
            if ($(self).attr("disabled") == "disabled") {
                return false; 
            }  
            $('.btn-loader').attr("disabled", true);
            txt_btn = $(self).html(); 
            //console.log(txt_btn); 
            var text = 'Loading...';         
            if($(self).attr('data-load-text') != undefined && $(self).attr('data-load-text') != ""){
                var text = $(self).attr('data-load-text');
            }
            $(self).html('<span class="spinner"><i class="fa fa-spinner fa-spin" title="button-loader"></i></span> '+text);
            $(self).addClass('active'); 
        }
        
        if (action == 'stop') {   
            $(self).html(txt_btn);    
            $(self).removeClass('active'); 
            $('.btn-loader').attr("disabled", false);
        }
    }  
    
    $('#start_date').datepicker({ 
            changeMonth: true,  
            changeYear: true,
            onSelect: function(dateStr) {     
                  date = $(this).datepicker('getDate');
                  if (date) {
                        date.setDate(date.getDate() + 1);
                  }     
                  $('#end_date').datepicker('option', 'minDate', date);
            } 
    }); 
    $('#end_date').datepicker({ 
        minDate: 0,  
        changeMonth: true,
        changeYear: true,
        onSelect: function (selectedDate) { 
              date = $('#start_date').datepicker('getDate');  
              if (date) {     
                    date.setDate(date.getDate());      
              }  
              $('#start_date').datepicker('option', 'minDate', date || 0);  
        }
    });
    
    $('.btn-loader').click(function(){ 
        btn_loader = this; 
        $(this).buttonLoader('start'); 
    });
    
    $('.radio-box').click(function(){ 
        $('.radio-box').removeClass('active');
        $(this).addClass('active'); 
    });
    
    /*********** login and register form ********/
    $('#login-form-link').click(function(e) {
		$("#login-form").delay(100).fadeIn(100);
 		$("#register-form").fadeOut(100);
		$('#register-form-link').removeClass('active');
		$(this).addClass('active');
		e.preventDefault();
	});
	$('#register-form-link').click(function(e) {
		$("#register-form").delay(100).fadeIn(100);
 		$("#login-form").fadeOut(100);
		$('#login-form-link').removeClass('active');
		$(this).addClass('active');
		e.preventDefault();
	});
	 
	if(action=='projectform'){
    	/********* step project form ********/
        $('#btn-next-step1').click(function(){  
            
            $('#project_form').valid();   
              
        	$.each( base64_array, function( key, value ) { 
        	    if(key==0)base64_file1 = value;
        	    if(key==1)base64_file2 = value;
        	    if(key==2)base64_file3 = value;
        	    if(key==3)base64_file4 = value;
        	    if(key==4)base64_file5 = value;
        	    if(key==5)base64_file6 = value;
        	    if(key==6)base64_file7 = value;
        	    if(key==7)base64_file8 = value;
        	    if(key==8)base64_file9 = value;
            });    
            
        	if($('#project_form').valid()){
        	   var company = $("#company").val();
        	   if(company=='yes'){   
        	       // company info
        	       var company_name = $('#company_name').val();
        	       var company_country = $('#company_country').val();
        	       var company_id = $('#company_id').val(); 
        	       var company_address = $('#company_address').val();
        	       if(!company_name){
        	           setError('#result-company', 'Please enter company name.'); 
        	           $('#company_name').focus();
        	       }else if(!company_country){  
        	           setError('#result-company', 'Please enter country of incorporation.'); 
        	           $('#company_country').focus();
        	       }else if(!company_id){ 
        	           setError('#result-company', 'Please enter ID number of the company.'); 
        	           $('#company_id').focus();
        	       }else if(!company_name){  
        	           setError('#company_address-company', 'Please enter Company Address.'); 
        	           $('#company_address').focus(); 
        	       }else{ 
            	       var apiLink = apiUrl+"profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=companyUpdate";
            	       var data ={     
                	               'company_name':company_name,
                	               'company_country':company_country, 
                	               'company_id':company_id, 
                	               'company_address':company_address
                	             }; 
                	   
            	       $.post(apiLink, data, function(result){
            	           if(result.status==200){ 
            	               $('#tab-step2').addClass('process-active active');
                               $('.tab-pane').removeClass('active'); 
                               $('#tab2').addClass('active');   
                               $(window).scrollTop(0);
            	           }else{ 
            	               setError('#result-company', result.items);  
            	           }
            	           $(btn_loader).buttonLoader('stop');
            	       },'json');
        	       }
        	   }else{   
        	       $(btn_loader).buttonLoader('stop');
        	       $('#tab-step2').addClass('process-active active');
                   $('.tab-pane').removeClass('active'); 
                   $('#tab2').addClass('active');   
                   $(window).scrollTop(0);
        	   }
        	}else{
        	    $(btn_loader).buttonLoader('stop'); 
        	} 
        }); 
         
        $('#btn-next-step2').click(function(){
            
            gotoStep3();  
        }); 
        
        $('#btn-next-step3').click(function(){
            // if ($('.txt-policy').scrollTop() == $('.txt-policy')[0].scrollHeight - $('.txt-policy').height()) {
            //     gotoStep4(); 
            // }else{   
            //     setError('#error-policy', 'Please read the privacy policy.');    
            // }
            
            gotoStep4(); 
            /* 
            if ($('.txt-policy')[0].scrollHeight >= $('.txt-policy').height()) {
                gotoStep4();     
            }else{
                
                $('#error-policy').();
                //$('#btn-next-step3').addClass('disabled');   
            }
            */
           
        });  
	}else if(action=='contract'){
	    
	    /********* step project form ********/
        $('#btn-next-step1').click(function(){  
            if($('.check_policy').is(':checked')){  
                $('#error-policy').hide();
                
                projectAccept();  
            }else{  
                $(btn_loader).buttonLoader('stop');
                setError('#error-policy',txt_lang.confirm_policy);  
            }     
        });    
         
        $('#btn-next-step2').click(function(){  
            alert('Send to Api payment.');
            $('#tab-step3').addClass('process-active active');
            $('.tab-pane').removeClass('active');  
            $('#tab3').addClass('active');   
            $(window).scrollTop(0); 
        }); 
        
	}   
    
    /**********************  prev  ************************/
    
    $('#btn-prev-step2').click(function(){ 
       $('#tab-step2').removeClass('process-active active'); 
       $('.tab-pane').removeClass('active'); 
       $('#tab1').addClass('active'); 
       $(window).scrollTop(0); 
    });    
     
    $('#btn-prev-step3').click(function(){   
       $('#tab-step3').removeClass('process-active active'); 
       $('.tab-pane').removeClass('active');  
       $('#tab2').addClass('active'); 
       $(window).scrollTop(0);
    });  
    
    
    /********* validate form  register ********/
    
    $("#register-form").validate({
        rules: {
            'regis-name':'require',
            'regis-email':'require',
            'password': {  
                required: true, 
                minlength: 6  
            },
            'confirm-password': {     
                equalTo: "#input-password",
                minlength: 6
           }
        },
        messages:{
            password: { 
              required:"the password is required"
            }
        },
        onfocusout: false, 
        invalidHandler: function(form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {                    
                validator.errorList[0].element.focus();
            }  
        }   
    });
    
    $('#customer').change(function(){
        var name = $('#customer option:selected').text().trim();
        var id = $(this).val();    
        var email = $('#customer option:selected').attr('data-email');  
        var phone = $('#customer option:selected').attr('data-phone'); 
        //console.log({name,id,email});
        if(id!=0){ 
            $('#customer_id').val(id);
            $('#name').val(name);  
            $('#email').val(email); 
            $('#phone_number').val(phone);
        }else{   
            $('#customer_id').val(0);
            $('#name').val(''); 
            $('#email').val('');
            $('#phone_number').val('');
        }
        $('#project-form2').valid(); 
    }); 
    
    $('#company').change(function(){
        var company = $(this).val();
        if(company=='yes'){    
            $('#company-block').show();  
            $('#company-block-detail').show();
        }else{  
            $('#company-block').hide();  
            $('#company-block-detail').hide();
        }
    });
      
    $("#attachfile_project").change(function() { 
        base64_file1,base64_file2,base64_file3,base64_file4,base64_file5,base64_file6,base64_file7,base64_file8,base64_file9 = ''; 
        var names = [];  
        if($(this).get(0).files.length<=9){ 
            for (var i = 0; i < $(this).get(0).files.length; ++i) {
                var reader = new FileReader(); 
                reader.readAsDataURL($(this).get(0).files[i]);
                base64_array.push(reader);
                //console.log(i);    
            }  
        }else{  
            $(this).val('');
            //rs-display
            setError('#rs-display', 'Upload maximum 9 file.'); 
        }
        //$("input[name=file]").val(names);
    });
    
    $("#attachfile").change(function(){
        base64_file = getBase64(this);  
    }); 
    /*
    $("#attachfile2").change(function(){
        if (this.files && this.files[0]) {   
            var reader = new FileReader();     
            reader.onload = function (e) {    
                base64_file2 = e.target.result;
            }    
            reader.readAsDataURL(this.files[0]);
        }
    });
     
    $("#attachfile3").change(function(){
        if (this.files && this.files[0]) {   
            var reader = new FileReader();     
            reader.onload = function (e) {    
                base64_file3 = e.target.result;
            }    
            reader.readAsDataURL(this.files[0]);
        }  
    });
    
    $("#attachfile4").change(function(){
        if (this.files && this.files[0]) {   
            var reader = new FileReader();     
            reader.onload = function (e) {    
                base64_file4 = e.target.result;
            }    
            reader.readAsDataURL(this.files[0]);
        }  
    });
    
    $("#attachfile5").change(function(){
        if (this.files && this.files[0]) {   
            var reader = new FileReader();     
            reader.onload = function (e) {    
                base64_file5 = e.target.result;
            }    
            reader.readAsDataURL(this.files[0]);
        } 
    }); 
    
    $("#attachfile6").change(function(){
        if (this.files && this.files[0]) {   
            var reader = new FileReader();     
            reader.onload = function (e) {    
                base64_file6 = e.target.result;
            }    
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    $("#attachfile7").change(function(){
        if (this.files && this.files[0]) {   
            var reader = new FileReader();     
            reader.onload = function (e) {    
                base64_file7 = e.target.result;
            }    
            reader.readAsDataURL(this.files[0]);
        }  
    }); 
    
    $("#attachfile8").change(function(){
        if (this.files && this.files[0]) {   
            var reader = new FileReader();     
            reader.onload = function (e) {    
                base64_file8 = e.target.result;
            }    
            reader.readAsDataURL(this.files[0]);
        }  
    });  
     
    $("#attachfile9").change(function(){
        if (this.files && this.files[0]) {   
            var reader = new FileReader();     
            reader.onload = function (e) {    
                base64_file9 = e.target.result;
            }    
            reader.readAsDataURL(this.files[0]);
        } 
    });
    */  
    
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
         
        $("#form-bank").validate({     
            rules: { 
                'iban_account':"required",
                'userbnak_name':"required",
                "bic_code":"required", 
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
    } 
    
    
    if(action=='projectform'){   
        $('.txt-policy').scroll(function () {  
            if ($(this).scrollTop() == $(this)[0].scrollHeight - $(this).height()) {
                //$('#btn-next-step3').removeClass('disabled');    
            }else{ 
                //$('#btn-next-step3').addClass('disabled');   
            } 
        });
    }
    
});  

function get_Base64(file) {
   var reader = new FileReader(); 
   reader.readAsDataURL(file);
   reader.onload = function () {
     return reader.result; 
   };
   reader.onerror = function (error) {
     return '';
   };
}

$.validator.addMethod("greaterThan", 
function(value, element, params) {
    if (!/Invalid|NaN/.test(new Date(value))) {
        return new Date(value) > new Date($(params).val());
    }   
    return new Date(value) > new Date($(params).val());    
},'Must be greater than {0}.');    

$.validator.addMethod("sirealRegex", function(value, element) {
    return this.optional(element) || /^[a-z0-9\-]+$/i.test(value);
}, txt_lang.alert_sireal); 
 
$.validator.addMethod("phoneNumber", function (phone_number, element) {  
    return true;
}, txt_lang.alert_phone);
  
$.validator.addMethod("phoneLandline", function (phone_number, element)
{
    return true;
}, txt_lang.alert_phone);   

$.validator.addMethod("email", function (email, element)  
{
    return validateEmail(email); 
    
}, txt_lang.alert_valid_email);   


function phonenumber(inputtxt)
{
    return true;
} 

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

function getBase64(input) { 
    if (input.files && input.files[0]) {  
        var reader = new FileReader();   
        reader.onload = function (e) {  
            base64file = e.target.result; 
            //console.log(base64file);
            base64_file = base64file;
            return base64file;  
        } 
        reader.readAsDataURL(input.files[0]);
    }
} 

$.date = function(dateObject)
{
    var d = new Date(dateObject);
    var day = d.getDate();
    var month = d.getMonth() + 1;
    var year = d.getFullYear();
    if (day < 10) {
        day = "0" + day;
    }
    if (month < 10) {
        month = "0" + month;
    } 
    var date = year + "-" + month + "-" + day; 

    return date;
}; 
 
function validateEmail(email) 
{ 
    var re = /^(([^<>()\[\]\\.,;+:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
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
        window.location = base_url;   
    }  
    
    if(action=='login' && getCookie('loginEmail')!=''){  
    	$('#login-email').val(getCookie('loginEmail')); 
    }
     
    if(action=='consumer' || action=='supplier'){ 
    	getByStatus(0); 
    }
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
 

if(action=='account'){ 
    var password = document.getElementById("input-password"), 
        confirm_password = document.getElementById("input-confirm-password");
    function validatePassword(){  
      if(password.value != confirm_password.value) {  
        confirm_password.setCustomValidity("Passwords Don't Match");
      } else {
        confirm_password.setCustomValidity('');
      } 
    }
    password.onchange = validatePassword;  
    confirm_password.onkeyup = validatePassword;
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

function makeid()
{ 
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    return text; 
}

function number_format(number, decimals, dec_point, thousands_sep)
{ 
    var n = !isFinite(+number) ? 0 : +number, 
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        toFixedFix = function (n, prec) {
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            var k = Math.pow(10, prec);
            return Math.round(n * k) / k;
        }, 
        s = (prec ? toFixedFix(n, prec) : Math.round(n)).toString().split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
} 
 
function toDate(dateStr) {   
    var d = new Date(dateStr); 
    var curr_date = d.getDate();
    var curr_month = d.getMonth() + 1;
    var curr_year = d.getFullYear();  
    return curr_month+'/'+curr_date+'/'+curr_year
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
    if(getCookie("uid")){ 
      var html = '<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="img-profile" style="background: url('+getCookie("uimg")+') center no-repeat;background-size: contain;"></i> '+getCookie("uname")+' <span class="caret"></span></a>';    
          html += '<ul id="login-dp" class="dropdown-menu">';
          html += '<li>';  
          html += '<a href="'+base_url+'profile"><i class="img-profile-submenu" style="background: url('+getCookie("uimg")+') center no-repeat;background-size: contain;"></i> Profile</a>';   
          html += '</li>';
          html += '<li>';
          html += '<a href="javascript:logOut();"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a>';  
          html += '</li>';
          html += '</ul>';
      //$('#user-register').hide();    
      $('#menu-account').html(html).show();  
    } 
}

function logOut()
{
    $( "#logout-confirm" ).dialog({
        resizable: true,
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
	    	    window.location = base_url;   
            },
            Cancel: function() {
            $( this ).dialog( "close" ); 
            }
        }
    });
}
 
function goLogin(){
    var email = $('#login-email').val(), password = $('#login-password').val();  
    var url = apiUrl+'login/?'+user_api+'&'+password_api+'&email='+email+'&upassword='+password;
    if(!validateEmail(email)){  
	    setError('#login-rs', txt_lang.alert_valid_email);
        $(btn_loader).buttonLoader('stop');
	}else{  
        $.get(url, function(result){ 
            //console.log(result);
            if(result.status==200){   
                uid = result.items.id; 
                setCookie("uid",result.items.id);  
                setCookie("uname",result.items.name);  
                if(result.items.image!='' && result.items.image!=null){      
                    setCookie("uimg", result.items.image_url); 
                }else{      
                	setCookie("uimg",'https://files.renovly.com/setting/avatar.jpg'); 
                }   
                setCookie("utype", result.items.type);      
                 
                if(action=='contract' || action=='projectform'){     
                	setTimeout(function(){     
    			 	    window.location.reload();     
    			 	},1000);   
                }else if(getCookie('gotopage')!=''){    
                	window.location = getCookie('gotopage'); 
                }else{     
                	window.location = base_url;     
                }
            }else{  
                setError('#login-rs', result.items);   
            }
        },'json');
	}
    return false;
}

function userRegister() 
{
	var name = $('#regis-name').val();
	var email = $('#regis-email').val(); 
	var pass = $('#input-password').val();
	var confirmpass = $('input-confirmpass').val();  
	var url = apiUrl+'regis/?'+user_api+'&'+password_api;
	$('#register-form').valid(); 
	if($('#register-form').valid()){
	    
	    var data = {'email':email,'name':name,'upassword':pass,'facebook_id':0};
	    $.post(url, data, function(result){
	        if(result.status==200){      
	            $('#login-email').val(email);  
	            $('#login-password').val(pass); 
	            goLogin(); 
	        }else{    
	            //console.log(result.items);   
	            setError('#register-rs', result.items);
	            $(btn_loader).buttonLoader('stop');
	        } 
	    },'json');
	}else{ 
	    setTimeout(function (){   
            $(btn_loader).buttonLoader('stop');
        }, 100);
	}
} 
 
function gotoStep3(){ 
    
    $('#project-form2').valid();  
	if($('#project-form2').valid()){
	    var customer_id = $('#customer_id').val();
	    var name = $('#name').val(); 
	    var email = $('#email').val();  
	    
	    var project_name = $('#project_name').val(); 
	    var project_price = $('#project_price').val();
	    var start_date = $('#start_date').val(); 
	    var end_date = $('#end_date').val(); 
	    var detail_note = $('#project_detail').val(); 
	    var contract_no = $('#project_contract_no').val();
	    var phone_number = $('#phone_number').val(); 
	    var who_pay_fee = $("input[type='radio']:checked").val();
	    var txt_pay_fee = $(".btn.btn-default.radio-box.active").text().trim();
	    
	    if(customer_id==0){   
        	var url = apiUrl+'profile/?'+user_api+'&'+password_api+'&act=new';
            var data = {'email':email,'name':name};  
            $.post(url, data, function(result){
                if(result.status==200){  
                    $('#customer_id').val(result.items);
                    $('#tab-step3').addClass('process-active active');
                    $('.tab-pane').removeClass('active'); 
                    $('#tab3').addClass('active');   
                    $(window).scrollTop(0); 
                }  
                $(btn_loader).buttonLoader('stop'); 
            },'json');   
	    }else{     
	        $(btn_loader).buttonLoader('stop');
            $('#tab-step3').addClass('process-active active');
            $('.tab-pane').removeClass('active'); 
            $('#tab3').addClass('active');   
            $(window).scrollTop(0);
	    }
	    
	    $('#for-customer-name').html(name);   
		$('#detail-customer-name').html(name);
	    $('#detail-projectname').html(project_name);  
        $('#detail-sub-price').html(number_format(project_price, 2));    
        $('#detail-total-price').html(number_format(project_price, 2)); 
        $('#detail-start-date').html(start_date);  
        $('#detail-end-date').html(end_date);
          
        if(!contract_no) contract_no = '-';  
        //$('#detail-contact-no').html(contract_no); 
        $('#detail-fullname').html(name);
        $('#detail-email').html(email); 
        $('#detail-phone_number').html(phone_number);
        $('#service-free').html(txt_pay_fee);
        if(!detail_note) detail_note = '-';
        $('#your-note').html(detail_note); 
         
        $('#detail-company-name').html($('#company_name').val());
        $('#detail-company-country').html($('#company_country').val());
        $('#detail-company-id').html($('#company_id').val());
        $('#detail-company-address').html($('#company_address').val());
        
        var fee_price = 10;
        
        $.each(service_feee, function(key, items){
            
            project_price = parseFloat(project_price);
            var fee_key = parseFloat(key.replace('fee', '')); 
            var items = parseFloat(items); 
            
            if(fee_key>=project_price){
                if(items==10){  
                    fee_price = parseFloat(items);      
                }else{   
                    fee_price = parseFloat(project_price*items);   
                }    
                console.log([fee_key, project_price, fee_price, items]); 
                $('#detail-total-fee').html(number_format(fee_price, 2)); 
                $('#detail-total-price').html(number_format((project_price+fee_price), 2)); 
            } 
             
        });
        
	}else{
	    setTimeout(function (){   
            $(btn_loader).buttonLoader('stop');
        }, 100);
	}
}

function gotoStep4(){ 
    
    if($('.check_policy').is(':checked')){   
        $('#error-policy').hide();    
        var project_name = $('#project_name').val();   
        $('#send_email_subject').val(project_name+' by zenovly.com');        
        $('#send_email_message').val('Dear '+$('#name').val()+' Project Name : '+project_name); 
        $('#sendEmail').modal('show');    
    }else{    
        setError('#error-policy',txt_lang.confirm_policy);  
    } 
    
}


function sendEmailtoCustomer() 
{ 
   	var subject = $('#send_email_subject').val();
   	var message = $('#send_email_message').val();
   	if(!subject){ 
   		setError('#contact-result', 'Please enter subject !');
   		$('#send_email_subject').focus();   
   	}else if(!message){  
   		setError('#contact-result', 'Please enter message !');
   		$('#send_email_subject').focus();
   	}else{  
   		customerContract(); 
   	}    
}  

function customerContract()  
{ 
	var customer_id = $('#customer_id').val();
	var customer_type = $('#customer_type').val();
	var zenovly_type = $('#zenovly_type').val();
	
    var name = $('#name').val();  
    var email = $('#email').val(); 
    var project_name = $('#project_name').val(); 
    var project_price = $('#project_price').val();
    var start_date = $('#start_date').val(); 
    var end_date = $('#end_date').val(); 
    var detail_note = $('#project_detail').val(); 
    var contract_no = $('#project_contract_no').val();
    var phone_number = $('#phone_number').val(); 
    var who_pay_fee = $("input[type='radio']:checked").val();
    
    var user_name = $('#user_name').val();
    var user_email = $('#user_email').val(); 
    var user_phone = $('#user_phone').val(); 
    
    var formData = new FormData();  
	start_date = $.date(start_date); 
	end_date = $.date(end_date);         
	
	formData.append('request', customer_type);  
	formData.append('zenovly_type', zenovly_type); 
	    
    if(customer_type==1){ // 1 = seller 
         
		formData.append('seller_id', uid);  
		formData.append('seller_name', user_name); 
		formData.append('seller_email', user_email);
		formData.append('seller_number', user_phone);
		
		formData.append('buyer_id', customer_id); 
		formData.append('buyer_name', name); 
		formData.append('buyer_email', email);
		formData.append('buyer_number', phone_number);
		
    }else{ // 0 = buyer 
    
		formData.append('buyer_id', uid);
		formData.append('buyer_name', user_name);
		formData.append('buyer_email', user_email);
		formData.append('buyer_number', user_phone);
		
		formData.append('seller_id', customer_id);
		formData.append('seller_name', name);  
		formData.append('seller_email', email);
		formData.append('seller_number', phone_number);
    } 
    
    formData.append('total_price', project_price);
    formData.append('project_name', project_name);
    formData.append('start_date', start_date);
    formData.append('end_date', end_date); 
    formData.append('note', detail_note);  
    formData.append('contract_number', contract_no);
    formData.append('who_pay_fee', who_pay_fee); 
    
    formData.append('contract_img', base64_file1);  
    formData.append('contract_img2', base64_file2); 
    formData.append('contract_img3', base64_file3);
    formData.append('contract_img4', base64_file4);
    formData.append('contract_img5', base64_file5);
    formData.append('contract_img6', base64_file6);
    formData.append('contract_img7', base64_file7);
    formData.append('contract_img8', base64_file8);
    formData.append('contract_img9', base64_file9); 
    //$.each(base64_array, function(){  
       
    
	formData.append('project_name', project_name);  
	formData.append('subject', $('#send_email_subject').val()); 
	formData.append('body', $('#send_email_message').val()); 
	 
	var url = apiUrl+'makecontract/?'+user_api+'&'+password_api+'&act=4';  
	
	if(uid && customer_id){
	    $.ajax({
          url: url,
          data: formData,
          processData: false,
          contentType: false, 
          dataType : 'json', 
          type: 'POST',
          success: function(result){ 
            //console.log(result);   
            if(result.status == 200)       
            {        
				setsSuccess('#contact-result', 'Successfuly.'); 
				setTimeout(function(){   
					$('#sendEmail').modal('hide'); 
					$('#tab-step4').addClass('process-active active');
		            $('.tab-pane').removeClass('active'); 
		            $('#tab4').addClass('active');  
		            $(window).scrollTop(0);   
				},3100);  
            }else{     
                setError('#contact-result', txt_error);
            }   
            $(btn_loader).buttonLoader('stop');
          }
        });
          
        /*
    	$.post(url,formData,function(result){ 
    		if(result.status == 200)       
            {       
				setsSuccess('#contact-result', 'Successfuly.'); 
				setTimeout(function(){   
					$('#sendEmail').modal('hide'); 
					$('#tab-step4').addClass('process-active active');
		            $('.tab-pane').removeClass('active'); 
		            $('#tab4').addClass('active');  
		            $(window).scrollTop(0);   
				},3100);  
            }else{     
                setError('#contact-result', txt_error);
            }   
            $(btn_loader).buttonLoader('stop');
		},'json');   
		*/ 
		
	}else{         
		setError('#contact-result', txt_error);
		$(btn_loader).buttonLoader('stop'); 
	} 
} 


function customerNewpassword()
{      
	$('#login-rs').html('');  
	var email = $('#login-email').val();   
	var pass = $('#login-password').val();     
	var url = apiUrl+'profile/?'+user_api+'&'+password_api+'&act=changePasswordByEmail';   
	if(pass.length >= 5){           
        url += '&email='+email+'&upassword='+pass;    
        $.get(url, function(result){        
    		if(result.status == 200)        
            {          
			 	setsSuccess('#login-rs', txt_lang.alert_password_successfuly); 
			 	goLogin();           
            }      
            else        
            { 
                setError('#login-rs', result.items); 
                $('#login-password').focus();      
            }   
            $(btn_loader).buttonLoader('stop');
		}, 'json');      
	}else{    
		setError('#login-rs', txt_lang.alert_password_long);
		$('#login-password').focus();   
	}  
	return false; 
}

function projectAccept(){
    var user_request = $('#user_request').val();
    var contract_id = $('#contract_id').val();
    var buyer_id = $('#buyer_id').val();   
    var seller_id = $('#seller_id').val();  
    var url = apiUrl+'contract/'+contract_id+'/?'+user_api+'&'+password_api+'&act=accept'; 
    var data = {'buyer_id':buyer_id,'seller_id':seller_id};  
    $('#payment-loading').show();
    $.post(url, data, function(result){  
        if(result.status==200){     
            if(user_request==1){    
                //console.log(base_url+'contract/'+contract_id+'/');    
                window.location = base_url+'contractinfo/'+contract_id+'/'; 
            }else{    
                gotoPayment();  
            }  
            $(btn_loader).buttonLoader('stop');
        }else{     
            setError('#error-policy', result.items);
            $(btn_loader).buttonLoader('stop');
        }  
        $('#payment-loading').hide();
    },'json');   
} 


function gotoPayment(){   
    var pay_price = $('#pay_price').val();
    var mangopay_id = $('#mangopay_id').val();   
    var mangopay_wallet= $('#mangopay_wallet').val();
    var contract_id = $('#contract_id').val();
    var url_pay = apiUrl+'pay/'+mangopay_id+'/?'+user_api+'&'+password_api+'&wid='+mangopay_wallet+'&amount='+(pay_price*100)+'&zenovly_id='+contract_id+'&returnURL='+document.URL+'';
    window.location = url_pay;   
}




function contactUs()
{
	var name = $('#contactus-name').val();
	var email = $('#contactus-email').val();
	var subject = $('#contactus-subject').val();
	var message = $('#contactus-message').val();
	$('#contact-result').html('');  
	var apiLink = apiUrl+'mail/?username=RockStar&password=Um9ja1N0YXI=';
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
                setError('#contact-result', txt_lang.oops_somting);   
            }
		}).catch(function (error) { 
			setError('#contact-result', error); 
		}); 
	}
}

/*--Facebook--*/
if (typeof(FB) != 'undefined' && FB != null )
{
    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            //console.log('Logged in.');
        }
    });
}

function getUserData()
{  
	FB.api('/me', {fields: 'id,name,email'}, function(response)
	{
	    //console.log(response);
		var apiLink = apiUrl+"regis/?username=RockStar&password=Um9ja1N0YXI="; 
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

function connectUserFB()   
{   
    $('#result-social').html('');     
	var apiLink = apiUrl+"profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=edit";  
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

function userLoginFB(rs) 
{
	var apiLink = apiUrl+"login/?username=RockStar&password=Um9ja1N0YXI=&rd="+makeid(); 
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

function userRegisterFB(rs)
{
	if(rs.email!=='')
	{
        var apiLink = apiUrl+"regis/?username=RockStar&password=Um9ja1N0YXI=";
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




function forgotpassword(){        
	$('#forgot-email-result').html('');   
	var email = document.getElementById('forgot-email').value;    
	var apiLink = apiUrl+"profile/?username=RockStar&password=Um9ja1N0YXI=&act=forgotPassword";  
	if(validateEmail(email)){      
		apiLink += '&email='+email; 
	    axios.get(apiLink).then(function (response) {    
    		var result = response.data; 
    		console.log(result); 
    		if(result.status == 200)    
            {     
			 	$('#forgot-email').val('');
			 	setsSuccess('#forgot-email-result', result.items);
			 	setTimeout(function(){     
			 	  window.location = baseURL+lang+'/account/'; 
			 	},4000);        
            }else{  
                setError('#forgot-email-result', result.items); 
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

function forgotnewpassword()  
{  
	$('#forgot-newpass-display').html(''); 
	var forgot_email = document.getElementById('forgot-newemail').value; 
	var forgot_pass = document.getElementById('forgot-newpass').value;  
	var apiLink = apiUrl+"profile/?username=RockStar&password=Um9ja1N0YXI=&act=changePasswordByEmail";  
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
		        setTimeout(function(){     
			 	  window.location = baseURL+lang+'/account/'; 
			 	},6000);  
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


function editProfile() 
{   
    
    $('#result').html('');    
	var apiLink = apiUrl+"profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=edit";  
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
			 	setTimeout(function(){    
			 	    //window.location.reload();   
			 	},3100); 
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
	var apiLink = apiUrl+"profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=imgPF";
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
                setError('#imgP-result', result.items); 
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

function editCompanyInfo() 
{   
    $('#result-company').html('');
	var apiLink = apiUrl+"profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=companyUpdate";  
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
                setError('#result-company', result.items); 
            }  
            $(btn_loader).buttonLoader('stop');
		}).catch(function (error) {  
			setError('#result-company',error);  
			$(btn_loader).buttonLoader('stop'); 
		}); 
	}  
	return false;  
} 


function newPassword() 
{ 
	var error = 0; 
	$('#result').html('');  
	var apiLink = apiUrl+"profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=changePassword";  
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
                setError('#result', response.items);  
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

var status_def = 1;   
var perpage = 21;
var page = 1; 
function getByStatus(status)    
{ 
	if(status_def!=status)
	{    
		page=1;  
		status_def = status; 
		$('#pagination').twbsPagination('destroy'); 
	}  
	
	var html = '<tr><td colspan="8" class="text-center">Loadding....</td></tr>';  
	$('#contract-list').html(html);           
    var apiLink = apiUrl+"contract/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act="+action+"&status="+status+'&page='+page+'&rd='+makeid();
    //console.log(apiLink);  
    axios.get(apiLink).then(function (response) {      
		var result = response.data;  
		html = ''; 
		$('#contract-list').html(html);     
		if(result.status==200)         
        {   
		 	var no_rows = 1; 
		 	if(page>1){
		 		no_rows = (page*perpage)-(perpage+1); 
		 	}     
		 	$.each(result.items, function(i, item) {
		 		html +='<tr>';    
		 		html +='<td>'+no_rows+'</td>';  
		 		if(!item.project_name) item.project_name = '-';  
			 	html +='<td>'+item.project_name+'</td>';
			 	//html +='<td>'+item.contract_name+'</td>';  
			 	//html +='<td>'+item.contract_company+'</td>';     
			 	html +='<td>'+number_format(item.total_price,2)+'</td>';   
			 	html +='<td>'+toDate(item.start_date)+'</td>';   
			 	html +='<td>'+toDate(item.end_date)+'</td>';             
			 	html +='<td>';
			 	html +='<a class="btn btn-info btn-xs" target="_blank" href="'+baseURL+lang+"/contractinfo/"+item.id+"/"+'"><i class="fa fa-eye"></i>  Detial</a>';  
			 	if(action=='buyer' && item.clickNotGet==1){                 
			 	    html +=' <button class="btn btn-warning btn-xs" onclick="statusNotItem(this,'+item.id+');"><i class="fa fa-exclamation"></i>  I didn\'t get item</button>';   
			 	}
			 	if(action=='buyer' && item.status==5){               
			 	    html +=' <button class="btn btn-success btn-xs" onclick="statusDone(this,'+item.id+');"><i class="fa fa-play-circle-o"></i>  Done</button>';   
			 	}    
			 	html +='</td>';    
			 	html +='<tr>';  
			 	no_rows++;
			}); 
			var total_page = Math.ceil((result.total/perpage));
			if(total_page>1){ 
				$('#pagination').twbsPagination({   
			        totalPages: Math.ceil((result.total/perpage)),  
			        visiblePages: 7,
			        onPageClick: function (event, p) {
			        	page = p;  
			        	getByStatus(status_def); 
			        }   
			    });
			}
        }else{   
        	html = '<tr><td colspan="7" class="text-center">Not '+action+' contract</td></tr>'; 
        }  
        $('#contract-list').html(html); 
	}).catch(function (error) { 
		$('#contract-list').html('<tr><td colspan="7" class="text-center">'+error+'</td></tr>');
	});  
}  

function statusDone(self, cid){      
    var url = apiUrl+'done/'+cid+'/?'+user_api+'&'+password_api+'&sstatus=3';   
    //console.log(url);  
    $(self).html('<span class="spinner"><i class="fa fa-spinner fa-spin" title="button-loader"></i></span> Loadding...'); 
    $(self).attr('disabled','disabled'); 
    $.get(url, function(result){    
        console.log(result);    
        if(result.status==200){     
            getByStatus(status_def);  
        } 
        $(self).html(tt); 
        $(self).removeAttr('disabled');
    },'json');    
}


function addBank(){ 
    var mangopay_id = $('#mangopay_id').val(); 
    var iban_account = $('#iban_account').val(); 
    var userbnak_name = $('#userbnak_name').val();
    var bic_code = $('#bic_code').val();
    var userbank_address = $('#userbank_address').val();   
    
    var url = apiUrl+'addbank/'+uid+'/?'+user_api+'&'+password_api+'&mangopay_id='+mangopay_id+'&iban='+iban_account+'&uname='+userbnak_name+'&bic='+bic_code+'&address='+userbank_address;   
    $("#form-bank").valid();      
	if($("#form-bank").valid()){  
        $.get(url, function(result){     
            if(result.status==200){             
                setsSuccess('#result-bank', result.items);
                $('#iban_account').val('');  
                $('#userbnak_name').val(''); 
                $('#bic_code').val(''); 
                $('#userbank_address').val('');
            }else{  
                setError('#result-bank', result.items);   
            }   
            $(btn_loader).buttonLoader('stop');  
        },'json');    
	}else{ 
	    setTimeout(function () {  
	        $(btn_loader).buttonLoader('stop');
	    }, 100);  
	}
	return false;
} 

function statusNotItem(self, cid){        
    var url = apiUrl+'done/'+cid+'/?'+user_api+'&'+password_api+'&sstatus=4';   
    //console.log(url); 
    var tt = $(self).html();
    $(self).html('<span class="spinner"><i class="fa fa-spinner fa-spin" title="button-loader"></i></span> Loadding...'); 
    $(self).attr('disabled','disabled'); 
    $.get(url, function(result){    
        console.log(result);   
        if(result.status==200){     
            getByStatus(status_def);  
        }  
        $(self).html(tt); 
        $(self).removeAttr('disabled');
    },'json');    
}

function trackingcode(cid, uid){
    var tackingcode = $('#tracking_code').val();
    var url = apiUrl+'contract/'+cid+'/?'+user_api+'&'+password_api+'&act=addTracking&user_id='+uid+'&shipping_tracking_number='+tackingcode;   
    if(!tackingcode){  
        setError('#trackingcode-result', 'Please enter tracking code.');
        $('#tracking_code').focus();  
        
    }else{
        $.get(url, function(result){
            if(result.status==200){    
                $('#tracking_code').val('');  
                setsSuccess('#trackingcode-result', 'Tracking code saved successfully.');
                setTimeout(function(){   
                    window.location.reload(); 
                },1000);    
            }else{ 
                setError('#trackingcode-result', result.items); 
            }   
            setTimeout(function () {  
    	        $(btn_loader).buttonLoader('stop');
    	    }, 100);  
        },'json'); 
    }
    $(btn_loader).buttonLoader('stop');
}
