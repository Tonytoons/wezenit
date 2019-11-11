var btn_loader = '';
var txt_btn = '';
var error_alert = lang_alert.new_aler_error;/*'Sorry, we can\'t process this time please try again later!'; */
var bFbStatus = false; 
var fbID = 0;
var fbName = "";
var fbEmail = "";
var fbFullName = '';
var fbGender = '';
var connected = 0;
var redirect_utl = '';
var base_url = baseURL+''+lang+'/'; 
var action_name = ''; 


var base_url = baseURL+action_name+lang+'/'; 

$('.btn-loader').click(function() {
	btn_loader = $(this);
	$(btn_loader).buttonLoader('start');
});
$.fn.buttonLoader = function(action) {
	var self = $(this); 
	$(self).attr("disabled", false);
	if (action == 'start') { 
		if ($(self).attr("disabled") == "disabled") {
			return false;
		} 
		$('.btn-loader').attr("disabled", true);
		txt_btn = $(self).html();
		var text = lang_alert.new_aler_loadding; /*'Loading...'; */
		if ($(self).attr('data-load-text') != undefined && $(self).attr('data-load-text') != "") {
			var text = $(self).attr('data-load-text');
		}
		$(self).html('<span class="spinner"><i class="fa fa-spinner fa-spin" title="button-loader"></i></span> ' + text);
		$(self).addClass('active');
	}
	if (action == 'stop') {
		$(self).html(txt_btn);
		$(self).removeClass('active');
		$('.btn-loader').attr("disabled", false);
	}
}
 
$(document).ready(function($) {
	/*  
	"use strict";

	var loader = function() {
		
		setTimeout(function() { 
			if($('#pb_loader').length > 0) {
				$('#pb_loader').removeClass('show');
			}  
		}, 700);
	}; 
	loader();
	*/
	 
	$('.datepicker').datepicker({ 
        format: 'mm/dd/yyyy'
    }); 
    
    $('#profile-birthday').datepicker({
        maxDate: 0,
        changeMonth: true,
        changeYear: true,
        format: 'mm/dd/yyyy'
    });
    
    /*
    $('#form-search').live("submit", function() {
        alert('sss');
    }); 
    */ 


});

$('#login-password, #login-email').keypress(function(e) {
    if(e.which == 13) {
    	btn_loader = $(".btn-loader");
    	$(btn_loader).buttonLoader('start'); 
    	login();
    }
});

$('#confirm-password, #input-password, #regis-email, #regis-name').keypress(function(e) {
    if(e.which == 13) {  
    	btn_loader = $(".btn-loader");
    	$(btn_loader).buttonLoader('start'); 
    	register();
    }
});
 

/*--Facebook--*/
if (typeof(FB) != 'undefined' && FB != null )
{
	FB.getLoginStatus(function(response) {
	 	console.log(response.status);
	    if (response.status === 'connected') {
	       connected = 1; 
	    }
	});  
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
	swal({
        title: lang_alert.new_aler_logout,
        //text: "Once a invoice is created, you will not be able to delete without the help of support",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#5d2a73',  
        confirmButtonText: lang_alert.new_aler_logout_yes,
        cancelButtonText: lang_alert.new_aler_logout_no,
    }).then(function() {
    	 
    	setCookie("uid", '', '', 0);  
        setCookie("uname", '', '', 0);  
        setCookie("uimg", '', '', 0);  
        setCookie("utype", '', '', 0); 
	    //setCookie(name, '', '', 0);
	      
	    var url = base_url+'?r='+makeid();
	    console.log(url);  
	    setTimeout(function() {
			window.location = url; 
		}, 500); 
    },function(dismiss) {
        if(dismiss == 'cancel') {
            
        }
    });
    
    
    //window.location = base_url+'?r='+make_id;     
           
}

