<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

if (!CSalePdf::isPdfAvailable())
	die();

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;
/** @var CSaleTfpdf $pdf */
$pdf = new CSalePdf('P', 'pt', 'A4');

if ($params['BILLEN_BACKGROUND'])
{
	$pdf->SetBackground(
		$params['BILLEN_BACKGROUND'],
		$params['BILLEN_BACKGROUND_STYLE']
	);
}

$pageWidth  = $pdf->GetPageWidth();
$pageHeight = $pdf->GetPageHeight();

$pdf->AddFont('Font', '', 'pt_sans-regular.ttf', true);
$pdf->AddFont('Font', 'B', 'pt_sans-bold.ttf', true);

$fontFamily = 'Font';
$fontSize   = 10.5;

$margin = array(
	'top' => intval($params['BILLEN_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILLEN_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILLEN_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILLEN_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

$pdf->SetDisplayMode(100, 'continuous');
$pdf->SetMargins($margin['left'], $margin['top'], $margin['right']);
$pdf->SetAutoPageBreak(true, $margin['bottom']);

$pdf->AddPage();


$y0 = $pdf->GetY();
$logoHeight = 0;
$logoWidth = 0;

if ($params['BILLEN_PATH_TO_LOGO'])
{
	list($imageHeight, $imageWidth) = $pdf->GetImageSize($params['BILLEN_PATH_TO_LOGO']);

	$imgDpi = intval($params['BILLEN_LOGO_DPI']) ?: 96;
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

	$pdf->Image($params['BILLEN_PATH_TO_LOGO'], $pdf->GetX(), $pdf->GetY(), -$imgDpi, -$imgDpi);
}

$pdf->SetFont($fontFamily, 'B', $fontSize);

$text = CSalePdf::prepareToPdf($params["SELLER_COMPANY_NAME"]);
$textWidth = $width - $logoWidth;
while ($pdf->GetStringWidth($text))
{
	list($string, $text) = $pdf->splitString($text, $textWidth);
	$pdf->SetX($pdf->GetX() + $logoWidth);
	$pdf->Cell($textWidth, 15, $string, 0, 0, 'L');
	$pdf->Ln();
}

if ($params["SELLER_COMPANY_ADDRESS"])
{
	$sellerAddress = $params["SELLER_COMPANY_ADDRESS"];
	if (is_string($sellerAddress))
	{
		$sellerAddress = explode("\n", str_replace(array("\r\n", "\n", "\r"), "\n", $sellerAddress));
		if (count($sellerAddress) === 1)
			$sellerAddress = $sellerAddress[0];
	}
	if (is_array($sellerAddress))
	{
		if (!empty($sellerAddress))
		{
			foreach ($sellerAddress as $item)
			{
				$text = $pdf->prepareToPdf($item);
				$textWidth = $width - $logoWidth;
				while ($pdf->GetStringWidth($text))
				{
					list($string, $text) = $pdf->splitString($text, $textWidth);
					$pdf->SetX($pdf->GetX() + $logoWidth);
					$pdf->Cell($textWidth, 15, $string, 0, 0, 'L');
					$pdf->Ln();
				}
			}
			unset($item);
		}
	}
	else
	{
		$text = $pdf->prepareToPdf($sellerAddress);
		$textWidth = $width - $logoWidth;
		while ($pdf->GetStringWidth($text))
		{
			list($string, $text) = $pdf->splitString($text, $textWidth);
			$pdf->SetX($pdf->GetX() + $logoWidth);
			$pdf->Cell($textWidth, 15, $string, 0, 0, 'L');
			$pdf->Ln();
		}
	}
}

if ($params["SELLER_COMPANY_PHONE"])
{
	$pdf->Ln();
	$text = CSalePdf::prepareToPdf(sprintf("Tel.: %s", $params["SELLER_COMPANY_PHONE"]));
	$textWidth = $width - $logoWidth;
	while ($pdf->GetStringWidth($text))
	{
		list($string, $text) = $pdf->splitString($text, $textWidth);
		$pdf->SetX($pdf->GetX() + $logoWidth);
		$pdf->Cell($textWidth, 15, $string, 0, 0, 'L');
		$pdf->Ln();
	}
}

$pdf->Ln();
$pdf->SetY(max($y0 + $logoHeight, $pdf->GetY()));
$pdf->Ln();

if ($params['BILLEN_HEADER'])
{
	$pdf->SetFont($fontFamily, 'B', $fontSize * 2);
	$pdf->Cell(0, 15, CSalePdf::prepareToPdf($params['BILLEN_HEADER']), 0, 0, 'C');

	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
}
$pdf->SetFont($fontFamily, 'B', $fontSize);

if ($params['BILLEN_PAYER_SHOW'] === 'Y')
{
	if ($params["BUYER_PERSON_COMPANY_NAME"])
	{
		$pdf->Write(15, CSalePdf::prepareToPdf('To'));
	}

	$pdf->SetFont($fontFamily, '', $fontSize);

	$invoiceNo = CSalePdf::prepareToPdf($params["ACCOUNT_NUMBER"]);
	$invoiceNoWidth = $pdf->GetStringWidth($invoiceNo);

	$invoiceDate = CSalePdf::prepareToPdf($params["DATE_INSERT"]);
	$invoiceDateWidth = $pdf->GetStringWidth($invoiceDate);

	$invoiceDueDate = CSalePdf::prepareToPdf(
		ConvertDateTime($params["DATE_PAY_BEFORE"], FORMAT_DATE)
			?: $params["DATE_PAY_BEFORE"]
	);
	$invoiceDueDateWidth = $pdf->GetStringWidth($invoiceDueDate);

	$invoiceInfoWidth = max($invoiceNoWidth, $invoiceDateWidth, $invoiceDueDateWidth);

	$headerTitle = CSalePdf::prepareToPdf($params['BILLEN_HEADER'].' # ');
	$issueDateTitle = CSalePdf::prepareToPdf('Issue Date ');
	$dueDateTitle = CSalePdf::prepareToPdf('Due Date ');
	$titleWidth = max($pdf->GetStringWidth($headerTitle), $pdf->GetStringWidth($issueDateTitle), $pdf->GetStringWidth($dueDateTitle));

	$pdf->Cell(0, 15);
	$invoiceInfoValueX = $pdf->GetX() - $invoiceInfoWidth - 6;
	$invoiceInfoTitleX = $invoiceInfoValueX - $titleWidth - 3;
	$pdf->SetX($invoiceInfoValueX);
	$pdf->Write(15, $invoiceNo);

	$pdf->SetFont($fontFamily, 'B', $fontSize);

	$pdf->SetX($invoiceInfoTitleX);
	$pdf->Write(15, $headerTitle);
	$pdf->Ln();

	$pdf->SetFont($fontFamily, '', $fontSize);

	if ($params["BUYER_PERSON_COMPANY_NAME"])
	{
		$pdf->Write(15, CSalePdf::prepareToPdf($params["BUYER_PERSON_COMPANY_NAME"]));
	}

	$pdf->Cell(0, 15);
	$pdf->SetX($invoiceInfoValueX);
	$pdf->Write(15, $invoiceDate);

	$pdf->SetFont($fontFamily, 'B', $fontSize);

	$pdf->SetX($invoiceInfoTitleX);
	$pdf->Write(15, $issueDateTitle);
	$pdf->Ln();
	$invoiceInfoY = $pdf->GetY();
}
$pdf->SetFont($fontFamily, '', $fontSize);

if ($params["BUYER_PERSON_COMPANY_NAME"])
{
	if ($params["BUYER_PERSON_COMPANY_ADDRESS"])
	{
		$buyerAddress = $params["BUYER_PERSON_COMPANY_ADDRESS"];
		if($buyerAddress)
		{
			if (is_string($buyerAddress))
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

	if ($params['BUYER_PERSON_COMPANY_PHONE'])
	{
		$pdf->Write(15, CSalePdf::prepareToPdf("Tel.: ".$params['BUYER_PERSON_COMPANY_PHONE']));
		$pdf->Ln();
	}

	if ($params['BUYER_PERSON_COMPANY_FAX'])
	{
		$pdf->Write(15, CSalePdf::prepareToPdf("Fax: ".$params['BUYER_PERSON_COMPANY_FAX']));
		$pdf->Ln();
	}

	if ($params['BUYER_PERSON_COMPANY_NAME_CONTACT'])
	{
		$pdf->Write(15, CSalePdf::prepareToPdf($params['BUYER_PERSON_COMPANY_NAME_CONTACT']));
		$pdf->Ln();
	}
}
if ($params['BILLEN_PAYER_SHOW'] === 'Y')
{
	if ($params["DATE_PAY_BEFORE"])
	{
		$lastY = $pdf->GetY();
		$pdf->SetY($invoiceInfoY);
		$pdf->SetFont($fontFamily, '', $fontSize);
		$pdf->Cell(0, 15);
		$pdf->SetX($invoiceInfoValueX);
		$pdf->Write(15, $invoiceDueDate);

		$pdf->SetFont($fontFamily, 'B', $fontSize);

		$pdf->SetX($invoiceInfoTitleX);
		$pdf->Write(15, $dueDateTitle);
		$pdf->SetY($lastY);
	}
}

$pdf->Ln();
$pdf->Ln();

$pdf->SetFont($fontFamily, '', $fontSize);

$columnList = array('NUMBER', 'NAME', 'QUANTITY', 'MEASURE', 'PRICE', 'VAT_RATE', 'SUM');

$arCols = array();
foreach ($columnList as $column)
{
	if ($params['BILLEN_COLUMN_'.$column.'_SHOW'] == 'Y')
	{
		$arCols[$column] = array(
			'NAME' => CSalePdf::prepareToPdf($params['BILLEN_COLUMN_'.$column.'_TITLE']),
			'SORT' => $params['BILLEN_COLUMN_'.$column.'_SORT']
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

		$productName = $basketItem["NAME"];
		if ($productName == "OrderDelivery")
			$productName = "Shipping";
		else if ($productName == "OrderDiscount")
			$productName = "Discount";

		$arCells[++$n] = array();
		foreach ($arCols as $columnId => $col)
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
					$data = CSalePdf::prepareToPdf($basketItem["MEASURE_NAME"] ? $basketItem["MEASURE_NAME"] : 'pcs');
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'PRICE':
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($vatLessPrice, $basketItem['CURRENCY'], false));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'VAT_RATE':
					$data = CSalePdf::prepareToPdf(roundEx($basketItem['VAT_RATE']*100, SALE_VALUE_PRECISION)."%");
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

		if ($basketItem['PROPS'])
		{
			foreach ($basketItem['PROPS'] as $basketItemProperty)
			{
				if ($basketItemProperty['CODE'] == 'CATALOG.XML_ID' || $basketItemProperty['CODE'] == 'PRODUCT.XML_ID')
					continue;

				$arProps[$n][] = CSalePdf::prepareToPdf(sprintf("%s: %s", $basketItemProperty["NAME"], $basketItemProperty["VALUE"]));
			}
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
		$sDeliveryItem = "Shipping";
		if ($params['DELIVERY_NAME'])
			$sDeliveryItem .= sprintf(" (%s)", $params['DELIVERY_NAME']);

		$arCells[++$n] = array();
		foreach ($arCols as $columnId => $col)
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
				default:
					$data = CSalePdf::prepareToPdf('');
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
	if ($params['BILLEN_TOTAL_SHOW'] == 'Y')
	{
		$eps = 0.0001;
		if ($params['SUM'] - $sum > $eps)
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf("Subtotal:");
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

				$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(sprintf("Tax (%s%%):", roundEx($vatRate * 100, SALE_VALUE_PRECISION)));
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
						($tax["IS_IN_PRICE"] == "Y") ? "Included " : "",
						$tax["TAX_NAME"],
						sprintf(' (%s%%)', roundEx($tax["VALUE"], SALE_VALUE_PRECISION))
					));
					$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($tax["VALUE_MONEY"], $params['CURRENCY'], false));
				}
			}
			else
			{
				$arCells[++$n] = array();
				for ($i = 0; $i < $columnCount; $i++)
					$arCells[$n][$arColumnKeys[$i]] = null;

				$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf("Tax (0%):");
				$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency(0, $params['CURRENCY'], false));
			}
		}

		if ($params['SUM_PAID'])
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf("Payment made:");
			$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($params['SUM_PAID'], $params['CURRENCY'], false));
		}

		if ($params['DISCOUNT_PRICE'] > 0)
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf("Discount:");
			$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($params['DISCOUNT_PRICE'], $params['CURRENCY'], false));
		}

		$arCells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$arCells[$n][$arColumnKeys[$i]] = null;

		$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf("Total:");
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
		list($string, $arCols[$columnId]['NAME']) = $pdf->splitString($column['NAME'], $arRowsContentWidth[$columnId]);
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
					$pdf->Cell($rowWidth, 15, $string, 0, 0,   ($n > $items) ? 'R' : 'L');
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


	if ($params['BILLEN_COLUMN_NAME_SHOW'] == 'Y')
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
$pdf->Ln();


