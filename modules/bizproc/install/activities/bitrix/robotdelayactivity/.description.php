<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arActivityDescription = [
	"NAME" => GetMessage("BPRDA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPRDA_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "RobotDelayActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => [
		"ID" => "other",
	],
];
