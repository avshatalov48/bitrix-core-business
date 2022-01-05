<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;

/**
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var array $arResult
 * @var array $arParams
 * @var CBitrixComponentTemplate $this
 * @var ForumCommentsComponent $this->__component
 */

if ($arResult["ERROR_MESSAGE"] && mb_strpos($arResult["ERROR_MESSAGE"], "MID=") !== false)
{
	$arResult["ERROR_MESSAGE"] = preg_replace(array("/\(MID\=\d+\)/is", "/\s\s/", "/\s\./"), array("", " ", "."), $arResult["ERROR_MESSAGE"]);
}
if ($arResult["OK_MESSAGE"] && mb_strpos($arResult["OK_MESSAGE"], "MID=") !== false)
{
	$arResult["OK_MESSAGE"] = preg_replace(array("/\(MID\=\d+\)/is", "/\s\s/", "/\s\./"), array("", " ", "."), $arResult["OK_MESSAGE"]);
}

$arParams["SHOW_LINK_TO_MESSAGE"] = ($arParams["SHOW_LINK_TO_MESSAGE"] === "N" ? "N" : "Y");
$arParams["SHOW_MINIMIZED"] = "Y";
$arParams["form_index"] = str_pad($arParams["COMPONENT_ID"], 7, "0", STR_PAD_LEFT);
$arParams["FORM_ID"] = "COMMENTS_".$arParams["form_index"];
$arParams["jsObjName"] = "oLHE_FC".$arParams["form_index"];
$arParams["LheId"] = "idLHE_FC".$arParams["form_index"];
$arParams["tplID"] = 'COMMENT_'.$arParams["ENTITY_TYPE"].'_'.$arParams["form_index"];

include_once(__DIR__."/functions.php");
include_once(__DIR__."/../.default/functions.php");

$visibleRecordsCount = 3;

$arResult["PUSH&PULL"] = $arResult["PUSH&PULL"] ?? false;
$arResult["VISIBLE_RECORDS_COUNT"] = $visibleRecordsCount;

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$post = array_merge($request->getQueryList()->toArray(), $request->getPostList()->toArray());

Loader::includeModule('mobile');

