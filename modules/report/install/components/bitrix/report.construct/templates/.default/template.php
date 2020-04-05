<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->SetTitle(GetMessage('REPORT_CONSTRUCT'));

CJSCore::Init(array('report', 'socnetlogdest'));

$jsClass = 'ReportConstructClass_'.$arResult['randomString'];

if (!empty($arResult['ERROR']))
{
	echo $arResult['ERROR'];
	return false;
}

if (!empty($arResult['FORM_ERROR']))
{
	?>
	<font color='red'><?=$arResult['FORM_ERROR']?></font><br/><br/>
	<?
}

?>

<? if ($arParams['ACTION'] == 'delete'): ?>
	<?=sprintf(GetMessage('REPORT_DELETE_CONFIRM'), htmlspecialcharsbx($arResult['report']['TITLE']))?>
	<br /><br />
	<form style="float: left;" method="POST" action="<?=CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_REPORT_CONSTRUCT"], array("report_id" => $arParams['REPORT_ID'], 'action' => 'delete_confirmed'));?>">
		<?php echo bitrix_sessid_post('csrf_token')?>
		<input type="submit" value="<?=GetMessage('REPORT_DELETE_BUTTON')?>" />
	</form>
	<div style="float: left;">&nbsp;</div>
	<form method="GET" action="<?=CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_REPORT_LIST"], array());?>">
		<input type="submit" value="<?=GetMessage('REPORT_DELETE_CANCEL')?>" />
	</form>
	<? return true; ?>
<? endif; ?>

<?

$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/js/report/css/report.css');
$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/report/construct.js');

CJSCore::Init(array('date'));

// filter fields selectors
if (is_array($arResult['ufInfo']))
{
	$APPLICATION->IncludeComponent(
		'bitrix:report.filter.field.selector',
		'',
		array('ufInfo' => $arResult['ufInfo']),
		false,
		array('HIDE_ICONS' => true)
	);
}

?>

<script type="text/javascript">
var GLOBAL_BX_REPORT_USING_CHARTS = true;

var GLOBAL_REPORT_SELECT_COLUMN_COUNT = 0;
var GLOBAL_REPORT_FILTER_COUNT = 1;
var GLOBAL_PRE_FILTERS = null;

<? if (!empty($arResult["preSettings"]["filter"])): ?>
GLOBAL_PRE_FILTERS = <?=CUtil::PhpToJSObject($arResult["preSettings"]["filter"])?>;
<? endif; ?>

BX.message({'REPORT_DEFAULT_TITLE': '<?=CUtil::JSEscape(GetMessage('REPORT_DEFAULT_TITLE'))?>'});
BX.message({'REPORT_ADD': '<?=CUtil::JSEscape(GetMessage('REPORT_ADD'))?>'});
BX.message({'REPORT_CANCEL': '<?=CUtil::JSEscape(GetMessage('REPORT_CANCEL'))?>'});
BX.message({'REPORT_PRCNT_VIEW_IS_NOT_AVAILABLE': '<?=CUtil::JSEscape(GetMessage('REPORT_PRCNT_VIEW_IS_NOT_AVAILABLE'))?>'});
BX.message({'REPORT_PRCNT_BUTTON_TITLE': '<?=CUtil::JSEscape(GetMessage('REPORT_PRCNT_BUTTON_TITLE'))?>'});
BX.message({
	REPORT_BTN_SAVE: '<?=GetMessageJS("REPORT_BTN_SAVE")?>',
	REPORT_BTN_CLOSE: '<?=GetMessageJS("REPORT_BTN_CLOSE")?>',
	REPORT_SHARING_TITLE_POPUP: '<?=GetMessageJS("REPORT_SHARING_TITLE_POPUP")?>',
	REPORT_SHARING_NAME_RIGHTS_USER: '<?=GetMessageJS("REPORT_SHARING_NAME_RIGHTS_USER")?>',
	REPORT_SHARING_NAME_RIGHTS: '<?=GetMessageJS("REPORT_SHARING_NAME_RIGHTS")?>',
	REPORT_SHARING_NAME_ADD_RIGHTS_USER: '<?=GetMessageJS("REPORT_SHARING_NAME_ADD_RIGHTS_USER")?>'
});

initReportControls();
</script>

<form method="POST" name="task-filter-form" id="task-filter-form" action="<?=CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_REPORT_CONSTRUCT"], array("report_id" => $arParams['REPORT_ID'], 'action' => $arParams['ACTION']));?>">
<?php echo bitrix_sessid_post('csrf_token')?>

<div class="reports-constructor">



