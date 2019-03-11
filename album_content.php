
<?php include_once('layout/_header.php') ?>

  <link type="text/css" href="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/jquery-textcomplete/media/stylesheets/textcomplete.css" rel="stylesheet" />
  
  <link type="text/css" href="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/lightGallery-master/dist/css/lightgallery.min.css" rel="stylesheet" />
  <link type="text/css" href="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/lightGallery-master/dist/css/lightgallery-custom.min.css" rel="stylesheet" />
  
  <link type="text/css" href="https://www.pinpinbox.com/js/jBox-0.4.8/jBox.css" rel="stylesheet" />
  <link type="text/css" href="https://www.pinpinbox.com/js/jBox-0.4.8/plugins/Confirm/jBox.Confirm.css" rel="stylesheet" />
  
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/jquery-textcomplete/jquery.textcomplete.js"></script>
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/jquery-textcomplete/jquery.overlay.js"></script>
  
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/lightGallery-master/dist/js/lightgallery-all-modify.min.js"></script>
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/lightGallery-master/dist/js/lg-audio.min.js"></script>
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/lightGallery-master/dist/js/lg-subhtml.min.js"></script>
  
  <script type="text/javascript" src="https://www.pinpinbox.com/js/jBox-0.4.8/jBox.min.js"></script>
  <script type="text/javascript" src="https://www.pinpinbox.com/js/jBox-0.4.8/plugins/Confirm/jBox.Confirm.min.js"></script>
  
  
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/autolink-min.js"></script>
  
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/jquery-timeago-master/js/jquery.timeago.js"></script>
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/jquery-timeago-master/js/jquery.timeago.zh-TW.js"></script>
  
  <script type="text/javascript" src="https://ppb.sharemomo.com/static_file/pinpinbox/zh_TW/js/jquery.nicescroll.min.js"></script>
  
  <?php include_once('layout/_report.php') ?>
  
  
  <!-- 測試預設值 (B的靜態頁面測試使用) 區塊開始 -->
  <?php
    //$type = 'user';
	$type = 'album';
  ?>
  <!-- 測試預設值 (B的靜態頁面測試使用) 區塊開始 -->
  
  
  <!-- 主要內容區開始 --> 
  <div id="content">
    
    <div id="album_content_area" class="album_content_area">
	
	  <div class="album_content_area_warp">
	  
	    <!-- 作品資訊區塊開始 -->
		<div class="album_content_main_wrap">
	      <div id="album_content_main" class="album_content_main">
		    <div class="album_content_box">
			  <div class="album_content_box_img">
			    <div class="album_content_box_img_area">
				  <img src="https://img2.jiemian.com/101/original/20150626/143531518786448200_a580x330.jpg" 
			            alt="你愿意花10万元跟着BBC去非洲大草原旅行吗" 
						title="你愿意花10万元跟着BBC去非洲大草原旅行吗">
			    </div>
			    <div class="album_content_box_img_btn" onclick="browseKit_album('https://www.pinpinbox.com/index/album/show_photo/', {album_id: '15124', keyPress : 1})" ><div class="btn_new btn_dark_opacity">進入觀看</div></div>
			  </div>
			  <div class="album_content_box_info">
			    <div class="album_content_box_icon">
				  <div class="album_content_box_icons"><span><i class="fa fa-volume-down"></i><i class="fa fa-play-circle"></i><i class="fa fa-gift"></i></span></div>
				  <div class="album_content_box_pages"><span>14頁</span></div>
					  
				  <div class="album_content_box_menu_btn">
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
					 
				</div><!-- .content_box_icon -->
				<div class="content_box_name">你愿意花10万元跟着BBC去非洲大草原旅行吗</div>
			    <div id="album_content_desc" class="album_content_desc">
				  <div id="album_content_box_desc" class="album_content_box_desc" >用热感应设备观察野生动物，听BBC纪录片团队讲述当地故事，BBC的这趟非洲大草原的14日行程希望与更多的人分享纪录片拍摄的激情与故事。BBC在其纪录片拍摄地开发了“地球之旅”系列产品，并在游客旅行途中放映所到之处的纪录短片、讲述拍摄故事，引导游客用专业拍摄器材体验自然。最近一款非洲肯尼亚、坦桑尼亚的旅游线路在寻找中国游客。</div>
                  <div id="album_content_desc_more" class="btn_new">更多</div>
                </div><!-- .album_content_desc -->
			  </div><!-- .content_box_info -->
	        </div><!-- .album_content_box -->
				
				
			<!-- 作品資訊, 贊助/關注, 分享, 創作人資訊, 投票, 查看更多區塊開始 -->
			<div class="album_content_donate_area_wrap">
			
	          <div class="album_content_donate_area">
			    
				<div class="album_content_more_info_area">
				
				  <!-- 作品資訊 -->
				  <div class="album_content_more_info">
				    <div class="album_content_more_info_title">作品資訊</div>
					<div class="album_content_more_info_detail">
					  <div class="album_content_more_info_date"><span><i class="fa fa-clock-o"></i></span><span>2019/03/22</span></div>
					  <div class="album_content_more_info_location"><span><i class="fa fa-map-marker"></i></span><span>非洲大草原</span></div>
					  <div class="album_content_more_info_view"><span><i class="fa fa-eye"></i></span><span>788446</span></div>
					  <div class="album_content_more_info_pin">
					    <span><a href="javascript:void(0)" title="釘一下" onclick="likes('15124');"><i name="pin_likes" class="fa fa-thumb-tack"></i></a></span>
						<span>57</span>
					  </div>
				    </div>
				  </div>
					
				  <!-- 贊助/關注 -->
                  <div class="album_content_donate">
			        <div class="donate_btn">
			          <!-- 若作品有設定贊助時顯示贊助按鈕 若沒有則顯示收藏按鈕 -->
					  <!--<span class="btn_new btn_main">贊助</span>-->
					  <span class="btn_new btn_attention">已贊助</span>
					  <!-- //20190131: 贊助後變"已贊助", 收藏後變"已收藏"區塊結束 -->
		              <!--<span class="btn_new btn_pink">收藏</span>-->
			        </div>
			        <div class="donate_desc">
		              <!-- 若有贊助條件則顯示區塊開始 -->
		              <span>贊助條件</span>
		              <span>1200P</span>
		              <span class="p_to_nt">(600TWD)</span>
		              <!-- 若有贊助條件則顯示區塊結束 -->
		            </div>
	                <div class="album_content_back" onclick="$('.jBox-closeButton').click();">
		              <span><i class="fa fa-angle-left"></i></span>
		              <span>返回</span>
		            </div>
		          </div><!-- .album_content_donate -->
					
				  <!-- 社群區塊  -->
			      <div class="social_links_box_area">
			        <div class="social_links_box_area_wrap">
			          <div id="album_content_social_links_box" class="social_links_box">
			            <div class="social_links_title">分享此作品</div>
				        <div class="social_links_box_icons">
		                  <div id="album_content_social_links" class="social_links">
				
                            <!-- //20190131: div與a區塊間無空格開始 -->
					        <div class="addthis_inline_share_toolbox"></div><!-- Go to www.addthis.com/dashboard to customize your tools --><script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5c234e41206c2da6"></script><!-- //20181227: 加入QRCODE區塊開始 --><a href="href="https://cdn.pinpinbox.com/storage/zh_TW/user/3876/album/15124/qrcode$d2f5.jpg" title="顯示QRcode" target="_blank">
		                    <!-- //20190131: div與a區塊間無空格結束 -->
					  
					          <img src="images/assets-v7/qr_square.svg" >
							</a>
				  
		                  </div>
	                      <div id="album_content_social_links2" class="social_links2" title="複製作品網址" ><i class="fa fa-link"></i></div>
		                </div>
			          </div><!-- .social_links_box -->
			        </div><!-- .social_links_box_area_wrap -->
			      </div><!-- .social_links_box_area -->
			      <div id="snackbar">
		            <span>已複製到剪貼簿</span>
		            <span>
				      <input id="page_url" type="text" value="https://www.pinpinbox.com/index/album/content/?album_id=15124&click=name" autocomplete="off">
				    </span>
		          </div>
					
				</div><!-- .album_content_more_info_area -->
				  				
				
				<!-- 作者資訊區塊開始 -->
	            <div class="album_content_creative_info">
			 	  <div class="album_content_creative_info_wrap">
				  
				    <div class="album_content_creative_info_title_area">
					  <div class="album_content_creative_info_title">創作人</div>
					  <div class="album_content_creative_info_btn">
					    <a href="https://www.pinpinbox.com/index/creative/content/?user_id=11324" >
					      <span class="btn_new">更多他/她的作品</span>
						</a>
			          </div>
					</div>
				    <div class="album_content_creative_info_img_area">
					  <div class="album_content_creative_info_img_name">
				        <div class="album_content_creative_info_img"><a href="https://www.pinpinbox.com/papid" title="BBC纪录片团队">
						
						<!--//20190222: 新增div, img class name區塊開始-->
		                <div class="profile_img_wrap">
						  <img class="profile_img" onerror="this.src='https://cdn.pinpinbox.com/upload/pinpinbox/diy/20181108/5be3e59eb0174.jpg'" src="https://img2.jiemian.com/101/original/20170602/149639701848296700_a580x330.jpg">
						</div>
						<!--//20190222: 新增div, img class name區塊結束-->
						
						</a></div>
		                
						<div class="album_content_creative_info_name">
					      <span><a href="https://www.pinpinbox.com/papid" title="BBC纪录片团队">cailum_11111111111111</a></span>
					    </div>
                      </div><!-- .album_content_creative_info_img_name -->
					 
					  <div class="album_content_creative_info_img_btn">
					    <!--
					    <a name="buttonFollow" href="javascript:void(0)" onclick="follow();"><div class="btn_new btn_pink">關注</div></a>
						-->
						<a name="buttonFollow" href="javascript:void(0)" onclick="follow();"><div class="btn_new btn_attention">取消關注</div></a>
						
					  </div>
					  
					</div><!-- .album_content_creative_info_img_area -->
				  </div>
                </div><!-- .album_content_creative_info -->
			    <!-- 作者資訊區塊結束 -->
				
				<!-- 投票區塊開始 -->
				<div class="activity_vote">
				  <div class="activity_vote_desc">作品正在參加活動</div>
				  <div class="activity_vote_area">
					<span class="activity_vote_title">你愿意花10万元跟着BBC去非洲大草原旅行吗</span>
					<span class="btn_new btn_main">投票</span>
				  </div>
				</div>
				<!-- 投票區塊結束 -->
				
		        <!-- 你可能會想看區塊  -->
		        <div id="removeInPop" class="intro_see_more_area">
		          <div class="intro_title">你可能會想看</div>
		          <div class="intro_see_more">
		            <div class="intro_see_more_box" data-href="https://www.pinpinbox.com/index/album/content_v2/?album_id=15896&amp;categoryarea_id=3&amp;click=cover" title="蛋蛋俠要成功" >
                      <div class="intro_see_more_img">
					    <div class="intro_see_more_img_area">
						  <img src="https://img1.jiemian.com/101/original/20150626/143531521361286700_a580xH.jpg" onerror="this.src='https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/origin.jpg'">
						</div>
                      </div>
                      <div class="intro_see_more_name">这款产品为游客提供BBC专业的拍摄器材，并配备游猎小组专业向导，全程14天</div>
                    </div>
					
					<div class="intro_see_more_box" data-href="https://www.pinpinbox.com/index/album/content_v2/?album_id=15895&amp;categoryarea_id=3&amp;click=cover" title="功夫蛋蛋俠過端午">
                      <div class="intro_see_more_img">
                        <div class="intro_see_more_img_area"><img src="https://img3.jiemian.com/101/original/20150626/143531522381761500_a580xH.jpg"></div>
                      </div>
                      <div class="intro_see_more_name">游客将在夜间观赏BBC团队特别剪辑的订制短片，并有机会使用BBC的专业摄影摄像器材，从电影、电视从业者角度感受旅行，比如使用夜视镜和红外设别来捕捉只有夜间出没的动物影响，用专业设别探听海底的声音。</div>
                    </div>
					
					<div class="intro_see_more_box" data-href="https://www.pinpinbox.com/index/album/content_v2/?album_id=15863&amp;categoryarea_id=3&amp;click=cover" title="功夫蛋蛋俠過中秋">
                      <div class="intro_see_more_img">
                        <div class="intro_see_more_img_area"><img src="https://img1.jiemian.com/101/original/20150626/143531527077876400_a580xH.jpg" onerror="this.src='https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/origin.jpg'"></div>
                      </div>
                      <div class="intro_see_more_name">这里的小屋坐落在锯齿状轮廓的火山口边缘，灵感来自附近的奥杜威峡谷的史前遗址——“人类的摇篮”。</div>
                    </div>
					
					<div class="intro_see_more_box" data-href="https://www.pinpinbox.com/index/album/content_v2/?album_id=15858&amp;categoryarea_id=3&amp;click=cover" title="EGGTACK Logo">
                      <div class="intro_see_more_img">
                        <div class="intro_see_more_img_area"><img src="https://img2.jiemian.com/101/original/20150626/143531529296441700_a580xH.jpg" onerror="this.src='https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/origin.jpg'"></div>
                      </div>
                      <div class="intro_see_more_name">塞伦盖蒂四季酒店，位于非洲最高峰乞力马扎罗山下的安博塞利塞雷纳游猎山庄，以及非洲最奢华的度假村、“世界一流酒店组织”成员费尔蒙肯尼亚山野生动物园俱乐部。</div>
                    </div>
					
					<div class="intro_see_more_box" data-href="https://www.pinpinbox.com/index/album/content/?album_id=15122&categoryarea_id=13&click=cover" title="【公益募資】家園募資計劃｜小愉兒們想要有個家…｜小愉兒 x pinpinbox">
					  <div class="intro_see_more_img">
						<div class="intro_see_more_img_area"><img src="https://img3.jiemian.com/101/original/20150626/143531530273912000_a580xH.jpg" onerror="this.src='https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/origin.jpg'"></div>
					  </div>
					  <div class="intro_see_more_name">游客可以在Nabyuki镇观看当地纺织工人如何剪切、纺纱、染色和手工编织羊毛，并与沿途各地中国人民的老朋友密切交流。</div>
					</div>
					
					<div class="intro_see_more_box" data-href="https://www.pinpinbox.com/index/album/content/?album_id=10313&categoryarea_id=13&click=cover" title="小愉兒聖誕公益活動形象影片 | pinpinbox">
					  <div class="intro_see_more_img">
						<div class="intro_see_more_img_area"><img src="https://img1.jiemian.com/101/original/20150626/143531525965354500_a580xH.jpg" onerror="this.src='https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/origin.jpg'"></div>
					  </div>
				      <div class="intro_see_more_name">在马赛马拉动物保护区，游客可以乘坐热气球俯瞰大草原。</div>
					</div>
					
		          </div><!-- .intro_see_more -->
		        </div><!-- .intro_see_more_area  -->
				
		      </div><!-- .album_content_donate_area -->
	          
			</div><!-- .album_content_donate_area_wrap -->
			<!-- 作品資訊, 贊助/關注, 分享, 創作人資訊, 投票, 查看更多區塊結束 -->	
				
				
		    <!-- 留言區塊開始 -->
			
		    <!--//20190304: 嵌入留言區塊開始-->
			  <?php include_once('layout/_message_board.php') ?>
			  <!--//20190304: 嵌入留言區塊結束-->
			  
		    <!-- 留言區塊結束 -->
		  </div><!-- #album_content_main -->
			  
		</div><!-- .album_content_main_wrap -->
		
	  </div><!-- .album_content_area_warp -->
	</div><!-- .album_content_area -->
  </div><!-- #content -->
  <!-- 主要內容區結束 -->


