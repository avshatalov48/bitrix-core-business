<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arParams["WATERMARK_MIN_PICTURE_SIZE"] = intVal($arParams["WATERMARK_MIN_PICTURE_SIZE"]);

$test_str = '/bitrix/urlrewrite.php?SEF_APPLICATION_CUR_PAGE_URL=';
if (strncmp(POST_FORM_ACTION_URI, $test_str, 52) === 0)
{
	$sUrlPath = urldecode(substr(POST_FORM_ACTION_URI, 52));
	$sUrlPath = CHTTP::urlDeleteParams($sUrlPath, array("view_mode", "sessid", "uploader_redirect"), true);
	$arParams["ACTION_URL"] = htmlspecialcharsbx("/bitrix/urlrewrite.php?SEF_APPLICATION_CUR_PAGE_URL=".urlencode($sUrlPath));
}
else
{
	$arParams["ACTION_URL"] = CHTTP::urlDeleteParams(htmlspecialcharsback(POST_FORM_ACTION_URI), array("view_mode", "sessid", "uploader_redirect"), true);
}

// Include upload handler functions
require_once(str_replace(array("\\", "//"), "/", dirname(__FILE__)."/functions.php"));

$arParams["UPLOADER_ID"] = getImageUploaderId(); // Unique ID of the Image uploader on the page

$arParams["VIEW_MODE"] = "form";

if ($arParams["USE_WATERMARK"] == "Y")
{
	if (isset($_REQUEST['watermark_iframe']) && $_REQUEST['watermark_iframe'] == 'Y' && check_bitrix_sessid())
	{
		$UploadError = false;
		$pathto = '';
		if ($_SERVER['REQUEST_METHOD'] == "POST")
		{
			$file = $_FILES['watermark_img'];
			$checkImgMsg = CFile::CheckImageFile($file);
			if ($file['error'] != 0)
			{
				$UploadError = "[IU_WM01] ".GetMessage("P_WM_IMG_ERROR01");
			}
			elseif(strlen($checkImgMsg) > 0 || $checkImgMsg === "")
			{
				$UploadError = "[IU_WM02] ".($checkImgMsg === "" ? GetMessage("P_WM_IMG_ERROR02") : $checkImgMsg);
			}
			else
			{
				$imgArray = CFile::GetImageSize($file["tmp_name"]);
				if(is_array($imgArray))
				{
					$width = $imgArray[0];
					$height = $imgArray[1];
				}

				$pathto = CTempFile::GetDirectoryName(1).'/'."watermark_".md5($file["name"]).GetFileExtension($file["name"]);
				CheckDirPath($pathto);

				$pathtoRel = substr($pathto, strlen($_SERVER["DOCUMENT_ROOT"]));

				if(!move_uploaded_file($file["tmp_name"], $pathto))
					$UploadError = "[IU_WM03] ".GetMessage("P_WM_IMG_ERROR03");
			}
		}
		$APPLICATION->RestartBuffer();
		?>
			<script>
			<?if ($UploadError === false && $pathto != ''):?>
				top.bxiu_wm_img_res = {path: '<?= CUtil::JSEscape($pathtoRel)?>', width: '<?= $width?>', height: '<?= $height?>'};
			<?elseif($UploadError !== false):?>
				top.bxiu_wm_img_res = {error: '<?= $UploadError?>'};
			<?endif;?>
			</script>
		<?
		die();
	}
}

if (!CModule::IncludeModule("photogallery"))
	return ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
if(!CModule::IncludeModule("iblock"))
	return ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
if ($arParams["BEHAVIOUR"] == "USER" && empty($arParams["USER_ALIAS"]))
	return ShowError(GetMessage("P_GALLERY_EMPTY"));

// Include updater class
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/uploader.php");

