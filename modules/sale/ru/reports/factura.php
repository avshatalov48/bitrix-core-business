<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
/*
Данный документ учитывает только налог с мнемоническим кодом "NDS". Остальные налоги при формировании документа не отображаются

Скопируйте этот файл в папку /bitrix/admin/reports и измените по своему усмотрению

$ORDER_ID - ID текущего заказа

$arOrder - массив атрибутов заказа (ID, доставка, стоимость, дата создания и т.д.)
Следующий PHP код:
print_r($arOrder);
выведет на экран содержимое массива $arOrder.

$arOrderProps - массив свойств заказа (вводятся покупателями при оформлении заказа) следующей структуры:
array(
	"мнемонический код (или ID если мнемонический код пуст) свойства" => "значение свойства"
	)

$arParams - массив из настроек Печатных форм

$arUser - массив из настроек пользователя, совершившего заказ
*/
?><html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=<?=LANG_CHARSET?>">
<meta name=ProgId content=Excel.Sheet>
<title langs="ru">Счет-фактура</title>
<style>
<!--table
	{mso-displayed-decimal-separator:"\.";
	mso-displayed-thousand-separator:" ";}
@page
	{margin:.2in .39in .28in .59in;
	mso-header-margin:.2in;
	mso-footer-margin:.28in;
	mso-page-orientation:landscape;}
tr
	{mso-height-source:auto;}
col
	{mso-width-source:auto;}
br
	{mso-data-placement:same-cell;}
.style0
	{mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	white-space:nowrap;
	mso-rotate:0;
	mso-background-source:auto;
	mso-pattern:auto;
	color:windowtext;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:"Arial Cyr";
	mso-generic-font-family:auto;
	mso-font-charset:204;
	border:none;
	mso-protection:locked visible;
	mso-style-name:Обычный;
	mso-style-id:0;}
.style22
	{mso-number-format:General;
	text-align:general;
	white-space:nowrap;
	mso-rotate:0;
	mso-background-source:auto;
	mso-pattern:auto;
	color:windowtext;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:"Arial Cyr";
	mso-generic-font-family:auto;
	mso-font-charset:204;
	border:none;
	mso-protection:locked visible;
	mso-style-name:Обычный_Sf_131;}
.style27
	{mso-number-format:0%;
	mso-style-name:Процентный;
	mso-style-id:5;}
td
	{mso-style-parent:style0;
	padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:windowtext;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:"Arial Cyr";
	mso-generic-font-family:auto;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	border:none;
	mso-background-source:auto;
	mso-pattern:auto;
	mso-protection:locked visible;
	white-space:nowrap;
	mso-rotate:0;}
.xl32
	{mso-style-parent:style22;}
.xl33
	{mso-style-parent:style22;
	font-size:8.0pt;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;}
.xl34
	{mso-style-parent:style22;
	font-size:2.0pt;
	font-family:"Arial Cyr", sans-serif;
	border-top:none;
	border-right:none;
	border-bottom:2.0pt double windowtext;
	border-left:none;}
.xl35
	{mso-style-parent:style22;
	text-align:right;
	border-top:none;
	border-right:none;
	border-bottom:2.0pt double windowtext;
	border-left:none;}
.xl36
	{mso-style-parent:style22;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;}
.xl37
	{mso-style-parent:style22;
	vertical-align:middle;
	white-space:normal;}
.xl38
	{mso-style-parent:style22;
	font-size:8.0pt;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;}
.xl39
	{mso-style-parent:style22;
	vertical-align:middle;}
.xl40
	{mso-style-parent:style22;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	text-align:center;
	vertical-align:top;
	border:.5pt solid windowtext;
	white-space:normal;}
.xl41
	{mso-style-parent:style22;
	font-size:9.0pt;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	text-align:center;
	vertical-align:top;
	border:.5pt solid windowtext;}
.xl42
	{mso-style-parent:style22;
	font-size:9.0pt;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	mso-number-format:Standard;
	vertical-align:top;
	border:.5pt solid windowtext;}
.xl43
	{mso-style-parent:style22;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;}
.xl44
	{mso-style-parent:style27;
	font-size:9.0pt;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	mso-number-format:0%;
	vertical-align:top;
	border:.5pt solid windowtext;
	white-space:normal;}