$('a.new-btn1, a.btn1').click(function(e) { 
	if(action=='index'){
		e.preventDefault();	
		var ck = $(this).attr("href");	
		var target = $(ck);	
		if (target.length) {	
			$('html, body').stop().animate({	
				scrollTop : (target.offset().top - 50)	}, 
			1000);	
			
		}	
		return false;
	} 
});


/*********** login and register form ********/
$('#login-form-link').click(function(e) { 
	$("#login-form, #img-login").delay(100).fadeIn(100);
 	$("#register-form, #img-regis").fadeOut(100);
	$('#register-form-link').removeClass('active');
	$(this).addClass('active');
	
	e.preventDefault(); 
}); 
$('#register-form-link').click(function(e) {
	$("#register-form, #img-regis").delay(100).fadeIn(100);
 	$("#login-form, #img-login").fadeOut(100); 
	$('#login-form-link').removeClass('active');
	$(this).addClass('active');
	e.preventDefault();
});

window.alert = function(title) { 
	swal({
		title : title,
		text : "",
		type : 'warning', 
		timer: 5000,  
	    showCancelButton: false,
	    showConfirmButton: false
	}).then(
	  function () {},
	  // handling the promise rejection
	  function (dismiss) {
	    if (dismiss === 'timer') {
	      $(btn_loader).buttonLoader('stop'); 
	    }
	  }
	);    
	setTimeout(function() {
		$(btn_loader).buttonLoader('stop');
	}, 100); 
	return false;
}; 
 
function alert_error(title) {
	swal({
		title : title,
		text : "",
		type : 'error',
		timer: 5000, 
	    showCancelButton: false,
	    showConfirmButton: false
	}).then(
	  function () {},
	  // handling the promise rejection
	  function (dismiss) {
	    if (dismiss === 'timer') {
	      $(btn_loader).buttonLoader('stop'); 
	    }
	  }
	);  
	setTimeout(function() {
		$(btn_loader).buttonLoader('stop');
	}, 1000); 
	return false;
}  

function alert_success(title) {  
	swal({
		title : title,
		text : "",
		type : 'success',
		timer: 5000, 
	    showCancelButton: false,
	    showConfirmButton: false
	}).then( 
	  function () {}, 
	  // handling the promise rejection
	  function (dismiss) {
	    if (dismiss === 'timer') {
	      $(btn_loader).buttonLoader('stop'); 
	    }
	  }
	); 
	setTimeout(function() {
	   $(btn_loader).buttonLoader('stop');
	}, 1000); 
	return false;
} 
 
function login(){ 
	var email = $('#login-email').val();
	var password = $('#login-password').val(); 
	 
	if (!validateEmail(email)) {
		alert(lang_alert.new_aler_enter_email);
		$('#login-email').focus();
	} else if ((password.length < 6) || (password.length > 15)) { 
		alert(lang_alert.new_aler_pass_6_15);  
		$('#login-password').focus(); 
	} else {   
		var url = base_url+'account/?act=login&r='+makeid();   
		var data = {'email':email, 'password':password};  
		$.post(url, data, function(result){
			if(result.status==200){ 
				uid = result.items.id;  
                setCookie("uid",result.items.id);  
                setCookie("uname",result.items.name);  
                if(result.items.image!='' && result.items.image!=null){      
                    setCookie("uimg", result.items.image_url);  
                }else{       
                	//setCookie("uimg",'https://files.renovly.com/setting/avatar.jpg'); 
                }   
                setCookie("utype", result.items.type);      
                 
                if(action=='contract' || action=='projectform' || action=='contractinfo'){   
    			 	window.location.reload();    
                }else if(getCookie('gotopage')!=''){     
                	window.location = getCookie('gotopage');  
                }else{      
                	window.location = base_url+'?r='+makeid();      
                }  
			}else{  
				setTimeout(function(){   
					$(btn_loader).buttonLoader('stop');	
			 	},1000);  
			 	alert_error(result.items); 
			}  
			return false; 
		},'json').fail(function() {
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);   
		    alert_error(error_alert);
		});     
	} 
	
	return false; 
}