<!-- period -->
<div class="webform-main-fields">
	<div class="webform-corners-top">
		<div class="webform-left-corner"></div>
		<div class="webform-right-corner"></div>
	</div>
	<div class="webform-content filter-field-date-combobox">
		<div class="reports-title-label"><?=GetMessage('REPORT_TITLE')?></div>
		<?
			$_title = '';
			if (!empty($arResult['report']['TITLE']))
			{
				$_title = $arResult['report']['TITLE'];
			}
		?>
		<div class="reports-new-title-wrap">
			<input type="text" class="reports-new-title" id="reports-new-title" name="report_title" value="<?=htmlspecialcharsbx($_title)?>" />
		</div>

		<div class="reports-title-label"><?=GetMessage('REPORT_DESCRIPTION')?></div>
		<div class="reports-description-wrap">
			<textarea class="reports-description" name="report_description"><?=htmlspecialcharsbx($arResult['report']['DESCRIPTION'])?></textarea>
		</div>

		<div class="reports-title-label"><?=GetMessage('REPORT_PERIOD')?></div>
		<select class="filter-dropdown" onchange="OnTaskIntervalChange(this)" id="task-interval-filter" name="F_DATE_TYPE">
			<?php foreach($arResult['periodTypes'] as $key):?>
				<option value="<?=$key?>"<?=($key == $arResult["preSettings"]["period"]['type']) ? ' selected' : ''?>><?=GetMessage('REPORT_CALEND_'.ToUpper($key))?></option>
			<?php endforeach;?>
		</select>
		<?
			$_date_from = '';
			$_date_to = '';
			if ($arResult["preSettings"]["period"]['type'] == 'interval')
			{
				$_date_from = ConvertTimeStamp($arResult["preSettings"]["period"]['value'][0], 'SHORT');
				$_date_to = ConvertTimeStamp($arResult["preSettings"]["period"]['value'][1], 'SHORT');
			}
			else if ($arResult["preSettings"]["period"]['type'] == 'before')
			{
				$_date_from = ConvertTimeStamp($arResult["preSettings"]["period"]['value'], 'SHORT');
			}
			else if ($arResult["preSettings"]["period"]['type'] == 'after')
			{
				$_date_to = ConvertTimeStamp($arResult["preSettings"]["period"]['value'], 'SHORT');
			}
		?>
		<span class="filter-date-interval"><span class="filter-date-interval-from-wrap"><input type="text" class="filter-date-interval-from" name="F_DATE_FROM" id="REPORT_INTERVAL_F_DATE_FROM" value="<?=$_date_from?>" /><a class="filter-date-interval-calendar" href="" title="<?php echo GetMessage("REPORT_CALEND_PICK_DATE")?>" id="filter-date-interval-calendar-from"><img border="0" src="/bitrix/js/main/core/images/calendar-icon.gif" alt="<?php echo GetMessage("REPORT_CALEND_PICK_DATE")?>"/></a></span><span class="filter-date-interval-hellip">&hellip;</span><span class="filter-date-interval-to-wrap"><input type="text" class="filter-date-interval-to" name="F_DATE_TO" id="REPORT_INTERVAL_F_DATE_TO" value="<?=$_date_to?>" /><a href="" class="filter-date-interval-calendar" title="<?php echo GetMessage("REPORT_CALEND_PICK_DATE")?>" id="filter-date-interval-calendar-to"><img border="0" src="/bitrix/js/main/core/images/calendar-icon.gif" alt="<?php echo GetMessage("REPORT_CALEND_PICK_DATE")?>"/></a></span></span>
		<span class="filter-day-interval<?php if ($arResult["preSettings"]["period"]['type'] == "days"):?> filter-day-interval-selected<?php endif?>"><input type="text" size="5" class="filter-date-days" value="<?php echo $arResult["preSettings"]["period"]['type'] == "days" ? $arResult["preSettings"]["period"]['value'] : ""?>" name="F_DATE_DAYS" /> <?php echo GetMessage("REPORT_CALEND_REPORT_DAYS")?></span>

	</div>
</div>


<!-- select -->
<div class="reports-content-block" id='report_columns_list'>
	<span class="reports-content-block-title"><?=GetMessage('REPORT_COLUMNS')?></span>
	<div class="reports-add-columns-block" id="reports-add-columns-block">

		<div id="reports-forming-column-example" style="display: none">
			<input type="hidden" name="report_select_columns[%s][name]" />
			<span class="reports-add-col-checkbox">
				<input type="checkbox" class="reports-checkbox"/>
			</span><span
				class="reports-add-col-buttons-bl">
				<span class="reports-add-col-button-down"></span><span class="reports-add-col-button-up"></span>
			</span><span
			class="reports-add-col-title"><span
			class="reports-add-col-tit-text"></span><span
			class="reports-add-col-input"><input type="text" name="report_select_columns[%s][alias]"/><span
			class="reports-add-col-inp-title"><?=GetMessage('REPORT_NEW_COLUMN_TITLE')?></span></span><span
				class="reports-add-col-tit-prcnt" title="<?=GetMessage('REPORT_PRCNT_BUTTON_TITLE')?>"></span><span
			class="reports-add-col-tit-edit" title="<?=GetMessage('REPORT_CHANGE_COLUMN_TITLE')?>"></span><span
			class="reports-add-col-tit-remove" title="<?=GetMessage('REPORT_REMOVE_COLUMN')?>"></span></span>
		</div>

		<div class="reports-add-column" id="reports-add-column-block">
			<span class="reports-checkbox-arrow"></span>
			<span class="reports-checkbox-title"><?=GetMessage('REPORT_CALC_COLUMN')?></span><span
				class="reports-add-column-link reports-dashed" id="reports-add-select-column-button"><?=GetMessage('REPORT_ADD_SELECT_COLUMN')?></span>
		</div>
	</div>
	<div class="reports-sort-column">
		<span class="reports-content-block-title"><?=GetMessage('REPORT_SORT_BY_SELECT_COLUMN')?></span><select class="reports-sort-select" id="reports-sort-select" name="reports_sort_select"><option value="_">_</option></select>
		<select name="reports_sort_type_select" id="reports-sort-type-select" class="reports-sort-type-select"><option value="ASC"><?=GetMessage('REPORT_SORT_TYPE_ASC')?></option><option value="DESC"><?=GetMessage('REPORT_SORT_TYPE_DESC')?></option></select>
	</div>

	<script type="text/javascript">

	BX.ready(function() {

		<? foreach ($arResult['preSettings']['select'] as $num => $selElem): ?>
			addSelectColumn(BX.findChild(
				BX('reports-add_col-popup-cont'),
				{tag:'input', attr:{type:'checkbox', name:'<?=CUtil::JSEscape($selElem['name'])?>'}}, true
			),
			'<?=strlen($selElem['aggr']) ? CUtil::JSEscape($selElem['aggr']) : ''?>',
			'<?=strlen($selElem['alias']) ? CUtil::JSEscape($selElem['alias']) : ''?>',
			<?=$num?>,
			<?=($selElem['grouping']) ? 'true' : 'false'?>,
			<?=($selElem['grouping_subtotal']) ? 'true' : 'false'?>);
		<? endforeach; ?>

		<? foreach ($arResult['preSettings']['select'] as $num => $selElem): ?>
			<? if (strlen($selElem['prcnt'])): ?>
				setPrcntView(<?=$num?>, '<?=CUtil::JSEscape($selElem['prcnt'])?>');
			<? endif; ?>
		<? endforeach; ?>

		<? if (array_key_exists("sort", $arResult["preSettings"])): ?>
			// add default sort
			setSelectValue(BX('reports-sort-select'), '<?=CUtil::JSEscape($arResult["preSettings"]['sort'])?>');
		<? endif; ?>

		<? if (array_key_exists("sort_type", $arResult["preSettings"])): ?>
			// add default sort
			setSelectValue(BX('reports-sort-type-select'), '<?=CUtil::JSEscape($arResult["preSettings"]['sort_type'])?>');
		<? endif; ?>

		startSubFilterRestore();
	});

	</script>
