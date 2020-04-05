<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load("main.rating");

if ($arParams['MOBILE'] != 'Y')
{
	$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/rating.vote/templates/like_react/popup.css");
}
$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/rating.vote/templates/like_react/style.css");

	ob_start();
	?><span id="bx-ilike-user-reaction-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" data-value="<?=htmlspecialcharsbx($arParams['USER_REACTION'])?>" style="display: none;"></span><?
	?><span id="feed-post-emoji-icons-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" class="feed-post-emoji-icon-box"><?
		?><span
		 data-like-id="<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>"
		 class="feed-post-emoji-icon-container"
		><?
			if (!empty($arParams['REACTIONS_LIST']))
			{
				$reactionIndex = 1;
				foreach($arParams['REACTIONS_LIST'] as $key => $value)
				{
					if (intval($value) <= 0)
					{
						continue;
					}
					?><div
					 id="bx-ilike-result-reaction-<?=htmlspecialcharsbx($key)?>-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>"
					 class="feed-post-emoji-icon-item feed-post-emoji-icon-<?=htmlspecialcharsbx($key)?> feed-post-emoji-icon-item-<?=$reactionIndex?> feed-post-emoji-icon-active"
					 data-reaction="<?=htmlspecialcharsbx($key)?>"
					 data-like-id="<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>"
					 data-value="<?=intval($value)?>"
					 title="<?=htmlspecialcharsbx(\CRatingsComponentsMain::getRatingLikeMessage($key))?>"
					<?=$arParams['MOBILE'] == 'Y' ? '' : ' onmouseenter="BXRL.render.resultReactionMouseEnter(event);"'?>
					<?=$arParams['MOBILE'] == 'Y' ? '' : ' onmouseleave="BXRL.render.resultReactionMouseLeave(event);"'?>
					<?=$arParams['MOBILE'] == 'Y' ? '' : ' onclick="BXRL.render.resultReactionClick(event);"'?>
					></div><?
					$reactionIndex++;
				}
			}
		?></span><?
		?><div
			 id="bx-ilike-count-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>"
			 data-myreaction="<?=htmlspecialcharsbx($arParams['USER_REACTION'])?>"
			 class="feed-post-emoji-text-box bx-ilike-right-wrap <?=($arResult['USER_HAS_VOTED'] == 'N'? '': 'bx-you-like')?>"
			 <?=($arResult["COMMENT"] != "Y" ? 'style="display: none;"' : '')?>><?
			?><div class="feed-post-emoji-text-item bx-ilike-right<?=(intval($arResult['TOTAL_POSITIVE_VOTES']) <= 0 ? ' feed-post-emoji-text-counter-invisible' : '')?>"><?=intval($arResult['TOTAL_POSITIVE_VOTES'])?></div><?
		?></div><?
	?></span><?

	$likeReactions = ob_get_clean();

	$topCount = (!empty($arParams['TOP_DATA']) && is_array($arParams['TOP_DATA']) ? count($arParams['TOP_DATA']) : 0);
	$more = intval($arResult['TOTAL_VOTES']) - $topCount;
	$you = ($arParams['USER_HAS_VOTED'] == 'Y');

	if (
		!empty($arParams['TOP_DATA'])
		&& is_array($arParams['TOP_DATA'])
	)
	{
		foreach($arParams['TOP_DATA'] as $userData)
		{
			if (
				$you
				&& $userData['ID'] == $USER->getId()
			)
			{
				$topCount--;
			}
		}
	}

	if (
		!$you
		&& $topCount <= 0
	)
	{
		$topUsersMessage = "";
	}
	else
	{
		$topUsersMessage = Bitrix\Main\Localization\Loc::getMessage('RATING_LIKE_TOP_TEXT2_'.($you ? 'YOU_' : '').($topCount).($more > 0 ? '_MORE' : ''), array(
			"#OVERFLOW_START#" => ($arParams['MOBILE'] == 'Y' ? '<span class="feed-post-emoji-text-item-overflow">' : ''),
			"#OVERFLOW_END#" => ($arParams['MOBILE'] == 'Y' ? '</span>' : ''),
			"#MORE_START#" => ($arParams['MOBILE'] == 'Y' ? '<span class="feed-post-emoji-text-item-more">' : '&nbsp;'),
			"#MORE_END#" => ($arParams['MOBILE'] == 'Y' ? '</span>' : '')
		));
	}

	$usersData = array(
		'TOP' => array(),
		'MORE' => $more
	);

	ob_start();

	?><div class="feed-post-emoji-text-box" id="bx-ilike-top-users-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>"><?

		if (
			!empty($arParams['TOP_DATA'])
			&& is_array($arParams['TOP_DATA'])
		)
		{
			$topUserCount = 1;
			$userList = array();
			$youInTop = false;

			foreach($arParams['TOP_DATA'] as $userData)
			{
				if ($userData['ID'] == $USER->getId())
				{
					$youInTop = true;
					continue;
				}

				$topUsersMessage = str_replace('#USER_'.$topUserCount.'#', '<span class="feed-post-emoji-text-item">'.$userData['NAME_FORMATTED'].'</span>', $topUsersMessage);

				$userList[] = array(
					'ID' => intval($userData['ID']),
					'NAME_FORMATTED' => $userData['NAME_FORMATTED'],
					'WEIGHT' => floatval($userData['WEIGHT'])
				);
				$topUserCount++;
			}
			if (
				$you
				&& !$youInTop)
			{
				$usersData['MORE']--;
			}
			$usersData['TOP'] = $userList;

			$topUsersMessage = str_replace('#USERS_MORE#', '<span class="feed-post-emoji-text-item">'.$usersData['MORE'].'</span>', $topUsersMessage);

			?><?=$topUsersMessage?><?
		}

	?></div><?
	?><span style="display: none;" id="bx-ilike-top-users-data-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" data-users="<?=htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($usersData))?>"></span><?

	$likeTopUsers = ob_get_clean();

	if ($arResult["COMMENT"] == "Y")
	{
		?><div id="feed-post-emoji-top-panel-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" class="feed-post-emoji-container<?=($reactionIndex > 1 ? ' feed-post-emoji-container-nonempty' : '')?>" data-popup="N"><?
			?><?=$likeReactions?><?
		?></div><?
	}
	else
	{
		?><div id="feed-post-emoji-top-panel-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" class="feed-post-emoji-container" data-popup="N"><?
			?><?=$likeReactions?><?
			?><?=$likeTopUsers?><?
		?></div><?
	}

