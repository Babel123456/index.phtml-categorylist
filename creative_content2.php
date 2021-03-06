
<!doctype html>
<html lang="zh">
<head>
    <link rel="icon" type="image/vnd.microsoft.icon" href="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/favicon.ico"/>
    <link rel="manifest" href="https://www.pinpinbox.com/manifest.json">
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width ,initial-scale=1.0">
	<link type="text/css" href="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/css/bootstrap.min.css" rel="stylesheet" />
	<link type="text/css" href="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/css/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <!--
	<link type="text/css" href="./css/style_v2.css" rel="stylesheet" />
    -->
	<link type="text/css" href="style2.css" rel="stylesheet" />
	
    <!-- 20190121 加入原有的DEEPLINK FUNCTION開始 -->
	<script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/browser-deeplink-master/browser-deeplink.min.js"></script>
	<!-- 20190121 加入原有的DEEPLINK FUNCTION結束 -->

</head>
<body>
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/jquery-1.11.3.min.js"></script>
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/bootstrap.min.js"></script>
  <!-- 表頭區塊開始 -->
  <div id="header">
  <!-- 手機版搜尋區塊開始 -->
    <div id="mobile_search">
	  <div class="header_search">
		  <div class="header_search_box">
		      <!-- 20190114 加入<a>與MARS v2同步開始 -->
			  <a href="javascript:void(0);" onclick="_search('mobile')"><i class="fa fa-search"></i></a>
			  <!-- 20190114 加入<a>與MARS v2同步結束 -->
			  <!-- 20190114 加入id, data-searchtype與MARS v2同步開始 -->
			  <input type="text" class="search_text" name="searchkey" id="mobile_searchkey" data-searchtype="user" data-device="mobile" autocomplete="off">
              <!-- 20190114 加入id, data-searchtype與MARS v2同步結束 -->
			  
			  <!-- 20190114 移掉選單區塊, 改用BOOTSTRAP選單開始 -->
			  <!--
			  <div class="search_btn"><span id="search_type" data-searchtype="user">創作人</span> <i class="fa fa-caret-down"></i>
			   <div class="search_menu">
				      <ul>
					    <li><a href="javascript:void(0)" data-searchtype="user">創作人</a></li>
					    <li><a href="javascript:void(0)" data-searchtype="album">作品</a></li>
				      </ul>
				  </div>
			  </div>
			  -->
			  
			  <div class="search_btn">
			    <!-- 20190114 加入BOOTSTRAP DIV區塊, 將SPAN ID換成CLASS開始 -->
			    <div class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
				  <span class="search_type" data-searchtype="user">創作人</span> <i class="fa fa-caret-down"></i>
				</div>
				<!-- 20190114 加入BOOTSTRAP DIV區塊, 將SPAN ID換成CLASS結束 -->
				<!-- 20190114 置換BOOTSTRAP CLASS開始 -->
			    <!--<div class="search_menu">-->
			    <div class="dropdown-menu dropdown_menu" role="menu">
				<!-- 20190114 置換BOOTSTRAP CLASS結束 -->
				  <ul>
				    <!-- 20190114 在<a>加入class="searchtype", data-...與MARS v2同步開始 -->
					<li><a href="javascript:void(0)"  class="searchtype" data-searchtype="user" data-device="mobile">創作人</a></li>
			        <li><a href="javascript:void(0)"  class="searchtype" data-searchtype="album" data-device="mobile">作品</a></li>
					<!-- 20190114 在<a>加入class="searchtype"與MARS v2同步結束 -->
			      </ul>
				</div>
			  </div>
			  
			  <!-- 20190114 移掉選單區塊, 改用BOOTSTRAP選單結束 -->
			  
		  </div>
		</div>
	  <div class="header_search_close"><i class="fa fa-times"></i></div>
	</div>
	<!-- 電腦版搜尋區塊開始 -->
	  <div id="header_top">
		<div class="header_logo"><a href="https://www.pinpinbox.com/">pinpinbox釘釘吧</a></div>
		<div class="header_search">
		  <div class="header_search_box">
			  <!-- 20190114 加入<a>與MARS v2同步開始 -->
			  <a href="javascript:void(0);" onclick="_search('desktop')"><i class="fa fa-search"></i></a>
			  <!-- 20190114 加入<a>與MARS v2同步結束 -->
			  <!-- 20190114 加入id, data-searchtype與MARS v2同步開始 -->
			  <input type="text" class="search_text" name="searchkey" id="desktop_searchkey" data-searchtype="user" data-device="desktop" autocomplete="off">
              <!-- 20190114 加入id, data-searchtype與MARS v2同步結束 -->
			  
			  <!-- 20190114 移掉選單區塊, 改用BOOTSTRAP選單開始 -->
			  <!--
			  <div class="search_btn"><span class="search_type" data-searchtype="user">創作人</span> <i class="fa fa-caret-down"></i>
			   <div class="search_menu">
				      <ul>
					    <li><a href="javascript:void(0)" data-searchtype="user">創作人</a></li>
					    <li><a href="javascript:void(0)" data-searchtype="album">作品</a></li>
				      </ul>
				  </div>
			  </div>
			  -->
			  
			  <div class="search_btn">
			    <!-- 20190114 加入BOOTSTRAP DIV區塊, 將SPAN ID換成CLASS開始 -->
			    <div class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
				  <span class="search_type" data-searchtype="user">創作人</span> <i class="fa fa-caret-down"></i>
				</div>
				<!-- 20190114 加入BOOTSTRAP DIV區塊, 將SPAN ID換成CLASS結束 -->
				<!-- 20190114 置換BOOTSTRAP CLASS開始 -->
			    <!--<div class="search_menu">-->
			    <div class="dropdown-menu dropdown_menu" role="menu">
				<!-- 20190114 置換BOOTSTRAP CLASS結束 -->
				  <ul>
				    <!-- 20190114 在<a>加入class="searchtype", data-...與MARS v2同步開始 -->
					<li><a href="javascript:void(0)"  class="searchtype" data-searchtype="user" data-device="desktop">創作人</a></li>
			        <li><a href="javascript:void(0)"  class="searchtype" data-searchtype="album" data-device="desktop">作品</a></li>
					<!-- 20190114 在<a>加入class="searchtype"與MARS v2同步結束 -->
			      </ul>
				</div>
			  </div>
			  
			  <!-- 20190114 移掉選單區塊, 改用BOOTSTRAP選單結束 -->
		  </div>
		</div>
		<!-- 20181116: 改字與加"P好康"開始 -->
		<!-- //20190114: 將目P點與購買移至頭圖右邊開始 -->
		<!--<div class="pin_creator">-->
		  <!--<a href="https://www.pinpinbox.com/index/user/point/" title="立即購買Ｐ點贊助">購買Ｐ點</a>-->
		  <!--目前P點:41225&nbsp;&nbsp;<a href="https://www.pinpinbox.com/index/user/point/">購買</a>
		</div>-->
		<!-- //20190114: 將目P點與購買移至頭圖右邊結束 -->
		<div class="pin_gift">
		  <!-- //20190114: 改樣式名稱開始 -->
		  <div class="pin_gift_style"><a href="index.php#p_bannner" title="賺P點 抽好康 買好物">Ｐ好康</a></div>
		  <!-- //20190114: 改樣式名稱結束 -->
		  </div>
		<!-- 20181116: 改字與加"P好康"結束 -->
		<!-- 電腦版選單開始 -->
		<div class="header_menu">
		  <a href="#">建立作品</a><a href="#" id="notifier">通知<span class="notifier_icon"></span></a>
		  <!--<a href="#" id="login">登入 | 註冊</a>-->
		  <a href="javascript:void(0);" id="login">
            <img onerror="this.src='https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/face_sample.png'" src="https://cdn.pinpinbox.com/storage/zh_TW/user/4581/picture$18d7.jpg">
          </a>
		</div>
		<!-- //20190114: 將目P點與購買移至頭圖右邊與加入DIV區塊開始 -->
		<div class="pin_creator">
		  <!--<a href="https://www.pinpinbox.com/index/user/point/" title="立即購買Ｐ點贊助">購買Ｐ點</a>-->
		  <div class="current_point">目前P點：41225</div>
		  <div class="buy_point"><a href="https://www.pinpinbox.com/index/user/point/">購買P點</a></div>
		</div>
		<!-- //20190114: 將目P點與購買移至頭圖右邊與加入DIV區塊結束 -->
		<!-- 電腦版選單開始 -->
		<!-- 手機版選單開始 -->
		<div class="mobile_menu">
		  <div class="mobile_img"><i class="fa fa-search"></i><i id="notifier_m" class="fa fa-bell"></i><i id="login_m" class="fa fa-bars" ></i></div>
		</div>
		  <!-- 手機版選單結束 -->
	  </div>
	  <!-- //20181005: 新加入區塊開始 -->
	  <div id="login_menu">
	    <ul>
		<!--
		<li><div><ul>
			  <li><a href="#">Hi, 訪客</a></li>
			  <li><a href="#">登入</a></li>
			  <li><a href="#">註冊</a></li>
			</ul></div><div></div>
		  </li>
		  -->
		  <li><div>babelbabelbabelbabelbabelbabel</div><div>
		    <ul>
			  <!-- //20181221: #0002269, 關於我 改成 我的專區頻道-->
			  <li><a href="creative_content.php#tab3">我的專區頻道</a></li>
			  <!-- -->
			  <li><a href="creative_content.php#tab0">我的作品</a></li>
			  <li><a href="creative_content.php#tab1">我的收藏</a></li>
			  <li><a href="creative_content.php#tab2">共用協作</a></li>
			  <li><a href="creative_content.php#tab4">留言板</a></li>
			</ul></div>
		  </li>
		  <li><div>管理中心</div><div>
			<ul>
			   <li><a href="#">積分管理</a></li>
			   <li><a href="#">P點購買、消費查詢</a></li>
			   <li><a href="#">會員資料</a></li>
			</ul></div>
		  </li>
		  <li><div>系統</div><div>
		    <ul>
			   <li><a href="#">回報問題</a></li>
			   <li><a href="#">回報記錄</a></li>
			</ul></div>
		  </li>
		</ul>
		<a href="#"><div class="btn_new">登出</div></a>
	  </div>
	  <div id="notifier_menu">
		<ul class="noticeWarpper">
		  <li><a href="#">
		      <div class="notifier_img_c"><img src="https://cdn.pinpinbox.com/storage/zh_TW/user/44/picture$f673.jpg"></div>
		      <div class="notifier_info"><span>李登輝邀請您共用作品!立即進入?</span>
			  <span><time datetime="2018-10-16 12:59:30" class="timeago"></time></span></div></a></li>
		  <li><a href="#">
		      <div class="notifier_img_w"><img src="https://cdn.pinpinbox.com/storage/zh_TW/user/3874/cover$f078.jpg"></div>
			  <div class="notifier_info"><span>Kevin發佈了新作品[ABCDE]</span>
			  <span><time datetime="2019-10-16 12:59:05" class="timeago"></time></span></div></a></li>
		  <li><a href="#">
		      <div class="notifier_img_w"><img src="https://cdn.pinpinbox.com/storage/zh_TW/user/61/cover$e1cb.jpg"></div>
			  <div class="notifier_info"><span>周潤發發佈了新作品[賭神Beta]</span>
			  <span><time datetime="2017-10-16 12:58:57" class="timeago"></time></span></div></a></li>
		</ul>
		<a href="#"><div class="btn_new">更多</div></a>
	  </div>
  </div>
  <!-- 表頭區塊結束 -->
  <link type="text/css" href="https://ppb.sharemomo.com/static_file/pinpinbox/zh_TW/js/jquery-textcomplete/media/stylesheets/textcomplete.css" rel="stylesheet" />
  
  <link type="text/css" href="https://www.pinpinbox.com/js/jBox-0.4.8/jBox.css" rel="stylesheet" />
  <link type="text/css" href="https://www.pinpinbox.com/js/jBox-0.4.8/plugins/Confirm/jBox.Confirm.css" rel="stylesheet" />
  
  <link type="text/css" href="https://ppb.sharemomo.com/static_file/pinpinbox/zh_TW/js/lightGallery-master/dist/css/lightgallery.min.css" rel="stylesheet" />
  <link type="text/css" href="https://ppb.sharemomo.com/static_file/pinpinbox/zh_TW/js/lightGallery-master/dist/css/lightgallery-custom.min.css" rel="stylesheet" />
  
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/waypoint/js/waypoint.js"></script>
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/counterjs/js/jquery.counterup.min.js"></script>

  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/imagesloaded.pkgd.min.js"></script>
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/masonry/js/masonry.pkgd.min.js"></script>
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/jquery.infinitescroll.min.js"></script>
  
  <script type="text/javascript" src="https://ppb.sharemomo.com/static_file/pinpinbox/zh_TW/js/jquery-textcomplete/jquery.textcomplete.js"></script>
  <script type="text/javascript" src="https://ppb.sharemomo.com/static_file/pinpinbox/zh_TW/js/jquery-textcomplete/jquery.overlay.js"></script>
  
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/jquery-timeago-master/js/jquery.timeago.js"></script>
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/jquery-timeago-master/js/jquery.timeago.zh-TW.js"></script>
  
  <script type="text/javascript" src="https://www.pinpinbox.com/js/jBox-0.4.8/jBox.min.js"></script>
  <script type="text/javascript" src="https://www.pinpinbox.com/js/jBox-0.4.8/plugins/Confirm/jBox.Confirm.min.js"></script>
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/autolink-min.js"></script>
  
  
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/jquery.nicescroll.min.js"></script>
  
  
  <script type="text/javascript" src="https://ppb.sharemomo.com/static_file/pinpinbox/zh_TW/js/lightGallery-master/dist/js/lightgallery-all-modify.min.js"></script>
  <script type="text/javascript" src="https://ppb.sharemomo.com/static_file/pinpinbox/zh_TW/js/lightGallery-master/dist/js/lg-audio.min.js"></script>
  <script type="text/javascript" src="https://ppb.sharemomo.com/static_file/pinpinbox/zh_TW/js/lightGallery-master/dist/js/lg-subhtml.min.js"></script>
  
  
  
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
  
  <!-- 測試預設值 (B的靜態頁面測試使用) 區塊開始 -->
    <!-- 測試預設值 (B的靜態頁面測試使用) 區塊開始 -->
  
  
  <!-- 主要內容區開始 -->
  <div id="content">
    <!-- 創作人內容區塊開始 -->
	<div class="creative_content_header">
	  <div class="creative_info_area">
	  
	    <div id="creative_name" >
		  
		  <span>
		  
		    <!--//20190222: 新增div, img class name區塊開始-->
		    <div class="profile_img_wrap">
			  <img class="profile_img" src="https://cdn.pinpinbox.com/storage/zh_TW/user/4119/picture$7d29.jpg" title="點我分享">
		    </div>
		    <!--//20190222: 新增div區塊結束-->
		    
		  </span>
		  <span title="煒承Answer煒承AnswerSung">煒承Answer煒承Sung煒承Answer煒承Sung煒承Answer煒承Sung煒承Answer煒承Sung</span>
		</div>
		
		<!-- 社群區塊開始  -->
		<div id="creative_content_social_links_box" class="social_links_box">
		  <div class="social_links_title">分享</div>
		
		  <div class="social_links_box_area">
		
		     <div id="creative_content_social_links" class="social_links">
		
             <!-- Go to www.addthis.com/dashboard to customize your tools -->
             <div class="addthis_inline_share_toolbox"></div><!-- Go to www.addthis.com/dashboard to customize your tools --><script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5c234e41206c2da6"></script><!-- //20181227: 加入QRCODE區塊開始 --><a href="href="https://cdn.pinpinbox.com/storage/zh_TW/user/3876/album/15124/qrcode$d2f5.jpg" title="顯示QRcode" target="_blank"><!-- //20190212: 圖檔PNG換成SVG開始 --><img src="images/assets-v7/qr_square.svg" >
			 </a>
			</div>
	        <div id="creative_content_social_links2" class="social_links2" title="複製作品網址" ><i class="fa fa-link"></i></div>
		  </div>
		</div><!-- .social_links_box -->
		<!-- 社群區塊結束  -->
		
		<div id="creative_content_snackbar">
		   <span>已複製到剪貼簿</span>
		   <span><input id="creative_content_page_url" type="text" value="https://www.pinpinbox.com/123.html"></span>
		</div>
				
		<div id="social_links">
		  <a href="https://babel-lab.github.io/RWD-Portfolio/index.htm" title="web" target="_blank">
		    <img src="images/assets-v7/social_home.png">
		  </a>
		  <a href="https://www.facebook.com/profile.php?id=100006321210298&amp;fref=ts" title="facebook" target="_blank">
		    <img src="images/assets-v7/social_fb.png">
		  </a>
		  <a href="#" title="youtube" target="_blank"><img src="images/assets-v7/social_youtube.png"></a>
		  <a href="#" title="Google+" target="_blank"><img src="images/assets-v7/social_g+.png"></a>
		  <a href="#" title="Pinterest" target="_blank">
		    <img src="images/assets-v7/social_pinterest.png" >
		  </a>
		  <a href="https://www.instagram.com/_ccartist/" title="instagram" target="_blank">
		    <img src="images/assets-v7/social_ig.png">
		  </a>
		  <a href="#" title="twitter" target="_blank">
		    <img src="images/assets-v7/social_twitter.png" >
		  </a>
		  <a href="#" title="LinkedIn" target="_blank">
		    <img src="images/assets-v7/social_in.png" >
		  </a>
		  <a href="https://www.webtoons.com/zh-hant/challenge/%E6%96%B0%E6%A0%A1%E5%9C%92%E6%80%AA%E8%AB%87/list?title_no=6164&amp;page=3" title="line" target="_blank">
		    <img src="images/assets-v7/social_line.png">
		  </a>
		  <a href="http://babel-lab.blogspot.com/" title="blog" target="_blank">
		    <img src="images/assets-v7/social_blogger.png">
		  </a>
		</div>
	    <div id="social_links2" title="社群連結" ><i class="fa fa-link"></i></div>
		<div class="attention_num">關注 : 54462</div>
		<div class="edit_btn"><!-- 編輯 或 關注按鈕 -->
		
		  <!--<a href="creative_edit.php"><div class="btn_new">編輯</div></a>-->
		  <!--
		  <a style="" name="buttonFollow" href="javascript:void(0)" onclick="follow();"><div class="btn_new btn_pink">關注</div></a>
		  -->
		  
		  <a style="" name="buttonFollow" href="javascript:void(0)" onclick="follow();"><div class="btn_new btn_attention">取消關注</div></a>
		  
		</div>
		<div class="social_links_close"><i class="fa fa-times"></i></div>
	  </div><!--.creative_info_area -->
	</div><!-- .creative_content_header -->
    <div class="creative_content">
	  <div class="creative_title">
	    <div><span>绘画&摄影&萌宠</span></div>
		<div>
		  <div class="creative_num_donate">
		    <div><span class="creative_num counter">7882</span><span>贊助次數</span></div>
		  </div>
		  <div class="creative_num_view">
            <div><span class="creative_num counter">58972</span><span>瀏覽次數</span></div>
		  </div>
		</div>
	  </div>
	  <!-- 20181227 若沒有BANNER就不顯示此區塊開始 -->
	  <div class="creative_banner">
	    <img src="https://cdn.pinpinbox.com/storage/zh_TW/user/4056/cover$2d9d.jpg">
	  </div>
	  <!-- 20181227 若沒有BANNER就不顯示此區塊結束 -->
	  <div class="main_content">
	    <div class="tab-container">
		  <div class="tab_list_area" >
		    <!-- 手機版列表選單開始 -->
		    <div id="mobile_tab" ><i class="fa fa-list-ul"></i><span id="tab_title">作品集</span></div>
		    <!-- 手機版列表選單結束 -->
  	        <ul class="nav nav-pills mobile_tab_menu" id="mobile_tab_menu" >
			  <!-- 20181227 看人就顯示"作品集"區塊開始 -->
	          <!--
			  <li class="active"><a data-toggle="tab" href="#tab0">我的作品</a></li>
			  -->
			  <li class="active"><a data-toggle="tab" href="#tab0" onclick="$('#mobile_tab #tab_title').text('作品集');">作品集</a></li>
		      <!-- 20181227 看人就顯示"作品集"區塊結束 -->
			  <li><a data-toggle="tab" href="#tab1" onclick="$('#mobile_tab #tab_title').text('收藏˙贊助');">收藏˙贊助</a></li>
			  <li><a data-toggle="tab" href="#tab2" onclick="$('#mobile_tab #tab_title').text('群組作品');">群組作品</a></li>
		      <li><a data-toggle="tab" href="#tab3" onclick="$('#mobile_tab #tab_title').text('關於我');">關於我</a></li>
		      <li><a data-toggle="tab" href="#tab4" onclick="$('#mobile_tab #tab_title').text('留言版');">留言版</a></li>
		    </ul>
			
			
		    <!-- 搜尋區塊開始 -->
			<div id="content_search">
		      <div class="content_search_open"><i class="fa fa-search"></i></div>
	          <div class="content_search_box">
	      	    <a href="javascript:void(0);" onclick="_search('mobile')"><i class="fa fa-search"></i></a>
	      	      <input type="text" class="search_text" name="searchkey" id="mobile_searchkey" data-searchtype="album" data-device="mobile" autocomplete="off">
	          </div>
              <div class="content_search_close"><i class="fa fa-times"></i></div>
	        </div>
		    <!-- 搜尋區塊結束 -->
			
		  </div>
		  
  	      <div class="tab_content">
  		    <!-- 我的作品區塊開始 -->
  		    <div id="tab0" class="tab active">
              <div class="waterfall_content">
                
				 <div class="content_box">
				   <div class="lock_box">
				     <span><i class="fa fa-lock"></i></span>
				   </div>
				   <div class="content_box_img" onclick="popview('http://localhost/creative_content.php');" >
				     <!--<a href="https://www.pinpinbox.com/index/album/content/?album_id=5534&amp;click=name">-->
					   <img src="https://cdn.pinpinbox.com/upload/pinpinbox/diy/20181227/5c24b13e8af47.jpg">
					 <!--</a>-->
				   </div>
				   <div class="content_box_info">
				     <div>
				       <div class="content_box_icon"><i class="fa fa-volume-down"></i><i class="fa fa-play-circle"></i><i class="fa fa-gift"></i></div>
					   <div class="content_box_name" data-album_id="5534" title="扇形車站"><a href="https://www.pinpinbox.com/index/album/content/?album_id=5534&amp;click=name">扇形車庫 自9月1日起10個人以上的團體必須申請導覽，必須在參訪14天前填妥申請表，

