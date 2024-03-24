<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var $APPLICATION \CMain */
/** @var array $arResult */
/** @var array $arParams */
/** @var \CBitrixComponent $component */

use Bitrix\Socialnetwork\Livefeed\Context\Context;

$groupId = $arResult['groupId'];
$state = $arResult['state'];
?>

<div class="sn-spaces__group-tasks">
<?php
	$APPLICATION->includeComponent(
		'bitrix:tasks.task.list',
		'',
		[
			'CONTEXT' => Context::SPACES,

			'KANBAN_SHOW_VIEW_MODE'=>'Y',
			'GROUP_ID' => $groupId,

			'STATE' => [
				'ROLES' => $state['ROLES'],
				'SELECTED_ROLES' => $state['ROLES'],
				'VIEWS' => $state['VIEWS'],
				'SELECTED_VIEWS' => $state['VIEWS'],
			],

			'TASKS_ALWAYS_EXPANDED' => 'Y',
			'LAZY_LOAD' => 'Y',
			'PATH_TO_GROUP_GENERAL' => $arParams['PATH_TO_GROUP_DISCUSSIONS'],
			'PATH_TO_GROUP_CALENDAR' => $arParams['PATH_TO_GROUP_CALENDAR'],
			'PATH_TO_GROUP_DISK' => $arParams['PATH_TO_GROUP_FILES'],
			'PATH_TO_USER' => $arParams['PATH_TO_USER'],

			'INCLUDE_INTERFACE_HEADER' => 'Y',
			'TIMELINE_MODE' => $arResult['isTimelineKanban'],
			'PERSONAL' => $arResult['isPersonalKanban'],
			'ITEMS_COUNT' => '50',

			'PATH_TO_USER_PROFILE' => $arParams['PATH_TO_USER'],
			'PATH_TO_MESSAGES_CHAT' => $arParams['PATH_TO_MESSAGES_CHAT'],
			'PATH_TO_CONPANY_DEPARTMENT' => $arParams['PATH_TO_COMPANY_DEPARTMENT'],
			'PATH_TO_VIDEO_CALL' => $arParams['PATH_TO_VIDEO_CALL'],
			'PATH_TO_USER_TASKS' => $arParams['PATH_TO_USER_TASKS'],
			'PATH_TO_USER_TASKS_TASK' => $arParams['PATH_TO_USER_TASKS_TASK'],
			'PATH_TO_USER_TASKS_VIEW' => $arParams['PATH_TO_USER_TASKS_VIEW'],
			'PATH_TO_USER_TASKS_REPORT' => $arParams['PATH_TO_USER_TASKS_REPORT'],
			'PATH_TO_USER_TASKS_TEMPLATES' => $arParams['PATH_TO_USER_TASKS_TEMPLATES'],
			'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
			'PATH_TO_GROUP_TASKS' => $arParams['PATH_TO_GROUP_TASKS'],
			'PATH_TO_GROUP_TASKS_TASK' => $arParams['PATH_TO_GROUP_TASKS_TASK'],
			'PATH_TO_GROUP_TASKS_VIEW' => $arParams['PATH_TO_GROUP_TASKS_VIEW'],
			'PATH_TO_GROUP_TASKS_REPORT' => $arParams['PATH_TO_GROUP_TASKS_REPORT'],
			'SET_NAV_CHAIN' => $arParams['SET_NAV_CHAIN'],
			'FORUM_ID' => $arParams['TASK_FORUM_ID'],
			'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
			'SHOW_LOGIN' => $arParams['SHOW_LOGIN'],
			'DATE_TIME_FORMAT' => $arParams['DATE_TIME_FORMAT'],
			'SHOW_YEAR' => $arParams['SHOW_YEAR'],
			'CACHE_TYPE' => $arParams['CACHE_TYPE'],
			'CACHE_TIME' => $arParams['CACHE_TIME'],
			'USE_THUMBNAIL_LIST' => 'N',
			'INLINE' => 'Y',
			'USE_PAGINATION' => 'Y',
			'HIDE_OWNER_IN_TITLE' => $arParams['HIDE_OWNER_IN_TITLE'],
			'PREORDER' => [
				'STATUS_COMPLETE' => 'asc'
			],
		]
	);
?>
</div>