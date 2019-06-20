$('nav a').on('click', function() {

    var scrollAnchor = $(this).attr('data-scroll'),
        scrollPoint = $('section[data-anchor="' + scrollAnchor + '"]').offset().top+100;

    $('body,html').animate({
        scrollTop: scrollPoint
    }, 500);

    return false;

})


$(window).scroll(function() {
    var windscroll = $(window).scrollTop();
    if (windscroll >= 0) {
       
        $('#wrapper section').each(function(i) {
            if ($(this).position().top <= windscroll) {
                $('nav a.active').removeClass('active');
                $('nav a').eq(i).addClass('active');
            }
        });

    } else {

       
        $('nav a.active').removeClass('active');
        $('nav a:first').addClass('active');
    }

}).scroll();

//控制畫框高度
$(document).ready(sizeContent);

//Every resize of window
$(window).resize(sizeContent);

//Dynamically assign height
function sizeContent() {
    var newHeight = $(window).height();

    $("section").css("min-height", newHeight + "px");

}

var s = skrollr.init();//初始化