?><span class="bx-ilike-wrap-block bx-ilike-wrap-block-react" id="bx-ilike-popup-cont-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" style="display:none;"><?
	?><span class="bx-ilike-popup"><span class="bx-ilike-wait"></span></span><?
?></span><?
?>
<script>
BX.ready(function() {
<?if ($arResult['AJAX_MODE'] == 'Y'):?>
	BX.loadCSS('/bitrix/components/bitrix/rating.vote/templates/like_react/popup.css');
	BX.loadCSS('/bitrix/components/bitrix/rating.vote/templates/like_react/style.css');
	BX.loadScript('/bitrix/js/main/rating_like.js', function() {
<?endif;?>
		if (!window.RatingLike && top.RatingLike)
			RatingLike = top.RatingLike;

		if (typeof(RatingLike) == 'undefined')
			return false;

		if (typeof(RatingLikeInited) == 'undefined')
		{
			RatingLikeInited = true;
			RatingLike.setParams({
				pathToUserProfile: '<?=CUtil::JSEscape($arResult['PATH_TO_USER_PROFILE'])?>'
			});
		}

		RatingLike.Set(
			'<?=CUtil::JSEscape($arResult['VOTE_ID'])?>',
			'<?=CUtil::JSEscape($arResult['ENTITY_TYPE_ID'])?>',
			'<?=IntVal($arResult['ENTITY_ID'])?>',
			'<?=CUtil::JSEscape($arResult['VOTE_AVAILABLE'])?>',
			'<?=$USER->GetId()?>',
			{
				'LIKE_Y' : '<?=htmlspecialcharsBx(CUtil::JSEscape($arResult['RATING_TEXT_LIKE_Y']))?>',
				'LIKE_N' : '<?=htmlspecialcharsBx(CUtil::JSEscape($arResult['RATING_TEXT_LIKE_Y']))?>',
				'LIKE_D' : '<?=htmlspecialcharsBx(CUtil::JSEscape($arResult['RATING_TEXT_LIKE_D']))?>'
			},
			'<?=CUtil::JSEscape($arResult['LIKE_TEMPLATE'])?>',
			'<?=CUtil::JSEscape($arResult['PATH_TO_USER_PROFILE'])?>',
			false,
			<?=$arParams['MOBILE'] == 'Y' ? 'true' : 'false'?>
		);

		if (typeof(RatingLikePullInit) == 'undefined')
		{
			RatingLikePullInit = true;
			<?
			if (
				isset($arParams['MOBILE'])
				&& $arParams['MOBILE'] == 'Y'
			)
			{
				?>
				BXMobileApp.addCustomEvent("onPull-main", function(data) {
					if (data.command == 'rating_vote')
					{
						RatingLike.LiveUpdate(data.params);
					}
				});
				<?
			}
			else
			{
				?>
				BX.addCustomEvent("onPullEvent-main", function(command, params) {
					if (command == 'rating_vote')
					{
						RatingLike.LiveUpdate(params);
					}
				});
				<?
			}
            ?>
		}

<?if ($arResult['AJAX_MODE'] == 'Y'):?>
	});
<?endif;?>

});
</script>