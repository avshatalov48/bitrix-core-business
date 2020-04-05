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
	table.acc td { border: 1pt solid #000000; padding: 0pt 3pt; line-height: 21pt; }
	table.it td { border: 1pt solid #000000; padding: 0pt 3pt; }
	table.sign td { font-weight: bold; vertical-align: bottom; }
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
		<td style="padding-right: 5pt; padding-bottom: 5pt; ">
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
			<b><?=sprintf("Тел.: %s", CSalePaySystemAction::GetParamValue("SELLER_PHONE", false)); ?></b><br>
			<? } ?>
		</td>
	</tr>
</table>

<?

if (CSalePaySystemAction::GetParamValue("SELLER_BANK", false))
{
	$sellerBank = sprintf(
		"%s %s",
		CSalePaySystemAction::GetParamValue("SELLER_BANK", false),
		CSalePaySystemAction::GetParamValue("SELLER_BCITY", false)
	);
	$sellerRs = CSalePaySystemAction::GetParamValue("SELLER_RS", false);
}
else
{
	$rsPattern = '/\s*\d{10,100}\s*/';

	$sellerBank = trim(preg_replace($rsPattern, ' ', CSalePaySystemAction::GetParamValue("SELLER_RS", false)));

	preg_match($rsPattern, CSalePaySystemAction::GetParamValue("SELLER_RS", false), $matches);
	$sellerRs = trim($matches[0]);
}

?>
<table class="acc" width="100%">
	<colgroup>
		<col width="29%">
		<col width="29%">
		<col width="10%">
		<col width="32%">
	</colgroup>
	<tr>
		<td>
			<? if (CSalePaySystemAction::GetParamValue("SELLER_INN", false)) { ?>
			<?=sprintf("ИНН %s", CSalePaySystemAction::GetParamValue("SELLER_INN", false)); ?>
			<? } else { ?>
			&nbsp;
			<? } ?>
		</td>
		<td>
			<? if (CSalePaySystemAction::GetParamValue("SELLER_KPP", false)) { ?>
			<?=sprintf("КПП %s", CSalePaySystemAction::GetParamValue("SELLER_KPP", false)); ?>
			<? } else { ?>
			&nbsp;
			<? } ?>
		</td>
		<td rowspan="2">
			<br>
			<br>
			Сч. №
		</td>
		<td rowspan="2">
			<br>
			<br>
			<?=$sellerRs; ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			Получатель<br>
			<?=CSalePaySystemAction::GetParamValue("SELLER_NAME", false); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			Банк получателя<br>
			<?=$sellerBank; ?>
		</td>
		<td>
			БИК<br>
			Сч. №
		</td>
		<td>
			<?=CSalePaySystemAction::GetParamValue("SELLER_BIK", false); ?><br>
			<?=CSalePaySystemAction::GetParamValue("SELLER_KS", false); ?>
		</td>
	</tr>
</table>

<br>
<br>

<table width="100%">
	<colgroup>
		<col width="50%">
		<col width="0">
		<col width="50%">
	</colgroup>
	<tr>
		<td></td>
		<td style="font-size: 2em; font-weight: bold; text-align: center"><nobr><?=sprintf(
			"СЧЕТ № %s от %s",
			htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ACCOUNT_NUMBER"]),
			CSalePaySystemAction::GetParamValue("DATE_INSERT", false)
		); ?></nobr></td>
		<td></td>
	</tr>
<? if (CSalePaySystemAction::GetParamValue("ORDER_SUBJECT", false)) { ?>
	<tr>
		<td></td>
		<td><?=CSalePaySystemAction::GetParamValue("ORDER_SUBJECT", false); ?></td>
		<td></td>
	</tr>
<? } ?>
<? if (CSalePaySystemAction::GetParamValue("DATE_PAY_BEFORE", false)) { ?>
	<tr>
		<td></td>
		<td><?=sprintf(
			"Срок оплаты %s",
			ConvertDateTime(CSalePaySystemAction::GetParamValue("DATE_PAY_BEFORE", false), FORMAT_DATE)
				?: CSalePaySystemAction::GetParamValue("DATE_PAY_BEFORE", false)
		); ?></td>
		<td></td>
	</tr>
<? } ?>
</table>

<br>
<?

if (CSalePaySystemAction::GetParamValue("BUYER_NAME", false)) {

	echo sprintf(
		"Плательщик: %s",
		CSalePaySystemAction::GetParamValue("BUYER_NAME", false)
	);
	if (CSalePaySystemAction::GetParamValue("BUYER_INN", false))
		echo sprintf(" ИНН %s", CSalePaySystemAction::GetParamValue("BUYER_INN", false));
	if (CSalePaySystemAction::GetParamValue("BUYER_ADDRESS", false))
		echo sprintf(", %s", CSalePaySystemAction::GetParamValue("BUYER_ADDRESS", false));
	if (CSalePaySystemAction::GetParamValue("BUYER_PHONE", false))
		echo sprintf(", %s", CSalePaySystemAction::GetParamValue("BUYER_PHONE", false));
	if (CSalePaySystemAction::GetParamValue("BUYER_FAX", false))
		echo sprintf(", %s", CSalePaySystemAction::GetParamValue("BUYER_FAX", false));
	if (CSalePaySystemAction::GetParamValue("BUYER_PAYER_NAME", false))
		echo sprintf(", %s", CSalePaySystemAction::GetParamValue("BUYER_PAYER_NAME", false));
}

?>

<br>
<br>

<?

$arBasketItems = CSalePaySystemAction::GetParamValue("BASKET_ITEMS", false);
if(!is_array($arBasketItems))
{
	$arBasketItems = array();
	$dbBasket = CSaleBasket::GetList(
		array("DATE_INSERT" => "ASC", "NAME" => "ASC"),
		array("ORDER_ID" => $ORDER_ID),
		false, false,
		array("ID", "PRICE", "CURRENCY", "QUANTITY", "NAME", "VAT_RATE", "MEASURE_NAME")
	);
	while ($arBasket = $dbBasket->Fetch())
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
		$arBasketItems[] = $arBasket;
	}
}

