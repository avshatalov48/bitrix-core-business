<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$APPLICATION->AddHeadScript("/bitrix/components/bitrix/rating.vote/templates/mobile_comment_like/script_attached.js");
?><script>
BX.message({
	RVCPathToUserProfile: '<?=CUtil::JSEscape(htmlspecialcharsbx(str_replace("#", "(_)", $arResult['PATH_TO_USER_PROFILE'])))?>',
	RVCListBack: '<?=GetMessageJS("RATING_COMMENT_LIST_BACK")?>'
});
</script>
<span><div class="post-comment-likes<?=($arResult['USER_HAS_VOTED'] == "N" ? "": "-liked")?><?
?><?=(intval($arResult["TOTAL_VOTES"]) > 1
		|| (
			intval($arResult["TOTAL_VOTES"]) == 1
			&& $arResult['USER_HAS_VOTED'] == "N"
		) ? " post-comment-liked" : "")?>" id="bx-ilike-button-<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_ID']))?>"><?

	$like = COption::GetOptionString("main", "rating_text_like_y", GetMessage("RATING_COMMENT_LIKE"));

	?><div class="post-comment-likes-text"><?=htmlspecialcharsEx($like)?></div><?
	?><div class="post-comment-likes-counter" id="bx-ilike-count-<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_ID']))?>"><?
		?><?=htmlspecialcharsEx($arResult['TOTAL_VOTES'])?><?
	?></div><?
?></div></span><?
?><script type="text/javascript">
BX.ready(function() {
	var f = function() {
		new RatingLikeComments(
			'<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_ID']))?>',
			'<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['ENTITY_TYPE_ID']))?>',
			'<?=IntVal($arResult['ENTITY_ID'])?>',
			'<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_AVAILABLE']))?>'
		);
	};
	if (!RatingLikeComments)
	{
		window["RatingLikeCommentsQueue"] = (window["RatingLikeCommentsQueue"] || []);
		window["RatingLikeCommentsQueue"].push(f);
	}
	else
	{
		f();
	}
});
</script>