<?php
/* 名稱：hiAir SMS2 For PHP的Class
 * 撰寫者 : HiNet - hiAir , Chih-Ming Liao
 * 日期 : 2006/06/27
 */
class sms2{

   var $usenet_handle;    /* socket handle*/
   var $ret_code;
   var $ret_msg;
   var $send_msisdn="";
   var $send_msg_len=266; /* Socket 傳送 SendMsg 的長度為266 */
   var $ret_msg_len=244;  /* Socket 接收 RetMsg 的長度為244 */
   var $send_set_len=100;
   var $send_content_len=160;
   
   function sms2(){  }

   /* 函式說明：建立連線與認證
    * $server_ip:伺服器IP, $server_port:伺服器Port, $TimeOut:連線timeout時間
    * $user_acc:帳號, $user_pwd:密碼
    * return -1：網路連線失敗, 0：連線與認證成功, 1:連線成功，認證失敗
    */
   function create_conn($server_ip, $server_port, $TimeOut, $user_acc, $user_pwd){
      $msg_type=0;	   /* 0:檢查帳號密碼 1:傳送簡訊 2:查詢傳送結果 */

      $this->usenet_handle = fsockopen($server_ip, $server_port, $errno, $errstr, $TimeOut);
      if(!$this->usenet_handle) {
      	 $this->ret_code=-1;
      	 $this->ret_msg="Connection failed!";
      	 return $this->ret_code;
      }
      /* 帳號密碼檢查 */
      $msg_set=$user_acc . "\0" . $user_pwd . "\0";
      $in_temp = pack("C",$msg_type) . pack("C",1) . pack("C",1) . pack("C",0) . pack("C",strlen($msg_set)) . pack("C",0) . $msg_set;
      
      /*---將未滿$send_msg_len的資料填\0補滿 */
      $len_p = $this->send_msg_len - strlen($in_temp);
      $zero_buf='';
      for($i=0;$i<$len_p;$i++){
         $zero_buf = $zero_buf . "\0";
      }
      
      $in = $in_temp . $zero_buf;
      $out = '';
      $write = fwrite ($this->usenet_handle, $in);
      $out = fread ($this->usenet_handle, $this->ret_msg_len);
      /* 取出ret_code */
      $ret_C = substr($out, 0, 1);           /* 取出 ret_code */
      $ret_code_array = unpack("C", $ret_C); /* 將$ret_C 轉成unsigned char , unpack 會return array*/
      $ret_code_value = each ($ret_code_array);    /* array[1]為ret_code的值 */
      /* 取出ret_content*/
      $ret_CL = substr($out, 3, 1);          /* 取出 ret_content_len */
      $ret_cl_array = unpack("C", $ret_CL);  /* 將$ret_CL 轉成unsigned char , unpack 會return array*/
      $ret_content_len = each ($ret_cl_array); /* array[1]為ret_content_len的值 */
      $ret_content = substr($out, 84, $ret_content_len[1]); /* 取得回傳的內容*/

      $this->ret_code=$ret_code_value[1];  /* array[1]為ret_code的值 */
      $this->ret_msg=$ret_content;
      return $this->ret_code;
   }   

   /* 函式說明：傳送文字簡訊
    * $tel:接收門號, 簡訊內容
    * return ret_code
    */
   function send_text( $mobile_number, $message){   	  
   	  if(substr($mobile_number, 0, 1)== "+" ){
      	 $msg_type=15; /* 傳送國際簡訊 */
      }else{
      	 $msg_type=1; /* 傳送國內簡訊 */
      }
      	 
      $send_type="01"; /* 01 : 即時傳送*/
      $msg_set_str=$mobile_number . "\0" . $send_type . "\0";

      /*---將未滿$msg_set長度的資料填\0補滿 */
      $len_p = $this->send_set_len - strlen($msg_set_str);
      $zero_buf='';
      for($i=0;$i<$len_p;$i++){
         $zero_buf = $zero_buf . "\0";
      }
      $msg_set = $msg_set_str . $zero_buf;
   
      /*---將未滿$msg_content長度的資料填\0補滿 */
      $len_p = $this->send_content_len - strlen($message);
      $zero_buf='';
      for($i=0;$i<$len_p;$i++){
         $zero_buf = $zero_buf . "\0";
      }
      $msg_content = $message . $zero_buf;
         
      $in = pack("C",$msg_type) . pack("C",1) . pack("C",1) . pack("C",0) . pack("C",strlen($msg_set_str)) . pack("C",strlen($message)) . $msg_set . $msg_content;
      
      $write = fwrite ($this->usenet_handle, $in);
      $out = fread ($this->usenet_handle, $this->ret_msg_len);
      $ret_C = substr($out, 0, 1); /* 取出 ret_code */
      $ret_code_array = unpack("C", $ret_C); /* 將$ret_C 轉成unsigned char , unpack 會return array*/
      $ret_code_value = each ($ret_code_array); /* array[1]為ret_code的值 */
   
      $ret_CL = substr($out, 3, 1); /* 取出 ret_content_len */
      $ret_cl_array = unpack("C", $ret_CL); /* 將$ret_CL 轉成unsigned char , unpack 會return array*/
      $ret_content_len = each ($ret_cl_array); /* array[1]為ret_content_len的值 */
      $ret_content = substr($out, 84, $ret_content_len[1]); /* 取得回傳的內容*/
      
      $this->ret_code=$ret_code_value[1];  /* array[1]為ret_code的值 */
      $this->ret_msg=$ret_content;
      return $this->ret_code;
   }


