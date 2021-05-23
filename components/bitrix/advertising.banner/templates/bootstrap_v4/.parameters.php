<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("advertising"))
	return;

$arTemplateParameters = array(
	"BS_EFFECT" => array(
		"NAME" => GetMessage("ADV_BS_EFFECT"),
		"PARENT" => "BASE",
		"TYPE" => "LIST",
		"VALUES" => array(
			'fade' => GetMessage("ADV_BS_EFFECT_FADE"),
			'slide' => GetMessage("ADV_BS_EFFECT_SLIDE")
		)
	),
	"BS_CYCLING" => array(
		"NAME" => GetMessage("ADV_BS_CYCLING"),
		"PARENT" => "BASE",
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y"
	)
);
if ($arCurrentValues['BS_CYCLING'] == 'Y')
{
	$arTemplateParameters['BS_INTERVAL'] = array(
		"NAME" => GetMessage("ADV_BS_INTERVAL"),
		"PARENT" => "BASE",
		"TYPE" => "STRING",
		"DEFAULT" => "5000"
	);
	$arTemplateParameters["BS_PAUSE"] = array(
		"NAME" => GetMessage("ADV_BS_PAUSE"),
		"PARENT" => "BASE",
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"
	);
}
$arTemplateParameters['BS_WRAP'] = array(
	"NAME" => GetMessage("ADV_BS_WRAP"),
	"PARENT" => "BASE",
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y"
);
$arTemplateParameters["BS_KEYBOARD"] = array(
	"NAME" => GetMessage("ADV_BS_KEYBOARD"),
	"PARENT" => "BASE",
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y"
);
$arTemplateParameters["BS_ARROW_NAV"] = array(
	"NAME" => GetMessage("ADV_BS_ARROW_NAV"),
	"PARENT" => "BASE",
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y"
);
$arTemplateParameters["BS_BULLET_NAV"] = array(
	"NAME" => GetMessage("ADV_BS_BULLET_NAV"),
	"PARENT" => "BASE",
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y"
);
$arTemplateParameters["BS_HIDE_FOR_TABLETS"] = array(
	"NAME" => GetMessage("ADV_BS_HIDE_FOR_TABLETS"),
	"PARENT" => "BASE",
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N"
);
$arTemplateParameters["BS_HIDE_FOR_PHONES"] = array(
	"NAME" => GetMessage("ADV_BS_HIDE_FOR_PHONES"),
	"PARENT" => "BASE",
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N"
);
$arTemplateParameters["CACHE_TIME"] = Array("DEFAULT"=>"0");
$arTemplateParameters["NEED_TEMPLATE"] = "Y";
