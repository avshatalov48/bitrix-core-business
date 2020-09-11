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
		"COMMENTS_PER_PAGE" => Array(
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => GetMessage("F_COMMENTS_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => intval(COption::GetOptionString("forum", "COMMENTS_PER_PAGE", "10"))),

		"CACHE_TIME" => Array(),
		"SET_TITLE" => Array(),

		"TOPIC_POST_MESSAGE_LENGTH" => Array(
			"PARENT" => "TOPIC_POST_MESSAGE_SETTINGS",
			"NAME" => GetMessage("F_TOPIC_POST_MESSAGE_LENGTH"),
			"TYPE" => "STRING",
			"DEFAULT" => "0"),
	)
);

$arEditorSettings = array("ALLOW_HTML", "ALLOW_ANCHOR", "ALLOW_BIU",
	"ALLOW_IMG", "ALLOW_VIDEO", "ALLOW_LIST", "ALLOW_QUOTE", "ALLOW_CODE", "ALLOW_ALIGN",
	"ALLOW_TABLE", "ALLOW_FONT", "ALLOW_SMILES", "ALLOW_NL2BR");
foreach ($arEditorSettings as $settingName)
{
	$hidden = "N";
	if ($selectedForumProps !== null)
		$hidden = ($selectedForumProps[$settingName] === "N");
	$arComponentParameters['PARAMETERS'][$settingName] = array(
			"PARENT" => "TOPIC_POST_MESSAGE_SETTINGS",
			"NAME" => GetMessage($settingName."_TITLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"HIDDEN" => $hidden
		);
}

CForumParameters::AddPagerSettings(
	$arComponentParameters,
	GetMessage("F_TOPICS"),
	array("bAddGroupOnly" => ($arCurrentValues["SET_NAVIGATION"] != "Y")));
?>
