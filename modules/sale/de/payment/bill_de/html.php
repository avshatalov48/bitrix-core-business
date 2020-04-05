<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
if (!is_array($arOrder))
	$arOrder = CSaleOrder::GetByID($ORDER_ID);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Rechnung</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET?>">
<style>
	table { border-collapse: collapse; }
	table.it td { border: 1pt solid #000000; padding: 0pt 3pt; }
	table.sign td { vertical-align: bottom }
	table.header td { padding: 0pt; vertical-align: top; }
</style>
</head>

<?

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;

$pageWidth  = 595.28;
$pageHeight = 841.89;

$background = '#ffffff';
if (CSalePaySystemAction::GetParamValue('BACKGROUND', false))
{
	$path = CSalePaySystemAction::GetParamValue('BACKGROUND', false);
	if (intval($path) > 0)
	{
		if ($arFile = CFile::GetFileArray($path))
			$path = $arFile['SRC'];
	}

	$backgroundStyle = CSalePaySystemAction::GetParamValue('BACKGROUND_STYLE', false);
	if (!in_array($backgroundStyle, array('none', 'tile', 'stretch')))
		$backgroundStyle = 'none';

	if ($path)
	{
		switch ($backgroundStyle)
		{
			case 'none':
				$background = "url('" . $path . "') 0 0 no-repeat";
				break;
			case 'tile':
				$background = "url('" . $path . "') 0 0 repeat";
				break;
			case 'stretch':
				$background = sprintf(
					"url('%s') 0 0 repeat-y; background-size: %.02fpt %.02fpt",
					$path, $pageWidth, $pageHeight
				);
				break;
		}
	}
}

$margin = array(
	'top' => intval(CSalePaySystemAction::GetParamValue('MARGIN_TOP', false) ?: 15) * 72/25.4,
	'right' => intval(CSalePaySystemAction::GetParamValue('MARGIN_RIGHT', false) ?: 15) * 72/25.4,
	'bottom' => intval(CSalePaySystemAction::GetParamValue('MARGIN_BOTTOM', false) ?: 15) * 72/25.4,
	'left' => intval(CSalePaySystemAction::GetParamValue('MARGIN_LEFT', false) ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

?>

<body style="margin: 0pt; padding: 0pt;"<? if ($_REQUEST['PRINT'] == 'Y') { ?> onload="setTimeout(window.print, 0);"<? } ?>>

<div style="margin: 0pt; padding: <?=join('pt ', $margin); ?>pt; width: <?=$width; ?>pt; background: <?=$background; ?>">

<table class="header">
	<tr>
		<? if (CSalePaySystemAction::GetParamValue("PATH_TO_LOGO", false)) { ?>
		<td style="padding-right: 5pt; ">
			<? $imgParams = CFile::_GetImgParams(CSalePaySystemAction::GetParamValue('PATH_TO_LOGO', false)); ?>
			<? $imgWidth = $imgParams['WIDTH'] * 96 / (intval(CSalePaySystemAction::GetParamValue('LOGO_DPI', false)) ?: 96); ?>
			<img src="<?=$imgParams['SRC']; ?>" width="<?=$imgWidth; ?>" />
		</td>
		<? } ?>
		<td style="font-size: 3em; ">
			<b><?=CSalePaySystemAction::GetParamValue("SELLER_NAME", false); ?></b>
		</td>
	</tr>
</table>
<br>

<span style="text-decoration: underline">
	<small>
		<b><?=CSalePaySystemAction::GetParamValue("SELLER_NAME", false); ?><?
		if (CSalePaySystemAction::GetParamValue("SELLER_ADDRESS", false))
		{
			?> - <?=CSalePaySystemAction::GetParamValue("SELLER_ADDRESS", false); ?><?
		}
?></b></small></span>
<br>
<br>
<br>


<? if (CSalePaySystemAction::GetParamValue("BUYER_NAME", false)) { ?>
	<b><?=CSalePaySystemAction::GetParamValue("BUYER_NAME", false); ?></b>
	<br>

	<? if (CSalePaySystemAction::GetParamValue("BUYER_ADDRESS", false)) { ?>
	<b><?=CSalePaySystemAction::GetParamValue("BUYER_ADDRESS", false); ?></b>
	<br>
	<? } ?>
<? } ?>

<br>
<br>
<br>
<br>

<span style="font-size: 2em"><b>Rechnung</b></span>

<br>
<br>
<br>

<table width="100%" style="font-weight: bold">
	<tr>
		<td><?=sprintf(
			'Rechnung Nr. %s',
			htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ACCOUNT_NUMBER"])
		); ?></td>
		<td><? if (CSalePaySystemAction::GetParamValue("BUYER_ID", false)) {
		echo sprintf(
			'Kunden-Nr.: %s',
			CSalePaySystemAction::GetParamValue("BUYER_ID", false)
		); } ?></td>
		<td align="right"><?=sprintf(
			'Datum: %s',
			CSalePaySystemAction::GetParamValue("DATE_INSERT", false)
		); ?></td>
	</tr>
	<? if (CSalePaySystemAction::GetParamValue("DATE_PAY_BEFORE", false)) { ?>
	<tr>
		<td></td>
		<td></td>
		<td align="right"><?=sprintf(
			'Bezahlen bis: %s',
			ConvertDateTime(CSalePaySystemAction::GetParamValue("DATE_PAY_BEFORE", false), FORMAT_DATE)
				?: CSalePaySystemAction::GetParamValue("DATE_PAY_BEFORE", false)
		); ?></td>
	</tr>
	<? } ?>
</table>
<small><b>Bitte bei Zahlungen und Schriftverkehr angeben!</b></small>
<br>
<br>
<br>


<?

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

	$n = 0;
	$sum = 0.00;
	$vat = 0;
	$vats = array();
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

		// @TODO: replace with real vatless price
		if (isset($arBasket['VAT_INCLUDED']) && $arBasket['VAT_INCLUDED'] === 'Y')
			$arBasket["VATLESS_PRICE"] = roundEx($arBasket["PRICE"] / (1 + $arBasket["VAT_RATE"]), SALE_VALUE_PRECISION);
		else
			$arBasket["VATLESS_PRICE"] = $arBasket["PRICE"];

		$productName = $arBasket["NAME"];
		if ($productName == "OrderDelivery")
			$productName = "Schifffahrt";
		else if ($productName == "OrderDiscount")
			$productName = "Rabatt";

		$arCells[++$n] = array(
			1 => $n,
			htmlspecialcharsbx($productName),
			roundEx($arBasket["QUANTITY"], SALE_VALUE_PRECISION),
			$arBasket["MEASURE_NAME"] ? htmlspecialcharsbx($arBasket["MEASURE_NAME"]) : 'St.',
			SaleFormatCurrency($arBasket["VATLESS_PRICE"], $arBasket["CURRENCY"], false),
			roundEx($arBasket["VAT_RATE"]*100, SALE_VALUE_PRECISION) . "%",
			SaleFormatCurrency(
				$arBasket["VATLESS_PRICE"] * $arBasket["QUANTITY"],
				$arBasket["CURRENCY"],
				false
			)
		);

		$arProps[$n] = array();
		foreach ($arBasket["PROPS"] as $vv)
			$arProps[$n][] = htmlspecialcharsbx(sprintf("%s: %s", $vv["NAME"], $vv["VALUE"]));

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

		$sDeliveryItem = "Schifffahrt";
		if (strlen($arDelivery_tmp["NAME"]) > 0)
			$sDeliveryItem .= sprintf(" (%s)", $arDelivery_tmp["NAME"]);
		$arCells[++$n] = array(
			1 => $n,
			htmlspecialcharsbx($sDeliveryItem),
			1,
			'',
			SaleFormatCurrency(
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"] / (1 + $vat),
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				false
			),
			roundEx($vat*100, SALE_VALUE_PRECISION) . "%",
			SaleFormatCurrency(
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"] / (1 + $vat),
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				false
			)
		);

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
			"Nettobetrag:",
			SaleFormatCurrency($sum, $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"], false)
		);
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
				sprintf(
					"zzgl. %s%% MwSt:",
					roundEx($vatRate * 100, SALE_VALUE_PRECISION)
				),
				SaleFormatCurrency(
					$vatSum,
					$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
					false
				)
			);
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
				htmlspecialcharsbx(sprintf(
					"%s%s%s:",
					($arTaxList["IS_IN_PRICE"] == "Y") ? "inkl." : "zzgl.",
					sprintf(' %s%% ', roundEx($arTaxList["VALUE"], SALE_VALUE_PRECISION)),
					$arTaxList["TAX_NAME"]
				)),
				SaleFormatCurrency(
					$arTaxList["VALUE_MONEY"],
					$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
					false
				)
			);
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
			"Payment made:",
			SaleFormatCurrency(
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SUM_PAID"],
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				false
			)
		);
	}

	if (DoubleVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DISCOUNT_VALUE"]) > 0)
	{
		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			"Rabatt:",
			SaleFormatCurrency(
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DISCOUNT_VALUE"],
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				false
			)
		);
	}

	$arCells[++$n] = array(
		1 => null,
		null,
		null,
		null,
		null,
		"Gesamtbetrag:",
		SaleFormatCurrency(
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"],
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
			false
		)
	);
}

