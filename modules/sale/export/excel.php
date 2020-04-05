<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

IncludeModuleLangFile(__FILE__);

if (!isset($arFilter) || !is_array($arFilter))
	die("Wrong use 1");
if (!isset($arSelectFields) || !is_array($arSelectFields))
	die("Wrong use 2");

$dbOrderList = CSaleOrder::GetList(
		array($by => $order),
		$arFilter,
		false,
		false,
		$arSelectFields
	);

ob_start();
?>
<table border="1">
	<tr>
		<?
		for ($i = 0; $i < count($arShownFieldsParams); $i++)
		{
			switch ($arShownFieldsParams[$i]["KEY"])
			{
				case "ID":
					echo "<td>".$arShownFieldsParams[$i]["COLUMN_NAME"]."</td>";
					echo "<td>".GetMessage("SEXE_ORDER_DATE")."</td>";
					break;
				case "PAYED":
					echo "<td>".$arShownFieldsParams[$i]["COLUMN_NAME"]."</td>";
					echo "<td>".GetMessage("SEXE_PAY_DATE")."</td>";
					break;
				default:
					echo "<td>".$arShownFieldsParams[$i]["COLUMN_NAME"]."</td>";
					break;
			}
		}
		?>
	</tr>
	<?
	while ($arOrder = $dbOrderList->Fetch()):
		?>
		<tr>
			<?
			for ($i = 0; $i < count($arShownFieldsParams); $i++)
			{
				?>
				<td>
					<?
					switch ($arShownFieldsParams[$i]["KEY"])
					{
						case "ID":
							echo $arOrder["ID"]."</td><td>".$arOrder["DATE_INSERT"];
							break;
						case "LID":
							if (!isset($LOCAL_SITE_LIST_CACHE[$arOrder["LID"]])
								|| !is_array($LOCAL_SITE_LIST_CACHE[$arOrder["LID"]]))
							{
								$dbSite = CSite::GetByID($arOrder["LID"]);
								if ($arSite = $dbSite->Fetch())
									$LOCAL_SITE_LIST_CACHE[$arOrder["LID"]] = htmlspecialcharsEx($arSite["NAME"]);
							}
							echo "[".$arOrder["LID"]."] ".$LOCAL_SITE_LIST_CACHE[$arOrder["LID"]];
							break;
						case "PERSON_TYPE":
							if (!isset($LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]])
								|| !is_array($LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]]))
							{
								if ($arPersonType = CSalePersonType::GetByID($arOrder["PERSON_TYPE_ID"]))
									$LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]] = htmlspecialcharsEx($arPersonType["NAME"]);
							}
							echo "[".$arOrder["PERSON_TYPE_ID"]."] ".$LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]];
							break;
						case "PAYED":
							echo (($arOrder["PAYED"] == "Y") ? GetMessage("SEXE_YES") : GetMessage("SEXE_NO"))."</td><td>";
							echo $arOrder["DATE_PAYED"];
							break;
						case "CANCELED":
							echo (($arOrder["CANCELED"] == "Y") ? GetMessage("SEXE_YES") : GetMessage("SEXE_NO"))." ";
							echo $arOrder["DATE_CANCELED"];
							break;
						case "STATUS":
							if (!isset($LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]])
								|| !is_array($LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]]))
							{
								if ($arStatus = CSaleStatus::GetByID($arOrder["STATUS_ID"]))
									$LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]] = htmlspecialcharsEx($arStatus["NAME"]);
							}

							echo "[".$arOrder["STATUS_ID"]."] ".$LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]]." ";
							echo $arOrder["DATE_STATUS"];
							break;
						case "PAY_VOUCHER_NUM":
							echo $arOrder["PAY_VOUCHER_NUM"];
							break;
						case "PAY_VOUCHER_DATE":
							echo $arOrder["PAY_VOUCHER_DATE"];
							break;
						case "DELIVERY_DOC_NUM":
							echo $arOrder["DELIVERY_DOC_NUM"];
							break;
						case "DELIVERY_DOC_DATE":
							echo $arOrder["DELIVERY_DOC_DATE"];
							break;
						case "PRICE_DELIVERY":
							echo SaleFormatCurrency($arOrder["PRICE_DELIVERY"], $arOrder["CURRENCY"]);
							break;
						case "ALLOW_DELIVERY":
							echo (($arOrder["ALLOW_DELIVERY"] == "Y") ? GetMessage("SEXE_YES") : GetMessage("SEXE_NO"))." ";
							echo $arOrder["DATE_ALLOW_DELIVERY"];
							break;
						case "PRICE":
							echo SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"]);
							break;
						case "SUM_PAID":
							echo SaleFormatCurrency($arOrder["SUM_PAID"], $arOrder["CURRENCY"]);
							break;
						case "USER":
							if (!isset($LOCAL_PAYED_USER_CACHE[$arOrder["USER_ID"]])
								|| !is_array($LOCAL_PAYED_USER_CACHE[$arOrder["USER_ID"]]))
							{
								$dbUser = CUser::GetByID($arOrder["USER_ID"]);
								if ($arUser = $dbUser->Fetch())
									$LOCAL_PAYED_USER_CACHE[$arOrder["USER_ID"]] = htmlspecialcharsEx($arUser["NAME"].((strlen($arUser["NAME"])<=0 || strlen($arUser["LAST_NAME"])<=0) ? "" : " ").$arUser["LAST_NAME"]." (".$arUser["LOGIN"].")");
							}
							echo "[".$arOrder["USER_ID"]."] ";
							echo $LOCAL_PAYED_USER_CACHE[$arOrder["USER_ID"]];
							break;
						case "PAY_SYSTEM":
							if (IntVal($arOrder["PAY_SYSTEM_ID"]) > 0)
							{
								if (!isset($LOCAL_PAY_SYSTEM_CACHE[$arOrder["PAY_SYSTEM_ID"]])
									|| !is_array($LOCAL_PAY_SYSTEM_CACHE[$arOrder["PAY_SYSTEM_ID"]]))
								{
									if ($arPaySys = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"]))
										$LOCAL_PAY_SYSTEM_CACHE[$arOrder["PAY_SYSTEM_ID"]] = htmlspecialcharsEx($arPaySys["NAME"]);
								}

								echo "[".$arOrder["PAY_SYSTEM_ID"]."] ".$LOCAL_PAY_SYSTEM_CACHE[$arOrder["PAY_SYSTEM_ID"]];
							}
							break;
						case "DELIVERY":
							if (IntVal($arOrder["DELIVERY_ID"]) > 0)
							{
								if (!isset($LOCAL_DELIVERY_CACHE[$arOrder["DELIVERY_ID"]])
									|| !is_array($LOCAL_DELIVERY_CACHE[$arOrder["DELIVERY_ID"]]))
								{
									if ($arDelivery = CSaleDelivery::GetByID($arOrder["DELIVERY_ID"]))
										$LOCAL_DELIVERY_CACHE[$arOrder["DELIVERY_ID"]] = htmlspecialcharsEx($arDelivery["NAME"]);
								}

								echo "[".$arOrder["DELIVERY_ID"]."] ".$LOCAL_DELIVERY_CACHE[$arOrder["DELIVERY_ID"]];
							}
							break;
						case "DATE_UPDATE":
							echo $arOrder["DATE_UPDATE"];
							break;
						case "PS_STATUS":
							if ($arOrder["PS_STATUS"] == "Y")
								echo GetMessage("SEXE_SUCCESS")." ".$arOrder["PS_RESPONSE_DATE"];
							elseif ($arOrder["PS_STATUS"] == "N")
								echo GetMessage("SEXE_UNSUCCESS")." ".$arOrder["PS_RESPONSE_DATE"];
							else
								echo GetMessage("SEXE_NONE");
							break;
						case "PS_SUM":
							echo SaleFormatCurrency($arOrder["PS_SUM"], $arOrder["PS_CURRENCY"]);
							break;
						case "TAX_VALUE":
							echo SaleFormatCurrency($arOrder["TAX_VALUE"], $arOrder["CURRENCY"]);
							break;
						case "BASKET":
							$bNeedLine = False;
							$dbItemsList = CSaleBasket::GetList(
									array("NAME" => "ASC"),
									array("ORDER_ID" => $arOrder["ID"])
								);
							while ($arItem = $dbItemsList->Fetch())
							{
								if ($bNeedLine)
									echo "\n";
								$bNeedLine = True;

								echo "[".$arItem["PRODUCT_ID"]."] ";
								echo $arItem["NAME"];
								echo " (".$arItem["QUANTITY"].GetMessage("SEXE_SHT");
							}
							break;
					}
					?>
				</td>
				<?
			}
			?>
		</tr>
		<?
	endwhile;
	?>
</table>

<?

$content = ob_get_contents();
ob_end_clean();

header('Pragma: public');
header('Cache-control: private');
header('Accept-Ranges: bytes');
header('Content-Length: '.strlen($content));
header("Content-Type: application/vnd.ms-excel");
header('Content-Disposition: attachment; filename=excel_dump.xls');

echo $content;
?>