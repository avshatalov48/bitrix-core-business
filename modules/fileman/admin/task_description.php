<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"FILEMAN_DENIED" => array(
		"title" => Loc::getMessage('TASK_NAME_FILEMAN_DENIED'),
		"description" => Loc::getMessage('TASK_DESC_FILEMAN_DENIED'),
	),
	"FILEMAN_ALLOWED_FOLDERS" => array(
		"title" => Loc::getMessage('TASK_NAME_FILEMAN_ALLOWED_FOLDERS'),
		"description" => Loc::getMessage('TASK_DESC_FILEMAN_ALLOWED_FOLDERS'),
	),
	"FILEMAN_FULL_ACCESS" => array(
		"title" => Loc::getMessage('TASK_NAME_FILEMAN_FULL_ACCESS'),
		"description" => Loc::getMessage('TASK_DESC_FILEMAN_FULL_ACCESS'),
	),
	"MEDIALIB_DENIED" => array(
		"title" => Loc::getMessage('TASK_NAME_MEDIALIB_DENIED'),
		"description" => Loc::getMessage('TASK_DESC_MEDIALIB_DENIED'),
	),
	"MEDIALIB_VIEW" => array(
		"title" => Loc::getMessage('TASK_NAME_MEDIALIB_VIEW'),
		"description" => Loc::getMessage('TASK_DESC_MEDIALIB_VIEW'),
	),
	"MEDIALIB_ONLY_NEW" => array(
		"title" => Loc::getMessage('TASK_NAME_MEDIALIB_ONLY_NEW'),
		"description" => Loc::getMessage('TASK_DESC_MEDIALIB_ONLY_NEW'),
	),
	"MEDIALIB_EDIT_ITEMS" => array(
		"title" => Loc::getMessage('TASK_NAME_MEDIALIB_EDIT_ITEMS'),
		"description" => Loc::getMessage('TASK_DESC_MEDIALIB_EDIT_ITEMS'),
	),
	"MEDIALIB_EDITOR" => array(
		"title" => Loc::getMessage('TASK_NAME_MEDIALIB_EDITOR'),
		"description" => Loc::getMessage('TASK_DESC_MEDIALIB_EDITOR'),
	),
	"MEDIALIB_FULL" => array(
		"title" => Loc::getMessage('TASK_NAME_MEDIALIB_FULL'),
		"description" => Loc::getMessage('TASK_DESC_MEDIALIB_FULL'),
	),
	"STICKERS_DENIED" => array(
		"title" => Loc::getMessage('TASK_NAME_STICKERS_DENIED'),
		"description" => Loc::getMessage('TASK_DESC_STICKERS_DENIED'),
	),
	"STICKERS_READ" => array(
		"title" => Loc::getMessage('TASK_NAME_STICKERS_READ'),
		"description" => Loc::getMessage('TASK_DESC_STICKERS_READ'),
	),
	"STICKERS_EDIT" => array(
		"title" => Loc::getMessage('TASK_NAME_STICKERS_EDIT'),
		"description" => Loc::getMessage('TASK_DESC_STICKERS_EDIT'),
	),
	"STICKERS" => array(
		"title" => Loc::getMessage('TASK_BINDING_STICKERS'),
	),
	"MEDIALIB" => array(
		"title" => Loc::getMessage('TASK_BINDING_MEDIALIB'),
	),
);
