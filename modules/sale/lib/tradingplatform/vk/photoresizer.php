<?php

namespace Bitrix\Sale\TradingPlatform\Vk;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\TradingPlatform;
use Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

/**
 * Owerwrite system image resize method, because we need increase image
 *
 * Class PhotoResizer
 * @package Bitrix\Sale\TradingPlatform\Vk
 */
class PhotoResizer
{
	const RESIZE_NO = 0;
	const RESIZE_UP = 10;
	const RESIZE_UP_CROP = 20;
	const RESIZE_DOWN = 30;
	const RESIZE_DOWN_CROP = 40;
	/**
	 * Return picture URL by picture src
	 *
	 * @param $src
	 * @param string $domain - old param. May be empty
	 * @return string
	 */
	protected function buildPictureUrl($src, $domain = '')
	{
		if ($domain == '')
		{
//			get different variants of domain URL, because in diff site some variants not work
			$server = Application::getInstance()->getContext()->getServer();
			if ($host = $server->getHttpHost())
			{
				$domain = strtok($host, ':');
			}
			elseif ($name = $server->getServerName())
			{
				$domain = strtok($name, ':');
			}
			else
			{
				$domain = strtok(SITE_SERVER_NAME, ':');
			}
		}
		
		$protocol = \CMain::IsHTTPS() ? "https://" : "http://";
		// relative path by '/'
		if (mb_substr($src, 0, 1) == "/")
		{
			$strFile = $protocol . $domain . implode("/", array_map("rawurlencode", explode("/", $src)));
		}
		// full path
		elseif (preg_match("/^(http|https):\\/\\/(.*?)\\/(.*)\$/", $src, $match))
		{
			$strFile = $protocol . $match[2] . '/' . implode("/", array_map("rawurlencode", explode("/", $match[3])));
		}
		// default
		else
		{
			$strFile = $src;
		}
		
		return $strFile;
	}
	
	
	public static function sortPhotoArray($photos, $type)
	{
		$sortedPhotos = array();
		
		switch ($type)
		{
			case 'PRODUCT':
				$count = Vk::MAX_PHOTOS_IN_PRODUCT;
//				todo: for photos we must ALWAYS set VK-field in first places
				break;
			
			case 'ALBUM':
				$count = Vk::MAX_PHOTOS_IN_ALBUM;
				break;
			
			default:
				return $photos;
		}
		
//		match sizes of photos. Key of array - sum of width and height
		$photosSizes = array();
		foreach($photos as $photo)
		{
//			photo may be not set
			if($photo)
			{
				$sizes = \CFile::GetFileArray($photo);
				$photosSizes[$sizes['HEIGHT'] + $sizes['WIDTH']] = array(
					'PHOTO_ID' => $photo,
					'SIZES_SUM' => $sizes['HEIGHT'] + $sizes['WIDTH']
				);
			}
		}
		krsort($photosSizes);
		
//		get elements from sorted sizes until not catch count
		while(count($sortedPhotos) < $count)
		{
			$biggestPhoto = current($photosSizes);
			$sortedPhotos[$biggestPhoto['PHOTO_ID']] = $biggestPhoto['PHOTO_ID'];
			unset($photosSizes[$biggestPhoto['SIZES_SUM']]);
		}
		
		return $sortedPhotos;
	}
	
	
	/**
	 * Check photo sizes by type of converter
	 *
	 * @param $photos - array of photo IDs, ore one photo ID
	 * @param $type
	 * @return array - only photos with satisfy of requirements.
	 */
	public static function checkPhotos($photos, $type)
	{
//		check empty photos
		if(!$photos)
			return NULL;
		
		$result = array();
		
		switch ($type)
		{
			case 'PRODUCT':
				$count = Vk::MAX_PHOTOS_IN_PRODUCT;
				$needMainPhoto = true;
				$sizesLimits = array(
					'MIN_WIDTH' => Vk::MIN_PRODUCT_PHOTO_WIDTH,
					'MIN_HEIGHT' => Vk::MIN_PRODUCT_PHOTO_HEIGHT,
					'MAX_SIZES_SUM' => Vk::MAX_PRODUCT_PHOTO_SIZES_SUM,
					'MAX_SIZE' => Vk::MAX_PRODUCT_PHOTO_SIZE,
					'RATIO_V' => Vk::MAX_PRODUCT_RATIO_V,	// width / height
					'RATIO_H' => Vk::MAX_PRODUCT_RATIO_H,
				);
				break;
			
			case 'ALBUM':
				$count = Vk::MAX_PHOTOS_IN_ALBUM;
				$needMainPhoto = false;
				$sizesLimits = array(
					'MIN_WIDTH' => Vk::MIN_ALBUM_PHOTO_WIDTH,
					'MIN_HEIGHT' => Vk::MIN_ALBUM_PHOTO_HEIGHT,
					'MAX_SIZES_SUM' => Vk::MAX_ALBUM_PHOTO_SIZES_SUM,
					'MAX_SIZE' => Vk::MAX_ALBUM_PHOTO_SIZE,
					'RATIO_V' => Vk::MAX_ALBUM_RATIO_V,	// width / height
					'RATIO_H' => Vk::MAX_ALBUM_RATIO_H,
				);
//				CONVERT photo-id format if needed
				if (!is_array($photos))
					$photos = array($photos => array("PHOTO_BX_ID" => $photos));
				break;
			
			default:
				return $photos;
		}

//		PROCESSED
		$i = 1;
		foreach ($photos as $photoId => $photo)
		{
			if ($photoChecked = self::checkPhoto($photoId, $sizesLimits))
			{
//				MAIN photo is first
				if ($i == 1 && $needMainPhoto)
				{
					$result["PHOTO_MAIN_BX_ID"] = $photoChecked['ID'];
					$result["PHOTO_MAIN_URL"] = $photoChecked['URL'];
					$count++;    //increase limit for other photos
					$i++;
				}

//				other PHOTOS
				elseif ($i++ <= $count)
				{
					$result["PHOTOS"][$photoChecked['ID']]["PHOTO_BX_ID"] = $photoChecked['ID'];
					$result["PHOTOS"][$photoChecked['ID']]["PHOTO_URL"] = $photoChecked['URL'];
				}
				
				else
				{
					break;
				}

//				set flag if image was be resized
				if ($photoChecked['RESIZE'])
				{
					$result["RESIZE"] = true;
					$result["RESIZE_TYPE"] = $photoChecked['RESIZE'];
				}
			}
		}
		
		return $result;
	}
	
	
	/**
	 * Check sizes and filesize of one photo.
	 * Return only check passed photos
	 *
	 * @param $photoId
	 * @param $sizesLimits
	 * @return mixed
	 */
	private function checkPhoto($photoId, $sizesLimits)
	{
		$photoParams = \CFile::GetFileArray($photoId);
//		check bad files
		if(!$photoParams)
			return false;
		$photoSrc = $photoParams["SRC"];
		$photoUrl = self::buildPictureUrl($photoParams["SRC"]);
		$needResize = self::RESIZE_NO;
		$resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;	// default not crop

//		need 'RESIZE_UP';
		if (
			$photoParams['HEIGHT'] < $sizesLimits['MIN_HEIGHT'] ||
			$photoParams['WIDTH'] < $sizesLimits['MIN_WIDTH']
		)
		{
			$needResize = self::RESIZE_UP;
		}

//		need 'RESIZE_DOWN';
		if (
			($photoParams['HEIGHT'] + $photoParams['WIDTH']) > $sizesLimits['MAX_SIZES_SUM'] ||
			$photoParams['FILE_SIZE'] > $sizesLimits['MAX_SIZE']
		)
		{
			$needResize = self::RESIZE_DOWN;
		}

// 		for big RATIO - need a crop. If need resize - use $needResize flag, else - always resize down
		if (
			(isset($sizesLimits['RATIO_V']) && $photoParams['WIDTH'] / $photoParams['HEIGHT'] <= $sizesLimits['RATIO_V']) ||
			(isset($sizesLimits['RATIO_H']) && $photoParams['WIDTH'] / $photoParams['HEIGHT'] >= $sizesLimits['RATIO_H'])
		)
		{
			// UP, but we reduce image (need for a get correct sizes)
			if ($needResize == self::RESIZE_UP || $needResize == self::RESIZE_NO)
			{
				$needResize = self::RESIZE_UP_CROP;
			}
			elseif ($needResize == self::RESIZE_DOWN)
			{
				$needResize = self::RESIZE_DOWN_CROP;
			}
		}

//		calculate new sizes
		if ($needResize)
		{
			switch ($needResize)
			{
				case self::RESIZE_UP:
					$multiplier = max($sizesLimits['MIN_WIDTH'] / $photoParams['WIDTH'], $sizesLimits['MIN_HEIGHT'] / $photoParams['HEIGHT']);
					$newWidth = ceil($photoParams['WIDTH'] * $multiplier);
					$newHeight = ceil($photoParams['HEIGHT'] * $multiplier);
					break;
				
				case self::RESIZE_DOWN:
					$ratio = $sizesLimits['MIN_WIDTH'] / $sizesLimits['MIN_HEIGHT'];
					$newHeight = floor($sizesLimits['MAX_SIZES_SUM'] / ($ratio + 1));
					$newWidth = floor($ratio * $newHeight);
					break;
					
				case self::RESIZE_UP_CROP:
					if(($sizesLimits['MIN_WIDTH'] / $photoParams['WIDTH']) < ($sizesLimits['MIN_HEIGHT'] / $photoParams['HEIGHT']))
					{
						$ratio = $sizesLimits["RATIO_H"];
						$newHeight = $sizesLimits['MIN_HEIGHT'];
						$newWidth = floor($sizesLimits['MIN_HEIGHT'] * $ratio) - 1; // minus one for preserve overratio.
					}
					else
					{
						$ratio = $sizesLimits["RATIO_V"];
						$newWidth = $sizesLimits['MIN_WIDTH'];
						$newHeight = floor($sizesLimits['MIN_WIDTH'] / $ratio) - 1; // minus one for preserve overratio.
					}
					$resizeType = BX_RESIZE_IMAGE_EXACT;	// resize with crop
					break;
					
				case self::RESIZE_DOWN_CROP:
					if(($sizesLimits['MIN_WIDTH'] / $photoParams['WIDTH']) < ($sizesLimits['MIN_HEIGHT'] / $photoParams['HEIGHT']))
					{
						$ratio = $sizesLimits["RATIO_H"];
						$newHeight = floor($sizesLimits['MAX_SIZES_SUM'] / ($ratio + 1));
						$newWidth = floor($ratio * $newHeight) - 1;	// need -1, because sizes must be <= ratio
					}
					else
					{
						$ratio = $sizesLimits["RATIO_V"];
						$newHeight = floor($sizesLimits['MAX_SIZES_SUM'] / ($ratio + 1));
						$newWidth = floor($ratio * $newHeight--);	// need -1, because sizes must be <= ratio
					}
					$resizeType = BX_RESIZE_IMAGE_EXACT;	// resize with crop
					break;
				
				default:
					return false;
			}

			$resizeFilters = false;
			if ($needResize == self::RESIZE_UP || $needResize == self::RESIZE_UP_CROP)
			{
//				set empty array for UP resizing - PNG may be failed by timeout
				$resizeFilters = [];
			}
			
			$resizedPhoto = self::ResizeImageGet(
				$photoId,
				array('width' => $newWidth, 'height' => $newHeight),
				$resizeType,
				true,
				$resizeFilters
			);

//			need save new photo
			$photoId = \CFile::SaveFile(
				\CFile::MakeFileArray($resizedPhoto['SRC']),
				"resize_cache/vk_export_resize_img"
			);
			$resizedFile = \CFile::GetFileArray($photoId);
			$photoUrl = self::buildPictureUrl($resizedFile['SRC']);
			$photoSrc = $resizedFile['SRC'];
		}
		
		return array(
			'RESIZE' => $needResize,
			'SRC' => $photoSrc,
			'URL' => $photoUrl,
			'ID' => $photoId,
		);
	}
	