$pdf->SetFont($fontFamily, 'B', $fontSize);

if ($params["BILLEN_COMMENT1"] || $params["BILLEN_COMMENT2"])
{
	$pdf->Write(15, CSalePdf::prepareToPdf('Terms & Conditions'));
	$pdf->Ln();

	$pdf->SetFont($fontFamily, '', $fontSize);

	if ($params["BILLEN_COMMENT1"])
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILLEN_COMMENT1"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}

	if ($params["BILLEN_COMMENT2"])
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILLEN_COMMENT2"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}
}

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

if ($params['BILLEN_PATH_TO_STAMP'])
{
	$filePath = $pdf->GetImagePath($params['BILLEN_PATH_TO_STAMP']);
	if ($filePath != '' && !$blank && \Bitrix\Main\IO\File::isFileExists($filePath))
	{
		list($stampHeight, $stampWidth) = $pdf->GetImageSize($params['BILLEN_PATH_TO_STAMP']);

		if ($stampHeight && $stampWidth)
		{
			if ($stampHeight > 120 || $stampWidth > 120)
			{
				$ratio = 120 / max($stampHeight, $stampWidth);
				$stampHeight = $ratio * $stampHeight;
				$stampWidth  = $ratio * $stampWidth;
			}

			$pdf->Image(
				$params['BILLEN_PATH_TO_STAMP'],
				$margin['left']+$width/2+45, $pdf->GetY(),
				$stampWidth, $stampHeight
			);
		}
	}
}

