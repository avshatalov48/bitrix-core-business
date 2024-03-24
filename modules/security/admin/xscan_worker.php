<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm('');
}

IncludeModuleLangFile(__FILE__);

if (function_exists('mb_internal_encoding'))
{
	mb_internal_encoding('ISO-8859-1');
}

$strError = '';
$file = '';

$APPLICATION->SetTitle(GetMessage("BITRIX_XSCAN_SEARCH"));

if (!isset($_REQUEST['ajax']) && !isset($_REQUEST['grid_action']))
{
	require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/prolog_admin_after.php");
}

CModule::IncludeModule('security');

$filter = \Bitrix\Security\Controller\Xscan::getFilter();

if (isset($_POST['download-files']))
{
	if (!check_bitrix_sessid())
	{
		$strError = CBitrixXscan::ShowMsg(GetMessage("BITRIX_XSCAN_SESSIA_USTARELA_OBN"), 'red');
		echo $strError;
		die();
	}

	$filter['TYPE'] = 'file';
	$all = isset($_POST['all']) && $_POST['all'] === 'true';

	if (!$all)
	{
		$filesId = Bitrix\Main\Web\Json::decode($_POST['download-files']);
		$filter = ['@ID' => $filesId];
	}

	$list = \Bitrix\Security\XScanResultTable::getList([
		'select' => [
			'SRC',
		],
		'filter' => $filter,
	])->fetchAll();

	$files = array_column($list, 'SRC');

	foreach ($files as $i => $file)
	{
		if (!file_exists($file) && file_exists($new_f = preg_replace('#\.php[578]?$#i', '.ph_', $file)))
		{
			$files[$i] = $new_f;
		}
	}

	$tempDir = CTempFile::GetDirectoryName(1);
	CheckDirPath($tempDir);
	$tempFile = $tempDir . Bitrix\Main\Security\Random::getString(32);

	$zip = CBXArchive::GetArchive($tempFile, 'ZIP');
	$zip->Pack($files);

	$tempFile = \CFile::MakeFileArray($tempFile);

	\CFile::ViewByUser($tempFile, [
		"force_download" => true,
		"attachment_name" => 'xscan_results.zip',
	]);

	\Bitrix\Main\Application::getInstance()->end();
}

$grid_options = new Bitrix\Main\Grid\Options('report_list');
$nav_params = $grid_options->GetNavParams();

$nav = new \Bitrix\Main\UI\PageNavigation("report_list");
$nav->allowAllRecords(false)
	->setPageSize($nav_params['nPageSize'])
;

$session = \Bitrix\Main\Application::getInstance()->getSession();

if (isset($_GET['clear_nav']) && $_GET['clear_nav'] == 'Y')
{
	$nav->setCurrentPage(1);
}
elseif (isset($_GET['grid_action']) && $_GET['grid_action'] === 'more' && $_GET['grid_id'] === $grid_options->getId())
{
	$nav->setCurrentPage($_GET['report_list']);
}
elseif (isset($_GET['grid_action']) && $_GET['grid_action'] === 'pagination')
{
	$nav->initFromUri();
}
elseif ($session->has('xscan_page'))
{
	$nav->setCurrentPage($session['xscan_page']);
}

$session['xscan_page'] = $nav->getCurrentPage();

\Bitrix\Main\UI\Extension::load(["ui.layout-form", "ui.buttons", "ui.dialogs.messagebox", "ui.progressbar", "ui.alerts", "sidepanel"]);

$scaner = new CBitrixXscan();
$start_path = isset($_REQUEST['start_path']) ? $_REQUEST['start_path'] : $_SERVER['DOCUMENT_ROOT'];
$start_path = rtrim($start_path, '/');

