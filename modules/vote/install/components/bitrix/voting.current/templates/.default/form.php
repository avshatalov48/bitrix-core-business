<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$id = "voting_current_".rand(100, 10000);

if ($arParams["SHOW_RESULTS"] == "Y")
{
	$this->IncludeLangFile("form.php");
	CUtil::InitJSCore();
	ob_start();
}
?><div id="<?=$id?>_form"><?$APPLICATION->IncludeComponent(
		"bitrix:voting.form",
		".default",
		Array(
			"VOTE_ID" => $arResult["VOTE_ID"],
			"VOTE_ASK_CAPTCHA" => $arParams["VOTE_ASK_CAPTCHA"] ?? null,
			"PERMISSION" => $arParams["PERMISSION"],
			"VOTE_RESULT_TEMPLATE" => $arResult["VOTE_RESULT_TEMPLATE"],
			"ADDITIONAL_CACHE_ID" => $arResult["ADDITIONAL_CACHE_ID"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		),
		($this->__component->__parent ? $this->__component->__parent : $component)
	);?></div><?
if ($arParams["SHOW_RESULTS"] == "Y")
{
	$sForm = ob_get_clean();
	?><?=preg_replace(
		"/(\<a name\=\"show_result\" )/",
		"$1 onclick=\"BX('".$id."_form').style.display='none';BX('".$id."_result').style.display='block';return false;\" ",
		$sForm);?><?
?>
<div id="<?=$id?>_result" style="display:none;"><?
	?><?$APPLICATION->IncludeComponent("bitrix:voting.result", ".default",
		Array(
			"VOTE_ID" => $arResult["VOTE_ID"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"PERMISSION" => $arParams["PERMISSION"],
			"ADDITIONAL_CACHE_ID" => $arResult["ADDITIONAL_CACHE_ID"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"VOTE_ALL_RESULTS" => $arParams["VOTE_ALL_RESULTS"],
			"CAN_VOTE" => $arParams["CAN_VOTE"]),
		($this->__component->__parent ? $this->__component->__parent : $component),
		array("HIDE_ICONS" => "Y")
	);?>
	<?if ($arParams["CAN_VOTE"] == "Y"):?>
	<div class="vote-form-box-buttons vote-vote-footer">
		<span class="vote-form-box-button vote-form-box-button-single">
			<a name="show_form" <?
				?>onclick="BX('<?=$id?>_form').style.display='block';BX('<?=$id?>_result').style.display='none';return false;" <?
					?>href="<?=$APPLICATION->GetCurPageParam("", array("VOTE_ID","VOTING_OK","VOTE_SUCCESSFULL", "view_result"))?>" <?
				?>><?=GetMessage("VOTE_BACK")?></a>
		</span>
	</div>
	<?endif;?>
</div>
<?
}
?>