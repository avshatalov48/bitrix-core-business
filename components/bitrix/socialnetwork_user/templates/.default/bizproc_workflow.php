<?php

/** @var CMain $APPLICATION */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$idParam = $arResult["VARIABLES"]["task_id"] ?? null;
$taskId = null;
$workflowId = null;

if ((string)(int)$idParam === (string)$idParam)
{
	$taskId = (int)$idParam;
}
else
{
	$workflowId = $idParam;
}


$cmpParams = [
	'WORKFLOW_ID' => $workflowId,
	'TASK_ID' => $taskId,
	'USER_ID' => $_GET['USER_ID'] ?? null,
	'SET_TITLE' => $arParams["SET_TITLE"] ?? 'Y',
];

if (($_REQUEST['IFRAME'] ?? '') === 'Y' && ($_REQUEST['IFRAME_TYPE'] ?? '') === 'SIDE_SLIDER')
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		array(
			'POPUP_COMPONENT_NAME' => 'bitrix:bizproc.workflow.info',
			'POPUP_COMPONENT_TEMPLATE_NAME' => 'slider',
			'POPUP_COMPONENT_PARAMS' => $cmpParams,
			'PLAIN_VIEW' => true,
			'USE_PADDING' => false,
		)
	);
}
else
{
	$pageId = "";
	include("util_menu.php");

	$APPLICATION->IncludeComponent('bitrix:bizproc.workflow.info', 'page-slider', $cmpParams);
}
