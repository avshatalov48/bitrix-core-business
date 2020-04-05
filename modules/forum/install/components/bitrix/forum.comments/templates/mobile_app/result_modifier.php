<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var array $arResult
 * @var array $arParams
 * @var CBitrixComponentTemplate $this
 * @var ForumCommentsComponent $this->__component
 */
if ($arResult["ERROR_MESSAGE"] && strpos($arResult["ERROR_MESSAGE"], "MID=") !== false)
{
	$arResult["ERROR_MESSAGE"] = preg_replace(array("/\(MID\=\d+\)/is", "/\s\s/", "/\s\./"), array("", " ", "."), $arResult["ERROR_MESSAGE"]);
}
if ($arResult["OK_MESSAGE"] && strpos($arResult["OK_MESSAGE"], "MID=") !== false)
{
	$arResult["OK_MESSAGE"] = preg_replace(array("/\(MID\=\d+\)/is", "/\s\s/", "/\s\./"), array("", " ", "."), $arResult["OK_MESSAGE"]);
}

$arParams["SHOW_LINK_TO_MESSAGE"] = ($arParams["SHOW_LINK_TO_MESSAGE"] == "N" ? "N" : "Y");
$arParams["SHOW_MINIMIZED"] = "Y";
$arParams["form_index"] = str_pad($arParams["COMPONENT_ID"], 7, "0", STR_PAD_LEFT);
$arParams["FORM_ID"] = "COMMENTS_".$arParams["form_index"];
$arParams["jsObjName"] = "oLHE_FC".$arParams["form_index"];
$arParams["LheId"] = "idLHE_FC".$arParams["form_index"];
$arParams["tplID"] = 'COMMENT_'.$arParams["ENTITY_TYPE"].'_'.$arParams["form_index"];

include_once(__DIR__."/functions.php");
include_once(__DIR__."/../.default/functions.php");

$arResult["PUSH&PULL"] = false;
if (!empty($arResult["MESSAGES"]))
{
	$arResult["NAV_STRING"] = GetPagePath(false, false);
	if ($arResult["NAV_RESULT"])
	{
		$strNavQueryString = htmlspecialcharsbx(DeleteParam(array(
			"PAGEN_".$arResult["NAV_RESULT"]->NavNum,
			"SIZEN_".$arResult["NAV_RESULT"]->NavNum,
			"SHOWALL_".$arResult["NAV_RESULT"]->NavNum,
			"MID", "result",
			"PHPSESSID",
			"clear_cache"
		)));
		if (!!$strNavQueryString)
			$arResult["NAV_STRING"] .= "?".$strNavQueryString;
	}
	foreach ($arResult["MESSAGES"] as $key => $res)
	{
		$arResult["MESSAGES"][$key] = forumCommentsCommentMobile($res, $arParams, $arResult, $this->__component);
		if (intval($arResult["RESULT"]) == intval($res["ID"]))
		{
			if ($this->__component->prepareMobileData)
			{
				$arResult["MESSAGES"][$key]["WEB"] = forumCommentsCommentWeb(
					$res,
					$arParams,
					$arResult,
					$this->__component
				);
			}
			$arResult["PUSH&PULL"] = array(
				"ID" => $arResult["RESULT"],
				"ACTION" => $_REQUEST['REVIEW_ACTION'] == "EDIT" ? "EDIT" : "REPLY"
			);
		}
	}
}

$arResult["bTasksInstalled"] = \Bitrix\Main\Loader::includeModule("tasks");
$arResult["bTasksAvailable"] = (
	$arResult["bTasksInstalled"]
	&& (
		!\Bitrix\Main\Loader::includeModule('bitrix24')
		|| CBitrix24BusinessTools::isToolAvailable($USER->getId(), "tasks")
	)
);
