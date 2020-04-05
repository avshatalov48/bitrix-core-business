<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
class CPhotoUploader
{
	var $arParams;
	var $arResult;
	var $arWatermark = array();
	var $post;
	function __construct(&$arParams, $arResult)
	{
		$this->arParams = &$arParams;
		$this->arResult = $arResult;
		if ($arParams['WATERMARK_RULES'] == 'ALL')
		{
			$arWatermark = array(
				'type' => strtolower($arParams['WATERMARK_TYPE']),
				'position' => $arParams['WATERMARK_POSITION']
			);

			if($arParams['WATERMARK_TYPE'] == 'TEXT')
			{
				$arWatermark['coefficient'] = $arParams['WATERMARK_SIZE'];
				$arWatermark['text'] = trim($arParams['WATERMARK_TEXT']);
				$arWatermark['font'] = trim($arParams['PATH_TO_FONT']);
				$arWatermark['color'] = trim($arParams['WATERMARK_COLOR']);
				$arWatermark['use_copyright'] = "N";
			}
			else
			{
				$arWatermark['file'] = $arParams['WATERMARK_FILE'];
				$arWatermark['alpha_level'] = $arParams['WATERMARK_TRANSPARENCY'];
				$arWatermark['size'] = 'real';
				$arWatermark['fill'] = $arParams['WATERMARK_FILE_ORDER'] == 'usual' ? 'exact' : $arParams['WATERMARK_FILE_ORDER'];
			}
			$this->arWatermark = $arWatermark;
		}
		elseif ($_POST['photo_watermark_use'] == 'Y')
		{
			$this->arWatermark = array(
				'type' => $_POST['photo_watermark_type'],
				'text' => $_POST['photo_watermark_text'],
				'font' => $arParams['PATH_TO_FONT'],
				'position' => $_POST['photo_watermark_position'],
				'color' => $_POST['photo_watermark_color'],
				'size' => $_POST['photo_watermark_size'],
				'fill' => 'resize', // resize | exact | repeat
				'file' => $_SERVER["DOCUMENT_ROOT"].$_POST['photo_watermark_path'],
				'alpha_level' => $_POST['photo_watermark_opacity'],
				'use_copyright' => $_POST["photo_watermark_copyright"] == "Y" ? "Y" : "N"
			);
		}
	}

	/**
	 * Creates new section in iblock
	 * @param $arParams
	 * @param $arResult
	 * @param $name
	 * @return bool|int
	 */
	public static function createAlbum($arParams, $arResult, &$name)
	{
		if (!CModule::IncludeModule("iblock"))
			return false;
		$name = trim($name);
		$name = (strlen($name) > 0 ? $name : GetMessage("P_NEW_ALBUM"));
		$name = (strlen($name) > 0 ? $name : "New album");
		$arFields = Array(
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_SECTION_ID" => ($arParams["BEHAVIOUR"] == "USER" ? $arResult["GALLERY"]["ID"] : 0),
			"DATE" => ConvertTimeStamp(time()+CTimeZone::GetOffset()),
			"UF_DATE" => ConvertTimeStamp(time()+CTimeZone::GetOffset()),
			"NAME" => $name
		);
		$GLOBALS["UF_DATE"] = $arFields["UF_DATE"];

		$bs = new CIBlockSection;
		$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);

