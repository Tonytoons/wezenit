
var app = new Vue({
	         			el: '#app',
	                	data: {
									detail:false,
									closeMenu:false,
									channels:false,
	                      			loader:true,
									topBar:true,
									topMenu:true,
	                                hotPage:true,
									footer:true,
									topHome:'#b8000c',
									topNew:'#626262',
									topMbar:'#626262',
									footHome:'#fff',
									footNew:'#162a36',
									footMbar:'#162a36'
	                   		}
	    		});
var homeType = 'hot';
var apiURL = 'http://35.157.227.243/api';
var channel = '';

function init()
{
    if(action == 'new')
	{
		homeType = 'new';
		app.loader=true;
		app.topMenu=true;
		app.hotPage=true;
		app.closeMenu=false;
		app.detail=false;
		app.channels=false;
		app.topHome='#626262';
		app.topNew='#b8000c';
		app.topMbar='#626262';
		app.footHome='#162a36';
		app.footNew='#fff';
		app.footMbar='#162a36';
		window.history.pushState("New", "Title", "http://35.157.227.243/"+lang+"/new/");
	}
	else if(action == 'contact')
	{
		app.loader=true;
		homeType = 'channels';
		app.topMenu=true;
		app.channels=true;
		app.closeMenu=false;
		window.history.pushState("Channels", "Title", "http://35.157.227.243/"+lang+"/contact/");
		
		app.detail=false;
		app.hotPage=false;
		app.topHome='#626262';
		app.topNew='#626262';
		app.topMbar='#b8000c';
		app.footHome='#162a36';
		app.footNew='#162a36';
		app.footMbar='#fff';
	}
	else if(action == 'detail')
	{
		loadFBapi();
		homeType = 'detail';
		app.loader=true;
		app.detail=true;
		app.closeMenu=true;
		app.topMenu=false;
		app.channels=false;
		app.hotPage=false;
		app.topHome='#b8000c';
		app.topNew='#626262';
		app.topMbar='#626262';
		app.footHome='#fff';
		app.footNew='#162a36';
		app.footMbar='#162a36';
		window.history.pushState("Detail", "Title", "http://35.157.227.243/"+lang+"/detail/"+id);
	}
	else
	{
		homeType = 'hot';
		app.loader=true;
		app.topMenu=true;
		app.hotPage=true;
		app.closeMenu=false;
		app.channels=false;
		app.detail=false;
		app.topHome='#b8000c';
		app.topNew='#626262';
		app.topMbar='#626262';
		app.footHome='#fff';
		app.footNew='#162a36';
		app.footMbar='#162a36';
		window.history.pushState("Home", "Title", "http://35.157.227.243/"+lang+"/");
	}
	app.loader=false;
}

function makeid()
{
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}

function getChannels()
{
	document.title = 'Contact Us';
	homeType = 'channels';
	app.loader=true;
	app.topMenu=true;
	app.channels=true;
	app.closeMenu=false;
	app.detail=false;
	app.hotPage=false;
	app.topHome='#626262';
	app.topNew='#626262';
	app.topMbar='#b8000c';
	app.footHome='#162a36';
	app.footNew='#162a36';
	app.footMbar='#fff';
	window.history.pushState("Channels", "Title", "http://35.157.227.243/"+lang+"/contact/");
	app.loader=false;
}

