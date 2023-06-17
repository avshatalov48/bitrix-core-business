<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var CMain $APPLICATION
 * @var array $arResult
 * @var array $arParams
 * @var CBitrixComponentTemplate $this
 * @var SocialnetworkBlogPostComment $this->__component
 */

$arResult["RECORDS"] = [];
$arParams["SHOW_MINIMIZED"] = "Y";
$arParams["ENTITY_TYPE"] = "BG";
$arParams["ENTITY_XML_ID"] = "BLOG_".$arParams["ID"];
$arParams["ENTITY_ID"] = $arParams["ID"];
$arResult["newCount"] = $arResult["~newCount"] ?? null;

include_once(__DIR__."/functions.php");
include_once(__DIR__."/../.default/functions.php");

$arResult["PUSH&PULL"] = false;

if (
	!empty($arResult["CommentsResult"])
	&& is_array($arResult["CommentsResult"])
	&& \Bitrix\Main\Loader::includeModule('mobile')
)
{
	$arResult["~CommentsResult"] = $arResult["CommentsResult"] = array_reverse($arResult["CommentsResult"]);
	CPageOption::SetOptionString("main", "nav_page_in_session", "N");
	$filter = false; $commentId = 0;
	if (!empty($_REQUEST["FILTER"]))
	{
		if (isset($_REQUEST["FILTER"]["<ID"]) && in_array($_REQUEST["FILTER"]["<ID"], $arResult["IDS"]))
		{
			$filter = "<ID";
			$commentId = $_REQUEST["FILTER"][$filter];
		}
		else if (isset($_REQUEST["FILTER"][">ID"]) && in_array($_REQUEST["FILTER"][">ID"], $arResult["IDS"]))
		{
			$filter = ">ID";
			$commentId = $_REQUEST["FILTER"][$filter];
		}
		else if (isset($_REQUEST["FILTER"]["ID"]) && in_array($_REQUEST["FILTER"]["ID"], $arResult["IDS"]))
		{
			$filter = "ID";
			$commentId = (!!$arResult["ajax_comment"] ? $arResult["ajax_comment"] : $_REQUEST[$arParams["COMMENT_ID_VAR"]]);
		}
	}
	else if (
		$_REQUEST[$arParams["COMMENT_ID_VAR"]] ?? null
		&& in_array($_REQUEST[$arParams["COMMENT_ID_VAR"]], $arResult["IDS"])
	)
	{
		$filter = ">=ID";
		$commentId = $_REQUEST[$arParams["COMMENT_ID_VAR"]];
	}
	else if
	(
		($_REQUEST["ACTION"] ?? null) === "CONVERT"
		&& in_array($_REQUEST["ENTITY_TYPE_ID"], array('LOG_COMMENT', 'BLOG_COMMENT'))
		&& $_REQUEST["ENTITY_ID"]
		&& in_array((int)$_REQUEST["ENTITY_ID"], $arResult["IDS"])
	)
	{
		$filter = ">=ID";
		$commentId = (int)$_REQUEST["ENTITY_ID"];
	}
	if (!!$filter)
	{
		$id = reset($arResult["IDS"]);
		$CommentsResult = array();
		while ($id > 0 && $id != $commentId)
		{
			array_unshift($CommentsResult, array_pop($arResult["CommentsResult"]));
			$id = next($arResult["IDS"]);
		}

		if ($filter === "<ID")
		{
			$arResult["CommentsResult"] = $CommentsResult;
		}
		elseif ($filter === ">ID")
		{
			array_unshift($CommentsResult, array_pop($arResult["CommentsResult"]));
		}
		elseif ($filter === "ID")
		{
			$arResult["CommentsResult"] = array(array_pop($arResult["CommentsResult"]));
		}
		else
		{
			if (count($arResult["CommentsResult"]) > $arResult["newCount"])
			{
				$arResult["newCount"] = count($arResult["CommentsResult"]);
				if (
					$filter === ">=ID"
					&& $commentId > 0
				) // commentId in $_REQUEST
				{
					$arParams["PAGE_SIZE"] = $arResult["newCount"];
				}
			}

			$arResult["CommentsResult"] = $arResult["~CommentsResult"];
		}
	}

	$res = new CDBResult;
	$arResult["NAV_RESULT"] = $res;

	if (
		$arParams["NAV_TYPE_NEW"] === 'Y'
		&& !$filter
		&& $arResult['firstPage']
		&& ($arResult["Post"]["NUM_COMMENTS_ALL"] - count($arResult["CommentsResult"])) > 0
	)
	{
		$arCommentsFullListCut = array();

		foreach ($arResult["CommentsResult"] as $key => $arCommentTmp)
		{
			if (
				(
					$arCommentTmp["DATE_CREATE_TS"] > ($arParams["LAST_LOG_TS"] - $arResult["TZ_OFFSET"])
					&& $key >= $arParams["PAGE_SIZE"]
				) // new comments, no more than 20
				|| (
					(
						$arCommentTmp["DATE_CREATE_TS"] <= ($arParams["LAST_LOG_TS"] - $arResult["TZ_OFFSET"])
						|| $arParams["LAST_LOG_TS"] <= 0
					)
					&& $key >= $arParams["PAGE_SIZE_MIN"]
				) // old comments, no more than 3
			)
			{
				continue;
			}

			$arCommentsFullListCut[] = $arCommentTmp;
		}

		$arResult["NAV_RESULT"]->InitFromArray(array_fill(0, $arResult["Post"]["NUM_COMMENTS_ALL"], null));
		$res->InitFromArray($arCommentsFullListCut);
	}
	else
	{
		$res->InitFromArray($arResult["CommentsResult"]);
		$arResult["NAV_RESULT"] = $res;
	}

	$arResult["NAV_RESULT"]->NavStart($arParams["PAGE_SIZE"], false);

	if (
		$arParams["NAV_TYPE_NEW"] === 'Y'
		&& !$filter
		&& $arResult['firstPage']
	)
	{
		$arResult["NAV_RESULT"]->NavRecordCount = $arResult["Post"]["NUM_COMMENTS_ALL"]; //  to fix NavStart
	}

	$arResult["NAV_STRING"] = str_replace(
		array("#LAST_LOG_TS#"),
		array(
			(int)$arParams["LAST_LOG_TS"] > 0
				? "&LAST_LOG_TS=" . (int)$arParams["LAST_LOG_TS"]
				: ''
		),
		$arResult["urlMobileToPost"]
	);

	$commentData = [];
	$commentInlineDiskData = [];
	$inlineDiskObjectIdList = [];
	$inlineDiskAttachedObjectIdList = [];

	while ($comment = $res->Fetch())
	{
		if (empty($comment['ID']))
		{
			continue;
		}

		$commentData[] = $comment;
	}

	foreach ($commentData as $comment)
	{
		if (
			!empty($comment['POST_TEXT'])
			&& \Bitrix\Main\ModuleManager::isModuleInstalled('disk')
		)
		{
			$commentObjectId = [];
			$commentAttachedObjectId = [];

			if ($ufData = \Bitrix\Mobile\Livefeed\Helper::getDiskDataByCommentText($comment['POST_TEXT']))
			{
				$commentInlineDiskData[$comment['ID']] = $ufData;
				$inlineDiskObjectIdList = array_merge($inlineDiskObjectIdList, $ufData['OBJECT_ID']);
				$inlineDiskAttachedObjectIdList = array_merge($inlineDiskAttachedObjectIdList, $ufData['ATTACHED_OBJECT_ID']);
			}
		}
	}

	$inlineDiskAttachedObjectIdImageList = [];
	$entityAttachedObjectIdList = [];
	if ($ufData = \Bitrix\Mobile\Livefeed\Helper::getDiskUFDataForComments($inlineDiskObjectIdList, $inlineDiskAttachedObjectIdList))
	{
		$inlineDiskAttachedObjectIdImageList = $ufData['ATTACHED_OBJECT_DATA'];
		$entityAttachedObjectIdList = $ufData['ENTITIES_DATA'];
	}

	foreach ($commentData as $comment)
	{
		$arResult["RECORDS"][$comment["ID"]] = socialnetworkBlogPostCommentMobile($comment, $arParams, $arResult, $this->getComponent());
		$mobileComment = $arResult["RECORDS"][$comment["ID"]];

		if ((int)$arResult["ajax_comment"] === (int)$comment["ID"])
		{
			if ($this->__component->prepareMobileData && function_exists("socialnetworkBlogPostCommentWeb"))
			{
				$arResult["RECORDS"][$comment["ID"]]["WEB"] = socialnetworkBlogPostCommentWeb(
					$comment,
					array_merge($arParams, array("SHOW_RATING" => "Y")),
					$arResult,
					$this->getComponent()
				);
				$arResult["RECORDS"][$comment["ID"]]['RATING_VOTE_ID'] = (!empty($arResult["RECORDS"][$comment["ID"]]["WEB"]['RATING_VOTE_ID']) ? $arResult["RECORDS"][$comment["ID"]]["WEB"]['RATING_VOTE_ID'] : '');
				$arResult["RECORDS"][$comment["ID"]]['RATING_USER_HAS_VOTED'] = (!empty($arResult["RECORDS"][$comment["ID"]]["WEB"]['RATING_USER_HAS_VOTED']) ? $arResult["RECORDS"][$comment["ID"]]["WEB"]['RATING_USER_HAS_VOTED'] : '');
			}
			$arResult["PUSH&PULL"] = array(
				"ID" => $comment["ID"],
				"ACTION" => $_POST["act"] === "edit" ? "EDIT" : "REPLY"
			);
		}

		if (
			!empty($inlineDiskAttachedObjectIdImageList)
			&& isset($commentInlineDiskData[$comment['ID']])
		)
		{
			$inlineAttachedImagesId = \Bitrix\Mobile\Livefeed\Helper::getCommentInlineAttachedImagesId([
				'commentId' => $comment["ID"],
				'inlineDiskAttachedObjectIdImageList' => $inlineDiskAttachedObjectIdImageList,
				'commentInlineDiskData' => $commentInlineDiskData[$comment["ID"]],
				'entityAttachedObjectIdList' => $entityAttachedObjectIdList[$comment["ID"]],
			]);

			if (
				!empty($arResult["RECORDS"][$comment["ID"]]["UF"])
				&& !empty($arResult["RECORDS"][$comment["ID"]]["UF"]["UF_BLOG_COMMENT_FILE"])
				&& !empty($arResult["RECORDS"][$comment["ID"]]["UF"]["UF_BLOG_COMMENT_FILE"]['VALUE'])
			)
			{
				$arResult["RECORDS"][$comment["ID"]]["UF"]["UF_BLOG_COMMENT_FILE"]['VALUE_INLINE'] = $inlineAttachedImagesId;
			}
		}
	}
}
elseif ($arResult["ajax_comment"] > 0 && $arResult['deleteCommentId'] > 0)
{
	$arResult["PUSH&PULL"] = array(
		"ID" => $arResult["ajax_comment"],
		"ACTION" => "DELETE"
	);
}