?>

	<script>
		function callback(result)
		{
			if (BX.SidePanel.Instance.isOpen()) {
				BX.SidePanel.Instance.postMessage(window, 'xscan-grid', {'result': result});
				BX.SidePanel.Instance.close();
			} else if (BX('alert_msg')) {
				BX('alert_msg').innerHTML = result;
				GridRenew();
			} else {
				window.close();
			}
		}

		<?php
		if (isset($_GET['pro']) && $_GET['pro'] !== 'off')
		{
			echo "localStorage.setItem('xscan_pro', true);";
		}
		elseif (isset($_GET['pro']) && $_GET['pro'] == 'off')
		{
			echo "localStorage.removeItem('xscan_pro');";
		}
		?>

		var pro = localStorage.getItem('xscan_pro');

		function xscan_prison(file)
		{
			if (pro) {
				BX.ajax.runAction('security.xscan.prison', { data: {file: file}}).then(function (response) {callback(response.data)});
			} else {
				BX.UI.Dialogs.MessageBox.confirm('<?= GetMessage("BITRIX_XSCAN_WARN") ?>', () => {
					BX.ajax.runAction('security.xscan.prison', { data: {file: file}}).then(function (response) {callback(response.data)});
					return true;
				});
			}
		}

		function xscan_hide(file)
		{
			if (pro) {
				BX.ajax.runAction('security.xscan.hide', {data: {file: file}}).then(function (response) {callback(response.data)});
			} else {
				BX.UI.Dialogs.MessageBox.confirm('<?= GetMessage("BITRIX_XSCAN_HIDE") ?>', () => {
					BX.ajax.runAction('security.xscan.hide', {data: {file: file}}).then(function (response) {callback(response.data)});
					return true;
				});
			}
		}

		function xscan_release(file)
		{
			if (pro) {
				BX.ajax.runAction('security.xscan.release', { data: {file: file}}).then(function (response) {callback(response.data)});
			} else {
				BX.UI.Dialogs.MessageBox.confirm('<?= GetMessage("BITRIX_XSCAN_WARN_RELEASE") ?>', () => {
					BX.ajax.runAction('security.xscan.release', { data: {file: file}}).then(function (response) {callback(response.data)});
					return true;
				});
			}
		}

	</script>

<?php

if (isset($_GET['action']) && $_GET['action'] === 'showfile')
{
	if (isset($_REQUEST['file']))
	{
		$file = '/' . trim($_REQUEST['file'], '/');
	}

	if (!$file || !file_exists($file))
	{
		echo CBitrixXscan::ShowMsg(GetMessage("BITRIX_XSCAN_FILE_NOT_FOUND") . htmlspecialcharsbx($file), 'red');
	}
	else
	{
		$stat = stat($file);
		$res = $scaner->CheckFile($file);

		?>

		<div class="ui-alert ui-alert-icon-warning">
			<span
				class="ui-alert-message"><strong><?= GetMessage("BITRIX_XSCAN_FAYL") ?></strong> <?= htmlspecialcharsbx($file) ?></span>
		</div>

		<div class="ui-alert ui-alert-icon-warning">
			<span
				class="ui-alert-message"><strong><?= GetMessage("BITRIX_XSCAN_M_DATE") ?></strong> <?= ConvertTimeStamp($stat['mtime'], "FULL") ?></span>
		</div>

		<div class="ui-alert ui-alert-icon-warning">
			<span
				class="ui-alert-message"><strong><?= GetMessage("BITRIX_XSCAN_C_DATE") ?></strong> <?= ConvertTimeStamp($stat['ctime'], "FULL") ?></span>
		</div>

		<?php

		if ($res)
		{
			?>

			<div class="ui-alert ui-alert-icon-warning">
				<span
					class="ui-alert-message"><strong><?= GetMessage("BITRIX_XSCAN_SCORE") ?></strong> <?= htmlspecialcharsbx($scaner->getScore()) ?></span>
			</div>

			<?php

			foreach ($scaner->getResult() as $value)
			{
				?>

				<div class="ui-alert ui-alert-danger ui-alert-icon-danger" style="flex-wrap: wrap">
					<span class="ui-alert-message"><strong><?= GetMessage("BITRIX_XSCAN_PODOZRITELQNYY_KOD") ?></strong></span>
					<span style="width: 100%"><br></span>
					<span class="ui-alert-message"><?= $value['subj'] ?></span>
					<span style="width: 100%"><br></span>
					<span><?= nl2br(htmlspecialcharsbx($value['code'])); ?></span>
				</div>

				<?php
			}
		}
		elseif (in_array($file, $scaner->getErrors()))
		{
			echo CBitrixXscan::ShowMsg(GetMessage("BITRIX_XSCAN_FILE_ERROR"), 'red');
		}
		else
		{
			echo CBitrixXscan::ShowMsg(GetMessage("BITRIX_XSCAN_FAYL_NE_VYGLADIT_POD"), 'green');
		}

		echo '<div class="ui-alert"><span class="ui-alert-message">' . highlight_file($file, true) . '</span></div>';

		echo '<div style="position:fixed; bottom:25px; right:20px;">';

		if (preg_match('/\.ph[_p][578]?$/i', $file))
		{
			echo strtolower(substr($file, -4)) !== '.ph_' ? CBitrixXscan::getIsolateButton($file) : CBitrixXscan::getUnIsolateButton($file);
		}

		if (!isset($_GET['hta']))
		{
			echo CBitrixXscan::getHideButton($file);
		}

		echo '<a class="ui-btn ui-btn-primary ui-btn-sm" href="xscan_file_edit.php?path=' . urlencode($file) . '&full_src=Y&IFRAME=Y&back_url=' . urlencode($_SERVER['REQUEST_URI']) . '">' . GetMessage("BITRIX_XSCAN_EDIT_BTN") . '</a>';

		if (isset($_GET['IFRAME_TYPE']) && $_GET['IFRAME_TYPE'] == 'SIDE_SLIDER')
		{
			echo '<button class="ui-btn ui-btn-primary-dark ui-btn-sm" onclick="BX.SidePanel.Instance.close();">' . GetMessage("BITRIX_XSCAN_CLOSE_BTN") . '</button>';
		}
		echo '</div>';

		CMain::FinalActions();
		die();
	}
}

