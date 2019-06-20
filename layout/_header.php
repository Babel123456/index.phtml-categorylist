<!-- 測試預設值 (B的靜態頁面測試使用) 區塊開始 -->
  <?php
  
   //20190520: 早上網路大當機, 更改一下網路前綴, 讓作業順利進行
  
    //$url_prefix = './pinpinbox_test'; //本機路徑
	 
    $url_prefix = 'https://cdn.pinpinbox.com'; //網路上路徑
	
  ?>
  <!-- 測試預設值 (B的靜態頁面測試使用) 區塊開始 -->


<!doctype html>
<html lang="zh">
<head>
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?=$url_prefix?>/static_file/pinpinbox/zh_TW/images/favicon.ico"/>
    <link rel="manifest" href="https://www.pinpinbox.com/manifest.json">
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width ,initial-scale=1.0">
	<link type="text/css" href="<?=$url_prefix?>/static_file/pinpinbox/zh_TW/css/bootstrap.min.css" rel="stylesheet" />
	<link type="text/css" href="<?=$url_prefix?>/static_file/pinpinbox/zh_TW/css/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link type="text/css" href="./css/style_v2.css" rel="stylesheet" />
    <script type="text/javascript" src="<?=$url_prefix?>/static_file/pinpinbox/zh_TW/js/browser-deeplink-master/browser-deeplink.min.js"></script>
