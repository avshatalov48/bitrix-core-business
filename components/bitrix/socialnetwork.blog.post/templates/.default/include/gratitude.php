<?
use Bitrix\Main\Web\Uri;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var boolean $is_unread */

if (!empty($arResult['GRATITUDE']))
{
	$gratUsersCount = (!empty($arResult['GRATITUDE']['USERS_FULL']) ? count($arResult['GRATITUDE']['USERS_FULL']) : 0);

	$classNameList = [
		'feed-grat-block',
		'feed-info-block'
	];

	if ($gratUsersCount === 1)
	{
		$classNameList[] = 'feed-grat-block-one';
	}

	if ($gratUsersCount <= 2)
	{
		$classNameList[] = 'feed-grat-block-large';
	}

	?><div class="<?=implode(' ', $classNameList)?>"><?

		?><span class="feed-workday-left-side"><?
			?><div class="feed-grat-img"></div><?
			?><div class="feed-grat-block-arrow"></div><?
			?><div class="feed-user-name-wrap-outer"><?
				foreach($arResult["GRATITUDE"]["USERS_FULL"] as $gratUserFields)
				{
					$anchorId = 'post_grat_'.$gratUserFields['ID'].'_'.\Bitrix\Main\Security\Random::getString(5);

					?><span class="feed-user-name-wrap"><?
						?><div class="ui-icon ui-icon-common-user feed-user-avatar"><?
							$stylesList = [];
							if (
								isset($gratUserFields['AVATAR_SRC'])
								&& $gratUserFields['AVATAR_SRC'] <> ''
							)
							{
								$stylesList[] = "background: url('".Uri::urnEncode($gratUserFields['AVATAR_SRC'])."');";
								$stylesList[] = "background-size: cover;";
							}
							?><i style="<?=implode(' ', $stylesList)?>"></i><?
						?></div><?
						?><div class="feed-user-name-wrap-inner"><?
							?><a class="feed-workday-user-name" href="<?=($gratUserFields['URL'] ? $gratUserFields['URL'] : 'javascript:void(0);')?>"
							 id="<?=$anchorId?>"
							 bx-tooltip-user-id="<?=($gratUserFields['URL'] ? $gratUserFields['ID'] : "")?>"><?
								?><?=CUser::formatName($arParams['NAME_TEMPLATE'], $gratUserFields)?><?
							?></a><?
							?><span class="feed-workday-user-position"><?=htmlspecialcharsbx($gratUserFields['WORK_POSITION'])?></span><?
						?></div><?
					?></span><?
				}
			?></div><?
		?></span><?
	?></div><?
}
?>