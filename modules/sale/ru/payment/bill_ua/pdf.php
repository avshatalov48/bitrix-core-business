<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$arPaySysAction["ENCODING"] = "";
$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
if (!is_array($arOrder))
	$arOrder = CSaleOrder::GetByID($ORDER_ID);

if (!CSalePdf::isPdfAvailable())
	die();

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;

$pdf = new CSalePdf('P', 'pt', 'A4');

if (CSalePaySystemAction::GetParamValue('BACKGROUND', false))
{
	$pdf->SetBackground(
		CSalePaySystemAction::GetParamValue('BACKGROUND', false),
		CSalePaySystemAction::GetParamValue('BACKGROUND_STYLE', false)
	);
}

$pageWidth  = $pdf->GetPageWidth();
$pageHeight = $pdf->GetPageHeight();

$pdf->AddFont('Font', '', 'pt_sans-regular.ttf', true);
$pdf->AddFont('Font', 'B', 'pt_sans-bold.ttf', true);

$fontFamily = 'Font';
$fontSize   = 10.5;

$margin = array(
	'top' => intval(CSalePaySystemAction::GetParamValue('MARGIN_TOP', false) ?: 15) * 72/25.4,
	'right' => intval(CSalePaySystemAction::GetParamValue('MARGIN_RIGHT', false) ?: 15) * 72/25.4,
	'bottom' => intval(CSalePaySystemAction::GetParamValue('MARGIN_BOTTOM', false) ?: 15) * 72/25.4,
	'left' => intval(CSalePaySystemAction::GetParamValue('MARGIN_LEFT', false) ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

$pdf->SetDisplayMode(100, 'continuous');
$pdf->SetMargins($margin['left'], $margin['top'], $margin['right']);
$pdf->SetAutoPageBreak(true, $margin['bottom']);

$pdf->AddPage();


$pdf->SetFont($fontFamily, 'B', $fontSize);

$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(
	"Рахунок на оплату №%s від %s",
	$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ACCOUNT_NUMBER"],
	CSalePaySystemAction::GetParamValue("DATE_INSERT", false)
)));
$pdf->Ln();
$pdf->Ln();

$pdf->SetFont($fontFamily, '', $fontSize);

$title = CSalePdf::prepareToPdf('Постачальник: ');
$title_width = $pdf->GetStringWidth($title);
$pdf->Write(15, $title);

$pdf->Write(15, CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("SELLER_NAME", false)));
$pdf->Ln();

$pdf->Cell($title_width, 15, '');
$pdf->MultiCell(0, 15, CSalePdf::prepareToPdf(sprintf(
	'Р/р %s, Банк %s, МФО %s',
	CSalePaySystemAction::GetParamValue("SELLER_RS", false),
	CSalePaySystemAction::GetParamValue("SELLER_BANK", false),
	CSalePaySystemAction::GetParamValue("SELLER_MFO", false)
)));

$pdf->Cell($title_width, 15, '');
$pdf->MultiCell(0, 15, CSalePdf::prepareToPdf(sprintf(
	'Юридична адреса: %s, тел.: %s',
	CSalePaySystemAction::GetParamValue("SELLER_ADDRESS", false),
	CSalePaySystemAction::GetParamValue("SELLER_PHONE", false)
)));

$pdf->Cell($title_width, 15, '');
$pdf->MultiCell(0, 15, CSalePdf::prepareToPdf(sprintf(
	'ЄДРПОУ: %s, ІПН: %s, № свід. ПДВ: %s',
	CSalePaySystemAction::GetParamValue("SELLER_EDRPOY", false),
	CSalePaySystemAction::GetParamValue("SELLER_IPN", false),
	CSalePaySystemAction::GetParamValue("SELLER_PDV", false)
)));

if (CSalePaySystemAction::GetParamValue("SELLER_SYS", false))
{
	$pdf->Cell($title_width, 15, '');
	$pdf->Write(15, CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("SELLER_SYS", false)));
	$pdf->Ln();
}
$pdf->Ln();

$pdf->Cell($title_width, 15, CSalePdf::prepareToPdf('Покупець: '));

$pdf->Write(15, CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("BUYER_NAME", false)));
$pdf->Ln();

$buyerPhone = CSalePaySystemAction::GetParamValue("BUYER_PHONE", false);
$buyerFax = CSalePaySystemAction::GetParamValue("BUYER_FAX", false);
if ($buyerPhone || $buyerFax)
{
	$pdf->Cell($title_width, 15, '');

	if ($buyerPhone)
	{
		$pdf->Write(15, CSalePdf::prepareToPdf(sprintf('тел.: %s', $buyerPhone)));
		if ($buyerFax)
			$pdf->Write(15, CSalePdf::prepareToPdf(', '));
	}

	if ($buyerFax)
		$pdf->Write(15, CSalePdf::prepareToPdf(sprintf('факс: %s', $buyerFax)));

	$pdf->Ln();
}

