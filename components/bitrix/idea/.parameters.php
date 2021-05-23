<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("blog"))
	return false;
if(!CModule::IncludeModule("iblock"))
		return false;
if(!CModule::IncludeModule("idea"))
	return false;
$arCurrentValues = (is_array($arCurrentValues) ? $arCurrentValues : array());
//User groups, offical
$arGroups = array();
$oRes = CGroup::GetList($by = "name", $order = "asc"); 
while($arRes = $oRes->Fetch())
	$arGroups[$arRes["ID"]] = $arRes["NAME"];

//IB Idea category
$arIb = array();
$oRes = CIblock::GetList(
	array("IBLOCK_ID" => "ASC", "NAME" => "DESC"),
	array('ACTIVE'=>'Y')
);
while($arRes = $oRes->Fetch())
	$arIb[$arRes["ID"]] = '('.$arRes["IBLOCK_TYPE_ID"].') '.$arRes["NAME"];

//Default Idea Status
$arUFStatus = array();
$arStatusList = CIdeaManagment::getInstance()->Idea()->GetStatusList();
foreach($arStatusList as $Status)
	$arUFStatus[$Status["ID"]] = $Status["VALUE"];

//Blog URL
$arBlog = array();
$dbBlog = CBlog::GetList(array("NAME" => "ASC"), array("ACTIVE" => "Y"), false, false, array("ID", "NAME", "URL"));
while($Blog = $dbBlog->Fetch())
	$arBlog[$Blog["URL"]] = $Blog["NAME"];