		$ID = $bs->Add($arFields);
		if ($ID > 0)
		{
			CIBlockSection::ReSort($arParams["IBLOCK_ID"]);

			$arPropertiesNeed = array(); // Array of properties to create
			foreach ($arParams['converters'] as $val)
			{
				if ($val['code'] == "real_picture" || $val['code'] == "thumbnail")
					continue;

				$db_res = CIBlock::GetProperties($arParams["IBLOCK_ID"], array(), array("CODE" => $val['code']));
				if (!($db_res && $res = $db_res->Fetch()))
					$arPropertiesNeed[] = $val['code'];
			}

			if (count($arPropertiesNeed) > 0)
			{
				$obProperty = new CIBlockProperty;
				foreach ($arPropertiesNeed as $key)
				{
					$res = $obProperty->Add(array(
						"IBLOCK_ID" => $arParams["IBLOCK_ID"],
						"ACTIVE" => "Y",
						"PROPERTY_TYPE" => "F",
						"MULTIPLE" => "N",
						"NAME" => (strLen(GetMessage("P_".strToUpper($key))) > 0 ? GetMessage("P_".strToUpper($key)) : strToUpper($key)),
						"CODE" => strToUpper($key),
						"FILE_TYPE" => "jpg, gif, bmp, png, jpeg"
					));
				}
			}

			// Check Public property
			$arPropertiesNeed = array();
			foreach (array("PUBLIC_ELEMENT", "APPROVE_ELEMENT") as $key)
			{
				$db_res = CIBlock::GetProperties($arParams["IBLOCK_ID"], array(), array("CODE" => $key));
				if (!$db_res || !($res = $db_res->Fetch()))
					$arPropertiesNeed[] = $key;
			}

			if (count($arPropertiesNeed) > 0)
			{
				$obProperty = new CIBlockProperty;
				foreach ($arPropertiesNeed as $key)
				{
					$obProperty->Add(array(
						"IBLOCK_ID" => $arParams["IBLOCK_ID"],
						"ACTIVE" => "Y",
						"PROPERTY_TYPE" => "S",
						"MULTIPLE" => "N",
						"NAME" => (strLen(GetMessage("P_".$key)) > 0 ? GetMessage("P_".$key) : $key),
						"DEFAULT_VALUE" => "N",
						"CODE" => $key
					));
				}
			}
		}
		return ($ID > 0 ? $ID : false);
	}

	/**
	 * Adds new properties in block
	 * @param $arParams
	 * @return bool
	 */
	public static function adjustIBlock($arParams)
	{
		$arPropertiesNeed = array(); // Array of properties needed to create
		foreach ($arParams['converters'] as $key => $val)
		{
			if ($val['code'] == "real_picture" || $val['code'] == "thumbnail")
				continue;

			$db_res = CIBlock::GetProperties($arParams["IBLOCK_ID"], array(), array("CODE" => $val['code']));
			if (!($db_res && $res = $db_res->Fetch()))
				$arPropertiesNeed[] = $val['code'];
		}

		if (count($arPropertiesNeed) > 0)
		{
			$obProperty = new CIBlockProperty;
			foreach ($arPropertiesNeed as $key)
			{
				$res = $obProperty->Add(array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "F",
					"MULTIPLE" => "N",
					"NAME" => (strLen(GetMessage("P_".strToUpper($key))) > 0 ? GetMessage("P_".strToUpper($key)) : strToUpper($key)),
					"CODE" => strToUpper($key),
					"FILE_TYPE" => "jpg, gif, bmp, png, jpeg"
				));
			}
		}

		// Check Public property
		$arPropertiesNeed = array();
		foreach (array("PUBLIC_ELEMENT", "APPROVE_ELEMENT") as $key)
		{
			$db_res = CIBlock::GetProperties($arParams["IBLOCK_ID"], array(), array("CODE" => $key));
			if (!$db_res || !($res = $db_res->Fetch()))
				$arPropertiesNeed[] = $key;
		}

		if (count($arPropertiesNeed) > 0)
		{
			$obProperty = new CIBlockProperty;
			foreach ($arPropertiesNeed as $key)
			{
				$res = $obProperty->Add(array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "S",
					"MULTIPLE" => "N",
					"NAME" => (strLen(GetMessage("P_".$key)) > 0 ? GetMessage("P_".$key) : $key),
					"DEFAULT_VALUE" => "N",
					"CODE" => $key
				));
			}
		}
		return true;
	}

	function onBeforeUpload(&$package, &$upload, $post, $files, &$error)
	{
		$this->post = $post;
		return true;
	}
	function onAfterUpload($data, $post, $files)
	{
		$arParams = $this->arParams;
		$arResult = $this->arResult;
		$sectionsIds = array(0, $arParams['SECTION_ID']);
		$arGalleriesIds = array(0);
		$arUsers = array();


		if ($arResult['SECTION'] && $arResult['SECTION']['IBLOCK_SECTION_ID'])
			$sectionsIds[] = $arResult['SECTION']['IBLOCK_SECTION_ID'];
		if ($arParams["BEHAVIOUR"] == "USER")
			$sectionsIds[] = $arResult["GALLERY"]["ID"];

		if (isset($arResult["GALLERY"]["CODE"]))
		{
			$arGalleriesIds[] = $arResult["GALLERY"]["CODE"];
			if ($arResult["GALLERY"]["CREATED_BY"])
				$arUsers[] = $arResult["GALLERY"]["CREATED_BY"];
		}

		PClearComponentCacheEx($arParams["IBLOCK_ID"], $sectionsIds, $arGalleriesIds, $arUsers);
		return true;
	}
	function handleFile($hash, $photo, &$package, &$upload, &$error)
	{
		if (!CModule::IncludeModule("iblock"))
			return false;
		global $USER;
		$arParams = $this->arParams;

		if (!($upload["SECTION_ID"] > 0))
		{
			if ($this->post["photo_album_id"] > 0)
				$upload["SECTION_ID"] = $this->post["photo_album_id"];
			else
				$upload["NEW_SECTION_ID"] = $upload["SECTION_ID"] = self::createAlbum($this->arParams, $this->arResult, $this->post["new_album_name"]);
			$this->arParams["SECTION_ID"] = $upload["SECTION_ID"];
			$upload["redirectUrl"] = CComponentEngine::MakePathFromTemplate(
				$this->arParams["~SECTION_URL"],
				array("USER_ALIAS" => $this->arParams["USER_ALIAS"], "SECTION_ID" => $this->arParams["SECTION_ID"])
			);
			self::adjustIBlock($this->arParams);
		}
		if (!($upload["SECTION_ID"] > 0))
		{
			$error = "Album is not created or does not exist.";
			return false;
		}

		$arParams["bxu"]->checkCanvases($hash, $photo, $arParams['converters'], $this->arWatermark);

		// Props
		$_REQUEST["Public"] = ($_REQUEST["Public"] == "N" ? "N" : "Y");
		$Prop = array(
			"REAL_PICTURE" => array("n0" => $photo["files"]["default"]),
			"PUBLIC_ELEMENT" => array("n0" => $_REQUEST["Public"]),
			"APPROVE_ELEMENT" => array("n0" => (($arParams["ABS_PERMISSION"] >= "U" || $arParams["APPROVE_BY_DEFAULT"] == "Y") && $_REQUEST["Public"] == "Y") ? "Y" : "X")
		);
		foreach ($arParams['converters'] as $val)
		{
			if ($val['code'] != "default" && $val['code'] != "thumbnail")
				$Prop[strtoupper($val['code'])] = array("n0" => $photo["files"][$val['code']]);
		}
		// Real photo
		$arFields = Array(
			"ACTIVE" => (($arParams["MODERATION"] == "Y" && $arParams["ABS_PERMISSION"] < "U") ? "N" : "Y"),
			"MODIFIED_BY" => $USER->GetID(),
			"IBLOCK_SECTION" => $upload['SECTION_ID'],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"NAME" => $photo['name'],
			"CODE" => $photo['name'],
			"TAGS" => $photo['tags'],
			"DETAIL_TEXT" => $photo['description'],
			"DETAIL_TEXT_TYPE" => "text",
			"PREVIEW_PICTURE" => $photo["files"]["thumbnail"],
			"PREVIEW_TEXT" => $photo['description'],
			"PREVIEW_TEXT_TYPE" => "text",
			"PROPERTY_VALUES" => $Prop
		);

		$bs = new CIBlockElement;
		$ID = $bs->Add($arFields);
		if ($ID <= 0)
		{
			$error = $bs->LAST_ERROR;
			return false;
		}
		$arFields['ID'] = $ID;
		$_SESSION['arUploadedPhotos'][] = $ID;
		CIBlockElement::RecalcSections($ID);

		$arParams['SECTION_ID'] = $upload['SECTION_ID'];
		$arResult = $this->arResult;

		foreach(GetModuleEvents("photogallery", "OnAfterUpload", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields, $arParams, $arResult));

		// Add thumbnail only for new album
		if ($upload["NEW_SECTION_ID"] > 0 && !array_key_exists('NEW_SECTION_PICTURE', $upload))
		{
			$File = $photo["files"]["default"]; // Big picture
			$File['~tmp_name'] = $File['tmp_name'];
			$File['tmp_name'] .= "_album_cover.tmp";
			if (CFile::ResizeImageFile($File['tmp_name'], $File['tmp_name_1'], array('width' => $arParams["ALBUM_PHOTO_THUMBS"]["SIZE"], 'height' => $arParams["ALBUM_PHOTO_THUMBS"]["SIZE"]), BX_RESIZE_IMAGE_PROPORTIONAL))
			{
				$bs = new CIBlockSection;
				if ($bs->Update($upload["SECTION_ID"], Array("PICTURE" => $File), false, false))
					$upload['NEW_SECTION_PICTURE'] = true;
			}
		}
		return $ID;
	}
}
function getImageUploaderId($str = 'bx_img_upl_')
{
	static $iIndexOnPage = 0;
	$iIndexOnPage++;
	return $str . $iIndexOnPage;
}

