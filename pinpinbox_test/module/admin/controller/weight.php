<?php
class weightController extends backstageController {
	function __construct() {}
	
	function index() {
		if (is_ajax()) {
			if (isset($_POST['weight'])) (new weightModel)->replace($_POST['weight']);
			
			json_encode_return(1, _('Edit success.'));
		}
		
		parent::$data['action'] = parent::url(M_CLASS, 'index');
		
		//form for edit
		$m_weight = (new weightModel)->order(['keyword'=>'ASC'])->fetchAll();
		
		$a_album = array_multiple_search($m_weight, 'type', 'album');//album
		$a_user = array_multiple_search($m_weight, 'type', 'user');//user
		
		$Html = new Lib\html();
		
		//form
		$column = [];
		$extra = null;
		
		$column_1 = [];
		$extra_1 = null;
		
		foreach ($a_album as $v0) {
			list ($html_0, $js_0) = $Html->number('id="weight-' . $v0['type'] . '-' . $v0['keyword'] . '" data-type="' . $v0['type'] . '" data-keyword="' . $v0['keyword'] . '" value="' . $v0['weight'] . '" min="0" max="9.99" step="0.01" required');
			list ($html_1, $js_1) = $Html->text('id="remark-' . $v0['type'] . '-' . $v0['keyword'] . '" data-type="' . $v0['type'] . '" data-keyword="' . $v0['keyword'] . '" value="' . $v0['remark'] . '" size="64" maxlength="32"');
			$column_1[] = ['key'=>$v0['keyword'], 'value'=>$html_0 . '&emsp;&emsp;&emsp;&emsp;' . '占比：<span data-tag="weight-accounting"></span>%' . '&emsp;&emsp;&emsp;&emsp;' . _('Remark') . '：' . $html_1];
			$Html->set_js($js_0 . $js_1);
		}
		
		list ($html, $js) = $Html->table('class="table"', $column_1, $extra_1);
		$column[] = ['key'=>_('Album'), 'value'=>$html];
		$Html->set_js($js);
		
		$column_2 = [];
		$extra_2 = null;
		
		foreach ($a_user as $v0) {
			list ($html_0, $js_0) = $Html->number('id="weight-' . $v0['type'] . '-' . $v0['keyword'] . '" data-type="' . $v0['type'] . '" data-keyword="' . $v0['keyword'] . '" value="' . $v0['weight'] . '" min="0" max="9.99" step="0.01" required');
			list ($html_1, $js_1) = $Html->text('id="remark-' . $v0['type'] . '-' . $v0['keyword'] . '" data-type="' . $v0['type'] . '" data-keyword="' . $v0['keyword'] . '" value="' . $v0['remark'] . '" size="64" maxlength="32"');
			$column_2[] = ['key'=>$v0['keyword'], 'value'=>$html_0 . '&emsp;&emsp;&emsp;&emsp;' . '占比：<span data-tag="weight-accounting"></span>%' . '&emsp;&emsp;&emsp;&emsp;' . _('Remark') . '：' . $html_1];
			$Html->set_js($js_0 . $js_1);
		}
		
		list ($html, $js) = $Html->table('class="table"', $column_2, $extra_2);
		$column[] = ['key'=>_('User'), 'value'=>$html];
		$Html->set_js($js);
		
		$m_weight = (new weightModel)->column(['modifytime', 'modifyadmin_id'])->order(['modifytime'=>'DESC'])->limit('0,1')->fetch();
		
		$column[] = ['key'=>_('Modify Time'), 'value'=>$m_weight['modifytime']];
		
		$column[] = ['key'=>_('Modify Admin Name'), 'value'=>adminModel::getOne($m_weight['modifyadmin_id'])['name']];
		
		list ($html, $js) = $Html->submit('value="'._('Submit').'"');
		$column[] = ['key'=>'&nbsp;', 'value'=>$html];
		$Html->set_js($js);
		
		list ($html, $js) = $Html->table('class="table"', $column, $extra);
		$a_tabs[0] = ['href'=>'#tabs-0', 'name'=>_('Form'), 'value'=>$html];
		$Html->set_js($js);
		
		list ($html, $js) = $Html->tabs($a_tabs);
		$formcontent = $html;
		$Html->set_js($js);
		
		list($html, $js) = $Html->form('id="form"', $formcontent);
		parent::$data['form'] = $html;
		$Html->set_js($js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
}