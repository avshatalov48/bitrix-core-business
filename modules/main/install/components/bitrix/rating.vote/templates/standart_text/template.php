<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<span class="bx-rating<?=($arResult['VOTE_AVAILABLE'] == 'N' ? ' bx-rating-disabled' : '')?><?=($arResult['USER_HAS_VOTED'] == 'Y' ? ' bx-rating-active' : '')?>" id="bx-rating-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" title="<?=($arResult['VOTE_AVAILABLE'] == 'N'? htmlspecialcharsbx($arResult['ALLOW_VOTE']['ERROR_MSG']) : '')?>"><span class="bx-rating-absolute"><span class="bx-rating-question"><?=($arResult['VOTE_AVAILABLE'] == 'N'? $arResult['RATING_TEXT_D'] : $arResult['RATING_TEXT_A'])?></span><span class="bx-rating-yes<?=($arResult['VOTE_BUTTON'] == 'PLUS'? ' bx-rating-yes-active': '')?>" title="<?=$arResult['VOTE_AVAILABLE'] == 'N'? '': ($arResult['VOTE_BUTTON'] == 'PLUS'? GetMessage("RATING_COMPONENT_CANCEL"): GetMessage("RATING_COMPONENT_PLUS"))?>"><a class="bx-rating-yes-count" href="javascript:void(0)"><?=intval($arResult['TOTAL_POSITIVE_VOTES'])?></a><a class="bx-rating-yes-text" href="javascript:void(0)"><?=$arResult['RATING_TEXT_Y']?></a></span><span class="bx-rating-separator">/</span><span class="bx-rating-no<?=($arResult['VOTE_BUTTON'] == 'MINUS'? ' bx-rating-no-active': '')?>" title="<?=$arResult['VOTE_AVAILABLE'] == 'N'? '': ($arResult['VOTE_BUTTON'] == 'MINUS'? GetMessage("RATING_COMPONENT_CANCEL"): GetMessage("RATING_COMPONENT_MINUS"))?>"><a class="bx-rating-no-count" href="javascript:void(0)"><?=intval($arResult['TOTAL_NEGATIVE_VOTES'])?></a><a class="bx-rating-no-text" href="javascript:void(0)"><?=$arResult['RATING_TEXT_N']?></a></span></span></span>
<span id="bx-rating-popup-cont-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>-plus" style="display:none;"><span class="bx-ilike-popup bx-rating-popup"><span class="bx-ilike-wait"></span></span></span>
<span id="bx-rating-popup-cont-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>-minus" style="display:none;"><span class="bx-ilike-popup bx-rating-popup"><span class="bx-ilike-wait"></span></span></span>
<script type="text/javascript">
BX.ready(function() {
<?if ($arResult['AJAX_MODE'] == 'Y'):?>
	BX.loadCSS('/bitrix/components/bitrix/rating.vote/templates/standart_text/style.css');
	BX.loadScript('/bitrix/js/main/rating.js', function() {
<?endif;?>
	if (!window.Rating && top.Rating)
		Rating = top.Rating;
	Rating.Set(
		'<?=CUtil::JSEscape($arResult['VOTE_ID'])?>',
		'<?=CUtil::JSEscape($arResult['ENTITY_TYPE_ID'])?>',
		'<?=intval($arResult['ENTITY_ID'])?>',
		'<?=CUtil::JSEscape($arResult['VOTE_AVAILABLE'])?>',
		'<?=$USER->GetId()?>',
		{'PLUS' : '<?=GetMessageJS("RATING_COMPONENT_PLUS")?>', 'MINUS' : '<?=GetMessageJS("RATING_COMPONENT_MINUS")?>', 'CANCEL' : '<?=GetMessageJS("RATING_COMPONENT_CANCEL")?>'},
		'light',
		'<?=CUtil::JSEscape($arResult['PATH_TO_USER_PROFILE'])?>'
	);
<?if ($arResult['AJAX_MODE'] == 'Y'):?>
	});
<?endif;?>
});
</script>