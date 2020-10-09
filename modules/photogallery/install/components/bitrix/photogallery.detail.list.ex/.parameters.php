<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
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

$arUGroupsEx = Array();
$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
while($arUGroups = $dbUGroups -> Fetch())
	$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];

$res = unserialize(COption::GetOptionString("photogallery", "pictures"));
$arSights = array();
if (is_array($res))
	foreach ($res as $key => $val)
		$arSights[str_pad($key, 5, "_").$val["code"]] = $val["title"];

$arProperty_LNS = array();
$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>(isset($arCurrentValues["IBLOCK_ID"])?$arCurrentValues["IBLOCK_ID"]:$arCurrentValues["ID"])));
while ($arr=$rsProp->Fetch())
	$arProperty[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S")))
		$arProperty_LNS[$arr["CODE"]] = "[".$arr["CODE"]."] ".$arr["NAME"];

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y"),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y"),
		"BEHAVIOUR" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_BEHAVIOUR"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"SIMPLE" => GetMessage("IBLOCK_BEHAVIOUR_SIMPLE"),
				"USER" => GetMessage("IBLOCK_BEHAVIOUR_USER")),
			"DEFAULT" => "SIMPLE",
			"REFRESH" => "Y"),
		"SET_TITLE" => Array(),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
		)
	);

if ($arCurrentValues["BEHAVIOUR"] == "USER")
{
	$arComponentParameters["PARAMETERS"]["USER_ALIAS"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_USER_ALIAS"),
		"TYPE" => "STRING",
		"DEFAULT" => '={$_REQUEST["USER_ALIAS"]}');
}

/*		"PERMISSION" => array(),
*/
	$arComponentParameters["PARAMETERS"]["SECTION_ID"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_SECTION_ID"),
		"TYPE" => "STRING",
		"DEFAULT" => '={$_REQUEST["SECTION_ID"]}');

	$arComponentParameters["PARAMETERS"]["ELEMENT_LAST_TYPE"] = array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ELEMENT_LAST_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"none" => GetMessage("IBLOCK_ELEMENT_LAST_TYPE_NONE"),
				"count" => GetMessage("IBLOCK_ELEMENT_LAST_TYPE_COUNT"),
				"time" => GetMessage("IBLOCK_ELEMENT_LAST_TYPE_TIME"),
				"period" => GetMessage("IBLOCK_ELEMENT_LAST_TYPE_PERIOD"),
			),
			"DEFAULT" => "none",
			"REFRESH" => "Y");

if ($arCurrentValues["ELEMENT_LAST_TYPE"] == "count")
{
	$arComponentParameters["PARAMETERS"]["ELEMENTS_LAST_COUNT"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_ELEMENTS_LAST_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => '30');
}
elseif ($arCurrentValues["ELEMENT_LAST_TYPE"] == "time")
{
	$arComponentParameters["PARAMETERS"]["ELEMENTS_LAST_TIME"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_ELEMENTS_LAST_TIME"),
		"TYPE" => "STRING",
		"DEFAULT" => '30');
}
elseif ($arCurrentValues["ELEMENT_LAST_TYPE"] == "period")
{
	$arComponentParameters["PARAMETERS"]["ELEMENTS_LAST_TIME_FROM"] = array(
		"PARENT" => "BASE",
		"NAME" => str_replace("#FORMAT_DATETIME#", FORMAT_DATETIME, GetMessage("IBLOCK_ELEMENTS_LAST_TIME_FROM")),
		"TYPE" => "STRING",
		"DEFAULT" => '');
	$arComponentParameters["PARAMETERS"]["ELEMENTS_LAST_TIME_TO"] = array(
		"PARENT" => "BASE",
		"NAME" => str_replace("#FORMAT_DATETIME#", FORMAT_DATETIME, GetMessage("IBLOCK_ELEMENTS_LAST_TIME_TO")),
		"TYPE" => "STRING",
		"DEFAULT" => '');
}