?>
<table class="it" width="100%">
	<tr>
		<td><nobr>Pos.</nobr></td>
		<td><nobr>Leistung</nobr></td>
		<td><nobr>Anzahl</nobr></td>
		<td><nobr>Einheit</nobr></td>
		<td><nobr>Einzelpreis</nobr></td>
		<? if ($vat > 0) { ?>
		<td><nobr>MwSt.</nobr></td>
		<? } ?>
		<td><nobr>Gesamtpreis</nobr></td>
	</tr>
<?

$rowsCnt = count($arCells);
for ($n = 1; $n <= $rowsCnt; $n++)
{
	$accumulated = 0;

?>
	<tr valign="top">
		<? if (!is_null($arCells[$n][1])) { ?>
		<td align="center"><?=$arCells[$n][1]; ?></td>
		<? } else {
			$accumulated++;
		} ?>
		<? if (!is_null($arCells[$n][2])) { ?>
		<td align="left"
			style="word-break: break-word; word-wrap: break-word; <? if ($accumulated) {?>border-width: 0pt 1pt 0pt 0pt; <? } ?>"
			<? if ($accumulated) { ?>colspan="<?=($accumulated+1); ?>"<? $accumulated = 0; } ?>>
			<?=$arCells[$n][2]; ?>
			<? if (isset($arProps[$n]) && is_array($arProps[$n])) { ?>
			<? foreach ($arProps[$n] as $property) { ?>
			<br>
			<small><?=$property; ?></small>
			<? } ?>
			<? } ?>
		</td>
		<? } else {
			$accumulated++;
		} ?>
		<? for ($i = 3; $i <= 7; $i++) { ?>
			<? if (!is_null($arCells[$n][$i])) { ?>
				<? if ($i != 6 || $vat > 0 || is_null($arCells[$n][2])) { ?>
				<td align="right"
					<? if ($accumulated) { ?>
					style="border-width: 0pt 1pt 0pt 0pt"
					colspan="<?=(($i == 6 && $vat <= 0) ? $accumulated : $accumulated+1); ?>"
					<? $accumulated = 0; } ?>>
					<nobr><?=$arCells[$n][$i]; ?></nobr>
				</td>
				<? }
			} else {
				$accumulated++;
			}
		} ?>
	</tr>
<?

}