<script type="text/javascript" name="popview">
  //若作品介紹超過4行, 則出現按鈕
  function desc_more_btn(){
	if($('#album_content_box_desc').height()>=100){
	  $('#album_content_desc_more').addClass('show_block');
	}
  }

  //頁面載入時, 判斷更多按鈕是否出現
  $(document).ready(function() {
    desc_more_btn();
	
  });
  
	
  //當被POPUP顯示時, 判斷更多按鈕是否出現
  $(document).ajaxComplete(function( event, request, settings ) {
    desc_more_btn();
  }); 
	
  //複製作品網址按鈕
  $('#album_content_social_links_box').on('click', '#album_content_social_links2', function() {
	snackbar_show() ;
  });
	
  //彈出網址已複製訊息
  function snackbar_show(){
	//顯示訊息
	var x = document.getElementById("snackbar");
    x.className = "show";
	setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
	//拷貝網址
	var copy_url = document.getElementById("page_url");
    copy_url.select();
    document.execCommand("copy");
  }
	
  $('#album_content_box_desc').html($('#album_content_box_desc').html().autoLink({target: "_blank"}));
    
  //作品資訊顯示更多按鈕, 當更多展開後移除收合按鈕
  $('#album_content_desc_more').on('click', function(){
    $('#album_content_box_desc').toggleClass('show_block');
	$('#album_content_desc_more').removeClass('show_block');
  });
 
  $('.album_content_box_menu_btn').on('click', function(){
	$('.content_box_menu').toggleClass("content_box_menu_down");
  });
 
	
  
	//進入觀看
	var askAutoPlay = true, 
	a_album_element = [],	//browseKit element of album
	a_album_item = [];		//browseKit item of album
	
	function browseKit_album(url, param) {
	param.pageCalledByXHR = $('#pageCalledByXHR').length;
	if (a_album_item.length == 0) {
		//$.post(url, param, function(r) {
			//r = $.parseJSON(r);
			
			r = {"result":1,
			     "data":{
				   "favorited":false,
				   "readable":[
				  {"name":"",
				   "description":"\u2192 \u8acb\u5411\u53f3\u7ffb\u9801\uff0c\u65bc\u6700\u672b\u9801\u652f\u6301\u5c0f\u6109\u5152 \u0295\u2022\u1d25\u2022\u0294",
				   "image":"https:\/\/cdn.pinpinbox.com\/upload\/pinpinbox\/diy\/20181108\/5be3e59eb0174.jpg",
				   "image_thumbnail":"https:\/\/cdn.pinpinbox.com\/upload\/pinpinbox\/diy\/20181108\/5be3e59eb0174_120x90.jpg",
				   "width":1067,
				   "height":800,
				   "usefor":"image",
				   "hyperlink":[],
				   "audio_mode":"none",
				   "audio_loop":null,
				   "audio_target":null,
				   "video_refer":"none",
				   "video_target":"",
				   "recommendedBuyAlbum":false},
				  {"name":"",
				   "description":"",
				   "image":"https:\/\/cdn.pinpinbox.com\/upload\/pinpinbox\/diy\/20181108\/5be3e59faf8ac.jpg",
				   "image_thumbnail":"https:\/\/cdn.pinpinbox.com\/upload\/pinpinbox\/diy\/20181108\/5be3e59faf8ac_120x90.jpg",
				   "width":1067,
				   "height":800,
				   "usefor":"image",
				   "hyperlink":[],
				   "audio_mode":"none",
				   "audio_loop":null,
				   "audio_target":null,
				   "video_refer":"none",
				   "video_target":"",
				   "recommendedBuyAlbum":false},
				  {"name":"",
				   "description":"",
				   "image":"https:\/\/cdn.pinpinbox.com\/upload\/pinpinbox\/diy\/20181108\/5be3e5a09902e.jpg",
				   "image_thumbnail":"https:\/\/cdn.pinpinbox.com\/upload\/pinpinbox\/diy\/20181108\/5be3e5a09902e_120x90.jpg",
				   "width":1067,
				   "height":800,
				   "usefor":"image",
				   "hyperlink":[],
				   "audio_mode":"none",
				   "audio_loop":null,
				   "audio_target":null,
				   "video_refer":"none",
				   "video_target":"",
				   "recommendedBuyAlbum":false},
				  {"name":"",
				   "description":"",
				   "image":"https:\/\/cdn.pinpinbox.com\/upload\/pinpinbox\/diy\/20181108\/5be3e661ecf0f.jpg",
				   "image_thumbnail":"https:\/\/cdn.pinpinbox.com\/upload\/pinpinbox\/diy\/20181108\/5be3e661ecf0f_120x90.jpg",
				   "width":1067,
				   "height":800,
				   "usefor":"image",
				   "hyperlink":[{"icon":"","text":"\u9ede\u6211\u6d3d\u8a62pinpinbox\u7c89\u7d72\u5718","url":"https:\/\/www.facebook.com\/cpinpinbox\/?ref=aymt_homepage_panel"},{"icon":"","text":"\u9ede\u6211\u8cfc\u8cb7P\u9ede","url":"https:\/\/www.pinpinbox.com\/index\/user\/point\/"}],
				   "audio_mode":"none",
				   "audio_loop":null,
				   "audio_target":null,
				   "video_refer":"none",
				   "video_target":"",
				   "recommendedBuyAlbum":false},
				  {"name":"",
				   "description":"\u652f\u6301\u5c0f\u6109\u5152\uff0c\u8acb\u9ede\u4e0b\u65b9\u9023\u7d50\u89c0\u770b\u66f4\u591a\u52df\u8cc7\u8a08\u5283\u3002",
				   "image":"https:\/\/cdn.pinpinbox.com\/upload\/pinpinbox\/diy\/20181108\/5be3e5a246450.jpg",
				   "image_thumbnail":"https:\/\/cdn.pinpinbox.com\/upload\/pinpinbox\/diy\/20181108\/5be3e5a246450_120x90.jpg",
				   "width":1067,
				   "height":801,
				   "usefor":"image",
				   "hyperlink":[{"icon":"","text":"$360\u8056\u8a95\u5e78\u798f\u79ae\u76d2","url":"https:\/\/www.pinpinbox.com\/index\/album\/content\/?album_id=15123&categoryarea_id=13&autoplay=1"},{"icon":"","text":"\u5bb6\u5712\u52df\u8cc7\u8a08\u5283","url":"https:\/\/www.pinpinbox.com\/index\/album\/content\/?album_id=15122&categoryarea_id=13&autoplay=1"}],
				   "audio_mode":"none",
				   "audio_loop":null,
				   "audio_target":null,
				   "video_refer":"none",
				   "video_target":"",
				   "recommendedBuyAlbum":false},
				  {"image":"https:\/\/cdn.pinpinbox.com\/static_file\/pinpinbox\/zh_TW\/images\/preview_end_all.jpg",
				   "image_thumbnail":"https:\/\/cdn.pinpinbox.com\/static_file\/pinpinbox\/zh_TW\/images\/preview_end_all_80x120.jpg",
				   "width":668,
				   "height":1002,
				   "recommendedBuyAlbum":true,
				   "audio_mode":null,
				   "audio_loop":null,
				   "audio_target":null}],
				   "property":{
					 "album":{
					   "data":{
						 "album":{
						   "category_id":181,
						   "display_num_of_collect":false,
						   "template_id":0,
						   "name":"\u3010\u516c\u76ca\u52df\u8cc7\u3011\u8a8d\u8cfc$60\u624b\u5de5\u9905\u4e7e\uff5c\u6109\u4f60\u540c\u5728\uff0c\u9001\u611b\u5230\u80b2\u5e7c\u9662\uff5c\u5c0f\u6109\u5152 x pinpinbox",
						   "description":"\u2726 \u4e00\u4efd\u6eab\u6696\u3001\u5169\u500d\u611b\u5fc3\n\u4e0d\u662f\u6bcf\u500b\u5b69\u5b50\u90fd\u64c1\u6709\u6eab\u6696\u7684\u8056\u8a95\u7bc0\uff0c\u6211\u5011\u5e0c\u671b\u900f\u904e\u89aa\u624b\u88fd\u4f5c\u7684\u624b\u5de5\u611b\u5fc3\u9905\u4e7e\uff0c\u7d66\u4e88\u80b2\u5e7c\u9662\u7684\u5b69\u5b50\u5011\u6eab\u6696\u3002\n\u53ea\u8981$60\u5c31\u80fd\u8a8d\u8cfc\u4e00\u4efd\u9905\u4e7e\uff0c\u6211\u5011\u5c07\u66ff\u60a8\u628a\u9019\u4efd\u611b\u9001\u81f3\u80b2\u5e7c\u9662\uff0c\u540c\u6642\u5e6b\u52a9\u5c0f\u6109\u5152\u53ca\u5f31\u52e2\u5b69\u5b50\u5011\uff01\n\u9080\u8acb\u60a8\u548c\u5c0f\u6109\u5152\u4e00\u8d77\uff0c\u628a\u9019\u4efd\u611b\u9001\u7d66\u80b2\u5e7c\u9662\u7684\u5bf6\u8c9d\u5011\u3002\n\n\u2726 \u300c\u611b\u4ed6\uff0c\u5c31\u8981\u8b93\u4ed6\u81ea\u7acb\u81ea\u5f37\u300d\n\u5c0f\u6109\u5152\u662f\u7236\u6bcd\u5e0c\u671b\u300c\u6109\u5feb\u9577\u5927\u300d\u7684\u5fc3\u667a\u969c\u7919\u5b69\u5b50\u5011\uff0c\u66f4\u662f\u6700\u8a8d\u771f\u3001\u71b1\u60c5\u3001\u7528\u5fc3\u7684\u9ede\u5fc3\u70d8\u7119\u5e2b\uff01\n\n\u2726 \u60a8\u7684\u611b\u5fc3\u5c07\u65bc12\u6708\u8d77\u9001\u81f3\u53f0\u5317\u5404\u5927\u80b2\u5e7c\u9662\uff01\n\u8ca1\u5718\u6cd5\u4eba\u53f0\u7063\u5152\u7ae5\u66a8\u5bb6\u5ead\u6276\u52a9\u57fa\u91d1\u6703\u5927\u540c\u80b2\u5e7c\u9662\u3001\u8ca1\u5718\u6cd5\u4eba\u57fa\u7763\u6559\u53f0\u5317\u5e02\u79c1\u7acb\u4f2f\u5927\u5c3c\u5152\u5c11\u5bb6\u5712\u3001\u793e\u5718\u6cd5\u4eba\u4e2d\u83ef\u80b2\u5e7c\u6a5f\u69cb\u5152\u7ae5\u95dc\u61f7\u5354\u6703\u3001\u8ca1\u5718\u6cd5\u4eba\u57fa\u7763\u6559\u8056\u9053\u5152\u5c11\u798f\u5229\u57fa\u91d1\u6703\u9644\u5c6c\u53f0\u5317\u5e02\u79c1\u7acb\u8056\u9053\u5152\u7ae5\u4e4b\u5bb6\u3001\u8ca1\u5718\u6cd5\u4eba\u5929\u4e3b\u6559\u5584\u7267\u793e\u6703\u798f\u5229\u57fa\u91d1\u6703\u3001\u65b0\u5317\u666e\u8ce2\u6148\u6d77\u5bb6\u5712\u3001\u53f0\u7063\u5c55\u7fc5\u5354\u6703\u3001\u793e\u5718\u6cd5\u4eba\u4e16\u754c\u548c\u5e73\u6703\u2026\u6301\u7e8c\u589e\u52a0\u4e2d\u3002\n\n\u2713 \u4e0d\u77e5\u9053\u5982\u4f55\u8a8d\u8cfc\uff1f\u5c31\u8b93pinpinbox\u5c0f\u8001\u5e2b\u6559\u4f60\uff1a\nhttps:\/\/www.pinpinbox.com\/index\/album\/content\/?album_id=15111&categoryarea_id=11&autoplay=1\n\n\u2713 \u6ce8\u610f\u4e8b\u9805\uff1a\n1. \u8acb\u65bc\u8a8d\u8cfc\u5f8c\u7559\u4e0b\u8a73\u7d30\u8cc7\u6599\uff0c\u4ee5\u4fbf\u767c\u7968\u5bc4\u9001\u3002\n2. \u8cfc\u8cb7\u4efd\u6578\u53ca\u586b\u5bebP\u9ede(\u50f9\u9322)\u8acb\u898b\u4f5c\u54c1\u5167\u9801\u50f9\u76ee\u8868\u3002\n3. \u8a8d\u8cfc\u6b64\u5c08\u6848\uff0c\u60a8\u5c07\u4e0d\u6703\u7372\u5f97\u624b\u5de5\u9905\u4e7e\uff0c\u672c\u6b21\u52df\u8cc7\u9905\u4e7e\u5c07\u7d71\u4e00\u7531pinpinbox\u767c\u9001\u81f3\u53f0\u5317\u5404\u5927\u80b2\u5e7c\u9662\u3002\n\n\u2713 \u82e5\u6709\u4efb\u4f55\u7591\u554f\uff0c\u8acb\u6d3d\u300cpinpinbox\u300dFacebook\u7c89\u7d72\u5718\uff1a\nhttps:\/\/www.facebook.com\/cpinpinbox\/?ref=aymt_homepage_panel",
						   "cover":"pinpinbox\/diy\/20181108\/5be3e59eb0174.jpg",
						   "preview":"[\"pinpinbox\\\/diy\\\/20181108\\\/5be3e59eb0174.jpg\",\"pinpinbox\\\/diy\\\/20181108\\\/5be3e59faf8ac.jpg\",\"pinpinbox\\\/diy\\\/20181108\\\/5be3e5a09902e.jpg\",\"pinpinbox\\\/diy\\\/20181108\\\/5be3e5a246450.jpg\",\"pinpinbox\\\/diy\\\/20181108\\\/5be3e661ecf0f.jpg\"]",
						   "preview_page_num":5,
						   "photo":"[\"pinpinbox\\\/diy\\\/20181108\\\/5be3e59eb0174.jpg\",\"pinpinbox\\\/diy\\\/20181108\\\/5be3e59faf8ac.jpg\",\"pinpinbox\\\/diy\\\/20181108\\\/5be3e5a09902e.jpg\",\"pinpinbox\\\/diy\\\/20181108\\\/5be3e661ecf0f.jpg\",\"pinpinbox\\\/diy\\\/20181108\\\/5be3e5a246450.jpg\"]",
						   "location":"",
						   "rating":"general",
						   "reward_after_collect":true,
						   "reward_description":"1. \u8a8d\u8cfc\u4e00\u4efd\u611b\u5fc3\u9905\u4e7e\u70ba$60\u3001120P=$60 ( \u4ee5\u6b64\u985e\u63a8 )\u3002\n2. \u8acb\u7559\u4e0b\u8a73\u7d30\u8cc7\u6599\uff0c\u4ee5\u4fbf\u767c\u7968\u5bc4\u9001\u3002\n3. \u672c\u6b21\u52df\u8cc7\u9905\u4e7e\u5c07\u7d71\u4e00\u7531pinpinbox\u767c\u9001\u81f3\u53f0\u5317\u5404\u5927\u80b2\u5e7c\u9662\u3002",
						   "audio_mode":"none",
						   "audio_loop":false,
						   "audio_target":"",
						   "audio_refer":"none",
						   "point":120,
						   "act":"open",
						   "inserttime":"2018-11-08 15:28:17",
						   "publishtime":"2018-11-08 15:35:22"},
						   "albumstatistics":{
							 "count":43,"exchange":43,"likes":7,"messageboard":0,"viewed":3579},
							 "categoryarea":{"categoryarea_id":13},
							 "user":{"act":"open","name":"\u5c0f\u6109\u5152\u793e\u6703\u798f\u5229\u57fa\u91d1\u6703","picture":"https:\/\/cdn.pinpinbox.com\/storage\/zh_TW\/user\/3876\/picture$7c7d_160x160.jpg","user_id":3876}},
							 "photo":[{"name":"","description":"\u2192 \u8acb\u5411\u53f3\u7ffb\u9801\uff0c\u65bc\u6700\u672b\u9801\u652f\u6301\u5c0f\u6109\u5152 \u0295\u2022\u1d25\u2022\u0294",
							 "image":"pinpinbox\/diy\/20181108\/5be3e59eb0174.jpg",
							 "usefor":"image",
							 "hyperlink":"[{\"icon\":\"\",\"text\":\"\",\"url\":\"\"},{\"icon\":\"\",\"text\":\"\",\"url\":\"\"}]","audio_loop":0,"audio_refer":"none","audio_target":null,"video_refer":"none","video_target":""},
							{"name":"","description":"","image":"pinpinbox\/diy\/20181108\/5be3e59faf8ac.jpg","usefor":"image","hyperlink":"","audio_loop":0,"audio_refer":"none","audio_target":null,"video_refer":"none","video_target":""},
							{"name":"","description":"","image":"pinpinbox\/diy\/20181108\/5be3e5a09902e.jpg","usefor":"image","hyperlink":"","audio_loop":0,"audio_refer":"none","audio_target":null,"video_refer":"none","video_target":""},
							{"name":"","description":"","image":"pinpinbox\/diy\/20181108\/5be3e661ecf0f.jpg","usefor":"image","hyperlink":"[{\"icon\":\"\",\"text\":\"\\u9ede\\u6211\\u6d3d\\u8a62pinpinbox\\u7c89\\u7d72\\u5718\",\"url\":\"https:\\\/\\\/www.facebook.com\\\/cpinpinbox\\\/?ref=aymt_homepage_panel\"},{\"icon\":\"\",\"text\":\"\\u9ede\\u6211\\u8cfc\\u8cb7P\\u9ede\",\"url\":\"https:\\\/\\\/www.pinpinbox.com\\\/index\\\/user\\\/point\\\/\"}]","audio_loop":0,"audio_refer":"none","audio_target":null,"video_refer":"none","video_target":""},
	{"name":"","description":"\u652f\u6301\u5c0f\u6109\u5152\uff0c\u8acb\u9ede\u4e0b\u65b9\u9023\u7d50\u89c0\u770b\u66f4\u591a\u52df\u8cc7\u8a08\u5283\u3002","image":"pinpinbox\/diy\/20181108\/5be3e5a246450.jpg","usefor":"image","hyperlink":"[{\"icon\":\"\",\"text\":\"$360\\u8056\\u8a95\\u5e78\\u798f\\u79ae\\u76d2\",\"url\":\"https:\\\/\\\/www.pinpinbox.com\\\/index\\\/album\\\/content\\\/?album_id=15123&categoryarea_id=13&autoplay=1\"},{\"icon\":\"\",\"text\":\"\\u5bb6\\u5712\\u52df\\u8cc7\\u8a08\\u5283\",\"url\":\"https:\\\/\\\/www.pinpinbox.com\\\/index\\\/album\\\/content\\\/?album_id=15122&categoryarea_id=13&autoplay=1\"}]","audio_loop":0,"audio_refer":"none","audio_target":null,"video_refer":"none","video_target":""}],"releaseMode":1,"albumPhotos":5,"previewPhotos":5,"eventjoin":false,"qrcodeUrl":"https:\/\/cdn.pinpinbox.com\/storage\/zh_TW\/user\/3876\/album\/15124\/qrcode$d2f5.jpg","adjustAppQrcodeUrl":"https:\/\/cdn.pinpinbox.com\/storage\/zh_TW\/user\/3876\/album\/15124\/adjust_app_qrcode$62af.jpg"},"user":{"mobileDevice":false,"userpoint":"0","collected":false,"haslikes":0,"hasvoted":null},"property":{"login":0,"balance":-120,"favorited":false,"buyAlbumBoxContent":"<div class=\"content\"><p class=\"keypoint\" style=\"font-size:2em;\">\u8d0a\u52a9\u8cfc\u8cb7<\/p><br><p>\u8d0a\u52a9P\u9ede&nbsp;\uff1a<span class=\"red\">120P<\/span><\/p><p class=\"red\">NT: <span id=\"albumPoint2TWD\">60<\/span><\/p><\/div><br><p class=\"sub_description_title\">\u8aaa\u660e\uff1a<\/p><p class=\"description_text\">1. \u8a8d\u8cfc\u4e00\u4efd\u611b\u5fc3\u9905\u4e7e\u70ba$60\u3001120P=$60 ( \u4ee5\u6b64\u985e\u63a8 )\u3002\n2. \u8acb\u7559\u4e0b\u8a73\u7d30\u8cc7\u6599\uff0c\u4ee5\u4fbf\u767c\u7968\u5bc4\u9001\u3002\n3. \u672c\u6b21\u52df\u8cc7\u9905\u4e7e\u5c07\u7d71\u4e00\u7531pinpinbox\u767c\u9001\u81f3\u53f0\u5317\u5404\u5927\u80b2\u5e7c\u9662\u3002<\/p><\/div>","buyAlbumBoxBtn":"\u8acb\u5148\u767b\u5165","pc_button":"<li class=\"mobilehide02\" id=\"preview\">\r\n\t\t\t\t\t\r\n\t\t\t\t\t<a href=\"javascript:void(0);\" id=\"read\" onclick=\"browseKit_album('https:\/\/www.pinpinbox.com\/index\/album\/show_photo\/', {album_id: '15124', keyPress : 1})\" class=\"used big white twobtns \" style=\"margin-bottom:5px;\">\u89c0\u770b\r\n\t\t\t\t\t<\/a>\r\n\t\t\t\t\t<a href=\"javascript:void(0);\" onclick=\"buyalbum()\" id=\"trip_collect\" class=\"used big twobtns \">\r\n\t\t\t\t\t\t\u8d0a\u52a9\r\n\t\t\t\t\t<\/a>\r\n\t\t\t\t<\/li>","mobile_button":"<a href=\"javascript:void(0);\" id=\"read\" onclick=\"browseKit_album('https:\/\/www.pinpinbox.com\/index\/album\/show_photo\/', {album_id: '15124', keyPress : 1})\" class=\"used big white twobtns \">\u89c0\u770b\r\n\t\t\t\t\t<\/a>\r\n\t\t\t\t\t<a href=\"javascript:void(0);\" onclick=\"buyalbum()\" id=\"trip_collect\" class=\"used big twobtns \">\r\n\t\t\t\t\t\t\u8d0a\u52a9\r\n\t\t\t\t\t<\/a>","browseKitKeyPress":true}}}};
		
			
			
			
			
			
			if (r.result == 1) {
				for (var k0 in r.data.readable) {
					subHtml = '';

					if (r.data.readable[k0]['name']) subHtml += '<h4>' + r.data.readable[k0]['name'] + '</h4>';

					if (r.data.readable[k0]['description']) subHtml += '<p>' + r.data.readable[k0]['description'] + '</p>';
					
					if (r.data.readable[k0]['hyperlink']) {
						hyperlink = [];
						r.data.readable[k0]['hyperlink'].forEach(function(v0){
							hyperlink.push('<a target="_blank" href="' + v0.url + '">' + v0.text + '</a>');
						});
						subHtml += hyperlink.join('&emsp;');
					}

					o_audio = {loop: r.data.readable[k0].audio_loop, mode: r.data.readable[k0].audio_mode, src: r.data.readable[k0].audio_target};
                    //組抽獎影片圖示
                    slotHtml = '';
                    videoHtml = '';
                    switch (r.data.readable[k0]['usefor']) {
                        case 'slot':
                        case 'exchange':
                            slotHtml = '<img src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/assets-v6/gift-01.svg" style="display:block;position:absolute;right:2px;bottom:2px;z-index:3000;background:#FF5E68;width:20px;height:20px;border-radius:20px;">';
                            break;
                        case 'video':
                            videoHtml = '<img alt="影片" title="影片" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/assets-v6/play-01.svg"  style="display:block;position:absolute;left:2px;bottom:2px;z-index:3000;background:rgba(0,0,0,0.65);width:20px;height:20px;border-radius:20px;">';
                            break;
                    }

                    //組聲音圖示
                    audioHtml = '<img alt="聲音" title="聲音" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/assets-v6/speaker-01.svg" style="display:block;position:absolute;left:2px;bottom:2px;z-index:3000;background:rgba(0,0,0,0.65);width:20px;height:20px;border-radius:20px;">';

					switch (r.data.readable[k0]['usefor']) {
						case 'video':
							href = r.data.readable[k0]['video_target'];
							poster = r.data.readable[k0]['image'];
							thumb = r.data.readable[k0]['image_thumbnail'];
							platform = fetch_video_platform(href);
							switch (r.data.readable[k0]['video_refer']) {									
								case 'embed':
									a_album_item.push({
										audio: o_audio,
								    	href: href,
								        poster: poster,
								        src: href,
								        subHtml: subHtml,
                                        slotHtml: slotHtml,
                                        videoHtml: videoHtml,
								        thumb: thumb,
								        usefor: 'video',
								        platform: platform,
								        recommendedBuyAlbum : r.data.readable[k0]['recommendedBuyAlbum'],
								    });
									break;

								case 'file':
									if ($('#video-' + k0).length == 0) {
										$('body').append(
												'<div class="lgVideoWrapper" style="display:none;" id="video-' + k0 + '">' +
												'<video controlsList="nodownload" class="lg-video-object lg-html5" controls preload="none">' +
											    '<source src="' + href + '" type="video/mp4">Your browser does not support HTML5 video.</video>' +
											    '</div>'
										);
									} else {
                                        $('#video-' + k0).find('source').attr('src', href);
                                    }
									
									a_album_item.push({
										audio: o_audio,
								    	html: '#video-' + k0,
								        poster: poster,
								        subHtml: subHtml,
                                        slotHtml: slotHtml,
                                        videoHtml: videoHtml,
								        thumb: thumb,
								        usefor: 'video',
								        recommendedBuyAlbum : r.data.readable[k0]['recommendedBuyAlbum'],
								    });
									break;
							}
							break;

						default:
							href = r.data.readable[k0]['image'];
							thumb = r.data.readable[k0]['image_thumbnail'];
							a_album_item.push({
						        audio: o_audio,
						    	href: href,
						        src: href,
						        subHtml: subHtml,
                                slotHtml: slotHtml,
                                videoHtml: videoHtml,
						        thumb: thumb,
						        usefor: 'audio',
						        recommendedBuyAlbum : r.data.readable[k0]['recommendedBuyAlbum'],
						    });
							break;
					}
				}
				param.adjustAppQrcodeUrl = adjustAppQrcodeUrl = r.data.property.album.adjustAppQrcodeUrl;
				browseKit_v2(this, a_album_item, param);
			} else {
				_jBox(r, 'error');
			}
	//	});
	} else {
        param.adjustAppQrcodeUrl = adjustAppQrcodeUrl
		browseKit_v2(this, a_album_item, param);
	}
}