</div>


<!-- filters -->
<div class="webform-additional-fields">
	<div class="reports-content-block">
		<span class="reports-content-block-title reports-title-filter"><?=GetMessage('REPORT_FILTER')?></span>

		<div class="reports-filter-block">

			<div class="reports-limit-results" id="reports-filter-base-andor-selector">
				<span class="reports-limit-res-select-lable"><?=GetMessage('REPORT_RESULT_LIMIT_BY')?></span><select filterId="0" class="reports-limit-res-select">
				<option value="AND"><?=GetMessage('REPORT_ANDOR_AND')?></option>
				<option value="OR"><?=GetMessage('REPORT_ANDOR_OR')?></option>
			</select><span class="reports-limit-res-select-lable" id="reports-filter-base-andor-selector-text-and"><?=GetMessage('REPORT_RESULT_LIMIT_CONDITIONS')?></span><span class="reports-limit-res-select-lable" id="reports-filter-base-andor-selector-text-or" style="display: none;"><?=GetMessage('REPORT_RESULT_LIMIT_CONDITION')?></span>
			</div>

			<div id="reports-filter-item-example" style="display: none">
				<span class="reports-filter-item-name"><span class="reports-dashed"><?=GetMessage('REPORT_CHOOSE_FIELD')?></span></span><span class="reports-filter-butt-wrap"><span class="reports-filter-del-item"><i></i></span><span
					class="reports-filter-add-item"><i></i></span><span
					class="reports-filter-and-or"><span class="reports-filter-and-or-text"><?=GetMessage('REPORT_ANDOR')?></span><i></i></span><input type="checkbox" class="reports-checkbox" name="changeable" checked /></span>
			</div>

			<div id="reports-filter-andor-container-example" style="display: none" class="reports-filter-andor-container">
				<div class="reports-filter-andor-item">
					<select class="reports-filter-select-small" style="width: 80px;">
						<option value="AND"><?=GetMessage('REPORT_ANDOR_ALL')?></option>
						<option value="OR"><?=GetMessage('REPORT_ANDOR_ANY')?></option>
					</select>
					<span class="reports-limit-res-select-lable reports-limit-res-select-lable-and"><?=GetMessage('REPORT_ANDOR_ALL_LABEL')?></span><span class="reports-limit-res-select-lable reports-limit-res-select-lable-or" style="display: none;"><?=GetMessage('REPORT_ANDOR_ANY_LABEL')?></span><span class="reports-filter-butt-wrap"><span class="reports-filter-del-item"><i></i></span><span
						class="reports-filter-add-item"><i></i></span><span
						class="reports-filter-and-or"><span class="reports-filter-and-or-text"><?=GetMessage('REPORT_ANDOR')?></span><i></i></span><input type="checkbox" class="reports-checkbox" disabled/></span>
				</div>
			</div>

			<div class="reports-filter-andor-container" id="reports-filter-columns-container"></div>

			<div class="reports-filter-checkbox-title">
				<span class="reports-filter-checkbox-arrow"></span>
				<span class="reports-filter-checkbox-tit-text"><?=GetMessage('REPORT_CHANGE_FILTER_IN_VIEW')?></span>
			</div>
		</div>

		<script type="text/javascript">

			BX.ready(function() {
				<? if (!empty($arResult["preSettings"]["limit"])): ?>
				// add default limit
				setReportLimit(true, '<?=$arResult["preSettings"]["limit"]?>');
				<? endif; ?>
				<? if ($arResult["preSettings"]["grouping_mode"] === true): ?>
				enableReportLimit(false);
				<? endif; ?>
			});

		</script>

		<div class="reports-filter-quan-item">
			<input type="checkbox" class="reports-checkbox" id="report-filter-limit-checkbox"/>
			<span class="reports-limit-res-select-lable"><label for="report-filter-limit-checkbox"><?=GetMessage('REPORT_RESULT_LIMIT')?></label></span>
			<input type="text" class="reports-filter-quan-inp" id="report-filter-limit-input" name="report_filter_limit" disabled/>
		</div>

		<?
		$bShowRedNegValsOption =  true;
		$helperClassName = isset($arParams['REPORT_HELPER_CLASS']) ? $arParams['REPORT_HELPER_CLASS'] : '';
		if ($helperClassName != '')
		{
			$classNamePrefix = substr($helperClassName, 0, 7);
			if ($classNamePrefix === 'CTasksR' || $classNamePrefix === 'CCrmRep')
				$bShowRedNegValsOption = false;
		}
		?>
		<? if ($bShowRedNegValsOption): ?>
		<div class="reports-filter-quan-item">
			<input type="checkbox" <?=($arResult['preSettings']['red_neg_vals'] === true)?'checked="checked" ':''?>
				class="reports-checkbox" id="report-negative-values-red-checkbox" name="report_red_neg_vals"/>
			<span class="reports-limit-res-select-lable">
				<label for="report-negative-values-red-checkbox"><?=GetMessage('REPORT_NEGATIVE_VALUES_RED')?></label>
			</span>
		</div>
		<? endif;    // if ($bShowRedNegValsOption): ?>
	</div>
	<div class="webform-corners-bottom">
		<div class="webform-left-corner"></div>
		<div class="webform-right-corner"></div>
	</div>
</div>