.xl45
	{mso-style-parent:style22;
	font-size:9.0pt;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	mso-number-format:Standard;
	vertical-align:top;
	border:.5pt solid windowtext;
	white-space:normal;}
.xl46
	{mso-style-parent:style22;
	font-size:9.0pt;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	mso-number-format:Standard;
	text-align:center;
	vertical-align:top;
	border:.5pt solid windowtext;
	white-space:normal;}
.xl47
	{mso-style-parent:style22;
	font-weight:700;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	vertical-align:top;}
.xl48
	{mso-style-parent:style22;
	font-size:9.0pt;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	vertical-align:top;}
.xl49
	{mso-style-parent:style22;
	font-size:9.0pt;
	font-weight:700;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	vertical-align:top;}
.xl50
	{mso-style-parent:style22;
	vertical-align:top;}
.xl51
	{mso-style-parent:style0;
	vertical-align:top;}
.xl52
	{mso-style-parent:style22;
	text-align:right;
	vertical-align:top;}
.xl53
	{mso-style-parent:style22;
	font-size:9.0pt;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	mso-number-format:Standard;
	text-align:center;
	vertical-align:top;
	border:.5pt solid windowtext;
	white-space:normal;}
.xl54
	{mso-style-parent:style22;
	font-size:8.0pt;
	font-family:"Arial CYR", sans-serif;
	mso-font-charset:204;
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;
	white-space:normal;}
.xl55
	{mso-style-parent:style22;
	font-size:9.0pt;
	font-weight:700;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;
	white-space:normal;}
.xl56
	{mso-style-parent:style22;
	font-size:6.0pt;
	font-weight:700;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;
	white-space:normal;}
.xl57
	{mso-style-parent:style22;
	font-size:7.0pt;
	font-weight:700;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;
	white-space:normal;}
.xl58
	{mso-style-parent:style22;
	font-size:9.0pt;
	font-weight:700;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	border-top:none;
	border-right:none;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;}
.xl59
	{mso-style-parent:style22;
	border-top:none;
	border-right:none;
	border-bottom:.5pt solid windowtext;
	border-left:none;}
.xl60
	{mso-style-parent:style22;
	mso-number-format:Standard;
	font-size:9.0pt;
	border:.5pt solid windowtext;
	white-space:normal;}
.xl61
	{mso-style-parent:style22;
	font-size:9.0pt;
	border-top:none;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;}
.xl62
	{mso-style-parent:style22;
	mso-number-format:Standard;
	font-size:9.0pt;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:none;
	white-space:normal;}
.xl63
	{mso-style-parent:style22;
	font-size:9.0pt;
	font-weight:700;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	mso-number-format:Standard;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:none;
	white-space:normal;}
.xl64
	{mso-style-parent:style22;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	vertical-align:top;}
.xl65
	{mso-style-parent:style22;
	font-size:14.0pt;
	font-weight:700;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	text-align:center;}
.xl66
	{mso-style-parent:style22;
	font-size:9.0pt;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	text-align:left;
	vertical-align:top;
	border:.5pt solid windowtext;
	white-space:normal;}
.xl67
	{mso-style-parent:style22;
	font-size:14.0pt;
	font-weight:700;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	text-align:left;}
.xl68
	{mso-style-parent:style0;
	text-align:left;
	vertical-align:top;
	white-space:normal;}
.xl69
	{mso-style-parent:style0;
	text-align:left;
	vertical-align:top;}
.xl70
	{mso-style-parent:style0;
	font-size:9.0pt;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	mso-number-format:"\@";
	vertical-align:top;}
.xl71
	{mso-style-parent:style0;
	font-size:9.0pt;
	font-family:"Arial Cyr", sans-serif;
	mso-font-charset:204;
	vertical-align:top;}
.xl72
	{mso-style-parent:style0;
	mso-number-format:"\@";
	text-align:left;
	vertical-align:top;}
.xl73
	{mso-style-parent:style22;
	mso-number-format:"\@";
	text-align:left;
	vertical-align:top;}
-->
</style>
<title>Счет-фактура</title>
</head>

<body link=blue vlink=purple class=xl32>

