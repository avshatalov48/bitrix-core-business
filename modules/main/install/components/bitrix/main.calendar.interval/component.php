<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)
	die();

/**
 * Bitrix vars
 * @global array $arResult
 * @global array $arParams
 */

$arResult["INTERVALS"] = array(
	""=>GetMessage("interface_grid_no_no_no_2"),
	"today"=>GetMessage("inerface_grid_today"),
	"yesterday"=>GetMessage("inerface_grid_yesterday"),
	"tomorrow"=>GetMessage("inerface_grid_tomorrow"),
	"week"=>GetMessage("inerface_grid_week"),
	"week_ago"=>GetMessage("inerface_grid_week_ago"),
	"month"=>GetMessage("inerface_grid_month"),
	"month_ago"=>GetMessage("inerface_grid_month_ago"),
	"days"=>GetMessage("inerface_grid_last"),
	"exact"=>GetMessage("inerface_grid_exact"),
	"after"=>GetMessage("inerface_grid_later"),
	"before"=>GetMessage("inerface_grid_earlier"),
	"interval"=>GetMessage("inerface_grid_interval"),
);

if(is_array($arParams['INTERVALS']))
{
	$arInt = array();
	foreach($arParams['INTERVALS'] as $int)
	{
		if(array_key_exists($int, $arResult["INTERVALS"]))
		{
			$arInt[$int] = $arResult["INTERVALS"][$int];
		}
	}
	$arResult["INTERVALS"] = $arInt;
}

$this->IncludeComponentTemplate();