<!-- chart configuration -->
<?php if ($arParams['USE_CHART']): ?>
	<style type="text/css">
		.report-chart-add-ycolumn {
			display: none;
			float: left;
			position: relative;
			top: 0px;
			left: 0px;
			width: 10px;
			height: 10px;
			padding: 3px 3px 3px 3px;
			margin-left: 4px;
			background: url("/bitrix/js/report/css/images/sprites.png") -119px -3px no-repeat;
			cursor: pointer;
		}
		.report-chart-add-ycolumn-dummy {
			float: left;
			position: relative;
			top: 0px;
			left: 0px;
			width: 10px;
			height: 10px;
			padding: 3px 3px 3px 3px;
			margin-left: 4px;
		}
		.report-chart-remove-ycolumn {
			display: none;
			float: right;
			position: relative;
			top: 0px;
			right: 0px;
			width: 10px;
			height: 10px;
			padding: 3px 3px 3px 3px;
			cursor: pointer;
		}
		.report-chart-remove-ycolumn-dummy {
			float: right;
			position: relative;
			top: 0px;
			right: 0px;
			width: 10px;
			height: 10px;
			padding: 3px 3px 3px 3px;
		}
		.report-chart-span-minus {
			display: block;
			position: relative;
			top: 4px;
			width: 10px;
			height: 2px;
			background: url("/bitrix/js/report/css/images/sprites.png") -122px 0 no-repeat;
			cursor: pointer;
		}
		.report-chart-remove-ycolumn-bottom {
			float: left;
			left: 0;
			margin-left: 4px;
			right: auto;
		}
		.report-chart-add-ycolumn:hover {
			background-color: #C0C0C0;
		}
		.report-chart-remove-ycolumn:hover {
			background-color: #C0C0C0;
		}
		.report-chart-last-ycolumn-block:hover td .report-chart-add-ycolumn,
		.report-chart-last-ycolumn-block:hover td .report-chart-remove-ycolumn {display: block;}
		.report-chart-select-col {float: left; width: 100%;}

		/*#report-chart-config {padding-top: 20px;}*/
		/*#report-chart-switch {margin: 12px 0 12px 0;}*/
		#report-chart-params div {margin-bottom: 20px; padding: 4px 0;}
		#report-chart-display-checkbox {vertical-align: middle;}
		#report-chart-switch label {vertical-align: middle;}

		.webform-additional-fields { margin-bottom: 5px; }
		#report-chart-params { padding-top: 19px; }
		.chart-config-label {
			color: #303030/*#555555*/;
			/*font-size: 13px;
			padding: 5px 0 6px 4px;*/
		}
		#report-chart-config .reports-content-block-title { padding: 0 0 5px 1px; }
		.popup-window-close-icon {  margin-top: 0;}
	</style>
