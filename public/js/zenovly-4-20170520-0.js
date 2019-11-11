function newCustomer() 
{   
	var cid = $('#customer').val(); 
	if(cid==0){      
		var apiLink = apiURL+'/'+lang+"/profile/?username=RockStar&password=Um9ja1N0YXI=&act=new";    
		var id = 0; 
	   	var formData = new FormData();        
		formData.append('name', $('#full_name').val()); 
		formData.append('email', $('#email').val());
	    axios.post(apiLink, formData).then(function (response) {   
			var result = response.data; 
			if(result.status == 200)      
		    {     
		    	$('#customer_id').val(result.items); 
			    $('#tab-step2').addClass('process-active active');
		        $('.tab-pane').removeClass('active'); 
		        $('#tab2').addClass('active'); 
		        $(window).scrollTop(0);     
		        $('#for-customer-name').html($('#full_name').val());
		        $('#detail-customer-name').html($('#full_name').val()); 
		    } 
		    $(btn_loader).buttonLoader('stop');
		});    
	}else{ 
		$('#customer_id').val(cid); 
	    $('#tab-step2').addClass('process-active active');
        $('.tab-pane').removeClass('active'); 
        $('#tab2').addClass('active'); 
        $(window).scrollTop(0);    
        $('#for-customer-name').html($('#full_name').val());
        $('#detail-customer-name').html($('#full_name').val()); 
        $(btn_loader).buttonLoader('stop');
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
	var project_name = $('#project_name').val();
	var price = $("#project_price").val();
	var start_date = $('#start_date').val();
	var end_date = $('#end_date').val();
	var name = $('#billing_full_name').val(); 
	var serial = $("#sireal_number").val();
	var company = $('#billing_company_name').val();
	var address = $('#billing_company_address').val();
	var phone1 = $('#billing_mobile_number').val();
	var phone2 = $('#billing_company_phone').val();
	var email = $("#billing_company_email").val();
	
	var apiLink = apiURL+'/'+lang+"/makecontract/?username=RockStar&password=Um9ja1N0YXI=&act=2";  
	if(base64_file && uid && customer_id){         
		var formData = new FormData();  
		start_date = $.date(start_date); 
		end_date = $.date(end_date);     
		formData.append('user_id', customer_id);     
		formData.append('supplier_id', uid);    
		formData.append('total_price', price);
		formData.append('start_date', start_date);
		formData.append('end_date', end_date);
		formData.append('serial_number', serial);
		formData.append('contract_name', name);
		formData.append('contract_company', company);
		formData.append('company_address', address);
		formData.append('contract_phone', phone1);
		formData.append('contract_landline_phone', phone2);
		formData.append('contract_email', email);     
		formData.append('contract_img', base64_file);    
		formData.append('project_name', project_name);  
		formData.append('subject', $('#send_email_subject').val()); 
		formData.append('body', $('#send_email_message').val());  
    	axios.post(apiLink, formData).then(function (response) {
    		var result = response.data;  
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
            
		}).catch(function (error) {  
			setError('#contact-result',error);
			$(btn_loader).buttonLoader('stop'); 
		});   
	}else{      
		setError('#contact-result', txt_error);
		$(btn_loader).buttonLoader('stop'); 
	}
}
 
var status_def = 1;  

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
    var apiLink = apiURL+'/'+lang+"/contract/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act="+action+"&status="+status+'&page='+page;
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
			 	html +='<td>'+item.contract_company+'</td>';    
			 	html +='<td>'+number_format(item.total_price,2)+'</td>'; 
			 	html +='<td>'+toDate(item.start_date)+'</td>';   
			 	html +='<td>'+toDate(item.end_date)+'</td>';          
			 	html +='<td><a class="btn btn-info btn-xs" target="_blank" href="'+baseURL+''+lang+"/contractdetail/"+item.id+"/"+'"><i class="fa fa-eye"></i>  Detial</a></td>';   
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

function userNewpassword()  
{   
	var cid = $('#customer').val(); 
	if(cid==0){      
		var apiLink = apiURL+'/'+lang+"/profile/?username=RockStar&password=Um9ja1N0YXI=&act=new";    
		var id = 0; 
	   	var formData = new FormData();        
		formData.append('name', $('#full_name').val()); 
		formData.append('email', $('#email').val());
	    axios.post(apiLink, formData).then(function (response) {   
			var result = response.data; 
			if(result.status == 200)      
		    {     
		    	$('#customer_id').val(result.items); 
			    $('#tab-step2').addClass('process-active active');
		        $('.tab-pane').removeClass('active'); 
		        $('#tab2').addClass('active'); 
		        $(window).scrollTop(0);     
		        $('#for-customer-name').html($('#full_name').val());
		        $('#detail-customer-name').html($('#full_name').val()); 
		    } 
		    $(btn_loader).buttonLoader('stop');
		});    
	}else{ 
		$('#customer_id').val(cid); 
	    $('#tab-step2').addClass('process-active active');
        $('.tab-pane').removeClass('active'); 
        $('#tab2').addClass('active'); 
        $(window).scrollTop(0);    
        $('#for-customer-name').html($('#full_name').val());
        $('#detail-customer-name').html($('#full_name').val()); 
        $(btn_loader).buttonLoader('stop');
	} 
}

function customerNewpassword()
{     
	$('#login-error').html(''); 
	var email = $('#login-email').val();   
	var pass = $('#login-password').val();  
	var apiLink = apiURL+'/'+lang+"/profile/?username=RockStar&password=Um9ja1N0YXI=&act=changePasswordByEmail";  
	if(pass.length >= 5){      
		apiLink += '&email='+email+'&upassword='+pass;    
	    axios.get(apiLink).then(function (response) {     
    		var result = response.data;  
    		if(result.status == 200)    
            {         
			 	setsSuccess('#login-error', txt_lang.alert_password_successfuly); 
			 	userLogin();        
            }  
            else      
            { 
                setError('#login-error', txt_error); 
                $('#login-password').focus();      
            }   
            $(btn_loader).buttonLoader('stop');
		}).catch(function (error) {   
			setError('#login-error',error);  
			$(btn_loader).buttonLoader('stop');    
		});    
	}else{   
		setError('#login-error', txt_lang.alert_password_long);
		$('#login-password').focus();   
	} 
	return false; 
}