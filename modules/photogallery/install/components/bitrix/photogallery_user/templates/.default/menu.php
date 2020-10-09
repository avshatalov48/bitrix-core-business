<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
ob_start();
?><?$result = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.user",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"PAGE_NAME" => $arResult["PAGE_NAME"],
		"USER_ALIAS" => $arResult["VARIABLES"]["USER_ALIAS"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
		"ANALIZE_SOCNET_PERMISSION" => $arParams["ANALIZE_SOCNET_PERMISSION"],

		"SORT_BY" => $arParams["SECTION_SORT_BY"],
		"SORT_ORD" => $arParams["SECTION_SORT_ORD"],

		"INDEX_URL" => $arResult["URL_TEMPLATES"]["index"],
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		"GALLERIES_URL" => $arResult["URL_TEMPLATES"]["galleries"],
		"GALLERY_EDIT_URL" => $arResult["URL_TEMPLATES"]["gallery_edit"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],

		"RETURN_ARRAY" => "Y",
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		"ONLY_ONE_GALLERY" => $arParams["ONLY_ONE_GALLERY"],
		"GALLERY_GROUPS" => $arParams["GALLERY_GROUPS"],
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],

		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],

		"GALLERY_AVATAR_SIZE"	=>	$arParams["GALLERY_AVATAR_SIZE"]
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?><?
$res = ob_get_clean();
$this->__component->__photogallery_values = $result;

if ($arParams["SHOW_NAVIGATION"] != "N" && $arResult["PAGE_NAME"] != "INDEX"):
// text from main
	CMain::InitPathVars($site, $path);
	$DOC_ROOT = CSite::GetSiteDocRoot($site);

	$path = $GLOBALS["APPLICATION"]->GetCurDir();
	$arChain = Array();

	while(true)
	{
		$path = rtrim($path, "/");

		$chain_file_name = $DOC_ROOT.$path."/.section.php";
		if(file_exists($chain_file_name))
		{
			$sSectionName = "";
			include($chain_file_name);
			if($sSectionName <> '')
				$arChain[] = Array("TITLE"=>$sSectionName, "LINK"=>$path."/");
		}

		if($path.'/' == SITE_DIR)
			break;

		if($path == '')
			break;
		$pos = bxstrrpos($path, "/");
		if($pos===false)
			break;
		$path = mb_substr($path, 0, $pos + 1);
	}
	if ($arResult["PAGE_NAME"] == "DETAIL")
	{
		$GLOBALS["PHOTO_HIDE_LAST_BREADCRUMB"] = true;
	}
	elseif ($arResult["PAGE_NAME"] == "GALLERY")
	{
		$count = count($APPLICATION->arAdditionalChain) - 1;
		unset($APPLICATION->arAdditionalChain[$count]["LINK"]);
	}

	// Include breadcrumbs navigation to the top of all component pages
	if ($arParams["SHOW_NAVIGATION"] == "Y")
	{
		$GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:breadcrumb", ".default",
			Array(
				"START_FROM" => count($arChain) - 1,
				"PATH" => "",
				"SITE_ID" => "",
			), $component,
			array("HIDE_ICONS" => "Y")
		);
	}

endif;
?>
<div class="empty-clear"></div>
<?
if ($arResult["PAGE_NAME"] == "GALLERY" && !empty($result) && !empty($result["ALL"]["GALLERY"])):
	?><?=$res?><?
endif;
?>