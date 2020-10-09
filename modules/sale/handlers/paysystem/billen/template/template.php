<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
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
if ($params['BILLEN_BACKGROUND'])
{
	$path = $params['BILLEN_BACKGROUND'];
	if (intval($path) > 0)
	{
		if ($arFile = CFile::GetFileArray($path))
			$path = $arFile['SRC'];
	}

	$backgroundStyle = $params['BILLEN_BACKGROUND_STYLE'];
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
	'top' => intval($params['BILLEN_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILLEN_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILLEN_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILLEN_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

?>

<body style="margin: 0pt; padding: 0pt;"<? if ($_REQUEST['PRINT'] == 'Y') { ?> onload="setTimeout(window.print, 0);"<? } ?>>

<div style="margin: 0pt; padding: <?=join('pt ', $margin); ?>pt; width: <?=$width; ?>pt; background: <?=$background; ?>">

<table class="header">
	<tr>
		<? if ($params["BILLEN_PATH_TO_LOGO"]) { ?>
		<td style="padding-right: 5pt; ">
			<? $imgParams = CFile::_GetImgParams($params['BILLEN_PATH_TO_LOGO']);
				$dpi = intval($params['BILLEN_LOGO_DPI']) ?: 96;
				$imgWidth = $imgParams['WIDTH'] * 96 / $dpi;
				if ($imgWidth > $pageWidth)
					$imgWidth = $pageWidth * 0.6;
			?>
			<img src="<?=$imgParams['SRC']; ?>" width="<?=$imgWidth; ?>" />
		</td>
		<? } ?>
		<td>
			<b><?=htmlspecialcharsbx($params["SELLER_COMPANY_NAME"]); ?></b><br><?
			if ($params["SELLER_COMPANY_ADDRESS"]) {
				$sellerAddress = $params["SELLER_COMPANY_ADDRESS"];
				if (is_array($sellerAddress))
				{
					if (!empty($sellerAddress))
					{
						foreach ($sellerAddress as $i => $address)
						{
							$sellerAddress[$i] = htmlspecialcharsbx($address);
						}

						$addrValue = implode('<br>', $sellerAddress)
						?><div style="display: inline-block; vertical-align: top;"><b><?= $addrValue ?></b></div><?
						unset($addrValue);
					}
				}
				else
				{
					?><b><?= nl2br(htmlspecialcharsbx($sellerAddress)) ?></b><?
				}
				unset($sellerAddress);
				?><br><?
			} ?>
			<? if ($params["SELLER_COMPANY_PHONE"]) { ?>
			<b><?=sprintf("Tel.: %s", htmlspecialcharsbx($params["SELLER_COMPANY_PHONE"])); ?></b><br>
			<? } ?>
		</td>
	</tr>
</table>
<br>

<div style="text-align: center; font-size: 2em"><b><?=htmlspecialcharsbx($params['BILLEN_HEADER']);?></b></div>

<br>
<br>


<?if ($params['BILLEN_PAYER_SHOW'] == 'Y'):?>
	<table width="100%">
		<tr>
			<? if ($params["BUYER_PERSON_COMPANY_NAME"]) { ?>
			<td>
				<b>To</b><br>
				<?=htmlspecialcharsbx($params["BUYER_PERSON_COMPANY_NAME"]); ?><br><?
				if ($params["BUYER_PERSON_COMPANY_ADDRESS"]) {
					$buyerAddress = $params["BUYER_PERSON_COMPANY_ADDRESS"];
					if (is_array($buyerAddress))
					{
						if (!empty($buyerAddress))
						{
							foreach ($buyerAddress as $i => $address)
							{
								$buyerAddress[$i] = htmlspecialcharsbx($address);
							}

							$addrValue = implode('<br>', $buyerAddress)
							?><div style="display: inline-block; vertical-align: top;"><?= $addrValue ?><br></div><?
							unset($addrValue);
						}
					}
					else
					{
						?><?= nl2br(htmlspecialcharsbx($buyerAddress)) ?><br><?
					}
					unset($buyerAddress);
				}
				if ($params['BUYER_PERSON_COMPANY_PHONE'])
					echo "Tel.: ".htmlspecialcharsbx($params['BUYER_PERSON_COMPANY_PHONE']).'<br>';
				if ($params['BUYER_PERSON_COMPANY_FAX'])
					echo "Fax: ".htmlspecialcharsbx($params['BUYER_PERSON_COMPANY_FAX']).'<br>';
				if ($params['BUYER_PERSON_COMPANY_NAME_CONTACT'])
					echo htmlspecialcharsbx($params['BUYER_PERSON_COMPANY_NAME_CONTACT']).'<br>';
				?>
			</td>
			<? } ?>
		<td align="right" valign="top">
			<table class="inv">
					<tr align="left">
						<td><b><?=htmlspecialcharsbx($params['BILLEN_HEADER']);?> #&nbsp;</b></td>
						<td><?=htmlspecialcharsbx($params["ACCOUNT_NUMBER"]); ?></td>
					</tr>
					<tr align="left">
						<td><b>Issue Date&nbsp;</b></td>
						<td><?=htmlspecialcharsbx($params["DATE_INSERT"]); ?></td>
					</tr>
					<? if ($params["DATE_PAY_BEFORE"]) { ?>
					<tr align="left">
						<td><b>Due Date&nbsp;</b></td>
						<td><?=(
							ConvertDateTime($params["DATE_PAY_BEFORE"], FORMAT_DATE)
								?: htmlspecialcharsbx($params["DATE_PAY_BEFORE"])
						); ?></td>
					</tr>
					<? } ?>
				</table>
			</td>
		</tr>
	</table>
<?endif;?>
<br>
<br>
<br>

<?
$columnList = array('NUMBER', 'NAME', 'QUANTITY', 'MEASURE', 'PRICE', 'VAT_RATE', 'SUM');
$arCols = array();
foreach ($columnList as $column)
{
	if ($params['BILLEN_COLUMN_'.$column.'_SHOW'] == 'Y')
	{
		$arCols[$column] = array(
			'NAME' => htmlspecialcharsbx($params['BILLEN_COLUMN_'.$column.'_TITLE']),
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
			'NAME' => htmlspecialcharsbx($val['NAME']),
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
					$data = $n;
					break;
				case 'NAME':
					$data = htmlspecialcharsbx($productName);
					break;
				case 'QUANTITY':
					$data = roundEx($basketItem['QUANTITY'], SALE_VALUE_PRECISION);
					break;
				case 'MEASURE':
					$data = $basketItem["MEASURE_NAME"] ? htmlspecialcharsbx($basketItem["MEASURE_NAME"]) : 'pcs';
					break;
				case 'PRICE':
					$data = SaleFormatCurrency($vatLessPrice, $basketItem['CURRENCY'], false);
					break;
				case 'VAT_RATE':
					$data = roundEx($basketItem['VAT_RATE']*100, SALE_VALUE_PRECISION) . "%";
					break;
				case 'SUM':
					$data = SaleFormatCurrency($vatLessPrice * $basketItem['QUANTITY'], $basketItem['CURRENCY'], false);
					break;
				default :
					$data = ($basketItem[$columnId]) ?: '';
			}
			if ($data !== null)
				$arCells[$n][$columnId] = $data;
		}

		if ($basketItem['PROPS'])
		{
			$arProps[$n] = array();

			foreach ($basketItem['PROPS'] as $basketPropertyItem)
			{
				if ($basketPropertyItem['CODE'] == 'CATALOG.XML_ID' || $basketPropertyItem['CODE'] == 'PRODUCT.XML_ID')
					continue;
				$arProps[$n][] = htmlspecialcharsbx(sprintf("%s: %s", $basketPropertyItem["NAME"], $basketPropertyItem["VALUE"]));
			}
		}

		$sum += doubleval($vatLessPrice * $basketItem['QUANTITY']);
		$vat = max($vat, $basketItem['VAT_RATE']);
		if ($basketItem['VAT_RATE'] > 0)
		{
			$vatRate = (string)$basketItem['VAT_RATE'];
			if (!isset($vats[$vatRate]))
				$vats[$vatRate] = 0;

			if ($basketItem['IS_VAT_IN_PRICE'])
				$vats[$vatRate] += ($basketItem['PRICE'] - $vatLessPrice) * $basketItem['QUANTITY'];
			else
				$vats[$vatRate] += ($basketItem['PRICE']*(1 + $basketItem['VAT_RATE']) - $vatLessPrice) * $basketItem['QUANTITY'];
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
					$data = $n;
					break;
				case 'NAME':
					$data = htmlspecialcharsbx($sDeliveryItem);
					break;
				case 'QUANTITY':
					$data = 1;
					break;
				case 'MEASURE':
					$data = '';
					break;
				case 'PRICE':
					$data = SaleFormatCurrency($params['DELIVERY_PRICE'] / (1 + $vat), $params['CURRENCY'], false);
					break;
				case 'VAT_RATE':
					$data = roundEx($vat*100, SALE_VALUE_PRECISION) . "%";
					break;
				case 'SUM':
					$data = SaleFormatCurrency($params['DELIVERY_PRICE'] / (1 + $vat), $params['CURRENCY'], false);
					break;
				default :
					$data = '';
			}
			if ($data !== null)
				$arCells[$n][$columnId] = $data;
		}

		$sum += roundEx(
			$params['DELIVERY_PRICE'] / (1 + $vat),
			SALE_VALUE_PRECISION
		);

		if ($vat > 0)
			$vats[(string)$vat] += roundEx(
				$params['DELIVERY_PRICE'] / (1 + $vat),
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

			$arCells[$n][$arColumnKeys[$columnCount-2]] = "Subtotal:";
			$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($sum, $params['CURRENCY'], false);
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
					$arCells[$n][$i] = null;

				$arCells[$n][$arColumnKeys[$columnCount-2]] = sprintf("Tax (%s%%):", roundEx($vatRate * 100, SALE_VALUE_PRECISION));
				$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($vatSum, $params['CURRENCY'], false);
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

					$arCells[$n][$arColumnKeys[$columnCount-2]] = htmlspecialcharsbx(sprintf(
							"%s%s%s:",
							($tax["IS_IN_PRICE"] == "Y") ? "Included " : "",
							$tax["TAX_NAME"],
							sprintf(' (%s%%)', roundEx($tax["VALUE"], SALE_VALUE_PRECISION))
					));
					$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($tax["VALUE_MONEY"], $params['CURRENCY'], false);
				}
			}
			else
			{
				$arCells[++$n] = array();
				for ($i = 0; $i < $columnCount; $i++)
					$arCells[$n][$arColumnKeys[$i]] = null;

				$arCells[$n][$arColumnKeys[$columnCount-2]] = "Tax (0%):";
				$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency(0, $params['CURRENCY'], false);
			}
		}

		if ($params['SUM_PAID'])
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = "Payment made:";
			$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($params['SUM_PAID'], $params['CURRENCY'], false);
		}

		if ($params['DISCOUNT_PRICE'])
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = "Discount:";
			$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($params['DISCOUNT_PRICE'], $params['CURRENCY'], false);
		}

		$arCells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$arCells[$n][$arColumnKeys[$i]] = null;

		$arCells[$n][$arColumnKeys[$columnCount-2]] = "Total:";
		$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($params['SUM'], $params['CURRENCY'], false);
	}
}

