<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("socialnetwork"))
	return false;

$arComponentParameters = Array(
	"GROUPS" => array(
		"VARIABLE_ALIASES" => array(
			"NAME" => GetMessage("SONET_VARIABLE_ALIASES"),
		),
	),
	"PARAMETERS" => Array(
		"PATH_TO_USER" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_USER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"MESSAGE_VAR" => Array(
			"NAME" => GetMessage("SONET_MESSAGE_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"PATH_TO_GROUP" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_GROUP"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_MESSAGE_FORM" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_MESSAGE_FORM"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_MESSAGE_FORM_MESS" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_MESSAGE_FORM_MESS"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_MESSAGES_CHAT" => Array(
			"NAME" => GetMessage("SONET_PATH_TO_MESSAGES_CHAT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PAGE_VAR" => Array(
			"NAME" => GetMessage("SONET_PAGE_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"USER_VAR" => Array(
			"NAME" => GetMessage("SONET_USER_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"PATH_TO_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/bitrix/images/socialnetwork/smile/",
		),
		"AJAX_LONG_TIMEOUT" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_AJAX_LONG_TIMEOUT"),
			"TYPE" => "STRING",
			"DEFAULT" => 60
		),
		"NAME_TEMPLATE" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("SONET_NAME_TEMPLATE"),
			"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"SHOW_LOGIN" => Array(
			"NAME" => GetMessage("SONET_SHOW_LOGIN"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),			
		"POPUP" => Array(
			"NAME" => GetMessage("SONET_EVENTS_DYN_POPUP"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),			
		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("SONET_DATE_TIME_FORMAT"), "VISUAL"),
		"SHOW_YEAR" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SONET_SHOW_YEAR"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"Y" => GetMessage("SONET_SHOW_YEAR_VALUE_Y"),
				"M" => GetMessage("SONET_SHOW_YEAR_VALUE_M"),
				"N" => GetMessage("SONET_SHOW_YEAR_VALUE_N")
			),
			"MULTIPLE" => "N",
			"DEFAULT" => "Y"
		),	
	)
);

if (CModule::IncludeModule("intranet"))
{
	$arComponentParameters["PARAMETERS"]["PATH_TO_CONPANY_DEPARTMENT"] = array(
		"NAME" => GetMessage("SONET_PATH_TO_CONPANY_DEPARTMENT"),
		"DEFAULT" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
		"PARENT" => "URL_TEMPLATES",
	);
}
