<?php
/**
 * 简单加密函數
 * （使用範圍：僅對ASCII字符串進行簡單加密解密。）
 * （加密度较低、加密串不变化，但加密后的string短）
 *
 * @author 郭喜領
 * @version $Id:SimpleEncrypt.php, v1.0 2010-12-27 14:39+100 fengye $
 * @package SNSPlus
 * @copyright 2010(C)Mymaji
 */

class Ext_SimpleEncrypt {
	/**
     * 数据加密
     */
	public static function encode($str)
	{
		$str = (string) $str;
		$temp = '';
		for($i=0;$i<strlen($str);$i++)
		{

			switch($i%6)
			{
				case 0:
					$temp.=chr(ord($str{$i})-1);
					break;
				case 1:
					$temp.=chr(ord($str{$i})-5);
					break;
				case 2:
					$temp.=chr(ord($str{$i})-7);
					break;
				case 3:
					$temp.=chr(ord($str{$i})-2);
					break;
				case 4:
					$temp.=chr(ord($str{$i})-4);
					break;
				case 5:
					$temp.=chr(ord($str{$i})-9);
					break;
			}
		}
		$temp = self::base64_url_encode($temp);
		return $temp;
	}
	/*
	替换解密算法
	*/
	public static function decode($str)
	{
		$str = (string) $str;
		$str = self::base64_url_decode($str);
		$temp = '';
		for($i=0;$i<strlen($str);$i++)
		{

			switch($i%6)
			{
				case 0:
					$temp.=chr(ord($str{$i})+1);
					break;
				case 1:
					$temp.=chr(ord($str{$i})+5);
					break;
				case 2:
					$temp.=chr(ord($str{$i})+7);
					break;
				case 3:
					$temp.=chr(ord($str{$i})+2);
					break;
				case 4:
					$temp.=chr(ord($str{$i})+4);
					break;
				case 5:
					$temp.=chr(ord($str{$i})+9);
					break;
			}

		}
		return $temp;
	}
	/**
     * 
     * Base64編碼
     * @param string $input
     */
	public static function base64_url_encode($input) {
		$str = strtr(base64_encode($input), '+/=', '-_.');
		$str = str_replace('.', '', $str); // remove padding
		return $str;
	}
	/**
     * 
     * Base64解碼
     * @param string $input
     */
	public static function base64_url_decode($input) {
		return base64_decode(strtr($input, '-_', '+/'));
	}
}