$y0 = $pdf->GetY();

$bankAccNo = $params["SELLER_COMPANY_BANK_ACCOUNT"];
$bankRouteNo = $params["SELLER_COMPANY_BANK_ACCOUNT_CORR"];
$bankSwift = $params["SELLER_COMPANY_BANK_SWIFT"];

if ($bankAccNo && $bankRouteNo && $bankSwift)
{
	$pdf->SetFont($fontFamily, 'B', $fontSize);

	$pdf->Write(15, CSalePdf::prepareToPdf("Bank Details"));
	$pdf->Ln();

	$pdf->SetFont($fontFamily, '', $fontSize);

	$bankDetails = '';

	if ($params["SELLER_COMPANY_NAME"])
	{
		$bankDetails .= CSalePdf::prepareToPdf(sprintf(
			"Account Name: %s\n",
			$params["SELLER_COMPANY_NAME"]
		));
	}

	$bankDetails .= CSalePdf::prepareToPdf(sprintf("Account #: %s\n", $bankAccNo));

	$bank = $params["SELLER_COMPANY_BANK_NAME"];
	$bankAddr = $params["SELLER_COMPANY_BANK_ADDR"];
	$bankPhone = $params["SELLER_COMPANY_BANK_PHONE"];

	if ($bank || $bankAddr || $bankPhone)
	{
		$bankDetails .= CSalePdf::prepareToPdf("Bank Name and Address: ");
		if ($bank)
			$bankDetails .= CSalePdf::prepareToPdf($bank);
		$bankDetails .= CSalePdf::prepareToPdf("\n");

		if ($bankAddr)
			$bankDetails .= CSalePdf::prepareToPdf(sprintf("%s\n", $bankAddr));

		if ($bankPhone)
		{
			$bankDetails .= CSalePdf::prepareToPdf(sprintf("%s\n", $bankPhone));
		}
	}

	$bankDetails .= CSalePdf::prepareToPdf(sprintf("Bank's routing number: %s\n", $bankRouteNo));
	$bankDetails .= CSalePdf::prepareToPdf(sprintf("Bank SWIFT: %s\n", $bankSwift));

	$pdf->MultiCell($width/2, 15, $bankDetails, 0, 'L');
}

