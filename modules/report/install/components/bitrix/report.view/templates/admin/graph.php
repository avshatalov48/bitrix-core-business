<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

if (!check_bitrix_sessid()) exit;

Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

function checkPostChartData(&$postData, $chartXValueTypes, $chartTypes)
{
	$err = 0;

	// check meta
	$columnYValueTypes = array();
	$nColumns = 0;
	if (isset($postData['chartData']) && is_array($postData['chartData']))
	{
		$chartData = &$postData['chartData'];
		$chartTypeIds = array();
		foreach ($chartTypes as &$chartTypeInfo) $chartTypeIds[] = $chartTypeInfo['id'];
		$chartTypesIndexes = array_flip($chartTypeIds);
		$columnYValueTypes = $chartTypes[$chartTypesIndexes[$chartData['type']]]['value_types'];
		if (isset($chartData['type']) && in_array($chartData['type'], $chartTypeIds))
		{
			if (isset($chartData['columnTypes']) && is_array($chartData['columnTypes']))
			{
				$nColumns = count($chartData['columnTypes']);
				if ($nColumns >= 2)
				{
					if ($chartData['type'] == 'pie' && $nColumns != 2) $err = 5;
					else
					{
						foreach ($chartData['columnTypes'] as $columnIndex => $columnType)
						{
							if (is_int($columnIndex) && $columnIndex >= 0)
							{
								if ($columnIndex === 0)
								{
									if (!in_array($columnType, $chartXValueTypes)) $err = 7;
								}
								else
								{
									if (!in_array($columnType, $columnYValueTypes)) $err = 8;
								}
							}
							else $err = 6;
							if ($err !== 0) break;
						}
					}
				}
				else $err = 4;
			}
			else $err = 3;
		}
		else $err = 2;
	}
	else $err = 1;

	// check data
	if ($err === 0)
	{
		if (isset($chartData['data']) && is_array($chartData['data']))
		{
			foreach ($chartData['data'] as $rowIndex => &$dataRow)
			{
				if (is_int($rowIndex) && $rowIndex >= 0)
				{
					if (is_array($dataRow))
					{
						$nDataColumns = count($dataRow);
						if ($nDataColumns === $nColumns)
						{
							foreach ($dataRow as $columnIndex => &$dataValue)
							{
								if (is_int($columnIndex) && $columnIndex >= 0)
								{
									// convert type of value
									switch ($chartData['columnTypes'][$columnIndex])
									{
										case 'boolean':
											$dataValue = ($dataValue) ? true : false;
											break;
										case 'date':
										case 'datetime':
											if (!empty($dataValue))
											{
												if (!CheckDateTime($dataValue, CSite::GetDateFormat('SHORT'))) $err = 15;
											}
											break;
										case 'float':
											if (is_string($dataValue)) $dataValue = str_replace(' ', '', $dataValue);
											$dataValue = (float)$dataValue;
											break;
										case 'integer':
											if (is_string($dataValue)) $dataValue = str_replace(' ', '', $dataValue);
											$dataValue = (int)$dataValue;
											break;
										case 'string':
										case 'text':
										case 'enum':
										case 'file':
										case 'disk_file':
										case 'employee':
										case 'crm':
										case 'crm_status':
										case 'iblock_element':
										case 'iblock_section':
											$dataValue = (string)$dataValue;
											break;
										default:
											$err = 14;
									}
								}
								else $err = 13;
								if ($err !== 0) break;
							}
						}
						else $err = 12;
					}
					else $err = 11;
				}
				else $err = 10;
				if ($err !== 0) break;
			}
		}
		else $err = 9;
	}

	return $err;
}

$chartXValueTypes = array('boolean', 'date', 'datetime', 'float', 'integer', 'string', 'text', 'enum', 'file',
	'disk_file', 'employee', 'crm', 'crm_status', 'iblock_element', 'iblock_section', 'money');

