<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->setFramemode(false);

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

if (!CBXFeatures::IsFeatureEnabled('SaleCCards'))
	return;

if (!$USER->IsAuthorized())
{
	$APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"));
}

$ID = intval($arParams["ID"]);
$errorMessage = "";
$bVarsFromForm = false;

$arParams["PATH_TO_LIST"] = Trim($arParams["PATH_TO_LIST"]);
if ($arParams["PATH_TO_LIST"] == '')
	$arParams["PATH_TO_LIST"] = htmlspecialcharsbx($APPLICATION->GetCurPage());
	
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if($arParams["SET_TITLE"] == 'Y')
{
	if ($ID > 0)
		$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("STPC_TITLE_UPDATE")));
	else
		$APPLICATION->SetTitle(GetMessage("STPC_TITLE_ADD"));
}

if($_POST["reset"] <> '')
	LocalRedirect($arParams["PATH_TO_LIST"]);
if ($_SERVER["REQUEST_METHOD"]=="POST" && ($_POST["save"] <> '' || $_POST["apply"] <> '') && check_bitrix_sessid())
{
	if ($ID > 0)
	{
		$dbUserCards = CSaleUserCards::GetList(
				array(),
				array(
						"ID" => $ID,
						"USER_ID" => intval($USER->GetID())
					),
				false,
				false,
				array("ID")
			);
		if (!($arUserCards = $dbUserCards->Fetch()))
		{
			$errorMessage .= GetMessage("STPC_NO_CARD").". ";
		}
	}

	if ($errorMessage == '')
	{
		$PAY_SYSTEM_ACTION_ID = intval($_REQUEST["PAY_SYSTEM_ACTION_ID"]);
		if ($PAY_SYSTEM_ACTION_ID <= 0)
			$errorMessage .= GetMessage("STPC_EMPTY_PAY_SYS").". ";

		$CARD_TYPE = Trim($_REQUEST["CARD_TYPE"]);
		$CARD_TYPE = mb_strtoupper($CARD_TYPE);
		if ($CARD_TYPE == '')
			$errorMessage .= GetMessage("STPC_EMPTY_CARD_TYPE").". ";

		$CARD_NUM = preg_replace("/[\D]+/", "", $_REQUEST["CARD_NUM"]);
		if ($CARD_NUM == '')
		{
			$errorMessage .= GetMessage("STPC_EMPTY_CARDNUM").". ";
		}
		else
		{
			$cardType = CSaleUserCards::IdentifyCardType($CARD_NUM);
			if ($cardType != $CARD_TYPE)
				$errorMessage .= GetMessage("STPC_WRONG_CARDNUM").". ";
		}

		$CARD_EXP_MONTH = intval($_REQUEST["CARD_EXP_MONTH"]);
		if ($CARD_EXP_MONTH < 1 || $CARD_EXP_MONTH > 12)
			$errorMessage .= GetMessage("STPC_WRONG_MONTH").". ";

		$CARD_EXP_YEAR = intval($_REQUEST["CARD_EXP_YEAR"]);
		if ($CARD_EXP_YEAR < 2007 || $CARD_EXP_YEAR > 2100)
			$errorMessage .= GetMessage("STPC_WRONG_YEAR").". ";

		$CARD_CODE = Trim($_REQUEST["CARD_CODE"]);
	}

	if ($errorMessage == '')
	{
		$SUM_MIN = str_replace(",", ".", $_REQUEST["SUM_MIN"]);
		$SUM_MIN = DoubleVal($SUM_MIN);
		$SUM_MAX = str_replace(",", ".", $_REQUEST["SUM_MAX"]);
		$SUM_MAX = DoubleVal($SUM_MAX);
		$ACTIVE = (($_REQUEST["ACTIVE"] == "Y") ? "Y" : "N");
		$SORT = ((intval($_REQUEST["SORT"]) > 0) ? intval($_REQUEST["SORT"]) : 100);
		$CURRENCY = Trim($_REQUEST["CURRENCY"]);
		$SUM_CURRENCY = Trim($_REQUEST["SUM_CURRENCY"]);

		if (($SUM_MIN > 0 || $SUM_MAX > 0) && $SUM_CURRENCY == '')
			$errorMessage .= GetMessage("STPC_EMPTY_BCURRENCY").". ";
	}

	if ($errorMessage == '')
	{
		$arFields = array(
				"USER_ID" => intval($USER->GetID()),
				"ACTIVE" => $ACTIVE,
				"SORT" => $SORT,
				"PAY_SYSTEM_ACTION_ID" => $PAY_SYSTEM_ACTION_ID,
				"CURRENCY" => (($CURRENCY <> '') ? $CURRENCY : False),
				"CARD_TYPE" => $CARD_TYPE,
				"CARD_NUM" => CSaleUserCards::CryptData($CARD_NUM, "E"),
				"CARD_EXP_MONTH" => $CARD_EXP_MONTH,
				"CARD_EXP_YEAR" => $CARD_EXP_YEAR,
				"CARD_CODE" => $CARD_CODE,
				"SUM_MIN" => (($SUM_MIN > 0) ? $SUM_MIN : False),
				"SUM_MAX" => (($SUM_MAX > 0) ? $SUM_MAX : False),
				"SUM_CURRENCY" => (($SUM_CURRENCY <> '') ? $SUM_CURRENCY : False)
			);

		if ($ID > 0)
		{
			$res = CSaleUserCards::Update($ID, $arFields);
		}
		else
		{
			$ID = CSaleUserCards::Add($arFields);
			$res = ($ID > 0);
		}

		if (!$res)
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage .= $ex->GetString().". ";
			else
				$errorMessage .= GetMessage("STPC_ERROR_SAVING_CARD").". ";
		}
	}

	if ($errorMessage == '')
	{
		if ($_POST["save"] <> '')
			LocalRedirect($arParams["PATH_TO_LIST"]);
		elseif($_POST["apply"] <> '')
			LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_DETAIL"], Array("ID" => $ID)));

	}
	else
	{
		$bVarsFromForm = true;
	}
}

