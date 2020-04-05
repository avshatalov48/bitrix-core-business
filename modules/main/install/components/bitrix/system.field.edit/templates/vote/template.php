<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/**
 * @var $arParams array
 * @var $arResult array
 * @var $component CBitrixComponent
 */
if (CModule::IncludeModule("vote"))
{
	if (class_exists("\\Bitrix\\Vote\\UF\\Manager"))
	{
		$GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:voting.uf",
			".default",
			array(
				'EDIT' => 'Y',
				'PARAMS' => $arParams,
				'RESULT' => $arResult,
			),
			$component->__parent,
			array("HIDE_ICONS" => "Y")
		);
	}
	else
	{
		$GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:voting.vote.edit",
			".default",
			array(
				"bVarsFromForm" => $arParams["bVarsFromForm"],
				"CHANNEL_ID" => $arParams["~arUserField"]["SETTINGS"]["CHANNEL_ID"],
				"MULTIPLE" => $arParams["~arUserField"]["MULTIPLE"],
				"INPUT_NAME" => $arParams["~arUserField"]["FIELD_NAME"],
				"INPUT_VALUE" => $arParams["~arUserField"]["VALUE"]
			),
			$component->__parent,
			array("HIDE_ICONS" => "Y")
		);
	}
}