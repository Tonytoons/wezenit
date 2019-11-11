/*A jQuery plugin which add loading indicators into buttons
* By Minoli Perera
* MIT Licensed.
*/
var txt_btn = ''; 
(function ($) { 
    $.fn.buttonLoader = function (action) {  
        var self = $(this);  
        $(self).attr("disabled", false); 
        if (action == 'start') {
            if ($(self).attr("disabled") == "disabled") {
                return false;
            }  
            $('.btn-loader').attr("disabled", true);
            //$(self).attr('data-btn-text', $(self).text());
            txt_btn = $(self).html();
            var text = 'Loading';     
            //console.log($(self).attr('data-load-text'));  
            if($(self).attr('data-load-text') != undefined && $(self).attr('data-load-text') != ""){
                var text = $(self).attr('data-load-text');
            }
            $(self).html('<span class="spinner"><i class="fa fa-spinner fa-spin" title="button-loader"></i></span> '+text);
            $(self).addClass('active');
        }
        if (action == 'stop') {   
            $(self).html(txt_btn);  
            $(self).removeClass('active');
            $(self).attr("disabled", false);
        }
    }
})(jQuery);
