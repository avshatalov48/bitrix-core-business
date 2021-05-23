<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule('rest'))
	return;

$arComponentParameters = CRestUtil::getStandardParams();

$arComponentParameters['PARAMETERS']['CLASS'] = array(
	"CLASS" => array(
		"PARENT" => "BASE",
		"NAME" => GetMessage('REST_RSP_CLASS'),
		"TYPE" => "STRING",
		"DEFAULT" => ""
	),
);
?>