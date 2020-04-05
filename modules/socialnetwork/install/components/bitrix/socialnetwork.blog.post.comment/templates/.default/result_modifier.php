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

if(!empty($arResult["CommentsResult"]) && is_array($arResult["CommentsResult"]))
{
	$arResult["~CommentsResult"] = $arResult["CommentsResult"] = array_reverse($arResult["CommentsResult"]);
	CPageOption::SetOptionString("main", "nav_page_in_session", "N");
	$filter = false; $commentId = 0;
	if (!empty($_REQUEST["FILTER"]))
	{
		if (isset($_REQUEST["FILTER"]["<ID"]) && in_array($_REQUEST["FILTER"]["<ID"], $arResult["IDS"])) {
			$filter = "<ID";
			$commentId = $_REQUEST["FILTER"][$filter];
		} else if (isset($_REQUEST["FILTER"][">ID"]) && in_array($_REQUEST["FILTER"][">ID"], $arResult["IDS"])) {
			$filter = ">ID";
			$commentId = $_REQUEST["FILTER"][$filter];
		} else if (isset($_REQUEST["FILTER"]["ID"]) && in_array($_REQUEST["FILTER"]["ID"], $arResult["IDS"])) {
			$filter = "ID";
			$commentId = (!!$arResult["ajax_comment"] ? $arResult["ajax_comment"] : $_REQUEST[$arParams["COMMENT_ID_VAR"]]);
		}
	} else if ($_REQUEST[$arParams["COMMENT_ID_VAR"]] && in_array($_REQUEST[$arParams["COMMENT_ID_VAR"]], $arResult["IDS"])) {
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

	$arResult["NAV_RESULT"] = new CDBResult;
	$arResult["NAV_RESULT"]->InitFromArray($arResult["CommentsResult"]);
	$arResult["NAV_RESULT"]->NavStart($arParams["PAGE_SIZE"], false);
	$arResult["NAV_STRING"] = str_replace(
		array("#source_post_id#", "#post_id#", "#comment_id#", "&IFRAME=Y", "#LAST_LOG_TS#"),
		array($arResult["Post"]["ID"], $arResult["Post"]["ID"], 0, "", intval($arParams["LAST_LOG_TS"])),
		$arResult["urlToMore"]
	);

	while($comment = $arResult["NAV_RESULT"]->Fetch())
	{
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