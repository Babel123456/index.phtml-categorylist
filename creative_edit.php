<?php include_once('layout/_header.php') ?>

  <link type="text/css" href="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" />
  <link type="text/css" href="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/intl-tel-input-master/css/intlTelInput.css" rel="stylesheet" />
  
  <link type="text/css" href="https://www.pinpinbox.com/js/jBox-0.4.8/jBox.css" rel="stylesheet" />
  <link type="text/css" href="https://www.pinpinbox.com/js/jBox-0.4.8/plugins/Confirm/jBox.Confirm.css" rel="stylesheet" />
  
  <link type="text/css" href="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/croppie/css/croppie.css" rel="stylesheet" />
  
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/datepicker/js/bootstrap-datepicker.js"></script>
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/datepicker/js/bootstrap-datepicker.zh-TW.js"></script>	
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/intl-tel-input-master/js/intlTelInput.min.js"></script>

  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/ckeditor_4.5.10_full/ckeditor.js"></script>
  
  <script type="text/javascript" src="https://www.pinpinbox.com/js/jBox-0.4.8/jBox.min.js"></script>
  <script type="text/javascript" src="https://www.pinpinbox.com/js/jBox-0.4.8/plugins/Confirm/jBox.Confirm.min.js"></script>
  
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/croppie/js/croppie.min.js"></script>
  <script type="text/javascript" src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/js/croppie/js/exif.js"></script>
  <!-- 主要內容區開始 -->
  <div id="content">
  
  <!-- 創作人內容編輯資訊區塊開始 -->
	<div class="creative_content_header">
	  <div class="creative_info_area">
        <ul class="edit_tab">
          <li><a href="#tab01"><div class="btn_new">專區</div></a></li>
          <li><a href="#tab02"><div class="btn_new">個人</div></a></li>
          <li><a href="#tab03"><div class="btn_new">關於我</div></a></li>
		  <li><a href="javascript:void(0);">帳號：www.pinpinbox.com</a></li>
        </ul>
	  </div>
	</div>
    <div class="creative_edit">
	  <!-- 個人區塊開始 -->
	  <div id="tab01" class="tab-inner">
	   <!-- 上方BANNER圖區塊開始  -->
        <div id="creative_banner">
		  <div><div><img id="CreatorCover" src="https://cdn.pinpinbox.com/upload/pinpinbox/diy/20181128/5bfdbf7f49ea0.jpg" onerror="this.src='https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/assets-v6/pre-banner.png'"></div></div>
		  <div>
		    <div><span>專區名稱</span><span>			
			     <!--
				 <textarea name="creative_name" onkeydown="return checkCols();" maxlength="22" placeholder="請輸入專區名稱"></textarea>
			     -->
				 <input type="text" name="creative_name" onkeydown="return checkCols();" maxlength="22" placeholder="請輸入專區名稱(最長22個字)">
				 </span>
		    </div>
			<div><a name="buttonSave" href="javascript:void(0)" name="uploadCreatorCover" onclick="uploadAvatar('CreatorCover');"><div class="btn_new btn_main">上傳背景圖</div></a></div>
		  </div>
		</div>
		<!-- 上方BANNER圖區塊結束  -->
		<!-- 下方社群圖區塊開始  -->
		<div id="creative_social">
		  <div>連結</div>
		  <div id="creative_social_link">
		    <div>
		      <div><span><img src="images/assets-v7/social_home.png"></span><span><input name="sociallink_web" type="text" value="https://babel-lab.github.io/RWD-Portfolio/index.htm"></span></div>
			  <div><span><img src="images/assets-v7/social_fb.png"></span><span><input type="text" name="sociallink_facebook" value="https://zh-tw.facebook.com/babel.lab/"></span></div>
			  <div><span><img src="images/assets-v7/social_youtube.png"></span><span><input name="sociallink_youtube" type="text" value=""></span></div>
			  <div><span><img src="images/assets-v7/social_g+.png"></span><span><input name="sociallink_google" type="text" value=""></span></div>
			  <div><span><img src="images/assets-v7/social_pinterest.png"></span><span><input name="sociallink_pinterest" type="text" value=""></div>
		    </div>
			<div>
		      <div><span><img src="images/assets-v7/social_ig.png"></span><span><input name="sociallink_instagram" type="text" value=""></span></div>
			  <div><span><img src="images/assets-v7/social_twitter.png"></span><span><input name="sociallink_twitter" type="text" value=""></span></div>
			  <div><span><img src="images/assets-v7/social_in.png"></span><span><input name="sociallink_linkedin" type="text" value=""></span></div>
			  <div><span><img src="images/assets-v7/social_line.png"></span><span><input name="sociallink_line" type="text" value="https://www.webtoons.com/zh-hant/challenge/%E6%96%B0%E6%A0%A1%E5%9C%92%E6%80%AA%E8%AB%87/list?title_no=6164&amp;page=3"></span></div>
			  <div><span><img src="images/assets-v7/social_blogger.png"></span><span><input name="sociallink_blog" type="text" value="http://babel-lab.blogspot.com/"></div>
		    </div>
		  </div>
		  <div><a href="javascript:void(0)" name="buttonSave" onclick="save();"><div class="btn_new btn_main">編輯完成</div></a></div>
		</div>
		<!-- 下方社群圖區塊結束  -->
      </div>
	  <!-- 專區區塊開始 -->
      <div id="tab02" class="tab-inner">
	     <div id="creative_info">
         <div id="creative_head_info">
		   <div id="crea_face">
             <img src="https://cdn.pinpinbox.com/static_file/pinpinbox/zh_TW/images/face_sample.png" >
		   </div>
		   <div><div class="btn_new" onclick="uploadAvatar('avatar');">上傳頭像</div></div>
		 </div>
		 <div id="creative_info_edit"> 
		   <div><span>暱稱</span><span><input type="text" name="username" placeholder="請輸入你的暱稱" ></span></div>
		   <div class="creative_info_email"><span>信箱</span><span class="sub_desc">不同於帳號</span><span><input type="text" name="user_email"></span></div>
		   <div class="creative_short"><span>個人短網址</span><span><span>www.pinpinbox.com/</span>
		        <span><input type="text" maxlength="32" placeholder="個人帳號(英文/數字)" name="user_creative_code" ></span></span></div>
		   <div class="flex_column">
		     <div><span>生日</span>
		       <span class="input-daterange"><input type="text" name="user_birthday" value="1900-01-01" maxlength="10" autocomplete="off"></span>
		     </div>
		     <!--  姓別區塊開始  -->
		     <div><span>性別</span>
			      <!-- //20181211: 修改下拉選單開始 -->
			      <span class="custom-select">
				    <select id="user_gender">
                      <option value="none">不透露</option>
                      <option value="female">女性</option>
                      <option value="male">男性</option>
                    </select>
			      </span>
				  <!-- //20181211: 修改下拉選單結束 -->
		     </div>
		   <!--  姓別區塊結束  -->
		   <!--  婚姻區塊開始  -->
		   <div><span>婚姻</span>
		        <!-- //20181211: 修改下拉選單開始 -->
			    <span class="custom-select">
				  <select id="user_relationship">
                    <option value="none">不透露</option>
                    <option value="single">未婚</option>
                    <option value="married">已婚</option>
                  </select>
			    </span>
				<!-- //20181211: 修改下拉選單結束 -->
		   </div>
		   <!--  婚姻區塊結束  -->
		   </div>
		   <!--  居住地區塊開始  -->
		   <div class="flex_column">
		     <div><span>居住地</span>
			      <!-- //20181211: 修改下拉選單開始 -->
			      <span class="custom-select">
				    <!-- //20181225: 修改下拉選單ONCHANGE開始 -->
				    <select id="user_address_id_1st" onchange="addr2_list();">
					<!-- //20181225: 修改下拉選單ONCHANGE開始 -->
                      <option value="0">請選擇</option>
                      <option value="1">台灣地區</option>
                      <option value="2">港澳地區</option>
					  <option value="3">中國地區</option>
					  <option value="4">亞洲地區</option>
					  <option value="5">歐洲地區</option>
					  <option value="6">非洲地區</option>
					  <option value="7">北美洲地區</option>
					  <option value="8">中南美地區</option>
					  <option value="9">大洋洲地區</option>
                    </select>
			      </span>
				  <!-- //20181211: 修改下拉選單結束 -->
			 </div>
		     <div><!-- 排版用區塊 --><span></span><!-- 排版用區塊 -->
			      <!-- //20181211: 修改下拉選單開始 -->
			      <span class="custom-select">
				    <select id="user_address_id_2nd">
					  <option value="0">請選擇</option>
                      <option value="10">台北市</option>
                      <option value="11">新北市</option>
                      <option value="12">基隆市</option>
					  <option value="13">桃園市</option>
                    </select>
			      </span>
				  <!-- //20181211: 修改下拉選單結束 -->
			 </div>
			 <!-- 排版用區塊 --><div></div><!-- 排版用區塊 -->
		   </div>
		   <!--  居住地區塊結束  -->
		   <!--  興趣區塊開始  -->
		   <div class="flex_column">
		     <div><span>興趣</span>
			      <!-- //20181211: 修改下拉選單開始 -->
			      <span class="custom-select">
				    <!-- //20181225: 修改下拉選單ONCHANGE開始 -->
				    <select id="hobby_0" onchange="hobby123_list();">
					<!-- //20181225: 修改下拉選單ONCHANGE開始 -->
					  <option value="0">請選擇</option>
                      <option value="1">四處旅遊</option>
                      <option value="2">就愛攝影</option>
                      <option value="3">美食享受</option>
					  <option value="4">養身保健</option>
					  <option value="8">時尚潮流</option>
                      <option value="9">開運占術</option>
                      <option value="10">社交技巧</option>
                      <option value="12">心靈成長</option>
					  <option value="14">藝術創作</option>
					  <option value="17">生活美學</option>
                      <option value="18">文學欣賞</option>
                      <option value="19">語言學習</option>
                      <option value="20">音樂創作</option>
					  <option value="21">藝文娛樂</option>
					  <option value="22">公益活動</option>
                    </select>
			      </span>
				  <!-- //20181211: 修改下拉選單結束 -->
		     </div>
		     <div><!-- 排版用區塊 --><span></span><!-- 排版用區塊 -->
			      <!-- //20181211: 修改下拉選單開始 -->
			      <span class="custom-select" onchange="hobby123_list();">
				    <!-- //20181225: 修改下拉選單ONCHANGE開始 -->
				    <select id="hobby_1" onchange="hobby123_list();">
					<!-- //20181225: 修改下拉選單ONCHANGE開始 -->
					  <option value="0">請選擇</option>
                      <option value="1">四處旅遊</option>
                      <option value="2">就愛攝影</option>
                      <option value="3">美食享受</option>
					  <option value="4">養身保健</option>
					  <option value="8">時尚潮流</option>
                      <option value="9">開運占術</option>
                      <option value="10">社交技巧</option>
                      <option value="12">心靈成長</option>
					  <option value="14">藝術創作</option>
					  <option value="17">生活美學</option>
                      <option value="18">文學欣賞</option>
                      <option value="19">語言學習</option>
                      <option value="20">音樂創作</option>
					  <option value="21">藝文娛樂</option>
					  <option value="22">公益活動</option>
                    </select>
			      </span>
				  <!-- //20181211: 修改下拉選單結束 -->
			 </div>
			 <div><span></span>
			     <!-- //20181211: 修改下拉選單開始 -->
			      <span class="custom-select">
				    <!-- //20181225: 修改下拉選單ONCHANGE開始 -->
				    <select id="hobby_2" onchange="hobby123_list();">
					<!-- //20181225: 修改下拉選單ONCHANGE開始 -->
					  <option value="0">請選擇</option>
                      <option value="1">四處旅遊</option>
                      <option value="2">就愛攝影</option>
                      <option value="3">美食享受</option>
					  <option value="4">養身保健</option>
					  <option value="8">時尚潮流</option>
                      <option value="9">開運占術</option>
                      <option value="10">社交技巧</option>
                      <option value="12">心靈成長</option>
					  <option value="14">藝術創作</option>
					  <option value="17">生活美學</option>
                      <option value="18">文學欣賞</option>
                      <option value="19">語言學習</option>
                      <option value="20">音樂創作</option>
					  <option value="21">藝文娛樂</option>
					  <option value="22">公益活動</option>
                    </select>
			      </span>
				  <!-- //20181211: 修改下拉選單結束 -->
		     </div>
		   </div>
		   <!--  興趣區塊結束  -->
		   
		 </div><!-- creative_info_edit -->
		 <div id="epaper_option">
		   <div><div>留言板</div><div><span>
		   <!-- //20190129: radio加id, 加label標籤開始 -->
		   <input type="radio" id="discuss_01" name="discuss" value="open" checked="true">
		   <label for="discuss_01">開啟</label>
		   <!-- //20190129: radio加id, 加label標籤結束 -->
		   </span><span>
		   <!-- //20190129: radio加id, 加label標籤開始 -->
		   <input type="radio" id="discuss_02"  name="discuss" value="close">
		   <label for="discuss_02">關閉</label>
		   <!-- //20190129: radio加id, 加label標籤結束 -->
		   </span></div></div>
		   <div><div>訂閱電子報</div><div><span>
		   <!-- //20190129: radio加id, 加label標籤開始 -->
		   <input type="radio" id="newsletter_01" name="newsletter" value="open" checked="true">
		   <label for="newsletter_01">開啟</label>
		   <!-- //20190129: radio加id, 加label標籤結束 -->
		   </span><span>
		   <!-- //20190129: radio加id, 加label標籤開始 -->
		   <input type="radio" id="newsletter_02" name="newsletter" value="close">
		   <label for="newsletter_02">關閉</label>
		   <!-- //20190129: radio加id, 加label標籤結束 -->
		   </span></div></div>
		 </div>
		 <div class="btn_edit_complete"><div class="btn_new btn_main" >編輯完成</div></div>
		 <!-- 變更手機號碼區塊開始 -->
		 <div id="creative_phone">
		   <div><span>變更手機號碼</span><span class="sub_desc">當前號碼 : ********8777</span></div>
		   <div><span>要變更為</span>
		        <span>
		          <span><input type="text" id="mobile-number" placeholder="手機號碼"></span>
			      <span><a href="javascript:void(0)" class="used" onclick="send_smspwd()"><div class="btn_new btn_main">寄送驗證碼</div></a></span>
		        </span>
		   </div>
		   <div><span>輸入驗證碼</span><span><input type="text" name="smspassword" placeholder="請輸入收到的驗證碼"></span><span><div class="btn_new btn_main">送出</div></span></div>
		 </div>
		 <!-- 變更手機號碼區塊結束 -->
		 <!-- 變更密碼號碼區塊開始 -->
		 <form id="js_user_password_form" novalidate="novalidate">
		   <div id="creative_password">
		     <div><span>變更密碼</span></div>
		     <div><span>當前密碼</span>
		          <span><input type="password" name="old_pass" placeholder="輸入原密碼"></span>
		     </div>
		     <div><span>新密碼</span><span class="sub_desc">最少八位英數字</span>
		          <span><input type="password" name="new_pass" placeholder="輸入新密碼"></span>
		     </div>
		     <div><span>再確認一次新密碼</span>
			      <span>
		            <span><input type="password" name="new_pass_check" placeholder="再次輸入新密碼"></span>
			        <span><a href="javascript:void(0)" onclick="$('#js_user_password').trigger('click')"><div class="btn_new btn_main">送出</div></a></span>
		          </span>
			 </div>
			 <input style="display:none;" id="js_user_password" type="submit" value="">
		   </div>
		 </form>
		 <!-- 變更密碼號碼區塊結束 -->
		 </div><!-- #creative_info -->
	  </div>
	  <!-- 專區區塊結束 -->
	  <!-- 關於我區塊開始 -->
      <div id="tab03" class="tab-inner">
	  
	    <form method="post" class="cmxform" id="userDescriptionForm" action="#">

          <div id="creative_aboutme">
		    <div>關於我</div>
		    <div>
			  <!--<textarea id="aboutCreator" name="aboutCreator"><p>我的老婆是米蟲, 輕鬆生活四格小品~</p></textarea>-->
			  <textarea id="user_description" onkeydown="return checkCols();" name="user_description"><p>我的老婆是米蟲, 輕鬆生活四格小品!!!</p></textarea>
		    </div>
		    <div><a href="javascript:void(0)" onclick="$('#userDescriptionFormSubmit').trigger('click');" ><div class="btn_new btn_main">編輯完成</div></a>
		    
            <input type="submit" id="userDescriptionFormSubmit" style="display: none;" onclick="userDescriptionFormUpdate();return false;">		    </div>
		  </div>
		  
		</form>
	  </div>
	  <!-- 關於我區塊結束 -->
	<!-- 創作人內容編輯資訊區塊結束 -->
    </div>
	<div id="btn_back_creative_area" class="btn_back_creative_area">
      <a href="creative_content.php"><div class="btn_new btn_back_creative">返回創作人專區</div></a>
	</div>
  </div>
  <!-- 主要內容區結束 -->
