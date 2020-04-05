<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!\Bitrix\Main\Loader::includeModule("sender"))
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));

IncludeModuleLangFile(__FILE__);

$MAILING_ID = intval($_REQUEST['MAILING_ID']);
$ID = intval($_REQUEST['ID']);

$find_mailing_id = intval($_REQUEST['find_mailing_id']);
if($find_mailing_id>0)
	$MAILING_ID= $find_mailing_id;
$find_mailing_chain_id = intval($_REQUEST['find_mailing_chain_id']);
if($find_mailing_chain_id>0)
	$ID = $find_mailing_chain_id;

CJSCore::RegisterExt('sender_stat', array(
	'js' => array(
		'/bitrix/js/main/amcharts/3.3/amcharts.js',
		'/bitrix/js/main/amcharts/3.3/funnel.js',
		'/bitrix/js/main/amcharts/3.3/serial.js',
		'/bitrix/js/main/amcharts/3.3/themes/light.js',
	),
	'rel' => array('ajax', "date")
));
CJSCore::Init(array("sender_stat"));

$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_sender_statistics";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;

	return count($lAdmin->arFilterErrors)==0;
}

if($lAdmin->IsDefaultFilter())
{

}

$FilterArr = Array(
	"find_mailing_id",
);

$lAdmin->InitFilter($FilterArr);

if (CheckFilter())
{
	$arFilter = Array();
	if($find_mailing_id>0)
		$arFilter["=POSTING.MAILING_ID"] = $find_mailing_id;

	foreach($arFilter as $k => $v) if($v!==0 && empty($v)) unset($arFilter[$k]);
}

$mailing = null;
if($MAILING_ID > 0)
{
	$mailingDb = \Bitrix\Sender\MailingTable::getList(array(
		'filter' => array('ID' => $MAILING_ID, 'IS_TRIGGER' => 'Y')
	));
	if (!$mailing = $mailingDb->fetch())
		$MAILING_ID = 0;
}

$statList = array();
if($MAILING_ID > 0)
{
	$i = 1;
	$chainList = \Bitrix\Sender\MailingTable::getChain($MAILING_ID);
	foreach($chainList as $chain)
	{
		$stat = array(
			'NAME' => GetMessage("sender_stat_trig_letter") . ($i++),
			'SUBJECT' => $chain['SUBJECT'],
			'CNT' => array(
				'SENT_SUCCESS' => 0,
				'SENT_ERROR' => 0,
				'READ' => 0,
				'CLICK' => 0,
				'UNSUB' => 0,
				'GOAL' => 0,
				'START' => 0,
			)
		);
		$statRawDb = \Bitrix\Sender\PostingTable::getList(array(
			'select' => array(
				'CNT', 'READ_CNT', 'CLICK_CNT', 'UNSUB_CNT'
			),
			'filter' => array(
				'=MAILING_CHAIN_ID' => $chain['ID'],
			),
			'runtime' => array(
				new \Bitrix\Main\Entity\ExpressionField('CNT', 'SUM(%s)', 'COUNT_SEND_SUCCESS'),
				new \Bitrix\Main\Entity\ExpressionField('READ_CNT', 'SUM(%s)', 'COUNT_READ'),
				new \Bitrix\Main\Entity\ExpressionField('CLICK_CNT', 'SUM(%s)', 'COUNT_CLICK'),
				new \Bitrix\Main\Entity\ExpressionField('UNSUB_CNT', 'SUM(%s)', 'COUNT_UNSUB')
			),
		));
		while($statRaw = $statRawDb->fetch())
		{
			$stat['CNT']['SENT_SUCCESS'] += $statRaw['CNT'];
			$stat['CNT']['READ'] += $statRaw['READ_CNT'];
			$stat['CNT']['CLICK'] += $statRaw['CLICK_CNT'];
			$stat['CNT']['UNSUB'] += $statRaw['UNSUB_CNT'];

			$stat['CNT']['START'] += $statRaw['CNT'];
		}

		$statRawDb = \Bitrix\Sender\PostingRecipientTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=POSTING.MAILING_CHAIN_ID' => $chain['ID'],
				'=STATUS' => array(
					\Bitrix\Sender\PostingRecipientTable::SEND_RESULT_SUCCESS,
					\Bitrix\Sender\PostingRecipientTable::SEND_RESULT_DENY,
				),
				'!DATE_DENY' => null
			)
		));
		$stat['CNT']['GOAL'] = $statRawDb->getSelectedRowsCount();

		$statList['CHAIN'][] = $stat;
	}
}

if(!empty($statList))
{
	foreach($statList['CHAIN'] as $chain) foreach($chain['CNT'] as $k => $v)
	{
		if(!isset($statList['CNT'][$k]))
			$statList['CNT'][$k] = 0;

		$statList['CNT'][$k] += $v;
	}
	$statList['CNT']['START'] = $statList['CHAIN'][0]['CNT']['START'];

	$goalStart = 0;
	$goalEnd = 0;
	foreach($statList['CHAIN'] as $k => $chain)
	{
		$goalEnd = $goalStart + $chain['CNT']['GOAL'];
		$statList['CHAIN'][$k]['GOAL_START'] = $goalStart;
		$statList['CHAIN'][$k]['GOAL_END'] = $goalEnd;

		foreach($chain['CNT'] as $cntKey => $cntValue)
			$statList['CHAIN'][$k]['CNT_'.$cntKey] = $cntValue;

		$statList['CHAIN'][$k]['color'] = '#04D215';

		$goalStart = $goalEnd;
	}
}

