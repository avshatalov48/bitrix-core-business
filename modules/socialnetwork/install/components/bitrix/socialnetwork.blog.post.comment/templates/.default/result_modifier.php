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
 */

$arResult["RECORDS"] = array();
$arParams["SHOW_MINIMIZED"] = "Y";
$arParams["ENTITY_TYPE"] = "BG";
$arParams["ENTITY_XML_ID"] = "BLOG_".$arParams["ID"];
$arParams["ENTITY_ID"] = $arParams["ID"];
$arResult["newCount"] = ($arResult["~newCount"] ?? 0);
include_once(__DIR__."/functions.php");
include_once(__DIR__."/../mobile_app/functions.php");
$arResult["PUSH&PULL"] = false;

if (
	!empty($arResult["CommentsResult"])
	&& is_array($arResult["CommentsResult"])
)
{
	$arResult["~CommentsResult"] = $arResult["CommentsResult"] = array_reverse($arResult["CommentsResult"]);
	CPageOption::SetOptionString("main", "nav_page_in_session", "N");
	$filter = false;
	$commentId = 0;
	if (!empty($_REQUEST["FILTER"]))
	{
		if (
			isset($_REQUEST["FILTER"]["<ID"])
			&& in_array($_REQUEST["FILTER"]["<ID"], $arResult["IDS"])
		)
		{
			$filter = "<ID";
			$commentId = $_REQUEST["FILTER"][$filter];
		}
		else if (
			isset($_REQUEST["FILTER"][">ID"])
			&& in_array($_REQUEST["FILTER"][">ID"], $arResult["IDS"])
		)
		{
			$filter = ">ID";
			$commentId = $_REQUEST["FILTER"][$filter];
		}
		else if (
			isset($_REQUEST["FILTER"]["ID"])
			&& in_array($_REQUEST["FILTER"]["ID"], $arResult["IDS"])
		)
		{
			$filter = "ID";
			$commentId = (
				!!$arResult["ajax_comment"]
					? $arResult["ajax_comment"]
					: $_REQUEST[$arParams["COMMENT_ID_VAR"]] ?? null
			);
			if (empty($commentId))
			{
				$commentId = $_REQUEST["FILTER"][$filter];
			}
		}
	}
	else if (
		isset($_REQUEST[$arParams["COMMENT_ID_VAR"]])
		&& $_REQUEST[$arParams["COMMENT_ID_VAR"]]
		&& in_array($_REQUEST[$arParams["COMMENT_ID_VAR"]], $arResult["IDS"])
	)
	{
		$filter = ">=ID";
		$commentId = $_REQUEST[$arParams["COMMENT_ID_VAR"]];
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

	if (
		!$filter
		|| $filter === ">=ID"
	)
	{
		unset($_GET["commentId"]);
	}

	$arResult["NAV_RESULT"] = $res = new CDBResult;

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
					$arCommentTmp["DATE_CREATE_TS"] > $arParams["LAST_LOG_TS"]
					&& $key >= $arParams["PAGE_SIZE"]
				) // new comments, no more than 20
				|| (
					(
						$arCommentTmp["DATE_CREATE_TS"] <= $arParams["LAST_LOG_TS"]
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
		array("#source_post_id#", "#post_id#", "#comment_id#", "&IFRAME=Y", "#LAST_LOG_TS#"),
		array($arResult["Post"]["ID"], $arResult["Post"]["ID"], 0, "", (int)$arParams["LAST_LOG_TS"]),
		$arResult["urlToMore"]
	);

	while ($comment = $res->Fetch())
	{
		if (empty($comment['ID']))
		{
			continue;
		}

		$arResult["RECORDS"][$comment["ID"]] = socialnetworkBlogPostCommentWeb($comment, $arParams, $arResult, $this->getComponent());

		if ((int)$arResult["ajax_comment"] === (int)$comment["ID"])
		{
			if ($this->__component->prepareMobileData && function_exists("socialnetworkBlogPostCommentMobile"))
			{
				$arResult["RECORDS"][$comment["ID"]]["MOBILE"] = socialnetworkBlogPostCommentMobile(
					$comment,
					$arParams,
					$arResult,
					$this->getComponent()
				);

				if (
					!empty($comment['POST_TEXT'])
					&& \Bitrix\Main\ModuleManager::isModuleInstalled('disk')
				)
				{
					$inlineDiskObjectIdList = $inlineDiskAttachedObjectIdList = array();

					// parse inline disk object ids
					if (preg_match_all("#\\[disk file id=(n\\d+)\\]#isu", $comment['POST_TEXT'], $matches))
					{
						$inlineDiskObjectIdList = array_map(function($a) { return (int)mb_substr($a, 1); }, $matches[1]);
					}

					// parse inline disk attached object ids
					if (preg_match_all("#\\[disk file id=(\\d+)\\]#isu", $comment['POST_TEXT'], $matches))
					{
						$inlineDiskAttachedObjectIdList = array_map(function($a) { return (int)$a; }, $matches[1]);
					}

					// get inline attached images;
					$inlineDiskAttachedObjectIdImageList = array();
					if (
						(
							!empty($inlineDiskObjectIdList)
							|| !empty($inlineDiskAttachedObjectIdList)
						)
						&& \Bitrix\Main\Loader::includeModule('disk')
					)
					{
						$filter = array(
							'=OBJECT.TYPE_FILE' => \Bitrix\Disk\TypeFile::IMAGE
						);

						$subFilter = [];
						if (!empty($inlineDiskObjectIdList))
						{
							$subFilter['@OBJECT_ID'] = $inlineDiskObjectIdList;
						}
						elseif (!empty($inlineDiskAttachedObjectIdList))
						{
							$subFilter['@ID'] = $inlineDiskAttachedObjectIdList;
						}

						if (count($subFilter) > 1)
						{
							$subFilter['LOGIC'] = 'OR';
							$filter[] = $subFilter;
						}
						else
						{
							$filter = array_merge($filter, $subFilter);
						}

						$res = \Bitrix\Disk\Internals\AttachedObjectTable::getList(array(
							'filter' => $filter,
							'select' => array('ID', 'ENTITY_ID')
						));
						while ($attachedObjectFields = $res->fetch())
						{
							if ((int)$attachedObjectFields['ENTITY_ID'] === (int)$comment["ID"])
							{
								$inlineDiskAttachedObjectIdImageList[] = (int)$attachedObjectFields['ID'];
							}
						}
					}

					// find all inline images and remove them from UF
					if (!empty($inlineDiskAttachedObjectIdImageList))
					{
						if (
							!empty($arResult["RECORDS"][$comment["ID"]]["MOBILE"]["UF"])
							&& !empty($arResult["RECORDS"][$comment["ID"]]["MOBILE"]["UF"]["UF_BLOG_COMMENT_FILE"])
							&& !empty($arResult["RECORDS"][$comment["ID"]]["MOBILE"]["UF"]["UF_BLOG_COMMENT_FILE"]['VALUE'])
						)
						{
							$arResult["RECORDS"][$comment["ID"]]["MOBILE"]["UF"]["UF_BLOG_COMMENT_FILE"]['VALUE'] = array_diff($arResult["RECORDS"][$comment["ID"]]["MOBILE"]["UF"]["UF_BLOG_COMMENT_FILE"]['VALUE'], $inlineDiskAttachedObjectIdImageList);
						}
					}
				}
			}
			$arResult["PUSH&PULL"] = array(
				"ID" => $comment["ID"],
				"ACTION" => (
					$arResult['deleteCommentId'] === (int)$arResult["ajax_comment"]
						? "DELETE"
						: (
							$arResult['showCommentId'] === (int)$arResult["ajax_comment"]
							|| $arResult['hideCommentId'] === (int)$arResult['ajax_comment']
								? "MODERATE"
								: ($_POST["act"] === "edit" ? "EDIT" : "REPLY")
						)
				)
			);
		}
	}
}
else if ($arResult["ajax_comment"] > 0 && $arResult['deleteCommentId'] > 0)
{
	$arResult["PUSH&PULL"] = array(
		"ID" => $arResult["ajax_comment"],
		"ACTION" => "DELETE"
	);
}
