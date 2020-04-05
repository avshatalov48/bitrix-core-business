<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Conversion\Utils;
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

// RATES

if (! $rateTypes = RateManager::getTypes(array('MODULE' => 'sale')))
	die ('No rates available!');

$rateName = $_GET['rate'];

if (! $rateType = $rateTypes[$rateName])
{
	list ($rateName, $rateType) = each($rateTypes);
}

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

if (! $sites)
	die ('No sites available!');

$site = $_GET['site'] ?: $userOptions['site'];

if (! $siteName = $sites[$site])
{
	list ($site, $siteName) = each($sites);
}

// FILTER

$filter = array(
	'from' => $from->toString(),
	'to'   => $to->toString(),
	'site' => $site,
);

CUserOptions::SetOption('conversion', 'filter', array_merge($userOptions, $filter));

$filter['lang'] = LANGUAGE_ID;

// CONTEXT

GeneratorContext::generateInitialData($from);

$context = new ReportContext();

$context->setAttribute('conversion_site', $site);

$rates = $context->getRatesDeprecated($rateTypes, array(
	'>=DAY' => $from,
	'<=DAY' => $to,
));

if ($topRate = reset($rates))
{
	$traffic    = $topRate['DENOMINATOR'];
	$quantity   = $topRate['NUMERATOR'];
	$gross      = $topRate['SUM'];
	$conversion = $topRate['RATE'] * 100;
}
else
{
	$quantity   = 0;
	$traffic    = 0;
	$gross      = 0;
	$conversion = 0;
}

$averageBill   = $quantity ? ($gross / $quantity) : 0; // with no margin!!!
$clickPrice    = 0;
$advertExpense = 0;
$otherExpense  = 0;
$expense       = $advertExpense + $otherExpense;
$profit        = $gross - $expense;
$margin        = 0;
$cost          = round($gross - (($gross / (100 + $margin)) * $margin));

//// get advertExpense & clickPrice
//
//if (Loader::includeModule('seo'))
//{
//	$advertExpense = 0;
//
//	$result = Bitrix\Seo\Adv\YandexStatTable::getList(array(
//		'select' => array('SUM', 'CURRENCY'),
//		'filter' => array(
//			// TODO site id
//			'>=DATE_DAY' => $from,
//			'<=DATE_DAY' => $to,
//		),
//	));
//
//	while ($row = $result->fetch())
//	{
//		if ($currency = $row['CURRENCY'])
//		{
//			$advertExpense += Utils::convertToBaseCurrency($row['SUM'], $currency);
//		}
//	}
//}

// VIEW

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/conversion/admin/helpers/scale.php');

$APPLICATION->SetTitle(Loc::getMessage('CONVERSION_CALC_TITLE'));

CJSCore::Init(array('amcharts', 'amcharts_funnel'));

Bitrix\Conversion\AdminHelpers\renderFilter($filter);