$sort = $grid_options->GetSorting(['sort' => ['ID' => 'asc'], 'vars' => ['by' => 'by', 'order' => 'order']]);
$list = CBitrixXscan::getList($filter, $nav, $sort);
$total = CBitrixXscan::getTotal($filter);
$nav->setRecordCount($total);

$snippet = new \Bitrix\Main\Grid\Panel\Snippet();

if (!isset($_REQUEST['grid_action']))
{
	if (in_array("xdebug", get_loaded_extensions()))
	{
		echo CBitrixXscan::ShowMsg(GetMessage("BITRIX_XSCAN_XDEBUG"), 'red');
	}

	?>

	<form method="post" action="" onsubmit="return false;">

		<?= bitrix_sessid_post() ?>
		<div class="ui-form-row-inline">

			<div class="ui-form-row ui-form-row-line">
				<div class="ui-form-label">
					<div class="ui-ctl-label-text"><?= GetMessage("BITRIX_XSCAN_NACALQNYY_PUTQ") ?></div>
				</div>

				<div class="ui-form-content" style="margin-right: 15px">
					<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
						<input id="start_path" name="start_path" value="<?= htmlspecialcharsbx($start_path); ?>"
							   class="ui-ctl-element">
					</div>
				</div>

				<div class="ui-form-content">
					<button type="submit" onclick="Start();" id="start_button"
							class="ui-btn ui-btn-primary"><?= GetMessage("BITRIX_XSCAN_START_SCAN") ?></button>
				</div>
			</div>

		</div>
	</form>

	<form hidden id="download-form" method="POST" target="_blank">
		<?= bitrix_sessid_post() ?>
		<input value="" name="download-files" id="download-files">
		<input value="" name="all" id="download-checkbox">
	</form>

	<script>

		function xscan_download(files)
		{
			var gridObject = BX.Main.gridManager.getById("report_list");
			var grid = gridObject.instance
			var selectedIds = grid.getRows().getSelectedIds();

			var checkboxAll = document.getElementById('actallrows_report_list')
			var inputCheckbox = document.getElementById('download-checkbox')
			if (checkboxAll.checked) {
				inputCheckbox.value = true
			} else {
				inputCheckbox.value = false
			}

			var form = document.getElementById('download-form')
			var input = document.getElementById('download-files')
			input.value = JSON.stringify(selectedIds);
			form.submit()

			return true;
		}

		function xscan_hide_files(files)
		{
			var gridObject = BX.Main.gridManager.getById("report_list");
			var grid = gridObject.instance
			var selectedIds = grid.getRows().getSelectedIds();

			var checkboxAll = document.getElementById('actallrows_report_list')
			BX.ajax.runAction('security.xscan.hidefiles',
				{
					data: {
						files: selectedIds,
						all: checkboxAll.checked == true
					}
				}
			).then(function (response) {GridRenew();});

		return true;
		}

		function Start()
		{
			BX('start_button').classList.add('ui-btn-wait');
			BX('start_button').disabled = true;
			BX('alert_msg').innerHTML = '';
			go('Y');
		}

		function GridRenew()
		{
			var gridObject = BX.Main.gridManager.getById("report_list");

			if (gridObject.hasOwnProperty('instance')) {
				gridObject.instance.reloadTable('POST', {});
			}
		}

		function go(clean = 'N', progress = 0, total = 0, break_point = '')
		{
			BX.ajax({
				url: '/bitrix/services/main/ajax.php?action=security.xscan.scan',
				method: 'POST',
				data: {
					sessid: BX.bitrix_sessid(),
					progress: progress,
					total: total,
					clean: clean,
					break_point: break_point,
					start_path: BX('start_path').value,
				},
				onsuccess: function (result) {
					result = JSON.parse(result);
					result = result.data;
					GridRenew();
					if (result['error']) {
						BX('alert_msg').innerHTML = result['error'];
					}

					if (result['break_point']) {
						BX('progress_bar').style.display = '';
						BX('progressprc').style.width = result['prc'] + '%';

						if (result['total'] > 0){
							BX('progress').innerHTML = result['progress'] + " / " + result['total'];
						}
						else{
							BX('progress').innerHTML = result['progress'];
						}

						go('N', result['progress'], result['total'], result['break_point']);
					} else {
						BX('start_button').classList.remove('ui-btn-wait');
						BX('start_button').disabled = false;
						BX('progress_bar').style.display = 'none';
					}
				},
				onfailure: function (err, status, conf) {
					if (conf && conf.xhr && conf.xhr.getResponseHeader('xscan-bp')) {
						bp = conf.xhr.getResponseHeader('xscan-bp');
						GridRenew();
						BX.ajax.runAction('security.xscan.addError', { data: {file: bp }}).then(function (response) {
							go('N', progress, total, break_point);
						});
					}
				}
			});
		}

		BX.SidePanel.Instance.bindAnchors({
			rules:
				[
					{
						condition: [
							".*action=showfile&file=.*",
						],
						loader: "xscan",

						options: {
							animationDuration: 1,
							cacheable: false
						}
					}
				]
		});


		function income_event(a)
		{
			if (a.eventId == 'xscan-grid') {
				result = a.data.result;
				BX('alert_msg').innerHTML = result;
				GridRenew();
			}
		}

		BX.addCustomEvent('SidePanel.Slider:onMessage', income_event);

	</script>

	<div id="alert_msg">
	</div>

	<div id="progress_bar" style="display: none" class="ui-progressbar ui-progressbar-bg">
		<div class="ui-progressbar-text-before">
			<strong><?= GetMessage("BITRIX_XSCAN_IN_PROGRESS") ?></strong>
		</div>
		<div class="ui-progressbar-track">
			<div class="ui-progressbar-bar" id="progressprc" style=""></div>
		</div>
		<div class="ui-progressbar-text-after" id="progress"></div>
	</div>
	<br>

	<?php

	$APPLICATION->IncludeComponent(
		"bitrix:main.ui.filter",
		"",
		[
			"FILTER_ID" => 'report_filter',
			"GRID_ID" => 'report_list',
			"FILTER" => [
				[
					"id" => "mtime",
					"name" => GetMessage("BITRIX_XSCAN_M_DATE"),
					"type" => "date",
					"default" => "true",
				],
				[
					"id" => "ctime",
					"name" => GetMessage("BITRIX_XSCAN_C_DATE"),
					"type" => "date",
					"default" => "true",
				],
				[
					"id" => "tags",
					"name" => "tags",
					"type" => "list",
					"params" => [
						"multiple" => "Y",
					],
					"items" => ['core' => 'core',
						'no_prolog' => 'no_prolog',
						'obfuscator' => 'obfuscator',
						'lang' => 'lang',
						'hidden' => 'hidden',
						'random_name' => 'random_name',
						'marketplace' => 'marketplace',
					],
					"default" => 'true',

				],
				[
					"id" => "preset",
					"name" => "preset",
					"type" => "list",
					"items" => [
						"a" => "/bitrix/admin",
						"m" => "/bitrix/modules",
						"c" => "/bitrix/components",
						"!m" => "not /bitrix/modules",
						"pop" => GetMessage("BITRIX_XSCAN_POPULAR"),
					],
				],

			],
			"FILTER_PRESETS" => [
				"admin" => [
					"name" => '/bitrix/admin',
					"fields" => [
						"preset" => ["a"],
					],
				],
				"modules" => [
					"name" => '/bitrix/modules',
					"fields" => [
						"preset" => ["m"],
					],
				],
				"components" => [
					"name" => '/bitrix/components',
					"fields" => [
						"preset" => ["c"],
					],
				],
				"not_modules" => [
					"name" => 'not /bitrix/modules',
					"fields" => [
						"preset" => ["!m"],
					],
				],
				"popular" => [
					"name" => GetMessage("BITRIX_XSCAN_POPULAR"),
					"fields" => [
						"preset" => ["pop"],
					],
				],
			],
			"ENABLE_LIVE_SEARCH" => false,
			"ENABLE_LABEL" => true,
		]
	);
}

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	[
		'GRID_ID' => 'report_list',
		'COLUMNS' => [
			['id' => 'ID', 'name' => 'id', 'sort' => 'ID', 'default' => true],
			['id' => 'FILE_NAME', 'name' => GetMessage("BITRIX_XSCAN_NAME"), 'default' => true],
			['id' => 'FILE_TYPE', 'name' => GetMessage("BITRIX_XSCAN_TYPE"), 'default' => true],
			['id' => 'FILE_SIZE', 'name' => GetMessage("BITRIX_XSCAN_SIZE"), 'default' => true],
			['id' => 'FILE_SCORE', 'name' => GetMessage("BITRIX_XSCAN_SCORE"), 'sort' => 'SCORE', 'default' => true],
			['id' => 'FILE_MODIFY', 'name' => GetMessage("BITRIX_XSCAN_M_DATE"), 'sort' => 'MTIME', 'default' => true],
			['id' => 'FILE_CREATE', 'name' => GetMessage("BITRIX_XSCAN_C_DATE"), 'sort' => 'CTIME', 'default' => true],
			['id' => 'TAGS', 'name' => 'tags', 'default' => true],
			['id' => 'ACTIONS', 'name' => GetMessage("BITRIX_XSCAN_ACTIONS"), 'default' => true],
			['id' => 'HIDE', 'name' => GetMessage("BITRIX_XSCAN_HIDE_BTN"), 'default' => true],

		],
		'ROWS' => $list,
		'TOTAL_ROWS_COUNT' => $total,
		'SHOW_ROW_CHECKBOXES' => false,
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_TOTAL_COUNTER' => true,
		'ALLOW_COLUMNS_RESIZE' => true,
		'ALLOW_HORIZONTAL_SCROLL' => true,
		'ALLOW_SORT' => true,

		'AJAX_MODE' => 'Y',
		'AJAX_ID' => \CAjax::GetComponentID('bitrix:main.ui.grid', '', ''),
		'AJAX_OPTION_JUMP' => 'N',
		'AJAX_OPTION_STYLE' => 'N',
		'AJAX_OPTION_HISTORY' => 'N',

		'NAV_OBJECT' => $nav,
		'CURRENT_PAGE' => $nav->getCurrentPage(),
		'NAV_PARAM_NAME' => $nav->getId(),
		'SHOW_NAVIGATION_PANEL' => true,
		'SHOW_PAGINATION' => true,
		'SHOW_MORE_BUTTON' => false,
		'ENABLE_NEXT_PAGE' => true,

		'SHOW_CHECK_ALL_CHECKBOXES' => true,
		'SHOW_ROW_CHECKBOXES' => true,
		'SHOW_SELECTED_COUNTER' => true,
		'SHOW_ACTION_PANEL' => true,
		'ACTION_PANEL' => [
			'GROUPS' => [
				[
					'ITEMS' => [
						[
							"TYPE" => \Bitrix\Main\Grid\Panel\Types::BUTTON,
							"ID" => "action_button_download",
							"NAME" => "action_button_download",
							"TEXT" => GetMessage("BITRIX_XSCAN_DOWNLOAD"),
							'ONCHANGE' => [
								[
									'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
									'DATA' => [["JS" => "xscan_download()"]],
								],
							],
						],
						[
							"TYPE" => \Bitrix\Main\Grid\Panel\Types::BUTTON,
							"ID" => "action_button_hide",
							"NAME" => "action_button_hide",
							"TEXT" => GetMessage("BITRIX_XSCAN_HIDE_BTN"),
							'ONCHANGE' => [
								[
									'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
									'DATA' => [["JS" => "xscan_hide_files()"]],
								],
							],
						],
						$snippet->getForAllCheckbox(),
					],
				],
			],
		],

		'SHOW_PAGESIZE' => true,
		'DEFAULT_PAGE_SIZE' => 20,
		'PAGE_SIZES' => [
			['NAME' => "5", 'VALUE' => '5'],
			['NAME' => '10', 'VALUE' => '10'],
			['NAME' => '20', 'VALUE' => '20'],
			['NAME' => '50', 'VALUE' => '50'],
			['NAME' => '100', 'VALUE' => '100'],
		],

	]
);

if (!isset($_REQUEST['grid_action']))
{
	require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
}
