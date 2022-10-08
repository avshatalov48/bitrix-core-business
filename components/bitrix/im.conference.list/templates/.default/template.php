<?
/** @var array $arParams */
/** @var array $arResult */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;
use Bitrix\UI\Toolbar\ButtonLocation;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Bitrix\UI\Buttons\Button;
use Bitrix\UI\Buttons\Color;

Extension::load(["ui.notifications", "ui.dialogs.messagebox", "im.lib.clipboard", "ui.fonts.opensans"]);

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('CONFERENCE_LIST_TITLE'));
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');

if (count($arResult['ROWS']) === 0)
{
	$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'no-paddings');
}

$addBtn = new Button(
	[
		"color" => Color::PRIMARY,
		"text" => Loc::getMessage('CONFERENCE_LIST_BUTTON_CREATE'),
		"classList" => ["im-conference-list-panel-button-create"]
	]
);
Toolbar::addButton($addBtn, ButtonLocation::AFTER_TITLE);

Toolbar::addFilter(
	[
		'GRID_ID' => $arResult['GRID_ID'],
		'FILTER_ID' => $arResult['FILTER_ID'],
		'FILTER' => $arResult['FILTERS'],
		'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
		'ENABLE_LIVE_SEARCH' => true,
		'ENABLE_LABEL' => true,
		'RESET_TO_DEFAULT_MODE' => true,
	]
);

if (count($arResult['ROWS']) > 0)
{
	$navigation = $arResult['NAV_OBJECT'];
	$APPLICATION->IncludeComponent(
		"bitrix:main.ui.grid",
		"",
		array(
			"GRID_ID" => $arResult['GRID_ID'],
			"COLUMNS" => $arResult['COLUMNS'],
			"ROWS" => $arResult['ROWS'],
			'NAV_OBJECT' => $navigation,
			'PAGE_SIZES' => $navigation->getPageSizes(),
			'DEFAULT_PAGE_SIZE' => $navigation->getPageSize(),
			'TOTAL_ROWS_COUNT' => $navigation->getRecordCount(),
			'NAV_PARAM_NAME' => $navigation->getId(),
			'CURRENT_PAGE' => $navigation->getCurrentPage(),
			'PAGE_COUNT' => $navigation->getPageCount(),
			'SHOW_PAGESIZE' => true,
			'SHOW_ROW_CHECKBOXES' => false,
			'SHOW_GRID_SETTINGS_MENU' => true,
			'SHOW_PAGINATION' => true,
			'SHOW_SELECTED_COUNTER' => false,
			'SHOW_TOTAL_COUNTER' => true,
			//		'ACTION_PANEL' => $controlPanel,
			'ALLOW_COLUMNS_SORT' => true,
			'ALLOW_COLUMNS_RESIZE' => true,
			"AJAX_MODE" => "Y",
			"AJAX_OPTION_JUMP" => "N",
			"AJAX_OPTION_STYLE" => "N",
			"AJAX_OPTION_HISTORY" => "N"
		)
	);
}
else
{
?>
	<div class="im-conference-list-empty-wrap">
		<div class="im-conference-list-empty-title-wrap">
			<div class="im-conference-list-empty-title">
				<?= Loc::getMessage(
					'CONFERENCE_LIST_EMPTY_TITLE_NEW',
					[
						'#HD#' => '<span class="im-conference-list-empty-title-hd">' . Loc::getMessage('CONFERENCE_LIST_EMPTY_TITLE_HD') . '</span>',
						'#LIMIT#' => $arResult['USER_LIMIT']
					]
				) ?>
			</div>
			<div class="im-conference-list-empty-button-wrap">
				<button class="im-conference-list-empty-button ui-btn ui-btn-md ui-btn-success"><?= Loc::getMessage('CONFERENCE_LIST_EMPTY_BUTTON_CREATE') ?></button>
			</div>
		</div>
		<div class="im-conference-list-empty-image"></div>
	</div>
<?php
}
?>
<script type="text/javascript">
	BX.message(<?=Json::encode(Loc::loadLanguageFile(__FILE__))?>);
		new BX.Messenger.PhpComponent.ConferenceList(<?=Json::encode(
			[
				'gridId' => $arResult['GRID_ID'],
				'pathToAdd' => $arParams['PATH_TO_ADD'],
				'pathToList' => $arParams['PATH_TO_LIST'],
				'pathToEdit' => $arParams['PATH_TO_EDIT'],
				'sliderWidth' => $arResult['SLIDER_WIDTH']
			]
		)?>);
</script>