$dbUserCards = CSaleUserCards::GetList(
		array("DATE_UPDATE" => "DESC"),
		array(
				"ID" => $ID,
				"USER_ID" => intval($GLOBALS["USER"]->GetID())
			),
		false,
		false,
		array("ID", "USER_ID", "ACTIVE", "SORT", "PAY_SYSTEM_ACTION_ID", "CURRENCY", "CARD_TYPE", "CARD_NUM", "CARD_CODE", "CARD_EXP_MONTH", "CARD_EXP_YEAR", "DESCRIPTION", "SUM_MIN", "SUM_MAX", "SUM_CURRENCY", "TIMESTAMP_X", "LAST_STATUS", "LAST_STATUS_CODE", "LAST_STATUS_DESCRIPTION", "LAST_STATUS_MESSAGE", "LAST_SUM", "LAST_CURRENCY", "LAST_DATE")
	);
if ($arUserCards = $dbUserCards->GetNext())
{
	$arResult = $arUserCards;
	$arResult["CARD_NUM"] = CSaleUserCards::CryptData($arResult["CARD_NUM"], "D");
}
else
{
	$arResult["ID"] = 0;
	$arResult["ACTIVE"] = "Y";
	$arResult["SORT"] = 100;
}

if ($bVarsFromForm)
{
	foreach($_POST as $k => $v)
	{
		$arResult[$k] = htmlspecialcharsex($v);
		$arResult['~'.$k] = $v;
	}
}

$arResult["ERROR_MESSAGE"] = $errorMessage;

$dbPaySysActions = CSalePaySystemAction::GetList(
		array("PERSON_TYPE_ID" => "ASC", "NAME" => "ASC", "PT_NAME" => "ASC", "PS_NAME" => "ASC"),
		array(
				"HAVE_ACTION" => "Y"
			),
		false,
		false,
		array("*")
	);
$arResult["PAY_SYSTEM"] = Array();
while ($arPaySysActions = $dbPaySysActions->GetNext())
	$arResult["PAY_SYSTEM"][] = $arPaySysActions;

$dbCurrency = CCurrency::GetList('sort', 'asc');
$arResult["CURRENCY_INFO"] = Array();
while ($arCurrency = $dbCurrency->GetNext())
	$arResult["CURRENCY_INFO"][] = $arCurrency;

$arResult["CARD_TYPE_INFO"] = Array(
		"VISA" => "Visa",
		"MASTERCARD" => "MasterCard",
		"AMEX" => "Amex",
		"DINERS" => "Diners",
		"DISCOVER" => "Discover",
		"JCB" => "JCB",
		"ENROUTE" => "Enroute",
	);

$this->IncludeComponentTemplate();
?>