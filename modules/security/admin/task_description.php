<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"SECURITY_DENIED" => array(
		"title" => Loc::getMessage('TASK_NAME_SECURITY_DENIED'),
		"description" => Loc::getMessage('TASK_DESC_SECURITY_DENIED'),
	),
	"SECURITY_FILTER" => array(
		"title" => Loc::getMessage('TASK_NAME_SECURITY_FILTER'),
		"description" => Loc::getMessage('TASK_DESC_SECURITY_FILTER'),
	),
	"SECURITY_OTP" => array(
		"title" => Loc::getMessage('TASK_NAME_SECURITY_OTP'),
		"description" => Loc::getMessage('TASK_DESC_SECURITY_OTP'),
	),
	"SECURITY_VIEW_ALL_SETTINGS" => array(
		"title" => Loc::getMessage('TASK_NAME_SECURITY_VIEW_ALL_SETTINGS'),
		"description" => Loc::getMessage('TASK_DESC_SECURITY_VIEW_ALL_SETTINGS'),
	),
	"SECURITY_FULL_ACCESS" => array(
		"title" => Loc::getMessage('TASK_NAME_SECURITY_FULL_ACCESS'),
		"description" => Loc::getMessage('TASK_DESC_SECURITY_FULL_ACCESS'),
	),
);
