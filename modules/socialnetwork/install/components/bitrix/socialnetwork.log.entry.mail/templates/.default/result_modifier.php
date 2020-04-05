<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$mailModuleInstalled = \Bitrix\Main\Loader::includeModule('mail');

$arResult["AUTHOR"]["AVATAR_URL"] = (
isset($arResult["LOG_ENTRY"]["AVATAR_SRC"])
&& strlen($arResult["LOG_ENTRY"]["AVATAR_SRC"]) > 0
	? $arResult["LOG_ENTRY"]["AVATAR_SRC"]
	: '/bitrix/components/bitrix/socialnetwork.log.entry.mail/templates/.default/images/userpic.gif'
);


if (!empty($arResult["LOG_ENTRY"]["ATTACHMENTS"]))
{
	$countFiles = count($arResult["LOG_ENTRY"]["ATTACHMENTS"]);

	$arResult["ATTACHMENTS_VERB_SUFFIX"] = (
	$countFiles > 1
		? 2
		: 1
	);

	$countFiles = $countFiles % 100;

	if ($countFiles < 10)
	{
		$arResult["ATTACHMENTS_FILES_SUFFIX"] = $countFiles;
	}
	elseif ($countFiles <= 20)
	{
		$arResult["ATTACHMENTS_FILES_SUFFIX"] = 11;
	}
	else
	{
		$arResult["ATTACHMENTS_FILES_SUFFIX"] = ($countFiles % 10);
	}

	if (
		isset($arParams["RECIPIENT_ID"])
		&& intval($arParams["RECIPIENT_ID"]) > 0
		&& $mailModuleInstalled
	)
	{
		foreach($arResult["LOG_ENTRY"]["ATTACHMENTS"] as $inlineFileId => $attachment)
		{
			$backUrl = \Bitrix\Socialnetwork\ComponentHelper::getReplyToUrl($attachment["URL"], $arParams["RECIPIENT_ID"], 'ATTACHED_OBJECT', $attachment["ID"], $arResult["SITE"]["ID"], $arParams["URL"]);
			if ($backUrl)
			{
				$arResult["LOG_ENTRY"]["ATTACHMENTS"][$inlineFileId]["URL"] = $backUrl;
			}
		}

		$arResult["LOG_ENTRY"]["EVENT"]["MESSAGE_FORMATTED"] = \Bitrix\Socialnetwork\ComponentHelper::convertMailDiskFileBBCode($arResult["LOG_ENTRY"]["EVENT"]["MESSAGE_FORMATTED"], $arResult["LOG_ENTRY"]["ATTACHMENTS"]);
	}
}

if (!empty($arResult["COMMENTS"]))
{
	$title = (
		empty($arResult["LOG_ENTRY"]["EVENT"]["TITLE"])
		|| $arResult["LOG_ENTRY"]["EVENT"]["TITLE"] == '__EMPTY__'
			? \CTextParser::clearAllTags($arResult["LOG_ENTRY"]["EVENT"]["MESSAGE_FORMATTED"])
			: $arResult["LOG_ENTRY"]["EVENT"]["TITLE"]
	);

	$title = preg_replace(
		'|\[MAIL\sDISK\sFILE\sID=[n]*\d+\]|',
		'',
		$title
	);

	$title = str_replace(Array("\r\n", "\n", "\r"), " ", $title);
	$arResult["LOG_ENTRY"]["TITLE_FORMATTED"] = \truncateText($title, 100);

	foreach($arResult["COMMENTS"] as $key => $comment)
	{
		$dateCreateFormatted = \Bitrix\Socialnetwork\ComponentHelper::formatDateTimeToGMT($comment['EVENT']['LOG_DATE'], $comment['EVENT']['USER_ID']);
		if (
			isset($arParams["RECIPIENT_ID"])
			&& intval($arParams["RECIPIENT_ID"]) > 0
			&& $mailModuleInstalled
		)
		{
			foreach($comment["ATTACHMENTS"] as $inlineFileId => $attachment)
			{
				$backUrl = \Bitrix\Socialnetwork\ComponentHelper::getReplyToUrl($attachment["URL"], $arParams["RECIPIENT_ID"], 'ATTACHED_OBJECT', $attachment["ID"], $arResult["SITE"]["ID"], $arParams["URL"]);
				if ($backUrl)
				{
					$comment["ATTACHMENTS"][$inlineFileId]["URL"] = $backUrl;
				}
			}

			$comment["EVENT"]["MESSAGE_FORMATTED"] = \Bitrix\Socialnetwork\ComponentHelper::convertMailDiskFileBBCode($comment["EVENT"]["MESSAGE_FORMATTED"], $comment["ATTACHMENTS"]);
		}

		$res = array(
			"ID" => $comment["EVENT"]["ID"],
			"APPROVED" => "Y",
			"AUTHOR" => array(
				"ID" => $comment["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"],
				"NAME" => $comment["CREATED_BY"]["TOOLTIP_FIELDS"]["NAME"],
				"LAST_NAME" => $comment["CREATED_BY"]["TOOLTIP_FIELDS"]["LAST_NAME"],
				"SECOND_NAME" => $comment["CREATED_BY"]["TOOLTIP_FIELDS"]["SECOND_NAME"],
				"AVATAR" => (
				!empty($comment["AVATAR_SRC"])
					? $comment["AVATAR_SRC"]
					: ""
				)
			),
			"WEB" => array(
				"POST_TIME" => $dateCreateFormatted,
				"POST_DATE" => $dateCreateFormatted,
				"POST_MESSAGE_TEXT" => $comment["EVENT"]["MESSAGE_FORMATTED"]
			),
			"FILES" => array(),
			"UF" => $comment["UF"],
		);

		$arResult["COMMENTS"][$comment["EVENT"]["ID"]] = $res;
	}
}

$arResult["LOG_ENTRY_URL_COMMENT"] = $arResult["LOG_ENTRY_URL"];
if (
	isset($arParams["COMMENT_ID"])
	&& intval($arParams["COMMENT_ID"]) > 0
)
{
	$uri = new \Bitrix\Main\Web\Uri($arResult["LOG_ENTRY_URL"]);

	$uriScheme = $uri->getScheme();
	$uriHost = $uri->getHost();
	$uriPort = $uri->getPort();
	$uriPath = $uri->getPath();
	$uriQuery = $uri->getQuery();
	$uriFragment = $uri->getFragment();

	$arResult["LOG_ENTRY_URL_COMMENT"] = $uriScheme."://".
		$uriHost.
		(!empty($uriPort) && $uriPort != 80 ? ':'.$uriPort : '').
		$uriPath.
		(!empty($uriQuery) ? '?'.$uriQuery.'&' : '?').
		'commentId='.intval($arParams["COMMENT_ID"]).
		(!empty($uriFragment) ? '#'.$uriFragment : '');
}
?>