<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;

/** @var CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

foreach ($arResult['ERRORS'] as $error)
{
	ShowError($error);
}

foreach ($arResult['ROWS'] as $index => $data)
{
	foreach ($data as $dataKey => $dataValue)
	{
		if (is_string($data[$dataKey]))
		{
			$data[$dataKey] = htmlspecialcharsbx($dataValue);
		}
	}

	if ($data['TYPE_NAME'])
	{
		$data['TYPE_ID'] = $data['TYPE_NAME'];
	}

	if ($data['LETTER_TITLE'])
	{
		if ($data['URLS']['LETTER_EDIT'])
		{
			$data['LETTER_TITLE'] = '<a href="'.CUtil::JSEscape($data['URLS']['LETTER_EDIT']).'" '
				. 'onclick="BX.Sender.Page.open(\''.CUtil::JSEscape($data['URLS']['LETTER_EDIT']).'\'); return false;">'
				. $data['LETTER_TITLE'] . '</a>';
		}
		$data['LETTER_TITLE'] .= '<br>' . $data['MESSAGE_CODE'];
	}

	$actions = array();

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
		"FILTER_PRESETS" => $arResult['FILTER_PRESETS'],
		"DISABLE_SEARCH" => true,
		"ENABLE_LABEL" => true,
	)
);
$filterLayout = ob_get_clean();

$APPLICATION->IncludeComponent("bitrix:sender.ui.panel.title", "", array('LIST' => array(
	array('type' => 'filter', 'content' => $filterLayout),
	array('type' => 'buttons', 'list' => [
		[
			'type' => 'abuses',
			'href' => $arParams['PATH_TO_ABUSES'],
		],
	]),
)));


$controlPanel = array('GROUPS' => array(array('ITEMS' => array())));

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
		'ACTION_PANEL' => $controlPanel,
		"TOTAL_ROWS_COUNT" => $arResult['TOTAL_ROWS_COUNT'],
		'ALLOW_COLUMNS_SORT' => true,
		'ALLOW_COLUMNS_RESIZE' => true,
		"AJAX_MODE" => "Y",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "N",
		"AJAX_OPTION_HISTORY" => "N"
	)
);



?>
	<script type="text/javascript">
		BX.ready(function () {

			BX.Sender.ContactList.init(<?=Json::encode(array(
				'actionUri' => $arResult['ACTION_URI'],
				'messages' => $arResult['MESSAGES'],
				"gridId" => $arParams['GRID_ID'],
				'mess' => array()
			))?>);
		});
	</script>
<?