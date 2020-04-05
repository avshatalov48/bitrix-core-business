<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if(!Loader::includeModule("sender"))
{
	ShowError(Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));
}

/** @var $USER \CUser */
/** @var $APPLICATION \CMain */
if($APPLICATION->GetGroupRight("sender") == "D")
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


use Bitrix\Sender\Stat\Statistics;

$arResult = array(
	'DATA' => array(),
	'MAILING_COUNTERS' => array(),
);

$request = Context::getCurrent()->getRequest();
$action = $request->get('action');
if ($action == 'get_counters_dynamic')
{
	$stat = Statistics::create()->setUserId($USER->GetID())->initFilterFromRequest();
	echo Json::encode(array(
		'countersDynamic' => $stat->getCountersDynamic(),
	));
	\CMain::FinalActions();
	exit;
}
else
{
	$stat = Statistics::create()->setUserId($USER->GetID())->initFilterFromRequest();
	$arResult['DATA']['chainList'] = $stat->getChainList(3);
	$arResult['DATA']['counters'] = array();
	$counters = $stat->getCounters();
	$counters[] = $stat->getCounterSubscribers();
	$counters[] = $stat->getCounterPostings();
	foreach ($counters as $counter)
	{
		$arResult['DATA']['counters'][$counter['CODE']] = $counter;
	}

	$efficiency = $stat->getEfficiency();
	if (!$efficiency['VALUE'])
	{
		$globalStat = Statistics::create();
		$efficiency = $globalStat->getEfficiency();
	}
	$efficiency['PERCENT_VALUE'] *= 100;
	$efficiency['VALUE'] *= 100;
	$arResult['DATA']['efficiency'] = $efficiency;

	$arResult['COUNTERS_DYNAMIC_NAMES'] = array(
		'EFFICIENCY',
		'READ',
		'CLICK',
		'UNSUB',
	);
}

if ($action == 'getData' && empty($arResult['ERROR']))
{
	echo Json::encode($arResult['DATA']);
	\CMain::FinalActions();
	exit;
}

CJSCore::Init(array("sender_stat", "sender_page"));
$lAdmin = new CAdminList("tbl_sender_statistics");
$lAdmin->BeginCustomContent();
if(!empty($arResult['ERROR'])):
	$adminMessage = new CAdminMessage($arResult['ERROR']);
	echo $adminMessage->Show();
else:

$showLastPostingHtml = function (array $chain)
{
	?>
	<div class="bx-sender-releases-item">
		<p class="bx-sender-releases-item-info">
			<span class="bx-sender-releases-date">
				<?=htmlspecialcharsbx($chain['DATE_SENT_FORMATTED'])?>
			</span>
			<?=Loc::getMessage('SENDER_STATS_IN')?>
			<a href="/bitrix/admin/sender_campaign.php?edit&ID=<?=htmlspecialcharsbx($chain['MAILING_ID'])?>&lang=<?=LANGUAGE_ID?>"
				onclick="BX.Sender.Page.open(this.href); return false;"
				class="bx-sender-releases-section"
			>
				<?=htmlspecialcharsbx($chain['MAILING_NAME'])?>
			</a>
		</p>

		<a href="/bitrix/admin/sender_letters.php?stat&ID=<?=htmlspecialcharsbx($chain['MAILING_ID'])?>&ID=<?=htmlspecialcharsbx($chain['ID'])?>&lang=<?=LANGUAGE_ID?>"
			onclick="BX.Sender.Page.open(this.href); return false;"
			class="bx-sender-releases-title"
		>
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
			'filters' => $stat->getGlobalFilterData(),
			'efficiency' => $arResult['DATA']['efficiency'],
			'mess' => array(
				'' => '',
			)
		))?>;

		params.context = BX('BX_SENDER_STATISTICS');
		BX.Sender.GlobalStats.load(params);
	});
</script>

<div id="BX_SENDER_STATISTICS" class="">

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

			<div class="bx-sender-mailfilter-result" style="margin: 0 0 0 25px;">
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
					<div class="bx-gadget-speed-graph">
						<div class="bx-gadget-speed-ruler">
							<!--<span class="bx-gadget-speed-ruler-start">0%</span>-->
							<!--<span class="bx-gadget-speed-ruler-end">30%</span>-->
						</div>

						<div class="bx-gadget-speed-graph-box">
							<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-veryslow"></span>
							<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_STATS_EFFICIENCY_LEVEL_1')?></span>
						</div>

						<div class="bx-gadget-speed-graph-box">
							<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-slow"></span>
							<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_STATS_EFFICIENCY_LEVEL_2')?></span>
						</div>

						<div class="bx-gadget-speed-graph-box">
							<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-notfast"></span>
							<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_STATS_EFFICIENCY_LEVEL_3')?></span>
						</div>

						<div class="bx-gadget-speed-graph-box">
							<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-fast"></span>
							<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_STATS_EFFICIENCY_LEVEL_4')?></span>
						</div>

						<div class="bx-gadget-speed-graph-box">
							<span class="bx-gadget-speed-graph-part bx-gadget-speed-graph-varyfast"></span>
							<span class="bx-gadget-speed-graph-text"><?=Loc::getMessage('SENDER_STATS_EFFICIENCY_LEVEL_5')?></span>
						</div>

						<div data-bx-view-data-eff="" class="bx-gadget-speed-pointer" id="site-speed-pointer" style="left: 0;">
							<div class="bx-gadget-speed-value" id="site-speed-pointer-index">
								<span data-bx-view-data-eff-val="" class="bx-gadget-speed-value-percent">0%</span>
							</div>
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
						<a href="/bitrix/admin/sender_letters.php?edit=&ID=0&code=mail&lang=<?=LANGUAGE_ID?>"
							onclick="BX.Sender.Page.open(this.href); return false;"
							class="adm-btn adm-btn-save bx-sender-btn"
						>
							<?=Loc::getMessage('SENDER_STATS_CREATE_NEW_LETTER')?>
						</a>
					</div>
				</div>
			</div>
		</div>

		<div class="bx-sender-block-left-padding">
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
</div>
<?
endif;
$lAdmin->EndCustomContent();
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SENDER_STATS_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");