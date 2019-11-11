$(function () {
     
    $('#genres').selectpicker(); 
      
    if(total_page){
        
        window.pagObj = $('#pagination').twbsPagination({
        	startPage: page,
            totalPages: total_page,
            visiblePages: 5,
            hideOnlyOnePage: true,
    		href: false, 
            onPageClick: function (event, page) {
                //console.info(page + ' (from options)');
                //
            }   
        }).on('page', function (event, page) {        
        	//window.location = '/radioStation/?page='+page+'&limit='+limit+'&sortby='+sortby;
        	//window.location = '/radioStation/?page='+page+'&limit='+limit+'&sortby='+sortby+'&genre='+genre+'&year='+year+'&aid='+aid+'&name='+name; 
            //console.info(page + ' (from event listening)'); 
            var genre = $('#genres').val(); 
            var genre_name = convertToSlug($('#genres option:selected').toArray().map(item => item.text).join()); 
            //if(genre!='all') genre_name = convertToSlug($('.filter-option').text()); 
            if(!genre){
        	    genre_name = 'genres'; 
        	    genre = 'all';
        	}  
            window.location = basePath+'playlist/'+genre+'-'+genre_name+'/page/'+page+'/limit/'+limit+'/sortby/'+sortby+'/year/'+year+'/aid/'+aid+'/';
        });  
         
    }   
    
    $('#genres').on('hide.bs.select', function (e, clickedIndex, isSelected, previousValue) {
       //console.log([clickedIndex, isSelected,previousValue, e]);  
       filtergenres(); 
    });   
    
    $('#genres').selectpicker('val', id);
    
});
 
function filter(){ 
	
	var limit = $('#limit').val(); 
	var sortby = $('#sortby').val();
	var genre = $('#genres').val();
	var year =  $('#year').val();    
	var genre_name = convertToSlug($('#genres option:selected').toArray().map(item => item.text).join());  
	//if(genre!='all') genre_name = convertToSlug($('.filter-option').text());
	if(!genre){
	    genre_name = 'genres'; 
	    genre = 'all';
	} 
	window.location = basePath+'playlist/'+genre+'-'+genre_name+'/page/1/limit/'+limit+'/sortby/'+sortby+'/year/'+year+'/aid/'+aid+'/';  
	
}
   
function filtergenres(){      
	
	var limit = $('#limit').val(); 
	var sortby = $('#sortby').val();
	var genre = $('#genres').val();
	var year =  $('#year').val();     
	//var genre_name = $('option:selected', '#genres').attr('data-slug');
	var genre_name = convertToSlug($('#genres option:selected').toArray().map(item => item.text).join());
	if(!genre) genre_name = 'genres';  
	//console.log(genre_name); 
	if(genre){ 
 	    window.location = basePath+'playlist/'+genre+'-'+genre_name+'/';  
	}
	
}
 
function convertToSlug(Text)
{
    return Text
        .toLowerCase()
        .replace(/ /g,'-') 
        //.replace(/[^\w-]+/g,'')
        ;
}


 
var questions = [
  {question:"What's your first name?" ,type: "select", name:'first_name'},
  {question:"What's your last name?", name:'last_name'},
  {question:"What's your email?", name:'email', pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/},
  {question:"Create your password", name:'password', type: "password"}
];
var data_register = {'fname':'','lname':'','email':'','password':''}; 
/*
  do something after the questions have been answered
*/
var onComplete = function() {

    var h1 = document.createElement('h1');
    h1.appendChild(document.createTextNode('Thanks ' + questions[0].answer + ' for checking this pen out!'));
    $.post('register.php',data_register, function(rs){
        console.log(rs);
        if(rs=='OK'){
            
        }
    });
    setTimeout(function() {
      register.parentElement.appendChild(h1)
      setTimeout(function() { h1.style.opacity = 1 }, 50);
      $(".register-block").fadeOut('slow'); 
    }, 1000)

}

;(function(questions, onComplete) {

    var tTime = 100 // transition transform time from #register in ms
    var wTime = 200 // transition width time from #register in ms
    var eTime = 1000 // transition width time from inputLabel in ms

    // init
    // --------------
    if (questions.length == 0) return

    var position = 0

    putQuestion()

    forwardButton.addEventListener('click', validate)
    inputField.addEventListener('keyup', function(e) {
        transform(0, 0) // ie hack to redraw
        if (e.keyCode == 13) validate()
    })

    previousButton.addEventListener('click', function(e) {
        if (position === 0) return
        position -= 1
        hideCurrent(putQuestion)
    })


    // functions
    // --------------

    // load the next question
    function putQuestion() {
        inputLabel.innerHTML = questions[position].question
        inputField.type = questions[position].type || 'text'
        inputField.value = questions[position].answer || ''
        inputField.name = questions[position].name || ''
        inputField.focus()

        // set the progress of the background
        progress.style.width = position * 100 / questions.length + '%'

        previousButton.className = position ? 'ion-android-arrow-back' : 'ion-person'
          
        showCurrent()

    }

    // when submitting the current question
    function validate() {
        
        
        
        //if(inputField.name=='first_name')
        
        var validateCore = function() { 
           var rt = inputField.value.match(questions[position].pattern || /.+/); 
           return rt;  
        }

        if (!questions[position].validate) questions[position].validate = validateCore

        // check if the pattern matches
        if (!questions[position].validate()) wrong(inputField.focus.bind(inputField))
        else ok(function() {
            
            if(inputField.name=='first_name') data_register.fname = inputField.value;
            if(inputField.name=='last_name') data_register.lname = inputField.value;
            if(inputField.name=='email') data_register.email = inputField.value;
            if(inputField.name=='password') data_register.password = inputField.value;
              
            //console.log(inputField.value); 
            // execute the custom end function or the default value set
            if (questions[position].done) questions[position].done()
            else questions[position].answer = inputField.value

            ++position

            // if there is a new question, hide current and load next
            if (questions[position]) hideCurrent(putQuestion)
            else hideCurrent(function() {
                // remove the box if there is no next question
                register.className = 'close'
                progress.style.width = '100%'

                onComplete();
                
            })

        })

    }


    // helper
    // --------------

    function hideCurrent(callback) {
        inputContainer.style.opacity = 0
        inputLabel.style.marginLeft = 0
        inputProgress.style.width = 0
        inputProgress.style.transition = 'none'
        inputContainer.style.border = null
        setTimeout(callback, wTime)
    }

    function showCurrent(callback) {
        inputContainer.style.opacity = 1
        inputProgress.style.transition = ''
        inputProgress.style.width = '100%'
        setTimeout(callback, wTime)
    } 

    function transform(x, y) {
        register.style.transform = 'translate(' + x + 'px ,  ' + y + 'px)'
    }

    function ok(callback) {
        register.className = ''
        setTimeout(transform, tTime * 0, 0, 10)
        setTimeout(transform, tTime * 1, 0, 0)
        setTimeout(callback, tTime * 2)
    }

    function wrong(callback) {
        register.className = 'wrong'
        for (var i = 0; i < 6; i++) // shaking motion
            setTimeout(transform, tTime * i, (i % 2 * 2 - 1) * 20, 0)
        setTimeout(transform, tTime * 6, 0, 0)
        setTimeout(callback, tTime * 7)
    }

}(questions, onComplete));