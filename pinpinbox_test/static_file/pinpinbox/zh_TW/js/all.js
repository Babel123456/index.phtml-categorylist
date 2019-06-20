// JavaScript Document
$("*").dblclick(function (event)
{
    event.preventDefault();
    
});

//控制dropdown選單

$('.tips_control').on('click', function(event) {
    event.stopPropagation();
   
  if($('.tips_image').is(':visible')){
    $('.tips_image').slideUp();
    $(".tips_control a").html("<img src='images/push_down.png'  style='margin-right:10px; vertical-align:middle;'>展開教學範例");
   
    
} else {
    $('.tips_image').slideDown();
     $(".tips_control a").html("<img src='images/push_up.png'   style='margin-right:10px; vertical-align:middle;'>收起教學範例");
     
}        

});

// $('#member_open').on('mouseover', function(event) {
//     event.stopPropagation();

//     $('.member_nav').show();
// });
// $('.member_hide').on('mouseout', function(event) {
//     event.stopPropagation();

//     $('.member_nav').hide();
// });
// $('html').on('click', function() {
//     $('.member_nav').hide();
// });

$('#head_lang').on('click', function(event) {
    event.stopPropagation();
    $('.member_nav02').toggle();
});
$('html').on('click', function() {
    $('.member_nav02').hide();
});
$('.alert_btn').on('click', function(event) {
    event.stopPropagation();
    $('#alert_box').fadeIn();
});
$('#close_alert').on('click', function() {
    $('#alert_box').fadeOut();
});
$('#alert_bg').on('click', function() {
    $('#alert_box').fadeOut();
});
$('.pop_al_btn').on('click', function(event) {
    event.stopPropagation();
    $('#album_s').fadeIn();
});
$('#close_al').on('click', function() {
    $('#album_s').fadeOut();
});
$('.popup_bg').on('click', function() {
    $('#album_s').fadeOut();
});
$("#scroller ul li a").click(function (e) { // binding onclick
    if ($(this).parent().hasClass('selected')) {
              $(".selected ul").slideUp(100); // hiding popups
               $("#scroller .selected").removeClass("selected", 100, 'easeOutQuint');
           } else {
              $(".selected ul").slideUp(100); // hiding popups
               $("#scroller .selected").removeClass("selected", 100, 'easeOutQuint');

                if ($(this).next("ul").length) {
                    $(this).parent().addClass("selected", 100, 'easeOutQuint'); // display popup
                    $(".selected ul").slideDown(100);
                }
            }
             e.stopPropagation();
});
$("body").click(function () { // binding onclick to body
    $(".selected ul").slideUp(100); // hiding popups
    $("#scroller .selected").removeClass("selected");
});


//控制溢出文字加"..."
$(".name_more").each(function(i) {
    var divH = $(this).height();
    var $p = $("p", $(this)).eq(0);
    while ($p.outerHeight() > divH) {
        $p.text($p.text().replace(/(\s)*([a-zA-Z0-9]+|\W)(\.\.\.)?$/, "..."));
    };
});
$(".follow_txt").each(function(i) {
    var divH = $(this).height();
    var $p = $("span", $(this)).eq(0);
    while ($p.outerHeight() > divH) {
        $p.text($p.text().replace(/(\s)*([a-zA-Z0-9]+|\W)(\.\.\.)?$/, "..."));
    };
});
$(".act_info").each(function(i) {
    var divH = $(this).height();
    var $p = $("p", $(this)).eq(0);
    while ($p.outerHeight() > divH) {
        $p.text($p.text().replace(/(\s)*([a-zA-Z0-9]+|\W)(\.\.\.)?$/, "..."));
    };
});
$(".info_name h3").each(function(i) {
    var divH = $(this).height();
    var $p = $("a", $(this)).eq(0);
    while ($p.outerHeight() > divH) {
        $p.text($p.text().replace(/(\s)*([a-zA-Z0-9]+|\W)(\.\.\.)?$/, "..."));
    };
});

//控制導覽列隱藏


//控制mobile導覽列
$(document).ready(sizeContent);

//Every resize of window
$(window).resize(sizeContent);