function browseKit_v2(that, item, param) {
	a_album_element.push(that);
	var indexId = 0;

    if($('#mask02').data('indexId')) {
        indexId = $('#mask02').data('indexId');
    } else if(param.preview_page_length) {
        indexId = (param.preview_page_length >= item.length ) ? 0 : param.preview_page_length ;
    }

    var	$lg = $(that).lightGallery({
		loadYoutubeThumbnail: true,
		youtubeThumbSize : 'hqdefault',
        dynamic: true,
        dynamicEl: item,
        hash: false,
		hideBarsDelay: 3000,
		loop: false,
		download: false,
		index : indexId,
		thumbWidth: 80,
        keyPress : !param.keyPress,
		youtubePlayerParams: {
	        showinfo: 0,
	        rel: 0,
		},
    }).on('onAfterOpen.lg', function(e) {
    	if(item[0].audio.src != null) {
    		audioPlayer = $('#lg-audio'); 
    		audioPlayer.attr('controlsList', 'nodownload');
    		setTimeout(function(){ 
    			audioPlayer[0].play(); 
    		}, 700); 
    	}
            //加入抽獎影片圖示
            $(".lg-thumb-item").css('position','relative');
            for(i=0;i<item.length;i++) {
                if(item[i].audio.src!=null && item[i].audio.src!=''){
                    //加入聲音圖示
                    $(".lg-thumb-item:nth-child("+(i+1)+")").append(audioHtml);
                }
                $(".lg-thumb-item:nth-child("+(i+1)+")").append(item[i].slotHtml);
                $(".lg-thumb-item:nth-child("+(i+1)+")").append(item[i].videoHtml);
            }
	}).on('onAfterSlide.lg',function(e, p, i){
	    $lg.data('lightGallery').modules.audio.build();
        audioPlayer = $('#lg-audio'); _audipPlayer = document.getElementById("lg-audio");
        audioPlayer.attr('controlsList', 'nodownload');
        switch(item[i].usefor) {
        	case 'video' : audioPlayer[0].pause(); break;
        	case 'audio' : if( _audipPlayer.hasAttribute("autoplay")) audioPlayer[0].play(); break;
        }
        if(item[i].audio.mode == 'singular') askAutoPlay = false;
       
		if(item[i].audio.src != null && askAutoPlay) { $('span#lg-audio-button').trigger('click') ; }

		audioPlayer[0].addEventListener("play", function () {
			audioPlayer.bind('contextmenu',function() { return false; }); 
			if(!_audipPlayer.hasAttribute("autoplay")) audioMaskHide();
			askAutoPlay = false;
			audioPlayer.attr('autoplay', 'autoplay');
		}, false);

		audioPlayer[0].addEventListener("pause", function (e) {	
			if(e.target.ended == false ) {
				askAutoPlay = false;
				audioPlayer.removeAttr('autoplay');
			}
		}, false);

        if (item[i].slotHtml != null && item[i].slotHtml != '') {
            var slotConfirm = new jBox('Modal', {closeButton: 'title'});
            var slotContent = '', slotTitle = '';
            slotTitle += '<p class="keypoint" style="margin-right:-20px;font-size:2em;text-align:center;line-height:1.2em;">下載ＡＰＰ即可兌換／抽獎</p>'
            slotContent += '<div class="content" style="text-align:center;" >';
            if(isMobile()){
                slotContent += `<p style="margin:0 auto;width:80%;background:#FF5E68;color:#fff;border-radius:30px;padding:8px 5px;cursor: pointer;" data-uri="pinpinbox://index/album/content/?album_id=${param.album_id}"  onclick="clickHandler(this.dataset.uri);" ><img src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/assets-v6/gift-01.svg" style="vertical-align:middle;display:inline-block;background:#FF5E68;width:30px;height:30px;border-radius:30px;margin-right:3px;">`;
                slotContent += `<span style="display:inline-block;font-size:20px;vertical-align:middle;" >立即開啟</span></p>`;
            }else{
                slotContent += `<span data-uri="pinpinbox://index/album/content/?album_id=${param.album_id}"  onclick="clickHandler(this.dataset.uri);"><img src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/assets-v6/gift-01.svg" style="position:absolute;z-index:5;top:38.8%;left:44.8%;vertical-align:middle;display:block;background:#FF5E68;width:40px;height:40px;border-radius:40px;">`;
                slotContent += `<img src="${param.adjustAppQrcodeUrl}" style="padding:3px;width:180px;height:auto;" ></span>`;
            }
            slotConfirm.setTitle(slotTitle).setContent(slotContent).open();
        }
        $('.jBox-overlay:last-child').css('background-color','rgba(255,255,255,0.3)');
		if($('#JboxRecommendedBuyAlbum').length) RecommendedBuyAlbum.destroy();

		if(item[i].recommendedBuyAlbum ) recommendedBuyAlbum();
					
			setTimeout(function() {
                if( window.DMplayerE ) { window.DMplayerE.pause(); }

                if((item[i].platform) == 'facebook') {
					$('div.lg-current .lg-video-cont').empty();
					var element = `<div
									id="fbVideo"
									class="fb-video"
									data-href="${item[i].src}"
									data-width="500"
									data-allowfullscreen="true"></div>`;
					$('div.lg-current .lg-video-cont').append(element);
					window.FB.XFBML.parse();
                } else if ((item[i].platform) == 'dailymotion') {
                    var videoStr2Array = (item[i].src).split('/'), element = `<div id="DMplayer"></div>`;
                    $('div.lg-current .lg-video-cont').empty().append(element);
                    DMplay(videoStr2Array[(((item[i].src).split('/')).length - 1)], 640, 400);
                }

			}, 200);
    }).on('onAfterAppendSubHtml.lg',function(e){
        $lg.data('lightGallery').modules.subhtml.build();
    }).on('onCloseAfter.lg', function(e){
            });

    window.$lg = $lg;
}