傳真或郵寄申請表至彰化機務段。開放時間也改為每天上午10時至下午4時，周一至周五提

供導覽服務 (國定假日沒有)，每次導覽時間大約30分鐘，一天兩場上午及下午各一場次。</a></div>
				     </div>
					 					 
					 <div class="content_box_menu_btn">
					   <div class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						 <i class="fa fa-ellipsis-h"></i> 
					   </div>
					   <div class="dropdown-menu dropdown_menu" role="menu">
					     <ul>
						   <li><a href="javascript:void(0)" onclick="share('album',5534, 'https://cdn.pinpinbox.com/storage/zh_TW/user/4119/album/5534/qrcode$f90a.jpg' , 'https://cdn.pinpinbox.com/upload/pinpinbox/diy/20170814/59911dba7ba2f_330x248.jpg', 'https://www.pinpinbox.com/index/album/content/?album_id=5534&amp;autoplay=1&amp;categoryarea_id=1')">分享</a></li>
						   <li data-albumid="5534"><a onclick="buyalbum(5534);" href="javascript:void(0)">收藏</a></li>
						   <li><a class="alert_btn" href="javascript:void(0)" data-type="album" data-type_id="5534" >檢舉</a></li>
					     </ul>
					   </div>
					 </div>
					 
				   </div>
			     </div>
				 
				 <div class="content_box">
				   <div class="lock_box">
				     <span><i class="fa fa-lock"></i></span>
				   </div>
				   <div class="content_box_img" onclick="popview('http://localhost/creative_content.php');" >
					 <img src="https://cdn.pinpinbox.com/upload/pinpinbox/diy/20181128/5bfdbf7f49ea0.jpg">
				   </div>
				   <div class="content_box_info">
				     <div>
				       <div class="content_box_icon"><i class="fa fa-volume-down"></i><i class="fa fa-play-circle"></i><i class="fa fa-gift"></i></div>
					   <div class="content_box_name" data-album_id="5534" title="扇形車站"><a href="https://www.pinpinbox.com/index/album/content/?album_id=5534&amp;click=name">扇形車站</a></div>
				     </div>
				     					 
					 <div class="content_box_menu_btn">
					   <div class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						 <i class="fa fa-ellipsis-h"></i> 
					   </div>
					   <div class="dropdown-menu dropdown_menu" role="menu">
					     <ul>
						   <li><a href="javascript:void(0)" onclick="share('album',5534, 'https://cdn.pinpinbox.com/storage/zh_TW/user/4119/album/5534/qrcode$f90a.jpg' , 'https://cdn.pinpinbox.com/upload/pinpinbox/diy/20170814/59911dba7ba2f_330x248.jpg', 'https://www.pinpinbox.com/index/album/content/?album_id=5534&amp;autoplay=1&amp;categoryarea_id=1')">分享</a></li>
						   <li data-albumid="5534"><a onclick="buyalbum(5534);" href="javascript:void(0)">收藏</a></li>
						   <li><a class="alert_btn" href="javascript:void(0)" data-type="album" data-type_id="5534" >檢舉</a></li>
					     </ul>
					   </div>
					 </div>
					 
				   </div>
			     </div>
				
				<div class="content_box">
				   <div class="lock_box">
				     <span><i class="fa fa-lock"></i></span>
				   </div>
				   <div class="content_box_img" onclick="popview('http://localhost/creative_content.php');" >
					 <img src="https://cdn.pinpinbox.com/upload/pinpinbox/diy/20181214/5c137495ebe9e.jpg">
				   </div>
				   <div class="content_box_info">
				     <div>
				       <div class="content_box_icon"><i class="fa fa-volume-down"></i><i class="fa fa-play-circle"></i><i class="fa fa-gift"></i></div>
					   <div class="content_box_name" data-album_id="15499" title="CODE H 第一集"><a href="https://www.pinpinbox.com/index/album/content/?album_id=5534&amp;click=name">CODE H 第一集</a></div>
				     </div>
				    <div class="content_box_menu_btn">
					   <div class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						 <i class="fa fa-ellipsis-h"></i> 
					   </div>
					   <div class="dropdown-menu dropdown_menu" role="menu">
					     <ul>
						   <li><a href="javascript:void(0)" onclick="share('album',15499, 'https://cdn.pinpinbox.com/storage/zh_TW/user/4119/album/5534/qrcode$f90a.jpg' , 'https://cdn.pinpinbox.com/upload/pinpinbox/diy/20170814/59911dba7ba2f_330x248.jpg', 'https://www.pinpinbox.com/index/album/content/?album_id=5534&amp;autoplay=1&amp;categoryarea_id=1')">分享</a></li>
						   <li data-albumid="15499"><a onclick="buyalbum(15499);" href="javascript:void(0)">收藏</a></li>
						   <li><a class="alert_btn" href="javascript:void(0)" data-type="album" data-type_id="15499" >檢舉</a></li>
					     </ul>
					   </div>
					 </div>
				   </div>
			     </div>
				
              </div>
		    </div>
			<!-- 我的作品區塊結束 -->
			<!-- 收藏．贊助區塊開始 -->
  		    <div id="tab1" class="tab">
			  <div class="waterfall_content">
				 
				 <div class="content_box">
				   <div class="content_box_img"><a href="https://www.pinpinbox.com/index/album/content/?album_id=4902&amp;click=name"><img src="https://cdn.pinpinbox.com/upload/pinpinbox/diy/20170620/5948e3a4cd819.jpg"></a></div>
				   <div class="content_box_info">
				     <div>
				       <div class="content_box_icon"><i class="fa fa-volume-down"></i><i class="fa fa-play-circle"></i></div>
					   <div class="content_box_name" data-album_id="4902" title="三峽老街"><a href="https://www.pinpinbox.com/index/album/content/?album_id=4902&amp;click=name">三峽老街</a></div>
				     </div>
				     
					 <div class="content_box_menu_btn">
					   <div class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						 <i class="fa fa-ellipsis-h"></i> 
					   </div>
					   <div class="dropdown-menu dropdown_menu" role="menu">
					     <ul>
						   <li><a href="javascript:void(0)" onclick="share('album',5534, 'https://cdn.pinpinbox.com/storage/zh_TW/user/4119/album/5534/qrcode$f90a.jpg' , 'https://cdn.pinpinbox.com/upload/pinpinbox/diy/20170814/59911dba7ba2f_330x248.jpg', 'https://www.pinpinbox.com/index/album/content/?album_id=5534&amp;autoplay=1&amp;categoryarea_id=1')">分享</a></li>
						   <li data-albumid="5534"><a onclick="buyalbum(5534);" href="javascript:void(0)">收藏</a></li>
						   <li><a class="alert_btn" href="javascript:void(0)" data-type="album" data-type_id="5534" onclick="$('#alert_box_new').show();">檢舉</a></li>
					     </ul>
					   </div>
					 </div>
					 
				   </div>
				 </div>
				
              </div>
		    </div>
			<!-- 收藏．贊助區塊結束 -->
			<!-- 群組作品區塊開始 -->
  		    <div id="tab2" class="tab">
			  <div class="waterfall_content">				 
				 
				 <p class="no_content">沒有任何作品可以顯示</p>
				
              </div>
		    </div>
			<!-- 群組作品區塊結束 -->
			<!-- 關於我區塊開始 -->
  		    <div id="tab3" class="tab">
			  
			<div class="aboutme"><p><span style="font-size:20px;"><strong>2018小愉兒 x pinpinbox | 邀請您共同參與聖誕公益募資！</strong></span></p>