//Dynamically assign height
function sizeContent() {
    var newHeight = $(window).height() - $("#login_no").height();
    var imageHeight = $(".album_list").width() / 2 * 3;
    var imageHeight02 = $(".top_img").width() / 2 * 3;
    var imageHeight03 = $(".tem_imgbox").width() / 2 * 3;
    var imageHeight04 = $(".vote_btn").width();
    var wateritemwidth = $(".item_box").width()*2 +10;
    var imageHeight05 = $(".top_face").width() + 10;
    var coverheight = $(".upload_photo").width() / 2 * 3;
    var newHeight02 = $(window).height() - 50 - 60;
    $("#nav_wrapper").css("height", newHeight + "px");
    
    $(".temimg").css("height", imageHeight03 + "px");
    $(".top_img img").css("height", imageHeight02 + "px");

    $(".top_face").css("height", imageHeight05 + "px");
    $(".top_face").css("border-radius", imageHeight05 + "px");
    $("#alert_content").css("max-height", newHeight02 + "px");
    // $(".upload_photo").css("height", coverheight + "px");
    

}


//控制mobile物件滑動

$('#menu2_open').on('click', function() {
    $('#head_nav02').fadeIn();
});
$('#close_nav02').on('click', function() {
    $('#head_nav02').fadeOut();
});
$(document).ready(function() {

    // var nicesx = $(".tab_content").niceScroll({
    //     touchbehavior: true,
    //     cursorcolor: "transparent",
    //     cursoropacitymax: 0.6,
    //     cursorwidth: 8
    // });
    var nicesx = $("#temp_else, #cate_slide, #scroll_ablum, .pop_al_bottom").niceScroll({
        touchbehavior: true,
        cursorcolor: "#9d9d9d",
        cursoropacitymax: 0.6,
        cursorwidth: 8
    });
});

//控制廣告牆slide
// $('.bxslider').bxSlider({
//     mode: 'horizontal',
//     captions: true,
//     auto: true,

// });
$('.bxslider02').bxSlider({
    mode: 'horizontal',
    captions: true,
    speed: 800,
    adaptiveHeight: true,
    controls:($(".bxslider02 li").length > 1) ? true: false,
    auto: ($(".bxslider02 li").length > 1) ? true: false,


});

//控制tab切換

jQuery(document).ready(function() {
    jQuery('.etabs a').on('click', function(e) {
        var currentAttrValue = jQuery(this).attr('href');

        if(currentAttrValue.indexOf('#')>=0) {

            // Show/Hide Tabs
            jQuery('#tab-container ' + currentAttrValue).fadeIn(400).siblings().hide();

            // Change/remove current tab to active
            jQuery(this).parent('li').addClass('active').siblings().removeClass('active');

            e.preventDefault();
        }

    });
});

jQuery(document).ready(function() {
    jQuery('.etabs02 a').on('click', function(e) {
        var currentAttrValue = jQuery(this).attr('href');

        // Show/Hide Tabs
        jQuery('#tab-container ' + currentAttrValue).fadeIn(400).siblings().hide();

        // Change/remove current tab to active
        jQuery(this).parent('li').addClass('active').siblings().removeClass('active');

        e.preventDefault();
    });
});


//控制會員中心功能列浮動


//$(window).scroll(function() {

  //  if ($(window).scrollTop() + $(window).height() == $(document).height()) {

    //    $('#member_left').css({
      //      'position': 'absolute'
        //});
    //} else {

      //  $('#member_left').css({
        //    'position': 'fixed'
        //});
   // }
//});


// <![CDATA[


$('input:radio[name="report"]').change(function() {
    if ($(this).val() == '04') {
        $(".hide").show();
    } else {
        $(".hide").hide();
    }
});
$('.step02').hide();
$('#step_open').on('click', function(event) {
    $('.step02').show();
    $('.step01').hide();
});


// ]]>


//回頂部按鈕

$(function(){
    $("#scroll_top").click(function(){
        jQuery("html,body").animate({
            scrollTop:0
        },1000);
    });
    $(window).scroll(function() {
        if ( $(this).scrollTop() > 300){
            $('#scroll_top').fadeIn("fast");
        } else {
            $('#scroll_top').stop().fadeOut("fast");
        }
    });

});

