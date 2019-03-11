
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