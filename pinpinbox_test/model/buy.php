<?php
class buyModel extends Model {
	protected $database = 'cashflow';
	protected $table = 'buy';
	protected $memcache = 'cashflow';
	protected $join_table = [];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = [
				['class'=>__CLASS__],
		];
	
		return $return;
	}
	
	function usable($buy_id) {
		$result = 1;
		$message = null;
		
		if (empty($buy_id)) {
			$result = 0;
			$message = _('Buy ID is empty.');
			goto _return;
		}
		
		$m_buy = Model('buy')->column(['act'])->where([[[['buy_id', '=', $buy_id]], 'and']])->fetch();
		if (empty($m_buy)) {
			$result = 0;
			$message = _('Buy does not exist.');
			goto _return;
		}
		
		if ($m_buy['act'] != 'open') {
			$result = 0;
			$message = _('Buy is not open.');
			goto _return;
		}
		
		_return: return array_encode_return($result, $message);
	}
}