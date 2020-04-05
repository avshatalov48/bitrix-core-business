<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
__IncludeLang(dirname(__FILE__).'/lang/'.LANGUAGE_ID.'/'.basename(__FILE__));
?>
<?

if ($this->__component->__parent && $this->__component->__parent->arResult && array_key_exists("PATH_TO_MESSAGE_TO_GROUP", $this->__component->__parent->arResult))
	$arParams["PATH_TO_MESSAGE_TO_GROUP"] = $this->__component->__parent->arResult["PATH_TO_MESSAGE_TO_GROUP"];


if (intval($arResult["Group"]["IMAGE_ID"]) <= 0)
{
	$arResult["Group"]["IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, SITE_ID);
}

$arResult["Group"]["IMAGE_FILE"] = array("src" => "");

if (intval($arResult["Group"]["IMAGE_ID"]) > 0)
{

	$imageFile = CFile::GetFileArray($arResult["Group"]["IMAGE_ID"]);
	if ($imageFile !== false)
	{
		$arFileTmp = CFile::ResizeImageGet(
			$imageFile,
			array("width" => 50, "height" => 50),
			BX_RESIZE_IMAGE_EXACT,
			true
		);
	}

	if($arFileTmp && array_key_exists("src", $arFileTmp))
		$arResult["Group"]["IMAGE_FILE"] = $arFileTmp;
}

$arResult["Urls"]["MessageToGroup"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGE_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
$arResult["Title"]["blog"] = ((array_key_exists("blog", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["blog"]) > 0) ? $arResult["ActiveFeatures"]["blog"] : GetMessage("SONET_CM_BLOG"));	
?>