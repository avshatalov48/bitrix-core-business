<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;

/** @var CMain $APPLICATION */
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

	if ($data['NAME'])
	{
		if ($data['URLS']['EDIT'])
		{
			$data['NAME'] = '<a onclick="' . "BX.Sender.Page.open('" . CUtil::JSEscape($data['URLS']['EDIT']) . "'); return false;" . '" href="' . htmlspecialcharsbx($data['URLS']['EDIT']) . '">'
				.  htmlspecialcharsbx($data['NAME'])
				. '</a>';
		}

		$data['NAME'] .= '<br>' . Loc::getMessage('SENDER_CAMPAIGN_LIST_SITE') . ': ' . $data['SITE_ID'];
	}

	$data['LETTER'] = [];
	foreach ($data['LETTERS'] as $lIndex => $letter)
	{
		$data['LETTER'][] = htmlspecialcharsbx($letter['TITLE']);
		if ($lIndex > 0)
		{
			break;
		}
	}
	$moreLettersCount = count($data['LETTERS']) - 2;
	$moreLetters = $moreLettersCount > 0 ? Loc::getMessage('SENDER_CAMPAIGN_LIST_LETTERS_MORE', ['%count%' => $moreLettersCount]) . ', ' : '';
	$data['LETTER'][] = $moreLetters . '<a onclick="' . "BX.Sender.Page.open('" . CUtil::JSEscape($data['URLS']['CHAIN']) . "'); return false;"
		. '" href="' . htmlspecialcharsbx($data['URLS']['CHAIN']) . '">'
		. ($arParams['CAN_EDIT'] ? Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_EDIT_LETTERS') : Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_VIEW_LETTERS'))
		. '</a>';
	$data['LETTER'] = implode('<br>', $data['LETTER']);


	if ($data['ACTIVE'] === 'Y')
	{
		$data['STATE'] = '<a class="ui-btn ui-btn-xs ui-btn-light-border ui-btn-icon-stop" '
			. "onclick=\"BX.Sender.CampaignList.deactivate({$data['ID']});\">"
			. Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_STOP') . '</a>';
	}
	else
	{
		$data['STATE'] = '<a class="ui-btn ui-btn-xs ui-btn-success-light ui-btn-icon-start" '
			. "onclick=\"BX.Sender.CampaignList.activate({$data['ID']});\">"
			. Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_START') . '</a>';
	}

	$data['STAT'] = '<a class="sender-trigger-list-link-lowercase" '
		. 'onclick="' . "BX.Sender.Page.open('" . CUtil::JSEscape($data['URLS']['CHAIN']) . "'); return false;\""
		. ' href="' . htmlspecialcharsbx($data['URLS']['CHAIN']) . '">'
		.  Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_CHAIN')
		. '</a><br>';
	$data['STAT'] .= '<a class="sender-trigger-list-link-lowercase" '
		. 'onclick="' . "BX.Sender.Page.open('" . CUtil::JSEscape($data['URLS']['RECIPIENT']) . "'); return false;\""
		. ' href="' . htmlspecialcharsbx($data['URLS']['RECIPIENT']) . '">'
		.  Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_RECIPIENT')
		. '</a><br>';
	$data['STAT'] .= '<a class="sender-trigger-list-link-lowercase" '
		.' onclick="' . "BX.Sender.Page.open('" . CUtil::JSEscape($data['URLS']['STAT']) . "'); return false;\""
		. ' href="' . htmlspecialcharsbx($data['URLS']['STAT']) . '">'
		.  Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_STAT')
		. '</a>';

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

		$actions[] = array('SEPARATOR' => true);
		if ($data['ACTIVE'] === 'Y')
		{
			$actions[] = array(
				'TITLE' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_STOP'),
				'TEXT' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_STOP'),
				'ONCLICK' => "BX.Sender.CampaignList.deactivate({$data['ID']});"
			);
		}
		else
		{
			$actions[] = array(
				'TITLE' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_START'),
				'TEXT' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_START'),
				'ONCLICK' => "BX.Sender.CampaignList.activate({$data['ID']});"
			);
		}
	}

	$actions[] = array('SEPARATOR' => true);
	$actions[] = array(
		'TITLE' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_CHAIN'),
		'TEXT' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_CHAIN'),
		'ONCLICK' => "BX.Sender.Page.open('".CUtil::JSEscape($data['URLS']['CHAIN'])."')",
	);
	$actions[] = array(
		'TITLE' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_RECIPIENT'),
		'TEXT' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_RECIPIENT'),
		'ONCLICK' => "BX.Sender.Page.open('".CUtil::JSEscape($data['URLS']['RECIPIENT'])."')",
	);
	$actions[] = array(
		'TITLE' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_STAT'),
		'TEXT' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_STAT'),
		'ONCLICK' => "BX.Sender.Page.open('".CUtil::JSEscape($data['URLS']['STAT'])."')",
	);

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
		[
			'type' => 'abuses',
			'href' => $arParams['PATH_TO_ABUSES'],
		],
		$arParams['CAN_EDIT']
			?
			[
				'type' => 'list',
				'id' => 'SENDER_BUTTON_ADD',
				'caption' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_ADD'),
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
	<script>
		BX.ready(function () {
			BX.Sender.CampaignList.init(<?=Json::encode(array(
				'actionUri' => $arResult['ACTION_URI'],
				"gridId" => $arParams['GRID_ID'],
				"pathToEdit" => $arParams['PATH_TO_EDIT'],
				"pathToAdd" => $arParams['PATH_TO_ADD'],
				"presets" => $arResult['PRESETS'],
				'mess' => array(
					'manually' => Loc::getMessage('SENDER_CAMPAIGN_LIST_BTN_ADD_MANUAL')
				)
			))?>);
		});
	</script>
<?