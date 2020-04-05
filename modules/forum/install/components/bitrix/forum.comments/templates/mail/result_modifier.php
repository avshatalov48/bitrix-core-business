<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var CMain $APPLICATION
 * @var array $arResult
 * @var array $arParams
 * @var CBitrixComponentTemplate $this
 * @var ForumCommentsComponent $this->__component
 */

$arResult["COMMENTS_COUNT"] = 3;
$arResult["COMMENTS_ALL_COUNT"] = 0;

if (!empty($arResult["MESSAGES"]))
{
	$arResult["COMMENTS_ALL_COUNT"] = count($arResult["MESSAGES"]);
	$arResult["MESSAGES"] = array_slice($arResult["MESSAGES"], 0, $arResult["COMMENTS_COUNT"], true);
	/**
	 * @var $parser forumTextParser
	 */
	$parser = $arResult["PARSER"];
	$parser->arFiles = $arResult["FILES"];

	$forumSiteId = false;

	$arForumPaths = CForumNew::GetSites($arParams["FORUM_ID"]);
	if (!empty($arForumPaths))
	{
		$forumSiteId = array_shift(array_keys($arForumPaths));
	}

	$arParams["SITE_ID"] = $forumSiteId;
	foreach($arResult["MESSAGES"] as $key => $comment)
	{
		$dateCreateFormatted = \Bitrix\Socialnetwork\ComponentHelper::formatDateTimeToGMT($comment['POST_DATE'], $comment['AUTHOR_ID']);

		$parser->arUserfields = $comment["PROPS"];
		$text = $parser->convert4mail(
			$comment["~POST_MESSAGE_TEXT"],
			false,
			$comment["ALLOW"],
			array(
				"RECIPIENT_ID" => $arParams["RECIPIENT_ID"],
				"SITE_ID" => $forumSiteId
			));

		if (isset($comment["PROPS"]["UF_FORUM_MES_URL_PRV"]))
		{
			unset($comment["PROPS"]["UF_FORUM_MES_URL_PRV"]);
		}

		$res = array(
			"ID" => $comment["ID"],
			"APPROVED" => $comment["APPROVED"],
			"AUTHOR" => array(
				"ID" => $comment["AUTHOR_ID"],
				"NAME" => $comment["~NAME"],
				"LAST_NAME" => $comment["~LAST_NAME"],
				"SECOND_NAME" => $comment["~SECOND_NAME"],
				"AVATAR" => (
					$comment["AVATAR"] && $comment["AVATAR"]["FILE"]
						? $comment["AVATAR"]["FILE"]['src'] :
						""
				)
			),
			"WEB" => array(
				"POST_TIME" => $dateCreateFormatted,
				"POST_DATE" => $dateCreateFormatted,
				"POST_MESSAGE_TEXT" => $text
			),
			"FILES" => $comment["FILES"],
			"UF" => $comment["PROPS"],
		);

		$arResult["MESSAGES"][$key] = $res;

	}
}