<?php $fDisplayChart = $arResult['preSettings']['chart']['display']; ?>
	<div id="report-chart-config" class="webform-additional-fields">
		<div class="reports-content-block">
			<div id="report-chart-switch"
				class="reports-content-block-title<?php echo ($fDisplayChart)?' reports-title-filter':''; ?>">
				<input id="report-chart-display-checkbox"
					type="checkbox"<?php echo ($fDisplayChart)?' checked':''; ?>
					name="display_chart"/>
				<label for="report-chart-display-checkbox"><?php
					echo htmlspecialcharsbx(GetMessage('REPORT_CHART_DISPLAY')); ?></label>
			</div>
			<div id="report-chart-params"<?php echo ($fDisplayChart)?'':' style="display: none;"'; ?>>
				<div>
			<span><label class="chart-config-label" for="report-chart-type"><?php
					echo htmlspecialcharsbx(GetMessage('REPORT_CHART_TYPE_LABEL_TEXT').':'); ?></label></span>
			<span>
				<select id="report-chart-type" name="chart_type">
					<?php
					$bSelectFirst = true;
					$chartTypeSetting = $arResult['preSettings']['chart']['type'];
					if ($chartTypeSetting !== null)
					{
						$chartTypeIds = array();
						foreach ($arResult['chartTypes'] as $chartTypeInfo) $chartTypeIds[] = $chartTypeInfo['id'];
						if (in_array($chartTypeSetting, $chartTypeIds)) $bSelectFirst = false;
					}
					?>
					<?php $i = 0; foreach($arResult['chartTypes'] as $chartType): ?>
						<option value="<?=htmlspecialcharsbx($chartType['id'])?>"<?php
						if (($i++ === 0 && $bSelectFirst)
							|| $chartType['id'] == $chartTypeSetting) echo 'selected="selected"'; ?>>
							<?=htmlspecialcharsbx($chartType['name'])?>
						</option>
					<?php endforeach; ?>
				</select>
			</span>
				</div>
				<table>
					<tr>
						<td><label class="chart-config-label" for="report-chart-args"><?php
								echo htmlspecialcharsbx(GetMessage('REPORT_CHART_LABEL_TEXT_ARGS').':'); ?></label></td>
						<td>
							<select id="report-chart-args" class="report-chart-select-col" name="chart_x"></select>
						</td>
						<td>
							<span class="report-chart-remove-ycolumn-dummy"></span>
							<span class="report-chart-add-ycolumn-dummy"></span>
						</td>
					</tr>
					<?php
					$yColumnsMaxNumber = 10;
					$yColumns = $arResult['preSettings']['chart']['y_columns'];
					$yColumnLastIndex = 0;
					$yColumnsLabelText = GetMessage('REPORT_CHART_LABEL_TEXT_VALUES');
					if (is_array($yColumns))
					{
						$n = count($yColumns);
						if ($n > 1) $yColumnLastIndex = $n - 1;
					}
					?>
					<?php for ($i = 0; $i <= $yColumnLastIndex; $i++): ?>
						<tr<?php if ($yColumnLastIndex === $i) echo ' id="report-chart-last-ycolumn-block" class="report-chart-last-ycolumn-block"'; ?>>
							<td>
								<label class="chart-config-label"<?php echo ' for="report-chart-values['.$i.']"'; ?>><?php
									echo htmlspecialcharsbx($yColumnsLabelText.(($yColumnLastIndex > 0) ? ' ('.($i+1).')' : '').':'); ?>
								</label>
							</td>
							<td>
								<select<?php echo ' id="report-chart-values['.$i.']"'; ?>
									class="report-chart-select-col"<?php echo ' name="chart_y['.$i.']"'; ?>></select>
							</td>
							<td></td>
						</tr>
					<?php endfor; // for ($i = 1; $i <= $yColumnLastIndex; $i++): ?>
				</table>
			</div>
			<!-- add y column prototype -->
			<span id="report-chart-add-ycolumn-proto" class="report-chart-add-ycolumn"
				title="<?=htmlspecialcharsbx(GetMessage('REPORT_CHART_ADD_COL_LABEL_TITLE'))?>"></span>
			<!-- remove y column prototype -->
			<span id="report-chart-remove-ycolumn-proto" class="report-chart-remove-ycolumn"
				title="<?=htmlspecialcharsbx(GetMessage('REPORT_CHART_REMOVE_COL_LABEL_TITLE'))?>">
				<span class="report-chart-span-minus"></span>
			</span>
		</div>
		<div class="webform-corners-bottom">
			<div class="webform-left-corner"></div>
			<div class="webform-right-corner"></div>
		</div>
	</div>
	<script type="text/javascript">
	BX.ready(function () {
		var i, colId, match;
		var xColumnIndex = null, yColumnsIndexes = [];
		<?php
			$xColumnIndex = $arResult['preSettings']['chart']['x_column'];
			$yColumnsIndexes = $arResult['preSettings']['chart']['y_columns'];
			if ($xColumnIndex !== null && is_array($yColumnsIndexes) && count($yColumnsIndexes) > 0)
			{
				echo 'xColumnIndex = '.CUtil::JSEscape($xColumnIndex).';'.PHP_EOL;
				foreach ($yColumnsIndexes as $k => $v)
				{
					echo "\t\t".'yColumnsIndexes['.CUtil::JSEscape(intval($k)).'] = '.CUtil::JSEscape(intval($v)).';'.
						PHP_EOL;
				}
			}
		?>
		var xSelect =  BX('report-chart-args');
		if (xSelect) if (xColumnIndex !== null) setSelectValue(xSelect, xColumnIndex);
		var ySelects = BX.findChildren(BX('report-chart-params'), {'tag': 'select', attr: {name: /chart_y\[\d+\]/}}, true);
		for (i in ySelects)
		{
			if (match = /\[(\d+)\]/.exec(ySelects[i].name))
			{
				colId = match[1];
				if (colId !== null && yColumnsIndexes[colId] !== null)
					setSelectValue(ySelects[i], yColumnsIndexes[colId]);
			}
		}
		var chartCheckbox = BX('report-chart-display-checkbox');
		if (chartCheckbox)
		{
			BX.bind(chartCheckbox, 'click', function () {
				var chartSwitchBlock = BX('report-chart-switch');
				var chartParamsBlock = BX('report-chart-params');
				if (chartSwitchBlock)
				{
					if (this.checked) BX.addClass(chartSwitchBlock, 'reports-title-filter');
					else BX.removeClass(chartSwitchBlock, 'reports-title-filter');
				}
				if (chartParamsBlock)
				{
					if (this.checked) chartParamsBlock.style.display = '';
					else chartParamsBlock.style.display = 'none';
				}
			});
		}

		// create "report-chart-add-ycolumn" if needed
		chartCreateYColumnSpanAdd();

		// create "report-chart-remove-ycolumn" if needed
		chartCreateYColumnSpanRemove();

		// chart type processing
		BX.bind(BX('report-chart-type'), 'change', chartOnChangeType);

		chartOnChangeType();
	});
	function chartCreateYColumnSpanAdd()
	{
		var lastYcolumnBlock, container, ySelect, blockIndex;
		var addYColumnSpan = BX('report-chart-add-ycolumn');
		if (!addYColumnSpan)
		{
			lastYcolumnBlock = BX('report-chart-last-ycolumn-block');
			if (lastYcolumnBlock)
			{
				ySelect = BX.findChildren(lastYcolumnBlock, {tag: 'select', 'className': 'report-chart-select-col'}, true);
				if (ySelect)
				{
					container = BX.findNextSibling(ySelect[0].parentNode, {tag: 'td'});
					if (container)
					{
						blockIndex = chartGetStringIndex(ySelect[0].id);
						if (blockIndex < <?=CUtil::JSEscape($yColumnsMaxNumber - 1)?>)
						{
							addYColumnSpan = BX.clone(BX('report-chart-add-ycolumn-proto'), true);
							addYColumnSpan.id = 'report-chart-add-ycolumn';
							container.appendChild(addYColumnSpan);
							BX.bind(addYColumnSpan, 'click', chartOnClickAddYcolumn);
						}
					}
				}
			}
		}
	}
	function chartCreateYColumnSpanRemove()
	{
		var lastYcolumnBlock, container, ySelect, blockIndex;
		var removeYColumnSpan = BX('report-chart-remove-ycolumn');
		if (!removeYColumnSpan)
		{
			lastYcolumnBlock = BX('report-chart-last-ycolumn-block');
			if (lastYcolumnBlock)
			{
				ySelect = BX.findChildren(lastYcolumnBlock, {tag: 'select', 'className': 'report-chart-select-col'}, true);
				if (ySelect)
				{
					container = BX.findNextSibling(ySelect[0].parentNode, {tag: 'td'});
					if (container)
					{
						blockIndex = chartGetStringIndex(ySelect[0].id);
						if (blockIndex > 0)
						{
							removeYColumnSpan = BX.clone(BX('report-chart-remove-ycolumn-proto'), true);
							removeYColumnSpan.id = 'report-chart-remove-ycolumn';
							if (blockIndex === <?=CUtil::JSEscape($yColumnsMaxNumber - 1)?>)
								BX.addClass(removeYColumnSpan, 'report-chart-remove-ycolumn-bottom');
							container.appendChild(removeYColumnSpan);
							BX.bind(removeYColumnSpan, 'click', chartOnClickRemoveYcolumn);
						}
					}
				}
			}
		}
	}
	function chartOnClickAddYcolumn()
	{
		var container, block, blockCopy;
		var labelNode, labelText, selectNode, plusSpan, minusSpan;
		var prevLabelNode, prevLabelText;
		var blockIndex = null;
		block = this.parentNode.parentNode;
		if (block)
		{
			container = block.parentNode;
			chartRemoveWithClearId('report-chart-add-ycolumn');
			chartRemoveWithClearId('report-chart-remove-ycolumn');
			blockCopy = BX.clone(block);
			labelNode = BX.findChildren(blockCopy, {tag: 'label', 'className': 'chart-config-label'}, true);
			if (labelNode)
			{
				labelNode[0].setAttribute('for', chartIncStringIndex(labelNode[0].getAttribute('for')));
				selectNode = BX.findChildren(blockCopy, {tag: 'select', 'className': 'report-chart-select-col'}, true);
				if (selectNode)
				{
					blockIndex = chartGetStringIndex(selectNode[0].id) + 1;
					selectNode[0].id = chartIncStringIndex(selectNode[0].id);
					selectNode[0].name = chartIncStringIndex(selectNode[0].name);
				}
			}
			if (blockIndex != null)
			{
				chartSetYColumnLabelText(labelNode[0], true, blockIndex + 1);
				if (blockIndex === 1)
				{
					prevLabelNode = BX.findChildren(block, {tag: 'label', 'className': 'chart-config-label'}, true);
					if (prevLabelNode) chartSetYColumnLabelText(prevLabelNode[0], true, blockIndex);
				}
				BX.removeClass(block, 'report-chart-last-ycolumn-block');
				block.id = null;
				blockCopy.id = 'report-chart-last-ycolumn-block';
				container.appendChild(blockCopy);
				chartCreateYColumnSpanAdd();
				chartCreateYColumnSpanRemove();
			}
		}
	}
	function chartRemoveWithClearId(id)
	{
		if (id)
		{
			var el = BX(id);
			if (el)
			{
				el.id = null;
				BX.remove(el);
			}
		}
	}
	function chartOnClickRemoveYcolumn()
	{
		var container, block, prevBlock;
		var labelNode, labelText, selectNode, blockIndex;
		block = this.parentNode.parentNode;
		prevBlock = BX.findPreviousSibling(block, {tag: block.tagName});
		if (block && prevBlock)
		{
			container = block.parentNode;
			chartRemoveWithClearId('report-chart-add-ycolumn');
			chartRemoveWithClearId('report-chart-remove-ycolumn');
			labelNode = BX.findChildren(prevBlock, {tag: 'label', 'className': 'chart-config-label'}, true);
			if (labelNode)
			{
				selectNode = BX.findChildren(prevBlock, {tag: 'select', 'className': 'report-chart-select-col'}, true);
				if (selectNode) blockIndex = chartGetStringIndex(selectNode[0].id);
			}
			if (blockIndex != null)
			{
				if (blockIndex === 0) chartSetYColumnLabelText(labelNode[0]);
				BX.removeClass(block, 'report-chart-last-ycolumn-block');
				block.id = null;
				BX.remove(block);
				prevBlock.id = 'report-chart-last-ycolumn-block';
				BX.addClass(prevBlock, 'report-chart-last-ycolumn-block');
				chartCreateYColumnSpanAdd();
				chartCreateYColumnSpanRemove();
			}
		}
	}
	function chartGetStringIndex(str)
	{
		var match;
		var index = null;
		newStr = null;
		if (match = /\[(\d+)\]/.exec(str)) index = match[1];
		return parseInt(index);
	}
	function chartIncStringIndex(str)
	{
		var match, newStr;
		newStr = null;
		if (match = /\[(\d+)\]/.exec(str))
		{
			newStr = match.input.substr(0, match.index)+'['+(++match[1])+']'+
				match.input.substr(match.index+match[0].length);
		}
		return newStr;
	}
	function chartGetYColTypes()
	{
		return {<?php
		echo PHP_EOL;
		$i = 0; $li = count($arResult['chartTypes']) - 1;
		foreach ($arResult['chartTypes'] as $chartType)
		{
			echo "\t\t\t'".CUtil::JSEscape($chartType['id']).'\' : [';
			$j = 0; foreach ($chartType['value_types'] as $v) echo (($j++ > 0)?',':'').'\''.CUtil::JSEscape($v).'\'';
			echo ']'.(($i++ < $li)?',':'').PHP_EOL;
		} ?>
		};
	}
	function chartOnChangeType()
	{
		var chartTypeSelect;

		<?php
		// In this call selection lists of columns of values will be reformed.
		// It allows to filter types of sampled columns of values depending on chart type without use of a
		// special function. ?>
		rebuildSortSelect();

		chartTypeSelect = BX('report-chart-type');
		if (chartTypeSelect) chartSetYColumnStyle(chartTypeSelect.value === 'pie');
	}
	function chartSetYColumnStyle(single)
	{
		var labels, index, container, params;
		single = !!single;
		params = BX('report-chart-params');
		if (params)
		{
			labels = BX.findChildren(params, {tag: 'label', attr: {'for': /report-chart-values\[\d\]/}}, true);
			if (labels.length > 1)
			{
				for (var i in labels)
				{
					index = chartGetStringIndex(labels[i].getAttribute('for'));
					if (index === 0) chartSetYColumnLabelText(labels[i], !single, index + 1);
					else
					{
						container = labels[i].parentNode.parentNode;
						if (container) container.style.display = (single) ? 'none' : '';
					}
				}
			}
			else
			{
				container = labels[0].parentNode.parentNode;
				if (container)
				{
					if (single) BX.removeClass(container, 'report-chart-last-ycolumn-block');
					else BX.addClass(container, 'report-chart-last-ycolumn-block');
				}
			}
		}
	}
	function chartSetYColumnLabelText(label, indexed, index)
	{
		var labelText;
		if (!label) return;
		indexed = !!indexed;
		if (indexed && index == null) return;
		index = parseInt(index);
		labelText = '<?=CUtil::JSEscape($yColumnsLabelText)?>' + ((indexed) ? ' (' + index + ')' : '') + ':';
		label.innerHTML = labelText;
	}
	</script>