<p><span style="font-size:16px;">✦ pinpinbox愛心遍佈全台灣：</span>我們將把您愛心，從台北》桃園》新竹》苗栗》台中持續走下去。Coming soon…</p>

<p><img alt="" src="https://cdn.pinpinbox.com/upload/pinpinbox/creative/20181119/5bf23eff9befb.jpg" style="width: 1000px; height: 750px;"></p>

<p><span style="font-size:16px;">✦ 一份溫暖、兩倍愛心</span></p>

<p>不是每個孩子都擁有溫暖的聖誕節，我們希望透過親手製作的手工愛心餅乾，給予育幼院的孩子們溫暖。只要$60就能認購一份餅乾，我們將替您把這份愛送至育幼院，同時幫助小愉兒及弱勢孩子們！邀請您和小愉兒一起，把這份愛送給育幼院的寶貝們。</p>

<p><span style="font-size:16px;">✦ 大家好，我們是小愉兒</span></p>

<p>「愛他，就要讓他自立自強」，他們是父母希望「愉快長大」的心智障礙孩子們，更是最認真、熱情、用心的點心烘焙師！讓小愉兒們發揮所長、用愛與熱情製作餅乾的熊米屋愛心烘焙坊，位在基隆的一個小角落，是由一群充滿愛心的熊爸爸、熊媽媽們所成立的愛心烘焙坊。目的是希望，讓小愉兒的未來能得到更完善的生涯規畫和照顧，因此不斷積極的推展教育、訓練及養護工作。藉由做點心和賣點心，讓小愉兒們能靠自己的力量培養一技之長，能和人群互動，能像一般人一樣的生活在社會中。</p>

