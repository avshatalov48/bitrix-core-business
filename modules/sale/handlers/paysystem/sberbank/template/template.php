<?
	use Bitrix\Main\Localization\Loc;
	use Bitrix\Sale\Payment;
	use Bitrix\Sale\PriceMaths;

Loc::loadMessages(__FILE__);
	if (array_key_exists('PAYMENT_SHOULD_PAY', $params))
		$params['PAYMENT_SHOULD_PAY'] = PriceMaths::roundPrecision($params['PAYMENT_SHOULD_PAY']);
?>
<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_RECEIPT')?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET?>">
<style type="text/css">
H1 {font-size: 12pt;}
p, ul, ol, h1 {margin-top:6px; margin-bottom:6px}
td {font-size: 9pt;}
small {font-size: 7pt;}
body {font-size: 10pt;}
</style>
</head>
<body bgColor="#ffffff">

<table border="0" cellspacing="0" cellpadding="0" style="width:180mm; height:145mm;">
<tr valign="top">
	<td style="width:50mm; height:70mm; border:1pt solid #000000; border-bottom:none; border-right:none;" align="center">
	<b><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_TITLE')?></b><br>
	<font style="font-size:53mm">&nbsp;<br></font>
	<b><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_CASHIER')?></b>
	</td>
	<td style="border:1pt solid #000000; border-bottom:none;" align="center">
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td align="right"><small><i><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_FORM_TITLE')?></i></small></td>
			</tr>
			<tr>
				<td style="border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params["SELLER_COMPANY_NAME"])?></td>
			</tr>
			<tr>
				<td align="center"><small><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_COMPANY_NAME')?></small></td>
			</tr>
		</table>

		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td style="width:37mm; border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params["SELLER_COMPANY_INN"])."/".htmlspecialcharsbx($params['SELLER_COMPANY_KPP'])?></td>
				<td style="width:9mm;">&nbsp;</td>
				<td style="border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params["SELLER_COMPANY_BANK_ACCOUNT"])?></td>
			</tr>
			<tr>
				<td align="center"><small><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_INN')?></small></td>
				<td><small>&nbsp;</small></td>
				<td align="center"><small><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SETTLEMENT_ACC')?></small></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_IN')?>&nbsp;</td>
				<td style="width:73mm; border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params["SELLER_COMPANY_BANK_NAME"])?></td>
				<td align="right"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_BANK_BIC')?>&nbsp;&nbsp;</td>
				<td style="width:33mm; border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params["SELLER_COMPANY_BANK_BIC"])?></td>
			</tr>
			<tr>
				<td></td>
				<td align="center"><small><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_BANK_NAME')?></small></td>
				<td></td>
				<td></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td width="1%" nowrap><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_BANK_COR_ACC')?>&nbsp;&nbsp;</td>
				<td width="100%" style="border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params["SELLER_COMPANY_BANK_ACCOUNT_CORR"])?></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td style="width:60mm; border-bottom:1pt solid #000000;"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_ORDER_ID', array('#PAYMENT_ID#' => htmlspecialcharsbx($params["PAYMENT_ID"]), '#ORDER_ID#' => htmlspecialcharsbx($params["PAYMENT_ORDER_ID"])))?>
	<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_ORDER_FROM')?>
	<?=htmlspecialcharsbx($params["PAYMENT_DATE_INSERT"])?></td>
				<td style="width:2mm;">&nbsp;</td>
				<td style="border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params['BUYER_PERSON_BANK_ACCOUNT']);?></td>
			</tr>
			<tr>
				<td align="center"><small><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_PAYMENT_NAME')?></small></td>
				<td><small>&nbsp;</small></td>
				<td align="center"><small><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_PAYMENT_ACC')?></small></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td width="1%" nowrap><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_PAYER_FIO')?>&nbsp;&nbsp;</td>
				<td width="100%" style="border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params["BUYER_PERSON_FIO"])?></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td width="1%" nowrap><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_PAYER_ADDRESS')?>&nbsp;&nbsp;</td>
				<td width="100%" style="border-bottom:1pt solid #000000;"><?
				
					$sAddrFact = array();
					if($params["BUYER_PERSON_ZIP"] != '')
						$sAddrFact[] = htmlspecialcharsbx($params["BUYER_PERSON_ZIP"]);

					if($params["BUYER_PERSON_COUNTRY"] != '')
						$sAddrFact[] = htmlspecialcharsbx($params["BUYER_PERSON_COUNTRY"]);

					if($params["BUYER_PERSON_REGION"] != '')
						$sAddrFact[] = htmlspecialcharsbx($params["BUYER_PERSON_REGION"]);

					if($params["BUYER_PERSON_CITY"] != '')
					{
						$g = mb_substr($params["BUYER_PERSON_CITY"], 0, 2);
						$sAddrFact[] = '<nobr>'.($g<>Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_YEAR') && $g<>ToUpper(Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_YEAR'))? Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_YEAR')." ":"").htmlspecialcharsbx($params["BUYER_PERSON_CITY"]).'</nobr>';
					}

					if($params["BUYER_PERSON_VILLAGE"] != '')
						$sAddrFact[] = htmlspecialcharsbx($params["BUYER_PERSON_VILLAGE"]);

					if($params["BUYER_PERSON_STREET"] != '')
						$sAddrFact[] = htmlspecialcharsbx($params["BUYER_PERSON_STREET"]);

					if($params["BUYER_PERSON_ADDRESS_FACT"] != '')
						$sAddrFact[] = htmlspecialcharsbx($params["BUYER_PERSON_ADDRESS_FACT"]);

					echo implode(', ', $sAddrFact);
				?>&nbsp;</td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHOULD_PAY')?>&nbsp;<?
				if (mb_strpos($params["PAYMENT_SHOULD_PAY"], ".") !== false)
					$a = explode(".", $params["PAYMENT_SHOULD_PAY"]);
				else
					$a = explode(",", $params["PAYMENT_SHOULD_PAY"]);

				if ($a[1] <= 9 && $a[1] > 0)
					$a[1] = $a[1]."0";
				elseif ($a[1] == 0)
					$a[1] = "00";

				echo "<font style=\"text-decoration:underline;\">&nbsp;".htmlspecialcharsbx($a[0])."&nbsp;</font>&nbsp;".Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_RUB')."&nbsp;<font style=\"text-decoration:underline;\">&nbsp;".htmlspecialcharsbx($a[1])."&nbsp;</font>&nbsp;".Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_COP')."";
				?></td>
				<td align="right">&nbsp;&nbsp;<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_ADD_SUM')?>&nbsp;&nbsp;_____&nbsp;<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_RUB')?>&nbsp;____&nbsp;<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_COP')?></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_FINAL_SUM')?>&nbsp;&nbsp;_______&nbsp;<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_RUB')?>&nbsp;____&nbsp;<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_COP')?></td>
				<td align="right">&nbsp;&nbsp;&laquo;______&raquo;________________ 201____ <?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_YEAR')?></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td><small><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_CONFIRM')?></small></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td align="right"><b><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SIGNATURE')?> _____________________</b></td>
			</tr>
		</table>
	</td>
