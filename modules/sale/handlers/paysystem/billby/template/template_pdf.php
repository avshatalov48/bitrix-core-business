<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arPaySysAction["ENCODING"] = "";

if (!CSalePdf::isPdfAvailable())
	die();

$blank = ($_REQUEST['BLANK'] == 'Y');

/** @var CSaleTfpdf $pdf */
$pdf = new CSalePdf('P', 'pt', 'A4');

if ($params['BILLBY_BACKGROUND'])
{
	$pdf->SetBackground(
		$params['BILLBY_BACKGROUND'],
		$params['BILLBY_BACKGROUND_STYLE']
	);
}

$pageWidth  = $pdf->GetPageWidth();
$pageHeight = $pdf->GetPageHeight();

$pdf->AddFont('Font', '', 'pt_sans-regular.ttf', true);
$pdf->AddFont('Font', 'B', 'pt_sans-bold.ttf', true);

$fontFamily = 'Font';
$fontSize   = 10.5;
$defaultLineWidth = 0.567;
$lineHeight = 15;

$margin = array(
	'top' => intval($params['BILLBY_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILLBY_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILLBY_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILLBY_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

$pdf->SetDisplayMode(100, 'continuous');
$pdf->SetMargins($margin['left'], $margin['top'], $margin['right']);
$pdf->SetAutoPageBreak(true, $margin['bottom']);

$pdf->AddPage();


$x0 = $pdf->GetX();
$y0 = $pdf->GetY();
$logoHeight = 0;
$logoWidth = 0;

$textWidth = $width;
if ($params['BILLBY_HEADER_SHOW'] == 'Y')
{
	if ($params['BILLBY_PATH_TO_LOGO'])
	{
		list($imageHeight, $imageWidth) = $pdf->GetImageSize($params['BILLBY_PATH_TO_LOGO']);

		$imgMargin = 5;
		$imgDpi = intval($params['BILLBY_LOGO_DPI']) ?: 96;
		$imgZoom = 96 / $imgDpi;

		$logoHeight = $imageHeight * $imgZoom + $imgMargin;
		$logoWidth  = $imageWidth * $imgZoom + $imgMargin;
		if ($logoWidth >= $width)
		{
			$imgDpi = 96 * $imageWidth/($width*0.6 + 5);
			$imgZoom = 96 / $imgDpi;

			$logoHeight = $imageHeight * $imgZoom + $imgMargin;
			$logoWidth  = $imageWidth * $imgZoom + $imgMargin;
		}

		$textWidth = $width - $logoWidth;

		$pdf->Image($params['BILLBY_PATH_TO_LOGO'], $x0 + $textWidth + $imgMargin, $pdf->GetY(), -$imgDpi, -$imgDpi);
	}

	$pdf->SetFont($fontFamily, '', $fontSize);

	if ($textWidth >= 20)
	{
		// region Seller info
		$sellerInfoRows = array();
		$sellerInfoName = '';
		if ($params["SELLER_COMPANY_NAME"])
		{
			$sellerInfoName .= $params["SELLER_COMPANY_NAME"];
			if (!empty($sellerInfoName))
				$sellerInfoRows[] = $sellerInfoName;
		}
		unset($sellerInfoName);
		$sellerInfoTaxId = '';
		if ($params['SELLER_COMPANY_INN'])
		{
			$sellerInfoTaxId .= Loc::getMessage('SALE_HPS_BILLBY_INN').': '.$params['SELLER_COMPANY_INN'];
			if (!empty($sellerInfoTaxId))
				$sellerInfoRows[] = $sellerInfoTaxId;
		}
		unset($sellerInfoTaxId);
		$sellerInfoBank = '';
		$sellerBank = '';
		$sellerRs = '';
		if ($params["SELLER_COMPANY_BANK_NAME"])
		{
			$sellerBankCity = '';
			if ($params["SELLER_COMPANY_BANK_CITY"])
			{
				$sellerBankCity = $params["SELLER_COMPANY_BANK_CITY"];
				if (is_array($sellerBankCity))
					$sellerBankCity = implode(', ', $sellerBankCity);
				else
					$sellerBankCity = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerBankCity));
			}
			$sellerBank = sprintf(
				"%s %s",
				$params["SELLER_COMPANY_BANK_NAME"],
				$sellerBankCity
			);
			$sellerRs = $params["SELLER_COMPANY_BANK_ACCOUNT"];
		}
		else
		{
			$rsPattern = '/\s*\d{10,100}\s*/';

			$sellerBank = trim(preg_replace($rsPattern, ' ', $params["SELLER_COMPANY_BANK_ACCOUNT"]));

			preg_match($rsPattern, $params["SELLER_COMPANY_BANK_ACCOUNT"], $matches);
			$sellerRs = trim($matches[0]);
		}
		if (!empty($sellerRs))
		{
			$sellerRsPrefix = Loc::getMessage('SALE_HPS_BILLBY_SELLER_ACC_ABBR');
			if (!empty($sellerRsPrefix))
				$sellerRs = $sellerRsPrefix.' '.$sellerRs;
			unset($sellerRsPrefix);
			$sellerInfoBank .= $sellerRs;
		}
		unset($sellerRs);
		if (!empty($sellerBank))
		{
			if (!empty($sellerInfoBank))
				$sellerInfoBank .= ', ';
			$sellerInfoBank .= $sellerBank;
		}
		unset($sellerBank);
		if (!empty($params['SELLER_COMPANY_BANK_BIC']))
		{
			if (!empty($sellerInfoBank))
				$sellerInfoBank .= ', ';
			$sellerInfoBank .=
				Loc::getMessage('SALE_HPS_BILLBY_SELLER_BANK_BIK').' '.$params['SELLER_COMPANY_BANK_BIC'];
		}
		if (!empty($sellerInfoBank))
			$sellerInfoRows[] = $sellerInfoBank;
		unset($sellerInfoBank);
		$sellerInfoAddr = '';
		if ($params['SELLER_COMPANY_ADDRESS'])
		{
			$sellerAddr = $params['SELLER_COMPANY_ADDRESS'];
			if (is_array($sellerAddr))
				$sellerAddr = implode(', ', $sellerAddr);
			else
				$sellerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerAddr));
			if (!empty($sellerAddr))
				$sellerInfoAddr .= Loc::getMessage('SALE_HPS_BILLBY_ADDR_TITLE').': '.$sellerAddr;
		}
		if ($params["SELLER_COMPANY_PHONE"])
		{
			if (!empty($sellerInfoAddr))
				$sellerInfoAddr .= ', ';
			$phoneTitle = Loc::getMessage('SALE_HPS_BILLBY_PHONE_TITLE');
			if (!empty($phoneTitle))
				$sellerInfoAddr .= $phoneTitle.' ';
			$sellerInfoAddr .= $params["SELLER_COMPANY_PHONE"];
		}
		if (!empty($sellerInfoAddr))
			$sellerInfoRows[] = $sellerInfoAddr;
		unset($sellerInfoAddr);
		// endregion Seller info

		$pdf->SetX($x0);
		if (!empty($sellerInfoRows))
		{
			foreach ($sellerInfoRows as $text)
			{
				$text = CSalePdf::prepareToPdf($text);
				while ($pdf->GetStringWidth($text))
				{
					list($string, $text) = $pdf->splitString($text, $textWidth);
					$pdf->Cell($textWidth, $lineHeight, $string, 0, 0, 'L');
					$pdf->Ln();
				}
			}
		}
		unset($sellerInfoRows);
		$pdf->Ln();
	}
	$pdf->SetY(max($y0 + $logoHeight, $pdf->GetY()));
}
if ($params['BILLBY_HEADER'])
{
	$dateValue = $params["PAYMENT_DATE_INSERT"];
	if ($dateValue instanceof \Bitrix\Main\Type\Date || $dateValue instanceof \Bitrix\Main\Type\DateTime)
	{
		$dateValue = ToLower(FormatDate('d F Y', $dateValue->getTimestamp()));
		$yearPostfix = Loc::getMessage('SALE_HPS_BILLBY_YEAR_POSTFIX');
		if (!empty($yearPostfix))
			$dateValue .= $yearPostfix;
		unset($yearPostfix);
	}
	else if (is_string($dateValue))
	{
		$timeStampValue = MakeTimeStamp($dateValue);
		if ($timeStampValue !== false)
			$dateValue = ToLower(FormatDate('d F Y', $timeStampValue));
		unset($timeStampValue);
	}
	$pdf->SetFont($fontFamily, 'B', $fontSize * 1.6);
	$billNo_tmp = CSalePdf::prepareToPdf(
		$params['BILLBY_HEADER'].' '.Loc::getMessage('SALE_HPS_BILLBY_SELLER_TITLE', array('#PAYMENT_NUM#' => $params["ACCOUNT_NUMBER"], '#PAYMENT_DATE#' => $dateValue))
	);

	$billNo_width = $pdf->GetStringWidth($billNo_tmp);
	$pdf->Cell(0, $lineHeight, $billNo_tmp, 0, 0, 'C');
	$pdf->Ln();
}
$pdf->SetFont($fontFamily, '', $fontSize);

