<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$component = $this->getComponent();

if ($arParams["SHOW_EVENT_ID_FILTER"] == "Y")
{
	if ($arParams["IS_CRM"] == "Y")
	{
		$liveFeedFilter = new CCrmLiveFeedFilter(
			array(
				'GridFormID' => '',
				'EntityTypeID' => false
			)
		);
		AddEventHandler('socialnetwork', 'OnBeforeSonetLogFilterFill', array($liveFeedFilter, 'OnBeforeSonetLogFilterFill'));
	}

	$APPLICATION->IncludeComponent(
		"bitrix:socialnetwork.log.filter",
		(isset($arParams["FILTER_TEMPLATE"]) ? $arParams["FILTER_TEMPLATE"] : ".default"),
		[
			"arParams" => array_merge(
				$arParams,
				array(
					"TOP_OUT" => "Y",
					"USE_TARGET" => (!isset($arParams["USE_FILTER_TARGET"]) || $arParams["USE_FILTER_TARGET"] != "N" ? "Y" : "N"),
					"TARGET_ID" => (
					isset($_REQUEST["SONET_FILTER_MODE"])
					&& $_REQUEST["SONET_FILTER_MODE"] == "AJAX"
						? ""
						: "sonet_blog_form"
					),
					"USE_SONET_GROUPS" => (!isset($arParams["IS_CRM"]) || $arParams["IS_CRM"] != "Y" ? "Y" : "N"),
					"SHOW_FOLLOW" => (isset($arParams["SHOW_FOLLOW_FILTER"]) && $arParams["SHOW_FOLLOW_FILTER"] == "N" ? "N" : "Y"),
					"SHOW_EXPERT_MODE" => (isset($arParams["SHOW_EXPERT_MODE"]) && $arParams["SHOW_EXPERT_MODE"] == "N" ? "N" : "Y"),
					"EXPERT_MODE" => (isset($arResult["EXPERT_MODE"]) ? $arResult["EXPERT_MODE"] : "N"),
					"SET_EXPERT_MODE" => (isset($arResult["EXPERT_MODE_SET"]) && $arResult["EXPERT_MODE_SET"] === true ? "Y" : "N"),
					"USE_SMART_FILTER" => (isset($arResult["USE_SMART_FILTER"]) && $arResult["USE_SMART_FILTER"] == "Y" ? "Y" : "N"),
					"MY_GROUPS_ONLY" => (isset($arResult["MY_GROUPS_ONLY"]) && $arResult["MY_GROUPS_ONLY"] == "Y" ? "Y" : "N"),
					"FILTER_ID" => $arResult["FILTER_ID"]
				)
			),
			"arResult" => $arResult
		],
		null,
		[ "HIDE_ICONS" => "Y" ]
	);

	// deprecated
	if (isset($_REQUEST["SONET_FILTER_MODE"]) && $_REQUEST["SONET_FILTER_MODE"] == "AJAX")
	{
		return;
	}
}