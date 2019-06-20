<?php
class addressModel extends Model {
	protected $database = 'site';
	protected $table = 'address';
	protected $memcache = 'site';
	protected $join_table = ['addressmapping'];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = [
				['class'=>__CLASS__],
		];
	
		return $return;
	}
	
	function getByCoordinate($coordinate) {
		$result = 0;
		$message = null;
		$data = null;
		
		$coordinate = str_replace(' ', '', $coordinate);
		
		if ($coordinate === '') {
			$message = _('Param error.');
			goto _return;
		}
		
		$is_loop = false;
		
		_again:
		
		$param = [
				'key'=>'AIzaSyD78mmrTmLSYZgjMfCmGIBeyTKa6d5mvv4',
				'language'=>'zh-TW',
				'latlng'=>$coordinate,
				'result_type'=>'country|political|administrative_area_level_1',
		];
		
		$return = json_decode(curl('https://maps.googleapis.com/maps/api/geocode/json', $param, 'get'), true);
		
		//參考 https://developers.google.com/maps/documentation/geocoding/intro?csw=1#reverse-restricted
		switch ($return['status']) {
			case 'OK':
				list($lat, $lng) = explode(',', $coordinate);
				
				//篩選最接近的地理資訊
				$flag = null;
				$address_components = null;
				foreach ($return['results'] as $v0) {
					$a_northeast = $v0['geometry']['bounds']['northeast'];
					$a_southwest = $v0['geometry']['bounds']['southwest'];
					
					$distance = distance($lat, $lng, $a_northeast['lat'], $a_northeast['lng']) + distance($lat, $lng, $a_southwest['lat'], $a_southwest['lng']);
					
					if ($flag === null || $flag > $distance) {
						$flag = $distance;
						$address_components = $v0['address_components'];
					}
				}
				
				$array0 = [];
				foreach ($address_components as $v0) {
					if (in_array('postal_code', $v0['types'])) continue;//2016-04-28 Lion: google 回傳的資訊中, 會以最小地址依序到最大地址, 然後可能再接一筆郵遞區號, 為此反推時將之過濾掉
						
					$array0[] = $v0;
				}
				$a_addressmapping = array_reverse(array_column($array0, 'long_name'));
		
				$address_id_1st = Model('address')->column(['address.address_id'])->join([['inner join', 'addressmapping', 'using(address_id)']])->where([[[['address.level', '=', 0], ['addressmapping.name', '=', $a_addressmapping[0]]], 'and']])->fetchColumn();
				if (empty($address_id_1st)) $address_id_1st = 0;
		
				if (isset($a_addressmapping[1])) {
					$address_id_2nd = Model('address')->column(['address.address_id'])->join([['inner join', 'addressmapping', 'using(address_id)']])->where([[[['address.level', '=', 1], ['addressmapping.name', '=', $a_addressmapping[1]]], 'and']])->fetchColumn();
					if (empty($address_id_2nd)) {
						if (isset($a_addressmapping[2])) {//2016-04-28 Lion: 相容 google map 資訊, ex: 部分 "新竹市" 的第 2 順位資訊是 "臺灣省", 第 3 順位才是 "新竹市"
							$address_id_2nd = Model('address')->column(['address.address_id'])->join([['inner join', 'addressmapping', 'using(address_id)']])->where([[[['address.level', '=', 1], ['addressmapping.name', '=', $a_addressmapping[2]]], 'and']])->fetchColumn();
						}
					}
				}
				if (empty($address_id_2nd)) $address_id_2nd = 0;
		
				$result = 1;
				$message = $return['status'];
				$data = [$address_id_1st, $address_id_2nd];
				goto _return;
				break;
		
			case 'UNKNOWN_ERROR':
				if ($is_loop) {
					$message = 'Infinite loop';
					goto _return;
				} else {
					$is_loop = true;
					goto _again;
				}
				break;
		
			case 'INVALID_REQUEST':
			case 'OVER_QUERY_LIMIT':
			case 'ZERO_RESULTS':
				$message = $return['status'];
				goto _return;
				break;
		}
		
		_return: return array_encode_return($result, $message, null, $data);
	}
}