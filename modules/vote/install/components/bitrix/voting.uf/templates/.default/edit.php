<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

foreach ($arResult["ATTACHES"] as $attach)
{
	?><input type="hidden" name="<?=$arParams["~arUserField"]["FIELD_NAME"]?><?=($arParams["~arUserField"]["MULTIPLE"] == "Y" ? "[]" : "")?>" value="<?=$attach["ID"]?>" /><?

	$GLOBALS["APPLICATION"]->IncludeComponent(
		"bitrix:voting.vote.edit",
		".default",
		array(
			"bVarsFromForm" => $arParams["bVarsFromForm"],
			"CHANNEL_ID" => $arParams["~arUserField"]["SETTINGS"]["CHANNEL_ID"],
			"MULTIPLE" => $arParams["~arUserField"]["MULTIPLE"],
			"INPUT_NAME" => $arParams["~arUserField"]["FIELD_NAME"]."_".$attach["ID"],
			"INPUT_VALUE" => $attach["VOTE_ID"]
		),
		null,
		array("HIDE_ICONS" => "Y")
	);
}
if ($arParams["~arUserField"]["MULTIPLE"] == "Y" || empty($arResult["ATTACHES"]))
{
	?><input type="hidden" name="<?=$arParams["~arUserField"]["FIELD_NAME"]?><?=($arParams["~arUserField"]["MULTIPLE"] == "Y" ? "[]" : "")?>" value="n0" /><?
	$GLOBALS["APPLICATION"]->IncludeComponent(
		"bitrix:voting.vote.edit",
		".default",
		array(
			"bVarsFromForm" => $arParams["bVarsFromForm"],
			"CHANNEL_ID" => $arParams["~arUserField"]["SETTINGS"]["CHANNEL_ID"],
			"MULTIPLE" => $arParams["~arUserField"]["MULTIPLE"],
			"INPUT_NAME" => $arParams["~arUserField"]["FIELD_NAME"]."_n0",
			"INPUT_VALUE" => 0
		),
		null,
		array("HIDE_ICONS" => "Y")
	);
}