<p><span style="font-size:16px;">✦ 您的愛心將送至台北各大育幼院！</span></p>

<p>財團法人台灣兒童暨家庭扶助基金會大同育幼院、財團法人基督教台北市私立伯大尼兒少家園、社團法人中華育幼機構兒童關懷協會、財團法人基督教中國佈道會附屬臺北市私立聖道兒童之家、財團法人天主教善牧社會福利基金會、新北普賢慈海家園、台灣展翅協會…持續增加中。</p>

<p><span style="font-size:16px;">✦ 感謝贊助：</span>新中區扶輪社、上順旅行社、麗星郵輪、雙影創藝有限公司、上品生活科技股份有限公司。</p>

<p><span style="font-size:16px;">✦ 支持小愉兒募資活動</span></p>

<p>✓一份溫暖、兩倍愛心，送愛到育幼院。</p>

<p>✓小愉兒手工聖誕幸福禮盒，把最用心的甜蜜，送給最愛的人。</p>

<p>✓小愉兒想要有個家…家園建立計劃。</p>

<div class="youtube-embed-wrapper" style="position:relative;padding-bottom:56.25%;padding-top:30px;height:0;overflow:hidden"><iframe allowfullscreen="" frameborder="0" height="360" src="https://www.youtube.com/embed/8yXtVx8ds7A?rel=0" style="position:absolute;top:0;left:0;width:100%;height:100%" width="640"></iframe></div>

<p><br>
&nbsp;</p>
</div>
			</div>
			<!-- 關於我區塊結束 -->

			<!-- 留言板區塊開始 -->
  		    <div id="tab4" class="tab">
			
			  <!--//20190304: 嵌入留言區塊開始-->
			  <div class="message_board">

  <div class="message_count"> 12 則回覆</div>
  
  <!--//20190304: 加class name開始-->
  <div id="message_leave" class="message_leave">
  
    <!--//20190307: 加data-type開始-->
    <div id="pinpinboard" class="pinpinboard" data-type="pinpinboard_user">
    <!--//20190307: 加data-type結束-->
  
  <!--//20190304: 加class name結束-->
	
	<!--//20190307: 加div 與 class開始-->
	<div class="pinpinboard_wrap">
	<!--//20190307: 加div 與 class結束-->
	
      <span>
        <div class="profile_img_wrap">
          <img class="profile_img" src="https://ppb.sharemomo.com/storage/zh_TW/user/picture$2a39.jpg" onerror="this.src='https://ppb.sharemomo.com/static_file/pinpinbox/zh_TW/images/face_sample.svg'">
        </div>
      </span>
	  
      <span>
	  
        <div class="user_comment_textarea_wrapper" >
		
		  <textarea id="user_comment_textarea" placeholder="請先登入會員"
				
				<!--//20190304: 將min-height: 38px;改成24px開始-->
                rows="4" maxlength="300" style="min-height: 24px;"
				<!--//20190304: 將min-height: 38px;改成24px結束-->
				
                onfocus="location.href='#'"></textarea>
        </div>
		
		<!--//20190304: 按鈕移至此開始, 加class name-->
	    <div class="message_leave_btn_area">
          <a href="javascript:void(0)" class="comment_text_cancel" onclick="cancelCommtent('user');"><span class="btn_new">清除</span></a><a href="javascript:void(0)" class="comment_text_submit" onclick="addCommtent('user', '2');"><span class="btn_new message_submit">送出</span></a>
        </div>
	    <!--//20190304: 按鈕移至此結束開始-->
		
      </span>
	
	<!--//20190307: 加div 與 class開始-->
	</div>
	<!--//20190307: 加div 與 class結束-->
	
	
	
  </div><!-- #message_leave -->

  <div id="message_list" class="user_comment_list">
    <!--
    <div class="message_item" data-pinpinboard_id="1602">
      <div>
        <span>
            <a href="https://w3.pinpinbox.com/test01">
              <div class="profile_img_wrap">
                <img class="profile_img" title="KevinLin111" width="100" src="https://ppb.sharemomo.com/storage/zh_TW/user/17/picture$18bf.jpg" onerror="this.src='https://ppb.sharemomo.com/static_file/pinpinbox/zh_TW/images/face_sample.svg'">
              </div>
            </a>
        </span>
      </div>
      <div>
        <div>
            <span><a href="javascript:void(0);" onclick="addTag('17', 'KevinLin111')">KevinLin111</a></span>
            <span><time datetime="2018-04-16 16:30:17" class="timeago">2018-04-16 16:30:17</time></span>
        </div>
        <div>
            <div class="comment_reply_text"><span class="mention"><a class="message_tag" target="_blank" href="https://w3.pinpinbox.com/index/creative/content/?user_id=153">渣渣是笨蛋</a></span> </div>
            <div><span class="delete_box" onclick="delComment('user', 371, 2212)" aria-hidden="true">&times;</span><span class="sr-only"></span></div>
        </div>
      </div>
    </div>
	
	<div class="message_item" data-pinpinboard_id="1602">
      <div>
        <span>
            <a href="https://w3.pinpinbox.com/test01">
              <div class="profile_img_wrap">
                <img class="profile_img" title="KevinLin111" width="100" src="https://ppb.sharemomo.com/storage/zh_TW/user/17/picture$18bf.jpg" onerror="this.src='https://ppb.sharemomo.com/static_file/pinpinbox/zh_TW/images/face_sample.svg'">
              </div>
            </a>
        </span>
      </div>
      <div>
        <div>
            <span><a href="javascript:void(0);" onclick="addTag('17', 'KevinLin111')">KevinLin111</a></span>
            <span><time datetime="2018-04-16 16:30:17" class="timeago">2018-04-16 16:30:17</time></span>
        </div>
        <div>
            <div class="comment_reply_text"><span class="mention"><a class="message_tag" target="_blank" href="https://w3.pinpinbox.com/index/creative/content/?user_id=153">渣渣是笨蛋</a></span> </div>
            <div><span class="delete_box" onclick="delComment('user', 371, 2212)" aria-hidden="true">&times;</span><span class="sr-only"></span></div>
        </div>
      </div>
    </div>
	-->
  </div><!-- #message_list -->
</div><!-- .message_board -->

<script type="text/javascript" name="popview">

function addCommtent(type, type_id) {
	var comment = $('#'+type+'_comment_textarea'),
		comment_val = comment.val().replace(/\n/g,"<br>").trim(),
        push_notice_ids = new Set();

	$('.textoverlay').find('span').each(function(k, v){
        str = $(v).html();
        push_notice_ids.add(str.substring(str.indexOf('[')+1,str.indexOf(':')));
    });

	if(comment_val.length == 0) {
		comment.val('').attr('placeholder', '還沒有填寫留言').focus();
	} else {
		$.post('https://w3.pinpinbox.com/index/pinpinboard/addcomment/', {
			type :  type,
			type_id : type_id,
			text :  comment_val,
            push_notice_ids : Array.from(push_notice_ids),
		}, function(r) {
			r = $.parseJSON(r);
			switch (r.result) {
				case 1:
					$('#comment').val('');
					var newComment = `<div class="message_item" data-pinpinboard_id="${r.data.pinpinboard_id}">
                        <div>
                            <span><div class="profile_img_wrap"><a href="${r.data.user_url}"><img class="profile_img" title="${r.data.user_name}" width="100" src="${r.data.user_picture}" onerror="this.src=\'https://ppb.sharemomo.com/static_file/pinpinbox/zh_TW/images/face_sample.svg\'"></a></div></span>
                        </div>
                        <div>
                            <div>
                                <span><a href="${r.data.user_url}" onclick="addTag('${r.data.user.user_id}', '${r.data.user_name}')">${r.data.user_name}</a></span>
                                <span><time datetime="${r.data.inserttime}" class="timeago">${r.data.inserttime}</time></span>
                            </div>
                            <div>
                                <div class="comment_reply_text"> ${r.data.textToMention.autoLink({ target: "_blank" })} </div>
                                <div><span class="delete_box" onclick="delComment('${type}', ${type_id}, '${r.data.pinpinboard_id}')" aria-hidden="true">&times;</span><span class="sr-only"></span></div>
                            </div>
                        </div>
                    </div>`;
					$('.textoverlay').html('');
                    cancelCommtent(type);
					$('.'+type+'_comment_list').hide().prepend(newComment).fadeIn('slow');
					comment.val('');
					break;

                default : break;
			}
		});
	}
}

function addSymbol(type) {
    $('#'+type+'_comment_textarea').val($('#'+type+'_comment_textarea').val()+'\u0020@');
    $('#'+type+'_comment_textarea').focus();
}

function addTag(id, name) {
    var comment = $('#user_comment_textarea'),
        comment_val = comment.val().replace(/\n/g,"<br>").trim(),
        mention_ids = new Set([""]),
        regex = regex = /\[(.+?)\]/g,  matches = [] , match;

    while (match = regex.exec(comment_val)) {
        mention_ids.add(match[1].substring(match[1].indexOf('[')+1,match[1].indexOf(':')));
    }

    if(!mention_ids.has(id)) {
        comment.val(comment_val + `[${id}:${name}]`);
    }
}

function cancelCommtent(type) {
    $('#'+type+'_comment_textarea').val(''); // 20190222: 修改ID稱
    $('.textoverlay').html(''); //clear overlay
    $('#'+type+'_comment_textarea').css('height',$('#'+type+'_comment_textarea').css('min-height'));
}

function comment_setheight(comment_name){
    $(comment_name).height($(comment_name).css('min-height'));
    var comment_height = $(comment_name).height();
    var comment_scrollheight = $(comment_name)[0].scrollHeight;
    if(comment_scrollheight > comment_height){
        $(comment_name).height(comment_scrollheight);
    }
}

