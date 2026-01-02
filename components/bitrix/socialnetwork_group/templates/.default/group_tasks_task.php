<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
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
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;
use Bitrix\Socialnetwork\Internals\Registry\FeaturePermRegistry;
use Bitrix\Tasks\Slider\Factory\SliderFactory;
use Bitrix\Tasks\Slider\Path\TaskPathMaker;
use Bitrix\Tasks\V2\FormV2Feature;
use Bitrix\UI\Toolbar\Facade\Toolbar;

$userId = CurrentUser::get()->getId();
$pageId = "group_tasks";
$groupId = (int)$arResult['VARIABLES']['group_id'];
$taskId = (int)$arResult['VARIABLES']['task_id'];
$action =
	$arResult['VARIABLES']['action'] === 'edit'
		? 'edit'
		: 'view'
;

$usePadding = $action === 'edit' ?? false;
$formParams = [
	"ID" => $taskId,
	"GROUP_ID" => $groupId,
	"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER"],
	"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],
	"PATH_TO_GROUP_TASKS" => $arResult["PATH_TO_GROUP_TASKS"],
	"PATH_TO_GROUP_TASKS_TASK" => $arResult["PATH_TO_GROUP_TASKS_TASK"],
	"PATH_TO_USER_TASKS_TEMPLATES" => $arParams["PATH_TO_USER_TASKS_TEMPLATES"],
	"PATH_TO_USER_TEMPLATES_TEMPLATE" => $arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"],
	"SET_NAVCHAIN" => $arResult["SET_NAV_CHAIN"],
	"SET_TITLE" => $arResult["SET_TITLE"],
	"SHOW_RATING" => $arParams["SHOW_RATING"],
	"RATING_TYPE" => $arParams["RATING_TYPE"],
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
];
$formFeatureEnabled = Loader::includeModule('tasks')
	&& class_exists(FormV2Feature::class)
	&& FormV2Feature::isOn()
;
$isFormV2AllowedForGroup = Loader::includeModule('tasks')
	&& class_exists(FormV2Feature::class)
	&& in_array($groupId, FormV2Feature::getAllowedGroups(), true)
;
$request = Context::getCurrent()->getRequest();
$isOldForm = $request->get('OLD_FORM') === 'Y';
$hasTemplate = (int)$request->get('TEMPLATE') > 0 || (int)$request->get('FLOW_ID') > 0;
$isCommentLink = (bool)$request->get('MID');

$showPersonalTasks = false;
if (
	!FeaturePermRegistry::getInstance()->get(
		$groupId,
		'tasks',
		'view',
		$userId
	)
	|| !FeaturePermRegistry::getInstance()->get(
		$groupId,
		'tasks',
		'view_all',
		$userId
	)
)
{
	$showPersonalTasks = true;
}

if (Context::getCurrent()->getRequest()->get('IFRAME'))
{
	if (
		CSocNetFeatures::IsActiveFeature(
			SONET_ENTITY_GROUP,
			$groupId,
			'tasks'
		)
	)
	{
		$APPLICATION->IncludeComponent(
			'bitrix:ui.sidepanel.wrapper',
			'',
			[
				'POPUP_COMPONENT_NAME' => 'bitrix:tasks.iframe.popup',
				'POPUP_COMPONENT_TEMPLATE_NAME' => 'wrap',
				'POPUP_COMPONENT_PARAMS' => [
					"ACTION" => $action,
					"FORM_PARAMETERS" => $formParams,
					'HIDE_MENU_PANEL' => 'Y',
				],
				'USE_UI_TOOLBAR' => 'Y',
				'USE_PADDING' => $usePadding,
				'USE_FAST_WAY_CLOSE_LOADER' => $taskId > 0,
			],
			$component,
			["HIDE_ICONS" => "Y"]
		);
	}
}
else if (
	($formFeatureEnabled || $isFormV2AllowedForGroup)
	&& !$isOldForm
	&& !$hasTemplate
	&& !$isCommentLink
)
{
	$APPLICATION->SetPageProperty('BodyClass', 'no-all-paddings no-background');
	$APPLICATION->SetTitle('');
	Toolbar::deleteFavoriteStar();

	if (isset($showPersonalTasks) && $showPersonalTasks === true)
	{
		$context = SliderFactory::PERSONAL_CONTEXT;
		$ownerId = $userId;
	}
	else
	{
		$context = SliderFactory::GROUP_CONTEXT;
		$ownerId = $groupId;
	}

	$pathMaker = new TaskPathMaker(
		entityId: $taskId,
		ownerId: $ownerId,
		context: $context
	);

	$pathToList = $pathMaker->makeEntitiesListPath();
	$pathToTask = $pathMaker->makeEntityPath();

	Extension::load('tasks.v2.application.task-card');

	?>
	<script>
		top.BX.ready(async function()
		{
			const { TaskCard } = await top.BX.Runtime.loadExtension('tasks.v2.application.task-card');

			TaskCard.showFullCard({
				taskId: <?= $taskId ?>,
				groupId: <?= $groupId ?>,
				closeCompleteUrl: "<?= $pathToList ?>",
				url: "<?= $pathToTask ?>",
			});
		});
	</script>
	<?php
}
else
{
	$backgroundForTask = true;
	require_once('group_tasks_task_background.php');
}
