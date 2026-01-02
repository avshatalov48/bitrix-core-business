<?php

/** @var CMain $APPLICATION */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Perfmon\Model\CacheHitrateTable;
use Bitrix\UI\Buttons;
use Bitrix\UI\Toolbar\Facade\Toolbar;

const ADMIN_MODULE_NAME = 'perfmon';

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

Loader::includeModule('perfmon');


$RIGHT = CMain::GetGroupRight('perfmon');
if (!Bitrix\Main\Engine\CurrentUser::get()->isAdmin() || ($RIGHT < 'W'))
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

global $adminPage;
$adminPage->hideTitle();

\Bitrix\Main\UI\Extension::load(['ui.progressbar', 'ui.stepprocessing']);

$updateButton = new Buttons\Button([
	'color' => Buttons\Color::SECONDARY,
	'icon' => Buttons\Icon::BUSINESS,
	'click' => new Buttons\JsCode(
		'BX.UI.StepProcessing.ProcessManager.get("recountRate").showDialog()',
	),
	'text' => Loc::getMessage('PERFMON_HITRATE_RECOUNT_BUTTON'),
]);

$lastCalcTime = (int) \Bitrix\Main\Config\Option::get('perfmon', 'cacheRateDate', 0);
$connection = \Bitrix\Main\Application::getConnection();

$lastCache = $connection->query('SELECT DATE_HIT FROM b_perf_hit ORDER BY ID DESC LIMIT 1')->fetch();
if ($lastCache && $lastCache['DATE_HIT']->getTimestamp() > $lastCalcTime)
{
	Toolbar::addButton($updateButton);
}

$adminListTableID = 'tbl_perfmon_cache_hitrate';
$adminSort = new CAdminUiSorting($adminListTableID, 'RATE', 'DESC');
$by = $adminSort->getField();
$order = $adminSort->getOrder();
$adminList = new CAdminUiList($adminListTableID, $adminSort);

$arHeaders = [
	[
		'id' => 'MODULE_ID',
		'content' => Loc::getMessage('PERFMON_HITRATE_MODULE'),
		'align' => 'LEFT',
		'default' => true,
		'sticked' => 'Y',
	],
	[
		'id' => 'READ_COUNT',
		'content' => Loc::getMessage('PERFMON_HITRATE_READ'),
		'align' => 'right',
		'default' => true,
		'sticked' => 'Y',
		'sort' => 'READ_COUNT',
	],
	[
		'id' => 'WRITE_COUNT',
		'content' => Loc::getMessage('PERFMON_HITRATE_WRITE'),
		'align' => 'right',
		'default' => true,
		'sticked' => 'Y',
		'sort' => 'WRITE_COUNT',
	],
	[
		'id' => 'CLEAN_COUNT',
		'content' => Loc::getMessage('PERFMON_HITRATE_CLEAN'),
		'align' => 'right',
		'default' => true,
		'sticked' => 'Y',
		'sort' => 'CLEAN_COUNT',
	],
	[
		'id' => 'RATE',
		'content' => Loc::getMessage('PERFMON_HITRATE_RATE'),
		'align' => 'right',
		'default' => true,
		'sticked' => 'Y',
		'sort' => 'RATE',
	],
	[
		'id' => 'CACHE_SIZE',
		'content' => Loc::getMessage('PERFMON_HITRATE_SIZE'),
		'title' => Loc::getMessage('PERFMON_HITRATE_SIZE_ALT'),
		'align' => 'right',
		'default' => true,
		'sticked' => 'Y',
		'sort' => 'CACHE_SIZE',
	],
	[
		'id' => 'PATH',
		'content' => Loc::getMessage('PERFMON_HITRATE_PATH'),
		'default' => true,
		'sticked' => 'Y',
	],
];

$adminList->AddHeaders($arHeaders);

$filterFields = [
	[
		'id' => '=MODULE_ID',
		'name' => Loc::getMessage('PERFMON_HITRATE_MODULE'),
		'filterable' => '',
		'default' => true,
		'field' => '=MODULE_ID',
	],
	[
		'id' => 'RATE',
		'name' => Loc::getMessage('PERFMON_HITRATE_RATE'),
		'filterable' => '',
		'field' => 'RATE',
	],
];

