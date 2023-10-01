<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

function forumCommentsCommentWeb(
	array $comment,
	array $arParams,
	array $arResult,
	ForumCommentsComponent $component)
{
	$res = array(
		"ID" => $comment["ID"],
		"NEW" => ($comment["NEW"] == "Y" ? "Y" : "N"),
		"APPROVED" => $comment["APPROVED"],
		"COLLAPSED" => $comment["COLLAPSED"],
		"POST_TIMESTAMP" => $comment["POST_TIMESTAMP"],
	//	"POST_TIME" => $comment["POST_TIME"],
	//	"POST_DATE" => $comment["POST_DATE"],
		"AUTHOR" => array(
			"ID" => $comment["AUTHOR_ID"],
			"NAME" => $comment["~NAME"],
			"LAST_NAME" => $comment["~LAST_NAME"],
			"SECOND_NAME" => $comment["~SECOND_NAME"],
			"LOGIN" => $comment["~LOGIN"],
			"AVATAR" => ($comment["AVATAR"] && $comment["AVATAR"]["FILE"] ? $comment["AVATAR"]["FILE"]['src'] : ""),
			"PERSONAL_GENDER" => !empty($comment["~PERSONAL_GENDER"]) ? $comment["~PERSONAL_GENDER"] : "",
			"EXTERNAL_AUTH_ID" => $comment["~EXTERNAL_AUTH_ID"] ?? null
		),
		"FILES" => $comment["FILES"] ?? null,
		"UF" => $comment["PROPS"] ?? null,
		"POST_MESSAGE_TEXT" => $comment["POST_MESSAGE_TEXT"],
		"~POST_MESSAGE_TEXT" => $comment["~POST_MESSAGE_TEXT"],
		"CLASSNAME" => "",
		"BEFORE_HEADER" => "",
		"BEFORE_ACTIONS" => "",
		"AFTER_ACTIONS" => "",
		"AFTER_HEADER" => "",
		"BEFORE" => "",
		"AFTER" => "",
		"BEFORE_RECORD" => "",
		"AFTER_RECORD" => "",
		"AUX" => $comment["AUX"] ?? '',
		"AUX_LIVE_PARAMS" => $comment["AUX_LIVE_PARAMS"] ?? [],
		"CAN_DELETE" => $comment["CAN_DELETE"] ?? "Y",
		"SHOW_MOBILE_HINTS" => $comment['SHOW_MOBILE_HINTS'] ?? 'N',
	);

	if (!empty($res["FILES"]))
	{
		foreach ($res["FILES"] as $key => $file)
		{
			$res["FILES"][$key]["URL"] = "/bitrix/components/bitrix/forum.interface/show_file.php?fid=".$file["ID"];
			if (CFile::IsImage($file["SRC"], $file["CONTENT_TYPE"]))
			{
				$res["FILES"][$key]["THUMBNAIL"] = "/bitrix/components/bitrix/forum.interface/show_file.php?fid=".$file["ID"]."&width=90&height=90";
				$res["FILES"][$key]["SRC"] = "/bitrix/components/bitrix/forum.interface/show_file.php?fid=".$file["ID"];
			}

			$res["CLASSNAME"] = "feed-com-block-uf";
		}
	}

	if ($arParams["SHOW_RATING"] == "Y")
	{
		$res["RATING_VOTE_ID"] = 'FORUM_POST_'.$res['ID'].'-'.(time()+rand(0, 1000));
	}

	if (
		empty($res["CLASSNAME"])
		&& !empty($comment["PROPS"])
		&& is_array($comment["PROPS"])
	)
	{
		foreach ($comment["PROPS"] as $FIELD_NAME => $arUserField)
		{
			if (!empty($arUserField["VALUE"]))
			{
				$res["CLASSNAME"] = "feed-com-block-uf";
				break;
			}
		}
	}

	return $res;
}