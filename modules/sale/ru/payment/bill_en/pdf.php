<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
if (!is_array($arOrder))
	$arOrder = CSaleOrder::GetByID($ORDER_ID);

if (!CSalePdf::isPdfAvailable())
	die();

$pdf = new CSalePdf('P', 'pt', 'A4');

if (CSalePaySystemAction::GetParamValue('BACKGROUND'))
{
	$pdf->SetBackground(
		CSalePaySystemAction::GetParamValue('BACKGROUND'),
		CSalePaySystemAction::GetParamValue('BACKGROUND_STYLE')
	);
}

$pageWidth  = $pdf->GetPageWidth();
$pageHeight = $pdf->GetPageHeight();

$pdf->AddFont('Font', '', 'pt_sans-regular.ttf', true);
$pdf->AddFont('Font', 'B', 'pt_sans-bold.ttf', true);

$fontFamily = 'Font';
$fontSize   = 10.5;

$margin = array(
	'top' => intval(CSalePaySystemAction::GetParamValue('MARGIN_TOP') ?: 15) * 72/25.4,
	'right' => intval(CSalePaySystemAction::GetParamValue('MARGIN_RIGHT') ?: 15) * 72/25.4,
	'bottom' => intval(CSalePaySystemAction::GetParamValue('MARGIN_BOTTOM') ?: 15) * 72/25.4,
	'left' => intval(CSalePaySystemAction::GetParamValue('MARGIN_LEFT') ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

$pdf->SetDisplayMode(100, 'continuous');
$pdf->SetMargins($margin['left'], $margin['top'], $margin['right']);
$pdf->SetAutoPageBreak(true, $margin['bottom']);

$pdf->AddPage();


$y0 = $pdf->GetY();
$logoHeight = 0;
$logoWidth = 0;

if (CSalePaySystemAction::GetParamValue('PATH_TO_LOGO'))
{
	list($imageHeight, $imageWidth) = $pdf->GetImageSize(CSalePaySystemAction::GetParamValue('PATH_TO_LOGO'));

	$logoHeight = $imageHeight + 5;
	$logoWidth  = $imageWidth + 5;

	$pdf->Image(CSalePaySystemAction::GetParamValue('PATH_TO_LOGO'), $pdf->GetX(), $pdf->GetY());
}

$pdf->SetFont($fontFamily, 'B', $fontSize);

$pdf->SetX($pdf->GetX() + $logoWidth);
$pdf->Write(15, CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("SELLER_NAME")));
$pdf->Ln();

if (CSalePaySystemAction::GetParamValue("SELLER_ADDRESS"))
{
	$pdf->SetX($pdf->GetX() + $logoWidth);
	$pdf->Write(15, CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("SELLER_ADDRESS")));
	$pdf->Ln();
}

if (CSalePaySystemAction::GetParamValue("SELLER_PHONE"))
{
	$pdf->SetX($pdf->GetX() + $logoWidth);
	$pdf->Write(15, CSalePdf::prepareToPdf(sprintf("Tel.: %s", CSalePaySystemAction::GetParamValue("SELLER_PHONE"))));
	$pdf->Ln();
}

$pdf->Ln();
$pdf->SetY(max($y0 + $logoHeight, $pdf->GetY()));
$pdf->Ln();


$pdf->SetFont($fontFamily, 'B', $fontSize*2);
$pdf->Cell(0, 15, CSalePdf::prepareToPdf('Invoice'), 0, 0, 'C');

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

$pdf->SetFont($fontFamily, 'B', $fontSize);

if (CSalePaySystemAction::GetParamValue("BUYER_NAME"))
{
	$pdf->Write(15, CSalePdf::prepareToPdf('To'));
}

$pdf->SetFont($fontFamily, '', $fontSize);

$invoiceNo = CSalePdf::prepareToPdf($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ACCOUNT_NUMBER"]);
$invoiceNoWidth = $pdf->GetStringWidth($invoiceNo);

$invoiceDate = CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("DATE_INSERT"));
$invoiceDateWidth = $pdf->GetStringWidth($invoiceDate);

$invoiceDueDate = CSalePdf::prepareToPdf(ConvertDateTime(CSalePaySystemAction::GetParamValue("DATE_PAY_BEFORE"), FORMAT_DATE));
$invoiceDueDateWidth = $pdf->GetStringWidth($invoiceDueDate);

