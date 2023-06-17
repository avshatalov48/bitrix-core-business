<?php
IncludeModuleLangFile(__FILE__);

class CCloudStorage
{
	const FILE_SKIPPED = 0;
	const FILE_MOVED = 1;
	const FILE_PARTLY_UPLOADED = 2;
	const FILE_UPLOAD_ERROR = 3;

	public static $part_size = 0;
	public static $part_count = 0;

	private static $_services = /*.(array[string]CCloudStorageService).*/
		null;

	public static $file_skip_reason = '';
	protected static $lockId = '';
	/**
	 * @return void
	 */
	static function _init()
	{
		if (!isset(self::$_services))
		{
			$obService = /*.(CCloudStorageService).*/
				null;
			self::$_services = /*.(array[string]CCloudStorageService).*/
				array();
			foreach (GetModuleEvents("clouds", "OnGetStorageService", true) as $arEvent)
			{
				$obService = ExecuteModuleEventEx($arEvent);
				if (is_object($obService))
					self::$_services[$obService->GetID()] = $obService;
			}
		}
	}

	/**
	 * @param string $ID
	 * @return CCloudStorageService
	 */
	public static function GetServiceByID($ID)
	{
		self::_init();
		if (array_key_exists($ID, self::$_services))
			return self::$_services[$ID];
		else
			return null;
	}

	/**
	 * @return array[string]CCloudStorageService
	 */
	public static function GetServiceList()
	{
		self::_init();
		return self::$_services;
	}

	/**
	 * @param string $ID
	 * @return array[string]string|false
	 */
	public static function GetServiceLocationList($ID)
	{
		$obService = CCloudStorage::GetServiceByID($ID);
		if (is_object($obService))
			return $obService->GetLocationList();
		else
			return /*.(array[string]string).*/
				array();
	}

	/**
	 * @param string $ID
	 * @return string
	 */
	public static function GetServiceDescription($ID)
	{
		$obService = CCloudStorage::GetServiceByID($ID);
		if (is_object($obService))
			return $obService->GetName();
		else
			return "";
	}

	/**
	 * @param array [string]string $arFile
	 * @param string $strFileName
	 * @return CCloudStorageBucket
	 */
	public static function FindBucketForFile($arFile, $strFileName)
	{
		if (array_key_exists("size", $arFile) && $arFile["size"] > 0)
			$file_size = intval($arFile["size"]);
		elseif (array_key_exists("FILE_SIZE", $arFile) && $arFile["FILE_SIZE"] > 0)
			$file_size = intval($arFile["FILE_SIZE"]);
		else
			$file_size = intval($arFile["file_size"]);

		self::$file_skip_reason = '';
		$activeCounter = 0;
		$writableCounter = 0;
		foreach (CCloudStorageBucket::GetAllBuckets() as $bucket)
		{
			if ($bucket["ACTIVE"] !== "Y")
			{
				continue;
			}
			$activeCounter++;


			if ($bucket["READ_ONLY"] === "Y")
			{
				continue;
			}
			$writableCounter++;

			foreach ($bucket["FILE_RULES_COMPILED"] as $rule)
			{
				if ($rule["MODULE_MASK"] != "")
				{
					$bMatchModule = (preg_match($rule["MODULE_MASK"], $arFile["MODULE_ID"]) > 0);
				}
				else
				{
					$bMatchModule = true;
				}

				if ($rule["EXTENTION_MASK"] != "")
				{
					$bMatchExtention =
						(preg_match($rule["EXTENTION_MASK"], $strFileName) > 0)
						|| (preg_match($rule["EXTENTION_MASK"], $arFile["ORIGINAL_NAME"]) > 0);
				}
				else
				{
					$bMatchExtention = true;
				}

				if ($rule["SIZE_ARRAY"])
				{
					$bMatchSize = false;
					foreach ($rule["SIZE_ARRAY"] as $size)
					{
						if (
							($file_size >= $size[0])
							&& ($size[1] === 0.0 || $file_size <= $size[1])
						)
							$bMatchSize = true;
					}
				}
				else
				{
					$bMatchSize = true;
				}

				if (!$bMatchModule)
				{
					self::$file_skip_reason = 'NO_FILE_MODULE_MATCH';
				}
				elseif (!$bMatchExtention)
				{
					self::$file_skip_reason = 'NO_FILE_EXTENTION_MATCH';
				}
				elseif (!$bMatchSize)
				{
					self::$file_skip_reason = 'NO_FILE_SIZE_MATCH';
				}

				if ($bMatchModule && $bMatchExtention && $bMatchSize)
				{
					return new CCloudStorageBucket(intval($bucket["ID"]));
				}
			}
		}

		if (!$activeCounter)
		{
			self::$file_skip_reason = 'NO_ACTIVE_BUCKETS';
		}
		elseif (!$writableCounter)
		{
			self::$file_skip_reason = 'NO_WRITABLE_BUCKETS';
		}

		return null;
	}

