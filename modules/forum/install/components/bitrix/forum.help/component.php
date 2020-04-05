<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
	$arParams["CONTENT"] = trim($arParams["CONTENT"]);
// ************************* URL ***********************************************************************
	$URL_NAME_DEFAULT = array(
			"index" => "");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "FID", "TID", "UID", BX_AJAX_PARAM_ID));
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
// *****************************************************************************************
	$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
	// $arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
// **************************** TITLE ******************************************************
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
// *****************************************************************************************
// *************************/Input params***************************************************************
	$arResult["TEXT_MESSAGE"] = "";
	if (!empty($arParams["CONTENT"]))
	{
		$arParams["FILE_NAME"] = $_SERVER["DOCUMENT_ROOT"].$arParams["CONTENT"];
		if (is_file($arParams["FILE_NAME"]))
		{
			$arResult["TEXT_MESSAGE"] = $GLOBALS["APPLICATION"]->GetFileContent($arParams["FILE_NAME"]);
		}
	}
	if (empty($arResult["TEXT_MESSAGE"]))
		$arResult["TEXT_MESSAGE"] = GetMessage("F_CONTENT");
// *****************************************************************************************
	if ($arParams["SET_NAVIGATION"] != "N")
		$APPLICATION->AddChainItem(GetMessage("F_TITLE_NAV"));
	if ($arParams["SET_TITLE"] != "N")
		$APPLICATION->SetTitle(GetMessage("F_TITLE"));
	// if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
		// CForumNew::ShowPanel(0, 0, false);
// *****************************************************************************************
	$this->IncludeComponentTemplate();
?>