$pdf->SetY($y0 + 15);
if ($params["SELLER_COMPANY_DIRECTOR_POSITION"])
{
	if ($params["SELLER_COMPANY_DIRECTOR_NAME"] || $params["SELLER_COMPANY_DIR_SIGN"])
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

		if ($params["SELLER_COMPANY_DIRECTOR_NAME"])
		{
			$pdf->SetX($pdf->GetX() + $width/2 + 15);
			$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_DIRECTOR_NAME"]));
			$pdf->Ln();
			$pdf->Ln();
		}

		$pdf->SetX($pdf->GetX() + $width/2 + 15);
		$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_DIRECTOR_POSITION"]));

		$pdf->Cell(0, 15, '', 'B');

		if ($isDirSign)
		{
			$pdf->Image(
				$params['SELLER_COMPANY_DIR_SIGN'],
				$pdf->GetX() - 150, $pdf->GetY() - $signHeight + 15,
				$signWidth, $signHeight
			);
		}

		$pdf->Ln();
		$pdf->Ln();
	}
}

if ($params["SELLER_COMPANY_ACCOUNTANT_POSITION"])
{
	if ($params["SELLER_COMPANY_ACCOUNTANT_NAME"] || $params["SELLER_COMPANY_ACC_SIGN"])
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

		if ($params["SELLER_COMPANY_ACCOUNTANT_NAME"])
		{
			$pdf->SetX($pdf->GetX() + $width/2 + 15);
			$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_ACCOUNTANT_NAME"]));
			$pdf->Ln();
			$pdf->Ln();
		}

		$pdf->SetX($pdf->GetX() + $width/2 + 15);
		$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_ACCOUNTANT_POSITION"]));

		$pdf->Cell(0, 15, '', 'B');

		if ($isAccSign)
		{
			$pdf->Image(
				$params['SELLER_COMPANY_ACC_SIGN'],
				$pdf->GetX() - 150, $pdf->GetY() - $signHeight + 15,
				$signWidth, $signHeight
			);
		}

		$pdf->Ln();
	}
}

$dest = 'I';
if ($_REQUEST['GET_CONTENT'] == 'Y')
	$dest = 'S';
else if ($_REQUEST['DOWNLOAD'] == 'Y')
	$dest = 'D';

$fileName = sprintf(
	'Invoice # %s (Issue Date %s).pdf',
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

$trFileName = CUtil::translit($fileName, 'en', array('max_len' => 1024, 'safe_chars' => '.', 'replace_space' => '-'));

return $pdf->Output($trFileName, $dest, $fileName);
?>