if (CSalePaySystemAction::GetParamValue("BUYER_ADDRESS", false))
{
	$pdf->Cell($title_width, 15, '');

	$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(
		'Адреса: %s',
		CSalePaySystemAction::GetParamValue("BUYER_ADDRESS", false)
	)));

	$pdf->Ln();
}

$pdf->Ln();

if (CSalePaySystemAction::GetParamValue("BUYER_DOGOVOR", false))
{
	$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(
		'Договір: %s',
		CSalePaySystemAction::GetParamValue("BUYER_DOGOVOR", false)
	)));

	$pdf->Ln();
}


// Список товаров
$dbBasket = CSaleBasket::GetList(
	array("DATE_INSERT" => "ASC", "NAME" => "ASC"),
	array("ORDER_ID" => $ORDER_ID),
	false, false,
	array("ID", "PRICE", "CURRENCY", "QUANTITY", "NAME", "VAT_RATE", "MEASURE_NAME")
);
if ($arBasket = $dbBasket->Fetch())
{
	$arCells = array();
	$arProps = array();
	$arRowsWidth = array(1 => 0, 0, 0, 0, 0, 0, 0);

	$n = 0;
	$sum = 0.00;
	$vat = 0;
	do
	{
		// props in product basket
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

		$productName = $arBasket["NAME"];
		if ($productName == "OrderDelivery")
			$productName = "Доставка";
		else if ($productName == "OrderDiscount")
			$productName = "Знижка";

		$arCells[++$n] = array(
			1 => CSalePdf::prepareToPdf($n),
			CSalePdf::prepareToPdf($productName),
			CSalePdf::prepareToPdf(roundEx($arBasket["QUANTITY"], SALE_VALUE_PRECISION)),
			CSalePdf::prepareToPdf($arBasket["MEASURE_NAME"] ? $arBasket["MEASURE_NAME"] : 'шт.'),
			CSalePdf::prepareToPdf(SaleFormatCurrency($arBasket["PRICE"], $arBasket["CURRENCY"], true)),
			CSalePdf::prepareToPdf(roundEx($arBasket["VAT_RATE"]*100, SALE_VALUE_PRECISION)."%"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$arBasket["PRICE"] * $arBasket["QUANTITY"],
				$arBasket["CURRENCY"],
				true
			))
		);

		$arProps[$n] = array();
		foreach ($arBasket["PROPS"] as $vv)
			$arProps[$n][] = CSalePdf::prepareToPdf(sprintf("%s: %s", $vv["NAME"], $vv["VALUE"]));

		for ($i = 1; $i <= 7; $i++)
			$arRowsWidth[$i] = max($arRowsWidth[$i], $pdf->GetStringWidth($arCells[$n][$i]));

		$sum += doubleval($arBasket["PRICE"] * $arBasket["QUANTITY"]);
		$vat = max($vat, $arBasket["VAT_RATE"]);
	}
	while ($arBasket = $dbBasket->Fetch());

	if (DoubleVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"]) > 0)
	{
		$arDelivery_tmp = CSaleDelivery::GetByID($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DELIVERY_ID"]);

		$sDeliveryItem = "Доставка";
		if (strlen($arDelivery_tmp["NAME"]) > 0)
			$sDeliveryItem .= sprintf(" (%s)", $arDelivery_tmp["NAME"]);
		$arCells[++$n] = array(
			1 => CSalePdf::prepareToPdf($n),
			CSalePdf::prepareToPdf($sDeliveryItem),
			CSalePdf::prepareToPdf(1),
			CSalePdf::prepareToPdf(''),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"],
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				true
			)),
			CSalePdf::prepareToPdf(roundEx($vat*100, SALE_VALUE_PRECISION)."%"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"],
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				true
			))
		);

		for ($i = 1; $i <= 7; $i++)
			$arRowsWidth[$i] = max($arRowsWidth[$i], $pdf->GetStringWidth($arCells[$n][$i]));

		$sum += doubleval($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"]);
	}

	$items = $n;
