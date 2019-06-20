<?php
class cacheController extends backstageController {
	function __construct() {}
	
	function index() {
		list($jqgrid_html, $jqgrid_js) = parent::$html->jqgrid();
		parent::$data['index'] = $jqgrid_html;
		parent::$html->set_js($jqgrid_js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
	
	function form() {
	}
	
	function delete() {
		if (!empty($_POST)) {
			$key = $_POST['key'];
			$m = Core::model();
			$tmp1 = array();
			foreach (Core::$_config['CONFIG']['MC'] as $v1) {
				if (in_array($v1['SERVER'].':'.$v1['PORT'], $tmp1)) {
					continue;
				}
				$tmp1[] = $v1['SERVER'].':'.$v1['PORT'];
				
				list($class, $function) = $m->cachekey_decode($key);
				$m->cache_delete($class, array(array('class'=>$class, 'key'=>$key)));
			}
			json_encode_return(1, _('Success'));
		}
		die;
	}
	
	function json() {
		//取得條件
		$page = $_REQUEST['page'];
		$limit = $_REQUEST['rows'];
		$sidx = $_REQUEST['sidx'];
		$sord = $_REQUEST['sord'];
		$totalrows = isset($_REQUEST['totalrows'])? $_REQUEST['totalrows']: false;
		
		//組 where
		$where = null;
		if (!empty($_REQUEST['filters'])) {
			$filters = json_decode($_REQUEST['filters'], true);
			$groupOp = $filters['groupOp'];
			$rules = $filters['rules'];
			if (!empty($rules)) {
				$tmp7 = null;
				foreach ($rules as $v) {
					$field = $v['field'];
					$data = $v['data'];
					$tmp7 = $data;
				}
				$where = $tmp7;
			}
		}
		
		$m = Core::model();
		
		$tmp1 = array();
		$tmp8 = array();
		foreach (Core::$_config['CONFIG']['MC'] as $k1 => $v1) {
			if (in_array($v1['SERVER'].':'.$v1['PORT'], $tmp8)) {
				continue;
			}
			$tmp8[] = $v1['SERVER'].':'.$v1['PORT'];
			
			$m->mc($k1);
			$tmp9 = array();
			foreach ($m::$mc_instance[$k1]->get() as $v2) {
				foreach ($v2 as $v3) {
					foreach ($v3 as $k4 => $v4) {
						$tmp9[$k4] = $v4;
					}
				}
			}
			$tmp1 = array_merge($tmp1, $tmp9);
		}
		
		$tmp2 = empty($tmp1)? array() : $tmp1;
		
		//總筆數
		$count = 0;
		foreach ($tmp2 as $k1 => $v1) {
			//搜尋
			if (strpos($k1, $where) > 0 || ($where != null && strpos($k1, $where) === false)) continue;
			++$count;
		}
		
		//條件
		if ($totalrows) $limit = $totalrows;
		if (!$sidx) $sidx = 1;
		$total_pages = ($count > 0)? ceil($count / $limit) : 0;
		if ($page > $total_pages) $page = $total_pages;
		if ($limit < 0) $limit = 0;
		$start = $limit * $page - $limit;
		if ($start < 0) $start = 0;
		
		//data
		$response = array();
		$response['page'] = $page;
		$response['total'] = $total_pages;
		$response['records'] = $count;
		('asc' == $sord)? ksort($tmp2) : krsort($tmp2);
		$tmp3 = 0;
		$tmp4 = 0;
		$tmp5 = array();
		foreach ($tmp2 as $k1 => $v1) {
			//起始筆數
			++$tmp3;
			if ($start >= $tmp3) continue;
			
			//搜尋
			if (strpos($k1, $where) > 0 || ($where != null && strpos($k1, $where) === false)) continue;
			
			//處理 jqgrid 格式
			$tmp6 = array();
			$tmp6['key'] = $k1;
			//^2014-10-30 Lion: 暫時沒有看的必要, 顯示上也會有 loading
			//$tmp6['value'] = is_array($v1)? array_emit($v1) : $v1;
			$v = array();
			$v['cell'] = array_values($tmp6);
			
			//撈取筆數
			$tmp5[] = $v;
			++$tmp4;
			if ($limit == $tmp4) break;
		}
		$response['rows'] = $tmp5;
		
		die(json_encode($response));
	}
}