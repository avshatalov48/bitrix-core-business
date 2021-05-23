<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("blog"))
	return false;

$arGroupList = Array();
$dbGroup = CBlogGroup::GetList(Array("SITE_ID" => "ASC", "NAME" => "ASC"));
while($arGroup = $dbGroup->Fetch())
{
	$arGroupList[$arGroup["ID"]] = "(".$arGroup["SITE_ID"].") [".$arGroup["ID"]."] ".$arGroup["NAME"];
}

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"BLOG_COUNT" => Array(
				"NAME" => GetMessage("BLOG_DESCR_NB_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 6,
				"PARENT" => "VISUAL",
			),
		"SHOW_DESCRIPTION" => array(
			"NAME"				=> GetMessage("BLOG_DESCR_NB_S_DESCR"),
			"TYPE"				=> "LIST",
			"VALUES"			=> array("Y" => GetMessage("BLOG_DESCR_Y"), "N" => GetMessage("BLOG_DESCR_N")),
			"ADDITIONAL_VALUES"	=> "N",
			"DEFAULT"			=> "Y",
			"PARENT" => "VISUAL",
			),
		"SORT_BY1" => array(
			"NAME"				=> GetMessage("BLOG_DESCR_SORT_1"),
			"TYPE"				=> "LIST",
			"VALUES"			=> array(
				"DATE_CREATE"	=> GetMessage("BLOG_DESCR_DATE_CREATE"),
				"ID"			=> "ID",
				"NAME"			=> GetMessage("BLOG_DESCR_BLOG_NAME"),
				"LAST_POST_DATE"	=> GetMessage("BLOG_DESCR_LAST_MES"),
				),
			"ADDITIONAL_VALUES"	=> "Y",
			"DEFAULT"			=> "DATE_CREATE",
			"PARENT" => "ADDITIONAL_SETTINGS",
			),
		"SORT_ORDER1" => array(
			"NAME"				=> GetMessage("BLOG_DESCR_SORT_ORDER"),
			"TYPE"				=> "LIST",
			"VALUES"			=> array("ASC" => GetMessage("BLOG_DESCR_SORT_ASC"), "DESC" => GetMessage("BLOG_DESCR_SORT_DESC")),
			"ADDITIONAL_VALUES"	=> "N",
			"DEFAULT"			=> "DESC",
			"PARENT" => "ADDITIONAL_SETTINGS",
			),
		"SORT_BY2" => array(
			"NAME"				=> GetMessage("BLOG_DESCR_SORT_2"),
			"TYPE"				=> "LIST",
			"VALUES"			=> array(
				"DATE_CREATE"	=> GetMessage("BLOG_DESCR_DATE_CREATE"),
				"ID"			=> "ID",
				"NAME"			=> GetMessage("BLOG_DESCR_BLOG_NAME"),
				"LAST_POST_DATE"	=> GetMessage("BLOG_DESCR_LAST_MES"),
				),
			"ADDITIONAL_VALUES"	=> "Y",
			"DEFAULT"			=> "ID",
			"PARENT" => "ADDITIONAL_SETTINGS",
			),
		"SORT_ORDER2" => array(
			"NAME"				=> GetMessage("BLOG_DESCR_SORT_ORDER"),
			"TYPE"				=> "LIST",
			"VALUES"			=> array("ASC" => GetMessage("BLOG_DESCR_SORT_ASC"), "DESC" => GetMessage("BLOG_DESCR_SORT_DESC")),
			"ADDITIONAL_VALUES"	=> "N",
			"DEFAULT"			=> "DESC",
			"PARENT" => "ADDITIONAL_SETTINGS",
			),
		"PATH_TO_BLOG" => Array(
			"NAME" => GetMessage("BMN_PATH_TO_BLOG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_USER" => Array(
			"NAME" => GetMessage("BMN_PATH_TO_USER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_GROUP" => Array(
			"NAME" => GetMessage("BMN_PATH_TO_GROUP"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_GROUP_BLOG" => Array(
			"NAME" => GetMessage("BMN_PATH_TO_GROUP_BLOG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"BLOG_VAR" => Array(
			"NAME" => GetMessage("BMN_BLOG_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"USER_VAR" => Array(
			"NAME" => GetMessage("BMN_USER_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"PAGE_VAR" => Array(
			"NAME" => GetMessage("BMN_PAGE_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"CACHE_TIME"	=>	array("DEFAULT"=>"86400"),
		"GROUP_ID"=>array(
			"NAME" => GetMessage("BLG_GROUP_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arGroupList,
			"MULTIPLE" => "N",
			"DEFAULT" => "",	
			"ADDITIONAL_VALUES" => "Y",
			"PARENT" => "DATA_SOURCE",
		),

	)
);
?>