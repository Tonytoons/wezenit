var txt_btn = '';
var base64_file;
var project = 1;
var supplier = 1;

function setProject(value){
    project = value; 
}

function setSupplier(value){
    supplier = value;   
} 

(function ($)
{
    wow = new WOW( {
        animateClass: 'animated',
        offset:       100
    });
    wow.init();
    //jQuery to collapse the navbar on scroll
    $(window).scroll(function() {
        if ($(".navbar-default").offset().top > 50) {
            $(".navbar-fixed-top").addClass("top-nav-collapse");
        } else {
            $(".navbar-fixed-top").removeClass("top-nav-collapse");
        }
    });

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
            var text = 'Loading';         
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

    $("#shareButtonLabel").jsSocials({
        showCount: false,
        showLabel: true,
        shares: [
            "twitter",
            "facebook", 
            "googleplus",
            "linkedin",
            "pinterest" 
        ]
    });
    
})(jQuery);

var inputSTEP = 0;
$(document).ready(function ()
{
    $('.nav-tabs > li a[title]').tooltip();
    $('a[data-toggle="tab"]').on('show.bs.tab', function (e)
    {
        var $target = $(e.target);
        if ($target.parent().hasClass('disabled')) {
            return false;
        }
    });
 
    $(".next-step").click(function (e) {
        if(inputSTEP<3){ 
            var $active = $('.wizard .nav-tabs li.active');
            $active.next().removeClass('disabled');
            nextTab($active);
            inputSTEP++;
        }else if(inputSTEP==3){   
             $("#form-supplier").valid();
             if($("#form-supplier").valid()){
                inputSTEP = 4; 
                var $active = $('.wizard .nav-tabs li.active');
                $active.next().removeClass('disabled'); 
                nextTab($active);
             }
        }else if(inputSTEP==4){ 
            if($('.check_policy').is(':checked')){
                $('#error-policy').hide(); 
                sendContact();
            }else{
                setError('#error-policy',txt_lang.confirm_policy);  
            }
        }else if(inputSTEP==5){   
            $(btn_loader).buttonLoader('stop');
        }
    });
    $(".prev-step").click(function (e) {
        if(inputSTEP>0){ 
            inputSTEP--;
        }
        var $active = $('.wizard .nav-tabs li.active');
        prevTab($active);
    }); 
     
    $('.step-1.select-box').click(function(){  
        $('.step-1.select-box').removeClass('selected'); 
        $(this).addClass('selected'); 
        inputSTEP = 1; 
        $('.next-step-1').removeClass('disabled'); 
    });
    
    $('.step-2.select-box').click(function(){   
        $('.step-2.select-box').removeClass('selected'); 
        $(this).addClass('selected');
        inputSTEP = 2;    
        $('.next-step-2').removeClass('disabled'); 
    }); 
    
    $('.check_policy').change(function(){ 
        if($(this).is(':checked')){    
            $('#error-policy').hide(); 
        }
    });
    
    $('.next-step-2').click(function(){
    }); 

    $('.next-step-4').click(function(){
        $('.nav-tabs li').addClass('disabled');
    });

    $("#attachfile").change(function(){
        base64_file = getBase64(this);  
    });
}); 

function getBase64(input) { 
    if (input.files && input.files[0]) {  
        var reader = new FileReader();   
        reader.onload = function (e) { 
            base64_file = e.target.result; 
        } 
        reader.readAsDataURL(input.files[0]);
    }
} 

function nextTab(elem) { 
    $(elem).next().find('a[data-toggle="tab"]').click();
}
function prevTab(elem) {
    $(elem).prev().find('a[data-toggle="tab"]').click();
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

var date;
$(function() {
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
}); 

var dialog = $( "#subscribe-form" ).dialog({ 
  autoOpen: false,  
  width: 360,  
  modal: true,
  buttons: {  
    "OK": subscribe, 
    Cancel: function() {
      dialog.dialog( "close" );
    }
  },
  close: function() {   
    $( this ).dialog( "close" );
  } 
});     

$( "#btn-subscribe-form" ).button().on( "click", function() {
  dialog.dialog( "open" );
}); 

function subscribe(){
    var name = $('#subscribe-name').val(); 
	var email = $('#subscribe-email').val();
	$('#subscribe-result').html('');    
	var apiLink = "https://sendy.lespepitestech.com/subscribe";  
	if(!name){ 
		setError('#subscribe-result', txt_lang.alert_full_name);
		$('#subscribe-name').focus(); 
	}else if(!validateEmail(email)){ 
		setError('#subscribe-result', txt_lang.alert_email_format); 
		$('#subscribe-email').focus();
	}else{ 
	        
	    $('#sub-from').submit(); 
	    setTimeout(function(){    
		    dialog.dialog( "close" );     
	    },1000);
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

(function(d, s, id){
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) {return;}
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.com/en_US/sdk.js";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
 
window.fbAsyncInit = function() {
	FB.init({   
		appId      : '1871386853118422', 
		xfbml      : true,   
		version    : 'v2.9'
	});
}; 

$('.txt-policy').scroll(function () { 
    if ($(this).scrollTop() == $(this)[0].scrollHeight - $(this).height()) {
        $('.next-step-4').removeClass('disabled'); 
    }else{
        $('.next-step-4').addClass('disabled');
    }
});

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

function phonenumber(inputtxt)
{
    return true;
}