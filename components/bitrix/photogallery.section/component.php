<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("photogallery"))
	return ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
elseif (!IsModuleInstalled("iblock"))
	return ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
elseif (empty($arParams["SECTION_CODE"]) && intval($arParams["SECTION_ID"]) <= 0)
	return ShowError(GetMessage("P_SECTION_EMPTY"));
elseif ($arParams["BEHAVIOUR"] == "USER" && empty($arParams["USER_ALIAS"]))
	return ShowError(GetMessage("P_GALLERY_EMPTY"));

if (empty($arParams["INDEX_URL"]) && !empty($arParams["SECTIONS_TOP_URL"]))
	$arParams["INDEX_URL"] = $arParams["SECTIONS_TOP_URL"];
/********************************************************************
				Input params
********************************************************************/
//***************** BASE *******************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"] ?? '');
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
	$arParams["SECTION_CODE"] = trim($arParams["SECTION_CODE"] ?? '');
	$arParams["USER_ALIAS"] = trim($arParams["USER_ALIAS"] ?? '');
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"] ?? '');
//***************** URL ********************************************/
	$URL_NAME_DEFAULT = array(
		"index" => "",
		"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
		"detail_slide_show" => "PAGE_NAME=detail_slide_show".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		"section" => "PAGE_NAME=section".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#",
		"section_edit" => "PAGE_NAME=section_edit".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#",
		"section_edit_icon" => "PAGE_NAME=section_edit_icon".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#",
		"upload" => "PAGE_NAME=upload".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ACTION=upload");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[mb_strtoupper($URL)."_URL"] = trim($arParams[mb_strtoupper($URL)."_URL"]);
		if (empty($arParams[mb_strtoupper($URL)."_URL"]))
		{
			$arParams[mb_strtoupper($URL)."_URL"] = $GLOBALS["APPLICATION"]->GetCurPageParam($URL_VALUE,
				array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL", "sessid", "edit", "login", "USER_ALIAS", "order", "group_by"));
		}

		$arParams["~".mb_strtoupper($URL)."_URL"] = $arParams[mb_strtoupper($URL)."_URL"];
		$arParams[mb_strtoupper($URL)."_URL"] = htmlspecialcharsbx($arParams["~".mb_strtoupper($URL)."_URL"]);
	}
//***************** ADDITIONAL **************************************/
	$arParams["DATE_TIME_FORMAT"] = trim(!empty($arParams["DATE_TIME_FORMAT"]) ? $arParams["DATE_TIME_FORMAT"] :
		$GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("SHORT")));
	$arParams["ALBUM_PHOTO_SIZE"] = (intval($arParams["ALBUM_PHOTO_SIZE"]) > 0 ? intval($arParams["ALBUM_PHOTO_SIZE"]) : 150);
	$arParams["ALBUM_PHOTO_THUMBS_SIZE"] = (intval($arParams["ALBUM_PHOTO_THUMBS_SIZE"]) > 0 ? intval($arParams["ALBUM_PHOTO_THUMBS_SIZE"]) : 70);
	$arParams["GALLERY_SIZE"] = intval($arParams["GALLERY_SIZE"]);
	$arParams["RETURN_SECTION_INFO"] = ($arParams["RETURN_SECTION_INFO"] == "Y" ? "Y" : "N");
	$arParams["SET_STATUS_404"] = ($arParams["SET_STATUS_404"] == "Y" ? "Y" : "N");
//***************** STANDART ****************************************/
	if(!isset($arParams["CACHE_TIME"]))
		$arParams["CACHE_TIME"] = 3600;
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); //Turn off by default
/********************************************************************
				/Input params
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
/********************************************************************
				Main data
********************************************************************/
/************** SECTION ********************************************/
$res = $oPhoto->GetSection($arParams["SECTION_ID"], $arResult["SECTION"]);
if ($res > 400)
{
	return false;
}
elseif ($res == 301)
{
	$url = CComponentEngine::MakePathFromTemplate(
		$arParams["~SECTION_URL"],
		array(
			"USER_ALIAS" => $arGallery["CODE"],
			"SECTION_ID" => $arParams["SECTION_ID"])
		);

	if ($url == POST_FORM_ACTION_URI)
		$url = CComponentEngine::MakePathFromTemplate($arParams["~INDEX_URL"], array());
	LocalRedirect($url, false, "301 Moved Permanently");
	return false;
}
elseif (!$oPhoto->CheckPermission($arParams["PERMISSION"], $arResult["SECTION"]))
{
	if ($arParams["SET_TITLE"] == "Y")
		$GLOBALS["APPLICATION"]->SetTitle(GetMessage('P_SECTION_ACCESS_DENIED'));
	return false;
}
if (is_array($arResult["SECTION"]["~DATE"]))
	$arResult["SECTION"]["DATE"]["VALUE"] = PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arResult["SECTION"]["~DATE"]["VALUE"], CSite::GetDateFormat()));