function validateEmail(email) {
	var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
}

function validatePassword(value) {
    var error = "";
    var illegalChars = /[\W_]/; // allow only letters and numbers
 
    if (value == "") {
        error = lang_alert.new_aler_enater_pass;
        alert(error);
        return false;
 
    } else if ((value.length < 7) || (value.length > 15)) {
        error = lang_alert.new_aler_pass_wrong_length;
        alert(error);
        return false;
 
    } else if (illegalChars.test(value)) {
        error = lang_alert.new_aler_password_contains;
        alert(error);
        return false;
 
    } else if ( (value.search(/[a-zA-Z]+/)==-1) || (value.search(/[0-9]+/)==-1) ) {
        error = lang_alert.new_aler_password_must_contain;
        alert(error);
        return false;
 
    }
   return true;
}

function makeid()
{ 
    var text = ""; 
    var possible = "0123456789";
    for( var i=0; i < 6; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    return text;  
} 
 
function register(){    
	var name = $('#regis-name').val();
	var email = $('#regis-email').val();
	var password = $('#input-password').val();
	var confirmPass = $('#input-confirm-password').val(); 
	var buyer_id = $('#buyer_id').val();
	if(!buyer_id) buyer_id = 0;
	
	if(name.length < 2){  
		alert(lang_alert.new_aler_enter_name); 
		$('#regis-name').focus(); 
	}else if (!validateEmail(email)) {
		alert(lang_alert.new_aler_enter_email);
		$('#login-email').focus();
	} else if ((password.length < 6) || (password.length > 15)) { 
		alert(lang_alert.new_aler_pass_6_15); 
		$('#login-password').focus(); 
	}else if(password!=confirmPass){
		alert(lang_alert.new_aler_pass_not_same);   
		$('#input-confirm-password').val('');
		$('#input-confirm-password').focus(); 
	} else {    
		var url = base_url+'account/?act=register&r='+makeid();   
		var data = {'name':name,'email':email, 'password':password,'facebook_id':fbID,'buyer_id':buyer_id};     
		$.post(url, data, function(result){ 
			
			if(result.status==200){ 
				
				$('#login-email').val(email); 
				$('#login-password').val(password); 
				login();  
				/* 
				if(buyer_id){ 
					uid = buyer_id; 
					
	                setCookie("uid",buyer_id);   
	                setCookie("uname",name); 
				}else{
					uid = result.items.id;   
	                setCookie("uid",result.items.id);  
	                setCookie("uname",result.items.name); 
				}
                if(result.items.image!='' && result.items.image!=null){      
                    setCookie("uimg", result.items.image_url); 
                }else{       
                	//setCookie("uimg",'https://files.renovly.com/setting/avatar.jpg'); 
                }   
                setCookie("utype", result.items.type);      
                 
                if(action=='contract' || action=='projectform'){  
    			 	window.location.reload();   
                }else if(getCookie('gotopage')!=''){     
                	window.location = getCookie('gotopage'); 
                }else{     
                	window.location = base_url+'?r='+makeid();        
                }  
                */
			}else{ 
				 
				setTimeout(function(){   
					$(btn_loader).buttonLoader('stop');	
			 	},1000);   
			 	alert_error(result.items);    
			}  
			return false; 
		},'json').fail(function() {  
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);   
		    alert_error(error_alert); 
		});     
	} 
	  
	return false; 
}


