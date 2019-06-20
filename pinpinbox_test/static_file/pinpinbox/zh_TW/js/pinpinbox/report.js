$('div[id^=alert_radio_items]').click(function (evt) {
    var obj = $('input[name=report]'), chk_val = $('input[name=report]:checked').val();
    for (i = 0; i < obj.length; i++) {
        $('div[id^=alert_desc_items]').hide();
    }

    //依RADIO選項顯示其描述內容, 目前只有 7
    if (chk_val == 7) $('div[id=alert_desc_items07]').show();
    $("#alert_items").getNiceScroll().resize();
});

$(document).on('click', '#alert_btn', function (e) {
    alert_submit($(this).data('album_id'));
}).on('click', '.alert_btn', function (e) {
    e.stopPropagation();
    $('[data-toggle="dropdown"]').parent().removeClass('open');
    $('#alert_btn').data('album_id', $(this).data('type_id'));
    $('#alert_box_new').fadeIn();
    $('input[name=report]').removeAttr('checked');
    $('#input04_textarea').val('');
    $('div[id^=alert_desc_items]').hide(); //關閉所有描述內容
}).on('click', '#alert_close_btn', function (e) {
    $('#alert_box_new').fadeOut('slow', function () {});
}).on('click', '#alert_bg_new', function (e) {
    $('#alert_box_new').fadeOut(function () {
        $('#alert_close_btn').trigger('click');
    });
});

//當內容區塊過長時, 加NICESCROLL
var items_area = $("#alert_items").niceScroll({
    touchbehavior: true,
    enablemousewheel: true,
    cursorcolor: "transparent",
    cursoropacitymax: 0,
    cursorwidth: 10,
    autohidemode: false
});

//判斷是否是點擊選項, 或拖曳NICESCROLL區塊
var isDragging = false, startingPos = [0, 0];
$("#alert_items").mousedown(function (evt) {
    isDragging = false;
    startingPos = [evt.pageX, evt.pageY];
}).mousemove(function (evt) {
    if (!(evt.pageX === startingPos[0] && evt.pageY === startingPos[1])) {
        isDragging = true;
    }
}).mouseup(function (evt) {

});

//是否按下滑鼠右鍵
function right_click(evt) {
    var isRightMB;
    evt = evt || window.event;
    if ("which" in evt)  // Gecko (Firefox), WebKit (Safari/Chrome) & Opera
        isRightMB = evt.which == 3;
    else if ("button" in evt)  // IE, Opera
        isRightMB = evt.button == 2;
    return isRightMB;
}
