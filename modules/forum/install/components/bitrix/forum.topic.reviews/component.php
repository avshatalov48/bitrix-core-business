<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var string $componentName
 */
// FORUM
$arResult["SHOW_POST_FORM"] = (($arResult["USER"]["PERMISSION"] >= "M" || ($arResult["USER"]["PERMISSION"] >= "I" && !empty($arResult["MESSAGES"]))) ? "Y" : "N");

/* For custom template */
$arResult["LANGUAGE_ID"] = LANGUAGE_ID;
$arResult["IS_AUTHORIZED"] = $USER->IsAuthorized();
$arResult["PERMISSION"] = $arResult["USER"]["PERMISSION"];
$arResult["SHOW_NAME"] = $arResult["USER"]["SHOWED_NAME"];
$arResult["sessid"] = bitrix_sessid_post();
$arResult["SHOW_SUBSCRIBE"] = ((($arParams["SHOW_SUBSCRIBE"] == "Y") && ($arResult["USER"]["ID"] > 0 && $arResult["USER"]["PERMISSION"] > "E")) ? "Y" : "N");
$arResult["TOPIC_SUBSCRIBE"] = $arResult["USER"]["TOPIC_SUBSCRIBE"];
$arResult["FORUM_SUBSCRIBE"] = $arResult["USER"]["FORUM_SUBSCRIBE"];
$arResult["SHOW_LINK"] = (empty($arResult["read"]) ? "N" : "Y");
$arResult["SHOW_POSTS"]	= (empty($arResult["MESSAGES"]) ? "N" : "Y");
$arResult["PARSER"] = new forumTextParser(LANGUAGE_ID);
$arResult["PARSER"]->image_params = [
	"width" => $arResult["PARSER"]->imageWidth,
	"height" => $arResult["PARSER"]->imageHeight
];
$arResult["CURRENT_PAGE"] = $APPLICATION->GetCurPageParam();

$arResult["ELEMENT_REAL"] = $arResult["ELEMENT"];
$arResult["ELEMENT"] = array(
	"PRODUCT" => $arResult["ELEMENT"],
	"PRODUCT_PROPS" => array());
if (is_set($arResult["ELEMENT_REAL"], "PROPERTY_FORUM_TOPIC_ID_VALUE"))
{
	$arResult["ELEMENT"]["PRODUCT_PROPS"]["FORUM_TOPIC_ID"] = array("VALUE" => $arResult["ELEMENT_REAL"]["PROPERTY_FORUM_TOPIC_ID_VALUE"]);
	$arResult["ELEMENT"]["PRODUCT_PROPS"]["~FORUM_TOPIC_ID"] = array("VALUE" => $arResult["ELEMENT_REAL"]["~PROPERTY_FORUM_TOPIC_ID_VALUE"]);
}
if (is_set($arResult["ELEMENT_REAL"], "PROPERTY_FORUM_MESSAGE_CNT_VALUE"))
{
	$arResult["ELEMENT"]["PRODUCT_PROPS"]["FORUM_MESSAGE_CNT"] = array("VALUE" => $arResult["ELEMENT_REAL"]["PROPERTY_FORUM_MESSAGE_CNT_VALUE"]);
	$arResult["ELEMENT"]["PRODUCT_PROPS"]["~FORUM_MESSAGE_CNT"] = array("VALUE" => $arResult["ELEMENT_REAL"]["~PROPERTY_FORUM_MESSAGE_CNT_VALUE"]);
}
/* For custom template */
// *****************************************************************************************
$this->IncludeComponentTemplate();
// *****************************************************************************************
if ($arResult["FORUM_TOPIC_ID"] > 0)
	return CForumTopic::GetMessageCount($arParams["FORUM_ID"], $arResult["FORUM_TOPIC_ID"], (($arResult["USER"]["RIGHTS"]["MODERATE"] == "Y")?null:true));
else
	return 0;
?>