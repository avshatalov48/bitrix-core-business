<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

if (!CSalePdf::isPdfAvailable())
	die();

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;
/** @var CSaleTfpdf $pdf */
$pdf = new CSalePdf('P', 'pt', 'A4');

if ($params['BILLDE_BACKGROUND'])
{
	$pdf->SetBackground(
		$params['BILLDE_BACKGROUND'],
		$params['BILLDE_BACKGROUND_STYLE']
	);
}

$pageWidth  = $pdf->GetPageWidth();
$pageHeight = $pdf->GetPageHeight();

$pdf->AddFont('Font', '', 'pt_sans-regular.ttf', true);
$pdf->AddFont('Font', 'B', 'pt_sans-bold.ttf', true);

$fontFamily = 'Font';
$fontSize   = 10.5;

$margin = array(
	'top' => intval($params['BILLDE_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILLDE_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILLDE_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILLDE_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

$pdf->SetDisplayMode(100, 'continuous');
$pdf->SetMargins($margin['left'], $margin['top'], $margin['right']);
$pdf->SetAutoPageBreak(true, $margin['bottom']);

$pdf->AddPage();

$y0 = $pdf->GetY();
$logoHeight = 0;
$logoWidth = 0;

if ($params['BILLDE_PATH_TO_LOGO'])
{
	list($imageHeight, $imageWidth) = $pdf->GetImageSize($params['BILLDE_PATH_TO_LOGO']);

	$imgDpi = intval($params['BILLDE_LOGO_DPI']) ?: 96;
	$imgZoom = 96 / $imgDpi;

	$logoHeight = $imageHeight * $imgZoom + 5;
	$logoWidth  = $imageWidth * $imgZoom + 5;

	if ($logoWidth >= $width)
	{
		$imgDpi = 96 * $imageWidth/($width*0.6 + 5);
		$imgZoom = 96 / $imgDpi;

		$logoHeight = $imageHeight * $imgZoom + 5;
		$logoWidth  = $imageWidth * $imgZoom + 5;
	}

	$pdf->Image($params['BILLDE_PATH_TO_LOGO'], $pdf->GetX(), $pdf->GetY(), -$imgDpi, -$imgDpi);
}

$pdf->Ln(10);
if ($params["SELLER_COMPANY_NAME"])
{
	$pdf->SetFont($fontFamily, 'B', $fontSize);
	$pdf->SetX($pdf->GetX() + $logoWidth);
	$pdf->MultiCell(0, 30, CSalePdf::prepareToPdf($params["SELLER_COMPANY_NAME"]), 0, 'L');
	$pdf->Ln();
	$pdf->SetY(max($y0 + $logoHeight, $pdf->GetY()));
	$pdf->Ln(10);
}

$pdf->SetFont($fontFamily, 'B', $fontSize-2);
$pdf->SetY(max($y0 + $logoHeight, $pdf->GetY()));

$seller = $params["SELLER_COMPANY_NAME"];
if ($params["SELLER_COMPANY_ADDRESS"])
{
	$sellerAddr = $params["SELLER_COMPANY_ADDRESS"];
	if (is_array($sellerAddr))
		$sellerAddr = implode(', ', $sellerAddr);
	else
		$sellerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerAddr));
	$seller .= ' - ';
	$seller .= $sellerAddr;
	$seller .= '  ';
}

if ($seller)
{
$text = CSalePdf::prepareToPdf($seller);
while ($pdf->GetStringWidth($text))
{
	list($string, $text) = $pdf->splitString($text, $width);
	$pdf->Cell($pdf->GetStringWidth($string), 10, $string, 'B');
	$pdf->Ln();
}
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
}

if ($params["BUYER_PERSON_COMPANY_NAME"])
{
	$pdf->SetFont($fontFamily, 'B', $fontSize);
	$pdf->Write(15, CSalePdf::prepareToPdf($params["BUYER_PERSON_COMPANY_NAME"]));
	$pdf->Ln();
	$pdf->SetFont($fontFamily, '', $fontSize);
	if ($params["BUYER_PERSON_COMPANY_PAYER_NAME"])
	{
		$pdf->Write(15, CSalePdf::prepareToPdf($params["BUYER_PERSON_COMPANY_PAYER_NAME"]));
		$pdf->Ln();
	}
	$buyerAddress = $params["BUYER_PERSON_COMPANY_ADDRESS"];
	if($buyerAddress)
	{
		if(is_string($buyerAddress))
		{
			$buyerAddress = explode("\n", str_replace(array("\r\n", "\n", "\r"), "\n", $buyerAddress));
			if (count($buyerAddress) === 1)
				$buyerAddress = $buyerAddress[0];
		}
		if (is_array($buyerAddress))
		{
			if (!empty($buyerAddress))
			{
				foreach ($buyerAddress as $item)
				{
					$pdf->Write(15, CSalePdf::prepareToPdf($item));
					$pdf->Ln();
				}
				unset($item);
			}
		}
		else
		{
			$pdf->Write(15, CSalePdf::prepareToPdf($buyerAddress));
			$pdf->Ln();
		}
	}
}

$pdf->Ln();

if ($params['BILLDE_HEADER'])
{
	$pdf->SetFont($fontFamily, 'B', $fontSize * 2);
	$pdf->Write(15, CSalePdf::prepareToPdf($params['BILLDE_HEADER']));

	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
}
if ($params['BILLDE_HEADER_SHOW'] === 'Y')
{
	$pdf->SetFont($fontFamily, 'B', $fontSize);

	$pdf->Cell(0.35*$width, 15, CSalePdf::prepareToPdf(sprintf(
		$params['BILLDE_HEADER'].' Nr. %s',
		$params["ACCOUNT_NUMBER"]
	)));

	if ($params["BUYER_PERSON_COMPANY_ID"])
	{
		$pdf->Cell(0.35*$width, 15, CSalePdf::prepareToPdf(sprintf(
			'Kunden-Nr.: %s',
				$params["BUYER_PERSON_COMPANY_ID"]
		)));
	}

	$pdf->Cell(0, 15, CSalePdf::prepareToPdf(sprintf(
		'Datum: %s',
			$params["DATE_INSERT"]
	)), 0, 0, 'R');
	$pdf->Ln();

	if ($params["DATE_PAY_BEFORE"])
	{
		$pdf->Cell(0, 15, CSalePdf::prepareToPdf(sprintf(
			'Bezahlen bis: %s',
			ConvertDateTime($params["DATE_PAY_BEFORE"], FORMAT_DATE)
				?: $params["DATE_PAY_BEFORE"]
		)), 0, 0, 'R');
		$pdf->Ln();
	}

	$pdf->SetFont($fontFamily, 'B', $fontSize-2);
	$pdf->Write(15, CSalePdf::prepareToPdf('Bitte bei Zahlungen und Schriftverkehr angeben!'));

	$pdf->Ln();
	$pdf->Ln();
}

$pdf->SetFont($fontFamily, '', $fontSize);

$columnList = array('NUMBER', 'NAME', 'QUANTITY', 'MEASURE', 'PRICE', 'VAT_RATE', 'SUM');
$arCols = array();
foreach ($columnList as $column)
{
	if ($params['BILLDE_COLUMN_'.$column.'_SHOW'] == 'Y')
	{
		$arCols[$column] = array(
			'NAME' => CSalePdf::prepareToPdf($params['BILLDE_COLUMN_'.$column.'_TITLE']),
			'SORT' => $params['BILLDE_COLUMN_'.$column.'_SORT']
		);
	}
}
if ($params['USER_COLUMNS'])
{
	$columnList = array_merge($columnList, array_keys($params['USER_COLUMNS']));
	foreach ($params['USER_COLUMNS'] as $id => $val)
	{
		$arCols[$id] = array(
			'NAME' => CSalePdf::prepareToPdf($val['NAME']),
			'SORT' => $val['SORT']
		);
	}
}

uasort($arCols, function ($a, $b) {return ($a['SORT'] < $b['SORT']) ? -1 : 1;});

$arColumnKeys = array_keys($arCols);
$columnCount = count($arColumnKeys);

if ($params['BASKET_ITEMS'])
{
	$arCells = array();
	$arProps = array();

	$n = 0;
	$sum = 0.00;
	$vat = 0;
	$vats = array();

	foreach ($params['BASKET_ITEMS'] as $basketItem)
	{
		// @TODO: replace with real vatless price
		if ($basketItem['IS_VAT_IN_PRICE'])
			$vatLessPrice = roundEx($basketItem['PRICE'] / (1 + $basketItem['VAT_RATE']), SALE_VALUE_PRECISION);
		else
			$vatLessPrice = $basketItem['PRICE'];

		$productName = $basketItem['NAME'];
		if ($productName == "OrderDelivery")
			$productName = "Schifffahrt";
		else if ($productName == "OrderDiscount")
			$productName = "Rabatt";

		$arCells[++$n] = array();
		foreach ($arCols as $columnId => $cols)
		{
			$data = null;

			switch ($columnId)
			{
				case 'NUMBER':
					$data = CSalePdf::prepareToPdf($n);
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'NAME':
					$data = CSalePdf::prepareToPdf($productName);
					break;
				case 'QUANTITY':
					$data = CSalePdf::prepareToPdf(roundEx($basketItem['QUANTITY'], SALE_VALUE_PRECISION));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'MEASURE':
					$data = CSalePdf::prepareToPdf($basketItem["MEASURE_NAME"] ? $basketItem["MEASURE_NAME"] : 'St.');
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'PRICE':
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($vatLessPrice, $basketItem['CURRENCY'], false));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'VAT_RATE':
					$data = CSalePdf::prepareToPdf(roundEx($basketItem['VAT_RATE']*100, SALE_VALUE_PRECISION)."%");
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'SUM':
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($vatLessPrice * $basketItem['QUANTITY'], $basketItem['CURRENCY'], false));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				default :
					if (preg_match('/[^0-9 ,\.]/', $basketItem[$columnId]) === 0)
					{
						if (!array_key_exists('IS_DIGIT', $arCols[$columnId]))
							$arCols[$columnId]['IS_DIGIT'] = true;
					}
					else
					{
						$arCols[$columnId]['IS_DIGIT'] = false;
					}
					$data = ($basketItem[$columnId]) ? CSalePdf::prepareToPdf($basketItem[$columnId]) : '';
			}
			if ($data !== null)
				$arCells[$n][$columnId] = $data;
		}

		$arProps[$n] = array();

		foreach ($basketItem['PROPS'] as $basketPropertyItem)
		{
			if ($basketPropertyItem['CODE'] == 'CATALOG.XML_ID' || $basketPropertyItem['CODE'] == 'PRODUCT.XML_ID')
				continue;
			$arProps[$n][] = htmlspecialcharsbx(sprintf("%s: %s", $basketPropertyItem["NAME"], $basketPropertyItem["VALUE"]));
		}

		$sum += doubleval($vatLessPrice * $basketItem['QUANTITY']);
		$vat = max($vat, $basketItem['VAT_RATE']);
		if ($basketItem['VAT_RATE'] > 0)
		{
			if (!isset($vats[$basketItem['VAT_RATE']]))
				$vats[$basketItem['VAT_RATE']] = 0;

			if ($basketItem['IS_VAT_IN_PRICE'])
				$vats[$basketItem['VAT_RATE']] += ($basketItem['PRICE'] - $vatLessPrice) * $basketItem['QUANTITY'];
			else
				$vats[$basketItem['VAT_RATE']] += ($basketItem['PRICE']*(1 + $basketItem['VAT_RATE']) - $vatLessPrice) * $basketItem['QUANTITY'];
		}
	}

	if ($vat <= 0)
	{
		unset($arCols['VAT_RATE']);
		$columnCount = count($arCols);
		$arColumnKeys = array_keys($arCols);
		foreach ($arCells as $i => $cell)
			unset($arCells[$i]['VAT_RATE']);
	}

	if ($params['DELIVERY_PRICE'] > 0)
	{
		$sDeliveryItem = "Schifffahrt";
		if ($params['DELIVERY_NAME'] <> '')
			$sDeliveryItem .= sprintf(" (%s)", $params['DELIVERY_NAME']);
		$arCells[++$n] = array();
		foreach ($arCols as $columnId => $caption)
		{
			$data = null;

			switch ($columnId)
			{
				case 'NUMBER':
					$data = CSalePdf::prepareToPdf($n);
					break;
				case 'NAME':
					$data = CSalePdf::prepareToPdf($sDeliveryItem);
					break;
				case 'QUANTITY':
					$data = CSalePdf::prepareToPdf(1);
					break;
				case 'MEASURE':
					$data = CSalePdf::prepareToPdf('');
					break;
				case 'PRICE':
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($params['DELIVERY_PRICE'] / (1 + $vat), $params['CURRENCY'], false));
					break;
				case 'VAT_RATE':
					$data = CSalePdf::prepareToPdf(roundEx($vat*100, SALE_VALUE_PRECISION)."%");
					break;
				case 'SUM':
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($params['DELIVERY_PRICE'] / (1 + $vat), $params['CURRENCY'], false));
					break;
				default :
					$data = '';
			}
			if ($data !== null)
				$arCells[$n][$columnId] = $data;
		}

		$sum += roundEx(
			doubleval($params['DELIVERY_PRICE'] / (1 + $vat)),
			SALE_VALUE_PRECISION
		);

		if ($vat > 0)
			$vats[$vat] += roundEx(
				$params['DELIVERY_PRICE'] * $vat / (1 + $vat),
				SALE_VALUE_PRECISION
			);
	}

	$items = $n;
	if ($params['BILLDE_TOTAL_SHOW'] === 'Y')
	{
		$eps = 0.0001;
		if ($params['SUM'] - $sum > $eps)
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf("Nettobetrag:");
			$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($sum, $params['CURRENCY'], false));
		}

		if (!empty($vats))
		{
			// @TODO: remove on real vatless price implemented
			$delta = intval(roundEx(
				$params['SUM'] - $sum - array_sum($vats),
				SALE_VALUE_PRECISION
			) * pow(10, SALE_VALUE_PRECISION));

			if ($delta)
			{
				$vatRates = array_keys($vats);
				rsort($vatRates);

				$ful = intval($delta / count($vatRates));
				$ost = $delta % count($vatRates);

				foreach ($vatRates as $vatRate)
				{
					$vats[$vatRate] += ($ful + $ost) / pow(10, SALE_VALUE_PRECISION);

					if ($ost > 0)
						$ost--;
				}
			}

			foreach ($vats as $vatRate => $vatSum)
			{
				$arCells[++$n] = array();
				for ($i = 0; $i < $columnCount; $i++)
					$arCells[$n][$arColumnKeys[$i]] = null;

				$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(sprintf("zzgl. %s%% MwSt:", roundEx($vatRate * 100, SALE_VALUE_PRECISION)));
				$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($vatSum, $params['CURRENCY'], false));
			}
		}
		else
		{
			if ($params['TAXES'])
			{
				foreach ($params['TAXES'] as $tax)
				{
					$arCells[++$n] = array();
					for ($i = 0; $i < $columnCount; $i++)
						$arCells[$n][$arColumnKeys[$i]] = null;

					$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(sprintf(
						"%s%s%s:",
						($tax["IS_IN_PRICE"] == "Y") ? "inkl." : "zzgl.",
						sprintf(' %s%% ', roundEx($tax["VALUE"], SALE_VALUE_PRECISION)),
						$tax["TAX_NAME"]
					));
					$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($tax["VALUE_MONEY"], $params['CURRENCY'], false));
				}
			}
		}

		if ($params['SUM_PAID'] > 0)
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf("Payment made:");
			$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($params['SUM_PAID'], $params['CURRENCY'], false));
		}

		if ($params['DISCOUNT_PRICE'])
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf("Rabatt:");
			$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($params['DISCOUNT_PRICE'], $params['CURRENCY'], false));
		}

		$arCells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$arCells[$n][$arColumnKeys[$i]] = null;

		$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf("Gesamtbetrag:");
		$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($params['SUM'], $params['CURRENCY'], false));
	}

	$rowsInfo = $pdf->calculateRowsWidth($arCols, $arCells, $items, $width);
	$arRowsWidth = $rowsInfo['ROWS_WIDTH'];
	$arRowsContentWidth = $rowsInfo['ROWS_CONTENT_WIDTH'];
}
$pdf->Ln();

