<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Web\Json;
use Bitrix\Sender\Recipient;

/** @var CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

foreach ($arResult['ERRORS'] as $error)
{
	ShowError($error);
}

foreach ($arResult['ROWS'] as $index => $data)
{
	if (!empty($data['EMAIL']) && empty($data["~EMAIL"]))
	{
		$data['~EMAIL'] = htmlspecialcharsbx($data['EMAIL']);
		$data['~EMAIL'] .= "<br><span style=\"color:grey; font-size: 10px;\">";
		$data['~EMAIL'] .= Recipient\Normalizer::normalize($data['EMAIL'], Recipient\Type::EMAIL);
		$data['~EMAIL'] .= "</span>";
	}

	if (!empty($data['PHONE']) && empty($data["~PHONE"]))
	{
		$data['~PHONE'] = htmlspecialcharsbx($data['PHONE']);
		$data['~PHONE'] .= "<br><span style=\"color:grey; font-size: 10px;\">";
		$data['~PHONE'] .= Recipient\Normalizer::normalize($data['PHONE'], Recipient\Type::PHONE);
		$data['~PHONE'] .= "</span>";
	}

	foreach ($data as $dataKey => $dataValue)
	{
		if (!is_string($data[$dataKey]))
		{
			continue;
		}

		if (substr($dataKey, 0, 1) === '~')
		{
			continue;
		}

		if (!empty($data['~' . $dataKey]))
		{
			$data[$dataKey] = $data['~' . $dataKey];
		}
		else
		{
			$data[$dataKey] = htmlspecialcharsbx($dataValue);
		}
	}

	$actions = [];
	$arResult['ROWS'][$index] = array(
		'id' => $data['ID'],
		'columns' => $data,
		'actions' => $actions
	);
}

ob_start();
$APPLICATION->IncludeComponent(
	"bitrix:main.ui.filter",
	"",
	array(
		"FILTER_ID" => $arParams['FILTER_ID'],
		"GRID_ID" => $arParams['GRID_ID'],
		"FILTER" => $arResult['FILTERS'],
		"DISABLE_SEARCH" => true,
		"ENABLE_LABEL" => true,
	)
);
$filterLayout = ob_get_clean();

$APPLICATION->IncludeComponent(
	"bitrix:sender.ui.panel.title",
	"",
	[
		'LIST' => [
			[
				'type' => 'filter',
				'content' => $filterLayout
			],
		]
	]
);

$controlPanel = array('GROUPS' => array(array('ITEMS' => array())));
$containerId = 'bx-sender-connector-result-list';
?>
<div id="<?=htmlspecialcharsbx($containerId)?>">
	<?$APPLICATION->IncludeComponent(
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
			'ACTION_PANEL' => $controlPanel,
			"TOTAL_ROWS_COUNT" => $arResult['TOTAL_ROWS_COUNT'],
			'ALLOW_COLUMNS_SORT' => true,
			'ALLOW_COLUMNS_RESIZE' => true,
			"AJAX_MODE" => "Y",
			"AJAX_OPTION_JUMP" => "N",
			"AJAX_OPTION_STYLE" => "N",
			"AJAX_OPTION_HISTORY" => "N"
		)
	);?>
</div>
<?