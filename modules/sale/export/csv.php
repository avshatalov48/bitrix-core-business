<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

IncludeModuleLangFile(__FILE__);

if (!isset($arFilter) || !is_array($arFilter))
	die("Wrong use 1");
if (!isset($arSelectFields) || !is_array($arSelectFields))
	die("Wrong use 2");

$fieldsSeparator = ",";


$filter = array(
	'filter' => $arFilter,
	'select' => $arSelectFields,
	'runtime' => $runtimeFields
);

if (!empty($by))
{
	$order = (!empty($order) && $order == "DESC" ? "DESC" : "ASC");
	$filter['order'] = array($by => $order);
}

$dbOrderList = \Bitrix\Sale\Internals\OrderTable::getList($filter);
ob_start();

for ($i = 0, $max = count($arShownFieldsParams); $i < $max; $i++)
{
	switch ($arShownFieldsParams[$i]["KEY"])
	{
		case "ID":
			echo "\"".str_replace("\"", "\"\"", $arShownFieldsParams[$i]["COLUMN_NAME"])."\",";
			echo "\"".str_replace("\"", "\"\"", GetMessage("SEXC_ORDER_DATE"))."\"";
			break;
		case "PAYED":
			echo "\"".str_replace("\"", "\"\"", $arShownFieldsParams[$i]["COLUMN_NAME"])."\",";
			echo "\"".str_replace("\"", "\"\"", GetMessage("SEXC_PAY_DATE"))."\"";
			break;
		default:
			echo "\"".str_replace("\"", "\"\"", $arShownFieldsParams[$i]["COLUMN_NAME"])."\"";
			break;
	}

	if ($i < count($arShownFieldsParams) - 1)
		echo $fieldsSeparator;
}
echo "\n";