$arParams["UPLOADER_TYPE"] = 'form';

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intVal($arParams["IBLOCK_ID"]);
	$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
	$arParams["USER_ALIAS"] = trim($arParams["USER_ALIAS"]);
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);

	$arParams["IMAGE_UPLOADER_ACTIVEX_CLSID"] = "718B3D1E-FF0C-4EE6-9F3B-0166A5D1C1B9";
	$arParams["IMAGE_UPLOADER_ACTIVEX_CONTROL_VERSION"] = "6,0,20,0";
	$arParams["IMAGE_UPLOADER_JAVAAPPLET_VERSION"] = "6.0.20.0";

	$arParams["THUMBNAIL_ACTIVEX_CLSID"] = "58C8ACD5-D8A6-4AC8-9494-2E6CCF6DD2F8";
	$arParams["THUMBNAIL_ACTIVEX_CONTROL_VERSION"] = "3,5,204,0";
	$arParams["THUMBNAIL_JAVAAPPLET_VERSION"] = "1.1.81.0";
	$arParams["PATH_TO_TMP"] = CTempFile::GetDirectoryName(12, "uploader");

/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"index" => "",
		"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
		"section" => "PAGE_NAME=section".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#",
		"section_edit" => "PAGE_NAME=section_edit".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#"
	);

	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".strToUpper($URL)."_URL"]);
	}

	$arParams["SUCCESS_URL"] = CHTTP::urlDeleteParams(CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"])), array("sessid", "uploader_redirect"), true);

	$arParams["REDIRECT_URL"] = $arParams["ACTION_URL"];
	$arParams["REDIRECT_URL"] = CHTTP::urlDeleteParams($arParams["REDIRECT_URL"], array("clear_cache", "bitrix_include_areas", "bitrix_show_mode", "back_url_admin", "bx_photo_ajax", "change_view_mode_data", "sessid", "uploader_redirect"));
	$arParams["REDIRECT_URL"] .= (strpos($arParams["REDIRECT_URL"], "?") === false ? "?" : "&")."uploader_redirect=Y&sessid=".bitrix_sessid();

	$arParams["SIMPLE_FORM_URL"] = $APPLICATION->GetCurPageParam("view_mode=form&".bitrix_sessid_get(), array("view_mode", "sessid", "uploader_redirect"));
	$arParams["MULTIPLE_FORM_URL"] = $APPLICATION->GetCurPageParam("view_mode=applet&".bitrix_sessid_get(), array("view_mode", "sessid", "uploader_redirect"));

	$arParams["DETAIL_DROP_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_EDIT_URL"], array("USER_ALIAS" => $arParams["USER_ALIAS"], "ACTION" => "drop"));

