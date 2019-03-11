<div class="message_board">

  <div class="message_count"> 12 則回覆</div>
  
  <!--//20190304: 加class name開始-->
  <div id="message_leave" class="message_leave">
  
    <!--//20190307: 加data-type開始-->
    <div id="pinpinboard" class="pinpinboard" data-type="pinpinboard_<?=$type?>">
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
	  
        <div class="<?=$type?>_comment_textarea_wrapper" >
		
		  <textarea id="<?=$type?>_comment_textarea" placeholder="請先登入會員"
				
				<!--//20190304: 將min-height: 38px;改成24px開始-->
                rows="4" maxlength="300" style="min-height: 24px;"
				<!--//20190304: 將min-height: 38px;改成24px結束-->
				
                onfocus="location.href='#'"></textarea>
        </div>
		
		<!--//20190304: 按鈕移至此開始, 加class name-->
	    <div class="message_leave_btn_area">
          <a href="javascript:void(0)" class="comment_text_cancel" onclick="cancelCommtent('<?=$type?>');"><span class="btn_new">清除</span></a><a href="javascript:void(0)" class="comment_text_submit" onclick="addCommtent('<?=$type?>', '2');"><span class="btn_new message_submit">送出</span></a>
        </div>
	    <!--//20190304: 按鈕移至此結束開始-->
		
      </span>
	
	<!--//20190307: 加div 與 class開始-->
	</div>
	<!--//20190307: 加div 與 class結束-->
	
	
	
  </div><!-- #message_leave -->

  <div id="message_list" class="<?=$type?>_comment_list">
    
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
            <div><span class="delete_box" onclick="delComment('<?=$type?>', 371, 2212)" aria-hidden="true">&times;</span><span class="sr-only"></span></div>
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
            <div><span class="delete_box" onclick="delComment('<?=$type?>', 371, 2212)" aria-hidden="true">&times;</span><span class="sr-only"></span></div>
        </div>
      </div>
    </div>
	
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
    var comment = $('#<?=$type?>_comment_textarea'),
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

    if ($('#<?=$type?>_comment_textarea').length>0){
        //恢復初始高度
        cancelCommtent('<?=$type?>');
        $('#<?=$type?>_comment_textarea').bind('scroll keyup', function(){
            comment_setheight('#<?=$type?>_comment_textarea');
        });
    }

    $('#<?=$type?>_comment_textarea').textcomplete([
      {
        id: 'pinpinboard',
        //match: /\B@([\u4e00-\u9fa5a-zA-Z0-9]+)$/,
		match: /\B@([\u4e00-\u9fa5a-zA-Z0-9\s]+)$/,
        search: function (term, callback) {
		  /*
          $.post('https://w3.pinpinbox.com/index/pinpinboard/mention/', {
            searchkey : term,
            text : $('#<?=$type?>_comment_textarea').val(),
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
		
        index: 1,
		
      },
	  
	// 20190307: 指定選單出現位置與指定CLASSNAME區塊開始  
	//],{onKeydown: function (e, commands){  
    //],{appendTo: 'body', className:'<?=$type?>'},{onKeydown: function (e, commands){
	],{ placement: 'top',appendTo: $('[data-type="pinpinboard_<?=$type?>"]'), className:'<?=$type?>'},{onKeydown: function (e, commands){	
	// 20190307: 指定選單出現位置與指定CLASSNAME區塊結束
	
        if(e.ctrlKey && e.keyCode === 74){ // CTRL-J
          return commands.KEY_ENTER;
        }
       }
	   
    // 20190307: 移除appendTo: 'body'區塊開始  
	//},{appendTo: 'body'}).overlay([
	//}).overlay([
	},{select: function (e){
		console.log('select');
	}
		
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
			
			$('.<?=$type?>').attr('name', '<?=$type?>_textcomplete_dropdown');
			
			//若在專區裡POPUP作品資訊頁
			if($('.popview_content').length>0){
			  console.log('.popview_content');
			  $('[name="<?=$type?>_textcomplete_dropdown"]').addClass('position_absolute');
			}else{
			  $('[name="<?=$type?>_textcomplete_dropdown"]').removeClass('position_absolute');
			}
			console.log('scroll-1');
            $('[name="<?=$type?>_textcomplete_dropdown"]').scrollTop(0);
			//$('[name="<?=$type?>_textcomplete_dropdown"]').addClass('scroll_top');
			console.log('scroll-2');
			
			
        },
        'textComplete:hide': function (e) {
        }
    });
	// 20190307: 當選單出現時, 位置調整區塊結束
	
	$('time.timeago').timeago();
});

</script>