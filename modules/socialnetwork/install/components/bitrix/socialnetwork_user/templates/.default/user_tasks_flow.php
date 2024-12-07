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

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

$pageId = 'user_tasks_flow';

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'] . $this->getFolder() . '/result_modifier.php');

$userId = $arResult['VARIABLES']['user_id'];

if (
	!Loader::includeModule('tasks')
	|| !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $userId, 'tasks')
)
{
	echo Loc::getMessage(
		'SU_T_TASKS_UNAVAILABLE',
		[
			'#A_BEGIN#' =>
				'<a href="'
				. str_replace(['#user_id#', '#USER_ID#'], $userId, $arResult['PATH_TO_USER_FEATURES'])
				. '">'
			,
			'#A_END#' => '</a>',
		]
	);

	return;
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$APPLICATION->includeComponent(
	'bitrix:tasks.interface.topmenu',
	'',
	[
		'USER_ID' => $userId,
		'SECTION_URL_PREFIX' => '',

		'MARK_SECTION_FLOW_LIST' => 'Y',
		'USE_AJAX_ROLE_FILTER' => 'N',

		'PATH_TO_GROUP_TASKS' => $arParams['PATH_TO_GROUP_TASKS'],

		'PATH_TO_USER_TASKS' => $arResult['PATH_TO_USER_TASKS'],
		'PATH_TO_USER_TASKS_TASK' => $arResult['PATH_TO_USER_TASKS_TASK'],
		'PATH_TO_USER_TASKS_VIEW' => $arResult['PATH_TO_USER_TASKS_VIEW'],
		'PATH_TO_USER_TASKS_REPORT' => $arResult['PATH_TO_USER_TASKS_REPORT'],
		'PATH_TO_USER_TASKS_TEMPLATES' => $arResult['PATH_TO_USER_TASKS_TEMPLATES'],

		'SCOPE' => Bitrix\Tasks\UI\ScopeDictionary::SCOPE_TASKS_FLOW,
	],
	$component,
	[ 'HIDE_ICONS' => true ]
);

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:tasks.flow.list',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'SET_TITLE' => 'Y',
			'PATH_TO_GROUP_TASKS' => $arParams['PATH_TO_GROUP_TASKS'],
			'PATH_TO_USER_TASKS_TASK' => $arResult['PATH_TO_USER_TASKS_TASK'],
			'PATH_TO_USER_TASKS' => $arResult['PATH_TO_USER_TASKS'],
		],
		'USE_UI_TOOLBAR' => 'Y',
		'POPUP_COMPONENT_PARENT'=> $component
	]
);
