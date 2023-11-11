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

$classList = [
	'rating-vote',
];
if ($arResult['VOTE_AVAILABLE'] === 'N')
{
	$classList[] = 'rating-vote-disabled';
}
?>
<span
	id="rating-vote-<?= htmlspecialcharsbx($arResult['VOTE_ID']) ?>"
	data-vote-key-signed="<?= htmlspecialcharsbx($arResult['VOTE_KEY_SIGNED']) ?>"
	class="<?= implode(' ', $classList) ?>"
	title="<?= ($arResult['VOTE_AVAILABLE'] === 'N' ? htmlspecialcharsbx($arResult['ALLOW_VOTE']['ERROR_MSG']) : '') ?>"
>
	<span id="rating-vote-<?= htmlspecialcharsbx($arResult['VOTE_ID']) ?>-result" class="rating-vote-result rating-vote-result-<?= ($arResult['TOTAL_VALUE'] < 0 ? 'minus' : 'plus') ?>" title="<?= htmlspecialcharsbx($arResult['VOTE_TITLE']) ?>"> <?= htmlspecialcharsbx($arResult['TOTAL_VALUE']) ?></span>
	<?php
	$classList = [ 'rating-vote-plus' ];
	if ($arResult['VOTE_BUTTON'] === 'PLUS')
	{
		$classList[] = 'rating-vote-plus-active';
	}

	$title = (
		$arResult['VOTE_AVAILABLE'] === 'N'
			? ''
			: (
				$arResult['VOTE_BUTTON'] === 'PLUS'
					? GetMessage("RATING_COMPONENT_CANCEL")
					: GetMessage("RATING_COMPONENT_PLUS")
			)
	);
	?>
	<a id="rating-vote-<?= htmlspecialcharsbx($arResult['VOTE_ID']) ?>-plus" class="<?= implode(' ', $classList) ?>" title="<?= $title ?>"></a>&nbsp;<?php
	$classList = [ 'rating-vote-minus' ];
	if ($arResult['VOTE_BUTTON'] === 'MINUS')
	{
		$classList[] = 'rating-vote-minus-active';
	}
	$title = (
		$arResult['VOTE_AVAILABLE'] === 'N'
			? ''
			: (
				$arResult['VOTE_BUTTON'] === 'MINUS'
					? GetMessage("RATING_COMPONENT_CANCEL")
					: GetMessage("RATING_COMPONENT_MINUS")
			)
	);
	?><a id="rating-vote-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>-minus" class="<?= implode(' ', $classList) ?>"  title="<?= $title ?>"></a>
</span>
<script>
BX.ready(function(){
	<?php
	if ($arResult['AJAX_MODE'] === 'Y')
	{
		?>
		BX.loadCSS('/bitrix/components/bitrix/rating.vote/templates/standart/style.css');
		BX.loadScript('/bitrix/js/main/rating.js', function() {
		<?php
	}
	?>
	if (!window.Rating && top.Rating)
	{
		window.Rating = top.Rating;
	}

	window.Rating.Set(
		'<?= CUtil::JSEscape($arResult['VOTE_ID']) ?>',
		'<?= CUtil::JSEscape($arResult['ENTITY_TYPE_ID']) ?>',
		'<?= (int)$arResult['ENTITY_ID'] ?>',
		'<?= CUtil::JSEscape($arResult['VOTE_AVAILABLE']) ?>',
		'<?= $USER->GetId() ?>',
		{
			'PLUS': '<?= GetMessageJS("RATING_COMPONENT_PLUS") ?>',
			'MINUS': '<?= GetMessageJS("RATING_COMPONENT_MINUS") ?>',
			'CANCEL': '<?= GetMessageJS("RATING_COMPONENT_CANCEL") ?>',
		},
		'standart',
		'<?= CUtil::JSEscape($arResult['PATH_TO_USER_PROFILE']) ?>'
	);

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