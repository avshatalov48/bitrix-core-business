<?php

	if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

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

	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/js/report/css/report.css');
	$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/report/construct.js');

	CJSCore::Init(array('date', 'access'));

?>

<!-- Redefinition of some styles -->
<style type="text/css">
	#sale-report-construct-buttons-block { padding-top: 18px; }
	.reports-filter-quan-item {height: 30px;}
	.reports-filter-quan-item .reports-checkbox { margin: 0;}
	.reports-filter-block { width: 800px; }
	.report-filter-calendar { margin-left: 4px; }
	.reports-forming-column { height: 38px; padding-top: 0px; }
	.reports-add-col-button-down,
	.reports-add-col-button-up,
	.reports-checkbox { vertical-align: middle; margin-top: 8px; padding: 0;}

	/* grouping */
	.reports-grouping-checkbox,
	.reports-grouping-subtotal-checkbox {
		display:inline-block; margin-right:6px; position:relative; vertical-align:middle;
	}
	.reports-grouping-header { padding: 0 0 15px 42px; font-size: 0px; position: relative; }
	.reports-image-arrow-bottom1 {
				background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEU\
gAAAAcAAAAVCAIAAAAFLmMUAAAABnRSTlMA/wD/AP83WBt9AAAARElEQVQYle2RwQ3AMAgDQ8Rkt\
zes5j5QaaREygK918kyH2OSxoa3ZWYJ4GsFKJn7+Z9eUpPUyxbAHMusn+slItrt+LcH3bElmA5AL\
PgAAAAASUVORK5CYII=") no-repeat scroll 0 0 transparent;
	}
	.reports-image-arrow-bottom2 {
				background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEU\
gAAAIEAAAAVCAIAAADuNRpSAAAABnRSTlMA/wD/AP83WBt9AAAAe0lEQVRYhe3ZQQqAMAwAwVZ8W\
R7er8VDpVIfkBXcOfVYuhAh9sxsQp30BX5qjDEPEWEDTETMw8HeQ80GX2ADng14NuDZgGcDng14N\
uDZgPfsKtYGQ8W2fdHaYKiSs4hnA17PzNeXwIlU7Gj7oxug3j2L5tMbANH9n4y7AHQnFgWuXmHqA\
AAAAElFTkSuQmCC") no-repeat scroll 0 0 transparent;
	}
	.reports-checkbox-g-arrow-bottom {
		display: block; height: 21px; width: 7px; position: absolute; bottom: 0; left: 32px;
	}
	.reports-checkbox-gs-arrow-bottom {
		display: block; height: 21px; width: 129px; position: absolute; bottom: 0; left: 51px;
	}
	.reports-grouping-header-title {
		color:#a6a6a6;
		line-height:11px;
		font-size:11px;
		width: 130px;
		display: inline-block;
		position: relative;
		margin-right: 10px;
	}

	/*.reports-filter-item { height: 30px; }*/
	.reports-filter-butt-wrap { top: 3px; }
	.reports-filter-butt-wrap .reports-checkbox { margin-top: 0px; }
	.reports-filter-item-name { width: 160px; vertical-align: top; margin-top: 1px; }
	.reports-filter-item select { vertical-align: top; }
	.reports-sort-column { margin-bottom: 15px; }
	.reports-add-col-title { height: 16px; padding-top: 3px; }
	.reports-add-col-tit-prcnt,
	.reports-add-col-tit-edit,
	.reports-add-col-tit-prcnt-close,
	.reports-add-col-tit-remove { top: 4px; }
	.reports-add-col-title { height: 32px; }
	.reports-add-col-select.reports-add-col-select-calc { position: relative; top: -5px; }
	.reports-add-col-title { top: 10px; }
	.reports-add-col-tit-text { margin-right: 5px; }
	.reports-add-col-input input { position: relative; top: -5px; }
	.reports-add-col-inp-title { top: -17px; }
	.reports-add-col-select-prcnt, .reports-add-col-select-prcnt-by { position: relative; top: -5px; }
	.reports-limit-res-select-lable { margin-right: 5px; margin-left: 5px; }


	table.edit-table div {font-size:14px;}
	.reports-list-table th {
		font: 14px Arial,Helvetica,sans-serif;
	}

	.filter-field-date-combobox .filter-date-interval{display:none;}
	.filter-field-date-combobox span.filter-date-interval-hellip{display:none;}
	.filter-field-date-combobox .filter-date-interval-after{display:inline;}
	.filter-field-date-combobox .filter-date-interval-before{display:inline;}
	.filter-field-date-combobox .filter-date-interval-after.filter-date-interval-before{display:block;margin-top:0.5em;}
	.filter-field-date-combobox .filter-date-interval-after.filter-date-interval-before span.filter-date-interval-hellip{display:inline-block;margin:0;}
	.filter-field-date-combobox .filter-date-interval-to{display:none;}
	.filter-field-date-combobox .filter-date-interval-from{display:none;}
	.filter-field-date-combobox .filter-date-interval-after .filter-date-interval-to{display:inline;}
	.filter-field-date-combobox .filter-date-interval-before .filter-date-interval-from{display:inline;}
	.filter-field-date-combobox .filter-day-interval {display:none;}
	.filter-field-date-combobox .filter-day-interval-selected {display:inline;}
	.webform-content {
		padding: 7px 20px 15px 16px;
	}
	.pagetitle-wrap {
		margin: 0px -3px 0px -1px;
		min-height: 30px;
		padding: 0px 0px 4px 4px;
		position: relative;
	}
	.pagetitle {
		color: rgb(85, 85, 85);
		font-size: 30px;
		margin: -2px 0px 0px;
		padding: 0px;
		font-weight: normal;
		text-shadow: 0px 1px 0px rgb(255, 255, 255);
	}
	.pagetitle-menu {
		right: 5px;
		top: 0px;
		position: absolute;
		z-index: 2;
	}

	.adm-filter-box-sizing { width: auto; }
	.reports-title-label { padding-top: 20px; }
	#bx-admin-prefix .popup-window-close-icon { background-color: inherit; right: 0; top: 0; }

	.report-period-hidden {
		margin-top: 10px;
	}
	.report-period-hidden > input[type=checkbox] { margin: 0; }
