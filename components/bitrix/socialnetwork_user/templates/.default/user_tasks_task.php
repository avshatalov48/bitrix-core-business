<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponent $component */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Tasks\Slider\Path\PathMaker;
use Bitrix\Tasks\Slider\Path\TaskPathMaker;
use Bitrix\Tasks\V2\FormV2Feature;
use Bitrix\UI\Toolbar\Facade\Toolbar;

$pageId = "user_tasks";
$taskId = (int)$arResult['VARIABLES']['task_id'];
$userId = (int)$arResult['VARIABLES']['user_id'];
$action =
	$arResult['VARIABLES']['action'] === 'edit'
		? 'edit'
		: 'view'
;
$usePadding = $action === 'edit' ?? false;
$formFeatureEnabled = Loader::includeModule('tasks')
	&& class_exists(FormV2Feature::class)
	&& FormV2Feature::isOn()
;
$request = Context::getCurrent()->getRequest();
$isOldForm = $request->get('OLD_FORM') === 'Y';
$hasTemplate = (int)$request->get('TEMPLATE') > 0 || (int)$request->get('FLOW_ID') > 0;
$isCommentLink = (bool)$request->get('MID');

if (Context::getCurrent()->getRequest()->get('IFRAME'))
{
	include("util_menu.php");
	include("util_profile.php");

	Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].$this->getFolder().'/result_modifier.php');

	if (
		!CSocNetFeatures::IsActiveFeature(
			SONET_ENTITY_USER,
			$userId,
			"tasks"
		)
	)
	{
		$pathToUserFeatures = str_replace(["#user_id#", "#USER_ID#"], $userId, $arResult['PATH_TO_USER_FEATURES']);
		$pathToUserFeaturesHref = '<a href="' . $pathToUserFeatures . '">';

		echo Loc::getMessage('SU_T_TASKS_UNAVAILABLE', [
			'#A_BEGIN#' => $pathToUserFeaturesHref,
			'#A_END#' => '</a>',
		]);
	}
	else if (Loader::includeModule('tasks'))
	{
		$APPLICATION->IncludeComponent(
			'bitrix:ui.sidepanel.wrapper',
			'',
			[
				'POPUP_COMPONENT_NAME' => 'bitrix:tasks.iframe.popup',
				'POPUP_COMPONENT_TEMPLATE_NAME' => 'wrap',
				'POPUP_COMPONENT_PARAMS' => [
					"ACTION" => $action,
					"FORM_PARAMETERS" => [
						"ID" => $taskId,
						"GROUP_ID" => "",
						"USER_ID" => $userId,
						"PATH_TO_USER_TASKS" => $arResult["PATH_TO_USER_TASKS"],
						"PATH_TO_USER_TASKS_TASK" => $arResult["PATH_TO_USER_TASKS_TASK"],
						"PATH_TO_GROUP_TASKS" => $arParams["PATH_TO_GROUP_TASKS"],
						"PATH_TO_GROUP_TASKS_TASK" => "",
						"PATH_TO_USER_PROFILE" => $arResult["PATH_TO_USER"],
						"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
						"PATH_TO_USER_TASKS_PROJECTS_OVERVIEW" => $arResult["PATH_TO_USER_TASKS_PROJECTS_OVERVIEW"],
						"PATH_TO_USER_TASKS_TEMPLATES" => $arResult["PATH_TO_USER_TASKS_TEMPLATES"],
						"PATH_TO_USER_TEMPLATES_TEMPLATE" => $arResult["PATH_TO_USER_TEMPLATES_TEMPLATE"],
						"SET_NAVCHAIN" => $arResult["SET_NAV_CHAIN"],
						"SET_TITLE" => $arResult["SET_TITLE"],
						"SHOW_RATING" => $arParams["SHOW_RATING"],
						"RATING_TYPE" => $arParams["RATING_TYPE"],
						"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
					],
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
	$formFeatureEnabled
	&& !$isOldForm
	&& !$hasTemplate
	&& !$isCommentLink
)
{
	$APPLICATION->SetPageProperty('BodyClass', 'no-all-paddings no-background');
	$APPLICATION->SetTitle('');
	Toolbar::deleteFavoriteStar();

	$pathMaker = new TaskPathMaker(
		entityId: $taskId,
		ownerId: $userId,
		context: PathMaker::PERSONAL_CONTEXT
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
				closeCompleteUrl: "<?= $pathToList ?>",
				url: "<?= $pathToTask ?>",
			});
		});
	</script>
	<?php
}
else
{
	$userId = CurrentUser::get()->getId();
	$backgroundForTask = true;
	require_once('user_tasks_task_background.php');
}