?>
	<div class="adm-detail-block">
		<div class="adm-detail-content-wrap">
			<div class="adm-detail-content">
				<div class="adm-detail-title"><?=Loc::getMessage('CONVERSION_CALC_TITLE2')?></div>
				<div class="adm-detail-content-item-block">
					<div class="adm-profit-block">
						<?

						$menuItems = array();

						foreach ($sites as $id => $name)
						{
							$menuItems[$name] = array_merge($filter, array('site' => $id));
						}

						Bitrix\Conversion\AdminHelpers\renderScale(array(
							'SITE_NAME'  => $siteName,
							'SITE_MENU'  => $menuItems,
							'CONVERSION' => $conversion,
							'SCALE'      => $rateType['SCALE'],
						));

						?>
					</div>
					<div class="adm-profit-block">
						<div class="adm-profit-title"><?=Loc::getMessage('CONVERSION_CALC_INDICATORS')?></div>
						<div class="adm-profit-indicators-wrap" id="top-blocks-wrapper">
							<div class="adm-profit-indicators-item">
								<div class="adm-profit-indicators-block adm-profit-indicators-blue">
									<div class="adm-profit-indicators-title"><?=Loc::getMessage('CONVERSION_CALC_GROSS_INCOME')?></div>
									<div class="adm-profit-indicators-cont">
										<span class="adm-profit-indicators-alignment"></span>
										<span id="conversion-calc-topGross" class="adm-profit-indicators-num"></span>
									</div>
								</div>
							</div>
							<div class="adm-profit-indicators-item">
								<div class="adm-profit-indicators-block adm-profit-indicators-green">
									<div class="adm-profit-indicators-title"><?=Loc::getMessage('CONVERSION_CALC_ADVERT_BUDGET')?></div>
									<div class="adm-profit-indicators-cont">
										<span class="adm-profit-indicators-alignment"></span>
										<span id="conversion-calc-topAdvertExpense" class="adm-profit-indicators-num"></span>
									</div>
									<div class="adm-profit-indicators-title"><?=Loc::getMessage('CONVERSION_CALC_OTHER_EXPENSES')?></div>
									<div class="adm-profit-indicators-cont">
										<span class="adm-profit-indicators-alignment"></span>
										<span id="conversion-calc-topOtherExpense" class="adm-profit-indicators-num"></span>
									</div>
								</div>
							</div>
							<div class="adm-profit-indicators-item">
								<div class="adm-profit-indicators-block adm-profit-indicators-violet">
									<div class="adm-profit-indicators-title"><?=Loc::getMessage('CONVERSION_CALC_PROFIT')?></div>
									<div class="adm-profit-indicators-cont">
										<span class="adm-profit-indicators-alignment"></span>
										<span id="conversion-calc-topProfit" class="adm-profit-indicators-num"></span>
									</div>
								</div>
							</div>
							<div class="adm-profit-indicators-line"></div>
							<div class="adm-profit-indicators-marks-wrap adm-profit-indicators-mark-minus">
								<div class="adm-profit-indicators-marks-inner">
									<div class="adm-profit-indicators-mark"></div>
								</div>
							</div>
							<div class="adm-profit-indicators-marks-wrap adm-profit-indicators-mark-equal">
								<div class="adm-profit-indicators-marks-inner">
									<div class="adm-profit-indicators-mark"></div>
								</div>
							</div>
							<div class="adm-profit-indicators-block adm-profit-percent">
								<div class="adm-profit-indicators-title"><?=Loc::getMessage('CONVERSION_CALC_CONVERSION')?></div>
								<div class="adm-profit-indicators-cont">
									<span id="conversion-calc-topConversion"></span>
								</div>
							</div>
						</div>
					</div>

					<div class="adm-profit-block adm-profit-block-white">

						<div class="adm-profit-block-part">
							<div class="adm-profit-title adm-profit-title-funnel"><?=Loc::getMessage('CONVERSION_CALC_FUNNEL')?></div>
							<div class="adm-profit-funnel-block">
								<div id="bitrix-conversion-funnel" style="height:350px"></div>
								<div class="adm-profit-funnel-arrow">
									<div class="adm-profit-funnel-arrow-center"></div>
								</div>
							</div>
						</div>

						<div id="conversion-calc" class="adm-profit-block-part">
							<div class="adm-profit-title-wrap">
								<div class="adm-profit-title adm-profit-title-calc"><?=Loc::getMessage('CONVERSION_CALC_PROFITABILITY')?></div>
								<div id="conversion-calc-forecast" class="adm-profit-calc-toggle">
									<span class="adm-profit-calc-toggle-text"><?=Loc::getMessage('CONVERSION_CALC_FORECAST')?></span>
									<span class="adm-profit-calc-toggle-btn"></span>
								</div>
							</div>
							<div class="adm-profit-calc-block">
								<div class="adm-profit-calc-row">
									<div class="adm-profit-calc-cel">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_ADVERT_BUDGET')?></div>
										<input id="conversion-calc-advertExpense" type="text" readonly class="adm-profit-calc-inp">
									</div>
									<div class="adm-profit-calc-cel">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_CLICK_PRICE')?></div>
										<input id="conversion-calc-clickPrice" type="text" readonly class="adm-profit-calc-inp">
									</div>
									<div class="adm-profit-calc-cel adm-profit-calc-cel-yellow">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_DENOMINATOR')?></div>
										<input id="conversion-calc-traffic" type="text" readonly tabindex="-1">
									</div>
								</div>
								<div class="adm-profit-calc-row">
									<div class="adm-profit-calc-cel">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_DENOMINATOR')?></div>
										<input id="conversion-calc-traffic2" type="text" readonly tabindex="-1">
									</div>
									<div class="adm-profit-calc-cel">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_NUMERATOR')?></div>
										<input id="conversion-calc-quantity" type="text" readonly tabindex="-1">
									</div>
									<div class="adm-profit-calc-cel adm-profit-calc-cel-yellow">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_CONVERSION')?> %</div>
										<input id="conversion-calc-conversion" type="text" readonly class="adm-profit-calc-inp">
									</div>
								</div>
								<div class="adm-profit-calc-row">
									<div class="adm-profit-calc-cel">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_GROSS_INCOME')?></div>
										<input id="conversion-calc-gross" type="text" readonly tabindex="-1">
									</div>
									<div class="adm-profit-calc-cel">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_NUMERATOR')?></div>
										<input id="conversion-calc-quantity2" type="text" readonly tabindex="-1">
									</div>
									<div class="adm-profit-calc-cel adm-profit-calc-cel-red">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_AVERAGE_BILL')?></div>
										<input id="conversion-calc-averageBill" type="text" readonly class="adm-profit-calc-inp">
									</div>
								</div>
								<div class="adm-profit-calc-row">
									<div class="adm-profit-calc-cel">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_ADVERT_BUDGET')?></div>
										<input id="conversion-calc-advertExpense2" type="text" readonly tabindex="-1">
									</div>
									<div class="adm-profit-calc-cel">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_NUMERATOR')?></div>
										<input id="conversion-calc-quantity3" type="text" readonly tabindex="-1">
									</div>
									<div class="adm-profit-calc-cel adm-profit-calc-cel-red">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_CPA')?></div>
										<input id="conversion-calc-cpa" type="text" readonly tabindex="-1">
									</div>
								</div>
								<div class="adm-profit-calc-row">
									<div class="adm-profit-calc-cel">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_ADVERT_BUDGET')?></div>
										<input id="conversion-calc-advertExpense3" type="text" readonly tabindex="-1">
									</div>
									<div class="adm-profit-calc-cel">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_MARGIN')?>, %</div>
										<input id="conversion-calc-margin" type="text" readonly class="adm-profit-calc-inp">
									</div>
									<div class="adm-profit-calc-cel adm-profit-calc-cel-violet">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_ROI')?></div>
										<input id="conversion-calc-roi" type="text" readonly tabindex="-1">
									</div>
								</div>
								<div class="adm-profit-calc-row">
									<div class="adm-profit-calc-cel adm-profit-calc-footer">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_OTHER_EXPENSES')?></div>
										<input id="conversion-calc-otherExpenses" type="text" readonly class="adm-profit-calc-inp">
									</div>
									<div class="adm-profit-calc-cel adm-profit-calc-footer">
										<div class="adm-profit-calc-cel-header"><?=Loc::getMessage('CONVERSION_CALC_COST')?></div>
										<input id="conversion-calc-cost" type="text" readonly class="adm-profit-calc-inp">
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
			<div class="adm-detail-content-btns-wrap">
				<div class="adm-detail-content-btns adm-detail-content-btns-empty"></div>
			</div>
		</div>
	</div>
	<script>

