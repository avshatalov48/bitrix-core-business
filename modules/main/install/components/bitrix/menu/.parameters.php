<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$site = ($_REQUEST["site"] <> ''? $_REQUEST["site"] : ($_REQUEST["src_site"] <> ''? $_REQUEST["src_site"] : false));
$arMenu = GetMenuTypes($site);

$arComponentParameters = array(
	"GROUPS" => array(
		"CACHE_SETTINGS" => array(
			"NAME" => GetMessage("COMP_GROUP_CACHE_SETTINGS"),
			"SORT" => 600
		),
	),
	"PARAMETERS" => array(

		"ROOT_MENU_TYPE" => Array(
			"NAME"=>GetMessage("MAIN_MENU_TYPE_NAME"),
			"TYPE" => "LIST",
			"DEFAULT"=>'left',
			"VALUES" => $arMenu,
			"ADDITIONAL_VALUES"	=> "Y",
			"DEFAULT"=>'left',
			"PARENT" => "BASE",
			"COLS" => 45
		),

		"MAX_LEVEL" => Array(
			"NAME"=>GetMessage("MAX_LEVEL_NAME"),
			"TYPE" => "LIST",
			"DEFAULT"=>'1',
			"PARENT" => "ADDITIONAL_SETTINGS",
			"VALUES" => Array(
				1 => "1",
				2 => "2",
				3 => "3",
				4 => "4",
			),
			"ADDITIONAL_VALUES"	=> "N",
		),

		"CHILD_MENU_TYPE" => Array(
			"NAME"=>GetMessage("CHILD_MENU_TYPE_NAME"),
			"TYPE" => "LIST",
			"DEFAULT"=>'left',
			"VALUES" => $arMenu,
			"ADDITIONAL_VALUES"	=> "Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT"=>'left',
			"COLS" => 45
		),

		"USE_EXT" => Array(
			"NAME"=>GetMessage("USE_EXT_NAME"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>'N',
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		
		"DELAY" => Array(
			"NAME"=>GetMessage("DELAY_NAME"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>'N',
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"ALLOW_MULTI_SELECT" => Array(
			"NAME"=>GetMessage("comp_menu_allow_multi_select"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>'N',
			"PARENT" => "ADDITIONAL_SETTINGS",
		),

		"MENU_CACHE_TYPE" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("COMP_PROP_CACHE_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"A" => GetMessage("COMP_PROP_CACHE_TYPE_AUTO"),
				"Y" => GetMessage("COMP_PROP_CACHE_TYPE_YES"),
				"N" => GetMessage("COMP_PROP_CACHE_TYPE_NO"),
			),
			"DEFAULT" => "N",
			"ADDITIONAL_VALUES" => "N",
		),

		"MENU_CACHE_TIME" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("COMP_PROP_CACHE_TIME"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => 3600,
			"COLS" => 5,
		),

		"MENU_CACHE_USE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BM_MENU_CACHE_USE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),

		"MENU_CACHE_GET_VARS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BM_MENU_CACHE_GET_VARS"),
			"TYPE" => "STRING",
			"MULTIPLE" => "Y",
			"DEFAULT" => "",
			"COLS" => 15,
		),

	)
);
?>
