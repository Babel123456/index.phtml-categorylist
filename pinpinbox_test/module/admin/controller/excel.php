<?php
class excelController extends backstageController {
	function __construct() {}
	
	function index() {
		$PHPExcel = SDK('PHPExcel');
		
		/*
		 * Set properties
		 * setCreator: 作者
		 * setLastModifiedBy: 上次存檔者
		 * setTitle: 標題
		 * setSubject: 主旨
		 * setKeywords: 標記
		 * setCategory: 類別
		 * setDescription: 註解
		 */
		$PHPExcel->getProperties()->setCreator(adminModel::getSession()['name']);
		
		$SheetIndex = 0;
		
		foreach ($excel as $k0 => $v0) {
			//設定要操作的Sheet
			$PHPExcel->setActiveSheetIndex($SheetIndex);
			
			//Sheet的名稱不能含有*
			if (isset($v0['SheetTitle']) && !empty($v0['SheetTitle'])) {
				$SheetTitle = strtr($v0['SheetTitle'] , '*' , 'x');
				$PHPExcel->getActiveSheet()->setTitle($SheetTitle);
			}
			
			//儲存格內容
			foreach ($v0['SheetCell'] as $k1 => $v1) {
				foreach ($v1 as $k2 => $v2) {
					$PHPExcel->getActiveSheet()->setCellValue(toAlpha($k2).(int)($k1 + 1), $v2);
				}
			}
			
			//增加Sheet
			++$SheetIndex;
			if (count($excel) > $SheetIndex) $PHPExcel->createSheet();
		}
			
		//將指標移回第1個Sheet
		$PHPExcel->setActiveSheetIndex(0);
			
		//Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.date("Y-m-d His").' '.Session::get('tmp')['adminmenu_name_lv1'].'.xlsx"');
		header('Cache-Control: max-age=0');
			
		$objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		die;
	}
}