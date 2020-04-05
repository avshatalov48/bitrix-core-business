<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
/*
Copy this file to /bitrix/admin/reports folder and change it as you wish

$ORDER_ID - ID of current order

$arOrder - array of order attributes (ID, delivery, price, date create, etc.)
The following code:
print_r($arOrder);
will show the content of $arOrder array.

$arOrderProps - array of order properties of the following structure:
array(
	"mnemonic code (or ID if mnemonic code is empty) of property" => "property value"
	)
*/
?><!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Invoice</title>
<style>
.shopsTitle { font-family: Verdana, Arial, sans-serif; font-size: 18px; color: #727272; font-weight: bold; }
.mainText { font-family: Verdana, Arial, sans-serif; font-size: 12px; }
.tableheadrow { background-color: #C9C9C9; }
.tableheadcol { font-family: Verdana, Arial, sans-serif; font-size: 10px; color: #ffffff; font-weight: bold; }
.tablebodyrow { background-color: #F0F1F1; }
.tablebodycol { font-family: Verdana, Arial, sans-serif; font-size: 10px; color: #000000; }
.smallText { font-family: Verdana, Arial, sans-serif; font-size: 10px; }
</style>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">

<table border="0" width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td>
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td class="shopsTitle">
						<?/* NOTE: You should change this text to actual one 
						after copying this file info /bitrix/admin/reports folder*/?>
						<?=$arParams["COMPANY_NAME"]?><br>
						<?=$arParams["CITY"].", ".$arParams["ADDRESS"];?><br>
						<?=$arParams["COUNTRY"]?><br>
						<?=$arParams["INDEX"]?><br>
						<?=$arParams["PHINE"]?>
					</td>
					<td class="shopsTitle" align="right">
						&nbsp;
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td colspan="2"><hr width="100%" height="1"></td>
				</tr>
				<tr>
					<td valign="top">
						<table width="100%" border="0" cellspacing="0" cellpadding="2">
							<tr>
								<td class="mainText"><b>SOLD TO:</b></td>
							</tr>
							<tr>
								<td class="mainText">
								<?
								if(strlen($arParams["BUYER_COMPANY_NAME"]) > 0)
									echo $arParams["BUYER_COMPANY_NAME"];
								else
									echo $arParams["BUYER_LAST_NAME"]." ".$arParams["BUYER_FIRST_NAME"]." ".$arParams["BUYER_SECOND_NAME"];
								
								
								
								echo "<br>".$arParams["BUYER_COUNTRY"].", ".$arParams["BUYER_CITY"];
								echo "<br>".$arParams["BUYER_ADDRESS"];
								echo "<br>".$arParams["BUYER_INDEX"];
								
								if (strlen($arParams["BUYER_CONTACT"])>0) echo "<br>Contact person: ".$arParams["BUYER_CONTACT"];?>
								</td>
							</tr>
							<tr>
								<td>&nbsp; </td>
							</tr>
							<tr>
								<td class="mainText"><a href="mailto:<?echo $arOrderProps["F_EMAIL"];?>"><u><?echo $arOrderProps["F_EMAIL"];?></u></a></td>
							</tr>
						</table>
					</td>
					<td valign="top">
						<table width="100%" border="0" cellspacing="0" cellpadding="2">
							<tr>
								<td class="mainText"><b>SHIP TO:</b></td>
							</tr>
							<tr>
								<td class="mainText">
									<?
								if(strlen($arParams["BUYER_COMPANY_NAME"]) > 0)
									echo $arParams["BUYER_COMPANY_NAME"];
								else
									echo $arParams["BUYER_LAST_NAME"]." ".$arParams["BUYER_FIRST_NAME"]." ".$arParams["BUYER_SECOND_NAME"];
								
								
								
								echo "<br>".$arParams["BUYER_COUNTRY"].", ".$arParams["BUYER_CITY"];
								echo "<br>".$arParams["BUYER_ADDRESS"];
								echo "<br>".$arParams["BUYER_INDEX"];
								
								if (strlen($arParams["BUYER_CONTACT"])>0) echo "<br>Contact person: ".$arParams["BUYER_CONTACT"];?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>&nbsp; </td>
	</tr>
	<tr>
		<td>
			<table border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="mainText"><b>Payment Method:</b></td>
					<td class="mainText">
						[<?echo $arOrder["PAY_SYSTEM_ID"];?>]
						<?
						$arPaySys = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"]);
						echo htmlspecialcharsbx($arPaySys["NAME"]);
						?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>&nbsp; </td>
	</tr>
	<tr>
		<td>
			<?
			$bUseVat = false;
			$arBasketOrder = array();
			for ($i = 0; $i < count($arBasketIDs); $i++)
			{
				$arBasketTmp = CSaleBasket::GetByID($arBasketIDs[$i]);

				if (floatval($arBasketTmp["VAT_RATE"]) > 0 )
					$bUseVat = true;

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


			$arTaxList = array();
			$db_tax_list = CSaleOrderTax::GetList(array("APPLY_ORDER"=>"ASC"), Array("ORDER_ID"=>$ORDER_ID));
			while ($ar_tax_list = $db_tax_list->Fetch())
			{
				$arTaxList[] = $ar_tax_list;
			}
			$bVat = false;
			//ClearVars("b_");
			//$db_basket = CSaleBasket::GetList(($b="NAME"), ($o="ASC"), array("ORDER_ID"=>$ORDER_ID));
			if (count($arBasketOrder) > 0)
			{
				?>
				<table border="0" width="100%" cellspacing="0" cellpadding="2">
					<tr class="tableheadrow">
						<td class="tableheadcol" colspan="2">Products</td>
						<td class="tableheadcol" align="right">Price</td>
						<td class="tableheadcol" align="right">Tax</td>
						<td class="tableheadcol" align="right">Price (inc)</td>
						<td class="tableheadcol" align="right">Total</td>
					</tr>
					<?
					$sum = 0.00;
					$total_nds = 0;
					$mi = 0;
					//do
					foreach ($arBasketOrder as $arBasket)
					{
						if (floatval($arQuantities[$mi]) <= 0)
							$arQuantities[$mi] = DoubleVal($arBasket["QUANTITY"]);

						$b_AMOUNT = DoubleVal($arBasket["PRICE"]);
						$item_price = $b_AMOUNT;
						$nds_val = 0;
						$taxRate = 0;
						if(DoubleVal($arBasket["VAT_RATE"]) > 0)
						{
							$nds_val = $b_AMOUNT - DoubleVal($b_AMOUNT/(1+$arBasket["VAT_RATE"]));
							$item_price = $b_AMOUNT - $nds_val;
							$taxRate = $arBasket["VAT_RATE"]*100;
							$bVat = true;
						}
						elseif(!$bUseVat)
						{						
							$basket_tax = CSaleOrderTax::CountTaxes($b_AMOUNT, $arTaxList, $arOrder["CURRENCY"]);
							for ($i = 0; $i < count($arTaxList); $i++)
								if ($arTaxList[$i]["IS_IN_PRICE"] == "Y")
									$item_price -= $arTaxList[$i]["TAX_VAL"];
							
							$nds_val = DoubleVal($iNds > -1? $arTaxList[$iNds]["TAX_VAL"] : 0);
							$taxRate = ($iNds > -1? $arTaxList[$iNds]["VALUE"] : 0);
						}

						$total_nds += $nds_val*$arQuantities[$mi];
						?>
						<tr class="tablebodyrow">
							<td class="tablebodycol" valign="top" align="right">
								<?echo Bitrix\Sale\BasketItem::formatQuantity($arQuantities[$mi]); ?>&nbsp;x
							</td>
							<td class="tablebodycol" valign="top">
								<?echo "[".$arBasket["PRODUCT_ID"]."] ".$arBasket["NAME"]; ?>
							</td>
							<td class="tablebodycol" align="right" valign="top">
								<b><?echo SaleFormatCurrency($item_price, $arOrder["CURRENCY"]);?></b>
							</td>
							<td class="tablebodycol" align="right" valign="top">
								<?echo SaleFormatCurrency($nds_val, $arOrder["CURRENCY"]);?>
							</td>
							<td class="tablebodycol" align="right" valign="top">
								<b><?echo SaleFormatCurrency($nds_val+$item_price, $arOrder["CURRENCY"]);?></b>
							</td>
							<td class="tablebodycol" align="right" valign="top">
								<b><?echo SaleFormatCurrency(($item_price+$nds_val)*$arQuantities[$mi], $arOrder["CURRENCY"]);
								$sum += ($item_price+$nds_val)*$arQuantities[$mi];?></b>
							</td>
						</tr>
						<?
						$mi++;
					}
					//while ($db_basket->ExtractFields("b_"));
					?>
					<tr>
						<td align="right" colspan="8">
							<table border="0" cellspacing="0" cellpadding="2">
								<tr>
									<td align="right" class="smallText">Sub-Total:</td>
									<td align="right" class="smallText">
										<?echo SaleFormatCurrency($sum, $arOrder["CURRENCY"]) ?>
									</td>
								</tr>

								<?
								$db_tax_list = CSaleOrderTax::GetList(array("APPLY_ORDER"=>"ASC"), Array("ORDER_ID"=>$ORDER_ID));
								while ($ar_tax_list = $db_tax_list->Fetch())
								{
									?>
									<tr>
										<td align="right" class="smallText">
											<?
											if ($ar_tax_list["IS_IN_PRICE"]=="Y")
											{
												echo "Included ";
											}
											echo htmlspecialcharsbx($ar_tax_list["TAX_NAME"]);
											if ($ar_tax_list["IS_PERCENT"]=="Y")
											{
												echo " (".(int)$ar_tax_list["VALUE"]."%)";
											}
											?>:

											<?
											$total_nds += $arOrder['DELIVERY_VAT_SUM'];
											?>
										</td>
										<td align="right" class="smallText">
											<?=SaleFormatCurrency($total_nds, $arOrder["CURRENCY"])?>
										</td>
									</tr>
									<?
								}
								?>

								<?if (floatval($arOrder["DISCOUNT_VALUE"]) > 0):
									$sum -= $arOrder["DISCOUNT_VALUE"];
								?>
									<tr>
										<td align="right" class="smallText">Discount:</td>
										<td align="right" class="smallText">
											<?echo SaleFormatCurrency($arOrder["DISCOUNT_VALUE"], $arOrder["CURRENCY"]) ?>
										</td>
									</tr>
								<?endif?>

								<?if ($arOrder["DELIVERY_ID"]):?>
									<tr>
										<td align="right" class="smallText">
											Delivery 
											([<?echo $arOrder["DELIVERY_ID"];?>]
											<?
											$arDeliv = CSaleDelivery::GetByID($arOrder["DELIVERY_ID"]);
											echo htmlspecialcharsbx($arDeliv["NAME"]);
											?>):
										</td>
										<td align="right" class="smallText">
											<?echo SaleFormatCurrency($arOrder["PRICE_DELIVERY"], $arOrder["CURRENCY"]) ?>
										</td>
									</tr>
								<?endif;?>
								<tr>
									<td align="right" class="smallText">Total:</td>
									<td align="right" class="smallText">
										<b><?echo SaleFormatCurrency($sum+$arOrder["PRICE_DELIVERY"], $arOrder["CURRENCY"]) ?></b>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<?
			}
			?>
		</td>
	</tr>
</table>

<br>
</body>
</html>