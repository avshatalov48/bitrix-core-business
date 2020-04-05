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

$arParams["PATH_TO_DETAIL"] = Trim($arParams["PATH_TO_DETAIL"]);
if (strlen($arParams["PATH_TO_DETAIL"]) <= 0)
	$arParams["PATH_TO_DETAIL"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?ID=#ID#");
$arParams["URL_TO_NEW"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_DETAIL"], Array("ID" => "new"));

$arParams["PER_PAGE"] = (intval($arParams["PER_PAGE"]) <= 0 ? 20 : intval($arParams["PER_PAGE"]));
	
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if($arParams["SET_TITLE"] == 'Y')
	$APPLICATION->SetTitle(GetMessage("SPCL_DEFAULT_TITLE"));

//Delete profile
$errorMessage = "";
$del_id = IntVal($_REQUEST["del_id"]);
if ($del_id > 0 && check_bitrix_sessid())
{
	$dbUserCards = CSaleUserCards::GetList(
			array(),
			array(
					"ID" => $del_id,
					"USER_ID" => IntVal($USER->GetID())
				)
		);
	if ($arUserCards = $dbUserCards->Fetch())
	{
		if (!CSaleUserCards::Delete($arUserCards["ID"]))
		{
			if ($ex = $APPLICATION->GetException())
				$errorMessage = $ex->GetString();
			else
				$errorMessage = str_replace("#ID#", $del_id, GetMessage("STPCL_ERROR_DELETING"));
		}
	}
	else
	{
		$errorMessage = str_replace("#ID#", $del_id, GetMessage("STPCL_NO_CARD_FOUND"));
	}
}

if(strLen($errorMessage)>=0)
	$arResult["ERROR_MESSAGE"] = $errorMessage;
	
$by = (strlen($_REQUEST["by"])>0 ? $_REQUEST["by"]: "ID");
$order = (strlen($_REQUEST["order"])>0 ? $_REQUEST["order"]: "DESC");

$dbUserCards = CSaleUserCards::GetList(
		array($by => $order),
		array("USER_ID" => IntVal($USER->GetID()))
	);
$dbUserCards->NavStart($arParams["PER_PAGE"]);
$arResult["NAV_STRING"] = $dbUserCards->GetPageNavString(GetMessage("SPCL_PAGES"));
$arResult["CARDS"] = array();
while($arUserCards = $dbUserCards->GetNext())
{
	$arResultTmp = Array();
	$arResultTmp = $arUserCards;
	$arResultTmp["PAY_SYSTEM"] = CSalePaySystemAction::GetByID($arUserCards["PAY_SYSTEM_ACTION_ID"]);
	$arResultTmp["URL_TO_DETAIL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_DETAIL"], Array("ID" => $arUserCards["ID"]));
	$arResultTmp["URL_TO_DELETE"] = htmlspecialcharsbx($APPLICATION->GetCurPage())."?del_id=".$arUserCards["ID"]."&".bitrix_sessid_get();
	$arResult["CARDS"][] = $arResultTmp;
}

$this->IncludeComponentTemplate();
?>