<?
/** @global CMain $APPLICATION */
/** @global CUser $USER */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

use Bitrix\Main\Loader;
use Bitrix\Sale\Location;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale;

Loader::includeModule('sale');

$crmMode = (defined("BX_PUBLIC_MODE") && BX_PUBLIC_MODE && array_key_exists("CRM_MANAGER_USER_ID", $_REQUEST));

if ($crmMode)
{
	CUtil::DecodeUriComponent($_GET);
	CUtil::DecodeUriComponent($_POST);

	echo '<link rel="stylesheet" type="text/css" href="/bitrix/themes/.default/sale.css" />';
}

$bUseCatalog = Loader::includeModule('catalog');
$bUseIblock = $bUseCatalog;

IncludeModuleLangFile(__FILE__);
ClearVars();

$ID = 0;
if (isset($_REQUEST['ID']))
	$ID = (int)$_REQUEST['ID'];
if ($ID < 0)
	$ID = 0;
$COUNT_RECOM_BASKET_PROD = 2;
$arOrderOldTmp = false;
define("PROP_COUNT_LIMIT", 21);

$arFilter = array(
	"LID" => LANG,
	"ID" => "N"
);

$str_PRICE = 0;
$str_DISCOUNT_VALUE = 0;

if ($ID > 0)
{
	$dbOrder = CSaleOrder::GetList(
		array(),
		array("ID" => $ID),
		false,
		false,
		array()
	);

	if ($arOrderOldTmp = $dbOrder->ExtractFields("str_"))
		$arFilter["ID"] = $arOrderOldTmp["STATUS_ID"];
}

$arStatusList = false;
$arGroupByTmpSt = false;

$arUserGroups = $USER->GetUserGroupArray();
$intUserID = (int)$USER->GetID();

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if ($saleModulePermissions < "W")
{
	$arFilter["GROUP_ID"] = $arUserGroups;
	$arFilter["PERM_UPDATE"] = "Y";
	$arGroupByTmpSt = array("ID", "NAME", "MAX" => "PERM_UPDATE");
}

$dbStatusList = CSaleStatus::GetList(
	array(),
	$arFilter,
	$arGroupByTmpSt,
	false,
	array("ID", "NAME")
);
$arStatusList = $dbStatusList->Fetch();

if ($saleModulePermissions == "D" || ($saleModulePermissions < "W" && $arStatusList["PERM_UPDATE"] != "Y"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$errorMessage = "";

/*****************************************************************************/
/********************* UTIL AJAX *********************************************/
/*****************************************************************************/

if (CSaleLocation::isLocationProEnabled())
{
	if (isset($_REQUEST['ACT']))
	{
		$result = array(
			'ERRORS' => array(),
			'DATA' => array()
		);

		if($_REQUEST['ACT'] == 'GET_LOC_BY_ZIP')
		{
			$zip = '';
			if (isset($_REQUEST['ZIP']))
				$zip = (string)$_REQUEST['ZIP'];
			if ($zip == '')
			{
				$result['ERRORS'] = array('Not found');
			}
			else
			{
				$item = Location\Admin\LocationHelper::getLocationsByZip($zip, array('limit' => 1))->fetch();

				if (!isset($item['LOCATION_ID']))
					$result['ERRORS'] = array('Not found');
				else
				{
					$siteId = '';
					if(!empty($_REQUEST['SITE_ID']))
						$siteId = (string)$_REQUEST['SITE_ID'];
					elseif (defined('SITE_ID'))
						$siteId = SITE_ID;

					$result['DATA']['ID'] = (int)$item['LOCATION_ID'];

					if ($siteId != '')
					{
						if (!Location\SiteLocationTable::checkConnectionExists($siteId, $result['DATA']['ID']))
							$result['ERRORS'] = array('Found, but not connected');
					}
				}
			}
		}
		elseif($_REQUEST['ACT'] == 'GET_ZIP_BY_LOC')
		{
			if(!intval($_REQUEST['LOC']))
				$result['ERRORS'] = array('No location id passed');
			else
			{
				$item = \Bitrix\Sale\Location\LocationTable::getList(
					array(
						'filter' => array(
							'=ID' => intval($_REQUEST['LOC']),
							'=EXTERNAL.SERVICE.CODE' => 'ZIP'
						),
						'select' => array(
							'ZIP' => 'EXTERNAL.XML_ID'
						)
					)
				)->fetch();

				if($item['ZIP'] <> '')
				{
					$result['DATA']['ZIP'] = $item['ZIP'];
				}
				else
				{
					$result['ERRORS'] = array('None were found');
				}
			}
		}

		header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
		print(CUtil::PhpToJSObject(array(
			'result' => empty($result['ERRORS']),
			'errors' => $result['ERRORS'],
			'data' => $result['DATA']
		), false, false, true));
		die();
	}
}

/*****************************************************************************/
/********************* ORDER FUNCTIONS ***************************************/
/*****************************************************************************/

if (isset($_REQUEST['dontsave']) && $_REQUEST['dontsave'] == 'Y')
{
	$intLockUserID = 0;
	$strLockTime = '';
	DiscountCouponsManager::clear(true);
	if (!CSaleOrder::IsLocked($ID, $intLockUserID, $strLockTime))
		CSaleOrder::UnLock($ID);
	LocalRedirect("sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
}
if ($saleModulePermissions >= "W" && isset($_REQUEST['unlock']) && 'Y' == $_REQUEST['unlock'])
{
	$intLockUserID = 0;
	$strLockTime = '';
	DiscountCouponsManager::clear(true);
	if (CSaleOrder::IsLocked($ID, $intLockUserID, $strLockTime))
		CSaleOrder::UnLock($ID);
	LocalRedirect("sale_order_new.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
}

// include functions
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");


$callbackList = array(
	'CALLBACK_FUNC',
	'ORDER_CALLBACK_FUNC',
	'CANCEL_CALLBACK_FUNC',
	'PAY_CALLBACK_FUNC',
	'PRODUCT_PROVIDER_CLASS'
);
/*****************************************************************************/
/**************************** SAVE ORDER *************************************/
/*****************************************************************************/
$bVarsFromForm = false;

if (
	$_SERVER['REQUEST_METHOD'] == 'POST'
	&& check_bitrix_sessid()
	&& $saleModulePermissions >= 'U'
	&& isset($_POST['save_order_data']) && $_POST['save_order_data'] == 'Y'
	&& empty($_POST['dontsave']))
{
	$ID = intval($ID);
	$recalcOrder = "N";
	$isOrderConverted = \Bitrix\Main\Config\Option::get("main", "~sale_converted_15", 'Y');

	if (defined("SALE_DEBUG") && SALE_DEBUG)
		CSaleHelper::WriteToLog("order_new.php", array("POST" => $_POST), "ORNW1");

	// buyer type, new or existing
	$btnNewBuyer = "N";
	if ($btnTypeBuyer == "btnBuyerNew")
		$btnNewBuyer = "Y";

	$useStores = (isset($_POST["storeCount"]) && intval($_POST["storeCount"]) > 0) ? true : false;

	if ($LID == '')
		$errorMessage .= GetMessage("SOE_EMPTY_SITE")."<br>";

	$BASE_LANG_CURRENCY = CSaleLang::GetLangCurrency($LID);

	$str_PERSON_TYPE_ID = intval($buyer_type_id);
	if ($str_PERSON_TYPE_ID <= 0)
		$errorMessage .= GetMessage("SOE_EMPTY_PERS_TYPE")."<br>";

	if (($str_PERSON_TYPE_ID > 0) && !($arPersonType = CSalePersonType::GetByID($str_PERSON_TYPE_ID)))
		$errorMessage .= GetMessage("SOE_PERSON_NOT_FOUND")."<br>";

	$str_STATUS_ID = trim($STATUS_ID);
	if ($str_STATUS_ID <> '')
	{
		if ($saleModulePermissions < "W")
		{
			$dbStatusList = CSaleStatus::GetList(
				array(),
				array(
					"GROUP_ID" => $arUserGroups,
					"PERM_STATUS" => "Y",
					"ID" => $str_STATUS_ID
				),
				array("ID", "MAX" => "PERM_STATUS"),
				false,
				array("ID")
			);
			if (!$dbStatusList->Fetch())
				$errorMessage .= str_replace("#STATUS_ID#", $str_STATUS_ID, GetMessage("SOE_NO_STATUS_PERMS"))."<br>";
		}
	}

	$str_PAY_SYSTEM_ID = intval($PAY_SYSTEM_ID);
	if ($str_PAY_SYSTEM_ID <= 0)
		$errorMessage .= GetMessage("SOE_PAYSYS_EMPTY")."<br>";
	if (($str_PAY_SYSTEM_ID > 0) && !($arPaySys = CSalePaySystem::GetByID($str_PAY_SYSTEM_ID, $str_PERSON_TYPE_ID)))
		$errorMessage .= GetMessage("SOE_PAYSYS_NOT_FOUND")."<br>";

	if (empty($_POST["PRODUCT"]))
		$errorMessage .= GetMessage("SOE_EMPTY_ITEMS")."<br>";

	if (isset($DELIVERY_ID) AND $DELIVERY_ID != "")
	{
		$str_DELIVERY_ID = trim($DELIVERY_ID);
		$PRICE_DELIVERY = floatval($PRICE_DELIVERY);
	}

	$arCoupon = (!empty($_POST['COUPON']) ? fGetCoupon($_POST['COUPON']) : array());
	if (!empty($arCoupon))
		$recalcOrder = "Y";

	if (array_key_exists('ADDITIONAL_INFO', $_POST))
	{
		$str_ADDITIONAL_INFO = trim($_POST["ADDITIONAL_INFO"]);
	}

	$str_COMMENTS = trim($_POST["COMMENTS"]);

	if (isset($_POST["btnTypeBuyer"]) && $_POST["btnTypeBuyer"] == "btnBuyerNew")
	{
		$user_id = '';
		unset($user_profile);
	}

	$profileName = "";
	if (isset($user_profile) && $user_profile != "" && $btnNewBuyer == "N")
		$userProfileID = intval($user_profile);

	//array field send mail
	$FIO = "";
	$rsUser = CUser::GetByID($user_id);
	if($arUser = $rsUser->Fetch())
	{
		if ($arUser["LAST_NAME"] != "")
			$FIO .= $arUser["LAST_NAME"]." ";
		if ($arUser["NAME"] != "")
			$FIO .= $arUser["NAME"];
	}

	$arUserEmail = array("PAYER_NAME" => $FIO, "USER_EMAIL" => $arUser["EMAIL"]);

	$BREAK_NAME = isset($_POST["BREAK_NAME"]) ? $_POST["BREAK_NAME"] : "";
	if ($BREAK_NAME == GetMessage('NEWO_BREAK_NAME'))
		$BREAK_NAME = "";

	$BREAK_LAST_NAME = isset($_POST["BREAK_LAST_NAME"]) ? $_POST["BREAK_LAST_NAME"] : "";
	if ($BREAK_LAST_NAME == GetMessage('NEWO_BREAK_LAST_NAME'))
		$BREAK_LAST_NAME = "";

	$BREAK_SECOND_NAME = isset($_POST["BREAK_SECOND_NAME"]) ? $_POST["BREAK_SECOND_NAME"] : "";
	if ($BREAK_SECOND_NAME == GetMessage('NEWO_BREAK_SECOND_NAME'))
		$BREAK_SECOND_NAME = "";

	// checking order properties
	if ($errorMessage == '')
	{
		$arOrderPropsValues = array();

		$arPropFilter = array(
			"PERSON_TYPE_ID" => $str_PERSON_TYPE_ID,
			"ACTIVE" => "Y"
		);

		if ($str_PAY_SYSTEM_ID != 0)
		{
			$arPropFilter["RELATED"]["PAYSYSTEM_ID"] = $str_PAY_SYSTEM_ID;
			$arPropFilter["RELATED"]["TYPE"] = "WITH_NOT_RELATED";
		}

		if ($str_DELIVERY_ID <> '')
		{
			$arPropFilter["RELATED"]["DELIVERY_ID"] = $str_DELIVERY_ID;
			$arPropFilter["RELATED"]["TYPE"] = "WITH_NOT_RELATED";
		}

		$orderPropList = array();
		$orderFileProps = array();
		$orderFilePropsValue = array();
		$dbOrderProps = CSaleOrderProps::GetList(
			array("ID" => "ASC"),
			$arPropFilter,
			false,
			false,
			array("ID", "NAME", "TYPE", "REQUIED", "IS_LOCATION", "IS_EMAIL", "IS_PROFILE_NAME", "IS_PAYER", "IS_LOCATION4TAX", "CODE", "SORT")
		);
		while ($arOrderProps = $dbOrderProps->Fetch())
		{
			$orderPropList[$arOrderProps['ID']] = $arOrderProps;
			if ($arOrderProps['TYPE'] == 'FILE')
				$orderFileProps[$arOrderProps['ID']] = $arOrderProps;
		}

		if ($crmMode && $ID > 0 && !empty($orderFileProps))
		{
			$rsFileOrderProps = CSaleOrderPropsValue::GetList(
				array(),
				array("ORDER_ID" => $ID, 'ORDER_PROPS_ID' => array_keys($orderFileProps)),
				false,
				false,
				array("ID", "ORDER_ID", "ORDER_PROPS_ID", "VALUE")
			);
			while ($oneFileOrderProp = $rsFileOrderProps->Fetch())
			{
				if (!isset($orderFilePropsValue[$oneFileOrderProp['ORDER_PROPS_ID']]))
					$orderFilePropsValue[$oneFileOrderProp['ORDER_PROPS_ID']] = array();
				$oneFileOrderProp['VALUE'] = trim($oneFileOrderProp['VALUE']);
				if ($oneFileOrderProp['VALUE'] != '')
				{
					$orderFilePropsValue[$oneFileOrderProp['ORDER_PROPS_ID']] = explode(', ', $oneFileOrderProp['VALUE']);
				}
			}
			unset($oneFileOrderProp, $rsFileOrderProps);
		}

		foreach ($orderPropList as $arOrderProps)
		{
			if (!is_array(${"ORDER_PROP_".$arOrderProps["ID"]}))
				$curVal = trim($_POST["ORDER_PROP_".$arOrderProps["ID"]]);
			else
				$curVal = $_POST["ORDER_PROP_".$arOrderProps["ID"]];

			if ($arOrderProps["TYPE"] == "LOCATION")
			{
				$curVal = $_POST["CITY_ORDER_PROP_".$arOrderProps["ID"]];

				$regId = $_POST["REGION_ORDER_PROP_".$arOrderProps["ID"]."CITY_ORDER_PROP_".$arOrderProps["ID"]];
				$countryId = $_POST["ORDER_PROP_".$arOrderProps["ID"]."CITY_ORDER_PROP_".$arOrderProps["ID"]];

				if (intval($curVal) <= 0 && intval($regId) > 0)
				{
					$dbLoc = CSaleLocation::GetList(array(), array("REGION_ID" => $regId, "CITY_ID" => false), false, false, array("ID", "REGION_ID", "CITY_ID"));
					if($arLoc = $dbLoc->Fetch())
					{
						$curVal = $arLoc["ID"];
					}
				}
				if(intval($curVal) <= 0 && intval($countryId) > 0)
				{
					$dbLoc = CSaleLocation::GetList(array(), array("COUNTRY_ID" => $countryId, "REGION_ID" => false, "CITY_ID" => false), false, false, array("ID", "COUNTRY_ID", "REGION_ID", "CITY_ID"));
					if($arLoc = $dbLoc->Fetch())
					{
						$curVal = $arLoc["ID"];
					}
				}
			}

			if ($arOrderProps["TYPE"] == "FILE")
			{
				$curVal = array();
				$arReplacedFiles2Delete = array();
				if ($crmMode && $ID > 0)
				{
					if (isset($orderFilePropsValue[$arOrderProps["ID"]]))
					{
						foreach ($orderFilePropsValue[$arOrderProps["ID"]] as $keyFile => $valueFile)
						{
							$curVal[] = array('file_id' => $valueFile);
						}
					}
				}
				else
				{
					if (array_key_exists("ORDER_PROP_".$arOrderProps["ID"], $_FILES) && is_array($_FILES["ORDER_PROP_".$arOrderProps["ID"]]))
					{
						foreach ($_FILES["ORDER_PROP_".$arOrderProps["ID"]] as $param_name => $arValues)
						{
							if (is_array($arValues))
							{
								$i = 0;
								foreach ($arValues as $nIndex => $val)
								{
									if (mb_substr($nIndex, 1) != "undefined")
									{
										if (mb_substr($nIndex, 0, 1) == "n" && $val <> '') // if new file is added
										{
											$curVal[$i][$param_name] = $val;
										}
										else // if there is existing file id already
										{
											if (intval($nIndex) > 0)
											{
												if ($param_name == "name" && $val == '') // no file replacement
												{
													$curVal[$i]["file_id"] = $nIndex;

													// del flag
													if (isset($_POST["ORDER_PROP_".$arOrderProps["ID"]."_del"]))
													{
														if (array_key_exists($nIndex, $_POST["ORDER_PROP_".$arOrderProps["ID"]."_del"]))
															$curVal[$i]["del"] = $_POST["ORDER_PROP_".$arOrderProps["ID"]."_del"][$nIndex];
													}
												}
												elseif ($_FILES["ORDER_PROP_".$arOrderProps["ID"]]["name"][$nIndex] <> '') // replacement file data
												{
													$curVal[$i][$param_name] = $val;
													if (!in_array($nIndex, $arReplacedFiles2Delete))
														$arReplacedFiles2Delete[] = $nIndex;
												}
											}
										}
									}
									$i++;
								}
							}
						}

						//delete replaced files
						foreach ($arReplacedFiles2Delete as $id => $file_id)
						{
							$arReplace = array("file_id" => $file_id, "del" => "Y");
							$curVal[] = $arReplace;
						}
					}
				}
			}

			if ($arOrderProps["IS_PAYER"] == "Y")
			{
				if ($curVal == '' && $BREAK_NAME <> '' && $BREAK_LAST_NAME <> '')
					$curVal = $BREAK_NAME." ".$BREAK_LAST_NAME;
			}

			if ($arOrderProps["IS_EMAIL"] == "Y")
			{
				$arUserEmail["USER_EMAIL"] = trim($curVal);
			}

			if ($arOrderProps["IS_PROFILE_NAME"] == "Y")
			{
				$profileName = $curVal;
			}

			if (
				($arOrderProps["IS_LOCATION"]=="Y" || $arOrderProps["IS_LOCATION4TAX"]=="Y")
				&& intval($curVal) <= 0
				||
				($arOrderProps["IS_PROFILE_NAME"]=="Y" || $arOrderProps["IS_PAYER"]=="Y")
				&& $curVal == ''
				||
				$arOrderProps["REQUIED"]=="Y"
				&& $arOrderProps["TYPE"]=="LOCATION"
				&& intval($curVal) <= 0
				||
				$arOrderProps["REQUIED"]=="Y"
				&& ($arOrderProps["TYPE"]=="TEXT" || $arOrderProps["TYPE"]=="TEXTAREA" || $arOrderProps["TYPE"]=="RADIO" || $arOrderProps["TYPE"]=="SELECT")
				&& $curVal == ''
				||
				($arOrderProps["REQUIED"]=="Y"
				&& $arOrderProps["TYPE"]=="MULTISELECT"
				&& empty($curVal))
				||
				($arOrderProps["REQUIED"]=="Y"
				&& $arOrderProps["TYPE"]=="FILE"
				&& empty($curVal))
				)
			{
				$errorMessage .= str_replace("#NAME#", $arOrderProps["NAME"], GetMessage("SOE_EMPTY_PROP"))."<br>";
			}

			if ($arOrderProps["TYPE"] == "MULTISELECT")
			{
				$curVal = "";
				$countOrderProp = count($_POST["ORDER_PROP_".$arOrderProps["ID"]]);
				for ($i = 0; $i < $countOrderProp; $i++)
				{
					if ($i > 0)
						$curVal .= ",";

					$curVal .= $_POST["ORDER_PROP_".$arOrderProps["ID"]][$i];
				}
			}

			if ($arOrderProps["TYPE"] == "CHECKBOX" && $curVal == '' && $arOrderProps["REQUIED"] != "Y")
			{
				$curVal = "N";
			}

			$arOrderPropsValues[$arOrderProps["ID"]] = $curVal;
		}
	}

	//create a new user
	if ($btnNewBuyer == "Y" && $errorMessage == '')
	{
		if ($NEW_BUYER_EMAIL == '')
		{
			$emailId = '';
			$dbProperties = CSaleOrderProps::GetList(
				array("ID" => "ASC"),
				array("PERSON_TYPE_ID" => $str_PERSON_TYPE_ID, "ACTIVE" => "Y", "IS_EMAIL" => "Y", "RELATED" => false),
				false,
				false,
				array("ID")
			);
			while ($arProperties = $dbProperties->Fetch())
			{
				if ($emailId == '')
					$emailId = $arProperties["ID"];

				if ($arProperties["REQUIED"] == "Y")
					$emailId = $arProperties["ID"];
			}
			$NEW_BUYER_EMAIL = ${"ORDER_PROP_".$emailId};
		}

		if ($NEW_BUYER_EMAIL == '')
			$errorMessage .= GetMessage("NEWO_BUYER_REG_ERR_MAIL");

		//take default value PHONE for register user
		$dbOrderProps = CSaleOrderProps::GetList(
			array(),
			array("PERSON_TYPE_ID" => $str_PERSON_TYPE_ID, "ACTIVE" => "Y", "CODE" => "PHONE", "RELATED" => false),
			false,
			false,
			array("ID")
		);
		$arOrderProps = $dbOrderProps->Fetch();
		$NEW_BUYER_PHONE = "";
		if (!empty($arOrderProps))
			$NEW_BUYER_PHONE = trim($_POST["ORDER_PROP_".$arOrderProps["ID"]]);

		$NEW_BUYER_NAME = isset($_POST["NEW_BUYER_NAME"]) ? $_POST["NEW_BUYER_NAME"] : "";
		$NEW_BUYER_LAST_NAME = isset($_POST["NEW_BUYER_LAST_NAME"]) ? $_POST["NEW_BUYER_LAST_NAME"] : "";
		$NEW_BUYER_SECOND_NAME = isset($_POST["NEW_BUYER_SECOND_NAME"]) ? $_POST["NEW_BUYER_SECOND_NAME"] : "";

		if ($NEW_BUYER_NAME == "" && $NEW_BUYER_LAST_NAME == "")
		{
			$NEW_BUYER_NAME = $BREAK_NAME;
			$NEW_BUYER_LAST_NAME = $BREAK_LAST_NAME;
			$NEW_BUYER_SECOND_NAME = $BREAK_SECOND_NAME;
		}

		if ($NEW_BUYER_NAME == "" || $NEW_BUYER_LAST_NAME == "")
			$errorMessage .= GetMessage("NEWO_BUYER_REG_ERR_NAME")."<br>";

		$NEW_BUYER_FIO = $NEW_BUYER_LAST_NAME." ".$NEW_BUYER_NAME." ".$NEW_BUYER_SECOND_NAME;
		$arUserEmail["PAYER_NAME"] = $NEW_BUYER_FIO;

		if ($errorMessage == '')
		{
			$userRegister = array(
				"NAME" => $NEW_BUYER_NAME,
				"LAST_NAME" => $NEW_BUYER_LAST_NAME,
				"SECOND_NAME" => $NEW_BUYER_SECOND_NAME,
				"PERSONAL_MOBILE" => $NEW_BUYER_PHONE
			);

			$arPersonal = array("PERSONAL_MOBILE" => $NEW_BUYER_PHONE);

			$user_id = CSaleUser::DoAutoRegisterUser($NEW_BUYER_EMAIL, $userRegister, $LID, $arErrors, $arPersonal);
			if (!empty($arErrors))
			{
				foreach($arErrors as $val)
					$errorMessage .= $val["TEXT"];
			}
			else
			{
				$userProfileID = 0;
				$rsUser = CUser::GetByID($user_id);
				$arUser = $rsUser->Fetch();

				$userNew = str_replace("#FIO#", "(".$arUser["LOGIN"].")".(($arUser["NAME"] != "") ? " ".$arUser["NAME"] : "").(($arUser["LAST_NAME"] != "") ? " ".$arUser["LAST_NAME"] : ""), GetMessage("NEWO_BUYER_REG_OK"));
			}
		}
	}

	if (!isset($userProfileID))
		$profileName = "";

	$str_USER_ID = intval($user_id);
	if ($str_USER_ID <= 0 && $errorMessage == '')
	{
		$str_USER_ID = "";
		$errorMessage .= GetMessage("SOE_EMPTY_USER")."<br>";
	}
	elseif ($str_USER_ID > 0 && $errorMessage == '')
	{
		$rsUser = CUser::GetByID($str_USER_ID);
		if (!$rsUser->Fetch())
			$errorMessage .= GetMessage("NEWO_ERR_EMPTY_USER")."<br>";
	}

	// tmp hack to check if any product quantity is not enough to buy before saving data
	if (isset($_POST["PRODUCT"]) && !empty($_POST["PRODUCT"]))
	{
		foreach ($_POST["PRODUCT"] as $key => $val)
		{
			if (intval($val["PRODUCT_ID"]) > 0 && $val["MODULE"] == 'catalog' && $bUseCatalog)
			{
				if ($arCatalogProduct = CCatalogProduct::GetByID($val["PRODUCT_ID"]))
				{
					$dbBasketItems = CSaleBasket::GetList(array(), array("ID" => $val["ID"]), false, false, array('QUANTITY'));
					$arItems = $dbBasketItems->Fetch();

					if (floatval($val["QUANTITY"]) > floatval($arItems["QUANTITY"])
						&& $arCatalogProduct["CAN_BUY_ZERO"]!="Y"
						&& ($arCatalogProduct["QUANTITY_TRACE"]=="Y")
						//&& floatval($arCatalogProduct["QUANTITY"])<=0)
						//TODO - QUANTITY_RESERVED
						&& floatval($val["QUANTITY"] - $arItems["QUANTITY"]) > floatval($arCatalogProduct["QUANTITY"] + $arCatalogProduct["QUANTITY_RESERVED"])
					)
					{
						$errorMessage .= str_replace("#NAME#", $val['NAME'], GetMessage("NEWO_ERR_PRODUCT_NULL_BALANCE"));
					}
				}
			}
		}
	}

	// saving
	if ($errorMessage == '')
	{
		$couponsMode = ($ID > 0 ? DiscountCouponsManager::MODE_ORDER : DiscountCouponsManager::MODE_MANAGER);
		$couponsParams = array(
			'userId' => $str_USER_ID
		);
		if ($ID > 0)
			$couponsParams['orderId'] = $ID;
		DiscountCouponsManager::init($couponsMode, $couponsParams, false);
		unset($couponsParams, $couponsMode);

		if ($isOrderConverted != 'N')
		{
			$discountMode = ($ID > 0 ? Sale\Compatible\DiscountCompatibility::MODE_ORDER : Sale\Compatible\DiscountCompatibility::MODE_MANAGER);
			$discountParams = array(
				'SITE_ID' => $LID,
				'CURRENCY' => $BASE_LANG_CURRENCY
			);
			if ($ID > 0)
				$discountParams['ORDER_ID'] = $ID;
			Sale\Compatible\DiscountCompatibility::init($discountMode, $discountParams);
			unset($discountParams, $discountMode);
		}
		//send new user mail
		if ($btnNewBuyer == "Y" && $userNew <> '')
			CUser::SendUserInfo($str_USER_ID, $LID, $userNew, true);

		$arShoppingCart = array();
		$arOrderProductPrice = fGetUserShoppingCart($_POST["PRODUCT"], $LID, $recalcOrder);

		foreach ($arOrderProductPrice as &$arItem)
		{
			if ($arItem['BASKET_ID'] > 0)
			{
				$basketIdList[] = $arItem['BASKET_ID'];
			}
			else
			{
				if (empty($arItem['BASKET_ID']) && empty($arItem['ID']))
				{
					$module = trim($arItem['MODULE']);
					if (strval($module) != '')
					{
						Loader::includeModule($module);
					}

					foreach ($callbackList as $callbackName)
					{
						$callbackFieldName = (isset($arItem[$callbackName]) ? $arItem[$callbackName] : '');
						if ((!isset($callbackFieldName) && strval($callbackFieldName) == "")
								|| (!class_exists($callbackFieldName) && !function_exists($callbackFieldName)))
						{
							$arItem[$callbackName] = '';
						}


					}
				}
			}
			$arItem["ID_TMP"] = $arItem["ID"];
			unset($arItem["ID"]);
		}
		unset($arItem);

		if (!empty($basketIdList))
		{
			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

			/** @var Sale\Basket $basketClass */
			$basketClass = $registry->getBasketClassName();

			$basketRes = $basketClass::getList(
				array(
					'filter' => array(
						'=ID' => $basketIdList
					),
					'select' => array(
						'ID',
						'CALLBACK_FUNC',
						'ORDER_CALLBACK_FUNC',
						'CANCEL_CALLBACK_FUNC',
						'PAY_CALLBACK_FUNC',
						'PRODUCT_PROVIDER_CLASS'
					)
				)
			);
			while($data = $basketRes->fetch())
			{
				$basketList[$data['ID']] = $data;
			}

			foreach ($arOrderProductPrice as &$itemData)
			{
				if (!empty($basketList[$itemData['BASKET_ID']]))
				{
					foreach ($callbackList as $callbackName)
					{
						$itemData[$callbackName] = $basketList[$itemData['BASKET_ID']][$callbackName];
					}
				}
			}
			unset($itemData);
		}

		$tmpOrderId = ($ID == 0) ? 0 : $ID;

		$arOrderOptions = array(
			'CART_FIX' => (isset($_REQUEST['CART_FIX']) && 'Y' == $_REQUEST['CART_FIX'] ? 'Y' : 'N')
		);

		if ('Y' == $arOrderOptions['CART_FIX'])
		{
			$arShoppingCart = $arOrderProductPrice;
		}
		else
		{
			$arShoppingCart = CSaleBasket::DoGetUserShoppingCart($LID, $str_USER_ID, $arOrderProductPrice, $arErrors, $arCoupon, $tmpOrderId);
		}

		foreach ($arOrderProductPrice as $key => &$arItem)
		{
			$arItem["ID"] = $arItem["ID_TMP"];
			unset($arItem["ID_TMP"]);

			//$arShoppingCart[$key]["ID"] = $arItem["ID"];
		}
		unset($arItem);

		foreach ($arShoppingCart as &$v)
		{
			$v["ID"] = $v["ID_TMP"];
			unset($v["ID_TMP"]);
		}
		unset($v);

		$arErrors = array();
		$arWarnings = array();

		if (!empty($arShoppingCart))
		{
			foreach($arOrderProductPrice as $key => $val)
			{
				if ($val["NAME"] != $arShoppingCart[$key]["NAME"] AND $val["PRODUCT_ID"] == $arShoppingCart[$key]["PRODUCT_ID"])
					$arShoppingCart[$key]["NAME"] = $val["NAME"];

				if ($val["NOTES"] != '' && $val["NOTES"] != $arShoppingCart[$key]["NOTES"] AND $val["PRODUCT_ID"] == $arShoppingCart[$key]["PRODUCT_ID"])
					$arShoppingCart[$key]["NOTES"] = $val["NOTES"];
			}
		}

		//order parameters
		$arOrder = CSaleOrder::DoCalculateOrder(
			$LID,
			$str_USER_ID,
			$arShoppingCart,
			$str_PERSON_TYPE_ID,
			$arOrderPropsValues,
			$str_DELIVERY_ID,
			$str_PAY_SYSTEM_ID,
			$arOrderOptions,
			$arErrors,
			$arWarnings
		);

		//change delivery price
		if (floatval($arOrder["DELIVERY_PRICE"]) != $PRICE_DELIVERY)
		{
			$arOrder["PRICE"] = ($arOrder["PRICE"] - $arOrder["DELIVERY_PRICE"]) + $PRICE_DELIVERY;
			$arOrder["DELIVERY_PRICE"] = $PRICE_DELIVERY;
			$arOrder["PRICE_DELIVERY"] = $PRICE_DELIVERY;
		}

		if (!isset($arOrder["TRACKING_NUMBER"]) || $arOrder["TRACKING_NUMBER"] != $TRACKING_NUMBER)
			$arOrder["TRACKING_NUMBER"] = $TRACKING_NUMBER;

		if (empty($arShoppingCart) && !empty($arOrderProductPrice))
			$errorMessage .= GetMessage('NEWO_ERR_BASKET_NULL')."<br>";
		else
		{
			if (!empty($arWarnings))
			{
				foreach ($arWarnings as $val)
					$errorMessage .= $val["TEXT"]."<br>";
			}
			if (!empty($arErrors))
			{
				foreach ($arErrors as $val)
					$errorMessage .= $val["TEXT"]."<br>";
			}
		}
	}

	//prelimenary barcode and store quantity saving
	if ($errorMessage == '')
	{
		// todo: necessary to handle situation with 3 stores - 1, 2, 1
		// saving store / barcode data (calculating which records should be deleted / added / updated)
		$arStoreBarcodeOrderFormData = array();

		if ($useStores && (!isset($_POST["ORDER_DEDUCTED"]) || $_POST["ORDER_DEDUCTED"] == "N") && ($DEDUCTED == "Y" || $hasSavedBarcodes)) //not deducted yet
		{
			$bErrorFound = false;
			foreach ($_REQUEST["PRODUCT"] as $basketId => &$arProduct)
			{
				if (CSaleBasketHelper::isSetParent($arProduct))
					continue;

				if (!empty($arProduct["STORES"]) && is_array($arProduct["STORES"]))
				{
					//check if store info contains all necessary fields
					foreach ($arProduct["STORES"] as $recId => $arRecord)
					{
						if (!isset($arRecord["STORE_ID"]) || intval($arRecord["STORE_ID"]) < 0 || (!isset($arRecord["QUANTITY"])) || intval($arRecord["QUANTITY"]) < 0)
						{
							$errorMessage .= GetMessage("NEWO_ERR_STORE_WRONG_INFO_SAVING", array("#PRODUCT_NAME#" => $arProduct["NAME"]))."<br>";
							$bErrorFound = true;
							break;
						}
					}
					if ($bErrorFound)
						break;

					//if array item is in the basket, not newly added product
					if (isset($arProduct["BASKET_ID"]) && intval($arProduct["BASKET_ID"]) > 0)
					{
						if ($arProduct["BARCODE_MULTI"] == "N") //saving only store quantity info
						{
							$arStoreSavedRecords = array();
							$arStoreFormRecords = array();
							$arStoreIDToAdd = array();
//							$arStoreIDToDelete = array();

							$dbStoreBarcode = CSaleStoreBarcode::GetList(
								array(),
								array(
									"BASKET_ID" => $arProduct["BASKET_ID"],
								),
								false,
								false,
								array("ID", "BASKET_ID", "BARCODE", "QUANTITY", "STORE_ID")
							);
							while ($arStoreBarcode = $dbStoreBarcode->GetNext())
							{
								$arStoreSavedRecords[$arStoreBarcode["STORE_ID"]] = $arStoreBarcode;
							}

							foreach ($arProduct["STORES"] as $index => $arStore)
							{
								$arStoreFormRecords[$arStore["STORE_ID"]] = $arStore;

								if (!in_array($arStore["STORE_ID"], array_keys($arStoreSavedRecords)))
									$arStoreIDToAdd[] = $arStore["STORE_ID"];
							}

//							foreach ($arStoreSavedRecords as $index => $arRecord)
//							{
//								if (!in_array($arRecord["STORE_ID"], array_keys($arStoreFormRecords)))
//									$arStoreIDToDelete[$arRecord["ID"]] = $arRecord["STORE_ID"];
//							}
//
//							foreach ($arStoreIDToDelete as $id => $storeId)
//							{
//								CSaleStoreBarcode::Delete($id);
//							}
							/*
							foreach ($arStoreIDToAdd as $addId)
							{
								$arStoreBarcodeFields = array(
									"BASKET_ID"   => $arProduct["BASKET_ID"],
									"BARCODE"     => "",
									"STORE_ID"    => $addId,
									"QUANTITY"    => $arStoreFormRecords[$addId]["QUANTITY"],
									"CREATED_BY"  => (0 < $intUserID ? $intUserID : ""),
									"MODIFIED_BY" => (0 < $intUserID ? $intUserID : "")
								);

								CSaleStoreBarcode::Add($arStoreBarcodeFields);
							}

							foreach ($arStoreSavedRecords as $storeId => $arStoreBarcodeRecord)
							{
								if (!in_array($storeId, $arStoreIDToAdd) && !in_array($storeId, $arStoreIDToDelete))
								{
									if ($arStoreBarcodeRecord["QUANTITY"] != $arStoreFormRecords[$arStoreBarcodeRecord["STORE_ID"]]["QUANTITY"])
									{
										CSaleStoreBarcode::Update(
											$arStoreBarcodeRecord["ID"],
											array(
												"QUANTITY" => $arStoreFormRecords[$arStoreBarcodeRecord["STORE_ID"]]["QUANTITY"],
												"MODIFIED_BY" => (0 < $intUserID ? $intUserID : "")
											)
										);
									}
								}
							}
							*/
							$arProduct["HAS_SAVED_QUANTITY"] = "Y";
						}
						else //BARCODE_MULTI = Y
						{
							$arStoreFormRecords = array();
							foreach ($arProduct["STORES"] as $index => $arStore)
							{
								$arStoreFormRecords[$arStore["STORE_ID"]] = $arStore;
							}

							//deleting all previous records
							/*
							$dbStoreBarcode = CSaleStoreBarcode::GetList(
								array(),
								array(
									"BASKET_ID" => $arProduct["BASKET_ID"],
								),
								false,
								false,
								array("ID", "BASKET_ID", "BARCODE", "QUANTITY", "STORE_ID")
							);
							while ($arStoreBarcode = $dbStoreBarcode->GetNext())
							{
								CSaleStoreBarcode::Delete($arStoreBarcode["ID"]);
							}
							*/
							//adding new values
							/*foreach ($arStoreFormRecords as $arStoreFormRecord)
							{
								if (isset($arStoreFormRecord["BARCODE"]) && isset($arStoreFormRecord["BARCODE_FOUND"]))
								{
									foreach ($arStoreFormRecord["BARCODE"] as $barcodeId => $barcodeValue)
									{
										//save only non-empty and valid barcodes
										if (strlen($barcodeValue) > 0 &&  $arStoreFormRecord["BARCODE_FOUND"][$barcodeId] == "Y")
										{
											$arStoreBarcodeFields = array(
												"BASKET_ID"   => $arProduct["BASKET_ID"],
												"BARCODE"     => $barcodeValue,
												"STORE_ID"    => $arStoreFormRecord["STORE_ID"],
												"QUANTITY"    => 1,
												"CREATED_BY"  => (0 < $intUserID ? $intUserID : ""),
												"MODIFIED_BY" => (0 < $intUserID ? $intUserID : "")
											);

											CSaleStoreBarcode::Add($arStoreBarcodeFields);
										}
									}
								}
							}
							*/
							$arProduct["HAS_SAVED_QUANTITY"] = "Y";
						}

						$arStoreBarcodeOrderFormData[$basketId] = $arProduct["STORES"];
					}
				}
			}
			unset($arProduct);
		}


		//newly added products info
		if ($useStores)
		{
			foreach ($_REQUEST["PRODUCT"] as $basketId => $arProduct)
			{
				if (CSaleBasketHelper::isSetParent($arProduct))
					continue;

				if (isset($arProduct["NEW_PRODUCT"]))
					$arStoreBarcodeOrderFormData["new".$basketId] = $arProduct["STORES"];
			}
		}
	}

	if ($errorMessage == '')
	{
		//another order parameters
		$arAdditionalFields = array(
			"USER_DESCRIPTION" => $_POST["USER_DESCRIPTION"],
			"COMMENTS" => $str_COMMENTS,
		);

		if (isset($str_ADDITIONAL_INFO))
		{
			$arAdditionalFields['ADDITIONAL_INFO'] = $str_ADDITIONAL_INFO;
		}

		if (!empty($arOrder))
		{
			$arErrors = array();
			$OrderNewSendEmail = false;

			$arOldOrder = CSaleOrder::GetByID($ID);

			if ($ID <= 0 || $arOldOrder["STATUS_ID"] == $str_STATUS_ID)
				$arAdditionalFields["STATUS_ID"] = $str_STATUS_ID;

			if ($isOrderConverted != 'N')
			{
				$arAdditionalFields = array_merge($arAdditionalFields, array(
					'CANCELED' => (!empty($_POST["CANCELED"]) && trim($_POST["CANCELED"]) == "Y") ? "Y" : "N",
					'REASON_CANCELED' => (array_key_exists('REASON_CANCELED', $_POST) && strval(trim($_POST["REASON_CANCELED"])) != "") ? trim($_POST["REASON_CANCELED"]): null,

					'PAYED' => (!empty($_POST["PAYED"]) && trim($_POST["PAYED"]) == "Y") ? "Y" : "N",

					'PAY_VOUCHER_NUM' => (array_key_exists('PAY_VOUCHER_NUM', $_POST) && strval(trim($_POST["PAY_VOUCHER_NUM"])) != "") ? trim($_POST["PAY_VOUCHER_NUM"]): null,
					'PAY_VOUCHER_DATE' => (array_key_exists('PAY_VOUCHER_DATE', $_POST) && strval(trim($_POST["PAY_VOUCHER_DATE"])) != "") ? trim($_POST["PAY_VOUCHER_DATE"]): null,
					'PAY_FROM_ACCOUNT' => (array_key_exists('PAY_FROM_ACCOUNT', $_POST) && strval(trim($_POST["PAY_FROM_ACCOUNT"])) != "") ? trim($_POST["PAY_FROM_ACCOUNT"]): null,
					'PAY_CURRENT_ACCOUNT' => (array_key_exists('PAY_CURRENT_ACCOUNT', $_POST) && strval(trim($_POST["PAY_CURRENT_ACCOUNT"])) != "") ? trim($_POST["PAY_CURRENT_ACCOUNT"]): null,

					'PAY_FROM_ACCOUNT_BACK' => (!empty($_POST["PAY_FROM_ACCOUNT_BACK"]) && trim($_POST["PAY_FROM_ACCOUNT_BACK"]) == "Y") ? "Y" : "N",
					'SUM_PAID' => (array_key_exists('SUM_PAID', $_POST) && floatval($_POST["SUM_PAID"]) > 0) ? floatval($_POST["SUM_PAID"]): null,

					'ALLOW_DELIVERY' => (!empty($_POST["ALLOW_DELIVERY"]) && trim($_POST["ALLOW_DELIVERY"]) == "Y") ? "Y" : "N",
					'DELIVERY_DOC_NUM' => (array_key_exists('DELIVERY_DOC_NUM', $_POST) && strval(trim($_POST["DELIVERY_DOC_NUM"])) != "") ? trim($_POST["DELIVERY_DOC_NUM"]): null,
					'DELIVERY_DOC_DATE' => (array_key_exists('DELIVERY_DOC_DATE', $_POST) && strval(trim($_POST["DELIVERY_DOC_DATE"])) != "") ? trim($_POST["DELIVERY_DOC_DATE"]): null,

					'MARKED' => (!empty($_POST["MARKED"]) && trim($_POST["MARKED"]) == "Y") ? "Y" : "N",
					'REASON_MARKED' => (array_key_exists('REASON_MARKED', $_POST) && strval(trim($_POST["REASON_MARKED"])) != "") ? trim($_POST["REASON_MARKED"]): null,

					'DEDUCTED' => (!empty($_POST["DEDUCTED"]) && trim($_POST["DEDUCTED"]) == "Y") ? "Y" : "N",
					'REASON_UNDO_DEDUCTED' => (array_key_exists('REASON_UNDO_DEDUCTED', $_POST) && strval(trim($_POST["REASON_UNDO_DEDUCTED"])) != "") ? trim($_POST["REASON_UNDO_DEDUCTED"]): null,

					'RESERVED' => (!empty($_POST["RESERVED"]) && trim($_POST["RESERVED"]) == "Y") ? "Y" : "N",
				));
			}



			$bSaveBarcodes = ($hasSavedBarcodes || $DEDUCTED == "Y") ? true : false;

			$tmpID = CSaleOrder::DoSaveOrder($arOrder, $arAdditionalFields, $ID, $arErrors, $arCoupon, $arStoreBarcodeOrderFormData, $bSaveBarcodes);

			//delete from basket
			if ($tmpID > 0)
			{
				foreach($_POST["PRODUCT"] as $key => $val)
				{
					if (!isset($val["BASKET_ID"]) && intval($val["BASKET_ID"]) <= 0)
					{
						$dbBasket = CSaleBasket::GetList(
							array(),
							array(
								"ORDER_ID" => "NULL",
								"PRODUCT_ID" => $val["PRODUCT_ID"],
								"USER_ID" => $str_USER_ID,
								"LID" => $LID
							),
							false,
							false,
							array("ID", "TYPE", "SET_PARENT_ID")
						);
						$arBasket = $dbBasket->Fetch();
						if (!empty($arBasket) && !CSaleBasketHelper::isSetItem($arBasket))
							CSaleBasket::Delete($arBasket["ID"]);
					}
				}
			}

			if ($ID <= 0)
				$OrderNewSendEmail = true;
			else
			{
				if ($arOldOrder["STATUS_ID"] != $str_STATUS_ID)
					CSaleOrder::StatusOrder($ID, $str_STATUS_ID);
			}

			$ID = $tmpID;

			if ($ID > 0)
			{
				$arOrder2Update = array();

				if (empty($arErrors))
				{
					$CANCELED = trim($_POST["CANCELED"]);
					$REASON_CANCELED = trim($_POST["REASON_CANCELED"]);
					if ($CANCELED != "Y")
						$CANCELED = "N";

					if ($arOldOrder["CANCELED"] != $CANCELED)
					{
						$bUserCanCancelOrder = CSaleOrder::CanUserCancelOrder($ID, $arUserGroups, $intUserID);

						$errorMessageTmp = "";

						if (!$bUserCanCancelOrder)
						{
							$errorMessageTmp .= GetMessage("SOD_NO_PERMS2CANCEL").". ";
						}
						else
						{
							if (!CSaleOrder::CancelOrder($ID, $CANCELED, $REASON_CANCELED))
							{
								if ($ex = $APPLICATION->GetException())
								{
									if ($ex->GetID() != "ALREADY_FLAG")
										$errorMessageTmp .= $ex->GetString();
								}
								else
									$errorMessageTmp .= GetMessage("ERROR_CANCEL_ORDER").". ";
							}
						}

						if ($errorMessageTmp != "")
							$arErrors[] = $errorMessageTmp;
					}
					else
					{
						if($arOldOrder["REASON_CANCELED"] != $REASON_CANCELED)
							$arOrder2Update["REASON_CANCELED"] = $REASON_CANCELED;
					}
				}


			}
			if ($ID > 0 AND empty($arErrors))
			{
				//profile saving
				$str_USER_ID = intval($str_USER_ID);

				if (isset($userProfileID))
				{
					CSaleOrderUserProps::DoSaveUserProfile($str_USER_ID, $userProfileID, $profileName, $str_PERSON_TYPE_ID, $arOrderPropsValues, $arErrors);
				}
				unset($user_profile);

				//send new order mail
				if ($OrderNewSendEmail)
				{
					$strOrderList = "";
					foreach ($arOrder["BASKET_ITEMS"] as $val)
					{
						if (CSaleBasketHelper::isSetItem($val))
							continue;

						$measure = (isset($val["MEASURE_TEXT"])) ? $val["MEASURE_TEXT"] : GetMessage("SOA_SHT");
						$strOrderList .= $val["NAME"]." - ".$val["QUANTITY"]." ".$measure." x ".SaleFormatCurrency($val["PRICE"], $BASE_LANG_CURRENCY);
						$strOrderList .= "</br>";
					}

					$arOrderNew = CSaleOrder::GetByID($ID);

					//send mail
					$arFields = array(
						"ORDER_ID" => $arOrderNew["ACCOUNT_NUMBER"],
						"ORDER_DATE" => Date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT", $LID))),
						"ORDER_USER" => $arUserEmail["PAYER_NAME"],
						"PRICE" => SaleFormatCurrency($arOrder["PRICE"], $BASE_LANG_CURRENCY),
						"BCC" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
						"EMAIL" => $arUserEmail["USER_EMAIL"],
						"ORDER_LIST" => $strOrderList,
						"SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
						"DELIVERY_PRICE" => $arOrder["DELIVERY_PRICE"],
					);

					$eventName = "SALE_NEW_ORDER";

					$bSend = true;
					foreach(GetModuleEvents("sale", "OnOrderNewSendEmail", true) as $arEvent)
						if (ExecuteModuleEventEx($arEvent, array($ID, &$eventName, &$arFields))===false)
							$bSend = false;

					if($bSend)
					{
						$event = new CEvent;
						$event->Send($eventName, $LID, $arFields, "N");
					}

					CSaleMobileOrderPush::send("ORDER_CREATED", array("ORDER" => $arOrderNew));
				}
			}
			else
			{
				foreach($arErrors as $val)
				{
					if (is_array($val))
						$errorMessage .= $val["TEXT"]."<br>";
					else
						$errorMessage .= $val;
				}
			}
		}
		elseif (!empty($arErrors))
		{
			foreach($arErrors as $val)
			{
				if (is_array($val))
					$errorMessage .= $val["TEXT"]."<br>";
				else
					$errorMessage .= $val;
			}
		}
		else
		{
			$errorMessage .= GetMessage("SOE_SAVE_ERROR")."<br>";
		}
	}//end if save

	unset($location, $BTN_SAVE_BUYER, $buyertypechange, $userId, $user_id);

	if ('' == $errorMessage AND $ID > 0)
	{
		if ($crmMode)
			CRMModeOutput($ID);
		DiscountCouponsManager::clear(true);

		if (isset($save) AND $save <> '')
		{
			CSaleOrder::UnLock($ID);
			LocalRedirect("/bitrix/admin/sale_order.php?lang=".LANGUAGE_ID."&LID=".urlencode($LID).GetFilterParams("filter_", false));
		}

		if (isset($apply) AND $apply <> '')
			LocalRedirect("/bitrix/admin/sale_order_new.php?lang=".LANGUAGE_ID."&ID=".$ID."&LID=".urlencode($LID).GetFilterParams("filter_", false));
	}
	if ('' != $errorMessage)
		$bVarsFromForm = true;
}

if (!empty($dontsave))
{
	DiscountCouponsManager::clear(true);
	CSaleOrder::UnLock($ID);
	if ($crmMode)
		CRMModeOutput($ID);

	LocalRedirect("/bitrix/admin/sale_order.php?lang=".LANGUAGE_ID."&LID=".CUtil::JSEscape($LID));
}

/*****************************************************************************/
/************** Processing of requests from the proxy ************************/
/*****************************************************************************/

if (
	check_bitrix_sessid()
	&& !empty($_REQUEST['ORDER_AJAX']) && $_REQUEST['ORDER_AJAX'] == 'Y'
)
{
	/*
	* location
	*/
	if (isset($location) && !isset($product) && !isset($locationZip))
	{
		$tmpLocation = "";

		ob_start();

		CSaleLocation::proxySaleAjaxLocationsComponent(
			array(
				"SITE_ID" => $LID,
				"AJAX_CALL" => "Y",
				"COUNTRY_INPUT_NAME" => "ORDER_PROP_".$locid,
				"REGION_INPUT_NAME" => "REGION_ORDER_PROP_".$locid,
				"CITY_INPUT_NAME" => "CITY_ORDER_PROP_".$locid,
				"CITY_OUT_LOCATION" => "Y",
				"ALLOW_EMPTY_CITY" => "Y",
				"LOCATION_VALUE" => $location,
				"COUNTRY" => "",
				"ONCITYCHANGE" => "fChangeLocationCity",
			),
			array(
				"ID" => $location,
				"CODE" => "",
				"JS_CALLBACK" => 'fChangeLocationCity',
				"SHOW_DEFAULT_LOCATIONS" => 'Y',
				"JS_CONTROL_GLOBAL_ID" => 'saleOrderNew',
			),
			'',
			true,
			'location-block-wrapper'.(intval($locid) ? ' prop-'.intval($locid) : '')
		);

		$tmpLocation = ob_get_contents();
		ob_end_clean();

		$arData = array();
		if (intval($locid) > 0)
		{
			$arData["status"] = "ok";
			$arData["prop_id"] = $locid;
			$arData["location"] = $tmpLocation;
		}
		$result = CUtil::PhpToJSObject($arData);

		CRMModeOutput($result);
	}

	/*
	* change buyer type
	*/
	if (isset($buyertypechange))
	{
		if (!isset($ID) OR $ID == "") $ID = "";
		if (!isset($paysystemid) OR $paysystemid == "") $paysystemid = "";

		$arData = array();
		$arData["status"] = "ok";
		$arData["buyertype"] = fGetBuyerType($buyertypechange, $LID, $userId, $ID);
		$arData["buyerdelivery"] = fGetPaySystemsHTML($buyertypechange, $paysystemid);
		$arLocation = fGetLocationID($buyertypechange);

		$arData["location_id"] = $arLocation["LOCATION_ID"];
		$arData["location_zip_id"] = $arLocation["LOCATION_ZIP_ID"];

		$result = CUtil::PhpToJSObject($arData);

		CRMModeOutput($result);
	}

	/*
	* get locationId for delivery data
	*/
	if (isset($persontypeid))
	{
		$persontypeid = intval($persontypeid);

		$arData = array();
		$arLocation = fGetLocationID($persontypeid);

		$arData["location_id"] = $arLocation["LOCATION_ID"];
		$arData["location_zip_id"] = $arLocation["LOCATION_ZIP_ID"];

		$result = CUtil::PhpToJSObject($arData);

		CRMModeOutput($result);
	}

	/*
	* take a list profile and user basket
	*/
	if (isset($userId) && isset($buyerType) && (!isset($profileDefault) || $profileDefault == ""))
	{
		$id = intval($id);
		$userId = intval($userId);
		$oldUserId = 0;
		if (isset($_POST['oldUserId']))
			$oldUserId = (int)$_POST['oldUserId'];
		if ($oldUserId < 0)
			$oldUserId = 0;
		$buyerType = intval($buyerType);
		$LID = trim($LID);
		$currency = trim($currency);

		$couponsMode = ($id > 0 ? DiscountCouponsManager::MODE_ORDER : DiscountCouponsManager::MODE_MANAGER);
		$couponsParams = array(
			'userId' => $userId
		);
		if ($oldUserId != $userId)
			$couponsParams['oldUserId'] = $oldUserId;
		if ($id > 0)
			$couponsParams['orderId'] = $id;
		DiscountCouponsManager::init($couponsMode, $couponsParams, false);
		unset($couponsParams, $couponsMode);

		$arFuserItems = CSaleUser::GetList(array("USER_ID" => $userId));
		$fuserId = $arFuserItems["ID"];
		$arData = array();
		$arErrors = array();

		$arData["status"] = "ok";
		$arData["userProfileSelect"] = fUserProfile($userId, $buyerType);
		$arData["userName"] = fGetUserName($userId);

		$arShoppingCart = CSaleBasket::DoGetUserShoppingCart($LID, $userId, $fuserId, $arErrors, array());
		$arShoppingCart = fDeleteDoubleProduct($arShoppingCart, array(), 'N');
		$arData["userBasket"] = fGetFormatedProduct($userId, $LID, $arShoppingCart, $currency, 'basket');

		$viewedIterator = \Bitrix\Catalog\CatalogViewedProductTable::getList(
			array(
				"filter" => array("FUSER_ID" => $fuserId),
				"select" => array(
					"ID",
					"PRODUCT_ID",
					"LID" => "SITE_ID",
					"NAME" => "ELEMENT.NAME",
					"PREVIEW_PICTURE" => "ELEMENT.PREVIEW_PICTURE",
					"DETAIL_PICTURE" => "ELEMENT.DETAIL_PICTURE",
				),
				"order" => array("DATE_VISIT" => "DESC"),
				"limit" => 10
			)
		);

		$viewed = array();
		while($row = $viewedIterator->fetch())
		{
			$row['MODULE'] = "catalog";
			$viewed[$row['PRODUCT_ID']] = $row;
		}

		if (!empty($viewed))
		{
			$filter = array("ID" => array_keys($viewed));

			$elementIterator = CIBlockElement::GetList(array(), $filter, false, false, array('ID', 'IBLOCK_ID', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'));
			while ($fields = $elementIterator->GetNext())
			{
				$viewed[$fields['ID']]['DETAIL_PAGE_URL'] = $fields['~DETAIL_PAGE_URL'];

				if($viewed[$fields['ID']]['PREVIEW_PICTURE'] > 0)
				{
					$img = CFile::GetFileArray($viewed[$fields['ID']]['PREVIEW_PICTURE']);
					if($img)
						$viewed[$fields['ID']]['PREVIEW_PICTURE'] = $img['SRC'];
					else
						$viewed[$fields['ID']]['PREVIEW_PICTURE'] = false;
				}
				else
				{
					$viewed[$fields['ID']]['PREVIEW_PICTURE'] = false;
				}

				if($viewed[$fields['ID']]['DETAIL_PICTURE'] > 0)
				{
					$img = CFile::GetFileArray($viewed[$fields['ID']]['DETAIL_PICTURE']);

					if($img)
						$viewed[$fields['ID']]['DETAIL_PICTURE'] = $img['SRC'];
					else
						$viewed[$fields['ID']]['DETAIL_PICTURE'] = false;
				}
				else
				{
					$viewed[$fields['ID']]['DETAIL_PICTURE'] = false;
				}
			}

			// Prices
			$priceIterator = CPrice::getList(array(), array("PRODUCT_ID" => $filter['ID']), false, false, array("PRODUCT_ID", "PRICE", "CURRENCY"));
			while($price = $priceIterator->fetch())
			{
				if(!isset($viewed[$price['PRODUCT_ID']]['PRICE']))
				{
					$viewed[$price['PRODUCT_ID']]['PRICE'] = $price['PRICE'];
					$viewed[$price['PRODUCT_ID']]['CURRENCY'] = $price['CURRENCY'];
				}
			}
		}

		$arViewedResult = fDeleteDoubleProduct($viewed, $arFilterRecommended, 'N');
		$arData["viewed"] = fGetFormatedProduct($userId, $LID, $arViewedResult, $currency, 'viewed');


		$result = CUtil::PhpToJSObject($arData);

		CRMModeOutput($result);
	}

	/*
	* profile autocomplete script
	*/
	if (isset($userId) AND isset($buyerType) AND isset($profileDefault))
	{
		$userId = intval($userId);
		$buyerType = intval($buyerType);
		$profileDefault = intval($profileDefault);

		$arPropValuesTmp = array();
		$userProfile = array();
		$userProfile = CSaleOrderUserProps::DoLoadProfiles($userId, $buyerType);
		if ($profileDefault != "" AND $profileDefault != "0")
			$arPropValuesTmp = $userProfile[$profileDefault]["VALUES"];

		$dbVariants = CSaleOrderProps::GetList(
			array("SORT" => "ASC"),
			array(
				"PERSON_TYPE_ID" => $buyerType,
				"USER_PROPS" => "Y",
					"ACTIVE" => "Y",
					"RELATED" => false
			)
		);
		while ($arVariants = $dbVariants->Fetch())
		{
			if (isset($arPropValuesTmp[$arVariants["ID"]]))
				$arPropValues[$arVariants["ID"]] = $arPropValuesTmp[$arVariants["ID"]];
			else
				$arPropValues[$arVariants["ID"]] = $arVariants["DEFAULT_VALUE"];

			if($arVariants["IS_EMAIL"] == "Y" || $arVariants["IS_PAYER"] == "Y")
			{
				if($arPropValues[$arVariants["ID"]] == '' && intval($userId) > 0)
				{
					$rsUser = CUser::GetByID($userId);
					if ($arUser = $rsUser->Fetch())
					{
						if($arVariants["IS_EMAIL"] == "Y")
							$arPropValues[$arVariants["ID"]] = $arUser["EMAIL"];
						else
						{
							if ($arUser["LAST_NAME"] <> '')
								$arPropValues[$arVariants["ID"]] .= $arUser["LAST_NAME"];
							if ($arUser["NAME"] <> '')
								$arPropValues[$arVariants["ID"]] .= " ".$arUser["NAME"];
							if ($arUser["SECOND_NAME"] <> '' AND $arUser["NAME"] <> '')
								$arPropValues[$arVariants["ID"]] .= " ".$arUser["SECOND_NAME"];
						}
					}
				}
			}

		}

		$strPropsList = "";

		foreach ($arPropValues as $key => $val)
		{
			$key = CUtil::JSEscape(htmlspecialcharsback($key));
			$val = CUtil::JSEscape(htmlspecialcharsback($val));
			$strPropsList.=($strPropsList <> ''?', ':'').'"'.$key.'": "'.$val.'"';
		}

		if ($strPropsList <> '')
		{
			?>
			<script type="text/javascript">
				var arProps = {<?=$strPropsList?>},
					key,
					el,
					val,
					i,
					j;

				for (key in arProps)
				{
					el = document.getElementById("ORDER_PROP_" + key);
					val = arProps[key];
					if(el)
					{
						var elType = el.getAttribute('type');
						if (elType == "text" || elType == "textarea" || elType == "select")
						{
							el.value = val;
						}
						else if (elType == "radio")
						{
							elRadio = el.getElementsByTagName("input");
							for (i = 0; i < elRadio.length; i++)
							{
								if (elRadio[i].value == val)
								{
									elRadio[i].checked = true;
								}
								else
								{
									elRadio[i].checked = false;
								}
							}
						}
						else if (elType == "checkbox")
						{
							if (val == 'Y')
								el.checked = true;
							else
								el.checked = false;
						}
						else if (elType == "multyselect")
						{
							if (val.length > 0)
							{
								var arVals = val.split(',');
								for (i = 0; i < el.length; i++)
									{
										el[i].selected = false;
										for (j = 0; j < arVals.length; j++ )
										{
											if (arVals[j].trim() == el[i].value)
												el[i].selected = true;
										}
									}
							}
							else
							{
								el.selectedIndex = -1;
							}
						}
					}
				}

				<?if(CSaleLocation::isLocationProEnabled()):?>
					el = document.querySelector('[name="ORDER_PROP_' + locationID + '"]');
					if(!BX.type.isDomNode(el))
						el = document.querySelector('[name="CITY_ORDER_PROP_' + locationID + '"]');
				<?else:?>
					el = document.getElementById("ORDER_PROP_" + locationID + "CITY_ORDER_PROP_"+locationID);
				<?endif?>

				if(el && arProps[locationID])
				{
					BX.ajax.post('/bitrix/admin/sale_order_new.php', '<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&locid=' + locationID + '&propID=<?=$buyerType?>&LID=<?=CUtil::JSEscape($LID)?>&location=' + arProps[locationID], fLocationResult);
				}
				else
					fRecalProduct('', '', 'N', 'N', null);

			</script>
		<?
		}
		die();
	}

	/*
	* get additional products from the basket
	*/
	if (isset($getmorebasket) && $getmorebasket == "Y")
	{
		$userId = intval($userId);
		$arFuserItems = CSaleUser::GetList(array("USER_ID" => $userId));
		$fuserId = $arFuserItems["ID"];
		$arErrors = array();

		$arOrderProduct = CUtil::JsObjectToPhp($arProduct);
		$arShoppingCart = CSaleBasket::DoGetUserShoppingCart($LID, $userId, $fuserId, $arErrors, array());
		$arShoppingCart = fDeleteDoubleProduct($arShoppingCart, $arOrderProduct, $showAll);

		$result = fGetFormatedProduct($userId, $LID, $arShoppingCart, $CURRENCY, 'basket');

		CRMModeOutput($result);
	}

	/*
	* get user's viewed products
	*/
	if (isset($getmoreviewed) && $getmoreviewed == "Y")
	{
		$userId = intval($userId);
		$arFuserItems = CSaleUser::GetList(array("USER_ID" => $userId));
		$fuserId = $arFuserItems["ID"];
		$arErrors = array();

		$arOrderProduct = CUtil::JsObjectToPhp($arProduct);

		$viewedIterator = \Bitrix\Catalog\CatalogViewedProductTable::getList(
			array(
				"filter" => array("FUSER_ID" => $fuserId),
				"select" => array(
					"ID",
					"PRODUCT_ID",
					"LID" => "SITE_ID",
					"NAME" => "ELEMENT.NAME",
					"PREVIEW_PICTURE" => "ELEMENT.PREVIEW_PICTURE",
					"DETAIL_PICTURE" => "ELEMENT.DETAIL_PICTURE",
				),
				"order" => array("DATE_VISIT" => "DESC"),
				"limit" => 10
			)
		);

		$viewed = array();
		while($row = $viewedIterator->fetch())
		{
			$row['MODULE'] = "catalog";
			$viewed[$row['PRODUCT_ID']] = $row;
		}

		if (!empty($viewed))
		{
			$filter = array("ID" => array_keys($viewed));

			$elementIterator = CIBlockElement::GetList(array(), $filter, false, false, array('ID', 'IBLOCK_ID', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'));
			while ($fields = $elementIterator->GetNext())
			{
				$viewed[$fields['ID']]['DETAIL_PAGE_URL'] = $fields['~DETAIL_PAGE_URL'];

				if($viewed[$fields['ID']]['PREVIEW_PICTURE'] > 0)
				{
					$img = CFile::GetFileArray($viewed[$fields['ID']]['PREVIEW_PICTURE']);
					if($img)
						$viewed[$fields['ID']]['PREVIEW_PICTURE'] = $img['SRC'];
					else
						$viewed[$fields['ID']]['PREVIEW_PICTURE'] = false;
				}
				else
				{
					$viewed[$fields['ID']]['PREVIEW_PICTURE'] = false;
				}

				if($viewed[$fields['ID']]['DETAIL_PICTURE'] > 0)
				{
					$img = CFile::GetFileArray($viewed[$fields['ID']]['DETAIL_PICTURE']);

					if($img)
						$viewed[$fields['ID']]['DETAIL_PICTURE'] = $img['SRC'];
					else
						$viewed[$fields['ID']]['DETAIL_PICTURE'] = false;
				}
				else
				{
					$viewed[$fields['ID']]['DETAIL_PICTURE'] = false;
				}
			}


			// Prices
			$priceIterator = CPrice::getList(array(), array("PRODUCT_ID" => $filter['ID']), false, false, array("PRODUCT_ID", "PRICE", "CURRENCY"));
			while($price = $priceIterator->fetch())
			{
				if(!isset($viewed[$price['PRODUCT_ID']]['PRICE']))
				{
					$viewed[$price['PRODUCT_ID']]['PRICE'] = $price['PRICE'];
					$viewed[$price['PRODUCT_ID']]['CURRENCY'] = $price['CURRENCY'];
				}
			}
		}
		$arViewedCart = fDeleteDoubleProduct($viewed, $arOrderProduct, $showAll);

		$result = fGetFormatedProduct($userId, $LID, $arViewedCart, $CURRENCY, 'viewed');

		CRMModeOutput($result);
	}

	/*
	* recalculate order
	*/
	if (isset($product) && isset($user_id))
	{
		$result = "";
		$LID = (string)$LID;
		$id = intval($id);
		$user_id = intval($user_id);
		$paySystemId = intval($paySystemId);
		$buyerTypeId = intval($buyerTypeId);
		$location = intval($location);
		$locationID = intval($locationID);
		$locationZip = intval($locationZip);
		$locationZipID = intval($locationZipID);
		$WEIGHT_UNIT = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_unit', "", $LID));
		$WEIGHT_KOEF = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_koef', 1, $LID));
		$arDelivery = array();
		$recomMore = ($recomMore == "Y") ? "Y" : "N";
		$recalcOrder = ($recalcOrder == "Y") ? "Y" : "N";
		$cartFix = ('Y' == $cartFix ? 'Y' : 'N');
		$currency = CSaleLang::GetLangCurrency($LID);

		$couponManagerMode = ($id > 0 ? DiscountCouponsManager::MODE_ORDER : DiscountCouponsManager::MODE_MANAGER);
		$couponManagerParams = array(
			'userId' => $user_id
		);
		if ($id > 0)
			$couponManagerParams['orderId'] = $id;
		DiscountCouponsManager::init($couponManagerMode, $couponManagerParams, false);
		unset($couponManagerParams, $couponManagerMode);
		$newCoupons = array();
		if (!empty($_REQUEST['coupon']))
			$newCoupons = fGetCoupon($_REQUEST['coupon']);
		if (!empty($newCoupons))
		{
			foreach ($newCoupons as &$oneCoupon)
			{
				$resultCoupon = DiscountCouponsManager::add($oneCoupon);
			}
			unset($resultCoupon, $oneCoupon);
		}
		unset($newCoupons);
		if (!empty($_REQUEST['deleteCoupon']))
		{
			$resultCoupon = DiscountCouponsManager::delete($_REQUEST['deleteCoupon']);
			unset($resultCoupon);
		}
		$arOrderProduct = CUtil::JsObjectToPhp($product);

		$arOrderOptions = array(
			'CART_FIX' => $cartFix
		);

		$arOrderProductPrice = fGetUserShoppingCart($arOrderProduct, $LID, $recalcOrder);

		foreach ($arOrderProductPrice as &$arItem) // tmp hack not to update basket quantity data from catalog
		{
			$arItem["ID_TMP"] = $arItem["ID"];
			unset($arItem["ID"]);
		}
		unset($arItem);

		$tmpOrderId = ($id == 0) ? 0 : $id;

		if ('Y' == $arOrderOptions['CART_FIX'])
		{
			$arShoppingCart = $arOrderProductPrice;
		}
		else
		{
			$arShoppingCart = CSaleBasket::DoGetUserShoppingCart($LID, $user_id, $arOrderProductPrice, $arErrors, array(), $tmpOrderId);
		}

		$arOrderPropsValues = array();
		if ($locationID != "" AND $location != "")
			$arOrderPropsValues[$locationID] = $location;
		if ($locationZipID != "" AND $locationZip != "")
			$arOrderPropsValues[$locationZipID] = $locationZip;

		// enable/disable town for location
		$dbProperties = CSaleOrderProps::GetList(
			array("SORT" => "ASC"),
			array("ID" => $locationID, "ACTIVE" => "Y", ">INPUT_FIELD_LOCATION" => 0, "RELATED" => false),
			false,
			false,
			array("INPUT_FIELD_LOCATION")
		);
		if ($arProperties = $dbProperties->Fetch())
			$bDeleteFieldLocationID = $arProperties["INPUT_FIELD_LOCATION"];

		if(CSaleLocation::isLocationProEnabled())
		{
			$bDeleteFieldLocation = 'Y';// CSaleLocation::checkLocationIsAboveCity($location) ? 'Y' : 'N';
		}
		else
		{
			$rsLocationsList = CSaleLocation::GetList(
				array(),
				array("ID" => $location),
				false,
				false,
				array("ID", "CITY_ID")
			);
			$arCity = $rsLocationsList->GetNext();
			if (intval($arCity["CITY_ID"]) <= 0)
				$bDeleteFieldLocation = "Y";
			else
				$bDeleteFieldLocation = "N";
		}

		$arOrder = CSaleOrder::DoCalculateOrder(
			$LID,
			$user_id,
			$arShoppingCart,
			$buyerTypeId,
			$arOrderPropsValues,
			$deliveryId,
			$paySystemId,
			$arOrderOptions,
			$arErrors,
			$arWarnings
		);

		$orderDiscount = 0;
		$arData = array();
		$arFilterRecommended = array();
		$priceBaseTotal = 0;

		if (!empty($arOrder["BASKET_ITEMS"]))
		{
			$arCatalogProduct = array();
			foreach ($arOrder["BASKET_ITEMS"] as $val)
			{
				if (!CSaleBasketHelper::isSetItem($val))
				{
					$priceDiscountPercent = 0;
					$priceBase = $val["PRICE"] + $val["DISCOUNT_PRICE"];
					$priceDiscountPercent = roundEx(($val["DISCOUNT_PRICE"] * 100) / $priceBase, SALE_VALUE_PRECISION);

					$arData[$val["TABLE_ROW_ID"]]["PRICE_BASE"] = CCurrencyLang::CurrencyFormat($priceBase, $val["CURRENCY"], false);
					$arData[$val["TABLE_ROW_ID"]]["DISCOUNT_REPCENT"] = $priceDiscountPercent;
					$arData[$val["TABLE_ROW_ID"]]["DISCOUNT_PRICE"] = $val["DISCOUNT_PRICE"];
					$arData[$val["TABLE_ROW_ID"]]["PRICE"] = $val["PRICE"];
					$arData[$val["TABLE_ROW_ID"]]["PRICE_DISPLAY"] = CCurrencyLang::CurrencyFormat($val["PRICE"], $val["CURRENCY"], false);
					$arData[$val["TABLE_ROW_ID"]]["QUANTITY"] = $val["QUANTITY"];

					if (isset($val["QUANTITY_DEFAULT"]) && $val["QUANTITY_DEFAULT"] > 0 && $val["QUANTITY_DEFAULT"] != $val["QUANTITY"])
						$arData[$val["TABLE_ROW_ID"]]["WARNING_BALANCE"] = "Y";

					$arData[$val["TABLE_ROW_ID"]]["DISCOUNT_PRICE_DISPLAY"] = CCurrencyLang::CurrencyFormat($val["DISCOUNT_PRICE"], $val["CURRENCY"], false);
					$arData[$val["TABLE_ROW_ID"]]["SUMMA_DISPLAY"] = CCurrencyLang::CurrencyFormat(($val["PRICE"] * $val["QUANTITY"]), $val["CURRENCY"], false);
					$arData[$val["TABLE_ROW_ID"]]["CURRENCY"] = $val["CURRENCY"];
					$arData[$val["TABLE_ROW_ID"]]["NOTES"] = $val["NOTES"];

					$arData[$val["TABLE_ROW_ID"]]["BALANCE"] = 0;
					if ($val["MODULE"] == "catalog")
					{
						$arCatalogProduct[$val["PRODUCT_ID"]] = $val["TABLE_ROW_ID"];
					}
					$orderDiscount += $val["DISCOUNT_PRICE"] * $val["QUANTITY"];
					$arFilterRecommended[] = $val["PRODUCT_ID"];

					$priceBaseTotal += ($priceBase * $val["QUANTITY"]);
				}
			}


			if (!empty($arCatalogProduct) && $bUseCatalog)
			{
				$rsItems = CCatalogProduct::GetList(
					array(),
					array('ID' => array_keys($arCatalogProduct)),
					false,
					false,
					array('ID', 'QUANTITY')
				);
				while ($arItem = $rsItems->Fetch())
				{
					$strKey = $arCatalogProduct[$arItem['ID']];
					$arData[$strKey]["BALANCE"] = floatval($arItem['QUANTITY']);
				}
			}
		}
		$arData[0]["ORDER_ERROR"] = "N";

		//change delivery price
		$deliveryChangePrice = false;
		if ($delpricechange == "Y")
		{
			$arOrder["PRICE"] = ($arOrder["PRICE"] - $arOrder["DELIVERY_PRICE"]) + $deliveryPrice;
			$arOrder["DELIVERY_PRICE"] = $deliveryPrice;
			$arOrder["PRICE_DELIVERY"] = $deliveryPrice;
			$deliveryChangePrice = true;
			$arDelivery["DELIVERY_DEFAULT_PRICE"] = $deliveryPrice;
			$arDelivery["DELIVERY_DEFAULT"] = "";
			$arDelivery["DELIVERY_DEFAULT_ERR"] = "";
			$arDelivery["DELIVERY_DEFAULT_DESCRIPTION"] = "";
			$arData[0]["DELIVERY"] = "";
		}
		else
		{
			$arDelivery = fGetDeliverySystemsHTML($location, $locationZip, $arOrder["ORDER_WEIGHT"], $arOrder["ORDER_PRICE"], $currency, $LID, $deliveryId, $arShoppingCart);
		}

		$arData[0]["ORDER_ID"] = $id;
		$arData[0]["DELIVERY"] = $arDelivery["DELIVERY"];

		if (isset($arOrder["PRICE_DELIVERY"]) && floatval($arOrder["PRICE_DELIVERY"]) >= 0 && floatval($arOrder["PRICE_DELIVERY"])."!" == $arOrder["PRICE_DELIVERY"]."!") //if number
		{
			$arData[0]["DELIVERY_PRICE"] = $arOrder["PRICE_DELIVERY"];
			$arData[0]["DELIVERY_PRICE_FORMAT"] = SaleFormatCurrency($arOrder["PRICE_DELIVERY"], $currency);
		}
		else
		{
			if ($arDelivery["CURRENCY"] != $currency)
				$arDelivery["DELIVERY_DEFAULT_PRICE"] = roundEx(CCurrencyRates::ConvertCurrency($arDelivery["DELIVERY_DEFAULT_PRICE"], $arDelivery["CURRENCY"], $currency), SALE_VALUE_PRECISION);

			$arDelivery["DELIVERY_DEFAULT_PRICE"] = floatval($arDelivery["DELIVERY_DEFAULT_PRICE"]);
			$arData[0]["DELIVERY_PRICE"] = $arDelivery["DELIVERY_DEFAULT_PRICE"];
			$arData[0]["DELIVERY_PRICE_FORMAT"] = SaleFormatCurrency($arDelivery["DELIVERY_DEFAULT_PRICE"], $currency);
		}
		$arData[0]["DELIVERY_DEFAULT"] = $arDelivery["DELIVERY_DEFAULT"];

		if (isset($arOrder["PRICE_DELIVERY_DIFF"]))
			$arData[0]["PRICE_DELIVERY_DIFF"] = SaleFormatCurrency(roundEx($arOrder["PRICE_DELIVERY_DIFF"], SALE_VALUE_PRECISION), $currency);

		if ($arDelivery["DELIVERY_DEFAULT_ERR"] <> '')
		{
			$arData[0]["DELIVERY_DESCRIPTION"] = $arDelivery["DELIVERY_DEFAULT_ERR"];
			$arData[0]["ORDER_ERROR"] = "Y";
		}
		else
			$arData[0]["DELIVERY_DESCRIPTION"] = $arDelivery["DELIVERY_DEFAULT_DESCRIPTION"];

		// payment system price
		if (isset($arOrder["PAY_SYSTEM_PRICE"]) && floatval($arOrder["PAY_SYSTEM_PRICE"]) > 0)
			$arData[0]["PAY_SYSTEM_PRICE"] = SaleFormatCurrency(roundEx($arOrder["PAY_SYSTEM_PRICE"], SALE_VALUE_PRECISION), $currency);

		// full price
		if (!isset($arOrder["ORDER_PRICE"]) OR $arOrder["ORDER_PRICE"] == "" )
			$arOrder["ORDER_PRICE"] = 0;
		if (!isset($arOrder["PRICE"]) OR $arOrder["PRICE"] == "")
			$arOrder["PRICE"] = 0;
		if (!isset($arOrder["DISCOUNT_VALUE"]) OR $arOrder["DISCOUNT_VALUE"] == "")
			$arOrder["DISCOUNT_VALUE"] = 0;

		$arCurFormat = CCurrencyLang::GetCurrencyFormat($currency);
		$arData[0]["CURRENCY_FORMAT"] = trim(str_replace("#", '', $arCurFormat["FORMAT_STRING"]));
		$arData[0]["PRICE_TOTAL"] = SaleFormatCurrency($priceBaseTotal, $currency);
		$arData[0]["PRICE_WITH_DISCOUNT_FORMAT"] = SaleFormatCurrency($arOrder["ORDER_PRICE"], $currency);
		$arData[0]["PRICE_WITH_DISCOUNT"] = roundEx($arOrder["ORDER_PRICE"]);
		$arData[0]["PRICE_TAX"] = SaleFormatCurrency(floatval($arOrder["TAX_VALUE"]), $currency);
		$arData[0]["PRICE_WEIGHT_FORMAT"] = roundEx(floatval($arOrder["ORDER_WEIGHT"]/$WEIGHT_KOEF), SALE_WEIGHT_PRECISION)." ".$WEIGHT_UNIT;
		$arData[0]["PRICE_WEIGHT"] = roundEx(floatval($arOrder["ORDER_WEIGHT"]/$WEIGHT_KOEF), SALE_WEIGHT_PRECISION);
		$arData[0]["PRICE_TO_PAY"] = SaleFormatCurrency($arOrder["PRICE"], $currency);
		$arData[0]["PRICE_TO_PAY_DEFAULT"] = floatval($arOrder["PRICE"]);
		$arData[0]["PAY_ACCOUNT"] = $tmpPay["PAY_MESSAGE"];
		$arData[0]["PAY_ACCOUNT_CAN_BUY"] = $tmpPay["PAY_BUDGET"];
		$arData[0]["PAY_ACCOUNT_DEFAULT"] = floatval($tmpPay["CURRENT_BUDGET"]);
		$arData[0]["DISCOUNT_VALUE"] = $arOrder["DISCOUNT_VALUE"];
		$arData[0]["DISCOUNT_VALUE_FORMATED"] = SaleFormatCurrency($arOrder["DISCOUNT_VALUE"], $currency);
		$arData[0]["DISCOUNT_PRODUCT_VALUE"] = $orderDiscount;
		$arData[0]["LOCATION_TOWN_ID"] = intval($bDeleteFieldLocationID);
		$arData[0]["LOCATION_TOWN_ENABLE"] = $bDeleteFieldLocation;
		$tmpPay = fGetPayFromAccount($user_id, $currency);

		// recommended products
		$recommendedProduct = "";
		$arProductIdInBasket = array();
		$arData[0]["RECOMMENDET_CALC"] = "N";
		if ($recommendet == "Y")
		{
			$arRecommended = CSaleProduct::GetRecommendetProduct($user_id, $LID, $arFilterRecommended);
			$arRecommendedProduct = fDeleteDoubleProduct($arRecommended, $arFilterRecommended, $recomMore);

			$recommendedProduct = fGetFormatedProduct($user_id, $LID, $arRecommendedProduct, $currency, 'recom');
			$arData[0]["RECOMMENDET_CALC"] = "Y";
		}
		$arData[0]["RECOMMENDET_PRODUCT"] = $recommendedProduct;

		// coupons
		$arData[0]['COUPON_LIST'] = array();
		$couponsList = DiscountCouponsManager::get(true, array(), true, true);
		if (!empty($couponsList))
		{
			foreach ($couponsList as &$oneCoupon)
			{
				if ($oneCoupon['STATUS'] == DiscountCouponsManager::STATUS_NOT_FOUND || $oneCoupon['STATUS'] == DiscountCouponsManager::STATUS_FREEZE)
					$oneCoupon['JS_STATUS'] = 'BAD';
				elseif ($oneCoupon['STATUS'] == DiscountCouponsManager::STATUS_NOT_APPLYED || $oneCoupon['STATUS'] == DiscountCouponsManager::STATUS_ENTERED)
					$oneCoupon['JS_STATUS'] = 'ENTERED';
				else
					$oneCoupon['JS_STATUS'] = 'APPLYED';
				$oneCoupon['JS_CHECK_CODE'] = '';
				if (isset($oneCoupon['CHECK_CODE_TEXT']))
				{
					$oneCoupon['JS_CHECK_CODE'] = (
						is_array($oneCoupon['CHECK_CODE_TEXT'])
						? implode('<br>', $oneCoupon['CHECK_CODE_TEXT'])
						: $oneCoupon['CHECK_CODE_TEXT']
					);
				}
			}
			unset($oneCoupon);
			$arData[0]['COUPON_LIST'] = array_values($couponsList);
		}
		unset($couponsList);

		$result = CUtil::PhpToJSObject($arData);

		CRMModeOutput($result);
	}

	/*
	* check if barcode is valid (exists on the given store)
	*/
	if (isset($checkBarcode))
	{
		$arBarCodeParams = array(
			"basketItemId" => isset($_POST["basketItemId"]) ? intval($_POST["basketItemId"]) : "",
			"barcode" => isset($_POST["barcode"]) ? $_POST["barcode"] : "",
			"storeId" => isset($_POST["storeId"]) ? intval($_POST["storeId"]) : "",
			"productId" => isset($_POST["productId"]) ? intval($_POST["productId"]) : "",
			"productProvider" => isset($_POST["productProvider"]) ? $_POST["productProvider"] : "",
			"moduleName" => isset($_POST["moduleName"]) ? $_POST["moduleName"] : "",
			"barcodeMulti" => isset($_POST["barcodeMulti"]) && $_POST["barcodeMulti"] == "Y" ? "Y" : "N",
			"orderId" => $ID
		);

		$checkResult = CSaleOrderHelper::isBarCodeValid($arBarCodeParams);

		$arRes = array(
			"status" => $checkResult ? "ok" : "error"
		);
		$result = CUtil::PhpToJSObject($arRes);
		CRMModeOutput($result);
	}

	/*
	* get appropriate order properties if delivery system or payment system have been changed
	*/
	if (isset($get_props))
	{
		$arRes = array();
		$arFilter = array();
		$html = "";
		$ID = (isset($_POST["id"])) ? intval($_POST["id"]) : "";
		$userId = (isset($_POST["userId"])) ? intval($_POST["userId"]) : "";

		if (isset($_POST["delivery_id"]) && $_POST["delivery_id"] <> '')
			$arFilter["RELATED"]["DELIVERY_ID"] = $_POST["delivery_id"];

		if (isset($_POST["paysystem_id"]) && intval($_POST["paysystem_id"]) > 0)
			$arFilter["RELATED"]["PAYSYSTEM_ID"] = intval($_POST["paysystem_id"]);

		if (isset($arFilter["RELATED"]))
		{
			$dbRelatedProps = CSaleOrderProps::GetList(array(), $arFilter, false, false, array("*"));
			while ($arRelatedProps = $dbRelatedProps->GetNext())
			{
				$arRes[] = $arRelatedProps;
			}

			$html = getOrderPropertiesHTML($arRes, array(), $LID, $userId, $ID);
		}

		CRMModeOutput($html);
	}

	/*
	* save product table columns info and get appropriate product data to perform ajax table update
	*/
	if (isset($change_columns))
	{
		$arData = array();
		$arNamedColumns = array();
		$arProps = array();
		$ID = intval($ID);
		$arIDs = array();
		$arSku2Parent = array();

		$arColumns = array_filter(explode(",", $cols));
		$arIblockElementId = array_filter(explode(",", $ids));
		$arElementId = array();

		// make sure that columns data is correct - contains all required fields
		if (!array_key_exists("COLUMN_NAME", $arColumns))
			$arColumns[] = "COLUMN_NAME";
		if (!array_key_exists("COLUMN_QUANTITY", $arColumns))
			$arColumns[] = "COLUMN_QUANTITY";
		if (!array_key_exists("COLUMN_PRICE", $arColumns))
			$arColumns[] = "COLUMN_PRICE";
		if (!array_key_exists("COLUMN_SUM", $arColumns))
			$arColumns[] = "COLUMN_SUM";

		$res = CUserOptions::SetOption("order_basket_table", "table_columns", array("columns" => implode(",", $arColumns)));

		if ($bUseIblock)
		{
			$arCustomSelectFields = array();
			foreach ($arColumns as $id => $column)
			{
				if (mb_substr($column, 0, 9) == "PROPERTY_")
				{
					$arCustomSelectFields[] = $column;

					$dbres = CIBlockProperty::GetList(array(), array("CODE" => mb_substr($column, 9)));
					if ($arPropData = $dbres->GetNext())
					{
						$arProps[$column] = $arPropData;
						$arNamedColumns[$column] = $arPropData["NAME"];
					}
				}
				else
					$arNamedColumns[$column] = GetMessage("NEW_".$column);
			}

			// get data for new fields
			if (count($arIblockElementId) > 0 && $bUseCatalog)
			{
				foreach ($arIblockElementId as $iblockElementId)
				{
					$arElementId[] = $iblockElementId;

					$arParent = CCatalogSku::GetProductInfo($iblockElementId);
					if ($arParent)
					{
						$arElementId[] = $arParent["ID"];
						$arSku2Parent[$iblockElementId] = $arParent["ID"];
					}
				}

				// getting iblock props values accoring to columns' set

				$arItemKeys = array();
				$arSelect = array_merge(array("ID"), $arCustomSelectFields);
				$arProductData = getProductProps($arElementId, $arSelect);

				foreach ($arProductData as $arItem)
				{
					if (!in_array($arItem["ID"], $arSku2Parent)) // leave only original elements, without parents
						$arData[$arItem["ID"]] = $arItem;
				}

				foreach ($arData as $elemId => &$arItem)
				{
					if (array_key_exists($elemId, $arSku2Parent)) // if sku element doesn't have value of some property - we'll show parent element value instead
					{
						foreach ($arCustomSelectFields as $field)
						{
							$fieldVal = $field."_VALUE";
							$parentId = $arSku2Parent[$elemId];

							if ((!isset($arItem[$fieldVal]) || (isset($arItem[$fieldVal]) && $arItem[$fieldVal] == ''))
								&& (isset($arProductData[$parentId][$fieldVal]) && !empty($arProductData[$parentId][$fieldVal]))) // can be array or string
							{
								$arItem[$fieldVal] = $arProductData[$parentId][$fieldVal];
							}
						}
					}
				}
				unset($arItem);

				foreach ($arData as $elemId => &$arItem)
				{
					foreach ($arItem as $key => $value)
					{
						if ((mb_strpos($key, "PROPERTY_", 0) === 0) && (mb_strrpos($key, "_VALUE") == mb_strlen($key) - 6))
						{
							$code = str_replace(array("_VALUE"), "", $key);
							$propData = $arProps[$code];
							$arItem[$key] = getIblockPropInfo($value, $propData, array("WIDTH" => 90, "HEIGHT" => 90), $ID);
						}
					}
				}
			}
		}

		$result = CUtil::PhpToJSObject(array("status" => ($res) ? "Y" : "N", "data" => $arData, "columns" => $arNamedColumns, "columnsString" => array_keys($arNamedColumns)));

		CRMModeOutput($result);
	}

	/*
	* get product data to add product to the basket
	*/
	if (isset($get_product_params))
	{
		$productId = intval($productId);

		if (!isset($quantity)) // default value for quantity to be ordered
			$quantity = 1;

		$arParams = getProductDataToFillBasket($productId, $quantity, $userId, $LID, $userColumns);

		$result = CUtil::PhpToJSObject(array("params" => $arParams, "type" => $type));

		CRMModeOutput($result);
	}

}//end ORDER_AJAX=Y

/*****************************************************************************/
/**************************** FORM ORDER *************************************/
/*****************************************************************************/

//date order
$str_DATE_UPDATE = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", $lang)));
$str_DATE_INSERT = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", $lang)));

if (isset($ID) && $ID > 0)
{
	if (!$arOrderOldTmp)
		LocalRedirect("sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));

	$LID = $str_LID;
	$str_DELIVERY_ID = (isset($_POST["DELIVERY_ID"]) && $_POST["DELIVERY_ID"] <> '') ? $_POST["DELIVERY_ID"] : $str_DELIVERY_ID;
	$str_PAY_SYSTEM_ID = (isset($_POST["PAY_SYSTEM_ID"]) && intval($_POST["PAY_SYSTEM_ID"]) > 0) ? intval($_POST["PAY_SYSTEM_ID"]) : $str_PAY_SYSTEM_ID;
}

if (!isset($str_TAX_VALUE) OR $str_TAX_VALUE == "")
	$str_TAX_VALUE = 0;

if (intval($str_PERSON_TYPE_ID) <= 0)
{
	$str_PERSON_TYPE_ID = 0;
	$arFilter = array();
	$arFilter["ACTIVE"] = "Y";
	if($LID <> '')
		$arFilter["LID"] = $LID;
	$typeListCount = (int)CSalePersonType::GetList(array(), $arFilter, array());
	if ($typeListCount > 0)
	{
		$dbPersonType = CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"), $arFilter, false, false, array('ID', 'NAME', 'SORT'));
		if ($arPersonType = $dbPersonType->Fetch())
			$str_PERSON_TYPE_ID = $arPersonType["ID"];
	}
}

$arFuserItems = CSaleUser::GetList(array("USER_ID" => intval($str_USER_ID)));
$FUSER_ID = $arFuserItems["ID"];

/*
 * form select site
 */
if ((!isset($LID) OR $LID == "") AND (defined('BX_PUBLIC_MODE') AND BX_PUBLIC_MODE == 1))
{
	$arSitesShop = array();
	$arSitesTmp = array();
	$rsSites = CSite::GetList("id", "asc", array("ACTIVE" => "Y"));
	while ($arSite = $rsSites->GetNext())
	{
		$site = COption::GetOptionString("sale", "SHOP_SITE_".$arSite["ID"], "");
		if ($arSite["ID"] == $site)
		{
			$arSitesShop[] = array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);
		}
		$arSitesTmp[] = array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);
	}

	$rsCount = count($arSitesShop);
	if ($rsCount <= 0)
	{
		$arSitesShop = $arSitesTmp;
		$rsCount = count($arSitesShop);
	}

	if ($rsCount === 1)
	{
		$LID = $arSitesShop[0]["ID"];
	}
	elseif ($rsCount > 1)
	{
?>
		<div id="select_lid">
			<form action="" name="select_lid">
				<div style="margin:10px auto;text-align:center;">
					<div><?=GetMessage("NEWO_SELECT_SITE")?></div><br />
					<select name="LID" onChange="fLidChange(this);">
						<option selected="selected" value=""><?=GetMessage("NEWO_SELECT_SITE")?></option>
						<?
						foreach ($arSitesShop as $key => $val)
						{
						?>
							<option value="<?=$val["ID"]?>"><? echo $val["NAME"]." (".$val["ID"].")";?></option>
						<?
						}
						?>
					</select>
				</div>
				<script type="text/javascript">
					function fLidChange(el)
					{
						BX.showWait();
						BX.ajax.post("/bitrix/admin/sale_order_new.php", "<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&lang=<?=LANGUAGE_ID?>&LID=" + el.value, fLidChangeResult);
					}
					function fLidChangeResult(result)
					{
						fLidChangeDisableButtons(false);
						BX.closeWait();
						if (result.length > 0)
						{
							document.getElementById("select_lid").innerHTML = result;
						}
					}
					function fLidChangeDisableButtons(val)
					{
						var btn = document.getElementById("btn-save");
						if (btn)
							btn.disabled = val;
						btn = document.getElementById("btn-cancel");
						if (btn)
							btn.disabled = val;
					}
					BX.ready(function(){ fLidChangeDisableButtons(true); });
				</script>
			</form>
		</div>
<?
		die();
	}
	else
	{
		echo '<div style="margin:10px auto;text-align:center;">';
		echo GetMessage("NEWO_NO_SITE_SELECT");
		echo '<div>';
		die();
	}
}

if (!isset($str_CURRENCY) OR $str_CURRENCY == "")
	$str_CURRENCY = CSaleLang::GetLangCurrency($LID);

if (isset($ID) && $ID > 0)
	$title = GetMessage("SOEN_TAB_ORDER_TITLE");
else
	$title = GetMessage("SOEN_TAB_ORDER_NEW_TITLE");

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("SOEN_TAB_ORDER"), "ICON" => "sale", "TITLE" => $title),
);
$tabControl = new CAdminForm("order_edit_info", $aTabs, false, true);
$tabControl->SetShowSettings(false);

if (isset($ID) && $ID > 0)
	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("NEWO_TITLE_EDIT")));
elseif (isset($LID) && $LID != "")
{
	$siteName = $LID;
	$dbSite = CSite::GetByID($LID);
	if($arSite = $dbSite->Fetch())
		$siteName = $arSite["NAME"]." (".$LID.")";
	$APPLICATION->SetTitle(str_replace("#LID#", $siteName, GetMessage("NEWO_TITLE_ADD")));
}
else
	$APPLICATION->SetTitle(GetMessage("NEWO_TITLE_DEFAULT"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

CJSCore::Init('file_input');

$boolLocked = false;
$intLockUserID = 0;
$strLockUser = '';
$strLockUserExt = '';
$strLockUserInfo = '';
$strLockUserInfoExt = '';
$strLockTime = '';
$strNameFormat = CSite::GetNameFormat(true);

$boolLocked = CSaleOrder::IsLocked($ID, $intLockUserID, $strLockTime);
if ($boolLocked)
{
	$strLockUser = $intLockUserID;
	$strLockUserInfo = $intLockUserID;
	/** @var CDBResult $rsUsers */
	$rsUsers = CUser::GetList('ID',	'ASC', array('ID' => $intLockUserID), array('FIELDS' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME')));
	if ($arOneUser = $rsUsers->Fetch())
	{
		$strLockUser = CUser::FormatName($strNameFormat, $arOneUser);
		$strLockUserInfo = '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$intLockUserID.'">'.$strLockUser.'</a>';
	}
	$strLockUserExt = htmlspecialcharsbx(GetMessage(
		'SOE_ORDER_LOCKED2',
		array(
			'#ID#' => $strLockUser,
			'#DATE#' => $strLockTime,
		)
	));
	$strLockUserInfoExt = GetMessage(
		'SOE_ORDER_LOCKED2',
		array(
			'#ID#' => $strLockUserInfo,
			'#DATE#' => $strLockTime,
		)
	);
}

$aMenu = array();
if (0 < $ID)
{
	$aMenu = array(
		array(
			"ICON" => "btn_list",
			"TEXT" => GetMessage("SOE_TO_LIST"),
			"LINK" => "/bitrix/admin/sale_order_new.php?lang=".LANGUAGE_ID."&ID=".$ID."&dontsave=Y&LID=".CUtil::JSEscape($LID).GetFilterParams("filter_")
		)
	);
}
else
{
	$aMenu = array(
		array(
			"ICON" => "btn_list",
			"TEXT" => GetMessage("SOE_TO_LIST"),
			"LINK" => "/bitrix/admin/sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_")
		)
	);
}

if ($boolLocked && $saleModulePermissions >= 'W')
{
	$aMenu[] = array(
		"TEXT" => GetMessage("SOE_TO_UNLOCK"),
		"LINK" => "/bitrix/admin/sale_order_new.php?ID=".$ID."&unlock=Y&lang=".LANGUAGE_ID.GetFilterParams("filter_"),
	);
}

$link = urlencode(DeleteParam(array("mode")));
$link = urlencode($APPLICATION->GetCurPage())."?mode=settings".($link <> "" ? "&".$link: "");

$bUserCanViewOrder = CSaleOrder::CanUserViewOrder($ID, $arUserGroups, $intUserID);
$bUserCanEditOrder = CSaleOrder::CanUserUpdateOrder($ID, $arUserGroups, $LID);
$bUserCanDeleteOrder = CSaleOrder::CanUserDeleteOrder($ID, $arUserGroups, $intUserID);
$bUserCanCancelOrder = CSaleOrder::CanUserCancelOrder($ID, $arUserGroups, $intUserID);
$bUserCanDeductOrder = CSaleOrder::CanUserChangeOrderFlag($ID, "PERM_DEDUCTION", $arUserGroups);
$bUserCanMarkOrder = CSaleOrder::CanUserMarkOrder($ID, $arUserGroups, $intUserID);
$bUserCanPayOrder = CSaleOrder::CanUserChangeOrderFlag($ID, "P", $arUserGroups);
$bUserCanDeliverOrder = CSaleOrder::CanUserChangeOrderFlag($ID, "D", $arUserGroups);

if ($bUserCanViewOrder && $ID > 0)
{
	$aMenu[] = array(
		"TEXT" => GetMessage("NEWO_DETAIL"),
		"TITLE"=>GetMessage("NEWO_DETAIL_TITLE"),
		"LINK" => "/bitrix/admin/sale_order_view.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_")
	);
}

if ($ID > 0)
{
	$aMenu[] = array(
		"TEXT" => GetMessage("NEWO_TO_PRINT"),
		"TITLE"=>GetMessage("NEWO_TO_PRINT_TITLE"),
		"LINK" => "/bitrix/admin/sale_order_print.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_")
	);
}
if (!$boolLocked && ($saleModulePermissions == "W" || $str_PAYED["PAYED"] != "Y" && $bUserCanDeleteOrder) && 0 < $ID)
{
	$aMenu[] = array(
		"TEXT" => GetMessage("NEWO_ORDER_DELETE"),
		"TITLE"=>GetMessage("NEWO_ORDER_DELETE_TITLE"),
		"LINK" => "javascript:if(confirm('".GetMessageJS("NEWO_CONFIRM_DEL_MESSAGE")."')) window.location='sale_order.php?ID=".$ID."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get().urlencode(GetFilterParams("filter_"))."'",
		"WARNING" => "Y"
	);
}

//delete context menu for remote query
if (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1)
{
	$context = new CAdminContextMenu($aMenu);
	$context->Show();
}


/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/

if ($boolLocked)
{
	CAdminMessage::ShowMessage(array(
		'MESSAGE' => $strLockUserInfoExt,
		'TYPE' => 'ERROR',
		'HTML' => true
	));
}

if ($ID > 0)
{
	DiscountCouponsManager::init(DiscountCouponsManager::MODE_ORDER, array('orderId' => $ID, 'userId' => $str_USER_ID));
}
else
{
	DiscountCouponsManager::init(DiscountCouponsManager::MODE_MANAGER, array('userId' => 0));
}

CAdminMessage::ShowMessage($errorMessage);

//double function from sale.ajax.location/process.js
?><script type="text/javascript">
function getLocation(country_id, region_id, city_id, arParams, site_id, admin_section)
{
	BX.showWait();

	property_id = arParams.CITY_INPUT_NAME;

	function getLocationResult(res)
	{
		BX.closeWait();

		var obContainer = document.getElementById('LOCATION_' + property_id);
		if (obContainer)
		{
			obContainer.innerHTML = res;
		}
	}

	arParams.COUNTRY = parseInt(country_id);
	arParams.REGION = parseInt(region_id);
	arParams.SITE_ID = '<?=LANGUAGE_ID?>';

	arParams.ADMIN_SECTION = "Y";

	var url = '/bitrix/components/bitrix/sale.ajax.locations/templates/.default/ajax.php';
	BX.ajax.post(url, arParams, getLocationResult);
}
</script>
<div id="form_content">
<?
$tabControl->BeginEpilogContent();

if (isset($_REQUEST["user_id"]) && intval($_REQUEST["user_id"]) > 0 && $_POST["btnTypeBuyer"] != "btnBuyerNew")
{
	$str_USER_ID = intval($_REQUEST["user_id"]);
}

?><?=bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<? echo LANGUAGE_ID; ?>">
<input type="hidden" name="LID" value="<?=htmlspecialcharsbx($LID)?>">
<input type="hidden" name="ID" value="<?=$ID?>">
<input type="hidden" name="save_order_data" value="Y">
<input type="hidden" name="RECALC_ORDER" id="RECALC_ORDER" value="N">

<?if (isset($_REQUEST["user_id"]) && intval($_REQUEST["user_id"]) > 0):?>
	<input type="hidden" name="user_id" value="<?=intval($_REQUEST["user_id"])?>">
<?endif;?>
<?
if (isset($_REQUEST["product"]) && !empty($_REQUEST["product"]) && is_array($_REQUEST["product"]))
{
	foreach ($_REQUEST["product"] as $val)
	{
		if(intval($val) > 0)
		{
			?><input type="hidden" name="product[]" value="<?=intval($val)?>"><?
		}
	}
}
?><input type="hidden" name="CART_FIX" value="<? echo (0 < $ID ? 'Y' : 'N'); ?>" id="CART_FIX"><?

$tabControl->EndEpilogContent();

if (!isset($LID) || $LID == "")
{
	$rsSites = CSite::GetList("id", "asc", array("ACTIVE" => "Y", "DEF" => "Y"));
	$arSite = $rsSites->Fetch();
	$LID = $arSite["ID"];
}

$urlForm = "";
if (isset($ID) AND $ID != "")
{
	$urlForm = "&ID=".$ID."&LID=".CUtil::JSEscape($LID);
	if (!$boolLocked)
		CSaleOrder::Lock($ID);
}

$res = \Bitrix\Sale\Internals\PaymentTable::getList(array(
	'select' => array('CNT'),
	'filter' => array(
		'ORDER_ID' => $ID
	),
	'runtime' => array(
		'CNT' => array(
			'data_type' => 'integer',
			'expression' => array('COUNT(ID)')
		)
	)
));
$payment = $res->fetch();

$res = \Bitrix\Sale\Internals\ShipmentTable::getList(array(
	'select' => array('CNT'),
	'filter' => array(
		'ORDER_ID' => $ID
	),
	'runtime' => array(
		'CNT' => array(
			'data_type' => 'integer',
			'expression' => array('COUNT(ID)')
		)
	)
));
$shipment = $res->fetch();

if ($payment['CNT'] > 1 || ($shipment['CNT'] - 1) > 1)
{
	$note = BeginNote();
	$note .= GetMessage('NEW_ERROR_SEVERAL_P_D');
	$note .= EndNote();
	echo $note;
}

$tabControl->Begin(array(
	"FORM_ACTION" => $APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID.$urlForm.GetFilterParams("filter_", false)
));

//order tabs
$tabControl->BeginNextFormTab();

$tabControl->AddSection("NEWO_TITLE_STATUS", GetMessage("NEWO_TITLE_STATUS"));

$tabControl->BeginCustomField("ORDER_STATUS", GetMessage("SOE_STATUS"), true);
?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=GetMessage("SOE_STATUS")?>:</td>
		<td width="60%">
			<?
			$arFilter = array("LID" => LANGUAGE_ID);
			$arGroupByTmp = false;

			if ($saleModulePermissions < "W")
			{
				$arFilter["GROUP_ID"] = $arUserGroups;
				$arFilter["PERM_STATUS_FROM"] = "Y";
				if ($str_STATUS_ID <> '')
					$arFilter["ID"] = $str_STATUS_ID;
				$arGroupByTmp = array("ID", "NAME", "MAX" => "PERM_STATUS_FROM");
			}
			$dbStatusList = CSaleStatus::GetList(
				array(),
				$arFilter,
				$arGroupByTmp,
				false,
				array("ID", "NAME", "SORT")
			);

			if ($dbStatusList->GetNext())
			{
			?>
				<select name="STATUS_ID" id="STATUS_ID">
					<?
					$arFilter = array("LID" => LANG);
					$arGroupByTmp = false;
					if ($saleModulePermissions < "W")
					{
						$arFilter["GROUP_ID"] = $GLOBALS["USER"]->GetUserGroupArray();
						$arFilter["PERM_STATUS"] = "Y";
					}
					$dbStatusListTmp = CSaleStatus::GetList(
						array("SORT" => "ASC"),
						$arFilter,
						$arGroupByTmp,
						false,
						array("ID", "NAME", "SORT")
					);
					while($arStatusListTmp = $dbStatusListTmp->GetNext())
					{
						?><option value="<?echo $arStatusListTmp["ID"] ?>"<?if ($arStatusListTmp["ID"]==$str_STATUS_ID) echo " selected"?>><?echo $arStatusListTmp["NAME"] ?> [<?echo $arStatusListTmp["ID"] ?>]</option><?
					}
					?>
				</select>
				<?
			}
			else
			{
				$arStatusLand = CSaleStatus::GetLangByID($str_STATUS_ID, LANGUAGE_ID);
				echo htmlspecialcharsEx("[".$str_STATUS_ID."] ".$arStatusLand["NAME"]);
			}
			?>
			<input type="hidden" name="old_user_id" id="old_user_id" value="<?=$str_USER_ID?>">
			<input type="hidden" name="user_id" id="user_id" value="<?=$str_USER_ID?>" onchange="fUserGetProfile(this);" >
		</td>
	</tr>
<?
$tabControl->EndCustomField("ORDER_STATUS");

if ($ID > 0)
{
	$arSitesShop = array();
	$rsSites = CSite::GetList("id", "asc", array("ACTIVE" => "Y"));
	while ($arSite = $rsSites->GetNext())
	{
		$site = COption::GetOptionString("sale", "SHOP_SITE_".$arSite["ID"], "");
		if ($arSite["ID"] == $site)
		{
			$arSitesShop[$arSite["ID"]] = array("ID" => $arSite["ID"], "NAME" => $arSite["NAME"]);
		}
	}

	if (count($arSitesShop) > 1)
	{
		$tabControl->BeginCustomField("ORDER_SITE", GetMessage("ORDER_SITE"), true);
		?>
		<tr>
			<td width="40%">
				<?= GetMessage("ORDER_SITE") ?>:
			</td>
			<td width="60%"><?=htmlspecialcharsbx($arSitesShop[$str_LID]["NAME"])." (".$str_LID.")"?>
			</td>
		</tr>
		<?
		$tabControl->EndCustomField("ORDER_SITE");
	}

	$tabControl->BeginCustomField("ORDER_CANCEL", GetMessage("SOE_CANCELED"), true);
	?>
	<tr>
		<td width="40%">
			<?= GetMessage("SOE_CANCELED") ?>:
		</td>
		<td width="60%">
			<input type="checkbox"<?if (!$bUserCanCancelOrder) echo " disabled";?> name="CANCELED" id="CANCELED" value="Y"<?if ($str_CANCELED == "Y") echo " checked";?>>&nbsp;<label for="CANCELED"><?=GetMessage("SO_YES")?></label>
			<?if($str_DATE_CANCELED <> '')
			{
				echo "&nbsp;(".$str_DATE_CANCELED.")";
			}
			?>
		</td>
	</tr>
	<tr>
		<td width="40%" valign="top">
			<?= GetMessage("SOE_CANCEL_REASON") ?>:
		</td>
		<td width="60%" valign="top">
			<textarea name="REASON_CANCELED"<?if (!$bUserCanCancelOrder) echo " disabled";?> rows="2" cols="40"><?= $str_REASON_CANCELED ?></textarea>
		</td>
	</tr>
	<?
	$tabControl->EndCustomField("ORDER_CANCEL");
}

$tabControl->AddSection("NEWO_TITLE_BUYER", GetMessage("NEWO_TITLE_BUYER"));

$tabControl->BeginCustomField("NEWO_BUYER", GetMessage("NEWO_BUYER"), true);

if ($ID <= 0)
{
	?>
<tr>
	<td width="40%" align="right">
		<a onClick="fButtonCurrent('btnBuyerNew')" href="javascript:void(0);" id="btnBuyerNew" class="adm-btn<?if ($_REQUEST["btnTypeBuyer"] == 'btnBuyerNew' || !isset($_REQUEST["btnTypeBuyer"])) echo ' adm-btn-active';?>"><?=GetMessage("NEWO_BUYER_NEW")?></a>
	</td>
	<td width="60%" align="left"><a onClick="fButtonCurrent('btnBuyerExist')" href="javascript:void(0);" id="btnBuyerExist" class="adm-btn<? if ($_REQUEST["btnTypeBuyer"] == 'btnBuyerExist') echo ' adm-btn-active';?>"><?=GetMessage("NEWO_BUYER_SELECT")?></a>
		<?
		$typeBuyerTmp = "btnBuyerNew";
		if ($bVarsFromForm && isset($_REQUEST["btnTypeBuyer"]))
			$typeBuyerTmp = htmlspecialcharsbx($_REQUEST["btnTypeBuyer"]);
		?>

		<input type="hidden" name="btnTypeBuyer" id="btnTypeBuyer" value="<?=$typeBuyerTmp?>" />
	</td>
</tr>
<?
}
?>
<tr>
	<td id="buyer_type_change" colspan="2">

		<script>
			function fChangeLocationCity(item, info, townPropId)
			{
				<?if(CSaleLocation::isLocationProEnabled()):?>
				BX(function(){

					/*
					var cityPropRow = document.querySelector('.-bx-order-property-city');

					if(BX.type.isElementNode(cityPropRow))
					{
						var cityInput = cityPropRow.querySelector('input');

						if(info.insideCity){
							BX.hide(cityPropRow);

							if(BX.type.isElementNode(cityInput))
								cityInput.value = '--';

						}else{
							BX.show(cityPropRow);

							if(BX.type.isElementNode(cityInput) && cityInput.value == '--')
								cityInput.value = '';
						}
					}
					*/

					if(typeof window.doneInit == 'undefined')
						return;

					if(window.jamFire == true){
						window.jamFire = false;
						return;
					}

					if(item.toString().length > 0){
						getZipByLocation(item, function(data){

							var zipInput = document.querySelector('.-bx-property-is-zip');

							if(BX.type.isDomNode(zipInput)){
								zipInput.value = data;
							}

						},function(){
						});
					}

					BX('CART_FIX').value= 'N';
					fRecalProduct('', '', 'N', 'N', null);
				});
				<?else:?>
					BX('CART_FIX').value= 'N';
					fRecalProduct('', '', 'N', 'N', null);
				<?endif?>
			}
		</script>

		<?=fGetBuyerType($str_PERSON_TYPE_ID, $LID, $str_USER_ID, $ID, $bVarsFromForm);?>

		<script type="text/javascript">

		function insertHtmlResult(container, result)
		{
			if(!BX.type.isString(result) || result.length == 0)
				return;

			var parsed = BX.processHTML(result);
			container.innerHTML = parsed.HTML;

			if(typeof parsed.SCRIPT !== 'undefined')
			{
				for(var k in parsed.SCRIPT)
					BX.evalGlobal(parsed.SCRIPT[k].JS);
			}
		}

		function fButtonCurrent(el)
		{
			if (el == 'btnBuyerNew')
			{
				BX.removeClass(BX("btnBuyerExist"), 'adm-btn-active');
				BX.addClass(BX("btnBuyerNew"), 'adm-btn-active');

				BX("btnBuyerExistField").style.display = 'none';
				BX("btnBuyerNewField").style.display = 'table-row';
				BX("btnTypeBuyer").value = 'btnBuyerNew';
				BX("buyer_profile_display").style.display = 'none';

				if (BX("BREAK_NAME"))
				{
					BX("BREAK_NAME").style.display = 'block';
					BX("NO_BREAK_NAME").style.display = 'none';
				}
			}
			else if (el == 'btnBuyerExist' || el == 'btnBuyerExistRemote')
			{
				BX.addClass(BX("btnBuyerExist"), 'adm-btn-active');
				BX.removeClass(BX("btnBuyerNew"), 'adm-btn-active');

				BX("btnBuyerExistField").style.display = 'table-row';
				if(BX("btnBuyerNewField"))
					BX("btnBuyerNewField").style.display = 'none';
				if(BX("btnTypeBuyer"))
					BX("btnTypeBuyer").value = 'btnBuyerExist';

				if (BX("BREAK_NAME"))
				{
					BX("BREAK_NAME").style.display = 'none';
					BX("NO_BREAK_NAME").style.display = 'block';
				}

				if (el == 'btnBuyerExist')
					window.open('/bitrix/admin/user_search.php?lang=<? echo LANGUAGE_ID; ?>&FN=order_edit_info_form&FC=user_id', '', 'scrollbars=yes,resizable=yes,width=840,height=500,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 840)/2-5));
			}
		}

		var orderID = '<?=$ID?>';
		var orderPaySystemID = '<?=$str_PAY_SYSTEM_ID?>';

		function fBuyerChangeType(el)
		{
			var userId = "";

			if (BX("user_id").value != "")
				userId = BX("user_id").value;

			BX.showWait();
			BX.ajax.post('/bitrix/admin/sale_order_new.php', '<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&paysystemid=' + orderPaySystemID + '&ID=' + orderID + '&LID=<?=CUtil::JSEscape($LID)?>&buyertypechange=' + el.value + '&userId=' + userId, fBuyerChangeTypeResult);
		}

		function fBuyerChangeTypeResult(res)
		{
			BX.closeWait();
			var rss = eval( '('+res+')' );

			if (rss["status"] == "ok")
			{
				BX('CART_FIX').value= 'N';

				var userEl = BX("user_id");
				var orderID = '<?=$ID?>';

				locationID = rss["location_id"];
				locationZipID = rss["location_zip_id"];

				insertHtmlResult(document.getElementById("buyer_type_change"), rss['buyertype']);
				insertHtmlResult(document.getElementById("buyer_type_delivery"), rss['buyerdelivery']);

				<?if(CSaleLocation::isLocationProEnabled()):?>
					initZipHandling();
				<?endif?>

				//document.getElementById("buyer_type_change").innerHTML = rss["buyertype"];
				//document.getElementById("buyer_type_delivery").innerHTML = rss["buyerdelivery"];

				if (userEl.value != "" && (orderID == '' || orderID == 0))
				{
					fUserGetProfile(userEl);
				}
				else
				{
					fRecalProduct('', '', 'N', 'N', null);
				}
			}
		}

		function fChangeProfile(el)
		{
			var userId = document.getElementById("user_id").value;
			var buyerType = document.getElementById("buyer_type_id").value;

			if (userId != "" && buyerType != "")
			{
				fGetExecScript(userId, buyerType, el.value);
			}
			else
			{
				BX.closeWait();
			}
		}

		function fLocationResult(result)
		{
			var res = eval( '('+result+')' );

			if (res["status"] == "ok")
			{
				BX('CART_FIX').value= 'N';

				<?if(CSaleLocation::isLocationProEnabled()):?>
					window.jamLocationCallbackFire = true;
					insertHtmlResult(document.querySelector('.location-selector-wrapper.prop-'+parseInt(res["prop_id"])), res["location"]);
					initZipHandling();
					window.jamLocationCallbackFire = false;
				<?else:?>
					document.getElementById("LOCATION_CITY_ORDER_PROP_" + res["prop_id"]).innerHTML = res["location"];
				<?endif?>
				fRecalProduct('', '', 'N', 'N', null);
			}
		}

		function fUserGetProfile(el)
		{
			var userId = el.value,
				buyerType = BX('buyer_type_id').value,
				oldUserId = BX('old_user_id'),
				postData;

			document.getElementById("buyer_profile_display").style.display = "none";

			if (userId != "" && buyerType != "")
			{
				BX.showWait();
				postData = {
					'sessid': BX.bitrix_sessid(),
					'ORDER_AJAX': 'Y',
					'id': <? echo $ID; ?>,
					'LID': '<? echo CUtil::JSEscape($LID); ?>',
					'currency': '<? echo $str_CURRENCY; ?>',
					'userId': userId,
					'buyerType': buyerType,
					'oldUserId': (!!oldUserId ? oldUserId.value : 0)
				};
				BX.ajax.post(
					'/bitrix/admin/sale_order_new.php',
					postData,
					fUserGetProfileResult
				);
			}
		}
		function fUserGetProfileResult(res)
		{
			var profile,
				oldUserId = BX('old_user_id'),
				userId = BX('user_id');
				rs = eval( '('+res+')' );
			if (rs["status"] == "ok")
			{
				BX.closeWait();
				if (!!oldUserId && !!userId)
					oldUserId.value = userId.value;
				document.getElementById("buyer_profile_display").style.display = "table-row";
				document.getElementById("buyer_profile_select").innerHTML = rs["userProfileSelect"];
				document.getElementById("user_name").innerHTML = rs["userName"];

				if (rs["viewed"].length > 0)
				{
					document.getElementById("buyer_viewed").innerHTML = rs["viewed"];
					fTabsSelect('buyer_viewed', 'tab_3');
				}
				else
				{
					document.getElementById("buyer_viewed").innerHTML = '';
					BX('tab_3').style.display = "none";
					BX('buyer_viewed').style.display = "none";

					if (BX('tab_1').style.display == "block")
						fTabsSelect('user_recomendet', 'tab_1');
					else if (BX('tab_2').style.display == "block")
						fTabsSelect('user_basket', 'tab_2');

				}
				if (rs["userBasket"].length > 0)
				{
					document.getElementById("user_basket").innerHTML = rs["userBasket"];
					fTabsSelect('user_basket', 'tab_2');
				}
				else
				{
					document.getElementById("user_basket").innerHTML = '';
					BX('tab_2').style.display = "none";
					BX('user_basket').style.display = "none";

					if (BX('tab_1').style.display == "block")
						fTabsSelect('user_recomendet', 'tab_1');
					else if (BX('tab_3').style.display == "block")
						fTabsSelect('buyer_viewed', 'tab_3');

				}
				profile = document.getElementById("user_profile");
				fChangeProfile(profile);
			}
			else
			{
				BX.closeWait();
			}
		}
		function fGetExecScript(userId, buyerType, profileDefault)
		{
			BX.ajax({
				url: '/bitrix/admin/sale_order_new.php',
				method: 'POST',
				data : '<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&LID=<?=CUtil::JSEscape($LID)?>&userId=' + userId + '&buyerType=' + buyerType + '&profileDefault=' + profileDefault,
				dataType: 'html',
				timeout: 30,
				async: true,
				processData: true,
				scriptsRunFirst: true,
				emulateOnload: true,
				start: true,
				cache: false
			});
			BX.closeWait();
		}
		</script>
	</td>
</tr>
<?
$tabControl->EndCustomField("NEWO_BUYER");

$tabControl->AddSection("BUYER_DELIVERY", GetMessage("SOE_DELIVERY"));

//select basket product and calc weight
$arBasketItem = array();
$arBasketItemIDs = array();
$useStores = false;
$hasSavedBarcodes = false;
$hasProductsWithMultipleBarcodes = false;
$arProductBarcode = array();
$arSku2Parent = array();

$arElementId = array();

$parentItemFound = false;

if ((isset($_REQUEST["PRODUCT"]) AND is_array($_REQUEST["PRODUCT"]) AND !empty($_REQUEST["PRODUCT"])) AND $bVarsFromForm)
{
	foreach ($_REQUEST["PRODUCT"] as $key => $val)
	{
		foreach ($val as $k => $v) // product fields
		{
			if (!is_array($v))
			{
				$val[$k] = htmlspecialcharsbx($v);
			}
			else
			{
				foreach ($v as $kp => $vp)
				{
					foreach ($vp as $kkp => $vvp)
					{
						if (!is_array($vvp))
						{
							$val[$k][$kp][$kkp] = htmlspecialcharsbx($vvp);
						}
						else //barcodes internal arrays
						{
							foreach ($vvp as $kvvp => $vvvp)
							{
								$val[$k][$kp][$kkp][$kvvp] = htmlspecialcharsbx($vvvp);
							}
						}
					}
				}
			}
		}

		$val["ID"] = $key;
		$arBasketItem[$key] = $val;

		//set variables modifying form look
		if ($arBasketItem[$key]["BARCODE_MULTI"] == "Y")
		{
			if (!$hasProductsWithMultipleBarcodes)
				$hasProductsWithMultipleBarcodes = true;
		}

		if (!$useStores && isset($arBasketItem[$key]["STORES"]) && !empty($arBasketItem[$key]["STORES"]) && intval($storeCount) > 0)
			$useStores = true;

		if (!$hasSavedBarcodes && $arBasketItem[$key]["HAS_SAVED_QUANTITY"] == "Y")
			$hasSavedBarcodes = true;

		$arElementId[] = $val["PRODUCT_ID"];
		if ($bUseCatalog)
			$arParent = CCatalogSku::GetProductInfo($val["PRODUCT_ID"]);

		if ($arParent)
			$arElementId[] = $arParent["ID"];

		foreach ($callbackList as $callbackName)
		{
			$arBasketItem[$key][$callbackName] = '';
		}

	}
}
elseif (isset($ID) AND $ID > 0)
{
	$bXmlId = COption::GetOptionString("sale", "show_order_product_xml_id", "N");

	$dbBasket = CSaleBasket::GetList(
		array("ID" => "ASC"),
		array("ORDER_ID" => $ID),
		false,
		false,
		array(
			"ID", "PRODUCT_ID", "PRODUCT_PRICE_ID", "PRICE", "CURRENCY", "WEIGHT",
			"QUANTITY", "NAME", "MODULE", "CALLBACK_FUNC", "NOTES", "DETAIL_PAGE_URL",
			"DISCOUNT_PRICE", "DISCOUNT_VALUE", "ORDER_CALLBACK_FUNC", "CANCEL_CALLBACK_FUNC",
			"PAY_CALLBACK_FUNC", "PRODUCT_PROVIDER_CLASS", "CATALOG_XML_ID", "PRODUCT_XML_ID",
			"VAT_RATE", "BARCODE_MULTI", "RESERVED", "CUSTOM_PRICE", "DIMENSIONS", "TYPE", "SET_PARENT_ID"
		)
	);
	while ($arBasket = $dbBasket->GetNext())
	{
		$arPropsFilter = array("BASKET_ID" => $arBasket["ID"]);

		if ($bXmlId == "N")
			$arPropsFilter["!CODE"] = array("PRODUCT.XML_ID", "CATALOG.XML_ID");

		$arBasket["PROPS"] = array();
		$dbBasketProps = CSaleBasket::GetPropsList(
			array("SORT" => "ASC", "NAME" => "ASC"),
			$arPropsFilter,
			false,
			false,
			array("ID", "LID", "BASKET_ID", "NAME", "VALUE", "CODE", "SORT")
		);
		while ($arBasketProps = $dbBasketProps->GetNext())
			$arBasket["PROPS"][$arBasketProps["ID"]] = $arBasketProps;


		if (CSaleBasketHelper::isSetParent($arBasket) && empty($arBasket['SET_PARENT_ID']))
		{
			$arBasket['SET_PARENT_ID'] = $arBasket['ID'];
		}
		$arBasketItem[$arBasket["ID"]] = $arBasket;

		$arElementId[] = $arBasket["PRODUCT_ID"];
		$arBasketItemIDs[] = $arBasket["ID"];
		if ($bUseCatalog)
		{
			$arParent = CCatalogSku::GetProductInfo($arBasket["PRODUCT_ID"]);

			if ($arParent)
			{
				$arSku2Parent[$arBasket["PRODUCT_ID"]] = $arParent["ID"];
				$arElementId[] = $arParent["ID"];
			}
		}

		if ($arBasketItem[$arBasket["ID"]]["BARCODE_MULTI"] == "Y")
		{
			if (!$hasProductsWithMultipleBarcodes)
				$hasProductsWithMultipleBarcodes = true;
		}

		$arBasketItem[$arBasket["ID"]]["STORES"] = array();

		$arBasketItem[$arBasket["ID"]]["HAS_SAVED_QUANTITY"] = "N";

		$tmpOrderPrice += $arBasket["PRICE"] * $arBasket["QUANTITY"];

		/** @var $productProvider IBXSaleProductProvider */
		if ($productProvider = CSaleBasket::GetProductProvider($arBasket))
		{
			$storeCount = $productProvider::GetStoresCount(array("SITE_ID" => $LID)); // with exact SITE_ID or SITE_ID = NULL

			if ($storeCount > 0)
			{
				$arProductStore = $productProvider::GetProductStores(array(
					"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
					"SITE_ID" => $LID,
					'BASKET_ID' => $arBasket["ID"]
				));
				if (is_array($arProductStore))
				{
					$arBasketItem[$arBasket["ID"]]["STORES"] = $arProductStore;

					if (!$useStores)
						$useStores = true;
				}

				// if barcodes/store quantity are already saved for this product,
				// then check if barcodes are still valid and save them to the store array
				$ind = 0;
				$dbres = CSaleStoreBarcode::GetList(
					array(),
					array("BASKET_ID" => $arBasket["ID"]),
					false,
					false,
					array("ID", "BASKET_ID", "BARCODE", "STORE_ID", "ORDER_ID", "QUANTITY")
				);
				while ($arRes = $dbres->GetNext())
				{
					$arCheckBarcodeFields = array(
						"BARCODE"    => $arRes["BARCODE"],
						"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
						"ORDER_ID"   => $ID
					);

					if ($arBasketItem[$arBasket["ID"]]["BARCODE_MULTI"] == "Y")
						$arCheckBarcodeFields["STORE_ID"] = $arRes["STORE_ID"];

					if ($arRes["BARCODE"] == "")
						$res = true;
					else
						$res = $productProvider::CheckProductBarcode($arCheckBarcodeFields);

					//TODO - not checked anymore - show or hide?
					//saving barcode and quantity info to the specific store array
					// if ($res)
					// {
					foreach ($arBasketItem[$arBasket["ID"]]["STORES"] as $storeId => $arStoreInfo)
					{
						if ($arStoreInfo["STORE_ID"] == $arRes["STORE_ID"])
						{
							$arBasketItem[$arBasket["ID"]]["STORES"][$storeId]["BARCODE"][$arRes["ID"]] = $arRes["BARCODE"];
							$arBasketItem[$arBasket["ID"]]["STORES"][$storeId]["BARCODE_FOUND"][$arRes["ID"]] = ($res) ? "Y" : "N";

							if ($arBasketItem[$arBasket["ID"]]["BARCODE_MULTI"] == "Y")
								$arBasketItem[$arBasket["ID"]]["STORES"][$storeId]["QUANTITY"] += $arRes["QUANTITY"];
							else
								$arBasketItem[$arBasket["ID"]]["STORES"][$storeId]["QUANTITY"] = $arRes["QUANTITY"];
						}
					}

					$arBasketItem[$arBasket["ID"]]["HAS_SAVED_QUANTITY"] = "Y";

					if (!$hasSavedBarcodes)
						$hasSavedBarcodes = true;
					// }
					$ind++;
				}
			}
			// else if ($storeCount == -1) TODO - storeCount = -1 not used at all
			// storeCount = 0 - different logic?
			// }
		}

		if (CSaleBasketHelper::isSetParent($arBasket) || CSaleBasketHelper::isSetItem($arBasket))
		{
			$parentItemFound = true;
		}
	}
}

if (!empty($arBasketItem)) // measures and ratio
{
	$arBasketItem = getMeasures($arBasketItem);
	$arBasketItem = getRatio($arBasketItem);
}

if ($parentItemFound === true && !empty($arBasketItem) && is_array($arBasketItem))
{
	$arBasketItem = CSaleBasketHelper::reSortItems($arBasketItem, true);
}

$arProductData = array();
$arIblockProps = array();
$arTmpColumns = array();
$arColumnsOptions = CUserOptions::GetOption("order_basket_table", "table_columns");

$arCustomSelectFields = array();
if ($arColumnsOptions)
{
	$arTmpColumns = explode(",", $arColumnsOptions["columns"]);

	$count = 0;
	foreach ($arTmpColumns as $id => $columnCode)
	{
		if (mb_substr($columnCode, 0, 9) == "PROPERTY_" && $count < PROP_COUNT_LIMIT)
		{
			$arCustomSelectFields[] = $columnCode;

			$dbres = CIBlockProperty::GetList(array(), array("CODE" => mb_substr($columnCode, 9)));
			if ($arPropData = $dbres->GetNext())
				$arIblockProps[$columnCode] = $arPropData;

			$count++;
		}
	}
}

if (!empty($arElementId))
{
	$arSelect = array_merge(array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "IBLOCK_TYPE_ID", "PREVIEW_PICTURE", "DETAIL_PICTURE"), $arCustomSelectFields);

	$arProductData = getProductProps($arElementId, $arSelect);
}

$productWeight = 0.0;
foreach ($arBasketItem as $key => &$arItem)
{
	if (!empty($arProductData[$arItem["PRODUCT_ID"]]))
	{
		if ($arItem["MODULE"] == "catalog" && $bUseIblock)
		{
			$arBasketItem[$key]["EDIT_PAGE_URL"] = CIBlock::GetAdminElementEditLink($arProductData[$arItem["PRODUCT_ID"]]["IBLOCK_ID"], $arItem["PRODUCT_ID"], array(
				"find_section_section" => $arProductData[$arItem["PRODUCT_ID"]]["IBLOCK_SECTION_ID"],
				'WF' => 'Y',
			));
		}

		foreach ($arProductData[$arItem["PRODUCT_ID"]] as $productKey => $value)
		{
			if ((mb_substr($productKey, 0, 9) === "PROPERTY_") && (mb_substr($productKey, -6) === "_VALUE"))
			{
				$propertyCode = str_replace("_VALUE", "", $productKey);
				$arItem[$propertyCode] = $value;
			}
		}

		if (array_key_exists($arItem["PRODUCT_ID"], $arSku2Parent)) // if sku element doesn't have value of some property - we'll show parent element value instead
		{
			foreach ($arCustomSelectFields as $field)
			{
				$fieldVal = $field."_VALUE";
				$parentId = $arSku2Parent[$arItem["PRODUCT_ID"]];

				if ((!isset($arItem[$field]) || (isset($arItem[$field]) && $arItem[$field] == ''))
					&& (isset($arProductData[$parentId][$fieldVal]) && !empty($arProductData[$parentId][$fieldVal]))) // can be array or string
				{
					$arItem[$field] = $arProductData[$parentId][$fieldVal];
				}
			}
		}
	}

	if (!CSaleBasketHelper::isSetParent($arItem))
	{
		$productWeight += ($arItem["WEIGHT"] * $arItem["QUANTITY"]);
	}
}
unset($arItem);

$arDeliveryOrder = fGetDeliverySystemsHTML($locationID, $locationZipID, $productWeight, ($str_PRICE-$str_PRICE_DELIVERY), $str_CURRENCY, $LID, $str_DELIVERY_ID, $arBasketItem);

$tabControl->BeginCustomField("DELIVERY_SERVICE", GetMessage("NEWO_DELIVERY_SERVICE"), true);
?>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%">
			<?=GetMessage("SOE_DELIVERY_COM")?>:
		</td>
		<td width="60%" class="adm-detail-content-cell-r">
			<div id="DELIVERY_SELECT"><?=$arDeliveryOrder["DELIVERY"]; ?></div>
			<div id="DELIVER_ID_DESC"><?=$arDeliveryOrder["DELIVERY_DEFAULT_DESCRIPTION"]?></div>
		</td>
	</tr>
	<tr>
		<td class="adm-detail-content-cell-l">
			<?=GetMessage("SOE_DELIVERY_PRICE")?>:
		</td>
		<td class="adm-detail-content-cell-r">
			<?
				$deliveryPrice = roundEx($str_PRICE_DELIVERY, SALE_VALUE_PRECISION);

				if ($bVarsFromForm)
					$deliveryPrice = roundEx($PRICE_DELIVERY, SALE_VALUE_PRECISION);
			?>
			<input type="text" onChange="fChangeDeliveryPrice();" name="PRICE_DELIVERY" id="DELIVERY_ID_PRICE" size="10" maxlength="20" value="<?=$deliveryPrice;?>" >
			<input type="hidden" name="change_delivery_price" value="N" id="change_delivery_price">
			<script type="text/javascript">
				function fChangeDeliveryPrice()
				{
					document.getElementById("change_delivery_price").value = "Y";
					fRecalProduct('', '', 'N', 'N', null);
				}

				function fChangeDelivery()
				{
					fGetRelatedOrderProps();
					BX('CART_FIX').value= 'N';
					document.getElementById("change_delivery_price").value = "N";
					fRecalProduct('', '', 'N', 'N', null);
				}
			</script>
		</td>
	</tr>
	<?
	if ($bVarsFromForm)
		$priceDeliveryDiff = $PRICE_DELIVERY_DIFF;
	else
		$priceDeliveryDiff = SaleFormatCurrency(roundEx($arDeliveryOrder["DELIVERY_DEFAULT_PRICE"] - $str_PRICE_DELIVERY, SALE_VALUE_PRECISION), $str_CURRENCY);

	$hidden = (preg_replace("/[^0-9]/", '', $priceDeliveryDiff) !== '' ? '' : 'style="display:none"');
	?>
	<tr id="DELIVERY_PRICE_DIFF_BLOCK" <?=$hidden?>>
		<td>
			<?=GetMessage("SOE_DELIVERY_PRICE_DIFF")?>:
		</td>
		<td id="DELIVERY_PRICE_DIFF">
			<?=$priceDeliveryDiff?>
		</td>
		<input type="hidden" value="<?=$priceDeliveryDiff?>" name="PRICE_DELIVERY_DIFF" id="PRICE_DELIVERY_DIFF" />
	</tr>
<?
$tabControl->EndCustomField("DELIVERY_SERVICE");

if($ID > 0)
{
	$tabControl->BeginCustomField("ORDER_ALLOW_DELIVERY", GetMessage("SOE_DELIVERY_ALLOWED"), true);
	?>
	<tr>
		<td width="40%">
			<?= GetMessage("SOE_DELIVERY_ALLOWED") ?>:
		</td>
		<td width="60%">
			<input type="checkbox" name="ALLOW_DELIVERY" id="ALLOW_DELIVERY"<?if (!$bUserCanDeliverOrder) echo " disabled";?> value="Y"<?if ($str_ALLOW_DELIVERY == "Y") echo " checked";?>>&nbsp;<label for="ALLOW_DELIVERY"><?=GetMessage("SO_YES")?></label>
			<?if($str_DATE_ALLOW_DELIVERY <> '')
			{
				echo "&nbsp;(".$str_DATE_ALLOW_DELIVERY.")";
			}
			?>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<?= GetMessage("SOE_DEL_VOUCHER_NUM") ?>:
		</td>
		<td width="60%">
			<input type="text" name="DELIVERY_DOC_NUM" value="<?= $str_DELIVERY_DOC_NUM ?>" size="20" maxlength="20">
		</td>
	</tr>
	<tr>
		<td width="40%">
			<?= GetMessage("SOE_DEL_VOUCHER_DATE") ?>:
		</td>
		<td width="60%">
			<?= CalendarDate("DELIVERY_DOC_DATE", $str_DELIVERY_DOC_DATE, "order_edit_info_form", "10", 'class="typeinput"'); ?>
		</td>
	</tr>
	<?
	$tabControl->EndCustomField("ORDER_ALLOW_DELIVERY");
}
$tabControl->BeginCustomField("ORDER_TRACKING_NUMBER", GetMessage("SOE_TRACKING_NUMBER"));
?>
<tr>
	<td width="40%">
		<?= GetMessage("SOE_TRACKING_NUMBER") ?>:
	</td>
	<td width="60%">
		<input type="text" name="TRACKING_NUMBER" id="TRACKING_NUMBER" size="30" maxlength="250" value="<?=$str_TRACKING_NUMBER;?>" >
	</td>
</tr>
<?
$tabControl->EndCustomField("ORDER_TRACKING_NUMBER");

$tabControl->AddSection("BUYER_PAYMENT", GetMessage("SOE_PAYMENT"));

$tabControl->BeginCustomField("BUYER_PAY_SYSTEM", GetMessage("SOE_PAY_SYSTEM"), true);
?>
<tr>
	<td id="buyer_type_delivery" colspan="2">
		<?=fGetPaySystemsHTML($str_PERSON_TYPE_ID, $str_PAY_SYSTEM_ID);?>
		<script type="text/javascript">
			function fChangePaymentSystem()
			{
				fGetRelatedOrderProps();
				BX('CART_FIX').value= 'N';
				fRecalProduct('', '', 'N', 'N', null);
			}
		</script>
	</td>
</tr>
<?
if ($bVarsFromForm)
{
	$pricePay = $PAY_SYSTEM_PRICE;
}
else
{
	$paySystemPrice = 0;
	if (intval($str_PAY_SYSTEM_ID) > 0)
	{
		$arPaySystem = array();
		$dbPaySystem = CSalePaySystem::GetList(
			array("SORT" => "ASC", "PSA_NAME" => "ASC"),
			array(
				"ACTIVE" => "Y",
				"PERSON_TYPE_ID" => $str_PERSON_TYPE_ID,
				"PSA_HAVE_PAYMENT" => "Y"
			)
		);

		while ($arPaySystem = $dbPaySystem->Fetch())
		{
			if ($arPaySystem["ID"] == $str_PAY_SYSTEM_ID)
			{
				$paySystemPrice = CSalePaySystemsHelper::getPSPrice(
					$arPaySystem,
					$tmpOrderPrice,
					$arDeliveryOrder["DELIVERY_DEFAULT_PRICE"],
					$locationID
				);
			}
		}
	}

	$pricePay = SaleFormatCurrency(roundEx($paySystemPrice, SALE_VALUE_PRECISION), $str_CURRENCY);
}

$hidden = ((float)preg_replace("/[^0-9]/", "", $pricePay) > 0 ?'' : 'style="display:none"');
?>
<tr id="PAY_SYSTEM_PRICE_BLOCK" <?=$hidden?>>
	<td class="adm-detail-content-cell-l">
		<?=GetMessage("SOE_PAY_SYSTEM_PRICE");?>
	</td>
	<td class="adm-detail-content-cell-r" id="PAY_SYSTEM_PRICE_VAL">
		<?=$pricePay?>
	</td>
	<input type="hidden" id="PAY_SYSTEM_PRICE" name="PAY_SYSTEM_PRICE" value="<?=$pricePay?>">
</tr>
<?
$tabControl->EndCustomField("BUYER_PAY_SYSTEM");

if ($ID > 0)
{
	$tabControl->BeginCustomField("ORDER_PAYED", GetMessage("SOE_ORDER_PAID"), true);
	?>
	<tr>
		<td width="40%" valign="top">
			<?= GetMessage("SOE_ORDER_PAID") ?>:
		</td>
		<td width="60%">
			<input type="checkbox"<?if (!$bUserCanPayOrder) echo " disabled";?> name="PAYED" id="PAYED" value="Y"<?if ($str_PAYED == "Y") echo " checked";?> onchange="BX.show(BX('ORDER_PAYED_MORE'))">&nbsp;<label for="PAYED"><?=GetMessage("SO_YES")?></label>
			<?if($str_DATE_PAYED <> '')
			{
				echo "&nbsp;(".$str_DATE_PAYED.")";
			}
			?><div id="ORDER_PAYED_MORE" style="display:none;"><?
			$arPayDefault = fGetPayFromAccount($str_USER_ID, $str_CURRENCY);
			if($str_PAYED == "Y")
			{
				?>
				<input type="checkbox" name="PAY_FROM_ACCOUNT_BACK" id="PAY_FROM_ACCOUNT_BACK" value="Y"/>&nbsp;<label for="PAY_FROM_ACCOUNT_BACK"><?=GetMessage('SOD_PAY_ACCOUNT_BACK')?></label>
				<?
			}
			else
			{
				$buyerCanPay = "none";
				if (floatval($arPayDefault["PAY_BUDGET"]) > 0):
					$buyerCanPay = "block";
				endif;
				?>
				<span id="buyerCanBuy" style="display:<?=$buyerCanPay?>">
					<input type="checkbox" name="PAY_CURRENT_ACCOUNT" id="PAY_CURRENT_ACCOUNT" value="Y" <?if ($PAY_CURRENT_ACCOUNT == "Y") echo " checked";?><?if (!$bUserCanPayOrder) echo " disabled";?>/>&nbsp;<label for="PAY_CURRENT_ACCOUNT"><?=GetMessage("NEWO_CURRENT_ACCOUNT")?> (<span id="PAY_CURRENT_ACCOUNT_DESC"><?=$arPayDefault["PAY_MESSAGE"]?></span>)</label>
				</span>
				<?
			}
			?>
			</div>
		</td>
	</tr>
	<tr>
		<td width="40%">
			<?= GetMessage("SOE_VOUCHER_NUM") ?>:
		</td>
		<td width="60%">
			<input type="text" name="PAY_VOUCHER_NUM" value="<?= $str_PAY_VOUCHER_NUM ?>" size="20" maxlength="20">
		</td>
	</tr>
	<tr>
		<td width="40%">
			<?= GetMessage("SOE_VOUCHER_DATE") ?>:
		</td>
		<td width="60%">
			<?= CalendarDate("PAY_VOUCHER_DATE", $str_PAY_VOUCHER_DATE, "order_edit_info_form", "10", 'class="typeinput"'.((!$bUserCanPayOrder) ? " disabled" : "")); ?>
		</td>
	</tr>
	<?
	$tabControl->EndCustomField("ORDER_PAYED");
}

// order properties related to the delivery and payment systems

$html = "";
$arRelFilter = array();
$arRelFilter["RELATED"]["DELIVERY_ID"] = $str_DELIVERY_ID;
$arRelFilter["RELATED"]["PAYSYSTEM_ID"] = $str_PAY_SYSTEM_ID;

if (isset($arRelFilter["RELATED"]) && ($arRelFilter["RELATED"]["DELIVERY_ID"] <> '' || intval($arRelFilter["RELATED"]["PAYSYSTEM_ID"]) > 0))
{
	$dbRelatedProps = CSaleOrderProps::GetList(array(), $arRelFilter, false, false, array("*"));
	while ($arRelatedProps = $dbRelatedProps->GetNext())
		$arProps[] = $arRelatedProps;

	$arPropsValues = array();
	if (!$bVarsFromForm)
	{
		$dbPropValue = CSaleOrderPropsValue::GetOrderRelatedProps($ID, $arRelFilter["RELATED"]);
		while ($arValue = $dbPropValue->GetNext())
			$arPropsValues[$arValue["ORDER_PROPS_ID"]] = $arValue["VALUE"];
	}

	$propsHTML = getOrderPropertiesHTML($arProps, $arPropsValues, $LID, $str_USER_ID, $ID, $bVarsFromForm);
}

$tabControl->AddSection("RELATED_PROPS", GetMessage("NEW_ORDER_RELATED_PROPS"));
$tabControl->BeginCustomField("RELATED_PROPS_DATA", GetMessage("NEW_ORDER_RELATED_PROPS"), true);
?>
<tr>
	<td id="related_props_content" colspan="2">
		<?=$propsHTML?>
	</td>
</tr>
<?
$tabControl->EndCustomField("RELATED_PROPS_DATA");

$tabControl->AddSection("NEWO_COMMENTS", GetMessage("NEWO_COMMENTS"));
$tabControl->BeginCustomField("NEWO_COMMENTS_A", GetMessage("NEWO_COMMENTS"), true);
?>
<tr>
	<td width="40%" valign="top"><?=GetMessage("SOE_COMMENT")?>:<br /><small><?=GetMessage("SOE_COMMENT_NOTE")?></small></td>
	<td width="60%">
		<textarea name="COMMENTS" cols="40" rows="5"><?=htmlspecialcharsbx($str_COMMENTS)?></textarea>
	</td>
</tr>
<?if ($str_ADDITIONAL_INFO <> ''):?>
<tr>
	<td width="40%" valign="top"><?=GetMessage("SOE_ADDITIONAL")?>:</td>
	<td width="60%">
		<?=htmlspecialcharsbx($str_ADDITIONAL_INFO);?>
	</td>
</tr>
<?
endif;
$tabControl->EndCustomField("NEWO_COMMENTS_A");

//order marked
if($ID > 0)
{
	$tabControl->AddSection("ORDER_MARKING", GetMessage("SOE_MARK"));

	$tabControl->BeginCustomField("ORDER_MARK", GetMessage("SOE_MARKED"), true);
	?>
	<tr>
		<td width="40%">
			<?= GetMessage("SOE_MARKED") ?>:
		</td>
		<td width="60%">
			<input type="checkbox"<?if (!$bUserCanMarkOrder) echo " disabled";?> onclick="fShowReasonMarkedBlock(this.checked);" name="MARKED" id="MARKED" value="Y"<?if ($str_MARKED == "Y") echo " checked";?>>&nbsp;<label for="MARKED"><?=GetMessage("SO_YES");?></label>
			<?if($str_DATE_MARKED <> '' && $str_MARKED == "Y")
			{
				echo "&nbsp;(".$str_DATE_MARKED.")";
			}
			?>
		</td>
	</tr>
	<tr id="reason_marked_block" style="display:<?=($str_DATE_MARKED <> '' && ($str_MARKED == "Y")) ? "table-row" : "none"?>">
		<td width="40%" valign="top">
			<?= GetMessage("SOE_MARK_REASON") ?>:
		</td>
		<td width="60%" valign="top">
			<textarea id="REASON_MARKED" name="REASON_MARKED"<?if (!$bUserCanMarkOrder) echo " disabled";?> rows="5" cols="40"><?= $str_REASON_MARKED ?></textarea>
		</td>
	</tr>
	<script type="text/javascript">
		function fShowReasonMarkedBlock(isChecked)
		{
			var reasonBlock = BX('reason_marked_block');
				reasonTextarea = BX('REASON_MARKED');

			if (isChecked)
			{
				reasonBlock.style.display = 'table-row';
			}
			else
			{
				if (reasonTextarea.value == '')
					reasonBlock.style.display = 'none';
			}
		}
	</script>
	<?
	$tabControl->EndCustomField("ORDER_MARK");

	$tabControl->AddSection("ORDER_DEDUCTION", GetMessage("SOE_DEDUCTION"));

	$tabControl->BeginCustomField("ORDER_DEDUCT", GetMessage("SOE_DEDUCTED"), true);
	?>
	<tr>
		<td width="40%">
			<?
			if ($str_DEDUCTED == "Y")
				echo GetMessage("SOE_DEDUCTED");
			else
				echo GetMessage("SOE_DO_DEDUCT");
			?>
		</td>
		<td width="60%">
			<input name="DEDUCTED" id="DEDUCTED" type="checkbox" <?if(!$bUserCanDeductOrder)echo"disabled";?> value="<?=($str_DEDUCTED == "Y") ? "Y" : "N"?>" <?if($str_DEDUCTED == "Y")echo"checked";?> onclick="toggleStoresView(this, <?=($useStores) ? "true" : "false"?>)">
			<input name="ORDER_DEDUCTED" id="ORDER_DEDUCTED" type="hidden" value="<?=($str_DEDUCTED == "Y") ? "Y" : "N"?>">
			<input name="HAS_PRODUCTS_WITH_BARCODE_MULTI" id="HAS_PRODUCTS_WITH_BARCODE_MULTI" type="hidden" value="<?=($hasProductsWithMultipleBarcodes) ? "Y" : "N"?>" />
			<input name="HAS_SAVED_BARCODES" id="HAS_SAVED_BARCODES" type="hidden" value="<?=($hasSavedBarcodes) ? "Y" : "N"?>" />
			<input name="storeCount" id="storeCount" type="hidden" value="<?=$storeCount?>" />
			<label for="DEDUCTED"><?=GetMessage("SO_YES")?></label>
			<?
			if ($str_DATE_DEDUCTED <> ''):
				echo "&nbsp;(".$str_DATE_DEDUCTED.")";
			endif;
			?>
		</td>
	</tr>
	<tr id="reason_undo_deducted_area" style="display:<? echo ($str_DEDUCTED == "N" && $str_REASON_UNDO_DEDUCTED <> '' ? 'table-row;' : 'none;'); ?>">
		<td width="40%" valign="top">
			<?= GetMessage("SOE_UNDO_DEDUCT_REASON") ?>:
		</td>
		<td width="60%" valign="top">
			<textarea name="REASON_UNDO_DEDUCTED" <?if (!$bUserCanDeductOrder) echo " disabled"?> rows="2" cols="40"><?= $str_REASON_UNDO_DEDUCTED ?></textarea>
		</td>
	</tr>
	<?
	$tabControl->EndCustomField("ORDER_DEDUCT");
}

$tabControl->BeginCustomField("BASKET_CONTAINER", GetMessage("NEWO_BASKET_CONTAINER"), true);
?>
<tr>
	<td colspan="2" valign="top">
		<table width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td width="88%" align="left" class="heading" ><?=GetMessage("NEWO_TITLE_ORDER")?></td>
				<td align="right" nowrap>
					<span title="<?=GetMessage("SOE_ADD_ITEMS")?>" onClick="AddProductSearch();" class="adm-btn adm-btn-green adm-btn-add"  style="display:inline;white-space:nowrap;"><?=GetMessage("SOE_ADD_ITEMS")?></span>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td colspan="2" id="ID_BASKET_CONTAINER">
		<?
		if(
			$bUseIblock
			&& (
				!empty($_REQUEST["productDelay"])
				|| !empty($_REQUEST["productSub"])
				|| !empty($_REQUEST["productNA"])
			)
		)
		{
			echo BeginNote();
			echo GetMessage("NEWO_PRODUCTS_MES")."<br />";
			if(!empty($_REQUEST["productSub"]))
			{
				$dbItem = CIBlockElement::GetList(array(), array("ID" => $_REQUEST["productSub"]), false, false, array("ID", "NAME", "IBLOCK_ID", "IBLOCK_SECTION_ID"));
				while($arItem = $dbItem->Fetch())
					echo "<b><a href=\"".CIBlock::GetAdminElementEditLink($arItem["IBLOCK_ID"], $arItem["ID"], array(
						"find_section_section" => $arItem["IBLOCK_SECTION_ID"],
						'WF' => 'Y',
					))."\">".htmlspecialcharsbx($arItem["NAME"])."</a></b> (".GetMessage("NEWO_PRODUCTS_SUB").")<br />";
			}
			if(!empty($_REQUEST["productDelay"]))
			{
				$dbItem = CIBlockElement::GetList(array(), array("ID" => $_REQUEST["productDelay"]), false, false, array("ID", "NAME", "IBLOCK_ID", "IBLOCK_SECTION_ID"));
				while($arItem = $dbItem->Fetch())
					echo "<b><a href=\"".CIBlock::GetAdminElementEditLink($arItem["IBLOCK_ID"], $arItem["ID"], array(
						"find_section_section" => $arItem["IBLOCK_SECTION_ID"],
						'WF' => 'Y',
					))."\">".htmlspecialcharsbx($arItem["NAME"])."</a></b> (".GetMessage("NEWO_PRODUCTS_DELAY").")<br />";
			}
			if(!empty($_REQUEST["productNA"]))
			{
				$dbItem = CIBlockElement::GetList(array(), array("ID" => $_REQUEST["productNA"]), false, false, array("ID", "NAME", "IBLOCK_ID", "IBLOCK_SECTION_ID"));
				while($arItem = $dbItem->Fetch())
					echo "<b><a href=\"".CIBlock::GetAdminElementEditLink($arItem["IBLOCK_ID"], $arItem["ID"], array(
						"find_section_section" => $arItem["IBLOCK_SECTION_ID"],
						'WF' => 'Y',
					))."\">".htmlspecialcharsbx($arItem["NAME"])."</a></b> (".GetMessage("NEWO_PRODUCTS_NA").")<br />";
			}
			echo EndNote();
		}
		?>
		<script type="text/javascript">
			var arProduct = [];
			var arProductEditCountProps = [];
			var countProduct = 0;
		</script>
		<?
		$arCurFormat = CCurrencyLang::GetCurrencyFormat($str_CURRENCY);

		$CURRENCY_FORMAT = trim(str_replace("#", '', $arCurFormat["FORMAT_STRING"]));
		$CURRENCY_FORMAT = strip_tags(preg_replace(
			'#<script[^>]*?>.*?</script[^>]*?>#is',
			'',
			$CURRENCY_FORMAT
		));

		$ORDER_TOTAL_PRICE = 0;
		$ORDER_PRICE_WITH_DISCOUNT = 0;
		$productCountAll = 0;
		$productWeight = 0;
		$arFilterRecommended = array();
		$WEIGHT_UNIT = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_unit', "", $LID));
		$WEIGHT_KOEF = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_koef', 1, $LID));

		$QUANTITY_FACTORIAL = COption::GetOptionString('sale', 'QUANTITY_FACTORIAL', "N");
		if (!isset($QUANTITY_FACTORIAL) OR $QUANTITY_FACTORIAL == "")
			$QUANTITY_FACTORIAL = 'N';

		//edit form props
		$formTemplateTableStart = '
			<input id="FORM_BASKET_PRODUCT_ID" name="BASKET_PRODUCT_ID" value="" type="hidden">
			<table class="edit-table" style="background-color:rgb(245, 249, 249); border: 1px solid #B8C1DD; width: 600px;font-size:12px;">';
		$formTemplateMain = '<tr style="background-color:rgb(224, 232, 234);color:#525355;font-weight:bold;text-align:center;">
					<td colspan="2" align="center">
					<table width="100%">
					<tr>
						<td align="center">'.GetMessage("SOE_BASKET_EDIT").'</td>
						<td width="10"><a href="javascript:void(0);" onClick="SaleBasketEditTool.PopupHide();" style="color:#525355;float:right;margin-right:5px;font-weight:normal;text-decoration:none;font-size:12px;">&times;<a></td>
					</tr>
					</table>
					</td>
				</tr>
				<tr>
					<td width="40%">&nbsp;</td>
					<td align="left" width="60%">
					<div id="basketError" style="display:none;">
						<table class="message message-error" border="0" cellpadding="0" cellspacing="0" style="border:2px solid #FF0000;color:#FF0000">
							<tr>
								<td>
									<table class="content" border="0" cellpadding="0" cellspacing="0" style="margin:4px;">
										<tr>
											<td valign="top"><div class="icon-error"></div></td>
											<td>
												<span class="message-title" style="font-weight:bold;">'.GetMessage("SOE_BASKET_ERROR").'</span><br>
												<div class="empty" style="height: 5px;"></div><div id="basketErrorText"></div>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div></td>
				</tr>';
		$formTemplateProduct = '<tr id="FORM_NEWPROD_CODE" class="adm-detail-required-field">
					<td class="field-name" align="right">'.GetMessage("SOE_ITEM_ID").':</td>
					<td><input size="10" id="FORM_PROD_BASKET_ID" name="FORM_PROD_BASKET_ID" type="text" value="" tabindex="1"></td>
				</tr>
				<tr class="adm-detail-required-field">
					<td class="field-name" align="right">'.GetMessage("SOE_ITEM_NAME").':</td>
					<td><input size="40" id="FORM_PROD_BASKET_NAME" name="FORM_PROD_BASKET_NAME" type="text" value="" tabindex="2"></td>
				</tr>
				<tr>
					<td class="field-name" align="right">'.GetMessage("SOE_ITEM_PATH").':</td>
					<td><input id="FORM_PROD_BASKET_DETAIL_URL" name="FORM_BASKET_CATALOG_XML_ID" value="" size="40" type="text" tabindex="3"></td>
				</tr>
				<tr>
					<td class="field-name" align="right">'.GetMessage("SOE_BASKET_CATALOG_XML").':</td>
					<td><input id="FORM_BASKET_CATALOG_XML" name="FORM_BASKET_CATALOG_XML" value="" size="40" type="text" tabindex="4"></td>
				</tr>
				<tr>
					<td class="field-name" align="right">'.GetMessage("SOE_BASKET_PRODUCT_XML").':</td>
					<td><input id="FORM_PROD_BASKET_PRODUCT_XML" name="FORM_PROD_BASKET_PRODUCT_XML" value="" size="40" type="text" tabindex="5"></td>
				</tr>
				<tr>
					<td class="field-name" align="right">'.GetMessage("SOE_ITEM_DESCR").':</td>
					<td><input name="FORM_PROD_BASKET_NOTES" id="FORM_PROD_BASKET_NOTES" size="40" maxlength="250" value="" type="text" tabindex="6"></td>
				</tr>';
		$formTemplateProductProps ='<tr>
					<td class="field-name" align="right" valign="top" width="40%">'.GetMessage("SOE_ITEM_PROPS").':</td>
					<td width="60%">
						<table id="BASKET_PROP_TABLE" class="internal" border="0" cellpadding="3" cellspacing="1" style="width: 390px;">
							<tr class="heading" style="border-collapse:collapse;background-color:#E7EAF5;color:#525355;">
								<td align="center">'.GetMessage("SOE_IP_NAME").'</td>
								<td align="center">'.GetMessage("SOE_IP_VALUE").'</td>
								<td align="center">'.GetMessage("SOE_IP_CODE").'</td>
								<td align="center">'.GetMessage("SOE_IP_SORT").'</td>
							</tr>
						</table>

						<input value="'.GetMessage("SOE_PROPERTY_MORE").'" onclick="BasketAddPropSection()" type="button">
					</td>
				</tr>';
		$formTemplateAction = '<tr>
					<td class="field-name" align="right">'.GetMessage("SALE_F_QUANTITY").':</td>
					<td><input name="FORM_PROD_BASKET_QUANTITY" id="FORM_PROD_BASKET_QUANTITY" size="10" maxlength="20" value="1" type="text" tabindex="7"></td>
				</tr>
				<tr>
					<td class="field-name" align="right">'.GetMessage("SALE_F_PRICE").':</td>
					<td><input name="FORM_PROD_BASKET_PRICE" id="FORM_PROD_BASKET_PRICE" size="10" maxlength="20" value="1" type="text" tabindex="8"> ('.$CURRENCY_FORMAT.')</td>
				</tr>
				<tr>
					<td class="field-name" align="right">'.GetMessage("SOE_WEIGHT").':</td>
					<td><input name="FORM_PROD_BASKET_WEIGHT" id="FORM_PROD_BASKET_WEIGHT" size="10" maxlength="20" value="0" type="text" tabindex="9"> ('.GetMessage("SOE_GRAMM").')</td>
				</tr>
				<tr>
					<td colspan="2" align="center"><br><input name="btn1" value="'.GetMessage("SOE_APPLY").'" onclick="SaveProduct();" type="button"> <input name="btn2" value="'.GetMessage("SALE_CANCEL").'" onclick="SaleBasketEditTool.PopupHide();" type="button"></td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>';

		$formTemplateTableFinish = '</table>';

		// basket table columns settings form
		$arUserColumns = array();
		$arDefaultColumns = array(
			"COLUMN_NUMBER" => GetMessage("NEW_COLUMN_NUMBER"),
			"COLUMN_IMAGE" => GetMessage("NEW_COLUMN_IMAGE"),
			"COLUMN_NAME" => GetMessage("NEW_COLUMN_NAME"),
			"COLUMN_QUANTITY" => GetMessage("NEW_COLUMN_QUANTITY"),
			"COLUMN_REMAINING_QUANTITY" => GetMessage("NEW_COLUMN_REMAINING_QUANTITY"),
			"COLUMN_PROPS" => GetMessage("NEW_COLUMN_PROPS"),
			"COLUMN_PRICE" => GetMessage("NEW_COLUMN_PRICE"),
			"COLUMN_SUM" => GetMessage("NEW_COLUMN_SUM"),
		);

		if ($arColumnsOptions)
		{
			$count = 0;
			foreach ($arTmpColumns as $id => $columnCode)
			{
				if (mb_substr($columnCode, 0, 9) == "PROPERTY_" && $count < PROP_COUNT_LIMIT)
				{
					foreach ($arIblockProps as $propData)
					{
						if ($columnCode == "PROPERTY_".$propData["CODE"])
						{
							$arUserColumns[$columnCode] = $propData["NAME"];
							$count++;
							break;
						}
					}
				}
				else
					$arUserColumns[$columnCode] = GetMessage("NEW_".$columnCode);
			}
		}
		else
			$arUserColumns = $arDefaultColumns;

		$arAvailableColumns = array_merge($arDefaultColumns, getAdditionalColumns());

		// exclude already selected columns from all available columns
		foreach ($arUserColumns as $key => $value)
		{
			if (array_key_exists($key, $arAvailableColumns))
				unset($arAvailableColumns[$key]);
		}

		// include required columns into user's set of columns if they are not there yet
		if (!array_key_exists("COLUMN_NAME", $arUserColumns))
			$arUserColumns = array("COLUMN_NAME" => GetMessage("NEW_COLUMN_NAME")) + $arUserColumns;

		if (!array_key_exists("COLUMN_QUANTITY", $arUserColumns))
			$arUserColumns["COLUMN_QUANTITY"] = GetMessage("NEW_COLUMN_QUANTITY");

		if (!array_key_exists("COLUMN_PRICE", $arUserColumns))
			$arUserColumns["COLUMN_PRICE"] = GetMessage("NEW_COLUMN_PRICE");

		if (!array_key_exists("COLUMN_SUM", $arUserColumns))
			$arUserColumns["COLUMN_SUM"] = GetMessage("NEW_COLUMN_SUM");

		// remember user's columns set
		$strUserColumns = implode(",", array_keys($arUserColumns));

		// make html
		$arAvailableColumnsHTML = "";
		foreach ($arAvailableColumns as $key => $value)
			$arAvailableColumnsHTML .= "<option value=".$key.">".$value."</option>";

		$arUserColumnsHTML = "";
		foreach ($arUserColumns as $key => $value)
			$arUserColumnsHTML .= "<option value=".$key.">".$value."</option>";

		$IDs = "";
		foreach ($arBasketItem as $val)
		{
			if ($IDs == '')
				$IDs = $val["PRODUCT_ID"];
			else
				$IDs .= ",".$val["PRODUCT_ID"];
		}

		$settingsTemplate = '
			<div id="columns_form">
				<table width="100%">
					<tr>
						<td colspan="2" align="center">
							<table>
								<tr>
									<td style="background-image:none" nowrap>
										<div style="margin-bottom:5px">'.GetMessage("NEWO_AVAILABLE_COLUMNS").'</div>
											<div class="scrollable">
												<select
													name="view_all_cols"
													class="settings_select"
													multiple
													size="'.count($arAvailableColumns).'"
													ondblclick="this.form.add_btn.onclick()"
													onchange="this.form.add_btn.disabled = (this.selectedIndex == -1)"
												>
												'.$arAvailableColumnsHTML.'
												</select>
											</div>
										</div>
									</td>
									<td style="background-image:none">
										<div style="margin-bottom:5px"><input type="button" name="add_btn" value="&gt;" title="'.GetMessage("NEWO_ADD_COLUMN").'" style="width:30px;" disabled onclick="jsSelectUtils.addSelectedOptions(this.form.view_all_cols, this.form.view_cols, false); jsSelectUtils.deleteSelectedOptions(this.form.view_all_cols); "></div>
										<div style="margin-bottom:5px"><input type="button" name="del_btn" value="&lt;" title="'.GetMessage("NEWO_DELETE_COLUMN").'" style="width:30px;" disabled onclick="jsSelectUtils.addSelectedOptions(this.form.view_cols, this.form.view_all_cols, false, true); jsSelectUtils.deleteSelectedOptions(this.form.view_cols);"></div>
									</td>
									<td style="background-image:none" nowrap>
										<div style="margin-bottom:5px">'.GetMessage("NEWO_SELECTED_COLUMNS").'</div>
											<div class="scrollable">
												<select
													class="settings_select"
													name="view_cols"
													multiple
													size="'.count($arUserColumns).'"
													ondblclick="this.form.del_btn.onclick()"
													onchange="this.form.del_btn.disabled = this.form.up_btn.disabled = this.form.down_btn.disabled = (this.selectedIndex == -1)"
													>
												'.$arUserColumnsHTML.'
												</select>
											</div>
										</div>
									</td>
									<td style="background-image:none">
										<div style="margin-bottom:5px"><input type="button" name="up_btn" value="'.GetMessage("NEWO_UP").'" title="'.GetMessage("NEWO_MOVE_UP").'" class="bx-grid-btn" style="width:60px;" disabled onclick="jsSelectUtils.moveOptionsUp(this.form.view_cols)"></div>
										<div style="margin-bottom:5px"><input type="button" name="down_btn" value="'.GetMessage("NEWO_DOWN").'" title="'.GetMessage("NEWO_MOVE_DOWN").'" class="bx-grid-btn" style="width:60px;" disabled onclick="jsSelectUtils.moveOptionsDown(this.form.view_cols)"></div>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>';
		$settingsTemplate = CUtil::JSEscape($settingsTemplate);
	?>
	<br>
	<input type="hidden" id="userColumns" name="userColumns" value="<?=htmlspecialcharsbx($strUserColumns)?>" />
	<input type="hidden" id="ids" name="ids" value="<?=$IDs?>" />


	<!-- basket items table -->
	<table id="BASKET_TABLE" cellpadding="3" cellspacing="1" border="0" width="100%" class="internal">

		<!-- table header with stores columns -->
		<tr id="heading_with_stores" class="heading" <?=($useStores && ($str_DEDUCTED == "Y" || $hasSavedBarcodes)) ? "style=\"display:table-row\"" : "style=\"display:none\""?>>
			<td id="table_settings" onclick="showColumnsForm();"></td>
			<? getColumnsHeaders($arUserColumns, "edit", true);?>
		</tr>

		<!-- table header WITHOUT stores columns. Only one is shown at the time -->
		<tr id="heading_without_stores" class="heading" <?=($useStores && ($str_DEDUCTED == "Y" || $hasSavedBarcodes)) ? "style=\"display:none\"" : "style=\"display:table-row\""?>>
			<td id="table_settings" onclick="showColumnsForm();"></td>
			<? getColumnsHeaders($arUserColumns, "edit", false);?>
		</tr>

		<?
		$productNumber = 0;

		foreach ($arBasketItem as $val)
		{
			$productImg = "";
			$arProductImg = array();
			$hidden = "";
			$setItemClass = "";
			if (CSaleBasketHelper::isSetItem($val))
			{
				$hidden = 'style="display:none"';
				$setItemClass = 'class="set_item_'.$val['SET_PARENT_ID'].'"';
			}

			if ($bUseIblock)
			{
				$arProductImg["PREVIEW_PICTURE"] = $arProductData[$val["PRODUCT_ID"]]["PREVIEW_PICTURE"];
				$arProductImg["DETAIL_PICTURE"] = $arProductData[$val["PRODUCT_ID"]]["DETAIL_PICTURE"];

				if ($bUseCatalog)
					$arParent = CCatalogSku::GetProductInfo($val["PRODUCT_ID"]);
				if ($arParent)
				{
					if (empty($arProductImg["PREVIEW_PICTURE"]))
						$arProductImg["PREVIEW_PICTURE"] = $arProductData[$arParent["ID"]]["PREVIEW_PICTURE"];

					if (empty($arProductImg["DETAIL_PICTURE"]))
						$arProductImg["DETAIL_PICTURE"] = $arProductData[$arParent["ID"]]["DETAIL_PICTURE"];
				}

				if($arProductImg["PREVIEW_PICTURE"] != "")
					$productImg = $arProductImg["PREVIEW_PICTURE"];
				elseif($arProductImg["DETAIL_PICTURE"] != "")
					$productImg = $arProductImg["DETAIL_PICTURE"];
			}

			if ($productImg != "")
			{
				$arFile = CFile::GetFileArray($productImg);
				$productImg = CFile::ResizeImageGet($arFile, array('width'=>80, 'height'=>80), BX_RESIZE_IMAGE_PROPORTIONAL, false, false);
				$val["PICTURE"] = $productImg;
			}

			$propsProd = "";
			$countProp = 0;
			if (!empty($val["PROPS"]) && is_array($val["PROPS"]))
			{
				foreach($val["PROPS"] as $valProd)
				{
					$countProp++;
					$propsProd .= '<input type="hidden" name="PRODUCT['.$val["ID"].'][PROPS]['.$countProp.'][NAME]" id="PRODUCT_PROPS_NAME_'.$val["ID"].'_'.$countProp.'" value="'.$valProd["NAME"].'" />';
					$propsProd .= '<input type="hidden" name="PRODUCT['.$val["ID"].'][PROPS]['.$countProp.'][VALUE]" id="PRODUCT_PROPS_VALUE_'.$val["ID"].'_'.$countProp.'" value="'.$valProd["VALUE"].'" />';
					$propsProd .= '<input type="hidden" name="PRODUCT['.$val["ID"].'][PROPS]['.$countProp.'][CODE]" id="PRODUCT_PROPS_CODE_'.$val["ID"].'_'.$countProp.'" value="'.$valProd["CODE"].'" />';
					$propsProd .= '<input type="hidden" name="PRODUCT['.$val["ID"].'][PROPS]['.$countProp.'][SORT]" id="PRODUCT_PROPS_SORT_'.$val["ID"].'_'.$countProp.'" value="'.$valProd["SORT"].'" />';
				}
			}

			$val["QUANTITY"] = $QUANTITY_FACTORIAL == 'Y' ? floatval($val["QUANTITY"]) : intval($val["QUANTITY"]);

			$productCountAll += $val["QUANTITY"];

				if (!CSaleBasketHelper::isSetParent($val))
				{
					$productWeight += ($val["WEIGHT"] * $val["QUANTITY"]);
				}


			if (!CSaleBasketHelper::isSetItem($val))
			{
				$ORDER_TOTAL_PRICE += ($val["PRICE"] + $val["DISCOUNT_PRICE"]) * $val["QUANTITY"];
				$ORDER_PRICE_WITH_DISCOUNT += $val["PRICE"] * $val["QUANTITY"];
			}

			$arFilterRecommended[] = $val["PRODUCT_ID"];
		?>

		<!-- product row with custom columns -->

		<tr id="BASKET_TABLE_ROW_<?=$val["ID"]?>" <?=$hidden?> <?=$setItemClass?> onmouseover="fMouseOver(this);" onmouseout="fMouseOut(this);">

			<td class="action">
				<?
				if (!CSaleBasketHelper::isSetItem($val)):
					$arActions = array();

					if (!CSaleBasketHelper::isSetParent($val))
						$arActions[] = array("ICON"=>"view", "TEXT"=>GetMessage("SOE_JS_EDIT"), "ACTION"=>"ShowProductEdit(".$val["ID"].");", "DEFAULT"=>true);

					$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SOE_JS_DEL_WITHOUT_DISCOUNT"), "ACTION"=>"DeleteProduct(this, ".$val["ID"].", false);fEnableSub();");
					$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SOE_JS_DEL_WITH_DISCOUNT"), "ACTION"=>"DeleteProduct(this, ".$val["ID"].", true);fEnableSub();");
					$productNumber++;
				?>
					<div class="adm-list-table-popup" onClick="this.blur();BX.adminList.ShowMenu(this, <?=CUtil::PhpToJsObject($arActions)?>);"></div>
				<?
				endif;
				?>
			</td>

			<?
			$arShownColumns = array();

			foreach ($arUserColumns as $columnCode => $columnName)
			{
				// either show column or make it hidden (for ajax manipulations later)
				if (($columnCode == "COLUMN_NUMBER") || (!array_key_exists("COLUMN_NUMBER", $arUserColumns) && !in_array("COLUMN_NUMBER", $arShownColumns)))
				{
					$hidden = (!(array_key_exists("COLUMN_NUMBER", $arUserColumns))) ? "style=\"display:none\"" : "";
					$arShownColumns[] = "COLUMN_NUMBER";
					?>
					<td class="COLUMN_NUMBER" <?=$hidden?>>
						<div><?=(!CSaleBasketHelper::isSetItem($val)) ? $productNumber : ""?></div>
					</td>
					<?
				}

				if (($columnCode == "COLUMN_IMAGE") || (!array_key_exists("COLUMN_IMAGE", $arUserColumns) && !in_array("COLUMN_IMAGE", $arShownColumns)))
				{
					$hidden = (!(array_key_exists("COLUMN_IMAGE", $arUserColumns))) ? "style=\"display:none\"" : "";
					$arShownColumns[] = "COLUMN_IMAGE";
					?>
					<td class="COLUMN_IMAGE" <?=$hidden?>>
						<?if (is_array($val["PICTURE"])):?>
							<img src="<?=$val["PICTURE"]["src"]?>" alt="" border="0" />
						<?else:?>
							<div class="no_foto"><?=GetMessage('NO_FOTO');?></div>
						<?endif?>
					</td>
					<?
				}

				if ($columnCode == "COLUMN_NAME")
				{
					?>
					<td class="COLUMN_NAME">
						<div id="product_name_<?=$val["ID"]?>">
							<?
							$linkClass = (CSaleBasketHelper::isSetItem($val)) ? "set-item-link-name" : "";
							if ($val["EDIT_PAGE_URL"] <> ''):?>
								<a href="<?echo $val["EDIT_PAGE_URL"]?>" target="_blank" class="name-link <?=$linkClass?>">
							<?
							endif;
								echo trim($val["NAME"]);
							if ($val["EDIT_PAGE_URL"] <> ''):
							?>
								</a>
							<?
							endif;
							if (CSaleBasketHelper::isSetParent($val)):
							?>
								<div class="set-link-block">
									<a class="dashed-link show-set-link" href="javascript:void(0);" id="set_toggle_link_<?=$val["SET_PARENT_ID"]?>" onclick="fToggleSetItems('<?=$val["SET_PARENT_ID"]?>');"><?=GetMessage("SOE_SHOW_SET")?></a>
								</div>
							<?
							endif;
							?>
						</div>

						<?if (!isset($val["NEW_PRODUCT"])):?>
							<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][ID]"                 id="BASKET_<?=$val["ID"]?>" value="<?=$val["ID"]?>" />
							<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][BASKET_ID]"          id="BASKET_<?=$val["ID"]?>" value="<?=$val["ID"]?>" />
						<?else:?>
							<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][NEW_PRODUCT]"        id="PRODUCT[<?=$val["ID"]?>][NEW_PRODUCT]" value="NEW_PRODUCT" />
						<?endif;?>

						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][CURRENCY]"               id="CURRENCY_<?=$val["ID"]?>" value="<?=$val["CURRENCY"]?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][CALLBACK_FUNC]"          id="CALLBACK_FUNC_<?=$val["ID"]?>" value="<?=$val["CALLBACK_FUNC"]?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][ORDER_CALLBACK_FUNC]"    id="ORDER_CALLBACK_FUNC_<?=$val["ID"]?>" value="<?=$val["ORDER_CALLBACK_FUNC"]?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][CANCEL_CALLBACK_FUNC]"   id="CANCEL_CALLBACK_FUNC_<?=$val["ID"]?>" value="<?=$val["CANCEL_CALLBACK_FUNC"]?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][PAY_CALLBACK_FUNC]"      id="PAY_CALLBACK_FUNC_<?=$val["ID"]?>" value="<?=$val["PAY_CALLBACK_FUNC"]?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][PRODUCT_PROVIDER_CLASS]" id="PRODUCT_PROVIDER_CLASS_<?=$val["ID"]?>" value="<?=$val["PRODUCT_PROVIDER_CLASS"]?>" >
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][DISCOUNT_PRICE]"         id="PRODUCT[<?=$val["ID"]?>][DISCOUNT_PRICE]" value="<?=$val["DISCOUNT_PRICE"]?>" >
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][VAT_RATE]"               id="PRODUCT[<?=$val["ID"]?>][VAT_RATE]" value="<?=$val["VAT_RATE"]?>" >
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][WEIGHT]"                 id="PRODUCT[<?=$val["ID"]?>][WEIGHT]" value="<?=$val["WEIGHT"]?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][MODULE]"                 id="PRODUCT[<?=$val["ID"]?>][MODULE]" value="<?=$val["MODULE"]?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][NOTES]"                  id="PRODUCT[<?=$val["ID"]?>][NOTES]" value="<?=$val["NOTES"]?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][CATALOG_XML_ID]"         id="PRODUCT[<?=$val["ID"]?>][CATALOG_XML_ID]" value="<?=$val["CATALOG_XML_ID"]?>" >
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][PRODUCT_XML_ID]"         id="PRODUCT[<?=$val["ID"]?>][PRODUCT_XML_ID]" value="<?=$val["PRODUCT_XML_ID"]?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][DETAIL_PAGE_URL]"        id="PRODUCT[<?=$val["ID"]?>][DETAIL_PAGE_URL]" value="<?=$val["DETAIL_PAGE_URL"]?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][NAME]"                   id="PRODUCT[<?=$val["ID"]?>][NAME]" value="<?=$val["NAME"]?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][PRICE_DEFAULT]"          id="PRODUCT[<?=$val["ID"]?>][PRICE_DEFAULT]" value="<?=$val["PRICE"]; ?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][PRODUCT_ID]"             id="PRODUCT[<?=$val["ID"]?>][PRODUCT_ID]" value="<?=$val["PRODUCT_ID"]?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][BARCODE_MULTI]"          id="PRODUCT[<?=$val["ID"]?>][BARCODE_MULTI]" value="<?=($val["BARCODE_MULTI"] == 'Y') ? "Y" : "N"?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][HAS_SAVED_QUANTITY]"     id="PRODUCT[<?=$val["ID"]?>][HAS_SAVED_QUANTITY]" value="<?=($val["HAS_SAVED_QUANTITY"] == 'Y') ? "Y" : "N"?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][CUSTOM_PRICE]"           id="PRODUCT[<?=$val["ID"]?>][CUSTOM_PRICE]" value="<?=($val["CUSTOM_PRICE"] == 'Y') ? "Y" : "N"?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][TYPE]"                   id="PRODUCT[<?=$val["ID"]?>][TYPE]" value="<?=$val["TYPE"];?>" />
						<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][SET_PARENT_ID]"          id="PRODUCT[<?=$val["ID"]?>][SET_PARENT_ID]" value="<?=$val["SET_PARENT_ID"];?>" />

						<input type="hidden" name="edit_page_url_<?=$val["ID"]?>"                    id="edit_page_url_<?=$val["ID"]?>" value="<?=$val["EDIT_PAGE_URL"]?>" />

						<span id="product_props_<?=$val["ID"]?>"><?=$propsProd?></span>
						<script type="text/javascript">
							arProduct[<? echo $val["ID"]; ?>] = '<? echo $val["PRODUCT_ID"]; ?>';
							arProductEditCountProps[<? echo $val["ID"]; ?>] = <? echo $countProp; ?>;
							countProduct = countProduct + 1;
						</script>
					</td>
					<?
				}

				if ($columnCode == "COLUMN_QUANTITY") // including store fields for deducting (hidden by default)
				{
					?>
					<td id="DIV_QUANTITY_<?=$val["ID"]?>" class="COLUMN_QUANTITY">
						<div class="quantity-block">
							<div class="quantity-block-wrap">
								<div class="quantity-wrap">
									<?
									if (!CSaleBasketHelper::isSetItem($val)):
									?>
										<input
											type="text"
											name="PRODUCT[<?=$val["ID"]?>][QUANTITY]"
											id="PRODUCT[<?=$val["ID"]?>][QUANTITY]"
											class="quantity-field"
											value="<?=$val["QUANTITY"]?>"
											size="4"
											maxlength="7"
											onchange="fRecalProduct(<?=$val["ID"]?>, '', 'N', 'N', null);"
										>
									<?
									else:
									?>
										<span id="set_item_quantity_<?=$val["ID"]?>"><?=$val["QUANTITY"]?></span>&nbsp;<span><?=$val["MEASURE_TEXT"]?></span>
										<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][QUANTITY]" id="PRODUCT[<?=$val["ID"]?>][QUANTITY]" value="<?=$val["QUANTITY"]?>" >
									<?
									endif;
									?>
								</div>
								<?
								$left = -3;
								if (isset($val["MEASURE_RATIO"]) && $val["MEASURE_RATIO"] != 1 && !CSaleBasketHelper::isInSet($val)):
									$left = 10;
								?>
									<div class="quantity_control">
										<a href="javascript:void(0)" title="<?=GetMessage("NEWO_UP_RATIO", array("#RATIO#" => $val["MEASURE_RATIO"]));?>" class="plus" onclick="fChangeQuantityValue(<?=$val["ID"]?>, 'up', <?=floatval($val["MEASURE_RATIO"])?>);"></a>
										<a href="javascript:void(0)" title="<?=GetMessage("NEWO_DOWN_RATIO", array("#RATIO#" => $val["MEASURE_RATIO"]));?>" class="minus" onclick="fChangeQuantityValue(<?=$val["ID"]?>, 'down', <?=floatval($val["MEASURE_RATIO"])?>);"></a>
									</div>
								<?
								endif;
								if (isset($val["MEASURE_TEXT"]) && !CSaleBasketHelper::isSetItem($val)):
								?>
									<div class="measure-wrap" style="left: <?=$left?>px">
										<span class="measure-text"><?=$val["MEASURE_TEXT"];?></span>
									</div>
								<?
								endif;
								?>
							</div>
						</div>
						<span class="warning_balance" id="warning_balance_<?=$val["ID"]?>"></span>
					</td>

					<!-- store selector -->
					<td id="td_store_block_<?=$val["ID"]?>" class="store" style="<? echo (($useStores && ($str_DEDUCTED == "Y" || $hasSavedBarcodes)) ? '' : 'display:none'); ?>;">
						<div id="store_block_<?=$val["ID"]?>" style="display:<?=((!CSaleBasketHelper::isSetParent($val)) ? '' : 'none')?>;">
							<div id="store_select_block_<?=$val["ID"]?>">
								<?
								$hasValidStores = true;
								if (is_array($arBasketItem[$val["ID"]]["STORES"]) && !empty($arBasketItem[$val["ID"]]["STORES"])) //is too strong?
								{
									foreach ($arBasketItem[$val["ID"]]["STORES"] as $storeId => $arStore)
									{
										if (!isset($arStore["STORE_ID"]) || intval($arStore["STORE_ID"]) < 0 || !isset($arStore["AMOUNT"]) || intval($arStore["AMOUNT"]) < 0)
										{
											$hasValidStores	= false;
											break;
										}
									}
								}
								else
								{
									$hasValidStores	= false;
								}

								if ($hasValidStores)
								{
									if ($arBasketItem[$val["ID"]]["HAS_SAVED_QUANTITY"] == "Y")
									{
										$ind = 0;
										foreach ($arBasketItem[$val["ID"]]["STORES"] as $storeId => $arStore)
										{
											if (isset($arStore["QUANTITY"]))
											{
												$strSelectID = 'PRODUCT['.$val["ID"].'][STORES]['.$storeId.'][STORE_ID]';
												?>
												<div id="store_select_wrapper_<?=$val["ID"]?>_<?=$storeId?>" class="store_select_wrapper <?=($ind>0) ? "store_row_element" : ""?>">
													<div id="store_select_delete_<?=$val["ID"]?>_<?=$storeId?>" class="store_select_delete <?=($ind>0) ? "store_row_element" : ""?>"></div>
														<select id="<? echo $strSelectID; ?>" name="<? echo $strSelectID; ?>" class="store_first_row_element" onchange="fChangeStoreSelector(this, <?=$val["ID"]?>, <?=$storeId?>, <?=CUtil::PhpToJSObject($arBasketItem[$val["ID"]]["STORES"])?>)" class="<?=($ind>0) ? "store_row_element" : ""?>">
														<?
														foreach($arBasketItem[$val["ID"]]["STORES"] as &$arStore2)
														{
														?>
															<option value="<?=$arStore2["STORE_ID"]?>" <? if ($arStore["STORE_ID"] == $arStore2["STORE_ID"]) echo 'selected'; ?>>
															<? echo htmlspecialcharsex($arStore2["STORE_NAME"])." [".$arStore2["STORE_ID"]."]"; ?>
															</option>
														<?
														}
														if (isset($arStore2))
															unset($arStore2);
														?>
														</select>
													<input name="PRODUCT[<?=$val["ID"]?>][STORES][<?=$storeId?>][STORE_NAME]" id="PRODUCT[<?=$val["ID"]?>][STORES][<?=$storeId?>][STORE_NAME]" type="hidden" value="<?=$arStore["STORE_NAME"]?>">
													<input name="PRODUCT[<?=$val["ID"]?>][STORES][<?=$storeId?>][AMOUNT]" id="PRODUCT[<?=$val["ID"]?>][STORES][<?=$storeId?>][AMOUNT]" type="hidden" value="<?=$arStore["AMOUNT"]?>">
												</div>
												<script type="text/javascript"> //store selector change
													BX.bind(BX('<? echo $strSelectID; ?>'), 'change', function() {
																		return fChangeStoreSelector(this, '<? echo $val["ID"]; ?>', '<? echo $ind; ?>', '<? echo CUtil::JSEscape(CUtil::PHPToJsObject($arBasketItem[$val["ID"]]["STORES"])); ?>');
													});
												</script>
												<?
												if ($ind > 0)
												{
												?>
												<script type="text/javascript"> //store delete button
													BX.bind(BX('store_select_wrapper_<?=$val["ID"]?>_<?=$storeId?>'), 'mouseover', function() {
															BX.addClass(BX('store_select_delete_<?=$val["ID"]?>_<?=$storeId?>'), "store_select_delete_button");
														}
													);
													BX.bind(BX('store_select_wrapper_<?=$val["ID"]?>_<?=$storeId?>'), 'mouseout', function() {
															BX.removeClass(BX('store_select_delete_<?=$val["ID"]?>_<?=$storeId?>'), "store_select_delete_button");
														}
													);
													BX.bind(BX('store_select_delete_<?=$val["ID"]?>_<?=$storeId?>'), 'click', function() {
															return fDeleteStore('<?=$val["ID"]?>', '<?=$val["ID"]?>_<?=$storeId?>', '<?=count($arBasketItem[$val["ID"]]["STORES"])?>');
														}
													);
												</script>
												<?
												}
											$ind++;
											} //quantity is defined
											// $ind++;
										}
										$intTmpCount = count($arBasketItem[$val["ID"]]["STORES"]);
										if ($intTmpCount > 1)
										{
											?>
											<a id="add_store_link_<?=$val["ID"]?>" <?=($intTmpCount > $ind) ? '' : 'style="display:none;"' ?> class="add_store" href="javascript:void(0);" onclick="fAddStore(<?=$val["ID"]?>, <?=CUtil::PhpToJSObject($arBasketItem[$val["ID"]]["STORES"])?>, <? echo ($intTmpCount - 1); ?>, <?=($val["BARCODE_MULTI"] == "Y")? "true" : "false"?>);"><span></span><?=GetMessage("SALE_F_ADD_STORE")?></a>
											<?
										}
									}
									else
									{
									?>
										<div id="store_select_wrapper_<?=$val["ID"]?>_0" class="store_select_wrapper">
											<div id="store_select_delete_<?=$val["ID"]?>_0" class="store_select_delete"></div>
											<select id="PRODUCT[<?=$val["ID"]?>][STORES][0][STORE_ID]" name="PRODUCT[<?=$val["ID"]?>][STORES][0][STORE_ID]" class="store_first_row_element" onchange="fChangeStoreSelector(this, <?=$val["ID"]?>, 0, <?=CUtil::PhpToJSObject($arBasketItem[$val["ID"]]["STORES"])?>)">
											<?
											if (is_array($arBasketItem[$val["ID"]]["STORES"]))
											{
												foreach($arBasketItem[$val["ID"]]["STORES"] as &$arStore)
												{
													?>
													<option value="<?=$arStore["STORE_ID"]?>"><?=htmlspecialcharsex($arStore["STORE_NAME"])." [".$arStore["STORE_ID"]."]"?></option>
													<?
												}
												if (isset($arStore))
													unset($arStore);
											}
											?>
											</select>

											<input name="PRODUCT[<?=$val["ID"]?>][STORES][0][STORE_NAME]" id="PRODUCT[<?=$val["ID"]?>][STORES][0][STORE_NAME]" type="hidden" value="<?=$arBasketItem[$val["ID"]]["STORES"][0]["STORE_NAME"]?>">
											<input name="PRODUCT[<?=$val["ID"]?>][STORES][0][AMOUNT]" id="PRODUCT[<?=$val["ID"]?>][STORES][0][AMOUNT]" type="hidden" value="<?=$arBasketItem[$val["ID"]]["STORES"][0]["AMOUNT"]?>">
											<?
											$intTmpCount = count($arBasketItem[$val["ID"]]["STORES"]);
											if ($intTmpCount > 1)
											{
												?>
												<a id="add_store_link_<?=$val["ID"]?>" class="add_store" href="javascript:void(0);" onclick="fAddStore(<?=$val["ID"]?>, <?=CUtil::PhpToJSObject($arBasketItem[$val["ID"]]["STORES"])?>, <? echo ($intTmpCount - 1); ?>, <?=($val["BARCODE_MULTI"] == "Y")? "true" : "false"?>);"><span></span><?=GetMessage("SALE_F_ADD_STORE")?></a>
												<?
											}
											?>
										</div>
									<?
									}
								}
								else //no valid stores to show
								{
									?><div class="store_product_no_stores"><?=GetMessage("NEWO_NO_PRODUCT_STORES")?></div><?
								}
								?>
							</div>
						</div>
					</td>

					<!-- quantity on the store -->
					<td class="store_amount" id="store_amount_block_<?=$val["ID"]?>" nowrap <?=($useStores && ($str_DEDUCTED == "Y" || $hasSavedBarcodes) ? '' : 'style="display:none;"'); ?>>
						<div style="display:<?=((!CSaleBasketHelper::isSetParent($val)) ? '' : 'none')?>;">
							<?
							if ($hasValidStores)
							{
								if ($arBasketItem[$val["ID"]]["HAS_SAVED_QUANTITY"] == "Y")
								{
									$ind = 0;
									foreach ($arBasketItem[$val["ID"]]["STORES"] as $storeId => $arStore)
									{
										if (isset($arStore["QUANTITY"]))
										{
										?>
										<div id="store_amount_wrapper_<?=$val["ID"]?>_<?=$storeId?>" class="<?=($ind>0) ? "store_row_element" : ""?>">
											<input name="PRODUCT[<?=$val["ID"]?>][STORES][<?=$storeId?>][QUANTITY]" id="PRODUCT[<?=$val["ID"]?>][STORES][<?=$storeId?>][QUANTITY]" value="<?=$arStore['QUANTITY']?>" size="4" maxlength="7" type="text" ><span id="store_max_amount_<?=$val["ID"]?>_<?=$storeId?>">&nbsp;/&nbsp;<?=$arBasketItem[$val["ID"]]["STORES"][$storeId]["AMOUNT"]?></span>
										</div>
										<?
										}
										$ind++;
									}
								}
								else
								{
								?>
									<div id="store_amount_wrapper_<?=$val["ID"]?>_0">
										<input name="PRODUCT[<?=$val["ID"]?>][STORES][0][QUANTITY]" id="PRODUCT[<?=$val["ID"]?>][STORES][0][QUANTITY]" value="" size="4" maxlength="7" type="text" ><span id="store_max_amount_<?=$val["ID"]?>_0">&nbsp;/&nbsp;<?=$arBasketItem[$val["ID"]]["STORES"][0]["AMOUNT"]?></span>
									</div>
								<?
								}
							}
							?>
						</div>
					</td>

					<!-- barcode data (form popup button if BARCODE_MULTI = Y or input field) -->
					<td class="store_barcode" id="store_barcode_block_<?=$val["ID"]?>" <?=($useStores && ($str_DEDUCTED == "Y" || $hasSavedBarcodes) ? "" : 'style="display:none;"'); ?>>
						<div style="display:<?=((!CSaleBasketHelper::isSetParent($val)) ? '' : 'none')?>;">
							<?
							if ($hasValidStores)
							{
								if ($arBasketItem[$val["ID"]]["HAS_SAVED_QUANTITY"] == "Y")
								{
									$ind = 0;
									foreach ($arBasketItem[$val["ID"]]["STORES"] as $storeId => $arStore)
									{
										if (isset($arStore["QUANTITY"]) && $val["BARCODE_MULTI"] == "Y")
										{
										?>
										<div id="store_barcode_wrapper_<?=$val["ID"]?>_<?=$storeId?>" class="<?=($ind>0) ? "store_row_element" : ""?>">
											<div align="center">
												<a onclick="enterBarcodes(<?=$val["ID"]?>, <?=$storeId?>);" class="adm-btn adm-btn-barcode"><?=GetMessage("NEWO_STORE_ADD_BARCODES")?></a>
											</div>
											<div id="STORE_BARCODE_MULTI_DIV_<?=$val["ID"]?>_<?=$storeId?>" class="store_barcode_hidden_div">
												<div style="display: block;" class="store_barcode_scroll_div" id="STORE_BARCODE_DIV_SCROLL_<?=$val["ID"]?>_<?=$storeId?>">
													<table id="STORE_BARCODE_TABLE_MULTI_<?=$val["ID"]?>_<?=$storeId?>">
														<tbody>
															<?
															foreach ($arBasketItem[$val["ID"]]["STORES"] as $storeId2 => $arStore2)
															{
																if (isset($arStore2["BARCODE"]) && $arStore2["STORE_ID"] == $arStore["STORE_ID"])
																{
																	foreach ($arStore2["BARCODE"] as $barcodeId => $barcodeValue)
																	{
																		?>
																		<tr id="STORE_BARCODE_<?=$val["ID"]?>_<?=$storeId2?>_<?=$barcodeId?>">
																			<td>
																				<input
																					id="PRODUCT[<?=$val["ID"]?>][STORES][<?=$storeId2?>][BARCODE][<?=$barcodeId?>]"
																					name="PRODUCT[<?=$val["ID"]?>][STORES][<?=$storeId2?>][BARCODE][<?=$barcodeId?>]"
																					type="text"
																					maxlength="40"
																					size="13"
																					value="<?=$barcodeValue?>"
																					class="<?=setBarcodeClass($arStore2["BARCODE_FOUND"][$barcodeId])?>"
																					onChange="fCheckBarcode(<?=$val["ID"]?>, <?=$storeId2?>, true, <?=$barcodeId?>)"
																					>
																			</td>
																			<td>
																				<input
																					id="PRODUCT[<?=$val["ID"]?>][STORES][<?=$storeId2?>][BARCODE_FOUND][<?=$barcodeId?>]"
																					name="PRODUCT[<?=$val["ID"]?>][STORES][<?=$storeId2?>][BARCODE_FOUND][<?=$barcodeId?>]"
																					type="hidden"
																					value="<?=$arStore2["BARCODE_FOUND"][$barcodeId]?>"
																					>
																			</td>
																			<td>
																				<a class="split-delete-item" tabindex="<?=$barcodeId?>" href="javascript:void(0);" onclick="deleteBarcodeValue(<?=$val["ID"]?>, <?=$storeId2?>, <?=$barcodeId?>); " title="<?=GetMessage("NEWO_STORE_DELETE_BARCODE")?>"></a>
																			</td>
																		</tr>
																		<?
																	}
																}
															}
															?>
														</tbody>
													</table>
												</div>
											</div>
										</div>
										<?
										$ind++;
										}
									}

									if ($val["BARCODE_MULTI"] == "N")
									{
									?>
										<div id="store_barcode_wrapper_<?=$val["ID"]?>_0">
											<input name="PRODUCT[<?=$val["ID"]?>][STORES][0][BARCODE]" id="PRODUCT[<?=$val["ID"]?>][STORES][0][BARCODE]" onChange="fCheckBarcode(<?=$val["ID"]?>, 0)" type="text" />
										</div>
									<?
									}
								}
								else // if no saved quantity
								{
									if ($val["BARCODE_MULTI"] == "Y")
									{
									?>
										<div id="store_barcode_wrapper_<?=$val["ID"]?>_0">
											<div align="center">
												<a onclick="enterBarcodes(<?=$val["ID"]?>, 0);" class="adm-btn adm-btn-barcode"><?=GetMessage("NEWO_STORE_ADD_BARCODES")?></a>
											</div>
											<div id="STORE_BARCODE_MULTI_DIV_<?=$val["ID"]?>_0" class="store_barcode_hidden_div">
												<div style="display: block;" class="store_barcode_scroll_div" id="STORE_BARCODE_DIV_SCROLL_<?=$val["ID"]?>_0">
													<table id="STORE_BARCODE_TABLE_MULTI_<?=$val["ID"]?>_0">
														<tbody></tbody>
													</table>
												</div>
											</div>
										</div>
									<?
									}
									else
									{
									?>
										<input name="PRODUCT[<?=$val["ID"]?>][STORES][0][BARCODE]" id="PRODUCT[<?=$val["ID"]?>][STORES][0][BARCODE]" onChange="fCheckBarcode(<?=$val["ID"]?>, 0)" type="text" />
									<?
									}
								}
							}
							?>
						</div>
					</td>
					<!-- end of store data -->
					<?
				}

				if (($columnCode == "COLUMN_REMAINING_QUANTITY") || (!array_key_exists("COLUMN_REMAINING_QUANTITY", $arUserColumns) && !in_array("COLUMN_REMAINING_QUANTITY", $arShownColumns)))
				{
					$hidden = (!(array_key_exists("COLUMN_REMAINING_QUANTITY", $arUserColumns))) ? "style=\"display:none\"" : "";
					$arShownColumns[] = "COLUMN_REMAINING_QUANTITY";
					?>
					<td class="COLUMN_REMAINING_QUANTITY" <?=$hidden?>>
						<?
						$balance = "0";
						if ($val["MODULE"] == "catalog" && $bUseCatalog)
						{
							$ar_res = CCatalogProduct::GetByID($val["PRODUCT_ID"]);
							$balance = floatval($ar_res["QUANTITY"]);
						}
						?>
						<div id="DIV_BALANCE_<?=$val["ID"]?>"><?=$balance?></div>
					</td>
					<?
				}

				if (($columnCode == "COLUMN_PROPS") || (!array_key_exists("COLUMN_PROPS", $arUserColumns) && !in_array("COLUMN_PROPS", $arShownColumns)))
				{
					$hidden = (!(array_key_exists("COLUMN_PROPS", $arUserColumns))) ? "style=\"display:none\"" : "";
					$arShownColumns[] = "COLUMN_PROPS";
					?>
					<td class="COLUMN_PROPS" <?=$hidden?>>
						<div id="PRODUCT_PROPS_USER_<?=$val["ID"]?>">
							<?
							if (!empty($val["PROPS"]) && is_array($val["PROPS"]))
							{
								foreach($val["PROPS"] as $vv)
								{
									if($vv["VALUE"] <> '')
										echo $vv["NAME"].": ".$vv["VALUE"]."<br />";
								}
							}
							?>
						</div>
					</td>
					<?
				}

				if ($columnCode == "COLUMN_PRICE")
				{
					?>
					<td class="COLUMN_PRICE" style="white-space: nowrap;">
						<?
						$priceBase = ($val["DISCOUNT_PRICE"] + $val["PRICE"]);
						$priceDiscount = 0;
						$discountPercent = "";
						$priceBaseValue = "";

						if ($priceBase > 0 && $val["DISCOUNT_PRICE"] > 0)
							$priceDiscount = roundEx(($val["DISCOUNT_PRICE"] * 100) / $priceBase, SALE_VALUE_PRECISION);
						?>

						<div id="DIV_PRICE_<?=$val["ID"]?>" class="edit_price">
							<?
							if (!CSaleBasketHelper::isSetItem($val)):
							?>
								<span class="default_price_product" id="default_price_<?=$val["ID"]?>">
									<span class="formated_price" id="formated_price_<?=$val["ID"]?>" onclick="fEditPrice(<?=$val["ID"]?>, 'on');">
										<?=CCurrencyLang::CurrencyFormat($val["PRICE"], $str_CURRENCY, false);?>
									</span>
								</span>
								<span class="edit_price_product" id="edit_price_<?=$val["ID"]?>">
									<input maxlength="9" onchange="fRecalcCustomDiscount('<?=$val["ID"]?>');" onblur="fEditPrice('<?=$val["ID"]?>', 'exit');" type="text" name="PRODUCT[<?=$val["ID"]?>][PRICE]" id="PRODUCT[<?=$val["ID"]?>][PRICE]" value="<?=floatval($val["PRICE"])?>" size="5" >
								</span>
								<span id="currency_price_product" class="currency_price">
									<?=$CURRENCY_FORMAT?>
								</span>
								<a href="javascript:void(0);" onclick="fEditPrice(<?=$val["ID"]?>, 'on');">
									<span class="pencil"></span>
								</a>
							<?
							else: // Set items don't have control to change their prices
							?>
								<span class="default_price_product" id="default_price_<?=$val["ID"]?>">
									<span class="formated_price" id="formated_price_<?=$val["ID"]?>">
										<?=CCurrencyLang::CurrencyFormat($val["PRICE"], $str_CURRENCY, false);?>
									</span>
								</span>
								<span id="currency_price_product" class="currency_price">
									<?=$CURRENCY_FORMAT?>
								</span>
								<input type="hidden" name="PRODUCT[<?=$val["ID"]?>][PRICE]" id="PRODUCT[<?=$val["ID"]?>][PRICE]" value="<?=floatval($val["PRICE"])?>">
							<?
							endif;
							?>
						</div>
						<div id="DIV_PRICE_OLD_<?=$val["ID"]?>" class="base_price" style="display:none;"><?=CCurrencyLang::CurrencyFormat($val["PRICE"] + $val["DISCOUNT_PRICE"], $str_CURRENCY, false);?> <span><?=$CURRENCY_FORMAT?></span></div>

						<?
						if ($priceDiscount > 0)
							$discountPercent = "(".GetMessage('NEWO_PRICE_DISCOUNT')." ".$priceDiscount."%)";

						if ($priceBase > 0 && $priceBase != $val["PRICE"])
							$priceBaseValue = CCurrencyLang::CurrencyFormat($priceBase, $str_CURRENCY, false)." <span>".$CURRENCY_FORMAT."</span>";
						?>
							<div class="base_price" id="DIV_BASE_PRICE_WITH_DISCOUNT_<?=$val["ID"]?>"><?=$priceBaseValue;?></div>
							<div class="discount" id="DIV_DISCOUNT_<?=$val["ID"]?>" <?=($val["CUSTOM_PRICE"] == "Y" ? 'style="display:none;"' : ""); ?>><?=$discountPercent?></div>
							<div class="base_price_title" id="base_price_title_<?=$val["ID"]?>">
								<?=($val["CUSTOM_PRICE"] == "Y") ? GetMessage("NEWO_BASE_CATALOG_PRICE") : $val["NOTES"]?>
							</div>
					</td>
					<?
				}

				if ($columnCode == "COLUMN_SUM")
				{
					$hidden = (CSaleBasketHelper::isSetItem($val)) ? "style=\"display:none\"" : "";
					?>
					<td id="DIV_SUMMA_<?=$val["ID"]?>" class="COLUMN_SUM" nowrap>
						<div <?=$hidden?>><?=CCurrencyLang::CurrencyFormat(($val["QUANTITY"] * $val["PRICE"]), $str_CURRENCY, false);?> <span><?=$CURRENCY_FORMAT?></span></div>
					</td>
					<?
				}

				// custom property column
				if (mb_substr($columnCode, 0, 9) == "PROPERTY_")
				{
					?>
					<td class="property_field <?=$columnCode?>">
						<?=getIblockPropInfo($val[$columnCode], $arIblockProps[$columnCode], array("WIDTH" => 90, "HEIGHT" => 90), $ID);?>
					</td>
					<?
				}
			}
			?>
		</tr>
	<?
	}//end foreach $arBasketItem
	if ($ORDER_TOTAL_PRICE == $ORDER_PRICE_WITH_DISCOUNT)
		$ORDER_PRICE_WITH_DISCOUNT = 0;
	?>
	</table>
	<script type="text/javascript">
		function fToggleSetItems(setParentId)
		{
			var elements = document.getElementsByClassName('set_item_' + setParentId);
			var hide = false;

			for (var i = 0; i < elements.length; ++i)
			{
				if (elements[i].style.display == 'none' || elements[i].style.display == '')
				{
					elements[i].style.display = 'table-row';
					hide = true;
				}
				else
					elements[i].style.display = 'none';
			}

			if (hide)
				BX("set_toggle_link_" + setParentId).innerHTML = '<?=GetMessage("SOE_HIDE_SET")?>';
			else
				BX("set_toggle_link_" + setParentId).innerHTML = '<?=GetMessage("SOE_SHOW_SET")?>';
		}
	</script>
	<?
	$style = (array_key_exists("COLUMN_NUMBER", $arUserColumns)) ? "" : "style=\"display: none\"";
	?>
	<table id="total_count_table" <?=$style?>>
		<tr>
			<td id="total_number" colspan="<?=count($arUserColumns) + 1?>">
				<?=str_replace("#NUMBER#", $productNumber, GetMessage("SOE_TOTAL_NUMBER"))?>
			</td>
		</tr>
	</table>
	<!-- END OF PRODUCT TABLE -->

	</td>
</tr>
<tr>
	<td valign="top" align="left" colspan="2">
		<br>
		<div class="set_coupon" id="coupons_block">
			<?=GetMessage("NEWO_BASKET_COUPON")?>:<br>
			<input type="text" name="COUPON" id="COUPON" value="" /><a href="javascript:void(0);" onclick="fRecalByCoupon();"><?=GetMessage("NEWO_COUPON_RECALC")?></a><sup style="color:#BE0000;">1)</sup>
			<?
			$couponsList = DiscountCouponsManager::get(true, array(), true, true);
			$couponErrors = array();
			if ($couponsList === false)
			{
				$couponErrors = DiscountCouponsManager::getErrors();
			}
			elseif (!empty($couponsList))
			{
				foreach ($couponsList as $oneCoupon)
				{
					$couponClass = 'disabled';
					switch ($oneCoupon['STATUS'])
					{
						case DiscountCouponsManager::STATUS_NOT_FOUND:
						case DiscountCouponsManager::STATUS_FREEZE:
							$couponClass = 'bad';
							break;
						case DiscountCouponsManager::STATUS_APPLYED:
							$couponClass = 'good';
							break;
					}
					?><div class="bx_ordercart_coupon"><input disabled readonly type="text" name="OLD_COUPON[]" value="<?=htmlspecialcharsbx($oneCoupon['COUPON']);?>" class="<? echo $couponClass; ?>"><span class="<? echo $couponClass; ?>" data-coupon="<? echo htmlspecialcharsbx($oneCoupon['COUPON']); ?>"></span><div class="bx_ordercart_coupon_notes"><?
						if (isset($oneCoupon['CHECK_CODE_TEXT']))
						{
							echo (
								is_array($oneCoupon['CHECK_CODE_TEXT'])
								? implode('<br>', $oneCoupon['CHECK_CODE_TEXT'])
								: $oneCoupon['CHECK_CODE_TEXT']
							);
						}
					?></div></div><?
				}
				unset($couponClass, $oneCoupon);
			}
			?><div id="global-coupon-errors" style="display: <? echo (empty($couponErrors) ? 'none' : 'block'); ?>;"><?
			if (!empty($couponErrors))
				echo implode('<br>', $couponErrors);
			?></div>
		</div>

		<div style="float:right">
			<script type="text/javascript">
				function fMouseOver(el)
				{
					BX.addClass(BX(el.id), "tr_hover");
				}

				function fMouseOut(el)
				{
					BX.removeClass(BX(el.id), "tr_hover");
				}

				function fEditPrice(item, type)
				{
					if (type == 'on')
					{
						BX('DIV_PRICE_' + item).className = 'edit_price edit_enable';
						BX('PRODUCT['+item+'][PRICE]').focus();
					}
					if (type == 'exit')
					{
						BX('DIV_PRICE_' + item).className = 'edit_price';
					}
				}

				function fRecalcCustomDiscount(item)
				{
					var obFullPrice = BX('PRODUCT['+item+'][PRICE_DEFAULT]');
					var obCurPrice = BX('PRODUCT['+item+'][PRICE]');
					var obDiscount = BX('PRODUCT['+item+'][DISCOUNT_PRICE]');
					if (!!obFullPrice && !!obCurPrice && !!obDiscount)
					{
						obDiscount.value = parseFloat(obFullPrice.value) + parseFloat(obDiscount.value) - parseFloat(obCurPrice.value);
						var obCartFix = BX('CART_FIX');
						if (!!obCartFix && 'Y' == obCartFix.value)
						{
							if (!BX('PAYED') || !BX('PAYED').checked)
								obCartFix.value = 'N';
						}
					}
					fRecalProduct(item, 'price', 'N', 'N', null);
				}

				function AddProductSearch()
				{
					var popup = makeProductSearchDialog({
						caller: 'order_new',
						lang: '<?=LANGUAGE_ID?>',
						site_id: '<?=CUtil::JSEscape($LID)?>',
						callback: 'getParamsByProductId'
					});
					popup.Show();
				}

				function makeProductSearchDialog(params)
				{
					var caller = params.caller || '',
						lang = params.lang || 'ru',
						site_id = params.site_id || '',
						callback = params.callback || '',
						store_id = params.store_id || '0';

					var popup = new BX.CDialog({
						content_url: '/bitrix/tools/sale/product_search_dialog.php?lang='+lang+'&LID='+site_id+'&caller=' + caller + '&func_name='+callback+'&STORE_FROM_ID='+store_id,
						height: Math.max(500, window.innerHeight-400),
						width: Math.max(800, window.innerWidth-400),
						draggable: true,
						resizable: true,
						min_height: 500,
						min_width: 800
					});
					BX.addCustomEvent(popup, 'onWindowRegister', BX.defer(function(){
						popup.Get().style.position = 'fixed';
						popup.Get().style.top = (parseInt(popup.Get().style.top) - BX.GetWindowScrollPos().scrollTop) + 'px';
					}));
					return popup;
				}

			</script>
			<?
			$productAddBool = COption::GetOptionString('sale', 'SALE_ADMIN_NEW_PRODUCT');
			?>
			<?if ($productAddBool == "Y"):?>
				<span title="<?=GetMessage("SOE_NEW_ITEMS")?>" onClick="ShowProductEdit('', 'Y');" style="display:inline;" class="adm-btn adm-btn-green"><?=GetMessage("SOE_NEW_ITEMS")?></span>
			<?endif;?>
			<span title="<?=GetMessage("SOE_ADD_ITEMS")?>" onClick="AddProductSearch();" style="display:inline;" class="adm-btn adm-btn-green adm-btn-add"><?=GetMessage("SOE_ADD_ITEMS")?></span>
		</div>

<script type="text/javascript">
	var currencyBase = '<?=CSaleLang::GetLangCurrency($LID);?>',
		orderWeight = '<?=$productWeight?>',
		orderPrice = '<?=$str_PRICE?>';

	function fEnableSub()
	{
		if (document.getElementById('tbl_sale_order_edit'))
			document.getElementById('tbl_sale_order_edit').style.zIndex  = 10000;
	}
	function pJCFloatDiv()
	{
		var _this = this;
		this.floatDiv = null;
		this.x = this.y = 0;

		this.Show = function(div, left, top)
		{
			var component = BX.ZIndexManager.getComponent(div);
			if (!component)
			{
				BX.ZIndexManager.register(div);
			}

			BX.ZIndexManager.bringToFront(div);

			div.style.left = left + "px";
			div.style.top = top + "px";
		};

		this.Close = function(div)
		{
			if(!div)
				return;
			var frame = document.getElementById(div.id+"_frame");
			if(frame)
				frame.style.visibility = 'hidden';
		}
	}
	var pjsFloatDiv = new pJCFloatDiv();

	function SaleBasketEdit()
	{
		var _this = this;
		this.active = null;

		this.PopupShow = function(div, pos)
		{
			this.PopupHide();
			if(!div)
				return;
			if (typeof(pos) != "object")
				pos = {};

			this.active = div.id;
			div.ondrag = jsUtils.False;

			jsUtils.addEvent(document, "keypress", _this.OnKeyPress);

			div.style.width = div.offsetWidth + 'px';
			div.style.visibility = 'visible';

			var res = jsUtils.GetWindowSize();
			pos['top'] = parseInt(res["scrollTop"] + res["innerHeight"]/2 - div.offsetHeight/2);
			pos['left'] = parseInt(res["scrollLeft"] + res["innerWidth"]/2 - div.offsetWidth/2);
			if(pos['top'] < 5)
				pos['top'] = 5;
			if(pos['left'] < 5)
				pos['left'] = 5;

			pjsFloatDiv.Show(div, pos["left"], pos["top"]);
		}

		this.PopupHide = function()
		{
			var div = document.getElementById(_this.active);
			if(div)
			{
				pjsFloatDiv.Close(div);
				div.parentNode.removeChild(div);
			}
			this.active = null;
			jsUtils.removeEvent(document, "keypress", _this.OnKeyPress);
		}

		this.OnKeyPress = function(e)
		{
			if(!e) e = window.event;
			if(!e) return;
			if(e.keyCode == 27)
				_this.PopupHide();
		},

		this.IsVisible = function()
		{
			return (document.getElementById(this.active).style.visibility != 'hidden');
		}
	}

	check_ctrl_enter = function(e)
	{
		if(!e)
			e = window.event;

		if((e.keyCode == 13 || e.keyCode == 10) && e.ctrlKey)
		{
			alert('submit!');
		}
	}
	SaleBasketEditTool = new SaleBasketEdit();

	function ShowProductEdit(id, newElement)
	{
		var div = document.createElement("DIV");
		div.id = "product_edit";
		div.style.visible = 'hidden';
		div.style.position = 'absolute';
		div.style.zIndex = 1000;
		div.innerHTML = '<?=CUtil::JSEscape($formTemplateTableStart);?>' +
			'<?=CUtil::JSEscape($formTemplateMain); ?>' +
			'<?=CUtil::JSEscape($formTemplateProduct); ?>' +
			'<?=CUtil::JSEscape($formTemplateProductProps); ?>' +
			'<?=CUtil::JSEscape($formTemplateAction); ?>' +
			'<?=CUtil::JSEscape($formTemplateTableFinish); ?>';

		document.body.appendChild(div);
		SaleBasketEditTool.PopupShow(div);

		if (id != "")
		{
			document.getElementById('FORM_NEWPROD_CODE').style.display = 'none';
			document.getElementById('FORM_BASKET_PRODUCT_ID').value = id;
			document.getElementById('FORM_PROD_BASKET_ID').value = id;
			document.getElementById('FORM_PROD_BASKET_NAME').value = document.getElementById('PRODUCT[' + id + '][NAME]').value;
			document.getElementById('FORM_PROD_BASKET_DETAIL_URL').value = document.getElementById('PRODUCT[' + id + '][DETAIL_PAGE_URL]').value;
			document.getElementById('FORM_PROD_BASKET_NOTES').value = document.getElementById('PRODUCT[' + id + '][NOTES]').value;
			document.getElementById('FORM_BASKET_CATALOG_XML').value = document.getElementById('PRODUCT[' + id + '][CATALOG_XML_ID]').value;
			document.getElementById('FORM_PROD_BASKET_PRODUCT_XML').value = document.getElementById('PRODUCT[' + id + '][PRODUCT_XML_ID]').value;
			document.getElementById('FORM_PROD_BASKET_PRICE').value = document.getElementById('PRODUCT[' + id + '][PRICE]').value;
			document.getElementById('FORM_PROD_BASKET_WEIGHT').value = document.getElementById('PRODUCT[' + id + '][WEIGHT]').value;
			document.getElementById('FORM_PROD_BASKET_QUANTITY').value = document.getElementById('PRODUCT[' + id + '][QUANTITY]').value;
		}
		if (id != "" && arProductEditCountProps[id])
		{
			propCnt = parseInt(arProductEditCountProps[id]);
			for (i=1; i <= propCnt; i++)
			{
				if(document.getElementById("PRODUCT_PROPS_NAME_" + id + "_" + i))
				{
					nameProp = document.getElementById("PRODUCT_PROPS_NAME_" + id + "_" + i).value;
					codeProp = document.getElementById("PRODUCT_PROPS_CODE_" + id + "_" + i).value;
					valueProp = document.getElementById("PRODUCT_PROPS_VALUE_" + id + "_" + i).value;
					sortProp = document.getElementById("PRODUCT_PROPS_SORT_" + id + "_" + i).value;

					BasketAddPropSection(i, nameProp, codeProp, valueProp, sortProp);
				}
			}
		}
		else if (id != "")
			arProductEditCountProps[id] = 0;
	}

	function SaveProduct()
	{
		var error = '';

		if (BX('FORM_PROD_BASKET_ID').value.length > 0 && BX('FORM_NEWPROD_CODE').style.display == "none")
		{
			prod_id = BX('FORM_PROD_BASKET_ID').value;
			prod_id = parseInt(prod_id);
		}
		else
		{
			prod_id = countProduct;
			prod_id += 1;
		}

		if(prod_id.length <= 0 || isNaN(prod_id))
			error += '<?=GetMessageJS("SOE_NEW_ERR_PROD_ID")?><br />';
		if(document.getElementById('FORM_PROD_BASKET_NAME').value.length <= 0)
			error += '<?=GetMessageJS("SOE_NEW_ERR_PROD_NAME")?><br />';

		if(error.length > 0)
		{
			BX('basketError').style.display = 'block';
			BX('basketErrorText').innerHTML = error;
		}
		else
		{
			if (!arProductEditCountProps[prod_id])
				arProductEditCountProps[prod_id] = 1;
			propCnt = parseInt(arProductEditCountProps[prod_id]);

			var propsHTML = "";
			var props = "";
			var arProps = new Array();
			if(propCnt > 0)
			{
				for (i=1; i <= propCnt; i++)
				{
					if (document.getElementById('FORM_PROD_PROP_' + prod_id + '_NAME_' + i))
					{
						propName = BX.util.htmlspecialchars(document.getElementById('FORM_PROD_PROP_' + prod_id + '_NAME_' + i).value);
						propCode = BX.util.htmlspecialchars(document.getElementById('FORM_PROD_PROP_' + prod_id + '_CODE_' + i).value);
						propValue = BX.util.htmlspecialchars(document.getElementById('FORM_PROD_PROP_' + prod_id + '_VALUE_' + i).value);
						propSort = BX.util.htmlspecialchars(document.getElementById('FORM_PROD_PROP_' + prod_id + '_SORT_' + i).value);

						if (propName != "" && propValue != "")
						{
							//basket visible props
							if(document.getElementById('FORM_PROD_PROP_' + prod_id + '_NAME_' + i).value.length > 0)
							{
								if(propCode != "CATALOG.XML_ID" && propCode != "PRODUCT.XML_ID")
									propsHTML += propName + ': ' + propValue + '<br />';
							}

							arProps[arProps.length] = {
								NAME: propName,
								CODE: propCode,
								VALUE: propValue
							};
							props += '<input type="hidden" name="PRODUCT[' + prod_id + '][PROPS]['+i+'][NAME]" id="PRODUCT_PROPS_NAME_' + prod_id + '_' + i + '" value="' + propName + '" />';
							props += '<input type="hidden" name="PRODUCT[' + prod_id + '][PROPS]['+i+'][CODE]" id="PRODUCT_PROPS_CODE_' + prod_id + '_' + i + '" value="' + propCode + '" />';
							props += '<input type="hidden" name="PRODUCT[' + prod_id + '][PROPS]['+i+'][VALUE]" id="PRODUCT_PROPS_VALUE_' + prod_id + '_' + i + '" value="' + propValue + '" />';
							props += '<input type="hidden" name="PRODUCT[' + prod_id + '][PROPS]['+i+'][SORT]" id="PRODUCT_PROPS_SORT_' + prod_id + '_' + i + '" value="' + propSort + '" />';
						}
						else
						{
							arProductEditCountProps[prod_id] = propCnt - 1;
						}
					}
				}

				if (document.getElementById('PRODUCT_PROPS_USER_' + prod_id))
				{
					document.getElementById('PRODUCT_PROPS_USER_' + prod_id).innerHTML = propsHTML;
					document.getElementById('product_props_' + prod_id).innerHTML = props;
				}
			}

			if (document.getElementById('FORM_BASKET_PRODUCT_ID').value != "")
			{
				document.getElementById('PRODUCT[' + prod_id + '][DETAIL_PAGE_URL]').value = document.getElementById('FORM_PROD_BASKET_DETAIL_URL').value;

				if (BX('edit_page_url_'+prod_id) && BX('edit_page_url_'+prod_id).value.length > 0)
				{
					urlEdit = "<a href=\""+BX('edit_page_url_'+prod_id).value+"\" target=\"_blank\">"+BX.util.htmlspecialchars(document.getElementById('FORM_PROD_BASKET_NAME').value)+"</a>";
				}
				else
					urlEdit = BX.util.htmlspecialchars(document.getElementById('FORM_PROD_BASKET_NAME').value);

				document.getElementById('product_name_' + prod_id).innerHTML = urlEdit;
				document.getElementById('PRODUCT[' + prod_id + '][NAME]').value = document.getElementById('FORM_PROD_BASKET_NAME').value;
				document.getElementById('PRODUCT[' + prod_id + '][NOTES]').value = document.getElementById('FORM_PROD_BASKET_NOTES').value;
				document.getElementById('base_price_title_' + prod_id).innerHTML = document.getElementById('FORM_PROD_BASKET_NOTES').value;
				document.getElementById('PRODUCT[' + prod_id + '][CATALOG_XML_ID]').value = document.getElementById('FORM_BASKET_CATALOG_XML').value;
				document.getElementById('PRODUCT[' + prod_id + '][PRODUCT_XML_ID]').value = document.getElementById('FORM_PROD_BASKET_PRODUCT_XML').value;
				document.getElementById('PRODUCT[' + prod_id + '][QUANTITY]').value = document.getElementById('FORM_PROD_BASKET_QUANTITY').value;

				if (document.getElementById('PRODUCT[' + prod_id + '][PRICE]').value != document.getElementById('FORM_PROD_BASKET_PRICE').value)
				{
					document.getElementById('PRODUCT[' + prod_id + '][PRICE]').value = document.getElementById('FORM_PROD_BASKET_PRICE').value;
					document.getElementById('CALLBACK_FUNC_' + prod_id).value = "Y";
				}

				if (document.getElementById('PRODUCT[' + prod_id + '][WEIGHT]').value != document.getElementById('FORM_PROD_BASKET_WEIGHT').value)
				{
					document.getElementById('PRODUCT[' + prod_id + '][WEIGHT]').value = document.getElementById('FORM_PROD_BASKET_WEIGHT').value;
					document.getElementById('CALLBACK_FUNC_' + prod_id).value = "Y";
				}
			}
			else
			{
				var arParamsTmp = {
					id: prod_id,
					name: document.getElementById('FORM_PROD_BASKET_NAME').value,
					price: document.getElementById('FORM_PROD_BASKET_PRICE').value,
					priceFormated: document.getElementById('FORM_PROD_BASKET_PRICE').value,
					summaFormated: 1,
					priceType: document.getElementById('FORM_PROD_BASKET_NOTES').value,
					priceDiscount: 0,
					priceBase: document.getElementById('FORM_PROD_BASKET_PRICE').value,
					priceBaseFormat: document.getElementById('FORM_PROD_BASKET_PRICE').value,
					quantity: document.getElementById('FORM_PROD_BASKET_QUANTITY').value,
					url: document.getElementById('FORM_PROD_BASKET_DETAIL_URL').value,
					urlImg: '',
					vatRate: 0,
					weight: document.getElementById('FORM_PROD_BASKET_WEIGHT').value,
					currency: '<?=$str_CURRENCY?>',
					module: '',
					urlEdit: '',
					callback: '',
					skuProps: arProps,
					orderCallback: '',
					cancelCallback: '',
					payCallback: '',
					productProviderClass: '',
					catalogXmlID: document.getElementById('FORM_BASKET_CATALOG_XML').value,
					productXmlID: document.getElementById('FORM_PROD_BASKET_PRODUCT_XML').value
				};
				FillProductFields('', arParamsTmp, '');

				if (props.length > 0)
					document.getElementById('product_props_' + prod_id).innerHTML = props;
			}
			fRecalProduct('', '', 'N', 'N', null);
			SaleBasketEditTool.PopupHide();
		}
	}

	function BasketAddPropSection(id, nameProp, codeProp, valueProp, sortProp)
	{
		var error = '';

		if (!nameProp)
			nameProp = "";
		if (!codeProp)
			codeProp = "";
		if (!valueProp)
			valueProp = "";
		if (!sortProp)
			sortProp = "";
		if (!id)
			id = "";

		if (BX('FORM_PROD_BASKET_ID').value.length > 0 && BX('FORM_NEWPROD_CODE').style.display == "none")
		{
			prod_id = BX('FORM_PROD_BASKET_ID').value;
			prod_id = parseInt(prod_id);
		}
		else
		{
			prod_id = countProduct;
			prod_id += 1;
		}

		if(prod_id.length <= 0 || isNaN(prod_id))
			error += '<?=GetMessage("SOE_NEW_ERR_PROD_ID")?><br />';

		if(error.length > 0)
		{
			document.getElementById('basketError').style.display = 'block';
			document.getElementById('basketErrorText').innerHTML = error;
		}
		else
		{
			if (id == '')
			{
				if (!arProductEditCountProps[prod_id])
					arProductEditCountProps[prod_id] = 0;

				countProp = parseInt(arProductEditCountProps[prod_id]);
				countProp = countProp + 1;
				arProductEditCountProps[prod_id] = countProp;
			}
			else
			{
				countProp = id;
			}

			var oTbl = document.getElementById("BASKET_PROP_TABLE");
			if (!oTbl)
				return;
			var oRow = oTbl.insertRow(-1);
			var oCell = oRow.insertCell(-1);
			oCell.innerHTML = '<input type="text" maxlength="250" size="20" name="FORM_PROD_PROP_' + prod_id + '_NAME_' + countProp + '" id="FORM_PROD_PROP_' + prod_id + '_NAME_' + countProp + '" value="'+BX.util.htmlspecialchars(nameProp)+'" />';
			var oCell = oRow.insertCell(-1);
			oCell.innerHTML = '<input type="text" maxlength="250" size="20" name="FORM_PROD_PROP_' + prod_id + '_VALUE_' + countProp + '" id="FORM_PROD_PROP_' + prod_id + '_VALUE_' + countProp + '" value="'+BX.util.htmlspecialchars(valueProp)+'" />';
			var oCell = oRow.insertCell(-1);
			oCell.innerHTML = '<input type="text" maxlength="250" size="3" name="FORM_PROD_PROP_' + prod_id + '_CODE_' + countProp + '" id="FORM_PROD_PROP_' + prod_id + '_CODE_' + countProp + '" value="'+BX.util.htmlspecialchars(codeProp)+'" />';
			var oCell = oRow.insertCell(-1);
			oCell.innerHTML = '<input type="text" maxlength="10" size="2" name="FORM_PROD_PROP_' + prod_id + '_SORT_' + countProp + '" id="FORM_PROD_PROP_' + prod_id + '_SORT_' + countProp + '" value="'+BX.util.htmlspecialchars(sortProp)+'" />';
		}
	}

	function FillProductFields(index, arParams, iblockID)
	{
		BX('CART_FIX').value= 'N';
		countProduct = countProduct + 1;
		var ID = countProduct;

		if (BX('DEDUCTED') || BX('HAS_SAVED_BARCODES'))
			var bHideStoreInfo = (BX('DEDUCTED').checked || BX('HAS_SAVED_BARCODES').value == "Y") ? false : true;
		else
			var bHideStoreInfo = true;

		// table
		var oTbl = BX("BASKET_TABLE");
		if (!oTbl)
			return;

		var bSetItem = arParams['isSetItem'] == 'Y',
			bSetParent = arParams['isSetParent'] == 'Y';

		// insert new row after the last row of the table
		var rows = oTbl.rows,
			lastRow = rows[rows.length - 1],
			oRow = document.createElement('tr');

		lastRow.parentNode.insertBefore(oRow, lastRow.nextSibling);

		oRow.setAttribute('id','BASKET_TABLE_ROW_' + ID);

		if (!bSetItem)
		{
			oRow.setAttribute('onmouseout','fMouseOut(this);');
			oRow.setAttribute('onmouseover','fMouseOver(this);');
		}

		// insert columns
		var columnsData = BX('userColumns').value.split(','),
			productPropsValues = arParams['productPropsValues'],
			necessaryColumns = ['COLUMN_NUMBER', 'COLUMN_IMAGE', 'COLUMN_PROPS', 'COLUMN_REMAINING_QUANTITY'];

		var oCellAction = oRow.insertCell(-1);
			oCellAction.setAttribute('class', 'action');

		for (var n = 0; n < columnsData.length; n++) // iterate over each column from the user's column set
		{
			columnName = columnsData[n];

			// either show column or add it as hidden
			if ((columnName == "COLUMN_NUMBER") || (!BX.util.in_array("COLUMN_NUMBER", columnsData) && BX.util.in_array("COLUMN_NUMBER", necessaryColumns)))
			{
				var oCellNumber = oRow.insertCell(-1);
				oCellNumber.setAttribute('class','COLUMN_NUMBER');

				if (!BX.util.in_array("COLUMN_NUMBER", columnsData))
				{
					oCellNumber.setAttribute('style', 'display:none');
					necessaryColumns.splice(BX.util.array_search("COLUMN_NUMBER", necessaryColumns),1);
				}
			}

			if ((columnName == "COLUMN_IMAGE") || (!BX.util.in_array("COLUMN_IMAGE", columnsData) && BX.util.in_array("COLUMN_IMAGE", necessaryColumns)))
			{
				var oCellPhoto = oRow.insertCell(-1);
				oCellPhoto.setAttribute('class','COLUMN_IMAGE');

				if (!BX.util.in_array("COLUMN_IMAGE", columnsData))
				{
					oCellPhoto.setAttribute('style', 'display:none');
					necessaryColumns.splice(BX.util.array_search("COLUMN_IMAGE", necessaryColumns),1);
				}
			}

			if (columnName == "COLUMN_NAME")
			{
				var oCellName = oRow.insertCell(-1);
				oCellName.setAttribute('class','COLUMN_NAME');
			}

			if ((columnName == "COLUMN_REMAINING_QUANTITY") || (!BX.util.in_array("COLUMN_REMAINING_QUANTITY", columnsData) && BX.util.in_array("COLUMN_REMAINING_QUANTITY", necessaryColumns)))
			{
				var oCellBalance = oRow.insertCell(-1);
				oCellBalance.setAttribute('class','COLUMN_REMAINING_QUANTITY');

				if (!BX.util.in_array("COLUMN_REMAINING_QUANTITY", columnsData))
				{
					oCellBalance.setAttribute('style', 'display:none');
					necessaryColumns.splice(BX.util.array_search("COLUMN_REMAINING_QUANTITY", necessaryColumns),1);
				}
			}

			if ((columnName == "COLUMN_PROPS") || (!BX.util.in_array("COLUMN_PROPS", columnsData) && BX.util.in_array("COLUMN_PROPS", necessaryColumns)))
			{
				var oCellPROPS = oRow.insertCell(-1);
				oCellPROPS.setAttribute('class','COLUMN_PROPS');

				if (!BX.util.in_array("COLUMN_PROPS", columnsData))
				{
					oCellPROPS.setAttribute('style', 'display:none');
					necessaryColumns.splice(BX.util.array_search("COLUMN_PROPS", necessaryColumns),1);
				}
			}

			if (columnName == "COLUMN_PRICE")
			{
				var oCellPrice = oRow.insertCell(-1);
					oCellPrice.setAttribute('class','COLUMN_PRICE');
					oCellPrice.setAttribute('align','center');
					oCellPrice.setAttribute('nowrap','nowrap');
			}

			if (columnName == "COLUMN_SUM")
			{
				var oCellSumma = oRow.insertCell(-1);
					oCellSumma.setAttribute('id','DIV_SUMMA_' + ID);
					oCellSumma.setAttribute('class','COLUMN_SUM');
					oCellSumma.setAttribute('nowrap','nowrap');
			}

			if (columnName == "COLUMN_QUANTITY") // quantity and store info cells
			{
				var oCellQuantity = oRow.insertCell(-1);
					oCellQuantity.setAttribute('class','COLUMN_QUANTITY');
					oCellQuantity.setAttribute('id','DIV_QUANTITY_' + ID);

				var oCellStore = oRow.insertCell(-1);
					oCellStore.setAttribute('class', 'store');
					oCellStore.setAttribute('id', 'td_store_block_' + ID);
					if (bHideStoreInfo)
						oCellStore.setAttribute('style', 'display:none');

				var oCellStoreQuantity = oRow.insertCell(-1);
					oCellStoreQuantity.setAttribute('class', 'store_amount');
					oCellStoreQuantity.setAttribute('id', 'store_amount_block_' + ID);
					if (bHideStoreInfo)
						oCellStoreQuantity.setAttribute('style', 'display:none');

				var oCellBarcode = oRow.insertCell(-1);
					oCellBarcode.setAttribute('class', 'store_barcode');
					oCellBarcode.setAttribute('id', 'store_barcode_block_' + ID);
					if (bHideStoreInfo)
						oCellBarcode.setAttribute('style', 'display:none');
			}

			if (columnName.indexOf("PROPERTY_") == 0) // property columns
			{
				var cell = oRow.insertCell(-1);
				cell.className = 'property_field ' + columnName;

				if (typeof(productPropsValues[columnName + '_VALUE']) !== 'undefined')
					cell.innerHTML = productPropsValues[columnName + '_VALUE'];
				else
					cell.innerHTML = '';
			}
		}

		product_id = 0;
		var name = '';
		var price = 0.0;
		var priceFormated = '';
		var priceBase = 0.0;
		var priceBaseFormat = '';
		var priceType = '';
		var currency = '';
		var priceDiscount = 0.0;
		var quantity = 0;
		var summaFormated = '';
		var weight = 0;
		var vatRate = 0.0;
		var module = '';
		var valutaFormat = '';
		var catalogXmlID = '';
		var productXmlID = '';
		var url = '';
		var urlImg = '';
		var urlEdit = '';
		var balance = '';
		var priceTotalFormated = '';
		var discountPercent = '';
		var callback = '';
		var orderCallback = '';
		var cancelCallback = '';
		var payCallback = '';
		var productProviderClass = '';
		var arSkuProps = [];
		var barcodeMulti = '';
		var arStores = [];
		var productType = '';
		var setParentId = '';

		if (!!arParams.id)
			product_id = arParams.id;
		if (!!arParams.name)
			name = arParams.name;
		if (!!arParams.price)
			price = arParams.price;
		if (!!arParams.priceFormated)
			priceFormated = arParams.priceFormated;
		if (!!arParams.priceBase)
			priceBase = arParams.priceBase;
		if (!!arParams.priceBaseFormat)
			priceBaseFormat = arParams.priceBaseFormat;
		if (!!arParams.priceType)
			priceType = arParams.priceType;
		if (!!arParams.currency)
			currency = arParams.currency;
		if (!!arParams.priceDiscount)
			priceDiscount = arParams.priceDiscount;
		if (!!arParams.quantity)
			quantity = arParams.quantity;
		if (!!arParams.summaFormated)
			summaFormated = arParams.summaFormated;
		if (!!arParams.weight)
			weight = arParams.weight;
		if (!!arParams.vatRate)
			vatRate = arParams.vatRate;
		if (!!arParams.module)
			module = arParams.module;
		if (!!arParams.valutaFormat)
			valutaFormat = arParams.valutaFormat;
		if (!!arParams.catalogXmlID)
			catalogXmlID = arParams.catalogXmlID;
		if (!!arParams.productXmlID)
			productXmlID = arParams.productXmlID;
		if (!!arParams.url)
			url = arParams.url;
		if (!!arParams.urlImg)
			urlImg = arParams.urlImg;
		if (!!arParams.urlEdit)
			urlEdit = arParams.urlEdit;
		if (!!arParams.balance)
			balance = arParams.balance;
		if (!!arParams.priceTotalFormated)
			priceTotalFormated = arParams.priceTotalFormated;
		if (!!arParams.discountPercent)
			discountPercent = arParams.discountPercent;
		if (!!arParams.callback)
			callback = arParams.callback;
		if (!!arParams.orderCallback)
			orderCallback = arParams.orderCallback;
		if (!!arParams.cancelCallback)
			cancelCallback = arParams.cancelCallback;
		if (!!arParams.payCallback)
			payCallback = arParams.payCallback;
		if (!!arParams.productProviderClass)
			productProviderClass = arParams.productProviderClass;
		if (!!arParams.skuProps)
		{
			arSkuProps = (BX.type.isString(arParams.skuProps) ? eval('('+arParams.skuProps+')') : arParams.skuProps);
		}
		if (!!arParams.barcodeMulti)
			barcodeMulti = arParams.barcodeMulti;
		if (!!arParams.productType)
			productType = arParams.productType;
		if (!!arParams.setParentId)
			setParentId = arParams.setParentId;
		if (!!arParams.stores)
		{
			arStores = (BX.type.isString(arParams.stores) ? eval('('+arParams.stores+')') : arParams.stores);
		}

		var productProps = '<div id="PRODUCT_PROPS_USER_'+ ID + '">';
		var countProps = 1;
		var inputProps = "";

		if (bSetItem)
		{
			oRow.style.display = "none";
			oRow.setAttribute('class', 'set_item_' + setParentId);
		}

		if (!!arSkuProps && BX.type.isArray(arSkuProps))
		{
			for (var i = 0; i < arSkuProps.length; i++)
			{
				if (arSkuProps[i].NAME !== 'Product XML_ID' && arSkuProps[i].NAME !== 'Catalog XML_ID')
					productProps += arSkuProps[i].NAME+": "+arSkuProps[i].VALUE+"<br>";
				inputProps += '<input type="hidden" value="'+BX.util.htmlspecialchars(arSkuProps[i].NAME)+'" name="PRODUCT['+ID+'][PROPS]['+countProps+'][NAME]" id="PRODUCT_PROPS_NAME_'+ID+'_'+countProps+'" >';
				inputProps += '<input type="hidden" value="'+BX.util.htmlspecialchars(arSkuProps[i].VALUE)+'" name="PRODUCT['+ID+'][PROPS]['+countProps+'][VALUE]" id="PRODUCT_PROPS_VALUE_'+ID+'_'+countProps+'" >';
				inputProps += '<input type="hidden" value="'+BX.util.htmlspecialchars(arSkuProps[i].CODE)+'" name="PRODUCT['+ID+'][PROPS]['+countProps+'][CODE]" id="PRODUCT_PROPS_CODE_'+ID+'_'+countProps+'" >';
				inputProps += '<input type="hidden" value="'+countProps+'" name="PRODUCT['+ID+'][PROPS]['+countProps+'][SORT]" id="PRODUCT_PROPS_SORT_'+ID+'_'+countProps+'" >';
				countProps++;
			}
		}
		productProps += '</div>';
		arProductEditCountProps[ID] = countProps;
		oCellPROPS.innerHTML = productProps;

		// product name html
		var hiddenField = '<div id="product_name_' + ID + '">';

		var setItemLinkClass  = (bSetItem) ? 'set-item-link-name' : '';

		if (urlEdit.length > 0)
			hiddenField += '<a href="' + urlEdit + '" target="_blank" class="name-link ' + setItemLinkClass + '" >';

		hiddenField += BX.util.htmlspecialchars(name);

		if (urlEdit.length > 0)
			hiddenField += "</a>";

		if (bSetParent)
		{
			hiddenField += '<div class="set-link-block">';
			hiddenField += '<a class="dashed-link show-set-link" href="javascript:void(0);" id="set_toggle_link_' + setParentId + '" onclick="fToggleSetItems(\'' + setParentId + '\');"><?=GetMessage("SOE_SHOW_SET")?></a>';
			hiddenField += '</div>';
		}

		hiddenField = hiddenField + "</div>";

		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][NEW_PRODUCT]" id="PRODUCT[' + ID + '][NEW_PRODUCT]" value="NEW_PRODUCT" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][CALLBACK_FUNC]" id="CALLBACK_FUNC_' + ID + '" value="' + callback + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][ORDER_CALLBACK_FUNC]" id="ORDER_CALLBACK_FUNC_' + ID + '" value="' + orderCallback + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][CANCEL_CALLBACK_FUNC]" id="CANCEL_CALLBACK_FUNC_' + ID + '" value="' + cancelCallback + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][PAY_CALLBACK_FUNC]" id="PAY_CALLBACK_FUNC_' + ID + '" value="' + payCallback + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][PRODUCT_PROVIDER_CLASS]" id="PRODUCT_PROVIDER_CLASS_' + ID + '" value="' + productProviderClass + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][CURRENCY]" id="CURRENCY_' + ID + '" value="' + currency + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][DISCOUNT_PRICE]" id="PRODUCT[' + ID + '][DISCOUNT_PRICE]" value="' + priceDiscount + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][WEIGHT]" id="PRODUCT[' + ID + '][WEIGHT]" value="' + weight + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][VAT_RATE]" id="PRODUCT[' + ID + '][VAT_RATE]" value="' + vatRate + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][MODULE]" id="PRODUCT[' + ID + '][MODULE]" value="' + module + '" />';
		hiddenField += '<input type="hidden" name="BASKET_' +  ID + '" id="BASKET_' + ID + '" value="" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][NOTES]" id="PRODUCT[' + ID + '][NOTES]" value="' + priceType + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][CATALOG_XML_ID]" id="PRODUCT[' + ID + '][CATALOG_XML_ID]" value="' + catalogXmlID + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][PRODUCT_XML_ID]" id="PRODUCT[' + ID + '][PRODUCT_XML_ID]" value="' + productXmlID + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][DETAIL_PAGE_URL]" id="PRODUCT[' + ID + '][DETAIL_PAGE_URL]" value="' + url + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][NAME]" id="PRODUCT[' + ID + '][NAME]" value="' + BX.util.htmlspecialchars(name) + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][PRICE_DEFAULT]" id="PRODUCT[' + ID + '][PRICE_DEFAULT]" value="' + priceBase + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][PRODUCT_ID]" id="PRODUCT[' + ID + '][PRODUCT_ID]" value="' + product_id + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][BARCODE_MULTI]" id="PRODUCT[' + ID + '][BARCODE_MULTI]" value="' + barcodeMulti + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][CUSTOM_PRICE]" id="PRODUCT[' + ID + '][CUSTOM_PRICE]" value="N" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][TYPE]" id="PRODUCT[' + ID + '][TYPE]" value="' + productType + '" />';
		hiddenField += '<input type="hidden" name="PRODUCT[' + ID + '][SET_PARENT_ID]" id="PRODUCT[' + ID + '][SET_PARENT_ID]" value="' + setParentId + '" />';
		hiddenField += '<input type="hidden" name="edit_page_url_'+ID+'" id="edit_page_url_'+ID+'" value="' + urlEdit + '" />';
		hiddenField += '<span id="product_props_' + ID + '">'+inputProps+'</span>';

		var imgSrc = "&nbsp;";
		if (urlImg != "")
			imgSrc = '<img src="'+urlImg+'" alt="" border="0" />';
		else
			imgSrc = '<div class="no_foto"><?=GetMessageJS('NO_FOTO');?></div>';

		var actionHTML = '<div onclick="this.blur();BX.adminList.ShowMenu(this, ';
		actionHTML = actionHTML + "[{'ICON':'view','TEXT':'<?=GetMessage("SOE_JS_EDIT")?>','ACTION':'ShowProductEdit("+ID+");','DEFAULT':true},{'ICON':'delete','TEXT':'<?=GetMessage("SOE_JS_DEL_WITHOUT_DISCOUNT")?>','ACTION':'DeleteProduct(this, "+ID+", false);fEnableSub();'}, {'ICON':'delete','TEXT':'<?=GetMessage("SOE_JS_DEL_WITH_DISCOUNT")?>','ACTION':'DeleteProduct(this, "+ID+", true);fEnableSub();'}]);\" class=\"adm-list-table-popup\"></div>";

		if (!bSetItem)
			oCellAction.innerHTML = actionHTML;

		oCellPhoto.innerHTML = imgSrc;
		oCellName.innerHTML = hiddenField;

		var ratioHTML = '';
		var leftMargin = -3;
		if (!!arParams.ratio && arParams.ratio != 1)
		{
			var tipUp = '<?=GetMessage("NEWO_UP_RATIO")?>',
				tipDown = '<?=GetMessage("NEWO_DOWN_RATIO")?>';

			tipUp = tipUp.replace('#RATIO#', arParams.ratio);
			tipDown = tipDown.replace('#RATIO#', arParams.ratio);

			ratioHTML = '<div class="quantity_control">\
				<a href="javascript:void(0)" title="' + tipUp + '" class="plus" onclick="fChangeQuantityValue(' + ID + ', \'up\', ' + arParams.ratio + ');"></a>\
				<a href="javascript:void(0)" title="' + tipDown + '" class="minus" onclick="fChangeQuantityValue(' + ID + ', \'down\', ' + arParams.ratio + ');"></a>\
			</div>';

			leftMargin = 10;
		}

		var measureHTML = '';
		if (!!arParams.measureText && arParams.measureText.length > 0)
		{
			if (!bSetItem)
				measureHTML += '<div class="measure-wrap" style="left: ' + leftMargin + 'px">';

			measureHTML += '<span class="measure-text">' + arParams.measureText + '</span>';

			if (!bSetItem)
				measureHTML += '</div>';
		}

		var cellQuantityHTML = '';
		if (!bSetItem)
		{
			var oCellQuantityValue = '<input\
				type="text"\
				name="PRODUCT[' + ID + '][QUANTITY]"\
				id="PRODUCT[' + ID + '][QUANTITY]"\
				class="quantity-field"\
				value="' + quantity + '"\
				size="4"\
				maxlength="7"\
				onchange="fRecalProduct(' + ID + ', \'\', \'N\', \'N\', null);"\
				>' + ratioHTML;
		}
		else
		{
			var oCellQuantityValue = '<span id="set_item_quantity_' + ID + '">' + quantity + '</span>&nbsp;<span>' + measureHTML + '</span>\
				<input\
					type="hidden"\
					name="PRODUCT[' + ID + '][QUANTITY]"\
					id="PRODUCT[' + ID + '][QUANTITY]"\
					value="' + quantity + '"\
				>';
		}

		cellQuantityHTML = '<div class="quantity-block"><div class="quantity-block-wrap"><div class="quantity-wrap">' + oCellQuantityValue + '</div>';

		if (!bSetItem)
			cellQuantityHTML += measureHTML;

		cellQuantityHTML += '</div></div><span class="warning_balance" id="warning_balance_' + ID + '"></span>';

		oCellQuantity.innerHTML = cellQuantityHTML;

		var priceColumn = "";
		if (!valutaFormat) valutaFormat = '<?=CUtil::JSEscape($CURRENCY_FORMAT); ?>';

		priceColumn += '<div id="DIV_PRICE_'+ID+'" class="edit_price">';
		priceColumn += '<span class="default_price_product" id="default_price_'+ID+'">';
		priceColumn += '<span class="formated_price" id="formated_price_'+ID+'" onclick="fEditPrice('+ID+', \'on\');">' + priceFormated + '</span>';
		priceColumn += '</span>';
		priceColumn += '<span class="edit_price_product" id="edit_price_'+ID+'">';
		priceColumn += '<input maxlength="9" onblur="fEditPrice('+ID+', \'exit\');" onclick="fEditPrice('+ID+', \'on\');" onchange="fRecalcCustomDiscount('+ID+');" type="text" name="PRODUCT[' + ID + '][PRICE]" id="PRODUCT[' + ID + '][PRICE]" value="' + price + '" size="5" >';
		priceColumn += '</span>';
		priceColumn += '<span id="currency_price_product" class="currency_price">'+valutaFormat+'</span>';
		priceColumn += '<a href="javascript:void(0);" onclick="fEditPrice('+ID+', \'on\');"><span class="pencil"></span></a>';
		priceColumn += '</div>';
		priceColumn += '<div id="DIV_PRICE_OLD_'+ID+'" class="base_price" style="display:none;">' + priceBaseFormat + ' <span>'+valutaFormat+'</span></div>';

		priceColumn += '<div id="DIV_BASE_PRICE_WITH_DISCOUNT_'+ID+'" class="base_price">';

		if (discountPercent > 0)
			priceColumn += priceBaseFormat + '<span>'+valutaFormat+'</span>';

		priceColumn += '</div>';

		priceColumn += '<div id="DIV_DISCOUNT_'+ID+'" class="discount">';
		if (discountPercent > 0)
			priceColumn += '(<?=GetMessageJS('NEWO_PRICE_DISCOUNT')?> '+discountPercent+'%)';
		priceColumn += '</div>';
		priceColumn += '<div class="base_price_title" id="base_price_title_'+ID+'">'+priceType+'</div>';

		oCellPrice.innerHTML = priceColumn;

		if (bSetItem)
			oCellSumma.innerHTML = '';
		else
			oCellSumma.innerHTML = '<div>' + summaFormated + '<span>' + valutaFormat + '</span></div>';

		if (!balance) balance = 0;
		oCellBalance.innerHTML = '<div id="DIV_BALANCE_'+ID+'">' + balance + '</div>';

		if (arStores instanceof Array) //if store control is actually used and array of stores is supplied
		{
			if (arStores.length == 0)
			{
				var newStoreDivBlock = BX.create('div', {
						props: {
							id: 'store_select_block_'+ID
						},
						html: '<div class="store_product_no_stores"><?=GetMessageJS("NEWO_NO_PRODUCT_STORES")?></div>'
					});

				if (bSetParent)
					newStoreDivBlock.setAttribute('style', 'display:none');

				oCellStore.appendChild(newStoreDivBlock);
			}
			else
			{
				// store input fields
				var newStoreDivBlock = BX.create('div', {
						props: {
							'id': 'store_select_block_' + ID
						},
						children: [
							newStoreDiv = BX.create('div', {
								props: {
									'id': 'store_select_wrapper_' + ID,
									'className': 'store_row_element store_select_wrapper'
								},
								children: [
									newStoreDeleteDiv = BX.create('div', {
										props: {
											'id': 'store_select_delete_' + ID,
											'name': 'store_select_delete_' + ID,
											'className': 'store_row_element store_select_delete'
										}
									}),
									newStoreSelect = BX.create('select', {
										props: {
											'id': 'PRODUCT[' + ID + '][STORES][0][STORE_ID]',
											'name': 'PRODUCT[' + ID + '][STORES][0][STORE_ID]',
											'className': 'store_row_element'
										}
									}),
									newStoreAmountHidden = BX.create('input', {
										props: {
											'id': 'PRODUCT[' + ID + '][STORES][0][AMOUNT]',
											'name': 'PRODUCT[' + ID + '][STORES][0][AMOUNT]',
											'type': 'hidden',
											'value': arStores[0].AMOUNT
										}
									}),
									newStoreNameHidden = BX.create('input', {
										props: {
											'id': 'PRODUCT[' + ID + '][STORES][0][STORE_NAME]',
											'name': 'PRODUCT[' + ID + '][STORES][0][STORE_NAME]',
											'type': 'hidden',
											'value': arStores[0].STORE_NAME
										}
									})
								]
							}),
							addStoresLink = BX.create('a', {
								props: {
									'id': 'add_store_link_' + ID, //TODO - make hidden if arStore.length == 1
									'className': 'add_store',
									'href': 'javascript:void(0);'
								},
								html: '<span></span><?=GetMessageJS("SALE_F_ADD_STORE")?>'
							})
						]
					}),
					newAmountDiv = BX.create('div', {
						props: {
							'id' : 'store_amount_block_' + ID,
							'className': 'store_row_element'
						},
						children: [
							newAmountInput = BX.create('input', {
								props: {
									'id': 'PRODUCT[' + ID + '][STORES][0][QUANTITY]',
									'name': 'PRODUCT[' + ID + '][STORES][0][QUANTITY]',
									'type': 'text',
									'size': '4',
									'maxlength': '7'
								}
							}),
							newAmountSpan = BX.create('span', {
								props: {
									'id': 'store_max_amount_' + ID + '_0', //TODO
									'type': 'text',
									'size': '4',
									'maxlength': '7'
								},
								html: '&nbsp;/&nbsp;' + arStores[0].AMOUNT // TODO
							})
						]
					});

					if (barcodeMulti == "Y")
					{
						var barcodeButtonData = '<div align="center"><a onClick="enterBarcodes(' + ID + ', 0);" class="adm-btn adm-btn-barcode"><?=GetMessage("NEWO_STORE_ADD_BARCODES")?></a></div>';
						barcodeButtonData += '<div id="STORE_BARCODE_MULTI_DIV_' + ID + '_0" class="store_barcode_hidden_div">';
						barcodeButtonData += '<div style="display: block;" class="store_barcode_scroll_div" id="STORE_BARCODE_DIV_SCROLL_' + ID +  '_0">';
						barcodeButtonData += '<table id="STORE_BARCODE_TABLE_MULTI_' + ID + '_0"><tbody>';
						barcodeButtonData += '</tbody></table>';
						barcodeButtonData += '</div></div>';

						newBarcodeInputDiv = BX.create('div', {
							props: {
								'id' : 'store_barcode_wrapper_' + ID + '_0',
								'className' : 'store_row_element'
							},
							html: barcodeButtonData
						});

						if (BX('HAS_PRODUCTS_WITH_BARCODE_MULTI'))
							BX('HAS_PRODUCTS_WITH_BARCODE_MULTI').value = "Y";
					}
					else
					{
						newBarcodeInputDiv = BX.create('div', { //TODO
							props: {
								'id' : 'store_barcode_wrapper_' + ID + '_0'
							},
							children: [
								newBarcodeInput = BX.create('input', {
									props: {
										'id': 'PRODUCT[' + ID + '][STORES][0][BARCODE]',
										'name': 'PRODUCT[' + ID + '][STORES][0][BARCODE]',
										'type': 'text',
										'className': 'store_row_element'
									}
								}),
							]
						});
					}

				//adding select values
				for (var i = 0; i < arStores.length; i++) {
					newStoreSelect.options[newStoreSelect.options.length] = new Option(
						arStores[i].STORE_NAME + ' [' + arStores[i].STORE_ID + ']',
						arStores[i].STORE_ID
					);
				};

				oCellStore.appendChild(newStoreDivBlock);
				oCellStoreQuantity.appendChild(newAmountDiv);
				oCellBarcode.appendChild(newBarcodeInputDiv);

				BX.bind(BX('add_store_link_' + ID), 'click', function() {
						return fAddStore(ID, arStores, arStores.length, (barcodeMulti == "Y"));
					}
				);

				//store selector change
				BX.bind(BX('PRODUCT[' + ID + '][STORES][0][STORE_ID]'), 'change', function() {
						return fChangeStoreSelector(this, ID, 0, arStores);
					}
				);

				// barcode check
				BX.bind(BX('PRODUCT[' + ID + '][STORES][0][BARCODE]'), 'change', function() {
						return fCheckBarcode(ID, 0, false);
					}
				);
			}
		}

		//array product in basket
		arProduct[ID] = product_id;

		//insert number field in the table
		fUpdateProductCount();
		if (BX("ids"))
		{
			if (BX("ids").value.length > 0)
				BX("ids").value = BX("ids").value + ',' + product_id;
			else
				BX("ids").value = product_id;
		}

		fRecalProduct(BX("PRODUCT[" + ID + "][QUANTITY]"), ID, 'Y', 'N', null);
	}

	function getParamsByProductId(arParams, iblockID)
	{
		var productId = arParams['id'],
			productQuantity = arParams['quantity'],
			strUserColumns = BX("userColumns").value,
			userId = document.order_edit_info_form.user_id.value,
			dateURL = '<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&get_product_params=Y&ID=<?=$ID?>&LID=<?=CUtil::JSEscape($LID)?>&productId=' + productId + '&quantity=' + productQuantity + '&userId=' + userId + '&userColumns=' + strUserColumns;

		BX.showWait();

		BX.ajax.post(
			'/bitrix/admin/sale_order_new.php',
			dateURL,
			getParamsByProductIdResult
		);
	}

	function getParamsByProductIdResult(result)
	{
		BX.closeWait();

		if (BX.type.isString(result))
			var res = eval('('+result+')');
		else
			var res = result;

		FillProductFields(res['params']['id'], res['params'], 0);

		if (res['params']['setItems'].length > 0) // if set items exist
		{
			for (var i = 0; i < res['params']['setItems'].length; i++)
			{
				FillProductFields(res['params']['setItems'][i]['id'], res['params']['setItems'][i], 0, 'Y');
			}
		}
	}

	function DeleteProduct(el, id, type)
	{
		if (confirm('<?=GetMessage('SALE_CONFIRM_DELETE')?>'))
		{
			type = !!type;
			if (type)
			{
				var obCartFix = BX('CART_FIX');
				if (!!obCartFix)
				{
					obCartFix.value = 'N';
				}
			}

			var setItems = [];

			if (BX('PRODUCT[' + id + '][TYPE]').value == 1) // if parent of the set
			{
				for(var i in arProduct)
				{
					if ((BX('PRODUCT[' + i + '][TYPE]').value.length == 0 || BX('PRODUCT[' + i + '][TYPE]').value == 0)
						&& BX('PRODUCT[' + i + '][SET_PARENT_ID]').value == BX('PRODUCT[' + id + '][SET_PARENT_ID]').value)
						setItems.push(i);
				}
			}

			var trDel = document.getElementById("BASKET_TABLE_ROW_" + id).sectionRowIndex;
			var oTbl = document.getElementById("BASKET_TABLE");
			oTbl.deleteRow(trDel);
			delete arProduct[id];

			for (var i = 0; i < setItems.length; i++)
			{
				var trDel = document.getElementById("BASKET_TABLE_ROW_" + setItems[i]).sectionRowIndex;
				var oTbl = document.getElementById("BASKET_TABLE");
				oTbl.deleteRow(trDel);
				delete arProduct[setItems[i]];
			}

			fUpdateProductCount();
			fRecalProduct('', '', 'Y', 'N', null);

			fGetMoreBasket('');
			fGetMoreViewed('');
		}

		return false;
	}

	function enterBarcodes(basketItemId, storeId)
	{
		var formBarcodes,
			uniqId = basketItemId + '_' + storeId,
			oldQuantity = parseInt(BX("PRODUCT[" + basketItemId + "][STORES][" + storeId + "][QUANTITY]").defaultValue),
			newQuantity = parseInt(BX("PRODUCT[" + basketItemId + "][STORES][" + storeId + "][QUANTITY]").value);

		if (isNaN(oldQuantity))
			oldQuantity = 0;

		if (isNaN(newQuantity))
			newQuantity = 0;

		//current number of saved barcode inputs
		var tableMulti = BX("STORE_BARCODE_TABLE_MULTI_" + uniqId),
			rows = tableMulti.getElementsByTagName('tr'),
			barcodeFieldsNumber = rows.length;

		if (barcodeFieldsNumber < newQuantity) // add barcode rows
		{
			var barcodesToAdd = newQuantity - barcodeFieldsNumber,
				addedCount = 0,
				f = 0;

			while (barcodesToAdd != addedCount)
			{
				if (!BX("PRODUCT[" + basketItemId + "][STORES][" + storeId + "][BARCODE][" + f + "]"))
				{
					addBarcodeRow(basketItemId, storeId, f);
					addedCount++;
				}
				else
				{
					f++;
				}
			}
		}
		else if (barcodeFieldsNumber > newQuantity) // delete barcode rows
		{
			var barcodesToDelete = barcodeFieldsNumber - newQuantity,
				k = 0,
				curBarcode,
				arRowsToDelete = [];

			while (row = tableMulti.rows[k++])
			{
				curBarcode = BX.findChild(row, {'tag':'input', 'type': 'text'}, true);
				if (curBarcode.value.length == 0)
					arRowsToDelete.push(row);

				if (arRowsToDelete.length == barcodesToDelete)
					break;
			}

			for (var i = 0; i < arRowsToDelete.length; i++) // actually deleting
			{
				if (BX(arRowsToDelete[i]))
				{
					var trDel = BX(arRowsToDelete[i]).sectionRowIndex;
					tableMulti.deleteRow(trDel);
				}

			};
		}

		formBarcodes = BX.PopupWindowManager.create("sale-popup-barcodes-" + uniqId, BX("product_name_" + uniqId), {
			offsetTop : 0,
			offsetLeft : 0,
			autoHide : false,
			closeByEsc : true,
			closeIcon : false,
			draggable: {restrict:true},
			titleBar: {content: BX.create("span", {html: '<?=GetMessageJS('NEWO_STORE_FORM_ADD_BARCODES')?>', 'props': {'className': 'store-doc-title'}})},
			content : BX("STORE_BARCODE_DIV_SCROLL_" + uniqId),
			events : {
				onPopupFirstShow : BX.proxy(
					function(popupWindow)
					{
						if (BX("STORE_BARCODE_TABLE_MULTI_" + uniqId))
						{
							//TODO
							var multiTable = BX("STORE_BARCODE_TABLE_MULTI_" + uniqId);
							// var rows = multiTable.getElementsByTagName('tr');

							// for (var i = 0, row; row = multiTable.rows[i]; i++)
							// {
							// 	for (var j = 0, col; col = row.cells[j]; j++)
							// 	{
							// 		// var barcodeValues = BX.findChildren(BX(col.id), {'tag':'input', 'type': 'text'}, false);
							// 		// var barcodeFoundValues = BX.findChildren(BX(col.id), {'tag':'input', 'type': 'hidden'}, false);

							// 	}
							// }
						}
					},
					this
				)
			}
		});
		formBarcodes.setButtons([
			new BX.PopupWindowButton({
				text : "<?=GetMessage('SOE_APPLY')?>",
				className : "",
				events : {
					click : function()
					{
						BX('STORE_BARCODE_MULTI_DIV_' + uniqId).appendChild(BX("STORE_BARCODE_DIV_SCROLL_" + uniqId));
						formBarcodes.close();
					}
				}
			}),
			new BX.PopupWindowButton({
				text : "<?=GetMessage('SALE_CANCEL')?>",
				className : "",
				events : {
					click : function()
					{
						BX('STORE_BARCODE_MULTI_DIV_' + uniqId).appendChild(BX("STORE_BARCODE_DIV_SCROLL_" + uniqId));
						formBarcodes.close();
					}
				}
			})
		]);

		if (BX("sale-popup-barcodes-" + uniqId).getElementsByClassName("popup-window-content")[0].children.length <= 0)
			BX("sale-popup-barcodes-" + uniqId).getElementsByClassName("popup-window-content")[0].appendChild(BX("STORE_BARCODE_DIV_SCROLL_" + uniqId));

		formBarcodes.show();
		if (BX('PRODUCT[" + basketItemId + "][STORES][" + storeId + "][BARCODE][0]'))
			BX('PRODUCT[" + basketItemId + "][STORES][" + storeId + "][BARCODE][0]').focus();
	}

	function addBarcodeRow(basketItemId, storeId, barcodeId)
	{
		tableMulti = BX("STORE_BARCODE_TABLE_MULTI_" + basketItemId + "_" + storeId),
		oRow = tableMulti.insertRow(barcodeId);
		oRow.setAttribute('id', "STORE_BARCODE_" + basketItemId + "_" + storeId + "_" + barcodeId);
		oCell = oRow.insertCell(-1);
		oCell.innerHTML = '<input maxlength="40" type="text" size="13" name="PRODUCT['+basketItemId+'][STORES]['+storeId+'][BARCODE]['+barcodeId+']" id="PRODUCT['+basketItemId+'][STORES]['+storeId+'][BARCODE]['+ barcodeId+']">';

		oCellDel = oRow.insertCell(-1);
		oCellDel.innerHTML = '<a class="split-delete-item" tabIndex="-1" href="javascript:void(0);" onclick="deleteBarcodeValue('+basketItemId+', ' + storeId + ', ' + barcodeId + ');" title="<?=GetMessageJS('NEWO_STORE_DELETE_BARCODE')?>"></a>';

		oCellHidden = oRow.insertCell(-1);
		oCellHidden.innerHTML = "<input type=\"hidden\" value=\"N\" name=\"PRODUCT[" + basketItemId + "][STORES][" + storeId + "][BARCODE_FOUND][" + barcodeId + "]\" id=\"PRODUCT[" + basketItemId + "][STORES][" + storeId + "][BARCODE_FOUND][" + barcodeId + "]\">";

		// barcode check
		BX.bind(BX("PRODUCT[" + basketItemId + "][STORES][" + storeId + "][BARCODE][" + barcodeId + "]"), 'change', function() {
				return fCheckBarcode(basketItemId, storeId, true, barcodeId);
			}
		);
	}

	function deleteBarcodeValue(basketItemId, storeId, barcodeId)
	{
		BX("PRODUCT[" + basketItemId + "][STORES][" + storeId + "][BARCODE][" + barcodeId + "]").value = '';
		fCheckBarcode(basketItemId, storeId, true, barcodeId);
	}

	function fRecalByCoupon()
	{
		var obCartFix = BX('CART_FIX'),
			obCoupon = BX('COUPON'),
			recalcAllowed = true;
		if (!!obCoupon && obCoupon.value.length > 0)
		{
			if (!!obCartFix && obCartFix.value == 'Y')
			{
				if (!BX('PAYED') || !BX('PAYED').checked)
				{
					obCartFix.value = 'N';
				}
				else
				{
					recalcAllowed = false;
					alert('<?=CUtil::JSEscape(GetMessage('COUPONS_RECALC_BLOCKED')); ?>');
					obCoupon.value = '';
				}
			}
			if (recalcAllowed)
				fRecalProduct('', '', 'N', 'Y', { 'coupon' : obCoupon.value });
		}
	}

	function deleteCoupon(e)
	{
		var target = BX.proxy_context,
			value,
			obCartFix = BX('CART_FIX');
		if (!obCartFix || obCartFix.value == 'N')
		{
			if (!!target && target.hasAttribute('data-coupon'))
			{
				value = target.getAttribute('data-coupon');
				if (!!value && value.length > 0)
				{
					fRecalProduct('', '', 'N', 'Y', { 'deleteCoupon' : value });
				}
			}
		}
	}

	/**
	 * @param couponBlock
	 * @param {COUPON: string, JS_STATUS: string} oneCoupon - new coupon.
	 */
	function couponCreate(couponBlock, oneCoupon)
	{
		var couponClass = 'disabled';

		if (!BX.type.isElementNode(couponBlock))
			return;
		if (oneCoupon.JS_STATUS === 'BAD')
			couponClass = 'bad';
		else if (oneCoupon.JS_STATUS === 'APPLYED')
			couponClass = 'good';

		couponBlock.appendChild(BX.create(
			'div',
			{
				props: {
					className: 'bx_ordercart_coupon'
				},
				children: [
					BX.create(
						'input',
						{
							props: {
								className: couponClass,
								type: 'text',
								value: oneCoupon.COUPON,
								name: 'OLD_COUPON[]'
							},
							attrs: {
								disabled: true,
								readonly: true
							}
						}
					),
					BX.create(
						'span',
						{
							props: {
								className: couponClass
							},
							attrs: {
								'data-coupon': oneCoupon.COUPON
							}
						}
					),
					BX.create(
						'div',
						{
							props: {
								className: 'bx_ordercart_coupon_notes'
							},
							html: oneCoupon.JS_CHECK_CODE
						}
					)
				]
			}
		));
	}

	function couponListUpdate(res)
	{
		var couponBlock,
			couponClass,
			fieldCoupon,
			couponsCollection,
			couponFound,
			i,
			j,
			key;

		if (!!res && typeof res !== 'object')
		{
			return;
		}
		couponBlock = BX('coupons_block');
		if (!!couponBlock)
		{
			if (!!res.COUPON_LIST && BX.type.isArray(res.COUPON_LIST))
			{
				fieldCoupon = BX('COUPON');
				if (!!fieldCoupon)
				{
					fieldCoupon.value = '';
				}
				couponsCollection = BX.findChildren(couponBlock, { tagName: 'input', property: { name: 'OLD_COUPON[]' } }, true);
				if (!!couponsCollection)
				{
					if (BX.type.isElementNode(couponsCollection))
					{
						couponsCollection = [couponsCollection];
					}
					for (i = 0; i < res.COUPON_LIST.length; i++)
					{
						couponFound = false;
						key = -1;
						for (j = 0; j < couponsCollection.length; j++)
						{
							if (couponsCollection[j].value === res.COUPON_LIST[i].COUPON)
							{
								couponFound = true;
								key = j;
								couponsCollection[j].couponUpdate = true;
								break;
							}
						}
						if (couponFound)
						{
							couponClass = 'disabled';
							if (res.COUPON_LIST[i].JS_STATUS === 'BAD')
								couponClass = 'bad';
							else if (res.COUPON_LIST[i].JS_STATUS === 'APPLYED')
								couponClass = 'good';

							BX.adjust(couponsCollection[key], {props: {className: couponClass}});
							BX.adjust(couponsCollection[key].nextSibling, {props: {className: couponClass}});
							BX.adjust(couponsCollection[key].nextSibling.nextSibling, {html: res.COUPON_LIST[i].JS_CHECK_CODE});
						}
						else
						{
							couponCreate(couponBlock, res.COUPON_LIST[i]);
						}
					}
					for (j = 0; j < couponsCollection.length; j++)
					{
						if (typeof (couponsCollection[j].couponUpdate) === 'undefined' || !couponsCollection[j].couponUpdate)
						{
							BX.remove(couponsCollection[j].parentNode);
							couponsCollection[j] = null;
						}
						else
						{
							couponsCollection[j].couponUpdate = null;
						}
					}
				}
				else
				{
					for (i = 0; i < res.COUPON_LIST.length; i++)
					{
						couponCreate(couponBlock, res.COUPON_LIST[i]);
					}
				}
			}
		}
		couponBlock = null;
	}

	function fRecalProduct(id, type, recommendet, recalcAll, fields)
	{
		var location = '',
			locationZip = '',
			paySystemId = '',
			deliveryId = '',
			buyerTypeId = '',
			coupon = '',
			user_id = 0,
			setItemQuantity = 0,
			productData,
			j = 0,
			i,
			prevQuantity,
			updatedQuantity,
			recalcCallback,
			recalcOrder,
			input,
			selectedIndex,
			selectedOption,
			discount,
			deliveryPrice,
			dateURL,
			cartFix;

		if (BX('user_id'))
			user_id = BX('user_id').value;

		productData = "{";

		if (type != "" && type == "price")
			BX('CALLBACK_FUNC_' + id).value = "Y";

		if (BX('PRODUCT[' + id + '][QUANTITY]')) // will be used if arProduct is not empty
		{
			prevQuantity = parseFloat(BX('PRODUCT[' + id + '][QUANTITY]').defaultValue);
			updatedQuantity  = parseFloat(BX('PRODUCT[' + id + '][QUANTITY]').value);
			BX('PRODUCT[' + id + '][QUANTITY]').defaultValue = BX('PRODUCT[' + id + '][QUANTITY]').value;
		}

		recalcCallback = "";
		recalcOrder = "N";
		for (i in arProduct)
		{
			if (j > 0)
				productData = productData + ",";

			discount = '';
			if (BX('PRODUCT[' + i + '][DISCOUNT_PRICE]'))
				discount = BX('PRODUCT[' + i + '][DISCOUNT_PRICE]').value;

			var taxOrder = '<?=$str_TAX_VALUE?>';

			var pr = BX('PRODUCT[' + i + '][PRICE]').value.replace(',', '.');
			pr = parseFloat(pr);
//			prOld = parseFloat(BX('PRODUCT[' + i + '][PRICE_DEFAULT]').value);

			if (isNaN(pr) || pr <= 0)
				BX('PRODUCT[' + i + '][PRICE]').value = BX('PRODUCT[' + i + '][PRICE_DEFAULT]').value;


			if (BX('BASKET_' + i) && BX('BASKET_' + i).value.length <= 0 || recalcAll == "Y")
			{
				recalcCallback = BX('CALLBACK_FUNC_' + i).value;
				recalcOrder = "Y";
				BX('RECALC_ORDER').value = recalcOrder;
			}

			if (BX('CALLBACK_FUNC_' + i).value == "Y")
			{
				recalcCallback = 'Y';
				BX('PRODUCT[' + i + '][CUSTOM_PRICE]').value = "Y";
			}

			if (BX('PRODUCT[' + id + '][QUANTITY]') // if quantity of the parent set item is changed, change quantity of all set items
				&& (BX('PRODUCT[' + id + '][SET_PARENT_ID]').value.length > 0 && BX('PRODUCT[' + id + '][SET_PARENT_ID]').value != 0)
				&& BX('PRODUCT[' + i + '][SET_PARENT_ID]').value == BX('PRODUCT[' + id + '][SET_PARENT_ID]').value
				&& (BX('PRODUCT[' + i + '][TYPE]').value.length == 0)
				&& prevQuantity != updatedQuantity
				)
			{
				setItemQuantity = updatedQuantity * (parseFloat(BX('PRODUCT[' + i + '][QUANTITY]').value) / prevQuantity);

				BX('PRODUCT[' + i + '][QUANTITY]').value = setItemQuantity;
				BX('set_item_quantity_' + i).innerHTML = setItemQuantity;
			}

			if (BX('PRODUCT[' + i + '][QUANTITY]'))
				BX('PRODUCT[' + i + '][QUANTITY]').setAttribute('readonly', 'readonly');

			productData = productData + "'" + i + "':{";

			if (BX('BASKET_' + i))
				productData = productData + "'BASKET_ID':'" + BX('BASKET_' + i).value + "',";

			productData = productData + "'CALLBACK_FUNC':'" + recalcCallback + "',\n\
				'ORDER_CALLBACK_FUNC':'" + BX('ORDER_CALLBACK_FUNC_' + i).value + "',\n\
				'CANCEL_CALLBACK_FUNC':'" + BX('CANCEL_CALLBACK_FUNC_' + i).value + "',\n\
				'PAY_CALLBACK_FUNC':'" + BX('PAY_CALLBACK_FUNC_' + i).value + "',\n\
				'PRODUCT_PROVIDER_CLASS':'" + BX('PRODUCT_PROVIDER_CLASS_' + i).value + "',\n\
				'QUANTITY':'" + BX('PRODUCT[' + i + '][QUANTITY]').value + "',\n\
				'PRODUCT_ID':'" + BX('PRODUCT[' + i + '][PRODUCT_ID]').value + "',\n\
				'CURRENCY':'" + BX('CURRENCY_' + i).value + "',\n\
				'PRICE':'" + BX('PRODUCT[' + i + '][PRICE]').value + "',\n\
				'PRICE_DEFAULT':'" + BX('PRODUCT[' + i + '][PRICE_DEFAULT]').value + "',\n\
				'WEIGHT':'" + BX('PRODUCT[' + i + '][WEIGHT]').value + "',\n\
				'MODULE':'" + BX('PRODUCT[' + i + '][MODULE]').value + "',\n\
				'VAT_RATE':'" + BX('PRODUCT[' + i + '][VAT_RATE]').value + "',\n\
				'TAX_VALUE':'" + taxOrder + "',\n\
				'TYPE':'" + BX('PRODUCT[' + i + '][TYPE]').value + "',\n\
				'SET_PARENT_ID':'" + BX('PRODUCT[' + i + '][SET_PARENT_ID]').value + "',\n\
				'DISCOUNT_PRICE':'" + discount + "','CUSTOM_PRICE':'" + BX('PRODUCT[' + i + '][CUSTOM_PRICE]').value + "'}";
			j++;
		}
		productData = productData + "}";

		<?if(CSaleLocation::isLocationProEnabled()):?>

			input = document.querySelector('input[name="CITY_ORDER_PROP_' + locationID+'"]');

			if(!BX.type.isDomNode(input))
				input = document.querySelector('input[name="ORDER_PROP_' + locationID+'"]');

			if(BX.type.isDomNode(input))
				location = input.value;

		<?else:?>

			if (BX('CITY_ORDER_PROP_' + locationID))
			{
				selectedIndex = BX('CITY_ORDER_PROP_' + locationID).selectedIndex;
				selectedOption = BX('CITY_ORDER_PROP_' + locationID).options;
			}
			else if (BX('ORDER_PROP_' + locationID))
			{
				selectedIndex = BX('ORDER_PROP_' + locationID).selectedIndex;
				selectedOption = BX('ORDER_PROP_' + locationID).options;
			}

			if (locationID > 0 && selectedIndex > 0)
				location = selectedOption[selectedIndex].value;

		<?endif?>

		if (BX('ORDER_PROP_' + locationZipID))
			locationZip = BX('ORDER_PROP_' + locationZipID).value;

		deliveryId = document.getElementById('DELIVERY_ID').value;
		deliveryPrice = parseFloat(document.getElementById('DELIVERY_ID_PRICE').value);
		if (isNaN(deliveryPrice))
			deliveryPrice = 0;

		paySystemId = document.getElementById('PAY_SYSTEM_ID').value;
		buyerTypeId = document.getElementById('buyer_type_id').value;
		//coupon = document.getElementById('COUPON').value;

		var deliveryPriceChange = document.getElementById("change_delivery_price").value;
		var recomMore = document.getElementById('recom_more').value;

		cartFix = BX('CART_FIX').value;

		dateURL = '<?=bitrix_sessid_get()?>&ORDER_AJAX=Y'
			+ '&id=<?=$ID?>'
			+ '&LID=<?=CUtil::JSEscape($LID)?>'
			+ '&recalcOrder=' + recalcOrder
			+ '&cartFix=' + cartFix
			+ '&recomMore=' + recomMore
			+ '&recommendet=' + recommendet
			+ '&delpricechange=' + deliveryPriceChange
			+ '&user_id=' + user_id
			+ '&coupon=' + coupon
			+ '&currency=' + currencyBase
			+ '&buyerTypeId=' + buyerTypeId
			+ '&deliveryId=' + deliveryId
			+ '&deliveryPrice=' + deliveryPrice
			+ '&paySystemId=' + paySystemId
			+ '&location=' + location
			+ '&locationID=' + locationID
			+ '&locationZipID=' + locationZipID
			+ '&locationZip=' + locationZip
			+ '&product=' + productData;

		if (!!fields && typeof fields === 'object')
		{
			for (i in fields)
			{
				if (fields.hasOwnProperty(i))
				{
					dateURL += ('&' + i + '=' + fields[i]);
				}
			}
		}

		BX.showWait();
		BX.ajax.post('/bitrix/admin/sale_order_new.php', dateURL, fRecalProductResult);
	}

	function fRecalProductResult(result)
	{
		var i,
			res,
			changePriceProduct;

		BX.closeWait();
		if (result.length > 0)
		{
			res = eval( '('+result+')' );

			changePriceProduct = "N";
			for (i in res)
			{
				if (i > 0)
				{
					BX('PRODUCT[' + i + '][PRICE]').value = res[i]["PRICE"];

					BX('formated_price_' + i).innerHTML = res[i]["PRICE_DISPLAY"];

					//change price name
					if (BX('PRODUCT[' + i + '][CUSTOM_PRICE]').value != "Y")
					{
						if (res[i]["NOTES"].length > 0)
							BX('base_price_title_' + i).innerHTML = res[i]["NOTES"];
					}
					else
					{
						BX('base_price_title_' + i).innerHTML = '<?=GetMessage('NEWO_BASE_CATALOG_PRICE')?>';
					}

					if (res[i]["DISCOUNT_REPCENT"] != 0)
					{
						if (BX('PRODUCT[' + i + '][CUSTOM_PRICE]').value != "Y")
						{
							BX('DIV_DISCOUNT_' + i).innerHTML = '(<?=GetMessage('NEWO_PRICE_DISCOUNT')?> '+res[i]["DISCOUNT_REPCENT"]+'%)';
							BX('DIV_BASE_PRICE_WITH_DISCOUNT_' + i).innerHTML = res[i]["PRICE_BASE"]+" <span>"+res[0]["CURRENCY_FORMAT"]+"</span>";
							BX.show(BX('DIV_BASE_PRICE_WITH_DISCOUNT_'+i));
						}
						else
						{
							if (BX('DIV_DISCOUNT_' + i))
								BX('DIV_DISCOUNT_' + i).innerHTML = '';
						}
					}
					else
					{
						prOld = parseFloat(BX('PRODUCT[' + i + '][PRICE_DEFAULT]').value);

						if (res[i]["PRICE"] != prOld)
						{
							changePriceProduct = "Y";
						}

						if(BX('DIV_BASE_PRICE_WITH_DISCOUNT_'+i))
						{
							BX('DIV_BASE_PRICE_WITH_DISCOUNT_' + i).innerHTML = '';
							BX.hide(BX('DIV_BASE_PRICE_WITH_DISCOUNT_'+i));
						}

						if (BX('DIV_DISCOUNT_' + i))
							BX('DIV_DISCOUNT_' + i).innerHTML = '';
					}

					BX('PRODUCT[' + i + '][PRICE_DEFAULT]').value = res[i]["PRICE"];

					BX('DIV_SUMMA_' + i).innerHTML = "<div>" + res[i]["SUMMA_DISPLAY"] + " <span>"+res[0]["CURRENCY_FORMAT"]+"</span></div>";

					BX('PRODUCT[' + i + '][QUANTITY]').value = res[i]["QUANTITY"];

					BX('warning_balance_' + i).innerHTML = '';
					if (res[i]["WARNING_BALANCE"] && res[i]["WARNING_BALANCE"] == "Y")
					{
						BX('warning_balance_' + i).innerHTML = '<?=GetMessage("NEWO_WARNING_BALANCE")?>';
					}


					BX('DIV_BALANCE_' + i).value = res[i]["BALANCE"];
					BX('currency_price_product').innerHTML = res[0]["CURRENCY_FORMAT"];
					BX('PRODUCT[' + i + '][DISCOUNT_PRICE]').value = res[i]["DISCOUNT_PRICE"];
					BX('CURRENCY_' + i).value = res[i]["CURRENCY"];
				}
			}

			BX('DELIVER_ID_DESC').innerHTML = res[0]["DELIVERY_DESCRIPTION"];
			BX('DELIVERY_ID_PRICE').value = res[0]["DELIVERY_PRICE"];

			// delivery price
			if (typeof(res[0]["PRICE_DELIVERY_DIFF"]) !== 'undefined')
			{
				BX('DELIVERY_PRICE_DIFF').innerHTML = res[0]["PRICE_DELIVERY_DIFF"];
				BX('PRICE_DELIVERY_DIFF').value = res[0]["PRICE_DELIVERY_DIFF"];
				BX('DELIVERY_PRICE_DIFF_BLOCK').style.display = 'table-row';
			}
			else
				BX('DELIVERY_PRICE_DIFF_BLOCK').style.display = 'none';

			if (res[0]["DELIVERY"].length > 0)
				BX('DELIVERY_SELECT').innerHTML = res[0]["DELIVERY"];

			// payment system price
			if (typeof(res[0]["PAY_SYSTEM_PRICE"]) !== 'undefined')
			{
				BX('PAY_SYSTEM_PRICE_VAL').innerHTML = res[0]["PAY_SYSTEM_PRICE"];
				BX('PAY_SYSTEM_PRICE').value = res[0]["PAY_SYSTEM_PRICE"];
				BX('PAY_SYSTEM_PRICE_BLOCK').style.display = 'table-row';
			}
			else
				BX('PAY_SYSTEM_PRICE_BLOCK').style.display = 'none';

			if (res[0]["ORDER_ERROR"] == "N")
			{
				if (BX('town_location_'+res[0]["LOCATION_TOWN_ID"]))
				{
					if (res[0]["LOCATION_TOWN_ENABLE"] == 'Y')
						BX('town_location_'+res[0]["LOCATION_TOWN_ID"]).style.display = 'table-row';
					else
						BX('town_location_'+res[0]["LOCATION_TOWN_ID"]).style.display = 'none';
				}

				BX('ORDER_TOTAL_PRICE').innerHTML = res[0]["PRICE_TOTAL"];

				if (res[0]["DISCOUNT_PRODUCT_VALUE"] > 0)
				{
					BX('ORDER_PRICE_WITH_DISCOUNT_DESC_VISIBLE').style.display = 'table-row';
					BX('ORDER_PRICE_WITH_DISCOUNT').innerHTML = res[0]["PRICE_WITH_DISCOUNT_FORMAT"];
				}
				else
				{
					if (changePriceProduct == 'N')
						BX('ORDER_PRICE_WITH_DISCOUNT_DESC_VISIBLE').style.display = 'none';
					else
					{
						BX('ORDER_PRICE_WITH_DISCOUNT_DESC_VISIBLE').style.display = 'table-row';
						BX('ORDER_PRICE_WITH_DISCOUNT').innerHTML = res[0]["PRICE_WITH_DISCOUNT_FORMAT"];
					}
				}

				if (parseInt(res[0]["ORDER_ID"]) > 0)
				{
					if (parseFloat(res[0]["PAY_ACCOUNT_DEFAULT"]) >= parseFloat(res[0]["PRICE_TO_PAY_DEFAULT"]))
					{
						BX('PAY_CURRENT_ACCOUNT_DESC').innerHTML = res[0]["PAY_ACCOUNT"];
						BX('buyerCanBuy').style.display = 'block';
					}
					else
					{
						if (BX('buyerCanBuy'))
							BX('buyerCanBuy').style.display = 'none';
					}
				}

				BX('ORDER_DELIVERY_PRICE').innerHTML = res[0]["DELIVERY_PRICE_FORMAT"];
				BX('ORDER_TAX_PRICE').innerHTML = res[0]["PRICE_TAX"];
				BX('ORDER_WAIGHT').innerHTML = res[0]["PRICE_WEIGHT_FORMAT"];
				BX('ORDER_PRICE_ALL').innerHTML = res[0]["PRICE_TO_PAY"];
				BX('ORDER_DISCOUNT_PRICE_VALUE_VALUE').innerHTML = res[0]["DISCOUNT_VALUE_FORMATED"];

				if (parseFloat(res[0]["DISCOUNT_VALUE"]) > 0)
					BX('ORDER_DISCOUNT_PRICE_VALUE').style.display = "table-row";

				couponListUpdate(res[0]);

				if (res[0]["RECOMMENDET_CALC"] == "Y")
				{
					if (res[0]["RECOMMENDET_PRODUCT"].length == 0)
					{
						BX('tab_1').style.display = "none";
						BX('user_recomendet').style.display = "none";

						if (BX('user_basket').style.display == "block")
							fTabsSelect('user_basket', 'tab_2');
						else if (BX('buyer_viewed').style.display == "block")
							fTabsSelect('buyer_viewed', 'tab_3');
						else if (BX('tab_2').style.display == "block")
							fTabsSelect('user_basket', 'tab_2');
						else if (BX('tab_3').style.display == "block")
							fTabsSelect('buyer_viewed', 'tab_3');
					}
					else
					{
						BX('user_recomendet').innerHTML = res[0]["RECOMMENDET_PRODUCT"];
						if (BX('user_basket').style.display != "block" && BX('buyer_viewed').style.display != "block")
							fTabsSelect('user_recomendet', 'tab_1');
						else
							BX('tab_1').style.display = "block";
					}
				}

				orderWeight = res[0]["PRICE_WEIGHT"];
				orderPrice = res[0]["PRICE_WITH_DISCOUNT"];

				fGetMoreBasket('');
				fGetMoreViewed('');
			}
		}
		if (!!arProduct)
		{
			for (i in arProduct)
			{
				if (!!BX('PRODUCT[' + i + '][QUANTITY]'))
				{
					BX('PRODUCT[' + i + '][QUANTITY]').removeAttribute('readonly');
				}
			}
		}
	}

	function fGetRelatedOrderProps()
	{
		var deliveryId = BX('DELIVERY_ID').value,
			paymentId = BX('PAY_SYSTEM_ID').value,
			userId = BX('user_id').value;

		BX.ajax({
			url: '/bitrix/admin/sale_order_new.php',
			method: 'POST',
			data : '<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&get_props=Y&id=<?=$ID?>&userId=' + userId + '&delivery_id=' + deliveryId + '&paysystem_id=' + paymentId + '&CURRENCY=<?=$str_CURRENCY?>&LID=<?=CUtil::JSEscape($LID)?>',
			dataType: 'html',
			timeout: 30,
			async: true,
			processData: true,
			scriptsRunFirst: false,
			emulateOnload: true,
			start: true,
			cache: false,
			onsuccess: function(res) {
				BX("related_props_content").innerHTML = res;
			}
		});
		BX.closeWait();
	}

	function fUpdateProductCount()
	{
		if (BX("BASKET_TABLE"))
		{
			var basketTable = BX("BASKET_TABLE"),
				productIndex = 1,
				productCount,
				itemId,
				productCountMessage = '<?=GetMessage("SOE_TOTAL_NUMBER")?>';

			for (var i = 2, row; row = basketTable.rows[i]; i++)
			{
				itemId = row.id.replace("BASKET_TABLE_ROW_", "");

				for (var p = 0, col; col = row.cells[p]; p++)
				{
					if (col.className == 'COLUMN_NUMBER')
					{
						// if is set item
						if (BX('PRODUCT[' + itemId + '][TYPE]') && BX('PRODUCT[' + itemId + '][SET_PARENT_ID]')
							&& ((BX('PRODUCT[' + itemId + '][TYPE]').value.length == 0 || BX('PRODUCT[' + itemId + '][TYPE]').value == 0)
								&& (BX('PRODUCT[' + itemId + '][SET_PARENT_ID]').value.length > 0 && BX('PRODUCT[' + itemId + '][SET_PARENT_ID]').value != 0))
							)
							continue;

						col.innerHTML = productIndex;

						productIndex++;
					}
				}
			}

			if (BX("total_number"))
				BX("total_number").innerHTML = productCountMessage.replace('#NUMBER#', productIndex - 1);
		}
	}

	/*
	* click on recommended More
	*/
	function fGetMoreRecom()
	{
		BX('recom_more').value = "Y";
		fRecalProduct('', '', 'Y', 'N', null);
	}

	/*
	* click on basket more
	*/
	function fGetMoreBasket(showAll)
	{
		recalcViewed = showAll;

		if (showAll == "Y")
			BX('recom_more_basket').value = "Y";

		showAll = BX('recom_more_basket').value;
		var userId = BX('user_id').value;
		var productData = "{";
		for(var i in arProduct)
			productData = productData + "'"+i+"':'"+arProduct[i]+"',";
		productData = productData + "}";

		BX.ajax.post('/bitrix/admin/sale_order_new.php', '<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&showAll='+showAll+'&arProduct='+productData+'&getmorebasket=Y&CURRENCY=<?=$str_CURRENCY?>&LID=<?=CUtil::JSEscape($LID)?>&userId=' + userId, fGetMoreBasketResult);
	}

	function fGetMoreBasketResult(res)
	{
		if (res.length > 0)
			BX('user_basket').innerHTML = res;
		else
		{
			BX('tab_2').style.display = "none";
			BX('user_basket').style.display = "none";

			if (BX('tab_1').style.display == "block")
				fTabsSelect('user_recomendet', 'tab_1');
			else if (BX('tab_3').style.display == "block")
				fTabsSelect('buyer_viewed', 'tab_3');
		}

		if (recalcViewed != "R")
			fGetMoreViewed('R');
	}

	/*
	* click on basket more
	*/
	function fGetMoreViewed(showAll) {
		recalcBasket = showAll;

		if (showAll == "Y")
			BX('recom_more_viewed').value = "Y";

		showAll = BX('recom_more_viewed').value;
		var userId = BX('user_id').value;
		var productData = "{";
		for(var i in arProduct)
			productData = productData + "'"+i+"':'"+arProduct[i]+"',";
		productData = productData + "}";

		BX.ajax.post('/bitrix/admin/sale_order_new.php', '<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&showAll='+showAll+'&arProduct='+productData+'&getmoreviewed=Y&CURRENCY=<?=$str_CURRENCY?>&LID=<?=CUtil::JSEscape($LID)?>&userId=' + userId, fGetMoreViewedResult);
	}

	function fGetMoreViewedResult(res)
	{
		if (res.length > 0)
			BX('buyer_viewed').innerHTML = res;
		else
		{
			BX('tab_3').style.display = "none";
			BX('buyer_viewed').style.display = "none";

			if (BX('tab_1').style.display == "block")
				fTabsSelect('user_recomendet', 'tab_1');
			else if (BX('tab_2').style.display == "block")
				fTabsSelect('user_basket', 'tab_2');
		}

		if (recalcBasket != "R")
			fGetMoreBasket('R');
	}

	function fAddToBasketMoreProductResult(res)
	{
		BX.closeWait();
		var result = eval( '('+res+')' ),
			type = result["type"],
			params = result["params"];

		FillProductFields(0, params, 0);

		if (params['setItems'].length > 0) // if set items exist
		{
			for (var i = 0; i < params['setItems'].length; i++)
			{
				FillProductFields(params['setItems'][i]['id'], params['setItems'][i], 0, 'Y');
			}
		}

		if (type == 'basket')
			fGetMoreBasket('');
		if (type == 'viewed')
			fGetMoreViewed('');

		return false;
	}

	/*
	* add to order basket table products from recommended products or from basket
	*/
	function fAddToBasketMoreProduct(type, productId)
	{
		BX.showWait();
		var strUserColumns = BX("userColumns").value,
			userId = document.order_edit_info_form.user_id.value;

		BX.ajax.post(
			'/bitrix/admin/sale_order_new.php',
			'<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&get_product_params=Y&ID=<?=$ID?>&LID=<?=CUtil::JSEscape($LID)?>&type=' + type + '&productId=' + productId + '&userId=' + userId + '&userColumns=' + strUserColumns,
			fAddToBasketMoreProductResult
		);
	}

	function fAddStore(id, arStores, maxSelectNumber, isMultiBarcode)
	{
		//TODO - when adding - iterate over all existing selectors from the first to the maxSelectNumber
		//the first found will be the new index

		var storeSelectors = BX.findChildren(BX('store_select_block_' + id), {'tag':'div', 'className': 'store_select_wrapper'}, false),
			uniqId = id + '_' + storeSelectors.length,
			newStoreId = storeSelectors.length,
			countStoreSelectors = storeSelectors.length + 1,
			newStoreDiv = BX.create('div', {
				props: {
					'id': 'store_select_wrapper_' + uniqId,
					'className': 'store_row_element store_select_wrapper'
				},
				children: [
					newStoreDeleteDiv = BX.create('div', {
						props: {
							'id': 'store_select_delete_' + uniqId,
							'name': 'store_select_delete_' + uniqId,
							'className': 'store_row_element store_select_delete',
						},
					}),
					newStoreSelect = BX.create('select', {
						props: {
							'id': 'PRODUCT[' + id + '][STORES][' + newStoreId + '][STORE_ID]',
							'name': 'PRODUCT[' + id + '][STORES][' + newStoreId + '][STORE_ID]',
							'className': 'store_row_element',
						},
					}),
					newStoreAmountHidden = BX.create('input', {
						props: {
							'id': 'PRODUCT[' + id + '][STORES][' + newStoreId + '][AMOUNT]',
							'name': 'PRODUCT[' + id + '][STORES][' + newStoreId + '][AMOUNT]',
							'type': 'hidden',
							'value': arStores[0].AMOUNT //TODO
						},
					}),
					newStoreNameHidden = BX.create('input', {
						props: {
							'id': 'PRODUCT[' + id + '][STORES][' + newStoreId + '][STORE_NAME]',
							'name': 'PRODUCT[' + id + '][STORES][' + newStoreId + '][STORE_NAME]',
							'type': 'hidden',
							'value': arStores[0].STORE_NAME //TODO
						},
					})
				]
			}),
			newAmountDiv = BX.create('div', {
				props: {
					'id' : 'store_amount_wrapper_' + uniqId,
					'className': 'store_row_element'
				},
				children: [
					newAmountInput = BX.create('input', {
						props: {
							'id': 'PRODUCT[' + id + '][STORES][' + newStoreId + '][QUANTITY]',
							'name': 'PRODUCT[' + id + '][STORES][' + newStoreId + '][QUANTITY]',
							'type': 'text',
							'size': '4',
							'maxlength': '7',
						}
					}),
					newAmountSpan = BX.create('span', {
						props: {
							'id': 'store_max_amount_' + uniqId,
							'type': 'text',
							'size': '4',
							'maxlength': '7'
						},
						html: '&nbsp;/&nbsp;' + arStores[0].AMOUNT
					})
				]
			});

		if (isMultiBarcode)
		{
			// todo - change to DOM later
			var barcodeButtonData = '<div align="center"><a onClick="enterBarcodes(' + id + ', ' + newStoreId + ');" class="adm-btn adm-btn-barcode"><?=GetMessage("NEWO_STORE_ADD_BARCODES")?></a></div>';
			barcodeButtonData += '<div id="STORE_BARCODE_MULTI_DIV_' + id + '_' + newStoreId + '" class="store_barcode_hidden_div">';
			barcodeButtonData += '<div style="display: block;" class="store_barcode_scroll_div" id="STORE_BARCODE_DIV_SCROLL_' + id +  '_' + newStoreId + '">';
			barcodeButtonData += '<table id="STORE_BARCODE_TABLE_MULTI_' + id + '_' + newStoreId + '"><tbody>';
			barcodeButtonData += '</tbody></table></div></div>';

			newBarcodeInputDiv = BX.create('div', {
				props: {
					'id' : 'store_barcode_wrapper_' + uniqId,
					'className' : 'store_row_element'
				},
				html: barcodeButtonData
			});

			BX('store_barcode_block_' + id).appendChild(newBarcodeInputDiv);
		}

		//adding select values
		for (var i = 0; i < arStores.length; i++) {
			newStoreSelect.options[newStoreSelect.options.length] = new Option(
				arStores[i].STORE_NAME + ' [' + arStores[i].STORE_ID + ']',
				arStores[i].STORE_ID
			);
		};

		BX('store_select_block_' + id).appendChild(newStoreDiv);
		BX('store_amount_block_' + id).appendChild(newAmountDiv);
		// BX('store_barcode_found_block_' + id).appendChild(newBarcodeFoundCheckbox);

		//store selector change
		BX.bind(BX('PRODUCT[' + id + '][STORES][' + newStoreId + '][STORE_ID]'), 'change', function() {
				return fChangeStoreSelector(this, id, storeSelectors.length, arStores);
			}
		);

		//store delete button
		BX.bind(BX('store_select_wrapper_' + uniqId), 'mouseover', function() {
				BX.addClass(BX('store_select_delete_' + uniqId), "store_select_delete_button");
			}
		);
		BX.bind(BX('store_select_wrapper_' + uniqId), 'mouseout', function() {
				BX.removeClass(BX('store_select_delete_' + uniqId), "store_select_delete_button");
			}
		);
		BX.bind(BX('store_select_delete_' + uniqId), 'click', function() {
				return fDeleteStore(id, uniqId, maxSelectNumber);
			}
		);

		// barcode check
		BX.bind(BX('PRODUCT[' + id + '][STORES][' + newStoreId + '][BARCODE]'), 'change', function() {
				return fCheckBarcode(id, storeSelectors.length, false);
			}
		);

		if (countStoreSelectors >= maxSelectNumber)
			BX('add_store_link_' + id).style.display = "none";
	}

	function fDeleteStore(id, uniqId, maxSelectNumber)
	{
		var isMultiBarcode = (BX("PRODUCT[" + id + "][BARCODE_MULTI]").value == "Y") ? true : false;

		BX.remove(BX('store_select_wrapper_' + uniqId));
		BX.remove(BX('store_amount_wrapper_' + uniqId));

		if (isMultiBarcode) // only product with multi barcode has more than 1 barcode control (button or input field) which should be deleted
			BX.remove(BX('store_barcode_wrapper_' + uniqId));

		var storeSelectors = BX.findChildren(BX('store_select_block_' + id), {'tag':'div', 'className': 'store_select_wrapper'}, false),
			countStoreSelectors = storeSelectors.length + 1;

		//show again link 'Add store'
		if (countStoreSelectors >= maxSelectNumber)
			BX('add_store_link_' + id).style.display = "inline";
	}

	function fChangeStoreSelector(el, basketItemId, selectorIndex, arStores)
	{
		var storeIndex = el.options[el.selectedIndex].value.split("_").pop();

		for (var i = 0; i < arStores.length; i++)
		{
			if (arStores[i].STORE_ID == storeIndex)
			{
				BX('store_max_amount_' + basketItemId + '_' + selectorIndex).innerHTML = '&nbsp;/&nbsp;' + arStores[i].AMOUNT;
			}
		};

		BX('PRODUCT[' + basketItemId + '][STORES][' + selectorIndex + '][AMOUNT]').value = arStores[el.selectedIndex].AMOUNT;
		BX('PRODUCT[' + basketItemId + '][STORES][' + selectorIndex + '][STORE_NAME]').value = arStores[el.selectedIndex].STORE_NAME;

		//TODO
		// var barcodeSelector = BX('PRODUCT[' + basketItemId + '][STORES][' + selectorIndex + '][BARCODE]');
	}

	function fCheckBarcode(basketItemId, storeId, isMultiBarcode, barcodeId)
	{
		var isNewProduct;
		if (BX("PRODUCT[" + basketItemId + "][NEW_PRODUCT]"))
			isNewProduct = true;
		else
			isNewProduct = false;

		if (isNewProduct)
		{
			var productId = BX("PRODUCT[" + basketItemId + "][PRODUCT_ID]").value,
				productProvider = BX("PRODUCT_PROVIDER_CLASS_" + basketItemId).value,
				moduleName = BX("PRODUCT[" + basketItemId + "][MODULE]").value,
				barcodeMulti = BX("PRODUCT[" + basketItemId + "][BARCODE_MULTI]").value;
		}

		if (isMultiBarcode)
		{
			var barcode = BX('PRODUCT[' + basketItemId + '][STORES][' + storeId + '][BARCODE][' + barcodeId + ']'),
				barcodeFound = BX('PRODUCT[' + basketItemId + '][STORES][' + storeId + '][BARCODE_FOUND][' + barcodeId + ']');
		}
		else
		{
			var barcode = BX('PRODUCT[' + basketItemId + '][STORES][' + storeId + '][BARCODE]'),
				barcodeFound = '';
		}

		var realStoreId = BX('PRODUCT[' + basketItemId + '][STORES][' + storeId + '][STORE_ID]').value;

		if (barcode.value.length == 0)
		{
			BX.removeClass(barcode, 'store_barcode_not_found');
			BX.removeClass(barcode, 'store_barcode_found_input');
			barcodeFound.value = "N";
		}
		else
		{
			if (isNewProduct)
			{
				BX.showWait();
				BX.ajax.post(
					'/bitrix/admin/sale_order_new.php',
					'<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&id=<?=$ID?>&LID=<?=CUtil::JSEscape($LID)?>&checkBarcode=Y&productId=' + productId + '&barcode=' + barcode.value + '&storeId=' + realStoreId + '&productProvider=' + productProvider + '&moduleName=' + moduleName + '&barcodeMulti=' + barcodeMulti,
					function (res)
					{
						var result = eval( '('+res+')' );

						BX.closeWait();
						if (result["status"] == "ok")
						{
							BX.removeClass(barcode, 'store_barcode_not_found');
							BX.addClass(barcode, 'store_barcode_found_input');
							barcodeFound.value = "Y";
						}
						else
						{
							if (barcode.value != '')
							{
								BX.removeClass(barcode, 'store_barcode_found_input');
								BX.addClass(barcode, 'store_barcode_not_found');
								barcodeFound.value = "N";
							}
							else
							{
								BX.removeClass(barcode, 'store_barcode_not_found');
								BX.removeClass(barcode, 'store_barcode_found_input');
								barcodeFound.value = "N";
							}
						}
					}
				);
			}
			else
			{
				BX.showWait();
				BX.ajax.post(
					'/bitrix/admin/sale_order_new.php',
					'<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&id=<?=$ID?>&LID=<?=CUtil::JSEscape($LID)?>&checkBarcode=Y&basketItemId=' + basketItemId + '&barcode=' + barcode.value + '&storeId=' + realStoreId,
					function (res)
					{
						var result = eval( '('+res+')' );

						BX.closeWait();
						if (result["status"] == "ok")
						{
							BX.removeClass(barcode, 'store_barcode_not_found');
							BX.addClass(barcode, 'store_barcode_found_input');
							barcodeFound.value = "Y";
						}
						else
						{
							if (barcode.value != '')
							{
								BX.removeClass(barcode, 'store_barcode_found_input');
								BX.addClass(barcode, 'store_barcode_not_found');
								barcodeFound.value = "N";
							}
							else
							{
								BX.removeClass(barcode, 'store_barcode_not_found');
								BX.removeClass(barcode, 'store_barcode_found_input');
								barcodeFound.value = "N";
							}
						}
					}
				);
			}
		}
	}

	function fShowReasonTextarea(showArea)
	{
		if (!showArea)
		{
			BX('reason_undo_deducted_area').style.display = 'table-row';
		}
		else
		{
			BX('reason_undo_deducted_area').style.display = 'none';
		}
	}

	function toggleStoresView(el, useStores)
	{
		var checkboxValue = el.checked;

		el.value = (el.value == "Y") ? "N" : "Y"; // toggle value

		var hasMultipleBarcodes = (BX('HAS_PRODUCTS_WITH_BARCODE_MULTI').value == "Y") ? true : false;

		!fShowReasonTextarea(checkboxValue);

		if (useStores)
		{
			if (checkboxValue)
			{
				BX('heading_with_stores').style.display = 'table-row';
				BX('heading_without_stores').style.display = 'none';

				var store_items = [];
				store_items = store_items.concat(BX.findChildren(BX('BASKET_TABLE'), {className:'store'}, true));
				store_items = store_items.concat(BX.findChildren(BX('BASKET_TABLE'), {className:'store_amount'}, true));
				store_items = store_items.concat(BX.findChildren(BX('BASKET_TABLE'), {className:'store_barcode'}, true));

				if (store_items)
				{
					for(var i=0; i<store_items.length; i++)
					{
						store_items[i].style.display = 'table-cell';
					}
				}

				// show hidden set items
				var basketTable = BX("BASKET_TABLE"),
					itemId;

				for (var i = 2, row; row = basketTable.rows[i]; i++)
				{
					itemId = row.id.replace("BASKET_TABLE_ROW_", "");

					if (((BX('PRODUCT[' + itemId + '][TYPE]').value.length == 0 || BX('PRODUCT[' + itemId + '][TYPE]').value == 0)  && BX('PRODUCT[' + itemId + '][SET_PARENT_ID]').value.length > 0)) // if is set item
					{
						BX("BASKET_TABLE_ROW_" + itemId).style.display = 'table-row';
						BX("set_toggle_link_" + BX('PRODUCT[' + itemId + '][SET_PARENT_ID]').value).innerHTML = '<?=GetMessage("SOE_HIDE_SET")?>';
					}
				}
			}
			else
			{
				BX('heading_without_stores').style.display = 'table-row';
				BX('heading_with_stores').style.display = 'none';

				var store_items = [];
				store_items = store_items.concat(BX.findChildren(BX('BASKET_TABLE'), {className:'store'}, true));
				store_items = store_items.concat(BX.findChildren(BX('BASKET_TABLE'), {className:'store_amount'}, true));
				store_items = store_items.concat(BX.findChildren(BX('BASKET_TABLE'), {className:'store_barcode'}, true));

				if (store_items)
				{
					for (var i=0; i<store_items.length; i++)
					{
						store_items[i].style.display = 'none';
					}
				}
			}
		}
	}

	function showColumnsForm()
	{
		var bCreated = false;
		if(!window['orderNewTableSettings'])
		{
			window['orderNewTableSettings'] = new BX.CDialog({
				'content':'<form id="order_new_table_settings" name="order_new_table_settings"></form>',
				'title': '<?=GetMessage("NEWO_COLUMNS_SETTINGS")?>',
				'width': 850,
				'height': 350,
				'resizable': false
			});
			bCreated = true;
		}

		window['orderNewTableSettings'].ClearButtons();
		window['orderNewTableSettings'].SetButtons([
			{
				'title': '<?=GetMessage("SOE_APPLY")?>',
				'action': function() {
					var form = document['order_new_table_settings'],
						sCols = '';
						n = form.view_cols.length;
						IDs = BX("ids").value,
						propCount = 0,
						PROP_COUNT_LIMIT = 21,
						bPropCountLimitAlert = false,
						bHideTotalCountTable = true;

					for (var i=0; i<n; i++)
					{
						if (typeof(form.view_cols[i]) !== 'undefined')
						{
							if (form.view_cols[i].value.indexOf("PROPERTY_") == 0) // property columns (show if limit not exceeded)
							{
								propCount++;

								if (propCount < PROP_COUNT_LIMIT)
									sCols += (sCols!=''? ',':'') + form.view_cols[i].value;
								else
								{
									if (!bPropCountLimitAlert)
									{
										alert('<?=GetMessage("SOE_PROP_COUNT_LIMIT")?>');
										bPropCountLimitAlert = true;
									}
									continue;
								}
							}
							else // other columns
							{
								sCols += (sCols!=''? ',':'') + form.view_cols[i].value;

								if (form.view_cols[i].value == 'COLUMN_NUMBER')
									bHideTotalCountTable = false;
							}
						}
					}

					if (bHideTotalCountTable)
						BX("total_count_table").style.display = 'none';

					BX.showWait();
					BX.ajax.post('/bitrix/admin/sale_order_new.php', '<?=bitrix_sessid_get()?>&ORDER_AJAX=Y&change_columns=Y&cols=' + sCols + '&ids=' + IDs + '&ID=<?=$ID?>', changeColumns);

					this.parentWindow.Close();
				}
			},
			BX.CDialog.prototype.btnCancel
		]);
		window['orderNewTableSettings'].Show();

		var form = document['order_new_table_settings'],
			table = document.createElement("div");

		table.innerHTML = '<?=$settingsTemplate?>';

		if(bCreated)
			form.appendChild(table);
	}

	function changeColumns(res)
	{
		BX.closeWait();

		var result = eval( '('+res+')' );

		if (result["status"] == "Y")
		{
			var basketContainer = BX("ID_BASKET_CONTAINER"),
				columnsData = eval(result["columns"]),
				columnsString = eval(result["columnsString"]),
				propData = eval(result["data"]),
				oldTable = BX("BASKET_TABLE");

			newTable = oldTable.cloneNode(true);
			newTable.id = "NEW_BASKET_TABLE";
			basketContainer.appendChild(newTable);

			// HEADERS
			var headerWithStores = newTable.rows[0],
				headerWithoutStores = newTable.rows[1];

			// remove old headers except the first menu cell
			for (var i = headerWithoutStores.cells.length - 1; i > 0; i--)
				headerWithoutStores.deleteCell(i);

			for (var i = headerWithStores.cells.length - 1; i > 0; i--)
				headerWithStores.deleteCell(i);

			// add new headers on the basis of ajax data
			for (var columnName in columnsData)
			{
				var cell = headerWithStores.insertCell(-1);
				cell.innerHTML = columnsData[columnName];

				if (columnName == 'COLUMN_QUANTITY') // add hidden store data columns' headers
				{
					var cell = headerWithStores.insertCell(-1);
					cell.innerHTML = '<?=GetMessage("SALE_F_STORE")?>';
					var cell = headerWithStores.insertCell(-1);
					cell.innerHTML = '<?=GetMessage("SALE_F_STORE_AMOUNT")?>';
					var cell = headerWithStores.insertCell(-1);
					cell.innerHTML = '<?=GetMessage("SALE_F_STORE_BARCODE")?>';
				}

				if (columnName == 'COLUMN_NUMBER')
					BX('total_count_table').style.display = 'table';

				var cell = headerWithoutStores.insertCell(-1);
				cell.innerHTML = columnsData[columnName];
			}

			// TABLE
			// filling newTable cells with data: by cloning from the old table or from ajax data
			for (var i = 2, row; row = newTable.rows[i]; i++)
			{
				for (var k = row.cells.length - 1; k > 0; k--) // remove old cells
					row.deleteCell(k);

				var itemId = row.id.replace("BASKET_TABLE_ROW_", ""),
					productId = BX("PRODUCT[" + itemId + "][PRODUCT_ID]").value;

				oldRow = oldTable.rows[i];
				for (var columnName in columnsData) // iterate over each column from the user's column set
				{
					var bHasContent = false;
					for (var p = 0, col; col = oldRow.cells[p]; p++)
					{
						if (col.className.indexOf(columnName) != -1) // if found in the old table - clone cell data
						{
							var cell = oldRow.cells[p];
							if (cell.style.display == 'none')
								cell.style.display = "table-cell";

							row.appendChild(cell);

							if (col.className == 'COLUMN_QUANTITY') // add store data columns
							{
								var cell = BX('td_store_block_' + itemId);
								row.appendChild(cell);
								var cell = BX('store_amount_block_' + itemId);
								row.appendChild(cell);
								var cell = BX('store_barcode_block_' + itemId);
								row.appendChild(cell);
							}

							bHasContent = true;
							break;
						}
					}

					if (!bHasContent) // add from ajax data
					{
						var cell = row.insertCell(-1);
						cell.innerHTML = propData[productId][columnName + '_VALUE'];
						cell.className = 'property_field ' + columnName;
					}
				}

				// some columns should be added as hidden even if they are not in the columnsData
				var arNecessaryColumns = ['COLUMN_NUMBER', 'COLUMN_IMAGE', 'COLUMN_PROPS', 'COLUMN_REMAINING_QUANTITY'],
					addedColumns = [];

				for (var n = 0, column2add; column2add = arNecessaryColumns[n]; n++)
				{
					for (var p = 0, col; col = oldRow.cells[p]; p++)
					{
						if (!(column2add in columnsData) && (!BX.util.in_array(column2add, addedColumns)) && (col.className == column2add))
						{
							var cell = oldRow.cells[p];
							cell.style.display = 'none';
							row.appendChild(cell);
							addedColumns.push(column2add);
						}
					}
					addedColumns = [];
				}
			}

			// remove old table
			oldTable.parentNode.removeChild(oldTable);
			newTable.id = "BASKET_TABLE";

			// move total_product_count_table
			if (BX("total_count_table"))
			{
				totalProductNumberTable = BX("total_count_table");
				basketContainer.appendChild(totalProductNumberTable.cloneNode(true));
				totalProductNumberTable.parentNode.removeChild(totalProductNumberTable);
			}

			BX("userColumns").value = columnsString;
		}
	}

	function fChangeQuantityValue(basketId, action, ratio)
	{
		var quantityFieldId = 'PRODUCT[' + basketId + '][QUANTITY]'

		if (BX(quantityFieldId))
		{
			var oldVal = parseFloat(BX(quantityFieldId).value),
				newVal;

			if (action == 'up')
			{
				newVal = oldVal + ratio;
			}
			else if (action == 'down')
			{
				newVal = oldVal - ratio;
			}

			BX(quantityFieldId).value = newVal;
			fRecalProduct(basketId, '', 'N', 'N', null);
		}
	}
</script>
	</td>
</tr>
<tr>
	<td colspan="2"><br>
		<input type="hidden" name="recom_more" id="recom_more" value="N" >
		<input type="hidden" name="recom_more_basket" id="recom_more_basket" value="N" >
		<input type="hidden" name="recom_more_viewed" id="recom_more_viewed" value="N" >
		<table width="100%" class="order_summary">
			<tr>
				<td valign="top" id="itog_tabs" class="load_product">
					<table width="100%" class="itog_header"><tr><td><?=GetMessage('NEWO_SUBTAB_RECOM_REQUEST');?></td></tr></table>
					<br>
					<div id="tabs">
						<?
						$displayNone = "block";
						$displayNoneBasket = "block";
						$displayNoneViewed = "block";

						$arRecommended = CSaleProduct::GetRecommendetProduct($str_USER_ID, $LID, $arFilterRecommended);

						$arRecommendedResult = fDeleteDoubleProduct($arRecommended, $arFilterRecommended, 'N');
						if (empty($arRecommendedResult["ITEMS"]))
							$displayNone = "none";

						$arCartWithoutSetItems = array();
						$arTmpShoppingCart = CSaleBasket::DoGetUserShoppingCart($LID, $str_USER_ID, $FUSER_ID, $arErrors, $arCoupon);

						if (is_array($arTmpShoppingCart))
						{
							foreach ($arTmpShoppingCart as $arCartItem)
							{
								if (CSaleBasketHelper::isSetItem($arCartItem))
									continue;

								$arCartWithoutSetItems[] = $arCartItem;
							}
						}
						$arShoppingCart = fDeleteDoubleProduct($arCartWithoutSetItems, $arFilterRecommended, 'N');
						if (empty($arShoppingCart["ITEMS"]))
							$displayNoneBasket = "none";

						$viewed = array();
						$arViewedResult = array();
						//
						if (Loader::includeModule('catalog'))
						{
							$viewedIterator = \Bitrix\Catalog\CatalogViewedProductTable::getList(
								array(
									"filter" => array("FUSER_ID" => $arFuserItems["ID"], "SITE_ID" => $str_LID),
									"select" => array(
										"ID",
										"PRODUCT_ID",
										"LID" => "SITE_ID",
										"NAME" => "ELEMENT.NAME",
										"PREVIEW_PICTURE" => "ELEMENT.PREVIEW_PICTURE",
										"DETAIL_PICTURE" => "ELEMENT.DETAIL_PICTURE",
									),
									"order" => array("DATE_VISIT" => "DESC"),
									"limit" => 10
								)
							);

							while($row = $viewedIterator->fetch())
							{
								$row['MODULE'] = "catalog";
								$viewed[$row['PRODUCT_ID']] = $row;
							}


						if (!empty($viewed))
						{
							$filter = array("ID" => array_keys($viewed));

							$elementIterator = CIBlockElement::GetList(array(), $filter, false, false, array('ID', 'IBLOCK_ID', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'));
							while ($fields = $elementIterator->GetNext())
							{
								$viewed[$fields['ID']]['DETAIL_PAGE_URL'] = $fields['~DETAIL_PAGE_URL'];

								if($viewed[$fields['ID']]['PREVIEW_PICTURE'] > 0)
								{
									$img = CFile::GetFileArray($viewed[$fields['ID']]['PREVIEW_PICTURE']);
									if($img)
										$viewed[$fields['ID']]['PREVIEW_PICTURE'] = $img['SRC'];
									else
										$viewed[$fields['ID']]['PREVIEW_PICTURE'] = false;
								}
								else
								{
									$viewed[$fields['ID']]['PREVIEW_PICTURE'] = false;
								}

								if($viewed[$fields['ID']]['DETAIL_PICTURE'] > 0)
								{
									$img = CFile::GetFileArray($viewed[$fields['ID']]['DETAIL_PICTURE']);

									if($img)
										$viewed[$fields['ID']]['DETAIL_PICTURE'] = $img['SRC'];
									else
										$viewed[$fields['ID']]['DETAIL_PICTURE'] = false;
								}
								else
								{
									$viewed[$fields['ID']]['DETAIL_PICTURE'] = false;
								}
							}

							// Prices
							$priceIterator = CPrice::getList(array(), array("PRODUCT_ID" => $filter['ID']), false, false, array("PRODUCT_ID", "PRICE", "CURRENCY"));
							while($price = $priceIterator->fetch())
							{
								if(!isset($viewed[$price['PRODUCT_ID']]['PRICE']))
								{
									$viewed[$price['PRODUCT_ID']]['PRICE'] = $price['PRICE'];
									$viewed[$price['PRODUCT_ID']]['CURRENCY'] = $price['CURRENCY'];
								}
							}
						}

						//
						}
						$arViewedResult = fDeleteDoubleProduct($viewed, $arFilterRecommended, 'N');
						if (empty($arViewedResult["ITEMS"]))
							$displayNoneViewed = "none";

						$tabBasket = "tabs";
						$tabViewed = "tabs";

						if ($displayNoneBasket == 'none' && $displayNone == 'none' && $displayNoneViewed == 'block')
							$tabViewed .= " active";
						if ($displayNoneBasket == 'block' && $displayNone == 'none')
							$tabBasket .= " active";
						?>
						<div id="tab_1" style="display:<?=$displayNone?>" class="tabs active" onClick="fTabsSelect('user_recomendet', this);"><?=GetMessage('NEWO_SUBTAB_RECOMENET')?></div>
						<div id="tab_2" style="display:<?=$displayNoneBasket?>" class="<?=$tabBasket?>" onClick="fTabsSelect('user_basket', this);"><?=GetMessage('NEWO_SUBTAB_BASKET')?></div>
						<div id="tab_3" style="display:<?=$displayNoneViewed?>" class="<?=$tabViewed?>" onClick="fTabsSelect('buyer_viewed', this);"><?=GetMessage('NEWO_SUBTAB_LOOKED')?></div>

						<?
						if ($displayNone == 'block')
						{
							$displayNoneBasket = 'none';
							$displayNoneViewed = 'none';
						}
						if ($displayNoneBasket == 'block')
						{
							$displayNone = 'none';
							$displayNoneViewed = 'none';
						}
						if ($displayNoneViewed == 'block')
						{
							$displayNone = 'none';
							$displayNoneBasket = 'none';
						}
						?>
						<div id="user_recomendet" class="tabstext active" style="display:<?=$displayNone?>">
							<? echo fGetFormatedProduct($str_USER_ID, $LID, $arRecommendedResult, $str_CURRENCY, 'recom');?>
						</div>

						<div id="user_basket" class="tabstext active" style="display:<?=$displayNoneBasket?>">
						<?
							if (!empty($arShoppingCart["ITEMS"]))
								echo fGetFormatedProduct($str_USER_ID, $LID, $arShoppingCart, $str_CURRENCY, 'basket');
						?>
						</div>

						<div id="buyer_viewed" class="tabstext active" style="display:<?=$displayNoneViewed?>">
						<?
							if (!empty($arViewedResult["ITEMS"]))
								echo fGetFormatedProduct($str_USER_ID, $LID, $arViewedResult, $str_CURRENCY, 'viewed');
						?>

						</div>
					</div>
					<script type="text/javascript">
					function fTabsSelect(tabText, el)
					{
						BX('tab_1').className = "tabs";
						BX('tab_2').className = "tabs";
						BX('tab_3').className = "tabs";

						BX(el).className = "tabs active";
						BX(el).style.display = 'block';

						BX('user_recomendet').className = "tabstext";
						BX('user_basket').className = "tabstext";
						BX('buyer_viewed').className = "tabstext";
						BX('user_recomendet').style.display = 'none';
						BX('user_basket').style.display = 'none';
						BX('buyer_viewed').style.display = 'none';

						BX(tabText).style.display = 'block';
						BX(tabText).className = "tabstext active";
					}
					</script>
				</td>

				<td valign="top" class="summary">
					<div class="order-itog">
						<table width="100%">
							<tr>
								<td class="title">
									<?=GetMessage("NEWO_TOTAL_PRICE")?>
								</td>
								<td nowrap class="title">
									<div id="ORDER_TOTAL_PRICE" style="white-space:nowrap;">
										<?=SaleFormatCurrency($ORDER_TOTAL_PRICE, $str_CURRENCY);?>
									</div>
								</td>
							</tr>
							<tr class="price" style="display:<?echo (($ORDER_PRICE_WITH_DISCOUNT > 0) ? 'table-row' : 'none');?>" id="ORDER_PRICE_WITH_DISCOUNT_DESC_VISIBLE">
								<td id="ORDER_PRICE_WITH_DISCOUNT_DESC" class="title" >
									<div><?=GetMessage("NEWO_TOTAL_PRICE_WITH_DISCOUNT_MARGIN")?></div>
								</td>
								<td nowrap>
									<div id="ORDER_PRICE_WITH_DISCOUNT">
											<?=SaleFormatCurrency($ORDER_PRICE_WITH_DISCOUNT, $str_CURRENCY);?>
									</div>
								</td>
							</tr>
							<tr>
								<td class="title">
									<?=GetMessage("NEWO_TOTAL_DELIVERY")?>
								</td>
								<td nowrap>
									<div id="ORDER_DELIVERY_PRICE" style="white-space:nowrap;">
										<?=SaleFormatCurrency($deliveryPrice, $str_CURRENCY);?>
									</div>
								</td>
							</tr>
							<tr>
								<td class="title">
									<?=GetMessage("NEWO_TOTAL_TAX")?>
								</td>
								<td nowrap>
									<div id="ORDER_TAX_PRICE" style="white-space:nowrap;">
										<?=SaleFormatCurrency($str_TAX_VALUE, $str_CURRENCY);?>
									</div>
								</td>
							</tr>
							<tr>
								<td class="title">
									<?=GetMessage("NEWO_TOTAL_WEIGHT")?>
								</td>
								<td nowrap>
									<div id="ORDER_WAIGHT" style="white-space:nowrap;">
										<?=roundEx(floatval($productWeight / $WEIGHT_KOEF), SALE_WEIGHT_PRECISION)." ".$WEIGHT_UNIT;?>
									</div>
								</td>
							</tr>
						<tr>
						<td class="title">
							<?=GetMessage("NEWO_TOTAL_PAY_ACCOUNT2")?>
						</td>
						<td nowrap class="sum_paid" onclick="fEditSumPaid(true);">
							<span id="ORDER_PAY_FROM_ACCOUNT" style="white-space:nowrap;" onclick="fEditSumPaid(true);">
								<?
								$str_SUM_PAID = floatval($str_SUM_PAID);
								?>
								<?=CCurrencyLang::CurrencyFormat($str_SUM_PAID, $str_CURRENCY, false);?>
							</span>

							<span id="sum_paid_edit" style="display:none">
								<input type="text"
									size="5"
									value="<?=floatval($str_SUM_PAID);?>"
									id="ORDER_PAY_FROM_ACCOUNT_EDIT"
									name="SUM_PAID"
									onblur="fEditSumPaid(false)"
									maxlength="9">
							</span>

							<span>
								<?=$CURRENCY_FORMAT?>
							</span>

							<a onclick="fEditSumPaid(true);" href="javascript:void(0);">
								<span class="pencil"></span>
							</a>
						</td>
						</tr>
						<tr class="price" style="display:<?echo (($str_DISCOUNT_VALUE > 0) ? 'table-row' : 'none');?>" id="ORDER_DISCOUNT_PRICE_VALUE">
							<td class="title" >
								<?=GetMessage("NEWO_TOTAL_DISCOUNT_PRICE_VALUE")?>
							</td>
							<td nowrap>
								<div id="ORDER_DISCOUNT_PRICE_VALUE_VALUE" style="white-space:nowrap;">
										<?=SaleFormatCurrency($str_DISCOUNT_VALUE, $str_CURRENCY);?>
								</div>
							</td>
						</tr>
							<tr class="itog">
								<td class='ileft'>
									<div><?=GetMessage("NEWO_TOTAL_TOTAL")?></div>
								</td>
								<td class='iright' nowrap>
									<div id="ORDER_PRICE_ALL" style="white-space:nowrap;">
										<?=SaleFormatCurrency($str_PRICE, $str_CURRENCY);?>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</table>
	</td>
</tr>
<?
$tabControl->EndCustomField("BASKET_CONTAINER");

if (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1)
{
	$tabControl->Buttons(
		array(
			"disabled" => ($boolLocked || !$bUserCanEditOrder),
			"back_url" => "/bitrix/admin/sale_order_new.php?lang=".LANGUAGE_ID."&ID=".$ID."&dontsave=Y&LID=".CUtil::JSEscape($LID).GetFilterParams("filter_"))
	);
}

$tabControl->Show();

// order basket user by manager
if (isset($_GET["user_id"]) && isset($_GET["LID"]) && !$bVarsFromForm)
{
	$userId = intval($_GET["user_id"]);
	$LID = trim($_GET["LID"]);

	$arParams = array();
	echo '<script type="text/javascript">';
	echo 'window.onload = function () {';
	echo 'fUserGetProfile(BX(\'user_id\'));';

	if ($bUseCatalog && !empty($_GET["product"]) && is_array($_GET["product"]))
	{
		$arProductId = array();
		$arGetProduct = array();

		foreach ($_GET["product"] as $key => $val)
		{
			$key = intval($key);
			if ($key > 0)
				$arGetProduct[$key] = (floatval($val) > 0) ? floatval($val) : 1;
		}

		$arOrder["SORT"] = "ASC";
		if (!empty($arGetProduct))
		{
			foreach ($arGetProduct as $productId => $quantity)
			{
				$arParams = getProductDataToFillBasket($productId, $quantity, $userId, $LID, $strUserColumns);
				$res = CUtil::PhpToJSObject(array("params" => $arParams, "type" => ""));

				echo "getParamsByProductIdResult(".$res.");\n";
			}
		}
	}
	if ($str_USER_ID > 0)
		echo "fButtonCurrent('btnBuyerExistRemote');";
	echo "};";
	echo "</script>";
}
echo '</div>';//end div for form
?>
<div class="sale_popup_form" id="popup_form_sku_order" style="display:none;">
	<table width="100%">
		<tr><td></td></tr>
		<tr>
			<td><small><span id="listItemPrice"></span>&nbsp;<span id="listItemOldPrice"></span></small></td>
		</tr>
		<tr>
			<td><hr></td>
		</tr>
	</table>

	<table width="100%" id="sku_selectors_list">
		<tr>
			<td colspan="2"></td>
		</tr>
	</table>

	<span id="prod_order_button"></span>
	<input type="hidden" value="" name="popup-params-product" id="popup-params-product" >
	<input type="hidden" value="" name="popup-params-type" id="popup-params-type" >
</div>
<script type="text/javascript">
	var wind = new BX.PopupWindow('popup_sku', this, {
		offsetTop : 10,
		offsetLeft : 0,
		autoHide : true,
		closeByEsc : true,
		closeIcon : true,
		draggable: {restrict:true},
		titleBar: {content: BX.create("span", {html: '', 'props': {'className': 'sale-popup-title-bar'}})},
		content : document.getElementById("popup_form_sku_order"),

		buttons: [
			new BX.PopupWindowButton({
				text : '<?=GetMessageJS('NEWO_POPUP_CAN_BUY_NOT');?>',
				id : "popup_sku_save",
				events : {
					click : function()
					{
						if (BX('popup-params-product').value.length > 0)
						{
							if (BX('popup-params-type').value == 'neworder')
							{
								window.location = BX('popup-params-product').value;
							}
							else
							{
								var res = eval( '('+BX('popup-params-product').value+')' );
								fAddToBasketMoreProduct(res['type'], res['id']);
							}

							wind.close();
						}
					}
				}
			}),
			new BX.PopupWindowButton({
				text : '<?=GetMessageJS('NEWO_POPUP_CLOSE');?>',
				id : "popup_sku_cancel",
				events : {
					click : function() {
						wind.close();
					}
				}
			})
		]
	});

	function fAddToBasketMoreProductSku(arSKU, arProperties, type, message)
	{
		BX.message(message);
		wind.show();
		buildSelect("sku_selectors_list", 0, arSKU, arProperties, type);
		var properties_num = arProperties.length;
		var lastPropCode = arProperties[properties_num-1].CODE;
		addHtml(lastPropCode, arSKU, type);
	}

	function buildSelect(cont_name, prop_num, arSKU, arProperties, type)
	{
		var properties_num = arProperties.length;
		var lastPropCode = arProperties[properties_num-1].CODE;

		for (var i = prop_num; i < properties_num; i++)
		{
			var q = BX('prop_' + i);
			if (q)
				q.parentNode.removeChild(q);
		}

		var select = BX.create('SELECT', {
			props: {
				name: arProperties[prop_num].CODE,
				id :  arProperties[prop_num].CODE
			},
			events: {
				change: (prop_num < properties_num-1)
					? function() {
						buildSelect(cont_name, prop_num + 1, arSKU, arProperties, type);
						if (this.value != "null")
							BX(arProperties[prop_num+1].CODE).disabled = false;
						addHtml(lastPropCode, arSKU, type);
					}
					: function() {
						if (this.value != "null")
							addHtml(lastPropCode, arSKU, type)
					}
			}
		});
		if (prop_num != 0) select.disabled = true;

		var ar = [];
		select.add(new Option(arProperties[prop_num].NAME, 'null'));

		for (var i = 0; i < arSKU.length; i++)
		{
			if (checkSKU(arSKU[i], prop_num, arProperties) && !BX.util.in_array(arSKU[i][prop_num], ar))
			{
				select.add(new Option(
						arSKU[i][prop_num],
						prop_num < properties_num-1 ? arSKU[i][prop_num] : arSKU[i]["ID"]
				));
				ar.push(arSKU[i][prop_num]);
			}
		}

		var cont = BX.create('tr', {
			props: {id: 'prop_' + prop_num},
			children:[
				BX.create('td', {html: arProperties[prop_num].NAME + ': '}),
				BX.create('td', { children:[
					select
				]})
			]
		});

		var tmp = BX.findChild(BX(cont_name), {tagName:'tbody'}, false, false);

		tmp.appendChild(cont);

		if (prop_num < properties_num-1)
			buildSelect(cont_name, prop_num + 1, arSKU, arProperties, type);
	}

	function checkSKU(SKU, prop_num, arProperties)
	{
		for (var i = 0; i < prop_num; i++)
		{
			code = BX.findChild(BX('popup_sku'), {'attr': {name: arProperties[i].CODE}}, true, false).value;
			if (SKU[i] != code)
				return false;
		}
		return true;
	}

	function addHtml(lastPropCode, arSKU, type)
	{
		var selectedSkuId = BX(lastPropCode).value;
		var btnText = '';

		BX('popup-window-titlebar-popup_sku').innerHTML = '<span class="sale-popup-title-bar">'+arSKU[0]["PRODUCT_NAME"]+'</span>';
		BX("listItemPrice").innerHTML = BX.message('PRODUCT_PRICE_FROM')+" "+arSKU[0]["MIN_PRICE"];
		BX("listItemOldPrice").innerHTML = '';

		for (var i = 0; i < arSKU.length; i++)
		{
			if (arSKU[i]["ID"] == selectedSkuId)
			{
				BX('popup-window-titlebar-popup_sku').innerHTML = '<span class="sale-popup-title-bar">'+arSKU[i]["NAME"]+'</span>';

				if (arSKU[i]["DISCOUNT_PRICE"] != "")
				{
					BX("listItemPrice").innerHTML = arSKU[i]["DISCOUNT_PRICE_FORMATED"]+" "+arSKU[i]["VALUTA_FORMAT"];
					BX("listItemOldPrice").innerHTML = arSKU[i]["PRICE_FORMATED"]+" "+arSKU[i]["VALUTA_FORMAT"];
					summaFormated = arSKU[i]["DISCOUNT_PRICE_FORMATED"];
					price = arSKU[i]["DISCOUNT_PRICE"];
					priceFormated = arSKU[i]["DISCOUNT_PRICE_FORMATED"];
					priceDiscount = arSKU[i]["PRICE"] - arSKU[i]["DISCOUNT_PRICE"];
				}
				else
				{
					BX("listItemPrice").innerHTML = arSKU[i]["PRICE_FORMATED"]+" "+arSKU[i]["VALUTA_FORMAT"];
					BX("listItemOldPrice").innerHTML = "";
					summaFormated = arSKU[i]["PRICE_FORMATED"];
					price = arSKU[i]["PRICE"];
					priceFormated = arSKU[i]["PRICE_FORMATED"];
					priceDiscount = 0;
				}

				if (arSKU[i]["CAN_BUY"] == "Y")
				{
					var arParams = "{'id' : '"+arSKU[i]["ID"]+"',\n\
					'name' : '"+arSKU[i]["NAME"]+"',\n\
					'url' : '',\n\
					'urlEdit' : '"+arSKU[i]["URL_EDIT"]+"',\n\
					'urlImg' : '"+arSKU[i]["ImageUrl"]+"',\n\
					'price' : '"+price+"',\n\
					'priceFormated' : '"+priceFormated+"',\n\
					'valutaFormat' : '"+arSKU[i]["VALUTA_FORMAT"]+"',\n\
					'priceDiscount' : '"+priceDiscount+"',\n\
					'priceBase' : '"+arSKU[i]["PRICE"]+"',\n\
					'priceBaseFormat' : '"+arSKU[i]["PRICE_FORMATED"]+"',\n\
					'priceTotalFormated' : '"+arSKU[i]["DISCOUNT_PRICE"]+"',\n\
					'discountPercent' : '"+arSKU[i]["DISCOUNT_PERCENT"]+"',\n\
					'summaFormated' : '"+summaFormated+"',\n\
					'quantity' : '1','module' : 'catalog',\n\
					'currency' : '"+arSKU[i]["CURRENCY"]+"',\n\
					'skuProps' : \""+arSKU[i]["SKU_PROPS"]+"\",\n\
					'weight' : '0','vatRate' : '0','priceType' : '',\n\
					'balance' : '"+arSKU[i]["BALANCE"]+"',\n\
					'catalogXmlID' : '','productXmlID' : '',\n\
					'callback' : '',\n\
					'orderCallback':'',\n\
					'cancelCallback':'',\n\
					'payCallback' : '',\n\
					'type' : '" + type + "',\n\
					'productProviderClass':'CCatalogProductProvider'}";

					BX('popup-params-type').value = type;

					if (type != 'neworder')
					{
						message = BX.message('PRODUCT_ADD');
						BX('popup-params-product').value = arParams;
					}
					else
					{
						message = BX.message('PRODUCT_ORDER');
						BX('popup-params-product').value = "/bitrix/admin/sale_order_new.php?lang=<?=LANGUAGE_ID?>&user_id="+arSKU[i]["USER_ID"]+"&LID="+arSKU[i]["LID"]+"&product["+arSKU[i]["ID"]+"]=1";
					}
				}
				else
				{
					BX('popup-params-product').value = '';
					message = BX.message('PRODUCT_NOT_ADD');
				}

				BX.findChild(BX('popup_sku_save'), {'className': 'popup-window-button-text'}, true, false).innerHTML = message;
			}

			if (arSKU[i]["ID"] == selectedSkuId)
				break;
		}
	}

	function fEditSumPaid(edit)
	{
		if (edit)
		{
			var payFromAccountSum = parseFloat(BX('ORDER_PAY_FROM_ACCOUNT_EDIT').value.replace(' ', '').replace(',', '.'));
			BX('ORDER_PAY_FROM_ACCOUNT_EDIT').value = payFromAccountSum;

			BX('ORDER_PAY_FROM_ACCOUNT').style.display = 'none';
			BX('sum_paid_edit').style.display = 'inline-block';
			BX('ORDER_PAY_FROM_ACCOUNT_EDIT').focus();
		}
		else
		{
			BX('ORDER_PAY_FROM_ACCOUNT').style.display = 'inline-block';
			BX('sum_paid_edit').style.display = 'none';



			var maxValue = parseFloat(BX('ORDER_PRICE_ALL').innerHTML.replace(' ', '').replace(',', '.')),
				newValue = 0,
				sumPriceValue = parseFloat(BX('ORDER_PAY_FROM_ACCOUNT_EDIT').value);

			if (!isNaN(sumPriceValue))
			{
				if (sumPriceValue <= maxValue)
					newValue = sumPriceValue;
				else
				{
					newValue = maxValue;
				}
			}

			BX('ORDER_PAY_FROM_ACCOUNT_EDIT').value = newValue;
			BX('ORDER_PAY_FROM_ACCOUNT').innerHTML = newValue;
		}
	}

	<?if(CSaleLocation::isLocationProEnabled()):?>

		function initZipHandling()
		{
			var zipInput = document.querySelector('.-bx-property-is-zip');

			if(BX.type.isDomNode(zipInput)){

				BX.bindDebouncedChange(zipInput, function(value){

					if(typeof window.orderNewLocationPropId == 'undefined' && typeof BX.locationSelectors == 'undefined' || typeof BX.locationSelectors[window.orderNewLocationPropId] == 'undefined')
						return;

					if(BX.type.isNotEmptyString(value) && /^\s*\d+\s*$/.test(value) && value.length > 3){

						getLocationByZip(value, function(locationId){
							window.jamFire = true;
							BX.locationSelectors[window.orderNewLocationPropId].setValueByLocationId(locationId, true);
						}, function(){
							try{
								window.jamFire = true;
								BX.locationSelectors[window.orderNewLocationPropId].clearSelected();
							}catch(e){}
						});
					}
				});
			}
		}

		var indexCache = {};
		var locationCache = {};

		function getLocationByZip(value, successCallback, notFoundCallback)
		{
			if(typeof indexCache[value] != 'undefined')
			{
				successCallback.apply(this, [indexCache[value]]);
				return;
			}

			ShowWaitWindow();

			var ctx = this;

			BX.ajax({

				url: '/bitrix/admin/sale_order_new.php',
				method: 'post',
				dataType: 'json',
				async: true,
				processData: true,
				emulateOnload: true,
				start: true,
				data: {'ACT': 'GET_LOC_BY_ZIP', 'ZIP': value, 'SITE_ID': '<?=CUtil::JSEscape($LID)?>'},
				//cache: true,
				onsuccess: function(result){
					CloseWaitWindow();
					if(result.result){
						indexCache[value] = result.data.ID;

						successCallback.apply(ctx, [result.data.ID]);

					}else
						notFoundCallback.call(ctx);

				},
				onfailure: function(type, e){

					CloseWaitWindow();
					// on error do nothing
				}

			});
		}

		function getZipByLocation(value, successCallback, notFoundCallback)
		{
			if(typeof locationCache[value] != 'undefined')
			{
				successCallback.apply(this, [locationCache[value]]);
				return;
			}

			ShowWaitWindow();

			var ctx = this;

			BX.ajax({

				url: '/bitrix/admin/sale_order_new.php',
				method: 'post',
				dataType: 'json',
				async: true,
				processData: true,
				emulateOnload: true,
				start: true,
				data: {'ACT': 'GET_ZIP_BY_LOC', 'LOC': value},
				//cache: true,
				onsuccess: function(result){

					CloseWaitWindow();
					if(result.result){

						locationCache[value] = result.data.ZIP;

						successCallback.apply(ctx, [result.data.ZIP]);

					}else
						notFoundCallback.call(ctx);

				},
				onfailure: function(type, e){

					CloseWaitWindow();
					// on error do nothing
				}

			});
		}

		initZipHandling();

		BX(function(){
			window.doneInit = true;
		});
	<?endif?>
BX.ready(function() {
	var couponBlock = BX('coupons_block');
	if (!!couponBlock)
		BX.bindDelegate(couponBlock, 'click', { 'attribute': 'data-coupon' }, BX.delegate(function(e){ deleteCoupon(e); }, this));
});
</script>
<?echo BeginNote();?>
1) - <?=GetMessage("NEWO_ORDER_RECOUNT_HINT")?><br>
<?
echo EndNote();

require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/epilog_admin.php");
?>