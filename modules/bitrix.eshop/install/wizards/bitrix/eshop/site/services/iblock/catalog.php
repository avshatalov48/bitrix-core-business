<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("iblock") || !CModule::IncludeModule("catalog"))
	return;

if(COption::GetOptionString("eshop", "wizard_installed", "N", WIZARD_SITE_ID) == "Y" && !WIZARD_INSTALL_DEMO_DATA)
	return;

//catalog iblock import
$shopLocalization = $wizard->GetVar("shopLocalization");

$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/catalog.xml";
switch ($shopLocalization)
{
	case 'ua':
		$iblockXMLFilePrices = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/catalog_prices_ua.xml";
		if (!CCurrency::GetByID('UAH'))
		{
			$arFields = array(
				"CURRENCY" => "UAH",
				"AMOUNT" => 39.41,
				"AMOUNT_CNT" => 10,
				"SORT" => 400
			);
			CCurrency::Add($arFields);

			$dbLangs = CLanguage::GetList($b, $o, array("ACTIVE" => "Y"));
			while ($arLangs = $dbLangs->Fetch())
			{
				IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install_lang.php", $arLangs["LID"]);
				$arFields = array(
					"LID" => $arLangs["LID"],
					"CURRENCY" => "UAH",
					"FORMAT_STRING" => GetMessage("CUR_INSTALL_UAH_FORMAT_STRING") ? GetMessage("CUR_INSTALL_UAH_FORMAT_STRING") : "",
					"FULL_NAME" => GetMessage("CUR_INSTALL_UAH_FULL_NAME"),
					"DEC_POINT" => GetMessage("CUR_INSTALL_UAH_DEC_POINT"),
					"THOUSANDS_VARIANT" => GetMessage("CUR_INSTALL_UAH_THOUSANDS_SEP"),
					"THOUSANDS_SEP" => false,
					"DECIMALS" => 2,
					"HIDE_ZERO" => "Y"
				);
				if (!empty($arFields))
					CCurrencyLang::Add($arFields);
			}
		}
		break;
	case 'bl':
		$iblockXMLFilePrices = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/catalog_prices_bl.xml";
		if (!CCurrency::GetByID('BYR'))
		{
			$arFields = array(
				"CURRENCY" => "BYR",
				"AMOUNT" => 36.72,
				"AMOUNT_CNT" => 10000,
				"SORT" => 500
			);
			CCurrency::Add($arFields);

			$dbLangs = CLanguage::GetList($b, $o, array("ACTIVE" => "Y"));
			while ($arLangs = $dbLangs->Fetch())
			{
				IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install_lang.php", $arLangs["LID"]);
				$arFields = array(
					"LID" => $arLangs["LID"],
					"CURRENCY" => "BYR",
					"FORMAT_STRING" => GetMessage("CUR_INSTALL_BYR_FORMAT_STRING") ? GetMessage("CUR_INSTALL_BYR_FORMAT_STRING") : "",
					"FULL_NAME" => GetMessage("CUR_INSTALL_BYR_FULL_NAME"),
					"DEC_POINT" => GetMessage("CUR_INSTALL_BYR_DEC_POINT"),
					"THOUSANDS_VARIANT" => GetMessage("CUR_INSTALL_BYR_THOUSANDS_SEP"),
					"THOUSANDS_SEP" => false,
					"DECIMALS" => 2,
					"HIDE_ZERO" => "Y"
				);
				if (!empty($arFields))
					CCurrencyLang::Add($arFields);
			}
		}
		break;
	default:
		$iblockXMLFilePrices = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/catalog_prices.xml";
		break;
}

$iblockCode = "clothes_".WIZARD_SITE_ID;
$iblockType = "catalog";