$x0 = $pdf->GetX();
$y0 = $pdf->GetY();

$k = 0;
do
{
	$newLine = false;
	foreach ($arCols as $columnId => $column)
	{
		list($string, $arCols[$columnId]['NAME']) = $pdf->splitString($column['NAME'], $arRowsWidth[$columnId]);
		if ($vat > 0 || $columnId !== 'VAT_RATE')
			$pdf->Cell($arRowsWidth[$columnId], 20, $string, 0, 0, $k ? 'L' : 'C');

		if ($arCols[$columnId]['NAME'])
		{
			$k++;
			$newLine = true;
		}

		$i = array_search($columnId, $arColumnKeys);
		${"x".($i+1)} = $pdf->GetX();
	}

	$pdf->Ln();
}
while($newLine);

$y5 = $pdf->GetY();

$pdf->Line($x0, $y0, ${"x".$columnCount}, $y0);
for ($i = 0; $i <= $columnCount; $i++)
{
	if ($vat > 0 || $arColumnKeys[$i] != 'VAT_RATE')
		$pdf->Line(${"x$i"}, $y0, ${"x$i"}, $y5);
}
$pdf->Line($x0, $y5, ${'x'.$columnCount}, $y5);

$rowsCnt = count($arCells);
for ($n = 1; $n <= $rowsCnt; $n++)
{
	$arRowsWidth_tmp = $arRowsWidth;
	$arRowsContentWidth_tmp = $arRowsContentWidth;
	$accumulated = 0;
	$accumulatedContent = 0;
	foreach ($arCols as $columnId => $column)
	{
		if (is_null($arCells[$n][$columnId]))
		{
			$accumulated += $arRowsWidth_tmp[$columnId];
			$arRowsWidth_tmp[$columnId] = null;
			$accumulatedContent += $arRowsContentWidth_tmp[$columnId];
			$arRowsContentWidth_tmp[$columnId] = null;
		}
		else
		{
			$arRowsWidth_tmp[$columnId] += $accumulated;
			$arRowsContentWidth_tmp[$columnId] += $accumulatedContent;
			$accumulated = 0;
			$accumulatedContent = 0;
		}
	}

	$x0 = $pdf->GetX();
	$y0 = $pdf->GetY();

	$pdf->SetFont($fontFamily, '', $fontSize);

	$l = 0;
	do
	{
		$newLine = false;
		foreach ($arCols as $columnId => $column)
		{
			$string = '';
			if (!is_null($arCells[$n][$columnId]))
				list($string, $arCells[$n][$columnId]) = $pdf->splitString($arCells[$n][$columnId], $arRowsContentWidth_tmp[$columnId]);

			$rowWidth = $arRowsWidth_tmp[$columnId];

			if (in_array($columnId, array('QUANTITY', 'MEASURE', 'PRICE', 'SUM')))
			{
				if (!is_null($arCells[$n][$columnId]))
				{
					$pdf->Cell($rowWidth, 15, $string, 0, 0, 'R');
				}
			}
			elseif ($columnId == 'NUMBER')
			{
				if (!is_null($arCells[$n][$columnId]))
					$pdf->Cell($rowWidth, 15, ($l == 0) ? $string : '', 0, 0, 'C');
			}
			elseif ($columnId == 'NAME')
			{
				if (!is_null($arCells[$n][$columnId]))
					$pdf->Cell($rowWidth, 15, $string, 0, 0,  ($n > $items) ? 'R' : '');
			}
			elseif ($columnId == 'VAT_RATE')
			{
				if (!is_null($arCells[$n][$columnId]))
					$pdf->Cell($rowWidth, 15, $string, 0, 0, 'R');
			}
			else
			{
				if (!is_null($arCells[$n][$columnId]))
				{
					$pdf->Cell($rowWidth, 15, $string, 0, 0, ($n > $items) ? 'R' : 'L');
				}
			}

			if ($l == 0)
			{
				$pos = array_search($columnId, $arColumnKeys);
				${'x'.($pos+1)} = $pdf->GetX();
			}

			if ($arCells[$n][$columnId])
				$newLine = true;
		}

		$pdf->Ln();
		$l++;
	}
	while($newLine);

	if ($params['BILLDE_COLUMN_NAME_SHOW'] == 'Y')
	{
		if (isset($arProps[$n]) && is_array($arProps[$n]))
		{
			$pdf->SetFont($fontFamily, '', $fontSize-2);
			foreach ($arProps[$n] as $property)
			{
				$i = 0;
				$line = 0;
				foreach ($arCols as $columnId => $col)
				{
					$i++;
					if ($i == $columnCount)
						$line = 1;
					if ($columnId == 'NAME')
						$pdf->Cell($arRowsWidth_tmp[$columnId], 12, $property, 0, $line);
					else
						$pdf->Cell($arRowsWidth_tmp[$columnId], 12, '', 0, $line);
				}
			}
		}
	}
	$y5 = $pdf->GetY();

	if ($y0 > $y5)
		$y0 = $margin['top'];

	for ($i = ($n > $items) ? $columnCount - 1 : 0; $i <= $columnCount; $i++)
	{
		if ($vat > 0 || $arColumnKeys[$i] != 'VAT_RATE')
			$pdf->Line(${"x$i"}, $y0, ${"x$i"}, $y5);
	}

	$pdf->Line(($n <= $items) ? $x0 : ${'x'.($columnCount-1)}, $y5, ${'x'.$columnCount}, $y5);
}
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();


