<?php

namespace z\core;

class Upload
{
	private static $error;
	private static $maxSize = 2; // 单位M
	private static $uploadsDir = ENTRY_PATH . Z_DS . 'uploads' . Z_DS;
	private static $imagesDir = ENTRY_PATH . Z_DS . 'images' . Z_DS;
	private static $waterMark = ENTRY_PATH . Z_DS . 'images' . Z_DS . 'watermark.png';
	private static $fontMark = '字体水印示例';
	private static $fontSize = 20; // 单位px
	private static $fontColor = array(255, 255, 255); // rgb颜色
	private static $waterMarkPlace = 1;
	private static $waterMarkAlpha = 0.4;
	private static $thumbSize = array(320, 375, 425, 480, 640, 768);
	private static $quality = 100;
	private static $_instance;

    private function __construct()
    {
		self::chkDir(self::$uploadsDir);
		self::chkDir(self::$imagesDir);
    }
	
	public static function init()
	{
		if(!isset(self::$_instance))
		{
			$c = __CLASS__;
			self::$_instance = new $c();
		}
		return self::$_instance;
	}
	
	// 检查目录
	private static function chkDir($dir)
	{
		if(!is_dir($dir))
		{
			mkdir($dir);
			chmod($dir, 0777);
		}
	}
	
	// 生成以年月命名的文件
	private static function mkNewDir($dirPrefix)
	{
		$dir = $dirPrefix . date('Ym') . Z_DS;
		self::chkDir($dir);
		return $dir;
	}
	
	// 检查文件类型
	public static function isType($fileType, $type)
	{
		return stripos($fileType, $type) === false ? false : true;
	}
	
	// 检查文件大小
	public static function chkSize($fileSzie)
	{
		return self::$maxSize*1048576 > $fileSzie ? true : false;
	}
	
	// 生成一定格式的唯一文件名
	private static function uniqueName($dir, $suffix)
	{
		$fileName = '';
		while (empty($fileName))
		{
			$fileName = $dir . time() . mt_rand(0, 9) . mt_rand(0, 9) . $suffix;
			$fileName = is_file($fileName) ? '' : $fileName;
		}
		return $fileName;
	}
	
	// 获取文件后缀名
	private static function getSuffix($fileName)
	{
		$pos = strripos($fileName, '.');
		return $pos !== false ? substr($fileName, $pos) : '';
	}
	
	// 获取不含后缀名的文件名
	private static function getRealName($fileName)
	{
		$res = explode('.', basename($fileName));
		return $res[0];
	}
	
	/**
	 * 批量上传
	 * @param: $files        array        $_FILES 或者形如array('key'=>array(array('name'=>'','type'=>'','tmp_name'=>'','error'=>'','size'=>'')))的数组
	 * @param: $addWater     boolean      是否添加水印
	 * @param: $mkThumb      boolean      是否生成缩略图
	 * @param: $mixed        string       缩略图宽度或宽度数组、宽高数组
	 * @param: $isSquare     boolean      缩略图是否为正方形
	 * @param: $quality      number       质量
	 */
	public static function uploadFileBatch($files, $addWater = false, $mkThumb = false, $mixed = '', $isSquare = false, $quality = 0)
	{
		$realFiles = array();
		foreach($files as $k => $v)
		{
			// 处理直接传入$_FILES的情况
			if(isset($v['name']))
			{
				if(is_array($v['name']))
				{
					foreach($v['name'] as $kk => $vv)
					{
						$realFiles[$k][] = array(
							'name'		=> $vv,
							'type'		=> $files[$k]['type'][$kk],
							'tmp_name'	=> $files[$k]['tmp_name'][$kk],
							'error'		=> $files[$k]['error'][$kk],
							'size'		=> $files[$k]['size'][$kk]
						);
					}
				}
				else
				{
					$realFiles[$k][] = $v;
				}
			}
			// 直接转换一下
			else
			{
				$realFiles[$k] = $v;
			}
		}
		// 处理批量上传
		$uploadResult = array();
		foreach($realFiles as $k => $v)
		{
			foreach($v as $vv)
			{
				$uploadResult[$k][] = uploadFile($vv, $addWater, $mkThumb, $mixed, $isSquare, $quality);
			}
		}
		return $uploadResult;
	}
	