// Called once before all uploads
function onBeforeUpload($Params)
{
	CModule::IncludeModule("iblock");
	$_SESSION['arUploadedPhotos'] = Array();
	$arParams = $Params['arParams'];
	$savedData = CImageUploader::GetSavedData();
	$savedData['UPLOADING_START'] = "Y";
	CImageUploader::SetSavedData($savedData);

	if ($savedData["SECTION_ID"] <= 0)
	{
		$arParams["SECTION_ID"] = GetAlbumId(
			array(
				'id' => $Params['packageFields']['photo_album_id'],
				'name' => $Params['packageFields']['new_album_name'],
				'arParams' => $arParams,
				'~arResult' => $Params['~arResult']
			)
		);

		$savedData = CImageUploader::GetSavedData();
		$savedData["SECTION_ID"] = $arParams["SECTION_ID"];
	}
	else
	{
		$arParams["SECTION_ID"] = $savedData["SECTION_ID"];
	}

	// Check and create properties
	if (count($savedData['arError']) == 0)
	{
		CPhotoUploader::adjustIBlock($arParams);
	}

	CImageUploader::SetSavedData($savedData);
	return true;
}

// Called once after uploads
function onAfterUpload($Params)
{
	$savedData = CImageUploader::GetSavedData();
	$arParams = $Params['arParams'];
	$arResult = $Params['~arResult'];

	$savedData['UPLOADING_SUCCESS'] = "Y";
	CImageUploader::SetSavedData($savedData);

	$sectionsIds = array(0, $arParams['SECTION_ID']);
	$arGalleriesIds = array(0);
	$arUsers = array();


	if ($arResult['SECTION'] && $arResult['SECTION']['IBLOCK_SECTION_ID'])
		$sectionsIds[] = $arResult['SECTION']['IBLOCK_SECTION_ID'];
	if ($arParams["BEHAVIOUR"] == "USER")
		$sectionsIds[] = $arResult["GALLERY"]["ID"];

	if (isset($arResult["GALLERY"]["CODE"]))
	{
		$arGalleriesIds[] = $arResult["GALLERY"]["CODE"];
		if ($arResult["GALLERY"]["CREATED_BY"])
			$arUsers[] = $arResult["GALLERY"]["CREATED_BY"];
	}

	PClearComponentCacheEx($arParams["IBLOCK_ID"], $sectionsIds, $arGalleriesIds, $arUsers);
}


