<?php
class settingsController extends backstageController {
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
			//form
			$keyword = $_POST['keyword'];
			$lang_id = $_POST['lang_id'];
			$admingroup_id = $_POST['admingroup_id'];
			$type = $_POST['type'];
			$value = $_POST['value'];
			$remark = $_POST['remark'];
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Model(M_CLASS)->where(array(array(array(array('keyword', '=', $keyword)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by [Keyword]'));
					}
					
					if (Model('settings_lang')->where(array(array(array(array('keyword', '=', $keyword), array('lang_id', '=', $lang_id)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by [Keyword] and [Lang]'));
					}
					
					Model(M_CLASS);
					Model('settings_lang');
					Model()->beginTransaction();
					
					//settings
					$add = array(
							'keyword'=>$keyword,
							'admingroup_id'=>$admingroup_id,
							'type'=>$type,
							'remark'=>$remark,
							'inserttime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->add($add);
					
					//settings_lang
					$add = array(
							'keyword'=>$keyword,
							'lang_id'=>$lang_id,
							'value'=>$value,
					);
					Model('settings_lang')->add($add);
					
					Model()->commit();
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
						
				//修改
				case 'edit':
					$m_settings = Model(M_CLASS)->where(array(array(array(array('keyword', '=', $keyword)), 'and')))->fetch();
					if ($m_settings['admingroup_id'] != adminModel::getSession()['lastloginadmingroup_id']) {
						json_encode_return(0, _('Admin Groups inconsistent.'));
					}
					
					Model(M_CLASS);
					Model('settings_lang');
					Model()->beginTransaction();
					
					//settings
					$edit = array(
							'type'=>$type,
							'remark'=>$remark,
							'modifytime'=>inserttime(),//由於可能沒有更動而未觸發 ON UPDATE CURRENT_TIMESTAMP, 為了在 grid 便於排序, 因此主動賦值
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->where(array(array(array(array('keyword', '=', $keyword)), 'and')))->edit($edit);
					
					//settings_lang
					$replace = array(
							'keyword'=>$keyword,
							'lang_id'=>$lang_id,
							'value'=>$value,
					);
					Model('settings_lang')->replace($replace);
					
					Model()->commit();
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$keyword = null;
		$lang_id = Core\Lang::$default;
		$admingroup_id = null;
		$type = 'textarea';
		$value_textarea = null;
		$value_editor = null;
		$remark = null;
		$inserttime = null;
		$modifytime = null;
		$modifyadmin_name = null;
		
		//form
		$column = array();
		$extra = null;
		
		//tabs
		$a_tabs = array();
		
		//form for add or edit
		switch ($_GET['act']) {
			//新增
			case 'add':
				//keyword
				list($html_keyword, $js) = parent::$html->text('id="keyword" name="keyword" value="'.$keyword.'" size="64" maxlength="32" required');
				parent::$html->set_js($js);
				
				//admingroup
				$join = array(
						array('left join', 'admin_admingroup' ,'using(admin_id)'),
						array('left join', 'admingroup' ,'using(admingroup_id)'),
				);
				$where = array(
						array(array(array('admin.admin_id', '=', adminModel::getSession()['admin_id'])), 'and'),
				);
				$m_admin = Model('admin')->column(array('admingroup.admingroup_id', 'admingroup.name', 'admin_admingroup.class'))->join($join)->where($where)->order(array('admingroup.name'=>'asc', 'admin_admingroup.class'=>'asc'))->fetchAll();
				$tmp0 = array();
				foreach ($m_admin as $v0) {
					$tmp0[] = array(
							'value'=>$v0['admingroup_id'],
							'text'=>$v0['name'].' - '.$v0['class'],
					);
				}
				list($html_admingroup, $js) = parent::$html->selectKit(['id'=>'admingroup_id', 'name'=>'admingroup_id'], $tmp0);
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'add'));
				break;
				
			//修改	
			case 'edit':
				$keyword = $_GET['keyword'];
				
				$m_settings = Model(M_CLASS)->where(array(array(array(array('keyword', '=', $keyword)), 'and')))->fetch();
				$m_settings_lang = Model('settings_lang')->where(array(array(array(array('keyword', '=', $keyword), array('lang_id', '=', Core\Lang::get())), 'and')))->fetch();
				
				//settings
				$keyword = $m_settings['keyword'];
				$admingroup_id = $m_settings['admingroup_id'];
				$type = $m_settings['type'];				
				$remark = $m_settings['remark'];
				$inserttime = $m_settings['inserttime'];
				$modifytime = $m_settings['modifytime'];
				$modifyadmin_name = adminModel::getOne($m_settings['modifyadmin_id'])['name'];
				
				//settings_lang
				$lang_id = $m_settings_lang['lang_id'];
				$value_textarea = $type == 'textarea'? $m_settings_lang['value'] : null;
				$value_editor = $type == 'editor'? $m_settings_lang['value'] : null;
				
				//keyword
				list($html_keyword, $js) = parent::$html->hidden('id="keyword" name="keyword" value="'.$keyword.'"', $keyword);
				parent::$html->set_js($js);
				
				//admingroup
				list($html_admingroup, $js) = parent::$html->hidden('id="admingroup_id" name="admingroup_id" value="'.$admingroup_id.'"', parent::get_grid_display('admingroup', $admingroup_id));
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		$column[] = array('key'=>_('Keyword'), 'value'=>$html_keyword);
		
		$m_lang = Model('lang')->where(array(array(array(array('act', '=', 'open')), 'and')))->order(array('lang_id'=>'asc'))->fetchAll();
		$a_lang_id = array();
		foreach ($m_lang as $v0) {
			$a_lang_id[] = array(
					'value'=>$v0['lang_id'],
					'text'=>$v0['lang_id'].' - '.$v0['name'],
			);
		}
		list($html, $js) = parent::$html->selectKit(['id'=>'lang_id', 'name'=>'lang_id'], $a_lang_id, $lang_id);
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('lang'), 'value'=>$html);
		parent::$html->set_js($js);		
		
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('admingroup'), 'value'=>$html_admingroup);
		
		$a_type = array();
		foreach (array('textarea'=>'Textarea', 'editor'=>'Editor') as $k0 => $v0) {
			$a_type[] = array(
					'name'=>'type',
					'value'=>$k0,
					'text'=>$v0,
			);
		}
		list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_type, $type);
		$column[] = array('key'=>_('Type'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->textarea('id="value-textarea" name="value-textarea" style="width:400px; height:100px; font-size:14px;"', htmlspecialchars($value_textarea));
		$column[] = array('key'=>_('Value'), 'value'=>$html, 'trattr'=>'id="value-tr-textarea"');
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->ckeditor('id="value-editor" name="value-editor"', $value_editor);
		$column[] = array('key'=>_('Value'), 'value'=>$html, 'trattr'=>'id="value-tr-editor"');
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->textarea('id="remark" name="remark" style="width:400px; height:100px; font-size:14px;"', htmlspecialchars($remark));
		$column[] = array('key'=>_('Remark'), 'value'=>$html);
		parent::$html->set_js($js);
		
		$column[] = array('key'=>_('Insert Time'), 'value'=>$inserttime);
		
		$column[] = array('key'=>_('Modify Time'), 'value'=>$modifytime);
		
		$column[] = array('key'=>_('Modify Admin Name'), 'value'=>$modifyadmin_name);
		
		list($html0, $js0) = parent::$html->submit('value="'._('Submit').'"');
		list($html1, $js1) = parent::$html->back('value="'._('Back').'"');
		$column[] = array('key'=>'&nbsp;', 'value'=>$html0.'&emsp;'.$html1);
		parent::$html->set_js($js0.$js1);
		
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
		
		//column
		$column = array(
				'keyword',
				'admingroup_id',
				'`type`',
				'remark',
				'modifytime',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
		foreach ($fetchAll as &$v0) {
			$v0['admingroupX'] = parent::get_grid_display('admingroup', $v0['admingroup_id']);
			$v0['remark'] = nl2br(htmlspecialchars($v0['remark']));
		}
		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
		
		die(json_encode($response));
	}
	
	//取得指定 lang_id 的值
	function extra0() {
		if (is_ajax()) {
			$m_settings_lang = Model('settings_lang')->where(array(array(array(array('keyword', '=', $_POST['keyword']), array('lang_id', '=', $_POST['lang_id'])), 'and')))->fetch();
			json_encode_return(1, null, null, array('value'=>$m_settings_lang['value']));
		}
		die();
	}
}