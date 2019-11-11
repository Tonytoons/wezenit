$(function(){ 
   var step = 0;  
   if(action=='supplierform'){
        $('#btn-next-step1').click(function(){
            var customer = $('#customer').val();
            if(customer==0){    
                $("#supplierform1").valid();     
                if($("#supplierform1").valid()){
                    newCustomer();    
                }else{
                    $(btn_loader).buttonLoader('stop');
                } 
            }else{
                $('#customer_id').val(customer);   
    		    $('#tab-step2').addClass('process-active active');
    	        $('.tab-pane').removeClass('active'); 
    	        $('#tab2').addClass('active'); 
    	        $(window).scrollTop(0);         
    	        $('#for-customer-name').html($('#customer option:selected').text().trim()); 
    	        $('#detail-customer-name').html($('#customer option:selected').text().trim()); 
    	        $(btn_loader).buttonLoader('stop'); 
            } 
        });    
        
        $('#btn-next-step2').click(function(){
            $("#supplierform2").valid();      
            if($("#supplierform2").valid()){
                
               $('#tab-step3').addClass('process-active active');
               $('.tab-pane').removeClass('active'); 
               $('#tab3').addClass('active'); 
               $(window).scrollTop(0);     
               
               $('#detail-projectname').html($('#project_name').val()); 
               $('#detail-sub-price').html(number_format($('#project_price').val(),2));  
               $('#detail-total-price').html(number_format($('#project_price').val(),2)); 
               $('#detail-start-date').html($('#start_date').val());  
               $('#detail-end-date').html($('#end_date').val());
               $('#detail-sireal_number').html($('#sireal_number').val());
               $('#detail-company_name').html($('#billing_company_name').val()); 
               $('#billing_company_email').html($('#billing_company_email').val());
               $('#detail-name').html($('#billing_full_name').val());
               $('#detail-company_address').html($('#billing_company_address').val()); 
               $('#detail-company_mobile_phone').html($('#billing_mobile_number').val());
               $('#detail-company_phone').html($('#billing_company_phone').val());  
               
               if($('.check_policy').is(':checked')){
               }else{
               } 
            } 
       }); 
        
       $('#btn-next-step3').click(function(){ 
           if($('.check_policy').is(':checked')){  
                $('#error-policy').hide();  
                var project_name = $('#project_name').val();   
                $('#send_email_subject').val(project_name+' by zenovly.com');        
       	        $('#send_email_message').val('Dear '+$('#full_name').val()+' Project Name : '+project_name); 
                $('#sendEmail').modal('show');  
            }else{    
                setError('#error-policy',txt_lang.confirm_policy);  
            }
       });    
       if(action=='supplierform'){ 
           
           $('.check_policy').change(function(){
                if($('.check_policy').is(':checked')){ 
                    $('#btn-send-email').removeClass('disabled'); 
                }else{  
                    $('#btn-send-email').addClass('disabled'); 
                }  
           });
            
           $('#customer').change(function(){
                var customer_id = $(this).val();   
                var customer_name = $('#customer option:selected').text().trim();
                if(customer_id!=0){    
                    $('#customer_id').val(customer_id); 
        		    $('#tab-step2').addClass('process-active active');
        	        $('.tab-pane').removeClass('active'); 
        	        $('#tab2').addClass('active'); 
        	        $(window).scrollTop(0);     
        	        $('#for-customer-name').html(customer_name);
        	        $('#detail-customer-name').html(customer_name); 
                } 
           });
       } 
   }else if(action=='contract'){ 
       
        $('#btn-next-step0').click(function(){  
            $('html, body').animate({     
                scrollTop: $("#login-form").offset().top-160
            }, 1000);    
            $('#login-password').focus(); 
       }); 
       
       $('#btn-next-step1').click(function(){   
            if($('.check_policy').is(':checked')){ 
                $('#tab-step2').addClass('process-active active');
                $('.tab-pane').removeClass('active'); 
                $('#tab2').addClass('active');   
                $(window).scrollTop(0); 
            }else{   
                setError('#error-policy',txt_lang.confirm_policy); 
            }   
       });
        
       
       $('#btn-next-step2').click(function(){  
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
    
   $('#btn-prev-step4').click(function(){ 
       $('#tab-step4').removeClass('process-active active'); 
       $('.tab-pane').removeClass('active'); 
       $('#tab3').addClass('active'); 
       $(window).scrollTop(0);
   });
});

$("#supplierform1").validate({   
    rules: {
        email: { 
          required: true, 
          email: true  
        },
        full_name:"required", 
    },
    messages: { 
        full_name:txt_lang.alert_full_name, 
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
        }
    } 
});
 
$("#supplierform2").validate({     
    rules: {
        attachfile: {    
           required: true,  
           extension: "pdf|jpg|jpeg|png|gif"
        },
        project_name:"required", 
        project_price:"required",  
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
        project_price:txt_lang.alert_price,  
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