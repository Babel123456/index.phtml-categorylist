//2016-08-15 Lion: subhtml
(function($, window, document, undefined) {

    'use strict';

    var defaults = {
    	subhtml: true
    };

    var SubHtml = function(element) {
        this.core = $(element).data('lightGallery');

        this.core.s = $.extend({}, defaults, this.core.s);
        
        if (this.core.s.subhtml) {
            this.init();
        }
        
        return this;
    };
    
    SubHtml.prototype.init = function() {
    	var _this = this;
    	
    	if ($('.lg-toogle-subhtml').length === 0) {
    		_this.core.$outer.append('<span class="lg-toogle-subhtml lg-icon"></span>');
    		_this.core.$outer.find('.lg-toogle-subhtml').on('click.lg', function(){
    			_this.core.$outer.toggleClass('lg-subhtml-open');
         	});
    	}
    };
    
    SubHtml.prototype.build = function() {
    	var _this = this;
    	
    	if (_this.core.s.subhtml) {
    		if (_this.core.$outer.find(_this.core.s.appendSubHtmlTo).hasClass('lg-empty-html')) {
    			_this.core.$outer.removeClass('lg-subhtml-open').find('.lg-toogle-subhtml').hide();
    		} else {
    			_this.core.$outer.addClass('lg-subhtml-open').find('.lg-toogle-subhtml').show();
    		}
    	}
    };
    
    SubHtml.prototype.destroy = function() {
    };
    
    $.fn.lightGallery.modules.subhtml = SubHtml;

})(jQuery, window, document);