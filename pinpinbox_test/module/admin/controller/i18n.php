<?php
class i18nController extends backstageController {
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
			$keyword_original = $_POST['keyword_original'];
			$keyword = $_POST['keyword'];
			$lang_id = $_POST['lang_id'];
			$value = $_POST['value'];
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Model(M_CLASS)->column(array('count(1)'))->where(array(array(array(array('keyword', '=', $keyword), array('lang_id', '=', $lang_id)), 'and')))->fetchColumn()) {
						json_encode_return(0, _('Data already exists by : ').'Keyword & '.parent::get_adminmenu_name_by_class('lang'));
					}
					
					$add = array(
							'keyword'=>$keyword,
							'lang_id'=>$lang_id,
							'value'=>$value,
							'inserttime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->add($add);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
						
				//修改
				case 'edit':
					$replace = array(
							'keyword'=>$keyword_original,
							'lang_id'=>$lang_id,
							'value'=>$value,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->replace($replace);
					
					//如果 keyword 有更動, 則 update 所有 keyword
					if ($keyword != $keyword_original) {
						$edit = array(
								'keyword'=>$keyword,
								'modifyadmin_id'=>adminModel::getSession()['admin_id'],
						);
						Model(M_CLASS)->where(array(array(array(array('keyword', '=', $keyword_original)), 'and')))->edit($edit);
					}
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$keyword_original = null;
		$keyword = null;
		$lang_id = \Core\Lang::$default;
		$value = null;
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
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'add'));
				break;
				
			//修改	
			case 'edit':
				$M_CLASS_id = $_GET[M_CLASS.'_id'];
				
				$m_i18n = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();
				
				//i18n
				$keyword = $keyword_original = $m_i18n['keyword'];
				$lang_id = $m_i18n['lang_id'];
				$value = $m_i18n['value'];
				$inserttime = $m_i18n['inserttime'];
				$modifytime = $m_i18n['modifytime'];
				$modifyadmin_name = adminModel::getOne($m_i18n['modifyadmin_id'])['name'];
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		list($html0, $js0) = parent::$html->hidden('id="keyword_original" name="keyword_original" value="'.htmlspecialchars($keyword_original).'"');
		list($html1, $js1) = parent::$html->textarea('id="keyword" name="keyword" style="width:400px; height:100px; font-size:14px;" required', htmlspecialchars($keyword));
		$column[] = array('key'=>_('Keyword'), 'value'=>$html0.$html1);
		parent::$html->set_js($js0.$js1);
		
		$m_lang = Model('lang')->where(array(array(array(array('act', '=', 'open')), 'and')))->order(array('lang_id'=>'asc'))->fetchAll();
		$a_lang_id = array();
		foreach ($m_lang as $v0) {
			$a_lang_id[] = array(
					'value'=>$v0['lang_id'],
					'text'=>$v0['lang_id'].' - '.$v0['name']
			);
		}
		list($html, $js) = parent::$html->selectKit(['id'=>'lang_id', 'name'=>'lang_id'], $a_lang_id, $lang_id);
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('lang'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->textarea('id="value" name="value" style="width:400px; height:100px; font-size:14px;"', htmlspecialchars($value));
		$column[] = array('key'=>_('Value'), 'value'=>$html);
		parent::$html->set_js($js);
		
		$column[] = array('key'=>_('Insert Time'), 'value'=>'<span id="inserttime">'.$inserttime.'</span>');
		
		$column[] = array('key'=>_('Modify Time'), 'value'=>'<span id="modifytime">'.$modifytime.'</span>');
		
		$column[] = array('key'=>_('Modify Admin Name'), 'value'=>'<span id="modifyadmin_id">'.$modifyadmin_name.'</span>');
		
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
				M_CLASS.'_id',
				'keyword',
				'lang_id',
				'value',
				'modifytime',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
		foreach ($fetchAll as &$v0) {
			$v0['keyword'] = nl2br(htmlspecialchars($v0['keyword']));
			$v0['value'] = nl2br(htmlspecialchars($v0['value']));
		}
		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
		
		die(json_encode($response));
	}
	
	//取得指定 lang_id 的值
	function extra0() {
		if (is_ajax()) {
			$m_i18n = Model(M_CLASS)->where(array(array(array(array('keyword', '=', $_POST['keyword_original']), array('lang_id', '=', $_POST['lang_id'])), 'and')))->fetch();
			$data = array(
					'value'=>$m_i18n['value'],
					'inserttime'=>$m_i18n['inserttime'],
					'modifytime'=>$m_i18n['modifytime'],
					'modifyadmin_id'=>adminModel::getOne($m_i18n['modifyadmin_id'])['name'],
			);
			json_encode_return(1, null, null, $data);
		}
		die();
	}
}