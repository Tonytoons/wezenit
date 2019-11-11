var btn_loader = '';
var txt_btn = '';
var table;
var type_img = ['jpg','png','gif','jpeg'];
var error_txt = 'Oops! Something went wrong, Please try again later.';
var language = $('#language').val();
if(language=='') language= 1;

$('.datepicker').datepicker({
    format: 'yyyy-mm-dd',
});

$('.btn-loader').click(function() {
	btn_loader = $(this);
	$(btn_loader).buttonLoader('start');
});
$.fn.buttonLoader = function(action) {
	var self = $(this); 
	$(self).attr("disabled", false);
	if (action == 'start') { 
		if ($(self).attr("disabled") == "disabled") {
			return false;
		} 
		$('.btn-loader').attr("disabled", true);
		txt_btn = $(self).html();
		var text = 'Loading...';
		if ($(self).attr('data-load-text') != undefined && $(self).attr('data-load-text') != "") {
			var text = $(self).attr('data-load-text');
		}
		$(self).html('<span class="spinner"><i class="fa fa-spinner fa-spin" title="button-loader"></i></span> ' + text);
		$(self).addClass('active');
	}
	if (action == 'stop') {
		$(self).html(txt_btn);
		$(self).removeClass('active');
		$('.btn-loader').attr("disabled", false);
	}
}

function changelang($this){ 
    language = $($this).val(); 
    id = $($this).val();
    url =  basePath+'admin/'+action+'/?langID='+language; 
    console.log(url);
    window.location = url; 
} 
 
function doNothing(){} 

function imagePreview(input, id, maxsize) {      
	$('#'+id).html('');
    if (input.files && input.files[0] && input.files[0].type.match('image.*')) { 
        var reader = new FileReader();
        reader.onload = function (e) {
            var html = '<img src="'+e.target.result+'" style="max-width:25%;">';  
            $('#'+id).html(html);  
        }
        reader.readAsDataURL(input.files[0]);
    }
}  
  
function uploadImage(image) { 
	//console.log(image);  
	var baseUrl = basePath+'admin/uploadImage/';    
    var data = new FormData();
       
    data.append("image", image);  
    //data.append("actionpage", action);  
    $.ajax({  
        url: baseUrl,
        cache: false,
        contentType: false,
        processData: false, 
        data: data,
        type: "post",
        success: function(url) {
        	console.log(url);   
            var image = $('<img>').attr('src', url);  
            $('.summernote').summernote("insertNode", image[0]);
        },
        error: function(data) { 
            console.log(data); 
        }
    });
}

function makeid()
{ 
    var text = ""; 
    var possible = "0123456789";
    for( var i=0; i < 6; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    return text;  
}  
 
$(':checkbox').checkboxpicker();  
$('#active').on('change', function() {
    var v = $(this).val();
    if(v==1){  
       $(this).val(0);
    }else{ 
       $(this).val(1);
    } 
}); 

function numberFormat (number, decimals, dec_point, thousands_sep) {
	
	//var nf = new Intl.NumberFormat();
    //return n.toLocaleString();//nf.format(n);     
    
    // *     example 1: number_format(1234.56);
    // *     returns 1: '1,235'
    // *     example 2: number_format(1234.56, 2, ',', ' ');
    // *     returns 2: '1 234,56'
    // *     example 3: number_format(1234.5678, 2, '.', '');
    // *     returns 3: '1234.57'
    // *     example 4: number_format(67, 2, ',', '.');
    // *     returns 4: '67,00'
    // *     example 5: number_format(1000);
    // *     returns 5: '1,000'
    // *     example 6: number_format(67.311, 2);
    // *     returns 6: '67.31'
    // *     example 7: number_format(1000.55, 1);
    // *     returns 7: '1,000.6'
    // *     example 8: number_format(67000, 5, ',', '.');
    // *     returns 8: '67.000,00000'
    // *     example 9: number_format(0.9, 0);
    // *     returns 9: '1'
    // *    example 10: number_format('1.20', 2);
    // *    returns 10: '1.20'
    // *    example 11: number_format('1.20', 4);
    // *    returns 11: '1.2000'
    // *    example 12: number_format('1.2000', 3);
    // *    returns 12: '1.200'
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
    
    
	//var parts=n.toString().split(".");
    //return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + (parts[1] ? "." + parts[1] : "");
     
    //return parseFloat(num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,"));
}  
/* 
$('.summernote').summernote({ 
  height: 500,   //set editable area's height
  codemirror: { // codemirror options
    theme: 'monokai'
  },     
  callbacks: {
    onImageUpload: function(image) {
        uploadImage(image[0]);
    }
  } 
});
*/ 