/********************************************************************
				/Main data
********************************************************************/

/********************************************************************
				Prepare Data
********************************************************************/
$url = array();
if ($arParams["BEHAVIOUR"] == "USER" && $arResult["SECTION"]["IBLOCK_SECTION_ID"] == $arResult["GALLERY"]["ID"])
	$url["BACK_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"],
			array("USER_ALIAS" => $arResult["GALLERY"]["CODE"]));
elseif (intval($arResult["SECTION"]["IBLOCK_SECTION_ID"]) > 0)
	$url["BACK_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["IBLOCK_SECTION_ID"]));
else
	$url["BACK_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~INDEX_URL"], array());

$url["SLIDE_SHOW_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_SLIDE_SHOW_URL"], array(
		"USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ELEMENT_ID" => 0,
		"USER_ID" => $arResult["GALLERY"]["CREATED_BY"], "GROUP_ID" => $arResult["GALLERY"]["SOCNET_GROUP_ID"])).
		(mb_strpos($arParams["~DETAIL_SLIDE_SHOW_URL"], "?") === false ? "?" : "&")."BACK_URL=".urlencode($GLOBALS['APPLICATION']->GetCurPageParam());

if ($arParams["PERMISSION"] >= "W")
{
	$url["NEW_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ACTION" => "new"));
	$url["EDIT_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ACTION" => "edit"));
	if ($arResult["SECTION"]["ELEMENTS_CNT"] > 0)
		$url["EDIT_ICON_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_ICON_URL"],
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ACTION" => "edit"));

	$url["DROP_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ACTION" => "drop")).
		(mb_strpos($arParams["~SECTION_EDIT_URL"], "?") === false ? "?" : "&").bitrix_sessid_get()."&edit=Y";
	if ($arParams["BEHAVIOUR"] != "USER" || $arParams["GALLERY_SIZE"] <= 0 || $arParams["GALLERY_SIZE"] > $arResult["GALLERY"]["UF_GALLERY_SIZE"])
		$url["UPLOAD_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~UPLOAD_URL"],
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"]));
}

//echo $url["DROP_LINK"];

foreach ($url as $key => $val)
{
	$arResult["SECTION"]["~".$key] = $val;
	$arResult["SECTION"][$key] = htmlspecialcharsbx($val);
}
/********************************************************************
				/Data
********************************************************************/

/********************************************************************
				For custom templates
********************************************************************/
$arResult["SECTIONS_CNT"] = $arResult["SECTION"]["SECTIONS_CNT"];
/********************************************************************
				/For custom templates
********************************************************************/
CUtil::InitJSCore(array('window', 'ajax'));
$this->IncludeComponentTemplate();

/********************************************************************
				Standart
********************************************************************/
/************** Title **********************************************/
if ($arParams["SET_TITLE"] == "Y")
	$GLOBALS["APPLICATION"]->SetTitle($arResult["SECTION"]["NAME"]);
/************** BreadCrumb *****************************************/
if ($arParams["SET_NAV_CHAIN"] == "Y")
{
	$bFounded = ($arParams["BEHAVIOUR"] != "USER");
	foreach($arResult["SECTION"]["PATH"] as $arPath)
	{
		if (!$bFounded):
			$bFounded = ($arResult["GALLERY"]["ID"] == $arPath["ID"]);
			continue;
		endif;

		if ($arPath["ID"] != $arParams["SECTION_ID"])
			$GLOBALS["APPLICATION"]->AddChainItem($arPath["NAME"],
				CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
					array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arPath["ID"])));
		else
			$GLOBALS["APPLICATION"]->AddChainItem($arPath["NAME"]);
	}
}
/************** Admin Panel ****************************************/
// if ($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized() && CModule::IncludeModule("iblock")):
	// CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, $arParams["SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
// endif;
/************** Returns ********************************************/
if ($arParams["RETURN_SECTION_INFO"] == "Y"):
	return $arResult["SECTION"];
else:
	return $arResult["SECTION"]["ID"];
endif;
/********************************************************************
				/Standart
********************************************************************/
?>