</head>
<body>
  <script type="text/javascript" src="<?=$url_prefix?>/static_file/pinpinbox/zh_TW/js/jquery-1.11.3.min.js"></script>
  <script type="text/javascript" src="<?=$url_prefix?>/static_file/pinpinbox/zh_TW/js/bootstrap.min.js"></script>
  <!-- 表頭區塊開始 -->
  <div id="header">
  <!-- 手機版搜尋區塊開始 -->
    <div id="mobile_search">
	  <div class="header_search">
		  <div class="header_search_box">
		     <a href="javascript:void(0);" onclick="_search('mobile')"><i class="fa fa-search"></i></a>
			  <input type="text" class="search_text" name="searchkey" id="mobile_searchkey" data-searchtype="user" data-device="mobile" autocomplete="off">
              <div class="search_btn">
			    <div class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
				  <span class="search_type" data-searchtype="user">創作人</span> <i class="fa fa-caret-down"></i>
				</div>
				<div class="dropdown-menu dropdown_menu" role="menu">
				  <ul>
					<li><a href="javascript:void(0)"  class="searchtype" data-searchtype="user" data-device="mobile">創作人</a></li>
			        <li><a href="javascript:void(0)"  class="searchtype" data-searchtype="album" data-device="mobile">作品</a></li>
				  </ul>
				</div>
			  </div>
		  </div>
		</div>
	  <div class="header_search_close"><i class="fa fa-times"></i></div>
	</div>
	<!-- 電腦版搜尋區塊開始 -->
	  <div id="header_top">
		<div class="header_logo"><a href="https://www.pinpinbox.com/">pinpinbox釘釘吧</a></div>
		<div class="header_search">
		  <div class="header_search_box">
			  <a href="javascript:void(0);" onclick="_search('desktop')"><i class="fa fa-search"></i></a>
			  <input type="text" class="search_text" name="searchkey" id="desktop_searchkey" data-searchtype="user" data-device="desktop" autocomplete="off">
              <div class="search_btn">
			    <div class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
				  <span class="search_type" data-searchtype="user">創作人</span> <i class="fa fa-caret-down"></i>
				</div>
				<div class="dropdown-menu dropdown_menu" role="menu">
				  <ul>
					<li><a href="javascript:void(0)"  class="searchtype" data-searchtype="user" data-device="desktop">創作人</a></li>
			        <li><a href="javascript:void(0)"  class="searchtype" data-searchtype="album" data-device="desktop">作品</a></li>
				  </ul>
				</div>
			  </div>
		  </div>
		</div>
		
		<!-- 電腦版選單開始 -->
		<div class="header_menu">
		  
		  <!-- 20190314: 加DIV去包A 選單開始 -->
		  <div class="create_album_area">
		    <a href="#">建立作品</a>
		  </div>
		  <!-- 20190314: 加DIV去包A 選單結束 -->
		  
		  <!-- 20190314: 加DIV去包A 選單開始 -->
		  <div class="notifier_menu_area">
		    <!-- 20190314: 改與加class name開始 -->
		    <a href="#" id="notifier" class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">通知<span class="notifier_icon"></span></a>
		  
		    <div id="notifier_menu" class="notifier_menu dropdown-menu dropdown_menu" role="menu">
			  <!--
			  <ul class="noticeWarpper">
			    <li>
                  <a href="javascript:void(0)">
                    <div class="notifier_img_c"><img src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/m_logo.png" onerror="this.src='https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/face_sample.png'"></div>
                    <div class="notifier_info"><span>目前沒有通知!</span><span><time datetime="null" class="timeago"></time></span></div>
                  </a>
                </li>
			  </ul>
              <a href="https://www.pinpinbox.com/index/notifications/"><div class="btn_new">通知中心</div></a>
			  -->
			  <ul>
			    <!-- 20190314: 改class name結束 -->
		        <li><a href="#">
		          <div class="notifier_img_c"><img src="<?=$url_prefix?>/storage/zh_TW/user/44/picture$f673.jpg"></div>
		          <div class="notifier_info"><span>李登輝邀請您共用作品!立即進入?李登輝邀請您共用作品!立即進入?李登輝邀請您共用作品!立即進入?李登輝邀請您共用作品!立即進入?</span>
			        <span><time datetime="2018-10-16 12:59:30" class="timeago"></time></span></div></a></li>
		        <li><a href="#">
		          <div class="notifier_img_w"><img src="<?=$url_prefix?>/storage/zh_TW/user/3874/cover$f078.jpg"></div>
			      <div class="notifier_info"><span>Kevin發佈了新作品[ABCDE]</span>
			        <span><time datetime="2019-10-16 12:59:05" class="timeago"></time></span></div></a></li>
		        <li><a href="#">
		          <div class="notifier_img_w"><img src="<?=$url_prefix?>/storage/zh_TW/user/4119/picture$7d29.jpg"></div>
			      <div class="notifier_info"><span>周潤發發佈了新作品[賭神Beta]</span>
			      <span><time datetime="2017-10-16 12:58:57" class="timeago"></time></span></div></a></li>
		      </ul>
		      <a href="#"><div class="btn_new">更多</div></a>
	        </div>
		  
		  </div>
		  <!-- 20190314: 加DIV去包A 選單結束 -->
		  
		  
		  <!-- 20190314: 加DIV去包A 選單開始 -->
		  <div class="login_menu_area">
		    <!--<a href="#" id="login">登入 | 註冊</a>-->
		    <!-- 20190314: 改與加class name開始 -->
			
		    <a href="javascript:void(0);" id="login" class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
              <img onerror="this.src='<?=$url_prefix?>/static_file/pinpinbox/zh_TW/images/face_sample.png'" src="<?=$url_prefix?>/storage/zh_TW/user/4581/picture$18d7.jpg">
            </a>
		  
		    <div id="login_menu" class="login_menu dropdown-menu dropdown_menu" role="menu">
		    <!-- 20190314: 改與加class name結束 -->
	          <ul>
		        <!--
			    <li><div><ul>
			             <li><a href="#">Hi, 訪客</a></li>
			             <li><a href="#">登入</a></li>
			             <li><a href="#">註冊</a></li>
			             </ul></div><div></div>
		        </li>
			    -->
		        <li><div>babelbabelbabelbabelbabelbabel</div>
			        <div>
		              <ul>
			            <li><a href="creative_content.php#tab3">我的專區頻道</a></li>
			            <li><a href="creative_content.php#tab0">我的作品</a></li>
			            <li><a href="creative_content.php#tab1">我的收藏</a></li>
			            <li><a href="creative_content.php#tab2">共用協作</a></li>
			            <li><a href="creative_content.php#tab4">留言板</a></li>
			          </ul></div>
		        </li>
		        <li><div>管理中心</div>
			      <div>
			        <ul>
			          <li><a href="#">積分管理</a></li>
			          <li><a href="#">P點購買、消費查詢</a></li>
			          <li><a href="#">會員資料</a></li>
			        </ul></div>
		        </li>
		        <li><div>系統</div>
			      <div>
		            <ul>
			          <li><a href="#">回報問題</a></li>
			          <li><a href="#">回報記錄</a></li>
			        </ul></div>
		        </li>
		      </ul>
		      <a href="#"><div class="btn_new">登出</div></a>
	        </div>
		  
		  </div>
		  <!-- 20190314: 加DIV去包A 選單結束 -->
		  
		</div>
		<div class="pin_creator">
		
		<!--
		  <div class="current_point">目前P點：41225</div>
		  <div class="buy_point"><a href="https://www.pinpinbox.com/index/user/point/">購買P點</a></div>
		-->
		</div>
		
		<!-- 電腦版選單開始 -->
		<!-- 手機版選單開始 -->
		<div class="mobile_menu">
		  <div class="mobile_img"><i id="search_m" class="fa fa-search"></i><i id="notifier_m" class="fa fa-bell"></i>
		  <!--<a href="https://w3.pinpinbox.com/index/user/login/">-->
		    <i id="login_m" class="fa fa-bars"></i>
		  <!--</a>--></div>
		</div>
		<!-- 手機版選單結束 -->
	  </div>
	  <!-- 20190314: 搬移到<a>通知旁開始 -->
	  <!--
	  <div id="login_menu">
	    <ul>
		<li><div><ul>
			  <li><a href="#">Hi, 訪客</a></li>
			  <li><a href="#">登入</a></li>
			  <li><a href="#">註冊</a></li>
			</ul></div><div></div>
		  </li>
		  <li><div>babelbabelbabelbabelbabelbabel</div><div>
		    <ul>
			  <li><a href="creative_content.php#tab3">我的專區頻道</a></li>
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
	  </div>-->
	  <!-- 20190314: 搬移到<a>通知旁結束 -->
	  
	  
	  
  </div>
  <!-- 表頭區塊結束 -->