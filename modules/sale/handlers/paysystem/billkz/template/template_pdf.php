<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arPaySysAction["ENCODING"] = "";

if (!CSalePdf::isPdfAvailable())
	die();

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;

/** @var CSaleTfpdf $pdf */
$pdf = new CSalePdf('P', 'pt', 'A4');

if ($params['BILLKZ_BACKGROUND'])
{
	$pdf->SetBackground(
		$params['BILLKZ_BACKGROUND'],
		$params['BILLKZ_BACKGROUND_STYLE']
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
	'top' => intval($params['BILLKZ_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILLKZ_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILLKZ_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILLKZ_MARGIN_LEFT'] ?: 20) * 72/25.4
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

if ($params['BILLKZ_HEADER_SHOW'] == 'Y')
{
	if ($params['BILLKZ_PATH_TO_LOGO'])
	{
		list($imageHeight, $imageWidth) = $pdf->GetImageSize($params['BILLKZ_PATH_TO_LOGO']);

		$imgDpi = intval($params['BILLKZ_LOGO_DPI']) ?: 96;
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

		$pdf->Image($params['BILLKZ_PATH_TO_LOGO'], $pdf->GetX(), $pdf->GetY(), -$imgDpi, -$imgDpi);
	}

	$textLeftMargin = $logoWidth + 10;
	if ($textLeftMargin < 95)
		$textLeftMargin = 95;

	$pdf->SetFont($fontFamily, '', $fontSize);

	$textWidth = $width - $textLeftMargin;
	if ($textWidth >= 20)
	{
		if ($params["BILLKZ_COMMENT1"])
		{
			$text = HTMLToTxt(
				preg_replace(
					array('#</div>\s*<div[^>]*>#i', '#</?div>#i'),
					array('<br>', '<br>'),
					CSalePdf::prepareToPdf($params["BILLKZ_COMMENT1"])
				), '', array(), 0
			);
			while ($pdf->GetStringWidth($text))
			{
				list($string, $text) = $pdf->splitString($text, $textWidth);
				$pdf->SetX($pdf->GetX() + $textLeftMargin);
				$pdf->Cell($textWidth, $lineHeight, $string, 0, 0, 'C');
				$pdf->Ln();
			}
		}
		if ($params["BILLKZ_COMMENT2"])
		{
			$pdf->Ln();
			$text = HTMLToTxt(
				preg_replace(
					array('#</div>\s*<div[^>]*>#i', '#</?div>#i'),
					array('<br>', '<br>'),
					CSalePdf::prepareToPdf($params["BILLKZ_COMMENT2"])
				), '', array(), 0
			);
			while ($pdf->GetStringWidth($text))
			{
				list($string, $text) = $pdf->splitString($text, $textWidth);
				$pdf->SetX($pdf->GetX() + $textLeftMargin);
				$pdf->Cell($textWidth, $lineHeight, $string, 0, 0, 'C');
				$pdf->Ln();
			}
		}
	}
	$pdf->SetY(max($y0 + $logoHeight, $pdf->GetY()));
	$pdf->Ln();

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
		$sellerRs = $params["SELLER_COMPANY_BANK_IIK"];
	}
	else
	{
		$rsPattern = '/\s*\d{10,100}\s*/';

		$sellerBank = trim(preg_replace($rsPattern, ' ', $params["SELLER_COMPANY_BANK_IIK"]));

		preg_match($rsPattern, $params["SELLER_COMPANY_BANK_IIK"], $matches);
		$sellerRs = trim($matches[0]);
	}

	$colInfo = array(
		array('prc' => 56),
		array('prc' => 25),
		array('prc' => 19)
	);
	foreach ($colInfo as $n => $info)
		$colInfo[$n]['width'] = $width * $info['prc'] / 100;
	$x0 = $pdf->GetX();
	$y0 = $pdf->GetY();
	$pdf->SetFont($fontFamily, 'B', $fontSize);
	$text = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLKZ_BENEFICIARY').':');
	$pdf->Cell($colInfo[0]['width'], $lineHeight, $text);
	$x1 = $x0 + $colInfo[0]['width'];
	$text = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLKZ_IIK'));
	$pdf->Cell($colInfo[1]['width'], $lineHeight, $text, 0, 0, 'C');
	$x2 = $x1 + $colInfo[1]['width'];
	$text = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLKZ_KBE'));
	$pdf->Cell($colInfo[2]['width'], $lineHeight, $text, 0, 0, 'C');
	$x3 = $x2 + $colInfo[2]['width'];
	$y1 = $y0 + $lineHeight;
	$pdf->Line($x0, $y0, $x3, $y0);
	$pdf->Ln();
	$text = CSalePdf::prepareToPdf($params["SELLER_COMPANY_NAME"] ?: ' ');
	while ($pdf->GetStringWidth($text) > 0)
	{
		list($string, $text) = $pdf->splitString($text, $colInfo[0]['width']);
		$pdf->Cell($colInfo[0]['width'], $lineHeight, $string);
		if ($text)
			$pdf->Ln();
	}
	$pdf->Ln();
	$y2 = $pdf->GetY();
	$pdf->SetFont($fontFamily, '', $fontSize);
	$text = CSalePdf::prepareToPdf(
		$params["SELLER_COMPANY_BIN"] ? Loc::getMessage('SALE_HPS_BILLKZ_BIN').': '.$params["SELLER_COMPANY_BIN"] : ' '
	);
	while ($pdf->GetStringWidth($text) > 0)
	{
		list($string, $text) = $pdf->splitString($text, $colInfo[0]['width']);
		$pdf->Cell($colInfo[0]['width'], $lineHeight, $string);
		if ($text)
			$pdf->Ln();
	}
	$pdf->Ln();
	$y3 = $pdf->GetY();
	$dy = ($y2 - $y1 - $lineHeight) / 2;
	$pdf->SetXY($x1, $y1 + $dy);
	$pdf->Cell($colInfo[1]['width'], $lineHeight, CSalePdf::prepareToPdf($sellerRs ?: ' '), 0, 0, 'C');
	$text = CSalePdf::prepareToPdf($params["SELLER_COMPANY_KBE"] ? $params["SELLER_COMPANY_KBE"] : ' ');
	$pdf->Cell($colInfo[2]['width'], $lineHeight, $text, 0, 0, 'C');
	$pdf->Line($x2, $y0, $x2, $y3);
	$pdf->Line($x0, $y3, $x3, $y3);
	$pdf->SetXY($x0, $y3);
	$colInfo = array(
		array('prc' => 56),
		array('prc' => 19),
		array('prc' => 25)
	);
	foreach ($colInfo as $n => $info)
		$colInfo[$n]['width'] = $width * $info['prc'] / 100;
	$text = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLKZ_BENEFICIARY_BANK').':');
	$pdf->Cell($colInfo[0]['width'], $lineHeight, $text);
	$x1 = $x0 + $colInfo[0]['width'];
	$pdf->SetFont($fontFamily, 'B', $fontSize);
	$text = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLKZ_SELLER_BANK_BIK'));
	$pdf->Cell($colInfo[1]['width'], $lineHeight, $text, 0, 0, 'C');
	$x2 = $x1 + $colInfo[1]['width'];
	$text = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLKZ_PAYMENT_PC'));
	$pdf->Cell($colInfo[2]['width'], $lineHeight, $text, 0, 0, 'C');
	$x3 = $x2 + $colInfo[2]['width'];
	$y4 = $y3 + $lineHeight;
	$pdf->Ln();
	$pdf->SetFont($fontFamily, '', $fontSize);
	$text = CSalePdf::prepareToPdf($sellerBank ?: ' ');
	while ($pdf->GetStringWidth($text) > 0)
	{
		list($string, $text) = $pdf->splitString($text, $colInfo[0]['width']);
		$pdf->Cell($colInfo[0]['width'], $lineHeight, $string);
		if ($text)
			$pdf->Ln();
	}
	$pdf->Ln();
	$y5 = $pdf->GetY();
	$dy = ($y5 - $y4 - $lineHeight) / 2;
	$pdf->SetXY($x1, $y4 + $dy);
	$text = CSalePdf::prepareToPdf($params["SELLER_COMPANY_BANK_BIC"] ?: ' ');
	$pdf->Cell($colInfo[1]['width'], $lineHeight, $text, 0, 0, 'C');
	$text = CSalePdf::prepareToPdf($params["PAYMENT_PC"] ?: ' ');
	$pdf->Cell($colInfo[2]['width'], $lineHeight, $text, 0, 0, 'C');
	$pdf->Line($x1, $y0, $x1, $y5);
	$pdf->Line($x2, $y3, $x2, $y5);
	$pdf->Line($x0, $y5, $x3, $y5);
	$pdf->SetXY($x0, $y5);
	$y6 = $y5;
	if ($params["BILLKZ_ORDER_SUBJECT"])
	{
		$text = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLKZ_PAYMENT_PURPOSE').':');
		$pdf->Cell($width, $lineHeight, $text);
		$pdf->Ln();
		$text = CSalePdf::prepareToPdf($params["BILLKZ_ORDER_SUBJECT"]);
		while ($pdf->GetStringWidth($text) > 0)
		{
			list($string, $text) = $pdf->splitString($text, $width);
			$pdf->Cell($width, $lineHeight, $string);
			if ($text)
				$pdf->Ln();
		}
		$pdf->Ln();
		$y6 = $pdf->GetY();
	}
	$pdf->Line($x0, $y0, $x0, $y6);
	$pdf->Line($x3, $y0, $x3, $y6);
	$pdf->Line($x0, $y6, $x3, $y6);
	$pdf->Ln();
	$pdf->Ln();
	if ($params['BILLKZ_HEADER'])
	{
		$dateValue = $params["PAYMENT_DATE_INSERT"];
		if ($dateValue instanceof \Bitrix\Main\Type\Date || $dateValue instanceof \Bitrix\Main\Type\DateTime)
		{
			$dateValue = ToLower(FormatDate('d F Y', $dateValue->getTimestamp()));
			$yearPostfix = Loc::getMessage('SALE_HPS_BILLKZ_YEAR_POSTFIX');
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
		$text = CSalePdf::prepareToPdf(
			$params['BILLKZ_HEADER'].' '.
			Loc::getMessage(
				'SALE_HPS_BILLKZ_SELLER_TITLE',
				array(
					'#PAYMENT_NUM#' => $params["ACCOUNT_NUMBER"],
					'#PAYMENT_DATE#' => $dateValue
				)
			)
		);
		while ($pdf->GetStringWidth($text) > 0)
		{
			list($string, $text) = $pdf->splitString($text, $width);
			$pdf->Cell($width, $lineHeight, $string);
			if ($text)
				$pdf->Ln();
		}
		$pdf->SetFont($fontFamily, '', $fontSize);
	}
	$pdf->Ln();
	$pdf->Ln();
	$y7 = $pdf->GetY();
	$pdf->SetLineWidth($defaultLineWidth * 2);
	$pdf->Line($x0, $y7, $x0 + $width, $y7);
	$pdf->SetLineWidth($defaultLineWidth);
	unset($y7);
	$pdf->Ln();
}
$sellerTitle = $sellerInfo = '';
if ($params["SELLER_COMPANY_BIN"] || $params["SELLER_COMPANY_IIN"])
{
	$sellerInfo .= Loc::getMessage('SALE_HPS_BILLKZ_BIN').' / '.Loc::getMessage('SALE_HPS_BILLKZ_IIN').' ';
	if ($params["SELLER_COMPANY_BIN"])
		$sellerInfo .= $params["SELLER_COMPANY_BIN"];
	else if ($params["SELLER_COMPANY_IIN"])
		$sellerInfo .= $params["SELLER_COMPANY_IIN"];
}
if ($params["SELLER_COMPANY_NAME"])
{
	if (!empty($sellerInfo))
		$sellerInfo .= ', ';
	$sellerInfo .= $params["SELLER_COMPANY_NAME"];
}
if ($params["SELLER_COMPANY_ADDRESS"])
{
	$buyerAddr = $params["SELLER_COMPANY_ADDRESS"];
	if (is_array($buyerAddr))
		$buyerAddr = implode(', ', $buyerAddr);
	else
		$buyerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($buyerAddr));
	if (!empty($sellerInfo))
		$sellerInfo .= ', ';
	$sellerInfo .= $buyerAddr;
}
if ($params["SELLER_COMPANY_PHONE"])
{
	if (!empty($sellerInfo))
		$sellerInfo .= ', ';
	$sellerInfo .= $params["SELLER_COMPANY_PHONE"];
}
if (!empty($sellerInfo))
	$sellerTitle = Loc::getMessage('SALE_HPS_BILLKZ_SELLER_NAME').':';

$buyerTitle = $buyerInfo = '';
if ($params['BILLKZ_PAYER_SHOW'] == 'Y')
{
	if ($params["BUYER_PERSON_COMPANY_BIN"] || $params["BUYER_PERSON_COMPANY_IIN"])
	{
		$buyerInfo .= Loc::getMessage('SALE_HPS_BILLKZ_BIN').' / '.Loc::getMessage('SALE_HPS_BILLKZ_IIN').' ';
		if ($params["BUYER_PERSON_COMPANY_BIN"])
			$buyerInfo .= $params["BUYER_PERSON_COMPANY_BIN"];
		else if ($params["BUYER_PERSON_COMPANY_IIN"])
			$buyerInfo .= $params["BUYER_PERSON_COMPANY_IIN"];
	}
	if ($params["BUYER_PERSON_COMPANY_NAME"])
	{
		if (!empty($buyerInfo))
			$buyerInfo .= ', ';
		$buyerInfo .= $params["BUYER_PERSON_COMPANY_NAME"];
	}
	if ($params["BUYER_PERSON_COMPANY_ADDRESS"])
	{
		$buyerAddr = $params["BUYER_PERSON_COMPANY_ADDRESS"];
		if (is_array($buyerAddr))
			$buyerAddr = implode(', ', $buyerAddr);
		else
			$buyerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($buyerAddr));
		if (!empty($buyerInfo))
			$buyerInfo .= ', ';
		$buyerInfo .= $buyerAddr;
	}
	if ($params["BUYER_PERSON_COMPANY_PHONE"])
	{
		if (!empty($buyerInfo))
			$buyerInfo .= ', ';
		$buyerInfo .= $params["BUYER_PERSON_COMPANY_PHONE"];
	}
	if ($params["BUYER_PERSON_COMPANY_FAX"])
	{
		if (!empty($buyerInfo))
			$buyerInfo .= ', ';
		$buyerInfo .= $params["BUYER_PERSON_COMPANY_FAX"];
	}
	if ($params["BUYER_PERSON_COMPANY_NAME_CONTACT"])
	{
		if (!empty($buyerInfo))
			$buyerInfo .= ', ';
		$buyerInfo .= $params["BUYER_PERSON_COMPANY_NAME_CONTACT"];
	}
	if (!empty($buyerInfo))
		$buyerTitle = Loc::getMessage('SALE_HPS_BILLKZ_BUYER_NAME').':';
}
$colInfo = array(
	array('prc' => 13),
	array('prc' => 87)
);
foreach ($colInfo as $n => $info)
	$colInfo[$n]['width'] = $width * $info['prc'] / 100;
