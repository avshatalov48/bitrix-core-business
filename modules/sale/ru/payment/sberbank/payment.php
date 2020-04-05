<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Квитанция</title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?= LANG_CHARSET ?>">
		<style type="text/css">
		H1 {font-size: 14pt;}
		</style>
	</head>
	<body bgColor="#ffffff">
		<TABLE height="538" bgColor="#ffffff" border=1 borderColor="#000000" cellPadding=3 cellSpacing=0 width="515">
		<!-- ИЗВЕЩЕНИЕ -->
		<TR> 
			<TD width="170" rowspan="4" valign="top"><B>&nbsp; <font size="-1">ИЗВЕЩЕНИЕ</font></B></TD>
			<TD valign="top" colspan="2"> 
			<div align="right"><b><font size="-1">Форма № ПД-4</font></b></div>
			<font size="-1">
			<?= htmlspecialcharsEx(CSalePaySystemAction::GetParamValue("SELLER_PARAMS")) ?>
			</font> 
			<hr size="1" color="black">
			<font size="-1"> 
			<?= htmlspecialcharsEx(CSalePaySystemAction::GetParamValue("PAYER_NAME")) ?>
			</font> 
			<hr size="1" color="black">
			<center>
			<font style="font-size: 7pt;">(ф.и.о. плательщика)</font> 
			</center>
			</TD>
		</TR>
		<TR> 
			<TD width=270 height="27"><font size="-1">Наименование платежа</font></TD>
			<TD width=100 align="center" height="27"><font size="-1">Сумма</font></TD>
		</TR>
		<!-- Изменяемые данные (начало) -->
		<TR> 
			<TD vAlign=middle width=270 height="53"> <font size="-1"><STRONG>Оплата заказа № 
			<?= IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]) ?>
			от 
			<?= htmlspecialcharsbx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT_DATE"]) ?></STRONG>
			</font></TD>
			<TD valign="middle" align="center"><b> <font size="-1"> 
			<?= SaleFormatCurrency($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"], $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"]) ?>
			</font></b></TD>
		</TR>
		<!-- Изменяемые данные (конец) -->
		<TR> 
			<TD colSpan="2"><font style="font-size: 9pt">
			<br>С условиями приема указанной в платежном документе суммы, в т.ч. с суммой взимаемой платы за услуги банка, ознакомлен и согласен. 
			<br><br>_______________ "____" __________ 201__ г.</font></TD>
		</TR>
		<!-- КВИТАНЦИЯ -->
		<TR> 
			<TD width="170" rowspan="4" valign="bottom"><B>&nbsp; <font size="-1">КВИТАНЦИЯ</font></B></TD>
			<TD valign="top" colspan="2"> 
			<div align="right"><b><font size="-1">Форма № ПД-4</font></b></div>
			<font size="-1">
			<?= htmlspecialcharsEx(CSalePaySystemAction::GetParamValue("SELLER_PARAMS")) ?>
			</font> 
			<hr size="1" color="black">
			<font size="-1"> 
			<?= htmlspecialcharsEx(CSalePaySystemAction::GetParamValue("PAYER_NAME")) ?>
			</font> 
			<hr size="1" color="black">
			<center>
			<font style="font-size: 7pt;">(ф.и.о. плательщика)</font> 
			</center>
			</TD>
		</TR>
		<TR> 
			<TD height=27 width=270><font size="-1">Наименование платежа</font></TD>
			<TD height=27 width=100 align="center"><font size="-1"> Сумма </font></TD>
		</TR>
		<!-- Изменяемые данные (начало) -->
		<TR> 
			<TD height=53 vAlign=middle width=270> <font size="-1"><strong>Оплата заказа № 
			<?= IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"]) ?>
			от 
			<?= htmlspecialcharsEx($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT_DATE"]) ?></strong></font></TD>
			<TD> 
			<center>
			<font size="-1"><b> <font size="-1"> 
			<?= SaleFormatCurrency($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"], $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"])?>
			</font></b> </font> 
			</center>
			</TD>
		</TR>
		<!-- Изменяемые данные (конец) -->
		<TR> 
			<TD colSpan="2"><font style="font-size: 9pt">
			<br>С условиями приема указанной в платежном документе суммы, в т.ч. с суммой взимаемой платы за услуги банка, ознакомлен и согласен. 
			<br><br>_______________ "____" __________ 201__ г.</font></TD>
		</TR>
		</TABLE>

		<!-- Условия поставки -->
		<h1><br>
		<b>Метод оплаты:</b> </h1>
		<ol>
			<li>Распечатайте квитанцию. Если у вас нет принтера, перепишете верхнюю часть квитанции и заполните по этому образцу стандартный бланк квитанции в вашем банке.</li>
			<li>Вырежьте по контуру квитанцию.</li>
			<li>Оплатите квитанцию в любом отделении банка, принимающего платежи от частных лиц.</li>
			<li>Сохраните квитанцию до подтверждения исполнения заказа.</li>
		</ol>
		<h1><b>Условия поставки:</b> </h1>
		<ul>
			<li>Отгрузка оплаченного товара производится после подтверждения факта платежа.</li>
			<li>Идентификация платежа производится по квитанции, поступившей в наш банк.</li>
		</ul>
		<h1 align="justify"><b>Сроки поставки:</b> </h1>
		<p align="justify">В течение 72 часов после факта подтверждения платежа.</p>
	</body>
</html>