$invoiceInfoWidth = max($invoiceNoWidth, $invoiceDateWidth, $invoiceDueDateWidth);

$pdf->Cell(0, 15, $invoiceNo, 0, 0, 'R');

$pdf->SetFont($fontFamily, 'B', $fontSize);

$title = CSalePdf::prepareToPdf('Invoice # ');
$titleWidth = $pdf->GetStringWidth($title);
$pdf->SetX($pdf->GetX() - $invoiceInfoWidth - $titleWidth - 6);
$pdf->Write(15, $title, 0, 0, 'R');
$pdf->Ln();

$pdf->SetFont($fontFamily, '', $fontSize);

if (CSalePaySystemAction::GetParamValue("BUYER_NAME"))
{
	$pdf->Write(15, CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("BUYER_NAME")));
}

$pdf->Cell(0, 15, $invoiceDate, 0, 0, 'R');

$pdf->SetFont($fontFamily, 'B', $fontSize);

$title = CSalePdf::prepareToPdf('Issue Date: ');
$titleWidth = $pdf->GetStringWidth($title);
$pdf->SetX($pdf->GetX() - $invoiceInfoWidth - $titleWidth - 6);
$pdf->Write(15, $title, 0, 0, 'R');
$pdf->Ln();

$pdf->SetFont($fontFamily, '', $fontSize);

if (CSalePaySystemAction::GetParamValue("BUYER_NAME"))
{
	if (CSalePaySystemAction::GetParamValue("BUYER_ADDRESS"))
	{
		$pdf->Write(15, CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("BUYER_ADDRESS")));
	}
}

if (CSalePaySystemAction::GetParamValue("DATE_PAY_BEFORE"))
{
	$pdf->Cell(0, 15, $invoiceDueDate, 0, 0, 'R');
	
	$pdf->SetFont($fontFamily, 'B', $fontSize);
	
	$title = CSalePdf::prepareToPdf('Due Date: ');
	$titleWidth = $pdf->GetStringWidth($title);
	$pdf->SetX($pdf->GetX() - $invoiceInfoWidth - $titleWidth - 6);
	$pdf->Write(15, $title, 0, 0, 'R');
}

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();


$pdf->SetFont($fontFamily, '', $fontSize);

