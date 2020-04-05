<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

/** @var CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */


?>

<script type="text/javascript">
	BX.ready(function () {
		var params = <?=Json::encode(array(
			'mailingId' => $arParams['MAILING_ID'],
			'chainId' => $arParams['CHAIN_ID'],
			'postingId' => $arParams['POSTING_ID'],
			'posting' => $arResult['DATA']['posting'],
			'chainList' => $arResult['CHAIN_LIST'],
			'clickList' => $arResult['DATA']['clickList'],
			'actionUrl' => $arResult['ACTION_URL'],
			'nameTemplate' => $arParams['NAME_TEMPLATE'],
			'pathToUserProfile' => $arResult['PATH_TO_USER_PROFILE'],
			'mess' => array(
				'allPostings' => Loc::getMessage('SENDER_LETTER_STAT_STATS_POSTINGS_ALL'),
				'readByTimeBalloon' => Loc::getMessage('SENDER_LETTER_STAT_STATS_READ_BY_TIME_CHART_BALLOON'),
			)
		))?>;

		params.context = BX('BX_SENDER_STATISTICS');
		BX.Sender.PostingsStats.load(params);
	});
</script>

<div id="BX_SENDER_STATISTICS" class="bx-sender-stat-wrapper">

	<div class="bx-sender-stat">
		<p class="bx-sender-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_ANALYTICS')?></p>
		<div data-bx-block="Counters" class="bx-sender-block">
			<div class="bx-sender-block-row">
				<div class="bx-sender-block-col-2 bx-sender-block-col-margin">
					<div class="bx-sender-tittle-statistic">
						<span class="bx-sender-title-statistic-text bx-sender-title-statistic-text-bold">
							<?=Loc::getMessage('SENDER_LETTER_STAT_STATS_CAMPAIGN')?>:
						</span>
						<span id="sender_stat_filter_chain_id" class="">
							<?=htmlspecialcharsbx($arResult['ROW']['NAME'])?>
						</span>
					</div>


					<div style="height: 40px;">
					</div>

					<div class="bx-sender-mailfilter-result">
						<div class="bx-sender-mailfilter-result-item bx-sender-mailfilter-2-items">
							<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_COUNTER_READ')?></p>
							<a class=""
								href="<?=htmlspecialcharsbx($arResult['URLS']['READ'])?>"
								onclick="BX.Sender.Page.open('<?= htmlspecialcharsbx($arResult['URLS']['READ'])?>'); return false;"
							>
								<span data-bx-point="counters/READ/VALUE_DISPLAY" class="bx-sender-mailfilter-result-total">
									<?=htmlspecialcharsbx($arResult['DATA']['CNT']['READ'])?>
								</span>
							</a>
						</div>
						<div class="bx-sender-mailfilter-result-item bx-sender-mailfilter-2-items">
							<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_COUNTER_CLICK')?></p>
							<a class=""
								href="<?=htmlspecialcharsbx($arResult['URLS']['CLICK'])?>"
								onclick="BX.Sender.Page.open('<?= htmlspecialcharsbx($arResult['URLS']['CLICK'])?>'); return false;"
							>
								<span data-bx-point="counters/CLICK/VALUE_DISPLAY" class="bx-sender-mailfilter-result-total">
									<?=htmlspecialcharsbx($arResult['DATA']['CNT']['CLICK'])?>
								</span>
							</a>
						</div>
					</div>
				</div>

				<div class="bx-sender-block-col-2">
					<div class="bx-sender-tittle-statistic">
						<span class="bx-sender-title-statistic-text">
							<?=Loc::getMessage('SENDER_LETTER_STAT_STATS_COUNTER_SEND_ALL')?>:
							<a class=""
								href="<?=htmlspecialcharsbx($arResult['URLS']['START'])?>"
								onclick="BX.Sender.Page.open('<?= htmlspecialcharsbx($arResult['URLS']['SENT_SUCCESS'])?>'); return false;"
							>
								<span class="bx-sender-title-statistic-number-bold">
									<?=htmlspecialcharsbx($arResult['DATA']['CNT']['START'])?>
								</span>
							</a>
						</span>
					</div>
					<div class="bx-sender-tittle-statistic">
						<span class="bx-sender-title-statistic-text">
							<?=Loc::getMessage('SENDER_LETTER_STAT_STATS_COUNTER_GOAL')?>:
							<span class="bx-sender-title-statistic-number-bold">
								<?=htmlspecialcharsbx($arResult['DATA']['CNT']['GOAL'])?>
							</span>
						</span>
					</div>

					<div class="bx-sender-mailfilter-result">
						<div class="bx-sender-mailfilter-result-item bx-sender-mailfilter-2-items">
							<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_COUNTER_SEND_ERROR')?></p>
							<a class=""
								href="<?=htmlspecialcharsbx($arResult['URLS']['SENT_ERROR'])?>"
								onclick="BX.Sender.Page.open('<?= htmlspecialcharsbx($arResult['URLS']['SENT_ERROR'])?>'); return false;"
							>
								<span data-bx-point="counters/SEND_ERROR/VALUE_DISPLAY" class="bx-sender-mailfilter-result-total">
									<?=htmlspecialcharsbx($arResult['DATA']['CNT']['SEND_ERROR'])?>
								</span>
							</a>
						</div>
						<div class="bx-sender-mailfilter-result-item bx-sender-mailfilter-2-items">
							<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_COUNTER_UNSUB')?></p>
							<a class=""
								href="<?=htmlspecialcharsbx($arResult['URLS']['UNSUB'])?>"
								onclick="BX.Sender.Page.open('<?= htmlspecialcharsbx($arResult['URLS']['UNSUB'])?>'); return false;"
							>
								<span data-bx-point="counters/UNSUB/VALUE_DISPLAY" class="bx-sender-mailfilter-result-total">
									<?=htmlspecialcharsbx($arResult['DATA']['CNT']['UNSUB'])?>
								</span>
							</a>
						</div>
					</div>
				</div>
			</div>

			<div>
				<p class="bx-sender-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_GOAL_AFTER_LETTER')?></p>
				<div class="bx-sender-block">

					<div id="chartdiv" class="sender-stat-reiterate-graph" style="height: 400px;"></div>
					<script type="text/javascript">
						BX.ready(function(){
							var chart = AmCharts.makeChart("chartdiv", {
								"theme": "dark",
								"type": "serial",
								"pathToImages": "/bitrix/js/main/amcharts/3.3/images/",
								"dataProvider": <?=CUtil::PhpToJSObject($arResult['DATA']['CHAIN'])?>,
								"valueAxes": [{
									"axisAlpha": 0,
									"gridAlpha": 0.1
								}],
								"startDuration": 1,
								"graphs": [{
									"balloonText": "<?=Loc::getMessage("SENDER_LETTER_STAT_STATS_NAME")?>: [[SUBJECT]]<br/><?=Loc::getMessage("SENDER_LETTER_STAT_STATS_GOAL_START")?>: [[CNT_START]]<br/><?=Loc::getMessage("SENDER_LETTER_STAT_STATS_GOAL_END")?>: [[CNT_GOAL]]",
									"colorField": "color",
									"fillAlphas": 0.8,
									"lineAlpha": 0,
									"openField": "GOAL_START",
									"type": "column",
									"valueField": "GOAL_END"
								}],
								"rotate": true,
								"columnWidth": 1,
								"categoryField": "NAME",
								"categoryAxis": {
									"gridPosition": "start",
									"axisAlpha": 0,
									"gridAlpha": 0.1,
									"position": "left"
								},
								"export": {
									"enabled": true
								}
							});
						});

					</script>
				</div>
			</div>

		</div>
	</div>

	<?
	$APPLICATION->IncludeComponent(
		"bitrix:sender.ui.button.panel",
		"",
		array(
			'CLOSE' => array(
				'URL' => $arParams['PATH_TO_LIST'],
				'CAPTION' => Loc::getMessage('SENDER_UI_BUTTON_PANEL_CLOSE')
			),
		),
		false
	);
	?>

	<script type="text/javascript">
		BX.ready(function () {
			BX.Sender.Letter.Stat.init();
		});
	</script>
</div>