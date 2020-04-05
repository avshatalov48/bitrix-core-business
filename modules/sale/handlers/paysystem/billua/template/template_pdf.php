<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!CSalePdf::isPdfAvailable())
	die();

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;
/** @var CSaleTfpdf $pdf */
$pdf = new CSalePdf('P', 'pt', 'A4');

if ($params['BILLUA_BACKGROUND'])
{
	$pdf->SetBackground(
		$params['BILLUA_BACKGROUND'],
		$params['BILLUA_BACKGROUND_STYLE']
	);
}

$pageWidth  = $pdf->GetPageWidth();
$pageHeight = $pdf->GetPageHeight();

$pdf->AddFont('Font', '', 'pt_sans-regular.ttf', true);
$pdf->AddFont('Font', 'B', 'pt_sans-bold.ttf', true);

$fontFamily = 'Font';
$fontSize   = 10.5;

$margin = array(
	'top' => intval($params['BILLUA_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILLUA_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILLUA_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILLUA_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

$pdf->SetDisplayMode(100, 'continuous');
$pdf->SetMargins($margin['left'], $margin['top'], $margin['right']);
$pdf->SetAutoPageBreak(true, $margin['bottom']);

$pdf->AddPage();


$pdf->SetFont($fontFamily, 'B', $fontSize);
if($params['BILLUA_HEADER'])
{
	$pdf->Write(15, CSalePdf::prepareToPdf($params['BILLUA_HEADER']).CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_TITLE', array('#PAYMENT_NUMBER#' => htmlspecialcharsbx($params["ACCOUNT_NUMBER"]), '#PAYMENT_DATE#' => $params["DATE_INSERT"]))));
	$pdf->Ln();
	$pdf->Ln();
}
if ($params['BILLUA_SELLER_SHOW'] == 'Y')
{
	$pdf->SetFont($fontFamily, '', $fontSize);

	$title = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_SELLER').': ');
	$title_width = $pdf->GetStringWidth($title);
	$pdf->Write(15, $title);

	$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_NAME"]));
	$pdf->Ln();

	$pdf->Cell($title_width, 15, '');
	$pdf->MultiCell(0, 15, CSalePdf::prepareToPdf(sprintf(
			Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_RS').' %s, '.Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_BANK').' %s, '.Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_MFO').' %s',
		$params["SELLER_COMPANY_BANK_ACCOUNT"],
		$params["SELLER_COMPANY_BANK_NAME"],
		$params["SELLER_COMPANY_MFO"]
	)));

	$sellerAddr = '';
	if ($params["SELLER_COMPANY_ADDRESS"])
	{
		$sellerAddr = $params["SELLER_COMPANY_ADDRESS"];
		if (is_array($sellerAddr))
			$sellerAddr = implode(', ', $sellerAddr);
		else
			$sellerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerAddr));
	}

	$pdf->Cell($title_width, 15, '');
	$pdf->MultiCell(0, 15, CSalePdf::prepareToPdf(sprintf(
		Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_ADDRESS').': %s, '.Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_PHONE').': %s',
		$sellerAddr,
		$params["SELLER_COMPANY_PHONE"]
	)));

	$pdf->Cell($title_width, 15, '');

	$requisiteList = array();
	foreach (array('EDRPOY', 'IPN', 'PDV') as $value)
	{
		if ($params["SELLER_COMPANY_".$value])
			$requisiteList[] = Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_'.$value).': '.$params["SELLER_COMPANY_".$value];
	}
	$text = join(', ', $requisiteList);

	$pdf->MultiCell(0, 15, CSalePdf::prepareToPdf($text));

	if ($params["SELLER_COMPANY_SYS"])
	{
		$pdf->Cell($title_width, 15, '');
		$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_SYS"]));
		$pdf->Ln();
	}
	$pdf->Ln();
}