/*
	if ($sum < $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE"])
	{
		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			CSalePdf::prepareToPdf("Подытог:"),
			CSalePdf::prepareToPdf(CurrencyFormatNumber($sum, $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]))
		);

		$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
	}
*/
	$orderTax = 0;
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
				($arTaxList["IS_IN_PRICE"] == "Y") ? "У тому числі " : "",
				($vat <= 0) ? $arTaxList["TAX_NAME"] : "ПДВ",
				($vat <= 0 && $arTaxList["IS_PERCENT"] == "Y")
					? sprintf(' (%s%%)', roundEx($arTaxList["VALUE"],SALE_VALUE_PRECISION))
					: ""
			)),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$arTaxList["VALUE_MONEY"],
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				true
			))
		);

		$orderTax += $arTaxList["VALUE_MONEY"];

		$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
	}

	if (DoubleVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SUM_PAID"]) > 0)
	{
		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			CSalePdf::prepareToPdf("Вже сплачено:"),
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
			CSalePdf::prepareToPdf("Знижка:"),
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
		CSalePdf::prepareToPdf($vat <= 0 ? "Всього без ПДВ:" : "Всього:"),
		CSalePdf::prepareToPdf(SaleFormatCurrency(
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"],
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
			true
		))
	);

	$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));

	$showVat = false;

	$arCurFormat = CCurrencyLang::GetCurrencyFormat($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]);
	$currency = trim(str_replace('#', '', $arCurFormat['FORMAT_STRING']));

	$arColsCaption = array(
		1 => CSalePdf::prepareToPdf('№'),
		CSalePdf::prepareToPdf('Товар/Послуга'),
		CSalePdf::prepareToPdf('Кіл-сть'),
		CSalePdf::prepareToPdf('Од.'),
		CSalePdf::prepareToPdf(($vat <= 0 ? 'Ціна без ПДВ, ' : 'Ціна з ПДВ, ').$currency),
		CSalePdf::prepareToPdf('Ставка ПДВ'),
		CSalePdf::prepareToPdf(($vat <= 0 ? 'Сума без ПДВ, ' : 'Сума з ПДВ, ').$currency)
	);
	for ($i = 1; $i <= 7; $i++)
		$arRowsWidth[$i] = max($arRowsWidth[$i], $pdf->GetStringWidth($arColsCaption[$i]));

	for ($i = 1; $i <= 7; $i++)
		$arRowsWidth[$i] += 10;
	if (!$showVat)
		$arRowsWidth[6] = 0;
	$arRowsWidth[2] = $width - (array_sum($arRowsWidth)-$arRowsWidth[2]);
}
$pdf->Ln();

$x0 = $pdf->GetX();
$y0 = $pdf->GetY();

for ($i = 1; $i <= 7; $i++)
{
	if ($showVat || $i != 6)
		$pdf->Cell($arRowsWidth[$i], 20, $arColsCaption[$i], 0, 0, 'C');
	${"x$i"} = $pdf->GetX();
}

$pdf->Ln();

$y5 = $pdf->GetY();

$pdf->Line($x0, $y0, $x7, $y0);
for ($i = 0; $i <= 7; $i++)
{
	if ($showVat || $i != 6)
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
		list($string, $text) = $pdf->splitString($text, $cellWidth-5);

		if (!is_null($arCells[$n][1]))
			$pdf->Cell($arRowsWidth_tmp[1], 15, ($l == 0) ? $arCells[$n][1] : '', 0, 0, 'C');
		if ($l == 0)
			$x1 = $pdf->GetX();

		if (!is_null($arCells[$n][2]))
			$pdf->Cell($arRowsWidth_tmp[2], 15, $string);
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

		if (!is_null($arCells[$n][6]))
		{
			if (is_null($arCells[$n][2]))
				$pdf->Cell($arRowsWidth_tmp[6], 15, $string, 0, 0, 'R');
			else if ($showVat)
				$pdf->Cell($arRowsWidth_tmp[6], 15, ($l == 0) ? $arCells[$n][6] : '', 0, 0, 'R');
		}
		if ($l == 0)
			$x6 = $pdf->GetX();

		if (!is_null($arCells[$n][7]))
			$pdf->Cell($arRowsWidth_tmp[7], 15, ($l == 0) ? $arCells[$n][7] : '', 0, 0, 'R');
		if ($l == 0)
			$x7 = $pdf->GetX();

		$pdf->Ln();
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
			if ($showVat)
				$pdf->Cell($arRowsWidth_tmp[6], 12, '');
			$pdf->Cell($arRowsWidth_tmp[7], 12, '', 0, 1);
		}
	}

	$y5 = $pdf->GetY();

	if ($y0 > $y5)
		$y0 = $margin['top'];
	for ($i = (is_null($arCells[$n][1])) ? 6 : 0; $i <= 7; $i++)
	{
		if ($showVat || $i != 5)
			$pdf->Line(${"x$i"}, $y0, ${"x$i"}, $y5);
	}

	$pdf->Line((!is_null($arCells[$n][1])) ? $x0 : $x6, $y5, $x7, $y5);
}
$pdf->Ln();


$pdf->SetFont($fontFamily, 'B', $fontSize);
$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(
	"Всього найменувань: %s, на суму %s",
	$items,
	($arOrder["CURRENCY"] == "UAH")
		? Number2Word_Rus(
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"],
			"Y",
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]
		)
		: SaleFormatCurrency(
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"],
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
			false
		)
)));
$pdf->Ln();