<script>
/*
function test_select(elementId){
  var e = document.getElementById('hobby_2').value;
  var value = e.options[e.selectedIndex].value;
  alert(value);
}
*/


  //專區編輯資訊開始
  //$(function(){
	//專區編輯表頭tab切換
    if($('.edit_tab').length>0){
      var $li = $('ul.edit_tab li:nth-child(-n+3)');
      $($li.eq(0).addClass('active').find('a').attr('href')).siblings('.tab-inner').hide();
	  //20181228: 小版不出現關於我在專區小方開始
	  if($( window ).width()< 800){
		$('#tab03[class="tab-inner"]').hide();
	  }else{
	    $('#tab03[class="tab-inner"]').show();
	  }
	  //20181228: 小版不出現關於我在專區小方結束
      $li.click(function(){
        $($(this).find('a').attr('href')).show().siblings('.tab-inner').hide();
        $(this).addClass('active').siblings('.active').removeClass('active');
		//當大版時, 標籤2與3一起顯示
	    if($( window ).width()> 801){
		  if($(this).find('a').attr('href')=='#tab01'){
		    $('#tab03[class="tab-inner"]').show();
		  }
	    }
		
      });
    }
  //});
  
  //專區編輯資訊結束
  $(document).ready(function() {
    //選取生日日期
    $('.input-daterange').datepicker({	
	  startDate: "-80y",
	  endDate: "",
	  format: "yyyy-mm-dd",
	  clearBtn: true,
	  startView: "decade",
	  language: "zh-TW",
	  todayHighlight: true,
    });

    //電話國碼
    $("#mobile-number").intlTelInput({defaultCountry: 'auto'});//電話國碼
	
  });
  
  //20181211: 移除開始
  /*
    
	//顯示下拉選單
	function menudrop_down(e){
	  $('.'+e+'_menudrop').toggleClass("show_block");
	}
	//關閉下拉選單
	function menudrop_up(e,parent_class){
	  if(e=='close'){
	    $('[class*=_menudrop]').removeClass("show_block");
	  }else{
        if(!e){			   
	      var str = parent_class;
          e = str.split(" ", 1);
	    }
		$(':not([class^="'+e+'"])').removeClass("show_block"); 
	  }
	}
	//下拉選單傳值
	function menudrop_set(e,v,vn){
	  $("#"+e).val(vn);
	  $("#"+e).data(e,v);
	  //若是居住地
	  if(e=='user_address_id_1st'){
		  
	    var str='<li><a href="javascript:void(0)" data-user_address_id_2nd="0" onclick="menudrop_set(\'user_address_id_2nd\',\'0\',\'--請選擇--\');">--請選擇--</a></li>';
		
		if(v=="1"){ //台灣地區, 組城市字串
		  
		 str+='<li><a href="javascript:void(0)" data-user_address_id_2nd="10" onclick="menudrop_set(\'user_address_id_2nd\',\'10\',\'台北市\');">台北市</a></li>';
		 str+='<li><a href="javascript:void(0)" data-user_address_id_2nd="11" onclick="menudrop_set(\'user_address_id_2nd\',\'11\',\'新北市\');">新北市</a></li>';
		 str+='<li><a href="javascript:void(0)" data-user_address_id_2nd="12" onclick="menudrop_set(\'user_address_id_2nd\',\'12\',\'新北市\');">基隆市</a></li>';
		 str+='<li><a href="javascript:void(0)" data-user_address_id_2nd="13" onclick="menudrop_set(\'user_address_id_2nd\',\'13\',\'桃園市\');">桃園市</a></li>';
		 		
		}
		$('.user_address_id_2nd_menudrop').html(str);
	  }
	}
	
	//當興趣1改變, 2與3的選單改變, 不重覆興趣選項
	function check_hoppy(text_id,text_value){
	
	
	
	}
	*/
	//20181211: 移除結束
	
	
	
	//20181211: 移除開始
	/*
	//按選單之外關閉選單
	$('body').click(function(evt) {
	  //menudrop_up(event.target.id,(!event.target.parentNode.className)? '':event.target.parentNode.className);
	  menudrop_up(event.target.id,(event.target.parentNode.className=='undefined' || event.target.parentNode.className== null)? '':event.target.parentNode.className);
      //menudrop_up(event.target.id,(event.target.parentNode.className[0].length>0)? event.target.parentNode.className:'');
	  
	});
	*/
	//20181211: 移除結束
	
	
	
    //當WIDNWOS RESIZE時, 關閉選單
    $( window ).resize(function(evt) {
		
      //20181211: 移除開始
	  /*
	  menudrop_up('close','');
	  */
	  //20181211: 移除結束
	  
	  
	  //若小版的關於我是打開的
	  if($( window ).width()> 801){
	    if($('ul.edit_tab li:nth-child(3)[class="active"]').length>0){
	      $li.eq(1).click();
	    }
	  }else{
	    if($('ul.edit_tab li:nth-child(2)[class="active"]').length>0){
		  $li.eq(1).click();
		}
	  }
    });
	
	//關於我編輯區塊
	$(document).ready(function () {
		if ($('#user_description').length > 0) { 
          CKEDITOR.replace('user_description', {
            //filebrowserUploadUrl: 'https://www.pinpinbox.com/index/upload/ckeditor/?class=creative',
            //filebrowserImageUploadUrl: 'https://www.pinpinbox.com/index/upload/ckeditor/?class=creative',
            //filebrowserFlashUploadUrl: 'https://www.pinpinbox.com/index/upload/ckeditor/?class=creative',
			filebrowserUploadUrl: 'http://192.168.16.118/uploadckeditor.php',
            filebrowserImageUploadUrl: 'http://192.168.16.118/uploadckeditor.php',
            filebrowserFlashUploadUrl: 'http://192.168.16.118/uploadckeditor.php',
            toolbar: 'Full',
            width: '100%',
            height: '400px'
          });
		}
    });
	//上傳圖片(基本上只改一點樣式, 有些無法調)
	function uploadAvatar(uploadType) {
		/*
        var uploadAvatarHtml = `<div class="demo-wrap upload-demo">
						        <div class="container">
						            <div class="grid">
						                <div class="col-1-2" style="display:none;">
						                    <div class="actions">
						                        <a class="btn file-btn">
						                            <input type="file" id="uploadCroppie" value="Choose a file" accept="image/*" />
						                        </a>
						                        <button class="upload-result">Result</button>
						                    </div>
						                </div>
						                <div class="col-1-2">
						                    <div class="upload-msg"></div>
						                    <div class="upload-demo-wrap">
						                        <div id="upload-demo"></div>
						                    </div>
						                </div>
						            </div>
						        </div>
						    </div><hr>
						    <div class="uploadCroppieBtnGroup">
						    	<a class="uploadCroppieBtn" id="reUploadCroppiie" href="javascript:void(0)">` + "上傳檔案" + `</a>
						    	<a class="uploadCroppieBtn uploadCroppieSave" id="saveCroppiie" href="javascript:void(0)">` + "編輯完成" + `</a>
						    </div>`;
        */
		//加入DIV與BTN樣式
		var uploadAvatarHtml = 
		`<div id="creative_head_img_upload">
		   <div class="demo-wrap upload-demo">
			  <div class="container">
				 <div class="grid">

		             <div class="col-1-2" style="display:none;">
		                 <div class="actions">
		                     <a class="btn file-btn">
		                         <input type="file" id="uploadCroppie" value="Choose a file" accept="image/*" />
		                     </a>
		                     <button class="upload-result">Result</button>
		                 </div>
		             </div>
		             <div class="col-1-2">
		                 <div class="upload-msg"></div>
		                 <div class="upload-demo-wrap">
		                     <div id="upload-demo"></div>
		                 </div>
		             </div>
		         </div>
		     </div>
		   </div>
		 <div class="uploadCroppieBtnGroup">
		 	<a class="uploadCroppieBtn" id="reUploadCroppiie" href="javascript:void(0)"><div class="btn_new">` + "上傳檔案" + `</div></a>
		 	<a class="uploadCroppieBtn uploadCroppieSave" id="saveCroppiie" href="javascript:void(0)"><div class="btn_new btn_main">` + "編輯完成" + `</div></a>
		 </div>
		 </div>`;
		if (uploadType == 'avatar') {
            jBoxtitle = '變更個人頭像';
            jBoxWidth = 380;
            jBoxHeight = 450; //改短一點
        } else {
            jBoxtitle = '專區背景圖片';
            jBoxWidth = 1000;
            jBoxHeight = 560; //改短一點
        }

        uploadView = new jBox('Modal', {
            width: jBoxWidth,
            height: jBoxHeight,
            title: jBoxtitle,
            closeButton: 'title',
            content: uploadAvatarHtml,
            id: 'uploadViewJbox',
            onOpen: function () {
                CroppieUpload(uploadType);
                $('.container').css('width', '100%');
                if (uploadType == 'avatar') {
                    $('.croppie-container').css('width', '36%');
                    $('.cr-slider-wrap').css('width', '270%');
                }

                $('#uploadCroppie').trigger('click');

                $('#saveCroppiie').on('click', function () {
                    $('button.upload-result').trigger('click');
                });

                $('#reUploadCroppiie').on('click', function () {
                    $('#uploadCroppie').trigger('click');
                })
            },
            onCloseComplete: function () {
                this.destroy();
            }
        }).open();
    };

	//
	 function CroppieUpload(uploadType) {
        var $uploadCrop;

        if (uploadType == 'avatar') {
            croppieViewportWidth = croppieViewportHeight = 200;
            croppieBoundaryWidth = croppieBoundaryHeight = 300;
            resultSize = 'viewport';
        } else {
            croppieViewportWidth = 480;
            croppieViewportHeight = 225;
            croppieBoundaryWidth = '100%';
            croppieBoundaryHeight = 400;
            resultSize = {'width': 960, 'height': 450,};
        }

        function readFile(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('.upload-demo').addClass('ready');
                    $uploadCrop.croppie('bind', {
                        url: e.target.result
                    }).then(function () {
                        console.log('jQuery bind complete');
                    });
                }

                reader.readAsDataURL(input.files[0]);
            }
            else {
                console.log("Sorry - you're browser doesn't support the FileReader API");
            }
        }

        $uploadCrop = $('#upload-demo').croppie({
            enableExif: true,
            viewport: {
                width: croppieViewportWidth,
                height: croppieViewportHeight,
                type: 'square'//2018-05-18 Lion: 以 circle 裁切 jpg 會有黑邊,
            },
            boundary: {
                width: croppieBoundaryWidth,
                height: croppieBoundaryHeight,
            },
        });

        $('#uploadCroppie').on('change', function () {
            readFile(this);
        });

        $('.upload-result').on('click', function (ev) {
            $uploadCrop.croppie('result', {
                format: 'jpeg',
                size: resultSize,
                type: 'canvas'
            }).then(function (resp) {
                $.post('https://www.pinpinbox.com/index/creative/croppie/', {
                    data: resp,
                    uploadType: uploadType,
                }, function (r) {
                    r = $.parseJSON(r);
                    switch (r.result) {
                        case 1:
                            if (uploadType == 'avatar') {
                                $('#crea_face>img, #member_open>img').attr('src', resp);
                            } else {
                                $('img#CreatorCover').attr('src', resp);
                            }

                            break;

                        default :
                            site_jBox(r, 'error');
                            break;
                    }
                });
                uploadView.close();
            });

        });
    }

	
	//20181211: 移除開始
	/*
	//20181126: 做為下拉選單的輸入方框不做反應開始
	$(function() {
      $('input[readonly]').on('touchstart', function(ev) {
        return false;
      });
    });
	//20181126: 做為下拉選單的輸入方框不做反應結束
	*/
	//20181211: 移除結束
	
	
	
	

	//20181211: 重製作下拉選單開始
	$(document).ready(function() {
      custom_select();
    });

	//20190121: 刪除FUCNTION, 移至_FOOTER.PHP裡開始