function sendContactUs()
{
    var name = $('#name').val();
    var email = $('#email').val();
    var message = $('#message').val();
    if(name.length < 2){  
		alert(lang_alert.new_aler_enter_name); 
		$('#name').focus();  
	}else if (!validateEmail(email)) {
		alert(lang_alert.new_aler_enter_email);
		$('#email').focus(); 
	}else if(message.length < 2){ 
		alert(lang_alert.new_aler_enter_message);
		$('#message').focus(); 
	}else{ 
		var url = base_url+'contact-us/?act=sendMail&r='+makeid();    
		var data = {'name':name,'email':email, 'message':message};    
		$.post(url, data, function(result){ 
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);  
			if(result.status==200){    
				alert_success(result.items);   
				$('#name, #email, #message').val('');
			}else{     
				alert_error(result.items);  
			}  
			return false; 
		},'json').fail(function() {
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);   
		    alert_error(error_alert);
		});    
	} 
	return false; 
} 

function forgotpassword()  
{
    var email = $('#email').val();
    if (!validateEmail(email)) {
		alert(lang_alert.new_aler_enter_email);
		$('#email').focus(); 
	}else{ 
		var url = base_url+'forgotpass/?r='+makeid();    
		var data = {'email':email, 'act':'forgot'};       
		$.post(url, data, function(result){    
			//console.log(result);   
			setTimeout(function(){    
				$(btn_loader).buttonLoader('stop');	
		 	},1000);
		 	
			if(result.status==200){   
				alert_success(result.items);       
				setTimeout(function(){     
					window.location = base_url+'account/?task=login&email='+email+'&r='+makeid(); 
			 	},1000);       
			}else{     
				alert_error(result.items);    
				  
			} 
			return false; 
		},'json').fail(function() {
		    alert_error(error_alert);
		}); 
	} 
	return false; 
}  

function getBase64(input, callback) { 
    if (input.files && input.files[0]) {  
        var reader = new FileReader();   
        reader.onload = function (e) {  
            base64file = e.target.result; 
            //console.log(base64file);
            base64_file = base64file;
            callback(base64file);   
        } 
        reader.readAsDataURL(input.files[0]);
    }
} 

$.date = function(dateStr)
{
	//console.log(dateStr);   
    
	var day = dateStr.substring(3, 5);
    var month = dateStr.substring(0, 2);
    var year = dateStr.substring(6, 10);  
     
    var date = year + "-" + month + "-" + day; 
    
    console.log(date);    
     
    return date;  
    
    /*
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

    return date; */
};  
 
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
    /// dateStr.toString().substring(0, 4)
    
    //console.log(dateStr);   
    
	var curr_date = dateStr.substring(8, 10);
    var curr_month = dateStr.substring(5, 7);
    var curr_year = dateStr.substring(0, 4);  
    
    var new_fomat = curr_month+'/'+curr_date+'/'+curr_year;
    //console.log(new_fomat); 
     
    return new_fomat;  
	/*
    var d = new Date(dateStr); 
    var curr_date = d.getDate();
    var curr_month = d.getMonth() + 1;
    var curr_year = d.getFullYear();  
    return curr_month+'/'+curr_date+'/'+curr_year */
}  

function addTrackingCode()
{
	swal({
	  title: lang_alert.new_aler_add_tracking_code,
	  input: 'text',
	  inputPlaceholder: lang_alert.new_aler_tracking_code,
	  showCancelButton: true,
      confirmButtonText: lang_alert.new_aler_save,  
      cancelButtonText: lang_alert.new_aler_cancal,
      showSpinner: true, 
      //timer: 2000
	}).then(function(result) {   
	  if(result!=''){ 
	  	btn_loader = $('#btn-tracking'); 
	  	$(btn_loader).buttonLoader('start'); 
	  	var url = base_url+'contractinfo/'+id+'/?act=addCode&code='+result+'&r='+makeid(); 
		$.get(url, function(result){    
			setTimeout(function(){    
				$(btn_loader).buttonLoader('stop');	
		 	},1000);    
			if(result.status==200){       
				alert_success(lang_alert.new_aler_tracking_code_ok);   
				setTimeout(function(){ 
					window.location.reload(); 
				},500); 
			}else{      
				alert_error(result.items);  
			}  
			return false; 
		},'json').fail(function() {
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);   
		    alert_error(error_alert);
		}); 
	  }else{
	  	alert(lang_alert.new_aler_tracking_code);
	  } 
	}, 
	function (dismiss) { 
	  if (dismiss === "cancel") {
	    
	  }
	});
}

