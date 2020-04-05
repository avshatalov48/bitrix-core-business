<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!function_exists("__sbpc_bind_post_to_form"))
{
	function __sbpc_bind_post_to_form($xml_id, $form_id_get=null, $arParams)
	{
		static $form_id = null;
		if ($form_id_get !== null)
		{
			$form_id = $form_id_get;
			return;
		}
?><script type="text/javascript">BX.ready(function(){__blogLinkEntity({'<?=CUtil::JSEscape($xml_id)?>' : ['BG', <?=$arParams["ID"]?>, '<?=$arParams["LOG_ID"]?>']}, <?if ($form_id == null) { ?> window.SBPC.form.id<? } else { ?>"<?=$form_id?>"<? } ?>);});</script><?
	}
}
function socialnetworkBlogPostCommentWeb(
	array $comment,
	array $arParams,
	array $arResult,
	SocialnetworkBlogPostComment $component)
{
	$arParams["AVATAR_SIZE"] = (intval($arParams["AVATAR_SIZE"]) ?: 58);
	$arAvatarSizes = array(
		"AVATAR_SIZE" => intval(array_key_exists("AVATAR_SIZE_COMMON", $arParams) ? $arParams["AVATAR_SIZE_COMMON"] : $arParams["AVATAR_SIZE"]),
		"AVATAR_SIZE_COMMENT" => intval($arParams["AVATAR_SIZE_COMMENT"])
	);
	$arAvatarSizes["AVATAR_SIZE"] = ($arAvatarSizes["AVATAR_SIZE"] > 0 ? $arAvatarSizes["AVATAR_SIZE"] : 100); // reference to CBlogUser::GetUserInfoArray
	$arAvatarSizes["AVATAR_SIZE_COMMENT"] = ($arAvatarSizes["AVATAR_SIZE_COMMENT"] > 0 ? $arAvatarSizes["AVATAR_SIZE_COMMENT"] : 100); // reference to CBlogUser::GetUserInfoArray
	$avatarKey = "PERSONAL_PHOTO_RESIZED";
	if ($arAvatarSizes["AVATAR_SIZE"] == $arParams["AVATAR_SIZE"])
		$avatarKey = "PERSONAL_PHOTO_resized";
	else if ($arAvatarSizes["AVATAR_SIZE_COMMENT"] == $arParams["AVATAR_SIZE"])
		$avatarKey = "PERSONAL_PHOTO_resized_30";

	$arUser = $arResult["userCache"][$comment["AUTHOR_ID"]];
	if (is_array($arUser) && !array_key_exists($avatarKey, $arUser) && intval($arUser["PERSONAL_PHOTO"]) > 0)
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

	if (!$component->isWeb())
	{
		static $parser = null;
		if ($parser == null)
		{
			$parser = new blogTextParser(false, $arParams["PATH_TO_SMILE"], array("bPublic" => $arParams["bPublicPage"]));
			$parser->bMobile = false;
			$parser->LAZYLOAD = (isset($arParams["LAZYLOAD"]) && $arParams["LAZYLOAD"] == "Y" ? "Y" : "N");
		}

		if (is_array($comment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"]))
		{
			$parser->arUserfields = array(
				"UF_BLOG_COMMENT_FILE" => array_merge(
					$comment["COMMENT_PROPERTIES"]["DATA"]["UF_BLOG_COMMENT_FILE"],
					array("TAG" => "DOCUMENT ID")
				)
			);
		}

		$arParserParams = array(
			"imageWidth" => $arParams["IMAGE_MAX_WIDTH"],
			"imageHeight" => $arParams["IMAGE_MAX_HEIGHT"]
		);

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
				"VIDEO" => (COption::GetOptionString("blog","allow_video", "Y") != "Y" || $arParams["ALLOW_VIDEO"] != "Y" ? "N" : "Y"),
				"SHORT_ANCHOR" => "Y"
			),
			$arParserParams
		);

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
				"MOBILE" => "N",
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
		"NEW" => ($arParams["FOLLOW"] != "N" && $comment["NEW"] == "Y" ? "Y" : "N"),
		"APPROVED" => ($comment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH ? "Y" : "N"),
		"AUX" => (!empty($comment["AuxType"]) ? $comment["AuxType"] : ''),
		"AUX_LIVE_PARAMS" => (!empty($comment["AUX_LIVE_PARAMS"]) ? $comment["AUX_LIVE_PARAMS"] : array()),
		"POST_TIMESTAMP" => (
			!empty($comment["DATE_CREATE_TS"])
				? ($comment["DATE_CREATE_TS"] + $arResult["TZ_OFFSET"])
				: (MakeTimeStamp($comment["DATE_CREATE"]) - $arResult["TZ_OFFSET"])
		),
		"AUTHOR" => array(
			"ID" => (is_array($arUser) ? $arUser["ID"] : false),
			"NAME" => is_array($arUser) ? $arUser["~NAME"] : (!empty($comment["AuthorName"]) ? $comment["AuthorName"] : ''),
			"LAST_NAME" => is_array($arUser) ? $arUser["~LAST_NAME"] : '',
			"SECOND_NAME" => is_array($arUser) ? $arUser["~SECOND_NAME"] : '',
			"LOGIN" => is_array($arUser) ? $arUser["~LOGIN"] : '',
			"PERSONAL_GENDER" => is_array($arUser) ? $arUser["~PERSONAL_GENDER"] : '',
			"AVATAR" => is_array($arUser) && array_key_exists($avatarKey, $arUser) ? $arUser[$avatarKey]["src"] : '',
			"EXTERNAL_AUTH_ID" => (
				is_array($arUser)
				&& isset($arUser["EXTERNAL_AUTH_ID"])
					? $arUser["EXTERNAL_AUTH_ID"]
					: false
			),
			"UF_USER_CRM_ENTITY" => is_array($arUser) && array_key_exists('UF_USER_CRM_ENTITY', $arUser) ? $arUser['UF_USER_CRM_ENTITY'] : false,
		),
		"FILES" => false,
		"UF" => false,
		"POST_MESSAGE_TEXT" => $text,
		"~POST_MESSAGE_TEXT" => $comment["POST_TEXT"],
		"CLASSNAME" => (
			!empty($comment["COMMENT_PROPERTIES"]["HIDDEN_DATA"])
			&& !empty($comment["COMMENT_PROPERTIES"]["HIDDEN_DATA"])
			&& !empty($comment["COMMENT_PROPERTIES"]["HIDDEN_DATA"]["UF_BLOG_COMM_URL_PRV"])
			&& !empty($comment["COMMENT_PROPERTIES"]["HIDDEN_DATA"]["UF_BLOG_COMM_URL_PRV"]["VALUE"])
				? "feed-com-block-urlpreview"
				: ""
		),
		"BEFORE_HEADER" => "",
		"BEFORE_ACTIONS" => "",
		"AFTER_ACTIONS" => "",
		"AFTER_HEADER" => "",
		"BEFORE" => "",
		"AFTER" => "",
		"BEFORE_RECORD" => "",
		"AFTER_RECORD" => ""
	);

	$aditStyle = ($comment["AuthorIsAdmin"] == "Y" ? "blog-comment-admin" : "") .
		($comment["AuthorIsPostAuthor"] == "Y" ? "blog-comment-author" : "");
	if ($aditStyle)
	{
		$res["BEFORE_RECORD"] = "<div class='".$aditStyle."'>";
		$res["AFTER_RECORD"] = "</div>";
	}

	if(!empty($arResult["arImages"][$comment["ID"]]))
	{
		$res["FILES"] = array();
		foreach($arResult["arImages"][$comment["ID"]] as $i => $val)
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

	if($comment["COMMENT_PROPERTIES"]["SHOW"] == "Y")
	{
		$res["UF"] = $comment["COMMENT_PROPERTIES"]["DATA"];
		foreach ($res["UF"] as $key => $arPostField)
		{
			if(!empty($arPostField["VALUE"]))
			{
				$res["UF"][$key]['POST_ID'] = $arParams['POST_DATA']['ID'];
				$res["UF"][$key]['URL_TO_POST'] = str_replace('#source_post_id#', $arPostField['POST_ID'], $arResult['urlToPost']);
			}
		}
	}

	ob_start();

	?><script>
		top.text<?=$comment["ID"]?> = text<?=$comment["ID"]?> = '<?=CUtil::JSEscape($comment["POST_TEXT"])?>';
		top.title<?=$comment["ID"]?> = title<?=$comment["ID"]?> = '<?=CUtil::JSEscape($comment["TITLE"])?>';
		top.arComFiles<?=$comment["ID"]?> = [];<?

		if ($comment["COMMENT_PROPERTIES"]["DATA"])
		{
			foreach($comment["COMMENT_PROPERTIES"]["DATA"] as $userField)
			{
				if (empty($userField["VALUE"]))
					continue;
				else if ($userField["USER_TYPE_ID"] == "disk_file")
				{
					?>
					top.arComDFiles<?=$comment["ID"]?> = BX.util.array_merge((top.arComDFiles<?=$comment["ID"]?> || []), <?=CUtil::PhpToJSObject($userField["VALUE"])?>);
					<?
				}
				else if ($userField["USER_TYPE_ID"] == "webdav_element")
				{
					?>
					top.arComDocs<?=$comment["ID"]?> = BX.util.array_merge((top.arComDocs<?=$comment["ID"]?> || []), <?=CUtil::PhpToJSObject($userField["VALUE"])?>);
					<?
				}
				else if ($userField["USER_TYPE_ID"] == "file")
				{
					?>
					top.arComFilesUf<?=$comment["ID"]?> = BX.util.array_merge((top.arComDocs<?=$comment["ID"]?> || []), <?=CUtil::PhpToJSObject($userField["VALUE"])?>);
					<?
				}
			}
		}

		if (is_array($comment["COMMENT_PROPERTIES"]["HIDDEN_DATA"]))
		{
			foreach($comment["COMMENT_PROPERTIES"]["HIDDEN_DATA"] as $userField)
			{
				if (empty($userField["VALUE"]))
					continue;
				else if ($userField["USER_TYPE_ID"] == "url_preview")
				{
					?>
					top.UrlPreview<?=$comment["ID"]?> = '<?=CUtil::JSEscape($userField["VALUE"])?>';
					<?
				}
			}
		}
		if(!empty($comment["showedImages"]))
		{
			foreach($comment["showedImages"] as $imgId)
			{
				if(!empty($arResult["Images"][$imgId]))
				{
					?>top.arComFiles<?=$comment["ID"]?>.push({
						id : '<?=$imgId?>',
						name : '<?=CUtil::JSEscape($arResult["Images"][$imgId]["fileName"])?>',
						type: 'image',
						src: '<?=CUtil::JSEscape($arResult["Images"][$imgId]["source"]["src"])?>',
						thumbnail: '<?=CUtil::JSEscape($arResult["Images"][$imgId]["src"])?>',
						isImage: true
					});<?
				}
			}
		}
	?></script><?
	$res["AFTER"] .= ob_get_clean();

	if ($arParams["SHOW_RATING"] == "Y")
	{
		$res["RATING_VOTE_ID"] = 'BLOG_COMMENT_'.$res['ID'].'-'.(time()+rand(0, 1000));
		$res["RATING_USER_HAS_VOTED"] = (
			isset($arResult['RATING'][$res["ID"]])
			&& isset($arResult['RATING'][$res["ID"]]["USER_HAS_VOTED"])
				? $arResult['RATING'][$res["ID"]]["USER_HAS_VOTED"]
				: "N"
		);
	}

	return $res;
}

?>