<table x:str border=0 cellpadding=0 cellspacing=0 width=987 style='border-collapse:
collapse;table-layout:fixed;width:740pt'>
<col class=xl32 width=311 style='mso-width-source:userset;mso-width-alt:11373;
width:233pt'>
<col class=xl32 width=40 style='mso-width-source:userset;mso-width-alt:1462;
width:30pt'>
<col class=xl32 width=39 style='mso-width-source:userset;mso-width-alt:1426;
width:29pt'>
<col class=xl32 width=71 style='mso-width-source:userset;mso-width-alt:2596;
width:53pt'>
<col class=xl32 width=80 style='mso-width-source:userset;mso-width-alt:2925;
width:60pt'>
<col class=xl32 width=31 style='mso-width-source:userset;mso-width-alt:1133;
width:23pt'>
<col class=xl32 width=43 style='mso-width-source:userset;mso-width-alt:1572;
width:32pt'>
<col class=xl32 width=78 style='mso-width-source:userset;mso-width-alt:2852;
width:59pt'>
<col class=xl32 width=83 style='mso-width-source:userset;mso-width-alt:3035;
width:62pt'>
<col class=xl32 width=97 style='mso-width-source:userset;mso-width-alt:3547;
width:73pt'>
<col class=xl32 width=114 style='mso-width-source:userset;mso-width-alt:4169;
width:86pt'>

<tr height=24 style='mso-height-source:userset;height:18.6pt'>
	<td nowrap height=24 class=xl67 colspan=11 style='height:18.6pt;mso-ignore:
	colspan;'>СЧЕТ-ФАКТУРА № 
	<input size="25" style="border:0px solid #000000;font-size:16px;font-style:bold;" type="text" value="_____ от '__' _______ ">
	</td>
	</tr>

	<tr height=24 style='mso-height-source:userset;height:18.6pt'>
	<td nowrap height=24 class=xl67 colspan=11 style='height:18.6pt;mso-ignore:
	colspan;'>ИСПРАВЛЕНИЕ №
	<input size="25" style="border:0px solid #000000;font-size:16px;font-style:bold;" type="text" value="_____ от '__' _______ ">
	</td>
</tr>
<tr height=12 style='mso-height-source:userset;height:9.6pt'>
	<td colspan=11 height=12 class=xl34 style='height:9.6pt'>&nbsp;</td>
</tr>
<tr class=xl50 height=19 style='mso-height-source:userset;height:14.25pt'>
	<td height=19 class=xl47 style='height:14.25pt'>Продавец</td>
	<td class=xl64 colspan=10 style='mso-ignore:colspan'><?=$arParams["COMPANY_NAME"]?></td>
</tr>
<tr class=xl50 height=18 style='mso-height-source:userset;height:13.5pt'>
	<td height=18 class=xl50 style='height:13.5pt'>Адрес</td>
	<td class=xl50 colspan=10 style='mso-ignore:colspan'><? echo $arParams["COUNTRY"].", ".$arParams["INDEX"].", г. ".$arParams["CITY"].", ".$arParams["ADDRESS"];?></td>
</tr>	 
<tr class=xl50 height=18 style='mso-height-source:userset;height:13.5pt'>
	<td height=18 class=xl50 style='height:13.5pt'>ИНН/КПП продавца</td>
	<td colspan=10 class=xl73><?=$arParams["INN"]?> / <?=$arParams["KPP"]?></td>
</tr>
<tr class=xl50 height=18 style='mso-height-source:userset;height:13.5pt'>
	<td height=18 class=xl50 style='height:13.5pt'>Грузоотправитель и его адрес</td>
	<td colspan=10 class=xl50><?=$arParams["COMPANY_NAME"]?>, <?=$arParams["COUNTRY"]?>, <?=$arParams["INDEX"]?>, г. <?=$arParams["CITY"]?>, <?=$arParams["ADDRESS"]?></td>
