
  <!-- FOOTER區塊開始 -->
  <div id="footer_v2" class="footer_v2">
    <div id="footer_area_v2" class="footer_area_v2">
		<div class="footer_social_link">
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
		  <div class="copy_right">© 2019 pinpinbox All Rights Reserved.</div>
		</div>
		<div class="footer_service_link">
		  <div class="service_link"><a href="https://www.pinpinbox.com/index/about/">關於我們</a></div>
		  <div class="service_link"><a href="https://www.pinpinbox.com/index/index/privacy/">服務條款</a></div>
		  <div class="service_link"><a href="https://www.pinpinbox.com/index/recruit/">合作說明</a></div>
		  <div class="service_link"><a href="#">客服中心</a></div>
		</div>
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
	

  //MOBILE版搜尋切換
  $('.mobile_menu #search_m').click(function() {
	$('#mobile_search').addClass("search_box_open");
	$('#header_top').addClass("header_top_close");
  });
  
  
  // 20190624 start here, 觸發小版搜尋框
  $('#search_m').click(function() {
	$('#header_search_v2').addClass("search_box_open");
	$('#search_m').addClass("hide");
	$('#login_m').addClass("hide");
	$('#header_search_close_v2').addClass("search_close_open");
    $('#login_menu').hide();
  });
  
  // 20190624 start here, 關閉小版搜尋框
  $('#header_search_close_v2').click(function() {
	$('#header_search_v2').removeClass("search_box_open");
	$('#search_m').removeClass("hide");
	$('#login_m').removeClass("hide");
	$('#header_search_close_v2').removeClass("search_close_open");
	$('#login_menu').hide();
  });
  
  
  //20190704 新選單V2區塊開始
  //當HEADER選單被按時, 關閉推薦選單
  $('#login_m').click(function(e) {
    if(($('#recommend_menu').length ===1) && $('.recommend_menu_close').hasClass('show_block') ){
      $('.recommend_menu_close').click();
    }
  });
  $('#search_m').click(function(e) {
    if(($('#recommend_menu').length ===1) && $('.recommend_menu_close').hasClass('show_block') ){
      $('.recommend_menu_close').click();
    }
  });
  //20190704 新選單V2區塊結束  
  
  //MOBILE版搜尋關閉
  $('.header_search_close').click(function(evt) {
	$('#mobile_search').removeClass("search_box_open");
	$('#header_top').removeClass("header_top_close");
  });
  
  //MOBILE版選單切換
  // 20190314 start here, 觸發大版選單
  $('#notifier_m').click(function(e) {
    e.stopPropagation();
    $('#notifier').dropdown('toggle');
  });
  // 20190314 end here
  
  // 20190314 start here, 觸發大版選單
  $('#login_m').click(function(e) {
	e.stopPropagation();
	// 20190624 修改 
    //$('#login').dropdown('toggle');
	
	//$('#login_link').dropdown('toggle');
	$('#login_menu').toggle();
	
    // 20190624 修改 
    $('#login_m').addClass("hide");
	$('#header_search_close_v2').addClass("search_close_open");
  
  });
  // 20190314 end here
  
  
  
});


// 20190621 加上MARS FUNCTION, 讓搜尋選單切換字開始
  // MARS v2
    $('a.searchtype').on('click', function(){
		console.log('a.searchtype');
        //var device = $(this).data('device');
        $('span.search_type').html($(this).html());
        //$('input#'+device+'_searchkey').data('searchtype' , $(this).data('searchtype'));
    });
    //暫代FUNCTION
	function _search() {
	}
// 20190621 加上MARS FUNCTION, 讓搜尋選單切換字結束


// 20190115 HEADER在首頁時, 移除底線區塊開始
  if($('#banner_catbtn').length>0){  //有輪播時不要底線
    $('#header').css('border','none');
  }


// 20190621 header -> header_v2
  window.onscroll = function() {($( window ).width()> 768)? header_sticky(): false;};
  var header = document.getElementById("header_v2");
  var sticky = header.offsetTop;
  function header_sticky(){
    if (window.pageYOffset > sticky){
      //header.classList.add("header_sticky");
	  header.classList.add("header_sticky_v2");
    }else{
      //header.classList.remove("header_sticky");
	  header.classList.remove("header_sticky_v2");
    }
  }






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