if (!empty($arResult["MESSAGES"]))
{
	if ($arResult["NAV_RESULT"]->NavRecordCount > $visibleRecordsCount)
	{
		$allMessages = 0;
		$regularMessages = 0;
		$findMessageId = (int)$arResult["MID"];
		$limitMessageCount = !is_array($request->get("FILTER"));
		foreach($arResult["MESSAGES"] as $id => $message)
		{
			if ((int)$message["~SERVICE_TYPE"] <= 0)
			{
				$regularMessages++;
			}
			$allMessages++;

			if ($limitMessageCount)
			{
				if ($findMessageId <= 0 && $regularMessages >= $visibleRecordsCount)
				{
					break;
				}

				if ($findMessageId > 0 && (int)$id === $findMessageId)
				{
					if ($regularMessages >= $visibleRecordsCount)
					{
						break;
					}
					$findMessageId = 0;
				}
			}
		}
		$arResult["MESSAGES"] = array_slice($arResult["MESSAGES"], 0, $allMessages, true);
		$arResult["VISIBLE_RECORDS_COUNT"] = count($arResult["MESSAGES"]);
		$arResult["NAV_RESULT"]->bShowAll = $arResult["NAV_RESULT"]->NavRecordCount <= $regularMessages;
		if ($limitMessageCount)
		{
			$arResult["NAV_RESULT"]->NavRecordCount += ($allMessages - $regularMessages);
		}
	}
	else
	{
		$arResult["NAV_RESULT"]->nSelectedCount = $arResult["NAV_RESULT"]->NavRecordCount;
		$arResult["NAV_RESULT"]->bShowAll = true;
	}

	array_walk($arResult["MESSAGES"], static function(&$item) {
		$item['COLLAPSED'] = (
			$item['~SERVICE_TYPE'] > 0
			&& $item['NEW'] !== 'Y'
				? 'Y'
				: 'N'
		);
		return $item;
	});

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
		if ($strNavQueryString)
		{
			$arResult["NAV_STRING"] .= "?" . $strNavQueryString;
		}
	}

	$commentData = [];
	$commentInlineDiskData = [];
	$inlineDiskObjectIdList = [];
	$inlineDiskAttachedObjectIdList = [];

	foreach ($arResult["MESSAGES"] as $key => $res)
	{
		if (
			!empty($res['~POST_MESSAGE_TEXT'])
			&& \Bitrix\Main\ModuleManager::isModuleInstalled('disk')
			&& method_exists('\Bitrix\Mobile\Livefeed\Helper', 'getDiskDataByCommentText')
		)
		{
			$commentObjectId = [];
			$commentAttachedObjectId = [];

			if ($ufData = \Bitrix\Mobile\Livefeed\Helper::getDiskDataByCommentText($res['~POST_MESSAGE_TEXT']))
			{
				$commentInlineDiskData[$key] = $ufData;
				$inlineDiskObjectIdList = array_merge($inlineDiskObjectIdList, $ufData['OBJECT_ID']);
				$inlineDiskAttachedObjectIdList = array_merge($inlineDiskAttachedObjectIdList, $ufData['ATTACHED_OBJECT_ID']);
			}
		}
	}

	$inlineDiskAttachedObjectIdImageList = [];
	$entityAttachedObjectIdList = [];
	if (
		method_exists('\Bitrix\Mobile\Livefeed\Helper', 'getDiskUFDataForComments')
		&& ($ufData = \Bitrix\Mobile\Livefeed\Helper::getDiskUFDataForComments($inlineDiskObjectIdList, $inlineDiskAttachedObjectIdList))
	)
	{
		$inlineDiskAttachedObjectIdImageList = $ufData['ATTACHED_OBJECT_DATA'];
		$entityAttachedObjectIdList = $ufData['ENTITIES_DATA'];
	}

	foreach ($arResult["MESSAGES"] as $key => $res)
	{
		$arResult["MESSAGES"][$key] = forumCommentsCommentMobile($res, $arParams, $arResult, $this->__component);
		if (
			(int)$arResult["RESULT"] === (int)$res["ID"]
			&& in_array($arResult["ACTION"], ["hide", "show", "edit", "add"])
		)
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
				$action = ($arResult["ACTION"] === "edit" ? "EDIT" : "REPLY");
			}
			$arResult["PUSH&PULL"] = array(
				"ID" => $arResult["RESULT"],
				"ACTION" => $action
			);
		}

		if (
			!empty($inlineDiskAttachedObjectIdImageList)
			&& isset($commentInlineDiskData[$key])
			&& method_exists('\Bitrix\Mobile\Livefeed\Helper', 'getCommentInlineAttachedImagesId')
		)
		{
			$inlineAttachedImagesId = \Bitrix\Mobile\Livefeed\Helper::getCommentInlineAttachedImagesId([
				'commentId' => $key,
				'inlineDiskAttachedObjectIdImageList' => $inlineDiskAttachedObjectIdImageList,
				'commentInlineDiskData' => $commentInlineDiskData[$key],
				'entityAttachedObjectIdList' => $entityAttachedObjectIdList[$key],
			]);

			if (
				!empty($res['PROPS'])
				&& !empty($res['PROPS']['UF_FORUM_MESSAGE_DOC'])
				&& !empty($res['PROPS']['UF_FORUM_MESSAGE_DOC']['VALUE'])
			)
			{
				$arResult['MESSAGES'][$key]['UF']['UF_FORUM_MESSAGE_DOC']['VALUE_INLINE'] = $inlineAttachedImagesId;
			}
		}
	}
}
if ($arResult["ACTION"] === "del" && $arResult["RESULT"] > 0)
{
	$arResult["PUSH&PULL"] = array(
		"ID" => $arResult["RESULT"],
		"ACTION" => "DELETE"
	);
}
$arResult["bTasksInstalled"] = Loader::includeModule("tasks");
$arResult["bTasksAvailable"] = (
	$arResult["bTasksInstalled"]
	&& (
		!Loader::includeModule('bitrix24')
		|| CBitrix24BusinessTools::isToolAvailable($USER->getId(), "tasks")
	)
);