?>
<table class="it" width="100%">
	<tr align="center">
		<?foreach ($arCols as $columnId => $col):?>
			<td><?=$col['NAME'];?></td>
		<?endforeach;?>
	</tr>
<?

$rowsCnt = count($arCells);
for ($n = 1; $n <= $rowsCnt; $n++)
{
	$accumulated = 0;

?>
	<tr valign="top">
		<?foreach ($arCols as $columnId => $cols):?>
			<?
				if (!is_null($arCells[$n][$columnId]))
				{
					if ($columnId === 'NUMBER')
					{?>
						<td align="center"><?=$arCells[$n][$columnId];?></td>
					<?}
					elseif ($columnId === 'NAME')
					{
					?>
						<td align="<?=($n > $items) ? 'right' : 'left';?>"
							style="word-break: break-word; word-wrap: break-word; <? if ($accumulated) {?>border-width: 0pt 1pt 0pt 0pt; <? } ?>"
							<? if ($accumulated) { ?>colspan="<?=($accumulated+1); ?>"<? $accumulated = 0; } ?>>
							<?=$arCells[$n][$columnId]; ?>
							<? if (isset($props[$n]) && is_array($props[$n])) { ?>
							<? foreach ($props[$n] as $property) { ?>
							<br>
							<small><?=$property; ?></small>
							<? } ?>
							<? } ?>
						</td>
					<?}
					else
					{
						if (!is_null($arCells[$n][$columnId]))
						{
							if ($columnId != 'VAT_RATE' || $vat > 0 || is_null($arCells[$n][$columnId]) || $n > $items)
							{ ?>
								<td align="right"
									<? if ($accumulated) { ?>
									style="border-width: 0pt 1pt 0pt 0pt"
									colspan="<?=(($columnId == 'VAT_RATE' && $vat <= 0) ? $accumulated : $accumulated+1); ?>"
									<? $accumulated = 0; } ?>>
									<?if ($columnId == 'SUM' || $columnId == 'PRICE'):?>
										<nobr><?=$arCells[$n][$columnId];?></nobr>
									<?else:?>
										<?=$arCells[$n][$columnId]; ?>
									<?endif;?>
								</td>
							<? }
						}
						else
						{
							$accumulated++;
						}
					}
				}
				else
				{
					$accumulated++;
				}
			?>
		<?endforeach;?>
	</tr>
<?

}