</tr>
<tr class=xl50 height=18 style='mso-height-source:userset;height:13.5pt'>
	<td height=18 class=xl50 style='height:13.5pt'>Грузополучатель и его адрес</td>
	<td class=xl64 colspan=10 style='mso-ignore:colspan'>
	<?if(empty($arParams))
	{	?>
		<?//изменить F_NAME, F_INDEX,... на реальные мнемонические коды свойств?>
		<?echo $arOrderProps["F_NAME"];?>,
		<?echo $arOrderProps["F_INDEX"];?>
		<?
		$arVal = CSaleLocation::GetByID($arOrderProps["F_LOCATION"], "ru");
		echo htmlspecialcharsbx($arVal["COUNTRY_NAME"]." - ".$arVal["CITY_NAME"]);
		?>
		<?if ($arOrderProps["F_CITY"] <> '') echo ", г. ".$arOrderProps["F_CITY"];?>
		<?if ($arOrderProps["F_ADDRESS"] <> '') echo ", ".$arOrderProps["F_ADDRESS"];?>
		<?
	}
	else
	{
		if($arParams["BUYER_COMPANY_NAME"] <> '')
			$buyerName = $arParams["BUYER_COMPANY_NAME"];
	    else
			$buyerName = $arParams["BUYER_LAST_NAME"]." ".$arParams["BUYER_FIRST_NAME"]." ".$arParams["BUYER_SECOND_NAME"];
		?>
		<?=$buyerName;?>, <?=$arParams["BUYER_COUNTRY"]?>, <?=$arParams["BUYER_INDEX"]?>, г. <?=$arParams["BUYER_CITY"]?>, <?=$arParams["BUYER_ADDRESS"]?>
		<?
	}
	?>
</td>
</tr>
<tr class=xl50 height=19 style='mso-height-source:userset;height:14.25pt'>
	<td height=19 class=xl50 style='height:14.25pt'>К платежно-расчетному документу</td>
	<td colspan=10 class=xl64 style='mso-ignore:colspan'>
	<input size="50" style="border:0px solid #000000;" type="text" value="№_______ от _______________">
	</td>
</tr>
<tr class=xl50 height=18 style='mso-height-source:userset;height:13.5pt'>
	<td height=18 class=xl47 style='height:13.5pt'>Покупатель</td>
	<td class=xl64 colspan=10>
	<?if(empty($arParams))
	{	
		//изменить F_NAME на реальный мнемонический код свойства заказа "название компании"
		echo $arOrderProps["F_NAME"];
	}
	else
	{
		echo $arParams["BUYER_COMPANY_NAME"];
	}?>
	</td>
</tr>
<tr class=xl50 height=18 style='mso-height-source:userset;height:13.5pt'>
	<td height=18 class=xl50 style='height:13.5pt'>Адрес</td>
	<td colspan=10 class=xl50 style='mso-ignore:colspan'>
	<?if(empty($arParams))
	{	
		
		//изменить F_INDEX, F_LOCATION,... на реальные мнемонические коды свойств
		echo $arOrderProps["F_INDEX"];
		$arVal = CSaleLocation::GetByID($arOrderProps["F_LOCATION"], "ru");
		echo htmlspecialcharsbx($arVal["COUNTRY_NAME"]." - ".$arVal["CITY_NAME"]);
		if ($arOrderProps["F_CITY"] <> '') echo ", г. ".$arOrderProps["F_CITY"];
		if ($arOrderProps["F_ADDRESS"] <> '') echo ", ".$arOrderProps["F_ADDRESS"];

	}
	else
	{
		echo $arParams["BUYER_COUNTRY"].", ".$arParams["BUYER_INDEX"].", г. ".$arParams["BUYER_CITY"].", ".$arParams["BUYER_ADDRESS"];
	}?>

	</td>
</tr>
<tr class=xl50 height=18 style='mso-height-source:userset;height:13.5pt'>
	<td height=18 class=xl50 style='height:13.5pt'>ИНН/КПП покупателя</td>
	<td colspan=10 class=xl68>
	<?if(empty($arParams))
	{	
		//изменить F_INN на реальный мнемонический код свойства заказа "INN компании"
		echo $arOrderProps["F_INN"];
	}
	else
	{
		echo $arParams["BUYER_INN"]." / ".$arParams["BUYER_KPP"];
	}?>
		
	</td>