function delComment(type, type_id, id) {
	$.post('https://w3.pinpinbox.com/index/pinpinboard/deleteComment/', {
        type : type,
        type_id : type_id,
        pinpinboard_id : id,
	}, function(r) {
		r = $.parseJSON(r);
		switch (r.result) {
			case 1:
				$('.'+type+'_comment_list').hide();
				$('div[data-pinpinboard_id="'+id+'"]').remove();
				$('.'+type+'_comment_list').fadeIn('slow');
				break;

			default :
				_jBox(r, 'error');
				break;
		}
	});
}

$(function(){
	$('.comment_reply_text').each(function(){
		$(this).html( $(this).html().autoLink({ target: "_blank" }) );
	});

    if ($('#user_comment_textarea').length>0){
        //恢復初始高度
        cancelCommtent('user');
        $('#user_comment_textarea').bind('scroll keyup', function(){
            comment_setheight('#user_comment_textarea');
        });
    }

    $('#user_comment_textarea').textcomplete([
      {
        id: 'pinpinboard',
        //match: /\B@([\u4e00-\u9fa5a-zA-Z0-9]+)$/,
		match: /\B@([\u4e00-\u9fa5a-zA-Z0-9\s]+)$/,
        search: function (term, callback) {
		  /*
          $.post('https://w3.pinpinbox.com/index/pinpinboard/mention/', {
            searchkey : term,
            text : $('#user_comment_textarea').val(),
          }, function(r) {
            r = $.parseJSON(r);
            callback($.map(r , function (v, i) {
              return (v.name.toLowerCase().indexOf(term.toLowerCase()) >= 0 ) ? v : null;
            }));
          });
		  */
		  callback(
		    [{"user_id":17,"name":"KevinLin111","picture":"https:\/\/ppb.sharemomo.com\/storage\/zh_TW\/user\/17\/picture$18bf.jpg"},
		    {"user_id":58,"name":"PinPin Box","picture":"https:\/\/ppb.sharemomo.com\/storage\/zh_TW\/user\/58\/picture$f2d7.jpg"},
		    {"user_id":90,"name":"\u767d\u6bd3","picture":"https:\/\/ppb.sharemomo.com\/static_file\/pinpinbox\/zh_TW\/images\/face_sample.svg"},
		    {"user_id":140,"name":"cailum_1","picture":"https:\/\/ppb.sharemomo.com\/storage\/zh_TW\/user\/140\/picture$091c.jpg"},
		    {"user_id":151,"name":"Bruce Lee","picture":"https:\/\/ppb.sharemomo.com\/storage\/zh_TW\/user\/151\/picture$e71b.jpg"},
		    {"user_id":159,"name":"Pin Pin Box","picture":"https:\/\/ppb.sharemomo.com\/static_file\/pinpinbox\/zh_TW\/images\/face_sample.svg"},
		    {"user_id":196,"name":"Bruce  Lee","picture":"https:\/\/ppb.sharemomo.com\/storage\/zh_TW\/user\/196\/picture$ef1e.jpg"},
			{"user_id":196,"name":"Lion + TT","picture":"https:\/\/ppb.sharemomo.com\/storage\/zh_TW\/user\/1\/picture$3e76.jpg"},
			{"user_id":196,"name":"Shela Yang","picture":"https:\/\/ppb.sharemomo.com\/storage\/zh_TW\/user\/3\/picture$b655.jpg"},
			{"user_id":196,"name":"Kapkid Lee","picture":"https:\/\/ppb.sharemomo.com\/storage\/zh_TW\/user\/54\/picture$0276.jpg"}]
    	  );
		},
        template: function (value) {
          return '<img src="'+value.picture+'"></img>' + value.name;
        },
        replace: function (value, term) {
          return '['+value.user_id+':'+value.name+'] ';
        },
        context : function(value) {
          return value;
        },
		
        //index: 1,
		
		placement: 'top',
		
      },
	  
	// 20190307: 指定選單出現位置與指定CLASSNAME區塊開始  
	//],{onKeydown: function (e, commands){  
    //],{appendTo: 'body', className:'user'},{onKeydown: function (e, commands){
	//],{appendTo: '.user_comment_textarea_wrapper', className:'user'},{onKeydown: function (e, commands){
	],{appendTo: $('[data-type="pinpinboard_user"]'), className:'user'},{onKeydown: function (e, commands){	
	// 20190307: 指定選單出現位置與指定CLASSNAME區塊結束
	
        if(e.ctrlKey && e.keyCode === 74){ // CTRL-J
          return commands.KEY_ENTER;
        }
       }
	   
    // 20190307: 移除appendTo: 'body'區塊開始  
	//},{appendTo: 'body'}).overlay([
	}).overlay([
	// 20190307: 移除appendTo: 'body'區塊結束 
    {
          match: /(\[{1}[0-9]+:{1}[^\[\]:]+\]{1})/g,
          css: {
          }
        }
		
	// 20190307: 當選單出現時, 位置調整區塊開始
	//]); 
	]).on({
        'textComplete:show': function (e) {
			
			$('.user').attr('name', 'user_textcomplete_dropdown');
			
			//若在專區裡POPUP作品資訊頁
			if($('.popview_content').length>0){
			  console.log('.popview_content');
			  $('[name="user_textcomplete_dropdown"]').addClass('position_absolute');
			}else{
			  $('[name="user_textcomplete_dropdown"]').removeClass('position_absolute');
			}
			
        },
        'textComplete:hide': function (e) {
        }
    });
	// 20190307: 當選單出現時, 位置調整區塊結束
	
	$('time.timeago').timeago();
});

</script>			  <!--//20190304: 嵌入留言區塊結束-->
			  
			  
		    </div>
			<!-- 留言板區塊結束 -->
	      </div><!-- .tab_content -->
	    </div><!-- .tab-container -->
	  </div><!-- .main_content -->
	<!-- 創作人內容區塊結束 -->
    </div><!-- .creative_content -->
  
  
  
  </div>
  <!-- 主要內容區結束 -->



<script>
  $(document).ready(function() {
      //時間之前
      $('time.timeago').timeago();
      
      //數字倒數
      $('.counter').counterUp({
          delay: 10,
          time: 1000
      });
    
      //當WIDNWOS RESIZE時, 關閉選單
      $( window ).resize(function(evt) {
		$('.social_links_close .fa').click();
		$('.content_search_close').click();
      });
    });
	
    //暫代function
    function _search(device = null) {
      if(device == null) { device = (isMobile()) ? 'mobile' : 'desktop' ; }
        var searchtype = $('input#'+device+'_searchkey').data('searchtype');
        switch (searchtype) {
  	      case 'album' :
  	      var url = 'https://www.pinpinbox.com/index/album/?rank_id=0';
  	    break;
  	      case 'user' :
  	      var url = 'https://www.pinpinbox.com/index/creative/?rank_id=0';
  	    break;
    }
    var searchkey = $('input#'+device+'_searchkey').val().trim();
    //cookie
    //...
    }


    //專區開始
    //社群連結按鈕
	$('#social_links2 .fa').on('click', function(){
	  //關閉非社群連結鈕
      $(".creative_info_area > div").addClass('hide').removeClass('show_tablecell');
	  //開啟社群小圖示與關閉按鈕
	  $("#social_links").addClass('show_tablecell');
	  $(".social_links_close").addClass('show_tablecell');
    });
	$('.social_links_close .fa').on('click', function(){
	  $(".creative_info_area > div").removeClass('hide');
	  $("#social_links").removeClass('show_tablecell');
	  $("#social_links2").removeClass('show_tablecell');
	  $(".social_links_close").removeClass('show_tablecell');
	});
	//瀑布流內容
	if($('.waterfall_content').length>0){
	  var $container = $('.waterfall_content');
      $container.imagesLoaded(function () {
        $container.masonry({
          itemSelector: '.content_box',
          columnWidth: '.content_box',
          gutter: 30,
        });
        $('div.content_box').animate({opacity: 1});
      });
      $('a[data-toggle=tab]').each(function () {
      	var $this = $(this);
      	$this.on('shown.bs.tab', function () {
      	  $container.imagesLoaded( function () {
      	  	$container.masonry({
      	  	  columnWidth: '.content_box',
      	  	  itemSelector: '.content_box'
      	  	});
      	  });
      	});
      });
	}
	
	//手機版選單列表
	$('#mobile_tab').on('click', function(){
	  $('.mobile_tab_menu').toggleClass("mobile_tab_down");
    });
	
	if($( window ).width() <= 768){
	  $('.mobile_tab_menu').on('click', function(){
	   $('.mobile_tab_menu').toggleClass("mobile_tab_down");
     });
	}
		
	//搜尋區塊開始
	$('.content_search_open').on('click', function(){
	  $('.content_search_open').addClass('hide').removeClass('show_tablecell');
	  $('.content_search_box').addClass('show_tablecell');
	  $('.content_search_close').addClass('show_tablecell');
    });
	$('.content_search_close').on('click', function(){
	  $('.content_search_open').removeClass('hide').removeClass('show_tablecell');
	  $('.content_search_box').removeClass('show_tablecell');
	  $('.content_search_close').removeClass('show_tablecell');
    });
	//搜尋區塊結束
	//作品右鍵選單
	
	//專區結束

</script>

<script>
			
			function processAjaxData(urlPath){
	          window.history.pushState("","",urlPath);
            }