	/**
	 * Overwrite system ResizeImageGet. Need for increase images
	 *
	 * @param $file
	 * @param $arSize
	 * @param int $resizeType
	 * @param bool $bInitSizes
	 * @param bool $arFilters
	 * @param bool $bImmediate
	 * @param bool $jpgQuality
	 * @return bool|mixed
	 */
	public static function ResizeImageGet($file, $arSize, $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL, $bInitSizes = false, $arFilters = false, $bImmediate = false, $jpgQuality = false)
	{
		if (!is_array($file) && intval($file) > 0)
		{
			$file = \CFile::GetFileArray($file);
		}
		
		if (!is_array($file) || !array_key_exists("FILE_NAME", $file) || $file["FILE_NAME"] == '')
			return false;
		
		if ($resizeType !== BX_RESIZE_IMAGE_EXACT && $resizeType !== BX_RESIZE_IMAGE_PROPORTIONAL_ALT)
			$resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;
		
		if (!is_array($arSize))
			$arSize = array();
		if (!array_key_exists("width", $arSize) || intval($arSize["width"]) <= 0)
			$arSize["width"] = 0;
		if (!array_key_exists("height", $arSize) || intval($arSize["height"]) <= 0)
			$arSize["height"] = 0;
		$arSize["width"] = intval($arSize["width"]);
		$arSize["height"] = intval($arSize["height"]);
		
		$uploadDirName = \COption::GetOptionString("main", "upload_dir", "upload");
		
		$imageFile = "/" . $uploadDirName . "/" . $file["SUBDIR"] . "/" . $file["FILE_NAME"];
		$arImageSize = false;
		$bFilters = is_array($arFilters) && !empty($arFilters);

		if (
			($arSize["width"] <= 0 /*|| $arSize["width"] >= $file["WIDTH"]*/)
			&& ($arSize["height"] <= 0 /*|| $arSize["height"] >= $file["HEIGHT"]*/)
		)
		{
			if ($bFilters)
			{
				//Only filters. Leave size unchanged
				$arSize["width"] = $file["WIDTH"];
				$arSize["height"] = $file["HEIGHT"];
				$resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;
			}
			else
			{
				global $arCloudImageSizeCache;
				$arCloudImageSizeCache[$file["SRC"]] = array($file["WIDTH"], $file["HEIGHT"]);
				
				return array(
					"SRC" => $file["SRC"],
					"width" => intval($file["WIDTH"]),
					"height" => intval($file["HEIGHT"]),
					"size" => $file["FILE_SIZE"],
				);
			}
		}
		
		$io = \CBXVirtualIo::GetInstance();
		$cacheImageFile = "/" . $uploadDirName . "/resize_cache/" . $file["SUBDIR"] . "/" . $arSize["width"] . "_" . $arSize["height"] . "_" . $resizeType . (is_array($arFilters) ? md5(serialize($arFilters)) : "") . "/" . $file["FILE_NAME"];
		
		$cacheImageFileCheck = $cacheImageFile;
		if ($file["CONTENT_TYPE"] == "image/bmp")
			$cacheImageFileCheck .= ".jpg";
		
		static $cache = array();
		$cache_id = $cacheImageFileCheck;
		if (isset($cache[$cache_id]))
		{
			return $cache[$cache_id];
		}
		elseif (!file_exists($io->GetPhysicalName($_SERVER["DOCUMENT_ROOT"] . $cacheImageFileCheck)))
		{
			/****************************** QUOTA ******************************/
			$bDiskQuota = true;
			if (\COption::GetOptionInt("main", "disk_space") > 0)
			{
				$quota = new \CDiskQuota();
				$bDiskQuota = $quota->CheckDiskQuota($file);
			}
			/****************************** QUOTA ******************************/
			
			if ($bDiskQuota)
			{
				if (!is_array($arFilters))
					$arFilters = array(
						array("name" => "sharpen", "precision" => 15),
					);
				
				$sourceImageFile = $_SERVER["DOCUMENT_ROOT"] . $imageFile;
				$cacheImageFileTmp = $_SERVER["DOCUMENT_ROOT"] . $cacheImageFile;
				$bNeedResize = true;
				$callbackData = NULL;
				
				foreach (GetModuleEvents("main", "OnBeforeResizeImage", true) as $arEvent)
				{
					if (ExecuteModuleEventEx($arEvent, array(
						$file,
						array($arSize, $resizeType, array(), false, $arFilters, $bImmediate),
						&$callbackData,
						&$bNeedResize,
						&$sourceImageFile,
						&$cacheImageFileTmp,
					)))
						break;
				}
				
				if ($bNeedResize && self::ResizeImageFile($sourceImageFile, $cacheImageFileTmp, $arSize, $resizeType, array(), $jpgQuality, $arFilters))
				{
					$cacheImageFile = mb_substr($cacheImageFileTmp, mb_strlen($_SERVER["DOCUMENT_ROOT"]));
					
					/****************************** QUOTA ******************************/
					if (\COption::GetOptionInt("main", "disk_space") > 0)
						\CDiskQuota::UpdateDiskQuota("file", filesize($io->GetPhysicalName($cacheImageFileTmp)), "insert");
					/****************************** QUOTA ******************************/
				}
				else
				{
					$cacheImageFile = $imageFile;
				}
				
				foreach (GetModuleEvents("main", "OnAfterResizeImage", true) as $arEvent)
				{
					if (ExecuteModuleEventEx($arEvent, array(
						$file,
						array($arSize, $resizeType, array(), false, $arFilters),
						&$callbackData,
						&$cacheImageFile,
						&$cacheImageFileTmp,
						&$arImageSize,
					)))
						break;
				}
			}
			else
			{
				$cacheImageFile = $imageFile;
			}
			
			$cacheImageFileCheck = $cacheImageFile;
		}
		
		if ($bInitSizes && !is_array($arImageSize))
		{
			$arImageSize = \CFile::GetImageSize($_SERVER["DOCUMENT_ROOT"] . $cacheImageFileCheck);
			
			$f = $io->GetFile($_SERVER["DOCUMENT_ROOT"] . $cacheImageFileCheck);
			$arImageSize[2] = $f->GetFileSize();
		}
		
		$cache[$cache_id] = array(
			"SRC" => $cacheImageFileCheck,
			"width" => intval($arImageSize[0]),
			"height" => intval($arImageSize[1]),
			"size" => $arImageSize[2],
		);
		
		return $cache[$cache_id];
	}
	