function forgotnewpassword()  
{  
	$('#forgot-newpass-display').html(''); 
	var forgot_email = $('#forgot-newemail').val(); 
	var forgot_pass = $('#forgot-newpass').val(); 
	var forgot_token = $('#forgot-token').val(); 
	
	if ((forgot_pass.length < 6) || (forgot_pass.length > 15)) { 
		alert(lang_alert.new_aler_new_pass); 
		$('#forgot-newpass').focus(); 	
		return false; 
	}else{
		var url = base_url+'forgotpassword/?act=newpass&token='+forgot_token+'&r='+makeid();      
		var data = {   
			"data[act]":'changePasswordByEmail', 
			"data[email]":forgot_email,          
			"data[upassword]":forgot_pass 
		};
		
		$.post(url, data, function(result){    
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);     
		 	console.log(result); 
			if(result.status==200){       
				alert_success(result.items);         
				setTimeout(function(){         
					window.location = base_url+'account/?task=login&email='+forgot_email+'&r='+makeid(); 
			 	},1000);        
			}else{       
				alert_error(result.items); 
			}   
			return false;  
		},'json').fail(function() {
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);   
		    alert_error(error_alert);
		});   
	}  
	return false; 
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

var mangopay_id;
$('.txt-policy').scroll(function () {   
	//console.log($(this).scrollTop());  
	var th = (($(this)[0].scrollHeight - $(this).height())-300);
	var sc = $(this).scrollTop(); 
	//console.log([th,sc]); 
    if (sc >= th) {    
    	  
    	if(action=='projectform'){ 
        	$('#btn-next-step3').removeClass('disabled'); 
    	}else{
    		$('#btn-next-step1').removeClass('disabled');
    	}
    	
    }else{ 
    	
    	if(action=='projectform'){
        	$('#btn-next-step3').addClass('disabled'); 
    	}else{
    		$('#btn-next-step1').addClass('disabled');
    	}  
        //$('#btn-next-step1, #btn-next-step3').addClass('disabled');
    }   
});    

if(action=='contract'){ 
	    
    /********* step project form ********/
    $('#btn-next-step1').click(function(){ 
    	
        if($('.check_policy').is(':checked')){ 
        	btn_loader = $(this);      
    		$(btn_loader).buttonLoader('start'); 
            projectAccept();    
        }else{  
            $(btn_loader).buttonLoader('stop');
            alert(lang_alert.new_aler_confirm_policy);  
        }     
    });    
     
    $('#btn-next-step2').click(function(){   
        alert(lang_alert.new_aler_send_api);   
        $('.tab-pane').removeClass('active');  
        $('#tab3').addClass('active');   
        $(window).scrollTop(0); 
    }); 
    
}   

