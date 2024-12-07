<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var $component */

$pageId = 'user_tasks_flow_create';
$userId = (int) $arResult['VARIABLES']['user_id'];

$url = str_replace(
	['#user_id#', '#USER_ID#'],
	$userId,
	$arResult['PATH_TO_USER_TASKS_FLOW']
);

$uri = new \Bitrix\Main\Web\Uri($url);

$uri->addParams(['create_flow' => 'Y']);

LocalRedirect($uri->getUri());
