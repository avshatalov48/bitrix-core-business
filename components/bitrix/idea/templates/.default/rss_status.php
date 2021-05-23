<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arStatusesByCode = array();
$arStatuses = CIdeaManagment::getInstance()->Idea()->GetStatusList();
foreach($arStatuses as $arStatus)
	$arStatusesByCode[$arStatus["XML_ID"]] = $arStatus;
?>
<?$APPLICATION->IncludeComponent(
		"bitrix:idea.rss",
		"",
		Array(
			"RSS_TYPE" => $arResult["VARIABLES"]["type"],
			"IDEA_URL" => $arParams["BLOG_URL"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"FILTER" => array(
				CIdeaManagment::UFStatusField => $arStatusesByCode[ToUpper($arResult["VARIABLES"]["status_code"])]["ID"],
			),
			"PATH_TO_POST" => $arResult["PATH_TO_POST"],
			"IMAGE_MAX_WIDTH"			=> $arParams["IMAGE_MAX_WIDTH"],
			"IMAGE_MAX_HEIGHT"			=> $arParams["IMAGE_MAX_HEIGHT"],
			"USER"			=> $arResult["PATH_TO_USER"],
			"INDEX"			=> $arResult["PATH_TO_INDEX"],
			"RSS_CNT" => 10,
			"CUSTOM_TITLE" => GetMessage("RSS_TITLE", array("#IDEA_STATUS#" => $arStatusesByCode[ToUpper($arResult["VARIABLES"]["status_code"])]["VALUE"])),
			"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
		),
		$component
);
?>