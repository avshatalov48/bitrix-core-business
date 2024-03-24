<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponent $component */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Tasks\Slider\Factory\SliderFactory;

$groupId = (int)$arResult['VARIABLES']['group_id'];
$taskId = (int)$arResult['VARIABLES']['task_id'];
$action = $arResult['VARIABLES']['action'] === 'edit' ? 'edit' : 'view';

$formParams = [
	'ID' => $taskId,
	'GROUP_ID' => $groupId,
	'PATH_TO_USER_PROFILE' => $arResult['PATH_TO_USER'],
	'PATH_TO_GROUP' => $arResult['PATH_TO_GROUP'],
	'PATH_TO_GROUP_TASKS' => $arResult['PATH_TO_GROUP_TASKS'],
	'PATH_TO_GROUP_TASKS_TASK' => $arResult['PATH_TO_GROUP_TASKS_TASK'],
	'PATH_TO_USER_TASKS_TEMPLATES' => $arResult['PATH_TO_USER_TASKS_TEMPLATES'],
	'PATH_TO_USER_TEMPLATES_TEMPLATE' => $arResult['PATH_TO_USER_TEMPLATES_TEMPLATE'],
	"SET_NAV_CHAIN"	=>	"Y",
	'SET_NAVCHAIN' => 'Y',
	'SET_TITLE' => 'Y',
	'SHOW_RATING' => 'Y',
	'RATING_TYPE' => 'like',
	'SHOW_YEAR' => 'Y',
	'DATE_TIME_FORMAT' => $arResult['DATE_TIME_FORMAT'],
	'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE'],
];

if (!Loader::includeModule('tasks'))
{
	return;
}

if (Context::getCurrent()->getRequest()->get('IFRAME'))
{
	$APPLICATION->IncludeComponent(
		'bitrix:tasks.iframe.popup',
		'wrap',
		[
			'ACTION' => $action,
			'FORM_PARAMETERS' => $formParams,
			'HIDE_MENU_PANEL' => 'Y',
		],
		$component,
		['HIDE_ICONS' => 'Y'],
	);
}
else
{
	$queryList = Context::getCurrent()->getRequest()->getQueryList()->toArray();
	$getParams = empty($queryList) ? '' : '?' . http_build_query($queryList);

	$factory = new SliderFactory();
	$factory->setAction($action)->setQueryParams($getParams);

	$slider = $factory->createEntitySlider(
		$taskId,
		SliderFactory::TASK,
		$groupId,
		SliderFactory::SPACE_CONTEXT,
	);

	$slider->open();
}
