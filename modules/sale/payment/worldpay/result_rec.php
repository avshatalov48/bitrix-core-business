<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET["server_responce"]) && $_GET["server_responce"] == "Y" && !empty($_POST["cartId"]))
{
	$bCorrect = True;

	if ($bCorrect && !($arOrder = CSaleOrder::GetByID(intval($_POST["cartId"]))))
		$bCorrect = False;

	if ($bCorrect)
	{
		CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"]);
		$strCallbackPassword = CSalePaySystemAction::GetParamValue("CALLBACK_PASSWORD");

		if ($strCallbackPassword == '' || $_POST["callbackPW"] != $strCallbackPassword)
			$bCorrect = False;
	}

	if ($bCorrect && isset($_POST["testMode"]) && intval($_POST["testMode"]) > 0)
		$bCorrect = False;

	if ($bCorrect)
	{
		$arFields = array(
				"PS_STATUS" => (($_POST["transStatus"]=="Y") ? "Y" : "N"),
				"PS_STATUS_CODE" => $_POST["transStatus"],
				"PS_STATUS_DESCRIPTION" => $_POST["rawAuthMessage"],
				"PS_STATUS_MESSAGE" => (($_POST["transStatus"]=="Y") ? ("The WorldPay ID for this transaction: ".$_POST["transId"].", Time of this transaction: ".Date("r", $_POST["transTime"])) : ""),
				"PS_SUM" => $_POST["authAmount"],
				"PS_CURRENCY" => $_POST["authCurrency"],
				"PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
				"USER_ID" => $arOrder["USER_ID"]
			);

		if ($arOrder["CURRENCY"]==$_POST["authCurrency"] && $arOrder["PRICE"]==$_POST["authAmount"])
		{
			CSaleOrder::PayOrder($arOrder["ID"], "Y");
		}

		CSaleOrder::Update($arOrder["ID"], $arFields);
	}
}
?>