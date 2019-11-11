var a; 
console.log(1); 
var base64_array = [];
var base64_file1='',base64_file2='',base64_file3='',base64_file4='',base64_file5='',base64_file6='',base64_file7='',base64_file8='',base64_file9 = '';
/*
$("#attachfile_project").change(function() {   
    var names = [];  
    if($(this).get(0).files.length<=9){  
        for (var i = 0; i < $(this).get(0).files.length; i++) {
            
            var reader = new FileReader();    
            reader.onload = function (e) {       
                
                if(i==1)base64_file1 = e.target.result;
        	    if(i==2)base64_file2 = e.target.result;
        	    if(i==3)base64_file3 = e.target.result;
        	    if(i==4)base64_file4 = e.target.result;
        	    if(i==5)base64_file5 = e.target.result;
        	    if(i==6)base64_file6 = e.target.result;
        	    if(i==7)base64_file7 = e.target.result;
        	    if(i==8)base64_file8 = e.target.result;
        	    if(i==9)base64_file9 = e.target.result;   
        	        
                base64_array.push(e.target.result);   
            } 
            reader.readAsDataURL($(this).get(0).files[i]);
        }  
    }else{  
        $(this).val(''); 
        setError('#rs-display', 'Upload maximum 9 file.'); 
    }
});
*/

$("#attachfile").change(function(){ 
    getBase64(this,function(rs){
        base64_file = rs;
    });  
});

var fc=1;
$("#btn-add-file").click(function(){ 
     if(fc<=9){ 
        fc++;
        $('#input-file'+fc).show();
     } 
     if(fc==9){    
        $(this).hide();  
     }
}); 

$(".remove-file").click(function(){ 
    
     $('#input-file'+fc).hide();
     $('#attachfile_project'+fc).val('');
     
     if(fc==1)base64_file1 = '';
     if(fc==2)base64_file2 = '';
     if(fc==3)base64_file3 = '';
     if(fc==4)base64_file4 = '';
     if(fc==5)base64_file5 = '';
     if(fc==6)base64_file6 = '';
     if(fc==7)base64_file7 = '';
     if(fc==8)base64_file8 = '';
     if(fc==9)base64_file9 = ''; 
     
     fc--;    
     if(fc<9){     
        $("#btn-add-file").show();  
     }
});



$(".input-file").change(function(){ 
    var c = $(this).attr("id").replace("attachfile_project",'');
    getBase64(this,function(rs){   
        //console.log(rs); 
        if(c==1)base64_file1 = rs;
	    if(c==2)base64_file2 = rs;
	    if(c==3)base64_file3 = rs;
	    if(c==4)base64_file4 = rs;
	    if(c==5)base64_file5 = rs;
	    if(c==6)base64_file6 = rs;
	    if(c==7)base64_file7 = rs;
	    if(c==8)base64_file8 = rs;
	    if(c==9)base64_file9 = rs; 
    }); 
}); 

$('#company').change(function(){
   if($(this).val()=='yes'){
       $('#company-block').show();
   }else{
       $('#company-block').hide();
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
        $('#email').val(email).attr("disabled", true); 
        $('#phone_number').val(phone);
        
        var url = base_url+'projectform/?act=UserCompany&uid='+id+'&r='+makeid();  
        $.get(url, function(result){ 
            //console.log(result); 
           if(result.status==200){    
               $('#above_position').val(result.items.above_position); 
               $('#above_company_name').val(result.items.above_company_name);
               $('#above_company_address').val(result.items.above_company_address);
               $('#above_company_website').val(result.items.above_company_website); 
           } 
        },'json'); 
    }else{    
        $('#customer_id').val(0);  
        $('#name').val('');   
        $('#email').val('').removeAttr("disabled");
        $('#phone_number').val('');
    }
}); 

/**********************  prev  ************************/
    
$('#btn-prev-step2').click(function(){ 
   $('.tab-pane').removeClass('active'); 
   $('#tab1').addClass('active'); 
   $(window).scrollTop(0); 
});    
 
$('#btn-prev-step3').click(function(){    
   $('.tab-pane').removeClass('active');  
   $('#tab2').addClass('active'); 
   $(window).scrollTop(0);
});

$('label.radio-box').click(function(){ 
    $('.radio-box').removeClass('active');
    $(this).addClass('active');
});
    