<?php endif; // if ($arParams['USE_CHART']): ?>

<!-- Sharing -->
<? if(!empty($arResult['SHARING_DATA'])): ?>
<div class="webform-additional-fields">
	<div class="reports-content-block">
		<span class="reports-title-sharing">
			<span class="reports-lable-title"><?=GetMessage('REPORT_SHARING_TITLE')?></span>
			<span id="report-title-sharing-help" class="report-options-help">
				<?=GetMessage('REPORT_SHARING_ILLUSTRATION')?>
			</span>
		</span>
		<div id="report-sharing-block" class="report-sharing-block"></div>
		<div style="display: none;" id="report-sharing-form-data"></div>
	</div>
</div>
<? endif ?>

<!-- save -->
<div class="webform-buttons task-buttons">
	<a class="webform-button webform-button-create" href="" id="report-save-button">
		<span class="webform-button-left"></span>
		<span class="webform-button-text"><?=$arParams['ACTION']=='edit'?GetMessage("REPORT_SAVE_BUTTON"):GetMessage("REPORT_CREATE_BUTTON")?></span>
		<span class="webform-button-right"></span>
	</a>
	<a class="webform-button-link webform-button-link-cancel"
		href="<?=$arParams['ACTION']=='edit'?htmlspecialcharsbx($_SERVER['HTTP_REFERER']):CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_REPORT_LIST"], array());?>">
		<?=GetMessage('REPORT_CANCEL')?>
	</a>
