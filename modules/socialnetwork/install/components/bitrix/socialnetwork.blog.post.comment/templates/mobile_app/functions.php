<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;

function socialnetworkBlogPostCommentMobile(
	array $comment,
	array $arParams,
	array $arResult,
	SocialnetworkBlogPostComment $component
): array
{
	$arParams["AVATAR_SIZE"] = ((int)$arParams["AVATAR_SIZE"] ?: 58);
	$arAvatarSizes = array(
		"AVATAR_SIZE" => (int) ($arParams["AVATAR_SIZE_COMMON"] ?? $arParams["AVATAR_SIZE"]),
		"AVATAR_SIZE_COMMENT" => (int) ($arParams["AVATAR_SIZE_COMMENT"] ?? null)
	);
	$arAvatarSizes["AVATAR_SIZE"] = ($arAvatarSizes["AVATAR_SIZE"] > 0 ? $arAvatarSizes["AVATAR_SIZE"] : 100); // reference to CBlogUser::GetUserInfoArray
	$arAvatarSizes["AVATAR_SIZE_COMMENT"] = ($arAvatarSizes["AVATAR_SIZE_COMMENT"] > 0 ? $arAvatarSizes["AVATAR_SIZE_COMMENT"] : 100); // reference to CBlogUser::GetUserInfoArray
	$avatarKey = "PERSONAL_PHOTO_RESIZED";
	if ($arAvatarSizes["AVATAR_SIZE"] === $arParams["AVATAR_SIZE"])
	{
		$avatarKey = "PERSONAL_PHOTO_resized";
	}
	elseif ($arAvatarSizes["AVATAR_SIZE_COMMENT"] === $arParams["AVATAR_SIZE"])
	{
		$avatarKey = "PERSONAL_PHOTO_resized_30";
	}

	$arUser = $arResult["userCache"][$comment["AUTHOR_ID"]];
	if (!array_key_exists($avatarKey, $arUser) && (int)$arUser["PERSONAL_PHOTO"] > 0)
	{
		$arResult["userCache"][$comment["AUTHOR_ID"]][$avatarKey] = CFile::ResizeImageGet(
			$arUser["PERSONAL_PHOTO"],
			array(
				"width" => $arParams["AVATAR_SIZE"],
				"height" => $arParams["AVATAR_SIZE"]
			),
			BX_RESIZE_IMAGE_EXACT
		);
		$arUser = $arResult["userCache"][$comment["AUTHOR_ID"]];
	}

	$text = $comment["TextFormated"];

	if ($component->isWeb())
	{
		static $parser = null;
		if ($parser === null)
		{
			$parser = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
			$parser->bMobile = true;
			$parser->LAZYLOAD = (isset($arParams["LAZYLOAD"]) && $arParams["LAZYLOAD"] === "Y" ? "Y" : "N");
		}
		if (is_array($comment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"] ?? null))
		{
			$parser->arUserfields = array(
				"UF_BLOG_COMMENT_FILE" => array_merge(
					$comment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"],
					array("TAG" => "DOCUMENT ID"))
			);
		}

		$text = $parser->convert(
			$comment["POST_TEXT"],
			false,
			$comment["showedImages"],
			array(
				"HTML" => "N",
				"ANCHOR" => "Y",
				"BIU" => "Y",
				"IMG" => "Y",
				"QUOTE" => "Y",
				"CODE" => "Y",
				"FONT" => "Y",
				"LIST" => "Y",
				"SMILES" => "Y",
				"NL2BR" => "N",
				"VIDEO" => (Option::get("blog","allow_video", "Y") !== "Y" || $arParams["ALLOW_VIDEO"] !== "Y" ? "N" : "Y"),
				"SHORT_ANCHOR" => "Y"
			),
			array(
				"pathToUser" => "/mobile/users/?user_id=#user_id#"
			));

		if (
			!empty($comment["COMMENT_PROPERTIES"])
			&& !empty($comment["COMMENT_PROPERTIES"]["HIDDEN_DATA"])
			&& !empty($comment["COMMENT_PROPERTIES"]["HIDDEN_DATA"]["UF_BLOG_COMM_URL_PRV"])
			&& !empty($comment["COMMENT_PROPERTIES"]["HIDDEN_DATA"]["UF_BLOG_COMM_URL_PRV"]["VALUE"])
		)
		{
			$arUF = $comment["COMMENT_PROPERTIES"]["HIDDEN_DATA"]["UF_BLOG_COMM_URL_PRV"];

			$urlPreviewText = \Bitrix\Socialnetwork\ComponentHelper::getUrlPreviewContent($arUF, array(
				"LAZYLOAD" => $arParams["LAZYLOAD"],
				"MOBILE" => "Y",
				"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
				"PATH_TO_USER" => $arParams["~PATH_TO_USER"]
			));

			if (!empty($urlPreviewText))
			{
				$text .= $urlPreviewText;
			}
		}
	}

	$res = array(
		"ID" => $comment["ID"],
		"NEW" => (
			(
				($arParams["FOLLOW"] ?? null) !== "N"
				&& ($comment["NEW"] ?? null) === "Y"
			) ? "Y" : "N"
		),
		"APPROVED" => ($comment["PUBLISH_STATUS"] === BLOG_PUBLISH_STATUS_PUBLISH ? "Y" : "N"),
		"AUX" => (!empty($comment["AuxType"]) ? $comment["AuxType"] : ''),
		"AUX_LIVE_PARAMS" => (!empty($comment["AUX_LIVE_PARAMS"]) ? $comment["AUX_LIVE_PARAMS"] : array()),
		"POST_TIMESTAMP" => (
			!empty($comment["DATE_CREATE_TS"])
				? ($comment["DATE_CREATE_TS"] + $arResult["TZ_OFFSET"])
				: (MakeTimeStamp($comment["DATE_CREATE"]) - $arResult["TZ_OFFSET"])
		),
		"AUTHOR" => array(
			"ID" => $arUser["ID"],
			"NAME" => $arUser["~NAME"],
			"LAST_NAME" => $arUser["~LAST_NAME"],
			"SECOND_NAME" => $arUser["~SECOND_NAME"],
			"PERSONAL_GENDER" => $arUser["~PERSONAL_GENDER"],
			"AVATAR" => array_key_exists($avatarKey, $arUser) ? $arUser[$avatarKey]["src"] : '',
			"EXTERNAL_AUTH_ID" => ($arUser["EXTERNAL_AUTH_ID"] ?? false)
		),
		"FILES" => false,
		"UF" => false,
		"POST_MESSAGE_TEXT" => $text,
		"~POST_MESSAGE_TEXT" => \Bitrix\Main\Text\Emoji::decode($comment["POST_TEXT"]),
		"CLASSNAME" => "",
		"BEFORE_HEADER" => "",
		"BEFORE_ACTIONS" => "",
		"AFTER_ACTIONS" => "",
		"AFTER_HEADER" => "",
		"BEFORE" => "",
		"AFTER" => "",
		"BEFORE_RECORD" => "",
		"AFTER_RECORD" => ""
	);

	if (!empty($arResult["arImages"][$comment["ID"]]))
	{
		$res["FILES"] = array();
		foreach ($arResult["arImages"][$comment["ID"]] as $i => $val)
		{
			$t = $arResult["Images"][$i];
			$res["FILES"][] = array(
				"THUMBNAIL" => $val["small"],
				"SRC" => $val["full"],
				"FILE_SIZE" => $t["source"]["size"],
				"CONTENT_TYPE" => "image/xyz",
				"ORIGINAL_NAME" => $t["fileName"],
				"FILE_NAME" => $t["fileName"]
			);
		}
	}

	if (($comment["COMMENT_PROPERTIES"]["SHOW"] ?? null) === "Y")
	{
		$res["UF"] = ($comment["COMMENT_PROPERTIES"]["DATA"] ?? []);
		foreach ($res["UF"] as $key => $arPostField)
		{
			if (!empty($arPostField["VALUE"]))
			{
				$res["UF"][$key]['POST_ID'] = $arParams['POST_DATA']['ID'];
				$res["UF"][$key]['URL_TO_POST'] = str_replace(
					'#source_post_id#',
					$arPostField['POST_ID'] ?? '',
					$arResult['urlToPost']
				);
			}
		}
	}

	if ($arParams["SHOW_RATING"] === "Y")
	{
		$res["RATING_VOTE_ID"] = 'BLOG_COMMENT_' . $res['ID'] . '-' . (time() + random_int(0, 1000));
		$res["RATING_USER_HAS_VOTED"] = ($arResult['RATING'][$res["ID"]]["USER_HAS_VOTED"] ?? "N");
	}

	return $res;
}
