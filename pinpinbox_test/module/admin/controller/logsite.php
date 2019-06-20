<?php
class logsiteController extends backstageController {
	function __construct() {}
	
	function index() {
		//data
		list($jqgrid_html, $jqgrid_js) = parent::$html->jqgrid();
		list($highcharts_html, $highcharts_js) = parent::$html->highcharts();
		parent::$html->set_js($jqgrid_js.$highcharts_js);
		parent::$data['html_jqgrid'] = $jqgrid_html;
		parent::$data['html_highcharts'] = $highcharts_html;
		
		$m_logsite = Core::model('logsite')->get(sql_select_encode(array('date', 'act', 'sum(count)'), null, null, array('date', 'act')), 'fetchAll');
		$tmp2 = array();
		$x = array();
		foreach ($m_logsite as $v1) {
			$x[$v1['date']] = $v1['date'];
			$tmp2[$v1['act']][$v1['date']] = $v1['sum(count)'];
		}
		$tmp3 = array();
		$tmp4 = array();
		$tmp5 = array();
		foreach ($tmp2 as $k1 => $v1) {
			foreach ($x as $v2) {
				$tmp3[$k1][$v2] = isset($v1[$v2])? (int)$v1[$v2] : 0;
			}
			$tmp4['name'] = $k1;
			$tmp4['data'] = array_values($tmp3[$k1]);
			$tmp5[] = $tmp4;
		}
		parent::$data['series'] = json_encode($tmp5);
		parent::$data['x'] = $x;
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
	
	function form() {
		
	}
	
	function delete() {
		
	}
	
	function json() {
		die;
	}
}