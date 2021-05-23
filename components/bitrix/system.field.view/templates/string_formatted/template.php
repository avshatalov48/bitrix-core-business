<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 */

if ($arParams["arUserField"]["MULTIPLE"] == "Y")
{
	for($i = 0, $l = count($arResult["VALUE"]); $i < $l; $i++)
	{
		$val = $arResult["VALUE"][$i];
		$name = str_replace("[]", "[".$i."]", $arParams["arUserField"]["FIELD_NAME"]);
		if ($val != "")
		{
			echo str_replace(
				array("#VALUE#"),
				array(CUserTypeStringFormatted::GetPublicViewHTML(
					array(
						"SETTINGS" => $arParams["arUserField"]["SETTINGS"]
					),
					array(
						"NAME" => $name,
						"VALUE" => $val
					)
				)
				),
				$arParams["arUserField"]["SETTINGS"]["PATTERN"]
			);
			echo "\n<br />\n";
		}
	}
}
else
{
	echo str_replace(
		array("#VALUE#"),
		array(CUserTypeStringFormatted::GetPublicViewHTML(
			array(
				"SETTINGS" => $arParams["arUserField"]["SETTINGS"]
			),
			array(
				"NAME" => $arParams["arUserField"]["FIELD_NAME"],
				"VALUE" => $arResult["VALUE"][0]
			)
		)
		),
		$arParams["arUserField"]["SETTINGS"]["PATTERN"]
	);
}