// Список товаров
$dbBasket = CSaleBasket::GetList(
	array("NAME" => "ASC"),
	array("ORDER_ID" => $ORDER_ID)
);
if ($arBasket = $dbBasket->Fetch())
{
	$arColsCaption = array(
		1 => CSalePdf::prepareToPdf('#'),
		CSalePdf::prepareToPdf('Item / Description'),
		CSalePdf::prepareToPdf('Qty'),
		CSalePdf::prepareToPdf('Units'),
		CSalePdf::prepareToPdf('Unit Price'),
		CSalePdf::prepareToPdf('Tax'),
		CSalePdf::prepareToPdf('Total')
	);
	$arCells = array();
	$arProps = array();
	$arRowsWidth = array(1 => 0, 0, 0, 0, 0, 0, 0);

	for ($i = 1; $i <= 7; $i++)
		$arRowsWidth[$i] = max($arRowsWidth[$i], $pdf->GetStringWidth($arColsCaption[$i]));
	
	$n = 0;
	$sum = 0.00;
	$vat = 0;
	$vats = array();
	do
	{
		// props in busket product
		$arProdProps = array();
		$dbBasketProps = CSaleBasket::GetPropsList(
			array("SORT" => "ASC", "ID" => "DESC"),
			array(
				"BASKET_ID" => $arBasket["ID"],
				"!CODE" => array("CATALOG.XML_ID", "PRODUCT.XML_ID")
			),
			false,
			false,
			array("ID", "BASKET_ID", "NAME", "VALUE", "CODE", "SORT")
		);
		while ($arBasketProps = $dbBasketProps->GetNext())
		{
			if (!empty($arBasketProps) && $arBasketProps["VALUE"] != "")
				$arProdProps[] = $arBasketProps;
		}
		$arBasket["PROPS"] = $arProdProps;

		// @TODO: replace with real vatless price
		$arBasket["VATLESS_PRICE"] = roundEx($arBasket["PRICE"] / (1 + $arBasket["VAT_RATE"]), SALE_VALUE_PRECISION);

		$arCells[++$n] = array(
			1 => CSalePdf::prepareToPdf($n),
			CSalePdf::prepareToPdf($arBasket["NAME"]),
			CSalePdf::prepareToPdf(roundEx($arBasket["QUANTITY"], SALE_VALUE_PRECISION)),
			CSalePdf::prepareToPdf('pcs'),
			CSalePdf::prepareToPdf(SaleFormatCurrency($arBasket["VATLESS_PRICE"], $arBasket["CURRENCY"], true)),
			CSalePdf::prepareToPdf(roundEx($arBasket["VAT_RATE"]*100, SALE_VALUE_PRECISION)."%"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$arBasket["VATLESS_PRICE"] * $arBasket["QUANTITY"],
				$arBasket["CURRENCY"],
				true
			))
		);

		$arProps[$n] = array();
		foreach ($arBasket["PROPS"] as $vv)
			$arProps[$n][] = CSalePdf::prepareToPdf(sprintf("%s: %s", $vv["NAME"], $vv["VALUE"]));

		for ($i = 1; $i <= 7; $i++)
			$arRowsWidth[$i] = max($arRowsWidth[$i], $pdf->GetStringWidth($arCells[$n][$i]));

		$sum += doubleval($arBasket["VATLESS_PRICE"] * $arBasket["QUANTITY"]);
		$vat = max($vat, $arBasket["VAT_RATE"]);
		if ($arBasket["VAT_RATE"] > 0)
		{
			if (!isset($vats[$arBasket["VAT_RATE"]]))
				$vats[$arBasket["VAT_RATE"]] = 0;
			$vats[$arBasket["VAT_RATE"]] += ($arBasket["PRICE"] - $arBasket["VATLESS_PRICE"]) * $arBasket["QUANTITY"];
		}
	}
	while ($arBasket = $dbBasket->Fetch());

	if (DoubleVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"]) > 0)
	{
		$arDelivery_tmp = CSaleDelivery::GetByID($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DELIVERY_ID"]);

		$sDeliveryItem = "Shipping";
		if (strlen($arDelivery_tmp["NAME"]) > 0)
			$sDeliveryItem .= sprintf(" (%s)", $arDelivery_tmp["NAME"]);
		$arCells[++$n] = array(
			1 => CSalePdf::prepareToPdf($n),
			CSalePdf::prepareToPdf($sDeliveryItem),
			CSalePdf::prepareToPdf(1),
			CSalePdf::prepareToPdf(''),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"] / (1 + $vat),
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				true
			)),
			CSalePdf::prepareToPdf(roundEx($vat*100, SALE_VALUE_PRECISION)."%"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"] / (1 + $vat),
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				true
			))
		);

		for ($i = 1; $i <= 7; $i++)
			$arRowsWidth[$i] = max($arRowsWidth[$i], $pdf->GetStringWidth($arCells[$n][$i]));

		$sum += roundEx(
			doubleval($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"] / (1 + $vat)),
			SALE_VALUE_PRECISION
		);

		if ($vat > 0)
			$vats[$vat] += roundEx(
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"] * $vat / (1 + $vat),
				SALE_VALUE_PRECISION
			);
	}

	$items = $n;

	if ($sum < $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE"])
	{
		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			CSalePdf::prepareToPdf("Subtotal:"),
			CSalePdf::prepareToPdf(SaleFormatCurrency($sum, $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"], true))
		);
	
		$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
	}


	if (!empty($vats))
	{
		// @TODO: remove on real vatless price implemented
		$delta = intval(roundEx(
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE"] - $sum - array_sum($vats),
			SALE_VALUE_PRECISION
		) * pow(10, SALE_VALUE_PRECISION));
		if ($delta)
		{
			$vatRates = array_keys($vats);
			rsort($vatRates);

			while (abs($delta) > 0)
			{
				foreach ($vatRates as $vatRate)
				{
					$vats[$vatRate] += abs($delta)/$delta / pow(10, SALE_VALUE_PRECISION);
					$delta -= abs($delta)/$delta;

					if ($delta == 0)
						break 2;
				}
			}
		}

		foreach ($vats as $vatRate => $vatSum)
		{
			$arCells[++$n] = array(
				1 => null,
				null,
				null,
				null,
				null,
				CSalePdf::prepareToPdf(sprintf(
					"Sales Tax (%s%%):",
					roundEx($vatRate * 100, SALE_VALUE_PRECISION)
				)),
				CSalePdf::prepareToPdf(SaleFormatCurrency(
					$vatSum,
					$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
					true
				))
			);

			$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
		}
	}
	else
	{
		$dbTaxList = CSaleOrderTax::GetList(
			array("APPLY_ORDER" => "ASC"),
			array("ORDER_ID" => $ORDER_ID)
		);

		while ($arTaxList = $dbTaxList->Fetch())
		{
			$arCells[++$n] = array(
				1 => null,
				null,
				null,
				null,
				null,
				CSalePdf::prepareToPdf(sprintf(
					"%s%s%s:",
					($arTaxList["IS_IN_PRICE"] == "Y") ? "Included " : "",
					$arTaxList["TAX_NAME"],
					sprintf(' (%s%%)', roundEx($arTaxList["VALUE"],SALE_VALUE_PRECISION))
				)),
				CSalePdf::prepareToPdf(SaleFormatCurrency(
					$arTaxList["VALUE_MONEY"],
					$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
					true
				))
			);

			$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
		}
	}

	if (DoubleVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SUM_PAID"]) > 0)
	{
		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			CSalePdf::prepareToPdf("Payment made:"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SUM_PAID"],
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				true
			))
		);

		$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
	}

	if (DoubleVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DISCOUNT_VALUE"]) > 0)
	{
		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			CSalePdf::prepareToPdf("Discount:"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DISCOUNT_VALUE"],
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				true
			))
		);

		$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
	}

	$arCells[++$n] = array(
		1 => null,
		null,
		null,
		null,
		null,
		CSalePdf::prepareToPdf("Total:"),
		CSalePdf::prepareToPdf(SaleFormatCurrency(
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"],
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
			true
		))
	);

	$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));

	for ($i = 1; $i <= 7; $i++)
		$arRowsWidth[$i] += 10;
	if ($vat <= 0)
		$arRowsWidth[6] = 0;
	$arRowsWidth[2] = $width - (array_sum($arRowsWidth)-$arRowsWidth[2]);
}
$pdf->Ln();

