<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if (!$USER->CanDoOperation("bitrixcloud_monitoring"))
{
	ShowError(GetMessage("BCLMME_ACCESS_DENIED"));
	return;
}

$arResult = array(
				"ACTION" => isset($_REQUEST["action"]) ? $_REQUEST["action"] : "edit",
				"DOMAIN" => isset($_REQUEST["domain"]) ? $_REQUEST["domain"] : '',
				"AJAX_PATH" => $componentPath."/ajax.php"
				);


if($arResult["DOMAIN"] === '')
{
	if(isset($arParams["LIST_URL"]))
	{
		LocalRedirect($arParams["LIST_URL"]);
	}
	else
	{
		echo GetMessage("BCLMME_NO_DATA");
		return;
	}
}

if (!CModule::IncludeModule('bitrixcloud'))
{
	ShowError(GetMessage("BCLMME_BC_NOT_INSTALLED"));
	return;
}

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError(GetMessage("BCLMME_MA_NOT_INSTALLED"));
	return;
}

CJSCore::Init('ajax');
CUtil::InitJSCore(array("mobile_monitoring"));

$monitoring = CBitrixCloudMonitoring::getInstance();

if(isset($arResult["ACTION"]))
{
	switch ($arResult["ACTION"])
	{
		case 'add':
			$arResult["DOMAIN_PARAMS"] = array(
				"DOMAIN" => $arResult["DOMAIN"],
				"IS_HTTPS" => "N",
				"LANG" => LANGUAGE_ID,
				"EMAILS" => array(
					COption::GetOptionString("main", "email_from", ""),
					),
				"TESTS" => array(
					"test_lic",
					"test_domain_registration",
					"test_http_response_time",
					),
			);

			break;

		case 'update':
			try
			{
				$result = $monitoring->startMonitoring(
					$arResult["DOMAIN"],
					$_REQUEST["IS_HTTPS"]==="Y",
					$_REQUEST["LANG"],
					$_REQUEST["EMAILS"],
					$_REQUEST["TESTS"]
				);

				if ($result != "")
				{
					ShowError($result);
					return;
				}

				LocalRedirect($arParams["LIST_URL"]);
			}
			catch (Exception $e)
			{
				ShowError($e->getMessage());
				return;
			}

			break;

		case 'delete':
			$strError = $monitoring->stopMonitoring($arResult["DOMAIN"]);

			if(strlen($strError) > 0)
			{
				ShowError($strError);
				return;
			}

			LocalRedirect($arParams["LIST_URL"]);
			break;


		case 'edit':
		default:
			try
			{
				$arList = $monitoring->getList();
			}
			catch (Exception $e)
			{
				ShowError($e->getMessage());
				return;
			}

			if (is_string($arList))
			{
				ShowError($arList);
				return;
			}

			foreach ($arList as $arRes)
			{
				if ($arRes["DOMAIN"] === $arResult["DOMAIN"])
				{
					$arResult["DOMAIN_PARAMS"] = $arRes;
					break;
				}
			}

			if(!isset($arResult["DOMAIN_PARAMS"]) && isset($arParams["LIST_URL"]) )
				LocalRedirect($arParams["LIST_URL"]);

			break;

	}
}

$converter = CBXPunycode::GetConverter();
$arResult["DOMAIN_CONVERTED"] = $converter->Decode($arResult["DOMAIN"]);
$arResult["LANG"] = LANGUAGE_ID;

$this->IncludeComponentTemplate();
?>
