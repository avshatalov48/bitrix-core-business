<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title><?=Loc::getMessage('SALE_HPS_BILLUA')?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET?>">
<style>
	table { border-collapse: collapse; }
	table.acc td { padding: 0pt; vertical-align: top; }
	table.it td { border: 1pt solid #000000; padding: 0pt 3pt; }
	table.sign td { font-weight: bold; vertical-align: bottom; }
</style>
</head>

<?

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;

$pageWidth  = 595.28;
$pageHeight = 841.89;

$background = '#ffffff';
if ($params['BILLUA_BACKGROUND'])
{
	$path = $params['BILLUA_BACKGROUND'];
	if (intval($path) > 0)
	{
		if ($arFile = CFile::GetFileArray($path))
			$path = $arFile['SRC'];
	}

	$backgroundStyle = $params['BILLUA_BACKGROUND_STYLE'];
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
	'top' => intval($params['BILLUA_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILLUA_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILLUA_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILLUA_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

?>

<body style="margin: 0pt; padding: 0pt;"<? if ($_REQUEST['PRINT'] == 'Y') { ?> onload="setTimeout(window.print, 0);"<? } ?>>

<div style="margin: 0pt; padding: <?=join('pt ', $margin); ?>pt; width: <?=$width; ?>pt; background: <?=$background; ?>">
<?if ($params['BILLUA_HEADER']):?>
	<b><?=$params['BILLUA_HEADER'].Loc::getMessage('SALE_HPS_BILLUA_TITLE', array('#PAYMENT_NUMBER#' => htmlspecialcharsbx($params["ACCOUNT_NUMBER"]), '#PAYMENT_DATE#' => $params["DATE_INSERT"])); ?></b>
	<br>
	<br>
<?endif;?>
<?

$buyerPhone = $params["BUYER_PERSON_COMPANY_PHONE"];
$buyerFax = $params["BUYER_PERSON_COMPANY_FAX"];

?>

<table class="acc">
	<? if ($params['BILLUA_SELLER_SHOW'] == 'Y'):?>
		<tr>
			<td><?=Loc::getMessage('SALE_HPS_BILLUA_SELLER')?>:</td>
			<td style="padding-left: 4pt; ">
				<?=$params["SELLER_COMPANY_NAME"]; ?>
				<br>
				<?=Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_RS')?> <?=$params["SELLER_COMPANY_BANK_ACCOUNT"]; ?>,
				<?=Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_BANK')?> <?=$params["SELLER_COMPANY_BANK_NAME"]; ?>,
				<?=Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_MFO')?> <?=$params["SELLER_COMPANY_MFO"]; ?>
				<br><?
				$sellerAddr = '';
				if ($params["SELLER_COMPANY_ADDRESS"])
				{
					$sellerAddr = $params["SELLER_COMPANY_ADDRESS"];
					if (is_array($sellerAddr))
						$sellerAddr = implode(', ', $sellerAddr);
					else
						$sellerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerAddr));
				}
				?>
				<?=Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_ADDRESS')?>: <?= $sellerAddr ?>,
				<?if($params["SELLER_COMPANY_PHONE"]):?>
					<?=Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_PHONE')?>: <?=$params["SELLER_COMPANY_PHONE"]; ?>
				<?endif;?>
				<br>
				<?
				$requisiteList = array();
				foreach (array('EDRPOY', 'IPN', 'PDV') as $value)
				{
					if ($params["SELLER_COMPANY_".$value])
						$requisiteList[] = Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_'.$value).' '.$params["SELLER_COMPANY_".$value];
				}
				echo join(', ', $requisiteList);

				if ($params["SELLER_COMPANY_SYS"]) { ?>
				<br>
				<?=$params["SELLER_COMPANY_SYS"]; ?>
				<? } ?>
			</td>
		</tr>
	<?endif;?>
	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
	<?if ($params['BILLUA_PAYER_SHOW'] === 'Y') :?>
		<tr>
			<td><?=Loc::getMessage('SALE_HPS_BILLUA_BUYER');?>:</td>
			<td style="padding-left: 4pt; ">
				<?=$params["BUYER_PERSON_COMPANY_NAME"]; ?>
				<? if ($buyerPhone || $buyerFax) { ?>
				<br>
				<? if ($buyerPhone) { ?><?=Loc::getMessage('SALE_HPS_BILLUA_BUYER_PHONE')?>: <?=$buyerPhone; ?><? if ($buyerFax) { ?>, <? } ?><? } ?>
				<? if ($buyerFax) { ?><?=Loc::getMessage('SALE_HPS_BILLUA_BUYER_FAX')?>: <?=$buyerFax; ?><? } ?>
				<? } ?><?
				if ($params["BUYER_PERSON_COMPANY_ADDRESS"])
				{
					$buyerAddr = $params["BUYER_PERSON_COMPANY_ADDRESS"];
					if (is_array($buyerAddr))
						$buyerAddr = implode(', ', $buyerAddr);
					else
						$buyerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($buyerAddr));
					?><br><?
					?><?=Loc::getMessage('SALE_HPS_BILLUA_BUYER_ADDRESS')?>: <?= $buyerAddr ?><?
				}
				?>
			</td>
		</tr>
	<?endif;?>
</table>
<br>

<? if ($params["BUYER_PERSON_COMPANY_DOGOVOR"]) { ?>
<?=Loc::getMessage('SALE_HPS_BILLUA_BUYER_DOGOVOR')?>: <?=$params["BUYER_PERSON_COMPANY_DOGOVOR"]; ?>
<br>
<? } ?>
<br>

<?
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
			'NAME' => htmlspecialcharsbx($caption),
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
					$data = $n;
					break;
				case 'NAME':
					$data = htmlspecialcharsbx($productName);
					break;
				case 'QUANTITY':
					$data = roundEx($basketItem['QUANTITY'], SALE_VALUE_PRECISION);
					break;
				case 'MEASURE':
					$data = $basketItem["MEASURE_NAME"] ? htmlspecialcharsbx($basketItem["MEASURE_NAME"]) : Loc::getMessage('SALE_HPS_BILLUA_MEASHURE');
					break;
				case 'PRICE':
					$data = SaleFormatCurrency($basketItem['PRICE'], $basketItem['CURRENCY'], true);
					break;
				case 'VAT_RATE':
					$data = roundEx($basketItem['VAT_RATE'] * 100, SALE_VALUE_PRECISION)."%";
					break;
				case 'SUM':
					$data = SaleFormatCurrency($basketItemPrice * $basketItem['QUANTITY'], $basketItem['CURRENCY'], true);
					break;
				default:
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

		$sum += doubleval($basketItem['PRICE'] * $basketItem['QUANTITY']);
		$vat = max($vat, $basketItem['VAT_RATE']);
	}

	if ($vat <= 0 && array_key_exists('VAT_RATE', $arCols))
	{
		unset($arCols['VAT_RATE']);
		$columnCount = count($arCols);
		$arColumnKeys = array_keys($arCols);
		foreach ($arCells as $i => $cell)
			unset($arCells[$i]['VAT_RATE']);
	}

	if ($vat > 0 && array_key_exists('PRICE', $arCols) && $isVatInPrice)
		$arCols['PRICE']['NAME'] = htmlspecialcharsbx($params['BILLUA_COLUMN_PRICE_TAX_TITLE'].', '.$currency);

	if ($vat > 0 && array_key_exists('SUM', $arCols))
		$arCols['SUM']['NAME'] = htmlspecialcharsbx($params['BILLUA_COLUMN_SUM_TAX_TITLE'].', '.$currency);

	if ($params['DELIVERY_PRICE'] > 0)
	{
		$sDeliveryItem = Loc::getMessage('SALE_HPS_BILLUA_DELIVERY');
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
					$data = SaleFormatCurrency($params['DELIVERY_PRICE'], $params['CURRENCY'], true);
					break;
				case 'VAT_RATE':
					$data = roundEx($vat * 100, SALE_VALUE_PRECISION)."%";
					break;
				case 'SUM':
					$data = SaleFormatCurrency($params['DELIVERY_PRICE'], $params['CURRENCY'], true);
					break;
				default:
					$data = '';
			}
			if ($data !== null)
				$arCells[$n][$columnId] = $data;
		}

		$sum += doubleval($params['PRICE']);
	}

	$items = $n;
	if ($params['BILLUA_TOTAL_SHOW'] == 'Y')
	{
		$orderTax = 0;

		if ($params['TAXES'])
		{
			foreach ($params['TAXES'] as $tax)
			{
				$arCells[++$n] = array();
				for ($i = 0; $i < $columnCount; $i++)
					$arCells[$n][$arColumnKeys[$i]] = null;

				$arCells[$n][$arColumnKeys[$columnCount-2]] = htmlspecialcharsbx(sprintf(
					"%s%s%s:",
					($tax["IS_IN_PRICE"] == "Y") ? Loc::getMessage('SALE_HPS_BILLUA_IN_PRICE') : "",
					($vat <= 0) ? $tax["TAX_NAME"] : Loc::getMessage('SALE_HPS_BILLUA_TAX'),
					($vat <= 0 && $tax["IS_PERCENT"] == "Y")
						? sprintf(' (%s%%)', roundEx($tax["VALUE"], SALE_VALUE_PRECISION))
						: ""
				));
				$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($tax["VALUE_MONEY"], $params['CURRENCY'], true);

				$orderTax += $tax["VALUE_MONEY"];
			}
		}

		if ($params['SUM_PAID'])
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILLUA_PAYMENT_PAID').':';
			$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($params['SUM_PAID'], $params['CURRENCY'], true);
		}

		if ($params['DISCOUNT_PRICE'])
		{
			$arCells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$arCells[$n][$arColumnKeys[$i]] = null;

			$arCells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILLUA_DISCOUNT');
			$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($params['DISCOUNT_PRICE'], $params['CURRENCY'], true);
		}


		$arCells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$arCells[$n][$arColumnKeys[$i]] = null;

		$arCells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILLUA_SUM').':';
		$arCells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($params['SUM'], $params['CURRENCY'], true);

		$showVat = false;
	}
}
?>
<table class="it" width="100%">
	<tr>
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
		<?foreach ($arCols as $columnId => $col):
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
		endforeach;?>
	</tr>
