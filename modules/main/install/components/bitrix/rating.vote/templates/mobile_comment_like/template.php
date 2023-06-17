<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
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
	'mobile.rating.comment',
]);

?><script>
BX.message({
	RVCPathToUserProfile: '<?=CUtil::JSEscape(htmlspecialcharsbx(str_replace("#", "(_)", $arResult['PATH_TO_USER_PROFILE'])))?>',
	RVCListBack: '<?=GetMessageJS("RATING_COMMENT_LIST_BACK")?>'
});
</script><?php
$classList = [
	'post-comment-likes' . ($arResult['USER_HAS_VOTED'] === "N" ? '' : '-liked'),
];
if (
	(int)$arResult["TOTAL_VOTES"] > 1
	|| (
		(int)($arResult['TOTAL_VOTES']) == 1
		&& $arResult['USER_HAS_VOTED'] === "N"
	)
)
{
	$classList[] = 'post-comment-liked';
}
?>
<span>
	<div
		class="<?= implode(' ', $classList) ?>"
		id="bx-ilike-button-<?= htmlspecialcharsbx($arResult['VOTE_ID']) ?>"
		data-vote-key-signed="<?= htmlspecialcharsbx($arResult['VOTE_KEY_SIGNED']) ?>"
	><?php

	$like = COption::GetOptionString("main", "rating_text_like_y", GetMessage("RATING_COMMENT_LIKE"));

	?><div class="post-comment-likes-text"><?=htmlspecialcharsEx($like)?></div><?php
	?><div class="post-comment-likes-counter" id="bx-ilike-count-<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_ID']))?>"><?php
		?><?=htmlspecialcharsEx($arResult['TOTAL_VOTES'])?><?php
	?></div><?php
	?></div>
</span><?php

?><script>
BX.ready(function() {
	var f = function() {
		new RatingLikeComments(
			'<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_ID']))?>',
			'<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['ENTITY_TYPE_ID']))?>',
			'<?=intval($arResult['ENTITY_ID'])?>',
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