function moreContent(type, npage, id)
{
	homeType = type;
	app.loader=true;
	app.topMenu=true;
	app.hotPage=true;
	app.closeMenu=false;
	app.channels=false;
	app.detail=false;
	app.channel=false;
	var content = 'content';
	if( (type == 'hot') || (type == 'new') ) 
	{
		content = 'content';
		apiLink = apiURL+'/'+lang+'/'+content+'/?username=RockStar&password=Um9ja1N0YXI=&for='+type+'&page='+npage+'&rd='+makeid();
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
	document.title = 'RockStar Academy';
	homeType = type;
	app.loader=true;
	app.topMenu=true;
	app.hotPage=true;
	app.closeMenu=false;
	app.channels=false;
	app.detail=false;
	if(type == 'hot')
	{
		app.topHome='#b8000c';
		app.topNew='#626262';
		app.topMbar='#626262';
		app.footHome='#fff';
		app.footNew='#162a36';
		app.footMbar='#162a36';
		window.history.pushState("Home", "Title", "http://35.157.227.243/"+lang+"/");
	}
	else if(type == 'new')
	{
		app.topHome='#626262';
		app.topNew='#b8000c';
		app.topMbar='#626262';
		app.footHome='#162a36';
		app.footNew='#fff';
		app.footMbar='#162a36';
		window.history.pushState("New", "Title", "http://35.157.227.243/"+lang+"/new/");
	}
	else
	{
		app.topHome='#626262';
		app.topNew='#626262';
		app.topMbar='#b8000c';
		app.footHome='#162a36';
		app.footNew='#162a36';
		app.footMbar='#fff';
		window.history.pushState("Channels", "Title", "http://35.157.227.243/"+lang+"/channels/");
	}
	var html = '';
	var vpage = parseInt(npage)+1;
	for (var i = 0; i < data.length; i++)
	{
		html += '<a class="vdo-box" href="javascript:getDetail(\''+data[i]['id']+'\');">';
		html += '<div class="img-vdo" style="background:url(http://35.157.227.243/img/blog/'+data[i]['img']+') center no-repeat; background-size:cover;"></div>';
		html += '<div class="name-vdo">'+data[i]['name']+'</div>';
		html += '<div class="view-vdo">view : '+data[i]['view'].toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")+'</div>';
		//html += '<div class="home-detail">'+data[i]['detail'].substr(0, 110)+' ...</div>';
		html += '</a>';
	}
	
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

function loadFBapi()
{
	var js = document.createElement('script');
    js.src = '//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=353878298092406&version=v2.0';
	document.getElementById("detail").appendChild(js);
	try 
	{
    	FB.XFBML.parse();
   	} catch (ex) { }
}

function getDetail(cid)
{
	app.loader=true;
	app.detail=true;
	app.closeMenu=true;
	app.topMenu=false;
	app.channels=false;
	app.hotPage=false;
	apiLink = apiURL+'/'+lang+'/content/'+cid+'/?username=RockStar&password=Um9ja1N0YXI='; 
    new Vue({
                    created: function () 
                    {
                        this.fetchData()
                    },
                    methods: 
                    {
                        fetchData : function () 
                        {
                            var xhr = new XMLHttpRequest()
                            var self = this
                            xhr.open('GET', apiLink)
                            xhr.onload = function () 
                            {
                                var subResult = JSON.parse(xhr.responseText);
                                if(subResult.status == 200)
                                {
									detailHTML(cid, subResult.items);
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

function detailHTML(id, data)
{
	document.getElementById("detail").innerHTML = '';
	if(data)
	{
		window.history.pushState("Detail", "Title", "http://35.157.227.243/"+lang+"/detail/"+id);
		var html = '';
		html += '<div class="contentVDO" style="background:url('+data.img+') top no-repeat; background-size:contain;"></div>';
		
		html += '<div class="contentBody">';
		html += '<div class="name-vdo">'+data.name+'</div>';
		html += '<div class="view-vdo">view : '+data.view+'</div>';
		if(data.detail)
		{
			html += '<div class="detail-dvdo">'+data.detail+'</div>';
		}
		html += '<div id="fb-root"></div>';
		html += '<div id="comments" class="fb-comments" data-href="http://35.157.227.243/'+lang+'/detail/'+id+'" data-width="100%" data-numposts="9" data-colorscheme="light"></div></div>';
		
		window.scrollTo(0, 0);
		document.getElementById("detail").innerHTML = html;
		app.loader = false;
		window.onscroll = function () 
		{
    		var rect = document.getElementById('comments').getBoundingClientRect();
    		if (rect.top < window.innerHeight) 
			{
        		loadFBapi();
        		window.onscroll = null;
    		} 
		}
		document.title = data.name;
	}
}

function closeDetail()
{
	document.title = 'RockStar Academy';
	app.loader=true;
	app.topMenu=true;
	app.detail=false;
	app.closeMenu=false;
	app.channels=false;
	app.hotPage=false;
	if( (homeType == 'new') || (homeType == 'hot') )
	{
		app.hotPage=true;
		app.detail=false;
		app.loader=false;
		if(homeType == 'new')
		{
			window.history.pushState("New", "Title", "http://35.157.227.243/"+lang+"/new/");
		}
		else
		{
			window.history.pushState("New", "Title", "http://35.157.227.243/"+lang+"/");
		}
	}
	else
	{
		app.hotPage=true;
		moreContent('hot', 1, '');
	}
}

function doNothing(){}

function isValidEmail(str)
{
	var filter = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i
	return (filter.test(str));
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
    var r = confirm("Press ok to logout!");
    if (r == true) 
    {
        app.loader=true;
        var cookies = document.cookie.split(";");
        for (var i = 0; i < cookies.length; i++) 
        {
            var cookie = cookies[i];
            var eqPos = cookie.indexOf("=");
            var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
            setCookie(name, '', '', 0);
        }
    	location.reload();
    }
}