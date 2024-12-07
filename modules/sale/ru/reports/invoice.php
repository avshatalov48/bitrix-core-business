<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
/*
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

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=<?=LANG_CHARSET?>">
<title langs="ru">Счет</title>
<style>
<!--
/* Style Definitions */
p.MsoNormal, li.MsoNormal, div.MsoNormal
	{mso-style-parent:"";
	margin:0cm;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:12.0pt;
	font-family:"Times New Roman";
	mso-fareast-font-family:"Times New Roman";}
p
	{margin-right:0cm;
	mso-margin-top-alt:auto;
	mso-margin-bottom-alt:auto;
	margin-left:0cm;
	mso-pagination:widow-orphan;
	font-size:12.0pt;
	font-family:"Times New Roman";
	mso-fareast-font-family:"Times New Roman";}
@page Section1
	{size:595.3pt 841.9pt;
	margin:2.0cm 42.5pt 2.0cm 3.0cm;
	mso-header-margin:35.4pt;
	mso-footer-margin:35.4pt;
	mso-paper-source:0;}
div.Section1
	{page:Section1;}
-->
</style>
</head>

<body bgcolor=white lang=RU style='tab-interval:35.4pt'>

<div class=Section1>

<!-- REPORT BODY -->
<!-- ИЗМЕНИТЕ ЭТОТ ТЕКСТ НА РЕАЛЬНЫЕ ДАННЫЕ ПОСЛЕ КОПИРОВАНИЯ В ПАПКУ /bitrix/admin/reports -->
<p><b>ПОСТАВЩИК:</b>
<?=$arParams["COMPANY_NAME"]?>
<br>
Адрес: <? echo $arParams["COUNTRY"].", ".$arParams["INDEX"].", г. ".$arParams["CITY"].", ".$arParams["ADDRESS"];?><br>
Телефон: <?=$arParams["PHONE"]?><br>
ИНН: <?=$arParams["INN"]?> / КПП: <?=$arParams["KPP"]?><br>
Банковские реквизиты:<br>
р/с <?=$arParams["RSCH"]?> в <?=$arParams["RSCH_BANK"]?> г. <?=$arParams["RSCH_CITY"]?><br>
к/с <?=$arParams["KSCH"]?><br>
БИК <?=$arParams["BIK"]?></p>

<p><b>ЗАКАЗЧИК: </b>
<!-- ИЗМЕНИТЕ КЛЮЧИ МАССИВА $arOrderProps НА РЕАЛЬНЫЕ ПОСЛЕ КОПИРОВАНИЯ В ПАПКУ /bitrix/admin/reports -->
<?
if(empty($arParams))
{
	echo "[".$arOrder["USER_ID"]."] ";
	$db_user = CUser::GetByID($arOrder["USER_ID"]);
	$arUser = $db_user->Fetch();
	echo htmlspecialcharsbx($arUser["NAME"])." ".htmlspecialcharsbx($arUser["LAST_NAME"]);

	if ($arOrderProps["F_INN"] <> '') echo "<br>ИНН: ".$arOrderProps["F_INN"];?>
	<br>Адрес:
	<?
	if ($arOrderProps["F_INDEX"] <> '') echo $arOrderProps["F_INDEX"].",";

	$arVal = CSaleLocation::GetByID($arOrderProps["F_LOCATION"], "ru");
	if($arVal["COUNTRY_NAME"] <> '' && $arVal["CITY_NAME"] <> '')
		echo htmlspecialcharsbx($arVal["COUNTRY_NAME"]." - ".$arVal["CITY_NAME"]);
	elseif($arVal["COUNTRY_NAME"] <> '' || $arVal["CITY_NAME"] <> '')
		echo htmlspecialcharsbx($arVal["COUNTRY_NAME"].$arVal["CITY_NAME"]);

	if ($arOrderProps["F_CITY"] <> '') echo ", г. ".$arOrderProps["F_CITY"];
	if ($arOrderProps["F_ADDRESS"] <> '' && $arOrderProps["F_CITY"] <> '')
		echo ", ".$arOrderProps["F_ADDRESS"];
	elseif($arOrderProps["F_ADDRESS"] <> '')
		echo $arOrderProps["F_ADDRESS"];

	if ($arOrderProps["F_EMAIL"] <> '') echo "<br>E-Mail: ".$arOrderProps["F_EMAIL"];?>
	<br>Контактное лицо: <?echo $arOrderProps["F_NAME"];?>
	<?
	if ($arOrderProps["F_PHONE"] <> '')
		echo "<br>Телефон: ".$arOrderProps["F_PHONE"];

}
else
{
	if($arParams["BUYER_COMPANY_NAME"] <> '')
		echo $arParams["BUYER_COMPANY_NAME"];
	else
		echo $arParams["BUYER_LAST_NAME"]." ".$arParams["BUYER_FIRST_NAME"]." ".$arParams["BUYER_SECOND_NAME"];

	if ($arParams["BUYER_INN"] <> '') echo "<br>ИНН/КПП: ".$arParams["BUYER_INN"]." / ".$arParams["BUYER_KPP"];

	echo "<br>Адрес: ".$arParams["BUYER_COUNTRY"].", ".$arParams["BUYER_INDEX"].", г. ".$arParams["BUYER_CITY"].", ".$arParams["BUYER_ADDRESS"];

	if ($arParams["BUYER_CONTACT"] <> '') echo "<br>Контактное лицо: ".$arParams["BUYER_CONTACT"];

	if ($arParams["BUYER_PHONE"] <> '')
		echo "<br>Телефон: ".$arParams["BUYER_PHONE"];

}
?>
<br>Платежная система:
[<?echo $arOrder["PAY_SYSTEM_ID"];?>]
<?
$arPaySys = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"]);
echo htmlspecialcharsbx($arPaySys["NAME"]);
?>
</p>
<p><b>СЧЕТ N:</b> <?echo $arOrder["ACCOUNT_NUMBER"]?> от <?echo $arOrder["DATE_INSERT_FORMAT"]?></p>

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

