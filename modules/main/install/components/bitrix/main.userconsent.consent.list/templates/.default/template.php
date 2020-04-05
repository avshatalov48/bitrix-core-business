<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var \CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

foreach ($arResult['ERRORS'] as $error)
{
	ShowError($error);
}

foreach ($arResult['ROWS'] as $index => $data)
{
	if ($data['USER'] && $data['USER_PATH'])
	{
		$data['USER'] = '<a href="' . htmlspecialcharsbx($data['USER_PATH']) . '" target="_blank">'
			.  htmlspecialcharsbx($data['USER'])
			. '</a>';
	}

	if ($data['ORIGIN'] && $data['ORIGIN_PATH'])
	{
		$data['ORIGIN'] = '<a href="' . htmlspecialcharsbx(CUtil::JSEscape($data['ORIGIN_PATH'])) . '" target="_blank">'
			.  htmlspecialcharsbx($data['ORIGIN'])
			. '</a>';
	}
	else
	{
		$data['ORIGIN'] = '';
		if ($data['ORIGIN_ID'])
		{
			$data['ORIGIN'] .= htmlspecialcharsbx($data['ORIGIN_ID']);
		}
		if ($data['ORIGINATOR_ID'])
		{
			$data['ORIGIN'] .= $data['ORIGIN'] ? '<br>' : '';
			$data['ORIGIN'] .= '<span style="color: #C3C3C3;">' . htmlspecialcharsbx($data['ORIGINATOR_ID']) . '</span>';
		}
	}

	if ($data['URL'])
	{
		$styleString = 'max-width: 400px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;';
		$data['URL'] = '<div style="' . $styleString . '"><a href="' . htmlspecialcharsbx(CUtil::JSEscape($data['URL'])) . '" target="_blank">'
			.  htmlspecialcharsbx($data['URL'])
			. '</a></div>';
	}

	$actions = array();
	$arResult['ROWS'][$index] = array(
		'id' => $data['ID'],
		'columns' => $data,
		'actions' => $actions
	);
}

$APPLICATION->IncludeComponent(
	"bitrix:main.ui.filter",
	"",
	array(
		"FILTER_ID" => $arParams['FILTER_ID'],
		"GRID_ID" => $arParams['GRID_ID'],
		"RENDER_FILTER_INTO_VIEW" => $arParams['RENDER_FILTER_INTO_VIEW'],
		"RENDER_FILTER_INTO_VIEW_SORT" => $arParams['RENDER_FILTER_INTO_VIEW_SORT'],
		"FILTER" => $arResult['FILTERS'],
		"DISABLE_SEARCH" => true,
		"ENABLE_LABEL" => true,
	)
);

$APPLICATION->IncludeComponent(
	"bitrix:main.ui.grid",
	"",
	array(
		"GRID_ID" => $arParams['GRID_ID'],
		"COLUMNS" => $arResult['COLUMNS'],
		"ROWS" => $arResult['ROWS'],
		"NAV_OBJECT" => $arResult['NAV_OBJECT'],
		"~NAV_PARAMS" => array('SHOW_ALWAYS' => false),
		'SHOW_ROW_CHECKBOXES' => false,
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_PAGINATION' => true,
		'SHOW_SELECTED_COUNTER' => false,
		'SHOW_TOTAL_COUNTER' => true,
		"TOTAL_ROWS_COUNT" => $arResult['TOTAL_ROWS_COUNT'],
		'ALLOW_COLUMNS_SORT' => false,
		'ALLOW_COLUMNS_RESIZE' => false,
		"AJAX_MODE" => "Y",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "N",
		"AJAX_OPTION_HISTORY" => "N"
	)
);