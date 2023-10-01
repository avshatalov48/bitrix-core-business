<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/****************************************************************************/
/***************    ENGLISH    **********************************************/
/* CommerceML format. Every item in shopping cart should have the following */
/* properties:                                                              */
/*	- catalog XML_ID (with mnemonic code CATALOG.XML_ID)                     */
/* - product XML_ID (with mnemonic code PRODUCT.XML_ID).                    */
/****************************************************************************/

$SALER_COMPANY_XML_ID = "1";
IncludeModuleLangFile(__FILE__);

if (!isset($arFilter) || !is_array($arFilter))
	die("Wrong use 1");


$filter = array(
	'filter' => $arFilter,
	'select' => array("ID", "LID", "PERSON_TYPE_ID", "PAYED", "DATE_PAYED", "EMP_PAYED_ID", "CANCELED", "DATE_CANCELED", "EMP_CANCELED_ID", "REASON_CANCELED", "STATUS_ID", "DATE_STATUS", "PAY_VOUCHER_NUM", "PAY_VOUCHER_DATE", "EMP_STATUS_ID", "PRICE_DELIVERY", "ALLOW_DELIVERY", "DATE_ALLOW_DELIVERY", "EMP_ALLOW_DELIVERY_ID", "PRICE", "CURRENCY", "DISCOUNT_VALUE", "SUM_PAID", "USER_ID", "PAY_SYSTEM_ID", "DELIVERY_ID", "DATE_INSERT", "DATE_INSERT_FORMAT", "DATE_UPDATE", "USER_DESCRIPTION", "ADDITIONAL_INFO",
		'PS_STATUS' => 'PAYMENT.PS_STATUS',
		'PS_STATUS_CODE' => 'PAYMENT.PS_STATUS_CODE',
		'PS_STATUS_DESCRIPTION' => 'PAYMENT.PS_STATUS_DESCRIPTION',
		'PS_STATUS_MESSAGE' => 'PAYMENT.PS_STATUS_MESSAGE',
		'PS_SUM' => 'PAYMENT.PS_SUM',
		'PS_CURRENCY' => 'PAYMENT.PS_CURRENCY',
		'PS_RESPONSE_DATE' => 'PAYMENT.PS_RESPONSE_DATE',

		"COMMENTS", "TAX_VALUE", "STAT_GID", "RECURRING_ID"),
	'runtime' => $runtimeFields
);


if (!empty($by))
{
	$order = (!empty($order) && $order == "DESC" ? "DESC" : "ASC");
	$filter['order'] = array($by => $order);
}

$dbOrderList = new CDBResult(\Bitrix\Sale\Internals\OrderTable::getList($filter));

ob_start();

echo "<"."?xml version=\"1.0\" encoding=\"".LANG_CHARSET."\"?".">\n";
echo "<BizTalk xmlns=\"urn:schemas-biztalk-org:biztalk/biztalk-0.81.xml\"><Route><From locationID=\"\" locationType=\"\" process=\"\" path=\"\" handle=\"\"/><To locationID=\"\" locationType=\"\" process=\"\" path=\"\" handle=\"\"/></Route><Body>\n";
echo "	<".GetMessage("CommerceInfo")." xmlns=\"urn:CommerceML\">\n";

