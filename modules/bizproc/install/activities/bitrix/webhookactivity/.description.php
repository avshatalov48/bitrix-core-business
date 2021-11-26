<?php

use Bitrix\Main;
use Bitrix\Rest;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arActivityDescription = array(
	"NAME" => GetMessage("BPWHA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPWHA_DESCR_DESCR"),
	"TYPE" => array('activity', 'robot_activity'),
	"CLASS" => "WebHookActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "other",
	),
	"ROBOT_SETTINGS" => array(
		'CATEGORY' => 'other'
	),
);

if (
	!Main\Loader::includeModule('rest')
	|| !Rest\Engine\Access::isAvailable()
)
{
	$arActivityDescription['EXCLUDED'] = true;
}
