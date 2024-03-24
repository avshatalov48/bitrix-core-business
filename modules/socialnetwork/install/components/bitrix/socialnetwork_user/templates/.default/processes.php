<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (
	\Bitrix\Main\Loader::includeModule('bizproc')
	&& class_exists(\Bitrix\Bizproc\Integration\Intranet\ToolsManager::class)
	&& !\Bitrix\Bizproc\Integration\Intranet\ToolsManager::getInstance()->isBizprocAvailable()
)
{
	echo \Bitrix\Bizproc\Integration\Intranet\ToolsManager::getInstance()->getBizprocUnavailableContent();
}
else
{
	$pageId = "";
	include("util_menu.php");

	$APPLICATION->IncludeComponent(
		"bitrix:lists.user.processes",
		"",
		Array(
			"USER_ID" => $arResult["VARIABLES"]["user_id"],
			"TASK_EDIT_URL" => str_replace("#task_id#", "#ID#", $arResult["PATH_TO_BIZPROC_EDIT"]),
			"PATH_TO_PROCESSES" => $arResult["PATH_TO_PROCESSES"],
			"PATH_TO_LIST_ELEMENT" => $arResult["PATH_TO_LIST_ELEMENT_EDIT"],
			"SET_TITLE" => $arParams["SET_TITLE"]
		),
		$component, array("HIDE_ICONS" => "Y")
	);
}
