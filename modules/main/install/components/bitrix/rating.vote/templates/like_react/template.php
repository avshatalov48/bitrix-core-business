<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load("main.rating");

$mobile = (isset($arParams['MOBILE']) && $arParams['MOBILE'] === 'Y');

if (!$mobile)
{
	$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/rating.vote/templates/like_react/popup.css");
}
$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/rating.vote/templates/like_react/style.css");

	ob_start();
	?><span id="bx-ilike-user-reaction-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" data-value="<?= htmlspecialcharsbx($arParams['USER_REACTION'] ?? '') ?>" style="display: none;"></span><?php
	?><span id="feed-post-emoji-icons-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" class="feed-post-emoji-icon-box"><?php
		?><span
		 data-like-id="<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>"
		 class="feed-post-emoji-icon-container"
		><?php
			$reactionIndex = 1;
			if (!empty($arParams['REACTIONS_LIST']))
			{
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
					<?=$mobile ? '' : ' onmouseenter="BXRL.render.resultReactionMouseEnter(event);"'?>
					<?=$mobile ? '' : ' onmouseleave="BXRL.render.resultReactionMouseLeave(event);"'?>
					<?=$mobile ? '' : ' onclick="BXRL.render.resultReactionClick(event);"'?>
					></div><?php
					$reactionIndex++;
				}
			}
		?></span><?php
		?><div
			 id="bx-ilike-count-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>"
			 data-myreaction="<?=htmlspecialcharsbx($arParams['USER_REACTION'])?>"
			 class="feed-post-emoji-text-box bx-ilike-right-wrap <?=($arResult['USER_HAS_VOTED'] === 'N'? '': 'bx-you-like')?>"
			 <?=(
			 	$arResult["COMMENT"] != "Y"
				&& !$mobile
				&& (!isset($arParams["TYPE"]) || $arParams["TYPE"] != 'POST')
					? 'style="display: none;"'
					: ''
			 )?>><?php
			?><div class="feed-post-emoji-text-item bx-ilike-right<?=((int)$arResult['TOTAL_POSITIVE_VOTES'] <= 0 ? ' feed-post-emoji-text-counter-invisible' : '')?>"><?=intval($arResult['TOTAL_POSITIVE_VOTES'])?></div><?php
		?></div><?php
	?></span><?php

	$likeReactions = ob_get_clean();

	$topCount = (!empty($arParams['TOP_DATA']) && is_array($arParams['TOP_DATA']) ? count($arParams['TOP_DATA']) : 0);
	$more = (int)$arResult['TOTAL_VOTES'] - $topCount;
	$you = ($arResult['USER_HAS_VOTED'] === 'Y');

	if (
		!$mobile
		&& !empty($arParams['TOP_DATA'])
		&& is_array($arParams['TOP_DATA'])
		&& $you
	)
	{
		foreach($arParams['TOP_DATA'] as $userData)
		{
			if ($userData['ID'] == $USER->getId())
			{
				$topCount--;
			}
		}
	}

	if (
		$topCount <= 0
		&& (
			$mobile
			|| !$you
		)
	)
	{
		$topUsersMessage = "";
	}
	else
	{
		if ($mobile)
		{
			$topUsersMessage = Loc::getMessage('RATING_LIKE_TOP_TEXT3_'.($topCount > 1 ? '2' : '1'), [
				"#OVERFLOW_START#" => ($mobile ? '<span class="feed-post-emoji-text-item-overflow">' : ''),
				"#OVERFLOW_END#" => ($mobile ? '</span>' : ''),
			]);
		}
		else
		{
			$topUsersMessage = Loc::getMessage('RATING_LIKE_TOP_TEXT2_'.($you ? 'YOU_' : '').($topCount).($more > 0 ? '_MORE' : ''), array(
				"#OVERFLOW_START#" => ($mobile ? '<span class="feed-post-emoji-text-item-overflow">' : ''),
				"#OVERFLOW_END#" => ($mobile ? '</span>' : ''),
				"#MORE_START#" => ($mobile ? '<span class="feed-post-emoji-text-item-more">' : '&nbsp;'),
				"#MORE_END#" => ($mobile ? '</span>' : '')
			));
		}
	}

	$usersData = array(
		'TOP' => array(),
		'MORE' => $more
	);

	ob_start();

	?><div class="feed-post-emoji-text-box" id="bx-ilike-top-users-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>"><?php

		if (
			!empty($arParams['TOP_DATA'])
			&& is_array($arParams['TOP_DATA'])
		)
		{
			$userList = [];

			if ($mobile)
			{
				foreach($arParams['TOP_DATA'] as $userData)
				{
					$userList[] = array(
						'ID' => (int)$userData['ID'],
						'NAME_FORMATTED' => ((int)$userData['ID'] === (int)$USER->getId() ? Loc::getMessage('RATING_LIKE_TOP_TEXT3_YOU') : $userData['NAME_FORMATTED']),
						'WEIGHT' => (float)$userData['WEIGHT']
					);
					continue;
				}

				usort($userList, function($a, $b) use ($USER) {

					if($a['ID'] === (int)$USER->getId())
					{
						return -1;
					}

					if ($b['ID'] === (int)$USER->getId())
					{
						return 1;
					}

					if ($a['WEIGHT'] === $b['WEIGHT'])
					{
						return 0;
					}

					return ($a['WEIGHT'] > $b['WEIGHT'] ? -1 : 1);
				});

				$userNameList = array_map(function($item) {
					return $item['NAME_FORMATTED'];
				}, $userList);

				if (count($userNameList) === 1)
				{
					$userNameBegin = $userNameList[0];
					$userNameEnd = '';
				}
				else
				{
					$userNameBegin = implode(Loc::getMessage('RATING_LIKE_TOP_TEXT3_USERLIST_SEPARATOR', [
						'#USERNAME#' => ''
					]), array_slice($userNameList, 0, count($userNameList)-1));
					$userNameEnd = $userNameList[count($userNameList)-1];
				}

				$topUsersMessage = str_replace(
					[ '#USER_LIST_BEGIN#', '#USER_LIST_END#' ],
					[ $userNameBegin, $userNameEnd ],
					$topUsersMessage
				);

				// remove yourself from serialized data
				$userList = array_filter($userList, function($item) use ($USER) {
					return ((int)$item['ID'] !== (int)$USER->getId());
				});
			}
			else
			{
				$topUserCount = 1;
				$youInTop = false;

				foreach($arParams['TOP_DATA'] as $userData)
				{
					if ((int)$userData['ID'] === (int)$USER->getId())
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

				$topUsersMessage = str_replace('#USERS_MORE#', '<span class="feed-post-emoji-text-item">'.$usersData['MORE'].'</span>', $topUsersMessage);
			}

			$usersData['TOP'] = $userList;

			?><?=$topUsersMessage?><?php
		}

	?></div><?php
	?><span style="display: none;" id="bx-ilike-top-users-data-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" data-users="<?=htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($usersData))?>"></span><?php

	$likeTopUsers = ob_get_clean();

	if ($arResult["COMMENT"] == "Y")
	{
		?><div id="feed-post-emoji-top-panel-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" class="feed-post-emoji-container<?=($reactionIndex > 1 ? ' feed-post-emoji-container-nonempty' : '')?>" data-popup="N"><?php
			?><?=$likeReactions?><?php
		?></div><?php
	}
	else
	{
		?><div id="feed-post-emoji-top-panel-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" class="feed-post-emoji-container" data-popup="N"><?php
			?><?=$likeReactions?><?php
			?><?=$likeTopUsers?><?php
		?></div><?php
	}

?><span class="bx-ilike-wrap-block bx-ilike-wrap-block-react" id="bx-ilike-popup-cont-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" style="display:none;"><?php
	?><span class="bx-ilike-popup"><span class="bx-ilike-wait"></span></span><?php
?></span><?php
?>
<script>
BX.ready(function() {
<?php
if ($arResult['AJAX_MODE'] === 'Y')
{
	?>
	BX.loadCSS('/bitrix/components/bitrix/rating.vote/templates/like_react/popup.css');
	BX.loadCSS('/bitrix/components/bitrix/rating.vote/templates/like_react/style.css');
	BX.loadScript('/bitrix/js/main/rating_like.js', function() {
	<?php
}
?>
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
			'<?=intval($arResult['ENTITY_ID'])?>',
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
			<?=$mobile ? 'true' : 'false'?>
		);

		if (typeof(RatingLikePullInit) == 'undefined')
		{
			RatingLikePullInit = true;
			<?php
			if ($mobile)
			{
				?>
				BXMobileApp.addCustomEvent("onPull-main", function(data) {
					if (data.command == 'rating_vote')
					{
						RatingLike.LiveUpdate(data.params);
					}
				});
				<?php
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
				<?php
			}
            ?>
		}

<?php
if ($arResult['AJAX_MODE'] === 'Y')
{
	?>
	});
	<?php
}
?>

});
</script>