<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arComponentParameters = Array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
	),
	"PARAMETERS" => Array(
		"MID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_MID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["MID"]}'),
		"FID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_FID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["FID"]}'),
		"UID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_UID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["UID"]}'),
		"mode" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_MODE"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["mode"]}'),
	
		"URL_TEMPLATES_PM_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "pm_list.php?FID=#FID#"),
		"URL_TEMPLATES_PM_READ" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_READ_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "pm_read.php?MID=#MID#"),
		"URL_TEMPLATES_PM_EDIT" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_EDIT_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "pm_edit.php?MID=#MID#"),
		"URL_TEMPLATES_PM_SEARCH" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_SEARCH_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "pm_search.php?MID=#MID#"),
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#"),

		"EDITOR_CODE_DEFAULT" => Array(
			"NAME" => GetMessage("F_EDITOR_CODE_DEFAULT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",),
		"SET_NAVIGATION" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_NAVIGATION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"NAME_TEMPLATE" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("F_NAME_TEMPLATE"),
			"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		
		"CACHE_TIME" => Array(),
		"SET_TITLE" => Array(),
	)
);
?>
