<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/** @var CBitrixComponentTemplate $this */
$this->IncludeLangFile('template.php');


$isStExport = (is_array($arResult['STEXPORT_OPTIONS']) && !empty($arResult['STEXPORT_OPTIONS']));
$isStExportFirstPage = false;
$isStExportLastPage = false;
if ($isStExport)
{
	$isStExportFirstPage = (isset($arResult['STEXPORT_OPTIONS']['STEXPORT_IS_FIRST_PAGE'])
		&& $arResult['STEXPORT_OPTIONS']['STEXPORT_IS_FIRST_PAGE'] === 'Y');
	$isStExportLastPage = (isset($arResult['STEXPORT_OPTIONS']['STEXPORT_IS_LAST_PAGE'])
		&& $arResult['STEXPORT_OPTIONS']['STEXPORT_IS_LAST_PAGE'] === 'Y');
}


$redSignMap = array();
$rowNum = 0;
foreach ($arResult['data'] as &$row)
{
	$colNum = 0;
	foreach($arResult['viewColumns'] as $col)
	{
		if (isset($arResult['customChartData'][$rowNum][$col['resultName']]['multiple']))
		{
			$customValueInfo = &$arResult['customChartData'][$rowNum][$col['resultName']];
			if ($customValueInfo['multiple'] === true)
			{
				$dataValue = 0;
				foreach ($customValueInfo as $cvKey => $cvInfo)
				{
					if ($cvKey !== 'multiple' && isset($cvInfo['type'])
						&& ($cvInfo['type'] === 'float' || $cvInfo['type'] === 'integer'))
					{
						if ($cvInfo['value'] < 0)
						{
							$redSignMap[$rowNum][$colNum] = true;
							break;
						}
					}
				}
				unset($cvKey, $cvInfo);
			}
			else
			{
				$cvInfo = &$customValueInfo[0];
				if (isset($cvInfo['type'])
					&& ($cvInfo['type'] === 'float' || $cvInfo['type'] === 'integer'))
				{
					if ($cvInfo['value'] < 0)
						$redSignMap[$rowNum][$colNum] = true;
				}
				unset($cvInfo);
			}
			unset($customValueInfo);
		}

		if (is_array($row[$col['resultName']]))
		{
			$row[$col['resultName']] = join(' / ', $row[$col['resultName']]);
		}
		$colNum++;
	}
	$rowNum++;
}
unset($row);

if (!$isStExport || $isStExportFirstPage)
{
?>
<meta http-equiv="Content-type" content="text/html;charset=<? echo LANG_CHARSET ?>"/>
<style type="text/css">
	.report-red-neg-val {
		color: red;
	}
</style>
<table border="1">
<thead>
<tr>
	<? foreach ($arResult['viewColumns'] as $colId => $col): ?>
		<th><?= htmlspecialcharsbx($col['humanTitle']) ?></th>
	<? endforeach; ?>
</tr>
</thead>
<tbody><?
} // (!$isStExport || $isStExportFirstPage)
$rowNum = 0;
foreach ($arResult['data'] as $row)
{
	?>
	<tr>
		<? $colNum = 0; ?>
		<? foreach ($arResult['viewColumns'] as $col): ?>
			<?php
			$td_class = '';
			if($arResult['settings']['red_neg_vals'] === true)
			{
				$finalValue = $row[$col['resultName']];
				if(isset($redSignMap[$rowNum][$colNum]) || (is_numeric($finalValue) && $finalValue < 0))
				{
					$td_class = ' class="report-red-neg-val"';
				}
			}
			?>
			<td<?= $td_class ?>><?= $row[$col['resultName']] ?></td>
			<? $colNum++; ?>
		<? endforeach; ?>
	</tr>
	<?
	$rowNum++;
}
if (!$isStExport || $isStExportLastPage)
{
?>
</tbody>
</table><?
} // !$isStExport || $isStExportLastPage
unset($redSignMap);