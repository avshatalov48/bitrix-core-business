<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

/** @var CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

Extension::load('ui.hint');
$containerId = 'BX_SENDER_STATISTICS';
$containerId = htmlspecialcharsbx($containerId);
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
			'actionUrl' => $arResult['ACTION_URI'],
			'nameTemplate' => $arParams['NAME_TEMPLATE'],
			'pathToUserProfile' => $arResult['PATH_TO_USER_PROFILE'],
			'mess' => array(
				'allPostings' => Loc::getMessage('SENDER_LETTER_STAT_STATS_POSTINGS_ALL'),
				'readByTimeBalloon' => Loc::getMessage('SENDER_LETTER_STAT_STATS_READ_BY_TIME_CHART_BALLOON'),
			)
		), JSON_PARTIAL_OUTPUT_ON_ERROR)?>;

		params.context = BX('<?=$containerId?>');
		BX.Sender.PostingsStats.load(params);
	});
</script>

<div id="<?=$containerId?>" class="bx-sender-stat-wrapper">

	<div class="bx-sender-block-first">
		<p class="bx-sender-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_EFFICIENCY_TITLE')?></p>

		<div class="bx-gadget-speed-speedo-block">
			<div class="bx-gadget-speed-graph">
				<div class="bx-gadget-speed-ruler">
<!--					<span class="bx-gadget-speed-ruler-start">0%</span>-->
<!--					<span class="bx-gadget-speed-ruler-end">30%</span>-->
				</div>
				<div class="bx-gadget-speed-graph-box">
					<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-veryslow"></span>
					<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_EFFICIENCY_LEVEL_1')?></span>
				</div>

				<div class="bx-gadget-speed-graph-box">
					<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-slow"></span>
					<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_EFFICIENCY_LEVEL_2')?></span>
				</div>
				<div class="bx-gadget-speed-graph-box">
					<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-notfast"></span>
					<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_EFFICIENCY_LEVEL_3')?></span>
				</div>
				<div class="bx-gadget-speed-graph-box">
					<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-fast"></span>
					<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_EFFICIENCY_LEVEL_4')?></span>
				</div>
				<div class="bx-gadget-speed-graph-box">
					<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-varyfast"></span>
					<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_EFFICIENCY_LEVEL_5')?></span>
				</div>

				<div class="bx-gadget-speed-pointer" id="site-speed-pointer" style="left: <?=($arResult['EFFICIENCY']['PERCENT_VALUE'] * 100)?>%;">
					<div class="bx-gadget-speed-value" id="site-speed-pointer-index">
						<?=htmlspecialcharsbx(($arResult['EFFICIENCY']['VALUE'] * 100))?>
						<span class="bx-gadget-speed-value-percent">%</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="bx-sender-stat">
		<p class="bx-sender-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_ANALYTICS')?></p>
		<div data-bx-block="Counters" class="bx-sender-block">
<!--			<div class="bx-sender-mailfilter">-->
<!--				<div class="bx-sender-mailfilter-half">-->
<!--					<div class="bx-sender-mailfilter-item">--><?//=Loc::getMessage('SENDER_LETTER_STAT_STATS_COUNTER_SEND_ALL')?><!--:</div>-->
<!--					<div data-bx-point="counters/SEND_ALL/VALUE_DISPLAY" class="bx-sender-mailfilter-item bx-sender-mailfilter-item-total">-->
<!--						--><?//=htmlspecialcharsbx($arResult['DATA']['counters']['SEND_ALL']['VALUE_DISPLAY'])?>
<!--					</div>-->
<!--				</div>-->
<!--				<div class="bx-sender-mailfilter-half bx-sender-mailfilter-half-right">-->
<!--					<span class="bx-sender-mailfilter-item-light">--><?//=Loc::getMessage('SENDER_LETTER_STAT_STATS_MAILING')?><!--:</span>-->
<!--					<span class="bx-sender-mailfilter-item">-->
<!--						--><?//=htmlspecialcharsbx($arResult['POSTING']['MAILING_NAME'])?>
<!--					</span>-->
<!--				</div>-->
<!--			</div>-->

			<div class="bx-sender-block-row">
				<div class="bx-sender-block-col-2 bx-sender-block-col-margin">
					<div class="bx-sender-tittle-statistic">
						<span class="bx-sender-title-statistic-text bx-sender-title-statistic-text-bold"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_POSTING')?>:</span>
						<span id="sender_stat_filter_chain_id" class="">
							<?=htmlspecialcharsbx($arResult['POSTING']['TITLE'])?>
						</span>
					</div>

					<div class="bx-sender-tittle-statistic">
						<span class="bx-sender-title-statistic-text">
						<?=Loc::getMessage("SENDER_LETTER_STAT_STATS_MAILING_SENT_BY_AUTHOR", array(
							'%date%' => '<span data-bx-point="posting/dateSent" class="bx-sender-min-width-80">'
								. htmlspecialcharsbx($arResult['DATA']['posting']['dateSent'] ?: Loc::getMessage('SENDER_LETTER_STAT_STATS_NOW'))
								. '</span>',
							'%name%' => '<a data-bx-point="posting/createdBy/url:href" href="' . htmlspecialcharsbx($arResult['DATA']['posting']['createdBy']['url']). '" target="_top" class="bx-sender-title-statistic-link">'
								. '<span data-bx-point="posting/createdBy/name" class="bx-sender-min-width-80">'
								. htmlspecialcharsbx($arResult['DATA']['posting']['createdBy']['name'])
								. '</span>'
								. '</a>'
						))?>
						</span>
					</div>

					<div class="bx-sender-graph">
						<div class="bx-sender-graph-info">
							<span class="bx-sender-graph-info-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_READ')?>:</span>
							<span data-bx-point="counters/READ/PERCENT_VALUE_DISPLAY" class="bx-sender-graph-info-param">
								<?=htmlspecialcharsbx($arResult['DATA']['counters']['READ']['PERCENT_VALUE_DISPLAY'])?>
							</span>
						</div>
						<div class="bx-sender-graph-scale">
							<div data-bx-point="counters/READ/PERCENT_VALUE:width" class="bx-sender-graph-scale-inner" style="width: <?=intval($arResult['DATA']['counters']['READ']['PERCENT_VALUE'] * 100)?>%;"></div>
						</div>
					</div>
					<!--
					<div class="bx-sender-tittle-statistic">
						<span class="bx-sender-tittle-statistic-half-text"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_MAILING_AVG')?></span>
						<span class="bx-sender-tittle-statistic-line"></span>
						<div class="bx-sender-tittle-statistic-proc">
							<?=htmlspecialcharsbx($arResult['MAILING_COUNTERS']['READ']['PERCENT_VALUE_DISPLAY'])?>
						</div>
					</div>
					-->
					<div class="bx-sender-mailfilter-result">
						<div class="bx-sender-mailfilter-result-item bx-sender-mailfilter-2-items">
							<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_COUNTER_READ')?></p>
							<a class=""
								href="<?=htmlspecialcharsbx($arResult['URLS']['READ'])?>"
								onclick="BX.Sender.Page.open('<?= CUtil::JSEscape($arResult['URLS']['READ'])?>'); return false;"
							>
								<span data-bx-point="counters/READ/VALUE_DISPLAY" class="bx-sender-mailfilter-result-total">
									<?=htmlspecialcharsbx($arResult['DATA']['counters']['READ']['VALUE_DISPLAY'])?>
								</span>
							</a>
						</div>
						<div class="bx-sender-mailfilter-result-item bx-sender-mailfilter-2-items">
							<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_COUNTER_CLICK')?></p>
							<a class=""
								href="<?=htmlspecialcharsbx($arResult['URLS']['CLICK'])?>"
								onclick="BX.Sender.Page.open('<?= CUtil::JSEscape($arResult['URLS']['CLICK'])?>'); return false;"
							>
								<span data-bx-point="counters/CLICK/VALUE_DISPLAY" class="bx-sender-mailfilter-result-total">
									<?=htmlspecialcharsbx($arResult['DATA']['counters']['CLICK']['VALUE_DISPLAY'])?>
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
								href="<?=htmlspecialcharsbx($arResult['URLS']['SENT_SUCCESS'])?>"
								onclick="BX.Sender.Page.open('<?= CUtil::JSEscape($arResult['URLS']['SENT_SUCCESS'])?>'); return false;"
							>
								<span class="bx-sender-title-statistic-number-bold">
									<?=htmlspecialcharsbx($arResult['DATA']['counters']['SEND_SUCCESS']['VALUE_DISPLAY'])?>
								</span>
							</a>
							<?=Loc::getMessage('SENDER_LETTER_STAT_STATS_FROM')?>
							<a class=""
								href="<?=htmlspecialcharsbx($arResult['URLS']['SEND_ALL'])?>"
								onclick="BX.Sender.Page.open('<?= CUtil::JSEscape($arResult['URLS']['SEND_ALL'])?>'); return false;"
							>
								<?=htmlspecialcharsbx($arResult['DATA']['counters']['SEND_ALL']['VALUE_DISPLAY'])?>
							</a>
						</span>
					</div>
					<div style="height: 11px;">
					</div>
					<div class="bx-sender-graph">
						<div class="bx-sender-graph-info">
							<span class="bx-sender-graph-info-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_CLICKED')?>:</span>
							<span data-bx-point="counters/CLICK/PERCENT_VALUE_DISPLAY" class="bx-sender-graph-info-param">
								<?=htmlspecialcharsbx($arResult['DATA']['counters']['CLICK']['PERCENT_VALUE_DISPLAY'])?>
							</span>
						</div>
						<div class="bx-sender-graph-scale">
							<div data-bx-point="counters/CLICK/PERCENT_VALUE:width"
								class="bx-sender-graph-scale-inner" style="width: <?=intval(
									($arResult['DATA']['counters']['CLICK']['PERCENT_VALUE']) * 100)?>%;"></div>
						</div>
					</div>
					<!--
					<div class="bx-sender-tittle-statistic">
						<span class="bx-sender-tittle-statistic-half-text">
							<?=Loc::getMessage('SENDER_LETTER_STAT_STATS_MAILING_AVG')?>
						</span>
						<span class="bx-sender-tittle-statistic-line"></span>
						<div class="bx-sender-tittle-statistic-proc">
							<?=htmlspecialcharsbx($arResult['MAILING_COUNTERS']['CLICK']['PERCENT_VALUE_DISPLAY'])?>
						</div>
					</div>
					-->
					<div class="bx-sender-mailfilter-result">
						<div class="bx-sender-mailfilter-result-item bx-sender-mailfilter-2-items">
							<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_COUNTER_SEND_ERROR')?></p>
							<a class=""
								href="<?=htmlspecialcharsbx($arResult['URLS']['SENT_ERROR'])?>"
								onclick="BX.Sender.Page.open('<?= CUtil::JSEscape($arResult['URLS']['SENT_ERROR'])?>'); return false;"
							>
								<span data-bx-point="counters/SEND_ERROR/VALUE_DISPLAY" class="bx-sender-mailfilter-result-total">
									<?=htmlspecialcharsbx($arResult['DATA']['counters']['SEND_ERROR']['VALUE_DISPLAY'])?>
								</span>
							</a>
							<?if ($arResult['CAN_RESEND_ERRORS']):?>
								<div class="sender-letter-stat-number-action">
									<span data-role="resend-errors" class="ui-btn ui-btn-xs ui-btn-light">
										<?=Loc::getMessage('SENDER_LETTER_STAT_RESEND')?>
									</span>
									<span data-hint="<?=Loc::getMessage('SENDER_LETTER_STAT_RESEND_HINT')?>"></span>
								</div>
							<?endif;?>
						</div>
						<div class="bx-sender-mailfilter-result-item bx-sender-mailfilter-2-items">
							<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_COUNTER_UNSUB')?></p>
							<a class=""
								href="<?=htmlspecialcharsbx($arResult['URLS']['UNSUB'])?>"
								onclick="BX.Sender.Page.open('<?= CUtil::JSEscape($arResult['URLS']['UNSUB'])?>'); return false;"
							>
								<span data-bx-point="counters/UNSUB/VALUE_DISPLAY" class="bx-sender-mailfilter-result-total">
									<?=htmlspecialcharsbx($arResult['DATA']['counters']['UNSUB']['VALUE_DISPLAY'])?>
								</span>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?if ($arResult['IS_SUPPORT_HEAT_MAP']):?>
		<div class="bx-sender-block" data-bx-block="ClickMap">
			<p class="bx-sender-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_CLICK_MAP')?></p>
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
		<?endif;?>

		<?/*
		<div class="bx-sender-block" data-bx-block="ReadByTime">
			<p class="bx-sender-title"><?=Loc::getMessage('SENDER_LETTER_STAT_STATS_READ_BY_TIME')?></p>
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
		*/?>

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
			BX.Sender.Letter.Stat.init(<?=Json::encode([
				'containerId' => $containerId,
				'letterId' => $arParams['CHAIN_ID'],
				'actionUri' => $arResult['ACTION_URI'],
			])?>);
		});
	</script>
</div>