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
use Bitrix\Main\Web\Json;

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'main.rating',
]);

$mobile = (isset($arParams['MOBILE']) && $arParams['MOBILE'] === 'Y');

if (!$mobile)
{
	$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/rating.vote/templates/like_react/popup.css');
}
$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/rating.vote/templates/like_react/style.css');

	ob_start();
	?><span id="bx-ilike-user-reaction-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" data-value="<?= htmlspecialcharsbx($arParams['USER_REACTION'] ?? '') ?>" style="display: none;"></span><?php
	?><span id="feed-post-emoji-icons-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" class="feed-post-emoji-icon-box"><?php
		?><span
		 data-like-id="<?= htmlspecialcharsbx($arResult['VOTE_ID']) ?>"
		 data-reactions-data="<?= htmlspecialcharsbx(Json::encode($arParams['REACTIONS_LIST'])) ?>"
		 class="feed-post-emoji-icon-container"
		></span><?php
		$classList = [
			'feed-post-emoji-text-box',
			'bx-ilike-right-wrap',
		];
		if ($arResult['USER_HAS_VOTED'] !== 'N')
		{
			$classList[] = 'bx-you-like';
		}
		?><div
			 id="bx-ilike-count-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>"
			 data-myreaction="<?=htmlspecialcharsbx($arParams['USER_REACTION'] ?? '')?>"
			 class="<?= implode(' ', $classList) ?>"
			 <?=(
			 	$arResult['COMMENT'] !== 'Y'
				&& !$mobile
				&& (!isset($arParams['TYPE']) || $arParams["TYPE"] !== 'POST')
					? 'style="display: none;"'
					: ''
			 )?>><?php
			$classList = [
				'feed-post-emoji-text-item',
				'bx-ilike-right'
			];
			if ((int)$arResult['TOTAL_POSITIVE_VOTES'] <= 0)
			{
				$classList[] = 'feed-post-emoji-text-counter-invisible';
			}
			?><div class="<?= implode(' ', $classList) ?>"><?= (int)$arResult['TOTAL_POSITIVE_VOTES'] ?></div><?php
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
			if ($userData['ID'] == $USER?->getId())
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
				"#OVERFLOW_START#" => '<span class="feed-post-emoji-text-item-overflow">',
				"#OVERFLOW_END#" => '</span>',
			]);
		}
		else
		{
			$topUsersMessage = Loc::getMessage('RATING_LIKE_TOP_TEXT2_'.($you ? 'YOU_' : '').($topCount).($more > 0 ? '_MORE' : ''), array(
				"#OVERFLOW_START#" => '',
				"#OVERFLOW_END#" => '',
				"#MORE_START#" => '&nbsp;',
				"#MORE_END#" => '',
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
						'NAME_FORMATTED' => ((int)$userData['ID'] === (int)$USER?->getId() ? Loc::getMessage('RATING_LIKE_TOP_TEXT3_YOU') : $userData['NAME_FORMATTED']),
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
					if ((int)$userData['ID'] === (int)$USER?->getId())
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
	?><span style="display: none;" id="bx-ilike-top-users-data-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" data-users="<?=htmlspecialcharsbx(Json::encode($usersData))?>"></span><?php

	$likeTopUsers = ob_get_clean();

	$classList = [ 'feed-post-emoji-container' ];
	if (!empty($arParams['REACTIONS_LIST']))
	{
		$classList[] = 'feed-post-emoji-container-nonempty';
	}

	?><div id="feed-post-emoji-top-panel-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" class="<?= implode(' ', $classList) ?>" data-popup="N"><?php
		?><?= $likeReactions ?><?php
		if ($arResult['COMMENT'] !== 'Y')
		{
			?><?= $likeTopUsers ?><?php
		}
	?></div><?php

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
	<?php
}
?>
	if (!window.RatingLike && top.RatingLike)
	{
		window.RatingLike = top.RatingLike;
	}

	if (BX.Type.isUndefined(RatingLike))
	{
		return false;
	}

	if (BX.Type.isUndefined(window.RatingLikeInited))
	{
		window.RatingLikeInited = true;
		window.RatingLike.setParams({
			pathToUserProfile: '<?= CUtil::JSEscape($arResult['PATH_TO_USER_PROFILE']) ?>',
		});
	}

	window.RatingLike.Set(
		{
			likeId: '<?= CUtil::JSEscape($arResult['VOTE_ID']) ?>',
			keySigned: '<?= CUtil::JSEscape($arResult['VOTE_KEY_SIGNED']) ?>',
			entityTypeId: '<?= CUtil::JSEscape($arResult['ENTITY_TYPE_ID']) ?>',
			entityId: '<?= (int) $arResult['ENTITY_ID'] ?>',
			available: '<?= CUtil::JSEscape($arResult['VOTE_AVAILABLE']) ?>',
			userId: '<?= (int)$USER?->GetId() ?>',
			localize: {
				'LIKE_Y': '<?= htmlspecialcharsBx(CUtil::JSEscape($arResult['RATING_TEXT_LIKE_Y'])) ?>',
				'LIKE_N': '<?= htmlspecialcharsBx(CUtil::JSEscape($arResult['RATING_TEXT_LIKE_Y'])) ?>',
				'LIKE_D': '<?= htmlspecialcharsBx(CUtil::JSEscape($arResult['RATING_TEXT_LIKE_D'])) ?>',
			},
			template: '<?= CUtil::JSEscape($arResult['LIKE_TEMPLATE']) ?>',
			pathToUserProfile: '<?= CUtil::JSEscape($arResult['PATH_TO_USER_PROFILE']) ?>',
			mobile: <?= ($mobile ? 'true' : 'false') ?>
		}
	);
});
</script>