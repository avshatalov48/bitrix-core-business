<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\UserConsent\Agreement;

$arComponentParameters = array(
	"PARAMETERS" => array(
		"ID"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("COMP_MAIN_USER_CONSENT_PARAM_ID"),
			"TYPE" => "LIST",
			"VALUES" => Agreement::getActiveList(),
			"MULTIPLE" => "N",
			"DEFAULT" => "",
		),
		"IS_CHECKED"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("COMP_MAIN_USER_CONSENT_PARAM_IS_CHECKED"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"AUTO_SAVE"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("COMP_MAIN_USER_CONSENT_PARAM_IS_AUTO_SAVE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"IS_LOADED"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("COMP_MAIN_USER_CONSENT_PARAM_IS_IS_LOADED"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
	),
);