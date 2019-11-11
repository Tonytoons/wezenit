function getDetail(cid)
{
	app.loader=false;
	var html = '';
	html += '<div class="container"><transition name="bounce">';
	html += '<div class="loader">';
	html += 'Loading...';
	html += '</div></transition></div>';
	document.getElementById("detail").innerHTML = html;
	app.detail=true;
	app.closeMenu=true;
	app.topMenu=false;
	app.channels=false;
	app.hotPage=false;
	app.login=false;
	app.register=false;
	apiLink = apiURL+'/'+lang+'/content/'+cid+'/?username=RockStar&password=Um9ja1N0YXI=&noCache='+noCache; 
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
	if(data)
	{
	    var maname = data.name.replace(/[!]/g,'');
		var malink = id+'-'+maname;
		malink = malink.replace(/ /g, '-');
		window.history.pushState(id, "Title", baseURL+lang+"/blog/"+malink+'/');
		var html = '';
		html += '<div class="container">';
        html += '<div class="card">';
        html += '<div class="img-detail" style="background:url('+data.img+') top center / contain no-repeat;"></div>';
        html += '<div class="card-block">';
        html += '<h4 class="card-title">'+data.name+'</h4>';
        html += '<p class="card-text">'+data.detail+'</p>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
		window.scrollTo(0, 0);
		document.getElementById("detail").innerHTML = html;
		app.loader = false;
		document.title = data.name;
		
		/*************** Set SEO *****************/ 
		setMetaTag('property', 'keywords', data.name);
		setMetaTag('property', 'description', data.detail);
		setMetaTag('property', 'og:title', data.name);  
		setMetaTag('property', 'og:description', data.detail); 
		setMetaTag('property', 'og:url', window.location.href);
		setMetaTag('property', 'og:image', data.img); 
		setMetaTag('property', 'twitter:title', data.name);  
		setMetaTag('property', 'twitter:description', data.detail); 
		setMetaTag('property', 'twitter:site', window.location.href);
		setMetaTag('property', 'twitter:image:src', data.img); 
	}
}