$pdf->SetFont($fontFamily, 'B', $fontSize);

if ($params["BILLDE_COMMENT1"] || $params["BILLDE_COMMENT2"])
{
	$pdf->SetFont($fontFamily, '', $fontSize);

	if ($params["BILLDE_COMMENT1"])
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILLDE_COMMENT1"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}

	if ($params["BILLDE_COMMENT2"])
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILLDE_COMMENT2"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}
}

$pdf->Ln();

if (!$blank && $params['BILLDE_PATH_TO_STAMP'])
{
	list($stampHeight, $stampWidth) = $pdf->GetImageSize($params['BILLDE_PATH_TO_STAMP']);

	if ($stampHeight && $stampWidth)
	{
		if ($stampHeight > 120 || $stampWidth > 120)
		{
			$ratio = 120 / max($stampHeight, $stampWidth);
			$stampHeight = $ratio * $stampHeight;
			$stampWidth  = $ratio * $stampWidth;
		}

		$pdf->Image(
			$params['BILLDE_PATH_TO_STAMP'],
			$margin['left']+40, $pdf->GetY(),
			$stampWidth, $stampHeight
		);
	}
}

if ($params["SELLER_COMPANY_DIRECTOR_POSITION"])
{
	$isDirSign = false;
	if (!$blank && $params['SELLER_COMPANY_DIR_SIGN'])
	{
		list($signHeight, $signWidth) = $pdf->GetImageSize($params['SELLER_COMPANY_DIR_SIGN']);

		if ($signHeight && $signWidth)
		{
			$ratio = min(37.5/$signHeight, 150/$signWidth);
			$signHeight = $ratio * $signHeight;
			$signWidth  = $ratio * $signWidth;

			$isDirSign = true;
		}
	}

	$sellerDirPos = CSalePdf::prepareToPdf($params["SELLER_COMPANY_DIRECTOR_POSITION"]);
	if ($isDirSign && $pdf->GetStringWidth($sellerDirPos) <= 160)
		$pdf->SetY($pdf->GetY() + min($signHeight, 30) - 15);
	$pdf->MultiCell(150, 15, $sellerDirPos, 0, 'L');
	$pdf->SetXY($margin['left'] + 150, $pdf->GetY() - 15);

	if ($isDirSign)
	{
		$pdf->Image(
			$params['SELLER_COMPANY_DIR_SIGN'],
			$pdf->GetX() + 80 - $signWidth/2, $pdf->GetY() - $signHeight + 15,
			$signWidth, $signHeight
		);
	}

	$x1 = $pdf->GetX();
	$pdf->Cell(160, 15, '');
	$x2 = $pdf->GetX();

	if ($params["SELLER_COMPANY_DIRECTOR_NAME"])
		$pdf->Write(15, CSalePdf::prepareToPdf('('.$params["SELLER_COMPANY_DIRECTOR_NAME"].')'));
	$pdf->Ln();

	$y2 = $pdf->GetY();
	$pdf->Line($x1, $y2, $x2, $y2);

	$pdf->Ln();
}

