function moreContent(type, npage, id)
{
	menuActive(); 
	homeType = type;
	app.loader=false;
	app.topMenu=true;
	var html = '';
	html += '<div class="container"><transition name="bounce">';
	html += '<div class="loader">';
	html += 'Loading...';
	html += '</div></transition></div>'; 
	document.getElementById("hotPage").innerHTML = html;
	app.hotPage=true;
	app.closeMenu=false;
	app.channels=false;
	app.detail=false;
	app.channel=false;
	app.login=false;
	app.register=false;
	app.profile=false;  
	
	app.profileContent=false;
	app.dashboardContent=false;
	app.newpassContent=false; 
	 
	var content = 'content';
	if( (type == 'hot') || (type == 'new') ) 
	{
		content = 'content';
		apiLink = apiURL+'/'+lang+'/'+content+'/?username=RockStar&password=Um9ja1N0YXI=&for='+type+'&page='+npage+'&noCache='+noCache+'&rd='+makeid();
	}
	else if(type == 'channels') 
	{
		content = 'cate';
		apiLink = apiURL+'/'+lang+'/'+content+'/?username=RockStar&password=Um9ja1N0YXI=&rd='+makeid();
	}
	else if(type == 'channel') 
	{
		content = 'cate';
		apiLink = apiURL+'/'+lang+'/'+content+'/'+id+'/?username=RockStar&password=Um9ja1N0YXI=&page='+npage+'&rd='+makeid();
	}
	
    new Vue({
                    created: function () 
                    {
                        this.fetchData()
                    },
                    methods: 
                    {
                        fetchData: function () 
                        {
                            var xhr = new XMLHttpRequest()
                            var self = this
                            xhr.open('GET', apiLink)
                            xhr.onload = function () 
                            {
                                var subResult = JSON.parse(xhr.responseText);
                                if(subResult.status == 200)
                                { 
									total = subResult.total;
									if(type == 'hot')
									{
										chot = subResult.items;
									}
									else
									{
										cnew = subResult.items;
									}
									
									if( (type == 'hot') || (type == 'new') ) 
									{
                                    	listHTML(subResult.items, npage, type);
									}
									else if(type == 'channels') 
									{
										channelsHTML(subResult.items, npage, type);
									}
                                }
                                else
                                {
                                    app.loader=false;
                                }
                                app.loader=false;
                            }
                            xhr.send()
                        }
                    }
                });
}

function listHTML(data, npage, type)
{
	homeType = type;
	app.loader=false;
	app.topMenu=true;
	app.hotPage=true;
	app.closeMenu=false;
	app.channels=false;
	app.detail=false;
	app.login=false; 
	app.register=false;
	app.profile=false;
	
	app.profileContent=false;
	app.dashboardContent=false;
	app.newpassContent=false; 
	
	if(type == 'hot')
	{
		app.topHome='#b8000c';
		app.topNew='#626262';
		app.topMbar='#626262';
		app.footHome='#fff';
		app.footNew='#162a36';
		app.footMbar='#162a36';
		document.title = 'Renovly';
		window.history.pushState("Home", "Title", baseURL+lang+'/');
		setSeo("Hot", "Hot", "Hot"); 
	}
	else if(type == 'new')
	{
		app.topHome='#626262';
		app.topNew='#b8000c';
		app.topMbar='#626262';
		app.footHome='#162a36';
		app.footNew='#fff';
		app.footMbar='#162a36';
		window.history.pushState("New", "Title", baseURL+lang+"/new/");
		setSeo("New", "New", "New");
	}
	else
	{
		app.topHome='#626262';
		app.topNew='#626262';
		app.topMbar='#b8000c';
		app.footHome='#162a36';
		app.footNew='#162a36';
		app.footMbar='#fff';
		window.history.pushState("Channels", "Title", baseURL+lang+"/channels/");
		//setSeo("Channels", "Channels", "Channels");
	}
	var html = '';
	var vpage = parseInt(npage)+1;
	html += '<div class="container">'; 
	if(type == 'hot')
	{
		html += '<h2> Renovly </h2><h3> '+txt_lang.Site_Title+'</h3>';
	}
	html += '<div class="row">';
	html += '<ul class="thumbnails">';
	for (var i = 0; i < data.length; i++)
	{ 
		
		html += '<li class="col-md-3 col-sm-3 col-xs-12">';	 
		html += '<a  href="javascript:getDetail('+data[i]['id']+');" class="thumbnail">';  
		html += '<div class="card-img-top home-img" style="background:url(https://files.renovly.com/blog/'+data[i]['img']+') center bottom / contain no-repeat; width: 100%; height: 250px;"></div>';   	   
		html += '<span class="caption"><i class="fa fa-eye" aria-hidden="true"></i></span>'; 	  
		html += '<h3>'+data[i]['name']+'</h3>';   	 
		html += '</a>'; 
		html += '</li>';  
	}
	html += '</ul>'; 
	html += '</div>';
	html += '</div>';
	if(npage == 1)
	{
		document.getElementById("hotPage").innerHTML = html;
	}
	else
	{
		document.getElementById("content"+npage).innerHTML = html;
	}
	
	app.loader=false;
}