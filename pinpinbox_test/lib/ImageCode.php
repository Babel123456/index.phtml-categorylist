<?php
/*
 * 驗證碼生成
 * 
 * @auhtor cluries
 * @link http://cuies.com
 * @date 2010-09-30
 */

class ImageCode {
	
	/**
	 * 验证码类型
	 * 
	 * @var string number|string|symbol
	 */
	private $_type = 'number|string';
	
	private $_length = 4;
	
	private $_width = 80;
	
	private $_height = 30;
	
	private $_imageType = 'png';
	
	private $_color;
	
	private $_background;
	
	private $_code = null;
	
	private $_image;
	
	private $_level = 4;
	
	private $_font = null;
	
	function __construct($length = 4) {
		$this->_length = intval ( $length );
		$this->init ();
	}
	
	private function init() {
		$this->_color = new RGB ( 0, 0, 0 );
		$this->_background = new RGB ( 255, 255, 255 );
	}
	
	public function setType($type) {
		
		$this->_type = $type;
	}
	
	private function randomCode() {
		if (empty ( $this->_type )) {
			throw new Exception ( 'ImageCode::Type is musted!' );
		}
		
		$seed = array ();
		$seed ['number'] = str_split ( '23456789' );
		$seed ['string'] = str_split ( 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ' );
		$seed ['symbol'] = str_split ( '<>?@!#$%^&*+=' );
		
		$typeSeed = array ();
		$dict = explode ( '|', $this->_type );
		foreach ( $seed as $key => $value ) {
			if (in_array ( $key, $dict )) {
				$typeSeed = array_merge ( $typeSeed, $value );
			}
		}
		
		return $typeSeed [array_rand ( $typeSeed )];
	
	}
	
	public function createImage() {
		$image = imagecreatetruecolor ( $this->_width, $this->_height );
		$background = $this->imageColorAllocate ( $image, $this->_background );
		$backgroundInverted = $this->imageColorAllocate ( $image, $this->_background->getInverted () );
		$color = $this->imageColorAllocate ( $image, $this->_color );
		$similar = $this->imageColorAllocate ( $image, $this->_color->getSimilar () );
		
		//fill background
		imagefill ( $image, 0, 0, $background );
		
		$style = array ($background, $backgroundInverted, $color, $similar );
		$style = array_merge ( $style, $style, $style, $style );
		$style = array_merge ( $style, $style );
		shuffle ( $style );
		
		imagesetstyle ( $image, $style );
		//random line
		for($i = 0; $i < $this->_level; $i ++) {
			imageline ( $image, rand ( 0, $this->_width ), rand ( 0, $this->_height ), rand ( 0, $this->_width ), rand ( 0, $this->_height ), IMG_COLOR_STYLED );
		}
		
		//random point or char
		$loopNumber = $this->_level * 2;
		for($i = 0; $i < $loopNumber; $i ++) {
			imagechar ( $image, 1, rand ( 0, $this->_width ), rand ( 0, $this->_height ), $this->randomCode (), $this->imageColorAllocate ( $image, RGB::getRandom () ) );
		}
		
		return $this->writeCodeInImage ( $image, $color );
	}
	
	private function writeCodeInImage(&$image, &$color) {
		//write code
		$xOffset = rand ( 0, 10 );
		if (empty ( $this->_code )) {
			$this->preImage ();
		}
		for($i = 0; $i < $this->_length; $i ++) {
			$writeCode = $this->_code [$i];
			if (empty ( $this->_font )) {
				$yOffset = rand ( 0, $this->_height - 20 );
				imagechar ( $image, 8, $xOffset, $yOffset, $writeCode, $color );
				imagechar ( $image, 8, $xOffset + 1, $yOffset + 1, $writeCode, $color );
			} else {
				$yOffset = rand ( $this->_height - 15, $this->_height );
				imagettftext ( $image, 16, rand ( - 30, 30 ), $xOffset, $yOffset, $color, $this->_font, $writeCode );
			}
			$xOffset += rand ( 10, 20 );
		}
		
		$this->_image = $image;
		return $image;
	}
	
	public function preImage() {
		$this->_code = '';
		for($i = 0; $i < $this->_length; $i ++) {
			$this->_code .= $this->randomCode ();
		}
	}
	
	public function getImage($imageType = null) {
		if (empty ( $imageType )) {
			$imageType = $this->_imageType;
		}
		
		if (empty ( $this->_image )) {
			$this->createImage ();
		}
		
		$func = "image{$imageType}";
		return call_user_func ( $func, $this->_image );
	}
	
	public function printImage() {
		Header ( "Content-type: image/PNG" );
		return $this->getImage ();
	}
	
	private function imageColorAllocate($image, $color) {
		
		if (! ($color instanceof RGB)) {
			throw new Exception ( $this->getExceptionMessage ( 'Color Parameters Error' ) );
			return;
		}
		
		return ImageColorAllocate ( $image, $color->R, $color->G, $color->B );
	}
	
	public function setBackground($background) {
		
		if (! ($background instanceof RGB)) {
			throw new Exception ( $this->getExceptionMessage ( 'Color Parameters Error' ) );
			return;
		}
		
		$this->_background = $background;
	}
	
	public function setColor($color) {
		
		if (! ($color instanceof RGB)) {
			throw new Exception ( $this->getExceptionMessage ( 'Color Parameters Error' ) );
			return;
		}
		
		$this->_color = $color;
	}
	
	public function setImageType($type) {
		
		$type = strtolower ( $type );
		$typeDict = array ('png', 'gif', 'jpg' );
		if (! in_array ( $type, $typeDict )) {
			throw new Exception ( $this->getExceptionMessage ( 'ImageType Parameters Error' ) );
		}
		
		$this->_imageType = $type;
	}
	
	public function setWidth($width) {
		$this->_width = intval ( $width );
	}
	
	public function setHeight($height) {
		$this->_height = intval ( $height );
	}
	
	public function setImageSize($width, $height) {
		$this->_width = intval ( $width );
		$this->_height = intval ( $height );
	}
	
	public function setFont($font) {
		$this->_font = $font;
	}
	
	public function getCode() {
		return $this->_code;
	}
	
	public function setLevel($level) {
		$this->_level = abs ( intval ( $level ) );
	}
	
	private function getExceptionMessage($message) {
		return 'ImageCode::' . $message;
	}
}

class RGB {
	
	public $R;
	
	public $G;
	
	public $B;
	
	public function __construct($r, $g, $b) {
		$this->R = $r;
		$this->G = $g;
		$this->B = $b;
	}
	
	public static function getRandom() {
		return new RGB ( rand ( 0, 255 ), rand ( 0, 255 ), rand ( 0, 255 ) );
	}
	
	public function getInverted() {
		return new RGB ( 255 - $this->R, 255 - $this->G, 255 - $this->B );
	}
	
	public function getSimilar() {
		$r = $this->getSimialarValue ( $this->R );
		$g = $this->getSimialarValue ( $this->G );
		$b = $this->getSimialarValue ( $this->B );
		return new RGB ( $r, $g, $b );
	}
	
	private function getSimialarValue($v) {
		return (255 - $v) > 15 ? 240 - $v : 270 - $v;
	}
}

?>