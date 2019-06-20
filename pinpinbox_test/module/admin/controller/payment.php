<?php

class paymentController extends backstageController
{
    function __construct()
    {
    }

    function index()
    {
        list($html, $js) = parent::$html->grid();
        parent::$data['index'] = $html;
        parent::$html->set_js($js);

        parent::headbar();
        parent::footbar();
        parent::jquery_set();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function form()
    {
        if (is_ajax()) {
            $remark = $_POST['remark'];
            $state = $_POST['state'];

            switch ($_GET['act']) {
                //修改
                case 'edit':
                    $M_CLASS_id = $_POST[M_CLASS . '_id'];

                    $edit = array(
                        'remark' => $remark,
                        'state' => $state,
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    );
                    Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', $M_CLASS_id)), 'and')))->edit($edit);

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;
            }

            die;
        }

        //初始值-form
        $date = date('Y-m', strtotime(date('Y-m-d') . '-1 month'));
        $remark = null;
        $state = 'pretreat';
        $inserttime = null;
        $modifytime = null;
        $modifyadmin_name = null;

        //form
        $column = array();
        $extra = null;

        //form for add or edit
        switch ($_GET['act']) {
            //修改
            case 'edit':
                if (!empty($_GET)) {
                    $M_CLASS_id = $_GET[M_CLASS . '_id'];

                    $m_payment = Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', $M_CLASS_id)), 'and')))->fetch();

                    $date = $m_payment['date'];
                    $remark = $m_payment['remark'];
                    $state = $m_payment['state'];
                    $inserttime = $m_payment['inserttime'];
                    $modifytime = $m_payment['modifytime'];
                    $modifyadmin_name = adminModel::getOne($m_payment['modifyadmin_id'])['name'];
                }

                list($html, $js) = parent::$html->hidden('id="' . M_CLASS . '_id" name="' . M_CLASS . '_id" value="' . $M_CLASS_id . '"');
                $extra .= $html;
                parent::$html->set_js($js);

                list($html_date, $js) = parent::$html->hidden('id="date" name="date" value="' . $date . '"', $date);
                parent::$html->set_js($js);

                parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'edit'));
                break;
        }

        $column[] = array('key' => _('Date'), 'value' => $html_date, 'key_remark' => '預處理[收益]資料的月份，最新僅到上一個月');

        list($html, $js) = parent::$html->textarea('id="remark" name="remark" style="width:400px; height:100px; font-size:14px;"', htmlspecialchars($remark));
        $column[] = array('key' => _('Remark'), 'value' => $html);
        parent::$html->set_js($js);

        $a_state = array();
        foreach (json_decode(Core::settings('PAYMENT_STATE'), true) as $k0 => $v0) {
            $a_state[] = array(
                'name' => 'state',
                'value' => $k0,
                'text' => $v0,
            );
        }
        list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_state, $state);
        $column[] = array('key' => _('State'), 'value' => $html);
        parent::$html->set_js($js);

        $column[] = array('key' => _('Insert Time'), 'value' => $inserttime);

        $column[] = array('key' => _('Modify Time'), 'value' => $modifytime);

        $column[] = array('key' => _('Modify Admin Name'), 'value' => $modifyadmin_name);

        list($html0, $js0) = parent::$html->submit('value="' . _('Submit') . '"');
        list($html1, $js1) = parent::$html->back('value="' . _('Back') . '"');
        $column[] = array('key' => '&nbsp;', 'value' => $html0 . '&emsp;' . $html1);
        parent::$html->set_js($js0 . $js1);

        list($html, $js) = parent::$html->table('class="table"', $column, $extra);
        $a_tabs[0] = array('href' => '#tabs-0', 'name' => _('Form'), 'value' => $html);
        parent::$html->set_js($js);

        //income
        $column = array();
        $extra = null;

        list($html, $js) = parent::$html->grid();
        $column[] = array('key' => parent::get_adminmenu_name_by_class('income'), 'value' => $html);
        parent::$html->set_js($js);
        parent::$data[M_CLASS . '_id'] = empty($M_CLASS_id) ? '[]' : $M_CLASS_id;

        list($html, $js) = parent::$html->table('class="table"', $column, $extra);
        $a_tabs[1] = array('href' => '#tabs-1', 'name' => parent::get_adminmenu_name_by_class('income'), 'value' => $html);
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

    function delete()
    {
        die;
    }

    function json()
    {
        $response = array();

        $case = isset($_POST['case']) ? $_POST['case'] : null;

        switch ($case) {
            default:
                //column
                $column = array(
                    M_CLASS . '_id',
                    'date',
                    'remark',
                    'state',
                    'modifytime',
                );

                list($where, $group, $order, $limit) = parent::grid_request_encode();

                //data
                $fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
                foreach ($fetchAll as &$v0) {
                    $v0['remark'] = nl2br(htmlspecialchars($v0['remark']));
                    $v0['incomeX'] = Model('income')->column(array('count(1)'))->where(array(array(array(array(M_CLASS . '_id', '=', $v0[M_CLASS . '_id'])), 'and')))->fetchColumn();
                }
                $response['data'] = $fetchAll;

                //total
                $response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
                break;

            case 'income':
                //column
                $column = array(
                    'income_id',
                    'user_id',
                    'payment_id',
                    'total',
                    'currency',
                    'remittance',
                    'remittance_info',
                    'state',
                    'modifytime',
                );

                list($where, $group, $order, $limit) = parent::grid_request_encode();

                //data
                $fetchAll = Model('income')->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
                foreach ($fetchAll as &$v0) {
                    $v0['remittance_info'] = parent::grid_json_decode($v0['remittance_info']);
                    $v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
                    $v0['paymentX'] = parent::get_grid_display('payment', $v0['payment_id']);
                    $v0['settlementX'] = Model('settlement')->column(array('count(1)'))->where(array(array(array(array('income_id', '=', $v0['income_id'])), 'and')))->fetchColumn();
                }
                $response['data'] = $fetchAll;

                //total
                $response['total'] = Model('income')->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
                break;
        }

        die(json_encode($response));
    }

    function excel()
    {
        $payment_id = empty($_GET['payment_id']) ? null : $_GET['payment_id'];

        $file = \paymentModel::cerateExcel($payment_id);

        $fp = fopen($file, 'rb');

        //export
        header('Cache-Control: max-age=0');
        header('Content-Disposition: attachment;filename="' . pathinfo($file, PATHINFO_BASENAME) . '"');
        header("Content-Length: " . filesize($file));
        header('Content-Type: application/vnd.ms-excel');

        fpassthru($fp);

        die;
    }
}