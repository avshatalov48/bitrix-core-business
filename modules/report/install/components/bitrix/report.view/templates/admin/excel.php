<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/** @var CBitrixComponentTemplate $this */
$this->IncludeLangFile('template.php');

?>
<html>
<head>
	<meta http-equiv="Content-type" content="text/html;charset=<?echo LANG_CHARSET?>" />
<?php if ($arResult['groupingMode'] === true): // style for grouping mode ?>
	<style type="text/css">
		.reports-grouping-table {border-collapse: collapse;}
		.reports-grouping-table thead .reports-grouping-table-head-row td,
		.reports-grouping-table tbody .reports-grouping-group-row td,
		.reports-grouping-table tbody .reports-grouping-total-row td {font-weight: bold; background-color: #e8e8e8;}
		.reports-grouping-table .reports-grouping-table-row-separator {
			border: 0;
			font-size: 0;
			height: 3pt;
		}
		.reports-grouping-table thead .reports-grouping-table-head-row td {vertical-align: middle;}
		.reports-grouping-table .reports-grouping-table-row-separator td {border: 0;}
		.reports-grouping-table tr td {border: .5pt solid gray;}
	</style>
<?php endif;    // style for grouping mode ?>
</head>
<body>
<?php if ($arResult['groupingMode'] === true): // show result using a grouping mode ?>
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
				"\t".'<table class="reports-grouping-table" cellpadding=2 cellspacing=0>'.PHP_EOL.
					"\t\t".'<thead>'.PHP_EOL;
			foreach ($arGroups as $groupColumnIndex)
			{
				$strHtml .=
					"\t\t\t".'<tr class="reports-grouping-table-head-row">'.PHP_EOL.
					"\t\t\t\t".'<td>'.htmlspecialcharsbx($arViewColumns[$groupColumnIndex]['humanTitle']).'</td>'.PHP_EOL;
				if ($bFirstGroup)
				{
					$bFirstGroup = false;
					foreach ($arColumns as $viewColumnIndex)
					{
						$strHtml .=
							"\t\t\t\t".'<td style="text-align: center;"';
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

							if ($level == $nGroups - 1)
							{
								$rowClass = ' reports-grouping-data-row';
							}
							else
							{
								$rowClass = ' reports-grouping-group-row';
							}

							if (!empty($rowClass)) $rowClass = ' class="'.ltrim($rowClass).'"';

							$margin = ($level > 0) ? ' style="margin-left: '.($level*$marginWidth).'px;"' : '';
							/*$rowClass .= ' style="mso-outline-level:'.($level+1).'"';*/
							$strHtml .=
								"\t\t\t".'<tr'.$rowClass.'>'.PHP_EOL.
								"\t\t\t\t".'<td><div'.$margin.'>'.$groupValue.'</div></td>'.PHP_EOL;
							foreach ($arSubTotal as $k => $subValue)
							{
								$cellStyle = '';
								if (($arResult['settings']['red_neg_vals'] ?? false) === true)
								{
									if (is_numeric($subValue) && $subValue < 0) $cellStyle .= ' color: red;';
								}

								// cell align
								$colAlign = ($arViewColumns[$arColumns[$k]]['align'] ?? '');
								if ($colAlign === null)
								{
									if (CReport::isColumnPercentable($arViewColumns[$arColumns[$k]], $arResult['helperClassName']))
									{
										$cellStyle .= ' text-align: right;';
									}
								}
								else if ($colAlign === 'right')
								{
									$cellStyle .= ' text-align: right;';
								}

								if (!empty($cellStyle)) $cellStyle = ' style="'.ltrim($cellStyle).'"';

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
									"\t\t\t\t".'<td'.$cellStyle.'>'.(($finalSubValue === '') ? '&nbsp;' : $finalSubValue).'</td>'.PHP_EOL;
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
						if (($arViewColumns[$viewColumnIndex]['aggr'] ?? '') === 'AVG'
							|| ($arViewColumns[$viewColumnIndex]['grouping_aggr'] ?? '') === 'AVG'
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
							if (($arViewColumns[$viewColumnIndex]['aggr'] ?? '') === 'AVG'
								|| ($arViewColumns[$viewColumnIndex]['grouping_aggr'] ?? '') === 'AVG'
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
						"\t\t\t".'<tr class="reports-grouping-total-row">'.PHP_EOL.
						"\t\t\t\t".'<td>'.htmlspecialcharsbx(GetMessage('REPORT_TOTAL')).'</td>'.PHP_EOL;
					foreach ($total as $k => $v)
					{
						$cellStyle = '';
						if ($arResult['settings']['red_neg_vals'] === true)
						{
							if (is_numeric($v) && $v < 0) $cellStyle .= ' color: red;';
						}

						// cell align
						$colAlign = ($arViewColumns[$arColumns[$k]]['align'] ?? '');
						if ($colAlign === null)
						{
							if (CReport::isColumnPercentable($arViewColumns[$arColumns[$k]], $arResult['helperClassName']))
							{
								$cellStyle = ' text-align: right;';
							}
						}
						else if ($colAlign === 'right')
						{
							$cellStyle = ' text-align: right;';
						}

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
						if (!empty($cellStyle)) $cellStyle = ' class="'.ltrim($cellStyle).'"';
						$strHtml .=
							"\t\t\t\t".'<td'.$cellStyle.'>'.(($finalTotalValue === '') ? '&nbsp;' : $finalTotalValue).'</td>'.PHP_EOL;
					}
					$strHtml .=
						"\t\t\t".'</tr>'.PHP_EOL;
				}
			}
			$strHtml .= "\t\t".'</tbody>'.PHP_EOL."\t".'</table>'.PHP_EOL;
		}
	}

	return array('total' => $total, 'html' => $strHtml);
}

$arGroupingResult = groupingReportResultHtml($arParams, $arResult);
echo $arGroupingResult['html'];
unset($arGroupingResult);
?>

<?php else :    // if ($arResult['groupingMode'] === true): ?>
<?php
	foreach ($arResult['data'] as &$row)
	{
		foreach($arResult['viewColumns'] as $col)
		{
			if (is_array($row[$col['resultName']]))
			{
				$row[$col['resultName']] = join(' / ', $row[$col['resultName']]);
			}
		}
	}
	unset($row);
?>
<style type="text/css">
	.report-red-neg-val { color: red; }
</style>
<table border="1">
	<thead>
		<tr>
			<? foreach($arResult['viewColumns'] as $colId => $col): ?>
				<th><?=htmlspecialcharsbx($col['humanTitle'])?></th>
			<? endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<? foreach ($arResult['data'] as $row): ?>
			<tr>
				<? foreach($arResult['viewColumns'] as $col): ?>
				<?php
					$td_class = '';
					if ($arResult['settings']['red_neg_vals'] === true)
					{
						$finalValue = $row[$col['resultName']];
						if (is_numeric($finalValue) && $finalValue < 0) $td_class = ' class="report-red-neg-val"';
					}
				?>
					<td<?=$td_class?>><?=$row[$col['resultName']]?></td>
				<? endforeach; ?>
			</tr>
		<? endforeach; ?>
	</tbody>
</table>

<?php endif;    // if ($arResult['groupingMode'] === true): ?>
</body>
</html>
