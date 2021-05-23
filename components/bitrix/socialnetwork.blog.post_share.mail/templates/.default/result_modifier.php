<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
foreach($arResult["AUTHORS"] as $authorId => $arAuthor)
{
	$arAuthor["AVATAR_URL"] = (
		isset($arAuthor["PERSONAL_PHOTO_resized"])
		&& isset($arAuthor["PERSONAL_PHOTO_resized"]["src"])
		&& $arAuthor["PERSONAL_PHOTO_resized"]["src"] <> ''
			? $arAuthor["PERSONAL_PHOTO_resized"]["src"]
			: '/bitrix/components/bitrix/socialnetwork.blog.post_share.mail/templates/.default/images/userpic.gif'
	);

	$arAuthor["AVATAR_COMMENT_URL"] = (
		isset($arAuthor["PERSONAL_PHOTO_resized_30"])
		&& isset($arAuthor["PERSONAL_PHOTO_resized_30"]["src"])
		&& $arAuthor["PERSONAL_PHOTO_resized_30"]["src"] <> ''
			? $arAuthor["PERSONAL_PHOTO_resized_30"]["src"]
			: '/bitrix/components/bitrix/socialnetwork.blog.post_share.mail/templates/.default/images/userpic.gif'
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

if (!empty($arResult["POST"]["ATTACHMENTS"]))
{
	$countFiles = count($arResult["POST"]["ATTACHMENTS"]);

	$arResult["POST_ATTACHMENTS_VERB_SUFFIX"] = (
		$countFiles > 1
			? 2
			: 1
	);

	$countFiles = $countFiles % 100;

	if ($countFiles < 10)
	{
		$arResult["POST_ATTACHMENTS_FILES_SUFFIX"] = $countFiles;
	}
	elseif ($countFiles <= 20)
	{
		$arResult["POST_ATTACHMENTS_FILES_SUFFIX"] = 11;
	}
	else
	{
		$arResult["POST_ATTACHMENTS_FILES_SUFFIX"] = ($countFiles % 10);
	}
}

if (
	isset($arParams["RECIPIENT_ID"])
	&& intval($arParams["RECIPIENT_ID"]) > 0
	&& CModule::IncludeModule('mail')
)
{
	if (!empty($arResult["POST"]["ATTACHMENTS"]))
	{
		foreach($arResult["POST"]["ATTACHMENTS"] as $inlineFileId => $attachment)
		{
			$backUrl = \Bitrix\Socialnetwork\ComponentHelper::getReplyToUrl($attachment["URL"], $arParams["RECIPIENT_ID"], 'ATTACHED_OBJECT', $attachment["ID"], $arResult["POST"]["BLOG_GROUP_SITE_ID"], $arParams["URL"]);
			if ($backUrl)
			{
				$arResult["POST"]["ATTACHMENTS"][$inlineFileId]["URL"] = $backUrl;
			}
		}

		$arResult["POST"]["DETAIL_TEXT_FORMATTED"] = \Bitrix\Socialnetwork\ComponentHelper::convertMailDiskFileBBCode($arResult["POST"]["DETAIL_TEXT_FORMATTED"], $arResult["POST"]["ATTACHMENTS"]);
	}

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

?>