/********* step project form ********/
$('#btn-next-step1').click(function(){ 
    console.log(1); 
    var project_name = $('#project_name').val();
    var project_price = $('#project_price').val();
    var project_detail = $('#project_detail').val();
    var company = $('#company').val();
    
    if(!project_name){  
        alert(lang_alert.new_aler_project_name);  
        $('#project_name').focus();
        return false;
    }else if(!project_price){
        alert(lang_alert.new_aler_enter_price);  
        $('#project_price').focus();
        return false; 
    }else if(!project_detail){
        alert(lang_alert.new_aler_enter_detail); 
        $('#project_detail').focus();
        return false; 
    }else if(company=='yes'){
        var company_name = $('#company_name').val();
        var company_country = $('#company_country').val();
        var company_id = $('#company_id').val(); 
        var company_address = $('#company_address').val();
        if(!company_name){
           alert(lang_alert.new_aler_company_name); 
           $('#company_name').focus();
        }else if(!company_country){  
           alert(lang_alert.new_aler_country_incorporation); 
           $('#company_country').focus();
        }else if(!company_id){ 
           alert(lang_alert.new_aler_id_number_company);  
           $('#company_id').focus();   
        }else if(!company_address){     
           alert(lang_alert.new_aler_company_addr);   
           $('#company_address').focus();   
        }else{ 
           //var apiLink = apiUrl+"profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=companyUpdate";
            btn_loader = $(this);     
            $(btn_loader).buttonLoader('start'); 
            var data ={     
                       'data[company_name]':company_name,
                       'data[company_country]':company_country, 
                       'data[company_id]':company_id, 
                       'data[company_address]':company_address
                    };     
            var url = base_url+'projectform/?act=companyUpdate&r='+makeid();  
            $.post(url, data, function(result){  
               if(result.status==200){  
                   $('.tab-pane').removeClass('active'); 
                   $('#tab2').addClass('active');   
                   $(window).scrollTop(0);
               }else{ 
                   alert_error(result.items);  
               }
               $(btn_loader).buttonLoader('stop'); 
            },'json').fail(function() {
    			setTimeout(function(){   
    				$(btn_loader).buttonLoader('stop');	
    		 	},1000);   
    		    alert_error(error_alert);
    		});   
        } 
    }else if(company=='no'){
        $('.tab-pane').removeClass('active');
        $('#tab2').addClass('active');  
        
        $(window).scrollTop(0); 
    } 
     
    $('#above-company').hide();
    
}); 
 
