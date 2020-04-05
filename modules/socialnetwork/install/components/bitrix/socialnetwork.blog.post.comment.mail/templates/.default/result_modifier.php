<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

foreach($arResult["AUTHORS"] as $authorId => $arAuthor)
{
	$arAuthor["AVATAR_URL"] = (
	isset($arAuthor["PERSONAL_PHOTO_resized"])
	&& isset($arAuthor["PERSONAL_PHOTO_resized"]["src"])
	&& strlen($arAuthor["PERSONAL_PHOTO_resized"]["src"]) > 0
		? $arAuthor["PERSONAL_PHOTO_resized"]["src"]
		: '/bitrix/components/bitrix/socialnetwork.blog.post.comment.mail/templates/.default/images/userpic.gif'
	);

	$arAuthor["AVATAR_COMMENT_URL"] = (
	isset($arAuthor["PERSONAL_PHOTO_resized_30"])
	&& isset($arAuthor["PERSONAL_PHOTO_resized_30"]["src"])
	&& strlen($arAuthor["PERSONAL_PHOTO_resized_30"]["src"]) > 0
		? $arAuthor["PERSONAL_PHOTO_resized_30"]["src"]
		: false
	);

	$arAuthor["GENDER_SUFFIX"] = "";
	if ($arAuthor["PERSONAL_GENDER"] == "M")
	{
		$arAuthor["GENDER_SUFFIX"] = "_M";
	}
	elseif ($arAuthor["PERSONAL_GENDER"] == "F")
	{
		$arAuthor["GENDER_SUFFIX"] = "_F";
	}

	$arResult["AUTHORS"][$authorId] = $arAuthor;
}

if (
	isset($arParams["RECIPIENT_ID"])
	&& intval($arParams["RECIPIENT_ID"]) > 0
	&& CModule::IncludeModule('mail')
)
{
	if (!empty($arResult["COMMENTS"]))
	{
		foreach ($arResult["COMMENTS"] as $commentKey => $arComment)
		{
			if (!empty($arComment["ATTACHMENTS"]))
			{
				foreach($arComment["ATTACHMENTS"] as $inlineFileId => $attachment)
				{
					$backUrl = \Bitrix\Socialnetwork\ComponentHelper::getReplyToUrl($attachment["URL"], $arParams["RECIPIENT_ID"], 'ATTACHED_OBJECT', $attachment["ID"], $arResult["POST"]["BLOG_GROUP_SITE_ID"], $arParams["URL"]);
					if ($backUrl)
					{
						$arResult["COMMENTS"][$commentKey]["ATTACHMENTS"][$inlineFileId]["URL"] = $backUrl;
					}
				}

				$arResult["COMMENTS"][$commentKey]["POST_TEXT_FORMATTED"] = \Bitrix\Socialnetwork\ComponentHelper::convertMailDiskFileBBCode($arResult["COMMENTS"][$commentKey]["POST_TEXT_FORMATTED"], $arResult["COMMENTS"][$commentKey]["ATTACHMENTS"]);
			}
		}
	}
}

$arResult["RECORDS"] = array();
foreach($arResult["COMMENTS"] as $key => $arComment)
{
	$authorId = $arComment["AUTHOR_ID"];
	$arResult["RECORDS"][$arComment["ID"]] = array(
		"ID" => $arComment["ID"],
		"APPROVED" => "Y",
		"AUTHOR" => array(
			"ID" => $arResult["AUTHORS"][$authorId]["ID"],
			"NAME" => $arResult["AUTHORS"][$authorId]["NAME"],
			"LAST_NAME" => $arResult["AUTHORS"][$authorId]["LAST_NAME"],
			"SECOND_NAME" => $arResult["AUTHORS"][$authorId]["SECOND_NAME"],
			"LOGIN" => $arResult["AUTHORS"][$authorId]["LOGIN"],
			"AVATAR" => $arResult["AUTHORS"][$authorId]["AVATAR_COMMENT_URL"],
			"EXTERNAL_AUTH_ID" => $arResult["AUTHORS"][$authorId]["EXTERNAL_AUTH_ID"]
		),
		"ATTACHMENTS" => $arComment["ATTACHMENTS"],
		"WEB" => array(
			"POST_TIME" => $arComment["DATE_CREATE_FORMATTED"],
			"POST_DATE" => $arComment["DATE_CREATE_FORMATTED"],
			"POST_MESSAGE_TEXT" => $arComment["POST_TEXT_FORMATTED"]
		),
		"UF" => $arComment["PROPS"]
	);
}

$arResult["POST_URL_COMMENT"] = $arResult["POST_URL"];
if (
	isset($arParams["COMMENT_ID"])
	&& intval($arParams["COMMENT_ID"]) > 0
)
{
	$uri = new \Bitrix\Main\Web\Uri($arResult["POST_URL"]);

	$uriScheme = $uri->getScheme();
	$uriHost = $uri->getHost();
	$uriPort = $uri->getPort();
	$uriPath = $uri->getPath();
	$uriQuery = $uri->getQuery();
	$uriFragment = $uri->getFragment();

	$arResult["POST_URL_COMMENT"] = $uriScheme."://".
		$uriHost.
		(!empty($uriPort) && $uriPort != 80 ? ':'.$uriPort : '').
		$uriPath.
		(!empty($uriQuery) ? '?'.$uriQuery.'&' : '?').
		'commentId='.intval($arParams["COMMENT_ID"]).
		(!empty($uriFragment) ? '#'.$uriFragment : '');
}
?>