if ($params["SELLER_COMPANY_ACCOUNTANT_POSITION"])
{
	$isAccSign = false;
	if (!$blank && $params['SELLER_COMPANY_ACC_SIGN'])
	{
		list($signHeight, $signWidth) = $pdf->GetImageSize($params['SELLER_COMPANY_ACC_SIGN']);

		if ($signHeight && $signWidth)
		{
			$ratio = min(37.5/$signHeight, 150/$signWidth);
			$signHeight = $ratio * $signHeight;
			$signWidth  = $ratio * $signWidth;

			$isAccSign = true;
		}
	}

	$sellerAccPos = CSalePdf::prepareToPdf($params["SELLER_COMPANY_ACCOUNTANT_POSITION"]);
	if ($isAccSign && $pdf->GetStringWidth($sellerAccPos) <= 160)
		$pdf->SetY($pdf->GetY() + min($signHeight, 30) - 15);
	$pdf->MultiCell(150, 15, $sellerAccPos, 0, 'L');
	$pdf->SetXY($margin['left'] + 150, $pdf->GetY() - 15);

	if ($isAccSign)
	{
		$pdf->Image(
				$params['SELLER_COMPANY_ACC_SIGN'],
			$pdf->GetX() + 80 - $signWidth/2, $pdf->GetY() - $signHeight + 15,
			$signWidth, $signHeight
		);
	}

	$x1 = $pdf->GetX();
	$pdf->Cell(($params["SELLER_COMPANY_DIRECTOR_NAME"]) ? $x2-$x1 : 160, 15, '');
	$x2 = $pdf->GetX();

	if ($params["SELLER_COMPANY_ACCOUNTANT_NAME"])
		$pdf->Write(15, CSalePdf::prepareToPdf('('.$params["SELLER_COMPANY_ACCOUNTANT_NAME"].')'));
	$pdf->Ln();

	$y2 = $pdf->GetY();
	$pdf->Line($x1, $y2, $x2, $y2);
}

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();


