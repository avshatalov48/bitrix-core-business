<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
	"STRUCTURE_FILTER" => array(
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "structure",
		"NAME" => GetMessage("TP_BSP_STRUCTURE_FILTER"),
		"PARENT" => "BASE"
	),
	"USE_SUGGEST" => Array(
		"NAME" => GetMessage("TP_BSP_USE_SUGGEST"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
	),
	"NAME_TEMPLATE" => array(
		"TYPE" => "LIST",
		"NAME" => GetMessage("TP_BSP_NAME_TEMPLATE"),
		"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
		"MULTIPLE" => "N",
		"ADDITIONAL_VALUES" => "Y",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	),
	"SHOW_LOGIN" => Array(
		"NAME" => GetMessage("TP_BSP_SHOW_LOGIN"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"VALUE" => "Y",
		"DEFAULT" =>"Y",
		"PARENT" => "ADDITIONAL_SETTINGS",
	)
);

if (IsModuleInstalled('socialnetwork'))
{
	$arTemplateParameters["PATH_TO_SONET_MESSAGES_CHAT"] = array(
			"TYPE" => "STRING",
			"DEFAULT" => "/company/personal/messages/chat/#USER_ID#/",
			"NAME" => GetMessage("TP_BSP_PATH_TO_SONET_MESSAGES_CHAT"),
			"PARENT" => "ADDITIONAL_SETTINGS",
		);

	if (IsModuleInstalled('intranet'))
		$arTemplateParameters["PATH_TO_CONPANY_DEPARTMENT"] = array(
			"TYPE" => "STRING",
			"DEFAULT" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
			"NAME" => GetMessage("TP_BSP_PATH_TO_CONPANY_DEPARTMENT"),
			"PARENT" => "ADDITIONAL_SETTINGS",
		);
}

if(COption::GetOptionString("search", "use_social_rating") == "Y")
{
	$arTemplateParameters["SHOW_RATING"] = Array(
		"NAME" => GetMessage("TP_BSP_SHOW_RATING"),
		"TYPE" => "LIST",
		"VALUES" => Array(
			"" => GetMessage("TP_BSP_SHOW_RATING_CONFIG"),
			"Y" => GetMessage("MAIN_YES"),
			"N" => GetMessage("MAIN_NO"),
		),
		"MULTIPLE" => "N",
		"DEFAULT" => "",
	);
	$arTemplateParameters["RATING_TYPE"] = Array(
		"NAME" => GetMessage("TP_BSP_RATING_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => Array(
			"" => GetMessage("TP_BSP_RATING_TYPE_CONFIG"),
			"like" => GetMessage("TP_BSP_RATING_TYPE_LIKE_TEXT"),
			"like_graphic" => GetMessage("TP_BSP_RATING_TYPE_LIKE_GRAPHIC"),
			"standart_text" => GetMessage("TP_BSP_RATING_TYPE_STANDART_TEXT"),
			"standart" => GetMessage("TP_BSP_RATING_TYPE_STANDART_GRAPHIC"),
		),
		"MULTIPLE" => "N",
		"DEFAULT" => "",
	);
	$arTemplateParameters["PATH_TO_USER_PROFILE"] = Array(
		"NAME" => GetMessage("TP_BSP_PATH_TO_USER_PROFILE"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	);
}
?>
