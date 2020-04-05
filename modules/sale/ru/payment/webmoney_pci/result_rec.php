<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$bCorrectPayment = True;

	if (!($arOrder = CSaleOrder::GetByID(IntVal($_POST["pci_wmtid"]))))
		$bCorrectPayment = False;

	$CNST_PAYEE_PURSE = CSalePaySystemAction::GetParamValue("ACC_NUMBER");
	$CNST_SECRET_KEY = CSalePaySystemAction::GetParamValue("CNST_SECRET_KEY");

	if(strlen($CNST_SECRET_KEY) <=0 )
		$bCorrectPayment = False;

	if($_POST["LMI_PREREQUEST"] == "1")
	{
	
		if(round($arOrder["PRICE"],2) == round($_POST["LMI_PAYMENT_AMOUNT"],2)
			&& $CNST_PAYEE_PURSE == $_POST["LMI_PAYEE_PURSE"])
		{
			$APPLICATION->RestartBuffer();
			echo "YES";
			die();
		}
	}
	else
	{
	
		$SERVER_NAME_tmp = "";
		if (defined("SITE_SERVER_NAME"))
			$SERVER_NAME_tmp = SITE_SERVER_NAME;
		if (strlen($SERVER_NAME_tmp)<=0)
			$SERVER_NAME_tmp = COption::GetOptionString("main", "server_name", "");

	/*
	&purse=".$strPAYEE_PURSE;
$strPayPath .= "&amount=".round($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"], 2);
$strPayPath .= "&method=POST";
$strPayPath .= "&desc=Order_".IntVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"])
*/

		$strCheck = md5(
			$_POST["pci_wmtid"].
			$_POST["WMID"].
			md5(ToUpper(
				"http://".$SERVER_NAME_tmp.(CSalePaySystemAction::GetParamValue("PATH_TO_RESULT")).
				"?ORDER_ID=".$arOrder["ID"].
				$CNST_PAYEE_PURSE.
				round($arOrder["PRICE"], 2).
				"Order_".$arOrder["ID"].
				CSalePaySystemAction::GetParamValue("TEST_MODE")
				)).
			$_POST["pci_pursesrc"].
			$_POST["pci_pursedest"].
			$_POST["pci_amount"].
			$_POST["pci_desc"].
			$_POST["pci_datecrt"].
			$_POST["pci_mode"].
			md5($CNST_SECRET_KEY));
		if ($_POST["pci_marker"] != $strCheck)
			$bCorrectPayment = False;

		if ($bCorrectPayment)
		{
			$strPS_STATUS_DESCRIPTION = "";
			if (strlen($_POST["pci_mode"]) > 0)
				$strPS_STATUS_DESCRIPTION .= "тестовый режим, реально деньги не переводились; ";
			$strPS_STATUS_DESCRIPTION .= "кошелек продавца - ".$_POST["pci_pursedest"]."; ";
			$strPS_STATUS_DESCRIPTION .= "номер операции - ".$_POST["pci_wmtid"]."; ";
			$strPS_STATUS_DESCRIPTION .= "дата платежа - ".$_POST["pci_datecrt"]."";

			$strPS_STATUS_MESSAGE = "";
			$strPS_STATUS_MESSAGE .= "кошелек покупателя - ".$_POST["pci_pursesrc"]."; ";
			$strPS_STATUS_MESSAGE .= "WMId покупателя - ".$_POST["WMID"]."; ";
			$strPS_STATUS_MESSAGE .= "".$_POST["pci_desc"]."";

			$arFields = array(
					"PS_STATUS" => "Y",
					"PS_STATUS_CODE" => "-",
					"PS_STATUS_DESCRIPTION" => $strPS_STATUS_DESCRIPTION,
					"PS_STATUS_MESSAGE" => $strPS_STATUS_MESSAGE,
					"PS_SUM" => $_POST["pci_amount"],
					"PS_CURRENCY" => $arOrder["CURRENCY"],
					"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
					"USER_ID" => $arOrder["USER_ID"]
				);

			// You can comment this code if you want PAYED flag not to be set automatically
			if ($arOrder["PRICE"] == $_POST["pci_amount"] 
				&& $CNST_PAYEE_PURSE == $_POST["pci_pursedest"])
			{
				CSaleOrder::PayOrder($arOrder["ID"], "Y");
			}

			CSaleOrder::Update($arOrder["ID"], $arFields);
		}
	}
}

?>