if ($params["BILLBY_ORDER_SUBJECT"])
{
	$pdf->Cell($width/2-$billNo_width/2-2, $lineHeight, '');
	$pdf->MultiCell(0, 15, CSalePdf::prepareToPdf($params["BILLBY_ORDER_SUBJECT"]), 0, 'L');
}

if ($params["PAYMENT_DATE_PAY_BEFORE"])
{
	$pdf->Cell($width/2-$billNo_width/2-2, $lineHeight, '');
	$pdf->MultiCell(0, $lineHeight, CSalePdf::prepareToPdf(
			Loc::getMessage('SALE_HPS_BILLBY_SELLER_DATE_END', array('#PAYMENT_DATE_END#' => ConvertDateTime($params["PAYMENT_DATE_PAY_BEFORE"], FORMAT_DATE) ?: $params["PAYMENT_DATE_PAY_BEFORE"]))), 0, 'L');
}

$pdf->Ln();
if ($params['BILLBY_PAYER_SHOW'] == 'Y')
{
	// region Buyer info
	$buyerInfoRows = array();
	if ($params['BUYER_PERSON_COMPANY_DOGOVOR'])
	{
		$buyerInfoRows[] =
			Loc::getMessage('SALE_HPS_BILLBY_BUYER_DOGOVOR').': '.$params['BUYER_PERSON_COMPANY_DOGOVOR'];
		$buyerInfoRows[] = '';
	}
	$buyerInfoName = Loc::getMessage('SALE_HPS_BILLBY_BUYER_TITLE').':';
	if ($params["BUYER_PERSON_COMPANY_NAME"])
	{
		if (!empty($buyerInfoName))
			$buyerInfoName .= ' ';
		$buyerInfoName .= $params["BUYER_PERSON_COMPANY_NAME"];
	}
	if (!empty($buyerInfoName))
		$buyerInfoRows[] = $buyerInfoName;
	unset($buyerInfoName);
	$buyerInfoTaxId = '';
	if ($params['BUYER_PERSON_COMPANY_INN'])
	{
		$buyerInfoTaxId .= Loc::getMessage('SALE_HPS_BILLBY_INN').': '.$params['BUYER_PERSON_COMPANY_INN'];
		if (!empty($buyerInfoTaxId))
			$buyerInfoRows[] = $buyerInfoTaxId;
	}
	unset($buyerInfoTaxId);
	$buyerInfoBank = '';
	$buyerBank = '';
	$buyerRs = '';
	if ($params["BUYER_PERSON_COMPANY_BANK_NAME"])
	{
		$buyerBankCity = '';
		if ($params["BUYER_PERSON_COMPANY_BANK_CITY"])
		{
			$buyerBankCity = $params["BUYER_PERSON_COMPANY_BANK_CITY"];
			if (is_array($buyerBankCity))
				$buyerBankCity = implode(', ', $buyerBankCity);
			else
				$buyerBankCity = str_replace(array("\r\n", "\n", "\r"), ', ', strval($buyerBankCity));
		}
		$buyerBank = sprintf(
			"%s %s",
			$params["BUYER_PERSON_COMPANY_BANK_NAME"],
			$buyerBankCity
		);
		$buyerRs = $params["BUYER_PERSON_COMPANY_BANK_ACCOUNT"];
	}
	else
	{
		$rsPattern = '/\s*\d{10,100}\s*/';

		$buyerBank = trim(preg_replace($rsPattern, ' ', $params["BUYER_PERSON_COMPANY_BANK_ACCOUNT"]));

		preg_match($rsPattern, $params["BUYER_PERSON_COMPANY_BANK_ACCOUNT"], $matches);
		$buyerRs = trim($matches[0]);
	}
	if (!empty($buyerRs))
	{
		$buyerRsPrefix = Loc::getMessage('SALE_HPS_BILLBY_SELLER_ACC_ABBR');
		if (!empty($buyerRsPrefix))
			$buyerRs = $buyerRsPrefix.' '.$buyerRs;
		unset($buyerRsPrefix);
		$buyerInfoBank .= $buyerRs;
	}
	unset($buyerRs);
	if (!empty($buyerBank))
	{
		if (!empty($buyerInfoBank))
			$buyerInfoBank .= ', ';
		$buyerInfoBank .= $buyerBank;
	}
	unset($buyerBank);
	if (!empty($params['BUYER_PERSON_COMPANY_BANK_BIC']))
	{
		if (!empty($buyerInfoBank))
			$buyerInfoBank .= ', ';
		$buyerInfoBank .= Loc::getMessage('SALE_HPS_BILLBY_SELLER_BANK_BIK').' '.$params['BUYER_PERSON_COMPANY_BANK_BIC'];
	}
	if (!empty($buyerInfoBank))
		$buyerInfoRows[] = $buyerInfoBank;
	unset($buyerInfoBank);
	$buyerInfoAddr = '';
	if ($params['BUYER_PERSON_COMPANY_ADDRESS'])
	{
		$buyerAddr = $params['BUYER_PERSON_COMPANY_ADDRESS'];
		if (is_array($buyerAddr))
			$buyerAddr = implode(', ', $buyerAddr);
		else
			$buyerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($buyerAddr));
		if (!empty($buyerAddr))
			$buyerInfoAddr .= Loc::getMessage('SALE_HPS_BILLBY_ADDR_TITLE').': '.$buyerAddr;
	}
	if ($params["BUYER_PERSON_COMPANY_PHONE"])
	{
		if (!empty($buyerInfoAddr))
			$buyerInfoAddr .= ', ';
		$phoneTitle = Loc::getMessage('SALE_HPS_BILLBY_PHONE_TITLE');
		if (!empty($phoneTitle))
			$buyerInfoAddr .= $phoneTitle.' ';
		$buyerInfoAddr .= $params["BUYER_PERSON_COMPANY_PHONE"];
	}
	if ($params["BUYER_PERSON_COMPANY_FAX"])
	{
		if (!empty($buyerInfoAddr))
			$buyerInfoAddr .= ', ';
		$phoneTitle = Loc::getMessage('SALE_HPS_BILLBY_FAX_TITLE');
		if (!empty($phoneTitle))
			$buyerInfoAddr .= $phoneTitle.' ';
		$buyerInfoAddr .= $params["BUYER_PERSON_COMPANY_FAX"];
	}
	if ($params["BUYER_PERSON_COMPANY_NAME_CONTACT"])
	{
		if (!empty($buyerInfoAddr))
			$buyerInfoAddr .= ', ';
		$buyerInfoAddr .= $params["BUYER_PERSON_COMPANY_NAME_CONTACT"];
	}
	if (!empty($buyerInfoAddr))
		$buyerInfoRows[] = $buyerInfoAddr;
	unset($buyerInfoAddr);
	// endregion Buyer info

	$pdf->SetX($x0);
	$textWidth = $width;
	if (!empty($buyerInfoRows))
	{
		foreach ($buyerInfoRows as $text)
		{
			if (empty($text))
			{
				$pdf->Ln();
			}
			else
			{
				$text = CSalePdf::prepareToPdf($text);
				while ($pdf->GetStringWidth($text))
				{
					list($string, $text) = $pdf->splitString($text, $textWidth);
					$pdf->Cell($textWidth, $lineHeight, $string, 0, 0, 'L');
					$pdf->Ln();
				}
			}
		}
	}
	unset($buyerInfoRows);
}
$arCurFormat = CCurrencyLang::GetCurrencyFormat($params['CURRENCY']);
$currency = preg_replace('/(^|[^&])#/', '${1}', $arCurFormat['FORMAT_STRING']);
	$currency = strip_tags($currency);

