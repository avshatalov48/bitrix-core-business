<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if(!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
	return;

$arParas["REDIRECT_PAGE"] = trim($arParams["REDIRECT_PAGE"]);

if ($arParams["SET_TITLE"] == '') $arParams["SET_TITLE"] = "Y";

if (
	$arParams["REDIRECT_PAGE"] == ''
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
				if ($USER_LOGIN == '')
					$errorMessage .= GetMessage("SPCR1_ON_LOGIN").".<br />";

				$USER_PASSWORD = $_REQUEST["USER_PASSWORD"];

				if ($errorMessage == '')
				{
					$arAuthResult = $GLOBALS["USER"]->Login($USER_LOGIN, $USER_PASSWORD, "N");
					if ($arAuthResult != False && $arAuthResult["TYPE"] == "ERROR")
						$errorMessage .= GetMessage("SPCR1_ERR_REG").(($arAuthResult["MESSAGE"] <> '') ? ": ".$arAuthResult["MESSAGE"] : ".<br />" );
				}
			}
			elseif ($_REQUEST["do_register"] == "Y")
			{
				$NEW_NAME = $_REQUEST["NEW_NAME"];
				if ($NEW_NAME == '')
					$errorMessage .= GetMessage("SPCR1_NO_NAME").".<br />";

				$NEW_LAST_NAME = $_REQUEST["NEW_LAST_NAME"];
				if ($NEW_LAST_NAME == '')
					$errorMessage .= GetMessage("SPCR1_NO_LASTNAME").".<br />";

				$NEW_EMAIL = $_REQUEST["NEW_EMAIL"];
				if ($NEW_EMAIL == '')
					$errorMessage .= GetMessage("SPCR1_NO_EMAIL").".<br />";
				elseif (!check_email($NEW_EMAIL))
					$errorMessage .= GetMessage("SPCR1_BAD_EMAIL").".<br />";

				$NEW_LOGIN = $_REQUEST["NEW_LOGIN"];
				if ($NEW_LOGIN == '')
					$errorMessage .= GetMessage("SPCR1_NO_LOGIN").".<br />";

				$NEW_PASSWORD = $_REQUEST["NEW_PASSWORD"];
				if ($NEW_PASSWORD == '')
					$errorMessage .= GetMessage("SPCR1_NO_PASSWORD").".<br />";

				$NEW_PASSWORD_CONFIRM = $_REQUEST["NEW_PASSWORD_CONFIRM"];
				if ($NEW_PASSWORD_CONFIRM == '')
					$errorMessage .= GetMessage("SPCR1_NO_PASSWORD_CONF").".<br />";

				if ($NEW_PASSWORD <> '' && $NEW_PASSWORD_CONFIRM <> '' && $NEW_PASSWORD != $NEW_PASSWORD_CONFIRM)
					$errorMessage .= GetMessage("SPCR1_NO_CONF").".<br />";

				if ($errorMessage == '')
				{
					$arAuthResult = $GLOBALS["USER"]->Register($NEW_LOGIN, $NEW_NAME, $NEW_LAST_NAME, $NEW_PASSWORD, $NEW_PASSWORD_CONFIRM, $NEW_EMAIL, LANG, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
					if ($arAuthResult != False && $arAuthResult["TYPE"] == "ERROR")
						$errorMessage .= GetMessage("SPCR1_ERR_REGISTER").(($arAuthResult["MESSAGE"] <> '') ? ": ".$arAuthResult["MESSAGE"] : ".<br />" );
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
				"USER_ID" => intval($GLOBALS["USER"]->GetID()),
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
				if ($_REQUEST["is_agree_agreement"] == "Y")
				{
					if ($_REQUEST["agree_agreement"] != "Y")
						$errorMessage .= GetMessage("SPCR1_NO_AGREE").".<br />";
				}

				$arResult["agree_agreement"] = $_REQUEST["agree_agreement"] == "Y" ? "Y" : "N";

				$AFF_SITE = Trim($_REQUEST["AFF_SITE"]);
				if ($AFF_SITE == '')
					$errorMessage .= GetMessage("SPCR1_NO_SITE").".<br />";

				$arResult["AFF_SITE"] = htmlspecialcharsbx($AFF_SITE);

				$AFF_DESCRIPTION = Trim($_REQUEST["AFF_DESCRIPTION"]);
				if ($AFF_DESCRIPTION == '')
					$errorMessage .= GetMessage("SPCR1_NO_DESCR").".<br />";

				$arResult["AFF_DESCRIPTION"] = htmlspecialcharsbx($AFF_DESCRIPTION);

				if ($errorMessage == '')
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

				if ($errorMessage == '')
				{
					$arFields = array(
						"SITE_ID" => SITE_ID,
						"USER_ID" => intval($GLOBALS["USER"]->GetID()),
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
				$arResult["AGREEMENT_TEXT_FILE"] = false;
			}

			$arResult['USER_CONSENT_PROPERTY_DATA'] = [
				GetMessage("SPCR1_SITE_URL_CONSENT"),
				GetMessage("SPCR1_SITE_DESCR_CONSENT")
			];
		}
	}

	$arResult["REDIRECT_PAGE"] = htmlspecialcharsbx($arParams["REDIRECT_PAGE"]);
	$arResult["DEFAULT_USER_LOGIN"] = ($_REQUEST["USER_LOGIN"] <> '') ? htmlspecialcharsbx($_REQUEST["USER_LOGIN"]) : htmlspecialcharsbx($arResult["DEFAULT_USER_LOGIN"]);

	$this->IncludeComponentTemplate();
}
else
{
	?>
	<b><? ShowError(GetMessage("SPCR1_NO_SHOP")) ?></b>
	<?
}
?>