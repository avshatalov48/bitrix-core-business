<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>


<?
$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.messages_menu",
	"",
	Array(
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"PATH_TO_MESSAGES_INPUT" => $arResult["PATH_TO_MESSAGES_INPUT"],
		"PATH_TO_MESSAGES_OUTPUT" => $arResult["PATH_TO_MESSAGES_OUTPUT"],
		"PATH_TO_MESSAGES_USERS" => $arResult["PATH_TO_MESSAGES_USERS"],
		"PATH_TO_USER_BAN" => $arResult["PATH_TO_USER_BAN"],
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_LOG" => $arResult["PATH_TO_LOG"],
		"PATH_TO_SUBSCRIBE" => $arResult["PATH_TO_SUBSCRIBE"],
		"PATH_TO_TASKS" => $arResult["PATH_TO_TASKS"],
		"PAGE_ID" => "tasks"
	),
	$component
);
?>

<br />

<?
	$APPLICATION->IncludeComponent(
		"bitrix:intranet.tasks",
		".default",
		Array(
			"IBLOCK_ID" => 171,
			"TASK_TYPE" => 'personal',
			"ITEMS_COUNT" => 20, 
			"TASKS_FIELDS_SEARCHABLE" => array(
				"ID",
				"NAME",
				"MODIFIED_BY",
				"DATE_CREATE",
				"CREATED_BY",
				"DATE_ACTIVE_FROM",
				"DATE_ACTIVE_TO",
				"ACTIVE_DATE",
				"SECTION_ID",
			),
			"TASKS_PROPERTY_SEARCHABLE" => array(
				"TaskPriority",
				"TASKSTATUS",
				"TaskComplete",
				"TASKASSIGNEDTO",
				"TaskAlert",
				"TASKSIZE",
				"TaskSizeReal",
				"TaskFinish",
			),
			"TASKS_FIELDS_SHOW" => array(
				"ID",
				"NAME",
				"MODIFIED_BY",
				"DATE_CREATE",
				"CREATED_BY",
				"DATE_ACTIVE_FROM",
				"DATE_ACTIVE_TO",
				"SECTION_ID",
				"DETAIL_TEXT",
			),
			"TASKS_PROPERTY_SHOW" => array(
				"TASKPRIORITY",
				"TaskStatus",
				"TASKCOMPLETE",
				"TaskAssignedTo",
				"TaskAlert",
				"TaskSize",
				"TASKSIZEREAL",
				"TaskFinish",
			),
		)
	);
?>