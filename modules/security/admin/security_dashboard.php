<?php

use Bitrix\Main\EventLog\Internal\EventLogTable;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

define("ADMIN_MODULE_NAME", "security");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");


Extension::load([
	'ui.design-tokens',
	'amcharts',
	'amcharts_serial',
	'main.wwallpopup'
]);


CModule::IncludeModule('security');
IncludeModuleLangFile(__FILE__);

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 **/

$isPortal = \Bitrix\Main\ModuleManager::isModuleInstalled('intranet');

// last 20 events
$events = [
	'SECURITY_VIRUS',
	'SECURITY_FILTER_SQL',
	'SECURITY_FILTER_XSS',
	'SECURITY_FILTER_XSS2',
	'SECURITY_FILTER_PHP',
	'SECURITY_REDIRECT',
	'SECURITY_HOST_RESTRICTION',
	'SECURITY_WWALL_EXIT',
	'SECURITY_WWALL_UNSET',
	'SECURITY_WWALL_MODIFY'
];

$arFilter = [
	'=AUDIT_TYPE_ID' => $events
];
$arNavParams = [
	'nPageSize' => 20
];

$rsData = CEventLog::GetList(array('ID' => 'desc'), $arFilter, $arNavParams);

$arAuditTypes = CEventLog::GetEventTypes();
$securityLog = [];

while ($row = $rsData->Fetch())
{
	$title = array_key_exists($row['AUDIT_TYPE_ID'], $arAuditTypes)
		? preg_replace("/^\\[.*?\\]\\s+/", "", $arAuditTypes[$row['AUDIT_TYPE_ID']])
		: $row['AUDIT_TYPE_ID'];

	if (str_starts_with($title, 'SECURITY_WWALL_'))
	{
		// common title
		$title = \Bitrix\Main\Localization\Loc::getMessage('MAIN_EVENTLOG_SECURITY_WWALL_COMMON');

		// replace description
		$row['ITEM_ID'] = $row['REQUEST_URI'];
	}

	$row['TITLE'] = $title;

	$securityLog[] = $row;
}

// last 30 days event stats
$daysAgo = 30;
$sqlHelper = \Bitrix\Main\Application::getConnection()->getSqlHelper();
$dateFrom = (new \Bitrix\Main\Type\DateTime())->add('-P'.($daysAgo).'D');
$historyStatsResult = EventLogTable::query()
	->addSelect(Query::expr('CNT')->count('ID'))
	->addSelect(new ExpressionField(
		'DT',
		str_replace(
			['%', 'FIELD'],
			['%%', '%s'],
			$sqlHelper->formatDate('DD.MM', 'FIELD')),
		'TIMESTAMP_X'
	))
	->where('TIMESTAMP_X', '>', $dateFrom)
	->whereIn('AUDIT_TYPE_ID', $events)
	->exec();

$historyStats = [];
while ($row = $historyStatsResult->fetch())
{
	$historyStats[$row['DT']] = (int) $row['CNT'];
}

// format for graph
$historyItems = [];
for ($i=$daysAgo-1; $i>=0; $i--)
{
	$dateFrom = (new \Bitrix\Main\Type\DateTime())->add('-P'.$i.'D');
	$date = $dateFrom->format('d.m');

	$count = isset($historyStats[$date]) ? $historyStats[$date] : 0;

	$historyItems[] = [
		'date' => $date,
		'value' => $count
	];
}


// check status, do we need to update
$modulesToUpdate = \Bitrix\Main\Security\W\Rules\RuleRecordTable::query()
	->setDistinct(true)
	->addSelect('MODULE')
	->addSelect('MODULE_VERSION')
	->fetchAll();

