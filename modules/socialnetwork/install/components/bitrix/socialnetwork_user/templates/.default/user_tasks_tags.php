<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var CBitrixComponentTemplate $this */
/** @var CBitrixComponent $component */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Tasks\Internals\Routes\RouteDictionary;

if (!Loader::includeModule('tasks'))
{
	return;
}

if (
	Context::getCurrent()->getRequest()->get('IFRAME') === 'Y'
)
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:tasks.tag.list',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => [
				'PATH_TO_USER_TASKS_TASK' => RouteDictionary::PATH_TO_USER_TASK,
				'PATH_TO_USER' => $arResult['PATH_TO_USER'],
			],
			'USE_UI_TOOLBAR' => 'Y',
		],
		$component
	);
}
else
{
	$tagSliderParams = [
		'TAGS_SLIDER' => RouteDictionary::PATH_TO_USER_TAGS,
		'TAGS_SLIDER_PATH_TO_USER_TASKS_TASK' => RouteDictionary::PATH_TO_USER_TASK,
		'TAGS_SLIDER_PATH_TO_USER' => $arResult['PATH_TO_USER'],
		'TAGS_SLIDER_GROUP_ID' => Context::getCurrent()->getRequest()->get('GROUP_ID') ?? 0,
	];
	require_once('user_tasks.php');
}