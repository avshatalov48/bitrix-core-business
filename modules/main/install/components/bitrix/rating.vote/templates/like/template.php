<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

?><span class="ilike-light"><span class="bx-ilike-button <?=($arResult['VOTE_AVAILABLE'] == 'Y'? '': 'bx-ilike-button-disable')?>" id="bx-ilike-button-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>"><span class="bx-ilike-right-wrap <?=($arResult['USER_HAS_VOTED'] == 'N'? '': 'bx-you-like')?>"><span class="bx-ilike-right"><?=htmlspecialcharsEx($arResult['TOTAL_VOTES'])?></span></span><span class="bx-ilike-left-wrap" <?=($arResult['VOTE_AVAILABLE'] == 'Y'? '': 'title="'.htmlspecialcharsbx($arResult['ALLOW_VOTE']['ERROR_MSG']).'"')?>><?
	if($arResult['VOTE_AVAILABLE'] == 'Y'):
		?><a href="#like" class="bx-ilike-text"><?=($arResult['USER_HAS_VOTED'] == 'N'? htmlspecialcharsbx($arResult['RATING_TEXT_LIKE_Y']): htmlspecialcharsbx($arResult['RATING_TEXT_LIKE_Y']))?></a><?
	endif;?></span></span><span class="bx-ilike-wrap-block" id="bx-ilike-popup-cont-<?=htmlspecialcharsbx($arResult['VOTE_ID'])?>" style="display:none;"><span class="bx-ilike-popup"><span class="bx-ilike-wait"></span></span></span></span>
<script type="text/javascript">
BX.ready(function() {
<?if ($arResult['AJAX_MODE'] == 'Y'):?>
	BX.loadCSS('/bitrix/components/bitrix/rating.vote/templates/like/popup.css');
	BX.loadCSS('/bitrix/components/bitrix/rating.vote/templates/like/style.css');
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
			{'LIKE_Y' : '<?=htmlspecialcharsBx(CUtil::JSEscape($arResult['RATING_TEXT_LIKE_Y']))?>', 'LIKE_N' : '<?=htmlspecialcharsBx(CUtil::JSEscape($arResult['RATING_TEXT_LIKE_Y']))?>', 'LIKE_D' : '<?=htmlspecialcharsBx(CUtil::JSEscape($arResult['RATING_TEXT_LIKE_D']))?>'},
			'<?=CUtil::JSEscape($arResult['LIKE_TEMPLATE'])?>',
			'<?=CUtil::JSEscape($arResult['PATH_TO_USER_PROFILE'])?>'
		);

		if (typeof(RatingLikePullInit) == 'undefined')
		{
			RatingLikePullInit = true;
			BX.addCustomEvent("onPullEvent-main", function(command,params) {
				if (command == 'rating_vote')
				{
					RatingLike.LiveUpdate(params);
				}
			});
		}

<?if ($arResult['AJAX_MODE'] == 'Y'):?>
	});
<?endif;?>

});
</script>