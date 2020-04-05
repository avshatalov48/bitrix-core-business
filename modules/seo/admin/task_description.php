<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"SEO_DENIED" => array(
		"title" => Loc::getMessage('TASK_NAME_SEO_DENIED'),
	),
	"SEO_EDIT" => array(
		"title" => Loc::getMessage('TASK_NAME_SEO_EDIT'),
	),
	"SEO_FULL_ACCESS" => array(
		"title" => Loc::getMessage('TASK_NAME_SEO_FULL_ACCESS'),
	),
);