</tr>



<tr valign="top">
	<td style="width:50mm; height:70mm; border:1pt solid #000000; border-right:none;" align="center">
	<b><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_TITLE')?></b><br>
	<font style="font-size:53mm">&nbsp;<br></font>
	<b><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_CASHIER')?></b>
	</td>
	<td style="border:1pt solid #000000;" align="center">
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td align="right"><small><i><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_FORM_TITLE')?></i></small></td>
			</tr>
			<tr>
				<td style="border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params["SELLER_COMPANY_NAME"])?></td>
			</tr>
			<tr>
				<td align="center"><small><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_COMPANY_NAME')?></small></td>
			</tr>
		</table>

		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td style="width:37mm; border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params["SELLER_COMPANY_INN"])."/".htmlspecialcharsbx($params["SELLER_COMPANY_KPP"])?></td>
				<td style="width:9mm;">&nbsp;</td>
				<td style="border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params["SELLER_COMPANY_BANK_ACCOUNT"])?></td>
			</tr>
			<tr>
				<td align="center"><small><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_INN')?></small></td>
				<td><small>&nbsp;</small></td>
				<td align="center"><small><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SETTLEMENT_ACC')?></small></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_IN')?>&nbsp;</td>
				<td style="width:73mm; border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params["SELLER_COMPANY_BANK_NAME"])?></td>
				<td align="right"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_BANK_BIC')?>&nbsp;&nbsp;</td>
				<td style="width:33mm; border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params["SELLER_COMPANY_BANK_BIC"])?></td>
			</tr>
			<tr>
				<td></td>
				<td align="center"><small><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_BANK_NAME')?></small></td>
				<td></td>
				<td></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td width="1%" nowrap><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_BANK_COR_ACC')?>&nbsp;&nbsp;</td>
				<td width="100%" style="border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params["SELLER_COMPANY_BANK_ACCOUNT_CORR"])?></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td style="width:60mm; border-bottom:1pt solid #000000;"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_ORDER_ID', array('#PAYMENT_ID#' => htmlspecialcharsbx($params["PAYMENT_ID"]), '#ORDER_ID#' => htmlspecialcharsbx($params["PAYMENT_ORDER_ID"])))?>
	<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_ORDER_FROM')?>
	<?=htmlspecialcharsbx($params["PAYMENT_DATE_INSERT"])?></td>
				<td style="width:2mm;">&nbsp;</td>
				<td style="border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params['BUYER_PERSON_BANK_ACCOUNT']);?></td>
			</tr>
			<tr>
				<td align="center"><small><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_PAYMENT_NAME')?></small></td>
				<td><small>&nbsp;</small></td>
				<td align="center"><small><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_PAYMENT_ACC')?></small></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td width="1%" nowrap><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_PAYER_FIO')?>&nbsp;&nbsp;</td>
				<td width="100%" style="border-bottom:1pt solid #000000;"><?=htmlspecialcharsbx($params["BUYER_PERSON_FIO"])?></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td width="1%" nowrap><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_PAYER_ADDRESS')?>&nbsp;&nbsp;</td>
				<td width="100%" style="border-bottom:1pt solid #000000;"><?
				
					$sAddrFact = array();
					if($params["BUYER_PERSON_ZIP"] != '')
						$sAddrFact[] = htmlspecialcharsbx($params["BUYER_PERSON_ZIP"]);

					if($params["BUYER_PERSON_COUNTRY"] != '')
						$sAddrFact[] = htmlspecialcharsbx($params["BUYER_PERSON_COUNTRY"]);

					if($params["BUYER_PERSON_REGION"] != '')
						$sAddrFact[] = htmlspecialcharsbx($params["BUYER_PERSON_REGION"]);

					if($params["BUYER_PERSON_CITY"] != '')
					{
						$g = mb_substr($params["BUYER_PERSON_CITY"], 0, 2);
						$sAddrFact[] = '<nobr>'.($g<>Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_YEAR') && $g<>ToUpper(Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_YEAR'))? Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_YEAR')." ":"").htmlspecialcharsbx($params["BUYER_PERSON_CITY"]).'</nobr>';
					}

					if($params["BUYER_PERSON_VILLAGE"] != '')
						$sAddrFact[] = htmlspecialcharsbx($params["BUYER_PERSON_VILLAGE"]);

					if($params["BUYER_PERSON_STREET"] != '')
						$sAddrFact[] = htmlspecialcharsbx($params["BUYER_PERSON_STREET"]);

					if($params["BUYER_PERSON_ADDRESS_FACT"] != '')
						$sAddrFact[] = htmlspecialcharsbx($params["BUYER_PERSON_ADDRESS_FACT"]);

					echo implode(', ', $sAddrFact);
				?>&nbsp;</td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHOULD_PAY')?>&nbsp;<?
				if(mb_strpos($params["PAYMENT_SHOULD_PAY"], ".") !== false)
					$a = explode(".", $params["PAYMENT_SHOULD_PAY"]);
				else
					$a = explode(",", $params["PAYMENT_SHOULD_PAY"]);

				if ($a[1] <= 9 && $a[1] > 0)
					$a[1] = $a[1]."0";
				elseif ($a[1] == 0)
					$a[1] = "00";

				echo "<font style=\"text-decoration:underline;\">&nbsp;".htmlspecialcharsbx($a[0])."&nbsp;</font>&nbsp;".Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_RUB')."&nbsp;<font style=\"text-decoration:underline;\">&nbsp;".htmlspecialcharsbx($a[1])."&nbsp;</font>&nbsp;".Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_COP');
				?></td>
				<td align="right">&nbsp;&nbsp;<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_ADD_SUM')?>&nbsp;&nbsp;_____&nbsp;<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_RUB')?>&nbsp;____&nbsp;<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_COP')?></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_FINAL_SUM')?>&nbsp;&nbsp;_______&nbsp;<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_RUB')?>&nbsp;____&nbsp;<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_COP')?></td>
				<td align="right">&nbsp;&nbsp;&laquo;______&raquo;________________ 201____ <?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SHORT_YEAR')?></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td><small><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_CONFIRM')?></small></td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="0" style="width:122mm; margin-top:3pt;">
			<tr>
				<td align="right"><b><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_SIGNATURE')?> _____________________</b></td>
			</tr>
		</table>
	</td>
</tr>
</table>
<br />
<h1><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_WARNING')?></h1>

<!-- CONDITIONS -->
<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_FORM_CONDITIONS')?>


<p><b><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_NOTE')?></b>
<?=htmlspecialcharsbx($params["SELLER_COMPANY_NAME"])?>
	<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_SBERBANK_NOTE_DESCRIPTION')?></p>
</body>
</html>