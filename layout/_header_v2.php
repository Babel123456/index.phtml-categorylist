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
  <div id="header_v2" class="header_v2">
  
    <div id="header_v2_top" class="header_v2_top">
	
	  <div class="top_left_area">
		<div class="service_link"><a href="#">Service</a></div>
		<div class="service_link"><a href="#">Content</a></div>
		<div class="social_link">
		  <a href="https://www.facebook.com/pinpinboxtw/" title="facebook" target="_blank">
		    <img src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/assets-v7/social_facebook.png" title="facebook">
          </a>
		</div>
		<div class="social_link">
		  <a href="https://www.youtube.com/channel/UCAUmAHykldcgI7Vc4OgyOPA" title="youtube" target="_blank">
		    <img src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/assets-v7/social_youtube.png">
		  </a>
		</div>
	  </div>
	  
	  <div class="top_center_area">
	    <div class="header_logo_v2"><a href="https://www.pinpinbox.com/">pinpinbox釘釘吧</a></div>
	  </div>
	  
	  <div class="top_right_area">
	    
		<!-- 搜尋區塊開始 -->
		<div id="header_search_v2" class="header_search_v2">
		  <div class="header_search_box">
			  <div class="search_btn">
			    <div class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
				  <span class="search_type" data-searchtype="user">創作人</span> <i class="fa fa-caret-down"></i>
				</div>
				<div class="dropdown-menu dropdown_menu" role="menu">
				  <ul>
					<li><a href="javascript:void(0)"  class="searchtype" data-searchtype="user" data-device="">創作人</a></li>
			        <li><a href="javascript:void(0)"  class="searchtype" data-searchtype="album" data-device="">作品</a></li>
				  </ul>
				</div>
			  </div>
			  <div class="search_input_text"><input type="text" class="search_text" name="searchkey" id="searchkey" data-searchtype="user" data-device="" autocomplete="off"></div>
              <div class="search_icon">
			    <a href="javascript:void(0);" onclick="_search()"><i class="fa fa-search"></i></a>
			  </div>
		  </div>
		</div>
		<!-- 搜尋區塊結束 -->
		
		
		<!-- 登入區塊開始 -->
		<!-- 電腦版選單開始 -->
		<div class="header_menu_v2">
		  
		  <div class="login_menu_area">
		  
		    <!-- 大版未登入時出現區塊開始-->
			<!--
			<div class="sign_link">
		      <a href="#">Sign in | up</a>
		    </div>
			-->
			<!-- 大版未登入時出現區塊結束-->
			
		    
			<div id="login_link" class="login_link" class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
		      
			  <a href="javascript:void(0);" id="login" class="login">
                <img onerror="this.src='<?=$url_prefix?>/static_file/pinpinbox/zh_TW/images/face_sample.png'" src="<?=$url_prefix?>/storage/zh_TW/user/4581/picture$18d7.jpg">
              </a>
			  <i id="login_w" class="fa fa-bars login_w"></i>
			
			</div>
		    
		    <div id="login_menu" class="login_menu dropdown-menu dropdown_menu" role="menu">
			
		      <ul>		
			  　<!-- 已登入會出現區塊開始 -->
								
		        <li>
		            <ul>
					  <li class="login_menu_user_info">
				        <div class="login_menu_user_info_image">
					      <div class="profile_img_wrap">
			                <img class="profile_img" src="https://cdn.pinpinbox.com/storage/zh_TW/user/4069/picture$ea13.jpg">
		                  </div>
					    </div>
					    <div class="login_menu_user_info_name">Michael Jordan</div>
				      </li>
					
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
				
				<!-- 已登入會出現區塊結束 -->
				
				<li>
		            <ul>
					  <!-- 未登入小版區塊開始 -->
					  <!--
				      <li class="sign_link">
		                <ul>
			              <li><a href="#">Sign in | up</a></li>
			            </ul>
		              </li>
				      -->
					  <!-- 未登入小版區塊結束 -->
					
					  <!-- 有無登入都出現區塊開始 -->
			          <li><a href="#">Service</a></li>
					  <li><a href="#">Contact</a></li>
			          <li class="social_link_area">
					    <div class="social_link">
		                  <a href="https://www.facebook.com/pinpinboxtw/" title="facebook" target="_blank">
		                    <img src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/assets-v7/social_facebook.png" title="facebook">
                          </a>
		                </div>
		                <div class="social_link">
		                  <a href="https://www.youtube.com/channel/UCAUmAHykldcgI7Vc4OgyOPA" title="youtube" target="_blank">
		                    <img src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/assets-v7/social_youtube.png">
		                  </a>
		                </div>
					  </li>
					  <!-- 有無登入都出現區塊結束 -->
			        </ul>
		        </li>
				
		      </ul>
			  
			  <!-- 登入後才會出現按鈕: 登入改英文字-->
		      <a href="#"><div class="btn_new">Sing out</div></a>
			  
	        </div>
			
		  </div><!-- .login_menu_area -->  
		  
		</div><!-- .header_menu -->
		<!-- 電腦版選單結束 -->
		<!-- 登入區塊結束 -->
		
		<!-- 手機版選單開始 -->
		<div id="mobile_menu_v2" class="mobile_menu_v2">
		  <div id="search_m" class="search_m"><i class="fa fa-search"></i></div>
		  <div id="login_m" class="login_m"><i class="fa fa-bars"></i></div>
		  <div id="header_search_close_v2" class="header_search_close_v2"><i class="fa fa-times"></i></div>
		</div>
		<!-- 手機版選單結束 -->		
		
	  </div><!-- .top_right_area -->
	
	</div>
	
  </div>
  <!-- 表頭區塊結束 -->