</tr>
<tr class=xl50 height=19 style='mso-height-source:userset;height:14.25pt'>
	<td height=19 class=xl50 style='height:14.25pt;mso-ignore:colspan'>Валюта: наименование, код </td>
	<td colspan=6 class=xl50 style='mso-ignore:colspan'><input size="50" style="border:0px solid #000000;" type="text" value="__________________________"></td>
</tr>
<tr height=15 style='mso-height-source:userset;height:11.25pt'>
	<td height=15 colspan=11 class=xl32 style='height:11.25pt;mso-ignore:colspan'></td>
</tr>

<tr>
	<td colspan="11">
		<table cellpadding=0 cellspacing=0 width=987 style='border-collapse:collapse;table-layout:fixed;width:100%'>
			<tr class=xl37 height=44 style='mso-height-source:userset;height:33.6pt'>
				<td rowspan="2" class="xl54" style='width:150pt'>Наименование товара (описание  <br>выполненных работ, оказанных услуг),  <br>имущественного права</td>
				<td colspan="2" class="xl54" style='width:80pt'>Единица измерения</td>
				<td rowspan="2" class="xl54" style='width:30pt'>Коли-<br>чество<br>(объем)</td>
				<td rowspan="2" class="xl54" style='width:60pt'>Цена<br>(тариф)<br>за единицу<br>измерения</td>
				<td rowspan="2" class="xl54" style='width:60pt'>Стоимость товаров (работ, услуг), имущественных прав без налога - всего</td>
				<td rowspan="2" class="xl54" style='width:30pt'>В том числе сумма акциза</td>
				<td rowspan="2" class="xl54" style='width:40pt'>Налоговая<br>ставка</td>
				<td rowspan="2" class="xl54" style='width:60pt'>Сумма налога, предъяв-ляемая покупателю</td>
				<td rowspan="2" class="xl54" style='width:60pt'>Стоимость товаров (работ, услуг), имущественных прав с налогом - всего</td>
				<td colspan="2" class="xl54" style='width:70pt'>Страна происхождения товара</td>
				<td rowspan="2" class="xl56" style='width:40pt'>Номер таможенной декларации</td>
			</tr>
			<tr>
				<td class="xl56" style='width:20pt'>код</td>
				<td class="xl56" style='width:60pt'>условное обозначение (националь<br>ное)</td>
				<td class="xl56" >цифровой код</td>
				<td class="xl56" >краткое наименова<br>ние</td>
			</tr>
			<tr>
				<td class="xl38">1</td>
				<td class="xl38">2</td>
				<td class="xl38">2a</td>
				<td class="xl38">3</td>
				<td class="xl38">4</td>
				<td class="xl38">5</td>
				<td class="xl38">6</td>
				<td class="xl38">7</td>
				<td class="xl38">8</td>
				<td class="xl38">9</td>
				<td class="xl38">10</td>
				<td class="xl38">10a</td>
				<td class="xl38">11</td>
			</tr>
<?
$priceTotal = 0;
$bUseVat = false;
$arBasketOrder = array();
for ($i = 0, $max = count($arBasketIDs); $i < $max; $i++)
{
	$arBasketTmp = CSaleBasket::GetByID($arBasketIDs[$i]);

	if (floatval($arBasketTmp["VAT_RATE"]) > 0 )
		$bUseVat = true;

	$priceTotal += $arBasketTmp["PRICE"]*$arBasketTmp["QUANTITY"];

	$arBasketTmp["PROPS"] = array();
	if (isset($_GET["PROPS_ENABLE"]) && $_GET["PROPS_ENABLE"] == "Y")
	{
		$dbBasketProps = CSaleBasket::GetPropsList(
				array("SORT" => "ASC", "NAME" => "ASC"),
				array("BASKET_ID" => $arBasketTmp["ID"]),
				false,
				false,
				array("ID", "BASKET_ID", "NAME", "VALUE", "CODE", "SORT")
			);
		while ($arBasketProps = $dbBasketProps->GetNext())
			$arBasketTmp["PROPS"][$arBasketProps["ID"]] = $arBasketProps;
	}

	$arBasketOrder[] = $arBasketTmp;
}

if ($arOrder['DELIVERY_VAT_RATE'] > 0)
{
	$bUseVat = true;
}