$pdf->SetFont($fontFamily, '', $fontSize-2);

$sellerName = $params["SELLER_COMPANY_NAME"];
$sellerAddr = $params["SELLER_COMPANY_ADDRESS"];
if (is_array($sellerAddr))
	$sellerAddr = implode(', ', $sellerAddr);
else
	$sellerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerAddr));

$sellerData = array();

if ($sellerName)
	$sellerData[] = $sellerName;
if ($sellerAddr)
	$sellerData[] = $sellerAddr;

if (!empty($sellerData))
{
	$text = CSalePdf::prepareToPdf(join(' - ', $sellerData));
	while ($pdf->GetStringWidth($text))
	{
		list($string, $text) = $pdf->splitString($text, $width);
		$pdf->Cell(0, 15, $string, 0, 0, 'C');
		$pdf->Ln();
	}
}


$sellerPhone = $params["SELLER_COMPANY_PHONE"];
$sellerEmail = $params["SELLER_COMPANY_EMAIL"];

$sellerData = array();

if ($sellerPhone)
	$sellerData[] = sprintf('Telefon: %s', $sellerPhone);
if ($sellerEmail)
	$sellerData[] = sprintf('Mail: %s', $sellerEmail);

if (!empty($sellerData))
{
	$text = CSalePdf::prepareToPdf(join(' - ', $sellerData));
	while ($pdf->GetStringWidth($text))
	{
		list($string, $text) = $pdf->splitString($text, $width);
		$pdf->Cell(0, 15, $string, 0, 0, 'C');
		$pdf->Ln();
	}
}


