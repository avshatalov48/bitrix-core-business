<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="voting-form-box">
	<?$APPLICATION->IncludeComponent("bitrix:voting.result", ".default",
	Array(
		"VOTE_ID" => $arResult["VOTE_ID"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"ADDITIONAL_CACHE_ID" => $arResult["ADDITIONAL_CACHE_ID"],
		"NEED_SORT" => "N",
		"CAN_VOTE" => $arParams["CAN_VOTE"]),
	($this->__component->__parent ? $this->__component->__parent : $component),
	array("HIDE_ICONS" => "Y"));?>
	<?if ($arParams["SHOW_RESULTS"] == "Y" && $arParams["CAN_VOTE"] == "Y"):?>
	<div class="vote-form-box-buttons vote-vote-footer">
		<span class="vote-form-box-button vote-form-box-button-single">
			<a name="show_form" <?
				?>href="<?=$APPLICATION->GetCurPageParam("", array("VOTE_ID","VOTING_OK","VOTE_SUCCESSFULL", "view_result"))?>" <?
				?>><?=GetMessage("VOTE_BACK")?></a>
		</span>
	</div>
	<?endif;?>
</div>