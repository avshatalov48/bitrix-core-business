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
?>

<div class="sn-spaces__group-calendar">
<?php
	$APPLICATION->includeComponent(
		'bitrix:calendar.grid',
		'',
		[
			'CONTEXT' => Context::SPACES,
			'CALENDAR_TYPE' => 'group',
			'GROUP_ID' => $groupId,
			'OWNER_ID' => $groupId,
			'ALLOW_SUPERPOSE' => $arParams['CALENDAR_ALLOW_SUPERPOSE'],
			'ALLOW_RES_MEETING' => $arParams['CALENDAR_ALLOW_RES_MEETING'],
			'SET_TITLE' => 'Y',
			'SET_NAV_CHAIN' => 'Y',
			'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
			'PATH_TO_USER' => $arParams['PATH_TO_USER'],
			'PATH_TO_COMPANY_DEPARTMENT' => $arParams['PATH_TO_COMPANY_DEPARTMENT'],
			'HIDE_OWNER_IN_TITLE' => $arParams['HIDE_OWNER_IN_TITLE'],
			'PATH_TO_USER_TASK' => $arParams['PATH_TO_USER_TASKS_TASK'],
			'PATH_TO_GROUP_TASK' => $arParams['PATH_TO_GROUP_TASKS_TASK'],
			'PATH_TO_GROUP_GENERAL' => $arParams['PATH_TO_GROUP_DISCUSSIONS'],
			'PATH_TO_GROUP_CALENDAR' => $arParams['PATH_TO_GROUP_CALENDAR'],
			'PATH_TO_GROUP_DISK' => $arParams['PATH_TO_GROUP_FILES'],
			'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
			'PATH_TO_GROUP_TASKS' => $arParams['PATH_TO_GROUP_TASKS'],
		]
	);
?>
</div>
