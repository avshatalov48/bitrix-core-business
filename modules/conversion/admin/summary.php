<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Conversion\RateManager;
use Bitrix\Conversion\ReportContext;
use Bitrix\Conversion\GeneratorContext;
use Bitrix\Main\Loader;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Loader::IncludeModule('conversion');

if ($APPLICATION->GetGroupRight('conversion') < 'R')
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));

$userOptions = CUserOptions::GetOption('conversion', 'filter', array());

// PERIOD

$from = ($d = $_GET['from'] ?: $userOptions['from']) && Date::isCorrect($d) ? new Date($d) : Date::createFromPhp(new DateTime('first day of last month'));
$to   = ($d = $_GET['to'  ] ?: $userOptions['to'  ]) && Date::isCorrect($d) ? new Date($d) : Date::createFromPhp(new DateTime('last day of this month'));

// SITES

$sites = array();

$result = SiteTable::getList(array(
	'select' => array('LID', 'NAME'),
	'order'  => array('DEF' => 'DESC', 'SORT' => 'ASC'),
));

while ($row = $result->fetch())
{
	$sites[$row['LID']] = $row['NAME'];
}

$site = $_GET['site'] ?: $userOptions['site'];

if (! $siteName = $sites[$site])
{
	list ($site, $siteName) = each($sites);
}

// SPLITS

$groupedAttributeTypes = \Bitrix\Conversion\AttributeManager::getGroupedTypes(); // $splitGroups
unset($groupedAttributeTypes[null]);

$attributeGroupName = $_GET['split'] ?: $userOptions['split']; // $splitGroupKey

if (! $attributeTypes = $groupedAttributeTypes[$attributeGroupName]) // $splitGroup
{
	list ($attributeGroupName, $attributeTypes) = each($groupedAttributeTypes);
}

$attributeGroupTypes = \Bitrix\Conversion\AttributeGroupManager::getTypes();

$splits = array();

foreach ($attributeTypes as $name => $type)
{
	$splits[$name] = array(
		'NAME'  => $name,
		'TITLE' => $type['NAME'] ?: $name,
		'SPLIT_BY' => $type['SPLIT_BY'],
		'BG_COLOR' => $type['BG_COLOR'] ?: '#4b9ec1',
	);
}

$splits += array(
	'other' => array(
		'TITLE' => Loc::getMessage('CONVERSION_SPLIT_OTHER'),
		'BG_COLOR' => '#96c023',
	),
	'total' => array(
		'TITLE' => Loc::getMessage('CONVERSION_SPLIT_TOTAL'),
		'BG_COLOR' => '#33ade1',
	),
);

// RATES

$scale = array(0.5, 1, 1.5, 2, 5);

if ($rateTypes = RateManager::getTypes(array('ACTIVE' => true)))
{
	$topRateName = $_GET['rate'] ?: $userOptions['rate'];

	if ($topRateType = $rateTypes[$topRateName])
	{
		$rateTypes = array($topRateName => $topRateType) + $rateTypes;
	}
	else
	{
		list ($topRateName, $topRateType) = each($rateTypes);
	}

	if (is_array($topRateType['SCALE']) && count($topRateType['SCALE']) === 5)
	{
		$scale = $topRateType['SCALE'];
	}
}
else
{
	$topRateName = null;
	$topRateType = null;
}

// FILTER

$filter = array(
	'from'  => $from->toString(),
	'to'    => $to->toString(),
	'site'  => $site,
	'split' => $attributeGroupName,
	'rate'  => $topRateName,
);

CUserOptions::SetOption('conversion', 'filter', array_merge($userOptions, $filter));

$filter['lang'] = LANGUAGE_ID;

// CONTEXT

GeneratorContext::generateInitialData($from);

$context = new ReportContext();

$context->setAttribute('conversion_site', $site);

$splitRates = $context->getSplitRatesDeprecated($splits, $rateTypes, array(
	'>=DAY' => $from,
	'<=DAY' => $to,
), array('FORMAT' => 'Y-m-d', 'SELECT' => 'RATE'));

$totalRates = $splitRates['total'];
unset($splitRates['total']);

if ($totalTopRate = reset($totalRates))
{
	$totalTopDenominator = $totalTopRate['DENOMINATOR'];
	$totalTopConversion  = $totalTopRate['RATE'] * 100;
}
else
{
	$totalTopDenominator = 0;
	$totalTopConversion  = 0;
}

