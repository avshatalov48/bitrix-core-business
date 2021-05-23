<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"WEBSERVICE_NAME" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BX_WS_WEBSERVICE_NAME"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",			
			"DEFAULT" => "WebService EndPoint: Bitrix",
			"REFRESH" => "Y",
		),
		"WEBSERVICE_MODULE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BX_WS_WEBSERVICE_MODULE"),
			"TYPE" => "STRING",		
			"MULTIPLE" => "N",	
			"DEFAULT" => "",
			"REFRESH" => "Y",
		),
		"WEBSERVICE_CLASS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BX_WS_WEBSERVICE_CLASS"),
			"TYPE" => "STRING",		
			"MULTIPLE" => "N",	
			"DEFAULT" => "CGenericWSStub",
			"REFRESH" => "Y",
		)
	),
);
?>