$x0 = $pdf->GetX();
$y0 = $pdf->GetY();

for ($i = 1; $i <= 7; $i++)
{
	if ($vat > 0 || $i != 6)
		$pdf->Cell($arRowsWidth[$i], 20, $arColsCaption[$i], 0, 0, 'C');
	${"x$i"} = $pdf->GetX();
}

$pdf->Ln();

$y5 = $pdf->GetY();

$pdf->Line($x0, $y0, $x7, $y0);
for ($i = 0; $i <= 7; $i++)
{
	if ($vat > 0 || $i != 6)
		$pdf->Line(${"x$i"}, $y0, ${"x$i"}, $y5);
}
$pdf->Line($x0, $y5, $x7, $y5);

$rowsCnt = count($arCells);
for ($n = 1; $n <= $rowsCnt; $n++)
{
	$arRowsWidth_tmp = $arRowsWidth;
	$accumulated = 0;
	for ($j = 1; $j <= 7; $j++)
	{
		if (is_null($arCells[$n][$j]))
		{
			$accumulated += $arRowsWidth_tmp[$j];
			$arRowsWidth_tmp[$j] = null;
		}
		else
		{
			$arRowsWidth_tmp[$j] += $accumulated;
			$accumulated = 0;
		}
	}

	$x0 = $pdf->GetX();
	$y0 = $pdf->GetY();

	$pdf->SetFont($fontFamily, '', $fontSize);

	if (!is_null($arCells[$n][2]))
	{
		$text = $arCells[$n][2];
		$cellWidth = $arRowsWidth_tmp[2];
	}
	else
	{
		$text = $arCells[$n][6];
		$cellWidth = $arRowsWidth_tmp[6];
	}

	for ($l = 0; $pdf->GetStringWidth($text) > 0; $l++)
	{
		$pos = ($pdf->GetStringWidth($text) > $cellWidth)
			? strrpos(substr($text, 0, strlen($text)*$cellWidth/$pdf->GetStringWidth($text)), ' ')
			: strlen($text);
		if (!$pos)
			$pos = strlen($text);

		if (!is_null($arCells[$n][1]))
			$pdf->Cell($arRowsWidth_tmp[1], 15, ($l == 0) ? $arCells[$n][1] : '', 0, 0, 'C');
		if ($l == 0)
			$x1 = $pdf->GetX();

		if (!is_null($arCells[$n][2]))
			$pdf->Cell($arRowsWidth_tmp[2], 15, substr($text, 0, $pos));
		if ($l == 0)
			$x2 = $pdf->GetX();

		if (!is_null($arCells[$n][3]))
			$pdf->Cell($arRowsWidth_tmp[3], 15, ($l == 0) ? $arCells[$n][3] : '', 0, 0, 'R');
		if ($l == 0)
			$x3 = $pdf->GetX();

		if (!is_null($arCells[$n][4]))
			$pdf->Cell($arRowsWidth_tmp[4], 15, ($l == 0) ? $arCells[$n][4] : '', 0, 0, 'R');
		if ($l == 0)
			$x4 = $pdf->GetX();

		if (!is_null($arCells[$n][5]))
			$pdf->Cell($arRowsWidth_tmp[5], 15, ($l == 0) ? $arCells[$n][5] : '', 0, 0, 'R');
		if ($l == 0)
			$x5 = $pdf->GetX();

		if (!is_null($arCells[$n][6])) {
			if (is_null($arCells[$n][2]))
				$pdf->Cell($arRowsWidth_tmp[6], 15, substr($text, 0, $pos), 0, 0, 'R');
			else if ($vat > 0)
				$pdf->Cell($arRowsWidth_tmp[6], 15, ($l == 0) ? $arCells[$n][6] : '', 0, 0, 'R');
		}
		if ($l == 0)
			$x6 = $pdf->GetX();

		if (!is_null($arCells[$n][7]))
			$pdf->Cell($arRowsWidth_tmp[7], 15, ($l == 0) ? $arCells[$n][7] : '', 0, 0, 'R');
		if ($l == 0)
			$x7 = $pdf->GetX();

		$pdf->Ln();

		$text = trim(substr($text, $pos));
	}

	if (isset($arProps[$n]) && is_array($arProps[$n]))
	{
		$pdf->SetFont($fontFamily, '', $fontSize-2);
		foreach ($arProps[$n] as $property)
		{
			$pdf->Cell($arRowsWidth_tmp[1], 12, '');
			$pdf->Cell($arRowsWidth_tmp[2], 12, $property);
			$pdf->Cell($arRowsWidth_tmp[3], 12, '');
			$pdf->Cell($arRowsWidth_tmp[4], 12, '');
			$pdf->Cell($arRowsWidth_tmp[5], 12, '');
			if ($vat > 0)
				$pdf->Cell($arRowsWidth_tmp[6], 12, '');
			$pdf->Cell($arRowsWidth_tmp[7], 12, '', 0, 1);
		}
	}

	$y5 = $pdf->GetY();

	if ($y0 > $y5)
		$y0 = $margin['top'];
	for ($i = (is_null($arCells[$n][1])) ? 6 : 0; $i <= 7; $i++)
	{
		if ($vat > 0 || $i != 5)
			$pdf->Line(${"x$i"}, $y0, ${"x$i"}, $y5);
	}

	$pdf->Line((!is_null($arCells[$n][1])) ? $x0 : $x6, $y5, $x7, $y5);
}
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();