</div>


<!-- preview -->
<div class="reports-preview-table-report" id="reports-preview-table-report">
	<span class="reports-prev-table-title"><?=GetMessage('REPORT_SCHEME_PREVIEW')?></span>

	<div class="reports-list">
		<div class="reports-list-left-corner"></div>
		<div class="reports-list-right-corner"></div>
		<table cellspacing="0" class="reports-list-table">
			<tr>
				<th></th>
			</tr>
		</table>
	</div>
</div>


</div>

</form>




<!-- add select column popup -->
<div class="reports-add_col-popup-cont" id="reports-add_col-popup-cont" style="display:none;">
	<div class="reports-add_col-popup-title"><?=GetMessage('REPORT_POPUP_COLUMN_TITLE')?></div>
	<div class="popup-window-hr popup-window-buttons-hr"><i></i></div>
	<div class="reports-add_col-popup">
		<?=call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'buildHTMLSelectTreePopup'), $arResult['fieldsTree'])?>
	</div>
</div>

<!-- choose filter column popup -->
<div class="reports-add_col-popup-cont reports-add_filcol-popup-cont" id="reports-add_filcol-popup-cont" style="display:none;">
	<div class="reports-add_col-popup-title"><?=GetMessage('REPORT_POPUP_FILTER_TITLE')?></div>
	<div class="popup-window-hr popup-window-buttons-hr"><i></i></div>
	<div class="reports-add_col-popup">
		<?=call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'buildHTMLSelectTreePopup'), $arResult['fieldsTree'], true)?>
	</div>
</div>

<!-- percent view examples -->
<div id="report-select-prcnt-examples" style="display: none">
	<select class="reports-add-col-select-prcnt" style="margin-right: 4px; margin-left: 4px; display: none;" disabled>
		<option value="self_column"><?=GetMessage('REPORT_PRCNT_BY_COLUMN')?></option>
		<option value="other_field"><?=GetMessage('REPORT_PRCNT_BY_FIELD')?></option>
	</select>
	<select	class="reports-add-col-select-prcnt-by" style="display: none;" disabled>
	</select>
</div>

<!-- select calc examples -->
<div id="report-select-calc-examples" style="display: none">
	<? foreach($arResult['calcVariations'] as $key => $values): ?>
		<select id="report-select-calc-<?=$key?>" disabled>
		<? foreach ($values as $v): ?>
			<option value="<?=$v?>"><?=GetMessage('REPORT_SELECT_CALC_VAR_'.$v)?></option>
		<? endforeach; ?>
		</select>
	<? endforeach; ?>
</div>

<!-- filter compare examples -->
<div id="report-filter-compare-examples" style="display: none">
	<? foreach($arResult['compareVariations'] as $key => $values): ?>
		<select id="report-filter-compare-<?=$key?>" class="reports-filter-select report-filter-compare-<?=$key?>">
		<? foreach ($values as $v): ?>
			<option value="<?=$v?>"><?=GetMessage('REPORT_FILTER_COMPARE_VAR_'.$v)?></option>
		<? endforeach; ?>
		</select>
	<? endforeach; ?>
</div>

