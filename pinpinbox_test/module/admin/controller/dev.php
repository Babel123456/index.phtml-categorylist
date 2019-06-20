<?php
class devController extends backstageController {
	function __construct() {}
	
	function index () {
		list($jqgrid_html, $jqgrid_js) = parent::$html->grid();
		list($imagebox_html, $imagebox_js) = parent::$html->imagebox('.jqgrid_img');
		list($html2, $js2) = parent::$html->magnific_popup();
		parent::$data['index'] = $jqgrid_html.$imagebox_html.$html2;
		parent::$html->set_js($jqgrid_js.$imagebox_js.$js2);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
	
	function form () {
		if (!empty($_POST)) {
			switch ($_GET['act']) {
				case 'add'://新增
					json_encode_return(1, _('Success, back to previous page?'), parent::url('dev', 'index'));
					break;
						
				case 'edit'://修改
					json_encode_return(1, _('Success, back to previous page?'), parent::url('dev', 'index'));
					break;
			}
		}
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		
		switch ($_GET['act']) {
			case 'add':
				parent::$data['action'] = parent::url('dev', 'form', array('act' => 'add'));
				break;
				
			case 'edit':
				parent::$data['action'] = parent::url('dev', 'form', array('act' => 'edit'));
				break;
		}
		
		//form
		$column = array();
		$extra = null;
		
		list($html, $js) = parent::$html->text('id="name" name="name" value=""');
		$column[] = array('key'=>_('Name'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->date('name="date" value=""');
		$column[] = array('key'=>_('Date'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->time('name="time" value=""');
		$column[] = array('key'=>_('Time'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->datetime('name="datetime" value=""', array('minDateTime'=>'', 'maxDateTime'=>date('Y,m,d,H,i', mktime(date('H'), date('i'), 0, date('m'), date('d') + 90, date('Y')))));
		$column[] = array('key'=>_('DateTime'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->upload('name="upload" value=""');
		$column[] = array('key'=>_('Upload'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->textarea('name="textarea" value=""');
		$column[] = array('key'=>_('Textarea'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->ckeditor('id="editor" name="editor" value=""');
		$column[] = array('key'=>_('Editor'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->color('id="color" name="color" value=""');
		$column[] = array('key'=>_('Color'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->image('id="background" name="background" value=""');
		$column[] = array('key'=>_('Background'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->image('id="foreground" name="foreground" value=""');
		$column[] = array('key'=>_('Foreground'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->image_combine('id="image_combine" name="image_combine" value=""');
		$column[] = array('key'=>_('Image Combine'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->dynamictable('keyvalueremark', 'name="dev[one][]"');
		$column[] = array('key'=>'dynamictable - keyvalueremark', 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->keyvalueremark('name="dev"');
		$column[] = array('key'=>'keyvalueremark', 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html1, $js1) = parent::$html->submit('value="'._('Submit').'"');
		list($html2, $js2) = parent::$html->back('value="'._('Back').'"');
		$column[] = array('key'=>'&nbsp;', 'value'=>$html1.'&emsp;'.$html2);
		parent::$html->set_js($js1.$js2);
		
		list($html, $js) = parent::$html->table('class="table"', $column, $extra);
		$a_tabs[0] = array('href'=>'#tabs-0', 'name'=>_('Form'), 'value'=>$html);
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->tabs($a_tabs);
		$formcontent = $html;
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->form('id="form" action="" method="post" onsubmit="false"', $formcontent);
		parent::$data['form'] = $html;
		parent::$html->set_js($js);
		
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
	
	function delete () {
		if (!empty($_POST)) {
			json_encode_return(1, _('Success'));
		}
		exit;
	}
	
	function json () {
		$response = array();
		
		//column
		$column = array(
				'`admin_id`',
				'`account`',
				'`name`',
				'`inserttime`',
				'`modifytime`',
				'`modifyadmin_id`',
		);
		
		list($where, $group, $order, $limit) = parent::grid_request_encode();
		
		//data
		$fetchAll = model('admin')->get(sql_select_encode($column, null, $where, $group, null, $order, $limit), 'fetchAll');
		foreach ($fetchAll as $k => &$v) {
			$v['modifyadmin_id'] = adminModel::getOne($v['modifyadmin_id'])['name'];
		}
		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = model('admin')->get(sql_select_encode(array('count(1)'), null, $where, $group), 'fetchColumn');
		
		die(json_encode($response));
	}
}