</style>

<script type="text/javascript">

	initReportControls();
	BX.message({'REPORT_DEFAULT_TITLE': '<?=CUtil::JSEscape(GetMessage('REPORT_DEFAULT_TITLE'))?>'});
	BX.message({'REPORT_ADD': '<?=CUtil::JSEscape(GetMessage('REPORT_ADD'))?>'});
	BX.message({'REPORT_CANCEL': '<?=CUtil::JSEscape(GetMessage('REPORT_CANCEL'))?>'});
	BX.message({'REPORT_PRCNT_VIEW_IS_NOT_AVAILABLE': '<?=CUtil::JSEscape(GetMessage('REPORT_PRCNT_VIEW_IS_NOT_AVAILABLE'))?>'});
	BX.message({'REPORT_PRCNT_BUTTON_TITLE': '<?=CUtil::JSEscape(GetMessage('REPORT_PRCNT_BUTTON_TITLE'))?>'});

</script>

<!-- The form is defined in a body of administrative page -->
<?php echo bitrix_sessid_post('csrf_token')?>

<div class="reports-constructor">

<div class="adm-filter-wrap">
	<?
		$_title = '';
		if (!empty($arResult['report']['TITLE'])) $_title = $arResult['report']['TITLE'];
	?>
	<div class="adm-input-wrap">
		<div class="reports-title-label"><?=GetMessage('REPORT_TITLE')?></div>

		<input style="padding-left: 5px; padding-right: 5px;" class="adm-input" type="text" id="reports-new-title" name="report_title" value="<?=htmlspecialcharsbx($_title)?>" />

		<div class="reports-title-label"><?=GetMessage('REPORT_DESCRIPTION')?></div>

		<div style="padding-left: 0px; padding-right: 10px;">
			<textarea rows="5" style="padding-left: 5px; padding-right: 5px; width: 100%;" name="report_description"><?=htmlspecialcharsbx($arResult['report']['DESCRIPTION'])?></textarea>
		</div>
	</div>



	<?php /*    Site option    */ ?>
	<? if (isset($arParams['F_SALE_SITE'])): ?>
	<div class="reports-title-label"><?=GetMessage('SALE_REPORT_SITE')?></div>

	<div class="adm-filter-alignment adm-calendar-block">
		<div class="adm-filter-box-sizing">
			<?php
			$selected = $arParams['F_SALE_SITE'];
			$siteList = call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getSiteList'));
			?>
			<span class="adm-select-wrap">
				<select class="adm-select" id="sale-site-filter" name="F_SALE_SITE">
					<? foreach($siteList as $kLID => $vSiteName): ?>
					<option <?php
								if ($kLID==$selected) echo 'selected="1"';
								?>value="<?=htmlspecialcharsbx($kLID)?>"><?=htmlspecialcharsbx($vSiteName)?></option>
					<? endforeach; ?>
				</select>
			</span>
		</div>
	</div>
	<? endif; ?>



	<div class="reports-title-label"><?=GetMessage('REPORT_PERIOD')?></div>

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
	<!-- stub -->
	<div style="display: none;">
		<select id="task-interval-filter">
			<option value="" selected></option>
		</select>
		<span class="filter-date-interval"></span>
		<span class="filter-day-interval">	</span>
	</div>

	<!-- period -->
	<div class="adm-filter-alignment adm-calendar-block">
		<div class="adm-filter-box-sizing">
			<!-- period select -->
			<span class="adm-select-wrap adm-calendar-period">
				<select onchange="OnReportIntervalChange(this)" name="F_DATE_TYPE"
						id="report-interval-filter" class="adm-select adm-calendar-period">
					<?php foreach($arResult['periodTypes'] as $key):?>
					<option value="<?=htmlspecialcharsbx($key)?>"<?=($key == $arResult["preSettings"]["period"]['type']) ? ' selected' : ''?>><?=GetMessage('REPORT_CALEND_'.ToUpper(htmlspecialcharsbx($key)))?></option>
					<?php endforeach;?>
				</select>
			</span>
			<!-- filter date from -->
			<div style="display: none;" class="adm-input-wrap adm-calendar-inp adm-calendar-first">
				<input type="text" value="<?=$_date_from?>" size="10" name="F_DATE_FROM"
					id="REPORT_INTERVAL_F_DATE_FROM" class="adm-input adm-calendar-from">
				<img onclick="BX.calendar({node:this, field:'REPORT_INTERVAL_F_DATE_FROM', form: '', bTime: false, bHideTime: false});"
							title="<?php echo GetMessage("TASKS_PICK_DATE")?>" class="adm-calendar-icon">
			</div>
			<!-- filter separator -->
			<span style="display: none;" class="adm-calendar-separate"></span>
			<!-- filter date to -->
			<div style="display: none;" class="adm-input-wrap adm-calendar-second">
				<input type="text" value="<?=$_date_to?>" size="10" name="F_DATE_TO"
					id="REPORT_INTERVAL_F_DATE_TO" class="adm-input adm-calendar-to">
				<img onclick="BX.calendar({node:this, field:'REPORT_INTERVAL_F_DATE_TO', form: '', bTime: false, bHideTime: false});"
					title="<?php echo GetMessage("TASKS_PICK_DATE")?>" class="adm-calendar-icon">
			</div>
			<!-- days field -->
			<div style="display: none;" class="adm-input-wrap filter-day-interval">
				<span class="<?php if ($arResult["preSettings"]["period"]['type'] == "days"): ?>filter-day-interval-selected<?php endif; ?>">
					<input type="text" size="5" class="filter-date-days"
						value="<?php echo $arResult["preSettings"]["period"]['type'] == "days" ? $arResult["preSettings"]["period"]['value'] : ""?>"
						name="F_DATE_DAYS"/>
				</span>
				<span> <?php echo GetMessage("REPORT_CALEND_REPORT_DAYS")?></span>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		function OnReportIntervalChange(select)
		{
			var filterSelectContainer = BX.findParent(select);
			var filterDateFrom = BX.findNextSibling(filterSelectContainer, {'tag':'div', 'className': 'adm-calendar-first'});
			var filterDateSeparator = BX.findNextSibling(filterDateFrom, {'tag':'span', 'className': 'adm-calendar-separate'});
			var filterDateTo = BX.findNextSibling(filterDateSeparator, {'tag':'div', 'className': 'adm-calendar-second'});
			var filterDays = BX.findNextSibling(filterDateTo, {'tag':'div', 'className': 'filter-day-interval'});

			filterDateFrom.style.display = 'none';
			filterDateSeparator.style.display = 'none';
			filterDateTo.style.display = 'none';
			filterDays.style.display = 'none';

			if (select.value == "interval")
			{
				filterDateFrom.style.display = 'inline-block';
				filterDateSeparator.style.display = 'inline-block';
				filterDateTo.style.display = 'inline-block';
			}
			else if(select.value == "before") filterDateTo.style.display = 'inline-block';
			else if(select.value == "after") filterDateFrom.style.display = 'inline-block';
			else if(select.value == "days") filterDays.style.display = 'inline-block';
		}

		BX.ready(function() {
			OnReportIntervalChange(BX('report-interval-filter'));
		});
	</script>
	<div class="report-period-hidden">
		<input type="hidden" name="period_hidden" value="N">
		<input type="checkbox" <?=($arResult['preSettings']['period']['hidden'] === 'Y')?'checked="checked" ':''?>
			class="reports-checkbox" id="report-period-hidden-checkbox" name="period_hidden" value="Y" />
		<span class="reports-limit-res-select-lable">
			<label for="report-period-hidden-checkbox"><?=GetMessage('REPORT_PERIOD_HIDDEN')?></label>
		</span>
	</div>
