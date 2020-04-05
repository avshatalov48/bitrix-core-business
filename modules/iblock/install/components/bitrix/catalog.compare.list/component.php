<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Iblock;

if (!Loader::includeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}
/*************************************************************************
	Processing of received parameters
*************************************************************************/
unset($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

$arParams["DETAIL_URL"]=trim($arParams["DETAIL_URL"]);

$arParams["ACTION_VARIABLE"] = trim($arParams["ACTION_VARIABLE"]);
if ($arParams["ACTION_VARIABLE"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["ACTION_VARIABLE"]))
	$arParams["ACTION_VARIABLE"] = "action";

$arParams["PRODUCT_ID_VARIABLE"] = trim($arParams["PRODUCT_ID_VARIABLE"]);
if ($arParams["PRODUCT_ID_VARIABLE"] == '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PRODUCT_ID_VARIABLE"]))
	$arParams["PRODUCT_ID_VARIABLE"] = "id";

$arParams['COMPARE_URL'] = (isset($arParams['COMPARE_URL']) ? trim($arParams['COMPARE_URL']) : '');
if ($arParams['COMPARE_URL'] == '')
	$arParams['COMPARE_URL'] = "compare.php";

$arParams["NAME"]=trim($arParams["NAME"]);
if ($arParams["NAME"] == '')
	$arParams["NAME"] = "CATALOG_COMPARE_LIST";

if (!isset($_SESSION[$arParams["NAME"]]) || !is_array($_SESSION[$arParams["NAME"]]))
	$_SESSION[$arParams["NAME"]] = array();

if (!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]) || !is_array($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]))
	$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]] = array();

if (!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"]) || !is_array($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"]))
	$_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"] = array();

