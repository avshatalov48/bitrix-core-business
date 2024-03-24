<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/* @var CMain $APPLICATION */

$APPLICATION->SetAdditionalCSS('/bitrix/gadgets/bitrix/bitrixcloud_monitoring/styles.css');
$converter = CBXPunycode::GetConverter();
$saleIncluded = CModule::IncludeModule('sale') && CModule::IncludeModule('currency');

$intervalLang = [
	'sale' => [
		7 => GetMessage('GD_BITRIXCLOUD_MONITOR_MESS_ALERT1_WEEK'),
		30 => GetMessage('GD_BITRIXCLOUD_MONITOR_MESS_ALERT1_MONTH'),
		90 => GetMessage('GD_BITRIXCLOUD_MONITOR_MESS_ALERT1_QUARTER'),
		365 => GetMessage('GD_BITRIXCLOUD_MONITOR_MESS_ALERT1_YEAR'),
	],
	'uptime' => [
		7 => GetMessage('GD_BITRIXCLOUD_MONITOR_MESS_ALERT2_WEEK'),
		30 => GetMessage('GD_BITRIXCLOUD_MONITOR_MESS_ALERT2_MONTH'),
		90 => GetMessage('GD_BITRIXCLOUD_MONITOR_MESS_ALERT2_QUARTER'),
		365 => GetMessage('GD_BITRIXCLOUD_MONITOR_MESS_ALERT2_YEAR'),
	],
];

$uptime = '';
$testCount = 0;
/** @var CBitrixCloudMonitoringTest $testAlert */
$testAlert = null;
$testDomain = '';
$monitoring = null;
$bAlert = false;
if (CModule::IncludeModule('bitrixcloud'))
{
	$monitoring = CBitrixCloudMonitoring::getInstance();
	$monitoringResults = $monitoring->getMonitoringResults();
	if (!is_string($monitoringResults))
	{
		if ($monitoringResults->getStatus() === CBitrixCloudMonitoringResult::RED_LAMP)
		{
			$bAlert = true;

			foreach ($monitoringResults as $domainName => $domainResult)
			{
				foreach ($domainResult as $testId => $testResult)
				{
					if ($testResult->getStatus() === CBitrixCloudMonitoringResult::RED_LAMP)
					{
						$testCount++;
						$testAlert = $testResult;
						$testDomain = $domainName;
					}
				}
			}

			$uptime = $monitoring->getWorstUptime('test_http_response_time');
		}
	}
}

$sum = 0;
$sumHtml = '';
$alertIntervalText = '';
$uptimeRate = 1;

if ($bAlert)
{
	if ($uptime !== '')
	{
		$uptime = explode('/', $uptime);
		if ($uptime[0] > 0 && $uptime[1] > 0)
		{
			$uptimeRate = $uptime[0] / $uptime[1];
		}
	}

	if ($uptimeRate < 1 && $saleIncluded)
	{
		$base = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
		$r = CSaleOrder::GetList([], [
			'>=DATE_INSERT' => ConvertTimeStamp(time() - $monitoring->getInterval() * 24 * 3400, 'SHORT'),
		], ['LID', 'CURRENCY', 'SUM' => 'PRICE']);

		while ($a = $r->Fetch())
		{
			$sum += CCurrencyRates::ConvertCurrency($a['PRICE'], $a['CURRENCY'], $base);
		}

		$sum *= (1 - $uptimeRate);

		if ($sum <= 0.0)
		{
			$sumHtml = number_format((1 - $uptimeRate) * 100, 2, '.', ' ') . '%';
			$alertIntervalText = $intervalLang['uptime'][$monitoring->getInterval()];
		}
		else
		{
			$sumHtml = CCurrencyLang::CurrencyFormat($sum, $base, true);
			$alertIntervalText = $intervalLang['sale'][$monitoring->getInterval()];
		}
	}
	elseif ($testCount === 1 && HasMessage('GD_BITRIXCLOUD_MONITOR_' . mb_strtoupper($testAlert->getName())))
	{
		$uptimeRate = 1;
		$resultText = FormatDate('ddiff', time(), $testAlert->getResult());
		$sumHtml = GetMessage('GD_BITRIXCLOUD_MONITOR_' . mb_strtoupper($testAlert->getName()), [
			'#DOMAIN#' => $converter->Decode($testDomain),
			'#DAYS#' => $resultText,
		]);
	}
	elseif ($uptimeRate < 1)
	{
		$sumHtml = number_format((1 - $uptimeRate) * 100, 2, '.', ' ') . '%';
		$alertIntervalText = $intervalLang['uptime'][$monitoring->getInterval()];
	}
	else
	{
		$sumHtml = GetMessage('GD_BITRIXCLOUD_MONITOR_PROBLEMS', [
			'#COUNT#' => $testCount,
		]);
	}
}
?>
<div class="bx-gadgets-content-layout-inspector">
	<div class="bx-gadgets-title"><?php echo GetMessage('GD_BITRIXCLOUD_MONITOR')?></div>
	<div class="bx-gadget-bottom-cont bx-gadget-bottom-button-cont bx-gadget-mark-cont">
<?php
	if ($uptimeRate < 1)
	{
?>
		<a class="bx-gadget-button" href="/bitrix/admin/bitrixcloud_monitoring_admin.php?lang=<?php echo LANGUAGE_ID?>&amp;referer=gadget">
			<div class="bx-gadget-button-lamp"></div>
			<div class="bx-gadget-button-text"><?php echo GetMessage('GD_BITRIXCLOUD_MONITOR_BTN_ALERT')?></div>
		</a>
		<div class="bx-gadget-mark"><?php echo $sumHtml?></div>
		<div class="bx-gadget-desc bx-gadget-desc-wmark"><?php echo $alertIntervalText;?></div>
<?php
	}
	elseif ($bAlert)
	{
?>
		<a class="bx-gadget-button" href="/bitrix/admin/bitrixcloud_monitoring_admin.php?lang=<?php echo LANGUAGE_ID?>&amp;referer=gadget">
			<div class="bx-gadget-button-lamp"></div>
			<div class="bx-gadget-button-text"><?php echo GetMessage('GD_BITRIXCLOUD_MONITOR_BTN_OK')?></div>
		</a>
		<div class="bx-gadget-desc bx-gadget-desc-wmark"><?php echo $sumHtml;?></div>
<?php
	}
	else
	{
		?>
		<a class="bx-gadget-button" href="/bitrix/admin/bitrixcloud_monitoring_admin.php?lang=<?php echo LANGUAGE_ID?>&amp;referer=gadget">
			<div class="bx-gadget-button-lamp"></div>
			<div class="bx-gadget-button-text"><?php echo GetMessage('GD_BITRIXCLOUD_MONITOR_BTN_OK')?></div>
		</a>
		<div class="bx-gadget-mark"><?php echo GetMessage('GD_BITRIXCLOUD_MONITOR_MESS_OK')?></div>
	<?php
	}
?>
	</div>
</div>
<div class="bx-gadget-shield"></div>