	/**
	 * Overwrite system ResizeImageFile. Need for increase images
	 *
	 * @param $sourceFile
	 * @param $destinationFile
	 * @param $arSize
	 * @param int $resizeType
	 * @param array $arWaterMark
	 * @param bool $jpgQuality
	 * @param bool $arFilters
	 * @return bool
	 */
	private static function ResizeImageFile($sourceFile, &$destinationFile, $arSize, $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL, $arWaterMark = array(), $jpgQuality = false, $arFilters = false)
	{
		$io = \CBXVirtualIo::GetInstance();
		
		if (!$io->FileExists($sourceFile))
			return false;
		
		$bNeedCreatePicture = false;
		
		if ($resizeType !== BX_RESIZE_IMAGE_EXACT && $resizeType !== BX_RESIZE_IMAGE_PROPORTIONAL_ALT)
			$resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;
		
		if (!is_array($arSize))
			$arSize = array();
		if (!array_key_exists("width", $arSize) || intval($arSize["width"]) <= 0)
			$arSize["width"] = 0;
		if (!array_key_exists("height", $arSize) || intval($arSize["height"]) <= 0)
			$arSize["height"] = 0;
		$arSize["width"] = intval($arSize["width"]);
		$arSize["height"] = intval($arSize["height"]);
		
		$arSourceSize = array("x" => 0, "y" => 0, "width" => 0, "height" => 0);
		$arDestinationSize = array("x" => 0, "y" => 0, "width" => 0, "height" => 0);
		
		$arSourceFileSizeTmp = \CFile::GetImageSize($sourceFile);
		if (!in_array($arSourceFileSizeTmp[2], array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_BMP)))
			return false;
		