$pdf->SetFont($fontFamily, 'B', $fontSize);

if (CSalePaySystemAction::GetParamValue("COMMENT1") || CSalePaySystemAction::GetParamValue("COMMENT2"))
{
	$pdf->Write(15, CSalePdf::prepareToPdf('Terms & Conditions'));
	$pdf->Ln();
	
	$pdf->SetFont($fontFamily, '', $fontSize);

	if (CSalePaySystemAction::GetParamValue("COMMENT1"))
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("COMMENT1"))
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}

	if (CSalePaySystemAction::GetParamValue("COMMENT2"))
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("COMMENT2"))
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}
}

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

if (CSalePaySystemAction::GetParamValue('PATH_TO_STAMP'))
	$pdf->Image(CSalePaySystemAction::GetParamValue('PATH_TO_STAMP'), $margin['left']+$width/2+45, $pdf->GetY());


$y0 = $pdf->GetY();

$bankAccNo = CSalePaySystemAction::GetParamValue("SELLER_BANK_ACCNO");
$bankRouteNo = CSalePaySystemAction::GetParamValue("SELLER_BANK_ROUTENO");
$bankSwift = CSalePaySystemAction::GetParamValue("SELLER_BANK_SWIFT");

if ($bankAccNo && $bankRouteNo && $bankSwift)
{
	$pdf->SetFont($fontFamily, 'B', $fontSize);
	
	$pdf->Write(15, CSalePdf::prepareToPdf("Bank Details"));
	$pdf->Ln();

	$pdf->SetFont($fontFamily, '', $fontSize);

	$bankDetails = '';

	if (CSalePaySystemAction::GetParamValue("SELLER_NAME"))
	{
		$bankDetails .= CSalePdf::prepareToPdf(sprintf(
			"Account Name: %s\n",
			CSalePaySystemAction::GetParamValue("SELLER_NAME")
		));
	}

	$bankDetails .= CSalePdf::prepareToPdf(sprintf("Account #: %s\n", $bankAccNo));

	$bank = CSalePaySystemAction::GetParamValue("SELLER_BANK");
	$bankAddr = CSalePaySystemAction::GetParamValue("SELLER_BANK_ADDR");
	$bankPhone = CSalePaySystemAction::GetParamValue("SELLER_BANK_PHONE");

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
if (CSalePaySystemAction::GetParamValue("SELLER_DIR") || CSalePaySystemAction::GetParamValue("SELLER_DIR_SIGN"))
{
	$isDirSign = false;
	if (CSalePaySystemAction::GetParamValue('SELLER_DIR_SIGN'))
	{
		list($signHeight, $signWidth) = $pdf->GetImageSize(CSalePaySystemAction::GetParamValue('SELLER_DIR_SIGN'));

		if ($signHeight && $signWidth)
		{
			$ratio = min(37.5/$signHeight, 150/$signWidth);
			$signHeight = $ratio * $signHeight;
			$signWidth  = $ratio * $signWidth;

			$isDirSign = true;
		}
	}

	if (CSalePaySystemAction::GetParamValue("SELLER_DIR"))
	{
		$pdf->SetX($pdf->GetX() + $width/2 + 15);
		$pdf->Write(15, CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("SELLER_DIR")));
		$pdf->Ln();
		$pdf->Ln();
	}

	$pdf->SetX($pdf->GetX() + $width/2 + 15);
	$pdf->Write(15, CSalePdf::prepareToPdf('The Director '));

	$pdf->Cell(0, 15, '', 'B');

	if ($isDirSign)
	{
		$pdf->Image(
			CSalePaySystemAction::GetParamValue('SELLER_DIR_SIGN'),
			$pdf->GetX() - 150, $pdf->GetY() - $signHeight + 15,
			$signWidth, $signHeight
		);
	}

	$pdf->Ln();
	$pdf->Ln();
}

