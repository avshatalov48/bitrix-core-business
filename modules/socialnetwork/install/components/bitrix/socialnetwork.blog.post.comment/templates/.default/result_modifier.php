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
include_once(__DIR__."/../mobile_app/functions.php");
$arResult["PUSH&PULL"] = false;

if(
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
			$commentId = (!!$arResult["ajax_comment"] ? $arResult["ajax_comment"] : $_REQUEST[$arParams["COMMENT_ID_VAR"]]);
		}
	}
	else if (
		$_REQUEST[$arParams["COMMENT_ID_VAR"]]
		&& in_array($_REQUEST[$arParams["COMMENT_ID_VAR"]], $arResult["IDS"])
	)
	{
		$filter = ">=ID";
		$commentId = $_REQUEST[$arParams["COMMENT_ID_VAR"]];
	}
/*
	else if (
		!empty($arParams['LOG_CONTENT_ITEM_TYPE'])
		&& $arParams['LOG_CONTENT_ITEM_TYPE'] == \Bitrix\Socialnetwork\LogIndexTable::ITEM_TYPE_COMMENT
		&& !empty($arParams['LOG_CONTENT_ITEM_ID'])
		&& intval($arParams['LOG_CONTENT_ITEM_ID']) > 0
		&& !empty($arParams['LOG_ID'])
		&& intval($arParams['LOG_ID']) > 0
	)
	{
		$res = \Bitrix\Socialnetwork\LogCommentTable::getList(array(
			'filter' => array(
				'=ID' =>  intval($arParams['LOG_CONTENT_ITEM_ID'])
			),
			'select' => array('SOURCE_ID', 'LOG_ID')
		));
		if (
			($logCommentFields = $res->fetch())
			&& intval($logCommentFields['LOG_ID']) == intval($arParams['LOG_ID'])
			&& intval($logCommentFields['SOURCE_ID']) > 0
		)
		{
			$filter = ">=ID";
			$commentId = intval($logCommentFields['SOURCE_ID']);
		}
	}
*/
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
	if (
		!$filter
		|| $filter == ">=ID"
	)
	{
		unset($_GET["commentId"]);
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
		array("#source_post_id#", "#post_id#", "#comment_id#", "&IFRAME=Y", "#LAST_LOG_TS#"),
		array($arResult["Post"]["ID"], $arResult["Post"]["ID"], 0, "", intval($arParams["LAST_LOG_TS"])),
		$arResult["urlToMore"]
	);

	while($comment = $res->Fetch())
	{
		if (empty($comment['ID']))
		{
			continue;
		}

		$arResult["RECORDS"][$comment["ID"]] = socialnetworkBlogPostCommentWeb($comment, $arParams, $arResult, $this->__component);

		if (intval($arResult["ajax_comment"]) == intval($comment["ID"]))
		{
			if ($this->__component->prepareMobileData)
			{
				$arResult["RECORDS"][$comment["ID"]]["MOBILE"] = socialnetworkBlogPostCommentMobile(
					$comment,
					$arParams,
					$arResult,
					$this->__component
				);

				if (
					!empty($comment['POST_TEXT'])
					&& \Bitrix\Main\ModuleManager::isModuleInstalled('disk')
				)
				{
					$inlineDiskObjectIdList = $inlineDiskAttachedObjectIdList = array();

					// parse inline disk object ids
					if (preg_match_all("#\\[disk file id=(n\\d+)\\]#is".BX_UTF_PCRE_MODIFIER, $comment['POST_TEXT'], $matches))
					{
						$inlineDiskObjectIdList = array_map(function($a) { return intval(substr($a, 1)); }, $matches[1]);
					}

					// parse inline disk attached object ids
					if (preg_match_all("#\\[disk file id=(\\d+)\\]#is".BX_UTF_PCRE_MODIFIER, $comment['POST_TEXT'], $matches))
					{
						$inlineDiskAttachedObjectIdList = array_map(function($a) { return intval($a); }, $matches[1]);
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
							'select' => array('ID', 'ENTITY_ID')
						));
						while ($attachedObjectFields = $res->fetch())
						{
							if (intval($attachedObjectFields['ENTITY_ID']) == intval($comment["ID"]))
							{
								$inlineDiskAttachedObjectIdImageList[] = intval($attachedObjectFields['ID']);
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
				"ACTION" => ($_GET["delete_comment_id"] == $arResult["ajax_comment"] ? "DELETE" : (
						$_GET["show_comment_id"] == $arResult["ajax_comment"] || $_GET["hide_comment_id"] == $arResult["ajax_comment"] ? "MODERATE" : (
							$_POST["act"] == "edit" ? "EDIT" : "REPLY"
						)
					)
				)
			);
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