// Used to get album and create new album (only once)
function GetAlbumId($Params)
{
	$arParams = $Params['arParams'];
	$sectionId = false;
	$savedData = CImageUploader::GetSavedData();
	$arResult = $Params['~arResult'];

	if ($savedData['SECTION_ID'] <= 0)
	{
		// Upload photos to existing album
		if ($Params['id'] !== 'new' && intVal($Params['id']) > 0)
		{
			$sectionId = intVal($Params['id']);
		}
		// Create new album
		else	if ($Params['id'] == 'new')
		{
			$sectionId = CPhotoUploader::createAlbum(
				$arParams,
				$arResult,
				$Params['name']
			);
			if ($sectionId > 0)
			{
				$arFilter = array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "CODE" => $arParams["USER_ALIAS"], "SECTION_ID" => 0);
				//$db_res = CIBlockSection::GetList(array(), $arFilter, false, array("ID", "NAME", "CREATED_BY", "RIGHT_MARGIN", "LEFT_MARGIN", "CODE", "UF_GALLERY_SIZE"));
				//$arResult["GALLERY"] = $db_res->Fetch();

				$savedData['NEW_SECTION_NAME'] = $Params['name'];
				$savedData['NEW_SECTION_PICTURE'] = "";
			}
			else
			{
				CImageUploader::SaveError(array(array("id" => "BXPH_FUNC_001", "text" => $bs->LAST_ERROR)));
			}
		}
	}

	$savedData["SECTION_ID"] = $sectionId;
	CImageUploader::SetSavedData($savedData);
	return $sectionId;
}