	/**
	 * @param array [string]string $arFile
	 * @param array [string]string $arResizeParams
	 * @param array [string]mixed $callbackData
	 * @param bool $bNeedResize
	 * @param array [string]string $sourceImageFile
	 * @param array [string]string $cacheImageFileTmp
	 * @return bool
	 */
	public static function OnBeforeResizeImage($arFile, $arResizeParams, &$callbackData, &$bNeedResize, &$sourceImageFile, &$cacheImageFileTmp)
	{
		$callbackData = null;

		if (intval($arFile["HANDLER_ID"]) <= 0)
			return false;

		$obSourceBucket = new CCloudStorageBucket(intval($arFile["HANDLER_ID"]));
		if (!$obSourceBucket->Init())
			return false;

		$callbackData = /*.(array[string]mixed).*/
			array();
		$callbackData["obSourceBucket"] = $obSourceBucket;

		//Assume target bucket same as source
		$callbackData["obTargetBucket"] = $obTargetBucket = $obSourceBucket;

		//if original file bucket is read only
		if ($obSourceBucket->READ_ONLY === "Y") //Try to find bucket with write rights
		{
			$bucket = CCloudStorage::FindBucketForFile($arFile, $arFile["FILE_NAME"]);
			if (!is_object($bucket))
				return false;
			if ($bucket->Init())
			{
				$callbackData["obTargetBucket"] = $obTargetBucket = $bucket;
			}
		}

		if (!$arFile["SRC"])
		{
			$arFile["SRC"] = $obSourceBucket->GetFileSRC($arFile, false);
		}

		if (defined("BX_MOBILE") && constant("BX_MOBILE") === true)
			$bImmediate = true;
		else
			$bImmediate = $arResizeParams[5];

		$callbackData["cacheID"] = $arFile["ID"]."/".md5(serialize($arResizeParams));
		$callbackData["cacheOBJ"] = new CPHPCache;
		$callbackData["fileDIR"] = "/"."resize_cache/".$callbackData["cacheID"]."/".$arFile["SUBDIR"];
		$callbackData["fileNAME"] = $arFile["FILE_NAME"];
		$callbackData["fileURL"] = $callbackData["fileDIR"]."/".$callbackData["fileNAME"];

		$result = true;
		if ($callbackData["cacheOBJ"]->StartDataCache(CACHED_clouds_file_resize, $callbackData["cacheID"], "clouds"))
		{
			$cacheImageFile = $obTargetBucket->GetFileSRC($callbackData["fileURL"], false);
			$arDestinationSize = array();

			//Check if it is cache file was deleted, but there was a successful resize
			$delayInfo = $bImmediate ? false : CCloudStorage::ResizeImageFileGet($cacheImageFile);
			if (is_array($delayInfo) && ($delayInfo["ERROR_CODE"] < 10))
			{
				$callbackData["cacheSTARTED"] = true;
				if ($arFile["FILE_SIZE"] > 1)
					$callbackData["fileSize"] = $arFile["FILE_SIZE"];
				$bNeedResize = false;
				$result = true;
			}
			//Check if it is cache file was deleted, but not the file in the cloud
			elseif ($fs = $obTargetBucket->FileExists($callbackData["fileURL"]))
			{
				//If file was resized before the fact was registered
				if (COption::GetOptionString("clouds", "delayed_resize") === "Y")
				{
					CCloudStorage::ResizeImageFileAdd(
						$arDestinationSize,
						$arFile,
						$cacheImageFile,
						$arResizeParams,
						9 //already where
					);
				}

				$callbackData["cacheSTARTED"] = true;
				if ($fs > 1)
					$callbackData["fileSize"] = $fs;
				$bNeedResize = false;
				$result = true;
			}
			else
			{
				$callbackData["tmpFile"] = CFile::GetTempName('', $arFile["FILE_NAME"]);
				$callbackData["tmpFile"] = preg_replace("#[\\\\\\/]+#", "/", $callbackData["tmpFile"]);

				if (
					!$bImmediate
					&& COption::GetOptionString("clouds", "delayed_resize") === "Y"
					&& CCloudStorage::ResizeImageFileDelay(
						$arDestinationSize,
						$arFile,
						$cacheImageFile,
						$arResizeParams
					)
				)
				{
					$callbackData["delayedResize"] = true;
					$callbackData["cacheSTARTED"] = false;
					$bNeedResize = false;
					$callbackData["cacheOBJ"]->AbortDataCache();
					$callbackData["cacheVARS"] = array(
						"cacheImageFile" => $cacheImageFile,
						"width" => $arDestinationSize["width"],
						"height" => $arDestinationSize["height"],
						"size" => null,
					);
					$result = true;
				}
				elseif ($obSourceBucket->DownloadToFile($arFile, $callbackData["tmpFile"]))
				{
					$callbackData["cacheSTARTED"] = true;
					$bNeedResize = true;
					$sourceImageFile = $callbackData["tmpFile"];
					$cacheImageFileTmp = CFile::GetTempName('', $arFile["FILE_NAME"]);
					$result = true;
				}
				else
				{
					$callbackData["cacheSTARTED"] = false;
					$bNeedResize = false;
					$callbackData["cacheOBJ"]->AbortDataCache();
					$result = false;
				}
			}
		}
		else
		{
			$callbackData["cacheSTARTED"] = false;
			$callbackData["cacheVARS"] = $callbackData["cacheOBJ"]->GetVars();
			$bNeedResize = false;
			$result = true;
		}

		return $result;
	}

	public static function OnAfterResizeImage($arFile, $arResizeParams, &$callbackData, &$cacheImageFile, &$cacheImageFileTmp, &$arImageSize)
	{
		global $arCloudImageSizeCache;
		$io = CBXVirtualIo::GetInstance();

		if (!is_array($callbackData))
			return false;

		if ($callbackData["cacheSTARTED"])
		{
			/** @var CCloudStorageBucket $obTargetBucket */
			$obTargetBucket = $callbackData["obTargetBucket"];
			/** @var CPHPCache $cacheOBJ */
			$cacheOBJ = $callbackData["cacheOBJ"];

			if (isset($callbackData["tmpFile"])) //have to upload to the cloud
			{
				$arFileToStore = CFile::MakeFileArray($io->GetPhysicalName($cacheImageFileTmp));
				if (!$arFileToStore)
				{
					$cacheOBJ->AbortDataCache();

					$tmpFile = $io->GetPhysicalName($callbackData["tmpFile"]);
					unlink($tmpFile);
					@rmdir(mb_substr($tmpFile, 0, -mb_strlen(bx_basename($tmpFile))));

					unlink($cacheImageFileTmp);
					@rmdir(mb_substr($cacheImageFileTmp, 0, -mb_strlen(bx_basename($cacheImageFileTmp))));

					$obSourceBucket = new CCloudStorageBucket(intval($arFile["HANDLER_ID"]));
					if ($obSourceBucket->Init())
					{
						$cacheImageFile = $obSourceBucket->GetFileSRC($arFile, false);
					}

					return false;
				}

				if (!preg_match("/^image\\//", $arFileToStore["type"]))
					$arFileToStore["type"] = $arFile["CONTENT_TYPE"];

				if ($obTargetBucket->SaveFile($callbackData["fileURL"], $arFileToStore))
				{
					$cacheImageFile = $obTargetBucket->GetFileSRC($callbackData["fileURL"], false);

					$arImageSize = CFile::GetImageSize($cacheImageFileTmp);
					$arImageSize[2] = filesize($io->GetPhysicalName($cacheImageFileTmp));
					$iFileSize = filesize($arFileToStore["tmp_name"]);

					if (!is_array($arImageSize))
						$arImageSize = array(0, 0);
					$cacheOBJ->EndDataCache(array(
						"cacheImageFile" => $cacheImageFile,
						"width" => $arImageSize[0],
						"height" => $arImageSize[1],
						"size" => $arImageSize[2],
					));

					$tmpFile = $io->GetPhysicalName($callbackData["tmpFile"]);
					unlink($tmpFile);
					@rmdir(mb_substr($tmpFile, 0, -mb_strlen(bx_basename($tmpFile))));

					$arCloudImageSizeCache[$cacheImageFile] = $arImageSize;

					$obTargetBucket->IncFileCounter($iFileSize);

					if (
						COption::GetOptionString("clouds", "delayed_resize") === "Y"
						&& !is_array(CCloudStorage::ResizeImageFileGet($cacheImageFile))
					)
					{
						$arDestinationSize = array();
						CCloudStorage::ResizeImageFileAdd(
							$arDestinationSize,
							$arFile,
							$cacheImageFile,
							$arResizeParams,
							9 //already there
						);
					}
				}
				else
				{
					$cacheOBJ->AbortDataCache();

					$tmpFile = $io->GetPhysicalName($callbackData["tmpFile"]);
					unlink($tmpFile);
					@rmdir(mb_substr($tmpFile, 0, -mb_strlen(bx_basename($tmpFile))));

					unlink($cacheImageFileTmp);
					@rmdir(mb_substr($cacheImageFileTmp, 0, -mb_strlen(bx_basename($cacheImageFileTmp))));

					$obSourceBucket = new CCloudStorageBucket(intval($arFile["HANDLER_ID"]));
					if ($obSourceBucket->Init())
					{
						$cacheImageFile = $obSourceBucket->GetFileSRC($arFile, false);
					}

					return false;
				}
			}
			else //the file is already in the cloud
			{
				$bNeedCreatePicture = false;
				$arSourceSize = array("x" => 0, "y" => 0, "width" => 0, "height" => 0);
				$arDestinationSize = array("x" => 0, "y" => 0, "width" => 0, "height" => 0);
				CFile::ScaleImage($arFile["WIDTH"], $arFile["HEIGHT"], $arResizeParams[0], $arResizeParams[1], $bNeedCreatePicture, $arSourceSize, $arDestinationSize);

				$cacheImageFile = $obTargetBucket->GetFileSRC($callbackData["fileURL"], false);
				$arImageSize = array(
					$arDestinationSize["width"],
					$arDestinationSize["height"],
					isset($callbackData["fileSize"])? $callbackData["fileSize"]: $obTargetBucket->GetFileSize($callbackData["fileURL"]),
				);
				$cacheOBJ->EndDataCache(array(
					"cacheImageFile" => $cacheImageFile,
					"width" => $arImageSize[0],
					"height" => $arImageSize[1],
					"size" => $arImageSize[2],
				));

				$arCloudImageSizeCache[$cacheImageFile] = $arImageSize;
			}
		}
		elseif (is_array($callbackData["cacheVARS"]))
		{
			$cacheImageFile = $callbackData["cacheVARS"]["cacheImageFile"];
			$arImageSize = array(
				$callbackData["cacheVARS"]["width"],
				$callbackData["cacheVARS"]["height"],
				$callbackData["cacheVARS"]["size"],
			);
			$arCloudImageSizeCache[$cacheImageFile] = $arImageSize;
		}
		else
		{
			return false;
		}

		foreach (GetModuleEvents("clouds", "OnAfterResizeImage", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$callbackData["delayedResize"] ?? false, &$cacheImageFile]);
		}