$APPLICATION->SetTitle(GetMessage("SEC_WWALL_DASHBOARD_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>

<?php if (empty($modulesToUpdate)): ?>
	<div class="adm-security-banner">
		<div class="adm-security-banner_icon"></div>
		<div class="adm-security-banner_content">
			<div class="adm-security-banner_title"><?=
				$isPortal
					? Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_TITLE_SEC_CP')
					: Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_TITLE_SEC') ?></div>
			<div class="adm-security-banner_text"><?=
				$isPortal
					? Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_DESC_SEC_CP')
					: Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_DESC_SEC')?></div>
			<div class="adm-security-banner__btn-box">
				<div class="adm-security-banner__desc">
					<div class="adm-security-banner__desc_text"><?= Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_UPD_SEC') ?></div>
					<a href="<?= Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_UPD_HISTORY')?>" class="adm-security-banner__desc_link tooltip" target="_blank" data-tooltip-content=""><?= Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_UPD_NEW_SEC') ?></a>
				</div>
			</div>
		</div>
	</div>
<?php else: ?>
	<div class="adm-security-banner --warning">
		<div class="adm-security-banner_icon"></div>
		<div class="adm-security-banner_content">
			<div class="adm-security-banner_title"><?=
				$isPortal
					? Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_TITLE_UNSEC_CP')
					: Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_TITLE_UNSEC')?></div>
			<?php if(!empty($securityLog)): ?>
				<div class="adm-security-banner_text"><?=
					$isPortal
						? Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_DESC_UNSEC_CP')
						: Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_DESC_UNSEC')?></div>
			<?php else: ?>
				<div class="adm-security-banner_text"><?=
					$isPortal
						? Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_DESC_UNSEC_EMPTY_CP')
						: Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_DESC_UNSEC_EMPTY')?></div>
			<?php endif; ?>
			<div class="adm-security-banner__btn-box">
				<a href="/bitrix/admin/update_system.php"><button class="adm-btn adm-btn-refresh --size-32"><?= Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_UPD_UNSEC') ?></button></a>
			</div>
		</div>
	</div>
<?php endif; ?>

<div class="adm-security-grid">
	<div class="adm-security-history">
		<div class="adm-security-history_title"><?= Loc::getMessage('SEC_WWALL_DASHBOARD_HISTORY_TITLE') ?></div>
		<div class="adm-security-history_inner">
			<?php if (empty($securityLog)): ?>
				<div class="adm-security-history_empty-state">
					<div class="adm-security-history_empty-state_icon"></div>
					<div class="adm-security-history_empty-state_text"><?= Loc::getMessage('SEC_WWALL_DASHBOARD_HISTORY_TITLE_EMPTY') ?></div>
				</div>
			<?php else: foreach ($securityLog as $logItem): ?>
				<div class="adm-security-history_item">
					<div class="adm-security-history_icon"></div>
					<div class="adm-security-history_content">
						<div class="adm-security-history_subtitle"><?php echo htmlspecialcharsbx($logItem['TITLE']) ?></div>
						<div class="adm-security-history_info">
							<div class="adm-security-history_info-value"><?php echo FormatDate("x", MakeTimeStamp($logItem["TIMESTAMP_X"])) ?></div>
							<div class="adm-security-history_info-value"><?php echo htmlspecialcharsbx($logItem['REMOTE_ADDR']) ?></div>
							<a href="#" class="adm-security-history_info-value --link tooltip" data-tooltip-content="<?php echo htmlspecialcharsbx($logItem['ITEM_ID'])?>"><?= Loc::getMessage('SEC_WWALL_DASHBOARD_HISTORY_OBJECT') ?></a>
						</div>
					</div>
					<div class="adm-security-history_status"><?= Loc::getMessage('SEC_WWALL_DASHBOARD_HISTORY_OBJECT_SEC') ?></div>
				</div>
			<?php endforeach; endif; ?>
		</div>
	</div>
	<div class="adm-security-grid-inner">
		<div class="adm-security-graph">
			<div class="adm-security-graph_title"><?= Loc::getMessage('SEC_WWALL_DASHBOARD_STATS_TITLE') ?>
			</div>
			<div id="chartdiv" class="adm-security-graph_inner"></div>
		</div>
		<div class="adm-security-row-multiple">
			<div class="adm-security-info__box --core">
				<div class="adm-security-info_icon"></div>
				<div class="adm-security-info_title"><?= Loc::getMessage('SEC_WWALL_DASHBOARD_KERNEL_TITLE') ?></div>
				<div class="adm-security-info_balloon"><?= Loc::getMessage('SEC_WWALL_DASHBOARD_KERNEL_SOON') ?></div>
			</div
				<!-- delete --ready if you want to see update status-->
			<div class="adm-security-info__box --platform <?php if($modulesToUpdate) echo "--warning" ?>">
				<div class="adm-security-info_icon"></div>
				<div class="adm-security-info_title"><?=
					$isPortal
						? Loc::getMessage('SEC_WWALL_DASHBOARD_SITE_TITLE_CP')
						: Loc::getMessage('SEC_WWALL_DASHBOARD_SITE_TITLE')?></div>
				<?php if($modulesToUpdate): ?>
					<div class="adm-security-info_balloon"><?= Loc::getMessage('SEC_WWALL_DASHBOARD_SITE_TO_UPDATE') ?></div>
				<?php else: ?>
					<div class="adm-security-info_balloon --ready"><?= Loc::getMessage('SEC_WWALL_DASHBOARD_SITE_UPDATED') ?></div>
				<?php endif; ?>
				<div class="adm-security-info_btn-box">
					<?php if($modulesToUpdate): ?>
						<a href="/bitrix/admin/update_system.php"><button class="adm-btn adm-btn-refresh --size-23"><?= Loc::getMessage('SEC_WWALL_DASHBOARD_SITE_UPDATE_ACTION') ?></button></a>
					<?php endif; ?>
					<a target="_blank" href="<?= Loc::getMessage('SEC_WWALL_DASHBOARD_STATUS_UPD_HISTORY')?>" class="adm-security-info_link tooltip" data-tooltip-content=""><?= Loc::getMessage('SEC_WWALL_DASHBOARD_SITE_UPDATE_WHATS_NEW') ?></a>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	BX.ready(function() {

		// popup sample
		var popup = new BX.Main.WwallPopup({
			isSuccess: false,
			isPortal: <?= Json::encode($isPortal) ?>
		});
		// popup.show();

		this.tooltip = new BX.PopupWindow({
			bindElement: false,
			className: 'adm-security-tooltip',
			darkMode: true,
			angle: true,
			bindOptions: { position: "top" },
			width: 300,
			content: '',
		});

		var securityTooltip = document.querySelectorAll('.tooltip');
		for (let i = 0; i < securityTooltip.length; i++)
		{
			var securityTooltipItem = securityTooltip[i];

			securityTooltipItem.addEventListener("mouseenter", function(event) {
				setTimeout(function() {
					this.tooltip.setBindElement(event.target);
					if (event.target.dataset.tooltipContent.length)
					{
						this.tooltip.contentContainer.append(event.target.dataset.tooltipContent);
						this.tooltip.show();
					}
				}.bind(this), 400)
			}.bind(this));

			securityTooltipItem.addEventListener("mouseleave", function() {
				setTimeout(function() {
					this.tooltip.contentContainer.textContent = '';
					this.tooltip.close();
				}.bind(this), 400)

			}.bind(this));

		}

		var chartData = <?php echo json_encode($historyItems) ?>;

		var chart = AmCharts.makeChart("chartdiv", {
			"type": "serial",
			"colorRanges": [{
				"start": 0,
				"end": 2,
				"color": "#44a41e",
				"variation": 0,
				"valueProperty": "value",
				"colorProperty": "color"
			}, {
				"start": 3,
				"end": 4,
				"color": "#dbc62d",
				"variation": 0,
				"valueProperty": "value",
				"colorProperty": "color"
			}, {
				"start": 5,
				"end": 100000,
				"color": "#e96d2a",
				"variation": 0,
				"valueProperty": "value",
				"colorProperty": "color"
			}],
			"dataProvider": chartData,
			"categoryField": "date",
			"dataDateFormat": "MM-DD-YYYY",
			"balloon": {
				"adjustBorderColor": false,
				"horizontalPadding": 8,
				"verticalPadding": 4,
				"borderThickness": 0,
				"color": "#fff",
				"cornerRadius": 4,
				"fillAlpha": 1,
			},
			"graphs": [{
				"alphaField": "alpha",
				"balloonText": "<?= CUtil::JSEscape(Loc::getMessage('SEC_WWALL_DASHBOARD_STATS_TOOLTIP'))?>",
				"valueField": "value",
				"type": "column",
				"fillAlphas": 1,
				"lineAlpha": 0,
				"colorField": "color",
				"cornerRadiusTop": 1,
				}],
			"categoryAxis":
			{
				// "parseDates": true,
				"autoGridCount": true,
				"gridCount": chartData.length,
				"gridPosition": "start",
				"axisColor": "#ececec",
				"color": "#a8adb4",
				"fontSize": 7,
				"gridColor": "#ececec",
				"gridAlpha": 0,
				"tickPosition": "start",
			},
			"valueAxes": [{
				"minPeriod": "DD.MM",
				"axisColor": "#ececec",
				"dateFormats": {
					period: "DD",
					format: "DD MMM",
				},
				"color": "#a8adb4",
				"fontSize": 7,
			}],
			"guides": [{
				"lineColor": "#ececec",
			}],
			"gridAboveGraphs": false,
			"startDuration": 1,
			"autoMargins": false,
			"marginLeft": 25,
			"marginRight": 18,
			"marginBottom": 25,
		});

		AmCharts.addInitHandler(function(chart) {

			var dataProvider = chart.dataProvider;
			var colorRanges = chart.colorRanges;

			function ColorLuminance(hex, lum) {
				hex = String(hex).replace(/[^0-9a-f]/gi, '');
				if (hex.length < 6) {
					hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
				}
				lum = lum || 0;

				var rgb = "#",
					c, i;
				for (i = 0; i < 3; i++) {
					c = parseInt(hex.substr(i * 2, 2), 16);
					c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
					rgb += ("00" + c).substr(c.length);
				}

				return rgb;
			}

			if (colorRanges)
			{
				var item;
				var range;
				var valueProperty;
				var value;
				var average;
				var variation;
				for (var i = 0, iLen = dataProvider.length; i < iLen; i++) {

					item = dataProvider[i];

					for (var x = 0, xLen = colorRanges.length; x < xLen; x++) {

						range = colorRanges[x];
						valueProperty = range.valueProperty;
						value = item[valueProperty];

						if (value >= range.start && value <= range.end) {
							average = (range.start - range.end) / 2;

							if (value <= average)
								variation = (range.variation * -1) / value * average;
							else if (value > average)
								variation = range.variation / value * average;

							item[range.colorProperty] = ColorLuminance(range.color, variation.toFixed(2));
						}
					}
				}
			}

		}, ["serial"]);
	});
</script>

<?php
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");