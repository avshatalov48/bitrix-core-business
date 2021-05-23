<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

/** @var CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

$showLastPostingHtml = function (array $chain)
{
	?>
	<div class="bx-sender-releases-item">
		<p class="bx-sender-releases-item-info">
			<span class="bx-sender-releases-date">
				<?=htmlspecialcharsbx($chain['DATE_SENT_FORMATTED'])?>
			</span>
			<?=Loc::getMessage('SENDER_STATS_IN')?>
			<a href="/bitrix/admin/sender_mailing_chain_admin.php?MAILING_ID=<?=htmlspecialcharsbx($chain['MAILING_ID'])?>&lang=<?=LANGUAGE_ID?>" class="bx-sender-releases-section">
				<?=htmlspecialcharsbx($chain['MAILING_NAME'])?>
			</a>
		</p>
		<a href="/bitrix/admin/sender_mailing_stat.php?MAILING_ID=<?=htmlspecialcharsbx($chain['MAILING_ID'])?>&ID=<?=htmlspecialcharsbx($chain['ID'])?>&lang=<?=LANGUAGE_ID?>" class="bx-sender-releases-title">
			<?=htmlspecialcharsbx($chain['NAME'])?>
		</a>
	</div>
	<?
}
?>

<script id="sender-stat-template-last-posting" type="text/html">
	<?$showLastPostingHtml(array(
		'DATE_SENT_FORMATTED' => '%DATE_SENT_FORMATTED%',
		'MAILING_ID' => '%MAILING_ID%',
		'MAILING_NAME' => '%MAILING_NAME%',
		'NAME' => '%NAME%',
		'ID' => '%ID%',
	));?>
</script>

<script>
	BX.ready(function () {
		var params = <?=Json::encode(array(
			'filters' => $arResult['FILTER_DATA'],
			'efficiency' => $arResult['DATA']['efficiency'],
			'mess' => array(
				'' => '',
			)
		))?>;

		params.context = BX('BX_SENDER_STATISTICS');
		BX.Sender.GlobalStats.load(params);
	});
</script>

<div id="BX_SENDER_STATISTICS" class="bx-sender-stat-wrapper">

	<div class="bx-sender-stat">
		<div data-bx-block="Counters" class="bx-sender-block">
			<div class="bx-sender-mailfilter">
				<div class="bx-sender-mailfilter-item"><?=Loc::getMessage('SENDER_STATS_COUNTER_SEND_ALL')?>:</div>
				<div data-bx-point="counters/SEND_ALL/VALUE_DISPLAY" class="bx-sender-mailfilter-item bx-sender-mailfilter-item-total">
					<?=htmlspecialcharsbx($arResult['DATA']['counters']['SEND_ALL']['VALUE_DISPLAY'])?>
				</div>
				<div class="bx-sender-mailfilter-item">
					<span class="bx-sender-mailfilter-item-light"><?=Loc::getMessage('SENDER_STATS_FILTER_PERIOD_FOR')?></span>
					<span id="sender_stat_filter_period" class="bx-sender-mailfilter-item-link">

					</span>
				</div>
				<div class="bx-sender-mailfilter-item">
					<span class="bx-sender-mailfilter-item-light"><?=Loc::getMessage('SENDER_STATS_FILTER_FROM_AUTHOR')?></span>
					<span id="sender_stat_filter_authorid" class="bx-sender-mailfilter-item-link">

					</span>
				</div>
			</div>

			<div class="bx-sender-mailfilter-result">
				<div class="bx-sender-mailfilter-result-item">
					<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_STATS_COUNTER_READ')?></p>
					<span data-bx-point="counters/READ/PERCENT_VALUE_DISPLAY" class="bx-sender-mailfilter-result-total bx-sender-mailfilter-result-total-proc">
						<?=htmlspecialcharsbx($arResult['DATA']['counters']['READ']['PERCENT_VALUE_DISPLAY'])?>
					</span>
				</div>
				<div class="bx-sender-mailfilter-result-item">
					<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_STATS_COUNTER_CLICK')?></p>
					<span data-bx-point="counters/CLICK/PERCENT_VALUE_DISPLAY" class="bx-sender-mailfilter-result-total bx-sender-mailfilter-result-total-proc">
						<?=htmlspecialcharsbx($arResult['DATA']['counters']['CLICK']['PERCENT_VALUE_DISPLAY'])?>
					</span>
				</div>
				<div class="bx-sender-mailfilter-result-item">
					<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_STATS_COUNTER_UNSUB')?></p>
					<span data-bx-point="counters/UNSUB/PERCENT_VALUE_DISPLAY" class="bx-sender-mailfilter-result-total bx-sender-mailfilter-result-total-proc">
						<?=htmlspecialcharsbx($arResult['DATA']['counters']['UNSUB']['PERCENT_VALUE_DISPLAY'])?>
					</span>
				</div>
				<div class="bx-sender-mailfilter-result-item">
					<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_STATS_COUNTER_SUBS')?></p>
					<span data-bx-point="counters/SUBS/VALUE_DISPLAY" class="bx-sender-mailfilter-result-total">
						<?=htmlspecialcharsbx($arResult['DATA']['counters']['SUBS']['VALUE_DISPLAY'])?>
					</span>
				</div>
				<div class="bx-sender-mailfilter-result-item">
					<p class="bx-sender-mailfilter-result-title"><?=Loc::getMessage('SENDER_STATS_COUNTER_POSTINGS')?></p>
					<span data-bx-point="counters/POSTINGS/VALUE_DISPLAY" class="bx-sender-mailfilter-result-total">
						<?=htmlspecialcharsbx($arResult['DATA']['counters']['POSTINGS']['VALUE_DISPLAY'])?>
					</span>
				</div>
			</div>
		</div>

		<div class="bx-sender-block-left-padding">

			<div data-bx-block="Efficiency" class="bx-sender-block-top-padding">
				<p class="bx-sender-title"><?=Loc::getMessage('SENDER_STATS_EFFICIENCY_TITLE')?></p>
				<div class="bx-gadget-speed-speedo-block">
					<div class="bx-gadget-speed-ruler">
						<span class="bx-gadget-speed-ruler-start">0%</span>
						<span class="bx-gadget-speed-ruler-end">30%</span>
					</div>
					<div class="bx-gadget-speed-graph">
						<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-veryslow">
							<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_STATS_EFFICIENCY_LEVEL_1')?></span>
						</span>

						<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-slow">
							<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_STATS_EFFICIENCY_LEVEL_2')?></span>
						</span>

						<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-notfast">
							<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_STATS_EFFICIENCY_LEVEL_3')?></span>
						</span>

						<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-fast">
							<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_STATS_EFFICIENCY_LEVEL_4')?></span>
						</span>

						<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-varyfast">
							<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_STATS_EFFICIENCY_LEVEL_5')?></span>
						</span>

						<div data-bx-view-data-eff="" class="bx-gadget-speed-pointer" style="left: 0;">
							<div data-bx-view-data-eff-val="" class="bx-gadget-speed-value">0%</div>
						</div>
					</div>
				</div>
			</div>

			<div data-bx-block="ChainList" class="bx-sender-last-postings bx-sender-block-top-padding">
				<p class="bx-sender-title"><?=Loc::getMessage('SENDER_STATS_RECENT_POSTINGS')?></p>
				<div data-bx-view-loader="" class="bx-sender-insert bx-sender-insert-last-postings bx-sender-insert-loader" style="display: none;">
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
				<div data-bx-view-data="" class="bx-sender-releases">
					<div data-bx-view-data-postings="" class="bx-sender-last-releases">
						<?foreach($arResult['DATA']['chainList'] as $chain):?>
							<?$showLastPostingHtml($chain)?>
						<?endforeach;?>
					</div>
					<div class="bx-sender-new-releases">
						<a href="/bitrix/admin/sender_mailing_wizard.php?IS_TRIGGER=N&lang=ru" class="adm-btn adm-btn-save bx-sender-btn">
							<?=Loc::getMessage('SENDER_STATS_CREATE_NEW')?>
						</a>
					</div>
				</div>
			</div>
		</div>


		<div data-bx-block="CountersDynamic">
			<?
			foreach ($arResult['COUNTERS_DYNAMIC_NAMES'] as $name):
				$name = htmlspecialcharsbx($name);
				?>
				<div class="bx-sender-block" data-bx-chart="<?=$name?>">
					<p class="bx-sender-title"><?=Loc::getMessage('SENDER_STATS_CHART_' . $name)?></p>
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
					<div data-bx-view-data="" class="bx-sender-block-view-data bx-sender-resizer"></div>
					<div data-bx-view-text="" class="bx-sender-block-view-text">
						<div class="bx-sender-block-view-text-item"><?=Loc::getMessage('SENDER_STATS_NO_DATA')?></div>
					</div>
				</div>
				<?
			endforeach;
			?>
		</div>

	</div>
</div>