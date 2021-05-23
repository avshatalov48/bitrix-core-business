<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// if ($arParams["PERMISSION"] >= "U") // 
// {
	// $url = CComponentEngine::MakePathFromTemplate($arResult["URL_TEMPLATES"]["section_edit"], array("SECTION_ID" => intval($arResult["VARIABLES"]["SECTION_ID"]), "ACTION" => "new"));
// }
if ($arParams["SHOW_NAVIGATION"] == "Y" && mb_strtolower($arResult["PAGE_NAME"]) != "index")
{
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

		$pos = bxstrrpos($path, "/");
		if($path.'/' == SITE_DIR || $path == '' || $pos === false)
			break;
		$path = mb_substr($path, 0, $pos + 1);
	}

	if ($arResult["PAGE_NAME"] == "detail" /* || $arResult["PAGE_NAME"] == "section"*/)
		$GLOBALS["PHOTO_HIDE_LAST_BREADCRUMB"] = true;
	
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

?>