function projectAccept(){
	
    var user_request = $('#user_request').val();
    var contract_id = $('#contract_id').val();
    var buyer_id = $('#buyer_id').val();   
    var seller_id = $('#seller_id').val();  
    /*
    var url = apiUrl+'contract/'+contract_id+'/?'+user_api+'&'+password_api+'&act=accept'; 
    var data = {'buyer_id':buyer_id,'seller_id':seller_id};  
    */
    
    var data ={     
    		   'data[id]':contract_id, 
               'data[buyer_id]':buyer_id, 
               'data[seller_id]':seller_id
            };     
             
    var url = base_url+'contract/?act=accept&r='+makeid();  
     
    $.post(url, data, function(result){  
       if(result.status==200){     
            if(user_request==1){        
                window.location = base_url+'contractinfo/'+contract_id+'/'; 
            }else{    
                gotoPayment();    
            }      
            $(btn_loader).buttonLoader('stop');
        }else{      
            alert_error(result.items);  
            setTimeout(function(){     
				$(btn_loader).buttonLoader('stop');	
		 	},1000);  
        }  
    },'json').fail(function() {
		setTimeout(function(){     
			$(btn_loader).buttonLoader('stop');	
	 	},1000);    
	    alert_error(error_alert);
	});  
}    

 
function gotoPayment(){      
    var pay_price = $('#pay_price').val();
    var mangopay_id = $('#mangopay_id').val();   
    var mangopay_wallet= $('#mangopay_wallet').val(); 
    var contract_id = $('#contract_id').val(); 
    //var url_pay = apiUrl+'pay/'+mangopay_id+'/?'+user_api+'&'+password_api+'&wid='+mangopay_wallet+'&amount='+(pay_price*100)+'&zenovly_id='+contract_id+'&returnURL='+document.URL+''+makeid();
    window.location.reload(); // = url_pay;      
} 



function getUserData(callback)
{  
	FB.api('/me', {fields: 'id,name,email'}, function(response)
	{ 
		fbID = response.id;  
		if(!response.email){   
			response.email = response.id+'@facebook.com'; 
		}        
		callback(response); 
	}); 
} 

function connectFB(callback)
{
	if(connected==0){
		FB.login(function(response) {  
			if (response.authResponse) { 
				getUserData(function(rs){
					callback(rs); 
				}); 
			}else{   
				setTimeout(function(){     
					$(btn_loader).buttonLoader('stop');	
			 	},1000);     
			}   
		}, {scope: 'email,public_profile', return_scopes: true});
	}else{
		getUserData(function(rs){
			callback(rs); 
		});  
	} 
	/*
	FB.getLoginStatus(function(response) {
	    if (response.status === 'connected') {
			getUserData(function(rs){
				callback(rs); 
			}); 
	    }else{ 
    		FB.login(function(response) {  
				if (response.authResponse) { 
					getUserData(function(rs){
						callback(rs); 
					}); 
				}   
			}, {scope: 'email,public_profile', return_scopes: true});
	    }
	});*/
}

function connectUserFB(){ 
	var act_fb = $('.header-tabs.active').attr('id').toLowerCase(); 
	//btn_loader = $('.btn-facebook');     
	//$(btn_loader).buttonLoader('start');  
	connectFB(function(rs){   
		if(act_fb=='login-form-link'){   
			loginFB(rs);
		}else if(act_fb=='register-form-link'){
			registerFB(rs); 
		}else{    
			//connectFB(rs)
		}     
		//console.log(rs);    
	});
}

function loginFB(rs){
	
	var url = base_url+'account/?act=login&r='+makeid();   
	var data = {'email':rs.email, 'password':'','facebook_id':rs.id};  
	$.post(url, data, function(result){ 
		if(result.status==200){  
			uid = result.items.id;  
            setCookie("uid",result.items.id);  
            setCookie("uname",result.items.name);  
            if(result.items.image!='' && result.items.image!=null){      
                setCookie("uimg", result.items.image_url);  
            }else{       
            	//setCookie("uimg",'https://files.renovly.com/setting/avatar.jpg'); 
            }    
            setCookie("utype", result.items.type);      
             
            if(action=='contract' || action=='projectform'){  
			 	window.location.reload();   
            }else if(getCookie('gotopage')!=''){     
            	window.location = getCookie('gotopage');  
            }else{      
            	window.location = base_url+'?r='+makeid();      
            }  
		}else{  
			registerFB(rs); 
			/*
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);  
		 	alert_error(result.items); */
		}  
		return false; 
	},'json').fail(function() {
		setTimeout(function(){   
			$(btn_loader).buttonLoader('stop');	
	 	},1000);   
	    alert_error(error_alert);
	}); 
} 

