<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("socialnetwork"))
	return false;
	
if(!CModule::IncludeModule("wiki"))
	return false;

if (intval($arParams["SOCNET_GROUP_ID"]) > 0)
{
	$arActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"]);
	$bWiki = (array_key_exists("wiki", $arActiveFeatures));
}

if (!is_array($arGadgetParams) || !array_key_exists("TEXT_LIMIT", $arGadgetParams) || $arGadgetParams["TEXT_LIMIT"] <= 0)
	$arGadgetParams["TEXT_LIMIT"] = 500;

if ($bWiki)
{
	?>
	<?$APPLICATION->IncludeComponent(
		"bitrix:wiki.show",
		"gadget",
		Array(
			"PATH_TO_POST" => CComponentEngine::MakePathFromTemplate($arParams["PARENT_COMPONENT_RESULT"]["PATH_TO_GROUP_WIKI_POST"], array("group_id" => $arParams["SOCNET_GROUP_ID"])),		
			"PATH_TO_POST_EDIT" => CComponentEngine::MakePathFromTemplate($arParams["PARENT_COMPONENT_RESULT"]["PATH_TO_GROUP_WIKI_POST_EDIT"], array("group_id" => $arParams["SOCNET_GROUP_ID"])),
			"PATH_TO_CATEGORIES" => CComponentEngine::MakePathFromTemplate($arParams["PARENT_COMPONENT_RESULT"]["PATH_TO_GROUP_WIKI_CATEGORIES"], array("group_id" => $arParams["SOCNET_GROUP_ID"])),
			"PAGE_VAR" => 'title',
			"OPER_VAR" => 'oper',
			"IBLOCK_TYPE" => COption::GetOptionString("wiki", "socnet_iblock_type_id"),
			"IBLOCK_ID" => COption::GetOptionString("wiki", "socnet_iblock_id"),
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"SOCNET_GROUP_ID" => $arParams["SOCNET_GROUP_ID"],
			"SET_TITLE" => "N",
			"TEXT_LIMIT" => $arGadgetParams["TEXT_LIMIT"]
		),
		false,
		array("HIDE_ICONS" => "Y")							
	);?>
	<?
}
else
	echo GetMessage('GD_SONET_GROUP_WIKI_FEATURE_INACTIVE');
?>