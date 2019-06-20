// JavaScript Document

//畫框縮放控制
$(document).ready(sizeContent);

//Every resize of window
$(window).resize(sizeContent);

//Dynamically assign height
function sizeContent() {
    var newHeight = $("html").height() - $("#header").height() - $("#editor_info").height() - 10 ;
    var menuHeight = $(".menu02").height() - $(".menu_info").height() - 20 ;
    var menuHeight02 = $(".menu").height() - $(".menu_info").height() - 20 ;
    var editHeight = $("#edit_in").height();
    var editWidth = $("#edit_in").width();
    $("#editor_wrap").css("height", newHeight + "px");
    $("#album_list").css("height", menuHeight + "px");
    $("#temp_list").css("height", menuHeight02 + "px");
    var wrapHeight = $("#editor_wrap").height()- 50 ;
    var scale = Math.min(1 , wrapHeight/editHeight);
    // early exit
    $('#edit_in').css({'transform': 'scale(' + scale + ')'});
    $('#edit_box').css({ width: editWidth * scale, height: editHeight * scale });

}


//MENU box 控制

$(document).ready(function() {
  $('.changeto2').click(function() {
      $('.menu02').css({'display': 'block'});
      $('.menu').css({'display': 'none'});
      $('#mask').removeClass('maskopen');
      $('#mask').addClass('maskclose');
      $('#mask02').removeClass('maskclose');
      $('#mask02').addClass('maskopen');
      if($('.menu02').hasClass('slide-menu-bottom02')){
        $('.menu02').removeClass('slide-menu-bottom02');
        $('.menu02').addClass('slide-menu-up');
      }      
  });

  $('.changeto1').click(function() {
      $('.menu02').css({'display': 'none'});
      $('.menu').css({'display': 'block'});
      $('.menu').removeClass('slide-menu-bottom');
      $('.menu').addClass('slide-menu-up');
      $('#mask02').removeClass('maskopen');
      $('#mask02').addClass('maskclose');
      $('#mask').removeClass('maskclose');
      $('#mask').addClass('maskopen');

  });


  $('.con_icon02').click(function() {
    if($('.menu02').css({'display': 'none'})) {
      $('.menu02').css({'display': 'block'});
      $('.menu').css({'display': 'none'});
      $('#mask02').addClass('maskopen');
      $('#mask02').removeClass('maskclose');
    }
    if($('.menu02').hasClass('slide-menu-bottom02')){
      $('.menu02').removeClass('slide-menu-bottom02');
      $('.menu02').addClass('slide-menu-up');
    }

  });
  $('.con_icon04').click(function() {
    if($('.menu').css({'display': 'none'})) {
      $('.menu').css({'display': 'block'});
      $('.menu02').css({'display': 'none'});
      $('#mask').addClass('maskopen');
      $('#mask').removeClass('maskclose');
    }
    if($('.menu').hasClass('slide-menu-bottom')){
      $('.menu').removeClass('slide-menu-bottom');
      $('.menu').addClass('slide-menu-up');
    }

  });
  $('#menu_open').click(function() {

      if($('.menu').hasClass('slide-menu-bottom')) {
        $('.menu').addClass('slide-menu-up', 1000, 'easeOutQuint');
        $('#mask').addClass('maskopen');
        $('#mask').removeClass('maskclose');
        $('.menu').removeClass('slide-menu-bottom'); 
      } 

      else {
        $('.menu').removeClass('slide-menu-up');
        $('#mask').removeClass('maskopen');
        $('#mask').addClass('maskclose');
        $('.menu').addClass('slide-menu-bottom', 1000, 'easeOutQuint'); 
      };
  
    });
   $('#menu_open02').click(function() {

      if($('.menu02').hasClass('slide-menu-up')) {
        $('.menu02').addClass('slide-menu-bottom02', 1000, 'easeOutQuint');
        $('#mask02').addClass('maskclose');
        $('#mask02').removeClass('maskopen');
        $('.menu02').removeClass('slide-menu-up');
        $('#mask').addClass('maskclose');
        $('#mask').removeClass('maskopen'); 
      } 

      else {
        $('.menu02').removeClass('slide-menu-bottom02');
        $('#mask02').removeClass('maskclose');
        $('#mask02').addClass('maskopen');
        $('.menu02').addClass('slide-menu-up', 1000, 'easeOutQuint'); 
      };
  
    });
  $('#mask').click(function() {
      if($('#mask').hasClass('maskopen')) {
        $('.menu').removeClass('slide-menu-up');
        $('#mask').addClass('maskclose');
        $('#mask').removeClass('maskopen');
         $('.menu').addClass('slide-menu-bottom', 1000, 'easeOutQuint'); 
      }
    });

  $('#mask02').click(function() {
      if($('#mask02').hasClass('maskopen')) {
        $('.menu02').removeClass('slide-menu-up');
        $('.menu02').addClass('slide-menu-bottom02');
        $('#mask02').addClass('maskclose');
        $('#mask02').removeClass('maskopen');
        $('#mask').addClass('maskclose');
        $('#mask').removeClass('maskopen');
      }
    });
});

//相本順序控制
(function($, window, undefined) {
  /*
  * Sortable by Converted Figures
  */
  $('#album_list').sortable({
    
    cursor: "move",
    opacity: 0.8,
    items: '.menu_item',
    containment: 'parent',
    update: function(event, ui) {
      // YOU CAN WRITE SOME UPDATE CODE HERE BY AJAX OR SUMMIT
    },
    stop: function(event, ui) {
      $('.menu_item').each(function(k, v) {
        $(v).find('.file-num').text('p'+(k+1));
      });

    }
  });
})(jQuery, window);



