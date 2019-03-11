<!doctype html>
<html lang="zh">
<head>
    <link rel="icon" type="image/vnd.microsoft.icon" href="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/favicon.ico"/>
    <link rel="manifest" href="https://www.pinpinbox.com/manifest.json">
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width ,initial-scale=1.0">
	<link type="text/css" href="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/css/bootstrap.min.css" rel="stylesheet" />
	<link type="text/css" href="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/css/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link type="text/css" href="./css/style_v2.css" rel="stylesheet" />
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