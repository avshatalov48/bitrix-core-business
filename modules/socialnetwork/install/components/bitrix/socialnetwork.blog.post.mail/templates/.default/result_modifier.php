<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arResult["AUTHOR"]["AVATAR_URL"] = (
	isset($arResult["AUTHOR"]["PERSONAL_PHOTO_resized"])
	&& isset($arResult["AUTHOR"]["PERSONAL_PHOTO_resized"]["src"])
	&& strlen($arResult["AUTHOR"]["PERSONAL_PHOTO_resized"]["src"]) > 0
		? $arResult["AUTHOR"]["PERSONAL_PHOTO_resized"]["src"]
		: '/bitrix/components/bitrix/socialnetwork.blog.post.mail/templates/.default/images/userpic.gif'
);

$arResult["AUTHOR"]["GENDER_SUFFIX"] = "";
if ($arResult["AUTHOR"]["PERSONAL_GENDER"] == "M")
{
	$arResult["AUTHOR"]["GENDER_SUFFIX"] = "_M";
}
elseif ($arResult["AUTHOR"]["PERSONAL_GENDER"] == "F")
{
	$arResult["AUTHOR"]["GENDER_SUFFIX"] = "_F";
}

if (!empty($arResult["POST"]["ATTACHMENTS"]))
{
	$countFiles = count($arResult["POST"]["ATTACHMENTS"]);

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
		&& \Bitrix\Main\Loader::includeModule('mail')
	)
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
}
?>