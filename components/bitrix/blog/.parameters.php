<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("blog"))
	return false;

$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
$userProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$userProp[$val["FIELD_NAME"]] = ($val["EDIT_FORM_LABEL"] <> '' ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}

$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_BLOG", 0, LANGUAGE_ID);
$blogProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$blogProp[$val["FIELD_NAME"]] = ($val["EDIT_FORM_LABEL"] <> '' ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}

$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", 0, LANGUAGE_ID);
$postProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$postProp[$val["FIELD_NAME"]] = ($val["EDIT_FORM_LABEL"] <> '' ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}
unset($postProp["UF_BLOG_POST_DOC"]);
unset($postProp["UF_BLOG_POST_FILE"]);

$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_COMMENT", 0, LANGUAGE_ID);
$commentProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$commentProp[$val["FIELD_NAME"]] = ($val["EDIT_FORM_LABEL"] <> '' ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}
unset($commentProp["UF_BLOG_COMMENT_DOC"]);
unset($commentProp["UF_BLOG_COMMENT_FILE"]);

$arComponentParameters = array(
	"GROUPS" => Array(
		"COMMENT" => array("NAME" => GetMessage("BLOG_COMMENT_SETTINGS")),
		),
	"PARAMETERS" => array(
		"USER_CONSENT" => array(),
		"VARIABLE_ALIASES" => Array(
			"blog" => Array(
					"NAME" => GetMessage("BC_BLOG_VAR"),
					"DEFAULT" => "blog",
					),
			"post_id" => Array(
					"NAME" => GetMessage("BC_POST_VAR"),
					"DEFAULT" => "id",
					),
			"user_id" => Array(
					"NAME" => GetMessage("BC_USER_VAR"),
					"DEFAULT" => "id",
					),
			"page" => Array(
					"NAME" => GetMessage("BC_PAGE_VAR"),
					"DEFAULT" => "page",
					),
			"group_id" => Array(
					"NAME" => GetMessage("BC_GROUP_VAR"),
					"DEFAULT" => "id",
					),
			),
		"SEF_MODE" => Array(
			"index	" => array(
				"NAME" => GetMessage("BC_SEF_PATH_INDEX"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array(),
			),
			"group" => array(
				"NAME" => GetMessage("BC_SEF_PATH_GROUP"),
				"DEFAULT" => "group/#group_id#/",
				"VARIABLES" => array("group_id"),
			),
			"blog" => array(
				"NAME" => GetMessage("BC_SEF_PATH_BLOG"),
				"DEFAULT" => "#blog#/",
				"VARIABLES" => array("blog"),
			),
			"user" => array(
				"NAME" => GetMessage("BC_SEF_PATH_USER"),
				"DEFAULT" => "user/#user_id#/",
				"VARIABLES" => array("user_id"),
			),
			"user_friends" => array(
				"NAME" => GetMessage("BC_SEF_PATH_USER_FRIENDS"),
				"DEFAULT" => "friends/#user_id#/",
				"VARIABLES" => array("user_id"),
			),
			"search" => array(
				"NAME" => GetMessage("BC_SEF_PATH_SEARCH"),
				"DEFAULT" => "search.php",
				"VARIABLES" => array(),
			),
			"user_settings" => array(
				"NAME" => GetMessage("BC_SEF_PATH_USER_SETTINGS"),
				"DEFAULT" => "#blog#/user_settings.php",
				"VARIABLES" => array("blog"),
			),
			"user_settings_edit" => array(
				"NAME" => GetMessage("BC_SEF_PATH_USER_SETTINGS_EDIT"),
				"DEFAULT" => "#blog#/user_settings_edit.php?id=#user_id#",
				"VARIABLES" => array("blog", "user_id"),
			),
			"group_edit" => array(
				"NAME" => GetMessage("BC_SEF_PATH_GROUP_EDIT"),
				"DEFAULT" => "#blog#/group_edit.php",
				"VARIABLES" => array("blog"),
			),
			"blog_edit" => array(
				"NAME" => GetMessage("BC_SEF_PATH_BLOG_EDIT"),
				"DEFAULT" => "#blog#/blog_edit.php",
				"VARIABLES" => array("blog"),
			),
			"category_edit" => array(
				"NAME" => GetMessage("BC_SEF_PATH_CATEGORY_EDIT"),
				"DEFAULT" => "#blog#/category_edit.php",
				"VARIABLES" => array("blog"),
			),
			"post_edit" => array(
				"NAME" => GetMessage("BC_SEF_PATH_POST_EDIT"),
				"DEFAULT" => "#blog#/post_edit.php?id=#post_id#",
				"VARIABLES" => array("blog", "post_id"),
			),
			"draft" => array(
				"NAME" => GetMessage("BC_SEF_PATH_DRAFT"),
				"DEFAULT" => "#blog#/draft.php",
				"VARIABLES" => array("blog"),
			),
			"moderation" => array(
				"NAME" => GetMessage("BC_SEF_PATH_MODERATION"),
				"DEFAULT" => "#blog#/moderation.php",
				"VARIABLES" => array("blog"),
			),
			"trackback" => array(
				"NAME" => GetMessage("BC_SEF_PATH_TRACKBACK"),
				"DEFAULT" => "={POST_FORM_ACTION_URI.'&blog=#blog#&id=#post_id#&page=trackback'}",
				"VARIABLES" => array("blog", "post_id"),
			),
			"post" => array(
				"NAME" => GetMessage("BC_SEF_PATH_POST"),
				"DEFAULT" => "#blog#/#post_id#/",
				"VARIABLES" => array("blog", "post_id"),
			),
			"post_rss" => array(
				"NAME" => GetMessage("BC_SEF_PATH_POST_RSS"),
				"DEFAULT" => "#blog#/rss/#type#/#post_id#",
				"VARIABLES" => array("blog", "post_id", "type"),
			),
			"rss" => array(
				"NAME" => GetMessage("BC_SEF_PATH_RSS"),
				"DEFAULT" => "#blog#/rss/#type#",
				"VARIABLES" => array("blog", "type"),
			),
			"rss_all" => array(
				"NAME" => GetMessage("BC_SEF_PATH_RSS_ALL"),
				"DEFAULT" => "rss/#type#/#group_id#",
				"VARIABLES" => array("type", "group_id"),
			),
		),
		"PATH_TO_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("BC_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/bitrix/images/blog/smile/",
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
		"SET_TITLE" => Array(),
		"CACHE_TIME_LONG"	=>	array(
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
			"DEFAULT" =>"Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"SEO_USE" => Array(
			"NAME" => GetMessage("BC_SEO_USE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => array("N" => GetMessage("BC_SEO_N"), "Y" => GetMessage("BC_SEO_Y"), "D" => GetMessage("BC_SEO_D")),
			"DEFAULT" =>"Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"MESSAGE_COUNT" => Array(
				"NAME" => GetMessage("BC_MESSAGE_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 25,
				"PARENT" => "VISUAL",
			),		
		"PERIOD_DAYS" => Array(
				"NAME" => GetMessage("BC_PERIOD_DAYS"),
				"TYPE" => "STRING",
				"DEFAULT" => 30,
				"PARENT" => "VISUAL",
			),
		"MESSAGE_COUNT_MAIN" => Array(
				"NAME" => GetMessage("BC_MESSAGE_COUNT_MAIN"),
				"TYPE" => "STRING",
				"DEFAULT" => "6",
				"PARENT" => "VISUAL",
			),
		"BLOG_COUNT_MAIN" => Array(
				"NAME" => GetMessage("BC_BLOG_COUNT_MAIN"),
				"TYPE" => "STRING",
				"DEFAULT" => "6",
				"PARENT" => "VISUAL",
			),
		"COMMENTS_COUNT" => Array(
				"NAME" => GetMessage("BC_COMMENTS_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 25,
				"PARENT" => "COMMENT",
			),
		"COMMENTS_LIST_VIEW" => Array(
			"NAME" => GetMessage("BC_COMMENTS_LIST_VIEW"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			//Match comments view: list or tree. For existing components - use tree, for new - use list
			"DEFAULT" => (isset($arCurrentValues) && $arCurrentValues["COMMENTS_LIST_VIEW"] === null) ? "N" : "Y",
			"PARENT" => "COMMENT",
			"REFRESH" => "Y",
		),
		"AJAX_PAGINATION" => array(
			"NAME" => GetMessage("BPC_AJAX_PAGINATION"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" => "N",
			"PARENT" => "COMMENT",
			"HIDDEN" => ($arCurrentValues["COMMENTS_LIST_VIEW"] == "Y") ? "Y" : "N",
		),
		"MESSAGE_LENGTH" => Array(
				"NAME" => GetMessage("BC_MESSAGE_LENTH"),
				"TYPE" => "STRING",
				"DEFAULT" => "100",
				"PARENT" => "VISUAL",
			),
		"BLOG_COUNT" => Array(
				"NAME" => GetMessage("BC_BLOG_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 20,
				"PARENT" => "VISUAL",
			),
		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("BC_DATE_TIME_FORMAT"), "VISUAL"),
		"NAV_TEMPLATE" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("BB_NAV_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"USER_PROPERTY"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("USER_PROPERTY"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),	
		),
		"BLOG_PROPERTY"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("BLOG_PROPERTY"),
			"TYPE" => "LIST",
			"VALUES" => $blogProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),	
		),
		"BLOG_PROPERTY_LIST"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("BLOG_PROPERTY_LIST"),
			"TYPE" => "LIST",
			"VALUES" => $blogProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),	
		),
		"POST_PROPERTY"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("POST_PROPERTY"),
			"TYPE" => "LIST",
			"VALUES" => $postProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),	
		),
		"POST_PROPERTY_LIST"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("POST_PROPERTY_LIST"),
			"TYPE" => "LIST",
			"VALUES" => $postProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),	
		),
		"COMMENT_PROPERTY"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("COMMENT_PROPERTY"),
			"TYPE" => "LIST",
			"VALUES" => $commentProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),	
		),
		"USE_ASC_PAGING" => Array(
			"NAME" => GetMessage("BC_USE_ASC_PAGING"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "N",
			"DEFAULT" =>"",
			"PARENT" => "ADDITIONAL_SETTINGS",
			),
		"NOT_USE_COMMENT_TITLE" => Array(
			"NAME" => GetMessage("BC_NOT_USE_COMMENT_TITLE"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"",
			"PARENT" => "COMMENT",
			),

		"SHOW_RATING" => array(
			"NAME" => GetMessage("SHOW_RATING"),
			"TYPE" => "LIST",
			"VALUES" => Array(
				"" => GetMessage("SHOW_RATING_CONFIG"),
				"Y" => GetMessage("MAIN_YES"),
				"N" => GetMessage("MAIN_NO"),
			),
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"RATING_TYPE" => Array(
			"NAME" => GetMessage("RATING_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => Array(
				"" => GetMessage("RATING_TYPE_CONFIG"),
				"like" => GetMessage("RATING_TYPE_LIKE_TEXT"),
				"like_graphic" => GetMessage("RATING_TYPE_LIKE_GRAPHIC"),
				"standart_text" => GetMessage("RATING_TYPE_STANDART_TEXT"),
				"standart" => GetMessage("RATING_TYPE_STANDART_GRAPHIC"),
			),
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),	
		"SMILES_COUNT" => Array(
				"NAME" => GetMessage("BPC_SMILES_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 4,
				"PARENT" => "VISUAL",
			),
		"IMAGE_MAX_WIDTH" => Array(
				"NAME" => GetMessage("BPC_IMAGE_MAX_WIDTH").' ('.GetMessage("BPC_IMAGE_MAX_SIZES_TEXT").' '.COption::GetOptionString('blog', 'image_max_width').')',
				"TYPE" => "STRING",
				"DEFAULT" => COption::GetOptionString('blog', 'image_max_width'),
				"PARENT" => "VISUAL",
			),		
		"IMAGE_MAX_HEIGHT" => Array(
				"NAME" => GetMessage("BPC_IMAGE_MAX_HEIGHT").' ('.GetMessage("BPC_IMAGE_MAX_SIZES_TEXT").' '.COption::GetOptionString('blog', 'image_max_height').')',
				"TYPE" => "STRING",
				"DEFAULT" => COption::GetOptionString('blog', 'image_max_height'),
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
		"COMMENT_EDITOR_RESIZABLE" => Array(
				"NAME" => GetMessage("BPC_COMMENT_EDITOR_RESIZABLE"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "Y",
				"PARENT" => "COMMENT",
			),		
		"COMMENT_EDITOR_DEFAULT_HEIGHT" => Array(
				"NAME" => GetMessage("BPC_COMMENT_EDITOR_DEFAULT_HEIGHT"),
				"TYPE" => "STRING",
				"DEFAULT" => 200,
				"PARENT" => "COMMENT",
			),
		"COMMENT_EDITOR_CODE_DEFAULT" => Array(
				"NAME" => GetMessage("BPC_COMMENT_EDITOR_CODE_DEFAULT"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"PARENT" => "COMMENT",
			),
		"COMMENT_ALLOW_VIDEO" => Array(
				"NAME" => GetMessage("BPC_COMMENT_ALLOW_VIDEO"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "Y",
				"PARENT" => "COMMENT",
			),
		"COMMENT_ALLOW_IMAGE_UPLOAD" => Array(
				"NAME" => GetMessage("BPC_ALLOW_IMAGE_UPLOAD"),
				"TYPE" => "LIST",
				"VALUES" => Array(
						"A" => GetMessage("BPC_ALLOW_IMAGE_UPLOAD_A"),
						"R" => GetMessage("BPC_ALLOW_IMAGE_UPLOAD_R"),
						"N" => GetMessage("BPC_ALLOW_IMAGE_UPLOAD_N"),
					),
				"MULTIPLE" => "N",
				"DEFAULT" => "A",
				"PARENT" => "COMMENT",
			),
		"SHOW_SPAM" => Array(
				"NAME" => GetMessage("BPC_SHOW_SPAM"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N",
				"PARENT" => "COMMENT",
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
				"PARENT" => "COMMENT",
			),
		"NO_URL_IN_COMMENTS_AUTHORITY" => Array(
				"NAME" => GetMessage("BPC_NO_URL_IN_COMMENTS_AUTHORITY"),
				"TYPE" => "STRING",
				"DEFAULT" => "",
				"PARENT" => "COMMENT",
			),
		"ALLOW_POST_CODE" => Array(
				"NAME" => GetMessage("BPC_ALLOW_POST_CODE"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "Y",
				"PARENT" => "ADDITIONAL_SETTINGS",
				"REFRESH" => "Y",
			),
	),
); 

if ($arCurrentValues["ALLOW_POST_CODE"] != "N")
{
	$arComponentParameters["PARAMETERS"]["USE_GOOGLE_CODE"] = array(
		"NAME" => GetMessage("BPE_USE_GOOGLE_CODE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);
}

?>