if ($vat > 0)
{
	$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(
		"У т.ч. ПДВ: %s",
		($arOrder["CURRENCY"] == "UAH")
			? Number2Word_Rus($orderTax, "Y", $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"])
			: SaleFormatCurrency($orderTax, $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"], false)
	)));
}
else
{
	$pdf->Write(15, CSalePdf::prepareToPdf("Без ПДВ"));
}
$pdf->Ln();
$pdf->Ln();

if (CSalePaySystemAction::GetParamValue("COMMENT1", false) || CSalePaySystemAction::GetParamValue("COMMENT2", false))
{
	$pdf->Write(15, CSalePdf::prepareToPdf('Умови та коментарі'));
	$pdf->Ln();

	$pdf->SetFont($fontFamily, '', $fontSize);

	if (CSalePaySystemAction::GetParamValue("COMMENT1", false))
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("COMMENT1", false))
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}

	if (CSalePaySystemAction::GetParamValue("COMMENT2", false))
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("COMMENT2", false))
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}
}

$pdf->Ln();
if (!$blank && CSalePaySystemAction::GetParamValue('PATH_TO_STAMP', false))
{
	list($stampHeight, $stampWidth) = $pdf->GetImageSize(CSalePaySystemAction::GetParamValue('PATH_TO_STAMP', false));

	if ($stampHeight && $stampWidth)
	{
		if ($stampHeight > 120 || $stampWidth > 120)
		{
			$ratio = 120 / max($stampHeight, $stampWidth);
			$stampHeight = $ratio * $stampHeight;
			$stampWidth  = $ratio * $stampWidth;
		}

		$pdf->Image(
			CSalePaySystemAction::GetParamValue('PATH_TO_STAMP', false),
			$margin['left']+40, $pdf->GetY(),
			$stampWidth, $stampHeight
		);
	}
}


$pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX()+$width, $pdf->GetY());
$pdf->Ln();
$pdf->Ln();

$isAccSign = false;
if (!$blank && CSalePaySystemAction::GetParamValue('SELLER_ACC_SIGN', false))
{
	list($signHeight, $signWidth) = $pdf->GetImageSize(CSalePaySystemAction::GetParamValue('SELLER_ACC_SIGN', false));

	if ($signHeight && $signWidth)
	{
		$ratio = min(37.5/$signHeight, 150/$signWidth);
		$signHeight = $ratio * $signHeight;
		$signWidth  = $ratio * $signWidth;

		$isAccSign = true;
	}
}

$pdf->SetFont($fontFamily, 'B', $fontSize);
$pdf->Write(15, CSalePdf::prepareToPdf('Виписав(ла): '));

if ($isAccSign)
{
	$pdf->Image(
		CSalePaySystemAction::GetParamValue('SELLER_ACC_SIGN', false),
		$pdf->GetX() + 80 - $signWidth/2, $pdf->GetY() - $signHeight + 15,
		$signWidth, $signHeight
	);
}

$pdf->SetFont($fontFamily, '', $fontSize);
$pdf->Cell(160, 15, '', 'B', 0, 'C');

$pdf->Write(15, CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("SELLER_ACC", false)));

$pdf->SetX(max($pdf->GetX()+20, $margin['left']+3*$width/5));

$pdf->SetFont($fontFamily, 'B', $fontSize);
$pdf->Write(15, CSalePdf::prepareToPdf('Посада: '));

$pdf->SetFont($fontFamily, '', $fontSize);
$pdf->Cell(0, 15, CSalePdf::prepareToPdf(CSalePaySystemAction::GetParamValue("SELLER_ACC_POS", false)), 'B', 0, 'C');

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

if (CSalePaySystemAction::GetParamValue("DATE_PAY_BEFORE", false))
{
	$pdf->SetFont($fontFamily, 'B', $fontSize);
	$pdf->Cell(0, 15, CSalePdf::prepareToPdf(sprintf(
		"Рахунок дійсний до сплати до %s",
		ConvertDateTime(CSalePaySystemAction::GetParamValue("DATE_PAY_BEFORE", false), FORMAT_DATE)
			?: CSalePaySystemAction::GetParamValue("DATE_PAY_BEFORE", false)
	)), 0, 0, 'R');
}


$dest = 'I';
if ($_REQUEST['GET_CONTENT'] == 'Y')
	$dest = 'S';
else if ($_REQUEST['DOWNLOAD'] == 'Y')
	$dest = 'D';

return $pdf->Output(
	sprintf(
		'Rakhunok No%s vid %s.pdf',
		$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ACCOUNT_NUMBER"],
		ConvertDateTime($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"], 'YYYY-MM-DD')
	), $dest
);
?>