// VIEW

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/conversion/admin/helpers/scale.php');

$APPLICATION->SetTitle(Loc::getMessage('CONVERSION_SUMMARY_TITLE'));

CJSCore::Init(array('amcharts', 'amcharts_serial'));

function conversion_renderRate(array $rate, array $rateType)
{
	?>
	<div class="stat-item">
		<span class="stat-item-subtitle"><?=$rateType['NAME']?></span>
		<div class="stat-item-block stat-item-block-conversion">
			<span class="stat-item-block-inner">
				<span class="stat-item-block-title"><?=Loc::getMessage('CONVERSION_SALE_RATE_CONVERSION')?></span>
				<span class="stat-item-block-digit"><?=number_format($rate['RATE'] * 100, 2)?><span>%</span></span>
			</span>
		</div>
		<div class="stat-item-block stat-item-block-first">
			<span class="stat-item-block-inner">
				<?

				if (isset($rate['SUM']))
				{
					?>
					<span class="stat-item-block-title"><?=Loc::getMessage('CONVERSION_SALE_RATE_SUM')?></span>
					<span class="stat-item-block-digit"><?=number_format($rate['SUM'])?>
						<span><? if (isset($rateType['UNITS']['SUM'])) echo htmlspecialcharsbx($rateType['UNITS']['SUM']); ?></span>
					</span>
					<?
				}

				?>
			</span>
		</div>
		<div class="stat-item-block">
			<span class="stat-item-block-inner">
				<span class="stat-item-block-title"><?=Loc::getMessage('CONVERSION_SALE_RATE_QUANTITY')?></span>
				<span class="stat-item-block-digit"><?=(isset($rate['QUANTITY']) ? $rate['QUANTITY'] : $rate['NUMERATOR']) ?></span>
			</span>
		</div>
	</div>
	<?
}

function conversion_renderGraph(array $splitRates, array $splits, $height)
{
	static $index = 0;
	$index++;

	$data = array();
	$graphs = '';

	foreach ($splitRates as $splitKey => $rates)
	{
		if ($rate = reset($rates))
		{
			$split = $splits[$splitKey];

			foreach ($rate['STEPS'] as $step => $rate)
			{
				$data[$step][$splitKey] = number_format($rate * 100, 2);
			}

			ksort($data);

			$splitKey = CUtil::JSEscape($splitKey);

			$graphs .= "
				var {$splitKey} = new AmCharts.AmGraph();
				{$splitKey}.connect       = false;
				{$splitKey}.balloonText   = '".CUtil::JSEscape($split['TITLE']).": [[value]]%';
				{$splitKey}.valueField    = '{$splitKey}';
				{$splitKey}.type          = 'smoothedLine';
				{$splitKey}.lineThickness = 2;
				{$splitKey}.bullet        = 'round';
				{$splitKey}.lineColor     = '".CUtil::JSEscape($split['BG_COLOR'])."';
				chart.addGraph({$splitKey});
			";
		}
	}

	?>
	<div id="bitrix-conversion-graph-<?=$index?>" style="height:<?=$height?>"></div>
	<script type="text/javascript">

		AmCharts.ready(function()
		{
			var chart = new AmCharts.AmSerialChart();

			chart.path         = BX.message('AMCHARTS_PATH') || '/bitrix/js/main/amcharts/3.13/'; // TODO
			chart.pathToImages = BX.message('AMCHARTS_IMAGES_PATH') || '/bitrix/js/main/amcharts/3.13/images/'; // TODO

			var monthNames = [];
			var shortMonthNames = [];
			for(var m = 1; m <= 12; m++)
			{
				monthNames.push(BX.message['MONTH_'+m.toString()]);
				shortMonthNames.push(BX.message['MONTH_'+m.toString()+'_S']);
			}
			AmCharts.monthNames = monthNames;
			AmCharts.shortMonthNames = shortMonthNames;

			chart.zoomOutText   = '<?=CUtil::JSEscape(Loc::getMessage('CONVERSION_SUMMARY_GRAPH_SHOW_ALL'))?>';
			chart.dataProvider = <?

				$json = array();

				foreach ($data as $step => $rates)
				{
					$json []= array('date' => $step) + $rates;
				}

				echo Json::encode($json);

			?>;
			chart.categoryField = 'date';
			chart.theme         = 'none';

			chart.decimalSeparator = ',';
			chart.autoMargins   = false;
			chart.marginLeft    = 60;
			chart.marginRight   = 20;
			chart.marginTop     = 20;
			chart.marginBottom  = 40;

			chart.chartCursor = {
				enabled: true,
				cursorColor: '#808080',
				oneBalloonOnly: true,
				categoryBalloonEnabled: true,
				categoryBalloonColor: '#000000',
				categoryBalloonDateFormat: 'DD.MM.YYYY'
			};

			chart.chartScrollbar = {};
			chart.dataDateFormat = 'YYYY-MM-DD';
			chart.valueAxes = [{'unit': '%'}];
			chart.categoryAxis =
			{
				parseDates: true,
				minPeriod: 'DD',
				equalSpacing: true,
				markPeriodChange: false,
				autoGridCount: false,
				gridCount: 0,
				dateFormats: [
					{period: 'DD', format: 'D MMM'},
					{period: 'WW', format: 'D MMM'},
					{period: 'MM', format: 'MMMM'},
					{period: 'YYYY', format: 'YYYY'}
				]
			};

			chart.addListener('zoomed', function(params) {
				var chart = params.chart;
				var axis  = chart.categoryAxis;

				while (axis.guides.length > 0)
					axis.removeGuide(axis.guides[0]);

				var step = Math.ceil((1+params.endIndex-params.startIndex) / 10);
				for (var i = params.startIndex; i <= params.endIndex; i = i + step)
				{
					axis.addGuide(new AmCharts.Guide({Guide: {
						date: chart.dataProvider[i].date,
						label: AmCharts.formatDate(chart.chartData[i].category, 'D MMM')
					}}));
				}

				chart.validateNow();
			});

			<?=$graphs?>

			chart.write('bitrix-conversion-graph-<?=$index?>');
		});
	</script>
	<?
}