/***************** ADDITIONAL **************************************/
	$arParams["UPLOAD_MAX_FILE"] = 1;

	$max_upload_size = min(_get_size(ini_get('post_max_size')), _get_size(ini_get('upload_max_filesize')));
	$arResult["UPLOAD_MAX_FILE_SIZE"] = intVal($arParams["UPLOAD_MAX_FILE_SIZE"]);
	if ($arResult["UPLOAD_MAX_FILE_SIZE"] <= 0 || $arResult["UPLOAD_MAX_FILE_SIZE"] > $max_upload_size)
		$arResult["UPLOAD_MAX_FILE_SIZE"] = $max_upload_size;
	$arResult["UPLOAD_MAX_FILE_SIZE_MB"] = $arParams["UPLOAD_MAX_FILE_SIZE"];
	$arResult["UPLOAD_MAX_FILE_SIZE"] = $arParams["UPLOAD_MAX_FILE_SIZE"] * 1024 * 1024;
	$arParams["UPLOAD_MAX_FILE_SIZE"] = $arResult["UPLOAD_MAX_FILE_SIZE"];

	// Additional sights
	$arParams["PICTURES_INFO"] = @unserialize(COption::GetOptionString("photogallery", "pictures"));
	$arParams["PICTURES_INFO"] = (is_array($arParams["PICTURES_INFO"]) ? $arParams["PICTURES_INFO"] : array());
	$arParams["PICTURES"] = array();
	if (!empty($arParams["PICTURES_INFO"]) && is_array($arParams["ADDITIONAL_SIGHTS"]) && !empty($arParams["ADDITIONAL_SIGHTS"]))
	{
		foreach ($arParams["PICTURES_INFO"] as $key => $val)
		{
			if (in_array(str_pad($key, 5, "_").$val["code"], $arParams["ADDITIONAL_SIGHTS"]))
			{
				$arParams["PICTURES"][$val["code"]] = array(
					"size" => $arParams["PICTURES_INFO"][$key]["size"],
					"quality" => $arParams["PICTURES_INFO"][$key]["quality"]
				);
			}
		}
	}
	$arParams["MODERATION"] = ($arParams["MODERATION"] == "Y" ? "Y" : "N");
	$arParams["PUBLIC_BY_DEFAULT"] = ($arParams["SHOW_PUBLIC"] == "N" || $arParams["PUBLIC_BY_DEFAULT"] != "N" ? "Y" : "N");
	$arParams["APPROVE_BY_DEFAULT"] = ($arParams["APPROVE_BY_DEFAULT"] == "N" ? "N" : "Y");

	// Sizes
	$arParams['SIZES'] = array(1280, 1024, 800);

	$arParams["ORIGINAL_SIZE"] = intVal($arParams["ORIGINAL_SIZE"]);
	$arParams['SIZES_SHOWN'] = array();
	if (!in_array($arParams["ORIGINAL_SIZE"], $arParams['SIZES']) && $arParams["ORIGINAL_SIZE"] > 0)
		$arParams['SIZES'] = array_merge(array($arParams["ORIGINAL_SIZE"]), $arParams['SIZES']);

	foreach ($arParams['SIZES'] as $size)
		if ($arParams["ORIGINAL_SIZE"] <= 0 || $arParams["ORIGINAL_SIZE"] >= $size)
			$arParams['SIZES_SHOWN'][] = array($size, $size."x".(round($size * 0.75)));

	if ($arParams["SHOW_RESIZER"] != "N")
		$arParams["SHOW_RESIZER"] = ($arParams["ORIGINAL_SIZE"] <= 0 || $arParams["ORIGINAL_SIZE"] > $arParams['SIZES'][0][0]) ? 'Y' : 'N';

	$arParams["SHOW_TITLE"] = $arParams["SHOW_TITLE"] == 'Y' ? 'Y' : 'N';
	$arParams['SHOW_DETAIL_PHOTO_PAGE'] = 'N'; //
	$arParams["SHOW_PUBLIC"] = ($arParams["SHOW_PUBLIC"] == "N" ? "N" : "Y");
	$arParams["SHOW_TAGS"] = (IsModuleInstalled("search") && $arParams["SHOW_TAGS"] == 'Y') ? 'Y' : 'N';

	$arParams["SHOW_TITLE"] = 'Y';

	if ($arParams["BEHAVIOUR"] != "USER")
		$arParams["SHOW_PUBLIC"] = "N";

	if ($arParams["USE_WATERMARK"] == "Y")
	{
		$arParams["WATERMARK_RULES"] = ($arParams["WATERMARK_RULES"] == "ALL" ? "ALL" : "USER");
		$arParams["WATERMARK_TYPE"] = ($arParams["WATERMARK_TYPE"] == "TEXT" ? "TEXT" : "PICTURE");
		$arParams["WATERMARK_TEXT"] = trim($arParams["WATERMARK_TEXT"]);

		if($arParams["WATERMARK_RULES"] == 'ALL')
			$arParams["SHOW_WATERMARK"] = "N";

		// We have ugly default font but it's better than no font at all
		if (!$arParams["PATH_TO_FONT"])
			$arParams["PATH_TO_FONT"] = "default.ttf";

		$arParams["PATH_TO_FONT"] = str_replace(array("\\", "//"), "/", trim($arParams["PATH_TO_FONT"]));
		if(file_exists($_SERVER['DOCUMENT_ROOT'].$arParams["PATH_TO_FONT"]))
		{
			$arParams["PATH_TO_FONT"] = $_SERVER['DOCUMENT_ROOT'].$arParams["PATH_TO_FONT"];
		}
		else
		{
			$arParams["PATH_TO_FONT"] = str_replace(array("\\", "//"), "/", $_SERVER['DOCUMENT_ROOT']."/".BX_ROOT."/modules/photogallery/fonts/".trim($arParams["PATH_TO_FONT"]));
			$arParams["PATH_TO_FONT"] = (file_exists($arParams["PATH_TO_FONT"]) ? $arParams["PATH_TO_FONT"] : "");
		}

		$arParams["WATERMARK_COLOR"] = '#'.trim($arParams["WATERMARK_COLOR"], ' #');
		$arParams["WATERMARK_SIZE"] = intVal($arParams["WATERMARK_SIZE"]);
		$arParams["WATERMARK_FILE_REL"] = '/'.trim($arParams["WATERMARK_FILE"], ' /');
		$arParams["WATERMARK_FILE"] = str_replace(array("\\", "//"), "/", $_SERVER['DOCUMENT_ROOT'].$arParams["WATERMARK_FILE_REL"]);
		$arParams["WATERMARK_FILE"] = (file_exists($arParams["WATERMARK_FILE"]) ? $arParams["WATERMARK_FILE"] : "");
		$arParams["WATERMARK_FILE_ORDER"] = strtolower($arParams["WATERMARK_FILE_ORDER"]);
		$arParams["WATERMARK_POSITION"] = trim($arParams["WATERMARK_POSITION"]);

		if ($arParams["WATERMARK_FILE"] && CFile::IsImage($arParams["WATERMARK_FILE"]))
		{
			$imgArray = CFile::GetImageSize($arParams["WATERMARK_FILE"]);
			$arParams["WATERMARK_FILE_WIDTH"] = $imgArray[0];
			$arParams["WATERMARK_FILE_HEIGHT"] = $imgArray[1];
		}
		else
		{
			$arParams["WATERMARK_FILE"] = "";
			$arParams["WATERMARK_FILE_REL"] = "";
		}

		$arPositions = array("TopLeft", "TopCenter", "TopRight", "CenterLeft", "Center", "CenterRight", "BottomLeft", "BottomCenter", "BottomRight");
		$arPositions2 = array("tl", "tc", "tr", "ml", "mc", "mr", "bl", "bc", "br");

		if (in_array($arParams["WATERMARK_POSITION"], $arPositions2))
			$arParams["WATERMARK_POSITION"] = str_replace($arPositions2, $arPositions, $arParams["WATERMARK_POSITION"]);
		else
			$arParams["WATERMARK_POSITION"] = "BottomRight";

		$arParams["WATERMARK_TRANSPARENCY"] = trim($arParams["WATERMARK_TRANSPARENCY"]);
		$arParams["WATERMARK_MIN_PICTURE_SIZE"] = (intVal($arParams["WATERMARK_MIN_PICTURE_SIZE"]) > 0 ? intVal($arParams["WATERMARK_MIN_PICTURE_SIZE"]) : 800);

		if (!function_exists("gd_info"))
			$arParams["USE_WATERMARK"] = "N";
	}

	// Check REAL_PICTURE property
	$rsProperty = CIBlockProperty::GetList(array(), array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"CODE" => 'REAL_PICTURE'
	));
	$arProperty = $rsProperty->Fetch();
	if(!$arProperty)
	{
		$obProperty = new CIBlockProperty;
		$obProperty->Add(array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"ACTIVE" => "Y",
			"PROPERTY_TYPE" => "F",
			"FILE_TYPE" => "jpg, gif, bmp, png, jpeg",
			"MULTIPLE" => 'N',
			"NAME" => GetMessage("P_REAL_PICTURE"),
			"CODE" => "REAL_PICTURE"
		));
	}

	// Get user options
	$arParams["USER_SETTINGS"] = CUserOptions::GetOption('main', $arParams["UPLOADER_ID"]);

	$arParams["GALLERY_SIZE"] = intVal($arParams["GALLERY_SIZE"]) * 1024 * 1024;
	$arParams["ALBUM_PHOTO_THUMBS"] = array("SIZE" => (intVal($arParams["ALBUM_PHOTO_THUMBS_WIDTH"]) > 0 ? intVal($arParams["ALBUM_PHOTO_THUMBS_WIDTH"]) : 120));

	// Thumbnail size
	$arParams["THUMBNAIL_SIZE"] = (intval($arParams["THUMBNAIL_SIZE"]) > 0) ? intval($arParams["THUMBNAIL_SIZE"]) : 90;
	if ($arParams["THUMBNAIL_SIZE"] < 50)
		$arParams["THUMBNAIL_SIZE"] = 50;

	// We use only square thumbnails, so we increase thumbnail size for better quality
	$thumbSize = round($arParams["THUMBNAIL_SIZE"] * 1.8);

	$arParams["ORIGINAL_SIZE"] = intVal($arParams["ORIGINAL_SIZE"]);
	$arParams["NEW_ALBUM_NAME"] = strLen(GetMessage("P_NEW_ALBUM")) > 0 ? GetMessage("P_NEW_ALBUM") : "New album";

	/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
	$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); //Turn off by default
	if(!isset($arParams["CACHE_TIME"]))
		$arParams["CACHE_TIME"] = 3600;
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/

