<?php
class buyController extends backstageController {
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
			$platform = $_POST['platform'];
			$platform_flag = $_POST['platform_flag'];
			$assets = $_POST['assets'];
			$assets_item = $_POST['assets_item'];
			$total = $_POST['total'];
			$currency = $_POST['currency'];
			$obtain = $_POST['obtain'];
			$sequence = $_POST['sequence'];
			$act = $_POST['act'];
			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Model(M_CLASS)->column(['count(1)'])->where([[[['platform', '=', $platform], ['assets', '=', $assets], ['assets_item', '=', $assets_item], ['total', '=', $total], ['currency', '=', $currency]], 'and']])->fetchColumn()) {
						json_encode_return(0, _('Data already exists by [Platform] and [Assets] and [Assets Item] and [Total] and [Currency]'));
					}
					
					if ($platform_flag != null && Model(M_CLASS)->column(['count(1)'])->where([[[['platform', '=', $platform], ['platform_flag', '=', $platform_flag]], 'and']])->fetchColumn()) {
						json_encode_return(0, _('Data already exists by [Platform] and [Platform Flag]'));
					}
					
					//M_CLASS
					$add = [
							'platform'=>$platform,
							'platform_flag'=>$platform_flag,
							'assets'=>$assets,
							'assets_item'=>$assets_item,
							'total'=>$total,
							'currency'=>$currency,
							'obtain'=>$obtain,
							'sequence'=>$sequence,
							'act'=>$act,
							'inserttime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					];
					Model(M_CLASS)->add($add);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
				
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					if (Model(M_CLASS)->column(['count(1)'])->where([[[[M_CLASS.'_id', '!=', $M_CLASS_id], ['platform', '=', $platform], ['assets', '=', $assets], ['assets_item', '=', $assets_item], ['total', '=', $total], ['currency', '=', $currency]], 'and']])->fetchColumn()) {
						json_encode_return(0, _('Data already exists by [Platform] and [Assets] and [Assets Item] and [Total] and [Currency]'));
					}
					
					if (Model(M_CLASS)->column(['count(1)'])->where([[[[M_CLASS.'_id', '!=', $M_CLASS_id], ['platform', '=', $platform], ['platform_flag', '=', $platform_flag]], 'and']])->fetchColumn()) {
						json_encode_return(0, _('Data already exists by [Platform] and [Platform Flag]'));
					}
					
					//M_CLASS
					$edit = [
							'platform_flag'=>$platform_flag,
							'total'=>$total,
							'obtain'=>$obtain,
							'sequence'=>$sequence,
							'act'=>$act,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					];
					Model(M_CLASS)->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->edit($edit);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-from
		$platform = 'apple';
		$platform_flag = null;
		$assets = 'usergrade';
		$assets_item = 'plus';
		$assets_item_usergrade = 'plus';
		$assets_item_userpoint = 'point';
		$total = 0;
		$currency = 'TWD';
		$obtain = 0;
		$sequence = 255;
		$act = 'close';
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
				$a_platform = [];
				foreach (json_decode(Core::settings('PLATFORM'), true) as $k0 => $v0) {
					$a_platform[] = [
							'name'=>'platform',
							'value'=>$k0,
							'text'=>$v0,
					];
				}
				list($html_platform, $js_platform) = parent::$html->radiotable('150px', '30px', 5, $a_platform, $platform);
				parent::$html->set_js($js_platform);
				
				$a_assets = array();
				foreach (json_decode(Core::settings('ASSETS'), true) as $k0 => $v0) {
					$a_assets[] = [
							'name'=>'assets',
							'value'=>$k0,
							'text'=>$v0,
					];
				}
				list($html_assets, $js_assets) = parent::$html->radiotable('150px', '30px', 5, $a_assets, $assets);
				parent::$html->set_js($js_assets);
				
				$a_assets_item_usergrade = [];
				foreach (json_decode(Core::settings('ASSETS_ITEM_USERGRADE'), true) as $k0 => $v0) {
					$a_assets_item_usergrade[] = [
							'name'=>'assets_item_usergrade',
							'value'=>$k0,
							'text'=>$v0,
					];
				}
				list($html_assets_item_usergrade, $js) = parent::$html->radiotable('150px', '30px', 5, $a_assets_item_usergrade, $assets_item_usergrade);
				parent::$html->set_js($js);
				
				$a_assets_item_userpoint = [];
				foreach (json_decode(Core::settings('ASSETS_ITEM_USERPOINT'), true) as $k0 => $v0) {
					$a_assets_item_userpoint[] = [
							'name'=>'assets_item_userpoint',
							'value'=>$k0,
							'text'=>$v0,
					];
				}
				list($html_assets_item_userpoint, $js) = parent::$html->radiotable('150px', '30px', 5, $a_assets_item_userpoint, $assets_item_userpoint);
				parent::$html->set_js($js);
				
				$a_currency = [];
				foreach (json_decode(Core::settings('CURRENCY'), true) as $k0 => $v0) {
					$a_currency[] = [
							'name'=>'currency',
							'value'=>$k0,
							'text'=>$v0,
					];
				}
				list($html_currency, $js) = parent::$html->radiotable('150px', '30px', 5, $a_currency, $currency);
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', ['act'=>'add']);
				break;
				
			//修改
			case 'edit':
				if (!empty($_GET)) {
					$M_CLASS_id = $_GET[M_CLASS.'_id'];
					
					$m_buy = Model(M_CLASS)->where([[[[M_CLASS.'_id', '=', $M_CLASS_id]], 'and']])->fetch();
					
					//form
					$platform = $m_buy['platform'];
					$platform_flag = $m_buy['platform_flag'];
					$assets = $m_buy['assets'];
					$assets_item = $m_buy['assets_item'];
					$assets_item_usergrade = $assets == 'usergrade'? $m_buy['assets_item'] : null;
					$assets_item_userpoint = $assets == 'userpoint'? $m_buy['assets_item'] : null;
					$total = $m_buy['total'];
					$currency = $m_buy['currency'];
					$obtain = $m_buy['obtain'];
					$sequence = $m_buy['sequence'];
					$act = $m_buy['act'];
					$inserttime = $m_buy['inserttime'];
					$modifytime = $m_buy['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_buy['modifyadmin_id'])['name'];
				}
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				list($html_platform, $js) = parent::$html->hidden('id="platform" name="platform" value="'.$platform.'"', $platform);
				parent::$html->set_js($js);
				
				list($html_assets, $js) = parent::$html->hidden('id="assets" name="assets" value="'.$assets.'"', $assets);
				parent::$html->set_js($js);
				
				$html_assets_item_usergrade = $assets_item_usergrade;
				
				$html_assets_item_userpoint = $assets_item_userpoint;
				
				list($html_currency, $js) = parent::$html->hidden('id="currency" name="currency" value="'.$currency.'"', $currency);
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', ['act'=>'edit']);
				break;
		}
		
		$column[] = ['key'=>_('Platform'), 'value'=>$html_platform];
		
		list($html, $js) = parent::$html->text('id="platform_flag" name="platform_flag" value="'.$platform_flag.'" size="32" maxlength="32"');
		$column[] = ['key'=>'Platform Flag', 'value'=>$html];
		parent::$html->set_js($js);
		
		$column[] = ['key'=>_('Assets'), 'value'=>$html_assets];
		
		$column[] = ['key'=>_('Assets Item'), 'value'=>$html_assets_item_usergrade, 'trattr'=>'id="assets_item-tr-usergrade"', 'key_remark'=>'For UserGrade'];
		$column[] = ['key'=>_('Assets Item'), 'value'=>$html_assets_item_userpoint, 'trattr'=>'id="assets_item-tr-userpoint"', 'key_remark'=>'For UserPoint'];
		parent::$data['assets_item'] = $assets_item;
		
		list($html, $js) = parent::$html->number('id="total" name="total" value="'.$total.'" min="0" required');
		$column[] = ['key'=>_('Total'), 'value'=>$html];
		parent::$html->set_js($js);
		
		$column[] = ['key'=>_('Currency'), 'value'=>$html_currency];
		
		list($html, $js) = parent::$html->number('id="obtain" name="obtain" value="'.$obtain.'" min="0" max="65535" required');
		$column[] = ['key'=>_('Obtain'), 'value'=>$html];
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="'.$sequence.'" min="0" max="255" required');
		$column[] = ['key'=>_('Sequence'),'value'=>$html];
		parent::$html->set_js($js);
		
		$a_act = [];
		foreach (json_decode(Core::settings('BUY_ACT'), true) as $k0 => $v0) {
			$a_act[] = [
					'name'=>'act',
					'value'=>$k0,
					'text'=>$v0,
			];
		}
		list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_act, $act);
		$column[] = ['key'=>_('Act'), 'value'=>$html];
		parent::$html->set_js($js);
		
		$column[] = ['key'=>_('Insert Time'), 'value'=>$inserttime];
		
		$column[] = ['key'=>_('Modify Time'), 'value'=>$modifytime];
		
		$column[] = ['key'=>_('Modify Admin Name'), 'value'=>$modifyadmin_name];
		
		list($html0, $js0) = parent::$html->submit('value="'._('Submit').'"');
		list($html1, $js1) = parent::$html->back('value="'._('Back').'"');
		$column[] = ['key'=>'&nbsp;', 'value'=>$html0.'&emsp;'.$html1];
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
		$response = array();
		
		$case = isset($_POST['case'])? $_POST['case'] : null;
		
		switch ($case) {
			default:
				//column
				$column = array(
						M_CLASS.'_id',
						'platform',
						'platform_flag',
						'assets',
						'assets_item',
						'total',
						'currency',
						'obtain',
						'sequence',
						'act',
						'modifytime',
				);
				
				list($where, $group, $order, $limit) = parent::grid_request_encode();
				
				//data
				$response['data'] = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
				
				//total
				$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
				break;
		}
		
		die(json_encode($response));
	}
	
	function grid_edit() {
		if (!empty($_REQUEST['models'])) {
			foreach ($_REQUEST['models'] as $v0) {
				Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', (int)$v0[M_CLASS.'_id'])), 'and')))->edit(array('sequence'=>$v0['sequence'], 'modifyadmin_id'=>adminModel::getSession()['admin_id']));
			}
			json_encode_return(1, 'Edit success.');
		}
		die;
	}
}