Bitrix\Conversion\AdminHelpers\renderFilter($filter);

?>
	<div class="adm-detail-block">


		<div class="adm-detail-content-wrap">
			<div class="adm-detail-content">
				<div class="adm-detail-title"><?=Loc::getMessage('CONVERSION_SUMMARY_TITLE2')?></div>
				<div class="adm-detail-content-item-block stat-item-block-container">
					<?

					$menuItems = array();

					foreach ($sites as $id => $name)
					{
						$menuItems[$name] = array_merge($filter, array('site' => $id));
					}

					Bitrix\Conversion\AdminHelpers\renderScale(array(
						'SITE_NAME'  => $siteName,
						'SITE_MENU'  => $menuItems,
						'CONVERSION' => $totalTopConversion,
						'SCALE'      => $scale,
					));

					?>
					<div class="stat-item-container item-total">
						<div class="stat-item-title">
							<?=Loc::getMessage('CONVERSION_SUMMARY_PERIOD', array(
								'#from#' => $from,
								'#to#'   => $to,
							))?>
						</div>
						<div class="stat-graph-container">
							<span class="stat-graph-title"><?=Loc::getMessage('CONVERSION_SUMMARY_TOTAL_GRAPH')?></span>
							<?conversion_renderGraph(array('total' => $totalRates), $splits, '200px')?>
						</div>
					</div>
				</div>
			</div>
			<div class="adm-detail-content-btns-wrap">
				<div class="adm-detail-content-btns adm-detail-content-btns-empty"></div>
			</div>
		</div>


		<div class="adm-detail-content-wrap">
			<div class="adm-detail-content">
				<div class="adm-detail-title stat-title">
					<span id="bitrix-conversion-split" class="stat-title-name">
						<?=($g = $attributeGroupTypes[$attributeGroupName]) ? $g['NAME'] : $attributeGroupName?>
					</span>
					<span class="stat-title-select"></span>
					<?

					$menuItems = array();

					foreach ($groupedAttributeTypes as $name => $types)
					{
						$menuItems[($g = $attributeGroupTypes[$name]) ? $g['NAME'] : $name] = array_merge($filter, array('split' => $name));
					}

					Bitrix\Conversion\AdminHelpers\renderMenu('bitrix-conversion-split', $menuItems);

					?>
				</div>
				<div class="adm-detail-content-item-block stat-item-block-container">
					<?

					// Total Split /////////////////////////////////////////////////////////////////////////////////////

					$split = $splits['total'];
					$rates = $totalRates;

					?>
					<div class="stat-item-container item-detailed">
						<span class="stat-item-equality"></span>
						<div class="stat-graph-container">
							<span class="stat-graph-title"><?=Loc::getMessage('CONVERSION_SUMMARY_DETAILED')?></span>
							<?conversion_renderGraph($splitRates, $splits, '300px')?>
						</div>
						<div class="stat-item-title" style="background: <?=$split['BG_COLOR']?>">
							<?=$split['TITLE']?>
							<span id="bitrix-conversion-rate" class="stat-item-title-name">
								<?=$topRateType ? $topRateType['NAME'] : ''?>
							</span>
							<span class="stat-item-title-name-select"></span>
							<?

							$menuItems = array();

							foreach ($rateTypes as $name => $type)
							{
								$menuItems[$type['NAME']] = array_merge($filter, array('rate' => $name));
							}

							Bitrix\Conversion\AdminHelpers\renderMenu('bitrix-conversion-rate', $menuItems);

							?>
							<span class="stat-item-title-traffic">
								<span><?=Loc::getMessage('CONVERSION_SUMMARY_TRAFFIC')?>:</span>
								<?=$totalTopDenominator?> <span>|</span> 100%
							</span>
						</div>
						<?

						$rateName = key($rates);

						if ($rate = array_shift($rates))
						{
							conversion_renderRate($rate, $rateTypes[$rateName]);
						}

						?>
						<div class="stat-item-block-more more-deployed">
							<?

							foreach ($rates as $name => $rate)
							{
								conversion_renderRate($rate, $rateTypes[$name]);
							}

							?>
							<a href="#" onclick="

								BX.toggleClass(this.parentNode, 'more-deployed');
								this.firstChild.firstChild.innerHTML = BX.hasClass(this.parentNode, 'more-deployed')
									? '<?=Loc::getMessage('CONVERSION_SUMMARY_LESS')?>'
									: '<?=Loc::getMessage('CONVERSION_SUMMARY_MORE')?>';
								return false;

								"><span><span><?=Loc::getMessage('CONVERSION_SUMMARY_LESS')?></span></span></a>
						</div>
					</div>

					<div class="stat-description">&nbsp;</div>

					<?

					// Other Splits ////////////////////////////////////////////////////////////////////////////////////

					$index = 0;

					foreach ($splitRates as $splitKey => $rates)
					{
						$index++;

						$split = $splits[$splitKey];

						$rateName = key($rates);

						if ($rate = array_shift($rates))
						{
							$denominator = $rate['DENOMINATOR'];
						}
						else
						{
							$denominator = 0;
						}

						?>
						<div class="stat-item-container">
							<?

							if ($index > 1)
							{
								?><span class="stat-item-plus" style="background: <?=$split['BG_COLOR']?>"></span><?
							}

							?>
							<div class="stat-item-title" style="background: <?=$split['BG_COLOR']?>">
								<?

								if ($split['SPLIT_BY'])
								{
									echo '<a href="conversion_detailed.php?'
										.http_build_query(array_merge($filter, array('split' => $split['SPLIT_BY'], $split['NAME'] => '')))
										.'">'
										.$split['TITLE']
										.'</a>';
								}
								else
								{
									echo $split['TITLE'];
								}

								?>
								<span class="stat-item-title-traffic">
									<span><?=Loc::getMessage('CONVERSION_SUMMARY_TRAFFIC')?>:</span>
									<?=$denominator?>
									<span>|</span>
									<?=$totalTopDenominator ? number_format($denominator / $totalTopDenominator * 100) : 0?>%
								</span>
							</div>
							<?

							if ($rate)
							{
								conversion_renderRate($rate, $rateTypes[$rateName]);
							}

							?>
							<div class="stat-item-block-more">
								<?

								foreach ($rates as $name => $rate)
								{
									conversion_renderRate($rate, $rateTypes[$name]);
								}

								?>
								<a href="#" onclick="

									BX.toggleClass(this.parentNode, 'more-deployed');
									this.firstChild.firstChild.innerHTML = BX.hasClass(this.parentNode, 'more-deployed')
										? '<?=Loc::getMessage('CONVERSION_SUMMARY_LESS')?>'
										: '<?=Loc::getMessage('CONVERSION_SUMMARY_MORE')?>';
									return false;

									"><span><span><?=Loc::getMessage('CONVERSION_SUMMARY_MORE')?></span></span></a>
							</div>
						</div>
						<?
					}

					?>
				</div>
			</div>
			<div class="adm-detail-content-btns-wrap">
				<div class="adm-detail-content-btns adm-detail-content-btns-empty"></div>
			</div>
		</div>


	</div>
<?

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
