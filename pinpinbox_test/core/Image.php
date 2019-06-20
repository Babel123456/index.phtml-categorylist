<?php
/**
 * Image 處理器
 * <p>v1.0 2015-06-29</p>
 * @author lion
 */

namespace Core;

class Image
{
	private
		$attr,
		$exif,
		$height,
		$height_target,
		$image,
		$image_target,
		$imagick,
		$quality,
		$quality_target,
		$type,//1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP，7 = TIFF(intel byte order)，8 = TIFF(motorola byte order)，9 = JPC，10 = JP2，11 = JPX，12 = JB2，13 = SWC，14 = IFF，15 = WBMP，16 = XBM
		$type_correspond = [1 => 'gif', 2 => 'jpg', 3 => 'png', 6 => 'bmp'],
		$type_target,
		$width,
		$width_target;

	public static
		$usableOfImagick = false;

	function __construct()
	{
		if (self::$usableOfImagick  ) {
			$this->imagick = new \Imagick();

			$this->imagick->setResolution(108, 108);//max 300, 300; must be called before loading or creating an image.
		}
	}

	function __destruct()
	{
		if (self::$usableOfImagick) {
			$this->imagick->clear();
		}
	}

	/**
	 * 取得圖檔屬性
	 * @return string
	 */
	function getAttr()
	{
		return $this->attr;
	}

	/**
	 * 取得圖檔高度
	 * @return integer
	 */
	function getHeight()
	{
		return $this->height;
	}

	/**
	 * 取得圖檔目標高度
	 * @return integer
	 */
	function getHeightTarget()
	{
		return $this->height_target;
	}

	/**
	 * 取得圖檔品質
	 * @return int
	 */
	function getQuality()
	{
		if ($this->quality === null) $this->quality = shell_exec('identify -format %Q ' . escapeshellarg($this->image));

		return $this->quality;
	}

	/**
	 * 取得原圖檔完整路徑
	 * @param string $file
	 * @return string
	 */
	function getSource($file)
	{
		$pathinfo = pathinfo($file);

		$tmp0 = explode('_', $pathinfo['filename']);

		if (isset($tmp0[1])) {
			$file = $pathinfo['dirname'] . (is_url($file) ? '/' : DIRECTORY_SEPARATOR) . preg_replace('/(_[0-9]+x[0-9]+)$/i', '', $pathinfo['filename']) . '.' . $pathinfo['extension'];
		}

		return $file;
	}

