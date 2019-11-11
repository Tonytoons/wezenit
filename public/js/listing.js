const Home = 
{
    created: function ()
    {
        apiLink = apiURL+'/en/content/?username=RockStar&password=Um9ja1N0YXI=&for=hot&page=1&rd='+makeid();
        var xhr = new XMLHttpRequest()
        var self = this
        xhr.open('GET', apiLink)
        xhr.onload = function () 
        {
            var subResult = JSON.parse(xhr.responseText);
            if(subResult.status == 200)
            {
				makeHTML(subResult.items);
            }
            else{}
        }
        xhr.send()
    },
    template: '<div id="home"></div>',
}

function makeHTML(data)
{
    var html = '';
    
    html +='<div><h1> Renovly ss</h1> </div> <div> <h2> Your renovation made easy </h2> </div>';
    html += '<div class="row">';
    for (var i = 0; i < data.length; i++)
    {
        html += '<a class="col-sm-4" href="./#/'+data[i]['id']+'">';
        html += '<div class="card">';
        html += '<img class="card-img-top home-img" src="https://starter-kit-tonytoons.c9users.io/public/img/blog/'+data[i]['img']+'" alt="'+data[i]['name']+'">';
        html += '<div class="card-block">';
        html += '<p class="card-text">'+data[i]['name']+'et oui</p>';
        html += '</div>';
        html += '</div>';
        html += '</a>';
    }
    html += '</div>';
    document.title = 'Renovly';
    document.getElementById("home").innerHTML = html;
    app.loader=false;
}