// Precision
$currencyFormat = CCurrencyLang::GetFormatDescription($params['CURRENCY']);
if ($currencyFormat === false)
	$currencyFormat = CCurrencyLang::GetDefaultValues();
$currencyPrecision = (int)$currencyFormat['DECIMALS'];
if ($currencyPrecision <= 0)
	$currencyPrecision = 2;
$salePrecision = (int)Bitrix\Main\Config\Option::get('sale', 'value_precision', 2);
if ($salePrecision <= 0)
	$salePrecision = 2;
$salePrecision = min($salePrecision, SALE_VALUE_PRECISION);
$precision = min($salePrecision, $currencyPrecision);


$columnList = array('NUMBER', 'NAME', 'QUANTITY', 'MEASURE', 'PRICE', 'SUM', 'VAT_RATE', 'VAT_SUM', 'TOTAL');
$arCols = array();
$vatRateColumn = 0;
foreach ($columnList as $column)
{
	if ($params['BILLBY_COLUMN_'.$column.'_SHOW'] == 'Y')
	{
		$caption = $params['BILLBY_COLUMN_'.$column.'_TITLE'];

		$arCols[$column] = array(
			'NAME' => CSalePdf::prepareToPdf($caption),
			'SORT' => $params['BILLBY_COLUMN_'.$column.'_SORT']
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

$taxes = array();

$arRowsWidth = array();
$arRowsContentWidth = array();
$showTotalRow = false;
$totalTitleColIndex = -1;
if (count($params['BASKET_ITEMS']) > 0)
{
	$arCells = array();
	$arProps = array();

	$n = 0;
	$sum = 0.00;
	$totalSum = 0.00;
	$totalVatSum = 0.00;
	$totalSumWithVat = 0.00;
	$vat = 0;

	foreach ($params['BASKET_ITEMS'] as $basketItem)
	{
		$productName = $basketItem["NAME"];
		if ($productName == "OrderDelivery")
			$productName = Loc::getMessage('SALE_HPS_BILLBY_DELIVERY');
		else if ($productName == "OrderDiscount")
			$productName = Loc::getMessage('SALE_HPS_BILLBY_DISCOUNT');

		if ($basketItem['IS_VAT_IN_PRICE'])
		{
			$basketItemTotal = doubleval($basketItem['PRICE'] * $basketItem['QUANTITY']);

			if (($basketItem['VAT_RATE'] != 0.0))
			{
				$basketItemSum = $basketItemTotal / (1 + doubleval($basketItem['VAT_RATE']));
				$basketItemVatSum = doubleval($basketItemSum * $basketItem['VAT_RATE']);
			}
			else
			{
				$basketItemSum = $basketItemTotal;
				$basketItemVatSum = 0.0;
			}
		}
		else
		{
			$basketItemSum = doubleval($basketItem['PRICE'] * $basketItem['QUANTITY']);

			if (($basketItem['VAT_RATE'] != 0.0))
			{
				$basketItemTotal = doubleval($basketItemSum * (1 + doubleval($basketItem['VAT_RATE'])));
				$basketItemVatSum = doubleval($basketItemSum * doubleval($basketItem['VAT_RATE']));
			}
			else
			{
				$basketItemTotal = $basketItemSum;
				$basketItemVatSum = 0.0;
			}
		}

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
					$data = CSalePdf::prepareToPdf($basketItem["MEASURE_NAME"] ? $basketItem["MEASURE_NAME"] : Loc::getMessage('SALE_HPS_BILLBY_BASKET_MEASURE_DEFAULT'));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'PRICE':
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($basketItem['PRICE'], $basketItem['CURRENCY'], true));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'SUM':
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($basketItemSum, $basketItem['CURRENCY'], true));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'VAT_RATE':
					if ($basketItem['VAT_RATE'] == 0.0)
						$data = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLBY_TOTAL_VAT_RATE_NO'));
					else
						$data = CSalePdf::prepareToPdf(roundEx($basketItem['VAT_RATE'] * 100, $precision)."%");
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'VAT_SUM':
					if ($basketItem['VAT_RATE'] == 0.0)
						$data = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLBY_TOTAL_VAT_SUM_NO'));
					else
						$data = CSalePdf::prepareToPdf(SaleFormatCurrency($basketItemVatSum, $basketItem['CURRENCY'], true));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'TOTAL':
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($basketItemTotal, $basketItem['CURRENCY'], true));
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

			$arProps[$n][] = $pdf::prepareToPdf(sprintf("%s: %s", $basketPropertyItem["NAME"], $basketPropertyItem["VALUE"]));
		}

		$sum += roundEx(doubleval($basketItem['PRICE'] * $basketItem['QUANTITY']), $precision);
		$vat = max($vat, $basketItem['VAT_RATE']);
		$totalSum += roundEx($basketItemSum, $precision);
		$totalVatSum += roundEx($basketItemVatSum, $precision);
		$totalSumWithVat += roundEx($basketItemTotal, $precision);
	}

	if ($params['DELIVERY_PRICE'] > 0)
	{
		$sDeliveryItem = Loc::getMessage('SALE_HPS_BILLBY_DELIVERY');
		if ($params['DELIVERY_NAME'])
			$sDeliveryItem .= sprintf(" (%s)", $params['DELIVERY_NAME']);

		$basketItemTotal = $params['DELIVERY_PRICE'];

		if (($vat != 0.0))
		{
			$basketItemSum = $basketItemTotal / (1 + doubleval($vat));
			$basketItemVatSum = doubleval($basketItemSum * $vat);
		}
		else
		{
			$basketItemSum = $basketItemTotal;
			$basketItemVatSum = 0.0;
		}

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
					$data = CSalePdf::prepareToPdf($sDeliveryItem);
					break;
				case 'QUANTITY':
					$data = CSalePdf::prepareToPdf(1);
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'MEASURE':
					$data = CSalePdf::prepareToPdf('');
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'PRICE':
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($params['DELIVERY_PRICE'], $params['CURRENCY'], true));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'SUM':
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($basketItemSum, $params['CURRENCY'], true));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'VAT_RATE':
					if ($vat == 0.0)
						$data = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLBY_TOTAL_VAT_RATE_NO'));
					else
						$data = CSalePdf::prepareToPdf(roundEx($vat * 100, $precision)."%");
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'VAT_SUM':
					if ($vat == 0.0)
						$data = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLBY_TOTAL_VAT_SUM_NO'));
					else
						$data = CSalePdf::prepareToPdf(SaleFormatCurrency($basketItemVatSum, $params['CURRENCY'], true));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				case 'TOTAL':
					$data = CSalePdf::prepareToPdf(SaleFormatCurrency($basketItemTotal, $params['CURRENCY'], true));
					$arCols[$columnId]['IS_DIGIT'] = true;
					break;
				default:
					$data = '';
			}
			if ($data !== null)
				$arCells[$n][$columnId] = $data;
		}

		$sum += roundEx(doubleval($params['DELIVERY_PRICE']), $precision);
		$totalSum += roundEx($basketItemSum, $precision);
		$totalVatSum += roundEx($basketItemVatSum, $precision);
		$totalSumWithVat += roundEx($basketItemTotal, $precision);
	}

	$totalRowIsLast = false;
	$totalRowIndex = -1;
	$cntBasketItem = $n;
	if ($params['BILLBY_TOTAL_SHOW'] == 'Y')
	{
		$totalRowValues = array();
		foreach ($arColumnKeys as $colNum => $colCode)
		{
			$skip = false;
			$value = null;
			$isDigit = false;
			switch ($colCode)
			{
				case 'SUM':
					$value = CSalePdf::prepareToPdf(SaleFormatCurrency($totalSum, $params['CURRENCY'], true));
					$isDigit = true;
					break;
				case 'VAT_RATE':
					$value = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLBY_TOTAL_VAT_RATE_X'));
					$isDigit = true;
					break;
				case 'VAT_SUM':
					if ($totalVatSum == 0.0)
						$value = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLBY_TOTAL_VAT_SUM_NO'));
					else
						$value = CSalePdf::prepareToPdf(SaleFormatCurrency($totalVatSum, $params['CURRENCY'], true));
					$isDigit = true;
					break;
				case 'TOTAL':
					$value = CSalePdf::prepareToPdf(SaleFormatCurrency($totalSumWithVat, $params['CURRENCY'], true));
					$isDigit = true;
					break;
				default:
					$skip = true;
			}
			if (!$skip)
				$totalRowValues[$colNum] = array('value' => $value, 'isDigit' => $isDigit);
		}
		unset($skip, $value, $isDigit);
		$totalTitleColIndex = (empty($totalRowValues) ? 0 : (int)min(array_keys($totalRowValues))) - 1;
		if ($totalTitleColIndex >= 0)
			$totalRowValues[$totalTitleColIndex] = array('value' => CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLBY_TOTAL_SUM')));
		if (!empty($totalRowValues))
		{
			$showTotalRow = true;
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
			{
				if (isset($totalRowValues[$i]))
				{
					$arCells[$n][$arColumnKeys[$i]] = $totalRowValues[$i]['value'];
					if (isset($totalRowValues[$i]['isDigit']))
						$arCols[$arColumnKeys[$i]]['IS_DIGIT'] = $totalRowValues[$i]['isDigit'];
				}
				else
				{
					$arCells[$n][$arColumnKeys[$i]] = null;
				}
			}
			$totalRowIndex = $n;
		}
		unset($totalRowValues);

		$totalRowIsLast = true;
		if ($params['TAXES'])
		{
			foreach ($params['TAXES'] as $tax)
			{
				if (isset($tax['CODE']) && $tax['CODE'] !== 'VAT')
				{
					$totalRowIsLast = false;
					$arCells[++$n] = array();
					for ($i = 0; $i < $columnCount; $i++)
						$arCells[$n][$arColumnKeys[$i]] = null;

					$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(sprintf(
						"%s%s%s:",
						($tax["IS_IN_PRICE"] == "Y") ? Loc::getMessage('SALE_HPS_BILLBY_INCLUDING') : "",
						$tax["TAX_NAME"],
						($vat <= 0 && $tax["IS_PERCENT"] == "Y") ? sprintf(' (%s%%)', roundEx($tax["VALUE"], $precision)) : ""
					));
					$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($tax["VALUE_MONEY"], $params['CURRENCY'], true));
				}
			}
		}

		if ($params['SUM_PAID'] > 0)
		{
			$totalRowIsLast = false;
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLBY_TOTAL_PAID'));
			$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($params['SUM_PAID'], $params['CURRENCY'], true));
		}

		if ($params['DISCOUNT_PRICE'] > 0)
		{
			$totalRowIsLast = false;
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLBY_TOTAL_DISCOUNT'));
			$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($params['DISCOUNT_PRICE'], $params['CURRENCY'], true));
		}

		if (!$totalRowIsLast)
		{
			$arCells[$totalRowIndex][$arColumnKeys[$totalTitleColIndex]] = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLBY_SUBTOTAL'));

			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount - 2]] = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLBY_TOTAL_SUM'));
			$arCells[$n][$arColumnKeys[$columnCount - 1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($params['SUM'], $params['CURRENCY'], true));
		}
	}

	$pdf->SetFont($fontFamily, 'B', $fontSize);
	$rowsInfo = $pdf->calculateRowsWidth($arCols, $arCells, $totalRowIndex > 0 ? $totalRowIndex : $cntBasketItem, $width, 3);
	$pdf->SetFont($fontFamily, '', $fontSize);
	$arRowsWidth = $rowsInfo['ROWS_WIDTH'];
	$arRowsContentWidth = $rowsInfo['ROWS_CONTENT_WIDTH'];
}
$pdf->Ln();