		$orientation = 0;
		if ($arSourceFileSizeTmp[2] == IMAGETYPE_JPEG)
		{
			$exifData = \CFile::ExtractImageExif($io->GetPhysicalName($sourceFile));
			if ($exifData && isset($exifData['Orientation']))
			{
				$orientation = $exifData['Orientation'];
				//swap width and height
				if ($orientation >= 5 && $orientation <= 8)
				{
					$tmp = $arSourceFileSizeTmp[1];
					$arSourceFileSizeTmp[1] = $arSourceFileSizeTmp[0];
					$arSourceFileSizeTmp[0] = $tmp;
				}
			}
		}
		
		if (\CFile::isEnabledTrackingResizeImage())
		{
			header("X-Bitrix-Resize-Image: {$arSize["width"]}_{$arSize["height"]}_{$resizeType}");
		}
//		imagemagick was be here. I delete them to simplification
		
		if ($io->Copy($sourceFile, $destinationFile))
		{
			switch ($arSourceFileSizeTmp[2])
			{
				case IMAGETYPE_GIF:
					$sourceImage = imagecreatefromgif($io->GetPhysicalName($sourceFile));
					$bHasAlpha = true;
					break;
				case IMAGETYPE_PNG:
					$sourceImage = imagecreatefrompng($io->GetPhysicalName($sourceFile));
					$bHasAlpha = true;
					break;
				case IMAGETYPE_BMP:
					$sourceImage = \CFile::ImageCreateFromBMP($io->GetPhysicalName($sourceFile));
					$bHasAlpha = false;
					break;
				default:
					$sourceImage = imagecreatefromjpeg($io->GetPhysicalName($sourceFile));
					if ($sourceImage === false)
					{
						ini_set('gd.jpeg_ignore_warning', 1);
						$sourceImage = imagecreatefromjpeg($io->GetPhysicalName($sourceFile));
					}
					
					if ($orientation > 1)
					{
						$properlyOriented = \CFile::ImageHandleOrientation($orientation, $sourceImage);
						
						if ($jpgQuality === false)
							$jpgQuality = intval(\COption::GetOptionString('main', 'image_resize_quality', '95'));
						if ($jpgQuality <= 0 || $jpgQuality > 100)
							$jpgQuality = 95;
						
						if ($properlyOriented)
						{
							imagejpeg($properlyOriented, $io->GetPhysicalName($destinationFile), $jpgQuality);
							$sourceImage = $properlyOriented;
						}
					}
					$bHasAlpha = false;
					break;
			}
			
			$sourceImageWidth = intval(imagesx($sourceImage));
			$sourceImageHeight = intval(imagesy($sourceImage));
			
			if ($sourceImageWidth > 0 && $sourceImageHeight > 0)
			{
				if ($arSize["width"] <= 0 || $arSize["height"] <= 0)
				{
					$arSize["width"] = $sourceImageWidth;
					$arSize["height"] = $sourceImageHeight;
				}
				
				self::ScaleImage($sourceImageWidth, $sourceImageHeight, $arSize, $resizeType, $bNeedCreatePicture, $arSourceSize, $arDestinationSize);
				
				if ($bNeedCreatePicture)
				{
					if (\CFile::IsGD2())
					{
						$picture = imagecreatetruecolor($arDestinationSize["width"], $arDestinationSize["height"]);
						if ($arSourceFileSizeTmp[2] == IMAGETYPE_PNG)
						{
							$transparentcolor = imagecolorallocatealpha($picture, 0, 0, 0, 127);
							imagefilledrectangle($picture, 0, 0, $arDestinationSize["width"], $arDestinationSize["height"], $transparentcolor);
							
							imagealphablending($picture, false);
							imagecopyresampled($picture, $sourceImage,
								0, 0, $arSourceSize["x"], $arSourceSize["y"],
								$arDestinationSize["width"], $arDestinationSize["height"], $arSourceSize["width"], $arSourceSize["height"]);
							imagealphablending($picture, true);
						}
						elseif ($arSourceFileSizeTmp[2] == IMAGETYPE_GIF)
						{
							imagepalettecopy($picture, $sourceImage);
							
							//Save transparency for GIFs
							$transparentcolor = imagecolortransparent($sourceImage);
							if ($transparentcolor >= 0 && $transparentcolor < imagecolorstotal($sourceImage))
							{
								$RGB = imagecolorsforindex($sourceImage, $transparentcolor);
								$transparentcolor = imagecolorallocate($picture, $RGB["red"], $RGB["green"], $RGB["blue"]);
								imagecolortransparent($picture, $transparentcolor);
								imagefilledrectangle($picture, 0, 0, $arDestinationSize["width"], $arDestinationSize["height"], $transparentcolor);
							}
							
							imagecopyresampled($picture, $sourceImage,
								0, 0, $arSourceSize["x"], $arSourceSize["y"],
								$arDestinationSize["width"], $arDestinationSize["height"], $arSourceSize["width"], $arSourceSize["height"]);
						}
						else
						{
							imagecopyresampled($picture, $sourceImage,
								0, 0, $arSourceSize["x"], $arSourceSize["y"],
								$arDestinationSize["width"], $arDestinationSize["height"], $arSourceSize["width"], $arSourceSize["height"]);
						}
					}
					else
					{
						$picture = imagecreate($arDestinationSize["width"], $arDestinationSize["height"]);
						imagecopyresized($picture, $sourceImage,
							0, 0, $arSourceSize["x"], $arSourceSize["y"],
							$arDestinationSize["width"], $arDestinationSize["height"], $arSourceSize["width"], $arSourceSize["height"]);
					}
				}
				else
				{
					$picture = $sourceImage;
				}
				
				if (is_array($arFilters))
				{
					foreach ($arFilters as $arFilter)
						$bNeedCreatePicture |= \CFile::ApplyImageFilter($picture, $arFilter, $bHasAlpha);
				}
				
				if (is_array($arWaterMark))
				{
					$arWaterMark["name"] = "watermark";
					$bNeedCreatePicture |= \CFile::ApplyImageFilter($picture, $arWaterMark, $bHasAlpha);
				}
				
				if ($bNeedCreatePicture)
				{
					if ($io->FileExists($destinationFile))
						$io->Delete($destinationFile);
					switch ($arSourceFileSizeTmp[2])
					{
						case IMAGETYPE_GIF:
							imagegif($picture, $io->GetPhysicalName($destinationFile));
							break;
						case IMAGETYPE_PNG:
							imagealphablending($picture, false);
							imagesavealpha($picture, true);
							imagepng($picture, $io->GetPhysicalName($destinationFile));
							break;
						default:
							if ($arSourceFileSizeTmp[2] == IMAGETYPE_BMP)
								$destinationFile .= ".jpg";
							if ($jpgQuality === false)
								$jpgQuality = intval(\COption::GetOptionString('main', 'image_resize_quality', '95'));
							if ($jpgQuality <= 0 || $jpgQuality > 100)
								$jpgQuality = 95;
							imagejpeg($picture, $io->GetPhysicalName($destinationFile), $jpgQuality);
							break;
					}
					imagedestroy($picture);
				}
			}
			
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Overwrite system ScaleImage. Need for increase images
	 *
	 * @param $sourceImageWidth
	 * @param $sourceImageHeight
	 * @param $arSize
	 * @param $resizeType
	 * @param $bNeedCreatePicture
	 * @param $arSourceSize
	 * @param $arDestinationSize
	 */
	private static function ScaleImage($sourceImageWidth, $sourceImageHeight, $arSize, $resizeType, &$bNeedCreatePicture, &$arSourceSize, &$arDestinationSize)
	{
		if (!is_array($arSize))
			$arSize = array();
		if (!array_key_exists("width", $arSize) || intval($arSize["width"]) <= 0)
			$arSize["width"] = 0;
		if (!array_key_exists("height", $arSize) || intval($arSize["height"]) <= 0)
			$arSize["height"] = 0;
		$arSize["width"] = intval($arSize["width"]);
		$arSize["height"] = intval($arSize["height"]);
		
		$bNeedCreatePicture = false;
		$arSourceSize = array("x" => 0, "y" => 0, "width" => 0, "height" => 0);
		$arDestinationSize = array("x" => 0, "y" => 0, "width" => 0, "height" => 0);
		
		if ($sourceImageWidth > 0 && $sourceImageHeight > 0)
		{
			if ($arSize["width"] > 0 && $arSize["height"] > 0)
			{
				switch ($resizeType)
				{
					case BX_RESIZE_IMAGE_EXACT:
						$bNeedCreatePicture = true;
						
						$ratio = (($sourceImageWidth / $sourceImageHeight) < ($arSize["width"] / $arSize["height"])) ?
							$arSize["width"] / $sourceImageWidth : $arSize["height"] / $sourceImageHeight;
						
						$x = max(0, ceil($sourceImageWidth / 2 - ($arSize["width"] / 2) / $ratio));
						$y = max(0, ceil($sourceImageHeight / 2 - ($arSize["height"] / 2) / $ratio));
						
						$arDestinationSize["width"] = $arSize["width"];
						$arDestinationSize["height"] = $arSize["height"];
						
						$arSourceSize["x"] = $x;
						$arSourceSize["y"] = $y;
						$arSourceSize["width"] = ceil($arSize["width"] / $ratio);
						$arSourceSize["height"] = ceil($arSize["height"] / $ratio);
						break;
					
					default:
						if ($resizeType == BX_RESIZE_IMAGE_PROPORTIONAL_ALT)
						{
							$width = max($sourceImageWidth, $sourceImageHeight);
							$height = min($sourceImageWidth, $sourceImageHeight);
						}
						else
						{
							$width = $sourceImageWidth;
							$height = $sourceImageHeight;
						}
						$ResizeCoeff["width"] = $arSize["width"] / $width;
						$ResizeCoeff["height"] = $arSize["height"] / $height;
						
						$iResizeCoeff = min($ResizeCoeff["width"], $ResizeCoeff["height"]);
//						$iResizeCoeff = ((0 < $iResizeCoeff) && ($iResizeCoeff < 1) ? $iResizeCoeff : 1);
						$bNeedCreatePicture = ($iResizeCoeff > 0 ? true : false);
						
						$arDestinationSize["width"] = max(1, intval(ceil($iResizeCoeff * $sourceImageWidth)));
						$arDestinationSize["height"] = max(1, intval(ceil($iResizeCoeff * $sourceImageHeight)));
						
						$arSourceSize["x"] = 0;
						$arSourceSize["y"] = 0;
						$arSourceSize["width"] = $sourceImageWidth;
						$arSourceSize["height"] = $sourceImageHeight;
						break;
				}
			}
			else
			{
				$arSourceSize = array("x" => 0, "y" => 0, "width" => $sourceImageWidth, "height" => $sourceImageHeight);
				$arDestinationSize = array("x" => 0, "y" => 0, "width" => $sourceImageWidth, "height" => $sourceImageHeight);
			}
		}
	}
}