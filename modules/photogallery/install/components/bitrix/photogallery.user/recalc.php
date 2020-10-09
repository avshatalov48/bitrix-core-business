<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("iblock")):
	return 0;
elseif (empty($arResult["GALLERY"])):
	return 0;
elseif ($arParams["PERMISSION"] < "W" && intval($arResult["GALLERY"]["CREATED_BY"]) != intval($GLOBALS["USER"]->GetId())):
	return 0;
elseif ($arResult["GALLERY"]["ELEMENTS_CNT"] > 0):
	return 0;
endif;

$arResult["ROW_COUNT"] = 20;
$arElements = array();
$arFile = array(
	"ID" => array(),
	"SIZE" => 0,
	"COUNT" => 0);
$bAjaxCall = ($_REQUEST["AJAX_CALL"] == "Y" ? "Y" : "N");
$sSatus = ($_REQUEST["status"] == "continue" ? "CONTINUE" : "BEGIN");
$arInfoRecalc = @unserialize($arResult["GALLERY"]["~UF_GALLERY_RECALC"]);
	
if (empty($arInfoRecalc) || !is_array($arInfoRecalc) || $arInfoRecalc["STATUS"] == "DONE" || $sSatus != "CONTINUE")
{
	$arInfoRecalc = array(
		"STEP" => 0,
		"LAST_ELEMENT_ID" => 0,
		"FILE_SIZE" => 0,
		"FILE_COUNT" => 0,
		"STATUS" => "BEGIN");
}
	
$arFilter = array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"ACTIVE_DATE" => "Y",
	"ACTIVE" => "Y",
	"SUBSECTION" => array(array($arResult["GALLERY"]["LEFT_MARGIN"], $arResult["GALLERY"]["RIGHT_MARGIN"])),
	">ID" => $arInfoRecalc["LAST_ELEMENT_ID"]);

	$db_res = CIBlockElement::GetList(array("ID" => "ASC"), $arFilter, false, array("nTopCount" => $arResult["ROW_COUNT"]), array("PROPERTY_REAL_PICTURE"));
	while ($res = $db_res->GetNextElement())
	{
		$arElement = $res->GetFields();
		$arElements[] = $arElement;
		if (intval($arElement["PROPERTY_REAL_PICTURE_VALUE"]) > 0)
			$arFile["ID"][] = $arElement["PROPERTY_REAL_PICTURE_VALUE"];
		$arInfoRecalc["LAST_ELEMENT_ID"] = $arElement["ID"];
	}
	if (!empty($arFile["ID"]))
	{
		$db_res = CFile::GetList(array(), array("@ID" => implode(",", $arFile["ID"])));
		while ($res = $db_res->Fetch()) 
		{
			$arFile["SIZE"] += doubleVal($res["FILE_SIZE"]);
			$arFile["COUNT"]++;
		}
	}
	
	
	$arInfoRecalc["STEP"]++;
	$arInfoRecalc["LAST_ELEMENT_ID"] = $arInfoRecalc["LAST_ELEMENT_ID"];
	$arInfoRecalc["FILE_SIZE"] = doubleVal($arInfoRecalc["FILE_SIZE"]) + doubleVal($arFile["SIZE"]);
	$arInfoRecalc["FILE_COUNT"] = doubleVal($arInfoRecalc["FILE_COUNT"]) + doubleVal($arFile["COUNT"]);
	$arInfoRecalc["STATUS"] = (count($arFile["ID"]) < $arResult["ROW_COUNT"] ? "DONE" : "CONTINUE");
	
	if ($arInfoRecalc["STATUS"] == "DONE")
	{
		$arFields = array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID"], 
			"UF_GALLERY_RECALC" => serialize($arInfoRecalc),
			"UF_GALLERY_SIZE" => $arInfoRecalc["FILE_SIZE"]);

		$GLOBALS["UF_GALLERY_RECALC"] = $arFields["UF_GALLERY_RECALC"];
		$GLOBALS["UF_GALLERY_SIZE"] = $arFields["UF_GALLERY_SIZE"];

		$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
		$bs = new CIBlockSection;
		$bs->Update($arResult["GALLERY"]["ID"], $arFields);
		BXClearCache(true, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/user/data/".$arResult["GALLERY"]["CREATED_BY"]."/");
		BXClearCache(true, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/gallery/".$arResult["GALLERY"]["~CODE"]."/");

		$url = $GLOBALS['APPLICATION']->GetCurPageParam("", array("action", "status"));
		
		if ($bAjaxCall != "Y")
		{
			LocalRedirect($url);
		}
		elseif ($arParams["GALLERY_SIZE"] > 0)
		{
			$GLOBALS['APPLICATION']->RestartBuffer();
			$arFields = array(
				"STATUS" => "DONE",
				"PERCENT" => intval(doubleVal($arInfoRecalc["FILE_SIZE"])/$arParams["GALLERY_SIZE"]*100));
			?><?=CUtil::PhpToJSObject($arFields);?><?
			die();
		}
	}
	else 
	{
		$arFields = array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID"], 
			"UF_GALLERY_RECALC" => serialize($arInfoRecalc),
			"UF_GALLERY_SIZE" => doubleVal($arResult["GALLERY"]["UF_GALLERY_SIZE"]));

		$GLOBALS["UF_GALLERY_RECALC"] = $arFields["UF_GALLERY_RECALC"];
		$GLOBALS["UF_GALLERY_SIZE"] = $arFields["UF_GALLERY_SIZE"];

		$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
		$bs = new CIBlockSection;
		$bs->Update($arResult["GALLERY"]["ID"], $arFields);
		BXClearCache(true, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/user/data/".$arResult["GALLERY"]["CREATED_BY"]."/");
		BXClearCache(true, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/gallery/".$arResult["GALLERY"]["~CODE"]."/");
		$arResult["GALLERY"]["~UF_GALLERY_RECALC"] = $arFields["UF_GALLERY_RECALC"];
		$arResult["GALLERY"]["UF_GALLERY_RECALC"] = htmlspecialcharsEx($arFields["UF_GALLERY_RECALC"]);
		
		if ($bAjaxCall == "Y")
		{
			$GLOBALS['APPLICATION']->RestartBuffer();
			$arFields = array(
				"STATUS" => "CONTINUE",
				"PERCENT" => intval(intVal($arInfoRecalc["FILE_COUNT"])/$arResult["GALLERY"]["ELEMENTS_CNT"]*100));
			?><?=CUtil::PhpToJSObject($arFields);?><?
			die();
		}
	}
?>