<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Оплата через WebMoney</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?= LANG_CHARSET ?>">
</head>
<body bgColor="#ffffff">
<?
$ORDER_ID = IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]);
if ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($_POST["SendAdditionalInfo"]) > 0)
{
	$strSql = 
		"UPDATE b_sale_order SET ".
		"	ADDITIONAL_INFO = 'Идентификатор в системе WebMoney: ".$DB->ForSql($_POST["WEBMONEY_ID"], 150)."' ".
		"WHERE ID=".$ORDER_ID." AND USER_ID=".IntVal($USER->GetID())." AND PAYED<>'Y'";
	$DB->Query($strSql);
	?>
	<font class="text"><font color="#006600"><b>Спасибо, ваш идентификатор записан. Вы можете закрыть данное окно.</b></font></font>
	<?
}
?>
<p><font class="tablebodytext"><b>Счет № <?= $ORDER_ID ?> от <?= htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT"]) ?></b></font></p>
<p>
Сумма к оплате: <b><?echo SaleFormatCurrency($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"], $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]) ?></b>
</font></p>

<form method="POST" action="">
<p><font class="tablebodytext">
Пожалуйста, сообщите ваш идентификатор в системе WebMoney. 
В течение 24 часов мы вышлем вам счет для
оплаты средствами системы WebMoney.</font></p>
<table border="0" cellspacing="0" cellpadding="3">
<tr>
	<td><font class="tableheadtext">Идентификатор:</font></td>
	<td><font size="-1"><input type="text" name="WEBMONEY_ID" size="30" value="<?= htmlspecialcharsbx($WEBMONEY_ID) ?>"></font></td>
</tr>
</table>
<p><font class="tablebodytext">
<input type="hidden" name="ORDER_ID" value="<?= $ORDER_ID ?>">
<input type="hidden" name="SendAdditionalInfo" value="send">
<input type="submit" name="SendAdditionalInfo" value="Отправить"></font></p>
</form>

<p><font class="tablebodytext">Счет действителен в течение трех дней.</font></p>

</body>
</html>