if ($params['BILLUA_PAYER_SHOW'] === 'Y')
{
	$pdf->Cell($title_width, 15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_BUYER').': '));

	$pdf->Write(15, CSalePdf::prepareToPdf($params["BUYER_PERSON_COMPANY_NAME"]));
	$pdf->Ln();

	$buyerPhone = $params["BUYER_PERSON_COMPANY_PHONE"];
	$buyerFax = $params["BUYER_PERSON_COMPANY_FAX"];
	if ($buyerPhone || $buyerFax)
	{
		$pdf->Cell($title_width, 15, '');

		if ($buyerPhone)
		{
			$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(Loc::getMessage('SALE_HPS_BILLUA_BUYER_PHONE').': %s', $buyerPhone)));
			if ($buyerFax)
				$pdf->Write(15, CSalePdf::prepareToPdf(', '));
		}

		if ($buyerFax)
			$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(Loc::getMessage('SALE_HPS_BILLUA_BUYER_FAX').': %s', $buyerFax)));

		$pdf->Ln();
	}

	if ($params["BUYER_PERSON_COMPANY_ADDRESS"])
	{
		$buyerAddr = $params["BUYER_PERSON_COMPANY_ADDRESS"];
		if (is_array($buyerAddr))
			$buyerAddr = implode(', ', $buyerAddr);
		else
			$buyerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($buyerAddr));
		$pdf->Cell($title_width, 15, '');
		$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(
			Loc::getMessage('SALE_HPS_BILLUA_BUYER_ADDRESS').': %s',
			$buyerAddr
		)));
		$pdf->Ln();
	}

	$pdf->Ln();

	if ($params["BUYER_PERSON_COMPANY_DOGOVOR"])
	{
		$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(
			Loc::getMessage('SALE_HPS_BILLUA_BUYER_DOGOVOR').': %s',
				$params["BUYER_PERSON_COMPANY_DOGOVOR"]
		)));

		$pdf->Ln();
	}
}
$arCurFormat = CCurrencyLang::GetCurrencyFormat($params['CURRENCY']);
$currency = trim(str_replace('#', '', $arCurFormat['FORMAT_STRING']));

