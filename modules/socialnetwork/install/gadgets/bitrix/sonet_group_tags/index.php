<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("socialnetwork"))
	return false;

if (intval($arGadgetParams["GROUP_ID"]) > 0)
{
	$arActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_GROUP, $arGadgetParams["GROUP_ID"]);
	$bSearch = (array_key_exists("search", $arActiveFeatures));
}

if ($bSearch)
{
	?>
	<?$GLOBALS["APPLICATION"]->IncludeComponent(
		"bitrix:search.tags.cloud",
		"gadget",
		Array(
			"PAGE_ELEMENTS" => $arGadgetParams["PAGE_ELEMENTS"],
			"PERIOD" => $arGadgetParams["PERIOD"],
			"URL_SEARCH" => CComponentEngine::MakePathFromTemplate($arGadgetParams["PATH_TO_GROUP_CONTENT_SEARCH"], array("group_id" => $arGadgetParams["GROUP_ID"])),
			"FONT_MAX" => $arGadgetParams["FONT_MAX"],
			"FONT_MIN" => $arGadgetParams["FONT_MIN"],
			"COLOR_NEW" => $arGadgetParams["COLOR_NEW"],
			"COLOR_OLD" => $arParams["COLOR_OLD"],
			"WIDTH" => "100%",
			"SORT" => "NAME",
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"arrFILTER" => array(
				0 => "socialnetwork",
			),
			"arrFILTER_socialnetwork" => array(
				0 => $arGadgetParams["GROUP_ID"],	
			),
		),
		false,
		array("HIDE_ICONS" => "Y")							
	);
	?>
	<?
}
else
	echo GetMessage('GD_SONET_GROUP_TAGS_FEATURE_INACTIVE');
?>