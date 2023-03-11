<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arActivityDescription = [
	"NAME" => GetMessage("BPWWD_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPWWD_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "WaitWorkDayActivity",
	'JSCLASS' => 'BizProcActivity',
	"CATEGORY" => [
		"ID" => "other",
	],
];

if (!CBPHelper::isWorkTimeAvailable())
{
	$arActivityDescription['EXCLUDED'] = true;
}