$arComponentParameters["PARAMETERS"]["ELEMENT_SORT_FIELD"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD"),
	"TYPE" => "LIST",
	"VALUES" => array(
		"SHOW_COUNTER" => GetMessage("IBLOCK_SORT_SHOWS"),
		"SORT" => GetMessage("IBLOCK_SORT_SORT"),
		"TIMESTAMP_X" => GetMessage("IBLOCK_SORT_TIMESTAMP"),
		"NAME" => GetMessage("IBLOCK_SORT_NAME"),
		"ID" => $arCurrentValues["DRAG_SORT"] == "N" ? GetMessage("IBLOCK_SORT_ID") : GetMessage("IBLOCK_SORT_ID_SORTED"),
		"PROPERTY_RATING" => GetMessage("IBLOCK_SORT_RATING"),
		"PROPERTY_FORUM_MESSAGE_CNT" => GetMessage("IBLOCK_SORT_COMMENTS_FORUM"),
		"PROPERTY_BLOG_COMMENTS_CNT" => GetMessage("IBLOCK_SORT_COMMENTS_BLOG")
	),
	"ADDITIONAL_VALUES" => "Y",
	"DEFAULT" => "SORT");
$arComponentParameters["PARAMETERS"]["ELEMENT_SORT_ORDER"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
	"TYPE" => "LIST",
	"VALUES" => array(
		"asc" => GetMessage("IBLOCK_SORT_ASC"),
		"desc" => GetMessage("IBLOCK_SORT_DESC")),
	"DEFAULT" => "asc");
$arComponentParameters["PARAMETERS"]["ELEMENT_SORT_FIELD1"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD1"),
	"TYPE" => "LIST",
	"VALUES" => array(
		"SHOW_COUNTER" => GetMessage("IBLOCK_SORT_SHOWS"),
		"SORT" => GetMessage("IBLOCK_SORT_SORT"),
		"TIMESTAMP_X" => GetMessage("IBLOCK_SORT_TIMESTAMP"),
		"NAME" => GetMessage("IBLOCK_SORT_NAME"),
		"ID" => GetMessage("IBLOCK_SORT_ID"),
		"PROPERTY_RATING" => GetMessage("IBLOCK_SORT_RATING"),
		"PROPERTY_FORUM_MESSAGE_CNT" => GetMessage("IBLOCK_SORT_COMMENTS_FORUM"),
		"PROPERTY_BLOG_COMMENTS_CNT" => GetMessage("IBLOCK_SORT_COMMENTS_BLOG")),
	"ADDITIONAL_VALUES" => "Y",
	"DEFAULT" => ""
);
$arComponentParameters["PARAMETERS"]["ELEMENT_SORT_ORDER1"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
	"TYPE" => "LIST",
	"VALUES" => array(
		"asc" => GetMessage("IBLOCK_SORT_ASC"),
		"desc" => GetMessage("IBLOCK_SORT_DESC")),
	"DEFAULT" => "asc");

