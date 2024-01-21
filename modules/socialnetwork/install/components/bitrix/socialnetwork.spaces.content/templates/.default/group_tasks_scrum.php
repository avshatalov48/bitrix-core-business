<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var $APPLICATION \CMain */
/** @var array $arResult */
/** @var array $arParams */
/** @var \CBitrixComponent $component */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

$groupId = $arResult['groupId'];
$userId = $arResult['userId'];
?>

<div class="sn-spaces__group-tasks">
<?php
	$APPLICATION->includeComponent(
		'bitrix:tasks.scrum',
		'',
		[
			'CONTEXT' => 'spaces',

			'GROUP_ID' => $groupId,
			'USER_ID' => $userId,

			'PATH_TO_USER_TASKS' => $arParams['PATH_TO_USER_TASKS'],
			'PATH_TO_GROUP_TASKS' => $arParams['PATH_TO_GROUP_TASKS'],
			'PATH_TO_GROUP_TASKS_TASK' => $arParams['PATH_TO_GROUP_TASKS_TASK'],
			'PATH_TO_SCRUM_TEAM_SPEED' => $arParams['PATH_TO_SCRUM_TEAM_SPEED'],
			'PATH_TO_SCRUM_BURN_DOWN' => $arParams['PATH_TO_SCRUM_BURN_DOWN'],

			'SET_TITLE' => 'N',
		]
	);
?>
</div>

<style>
	.workarea-content-paddings, .sn-spaces__content, .tasks-scrum-kanban {
		overflow: unset;
	}

	.tasks-scrum-kanban-header {
		position: sticky;
	}

	.tasks-scrum-kanban-header-target-observer.--with-margin {
		margin: 0 !important;
	}

	.sn-spaces__content {
		position: relative;
	}

	.tasks-kanban__start {
		height: calc(100vh - 200px);
	}
</style>