if (is_array($arBasketOrder) && !empty($arBasketOrder))
{
	$arBasketOrder = getMeasures($arBasketOrder);
}

//разбрасываем скидку на заказ по товарам
if (floatval($arOrder["DISCOUNT_VALUE"]) > 0)
{
	$arBasketOrder = GetUniformDestribution($arBasketOrder, $arOrder["DISCOUNT_VALUE"], $priceTotal);
}

//налоги
$arTaxList = array();
$db_tax_list = CSaleOrderTax::GetList(array("APPLY_ORDER"=>"ASC"), Array("ORDER_ID"=>$ORDER_ID));
$iNds = -1;
$i = 0;
while ($ar_tax_list = $db_tax_list->Fetch())
{
	$arTaxList[$i] = $ar_tax_list;
	// определяем, какой из налогов - НДС
	// НДС должен иметь код NDS, либо необходимо перенести этот шаблон 
	// в каталог пользовательских шаблонов и исправить
	if ($arTaxList[$i]["CODE"] == "NDS")
		$iNds = $i;
	$i++;
}

//состав заказа
$total_price = 0.00;
$total_sum = 0.00;
$total_nds = 0.00;
$bVat = false;
$mi = 0;

foreach ($arBasketOrder as $arBasket):
	$nds_val = 0;
	$taxRate = 0;

	if (floatval($arQuantities[$mi]) <= 0)
		$arQuantities[$mi] = DoubleVal($arBasket["QUANTITY"]);
	
	$b_AMOUNT = DoubleVal($arBasket["PRICE"]);

	//определяем начальную цену
	$item_price = $b_AMOUNT;

	$nds_val = 0;
	$taxRate = 0;
	if(DoubleVal($arBasket["VAT_RATE"]) > 0)
	{
		$bVat = true;
		$nds_val = ($b_AMOUNT - DoubleVal($b_AMOUNT/(1+$arBasket["VAT_RATE"])));
		$item_price = $b_AMOUNT - $nds_val;
		$taxRate = $arBasket["VAT_RATE"]*100;
	}
	elseif(!$bUseVat)
	{
		$basket_tax = CSaleOrderTax::CountTaxes($b_AMOUNT*$arQuantities[$mi], $arTaxList, $arOrder["CURRENCY"]);

		for ($i = 0, $max = count($arTaxList); $i < $max; $i++)
		{
			if ($arTaxList[$i]["IS_IN_PRICE"] == "Y")
			{
				$item_price -= $arTaxList[$i]["TAX_VAL"];
			}
			$nds_val += DoubleVal($arTaxList[$i]["TAX_VAL"]);
			$taxRate += ($arTaxList[$i]["VALUE"]);
		}
	}