if (!empty($arBasketItems))
{
	$arCells = array();
	$arProps = array();

	$n = 0;
	$sum = 0.00;
	$vat = 0;
	foreach($arBasketItems as &$arBasket)
	{
		$productName = $arBasket["NAME"];
		if ($productName == "OrderDelivery")
			$productName = "Доставка";
		else if ($productName == "OrderDiscount")
			$productName = "Скидка";

		$arCells[++$n] = array(
			1 => $n,
			htmlspecialcharsbx($productName),
			roundEx($arBasket["QUANTITY"], SALE_VALUE_PRECISION),
			$arBasket["MEASURE_NAME"] ? htmlspecialcharsbx($arBasket["MEASURE_NAME"]) : 'шт.',
			SaleFormatCurrency($arBasket["PRICE"], $arBasket["CURRENCY"], true),
			roundEx($arBasket["VAT_RATE"]*100, SALE_VALUE_PRECISION) . "%",
			SaleFormatCurrency(
				$arBasket["PRICE"] * $arBasket["QUANTITY"],
				$arBasket["CURRENCY"],
				true
			)
		);

		if(isset($arBasket["PROPS"]))
		{
			$arProps[$n] = array();
			foreach ($arBasket["PROPS"] as $vv)
				$arProps[$n][] = htmlspecialcharsbx(sprintf("%s: %s", $vv["NAME"], $vv["VALUE"]));
		}

		$sum += doubleval($arBasket["PRICE"] * $arBasket["QUANTITY"]);
		$vat = max($vat, $arBasket["VAT_RATE"]);
	}
	unset($arBasket);

	if (DoubleVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"]) > 0)
	{
		$arDelivery_tmp = CSaleDelivery::GetByID($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DELIVERY_ID"]);

		$sDeliveryItem = "Доставка";
		if (strlen($arDelivery_tmp["NAME"]) > 0)
			$sDeliveryItem .= sprintf(" (%s)", $arDelivery_tmp["NAME"]);
		$arCells[++$n] = array(
			1 => $n,
			htmlspecialcharsbx($sDeliveryItem),
			1,
			'',
			SaleFormatCurrency(
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"],
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				true
			),
			roundEx($vat*100, SALE_VALUE_PRECISION) . "%",
			SaleFormatCurrency(
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"],
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				true
			)
		);

		$sum += doubleval($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"]);
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
			"Подытог:",
			SaleFormatCurrency($sum, $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"], true)
		);
	}

	$taxes = false;
	$dbTaxList = CSaleOrderTax::GetList(
		array("APPLY_ORDER" => "ASC"),
		array("ORDER_ID" => $ORDER_ID)
	);

	while ($arTaxList = $dbTaxList->Fetch())
	{
		$taxes = true;

		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			htmlspecialcharsbx(sprintf(
				"%s%s%s:",
				($arTaxList["IS_IN_PRICE"] == "Y") ? "В том числе " : "",
				$arTaxList["TAX_NAME"],
				($vat <= 0 && $arTaxList["IS_PERCENT"] == "Y")
					? sprintf(' (%s%%)', roundEx($arTaxList["VALUE"],SALE_VALUE_PRECISION))
					: ""
			)),
			SaleFormatCurrency(
				$arTaxList["VALUE_MONEY"],
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
				true
			)
		);
	}

	if (!$taxes)
	{
		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			htmlspecialcharsbx("НДС:"),
			htmlspecialcharsbx("Без НДС")
		);
	}

	if (DoubleVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SUM_PAID"]) > 0)
	{
		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			"Уже оплачено:",
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
			"Скидка:",
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
		"Итого:",
		SaleFormatCurrency(
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"],
			$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
			true
		)
	);
}

$arCurFormat = CCurrencyLang::GetCurrencyFormat($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]);
$currency = trim(str_replace('#', '', $arCurFormat['FORMAT_STRING']));
?>
<table class="it" width="100%">
	<tr>
		<td><nobr>№</nobr></td>
		<td><nobr>Наименование товара</nobr></td>
		<td><nobr>Кол-во</nobr></td>
		<td><nobr>Ед.</nobr></td>
		<td><nobr>Цена, <?=$currency; ?></nobr></td>
		<? if ($vat > 0) { ?>
		<td><nobr>Ставка НДС</nobr></td>
		<? } ?>
		<td><nobr>Сумма, <?=$currency; ?></nobr></td>
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

<?=sprintf(
	"Всего наименований %s, на сумму %s",
	$items,
	SaleFormatCurrency(
		$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"],
		$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
		false
	)
); ?>
<br>

<b>
<?

if (in_array($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"], array("RUR", "RUB")))
{
	echo Number2Word_Rus($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"]);
}
else
{
	echo SaleFormatCurrency(
		$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"],
		$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
		false
	);
}

?>
</b>

<br>
<br>

<? if (CSalePaySystemAction::GetParamValue("COMMENT1", false) || CSalePaySystemAction::GetParamValue("COMMENT2", false)) { ?>
<b>Условия и комментарии</b>
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

</div>

</body>
</html>