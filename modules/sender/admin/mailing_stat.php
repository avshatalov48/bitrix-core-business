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

/** @var $APPLICATION \CMain */
if($APPLICATION->GetGroupRight("sender") == "D")
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


use Bitrix\Sender\Stat\Statistics;
use Bitrix\Sender\MailingChainTable;
use Bitrix\Sender\PostingTable;
use Bitrix\Fileman\Block\Editor as BlockEditor;



$request = Context::getCurrent()->getRequest();
$mailingId = intval($request->get('MAILING_ID'));
if (!$mailingId)
{
	$mailingId = intval($request->get('mailingId'));
}
$chainId = intval($request->get('ID'));
if (!$chainId)
{
	$chainId = intval($request->get('chainId'));
}
$postingId = null;
$arResult = array(
	'DATA' => array(),
	'MAILING_COUNTERS' => array(),
);

$postingFilter = array('=MAILING_ID' => $mailingId, '!DATE_SENT' => null);
if ($chainId)
{
	$postingFilter['=MAILING_CHAIN_ID'] = $chainId;
}
$postingDb = PostingTable::getList(array(
	'select' => array(
		'ID', 'MAILING_CHAIN_ID',
		'TITLE' => 'MAILING_CHAIN.TITLE', 'SUBJECT' => 'MAILING_CHAIN.SUBJECT',
		'MAILING_NAME' => 'MAILING.NAME', 'DATE_SENT',
		'LINK_PARAMS' => 'MAILING_CHAIN.LINK_PARAMS',
		'CREATED_BY' => 'MAILING_CHAIN.CREATED_BY',
		'CREATED_BY_NAME' => 'MAILING_CHAIN.CREATED_BY_USER.NAME',
		'CREATED_BY_LAST_NAME' => 'MAILING_CHAIN.CREATED_BY_USER.LAST_NAME',
		'CREATED_BY_SECOND_NAME' => 'MAILING_CHAIN.CREATED_BY_USER.SECOND_NAME',
		'CREATED_BY_LOGIN' => 'MAILING_CHAIN.CREATED_BY_USER.LOGIN',
		'CREATED_BY_TITLE' => 'MAILING_CHAIN.CREATED_BY_USER.TITLE',
	),
	'filter' => $postingFilter,
	'limit' => 1,
	'order' => array('DATE_SENT' => 'DESC', 'DATE_CREATE' => 'DESC'),
));
$posting = $postingDb->fetch();
if($posting)
{
	$chainId = intval($posting['MAILING_CHAIN_ID']);
	$postingId = intval($posting['ID']);
}


$action = $request->get('action');
if($action == 'get_template' && $chainId)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
	$message = MailingChainTable::getMessageById($chainId);
	Loader::includeModule('fileman');
	$message = BlockEditor::getHtmlForEditor($message, Context::getCurrent()->getCulture()->getCharset());
	echo $message;
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
	exit;
}
elseif ($action == 'get_read_by_time')
{
	$mailingStat = Statistics::create()->filter('chainId', $chainId);
	$mailingStat->setCacheTtl(0);
	echo Json::encode(array(
		'recommendedTime' => $mailingStat->getRecommendedSendTime(),
		'readingByTimeList' => $mailingStat->getReadingByDayTime(),
	));
	\CMain::FinalActions();
	exit;
}
elseif($postingId)
{
	$arResult['POSTING'] = $posting;
	if (!$arResult['POSTING']['TITLE'])
	{
		$arResult['POSTING']['TITLE'] = $arResult['POSTING']['SUBJECT'];
	}

	$mailingStat = Statistics::create()->filter('mailingId', $mailingId);
	$mailingStat->setCacheTtl(0);
	$arResult['CHAIN_LIST'] = $mailingStat->getChainList(7);
	$arResult['EFFICIENCY'] = $mailingStat->getEfficiency();
	$mailingCounters = $mailingStat->getCounters();
	foreach ($mailingCounters as $counter)
	{
		$arResult['MAILING_COUNTERS'][$counter['CODE']] = $counter;
	}


	$arResult['DATA']['posting']['linkParams'] = $arResult['POSTING']['LINK_PARAMS'];
	if ($arResult['POSTING']['DATE_SENT'])
	{
		$arResult['DATA']['posting']['dateSent'] = FormatDate('x', $arResult['POSTING']['DATE_SENT']->getTimestamp());
	}

	$arResult['DATA']['posting']['createdBy'] = array(
		'id' => $arResult['POSTING']['CREATED_BY'],
		'name' => '',
		'url' => '/bitrix/admin/user_edit.php?ID=' . intval($arResult['POSTING']['CREATED_BY']) . '&lang=' . LANGUAGE_ID,
	);
	$arResult['DATA']['posting']['createdBy']['name'] = \CUser::FormatName(
		\CSite::GetNameFormat(true),
		array(
			"TITLE" => $arResult['POSTING']['CREATED_BY_TITLE'],
			"NAME" => $arResult['POSTING']['CREATED_BY_NAME'],
			"SECOND_NAME" => $arResult['POSTING']['CREATED_BY_SECOND_NAME'],
			"LAST_NAME" => $arResult['POSTING']['CREATED_BY_LAST_NAME'],
			"LOGIN" => $arResult['POSTING']['CREATED_BY_LOGIN'],
		),
		true, true
	);

	$postingStat = Statistics::create()->filter('mailingId', $mailingId)->filter('postingId', $postingId);
	$postingStat->setCacheTtl(0);
	$arResult['DATA']['clickList'] = $postingStat->getClickLinks();
	$arResult['DATA']['counters'] = array();
	$counters = $postingStat->getCounters();
	foreach ($counters as $counter)
	{
		$arResult['DATA']['counters'][$counter['CODE']] = $counter;
	}
}
else
{
	$arResult['ERROR'] = GetMessage("SENDER_MAILING_STATS_NO_POSTINGS");
}


if ($action == 'get_data' && empty($arResult['ERROR']))
{
	echo Json::encode($arResult['DATA']);
	\CMain::FinalActions();
	exit;
}

$lAdmin = new CAdminList("tbl_sender_mailing_stat");
$lAdmin->BeginCustomContent();
if(!empty($arResult['ERROR'])):
	$adminMessage = new CAdminMessage($arResult['ERROR']);
	echo $adminMessage->Show();
else:
	CJSCore::Init(array("sender_stat"));
	?>
	<script>
		BX.ready(function () {
			var params = <?=Json::encode(array(
				'mailingId' => $mailingId,
				'chainId' => $chainId,
				'postingId' => $postingId,
				'posting' => $arResult['DATA']['posting'],
				'chainList' => $arResult['CHAIN_LIST'],
				'clickList' => $arResult['DATA']['clickList'],
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
	<?
endif;
$lAdmin->EndCustomContent();
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('SENDER_MAILING_STATS_TITLE'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");