</div>

<!-- select -->
<div class="reports-content-block-disabled-style" id='report_columns_list'>
	<span class="reports-content-block-title"><?=GetMessage('REPORT_COLUMNS')?></span>
	<div class="reports-add-columns-block" id="reports-add-columns-block">

		<div class="reports-grouping-header">
			<span class="reports-checkbox-g-arrow-bottom reports-image-arrow-bottom1"></span>
			<span class="reports-grouping-header-title"><?=GetMessage('REPORT_GROUP_RESULT_BY_COLUMN')?></span>
			<span class="reports-checkbox-gs-arrow-bottom reports-image-arrow-bottom2"></span>
			<span class="reports-grouping-header-title"><?=GetMessage('REPORT_GROUP_RESULT_SUBTOTAL')?></span>
		</div>

		<div id="reports-forming-column-example" style="display: none">
			<input type="hidden" name="report_select_columns[%s][name]" />
			<span class="reports-add-col-checkbox">
				<input type="checkbox" class="reports-checkbox"/>
			</span><span class="reports-grouping-checkbox">
				<input type="checkbox" class="reports-checkbox" />
			</span><span class="reports-grouping-subtotal-checkbox">
				<input type="checkbox" class="reports-checkbox" />
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

	<?php /*   use columns selection of price types   */ ?>
	<?php if (call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getOwnerId')) === 'sale_SaleProduct'): ?>
	<div class="reports-filter-quan-item">
		<input type="checkbox" <?=($arResult['preSettings']['helper_spec']['ucspt'] === true)?'checked="checked" ':''?>
			class="reports-checkbox" id="report-helper-spec-ucspt" name="helper_spec_ucspt" />
			<span class="reports-limit-res-select-lable">
				<label for="report-helper-spec-ucspt"><?=GetMessage('REPORT_HELPER_SPEC_UCSPT')?></label>
			</span>
	</div>
	<?php endif; ?>

	<div class="reports-sort-column">
		<span class="reports-content-block-title"><?=GetMessage('REPORT_SORT_BY_SELECT_COLUMN')?></span><select class="reports-sort-select" id="reports-sort-select" name="reports_sort_select"><option value="_">_</option></select>
		<select name="reports_sort_type_select" id="reports-sort-type-select" class="reports-sort-type-select"><option value="ASC"><?=GetMessage('REPORT_SORT_TYPE_ASC')?></option><option value="DESC"><?=GetMessage('REPORT_SORT_TYPE_DESC')?></option></select>
	</div>

	<script type="text/javascript">

		BX.ready(function() {

			GLOBAL_REPORT_SELECT_COLUMN_COUNT = 0;

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
	<div class="reports-content-block-disabled-style">
		<span class="reports-content-block-title reports-title-filter"><?=GetMessage('REPORT_FILTER')?></span>

		<div class="reports-filter-block">

			<div class="reports-limit-results" id="reports-filter-base-andor-selector">
				<span class="reports-limit-res-select-lable"><?=GetMessage('REPORT_RESULT_LIMIT_BY')?></span><select filterId="0" class="reports-limit-res-select">
				<option value="AND"><?=GetMessage('REPORT_ANDOR_AND')?></option>
				<option value="OR"><?=GetMessage('REPORT_ANDOR_OR')?></option>
			</select><span class="reports-limit-res-select-lable" id="reports-filter-base-andor-selector-text-and"><?=GetMessage('REPORT_RESULT_LIMIT_CONDITIONS')?></span><span class="reports-limit-res-select-lable" id="reports-filter-base-andor-selector-text-or" style="display: none;"><?=GetMessage('REPORT_RESULT_LIMIT_CONDITION')?></span>
			</div>

			<div id="reports-filter-item-example" style="display: none;">
				<span class="reports-filter-item-name"><span class="reports-dashed"><?=GetMessage('REPORT_CHOOSE_FIELD')?></span></span><span class="reports-filter-butt-wrap"><span class="reports-filter-del-item"><i></i></span><span
				class="reports-filter-add-item"><i></i></span><span
				class="reports-filter-and-or"><span class="reports-filter-and-or-text"><?=GetMessage('REPORT_ANDOR')?></span><i></i></span><input type="checkbox" class="reports-checkbox" name="changeable" checked /></span>
			</div>

			<div id="reports-filter-andor-container-example" style="display: none" class="reports-filter-andor-container">
				<div class="reports-filter-andor-item">
					<select style="width: 80px;">
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

			var GLOBAL_REPORT_FILTER_COUNT = 1;
			var GLOBAL_REPORT_GROUPING_COLUMNS_COUNT = 0;
			var GLOBAL_PRE_FILTERS = null;

			<? if (!empty($arResult["preSettings"]["filter"])): ?>
				var GLOBAL_PRE_FILTERS = <?=CUtil::PhpToJSObject($arResult["preSettings"]["filter"])?>;
			<? endif; ?>

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

		<div class="reports-filter-quan-item">
			<input type="checkbox" <?=($arResult['preSettings']['red_neg_vals'] === true)?'checked="checked" ':''?>
				class="reports-checkbox" id="report-negative-values-red-checkbox" name="report_red_neg_vals"/>
			<span class="reports-limit-res-select-lable">
				<label for="report-negative-values-red-checkbox"><?=GetMessage('REPORT_NEGATIVE_VALUES_RED')?></label>
			</span>
		</div>

		<div class="reports-filter-quan-item" style="margin-top: 10px;">
			<input type="checkbox" <?=($arResult['preSettings']['mobile']['enabled'] === true)?'checked="checked" ':''?>
				class="reports-checkbox" id="report-mobile-settings" name="report_mobile_enabled"/>
			<span class="reports-limit-res-select-lable">
				<label for="report-mobile-settings"><?=GetMessage('REPORT_MOBILE_SETTINGS')?></label>
			</span>
		</div>
	</div>
	<div class="webform-corners-bottom">
		<div class="webform-left-corner"></div>
		<div class="webform-right-corner"></div>
	</div>
</div>

<!-- preview -->
<div class="reports-preview-table-report" id="reports-preview-table-report">
	<span class="reports-prev-table-title"><?=GetMessage('REPORT_SCHEME_PREVIEW')?></span>

	<div class="reports-list">
		<div class="reports-list-left-corner"></div>
		<div class="reports-list-right-corner"></div>
		<table cellspacing="0" class="report-list-table">
			<tr>
				<th></th>
			</tr>
		</table>
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

	#report-chart-config {padding-top: 20px;}
	#report-chart-switch {margin: 12px 0 12px 0;}
	#report-chart-params div {margin-bottom: 20px; padding: 4px 0;}
	#report-chart-display-checkbox {vertical-align: middle;}
	#report-chart-switch label {vertical-align: middle;}
</style>
<script type="text/javascript">
	var GLOBAL_BX_REPORT_USING_CHARTS = true;
</script>
<?php $fDisplayChart = $arResult['preSettings']['chart']['display']; ?>
<div id="report-chart-config">
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
			<span><label class="reports-title-label" for="report-chart-type"><?php
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
		<td><label class="reports-title-label" for="report-chart-args"><?php
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
			<label class="reports-title-label"<?php echo ' for="report-chart-values['.$i.']"'; ?>><?php
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
			labelNode = BX.findChildren(blockCopy, {tag: 'label', 'className': 'reports-title-label'}, true);
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
					prevLabelNode = BX.findChildren(block, {tag: 'label', 'className': 'reports-title-label'}, true);
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
		prevBlock = BX.findPreviousSibling(block, {tag: block.tagName})
		if (block && prevBlock)
		{
			container = block.parentNode;
			chartRemoveWithClearId('report-chart-add-ycolumn');
			chartRemoveWithClearId('report-chart-remove-ycolumn');
			labelNode = BX.findChildren(prevBlock, {tag: 'label', 'className': 'reports-title-label'}, true);
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
			labels = BX.findChildren(params, {tag: 'label', attr: {for: /report-chart-values\[\d\]/}}, true);
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

</div>

<!-- add select column popup -->
<div class="reports-add_col-popup-cont" id="reports-add_col-popup-cont" style="display:none;">
	<div class="reports-add_col-popup-title">
		<?=GetMessage('REPORT_POPUP_COLUMN_TITLE'.'_'.call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getOwnerId')))?>
	</div>
	<div class="popup-window-hr popup-window-buttons-hr"><i></i></div>
	<div class="reports-add_col-popup">
		<?=call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'buildHTMLSelectTreePopup'), $arResult['fieldsTree'])?>
	</div>
</div>

<!-- choose filter column popup --><?php
$refChooseParam = call_user_func([$arParams['REPORT_HELPER_CLASS'], 'getFiltrableColumnGroups']);
if (!is_array($refChooseParam) || empty($refChooseParam))
{
	$refChooseParam = true;
}
?>
<div class="reports-add_col-popup-cont reports-add_filcol-popup-cont" id="reports-add_filcol-popup-cont" style="display:none;">
	<div class="reports-add_col-popup-title">
		<?=GetMessage('REPORT_POPUP_FILTER_TITLE'.'_'.call_user_func(array($arParams['REPORT_HELPER_CLASS'], 'getOwnerId')))?>
	</div>
	<div class="popup-window-hr popup-window-buttons-hr"><i></i></div>
	<div class="reports-add_col-popup">
		<?php echo call_user_func(
			[$arParams['REPORT_HELPER_CLASS'], 'buildHTMLSelectTreePopup'],
			$arResult['fieldsTree'],
			$refChooseParam
		); ?>
	</div>
</div>

<!-- percent view examples -->
<div id="report-select-prcnt-examples" style="display: none">
	<select class="reports-add-col-select-prcnt" style="margin-left: 4px; display: none;" disabled>
		<option value="self_column"><?=GetMessage('REPORT_PRCNT_BY_COLUMN')?></option>
		<option value="other_field"><?=GetMessage('REPORT_PRCNT_BY_FIELD')?></option>
	</select>
	<select	class="reports-add-col-select-prcnt-by" style="margin-left: 4px; display: none;" disabled>
	</select>
</div>

<!-- select calc examples -->
<div id="report-select-calc-examples" style="display: none">
	<? foreach($arResult['calcVariations'] as $key => $values): ?>
	<select id="report-select-calc-<?=$key?>" disabled>
		<? foreach ($values as $v): ?>
		<option value="<?=htmlspecialcharsbx($v)?>"><?=GetMessage('REPORT_SELECT_CALC_VAR_'.htmlspecialcharsbx($v))?></option>
		<? endforeach; ?>
	</select>
	<? endforeach; ?>
</div>

<!-- filter compare examples -->
<div id="report-filter-compare-examples" style="display: none">
	<? foreach($arResult['compareVariations'] as $key => $values): ?>
	<select id="report-filter-compare-<?=htmlspecialcharsbx($key)?>" class="report-filter-compare-<?=htmlspecialcharsbx($key)?>">
		<? foreach ($values as $v): ?>
		<option value="<?=$v?>"><?=GetMessage('REPORT_FILTER_COMPARE_VAR_'.$v)?></option>
		<? endforeach; ?>
	</select>
	<? endforeach; ?>
</div>

<!-- filter value control examples -->
<div id="report-filter-value-control-examples" style="display: none">

	<span name="report-filter-value-control-integer">
		<input type="text" name="value" />
	</span>

	<span name="report-filter-value-control-float">
		<input type="text" name="value" />
	</span>

	<span name="report-filter-value-control-string">
		<input type="text" name="value" />
	</span>

	<span name="report-filter-value-control-boolean">
		<select class="report-filter-select" name="value">
			<option value=""><?=GetMessage('REPORT_IGNORE_FILTER_VALUE')?></option>
			<option value="true"><?=GetMessage('REPORT_BOOLEAN_VALUE_TRUE')?></option>
			<option value="false"><?=GetMessage('REPORT_BOOLEAN_VALUE_FALSE')?></option>
		</select>
	</span>

	<span name="report-filter-value-control-datetime" class="report-filter-calendar">
		<input type="text" class="reports-filter-input" name="value" /><img alt="img" class="reports-filt-calen-img" src="/bitrix/js/main/core/images/calendar-icon.gif" />
	</span>

</div>
