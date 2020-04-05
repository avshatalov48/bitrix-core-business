<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
if (!is_array($arOrder))
	$arOrder = CSaleOrder::GetByID($ORDER_ID);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Invoice</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET?>">
<style>
	table { border-collapse: collapse; }
	table.it td { border: 1pt solid #000000; padding: 0pt 3pt; }
	table.inv td, table.sign td { padding: 0pt; }
	table.sign td { vertical-align: top; }
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
		<td>
			<b><?=CSalePaySystemAction::GetParamValue("SELLER_NAME", false); ?></b><br>
			<? if (CSalePaySystemAction::GetParamValue("SELLER_ADDRESS", false)) { ?>
			<b><?=CSalePaySystemAction::GetParamValue("SELLER_ADDRESS", false); ?></b><br>
			<? } ?>
			<? if (CSalePaySystemAction::GetParamValue("SELLER_PHONE", false)) { ?>
			<b><?=sprintf("Tel.: %s", CSalePaySystemAction::GetParamValue("SELLER_PHONE", false)); ?></b><br>
			<? } ?>
		</td>
	</tr>
</table>
<br>

<div style="text-align: center; font-size: 2em"><b>Invoice</b></div>

<br>
<br>

<table width="100%">
	<tr>
		<? if (CSalePaySystemAction::GetParamValue("BUYER_NAME", false)) { ?>
		<td>
			<b>To</b><br>
			<?=CSalePaySystemAction::GetParamValue("BUYER_NAME", false); ?><br>
			<? if (CSalePaySystemAction::GetParamValue("BUYER_ADDRESS", false)) { ?>
			<?=CSalePaySystemAction::GetParamValue("BUYER_ADDRESS", false); ?>
			<? } ?>
		</td>
		<? } ?>
		<td align="right">
			<table class="inv">
				<tr align="right">
					<td><b>Invoice #&nbsp;</b></td>
					<td><?=htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ACCOUNT_NUMBER"]); ?></td>
				</tr>
				<tr align="right">
					<td><b>Issue Date:&nbsp;</b></td>
					<td><?=CSalePaySystemAction::GetParamValue("DATE_INSERT", false); ?></td>
				</tr>
				<? if (CSalePaySystemAction::GetParamValue("DATE_PAY_BEFORE", false)) { ?>
				<tr align="right">
					<td><b>Due Date:&nbsp;</b></td>
					<td><?=(
						ConvertDateTime(CSalePaySystemAction::GetParamValue("DATE_PAY_BEFORE", false), FORMAT_DATE)
							?: CSalePaySystemAction::GetParamValue("DATE_PAY_BEFORE", false)
					); ?></td>
				</tr>
				<? } ?>
			</table>
		</td>
	</tr>
</table>

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
			$productName = "Shipping";
		else if ($productName == "OrderDiscount")
			$productName = "Discount";

		$arCells[++$n] = array(
			1 => $n,
			htmlspecialcharsbx($productName),
			roundEx($arBasket["QUANTITY"], SALE_VALUE_PRECISION),
			$arBasket["MEASURE_NAME"] ? htmlspecialcharsbx($arBasket["MEASURE_NAME"]) : 'pcs',
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

		$sDeliveryItem = "Shipping";
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
			"Subtotal:",
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
					"Tax (%s%%):",
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
					($arTaxList["IS_IN_PRICE"] == "Y") ? "Included " : "",
					$arTaxList["TAX_NAME"],
					sprintf(' (%s%%)', roundEx($arTaxList["VALUE"],SALE_VALUE_PRECISION))
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
			"Discount:",
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
		"Total:",
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
		<td><nobr>#</nobr></td>
		<td><nobr>Item / Description</nobr></td>
		<td><nobr>Qty</nobr></td>
		<td><nobr>Units</nobr></td>
		<td><nobr>Unit Price</nobr></td>
		<? if ($vat > 0) { ?>
		<td><nobr>Tax Rate</nobr></td>
		<? } ?>
		<td><nobr>Total</nobr></td>
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
<br>

<? if (CSalePaySystemAction::GetParamValue("COMMENT1", false) || CSalePaySystemAction::GetParamValue("COMMENT2", false)) { ?>
<b>Terms & Conditions</b>
<br>
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
<br>
<br>

<? $bankAccNo = CSalePaySystemAction::GetParamValue("SELLER_BANK_ACCNO", false); ?>
<? $bankRouteNo = CSalePaySystemAction::GetParamValue("SELLER_BANK_ROUTENO", false); ?>
<? $bankSwift = CSalePaySystemAction::GetParamValue("SELLER_BANK_SWIFT", false); ?>

<table class="sign" style="width: 100%; ">
	<tr>
		<td style="width: 50%; ">

		<? if ($bankAccNo && $bankRouteNo && $bankSwift) { ?>

			<b>Bank Details</b>
			<br>

			<? if (CSalePaySystemAction::GetParamValue("SELLER_NAME", false)) { ?>
				Account Name: <?=CSalePaySystemAction::GetParamValue("SELLER_NAME", false); ?>
				<br>
			<? } ?>

			Account #: <?=$bankAccNo; ?>
			<br>

			<? $bank = CSalePaySystemAction::GetParamValue("SELLER_BANK", false); ?>
			<? $bankAddr = CSalePaySystemAction::GetParamValue("SELLER_BANK_ADDR", false); ?>
			<? $bankPhone = CSalePaySystemAction::GetParamValue("SELLER_BANK_PHONE", false); ?>

			<? if ($bank || $bankAddr || $bankPhone) { ?>
				Bank Name and Address: <? if ($bank) { ?><?=$bank; ?><? } ?>
				<br>

				<? if ($bankAddr) { ?>
					<?=$bankAddr; ?>
					<br>
				<? } ?>

				<? if ($bankPhone) { ?>
					<?=$bankPhone; ?>
					<br>
				<? } ?>
			<? } ?>

			Bank's routing number: <?=$bankRouteNo; ?>
			<br>

			Bank SWIFT: <?=$bankSwift; ?>
			<br>
		<? } ?>

		</td>
		<td style="width: 50%; ">

			<? if (!$blank) { ?>
			<div style="position: relative; "><?=CFile::ShowImage(
				CSalePaySystemAction::GetParamValue("PATH_TO_STAMP", false),
				160, 160,
				'style="position: absolute; left: 30pt; "'
			); ?></div>
			<? } ?>

			<table style="width: 100%; position: relative; ">
				<colgroup>
					<col width="0">
					<col width="100%">
				</colgroup>
				<? if (CSalePaySystemAction::GetParamValue("SELLER_DIR_POS", false)) { ?>
				<? if (CSalePaySystemAction::GetParamValue("SELLER_DIR", false) || CSalePaySystemAction::GetParamValue("SELLER_DIR_SIGN", false)) { ?>
				<? if (CSalePaySystemAction::GetParamValue("SELLER_DIR", false)) { ?>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td colspan="2"><?=CSalePaySystemAction::GetParamValue("SELLER_DIR", false); ?></td>
				</tr>
				<? } ?>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><nobr><?=CSalePaySystemAction::GetParamValue("SELLER_DIR_POS", false); ?></nobr></td>
					<td style="border-bottom: 1pt solid #000000; text-align: center; ">
						<? if (!$blank && CSalePaySystemAction::GetParamValue("SELLER_DIR_SIGN", false)) { ?>
						<span style="position: relative; ">&nbsp;<?=CFile::ShowImage(
							CSalePaySystemAction::GetParamValue("SELLER_DIR_SIGN", false),
							200, 50,
							'style="position: absolute; margin-left: -75pt; bottom: 0pt; "'
						); ?></span>
						<? } ?>
					</td>
				</tr>
				<? } ?>
				<? } ?>
				<? if (CSalePaySystemAction::GetParamValue("SELLER_ACC_POS", false)) { ?>
				<? if (CSalePaySystemAction::GetParamValue("SELLER_ACC", false) || CSalePaySystemAction::GetParamValue("SELLER_ACC_SIGN", false)) { ?>
				<? if (CSalePaySystemAction::GetParamValue("SELLER_ACC", false)) { ?>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td colspan="2"><?=CSalePaySystemAction::GetParamValue("SELLER_ACC", false); ?></td>
				</tr>
				<? } ?>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><nobr><?=CSalePaySystemAction::GetParamValue("SELLER_ACC_POS", false); ?></nobr></td>
					<td style="border-bottom: 1pt solid #000000; text-align: center; ">
						<? if (!$blank && CSalePaySystemAction::GetParamValue("SELLER_ACC_SIGN", false)) { ?>
						<span style="position: relative; ">&nbsp;<?=CFile::ShowImage(
							CSalePaySystemAction::GetParamValue("SELLER_ACC_SIGN", false),
							200, 50,
							'style="position: absolute; margin-left: -75pt; bottom: 0pt; "'
						); ?></span>
						<? } ?>
					</td>
				</tr>
				<? } ?>
				<? } ?>
			</table>

		</td>
	</tr>
</table>

</div>

</body>
</html>