$arCells = array();
$columnList = array('NUMBER', 'NAME', 'QUANTITY', 'MEASURE', 'PRICE', 'VAT_RATE', 'SUM');
$arCols = array();
$vatRateColumn = 0;
foreach ($columnList as $column)
{
	if ($params['BILLUA_COLUMN_'.$column.'_SHOW'] == 'Y')
	{
		$caption = $params['BILLUA_COLUMN_'.$column.'_TITLE'];
		if (in_array($column, array('PRICE', 'SUM')))
			$caption .= ', '.$currency;

		$arCols[$column] = array(
			'NAME' => CSalePdf::prepareToPdf($caption),
			'SORT' => $params['BILLUA_COLUMN_'.$column.'_SORT']
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

	$isVatInPrice = false;
	$n = 0;
	$sum = 0.00;
	$vat = 0;

	foreach ($params['BASKET_ITEMS'] as $basketItem)
	{
		$productName = $basketItem["NAME"];
		if ($productName == "OrderDelivery")
			$productName = Loc::getMessage('SALE_HPS_BILLUA_DELIVERY');
		else if ($productName == "OrderDiscount")
			$productName = Loc::getMessage('SALE_HPS_BILLUA_DISCOUNT');

		$isVatInPrice = $basketItem['IS_VAT_IN_PRICE'];

		if ($basketItem['IS_VAT_IN_PRICE'])
			$basketItemPrice = $basketItem['PRICE'];
		else
			$basketItemPrice = $basketItem['PRICE']*(1 + $basketItem['VAT_RATE']);

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
					$data = CSalePdf::prepareToPdf($basketItem["MEASURE_NAME"] ? $basketItem["MEASURE_NAME"] : Loc::getMessage('SALE_HPS_BILLUA_MEASHURE'));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'PRICE':
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($basketItem['PRICE'], $basketItem['CURRENCY'], true));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'VAT_RATE':
					$data = CSalePdf::prepareToPdf(roundEx($basketItem['VAT_RATE']*100, SALE_VALUE_PRECISION)."%");
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'SUM':
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($basketItemPrice * $basketItem['QUANTITY'], $basketItem['CURRENCY'], true));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				default:
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
			$arProps[$n][] = CSalePdf::prepareToPdf(sprintf("%s: %s", $basketPropertyItem["NAME"], $basketPropertyItem["VALUE"]));
		}

		$sum += doubleval($basketItem['PRICE'] * $basketItem['QUANTITY']);
		$vat = max($vat, $basketItem['VAT_RATE']);
	}

	if ($vat <= 0)
	{
		unset($arCols['VAT_RATE']);
		$columnCount = count($arCols);
		$arColumnKeys = array_keys($arCols);
		foreach ($arCells as $i => $cell)
			unset($arCells[$i]['VAT_RATE']);
	}

	if ($vat > 0 && array_key_exists('PRICE', $arCols) && $isVatInPrice)
		$arCols['PRICE']['NAME'] = CSalePdf::prepareToPdf($params['BILLUA_COLUMN_PRICE_TAX_TITLE'].', '.$currency);

	if ($vat > 0 && array_key_exists('SUM', $arCols))
		$arCols['SUM']['NAME'] = CSalePdf::prepareToPdf($params['BILLUA_COLUMN_SUM_TAX_TITLE'].', '.$currency);

	if ($params['DELIVERY_PRICE'] > 0)
	{
		$sDeliveryItem = Loc::getMessage('SALE_HPS_BILLUA_DELIVERY');
		if ($params['DELVIERY_NAME'])
			$sDeliveryItem .= sprintf(" (%s)", $params['DELVIERY_NAME']);

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
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($params['DELIVERY_PRICE'], $params['CURRENCY'], true));
					break;
				case 'VAT_RATE':
					$data = CSalePdf::prepareToPdf(roundEx($vat*100, SALE_VALUE_PRECISION)."%");
					break;
				case 'SUM':
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($params['DELIVERY_PRICE'], $params['CURRENCY'], true));
					break;
				default:
					$data = '';
			}
			if ($data !== null)
				$arCells[$n][$columnId] = $data;
		}

		$sum += doubleval($params['DELIVERY_PRICE']);
	}

	$items = $n;
	$orderTax = 0;
	if ($params['BILLUA_TOTAL_SHOW'] === 'Y')
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
					($tax["IS_IN_PRICE"] == "Y") ? Loc::getMessage('SALE_HPS_BILLUA_IN_PRICE') : "",
					($vat <= 0) ? $tax["TAX_NAME"] : Loc::getMessage('SALE_HPS_BILLUA_TAX'),
					($vat <= 0 && $tax["IS_PERCENT"] == "Y")
						? sprintf(' (%s%%)', roundEx($tax["VALUE"],SALE_VALUE_PRECISION))
						: ""
				));
				$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency(
					$tax["VALUE_MONEY"],
					$params['CURRENCY'],
					true
				));

				$orderTax += $tax["VALUE_MONEY"];
			}
		}

		if ($params['SUM_PAID'])
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_PAYMENT_PAID').":");
			$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($params['DELIVERY_PRICE'], $params['CURRENCY'], true));
		}

		if ($params['DISCOUNT_PRICE'])
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_DISCOUNT').":");
			$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($params['DISCOUNT_PRICE'], $params['CURRENCY'], true));
		}

		$arCells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$arCells[$n][$arColumnKeys[$i]] = null;

		$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_SUM').':');
		$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($params['SUM'], $params['CURRENCY'], true));
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

	if ($params['BILLUA_COLUMN_NAME_SHOW'] == 'Y')
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
if ($params['BILLUA_TOTAL_SHOW'] === 'Y')
{
	$pdf->SetFont($fontFamily, 'B', $fontSize);
	$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(
		Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEMS_TOTAL'),
		$items,
		($params['CURRENCY'] == "UAH")
			? Number2Word_Rus(
				$params['SUM'],
				"Y",
				$params['CURRENCY']
			)
			: SaleFormatCurrency(
				$params['SUM'],
				$params['CURRENCY'],
				false
			)
	)));
	$pdf->Ln();

	if ($vat > 0)
	{
		$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(
				Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEMS_TAX'),
			($params['CURRENCY'] == "UAH")
				? Number2Word_Rus($orderTax, "Y", $params['CURRENCY'])
				: SaleFormatCurrency($orderTax, $params['CURRENCY'], false)
		)));
	}
	elseif($orderTax == 0)
	{
		$pdf->Write(15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEMS_WITHOUT_TAX')));
	}
	$pdf->Ln();
	$pdf->Ln();
}
if ($params["BILLUA_COMMENT1"] || $params["BILLUA_COMMENT2"])
{
	$pdf->Write(15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_COMMENT')));
	$pdf->Ln();

	$pdf->SetFont($fontFamily, '', $fontSize);

	if ($params["BILLUA_COMMENT1"])
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILLUA_COMMENT1"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}

	if ($params["BILLUA_COMMENT2"])
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILLUA_COMMENT2"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}
}

$pdf->Ln();
if ($params['BILLUA_FOOTER_SHOW'] == 'Y')
{
	if ($params['BILLUA_PATH_TO_STAMP'])
	{
		$filePath = $pdf->GetImagePath($params['BILLUA_PATH_TO_STAMP']);
		if ($filePath != '' && !$blank && \Bitrix\Main\IO\File::isFileExists($filePath))
		{
			list($stampHeight, $stampWidth) = $pdf->GetImageSize($params['BILLUA_PATH_TO_STAMP']);
			if ($stampHeight && $stampWidth)
			{
				if ($stampHeight > 120 || $stampWidth > 120)
				{
					$ratio = 120 / max($stampHeight, $stampWidth);
					$stampHeight = $ratio * $stampHeight;
					$stampWidth = $ratio * $stampWidth;
				}
				$pdf->Image(
						$params['BILLUA_PATH_TO_STAMP'],
						$margin['left'] + 40, $pdf->GetY(),
						$stampWidth, $stampHeight
				);
			}
		}
	}

	$pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX()+$width, $pdf->GetY());
	$pdf->Ln();
	$pdf->Ln();

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

	$pdf->SetFont($fontFamily, 'B', $fontSize);
	$pdf->Write(15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_WRITER').': '));

	if ($isAccSign)
	{
		$pdf->Image(
			$params['SELLER_COMPANY_ACC_SIGN'],
			$pdf->GetX() + 80 - $signWidth/2, $pdf->GetY() - $signHeight + 15,
			$signWidth, $signHeight
		);
	}

	$pdf->SetFont($fontFamily, '', $fontSize);
	$pdf->Cell(160, 15, '', 'B', 0, 'C');
	if ($params["SELLER_COMPANY_ACCOUNTANT_NAME"])
		$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_ACCOUNTANT_NAME"]));

	$pdf->SetX(max($pdf->GetX()+20, $margin['left']+3*$width/5));
	if ($params["SELLER_COMPANY_ACCOUNTANT_POSITION"])
	{
		$pdf->SetFont($fontFamily, 'B', $fontSize);
		$pdf->Write(15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_ACC_POSITION').': '));

		$pdf->SetFont($fontFamily, '', $fontSize);
		$pdf->Cell(0, 15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_ACCOUNTANT_POSITION"]), 'B', 0, 'C');
	}
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
}
if ($params["DATE_PAY_BEFORE"])
{
	$pdf->SetFont($fontFamily, 'B', $fontSize);
	$pdf->Cell(0, 15, CSalePdf::prepareToPdf(sprintf(
		Loc::getMessage('SALE_HPS_BILLUA_DATE_PAID_BEFORE'),
		ConvertDateTime($params["DATE_PAY_BEFORE"], FORMAT_DATE)
			?: $params["DATE_PAY_BEFORE"]
	)), 0, 0, 'R');
}


$dest = 'I';
if ($_REQUEST['GET_CONTENT'] == 'Y')
	$dest = 'S';
else if ($_REQUEST['DOWNLOAD'] == 'Y')
	$dest = 'D';

$fileName = sprintf(
	'Rakhunok No%s vid %s.pdf',
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

$trFileName = CUtil::translit($fileName, 'la', array('max_len' => 1024, 'safe_chars' => '.', 'replace_space' => '-'));

return $pdf->Output($trFileName, $dest, $fileName);
?>