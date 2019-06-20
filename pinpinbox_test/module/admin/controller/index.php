<?php
class indexController extends backstageController {
	function __construct() {}
	
	function admingroup_switch() {
		if (!empty($_POST['admingroup_id'])) {
			//要檢查 $_POST['admingroup_id'] 是否屬於登入的 admin，避免駭客行為
			$m_admingroup = admingroupModel::newly()
				->join([['left join', 'admin_admingroup', 'using(admingroup_id)']])
				->where([[[['admin_admingroup.admin_id', '=', adminModel::getSession()['admin_id']]], 'and']])
				->fetchAll();
			
			if (array_multiple_search($m_admingroup, 'admingroup_id', $_POST['admingroup_id'])) {
				adminModel::newly()
					->where([[[['admin_id', '=', adminModel::getSession()['admin_id']]], 'and']])
					->edit([
							'lastloginadmingroup_id'=>$_POST['admingroup_id'],
							'modifyadmin_id'=>adminModel::getSession()['admin_id']
					]);
				
				parent::set_admin(adminModel::getSession()['admin_id']);
				json_encode_return(1);
			} else {
				json_encode_return(0);
			}
		}
		json_encode_return(0);
	}
	
	function index() {
		$startdate = date('Y-m-d', strtotime('-1 month'));
		$enddate = date('Y-m-d', strtotime('-1 day'));
		
		parent::$data['userpageview'] = Model('userpageview')->getChartDataOfPageview([$startdate, $enddate]);
		
		parent::$data['pageview_average'] = Model('userpageview')->getPageviewAverage();
		
		parent::$data['user_average'] = Model('userpageview')->getUserAverage();
		
		parent::$data['pageview_highest'] = Model('userpageview')->getPageviewHighest();
		
		parent::$data['user_highest'] = Model('userpageview')->getUserHighest();
		
		list($a_period, $a_series) = Model('userstructure')->getChartDataOfAge();
		parent::$data['userage_x'] = $a_period;
		parent::$data['userage_series'] = $a_series;
		
		parent::$data['usergender'] = Model('userstructure')->getChartDataOfGender();
		
		parent::$data['userrelationship'] = Model('userstructure')->getChartDataOfRelationship();
		
		parent::$data['age_average'] = Model('userstructure')->getAgeAverage();
		
		parent::$data['age_lowest'] = Model('userstructure')->getAgeLowest();
		
		parent::$data['age_highest'] = Model('userstructure')->getAgeHighest();
		
		parent::$html->chartKit();
		parent::$html->tabKit();
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
	
	function searchstructure() {
		if (is_ajax()) {
			$response = [];
			
			//column
			$column = [
					'searchkey',
					'SUM(`count`) `count`',
			];
			
			list($where, $group, $order, $limit) = parent::grid_request_encode();
			
			$group = array_merge(['searchkey'], (array)$group);
			
			//data
			$response['data'] = Model('searchstructure')->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
			
			//total
			$response['total'] = count(Model('searchstructure')->column(['COUNT(1)'])->where($where)->group($group)->fetchAll());
			
			die(json_encode($response));
		}
		
		parent::$data['searchtype'] = Model('searchstructure')->getChartDataOfSearchType();
		
		list($html, $js) = parent::$html->grid();
		parent::$data['html_gird'] = $html;
		parent::$html->set_js($js);
		
		parent::$html->chartKit();
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
	
	function userregistration() {
		$startdate = date('Y-m-d', strtotime('-1 month'));
		$enddate = date('Y-m-d', strtotime('-1 day'));
		
		parent::$data['usergrowth'] = Model('userregistration')->getChartDataOfGrowth([$startdate, $enddate]);
		
		parent::$data['userway'] = Model('userregistration')->getChartDataOfWay();
		
		parent::$data['totalamount'] = number_format(Model('userregistration')->column(['SUM(`count`)'])->fetchColumn());
		
		parent::$data['registration_average'] = Model('userregistration')->getRegistrationAverage();
		
		parent::$data['registration_highest'] = Model('userregistration')->getRegistrationHighest();
		
		parent::$html->chartKit();
		parent::$html->tabKit();
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
	
	function login() {
		if (!empty($_POST)) {
			$account = (isset($_POST['account']) && trim($_POST['account']) !== '')? trim($_POST['account']) : null;
			$password = (isset($_POST['password']) && trim($_POST['password']) !== '')? trim($_POST['password']) : null;
			
			list ($result, $message, $redirect) = array_decode_return(adminModel::login($account, $password));
			
			json_encode_return($result, $message, $redirect);
		}
		
		if (adminModel::getSession()) redirect(empty(query_string_parse()['redirect'])? parent::url('index', 'index', null, 'admin') : query_string_parse()['redirect']);
		
		parent::$html->set_css(static_file('css/login.css'), 'href');
		parent::$html->set_jquery();
		
		parent::$view[] = view();
	}
	
	function logout() {
		parent::logout();

		redirect(parent::url('index', 'login'));
	}
}