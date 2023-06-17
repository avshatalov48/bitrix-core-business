<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var boolean $is_unread */
/** @var string $ind */
/** @var array $arEvent */

$component = $this->getComponent();

$arComponentParams = array_merge($arParams, [
	"LOG_ID" => $arEvent["ID"],
	"LAST_LOG_TS" => (
		$arParams["SET_LOG_COUNTER"] === "Y"
		&& !(isset($arResult["EXPERT_MODE_SET"]) && $arResult["EXPERT_MODE_SET"])
			? $arResult["LAST_LOG_TS"] :
			0
	),
	"COUNTER_TYPE" => $arResult["COUNTER_TYPE"],
	"AJAX_CALL" => $arResult["AJAX_CALL"],
	"bReload" => $arResult["bReload"],
	"bGetComments" => $arResult["bGetComments"],
	"IND" => $ind,
	"EVENT" => [
		"IS_UNREAD" => $is_unread
	],
	"LAZYLOAD" => "Y",
	"FROM_LOG" => (isset($arParams["LOG_ID"]) && (int)$arParams["LOG_ID"] > 0 ? "N" : "Y"),
	"PATH_TO_LOG_TAG" => $arResult["PATH_TO_LOG_TAG"],
	'TOP_RATING_DATA' => ($arResult['TOP_RATING_DATA'][$arEvent["ID"]] ?? false),
	'FORM_ID' => $arParams['FORM_ID'],
	'FORUM_ID' => $arParams['FORUM_ID'] ?? null,
	'TASK_RESULT_TASK_ID' => (
		($arResult['RESULT_TASKS_DATA'][(int)$arEvent['ID']] ?? 0)
			? (int)$arResult['RESULT_TASKS_DATA'][(int)$arEvent['ID']]
			: 0
	),
]);

if (isset($arResult['UNREAD_COMMENTS_ID_LIST'][$arEvent['ID']]))
{
	$arComponentParams['UNREAD_COMMENTS_ID_LIST'] = $arResult['UNREAD_COMMENTS_ID_LIST'][$arEvent['ID']];
}
elseif ($arResult['LOG_COUNTER'] <= 0)
{
	$arComponentParams['UNREAD_COMMENTS_ID_LIST'] = [];
}

if ($USER->isAuthorized())
{
	$arComponentParams['EVENT']['PINNED'] = (
		array_key_exists('PINNED_USER_ID', $arEvent)
		&& (int)$arEvent['PINNED_USER_ID'] > 0
			? 'Y'
			: 'N'
	);

	if ($arResult["SHOW_FOLLOW_CONTROL"] === "Y")
	{
		$arComponentParams["USE_FOLLOW"] = "Y";
		$arComponentParams["EVENT"]["FOLLOW"] = $arEvent["FOLLOW"];
		$arComponentParams["EVENT"]["DATE_FOLLOW"] = $arEvent["DATE_FOLLOW"];
	}

	if (
		!isset($arParams["USE_FAVORITES"])
		|| $arParams["USE_FAVORITES"] !== "N"
	)
	{
		$arComponentParams["EVENT"]["FAVORITES"] = (
			array_key_exists("FAVORITES_USER_ID", $arEvent)
			&& intval($arEvent["FAVORITES_USER_ID"]) > 0
				? "Y"
				: "N"
		);
	}
}

if (!empty($arEvent['CONTENT_ID']))
{
	$arComponentParams['CONTENT_ID'] = $arEvent['CONTENT_ID'];

	if (!empty($arResult["ContentViewData"][$arEvent['CONTENT_ID']]))
	{
		$arComponentParams['CONTENT_VIEW_CNT'] = $arResult["ContentViewData"][$arEvent['CONTENT_ID']]['CNT'];
	}
}

if (!empty($_REQUEST["commentId"]))
{
	$arComponentParams["COMMENT_ID"] = intval($_REQUEST["commentId"]);
}

$arComponentParams['PINNED_PANEL_DATA'] = (
	array_key_exists('PINNED_PANEL_DATA', $arEvent)
		? $arEvent['PINNED_PANEL_DATA']
		: []
);

$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.log.entry",
	"",
	$arComponentParams,
	$component
);