//POPVIEW
	function popview(url) {
	  
	$('.jBox-wrapper').remove();
	//url = 'http://localhost/album_content.php';
	//url = 'http://192.168.16.115/album_content.php';
	url = 'http://192.168.16.118/album_content.php';
	
	//url='http://localhost/messageboard2.html';
	//url='http://localhost/popup.html';
	//url='http://localhost/testdivscroll.html';
	$.post(url, {
	  data : null,
	}, function(r) {
		
		//r='<div id="album_content_area" class="album_content_area"><div class="album_content_intro_area"> <div class="album_content_right_area"> <div class="album_content_donate_area"> <div class="album_content_intro_info"> <div class="album_content_creative_info"> <div class="album_content_creative_info_img"><a href="https://www.pinpinbox.com/papid" title="小愉兒社會福利基金會小愉兒社會福利基金會小愉兒社會福利基金會"><img src="https://cdn.pinpinbox.com/storage/zh_TW/user/3876/picture$7c7d.jpg"></a></div> <div class="album_content_creative_info_name"><span><a href="https://www.pinpinbox.com/papid" title="小愉兒社會福利">小愉兒社會福利</a></span><span class="sub_desc">2018/11/08</span></div> </div> <div class="album_content_creative_info_num"> <div class="album_content_creative_info_pin"><a href="javascript:void(0)" title="釘一下" ><i name="likes" class="fa fa-thumb-tack"></i>57</a></div> <div class="album_content_creative_info_view"><i class="fa fa-eye"></i>788446</div> </div> </div> <div class="album_content_donate"> <div class="donate_btn"> <span class="btn_new btn_main">贊助</span> </div> <div class="donate_desc"> <span>贊助條件</span> <span>1200P</span> <span class="p_to_nt">(600TWD)</span> </div> <div class="album_content_back" onclick="$(\'.jBox-closeButton\').click();"> <span><i class="fa fa-angle-left"></i></span> <span>返回</span> </div> </div> <div class="activity_vote"> <div class="activity_vote_desc">作品正在參加活動</div> <div class="activity_vote_area"> <span class="activity_vote_title">我的創意我的傳藝我的創意我的傳藝</span> <span class="btn_new btn_main">投票</span> </div> </div> <div id="removeInPop" class="intro_see_more_area"> <div class="intro_title">你可能會想看</div> <div class="intro_see_more"> <div class="intro_see_more_box"> <a href="https://www.pinpinbox.com/index/album/content/?album_id=15124&categoryarea_id=13&click=cover" title="【公益募資】認購$60手工餅乾｜愉你同在，送愛到育幼院｜小愉兒 x pinpinbox"> <div class="intro_see_more_img"> <div class="intro_see_more_img_position"><img src="https://cdn.pinpinbox.com/upload/pinpinbox/diy/20181227/5c24b13e8af47.jpg" ></div> </div> <div class="intro_see_more_name">【公益募資】認購$60手工餅乾｜愉你同在，送愛到育幼院｜小愉兒 x pinpinbox</div> </a> </div> <div class="intro_see_more_box"> <a href="https://www.pinpinbox.com/index/album/content/?album_id=15122&categoryarea_id=13&click=cover" title="【公益募資】家園募資計劃｜小愉兒們想要有個家…｜小愉兒 x pinpinbox"> <div class="intro_see_more_img"> <div class="intro_see_more_img_position"><img src="https://cdn.pinpinbox.com/upload/pinpinbox/diy/20181112/5be915bbdf0b0.jpg"></div> </div> <div class="intro_see_more_name">【公益募資】家園募資計劃｜小愉兒們想要有個家…｜小愉兒 x pinpinbox</div> </a> </div> <div class="intro_see_more_box"> <a href="https://www.pinpinbox.com/index/album/content/?album_id=10313&categoryarea_id=13&click=cover" title="小愉兒聖誕公益活動形象影片 | pinpinbox"> <div class="intro_see_more_img"> <div class="intro_see_more_img_position"><img src="https://cdn.pinpinbox.com/upload/pinpinbox/diy/20181108/5be3bc4d518b5.jpg"></div> </div> <div class="intro_see_more_name">小愉兒聖誕公益活動形象影片 | pinpinbox</div> </a> </div> <div class="intro_see_more_box"> <a href="https://www.pinpinbox.com/index/album/content/?album_id=15124&categoryarea_id=13&click=cover" title="【公益募資】認購$60手工餅乾｜愉你同在，送愛到育幼院｜小愉兒 x pinpinbox"> <div class="intro_see_more_img"> <div class="intro_see_more_img_position"><img src="https://cdn.pinpinbox.com/upload/pinpinbox/diy/20181112/5be915bbdf0b0.jpg" ></div> </div> <div class="intro_see_more_name">【公益募資】認購$60手工餅乾｜愉你同在，送愛到育幼院｜小愉兒 x pinpinbox</div> </a> </div> <div class="intro_see_more_box"> <a href="https://www.pinpinbox.com/index/album/content/?album_id=15122&categoryarea_id=13&click=cover" title="【公益募資】家園募資計劃｜小愉兒們想要有個家…｜小愉兒 x pinpinbox"> <div class="intro_see_more_img"> <div class="intro_see_more_img_position"><img src="https://cdn.pinpinbox.com/upload/pinpinbox/diy/20181112/5be915bbdf0b0.jpg" ></div> </div> <div class="intro_see_more_name">【公益募資】家園募資計劃｜小愉兒們想要有個家…｜小愉兒 x pinpinbox</div> </a> </div> <div class="intro_see_more_box"> <a href="https://www.pinpinbox.com/index/album/content/?album_id=10313&categoryarea_id=13&click=cover" title="小愉兒聖誕公益活動形象影片 | pinpinbox"> <div class="intro_see_more_img"> <div class="intro_see_more_img_position"><img src="https://cdn.pinpinbox.com/upload/pinpinbox/diy/20181108/5be3bc4d518b5.jpg"></div> </div> <div class="intro_see_more_name">小愉兒聖誕公益活動形象影片 | pinpinbox</div> </a> </div> </div> </div> </div> </div> <div class="album_content_left_area"> <div id="album_content_main" class="album_content_main"> <div class="album_content_box"> <div class="album_content_box_img"> <div class="album_content_box_img_area"> <img src="https://cdn.pinpinbox.com/upload/pinpinbox/diy/20181128/5bfdbf7f49ea0.jpg" alt="【公益募資】認購$60手工餅乾｜愉你同在，送愛到育幼院｜小愉兒 x pinpinbox" title="【公益募資】認購$60手工餅乾｜愉你同在，送愛到育幼院｜小愉兒 x pinpinbox"> </div> <div class="album_content_box_img_btn"  ><div class="btn_new btn_dark_opacity">進入觀看</div></div> </div> <div class="album_content_box_info"> <div class="album_content_box_icon"> <div class="album_content_box_icons"><span><i class="fa fa-volume-down"></i><i class="fa fa-play-circle"></i><i class="fa fa-gift"></i></span></div> <div class="album_content_box_pages"><span>14頁</span></div> <div class="album_content_box_menu_btn"> <i class="fa fa-ellipsis-h"></i> <div class="content_box_menu"> <ul> <li><a href="javascript:void(0)" 分享</a></li> <li data-albumid="4902"><a onclick="buyalbum(4902);" href="javascript:void(0)">收藏</a></li> <li><a class="alert_btn" href="javascript:void(0)" data-type="album" data-type_id="4902">檢舉</a></li> </ul> </div> </div> </div> <div class="content_box_name">【公益募資】認購$60手工餅乾｜愉你同在，送愛到育幼院｜小愉兒 x pinpinbox</div> <div id="album_content_desc" class="album_content_desc"> <div id="album_content_box_desc" class="album_content_box_desc" >✦ 一份溫暖、兩倍愛心<br>不是每個孩子都擁有溫暖的聖誕節，我們希望透過親手製作的手工愛心餅乾，給予育幼院的孩子們溫暖。<br>只要$60就能認購一份餅乾，我們將替您把這份愛送至育幼院，同時幫助小愉兒及弱勢孩子們！<br> 邀請您和小愉兒一起，把這份愛送給育幼院的寶貝們。<br><br>✦ 「愛他，就要讓他自立自強」<br>小愉兒是父母希望「愉快長大」的心智障礙孩子們，更是最認真、熱情、用心的點心烘焙師！<br><br>✦ 您的愛心將於12月起送至台北各大育幼院！<br> 財團法人台灣兒童暨家庭扶助基金會大同育幼院、財團法人基督教台北市私立伯大尼兒少家園、社團法人中華育幼機構兒童關懷協會、財團法人基督教聖道兒少福利基金會附屬台北市私立聖道兒童之家、財團法人天主教善牧社會福利基金會、新北普賢慈海家園、台灣展翅協會、社團法人世界和平會…持續增加中。<br> <br>✓ 不知道如何認購？就讓pinpinbox小老師教你：<br><a href="https://www.pinpinbox.com/index/album/content/?album_id=15111&amp;categoryarea_id=11&amp;autoplay=1" target="_blank">https://www.pinpinbox.com/index/album/content/?album_id=15111&amp;categoryarea_id=11&amp;autoplay=1</a><br><br> ✓ 注意事項：<br>1. 請於認購後留下詳細資料，以便發票寄送。<br>2. 購買份數及填寫P點(價錢)請見作品內頁價目表。<br>3. 認購此專案，您將不會獲得手工餅乾，本次募資餅乾將統一由pinpinbox發送至台北各大育幼院。<br><br>✓ 若有任何疑問，請洽「pinpinbox」Facebook粉絲團：<br> <a href="https://www.facebook.com/cpinpinbox/?ref=aymt_homepage_panel" target="_blank">https://www.facebook.com/cpinpinbox/?ref=aymt_homepage_panel</a> </div> <div id="album_content_desc_more" class="btn_new">更多</div> </div> </div> </div> <div class="activity_vote"> <div class="activity_vote_desc">作品正在參加活動</div> <div class="activity_vote_area"> <span class="activity_vote_title">我的創意我的傳藝我的創意我的傳藝</span> <span class="btn_new btn_main">投票</span> </div> </div> <div class="message_board_box"> <div class="message_reply_count">572则回复</div> <div class="message_board"> <div id="album_content_message_leave" class="message_leave"> <div id="album_content_pinpinboard" class="pinpinboard"> <span><img src="https://cdn.pinpinbox.com/storage/zh_TW/user/4581/picture$3a34.jpg"></span> <span><textarea id="album_content_user_comment" rows="4" maxlength="300" placeholder="有什麼想表達的嗎?"></textarea></span> </div> <div><span class="btn_new">清除</span><span class="btn_new message_submit">送出</span></div> </div> <div id="album_content_message_list" class="message_list"> <div class="message_item"> <div> <span><img src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/face_sample.svg"></span> </div> <div> <div><span><a href="javascript:void(0);" > 陈天</a></span><span><time datetime="2018/05/28 03:05:33" class="timeago">21天之前</time></span></div> <div> <div class="comment_reply_text">眭老师您好翻越千山万水，我都要看你节目我是内地粉丝翻墙，看你节目网络不稳定</div> <div><span class="delete_box"><i class="fa fa-times"></i></span></div> </div> </div> </div> <div class="message_item"> <div> <span><img src="https://cdn.pinpinbox.com/storage/zh_TW/user/4376/picture$b787.jpg"></span> </div> <div> <div><span><a href="javascript:void(0);" > 眭澔平</a></span><span><time datetime="999/05/17 10:05:26" class="timeago">21天之前</time></span></div> <div> <div class="comment_reply_text"><span><a class="message_tag" target="_blank" href="https://www.pinpinbox.com/index/creative/content/?user_id=4563">June Wu</a></span> 謝謝！每週都會有新影片。2018/10/17 澔平持續深入非洲，探索中非俾格米人最原始部落，參與世人未曾知曉的團圓祭典，傾聽花開的聲音。</div> <div><span class="delete_box"><i class="fa fa-times"></i></span></div> </div> </div> </div> </div> </div> </div> </div> <div id="album_content_social_links_box" class="social_links_box"> <div class="social_links_title">分享</div> <div class="social_links_box_area"> <div id="album_content_social_links" class="social_links"> <a href="href="https://cdn.pinpinbox.com/storage/zh_TW/user/3876/album/15124/qrcode$d2f5.jpg" title="顯示QRcode" target="_blank"> <img src="images/assets-v7/qr_square.png" > </a> </div> <div id="album_content_social_links2" class="social_links2" title="複製作品網址" ><i class="fa fa-link"></i></div> </div> </div> <div id="snackbar"> <span>已複製到剪貼簿</span> <span><input id="page_url" type="text" value="https://www.pinpinbox.com/index/album/content/?album_id=15124&click=name"></span> </div> </div> </div> </div> </div>';
		content = $(r).find('#album_content_area');
		//content = r;
		//pinpinboard = $(r).find('#album_content_pinpinboard');
		script = $(r).filter('script[name="popview"]');
		
		
        
		if(!$('#JboxPopview').length) {
			var popview = new jBox('Modal', {
				addClass : 'popview_content',
				id : 'JboxPopview',
				//width: 1200,
	            //height: 1600,
				//height: 820,
				
				//20190222: 樣式移至CSS控制區塊開始
	              //zIndex : 1000,
				//20190222: 樣式移至CSS控制區塊結束
				
				target: $('body'), //20181214: Add for position 
				isolateScroll: false, //20190212: mouse wheel
				onCloseComplete: function() {
			  		//processAjaxData("https://www.pinpinbox.com/papid?rank_id=1&");
					popview.destroy();
					browseKitRefresh();
				},
				onOpen : function(){
                  //processAjaxData(url);
                  //$('li.removeInPop').remove();
					//$('#removeInPop').remove();
                  //$('.jBox-content').append(pinpinboard);
                  $('.jBox-content').append(script);
                  //$('#pinpinboardAnchor').on('click', function(){
						//$('.jBox-content').animate({scrollTop:$('.comment_info_textarea').offset().top}, 10);
					//})
                  //$('.jBox-content .descrip').html( $('.jBox-content .descrip').html().autoLink({ target: "_blank" }) ) ;
					$('.jBox-content .descrip').html( $('.jBox-content .descrip').html() ) ;
					window.onpopstate = function(event) { popview.close();};
				
				},
			}).setContent(content).open();
		}//if(!$('#JboxPopview').length)
		//$(window).trigger('resize');	
	});
}////POPVIEW
			
			
			function browseKitRefresh() {
	//a_album_item = [];
	
	//$.each(a_album_element, function(k0, v0) {
		//$(v0).removeData('lightGallery');
	//});
}

	
		
		//複製作品網址按鈕
	    $('#creative_content_social_links_box').on('click', '#creative_content_social_links2', function() {
	      creative_content_snackbar_show() ;
	    });
	    
	    //彈出網址已複製訊息
	    function creative_content_snackbar_show(){
	      //顯示訊息
	      var x = document.getElementById("creative_content_snackbar");
	      x.className = "show";
	      setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
	      //拷貝網址
	      var copy_url = document.getElementById("creative_content_page_url");
          copy_url.select();
          document.execCommand("copy");
	    }
	    
		$('#creative_name, #creative_content_social_links_box').on('click', function(){
	      $('#creative_content_social_links_box').toggleClass("show_block");
        });
		
					
	    $('.alert_btn').on('click', function(){
			
		  // 20190107: 當按下檢舉而選單沒有關閉時 開始
		  $('[data-toggle="dropdown"]').parent().removeClass('open');
		  // 20190107: 當按下檢舉而選單沒有關閉時 結束
		  
          $('[data-toggle="dropdown"]').parent().addClass('open');
	      
		  $('#alert_box_new').show();
		  $('#alert_items').getNiceScroll().resize();
        });
			
			</script>



  <!-- FOOTER區塊開始 -->
  <div id="footer">
    <div id="footer_area">
		  <ul>
		    <!-- //20181221: 加入class="mobile_show"開始 -->
			<li class="mobile_show"><a href="https://www.pinpinbox.com/index/about/">關於我們</a></li>
			<li class="mobile_show"><a href="https://www.pinpinbox.com/index/tutorial/?tutorial_viewed=1" class="cicle_btn" style="z-index: 100">經營小幫手</a></li>
			<!-- //20181221: 加入class="mobile_show"結束 -->
			<li class="mobile_show"><a href="https://www.pinpinbox.com/index/index/qanda/">Q&amp;A</a></li>
			<li class="mobile_show" ><a href="https://www.pinpinbox.com/index/recruit/">企業合作</a></li>
			
			<li class="mobile_show"><a href="https://www.pinpinbox.com/index/index/privacy/">條款與政策</a></li>
			
			<!-- //20190121: 加入class="mobile_show"與置換成BOOTSTRAP選單開始 -->
			<!--
			<li class="dropup">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">下載APP<span class="caret"></span></a>
				<ul class="dropdown-menu" role="menu">
					<li><a id="apple_btn" data-url="pinpinbox://index/"  onclick="clickHandler(this.dataset.uri)" href="https://itunes.apple.com/tw/app/id1057840696">iOS</a></li>
					<li><a data-url="pinpinbox://index/"  onclick="clickHandler(this.dataset.uri)" href="https://play.google.com/store/apps/details?id=com.pinpinbox.android">Android</a></li>
				</ul>	
			</li>
            -->

           <li class="app_load mobile_show">
		     <div class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
			   <span>下載APP <i class="fa fa-caret-down"></i>
			 </div>
		   
		     <div class="dropdown-menu dropdown_menu" role="menu">
			   <ul>
				 <li><a id="apple_btn" data-url="pinpinbox://index/" target="_blank" onclick="clickHandler(this.dataset.uri)" href="https://itunes.apple.com/tw/app/id1057840696">iOS</a></li>
				 <li><a data-url="pinpinbox://index/" target="_blank" onclick="clickHandler(this.dataset.uri)" href="https://play.google.com/store/apps/details?id=com.pinpinbox.android">Android</a></li>
			   </ul>
			 </div>
			</li>
			
			<!-- //20190121: li加入class="mobile_show"與置換成BOOTSTRAP選單結束 -->
			
			<!-- //20190121: 加入class="mobile_show"與置換成BOOTSTRAP選單開始 -->
			<!--
			<li class="dropup footer_lang">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">語言<span class="caret"></span></a>
				<ul class="dropdown-menu" role="menu">
					<li ><a href="javascript:void(0)" >English</a></li>
					<li ><a href="javascript:void(0)" >日本語</a></li>
					<li ><a href="javascript:void(0)" >簡體中文</a></li>
					<li ><a href="https://www.pinpinbox.com/index/?lang=zh_TW">繁體中文</a></li>
				</ul>							
			</li>
			-->
			
			<li class="footer_lang">
			  <div class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
			    <span>語言 <i class="fa fa-caret-down"></i>
			  </div>
			  <div class="dropdown-menu dropdown_menu" role="menu">
			    <ul>
				  <li><a href="javascript:void(0)" >English</a></li>
				  <li><a href="javascript:void(0)" >日本語</a></li>
				  <li><a href="javascript:void(0)" >簡體中文</a></li>
				  <li><a href="https://www.pinpinbox.com/index/?lang=zh_TW">繁體中文</a></li>
			    </ul>
			  </div>					
			</li>
			
			<!-- //20190121: li加入class="mobile_show"與置換成BOOTSTRAP選單結束 -->
			
			<li class="mobile_show mobile_footer_lang">
			  <!-- //20190121: 下拉選單改用BOOTSTRAP開始 -->
			  <!--
			  <select>
				<option>簡體中文</option>
				<option selected>繁體中文</option>
				<option>English</option>
			  </select>
			  -->
			  <span class="custom-select">

			    <!-- //20190122: 加上ONCHANGE與更改OPTION值為連結開始 -->
				<select id="lang_type" onchange="to_href();">
				   
				  <option value="https://www.pinpinbox.com/index/?lang=ch">簡體中文</option>
				  <option value="https://www.pinpinbox.com/index/?lang=zh_TW" selected>繁體中文</option>
				  <option value="https://www.pinpinbox.com/index/?lang=en">English</option>
				  
			    </select>
				<!-- //20190122: 加上ONCHANGE與更改OPTION值為連結結束 -->
			  </span>
			  <!-- //20190121: 下拉選單改用BOOTSTRAP結束 -->
			</li>
			
			<li class="footer_copyright">© 2018 pinpinbox All Rights Reserved.</li>
		  </ul>
	</div>
  </div>
  <!-- FOOTER區塊結束 -->

  <!-- 回上方箭頭與FB區塊開始  -->
  <div id="right_btn">
		<div id="scroll_top"  ><a href="javascript:void(0)"><img src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/icon_top.svg" ></a></div>
		<div id="p_link"><a href="https://www.pinpinbox.com/index/user/point/" target="_blank" title="P點購買"><img src="images/assets-v7/icon_p.svg" onerror="this.onerror=null; this.src='icon_p.svg'"></a></div>
  </div>
  <!-- 回上方箭頭與FB區塊結束 -->

  <script>

