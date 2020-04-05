<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult['ERROR']))
{
	echo $arResult['ERROR'];
	return false;
}

?>
<div class="mobile-admin-report-ptitle"><?=GetMessage('REPORT_REPORT').": ".$arResult['report']['TITLE']?></div>
<?

// determine column data type
function getResultColumnDataType(&$viewColumnInfo, &$customColumnTypes = array(), $helperClassName)
{
	$dataType = null;
	if (array_key_exists($viewColumnInfo['fieldName'], $customColumnTypes))
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

//<!-- result table -->
if ($arResult['groupingMode'] === true): // show result using a grouping mode

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
			$strHtml .=
				"\t".'<div class="order_infoblock">'.PHP_EOL.
				"\t\t".'<div class="order_infoblock_content">'.PHP_EOL.
				"\t\t\t".'<table class="bx_table_item_remainder">'.PHP_EOL.
					"\t\t\t\t".'<thead>'.PHP_EOL;
				$strHtml .=
					"\t\t\t\t\t".'<tr class="mobile-admin-report-header">'.PHP_EOL.
					"\t\t\t\t\t\t".'<td>'.htmlspecialcharsbx($arViewColumns[$groupColumnIndex]['humanTitle']);

				$bFirst = true;
				foreach ($arGroups as $groupColumnIndex)
				{
					if($bFirst)
						$strHtml .= htmlspecialcharsbx($arViewColumns[$groupColumnIndex]['humanTitle']);
					else
						$strHtml .= '<span>'.htmlspecialcharsbx($arViewColumns[$groupColumnIndex]['humanTitle']).'</span>';
					$bFirst = false;
				}

				$strHtml .= "\t\t\t\t\t\t".'</td>'.PHP_EOL;

				foreach ($arColumns as $viewColumnIndex)
				{
					$strHtml .="\t\t\t\t\t\t".'<td defaultSort="'.$arViewColumns[$viewColumnIndex]['defaultSort'].'">'.
						htmlspecialcharsbx($arViewColumns[$viewColumnIndex]['humanTitle'])."\t\t\t\t\t\t".'</td>'.PHP_EOL;
				}

				$strHtml .=
					"\t\t\t\t\t".'</tr>'.PHP_EOL;

			$strHtml .=
				"\t\t\t\t".'</thead>'.PHP_EOL;
		}

		if ($nRows > 0)
		{
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
					if ($bUseRowSet) list(,$dataIndex) = each($arRowSet);
					else list($dataIndex,) = each($arData);

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
				$closeTBody = false;
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

							if ($level == 0)
							{
								if($closeTBody)
									$strHtml .= "\t\t\t\t".'</tbody>'.PHP_EOL;

								$strHtml .= "\t\t\t\t".'<tbody>'.PHP_EOL;
								$closeTBody = true;

								$rowClass = ' class="mobile-admin-report-category"';
							}
							else
								$rowClass ='';

							$strHtml .=	"\t\t\t\t\t".'<tr'.$rowClass.'>'.PHP_EOL."\t\t\t\t\t\t".'<td>';

							if ($level == $nGroups - 1)
								$strHtml .= '<span>'.$groupValue.'</span>';
							else
								$strHtml .= $groupValue;

							$strHtml .= "\t\t\t\t\t\t".'</td>'.PHP_EOL;
							foreach ($arSubTotal as $k => $subValue)
							{
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
									$finalSubValue = '&nbsp;';
								}
								$strHtml .=
									"\t\t\t\t\t\t".'<td>'.$finalSubValue.'</td>'.PHP_EOL;
							}
							$strHtml .=
								"\t\t\t\t\t".'</tr>'.PHP_EOL;

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
						if ($bUseRowSet) list(,$dataIndex) = each($arRowSet);
						else list($dataIndex,) = each($arData);

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
								// normal value
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
						"\t\t\t".'</tbody><tbody><tr style="background: none repeat scroll 0 0 #EDF2D4;">'.PHP_EOL.
						"\t\t\t\t".'<td>'.htmlspecialcharsbx(GetMessage('REPORT_TOTAL')).':</td>'.PHP_EOL;
					foreach ($total as $k => $v)
					{

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
							$finalTotalValue = '&nbsp;';
						}
						$strHtml .=
							"\t\t\t\t".'<td>'.$finalTotalValue.'</td>'.PHP_EOL;
					}
					$strHtml .=
						"\t\t\t".'</tr>'.PHP_EOL;
				}
			}
			$strHtml .= "\t".'</tbody></table>'.PHP_EOL.'</div></div>'.PHP_EOL;
		}
	}

	return array('total' => $total, 'html' => $strHtml);
}


$arGroupingResult = groupingReportResultHtml($arParams, $arResult);
echo $arGroupingResult['html'];
unset($arGroupingResult);
?>
<?php
// </editor-fold>
?>
<?php else: // show result using a default mode?>
<div class="order_infoblock">
<div class="order_infoblock_title"><?=$arResult['report']['TITLE']?></div>
<div class="order_infoblock_content">

	<table class="bx_table_item_remainder">
		<!-- head -->
		<thead>
		<tr>
			<? $i = 0; foreach($arResult['viewColumns'] as $colId => $col): ?>
			<?
			$i++;

			// sorting
			//$defaultSort = 'DESC';
			$defaultSort = $col['defaultSort'];

			?>
			<td <?php
				?> defaultSort="<?=$defaultSort?>">
					<?=htmlspecialcharsbx($col['humanTitle'])?>

			</td>
			<? endforeach; ?>
		</tr>
		</thead>

		<!-- data -->
		<tbody>
		<? foreach ($arResult['data'] as $row): ?>
		<tr>
			<? $i = 0; foreach($arResult['viewColumns'] as $col): ?>
			<?
			$i++;

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
				elseif (strlen($row[$col['resultName']]))
				{
					$finalValue = '<a href="'.$row['__HREF_'.$col['resultName']].'">'.$row[$col['resultName']].'</a>';
				}
			}

			// magic glue
			if (is_array($finalValue))
			{
				$finalValue = join(' / ', $finalValue);
			}
			?>
			<td><?=$finalValue?></td>
			<? endforeach; ?>
		</tr>
		<? endforeach; ?>
		<tr>
			<? $i = 0; foreach($arResult['viewColumns'] as $col): ?>
			<?
			$i++;

			if (array_key_exists('TOTAL_'.$col['resultName'], $arResult['total']))
			{
				$finalValue = $arResult['total']['TOTAL_'.$col['resultName']];
			}
			else $finalValue = '&mdash;';
			?>
			<td></td>
			<? endforeach; ?>
		</tr>
		</tbody>

	</table>
</div></div>
<?php endif; ?>
<script type="text/javascript">
	app.setPageTitle({title: "<?=GetMessage('REPORT_REPORT').": ".$arResult['report']['TITLE']?>"});
</script>
