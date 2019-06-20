<?php
class newsarea_newsController extends backstageController {
	function __construct() {}
	
	function index() {
		list($jqgrid_html, $jqgrid_js) = parent::$html->jqgrid();
		parent::$data['index'] = $jqgrid_html;
		parent::$html->set_js($jqgrid_js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
	
	function form() {
		if (is_ajax()) {
			//form
			$sequence = $_POST['sequence'];
			
			switch ($_GET['act']) {
				//新增
				case 'add':
					$newsarea_id = $_POST['newsarea_id'];
					$news_id = $_POST['news_id'];
					
					if (Core::model(M_CLASS)->get(sql_select_encode(null, null, array(array('newsarea_id', '=', $newsarea_id), array('news_id', '=', $news_id))))) {
						json_encode_return(0, _('Data already exists.'));
					}
					
					//form
					$param = array();
					$param['newsarea_id'] = $newsarea_id;
					$param['news_id'] = $news_id;
					$param['sequence'] = $sequence;
					
					Core::model(M_CLASS)->add($param);
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
		
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					//form
					$param = array();
					$param['sequence'] = $sequence;
					
					Core::model(M_CLASS)->edit($M_CLASS_id, $param);
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-form
		$newsarea_id = null;
		$news_id = null;
		$sequence = 255;
		$inserttime = null;
		$modifytime = null;
		$modifyadmin_name = null;
		
		//table-form
		$column = array();
		$extra = null;
		
		//tabs
		$a_tabs = array();
		
		//form for add or edit
		switch ($_GET['act']) {
			//新增
			case 'add':
				//newsarea
				if (isset($_GET['newsarea_id'])) {
					$m_newsarea = Core::model('newsarea')->get(sql_select_encode(null, null, array(array('newsarea_id', '=', (int)$_GET['newsarea_id']))));
					$newsarea_id = $m_newsarea['newsarea_id'];
					$newsarea_name = $m_newsarea['name'];
					list($html, $js) = parent::$html->hidden('id="newsarea_id" name="newsarea_id" value="'.$newsarea_id.'"', parent::get_area_level_format_string('newsarea', $newsarea_id));
				} else {
					$m_newsarea = Core::model('newsarea')->get(sql_select_encode(null, null, null, null, null, array('newsarea_id'=>'asc')), 'fetchAll');
					$tmp2 = array();
					foreach ($m_newsarea as $v1) {
						$tmp2[$v1['newsarea_id']] = parent::get_area_level_format_string('newsarea', $v1['newsarea_id']);
					}
					asort($tmp2);
					list($html, $js) = parent::$html->select2('id="newsarea_id" name="newsarea_id"', $tmp2);
				}
				$html_newsarea = $html;
				parent::$html->set_js($js);
				
				//news
				$m_news = Core::model('news')->get(null, 'fetchAll');
				$tmp2 = array();
				foreach ($m_news as $v1) {
					$tmp2[$v1['news_id']] = $v1['name'];
				}
				asort($tmp2);
				list($html, $js) = parent::$html->select2('id="news_id" name="news_id"', $tmp2);
				$html_news = $html;
				parent::$html->set_js($js);
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'add'));
				break;
		
			//修改
			case 'edit':
				if (!empty($_GET)) {
					$M_CLASS_id = $_GET[M_CLASS.'_id'];
					
					$m_newsarea_news = Core::model(M_CLASS)->get(sql_select_encode(null, null, array(array(M_CLASS.'_id', '=', $M_CLASS_id))));
					
					//form
					$sequence = $m_newsarea_news['sequence'];
					$inserttime = $m_newsarea_news['inserttime'];
					$modifytime = $m_newsarea_news['modifytime'];
					$modifyadmin_name = parent::get_admin_name_by_admin_id($m_newsarea_news['modifyadmin_id']);
					
					//newsarea
					$m_newsarea = Core::model('newsarea')->get(sql_select_encode(null, array(array('left join', 'newsarea_news', 'using(newsarea_id)')), array(array(M_CLASS.'.'.M_CLASS.'_id', '=', $M_CLASS_id))));
					if (!empty($m_newsarea)) {
						$newsarea_id = $m_newsarea['newsarea_id'];
						$newsarea_name = $m_newsarea['name'];
					}
						
					//news
					$m_news = Core::model('news')->get(sql_select_encode(null, array(array('left join', 'newsarea_news', 'using(news_id)')), array(array(M_CLASS.'.'.M_CLASS.'_id', '=', $M_CLASS_id))));
					if (!empty($m_news)) {
						$news_id = $m_news['news_id'];
						$news_name = $m_news['name'];
					}
				}
				
				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);
				
				$html_newsarea = parent::get_area_level_format_string('newsarea', $newsarea_id);
				$html_news = $news_name;
				
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('newsarea'), 'value'=>$html_newsarea);
		
		$column[] = array('key'=>parent::get_adminmenu_name_by_class('news'), 'value'=>$html_news);
		
		list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="'.$sequence.'" min="0" max="255" required');
		$column[] = array('key'=>_('Sequence'),'value'=>$html);
		parent::$html->set_js($js);
		
		$column[] = array('key'=>_('Insert Time'), 'value'=>$inserttime);
		
		$column[] = array('key'=>_('Modify Time'), 'value'=>$modifytime);
		
		$column[] = array('key'=>_('Modify Admin Name'), 'value'=>$modifyadmin_name);
		
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
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view('admin', M_CLASS, M_FUNCTION);
	}
	
	function delete() {
		if (!empty($_POST)) {
			Core::model(M_CLASS)->delete(array(array(M_CLASS.'_id', '=', $_POST[M_CLASS.'_id'])));
			json_encode_return(1, _('Success'));
		}
		die;
	}
	
	function json() {
		if (!class_exists('db')) include PATH_ROOT.'lib/db.php';
		$db = new db(Core::$_config['CONFIG']['DB']['site']);
		
		//取得條件
		$page = $_REQUEST['page'];
		$limit = $_REQUEST['rows'];
		$sidx = $_REQUEST['sidx'];
		$sord = $_REQUEST['sord'];
		$totalrows = isset($_REQUEST['totalrows'])? $_REQUEST['totalrows']: false;
		
		//其它條件
		$extra1 = null;
		$extra2 = null;
		if (isset($_GET['newsarea_id'])) {
			$extra1 = M_CLASS.'.newsarea_id = '.$db->quote((int)$_GET['newsarea_id']).' and';
			$extra2 = 'where '.M_CLASS.'.newsarea_id = '.$db->quote((int)$_GET['newsarea_id']);
		}
		
		//組 where
		$where = null;
		if (!empty($_REQUEST['filters'])) {
			$filters = json_decode($_REQUEST['filters'], true);
			$groupOp = $filters['groupOp'];
			$rules = $filters['rules'];
			if (!empty($rules)) {
				$tmp = array();
				foreach ($rules as $v) {
					$field = $v['field'];
					$data = $v['data'];
					$tmp[] = $field.' like '.$db->quote($data.'%');
				}
				$where = 'where '.$extra1.' '.implode(' '.$groupOp.' ', $tmp);
			} else {
				$where = $extra2;
			}
		} else {
			$where = $extra2;
		}
		
		//總筆數
		$sql = "SELECT count(".M_CLASS.".newsarea_id)
			FROM ".M_CLASS."
			".$where;
		$count = $db->fetchColumn($sql);
		
		//條件
		if ($totalrows) $limit = $totalrows;
		if (!$sidx) $sidx = 1;
		$total_pages = ($count > 0)? ceil($count / $limit) : 0;
		if ($page > $total_pages) $page = $total_pages;
		if ($limit < 0) $limit = 0;
		$start = $limit * $page - $limit;
		if ($start < 0) $start = 0;
		
		//data
		$sql = "SELECT ".M_CLASS.".".M_CLASS."_id, ".M_CLASS.".newsarea_id, ".M_CLASS.".news_id, ".M_CLASS.".sequence, ".M_CLASS.".modifytime, ".M_CLASS.".modifyadmin_id, ".M_CLASS.".newsarea_id, ".M_CLASS.".news_id
			FROM ".M_CLASS."
			$where ORDER BY $sidx $sord LIMIT $start, $limit";
		$fetchAll = $db->fetchAll($sql);
		$response = array();
		$response['page'] = $page;
		$response['total'] = $total_pages;
		$response['records'] = $count;
		foreach ($fetchAll as $k => &$v) {
			$v['newsarea_id'] = parent::get_grid_display('newsarea_id', $v['newsarea_id']);
			
			$v['news_id'] = parent::get_grid_display('news_id', $v['news_id']);
			
			$v['modifyadmin_id'] = parent::get_admin_name_by_admin_id($v['modifyadmin_id']);
			
			$tmp = array_values($v);
			$v = array();
			$v['cell'] = $tmp;
		}
		$response['rows'] = $fetchAll;
		
		die(json_encode($response));
	}
	
	function jqgrid_edit() {
		if (isset($_REQUEST['oper'])) {
			$oper = $_REQUEST['oper'];
			switch ($oper) {
				case 'edit':
					$M_CLASS_id = $_REQUEST[M_CLASS.'_id'];
					$celname = $_REQUEST['celname'];
					$value = $_REQUEST['value'];
						
					Core::model(M_CLASS)->edit($M_CLASS_id, array($celname=>$value));
					break;
						
				default:
					break;
			}
		}
		die;
	}
}