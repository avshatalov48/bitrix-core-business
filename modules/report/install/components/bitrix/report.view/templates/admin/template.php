<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult['ERROR']))
{
	echo $arResult['ERROR'];
	return false;
}

// calendar
CJSCore::Init(array('date','access'));

$isPeriodHidden = isset($arResult['settings']['period']['hidden']) && $arResult['settings']['period']['hidden'] === 'Y';

$arPeriodTypes = array(
	"month" => GetMessage("TASKS_THIS_MONTH"),
	"month_ago" => GetMessage("TASKS_PREVIOUS_MONTH"),
	"week" => GetMessage("TASKS_THIS_WEEK"),
	"week_ago" => GetMessage("TASKS_PREVIOUS_WEEK"),
	"days" => GetMessage("TASKS_LAST_N_DAYS"),
	"after" => GetMessage("TASKS_AFTER"),
	"before" => GetMessage("TASKS_BEFORE"),
	"interval" => GetMessage("TASKS_DATE_INTERVAL"),
	"all" => GetMessage("TASKS_DATE_ALL")
);

// <editor-fold defaultstate="collapsed" desc="Area for the upper buttons">

$aMenu = array(
	array(
		"TEXT" => GetMessage("REPORT_RETURN_TO_LIST"),
		"LINK" => $arParams["PATH_TO_REPORT_LIST"],
		"ICON"=>"btn_list",
	),
	array(
		"TEXT" => GetMessage("REPORT_EXCEL_EXPORT"),
		"LINK" => $APPLICATION->GetCurPageParam("EXCEL=Y"),
	)
);
if ($arResult['SHOW_EDIT_BUTTON'] == false)
{
	// do nothing
}
else if ($arResult['MARK_DEFAULT'] > 0)
{
	$aMenu[] = array(
		"TEXT" => GetMessage("REPORT_COPY"),
		"LINK" => $arParams["PATH_TO_REPORT_CONSTRUCT"].'&copyID='.$arParams['REPORT_ID'],
		"ICON"=>"btn_copy"
	);
}
else
{
	$aMenu[] = array(
		"TEXT" => GetMessage("REPORT_EDIT"),
		"LINK" => $arParams["PATH_TO_REPORT_CONSTRUCT"].'&ID='.$arParams['REPORT_ID'],
		"ICON"=>"btn_edit"
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();

// </editor-fold>
?>

<style type="text/css">
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
	.adm-filter-main-table { width: auto !important;}
	.adm-list-table-header {
		cursor: pointer;
	}
	.reports-total-column { cursor: default; }
	.report-red-neg-val {color: red}
	.adm-filter-item-center {padding-bottom: 12px;}
	.adm-filter-item-left { white-space: nowrap; }
	.adm-filter-box-sizing { width: auto; min-width: 300px;}
	.adm-filter-content .adm-select-wrap { max-width: none; }
	.adm-workarea .adm-input-wrap .adm-input { min-width: 110px; }
	.filter-field-hidden { display: none; }
</style>

<!-- filter form -->
<form id="report-rewrite-filter" action="<?=$arParams["PATH_TO_REPORT_VIEW"]?>" method="GET">
<input type="hidden" name="lang" value="<?=htmlspecialcharsbx(LANGUAGE_ID)?>" />
<input type="hidden" name="ID" value="<?=htmlspecialcharsbx($arParams['REPORT_ID'])?>" />
<input type="hidden" name="sort_id" value="<?=htmlspecialcharsbx($arResult['sort_id'])?>" />
<input type="hidden" name="sort_type" value="<?=htmlspecialcharsbx($arResult['sort_type'])?>" />
<? if(isset($_REQUEST['publicSidePanel']) && $_REQUEST['publicSidePanel'] == 'Y'): ?>
	<input type="hidden" name="publicSidePanel" value="Y" />
<? endif ?>
<? if(isset($_REQUEST['IFRAME']) && $_REQUEST['IFRAME'] == 'Y'): ?>
	<input type="hidden" name="IFRAME" value="Y" />
	<input type="hidden" name="IFRAME_TYPE" value="SIDE_SLIDER" />
<? endif ?>
<?
// prepare info
$info = array();
foreach($arResult['changeableFilters'] as $chFilter)
{
	$field = isset($chFilter['field']) ? $chFilter['field'] : null;
	// Try to obtain qualified field name (e.g. 'COMPANY_BY.COMPANY_TYPE_BY.STATUS_ID')
	$name = isset($chFilter['name']) ? $chFilter['name'] : ($field ? $field->GetName() : '');
	$info[] = array(
		'TITLE' => $chFilter['title'],
		'COMPARE' => ToLower(GetMessage('REPORT_FILTER_COMPARE_VAR_'.$chFilter['compare'])),
		'NAME' =>$chFilter['formName'],
		'ID' => $chFilter['formId'],
		'VALUE' => $chFilter['value'],
		'FIELD_NAME' => $name,
		'FIELD_TYPE' => $chFilter['data_type'],
		'IS_UF' => $chFilter['isUF'],
		'UF_ID' => $chFilter['ufId'],
		'UF_NAME' => $chFilter['ufName']
	);
}
?>

<table class="adm-filter-main-table">
	<tbody>
	<tr>
		<td class="adm-filter-main-table-cell">
			<div id="filter-tabs" class="adm-filter-tabs-block">
				<span class="adm-filter-tab adm-filter-tab-active" style="cursor: default;"><?=GetMessage('REPORT_FILTER')?></span>
			</div>
		</td>
	</tr>
	<tr>
		<td class="adm-filter-main-table-cell">
			<div class="adm-filter-content">
				<div class="adm-filter-content-table-wrap">
					<!-- control examples -->
					<table cellspacing="0" id="adm-report-chfilter-examples" class="adm-filter-content-table" style="display: none;">
						<tbody>
						<!-- date example -->
						<tr class="chfilter-field-datetime adm-report-chfilter-control">
							<td class="adm-filter-item-left">%TITLE% "%COMPARE%":</td>
							<td class="adm-filter-item-center">
								<div class="adm-filter-alignment adm-calendar-block">
									<div class="adm-filter-box-sizing">
										<div class="adm-input-wrap adm-calendar-inp adm-calendar-first">
											<input type="text" value="%VALUE%" name="%NAME%" id="%ID%" class="adm-input adm-filter-from">
											<span title="<?php echo GetMessage("TASKS_PICK_DATE")?>" class="adm-calendar-icon"
												onclick="BX.calendar({node:this, field:'%ID%', form: '', bTime: false, bHideTime: false});"></span>
										</div>
									</div>
								</div>
							</td>
							<td class="adm-filter-item-right"></td>
						</tr>
						<!-- string example -->
						<tr class="chfilter-field-string adm-report-chfilter-control">
							<td class="adm-filter-item-left">%TITLE% "%COMPARE%":</td>
							<td class="adm-filter-item-center">
								<div class="adm-filter-alignment">
									<div class="adm-filter-box-sizing">
										<div class="adm-input-wrap">
											<input type="text" value="%VALUE%" name="%NAME%" class="adm-input">
										</div>
									</div>
								</div>
							</td>
							<td class="adm-filter-item-right"></td>
						</tr>
						<!-- integer example -->
						<tr class="chfilter-field-integer adm-report-chfilter-control">
							<td class="adm-filter-item-left">%TITLE% "%COMPARE%":</td>
							<td class="adm-filter-item-center">
								<div class="adm-filter-alignment">
									<div class="adm-filter-box-sizing">
										<div class="adm-input-wrap">
											<input type="text" value="%VALUE%" name="%NAME%" class="adm-input">
										</div>
									</div>
								</div>
							</td>
							<td class="adm-filter-item-right"></td>
						</tr>
						<!-- float example -->
						<tr class="chfilter-field-float adm-report-chfilter-control">
							<td class="adm-filter-item-left">%TITLE% "%COMPARE%":</td>
							<td class="adm-filter-item-center">
								<div class="adm-filter-alignment">
									<div class="adm-filter-box-sizing">
										<div class="adm-input-wrap">
											<input type="text" value="%VALUE%" name="%NAME%" class="adm-input">
										</div>
									</div>
								</div>
							</td>
							<td class="adm-filter-item-right"></td>
						</tr>
						<!-- boolean example -->
						<tr class="chfilter-field-boolean adm-report-chfilter-control" callback="RTFilter_chooseBoolean">
							<td class="adm-filter-item-left">%TITLE% "%COMPARE%":</td>
							<td class="adm-filter-item-center">
								<div class="adm-filter-alignment">
									<div class="adm-filter-box-sizing">
									<span class="adm-select-wrap">
										<select class="adm-select" id="%ID%" name="%NAME%" caller="true">
											<option value=""><?=GetMessage('REPORT_IGNORE_FILTER_VALUE')?></option>
											<option value="true"><?=GetMessage('REPORT_BOOLEAN_VALUE_TRUE')?></option>
											<option value="false"><?=GetMessage('REPORT_BOOLEAN_VALUE_FALSE')?></option>
										</select>
										<script type="text/javascript">
											function RTFilter_chooseBooleanCatch(value)
											{
												setSelectValue(RTFilter_chooseBoolean_LAST_CALLER, value);
											}
										</script>
									</span>
								</div>
								</div>
							</td>
							<td class="adm-filter-item-right"></td>
						</tr>
						</tbody>
					</table>

					<table cellspacing="0" class="adm-filter-content-table" style="display: table;">
						<tbody>

						<?php /*    Site option    */ ?>
						<? if (isset($arParams['F_SALE_SITE'])): ?>
						<tr>
							<td class="adm-filter-item-left"><?=GetMessage('SALE_REPORT_SITE').':'?></td>
							<td class="adm-filter-item-center">
								<div class="adm-filter-alignment">
									<div class="adm-filter-box-sizing">
										<?php
										$selected = $arParams['F_SALE_SITE'];
										$siteList = call_user_func(array($arResult['helperClassName'], 'getSiteList'));
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
							</td>
							<td class="adm-filter-item-right"></td>
						</tr>
						<? endif; ?>

						<!-- period -->
						<tr<? echo $isPeriodHidden ? ' class="filter-field-hidden"' : ''; ?>>
							<td class="adm-filter-item-left"><?=GetMessage('REPORT_PERIOD').':'?></td>
							<td class="adm-filter-item-center">
								<div class="adm-filter-alignment adm-calendar-block">
									<div class="adm-filter-box-sizing">
										<span class="adm-select-wrap adm-calendar-period">
											<select onchange="OnReportIntervalChange(this)" name="F_DATE_TYPE"
													id="report-interval-filter" class="adm-select adm-calendar-period">
												<?php foreach ($arPeriodTypes as $key => $type): ?>
												<option value="<?php echo htmlspecialcharsbx($key)?>"<?=($key == $arResult['period']['type']) ? " selected" : ""?>><?php echo htmlspecialcharsbx($type)?></option>
												<?php endforeach;?>
											</select>
										</span>
										<!-- filter date from -->
										<div style="display: none;" class="adm-input-wrap adm-calendar-inp adm-calendar-first">
											<input type="text" value="<?=$arResult['form_date']['from']?>" name="F_DATE_FROM"
												id="REPORT_INTERVAL_F_DATE_FROM" class="adm-input adm-calendar-from">
											<span onclick="BX.calendar({node:this, field:'REPORT_INTERVAL_F_DATE_FROM', form: '', bTime: false, bHideTime: false});"
												title="<?php echo GetMessage("TASKS_PICK_DATE")?>" class="adm-calendar-icon"></span>
										</div>
										<!-- filter separator -->
										<span style="display: none;" class="adm-calendar-separate"></span>
										<!-- filter date to -->
										<div style="display: none;" class="adm-input-wrap adm-calendar-second">
											<input type="text" value="<?=$arResult['form_date']['to']?>" name="F_DATE_TO"
												id="REPORT_INTERVAL_F_DATE_TO" class="adm-input adm-calendar-to">
											<span onclick="BX.calendar({node:this, field:'REPORT_INTERVAL_F_DATE_TO', form: '', bTime: false, bHideTime: false});"
												title="<?php echo GetMessage("TASKS_PICK_DATE")?>" class="adm-calendar-icon"></span>
										</div>
										<!-- days field -->
										<div style="display: none;" class="adm-input-wrap filter-day-interval">
											<span class="<?php if ($arResult["FILTER"]["F_DATE_TYPE"] == "days"): ?>filter-day-interval-selected<?php endif; ?>">
												<input type="text" class="filter-date-days" value="<?=$arResult['form_date']['days']?>"
													name="F_DATE_DAYS"/>
											</span>
											<span><?php echo GetMessage("TASKS_REPORT_DAYS")?></span>
										</div>
									</div>
								</div>
							</td>
							<td class="adm-filter-item-right"></td>
						</tr>
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
								/*else if(select.value == "before") filterDateTo.style.display = 'inline-block';*/
								else if(select.value == "before") filterDateFrom.style.display = 'inline-block';
								/*else if(select.value == "after") filterDateFrom.style.display = 'inline-block';*/
								else if(select.value == "after") filterDateTo.style.display = 'inline-block';
								else if(select.value == "days") filterDays.style.display = 'inline-block';
							}

							BX.ready(function() {
								OnReportIntervalChange(BX('report-interval-filter'));
							});
						</script>

						<?php /*    Sale report currency selection    */    ?>
						<? if (mb_substr(call_user_func(array($arResult['helperClassName'], 'getOwnerId')), 0, 5) === 'sale_'): ?>
						<tr>
							<td class="adm-filter-item-left"><?=GetMessage('SALE_REPORT_CURRENCY').':'?></td>
							<td class="adm-filter-item-center">
								<div class="adm-filter-alignment">
									<div class="adm-filter-box-sizing">
										<span class="adm-select-wrap">
											<select class="adm-select" id="sale-currency-filter" name="F_SALE_CURRENCY">
												<?php
												$arCurrencies = call_user_func(array($arResult['helperClassName'], 'getCurrencies'));
												?>
												<? foreach($arCurrencies as $k => $v): ?>
												<option <?php
													if ($v['selected'] === true) echo 'selected="1"';
													?> value="<?=htmlspecialcharsbx($k)?>"><?=htmlspecialcharsbx($k.' ('.$v['name'].')')?></option>
												<? endforeach; ?>
											</select>
										</span>
									</div>
								</div>
							</td>
							<td class="adm-filter-item-right"></td>
						</tr>
						<? endif; ?>

						<?php /*    Product custom "quantity" filter    */    ?>
						<? if (call_user_func(array($arResult['helperClassName'], 'getOwnerId')) === 'sale_SaleProduct'):
							$saleProductFilter = array(
								'all' => GetMessage('SALE_REPORT_PRODUCTS_ALL'),
								'avail' => GetMessage('SALE_REPORT_PRODUCTS_AVAIL'),
								'not_avail' => GetMessage('SALE_REPORT_PRODUCTS_NOT_AVAIL')
							);
							if (!empty($arParams['F_SALE_PRODUCT'])) $selected = $arParams['F_SALE_PRODUCT'];
							else $selected = 'all';
						?>
						<tr>
							<td class="adm-filter-item-left"><?=GetMessage('SALE_REPORT_PRODUCTS').':'?></td>
							<td class="adm-filter-item-center">
								<div class="adm-filter-alignment">
									<div class="adm-filter-box-sizing">
										<span class="adm-select-wrap">
											<select class="adm-select" id="sale-product-filter" name="F_SALE_PRODUCT">
												<? foreach($saleProductFilter as $k => $v): ?>
												<option <?php
													if ($k === $selected) echo 'selected="1"';
													?> value="<?=htmlspecialcharsbx($k)?>"><?=htmlspecialcharsbx($v)?></option>
												<? endforeach; ?>
											</select>
										</span>
									</div>
								</div>
							</td>
							<td class="adm-filter-item-right"></td>
						</tr>
						<? endif; ?>

						<?php /*    Product custom "types of prices" filter    */    ?>
						<? if (call_user_func(array($arResult['helperClassName'], 'getOwnerId')) === 'sale_SaleProduct'
							&& $arResult['settings']['helper_spec']['ucspt'] === true): ?>
						<tr>
							<td class="adm-filter-item-left"><?=GetMessage('SALE_REPORT_PRICE_TYPES').':'?></td>
							<td class="adm-filter-item-center">
								<div class="adm-filter-alignment">
									<div class="adm-filter-box-sizing">
										<span class="adm-select-wrap-multiple">
											<select class="adm-select-multiple" id="sale-ucspt-filter" name="F_SALE_UCSPT[]"
												multiple="multiple" size="5">
											<?php
											$arPriceTypes = call_user_func(array($arResult['helperClassName'], 'getPriceTypes'));
											?>
											<?php foreach($arPriceTypes as $k => $v): ?>
											<option <?php
												if ($v['selected'] === true) echo 'selected="1"';
												?>value="<?=htmlspecialcharsbx($k)?>"><?=htmlspecialcharsbx($v['name'])?></option>
											<?php endforeach; ?>
											</select>
										</span>
									</div>
								</div>
							</td>
							<td class="adm-filter-item-right"></td>
						</tr>
						<? endif; ?>

						<tr id="adm-report-filter-chfilter" style="display: none;"></tr>

						</tbody>
					</table>
				</div>
				<div id="tbl_sale_transact_filter_bottom_separator" class="adm-filter-bottom-separate" style="display: block;"></div>
				<div class="adm-filter-bottom">
					<span id="report-rewrite-filter-button" class="adm-btn-wrap"><input type="submit" value="<?=GetMessage('REPORT_FILTER_APPLY')?>" title="<?=GetMessage('REPORT_FILTER_APPLY')?>" name="set_filter" class="adm-btn"></span>
					<span id="report-reset-filter-button" class="adm-btn-wrap"><input type="submit" value="<?=GetMessage('REPORT_FILTER_CANCEL')?>" title="<?=GetMessage('REPORT_FILTER_CANCEL')?>" name="del_filter" class="adm-btn"></span>
				</div>
			</div>
		</td>
	</tr>
	</tbody>
</table>
<script type="text/javascript">
	BX.ready(function(){
		BX.bind(BX('report-reset-filter-button'), 'click', function(){
			BX.submit(BX('report-reset-filter'));
		});
		BX.bind(BX('report-rewrite-filter-button'), 'click', function(){
			BX.submit(BX('report-rewrite-filter'));
		});
	});
	function setSelectValue(select, value)
	{
		var i, j;
		var bFirstSelected = false;
		var bMultiple = !!(select.getAttribute('multiple'));
		if (!(value instanceof Array)) value = new Array(value);
		for (i=0; i<select.options.length; i++)
		{
			for (j in value)
			{
				if (select.options[i].value == value[j])
				{
					if (!bFirstSelected) {bFirstSelected = true; select.selectedIndex = i;}
					select.options[i].selected = true;
					break;
				}
			}
			if (!bMultiple && bFirstSelected) break;
		}
	}
</script>


<!-- insert changeable filters -->
<script type="text/javascript">

	function replaceInAttributesAndTextElements(el, info) {
		var i, attr, bMultipleSelect;
		while (el) {
			if (3 == el.nodeType)
			{
				if (el.nodeValue)
				{
					el.nodeValue = el.nodeValue.replace(/%((?!VALUE)[A-Z]+)%/gi,
						function(str, p1, offset, s)
						{
							var n = p1.toUpperCase();
							return typeof(info[n]) != 'undefined' ? BX.util.htmlspecialchars(info[n]) : str;
						}
					);
					el.nodeVaue = el.nodeValue.replace('%VALUE%', BX.util.htmlspecialchars(info.VALUE));
				}
			}
			else if (1 == el.nodeType)
			{
				bMultipleSelect = !!(el.getAttribute('multiple'));
				for (i in el.attributes)
				{
					attr = el.attributes[i];
					if (attr)
					{
						if (attr.value)
						{
							attr.value = attr.value.replace(/%((?!VALUE)[A-Z]+)%/gi,
								function(str, p1, offset, s)
								{
									var n = p1.toUpperCase();
									var newStr = typeof(info[n]) != 'undefined' ? BX.util.htmlspecialchars(info[n]) : str;
									if (bMultipleSelect) newStr += '[]';
									return newStr;
								}
							);
							attr.value = attr.value.replace('%VALUE%', BX.util.htmlspecialchars(info.VALUE));
						}
					}
				}
			}
			replaceInAttributesAndTextElements(el.firstChild, info);
			el = el.nextSibling;
		}
	}

	BX.ready(function() {
		var info = <?=CUtil::PhpToJSObject($info)?>;
		var cpControl, fieldType;
		for (var i in info)
		{
			if (!info.hasOwnProperty(i))
				continue;

			cpControl = null;
			fieldType = info[i].FIELD_TYPE;
			// insert value control
			// search in `examples-custom` by name or type
			// then search in `examples` by type
			cpControl = BX.clone(
				BX.findChild(
					BX('adm-report-chfilter-examples-custom'),
					{className: 'chfilter-field-' + info[i].FIELD_NAME},
					true
				)
				||
				BX.findChild(
					BX('adm-report-chfilter-examples-custom'),
					{className: 'chfilter-field-' + fieldType},
					true
				)
				||
				BX.findChild(
					BX('adm-report-chfilter-examples'),
					{className: 'chfilter-field-' + fieldType},
					true
				),
				true
			);

			//global replace %ID%, %NAME%, %TITLE% and etc.
			replaceInAttributesAndTextElements(cpControl, info[i]);
			if (cpControl.getAttribute('callback') != null)
			{
				// set last caller
				var callerName = cpControl.getAttribute('callback') + '_LAST_CALLER';
				var callerObj = BX.findChild(cpControl, {attr:'caller'}, true);
				window[callerName] = callerObj;

				// set value
				var cbFuncName = cpControl.getAttribute('callback') + 'Catch';
				window[cbFuncName](info[i].VALUE);
			}

			BX.findParent(BX('adm-report-filter-chfilter')).appendChild(cpControl);
		}
	});

</script>

</form>

<form id="report-reset-filter" action="<?=$arParams["PATH_TO_REPORT_VIEW"]?>" method="GET">
	<input type="hidden" name="lang" value="<?=htmlspecialcharsbx(LANGUAGE_ID)?>" />
	<input type="hidden" name="ID" value="<?=htmlspecialcharsbx($arParams['REPORT_ID'])?>" />
	<input type="hidden" name="sort_id" value="<?=htmlspecialcharsbx($arResult['sort_id'])?>" />
	<input type="hidden" name="sort_type" value="<?=htmlspecialcharsbx($arResult['sort_type'])?>" />
</form>

<div style="padding-top: 18px;"></div>



<?php
// determine column data type
function getResultColumnDataType(&$viewColumnInfo, &$customColumnTypes, $helperClassName)
{
	$dataType = null;
	if (is_array($customColumnTypes) && array_key_exists($viewColumnInfo['fieldName'], $customColumnTypes))
	{
		$dataType = $customColumnTypes[$viewColumnInfo['fieldName']];
	}
	else
	{
		/** @var Bitrix\Main\Entity\Field[] $viewColumnInfo */
		$dataType = call_user_func(array($helperClassName, 'getFieldDataType'), $viewColumnInfo['field']);
	}
	if (!empty($viewColumnInfo['prcnt']))
	{
		$dataType = 'float';
	}
	else if (!empty($viewColumnInfo['aggr']))
	{
		if ($viewColumnInfo['aggr'] == 'COUNT_DISTINCT') $dataType = 'integer';
		else if ($viewColumnInfo['aggr'] == 'GROUP_CONCAT') $dataType = 'string';
		else if ($dataType == 'boolean')
		{
			if ($viewColumnInfo['aggr'] == 'MIN' || $viewColumnInfo['aggr'] == 'AVG'
				|| $viewColumnInfo['aggr'] == 'MAX' || $viewColumnInfo['aggr'] == 'SUM'
				|| $viewColumnInfo['aggr'] == 'COUNT_DISTINCT')
			{
				$dataType = 'integer';
			}
		}

	}
	return $dataType;
}
?>



<!-- result table -->
<?php if ($arResult['groupingMode'] === true): // show result using a grouping mode ?>
<?php
// <editor-fold defaultstate="collapsed" desc="Grouping mode">
?>



<style type="text/css">
	/*.reports-grouping-table-wrap {
		background-color: white;
		padding: 20px;
		margin-bottom: 20px;
		border: 1px solid lightgray;
		border-radius: 5px
	}*/
	.adm-list-table-header .adm-list-table-cell
	{
		height: auto;
		padding: 2px;
	}

	.reports-grouping-table {width: 100%; border-collapse: collapse;}
	.reports-grouping-table thead .reports-grouping-table-head-row,
	.reports-grouping-table tbody .reports-grouping-group-row,
	.reports-grouping-table tbody .reports-grouping-total-row
	{
		font-weight: bold;
	}
	.reports-grouping-table .reports-grouping-table-row-separator
	{
		border: 0;
		font-size: 0;
	}
	.reports-grouping-table .reports-grouping-table-row-separator td {border: 0; background-color: transparent;}
	.reports-grouping-table tr td
	{
		border: 1px solid #89979D; background-color: white;
	}
	.reports-grouping-table td.align-right {text-align: right;}
	.reports-grouping-table td.align-left {text-align: left;}
	.reports-grouping-table td.align-center {text-align: center;}
	.adm-list-table-header {cursor: auto;}
	.reports-grouping-table .reports-grouping-group-row td.adm-list-table-cell
	{
		background: none #E0E9EC;
		border-color: #89979D;
	}
</style>

<?php
function groupingReportResultHtml(&$arParams, &$arResult, $level = 0, $arRowSet = array())
{
	$strHtml = '';
	$total = array();
	$arViewColumns = &$arResult['viewColumns'];
	$arData = &$arResult['data'];

	// static variables
	static $arGroups = array();
	static $arColumns = array();
	static $nGroups = 0;
	static $nColumns = 0;
	static $arValueTypes = array();
	static $marginWidth = 20;

	// chart info variables
	static $bChartGrouping = false;
	static $arChartColumns = array();
	static $chartColumnMaxGroupLevel = -1;
	static $chartDataRowIndex = -1;
	static $arChartGroupingValues = array();

	// initialize static variables
	if ($level === 0)
	{
		foreach($arViewColumns as $viewColumnIndex => $viewColumn)
		{
			if ($viewColumn['grouping']) $arGroups[$nGroups++] = $viewColumnIndex;
			else $arColumns[$nColumns++] = $viewColumnIndex;
			$arValueTypes[$viewColumnIndex] = getResultColumnDataType($viewColumn, $arResult['customColumnTypes'],
				$arResult['helperClassName']);
		}

		// chart columns
		if ($arParams['USE_CHART'] && $arResult['settings']['chart']['display'])
		{
			$chartSettings = $arResult['settings']['chart'];
			if (isset($chartSettings['x_column']) && is_array($chartSettings['y_columns']))
			{
				$arChartColumns[] = $chartSettings['x_column'];
				$arChartColumns = array_merge($arChartColumns, $chartSettings['y_columns']);
			}

			// obtain max group level of chart columns
			foreach ($arChartColumns as $nChartColumn)
			{
				foreach ($arGroups as $groupLevel => $nGroup)
				{
					if ($nChartColumn == $nGroup)
					{
						$bChartGrouping = true;
						$chartColumnMaxGroupLevel = max($chartColumnMaxGroupLevel, $groupLevel);
					}
				}
			}
		}
	}

	$nRows = count($arRowSet);
	$bUseRowSet = ($nRows > 0) ? true : false;
	if (!$bUseRowSet) $nRows = count($arData);
	if ($nGroups > 0)
	{
		// grouping table header
		if ($level === 0)
		{
			$bFirstGroup = true;
			$strHtml .= 
				'<div class="reports-grouping-table-wrap">'.PHP_EOL.
				"\t".'<table id="report-result-table" class="reports-grouping-table" cellpadding=2 cellspacing=0>'.PHP_EOL.
					"\t\t".'<thead>'.PHP_EOL;
			foreach ($arGroups as $groupColumnIndex)
			{
				$strHtml .=
					"\t\t\t".'<tr class="adm-list-table-header reports-grouping-table-head-row">'.PHP_EOL.
					"\t\t\t\t".'<td class="adm-list-table-cell">'.htmlspecialcharsbx($arViewColumns[$groupColumnIndex]['humanTitle']).'</td>'.PHP_EOL;
				if ($bFirstGroup)
				{
					$bFirstGroup = false;
					foreach ($arColumns as $viewColumnIndex)
					{
						$strHtml .=
							"\t\t\t\t".'<td class="adm-list-table-cell align-center"';
						if ($nGroups > 1) $strHtml .= ' rowspan="'.htmlspecialcharsbx($nGroups).'"';
						$strHtml .= ' colId="'.$viewColumnIndex.'" defaultSort="'.$arViewColumns[$viewColumnIndex]['defaultSort'].'">'.
							htmlspecialcharsbx($arViewColumns[$viewColumnIndex]['humanTitle']).'</td>'.PHP_EOL;
					}
				}
				$strHtml .=
					"\t\t\t".'</tr>'.PHP_EOL;
			}
			$strHtml .=
				"\t\t".'</thead>'.PHP_EOL;
		}

		if ($nRows > 0)
		{
			// table header separator
			if ($level === 0)
			{
				$strHtml .=
					"\t\t".'<tbody>'.PHP_EOL.
					"\t\t\t".'<tr class="reports-grouping-table-row-separator"><td></td></tr>'.PHP_EOL;
			}

			// init total
			if ($nColumns > 0) foreach (array_keys($arColumns) as $columnIndex) $total[$columnIndex] = null;
	
			if ($level < $nGroups)
			{
				// fill group arrays
				$arGroupValues = array();
				$arGroupValuesIndexes = array();
				$rowNumber = 0;
				$groupDataType = $arValueTypes[$arGroups[$level]];
				$dataIndex = null;
				reset($arData);
				while ($rowNumber++ < $nRows)
				{
					// get index
					if ($bUseRowSet)
					{
						$dataIndex = current($arRowSet);
						next($arRowSet);
					}
					else
					{
						$dataIndex = key($arData);
						next($arData);
					}
	
					// fill index and value of group
					$arGroupValuesIndexes[] = $dataIndex;
					$groupValue = $arData[$dataIndex][$arViewColumns[$arGroups[$level]]['resultName']];
					if ($groupDataType === 'date' || $groupDataType === 'datetime') // convert value for a sorting
					{
						$groupValue = MakeTimeStamp($groupValue, CSite::GetDateFormat('SHORT'));
					}

					// magic glue
					if (is_array($groupValue)) $groupValue = join(' / ', $groupValue);

					$arGroupValues[] = $groupValue;
				}
	
				// determine sort options
				$groupSortOption = SORT_STRING;
				$groupSortDirection = SORT_ASC;
				if (in_array($groupDataType, array('date','datetime','integer','float'))) $groupSortOption = SORT_NUMERIC;
				if ($arGroups[$level] == $arResult['sort_id'])
				{
					if ($arResult['sort_type'] != 'ASC') $groupSortDirection = SORT_DESC;
				}
	
				// sort group
				array_multisort($arGroupValues, $groupSortOption, $groupSortDirection,
					$arGroupValuesIndexes, SORT_NUMERIC, SORT_ASC);
	
				// recursive scan
				$prev = null;
				$newRowSet = array();
				$nGroupValues = count($arGroupValues);
				$nSubRows = 0;
				for ($i = 0; $i < $nGroupValues; $i++)
				{
					$cur = $arGroupValues[$i];
					if ($i == 0) $prev = $cur;
					$bLastValue = ($nGroupValues - 1 == $i);
					if ($cur != $prev || $bLastValue)
					{
						$n = ($bLastValue && $cur != $prev) ? 2 : 1;
						while ($n-- > 0)
						{
							// chart values index
							if ($bChartGrouping && $level == $chartColumnMaxGroupLevel) $chartDataRowIndex++;

							if ($bLastValue && $cur == $prev) $newRowSet[] = $arGroupValuesIndexes[$i];
							$arGroupingResult = groupingReportResultHtml($arParams, $arResult, $level+1, $newRowSet);
							$arSubTotal = $arGroupingResult['total'];
							$strSubHtml = $arGroupingResult['html'];
							unset($arGroupingResult);
							$newRowSet = array();
							if (!$bLastValue) $newRowSet[] = $arGroupValuesIndexes[$i];
							$prev = $cur;
	
							// show row
							$groupValueIndex = ($bLastValue && $n === 0) ? $i : $i - 1;
							$groupValueKey = $arViewColumns[$arGroups[$level]]['resultName'];
							$groupValue = $arData[$arGroupValuesIndexes[$groupValueIndex]][$groupValueKey];

							// magic glue
							if (is_array($groupValue)) $groupValue = join(' / ', $groupValue);

							// add chart values
							if ($bChartGrouping && in_array($arGroups[$level], $arChartColumns))
							{
								$tempChartDataRowIndex = $chartDataRowIndex;
								while ($tempChartDataRowIndex >= 0 &&
									!isset($arChartGroupingValues[$tempChartDataRowIndex][$arGroups[$level]])
								)
								{
									$arChartGroupingValues[$tempChartDataRowIndex--][$arGroups[$level]] = $groupValue;
								}
								unset($tempChartDataRowIndex);
							}

							// values of groups were processed as normal values
							/*if (method_exists($arResult['helperClassName'], 'formatResultGroupingValue'))
							{
								// format group value
								call_user_func(
									array($arResult['helperClassName'], 'formatResultGroupingValue'),
									array(
										'k' => $groupValueKey,
										'v' => &$groupValue,
										'row' => &$arData[$arGroupValuesIndexes[$groupValueIndex]],
										'cInfo' => &$arViewColumns[$arGroups[$level]]
									)
								);
							}*/

							$cellClassFirstColumn = '';
							if ($level == $nGroups - 1)
							{
								$rowClass = ' reports-grouping-data-row';
							}
							else
							{
								$rowClass = ' reports-grouping-group-row';
								$cellClassFirstColumn = 'adm-list-table-cell';
							}
							$cellClassAttr = '';
							if (!empty($cellClassFirstColumn)) $cellClassAttr = ' class="'.$cellClassFirstColumn.'"';

							$margin = ($level > 0) ? ' style="margin-left: '.($level*$marginWidth).'px;"' : '';
							$strHtml .=
								"\t\t\t".'<tr class="adm-list-table-header'.$rowClass.'">'.PHP_EOL.
								"\t\t\t\t".'<td'.$cellClassAttr.'><div'.$margin.'>'.(($groupValue === '') ? '&nbsp;' : $groupValue).'</div></td>'.PHP_EOL;
							foreach ($arSubTotal as $k => $subValue)
							{
								$cellAlign = '';
								$cellClass = $cellClassFirstColumn;
								if ($arResult['settings']['red_neg_vals'] === true)
								{
									if (is_numeric($subValue) && $subValue < 0) $cellClass .= ' report-red-neg-val';
								}

								// cell align
								$colAlign = $arViewColumns[$arColumns[$k]]['align'];
								if ($colAlign === null)
								{
									if (CReport::isColumnPercentable($arViewColumns[$arColumns[$k]], $arResult['helperClassName']))
									{
										$cellAlign = ' align-right';
									}
								}
								else if ($colAlign === 'right')
								{
									$cellAlign = ' align-right"';
								}

								if (!empty($cellAlign)) $cellClass .= $cellAlign;
								if (!empty($cellClass)) $cellClass = ' class="'.ltrim($cellClass).'"';

								$bGroupingSubtotal = $arViewColumns[$arColumns[$k]]['grouping_subtotal'];
								if ($bGroupingSubtotal || $level == $nGroups - 1)
								{
									$finalSubValue = $subValue;
									if (method_exists($arResult['helperClassName'], 'formatResultGroupingTotal'))
									{
										// format subtotal value
										$subValueKey = $arViewColumns[$arColumns[$k]]['resultName'];
										call_user_func(
											array($arResult['helperClassName'], 'formatResultGroupingTotal'),
											array(
												'k' => $subValueKey,
												'v' => &$finalSubValue,
												'cInfo' => &$arViewColumns[$arColumns[$k]]
											)
										);
									}
								}
								else
								{
									$finalSubValue = '';
								}
								$strHtml .=
									"\t\t\t\t".'<td'.$cellClass.'>'.(($finalSubValue === '') ? '&nbsp;' : $finalSubValue).'</td>'.PHP_EOL;

								// add chart values
								if ($bChartGrouping && $level == $chartColumnMaxGroupLevel && in_array($arColumns[$k], $arChartColumns))
								{
									$arChartGroupingValues[$chartDataRowIndex][$arColumns[$k]] = $finalSubValue;
								}
							}
							$strHtml .=
								"\t\t\t".'</tr>'.PHP_EOL;
							$strHtml .= $strSubHtml;
	
							// total += subtotal
							if ($nColumns > 0)
							{
								foreach ($arColumns as $columnIndex => $viewColumnIndex)
								{
									$columnDataType = $arValueTypes[$viewColumnIndex];
									if ($columnDataType === 'integer' || $columnDataType === 'float')
									{
										if (is_string($arSubTotal[$columnIndex]))
										{
											$arSubTotal[$columnIndex] = str_replace(' ', '', $arSubTotal[$columnIndex]);
										}
										$total[$columnIndex] += $arSubTotal[$columnIndex];
									}
								}
								$nSubRows++;
							}
						} // while ($n-- > 0)
					}
					else $newRowSet[] = $arGroupValuesIndexes[$i];
				}
				// calculate average values
				if ($nSubRows > 1)
				{
					foreach ($arColumns as $columnIndex => $viewColumnIndex)
					{
						if ($arViewColumns[$viewColumnIndex]['aggr'] === 'AVG'
							|| $arViewColumns[$viewColumnIndex]['grouping_aggr'] === 'AVG'
						)
						{
							$total[$columnIndex] = $total[$columnIndex] / $nSubRows;
						}
					}
				}
			}
			else // last level
			{
				if ($nColumns > 0)
				{
					$rowNumber = 0;
					while ($rowNumber++ < $nRows)
					{
						// get index
						if ($bUseRowSet)
						{
							$dataIndex = current($arRowSet);
							next($arRowSet);
						}
						else
						{
							$dataIndex = key($arData);
							next($arData);
						}
	
						// total += values
						foreach ($arColumns as $columnIndex => $viewColumnIndex)
						{
							$columnDataType = $arValueTypes[$viewColumnIndex];
							if ($nRows == 1)
							{
								$dataValueKey = $arViewColumns[$viewColumnIndex]['resultName'];
								$dataValue = $arData[$dataIndex][$dataValueKey];
								// normal value
								/*if (method_exists($arResult['helperClassName'], 'formatResultGroupingValue'))
								{
									// format result value
									call_user_func(
										array($arResult['helperClassName'], 'formatResultGroupingValue'),
										array(
											'k' => $dataValueKey,
											'v' => &$dataValue,
											'row' => &$arData[$dataIndex],
											'cInfo' => &$arViewColumns[$viewColumnIndex]
										)
									);
								}*/
								if ($columnDataType === 'integer' || $columnDataType === 'float' && is_string($dataValue))
								{
									$dataValue = str_replace(' ', '', $dataValue);
								}
								$total[$columnIndex] = $dataValue;
							}
							else if ($columnDataType === 'integer' || $columnDataType === 'float')
							{
								$dataValue = $arData[$dataIndex][$arViewColumns[$viewColumnIndex]['resultName']];
								if (is_string($dataValue)) $dataValue = str_replace(' ', '', $dataValue);
								$total[$columnIndex] += $dataValue;
							}
						}
					}
					// calculate average values
					if ($nRows > 1)
					{
						foreach ($arColumns as $columnIndex => $viewColumnIndex)
						{
							if ($arViewColumns[$viewColumnIndex]['aggr'] === 'AVG'
								|| $arViewColumns[$viewColumnIndex]['grouping_aggr'] === 'AVG'
							)
							{
								$total[$columnIndex] = $total[$columnIndex] / $nRows;
							}
						}
					}
				}
			}
		}
	
		// show total
		if ($level === 0)
		{
			if (count($total) > 0)
			{
				// show total check
				$bShowTotal = false;
				foreach ($total as $k => $v)
				{
					if ($arViewColumns[$arColumns[$k]]['grouping_subtotal'])
					{
						$bShowTotal = true;
						break;
					}
				}

				if ($bShowTotal)
				{
					$strHtml .=
						"\t\t\t".'<tr class="reports-grouping-table-row-separator"><td></td></tr>'.PHP_EOL;
					$strHtml .=
						"\t\t\t".'<tr class="adm-list-table-header reports-grouping-total-row">'.PHP_EOL.
						"\t\t\t\t".'<td class="adm-list-table-cell">'.htmlspecialcharsbx(GetMessage('REPORT_TOTAL')).'</td>'.PHP_EOL;
					foreach ($total as $k => $v)
					{
						$cellAlign = '';
						$cellClass = '';
						if ($arResult['settings']['red_neg_vals'] === true)
						{
							if (is_numeric($v) && $v < 0) $cellClass .= ' report-red-neg-val';
						}

						// cell align
						$colAlign = $arViewColumns[$arColumns[$k]]['align'];
						if ($colAlign === null)
						{
							if (CReport::isColumnPercentable($arViewColumns[$arColumns[$k]], $arResult['helperClassName']))
							{
								$cellAlign = ' align-right';
							}
						}
						else if ($colAlign === 'right')
						{
							$cellAlign = ' align-right';
						}

						if (!empty($cellAlign)) $cellClass .= $cellAlign;

						$bGroupingSubtotal = $arViewColumns[$arColumns[$k]]['grouping_subtotal'];
						if ($bGroupingSubtotal)
						{
							$finalTotalValue = $v;
							if (method_exists($arResult['helperClassName'], 'formatResultGroupingTotal'))
							{
								// format subtotal value
								$subValueKey = $arViewColumns[$arColumns[$k]]['resultName'];
								call_user_func(
									array($arResult['helperClassName'], 'formatResultGroupingTotal'),
									array(
										'k' => $subValueKey,
										'v' => &$finalTotalValue,
										'cInfo' => &$arViewColumns[$arColumns[$k]]
									)
								);
							}
						}
						else
						{
							$finalTotalValue = '';
						}
						$strHtml .=
							"\t\t\t\t".'<td class="adm-list-table-cell'.$cellClass.'">'.(($finalTotalValue === '') ? '&nbsp;' : $finalTotalValue).'</td>'.PHP_EOL;
					}
					$strHtml .=
						"\t\t\t".'</tr>'.PHP_EOL;
				}
			}
			$strHtml .= "\t\t".'</tbody>'.PHP_EOL."\t".'</table>'.PHP_EOL.'</div>'.PHP_EOL;
		}
	}

	$result = array('total' => $total, 'html' => $strHtml);

	// return chart values
	if ($bChartGrouping && $level === 0)
	{
		$result['chart'] = $arChartGroupingValues;
		$arChartGroupingValues = array();
	}

	return $result;
}

$arGroupingResult = groupingReportResultHtml($arParams, $arResult);
echo $arGroupingResult['html'];
unset($arGroupingResult['html']);
?>
<?php
// </editor-fold>
?>
<?php else: // show result using a default mode?>
<table cellspacing="0" class="adm-list-table" id="report-result-table">
	<!-- head -->
	<thead>
	<tr class="adm-list-table-header">
		<? $i = 0; foreach($arResult['viewColumns'] as $colId => $col): ?>
		<?
		$i++;

		if ($i == 1)
		{
			$th_class = 'reports-first-column';
		}
		else if ($i == count($arResult['viewColumns']))
		{
			$th_class = 'reports-last-column';
		}
		else
		{
			$th_class = 'reports-head-cell';
		}

		// sorting
		//$defaultSort = 'DESC';
		$defaultSort = $col['defaultSort'];

		if ($colId == $arResult['sort_id'])
		{
			$th_class .= ' reports-selected-column';

			if($arResult['sort_type'] == 'ASC')
			{
				$th_class .= ' reports-head-cell-top';
			}
		}
		else
		{
			if ($defaultSort == 'ASC')
			{
				$th_class .= ' reports-head-cell-top';
			}
		}

		?>
		<td class="adm-list-table-header adm-list-table-cell adm-list-table-cell-sort <?php
			if ($colId == $arResult['sort_id'])
			{
				if($arResult['sort_type'] == 'ASC')
					echo 'adm-list-table-cell-sort-up';
				else
					echo 'adm-list-table-cell-sort-down';
			}
			?> <?=$th_class?>" colId="<?=$colId?>" defaultSort="<?=$defaultSort?>">
			<div class="adm-list-table-cell-inner reports-head-cell">
				<span class="reports-head-cell-title"><?=htmlspecialcharsbx($col['humanTitle'])?></span>
			</div>
		</td>
		<? endforeach; ?>
	</tr>
	</thead>

	<!-- data -->
	<tbody>
	<? foreach ($arResult['data'] as $row): ?>
	<tr class="adm-list-table-row">
		<? $i = 0; foreach($arResult['viewColumns'] as $col): ?>
		<?
		$i++;
		if ($i == 1)
		{
			$td_class = 'reports-first-column';
		}
		else if ($i == count($arResult['viewColumns']))
		{
			$td_class = 'reports-last-column';
		}
		else
		{
			$td_class = '';
		}

		if ($col['align'] === 'right')
		{
			$td_class .= ' align-right';
		}
		else if ($col['align'] === 'left')
		{
			$td_class .= ' align-left';
		}
		else
		{
			if (CReport::isColumnPercentable($col, $arResult['helperClassName']))
			{
				$td_class .= ' align-right';
			}
			else
			{
				$td_class .= ' align-left';
			}
		}

		$finalValue = $row[$col['resultName']];

		// add link
		if (!empty($col['href']) && !empty($row['__HREF_'.$col['resultName']]))
		{
			if (is_array($finalValue))
			{
				// grc
				foreach ($finalValue as $grcIndex => $v)
				{
					$finalValue[$grcIndex] = '<a href="'
						.$arResult['grcData'][$col['resultName']][$grcIndex]['__HREF_'.$col['resultName']]
						.'">'.$v.'</a>';
				}
			}
			elseif(mb_strlen($row[$col['resultName']]))
			{
				$finalValue = '<a href="'.$row['__HREF_'.$col['resultName']].'">'.$row[$col['resultName']].'</a>';
			}
		}

		// magic glue
		if (is_array($finalValue))
		{
			$finalValue = join(' / ', $finalValue);
		}
		if ($arResult['settings']['red_neg_vals'] === true)
		{
			if (is_numeric($finalValue) && $finalValue < 0) $td_class .= ' report-red-neg-val';
		}
		?>
		<td class="adm-list-table-cell <?=$td_class?>"><?=$finalValue?></td>
		<? endforeach; ?>
	</tr>
	<? endforeach; ?>

	<tr>
		<td colspan="<?=count($arResult['viewColumns'])?>" class="reports-pretotal-column">
			<?php echo $arResult["NAV_STRING"]?>
			<div style="height: 50px;"></div>
			<span style="font-size: 14px;"><?=GetMessage('REPORT_TOTAL')?></span>
		</td>
	</tr>

	<tr class="adm-list-table-header">
		<? $i = 0; foreach($arResult['viewColumns'] as $col): ?>
		<?
		$i++;
		if ($i == 1)
		{
			$td_class = 'reports-first-column';
		}
		else if ($i == count($arResult['viewColumns']))
		{
			$td_class = 'reports-last-column';
		}
		else
		{
			$td_class = '';
		}
		?>
		<td class="adm-list-table-cell <?=$td_class?> reports-total-column">
			<div class="adm-list-table-cell-inner reports-head-cell">
				<span class="reports-head-cell-title"><?=htmlspecialcharsbx($col['humanTitle'])?></span>
			</div>
		</td>
		<? endforeach; ?>
	</tr>

	<tr class="adm-list-table-row">
		<? $i = 0; foreach($arResult['viewColumns'] as $col): ?>
		<?
		$i++;
		if ($i == 1)
		{
			$td_class = 'reports-first-column';
		}
		else if ($i == count($arResult['viewColumns']))
		{
			$td_class = 'reports-last-column';
		}
		else
		{
			$td_class = '';
		}

		if ($col['align'] === 'right')
		{
			$td_class .= ' align-right';
		}
		else if ($col['align'] === 'left')
		{
			$td_class .= ' align-left';
		}
		else
		{
			if (CReport::isColumnPercentable($col, $arResult['helperClassName']))
			{
				$td_class .= ' align-right';
			}
			else
			{
				$td_class .= ' align-left';
			}
		}

		if (array_key_exists('TOTAL_'.$col['resultName'], $arResult['total']))
		{
			$finalValue = $arResult['total']['TOTAL_'.$col['resultName']];
			if ($arResult['settings']['red_neg_vals'] === true)
			{
				if (is_numeric($finalValue) && $finalValue < 0) $td_class .= ' report-red-neg-val';
			}
		}
		else $finalValue = '&mdash;';
		?>
		<td class="adm-list-table-cell <?=$td_class?>"><?=$finalValue?></td>
		<? endforeach; ?>
	</tr>
	</tbody>


</table>
<script type="text/javascript">
	BX.ready(function(){
		var rows = BX.findChildren(BX('report-result-table'), {tag:'td', 'className':'adm-list-table-header'}, true);
		for (i in rows)
		{
			var ds = rows[i].getAttribute('defaultSort');
			if (ds == '')
			{
				BX.addClass(rows[i], 'report-column-disabled-sort');
				BX.removeClass(rows[i], 'adm-list-table-cell-sort');
				continue;
			}

			BX.bind(rows[i], 'click', function(){
				var colId = this.getAttribute('colId');
				var sortType = '';

				var isCurrent = BX.hasClass(this, 'reports-selected-column');

				if (isCurrent)
				{
					var currentSortType = BX.hasClass(this, 'reports-head-cell-top') ? 'ASC' : 'DESC';
					sortType = currentSortType == 'ASC' ? 'DESC' : 'ASC';
				}
				else
				{
					sortType = this.getAttribute('defaultSort');
				}

				var idInp = BX.findChild(BX('report-rewrite-filter'), {attr:{name:'sort_id'}});
				var typeInp = BX.findChild(BX('report-rewrite-filter'), {attr:{name:'sort_type'}});

				idInp.value = colId;
				typeInp.value = sortType;

				BX.submit(BX('report-rewrite-filter'));
			});
		}
	});
</script>
<?php endif; ?>	

<?php if ($arParams['USE_CHART'] && $arResult['settings']['chart']['display']): ?>
<style type="text/css">
	#report-chart-legend-row-example {display: none;}
	.report-chart-legend-container {margin-top: 20px;}
	.report-chart-legend-container div {margin: 5px;}
	.report-chart-legend-stick {display: inline-block; width: 45px; height: 2px; vertical-align: middle;}
	.report-chart-legend-square {display: inline-block; width: 20px; height: 20px; vertical-align: middle;}
	.report-chart-legend-label {
		font-size: 14px;
		font-family: Verdana, Arial, sans-serif;
		vertical-align: middle;
		margin-left: 10px;
	}
</style>
<?php
	// data preparation for the chart
	function prepareChartData(&$arResult, &$arGroupingResult = null)
	{
		$nMaxValues = 500;
		$result = array('requestData' => array(), 'columnsNames' => array(), 'err' => 0);

		// check
		$chartSettings = $arResult['settings']['chart'];
		if (!isset($chartSettings['x_column']))
		{
			$result['err'] = 49;
		}
		$xColumnIndex = $chartSettings['x_column'];
		if (!is_array($arResult['viewColumns'][$xColumnIndex]))
		{
			$result['err'] = 49;
			return $result;
		}
		if (!is_array($chartSettings['y_columns']))
		{
			$result['err'] = 49;
			return $result;
		}
		$yColumnsCount = count($chartSettings['y_columns']);
		if ($yColumnsCount === 0)
		{
			$result['err'] = 49;
			return $result;
		}
		foreach ($chartSettings['y_columns'] as $yColumnIndex)
		{
			if (!is_array($arResult['viewColumns'][$yColumnIndex]))
			{
				$result['err'] = 49;
				break;
			}
		}
		if ($result['err'] !== 0)
		{
			return $result;
		}

		$chartTypeIds = array();
		foreach ($arResult['chartTypes'] as $chartTypeInfo) $chartTypeIds[] = $chartTypeInfo['id'];
		if (!is_set($chartSettings['type'])
			|| empty($chartSettings['type'])
			|| !in_array($chartSettings['type'], $chartTypeIds))
		{
			$result['err'] = 49;
			return $result;
		}

		$chartType = $chartSettings['type'];
		if ($chartType === 'pie')
			$yColumnsCount = 1;    // pie chart has only one array of a values
		$xColumnDataType = getResultColumnDataType($arResult['viewColumns'][$xColumnIndex],
			$arResult['customColumnTypes'], $arResult['helperClassName']);
		$xColumnResultName = $arResult['viewColumns'][$xColumnIndex]['resultName'];
		$yColumnsIndexes = array();
		$yColumnsResultNames = array();
		$columnsHumanTitles = array();
		$columnsHumanTitles[0] = $arResult['viewColumns'][$xColumnIndex]['humanTitle'];
		$columnsTypes = array();
		$columnsTypes[0] = $xColumnDataType;
		for ($i = 0; $i < $yColumnsCount; $i++)
		{
			$yColumnsIndexes[] = $yColumnIndex = $chartSettings['y_columns'][$i];
			$yColumnsResultNames[] = $arResult['viewColumns'][$yColumnIndex]['resultName'];
			$columnsHumanTitles[] = $arResult['viewColumns'][$yColumnIndex]['humanTitle'];
			$columnsTypes[$i + 1] = getResultColumnDataType($arResult['viewColumns'][$yColumnIndex],
				$arResult['customColumnTypes'], $arResult['helperClassName']);
		}
		$requestData = array(
			'type' => $chartType,
			'columnTypes' => $columnsTypes
		);
		if (!is_null($arGroupingResult) && is_array($arGroupingResult))
		{
			$n = count($arGroupingResult);
			if ($chartType !== 'pie')
				$n = min($nMaxValues, $n);
			for ($i = 0; $i < $n; $i++)
			{
				$row = array();
				$dataRow = $arGroupingResult[$i];
				$row[0] = $dataRow[$xColumnIndex];
				foreach ($yColumnsIndexes as $yColumnIndex) $row[] = $dataRow[$yColumnIndex];
				$requestData['data'][] = $row;
			}
		}
		else
		{
			$n = count($arResult['data']);
			if ($chartType !== 'pie')
				$n = min($nMaxValues, $n);
			for ($i = 0; $i < $n; $i++)
			{
				$row = array();
				$dataRow = $arResult['data'][$i];
				$row[0] = $dataRow[$xColumnResultName];
				foreach ($yColumnsResultNames as $yColumnResultName) $row[] = $dataRow[$yColumnResultName];
				$requestData['data'][] = $row;
			}
		}

		$result['requestData'] = $requestData;
		$result['columnsNames'] = $columnsHumanTitles;

		return $result;
	}

	/*
	// Example.
	// Through AJAX requestData goes only. ColumnsNames is used only for creation a legend of a chart.
	// For the creation a legend of the round chart, using values from AJAX response.

	// Round:
	$chartData = array(
		'requestData' => array(
			'type' => 'pie',
			'width' => 670,
			'height' => 420,
			'columnTypes' => array(
				'string', 'float'
			),
			'data' => array(
				array( 'Mirrors',   3 ),
				array( 'Textiles', 10 ),
				array( 'Ligts', 2 ),
				array( 'Sofas',    9 )
			)
		),
		'columnsNames' => array(
			'Number of sold goods'
		)
	);

	// Histogram:
	$chartData = array(
		'requestData' => array(
			'type' => 'bar',
			'width' => 670,
			'height' => 420,
			'columnTypes' => array(
				'string', 'float', 'integer'
			),
			'data' => array(
				array( 'Mirrors',    3, 3 ),
				array( 'Textiles',  10, 2 ),
				array( 'Ligts',  2, 1 ),
				array( 'Sofas',     9, 1 )
			)
		),
		'columnsNames' => array(
			'Number of sold goods',
			'Number of goods in category'
		)
	);

	// Line:
	$chartData = array(
		'requestData' => array(
			'type' => 'line',
			'width' => 670,
			'height' => 420,
			'columnTypes' => array(
				'string', 'float', 'integer'
			),
			'data' => array(
				array( 'Mirrors',    3, 3 ),
				array( 'Textiles',  10, 2 ),
				array( 'Ligts',  2, 1 ),
				array( 'Sofas',     9, 1 )
			)
		),
		'columnsNames' => array(
			'Number of sold goods',
			'Number of goods in category'
		)
	);
	*/
	$chartData = prepareChartData($arResult, $arGroupingResult['chart']);
	if (is_array($chartData) && isset($chartData['err']) && $chartData['err'] !== 0)
	{
		$chartData = null;
	}
	unset($arGroupingResult);
?>
<div style="font-size: 14px; margin: 40px 2px 2px 2px;"><?php echo GetMessage('REPORT_CHART').':'; ?></div>
<div id="report-chart-container" class="graph" style="margin-top: 0; font-size: 14px;">
	<div style="font-size: 0;">
	<img id="report-chart-image" src="data:image/png;base64,
		iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAAXNSR0IArs4c6QAAAARnQU1BAACx
		jwv8YQUAAAAJcEhZcwAADsIAAA7CARUoSoAAAAEOSURBVFhHzdhbCsQgDAXQ6azL3eu+plhQOq2P
		JPf68ENBUA4xQvTw3v8+G7cjAZ1zWzFDCJfnG7uISxM7KKMlBewC7oS843IEd0E+cS/gykiWcEXg
		CmQNVwXORLZwTeAMZA/XBY5ESnAi4AikFCcGMpEanArIQGpxaiCCtOBMQAvSijMDNUgEBwElSBQH
		A1tIBo4CLCFZOBrwjmTiqMBUU7LHXFGjG6fIsZ8PFODzWJlIGFjLORYSAvYuBANpBvZwKadRpAko
		xTGQaqAWhyJVQCsOQYqBKM6KFAFZOAuyC2TjtMgmcBROg6wCR+OkyCJwFk6CfAFn43rIP+AqXAuZ
		gatxNeT2n+gZiFbUo9af6LvlbUS4p8YAAAAASUVORK5CYII=" title="" alt="" />
	</div>
	<div id="report-chart-legend-container"></div>
</div>
<div id="report-chart-legend-row-example">
	<div class="report-chart-legend-stick"></div><span class="report-chart-legend-label"></span>
</div>
<script type="text/javascript">
	BX.ready(function () {
		var chartData = <?=CUtil::PhpToJSObject($chartData, true)?>;
		if (!chartData || !chartData['columnsNames'] || !chartData['requestData']) return;
		var columnsNames = chartData['columnsNames'];
		var requestData = chartData.requestData;
		var imgContainer = BX('report-chart-container');
		url = '<?php echo CUtil::JSEscape($this->GetFolder().'/graph.php?sessid='.
			CUtil::JSEscape(bitrix_sessid())); ?>';
		BX.showWait(imgContainer, '<?=CUtil::JSEscape(GetMessage('REPORT_CHART_CREATION').'...')?>');
		requestData['width'] = imgContainer.offsetWidth - 34;
		BX.ajax.post(url, {'chartData': requestData}, function (data) {
			var response, img;
			if (data)
			{
				eval('response = ' + data);
				if (response)
				{
					if (response.imageData)
					{
						if (response.imageData.substr(0,10) === 'data:image')
						{
							img = BX('report-chart-image');
							img.src = response.imageData;
							if (response.legendInfo)
							{
								var legendContainer = BX('report-chart-legend-container');
								var legendRowExample = BX('report-chart-legend-row-example');
								var chartType = requestData['type'];
								var legendNewRow, legendStick, legendLabel;
								for (var i in response.legendInfo)
								{
									if (i == 0) BX.addClass(legendContainer, 'report-chart-legend-container');
									legendNewRow = BX.clone(legendRowExample, true);
									if (legendNewRow)
									{
										legendNewRow.removeAttribute('id');
										legendStick = BX.findChild(legendNewRow, {'className': 'report-chart-legend-stick'});
										legendLabel = BX.findChild(legendNewRow, {'className': 'report-chart-legend-label'});
										if (chartType === 'bar' || chartType === 'pie')
										{
											legendStick.className = 'report-chart-legend-square';
										}
										if (chartType === 'pie')
										{
											legendStick.style.backgroundColor = '#'+response.legendInfo[i]['color'];
											var labelText = '"' + response.legendInfo[i]['label'] + '"';
											var trifleText = '<?=CUtil::JSEscape(GetMessage('REPORT_CHART_TRIFLE_LABEL_TEXT'))?>';
											if (labelText === '"__trifle__"') labelText = trifleText;
											legendLabel.innerHTML = labelText + ': ' +
												response.legendInfo[i]['value'] + ' (' +
												response.legendInfo[i]['prcnt'] + '%)';
										}
										else
										{
											legendStick.style.backgroundColor = '#'+response.legendInfo[i];
											legendLabel.innerHTML = columnsNames[parseInt(i)+1];
										}
										legendContainer.appendChild(legendNewRow);
									}
								}
							}
						}
					}
				}
			}
			BX.closeWait();
		});
	});
</script>
<?php endif; // if ($arParams['USE_CHART'] && $arResult['settings']['chart']['display']): ?>

<!-- currency label -->
<? if (isset($arParams['REPORT_CURRENCY_LABEL_TEXT'])): ?>
<div class="adm-info-message-wrap">
	<div class="adm-info-message">
		<?=$arParams['REPORT_CURRENCY_LABEL_TEXT']?>
	</div>
</div>
<? endif; ?>

<!-- weight units label -->
<? if (isset($arParams['REPORT_WEIGHT_UNITS_LABEL_TEXT'])): ?>
<div class="adm-info-message-wrap">
	<div class="adm-info-message">
		<?=$arParams['REPORT_WEIGHT_UNITS_LABEL_TEXT']?>
	</div>
</div>
<? endif; ?>

<!-- description -->
<? if($arResult['report']['DESCRIPTION'] <> ''): ?>
	<div class="adm-info-message-wrap">
		<div class="adm-info-message">
			<?= htmlspecialcharsbx($arResult['report']['DESCRIPTION']) ?>
		</div>
	</div>
<? endif; ?>