<!-- filter value control examples -->
<div id="report-filter-value-control-examples" style="display: none">

	<span name="report-filter-value-control-integer" class="report-filter-vcc">
		<input class="reports-filter-input" type="text" name="value" />
	</span>

	<span name="report-filter-value-control-float" class="report-filter-vcc">
		<input class="reports-filter-input" type="text" name="value" />
	</span>

	<span name="report-filter-value-control-string" class="report-filter-vcc">
		<input class="reports-filter-input" type="text" name="value" />
	</span>

	<span name="report-filter-value-control-text" class="report-filter-vcc">
		<input class="reports-filter-input" type="text" name="value" />
	</span>

	<span name="report-filter-value-control-boolean" class="report-filter-vcc">
		<select class="reports-filter-select-small" name="value">
			<option value=""><?=GetMessage('REPORT_IGNORE_FILTER_VALUE')?></option>
			<option value="true"><?=GetMessage('REPORT_BOOLEAN_VALUE_TRUE')?></option>
			<option value="false"><?=GetMessage('REPORT_BOOLEAN_VALUE_FALSE')?></option>
		</select>
	</span>

	<span name="report-filter-value-control-datetime" class="report-filter-calendar">
		<input type="text" class="reports-filter-input" name="value" /><img alt="img" class="reports-filt-calen-img" src="/bitrix/js/main/core/images/calendar-icon.gif" />
	</span>

	<span name="report-filter-value-control-employee" callback="RTFilter_chooseUser">
		<a href="" class="report-select-popup-link" caller="true"><?=GetMessage('REPORT_CHOOSE')?></a>
		<input type="hidden" name="value" />
	</span>
	<span name="report-filter-value-control-\Bitrix\Main\User" callback="RTFilter_chooseUser">
		<a href="" class="report-select-popup-link" caller="true"><?=GetMessage('REPORT_CHOOSE')?></a>
		<input type="hidden" name="value" />
	</span>
	<script type="text/javascript">
		var RTFilter_chooseUser_LAST_CALLER;
		function RTFilter_chooseUser(span)
		{
			var a = BX.findChild(span, {tag:'a'});

			BX.bind(a, 'click', ShowSingleSelector);
			BX.bind(a, 'click', function(e){
				RTFilter_chooseUser_LAST_CALLER = this;
			});
		}

		function RTFilter_chooseUserCatch(user)
		{
			var userContainer = RTFilter_chooseUser_LAST_CALLER.parentNode;

			if (parseInt(user.id) > 0)
			{
				BX.findChild(userContainer, {tag:'a'}).innerHTML = user.name;
			}

			BX.addClass(BX.findChild(userContainer, {tag:'a'}), 'report-select-popup-link-active');
			BX.findChild(userContainer, {attr:{name:'value'}}).value = user.id;

			try
			{
				singlePopup.close();
			}
			catch (e) {}

		}
	</script>

	<span name="report-filter-value-control-\Bitrix\Socialnetwork\Workgroup" callback="RTFilter_chooseGroup">
		<a href="" class="report-select-popup-link" caller="true"><?=GetMessage('REPORT_CHOOSE')?></a>
		<input type="hidden" name="value" />
	</span>
	<script type="text/javascript">
		var RTFilter_chooseGroup_LAST_CALLER;
		function RTFilter_chooseGroup(span)
		{
			var a = BX.findChild(span, {tag:'a'});
			BX.bind(a, 'click', function(e){
				BX.PreventDefault(e);
				groupsPopup.popupWindow.setBindElement(this);
				groupsPopup.show();
				RTFilter_chooseGroup_LAST_CALLER = this;
			});
		}

		function RTFilter_chooseGroupCatch(group)
		{
			if (group.length < 1) return;

			group = group[0];
			var groupContainer = RTFilter_chooseGroup_LAST_CALLER.parentNode;

			if (parseInt(group.id) > 0)
			{
				BX.findChild(groupContainer, {tag:'a'}).innerHTML = group.title;
			}

			BX.addClass(BX.findChild(groupContainer, {tag:'a'}), 'report-select-popup-link-active');
			BX.findChild(groupContainer, {attr:{name:'value'}}).value = group.id;

			try
			{
				groupsPopup.popupWindow.close();
			}
			catch (e) {}
		}
	</script>

</div>

<!-- filter value control examples for UF enumerations -->
<div id="report-filter-value-control-examples-ufenums" style="display: none">
	<?
	if (is_array($arResult['ufEnumerations'])):
		foreach ($arResult['ufEnumerations'] as $ufId => $enums):
			foreach ($enums as $fieldKey => $enum):
	?>
	<span name="report-filter-value-control-<?=($ufId.'_'.$fieldKey)?>" class="report-filter-vcc">
		<select class="reports-filter-select-small" name="value">
			<option value=""><?=GetMessage('REPORT_IGNORE_FILTER_VALUE')?></option>
	<?
				foreach ($enum as $itemId => $itemInfo):
	?>
			<option value="<?=$itemId?>"><?=$itemInfo['VALUE']?></option>
	<?
				endforeach;
	?>
		</select>
	</span>
	<?
			endforeach;
		endforeach;
	endif;
	?>
</div>

<!-- user selector -->

<script type="text/javascript">

function ShowSingleSelector(e) {

	if(!e) e = window.event;

	//if (!singlePopup)
	{
		singlePopup = BX.PopupWindowManager.create("single-employee-popup-"+Math.random(), this, {
			offsetTop : 1,
			autoHide : true,
			content : BX("Single_selector_content")
		});
	}

	if (singlePopup.popupContainer.style.display != "block")
	{
		singlePopup.show();
	}

	BX.PreventDefault(e);
}



</script>

<?php
$name = $APPLICATION->IncludeComponent(
		"bitrix:intranet.user.selector.new", ".default", array(
			"MULTIPLE" => "N",
			"NAME" => "Single",
			"VALUE" => 1,
			"POPUP" => "Y",
			"ON_SELECT" => "RTFilter_chooseUserCatch",
			"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER_PROFILE"],
			"SITE_ID" => SITE_ID,
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
		), null, array("HIDE_ICONS" => "Y")
	);

?>


<!-- group selector -->

<?php

$name = $APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.group.selector", ".default", array(
		"ON_SELECT" => "RTFilter_chooseGroupCatch", //callback
	), null, array("HIDE_ICONS" => "Y")
);

?>

<!-- Connection js class -->
<script type="text/javascript">
	BX(function () {

		BX.Report['<?=$jsClass?>'] = new BX.Report.ReportConstructClass({
			jsClass:'<?=$jsClass?>',
			sharingData: <?= Bitrix\Main\Web\Json::encode($arResult['SHARING_DATA']) ?>,
			sessionError: '<?= !empty($_SESSION['REPORT_LIST_ERROR']) ? true : false ?>'
		});

	});
</script>

<?if(!defined('REPORT_LIST_ERROR') && !empty($_SESSION['REPORT_LIST_ERROR'])):?>
	<? define("REPORT_LIST_ERROR", true); ?>
	<div id="report-list-error" style="display: none;"><?=$_SESSION['REPORT_LIST_ERROR']?></div>
	<? unset($_SESSION['REPORT_LIST_ERROR']); ?>
<? endif ?>

<?php $this->SetViewTarget("pagetitle", 100);?>
	<? if($arParams['REPORT_ID'] && false): ?>
	<a class="webform-small-button webform-small-button-blue"
		onclick="BX.Report['<?=$jsClass?>'].export('<?=$arParams['REPORT_ID']?>')">
		<span class="webform-small-button-text"><?=GetMessage('REPORT_TITLE_EXPORT')?></span>
	</a>
	&nbsp;
	<? endif ?>
	<a class="webform-small-button webform-small-button-blue webform-small-button-back"
		href="<?=CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_REPORT_LIST"], array());?>">
		<span class="webform-small-button-icon"></span>
		<span class="webform-small-button-text"><?=GetMessage('REPORT_RETURN_TO_LIST')?></span>
	</a>
<?php $this->EndViewTarget();?>