$bankAccNo = $params["SELLER_COMPANY_BANK_ACCOUNT"];
$bankBlz   = $params["SELLER_COMPANY_BANK_BIC"];
$bankIban  = $params["SELLER_COMPANY_BANK_IBAN"];
$bankSwift = $params["SELLER_COMPANY_BANK_SWIFT"];
$bank      = $params["SELLER_COMPANY_BANK_NAME"];

$bankData = array();

if ($bankAccNo)
	$bankData[] = sprintf('Konto Nr.: %s', $bankAccNo);
if ($bankBlz)
	$bankData[] = sprintf('BLZ: %s', $bankBlz);
if ($bankIban)
	$bankData[] = sprintf('IBAN: %s', $bankIban);
if ($bankSwift)
	$bankData[] = sprintf('BIC/SWIFT: %s', $bankSwift);
if ($bank)
	$bankData[] = $bank;

if (!empty($bankData))
{
	$text = CSalePdf::prepareToPdf(join(' - ', $bankData));
	while ($pdf->GetStringWidth($text))
	{
		list($string, $text) = $pdf->splitString($text, $width);
		$pdf->Cell(0, 15, $string, 0, 0, 'C');
		$pdf->Ln();
	}
}


$sellerEuInn = $params["SELLER_COMPANY_EU_INN"];
$sellerInn   = $params["SELLER_COMPANY_INN"];
$sellerReg   = $params["SELLER_COMPANY_REG"];
$sellerDir   = $params["SELLER_COMPANY_DIRECTOR_NAME"];

