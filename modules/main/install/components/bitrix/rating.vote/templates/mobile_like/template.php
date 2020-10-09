<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$APPLICATION->AddHeadScript("/bitrix/components/bitrix/rating.vote/templates/mobile_like/script_attached.js");
?><script>
	BX.message({
		RVSessID: '<?=CUtil::JSEscape(bitrix_sessid())?>',
		RVPathToUserProfile: '<?=CUtil::JSEscape(htmlspecialcharsbx(str_replace("#", "(_)", $arResult['PATH_TO_USER_PROFILE'])))?>',
		RVListBack: '<?=CUtil::JSEscape(GetMessage("RV_T_LIST_BACK"))?>',
		RVRunEvent: '<?=(intval($arParams["VOTE_RAND"]) > 0 ? "Y" : "N")?>'
	});
</script><?
?><div class="post-item-informers post-item-inform-likes<?=($arResult['USER_HAS_VOTED'] == 'Y' ? '-active' : '')?>" id="bx-ilike-box-<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_ID']))?>"><?

	$like = COption::GetOptionString("main", "rating_text_like_y", GetMessage("RV_T_LIKE"));
	$like2 = str_replace('#LIKE#', $like, GetMessage("RV_T_LIKE2_PATTERN"));

	if (
		intval($arResult["TOTAL_VOTES"]) > 1
		|| (
			intval($arResult["TOTAL_VOTES"]) == 1
			&& $arResult['USER_HAS_VOTED'] == "N"
		)
	)
	{
		?><div class="post-item-inform-left"><?=htmlspecialcharsEx($like2)?></div><?
		?><div class="post-item-inform-right"><span class="post-item-inform-right-text"><?=htmlspecialcharsEx($arResult["TOTAL_VOTES"])?></span></div><?
	}
	else
	{
		?><div class="post-item-inform-left"><?=htmlspecialcharsEx($like)?></div><?
		?><div class="post-item-inform-right" style="display: none;"><span class="post-item-inform-right-text"><?=htmlspecialcharsEx($arResult["TOTAL_VOTES"])?></span></div><?
	}

?></div><?
?><script>
BX.ready(function() {
	if (!window.RatingLike && top.RatingLike)
	{
		RatingLike = top.RatingLike;
	}
	RatingLike.Set(
		'<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_ID']))?>', 
		'<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['ENTITY_TYPE_ID']))?>', 
		'<?=intval($arResult['ENTITY_ID'])?>',
		'<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_AVAILABLE']))?>'
	);
});
</script>