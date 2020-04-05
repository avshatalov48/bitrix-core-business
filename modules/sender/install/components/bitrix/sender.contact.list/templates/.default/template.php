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

	$data['STAT'] = '';
	if ($data['HAS_STATISTICS'])
	{
		$data['STAT'] = '<a href="'.CUtil::JSEscape($data['URLS']['RECIPIENT']).'" '
			. 'onclick="BX.Sender.Page.open(\''.CUtil::JSEscape($data['URLS']['RECIPIENT']).'\'); return false;">'
			. Loc::getMessage('SENDER_CONTACT_LIST_BTN_RECIPIENT_STAT') . '</a>';
	}

	$actions = [];
	$actions[] = [
		'TITLE' => $arParams['CAN_EDIT'] ? Loc::getMessage('SENDER_CONTACT_LIST_BTN_EDIT_TITLE') : Loc::getMessage('SENDER_CONTACT_LIST_BTN_VIEW_TITLE'),
		'TEXT' => $arParams['CAN_EDIT'] ? Loc::getMessage('SENDER_CONTACT_LIST_BTN_EDIT') : Loc::getMessage('SENDER_CONTACT_LIST_BTN_VIEW'),
		'ONCLICK' => "BX.Sender.Page.open('".CUtil::JSEscape($data['URLS']['EDIT'])."')",
		'DEFAULT' => true
	];
	if ($arParams['CAN_EDIT'])
	{
		if ($data['BLACKLISTED'] === 'Y')
		{
			$actions[] = array(
				'TITLE' => Loc::getMessage('SENDER_CONTACT_LIST_BTN_REMOVE_FROM_BL_TITLE'),
				'TEXT' => Loc::getMessage('SENDER_CONTACT_LIST_BTN_REMOVE_FROM_BL'),
				'ONCLICK' => "BX.Sender.ContactList.removeFromBlacklist({$data['ID']});"
			);
		}
		else
		{
			$actions[] = array(
				'TITLE' => Loc::getMessage('SENDER_CONTACT_LIST_BTN_ADD_TO_BL'),
				'TEXT' => Loc::getMessage('SENDER_CONTACT_LIST_BTN_ADD_TO_BL_TITLE'),
				'ONCLICK' => "BX.Sender.ContactList.addToBlacklist({$data['ID']});"
			);
		}

		if ($arParams['LIST_ID'])
		{
			$actions[] = array(
				'TITLE' => Loc::getMessage('SENDER_CONTACT_LIST_BTN_REMOVE_FROM_LIST_TITLE'),
				'TEXT' => Loc::getMessage('SENDER_CONTACT_LIST_BTN_REMOVE_FROM_LIST'),
				'ONCLICK' => "BX.Sender.ContactList.removeFromList({$data['ID']}, {$arParams['LIST_ID']});"
			);
		}
		else
		{
			$actions[] = array(
				'TITLE' => Loc::getMessage('SENDER_CONTACT_LIST_BTN_REMOVE_TITLE'),
				'TEXT' => Loc::getMessage('SENDER_CONTACT_LIST_BTN_REMOVE'),
				'ONCLICK' => "BX.Sender.ContactList.remove({$data['ID']});"
			);
		}
	}

	if ($data['HAS_STATISTICS'])
	{
		$actions[] = array('SEPARATOR' => true);
		$actions[] = [
			'TITLE' => Loc::getMessage('SENDER_CONTACT_LIST_BTN_RECIPIENT_TITLE'),
			'TEXT' => Loc::getMessage('SENDER_CONTACT_LIST_BTN_RECIPIENT'),
			'ONCLICK' => "BX.Sender.Page.open('".CUtil::JSEscape($data['URLS']['RECIPIENT'])."')",
			'DEFAULT' => true
		];
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
		"FILTER_PRESETS" => $arResult['FILTER_PRESETS'],
		"DISABLE_SEARCH" => true,
		"ENABLE_LABEL" => true,
	)
);
$filterLayout = ob_get_clean();

$APPLICATION->IncludeComponent("bitrix:sender.ui.panel.title", "", array('LIST' => array(
	array('type' => 'filter', 'content' => $filterLayout),
	$arParams['LIST_ID'] ? null : array('type' => 'buttons', 'list' => [
		$arParams['SHOW_SETS']
			?
			[
				'type' => 'button',
				'id' => 'SENDER_CONTACT_LIST_BTN_SET_LIST',
				'caption' => Loc::getMessage('SENDER_CONTACT_LIST_BTN_SET_LIST'),
				'href' => $arParams['PATH_TO_SET_LIST'],
				'sliding' => true,
			]
			:
			null,
		$arParams['CAN_EDIT']
			?
			[
				'type' => 'add',
				'id' => 'SENDER_BUTTON_ADD',
				'caption' => Loc::getMessage('SENDER_CONTACT_LIST_BTN_ADD'),
				'href' => $arParams['PATH_TO_IMPORT']
			]
			:
			null
	])),
));


$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
$controlPanel = array('GROUPS' => array(array('ITEMS' => array())));
if ($arParams['CAN_EDIT'])
{
	$button = $snippet->getRemoveButton();
	$button['TEXT'] = Loc::getMessage('SENDER_CONTACT_LIST_BTN_REMOVE_FROM_L');
	$button['TITLE'] = Loc::getMessage('SENDER_CONTACT_LIST_BTN_REMOVE_FROM_L_TITLE');
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

			BX.Sender.ContactList.init(<?=Json::encode(array(
				'actionUri' => $arResult['ACTION_URI'],
				'messages' => $arResult['MESSAGES'],
				"gridId" => $arParams['GRID_ID'],
				'mess' => array()
			))?>);
		});
	</script>
<?