$y8 = $pdf->GetY();
$text = CSalePdf::prepareToPdf($sellerTitle ?: ' ');
while ($pdf->GetStringWidth($text) > 0)
{
	list($string, $text) = $pdf->splitString($text, $colInfo[0]['width']);
	$pdf->Cell($colInfo[0]['width'], $lineHeight, $string);
	if ($text)
		$pdf->Ln();
}
$pdf->Ln();
$y9 = $pdf->GetY();
$x1 = $x0 + $colInfo[0]['width'];
$pdf->SetXY($x1, $y8);
$text = CSalePdf::prepareToPdf($sellerInfo ?: ' ');
while ($pdf->GetStringWidth($text) > 0)
{
	list($string, $text) = $pdf->splitString($text, $colInfo[1]['width']);
	$pdf->Cell($colInfo[1]['width'], $lineHeight, $string);
	if ($text)
	{
		$pdf->Ln();
		$pdf->SetX($x1);
	}
}
$pdf->Ln();
$y9 = max($y9, $pdf->GetY());
$y10 = $y9 + 9;
$pdf->SetY($y10);
$text = CSalePdf::prepareToPdf($buyerTitle ?: ' ');
while ($pdf->GetStringWidth($text) > 0)
{
	list($string, $text) = $pdf->splitString($text, $colInfo[0]['width']);
	$pdf->Cell($colInfo[0]['width'], $lineHeight, $string);
	if ($text)
		$pdf->Ln();
}
$pdf->Ln();
$y11 = $pdf->GetY();
$pdf->SetXY($x1, $y10);
$text = CSalePdf::prepareToPdf($buyerInfo ?: ' ');
while ($pdf->GetStringWidth($text) > 0)
{
	list($string, $text) = $pdf->splitString($text, $colInfo[1]['width']);
	$pdf->Cell($colInfo[1]['width'], $lineHeight, $string);
	if ($text)
	{
		$pdf->Ln();
		$pdf->SetX($x1);
	}
}
$pdf->Ln();
$y11 = max($y11, $pdf->GetY());
$y12 = $y11 + 9;
if ($params['BUYER_PERSON_COMPANY_DOGOVOR'])
{
	$pdf->SetY($y12);
	$text = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLKZ_BUYER_DOGOVOR').':');
	while ($pdf->GetStringWidth($text) > 0)
	{
		list($string, $text) = $pdf->splitString($text, $colInfo[0]['width']);
		$pdf->Cell($colInfo[0]['width'], $lineHeight, $string);
		if ($text)
			$pdf->Ln();
	}
	$pdf->Ln();
	$y13 = $pdf->GetY();
	$pdf->SetXY($x1, $y12);
	$text = CSalePdf::prepareToPdf($params['BUYER_PERSON_COMPANY_DOGOVOR'] ?: ' ');
	while ($pdf->GetStringWidth($text) > 0)
	{
		list($string, $text) = $pdf->splitString($text, $colInfo[1]['width']);
		$pdf->Cell($colInfo[1]['width'], $lineHeight, $string);
		if ($text)
		{
			$pdf->Ln();
			$pdf->SetX($x1);
		}
	}
	$pdf->Ln();
}
$pdf->Ln();

