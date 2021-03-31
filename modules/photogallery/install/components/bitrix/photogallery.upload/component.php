<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
// Include upload handler functions
require_once(__DIR__."/functions.php");
$arParams["UPLOADER_ID"] = getImageUploaderId(); // Unique ID of the Image uploader on the page
$arParams["VIEW_MODE"] = "form";
//region *************Save watermark file*************
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
			elseif($checkImgMsg <> '' || $checkImgMsg === "")
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

				$pathtoRel = mb_substr($pathto, mb_strlen($_SERVER["DOCUMENT_ROOT"]));

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
//endregion
//region *************Input Params*************
$arResult["UPLOAD_MAX_FILE_SIZE"] = $arParams["UPLOAD_MAX_FILE_SIZE"];
$arResult["UPLOAD_MAX_FILE_SIZE_MB"] = round($arParams["UPLOAD_MAX_FILE_SIZE"]/1024/1024, 2);

$arParams['WATERMARK_FILE_REL'] = '/'.trim($arParams['WATERMARK_FILE'], ' /');

$arParams['SIZES_SHOWN'] = [];
foreach ($arParams['SIZES'] as $size)
{
	if ($arParams["ORIGINAL_SIZE"] <= 0 || $arParams["ORIGINAL_SIZE"] >= $size)
	{
		$arParams['SIZES_SHOWN'][] = array($size, $size."x".(round($size * 0.75)));
	}
}
if ($arParams["SHOW_RESIZER"] != "N")
{
	$arParams["SHOW_RESIZER"] = ($arParams["ORIGINAL_SIZE"] <= 0
		|| $arParams["ORIGINAL_SIZE"] > $arParams['SIZES'][0][0]) ? 'Y' : 'N';
}

$arParams["SHOW_TITLE"] = $arParams["SHOW_TITLE"] == 'Y' ? 'Y' : 'N';
$arParams["SHOW_PUBLIC"] = ($arParams["SHOW_PUBLIC"] == "N" || $arParams["BEHAVIOUR"] != "USER" ? "N" : "Y");
$arParams["SHOW_TAGS"] = (IsModuleInstalled("search") && $arParams["SHOW_TAGS"] == 'Y') ? 'Y' : 'N';
$arParams["SHOW_TITLE"] = 'Y';
// Get user options
$arParams['UPLOADER_ID'] = getImageUploaderId(); // Unique ID of the Image uploader on the page
$arParams["USER_SETTINGS"] = CUserOptions::GetOption('main', $arParams["UPLOADER_ID"]);
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
//endregion

/********************************************************************
				Default values
********************************************************************/
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
$arResult['UPLOAD_MAX_FILE_SIZE_MB'] = round($arParams['UPLOAD_MAX_FILE_SIZE'] / 1024 / 1024, 2);
$arResult['UPLOAD_MAX_FILE_SIZE'] = $arParams['UPLOAD_MAX_FILE_SIZE'];

/********************************************************************
				/Default params
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