if ($_REQUEST["uploader_redirect"] == "Y" && check_bitrix_sessid())
{
	$arErrors = CImageUploader::CheckErrors();
	$savedData = CImageUploader::GetSavedData();

	if (is_array($savedData) || ($arErrors && count($arErrors) > 0))
	{
		if ((!$savedData['SECTION_ID'] || ($savedData['UPLOADING_START'] == "Y" && $savedData['UPLOADING_SUCCESS'] != "Y")) && !$arErrors)
		{
			$arErrors = array(array(
				'id' => "BXPH_UNKNOWN_UPLOAD",
				'text' => GetMessage('P_BXPH_UNKNOWN_UPLOAD')
			));
		}

		CImageUploader::CleanSavedData();
		if (!$arErrors)
		{
			$arParams["SUCCESS_URL"] = CHTTP::urlDeleteParams(CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $savedData["SECTION_ID"])), array("sessid", "uploader_redirect"));
			return LocalRedirect($arParams['SUCCESS_URL']);
		}
		else
		{
			$arResult['ERROR_MESSAGE'] = "";
			foreach ($arErrors as $err)
				$arResult['ERROR_MESSAGE'] = '['.$err['id'].'] '.(strlen($err['text']) > 0 ? $err['text'] : GetMessage('P_UNKNOWN_ERROR'))."<br>";
		}
	}
}