//20181225: 修改區塊開始
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
		//只給興趣的仿下拉選單項目ID值同SELECT下拉選單值
		if(selElmnt.id == 'hobby_0' || selElmnt.id == 'hobby_1' || selElmnt.id =='hobby_2'){
		  if(j>0){
		    c.setAttribute("id", selElmnt.id+"_"+selElmnt.options[j].value); //第一筆請選擇不理會它
		  }
		}
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
  
  //特殊處理部份  
  addr2_list();	//當頁面載入時先關閉城市選單
}//custom_select()
//20181225: 修改區塊結束

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


  //20181211: 重製作下拉選單結束
//20190121: 刪除FUCNTION, 移至_FOOTER.PHP裡結束
		
  //20181225: 下拉清單特殊處理FUNCTION區塊開始
  //依居住地顯示城市選項清單
  function addr2_list(){
	if($('#user_address_id_1st').prop('selectedIndex')=='1'){ //台灣
	  $('#user_address_id_2nd ~ .select-items > div').css('display','block');
	  $('#user_address_id_2nd ~ .select-items > div:first-child').css('margin-bottom','16px');
	}else{
	  $('#user_address_id_2nd').prop('selectedIndex',0); //城市歸0;
	  $('#user_address_id_2nd ~ .select-selected').html('請選擇');
	  $('#user_address_id_2nd ~ .select-items > div:not(:first-child)').css('display','none');
	  $('#user_address_id_2nd ~ .select-items > div:first-child').css('margin-bottom','0');
	}
  }	
  //三個興選項值不重覆
  function hobby123_list(){
	var hobby_0 = $('#hobby_0').val(), 
	    hobby_1 = $('#hobby_1').val(), 
		hobby_2 = $('#hobby_2').val();
	$('[id^="hobby_"] ~ .select-items > div:not(:first-child)').css('display','block'); //興趣選項先全顯示
	//關閉其他2個興趣的相同選項
	$('#hobby_1 ~ .select-items #hobby_1_'+hobby_0).css('display','none'); //與興趣1同的選項不顯示
	$('#hobby_2 ~ .select-items #hobby_2_'+hobby_0).css('display','none'); //與興趣1同的選項不顯示
	$('#hobby_0 ~ .select-items #hobby_0_'+hobby_1).css('display','none'); //與興趣2同的選項不顯示
	$('#hobby_2 ~ .select-items #hobby_2_'+hobby_1).css('display','none'); //與興趣2同的選項不顯示
	$('#hobby_0 ~ .select-items #hobby_0_'+hobby_2).css('display','none'); //與興趣3同的選項不顯示
	$('#hobby_1 ~ .select-items #hobby_1_'+hobby_2).css('display','none'); //與興趣3同的選項不顯示
  }   
  //20181225: 下拉清單特殊處理FUNCTION區塊結束
	
  //20190129: 此頁移除FOOTER, 讓"返回創作人專區"常駐在下方開始
  
    $(document).ready(function() {
      $('body #footer').addClass('hide');
    });
      
  //20190129: 此頁移除FOOTER, 讓"返回創作人專區"常駐在下方結束
  
  
  
  
  //20190311: 加入關於我FUNCTION
  // 更新關於我內容
  function userDescriptionFormUpdate() {
	
	console.log(CKEDITOR.instances['user_description'].getData());
	
	
	/*
    $("#userDescriptionForm").validate({
      rules: {},
      messages : {},
      submitHandler : function(form) {
          $.post('https://www.pinpinbox.com/index/creative/updateDescription/', {
              user_description: CKEDITOR.instances['user_description'].getData(),
          }, function (r) {
              r = $.parseJSON(r);
              switch (r.result) {
                  case 1:
                      _jBox(r, 'success');
                      break;

                  default :
                      _jBox(r, 'error');
                      break;
              }
          });
      },
      success: function(error){},
    });
	*/
  }


  
  
  
</script>

<?php include_once('layout/_footer.php') ?>
