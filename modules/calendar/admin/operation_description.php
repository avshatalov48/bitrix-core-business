<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"CALENDAR_TYPE_VIEW" => array(
		"title" => Loc::getMessage("OP_NAME_CALENDAR_TYPE_VIEW"),
	),
	"CALENDAR_TYPE_ADD" => array(
		"title" => Loc::getMessage("OP_NAME_CALENDAR_TYPE_ADD"),
	),
	"CALENDAR_TYPE_EDIT" => array(
		"title" => Loc::getMessage("OP_NAME_CALENDAR_TYPE_EDIT"),
	),
	"CALENDAR_TYPE_EDIT_ACCESS" => array(
		"title" => Loc::getMessage("OP_NAME_CALENDAR_TYPE_EDIT_ACCESS"),
	),
	"CALENDAR_TYPE_EDIT_SECTION" => array(
		"title" => Loc::getMessage("OP_NAME_CALENDAR_TYPE_EDIT_SECTION"),
	),
	"CALENDAR_ADD" => array(
		"title" => Loc::getMessage("OP_NAME_CALENDAR_ADD"),
	),
	"CALENDAR_EDIT" => array(
		"title" => Loc::getMessage("OP_NAME_CALENDAR_EDIT"),
	),
	"CALENDAR_EDIT_SECTION" => array(
		"title" => Loc::getMessage("OP_NAME_CALENDAR_EDIT_SECTION"),
	),
	"CALENDAR_VIEW_FULL" => array(
		"title" => Loc::getMessage("OP_NAME_CALENDAR_VIEW_FULL"),
	),
	"CALENDAR_VIEW_TIME" => array(
		"title" => Loc::getMessage("OP_NAME_CALENDAR_VIEW_TIME"),
	),
	"CALENDAR_VIEW_TITLE" => array(
		"title" => Loc::getMessage("OP_NAME_CALENDAR_VIEW_TITLE"),
	),
	"CALENDAR_EDIT_ACCESS" => array(
		"title" => Loc::getMessage("OP_NAME_CALENDAR_EDIT_ACCESS"),
	),
);
