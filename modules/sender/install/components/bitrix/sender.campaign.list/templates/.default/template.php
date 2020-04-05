<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;

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

	if ($data['NAME'] && $data['URLS']['EDIT'])
	{
		$data['NAME'] = '<a onclick="' . "BX.Sender.Page.open('" . CUtil::JSEscape($data['URLS']['EDIT']) . "'); return false;" . '" href="' . htmlspecialcharsbx($data['URLS']['EDIT']) . '">'
			.  htmlspecialcharsbx($data['NAME'])
			. '</a>';
	}

	$actions = [];
	$actions[] = array(
		'TITLE' => $arParams['CAN_EDIT'] ? Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_EDIT_TITLE') : Loc::getMessage('SENDER_TEMPLATE_LIST_BTN_VIEW_TITLE'),
		'TEXT' => $arParams['CAN_EDIT'] ? Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_EDIT') : Loc::getMessage('SENDER_TEMPLATE_LIST_BTN_VIEW'),
		'ONCLICK' => "BX.Sender.Page.open('".CUtil::JSEscape($data['URLS']['EDIT'])."')",
		'DEFAULT' => true
	);
	if ($arParams['CAN_EDIT'])
	{
		$actions[] = array(
			'TITLE' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_REMOVE_TITLE'),
			'TEXT' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_REMOVE'),
			'ONCLICK' => "BX.Sender.CampaignList.remove({$data['ID']});"
		);
	}

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
		'ENABLE_LIVE_SEARCH' => true,
		"ENABLE_LABEL" => true,
	)
);
$filterLayout = ob_get_clean();


$APPLICATION->IncludeComponent("bitrix:sender.ui.panel.title", "", array('LIST' => array(
	array('type' => 'filter', 'content' => $filterLayout),
	array('type' => 'buttons', 'list' => [
		$arParams['CAN_EDIT']
			?
			[
				'type' => 'add',
				'id' => 'SENDER_BUTTON_ADD',
				'caption' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_ADD'),
				'href' => $arParams['PATH_TO_ADD']
			]
			:
			null
	]),
)));

$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
$controlPanel = array('GROUPS' => array(array('ITEMS' => array())));
if ($arParams['CAN_EDIT'])
{
	$button = $snippet->getRemoveButton();
	$button['ONCHANGE'][0]['DATA'][0]['JS'] = 'BX.Sender.CampaignList.removeSelected()';
	$controlPanel['GROUPS'][0]['ITEMS'][] = $button;
}

$APPLICATION->IncludeComponent(
	"bitrix:main.ui.grid",
	"",
	array(
		"GRID_ID" => $arParams['GRID_ID'],
		"COLUMNS" => $arResult['COLUMNS'],
		"ROWS" => $arResult['ROWS'],
		"NAV_OBJECT" => $arResult['NAV_OBJECT'],
		"~NAV_PARAMS" => array('SHOW_ALWAYS' => false),
		'SHOW_ROW_CHECKBOXES' => $arParams['CAN_EDIT'],
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_PAGINATION' => true,
		'SHOW_SELECTED_COUNTER' => true,
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
			BX.Sender.CampaignList.init(<?=Json::encode(array(
				'actionUri' => $arResult['ACTION_URI'],
				"gridId" => $arParams['GRID_ID'],
				"pathToEdit" => $arParams['PATH_TO_EDIT'],
				'mess' => array()
			))?>);
		});
	</script>
<?