   /* 函式說明：傳送WapPush簡訊
    * $tel:接收門號, 簡訊內容
    * return ret_code
    */
   function send_wappush( $mobile_number, $wap_title, $wap_url){
      $msg_type=13; /* 傳送簡訊 */
      $send_type="01"; /* 01:SI*/
      $msg_set_str=$mobile_number . "\0" . $send_type . "\0";

      /*---將未滿$msg_set長度的資料填\0補滿 */
      $len_p = $this->send_set_len - strlen($msg_set_str);
      $zero_buf='';
      for($i=0;$i<$len_p;$i++){
         $zero_buf = $zero_buf . "\0";
      }
      $msg_set = $msg_set_str . $zero_buf;
   
      /*---將未滿$msg_content長度的資料填\0補滿 */
      $msg_content_tmp = $wap_url . "\0" . $wap_title . "\0";
      $len_p = $this->send_content_len - strlen($msg_content_tmp);
      $zero_buf='';
      for($i=0;$i<$len_p;$i++){
         $zero_buf = $zero_buf . "\0";
      }
      $msg_content = $msg_content_tmp . $zero_buf;
   
      $in = pack("C",$msg_type) . pack("C",1) . pack("C",1) . pack("C",0) . pack("C",strlen($msg_set_str)) . pack("C",strlen($msg_content_tmp)) . $msg_set . $msg_content;
      
      $write = fwrite ($this->usenet_handle, $in);
      $out = fread ($this->usenet_handle, $this->ret_msg_len);
      $ret_C = substr($out, 0, 1); /* 取出 ret_code */
      $ret_code_array = unpack("C", $ret_C); /* 將$ret_C 轉成unsigned char , unpack 會return array*/
      $ret_code_value = each ($ret_code_array); /* array[1]為ret_code的值 */
   
      $ret_CL = substr($out, 3, 1); /* 取出 ret_content_len */
      $ret_cl_array = unpack("C", $ret_CL); /* 將$ret_CL 轉成unsigned char , unpack 會return array*/
      $ret_content_len = each ($ret_cl_array); /* array[1]為ret_content_len的值 */
      $ret_content = substr($out, 84, $ret_content_len[1]); /* 取得回傳的內容*/
      
      $this->ret_code=$ret_code_value[1];  /* array[1]為ret_code的值 */
      $this->ret_msg=$ret_content;
      return $this->ret_code;
   }

   /* 函式說明：查詢text發訊結果
    * $messageid:訊息ID
    * return ret_code
    */
   function query_text( $messageid){
      $msg_type=2; /* 查詢text傳送結果 */
      $msg_set=$messageid;
      $in_temp = pack("C",$msg_type) . pack("C",1) . pack("C",1) . pack("C",0) . pack("C",strlen($msg_set)) . pack("C",0) . $msg_set;
      
      /*---將未滿$send_msg_len的資料填\0補滿 */
      $len_p = $this->send_msg_len - strlen($in_temp);
      $zero_buf='';
      for($i=0;$i<$len_p;$i++){
         $zero_buf = $zero_buf . "\0";
      }
      
      $in = $in_temp . $zero_buf;
      $out = '';
      $write = fwrite ($this->usenet_handle, $in);
      $out = fread ($this->usenet_handle, $this->ret_msg_len);
      $ret_C = substr($out, 0, 1); /* 取出 ret_code */
      $ret_code_array = unpack("C", $ret_C); /* 將$ret_C 轉成unsigned char , unpack 會return array*/
      $ret_code_value = each ($ret_code_array); /* array[1]為ret_code的值 */
   
      $ret_CL = substr($out, 3, 1); /* 取出 ret_content_len */
      $ret_cl_array = unpack("C", $ret_CL); /* 將$ret_CL 轉成unsigned char , unpack 會return array*/
      $ret_content_len = each ($ret_cl_array); /* array[1]為ret_content_len的值 */
      $ret_content = substr($out, 84, $ret_content_len[1]); /* 取得回傳的內容*/
      
      $this->ret_code=$ret_code_value[1];  /* array[1]為ret_code的值 */
      $this->ret_msg=$ret_content;
      return $this->ret_code;
   }


