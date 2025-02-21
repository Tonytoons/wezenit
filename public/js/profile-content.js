function profileContent(content){
	app.loader=true;
	var html = '';
	app.profileContent=false;
	app.dashboardContent=false;
	app.newpassContent=false;
	window.scrollTo(0, 0);
	if(content=='dashboard'){ 
		document.title = txt_lang.Dashboard;
		html += '<legend>'+txt_lang.Dashboard+'</legend>';
		html += '<div class="row">';
		html += '<div class="col-lg-6 col-md-12">'; 
		html += '<div class="panel panel-primary">';
		html += '<div class="panel-heading">';
		html += '<div class="row">';
		html += '<div class="col-xs-3">';
		html += '<i class="fa fa-comments fa-5x"></i>';
		html += '</div>';
		html += '<div class="col-xs-9 text-right">';
		html += '<div class="huge">26</div>';
		html += '<div>New Comments!</div>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '<a href="#">';
		html += '<div class="panel-footer">';
		html += '<span class="pull-left">View Details</span>';
		html += '<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>';
		html += '<div class="clearfix"></div>';
		html += '</div> </a>';
		html += '</div>';
		html += '</div>';
		html += '<div class="col-lg-6 col-md-12">';
		html += '<div class="panel panel-green">';
		html += '<div class="panel-heading">';
		html += '<div class="row">';
		html += '<div class="col-xs-3">';
		html += '<i class="fa fa-tasks fa-5x"></i>';
		html += '</div>';
		html += '<div class="col-xs-9 text-right">';
		html += '<div class="huge">12</div>';
		html += '<div>New Tasks!</div>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '<a href="#">';
		html += '<div class="panel-footer">';
		html += '<span class="pull-left">View Details</span>';
		html += '<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>';
		html += '<div class="clearfix"></div>';
		html += '</div> </a>';
		html += '</div>';
		html += '</div>';
		html += '<div class="col-lg-6 col-md-12">';
		html += '<div class="panel panel-yellow">';
		html += '<div class="panel-heading">';
		html += '<div class="row">';
		html += '<div class="col-xs-3">';
		html += '<i class="fa fa-shopping-cart fa-5x"></i>';
		html += '</div>';
		html += '<div class="col-xs-9 text-right">';
		html += '<div class="huge">124</div>';
		html += '<div>New Orders!</div>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '<a href="#">';
		html += '<div class="panel-footer">';
		html += '<span class="pull-left">View Details</span>';
		html += '<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>';
		html += '<div class="clearfix"></div>';
		html += '</div> </a>';
		html += '</div>';
		html += '</div>';
		html += '<div class="col-lg-6 col-md-12">';
		html += '<div class="panel panel-red">';
		html += '<div class="panel-heading">';
		html += '<div class="row">';
		html += '<div class="col-xs-3">';
		html += '<i class="fa fa-support fa-5x"></i>';
		html += '</div>';
		html += '<div class="col-xs-9 text-right">';
		html += '<div class="huge">13</div>';
		html += '<div>Support Tickets!</div>';
		html += '</div>';
		html += '</div>';
		html += '</div>'; 
		html += '<a href="#">';
		html += '<div class="panel-footer">';
		html += '<span class="pull-left">View Details</span>';
		html += '<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>';
		html += '<div class="clearfix"></div>';
		html += '</div> </a>';
		html += '</div>';
		html += '</div>'; 
		html += '</div>';
		app.dashboardContent=true;
		document.getElementById("dashboardContent").innerHTML = html; 
		window.history.pushState(txt_lang.Dashboard, "Title", baseURL+lang+"/dashboard/");
		setSeo(txt_lang.Dashboard, txt_lang.Dashboard, txt_lang.Dashboard);  
		
	}else if(content=='password'){  
		document.title = txt_lang.New_Password;
		html += '<legend>'+txt_lang.New_Password+'</legend>';
		html += '<div class="row">';
		html += '<form class="form-horizontal">';
		html += '<fieldset>';
		html += '<div class="form-group">';
		html += '<label class="col-md-3 control-label" for="profile-oldpassword">'+txt_lang.Old_Password+'</label>';
		html += '<div class="col-md-6">';
		html += '<div class="input-group">';
		html += '<div class="input-group-addon">';
		html += '<i class="fa fa-key" aria-hidden="true"></i>';
		html += '</div>';
		html += '<input id="profile-oldpassword" name="profile-oldpassword" type="password" placeholder="'+txt_lang.Old_Password+'" class="form-control input-md">';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '<div class="form-group">';
		html += '<label class="col-md-3 control-label" for="profile-newpass">'+txt_lang.New_Password+'</label>';
		html += '<div class="col-md-6">';
		html += '<div class="input-group">';
		html += '<div class="input-group-addon">';
		html += '<i class="fa fa-key" aria-hidden="true"></i>';
		html += '</div>';
		html += '<input id="profile-newpass" name="profile-newpass" type="password" placeholder="'+txt_lang.New_Password+'" class="form-control input-md">';
		html += '</div>';
		html += '</div>';
		html += '</div>'; 
		html += '<div class="form-group">';
		html += '<label class="col-md-3 control-label" for="New Password">'+txt_lang.Confirm_Password+'</label>';
		html += '<div class="col-md-6">';
		html += '<div class="input-group">';
		html += '<div class="input-group-addon">';
		html += '<i class="fa fa-key" aria-hidden="true"></i>';
		html += '</div>';
		html += '<input id="profile-confnewpass" name="profile-confnewpass" type="password" placeholder="'+txt_lang.Confirm_Password+'" class="form-control input-md">';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '<div class="form-group">';
		html += '<label class="col-md-3 control-label" ></label>';
		html += '<div class="col-md-6">'; 
		html += '<a href="javascript:newPassword();"  class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> '+txt_lang.Change_password+'</a>';
		html += '</div>';
		html += '</div>';
		html += '</fieldset>'; 
		html += '</form>'; 
		html += '</div>'; 
		app.newpassContent=true;
		document.getElementById("newpassContent").innerHTML = html; 
		window.history.pushState(txt_lang.New_Password, "Title", baseURL+lang+"/newpassword/"); 
		setSeo(txt_lang.New_Password, txt_lang.New_Password, txt_lang.New_Password);
		
	}else{  
		
		document.title = txt_lang.Profile;  
		html += '<legend>'+txt_lang.General_Profile+'</legend>'; 
		html += '<div class="row">';
		html += '<form class="form-horizontal">';
		html += '<fieldset>';
		html += '<div class="form-group">';
		html += '<label class="col-md-3 control-label" for="profile-name">'+txt_lang.Full_name+'</label>';
		html += '<div class="col-md-6">';
		html += '<div class="input-group">';
		html += '<div class="input-group-addon">';
		html += '<i class="fa fa-user"> </i>';
		html += '</div>';
		html += '<input id="profile-name" name="profile-name" type="text" placeholder="'+txt_lang.Full_name+'" class="form-control input-md">';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '<div class="form-group">';
		html += '<label class="col-md-3 control-label" for="profile-email">'+txt_lang.Email_Address+'</label>'; 
		html += '<div class="col-md-6">';
		html += '<div class="input-group">';
		html += '<div class="input-group-addon">';
		html += '<i class="fa fa-envelope-o"></i>';
		html += '</div>';
		html += '<input id="profile-email" name="profile-email"  type="text" placeholder="'+txt_lang.Email_Address+'" class="form-control input-md">';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '<div class="form-group">';
		html += '<label class="col-md-3 control-label" for="Date Of Birth">'+txt_lang.Date_Of_Birth+'</label>';
		html += '<div class="col-md-6">';
		html += '<div class="input-group">';
		html += '<div class="input-group-addon">';
		html += '<i class="fa fa-birthday-cake"></i>';
		html += '</div>';
		html += '<input id="profile-birthday" name="profile-birthday" type="text" value="" placeholder="'+txt_lang.Date_Of_Birth+'" class="form-control input-md datepicker" readonly>'; 
		html += '</div>';
		html += '</div>';
		html += '</div>'; 
		html += '<div class="form-group">';
		html += '<label class="col-md-3 control-label" for="Gender">'+txt_lang.Gender+'</label>'; 
		html += '<div class="col-md-6">';
		html += '<select id="profile-gender" name="profile-gender" class="form-control input-md">';
		html += '<option value="0">'+txt_lang.Male+'</option>';
		html += '<option value="1">'+txt_lang.Female+'</option>';
		html += '<option value="2">'+txt_lang.Other+'</option>';
		html += '</select>';
		html += '</div>';
		html += '</div>';
		html += '<div class="form-group">';
		html += '<label class="col-md-3 control-label" for="Phone number ">'+txt_lang.Phone_number+'</label>';
		html += '<div class="col-md-6">';
		html += '<div class="input-group othertop">';
		html += '<div class="input-group-addon">';
		html += '<i class="fa fa-mobile fa-1x" style="font-size: 20px;"></i>';
		html += '</div>';
		html += '<input id="profile-phone" name="profile-phone" type="text" placeholder="'+txt_lang.Phone_number+'" class="form-control input-md">';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '<div class="form-group">';
		html += '<label class="col-md-3 control-label" for="profile-address">'+txt_lang.Address+'</label>';
		html += '<div class="col-md-6">';
		html += '<div class="input-group">';
		html += '<div class="input-group-addon">';
		html += '<i class="fa fa-location-arrow" aria-hidden="true"></i>';
		html += '</div>';
		html += '<textarea id="profile-address" name="profile-address" rows="5" class="form-control input-md"></textarea>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '<div class="form-group">';
		html += '<label class="col-md-3 control-label" ></label>';
		html += '<div class="col-md-6">'; 
		html += '<a href="javascript:editProfile();" class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> '+txt_lang.Save+'</a>';
		html += '</div>';
		html += '</div> '; 
		html += '</fieldset>';
		html += '</form>';
		html += '</<div>';
		app.profileContent=true;  
		document.getElementById("profileContent").innerHTML = html; 
		getUserProfile(id);  
		window.history.pushState(txt_lang.Profile, "Title", baseURL+lang+"/profile/");
		setSeo(txt_lang.Profile, txt_lang.Profile, txt_lang.Profile); 
	}
	app.loader=false; 
}