// <editor-fold defaultstate="collapsed" desc="chart types">
$chartTypes = array(
	array('id' => 'line', 'name' => GetMessage('REPORT_CHART_TYPE_LINE'), 'value_types' => array(
		/*'boolean', 'date', 'datetime', */
		'float', 'integer'/* , 'string', 'text', 'enum', 'file', 'disk_file', 'employee', 'crm', 'crm_status',
		'iblock_element', 'iblock_section', 'money'*/)),
	array('id' => 'bar', 'name' => GetMessage('REPORT_CHART_TYPE_BAR'), 'value_types' => array(
		/*'boolean', 'date', 'datetime', */
		'float', 'integer'/* , 'string', 'text', 'enum', 'file', 'disk_file', 'employee', 'crm', 'crm_status',
		'iblock_element', 'iblock_section', 'money'*/)),
	array('id' => 'pie', 'name' => GetMessage('REPORT_CHART_TYPE_PIE'), 'value_types' => array(
		/*'boolean', 'date', 'datetime', */
		'float', 'integer'/* , 'string', 'text', 'enum', 'file', 'disk_file', 'employee', 'crm', 'crm_status',
		'iblock_element', 'iblock_section', 'money'*/)),
);
// </editor-fold>

CUtil::JSPostUnescape();

$errorCode = checkPostChartData($_POST, $chartXValueTypes, $chartTypes);