// Called for every file which loaded by Java/ActiveX/Flash or simple uploader
function handleFile($Info, $arFiles, $Params)
{
	CModule::IncludeModule("iblock");

	global $USER;
	$arParams = $Params['arParams'];
	$savedData = CImageUploader::GetSavedData();

	$arErrors = array();
	// Check file sizes and types (types only for mass uploaders)

	foreach($arFiles as $file)
	{
		if ($file['size'] > 0 && $arParams["UPLOAD_MAX_FILE_SIZE"] > 0 && $file['size'] > $arParams["UPLOAD_MAX_FILE_SIZE"])
			$arErrors[] = array("file" => $file['name'], "id" => "BXPH_FUNC_HANDLE_2_LARGE_SIZE","text" => GetMessage('P_WM_IMG_ERROR04'));

		if ($file['type'] && strpos(strtolower($file['type']), 'image') === false)
			$arErrors[] = array("file" => $file['name'], "id" => "BXPH_FUNC_HANDLE_2_TYPE","text" => GetMessage('P_WM_IMG_ERROR02'));
	}

	if (count($arErrors) > 0) // Exit if we have any errors
		return CImageUploader::SaveError($arErrors);

	// Handle watermark for Flash-uploader
	if ($arParams["UPLOADER_TYPE"] == 'flash')
	{
		$arWatermark = false;

		if ($arParams['WATERMARK_RULES'] == 'ALL')
		{
			$arWatermark = array(
				'type' => strtolower($arParams['WATERMARK_TYPE']),
				'position' => $arParams['WATERMARK_POSITION']
			);

			if($arParams['WATERMARK_TYPE'] == 'TEXT')
			{
				$arWatermark['coefficient'] = $arParams['WATERMARK_SIZE'];
				$arWatermark['text'] = trim($arParams['WATERMARK_TEXT']);
				$arWatermark['font'] = trim($arParams['PATH_TO_FONT']);
				$arWatermark['color'] = trim($arParams['WATERMARK_COLOR']);
				$arWatermark['use_copyright'] = "N";
			}
			else
			{
				$arWatermark['file'] = $arParams['WATERMARK_FILE'];
				$arWatermark['alpha_level'] = $arParams['WATERMARK_TRANSPARENCY'];
				$arWatermark['size'] = 'real';
				$arWatermark['fill'] = $arParams['WATERMARK_FILE_ORDER'] == 'usual' ? 'exact' : $arParams['WATERMARK_FILE_ORDER'];
			}
		}
		elseif ($Params['packageFields']['photo_watermark_use'] == 'Y')
		{
			$arWatermark = array(
				'type' => $Params['packageFields']['photo_watermark_type'],
				'text' => $Params['packageFields']['photo_watermark_text'],
				'font' => $arParams['PATH_TO_FONT'],
				'position' => $Params['packageFields']['photo_watermark_position'],
				'color' => $Params['packageFields']['photo_watermark_color'],
				'size' => $Params['packageFields']['photo_watermark_size'],
				'fill' => 'resize', // resize | exact | repeat
				'file' => $_SERVER["DOCUMENT_ROOT"].$Params['packageFields']['photo_watermark_path'], // TODO!
				'alpha_level' => $Params['packageFields']['photo_watermark_opacity'],
				'use_copyright' => $Params['packageFields']["photo_watermark_copyright"] == "Y" ? "Y" : "N"
			);
		}

		if($arWatermark)
		{
			// Add watermark here
			foreach($arFiles as $i => $file)
			{
				if ($i == 1) // It's thumbnail skip it
					continue;

				$size = CFile::GetImageSize($file['tmp_name']);
				$type = $size[2];
				$sourceImage = CFile::CreateImage($file['tmp_name'], $type);

				if ($sourceImage)
				{
					$res = CFile::Watermark($sourceImage, $arWatermark);
					if(file_exists($file['tmp_name']))
						unlink($file['tmp_name']);

					switch ($type)
					{
						case IMAGETYPE_GIF:
							imagegif($sourceImage, $file['tmp_name']);
							break;
						case IMAGETYPE_PNG:
							imagealphablending($sourceImage, false);
							imagesavealpha($sourceImage, true);
							imagepng($sourceImage, $file['tmp_name']);
							break;
						default:
							if ($arSourceFileSizeTmp[2] == IMAGETYPE_BMP)
								$file['tmp_name'] .= ".jpg";
							imagejpeg($sourceImage, $file['tmp_name'], 100);
							break;
					}
					if ($sourceImage)
						imagedestroy($sourceImage);
				}
			}
		}
	}

	// Props
	$Prop = array();
	// Additional image copyies
	$ind = -1;
	foreach ($arParams['converters'] as $key => $val)
	{
		$ind++;
		if ($val['code'] == "real_picture" || $val['code'] == "thumbnail")
			continue;
		$Prop[strtoupper($val['code'])] = array("n0" => $arFiles[$ind]);
	}

	$_REQUEST["Public"] = $_REQUEST["Public"] == "N" ? "N" : "Y";
	$Prop["PUBLIC_ELEMENT"] = array("n0" => $_REQUEST["Public"]);
	$Prop["APPROVE_ELEMENT"] = array("n0" => (($arParams["ABS_PERMISSION"] >= "U" || $arParams["APPROVE_BY_DEFAULT"] == "Y") && $_REQUEST["Public"] == "Y") ? "Y" : "X");

	// Real photo
	$Prop["REAL_PICTURE"] = array("n0" => $arFiles[0]);
	$arFields = Array(
		"ACTIVE" => (($arParams["MODERATION"] == "Y" && $arParams["ABS_PERMISSION"] < "U") ? "N" : "Y"),
		"MODIFIED_BY" => $USER->GetID(),
		"IBLOCK_SECTION" => $savedData['SECTION_ID'],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"NAME" => $Info['name'],
		"CODE" => $Info['name'],
		"TAGS" => $Info['tags'],
		//"DETAIL_PICTURE" => $arFiles[0],
		"DETAIL_TEXT" => $Info['description'],
		"DETAIL_TEXT_TYPE" => "text",
		"PREVIEW_PICTURE" => $arFiles[1],
		"PREVIEW_TEXT" => $Info['description'],
		"PREVIEW_TEXT_TYPE" => "text",
		"PROPERTY_VALUES" => $Prop
	);

	//$arFields["NAME"] = (!empty($arFields["NAME"]) ? $arFields["NAME"] : $File["REAL_PICTURE"]["name"]);
	//$arFields["DATE_CREATE"] = (intVal($arRealFile["ExifTimeStamp"]) > 0 ?
	//	ConvertTimeStamp($arRealFile["ExifTimeStamp"], "FULL") : $arFields["DATE_CREATE"]);

	$bs = new CIBlockElement;
	$ID = $bs->Add($arFields);

	if ($ID <= 0)
	{
		$strError = $bs->LAST_ERROR;
		$arErrors = array();
		$arTmp = explode("<br>", $strError);
		foreach($arTmp as $er)
			if (trim($er) != '' && !in_array($er, $arErrors))
				$arErrors[] = array("id" => "BXPH_FUNC_002","text" => $er);
		CImageUploader::SaveError($arErrors);
	}
	else
	{
		$arFields['ID'] = $ID;
		$_SESSION['arUploadedPhotos'][] = $ID;
		CIBlockElement::RecalcSections($ID);

		$arParams['SECTION_ID'] = $savedData['SECTION_ID'];
		$arResult = $Params['~arResult'];

		foreach(GetModuleEvents("photogallery", "OnAfterUpload", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields, $arParams, $arResult));

		// Add thumbnail only for new album
		if ($savedData['NEW_SECTION_NAME'] && !$savedData['NEW_SECTION_PICTURE'])
		{
			$File = $arFiles[0]; // Big picture
			$File['tmp_name_1'] = $File['tmp_name'];
			$File['tmp_name'] = substr($File['tmp_name'], 0, strrpos($File['tmp_name'], "."))."_album_cover.tmp";

			if (CFile::ResizeImageFile($File['tmp_name_1'], $File['tmp_name'], array('width' => $arParams["ALBUM_PHOTO_THUMBS"]["SIZE"], 'height' => $arParams["ALBUM_PHOTO_THUMBS"]["SIZE"]), BX_RESIZE_IMAGE_PROPORTIONAL))
			{
				$bs = new CIBlockSection;
				if ($bs->Update($savedData["SECTION_ID"], Array("PICTURE" => $File), false, false))
				{
					$savedData['NEW_SECTION_PICTURE'] = true;
					CImageUploader::SetSavedData($savedData);
				}
			}
		}
	}
	return $ID;
}

