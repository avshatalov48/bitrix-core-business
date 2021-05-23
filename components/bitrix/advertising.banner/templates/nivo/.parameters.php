<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("advertising"))
	return;

$arTemplateParameters = array(
	"EFFECT" => array(
		"NAME" => GetMessage("ADV_NIVO_EFFECT"),
		"PARENT" => "BASE",
		"TYPE" => "LIST",
		"VALUES" => array(
			'random' => GetMessage("ADV_NIVO_EFFECT_RANDOM"),
			'sliceDownRight' => 'sliceDownRight',
			'sliceDownLeft' => 'sliceDownLeft',
			'fold' => 'fold',
			'fade' => 'fade',
			'slideInRight' => 'slideInRight',
			'slideInLeft' => 'slideInLeft'
		),
		"DEFAULT" => "random"
	)
);
$arTemplateParameters["CYCLING"] = array(
	"NAME" => GetMessage("ADV_NIVO_CYCLING"),
	"PARENT" => "BASE",
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"REFRESH" => "Y"
);

if ($arCurrentValues['CYCLING'] == 'Y')
{
	$arTemplateParameters["INTERVAL"] = array(
		"NAME" => GetMessage("ADV_NIVO_INTERVAL"),
		"PARENT" => "BASE",
		"TYPE" => "STRING",
		"DEFAULT" => "5000"
	);
	$arTemplateParameters["PAUSE"] = array(
		"NAME" => GetMessage("ADV_NIVO_PAUSE"),
		"PARENT" => "BASE",
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"
	);
}
$arTemplateParameters["SPEED"] = array(
	"NAME" => GetMessage("ADV_NIVO_SPEED"),
	"PARENT" => "BASE",
	"TYPE" => "STRING",
	"DEFAULT" => "500"
);
$arTemplateParameters["JQUERY"] = array(
	"NAME" => GetMessage("ADV_NIVO_JQUERY"),
	"PARENT" => "BASE",
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y"
);
$arTemplateParameters["DIRECTION_NAV"] = array(
	"NAME" => GetMessage("ADV_NIVO_DIRECTION_NAV"),
	"PARENT" => "BASE",
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y"
);
$arTemplateParameters["CONTROL_NAV"] = array(
	"NAME" => GetMessage("ADV_NIVO_CONTROL_NAV"),
	"PARENT" => "BASE",
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y"
);
$arTemplateParameters["CACHE_TIME"] = Array("DEFAULT"=>"0");
$arTemplateParameters["NEED_TEMPLATE"] = "Y";