	/**
	 * 处理文件上传
	 * @param: $file         array        包含name、type、tmp_name、error、size的数组
	 * @param: $addWater     boolean      是否添加水印
	 * @param: $mkThumb      boolean      是否生成缩略图
	 * @param: $mixed        string       缩略图宽度或宽度数组、宽高数组
	 * @param: $isSquare     boolean      缩略图是否为正方形
	 * @param: $quality      number       质量
	 */
	public static function uploadFile($file, $addWater = false, $mkThumb = false, $mixed = '', $isSquare = false, $quality = 0)
	{
		// 检查是否存在错误
		if($file['error'])
		{
			self::$error = '上传错误！';
			return false;
		}
		// 检查文件后缀名和文件类型是否一致
		if(stripos($file['type'], self::getSuffix($file['name'])) === false)
		{
			self::$error = '文件类型异常！';
			return false;
		}
		// 检查文件大小
		// TODO 考虑到允许上传视频的话，需要单独对图片、视频等类型的文件做判断
		if(!self::chkSize($file['size']))
		{
			self::$error = '文件过大！';
			return false;
		}
		// 在uploads下建立以年月命名的文件夹
		$newUploadsDir = self::mkNewDir(self::$uploadsDir);
		// 生成文件名
		$fileName = self::uniqueName($newUploadsDir, self::getSuffix($file['name']));
		// 将上传的文件移动到统一的文件夹中
		if(move_uploaded_file($file['tmp_name'], $fileName))
		{
			@chmod($fileName,0755);
			// 判断上传文件是否为图片，并作后续处理
			if(self::isType($file['type'], 'image'))
			{
				// 在images下建立以年月命名的文件夹
				$newImagesDir = self::mkNewDir(self::$imagesDir);
				$newFileName = $newImagesDir . basename($fileName);
				// 复制图片到文件夹中
				copy($fileName, $newFileName);
				// 添加水印
				if($addWater)
				{
					self::addWaterMark($newFileName, $quality);
				}
				if($mkThumb)
				{
					self::mkThumbBatch($newFileName, $mixed, $isSquare, $quality);
				}
				return $newFileName;
			}
			// TODO 这里增加对其他文件类型的判断以及处理
			else
			{
				return $fileName;
			}
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 批量创建缩略图
	 * @param: $fileName     string       包含路径的原图文件名
	 * @param: $mixed        string       缩略图宽度或宽度数组、宽高数组
	 * @param: $isSquare     boolean      缩略图是否为正方形
	 * @param: $quality      number       质量
	 */
	private static function mkThumbBatch($fileName, $mixed = '', $isSquare = false, $quality = 0)
	{
		// 设置缩略图质量
		$quality = $quality ? $quality : self::$quality;
		// 获取缩略图存放文件夹
		$fileDir = self::mkNewDir(self::$imagesDir);
		// 获取图像不含后缀的文件名
		$realName = self::getRealName($fileName);
		// 获取图像信息
		$info = @getimagesize($fileName);
		// 获得源图资源
		$srcIamge = self::getSrcImage($fileName, $info[2]);
		// 取得缩略尺寸
		$thumbSizes = self::getThumbData($info[0], $info[1], $mixed, $isSquare);
		
		foreach($thumbSizes as $v)
		{
			$res[] = self::mkThumb($fileDir, $realName, $srcIamge, $v[0], $v[1], $info[0], $info[1], $quality);
		}
		// 销毁图像
		imagedestroy($srcIamge);
		return $res;
	}
	
	/**
	 * 创建缩略图
	 * @param: $fileDir       string       存放路径
	 * @param: $realName      string       源文件名（不含后缀）
	 * @param: $srcIamge      resource     源图像资源
	 * @param: $thumbWidth    number       缩略图宽
	 * @param: $thumbHeight   number       缩略图高
	 * @param: $imgWidth      number       原图宽
	 * @param: $imgHeight     number       原图高
	 * @param: $quality       number       质量
	 */
	private function mkThumb($fileDir, $realName, $srcIamge, $thumbWidth, $thumbHeight, $imgWidth, $imgHeight, $quality)
	{
		// 创建缩略图资源
		// GD2以上版本
		$srcThumb = @imagecreatetruecolor($thumbWidth, $thumbHeight);
		if(!$srcThumb)
		{
			// 所有版本，但是这玩意会失真，甚至变色 -.-#
			$srcThumb = imagecreate($thumbWidth, $thumbHeight);
		}
		// TODO 如需着色，可能需要用到sscanf、imagecolorallocate、imagefilledrectangle函数分别格式化颜色数据，分配颜色，填充图像
		// 拷贝图像并调整大小
		// GD2以上版本，可得到质量更好的图像
		$imgThumb = imagecopyresampled($srcThumb, $srcIamge, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $imgWidth, $imgHeight);
		if(!$imgThumb)
		{
			// 适用所有版本，优点是速度快，缺点是质量较差
			$imgThumb = imagecopyresized($srcThumb, $srcIamge, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $imgWidth, $imgHeight);
		}
		// 缩略图统一生成jpg格式
		$fileName = $fileDir . $realName . '@' . $thumbWidth . 'x' . $thumbHeight . '.jpg';
		// 激活隔行扫描（渐进式显示JPG）
		imageinterlace($srcThumb, 1);
		// 输出图像到文件
		imagejpeg($srcThumb, $fileName, $quality);
		// 销毁图像
        imagedestroy($srcThumb);
		// 正确生成则返回缩略图地址
		return is_file($fileName) ? $fileName : false;
	}
	
	/**
	 * 添加水印
	 * @param: $fileName       string       包含路径的原图文件名
	 * @param: $quality        number       质量
	 */
	private static function addWaterMark($fileName, $quality = 0)
	{
		// 设置图片输出质量
		$quality = $quality ? $quality : self::$quality;
		// 获取水印图信息
		$info = @getimagesize(self::$waterMark);
		// 获得水印图资源
		$waterMarkIamge = self::getSrcImage(self::$waterMark, $info[2]);
		// 获取原图信息
		$imgInfo = @getimagesize($fileName);
		// 获得原图资源
		$srcIamge = self::getSrcImage($fileName, $imgInfo[2]);
		// 确定水印位置（1居中、2左上、3右上、4右下、5左下）
		switch(self::$waterMarkPlace)
		{
			case 2:
				$x = 0;
				$y = 0;
				break;
			case 3:
				$x = $imgInfo[0] - $info[0];
				$y = 0;
				break;
			case 4:
				$x = $imgInfo[0] - $info[0];
				$y = $imgInfo[1] - $info[1];
				break;
			case 5:
				$x = 0;
				$y = $imgInfo[1] - $info[1];
				break;
			default:
				$x = ($imgInfo[0] - $info[0]) / 2;
				$y = ($imgInfo[1] - $info[1]) / 2;
		}
		// 设定混色模式，允许透明度
		imagealphablending($waterMarkIamge, true);
		// 把水印拷贝到图片中
		imagecopy($srcIamge, $waterMarkIamge, $x, $y, 0, 0, $info[0], $info[1]);
		// TODO 若水印图片格式不是确定的PNG，应在此处增加对非PNG格式的处理
		// imagecopymerge($srcIamge, $waterMarkIamge, $x, $y, 0, 0,$info[0], $info[1], self::$waterMarkAlpha);
	
		// 激活隔行扫描（渐进式显示JPG）
		imageinterlace($srcIamge, 1);
		// 输出图像到文件
		imagejpeg($srcIamge, $fileName, $quality);
		// 销毁图像
        imagedestroy($srcIamge);
        imagedestroy($waterMarkIamge);
	}
	
	/**
	 * 统一缩略图调整所需要的数据
	 * @param: $imgWidth number 原图宽
	 * @param: $imgHeight number 原图高
	 * @param: $mixed array 可能仅指定宽；可能由多个宽组成的一维数组；可能由指定宽高组成的二维数组
	 * @param: $isSquare boolean 是否统一为正方形缩略图（指定宽高时无效）
	 */
	private static function getThumbData($imgWidth, $imgHeight, $mixed = '', $isSquare = false)
	{
		// 0为保留原始尺寸
		$mixed = !empty($mixed) || $mixed === 0 ? $mixed : self::$thumbSize;
		$mixed = is_array($mixed) ? $mixed : array($mixed);
		// 计算图像宽高比
		$ratio = $imgWidth / $imgHeight;
		// 创建一个二维数组以保存统一的数据
		// 每个子数组元素为：0宽、1高
		$realData = array();
		// 处理宽度小于0以及可能出现的无法确定高度的情况
		foreach($mixed as $v)
		{
			$realData[] = self::getWH($imgWidth, $v, $ratio, $isSquare);
		}
		return $realData;
	}
	
	// 修正宽高
	private static function getWH($ow, $m, $ratio, $isSquare)
	{
		$m = is_array($m) ? $m : array($m);
		$tmpW = $m[0] > 0 ? $m[0] : $ow;
		$tmpH = isset($m[1]) && $m[1] > 0 ? $m[1] : ($isSquare ? $tmpW : $tmpW / $ratio);
		return array($tmpW, $tmpH);
	}
	
	// 获取源图像连接资源
	private static function getSrcImage($fileName, $mimeType)
	{
		switch ($mimeType)
        {
        	case 1:
        	case 'image/gif':
        		return imagecreatefromgif($fileName);
        		break;
        	case 2:
        	case 'image/pjpeg':
        	case 'image/jpeg':
        		return imagecreatefromjpeg($fileName);
        		break;
        	case 3:
        	case 'image/x-png':
        	case 'image/png':
        		return imagecreatefrompng($fileName);
        		break;
        	default:
        		return false;
		}
	}
	
	
}