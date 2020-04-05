<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if(!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
	return;

$arParas["REDIRECT_PAGE"] = trim($arParams["REDIRECT_PAGE"]);

if (strlen($arParams["SET_TITLE"]) <= 0) $arParams["SET_TITLE"] = "Y";

if (
	strlen($arParams["REDIRECT_PAGE"]) <= 0
	|| preg_match('/^([a-z0-9]+):\\/\\//', $arParams["REDIRECT_PAGE"])
)
	$arParams["REDIRECT_PAGE"] = "index.php";

if (CModule::IncludeModule("sale"))
{
	CSaleAffiliate::GetAffiliate();

	$errorMessage = "";

	$arResult = array();

	if (!$GLOBALS["USER"]->IsAuthorized())
	{
		if($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid())
		{
			if ($_REQUEST["do_authorize"] == "Y")
			{
				$USER_LOGIN = $_REQUEST["USER_LOGIN"];
				if (strlen($USER_LOGIN) <= 0)
					$errorMessage .= GetMessage("SPCR1_ON_LOGIN").".<br />";

				$USER_PASSWORD = $_REQUEST["USER_PASSWORD"];

				if (strlen($errorMessage) <= 0)
				{
					$arAuthResult = $GLOBALS["USER"]->Login($USER_LOGIN, $USER_PASSWORD, "N");
					if ($arAuthResult != False && $arAuthResult["TYPE"] == "ERROR")
						$errorMessage .= GetMessage("SPCR1_ERR_REG").((strlen($arAuthResult["MESSAGE"]) > 0) ? ": ".$arAuthResult["MESSAGE"] : ".<br />" );
				}
			}
			elseif ($_REQUEST["do_register"] == "Y")
			{
				$NEW_NAME = $_REQUEST["NEW_NAME"];
				if (strlen($NEW_NAME) <= 0)
					$errorMessage .= GetMessage("SPCR1_NO_NAME").".<br />";

				$NEW_LAST_NAME = $_REQUEST["NEW_LAST_NAME"];
				if (strlen($NEW_LAST_NAME) <= 0)
					$errorMessage .= GetMessage("SPCR1_NO_LASTNAME").".<br />";

				$NEW_EMAIL = $_REQUEST["NEW_EMAIL"];
				if (strlen($NEW_EMAIL) <= 0)
					$errorMessage .= GetMessage("SPCR1_NO_EMAIL").".<br />";
				elseif (!check_email($NEW_EMAIL))
					$errorMessage .= GetMessage("SPCR1_BAD_EMAIL").".<br />";

				$NEW_LOGIN = $_REQUEST["NEW_LOGIN"];
				if (strlen($NEW_LOGIN) <= 0)
					$errorMessage .= GetMessage("SPCR1_NO_LOGIN").".<br />";

				$NEW_PASSWORD = $_REQUEST["NEW_PASSWORD"];
				if (strlen($NEW_PASSWORD) <= 0)
					$errorMessage .= GetMessage("SPCR1_NO_PASSWORD").".<br />";

				$NEW_PASSWORD_CONFIRM = $_REQUEST["NEW_PASSWORD_CONFIRM"];
				if (strlen($NEW_PASSWORD_CONFIRM) <= 0)
					$errorMessage .= GetMessage("SPCR1_NO_PASSWORD_CONF").".<br />";

				if (strlen($NEW_PASSWORD) > 0 && strlen($NEW_PASSWORD_CONFIRM) > 0 && $NEW_PASSWORD != $NEW_PASSWORD_CONFIRM)
					$errorMessage .= GetMessage("SPCR1_NO_CONF").".<br />";

				if (strlen($errorMessage) <= 0)
				{
					$arAuthResult = $GLOBALS["USER"]->Register($NEW_LOGIN, $NEW_NAME, $NEW_LAST_NAME, $NEW_PASSWORD, $NEW_PASSWORD_CONFIRM, $NEW_EMAIL, LANG, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
					if ($arAuthResult != False && $arAuthResult["TYPE"] == "ERROR")
						$errorMessage .= GetMessage("SPCR1_ERR_REGISTER").((strlen($arAuthResult["MESSAGE"]) > 0) ? ": ".$arAuthResult["MESSAGE"] : ".<br />" );
					else
						if ($GLOBALS["USER"]->IsAuthorized())
							CUser::SendUserInfo($GLOBALS["USER"]->GetID(), SITE_ID, GetMessage("INFO_REQ"), true);
				}

				$arResult["NEW_LOGIN"] = htmlspecialcharsbx($NEW_LOGIN);
				$arResult["NEW_NAME"] = htmlspecialcharsbx($NEW_NAME);
				$arResult["NEW_LAST_NAME"] = htmlspecialcharsbx($NEW_LAST_NAME);
				$arResult["NEW_PASSWORD"] = htmlspecialcharsbx($NEW_PASSWORD);
				$arResult["NEW_PASSWORD_CONFIRM"] = htmlspecialcharsbx($NEW_PASSWORD_CONFIRM);
				$arResult["NEW_EMAIL"] = htmlspecialcharsbx($NEW_EMAIL);
			}
		}
	}

	$arResult["AFFILIATE"] = "N";
	$arResult["UNACTIVE_AFFILIATE"] = "N";
	if ($GLOBALS["USER"]->IsAuthorized())
	{
		$dbAffiliate = CSaleAffiliate::GetList(
			array("TRANSACT_DATE" => "ASC"),
			array(
				"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
				"SITE_ID" => SITE_ID
			),
			false,
			false,
			array("ID", "ACTIVE")
		);
		if ($arAffiliate = $dbAffiliate->Fetch())
		{
			$arResult["AFFILIATE"] = "Y";
			if ($arAffiliate["ACTIVE"] == "Y")
			{
				LocalRedirect($arParams["REDIRECT_PAGE"]);
				die();
			}
			else
			{
				$arResult["UNACTIVE_AFFILIATE"] = "Y";
			}
		}
	}

	if ($arResult["AFFILIATE"] == "N")
	{
		if ($arParams["SET_TITLE"] == "Y")
			$APPLICATION->SetTitle(GetMessage("SPCR1_REGISTER_AFF"));

		/****************************************************************/
		/*********     ACTIONS    ***************************************/
		/****************************************************************/
		if($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid())
		{
			if ($_REQUEST["do_agree"] == "Y")
			{
				if ($_REQUEST["agree_agreement"] != "Y")
					$errorMessage .= GetMessage("SPCR1_NO_AGREE").".<br />";

				$arResult["agree_agreement"] = $_REQUEST["agree_agreement"] == "Y" ? "Y" : "N";

				$AFF_SITE = Trim($_REQUEST["AFF_SITE"]);
				if (StrLen($AFF_SITE) <= 0)
					$errorMessage .= GetMessage("SPCR1_NO_SITE").".<br />";

				$arResult["AFF_SITE"] = htmlspecialcharsbx($AFF_SITE);

				$AFF_DESCRIPTION = Trim($_REQUEST["AFF_DESCRIPTION"]);
				if (StrLen($AFF_DESCRIPTION) <= 0)
					$errorMessage .= GetMessage("SPCR1_NO_DESCR").".<br />";

				$arResult["AFF_DESCRIPTION"] = htmlspecialcharsbx($AFF_DESCRIPTION);

				if (StrLen($errorMessage) <= 0)
				{
					$dbPlan = CSaleAffiliatePlan::GetList(
						array("MIN_PLAN_VALUE" => "ASC"),
						array(
							"SITE_ID" => SITE_ID,
							"ACTIVE" => "Y",
						),
						false,
						false,
						array("ID", "MIN_PLAN_VALUE")
					);
					$arPlan = $dbPlan->Fetch();

					if (!$arPlan)
						$errorMessage .= GetMessage("SPCR1_NO_PLANS").".<br />";
				}

				if (StrLen($errorMessage) <= 0)
				{
					$arFields = array(
						"SITE_ID" => SITE_ID,
						"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
						"PLAN_ID" => $arPlan["ID"],
						"ACTIVE" => ((DoubleVal($arPlan["MIN_PLAN_VALUE"]) > 0) ? "N" : "Y"),
						"DATE_CREATE" => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()+CTimeZone::GetOffset()),
						"PAID_SUM" => 0,
						"PENDING_SUM" => 0,
						"LAST_CALCULATE" => false,
						"FIX_PLAN" => "N",
						"AFF_SITE" => $AFF_SITE,
						"AFF_DESCRIPTION" => $AFF_DESCRIPTION
					);

					$affiliateID = CSaleAffiliate::GetAffiliate();
					if ($affiliateID > 0)
						$arFields["AFFILIATE_ID"] = $affiliateID;
					else
						$arFields["AFFILIATE_ID"] = false;

					if (!CSaleAffiliate::Add($arFields))
					{
						if ($ex = $GLOBALS["APPLICATION"]->GetException())
							$errorMessage .= $ex->GetString().".<br />";
						else
							$errorMessage .= GetMessage("SPCR1_ERR_AFF").".<br />";
					}
					else
					{
						LocalRedirect($arParams["REDIRECT_PAGE"]);
						die();
					}
				}
			}
		}
		
		$arResult["ERROR_MESSAGE"] = $errorMessage;
		$arResult["CURRENT_PAGE"] = $APPLICATION->GetCurPage();
		if (!$GLOBALS["USER"]->IsAuthorized())
		{
			$arResult["USER_AUTHORIZED"] = "N";
			$arResult["DEFAULT_USER_LOGIN"] = ${COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"};

			$arResult["CAPTCHA_CODE"] = False;
			if (COption::GetOptionString("main", "captcha_registration", "N") == "Y")
				$arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($GLOBALS["APPLICATION"]->CaptchaGetCode());
		}
		else
		{
			$arResult["USER_AUTHORIZED"] = "Y";
			
			$arResult["AGREEMENT_TEXT_FILE"] = $arParams["AGREEMENT_TEXT_FILE"];
			if (empty($arResult["AGREEMENT_TEXT_FILE"]) || !file_exists($_SERVER["DOCUMENT_ROOT"].$arResult["AGREEMENT_TEXT_FILE"]))
			{
				$arResult["AGREEMENT_TEXT_FILE"] = "/bitrix/components/bitrix/sale.affiliate.register/agreement-".SITE_ID.".htm";
				if (!file_exists($_SERVER["DOCUMENT_ROOT"].$arResult["AGREEMENT_TEXT_FILE"]))
				{
					$arResult["AGREEMENT_TEXT_FILE"] = "/bitrix/php_interface/agreement.htm";
					if (!file_exists($_SERVER["DOCUMENT_ROOT"].$arResult["AGREEMENT_TEXT_FILE"]))
					{
						$arResult["AGREEMENT_TEXT_FILE"] = false;
					}
				}
			}
		}
	}
	
	$arResult["REDIRECT_PAGE"] = htmlspecialcharsbx($arParams["REDIRECT_PAGE"]);
	$arResult["DEFAULT_USER_LOGIN"] = (strlen($_REQUEST["USER_LOGIN"]) > 0) ? htmlspecialcharsbx($_REQUEST["USER_LOGIN"]) : htmlspecialcharsbx($arResult["DEFAULT_USER_LOGIN"]);

	$this->IncludeComponentTemplate();
}
else
{
	?>
	<b><?=ShowError(GetMessage("SPCR1_NO_SHOP"))?></b>
	<?
}
?>