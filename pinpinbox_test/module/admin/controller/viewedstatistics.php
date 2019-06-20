<?php
class viewedstatisticsController extends backstageController {
	function __construct() {}
	
	function index() {
		list($html0, $js0) = parent::$html->grid();
		list($html1, $js1) = parent::$html->browseKit(array('selector'=>'.grid-img'));
		parent::$data['index'] = $html0.$html1;
		parent::$html->set_js($js0.$js1);
		
		$categoryarea = (new categoryareaModel)->column(['categoryarea_id', 'name'])->where([[[['act', '=', 'open']], 'and']])->fetchAll();

		parent::$data['categoryarea'] = $categoryarea ;

		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}	
	
	function FetchAlbumByAll($where, $order, $value) {
		$response = [];
		$response['data'] = Model('albumstatistics2viewed')->getAlbumViewedByAll($where, $order, $value);
		return $response;
	}

	function FetchAlbumByAlbumid($where, $order, $value) {
		$response = [];
		$response['data'] = Model('albumstatistics2viewed')->getAlbumViewedByAlbumId($where, $order, $value);
		return $response;
	}

	function FetchAlbumByCategoryarea($where, $order, $value) {
		$response = [];
		$response['data'] = Model('albumstatistics2viewed')->getAlbumViewedByCategoryarea($where, $order, $value);
		return $response;
	}

	function FetchAlbumByUserid($where, $order, $value) {
		$response = [];
		$response['data'] = Model('albumstatistics2viewed')->getAlbumViewedByUserId($where, $order, $value);
		return $response;
	}

	function FetchAlbumByUserrate($where, $order, $value) {
		$response = [];
		$response['data'] = Model('albumstatistics2viewed')->getAlbumViewedByUserRate($value);
		return $response;
	}

	function json() {
		$response = array();
		$key = isset($_POST['key'])? $_POST['key'] : null;
		$value = isset($_POST['value'])? $_POST['value'] : null;
		$date = isset($_POST['date'])? $_POST['date'] : null;
		$column = [] ;

		switch ($date) {
			case 'thisday':
				$startDay = $endDay = date('Y-m-d');
				break;

			case 'thisweek':
				$dayOfWeek = date('w', time());
				if($dayOfWeek == 0) $dayOfWeek = 7;
				$dayOfWeek--;
				$startDay = date('Y-m-d', strtotime( date('Y-m-d', time())."-$dayOfWeek days"));
				$endDay = date('Y-m-d', strtotime( $startDay.'+6 days' ) );
				break;

			case 'thismonth':
				$thisYm = date('Y-m' ,time());
				$days = (in_array($thisYm, ['01', '03', '05' , '07', '08', '10', '12'])) ? '31' : '30' ;
				$startDay = $thisYm.'-01';
				$endDay = $thisYm.'-'.$days;
				break;
			
			default:
				$startDay = $date['from'];
				$endDay = $date['to'];
				break;
		}

		list($where, $group, $order, $limit) = parent::grid_request_encode();
		$where = array_merge([[[['datatime', '>=' , $startDay.' 00:00:00'], ['datatime', '<', $endDay.' 23:59:59']], 'and']], (array)$where);

		$func = 'FetchAlbumBy'.ucfirst($key);
		$response = $this->$func($where, $order, $value);
		$response['interval'] = '從 '.$startDay.' ~ 至 '.$endDay;
		die(json_encode($response));
	}

}