$('#btn-next-step2').click(function(){
    
    var customer_id = $('#customer_id').val();
    var name = $('#name').val(); 
    var email = $('#email').val(); 
	var phone_number = $('#phone_number').val(); 
	
	var above_name = $('#above_name').val();
        
    var above_position = $('#above_position').val();
    var above_company_name = $('#above_company_name').val();
    var above_company_address = $('#above_company_address').val();
    var above_company_website = $('#above_company_website').val(); 
           
    if(!name){
        alert(lang_alert.new_aler_enter_name);
        $('#name').focus();
    }else if(!validateEmail(email)){
        alert(lang_alert.new_aler_email); 
        $('#email').focus();  
    }else if(above_name == 2  && !above_position){
        alert('Please enter position.!'); 
        return false;  
    }else if(above_name == 2  && !above_company_name){
        alert('Please enter company name.!');
        return false;
    }else if(above_name == 2  && !above_company_address){
        alert('Please enter company address.!');
        return false;
    } 
    else{ 
        
        btn_loader = $(this);     
        $(btn_loader).buttonLoader('start'); 
	    var project_name = $('#project_name').val(); 
	    var project_price = $('#project_price').val();
	    var start_date = $('#start_date').val(); 
	    var end_date = $('#end_date').val(); 
	    var detail_note = $('#project_detail').val(); 
	    var contract_no = $('#project_contract_no').val();
	     
	    var who_pay_fee = $("input[type='radio']:checked").val();
	    var txt_pay_fee = $(".btn.btn-default.radio-box.active").text().trim();
	    
	    
        
	    if(customer_id==0){   
        	//var url = apiUrl+'profile/?'+user_api+'&'+password_api+'&act=new';
        	var url = base_url+'projectform/?act=newCustomer&r='+makeid();
            var data = {  
                'data[email]':email,  
                'data[name]':name,
                'data[phone_number]':phone_number,
                'data[above_position]':above_position, 
                'data[above_company_name]':above_company_name,
                'data[above_company_address]':above_company_address,
                'data[above_company_website]':above_company_website
            }; 
           
            $.post(url, data, function(result){
                if(result.status==200){  
                    $('#customer_id').val(result.items);
                    $('.tab-pane').removeClass('active'); 
                    $('#tab3').addClass('active');   
                    $(window).scrollTop(0); 
                }  
                $(btn_loader).buttonLoader('stop'); 
            },'json').fail(function() { 
    			setTimeout(function(){   
    				$(btn_loader).buttonLoader('stop');	
    		 	},1000);   
    		    alert_error(error_alert);
    		});    
	    }else{
	        
	        var url = base_url+'projectform/?act=editCustomer&r='+makeid();
            var data = {     
                'data[name]':name, 
                'data[phone]':phone_number,
                'data[above_position]':above_position, 
                'data[above_company_name]':above_company_name,
                'data[above_company_address]':above_company_address,
                'data[above_company_website]':above_company_website,
                'data[customer_id]':customer_id
            };  
           
            $.post(url, data, function(result){
                if(result.status==200){  
                    $(btn_loader).buttonLoader('stop');
                    $('.tab-pane').removeClass('active'); 
                    $('#tab3').addClass('active');   
                    $(window).scrollTop(0);
                }   
                $(btn_loader).buttonLoader('stop'); 
            },'json').fail(function() { 
    			setTimeout(function(){   
    				$(btn_loader).buttonLoader('stop');	
    		 	},1000);   
    		    alert_error(error_alert);
    		});     
	        /*
	        $(btn_loader).buttonLoader('stop');
            $('.tab-pane').removeClass('active'); 
            $('#tab3').addClass('active');   
            $(window).scrollTop(0); */
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
        if(phone_number){
            $('#detail-phone_number').html(phone_number);
        }else{ 
            $('#detail-phone_number').html('-');
        }
        $('#service-free').html(txt_pay_fee);
        if(!detail_note) detail_note = '-';
        $('#your-note').html(detail_note);  
         
        $('#detail-company-name').html($('#company_name').val());
        $('#detail-company-country').html($('#company_country').val());
        $('#detail-company-id').html($('#company_id').val());
        $('#detail-company-address').html($('#company_address').val());
        
        $('#detail-above_name').html('Pesronal'); 
        var company = $('#company').val();
        if(company=='yes'){  
            $('#company-block-detail').show();   
        }
        if(above_name==2){
            $('#detail-above_name').html('Business'); 
            $('#above_detail').show();     
            $('#detail-above_position').html(above_position); 
            $('#detail-above_company_name').html(above_company_name); 
            $('#detail-above_company_address').html(above_company_address); 
            $('#detail-above_company_website').html(above_company_website); 
        }else{
            $('#above_detail').hide();
        }
        
        var fee_price = 10; 
        
        $.each(service_feee, function(key, items){
            
            project_price = parseFloat(project_price);
            var fee_key = parseFloat(key.replace('fee', '')); 
            var items = parseFloat(items); 
            
            if(fee_key>project_price){
                if(items==10){    
                    fee_price = parseFloat(items);      
                }else{   
                    fee_price = parseFloat(project_price*items);   
                }    
                
                /*
                console.log([fee_key, project_price, fee_price, items]); 
                if(who_pay_fee!=2){   
                    $('#detail-total-fee').html(number_format(fee_price*2, 2));    
                    $('#detail-total-price').html(number_format((project_price+(fee_price*2)), 2));
                }else{   
                    $('#detail-total-fee').html(number_format(fee_price, 2));   
                    $('#detail-total-price').html(number_format((project_price+fee_price), 2));
                }
                */
                
                $('#detail-total-fee').html(number_format(fee_price, 2)); 
                $('#detail-total-price').html(number_format((project_price+fee_price), 2));
                
            } 
             
        });
        
	}
}); 

$('#btn-next-step3').click(function(){
    
    // if ($('.txt-policy').scrollTop() == $('.txt-policy')[0].scrollHeight - $('.txt-policy').height()) {
    //     gotoStep4(); 
    // }else{   
    //     setError('#error-policy', 'Please read the privacy policy.');    
    // }
    
    //gotoStep4(); 
    /* 
    if ($('.txt-policy')[0].scrollHeight >= $('.txt-policy').height()) {
        gotoStep4();     
    }else{
        
        $('#error-policy').();
        //$('#btn-next-step3').addClass('disabled');   
    }
    */
    
    if($('.check_policy').is(':checked')){      
        $('#error-policy').hide();     
        var project_name = $('#project_name').val();   
        $('#send_email_subject').val(project_name+' by wezenit.com');          
        $('#send_email_message').val(lang_alert.new_dear+' :'+$('#name').val()+' '+lang_alert.new_project_name+' : '+project_name); 
        $('#sendEmail').modal('show');      
    }else{     
        alert(lang_alert.new_aler_confirm_policy);   
    } 
    
});   

//$(".filestyle").filestyle();
var button_text = 'Choose file';
if(lang=='fr'){ 
   button_text = 'Choisir un fichier'; 
} 

$(window).on("load", function() { 
    $(".filestyle").filestyle('destroy');
    $(".filestyle").filestyle({'buttonText':button_text});
});
function sendEmailtoCustomer() 
{ 
   	var subject = $('#send_email_subject').val();
   	var message = $('#send_email_message').val();
   	if(!subject){ 
   		alert('Please enter subject !');
   		$('#send_email_subject').focus();   
   	}else if(!message){    
   		alert(lang_alert.new_aler_enter_message);
   		$('#send_email_message').focus();
   	}else{  
   		customerContract(); 
   	}    
}  

function customerContract()  
{ 
	var customer_id = $('#customer_id').val();
	var customer_type = $('#customer_type').val();
	var zenovly_type = $('#zenovly_type').val();
	var above_name = $('#above_name').val();
	
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
    
    var data ={}
    
	start_date = $.date(start_date); 
	end_date = $.date(end_date);         
	
	var above_position = $('#above_position').val();
	var above_company_name = $('#above_company_name').val();
	var above_company_address = $('#above_name').val();
	var above_company_website = $('#above_company_website').val();
	   
    if(customer_type==1){ // 1 = seller 
          
		var data ={ 
    	     'data[request]': customer_type,
    	     'data[zenovly_type]': zenovly_type,
    	     'data[company]': $('#company').val(),
    	     'data[above_name]':above_name,
    	     'data[seller_id]': id,
    	     'data[seller_name]': user_name,
    	     'data[seller_email]': user_email,
    	     'data[seller_number]': user_phone,
    	     'data[buyer_id]': customer_id,
    	     'data[buyer_name]': name,
    	     'data[buyer_email]': email,
    	     'data[buyer_number]': phone_number,
    	     'data[total_price]': project_price,
    	     'data[project_name]': project_name,
    	     'data[start_date]': start_date,
    	     'data[end_date]': end_date,
    	     'data[note]': detail_note,
    	     'data[contract_number]': contract_no,
    	     'data[who_pay_fee]': who_pay_fee,
    	     'data[contract_img]': base64_file1,
    	     'data[contract_img2]': base64_file2,
    	     'data[contract_img3]': base64_file3,
    	     'data[contract_img4]': base64_file4,
    	     'data[contract_img5]': base64_file5,
    	     'data[contract_img6]': base64_file6,
    	     'data[contract_img7]': base64_file7,
    	     'data[contract_img8]': base64_file8,
    	     'data[contract_img9]': base64_file9,
    	     'data[project_name]': project_name,
    	     'data[subject]': $('#send_email_subject').val(),
    	     'data[body]': $('#send_email_message').val()
    	 };
		
    }else{ // 0 = buyer 
        
		var data ={   
    	     'data[request]': customer_type,
    	     'data[zenovly_type]': zenovly_type,
    	     'data[company]': $('#company').val(),
    	     'data[above_name]':above_name,  
    	     'data[buyer_id]': id,
    	     'data[buyer_name]': user_name,
    	     'data[buyer_email]': user_email,
    	     'data[buyer_number]': user_phone,
    	     'data[seller_id]': customer_id,
    	     'data[seller_name]': name,
    	     'data[seller_email]': email, 
    	     'data[seller_number]': phone_number,  
    	     'data[total_price]': project_price,
    	     'data[project_name]': project_name,
    	     'data[start_date]': start_date,
    	     'data[end_date]': end_date, 
    	     'data[note]': detail_note,
    	     'data[contract_number]': contract_no,
    	     'data[who_pay_fee]': who_pay_fee,
    	     'data[contract_img]': base64_file1,
    	     'data[contract_img2]': base64_file2,
    	     'data[contract_img3]': base64_file3,
    	     'data[contract_img4]': base64_file4,
    	     'data[contract_img5]': base64_file5,
    	     'data[contract_img6]': base64_file6,
    	     'data[contract_img7]': base64_file7,
    	     'data[contract_img8]': base64_file8,
    	     'data[contract_img9]': base64_file9, 
    	     'data[project_name]': project_name,
    	     'data[subject': $('#send_email_subject').val(),
    	     'data[body': $('#send_email_message').val()
    	 };  
    }    
     
    
	 
	//var url = apiUrl+'makecontract/?'+user_api+'&'+password_api+'&act=4';  
	
	if(id && customer_id){ 
	    
	    var url = base_url+'projectform/?act=makeContracttomer&r='+makeid();
        $.post(url, data, function(result){
            if(result.status == 200)       
            {      
                $('#sendEmail').modal('hide'); 
				alert_success(result.items);   
				setTimeout(function(){    
		            $('.tab-pane').removeClass('active'); 
		            $('#tab4').addClass('active');  
		            $(window).scrollTop(0);   
				},1000);    
            }else{     
                alert_error(result.items); 
            }   
            
            $(btn_loader).buttonLoader('stop'); 
            
        },'json').fail(function() {
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	 
		 	},1000);    
		    alert_error(error_alert); 
		});   
		 
	}else{          
		alert(error_alert); 
		$(btn_loader).buttonLoader('stop');  
	} 
}  

$('#above_name').change(function(){
    var above = $(this).val();
    if(above==2){ 
        $('#above-company').show();
    }else{   
        //$('#above_position, #above_company_name, #above_company_address, #above_company_website').val('');
        $('#above-company').hide();  
    }
});  