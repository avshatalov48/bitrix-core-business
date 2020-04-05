<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"MAIN_DENIED" => array(
		"title" => Loc::getMessage('TASK_NAME_MAIN_DENIED'),
		"description" => Loc::getMessage('TASK_DESC_MAIN_DENIED'),
	),
	"MAIN_CHANGE_PROFILE" => array(
		"title" => Loc::getMessage('TASK_NAME_MAIN_CHANGE_PROFILE'),
		"description" => Loc::getMessage('TASK_DESC_MAIN_CHANGE_PROFILE'),
	),
	"MAIN_VIEW_ALL_SETTINGS" => array(
		"title" => Loc::getMessage('TASK_NAME_MAIN_VIEW_ALL_SETTINGS'),
		"description" => Loc::getMessage('TASK_DESC_MAIN_VIEW_ALL_SETTINGS'),
	),
	"MAIN_VIEW_ALL_SETTINGS_CHANGE_PROFILE" => array(
		"title" => Loc::getMessage('TASK_NAME_MAIN_VIEW_ALL_SETTINGS_CHANGE_PROFILE'),
		"description" => Loc::getMessage('TASK_DESC_MAIN_VIEW_ALL_SETTINGS_CHANGE_PROFILE'),
	),
	"MAIN_EDIT_SUBORDINATE_USERS" => array(
		"title" => Loc::getMessage('TASK_NAME_MAIN_EDIT_SUBORDINATE_USERS'),
		"description" => Loc::getMessage('TASK_DESC_MAIN_EDIT_SUBORDINATE_USERS'),
	),
	"MAIN_FULL_ACCESS" => array(
		"title" => Loc::getMessage('TASK_NAME_MAIN_FULL_ACCESS'),
		"description" => Loc::getMessage('TASK_DESC_MAIN_FULL_ACCESS'),
	),
	"FM_FOLDER_ACCESS_DENIED" => array(
		"title" => Loc::getMessage('TASK_NAME_FM_FOLDER_ACCESS_DENIED'),
		"description" => Loc::getMessage('TASK_DESC_FM_FOLDER_ACCESS_DENIED'),
	),
	"FM_FOLDER_ACCESS_READ" => array(
		"title" => Loc::getMessage('TASK_NAME_FM_FOLDER_ACCESS_READ'),
		"description" => Loc::getMessage('TASK_DESC_FM_FOLDER_ACCESS_READ'),
	),
	"FM_FOLDER_ACCESS_WRITE" => array(
		"title" => Loc::getMessage('TASK_NAME_FM_FOLDER_ACCESS_WRITE'),
		"description" => Loc::getMessage('TASK_DESC_FM_FOLDER_ACCESS_WRITE'),
	),
	"FM_FOLDER_ACCESS_WORKFLOW" => array(
		"title" => Loc::getMessage('TASK_NAME_FM_FOLDER_ACCESS_WORKFLOW'),
		"description" => Loc::getMessage('TASK_DESC_FM_FOLDER_ACCESS_WORKFLOW'),
	),
	"FM_FOLDER_ACCESS_FULL" => array(
		"title" => Loc::getMessage('TASK_NAME_FM_FOLDER_ACCESS_FULL'),
		"description" => Loc::getMessage('TASK_DESC_FM_FOLDER_ACCESS_FULL'),
	),
	"MODULE" => array(
		"title" => Loc::getMessage("TASK_BINDING_MODULE"),
	),
	"FILE" => array(
		"title" => Loc::getMessage("TASK_BINDING_FILE"),
	),
);