$arComponentParameters = array(
	"PARAMETERS" => array(
		"VARIABLE_ALIASES" => Array(
			"post_id" => Array(
				"NAME" => GetMessage("BC_POST_VAR"),
				"DEFAULT" => "post_id",
			),
			"user_id" => Array(
				"NAME" => GetMessage("BC_USER_VAR"),
				"DEFAULT" => "user_id",
			),
			"page" => Array(
				"NAME" => GetMessage("BC_PAGE_VAR"),
				"DEFAULT" => "page",
			),
			"category_1" => Array(
				"NAME" => GetMessage("IDEA_PARAMETER_CATEGORY_1_VARIABLE_TITLE"),
				"DEFAULT" => "category_1",
			),
			"category_2" => Array(
				"NAME" => GetMessage("IDEA_PARAMETER_CATEGORY_2_VARIABLE_TITLE"),
				"DEFAULT" => "category_2",
			),
			"status_code" => Array(
				"NAME" => GetMessage("IDEA_PARAMETER_STATUS_CODE_VARIABLE_TITLE"),
				"DEFAULT" => "status_code",
			),
			"category" => Array(
				"NAME" => GetMessage("IDEA_PARAMETER_CATEGORY_VARIABLE_TITLE"), //LANG
				"DEFAULT" => "category",
			),
		),
		"SEF_MODE" => Array(
			"index" => array(
				"NAME" => GetMessage("BC_SEF_PATH_INDEX"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array(),
			),
			//Statuese&Categories
			"status_0" => array(
				"NAME" => GetMessage("IDEA_PARAMETER_STATUS_0_TITLE"),
				"DEFAULT" => "status/#status_code#/",
				"VARIABLES" => array("status_code"),
			),
			"category_1" => array(
				"NAME" => GetMessage("IDEA_PARAMETER_CATEGORY_1_TITLE"),
				"DEFAULT" => "category/#category_1#/",
				"VARIABLES" => array("category_1"),
			),
			"category_1_status" => array(
				"NAME" => GetMessage("IDEA_PARAMETER_STATUS_1_TITLE"),
				"DEFAULT" => "category/#category_1#/status/#status_code#/",
				"VARIABLES" => array("category_1", "status_code"),
			),
			"category_2" => array(
				"NAME" => GetMessage("IDEA_PARAMETER_CATEGORY_2_TITLE"),
				"DEFAULT" => "category/#category_1#/#category_2#/",
				"VARIABLES" => array("category_1", "category_2"),
			),
			"category_2_status" => array(
				"NAME" => GetMessage("IDEA_PARAMETER_STATUS_2_TITLE"),
				"DEFAULT" => "category/#category_1#/#category_2#/status/#status_code#/",
				"VARIABLES" => array("category_1", "category_2", "status_code"),
			),
			//User
			"user_ideas" => array(
				"NAME" => GetMessage("IDEA_PARAMETER_USER_IDEAS_TITLE"),
				"DEFAULT" => "user/#user_id#/idea/",
				"VARIABLES" => array("user_id"),
			),
			"user_ideas_status" => array(
				"NAME" => GetMessage("IDEA_PARAMETER_USER_IDEAS_STATUS_TITLE"),
				"DEFAULT" => "user/#user_id#/idea/status/#status_code#/",
				"VARIABLES" => array("user_id", "status_code"),
			),
			"user" => array(
				"NAME" => GetMessage("BC_SEF_PATH_USER"),
				"DEFAULT" => "user/#user_id#/",
				"VARIABLES" => array("user_id"),
			),
			"user_subscribe" => array(
				"NAME" => GetMessage("IDEA_PARAMETER_USER_SUBSCRIBE"),
				"DEFAULT" => "user/#user_id#/subscribe/",
				"VARIABLES" => array("user_id"),
			),
			//Post
			"post_edit" => array(
				"NAME" => GetMessage("BC_SEF_PATH_POST_EDIT"),
				"DEFAULT" => "edit/#post_id#/",
				"VARIABLES" => array("post_id"),
			),
			"post" => array(
				"NAME" => GetMessage("BC_SEF_PATH_POST"),
				"DEFAULT" => "#post_id#/",
				"VARIABLES" => array("blog", "post_id"),
			),
		),
		//main parameters
		"BLOG_URL" => array(
			"NAME" => GetMessage("ONE_BLOG_BLOG_URL"),
			"TYPE" => "LIST",
			"DEFAULT" => "",
			"PARENT" => "BASE",
			"REFRESH" => "Y",
			"VALUES" => $arBlog,
			"ADDITIONAL_VALUES" => "Y",
		),
		"IBLOCK_CATEGORIES" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("BC_POST_IBLOCK_CATEGORIES"),
			"VALUES" => $arIb,
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"POST_BIND_USER" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("BC_POST_BIND_USER"),
			"VALUES" => $arGroups,
			"MULTIPLE" => "Y",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"POST_BIND_STATUS_DEFAULT" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("IDEA_PARAM_POST_BIND_STATUS_DEFAULT"),
			"VALUES" => $arUFStatus,
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"PARENT" => "BASE",
		),
		"PATH_TO_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("BC_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/bitrix/images/blog/smile/",
		),
		"CACHE_TIME" => Array("DEFAULT" => 3600),
		"SET_TITLE" => Array(),
		"CACHE_TIME_LONG" => array(
			"NAME" => GetMessage("BC_CACHE_TIME_LONG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "604800",
			"COLS" => 25,
			"PARENT" => "CACHE_SETTINGS",
		),
		"SET_NAV_CHAIN" => Array(
			"NAME" => GetMessage("BC_SET_NAV_CHAIN"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" => "Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"MESSAGE_COUNT" => Array(
			"NAME" => GetMessage("BC_MESSAGE_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => 25,
			"PARENT" => "VISUAL",
		),
		"COMMENTS_COUNT" => Array(
			"NAME" => GetMessage("BC_COMMENTS_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => 25,
			"PARENT" => "VISUAL",
		),
		"USE_ASC_PAGING" => Array(
			"NAME" => GetMessage("BC_USE_ASC_PAGING"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "N",
			"DEFAULT" => "",
			"PARENT" => "VISUAL",
		),
		"TAGS_COUNT" => Array(
			"NAME" => GetMessage("BC_TAGS_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => 0,
			"PARENT" => "VISUAL",
		),
		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("BC_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"NAV_TEMPLATE" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("BB_NAV_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"NAME_TEMPLATE" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("BC_NAME_TEMPLATE"),
			"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "",
		),
		"SHOW_LOGIN" => Array(
			"NAME" => GetMessage("BC_SHOW_LOGIN"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" => "Y",
		),
		"SMILES_COUNT" => Array(
			"NAME" => GetMessage("BPC_SMILES_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => 1,
			"PARENT" => "VISUAL",
		),
		"IMAGE_MAX_WIDTH" => Array(
			"NAME" => GetMessage("BPC_IMAGE_MAX_WIDTH"),
			"TYPE" => "STRING",
			"DEFAULT" => 800,
			"PARENT" => "VISUAL",
		),
		"IMAGE_MAX_HEIGHT" => Array(
			"NAME" => GetMessage("BPC_IMAGE_MAX_HEIGHT"),
			"TYPE" => "STRING",
			"DEFAULT" => 0,
			"PARENT" => "VISUAL",
		),
		"EDITOR_RESIZABLE" => Array(
			"NAME" => GetMessage("BPC_EDITOR_RESIZABLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "VISUAL",
		),
		"EDITOR_CODE_DEFAULT" => Array(
			"NAME" => GetMessage("BPC_EDITOR_CODE_DEFAULT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "VISUAL",
		),
		"EDITOR_DEFAULT_HEIGHT" => Array(
			"NAME" => GetMessage("BPC_EDITOR_DEFAULT_HEIGHT"),
			"TYPE" => "STRING",
			"DEFAULT" => 300,
			"PARENT" => "VISUAL",
		),
		"COMMENT_EDITOR_RESIZABLE" => Array(
			"NAME" => GetMessage("BPC_COMMENT_EDITOR_RESIZABLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "VISUAL",
		),
		"COMMENT_EDITOR_CODE_DEFAULT" => Array(
			"NAME" => GetMessage("BPC_COMMENT_EDITOR_CODE_DEFAULT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "VISUAL",
		),
		"COMMENT_EDITOR_DEFAULT_HEIGHT" => Array(
			"NAME" => GetMessage("BPC_COMMENT_EDITOR_DEFAULT_HEIGHT"),
			"TYPE" => "STRING",
			"DEFAULT" => 200,
			"PARENT" => "VISUAL",
		),
		"COMMENT_ALLOW_VIDEO" => Array(
			"NAME" => GetMessage("BPC_COMMENT_ALLOW_VIDEO"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"NO_URL_IN_COMMENTS" => Array(
			"NAME" => GetMessage("BPC_NO_URL_IN_COMMENTS"),
			"TYPE" => "LIST",
			"VALUES" => Array(
				"" => GetMessage("BPC_NO_URL_IN_COMMENTS_N"),
				"A" => GetMessage("BPC_NO_URL_IN_COMMENTS_A"),
				"L" => GetMessage("BPC_NO_URL_IN_COMMENTS_L"),
			),
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
	),
);

foreach (array("COMMENT_EDITOR_CODE_DEFAULT", "COMMENT_EDITOR_RESIZABLE", "SMILES_COUNT", "IMAGE_MAX_HEIGHT") as $key)
	if (!array_key_exists($key, $arCurrentValues))
		unset($arComponentParameters["PARAMETERS"][$key]);

//Use symbolcode
$arComponentParameters["PARAMETERS"]["ALLOW_POST_CODE"] = array(
	"NAME" => GetMessage("BPC_ALLOW_POST_CODE"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
	"PARENT" => "ADDITIONAL_SETTINGS",
	"REFRESH" => "Y",
);

if($arCurrentValues["ALLOW_POST_CODE"] != "N")
{
	$arComponentParameters["PARAMETERS"]["USE_GOOGLE_CODE"] = array(
		"NAME" => GetMessage("BPE_USE_GOOGLE_CODE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);
}
if (!array_key_exists("IBLOCK_CATEGORIES", $arCurrentValues) && array_key_exists("IBLOCK_CATOGORIES", $arCurrentValues))
	$arCurrentValues["IBLOCK_CATEGORIES"] = $arCurrentValues["IBLOCK_CATOGORIES"];

//Rating
$arComponentParameters["PARAMETERS"]["SHOW_RATING"] = array(
	"NAME" => GetMessage("B_SHOW_RATING"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"REFRESH" => "Y",
	"PARENT" => "ADDITIONAL_SETTINGS",
);
if($arCurrentValues["SHOW_RATING"] == "Y")
{
	$arComponentParameters["PARAMETERS"]["RATING_TEMPLATE"] = array(
		"NAME" => GetMessage("IDEA_PARAM_RATING_TEMPLATE_TITLE"),
		"TYPE" => "LIST",
		"DEFAULT" => "standart",
		"REFRESH" => "N",
		"PARENT" => "ADDITIONAL_SETTINGS",
		"MULTIPLE" => "N",
		"VALUES" => Array(
			"standart" => GetMessage("IDEA_PARAM_RATING_TEMPLATE_STANDART"),
			"like" => GetMessage("IDEA_PARAM_RATING_TEMPLATE_LIKE"),
		),
	);
}

//Sonet Log
if(IsModuleInstalled('intranet'))
	$arComponentParameters["PARAMETERS"]["DISABLE_SONET_LOG"] = array(
		"NAME" => GetMessage("IDEA_DISABLE_SONET_LOG"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);
//Email
$arComponentParameters["PARAMETERS"]["DISABLE_EMAIL"] = array(
	"NAME" => GetMessage("IDEA_DISABLE_EMAIL"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"PARENT" => "ADDITIONAL_SETTINGS",
);

//RSS
$arComponentParameters["PARAMETERS"]["DISABLE_RSS"] = array(
	"NAME" => GetMessage("IDEA_DISABLE_RSS"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"REFRESH" => "Y",
	"PARENT" => "ADDITIONAL_SETTINGS",
);
if ($arCurrentValues["DISABLE_RSS"] != "Y")
{
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["post_rss"] = array(
		"NAME" => GetMessage("BC_SEF_PATH_POST_RSS"),
		"DEFAULT" => "rss/#type#/#post_id#/",
		"VARIABLES" => array("post_id", "type"),
	);
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["rss"] = array(
		"NAME" => GetMessage("BC_SEF_PATH_RSS"),
		"DEFAULT" => "rss/#type#/",
		"VARIABLES" => array("type"),
	);
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["rss_status"] = array(
		"NAME" => GetMessage("BC_SEF_PATH_RSS_STATUS"),
		"DEFAULT" => "rss/#type#/status/#status_code#/",
		"VARIABLES" => array("type", "status_code"),
	);
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["rss_category"] = array(
		"NAME" => GetMessage("BC_SEF_PATH_RSS_CATEGORY"),
		"DEFAULT" => "rss/#type#/category/#category#/",
		"VARIABLES" => array("type", "category"),
	);
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["rss_category_status"] = array(
		"NAME" => GetMessage("BC_SEF_PATH_RSS_CATEGORY_STATUS"),
		"DEFAULT" => "rss/#type#/category/#category#/status/#status_code#/",
		"VARIABLES" => array("type", "category", "status_code"),
	);
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["rss_user_ideas"] = array(
		"NAME" => GetMessage("BC_SEF_PATH_RSS_USER_IDEAS"),
		"DEFAULT" => "rss/#type#/user/#user_id#/idea/",
		"VARIABLES" => array("type", "user_id"),
	);
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["rss_user_ideas_status"] = array(
		"NAME" => GetMessage("BC_SEF_PATH_RSS_USER_IDEAS_STATUS"),
		"DEFAULT" => "rss/#type#/user/#user_id#/idea/status/#status_code#/",
		"VARIABLES" => array("type", "user_id", "status_code"),
	);
}
?>