//		BX.ready(function ()
		AmCharts.ready(function ()
		{
			'use strict';

			<?

				$funnelData = array();
				$initialGrosses = array();

				foreach (array_reverse($rates) as $name => $rate)
				{
					$sum = round($rate['SUM']);

					$funnelData []= array(
						'title' => $rateTypes[$name]['NAME'],
						'value' => $sum,
					);

					$initialGrosses []= $sum;
				}

			?>

			var initialGross   = <?=$gross?>,
				initialGrosses = <?=Json::encode($initialGrosses)?>,
				funnelData     = <?=Json::encode($funnelData)?>,
				funnel         = new AmCharts.AmFunnelChart();

			funnel.dataProvider       = funnelData;
			funnel.theme              = 'none';
			funnel.labelText          = ' ';
			funnel.balloonText        = '[[title]]: <span style="white-space: nowrap; ">[[value]] <?=CUtil::JSEscape(Utils::getBaseCurrencyUnit())?></span>';
			funnel.titleField         = 'title';
			funnel.valueField         = 'value';
			funnel.thousandsSeparator = ' ';
			funnel.depth3D            = 160;
			funnel.angle              = 23;
			funnel.outlineAlpha       = 2;
			funnel.outlineColor       = '#FFFFFF';
			funnel.outlineThickness   = 2;
			funnel.marginRight        = 50;
			funnel.marginLeft         = 50;
			funnel.balloon            = {'fixedPosition': true};
			funnel.write('bitrix-conversion-funnel');

			var scaleShiftElement       = BX('conversion-scale-shift'),
				scaleConversionElement  = BX('conversion-scale-conversion'),
				scale                   = <?=Json::encode($rateType['SCALE'])?>,
				//
				topGrossElement         = BX('conversion-calc-topGross'),
				topOtherExpenseElement  = BX('conversion-calc-topOtherExpense'),
				topAdvertExpenseElement = BX('conversion-calc-topAdvertExpense'),
				topProfitElement        = BX('conversion-calc-topProfit'),
				topConversionElement    = BX('conversion-calc-topConversion'),
				//
				calcElement             = BX('conversion-calc'),
				forecastElement         = BX('conversion-calc-forecast'),
				forecastMode            = false,
				// 1
				advertExpenseElement    = BX('conversion-calc-advertExpense'),
				clickPriceElement       = BX('conversion-calc-clickPrice'),
				trafficElement          = BX('conversion-calc-traffic'),
				// 2
				traffic2Element         = BX('conversion-calc-traffic2'),
				quantityElement         = BX('conversion-calc-quantity'),
				conversionElement       = BX('conversion-calc-conversion'),
				// 3
				grossElement            = BX('conversion-calc-gross'),
				quantity2Element        = BX('conversion-calc-quantity2'),
				averageBillElement      = BX('conversion-calc-averageBill'),
				// 4
				advertExpense2Element   = BX('conversion-calc-advertExpense2'),
				quantity3Element        = BX('conversion-calc-quantity3'),
				cpaElement              = BX('conversion-calc-cpa'),
				// 5
				advertExpense3Element   = BX('conversion-calc-advertExpense3'),
				marginElement           = BX('conversion-calc-margin'),
				roiElement              = BX('conversion-calc-roi'),
				// 6
				otherExpensesElement    = BX('conversion-calc-otherExpenses'),
				costElement             = BX('conversion-calc-cost'),

				getShift = function (conversion, scale)
				{
					var shift = 100, min = 0, max, i = 0, length = scale.length;

					for (; i < length; i++)
					{
						max = scale[i];

						if (conversion == max)
						{
							shift = (i + 1) * 20;
							break;
						}
						else if (conversion < max)
						{
							shift = (i * 20) + ((conversion - min) * 20 / (max - min)); // TODO simplify
							break;
						}

						min = max;
					}

					return shift;
				},

				resetValues = function ()
				{
					// 1
					advertExpenseElement.value = advertExpense2Element.value = advertExpense3Element.value = topAdvertExpenseElement.innerHTML = <?=$advertExpense?>;
					clickPriceElement.value = <?=$clickPrice?>;
					trafficElement.value = traffic2Element.value = <?=$traffic?>;
					// 2
					quantityElement.value = quantity2Element.value = quantity3Element.value = <?=$quantity?>;
					conversionElement.value = <?=round($conversion, 2)?>;
					// 3
					grossElement.value = topGrossElement.innerHTML = <?=round($gross)?>;
					averageBillElement.value = Math.ceil(<?=$averageBill?>);
					// 4
					cpaElement.value = <?=$quantity ? round($advertExpense / $quantity) : 0?>;
					// 5
					marginElement.value = <?=$margin?>;
					roiElement.value = <?=$expense ? round(($gross - $cost) / $expense * 100, 2) : 0?>;
					// 6
					otherExpensesElement.value = topOtherExpenseElement.innerHTML = <?=$otherExpense?>;
					costElement.value          = <?=$cost?>;
					topProfitElement.innerHTML = <?=round($profit)?>;
					// scale
					scaleConversionElement.innerHTML = topConversionElement.innerHTML = '<?=number_format($conversion, 2)?>%';
					scaleShiftElement.style.left = getShift(<?=$conversion?>, scale) + '%';

					calcFixSize1.increase();
					calcFixSize1.decrease();
					calcFixSize2.increase();
					calcFixSize2.decrease();

					// funnel
					var length = funnelData.length, i = 0;
					for (; i < length; i++)
					{
						funnelData[i].value = initialGrosses[i];
					}
					funnel.validateData();
				};

			var costRecount = true,
				averageBillNoMargin = <?=$averageBill?>;

			var calcFixSize1 = new BX.FixFontSize({
				objList: [
					{
						node: topGrossElement,
						maxFontSize: 45,
						smallestValue: true
					},
					{
						node: topProfitElement,
						maxFontSize: 45,
						smallestValue: true
					}
				],
				onresize: true
			});

			var calcFixSize2 = new BX.FixFontSize({
				objList: [
					{
						node: topOtherExpenseElement,
						maxFontSize: 29,
						smallestValue: true
					},
					{
						node: topAdvertExpenseElement,
						maxFontSize: 29,
						smallestValue: true
					}
				],
				onresize: true
			});

			BX.bind(calcElement, 'keyup', function ()
			{
				var advertExpense = advertExpense2Element.value = advertExpense3Element.value = topAdvertExpenseElement.innerHTML = parseFloat(advertExpenseElement.value) || 0,
					clickPrice    = parseFloat(clickPriceElement.value) || 0,
					traffic       = advertExpense && clickPrice
						? trafficElement.value = traffic2Element.value = clickPrice ? Math.ceil(advertExpense / clickPrice) : 0
						: parseFloat(trafficElement.value) || 0,
					conversion    = parseFloat(conversionElement.value) || 0,
					quantity      = quantityElement.value = quantity2Element.value = quantity3Element.value = Math.round(conversion * traffic / 100),
					// 3
					averageBill   = parseFloat(averageBillElement.value) || 0,
					gross         = grossElement.value = topGrossElement.innerHTML = (averageBill * quantity).toFixed(),
					// 4
					cpa           = cpaElement.value = quantity ? Math.ceil(advertExpense / quantity) : 0,
					// 5
					margin        = parseFloat(marginElement.value) || 0,
					cost          = costRecount
						? costElement.value = Math.ceil(averageBillNoMargin * quantity)
//						? costElement.value = Math.ceil(gross - ((gross / (100 + margin)) * margin))
						: parseFloat(costElement.value) || 0,
					otherExpenses = topOtherExpenseElement.innerHTML = parseFloat(otherExpensesElement.value) || 0,
					expense       = advertExpense + otherExpenses,
					roi           = roiElement.value = expense ? Math.ceil((gross - cost) / expense * 100) : 0, //........
					profit        = topProfitElement.innerHTML = (gross - expense).toFixed();

				costRecount = true;

				calcFixSize1.increase();
				calcFixSize1.decrease();
				calcFixSize2.increase();
				calcFixSize2.decrease();

				// scale
				scaleConversionElement.innerHTML = topConversionElement.innerHTML = conversion.toFixed(2).substr(0, 5) + '%';
				scaleShiftElement.style.left = getShift(conversion, scale) + '%';

				// funnel
				var length = funnelData.length, i = 0, grossRate = initialGross ? gross / initialGross : 0, initial;

				for (; i < length; i++)
				{
					if (initial = initialGrosses[i])
					{
						funnelData[i].value = Math.ceil(initial * grossRate);
					}
					else
					{
						funnelData[i].value = Math.ceil(gross);
					}
				}

				funnel.validateData();
			});

			BX.bind(averageBillElement, 'keyup', function ()
			{
				var margin = parseFloat(marginElement.value) || 0,
					averageBill = parseFloat(averageBillElement.value) || 0;
				averageBillNoMargin = averageBill - ((averageBill / (100 + margin)) * margin);

			});

			BX.bind(marginElement, 'keyup', function ()
			{
				var margin = parseFloat(marginElement.value) || 0;
				averageBillElement.value = Math.ceil(averageBillNoMargin + (averageBillNoMargin * margin / 100));
			});

			BX.bind(costElement, 'keyup', function ()
			{
				var cost     = parseFloat(costElement.value) || 0,
					margin   = parseFloat(marginElement.value) || 0,
					quantity = parseFloat(quantityElement.value) || 0;

				averageBillNoMargin = quantity ? cost / quantity : 0;
				averageBillElement.value = Math.ceil(averageBillNoMargin + (averageBillNoMargin * margin / 100));

				costRecount = false;
			});

			// adm-profit-calc-cont
			// adm-profit-calc-cont adm-profit-calc-cont-inp
			// input adm-profit-calc-inp

			BX.bind(calcElement, 'keypress', function (e)
			{
				var ie, code, target;

				if (e)
				{
					code = e.which;
					target = e.target;
				}
				else
				{
					ie = window.event;
					code = ie.keyCode;
					target = ie.srcElement;
				}

				if (! ((code < 32)
					|| (code == 46 && target.value.indexOf('.') === -1)
					|| (code >= 48 && code <= 57)))
				{
					if (e)
					{
						e.stopPropagation();
						e.preventDefault();
					}
					else
					{
						ie.cancelBubble = true;
						ie.returnValue = false;
					}
				}
			});

			resetValues();

			BX.bind(forecastElement, 'click', function()
			{
				forecastMode && resetValues();

				BX.toggleClass(calcElement, 'adm-profit-block-part-active');
				BX.toggleClass(forecastElement, 'adm-profit-calc-toggle-active');

				forecastMode = !
					(     advertExpenseElement.readOnly
						= clickPriceElement.readOnly
						= conversionElement.readOnly
						= averageBillElement.readOnly
						= marginElement.readOnly
						= otherExpensesElement.readOnly
						= costElement.readOnly
						= forecastMode
					);
			});

		});

	</script>
<?

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
