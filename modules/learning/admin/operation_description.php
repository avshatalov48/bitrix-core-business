<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"LESSON_READ" => array(
		"title" => Loc::getMessage('OP_NAME_LESSON_READ'),
	),
	"LESSON_CREATE" => array(
		"title" => Loc::getMessage('OP_NAME_LESSON_CREATE'),
	),
	"LESSON_WRITE" => array(
		"title" => Loc::getMessage('OP_NAME_LESSON_WRITE'),
	),
	"LESSON_REMOVE" => array(
		"title" => Loc::getMessage('OP_NAME_LESSON_REMOVE'),
	),
	"LESSON_LINK_TO_PARENTS" => array(
		"title" => Loc::getMessage('OP_NAME_LESSON_LINK_TO_PARENTS'),
	),
	"LESSON_UNLINK_FROM_PARENTS" => array(
		"title" => Loc::getMessage('OP_NAME_LESSON_UNLINK_FROM_PARENTS'),
	),
	"LESSON_LINK_DESCENDANTS" => array(
		"title" => Loc::getMessage('OP_NAME_LESSON_LINK_DESCENDANTS'),
	),
	"LESSON_UNLINK_DESCENDANTS" => array(
		"title" => Loc::getMessage('OP_NAME_LESSON_UNLINK_DESCENDANTS'),
	),
	"LESSON_MANAGE_RIGHTS" => array(
		"title" => Loc::getMessage('OP_NAME_LESSON_MANAGE_RIGHTS'),
	),
);