function simpleUploadHandler($arParams, $arResult = array())
{
	global $APPLICATION;

	$APPLICATION->RestartBuffer();
	$sTmpPath = CImageUploader::SetTmpPath($_REQUEST["PackageGuid"], $arParams["PATH_TO_TMP"]);

	$savedData = CImageUploader::GetSavedData();
	if ($savedData['SECTION_ID'])
	{
		unset($savedData['SECTION_ID']);
		CImageUploader::SetSavedData($savedData);
	}

	onBeforeUpload(array(
		'pathToTmp' => $arParams["PATH_TO_TMP"],
		'arParams' => $arParams,
		"~arResult" => $arResult,
		'sessid' => bitrix_sessid(),
		'packageFields' => array(
			'photo_album_id' => $_POST['photo_album_id'],
			'new_album_name' => $_POST['new_album_name'],
			'photo_resize_size' => intVal($_POST['photo_resize_size'])
		)
	));

	$files = $_FILES['photos'];

	$file_count = 0;
	if (!empty($files))
	{
		if (!is_array($files['name']) && is_string($files['name']))
			$files = array(
				'name' => array($files['name']),
				'type' => array($files['type']),
				'tmp_name' => array($files['tmp_name']),
				'error' => array($files['error']),
				'size' => array($files['size'])
			);
		$file_count = count($files['name']);
	}

	if ($arParams['WATERMARK_RULES'] == 'ALL')
	{
		$arWatermark = array(
			'type' => strtolower($arParams['WATERMARK_TYPE']),
			'position' => $arParams['WATERMARK_POSITION']
		);

		if($arParams['WATERMARK_TYPE'] == 'TEXT')
		{
			$arWatermark['coefficient'] = $arParams['WATERMARK_SIZE'];
			$arWatermark['text'] = trim($arParams['WATERMARK_TEXT']);
			$arWatermark['font'] = trim($arParams['PATH_TO_FONT']);
			$arWatermark['color'] = trim($arParams['WATERMARK_COLOR']);
			$arWatermark['use_copyright'] = "N";
		}
		else
		{
			$arWatermark['file'] = $arParams['WATERMARK_FILE'];
			$arWatermark['alpha_level'] = $arParams['WATERMARK_TRANSPARENCY'];
			$arWatermark['size'] = 'real';
			$arWatermark['fill'] = $arParams['WATERMARK_FILE_ORDER'] == 'usual' ? 'exact' : $arParams['WATERMARK_FILE_ORDER'];
		}
	}
	elseif ($_POST['photo_watermark_use'] == 'Y')
	{
		$arWatermark = array(
			'type' => $_POST['photo_watermark_type'],
			'text' => $_POST['photo_watermark_text'],
			'font' => $arParams['PATH_TO_FONT'],
			'position' => $_POST['photo_watermark_position'],
			'color' => $_POST['photo_watermark_color'],
			'size' => $_POST['photo_watermark_size'],
			'fill' => 'resize', // resize | exact | repeat
			'file' => $_SERVER["DOCUMENT_ROOT"].$_POST['photo_watermark_path'],
			'alpha_level' => $_POST['photo_watermark_opacity'],
			'use_copyright' => $_POST["photo_watermark_copyright"] == "Y" ? "Y" : "N"
		);
	}
	else
	{
		$arWatermark = array();
	}

	$arIds = array();
	for ($i = 0; $i < $file_count; $i++)
	{
		$Info = array(
			'name' => $files['name'][$i],
			'filename' => $files['name'][$i],
			'description' => '',
			'tags' => ''
		);
		$arFiles = array();
		$name_i = $files['name'][$i];
		$type_i = $files['type'][$i];
		$tmp_name_i = $files['tmp_name'][$i];
		$error_i = $files['error'][$i];
		$size_i = $files['size'][$i];

		if ($size_i > 0 && $arParams["UPLOAD_MAX_FILE_SIZE"] > 0 && $size_i > $arParams["UPLOAD_MAX_FILE_SIZE"])
		{
			CImageUploader::SaveError(array(array("file" => $name_i, "id" => "BXPH_FUNC_HANDLE_LARGE_SIZE","text" => GetMessage('P_WM_IMG_ERROR04'))));
			continue;
		}
		if ($type_i && strpos(strtolower($type_i), 'image') === false)
		{
			CImageUploader::SaveError(array(array("file" => $name_i, "id" => "BXPH_FUNC_HANDLE_TYPE","text" => GetMessage('P_WM_IMG_ERROR02'))));
			continue;
		}

		$ext_i = GetFileExtension($name_i);
		$name_i = GetFileNameWithoutExtension($name_i);
		$name_i_ = preg_replace("/[^a-zA-Z0-9_]/i", "", $name_i);
		if ($name_i_ != $name_i)
			$name_i = ($name_i_ == '' ? substr(md5(mt_rand()), 0, 6) : '').$name_i_;

		// TODO: exif, iptc
		//$exif = CFile::ExtractImageExif($tmp_name_i);
		//$iptc = CFile::ExtractImageIPTC($tmp_name_i);

		$thumbSize = round($arParams["THUMBNAIL_SIZE"] * 1.8);
		foreach ($arParams['converters'] as $key => $val)
		{
			$destPath = $sTmpPath.$name_i."_".$key.".".$ext_i;
			if ($val["code"] == "real_picture") // original file
			{
				$size = intVal($_POST['photo_resize_size']);
				if ($arParams['ORIGINAL_SIZE'] && $arParams['ORIGINAL_SIZE'] > 0 && ($arParams['ORIGINAL_SIZE'] < $size || $size <= 0))
					$size = $arParams['ORIGINAL_SIZE'];

				$arSize = array('width' => $size, 'height' => $size);
				$jpegQuality = $arParams['JPEG_QUALITY'];
			}
			else if($val["code"] == "thumbnail")
			{
				$arSize = array('width' => $thumbSize, 'height' => $thumbSize);
				$jpegQuality = $arParams['JPEG_QUALITY1'];
			}
			else
			{
				$arSize = array('width' => intVal($val['width']), 'height' => intVal($val['height']));
			}

			if (!$jpegQuality || $jpegQuality < 20)
				$jpegQuality = false;

			$arCurWatermark = ($arSize['width'] > $arParams['WATERMARK_MIN_PICTURE_SIZE'] || $arSize['height'] > $arParams['WATERMARK_MIN_PICTURE_SIZE']) ? $arWatermark : array();

			$res = CFile::ResizeImageFile($tmp_name_i, $destPath, $arSize, BX_RESIZE_IMAGE_PROPORTIONAL, $arCurWatermark, $jpegQuality, array());

			$arFiles[] = array(
				'name' => $files['name'][$i],
				'tmp_name' => $destPath,
				'errors' => $files['error'][$i],
				'type' => $files['type'][$i],
				'size' => '',
				'mode' => $val["code"],
				'width' => '',
				'height' => '',
				'path' => $destPath
			);
		}

		$elementId = handleFile($Info, $arFiles, array('arParams' => $arParams, "~arResult" => $arResult));
		if ($elementId)
			$arIds[] = $elementId;

		foreach ($arFiles as $arFile)
		{
			if (file_exists($arFile['tmp_name']))
				unlink($arFile['tmp_name']);
		}
	}

	$jsResFiles = array();
	if (count($arIds) > 0)
	{
		$rsElement = CIBlockElement::GetList(array(), array("ID" => $arIds), false, false, array("ID", "PREVIEW_PICTURE"));
		while($arElement = $rsElement->Fetch())
		{
			$arFile = CFile::GetFileArray($arElement["PREVIEW_PICTURE"]);
			$jsResFiles[$arFile['ORIGINAL_NAME']] = Array(
				'ID' => $arElement["ID"],
				'PATH' => $arFile['SRC'],
				'WIDTH' => $arFile['WIDTH'],
				'HEIGHT' => $arFile['HEIGHT'],
				'NAME' => $arFile['FILE_NAME']
			);
		}
	}

	$savedData = CImageUploader::GetSavedData();
	if ($savedData['NEW_SECTION_NAME'] && $savedData['SECTION_ID'] > 0)
	{
		$newSectionName = $savedData['NEW_SECTION_NAME'];
		unset($savedData['NEW_SECTION_NAME']);
		CImageUploader::SetSavedData($savedData);
	}

	onAfterUpload(array('arParams' => $arParams, "~arResult" => $arResult));
	// Update redirect url
	$REDIRECT_URL = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $savedData["SECTION_ID"]));
	//$REDIRECT_URL = $REDIRECT_URL.(strpos($REDIRECT_URL, "?") === false ? "?" : "&")."uploader_redirect=Y&sessid=".bitrix_sessid();

	?>
	<script>top.bxiu_simple_res = {
		error: <?=(($savedData['arError'] && count($savedData['arError']) > 0) ? CUtil::PhpToJSObject($savedData['arError']) : '""')?>,
		files: <?= CUtil::PhpToJSObject($jsResFiles)?>,
		redirectUrl: '<?= CUtil::JSEscape($REDIRECT_URL)?>'
		<? if (!empty($newSectionName)):?>
		,newSection: {
			id: <?= intVal($savedData['SECTION_ID'])?>,
			title: '<?= CUtil::JSEscape($newSectionName);?>'
		}
		<?endif;?>
	};</script>
	<?
	$savedData['arError'] = array();
	CImageUploader::SetSavedData($savedData);
	die();
}

if (!function_exists("_get_size"))
{
	function _get_size($v)
	{
		$l = substr($v, -1);
		$ret = substr($v, 0, -1);
		switch(strtoupper($l))
		{
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'K':
				$ret /= 1024;
			break;
		}
		return $ret;
	}
}
?>