/********************************************************************
				Main data
********************************************************************/
$oPhoto = new CPGalleryInterface(
	array(
		"IBlockID" => $arParams["IBLOCK_ID"],
		"GalleryID" => $arParams["USER_ALIAS"],
		"Permission" => $arParams["PERMISSION_EXTERNAL"]),
	array(
		"cache_time" => $arParams["CACHE_TIME"],
		"set_404" => $arParams["SET_STATUS_404"]
		)
	);

if (!$oPhoto)
	return false;

$arResult["GALLERY"] = $oPhoto->Gallery;
$arParams["PERMISSION"] = $oPhoto->User["Permission"];
$arResult["SECTION"] = array();

/************** SECTION *************************************************/
if ($arParams["PERMISSION"] < "W")
	return ShowError(GetMessage("P_DENIED_ACCESS"));

if ($arParams["SECTION_ID"] > 0)
{
	$res = $oPhoto->GetSection($arParams["SECTION_ID"], $arResult["SECTION"]);
	if ($res > 400)
		return false;
	elseif ($res == 301)
	{
		$url = CComponentEngine::MakePathFromTemplate(
			$arParams["~SECTION_URL"],
			array(
				"USER_ALIAS" => $arGallery["CODE"],
				"SECTION_ID" => $arParams["SECTION_ID"]));
		LocalRedirect($url, false, "301 Moved Permanently");
		return false;
	}
}
$arParams["ABS_PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
if ($arParams["ABS_PERMISSION"] < "W" && 0 < $arParams["GALLERY_SIZE"] && $arParams["GALLERY_SIZE"] < intVal($arResult["GALLERY"]["UF_GALLERY_SIZE"]))
	return ShowError(GetMessage("P_GALLERY_NOT_SIZE"));

/********************************************************************
				/Main data
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
$arError = array();

/************** URL ************************************************/
$arResult["URL"] = array(
	"SECTION" => CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array("USER_ALIAS" => $arResult["GALLERY"]["CODE"], "SECTION_ID" => $arParams["SECTION_ID"])),
	"SECTION_EMPTY" => CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array("USER_ALIAS" => $arResult["GALLERY"]["CODE"], "SECTION_ID" => "#SECTION_ID#")),
	"GALLERY" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"], array("USER_ALIAS" => $arResult["GALLERY"]["CODE"])),
	"INDEX" => CComponentEngine::MakePathFromTemplate($arParams["~INDEX_URL"], array())
);
foreach ($arResult["URL"] as $key => $val)
{
	$arResult["URL"]["~".$key] = $val;
	$arResult["URL"][$key] = htmlspecialcharsbx($val);
}
$bVarsFromForm = false;
$arConverters = array(
	"real_picture" => array(
		"code" => "real_picture",
		"width" => ($arParams["ORIGINAL_SIZE"] > 0 ? $arParams["ORIGINAL_SIZE"] : false),
		"height" => ($arParams["ORIGINAL_SIZE"] > 0 ? $arParams["ORIGINAL_SIZE"] : false)
	),
	"thumbnail" => array(
		"code" => "thumbnail",
		"width" => $thumbSize,
		"height" => $thumbSize
	)
);