if (isset($_REQUEST[$arParams['ACTION_VARIABLE']]) && isset($_REQUEST[$arParams['PRODUCT_ID_VARIABLE']]))
{
	$successfulAction = true;
	$actionMessage = '';
	$actionByAjax = isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'Y';

	$productID = (int)$_REQUEST[$arParams['PRODUCT_ID_VARIABLE']];
	$resultCount = 0;
	if ($productID > 0)
	{
		switch (ToUpper($_REQUEST[$arParams['ACTION_VARIABLE']]))
		{
			case 'ADD_TO_COMPARE_LIST':
				$actionMessage = GetMessage('CP_BCCL_MESS_SUCCESSFUL_ADD_TO_COMPARE');
				if (!isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"][$productID]))
				{
					$found = true;
					$arOffers = CIBlockPriceTools::GetOffersIBlock($arParams["IBLOCK_ID"]);
					$OFFERS_IBLOCK_ID = $arOffers ? $arOffers["OFFERS_IBLOCK_ID"]: 0;

					$arSelect = array(
						"ID",
						"IBLOCK_ID",
						"IBLOCK_SECTION_ID",
						"NAME",
						"DETAIL_PAGE_URL",
					);
					$arFilter = array(
						"ID" => $productID,
						"IBLOCK_LID" => SITE_ID,
						"IBLOCK_ACTIVE" => "Y",
						"ACTIVE_DATE" => "Y",
						"ACTIVE" => "Y",
						"CHECK_PERMISSIONS" => "Y",
						"MIN_PERMISSION" => "R"
					);
					$arFilter["IBLOCK_ID"] = ($OFFERS_IBLOCK_ID > 0 ? array($arParams["IBLOCK_ID"], $OFFERS_IBLOCK_ID) : $arParams["IBLOCK_ID"]);

					$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
					$rsElement->SetUrlTemplates($arParams["DETAIL_URL"]);
					$arElement = $rsElement->GetNext();
					unset($rsElement);
					if (empty($arElement))
						$found = false;

					if ($found)
					{
						if ($arElement['IBLOCK_ID'] == $OFFERS_IBLOCK_ID)
						{
							$rsMasterProperty = CIBlockElement::GetProperty($arElement["IBLOCK_ID"], $arElement["ID"], array(), array("ID" => $arOffers["OFFERS_PROPERTY_ID"], "EMPTY" => "N"));
							$arMasterProperty = $rsMasterProperty->Fetch();
							unset($rsMasterProperty);
							if (empty($arMasterProperty))
								$found = false;
							if ($found)
							{
								$arMasterProperty['VALUE'] = (int)$arMasterProperty['VALUE'];
								if ($arMasterProperty['VALUE'] <= 0)
									$found = false;
							}
							if ($found)
							{
								$rsMaster = CIBlockElement::GetList(
									array(),
									array(
										'ID' => $arMasterProperty['VALUE'],
										'IBLOCK_ID' => $arMasterProperty['LINK_IBLOCK_ID'],
										'ACTIVE' => 'Y',
									),
									false,
									false,
									$arSelect
								);
								$rsMaster->SetUrlTemplates($arParams['DETAIL_URL']);
								$arMaster = $rsMaster->GetNext();
								unset($rsMaster);
								if (empty($arMaster))
								{
									$found = false;
								}
								else
								{
									$arMaster['NAME'] = $arElement['NAME'];
									$arElement = $arMaster;
								}
								unset($arMaster);
							}
						}
					}
					if ($found)
					{
						$sectionsList = array();
						$sectionsIterator = Iblock\SectionElementTable::getList(array(
							'select' => array('IBLOCK_SECTION_ID'),
							'filter' => array('=IBLOCK_ELEMENT_ID' => $arElement['ID'], '=ADDITIONAL_PROPERTY_ID' => null)
						));
						while ($section = $sectionsIterator->fetch())
						{
							$sectionId = (int)$section['IBLOCK_SECTION_ID'];
							$sectionsList[$sectionId] = $sectionId;
						}
						unset($section, $sectionsIterator);
						$_SESSION[$arParams['NAME']][$arParams['IBLOCK_ID']]['ITEMS'][$productID] = array(
							'ID' => $arElement['ID'],
							'~ID' => $arElement['~ID'],
							'IBLOCK_ID' => $arElement['IBLOCK_ID'],
							'~IBLOCK_ID' => $arElement['~IBLOCK_ID'],
							'IBLOCK_SECTION_ID' => $arElement['IBLOCK_SECTION_ID'],
							'~IBLOCK_SECTION_ID' => $arElement['~IBLOCK_SECTION_ID'],
							'NAME' => $arElement['NAME'],
							'~NAME' => $arElement['~NAME'],
							'DETAIL_PAGE_URL' => $arElement['DETAIL_PAGE_URL'],
							'~DETAIL_PAGE_URL' => $arElement['~DETAIL_PAGE_URL'],
							'SECTIONS_LIST' => $sectionsList,
							'PARENT_ID' => $productID,
							'DELETE_URL' => htmlspecialcharsbx($APPLICATION->GetCurPageParam(
								$arParams['ACTION_VARIABLE']."=DELETE_FROM_COMPARE_LIST&".$arParams['PRODUCT_ID_VARIABLE']."=".$productID,
								array($arParams['ACTION_VARIABLE'], $arParams['PRODUCT_ID_VARIABLE'])
							))
						);
						unset($sectionsList, $arElement);
						$resultCount = count($_SESSION[$arParams['NAME']][$arParams['IBLOCK_ID']]['ITEMS']);
					}
					else
					{
						$successfulAction = false;
						$actionMessage = GetMessage('CP_BCCL_ERR_MESS_PRODUCT_NOT_FOUND');
					}
				}
				break;
			case 'DELETE_FROM_COMPARE_LIST':
				if (isset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"][$productID]))
					unset($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"][$productID]);
				$actionMessage = GetMessage('CP_BCCL_MESS_SUCCESSFUL_DELETE_FROM_COMPARE');
				$resultCount = count($_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"]);
				break;
		}
	}
	else
	{
		$successfulAction = false;
		$actionMessage = GetMessage('CP_BCCL_ERR_MESS_PRODUCT_NOT_FOUND');
	}

	if ($actionByAjax)
	{
		if ($successfulAction)
			$addResult = array('STATUS' => 'OK', 'MESSAGE' => $actionMessage, 'ID' => $productID, 'COUNT' => $resultCount);
		else
			$addResult = array('STATUS' => 'ERROR', 'MESSAGE' => $actionMessage);

		$APPLICATION->RestartBuffer();
		header('Content-Type: application/json');
		echo Main\Web\Json::encode($addResult);
		die();
	}
}

$arResult = $_SESSION[$arParams["NAME"]][$arParams["IBLOCK_ID"]]["ITEMS"];

$this->includeComponentTemplate();