$arContra = array();
while ($dbOrderList->NavNext(true, "f_")):
	echo "		<".GetMessage("Document")." ".GetMessage("Operation")."=\"Order\" ".GetMessage("NumberDoc")."=\"".$f_ID."\" ";
	echo "".GetMessage("DateDoc")."=\"".CDatabase::FormatDate($f_DATE_INSERT, CLang::GetDateFormat("FULL", LANG), "Y-M-D")."\" ".GetMessage("TimeDoc")."=\"".CDatabase::FormatDate($f_DATE_INSERT, CLang::GetDateFormat("FULL", LANG), "HH:MI:SS")."\" ".GetMessage("PaymentDate")."=\"\" ";
	echo "".GetMessage("Sum")."=\"".$f_PRICE."\" ".GetMessage("Comment")."=\"\" ".GetMessage("Currency")."=\"".$f_CURRENCY."\">\n";
	echo "			<".GetMessage("CompanyInDocument")." ".GetMessage("Role")."=\"Saler\" ".GetMessage("Company")."=\"".$SALER_COMPANY_XML_ID."\"/>\n";
	echo "			<".GetMessage("CompanyInDocument")." ".GetMessage("Role")."=\"Buyer\" ".GetMessage("Company")."=\"BC".$f_USER_ID."\"/>\n";

	$dbOrderTax = CSaleOrderTax::GetList(
		array(),
		array("ORDER_ID" => $f_ID),
		false,
		false,
		array("ID", "TAX_NAME", "VALUE", "VALUE_MONEY", "CODE", "IS_IN_PRICE")
	);
	while ($arOrderTax = $dbOrderTax->Fetch())
	{
		echo "			<".GetMessage("TaxSum")." ".GetMessage("Tax")."=\"".htmlspecialcharsbx($arOrderTax["TAX_NAME"])."\" ".GetMessage("TaxRate")."=\"".htmlspecialcharsbx($arOrderTax["VALUE"])."\" ".GetMessage("Sum")."=\"".htmlspecialcharsbx($arOrderTax["VALUE_MONEY"])."\" ".GetMessage("IncludedInSum")."=\"".(($arOrderTax["IS_IN_PRICE"]=="Y") ? 1 : 0)."\"/>\n";
	}


	$dbBasket = CSaleBasket::GetList(
			array("NAME" => "ASC"),
			array("ORDER_ID" => $f_ID)
		);
	while ($arBasket = $dbBasket->Fetch())
	{
		$CATALOG_XML_ID = $arBasket["CATALOG_XML_ID"];
		$PRODUCT_XML_ID = $arBasket["PRODUCT_XML_ID"];
		if ($PRODUCT_XML_ID == '' && $CATALOG_XML_ID == '')
		{
			$dbBasketProps = CSaleBasket::GetPropsList(
					array("CODE" => "ASC"),
					array("BASKET_ID" => $arBasket["ID"])
				);
			while ($arBasketProps = $dbBasketProps->Fetch())
			{
				if ($arBasketProps["CODE"] == "CATALOG.XML_ID")
					$CATALOG_XML_ID = $arBasketProps["VALUE"];
				elseif ($arBasketProps["CODE"] == "PRODUCT.XML_ID")
				{
					$PRODUCT_XML_ID = $arBasketProps["VALUE"];
					if (mb_substr($PRODUCT_XML_ID, 0, 2) == "ID")
						$PRODUCT_XML_ID = mb_substr($PRODUCT_XML_ID, 2);
				}
			}
		}
		if ($PRODUCT_XML_ID == '')
			$PRODUCT_XML_ID = $arBasket["PRODUCT_ID"];

		echo "			<".GetMessage("Article")." ".GetMessage("Catalog")."=\"".$CATALOG_XML_ID."\" ".GetMessage("Product")."=\"".$PRODUCT_XML_ID."\" ".GetMessage("Unit")."=\"\" ".GetMessage("Amount")."=\"".$arBasket["QUANTITY"]."\" ".GetMessage("Price")."=\"".$arBasket["PRICE"]."\" ".GetMessage("Sum")."=\"".(DoubleVal($arBasket["PRICE"])*intval($arBasket["QUANTITY"]))."\" ".GetMessage("Description")."=\"".htmlspecialcharsbx($arBasket["NAME"])."\"/>\n";
	}
	echo "		</".GetMessage("Document").">\n";

	if (!in_array("BC".$f_USER_ID, $arContra))
	{
		$arContra[] = "BC".$f_USER_ID;

		$db_user = CUser::GetByID($f_USER_ID);
		$arUser = $db_user->Fetch();

		$contra_mail = "";
		$contra_name = $arUser["NAME"]." ".$arUser["LAST_NAME"];
		$contra_other = "";
		$db_props = CSaleOrderPropsValue::GetOrderProps($f_ID);
		while ($arProps = $db_props->Fetch())
		{
			if ($arProps["IS_EMAIL"]=="Y")
				$contra_mail = $arProps["VALUE"];
			if ($arProps["IS_PAYER"]=="Y")
				$contra_name = $arProps["VALUE"];
			if ($arProps["TYPE"]=="LOCATION")
			{
				$arLocs = CSaleLocation::GetByID($arProps["VALUE"], LANG);
				$contra_other .= $arProps["PROPERTY_NAME"]."=".$arLocs["COUNTRY_NAME"]." - ".$arLocs["CITY_NAME"];
			}
			else
			{
				$contra_other .= $arProps["PROPERTY_NAME"]."=".$arProps["VALUE"];
			}
			$contra_other .= " // ";
		}

		echo "		<".GetMessage("Company")." ".GetMessage("ID")."=\"BC".$f_USER_ID."\" ".GetMessage("Name")."=\"".htmlspecialcharsbx($contra_name)."\" ".GetMessage("DisplayName")."=\"".htmlspecialcharsbx($contra_name)."\" ".GetMessage("Address")."=\"\" ".GetMessage("JuridicAddress")."=\"\" ".GetMessage("WWW")."=\"\" ".GetMessage("Comment")."=\"".htmlspecialcharsbx($contra_other)."\">\n";
		echo "			<".GetMessage("Contact")." ".GetMessage("ID")."=\"B".$f_USER_ID."\" ".GetMessage("Name")."=\"".GetMessage("Contact")."\">\n";
		echo "				<".GetMessage("ContactMan").">".htmlspecialcharsbx($arUser["NAME"]." ".$arUser["LAST_NAME"])." (".htmlspecialcharsbx($contra_name).")</".GetMessage("ContactMan").">\n";
		echo "				<".GetMessage("E-mail").">".htmlspecialcharsbx($contra_mail)."</".GetMessage("E-mail").">\n";
		echo "			</".GetMessage("Contact").">\n";
		echo "		</".GetMessage("Company").">\n";
	}
endwhile;

echo "		<".GetMessage("Company")." ".GetMessage("ID")."=\"".$SALER_COMPANY_XML_ID."\"/>\n";
echo "	</".GetMessage("CommerceInfo").">\n";
echo "</Body></BizTalk>";

$content = ob_get_contents();
ob_end_clean();

header('Pragma: public');
header('Cache-control: private');
header('Accept-Ranges: bytes');
header("Content-Type: application/xml");
header('Content-Length: ' . strlen($content));
header("Content-Disposition: attachment; filename=order.xml");

echo $content;
die();
?>