$rsIBlock = CIBlock::GetList(array(), array("XML_ID" => $iblockCode, "TYPE" => $iblockType));
$IBLOCK_CATALOG_ID = false;
if ($arIBlock = $rsIBlock->Fetch())
{
	$IBLOCK_CATALOG_ID = $arIBlock["ID"];
}
else //for old furniture catalog
{
	$rsIBlock = CIBlock::GetList(array(), array("XML_ID" => "furniture_".WIZARD_SITE_ID, "TYPE" => $iblockType));
	if ($arIBlock = $rsIBlock->Fetch())
	{
		$IBLOCK_CATALOG_ID = $arIBlock["ID"];
	}
}
if (WIZARD_INSTALL_DEMO_DATA && $IBLOCK_CATALOG_ID)
{
	$boolFlag = true;
	$arSKU = CCatalogSKU::GetInfoByProductIBlock($IBLOCK_CATALOG_ID);
	if (!empty($arSKU))
	{
		$boolFlag = CCatalog::UnLinkSKUIBlock($IBLOCK_CATALOG_ID);
		if (!$boolFlag)
		{
			$strError = "";
			if ($ex = $APPLICATION->GetException())
			{
				$strError = $ex->GetString();
			}
			else
			{
				$strError = "Couldn't unlink iblocks";
			}
			//die($strError);
		}
		$boolFlag = CIBlock::Delete($arSKU['IBLOCK_ID']);
		if (!$boolFlag)
		{
			$strError = "";
			if ($ex = $APPLICATION->GetException())
			{
				$strError = $ex->GetString();
			}
			else
			{
				$strError = "Couldn't delete offers iblock";
			}
			//die($strError);
		}
	}
	if ($boolFlag)
	{
		$boolFlag = CIBlock::Delete($IBLOCK_CATALOG_ID);
		if (!$boolFlag)
		{
			$strError = "";
			if ($ex = $APPLICATION->GetException())
			{
				$strError = $ex->GetString();
			}
			else
			{
				$strError = "Couldn't delete catalog iblock";
			}
			//die($strError);
		}
	}
	if ($boolFlag)
	{
		$IBLOCK_CATALOG_ID = false;
	}
}


$dbResultList = CCatalogGroup::GetList(array(), array("BASE" => "Y"));
if(!($dbResultList->Fetch()))
{
	$arFields = array();
	$rsLanguage = CLanguage::GetList($by, $order, array());
	while($arLanguage = $rsLanguage->Fetch())
	{
		WizardServices::IncludeServiceLang("catalog.php", $arLanguage["ID"]);
		$arFields["USER_LANG"][$arLanguage["ID"]] = GetMessage("WIZ_PRICE_NAME");
	}
	$arFields["BASE"] = "Y";
	$arFields["SORT"] = 100;
	$arFields["NAME"] = "BASE";
	$arFields["USER_GROUP"] = array(1);
	$arFields["USER_GROUP_BUY"] = array(1);
	CCatalogGroup::Add($arFields);
}

if($IBLOCK_CATALOG_ID == false)
{
	$permissions = Array(
			"1" => "X",
			"2" => "R"
		);
	$dbGroup = CGroup::GetList($by = "", $order = "", Array("STRING_ID" => "sale_administrator"));
	if($arGroup = $dbGroup -> Fetch())
	{
		$permissions[$arGroup["ID"]] = 'W';
	}
	$dbGroup = CGroup::GetList($by = "", $order = "", Array("STRING_ID" => "content_editor"));
	if($arGroup = $dbGroup -> Fetch())
	{
		$permissions[$arGroup["ID"]] = 'W';
	}

	\Bitrix\Catalog\Product\Sku::disableUpdateAvailable();
	$IBLOCK_CATALOG_ID = WizardServices::ImportIBlockFromXML(
		$iblockXMLFile,
		"clothes",
		$iblockType,
		WIZARD_SITE_ID,
		$permissions
	);
	$IBLOCK_CATALOG_ID1 = WizardServices::ImportIBlockFromXML(
		$iblockXMLFilePrices,
		"clothes",
		$iblockType."_prices",
		WIZARD_SITE_ID,
		$permissions
	);
	\Bitrix\Catalog\Product\Sku::enableUpdateAvailable();
	if ($IBLOCK_CATALOG_ID < 1)
		return;

	$_SESSION["WIZARD_CATALOG_IBLOCK_ID"] = $IBLOCK_CATALOG_ID;
}
else
{
	$arSites = array();
	$db_res = CIBlock::GetSite($IBLOCK_CATALOG_ID);
	while ($res = $db_res->Fetch())
		$arSites[] = $res["LID"];
	if (!in_array(WIZARD_SITE_ID, $arSites))
	{
		$arSites[] = WIZARD_SITE_ID;
		$iblock = new CIBlock;
		$iblock->Update($IBLOCK_CATALOG_ID, array("LID" => $arSites));
	}
}
?>