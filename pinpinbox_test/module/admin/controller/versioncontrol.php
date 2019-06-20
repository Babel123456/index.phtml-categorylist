<?php
class versioncontrolController extends backstageController {
	function __construct() {}
	
	function index() {
		list ($html, $js) = parent::$html->grid();
		parent::$data['index'] = $html;
		parent::$html->set_js($js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
	
	function form() {
		if (is_ajax()) {
			//form
			$platform = $_POST['platform'];
			$version = $_POST['version'];
			$type = $_POST['type'];
			$target = $_POST['target'];
			$remark = $_POST['remark'];
			
			if ($target) sort($target, SORT_NATURAL);
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					if (versioncontrolModel::newly()->column(['COUNT(1)'])->where([[[['platform', '=', $platform], ['version', '=', $version]], 'and']])->fetchColumn()) {
						json_encode_return(0, _('Data already exists by : ') . '[' . _('Platform') . '] ' . _('and') . ' [' . _('Version') . ']');
					}
					
					versioncontrolModel::newly()->add([
							'platform'=>$platform,
							'`version`'=>$version,
							'`type`'=>$type,
							'target'=>json_encode($target),
							'remark'=>$remark,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					]);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
						
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					if (versioncontrolModel::newly()->column(['COUNT(1)'])->where([[[[M_CLASS.'_id', '!=', $M_CLASS_id], ['platform', '=', $platform], ['version', '=', $version]], 'and']])->fetchColumn()) {
						json_encode_return(0, _('Data already exists by : ') . '[' . _('Platform') . ']' . _('and') . '[' . _('Version') . ']');
					}
					
					versioncontrolModel::newly()->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->edit([
							'platform'=>$platform,
							'`version`'=>$version,
							'`type`'=>$type,
							'target'=>json_encode($target),
							'remark'=>$remark,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					]);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$platform = null;
		$version = null;
		$type = 'none';
		$a_target = [];
		$remark = null;
		$inserttime = null;
		$modifytime = null;
		$modifyadmin_name = null;
		
		//form
		$column = [];
		$extra = null;
		
		//tabs
		$a_tabs = [];
		
		//form for add or edit
		switch ($_GET['act']) {
			//新增
			case 'add':
				parent::$data['action'] = parent::url(M_CLASS, 'form', ['act'=>'add']);
				break;
				
			//修改
			case 'edit':
				$M_CLASS_id = $_GET[M_CLASS.'_id'];
				
				$m_versioncontrol = versioncontrolModel::newly()->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->fetch();
				
				$platform = $m_versioncontrol['platform'];
				$version = $m_versioncontrol['version'];
				$type = $m_versioncontrol['type'];				
				$a_target = json_decode($m_versioncontrol['target'], true);
				$remark = $m_versioncontrol['remark'];
				$inserttime = $m_versioncontrol['inserttime'];
				$modifytime = $m_versioncontrol['modifytime'];
				$modifyadmin_name = adminModel::getOne($m_versioncontrol['modifyadmin_id'])['name'];
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', ['act'=>'edit']);
				break;
		}
		
		$a_platform = [];
		foreach (versioncontrolModel::newly()->fetchEnum('platform') as $v0) {
			if ($v0 === 'none') continue;
			
			$a_platform[] = [
					'name'=>'platform',
					'value'=>$v0,
					'text'=>ucfirst($v0),
			];
		}
		list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_platform, $platform, true);
		$column[] = ['key'=>_('Platform'), 'value'=>$html];
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->text('id="version" name="version" value="'.$version.'" size="16" maxlength="16" required');
		$column[] = ['key'=>_('Version'), 'value'=>$html];
		parent::$html->set_js($js);
		
		$a_type = [];
		foreach (versioncontrolModel::newly()->fetchEnum('type') as $v0) {
			$a_type[] = [
					'name'=>'type',
					'value'=>$v0,
					'text'=>ucfirst($v0),
			];
		}
		list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_type, $type, true);
		$column[] = ['key'=>_('Type'), 'value'=>$html, 'key_remark'=>'作用類型'];
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->dynamictable('text', 'class="target" name="target[]"', $a_target);
		$column[] = ['key'=>_('Target'), 'value'=>$html, 'key_remark'=>'作用目標，生效於 [' . _('Type') . '] 為 Part 時'];
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->textarea('id="remark" name="remark" style="width:400px; height:100px; font-size:14px;"', htmlspecialchars($remark));
		$column[] = ['key'=>_('Remark'), 'value'=>$html];
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
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$fetchAll = versioncontrolModel::newly()
			->column([
					M_CLASS.'_id',
					'platform',
					'`version`',
					'`type`',
					'target',
					'modifytime',
			])
			->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
		foreach ($fetchAll as &$v0) {
			$v0['platform'] = ucfirst($v0['platform']);
			$v0['type'] = ucfirst($v0['type']);
			
			if ($v0['target'] !== '') $v0['target'] = implode(', ', json_decode($v0['target'], true));
		}
		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = versioncontrolModel::newly()->column(['COUNT(1)'])->where($where)->group($group)->fetchColumn();
		
		die(json_encode($response));
	}
}