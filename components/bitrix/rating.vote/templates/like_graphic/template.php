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

\Bitrix\Main\UI\Extension::load([
	'main.rating',
]);

$classList = [
	'bx-ilike-button',
];
if ($arResult['VOTE_AVAILABLE'] !== 'Y')
{
	$classList[] = 'bx-ilike-button-disable';
}
?><span class="ilike"><?php
	?><span
		class="<?= implode(' ', $classList) ?>>"
		id="bx-ilike-button-<?= htmlspecialcharsbx($arResult['VOTE_ID']) ?>"
		data-vote-key-signed="<?= htmlspecialcharsbx($arResult['VOTE_KEY_SIGNED']) ?>"
	><?php
		$classList = [
			'bx-ilike-left-wrap',
		];
		if ($arResult['USER_HAS_VOTED'] !== 'N')
		{
			$classList[] = 'bx-you-like';
		}
		$title = (
			$arResult['VOTE_AVAILABLE'] !== 'Y'
				? 'title="' . htmlspecialcharsbx($arResult['ALLOW_VOTE']['ERROR_MSG']) . '"'
				: ''
		);
		?><span class="<?= implode(' ', $classList) ?>" <?= $title ?>><?php
			?><span class="bx-ilike-left"></span><?php
			?><span class="bx-ilike-text"><?= htmlspecialcharsEx($arResult['USER_HAS_VOTED'] === 'N' ? $arResult['RATING_TEXT_LIKE_Y'] : $arResult['RATING_TEXT_LIKE_N']) ?></span><?php
		?></span><?php
		?><span class="bx-ilike-right-wrap"><?php
			?><span class="bx-ilike-right"><?= htmlspecialcharsEx($arResult['TOTAL_VOTES']) ?></span><?php
		?></span><?php
	?></span><?php
	?><span class="bx-ilike-wrap-block" id="bx-ilike-popup-cont-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" style="display:none;"><?php
		?><span class="bx-ilike-popup"><?php
			?><span class="bx-ilike-wait"></span><?php
		?></span><?php
	?></span><?php
?></span>
<script>
BX.ready(function()
{
	<?php
	if ($arResult['AJAX_MODE'] === 'Y')
	{
		?>
		BX.loadCSS('/bitrix/components/bitrix/rating.vote/templates/like/popup.css');
		BX.loadCSS('/bitrix/components/bitrix/rating.vote/templates/like_graphic/style.css');
		<?php
	}
	?>
		if (!window.RatingLike && top.RatingLike)
		{
			window.RatingLike = top.RatingLike;
		}

		RatingLike.Set(
			{
				likeId: '<?= CUtil::JSEscape($arResult['VOTE_ID']) ?>',
				keySigned: '<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_KEY_SIGNED']))?>',
				entityTypeId: '<?= CUtil::JSEscape($arResult['ENTITY_TYPE_ID']) ?>',
				entityId: '<?= (int)$arResult['ENTITY_ID'] ?>',
				available: '<?= CUtil::JSEscape($arResult['VOTE_AVAILABLE']) ?>',
				userId: '<?= $USER->GetId() ?>',
				localize: {
					'LIKE_Y': '<?= htmlspecialcharsBx(CUtil::JSEscape($arResult['RATING_TEXT_LIKE_N'])) ?>',
					'LIKE_N': '<?= htmlspecialcharsBx(CUtil::JSEscape($arResult['RATING_TEXT_LIKE_Y'])) ?>',
					'LIKE_D': '<?= htmlspecialcharsBx(CUtil::JSEscape($arResult['RATING_TEXT_LIKE_D'])) ?>',
				},
				template: 'standart',
				pathToUserProfile: '<?= CUtil::JSEscape($arResult['PATH_TO_USER_PROFILE']) ?>'
			},
		);

});	
</script>