$(document).ready(function() {
 
  //回上方箭頭
  $(window).scroll(function() {
    if ($(this).scrollTop() > 495)  {          /* 要滑動到選單的距離 */
       $('#scroll_top').addClass('topFixed');   /* 幫選單加上固定效果 */
    } else {
      $('#scroll_top').removeClass('topFixed'); /* 移除選單固定效果 */
    }
  });
  $("#scroll_top").click(function(){
		$("html,body").animate({scrollTop:0},900);
		return false;
	});


  //PC版選單切換
  $('a#login').click(function() {
    $('#login_menu').toggleClass("login_menu_down");
	// 20190115 移除.search_menu區塊開始
	$('.search_menu').removeClass("search_menu_down");
	// 20190115 移除.search_menu區塊結束
	$('#notifier_menu').removeClass("login_menu_down");
  });
  
  $('a#notifier').click(function() {
    $('#notifier_menu').toggleClass("login_menu_down");
	$('#login_menu').removeClass("login_menu_down");
	// 20190115 移除.search_menu區塊開始
	$('.search_menu').removeClass("search_menu_down");
	// 20190115 移除.search_menu區塊結束
  });
  
  
  //搜尋選單切換
  $('.search_btn').click(function() {
	// 20190115 移除.search_menu區塊開始
    $('.search_menu').toggleClass("search_menu_down");
	// 20190115 移除.search_menu區塊結束
	$('#login_menu').removeClass("login_menu_down");
	$('#notifier_menu').removeClass("login_menu_down");
  });
  //MOBILE版搜尋切換
  $('.mobile_menu i:nth-child(1)').click(function() {
	$('#mobile_search').addClass("search_box_open");
	$('#header_top').addClass("header_top_close");
	
	// 20190115 關閉另兩個選單區塊開始
	$('#login_menu').removeClass("login_menu_down");
	$('#notifier_menu').removeClass("login_menu_down");	
	// 20190115 關閉另兩個選單區塊結束
	
  });
  //MOBILE版搜尋關閉
  $('.header_search_close').click(function(evt) {
	// 20190104 移除關閉選單區塊開始
	//closemenu(evt);
    // 20190104 移除關閉選單區塊結束
	
	$('#mobile_search').removeClass("search_box_open");
	$('#header_top').removeClass("header_top_close");
	// 20190115 移除.search_menu區塊開始
	$('.search_menu').removeClass("search_menu_down");
	// 20190115 移除.search_menu區塊結束
  
  });
  
  //MOBILE版選單切換
  $('#notifier_m').click(function() {
	
	// 20190114 修改寫法開始(註: 因打開了登入選單)
    $('#notifier_menu').toggleClass("login_menu_down");
	$('#login_menu').removeClass("login_menu_down");	
	// 20190114 修改寫法結束
	
  });
  $('#login_m').click(function() {
    
	// 20190114 修改寫法開始(註: 因打開了通知選單)
    $('#login_menu').toggleClass("login_menu_down");
	$('#notifier_menu').removeClass("login_menu_down");	
	// 20190114 修改寫法結束
	
	
  });
  
  
  //當WIDNWOS RESIZE時, 關閉選單
  $( window ).resize(function(evt) {
      	  
	 // 20190104 移除關閉選單區塊開始
	//closemenu(evt);
    // 20190104 移除關閉選單區塊結束
	  	  
	  // 20190111 會在手機上影響輸入(輸入塊把視窗往上調), 關閉視窗改變偵測區塊開始
	  // 20191114 加入表頭搜尋區塊開始
	  //$('.header_search_close').click();
	  // 20191114 加入表頭搜尋區塊結束
	  // 20190111 會在手機上影響輸入(輸入塊把視窗往上調), 關閉視窗改變偵測區塊結束
  });
  
	//按選單之外關閉選單
	// 20190104 移除關閉選單區塊開始
	$('body').click(function(evt) {
	    
	//closemenu(evt);
   
     });
	  // 20190104 移除關閉選單區塊結束
});
//關閉選有選單
// 20190104 移除關閉選單區塊開始
/*
function closemenu(evt){
    if(event.target.id!="login" && $(evt.target).parents("#login").length==0 && $(evt.target).parents("#login_menu").length==0 && event.target.id != "login_menu"
    && event.target.id != "login_m" && $("#login_menu").hasClass( "login_menu_down" )){
	   $('#login_menu').removeClass("login_menu_down");
	}
	if(event.target.id != "notifier" && $(evt.target).parents("#notifier").length==0 && event.target.id != "notifier_menu"  
	  && $(evt.target).parents("#notifier_menu").length==0 && event.target.id != "notifier_m" && $("#notifier_menu").hasClass( "login_menu_down" )){
	    $('#notifier_menu').removeClass("login_menu_down");
	}
	if($(evt.target).parents(".search_btn").length==0 && $(evt.target).parents(".search_menu").length==0 && $(".search_menu").hasClass( "search_menu_down" )){
	   $('.search_menu').removeClass("search_menu_down");
	}
}
*/
// 20190104 移除關閉選單區塊結束