?>
</table>
<br>
<br>
<br>

<? if (CSalePaySystemAction::GetParamValue("COMMENT1", false) || CSalePaySystemAction::GetParamValue("COMMENT2", false)) { ?>
	<? if (CSalePaySystemAction::GetParamValue("COMMENT1", false)) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback(CSalePaySystemAction::GetParamValue("COMMENT1", false))
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
	<? if (CSalePaySystemAction::GetParamValue("COMMENT2", false)) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback(CSalePaySystemAction::GetParamValue("COMMENT2", false))
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
<? } ?>

<br>

<? if (!$blank) { ?>
<div style="position: relative; "><?=CFile::ShowImage(
	CSalePaySystemAction::GetParamValue("PATH_TO_STAMP", false),
	160, 160,
	'style="position: absolute; left: 40pt; "'
); ?></div>
<? } ?>

<div style="position: relative">
	<table class="sign">
		<? if (CSalePaySystemAction::GetParamValue("SELLER_DIR_POS", false)) { ?>
		<tr>
			<td style="width: 150pt; "><?=CSalePaySystemAction::GetParamValue("SELLER_DIR_POS", false); ?></td>
			<td style="width: 160pt; border: 1pt solid #000000; border-width: 0pt 0pt 1pt 0pt; text-align: center; ">
				<? if (!$blank) { ?>
				<?=CFile::ShowImage(CSalePaySystemAction::GetParamValue("SELLER_DIR_SIGN", false), 200, 50); ?>
				<? } ?>
			</td>
			<td>
				<? if (CSalePaySystemAction::GetParamValue("SELLER_DIR", false)) { ?>
				(<?=CSalePaySystemAction::GetParamValue("SELLER_DIR", false); ?>)
				<? } ?>
			</td>
		</tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<? } ?>
		<? if (CSalePaySystemAction::GetParamValue("SELLER_ACC_POS", false)) { ?>
		<tr>
			<td style="width: 150pt; "><?=CSalePaySystemAction::GetParamValue("SELLER_ACC_POS", false); ?></td>
			<td style="width: 160pt; border: 1pt solid #000000; border-width: 0pt 0pt 1pt 0pt; text-align: center; ">
				<? if (!$blank) { ?>
				<?=CFile::ShowImage(CSalePaySystemAction::GetParamValue("SELLER_ACC_SIGN", false), 200, 50); ?>
				<? } ?>
			</td>
			<td>
				<? if (CSalePaySystemAction::GetParamValue("SELLER_ACC", false)) { ?>
				(<?=CSalePaySystemAction::GetParamValue("SELLER_ACC", false); ?>)
				<? } ?>
			</td>
		</tr>
		<? } ?>
	</table>
