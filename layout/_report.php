
<div id="alert_box_new">
  <div id="alert_bg_new"></div>
  <div id="alert_box_area">
	<div id="alert_top_new">
	  <div id="alert_top_title">檢舉此作品</div>
	  <div id="alert_close_btn"><i class="fa fa-times"></i></div>
	</div>
	<div id="alert_content_new">
	  <div id="alert_items">
		  <div id="alert_items01">
		    <div id="alert_radio_items01"><input type="radio" value="1" id="input_01" name="report">
		      <label id="input_01" onclick="$(this).prev('input').click();">色情內容</label>
			</div>
		    <div id="alert_desc_items01"></div>
		  </div>
		  <div id="alert_items02">
		    <div id="alert_radio_items02"><input type="radio" value="2" id="input_02" name="report">
		      <label id="input_02" onclick="$(this).prev('input').click();">暴力或令人厭惡的內容</label>
			</div>
		    <div id="alert_desc_items02"></div>
		  </div>
		  <div id="alert_items03">
		    <div id="alert_radio_items03"><input type="radio" value="3" id="input_03" name="report">
		      <label id="input_03" onclick="$(this).prev('input').click();">仇恨或惡意內容</label>
			</div>
		    <div id="alert_desc_items03"></div>
		  </div>
		  <div id="alert_items04">
		    <div id="alert_radio_items04"><input type="radio" value="4" id="input_04" value="04" name="report">
		      <label id="input_04" onclick="$(this).prev('input').click();">有害的危險行為</label>
			</div>
		    <div id="alert_desc_items04"><textarea id="input04_textarea" rows="4" placeholder="詳細描述"></textarea></div>
		  </div>
		  <div id="alert_items05">
		    <div id="alert_radio_items05"><input type="radio" value="5" id="input_05" name="report">
		      <label id="input_05" onclick="$(this).prev('input').click();">虐待兒童</label>
			</div>
		    <div id="alert_desc_items05"></div>
		  </div>
		  <div id="alert_items06">
		    <div id="alert_radio_items06"><input type="radio" value="6" id="input_06" name="report">
		      <label id="input_06" onclick="$(this).prev('input').click();">垃圾內容或內容與標題不符</label>
			</div>
		    <div id="alert_desc_items06"></div>
		  </div>
		  <div id="alert_items07">
		    <div id="alert_radio_items07"><input type="radio" value="7" id="input_07" name="report">
		      <label id="input_07" onclick="$(this).prev('input').click();">侵犯我的權利</label>
			</div>
		    <div id="alert_desc_items07">
			  
		      <p>如果您確信有人在未獲授權的情況下，擅自在pinpinbox 上發佈您受版權保護的作品，這時可以提出侵犯版權通知。只有著作權人或其授權代理人可以提交這類要求。</p>
		      <p>如要向 pinpinbox 通報涉嫌侵權的，最簡單快速的做法就是填寫網路表單。建議使用桌機和筆電填寫這份表單；在行動裝置及平板電腦上操作會較為繁複。</p>
		      <p>如果您選擇提出侵權通知，要求我們移除相關內容，代表您採取了正式的法律程式。</p>
		      <p class="red">請勿提出不實聲明，當送出此項檢舉即表示您已閱讀、瞭解並同意上述說明。濫用這項程式可能會導致您的帳戶遭到停權，您本人也可能因此需要承擔其他法律責任。</p>
			
			</div>
		  </div>
		  <div id="alert_items08">
		    <div id="alert_radio_items08"><input type="radio" value="8" id="input_08" name="report">
		      <label id="input_08" onclick="$(this).prev('input').click();">文字相關檢舉</label>
			</div>
		    <div id="alert_desc_items08"></div>
		  </div>
		  <div id="alert_items09">
		    <div id="alert_radio_items09"><input type="radio" value="9" id="input_09" name="report">
		      <label id="input_09" onclick="$(this).prev('input').click();">詐騙內容</label>
			</div>
		    <div id="alert_desc_items09"></div>
		  </div>
	    
	  </div>
	  <div id="alert_desc_btn">
        <div class="btn_new btn_pink" id="alert_btn">送出</a></div>
	  </div>
	  
	  
	  
	</div><!-- #alert_content_new -->
  </div><!-- #alert_box_area -->
