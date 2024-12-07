<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult['ERROR']))
{
	echo $arResult['ERROR'];
	return false;
}

$arReportResult = array();

// determine column data type

function getReportHeader($arGroups,&$arResult)
{
	$retHtml = '<table class="bx_table_item_remainder"><thead><tr class="mobile-admin-report-header"><td>';

	$bFirst = true;
	foreach ($arGroups as $groupColumnIndex)
	{
		if($bFirst)
			$retHtml .= $arResult['viewColumns'][$groupColumnIndex]['humanTitle'];
		else
			$retHtml .= '<span>'.$arResult['viewColumns'][$groupColumnIndex]['humanTitle'].'</span>';

		$bFirst = false;
	}

	$retHtml .= '</td></tr></thead></table>';

	return $retHtml;
}

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

//<!-- result table -->
if ($arResult['groupingMode'] === true) // show result using a grouping mode
{
	function groupingReportResultHtml(&$arParams, &$arResult, $level = 0, $arRowSet = array())
	{
		$arReportData = array();
		$total = array();
		$arViewColumns = &$arResult['viewColumns'];
		$arData = &$arResult['data'];
		$finalRow = 0;
		$headerHtml = '';

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

			$headerHtml = getReportHeader($arGroups, $arResult);
		}
		$nRows = count($arRowSet);
		$bUseRowSet = ($nRows > 0) ? true : false;
		if (!$bUseRowSet) $nRows = count($arData);
		if ($nGroups > 0)
		{
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
								$groupValueIndex = ($bLastValue && $n === 0) ? $i : $i - 1;
								if ($bLastValue && $cur == $prev) $newRowSet[] = $arGroupValuesIndexes[$i];
								$arGroupingResult = groupingReportResultHtml($arParams, $arResult, $level+1, $newRowSet);
								$arSubTotal = $arGroupingResult['total'];
								$arReportData[$groupValueIndex]["CLOSED"] = true;

								if(!empty($arGroupingResult['array']))
									$arReportData[$groupValueIndex]["SECTIONS"] = $arGroupingResult['array'];

								unset($arGroupingResult);
								$newRowSet = array();
								if (!$bLastValue) $newRowSet[] = $arGroupValuesIndexes[$i];
								$prev = $cur;

								// show row
								$groupValueKey = $arViewColumns[$arGroups[$level]]['resultName'];
								$groupValue = $arData[$arGroupValuesIndexes[$groupValueIndex]][$groupValueKey];

								// magic glue
								if (is_array($groupValue)) $groupValue = join(' / ', $groupValue);

								if ($level == 0)
								{
									$closeTBody = true;
									$rowClass = ' class="mobile-admin-report-category"';
								}
								else
									$rowClass ='';

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

									if($k == 0)
										$arReportData[$groupValueIndex]["TITLE"] = $groupValue;

									if(empty($arReportData[$groupValueIndex]["SECTIONS"]))
									{
										$arReportData[$groupValueIndex]["CONTENT"][$k]["TITLE"] = $arViewColumns[$arColumns[$k]]['humanTitle'].": ";
										$arReportData[$groupValueIndex]["CONTENT"][$k]["VALUE"] = $finalSubValue;
									}

								}

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

						$finalRow = $i;
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
						$arReportData[$finalRow+1]["TITLE"]	= GetMessage('REPORT_TOTAL');
						$arReportData[$finalRow+1]["CLOSED"] = true;
						$arReportData[$finalRow+1]["HIGHLIGHTED"] = true;

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

							$arReportData[$finalRow+1]["CONTENT"][$k]["TITLE"] = $arViewColumns[$arColumns[$k]]['humanTitle'].": ";
							$arReportData[$finalRow+1]["CONTENT"][$k]["VALUE"] = $finalTotalValue;

							//$arReportData["finalTotalValue"][$k] = $finalTotalValue;
						}
					}
				}
			}
		}

		return array('total' => $total, 'array'=>$arReportData, 'headerHtml'=>$headerHtml);
	}

	$arReportResult = groupingReportResultHtml($arParams, $arResult);
}
else // show result using a default mode
{
	foreach ($arResult['data'] as $row)
	{
		$arTmpRow = array();
		$arTmpRowHead = array();
		$firstColIndex = false;

		foreach($arResult['viewColumns'] as $colIdx => $col)
		{
			if($firstColIndex === false)
				$firstColIndex = $colIdx;

			$finalValue = $row[$col['resultName']];

			// magic glue
			if (is_array($finalValue))
				$finalValue = join(' / ', $finalValue);

			if(empty($arTmpRowHead))
			{
				//$arTmpRowHead["TITLE"] = htmlspecialcharsbx($col['humanTitle']).": ".$finalValue;
				$arTmpRowHead["TITLE"] = $finalValue;
			}
			else
			{
				$arTmpRow[] = array(
					"TITLE" => htmlspecialcharsbx($col['humanTitle']).": ",
					"VALUE" => $finalValue
				);
			}
		}
			$arTmpRowHead["CONTENT"] = $arTmpRow;
			$arTmpRowHead["CLOSED"] = true;
			$arReportResult['array'][] = $arTmpRowHead;
	}

	$arTmpRow = array();
	$arTmpRowHead = array(
		"TITLE" => GetMessage('REPORT_TOTAL'),
		"CLOSED" => true
	);

	foreach($arResult['viewColumns'] as $col)
	{
		if (array_key_exists('TOTAL_'.$col['resultName'], $arResult['total']))
		{
			$finalValue = $arResult['total']['TOTAL_'.$col['resultName']];
		}
		else $finalValue = '&mdash;';

		$arTmpRow[] = array(
					"TITLE" => htmlspecialcharsbx($col['humanTitle']).": ",
					"VALUE" => $finalValue
		);

		$arTmpRowHead["CONTENT"] = $arTmpRow;
	}

	$arReportResult['array'][] = $arTmpRowHead;
	$arGroups = array($firstColIndex);
	$arReportResult['headerHtml'] = getReportHeader($arGroups, $arResult);

}

?>
<div class="mobile-admin-report-ptitle"><?=GetMessage('REPORT_REPORT').": ".$arResult['report']['TITLE']?></div>
<?

echo $arReportResult['headerHtml'];

$arReportParams = array(
	'JS_CONTAINER_ID' => 'test_list_enclosed',
	'DATA' => $arReportResult['array'],
	'INSCRIPTION_FOR_EMPTY' => GetMessage("REPORT_EMPTY")
	);

$APPLICATION->IncludeComponent(
	'bitrix:mobileapp.list.enclosed',
	'.default',
	$arReportParams,
	false
);

?>
<script>
	app.setPageTitle({title: "<?=GetMessage('REPORT_REPORT').": ".$arResult['report']['TITLE']?>"});
</script>
