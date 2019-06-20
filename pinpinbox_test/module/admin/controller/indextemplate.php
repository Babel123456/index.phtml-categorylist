<?php
class indextemplateController extends backstageController {
	function __construct() {}
	
	function template_search(){
		if (is_ajax()) {
			$template_id = (!empty($_POST['template_id'])) ? $_POST['template_id'] : null ;

			if($template_id === null) json_encode_return(0, _('未輸入相本id或輸入錯誤,請重新確認'), null, null);

			$m_template = Model('template')->column(['template.template_id', 'template.user_id', 'template.image', 'template.name template_name', 'user.name user_name', 'template.act'])->join([['left join', 'user', 'using(user_id)']])->where([[[['template_id', '=', $template_id], ['template.act', 'in', ['open', 'close']]] ,'and']])->fetch();
			$a_template = [
				'template_id' => $m_template['template_id'],
				'template' => $m_template['template_name'],
				'user' => $m_template['user_name'],
				'cover' => URL_UPLOAD.'pinpinbox'.$m_template['image'],
			];

			if(!empty($m_template)) {
				($m_template['act'] == 'close') ? json_encode_return(0, _('相本狀態為"關閉中, 無法引入為首頁布局'), null, null) : json_encode_return(1, null, null, json_encode($a_template));
			} else {
				json_encode_return(0, _('找不到指定的相本,請重新確認'), null, null);
			}
		}
	}

	function index() {
		list($html0, $js0) = parent::$html->grid();
		list($html1, $js1) = parent::$html->browseKit(array('selector'=>'.grid-img'));
		parent::$data['index'] = $html0.$html1;
		parent::$html->set_js($js0.$js1);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}

	function form() {
		if (is_ajax()) {
			//form
			$name = $_POST['name'];
			$sequence = $_POST['sequence'];
			$act = $_POST['act'];
			$exhibit = $_POST['exhibit'];
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Model(M_CLASS)->where(array(array(array(array('name', '=', $name)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					$add = array(
							'name'=>$name,
							'sequence'=>$sequence,
							'act'=>$act,
							'exhibit'=>$exhibit,
							'inserttime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					$M_CLASS_id = Model(M_CLASS)->add($add);
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
				
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					if (Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '!=', $M_CLASS_id), array('name', '=', $name)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					//form
					$edit = array(
							'name'=>$name,
							'sequence'=>$sequence,
							'act'=>$act,
							'exhibit'=>$exhibit,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->edit($edit);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-from
		$name = null;
		$sequence = null;
		$act = 'close';
		$inserttime = null;
		$modifytime = null;
		$modifyadmin_name = null;
		$exhibit = null;

		//form
		$column = array();
		$extra = null;
		
		//tabs
		$a_tabs = array();
		
		//layout
		$layout = '<div id="elementlayout">
					<ul id="sort">
						<li data-status="unset" id="item1"><img></li>
						<li data-status="unset" id="item2"><img></li>
						<li data-status="unset" id="item3"><img></li>
						<li data-status="unset" id="item4"><img></li>
					</ul>
				</div>';

		//form for add or edit
		switch ($_GET['act']) {
			//新增
			case 'add':
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'add'));
				break;
				
			//修改
			case 'edit':
				if (!empty($_GET)) {
					$M_CLASS_id = $_GET[M_CLASS.'_id'];
					$m_indextemplate = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();

					//form
					$name = $m_indextemplate['name'];
					$act = $m_indextemplate['act'];
					$exhibit = json_decode( $m_indextemplate['exhibit'] );
					$sequence = $m_indextemplate['sequence'];
					$inserttime = $m_indextemplate['inserttime'];
					$modifytime = $m_indextemplate['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_indextemplate['modifyadmin_id'])['name'];
				}
				
				//fetch template info
				foreach ($exhibit as $k0 => $v0) {
					$m_template = Model('template')->column(['template.template_id', 'template.user_id', 'template.image','template.name template_name', 'user.name user_name'])->join([['left join', 'user', 'using(user_id)']])->where([[[['template.act' ,'=', 'open'], ['template.template_id', '=' , $v0]] ,'and']])->fetch();
					$tmp[] = '<li id="item'.($k0+1).'" data-status="set" data-template_id="'.$v0.'"><img src="'.URL_UPLOAD.'pinpinbox'.$m_template['image'].'"></li>';
				}

				$layout = '<div id="elementlayout"><ul id="sort">'.implode('', $tmp).'</ul></div>';

				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);

				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'" size="64" maxlength="64" required');
		$column[] = array('key'=>_('Name'), 'value'=>$html);
		parent::$html->set_js($js);	

		list($html, $js) = parent::$html->number('id="search_template" name="search_template" value="" size="10" min="1" max="99999999" ');
		$column[] = array('key'=>_('選擇模板'), 'value'=>$html);

		list($html, $js) = array($layout, null);
		$column[] = array('key'=>_('Exhibit'), 'value'=>$html, 'key_remark'=>_('首頁展示內容'));
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="'.$sequence.'" min="0" max="255" required');
		$column[] = array('key'=>_('Sequence'),'value'=>$html);
		parent::$html->set_js($js);
		
		$a_act = array();
		foreach (json_decode(Core::settings('EVENT_ACT'), true) as $k0 => $v0) {
			$a_act[] = array(
					'name'=>'act',
					'value'=>$k0,
					'text'=>$v0,
			);
		}
		list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_act, $act);
		$column[] = array('key'=>_('Act'), 'value'=>$html);
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
		parent::$html->set_css(static_file('js/Image-Select/css/ImageSelect.css'), 'href');
		parent::$html->set_js(static_file('js/Image-Select/js/ImageSelect.jquery.js'), 'src');

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
				'name',
				'sequence',
				'act',
				'inserttime',
				'modifytime',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();

		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();

		
		die(json_encode($response));
	}
	
	function grid_edit() {
		if (!empty($_REQUEST)) {
			Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', (int)$_REQUEST[M_CLASS.'_id'])), 'and')))->edit(array('sequence'=>$_REQUEST['sequence'], 'modifyadmin_id'=>adminModel::getSession()['admin_id']));
			
			json_encode_return(1, 'Edit success.');
		}
		die;
	}

	
}