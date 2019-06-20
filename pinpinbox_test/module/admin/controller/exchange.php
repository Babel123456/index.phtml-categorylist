<?php
class exchangeController extends backstageController {
	function __construct() {}
	
	function index() {
		list($html, $js) = parent::$html->grid();
		parent::$data['index'] = $html;
		parent::$html->set_js($js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
	
	function form() {
		if (is_ajax()) {
			switch ($_GET['act']) {
				//新增
				case 'add':
					break;
				
				//修改
				case 'edit':
					break;
			}
		}
		
		//初始值-form
		
		//form
		$column = array();
		$extra = null;
		
		//form for add or edit
		switch ($_GET['act']) {
			//新增
			case 'add':
				break;
				
			//修改
			case 'edit':
				if (!empty($_GET)) {
					$M_CLASS_id = $_GET[M_CLASS.'_id'];
					
					$m_exchange = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();
					
					//user
					$html_user = parent::get_grid_display('user', $m_exchange['user_id']);
					
					//settlement
					$html_settlement = parent::get_grid_display('settlement', $m_exchange['settlement_id']);
					
					$platform = $m_exchange['platform'];
					$type = $m_exchange['type'];
					$id = $m_exchange['id'];
					$point_before = $m_exchange['point_before'];
					$point = $m_exchange['point'];
					$inserttime = $m_exchange['inserttime'];
					$modifytime = $m_exchange['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_exchange['modifyadmin_id'])['name'];
				}
				break;
		}
		
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('user'), 'value'=>$html_user);
		
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('settlement'), 'value'=>$html_settlement);
		
		$column[] = array('key'=>_('Platform'), 'value'=>$platform);
		
		$column[] = array('key'=>_('Type'), 'value'=>$type);
		
		$column[] = array('key'=>_('ID'), 'value'=>$id, 'key_remark'=>'Type 所屬的 ID');
		
		$column[] = array('key'=>_('Point Before'), 'value'=>$point_before, 'key_remark'=>'兌換前 User 持有的 Point');
		
		$column[] = array('key'=>_('Point'), 'value'=>$point);
		
		$column[] = array('key'=>_('Insert Time'), 'value'=>$inserttime);
		
		$column[] = array('key'=>_('Modify Time'), 'value'=>$modifytime);
		
		$column[] = array('key'=>_('Modify Admin Name'), 'value'=>$modifyadmin_name);
		
		list($html, $js) = parent::$html->back('value="'._('Back').'"');
		$column[] = array('key'=>'&nbsp;', 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->table('class="table"', $column, $extra);
		$a_tabs[0] = array('href'=>'#tabs-0', 'name'=>_('Form'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->tabs($a_tabs);
		$formcontent = $html;
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->form('id="form"', $formcontent);
		parent::$data['form'] = $html;
		parent::$html->set_js($js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
	
	function delete() {
		die;
	}
	
	function json() {
		$response = array();
		
		$case = isset($_POST['case'])? $_POST['case'] : null;
		
		switch ($case) {
			default:
				//column
				$column = array(
						M_CLASS.'_id',
						'user_id',
						'settlement_id',
						'platform',
						'type',
						'id',
						'point_before',
						'point',
						'modifytime',
				);
				
				list($where, $group, $order, $limit) = parent::grid_request_encode();
				
				//data
				$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
				foreach ($fetchAll as &$v0) {
					$v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
					$v0['settlementX'] = parent::get_grid_display('settlement', $v0['settlement_id']);
				}
				$response['data'] = $fetchAll;
				
				//total
				$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
				break;
		}
		
		die(json_encode($response));
	}
}