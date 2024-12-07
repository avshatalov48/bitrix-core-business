<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');

if ($arResult["FatalErrorMessage"] <> '')
{
	ShowError($arResult["FatalErrorMessage"]);
}
else
{
	if ($arResult["ErrorMessage"] <> '')
	{
		ShowError($arResult["ErrorMessage"]);
	}

	$APPLICATION->IncludeComponent(
		"bitrix:main.ui.grid",
		"",
		array(
			"GRID_ID"=>$arResult["GRID_ID"],
			"HEADERS"=>$arResult["HEADERS"],
			"SORT"=>$arResult["SORT"],
			"ROWS"=>$arResult["RECORDS"],
			"SHOW_CHECK_ALL_CHECKBOXES" => false,
			"SHOW_ROW_CHECKBOXES" => false,
			"SHOW_SELECTED_COUNTER" => false,
			"TOTAL_ROWS_COUNT" => count($arResult["RECORDS"]),
			'AJAX_ID' => isset($arParams['AJAX_ID']) ? $arParams['AJAX_ID'] : '',
			'AJAX_MODE' => "Y",
			"AJAX_OPTION_JUMP" => "N",
			"AJAX_OPTION_STYLE" => "N",
			"AJAX_OPTION_HISTORY" => "N",
			"FILTER"=>$arResult["FILTER"],
		),
		$component
	);
}