   /* 函式說明：查詢wappush發訊結果
    * $messageid:訊息ID
    * return ret_code
    */
   function query_wappush( $messageid){
      $msg_type=14; /* 查詢wappush傳送結果 */
      $msg_set=$messageid;
      $in_temp = pack("C",$msg_type) . pack("C",1) . pack("C",1) . pack("C",0) . pack("C",strlen($msg_set)) . pack("C",0) . $msg_set;
      
      /*---將未滿$send_msg_len的資料填\0補滿 */
      $len_p = $this->send_msg_len - strlen($in_temp);
      $zero_buf='';
      for($i=0;$i<$len_p;$i++){
         $zero_buf = $zero_buf . "\0";
      }
      
      $in = $in_temp . $zero_buf;
      $out = '';
      $write = fwrite ($this->usenet_handle, $in);
      $out = fread ($this->usenet_handle, $this->ret_msg_len);
      $ret_C = substr($out, 0, 1); /* 取出 ret_code */
      $ret_code_array = unpack("C", $ret_C); /* 將$ret_C 轉成unsigned char , unpack 會return array*/
      $ret_code_value = each ($ret_code_array); /* array[1]為ret_code的值 */
   
      $ret_CL = substr($out, 3, 1); /* 取出 ret_content_len */
      $ret_cl_array = unpack("C", $ret_CL); /* 將$ret_CL 轉成unsigned char , unpack 會return array*/
      $ret_content_len = each ($ret_cl_array); /* array[1]為ret_content_len的值 */
      $ret_content = substr($out, 84, $ret_content_len[1]); /* 取得回傳的內容*/
      
      $this->ret_code=$ret_code_value[1];  /* array[1]為ret_code的值 */
      $this->ret_msg=$ret_content;
      return $this->ret_code;
   }

   /* 函式說明：接收回傳的訊息
    * return ret_code
    */
   function recv_msg(){
      $msg_type=3; /* 接收回傳的訊息 */
      $msg_set="";
      $in_temp = pack("C",$msg_type) . pack("C",1) . pack("C",1) . pack("C",0) . pack("C",strlen($msg_set)) . pack("C",0) . $msg_set;
      
      /*---將未滿$send_msg_len的資料填\0補滿 */
      $len_p = $this->send_msg_len - strlen($in_temp);
      $zero_buf='';
      for($i=0;$i<$len_p;$i++){
         $zero_buf = $zero_buf . "\0";
      }
      
      $in = $in_temp . $zero_buf;
      $out = '';
      $write = fwrite ($this->usenet_handle, $in);
      $out = fread ($this->usenet_handle, $this->ret_msg_len);
      $ret_C = substr($out, 0, 1); /* 取出 ret_code */
      $ret_code_array = unpack("C", $ret_C); /* 將$ret_C 轉成unsigned char , unpack 會return array*/
      $ret_code_value = each ($ret_code_array); /* array[1]為ret_code的值 */

      $ret_CL = substr($out, 2, 1); /* 取出 ret_set_len */
      $ret_cl_array = unpack("C", $ret_CL); /* 將$ret_CL 轉成unsigned char , unpack 會return array*/
      $ret_set_len = each ($ret_cl_array); /* array[1]為ret_set_len的值 */
      $ret_set = substr($out, 4, $ret_set_len[1]); /* 取得回傳set的內容*/
      $send_msisdn_array = split('\x0',$ret_set); /* 取得傳回者的手機門號*/

      $ret_CL = substr($out, 3, 1); /* 取出 ret_content_len */
      $ret_cl_array = unpack("C", $ret_CL); /* 將$ret_CL 轉成unsigned char , unpack 會return array*/
      $ret_content_len = each ($ret_cl_array); /* array[1]為ret_content_len的值 */
      $ret_content = substr($out, 84, $ret_content_len[1]); /* 取得回傳的內容*/
      
      $this->ret_code=$ret_code_value[1];  /* array[1]為ret_code的值 */
      $this->ret_msg=$ret_content;
      $this->send_msisdn=$send_msisdn_array[0]; /* array[0]為回傳者的門號 */
      return $this->ret_code;
   }   

   /* 回傳ret_content的值 */
   function get_ret_msg(){
      return $this->ret_msg;
   }

   /* 回傳send_tel的值 */
   function get_send_tel(){
      return $this->send_msisdn;
   }
  
   /* 關閉連線 */
   function close_conn(){
   	  if($this->usenet_handle)
         fclose ($this->usenet_handle);
   }
}