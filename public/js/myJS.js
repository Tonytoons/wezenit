var apiURL = 'https://www.zenovly.com/api';

const New = 
{
    created: function (){ getHome('new') },
    template: '<div id="home"></div>',
}
const Detail = 
{
    created: function (){ getDetail(this.$route.params.id); },
    template: '<div id="home"></div>',
}

const routes = [
    { 
        path: '/', 
        component: Home,
    },
    { 
        path: '/new', 
        component: New,
    },
    { 
        path: '/login', 
        component: LoginPage,
    },
    { 
        path: '/:id', 
        component: Detail,
    },
]

const router = new VueRouter({
    //mode: 'history',
    routes 
})

const app = new Vue({
                        data: {
                                    loader:true,
	                   		},
	                    router,
                    }).$mount('#app')
                    
function getHome(type)
{
    content = 'content';
	apiLink = apiURL+'/en/'+content+'/?username=RockStar&password=Um9ja1N0YXI=&for='+type+'&page=1&rd='+makeid();
	
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
								    homeHTML(subResult.items, type);
                                }
                                else
                                {
                                    
                                }
                            }
                            xhr.send()
                        }
                    }
                });
}

function makeid()
{
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}

function homeHTML(data, type)
{
    var html = '';
    if(type == 'detail')
    {
        html += '<div class="card">';
        html += '<div class="img-detail" style="background:url('+data['img']+') top center / contain no-repeat;"></div>';
        html += '<div class="card-block">';
        html += '<h4 class="card-title">'+data['name']+'</h4>';
        html += '<p class="card-text">'+data['detail']+'</p>';
        html += '</div>';
        html += '</div>';
        document.title = data.name;
    }
    else
    {
        html += '<div class="row">';
        for (var i = 0; i < data.length; i++)
    	{ 
    		html += '<a class="col-sm-4" href="./#/'+data[i]['id']+'">';
            html += '<div class="card">';
            html += '<img class="card-img-top home-img" src="https://starter-kit-tonytoons.c9users.io/public/img/blog/'+data[i]['img']+'" alt="'+data[i]['name']+'">';
            html += '<div class="card-block">';
            html += '<p class="card-text">'+data[i]['name']+'</p>';
            html += '</div>';
            html += '</div>';
            html += '</a>';
        }
        html += '</div>';
    	document.title = 'Recents';
    }
    document.getElementById("home").innerHTML = html;
}

function getDetail(cid)
{
	apiLink = apiURL+'/en/content/'+cid+'/?username=RockStar&password=Um9ja1N0YXI=';  //alert(apiLink);
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
									homeHTML(subResult.items, 'detail');
                                }
                                else
                                {
                                    
                                }
                            }
                            xhr.send()
                        }
                    }
                });
}
 