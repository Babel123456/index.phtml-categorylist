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
  
  <!-- //20190620 加class V2 -->
  <div id="header" class="header header_v2">
  
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
	  
		
		<!-- //20190620 增加"service, contact, fb, youtube"區塊開始 -->
		<div class="logo_left_area">
		  <div class="service_link"><a href="#">Service</a></div>
		  <div class="service_link"><a href="#">Content</a></div>
		  <div class="social_link">
		    <a href="https://www.facebook.com/pinpinboxtw/" title="facebook" target="_blank">
			  <img src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/assets-v7/social_facebook.png" title="facebook">
            </a>
		  </div>
		  <div class="social_link">
		    <a href="https://www.youtube.com/channel/UCAUmAHykldcgI7Vc4OgyOPA" title="youtube" target="_blank">
			  <img src="images/assets-v7/social_youtube.png">
			</a>
		  </div>
		</div>
		<!-- //20190620 增加"service, contact, fb, youtube"區塊開始 -->
		
		
		<div class="header_logo"><a href="https://www.pinpinbox.com/">pinpinbox釘釘吧</a></div>
		<div class="header_search">
		  <div class="header_search_box">
			  <!-- //20190620 div排列順序交換區塊開始 -->
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
			  
			  <input type="text" class="search_text" name="searchkey" id="desktop_searchkey" data-searchtype="user" data-device="desktop" autocomplete="off">
              
			  <a href="javascript:void(0);" onclick="_search('desktop')"><i class="fa fa-search"></i></a>
			  <!-- //20190620 div排列順序交換區塊結束 -->
		  </div>
		</div>
		
		<!-- 電腦版選單開始 -->
		<div class="header_menu">
		  
		  <div class="login_menu_area">
		  
		    <!-- 未入時-->
			<div class="sign_link">
		      <a href="#">Sign in | up</a>
		    </div>
		    <!--
		    <a href="javascript:void(0);" id="login" class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
              <img onerror="this.src='<?=$url_prefix?>/static_file/pinpinbox/zh_TW/images/face_sample.png'" src="<?=$url_prefix?>/storage/zh_TW/user/4581/picture$18d7.jpg">
            </a>
		    -->
		    <div id="login_menu" class="login_menu dropdown-menu dropdown_menu" role="menu">
			
		      <ul>		
			  　<!-- 有登入會出現區塊開始 -->
				<li class="login_menu_user_info"><!-- 小版出現 -->
				      <div class="login_menu_user_info_image">
					    <div class="profile_img_wrap">
			              <img class="profile_img" src="https://cdn.pinpinbox.com/storage/zh_TW/user/4069/picture$ea13.jpg">
		                </div>
					  </div>
					  <div class="login_menu_user_info_name">Michael Jordan</div>
				</li>
				
								
		        <li>
		            <ul>
			          <li><a href="creative_content.php#tab0">我的作品</a></li>
			          <li><a href="creative_content.php#tab1">收藏・贊助</a></li>
			          <li><a href="creative_content.php#tab2">群組作品</a></li>
			          <li><a href="creative_edit.php">資訊編輯</a></li>
			        </ul>
		        </li>
				
		        <li>
			        <ul>
			          <li><a href="#">積分管理</a></li>
			          <li><a href="#">P點購買 | 查詢</a></li>
			        </ul>
		        </li>
				
		        <li>
		            <ul>
			          <li><a href="#">匯款資料變更</a></li>
					  <li><a href="#">回報問題</a></li>
			          <li><a href="#">回報記錄</a></li>
			        </ul>
		        </li>
				<!-- 有登入會出現區塊結束 -->
				
				
				<!-- 有無登入都出現區塊開始 -->
				 <li>
		            <ul>
			          <li><a href="#">Service</a></li>
					  <li><a href="#">Contact</a></li>
			          <li>
					   <div></div>
					   <div></div>
					  </li>
			        </ul>
		        </li>
				
				<!-- 有無登入都出現區塊結束 -->
				
		      </ul>
			  
			  
			  <!-- 登入改英文字-->
		      <a href="#"><div class="btn_new">Sing out</div></a>
			  
	        </div>
		  
		  </div>
		  
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
	  
	  
  </div>
  <!-- 表頭區塊結束 -->