function browseKitRefresh() {
	a_album_item = [];
	
	$.each(a_album_element, function(k0, v0) {
		$(v0).removeData('lightGallery');
	});
}

function recommendedBuyAlbum() {
        var myConfirm = new jBox('Confirm', {
            id: 'JboxRecommendedBuyAlbum',
            cancelButton: '關閉',
            confirmButton: '請先登入',
            closeOnConfirm : false,
            closeButton : 'box',
            confirm: function () {

                var recipient = {}, validation = true;
				
                if(validation) {

                    $.post('https://www.pinpinbox.com/index/album/buyalbum/?album_id=15124&categoryarea_id=13', {
                        album_id: '15124',
                        buy: true,
                        point_to_use: $('input[name="customerpoint"]').val(),
                        recipient: recipient,
                    }, function (r) {
                        r = $.parseJSON(r);
                        switch (r.result) {
                            case 1:
                                _jBox(r, 'success');
                                $lg.data('lightGallery').destroy();

                                $('a#trip_collect , #preview_mobile').remove();
                                $('a#read').css('width', (90 / $('#preview').find('a').length) + '%').attr('onclick', 'browseKit_album(\'https://www.pinpinbox.com/index/album/show_photo/\', {album_id: \'15124\'keyPress : 1})').html('閱讀').removeClass('white');

                                if ($('a#vote').length > 0) $('a#vote, a#vote_mobile').removeClass('threebtns').addClass('twobtns');

                                $('#heart_mobile').attr('onclick', 'browseKit_album(\'https://www.pinpinbox.com/index/album/show_photo/\', {album_id: \'15124\', keyPress : 1})').html('<img src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/icon_collection_click.svg">');

                                if (r.data) {
                                    if (r.data.task.message.length > 0) _TaskAlert(r, 'task');
                                }
                                if (r.data.album_count) $('span.albumstatistics').html(r.data.album_count);
                                browseKitRefresh();
                                setTimeout(function () {
                                    browseKit_album('https://www.pinpinbox.com/index/album/show_photo/', {
                                        album_id: '15124',
                                        keyPress : 1,
                                        preview_page_length: 5});
                                }, 500);
                                myConfirm.close();
                                break;

                            case 2:
                                _jBox(r, 'success_notext');
                                myConfirm.close();
                                break;
                            case 3:
                                _jBox({message:'P點不足請先儲值'}, 'info');
                                break;

                            default:
                                _jBox(r, 'error');
                                myConfirm.close();
                                break;
                        }
                    });
                }
            },
            onCloseComplete: function () {
                myConfirm.destroy();
            }
        }).setContent(`<div class="content"><p class="keypoint" style="font-size:2em;">贊助購買</p><br><p>贊助P點&nbsp;：<span class="red">120P</span></p><p class="red">NT: <span id="albumPoint2TWD">60</span></p></div><br><p class="sub_description_title">說明：</p><p class="description_text">1. 認購一份愛心餅乾為$60、120P=$60 ( 以此類推 )。
2. 請留下詳細資料，以便發票寄送。
3. 本次募資餅乾將統一由pinpinbox發送至台北各大育幼院。</p></div>`).open();

        window.RecommendedBuyAlbum = myConfirm;
    }

    function savings_tip() {
        _jBox({'message': '贊助P點可以閱讀完整作品，P點不足可以到「管理中心」>「商店」>「購買P點'}, 'info');
    }


	
	// 20190117 #2319:作品釘一下二態變化(name與class:likes->pin_likes)區塊開始
	function likes(id) {
	    //先用這一行代替動作切換
	    $('i[name="pin_likes"]').toggleClass('pin_likes');
		
		
		//使用MARS的FUNCTION(name與class:likes->pin_likes)
        //$.post('https://www.pinpinbox.com/index/album/likes/', {
           // album_id: id,
        //}, function (r) {
          //  r = $.parseJSON(r);
          //  switch (r.result) {
           //     case 1:
           //         if (r.data) {
            //            $('i[name="pin_likes"]').addClass('pin_likes').next('span').html(parseInt($('i[name="pin_likes"]').next('span').html()) + 1);
             //           $('i[name="pin_likes"]').animate({fontSize: "1.2em"}, {
             //               queue: false,
             //              duration: 80,
             //          }).animate({fontSize: "1.5em"}, 50);

            //        } else {

            //            $('i[name="pin_likes"]').removeClass('pin_likes').next('span').html(parseInt($('i[name="pin_likes"]').next('span').html()) - 1);
            //        }
            //        break;

            //    case 2:
            //        _jBox(r, 'success_notext');
            //        break;

            //    default :
            //        _jBox(r, 'error');
            //        break;
           // }
       // });
    }

</script>

<?php include_once('layout/_footer.php') ?>