if ($arParams['SHOW_DETAIL_PHOTO_PAGE'] == 'Y')
{
	$arConverters["preview"] = array(
		"code" => "preview",
		"width" => $arParams["PREVIEW_SIZE"]["SIZE"],
		"height" => $arParams["PREVIEW_SIZE"]["SIZE"]
	);
}


if (is_array($arParams["PICTURES"]) && !empty($arParams["PICTURES"]))
{
	foreach ($arParams["PICTURES"] as $key => $val)
	{
		$arConverters[$key] = array(
			"code" => $key,
			"width" => $val["size"],
			"height" => $val["size"]
		);
	}
}
$arParams['converters'] = $arConverters;
$res = new CPhotoUploader($arParams, $arResult);
$params = array(
	"copies" => array_diff_key($arParams['converters'], array("real_picture" => true)),
	"allowUpload" => "I",
	"uploadFileWidth" => $arParams["ORIGINAL_SIZE"],
	"uploadFileHeight" => $arParams["ORIGINAL_SIZE"],
	"uploadMaxFilesize" => $arParams["UPLOAD_MAX_FILE_SIZE"],
	"events" => array(
		"onUploadIsStarted" => array($res, "onBeforeUpload"),
		"onUploadIsContinued" => array($res, "onBeforeUpload"),
		"onPackageIsFinished" => array($res, "onAfterUpload"),
		"onFileIsUploaded" => array($res, "handleFile")
	)
);

