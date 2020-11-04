<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("blog"))
	return false;

$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", 0, LANGUAGE_ID);
$postProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$postProp[$val["FIELD_NAME"]] = ($val["EDIT_FORM_LABEL"] <> '' ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}

$arComponentParameters = Array(
	"GROUPS" => array(
		"VARIABLE_ALIASES" => array(
			"NAME" => GetMessage("B_VARIABLE_ALIASES"),
		),
	),
	"PARAMETERS" => Array(
		"USER_CONSENT" => array(),
		"BLOG_URL" => Array(
				"NAME" => GetMessage("BPE_BLOG_URL"),
				"TYPE" => "STRING",
				"DEFAULT" => "={\$blog}",
				"PARENT" => "DATA_SOURCE",
			),
		"ID" => Array(
				"NAME" => GetMessage("BPE_ID"),
				"TYPE" => "STRING",
				"DEFAULT" => "={\$id}",
				"PARENT" => "DATA_SOURCE",
			),
		"PATH_TO_BLOG" => Array(
			"NAME" => GetMessage("BPE_PATH_TO_BLOG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_POST" => Array(
			"NAME" => GetMessage("BPE_PATH_TO_POST"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_POST_EDIT" => Array(
			"NAME" => GetMessage("BPE_PATH_TO_POST_EDIT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_USER" => Array(
			"NAME" => GetMessage("BPE_PATH_TO_USER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_DRAFT" => Array(
			"NAME" => GetMessage("BH_PATH_TO_DRAFT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_SMILE" => Array(
			"NAME" => GetMessage("BB_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"BLOG_VAR" => Array(
			"NAME" => GetMessage("BPE_BLOG_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"POST_VAR" => Array(
			"NAME" => GetMessage("BPE_POST_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"USER_VAR" => Array(
			"NAME" => GetMessage("BPE_USER_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"PAGE_VAR" => Array(
			"NAME" => GetMessage("BPE_PAGE_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"SET_TITLE" =>Array(),
		"SEO_USE" => Array(
			"NAME" => GetMessage("BC_SEO_USE"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"POST_PROPERTY"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("POST_PROPERTY"),
			"TYPE" => "LIST",
			"VALUES" => $postProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),	
		),
		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("BC_DATE_TIME_FORMAT"), "VISUAL"),		
		"SMILES_COUNT" => Array(
				"NAME" => GetMessage("BPC_SMILES_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 4,
				"PARENT" => "VISUAL",
			),
		"ALLOW_POST_MOVE" => Array(
			"NAME" => GetMessage("BPE_ALLOW_POST_MOVE"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"REFRESH" => "Y",
		),
		"IMAGE_MAX_WIDTH" => Array(
				"NAME" => GetMessage("BPC_IMAGE_MAX_WIDTH"),
				"TYPE" => "STRING",
				"DEFAULT" => 600,
				"PARENT" => "VISUAL",
			),		
		"IMAGE_MAX_HEIGHT" => Array(
				"NAME" => GetMessage("BPC_IMAGE_MAX_HEIGHT"),
				"TYPE" => "STRING",
				"DEFAULT" => 600,
				"PARENT" => "VISUAL",
			),
		"EDITOR_RESIZABLE" => Array(
				"NAME" => GetMessage("BPC_EDITOR_RESIZABLE"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "Y",
				"PARENT" => "VISUAL",
			),		
		"EDITOR_DEFAULT_HEIGHT" => Array(
				"NAME" => GetMessage("BPC_EDITOR_DEFAULT_HEIGHT"),
				"TYPE" => "STRING",
				"DEFAULT" => 300,
				"PARENT" => "VISUAL",
			),
		"EDITOR_CODE_DEFAULT" => Array(
				"NAME" => GetMessage("BPC_EDITOR_CODE_DEFAULT"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"PARENT" => "VISUAL",
			),
		"ALLOW_POST_CODE" => Array(
				"NAME" => GetMessage("BPC_ALLOW_POST_CODE"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "Y",
				"PARENT" => "ADDITIONAL_SETTINGS",
				"REFRESH" => "Y",
			),
	)
);

if ($arCurrentValues["ALLOW_POST_MOVE"] == "Y")
{
	$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG_POST"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_BLOG_POST"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);	
	$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG_POST_EDIT"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_BLOG_POST_EDIT"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);	
	$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG_DRAFT"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_BLOG_DRAFT"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);	
	$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG_BLOG"] = array(
		"NAME" => GetMessage("BPE_PATH_TO_BLOG_BLOG"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);
	
	if(CModule::IncludeModule("socialnetwork"))
	{
		$arComponentParameters["PARAMETERS"]["PATH_TO_USER_POST"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_USER_POST"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		);	
		$arComponentParameters["PARAMETERS"]["PATH_TO_USER_POST_EDIT"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_USER_POST_EDIT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		);	
		$arComponentParameters["PARAMETERS"]["PATH_TO_USER_DRAFT"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_USER_DRAFT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		);	
		$arComponentParameters["PARAMETERS"]["PATH_TO_USER_BLOG"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_USER_BLOG"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		);
		
		$arComponentParameters["PARAMETERS"]["PATH_TO_GROUP_POST"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_GROUP_POST"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		);	
		$arComponentParameters["PARAMETERS"]["PATH_TO_GROUP_POST_EDIT"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_GROUP_POST_EDIT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		);	
		$arComponentParameters["PARAMETERS"]["PATH_TO_GROUP_DRAFT"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_GROUP_DRAFT"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		);	
		$arComponentParameters["PARAMETERS"]["PATH_TO_GROUP_BLOG"] = array(
			"NAME" => GetMessage("BPE_PATH_TO_GROUP_BLOG"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		);
	}
}
/*
if ($arCurrentValues["ALLOW_POST_CODE"] != "N")
{
	$arComponentParameters["PARAMETERS"]["USE_GOOGLE_CODE"] = array(
		"NAME" => GetMessage("BPE_USE_GOOGLE_CODE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);
}
*/
?>