<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->AddHeadString(
	'<link type="text/css" rel="stylesheet" href="/bitrix/themes/.default/check-list-style.css">');?>
<style type="text/css">
	.checklist-button-left-corn {background:url('/bitrix/js/main/core/images/controls-sprite.png') no-repeat left -328px;}
	.checklist-button-cont{background:url('/bitrix/js/main/core/images/controls-sprite.png') repeat-x left -356px;}
	.checklist-button-right-corn {background:url('/bitrix/js/main/core/images/controls-sprite.png') no-repeat -6px -328px;}
	.project-checked {background:url("/bitrix/themes/.default/images/checklist/checklist-sprite.png") no-repeat 0 -123px; height:11px;top:4px; font-size:1px; left:3px;float:left;width:14px;position:relative;margin-right:10px;}
	.checklist-report-info{vertical-align:top;display:inline-block;padding-left:10px;}
	.checklist-top-info-left{vertical-align:top}
	.bx-picture-statistic{vertical-align:middle;display:inline-block}
	.bx-table-checklist{text-align:center;width:80%;}
</style>
<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/checklist.php");
$checklist = new CCheckList;
$isStarted = $checklist->started;
if ($isStarted == true)
	$arStat = $checklist->GetSectionStat();
else
{
	$arReports = CCheckListResult::GetList(Array(),Array("REPORT"=>"Y"));
	if ($arReports)
	{
		$arReport = $arReports->GetNext();
		$arReportData = new CCheckList(is_array($arReport) ? $arReport["ID"] : false);
		$arReportInfo = $arReportData->GetReportInfo();
		$arStat = $arReportInfo["STAT"] ?? [];
//		if ($arReportInfo["INFO"]["PICTURE"])
//			$arPictureSrc = CFile::GetPath($arReportInfo["INFO"]["PICTURE"]);

	}
}
?>
<?if($isStarted):?>
	<div class="bx-gadgets-warning">
		<div class="bx-gadgets-warning-bord"></div>
		<div class="bx-gadgets-warning-bord2"></div>
		<div class="bx-gadgets-warning-text-red">
			<div class="bx-gadgets-warning-cont"><?=GetMessage("CL_PROJECT_NOT_PASSED");?></div>
		</div>
		<div class="bx-gadgets-warning-bord2"></div>
		<div class="bx-gadgets-warning-bord"></div>
	</div>
	<div><?=GetMessage("CL_CURRENT_STATE")?>:</div><br>
	<table class="bx-gadgets-table bx-table-checklist" cellspacing="0">
		<tr>
			<td><b><?=GetMessage("CL_TEST_TOTAL");?></b></td>
			<td><b><?=GetMessage("CL_TEST_CHECKED");?></b></td>
			<td><b><?=GetMessage("CL_TEST_FAILED");?></b></td>
			<td><b><?=GetMessage("CL_TEST_WAITING");?></b></td>
		</tr>
		<tr>
				<td><?=$arStat["TOTAL"]?></td>
				<td class="checklist-test-successfully" ><?=$arStat["CHECK"]?></td>
				<td class="checklist-test-unsuccessful"><?=$arStat["FAILED"]?></td>
				<td><?=$arStat["WAITING"]?></td>
		</tr>
	</table><br>
	<div><?=GetMessage("CL_TO_CHECKLIST_PAGE2",Array("#LANG#"=>LANG));?></div>
<?elseif(is_array($arReport)):?>
	<span class="project-checked"></span>
	<div class="bx-gadgets-warning bx-gadgets-info">
		<div class="bx-gadgets-warning-bord"></div>
		<div class="bx-gadgets-warning-bord2"></div>
		<div class="bx-gadgets-warning-text-green">
			<div class="bx-gadgets-warning-cont"><?=GetMessage("CL_PROJECT_PASSED");?></div>
		</div>
		<div class="bx-gadgets-warning-bord2"></div>
		<div class="bx-gadgets-warning-bord"></div>
	</div>
	<span class="checklist-top-info-left">
			<span class="checklist-top-info-left-item"><?=GetMessage("CL_TEST_TOTAL");?>:</span><br/>
			<span class="checklist-top-info-left-item"><?=GetMessage("CL_TEST_REQUIRE");?>:</span><br/>
			<span class="checklist-top-info-left-item checklist-test-successfully"><?=GetMessage("CL_TEST_CHECKED");?>:</span><br/>
			<span class="checklist-top-info-left-item checklist-test-unsuccessful"><?=GetMessage("CL_TEST_FAILED");?>:</span><br/>
			<span class="checklist-top-info-left-item checklist-test-not-necessarily"><?=GetMessage("CL_TEST_NOT_REQUIRE");?>:</span><br/>
		</span><span class="checklist-top-info-right-nambers table-statistic">
			<span class="checklist-top-info-left-item-qt"><?=$arReport["TOTAL"]?></span><br/>
			<span class="checklist-top-info-left-item-qt"><?=$arStat["REQUIRE"]?></span><br/>
			<span class="checklist-test-successfully"><?=$arStat["CHECK"]?></span><br/>
			<span class="checklist-test-unsuccessful"><?=$arStat["FAILED"]?></span><br/>
			<span class="checklist-test-not-necessarily"><?=($arStat["TOTAL"] - $arStat["REQUIRE"]);?></span><br/>
		</span>
		<span class="checklist-report-info">
			<span class="checklist-top-info-left-item checklist-testlist-grey"><?=GetMessage("CL_REPORT_DATE");?></span><br/>
			<span class="checklist-top-info-left-item"><b><?=$arReport["DATE_CREATE"]?></b></span><br/><br/>
			<span class="checklist-top-info-left-item checklist-testlist-grey"><?=GetMessage("CL_REPORT_TABLE_TESTER");?></span><br/>
			<span class="checklist-top-info-left-item">
			<?=$arReport["COMPANY_NAME"]?> (<?=$arReport["TESTER"]?>)
			</span>
		</span>
<?else:?>
	<span class="bx-gadgets-warning-cont-ball"><?=GetMessage("CL_NOT_CHECKED_YET");?></span><br><br>
	<?=GetMessage("CL_TO_CHECKLIST_PAGE",Array("#LANG#"=>LANG));?>

<?endif;?>
