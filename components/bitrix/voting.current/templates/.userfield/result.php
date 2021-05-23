<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->IncludeLangFile("result.php");
$params = $APPLICATION->IncludeComponent(
	"bitrix:voting.result",
	".default",
	Array(
		"VOTE_ID" => $arResult["VOTE_ID"],
		"PERMISSION" => $arParams["PERMISSION"],
		"VOTE_ALL_RESULTS" => "N",
		"NEED_SORT" => "N",
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"ADDITIONAL_CACHE_ID" => $arResult["ADDITIONAL_CACHE_ID"],
		"UID" => $arParams["UID"],
		"NAME_TEMPLATE" => $arParams["~NAME_TEMPLATE"],
		"PATH_TO_USER" => $arParams["~PATH_TO_USER"]),
	($this->__component->__parent ? $this->__component->__parent : $component),
	array("HIDE_ICONS" => "Y")
);
$this->__component->params = $params + array("uid" => $arParams["UID"]);
ob_start();
if ($arResult["VOTE"]["LAMP"] == "green" && $arParams["CAN_REVOTE"] == "Y" || $arParams["CAN_VOTE"] == "Y")
{
		?><a href="<?=$APPLICATION->GetCurPageParam("", $arParams["GET_KILL"])?>" id="vote-<?=$arParams["UID"]?>-revote" class="bx-vote-block-link" <?
		?>><?=($arParams["CAN_REVOTE"] == "Y" ? GetMessage("VOTE_RESUBMIT_BUTTON") : GetMessage("VOTE_SUBMIT_BUTTON"))?></a><?
}
if ($arParams["PERMISSION"] >= 4)
{
	?><a href="<?=$APPLICATION->GetCurPageParam(($arResult["VOTE"]["LAMP"] == "green" ? "stopVoting" : "resumeVoting")."=".$arResult["VOTE"]["ID"], $arParams["GET_KILL"])?>" <?
		?>id="vote-<?=$arParams["UID"]?>-<?=($arResult["VOTE"]["LAMP"] == "green" ? "stop" : "resume")?>" class="bx-vote-block-link"><?=($arResult["VOTE"]["LAMP"] == "green" ? GetMessage("VOTE_STOP_BUTTON") : GetMessage("VOTE_RESUME_BUTTON"))?></a><?
}
$res = ob_get_clean();
if (!empty($res))
{
	?><div class="bx-vote-bottom-block"><?=$res?></div><?
}
?>