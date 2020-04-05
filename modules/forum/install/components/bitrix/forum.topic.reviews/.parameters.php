<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum") || !CModule::IncludeModule("iblock"))
	return;
$arForum = array();
$db_res = CForumNew::GetList(array(), array());
$iForumDefault = 0;
if ($db_res && ($res = $db_res->GetNext()))
{
	do 
	{
		$iForumDefault = intVal($res["ID"]);
		$arForum[intVal($res["ID"])] = $res["NAME"];
	}while ($res = $db_res->GetNext());
}
$arIBlockType = array();
$sIBlockType = "";
$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
while ($arr=$rsIBlockType->Fetch())
{
	if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
	{
		if ($sIBlockType == "")
			$sIBlockType = $arr["ID"];
		$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
	}
}

$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
$iIblockDefault = 0;
while($arr=$rsIBlock->Fetch())
{
	if ($iIblockDefault <= 0)
		$iIblockDefault = intVal($arr["ID"]);
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"FORUM_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_FORUM_ID"),
			"TYPE" => "LIST",
			"DEFAULT" => $iForumDefault,
			"VALUES" => $arForum),
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
			"DEFAULT" => $sIBlockType),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock, 
			"DEFAULT" => $iIblockDefault),
		"ELEMENT_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}'),

		"URL_TEMPLATES_READ" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_READ_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"URL_TEMPLATES_DETAIL" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_DETAIL_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
			
		"MESSAGES_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => intVal(COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10"))),
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
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"EDITOR_CODE_DEFAULT" => Array(
			"NAME" => GetMessage("F_EDITOR_CODE_DEFAULT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "ADDITIONAL_SETTINGS",),
		"SHOW_AVATAR" => Array(
			"NAME" => GetMessage("F_SHOW_AVATAR"),
			"TYPE" => "LIST",
			"VALUES" => Array(
				"PHOTO" => GetMessage("F_SHOW_AVATAR_PHOTO"),
				"Y" => GetMessage("MAIN_YES"),
				"N" => GetMessage("MAIN_NO"),
			),
			"MULTIPLE" => "N",
			"DEFAULT" => "Y",
			"PARENT" => "ADDITIONAL_SETTINGS"),
		"SHOW_RATING" => Array(
			"NAME" => GetMessage("SHOW_RATING"),
			"TYPE" => "LIST",
			"VALUES" => Array(
				"" => GetMessage("SHOW_RATING_CONFIG"),
				"Y" => GetMessage("MAIN_YES"),
				"N" => GetMessage("MAIN_NO"),
			),
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS"),
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
		"CACHE_TIME" => Array(),
	)
);
?>
