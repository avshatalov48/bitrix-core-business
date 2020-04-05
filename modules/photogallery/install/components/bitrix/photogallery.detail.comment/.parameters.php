<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;
elseif (!IsModuleInstalled("blog") && !IsModuleInstalled("forum"))
	return;

$arIBlockType = array();
$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
while ($arr=$rsIBlockType->Fetch())
{
	if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
	{
		$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["~NAME"];
	}
}

$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arr = array();
if (IsModuleInstalled("blog"))
	$arr["blog"] = GetMessage("P_COMMENTS_TYPE_BLOG");
if (IsModuleInstalled("forum"))
	$arr["forum"] = GetMessage("P_COMMENTS_TYPE_FORUM");

$arComponentParameters = array(
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		),
		"ELEMENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}',
		),
		"DETAIL_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_DETAIL_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "detail.php?SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		),
		"COMMENTS_TYPE" => array(
			"PARENT" => "REVIEW_SETTINGS",
			"NAME" => GetMessage("P_COMMENTS_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arr,
			"DEFAULT" => "blog",
			"REFRESH" => "Y"), 
		"COMMENTS_COUNT" => array(
			"PARENT" => "REVIEW_SETTINGS",
			"NAME" => GetMessage("F_COMMENTS_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => 25), 
		
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
	),
);

$arCurrentValues["COMMENTS_TYPE"] = ($arCurrentValues["COMMENTS_TYPE"] == "forum" ? "forum" : "blog");
if ($arCurrentValues["COMMENTS_TYPE"] == "blog" && IsModuleInstalled("blog"))
{
	$arBlogs = array();
	if(CModule::IncludeModule("blog"))
	{
		$rsBlog = CBlog::GetList();
		while($arBlog=$rsBlog->Fetch())
			$arBlogs[$arBlog["URL"]] = $arBlog["NAME"];
	}
	$arComponentParameters["PARAMETERS"]["BLOG_URL"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("F_BLOG_URL"),
		"TYPE" => "LIST",
		"VALUES" => $arBlogs);
	$arComponentParameters["PARAMETERS"]["PATH_TO_SMILE"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("F_PATH_TO_SMILE"),
		"TYPE" => "STRING",
		"DEFAULT" => "/bitrix/images/blog/smile/");
	$arComponentParameters["PARAMETERS"]["PATH_TO_USER"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("P_PATH_TO_USER"),
		"TYPE" => "STRING",
		"DEFAULT" => "");
	$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("P_PATH_TO_BLOG"),
		"TYPE" => "STRING",
		"DEFAULT" => "");
}
elseif ($arCurrentValues["COMMENTS_TYPE"] == "forum" && IsModuleInstalled("forum"))
{
	$arForum = array();
	if (CModule::IncludeModule("forum"))
	{
	$db_res = CForumNew::GetList(array(), array());
	if ($db_res && ($res = $db_res->GetNext()))
	{
		do 
		{
			$arForum[intVal($res["ID"])] = $res["NAME"];
		}while ($res = $db_res->GetNext());
	}
	}
	$arComponentParameters["PARAMETERS"]["FORUM_ID"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("F_FORUM_ID"),
		"TYPE" => "LIST",
		"VALUES" => $arForum);
	$arComponentParameters["PARAMETERS"]["PATH_TO_SMILE"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("F_PATH_TO_SMILE"),
		"TYPE" => "STRING",
		"DEFAULT" => "/bitrix/images/forum/smile/");
	$arComponentParameters["PARAMETERS"]["URL_TEMPLATES_READ"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("F_READ_TEMPLATE"),
		"TYPE" => "STRING",
		"DEFAULT" => "");
	$arComponentParameters["PARAMETERS"]["USE_CAPTCHA"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("F_USE_CAPTCHA"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "Y");
	$arComponentParameters["PARAMETERS"]["PREORDER"] = Array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("F_PREORDER"),
		"TYPE" => "CHECKBOX",
		"MULTIPLE" => "N",
		"DEFAULT" => "Y");
}
?>