// 20190114 加上MARS FUNCTION, 讓搜尋選單切換字開始
  // MARS v2
    $('a.searchtype').on('click', function(){
        var device = $(this).data('device');
        $('span.search_type').html($(this).html());
        $('input#'+device+'_searchkey').data('searchtype' , $(this).data('searchtype'));
    });
    //暫代FUNCTION
	function _search(device = null) {
    
	}
// 20190114 加上MARS FUNCTION, 讓搜尋選單切換字結束


// 20190115 HEADER在首頁時, 移除底線區塊開始
  if($('#banner_catbtn').length>0){  //有輪播時不要底線
    $('#header').css('border','none');
  }

// 20190115 HEADER在首頁時, 移除底線區塊結束
// 20190115 在HEADER加上SCROLL時, 出現底線區塊開始
  window.onscroll = function() {header_sticky()};
  var header = document.getElementById("header");
  var sticky = header.offsetTop;
  function header_sticky(){
    if (window.pageYOffset > sticky){
      header.classList.add("header_sticky");
    }else{
      header.classList.remove("header_sticky");
    }
  }

// 20190115 在HEADER加上SCROLL時, 出現底線區塊結束




//20190121: 重製作下拉選單開始
	$(document).ready(function() {
      custom_select();
    });


function custom_select(){
  var x, i, j, selElmnt, a, b, c;
  /*look for any elements with the class "custom-select":*/
  x = document.getElementsByClassName("custom-select");  //包住SELECT元件的SPAN區塊
  for (i = 0; i < x.length; i++) {
    selElmnt = x[i].getElementsByTagName("select")[0];
	
	 /*for each element, create a new DIV that will act as the selected item:*/
     a = document.createElement("DIV");
     a.setAttribute("class", "select-selected"); //顯示被選的項目文字, 蓋在SELECT元件上
     a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;
	
	//查看是否已有仿製下拉選單
	if(!$('#'+selElmnt.id).siblings('.select-selected').length){
      x[i].appendChild(a);
	}
	if(!$('#'+selElmnt.id).siblings('.select-items').length){
		
	  /*for each element, create a new DIV that will contain the option list:*/
      b = document.createElement("DIV");
      b.setAttribute("class", "select-items select-hide"); //仿下拉選單
      for (j = 0; j < selElmnt.length; j++) {
        /*for each option in the original select element,
        create a new DIV that will act as an option item:*/
        c = document.createElement("DIV"); //仿每一筆下拉選單值OPTION
        c.innerHTML = selElmnt.options[j].innerHTML; //仿選單的顯示值等於被選的SELECT顯示值
		//特殊處理部份: 專區編輯頁面開始
		//只給興趣的仿下拉選單項目ID值同SELECT下拉選單值
		if(selElmnt.id == 'hobby_0' || selElmnt.id == 'hobby_1' || selElmnt.id =='hobby_2'){
		  if(j>0){
		    c.setAttribute("id", selElmnt.id+"_"+selElmnt.options[j].value); //第一筆請選擇不理會它
		  }
		}
		//特殊處理部份: 專區編輯頁面結束
		var tempClassName = selElmnt.options[j].value; //給仿下拉選單CLASS NAME同SELECT下拉選單值
        c.addEventListener("click", function(e) {
        //console.log(tempClassName);        
		/*when an item is clicked, update the original select box,
          and the selected item:*/
          var y, i, k, s, h;
          s = this.parentNode.parentNode.getElementsByTagName("select")[0]; //select物件
          h = this.parentNode.previousSibling; //顯示被選項目值		 
          for (i = 0; i < s.length; i++) {
            if (s.options[i].innerHTML == this.innerHTML) {
              s.selectedIndex = i;  //透過被選項目值更改SELECT被選值
			  $('#'+s.id).change(); //若原SELECT物件有設ONCHANGE時, 執行FUNCTION
              h.innerHTML = this.innerHTML;
			  y = this.parentNode.getElementsByClassName("same-as-selected"); //仿製下拉選單先前被選的項目
              for (k = 0; k < y.length; k++) {
                y[k].removeAttribute("class"); //之前被選的OPTION
              }
              this.setAttribute("class", "same-as-selected");  //新被選的OPTION
              break;
            }
          }
          h.click();
        });
        b.appendChild(c);
      }
      x[i].appendChild(b);
	}
    //if(!$('#'+selElmnt.id).siblings('.select-selected').length){
      a.addEventListener("click", function(e) {
        /*when the select box is clicked, close any other select boxes,
        and open/close the current select box:*/
        e.stopPropagation();
        closeAllSelect(this);
        this.nextSibling.classList.toggle("select-hide");
        this.classList.toggle("select-arrow-active");
      });
	//}
  }// for
  
  //特殊處理部份: 專區編輯頁面開始
  if($('#user_address_id_1st').length>0){
    addr2_list();	//當頁面載入時先關閉城市選單
  }
  //特殊處理部份: 專區編輯頁面結束
}//custom_select()

function closeAllSelect(elmnt) {
  /*a function that will close all select boxes in the document,
  except the current select box:*/
  var x, y, i, arrNo = [];
  x = document.getElementsByClassName("select-items");
  y = document.getElementsByClassName("select-selected");
  for (i = 0; i < y.length; i++) {
    if (elmnt == y[i]) {
      arrNo.push(i)
    } else {
      y[i].classList.remove("select-arrow-active");
    }
  }
  for (i = 0; i < x.length; i++) {
    if (arrNo.indexOf(i)) {
      x[i].classList.add("select-hide");
    }
  }
}
/*if the user clicks anywhere outside the select box,
then close all select boxes:*/
document.addEventListener("click", closeAllSelect);


  //20190121: 重製作下拉選單結束

  //20190121: 加入原有的DEEPLINK FUNCTION開始
  function clickHandler(uri) {
    deeplink.open(uri);

    return false;
}
//20190121: 加入原有的DEEPLINK FUNCTION開始

//20190122: 依使用者所選語系跳轉頁面開始
function to_href(){
  var lang_type = document.getElementById("lang_type");
  var lang_type_url = lang_type.options[lang_type.selectedIndex].value;
  location.href = lang_type_url;
}
//20190122: 依使用者所選語系跳轉頁面結束
</script>
  
  </body>
</html>