?>
<tr class=xl39>
	<td class=xl66 width=213 style='border-top:none;width:233pt'>
		<?echo htmlspecialcharsEx($arBasket["NAME"]) ?>
		<?
		if (is_array($arBasket["PROPS"]) && $_GET["PROPS_ENABLE"] == "Y")
		{
			foreach($arBasket["PROPS"] as $vv)
			{
				if($vv["VALUE"] <> '' && $vv["CODE"] != "CATALOG.XML_ID" && $vv["CODE"] != "PRODUCT.XML_ID")
					echo "<div style=\"font-size:8pt\">".$vv["NAME"].": ".$vv["VALUE"]."</div>";
			}
		}
		?>
	</td>
	<td class=xl40>---</td>
	<td class=xl40 width=40 style='border-top:none;border-left:none;width:30pt'><?=$arBasket['MEASURE_TEXT']?></td>
	<td class=xl41 style='border-top:none;border-left:none'><?echo Bitrix\Sale\BasketItem::formatQuantity($arQuantities[$mi]);?></td>
	<td align="right" class=xl42 style='border-top:none;border-left:none'><?=CCurrencyLang::CurrencyFormat($item_price, $arOrder["CURRENCY"], false);?></td>
	<td class=xl42 align=right style='border-top:none;border-left:none' x:num>
		<?
			echo CCurrencyLang::CurrencyFormat($item_price * $arQuantities[$mi], $arOrder["CURRENCY"], false);
			if (empty($arBasket['SET_PARENT_ID']))
			{
				$total_price += ($item_price*$arQuantities[$mi]);
			}
		?>
	</td>
	<td class=xl43 style='border-top:none;border-left:none'>&nbsp;</td>
	<td class=xl44 align=right width=43 style='border-top:none;border-left:none;
	width:32pt'><?=($taxRate > 0 || count($arTaxList) > 0) ? $taxRate."%" : "Без НДС";?></td>
	<td class=xl45 align=right width=78 style='border-top:none;border-left:none;
	width:59pt' x:num>
		<?
			echo CCurrencyLang::CurrencyFormat($nds_val*$arQuantities[$mi], $arOrder["CURRENCY"], false);
			if (empty($arBasket['SET_PARENT_ID']))
			{
				$total_nds += $nds_val*$arQuantities[$mi];
			}
		?>
	</td>

	<td class=xl45 align=right width=83 style='border-top:none;border-left:none;
	width:62pt' x:num>
		<?
			echo CCurrencyLang::CurrencyFormat($item_price*$arQuantities[$mi]+$nds_val*$arQuantities[$mi], $arOrder["CURRENCY"], false);
			if (empty($arBasket['SET_PARENT_ID']))
			{
				$total_sum += $item_price*$arQuantities[$mi]+$nds_val*$arQuantities[$mi];
			}
		?>
	</td>
	<td class=xl46 ><input size="5" style="border:0px solid #000000;font-size:14px;font-style:bold;text-align:center;" type="text" value="---"></td>
	<td class=xl46 ><input size="5" style="border:0px solid #000000;font-size:14px;font-style:bold;text-align:center;" type="text" value="---"></td>
	<td class=xl53 width=114 style='border-top:none;border-left:none;width:86pt'>---</td>
</tr>
<?
$mi++;
endforeach;

if ($arOrder["DELIVERY_ID"]):
	$basket_tax = CSaleOrderTax::CountTaxes(DoubleVal($arOrder["PRICE_DELIVERY"]), $arTaxList, $arOrder["CURRENCY"]);
	//определяем начальную цену
	$nds_val = $arOrder['DELIVERY_VAT_SUM'];
	$taxRate = $arOrder['DELIVERY_VAT_RATE'] * 100;
	$item_price = DoubleVal($arOrder["PRICE_DELIVERY"]) - $arOrder['DELIVERY_VAT_SUM'];
	?>
<tr class=xl39>
	<td class=xl66 width=213 style='border-top:none;
	width:213px'>Доставка</td>
	<td class=xl40>---</td>
	<td class=xl40 width=40 style='border-top:none;border-left:none;width:30pt'></td>
	<td class=xl41 style='border-top:none;border-left:none'>1</td>
	<td align="right" class=xl42 style='border-top:none;border-left:none'><?=CCurrencyLang::CurrencyFormat($item_price, $arOrder["CURRENCY"], false);?></td>
	<td class=xl42 align=right style='border-top:none;border-left:none' x:num><?=CCurrencyLang::CurrencyFormat($item_price, $arOrder["CURRENCY"], false); $total_price += $item_price;?></td>
	<td class=xl43 style='border-top:none;border-left:none'>&nbsp;</td>
	<td class=xl44 align=right width=43 style='border-top:none;border-left:none;
	width:32pt'><?=($taxRate > 0 || count($arTaxList) > 0) ? $taxRate."%" : "Без НДС";?></td>
	<td class=xl45 align=right width=78 style='border-top:none;border-left:none;
	width:59pt' x:num><?=CCurrencyLang::CurrencyFormat($nds_val, $arOrder["CURRENCY"], false); $total_nds += $nds_val;?></td>
	<td class=xl45 align=right width=83 style='border-top:none;border-left:none;
	width:62pt' x:num><?=CCurrencyLang::CurrencyFormat($nds_val+$item_price, $arOrder["CURRENCY"], false); $total_sum += $nds_val+$item_price?></td>
	<td class=xl46 ><input size="5" style="border:0px solid #000000;font-size:14px;font-style:bold;text-align:center;" type="text" value="---"></td>
	<td class=xl46 ><input size="5" style="border:0px solid #000000;font-size:14px;font-style:bold;text-align:center;" type="text" value="---"></td>
	<td class=xl53 width=114 style='border-top:none;border-left:none;width:86pt'>---</td>
