<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>КВИТАНЦІЯ</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?= LANG_CHARSET ?>">

<style type="text/css">
.b_kvit {
	border-collapse:collapse;
	border-spacing:0;
	width:180mm;
	font-size: 13px;
	font-family: Arial, Helvetica, sans-serif;
}
.b_kvit td {
	padding:10px;
}
.b_kvit td table {
	border-collapse:collapse;
	border-spacing:0;
	width: 100%;
	margin-top: 5px;
}
.b_kvit td table td {
	padding: 0;
	text-align: center;
	border:none;
}
.b_kvit .print {
	width:45mm;
	text-align: right;
	text-transform: uppercase;
	font-size: 16px;
	font-weight: bold;
	border: none;
	border-right: 3px solid #000;
	border-bottom: 3px solid #000;
	padding-top: 40px;
	padding-bottom: 40px;
}
.b_kvit .content {
	border: none;
	border-bottom: 3px solid #000;
	text-align: left;
	padding: 3px 5px  5px;
}
.b_kvit .print.last {
	border-bottom: none;
}
.b_kvit .content.last {
	border-bottom: none;
}
.b_kvit .form {
	width: 100%;
	text-align: right;
	font-size: 10px;
	font-style: italic;
}
.b_kvit .text {
	width: 100%;
}
.b_kvit .text span {
	border-bottom: 1px solid #000;
	text-align: center;
	display: inline-block;
	width: 94mm;
	font-weight: bold;
	font-style: italic;
}
.b_kvit .text input {
	border:none;
	padding:0;
	font-weight: bold;
	font-style: italic;
}
.left {
	float: left;
}
.right {
	float: right;
}
.b_kvit .number, .b_kvit .id, .b_kvit .bank,
.b_kvit .payer_num, .b_kvit .list, .b_kvit .summa {
	border: 1px solid #000;
	font-weight: bold;
	font-style: italic;
	font-size: 13px;
}
.b_kvit .number {
	width: 60mm;
}
.b_kvit .id {
	width: 55mm;
}
.b_kvit .comment {
	font-size: 11px;
}
.b_kvit .text.fio {
	border-bottom: 1px solid #000;
	text-align: center;
	margin-top: 5px;
	font-weight: bold;
	font-style: italic;
}
.b_kvit .text.connebt {
	font-size: 11px;
	text-align: left;
}

.b_kvit .payer_num {
	width: 68mm;
}
.b_kvit .list {
	width: 50mm;
	text-align: left;
	font-weight: normal;
	padding-left: 5px;
}
.b_kvit .list.val {
	font-weight: bold;
	text-align: center;
	width: auto;
}
.b_kvit .summa {
	width: 50mm;
}
.b_kvit .singuar {
	vertical-align: bottom;
	width: 65mm;
	text-align: left;
}
.b_kvit .singuar .text span {
	width: 30mm;
}
.b_kvit .sum_title {
	text-align: left;
}
</style>

</head>
<body bgColor="#fff">
<?
$arKvit = array(0 => "ПОВІДОМЛЕННЯ", 1 => "КВИТАНЦІЯ");
?>
<table class="b_kvit">
	<?for ($i = 0;$i < 2;$i++):?>
	<tr>
		<td class="print"><?=$arKvit[$i]?></td>
		<td class="content">
			<div class="form">додаток №61 ф.3</div>

			<div class="text">Отримувач платежу<span><?=(CSalePaySystemAction::GetParamValue("RECIPIENT_NAME"))?></span></div>

			<table>
				<tr>
					<td class="number"><?=(CSalePaySystemAction::GetParamValue("RECIPIENT_NUMBER"))?></td>
					<td></td>
					<td class="id"><?=(CSalePaySystemAction::GetParamValue("RECIPIENT_ID"))?></td>
				</tr>
				<tr class="comment">
					<td>Поточний рахунок отримувача</td>
					<td></td>
					<td>Ідентифікаційний код отримувача</td>
				</tr>
			</table>

			<div class="text">Дата валютування <b><input type="text" value="<?=date("d.m.Y")?>" ></b></div>

			<table>
				<tr>
					<td class="bank"><?=(CSalePaySystemAction::GetParamValue("RECIPIENT_BANK"))?></td>
					<td class="id"><?=(CSalePaySystemAction::GetParamValue("RECIPIENT_CODE_BANK"))?></td>
				</tr>
				<tr class="comment">
					<td>Установа банку</td>
					<td>Код установи банку</td>
				</tr>
			</table>

			<div class="text fio">
				<?
				$adres = CSalePaySystemAction::GetParamValue("PAYER_FIO");

				if (CSalePaySystemAction::GetParamValue("PAYER_INDEX") <> '')
					$adres .= ($adres<>""? ", ":"").CSalePaySystemAction::GetParamValue("PAYER_INDEX");

				if (CSalePaySystemAction::GetParamValue("PAYER_TOWN") <> '')
					$adres .= ($adres<>""? ", ":"")."г.".CSalePaySystemAction::GetParamValue("PAYER_TOWN");

				if (CSalePaySystemAction::GetParamValue("PAYER_ADRES") <> '')
					$adres .= ($adres<>""? ", ":"").CSalePaySystemAction::GetParamValue("PAYER_ADRES");

				echo $adres;
				?>
			</div>
			<div class="text comment">Прізвище, ім'я та по батькові, адреса платника</div>

			<table>
				<tr>
					<td></td>
					<td class="payer_num"><?=(CSalePaySystemAction::GetParamValue("PAYER_NUMBER"))?></td>
				</tr>
				<tr class="comment">
					<td></td>
					<td>Код установи банку</td>
				</tr>
			</table>

			<table>
				<tr>
					<td class="list">Призначення платежу</td>
					<td class="list val">оплата заказа № <?=(CSalePaySystemAction::GetParamValue("ORDER_ID"))?> от <?=(CSalePaySystemAction::GetParamValue("ORDER_DATE"))?></td>
				</tr>
				<tr>
					<td class="list">Період платежу</td>
					<td class="list val"><?=(CSalePaySystemAction::GetParamValue("PAYMENT_PERIOD"))?></td>
				</tr>
				<tr>
					<td class="list">Код виду платежу</td>
					<td class="list val"><?=(CSalePaySystemAction::GetParamValue("PAYMENT_CODE"))?></td>
				</tr>
				<tr>
					<td class="list">Код бюджетної класифікації</td>
					<td class="list val"><?=(CSalePaySystemAction::GetParamValue("PAYMENT_CLASSIC"))?></td>
				</tr>
			</table>

			<table>
				<tr>
					<td rowspan="3" class="singuar">
						<div class="text">Підпис платника<span>&nbsp;</span></div>
					</td>
					<td class="sum_title">Сума</td>
					<td class="summa"><?=(CSalePaySystemAction::GetParamValue("SHOULD_PAY"))?> грн.</td>
				</tr>
				<tr>
					<td class="sum_title">Пеня</td>
					<td class="summa">&nbsp;</td>
				</tr>
				<tr>
					<td class="sum_title">Усього</td>
					<td class="summa"><?=(CSalePaySystemAction::GetParamValue("SHOULD_PAY"))?> грн.</td>
				</tr>
			</table>
		</td>
	</tr>
	<?endfor;?>
</table>

</body>
</html>