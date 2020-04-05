<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("socialnetwork"))
	return false;
if (intval($arGadgetParams["USER_ID"]) > 0)
{
	$arActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_USER, $arGadgetParams["USER_ID"]);
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
			"URL_SEARCH" => CComponentEngine::MakePathFromTemplate($arGadgetParams["PATH_TO_USER_CONTENT_SEARCH"], array("user_id" => $arGadgetParams["USER_ID"])),
			"FONT_MAX" => $arGadgetParams["FONT_MAX"],
			"FONT_MIN" => $arGadgetParams["FONT_MIN"],
			"COLOR_NEW" => $arGadgetParams["COLOR_NEW"],
			"COLOR_OLD" => $arParams["COLOR_OLD"],
			"WIDTH" => "100%",
			"SORT" => "NAME",
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"arrFILTER" => array(
				0 => "socialnetwork_user",
			),
			"arrFILTER_socialnetwork_user" => array(
				0 => $arGadgetParams["USER_ID"],	
			),
		),
		false,
		array("HIDE_ICONS" => "Y")							
	);
	?>
	<?
}
else
	echo GetMessage('GD_SONET_USER_TAGS_FEATURE_INACTIVE');
?>