$filter = [];
$adminList->AddFilter($filterFields, $filter);
if (isset($filter['RATE']))
{
	$where = new CSQLWhere();
	$op = $where->MakeOperation($filter['RATE']);

	if ($filter['RATE'] !== $op['FIELD'])
	{
		$op['OPERATOR'] = mb_substr($filter['RATE'], 0, mb_strlen($filter['RATE']) - mb_strlen($op['FIELD']));

		$filter[$op['OPERATOR'] . 'RATE'] = $op['FIELD'];
		unset($filter['RATE']);
	}
}

$filterOption = new Bitrix\Main\UI\Filter\Options($adminListTableID);
$filterData = $filterOption->getFilter($filterFields);
$find = trim($filterData['FIND'] ?? '', " \t\n\r");
if ($find)
{
	$filter['%INIT_DIR'] = $find;
}


$nav = $adminList->getPageNavigation('nav-perfmon-cache-hitrate');
if ($adminList->isTotalCountRequest())
{
	$count = CacheHitrateTable::getCount($filter);
	$adminList->sendTotalCountResponse($count);
}

$cacheRateData = CacheHitrateTable::query()
	->setSelect(['*'])
	->setOrder([$by => $order])
;
if (!empty($filter))
{
	$cacheRateData->setFilter($filter);
}
if ($nav instanceof Bitrix\Main\UI\PageNavigation)
{
	$cacheRateData->setOffset($nav->getOffset());
	$cacheRateData->setLimit($nav->getLimit() + 1);
}

$cacheRateData = $cacheRateData->exec();

function getProgress(float $value): string
{
	if ($value > 85)
	{
		$type = 'success';
	}
	elseif ($value > 50)
	{
		$type = 'warning';
	}
	else
	{
		$type = 'danger';
	}

	return '<div class="ui-progressbar ui-progressbar-' . $type . '">
		<div class="ui-progressbar-track">
			<div class="ui-progressbar-bar" style="width:' . $value . '%;"></div>
		</div>
		<div class="ui-progressbar-text-after">' . $value . '%</div>
	</div>';
}

$n = 0;
$pageSize = $adminList->getNavSize();
while ($cache = $cacheRateData->fetch())
{
	$n++;
	if ($n > $pageSize)
	{
		break;
	}

	$rowList[$cache['ID']] = $row = &$adminList->AddRow(
		$cache['ID'],
		$cache,
	);

	if (isset($cache['RATE']))
	{
		$row->AddViewField('RATE', getProgress($cache['RATE']));
	}
	$row->AddViewField(
		'PATH',
		'<span class="compound base">' . $cache['BASE_DIR'] . '</span>'
		. '<span class="compound init">' . $cache['INIT_DIR'] . '</span>'
		. '<span class="compound path">' . $cache['FILE_NAME'] . '</span>',
	);
	$row->AddViewField('CACHE_SIZE', CFile::FormatSize($cache['CACHE_SIZE']));
	$row->AddViewField('MODULE_ID', '<span class="ui-label ui-label-tag-light ui-label-fill"><span class="ui-label-inner">' . $cache['MODULE_ID'] . '</span></span>');

	unset($row);
}

$nav->setRecordCount($nav->getOffset() + $n);
$adminList->setNavigation($nav, '', false);

$totalRate = \Bitrix\Main\Config\Option::get('perfmon', 'cacheRateTotal', 0);