//разбрасываем скидку на заказ по товарам
if (floatval($arOrder["DISCOUNT_VALUE"]) > 0)
{
	$arBasketOrder = GetUniformDestribution($arBasketOrder, $arOrder["DISCOUNT_VALUE"], $priceTotal);
}

//налоги
$arTaxList = array();
$db_tax_list = CSaleOrderTax::GetList(array("APPLY_ORDER"=>"ASC"), Array("ORDER_ID" => $ORDER_ID));
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
ClearVars("b_");
//$db_basket = CSaleBasket::GetList(($b="NAME"), ($o="ASC"), array("ORDER_ID"=>$ORDER_ID));
//if ($db_basket->ExtractFields("b_")):
$arCurFormat = CCurrencyLang::GetCurrencyFormat($arOrder["CURRENCY"]);
$currency = preg_replace('/(^|[^&])#/', '${1}', $arCurFormat['FORMAT_STRING']);
	?>
	<table border="0" cellspacing="0" cellpadding="2" width="100%">
		<tr bgcolor="#E2E2E2">
			<td align="center" style="border: 1pt solid #000000; border-right:none;">№</td>
			<td align="center" style="border: 1pt solid #000000; border-right:none;">Предмет счета</td>
			<td nowrap align="center" style="border: 1pt solid #000000; border-right:none;">Кол-во</td>
			<td nowrap align="center" style="border: 1pt solid #000000; border-right:none;">Цена,<?=$currency;?></td>
			<td nowrap align="center" style="border: 1pt solid #000000;">Сумма,<?=$currency;?></td>
		</tr>
		<?
		$n = 1;
		$sum = 0.00;
		$arTax = array("VAT_RATE" => 0, "TAX_RATE" => 0);
		$mi = 0;
		$total_sum = 0;

		foreach ($arBasketOrder as $arBasket)
		{
			$nds_val = 0;
			$taxRate = 0;

			if (floatval($arQuantities[$mi]) <= 0)
				$arQuantities[$mi] = DoubleVal($arBasket["QUANTITY"]);

			$b_AMOUNT = DoubleVal($arBasket["PRICE"]);

			//определяем начальную цену
			$item_price = $b_AMOUNT;

			if(DoubleVal($arBasket["VAT_RATE"]) > 0)
			{
				$bVat = true;
				$nds_val = ($b_AMOUNT - DoubleVal($b_AMOUNT/(1+$arBasket["VAT_RATE"])));
				$item_price = $b_AMOUNT - $nds_val;
				$taxRate = $arBasket["VAT_RATE"]*100;
			}
			elseif(!$bUseVat)
			{
				$basket_tax = CSaleOrderTax::CountTaxes($b_AMOUNT, $arTaxList, $arOrder["CURRENCY"]);
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
			if (empty($arBasket['SET_PARENT_ID']))
			{
				$total_nds += $nds_val*$arQuantities[$mi];
			}

			?>
			<tr valign="top">
				<td bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo $n++ ?>
				</td>
				<td bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo $arBasket["NAME"]; ?>
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
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo Bitrix\Sale\BasketItem::formatQuantity($arQuantities[$mi]); ?>
				</td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo CCurrencyLang::CurrencyFormat($arBasket["PRICE"], $arOrder["CURRENCY"], false) ?>
				</td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-top:none;">
					<?
						$sum = $arBasket["PRICE"] * $arQuantities[$mi];
						echo CCurrencyLang::CurrencyFormat($sum, $arOrder["CURRENCY"], false);
					?>
				</td>
			</tr>
			<?
			if (empty($arBasket['SET_PARENT_ID']))
			{
				$total_sum += $arBasket["PRICE"]*$arQuantities[$mi];
			}
			$mi++;
		}//endforeach
		?>

		<?if (False && DoubleVal($arOrder["DISCOUNT_VALUE"])>0):?>
			<tr>
				<td bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo $n++?>
				</td>
				<td bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					Скидка
				</td>
				<td valign="top" align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">1 </td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo CCurrencyLang::CurrencyFormat($arOrder["DISCOUNT_VALUE"], $arOrder["CURRENCY"], false);?>
				</td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-top:none;">
					<?echo CCurrencyLang::CurrencyFormat($arOrder["DISCOUNT_VALUE"], $arOrder["CURRENCY"], false);?>
				</td>
			</tr>
		<?endif?>



		<?if ($arOrder["DELIVERY_ID"]):?>
			<tr>
				<td bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?echo $n?>
				</td>
				<td bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					Доставка <?
					$deliveryId = \CSaleDelivery::getIdByCode($arOrder['DELIVERY_ID']);

					if($deliveryId > 0)
					{
						if($delivery = \Bitrix\Sale\Delivery\Services\Manager::getObjectById($deliveryId))
						{
							echo "[".htmlspecialcharsbx($delivery->getNameWithParent())."]";
						}
					}

					$total_nds += $arOrder["DELIVERY_VAT_SUM"];
					$total_sum += $arOrder["PRICE_DELIVERY"];
					?>
				</td>
				<td valign="top" align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">1 </td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?= CCurrencyLang::CurrencyFormat($arOrder["PRICE_DELIVERY"], $arOrder["CURRENCY"], false); ?>
				</td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-top:none;">
					<?echo CCurrencyLang::CurrencyFormat($arOrder["PRICE_DELIVERY"], $arOrder["CURRENCY"], false);?>
				</td>
			</tr>
		<?endif?>

		<?
		$db_tax_list = CSaleOrderTax::GetList(array("APPLY_ORDER"=>"ASC"), Array("ORDER_ID"=>$ORDER_ID));
		while ($ar_tax_list = $db_tax_list->Fetch())
		{
			?>
			<tr>
				<td align="right" bgcolor="#ffffff" colspan="4" style="border: 1pt solid #000000; border-right:none; border-top:none;">
					<?
					if ($ar_tax_list["IS_IN_PRICE"]=="Y")
					{
						echo "В том числе ";
					}
					echo htmlspecialcharsbx($ar_tax_list["TAX_NAME"]);
					if ($ar_tax_list["IS_PERCENT"]=="Y")
					{
						echo " (".(int)$ar_tax_list["VALUE"]."%)";
					}
					?>:
				</td>
				<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-top:none;">
					<?echo CCurrencyLang::CurrencyFormat($total_nds, $arOrder["CURRENCY"], false);?>
				</td>
			</tr>
			<?
		}
		?>
		<tr>
			<td align="right" bgcolor="#ffffff" colspan="4" style="border: 1pt solid #000000; border-right:none; border-top:none;">Итого:</td>
			<td align="right" bgcolor="#ffffff" style="border: 1pt solid #000000; border-top:none;">
				<?=CCurrencyLang::CurrencyFormat($total_sum, $arOrder["CURRENCY"], false);?>
			</td>
		</tr>
	</table>
<?//endif?>
<p><b>Итого к оплате:</b>
	<?
	if ($arOrder["CURRENCY"]=="RUR" || $arOrder["CURRENCY"]=="RUB")
	{
		echo Number2Word_Rus($arOrder["PRICE"]);
	}
	else
	{
		echo SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"]);
	}
	?>.</p>
<?
	if ($arOrder['CURRENCY'] === 'UAH')
		$contextCurrency = 'гривнах';
	else
		$contextCurrency = 'рублях';
?>
<p><font size="2">В случае непоступления средств на расчетный счет продавца в течение пяти
банковских дней со дня выписки счета, продавец оставляет за собой право
пересмотреть отпускную цену товара в <?=$contextCurrency;?> пропорционально изменению курса доллара
и выставить счет на доплату.<br><br>
В платежном поручении обязательно указать - "Оплата по счету № <?echo $arOrder["ACCOUNT_NUMBER"]?> от <?echo $arOrder["DATE_INSERT_FORMAT"] ?>".<br><br>
Получение товара только после прихода денег на расчетный счет компании.
</font></p>
<!-- END REPORT BODY -->

<p>&nbsp;</p>
<table border=0 cellspacing=0 cellpadding=0 width="100%">
<tr>
<td width="20%">
<p class=MsoNormal>Руководитель организации:</p>
</td>
<td width="80%">
<p class=MsoNormal>_______________ <input size="55" style="border:0px solid #000000;font-size:14px;font-style:bold;" type="text" value="/ <?echo (($arParams["DIRECTOR"] <> '') ? $arParams["DIRECTOR"] : "______________________________")?> /"></p>
</td>
</tr>
<tr>
<td>
<p class=MsoNormal>&nbsp;</p>
</td>
<td>
<p class=MsoNormal>&nbsp;</p>
</td>
</tr>
<tr>
<td>
<p class=MsoNormal>&nbsp;</p>
</td>
<td>
<p class=MsoNormal>&nbsp;</p>
</td>
</tr>
<tr>
<td>
<p class=MsoNormal>Гл. бухгалтер:</p>
</td>
<td>
<p class=MsoNormal>_______________ <input size="45" style="border:0px solid #000000;font-size:14px;font-style:bold;" type="text" value="/ <?echo (($arParams["BUHG"] <> '') ? $arParams["BUHG"] : "______________________________")?> /"></p>
</td>
</tr>
</table>
</div>
</body>
</html>