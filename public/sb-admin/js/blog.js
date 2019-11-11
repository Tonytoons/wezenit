$(function() {
     
    $('.summernote').summernote({ 
	  height: 500,   //set editable area's height
	  codemirror: { // codemirror options
	    theme: 'monokai'
	  },  
	  callbacks: {
	    onImageUpload: function(image) {
	         
	        uploadImage(image[0]); 
	    },
	    onMediaDelete : function($target, editor, $editable) {
          console.log($target[0].src); // img 
          removeImage($target[0].src);
          // remove element in editor 
          $target.remove(); 
        }   
	  }
	});
	
});

function uploadImage(file, thiss) {  
	//console.log(image);  
	var baseUrl = basePath+'admin/uploadimage/';  
    var data = new FormData();
      
    data.append("image", file);   
    data.append("actionpage", action);  
	$.ajax({
          data: data,
          type: "POST",
          url: baseUrl, 
          cache: false,
          contentType: false,
          processData: false,
          success: function(url) {   
          	console.log(url);  
          	//image.addClass('img-fluid img-blog');
            var image = $('<img class="img-fluid">').attr({src: url, class: "img-fluid"});  
            $(thiss).summernote("insertNode", image[0]);    
          }
    });
} 
 
function removeImage(file) {  
	var base_Url = basePath+'admin/uploadimage/?act=delimg'; 
	var data = new FormData();  
	data.append("urlFile", file);
	$.ajax({
		data : data,
		type : "POST",
		url : base_Url,
		cache : false,
		contentType : false,
		processData : false,
		dataType : 'json',
		success : function(rs) {
			console.log(rs); 
		}
	});
}  

function validateform(){
    var name_fr = $('#name').val();
    var name_en = $('#name_en').val();
    if(!name_fr){ 
        $('#block-en').hide();
        $('#block-fr').show();
        $('#lang').val(1);
        alert('Please enter your blog name fr.');
        $('#name').focus();
    }else if(!name_en){
        $('#block-fr').hide();
        $('#block-en').show();
        $('#lang').val(2);
        alert('Please enter your blog name en.');
        $('#name_en').focus(); 
    }else{
        $('#blogform').submit();
    }  
    $('.summernote').summernote('code');
}   

$(function() { 
    
    $("#lang").change(function(){
      if($(this).val()==2){
          $('#block-fr').hide();
          $('#block-en').show();
      }else{  
          $('#block-en').hide();
          $('#block-fr').show();
      } 
      $('.summernote').summernote('code');
    });
    
    
  
  /* 
  $("form[name='registration']").validate({  
    rules: {
     'blog[name]': { 
        required: true, 
        minlength: 2,
        remote: 
        {  
            url: url+'&r='+makeid(),
            type: "post", 
            data:{
               'name':function()
                {
                    return $('#name').val(); 
                }
           },     
           complete: function(data)  
            {
                console.log(data);
            }
        }
      }, 
     'pic': "required", 
    },
    messages: {  
      'blog[name]':{  
        required: "Please enter your blog name.",  
        remote: function() { return $.validator.format("This name is not available.", $("#name").val()) } 
      },
      'pic': "Please enter image blog" 
    },
     submitHandler: function(form)
     {
        form.submit();
     } 
  }); */
});