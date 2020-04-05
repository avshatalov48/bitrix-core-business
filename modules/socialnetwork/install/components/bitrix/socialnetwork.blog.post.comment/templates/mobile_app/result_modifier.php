<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var CMain $APPLICATION
 * @var array $arResult
 * @var array $arParams
 * @var CBitrixComponentTemplate $this
 * @var SocialnetworkBlogPostComment $this->__component
 */
$arResult["RECORDS"] = array();
$arParams["SHOW_MINIMIZED"] = "Y";
$arParams["ENTITY_TYPE"] = "BG";
$arParams["ENTITY_XML_ID"] = "BLOG_".$arParams["ID"];
$arParams["ENTITY_ID"] = $arParams["ID"];
$arResult["newCount"] = $arResult["~newCount"];
include_once(__DIR__."/functions.php");
include_once(__DIR__."/../.default/functions.php");
$arResult["PUSH&PULL"] = false;

if(!empty($arResult["CommentsResult"]) && is_array($arResult["CommentsResult"]))
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
	else if ($_REQUEST[$arParams["COMMENT_ID_VAR"]] && in_array($_REQUEST[$arParams["COMMENT_ID_VAR"]], $arResult["IDS"]))
	{
		$filter = ">=ID";
		$commentId = $_REQUEST[$arParams["COMMENT_ID_VAR"]];
	}
	else if
	(
		$_REQUEST["ACTION"] == "CONVERT"
		&& in_array($_REQUEST["ENTITY_TYPE_ID"], array('LOG_COMMENT', 'BLOG_COMMENT'))
		&& $_REQUEST["ENTITY_ID"]
		&& in_array(intval($_REQUEST["ENTITY_ID"]), $arResult["IDS"])
	)
	{
		$filter = ">=ID";
		$commentId = intval($_REQUEST["ENTITY_ID"]);
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
		if ($filter == "<ID")
			$arResult["CommentsResult"] = $CommentsResult;
		else if ($filter == ">ID")
			array_unshift($CommentsResult, array_pop($arResult["CommentsResult"]));
		else if ($filter == "ID")
			$arResult["CommentsResult"] = array(array_pop($arResult["CommentsResult"]));
		else
		{
			if (count($arResult["CommentsResult"]) > $arResult["newCount"])
			{
				$arResult["newCount"] = count($arResult["CommentsResult"]);
				if (
					$filter == ">=ID"
					&& $commentId > 0
				) // commentId in $_REQUEST
				{
					$arParams["PAGE_SIZE"] = $arResult["newCount"];
				}
			}
			$arResult["CommentsResult"] = $arResult["~CommentsResult"];
		}
	}

	$arResult["NAV_RESULT"] = $res = new CDBResult;

	if (
		$arParams["NAV_TYPE_NEW"] == 'Y'
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
		$arParams["NAV_TYPE_NEW"] == 'Y'
		&& !$filter
		&& $arResult['firstPage']
	)
	{
		$arResult["NAV_RESULT"]->NavRecordCount = $arResult["Post"]["NUM_COMMENTS_ALL"]; //  to fix NavStart
	}

	$arResult["NAV_STRING"] = str_replace(
		array("#LAST_LOG_TS#"),
		array(
			intval($arParams["LAST_LOG_TS"]) > 0
				? "&LAST_LOG_TS=".intval($arParams["LAST_LOG_TS"])
				: ''
		),
		$arResult["urlMobileToPost"]
	);

	$commentData = array();
	$commentInlineDiskData = array();
	$inlineDiskObjectIdList = array();
	$inlineDiskAttachedObjectIdList = array();

	while($comment = $res->Fetch())
	{
		if (empty($comment['ID']))
		{
			continue;
		}

		if (
			!empty($comment['POST_TEXT'])
			&& \Bitrix\Main\ModuleManager::isModuleInstalled('disk')
		)
		{
			$commentObjectId = $commentAttachedObjectId = array();

			if (preg_match_all("#\\[disk file id=(n\\d+)\\]#is".BX_UTF_PCRE_MODIFIER, $comment['POST_TEXT'], $matches))
			{
				$commentObjectId = array_map(function($a) { return intval(substr($a, 1)); }, $matches[1]);
				$inlineDiskObjectIdList = array_merge($inlineDiskObjectIdList, $commentObjectId);
			}

			if (preg_match_all("#\\[disk file id=(\\d+)\\]#is".BX_UTF_PCRE_MODIFIER, $comment['POST_TEXT'], $matches))
			{
				$commentAttachedObjectId = array_map(function($a) { return intval($a); }, $matches[1]);
				$inlineDiskAttachedObjectIdList = array_merge($inlineDiskAttachedObjectIdList, $commentAttachedObjectId);
			}

			if (
				!empty($commentObjectId)
				|| !empty($commentAttachedObjectId)
			)
			{
				$commentInlineDiskData[$comment['ID']] = array(
					'OBJECT_ID' => $commentObjectId,
					'ATTACHED_OBJECT_ID' => $commentAttachedObjectId
				);
			}
		}
		$commentData[] = $comment;
	}

	// get inline attached images;
	$inlineDiskAttachedObjectIdImageList = $entityAttachedObjectIdList = array();
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

		if(count($subFilter) > 1)
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
			'select' => array('ID', 'OBJECT_ID', 'ENTITY_ID')
		));
		while ($attachedObjectFields = $res->fetch())
		{
			$inlineDiskAttachedObjectIdImageList[intval($attachedObjectFields['ID'])] = intval($attachedObjectFields['OBJECT_ID']);
			if (!isset($entityAttachedObjectIdList[intval($attachedObjectFields['ENTITY_ID'])]))
			{
				$entityAttachedObjectIdList[intval($attachedObjectFields['ENTITY_ID'])] = array();
			}
			$entityAttachedObjectIdList[intval($attachedObjectFields['ENTITY_ID'])][] = intval($attachedObjectFields['ID']);
		}
	}

	foreach($commentData as $comment)
	{
		$arResult["RECORDS"][$comment["ID"]] = socialnetworkBlogPostCommentMobile($comment, $arParams, $arResult, $this->__component);
		$mobileComment = $arResult["RECORDS"][$comment["ID"]];

		if (intval($arResult["ajax_comment"]) == intval($comment["ID"]))
		{
			if ($this->__component->prepareMobileData)
			{
				$arResult["RECORDS"][$comment["ID"]]["WEB"] = socialnetworkBlogPostCommentWeb(
					$comment,
					array_merge($arParams, array("SHOW_RATING" => "Y")),
					$arResult,
					$this->__component
				);
				$arResult["RECORDS"][$comment["ID"]]['RATING_VOTE_ID'] = (!empty($arResult["RECORDS"][$comment["ID"]]["WEB"]['RATING_VOTE_ID']) ? $arResult["RECORDS"][$comment["ID"]]["WEB"]['RATING_VOTE_ID'] : '');
				$arResult["RECORDS"][$comment["ID"]]['RATING_USER_HAS_VOTED'] = (!empty($arResult["RECORDS"][$comment["ID"]]["WEB"]['RATING_USER_HAS_VOTED']) ? $arResult["RECORDS"][$comment["ID"]]["WEB"]['RATING_USER_HAS_VOTED'] : '');
			}
			$arResult["PUSH&PULL"] = array(
				"ID" => $comment["ID"],
				"ACTION" => $_POST["act"] == "edit" ? "EDIT" : "REPLY"
			);
		}

		// find all inline images and remove them from UF
		if (
			!empty($inlineDiskAttachedObjectIdImageList)
			&& isset($commentInlineDiskData[$comment['ID']])
		)
		{
			$inlineAttachedImagesId = array();
			if (!empty($commentInlineDiskData[$comment['ID']]['OBJECT_ID']))
			{
				foreach($commentInlineDiskData[$comment['ID']]['OBJECT_ID'] as $val)
				{
					$inlineAttachedImagesId = array_merge($inlineAttachedImagesId, array_keys($inlineDiskAttachedObjectIdImageList, $val));
				}
			}
			if (!empty($commentInlineDiskData[$comment['ID']]['ATTACHED_OBJECT_ID']))
			{
				$inlineAttachedImagesId = array_merge($inlineAttachedImagesId, array_intersect($commentInlineDiskData[$comment['ID']]['ATTACHED_OBJECT_ID'], array_keys($inlineDiskAttachedObjectIdImageList)));
			}

			if (is_array($entityAttachedObjectIdList[$comment['ID']]))
			{
				$inlineAttachedImagesId = array_intersect($inlineAttachedImagesId, $entityAttachedObjectIdList[$comment['ID']]);
			}

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
else if ($arResult["ajax_comment"] > 0 && $_GET["delete_comment_id"] > 0)
{
	$arResult["PUSH&PULL"] = array(
		"ID" => $arResult["ajax_comment"],
		"ACTION" => "DELETE"
	);
}
?>