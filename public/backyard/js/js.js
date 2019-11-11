var user_api = 'username=RockStar';
var password_api = 'password=Um9ja1N0YXI=';  
var btn_loader = '';
var uid = 0;
var txt_btn = '';
var base_url = baseURL+'new/'+lang+'/';

if(getCookie('uid')!=''){
    uid = getCookie('uid'); 
}

$(function() {
    init();
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
	 
	
	/********* step project form ********/
    
    $('#btn-next-step1').click(function(){  
       $('#tab-step2').addClass('process-active active');
       $('.tab-pane').removeClass('active'); 
       $('#tab2').addClass('active');   
       $(window).scrollTop(0); 
    }); 
     
    $('#btn-next-step2').click(function(){  
       $('#tab-step3').addClass('process-active active');
       $('.tab-pane').removeClass('active'); 
       $('#tab3').addClass('active');   
       $(window).scrollTop(0); 
    }); 
    
    $('#btn-next-step3').click(function(){  
       $('#tab-step4').addClass('process-active active');
       $('.tab-pane').removeClass('active'); 
       $('#tab4').addClass('active');    
       $(window).scrollTop(0); 
    }); 
      
    
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
    

    
});

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
	    setTimeout(function ()
    	{   
            $(btn_loader).buttonLoader('stop');
        }, 100);
	}
}
 


function project_contrack(){
    var who_pay_fee = $("input[type='radio']:checked").val();
}