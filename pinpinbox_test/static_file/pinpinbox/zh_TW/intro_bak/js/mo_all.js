//控制畫框高度
$(document).ready(sizeContent);

//Every resize of window
$(window).resize(sizeContent);

//Dynamically assign height
function sizeContent() {
    var newHeight = $(window).height();

    $(".section").css("height", newHeight + "px");

}

var s = skrollr.init();//初始化