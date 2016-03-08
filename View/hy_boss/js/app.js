//依赖JQuery
//
//
(function ($) {
    if (!$.setCookie) {
        $.extend({
            setCookie: function (c_name, value, exdays) {
                try {
                    if (!c_name) return false;
                    var exdate = new Date();
                    exdate.setDate(exdate.getDate() + exdays);
                    var c_value = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
                    document.cookie = c_name + "=" + c_value;
                }
                catch (err) {
                    return '';
                };
                return '';
            }
        });
    };
    if (!$.getCookie) {
        $.extend({
            getCookie: function (c_name) {
                try {
                    var i, x, y,
                        ARRcookies = document.cookie.split(";");
                    for (i = 0; i < ARRcookies.length; i++) {
                        x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
                        y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
                        x = x.replace(/^\s+|\s+$/g, "");
                        if (x == c_name) return (y);
                    };
                }
                catch (err) {
                    return '';
                };
                return '';
            }
        });
    };
})(jQuery);
$(document).ready( function(){
	if($.getCookie("WIDTH") == "1"){
		$(".container").css("width","auto");
	}
	$("#setWidth").click(function(){
		if($(this).attr("class") == 'icon-enlarge2'){
			$.setCookie("WIDTH","1");
			$(this).attr("class","icon-shrink2");
			$(".container").css("width","auto");
		}else{
			$.setCookie("WIDTH","0");
			$(this).attr("class","icon-enlarge2");
			$(".container").css("width","");
		}
	})

	
	$(".js-info").each(function(){
		var _this = this;
		var pos = 'south';
		var attr = $(this).attr('pos');

		if(attr=='left')
			pos = 'east';
		else if(attr == 'right')
			pos = 'west';
		else if(attr == 'bottom')
			pos = 'north'
		$(_this).darkTooltip({
			size:'lg',
			gravity:pos,
			content:'<hr>adas ds das s',
			animation:'flipIn',
			ajax:www+'ajax'+exp+'userjson',
			ajaxdata:{
				uid:$(_this).attr('uid')
			}
		});
	})

});
