<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
if (!is_array($arOrder))
	$arOrder = CSaleOrder::GetByID($ORDER_ID);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Счет</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET?>">
<style>
	table { border-collapse: collapse; }
	table.it td { border: 1pt solid #000000; padding: 0pt 3pt; }
</style>
</head>

<?

$pageWidth  = 595.28;
$pageHeight = 841.89;

$background = '#ffffff';
if (CSalePaySystemAction::GetParamValue('BACKGROUND'))
{
	$path = CSalePaySystemAction::GetParamValue('BACKGROUND');
	if (intval($path) > 0)
	{
		if ($arFile = CFile::GetFileArray($path))
			$path = $arFile['SRC'];
	}

	$backgroundStyle = CSalePaySystemAction::GetParamValue('BACKGROUND_STYLE');
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
	'top' => intval(CSalePaySystemAction::GetParamValue('MARGIN_TOP') ?: 15) * 72/25.4,
	'right' => intval(CSalePaySystemAction::GetParamValue('MARGIN_RIGHT') ?: 15) * 72/25.4,
	'bottom' => intval(CSalePaySystemAction::GetParamValue('MARGIN_BOTTOM') ?: 15) * 72/25.4,
	'left' => intval(CSalePaySystemAction::GetParamValue('MARGIN_LEFT') ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

?>

<body
	style="margin: 0pt; padding: <?=join('pt ', $margin); ?>pt; width: <?=$width; ?>pt; background: <?=$background; ?>"
	<? if ($_REQUEST['PRINT'] == 'Y') { ?>
	onload="setTimeout(window.print, 0);"
	<? } ?>
>

<?=CFile::ShowImage(
	CSalePaySystemAction::GetParamValue("PATH_TO_LOGO"),
	0, 0,
	'style="float: left; padding-right: 5pt; "'
); ?>

<div style="font-size: 3em; float: left; ">
	<b><?=CSalePaySystemAction::GetParamValue("SELLER_NAME"); ?></b>
</div>
<div style="clear: both; height: 5pt; "></div>
<br>


<span style="text-decoration: underline">
	<small>
		<b><?=CSalePaySystemAction::GetParamValue("SELLER_NAME"); ?><?
		if (CSalePaySystemAction::GetParamValue("SELLER_ADDRESS"))
		{
			?> – <?=CSalePaySystemAction::GetParamValue("SELLER_ADDRESS"); ?><?
		}
?></b></small></span>
<br>
<br>
<br>


<? if (CSalePaySystemAction::GetParamValue("BUYER_NAME")) { ?>
	<b><?=CSalePaySystemAction::GetParamValue("BUYER_NAME"); ?></b>
	<br>

	<? if (CSalePaySystemAction::GetParamValue("BUYER_ADDRESS")) { ?>
	<b><?=CSalePaySystemAction::GetParamValue("BUYER_ADDRESS"); ?></b>
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

<table width="100%">
	<tr style="font-weight: bold">
		<td><?=sprintf(
			'Rechnung Nr. %s',
			htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ACCOUNT_NUMBER"])
		); ?></td>
		<td><?=sprintf(
			'Kunden-Nr.: %s',
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["USER_ID"]
		); ?></td>
		<td align="right"><?=sprintf(
			'Datum: %s',
			CSalePaySystemAction::GetParamValue("DATE_INSERT")
		); ?></td>
	</tr>
</table>
<small><b>Bitte bei Zahlungen und Schriftverkehr angeben!</b></small>
<br>
<br>
<br>


<?

$dbBasket = CSaleBasket::GetList(
	array("NAME" => "ASC"),
	array("ORDER_ID" => $ORDER_ID)
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
			1 => $n,
			htmlspecialcharsbx($arBasket["NAME"]),
			roundEx($arBasket["QUANTITY"], SALE_VALUE_PRECISION),
			'St.',
			SaleFormatCurrency($arBasket["VATLESS_PRICE"], $arBasket["CURRENCY"], true),
			roundEx($arBasket["VAT_RATE"]*100, SALE_VALUE_PRECISION) . "%",
			SaleFormatCurrency(
				$arBasket["VATLESS_PRICE"] * $arBasket["QUANTITY"],
				$arBasket["CURRENCY"],
				true
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
				true
			),
			roundEx($vat*100, SALE_VALUE_PRECISION) . "%",
			SaleFormatCurrency(
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"] / (1 + $vat),
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				true
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
			SaleFormatCurrency($sum, $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"], true)
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
					true
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
					true
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
				true
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
				true
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
			true
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
			<? if ($accumulated) {
				?> style="border-width: 0pt 1pt 0pt 0pt" colspan="<?=($accumulated+1); ?>"<? $accumulated = 0;
			} ?>>
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

<? if (CSalePaySystemAction::GetParamValue("COMMENT1") || CSalePaySystemAction::GetParamValue("COMMENT2")) { ?>
	<? if (CSalePaySystemAction::GetParamValue("COMMENT1")) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback(CSalePaySystemAction::GetParamValue("COMMENT1"))
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
	<? if (CSalePaySystemAction::GetParamValue("COMMENT2")) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback(CSalePaySystemAction::GetParamValue("COMMENT2"))
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
<? } ?>

<br>

<div style="position: relative; "><?=CFile::ShowImage(
	CSalePaySystemAction::GetParamValue("PATH_TO_STAMP"),
	0, 0,
	'style="position: absolute; left: 40pt; "'
); ?></div>

<div style="position: relative">
	<table class="sign">
		<? if (CSalePaySystemAction::GetParamValue("SELLER_DIR") || CSalePaySystemAction::GetParamValue("SELLER_DIR_SIGN")) { ?>
		<tr>
			<td valign>Geschдftsfьhrer</td>
			<td style="width: 160pt; border: 1pt solid #000000; border-width: 0pt 0pt 1pt 0pt; text-align: center; ">
				<?=CFile::ShowImage(CSalePaySystemAction::GetParamValue("SELLER_DIR_SIGN"), 200, 50); ?>
			</td>
			<td>
				<? if (CSalePaySystemAction::GetParamValue("SELLER_DIR")) { ?>
				(<?=CSalePaySystemAction::GetParamValue("SELLER_DIR"); ?>)
				<? } ?>
			</td>
		</tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<? } ?>
		<? if (CSalePaySystemAction::GetParamValue("SELLER_ACC") || CSalePaySystemAction::GetParamValue("SELLER_ACC_SIGN")) { ?>
		<tr>
			<td>Buchhalter</td>
			<td style="width: 160pt; border: 1pt solid #000000; border-width: 0pt 0pt 1pt 0pt; text-align: center; ">
				<?=CFile::ShowImage(CSalePaySystemAction::GetParamValue("SELLER_ACC_SIGN"), 200, 50); ?>
			</td>
			<td>
				<? if (CSalePaySystemAction::GetParamValue("SELLER_ACC")) { ?>
				(<?=CSalePaySystemAction::GetParamValue("SELLER_ACC"); ?>)
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

$sellerName = CSalePaySystemAction::GetParamValue("SELLER_NAME");
$sellerAddr = CSalePaySystemAction::GetParamValue("SELLER_ADDRESS");

$sellerData = array();

if ($sellerName)
	$sellerData[] = $sellerName;
if ($sellerAddr)
	$sellerData[] = $sellerAddr;

if (!empty($sellerData))
{
	?><small><?=join(' – ', $sellerData); ?></small>
	<br><?
}


$sellerPhone = CSalePaySystemAction::GetParamValue("SELLER_PHONE");
$sellerEmail = CSalePaySystemAction::GetParamValue("SELLER_EMAIL");

$sellerData = array();

if ($sellerPhone)
	$sellerData[] = sprintf('Telefon: %s', $sellerPhone);
if ($sellerEmail)
	$sellerData[] = sprintf('Mail: %s', $sellerEmail);

if (!empty($sellerData))
{
	?><small><?=join(' – ', $sellerData); ?></small>
	<br><?
}


$bankAccNo = CSalePaySystemAction::GetParamValue("SELLER_BANK_ACCNO");
$bankBlz   = CSalePaySystemAction::GetParamValue("SELLER_BANK_BLZ");
$bankIban  = CSalePaySystemAction::GetParamValue("SELLER_BANK_IBAN");
$bankSwift = CSalePaySystemAction::GetParamValue("SELLER_BANK_SWIFT");
$bank      = CSalePaySystemAction::GetParamValue("SELLER_BANK");

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
	?><small><?=join(' – ', $bankData); ?></small>
	<br><?
}


$sellerEuInn = CSalePaySystemAction::GetParamValue("SELLER_EU_INN");
$sellerInn   = CSalePaySystemAction::GetParamValue("SELLER_INN");
$sellerReg   = CSalePaySystemAction::GetParamValue("SELLER_REG");
$sellerDir   = CSalePaySystemAction::GetParamValue("SELLER_DIR");

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
	?><small><?=join(' – ', $sellerData); ?></small>
	<br><?
}

?>

</div>

</body>
</html>