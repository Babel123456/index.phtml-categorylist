//2016-08-11 Lion: audio
(function($, window, document, undefined) {

    'use strict';

    var defaults = {
    	audio: true
    };

    var Audio = function(element) {
        this.core = $(element).data('lightGallery');

        this.core.s = $.extend({}, defaults, this.core.s);

        if (this.core.s.audio) {
            this.init();
        }
        
        this.playing = false;
        this.playingFlag = false;

        return this;
    };
    
    Audio.prototype.init = function() {
        var _this = this;
        
        if ($('#lg-audio-button').length === 0) {
        	_this.core.$outer.find('.lg-toolbar').append('<span class="lg-icon lg-audio-off" id="lg-audio-button"></span>');
        }
        
        if ($('#lg-audio').length === 0) {
        	_this.core.$outer.append(
        		'<div class="lg-audio-mask">' +
        			'<div class="lg-audio-area">' +
        				'<audio controls id="lg-audio"><source src="" type="audio/mpeg">Your browser does not support the audio element.</audio>' +
        			'</div>' +
        		'</div>'
        	);
        }
        
        $('.lg-audio-mask').hide();
        
        _this.core.$outer.on('click', '#lg-audio-button', function(){
        	$('.lg-audio-mask').fadeIn();
        }).on('click', '.lg-audio-mask', function(e){
        	e.stopPropagation();
        	$(this).fadeOut();
        }).on('click', '.lg-audio-area', function(e){
        	e.stopPropagation();
        });
        
    	$('#lg-audio').on({
    		play: function(){
    			_this.playing = true;
        		$('#lg-audio-button').removeClass('lg-audio-off').addClass('lg-audio-on');
        	},
        	pause: function(){
        		_this.playing = false;
        		$('#lg-audio-button').removeClass('lg-audio-on').addClass('lg-audio-off');
        	},
    	});
    };
    
    Audio.prototype.build = function() {
    	var _this = this;
    	
    	if (_this.core.s.audio) {
    		var _audio = _this.core.s.dynamicEl[_this.core.index].audio, $audio = $('#lg-audio');
    		
            $('.lg-audio-mask').fadeOut(function(){
            	if (_audio === undefined || _audio.src === null) {
            		$('#lg-audio-button').hide();
                	$audio.find('source').attr('src', '').end().removeAttr('loop').trigger('pause').prop('currentTime', 0).trigger('load');
                } else {
                	$('#lg-audio-button').show();
                	
                	switch (_audio.mode) {
                		case 'singular':
                			if ($audio.find('source').attr('src').length === 0) $audio.find('source').attr('src', _audio.src);
                			
                        	if (_this.core.$slide.eq(_this.core.index).find('.lg-video').length) {
                        		if (_this.playing) _this.playingFlag = true;
                        		$audio.trigger('pause');
                        	} else {
                        		if (_this.playingFlag) {
                        			_this.playingFlag = false;
                        			$audio.trigger('play');
                        		}
                        	}
                			break;
                 		
                		case 'plural':
                			$audio.find('source').attr('src', _audio.src).end().removeAttr('loop').trigger('pause').prop('currentTime', 0).trigger('load');
                			break;
                	}
                	
                	if (_audio.loop) $audio.attr('loop', true);
                }
            });
    	}
    };
    
    Audio.prototype.destroy = function() {
    };
    
    $.fn.lightGallery.modules.audio = Audio;

})(jQuery, window, document);