</tr>
<?endif?>
<tr>
	<td class=xl58>Всего к оплате:</td>
	<td class=xl59>&nbsp;</td>
	<td class=xl59>&nbsp;</td>
	<td class=xl59>&nbsp;</td>
	<td class=xl59>&nbsp;</td>
	<td class=xl60 align=right width=80 style='border-top:none;width:60pt' x:num><?=CCurrencyLang::CurrencyFormat($total_price, $arOrder["CURRENCY"], false);?></td>
	<td class=xl61 style='border-left:none'>&nbsp;</td>
	<td class=xl61 style='border-left:none'>&nbsp;</td>
	<td class=xl62 align=right width=78 style='border-top:none;width:59pt' x:num><?=CCurrencyLang::CurrencyFormat($total_nds, $arOrder["CURRENCY"], false);?></td>
	<td class=xl63 align=right width=83 style='border-top:none;width:62pt;white-space:nowrap' x:num><?=CCurrencyLang::CurrencyFormat($total_sum, $arOrder["CURRENCY"], false);?></td>
</tr>
<tr height=26 style='mso-height-source:userset;height:19.5pt'>
	<td height=26 class=xl36 style='height:19.5pt'></td>
	<td colspan=5 class=xl32 style='mso-ignore:colspan'></td>
	<td class=xl36></td>
	<td class=xl32></td>
	<td class=xl36></td>
	<td colspan=2 class=xl32 style='mso-ignore:colspan'></td>
</tr>
<tr valign="top">
	<td colspan=4 class=xl36>Руководитель организации<br> или иное уполномоченное лицо
		_______________ <input size="16" style="border:0px solid #000000;font-size:14px;font-style:bold;" type="text" value="/ <?echo (($arParams["DIRECTOR"] <> '') ? $arParams["DIRECTOR"] : "_______________")?> /"></td>
	<td class=xl36 colspan=2 style='mso-ignore:colspan'></td>
	<td colspan=6 class=xl32 style='mso-ignore:colspan'>Главный бухгалтер<br> или иное уполномоченное лицо _______________ <input size="16" style="border:0px solid #000000;font-size:14px;font-style:bold;" type="text" value="/ <?echo (($arParams["BUHG"] <> '') ? $arParams["BUHG"] : "_______________")?> /"></td>
</tr>
<tr height=0 style='display:none'>
	<td height=0 colspan=11 class=xl32 style='mso-ignore:colspan'></td>
</tr>
<tr height=17 style='height:12.75pt'>
	<td height=17 colspan=11 class=xl32 style='height:12.75pt;mso-ignore:colspan'></td>
</tr>
<tr height=17 style='height:12.75pt'>
	<td height=17 colspan=11 class=xl32 style='height:12.75pt;mso-ignore:colspan'></td>
</tr>
<tr height=22 style='mso-height-source:userset;height:16.5pt'>
	<td height=22 colspan=5 class=xl32 style='height:16.5pt;mso-ignore:colspan'>Индивидуальный предприниматель _____________ / <input size="16" style="border:0px solid #000000;font-size:14px;font-style:bold;" type="text" value="_______________"> /</td>
	<td height=22 colspan=1 class=xl32 style='height:16.5pt;mso-ignore:colspan'></td>
	<td colspan=5 class=xl36><input size="40" style="border:0px solid #000000;font-size:14px;font-style:bold;" type="text" value="_____________________________________"> </td>
</tr>
<tr height=20 style='mso-height-source:userset;height:15.0pt'>
	<td height=20 colspan=5 class=xl33 style='height:15.0pt;mso-ignore:colspan'></td>
	<td height=20 colspan=1 class=xl32 style='height:15.0pt;mso-ignore:colspan'></td>
	<td class=xl33 colspan=5 style='mso-ignore:colspan' align="center">(реквизиты свидетельства о государственной регистрации<br>
индивидуального предпринимателя)</td>
</tr>
<tr height=9 style='mso-height-source:userset;height:6.75pt'>
	<td height=9 colspan=11 class=xl32 style='height:6.75pt;mso-ignore:colspan'></td>
</tr>
</table>
</body>
</html>