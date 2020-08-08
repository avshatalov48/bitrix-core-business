<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/**
 * @var array $arParams
 * @var array $arResult
 * @global \CMain $APPLICATION
 * @var \CBitrixComponentTemplate $this
 * @var \TranslateListComponent $component
 */

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Translate;

//$isBitrix24Template = SITE_TEMPLATE_ID === "bitrix24";
$isAjax = $arResult['IS_AJAX_REQUEST'];



if (!$isAjax)
{
	?>
	<div class="adm-toolbar-panel-container">
		<?
		if (count($arResult['INIT_FOLDERS']) > 1)
		{
			?>
			<div class="adm-toolbar-panel-align-left" title="<?= Loc::getMessage('TR_STARTING_PATH') ?>">
				<button id="bx-translate-init-folder" class="ui-btn ui-btn-default ui-btn-dropdown ui-btn-split ui-btn-icon-lock">
					<?//= htmlspecialcharsbx($arResult['STARTING_PATH']) ?>
				</button>
			</div>
			<?
		}
		?>
		<div class="adm-toolbar-panel-flexible-space">
		<?

	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.filter',
		'',
		array(
			'COMPACT_STATE' => true,
			'DISABLE_SEARCH' => false,
			'GRID_ID' => $arParams['GRID_ID'],
			'FILTER_ID' => $arParams['FILTER_ID'],
			'FILTER' => $arResult['FILTER_DEFINITION'],
			'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
			'ENABLE_LIVE_SEARCH' => false,
			'ENABLE_LABEL' => true,
			'RESET_TO_DEFAULT_MODE' => false,
			'MESSAGES' => array(
				'MAIN_UI_FILTER__PLACEHOLDER' => Loc::getMessage('TRANS_PATH'),
				'MAIN_UI_FILTER__PLACEHOLDER_DEFAULT' => Loc::getMessage('TRANS_PATH_SEARCH'),
			),
		),
		$component,
		array('HIDE_ICONS' => 'Y')
	);

	// view mode
	$showCountPhrases = $arParams['SHOW_COUNT_PHRASES'];
	$showCountFiles = $arParams['SHOW_COUNT_FILES'];
	$showUntranslatedPhrases = $arParams['SHOW_UNTRANSLATED_PHRASES'];
	$showUntranslatedFiles = $arParams['SHOW_UNTRANSLATED_FILES'];

	if ($showCountFiles)
	{
		$dataTitle = Loc::getMessage('TR_INDEX_VIEW_MODE_TITLE_COUNT_FILES');
	}
	elseif ($showUntranslatedPhrases)
	{
		$dataTitle = Loc::getMessage('TR_INDEX_VIEW_MODE_TITLE_UNTRANSLATED');
	}
	elseif ($showUntranslatedFiles)
	{
		$dataTitle = Loc::getMessage('TR_INDEX_VIEW_MODE_TITLE_UNTRANSLATED_FILES');
	}
	else // $showCountPhrases
	{
		$dataTitle = Loc::getMessage('TR_INDEX_VIEW_MODE_TITLE_COUNT_PHRASES');
	}

	?>
		</div>
		<div class="adm-toolbar-panel-align-right">
			<button id="bx-translate-mode-menu-view-anchor" class="ui-btn ui-btn-dropdown ui-btn-default">
				<?= $dataTitle ?>
			</button>
			<button onclick="BX.Translate.ProcessManager.getInstance('index').showDialog()" class="ui-btn ui-btn-primary ui-btn-icon-task">
				<?= Loc::getMessage('TR_LIST_REFRESH_INDEX') ?>
			</button>
			<button id="bx-translate-extra-menu-anchor" class="ui-btn ui-btn-default ui-btn-icon-download"></button>
		</div>
	</div>
	<?

	foreach ($arResult['FILTER_DEFINITION'] as $fieldName => $fieldDef)
	{
		if ($fieldDef['type'] === 'list' && isset($fieldDef['group_values']))
		{
			?>
			<script type="text/javascript">
				BX.ready(function () {

					BX.addCustomEvent(window, 'UI::Select::change', BX.delegate(function (select, data) {

						this.radioOnMultiple(
							select,
							data,
							'<?= $fieldName ?>',
							<?= Json::encode($fieldDef['group_values']) ?>
						);

					}, BX.Translate.PathList));

				});
			</script>
			<?
		}
	}
}

