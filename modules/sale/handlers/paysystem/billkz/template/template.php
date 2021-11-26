<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title><?=Loc::getMessage('SALE_HPS_BILLKZ_TITLE')?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET?>">
<style type="text/css">
	table { border-collapse: collapse; }
	table.acc td { border: 1pt solid #000000; padding: 0 3pt; line-height: 16pt; }
	table.acc tr.ntb td { border-top-style: none; }
	table.acc tr.nbb td { border-bottom-style: none; }
	table.selbuy td { border: 0 none transparent; padding: 0 3pt; line-height: 16pt; vertical-align: top;}
	table.it td { border: 1pt solid #000000; padding: 0 3pt; }
	table.sign td { vertical-align: bottom; height: 50px;}
	table.header td { padding: 0; vertical-align: top; }
</style>
</head>

<?

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;

$pageWidth  = 595.28;
$pageHeight = 841.89;

$background = '#ffffff';
if ($params['BILLKZ_BACKGROUND'])
{
	$path = $params['BILLKZ_BACKGROUND'];
	if (intval($path) > 0)
	{
		if ($arFile = CFile::GetFileArray($path))
			$path = $arFile['SRC'];
	}

	$backgroundStyle = $params['BILLKZ_BACKGROUND_STYLE'];
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
	'top' => intval($params['BILLKZ_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILLKZ_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILLKZ_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILLKZ_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

?>

<body style="margin: 0pt; padding: 0pt; background: <?=$background; ?>"<? if ($_REQUEST['PRINT'] == 'Y') { ?> onload="setTimeout(window.print, 0);"<? } ?>>

<div style="margin: 0pt; padding: <?=join('pt ', $margin); ?>pt; width: <?=$width; ?>pt; background: <?=$background; ?>">

<?if ($params['BILLKZ_HEADER_SHOW'] == 'Y'):?>
	<? if ($params["BILLKZ_PATH_TO_LOGO"] || $params["BILLKZ_COMMENT1"] || $params["BILLKZ_COMMENT2"]) { ?>
	<table class="header" style="margin-bottom: 10pt; width: 100%">
		<tr>
			<td style="min-width: 80pt; padding-right: 5pt; padding-bottom: 5pt; ">
				<? if ($params["BILLKZ_PATH_TO_LOGO"]) { ?>
					<? $imgParams = CFile::_GetImgParams($params['BILLKZ_PATH_TO_LOGO']);
						$dpi = intval($params['BILLKZ_LOGO_DPI']) ?: 96;
						$imgWidth = $imgParams['WIDTH'] * 96 / $dpi;
						if ($imgWidth > $pageWidth)
							$imgWidth = $pageWidth * 0.6;
					?>
				<img src="<?=$imgParams['SRC']; ?>" width="<?=$imgWidth; ?>" />
				<? } ?>
			</td>
			<? if ($params["BILLKZ_COMMENT1"] || $params["BILLKZ_COMMENT2"]) { ?>
			<td style="width: 10pt;"></td>
			<td style="text-align: center; vertical-align: middle;">
				<? if ($params["BILLKZ_COMMENT1"]) { ?>
					<?=nl2br(HTMLToTxt(preg_replace(
						array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
						htmlspecialcharsback($params["BILLKZ_COMMENT1"])
					), '', array(), 0)); ?>
					<? if ($params["BILLKZ_COMMENT2"]) { ?>
					<br><br>
					<? } ?>
				<? } ?>
				<? if ($params["BILLKZ_COMMENT2"]) { ?>
					<?=nl2br(HTMLToTxt(preg_replace(
						array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
						htmlspecialcharsback($params["BILLKZ_COMMENT2"])
					), '', array(), 0)); ?>
				<? } ?>
			</td>
			<? } ?>
		</tr>
	</table>
	<? } ?>

	<?
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

	?>
	<table class="acc" width="100%">
		<colgroup>
			<col width="56%">
			<col width="19%">
			<col width="6%">
			<col width="19%">
		</colgroup>
		<tr class="nbb">
			<td><b><nobr><?= Loc::getMessage('SALE_HPS_BILLKZ_BENEFICIARY') ?>:</nobr></b></td>
			<td colspan="2" style="text-align: center;"><b><nobr><?= Loc::getMessage('SALE_HPS_BILLKZ_IIK') ?></nobr></b></td>
			<td style="text-align: center;"><b><nobr><?= Loc::getMessage('SALE_HPS_BILLKZ_KBE') ?></nobr></b></td>
		</tr>
		<tr class="ntb nbb">
			<td><b><?= htmlspecialcharsbx($params["SELLER_COMPANY_NAME"]) ?></b></td>
			<td colspan="2" style="text-align: center;"><?= $sellerRs ? htmlspecialcharsbx($sellerRs) : '&nbsp;' ?></td>
			<td style="text-align: center;"><?= $params["SELLER_COMPANY_KBE"] ? htmlspecialcharsbx($params["SELLER_COMPANY_KBE"]) : '&nbsp;' ?></td>
		</tr>
		<tr class="ntb">
			<td><?= $params["SELLER_COMPANY_BIN"] ? Loc::getMessage('SALE_HPS_BILLKZ_BIN').': '.htmlspecialcharsbx($params["SELLER_COMPANY_BIN"]) : '&nbsp;' ?></td>
			<td colspan="2">&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr class="ntb nbb">
			<td><nobr><?= Loc::getMessage('SALE_HPS_BILLKZ_BENEFICIARY_BANK') ?>:</nobr></td>
			<td style="text-align: center;"><b><nobr><?= Loc::getMessage('SALE_HPS_BILLKZ_SELLER_BANK_BIK') ?></nobr></b></td>
			<td colspan="2" style="text-align: center;"><b><nobr><?= Loc::getMessage('SALE_HPS_BILLKZ_PAYMENT_PC') ?></nobr></b></td>
		</tr>
		<tr class="ntb">
			<td><?= $sellerBank ? htmlspecialcharsbx($sellerBank) : '&nbsp;' ?></td>
			<td style="text-align: center;"><?= $params["SELLER_COMPANY_BANK_BIC"] ? htmlspecialcharsbx($params["SELLER_COMPANY_BANK_BIC"]) : '&nbsp;' ?></td>
			<td colspan="2" style="text-align: center;"><?= $params["PAYMENT_PC"] ? htmlspecialcharsbx($params["PAYMENT_PC"]) : '&nbsp;' ?></td>
		</tr>
		<? if ($params["BILLKZ_ORDER_SUBJECT"]): ?>
		<tr class="ntb nbb">
			<td colspan="4"><?= Loc::getMessage('SALE_HPS_BILLKZ_PAYMENT_PURPOSE') ?>:</td>
		</tr>
		<tr class="ntb">
			<td colspan="4"><?= htmlspecialcharsbx($params["BILLKZ_ORDER_SUBJECT"]) ?></td>
		</tr>
		<? endif; ?>
	</table>
<?endif;?>
<br>
<br>

<table width="100%">
	<colgroup>
		<col width="50%">
		<col width="0">
		<col width="50%">
	</colgroup>
<?if ($params['BILLKZ_HEADER']):?>
	<?
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
	?>
	<tr>
		<td style="font-size: 1.6em; font-weight: bold;">
			<nobr><?=htmlspecialcharsbx($params['BILLKZ_HEADER']);?> <?=Loc::getMessage('SALE_HPS_BILLKZ_SELLER_TITLE', array('#PAYMENT_NUM#' => htmlspecialcharsbx($params["ACCOUNT_NUMBER"]), '#PAYMENT_DATE#' => htmlspecialcharsbx($dateValue)));?></nobr>
		</td>
	</tr>
<?endif;?>
</table>
<table width="100%" style="margin: 16pt 0;"><tr style="border-bottom: 2pt solid #000000;"><td style="padding: 0;"></td></tr></table>
<?
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
?>
<table class="selbuy" width="100%">
	<colgroup>
		<col width="13%">
		<col width="87%">
	</colgroup>
	<tr>
		<td><?= !empty($sellerTitle) ? htmlspecialcharsbx($sellerTitle) : '&nbsp' ?></td>
		<td><?= !empty($sellerInfo) ? htmlspecialcharsbx($sellerInfo) : '&nbsp' ?></td>
	</tr>
<? if ($params['BILLKZ_PAYER_SHOW'] == 'Y'): ?>
	<tr><td colspan="2" style="padding: 0; line-height: 7pt;">&nbsp;</td></tr>
	<tr>
		<td><?= !empty($buyerTitle) ? htmlspecialcharsbx($buyerTitle) : '&nbsp' ?></td>
		<td><?= !empty($buyerInfo) ? htmlspecialcharsbx($buyerInfo) : '&nbsp' ?></td>
	</tr>
<? endif; ?>
<? if ($params['BUYER_PERSON_COMPANY_DOGOVOR']): ?>
	<tr><td colspan="2" style="padding: 0; line-height: 7pt;">&nbsp;</td></tr>
	<tr>
		<td><?= Loc::getMessage('SALE_HPS_BILLKZ_BUYER_DOGOVOR') ?>:</td>
		<td><?= htmlspecialcharsbx($params['BUYER_PERSON_COMPANY_DOGOVOR']) ?></td>
	</tr>
<? endif; ?>
</table>
<br>
<?php
$cells = array();
$props = array();

$n = 0;
$sum = 0.00;
$vat = 0;
$cntBasketItem = 0;

$columnList = array('NUMBER', 'NAME', 'QUANTITY', 'MEASURE', 'PRICE', 'VAT_RATE', 'SUM');
$arCols = array();
foreach ($columnList as $column)
{
	if ($params['BILLKZ_COLUMN_'.$column.'_SHOW'] == 'Y')
	{
		$arCols[$column] = array(
			'NAME' => htmlspecialcharsbx($params['BILLKZ_COLUMN_'.$column.'_TITLE']),
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
			'NAME' => htmlspecialcharsbx($val['NAME']),
			'SORT' => $val['SORT']
		);
	}
}

uasort($arCols, function ($a, $b) {return ($a['SORT'] < $b['SORT']) ? -1 : 1;});

$arColumnKeys = array_keys($arCols);
$columnCount = count($arColumnKeys);

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

	$cells[++$n] = array();
	foreach ($arCols as $columnId => $caption)
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
				$data = $basketItem["MEASURE_NAME"] ? htmlspecialcharsbx($basketItem["MEASURE_NAME"]) : Loc::getMessage('SALE_HPS_BILLKZ_BASKET_MEASURE_DEFAULT');
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
			default :
				$data = ($basketItem[$columnId]) ?: '';
		}
		if ($data !== null)
			$cells[$n][$columnId] = $data;
	}
	$props[$n] = array();
	if ($basketItem['PROPS'])
	{
		foreach ($basketItem['PROPS'] as $basketPropertyItem)
		{
			if ($basketPropertyItem['CODE'] == 'CATALOG.XML_ID' || $basketPropertyItem['CODE'] == 'PRODUCT.XML_ID')
				continue;
			$props[$n][] = htmlspecialcharsbx(sprintf("%s: %s", $basketPropertyItem["NAME"], $basketPropertyItem["VALUE"]));
		}
	}
	$sum += doubleval($basketItem['PRICE'] * $basketItem['QUANTITY']);
	$vat = max($vat, $basketItem['VAT_RATE']);
}

if ($vat <= 0)
{
	unset($arCols['VAT_RATE']);
	$columnCount = count($arCols);
	$arColumnKeys = array_keys($arCols);
	foreach ($cells as $i => $cell)
		unset($cells[$i]['VAT_RATE']);
}

if ($params['PRICE'] > 0)
{
	$deliveryItem = Loc::getMessage('SALE_HPS_BILLKZ_DELIVERY');

	if ($params['DELIVERY_NAME'])
		$deliveryItem .= sprintf(" (%s)", $params['DELIVERY_NAME']);
	$cells[++$n] = array();
	foreach ($arCols as $columnId => $caption)
	{
		$data = null;

		switch ($columnId)
		{
			case 'NUMBER':
				$data = $n;
				break;
			case 'NAME':
				$data = htmlspecialcharsbx($deliveryItem);
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
		}
		if ($data !== null)
			$cells[$n][$columnId] = $data;
	}
	$sum += doubleval($params['DELIVERY_PRICE']);
}

if ($params['BILLKZ_TOTAL_SHOW'] == 'Y')
{
	$cntBasketItem = $n;
	$eps = 0.0001;
	if ($params['SUM'] - $sum > $eps)
	{
		$cells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$cells[$n][$arColumnKeys[$i]] = null;

		$cells[$n][$arColumnKeys[$columnCount-2]] = htmlspecialcharsbx(Loc::getMessage('SALE_HPS_BILLKZ_SUBTOTAL'));
		$cells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($sum, $params['CURRENCY'], true);
	}

	if ($params['TAXES'])
	{
		foreach ($params['TAXES'] as $tax)
		{
			$cells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$cells[$n][$arColumnKeys[$i]] = null;

			$cells[$n][$arColumnKeys[$columnCount-2]] = htmlspecialcharsbx(sprintf(
					"%s%s%s:",
					($tax["IS_IN_PRICE"] == "Y") ? Loc::getMessage('SALE_HPS_BILLKZ_INCLUDING') : "",
					$tax["TAX_NAME"],
					($vat <= 0 && $tax["IS_PERCENT"] == "Y")
							? sprintf(' (%s%%)', roundEx($tax["VALUE"], SALE_VALUE_PRECISION))
							: ""
			));
			$cells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($tax["VALUE_MONEY"], $params['CURRENCY'], true);
		}
	}

	if (!$params['TAXES'])
	{
		$cells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$cells[$n][$i] = null;

		$cells[$n][$arColumnKeys[$columnCount-2]] = htmlspecialcharsbx(Loc::getMessage('SALE_HPS_BILLKZ_TOTAL_VAT_RATE'));
		$cells[$n][$arColumnKeys[$columnCount-1]] = htmlspecialcharsbx(Loc::getMessage('SALE_HPS_BILLKZ_TOTAL_VAT_RATE_NO'));
	}
	if ($params['SUM_PAID'] > 0)
	{
		$cells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$cells[$n][$arColumnKeys[$i]] = null;

		$cells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILLKZ_TOTAL_PAID');
		$cells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($params['SUM_PAID'], $params['CURRENCY'], true);
	}
	if ($params['DISCOUNT_PRICE'] > 0)
	{
		$cells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$cells[$n][$arColumnKeys[$i]] = null;

		$cells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILLKZ_TOTAL_DISCOUNT');
		$cells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($params['DISCOUNT_PRICE'], $params['CURRENCY'], true);
	}

	$cells[++$n] = array();
	for ($i = 0; $i < $columnCount; $i++)
		$cells[$n][$arColumnKeys[$i]] = null;

	$cells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILLKZ_TOTAL_SUM');
	$cells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($params['SUM'], $params['CURRENCY'], true);
}
?>
<table class="it" width="100%">
	<tr>
		<?foreach ($arCols as $columnId => $col):?>
			<td><?=$col['NAME'];?></td>
		<?endforeach;?>
	</tr>
<?

$rowsCnt = count($cells);
for ($n = 1; $n <= $rowsCnt; $n++):

	$accumulated = 0;
?>
	<tr valign="top">
		<?foreach ($arCols as $columnId => $col):?>
		<?
			if (!is_null($cells[$n][$columnId]))
			{
				if ($columnId === 'NUMBER')
				{?>
					<td align="center"><?=$cells[$n][$columnId];?></td>
				<?}
				elseif ($columnId === 'NAME')
				{
				?>
					<td align="<?=($n > $cntBasketItem) ? 'right' : 'left';?>"
						style="word-break: break-word; word-wrap: break-word; <? if ($accumulated) {?>border-width: 0pt 1pt 0pt 0pt; <? } ?>"
						<? if ($accumulated) { ?>colspan="<?=($accumulated+1); ?>"<? $accumulated = 0; } ?>>
						<?=$cells[$n][$columnId]; ?>
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
					if (!is_null($cells[$n][$columnId]))
					{
						if ($columnId != 'VAT_RATE' || $vat > 0 || is_null($cells[$n][$columnId]) || $n > $cntBasketItem)
						{ ?>
							<td align="right"
								<? if ($accumulated) { ?>
								style="border-width: 0pt 1pt 0pt 0pt"
								colspan="<?=(($columnId == 'VAT_RATE' && $vat <= 0) ? $accumulated : $accumulated+1); ?>"
								<? $accumulated = 0; } ?>>
								<?if ($columnId == 'SUM' || $columnId == 'PRICE'):?>
									<nobr><?=$cells[$n][$columnId];?></nobr>
								<?else:?>
									<?=$cells[$n][$columnId]; ?>
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

<?endfor;?>
</table>
<br>

<?if ($params['BILLKZ_TOTAL_SHOW'] == 'Y'):?>
	<?=Loc::getMessage(
			'SALE_HPS_BILLKZ_BASKET_TOTAL',
			array(
				'#BASKET_COUNT#' => $cntBasketItem,
				'#BASKET_PRICE#' => SaleFormatCurrency($params['SUM'], $params['CURRENCY'], false),
			)
	);?>
	<br>

	<b>
	<?

	if (in_array($params['CURRENCY'], array("RUR", "RUB", "UAH", "KZT", "BYR", "BYN")))
	{
		echo Number2Word_Rus($params['SUM'], "Y", $params['CURRENCY']);
	}
	else
	{
		echo SaleFormatCurrency(
			$params['SUM'],
			$params['CURRENCY'],
			false
		);
	}

	?>
	</b>
<?endif;?>
<table width="100%" style="margin: 16pt 0;"><tr style="border-bottom: 2pt solid #000000;"><td style="padding: 0;"></td></tr></table>
<?if ($params['BILLKZ_SIGN_SHOW'] == 'Y'):?>
	<?
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
		$executorInfo = str_pad('', 6 * 35, '&nbsp;');
	}
	?>
	<? if (!$blank) { ?>
	<div style="position: relative; "><?
		echo CFile::ShowImage($params["BILLKZ_PATH_TO_STAMP"], 160, 160, 'style="position: absolute; left: 40pt; "');
	?></div>
	<? } ?>
	<div style="position: relative">
		<table class="sign" style="width: 100%; margin-top: 50pt;">
			<colgroup>
				<col width="18%">
				<col width="50%">
				<col width="32%">
			</colgroup>
			<tr>
				<td style="white-space: nowrap;"><b><?= Loc::getMessage("SALE_HPS_BILLKZ_EXECUTOR") ?></b></td>
				<td style="border-bottom: 1pt solid #000000; text-align: center;">
					<?= $blank ? '&nbsp;' : CFile::ShowImage($params[$signParamName], 200, 50) ?>
				</td>
				<td style="white-space: nowrap; padding-left: 8pt;">
					<?= !empty($executorInfo) ? ' / '.htmlspecialcharsbx($executorInfo).' / ' : '&nbsp;' ?>
				</td>
			</tr>
		</table>
	</div>
<?endif;?>

</div>

</body>
</html>