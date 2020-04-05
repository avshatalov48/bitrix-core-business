<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"CALENDAR_DENIED" => array(
		"title" => Loc::getMessage("TASK_NAME_CALENDAR_DENIED"),
	),
	"CALENDAR_VIEW_TIME" => array(
		"title" => Loc::getMessage("TASK_NAME_CALENDAR_VIEW_TIME"),
	),
	"CALENDAR_VIEW_TITLE" => array(
		"title" => Loc::getMessage("TASK_NAME_CALENDAR_VIEW_TITLE"),
	),
	"CALENDAR_VIEW" => array(
		"title" => Loc::getMessage("TASK_NAME_CALENDAR_VIEW"),
	),
	"CALENDAR_EDIT" => array(
		"title" => Loc::getMessage("TASK_NAME_CALENDAR_EDIT"),
	),
	"CALENDAR_ACCESS" => array(
		"title" => Loc::getMessage("TASK_NAME_CALENDAR_ACCESS"),
	),

	"CALENDAR_TYPE_DENIED" => array(
		"title" => Loc::getMessage("TASK_NAME_CALENDAR_TYPE_DENIED"),
	),
	"CALENDAR_TYPE_VIEW" => array(
		"title" => Loc::getMessage("TASK_NAME_CALENDAR_TYPE_VIEW"),
	),
	"CALENDAR_TYPE_EDIT" => array(
		"title" => Loc::getMessage("TASK_NAME_CALENDAR_TYPE_EDIT"),
	),
	"CALENDAR_TYPE_ACCESS" => array(
		"title" => Loc::getMessage("TASK_NAME_CALENDAR_TYPE_ACCESS"),
	),

	"CALENDAR_SECTION" => array(
		"title" => Loc::getMessage("TASK_BINDING_CALENDAR_SECTION"),
	),
	"CALENDAR_TYPE" => array(
		"title" => Loc::getMessage("TASK_BINDING_CALENDAR_TYPE"),
	),
);