if (CSalePaySystemAction::GetParamValue("SELLER_ACC") || CSalePaySystemAction::GetParamValue("SELLER_ACC_SIGN"))
{
	$isAccSign = false;
	if (CSalePaySystemAction::GetParamValue('SELLER_ACC_SIGN'))
	{
		list($signHeight, $signWidth) = $pdf->GetImageSize(CSalePaySystemAction::GetParamValue('SELLER_ACC_SIGN'));

		if ($signHeight && $signWidth)
		{
			$ratio = min(37.5/$signHeight, 150/$signWidth);
			$signHeight = $ratio * $signHeight;
			$signWidth  = $ratio * $signWidth;

			$isAccSign = true;
		}
	}

	if (CSalePaySystemAction::GetParamValue("SELLER_ACC"))
	{
		$pdf->SetX($pdf->GetX() + $width/2 + 15);
		$pdf->Write(15, CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("SELLER_ACC")));
		$pdf->Ln();
		$pdf->Ln();
	}

	$pdf->SetX($pdf->GetX() + $width/2 + 15);
	$pdf->Write(15, CSalePdf::prepareToPdf('The Accountant '));

	$pdf->Cell(0, 15, '', 'B');

	if ($isAccSign)
	{
		$pdf->Image(
			CSalePaySystemAction::GetParamValue('SELLER_ACC_SIGN'),
			$pdf->GetX() - 150, $pdf->GetY() - $signHeight + 15,
			$signWidth, $signHeight
		);
	}

	$pdf->Ln();
}


$dest = 'I';
if ($_REQUEST['GET_CONTENT'] == 'Y')
	$dest = 'S';
else if ($_REQUEST['DOWNLOAD'] == 'Y')
	$dest = 'D';

return $pdf->Output(
	sprintf(
		'Invoice # %s (Issue Date %s).pdf',
		$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ACCOUNT_NUMBER"],
		ConvertDateTime($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"], 'YYYY-MM-DD')
	), $dest
);
