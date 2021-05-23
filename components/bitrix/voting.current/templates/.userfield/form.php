<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
	$this->IncludeLangFile("form.php");
	$params = $APPLICATION->IncludeComponent(
		"bitrix:voting.form",
		".default",
		Array(
			"VOTE_ID" => $arResult["VOTE_ID"],
			"VOTE_ASK_CAPTCHA" => $arParams["VOTE_ASK_CAPTCHA"],
			"PERMISSION" => $arParams["PERMISSION"],
			"VOTE_RESULT_TEMPLATE" => $arResult["VOTE_RESULT_TEMPLATE"],
			"ADDITIONAL_CACHE_ID" => $arResult["ADDITIONAL_CACHE_ID"],
			"UID" => $arParams["UID"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		),
		($this->__component->__parent ? $this->__component->__parent : $component),
		array("HIDE_ICONS" => "Y")
	);
	$this->__component->params = $params + array("uid" => $arParams["UID"]);
?>
<div class="bx-vote-bottom-block">
		<a href="javascript:void(0);" class="feed-add-button feed-add-com-button" id="vote-<?=$arParams["UID"]?>-act" <?
			?>onmousedown="BX.addClass(this, 'feed-add-button-press')" <?
			?>onmouseup="BX.removeClass(this,'feed-add-button-press')"><?
			?><?=GetMessage("VOTE_SUBMIT_BUTTON")?><?
		?></a><?
		?><a class="bx-vote-block-link" href="<?=$APPLICATION->GetCurPageParam("view_result=Y", $arParams["GET_KILL"])?>" id="vote-<?=$arParams["UID"]?>-results" ><?=GetMessage("VOTE_RESULTS")?></a><?
?></div>
