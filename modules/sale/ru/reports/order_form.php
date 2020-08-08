<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=<?=LANG_CHARSET?>">
<title langs="ru">Бланк заказа</title>
<style>
<!--
.header{font-size:17px; font-family:Tahoma;padding-left:8px;}
.sub_header{font-size:13px; font-family:Tahoma;padding-left:8px;}
.date{font-style:italic; font-family:Tahoma;padding-left:8px;}
.number{font-size:24px;font-family:Tahoma;font-style:italic;padding-left:8px;}
.user{font-size:12px;font-family:Tahoma;font-weight:bold;padding-left:8px;}
.summa{font-size:12px;font-family:Tahoma;font-weight:bold;padding-left:15px;}

table.blank {
	border-collapse: collapse;
	width: 585px;
}
table.blank td {
	border:0.5pt solid windowtext;
}
-->
</style>
</head>

<body bgcolor=white lang=RU style='tab-interval:35.4pt'>
<?
$page = intval($page);
if ($page<=0) $page = 1;
?>
<table height="920" align="center" border="0" cellpadding="0" cellspacing="0">
	<tr valign="top">
		<td colspan="3">
			<!-- Верхний колонтитул height="109" -->
		</td>
	</tr>
	<tr valign="top">
		<td colspan="3">
			<table cellpadding="0" cellspacing="0" border="0" width="595" align="center">
				<tr><td><br><br></td></tr>
				<tr>
					<td width="180"><font class="header">ТОВАРНЫЙ ЧЕК №</font></td>
					<td style="border-bottom : 1px solid Black;" nowrap>
						<font class="number"><?echo $arOrder["ACCOUNT_NUMBER"];?></font>
						<!--- <input size="30" style="border:1px;font-size:24px;font-style:italic;" type="text" value="<?echo $page;?>">-->
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td width="180"><font class="sub_header">ДАТА:</font></td>
					<td style="border-bottom : 1px solid Black;">
						<input class="date" size="30" style="border:0px solid #000000;" type="text" value="<?echo $arOrder["DATE_INSERT_FORMAT"];?>">
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td width="180"><font class="sub_header">КОМУ:</font></td>
					<td style="border-bottom : 1px solid Black;">
						<?if(empty($arParams))
						{
							$userName = $arOrderProps["F_NAME"];
						}
						else
						{
							if($arParams["BUYER_COMPANY_NAME"] <> '')
								$userName = $arParams["BUYER_COMPANY_NAME"];
							else
								$userName = $arParams["BUYER_LAST_NAME"]." ".$arParams["BUYER_FIRST_NAME"]." ".$arParams["BUYER_SECOND_NAME"];
						}?>
						<input class="user" size="50" style="border:0px solid #000000;" type="text" value="<?=$userName?>	">
					</td>
					<td>
					</td>
				</tr>
				<tr><td><br></td></tr>
			</table>

			<br>
			<?
			if (count($arBasketIDs)>0)
			{
				$arCurFormat = CCurrencyLang::GetCurrencyFormat($arOrder["CURRENCY"]);
				$currency = preg_replace('/(^|[^&])#/', '${1}', $arCurFormat['FORMAT_STRING']);
				?>
				<table class="blank">
					<tr>
						<td align="center">№</td>
						<td align="center">Наименование</td>
						<td align="center">Количество</td>
						<td align="center">Цена,<?=$currency;?></td>
						<td align="center">Cумма,<?=$currency;?></td>
					</tr>
					<?
					$priceTotal = 0;
					$bUseVat = false;
					$arBasketOrder = array();
					for ($i = 0, $countBasketIds = count($arBasketIDs); $i < $countBasketIds; $i++)
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


					$i = 0;
					$total_sum = 0;
					foreach ($arBasketOrder as $arBasket):
						$nds_val = 0;
						$taxRate = 0;

						if (floatval($arQuantities[$i]) <= 0)
							$arQuantities[$i] = DoubleVal($arBasket["QUANTITY"]);

						$b_AMOUNT = DoubleVal($arBasket["PRICE"]);

						//определяем начальную цену
						$item_price = $b_AMOUNT;

						if(DoubleVal($arBasket["VAT_RATE"]) > 0)
						{
							$nds_val = ($b_AMOUNT - DoubleVal($b_AMOUNT/(1+$arBasket["VAT_RATE"])));
							$item_price = $b_AMOUNT - $nds_val;
							$taxRate = $arBasket["VAT_RATE"]*100;
						}
						elseif(!$bUseVat)
						{
							$basket_tax = CSaleOrderTax::CountTaxes($b_AMOUNT*$arQuantities[$i], $arTaxList, $arOrder["CURRENCY"]);
							for ($mi = 0, $countTaxList = count($arTaxList); $mi < $countTaxList; $mi++)
							{
								if ($arTaxList[$mi]["IS_IN_PRICE"] == "Y")
								{
									$item_price -= $arTaxList[$mi]["TAX_VAL"];
								}
								$nds_val += DoubleVal($arTaxList[$mi]["TAX_VAL"]);
								$taxRate += ($arTaxList[$mi]["VALUE"]);
							}
						}
					?>
					<tr>
						<td><?echo $i+1;?></td>
						<td>
							<?echo htmlspecialcharsbx($arBasket["NAME"]);?>
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
						<td align="center"><?echo Bitrix\Sale\BasketItem::formatQuantity($arQuantities[$i]) ?></td>
						<td align="right" nowrap><?=CCurrencyLang::CurrencyFormat($arBasket["PRICE"], $arOrder["CURRENCY"], false);?></td>
						<td align="right" nowrap><?=CCurrencyLang::CurrencyFormat($arBasket["PRICE"]*$arQuantities[$i], $arOrder["CURRENCY"], false);?></td>
					</tr>
					<?
					if (empty($arBasket['SET_PARENT_ID']))
					{
						$total_sum += $arBasket["PRICE"]*$arQuantities[$i];
						$total_nds += $nds_val*$arQuantities[$i];
					}
					$i++;
					endforeach;
					?>

					<tr>
						<td align="right" colspan="4">
							Сумма:
						</td>
						<td align="right" nowrap>
							<?=CCurrencyLang::CurrencyFormat($total_sum, $arOrder["CURRENCY"], false);?>
						</td>
					</tr>

					<?
					if ($bUseVat || $arOrder['DELIVERY_VAT_RATE'] <= 0)
					{
						$db_tax_list = CSaleOrderTax::GetList(array("APPLY_ORDER"=>"ASC"), Array("ORDER_ID"=>$ORDER_ID));
						while ($ar_tax_list = $db_tax_list->Fetch())
						{
							?>
							<tr>
								<td align="right" colspan="4">
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
								<td align="right" nowrap>
									<?=CCurrencyLang::CurrencyFormat($total_nds, $arOrder["CURRENCY"], false);?>
								</td>
							</tr>
							<?
						}
					}
					?>

					<tr>
						<td align="right" colspan="4">
							Итого (без стоимости доставки):
						</td>
						<td align="right" nowrap>
							<?=CCurrencyLang::CurrencyFormat($total_sum, $arOrder["CURRENCY"], false);?>
						</td>
					</tr>
				</table>
				<?
			}
			?>
			<br>
		</td>
	</tr>
</table>

</body>
</html>