<?php
class deviceController extends backstageController {
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
			$enabled = filter_var($_POST['enabled'], FILTER_VALIDATE_BOOLEAN);
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					break;
		
				//修改
				case 'edit':
					$M_CLASS_id = isset($_POST[M_CLASS.'_id'])? $_POST[M_CLASS.'_id'] : null;
					
					if ($M_CLASS_id === null) json_encode_return(0, _('Param error'), parent::url(M_CLASS, 'index'));
					
					$m_device = deviceModel::newly()->column(['device_id', 'aws_sns_endpointarn'])->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->fetch();
					
					if (empty($m_device)) json_encode_return(0, _('Data does not exist.'), parent::url(M_CLASS, 'index'));
					
					deviceModel::newly()->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->edit([
							'enabled'=>$enabled,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					]);
					
					deviceModel::enable($m_device['device_id'], $enabled);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//init value-form
		
		//form
		$column = [];
		$extra = null;
		
		//form for add or edit
		switch ($_GET['act']) {
			//新增
			case 'add':
				break;
		
			//修改
			case 'edit':
				$M_CLASS_id = isset($_GET[M_CLASS.'_id'])? $_GET[M_CLASS.'_id'] : null;
				
				if ($M_CLASS_id === null) redirect(parent::url(M_CLASS, 'index'), _('Param error'));
				
				$m_device = deviceModel::newly()->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->fetch();
				
				$user_id = $m_device['user_id'];
				$identifier = $m_device['identifier'];
				$os = $m_device['os'];
				$browser = $m_device['browser'];
				$token = $m_device['token'];
				$aws_sns_endpointarn = $m_device['aws_sns_endpointarn'];
				$enabled = $m_device['enabled'];
				$inserttime = $m_device['inserttime'];
				$modifytime = $m_device['modifytime'];
				$modifyadmin_name = adminModel::getOne($m_device['modifyadmin_id'])['name'];
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', ['act'=>'edit']);
				break;
		}
		
		$column[] = ['key'=>parent::get_adminmenu_name_by_class('user'), 'value'=>parent::get_grid_display('user', $user_id)];
		
		$column[] = ['key'=>_('Identifier'), 'value'=>$identifier];
		
		switch ($os) {
			case 'android':
				$os = ucfirst($os);
				break;
					
			case 'ios':
				$os = lcfirst(strtoupper($os));
				break;
		}
		$column[] = ['key'=>_('OS'), 'value'=>$os];
		
		$column[] = ['key'=>_('Browser'), 'value'=>ucfirst($browser)];
		
		$column[] = ['key'=>_('Token'), 'value'=>$token];
		
		$column[] = ['key'=>_('AWS SNS Endpoint ARN'), 'value'=>$aws_sns_endpointarn];
		
		$a_enabled = [];
		foreach ([1=>'Ture', 0=>'False'] as $k0 =>$v0) {
			$a_enabled[] = [
					'name'=>'enabled',
					'value'=>$k0,
					'text'=>$v0,
			];
		}
		list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_enabled, $enabled, true);
		$column[] = ['key'=>_('Enabled'), 'value'=>$html];
		parent::$html->set_js($js);
		
		$column[] = ['key'=>_('Insert Time'), 'value'=>$inserttime];
		
		$column[] = ['key'=>_('Modify Time'), 'value'=>$modifytime];
		
		$column[] = ['key'=>_('Modify Admin Name'), 'value'=>$modifyadmin_name];
		
		list($html0, $js0) = parent::$html->submit('value="'._('Submit').'"');
		list($html1, $js1) = parent::$html->back('value="'._('Back').'"');
		$column[] = ['key'=>'&nbsp;', 'value'=>$html0 . '&emsp;' . $html1];
		parent::$html->set_js($js0.$js1);
		
		list($html, $js) = parent::$html->table('class="table"', $column, $extra);
		$a_tabs[0] = ['href'=>'#tabs-0', 'name'=>_('Form'), 'value'=>$html];
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
		$response = [];
		
		$case = isset($_POST['case'])? $_POST['case'] : null;
		
		switch ($case) {
			default:
				list ($where, $group, $order, $limit) = parent::grid_request_encode();
				
				//data
				$fetchAll = deviceModel::newly()
					->column([
							M_CLASS.'_id',
							'user_id',
							'os',
							'browser',
							'aws_sns_endpointarn',
							'enabled',
							'modifytime',
					])->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
				foreach ($fetchAll as &$v0) {
					$v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
					
					switch ($v0['os']) {
						case 'android':
							$v0['os'] = ucfirst($v0['os']);
							break;
							
						case 'ios':
							$v0['os'] = lcfirst(strtoupper($v0['os']));
							break;
					}
					
					$v0['browser'] = ucfirst($v0['browser']);
				}
				$response['data'] = $fetchAll;
				
				//total
				$response['total'] = deviceModel::newly()->column(['COUNT(1)'])->where($where)->group($group)->fetchColumn();
				break;
		}
		
		die(json_encode($response));
	}
}