/********* step project form ********/
$('#btn-next-step1').click(function(){  
    
    $('#project_form').valid();   
    /* 
	$.each( base64_array, function( key, value ) { 
	    if(key==0)base64_file1 = value.result;
	    if(key==1)base64_file2 = value.result;
	    if(key==2)base64_file3 = value.result;
	    if(key==3)base64_file4 = value.result;
	    if(key==4)base64_file5 = value.result;
	    if(key==5)base64_file6 = value.result;
	    if(key==6)base64_file7 = value.result;
	    if(key==7)base64_file8 = value.result;
	    if(key==8)base64_file9 = value.result;
    }); */     
    
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