$APPLICATION->SetTitle(Loc::getMessage('PERFMON_CACHE_HITRATE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

$APPLICATION->IncludeComponent('bitrix:ui.toolbar', 'admin', []);
$adminList->DisplayFilter($filterFields);
?>

<style>
	.compound {
		padding: 2px 0;
	}
	.base {
		background-color: var(--ui-color-palette-green-30);
		border-top-left-radius: 1rem;
		border-bottom-left-radius: 1rem;
		padding-left: 4px;
	}
	.init {
		background-color: var(--ui-color-palette-blue-30);
	}
	.path {
		background-color: var(--ui-color-palette-red-30);
		border-top-right-radius: 1rem;
		border-bottom-right-radius: 1rem;
		padding-right: 4px;
	}
	.ui-label-inner {
		font-size: 12px;
	}
</style>
<div id="total-wrapper"></div>
<script>
	let totalRateValue = <?=$totalRate?>;
	let color = BX.UI.ProgressBar.Color.DANGER;
	if (totalRateValue > 85)
	{
		color = BX.UI.ProgressBar.Color.SUCCESS;
	}
	else if (totalRateValue > 50)
	{
		color = BX.UI.ProgressBar.Color.WARNING;
	}
	let totalProgress = new BX.UI.ProgressBar({
		color,
		fill: true,
		value: totalRateValue,
		textBefore: '<?=Loc::getMessage('PERFMON_HITRATE_TOTAL');?>',
		textAfter: totalRateValue + '%',
	});

	totalProgress.renderTo(BX('total-wrapper'));

	BX.UI.StepProcessing.ProcessManager.create({
		'id': 'recountRate',
		'controller': 'bitrix:perfmon.cacherate',
		'showButtons': {
			'start':true
		},
		'messages': {
			'DialogTitle': '<?=Loc::getMessage('PERFMON_HITRATE_RECOUNT_TITLE');?>',
			'DialogSummary': '<?=Loc::getMessage('PERFMON_HITRATE_RECOUNT_SUMMARY');?>'
		},
		'dialogMinWidth': 500,
		'dialogMaxWidth': 500,
		'queue': [{
			'action': 'recountStat',
			'title': '<?=Loc::getMessage('PERFMON_HITRATE_RECOUNT_SUMMARY');?>',
			'handlers': {
				'StepCompleted': function (state, result) {
					if (state === BX.UI.StepProcessing.ProcessResultStatus.progress) {
						if (result.LAST_ID) {
							this.setParam('offset', result.LAST_ID);
							this.setParam('processed', result.PROCESSED_ITEMS);
						}
					}
					if (state === BX.UI.StepProcessing.ProcessResultStatus.completed)
					{
						this.setParam('offset', 0);
						this.setParam('processed', 0);
					}
				},
			}
		},
		{
			'action': 'countRate',
			'title': '<?=Loc::getMessage('PERFMON_HITRATE_RECOUNT_RATE_TITLE');?>',
			'handlers': {
				'StepCompleted': function (state, result) {
					if (state === BX.UI.StepProcessing.ProcessResultStatus.progress) {
						if (result.LAST_ID) {
							this.setParam('offset', result.LAST_ID);
							this.setParam('processed', result.PROCESSED_ITEMS);
						}
					}
				},
				'StateChanged': function (state, result) {
					if (state === BX.UI.StepProcessing.ProcessResultStatus.completed) {
						let grid = BX.Main.gridManager.getById("tbl_perfmon_cache_hitrate");
						if (grid) {
							grid.instance?.reload();
						}
					}
				}
			}
		},
		{
			'action': 'countTotalRate',
			'title': '<?=Loc::getMessage('PERFMON_HITRATE_RECOUNT_RATETOTAL_TITLE');?>',
			'handlers': {
				'StateChanged': function (state, result) {
					if (state === BX.UI.StepProcessing.ProcessResultStatus.completed) {
						let color = BX.UI.ProgressBar.Color.DANGER;
						if (result.TOTAL_RATE > 85) {
							color = BX.UI.ProgressBar.Color.SUCCESS;
						}
						else if (result.TOTAL_RATE > 50) {
							color = BX.UI.ProgressBar.Color.WARNING;
						}

						totalProgress.update(result.TOTAL_RATE);
						totalProgress.setColor(color);
						totalProgress.setTextAfter(result.TOTAL_RATE + '%');
						this.closeDialog();
					}
				}
			}
		}]
	});
</script>
<?php
$params = [
	'ACTION_PANEL' => false,
	'SHOW_COUNT_HTML' => true,
];
$adminList->DisplayList($params);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
