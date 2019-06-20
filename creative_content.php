<?php include_once('layout/_header.php') ?>

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
  
  
  <?php include_once('layout/_report.php') ?>
  
  
  <!-- 測試預設值 (B的靜態頁面測試使用) 區塊開始 -->
  <?php
    $type = 'creative'; //user
	//$type = 'album';
  ?>
  <!-- 測試預設值 (B的靜態頁面測試使用) 區塊開始 -->
  
  
  <!-- 主要內容區開始 -->
  <div id="content">
    <!-- 創作人內容區塊開始 -->
	<div class="creative_content_header">
	  <div class="creative_info_area">
	  
	    <!-- 20190318: 加class name, data-toggle開始 -->
	    <div id="creative_name" class="creative_name dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false" >
		<!-- 20190318: 加class name, data-toggle結束 --> 
		
		  <span>
		    <div class="profile_img_wrap">
			  <img class="profile_img" src="https://cdn.pinpinbox.com/storage/zh_TW/user/4119/picture$7d29.jpg" title="點我分享">
		    </div>
		  </span>
		  <span title="煒承Answer煒承AnswerSung">煒承Answer煒承Sung煒承Answer煒承Sung煒承Answer煒承Sung煒承Answer煒承Sung</span>
		
		</div>
		
		<!-- 社群區塊開始  -->
		<!-- 20190318: 加class name與role開始 -->
		<div id="creative_content_social_links_box" class="social_links_box dropdown-menu dropdown_menu" role="menu">
		<!-- 20190318: 加class name與role結束 -->
		
		  <div class="social_links_title">分享</div>
		  <div class="social_links_box_area">
		     <div id="creative_content_social_links" class="social_links">
             <div class="addthis_inline_share_toolbox"></div>
			 <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5c234e41206c2da6"></script>
			 <a href="href="https://cdn.pinpinbox.com/storage/zh_TW/user/3876/album/15124/qrcode$d2f5.jpg" title="顯示QRcode" target="_blank"><img src="images/assets-v7/qr_square.svg" >
			 </a>
			</div>
	        <div id="creative_content_social_links2" class="social_links2" title="複製作品網址" ><i class="fa fa-link"></i></div>
		  </div>
		</div>
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
		    <!-- 20190318 加class name區塊開始 -->	
		    <div id="mobile_tab" class="dropdown-sign dropdown-toggle " data-toggle="dropdown" aria-expanded="false"><i class="fa fa-list-ul"></i><span id="tab_title">作品集</span></div>
		    <!-- 20190318 加class name區塊結束 -->
			<!-- 手機版列表選單結束 -->
			
			<!-- 20190318 加div, class name區塊開始 -->
			<div id="mobile_tab_menu" class="mobile_tab_menu" role="menu">
			  <ul class="nav nav-pills">
			  <!-- 20190318 加div, class name區塊結束 -->
			    <li class="active"><a data-toggle="tab" href="#tab0" onclick="$('#mobile_tab #tab_title').text('作品集');"><!--我的作品-->作品集</a></li>
		        <li><a data-toggle="tab" href="#tab1" onclick="$('#mobile_tab #tab_title').text('收藏˙贊助');">收藏˙贊助</a></li>
			    <li><a data-toggle="tab" href="#tab2" onclick="$('#mobile_tab #tab_title').text('群組作品');">群組作品</a></li>
		        <li><a data-toggle="tab" href="#tab3" onclick="$('#mobile_tab #tab_title').text('關於我');">關於我</a></li>
		        <li><a data-toggle="tab" href="#tab4" onclick="$('#mobile_tab #tab_title').text('留言版');">留言版</a></li>
		      </ul>
			
			<!-- 20190318 加div, class name區塊開始 -->
			</div>
			<!-- 20190318 加div, class name區塊結束 -->
			
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
			
			  <?php include_once('layout/_message_board.php') ?>
			  
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
    
	  // 20190318: 加入大小版選單切換開始
      mobile_tab_menu_show();
	  // 20190318: 加入大小版選單切換結束
	
	
      //當WIDNWOS RESIZE時, 關閉選單
      $( window ).resize(function(evt) {
		  
		  
		// 20190318: 加入大小版選單切換開始
        mobile_tab_menu_show();
	    // 20190318: 加入大小版選單切換結束
		  
		  
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
	
	// 20190318: 移除開始
	//手機版選單列表
	//$('#mobile_tab').on('click', function(){
	  //$('.mobile_tab_menu').toggleClass("mobile_tab_down");
    //});
	// 20190318: 移除開始
	
	// 20190318: start here
	//大小版選單切換
	function mobile_tab_menu_show(){
	  if($( window ).width() <= 768){
	    $('#mobile_tab_menu').addClass('dropdown-menu dropdown_menu');
	  }else{
	    $('#mobile_tab_menu').removeClass('dropdown-menu dropdown_menu');
	  }
	}
	// 20190318: end here
	
	
	
	
	
	
		
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
	            zIndex : 1000,
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
	    
		
		// 20190318: 移除開始
		//$('#creative_name, #creative_content_social_links_box').on('click', function(){
	      //$('#creative_content_social_links_box').toggleClass("show_block");
        //});
		// 20190318: 移除結束
					
	    $('.alert_btn').on('click', function(){
			
		  // 20190107: 當按下檢舉而選單沒有關閉時 開始
		  $('[data-toggle="dropdown"]').parent().removeClass('open');
		  // 20190107: 當按下檢舉而選單沒有關閉時 結束
		  
          $('[data-toggle="dropdown"]').parent().addClass('open');
	      
		  $('#alert_box_new').show();
		  $('#alert_items').getNiceScroll().resize();
        });
			
			</script>


<?php include_once('layout/_footer.php') ?>
