<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\UI;

UI\Extension::load("ui.tooltip");

if($arResult["FatalError"] <> '')
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	$anchor_id = RandString(8);
	
	if ($arParams["INLINE"] != "Y")
	{
		$tooltipUserId = (
			$arResult["User"]["DETAIL_URL"] <> ''
			&& $arResult["CurrentUserPerms"]["Operations"]["viewprofile"]
			&& (
				!array_key_exists("USE_TOOLTIP", $arResult)
				|| $arResult["USE_TOOLTIP"]
			)
				? $arResult["User"]["ID"]
				: ''
		);

		if ($arResult["User"]["DETAIL_URL"] <> '' && $arResult["CurrentUserPerms"]["Operations"]["viewprofile"])
		{
			?><table cellspacing="0" cellpadding="0" border="0" id="anchor_<?=$anchor_id?>" class="bx-user-info-anchor" bx-tooltip-user-id="<?=$tooltipUserId?>"><?
		}
		else
		{
			?><table cellspacing="0" cellpadding="0" border="0" id="anchor_<?=$anchor_id?>" class="bx-user-info-anchor-nolink" bx-tooltip-user-id="<?=$tooltipUserId?>"><?
		}
		?><tr><?
		if ($arParams["USE_THUMBNAIL_LIST"] == "Y")
		{
			?><td class="bx-user-info-anchor-cell"><div class="bx-user-info-thumbnail" align="center" valign="middle" <?if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0): echo 'style="width: '.intval($arParams["THUMBNAIL_LIST_SIZE"]).'px; height: '.intval($arParams["THUMBNAIL_LIST_SIZE"]+2).'px;"'; endif;?>><?
			if ($arResult["User"]["HREF"] <> '')
			{
				?><a href="<?=$arResult["User"]["HREF"]?>"<?=($arParams["SEO_USER"] == "Y" ? ' rel="nofollow"' : '')?>><?=$arResult["User"]["PersonalPhotoImgThumbnail"]["Image"]?></a><?
			}
			elseif (
				$arResult["User"]["DETAIL_URL"] <> '' 
				&& $arResult["CurrentUserPerms"]["Operations"]["viewprofile"]
			)
			{
				?><a href="<?=$arResult["User"]["DETAIL_URL"]?>"<?=($arParams["SEO_USER"] == "Y" ? ' rel="nofollow"' : '')?>><?=$arResult["User"]["PersonalPhotoImgThumbnail"]["Image"]?></a><?
			}
			else
			{
				?><?=$arResult["User"]["PersonalPhotoImgThumbnail"]["Image"]?><?
			}
			?></div></td><?
		}
		?><td class="bx-user-info-anchor-cell" valign="top"><?
		if ($arResult["User"]["HREF"] <> '')
		{
			?><a class="bx-user-info-name" href="<?=$arResult["User"]["HREF"]?>"<?=($arParams["SEO_USER"] == "Y" ? ' rel="nofollow"' : '')?>><?=$arResult["User"]["NAME_FORMATTED"]?></a><?
		}
		elseif (
			$arResult["User"]["DETAIL_URL"] <> '' 
			&& $arResult["CurrentUserPerms"]["Operations"]["viewprofile"]
		)
		{
			?><a class="bx-user-info-name" href="<?=$arResult["User"]["DETAIL_URL"]?>"<?=($arParams["SEO_USER"] == "Y" ? ' rel="nofollow"' : '')?>><?=$arResult["User"]["NAME_FORMATTED"]?></a><?
		}
		else
		{
			?><div class="bx-user-info-name"><?=$arResult["User"]["NAME_FORMATTED"]?></div><?
		}
		?><?=($arResult["User"]["NAME_DESCRIPTION"] <> '' ? " (".$arResult["User"]["NAME_DESCRIPTION"].")": "")?><?
		if ($arResult["bSocialNetwork"])
		{
			if (array_key_exists("IS_ONLINE", $arParams))
			{
				$online_class_attrib = ($arParams["IS_ONLINE"] === true ? ' class="bx-user-info-online"' : ' class="bx-user-info-offline"');
			}
			else
			{
				$online_class_attrib = '';
			}

			if ($arResult["User"]["HREF"] <> '')
			{
				$link = $arResult["User"]["HREF"];
			}
			elseif (
				$arResult["User"]["DETAIL_URL"] <> '' 
				&& $arResult["CurrentUserPerms"]["Operations"]["viewprofile"]
			)
			{
				$link = $arResult["User"]["DETAIL_URL"];
			}
			else
			{
				$link = false;
			}
			?>
			<div class="bx-user-info-online-cell"><?
			if (!$link)
			{
				?><div id="<?=$arResult["User"]["HTML_ID"]?>"<?=$online_class_attrib?>></div><?
			}
			else
			{
				?><div id="<?=$arResult["User"]["HTML_ID"]?>"<?=$online_class_attrib?>><a href="<?=$link?>"><img src="/bitrix/images/1.gif" width="11" height="11" border="0"></a></div><?
			}
			?></div><?
		}
		?></td>
		</tr>
		</table><?
	}
	else
	{
		if (
			$arResult["User"]["DETAIL_URL"] <> '' 
			&& $arResult["CurrentUserPerms"]["Operations"]["viewprofile"]
		)
		{
			?><a href="<?=$arResult["User"]["DETAIL_URL"]?>"<?=($arParams["SEO_USER"] == "Y" ? ' rel="nofollow"' : '')?> id="anchor_<?=$anchor_id?>" bx-tooltip-user-id="<?=$arResult["User"]["ID"]?>"><?=$arResult["User"]["NAME_FORMATTED"]?></a><?
		}
		elseif ($arResult["User"]["DETAIL_URL"] <> '' && !$arResult["bSocialNetwork"])
		{
			?><a href="<?=$arResult["User"]["DETAIL_URL"]?>"<?=($arParams["SEO_USER"] == "Y" ? ' rel="nofollow"' : '')?> id="anchor_<?=$anchor_id?>"><?=$arResult["User"]["NAME_FORMATTED"]?></a><?
		}
		else
		{
			?><?=$arResult["User"]["NAME_FORMATTED"]?><?
		}
		?><?=($arResult["User"]["NAME_DESCRIPTION"] <> '' ? " (".$arResult["User"]["NAME_DESCRIPTION"].")": "")?><?
	}
}
?>