$arComponentParameters["PARAMETERS"]["DRAG_SORT"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("P_DRAG_SORT"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
	"REFRESH" => "Y"
);
/*$arComponentParameters["PARAMETERS"]["ELEMENT_FILTER"] = array();
/*$arComponentParameters["PARAMETERS"]["ELEMENT_SELECT_FIELD"] = array();
*/

$arComponentParameters["PARAMETERS"]["PROPERTY_CODE"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("T_IBLOCK_PROPERTY"),
	"TYPE" => "LIST",
	"MULTIPLE" => "Y",
	"VALUES" => $arProperty_LNS,
	"ADDITIONAL_VALUES" => "Y");

if($arCurrentValues["BEHAVIOUR"] == "USER")
{
	$arComponentParameters["PARAMETERS"]["GALLERY_URL"] = array(
		"PARENT" => "URL_TEMPLATES",
		"NAME" => GetMessage("IBLOCK_GALLERY_URL"),
		"TYPE" => "STRING",
		"DEFAULT" => "gallery.php?USER_ALIAS=#USER_ALIAS#"
	);
}
$arComponentParameters["PARAMETERS"]["DETAIL_URL"] = array(
	"PARENT" => "URL_TEMPLATES",
	"NAME" => GetMessage("IBLOCK_DETAIL_URL"),
	"TYPE" => "STRING",
	"DEFAULT" => "detail.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
		"SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#"
);
$arComponentParameters["PARAMETERS"]["DETAIL_SLIDE_SHOW_URL"] = array(
		"PARENT" => "URL_TEMPLATES",
		"NAME" => GetMessage("IBLOCK_DETAIL_SLIDE_SHOW_URL"),
		"TYPE" => "STRING",
		"DEFAULT" => "slide_show.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ?"USER_ALIAS=#USER_ALIAS#" : "")."SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#"
	);


if (IsModuleInstalled("blog") || IsModuleInstalled("forum"))
{
	$arComponentParameters["GROUPS"]["REVIEW_SETTINGS"] = array("NAME" => GetMessage("T_IBLOCK_DESC_REVIEW_SETTINGS"), "SORT" => 400);

	$arComponentParameters["PARAMETERS"]["USE_COMMENTS"] = array(
		"PARENT" => "REVIEW_SETTINGS",
		"NAME" => GetMessage("T_IBLOCK_DESC_USE_COMMENTS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y");

	if ($arCurrentValues["USE_COMMENTS"]=="Y")
	{
		$arr = array();
		$default = "";

		if (IsModuleInstalled("blog"))
		{
			$arr["blog"] = GetMessage("P_COMMENTS_TYPE_BLOG");
			$default = "blog";
		}
		if (IsModuleInstalled("forum"))
		{
			$arr["forum"] = GetMessage("P_COMMENTS_TYPE_FORUM");
			$default = "forum";
		}

		$arComponentParameters["PARAMETERS"]["COMMENTS_TYPE"] = Array(
			"PARENT" => "REVIEW_SETTINGS",
			"NAME" => GetMessage("P_COMMENTS_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arr,
			"DEFAULT" => $default,
			"REFRESH" => "Y");

		$arCurrentValues["COMMENTS_TYPE"] = ($arCurrentValues["COMMENTS_TYPE"] == "forum" || $arCurrentValues["COMMENTS_TYPE"] == "blog" ? $arCurrentValues["COMMENTS_TYPE"] : $default);

		if (IsModuleInstalled("blog") && $arCurrentValues["COMMENTS_TYPE"]=="blog")
		{
			$arBlogs = array();
			if(CModule::IncludeModule("blog"))
			{
				$rsBlog = CBlog::GetList();
				while($arBlog=$rsBlog->Fetch())
				{
					$arBlogs[$arBlog["URL"]] = $arBlog["NAME"];
					$url = $arBlog["URL"];
				}
			}
			$arComponentParameters["PARAMETERS"]["BLOG_URL"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_BLOG_URL"),
				"TYPE" => "LIST",
				"VALUES" => $arBlogs,
				"DEFAULT" => $url);
			$arComponentParameters["PARAMETERS"]["COMMENTS_COUNT"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_COMMENTS_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 25,
				"HIDDEN" => $hidden
			);
			$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("P_PATH_TO_BLOG"),
				"TYPE" => "STRING",
				"DEFAULT" => ""
			);
			$arComponentParameters["PARAMETERS"]["PATH_TO_SMILE"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_PATH_TO_SMILE"),
				"TYPE" => "STRING",
				"DEFAULT" => "/bitrix/images/blog/smile/",
				"HIDDEN" => $hidden
			);
		}
		elseif (IsModuleInstalled("forum") && $arCurrentValues["COMMENTS_TYPE"]=="forum")
		{
			$arForum = array();
			$fid = 0;
			if (CModule::IncludeModule("forum"))
			{
				$db_res = CForumNew::GetList(array(), array());
				if ($db_res && ($res = $db_res->GetNext()))
				{
					do
					{
						$arForum[intval($res["ID"])] = $res["NAME"];
						$fid = intval($res["ID"]);
					}while ($res = $db_res->GetNext());
				}
			}
			$arComponentParameters["PARAMETERS"]["FORUM_ID"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_FORUM_ID"),
				"TYPE" => "LIST",
				"VALUES" => $arForum,
				"DEFAULT" => $fid);
			$arComponentParameters["PARAMETERS"]["COMMENTS_COUNT"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_COMMENTS_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"),
				"HIDDEN" => $hidden
			);
			$arComponentParameters["PARAMETERS"]["URL_TEMPLATES_READ"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_READ_TEMPLATE"),
				"TYPE" => "STRING",
				"DEFAULT" => "",
				"HIDDEN" => $hidden
			);
			$arComponentParameters["PARAMETERS"]["URL_TEMPLATES_PROFILE_VIEW"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
				"TYPE" => "STRING",
				"DEFAULT" => "",
				"HIDDEN" => $hidden
			);
			$arComponentParameters["PARAMETERS"]["PATH_TO_SMILE"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_PATH_TO_SMILE"),
				"TYPE" => "STRING",
				"DEFAULT" => "/bitrix/images/forum/smile/",
				"HIDDEN" => $hidden
			);
			$arComponentParameters["PARAMETERS"]["USE_CAPTCHA"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_USE_CAPTCHA"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"HIDDEN" => $hidden
			);
			$arComponentParameters["PARAMETERS"]["PREORDER"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_PREORDER"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"HIDDEN" => $hidden
			);
			$arComponentParameters["PARAMETERS"]["POST_FIRST_MESSAGE"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_POST_FIRST_MESSAGE"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"HIDDEN" => $hidden
			);
		}

		$arComponentParameters["PARAMETERS"]["NAME_TEMPLATE"] = array(
			"PARENT" => "REVIEW_SETTINGS",
			"TYPE" => "LIST",
			"NAME" => GetMessage("P_SONET_NAME_TEMPLATE"),
			"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => ""
		);
	}
}


if (IsModuleInstalled("search"))
{
	$arComponentParameters["PARAMETERS"]["SEARCH_URL"] = array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_SEARCH_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "search.php");
}

$arComponentParameters["PARAMETERS"]["USE_PERMISSIONS"] = array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("T_IBLOCK_DESC_USE_PERMISSIONS"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N");
$arComponentParameters["PARAMETERS"]["GROUP_PERMISSIONS"] = array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("T_IBLOCK_DESC_GROUP_PERMISSIONS"),
	"TYPE" => "LIST",
	"VALUES" => $arUGroupsEx,
	"DEFAULT" => Array(1),
	"MULTIPLE" => "Y");

$arComponentParameters["PARAMETERS"]["USE_DESC_PAGE"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("T_USE_DESC_PAGE"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y");
$arComponentParameters["PARAMETERS"]["PAGE_ELEMENTS"] = array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("IBLOCK_PAGE_ELEMENTS"),
	"TYPE" => "STRING",
	"DEFAULT" => '50');
$arComponentParameters["PARAMETERS"]["DATE_TIME_FORMAT"] = CIBlockParameters::GetDateFormat(GetMessage("T_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS");
$arComponentParameters["PARAMETERS"]["SET_STATUS_404"] = array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("P_SET_STATUS_404"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N");

$arComponentParameters["PARAMETERS"]["ADDITIONAL_SIGHTS"] = array(
	"PARENT" => "PHOTO_SETTINGS",
	"NAME" => GetMessage("P_ADDITIONAL_SIGHTS"),
	"TYPE" => "LIST",
	"VALUES" => $arSights,
	"DEFAULT" => array(),
	"MULTIPLE" => "Y"
);
$arComponentParameters["PARAMETERS"]["PICTURES_SIGHT"] = array(
	"PARENT" => "PHOTO_SETTINGS",
	"NAME" => GetMessage("P_PICTURES_SIGHT"),
	"TYPE" => "LIST",
	"VALUES" => array_merge(array("" => "...", "detail" => GetMessage("P_DETAIL_PICTURES_SIGHT"), "real" => GetMessage("P_REAL_PICTURES_SIGHT")), $arSights),
	"DEFAULT" => ""
);
if($arCurrentValues["BEHAVIOUR"] == "USER")
{
	$arComponentParameters["PARAMETERS"]["GALLERY_SIZE"] = array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("P_GALLERY_SIZE"),
		"TYPE" => "STRING",
		"DEFAULT" => "");
}


$arComponentParameters["PARAMETERS"]["PATH_TO_USER"] = array(
	"PARENT" => "URL_TEMPLATES",
	"NAME" => GetMessage("P_PATH_TO_USER"),
	"DEFAULT" => "/company/personal/user/#USER_ID#",
);

$arComponentParameters["PARAMETERS"]["NAME_TEMPLATE"] = array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"TYPE" => "LIST",
	"NAME" => GetMessage("P_NAME_TEMPLATE"),
	"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
	"MULTIPLE" => "N",
	"ADDITIONAL_VALUES" => "Y",
	"DEFAULT" => "",
);

$arComponentParameters["PARAMETERS"]["SHOW_LOGIN"] = array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("P_SHOW_LOGIN"),
	"TYPE" => "CHECKBOX",
	"VALUE" => "Y",
	"DEFAULT" =>"Y",
);
?>