<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("blog"))
	return false;
if(!CModule::IncludeModule("iblock"))
		return false;
if(!CModule::IncludeModule("idea"))
	return false;
$arCurrentValues = (is_array($arCurrentValues) ? $arCurrentValues : array());
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
			//main parameters
			"BLOG_URL"=>array(
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

			"PATH_IDEA_INDEX" => Array(
				"NAME" => GetMessage("IDEA_PARAM_PATH_IDEA_INDEX"),
				"TYPE" => "STRING",
				"DEFAULT" => '/services/idea/',
				"PARENT" => "BASE",
			),
			"PATH_IDEA_POST" => Array(
				"NAME" => GetMessage("IDEA_PARAM_PATH_IDEA_POST"),
				"TYPE" => "STRING",
				"DEFAULT" => '/services/idea/#post_id#/',
				"PARENT" => "BASE",
			),
			"BUTTON_COLOR" => Array(
				"NAME" => GetMessage("IDEA_PARAM_BUTTON_COLOR"),
				"TYPE" => "STRING",
				"DEFAULT" => '#3EA822',
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
			"CATEGORIES_CNT" => Array(
					"NAME" => GetMessage("CATEGORIES_CNT"),
					"TYPE" => "STRING",
					"DEFAULT" => 4,
					"PARENT" => "VISUAL",
			),
			"LIST_MESSAGE_COUNT" => Array(
					"NAME" => GetMessage("BC_MESSAGE_COUNT"),
					"TYPE" => "STRING",
					"DEFAULT" => 8,
					"PARENT" => "VISUAL",
			),
			"AUTH_TEMPLATE" => array(
				"PARENT" => "BASE",
				"NAME" => GetMessage("IDEA_PARAM_AUTH_TEMPLATE"),
				"TYPE" => "STRING",
				"DEFAULT" => "",
			),
			"FORGOT_PASSWORD_URL" => Array(
				"NAME" => GetMessage("IDEA_PARAM_FORGOT_PASSWORD_URL"),
				"TYPE" => "STRING",
				"DEFAULT" => '',
				"PARENT" => "BASE",
			),
			"REGISTER_URL" => Array(
				"NAME" => GetMessage("IDEA_PARAM_REGISTER_URL"),
				"TYPE" => "STRING",
				"DEFAULT" => '',
				"PARENT" => "BASE",
			),

			"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
	),
); 

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
if (!array_key_exists("IBLOCK_CATEGORIES", $arCurrentValues) && array_key_exists("IBLOCK_CATOGORIES", $arCurrentValues))
	$arCurrentValues["IBLOCK_CATEGORIES"] = $arCurrentValues["IBLOCK_CATOGORIES"];
if (!array_key_exists("CATEGORIES_CNT", $arCurrentValues) && array_key_exists("CATOGORIES_CNT", $arCurrentValues))
	$arCurrentValues["CATEGORIES_CNT"] = $arCurrentValues["CATOGORIES_CNT"];

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
?>