?>
</table>
<br>
<br>
<br>
<br>

<? if ($params["BILLEN_COMMENT1"] || $params["BILLEN_COMMENT2"]) { ?>
<b>Terms & Conditions</b>
<br>
	<? if ($params["BILLEN_COMMENT1"]) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback($params["BILLEN_COMMENT1"])
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
	<? if ($params["BILLEN_COMMENT2"]) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback($params["BILLEN_COMMENT2"])
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
<? } ?>

<br>
<br>
<br>

<? $bankAccNo = $params["SELLER_COMPANY_BANK_ACCOUNT"]; ?>
<? $bankRouteNo = $params["SELLER_COMPANY_BANK_ACCOUNT_CORR"]; ?>
<? $bankSwift = $params["SELLER_COMPANY_BANK_SWIFT"]; ?>

<table class="sign" style="width: 100%; ">
	<tr>
		<td style="width: 50%; ">

		<? if ($bankAccNo && $bankRouteNo && $bankSwift) { ?>

			<b>Bank Details</b>
			<br>

			<? if ($params["SELLER_COMPANY_NAME"]) { ?>
				Account Name: <?=htmlspecialcharsbx($params["SELLER_COMPANY_NAME"]); ?>
				<br>
			<? } ?>

			Account #: <?=htmlspecialcharsbx($bankAccNo); ?>
			<br>

			<? $bank = $params["SELLER_COMPANY_BANK_NAME"]; ?>
			<? $bankAddr = $params["SELLER_COMPANY_BANK_ADDR"]; ?>
			<? $bankPhone = $params["SELLER_COMPANY_BANK_PHONE"]; ?>

			<? if ($bank || $bankAddr || $bankPhone) { ?>
				Bank Name and Address: <? if ($bank) { ?><?=htmlspecialcharsbx($bank); ?><? } ?>
				<br>

				<? if ($bankAddr) { ?>
					<?= nl2br(htmlspecialcharsbx($bankAddr)) ?>
					<br>
				<? } ?>

				<? if ($bankPhone) { ?>
					<?=htmlspecialcharsbx($bankPhone); ?>
					<br>
				<? } ?>
			<? } ?>

			Bank's routing number: <?=htmlspecialcharsbx($bankRouteNo); ?>
			<br>

			Bank SWIFT: <?=htmlspecialcharsbx($bankSwift); ?>
			<br>
		<? } ?>

		</td>
		<td style="width: 50%; ">

			<? if (!$blank) { ?>
			<div style="position: relative; "><?=CFile::ShowImage(
						$params["BILLEN_PATH_TO_STAMP"],
				160, 160,
				'style="position: absolute; left: 30pt; "'
			); ?></div>
			<? } ?>

			<table style="width: 100%; position: relative; ">
				<colgroup>
					<col width="0">
					<col width="100%">
				</colgroup>
				<? if ($params["SELLER_COMPANY_DIRECTOR_POSITION"]) { ?>
				<? if ($params["SELLER_COMPANY_DIRECTOR_NAME"] || $params["SELLER_COMPANY_DIR_SIGN"]) { ?>
				<? if ($params["SELLER_COMPANY_DIRECTOR_NAME"]) { ?>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td colspan="2"><?=htmlspecialcharsbx($params["SELLER_COMPANY_DIRECTOR_NAME"]); ?></td>
				</tr>
				<? } ?>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><nobr><?=htmlspecialcharsbx($params["SELLER_COMPANY_DIRECTOR_POSITION"]); ?></nobr></td>
					<td style="border-bottom: 1pt solid #000000; text-align: center; ">
						<? if (!$blank && $params["SELLER_COMPANY_DIR_SIGN"]) { ?>
						<span style="position: relative; ">&nbsp;<?=CFile::ShowImage(
							$params["SELLER_COMPANY_DIR_SIGN"],
							200, 50,
							'style="position: absolute; margin-left: -75pt; bottom: 0pt; "'
						); ?></span>
						<? } ?>
					</td>
				</tr>
				<? } ?>
				<? } ?>
				<? if ($params["SELLER_COMPANY_ACCOUNTANT_POSITION"]) { ?>
				<? if ($params["SELLER_COMPANY_ACCOUNTANT_NAME"] || $params["SELLER_COMPANY_ACC_SIGN"]) { ?>
				<? if ($params["SELLER_COMPANY_ACCOUNTANT_NAME"]) { ?>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td colspan="2"><?=htmlspecialcharsbx($params["SELLER_COMPANY_ACCOUNTANT_NAME"]); ?></td>
				</tr>
				<? } ?>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><nobr><?=htmlspecialcharsbx($params["SELLER_COMPANY_ACCOUNTANT_POSITION"]); ?></nobr></td>
					<td style="border-bottom: 1pt solid #000000; text-align: center; ">
						<? if (!$blank && $params["SELLER_COMPANY_ACC_SIGN"]) { ?>
						<span style="position: relative; ">&nbsp;<?=CFile::ShowImage(
							$params["SELLER_COMPANY_ACC_SIGN"],
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