		return true;
	}

	public static function ResizeImageFileGet($destinationFile)
	{
		global $DB;
		$destinationFile = preg_replace("/^https?:/i", "", $destinationFile);
		$destinationFile = CCloudUtil::URLEncode($destinationFile, "UTF-8", true);
		$q = $DB->Query("
			select
				ID
				,ERROR_CODE
				,FILE_ID
				,".$DB->DateToCharFunction("TIMESTAMP_X", "FULL")." TIMESTAMP_X
			from b_clouds_file_resize
			where TO_PATH = '".$DB->ForSql($destinationFile)."'
		");
		$a = $q->Fetch();
		return $a;
	}

	public static function ResizeImageFileAdd(&$arDestinationSize, $sourceFile, $destinationFile, $arResizeParams, $errorCode = 0)
	{
		global $DB;
		$destinationFile = preg_replace("/^https?:/i", "", $destinationFile);
		$destinationFile = CCloudUtil::URLEncode($destinationFile, "UTF-8", true);
		$q = $DB->Query("
			select
				ID
				,ERROR_CODE
				,PARAMS
				,".$DB->DateToCharFunction("TIMESTAMP_X", "FULL")." TIMESTAMP_X
			from b_clouds_file_resize
			where TO_PATH = '".$DB->ForSql($destinationFile)."'
		");

		$a = $q->Fetch();
		if ($a && $a["ERROR_CODE"] >= 10 && $a["ERROR_CODE"] < 20)
		{
			$DB->Query("DELETE from b_clouds_file_resize WHERE ID = ".$a["ID"]);
			$a = false;
		}

		if (!$a)
		{
			$arResizeParams["type"] = $sourceFile["CONTENT_TYPE"];
			$DB->Add("b_clouds_file_resize", array(
				"~TIMESTAMP_X" => $DB->CurrentTimeFunction(),
				"ERROR_CODE" => intval($errorCode),
				"PARAMS" => serialize($arResizeParams),
				"FROM_PATH" => CCloudUtil::URLEncode($sourceFile["SRC"], "UTF-8", true),
				"TO_PATH" => $destinationFile,
				"FILE_ID" => $sourceFile["ID"],
			));
		}
	}

	public static function ResizeImageFileDelay(&$arDestinationSize, $sourceFile, $destinationFile, $arResizeParams)
	{
		global $DB;
		$destinationFile = preg_replace("/^https?:/i", "", $destinationFile);
		$destinationFile = CCloudUtil::URLEncode($destinationFile, "UTF-8", true);
		$q = $DB->Query("
			select
				ID
				,ERROR_CODE
				,PARAMS
				,".$DB->DateToCharFunction("TIMESTAMP_X", "FULL")." TIMESTAMP_X
			from b_clouds_file_resize
			where TO_PATH = '".$DB->ForSql($destinationFile)."'
		");
		if ($resize = $q->Fetch())
		{
			if ($resize["ERROR_CODE"] < 10)
			{
				$arResizeParams = unserialize($resize["PARAMS"], ['allowed_classes' => false]);
				$id = $resize["ID"];
			} //Give it a try
			elseif (
				$resize["ERROR_CODE"] >= 10
				&& $resize["ERROR_CODE"] < 20
				&& (MakeTimeStamp($resize["TIMESTAMP_X"]) + 300/*5min*/) < (time() + CTimeZone::GetOffset())
			)
			{
				$DB->Query("
					UPDATE b_clouds_file_resize
					SET ERROR_CODE='1'
					WHERE ID=".$resize["ID"]."
				");
				$arResizeParams = unserialize($resize["PARAMS"], ['allowed_classes' => false]);
				$id = $resize["ID"];
			}
			else
			{
				return false;
			}
		}
		else
		{
			$id = 0;
		}

		$sourceImageWidth = $sourceFile["WIDTH"];
		$sourceImageHeight = $sourceFile["HEIGHT"];
		$arSize = $arResizeParams[0];
		$resizeType = $arResizeParams[1];
		$arWaterMark = $arResizeParams[2];
		$jpgQuality = $arResizeParams[3];
		$arFilters = $arResizeParams[4];
		$bNeedCreatePicture = false;
		$arSourceSize = array("x" => 0, "y" => 0, "width" => 0, "height" => 0);
		$arDestinationSize = array("x" => 0, "y" => 0, "width" => 0, "height" => 0);

		CFile::ScaleImage(
			$sourceImageWidth,
			$sourceImageHeight,
			$arSize,
			$resizeType,
			$bNeedCreatePicture,
			$arSourceSize,
			$arDestinationSize
		);
		$bNeedCreatePicture |= is_array($arWaterMark) && !empty($arWaterMark);
		$bNeedCreatePicture |= is_array($arFilters) && !empty($arFilters);

		if ($bNeedCreatePicture)
		{
			if ($id <= 0)
			{
				$arResizeParams["type"] = $sourceFile["CONTENT_TYPE"];
				$id = $DB->Add("b_clouds_file_resize", array(
					"~TIMESTAMP_X" => $DB->CurrentTimeFunction(),
					"ERROR_CODE" => "2",
					"PARAMS" => serialize($arResizeParams),
					"FROM_PATH" => CCloudUtil::URLEncode($sourceFile["SRC"], "UTF-8", true),
					"TO_PATH" => $destinationFile,
					"FILE_ID" => $sourceFile["ID"],
				));
			}

			return $id > 0;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param CCloudStorageBucket $obBucket
	 * @param string $path
	 * @return bool
	 */
	public static function ResizeImageFileCheck($obBucket, $path)
	{
		global $DB;
		$io = CBXVirtualIo::GetInstance();

		$path = preg_replace("/^https?:/i", "", $path);
		$path = CCloudUtil::URLEncode($path, "UTF-8", true);
		$q = $DB->Query("
			select
				ID
				,ERROR_CODE
				,TO_PATH
				,FROM_PATH
				,PARAMS
				,".$DB->DateToCharFunction("TIMESTAMP_X", "FULL")." TIMESTAMP_X
			from b_clouds_file_resize
			where TO_PATH = '".$DB->ForSql($path)."'
		");
		$task = $q->Fetch();
		if (!$task)
			return false;

		//File in the Sky with Diamonds
		if ($task["ERROR_CODE"] == 9)
		{
			return true;
		}

		//Fatal error
		if ($task["ERROR_CODE"] >= 20)
		{
			return false;
		}

		//Recoverable error
		if ($task["ERROR_CODE"] >= 10 && $task["ERROR_CODE"] < 20)
		{
			if ((MakeTimeStamp($task["TIMESTAMP_X"]) + 300/*5min*/) > (time() + CTimeZone::GetOffset()))
				return false;
		}

		$DB->Query("
			UPDATE b_clouds_file_resize
			SET ERROR_CODE = '11'
			WHERE ID = ".$task["ID"]."
		");

		$tmpFile = CFile::MakeFileArray($task["FROM_PATH"]);
		// if (!is_array($tmpFile) || !file_exists($tmpFile["tmp_name"]))
		// {
		// 	$tmpFile = CFile::MakeFileArray(\Bitrix\Main\Web\Uri::urnEncode($task["FROM_PATH"], "UTF-8"));
		// }

		if (!is_array($tmpFile) || !file_exists($tmpFile["tmp_name"]))
		{
			$DB->Query("
				UPDATE b_clouds_file_resize
				SET ERROR_CODE = '22'
				WHERE ID = ".$task["ID"]."
			");
			return false;
		}

		$arResizeParams = unserialize($task["PARAMS"], ['allowed_classes' => false]);
		if (!is_array($arResizeParams))
		{
			$DB->Query("
				UPDATE b_clouds_file_resize
				SET ERROR_CODE = '23'
				WHERE ID = ".$task["ID"]."
			");
			return false;
		}

		$DB->Query("
			UPDATE b_clouds_file_resize
			SET ERROR_CODE = '14'
			WHERE ID = ".$task["ID"]."
		");

		$arSize = $arResizeParams[0];
		$resizeType = $arResizeParams[1];
		$arWaterMark = $arResizeParams[2];
		$jpgQuality = $arResizeParams[3];
		$arFilters = $arResizeParams[4];

		$from_path = $io->GetLogicalName($tmpFile["tmp_name"]);
		$to_path = \Bitrix\Main\Text\Encoding::convertEncoding(rawurldecode($task["TO_PATH"]), "UTF-8", LANG_CHARSET);
		$to_path = CFile::GetTempName('', bx_basename($to_path));

		if (!CFile::ResizeImageFile($from_path, $to_path, $arSize, $resizeType, $arWaterMark, $jpgQuality, $arFilters))
		{
			$DB->Query("
				UPDATE b_clouds_file_resize
				SET ERROR_CODE = '25'
				WHERE ID = ".$task["ID"]."
			");
			return false;
		}

		$DB->Query("
			UPDATE b_clouds_file_resize
			SET ERROR_CODE = '16'
			WHERE ID = ".$task["ID"]."
		");

		$fileToStore = CFile::MakeFileArray($io->GetPhysicalName($to_path));
		if ($arResizeParams["type"] && !preg_match("/^image\\//", $fileToStore["type"]))
		{
			$fileToStore["type"] = $arResizeParams["type"];
		}

		$baseURL = preg_replace("/^https?:/i", "", $obBucket->GetFileSRC("/"));
		$pathToStore = mb_substr($task["TO_PATH"], mb_strlen($baseURL) - 1);
		$pathToStore = \Bitrix\Main\Text\Encoding::convertEncoding(rawurldecode($pathToStore), "UTF-8", LANG_CHARSET);
		if (!$obBucket->SaveFile($pathToStore, $fileToStore))
		{
			$DB->Query("
				UPDATE b_clouds_file_resize
				SET ERROR_CODE = '27'
				WHERE ID = ".$task["ID"]."
			");
			return false;
		}
		$obBucket->IncFileCounter($fileToStore["size"]);
		$DB->Query("
			UPDATE b_clouds_file_resize
			SET ERROR_CODE = '9'
			WHERE ID = ".$task["ID"]."
		");
		return true;
	}

	public static function OnMakeFileArray($arSourceFile, &$arDestination)
	{
		if (!is_array($arSourceFile))
		{
			$file = $arSourceFile;
			if (mb_substr($file, 0, mb_strlen($_SERVER["DOCUMENT_ROOT"])) == $_SERVER["DOCUMENT_ROOT"])
				$file = ltrim(mb_substr($file, mb_strlen($_SERVER["DOCUMENT_ROOT"])), "/");

			if (!preg_match("/^https?:\\/\\//", $file))
				return false;

			$bucket = CCloudStorage::FindBucketByFile($file);
			if (!is_object($bucket))
				return false;

			$filePath = mb_substr($file, mb_strlen($bucket->GetFileSRC("/")) - 1);
			$filePath = \Bitrix\Main\Text\Encoding::convertEncoding(rawurldecode($filePath), "UTF-8", LANG_CHARSET);

			$io = CBXVirtualIo::GetInstance();
			$target = CFile::GetTempName('', bx_basename($filePath));
			$target = preg_replace("#[\\\\\\/]+#", "/", $target);

			if ($bucket->DownloadToFile($filePath, $target))
			{
				$arDestination = $io->GetPhysicalName($target);
			}

			return true;
		}
		else
		{
			if ($arSourceFile["HANDLER_ID"] <= 0)
				return false;

			$bucket = new CCloudStorageBucket($arSourceFile["HANDLER_ID"]);
			if (!$bucket->Init())
				return false;

			$target = CFile::GetTempName('', $arSourceFile["FILE_NAME"]);
			$target = preg_replace("#[\\\\\\/]+#", "/", $target);

			if ($bucket->DownloadToFile($arSourceFile, $target))
			{
				$arDestination["name"] = ($arSourceFile['ORIGINAL_NAME'] <> ''? $arSourceFile['ORIGINAL_NAME']: $arSourceFile['FILE_NAME']);
				$arDestination["size"] = $arSourceFile['FILE_SIZE'];
				$arDestination["type"] = $arSourceFile['CONTENT_TYPE'];
				$arDestination["description"] = $arSourceFile['DESCRIPTION'];
				$arDestination["tmp_name"] = $target;
			}

			return true;
		}
	}

	public static function OnFileDelete($arFile)
	{
		global $DB;

		if ($arFile["HANDLER_ID"] <= 0)
			return false;

		$bucket = new CCloudStorageBucket($arFile["HANDLER_ID"]);
		if ((!$bucket->Init()) || ($bucket->READ_ONLY === "Y"))
			return false;

		$result = $bucket->DeleteFile("/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"]);
		if ($result)
			$bucket->DecFileCounter($arFile["FILE_SIZE"]);

		$path = '/resize_cache/'.$arFile["ID"]."/";
		$arCloudFiles = $bucket->ListFiles($path, true);
		if (is_array($arCloudFiles["file"]))
		{
			$delete_size = 0;
			foreach ($arCloudFiles["file"] as $i => $file_name)
			{
				$tmp = $bucket->DeleteFile($path.$file_name);
				if ($tmp)
				{
					$bucket->DecFileCounter($arCloudFiles["file_size"][$i]);
					$delete_size += $arCloudFiles["file_size"][$i];
				}
			}
			/****************************** QUOTA ******************************/
			if($delete_size > 0 && COption::GetOptionInt("main", "disk_space") > 0)
				CDiskQuota::updateDiskQuota("file", $delete_size, "delete");
			/****************************** QUOTA ******************************/
		}

		$DB->Query("
			DELETE FROM b_clouds_file_resize
			WHERE FILE_ID = ".intval($arFile["ID"])."
		", true);

		\Bitrix\Clouds\FileHashTable::deleteByFilePath($bucket->ID, "/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"]);

		return $result;
	}

	public static function DeleteDirFilesEx($path)
	{
		$path = rtrim($path, "/")."/";
		foreach (CCloudStorageBucket::GetAllBuckets() as $bucket)
		{
			$obBucket = new CCloudStorageBucket($bucket["ID"]);
			if (
				$obBucket->Init()
				&& ($bucket->READ_ONLY == "N")
				&& ($bucket->ACTIVE == "Y")
			)
			{
				$arCloudFiles = $obBucket->ListFiles($path, true);
				if (is_array($arCloudFiles["file"]))
				{
					foreach ($arCloudFiles["file"] as $i => $file_name)
					{
						$tmp = $obBucket->DeleteFile($path.$file_name);
						if ($tmp)
							$obBucket->DecFileCounter($arCloudFiles["file_size"][$i]);
					}
				}
			}
		}
	}

	public static function OnFileCopy(&$arFile, $newPath = "")
	{
		if ($arFile["HANDLER_ID"] <= 0)
			return false;

		$bucket = new CCloudStorageBucket($arFile["HANDLER_ID"]);
		if (!$bucket->Init())
			return false;

		if ($bucket->READ_ONLY == "Y")
			return false;

		$filePath = "";
		$newName = "";

		if($newPath <> '')
		{
			$filePath = "/".trim(str_replace("//", "/", $newPath), "/");
		}
		else
		{
			$strFileExt = strrchr($arFile["FILE_NAME"], ".");
			while(true)
			{
				$newName = md5(uniqid(mt_rand(), true)).$strFileExt;
				$filePath = "/".$arFile["SUBDIR"]."/".$newName;
				if(!$bucket->FileExists($filePath))
				{
					break;
				}
			}
		}

		if ($newPath == '')
		{
			if ($arFile["EXTERNAL_ID"] == "")
			{
				$arFile["EXTERNAL_ID"] = md5(mt_rand());
			}

			\Bitrix\Clouds\FileSaveTable::startFileOperation(
				$bucket->ID
				,$arFile["SUBDIR"]
				,$newName
				,$arFile["EXTERNAL_ID"]
			);
		}

		$result = $bucket->FileCopy($arFile, $filePath);

		if ($result)
		{
			$copySize = $arFile["FILE_SIZE"];
			$bucket->IncFileCounter($copySize);
			\Bitrix\Clouds\FileSaveTable::setFileSize(
				$bucket->ID
				,$arFile["SUBDIR"]
				,$newName
				,$copySize
			);

			if($newPath <> '')
			{
				$arFile["FILE_NAME"] = bx_basename($filePath);
				$arFile["SUBDIR"] = mb_substr($filePath, 1, -(mb_strlen(bx_basename($filePath)) + 1));
			}
			else
			{
				$arFile["FILE_NAME"] = $newName;
			}
		}

		return $result;
	}

	public static function OnGetFileSRC($arFile)
	{
		if ($arFile["HANDLER_ID"] <= 0)
		{
			return false;
		}

		$bucket = new CCloudStorageBucket($arFile["HANDLER_ID"]);
		if ($bucket->Init())
		{
			return $bucket->GetFileSRC($arFile, false);
		}

		return false;
	}

	protected static function _delete_file($file)
	{
		if (is_array($file))
		{
			CCloudStorage::OnFileDelete($file);
		}
		elseif (is_string($file) && file_exists($file))
		{
			unlink($file);
			@rmdir(mb_substr($file, 0, -mb_strlen(bx_basename($file))));
		}
	}

	public static function MoveFile($arFile, $obTargetBucket)
	{
		$io = CBXVirtualIo::GetInstance();
		self::$file_skip_reason = '';

		//Try to find suitable bucket for the file
		$bucket = CCloudStorage::FindBucketForFile($arFile, $arFile["FILE_NAME"]);
		if (!is_object($bucket))
		{
			return CCloudStorage::FILE_SKIPPED;
		}

		if (!$bucket->Init())
		{
			self::$file_skip_reason = 'FAILED_TO_INIT_BUCKET';
			return CCloudStorage::FILE_SKIPPED;
		}

		//Check if this is same bucket as the target
		if ($bucket->ID != $obTargetBucket->ID)
		{
			self::$file_skip_reason = 'FOUND_BUCKET_DOES_NOT_MATCH_TARGET';
			return CCloudStorage::FILE_SKIPPED;
		}

		$filePath = "/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"];
		$filePath = preg_replace("#[\\\\\\/]+#", "/", $filePath);

		if ($bucket->FileExists($filePath))
		{
			self::$file_skip_reason = 'CLOUD_FILE_EXISTS';
			return CCloudStorage::FILE_SKIPPED;
		}

		if ($arFile["FILE_SIZE"] > $bucket->GetService()->GetMinUploadPartSize())
		{
			$obUpload = new CCloudStorageUpload($filePath);
			if (!$obUpload->isStarted())
			{
				if ($arFile["HANDLER_ID"])
				{
					$ar = array();
					if (!CCloudStorage::OnMakeFileArray($arFile, $ar))
					{
						self::$file_skip_reason = 'FAILED_TO_DOWNLOAD_FILE_1';
						return CCloudStorage::FILE_SKIPPED;
					}

					if (!isset($ar["tmp_name"]))
					{
						self::$file_skip_reason = 'FAILED_TO_DOWNLOAD_FILE_2';
						return CCloudStorage::FILE_SKIPPED;
					}
				}
				else
				{
					$ar = CFile::MakeFileArray($arFile["ID"]);
					if (!isset($ar["tmp_name"]))
					{
						self::$file_skip_reason = 'FAILED_TO_GET_SOURCE_FILE_INFO_1';
						return CCloudStorage::FILE_SKIPPED;
					}
				}

				$temp_file = CTempFile::GetDirectoryName(2, "clouds").bx_basename($arFile["FILE_NAME"]);
				$temp_fileX = $io->GetPhysicalName($temp_file);
				CheckDirPath($temp_fileX);

				if (file_exists($ar["tmp_name"]))
				{
					$sourceFile = $ar["tmp_name"];
				}
				elseif (file_exists($io->GetPhysicalName($ar["tmp_name"])))
				{
					$sourceFile = $io->GetPhysicalName($ar["tmp_name"]);
				}
				else
				{
					self::$file_skip_reason = 'FAILED_TO_FIND_SOURCE_FILE';
					return CCloudStorage::FILE_SKIPPED;
				}

				if (!copy($sourceFile, $temp_fileX))
				{
					self::$file_skip_reason = 'FAILED_TO_COPY_SOURCE_FILE';
					return CCloudStorage::FILE_SKIPPED;
				}

				if ($obUpload->Start($bucket->ID, $arFile["FILE_SIZE"], $arFile["CONTENT_TYPE"], $temp_file))
				{
					return CCloudStorage::FILE_PARTLY_UPLOADED;
				}
				else
				{
					self::$file_skip_reason = 'FAILED_TO_START_UPLOAD';
					return CCloudStorage::FILE_SKIPPED;
				}
			}
			else
			{
				$temp_file = $obUpload->getTempFileName();
				$temp_fileX = $io->GetPhysicalName($temp_file);

				$fp = fopen($temp_fileX, "rb");
				if (!is_resource($fp))
				{
					self::$file_skip_reason = 'FAILED_TO_READ_SOURCE_FILE';
					return CCloudStorage::FILE_SKIPPED;
				}

				$pos = $obUpload->getPos();
				if ($pos > filesize($temp_fileX))
				{
					if ($obUpload->Finish())
					{
						$bucket->IncFileCounter(filesize($temp_fileX));

						if ($arFile["HANDLER_ID"])
						{
							self::_delete_file($arFile);
						}
						else
						{
							$ar = CFile::MakeFileArray($arFile["ID"]);
							$fileNameX = $io->GetPhysicalName($ar["tmp_name"]);
							self::_delete_file($fileNameX);
						}

						return CCloudStorage::FILE_MOVED;
					}
					else
					{
						self::$file_skip_reason = 'FAILED_TO_FINISH_UPLOAD';
						return CCloudStorage::FILE_SKIPPED;
					}
				}

				fseek($fp, $pos);
				self::$part_count = $obUpload->GetPartCount();
				self::$part_size = $obUpload->getPartSize();
				$part = fread($fp, self::$part_size);
				while ($obUpload->hasRetries())
				{
					if ($obUpload->Next($part))
					{
						return CCloudStorage::FILE_PARTLY_UPLOADED;
					}
				}

				self::$file_skip_reason = 'FAILED_TO_UPLOAD_FILE_CHUNK';
				return CCloudStorage::FILE_SKIPPED;
			}
		}
		else
		{
			if ($arFile["HANDLER_ID"])
			{
				$ar = array();
				if (!CCloudStorage::OnMakeFileArray($arFile, $ar))
				{
					self::$file_skip_reason = 'FAILED_TO_DOWNLOAD_FILE_3';
					return CCloudStorage::FILE_SKIPPED;
				}

				if (!isset($ar["tmp_name"]))
				{
					self::$file_skip_reason = 'FAILED_TO_DOWNLOAD_FILE_4';
					return CCloudStorage::FILE_SKIPPED;
				}
			}
			else
			{
				$ar = CFile::MakeFileArray($arFile["ID"]);
				if (!isset($ar["tmp_name"]))
				{
					self::$file_skip_reason = 'FAILED_TO_GET_SOURCE_FILE_INFO_2';
					return CCloudStorage::FILE_SKIPPED;
				}
			}

			$res = $bucket->SaveFile($filePath, $ar);
			if ($res)
			{
				$bucket->IncFileCounter(filesize($ar["tmp_name"]));

				if ($arFile["HANDLER_ID"])
				{
					self::_delete_file($arFile);
				}
				else
				{
					self::_delete_file($ar["tmp_name"]);
				}

				self::$file_skip_reason = 'FAILED_TO_UPLOAD_FILE';
				return CCloudStorage::FILE_MOVED;
			}
			else
			{        //delete temporary copy
				if ($arFile["HANDLER_ID"])
				{
					self::_delete_file($ar["tmp_name"]);
				}

				return CCloudStorage::FILE_SKIPPED;
			}

		}
	}

	public static function OnFileSave(&$arFile, $strFileName, $strSavePath, $bForceMD5 = false, $bSkipExt = false, $dirAdd = '')
	{
		if (!$arFile["tmp_name"] && !array_key_exists("content", $arFile))
			return false;

		if (array_key_exists("bucket", $arFile))
			$bucket = $arFile["bucket"];
		else
			$bucket = CCloudStorage::FindBucketForFile($arFile, $strFileName);

		if (!is_object($bucket))
			return false;

		if (!$bucket->Init())
			return false;

		$original = null;
		$copySize = false;
		$subDir = "";
		$filePath = "";

		if (array_key_exists("bucket", $arFile))
		{
			$newName = bx_basename($arFile["tmp_name"]);

			$prefix = $bucket->GetFileSRC("/");
			$subDir = mb_substr($arFile["tmp_name"], mb_strlen($prefix));
			$subDir = mb_substr($subDir, 0, -mb_strlen($newName) - 1);
		}
		else
		{
			if (array_key_exists("content", $arFile))
			{
				$arFile["tmp_name"] = CTempFile::GetFileName(bx_basename($arFile["name"]));
				CheckDirPath($arFile["tmp_name"]);
				$fp = fopen($arFile["tmp_name"], "ab");
				if ($fp)
				{
					fwrite($fp, $arFile["content"]);
					fclose($fp);
				}
			}

			if (
				$bForceMD5 != true
				&& COption::GetOptionString("main", "save_original_file_name", "N") == "Y"
			)
			{
				if (COption::GetOptionString("main", "convert_original_file_name", "Y") == "Y")
					$newName = CCloudStorage::translit($strFileName);
				else
					$newName = $strFileName;
			}
			else
			{
				$strFileExt = ($bSkipExt == true? '': strrchr($strFileName, "."));
				$newName = md5(uniqid(mt_rand(), true)).$strFileExt;
			}

			//check for double extension vulnerability
			$newName = RemoveScriptExtension($newName);
			$dir_add = $dirAdd;

			if (empty($dir_add))
			{
				while (true)
				{
					$dir_add = md5(mt_rand());
					$dir_add = mb_substr($dir_add, 0, 3)."/".$dir_add;

					$subDir = trim(trim($strSavePath, "/")."/".$dir_add, "/");
					$filePath = "/".$subDir."/".$newName;

					if (!$bucket->FileExists($filePath))
						break;
				}
			}
			else
			{
				$subDir = trim(trim($strSavePath, "/")."/".$dir_add, "/");
				$filePath = "/".$subDir."/".$newName;
			}

			if (!isset($arFile["external_id"]))
			{
				$arFile["external_id"] = md5(mt_rand());
			}

			\Bitrix\Clouds\FileSaveTable::startFileOperation(
				$bucket->ID
				,$subDir
				,$newName
				,$arFile["external_id"]
			);

			$targetPath = $bucket->GetFileSRC("/");
			if (mb_strpos($arFile["tmp_name"], $targetPath) === 0)
			{
				$arDbFile = array(
					"SUBDIR" => "",
					"FILE_NAME" => mb_substr($arFile["tmp_name"], mb_strlen($targetPath)),
					"CONTENT_TYPE" => $arFile["type"],
				);

				//get the file hash
				$arFile["FILE_HASH"] = '';
				if(COption::GetOptionString('main', 'control_file_duplicates', 'N') === 'Y')
				{
					$info = $bucket->GetFileInfo('/' . $arDbFile['FILE_NAME']);
					if($info)
					{
						$arFile["FILE_HASH"] = $info["hash"];
						$copySize = $info["size"];
					}
				}

				//control of duplicates
				if ($arFile["FILE_HASH"] <> '')
				{
					$original = CFile::FindDuplicate($copySize, $arFile["FILE_HASH"], $bucket->ID);
					if($original !== null)
					{
						$arFile["original_file"] = $original;
					}
				}

				//copy only if the file is not a duplicate
				if($original === null)
				{
					$copyPath = $bucket->FileCopy($arDbFile, $filePath);
					if (!$copyPath)
						return false;

					if ($copySize === false)
					{
						$info = $bucket->GetFileInfo('/' . urldecode(mb_substr($copyPath, mb_strlen($targetPath))));
						if ($info)
						{
							$copySize = $info["size"];
						}
						else
						{
							return false;
						}
					}
				}
			}
			else
			{
				$imgArray = CFile::GetImageSize($arFile["tmp_name"], true, false);
				if (is_array($imgArray) && $imgArray[2] == IMAGETYPE_JPEG)
				{
					$exifData = CFile::ExtractImageExif($arFile["tmp_name"]);
					if ($exifData && isset($exifData['Orientation']))
					{
						$properlyOriented = CFile::ImageHandleOrientation($exifData['Orientation'], $arFile["tmp_name"]);
						if ($properlyOriented)
						{
							$jpgQuality = intval(COption::GetOptionString('main', 'image_resize_quality', '95'));
							if ($jpgQuality <= 0 || $jpgQuality > 100)
								$jpgQuality = 95;

							imagejpeg($properlyOriented, $arFile["tmp_name"], $jpgQuality);
							clearstatcache(true, $arFile["tmp_name"]);
						}
						$arFile['size'] = filesize($arFile["tmp_name"]);
					}
				}

				if (!$bucket->SaveFile($filePath, $arFile))
				{
					return false;
				}

				//get the file hash
				$arFile["FILE_HASH"] = '';
				$size = 0;
				if(COption::GetOptionString('main', 'control_file_duplicates', 'N') === 'Y')
				{
					$info = $bucket->GetFileInfo($filePath);
					if($info)
					{
						$arFile["FILE_HASH"] = $info["hash"];
						$size = $info["size"];
					}
				}

				//control of duplicates
				if ($arFile["FILE_HASH"] <> '')
				{
					if (is_callable(['CFile', 'lockFileHash']))
					{
						static::$lockId = CFile::lockFileHash($size, $arFile["FILE_HASH"], $bucket->ID);
					}
					$original = CFile::FindDuplicate($size, $arFile["FILE_HASH"], $bucket->ID);
					if($original !== null)
					{
						$arFile["original_file"] = $original;

						//we don't need the duplicate anymore
						$bucket->DeleteFile($filePath);
					}
				}
			}
		}

		$arFile["HANDLER_ID"] = $bucket->ID;
		$arFile["WIDTH"] = 0;
		$arFile["HEIGHT"] = 0;

		if($original === null)
		{
			$arFile["SUBDIR"] = $subDir;
			$arFile["FILE_NAME"] = $newName;
		}
		else
		{
			//points to the original's physical path
			$arFile["SUBDIR"] = $original->getFile()->getSubdir();
			$arFile["FILE_NAME"] = $original->getFile()->getFileName();
		}

		if (array_key_exists("bucket", $arFile))
		{
			$arFile["WIDTH"] = $arFile["width"];
			$arFile["HEIGHT"] = $arFile["height"];
			$arFile["size"] = $arFile["file_size"];
		}
		elseif ($copySize !== false)
		{
			$arFile["WIDTH"] = $arFile["width"];
			$arFile["HEIGHT"] = $arFile["height"];
			$arFile["size"] = $copySize;

			//if the file is a duplicate we shouldn't increase the size counter
			if($original === null)
			{
				$bucket->IncFileCounter($copySize);
				\Bitrix\Clouds\FileSaveTable::setFileSize(
					$bucket->ID
					,$subDir
					,$newName
					,$copySize
				);
			}
		}
		else
		{
			//if the file is a duplicate we shouldn't increase the size counter
			if($original === null)
			{
				$fileSize = filesize($arFile["tmp_name"]);
				$bucket->IncFileCounter($fileSize);
				\Bitrix\Clouds\FileSaveTable::setFileSize(
					$bucket->ID
					,$subDir
					,$newName
					,$fileSize
				);
			}

			$flashEnabled = !CFile::IsImage($arFile["ORIGINAL_NAME"], $arFile["type"]);
			$imgArray = CFile::GetImageSize($arFile["tmp_name"], true, $flashEnabled);
			if (is_array($imgArray))
			{
				$arFile["WIDTH"] = $imgArray[0];
				$arFile["HEIGHT"] = $imgArray[1];
			}
		}

		if (isset($arFile["old_file"]))
		{
			CFile::Delete($arFile["old_file"]);
		}

		return true;
	}

	public static function OnAfterFileSave($arFile)
	{
		\Bitrix\Clouds\FileSaveTable::endFileOperation(
			$arFile["HANDLER_ID"]
			,$arFile["SUBDIR"]
			,$arFile["FILE_NAME"]
		);
		if (static::$lockId)
		{
			CFile::unlockFileHash(static::$lockId);
			static::$lockId = '';
		}
	}

	public static function OnAfterFileDeleteDuplicate($original, $duplicate)
	{
		$result = false;
		if ($original->getHandlerId() > 0)
		{
			$bucket = new CCloudStorageBucket($original->getHandlerId());
			if ($bucket->Init())
			{
				$duplicatePath = '/' . $duplicate->getSubdir() . '/' . $duplicate->getFileName();
				\Bitrix\Clouds\FileHashTable::deleteByFilePath($original->getHandlerId(), $duplicatePath);

				$result = $bucket->deleteFile($duplicatePath, $duplicate->getFileSize());
				if ($result)
				{
					$bucket->decFileCounter($duplicate->getFileSize());
				}
			}
		}
		return $result;
	}

	public static function CleanUp()
	{
		$buckets = array();
		$date = new \Bitrix\Main\Type\DateTime();
		$date->add("-1D");
		$savedFiles = \Bitrix\Clouds\FileSaveTable::getList(array(
			"filter" => array(
				"<TIMESTAMP_X" => $date,
			),
		));
		while ($saveFile = $savedFiles->fetchObject())
		{
			$dbFile = CFile::GetList(array(), array(
				"EXTERNAL_ID" => $saveFile->getExternalId(),
				"SUBDIR" => $saveFile->getSubdir(),
				"FILE_NAME" => $saveFile->getFileName(),
				"HANDLER_ID" => $saveFile->getBucketId(),
			));
			if ($dbFile->Fetch())
			{
				$saveFile->delete();
			}
			else
			{
				$bucketId = $saveFile->getBucketId();
				if (!isset($buckets[$bucketId]))
				{
					$buckets[$bucketId] = new \CCloudStorageBucket($bucketId);
				}
				$bucket = $buckets[$bucketId];

				if ($bucket->Init())
				{
					$filePath = "/".$saveFile->getSubdir()."/".$saveFile->getFileName();
					if ($bucket->DeleteFile($filePath))
					{
						$fileSize = $saveFile->getFileSize();
						if ($fileSize >= 0)
						{
							$bucket->DecFileCounter($fileSize);
						}
					}
					$saveFile->delete();
				}
			}
		}

		CCloudStorageUpload::CleanUp();

		return "CCloudStorage::CleanUp();";
	}

	public static function FindBucketByFile($file_name)
	{
		foreach (CCloudStorageBucket::GetAllBuckets() as $bucket)
		{
			if ($bucket["ACTIVE"] == "Y")
			{
				$obBucket = new CCloudStorageBucket($bucket["ID"]);
				if ($obBucket->Init())
				{
					$prefix = $obBucket->GetFileSRC("/");
					if (mb_substr($file_name, 0, mb_strlen($prefix)) === $prefix)
						return $obBucket;
				}
			}
		}
		return false;
	}

	public static function FindFileURIByURN($urn, $log_descr = "")
	{
		foreach (CCloudStorageBucket::GetAllBuckets() as $bucket)
		{
			if ($bucket["ACTIVE"] == "Y")
			{
				$obBucket = new CCloudStorageBucket($bucket["ID"]);
				if ($obBucket->Init() && $obBucket->FileExists($urn))
				{
					$uri = $obBucket->GetFileSRC($urn);

					if ($log_descr && COption::GetOptionString("clouds", "log_404_errors") === "Y")
						CEventLog::Log("WARNING", "CLOUDS_404", "clouds", $uri, $log_descr);

					return $uri;
				}
			}
		}
		return "";
	}

	public static function OnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
	{
		global $USER;
		if (!$USER->CanDoOperation("clouds_browse"))
			return;

		//When UnRegisterModuleDependences is called from module uninstall
		//cached EventHandlers may be called
		if (defined("BX_CLOUDS_UNINSTALLED"))
			return;

		$aMenu = array(
			"parent_menu" => "global_menu_content",
			"section" => "clouds",
			"sort" => 150,
			"text" => GetMessage("CLO_STORAGE_MENU"),
			"title" => GetMessage("CLO_STORAGE_TITLE"),
			"icon" => "clouds_menu_icon",
			"page_icon" => "clouds_page_icon",
			"items_id" => "menu_clouds",
			"items" => array()
		);

		$rsBuckets = CCloudStorageBucket::GetList(array("SORT" => "DESC", "ID" => "ASC"));
		while ($arBucket = $rsBuckets->Fetch())
			$aMenu["items"][] = array(
				"text" => $arBucket["BUCKET"],
				"url" => "clouds_file_list.php?lang=".LANGUAGE_ID."&bucket=".$arBucket["ID"]."&path=/",
				"more_url" => array(
					"clouds_file_list.php?bucket=".$arBucket["ID"],
				),
				"title" => "",
				"page_icon" => "clouds_page_icon",
				"items_id" => "menu_clouds_bucket_".$arBucket["ID"],
				"module_id" => "clouds",
				"items" => array()
			);

		if (!empty($aMenu["items"]))
			$aModuleMenu[] = $aMenu;
	}

	public static function OnAdminListDisplay(&$obList)
	{
		global $USER;

		if ($obList->table_id !== "tbl_fileman_admin")
			return;

		if (!is_object($USER) || !$USER->CanDoOperation("clouds_upload"))
			return;

		static $clouds = null;
		if (!isset($clouds))
		{
			$clouds = array();
			$rsClouds = CCloudStorageBucket::GetList(array("SORT" => "DESC", "ID" => "ASC"));
			while ($arStorage = $rsClouds->Fetch())
			{
				if ($arStorage["READ_ONLY"] == "N" && $arStorage["ACTIVE"] == "Y")
					$clouds[$arStorage["ID"]] = $arStorage["BUCKET"];
			}
		}

		if (empty($clouds))
			return;

		foreach ($obList->aRows as $obRow)
		{
			if ($obRow->arRes["TYPE"] === "F")
			{
				$ID = "F".$obRow->arRes["NAME"];
				$file = $obRow->arRes["NAME"];
				$path = mb_substr($obRow->arRes["ABS_PATH"], 0, -mb_strlen($file));

				$arSubMenu = array();
				foreach ($clouds as $id => $bucket)
					$arSubMenu[] = array(
						"TEXT" => $bucket,
						"ACTION" => $s = "if(confirm('".GetMessage("CLO_STORAGE_UPLOAD_CONF")."')) jsUtils.Redirect([], '".CUtil::AddSlashes("/bitrix/admin/clouds_file_list.php?lang=".LANGUAGE_ID."&bucket=".urlencode($id)."&path=".urlencode($path)."&ID=".urlencode($ID)."&action=upload&".bitrix_sessid_get())."');"
					);

				$obRow->aActions[] = array(
					"TEXT" => GetMessage("CLO_STORAGE_UPLOAD_MENU"),
					"MENU" => $arSubMenu,
				);
			}
		}
	}

	public static function HasActiveBuckets()
	{
		foreach (CCloudStorageBucket::GetAllBuckets() as $bucket)
			if ($bucket["ACTIVE"] === "Y")
				return true;
		return false;
	}

	public static function OnBeforeProlog()
	{
		if (defined("BX_CHECK_SHORT_URI") && BX_CHECK_SHORT_URI)
		{
			$upload_dir = "/".trim(COption::GetOptionString("main", "upload_dir", "upload"), "/")."/";
			$request_uri = \Bitrix\Main\Text\Encoding::convertEncoding(rawurldecode($_SERVER["REQUEST_URI"]), "UTF-8", LANG_CHARSET);

			foreach (CCloudStorageBucket::GetAllBuckets() as $arBucket)
			{
				if ($arBucket["ACTIVE"] == "Y")
				{
					$obBucket = new CCloudStorageBucket($arBucket["ID"]);
					if ($obBucket->Init())
					{
						$bucketUrl = $obBucket->GetFileSRC('/');
						$bucketPrefix = rtrim(parse_url($bucketUrl, PHP_URL_PATH), '/');
						$prefixMatch = $bucketPrefix ? "(?:$bucketPrefix|)" : "";
						$match = array();
						if (
							COption::GetOptionString("clouds", "delayed_resize") === "Y"
							&& preg_match("#^$prefixMatch(/resize_cache/.*\$)#", $request_uri, $match)
						)
						{
							session_write_close();
							$to_file = $obBucket->GetFileSRC($match[1], false);
							if (CCloudStorage::ResizeImageFileCheck($obBucket, $to_file))
							{
								$cache_time = 3600 * 24 * 30; // 30 days
								header("Cache-Control: max-age=".$cache_time);
								header("Expires: ".gmdate("D, d M Y H:i:s", time() + $cache_time)." GMT");
								header_remove("Pragma");
								LocalRedirect(\Bitrix\Main\Web\Uri::urnEncode($to_file, "UTF-8"), true, "301 Moved Permanently");
							}
						}
						elseif (
							!preg_match("/[?&]/", $request_uri)
							&& $obBucket->FileExists($request_uri)
						)
						{
							if (COption::GetOptionString("clouds", "log_404_errors") === "Y")
								CEventLog::Log("WARNING", "CLOUDS_404", "clouds", $_SERVER["REQUEST_URI"], $_SERVER["HTTP_REFERER"]);
							LocalRedirect($obBucket->GetFileSRC($request_uri), true);
						}
						elseif (mb_strpos($request_uri, $upload_dir) === 0)
						{
							$check_url = mb_substr($request_uri, mb_strlen($upload_dir) - 1);
							if ($obBucket->FileExists($check_url))
							{
								if (COption::GetOptionString("clouds", "log_404_errors") === "Y")
									CEventLog::Log("WARNING", "CLOUDS_404", "clouds", $_SERVER["REQUEST_URI"], $_SERVER["HTTP_REFERER"]);
								LocalRedirect($obBucket->GetFileSRC($check_url), true);
							}
						}
					}
				}
			}
		}
	}

	public static function GetAuditTypes()
	{
		return array(
			"CLOUDS_404" => "[CLOUDS_404] ".GetMessage("CLO_404_ON_MOVED_FILE"),
		);
	}

	public static function translit($file_name, $safe_chars = '')
	{
		return CUtil::translit($file_name, LANGUAGE_ID, array(
			"safe_chars" => "-. ".$safe_chars,
			"change_case" => false,
			"max_len" => 255,
		));
	}

	/**
	 * @param array [string]string $arFile
	 * @return void
	 */
	public static function FixFileContentType(&$arFile)
	{
		global $DB;
		$fixedContentType = "";

		if ($arFile["CONTENT_TYPE"] === "image/jpg")
			$fixedContentType = "image/jpeg";
		else
		{
			$hexContentType = unpack("H*", $arFile["CONTENT_TYPE"]);
			if (
				$hexContentType[1] === "e0f3e4e8ee2f6d706567"
				|| $hexContentType[1] === "d0b0d183d0b4d0b8d0be2f6d706567"
			)
				$fixedContentType = "audio/mpeg";
		}

		if ($fixedContentType !== "")
		{
			$arFile["CONTENT_TYPE"] = $fixedContentType;
			$DB->Query("
				UPDATE b_file
				SET CONTENT_TYPE = '".$DB->ForSQL($fixedContentType)."'
				WHERE ID = ".intval($arFile["ID"])."
			");
			CFile::CleanCache($arFile["ID"]);
		}
	}
}