$x0 = $pdf->GetX();
$y0 = $pdf->GetY();

do
{
	$newLine = false;
	foreach ($arCols as $columnId => $column)
	{
		list($string, $arCols[$columnId]['NAME']) = $pdf->splitString($column['NAME'], $arRowsContentWidth[$columnId]);
		$pdf->Cell($arRowsWidth[$columnId], $lineHeight, $string, 0, 0, 'C');

		if ($arCols[$columnId]['NAME'])
			$newLine = true;

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

	$pdf->SetFont($fontFamily, $n === $totalRowIndex ? 'B' : '', $fontSize);

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

			if (in_array($columnId, array('QUANTITY', 'MEASURE', 'PRICE', 'SUM', 'VAT_RATE', 'VAT_SUM', 'TOTAL'), true))
			{
				if (!is_null($arCells[$n][$columnId]))
				{
					$pdf->Cell($rowWidth, $lineHeight, $string, 0, 0, 'R');
				}
			}
			elseif ($columnId == 'NUMBER')
			{
				if (!is_null($arCells[$n][$columnId]))
					$pdf->Cell($rowWidth, $lineHeight, ($l == 0) ? $string : '', 0, 0, 'C');
			}
			elseif ($columnId == 'NAME')
			{
				if (!is_null($arCells[$n][$columnId]))
					$pdf->Cell($rowWidth, $lineHeight, $string, 0, 0,  ($n > $cntBasketItem) ? 'R' : '');
			}
			else
			{
				if (!is_null($arCells[$n][$columnId]))
				{
					$pdf->Cell($rowWidth, $lineHeight, $string, 0, 0,   ($n > $cntBasketItem) ? 'R' : 'L');
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

	if ($params['BILLBY_COLUMN_NAME_SHOW'] == 'Y')
	{
		if (isset($arProps[$n]) && is_array($arProps[$n]))
		{
			$pdf->SetFont($fontFamily, '', $fontSize - 2);
			foreach ($arProps[$n] as $property)
			{
				$i = 0;
				$line = 0;
				foreach ($arCols as $columnId => $caption)
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

	$startIndex = ($n > $cntBasketItem) ? (($showTotalRow && $n === $totalRowIndex) ? ($totalTitleColIndex >= 0 ? $totalTitleColIndex + 1 : 0) : $columnCount - 1) : 0;
	for ($i = $startIndex; $i <= $columnCount; $i++)
	{
		$pdf->Line(${"x$i"}, $y0, ${"x$i"}, $y5);
	}

	$pdf->Line(${"x$startIndex"}, $y5, ${'x'.$columnCount}, $y5);
}

if ($params['BILLBY_TOTAL_SHOW'] == 'Y')
{
	$pdf->SetFont($fontFamily, 'B', $fontSize);

	$inWords = in_array($params['CURRENCY'], array("RUR", "RUB", "UAH", "KZT", "BYR", "BYN"));
	$textWidth = $width;

	$pdf->Ln(5);
	$text = Loc::getMessage('SALE_HPS_BILLBY_TOTAL_VAT').': ';
	if ($inWords)
		$text .= Number2Word_Rus(roundEx($totalVatSum, $precision), "Y", $params['CURRENCY']);
	else
		$text .= SaleFormatCurrency(roundEx($totalVatSum, $precision), $params['CURRENCY'], false);
	unset($totalVatSum);
	if (!empty($text))
	{
		$text = CSalePdf::prepareToPdf($text);
		while ($pdf->GetStringWidth($text))
		{
			list($string, $text) = $pdf->splitString($text, $textWidth);
			$pdf->Cell($textWidth, $lineHeight, $string, 0, 0, 'L');
			$pdf->Ln($lineHeight);
		}
	}
	$pdf->Ln($lineHeight);

	$text = Loc::getMessage('SALE_HPS_BILLBY_TOTAL_SUM_WITH_VAT').': ';
	if ($inWords)
		$text .= Number2Word_Rus($totalSumWithVat, "Y", $params['CURRENCY']);
	else
		$text .= SaleFormatCurrency($totalSumWithVat, $params['CURRENCY'], false);

	if (!empty($text))
	{
		$text = CSalePdf::prepareToPdf($text);
		while ($pdf->GetStringWidth($text))
		{
			list($string, $text) = $pdf->splitString($text, $textWidth);
			$pdf->Cell($textWidth, $lineHeight, $string, 0, 0, 'L');
			$pdf->Ln();
		}
	}

	unset($inWords);
}
$pdf->Ln();
$pdf->Ln();

if ($params["BILLBY_COMMENT1"] || $params["BILLBY_COMMENT2"])
{
	$pdf->SetFont($fontFamily, '', $fontSize);

	if ($params["BILLBY_COMMENT1"])
	{
		$pdf->Write($lineHeight, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILLBY_COMMENT1"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}

	if ($params["BILLBY_COMMENT2"])
	{
		$pdf->Write($lineHeight, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILLBY_COMMENT2"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}
}

$pdf->Ln();
$pdf->Ln();

if ($params['BILLBY_SIGN_SHOW'] == 'Y')
{
	if ($params['BILLBY_PATH_TO_STAMP'])
	{
		$filePath = $pdf->GetImagePath($params['BILLBY_PATH_TO_STAMP']);

		if ($filePath != '' && !$blank && \Bitrix\Main\IO\File::isFileExists($filePath))
		{
			list($stampHeight, $stampWidth) = $pdf->GetImageSize($params['BILLBY_PATH_TO_STAMP']);
			if ($stampHeight && $stampWidth)
			{
				if ($stampHeight > 120 || $stampWidth > 120)
				{
					$ratio = 120 / max($stampHeight, $stampWidth);
					$stampHeight = $ratio * $stampHeight;
					$stampWidth = $ratio * $stampWidth;
				}

				if ($pdf->GetY() + $stampHeight > $pageHeight)
					$pdf->AddPage();

				$pdf->Image(
						$params['BILLBY_PATH_TO_STAMP'],
						$margin['left'] + 40, $pdf->GetY(),
						$stampWidth, $stampHeight
				);
			}
		}
	}

	$x2 = 0;
	$signHeight = 0;
	$signWidth = 0;
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
			$pdf->SetY($pdf->GetY() + max($signHeight, $lineHeight * 2) - $lineHeight);

		$pdf->SetFont($fontFamily, 'B', $fontSize);
		$pdf->MultiCell(150, $lineHeight, $sellerDirPos, 0, 'L');
		$pdf->SetFont($fontFamily, '', $fontSize);
		$pdf->SetXY($margin['left'] + 150, $pdf->GetY() - $lineHeight);

		if ($isDirSign)
		{
			$pdf->Image(
					$params['SELLER_COMPANY_DIR_SIGN'],
				$pdf->GetX() + 80 - $signWidth/2, $pdf->GetY() - $signHeight + $lineHeight,
				$signWidth, $signHeight
			);
		}

		$x1 = $pdf->GetX();
		$pdf->Cell(160, $lineHeight, '');
		$x2 = $pdf->GetX();

		if ($params["SELLER_COMPANY_DIRECTOR_NAME"])
			$pdf->Write($lineHeight, CSalePdf::prepareToPdf('('.$params["SELLER_COMPANY_DIRECTOR_NAME"].')'));
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
			$pdf->SetY($pdf->GetY() + max($signHeight, $lineHeight * 2) - $lineHeight);
		$pdf->SetFont($fontFamily, 'B', $fontSize);
		$pdf->MultiCell(150, $lineHeight, $sellerAccPos, 0, 'L');
		$pdf->SetFont($fontFamily, '', $fontSize);
		$pdf->SetXY($margin['left'] + 150, $pdf->GetY() - $lineHeight);

		if ($isAccSign)
		{
			$pdf->Image(
				$params['SELLER_COMPANY_ACC_SIGN'],
				$pdf->GetX() + 80 - $signWidth/2, $pdf->GetY() - $signHeight + $lineHeight,
				$signWidth, $signHeight
			);
		}

		$x1 = $pdf->GetX();
		$pdf->Cell(($params["SELLER_COMPANY_DIRECTOR_NAME"]) ? $x2-$x1 : 160, $lineHeight, '');
		$x2 = $pdf->GetX();

		if ($params["SELLER_COMPANY_ACCOUNTANT_NAME"])
			$pdf->Write($lineHeight, CSalePdf::prepareToPdf('('.$params["SELLER_COMPANY_ACCOUNTANT_NAME"].')'));
		$pdf->Ln();

		$y2 = $pdf->GetY();
		$pdf->Line($x1, $y2, $x2, $y2);
	}
}

$dest = 'I';
if ($_REQUEST['GET_CONTENT'] == 'Y')
	$dest = 'S';
else if ($_REQUEST['DOWNLOAD'] == 'Y')
	$dest = 'D';

$fileName = sprintf(
	'Schet No %s ot %s.pdf',
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
	ConvertDateTime($params['PAYMENT_DATE_INSERT'], 'YYYY-MM-DD')
);

$trFileName = CUtil::translit($fileName, 'ru', array('max_len' => 1024, 'safe_chars' => '.', 'replace_space' => '-'));

return $pdf->Output($trFileName, $dest, $fileName);
?>