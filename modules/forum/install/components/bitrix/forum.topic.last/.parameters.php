<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;

$arComponentParameters = Array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
		"TOPIC_POST_MESSAGE_SETTINGS" => array(
			"NAME" => GetMessage("F_TOPIC_POST_MESSAGE_SETTINGS"),
		)
	),

	"PARAMETERS" => Array(
		"FID" => CForumParameters::GetForumsMultiSelect(GetMessage("F_DEFAULT_FID"), "BASE"),
		"SHOW_FORUM_ANOTHER_SITE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_SHOW_FORUM_ANOTHER_SITE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"SORT_BY" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_SORTING_ORD"),
			"TYPE" => "LIST",
			"DEFAULT" => "LAST_POST_DATE",
			"VALUES" => array(
				"TITLE" => GetMessage("F_SHOW_TITLE"),
				"USER_START_NAME" => GetMessage("F_SHOW_USER_START_NAME"),
				"POSTS" => GetMessage("F_SHOW_POSTS"),
				"VIEWS" => GetMessage("F_SHOW_VIEWS"),
				"LAST_POST_DATE" => GetMessage("F_SHOW_LAST_POST_DATE"),
				"START_DATE" => GetMessage("F_SHOW_START_DATE"),
				"ID" => GetMessage("F_SHOW_ID")
			)
		),
		"SORT_ORDER" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_SORTING_BY"),
			"TYPE" => "LIST",
			"DEFAULT" => "DESC",
			"VALUES" =>  Array("ASC"=>GetMessage("F_DESC_ASC"), "DESC"=>GetMessage("F_DESC_DESC"))),
		"SORT_BY_SORT_FIRST" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_SORT_BY_SORT_FIRST"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),
		"URL_TEMPLATES_INDEX" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_INDEX_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "index.php"),
		"URL_TEMPLATES_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "list.php?FID=#FID#"),
		"URL_TEMPLATES_READ" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_READ_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "read.php?FID=#FID#&TID=#TID#"),
		"URL_TEMPLATES_MESSAGE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message.php?FID=#FID#&TID=#TID#&MID=#MID#"),
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#"),

		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"SET_NAVIGATION" => Array(
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => GetMessage("F_SET_NAVIGATION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y"),
		"TOPICS_PER_PAGE" => Array(
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => GetMessage("F_TOPICS_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => intval(COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"))),

		"CACHE_TAG" => Array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("F_CACHE_TAG"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"CACHE_TIME" => Array(),
		"SET_TITLE" => Array(),

		"SHOW_TOPIC_POST_MESSAGE" => Array(
			"PARENT" => "TOPIC_POST_MESSAGE_SETTINGS",
			"NAME" => GetMessage("F_TOPIC_POST_MESSAGE"),
			"TYPE" => "LIST",
			"DEFAULT" => "NONE",
			"VALUES" => array(
				"NONE" => GetMessage("F_TOPIC_POST_MESSAGE_NONE"),
				"FIRST_POST" => GetMessage("F_TOPIC_POST_MESSAGE_FIRST_POST"),
				"LAST_POST" => GetMessage("F_TOPIC_POST_MESSAGE_LAST_POST"),
			),
			"REFRESH" => "Y"
		)
	)
);
if ($arCurrentValues["SHOW_TOPIC_POST_MESSAGE"] == "FIRST_POST" || $arCurrentValues["SHOW_TOPIC_POST_MESSAGE"] == "LAST_POST")
{
	$arComponentParameters['PARAMETERS']["TOPIC_POST_MESSAGE_LENGTH"] = Array(
		"PARENT" => "TOPIC_POST_MESSAGE_SETTINGS",
		"NAME" => GetMessage("F_TOPIC_POST_MESSAGE_LENGTH"),
		"TYPE" => "STRING",
		"DEFAULT" => "0");
	$arComponentParameters['PARAMETERS']["IMAGE_SIZE"] = Array(
		"PARENT" => "TOPIC_POST_MESSAGE_SETTINGS",
		"NAME" => GetMessage("F_IMAGE_SIZE"),
		"TYPE" => "STRING",
		"DEFAULT" => "600");

	$arEditorSettings = array("ALLOW_HTML", "ALLOW_ANCHOR", "ALLOW_BIU",
		"ALLOW_IMG", "ALLOW_VIDEO", "ALLOW_LIST", "ALLOW_QUOTE", "ALLOW_CODE", "ALLOW_ALIGN",
		"ALLOW_TABLE", "ALLOW_FONT", "ALLOW_SMILES", "ALLOW_NL2BR");
	foreach ($arEditorSettings as $settingName)
	{
		$arComponentParameters['PARAMETERS'][$settingName] = array(
			"PARENT" => "TOPIC_POST_MESSAGE_SETTINGS",
			"NAME" => GetMessage($settingName."_TITLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y");
	}
}
CForumParameters::AddPagerSettings(
	$arComponentParameters,
	GetMessage("F_TOPICS"),
	array("bAddGroupOnly" => ($arCurrentValues["SET_NAVIGATION"] != "Y")));
?>