$arCurFormat = CCurrencyLang::GetCurrencyFormat($params['CURRENCY']);
$currency = preg_replace('/(^|[^&])#/', '${1}', $arCurFormat['FORMAT_STRING']);
	$currency = strip_tags($currency);

$columnList = array('NUMBER', 'NAME', 'QUANTITY', 'MEASURE', 'PRICE', 'VAT_RATE', 'SUM');
$arCols = array();
$vatRateColumn = 0;
foreach ($columnList as $column)
{
	if ($params['BILLKZ_COLUMN_'.$column.'_SHOW'] == 'Y')
	{
		$arCols[$column] = array(
			'NAME' => CSalePdf::prepareToPdf($params['BILLKZ_COLUMN_'.$column.'_TITLE']),
			'SORT' => $params['BILLKZ_COLUMN_'.$column.'_SORT']
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

if (count($params['BASKET_ITEMS']) > 0)
{
	$arCells = array();
	$arProps = array();

	$n = 0;
	$sum = 0.00;
	$vat = 0;
	foreach ($params['BASKET_ITEMS'] as $basketItem)
	{
		$productName = $basketItem["NAME"];
		if ($productName == "OrderDelivery")
			$productName = Loc::getMessage('SALE_HPS_BILLKZ_DELIVERY');
		else if ($productName == "OrderDiscount")
			$productName = Loc::getMessage('SALE_HPS_BILLKZ_DISCOUNT');

		if ($basketItem['IS_VAT_IN_PRICE'])
			$basketItemPrice = $basketItem['PRICE'];
		else
			$basketItemPrice = $basketItem['PRICE']*(1 + $basketItem['VAT_RATE']);

		$arCells[++$n] = array();
		foreach ($arCols as $columnId => $caption)
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
					$data = CSalePdf::prepareToPdf($basketItem["MEASURE_NAME"] ? $basketItem["MEASURE_NAME"] : Loc::getMessage('SALE_HPS_BILLKZ_BASKET_MEASURE_DEFAULT'));
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

			$arProps[$n][] = $pdf::prepareToPdf(sprintf("%s: %s", $basketPropertyItem["NAME"], $basketPropertyItem["VALUE"]));
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

	if ($params['DELIVERY_PRICE'] > 0)
	{
		$sDeliveryItem = Loc::getMessage('SALE_HPS_BILLKZ_DELIVERY');
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

	$cntBasketItem = $n;
	if ($params['BILLKZ_TOTAL_SHOW'] == 'Y')
	{
		$eps = 0.0001;
		if ($params['SUM'] - $sum > $eps)
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLKZ_SUBTOTAL'));
			$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($sum, $params['CURRENCY'], true));
		}

		if ($params['TAXES'])
		{
			foreach ($params['TAXES'] as $tax)
			{
				$arCells[++$n] = array();
				for ($i = 0; $i < $columnCount; $i++)
					$arCells[$n][$arColumnKeys[$i]] = null;

				$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(sprintf(
					"%s%s%s:",
					($tax["IS_IN_PRICE"] == "Y") ? Loc::getMessage('SALE_HPS_BILLKZ_INCLUDING') : "",
					$tax["TAX_NAME"],
					($vat <= 0 && $tax["IS_PERCENT"] == "Y") ? sprintf(' (%s%%)', roundEx($tax["VALUE"], SALE_VALUE_PRECISION)) : ""
				));
				$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($tax["VALUE_MONEY"], $params['CURRENCY'], true));
			}
		}

		if (!$params['TAXES'])
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLKZ_TOTAL_VAT_RATE'));
			$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLKZ_TOTAL_VAT_RATE_NO'));
		}

		if ($params['SUM_PAID'] > 0)
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLKZ_TOTAL_PAID'));
			$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($params['SUM_PAID'], $params['CURRENCY'], true));
		}

		if ($params['DISCOUNT_PRICE'] > 0)
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLKZ_TOTAL_DISCOUNT'));
			$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($params['DISCOUNT_PRICE'], $params['CURRENCY'], true));
		}


		$arCells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$arCells[$n][$arColumnKeys[$i]] = null;

		$arCells[$n][$arColumnKeys[$columnCount-2]] = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLKZ_TOTAL_SUM'));
		$arCells[$n][$arColumnKeys[$columnCount-1]] = CSalePdf::prepareToPdf(SaleFormatCurrency($params['SUM'], $params['CURRENCY'], true));
	}

	$rowsInfo = $pdf->calculateRowsWidth($arCols, $arCells, $cntBasketItem, $width);
	$arRowsWidth = $rowsInfo['ROWS_WIDTH'];
	$arRowsContentWidth = $rowsInfo['ROWS_CONTENT_WIDTH'];
}

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
					$pdf->Cell($rowWidth, 15, $string, 0, 0,  ($n > $cntBasketItem) ? 'R' : '');
			}
			elseif ($columnId == 'VAT_RATE')
			{
				if (!is_null($arCells[$n][$columnId]))
					$pdf->Cell($rowWidth, 15, ($l == 0) ? $string : '', 0, 0, 'R');
			}
			else
			{
				if (!is_null($arCells[$n][$columnId]))
				{
					$pdf->Cell($rowWidth, 15, $string, 0, 0,   ($n > $cntBasketItem) ? 'R' : 'L');
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

	if ($params['BILLKZ_COLUMN_NAME_SHOW'] == 'Y')
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

	for ($i = ($n > $cntBasketItem) ? $columnCount - 1 : 0; $i <= $columnCount; $i++)
	{
		if ($vat > 0 || $arColumnKeys[$i] != 'VAT_RATE')
			$pdf->Line(${"x$i"}, $y0, ${"x$i"}, $y5);
	}

	$pdf->Line(($n <= $cntBasketItem) ? $x0 : ${'x'.($columnCount-1)}, $y5, ${'x'.$columnCount}, $y5);
}
$pdf->Ln();

if ($params['BILLKZ_TOTAL_SHOW'] == 'Y')
{
	$pdf->SetFont($fontFamily, '', $fontSize);
	$pdf->Write($lineHeight, CSalePdf::prepareToPdf(Loc::getMessage(
		'SALE_HPS_BILLKZ_BASKET_TOTAL',
		array(
			'#BASKET_COUNT#' => $cntBasketItem,
			'#BASKET_PRICE#' => strip_tags(SaleFormatCurrency($params['SUM'], $params['CURRENCY'], false))
		)
	)));
	$pdf->Ln();

	$pdf->SetFont($fontFamily, 'B', $fontSize);
	if (in_array($params['CURRENCY'], array("RUR", "RUB", "UAH", "KZT", "BYR", "BYN")))
	{
		$pdf->Write(
			$lineHeight,
			CSalePdf::prepareToPdf(Number2Word_Rus($params['SUM'], "Y", $params['CURRENCY']))
		);
	}
	else
	{
		$pdf->Write($lineHeight, CSalePdf::prepareToPdf(strip_tags(SaleFormatCurrency(
			$params['SUM'],
			$params['CURRENCY'],
			false
		))));
	}
	$pdf->SetFont($fontFamily, '', $fontSize);
	$pdf->Ln();
	$pdf->Ln();
}

$y14 = $pdf->GetY();
$pdf->SetLineWidth($defaultLineWidth * 2);
$pdf->Line($x0, $y14, $x0 + $width, $y14);
$pdf->SetLineWidth($defaultLineWidth);
$pdf->Ln();

if ($params['BILLKZ_SIGN_SHOW'] == 'Y')
{
	$executorInfo = '';
	$signParamName = '';
	if ($params['SELLER_COMPANY_ACCOUNTANT_NAME'])
	{
		if ($params['SELLER_COMPANY_ACCOUNTANT_POSITION'])
			$executorInfo .= $params['SELLER_COMPANY_ACCOUNTANT_POSITION'];
		if (!empty($executorInfo))
			$executorInfo .= ' ';
		$executorInfo .= $params['SELLER_COMPANY_ACCOUNTANT_NAME'];
		$signParamName = 'SELLER_COMPANY_ACC_SIGN';
	}
	else if ($params['SELLER_COMPANY_DIRECTOR_NAME'])
	{
		if ($params['SELLER_COMPANY_DIRECTOR_POSITION'])
			$executorInfo .= $params['SELLER_COMPANY_DIRECTOR_POSITION'];
		if (!empty($executorInfo))
			$executorInfo .= ' ';
		$executorInfo .= $params['SELLER_COMPANY_DIRECTOR_NAME'];
		$signParamName = 'SELLER_COMPANY_DIR_SIGN';
	}
	else if ($params['SELLER_COMPANY_ACCOUNTANT_POSITION'])
	{
		$executorInfo .= $params['SELLER_COMPANY_ACCOUNTANT_POSITION'];
		$signParamName = 'SELLER_COMPANY_ACC_SIGN';
	}
	else if ($params['SELLER_COMPANY_DIRECTOR_POSITION'])
	{
		$executorInfo .= $params['SELLER_COMPANY_DIRECTOR_POSITION'];
		$signParamName = 'SELLER_COMPANY_DIR_SIGN';
	}
	else
	{
		$executorInfo = str_pad('', 6 * 35, ' ');
	}
	$stampHeight = $stampWidth = 0;
	if (!$blank && $params['BILLKZ_PATH_TO_STAMP'])
	{
		list($stampHeight, $stampWidth) = $pdf->GetImageSize($params['BILLKZ_PATH_TO_STAMP']);
	
		if ($stampHeight && $stampWidth)
		{
			if ($stampHeight > 120 || $stampWidth > 120)
			{
				$ratio = 120 / max($stampHeight, $stampWidth);
				$stampHeight = $ratio * $stampHeight;
				$stampWidth  = $ratio * $stampWidth;
			}
	
			$imageY = $pdf->GetY();
			$pageNumBefore = $pdf->PageNo();
	
			$pdf->Image(
				$params['BILLKZ_PATH_TO_STAMP'],
				$margin['left']+40, null,
				$stampWidth, $stampHeight
			);
	
			$pageNumAfter = $pdf->PageNo();
			if ($pageNumAfter === $pageNumBefore)
				$pdf->SetY($imageY);
			else
				$pdf->SetY($pdf->GetY() - $stampHeight);
			unset($imageY, $pageNumBefore, $pageNumAfter);
		}
	}

	$signHeight = $signWidth = 0;
	$isSign = false;
	if (!$blank && $params[$signParamName])
	{
		list($signHeight, $signWidth) = $pdf->GetImageSize($params[$signParamName]);

		if ($signHeight && $signWidth)
		{
			$ratio = min(37.5/$signHeight, 150/$signWidth);
			$signHeight = $ratio * $signHeight;
			$signWidth  = $ratio * $signWidth;

			$isSign = true;
		}
	}

	$colInfo = array(
		array('prc' => 18),
		array('prc' => 50),
		array('prc' => 32)
	);
	foreach ($colInfo as $n => $info)
		$colInfo[$n]['width'] = $width * $info['prc'] / 100;
	$y15 = $pdf->GetY();
	$pdf->SetY($y15 + max(($stampHeight ?: 150), $signHeight, $lineHeight) / 2);
	$pdf->SetFont($fontFamily, 'B', $fontSize);
	$text = CSalePdf::prepareToPdf(Loc::getMessage("SALE_HPS_BILLKZ_EXECUTOR"));
	$pdf->Cell($colInfo[0]['width'], $lineHeight, $text);
	$pdf->SetFont($fontFamily, '', $fontSize);
	$x1 = $x0 + $colInfo[0]['width'];
	$x2 = $x1 + $colInfo[1]['width'];
	$x3 = $x2 + $colInfo[2]['width'];
	$y16 = $pdf->GetY();
	$y17 = $y16 + $lineHeight + 1;
	$text = CSalePdf::prepareToPdf('/ '.($executorInfo ?: str_pad('', 6 * 35, ' ')).' /');
	$maxTextWidth = $width / 2;
	$textWidth = $pdf->GetStringWidth($text);
	if ($textWidth > $maxTextWidth)
	{
		$colInfo[1]['width'] = $width - $colInfo[0]['width'] - $maxTextWidth;
		$colInfo[2]['width'] = $maxTextWidth;
		$x2 = $x1 + $colInfo[1]['width'];
		$x3 = $x2 + $colInfo[2]['width'];
	}
	else if ($textWidth > $colInfo[2]['width'])
	{
		$colInfo[1]['width'] = $width - $colInfo[0]['width'] - $textWidth;
		$colInfo[2]['width'] = $textWidth;
		$x2 = $x1 + $colInfo[1]['width'];
		$x3 = $x2 + $colInfo[2]['width'];
	}
	$pdf->Line($x1, $y17, $x2, $y17);
	$pdf->SetX($x2);
	while ($pdf->GetStringWidth($text) > 0)
	{
		list($string, $text) = $pdf->splitString($text, $colInfo[2]['width']);
		$pdf->Cell($colInfo[2]['width'], $lineHeight, $string);
		if ($text)
		{
			$pdf->Ln();
			$pdf->SetX($x2);
		}
	}
	if ($isSign)
	{
		$pdf->Image(
			$params[$signParamName],
			$x1 + ($colInfo[1]['width'] - $signWidth) / 2,
			$pdf->GetY() - $signHeight + $lineHeight,
			$signWidth, $signHeight
		);
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