if (class_exists("CFileUploader"))
{
	$arParams["bxu"] = new CFileUploader($params, "get");
	$arParams["bxu"]->checkPost();
}
/********************************************************************
				/Default params
********************************************************************/
/********************************************************************
				Action
********************************************************************/
if (($_REQUEST["save_upload"] == "Y" && $_REQUEST["uploader_redirect"] != "Y" &&
		$_SERVER['REQUEST_METHOD'] == "POST" && !empty($_POST)) || isset($_POST["PackageGuid"]))
{
	//RestartBuffer and DIE inside!
	simpleUploadHandler($arParams, array("SECTION" => $arResult["SECTION"], "URL" => $arResult["URL"], "GALLERY" => $arResult["GALLERY"]));
	return;
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
/************** Chain Item *****************************************/
if (!empty($arParams["SECTION_ID"]))
{
	$arFilter = array(
		"ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y");
		$arFilter["ID"]=$arParams["SECTION_ID"];
	$db_res = CIBlockSection::GetList(array(), $arFilter);
	if ($db_res && $arResult["SECTION"] = $db_res->GetNext())
	{
		if ($rsPath = GetIBlockSectionPath($arParams["IBLOCK_ID"], $arResult["SECTION"]["ID"]))
			while ($arPath = $rsPath->GetNext())
				$arResult["SECTION"]["PATH"][] = $arPath;
	}
}
elseif ($arParams["BEHAVIOUR"] == "USER")
{
	$arResult["SECTION"] = $arResult["GALLERY"];
	$arResult["SECTION"]["PATH"] = array($arResult["GALLERY"]);
}
$arResult["SECTION"]["PATH"] = (is_array($arResult["SECTION"]["PATH"]) ? $arResult["SECTION"]["PATH"] : array());
/************** Sections List **************************************/
$arResult["SECTION_LIST"] = array();
$arFilter = array("ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y",
	"IBLOCK_ID" => $arParams["IBLOCK_ID"], "IBLOCK_ACTIVE" => "Y");
if ($arParams["BEHAVIOUR"] == "USER")
{
	$arFilter["!ID"] = $arResult["GALLERY"]["ID"];
	$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
	$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
}
$rsIBlockSectionList = CIBlockSection::GetTreeList($arFilter);
$iDiff = ($arParams["BEHAVIOUR"] == "USER" ? 2 : 1);
while ($arSection = $rsIBlockSectionList->GetNext())
{
	$len = ($arSection["DEPTH_LEVEL"] - $iDiff);
	$arSection["NAME"] = ($len > 0 ? str_repeat(" . ", $len) : "").$arSection["NAME"];
	$arResult["SECTION_LIST"][$arSection["ID"]] = $arSection["NAME"];
}
/********************************************************************
				/Data
********************************************************************/
/********************************************************************
				For custom components
********************************************************************/
foreach ($arResult["URL"] as $key => $val)
	$arResult[$key."_LINK"] = $val;
/********************************************************************
/For custom components
********************************************************************/
CUtil::InitJSCore(array('window', 'ajax'));

// Clean saved data before show
CImageUploader::CleanSavedData();

$this->IncludeComponentTemplate();
/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("P_TITLE"));
/************** Breadcrumb *****************************************/
if ($arParams["SET_NAV_CHAIN"] == "Y")
{
	$bFound = ($arParams["BEHAVIOUR"] != "USER");
	foreach ($arResult["SECTION"]["PATH"] as $arPath)
	{
		if (!$bFound)
		{
			$bFound = $arResult["GALLERY"]["ID"] == $arPath["ID"];
			continue;
		}
		$APPLICATION->AddChainItem($arPath["NAME"], CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arPath["ID"])));
	}
	$APPLICATION->AddChainItem(GetMessage("P_TITLE_CHAIN"));
}
/********************************************************************
				/Standart
********************************************************************/
?>