while ($arOrder = $dbOrderList->fetch())
{
	for ($i = 0, $max = count($arShownFieldsParams); $i < $max; $i++)
	{
		switch ($arShownFieldsParams[$i]["KEY"])
		{
			case "ID":
				echo "\"".str_replace("\"", "\"\"", $arOrder["ID"])."\"".$fieldsSeparator;
				echo "\"".str_replace("\"", "\"\"", $arOrder["DATE_INSERT"])."\"";
				break;
			case "LID":
				if (!isset($LOCAL_SITE_LIST_CACHE[$arOrder["LID"]])
					|| !is_array($LOCAL_SITE_LIST_CACHE[$arOrder["LID"]]))
				{
					$dbSite = CSite::GetByID($arOrder["LID"]);
					if ($arSite = $dbSite->Fetch())
						$LOCAL_SITE_LIST_CACHE[$arOrder["LID"]] = htmlspecialcharsEx($arSite["NAME"]);
				}
				$printValue = "[".$arOrder["LID"]."] ".$LOCAL_SITE_LIST_CACHE[$arOrder["LID"]];
				echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
				break;
			case "PERSON_TYPE":
				if (!isset($LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]])
					|| !is_array($LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]]))
				{
					if ($arPersonType = CSalePersonType::GetByID($arOrder["PERSON_TYPE_ID"]))
						$LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]] = htmlspecialcharsEx($arPersonType["NAME"]);
				}
				$printValue = "[".$arOrder["PERSON_TYPE_ID"]."] ".$LOCAL_PERSON_TYPE_CACHE[$arOrder["PERSON_TYPE_ID"]];
				echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
				break;
			case "PAYED":
				echo "\"".str_replace("\"", "\"\"", (($arOrder["PAYED"] == "Y") ? GetMessage("SEXC_YES") : GetMessage("SEXC_NO")))."\"".$fieldsSeparator;
				echo "\"".str_replace("\"", "\"\"", $arOrder["DATE_PAYED"])."\"";
				break;
			case "CANCELED":
				$printValue  = (($arOrder["CANCELED"] == "Y") ? GetMessage("SEXC_YES") : GetMessage("SEXC_NO"))." ";
				$printValue .= $arOrder["DATE_CANCELED"];
				echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
				break;
			case "STATUS":
				if (!isset($LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]])
					|| !is_array($LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]]))
				{
					if ($arStatus = CSaleStatus::GetByID($arOrder["STATUS_ID"]))
						$LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]] = htmlspecialcharsEx($arStatus["NAME"]);
				}

				$printValue  = "[".$arOrder["STATUS_ID"]."] ".$LOCAL_STATUS_CACHE[$arOrder["STATUS_ID"]]." ";
				$printValue .= $arOrder["DATE_STATUS"];
				echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
				break;
			case "PAY_VOUCHER_NUM":
				echo "\"".str_replace("\"", "\"\"", $arOrder["PAY_VOUCHER_NUM"])."\"";
				break;
			case "PAY_VOUCHER_DATE":
				echo "\"".str_replace("\"", "\"\"", $arOrder["PAY_VOUCHER_DATE"])."\"";
				break;
			case "DELIVERY_DOC_NUM":
				echo "\"".str_replace("\"", "\"\"", $arOrder["DELIVERY_DOC_NUM"])."\"";
				break;
			case "DELIVERY_DOC_DATE":
				echo "\"".str_replace("\"", "\"\"", $arOrder["DELIVERY_DOC_DATE"])."\"";
				break;
			case "PRICE_DELIVERY":
				$printValue = SaleFormatCurrency($arOrder["PRICE_DELIVERY"], $arOrder["CURRENCY"]);
				echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
				break;
			case "ALLOW_DELIVERY":
				$printValue  = (($arOrder["ALLOW_DELIVERY"] == "Y") ? GetMessage("SEXC_YES") : GetMessage("SEXC_NO"))." ";
				$printValue .= $arOrder["DATE_ALLOW_DELIVERY"];
				echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
				break;
			case "PRICE":
				$printValue = SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"]);
				echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
				break;
			case "SUM_PAID":
				$printValue = SaleFormatCurrency($arOrder["SUM_PAID"], $arOrder["CURRENCY"]);
				echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
				break;
			case "USER":
				if (!isset($LOCAL_PAYED_USER_CACHE[$arOrder["USER_ID"]])
					|| !is_array($LOCAL_PAYED_USER_CACHE[$arOrder["USER_ID"]]))
				{
					$dbUser = CUser::GetByID($arOrder["USER_ID"]);
					if ($arUser = $dbUser->Fetch())
						$LOCAL_PAYED_USER_CACHE[$arOrder["USER_ID"]] = htmlspecialcharsEx($arUser["NAME"].((strlen($arUser["NAME"])<=0 || strlen($arUser["LAST_NAME"])<=0) ? "" : " ").$arUser["LAST_NAME"]." (".$arUser["LOGIN"].")");
				}
				$printValue = "[".$arOrder["USER_ID"]."] ".$LOCAL_PAYED_USER_CACHE[$arOrder["USER_ID"]];
				echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
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

					$printValue = "[".$arOrder["PAY_SYSTEM_ID"]."] ".$LOCAL_PAY_SYSTEM_CACHE[$arOrder["PAY_SYSTEM_ID"]];
					echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
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

					$printValue = "[".$arOrder["DELIVERY_ID"]."] ".$LOCAL_DELIVERY_CACHE[$arOrder["DELIVERY_ID"]];
					echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
				}
				break;
			case "DATE_UPDATE":
				echo "\"".str_replace("\"", "\"\"", $arOrder["DATE_UPDATE"])."\"";
				break;
			case "PS_STATUS":
				if ($arOrder["PS_STATUS"] == "Y")
					$printValue = GetMessage("SEXC_SUCCESS")." ".$arOrder["PS_RESPONSE_DATE"];
				elseif ($arOrder["PS_STATUS"] == "N")
					$printValue = GetMessage("SEXC_UNSUCCESS")." ".$arOrder["PS_RESPONSE_DATE"];
				else
					$printValue = GetMessage("SEXC_NONE");
				break;
				echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
			case "PS_SUM":
				$printValue = SaleFormatCurrency($arOrder["PS_SUM"], $arOrder["PS_CURRENCY"]);
				echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
				break;
			case "TAX_VALUE":
				$printValue = SaleFormatCurrency($arOrder["TAX_VALUE"], $arOrder["CURRENCY"]);
				echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
				break;
			case "BASKET":
				$printValue = "";
				$bNeedLine = False;
				$dbItemsList = CSaleBasket::GetList(
						array("NAME" => "ASC"),
						array("ORDER_ID" => $arOrder["ID"])
					);
				while ($arItem = $dbItemsList->Fetch())
				{
					if ($bNeedLine)
						$printValue .= "\n";
					$bNeedLine = True;

					$printValue .= "[".$arItem["PRODUCT_ID"]."] ";
					$printValue .= $arItem["NAME"];
					$printValue .= " (".$arItem["QUANTITY"].GetMessage("SEXC_SHT");
				}
				echo "\"".str_replace("\"", "\"\"", $printValue)."\"";
				break;
		}

		if ($i < count($arShownFieldsParams) - 1)
			echo $fieldsSeparator;
	}
	echo "\n";
}

$content = ob_get_contents();
ob_end_clean();

header('Pragma: public');
header('Cache-control: private');
header('Accept-Ranges: bytes');
header('Content-Length: '.strlen($content));
header("Content-Type: application/force-download");
header('Content-Disposition: attachment; filename=data.csv');


echo $content;

?>