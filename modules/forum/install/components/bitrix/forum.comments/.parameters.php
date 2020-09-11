<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("forum"))
	return;
$arForum = array();
$db_res = CForumNew::GetList(array(), array());
$iForumDefault = 0;
$selectedForum = null;
$selectedForumProps = null;

if (is_set($arCurrentValues, "FORUM_ID"))
	$selectedForum = intval($arCurrentValues['FORUM_ID']);
$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("FORUM_MESSAGE", 0, LANGUAGE_ID);

$F_USER_FIELDS = array();
if (!empty($arRes))
	foreach ($arRes as $key => $val)
		$F_USER_FIELDS[$val["FIELD_NAME"]] = (empty($val["EDIT_FORM_LABEL"]) ? $val["FIELD_NAME"] : $val["EDIT_FORM_LABEL"]);

if ($db_res && ($res = $db_res->GetNext()))
{
	do 
	{
		$iForumDefault = intval($res["ID"]);
		$arForum[intval($res["ID"])] = $res["NAME"];
		if ($selectedForum !== null && $selectedForum === intval($res['ID']))
			$selectedForumProps = $res;
	}while ($res = $db_res->GetNext());
}

$arComponentParameters = Array(
	"GROUPS" => array(
		"EDITOR_SETTINGS" => array(
			"NAME" => GetMessage("F_EDITOR_SETTINGS"),
		)
	),

	"PARAMETERS" => Array(
		"ENTITY_TYPE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_ENTITY_TYPE"),
			"TYPE" => "STRING",
			"COLS" => 2,
			"DEFAULT" => ""),
		"ENTITY_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_ENTITY_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"ENTITY_XML_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_ENTITY_XML_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"FORUM_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_FORUM_ID"),
			"TYPE" => "LIST",
			"DEFAULT" => $iForumDefault,
			"REFRESH" => "Y",
			"VALUES" => $arForum),
		"PERMISSION" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_PERMISSION"),
			"TYPE" => "STRING",
			"COLS" => 1,
			"DEFAULT" => ""),
		"USER_FIELDS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_USER_FIELDS"),
			"TYPE" => "LIST",
			"VALUES" => $F_USER_FIELDS,
			"MULTIPLE" => "Y",
			"DEFAULT" => array_keys($F_USER_FIELDS),
			"HIDDEN" => "$hidden"),
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"MESSAGES_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => intval(COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"))),
		"PAGE_NAVIGATION_TEMPLATE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGE_NAVIGATION_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"NAME_TEMPLATE" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("F_NAME_TEMPLATE"),
			"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS"),
		"IMAGE_SIZE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_IMAGE_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "600"),
		"IMAGE_HTML_SIZE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_IMAGE_HTML_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "0"),
		"EDITOR_CODE_DEFAULT" => Array(
			"NAME" => GetMessage("F_EDITOR_CODE_DEFAULT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS"),
		"SUBSCRIBE_AUTHOR_ELEMENT" => Array(
			"NAME" => GetMessage("F_SUBSCRIBE_AUTHOR_ELEMENT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",),
		"SHOW_RATING" => Array(
			"NAME" => GetMessage("F_SHOW_RATING"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "ADDITIONAL_SETTINGS",),
		"SHOW_MINIMIZED" => Array(
			"NAME" => GetMessage("F_SHOW_MINIMIZED"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",),
		"USE_CAPTCHA" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_USE_CAPTCHA"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"PREORDER" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PREORDER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"SET_LAST_VISIT" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_LAST_VISIT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),
		"CACHE_TIME" => Array(),
	)
);

$arEditorSettings = array("ALLOW_HTML", "ALLOW_ANCHOR", "ALLOW_BIU", 
	"ALLOW_IMG", "ALLOW_VIDEO", "ALLOW_LIST", "ALLOW_QUOTE", "ALLOW_CODE", 
	"ALLOW_TABLE", "ALLOW_FONT", "ALLOW_SMILES", "ALLOW_NL2BR", "ALLOW_ALIGN",
	"ALLOW_MENTION");
foreach ($arEditorSettings as $settingName)
{
	$hidden = "N";
	if ($selectedForumProps !== null)
		$hidden = ($selectedForumProps[$settingName] === "N");
	$arComponentParameters['PARAMETERS'][$settingName] = array(
			"PARENT" => "EDITOR_SETTINGS",
			"NAME" => GetMessage($settingName."_TITLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"HIDDEN" => $hidden
		);
}
/*
GetMessage("ALLOW_HTML_TITLE");
GetMessage("ALLOW_ANCHOR_TITLE");
GetMessage("ALLOW_BIU_TITLE");
GetMessage("ALLOW_IMG_TITLE");
GetMessage("ALLOW_VIDEO_TITLE");
GetMessage("ALLOW_LIST_TITLE");
GetMessage("ALLOW_QUOTE_TITLE");
GetMessage("ALLOW_CODE_TITLE");
GetMessage("ALLOW_TABLE_TITLE");
GetMessage("ALLOW_FONT_TITLE");
GetMessage("ALLOW_SMILES_TITLE");
GetMessage("ALLOW_NL2BR_TITLE");
GetMessage("ALLOW_ALIGN_TITLE");
GetMessage("ALLOW_MENTION_TITLE");
*/
?>
