<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

?>

<script>
	BX.ready(function () {
		var params = <?=Json::encode(array(
			'mailingId' => $arResult['MAILING_ID'],
			'chainId' => $arResult['CHAIN_ID'],
			'postingId' => $arResult['POSTING_ID'],
			'posting' => $arResult['DATA']['posting'],
			'chainList' => $arResult['CHAIN_LIST'] ?? [],
			'clickList' => $arResult['DATA']['clickList'] ?? [],
			'mess' => array(
				'allPostings' => Loc::getMessage('SENDER_MAILING_STATS_POSTINGS_ALL'),
				'readByTimeBalloon' => Loc::getMessage('SENDER_MAILING_STATS_READ_BY_TIME_CHART_BALLOON'),
			)
		))?>;

		params.context = BX('BX_SENDER_STATISTICS');
		BX.Sender.PostingsStats.load(params);
	});
</script>

<div id="BX_SENDER_STATISTICS" class="bx-sender-stat-wrapper">

	<div class="bx-sender-block-first">
		<p class="bx-sender-title"><?=Loc::getMessage('SENDER_MAILING_STATS_EFFICIENCY_TITLE')?></p>

		<div class="bx-gadget-speed-speedo-block">
			<div class="bx-gadget-speed-ruler">
				<span class="bx-gadget-speed-ruler-start">0%</span>
				<span class="bx-gadget-speed-ruler-end">30%</span>
			</div>
			<div class="bx-gadget-speed-graph">
					<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-veryslow">
						<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_MAILING_STATS_EFFICIENCY_LEVEL_1')?></span>
					</span>

				<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-slow">
						<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_MAILING_STATS_EFFICIENCY_LEVEL_2')?></span>
					</span>

				<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-notfast">
						<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_MAILING_STATS_EFFICIENCY_LEVEL_3')?></span>
					</span>

				<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-fast">
						<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_MAILING_STATS_EFFICIENCY_LEVEL_4')?></span>
					</span>

				<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-varyfast">
						<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_MAILING_STATS_EFFICIENCY_LEVEL_5')?></span>
					</span>

				<div class="bx-gadget-speed-pointer" id="site-speed-pointer" style="left: <?=($arResult['EFFICIENCY']['PERCENT_VALUE'] * 100)?>%;">
					<div class="bx-gadget-speed-value" id="site-speed-pointer-index">
						<?=htmlspecialcharsbx(($arResult['EFFICIENCY']['VALUE'] * 100))?>%
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="bx-sender-stat">

		<div data-bx-block="Counters" class="bx-sender-block">
			<div class="bx-sender-mailfilter">
				<div class="bx-sender-mailfilter-half">
					<div class="bx-sender-mailfilter-item"><?=Loc::getMessage('SENDER_MAILING_STATS_COUNTER_SEND_ALL')?>:</div>
					<div data-bx-point="counters/SEND_ALL/VALUE_DISPLAY" class="bx-sender-mailfilter-item bx-sender-mailfilter-item-total">
						<?=htmlspecialcharsbx($arResult['DATA']['counters']['SEND_ALL']['VALUE_DISPLAY'])?>
					</div>
				</div>
				<div class="bx-sender-mailfilter-half bx-sender-mailfilter-half-right">
					<span class="bx-sender-mailfilter-item-light"><?=Loc::getMessage('SENDER_MAILING_STATS_MAILING')?>:</span>
					<span class="bx-sender-mailfilter-item">
						<?=htmlspecialcharsbx($arResult['POSTING']['MAILING_NAME'])?>
					</span>
				</div>
			</div>

			<div class="bx-sender-block-row">
				<div class="bx-sender-block-col-2">
					<div class="bx-sender-tittle-statistic">
						<span class="bx-sender-title-statistic-text bx-sender-title-statistic-text-bold"><?=Loc::getMessage('SENDER_MAILING_STATS_POSTING')?>:</span>
						<a id="sender_stat_filter_chain_id" class="bx-sender-title-statistic-link">
							<?=htmlspecialcharsbx($arResult['POSTING']['TITLE'])?>
						</a>
					</div>

					<div class="bx-sender-graph">
						<div class="bx-sender-graph-info">
							<span class="bx-sender-graph-info-title"><?=Loc::getMessage('SENDER_MAILING_STATS_READ')?>:</span>
							<span data-bx-point="counters/READ/PERCENT_VALUE_DISPLAY" class="bx-sender-graph-info-param">
								<?=htmlspecialcharsbx($arResult['DATA']['counters']['READ']['PERCENT_VALUE_DISPLAY'])?>
							</span>
						</div>
						<div class="bx-sender-graph-scale">
							<div data-bx-point="counters/READ/PERCENT_VALUE:width" class="bx-sender-graph-scale-inner" style="width: <?=intval($arResult['DATA']['counters']['READ']['PERCENT_VALUE'] * 100)?>%;"></div>
						</div>
					</div>
					<div class="bx-sender-tittle-statistic">
						<span class="bx-sender-tittle-statistic-half-text"><?=Loc::getMessage('SENDER_MAILING_STATS_MAILING_AVG')?></span>
						<span class="bx-sender-tittle-statistic-line"></span>
						<div class="bx-sender-tittle-statistic-proc">
							<?=htmlspecialcharsbx($arResult['MAILING_COUNTERS']['READ']['PERCENT_VALUE_DISPLAY'])?>
						</div>
					</div>
				</div>

				<div class="bx-sender-block-col-2">
					<div class="bx-sender-tittle-statistic">
						<span class="bx-sender-title-statistic-text">
						<?=Loc::getMessage("SENDER_MAILING_STATS_MAILING_SENT_BY_AUTHOR", array(
							'%date%' => '<span data-bx-point="posting/dateSent" class="bx-sender-min-width-80">'
								. htmlspecialcharsbx($arResult['DATA']['posting']['dateSent'])
								. '</span>',
							'%name%' => '<a data-bx-point="posting/createdBy/url:href" href="' . htmlspecialcharsbx($arResult['DATA']['posting']['createdBy']['url']). '" class="bx-sender-title-statistic-link">'
								. '<span data-bx-point="posting/createdBy/name" class="bx-sender-min-width-80">'
								. htmlspecialcharsbx($arResult['DATA']['posting']['createdBy']['name'])
								. '</span>'
								. '</a>'
						))?>
						</span>
					</div>
					<div class="bx-sender-graph">
						<div class="bx-sender-graph-info">
							<span class="bx-sender-graph-info-title"><?=Loc::getMessage('SENDER_MAILING_STATS_CLICKED')?>:</span>
							<span data-bx-point="counters/CLICK/PERCENT_VALUE_DISPLAY" class="bx-sender-graph-info-param">
								<?=htmlspecialcharsbx($arResult['DATA']['counters']['CLICK']['PERCENT_VALUE_DISPLAY'])?>
							</span>
						</div>
						<div class="bx-sender-graph-scale">
							<div data-bx-point="counters/CLICK/PERCENT_VALUE:width" class="bx-sender-graph-scale-inner" style="width: <?=intval($arResult['DATA']['counters']['CLICK']['PERCENT_VALUE'] * 100)?>%;"></div>
						</div>
					</div>
					<div class="bx-sender-tittle-statistic">
						<span class="bx-sender-tittle-statistic-half-text"><?=Loc::getMessage('SENDER_MAILING_STATS_MAILING_AVG')?></span>
						<span class="bx-sender-tittle-statistic-line"></span>
						<div class="bx-sender-tittle-statistic-proc">
							<?=htmlspecialcharsbx($arResult['MAILING_COUNTERS']['CLICK']['PERCENT_VALUE_DISPLAY'])?>
						</div>
					</div>
				</div>
			</div>

			<div class="bx-sender-mailfilter-result">
				<div class="bx-sender-mailfilter-result-item bx-sender-mailfilter-4-items">
					<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_MAILING_STATS_COUNTER_READ')?></p>
					<span data-bx-point="counters/READ/VALUE_DISPLAY" class="bx-sender-mailfilter-result-total">
							<?=htmlspecialcharsbx($arResult['DATA']['counters']['READ']['VALUE_DISPLAY'])?>
						</span>
				</div>
				<div class="bx-sender-mailfilter-result-item bx-sender-mailfilter-4-items">
					<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_MAILING_STATS_COUNTER_CLICK')?></p>
					<span data-bx-point="counters/CLICK/VALUE_DISPLAY" class="bx-sender-mailfilter-result-total">
							<?=htmlspecialcharsbx($arResult['DATA']['counters']['CLICK']['VALUE_DISPLAY'])?>
						</span>
				</div>
				<div class="bx-sender-mailfilter-result-item bx-sender-mailfilter-4-items">
					<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_MAILING_STATS_COUNTER_SEND_ERROR')?></p>
					<span data-bx-point="counters/SEND_ERROR/VALUE_DISPLAY" class="bx-sender-mailfilter-result-total">
							<?=htmlspecialcharsbx($arResult['DATA']['counters']['SEND_ERROR']['VALUE_DISPLAY'])?>
						</span>
				</div>
				<div class="bx-sender-mailfilter-result-item bx-sender-mailfilter-4-items">
					<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_MAILING_STATS_COUNTER_UNSUB')?></p>
					<span data-bx-point="counters/UNSUB/VALUE_DISPLAY" class="bx-sender-mailfilter-result-total">
							<?=htmlspecialcharsbx($arResult['DATA']['counters']['UNSUB']['VALUE_DISPLAY'])?>
						</span>
				</div>
			</div>
		</div>

		<div class="bx-sender-block" data-bx-block="ClickMap">
			<p class="bx-sender-title"><?=Loc::getMessage('SENDER_MAILING_STATS_CLICK_MAP')?></p>
			<div data-bx-view-loader="" class="bx-sender-insert bx-sender-insert-loader">
				<div class="bx-faceid-tracker-user-loader">
					<div class="bx-faceid-tracker-user-loader-item">
						<div class="bx-faceid-tracker-loader">
							<svg class="bx-faceid-tracker-circular" viewBox="25 25 50 50">
								<circle class="bx-faceid-tracker-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
							</svg>
						</div>
					</div>
				</div>
			</div>
			<div data-bx-view-data="" class="bx-sender-click-map" style="display: none;">
				<iframe data-bx-click-map="" class="bx-sender-click-map-frame bx-sender-resizer" width="100%" height="400" src=""></iframe>
			</div>
		</div>

		<div class="bx-sender-block" data-bx-block="ReadByTime">
			<p class="bx-sender-title"><?=Loc::getMessage('SENDER_MAILING_STATS_READ_BY_TIME')?></p>
			<div data-bx-view-loader="" class="bx-sender-insert bx-sender-insert-loader">
				<div class="bx-faceid-tracker-user-loader">
					<div class="bx-faceid-tracker-user-loader-item">
						<div class="bx-faceid-tracker-loader">
							<svg class="bx-faceid-tracker-circular" viewBox="25 25 50 50">
								<circle class="bx-faceid-tracker-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
							</svg>
						</div>
					</div>
				</div>
			</div>
			<div data-bx-view-data="" style="width: 100%; height: 500px; display: none;" class="bx-sender-resizer"></div>
		</div>

	</div>
</div>