if (!empty($arResult['ERROR_MESSAGE']))
{
	?>
	<div class="ui-alert ui-alert-danger ui-alert-icon-danger">
		<span class="ui-alert-message"><?= $arResult['ERROR_MESSAGE'] ?></span>
	</div>
	<?
}

$pageNav = array(
	'SHOW_MORE_BUTTON' => true,
	'NAV_OBJECT' => $arResult['NAV_OBJECT'],
	'TOTAL_ROWS_COUNT' => $arResult['TOTAL_ROWS_COUNT'],
	'CURRENT_PAGE' => $arResult['CURRENT_PAGE'],
	'DEFAULT_PAGE_SIZE' => 20,
	'PAGE_SIZES' => array(
		array('NAME' => '20', 'VALUE' => '20'),
		array('NAME' => '50', 'VALUE' => '50'),
		array('NAME' => '100', 'VALUE' => '100'),
		array('NAME' => '200', 'VALUE' => '200'),
		array('NAME' => '300', 'VALUE' => '300'),
		array('NAME' => '400', 'VALUE' => '400'),
		array('NAME' => '500', 'VALUE' => '500'),
		array('NAME' => '600', 'VALUE' => '600'),
		array('NAME' => '700', 'VALUE' => '700'),
		array('NAME' => '800', 'VALUE' => '800'),
		array('NAME' => '900', 'VALUE' => '900'),
	),
	'SHOW_PAGINATION' => true,
	'SHOW_PAGESIZE' => true,
);


$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	array_merge(array(
		'GRID_ID' => $arParams['GRID_ID'],
		'HEADERS' => $arResult['HEADERS'],
		'SORT' => isset($arResult['SORT']) ? $arResult['SORT'] : array('TITLE' => 'asc'),
		'ROWS' => isset($arResult['GRID_DATA']) ? $arResult['GRID_DATA'] : array(),

		'AJAX_MODE' => 'Y',
		'AJAX_OPTION_JUMP' => 'N',
		'AJAX_OPTION_STYLE' => 'N',
		'AJAX_OPTION_HISTORY' => 'N',

		'DISABLE_HEADERS_TRANSFORM' => false,
		'ALLOW_STICKED_COLUMNS' => false,
		'ALLOW_COLUMNS_SORT' => true,
		'ALLOW_ROWS_SORT' => false,
		'ALLOW_COLUMNS_RESIZE' => true,
		'ALLOW_HORIZONTAL_SCROLL' => true,
		'ALLOW_SORT' => $arResult['ALLOW_SORT'],
		'ALLOW_PIN_HEADER' => true,

		'SHOW_ACTION_PANEL' => true,
		'ACTION_PANEL' => $arResult['GROUP_ACTIONS'],

		'SHOW_CHECK_ALL_CHECKBOXES' => true,
		'SHOW_ROW_CHECKBOXES' => true,
		'SHOW_ROW_ACTIONS_MENU' => true,
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_NAVIGATION_PANEL' => true,
		'SHOW_PAGINATION' => true,
		'SHOW_SELECTED_COUNTER' => true,
		'SHOW_TOTAL_COUNTER' => true,
		'SHOW_PAGESIZE' => false,

		'ENABLE_COLLAPSIBLE_ROWS' => false,

	), $pageNav),
	$component,
	array('HIDE_ICONS' => 'Y')
);