function registerFB(rs)
{ 
	var url = base_url+'account/?act=register&r='+makeid();   
	var data = {'email':rs.email, 'name': rs.name, 'password':'', 'facebook_id':rs.id};    
	$.post(url, data, function(result){ 
		loginFB(rs);          
		/* 
		if(result.status==200){   
			
			uid = result.items.id;   
            setCookie("uid",result.items.id);  
            setCookie("uname",result.items.name);  
            if(result.items.image!='' && result.items.image!=null){      
                setCookie("uimg", result.items.image_url); 
            }else{       
            	//setCookie("uimg",'https://files.renovly.com/setting/avatar.jpg'); 
            } 
             
            setCookie("utype", result.items.type);      
              
            if(action=='contract' || action=='projectform'){  
			 	window.location.reload();   
            }else if(getCookie('gotopage')!=''){     
            	window.location = getCookie('gotopage'); 
            }else{     
            	window.location = base_url+'?r='+makeid();        
            }   
		}else{ 
			loginFB(rs); 
		
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);   
		 	alert_error(result.items);    
		}  */ 
		return false; 
	},'json').fail(function() {  
		setTimeout(function(){   
			$(btn_loader).buttonLoader('stop');	
	 	},1000);   
	    alert_error(error_alert); 
	}); 
	
}

function search(){ 
	//console.log('sssss');
	var s = $('#search-transaction').val();
	if(s.length <= 1){     
		alert(lang_alert.new_aler_keyword);    
		return false;
	}else{
		return true;	
	}
	return false;
}
 

function switchLang(lang){
	var url=window.location.href;   
	
	
	if(lang=='fr'){ 
		
		url = url.replace("/en/", "/fr/"); 
		
	}else{      
		url = url.replace("/fr/", "/en/"); 
	 
	} 
	 
	var n = url.search(lang);
	
	if(n<1){   
		 url += lang+'/'; 
	} 
	
	if(action=='index'){  
		 url = '/'+lang+'/?rd='+makeid(); 
		 console.log(url);    
	}   
	//console.log(url);  
	window.location = url; 
}

document.onreadystatechange = function(e)
{
  if(document.readyState=="interactive")
  {
    var all = document.getElementsByTagName("*");
    for (var i=0, max=all.length; i < max; i++) 
    {
      set_ele(all[i]);
    }
  }
}

function check_element(ele)
{
  var all = document.getElementsByTagName("*");
  var totalele=all.length;
  var per_inc=100/all.length;
  if($(ele).on())
  { 
    var prog_width=per_inc+Number($("#progress_width").val());
     
    $("#progress_width").val(prog_width);   
       
    $("#bar1").animate({width:prog_width+"%"},0,function(){
      if(document.getElementById("bar1").style.width=="100%")
      {
        $(".progress").fadeOut(); 
      }			
    });
  }
  else	
  {
    set_ele(ele);
  }
}

function set_ele(set_element)
{
   check_element(set_element);
} 

function newpasswordRegis(){
	var id = $("#id").val(); 
	var name = $("#name").val(); 
	var email = $("#email").val();
	var password = $("#password").val();
	var url = base_url+'account/?act=register&r='+makeid();   
	var data = {'name':name,'email':email, 'password':password,'facebook_id':'','buyer_id':id};  
	if ((password.length < 6) || (password.length > 15)) { 
		alert(lang_alert.new_aler_pass_6_15);  
		$('#password').focus();
		return false;  
	}else{ 
		$.post(url, data, function(result){
			if(result.status==200){ 
				$('#login-password').val(password); 
				login(); 
			}else{ 
				setTimeout(function(){   
					$(btn_loader).buttonLoader('stop');	
			 	},1000);   
			 	alert_error(result.items);    
			}  
			return false;
		},'json');
	}/* 
	setTimeout(function(){   
		$(btn_loader).buttonLoader('stop');	
 	},1000);  */  
}