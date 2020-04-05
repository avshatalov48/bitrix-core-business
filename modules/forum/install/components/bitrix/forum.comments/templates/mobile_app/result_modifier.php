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
$arResult["VISIBLE_RECORDS_COUNT"] = 3;

if (!empty($arResult["MESSAGES"]))
{
	if ($arResult['MID'] > 0)
	{
		$messagesList = [];
		foreach($arResult["MESSAGES"] as $messageid => $messageFields)
		{
			$arResult["VISIBLE_RECORDS_COUNT"]++;
			$messagesList[$messageid] = $messageFields;
			if ($messageid == $arResult['MID'])
			{
				break;
			}
		}

		$arResult["VISIBLE_RECORDS_COUNT"] = count($messagesList);
		if ($arResult["VISIBLE_RECORDS_COUNT"] < 3)
		{
			$arResult["VISIBLE_RECORDS_COUNT"] = 3;
		}

		if (count($arResult["MESSAGES"]) > $arResult["VISIBLE_RECORDS_COUNT"])
		{
			$arResult["MESSAGES"] = array_slice($arResult["MESSAGES"], 0, $arResult["VISIBLE_RECORDS_COUNT"]);
		}

		$arResult["NAV_RESULT"]->bShowAll = false;
	}

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
		if (in_array($arResult["ACTION"], ["hide", "show", "edit", "add"]) && intval($arResult["RESULT"]) == intval($res["ID"]))
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
			if (in_array($arResult["ACTION"], array("hide", "show")))
			{
				$action = "MODERATE";
			}
			else
			{
				$action = ($arResult["ACTION"] == "edit" ? "EDIT" : "REPLY");
			}
			$arResult["PUSH&PULL"] = array(
				"ID" => $arResult["RESULT"],
				"ACTION" => $action
			);
		}
	}
}
if ($arResult["ACTION"] == "del" && $arResult["RESULT"] > 0)
{
	$arResult["PUSH&PULL"] = array(
		"ID" => $arResult["RESULT"],
		"ACTION" => "DELETE"
	);
}
$arResult["bTasksInstalled"] = \Bitrix\Main\Loader::includeModule("tasks");
$arResult["bTasksAvailable"] = (
	$arResult["bTasksInstalled"]
	&& (
		!\Bitrix\Main\Loader::includeModule('bitrix24')
		|| CBitrix24BusinessTools::isToolAvailable($USER->getId(), "tasks")
	)
);
