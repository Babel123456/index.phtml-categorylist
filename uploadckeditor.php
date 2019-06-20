<?php
// 檔案的name預設是upload (我不知道去哪改耶XD 有人知道嗎?)
$uploaddir = 'upload/';
$uploadfile = $uploaddir . basename($_FILES['upload']['name']);
$fileUrl = "http://192.168.16.118/upload/" . basename($_FILES['upload']['name']);
//$CKEditorFuncNum = $_GET['CKEditorFuncNum'];
//
//  作了一些對圖片的處理後
//

// get details of the uploaded file
//$fileTmpPath = $_FILES['uploadedFile']['tmp_name'];
$fileName = $_FILES['upload']['name'];
$fileSize = $_FILES['upload']['size'];
$fileType = $_FILES['upload']['type'];
$fileNameCmps = explode(".", $fileName);
$fileExtension = strtolower(end($fileNameCmps));

move_uploaded_file($_FILES['upload']['tmp_name'], $uploadfile);
//echo '{"uploaded": 1,"fileName": "'.basename($_FILES['upload']['name']).'","url": "'.$fileUrl.'"}';
echo '{"uploaded": 1,"fileName": "'.basename($_FILES['upload']['name']).'","url": "'.$fileUrl.'"}';
// 再做一些處理後
// 如果上傳成功就是沒有錯誤訊息
/*
$errorMsg = '';
// 這邊記得回傳給ckeditor
//echo "<script>";
//echo "console.log('test');";
if($errorMsg==''){
    // CKEditor 的編號
    //$CKEditorFuncNum = isset($_GET['CKEditorFuncNum']) ? $_GET['CKEditorFuncNum'] : 2;
    $CKEditorFuncNum = $_GET['CKEditorFuncNum'];
	// $fileUrl是圖片網址 就自己先處理好吧
    $fileUrl = "http://192.168.16.118/upload/" . basename($_FILES['upload']['name']);
	//$fileUrl = $uploadfile;
	//echo "console.log('". $fileUrl ."');";
	//echo "console.log('". $CKEditorFuncNum ."');";
    //echo "window.parent.CKEDITOR.tools.callFunction(". $CKEditorFuncNum .",'" . $fileUrl . "','');";
    //echo '{"uploaded": 1,"fileName": "'.basename($_FILES['upload']['name']).'","url": "'.$fileUrl.'"}';
} else {
    //echo "console.log('".$errorMsg."');";
}
//echo "</script>";
*/

?> 