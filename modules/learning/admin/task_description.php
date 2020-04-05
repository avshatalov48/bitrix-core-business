<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"LEARNING_LESSON_ACCESS_DENIED" => array(
		"title" => Loc::getMessage('TASK_NAME_LEARNING_LESSON_ACCESS_DENIED'),
		"description" => Loc::getMessage('TASK_DESC_LEARNING_LESSON_ACCESS_DENIED'),
	),
	"LEARNING_LESSON_ACCESS_READ" => array(
		"title" => Loc::getMessage('TASK_NAME_LEARNING_LESSON_ACCESS_READ'),
		"description" => Loc::getMessage('TASK_DESC_LEARNING_LESSON_ACCESS_READ'),
	),
	"LEARNING_LESSON_ACCESS_MANAGE_BASIC" => array(
		"title" => Loc::getMessage('TASK_NAME_LEARNING_LESSON_ACCESS_MANAGE_BASIC'),
		"description" => Loc::getMessage('TASK_DESC_LEARNING_LESSON_ACCESS_MANAGE_BASIC'),
	),
	"LEARNING_LESSON_ACCESS_LINKAGE_AS_CHILD" => array(
		"title" => Loc::getMessage('TASK_NAME_LEARNING_LESSON_ACCESS_LINKAGE_AS_CHILD'),
		"description" => Loc::getMessage('TASK_DESC_LEARNING_LESSON_ACCESS_LINKAGE_AS_CHILD'),
	),
	"LEARNING_LESSON_ACCESS_LINKAGE_AS_PARENT" => array(
		"title" => Loc::getMessage('TASK_NAME_LEARNING_LESSON_ACCESS_LINKAGE_AS_PARENT'),
		"description" => Loc::getMessage('TASK_DESC_LEARNING_LESSON_ACCESS_LINKAGE_AS_PARENT'),
	),
	"LEARNING_LESSON_ACCESS_LINKAGE_ANY" => array(
		"title" => Loc::getMessage('TASK_NAME_LEARNING_LESSON_ACCESS_LINKAGE_ANY'),
		"description" => Loc::getMessage('TASK_DESC_LEARNING_LESSON_ACCESS_LINKAGE_ANY'),
	),
	"LEARNING_LESSON_ACCESS_MANAGE_AS_CHILD" => array(
		"title" => Loc::getMessage('TASK_NAME_LEARNING_LESSON_ACCESS_MANAGE_AS_CHILD'),
		"description" => Loc::getMessage('TASK_DESC_LEARNING_LESSON_ACCESS_MANAGE_AS_CHILD'),
	),
	"LEARNING_LESSON_ACCESS_MANAGE_AS_PARENT" => array(
		"title" => Loc::getMessage('TASK_NAME_LEARNING_LESSON_ACCESS_MANAGE_AS_PARENT'),
		"description" => Loc::getMessage('TASK_DESC_LEARNING_LESSON_ACCESS_MANAGE_AS_PARENT'),
	),
	"LEARNING_LESSON_ACCESS_MANAGE_DUAL" => array(
		"title" => Loc::getMessage('TASK_NAME_LEARNING_LESSON_ACCESS_MANAGE_DUAL'),
		"description" => Loc::getMessage('TASK_DESC_LEARNING_LESSON_ACCESS_MANAGE_DUAL'),
	),
	"LEARNING_LESSON_ACCESS_MANAGE_FULL" => array(
		"title" => Loc::getMessage('TASK_NAME_LEARNING_LESSON_ACCESS_MANAGE_FULL'),
		"description" => Loc::getMessage('TASK_DESC_LEARNING_LESSON_ACCESS_MANAGE_FULL'),
	),
	"LESSON" => array(
		"title" => Loc::getMessage('TASK_BINDING_LESSON'),
	),
);