</div><!-- #alert_box_new -->

<script>

//依RADIO選項顯示其描述內容區塊
$('div[id^=alert_radio_items]').click(function(evt) {
  var obj = $('input[name=report]');
  for(i=0;i < obj.length;i++){
    $('div[id^=alert_desc_items]').hide();
  }
  var chk_val = $('input[name=report]:checked').val();
  //依RADIO選項顯示其描述內容, 目前只有4, 7
  if(chk_val==7){
    $('div[id=alert_desc_items07]').show();
  }
  $("#alert_items").getNiceScroll().resize();
});

$(document).on('click', '#alert_btn' , function(e){
	alert_submit($(this).data('album_id'));
}).on('click', '#alert_btn' , function(e){
	e.stopPropagation();
	$('#alert_btn').data('album_id', $(this).data('album_id'));
	$('#alert_box_new').fadeIn();
	$('input[name=report]').removeAttr('checked');
	$('#input04_textarea').val('');
	$('div[id^=alert_desc_items]').hide(); //關閉所有描述內容
}).on('click', '#alert_close_btn' , function(e){
	$('#alert_box_new').fadeOut('slow', function() {
		
	});
}).on('click', '#alert_bg_new' , function(e){
	$('#alert_box_new').fadeOut(function() {
		$('#alert_close_btn').trigger('click');
	});
});


function alert_submit(id) {
  var obj = $('input[name=report]:checked');
  var text = '';
  if (obj.length < 1) {
  	alert('請選擇一個檢舉意向。');
  } else {
  	var value = obj.val();
  	if (value == 4) {
  		text = $('#input04_textarea').val();
  	}
  	$.post('https://www.pinpinbox.com/index/album/report/', {
  		value: value,
  		text: text,
  		album_id: id,
  		url : window.location.href ,
  	}, function(r) {
  		r = $.parseJSON(r);
  		if (r.result == 1) {
  			$('#alert_bg_new').trigger('click');
  			alert(r.message);
  		} else if(r.result == 2) {
  			$('#alert_bg_new').trigger('click');
  			location.href="https://www.pinpinbox.com/index/user/login/?redirect=https%253A%252F%252Fwww.pinpinbox.com%252Findex%252Falbum%252Fcontent%252F%253Freport%253D1%2526album_id%253D"+id;
  		} else {
  			$('#alert_bg_new').trigger('click');
  			alert(r.message);
  		}
  	});
  }
}


    //當內容區塊過長時, 加NICESCROLL
	var items_area = $("#alert_items").niceScroll({
        touchbehavior: true,
        enablemousewheel: true,
        cursorcolor: "transparent",
        cursoropacitymax: 0,
        cursorwidth: 10,
		autohidemode:false
    });
		
	//判斷是否是點擊選項, 或拖曳NICESCROLL區塊
	var isDragging=false, startingPos=[0,0];
    $("#alert_items").mousedown(function (evt){
        isDragging = false;
        startingPos = [evt.pageX, evt.pageY];
    }).mousemove(function (evt){
        if (!(evt.pageX === startingPos[0] && evt.pageY === startingPos[1])) {
            isDragging = true;
        }
    }).mouseup(function (evt){
		
	});
	
	
	//是否按下滑鼠右鍵
	function right_click(evt){
	  var isRightMB;
      evt = evt || window.event;
      if ("which" in evt)  // Gecko (Firefox), WebKit (Safari/Chrome) & Opera
        isRightMB = evt.which == 3; 
      else if ("button" in evt)  // IE, Opera 
        isRightMB = evt.button == 2; 
      return isRightMB;
	}
</script>