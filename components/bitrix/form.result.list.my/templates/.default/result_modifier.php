<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

foreach ($arResult['RESULTS'] as $FORM_ID => $arFormResults)
{
	foreach ($arFormResults as $key => $arRes)
	{
		$arResult['RESULTS'][$FORM_ID][$key]['DATE_CREATE'] = $GLOBALS['DB']->FormatDate($arRes['DATE_CREATE'], CSite::GetDateFormat('FULL'), CSite::GetDateFormat('SHORT'));
	}

}
?>