</div>

<br>
<br>
<br>


<div style="text-align: center">

<?

$sellerName = CSalePaySystemAction::GetParamValue("SELLER_NAME", false);
$sellerAddr = CSalePaySystemAction::GetParamValue("SELLER_ADDRESS", false);

$sellerData = array();

if ($sellerName)
	$sellerData[] = $sellerName;
if ($sellerAddr)
	$sellerData[] = $sellerAddr;

if (!empty($sellerData))
{
	?><small><?=join(' - ', $sellerData); ?></small>
	<br><?
}


$sellerPhone = CSalePaySystemAction::GetParamValue("SELLER_PHONE", false);
$sellerEmail = CSalePaySystemAction::GetParamValue("SELLER_EMAIL", false);

$sellerData = array();

if ($sellerPhone)
	$sellerData[] = sprintf('Telefon: %s', $sellerPhone);
if ($sellerEmail)
	$sellerData[] = sprintf('Mail: %s', $sellerEmail);

if (!empty($sellerData))
{
	?><small><?=join(' - ', $sellerData); ?></small>
	<br><?
}


$bankAccNo = CSalePaySystemAction::GetParamValue("SELLER_BANK_ACCNO", false);
$bankBlz   = CSalePaySystemAction::GetParamValue("SELLER_BANK_BLZ", false);
$bankIban  = CSalePaySystemAction::GetParamValue("SELLER_BANK_IBAN", false);
$bankSwift = CSalePaySystemAction::GetParamValue("SELLER_BANK_SWIFT", false);
$bank      = CSalePaySystemAction::GetParamValue("SELLER_BANK", false);

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
	?><small><?=join(' - ', $bankData); ?></small>
	<br><?
}


$sellerEuInn = CSalePaySystemAction::GetParamValue("SELLER_EU_INN", false);
$sellerInn   = CSalePaySystemAction::GetParamValue("SELLER_INN", false);
$sellerReg   = CSalePaySystemAction::GetParamValue("SELLER_REG", false);
$sellerDir   = CSalePaySystemAction::GetParamValue("SELLER_DIR", false);

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
	?><small><?=join(' - ', $sellerData); ?></small>
	<br><?
}

?>

</div>

</div>

</body>
</html>