if ($errorCode === 0)
{
	$chartData = &$_POST['chartData'];

	// chart size
	$minWidth = 192;
	$minHeight = 120;
	$maxWidth = 10000;
	$maxHeight = 6250;
	$baseColor = '6699CC';
	$backgroundColor = 'FFFFFF';

	if (isset($chartData['width']))
	{
		$width = intval($chartData['width']);
		if ($width < $minWidth) $width = $minWidth;
		if ($width > $maxWidth) $width = $maxWidth;
	}
	else $width = 670;

	if (isset($chartData['height']))
	{
		$height = intval($chartData['height']);
		if ($height < $minHeight) $height = $minHeight;
		if ($height > $maxHeight) $height = $maxHeight;
	}
	else $height = 420;

	if ($chartData['type'] === 'line')
	{
		if (count($chartData['data']) >= 2)
		{
			// <editor-fold defaultstate="collapsed" desc="prepare data for line chart">
			$arXLabels = array();
			$arXValues = array();
			$arYValues = array();
			$minX = null; $maxX = null;
			$minY = null; $maxY = null;
			$arYAll = array();
			foreach ($chartData['data'] as $rowIndex => $dataRow)
			{
				foreach ($dataRow as $columnIndex => $dataValue)
				{
					if ($rowIndex === 0)
					{
						if ($columnIndex > 0) $arYValues[$columnIndex-1] = array();
					}
					if ($columnIndex === 0)
					{
						$arXLabels[$rowIndex] = $dataValue;
						$arXValues[$rowIndex] = $rowIndex + 1;
						if ($rowIndex === 0) $maxX = $minX = $arXValues[$rowIndex];
						else
						{
							$minX = min($minX, $arXValues[$rowIndex]);
							$maxX = max($maxX, $arXValues[$rowIndex]);
						}
					}
					else
					{
						$arYAll[] = $arYValues[$columnIndex-1][$rowIndex] = $dataValue;
						if ($rowIndex === 0 && $columnIndex === 1) $maxY = $minY = $dataValue;
						else
						{
							$minY = min($minY, $dataValue);
							$maxY = max($maxY, $dataValue);
						}
					}
				}
			}
			// </editor-fold>

			// <editor-fold defaultstate="collapsed" desc="paint line chart">
			$imageHandle = $ImageHandle = CreateImageHandle($width, $height);

			$arYNorm = GetArrayY($arYAll, $minY, $maxY);
			$arrTTF_FONT = array(
				'X' => array(
					'FONT_PATH' => '/bitrix/components/bitrix/report.view/ttf/verdana.ttf',
					'FONT_SIZE' => 8,
					'FONT_SHIFT' => 12,
					'FONT_BASE' => 3
				),
				'Y' => array(
					'FONT_PATH' => '/bitrix/components/bitrix/report.view/ttf/verdana.ttf',
					'FONT_SIZE' => 8,
					'FONT_SHIFT' => 12,
					'FONT_BASE' => 3
				)
			);
			DrawCoordinatGrid($arXLabels, $arYNorm, $width, $height, $imageHandle,
				$backgroundColor, 'B1B1B1', '000000', 10, 2, $arrTTF_FONT);
			$nColors = count($arYValues);
			$color = $baseColor;
			$arLegendInfo = array();
			foreach ($arYValues as $columnIndex => $arY)
			{
				$arLegendInfo[$columnIndex] = $color;
				Graf($arXValues, $arY, $imageHandle, $minX, $maxX, $minY, $maxY, $color);
				$color = GetNextRGB($color, $nColors);
			}
			// </editor-fold>
		}
		else $errorCode = 42;    // At least two rows of values are required
	}
	else if ($chartData['type'] === 'bar')
	{
		if (count($chartData['data']) >= 2)
		{
			// <editor-fold defaultstate="collapsed" desc="prepare data for bar chart">
			$arXLabels = array();
			$arXValues = array();
			$arData = array();
			$minX = null; $maxX = null;
			$minY = null; $maxY = null;
			$arYAll = array();
			$color = $baseColor;
			$nColors = count($chartData['columnTypes']);
			$arLegendInfo = array();
			foreach ($chartData['data'] as $rowIndex => $dataRow)
			{
				$arData[$rowIndex] = array('DATA' => array(), 'COLORS' => array());
				foreach ($dataRow as $columnIndex => $dataValue)
				{
					if ($columnIndex === 0)
					{
						$arXLabels[$rowIndex] = $dataValue;
						$arXValues[$rowIndex] = $rowIndex + 1;
						if ($rowIndex === 0)
						{
							$maxX = $minX = $arXValues[$rowIndex];
							$arYAll[] = $maxY = $minY = 0;
						}
						else
						{
							$minX = min($minX, $arXValues[$rowIndex]);
							$maxX = max($maxX, $arXValues[$rowIndex]);
						}
						$color = $baseColor;
					}
					else
					{
						$arYAll[] = $arData[$rowIndex]['DATA'][$columnIndex-1] = ($dataValue < 0) ? 0 : $dataValue;
						$minY = min($minY, $dataValue);
						$maxY = max($maxY, $dataValue);
						$arData[$rowIndex]['COLORS'][$columnIndex-1][0] = $color;
						if ($rowIndex === 0) $arLegendInfo[$columnIndex-1] = $color;
						$color = GetNextRGB($color, $nColors);
					}
				}
			}
			// </editor-fold>

			// <editor-fold defaultstate="collapsed" desc="paint bar diagram">
			$imageHandle = $ImageHandle = CreateImageHandle($width, $height);

			$arYNorm = GetArrayY($arYAll, $minY, $maxY);
			$arrTTF_FONT = array(
				'type' => 'bar',
				'X' => array(
					'FONT_PATH' => '/bitrix/components/bitrix/report.view/ttf/verdana.ttf',
					'FONT_SIZE' => 8,
					'FONT_SHIFT' => 12,
					'FONT_BASE' => 3
				),
				'Y' => array(
					'FONT_PATH' => '/bitrix/components/bitrix/report.view/ttf/verdana.ttf',
					'FONT_SIZE' => 8,
					'FONT_SHIFT' => 12,
					'FONT_BASE' => 3
				)
			);
			$gridInfo = DrawCoordinatGrid($arXLabels, $arYNorm, $width, $height, $imageHandle,
				$backgroundColor, 'B1B1B1', '000000', 10, 2, $arrTTF_FONT);
			Bar_Diagram($imageHandle, $arData, $minY, $maxY, $gridInfo);
			// </editor-fold>
		}
		else $errorCode = 43;    // At least one row of values is required
	}
	else if ($chartData['type'] === 'pie')
	{
		if (count($chartData['data']) >= 1)
		{
			// <editor-fold defaultstate="collapsed" desc="prepare data for pie chart">
			$arConsolidated = array();
			foreach ($chartData['data'] as $rowIndex => $dataRow)
			{
				$index = $dataRow[0];
				$arConsolidated[$index] += $dataRow[1];
			}
			$sumAll = 0.0;
			foreach ($arConsolidated as $k => $v)
			{
				if ($v <= 0.0) unset($arConsolidated[$k]);
				else $sumAll += $v;
			}
			$arCounting = $arConsolidated;
			$nValues = count($arCounting);
			if ($nValues > 0)
			{
				$sumAllPrcnt = 0;
				foreach ($arCounting as $k => $v)
				{
					$arCounting[$k] = $v * 100 / $sumAll;
					$sumAllPrcnt =+ $arCounting[$k];
				}
				if (arsort($arCounting, SORT_NUMERIC))
				{
					$arTrifle = array();
					$averageValuePrcnt = $sumAllPrcnt/$nValues;
					$trifleFactor = max($averageValuePrcnt/50, 1.0);
					$i = 0; $prcntCount = 0.0; $offset = 0;
					foreach ($arCounting as $k => $v)
					{
						if ($v < $trifleFactor)
						{
							$offset = $i;
							break;
						}
						else $prcntCount += $v;
						$i++;
					}
					$sumTrifle = 0;
					if ($offset > 0)
					{
						$arTrifle = array_slice($arCounting, $offset, null, true);
						$arCounting = array_slice($arCounting, 0, $offset, true);
						foreach (array_keys($arTrifle) as $k) $sumTrifle += $arConsolidated[$k];
					}
					if (round($prcntCount,2) < 100.0)
					{
						$arCounting['__trifle__'] = 100.0 - $prcntCount;
						$arConsolidated['__trifle__'] = $sumTrifle;
						$nValues++;
					}
					$arData = array();
					$arLegendInfo = array();
					$i = 0; $color = $baseColor;
					foreach ($arCounting as $k => $v)
					{
						$arData[$i]['COUNTER'] = intval($v*100);
						$arData[$i]['COLOR'] = $color;
						$arLegendInfo[$i] = array(
							'color' => $color,
							'label' => CharsetConverter::ConvertCharset($k, LANG_CHARSET, 'UTF-8'),
							'value' => $arConsolidated[$k],
							'prcnt' => round($v,2)
						);
						$color = GetNextRGB($color, $nValues);
						$i++;
					}
				}
				else $errorCode = 46;
			}
			else $errorCode = 45;
			// </editor-fold>

			// <editor-fold defaultstate="collapsed" desc="paint pie diagram">
			if ($errorCode === 0)
			{
				$diameter = min($width, $height);
				$imageHandle = $ImageHandle = CreateImageHandle($diameter, $diameter);
				Circular_Diagram($imageHandle, $arData, $backgroundColor, $diameter,
					round($diameter/2), round($diameter/2));
				$h = $diameter * 0.6;
				$dh = 15;
				$imageHandleTemp = CreateImageHandle($diameter, $h+$dh);
				imagecopy($imageHandleTemp, $imageHandle, 0, 0, 0, ($diameter-$h)/2-$dh, $diameter, $h+$dh);
				imagedestroy($imageHandle);
				$imageHandle = $imageHandleTemp;
			}
			// </editor-fold>
		}
		else $errorCode = 44;    // At least one value is required
	}
	else $errorCode = 41;

	if ($errorCode === 0)
	{
		// <editor-fold defaultstate="collapsed" desc="render chart">
		ob_start();
		ShowImageHeader($imageHandle);
		$img_base64 = base64_encode(ob_get_contents());
		ob_end_clean();
		if (substr($img_base64, 0, 5) === 'iVBOR')
		{
			$imageData = 'data:image/png;base64,'.PHP_EOL.chunk_split($img_base64);
			$response = array(
				'errorCode' => 0,
				'errorMessage' => '',
				'imageData' => $imageData,
				'legendInfo' => $arLegendInfo
			);
		}
		// </editor-fold>
	}
}

if ($errorCode > 0)
{
	$response = array(
		'errorCode' => $errorCode,
		'errorMessage' => CharsetConverter::ConvertCharset(
			GetMessage('REPORT_CHART_ERR_'.sprintf('%02d', $errorCode)), LANG_CHARSET, 'UTF-8'
		)
	);
}

header("Content-type: application/x-www-form-urlencoded; charset=UTF-8");
echo CUtil::PhpToJSObject($response);
?>