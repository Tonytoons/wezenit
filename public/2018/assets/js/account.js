$("#leftside-navigation .sub-menu > a").click(function(e) {
  $("#leftside-navigation ul ul").slideUp(), $(this).next().is(":visible") || $(this).next().slideDown(),
  e.stopPropagation()
});



function editProfile() 
{     
	var name = $('#profile-name').val(); 
	var lname = $('#profile-lname').val(); 
	var email = $('#profile-email').val();
	var birthday = $('#profile-birthday').val();
	var gender = $('#profile-gender').val();
	var phone = $('#profile-phone').val();    
	var address = $('#profile-address').val();
	
	var nationality = $('#Nationality').val();
	var country = $('#CountryUser').val(); 
	
	var Cname = $('#company_name').val();    
	var Cemail = $('#company_email_supplier').val(); 
	var Caddress = $('#company_address').val(); 
	  
	//var birthday = $.date(birthday);   
	
	if(name.length < 2){   
		alert(lang_alert.new_aler_enter_name);   
		$('#profile-name').focus();   
	}else if(lname.length < 2){   
		alert('please enter last name!');   
		$('#profile-name').focus();   
	}else if (!validateEmail(email)) {
		alert(lang_alert.new_aler_enter_email); 
		$('#profile-email').focus(); 
	}else if(phone.length < 2){ 
		alert(lang_alert.new_aler_phone_number); 
		$('#profile-phone').focus(); 
	}else{    
		var url = base_url+'profile/?act=editProfile&r='+makeid();    
		var data = {
			"data[name]":name,
			"data[lastname]":lname,
			"data[email]":email, 
			"data[birth_day]":birthday,
			"data[gender]":gender,
			"data[phone]":phone,   
			"data[address]":address, 
			"data[nationality]":nationality,
			"data[country]":country,    
			"data[Cname]":Cname,         
			"data[Cemail]":Cemail,      
			"data[Caddress]":Caddress,   
			"data[City]":$('#profile-city').val(), 
			"data[Region]":$('#profile-region').val(), 
			"data[PostalCode]":$('#profile-postcode').val() 
			//"data[facebook_id]":fbID
		};     
		$.post(url, data, function(result){ 
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);    
			if(result.status==200){     
				alert_success(lang_alert.new_aler_edit_profile_ok);  
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

function editCompanyInfo() 
{    
	//var apiLink = apiUrl+"profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=companyUpdate";  
	
	var company_name = $('#company_name').val(); 
	var company_address = $('#company_address').val();
	var company_mobile_phone = $('#company_mobile_number').val();
	var company_phone = $('#company_landline_number').val(); 
	var company_email = $('#company_email_supplier').val(); 
	var company_country = $('#company_country').val();
	var company_id = $('#company_id').val();
	
	if(company_name.length < 2){    
		alert(lang_alert.new_aler_company_name);   
		$('#company_name').focus();    
	}else if(!company_country){  
       alert(lang_alert.new_aler_country_incorporation); 
       $('#company_country').focus();
    }else if(!company_id){ 
       alert(lang_alert.new_aler_id_number_company);  
       $('#company_id').focus();   
    }else if(company_address.length < 2){    
		alert(lang_alert.new_aler_company_addr);   
		$('#company_address').focus();    
	}else if(company_mobile_phone.length < 7){    
		alert(lang_alert.new_aler_mobile_phone);    
		$('#company_mobile_number').focus();     
	}else if (!validateEmail(company_email)) {
		alert(lang_alert.new_aler_email_sup); 
		$('#company_email_supplier').focus(); 
	}else{      
		var url = base_url+'profile/?act=editCompanyInfo&r='+makeid();     
		var data = { 
			"data[company_name]":company_name,
			"data[company_address]":company_address,
			"data[company_mobile_phone]":company_mobile_phone,
			"data[company_phone]":company_phone, 
			"data[company_email]":company_email,
			"data[company_id]":company_id, 
			"data[company_country]":company_country, 
			"data[company_city]":$('#company_city').val(),
			"data[company_region]":$('#company_region').val(),
			"data[company_postcode]":$('#company_postcode').val() 
		};        
		$.post(url, data, function(result){ 
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);    
			if(result.status==200){      
				alert_success(lang_alert.new_aler_edit_ok);   
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

function addBank(){ 
	var mangopay_id = $('#mangopay_id').val();
	var OwnerName = $('#OwnerName').val();
	var type = $('#Type').val();
	var Address = $('#Address').val();
	var City = $('#City').val();
	var Region = $('#Region').val();
	var PostalCode = $('#PostalCode').val();
	var Country = $('#Country').val();
	
	var url = base_url+'profile/?act=addBank&mangopay_id='+mangopay_id+'&r='+makeid();     
	var data = {
		"data[Mangopayid]":mangopay_id, 
		"data[OwnerName]":OwnerName,  
		"data[Type]":type,
		"data[Address]":Address, 
		"data[City]":City, 
		"data[Region]":Region,
		"data[PostalCode]":PostalCode,
		"data[Country]":Country
	};      
	
	if(type=='GB'){   
		
		var AccountNumber = $('#AccountNumber').val();
		var SortCode = $('#SortCode').val(); 
		if(AccountNumber.length < 2){
			alert(lang_alert.new_aler_account_number);
			return false;		
		}else if(SortCode.length < 2){
			alert(lang_alert.new_aler_sort_code);
			return false;
		}
		data["data[AccountNumber]"] = AccountNumber;
		data["data[SortCode]"] = SortCode;
		
	}else if(type=='US'){ 
		/* $('#input-AccountNumber ,#input-ABA ,#input-DepositAccountType').show(); */
		var AccountNumber = $('#AccountNumber').val();
		var ABA = $('#ABA').val(); 
		var DepositAccountType = $('#DepositAccountType').val(); 
		if(AccountNumber.length < 2){
			alert(lang_alert.new_aler_account_number);
			return false;		
		}else if(ABA.length < 2){
			alert('ABA.!');
			return false;
		}else if(DepositAccountType.length < 2){ 
			alert(lang_alert.new_aler_deposit_account_type);
			return false;
		}
		data["data[AccountNumber]"] = AccountNumber;
		data["data[ABA]"] = ABA; 
		data["data[DepositAccountType]"] = DepositAccountType; 
		
	}else if(type=='CA'){
		/* $('#input-AccountNumber ,#input-BranchCode ,#input-BankName ,#input-InstitutionNumber').show(); */
		var AccountNumber = $('#AccountNumber').val();
		var BranchCode = $('#BranchCode').val(); 
		var BankName = $('#BankName').val();
		var InstitutionNumber = $('#InstitutionNumber').val();
		if(AccountNumber.length < 2){
			alert(lang_alert.new_aler_account_number);
			return false;		
		}else if(BranchCode.length < 2){ 
			alert(lang_alert.new_aler_branch_code);
			return false;
		}else if(BankName.length < 2){   
			alert(lang_alert.new_aler_bank_name);
			return false;
		}else if(InstitutionNumber.length < 2){   
			alert(lang_alert.new_aler_institution_number);
			return false;
		}
		data["data[AccountNumber]"] = AccountNumber;
		data["data[BranchCode]"] = BranchCode; 
		data["data[BankName]"] = BankName; 
		data["data[InstitutionNumber]"] = InstitutionNumber;
		 
	}else if(type=='OTHER'){
		
		/* $('#input-AccountNumber ,#input-BIC').show(); */
		var AccountNumber = $('#AccountNumber').val();
		var BIC = $('#BIC').val(); 
		if(AccountNumber.length < 2){
			alert(lang_alert.new_aler_account_number);
			return false;		
		}else if(BIC.length < 2){
			alert(lang_alert.new_aler_bic_code); 
			return false;
		}
		data["data[AccountNumber]"] = AccountNumber;
		data["data[BIC]"] = BIC;  
		
	}else{  
		 
		/* $('#input-IBAN ,#input-BIC').show(); */
		var IBAN = $('#IBAN').val(); 
		var BIC = $('#BIC').val(); 
		if(IBAN.length < 2){
			alert(lang_alert.new_aler_ibank_acc); 
			return false; 		
		}else if(BIC.length < 2){
			alert(lang_alert.new_aler_bic_code); 
			return false;
		} 
		data["data[IBAN]"] = IBAN;
		data["data[BIC]"] = BIC; 
		
	}
	
	//console.log(data);  
	
	if(OwnerName.length < 2){
		alert(lang_alert.new_aler_owner_name);  
	}else if(type.length < 2){
		alert(lang_alert.new_aler_enter_type);  
	}else{
		$.post(url, data, function(result){ 
			  
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);    
			if(result.status==200){ 
				$('#form-bank').trigger("reset"); 
				banklist();     
				alert_success(lang_alert.new_aler_add_bank_ok);   
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
	/*
    var mangopay_id = $('#mangopay_id').val();  
    var iban_account = $('#iban_account').val(); 
    var userbnak_name = $('#userbnak_name').val();
    var bic_code = $('#bic_code').val();
    var userbank_address = $('#userbank_address').val();   
    if(mangopay_id.length < 2){     
		alert('Please enter ibank account!');     
		$('#mangopay_id').focus();      
	}else if(iban_account.length < 2){    
		alert('Please enter bank account name!');   
		$('#iban_account').focus();      
	}else if(bic_code.length < 2){     
		alert('Please enter bic code!');    
		$('#bic_code').focus();      
	}else if(userbank_address.length < 2){      
		alert('Please enter bank address!');    
		$('#userbank_address').focus();     
	}else{     
		//var url = apiUrl+'addbank/'+uid+'/?'+user_api+'&'+password_api+'&mangopay_id='+mangopay_id+'&iban='+iban_account+'&uname='+userbnak_name+'&bic='+bic_code+'&address='+userbank_address; 
    
	    
		$.post(url, data, function(result){ 
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);    
			if(result.status==200){     
				alert_success('Successfuly');   
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
	*/
} 

function newPassword()  
{ 
	var error = 0; 
	//$('#result').html('');  
	//var apiLink = apiUrl+"profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=changePassword";  
	var oldpass = $('#profile-oldpassword').val();  
	var newpass = $('#profile-newpass').val();  
	var confpass = $('#profile-confnewpass').val();   
	
	if((oldpass.length < 6) || (oldpass.length > 15)){   
		alert(lang_alert.new_aler_old_pass); 
		$('#profile-oldpassword').focus();	
		return false;
	}else if ((newpass.length < 6) || (newpass.length > 15)) { 
		alert(lang_alert.new_aler_new_pass); 
		$('#profile-newpass').focus();	
		return false; 
	}else if(newpass != confpass){        
		alert(lang_alert.new_aler_confirm_new_pass);  
		$('#profile-confnewpass').val('');
		$('#profile-confnewpass').focus();	 
		return false; 
	}else{        
		//apiLink +='&udpassword='+oldpass+"&upassword="+newpass;
	    var url = base_url+'profile/?act=changePassword&r='+makeid();     
		var data = { 
			"data[udpassword]":oldpass,    
			"data[upassword]":newpass
		};           
		$.post(url, data, function(result){  
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);    
			if(result.status==200){     
				$('#profile-oldpassword,#profile-newpass,#profile-confnewpass').val('');
				alert_success(result.items);     
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
	
	var html = '<tr><td colspan="8" class="text-center">'+lang_alert.new_aler_loadding+'</td></tr>';  
	$('#contract-list').html(html);      
	
    //var apiLink = apiUrl+"contract/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act="+action+"&status="+status+'&page='+page+'&rd='+makeid();
    var url = base_url+'profile/?act='+action+'&r='+makeid();
    if(action=='consumer' || action=='supplier'){ 
    	url = base_url+action+'/?act=getData&r='+makeid(); 
    } 
    var data = {  
			"data[status]":status,
			"data[page]":page,
			"data[act]":action
		}; 
    $.post(url, data, function(result){    
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
			 	if(action=='consumer' || action=='supplier'){
			 		html +='<td>'+item.contract_company+'</td>';  
			 	} 
			 	html +='<td>'+number_format(item.total_price,2)+'</td>';   
			 	html +='<td>'+toDate(item.start_date)+'</td>';   
			 	html +='<td>'+toDate(item.end_date)+'</td>'; 
			 	html +='<td>'+ar_status[item.status]+'</td>';
			 	html +='<td>'; 
			 	html +='<a class="btn btn-info btn-sm" target="_blank" href="'+base_url+"contractinfo/"+item.id+"/"+'?r='+makeid()+'&ck='+action+'"><i class="fa fa-eye"></i>  '+lang_alert.new_aler_detail+'</a>';  
			 	if(action=='buyer' && item.clickNotGet==1){                 
			 	    html +=' <button class="btn btn-warning btn-sm" onclick="statusNotItem(this,'+item.id+');"><i class="fa fa-exclamation"></i>  '+lang_alert.new_aler_no_item+'</button>';   
			 	}
			 	if(action=='buyer' && item.status==5){                
			 	    html +=' <button class="btn btn-success btn-sm" onclick="statusDone(this,'+item.id+');"><i class="fa fa-play-circle-o"></i>  '+lang_alert.new_aler_done+'</button>';   
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
        	html = '<tr><td colspan="8" class="text-center">'+result.items+'</td></tr>'; 
        }  
        $('#contract-list').html(html);  
    },'json').fail(function() {  
		$('#contract-list').html('');
	});  
} 

function getPayoutList()    
{ 
	
	$('#pagination').twbsPagination('destroy'); 
	var html = '<tr><td colspan="6" class="text-center">'+lang_alert.new_aler_loadding+'</td></tr>';  
	$('#payout-list').html(html);      
	
    //var apiLink = apiUrl+"contract/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act="+action+"&status="+status+'&page='+page+'&rd='+makeid();
    var url = base_url+'mywallets/?act=payoutlist&r='+makeid();
    var data = {   
			"data[page]":page,
			"data[act]":'payoutlist'
		}; 
    $.post(url, data, function(result){   
    	//console.log(result);
		html = '';  
		$('#payout-list').html(html);      
		if(result.status==200)         
        {   
		 	var no_rows = 1;  
		 	if(page>1){
		 		no_rows = (page*perpage)-(perpage+1); 
		 	}     
		 	$.each(result.items, function(i, item) {
		 		html +='<tr>';    
		 		html +='<td>'+item.result_id+'</td>';
			 	html +='<td>'+number_format(item.amount, 2)+'</td>'; 
			 	html +='<td>'+item.createdate+'</td>';    
			 	html +='<td>'+item.lastupdate+'</td>';
			 	html +='<td>'+item.status+'</td>';  
			 	html +='<td>'+item.result+'</td>';
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
			        	getPayoutList(); 
			        }   
			    });
			} 
        }else{   
        	html = '<tr><td colspan="6" class="text-center">'+result.items+'</td></tr>'; 
        }  
        $('#payout-list').html(html);  
    },'json').fail(function() {   
		$('#payout-list').html('');
	});  
} 

function getByStatusPro(status)     
{ 
	if(status_def!=status)
	{    
		page=1;  
		status_def = status; 
		$('#pagination').twbsPagination('destroy'); 
	}  
	
	var html = '<tr><td colspan="8" class="text-center">'+lang_alert.new_aler_loadding+'</td></tr>';  
	$('#contract-list').html(html);      
	
    //var apiLink = apiUrl+"contract/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act="+action+"&status="+status+'&page='+page+'&rd='+makeid();
    var url = base_url+action+'/?act=getData&r='+makeid();  
    var data = {    
			"data[status]":status,  
			"data[act]":action,   
			"data[page]":page
		}; 
    $.post(url, data, function(result){    
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
			 	html +='<td>'+ar_status[item.status]+'</td>';
			 	html +='<td>';  
			 	html +='<a class="btn btn-info btn-sm" target="_blank" href="'+base_url+"contractinfo/"+item.id+"/"+'"><i class="fa fa-eye"></i>  Detial</a>';  
			 	if(action=='buyer' && item.clickNotGet==1){                 
			 	    html +=' <button class="btn btn-warning btn-sm" onclick="statusNotItem(this,'+item.id+');"><i class="fa fa-exclamation"></i>  I didn\'t get item</button>';   
			 	}
			 	if(action=='buyer' && item.status==5){                
			 	    html +=' <button class="btn btn-success btn-sm" onclick="statusDone(this,'+item.id+');"><i class="fa fa-play-circle-o"></i>  Done</button>';   
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
        	html = '<tr><td colspan="8" class="text-center">'+result.items+'</td></tr>'; 
        }  
        $('#contract-list').html(html); 
    },'json').fail(function() {  
		$('#contract-list').html('');
	});  
} 

function statusDone(self, cid){      
    //var url = apiUrl+'done/'+cid+'/?'+user_api+'&'+password_api+'&sstatus=3';   
    //console.log(url); 
    //var url = base_url+'profile/?act=done&cid='+cid+'&sstatus=3&r='+makeid();
    var tt = $(self).html(); 
    $(self).html('<span class="spinner"><i class="fa fa-spinner fa-spin" title="button-loader"></i></span> '+lang_alert.new_aler_loadding+''); 
    $(self).attr('disabled','disabled'); 
    var url = base_url+'profile/?act=done&r='+makeid();
    var data = {  
			"data[sstatus]":3,  
			"data[cid]":cid 
		};
    $.post(url, data, function(result){       
        console.log(result);    
        if(result.status==200){     
            getByStatus(status_def);  
        } 
        $(self).html(tt); 
        $(self).removeAttr('disabled');
    },'json'); 
}

function statusNotItem(self, cid){        
    //var url = apiUrl+'done/'+cid+'/?'+user_api+'&'+password_api+'&sstatus=4';   
    //console.log(url); 
    //var url = base_url+'profile/?act=done&cid='+cid+'&sstatus=4&r='+makeid(); 
    var tt = $(self).html(); 
    $(self).html('<span class="spinner"><i class="fa fa-spinner fa-spin" title="button-loader"></i></span> '+lang_alert.new_aler_loadding+''); 
    $(self).attr('disabled','disabled');  
    var url = base_url+'profile/?act=done&r='+makeid();
    var data = {  
			"data[sstatus]":4,  
			"data[cid]":cid  
		};   
	
    $.post(url, data, function(result){    
        console.log(result);   
        if(result.status==200){     
            getByStatus(status_def);  
        }   
        $(self).html(tt); 
        $(self).removeAttr('disabled');
    },'json');   
}

var imgW; 
var imgH;

$(function(){ 
	if(action=='seller' || action=='buyer' || action=='consumer' || action=='supplier'){
        getByStatus('all'); 
    } 
    /*
    if(action=='consumer' || action=='supplier'){ 
    	getByStatusPro(0); 
    }*/
});    
 
var imagOG = '';  
 
$("#upimage").change(function(){
	  
	$('#og-profile').croppie('destroy');
	
	imgW = $('#og-profile').width();
	imgH = $('#og-profile').height();  
	 
	imagOG = $("#og-profile").attr('src');
	
	getBase64(this, function(rs){ 
		
		$("#og-profile").attr('src', rs).show();    
		$("#btn-crop-img").show();  
		
		setTimeout(function(){
			
			$('#og-profile').croppie({    
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
			$("#upimage").val(''); 
		},1000); 
		
	}); 
}); 

$(".btn-cancel").click(function(){ 
	$("#upimage").val('');
	$("#og-profile").attr('src', imagOG);
	$("#btn-crop-img").hide();   
	$('#og-profile').croppie('destroy'); 
});  

$(".btn-done").click(function(){
	
	$('#og-profile').croppie('result', {   
		type: 'canvas', 
		size: 'viewport'
	}).then(function (resp) { 
		//var apiLink = apiUrl+"profile/"+uid+"/?username=RockStar&password=Um9ja1N0YXI=&act=imgPF";
		if(resp){          
		    var url = base_url+'profile/?act=imgPF&r='+makeid();     
			var data = {      
				"data[img]":resp  
			};           
			$.post(url, data, function(result){  
				setTimeout(function(){   
					$(btn_loader).buttonLoader('stop');	
			 	},1000);    
				if(result.status==200){      
					alert_success(result.items);     
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
			alert_error('Not base64 file.'); 
			return false; 
		} 
		$("#upimage").val(''); 
		$("#og-profile").attr('src', resp);
		$("#btn-crop-img").hide();   
		$('#og-profile').croppie('destroy'); 
	});  
	
});


$("#Type").change(function() {
	$('#input-AccountNumber,#input-ABA ,#input-DepositAccountType ,#input-SortCode ,#input-BranchCode ,#input-BankName ,#input-InstitutionNumber ,#input-IBAN ,#input-BIC').hide();
	$('#AccountNumber,#ABA ,#SortCode ,#BranchCode ,#BankName ,#InstitutionNumber ,#IBAN ,#BIC').val(''); 
	var type = $(this).val(); 
	if(type=='GB'){  
		$('#input-AccountNumber ,#input-SortCode').show(); 
	}else if(type=='US'){
		$('#input-AccountNumber ,#input-ABA ,#input-DepositAccountType').show(); 
	}else if(type=='CA'){
		$('#input-AccountNumber ,#input-BranchCode ,#input-BankName ,#input-InstitutionNumber').show();
	}else if(type=='OTHER'){
		$('#input-AccountNumber ,#input-BIC').show();
	}else{  
		$('#input-IBAN ,#input-BIC').show();
	}
});

function banklist(){      
    var url = base_url+'profile/?act=BankList&r='+makeid();
    var html = '';  
	$('#bank-list').html(html);  
    $('#bank-list').html('<tr><td colspan="6" class="text-center"><span class="spinner"><i class="fa fa-spinner fa-spin" title="button-loader"></i></span> '+lang_alert.new_aler_loadding+'<td></tr>'); 
   
    var nodata = 'No data';
    if(lang=='fr'){
    	nodata = 'Pas de données';
    }
    $.get(url, function(result){ 
    	 console.log(result); 
    	      
		if(result.Status==200)         
        {    
        	var cc = 0;
        	if(result.result.length>0){
			 	$.each(result.result, function(i, item) {
			 		if(item.Active){ 
				 		html +='<tr>';    
				 		html +='<td>'+(cc+1)+'</td>';
				 		html +='<td>'+item.Type+'</td>';  
				 		html +='<td>'+item.OwnerName+'</td>'; 
				 		
				 		if(item.Type=='IBAN'){  
						 	html +='<td>'+item.Details.IBAN+'</td>'; 
						 	html +='<td>'+item.OwnerAddress.Country+'</td>';
				 		}else if(item.Type=='GB'){  
						 	html +='<td>'+item.Details.AccountNumber+'</td>'; 
						 	html +='<td>'+item.OwnerAddress.Country+'</td>';
				 		}else{  
				 			html +='<td>'+item.Details.AccountNumber+'</td>'; 
						 	html +='<td>'+item.Details.Country+'</td>';
				 		}
				 		/*
					 	if(item.Active){ 
					 		html +='<td>Enable</td>';
					 	}else{ 
					 		html +='<td>Disable</td>';
					 	} */  
					 	html +='<td><button class="btn btn-warning btn-sm" onclick="deactivate(this,'+item.Id+');"><i class="fa fa-trash"></i></button></td>'; 
					 	html +='<tr>'; 
					 	cc++; 
			 		}
			 		//console.log([i,cc]);
			 		if(result.result.length==(i+1) && cc==0){ 
			 			html = '<tr><td colspan="6" class="text-center">'+nodata+'</td></tr>'; 
			 		}
				});  
        	}else{   
        		html = '<tr><td colspan="6" class="text-center">'+nodata+'</td></tr>'; 
        	} 
        }else{      
        	html = '<tr><td colspan="6" class="text-center">'+result.Message+'</td></tr>'; 
        }  
        $('#bank-list').html(html); 
    },'json');    
}
function deactivate(self, bid){ 
	var tt = $(self).html();  
    $(self).html('<span class="spinner"><i class="fa fa-spinner fa-spin" title="button-loader"></i></span> '+lang_alert.new_aler_loadding+''); 
    $(self).attr('disabled','disabled'); 
     
    var url = base_url+'profile/?act=BankDeactivate&bid='+bid+'&r='+makeid();
    $.get(url, function(result){   
    	 //console.log(result);  
    	 banklist();  
    },'json');    
}



var kycFile = '';

$("#kycfile").change(function(){
	getBase64(this, function(rs){ 
		kycFile = rs;
	}); 
}); 

function kycUpload(){ 
	var url = base_url+'profile/?act=uploadKYC&r='+makeid();
	var type = $('#KYCType').val();
	var file = $('#kycfile').val();
	if(file !='' && kycFile !=''){ 
		var data = {'file':kycFile, 'type':type};
		$.post(url, data, function( results ) { 
			console.log(results); 
			
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000); 
		 	
			if(results.status==200){   
				$('#form-kyc')[0].reset();  
				alert_success('KYC documents upload successful');       
			}else{         
				alert_error(results.items);   
			}   
			KYClist();
			return false;  
		},'json').fail(function() {
			setTimeout(function(){   
				$(btn_loader).buttonLoader('stop');	
		 	},1000);   
		    alert_error(error_alert);
		}); 
	}else{
		setTimeout(function(){   
			$(btn_loader).buttonLoader('stop');	
	 	},1000);   
	 	alert_error('Not file.');
	}
}

function KYClist(){      
    var url = base_url+'profile/?act=KYClist&r='+makeid();
    var html = '';  
	$('#KYC-list').html(html);   
    $('#KYC-list').html('<tr><td colspan="5" class="text-center"><span class="spinner"><i class="fa fa-spinner fa-spin" title="button-loader"></i></span> '+lang_alert.new_aler_loadding+'<td></tr>'); 
   
    var nodata = 'No data';
    if(lang=='fr'){
    	nodata = 'Pas de données';
    }
    $.get(url, function(result){ 
    	 console.log(result); 
    	      
		if(result.Status==200)         
        {    
        	var cc = 0;
        	if(result.result.length>0){
			 	$.each(result.result, function(i, item) {
			 		html +='<tr>';    
			 		html +='<td>'+(cc+1)+'</td>'; 
			 		html +='<td>'+item.Id+'</td>'; 
			 		html +='<td>'+item.Type.replace('_',' ').toLowerCase()+'</td>';  
			 		html +='<td>'+item.Status.replace('_',' ').toLowerCase()+'</td>';
			 		html +='<td>'+item.createdate+'</td>';
				 	html +='<tr>'; 
				 	cc++; 
				});  
        	}else{   
        		html = '<tr><td colspan="5" class="text-center">'+nodata+'</td></tr>'; 
        	} 
        }else{       
        	html = '<tr><td colspan="5" class="text-center">'+nodata+'</td></tr>'; 
        }  
        $('#KYC-list').html(html);  
    },'json');    
}

window.onload = function() {
	if(action=='profile'){
		banklist(); 
		KYClist();
	}else if(action=='mywallets'){
		getPayoutList();
	}
}