$strError = "";
if($MAILING_ID <= 0)
{
	$strError = GetMessage("sender_stat_error_no_data");
}

$lAdmin->BeginCustomContent();
if(!empty($strError)):
	CAdminMessage::ShowMessage($strError);
else:

?>
<div class="sender_statistics" style="width: 100%;">
	<div class="sender-stat-cont">
		<div class="sender-stat-left">
			<?=GetMessage("sender_stat_trig_name")?> "<?=htmlspecialcharsbx($mailing['NAME'])?>"
			<div class="sender-stat-info">
				<div class="sender-stat-info-list">
					<?=GetMessage("sender_stat_trig_cnt_start", array('%CNT%' => $statList['CNT']['START']))?>
				</div>
				<div class="sender-stat-info-list">
					<?=GetMessage("sender_stat_trig_cnt_end", array('%CNT%' => $statList['CNT']['GOAL']))?>
				</div>
				<div class="sender-stat-info-list">
					<?=GetMessage("sender_stat_trig_cnt_unsub", array('%CNT%' => $statList['CNT']['UNSUB']))?>
				</div>
			</div>
		</div>
		<div class="sender-stat-left">
			&nbsp;
			<div class="sender-stat-info">
				<div class="sender-stat-info-list">
					<?=GetMessage("sender_stat_trig_cnt_sent", array('%CNT%' => $statList['CNT']['SENT_SUCCESS']))?>
				</div>
				<div class="sender-stat-info-list">
					<?=GetMessage("sender_stat_trig_cnt_read", array('%CNT%' => $statList['CNT']['READ']))?>
				</div>
				<div class="sender-stat-info-list">
					<?=GetMessage("sender_stat_trig_cnt_click", array('%CNT%' => $statList['CNT']['CLICK']))?>
				</div>
			</div>
		</div>
	</div>
	<div  class="sender-stat-reiterate-cont">
		<div class="sender-stat-reiterate-head"><?=GetMessage("sender_stat_trig_goal")?></div>
		<div id="chartdiv" class="sender-stat-reiterate-graph" style="height: 400px;"></div>
	</div>
	<script>
		BX.ready(function(){
			var chart = AmCharts.makeChart("chartdiv", {
				"theme": "dark",
				"type": "serial",
				"pathToImages": "/bitrix/js/main/amcharts/3.3/images/",
				"dataProvider": <?=CUtil::PhpToJSObject($statList['CHAIN'])?>,
				"valueAxes": [{
					"axisAlpha": 0,
					"gridAlpha": 0.1
				}],
				"startDuration": 1,
				"graphs": [{
					"balloonText": "<?=GetMessage("sender_stat_trig_goal_subject")?>: [[SUBJECT]]<br/><?=GetMessage("sender_stat_trig_goal_start")?>: [[CNT_START]]<br/><?=GetMessage("sender_stat_trig_goal_end")?>: [[CNT_GOAL]]",
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
<?
endif;
$lAdmin->EndCustomContent();
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("sender_stat_title"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$arMailingFilter = array();
$arFilterNames = array(
	GetMessage("sender_stat_flt_mailing")
);
if($MAILING_ID > 0)
{
	$arFilterNames[] = GetMessage("sender_stat_flt_mailing_chain");
	$arMailingFilter['=ID'] = $MAILING_ID;
}

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	$arFilterNames
);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
	<tr>
		<td><?=GetMessage("sender_stat_flt_mailing")?>:</td>
		<td>
			<?
			$arr = array();
			$mailingDb = \Bitrix\Sender\MailingTable::getList(array(
				'select'=>array('REFERENCE'=>'NAME','REFERENCE_ID'=>'ID'),
				'filter' => $arMailingFilter
			));
			while($arMailing = $mailingDb->fetch())
			{
				$arr['reference'][] = $arMailing['REFERENCE'];
				$arr['reference_id'][] = $arMailing['REFERENCE_ID'];
			}
			echo SelectBoxFromArray("find_mailing_id", $arr, $MAILING_ID, false, "");
			?>
		</td>
	</tr>

	<?if($MAILING_ID > 0):?>
	<tr>
		<td><?=GetMessage("sender_stat_flt_mailing_chain")?>:</td>
		<td valign="middle">
			<?
			$arr = array();
			$mailingChainDb = \Bitrix\Sender\MailingChainTable::getList(array(
				'select' => array('REFERENCE'=>'SUBJECT','REFERENCE_ID'=>'ID'),
				'filter' => array('MAILING_ID' => $MAILING_ID)
			));
			while($arMailingChain = $mailingChainDb->fetch())
			{
				$arr['reference'][] = $arMailingChain['REFERENCE'];
				$arr['reference_id'][] = $arMailingChain['REFERENCE_ID'];
			}
			echo SelectBoxFromArray("find_mailing_chain_id", $arr, $ID, false, "");
			?>
		</td>
	</tr>
	<?endif;?>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>