	/**
	 * 取得主要 Hex
	 * @return mixed
	 * @throws \Exception
	 */
	function getMainHex()
	{
		$widthRate = (float)$this->width / 16;
		$heightRate = (float)$this->height / 16;

		$rate = ($widthRate > $heightRate) ? $widthRate : $heightRate;

		$width = round($this->width / $rate);
		$height = round($this->height / $rate);

		$im_new = imagecreatetruecolor($width, $height);

		switch ($this->type) {
			case 1:
				$im_source = imagecreatefromgif($this->image);
				break;

			case 2:
				$im_source = imagecreatefromjpeg($this->image);
				break;

			case 3:
				$im_source = imagecreatefrompng($this->image);

				imagecolortransparent($im_new, imagecolorallocatealpha($im_new, 0, 0, 0, 127));
				imagealphablending($im_new, false);
				imagesavealpha($im_new, true);
				break;

			case 6:
				$im_source = imagecreatefromwbmp($this->image);
				break;

			default:
				//^ 用 imagecreatefromstring ?
				throw new \Exception('Unknown case');
				break;
		}

		imagecopyresampled($im_new, $im_source, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

		$area = $width * $height;
		$histogram = [];

		for ($i = 0; $i < $width; ++$i) {
			for ($j = 0; $j < $height; ++$j) {
				// get the rgb value for current pixel
				$rgb = ImageColorAt($im_new, $i, $j);

				// extract each value for r, g, b
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;

				// get the Value from the RGB value
				$v = round(($r + $g + $b) / 3);

				// add the point to the histogram
				if (!isset($histogram[$v])) $histogram[$v] = 0;

				$histogram[$v] += $v / $area;
				$histogram_color[$v] = rgb2hex($r, $g, $b);
			}
		}

		unset($histogram[255]);//2017-05-12 Lion: 255 目前都會取成白色，因此排除

		return $histogram_color[array_search(max($histogram), $histogram)];
	}

	/**
	 * 取得圖檔類型
	 * @return string
	 */
	function getType()
	{
		return $this->type;
	}

	/**
	 * 取得圖檔寬度
	 * @return integer
	 */
	function getWidth()
	{
		return $this->width;
	}

	/**
	 * 取得圖檔目標寬度
	 * @return integer
	 */
	function getWidthTarget()
	{
		return $this->width_target;
	}

	/**
	 * 儲存圖檔
	 * @param null $image_target
	 * @param bool $overwrite
	 * @param bool $delete
	 * @param bool $suffix
	 * @return null|string
	 * @throws \Exception
	 */
	function save($image_target = null, $overwrite = false, $delete = false, $suffix = true)
	{
		if ($this->image === null) {
			goto _return;
		}

		if ($image_target === null) {
			$pathinfo = pathinfo($this->image);

			$filename = ($suffix && ($this->width_target != $this->width || $this->height_target != $this->height)) ? $pathinfo['filename'] . '_' . $this->width_target . 'x' . $this->height_target : $pathinfo['filename'];

			$this->image_target = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $filename . '.' . $this->type_correspond[$this->type_target];
		} else {
			$this->image_target = $image_target;
		}

		if (!is_file($this->image_target) || $overwrite) {
			mkdir_p_v2(pathinfo($this->image_target, PATHINFO_DIRNAME));

			if (self::$usableOfImagick) {
				$this->setCorrect();

				if ($this->type_target == 2) {
					$this->imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);

					if ($this->quality_target !== null && $this->quality_target != $this->getQuality()) $this->imagick->setImageCompressionQuality($this->quality_target);

					$this->imagick->setInterlaceScheme(\Imagick::INTERLACE_PLANE);//參考 http://stackoverflow.com/questions/7261855/recommendation-for-compressing-jpg-files-with-imagemagick
				}

				switch ($this->type_target) {
					case 1:
						$this->imagick = $this->imagick->coalesceImages();

						foreach ($this->imagick as $frame) {
							$frame->thumbnailImage($this->width_target, $this->height_target);
							$frame->setImagePage($this->width_target, $this->height_target, 0, 0);
						}

						$this->imagick = $this->imagick->deconstructImages();

						$this->imagick->writeImages($this->image_target, true);
						break;

					default:
						$this->imagick->writeImage($this->image_target);
						break;
				}
			} else {
				switch ($this->type) {
					case 1:
						$im_source = imagecreatefromgif($this->image);
						break;

					case 2:
						$im_source = imagecreatefromjpeg($this->image);

						if (isset($this->exif['Orientation'])) {
							//翻轉來源圖像
							switch ($this->exif['Orientation']) {
								case 0: // undefined?
								case 1: // nothing
								case 65536 :// 170704 Mantis #1396 : 處理圖片得到65536造成異常, 日後取得此型態不進行圖片翻轉
									break;

								case 2: // horizontal flip
									imageflip($im_source, 1);
									break;

								case 3: // 180 rotate left
									$im_source = imagerotate($im_source, 180, 0);
									break;

								case 4: // vertical flip
									imageflip($im_source, 2);
									break;

								case 5: // vertical flip + 90 rotate right
									imageflip($im_source, 2);
									$im_source = imagerotate($im_source, -90, 0);
									break;

								case 6: // 90 rotate right
									$im_source = imagerotate($im_source, -90, 0);
									break;

								case 7: // horizontal flip + 90 rotate right
									imageflip($im_source, 1);
									$im_source = imagerotate($im_source, -90, 0);
									break;

								case 8: // 90 rotate left
									$im_source = imagerotate($im_source, 90, 0);
									break;

								//170705 Mantis #1396 : 處理圖片得到65536 / 65537 造成異常, 日後取得此型態不進行圖片翻轉及拋出異常
								default:
									\userlogModel::setExceptionV2(\Lib\Exception::LEVEL_NOTICE, 'Unknown case');
									break;
							}
						}
						break;

					case 3:
						$im_source = imagecreatefrompng($this->image);
						break;

					case 6:
						$im_source = imagecreatefromwbmp($this->image);
						break;

					default:
						//^ 用 imagecreatefromstring ?
						\userlogModel::setExceptionV2(\Lib\Exception::LEVEL_NOTICE, 'Unknown case');
						break;
				}

				/**
				 * 創建圖像標識符
				 * 2014-08-01: 應該不是判斷圖檔類型來使用 imagecreate 或 imagecreatetruecolor, 而是要判斷圖色構成(方法尋找中..)
				 */
				$im_new = imagecreatetruecolor($this->width_target, $this->height_target);
				switch ($this->type_target) {
					case 1:
					case 3:
						imagecolortransparent($im_new, imagecolorallocatealpha($im_new, 0, 0, 0, 127));
						imagealphablending($im_new, false);
						imagesavealpha($im_new, true);
						break;

					case 2:
						break;

					default:
						\userlogModel::setExceptionV2(\Lib\Exception::LEVEL_NOTICE, 'Unknown case');
						break;
				}
				imagecopyresampled($im_new, $im_source, 0, 0, 0, 0, $this->width_target, $this->height_target, $this->width, $this->height);

				//輸出目標圖像
				switch ($this->type_target) {
					case 1:
						imagegif($im_new, $this->image_target);
						break;

					case 2:
						call_user_func_array('imagejpeg', ($this->quality_target !== null && $this->quality_target != $this->getQuality()) ? [$im_new, $this->image_target, $this->quality_target] : [$im_new, $this->image_target]);
						imageinterlace($im_new, 1);
						break;

					case 3:
						imagepng($im_new, $this->image_target);
						break;

					case 6:
						imagewbmp($im_new, $this->image_target);
						break;

					default:
						\userlogModel::setExceptionV2(\Lib\Exception::LEVEL_NOTICE, 'Unknown case');
						break;
				}

				//釋放圖像內存
				imagedestroy($im_source);
				imagedestroy($im_new);
			}
		}

		if ($delete && $this->getSource($this->image_target) != $this->getSource($this->image)) {
			$pathinfo = pathinfo($this->image);

			\Core\File::delete(glob($pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'] . '*.' . $pathinfo['extension']));
		}

		_return:

		return $this->image_target;
	}

	/**
	 * 設置圖檔
	 * @param string $image : 圖檔完整路徑
	 * @param boolean $source : 是否尋回原圖檔進行處理
	 * @return \Core\Image
	 */
	function set($image, $source = true)
	{
		if (is_url($image)) {
			if (gethttpcode($image) != 200) {
				\userlogModel::setExceptionV2(\Lib\Exception::LEVEL_NOTICE, '"' . $image . '" is not a file.');

				goto _return;
			}
		} else {
			if (!is_file($image)) {
				\userlogModel::setExceptionV2(\Lib\Exception::LEVEL_NOTICE, '"' . $image . '" is not a file.');

				goto _return;
			}
		}

		if ($source) $image = $this->getSource($image);

		if (self::$usableOfImagick) {
			try {
				is_url($image) ? $this->imagick->readImageBlob(file_get_contents($image)) : $this->imagick->readimage($image);
			} catch (\ImagickException $exception) {
				\userlogModel::setExceptionV2(\Lib\Exception::LEVEL_NOTICE, '"' . $image . '" is not an image, ' . $exception->getMessage());

				goto _return;
			}
		}

		$this->image_target = $this->image = $image;

		list ($this->width, $this->height, $this->type, $this->attr) = getimagesize($this->image);

		/**
		 * 2014-07-29:
		 *     有些圖像會有無法解讀的 exif, 因此用 @ 屏蔽
		 * 2014-05-01:
		 *     處理數位圖像翻轉的情況，其中正負 90 度(以及其倍數)翻轉的圖像，用 getimagesize 取得的 $width 和 $height 會和實際相反，因此對換；
		 *     另外 exif_read_data 僅能支持 JPEG、TIFF
		 */
		if (in_array($this->type, [2, 8])) {
			$this->exif = @exif_read_data($this->image);
		}

		if (isset($this->exif['Orientation'])) {
			//width, height 互換
			switch ($this->exif['Orientation']) {
				case 5:
				case 6:
				case 7:
				case 8:
					$tmp0 = $this->height;
					$this->height = $this->width;
					$this->width = $tmp0;
					break;
			}
		}

		$this->width_target = $this->width;
		$this->height_target = $this->height;
		$this->type_target = $this->type;

		_return:

		return $this;
	}

	/**
	 * 將檔案方向轉正
	 */
	protected function setCorrect()
	{
		if (self::$usableOfImagick) {
			if ($this->getType() == 2 && isset($this->exif['Orientation'])) {
				switch ($this->exif['Orientation']) {
					case 0: // undefined?
					case 1: // nothing
						break;

					case 2: // horizontal flip
						$this->imagick->flopImage();
						break;

					case 3: // 180 rotate left
						$this->imagick->rotateImage(new \ImagickPixel(), 180);
						break;

					case 4: // vertical flip
						$this->imagick->flipImage();
						break;

					case 5: // vertical flip + 90 rotate right
						$this->imagick->flipImage();
						$this->imagick->rotateImage(new \ImagickPixel(), 90);
						break;

					case 6: // 90 rotate right
						$this->imagick->rotateImage(new \ImagickPixel(), 90);
						break;

					case 7: // horizontal flip + 90 rotate right
						$this->imagick->flopImage();
						$this->imagick->rotateImage(new \ImagickPixel(), 90);
						break;

					case 8: // 90 rotate left
						$this->imagick->rotateImage(new \ImagickPixel(), -90);
						break;

					default:
						\userlogModel::setExceptionV2(\Lib\Exception::LEVEL_NOTICE, 'Unknown case. "' . $this->exif['Orientation'] . '" of orientation by "' . $this->image_target . '".');
						break;
				}

				$this->imagick->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);

				$this->exif['Orientation'] = \Imagick::ORIENTATION_TOPLEFT;//2017-08-17 Lion: 這行很重要，因為轉正了所以要更新，才不會再次翻轉
			}
		}
	}

	/**
	 * 設置圖檔品質(目前僅支持 jpg)
	 * @param $quality
	 * @return $this
	 */
	function setQuality($quality)
	{
		$this->quality_target = $quality;

		return $this;
	}

	/**
	 * 設置圖檔寬、高
	 * @param number $width : 寬
	 * @param number $height : 高
	 * @param boolean $forced : 是否強制縮放為指定寬高
	 * @return \Core\Image
	 */
	function setSize($width = 100, $height = 100, $forced = false)
	{
		if ($this->image === null) goto _return;

		if ($this->width != $width || $this->height != $height) {
			if (!$forced) {
				$w_rate = $this->width / $width;
				$h_rate = $this->height / $height;

				$rate = ($w_rate > $h_rate) ? $w_rate : $h_rate;

				$width = round($this->width / $rate);
				$height = round($this->height / $rate);
			}

			$this->width_target = $width;
			$this->height_target = $height;

			if (self::$usableOfImagick) {
				$this->setCorrect();//2017-08-17 Lion: 轉正需要在 resize 之前

				$this->imagick->resizeImage($this->width_target, $this->height_target, \Imagick::FILTER_CATROM, 1);
			}
		}

		_return:

		return $this;
	}

	/**
	 * 設置圖檔類型
	 * @param $type
	 * @return $this
	 */
	function setType($type)
	{
		if ($this->image === null) goto _return;

		//如果 type 有更動，留意 imagick->setImageFormat 的部分也要處理
		switch ($s_type = strtolower($type)) {
			case 'gif':
				$type = 1;
				break;

			case 'jpg':
			case 'jpeg':
				$type = 2;
				break;

			case 'png':
				$type = 3;
				break;

			case 'bmp':
				$type = 6;
				break;

			default:
				\userlogModel::setExceptionV2(\Lib\Exception::LEVEL_NOTICE, 'Unknown case. "' . $s_type . '" of type.');
				break;
		}

		$this->type_target = $type;

		if (self::$usableOfImagick) {
			$this->imagick->setImageFormat($this->type_correspond[$this->type_target]);
		}

		_return:

		return $this;
	}

	/**
	 * 轉pdf為圖檔
	 * @param string $file
	 * @param array $user
	 * @param int $albumLeft
	 * @return array [boolean $result, array $filename]
	 */
	function setPdftoImage($file, $user, $albumLeft)
	{
		$return = false;
		$message = _('PDF 轉檔失敗, 請聯絡管理員');
		$a_filename = [];
		if (class_exists('Imagick')) {
			$imagick = new \Imagick();
			if ($albumLeft != 0) {
				$pdfPages = getPDFPages($file);

				if (!$pdfPages) {
                    $return = 0;
                    $message = _('處理pdf檔案失敗，請聯絡 pinpinbox。');
                } else if ($pdfPages <= $albumLeft) {
					$dir = M_PACKAGE . '/template/' . date('Ymd') . '/' . $user['user_id'] . '/fast_upload/';
					$splitCount = ($albumLeft > $pdfPages) ? ($pdfPages - 1) : ($albumLeft - 1);
					for ($i = 0; $i <= $splitCount; $i++) {
						$_filename = uniqid() . '.jpg';
						$a_filename[] = $_filename;
						$outPutFile = PATH_UPLOAD . $dir . $_filename;
						exec('convert -density 200 ' . $file . '[' . $i . '-' . $i . '] ' . $outPutFile);
					}
					$return = true;
				} else {
					$return = 2;
					$message = _('可用相片剩餘數量不足<p>請重新上傳</p><br>');
				}
			} else {
				$return = 2;
				$message = _('可用相片剩餘數量不足<p>請重新上傳</p><br> ');
			}
		} else {
			$message = _('Imagick 初始化失敗, 請聯絡管理員');
		}

		return [$return, $message, $a_filename];
	}
}