<?}?>
</table>
<br>
<?if ($params['BILLUA_TOTAL_SHOW'] == 'Y'): ?>
	<b><?=sprintf(
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
	); ?></b>
	<br>

	<? if ($vat > 0) { ?>
	<b><?=sprintf(
		Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEMS_TAX'),
		($params['CURRENCY'] == "UAH")
			? Number2Word_Rus($orderTax, "Y", $params['CURRENCY'])
			: SaleFormatCurrency($orderTax, $params['CURRENCY'], false)
	); ?></b>
	<? } elseif($orderTax == 0) { ?>
	<b><?=Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEMS_WITHOUT_TAX');?></b>
	<? } ?>
	<br>
	<br>
<?endif;?>
<? if ($params["BILLUA_COMMENT1"] || $params["BILLUA_COMMENT2"]) { ?>
<b><?=Loc::getMessage('SALE_HPS_BILLUA_COMMENT')?></b>
<br>
	<? if ($params["BILLUA_COMMENT1"]) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback($params["BILLUA_COMMENT1"])
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
	<? if ($params["BILLUA_COMMENT2"]) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback($params["BILLUA_COMMENT2"])
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
<? } ?>

<?if ($params['BILLUA_FOOTER_SHOW'] == 'Y'):?>
	<div style="border-bottom: 1pt solid #000000; width:100%; ">&nbsp;</div>

	<? if (!$blank) { ?>
	<div style="position: relative; "><?=CFile::ShowImage(
		$params["BILLUA_PATH_TO_STAMP"],
		160, 160,
		'style="position: absolute; left: 40pt; "'
	); ?></div>
	<? } ?>

	<br>
	<div style="position: relative">
		<table class="sign">
			<tr>
				<td><?=Loc::getMessage('SALE_HPS_BILLUA_WRITER')?>:&nbsp;</td>
				<td style="width: 160pt; border: 1pt solid #000000; border-width: 0pt 0pt 1pt 0pt; text-align: center; ">
					<? if (!$blank) { ?>
					<?=CFile::ShowImage($params["SELLER_COMPANY_ACC_SIGN"], 200, 50); ?>
					<? } ?>
				</td>
				<td style="width: 160pt; ">
					<input
						style="border: none; background: none; width: 100%; "
						type="text"
						value="<?=$params["SELLER_COMPANY_ACCOUNTANT_NAME"]; ?>"
					>
				</td>
				<td style="width: 20pt; ">&nbsp;</td>
				<?if ($params["SELLER_COMPANY_ACCOUNTANT_POSITION"]):?>
					<td><?=Loc::getMessage('SALE_HPS_BILLUA_ACC_POSITION')?>:&nbsp;</td>
					<td style="width: 160pt; border: 1pt solid #000000; border-width: 0pt 0pt 1pt 0pt; ">
						<input
							style="border: none; background: none; width: 100%; text-align: center; "
							type="text"
							value="<?=$params["SELLER_COMPANY_ACCOUNTANT_POSITION"]; ?>"
						>
					</td>
				<?endif;?>
			</tr>
		</table>
	</div>
<?endif;?>

<br>
<br>

<? if ($params["DATE_PAY_BEFORE"]) { ?>
<div style="text-align: right; "><b><?=sprintf(
	Loc::getMessage('SALE_HPS_BILLUA_DATE_PAID_BEFORE'),
	ConvertDateTime($params["DATE_PAY_BEFORE"], FORMAT_DATE)
		?: $params["DATE_PAY_BEFORE"]
); ?></b></div>
<? } ?>

</div>

</body>
</html>