$sellerData = array();

if ($sellerEuInn)
	$sellerData[] = sprintf('USt-IdNr.: %s', $sellerEuInn);
if ($sellerInn)
	$sellerData[] = sprintf('Steuernummer: %s', $sellerInn);
if ($sellerReg)
	$sellerData[] = $sellerReg;
if ($sellerDir)
	$sellerData[] = $sellerDir;

if (!empty($sellerData))
{
	$pdf->Cell(0, 15, CSalePdf::prepareToPdf(join(' - ', $sellerData)), 0, 0, 'C');
	$pdf->Ln();
}


$dest = 'I';
if ($_REQUEST['GET_CONTENT'] == 'Y')
	$dest = 'S';
else if ($_REQUEST['DOWNLOAD'] == 'Y')
	$dest = 'D';

$fileName = sprintf(
	'Rechnung Nr. %s (Datum %s).pdf',
	str_replace(
		array(
			chr(0), chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), chr(8), chr(9), chr(10), chr(11),
			chr(12), chr(13), chr(14), chr(15), chr(16), chr(17), chr(18), chr(19), chr(20), chr(21), chr(22),
			chr(23), chr(24), chr(25), chr(26), chr(27), chr(28), chr(29), chr(30), chr(31),
			'"', '*', '/', ':', '<', '>', '?', '\\', '|'
		),
		'_',
		strval($params["ACCOUNT_NUMBER"])
	),
	ConvertDateTime($params["DATE_BILL"], 'YYYY-MM-DD')
);

$trFileName = CUtil::translit($fileName, 'de', array('max_len' => 1024, 'safe_chars' => '.', 'replace_space' => '-'));

return $pdf->Output($trFileName, $dest, $fileName);
?>