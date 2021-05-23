<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("advertising"))
	return;

$arTemplateParameters = array(
	"HEIGHT" => array(
		"NAME" => GetMessage("ADV_PARALL_HEIGHT"),
		"PARENT" => "BASE",
		"TYPE" => "STRING",
		"DEFAULT" => "300"
	)
);
$arTemplateParameters["CACHE_TIME"] = Array("DEFAULT"=>"0");
$arTemplateParameters["NEED_TEMPLATE"] = "Y";