if (!$isAjax)
{
	?>
	<script type="text/javascript">
		BX.ready(function () {

			BX.Translate.PathList.init(<?=Json::encode(array(
				"tabId" => (string)$arParams['TAB_ID'],
				"gridId" => $arParams['GRID_ID'],
				"filterId" => $arParams['FILTER_ID'],
				'mode' => ((defined('ADMIN_SECTION') && ADMIN_SECTION === true) ? 'admin' : 'public'),
				"actionMode" => $arResult['ACTION'],
				'relUrl' => $arParams['LIST_PATH'],
				'defaults' => [
					'initFolders' => $arResult['INIT_FOLDERS'],
					'startingPath' => $arResult['STARTING_PATH'],
				],
				'messages' => [
					'AuthError' => Loc::getMessage('main_include_decode_pass_sess'),
					'ViewModeMenuCountPhrases' => Loc::getMessage("TR_INDEX_VIEW_MODE_MENU_COUNT_PHRASES"),
					'ViewModeTitleCountPhrases' => Loc::getMessage('TR_INDEX_VIEW_MODE_TITLE_COUNT_PHRASES'),
					'ViewModeMenuCountFiles' => Loc::getMessage("TR_INDEX_VIEW_MODE_MENU_COUNT_FILES"),
					'ViewModeTitleCountFiles' => Loc::getMessage('TR_INDEX_VIEW_MODE_TITLE_COUNT_FILES'),
					'ViewModeMenuUntranslatedPhrases' => Loc::getMessage("TR_INDEX_VIEW_MODE_MENU_UNTRANSLATED"),
					'ViewModeTitleUntranslatedPhrases' => Loc::getMessage('TR_INDEX_VIEW_MODE_TITLE_UNTRANSLATED'),
					'ViewModeMenuUntranslatedFiles' => Loc::getMessage("TR_INDEX_VIEW_MODE_MENU_UNTRANSLATED_FILES"),
					'ViewModeTitleUntranslatedFiles' => Loc::getMessage('TR_INDEX_VIEW_MODE_TITLE_UNTRANSLATED_FILES'),
					'ViewModeMenuHideEmptyFolders' => Loc::getMessage("TR_INDEX_VIEW_MODE_MENU_EMPTY_FOLDERS"),
					'ViewModeMenuShowDiffLinks' => Loc::getMessage("TR_INDEX_VIEW_MODE_MENU_DIFF_LINKS"),
				],
				'viewMode' => $arParams['VIEW_MODE'],
				'extraMenuItems' => [
					[
						'id' => 'translate-export-csv',
						'text' => Loc::getMessage("TR_LIST_EXPORT_CSV"),
						'onclick' => "BX.Translate.PathList.ExportMenuSelector();",
					],
					[
						'id' => 'translate-import-csv',
						'text' => Loc::getMessage("TR_LIST_IMPORT_CSV"),
						'onclick' => "BX.Translate.ProcessManager.getInstance('import').showDialog();",
					],
				],
			))?>);

			BX.Translate.PathList.ExportMenuSelector = function()
			{
				switch (BX.Translate.PathList.getActionMode())
				{
					case '<?= \TranslateListComponent::ACTION_SEARCH_FILE ?>':
						BX.Translate.ProcessManager.getInstance('exportSearch').showDialog();
						break;

					case '<?= \TranslateListComponent::ACTION_SEARCH_PHRASE ?>':
						BX.Translate.ProcessManager.getInstance('exportSearch').showDialog();
						break;

					default:
						BX.Translate.ProcessManager.getInstance('export').showDialog();
				}
			};

			if (BX.Translate.PathList.getFilter())
			{
				BX.Translate.PathList.getFilter().getSearch().setInputPlaceholder("<?= Loc::getMessage('TRANS_PATH_SEARCH') ?>");
				BX.Translate.PathList.getFilter().getSearch().input.value = "<?= \CUtil::JSEscape($arResult['PATH']) ?>";
			}
			if (BX.Translate.PathList.getGrid())
			{
				BX.Translate.PathList.getGrid().baseUrl = "<?= \CUtil::JSEscape($arParams['LIST_PATH']) ?>";
			}

			// clearEthalon
			BX.Translate.ProcessManager.create(<?=Json::encode([
				'id' => 'clearEthalon',
				'controller' => 'bitrix:translate.controller.editor.file',
				'messages' => [
					'DialogTitle' => Loc::getMessage("TR_CLEAR_DLG_TITLE"),
					'DialogSummary' => Loc::getMessage("TR_CLEAR_DLG_SUMMARY"),
					'DialogStartButton' => Loc::getMessage('TR_DLG_BTN_START'),
					'DialogStopButton' => Loc::getMessage('TR_DLG_BTN_STOP'),
					'DialogCloseButton' => Loc::getMessage('TR_DLG_BTN_CLOSE'),
					'AuthError' => Loc::getMessage('main_include_decode_pass_sess'),
					'RequestError' => Loc::getMessage('TR_DLG_REQUEST_ERR'),
					'RequestCanceling' => Loc::getMessage('TR_DLG_REQUEST_CANCEL'),
					'RequestCanceled' => Loc::getMessage('TR_CLEAR_DLG_CANCELED'),
					'RequestCompleted' => Loc::getMessage('TR_CLEAR_DLG_COMPLETED'),
				],
				'queue' => [
					[
						'controller' => 'bitrix:translate.controller.index.collector',
						'action' => Translate\Controller\Index\Collector::ACTION_COLLECT_LANG_PATH,
						'title' => Loc::getMessage('TR_INDEX_ACTION_COLLECT_LANG_PATH', ['#NUM#' => 1, '#LEN#' => 3]),
						'params' => ['checkIndexExists' => 'Y'],
						'progressBarTitle' => Loc::getMessage('TR_INDEX_ACTION_COLLECT_LANG_PATH_PROGRESS'),
					],
					[
						'controller' => 'bitrix:translate.controller.editor.file',
						'action' => Translate\Controller\Editor\File::ACTION_CLEAN_ETHALON,
						'title' => Loc::getMessage('TR_CLEAR_ACTION_CLEARING', ['#NUM#' => 2, '#LEN#' => 3]),
						'progressBarTitle' => Loc::getMessage('TR_CLEAR_PROGRESS'),
					],
					[
						'controller' => 'bitrix:translate.controller.editor.file',
						'action' => Translate\Controller\Editor\File::ACTION_WIPE_EMPTY,
						'title' => Loc::getMessage('TR_CLEAR_ACTION_WIPE_EMPTY', ['#NUM#' => 3, '#LEN#' => 3]),
						'progressBarTitle' => Loc::getMessage('TR_CLEAR_PROGRESS'),
					],
				],
				'params' => [
					'path' => $arResult['PATH'],
				],
				'sToken' => 's'. time(),
			])?>)
				.setHandler(
					'StateChanged',
					function (state, result)
					{
						/** @type {BX.Translate.Process} this */
						if (state === this.STATUSES.completed)
						{
							BX.Translate.PathList.reloadGrid();
							this.closeDialog();
						}
					}
				);

			BX.Translate.PathList.addGroupAction(
				'<?= Translate\Controller\Editor\File::ACTION_CLEAN_ETHALON ?>',
				function(pathList)
				{
					pathList = pathList.filter(function (p){
						return p !== null && p !== "";
					});
					var process = BX.Translate.ProcessManager.getInstance('clearEthalon');
					process
						.setParam('pathList', pathList.join("\r\n"))
						.showDialog();
				}
			);

			// index
			BX.Translate.ProcessManager.create(<?=Json::encode([
				'id' => 'index',
				'controller' => 'bitrix:translate.controller.index.collector',
				'messages' => [
					'DialogTitle' => Loc::getMessage("TR_INDEX_DLG_TITLE"),
					'DialogSummary' => Loc::getMessage("TR_INDEX_DLG_SUMMARY"),
					'DialogStartButton' => Loc::getMessage('TR_DLG_BTN_START'),
					'DialogStopButton' => Loc::getMessage('TR_DLG_BTN_STOP'),
					'DialogCloseButton' => Loc::getMessage('TR_DLG_BTN_CLOSE'),
					'AuthError' => Loc::getMessage('main_include_decode_pass_sess'),
					'RequestError' => Loc::getMessage('TR_DLG_REQUEST_ERR'),
					'RequestCanceling' => Loc::getMessage('TR_DLG_REQUEST_CANCEL'),
					'RequestCanceled' => Loc::getMessage('TR_INDEX_DLG_CANCELED'),
					'RequestCompleted' => Loc::getMessage('TR_INDEX_DLG_COMPLETED'),
				],
				'queue' => [
					[
						'action' => Translate\Controller\Index\Collector::ACTION_COLLECT_LANG_PATH,
						'title' => Loc::getMessage('TR_INDEX_ACTION_COLLECT_LANG_PATH', ['#NUM#' => 1, '#LEN#' => 4]),
						'progressBarTitle' => Loc::getMessage('TR_INDEX_ACTION_COLLECT_LANG_PATH_PROGRESS'),
					],
					[
						'action' => Translate\Controller\Index\Collector::ACTION_COLLECT_PATH,
						'title' => Loc::getMessage('TR_INDEX_ACTION_COLLECT_PATH', ['#NUM#' => 2, '#LEN#' => 4]),
						'progressBarTitle' => Loc::getMessage('TR_INDEX_ACTION_COLLECT_PATH_PROGRESS'),
					],
					[
						'action' => Translate\Controller\Index\Collector::ACTION_COLLECT_FILE,
						'title' => Loc::getMessage('TR_INDEX_ACTION_COLLECT_FILE', ['#NUM#' => 3, '#LEN#' => 4]),
						'progressBarTitle' => Loc::getMessage('TR_INDEX_ACTION_COLLECT_FILE_PROGRESS'),
					],
					[
						'action' => Translate\Controller\Index\Collector::ACTION_COLLECT_PHRASE,
						'title' => Loc::getMessage('TR_INDEX_ACTION_COLLECT_PHRASE', ['#NUM#' => 4, '#LEN#' => 4]),
						'progressBarTitle' => Loc::getMessage('TR_INDEX_ACTION_COLLECT_PHRASE_PROGRESS'),
					]
				],
				'params' => [
					'path' => $arResult['PATH'],
				],
				'sToken' => 's'. time(),
				'optionsFields' => [
					'languages' => [
						'name' => 'languages',
						'type' => 'select',
						'multiple' => 'Y',
						'size' => (count($arResult['LANGUAGES_TITLE']) >= 10 ? '10' : count($arResult['LANGUAGES_TITLE']) + 1),
						'title' => Loc::getMessage('TR_INDEX_DLG_PARAM_LANGUAGES'),
						'list' => array_merge(['all' => Loc::getMessage('TR_EXPORT_CSV_PARAM_LANGUAGES_ALL')], $arResult['LANGUAGES_TITLE']),
						'value' => 'all',
					],
				]
			])?>)
				.setHandler(
					'StateChanged',
					function (state, result)
					{
						/** @type {BX.Translate.Process} this */
						if (state === this.STATUSES.completed)
						{
							BX.Translate.PathList.reloadGrid();
							this.closeDialog();
						}
					}
				);

			//export
			BX.Translate.ProcessManager.create(<?=Json::encode([
				'id' => 'export',
				'controller' => 'bitrix:translate.controller.export.csv',
				'messages' => [
					'DialogTitle' => Loc::getMessage("TR_EXPORT_CSV_DLG_TITLE"),
					'DialogSummary' => Loc::getMessage("TR_EXPORT_CSV_DLG_SUMMARY"),
					'DialogStartButton' => Loc::getMessage('TR_EXPORT_CSV_DLG_BTN_START'),
					'DialogStopButton' => Loc::getMessage('TR_DLG_BTN_STOP'),
					'DialogCloseButton' => Loc::getMessage('TR_DLG_BTN_CLOSE'),
					'AuthError' => Loc::getMessage('main_include_decode_pass_sess'),
					'RequestError' => Loc::getMessage('TR_DLG_REQUEST_ERR'),
					'RequestCanceling' => Loc::getMessage('TR_DLG_REQUEST_CANCEL'),
					'RequestCanceled' => Loc::getMessage('TR_EXPORT_CSV_DLG_CANCELED'),
					'RequestCompleted' => Loc::getMessage('TR_EXPORT_CSV_DLG_COMPLETED'),
					'DialogExportDownloadButton' => Loc::getMessage('TR_EXPORT_DLG_DOWNLOAD'),
					'DialogExportClearButton' => Loc::getMessage('TR_EXPORT_DLG_CLEAR'),
				],
				'queue' => [
					[
						'controller' => 'bitrix:translate.controller.index.collector',
						'action' => Translate\Controller\Index\Collector::ACTION_COLLECT_LANG_PATH,
						'title' => Loc::getMessage('TR_INDEX_ACTION_COLLECT_LANG_PATH', ['#NUM#' => 1, '#LEN#' => 2]),
						'params' => ['checkIndexExists' => 'Y'],
						'progressBarTitle' => Loc::getMessage('TR_INDEX_ACTION_COLLECT_LANG_PATH_PROGRESS'),
					],
					[
						'controller' => 'bitrix:translate.controller.export.csv',
						'action' => Translate\Controller\Export\Csv::ACTION_EXPORT,
						'title' => Loc::getMessage('TR_EXPORT_CSV_DLG_TITLE', ['#NUM#' => 2, '#LEN#' => 2]),
						'progressBarTitle' => Loc::getMessage('TR_EXPORT_CSV_PROGRESS'),
					],
				],
				'params' => [
					'path' => $arResult['PATH'],
				],
				'sToken' => 's'. time(),
				'optionsFields' => [
					'collectUntranslated' => [
						'name' => 'collectUntranslated',
						'type' => 'checkbox',
						'title' => Loc::getMessage('TR_EXPORT_CSV_PARAM_UNTRANSLATED'),
						'value' => 'N'
					],
					'convertEncoding' => [
						'name' => 'convertEncoding',
						'type' => 'checkbox',
						'title' => Loc::getMessage('TR_EXPORT_CSV_PARAM_CONVERT_UTF8'),
						'value' => ((Main\Localization\Translation::useTranslationRepository() || Translate\Config::isUtfMode()) ? 'Y' : 'N'),
					],
					'languages' => [
						'name' => 'languages',
						'type' => 'select',
						'multiple' => 'Y',
						'size' => (count($arResult['LANGUAGES_TITLE']) >= 10 ? '10' : count($arResult['LANGUAGES_TITLE']) + 1),
						'title' => Loc::getMessage('TR_EXPORT_CSV_PARAM_LANGUAGES'),
						'list' => array_merge(['all' => Loc::getMessage('TR_EXPORT_CSV_PARAM_LANGUAGES_ALL')], $arResult['LANGUAGES_TITLE']),
						'value' => 'all',
					],
					'pathList' => [
						'name' => 'pathList',
						'type' => 'text',
						'size' => 10,
						'title' => Loc::getMessage('TR_EXPORT_CSV_PARAM_FILE_LIST'),
					],
				]
			])?>);

			BX.Translate.PathList.addGroupAction(
				'<?= Translate\Controller\Export\Csv::ACTION_EXPORT_PATH ?>',
				function(pathList, codeList)
				{
					pathList = pathList.filter(function (p){
						return p !== null && p !== "";
					});
					codeList = codeList.filter(function (c){
						return c !== null && c !== "";
					});
					var process = BX.Translate.ProcessManager.getInstance('export');
					process
						.setParam('codeList', codeList.join("\r\n"))
						.setOptionFieldValue('pathList', pathList.join("\r\n"))
						.showDialog()
						.setOptionFieldValue('pathList', null);
				}
			);

			//export search
			BX.Translate.ProcessManager.create(<?=Json::encode([
				'id' => 'exportSearch',
				'controller' => 'bitrix:translate.controller.export.csv',
				'messages' => [
					'DialogTitle' => Loc::getMessage("TR_EXPORT_CSV_DLG_TITLE"),
					'DialogSummary' => Loc::getMessage("TR_EXPORT_CSV_DLG_SUMMARY"),
					'DialogStartButton' => Loc::getMessage('TR_EXPORT_CSV_DLG_BTN_START'),
					'DialogStopButton' => Loc::getMessage('TR_DLG_BTN_STOP'),
					'DialogCloseButton' => Loc::getMessage('TR_DLG_BTN_CLOSE'),
					'AuthError' => Loc::getMessage('main_include_decode_pass_sess'),
					'RequestError' => Loc::getMessage('TR_DLG_REQUEST_ERR'),
					'RequestCanceling' => Loc::getMessage('TR_DLG_REQUEST_CANCEL'),
					'RequestCanceled' => Loc::getMessage('TR_EXPORT_CSV_DLG_CANCELED'),
					'RequestCompleted' => Loc::getMessage('TR_EXPORT_CSV_DLG_COMPLETED'),
					'DialogExportDownloadButton' => Loc::getMessage('TR_EXPORT_DLG_DOWNLOAD'),
					'DialogExportClearButton' => Loc::getMessage('TR_EXPORT_DLG_CLEAR'),
				],
				'queue' => [
					[
						'controller' => 'bitrix:translate.controller.export.csv',
						'action' => Translate\Controller\Export\Csv::ACTION_EXPORT,
						'title' => Loc::getMessage('TR_EXPORT_CSV_DLG_TITLE', ['#NUM#' => 2, '#LEN#' => 2]),
						'progressBarTitle' => Loc::getMessage('TR_EXPORT_CSV_PROGRESS'),
					],
				],
				'params' => [
					'path' => $arResult['PATH'],
				],
				'sToken' => 's'. time(),
				'optionsFields' => [
					'collectUntranslated' => [
						'name' => 'collectUntranslated',
						'type' => 'checkbox',
						'title' => Loc::getMessage('TR_EXPORT_CSV_PARAM_UNTRANSLATED'),
						'value' => 'N'
					],
					'convertEncoding' => [
						'name' => 'convertEncoding',
						'type' => 'checkbox',
						'title' => Loc::getMessage('TR_EXPORT_CSV_PARAM_CONVERT_UTF8'),
						'value' => ((Main\Localization\Translation::useTranslationRepository() || Translate\Config::isUtfMode()) ? 'Y' : 'N'),
					],
					'languages' => [
						'name' => 'languages',
						'type' => 'select',
						'multiple' => 'Y',
						'size' => (count($arResult['LANGUAGES_TITLE']) >= 10 ? '10' : count($arResult['LANGUAGES_TITLE']) + 1),
						'title' => Loc::getMessage('TR_EXPORT_CSV_PARAM_LANGUAGES'),
						'list' => array_merge(['all' => Loc::getMessage('TR_EXPORT_CSV_PARAM_LANGUAGES_ALL')], $arResult['LANGUAGES_TITLE']),
						'value' => 'all',
					],
				]
			])?>);


			//import
			<?
			$isUtfMode = Translate\Config::isUtfMode();
			$useTranslationRepository = Main\Localization\Translation::useTranslationRepository();

			$encodings = array();
			$allowedEncodings = Translate\Config::getAllowedEncodings();
			foreach ($allowedEncodings as $enc)
			{
				$encodings[$enc] = Translate\Config::getEncodingName($enc);
			}
			?>
			BX.Translate.ProcessManager.create(<?=Json::encode([
				'id' => 'import',
				'controller' => 'bitrix:translate.controller.import.csv',
				'messages' => [
					'DialogTitle' => Loc::getMessage("TR_IMPORT_DLG_TITLE"),
					'DialogSummary' => Loc::getMessage("TR_IMPORT_CSV_DLG_SUMMARY"),
					'DialogStartButton' => Loc::getMessage('TR_IMPORT_CSV_DLG_BTN_START'),
					'DialogStopButton' => Loc::getMessage('TR_DLG_BTN_STOP'),
					'DialogCloseButton' => Loc::getMessage('TR_DLG_BTN_CLOSE'),
					'AuthError' => Loc::getMessage('main_include_decode_pass_sess'),
					'RequestError' => Loc::getMessage('TR_DLG_REQUEST_ERR'),
					'RequestCanceling' => Loc::getMessage('TR_DLG_REQUEST_CANCEL'),
					'RequestCanceled' => Loc::getMessage('TR_IMPORT_CSV_DLG_CANCELED'),
					'RequestCompleted' => Loc::getMessage('TR_IMPORT_CSV_DLG_COMPLETED'),
				],
				'queue' => [
					[
						'action' => Translate\Controller\Import\Csv::ACTION_UPLOAD,
						'title' => Loc::getMessage('TR_IMPORT_ACTION_UPLOAD', ['#NUM#' => 1, '#LEN#' => 3]),
						'progressBarTitle' => Loc::getMessage('TR_IMPORT_ACTION_UPLOAD_PROGRESS'),
					],
					[
						'action' => Translate\Controller\Import\Csv::ACTION_IMPORT,
						'title' => Loc::getMessage('TR_IMPORT_CSV_DLG_TITLE', ['#NUM#' => 2, '#LEN#' => 3]),
						'progressBarTitle' => Loc::getMessage('TR_IMPORT_CSV_PROGRESS'),
					],
					[
						'action' => Translate\Controller\Import\Csv::ACTION_INDEX,
						'title' => Loc::getMessage('TR_INDEX_CSV_DLG_TITLE', ['#NUM#' => 3, '#LEN#' => 3]),
						'progressBarTitle' => Loc::getMessage('TR_INDEX_CSV_PROGRESS'),
					],
					[
						'action' => Translate\Controller\Import\Csv::ACTION_FINALIZE,
						'finalize' => true,
					],
				],
				'params' => [
					'path' => $arResult['PATH'],
				],
				'sToken' => 's'. time(),
				'optionsFields' => [
					'csvFile' => [
						'name' => 'csvFile',
						'type' => 'file',
						'title' => Loc::getMessage('TR_UPLOAD_CSV_FILE'),
						'obligatory' => true,
						'emptyMessage' => Loc::getMessage('TR_UPLOAD_CSV_FILE_EMPTY_ERROR'),
					],

					'encodingIn' => (!$isUtfMode && !$useTranslationRepository ?
						[
							'name' => 'encodingIn',
							'type' => 'checkbox',
							'title' => Loc::getMessage('TR_CONVERT_FROM_UTF8'),
							'value' => 'utf-8'
						]
						:
						[
							'name' => 'encodingIn',
							'type' => 'select',
							'title' => Loc::getMessage('TR_CONVERT_ENCODING'),
							'list' => $encodings,
							'value' => Main\Localization\Translation::getCurrentEncoding(),
						]
					),

					'updateMethod' => [
						'name' => 'updateMethod',
						'type' => 'radio',
						'title' => Loc::getMessage('TR_IMPORT_UPDATE_METHOD'),
						'list' => [
							Translate\Controller\Import\Csv::METHOD_ADD_ONLY => Loc::getMessage('TR_NO_REWRITE_LANG_FILES'),
							Translate\Controller\Import\Csv::METHOD_UPDATE_ONLY => Loc::getMessage('TR_UPDATE_LANG_FILES'),
							Translate\Controller\Import\Csv::METHOD_ADD_UPDATE => Loc::getMessage('TR_ADD_UPDATE_LANG_FILES'),
						],
						'value' => Translate\Controller\Import\Csv::METHOD_ADD_ONLY,
					],

					'reindex' => [
						'name' => 'reindex',
						'type' => 'checkbox',
						'title' => Loc::getMessage('TR_REINDEX'),
						'value' => 'Y',
					],
				]
			])?>)
				.setHandler(
					'StateChanged',
					function (state, result)
					{
						/** @type {BX.Translate.Process} this */
						if (state === this.STATUSES.completed)
						{
							var dialog = this.getDialog(), buttonsContainer = dialog.popupWindow.buttonsContainer;

							BX.clean(buttonsContainer);

							buttonsContainer.appendChild(BX.create(
								"span",
								{
									text: "<?= Loc::getMessage('TR_IMPORT_CSV_DLG_BTN_MORE') ?>",
									attrs: {className: "popup-window-button popup-window-button-accept"},
									events: {
										click: BX.proxy(function(){
											this.closeDialog();
											this.showDialog();
										}, this)
									}
								}
							));

							buttonsContainer.appendChild(BX.create(
								"span",
								{
									text: "<?= Loc::getMessage('TR_DLG_BTN_CLOSE') ?>",
									attrs: {className: "popup-window-button popup-window-button-link popup-window-button-link-cancel"},
									events: {
										click: BX.proxy(function(){
											this.closeDialog();
											BX.Translate.PathList.reloadGrid();
										}, this)
									}
								}
							));
						}
					}
				);


			// capture file drop event
			var dropContainer = BX("adm-workarea");
			if (dropContainer)
			{
				dropContainer.ondragover = dropContainer.ondragenter = function (evt) {
					evt.preventDefault();
				};
				dropContainer.ondrop = function (evt) {
					try
					{
						var process = BX.Translate.ProcessManager.getInstance('import');
						process.showDialog();

						var fileInput = process.dialog.getOption('csvFile');
						if (fileInput)
						{
							fileInput.files = evt.dataTransfer.files;
						}

						evt.preventDefault();
					} catch (ex)
					{
					}
				};
			}

		});
	</script>
	<?
}

