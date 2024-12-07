<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");

\Bitrix\Main\Loader::includeModule('sale');

$crmMode = (defined("BX_PUBLIC_MODE") && BX_PUBLIC_MODE && isset($_REQUEST["CRM_MANAGER_USER_ID"]));

if ($crmMode)
{
	echo '<link rel="stylesheet" type="text/css" href="/bitrix/themes/.default/sale.css" />';
}

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

\Bitrix\Main\Loader::registerAutoLoadClasses('sale',
							array(
								'\Bitrix\Sale\Helpers\Admin\Blocks\OrderShipmentStatus' => 'lib/helpers/admin/blocks/ordershipmentstatus.php',
							));

$ID = (isset($_REQUEST['ID']) ? (int)$_REQUEST['ID'] : 0);
$errorMessage = "";

if ($ID <= 0)
{
	if ($crmMode)
		CRMModeOutput("Order is not found");
	else
		LocalRedirect("sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
}

$arUserGroups = $USER->GetUserGroupArray();
$intUserID = intval($USER->GetID());

// basket table columns settings form
define("PROP_COUNT_LIMIT", 21);
$arUserColumns = array();
$arIblockProps = array();
$columns = CUserOptions::GetOption("order_basket_table", "table_columns");
$arSelectProps = array();

if ($columns)
{
	$arTmpColumns = explode(",", $columns["columns"]);
	if (CModule::IncludeModule("iblock"))
	{
		$count = 0;
		foreach ($arTmpColumns as $id => $columnCode)
		{
			if (strncmp($columnCode, "PROPERTY_", 9) == 0)
			{
				if ($count >= PROP_COUNT_LIMIT)
					continue;

				$propCode = mb_substr($columnCode, 9);
				if ($propCode == '')
					continue;
				$arSelectProps[] = $columnCode;
				$dbres = CIBlockProperty::GetList(array(), array("CODE" => $propCode));
				if ($arres = $dbres->GetNext())
				{
					$arUserColumns[$columnCode] = $arres["NAME"];
					$arIblockProps[$columnCode] = $arres;
				}
				$count++;
			}
			else
			{
				$arUserColumns[$columnCode] = GetMessage("SOD_".$columnCode);
			}
		}
	}
}
else
{
	$arUserColumns = array(
		"COLUMN_NUMBER" => GetMessage("SOD_COLUMN_NUMBER"),
		"COLUMN_IMAGE" => GetMessage("SOD_COLUMN_IMAGE"),
		"COLUMN_NAME" => GetMessage("SOD_COLUMN_NAME"),
		"COLUMN_QUANTITY" => GetMessage("SOD_COLUMN_QUANTITY"),
		"COLUMN_REMAINING_QUANTITY" => GetMessage("SOD_COLUMN_REMAINING_QUANTITY"),
		"COLUMN_PROPS" => GetMessage("SOD_COLUMN_PROPS"),
		"COLUMN_PRICE" => GetMessage("SOD_COLUMN_PRICE"),
		"COLUMN_SUM" => GetMessage("SOD_COLUMN_SUM"),
	);
}

$customTabber = new CAdminTabEngine("OnAdminSaleOrderView", array("ID" => $ID));

$arTransactTypes = array(
	"ORDER_PAY" => GetMessage("SOD_PAYMENT"),
	"CC_CHARGE_OFF" => GetMessage("SOD_FROM_CARD"),
	"OUT_CHARGE_OFF" => GetMessage("SOD_INPUT"),
	"ORDER_UNPAY" => GetMessage("SOD_CANCEL_PAYMENT"),
	"ORDER_CANCEL_PART" => GetMessage("SOD_CANCEL_SEMIPAYMENT"),
	"MANUAL" => GetMessage("SOD_HAND"),
	"DEL_ACCOUNT" => GetMessage("SOD_DELETE"),
	"AFFILIATE" => GetMessage("SOD1_AFFILIATES_PAY"),
);

$bUserCanViewOrder = CSaleOrder::CanUserViewOrder($ID, $arUserGroups, $intUserID);
$bUserCanEditOrder = CSaleOrder::CanUserUpdateOrder($ID, $arUserGroups);
$bUserCanCancelOrder = CSaleOrder::CanUserCancelOrder($ID, $arUserGroups, $intUserID);
$bUserCanDeductOrder = CSaleOrder::CanUserChangeOrderFlag($ID, "PERM_DEDUCTION", $arUserGroups);
$bUserCanMarkOrder = CSaleOrder::CanUserMarkOrder($ID, $arUserGroups, $intUserID);
$bUserCanPayOrder = CSaleOrder::CanUserChangeOrderFlag($ID, "PERM_PAYMENT", $arUserGroups);
$bUserCanDeliverOrder = CSaleOrder::CanUserChangeOrderFlag($ID, "PERM_DELIVERY", $arUserGroups);
$bUserCanDeleteOrder = CSaleOrder::CanUserDeleteOrder($ID, $arUserGroups, $intUserID);

if (isset($_REQUEST["ORDER_AJAX"]) AND $_REQUEST["ORDER_AJAX"] == "Y" AND check_bitrix_sessid())
{
	$type = $_REQUEST["type"];

	$order = CSaleOrder::getById($ID);
	$allowIds = \Bitrix\Main\Config\Option::get("sale", "p2p_status_list", "");
	if($allowIds <> '')
	{
		$allowIds = unserialize($allowIds, ['allowed_classes' => false]);
	}
	else
	{
		$allowIds = array();
	}

	/*
	* get more product
	*/
	if (isset($type) && $type != "")
	{

		$arResult = array();
		$arErrors = array();
		$LID = (array_key_exists('LID', $_REQUEST))? ($_REQUEST['LID']) : false;
		$currency = (array_key_exists('currency', $_REQUEST))? ($_REQUEST['currency']) : false;
		$userId = (array_key_exists('userId', $_REQUEST))? intval($_REQUEST['userId']) : false;
		$fUserId = (array_key_exists('fUserId', $_REQUEST))? intval($_REQUEST['fUserId']) : false;
		$arProduct = (array_key_exists('arProduct', $_REQUEST))? $_REQUEST['arProduct'] : false;

		$arOrderProduct = CUtil::JsObjectToPhp($arProduct);

		if ($type == 'basket')
		{
			$arCartWithoutSetItems = array();
			$arTmpShoppingCart = CSaleBasket::DoGetUserShoppingCart($LID, $userId, $fUserId, $arErrors, array());
			if (is_array($arTmpShoppingCart))
			{
				foreach ($arTmpShoppingCart as $arCartItem)
				{
					if (CSaleBasketHelper::isSetItem($arCartItem))
						continue;

					$arCartWithoutSetItems[] = $arCartItem;
				}
			}
			if (count($arCartWithoutSetItems) > 0)
				$arResult["ITEMS"] = fGetFormatedProductData($userId, $LID, $arCartWithoutSetItems, 1, $currency, $type, $crmMode);
			else
				$arResult["ITEMS"] = GetMessage('SOD_SUBTAB_BASKET_NULL');
		}
		if ($type == 'recom')
		{
			if (!is_array($arOrderProduct))
				$arOrderProduct = explode(",", $arOrderProduct);
			$arRecommendedResult = CSaleProduct::GetRecommendetProduct($userId, $LID, $arOrderProduct, "Y");
			$arResult["ITEMS"] = fGetFormatedProductData($userId, $LID, $arRecommendedResult, 1, $currency, $type, $crmMode);
		}
		if ($type == 'viewed' && CModule::includeModule("catalog"))
		{
			$viewedIterator = \Bitrix\Catalog\CatalogViewedProductTable::getList(array(
				'order' => array("DATE_VISIT" => "DESC"),
				'filter' => array('FUSER_ID' => $fUserId, "SITE_ID" => $LID),
				'select' => array("ID", "FUSER_ID", "DATE_VISIT", "PRODUCT_ID", "LID" => "SITE_ID", "NAME" => "ELEMENT.NAME", "PREVIEW_PICTURE" => "ELEMENT.PREVIEW_PICTURE", "DETAIL_PICTURE" => "ELEMENT.DETAIL_PICTURE" ),
				'limit' => 10
			));

			$arViewed = array();
			$arViewedIds = array();
			$viewedCount = 0;
			$mapViewed = array();
			while($viewed = $viewedIterator->fetch())
			{
				$viewed['MODULE'] = 'catalog';
				$arViewed[$viewedCount] = $viewed;
				$arViewedIds[] = $viewed['PRODUCT_ID'];
				$mapViewed[$viewed['PRODUCT_ID']] = $viewedCount;
				$viewedCount++;
			}
			unset($viewedCount);

			if (!empty($arViewedIds))
			{
				$baseGroup = CCatalogGroup::getBaseGroup();
				$priceIterator = CPrice::GetList(
					array(),
					array("PRODUCT_ID" => $arViewedIds, 'CATALOG_GROUP_ID' => $baseGroup['ID']),
					false,
					false,
					array("PRODUCT_ID", "PRICE", "CURRENCY")
				);
				while($productPrice = $priceIterator->fetch() )
				{
					if (isset($mapViewed[$productPrice['PRODUCT_ID']]))
					{
						$key = $mapViewed[$productPrice['PRODUCT_ID']];
						$arViewed[$key]["PRICE"] = $productPrice["PRICE"];
						$arViewed[$key]["CURRENCY"] = $productPrice["CURRENCY"];
					}
				}
			}
			$arResult["ITEMS"] =  fGetFormatedProductData($userId, $LID, $arViewed, 1, $currency, $type, $crmMode);
		}

		$arResult["TYPE"] = $type;
		$result = CUtil::PhpToJSObject($arResult);

		CRMModeOutput($result);
		exit;
	}

	/*
	 * save comment
	 */
	if (array_key_exists('comment', $_REQUEST) && $_REQUEST['comment'] <> '')
	{
		$ID = intval($ID);
		$comment = trim($comment);

		$bUserCanEditOrder = CSaleOrder::CanUserUpdateOrder($ID, $arUserGroups);

		if (isset($change) && $change == "Y" && $bUserCanEditOrder && !CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		{
			CSaleOrder::CommentsOrder($ID, $comment);
		}
		$arResult = array('message' => 'ok');
		$result = CUtil::PhpToJSObject($arResult);

		CRMModeOutput($result);
		exit;
	}

	/*
	 * save tracking number
	 */
	if (isset($_REQUEST["tracking_number"]) && (string)$_REQUEST["tracking_number"] !== '')
	{
		$ID = intval($ID);
		$tracking_number = trim($tracking_number);

		$bUserCanEditOrder = CSaleOrder::CanUserUpdateOrder($ID, $arUserGroups);

		if (isset($change) && $change == "Y" && $bUserCanEditOrder && !CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		{
			CSaleOrder::Update($ID, array("TRACKING_NUMBER" => $tracking_number));
		}
		$arResult = array('message' => 'ok');
		$result = CUtil::PhpToJSObject($arResult);

		CRMModeOutput($result);
		exit;
	}

	/*
	 * reason cancel
	 */
	if (isset($_REQUEST["change_cancel"]) && $_REQUEST["change_cancel"] == "Y")
	{
		$errorMessageTmp = "";
		$errorMessageReserve = "";
		$arResult = array();

		if (!$bUserCanCancelOrder)
			$errorMessageTmp .= GetMessage("SOD_NO_PERMS2CANCEL").". ";

		if ($errorMessageTmp == '')
		{
			$CANCELED = trim($_REQUEST["CANCELED"]);
			$REASON_CANCELED = trim($_REQUEST["REASON_CANCELED"]);
			if ($CANCELED != "Y")
				$CANCELED = "N";

			if ($CANCELED != "Y" && $CANCELED != "N")
				$errorMessageTmp .= GetMessage("SOD_WRONG_CANCEL_FLAG").". ";


		}

		if ($errorMessageTmp == '' && !CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		{
			if (!CSaleOrder::CancelOrder($ID, $CANCELED, $REASON_CANCELED))
			{
				if ($ex = $APPLICATION->GetException())
				{
					if ($ex->GetID() == "RESERVATION_ERROR")
					{
						$errorMessageReserve = $ex->GetString();
					}
					else if ($ex->GetID() != "ALREADY_FLAG")
					{
						$errorMessageTmp .= $ex->GetString();
					}
				}
				else
					$errorMessageTmp .= GetMessage("ERROR_CANCEL_ORDER").". ";
			}
		}

		$arResult["message"] = "ok";
		if ($errorMessageTmp <> '')
			$arResult["message"] = $errorMessageTmp;
		elseif (!CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		{
			$dbOrder = CSaleOrder::GetList(
				array("ID" => "DESC"),
				array("ID" => $ID),
				false,
				false,
				array("DATE_CANCELED", "EMP_CANCELED_ID")
			);
			if ($arOrder = $dbOrder->Fetch())
			{
				$arResult["DATE_CANCELED"] = $arOrder["DATE_CANCELED"];
				if (!$crmMode && intval($arOrder["EMP_CANCELED_ID"]) > 0)
					$arResult["EMP_CANCELED_ID"] = GetFormatedUserName($arOrder["EMP_CANCELED_ID"]);
			}
		}

		if ($errorMessageReserve <> '')
		{
			$arResult["reserve_message"] = $errorMessageReserve;
			$arResult["reserve_date"] = $arResult["DATE_CANCELED"];
		}
		else
		{
			$arResult["reserve_message"] = "ok";
		}

		$result = CUtil::PhpToJSObject($arResult);

		CRMModeOutput($result);
		exit;
	}

	/*
	 * reason undo deducted
	 */
	if (isset($_REQUEST["change_deduct"]) && $_REQUEST["change_deduct"] == "Y")
	{
		$errorMessageTmp = "";
		$errorMessageReserve = "";
		$arResult = array();



		if (!$bUserCanDeductOrder)
			$errorMessageTmp .= GetMessage("SOD_NO_PERMS2UNDO_DEDUCT").". ";

		if ($errorMessageTmp == '')
		{
			$UNDO_DEDUCT = (trim($_REQUEST["UNDO_DEDUCT"]) == "Y") ? "N" : "Y"; //reversed logic here
			$REASON_UNDO_DEDUCTED = trim($_REQUEST["REASON_UNDO_DEDUCTED"]);
			if ($UNDO_DEDUCT != "Y")
				$UNDO_DEDUCT = "N";

			if ($UNDO_DEDUCT != "Y" && $UNDO_DEDUCT != "N")
				$errorMessageTmp .= GetMessage("SOD_WRONG_DEDUCT_FLAG").". ";
		}

		if ($errorMessageTmp == '' && !CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		{
			if (!CSaleOrder::DeductOrder($ID, $UNDO_DEDUCT, $REASON_UNDO_DEDUCTED, false))
			{
				if ($ex = $APPLICATION->GetException())
				{
					if ($ex->GetID() == "RESERVATION_ERROR")
					{
						$errorMessageReserve = $ex->GetString();
					}
					else if ($ex->GetID() != "ALREADY_FLAG")
					{
						$errorMessageTmp .= $ex->GetString();
					}
				}
				else
					$errorMessageTmp .= GetMessage("ERROR_UNDO_DEDUCT_ORDER").". ";
			}
		}

		$arResult["message"] = "ok";
		if ($errorMessageTmp <> '')
			$arResult["message"] = $errorMessageTmp;
		elseif (!CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		{
			$dbOrder = CSaleOrder::GetList(
				array("ID" => "DESC"),
				array("ID" => $ID),
				false,
				false,
				array("DATE_DEDUCTED", "EMP_DEDUCTED_ID", "REASON_UNDO_DEDUCTED")
			);
			if ($arOrder = $dbOrder->Fetch())
			{
				$arResult["DATE_DEDUCTED"] = CUtil::JSEscape($arOrder["DATE_DEDUCTED"]);
				$arResult["REASON_UNDO_DEDUCTED"] = CUtil::JSEscape($arOrder["REASON_UNDO_DEDUCTED"]);
				if (!$crmMode && intval($arOrder["EMP_DEDUCTED_ID"]) > 0)
					$arResult["EMP_DEDUCTED_ID"] = CUtil::JSEscape(GetFormatedUserName($arOrder["EMP_DEDUCTED_ID"]));
			}
		}

		if ($errorMessageReserve <> '')
		{
			$arResult["reserve_message"] = $errorMessageReserve;
			$arResult["reserve_date"] = $arResult["DATE_DEDUCTED"];
		}
		else
		{
			$arResult["reserve_message"] = "ok";
		}

		$result = CUtil::PhpToJSObject($arResult);

		CRMModeOutput($result);
		exit;
	}

	/*
	 * reason marked
	 */
	if (isset($_REQUEST["change_marked"]) && $_REQUEST["change_marked"] == "Y")
	{
		$errorMessageTmp = "";
		$arResult = array();

		if (!$bUserCanMarkOrder)
			$errorMessageTmp .= GetMessage("SOD_NO_PERMS2MARK").". ";

		if ($errorMessageTmp == '')
		{
			$MARKED = trim($_REQUEST["MARKED"]);
			$REASON_MARKED = trim($_REQUEST["REASON_MARKED"]);
			if ($MARKED != "Y")
				$MARKED = "N";

			if ($MARKED != "Y" && $MARKED != "N")
				$errorMessageTmp .= GetMessage("SOD_WRONG_MARK_FLAG").". ";
		}

		if ($errorMessageTmp == '' && !CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		{
			if ($MARKED == "Y")
				$rs = CSaleOrder::SetMark($ID, $REASON_MARKED, (0 < $intUserID ? $intUserID : 0));
			else
				$rs = CSaleOrder::UnsetMark($ID, (0 < $intUserID ? $intUserID : 0));

			if (!$rs)
			{
				if ($ex = $APPLICATION->GetException())
				{
					if ($ex->GetID() != "ALREADY_FLAG")
						$errorMessageTmp .= $ex->GetString();
				}
				else
					$errorMessageTmp .= GetMessage("ERROR_MARK_ORDER").". ";
			}
		}

		$arResult["message"] = "ok";
		if ($errorMessageTmp <> '')
			$arResult["message"] = $errorMessageTmp;
		elseif (!CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		{
			$dbOrder = CSaleOrder::GetList(
				array("ID" => "DESC"),
				array("ID" => $ID),
				false,
				false,
				array("DATE_MARKED", "EMP_MARKED_ID")
			);
			if ($arOrder = $dbOrder->Fetch())
			{
				$arResult["DATE_MARKED"] = CUtil::JSEscape($arOrder["DATE_MARKED"]);
				if (!$crmMode && intval($arOrder["EMP_MARKED_ID"]) > 0)
					$arResult["EMP_MARKED_ID"] = CUtil::JSEscape(GetFormatedUserName($arOrder["EMP_CANCELED_ID"]));
			}
		}

		$result = CUtil::PhpToJSObject($arResult);

		CRMModeOutput($result);
		exit;
	}

	/*
	 * delivery
	 */
	if (isset($_REQUEST["change_delivery_form"]) && $_REQUEST["change_delivery_form"] == "Y")
	{
		$errorMessageTmp = "";
		$errorMessageReserve = "";

		if (!$bUserCanDeliverOrder)
			$errorMessageTmp .= GetMessage("SOD_NO_PERMS2DELIV").". ";

		if ($errorMessageTmp == '')
		{
			$ALLOW_DELIVERY = trim($_REQUEST["ALLOW_DELIVERY"]);
			if ($ALLOW_DELIVERY != "Y")
				$ALLOW_DELIVERY = "N";
			if ($ALLOW_DELIVERY != "Y" && $ALLOW_DELIVERY != "N")
				$errorMessageTmp .= GetMessage("SOD_WRONG_DELIV_FLAG").". ";
		}

		if ($errorMessageTmp == '' && !CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		{
			$arAdditionalFields = array(
				"DELIVERY_DOC_NUM" => (($_REQUEST["DELIVERY_DOC_NUM"] <> '') ? $_REQUEST["DELIVERY_DOC_NUM"] : False),
				"DELIVERY_DOC_DATE" => (($_REQUEST["DELIVERY_DOC_DATE"] <> '') ? $_REQUEST["DELIVERY_DOC_DATE"] : False)
			);

			if ($change_status_popup == "Y")
				$arAdditionalFields["NOT_CHANGE_STATUS"] = "Y";

			if (!CSaleOrder::DeliverOrder($ID, $ALLOW_DELIVERY, 0, $arAdditionalFields))
			{
				if ($ex = $APPLICATION->GetException())
				{
					if ($ex->GetID() == "RESERVATION_ERROR")
					{
						$errorMessageReserve = $ex->GetString();
					}
					else if ($ex->GetID() != "ALREADY_FLAG")
					{
						$errorMessageTmp .= $ex->GetString();
					}
				}
				else
					$errorMessageTmp .= GetMessage("ERROR_DELIVERY_ORDER").". ";
			}

			unset($arAdditionalFields["NOT_CHANGE_STATUS"]);

			//update for change data
			$res = CSaleOrder::Update($ID, $arAdditionalFields);
		}

		$arResult["message"] = "ok";
		$arResult["ALLOW_DELIVERY"] = $ALLOW_DELIVERY;
		if ($errorMessageTmp <> '')
			$arResult["message"] = $errorMessageTmp;
		elseif (!CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		{
			$arResult["STATUS_ID"] = "";
			$arResult["EMP_STATUS_ID"] = "";
			$arResult["DATE_STATUS"] = "";
			$arResult["DATE_ALLOW_DELIVERY"] = "";

			$dbOrder = CSaleOrder::GetList(
				array("ID" => "DESC"),
				array("ID" => $ID),
				false,
				false,
				array("DATE_ALLOW_DELIVERY", "EMP_ALLOW_DELIVERY_ID", "STATUS_ID", "DATE_STATUS", "EMP_STATUS_ID")
			);
			if ($arOrder = $dbOrder->Fetch())
			{

				$arResult["DATE_ALLOW_DELIVERY"] = $arOrder["DATE_ALLOW_DELIVERY"];
				if (!$crmMode && intval($arOrder["EMP_ALLOW_DELIVERY_ID"]) > 0)
					$arResult["EMP_ALLOW_DELIVERY_ID"] = GetFormatedUserName($arOrder["EMP_ALLOW_DELIVERY_ID"], false);

				$arResult["DATE_STATUS"] = $arOrder["DATE_STATUS"];
				if (!$crmMode && intval($arOrder["EMP_STATUS_ID"]) > 0)
					$arResult["EMP_STATUS_ID"] = GetFormatedUserName($arOrder["EMP_STATUS_ID"], false);

				$arResult["STATUS_ID"] = $arOrder["STATUS_ID"];
			}

			$arResult["DELIVERY_DOC_NUMBER_FORMAT"] = "";
			if($_REQUEST["DELIVERY_DOC_NUM"] <> '' || $_REQUEST["DELIVERY_DOC_DATE"] <> '')
				$arResult["DELIVERY_DOC_NUMBER_FORMAT"] = GetMessage("SOD_DELIV_DOC", Array("#NUM#" => htmlspecialcharsEx($_REQUEST["DELIVERY_DOC_NUM"]), "#DATE#" => htmlspecialcharsEx($_REQUEST["DELIVERY_DOC_DATE"])));
		}

		if (isset($_REQUEST["change_status"]) && $_REQUEST["change_status"] == "Y")
		{
			$arResultTmp = fChangeOrderStatus($ID, $_REQUEST["STATUS_ID"]);
			$arResult = array_merge($arResult, $arResultTmp);
		}

		if ($errorMessageReserve <> '')
		{
			$arResult["reserve_message"] = $errorMessageReserve;
			$arResult["reserve_date"] = $arResult["DATE_ALLOW_DELIVERY"];
		}
		else
		{
			$arResult["reserve_message"] = "ok";
		}

		$result = CUtil::PhpToJSObject($arResult);

		CRMModeOutput($result);
		exit;
	}

	/*
	 * Execute delivery action
	 */

	if (isset($_REQUEST["DELIVERY_ACTION"]) && $_REQUEST["DELIVERY_ACTION"] <> '')
	{
		$arResult = CSaleDeliveryHelper::execHandlerAction($ID, $_REQUEST["DELIVERY_ACTION"]);
		$result = CUtil::PhpToJSObject($arResult);
		CRMModeOutput($result);
		exit;
	}

	/*
	 * paysystem
	 */
	if (isset($_REQUEST["change_pay_form"]) && $_REQUEST["change_pay_form"] == "Y")
	{
		$errorMessageTmp = "";
		$errorMessageReserve = "";

		if (!$bUserCanPayOrder)
			$errorMessageTmp .= GetMessage("SOD_NO_PERMS2PAYFLAG").". ";

		if ($errorMessageTmp == '')
		{
			$PAYED = trim($_REQUEST["PAYED"]);
			if ($PAYED != "Y")
				$PAYED = "N";

			if ($PAYED != "Y" && $PAYED != "N")
				$errorMessageTmp .= GetMessage("SOD_WRONG_PAYFLAG").". ";



		}

		if ($errorMessageTmp == '' && !CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		{
			$arAdditionalFields = array(
				"PAY_VOUCHER_NUM" => (($_REQUEST["PAY_VOUCHER_NUM"] <> '') ? $_REQUEST["PAY_VOUCHER_NUM"] : False),
				"PAY_VOUCHER_DATE" => (($_REQUEST["PAY_VOUCHER_DATE"] <> '') ? $_REQUEST["PAY_VOUCHER_DATE"] : False)
			);

			$bWithdraw = true;
			$bPay = true;
			if ($_REQUEST["PAY_FROM_ACCOUNT"] == "Y")
				$bPay = false;

			if ($PAYED == "N" && $_REQUEST["PAY_FROM_ACCOUNT_BACK"] != "Y")
				$bWithdraw = false;

			if ($change_status_popup == "Y")
				$arAdditionalFields["NOT_CHANGE_STATUS"] = "Y";

			if (!CSaleOrder::PayOrder($ID, $PAYED, $bWithdraw, $bPay, 0, $arAdditionalFields))
			{
				if ($ex = $APPLICATION->GetException())
				{
					if ($ex->GetID() == "RESERVATION_ERROR")
					{
						$errorMessageReserve = $ex->GetString();
					}
					else if ($ex->GetID() != "ALREADY_FLAG")
						$errorMessageTmp .= $ex->GetString();
				}
				else
					$errorMessageTmp .= GetMessage("ERROR_PAY_ORDER").". ";
			}

			unset($arAdditionalFields["NOT_CHANGE_STATUS"]);

			//update for change data
			$res = CSaleOrder::Update($ID, $arAdditionalFields);
		}

		$arResult["message"] = "ok";
		$arResult["PAYED"] = $PAYED;
		$arResult["BUDGET_ENABLE"] = 'N';

		if ($errorMessageTmp <> '')
			$arResult["message"] = $errorMessageTmp;
		elseif (!CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		{
			$dbOrder = CSaleOrder::GetList(
				array("ID" => "DESC"),
				array("ID" => $ID),
				false,
				false,
				array("DATE_PAYED", "EMP_PAYED_ID", "STATUS_ID", "DATE_STATUS", "EMP_STATUS_ID", "PRICE", "USER_ID", "CURRENCY")
			);
			if ($arOrder = $dbOrder->Fetch())
			{
				$arResult["EMP_STATUS_ID"] = "";

				$arResult["DATE_PAYED"] = trim($arOrder["DATE_PAYED"]);
				if (!$crmMode && intval($arOrder["EMP_PAYED_ID"]) > 0)
					$arResult["EMP_PAYED_ID"] = GetFormatedUserName($arOrder["EMP_PAYED_ID"], false);

				$arResult["DATE_STATUS"] = $arOrder["DATE_STATUS"];
				if (!$crmMode && intval($arOrder["EMP_STATUS_ID"]) > 0)
					$arResult["EMP_STATUS_ID"] = GetFormatedUserName($arOrder["EMP_STATUS_ID"], false);

				$arResult["STATUS_ID"] = $arOrder["STATUS_ID"];

				//user budget
				$dbUserAccount = CSaleUserAccount::GetList(
					array(),
					array(
						"USER_ID" => $arOrder["USER_ID"],
						"CURRENCY" => $arOrder["CURRENCY"],
					)
				);
				$arUserAccount = $dbUserAccount->GetNext();
				if (floatval($arUserAccount["CURRENT_BUDGET"]) >= floatval($arOrder["PRICE"]))
				{
					$arResult["BUDGET_ENABLE"] = 'Y';
					$arResult["BUDGET_USER"] = SaleFormatCurrency(floatval($arUserAccount["CURRENT_BUDGET"]), $arOrder["CURRENCY"]);
				}
			}

			if (trim($_REQUEST["PAY_VOUCHER_NUM"]) <> '')
				$arResult["PAY_DOC_NUMBER_FORMAT"] = str_replace("#DATE#", $_REQUEST["PAY_VOUCHER_DATE"], str_replace("#NUM#", htmlspecialcharsEx($_REQUEST["PAY_VOUCHER_NUM"]), GetMessage("SOD_PAY_DOC")));
		}

		if (isset($_REQUEST["change_status"]) && $_REQUEST["change_status"] == "Y")
		{
			$arResultTmp = fChangeOrderStatus($ID, $_REQUEST["STATUS_ID"]);
			$arResult = array_merge($arResult, $arResultTmp);
		}

		if ($errorMessageReserve <> '')
		{
			$arResult["reserve_message"] = $errorMessageReserve;
			$arResult["reserve_date"] = $arResult["DATE_PAYED"];
		}
		else
		{
			$arResult["reserve_message"] = "ok";
		}

		$result = CUtil::PhpToJSObject($arResult);

		CRMModeOutput($result);
		exit;
	}

	/*
	 * change status
	 */
	if (isset($_REQUEST["change_status"]) && $_REQUEST["change_status"] == "Y")
	{
		$arResult = array();

		if (!CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
			$arResult = fChangeOrderStatus($ID, $_REQUEST["STATUS_ID"]);



		$result = CUtil::PhpToJSObject($arResult);

		CRMModeOutput($result);
		exit;
	}
}

/****************/
if ($saleModulePermissions >= "W" && array_key_exists('unlock', $_REQUEST) && 'Y' == $_REQUEST['unlock'])
{
	CSaleOrder::UnLock($ID);
	if ($crmMode)
		CRMModeOutput($ID);
	LocalRedirect("sale_order_detail.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
}
elseif ($saleModulePermissions >= "U" && check_bitrix_sessid() && !array_key_exists('dontsave', $_REQUEST))
{
	if(!$customTabber->Check())
	{
		if($ex = $APPLICATION->GetException())
			$errorMessage .= $ex->GetString();
		else
			$errorMessage .= "Error. ";
	}
	elseif ($_SERVER['REQUEST_METHOD'] == "POST" && $save_order_data == "Y")
	{
		if (CSaleOrder::IsLocked($ID, $lockedBY, $dateLock))
		{
			$errorMessage .= str_replace(array("#DATE#", "#ID#"), array($dateLock, $lockedBY), GetMessage("SOE_ORDER_LOCKED")).". ";
		}
		else
		{
			if ($errorMessage == '')
			{
				if ($crmMode)
					CRMModeOutput($ID);

				LocalRedirect("sale_order_detail.php?ID=".$ID."&save_order_result=ok&lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
			}
		}
	}
	elseif (isset($_REQUEST["action"]) && $_REQUEST["action"] == "ps_update")
	{
		$errorMessageTmp = "";

		$arOrder = CSaleOrder::GetByID($ID);
		if (!$arOrder)
			$errorMessageTmp .= GetMessage("ERROR_NO_ORDER")."<br>";

		if ($errorMessageTmp == '')
		{
			$psResultFile = "";

			$arPaySys = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"], $arOrder["PERSON_TYPE_ID"]);

			$psActionPath = $_SERVER["DOCUMENT_ROOT"].$arPaySys["PSA_ACTION_FILE"];
			$psActionPath = str_replace("\\", "/", $psActionPath);
			while (mb_substr($psActionPath, mb_strlen($psActionPath) - 1, 1) == "/")
				$psActionPath = mb_substr($psActionPath, 0, mb_strlen($psActionPath) - 1);

			if (file_exists($psActionPath) && is_dir($psActionPath))
			{
				if (file_exists($psActionPath."/result.php") && is_file($psActionPath."/result.php"))
					$psResultFile = $psActionPath."/result.php";
			}
			elseif ($arPaySys["PSA_RESULT_FILE"] <> '')
			{
				if (file_exists($_SERVER["DOCUMENT_ROOT"].$arPaySys["PSA_RESULT_FILE"])
					&& is_file($_SERVER["DOCUMENT_ROOT"].$arPaySys["PSA_RESULT_FILE"]))
					$psResultFile = $_SERVER["DOCUMENT_ROOT"].$arPaySys["PSA_RESULT_FILE"];
			}

			if ($psResultFile == '')
				$errorMessageTmp .= GetMessage("SOD_NO_PS_SCRIPT").". ";
		}

		if ($errorMessageTmp == '')
		{
			$ORDER_ID = $ID;
			CSalePaySystemAction::InitParamArrays($arOrder, $ID, $arPaySys["PSA_PARAMS"]);

			try
			{
				if (!include($psResultFile))
					$errorMessageTmp .= GetMessage("ERROR_CONNECT_PAY_SYS").". ";
			}
			catch(\Bitrix\Main\SystemException $e)
			{
				if($e->getCode() == CSalePaySystemAction::GET_PARAM_VALUE)
					$errorMessageTmp .= GetMessage("SOA_ERROR_PS")." ";
				else
					$errorMessageTmp .= $e->getMessage()." ";
			}
		}

		if ($errorMessageTmp == '')
		{
			$ORDER_ID = intval($ORDER_ID);
			$arOrder = CSaleOrder::GetByID($ORDER_ID);
			if (!$arOrder)
				$errorMessageTmp .= str_replace("#ID#", $ORDER_ID, GetMessage("SOD_NO_ORDER")).". ";
		}
		if ($errorMessageTmp == '')
		{
			if ($arOrder["PS_STATUS"] == "Y" && $arOrder["PAYED"] == "N")
			{
				if ($arOrder["CURRENCY"] == $arOrder["PS_CURRENCY"]
					&& doubleval($arOrder["PRICE"]) == doubleval($arOrder["PS_SUM"]))
				{
					if (!CSaleOrder::PayOrder($arOrder["ID"], "Y", True, True))
					{
						if ($ex = $APPLICATION->GetException())
							$errorMessageTmp .= $ex->GetString();
						else
							$errorMessageTmp .= str_replace("#ID#", $ORDER_ID, GetMessage("SOD_CANT_PAY")).". ";
					}
				}
			}
		}

		if ($errorMessageTmp != "")
			$errorMessage .= $errorMessageTmp;

		if ($errorMessage == '')
		{
			if ($crmMode)
				CRMModeOutput($ID);

			if ($apply <> '' || $_REQUEST["action"] == "ps_update")
				LocalRedirect("sale_order_detail.php?ID=".$ID."&save_order_result=ok_ps&lang=".LANGUAGE_ID.GetFilterParams("filter_", false));

			CSaleOrder::UnLock($ID);
			LocalRedirect("sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
		}
	}
	elseif (isset($_REQUEST["download"]) && $_REQUEST["download"] == "Y")
	{
		if (isset($_REQUEST["file_id"]) && intval($_REQUEST["file_id"]) > 0)
		{
			$arFile = CFile::GetFileArray(intval($_REQUEST["file_id"]));
			set_time_limit(0);
			CFile::ViewByUser($arFile, array("force_download" => true));
		}
	}
}
elseif (array_key_exists('dontsave', $_REQUEST) && 'Y' == $_REQUEST['dontsave'])
{
	$intLockUserID = 0;
	$strLockTime = '';
	if (!CSaleOrder::IsLocked($ID, $intLockUserID, $strLockTime))
		CSaleOrder::UnLock($ID);
	if ($crmMode)
		CRMModeOutput($ID);

	LocalRedirect("sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));
}
/****************/

$boolLocked = false;
$intLockUserID = 0;
$strLockUser = '';
$strLockUserExt = '';
$strLockUserInfo = '';
$strLockUserInfoExt = '';
$strLockTime = '';
$strNameFormat = CSite::GetNameFormat(true);

$dbOrder = CSaleOrder::GetList(
	array("ID" => "DESC"),
	array("ID" => $ID),
	false,
	false,
	array(
		"ID", "LID", "PERSON_TYPE_ID",
		"PAYED", "DATE_PAYED", "EMP_PAYED_ID", "PAY_VOUCHER_NUM", "PAY_VOUCHER_DATE",
		"CANCELED", "DATE_CANCELED", "EMP_CANCELED_ID", "REASON_CANCELED",
		"STATUS_ID", "DATE_STATUS", "EMP_STATUS_ID", "PRICE_DELIVERY",
		"ALLOW_DELIVERY", "DATE_ALLOW_DELIVERY", "EMP_ALLOW_DELIVERY_ID",
		"DEDUCTED", "DATE_DEDUCTED", "EMP_DEDUCTED_ID", "REASON_UNDO_DEDUCTED",
		"MARKED", "DATE_MARKED", "EMP_MARKED_ID", "REASON_MARKED",
		"PRICE", "CURRENCY", "DISCOUNT_VALUE", "SUM_PAID", "USER_ID", "PAY_SYSTEM_ID",
		"DELIVERY_ID", "DATE_INSERT", "DATE_INSERT_FORMAT", "DATE_UPDATE", "USER_DESCRIPTION",
		"ADDITIONAL_INFO", "PS_STATUS", "PS_STATUS_CODE", "PS_STATUS_DESCRIPTION",
		"PS_STATUS_MESSAGE", "PS_SUM", "PS_CURRENCY", "PS_RESPONSE_DATE", "COMMENTS",
		"TAX_VALUE", "STAT_GID", "RECURRING_ID", "AFFILIATE_ID", "LOCK_STATUS",
		"USER_LOGIN", "USER_NAME", "USER_LAST_NAME", "USER_EMAIL", "DELIVERY_DOC_NUM",
		"DELIVERY_DOC_DATE", "STORE_ID", "ACCOUNT_NUMBER", "TRACKING_NUMBER",
	)
);
if (!($arOrder = $dbOrder->Fetch()))
	LocalRedirect("sale_order.php?lang=".LANGUAGE_ID.GetFilterParams("filter_", false));

$boolLocked = CSaleOrder::IsLocked($ID, $intLockUserID, $strLockTime);
if ($boolLocked)
{
	$strLockUser = $intLockUserID;
	$strLockUserInfo = $intLockUserID;
	$rsUsers = CUser::GetList('ID', 'ASC', array('ID' => $intLockUserID), array('FIELDS' => array('ID', 'LOGIN', 'NAME', 'LAST_NAME')));
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

$WEIGHT_UNIT = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_unit', "", $arOrder["LID"]));
$WEIGHT_KOEF = htmlspecialcharsbx(COption::GetOptionString('sale', 'weight_koef', 1, $arOrder["LID"]));

$APPLICATION->SetTitle(GetMessage("SALE_EDIT_RECORD", array("#ID#"=>$ID)));

//get history order list
$arFieldsAll = array(
		"PERSON_TYPE_ID" => GetMessage('SOD_HIST_PERSON_TYPE_ID'),
		"PAYED" => GetMessage('SOD_HIST_PAYED'),
		"DATE_PAYED" => GetMessage('SOD_HIST_DATE_PAYED'),
		"EMP_PAYED_ID" => GetMessage('SOD_HIST_EMP_PAYED_ID'),
		"CANCELED" => GetMessage('SOD_HIST_CANCELED'),
		"DATE_CANCELED" => GetMessage('SOD_HIST_DATE_CANCELED'),
		"EMP_CANCELED_ID" => GetMessage('SOD_HIST_EMP_CANCELED_ID'),
		"REASON_CANCELED" => GetMessage('SOD_HIST_REASON_CANCELED'),
		"DEDUCTED" => GetMessage('SOD_HIST_DEDUCTED'),
		"DATE_DEDUCTED" => GetMessage('SOD_HIST_DATE_DEDUCTED'),
		"EMP_DEDUCTED_ID" => GetMessage('SOD_HIST_EMP_DEDUCTED_ID'),
		"REASON_UNDO_DEDUCTED" => GetMessage('SOD_HIST_REASON_UNDO_DEDUCTED'),
		"MARKED" => GetMessage('SOD_HIST_MARKED'),
		"DATE_MARKED" => GetMessage('SOD_HIST_DATE_MARKED'),
		"EMP_CANCELED_ID" => GetMessage('SOD_HIST_EMP_MARKED_ID'),
		"REASON_MARKED" => GetMessage('SOD_HIST_REASON_MARKED'),
		"RESERVED" => GetMessage('SOD_HIST_RESERVED'),
		"STATUS_ID" => GetMessage('SOD_HIST_STATUS_ID'),
		"DATE_STATUS" => GetMessage('SOD_HIST_DATE_STATUS'),
		"EMP_STATUS_ID" => GetMessage('SOD_HIST_EMP_STATUS_ID'),
		"PRICE_DELIVERY" => GetMessage('SOD_HIST_PRICE_DELIVERY'),
		"ALLOW_DELIVERY" => GetMessage('SOD_HIST_ALLOW_DELIVERY'),
		"DATE_ALLOW_DELIVERY" => GetMessage('SOD_HIST_DATE_ALLOW_DELIVERY'),
		"EMP_ALLOW_DELIVERY_ID" => GetMessage('SOD_HIST_EMP_ALLOW_DELIVERY_ID'),
		"PRICE" => GetMessage('SOD_HIST_PRICE'),
		"CURRENCY" => GetMessage('SOD_HIST_CURRENCY'),
		"DISCOUNT_VALUE" => GetMessage('SOD_HIST_DISCOUNT_VALUE'),
		"USER_ID" => GetMessage('SOD_HIST_USER_ID'),
		"PAY_SYSTEM_ID" => GetMessage('SOD_HIST_PAY_SYSTEM_ID'),
		"DELIVERY_ID" => GetMessage('SOD_HIST_DELIVERY_ID'),
		"PS_STATUS" => GetMessage('SOD_HIST_PS_STATUS'),
		"PS_STATUS_CODE" => GetMessage('SOD_HIST_PS_STATUS_CODE'),
		"PS_STATUS_DESCRIPTION" => GetMessage('SOD_HIST_PS_STATUS_DESCRIPTION'),
		"PS_STATUS_MESSAGE" => GetMessage('SOD_HIST_PS_STATUS_MESSAGE'),
		"PS_SUM" => GetMessage('SOD_HIST_PS_SUM'),
		"PS_CURRENCY" => GetMessage('SOD_HIST_PS_CURRENCY'),
		"PS_RESPONSE_" => GetMessage('SOD_HIST_PS_RESPONSE_'),
		"TAX_VALUE" => GetMessage('SOD_HIST_TAX_VALUE'),
		"STAT_GID" => GetMessage('SOD_HIST_STAT_GID'),
		"SUM_PAID" => GetMessage('SOD_HIST_SUM_PAID'),
		"RECURRING_ID" => GetMessage('SOD_HIST_RECURRING_ID'),
		"PAY_VOUCHER_NUM" => GetMessage('SOD_HIST_PAY_VOUCHER_NUM'),
		"PAY_VOUCHER_DATE" => GetMessage('SOD_HIST_PAY_VOUCHER_DATE'),
		"RECOUNT_FLAG" => GetMessage('SOD_HIST_RECOUNT_FLAG'),
		"AFFILIATE_ID" => GetMessage('SOD_HIST_AFFILIATE_ID'),
		"DELIVERY_DOC_NUM" => GetMessage('SOD_HIST_DELIVERY_DOC_NUM'),
		"DELIVERY_DOC_DATE" => GetMessage('SOD_HIST_DELIVERY_DOC_DATE')
	);

//get status order
$arOrderStatus = array();
$dbStatusList = CSaleStatus::GetList(
	array("SORT" => "ASC"),
	array("LID" => LANGUAGE_ID),
	false,
	false,
	array("ID", "NAME")
);
while ($arStatusList = $dbStatusList->Fetch())
	$arOrderStatus[htmlspecialcharsbx($arStatusList["ID"])] = htmlspecialcharsbx($arStatusList["NAME"]);

//get delivery
$arDelivery = array();
$dbDeliveryList = CSaleDelivery::GetList(
		array("SORT" => "ASC"),
		array()
		);
while ($arDeliveryList = $dbDeliveryList->Fetch())
	$arDelivery[$arDeliveryList["ID"]] = htmlspecialcharsbx($arDeliveryList["NAME"]);

//get paysystem
$arPaySystem = array();
$dbPaySystemList = CSalePaySystem::GetList(
		array("SORT"=>"ASC"),
		array()
		);
while ($arPaySystemList = $dbPaySystemList->Fetch())
	$arPaySystem[$arPaySystemList["ID"]] = htmlspecialcharsbx($arPaySystemList["NAME"]);


$sTableID_tab5 = "table_order_change";
$oSort_tab5 = new CAdminSorting($sTableID_tab5);
$lAdmin_tab5 = new CAdminList($sTableID_tab5, $oSort_tab5);

//FILTER ORDER CHANGE HISTORY
$arFilterFields = array(
	"filter_user",
	"filter_date_history",
	"filter_type"
);
$lAdmin_tab5->InitFilter($arFilterFields);

$by = trim(array_key_exists('by', $_REQUEST) ? $_REQUEST['by'] : '');
if ('' == $by)
	$by = 'DATE_CREATE';
$order = trim(array_key_exists('order', $_REQUEST) ? $_REQUEST['order'] : '');
if ('' == $order)
	$order = 'DESC';
$arHistSort[$by] = $order;
$arHistSort["ID"] = $order;

$arFilterHistory = array("ORDER_ID" => $ID);

if ($filter_type <> '') $arFilterHistory["TYPE"] = trim($filter_type);
if (intval($filter_user)>0) $arFilterHistory["USER_ID"] = intval($filter_user);

if ($filters_date_history_from <> '')
{
	$arFilterHistory["DATE_CREATE_FROM"] = Trim($filters_date_history_from);
}

if ($filters_date_history_to <> '')
{
	if ($arDate = ParseDateTime($filters_date_history_to, CSite::GetDateFormat("FULL", SITE_ID)))
	{
		if (mb_strlen($filters_date_history_to) < 11)
		{
			$arDate["HH"] = 23;
			$arDate["MI"] = 59;
			$arDate["SS"] = 59;
		}

		$filters_date_history_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID)), mktime($arDate["HH"], $arDate["MI"], $arDate["SS"], $arDate["MM"], $arDate["DD"], $arDate["YYYY"]));
		$arFilterHistory["DATE_CREATE_TO"] = $filters_date_history_to;
	}
	else
		$filters_date_history_to = "";
}

$arHistoryData = array();
$bUseOldHistory = false;

// collect records from old history to show in the new order changes list
$dbHistory = CSaleOrder::GetHistoryList(
	array("H_DATE_INSERT" => "DESC"),
	array("H_ORDER_ID" => $ID),
	false,
	false,
	array("*")
);

while ($arHistory = $dbHistory->Fetch())
{
	$res = convertHistoryToNewFormat($arHistory);

	if ($res)
	{
		$arHistoryData[] = $res;
		$bUseOldHistory = true;
	}
}

// new order history data
$dbOrderChange = CSaleOrderChange::GetList(
	$arHistSort,
	$arFilterHistory,
	false,
	false,
	array("*")
);

while ($arChangeRecord = $dbOrderChange->Fetch())
	$arHistoryData[] = $arChangeRecord;

// advancing sorting is necessary if old history results are mixed with new order changes
if ($bUseOldHistory)
{
	$arData = array();
	foreach ($arHistoryData as $index => $arHistoryRecord)
		$arData[$index]  = $arHistoryRecord[$by];

	$arIds = array();
	foreach ($arHistoryData as $index => $arHistoryRecord)
		$arIds[$index]  = $arHistoryRecord["ID"];

	array_multisort($arData, constant("SORT_".mb_strtoupper($order)), $arIds, constant("SORT_".mb_strtoupper($order)), $arHistoryData);
}

$dbRes = new CDBResult;
$dbRes->InitFromArray($arHistoryData);

$dbRecords = new CAdminResult($dbRes, $sTableID_tab5);
$dbRecords->NavStart();
$lAdmin_tab5->NavText($dbRecords->GetNavPrint(GetMessage('SOD_HIST_LIST')));

$histdHeader = array(
	array("id"=>"DATE_CREATE", "content"=>GetMessage("SOD_HIST_H_DATE"), "sort"=>"DATE_CREATE", "default"=>true),
	array("id"=>"USER_ID", "content"=>GetMessage("SOD_HIST_H_USER"), "sort"=>"USER_ID", "default"=>true),
	array("id"=>"TYPE", "content"=>GetMessage("SOD_HIST_TYPE"), "sort"=>"TYPE", "default"=>true),
	array("id"=>"DATA", "content"=>GetMessage("SOD_HIST_DATA"), "sort"=>"", "default"=>true),
);

$lAdmin_tab5->AddHeaders($histdHeader);

$arOperations = array();
while ($arChangeRecord = $dbRecords->Fetch())
{
	$row =& $lAdmin_tab5->AddRow($arChangeRecord["ID"], $arChangeRecord, '', '');

	$stmp = MakeTimeStamp($arChangeRecord["DATE_CREATE"], "DD.MM.YYYY HH:MI:SS");

	$row->AddField("DATE_CREATE", date("d.m.Y H:i", $stmp));
	$row->AddField("USER_ID", GetFormatedUserName($arChangeRecord["USER_ID"], false));

	$arRecord = CSaleOrderChange::GetRecordDescription($arChangeRecord["TYPE"], $arChangeRecord["DATA"]);

	$row->AddField("TYPE", $arRecord["NAME"]);
	$row->AddField("DATA", htmlspecialcharsbx($arRecord["INFO"]));

	$arOperations[$arChangeRecord["TYPE"]] = $arRecord["NAME"];
}

if($_REQUEST["table_id"]==$sTableID_tab5)
	$lAdmin_tab5->CheckListMode();

//end get history order list

$aTabs = array();
$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("SODN_TAB_ORDER"), "TITLE" => GetMessage("SODN_TAB_ORDER_DESCR"), "ICON" => "sale");
$aTabs[] = array("DIV" => "edit3", "TAB" => GetMessage("SODN_TAB_TRANSACT"), "TITLE" => GetMessage("SODN_TAB_TRANSACT_DESCR"), "ICON" => "sale");
$aTabs[] = array("DIV" => "edit4", "TAB" => GetMessage("SODN_TAB_HISTORY"), "TITLE" => GetMessage("SODN_TAB_HISTORY_DESCR"), "ICON" => "sale");

$tabControl = new CAdminForm("order_view_info", $aTabs, true, true);
$tabControl->SetShowSettings(false);

$tabControl->AddTabs($customTabber);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT" => GetMessage("SOD_TO_LIST"),
		"LINK" => "/bitrix/admin/sale_order_detail.php?ID=".$ID."&dontsave=Y&lang=".LANGUAGE_ID.GetFilterParams("filter_"),
		"ICON"=>"btn_list",
	)
);

if ($boolLocked && $saleModulePermissions >= 'W')
{
	$aMenu[] = array(
		"TEXT" => GetMessage("SOD_TO_UNLOCK"),
		"LINK" => "/bitrix/admin/sale_order_detail.php?ID=".$ID."&unlock=Y&lang=".LANGUAGE_ID.GetFilterParams("filter_"),
	);
}

if ($bUserCanEditOrder)
{
	if (!$boolLocked)
	{
		$aMenu[] = array(
			"TEXT" => GetMessage("SOD_TO_EDIT"),
			"LINK" => "/bitrix/admin/sale_order_edit.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_"),
			"ICON"=>"btn_edit",
		);
	}
	$aMenu[] = array(
		"TEXT" => GetMessage("SOD_TO_NEW_ORDER"),
		"LINK" => "/bitrix/admin/sale_order_edit.php?lang=".LANGUAGE_ID."&SITE_ID=".$arOrder["LID"],
		"ICON"=>"btn_edit",
	);
}

$aMenu[] = array(
	"TEXT" => GetMessage("SOD_TO_PRINT"),
	"LINK" => "/bitrix/admin/sale_order_print.php?ID=".$ID."&lang=".LANGUAGE_ID.GetFilterParams("filter_"),
);

if (!$boolLocked && ($saleModulePermissions == "W" || $arOrder["PAYED"] != "Y" && $bUserCanDeleteOrder))
{
	$aMenu[] = array(
		"TEXT" => GetMessage("SODN_CONFIRM_DEL"),
		"LINK" => "javascript:if(confirm('".GetMessageJS("SODN_CONFIRM_DEL_MESSAGE")."')) window.location='sale_order.php?ID=".$ID."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get().urlencode(GetFilterParams("filter_"))."'",
		"WARNING" => "Y",
		"ICON"=>"btn_delete",
	);
}

$link = DeleteParam(array("mode"));
$link = $APPLICATION->GetCurPage()."?mode=settings".($link <> ""? "&".$link:"");

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($boolLocked)
{
	CAdminMessage::ShowMessage(array(
		'MESSAGE' => $strLockUserInfoExt,
		'TYPE' => 'ERROR',
		'HTML' => true
	));
}

CAdminMessage::ShowMessage($errorMessage);

if ($save_order_result <> '')
{
	$okMessage = "";

	if ($save_order_result == "ok_status")
		$okMessage = GetMessage("SOD_OK_STATUS");
	elseif ($save_order_result == "ok_cancel")
		$okMessage = GetMessage("SOD_OK_CANCEL");
	elseif ($save_order_result == "ok_pay")
		$okMessage = GetMessage("SOD_OK_PAY");
	elseif ($save_order_result == "ok_delivery")
		$okMessage = GetMessage("SOD_OK_DELIVERY");
	elseif ($save_order_result == "ok_comment")
		$okMessage = GetMessage("SOD_OK_COMMENT");
	elseif ($save_order_result == "ok_ps")
		$okMessage = GetMessage("SOD_OK_PS");
	else
		$okMessage = GetMessage("SOD_OK_OK");

	CAdminMessage::ShowNote($okMessage);
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
	$note .= GetMessage('SOD_ERROR_SEVERAL_P_D');
	$note .= EndNote();
	echo $note;
}

if (!$bUserCanViewOrder)
{
	CAdminMessage::ShowMessage(str_replace("#ID#", $ID, GetMessage("SOD_NO_PERMS2VIEW")).". ");
}
else
{
	if (!$boolLocked)
		CSaleOrder::Lock($ID);

	$customOrderView = COption::GetOptionString("sale", "path2custom_view_order", "");
	if ($customOrderView <> ''
		&& file_exists($_SERVER["DOCUMENT_ROOT"].$customOrderView)
		&& is_file($_SERVER["DOCUMENT_ROOT"].$customOrderView))
	{
		include($_SERVER["DOCUMENT_ROOT"].$customOrderView);
	}
	else
	{
		$arBasketId = array();
		$arBasketItems = array();
		$arBasketPropsValues = array();
		$arElementId = array();
		$arSku2Parent = array();
		$orderBasketPrice = 0;
		$orderTotalPrice = 0;
		$orderTotalWeight = 0;

		$parentItemFound = false;

		$dbBasketTmp = CSaleBasket::GetList(
			array("ID" => "ASC"),
			array("ORDER_ID" => $ID),
			false,
			false,
			array(
				"ID", "PRODUCT_ID", "PRODUCT_PRICE_ID", "PRICE", "CURRENCY", "WEIGHT",
				"QUANTITY", "NAME", "MODULE", "CALLBACK_FUNC", "NOTES", "DETAIL_PAGE_URL", "DISCOUNT_PRICE",
				"DISCOUNT_VALUE", "ORDER_CALLBACK_FUNC", "CANCEL_CALLBACK_FUNC", "PAY_CALLBACK_FUNC", "CATALOG_XML_ID",
				"PRODUCT_XML_ID", "VAT_RATE", "DISCOUNT_NAME", "DISCOUNT_COUPON", "PRODUCT_PROVIDER_CLASS", "CUSTOM_PRICE",
				"TYPE", "SET_PARENT_ID", "DIMENSIONS", "RECOMMENDATION"
			)
		);
		while ($arBasketTmp = $dbBasketTmp->GetNext())
		{
			$arBasketId[] = $arBasketTmp["ID"];
			$arBasketTmp["DIMENSIONS"] = unserialize($arBasketTmp["~DIMENSIONS"], ['allowed_classes' => false]);

			$arBasketItems[] = $arBasketTmp;

			if (CModule::IncludeModule("catalog"))
			{
				$arParent = CCatalogSku::GetProductInfo($arBasketTmp["PRODUCT_ID"]);
				if ($arParent)
				{
					$arElementId[] = $arParent["ID"];
					$arSku2Parent[$arBasketTmp["PRODUCT_ID"]] = $arParent["ID"];
				}
			}

			$arElementId[] = $arBasketTmp["PRODUCT_ID"];
			$arBasketPropsValues[$arBasketTmp["PRODUCT_ID"]] = array();

			if (!CSaleBasketHelper::isSetItem($arBasketTmp))
			{
				$orderTotalPrice += ($arBasketTmp["PRICE"] + $arBasketTmp["DISCOUNT_PRICE"]) * $arBasketTmp["QUANTITY"];
				$orderBasketPrice += $arBasketTmp["PRICE"] * $arBasketTmp["QUANTITY"];
			}

				if (!CSaleBasketHelper::isSetParent($arBasketTmp))
				{
					$orderTotalWeight += floatval($arBasketTmp["WEIGHT"] * $arBasketTmp["QUANTITY"]);
				}

			if (CSaleBasketHelper::isSetParent($arBasketTmp) || CSaleBasketHelper::isSetItem($arBasketTmp))
			{
				$parentItemFound = true;
			}
		}

		if ($parentItemFound === true && !empty($arBasketItems) && is_array($arBasketItems))
		{
			$arBasketItems = CSaleBasketHelper::reSortItems($arBasketItems);
		}
?>

<style type="text/css">
	.bx-adm-bigdata-icon-medium-inner{
		position: relative;
		top: -5px;
		display: block;
		width:24px;
		height: 24px;
		background: url('/bitrix/panel/main/images/icons-sprite-7.png') no-repeat center -2176px;
		float: left;
		margin-right: 10px;
	}
</style>

<?
		$tabControl->BeginEpilogContent();
		?>
		<?= GetFilterHiddens("filter_"); ?>
		<?= bitrix_sessid_post(); ?>
		<input type="hidden" name="lang" value="<? echo LANGUAGE_ID; ?>">
		<input type="hidden" name="ID" value="<? echo $ID; ?>">
		<input type="hidden" name="save_order_data" value="Y">
		<?
		$tabControl->EndEpilogContent();

		$tabControl->Begin();

		$tabControl->BeginNextFormTab();

			$tabControl->AddSection("order_id", GetMessage("P_ORDER_ID"));
				$tabControl->BeginCustomField("ORDER_ACCOUNT_NUMBER", GetMessage("SOD_ORDER_ACCOUNT_NUMBER"));
					?>
					<tr>
						<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td width="60%"><?echo $arOrder["ACCOUNT_NUMBER"]?></td>
					</tr>
					<?
				$tabControl->EndCustomField("ORDER_ACCOUNT_NUMBER", '');
				$tabControl->BeginCustomField("ORDER_DATE_CREATE", GetMessage("SOD_ORDER_DATE_CREATE"));
					?>
					<tr>
						<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td width="60%"><?echo $arOrder["DATE_INSERT"]?></td>
					</tr>
					<?
				$tabControl->EndCustomField("ORDER_DATE_CREATE", '');

				$tabControl->BeginCustomField("DATE_UPDATE", GetMessage("SOD_DATE_UPDATE"));
					?>
					<tr>
						<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td width="60%"><?echo $arOrder["DATE_UPDATE"]?></td>
					</tr>
					<?
				$tabControl->EndCustomField("DATE_UPDATE", '');

				$arSitesShop = array();
				$rsSites = CSite::GetList("id", "asc", Array("ACTIVE" => "Y"));
				while ($arSite = $rsSites->Fetch())
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
						<td width="60%"><?=htmlspecialcharsbx($arSitesShop[$arOrder["LID"]]["NAME"])." (".$arOrder["LID"].")"?>
						</td>
					</tr>
					<?
					$tabControl->EndCustomField("ORDER_SITE");
				}

				$tabControl->BeginCustomField("ORDER_STATUS", GetMessage("SOD_CUR_STATUS"));
					?>
					<tr>
						<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td width="60%">
									<?
									$arStatusList = False;
									$arFilter = array("LID" => LANG);
									$arGroupByTmp = false;
									if ($saleModulePermissions < "W")
									{
										$arFilter["GROUP_ID"] = $arUserGroups;
										$arFilter["PERM_STATUS_FROM"] = "Y";
										$arFilter["ID"] = $arOrder["STATUS_ID"];
										$arGroupByTmp = array("ID", "NAME", "MAX" => "PERM_STATUS_FROM");
									}
									$dbStatusList = CSaleStatus::GetList(
											array(),
											$arFilter,
											$arGroupByTmp,
											false,
											array("ID", "NAME", "PERM_STATUS_FROM")
										);
									$arStatusList = $dbStatusList->GetNext();

									$statusOrder = "";

									?>
									<div id="editStatusDIV">
										<select name="STATUS_ID" id="STATUS_ID" <? echo (!$boolLocked ? 'onchange="BX(\'change_status\').value=\'Y\';"': ''); ?>>
											<?
											if ($arStatusList)
											{
												$arFilter = array("LID" => LANG);
												$arGroupByTmp = false;
												if ($saleModulePermissions < "W")
												{
													$arFilter["GROUP_ID"] = $arUserGroups;
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
													$select = "";
													if ($arStatusListTmp["ID"]==$arOrder["STATUS_ID"])
														$select = " selected";

													$statusOrder .= '<option value="'.$arStatusListTmp["ID"].'" '.$select.'>'.$arStatusListTmp["NAME"].'</option>';
												}
											}

											echo $statusOrder;
										?>
										</select>
										<input type="hidden" name="change_status" id="change_status" value="N">
										<input type="hidden" name="change_status_popup" id="change_status_popup" value="N"><?
										if (!$boolLocked)
										{
											?><a href="javascript:void(0);" onclick="fChangeStatus();return false;" class="adm-btn"><?=GetMessage('SALE_SAVE');?></a><?
										}
										else
										{
											?><a href="javascript:void(0);" onclick="return false;" class="adm-btn-disabled" title="<? echo $strLockUserExt; ?>"><?=GetMessage('SALE_SAVE');?></a><?
										}
										?>
										&nbsp;<span id="change_status_err" style="display: none;"></span>
										<script>
											function fChangeStatus()
											{
												var obStatusErr = BX('change_status_err');
												if (!!obStatusErr)
												{
													obStatusErr.innerHTML = '';
													obStatusErr.style.display = 'none';
												}
												BX.showWait();
												BX.ajax.post('/bitrix/admin/sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&save_order_data=Y&change_status=Y&STATUS_ID='+BX('STATUS_ID').value+'&ID=<?=$ID?>', fChangeStatusResult);

												return false;
											}

											function fChangeStatusResult(res)
											{
												var rs = eval( '('+res+')' );
												BX.closeWait();

												if (!!rs.STATUS_ERR && true == rs.STATUS_ERR)
												{
													var obStatusErr = BX('change_status_err');
													if (!!obStatusErr)
													{
														obStatusErr.innerHTML = rs.STATUS_ERR_MESS;
														obStatusErr.style.display = 'inline-block';
													}
												}
												else
												{
													if (BX('date_status_change') && rs['DATE_STATUS'] && rs['DATE_STATUS'].length > 0)
														BX('date_status_change').innerHTML = rs['DATE_STATUS'] + ' ' + rs['EMP_STATUS_ID'];
												}

												BX('change_status').value = 'N';
											}
										</script>
									</div>
						</td>
					</tr>

					<?if($arOrder["DATE_STATUS"] <> ''):?>
						<tr>
							<td><?=GetMessage('SOD_DATE_STATUS');?>:</td>
							<td id="date_status_change"><?=$arOrder["DATE_STATUS"]?>
								<?if (!$crmMode && intval($arOrder["EMP_STATUS_ID"]) > 0)
									echo GetFormatedUserName($arOrder["EMP_STATUS_ID"], false);
								?>
							</td>
						</tr>
					<?endif;?>
					<?
				$tabControl->EndCustomField("ORDER_STATUS", '');

				$tabControl->BeginCustomField("ORDER_CANCELED", GetMessage("SOD_CANCEL_Y"));
					?>
					<tr id="btn_show_cancel" style="display:<?=($arOrder["CANCELED"] == "N" && $bUserCanCancelOrder) ? 'table-row' : 'none'?>">
						<td width="40%">&nbsp;</td>
						<td valign="middle"><?
						if (!$boolLocked)
						{
							?><a title="<?=GetMessage('SOD_CANCEL_Y')?>" onclick="fShowCancelOrder(this, '');" class="adm-btn-wrap" href="javascript:void(0);"><span class="adm-btn"><?=GetMessage('SOD_CANCEL_Y')?></span></a><?
						}
						else
						{
							?><a title="<? echo $strLockUserExt; ?>" class="adm-btn-wrap" href="javascript:void(0);"><span class="adm-btn-disabled"><?=GetMessage('SOD_CANCEL_Y')?></span></a><?
						}
						?></td>
					</tr>
					<tr id="user_can_cancel" style="display:<?=($arOrder["CANCELED"] == "N" && !$bUserCanCancelOrder) ? 'table-row' : 'none'?>">
						<td width="40%">
							<?=GetMessage("SOD_CANCELED")?>
						</td>
						<td valign="middle">
							<?=GetMessage("SALE_NO")?>
						</td>
					</tr>
					<tr id="btn_cancel_cancel" style="display:<?=($arOrder["CANCELED"] != "N") ? 'table-row' : 'none'?>">
						<td>
							<span class="order_cancel_left"><?=GetMessage("SOD_CANCELED")?>:</span>
						</td>
						<td valign="top">
							<span class="order_cancel_right"><?=GetMessage("SALE_YES")?></span><?
							if($bUserCanCancelOrder)
							{
								?>&nbsp;&nbsp;<?
								if (!$boolLocked)
								{
									?><a href="javascript:void(0);" onclick="fCancelCancelOrder();" class="adm-btn-wrap"><span class="adm-btn"><?=GetMessage('SOD_CANCEL_N');?></span></a><?
								}
								else
								{
									?><a href="javascript:void(0);" class="adm-btn-wrap"><span class="adm-btn-disabled" title="<? echo $strLockUserExt; ?>"><?=GetMessage('SOD_CANCEL_N');?></span></a><?
								}
							}
							?>
						</td>
					</tr>
					<tr id="date_change_cancel" style="display:<?=($arOrder["DATE_CANCELED"] <> '') ? 'table-row' : 'none'?>">
						<td>
							<?=GetMessage('SOD_DATE_CANCELED');?>:
						</td>
						<td id="date_change_cancel_user">
							<?=$arOrder["DATE_CANCELED"]?>
							<?if (!$crmMode && intval($arOrder["EMP_CANCELED_ID"]) > 0)
								echo GetFormatedUserName($arOrder["EMP_CANCELED_ID"], false);
							?>
						</td>
					</tr>
					<tr id="reason_cancel" style="display:<?=($arOrder["REASON_CANCELED"] <> '') ? 'table-row' : 'none'?>">
						<td>
							<?=GetMessage('SOD_CANCEL_REASON_TITLE')?>:
						</td>
						<td id="reason_cancel_text">
							<?=htmlspecialcharsbx($arOrder["REASON_CANCELED"])?>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<div id="popup_cancel_order_form" class="sale_popup_form" style="display:none; font-size:13px;">
								<table>
									<tr>
										<td colspan="2"><?=GetMessage('SOD_CANCEL_REASON_TITLE')?><br />
											<?if(CSaleYMHandler::isOrderFromYandex($ID)):
												$reasonDisp = 'style="display: none;" ';
											?>
												<?=CSaleYMHandler::getCancelReasonsAsRadio("FORM_REASON_CANCELED", "FORM_REASON_CANCELED", false)?>
											<?else:
												$reasonDisp = "";
											?>
											<?endif;?>
											<textarea <?=$reasonDisp?>name="FORM_REASON_CANCELED" id="FORM_REASON_CANCELED" rows="3" cols="30"><?= htmlspecialcharsEx($arOrder["REASON_CANCELED"]) ?></textarea>
											<br /><small><?=GetMessage('SOD_CANCEL_REASON_ADIT')?></small>
										</td>
									</tr>
								</table>
							</div>
							<script>
								function fCancelCancelOrder()
								{
									BX.showWait();
									BX.ajax.post('/bitrix/admin/sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&save_order_data=Y&'+'&change_cancel=Y&CANCELED=N&ID=<?=$ID?>', fCancelCancelOrderResult);
								}

								function fCancelCancelOrderResult(res)
								{
									var rs = eval( '('+res+')' );
									BX.closeWait();
									if (rs["message"] == "ok")
									{
										BX('btn_cancel_cancel').style.display = "none";
										BX('btn_show_cancel').style.display = "table-row";
										BX('reason_cancel').style.display = "none";

										if (rs["DATE_CANCELED"].length > 0)
											BX('date_change_cancel_user').innerHTML = rs["DATE_CANCELED"] + ' ' + rs["EMP_CANCELED_ID"];

										BX('date_change_cancel').style.display = "table-row";
									}
								}

								function fChangeCancelResult(res)
								{
									var rs = eval( '('+res+')' );
									BX.closeWait();
									if (rs["message"] == "ok")
									{
										var emp_cancel_user = '';

										BX('btn_show_cancel').style.display = "none";
										BX('btn_cancel_cancel').style.display = "table-row";

										if (rs["DATE_CANCELED"] && rs["DATE_CANCELED"].length > 0)
											emp_cancel_user = rs["DATE_CANCELED"];

										if (rs["EMP_CANCELED_ID"] && rs["EMP_CANCELED_ID"].length > 0)
											emp_cancel_user += ' ' + rs["EMP_CANCELED_ID"];

										if (BX('date_change_cancel_user') && emp_cancel_user.length > 0)
											BX('date_change_cancel_user').innerHTML = emp_cancel_user;

										BX('date_change_cancel').style.display = "table-row";
										BX('reason_cancel_text').innerHTML = BX('FORM_REASON_CANCELED').value;
										BX('reason_cancel').style.display = "table-row";
									}

									fCheckReservationResult(rs);
								}

								function fShowCancelOrder(el, type)
								{
									formCancelOrder = BX.PopupWindowManager.create("sale-popup-cancel", el, {
										offsetTop : -100,
										offsetLeft : -150,
										autoHide : true,
										closeByEsc : true,
										closeIcon : true,
										titleBar : true,
										draggable: {restrict:true},
										titleBar: {content: BX.create("span", {html: '<? echo GetMessageJS("SOD_CANCEL_ORDER"); ?>', 'props': {'className': 'sale-popup-title-bar'}})},
										content : BX("popup_cancel_order_form")
									});
									formCancelOrder.setButtons([
										new BX.PopupWindowButton({
											text : "<?=GetMessage('SOD_POPUP_SAVE')?>",
											className : "",
											events : {
												click : function()
												{
													BX.showWait();
													BX.ajax.post('/bitrix/admin/sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&save_order_data=Y&'+'&change_cancel=Y&CANCELED=Y&REASON_CANCELED='+BX('FORM_REASON_CANCELED').value+'&ID=<?=$ID?>', fChangeCancelResult);
													formCancelOrder.close();
												}
											}
										}),
										new BX.PopupWindowButton({
											text : "<?=GetMessage('SOD_POPUP_CANCEL')?>",
											className : "",
											events : {
												click : function()
												{
													BX('FORM_REASON_CANCELED').value = '';
													formCancelOrder.close();
												}
											}
										})
									]);

									formCancelOrder.show();
									BX('FORM_REASON_CANCELED').focus();
								}
							</script>
						</td>
					</tr>
					<?
				$tabControl->EndCustomField("ORDER_CANCELED", '');

				$tabControl->BeginCustomField("ORDER_AFFILIATE", GetMessage("P_ORDER_AFFILIATE"));
					if (intval($arOrder["AFFILIATE_ID"]) > 0):
					?>
					<tr>
						<td width="40%"><?echo GetMessage("P_ORDER_AFFILIATE")?>:</td>
						<td width="60%">
						<?
							$dbAffiliate = CSaleAffiliate::GetList(
								array(),
								array("ID" => $arOrder["AFFILIATE_ID"]),
								false,
								false,
								array("ID", "SITE_ID", "USER_ID", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME")
							);
							if ($arAffiliate = $dbAffiliate->Fetch())
								echo '<a href="sale_affiliate_edit.php?ID='.intval($arOrder["AFFILIATE_ID"]).'&lang='.LANGUAGE_ID.'">'.$arAffiliate["USER_NAME"].' '.$arAffiliate["USER_LAST_NAME"].' ('.$arAffiliate["USER_LOGIN"].')</a>';
						?>
						</td>
					</tr>
					<?
					endif;
				$tabControl->EndCustomField("ORDER_AFFILIATE", '');

			$tabControl->AddSection("order_user", GetMessage("P_ORDER_USER_ACC"));

				$tabControl->BeginCustomField("ORDER_PROPS", GetMessage("SOD_ORDER_PROPS"));
					$dbUser = CUser::GetByID($arOrder["USER_ID"]);
					$arUser = $dbUser->Fetch();
				?>
					<tr>
						<td valign="top" width="40%"><?=GetMessage('SOD_BUYER_LOGIN')?>:</td>
						<td valign="middle"><?
						$strBuyerProfileUrl = '';
						if (CBXFeatures::IsFeatureEnabled('SaleAccounts'))
						{
							$strBuyerProfileUrl = '/bitrix/admin/sale_buyers_profile.php?USER_ID='.$arOrder["USER_ID"].'&lang='.LANGUAGE_ID;
						}
						else
						{
							$strBuyerProfileUrl = '/bitrix/admin/user_edit.php?ID='.$arOrder["USER_ID"].'&lang='.LANGUAGE_ID;
						}
						?><a href="<? echo $strBuyerProfileUrl; ?>"><? echo htmlspecialcharsEx($arUser["LOGIN"]); ?></a></td>
					</tr>
					<tr>
						<td valign="top"><?echo GetMessage("P_ORDER_PERS_TYPE")?>:</td>
						<td valign="middle"><?
							$arPersonType = CSalePersonType::GetByID($arOrder["PERSON_TYPE_ID"]);
							echo htmlspecialcharsEx($arPersonType["NAME"]);
							?>
						</td>
					</tr>
					<?

					//disabled town
					$arTownOrderProps = array();
					$dbProperties = CSaleOrderProps::GetList(
						array(),
						array(
							"ORDER_ID" => $ID,
							"PERSON_TYPE_ID" => $arPersonType["ID"],
							"ACTIVE" => "Y",
							">INPUT_FIELD_LOCATION" => 0
						),
						false,
						false,
						array("INPUT_FIELD_LOCATION")
					);
					while ($arProperties = $dbProperties->Fetch())
						$arTownOrderProps[$arProperties["INPUT_FIELD_LOCATION"]] = $arProperties["INPUT_FIELD_LOCATION"];

					$arEnableTownProps = array();
					$arOrderPropsValue = array();
					$dbOrderProps = CSaleOrderPropsValue::GetOrderProps($ID);
					while ($arOrderProps = $dbOrderProps->Fetch())
					{
						$arOrderPropsValue[] = $arOrderProps;
						if ($arOrderProps["TYPE"] == "LOCATION" && $arOrderProps["ACTIVE"] == "Y" && $arOrderProps["IS_LOCATION"] == "Y" && in_array($arOrderProps["INPUT_FIELD_LOCATION"], $arTownOrderProps))
						{
							if(CSaleLocation::isLocationProMigrated())
							{
								$arEnableTownProps[$arOrderProps["INPUT_FIELD_LOCATION"]] = true; //CSaleLocation::checkLocationIsAboveCity($arOrderProps["VALUE"]);
							}
							else
							{
								$arLocation = CSaleLocation::GetByID($arOrderProps["VALUE"]);
								if (intval($arLocation["CITY_ID"]) <= 0)
									$arEnableTownProps[$arOrderProps["INPUT_FIELD_LOCATION"]] = true;
								else
									$arEnableTownProps[$arOrderProps["INPUT_FIELD_LOCATION"]] = false;
							}
						}
					}

					$iGroup = -1;
					$locationData = 0;
					$locationZip = 0;
					foreach ($arOrderPropsValue as $arOrderProps)
					{
						if ($iGroup != intval($arOrderProps["PROPS_GROUP_ID"]))
						{
							?>
							<tr>
								<td colspan="2" style="text-align:center;font-weight:bold;font-size:14px;color:rgb(75, 98, 103);"><?=htmlspecialcharsEx($arOrderProps["GROUP_NAME"]);?></td>
							</tr>
							<?
							$iGroup = intval($arOrderProps["PROPS_GROUP_ID"]);
						}

						if (!in_array($arOrderProps["ORDER_PROPS_ID"], $arTownOrderProps) || (in_array($arOrderProps["ORDER_PROPS_ID"], $arTownOrderProps)
											&& isset($arEnableTownProps[$arOrderProps["ORDER_PROPS_ID"]])
											&& $arEnableTownProps[$arOrderProps["ORDER_PROPS_ID"]]))
						{
						?>
						<tr>
							<td valign="top"><?echo htmlspecialcharsEx($arOrderProps["NAME"])?>:</td>
							<td valign="middle">
							<?
							if ($arOrderProps["TYPE"] == "CHECKBOX")
							{
								if ($arOrderProps["VALUE"] == "Y")
									echo GetMessage("SALE_YES");
								else
									echo GetMessage("SALE_NO");
							}
							elseif ($arOrderProps["TYPE"] == "TEXT" || $arOrderProps["TYPE"] == "TEXTAREA")
							{
								if ($arOrderProps["CODE"] == 'phone' &&
									$arOrderProps["IS_EMAIL"] == "N" &&
									$arOrderProps["IS_PAYER"] == "N" &&
									$arOrderProps["IS_PROFILE_NAME"] == "N")
								{
									echo '<a href="callto:'.htmlspecialcharsbx($arOrderProps["VALUE"]).'">'.htmlspecialcharsEx($arOrderProps["VALUE"]).'</a>';
								}
								elseif ($arOrderProps["IS_EMAIL"] == "Y")
									echo '<a href="mailto:'.htmlspecialcharsbx($arOrderProps["VALUE"]).'">'.htmlspecialcharsEx($arOrderProps["VALUE"]).'</a>';
								else
									echo nl2br(htmlspecialcharsbx(trim($arOrderProps["VALUE"])));
							}
							elseif ($arOrderProps["TYPE"] == "SELECT" || $arOrderProps["TYPE"] == "RADIO")
							{
								$arVal = CSaleOrderPropsVariant::GetByValue($arOrderProps["ORDER_PROPS_ID"], $arOrderProps["VALUE"]);
								echo htmlspecialcharsEx($arVal["NAME"]);
							}
							elseif ($arOrderProps["TYPE"] == "MULTISELECT")
							{
								$curVal = explode(",", $arOrderProps["VALUE"]);
								$countCurVal = count($curVal);
								for ($i = 0; $i < $countCurVal; $i++)
								{
									$arVal = CSaleOrderPropsVariant::GetByValue($arOrderProps["ORDER_PROPS_ID"], $curVal[$i]);
									if ($i > 0)
										echo ", ";
									echo htmlspecialcharsEx($arVal["NAME"]);
								}
							}
							elseif ($arOrderProps["TYPE"] == "LOCATION")
							{
								$arOrder["LOCATION_TO"] = $arOrderProps["VALUE"];

								if(CSaleLocation::isLocationProEnabled())
								{
									$locationString = \Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringById($arOrderProps['VALUE']);
									if($locationString == '')
										$locationString = $arOrderProps['VALUE'];

									print(htmlspecialcharsEx($locationString));
								}
								else
								{
									$arVal = CSaleLocation::GetByID($arOrderProps["VALUE"], LANG);
									$locationString = $arVal["COUNTRY_NAME"];

									if ($arVal["REGION_NAME"] <> '' && $locationString <> '')
										$locationString .= " - ".$arVal["REGION_NAME"];
									elseif ($locationString == '' && $arVal["REGION_NAME"] <> '')
										$locationString = $arVal["REGION_NAME"];

									if ($locationString <> '' && $arVal["CITY_NAME"] <> '')
										$locationString .= " - ".$arVal["CITY_NAME"];
									elseif ($locationString == ''  && $arVal["CITY_NAME"] <> '')
										$locationString = $arVal["CITY_NAME"];

									echo htmlspecialcharsEx($locationString);
								}

								$locationData = $arOrderProps["VALUE"];

								$rsZipList = CSaleLocation::GetLocationZIP($locationData);
								if ($arZip = $rsZipList->Fetch())
								{
									if ($arZip["ZIP"] <> '')
										$locationZip = $arZip["ZIP"];
								}
							}
							elseif ($arOrderProps["TYPE"] == "FILE")
							{
								$arValues = unserialize($arOrderProps["VALUE"], ['allowed_classes' => false]);
								if (is_array($arValues))
								{
									foreach ($arValues as $fileId)
									{
										echo showImageOrDownloadLink(trim($fileId), $ID);
										echo "<br/>";
									}
								}
								else
								{
									echo showImageOrDownloadLink($arOrderProps["VALUE"], $ID);
								}
							}
							else
							{
								echo htmlspecialcharsEx($arOrderProps["VALUE"]);
							}
							?>
							</td>
						</tr>
					<?
						}
					}
				$tabControl->EndCustomField("ORDER_PROPS", '');

			$tabControl->AddSection("order_delivery", GetMessage("P_ORDER_DELIVERY_TITLE"));

				$tabControl->BeginCustomField("ORDER_DELIVERY", GetMessage("P_ORDER_DELIVERY"));
					?>
					<tr>
						<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td>
							<span id="allow_delivery_name">
								<?
								$arDeliveryName = array();
								$arDeliveryData = array();
								if (mb_strpos($arOrder["DELIVERY_ID"], ":") !== false)
								{
									$arDeliveryName = explode(":", $arOrder["DELIVERY_ID"]);

									$dbDelivery = CSaleDeliveryHandler::GetBySID($arDeliveryName[0]);
									$arDeliveryData = $dbDelivery->Fetch();

									echo "[".$arDeliveryData["SID"]."] ".htmlspecialcharsEx($arDeliveryData["NAME"])." (".$arOrder["LID"].")";
								}
								elseif (intval($arOrder["DELIVERY_ID"]) > 0)
								{
									$arDeliveryData = CSaleDelivery::GetByID($arOrder["DELIVERY_ID"]);
									echo htmlspecialcharsbx($arDeliveryData["NAME"]);
								}
								else
									echo GetMessage("SOD_NONE");
								?>
							</span>
						</td>
					</tr>
					<?
					if (!empty($arDeliveryName)):
					?>
					<tr>
						<td><?=GetMessage("SOD_DELIVERY_SERVICE_NAME")?>:</td>
						<td><? echo "[".htmlspecialcharsEx($arDeliveryName[1])."] ".htmlspecialcharsEx($arDeliveryData["PROFILES"][$arDeliveryName[1]]["TITLE"]); ?></td>
					</tr>
					<?
					$arDeliveryExtraParams = CSaleDeliveryHandler::GetHandlerExtraParams($arDeliveryData["SID"], $arDeliveryName[1], $arOrder);
					$depList = \Bitrix\Sale\Internals\OrderDeliveryReqTable::getList(array(
						'filter'=>array('=ORDER_ID'=>$ID),
					));
					if($dep = $depList->fetch())
					{
						$depParams = unserialize($dep["PARAMS"], ['allowed_classes' => false]);

						foreach($arDeliveryExtraParams as $paramId => $paramOptions)
						{
							if(isset($depParams[$paramId]))
							{
								if(isset($paramOptions["VALUES"]) && isset($paramOptions["VALUES"][$depParams[$paramId]]))
								{
									$value =  $paramOptions["VALUES"][$depParams[$paramId]];
								}
								else
								{
									$value = $depParams[$paramId];
								}
							}
							else
							{
								$value = "";
							}
							?>
							<tr>
								<td><?=$arDeliveryExtraParams[$paramId]["TITLE"].":"?></td>
								<td><?=htmlspecialcharsbx($value)?></td>
							</tr>
							<?
						}
					}

					endif;
				$tabControl->EndCustomField("ORDER_DELIVERY", '');

				$tabControl->BeginCustomField("STORE_DELIVERY", GetMessage("SOD_STORE_SEND"));
				if (intval($arOrder["STORE_ID"]) > 0):
				?>
					<tr>
						<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td>
							<?
							$dbList = CCatalogStore::GetList(
								array("SORT" => "DESC", "ID" => "DESC"),
								array("ACTIVE" => "Y", "ID" => $arOrder["STORE_ID"]),
								false,
								false,
								array("ID", "TITLE", "ADDRESS", "DESCRIPTION", "IMAGE_ID", "PHONE", "SCHEDULE", "LOCATION_ID", "GPS_N", "GPS_S")
							);
							if ($arList = $dbList->Fetch()):
							?>
								<div><?=htmlspecialcharsbx($arList["TITLE"])?></div>
							<?else:?>
								<div<?=GetMessage('SOD_STORE_SEND_NULL');?>></div>
							<?endif;?>
						</td>
					</tr>
				<?
				endif;
				$tabControl->EndCustomField("STORE_DELIVERY", '');

				$tabControl->BeginCustomField("ORDER_ALLOW_DELIVERY", GetMessage("P_ORDER_ALLOW_DELIVERY"));
				?>
					<tr id="btn_allow_delivery" style="display:<?=($arOrder["ALLOW_DELIVERY"] == "N" && $bUserCanDeliverOrder) ? 'table-row' : 'none'?>">
						<td width="40%">&nbsp;</td>
						<td valign="middle" class="btn_order"><?
						if (!$boolLocked)
						{
							?><a title="<?=GetMessage('SOD_ALLOW_DELIVERY_DO_Y')?>" onclick="fShowAllowDelivery(this, '');" class="adm-btn adm-btn-green" href="javascript:void(0);"><?=GetMessage('SOD_ALLOW_DELIVERY_DO_Y')?></a><?
						}
						else
						{
							?><a title="<? echo $strLockUserExt; ?>" class="adm-btn-disabled" href="javascript:void(0);"><?=GetMessage('SOD_ALLOW_DELIVERY_DO_Y')?></a><?
						}
						?></td>
					</tr>
					<tr id="allow_delivery_no_user" style="display:<?=($arOrder["ALLOW_DELIVERY"] == "N" && !$bUserCanDeliverOrder) ? 'table-row' : 'none'?>">
						<td><?=GetMessage("SOD_DELIVERY_IS_ALLOW")?>:</td>
						<td><?=GetMessage("SALE_NO")?></td>
					</tr>
					<tr id="allow_delivery_number" style="display:<?=(($arOrder["DELIVERY_DOC_NUM"] <> '' || mb_strlen($arOrder["DELIVERY_DOC_DATE"]))) ? 'table-row' : 'none'?>">
						<td valign="top"><?=GetMessage('SOD_NUMBER_ALLOW_DELIVERY');?>:</td>
						<td valign="middle" id="allow_delivery_doc_number_format"><?=GetMessage("SOD_DELIV_DOC", Array("#NUM#" => htmlspecialcharsEx($arOrder["DELIVERY_DOC_NUM"]), "#DATE#" => htmlspecialcharsEx($arOrder["DELIVERY_DOC_DATE"]))) ?></td>
					</tr>

					<tr id="allow_delivery_date" style="display:<?=($arOrder["DATE_ALLOW_DELIVERY"] <> '') ? 'table-row' : 'none'?>">
						<td><?=GetMessage('SOD_DATE_ALLOW_DELIVERY');?>:</td>
						<td id="allow_delivery_date_user"><?=$arOrder["DATE_ALLOW_DELIVERY"]?>
							<?if (!$crmMode && intval($arOrder["EMP_ALLOW_DELIVERY_ID"]) > 0)
								echo GetFormatedUserName($arOrder["EMP_ALLOW_DELIVERY_ID"], false);
							?>
						</td>
					</tr>

					<tr id="allow_delivery_is_allow" style="display:<?=($arOrder["ALLOW_DELIVERY"] != "N") ? 'table-row' : 'none'?>">
						<td><span class="alloy_payed_left"><?=GetMessage("SOD_DELIVERY_IS_ALLOW")?>:</span></td>
						<td><span class="alloy_payed_right"><?=GetMessage("SOD_DELIVERY_YES")?></span><?if($bUserCanDeliverOrder)
						{
							?>&nbsp;&nbsp;<?
							if (!$boolLocked)
							{
								?><a href="javascript:void(0);" onclick="fShowAllowDelivery(this, 'cancel');"><?=GetMessage('SOD_DELIVERY_EDIT');?></a><?
							}
							else
							{
								?><span style="text-decoration: line-through;" title="<? echo $strLockUserExt; ?>"><?=GetMessage('SOD_DELIVERY_EDIT');?></span><?
							}
						}
						?></td>
					</tr>
					<tr>
						<td colspan="2">
							<div id="popup_form" class="sale_popup_form adm-workarea" style="display:none; font-size:13px;">
								<table>
									<tr>
										<td class="head"><?=GetMessage('SOD_POPUP_ORDER_STATUS')?>:</td>
										<td><select name="FORM_STATUS_ID" id="FORM_STATUS_ID" onChange="fChangeOrderStatus();"><?=$statusOrder?></select></td>
									</tr>
									<?
										$arActions = array();

										$actions = CSaleDeliveryHandler::getActionsList($arOrder["DELIVERY_ID"]);

										if(array_key_exists("REQUEST_SELF", $actions))
											$arActions["REQUEST_SELF"] = $actions["REQUEST_SELF"];

										if(array_key_exists("REQUEST_TAKE", $actions))
											$arActions["REQUEST_TAKE"] = $actions["REQUEST_TAKE"];
									?>
									<?if(!empty($arActions)):?>
										<?
											$depList = \Bitrix\Sale\Internals\OrderDeliveryReqTable::getList(array(
												'filter'=>array('=ORDER_ID' => $ID),
											));

											if($dep = $depList->fetch())
											{
												$requestDisable = "";  // develop strlen($dep["DATE_REQUEST"]) > 0 ? " disabled" : "" ;
											}
											else
											{
												$requestDisable = "";
											}
										?>
										<tr>
											<td class="head"><?=GetMessage('SOD_POPUP_ORDER_ACTION')?>:</td>
											<td>
												<select name="FORM_ACTION_ID" id="FORM_ACTION_ID" onChange="fChangeDeliveryAction(this);" <?=$requestDisable?>>
													<option value=""><?=GetMessage("SALE_NO")?></option>
													<?foreach($arActions as $actionId => $actionName):?>
														<option value="<?=$actionId?>"><?=$actionName?></option>
													<?endforeach;?>
												</select>
											</td>
										</tr>
									<?endif;?>
									<tr>
										<td class="head"><?=GetMessage('SOD_POPUP_NUMBER_DOC')?>:</td>
										<td>
											<input type="text" class="popup_input" id="FORM_DELIVERY_DOC_NUM" name="FORM_DELIVERY_DOC_NUM" value="<?= htmlspecialcharsbx($arOrder["DELIVERY_DOC_NUM"]) ?>" size="30" maxlength="20" class="typeinput">
										</td>
									</tr>
									<tr>
										<td class="head"><?=GetMessage('SOD_POPUP_DATE_DOC')?>:</td>
										<td>
											<?= CalendarDate("FROM_DELIVERY_DOC_DATE", $arOrder["DELIVERY_DOC_DATE"], "change_delivery_form", "10", 'class="typeinput"'); ?>
										</td>
									</tr>

									<tr id="cancel_allow_delivery" style="display:none;">
										<td class="head"><label for="FORM_ALLOW_DELIVERY_CANCEL"><?=GetMessage('SOD_POPUP_DELIVERY_CANCEL')?>:</label></td>
										<td>
											<input type="checkbox" name="ALLOW_DELIVERY_CANCEL" id="FORM_ALLOW_DELIVERY_CANCEL" value="N" />
										</td>
									</tr>
								</table>
							</div>
							<script>
								function fChangeOrderStatus()
								{
									BX('change_status').value='Y';
									BX('change_status_popup').value='Y';
									var obStatusErr = BX('change_status_err');
									if (!!obStatusErr)
									{
										obStatusErr.innerHTML = '';
										obStatusErr.style.display = 'none';
									}

									BX('STATUS_ID').value = BX.findChild(BX('sale-popup-delivery'), {'attr': {id: 'FORM_STATUS_ID'}}, true, false).value;
								}

								function fChangeDeliveryAction(domNodeAId)
								{
									var disable;

									if(domNodeAId.value != "")
										disable = true;
									else
										disable = false;

									BX("FORM_DELIVERY_DOC_NUM").disabled = disable;
									delivery_date = BX.findChild(BX('sale-popup-delivery'), {'attr': {name: 'FROM_DELIVERY_DOC_DATE'}}, true, false);
									delivery_date.disabled = disable;
									delivery_date.nextElementSibling.style.display = disable ? "none" : "";

									var saveButton = BX.findChild(BX("deliverySaveButton"), {'class': 'popup-window-button-text'}, true, false);

									saveButton.innerHTML = disable ? "<?=GetMessage('SOD_POPUP_SEEND_AND_SAVE')?>" : "<?=GetMessage('SOD_POPUP_SAVE')?>";
								}

								function fChangeDeliveryActionIdResult(res)
								{
									if(res.RESULT == "ERROR")
									{
										alert("<?=GetMessage('SOD_POPUP_REQUEST_ERROR')?>");
									}
									else
									{
										if(res.TRACKING_NUMBER)
										{
											BX('tracking-number-text').value = res.TRACKING_NUMBER;
											BX('tracking-number-title').innerHTML = res.TRACKING_NUMBER;
										}

										alert("<?=GetMessage('SOD_POPUP_REQUEST_SUCCESS')?>");
									}
								}

								function fChangeDeliveryResult(res)
								{
									var rs = eval( '('+res+')' );
									BX.closeWait();
									if (rs["message"] == "ok")
									{
										if (rs["ALLOW_DELIVERY"] == "Y")
										{
											var emp_allow = '';

											BX('btn_allow_delivery').style.display = "none";
											// BX('allow_delivery_date').style.display = "none";
											BX('allow_delivery_number').style.display = "none";
											BX('allow_delivery_date').style.display = "table-row";
											BX('allow_delivery_is_allow').style.display = "table-row";

											if (rs["DATE_ALLOW_DELIVERY"].length > 0)
												emp_allow = rs["DATE_ALLOW_DELIVERY"];

											if (rs["EMP_ALLOW_DELIVERY_ID"] && rs["EMP_ALLOW_DELIVERY_ID"].length > 0)
												emp_allow += ' ' + rs["EMP_ALLOW_DELIVERY_ID"];

											if (BX('allow_delivery_date_user') && emp_allow.length > 0)
												BX('allow_delivery_date_user').innerHTML = emp_allow;

											if (rs["DELIVERY_DOC_NUMBER_FORMAT"].length > 0)
											{
												BX('allow_delivery_doc_number_format').innerHTML = rs["DELIVERY_DOC_NUMBER_FORMAT"];
												BX('allow_delivery_number').style.display = "table-row";
											}
										}
										else
										{
											// BX('allow_delivery_date').style.display = "table-row";
											BX('btn_allow_delivery').style.display = "table-row";
											// BX('allow_delivery_date2').style.display = "none";
											BX('allow_delivery_is_allow').style.display = "none";
											BX('allow_delivery_number').style.display = "none";
										}
										if (!!rs.STATUS_ERR && true == rs.STATUS_ERR)
										{
											var obStatusErr = BX('change_status_err');
											if (!!obStatusErr)
											{
												obStatusErr.innerHTML = rs.STATUS_ERR_MESS;
												obStatusErr.style.display = 'inline-block';
											}
										}
										else
										{
											if (BX('date_status_change') && rs['DATE_STATUS'].length > 0)
												BX('date_status_change').innerHTML = rs['DATE_STATUS'] + ' ' + rs['EMP_STATUS_ID'];

											if (rs['STATUS_ID'].length > 0)
												BX('STATUS_ID').value = rs['STATUS_ID'];
										}
									}

									fCheckReservationResult(rs);

									BX('change_status').value='N';
									BX('change_status_popup').value='N';
								}

								function fShowAllowDelivery(el, type)
								{
									if (type == 'cancel')
										BX("cancel_allow_delivery").style.display = 'table-row';

									BX('FORM_STATUS_ID').value = BX('STATUS_ID').value;
									if (BX('allow_delivery_is_allow').style.display == "none")
									{
										BX('FORM_ALLOW_DELIVERY_CANCEL').checked = false;
										BX('cancel_allow_delivery').style.display = "none";
									}

									formAllowDelivery = BX.PopupWindowManager.create("sale-popup-delivery", BX('allow_delivery_name'), {
										offsetTop : -100,
										offsetLeft : -150,
										autoHide : true,
										closeByEsc : true,
										closeIcon : true,
										titleBar : true,
										draggable: {restrict:true},
										titleBar: {content: BX.create("span", {html: '<? echo GetMessageJS('SOD_POPUP_DELIVE_TITLE'); ?>', 'props': {'className': 'sale-popup-title-bar'}})},
										content : BX("popup_form")
									});
									formAllowDelivery.setButtons([
										new BX.PopupWindowButton({
											text : "<?=GetMessageJS('SOD_POPUP_SAVE')?>",
											id: "deliverySaveButton",
											className : "",
											events : {
												click : function()
												{
													BX.showWait();
													if (BX.findChild(BX('sale-popup-delivery'), {'attr': {id: 'FORM_ALLOW_DELIVERY_CANCEL'}}, true, false).checked)
														allow_delivery = 'N';
													else
														allow_delivery = "Y";
													delivery_date = BX.findChild(BX('sale-popup-delivery'), {'attr': {name: 'FROM_DELIVERY_DOC_DATE'}}, true, false).value;

													var change_status = 'N';
													var status_id = '';
													if (BX('change_status') && BX('change_status').value == 'Y')
													{
														change_status = BX('change_status').value;
														status_id = BX('STATUS_ID').value;
													}

													var change_status_popup = 'N';
													if (BX('change_status_popup') && BX('change_status_popup').value == 'Y')
														change_status_popup = BX('change_status_popup').value;

													BX.ajax.post('/bitrix/admin/sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&save_order_data=Y&STATUS_ID='+status_id+'&change_status='+change_status+'&change_status_popup='+change_status_popup+'&change_delivery_form=Y&ALLOW_DELIVERY='+allow_delivery+'&DELIVERY_DOC_NUM='+BX('FORM_DELIVERY_DOC_NUM').value+'&DELIVERY_DOC_DATE='+delivery_date+'&ID=<?=$ID?>', fChangeDeliveryResult);

													var actionId = BX("FORM_ACTION_ID");
													if(actionId && actionId.value !== "")
													{
														BX.ajax({
															'method': 'POST',
															'dataType': 'json',
															'url': '/bitrix/admin/sale_order_detail.php',
															'data':  '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&DELIVERY_ACTION='+actionId.value+'&ID=<?=$ID?>',
															'onsuccess': fChangeDeliveryActionIdResult
														});
													}
													formAllowDelivery.close();
												}
											}
										}),
										new BX.PopupWindowButton({
											text : "<?=GetMessage('SOD_POPUP_CANCEL')?>",
											className : "",
											events : {
												click : function()
												{
													formAllowDelivery.close();
												}
											}
										})
									]);

									formAllowDelivery.show();
									BX('FORM_DELIVERY_DOC_NUM').focus();
								}

								/**
								* This function checks if reservation has been performed successfully
								* during 1) payment 2) delivery flag change or 3) cancellation (undoing reservation).
								* If there were any errors during that operation:
								* - it immediately updates order mark status in the interface
								* - and shows alert to the admin panel user
								*/
								function fCheckReservationResult(res)
								{
									if (res["reserve_message"] != "ok")
									{
										BX('user_can_mark').style.display = "none";
										BX('btn_mark_cancel').style.display = "table-row";
										BX('btn_mark_cancel_button').style.display = "table-row";

										BX('date_change_mark').style.display = "table-row";
										BX('date_change_mark_user').innerHTML = res["reserve_date"];

										BX('reason_mark').style.display = "table-row";
										BX('reason_mark_text').innerHTML = res["reserve_message"];

										alert(res["reserve_message"]);
									}
								}
							</script>
						</td>
					</tr>
				<?
				$tabControl->EndCustomField("ORDER_ALLOW_DELIVERY", '');

				$tabControl->BeginCustomField("ORDER_TRACKING_NUMBER", GetMessage("SOD_ORDER_TRACKING_NUMBER"));
					?>
					<tr>
						<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td width="60%">
							<div id="hover_comment"><?
							if (!$boolLocked)
							{
							?>
								<span id="tracking-number-title" onclick="fChangeTrackingNumber(this);">
									<?
									if ('' != $arOrder["TRACKING_NUMBER"])
										echo htmlspecialcharsbx($arOrder["TRACKING_NUMBER"]);
									else
										echo GetMessage('SOD_ORDER_TRACKING_NUMBER_ADD');
									?>
								</span>
								<span class="pencil"></span>
							<?
							}
							else
							{
								?><span id="tracking-number-title">
									<?
									if ('' != $arOrder["TRACKING_NUMBER"])
									{
										echo htmlspecialcharsbx($arOrder["TRACKING_NUMBER"]);
									}
									else
									{
										echo '<span style="text-decoration: line-through;" title="'.$strLockUserExt.'">'.GetMessage('SOD_ORDER_TRACKING_NUMBER_ADD').'</span>';
									}
									?>
								</span>
								<?
							}
							?>
							</div>
							<?
							if (!$boolLocked)
							{
							?>
							<input type="text" id="tracking-number-text" name="TRACKING_NUMBER" onChange="fEditTrackingNumber(this, 'change');"
								onBlur="fEditTrackingNumber(this, 'exit');" value="<?=htmlspecialcharsbx($arOrder["TRACKING_NUMBER"])?>" size="30">
							<?
							}
							else
							{
								?><input type="hidden" value="<?=htmlspecialcharsbx($arOrder["TRACKING_NUMBER"])?>" name="TRACKING_NUMBER" id="tracking-number-text"><?
							}
							?>
							<input type="hidden" name="change_tracking_number" id="id_change_tracking_number_hidden" value="N">

							<script>
								function fChangeTrackingNumber(el)
								{
									BX(el).style.display = 'none';
									BX('tracking-number-text').style.display = 'block';
									BX('tracking-number-text').focus();

								}

								function fEditTrackingNumber(el, type)
								{
									if (type == 'change')
									{
										BX.showWait();

										BX.ajax.post('/bitrix/admin/sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&change=Y&tracking_number='+BX.util.urlencode(el.value)+'&ID=<?=$ID?>', fEditTrackingNumberResult);

										if (BX('tracking-number-text').value.length > 0)
											BX('tracking-number-title').innerHTML = BX('tracking-number-text').value;
										else
											BX('tracking-number-title').innerHTML = '<?=GetMessage('SOD_ORDER_TRACKING_NUMBER_ADD')?>';
									}

									BX('tracking-number-title').style.display = 'inline-block';
									BX('tracking-number-text').style.display = 'none';
								}

								function fEditTrackingNumberResult(res)
								{
									BX.closeWait();
								}
							</script>
						</td>
					</tr>
				</tr>
				<?
				$tabControl->EndCustomField("ORDER_TRACKING_NUMBER", "");

				// additional delivery info (only for automated delivery systems and if delivery price was calculated)
				if (is_array($arDeliveryData) && !empty($arDeliveryData) && mb_strpos($arOrder["DELIVERY_ID"], ":") !== false && $arOrder["PRICE_DELIVERY"] != 0)
				{
					$arDeliveryId = explode(":", $arOrder["DELIVERY_ID"]);
					$profileId = $arDeliveryId[1];

					$arDeliveryOrder = array(
						"PRICE" => $orderTotalPrice,
						"WEIGHT" => $orderTotalWeight,
						"LOCATION_FROM" => COption::GetOptionString('sale', 'location', '', $LID),
						"LOCATION_TO" => $locationData,
						"LOCATION_ZIP" => $locationZip,
						"ITEMS" => $arBasketItems
					);


					$depList = \Bitrix\Sale\Internals\OrderDeliveryReqTable::getList(array(
						'filter'=>array('=ORDER_ID' => $ID),
					));

					if($dep = $depList->fetch())
					{
						$arDeliveryOrder["EXTRA_PARAMS"] = unserialize($dep["PARAMS"], ['allowed_classes' => false]);
					}

					$arPacks = CSaleDeliveryHelper::getBoxesFromConfig($profileId, $arDeliveryData["CONFIG"]["CONFIG"]);
					$arDeliveryResult = CSaleDeliveryHandler::CalculateFull($arDeliveryData["SID"], $profileId, $arDeliveryOrder, $arOrder["CURRENCY"], $LID);
					$delDiscountDiff = roundEx($arDeliveryResult["VALUE"] - $arOrder["PRICE_DELIVERY"], SALE_VALUE_PRECISION);

					$tabControl->BeginCustomField("ORDER_DELIVERY_ADDITIONAL_INFO", "");

					if (!empty($arPacks)):
						$arBox = array_shift(array_values($arPacks));
					?>
						<tr>
							<td width="40%"></td>
							<td width="60%">
								<a class="dashed-link" href="javascript:void(0);" onclick="fToggleDeliveryInfo();"><?=GetMessage("SOD_SHOW_DELIVERY_ADDITIONAL_INFO")?></a></td>
							</td>
						</tr>
						<tr class="hidden-delivery-info">
							<td><?=GetMessage("SOD_DELIVERY_BOX_SIZE")?>:</td>
							<td><?=GetMessage("SOD_DELIVERY_BOX_SIZE_VALUE", array("#L#" => $arBox["DIMENSIONS"][0], "#W#" => $arBox["DIMENSIONS"][1], "#H#" => $arBox["DIMENSIONS"][2]))?></td>
						</tr>
						<?
						if ($arDeliveryResult["RESULT"] == "OK"):
						?>
							<tr class="hidden-delivery-info">
								<td><?=GetMessage("SOD_DELIVERY_BOX_NUMBER")?>:</td>
								<td><?=$arDeliveryResult["PACKS_COUNT"]?></td>
							</tr>
							<tr class="hidden-delivery-info">
								<td><?=GetMessage("SOD_DELIVERY_SUM")?>:</td>
								<td><?=SaleFormatCurrency($arOrder["PRICE_DELIVERY"], $arOrder["CURRENCY"])?></td>
							</tr>
							<?
							if ($delDiscountDiff != 0):
							?>
								<tr class="hidden-delivery-info">
									<td><?=GetMessage("SOD_DELIVERY_DELIVERY_DISCOUNT_DIFF")?>:</td>
									<td><?=SaleFormatCurrency($delDiscountDiff, $arOrder["CURRENCY"])?></td>
								</tr>
							<?
							endif;
						elseif ($arDeliveryResult["RESULT"] == "ERROR"):
						?>
							<tr class="hidden-delivery-info">
								<td><?=GetMessage("SOD_DELIVERY_SUM_AND_BOX_NUMBER")?>:</td>
								<td><?=$arDeliveryResult["TEXT"]?></td>
							</tr>
						<?
						endif;

						$arData = array();
						if (isset($arDeliveryData["GETFEATURES"]) && is_callable($arDeliveryData["GETFEATURES"]))
							$arData = call_user_func($arDeliveryData["GETFEATURES"], $arDeliveryData["CONFIG"]["CONFIG"]);

						foreach ($arData as $paramName => $paramValue):
						?>
						<tr class="hidden-delivery-info">
							<td><?=$paramName?>:</td>
							<td><?=$paramValue?></td>
						</tr>
						<?
						endforeach;
					endif;
					?>
					<script>
						function fToggleDeliveryInfo()
						{
							var elements = document.getElementsByClassName('hidden-delivery-info');
							for (var i = 0; i < elements.length; ++i)
							{
								elements[i].style.display = (elements[i].style.display == 'none' || elements[i].style.display == '') ? 'table-row' : 'none';
							}
						}
					</script>
					<?

					$tabControl->EndCustomField("ORDER_DELIVERY_ADDITIONAL_INFO", "");
				}
				// end of additional delivery info

				$tabControl->AddSection("order_payment", GetMessage("P_ORDER_PAYMENT"));

				$tabControl->BeginCustomField("ORDER_PAYMENT", GetMessage("P_ORDER_PAYMENT"));
				?>
					<tr>
						<td valign="top"><?=GetMessage("P_ORDER_PAY_SYSTEM")?>:</td>
						<td valign="middle">
							<span id="payed_name">
							<?
							if (intval($arOrder["PAY_SYSTEM_ID"]) > 0)
							{
								$arPaySys = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"], $arOrder["PERSON_TYPE_ID"]);
								if ($arPaySys)
									echo htmlspecialcharsEx($arPaySys["NAME"]."");
								else
									echo '<font color="#FF0000">'.GetMessage("SOD_PAY_SYS_DISC").'</font>';
							}
							else
								echo GetMessage("SOD_NONE");
							?>
							</span>
						</td>
					</tr>
				<?
				$tabControl->EndCustomField("ORDER_PAYMENT", '');

				$tabControl->BeginCustomField("ORDER_PAYED", GetMessage("P_ORDER_PAYED"));
				?>
					<tr id="summary_pay" style="display:<?=($arOrder["PAYED"] == "N") ? 'table-row' : 'none'?>">
						<td valign="top"><?=GetMessage('SOD_PAYED_SUM');?>:</td>
						<td valign="middle"><?=SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"])?></td>
					</tr>

					<tr id="pay_date_pay" style="display:<?=($arOrder["DATE_PAYED"] <> '') ? 'table-row' : 'none'?>">
						<td><?=GetMessage('SOD_DATE_ALLOW_PAY_CHANGE');?>:</td>
						<td id="pay_date_pay_format"><?=$arOrder["DATE_PAYED"]?>
							<?if (!$crmMode && intval($arOrder["EMP_PAYED_ID"]) > 0)
								echo GetFormatedUserName($arOrder["EMP_PAYED_ID"], false);
							?>
						</td>
					</tr>
					<tr id="pay_pay_user" style="display:<?=($arOrder["PAYED"] == "N" && $bUserCanPayOrder) ? 'table-row' : 'none'?>">
						<td>&nbsp;</td>
						<td valign="middle" class="btn_order"><?
						if (!$boolLocked)
						{
							?><a title="<?=GetMessage('SOD_DO_PAY_ORDER')?>" onClick="fShowAllowPay(this);" class="adm-btn adm-btn-green" href="javascript:void(0);"><?=GetMessage('SOD_DO_PAY_ORDER')?></a><?
						}
						else
						{
							?><a title="<? echo $strLockUserExt; ?>" class="adm-btn-disabled" href="javascript:void(0);"><?=GetMessage('SOD_DO_PAY_ORDER')?></a><?
						}
						?></td>
					</tr>
					<tr id="pay_pay_user_no" style="display:<?=($arOrder["PAYED"] == "N" && !$bUserCanPayOrder) ? 'table-row' : 'none'?>">
						<td><?=GetMessage("SOD_PAYED_IS_ALLOW")?>:</td>
						<td><?=GetMessage("SALE_NO")?></td>
					</tr>
					<tr id="pay_allow_pay" style="display:<?=($arOrder["PAYED"] != "N" && $arOrder["PAY_VOUCHER_NUM"] <> '') ? 'table-row' : 'none'?>">
						<td><?=GetMessage('SOD_NUMBER_ALLOW_PAY');?>:</td>
						<td id="payed_doc_number_format"><?= str_replace("#DATE#", $arOrder["PAY_VOUCHER_DATE"], str_replace("#NUM#", htmlspecialcharsEx($arOrder["PAY_VOUCHER_NUM"]), GetMessage("SOD_PAY_DOC"))) ?></td>
					</tr>
					<tr id="pay_is_pay" style="display:<?=($arOrder["PAYED"] != "N") ? 'table-row' : 'none'?>">
						<td><span class="alloy_payed_left"><?=GetMessage("SOD_PAYED_IS_ALLOW")?>:</span></td>
						<td>
							<span class="alloy_payed_right"><?=GetMessage("SOD_PAYED_YES")?></span>
							<?if($bUserCanPayOrder)
							{
								?>&nbsp;&nbsp;<?
								if (!$boolLocked)
								{
									?><a href="javascript:void(0);" onclick="fShowAllowPay(this);"><?=GetMessage('SOD_DELIVERY_EDIT');?></a><?
								}
								else
								{
									?><span style="text-decoration: line-through;" title="<? echo $strLockUserExt; ?>"><?=GetMessage('SOD_DELIVERY_EDIT');?></span><?
								}
							}
							?>
						</td>
					</tr>

					<tr>
						<td colspan="2">
							<div id="popup_form_pay" class="sale_popup_form adm-workarea" style="display:none; font-size:13px;">
								<table>
									<tr>
										<td class="head"><?=GetMessage('SOD_POPUP_PAY_STATUS')?>:</td>
										<td><select name="FORM_PAY_STATUS_ID" id="FORM_PAY_STATUS_ID" onChange="fPayChangeOrderStatus();"><?=$statusOrder?></select></td>
									</tr>
									<tr>
										<td class="head"><?=GetMessage('SOD_POPUP_PAY_NUMBER_DOC')?>:</td>
										<td>
											<input type="text" id="FORM_PAY_VOUCHER_NUM" class="popup_input" name="FORM_PAY_VOUCHER_NUM" value="<?= htmlspecialcharsbx($arOrder["PAY_VOUCHER_NUM"]) ?>" size="30" maxlength="20" class="typeinput">
										</td>
									</tr>
									<tr>
										<td class="head"><?=GetMessage('SOD_POPUP_PAY_DATE_DOC')?>:</td>
										<td>
											<?= CalendarDate("FROM_PAY_VOUCHER_DATE", $arOrder["PAY_VOUCHER_DATE"], "change_pay_form", "10", 'class="typeinput"'); ?>
										</td>
									</tr>
									<?
									$dbUserAccount = CSaleUserAccount::GetList(
										array(),
										array(
											"USER_ID" => $arOrder["USER_ID"],
											"CURRENCY" => $arOrder["CURRENCY"],
											"LOCKED" => "N"
										)
									);
									$arUserAccount = $dbUserAccount->GetNext();
									if ($arUserAccount && is_array($arUserAccount)):
									?>
									<tr id="user_budget" style="display:<?=($arOrder["PAYED"] == "N" && floatval($arUserAccount["CURRENT_BUDGET"]) >= $arOrder["PRICE"]) ? 'table-row' : 'none'?>">
										<td class="head" nowrap><?=GetMessage('SOD_ORDER_USER_BUDGET')?>:</td>
										<td id="price_user_budget"><b><?=SaleFormatCurrency($arUserAccount["CURRENT_BUDGET"], $arOrder["CURRENCY"]);?></b></td>
									</tr>
									<tr id="pay_from_account" style="display:<?=($arOrder["PAYED"] == "N" && count($arUserAccount) > 1 && floatval($arUserAccount["CURRENT_BUDGET"]) >= $arOrder["PRICE"]) ? 'table-row' : 'none'?>">
										<td class="head" nowrap><label for="FORM_PAY_FROM_ACCOUNT"><?=GetMessage('SOD_PAY_ACCOUNT')?>:</label></td>
										<td>
											<input type="checkbox" value="Y" name="FORM_PAY_FROM_ACCOUNT" id="FORM_PAY_FROM_ACCOUNT" />
										</td>
									</tr>
									<?php endif; ?>
									<tr id="cancel_allow_pay" style="display:<?=($arOrder["PAYED"] == "Y") ? 'table-row' : 'none'?>">
										<td class="head"><label for="FORM_ALLOW_PAY_CANCEL"><?=GetMessage('SOD_POPUP_PAY_CANCEL')?>:</label></td>
										<td>
											<input type="checkbox" name="FORM_ALLOW_PAY_CANCEL" id="FORM_ALLOW_PAY_CANCEL" value="N" />
										</td>
									</tr>
									<tr id="repay_to_account" style="display:<?=($arOrder["PAYED"] == "Y") ? 'table-row' : 'none'?>">
										<td class="head"><label for="FORM_PAY_FROM_ACCOUNT_BACK"><?=GetMessage('SOD_PAY_ACCOUNT_BACK')?>:</label></td>
										<td>
											<input type="checkbox" name="FORM_PAY_FROM_ACCOUNT_BACK" id="FORM_PAY_FROM_ACCOUNT_BACK" value="N" />
										</td>
									</tr>
								</table>
							</div>
							<script>
								function fPayChangeOrderStatus()
								{
									BX('change_status').value='Y';
									BX('change_status_popup').value='Y';
									var obStatusErr = BX('change_status_err');
									if (!!obStatusErr)
									{
										obStatusErr.innerHTML = '';
										obStatusErr.style.display = 'none';
									}

									BX('STATUS_ID').value = BX.findChild(BX('sale-popup-pay'), {'attr': {id: 'FORM_PAY_STATUS_ID'}}, true, false).value;
								}

								function fChangePayResult(res)
								{
									var rs = eval( '('+res+')' );
									BX.closeWait();

									if (rs["message"] == "ok")
									{
										if (rs["PAYED"] == "Y")
										{
											var emp_payed = '';

											BX('summary_pay').style.display = "none";
											BX('pay_pay_user').style.display = "none";
											BX('pay_is_pay').style.display = "table-row";

											if (rs["DATE_PAYED"] && rs["DATE_PAYED"].length > 0)
											{
												emp_payed = rs["DATE_PAYED"]+'&nbsp;';

												if (rs["EMP_PAYED_ID"] && rs["EMP_PAYED_ID"].length > 0)
													emp_payed += rs["EMP_PAYED_ID"];

												if (BX('pay_date_pay_format') && emp_payed.length > 0)
													BX('pay_date_pay_format').innerHTML = emp_payed;

												BX('pay_date_pay').style.display = "table-row";
											}

											if (rs["PAY_DOC_NUMBER_FORMAT"] && rs["PAY_DOC_NUMBER_FORMAT"].length > 0)
											{
												BX('payed_doc_number_format').innerHTML = rs["PAY_DOC_NUMBER_FORMAT"];
												BX('pay_allow_pay').style.display = "table-row";
											}

											BX('user_budget').style.display = "none";
											BX('pay_from_account').style.display = "none";
											BX('cancel_allow_pay').style.display = "table-row";
											BX('repay_to_account').style.display = "table-row";
										}
										else
										{
											BX('summary_pay').style.display = "table-row";
											BX('pay_pay_user').style.display = "table-row";
											BX('pay_allow_pay').style.display = "none";
											BX('pay_date_pay').style.display = "table-row";
											BX('pay_is_pay').style.display = "none";

											if (rs["BUDGET_ENABLE"] && rs["BUDGET_ENABLE"] == "Y")
											{
												BX('price_user_budget').innerHTML = "<b>"+rs["BUDGET_USER"]+"</b>";

												BX('user_budget').style.display = "table-row";
												BX('pay_from_account').style.display = "table-row";
											}

											BX('cancel_allow_pay').style.display = "none";
											BX('repay_to_account').style.display = "none";
										}

										if (!!rs.STATUS_ERR && true == rs.STATUS_ERR)
										{
											var obStatusErr = BX('change_status_err');
											if (!!obStatusErr)
											{
												obStatusErr.innerHTML = rs.STATUS_ERR_MESS;
												obStatusErr.style.display = 'inline-block';
											}
										}
										else
										{
											if (BX('date_status_change') && rs['DATE_STATUS'].length > 0)
												BX('date_status_change').innerHTML = rs['DATE_STATUS'] + ' ' + rs['EMP_STATUS_ID'];

											if (rs['STATUS_ID'] && rs['STATUS_ID'].length > 0)
												BX('STATUS_ID').value = rs['STATUS_ID'];
										}
									}

									fCheckReservationResult(rs);

									BX('change_status').value='N';
									BX('change_status_popup').value='N';
								}

								function fShowAllowPay(el)
								{
									BX('FORM_PAY_STATUS_ID').value = BX('STATUS_ID').value;

									if (BX('FORM_ALLOW_PAY_CANCEL'))
										BX('FORM_ALLOW_PAY_CANCEL').checked = false;

									if (BX('FORM_PAY_FROM_ACCOUNT_BACK'))
										BX('FORM_PAY_FROM_ACCOUNT_BACK').checked = false;

									if (BX('FORM_PAY_FROM_ACCOUNT'))
										BX('FORM_PAY_FROM_ACCOUNT').checked = false;

									formAllowPay = BX.PopupWindowManager.create("sale-popup-pay", BX('payed_name'), {
										offsetTop : -100,
										offsetLeft : -150,
										autoHide : true,
										closeByEsc : true,
										closeIcon : true,
										titleBar : true,
										draggable: {restrict:true},
										titleBar: {content: BX.create("span", {html: '<?=GetMessageJS('SOD_POPUP_PAY_TITLE')?>', 'props': {'className': 'sale-popup-title-bar'}})},
										content : document.getElementById("popup_form_pay")
									});
									formAllowPay.setButtons([
										new BX.PopupWindowButton({
											text : "<?=GetMessage('SOD_POPUP_SAVE')?>",
											className : "",
											events : {
												click : function()
												{
													BX.showWait();

													payed = "Y";
													if (BX('FORM_ALLOW_PAY_CANCEL') && BX.findChild(BX('sale-popup-pay'), {'attr': {id: 'FORM_ALLOW_PAY_CANCEL'}}, true, false).checked)
														payed = "N";

													pay_date = BX.findChild(BX('popup_form_pay'), {'attr': {name: 'FROM_PAY_VOUCHER_DATE'}}, true, false).value;
													pay_num = BX('FORM_PAY_VOUCHER_NUM').value;

													var change_status = 'N';
													var status_id = '';
													if (BX('change_status') && BX('change_status').value == 'Y')
													{
														change_status = BX('change_status').value;
														status_id = BX('STATUS_ID').value;
													}

													var pay_from_account = "";
													if (BX('FORM_PAY_FROM_ACCOUNT') && BX.findChild(BX('sale-popup-pay'), {'attr': {id: 'FORM_PAY_FROM_ACCOUNT'}}, true, false).checked)
														pay_from_account = 'Y';

													var pay_from_account_back = "";
													if (BX('FORM_PAY_FROM_ACCOUNT_BACK') && BX.findChild(BX('sale-popup-pay'), {'attr': {id: 'FORM_PAY_FROM_ACCOUNT_BACK'}}, true, false).checked)
														pay_from_account_back = 'Y';

													var change_status_popup = 'N';
													if (BX('change_status_popup') && BX('change_status_popup').value == 'Y')
														change_status_popup = BX('change_status_popup').value;

													BX.ajax.post('/bitrix/admin/sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&save_order_data=Y&STATUS_ID='+status_id+'&change_status='+change_status+'&change_status_popup='+change_status_popup+'&change_pay_form=Y&PAYED='+payed+'&PAY_VOUCHER_NUM='+pay_num+'&PAY_VOUCHER_DATE='+pay_date+'&PAY_FROM_ACCOUNT='+pay_from_account+'&PAY_FROM_ACCOUNT_BACK='+pay_from_account_back+'&ID=<?=$ID?>', fChangePayResult);

													formAllowPay.close();
												}
											}
										}),
										new BX.PopupWindowButton({
											text : "<?=GetMessage('SOD_POPUP_CANCEL')?>",
											className : "",
											events : {
												click : function()
												{
													formAllowPay.close();
												}
											}
										})
									]);

									formAllowPay.show();
									BX('FORM_PAY_VOUCHER_NUM').focus();
								}
							</script>
						</td>
					</tr>
				<?
				$tabControl->EndCustomField("ORDER_PAYED", '');

				$arPaySys = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"], $arOrder["PERSON_TYPE_ID"]);
				if ($arOrder["PS_STATUS"] <> '')
				{
					$tabControl->AddSection("ps_stat", GetMessage("P_ORDER_PS_STATUS"));
					$tabControl->BeginCustomField("ORDER_PS_STATUS", GetMessage("P_ORDER_PS_STATUS"));
					?>
					<tr>
						<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td>
							<?
							echo (($arOrder["PS_STATUS"]=="Y") ? "OK" : "N");
							if (!$boolLocked)
							{
								if (!$crmMode && $arPaySys["PSA_HAVE_RESULT"] == "Y" || $arPaySys["PSA_RESULT_FILE"] <> '')
								{
									?>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<a href="/bitrix/admin/sale_order_detail.php?ID=<?= $ID ?>&action=ps_update&lang=<? echo LANGUAGE_ID; ?><?echo GetFilterParams("filter_")?>&<?= bitrix_sessid_get() ?>"><?echo GetMessage("P_ORDER_PS_STATUS_UPDATE") ?> &gt;&gt;</a>
									<?
								}
							}
							?>
						</td>
					</tr>
					<?
					$tabControl->EndCustomField("ORDER_PS_STATUS", '');

					$tabControl->BeginCustomField("ORDER_PS_STATUS_CODE", GetMessage("P_ORDER_PS_STATUS"));
					?>
					<tr>
						<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td><?echo $arOrder["PS_STATUS_CODE"] ;?></td>
					</tr>
					<?
					$tabControl->EndCustomField("ORDER_PS_STATUS_CODE", '');

					$tabControl->BeginCustomField("ORDER_PS_STATUS_DESCRIPTION", GetMessage("P_ORDER_PS_STATUS_DESCRIPTION"));
					?>
					<tr>
						<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td><?echo $arOrder["PS_STATUS_DESCRIPTION"] ;?></td>
					</tr>
					<?
					$tabControl->EndCustomField("ORDER_PS_STATUS_DESCRIPTION", '');

					$tabControl->BeginCustomField("ORDER_PS_STATUS_MESSAGE", GetMessage("P_ORDER_PS_STATUS_MESSAGE"));
					?>
					<tr>
						<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td><?echo $arOrder["PS_STATUS_MESSAGE"] ;?></td>
					</tr>
					<?
					$tabControl->EndCustomField("ORDER_PS_STATUS_MESSAGE", '');

					$tabControl->BeginCustomField("ORDER_PS_SUM", GetMessage("P_ORDER_PS_SUM"));
					?>
					<tr>
						<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td><?echo $arOrder["PS_SUM"] ;?></td>
					</tr>
					<?
					$tabControl->EndCustomField("ORDER_PS_SUM", '');

					$tabControl->BeginCustomField("ORDER_PS_CURRENCY", GetMessage("P_ORDER_PS_CURRENCY"));
					?>
					<tr>
						<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td><?echo $arOrder["PS_CURRENCY"] ;?></td>
					</tr>
					<?
					$tabControl->EndCustomField("ORDER_PS_CURRENCY", '');

					$tabControl->BeginCustomField("ORDER_PS_RESPONSE_DATE", GetMessage("P_ORDER_PS_RESPONSE_DATE"));
					?>
					<tr>
						<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td><?echo $arOrder["PS_RESPONSE_DATE"]; ?></td>
					</tr>
					<?
					$tabControl->EndCustomField("ORDER_PS_RESPONSE_DATE", '');
				}
				elseif (!$crmMode && $arPaySys["PSA_HAVE_RESULT"] == "Y" || $arPaySys["PSA_RESULT_FILE"] <> '')
				{
					$tabControl->AddSection("ps_stat", GetMessage("P_ORDER_PS_STATUS"));
					$tabControl->BeginCustomField("ORDER_PS_STATUS_REC", GetMessage("P_ORDER_PS_STATUS"));
					?>
					<tr>
						<td><?echo $tabControl->GetCustomLabelHTML()?>:</td>
						<td><?
						if (!$boolLocked)
						{
							?><a href="/bitrix/admin/sale_order_detail.php?ID=<?= $ID ?>&action=ps_update&lang=<? echo LANGUAGE_ID; ?><?= GetFilterParams("filter_") ?>&<?= bitrix_sessid_get() ?>"><?= GetMessage("P_ORDER_PS_STATUS_UPDATE") ?> &gt;&gt;</a><?
						}
						else
						{
							?><span style="text-decoration: line-through;" title="<? echo $strLockUserExt; ?>"><? echo GetMessage("P_ORDER_PS_STATUS_UPDATE"); ?></span><?
						}
						?></td>
					</tr>
					<?
					$tabControl->EndCustomField("ORDER_PS_STATUS_REC", '');
				}

				//order mark
				$tabControl->AddSection("order_mark", GetMessage("P_ORDER_MARK"));
				$tabControl->BeginCustomField("ORDER_MARKED", GetMessage("SOD_MARKED_Y"));
					?>
					<tr id="btn_show_marked" style="display:<?=($arOrder["MARKED"] == "N" && $bUserCanMarkOrder) ? 'table-row' : 'none'?>">
						<td width="40%"><?=GetMessage("SOD_MARKED")?>:</td>
						<td valign="middle">
							<?=GetMessage("SALE_NO")?>
							<!-- <a title="<?=GetMessage('SOD_MARKED')?>" onClick="fShowMarkOrder(this, '');" class="adm-btn-wrap" href="javascript:void(0);"><span class="adm-btn"><?=GetMessage('SOD_MARKED')?></span></a> -->
						</td>
					</tr>
					<tr id="user_can_mark" style="display:<?=($arOrder["MARKED"] == "N" && !$bUserCanMarkOrder) ? 'table-row' : 'none'?>">
						<td width="40%">
							<?=GetMessage("SOD_MARKED")?>:
						</td>
						<td valign="middle">
							<?=GetMessage("SALE_NO")?>
						</td>
					</tr>
					<tr id="btn_mark_cancel" style="display:<?=($arOrder["MARKED"] != "N") ? 'table-row' : 'none'?>">
						<td>
							<span class="order_cancel_left"><?=GetMessage("SOD_MARKED")?>:</span>
						</td>
						<td>
							<span class="order_marked_right"><?=GetMessage("SALE_YES")?></span>
						</td>
					</tr>
					<tr id="date_change_mark" style="display:<?=($arOrder["DATE_MARKED"] <> '' && $arOrder["MARKED"] == "Y") ? 'table-row' : 'none'?>">
						<td>
							<?=GetMessage('SOD_DATE_MARKED');?>:
						</td>
						<td id="date_change_mark_user">
							<?=$arOrder["DATE_MARKED"]?>
							<?if (!$crmMode && intval($arOrder["EMP_MARKED_ID"]) > 0)
								echo GetFormatedUserName($arOrder["EMP_MARKED_ID"], false);
							?>
						</td>
					</tr>
					<tr id="reason_mark" style="display:<?=($arOrder["MARKED"] != "N") ? 'table-row' : 'none'?>">
						<td>
							<?=GetMessage('SOD_MARK_REASON_TITLE')?>:
						</td>
						<td id="reason_mark_text">
							<?=htmlspecialcharsbx($arOrder["REASON_MARKED"])?>
						</td>
					</tr>
					<tr id="btn_mark_cancel_button" style="display:<?=($arOrder["MARKED"] != "N") ? 'table-row' : 'none'?>">
						<td width="40%">&nbsp;</td>
						<td>
							<?if($bUserCanMarkOrder)
							{
								if (!$boolLocked)
								{
									?><a href="javascript:void(0);" onclick="fCancelMarkOrder();" class="adm-btn-wrap"><span class="adm-btn"><?=GetMessage('SOD_MARK_N');?></span></a><?
								}
								else
								{
									?><a href="javascript:void(0);" class="adm-btn-wrap" title="<? echo $strLockUserExt; ?>"><span class="adm-btn-disabled"><?=GetMessage('SOD_MARK_N');?></span></a><?
								}
							}
							?>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<div id="popup_mark_order_form" class="sale_popup_form" style="display:none; font-size:13px;">
								<table>
									<tr>
										<td colspan="2"><?=GetMessage('SOD_MARK_REASON_TITLE')?><br />
											<textarea name="FORM_REASON_MARKED" id="FORM_REASON_MARKED" rows="3" cols="30"><?= htmlspecialcharsEx($arOrder["REASON_MARKED"]) ?></textarea><br />
										</td>
									</tr>
								</table>
							</div>

							<script>
								function fCancelMarkOrder()
								{
									BX.showWait();
									BX.ajax.post('/bitrix/admin/sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&save_order_data=Y&'+'&change_marked=Y&MARKED=N&ID=<?=$ID?>', fCancelMarkOrderResult);
								}

								function fCancelMarkOrderResult(res)
								{
									var rs = eval( '('+res+')' );
									BX.closeWait();
									if (rs["message"] == "ok")
									{
										BX('date_change_mark').style.display = "none";
										BX('btn_show_marked').style.display = "table-row";
										BX('reason_mark').style.display = "none";
										BX('btn_mark_cancel').style.display = "none";
										BX('btn_mark_cancel_button').style.display = "none";

										if (rs["DATE_MARKED"].length > 0)
											BX('date_change_mark_user').innerHTML = rs["DATE_MARKED"];

										if ( typeof rs["EMP_MARKED_ID"] != "undefined" )
											BX('date_change_mark_user').innerHTML += rs["EMP_MARKED_ID"];
									}
								}

								function fChangeMarkResult(res)
								{
									// todo: not used now
								}

								function fShowMarkOrder(el, type)
								{
									formMarkOrder = BX.PopupWindowManager.create("sale-popup-mark", el, {
										offsetTop : -100,
										offsetLeft : -150,
										autoHide : true,
										closeByEsc : true,
										closeIcon : true,
										titleBar : true,
										draggable: {restrict:true},
										titleBar: {content: BX.create("span", {html: '<?=GetMessageJS('SOD_MARK_ORDER')?>', 'props': {'className': 'sale-popup-title-bar'}})},
										content : BX("popup_mark_order_form")
									});

									formMarkOrder.setButtons([
										new BX.PopupWindowButton({
											text : "<?=GetMessage('SOD_POPUP_SAVE')?>",
											className : "",
											events : {
												click : function()
												{
													BX.showWait();
													BX.ajax.post('/bitrix/admin/sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&save_order_data=Y&'+'&change_marked=Y&MARKED=Y&REASON_MARKED='+BX.util.urlencode(BX('FORM_REASON_MARKED').value)+'&ID=<?=$ID?>', fChangeMarkResult);
													formMarkOrder.close();
												}
											}
										}),
										new BX.PopupWindowButton({
											text : "<?=GetMessage('SOD_POPUP_CANCEL')?>",
											className : "",
											events : {
												click : function()
												{
													BX('FORM_REASON_MARKED').value = '';
													formMarkOrder.close();
												}
											}
										})
									]);

									formMarkOrder.show();
									BX('FORM_REASON_MARKED').focus();
								}
							</script>
						</td>
					</tr>
				<?
				$tabControl->EndCustomField("ORDER_MARKED", '');

				$tabControl->AddSection("order_comments", GetMessage("SOD_COMMENTS"));

				$tabControl->BeginCustomField("ORDER_COMMENTS", GetMessage("SOD_COMMENTS"));

					if ($arOrder["USER_DESCRIPTION"] <> '')
					{
						?>
						<tr>
							<td valign="top"><?echo GetMessage("P_ORDER_USER_COMMENTS")?>:</td>
							<td valign="middle"><?echo htmlspecialcharsEx($arOrder["USER_DESCRIPTION"]); ?></td>
						</tr>
						<?
					}

					if ($arOrder["ADDITIONAL_INFO"] <> '')
					{
						?>
						<tr>
							<td valign="top"><?echo GetMessage("P_ORDER_ADDITIONAL_INFO")?>:</td>
							<td valign="middle"><?echo htmlspecialcharsEx($arOrder["ADDITIONAL_INFO"]); ?></td>
						</tr>
						<?
					}
				?>
				<tr>
					<td valign="top"><?echo GetMessage('SOD_ORDER_COMMENT_MANAGER_TITLE');?>:</td>
					<td valign="middle">
						<div id="hover_comment"><?
						if (!$boolLocked)
						{
						?>
							<span id="manager-comment-title" onClick="fShowComment(this);">
								<?
								if('' != $arOrder["COMMENTS"])
								{
									echo htmlspecialcharsbx($arOrder["COMMENTS"]);
								}
								else
								{
									echo GetMessage('SOD_ORDER_COMMENT_MANAGER');
								}
								?>
							</span>
							<span class="pencil"></span><?
						}
						else
						{
							if ('' != $arOrder["COMMENTS"])
							{
								echo htmlspecialcharsbx($arOrder["COMMENTS"]);
							}
							else
							{
								echo '<span style="text-decoration: line-through;" title="'.$strLockUserExt.'">'.GetMessage('SOD_ORDER_COMMENT_MANAGER').'</span>';
							}
						}
						?>
						</div>
						<?
						if (!$boolLocked)
						{
							?><textarea id="manager-comment-text"  name="COMMENTS" class="comment" onChange="fEditComment(this, 'change');" onblur="fEditComment(this, 'exit');"><?= htmlspecialcharsbx($arOrder["COMMENTS"]) ?></textarea><?
						}
						?>
						<input type="hidden" name="change_comments" id="id_change_comments_hidden" value="N">

						<script>
							function fShowComment(el)
							{
								BX(el).style.display = 'none';
								BX('manager-comment-text').style.display = 'block';
								BX('manager-comment-text').focus();

							}
							function fEditComment(el, type)
							{
								if (type == 'change')
								{
									BX.showWait();

									BX.ajax.post('/bitrix/admin/sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&change=Y&comment='+BX.util.urlencode(el.value)+'&ID=<?=$ID?>', fEditCommentResult);

									if (BX('manager-comment-text').value.length > 0)
										BX('manager-comment-title').innerHTML = BX('manager-comment-text').value;
									else
										BX('manager-comment-title').innerHTML = '<?=GetMessage('SOD_ORDER_COMMENT_MANAGER')?>';
								}

								BX('manager-comment-title').style.display = 'inline-block';
								BX('manager-comment-text').style.display = 'none';
							}

							function fEditCommentResult(res)
							{
								BX.closeWait();
							}
						</script>
					</td>
				</tr>
				<?
				$tabControl->EndCustomField("ORDER_COMMENTS", '');

				$tabControl->AddSection("order_deduction", GetMessage("P_ORDER_DEDUCTION"));
				$tabControl->BeginCustomField("ORDER_DEDUCTED", GetMessage("P_ORDER_DEDUCTED"));
				?>
					<tr id="deduct_message_yes" style="display: <?=($arOrder["DEDUCTED"] == "Y") ? "table-row" : "none" ?>"	>
						<td>
							<span class="alloy_payed_left"><?=GetMessage("SOD_DEDUCTED")?>:</span>
						</td>
						<td valign="top">
							<span class="alloy_payed_right"><?=GetMessage("SALE_YES")?></span>
						</td>
					</tr>
					<tr id="deduct_message_no" style="display: <?=($arOrder["DEDUCTED"] == "N") ? "table-row" : "none" ?>"	>
						<td>
							<?=GetMessage("SOD_DEDUCTED")?>:
						</td>
						<td valign="top">
							<?=GetMessage("SALE_NO")?>
						</td>
					</tr>
					<tr id="deduct_date" style="display:<?=($arOrder["DATE_DEDUCTED"] <> '') ? 'table-row' : 'none'?>">
						<td><?=GetMessage('SOD_DATE_DEDUCT_CHANGE');?>:</td>
						<td id="date_deduct_format"><?=$arOrder["DATE_DEDUCTED"]?>
							<?if (!$crmMode && intval($arOrder["EMP_DEDUCTED_ID"]) > 0)
								echo GetFormatedUserName($arOrder["EMP_DEDUCTED_ID"], false);
							?>
						</td>
					</tr>
					<tr id="reason_deduct" style="display:<?=($arOrder["DATE_DEDUCTED"] > 0 && $arOrder["DEDUCTED"] == "N") ? 'table-row' : 'none'?>">
						<td>
							<?=GetMessage('SOD_UNDO_DEDUCT_REASON_TITLE')?>:
						</td>
						<td id="reason_deduct_text">
							<?=htmlspecialcharsbx($arOrder["REASON_UNDO_DEDUCTED"])?>
						</td>
					</tr>
					<tr id="btn_show_deduct" style="display:<?=($arOrder["DEDUCTED"] == "Y" && $bUserCanDeductOrder) ? 'table-row' : 'none'?>">
						<td width="40%">&nbsp;</td>
						<td valign="middle"><?
						if (!$boolLocked)
						{
							?><a title="<?=GetMessage('SOD_DEDUCT_N')?>" onclick="fShowUndoDeductOrder(this, '');" class="adm-btn-wrap" href="javascript:void(0);"><span class="adm-btn"><?=GetMessage('SOD_DEDUCT_N')?></span></a><?
						}
						else
						{
							?><a title="<? echo $strLockUserExt; ?>" class="adm-btn-wrap" href="javascript:void(0);"><span class="adm-btn-disabled"><?=GetMessage('SOD_DEDUCT_N')?></span></a><?
						}
						?>
						</td>
					</tr>

					<tr>
						<td valign="top">
							<div id="popup_deduct_order_form" class="sale_popup_form" style="display:none; font-size:13px;">
								<table>
									<tr>
										<td colspan="2"><?=GetMessage('SOD_UNDO_DEDUCT_REASON_TITLE')?><br />
											<textarea name="FORM_REASON_UNDO_DEDUCT" id="FORM_REASON_UNDO_DEDUCT" rows="3" cols="30"><?= htmlspecialcharsEx($arOrder["REASON_UNDO_DEDUCTED"]) ?></textarea><br />
										</td>
									</tr>
								</table>
							</div>
							<script>
								function fUndoDeductOrderResult(res)
								{
									BX.closeWait();
									var rs = eval( '('+res+')' );

									if (rs["message"] == "ok")
									{
										BX('deduct_message_yes').style.display = "none";
										BX('deduct_message_no').style.display = "table-row";

										BX('btn_show_deduct').style.display = "none";
										BX('reason_deduct').style.display = "table-row";

										if (rs["DATE_DEDUCTED"].length > 0)
											BX('date_deduct_format').innerHTML = rs["DATE_DEDUCTED"] + ' ' + rs["EMP_DEDUCTED_ID"];

										BX('reason_deduct_text').innerHTML = rs["REASON_UNDO_DEDUCTED"];
									}
									else
									{
										alert(rs["message"]);
									}
								}

								//is used when setting DEDUCTION = "Y". possibly later

								// function fChangeUndoDeductResult(res)
								// {
								// 	var rs = eval( '('+res+')' );
								// 	BX.closeWait();
								// 	if (rs["message"] == "ok")
								// 	{
								// 		var emp_undo_deduct_user = '';

								// 		BX('btn_show_undo_deduct').style.display = "none";
								// 		BX('btn_undo_deduct_undo_deduct').style.display = "table-row";

								// 		if (rs["DATE_DEDUCTED"] && rs["DATE_DEDUCTED"].length > 0)
								// 			emp_undo_deduct_user = rs["DATE_DEDUCTED"];

								// 		if (rs["EMP_DEDUCTED_ID"] && rs["EMP_DEDUCTED_ID"].length > 0)
								// 			emp_undo_deduct_user += ' ' + rs["EMP_DEDUCTED_ID"];

								// 		if (BX('date_change_undo_deduct_user') && emp_undo_deduct_user.length > 0)
								// 			BX('date_change_undo_deduct_user').innerHTML = emp_undo_deduct_user;

								// 		BX('date_change_undo_deduct').style.display = "table-row";
								// 		BX('reason_undo_deduct_text').innerHTML = BX('FORM_REASON_UNDO_DEDUCT').value;
								// 		BX('reason_undo_deduct').style.display = "table-row";
								// 	}
								// }

								function fShowUndoDeductOrder(el, type)
								{
									formUndoDeductOrder = BX.PopupWindowManager.create("sale-popup-deduct", el, {
										offsetTop : -100,
										offsetLeft : -150,
										autoHide : true,
										closeByEsc : true,
										closeIcon : true,
										titleBar : true,
										draggable: {restrict:true},
										titleBar: {content: BX.create("span", {html: '<?=GetMessageJS('SOD_UNDO_DEDUCT_ORDER')?>', 'props': {'className': 'sale-popup-title-bar'}})},
										content : BX("popup_deduct_order_form")
									});
									formUndoDeductOrder.setButtons([
										new BX.PopupWindowButton({
											text : "<?=GetMessage('SOD_POPUP_SAVE')?>",
											className : "",
											events : {
												click : function()
												{
													BX.showWait();
													var urlparams = '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&save_order_data=Y&change_deduct=Y&UNDO_DEDUCT=Y&REASON_UNDO_DEDUCTED='+BX('FORM_REASON_UNDO_DEDUCT').value+'&ID=<?=$ID?>';
													BX.ajax.post('/bitrix/admin/sale_order_detail.php', urlparams, fUndoDeductOrderResult);
													formUndoDeductOrder.close();
												}
											}
										}),
										new BX.PopupWindowButton({
											text : "<?=GetMessage('SOD_POPUP_CANCEL')?>",
											className : "",
											events : {
												click : function()
												{
													BX('FORM_REASON_UNDO_DEDUCT').value = '';
													formUndoDeductOrder.close();
												}
											}
										})
									]);

									formUndoDeductOrder.show();
									BX('FORM_REASON_UNDO_DEDUCT').focus();
								}
							</script>
						</td>
					</tr>
				<?
				$tabControl->EndCustomField("ORDER_DEDUCTED", '');

				//order list
				$tabControl->AddSection("buyer_order", GetMessage("SOD_ORDER"));

				$tabControl->BeginCustomField("orders_list", GetMessage("SOD_ORDER"));
				?>
				<tr>
					<td colspan="2" valign="top">
						<!-- //? -->
						<table  id="BASKET_TABLE" cellpadding="3" cellspacing="1" border="0" width="100%" class="internal">

						<tr class="heading">
							<? getColumnsHeaders($arUserColumns, "detail", false); ?>
						</tr>
						<?
						$bXmlId = COption::GetOptionString("sale", "show_order_product_xml_id", "N");
						$arCurFormat = CCurrencyLang::GetCurrencyFormat($arOrder["CURRENCY"]);
						$CURRENCY_FORMAT = trim(str_replace("#", '', $arCurFormat["FORMAT_STRING"]));
						$ORDER_TOTAL_PRICE = 0;
						$ORDER_TOTAL_WEIGHT = 0;
						$arFilterRecomendet = array();
						$arBasketProps = array();

						$bUseCatalog = (CModule::IncludeModule("catalog")) ? true : false;
						$bUseIblock = (CModule::IncludeModule("iblock")) ? true : false;

						$arBasketItems = getMeasures($arBasketItems);

						if(!empty($arBasketId))
						{
							//select props from basket
							$arPropsFilter = array("BASKET_ID" => $arBasketId);
							if ($bXmlId == "N")
								$arPropsFilter["!CODE"] = array("PRODUCT.XML_ID", "CATALOG.XML_ID");

							$dbBasketPropsTmp = CSaleBasket::GetPropsList(
								array("BASKET_ID" => "ASC", "SORT" => "ASC", "NAME" => "ASC"),
								$arPropsFilter,
								false,
								false,
								array("ID", "BASKET_ID", "NAME", "VALUE", "CODE", "SORT")
							);
							while ($arBasketPropsTmp = $dbBasketPropsTmp->Fetch())
								$arBasketProps[$arBasketPropsTmp["BASKET_ID"]][] = $arBasketPropsTmp;

							$arBasketElement = array();
							$arElementData = array();

							if (!empty($arElementId)) // get properties for iblock elements and their parents (if any)
							{
								$arSelect = array_merge(
									array("ID", "PREVIEW_PICTURE", "DETAIL_PICTURE", "IBLOCK_TYPE_ID", "IBLOCK_ID", "IBLOCK_SECTION_ID"),
									$arSelectProps
								);

								$arProductData = getProductProps($arElementId, $arSelect);

								foreach ($arProductData as $key => &$value)
								{
									if (array_key_exists($value["ID"], $arSku2Parent)) // if sku element
									{
										if ($value["PREVIEW_PICTURE"] == "")
											$value["PREVIEW_PICTURE"] = $arProductData[$arSku2Parent[$value["ID"]]]["PREVIEW_PICTURE"];

										if ($value["DETAIL_PICTURE"] == "")
											$value["DETAIL_PICTURE"] = $arProductData[$arSku2Parent[$value["ID"]]]["DETAIL_PICTURE"];
									}

									$arBasketElement[$value["ID"]] = $value;
									$arBasketPropsValues[$key] = $value;
								}
								unset($value);

								// if sku element doesn't have some property value - we'll show parent element value instead
								foreach ($arBasketPropsValues as $key => &$arRecord)
								{
									if (array_key_exists($key, $arSku2Parent))
									{
										foreach ($arSelectProps as $field)
										{
											$fieldVal = $field."_VALUE";
											$parentId = $arSku2Parent[$key];

											if ((!isset($arRecord[$fieldVal]) || (isset($arRecord[$fieldVal]) && $arRecord[$fieldVal] == ''))
												&& (isset($arProductData[$parentId][$fieldVal]) && !empty($arProductData[$parentId][$fieldVal]))) // fieldVal can be array or string
											{
												$arRecord[$fieldVal] = $arProductData[$parentId][$fieldVal];
											}
										}
									}
								}
								unset($arRecord);
							}

							$productNumber = 0;
							foreach ($arBasketItems as $arItem)
							{
								if (!CSaleBasketHelper::isSetItem($arItem))
								{
									$ORDER_TOTAL_PRICE += ($arItem["PRICE"] + $arItem["DISCOUNT_PRICE"]) * $arItem["QUANTITY"];
									$arFilterRecomendet[] = $arItem["PRODUCT_ID"];
								}

								if (!CSaleBasketHelper::isSetParent($arItem))
								{
									$ORDER_TOTAL_WEIGHT += FloatVal($arItem["WEIGHT"] * $arItem["QUANTITY"]);
								}

								$hidden = "";
								$setItemClass = "";
								if (CSaleBasketHelper::isSetItem($arItem))
								{
									$hidden = "style=\"display:none\"";
									$setItemClass = "class=\"set_item_".$arItem["SET_PARENT_ID"]."\"";
								}
							?>
							<tr <?=$hidden?> <?=$setItemClass?>>
								<?
								if (!CSaleBasketHelper::isSetItem($arItem))
									$productNumber++;

								foreach ($arUserColumns as $columnCode => $columnName)
								{
									if ($columnCode == "COLUMN_NUMBER")
									{
										?>
										<td class="COLUMN_NUMBER">
											<div><?=(!CSaleBasketHelper::isSetItem($arItem)) ? $productNumber : ""?></div>
										</td>
										<?
									}

									if ($columnCode == "COLUMN_IMAGE")
									{
										?>
										<td class="COLUMN_IMAGE">
											<?
											$productImg = "";

											if ($bUseIblock)
											{
												$arProductInfo = $arBasketElement[$arItem["PRODUCT_ID"]];

												if($arProductInfo["PREVIEW_PICTURE"] != "")
													$productImg = $arProductInfo["PREVIEW_PICTURE"];
												elseif($arProductInfo["DETAIL_PICTURE"] != "")
													$productImg = $arProductInfo["DETAIL_PICTURE"];
											}

											if ($productImg != "")
											{
												$arFile = CFile::GetFileArray($productImg);
												$productImg = CFile::ResizeImageGet($arFile, array('width'=>80, 'height'=>80), BX_RESIZE_IMAGE_PROPORTIONAL, false, false);
												$arItem["PICTURE"] = $productImg;
											}

											if (is_array($arItem["PICTURE"]))
													echo '<img src="'.$arItem["PICTURE"]["src"].'" alt="" border="0" />';
												else
													echo '<div class="no_foto">'.GetMessage('SOD_NO_FOTO').'</div>';
											?>
										</td>
										<?
									}

									if ($columnCode == "COLUMN_NAME")
									{
										if ($bUseIblock)
										{
											$arProductInfo = $arBasketElement[$arItem["PRODUCT_ID"]];

											if ($arProductInfo["IBLOCK_ID"] > 0)
											{
												$arItem["EDIT_PAGE_URL"] = CIBlock::GetAdminElementEditLink($arProductInfo["IBLOCK_ID"], $arItem["PRODUCT_ID"], array(
													"find_section_section" => $arProductInfo["IBLOCK_SECTION_ID"],
													'WF' => 'Y',
												));
											}
										}
										?>
										<td class="COLUMN_NAME">

											<div class='bx-adm-bigdata-icon-medium-inner' <?=!$arItem['RECOMMENDATION']?'style="visibility: hidden"':''?>></div>

											<?
											$linkClass = (CSaleBasketHelper::isSetItem($arItem)) ? "set-item-link-name" : "";

											if ($arItem["EDIT_PAGE_URL"] <> ''):
											?>
												<a href="<?echo $arItem["EDIT_PAGE_URL"]?>" class="name-link <?=$linkClass?>" target="_blank">
											<?
											endif;

											echo trim($arItem["NAME"]);

											if ($arItem["EDIT_PAGE_URL"] <> ''):
											?>
												</a>
											<?
											endif;
											if (CSaleBasketHelper::isSetParent($arItem)):
											?>
												<div class="set-link-block">
													<a class="dashed-link show-set-link" href="javascript:void(0);" id="set_toggle_link_<?=$arItem["SET_PARENT_ID"]?>" onclick="fToggleSetItems(<?=$arItem["SET_PARENT_ID"]?>);"><?=GetMessage("SOD_SHOW_SET")?></a>
												</div>
											<?
											endif;
											?>
										</td>
										<?
									}

									if ($columnCode == "COLUMN_QUANTITY")
									{
										$measure = (isset($arItem["MEASURE_TEXT"])) ? $arItem["MEASURE_TEXT"] : "";
										?>
										<td class="COLUMN_QUANTITY">
											<?echo $arItem["QUANTITY"]."&nbsp".$measure?>
										</td>
										<?
									}

									if ($columnCode == "COLUMN_REMAINING_QUANTITY")
									{
										?>
										<td class="COLUMN_REMAINING_QUANTITY">
											<?
											$balance = 0;
											if ($arItem["MODULE"] == "catalog" && $bUseCatalog)
											{
												$ar_res = CCatalogProduct::GetByID($arItem["PRODUCT_ID"]);
												$balance = FloatVal($ar_res["QUANTITY"]);
											}
											?>
											<?echo $balance?>
										</td>
										<?
									}

									if ($columnCode == "COLUMN_PROPS")
									{
										?>
										<td class="COLUMN_PROPS">
											<?
											if (!empty($arBasketProps[$arItem["ID"]]) && is_array($arBasketProps[$arItem["ID"]]))
											{
												foreach ($arBasketProps[$arItem["ID"]] as &$val)
												{
													echo htmlspecialcharsex($val["NAME"].": ".$val["VALUE"])."<br />";
												}
												if (isset($val))
													unset($val);
											}
											?>
										</td>
										<?
									}

									if ($columnCode == "COLUMN_PRICE")
									{
										?>
										<td class="COLUMN_PRICE" nowrap>
												<?
												$priceDiscount = $priceBase = ($arItem["DISCOUNT_PRICE"] + $arItem["PRICE"]);
												if(DoubleVal($priceBase) > 0)
													$priceDiscount = roundEx(($arItem["DISCOUNT_PRICE"] * 100) / $priceBase, SALE_VALUE_PRECISION);
												?>

												<div class="edit_price">
													<span class="default_price_product" >
														<span class="formated_price"><?=CCurrencyLang::CurrencyFormat($arItem["PRICE"], $arItem["CURRENCY"], false);?></span>
													</span>
													<span class="currency_price"><?=$CURRENCY_FORMAT?></span>
												</div>
												<?
												if (0 < $priceDiscount)
												{
													?><div class="base_price" id="DIV_BASE_PRICE_WITH_DISCOUNT_<?=$arItem["PRODUCT_ID"]?>">
														<?=CCurrencyLang::CurrencyFormat($priceBase, $arItem["CURRENCY"], false);?>
														<span class="currency_price"><?=$CURRENCY_FORMAT?></span>
													</div><?
													if ('Y' != $arItem["CUSTOM_PRICE"])
													{
														?><div class="discount">(<? echo GetMessage('SOD_PRICE_DISCOUNT')." ".$priceDiscount?>%)</div><?
													}
												}
												?><div class="base_price_title">
													<?=('Y' == $arItem["CUSTOM_PRICE"]) ? GetMessage("SOD_BASE_CATALOG_PRICE") : $arItem["NOTES"];?>
												</div>
										</td>
										<?
									}

									if ($columnCode == "COLUMN_SUM")
									{
										?>
										<td class="COLUMN_SUM" nowrap>
											<?
											if (!CSaleBasketHelper::isSetItem($arItem)):
											?>
												<div><?=CCurrencyLang::CurrencyFormat(($arItem["QUANTITY"] * $arItem["PRICE"]), $arItem["CURRENCY"], false);?> <span><?=$CURRENCY_FORMAT?></span></div>
											<?
											endif;
											?>
										</td>
										<?
									}

									if (mb_substr($columnCode, 0, 9) == "PROPERTY_")
									{
										?>
										<td class="property_field <?=$columnCode?>">
											<?=getIblockPropInfo($arBasketPropsValues[$arItem["PRODUCT_ID"]][$columnCode."_VALUE"], $arIblockProps[$columnCode], array("WIDTH" => 90, "HEIGHT" => 90), $ID);?>
										</td>
										<?
									}
								}
								?>
							</tr>
							<?
							}//end while order
						}
						?>
						</table>
					</td>
				</tr>
				<script>
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
							BX("set_toggle_link_" + setParentId).innerHTML = '<?=GetMessage("SOD_HIDE_SET")?>';
						else
							BX("set_toggle_link_" + setParentId).innerHTML = '<?=GetMessage("SOD_SHOW_SET")?>';
					}
				</script>
				<?
				$tabControl->EndCustomField("orders_list");

				$tabControl->BeginCustomField("orders_itog", GetMessage("SOD_ORDER_ITOG"));
				?>
				<tr>
					<td colspan="2" valign="top">
						<br>
						<table width="100%" class="order_summary">
						<tr>
							<td class="load_product" valign="top">
								<table width="100%" class="itog_header"><tr><td><?=GetMessage('SOD_SUBTAB_RECOM_REQUEST');?></td></tr></table>
								<br>

								<div id="tabs">
									<?
									$displayNone = "block";
									$displayNoneBasket = "block";
									$displayNoneViewed = "block";

									$arRecommendedResult = CSaleProduct::GetRecommendetProduct($arOrder["USER_ID"], $arOrder["LID"], $arFilterRecomendet);
									$recomCnt = count($arRecommendedResult);

									if ($recomCnt > 2)
									{
										$arTmp = array();
										$arTmp[] = $arRecommendedResult[0];
										$arTmp[] = $arRecommendedResult[1];
										$arRecommendedResult = $arTmp;
									}
									if ($recomCnt <= 0)
										$displayNone = "none";

									$arErrors = array();
									$arFuserItems = CSaleUser::GetList(array("USER_ID" => intval($arOrder["USER_ID"])));

									$arCartWithoutSetItems = array();
									$arTmpShoppingCart = CSaleBasket::DoGetUserShoppingCart($arOrder["LID"], $arOrder["USER_ID"], $arFuserItems["ID"], $arErrors, array());
									if (is_array($arTmpShoppingCart))
									{
										foreach ($arTmpShoppingCart as $arCartItem)
										{
											if (CSaleBasketHelper::isSetItem($arCartItem))
												continue;

											$arCartWithoutSetItems[] = $arCartItem;
										}
									}
									$basketCnt = count($arCartWithoutSetItems);
									if ($basketCnt > 2)
									{
										$arTmp = array();
										$arTmp[] = $arCartWithoutSetItems[0];
										$arTmp[] = $arCartWithoutSetItems[1];
										$arCartWithoutSetItems = $arTmp;
									}
									if ($basketCnt <= 0)
										$displayNoneBasket = "none";

									///
									$arViewed = array();
									$arViewedIds = array();
									$viewedCount = 0;
									$mapViewed = array();
									if (CModule::includeModule("catalog"))
									{
										$viewedIterator = \Bitrix\Catalog\CatalogViewedProductTable::getList(array(
											'order' => array("DATE_VISIT" => "DESC"),
											'filter' => array('FUSER_ID' => $arFuserItems["ID"], "SITE_ID" =>$arOrder["LID"] ),
											'select' => array("ID", "FUSER_ID", "DATE_VISIT", "PRODUCT_ID", "LID" => "SITE_ID", "NAME" => "ELEMENT.NAME", "PREVIEW_PICTURE" => "ELEMENT.PREVIEW_PICTURE", "DETAIL_PICTURE" => "ELEMENT.DETAIL_PICTURE" )
										));

										while($viewed = $viewedIterator->fetch())
										{
											$viewed['MODULE'] = 'catalog';
											$arViewed[$viewedCount] = $viewed;
											$arViewedIds[] = $viewed['PRODUCT_ID'];
											$mapViewed[$viewed['PRODUCT_ID']] = $viewedCount;
											$viewedCount++;
										}
										unset($viewedCount);
										$baseGroup = CCatalogGroup::getBaseGroup();
										if (!empty($arViewedIds))
										{
											$priceIterator = CPrice::getList(
												array(),
												array("PRODUCT_ID" => $arViewedIds, 'CATALOG_GROUP_ID' => $baseGroup['ID']), false, false, array("PRODUCT_ID", "PRICE", "CURRENCY"));
											while($productPrice = $priceIterator->fetch() )
											{
												if (isset($mapViewed[$productPrice['PRODUCT_ID']]))
												{
													$key = $mapViewed[$productPrice['PRODUCT_ID']];
													$arViewed[$key]["PRICE"] = $productPrice["PRICE"];
													$arViewed[$key]["CURRENCY"] = $productPrice["CURRENCY"];
												}
											}
										}
										$viewedCnt = count($arViewed);
										$arViewed = array_slice($arViewed, 0, 2);
										if (count($arViewed) <= 0)
											$displayNoneViewed = "none";
									}
									else
									{
										$displayNoneViewed = "none";
									}

									$tabBasket = "tabs";
									$tabViewed = "tabs";

									if ($displayNoneBasket == 'none' && $displayNone == 'none' && $displayNoneViewed == 'block')
										$tabViewed .= " active";
									if ($displayNoneBasket == 'block' && $displayNone == 'none')
										$tabBasket .= " active";

									?>
									<div id="tab_1" style="display:<?=$displayNone?>"       class="tabs active"     onClick="fTabsSelect('buyer_recmon', this);" ><?=GetMessage('SOD_SUBTAB_RECOMENET')?></div>
									<div id="tab_2" style="display:<?=$displayNoneBasket?>" class="<?=$tabBasket?>" onClick="fTabsSelect('buyer_basket', this);"><?=GetMessage('SOD_SUBTAB_BASKET')?></div>
									<div id="tab_3" style="display:<?=$displayNoneViewed?>" class="<?=$tabViewed?>" onClick="fTabsSelect('buyer_viewed', this);"><?=GetMessage('SOD_SUBTAB_LOOKED')?></div>

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
									<div id="buyer_recmon" class="tabstext active" style="display:<?=$displayNone?>">
										<?echo fGetFormatedProductData($arOrder["USER_ID"], $arOrder["LID"], $arRecommendedResult, $recomCnt, $arOrder["CURRENCY"], 'recom', $crmMode);?>
									</div>

									<div id="buyer_basket" class="tabstext active" style="display:<?=$displayNoneBasket?>">
									<?
										if (count($arCartWithoutSetItems) > 0)
											echo fGetFormatedProductData($arOrder["USER_ID"], $arOrder["LID"], $arCartWithoutSetItems, $basketCnt, $arOrder["CURRENCY"], 'basket', $crmMode);
									?>
									</div>

									<div id="buyer_viewed" class="tabstext active" style="display:<?=$displayNoneViewed?>">
									<?
										if (count($arViewed) > 0)
											echo fGetFormatedProductData($arOrder["USER_ID"], $arOrder["LID"], $arViewed, $viewedCnt, $arOrder["CURRENCY"], 'viewed', $crmMode);
									?>
									</div>
								</div>
								<script>
								function fTabsSelect(tabText, el)
								{
									BX('tab_1').className = "tabs";
									BX('tab_2').className = "tabs";
									BX('tab_3').className = "tabs";

									BX(el).className = "tabs active";
									BX(el).className = "tabs active";
									BX(el).style.display = 'block';

									BX('buyer_recmon').className = "tabstext";
									BX('buyer_basket').className = "tabstext";
									BX('buyer_viewed').className = "tabstext";
									BX('buyer_recmon').style.display = 'none';
									BX('buyer_basket').style.display = 'none';
									BX('buyer_viewed').style.display = 'none';

									BX(tabText).style.display = 'block';
									BX(tabText).className = "tabstext active";
								}
								</script>
							</td>
							<td class="summary" valign="top">
								<div class="order-itog">
									<table>
										<tr>
											<td class="title"><?echo GetMessage("SOD_TOTAL_PRICE")?></td>
											<td nowrap style="white-space:nowrap;"><?=SaleFormatCurrency($ORDER_TOTAL_PRICE, $arOrder["CURRENCY"]);?></td>
										</tr>
										<tr class="price">
											<td class="title"><?echo GetMessage("SOD_TOTAL_PRICE_WITH_DISCOUNT_MARGIN")?></td>
											<td nowrap style="white-space:nowrap;"><?=SaleFormatCurrency($orderBasketPrice, $arOrder["CURRENCY"]);?></td>
										</tr>
										<tr>
											<td class="title"><?echo GetMessage("SOD_TOTAL_PRICE_DELIVERY")?></td>
											<td nowrap style="white-space:nowrap;"><?=SaleFormatCurrency($arOrder["PRICE_DELIVERY"], $arOrder["CURRENCY"]);?></td>
										</tr>

										<?if (floatval($arOrder["DISCOUNT_VALUE"]) > 0):?>
										<tr class="price">
											<td class="title" >
												<?echo GetMessage("NEWO_TOTAL_DISCOUNT_PRICE_VALUE")?>
											</td>
											<td nowrap style="white-space:nowrap;">
													<div><?=SaleFormatCurrency($arOrder["DISCOUNT_VALUE"], $arOrder["CURRENCY"]);?></div>
											</td>
										</tr>
										<?endif;?>

										<?if ($ORDER_TOTAL_WEIGHT > 0):?>
										<tr>
											<td class="title"><?echo GetMessage("NEWO_TOTAL_WEIGHT")?></td>
											<td nowrap style="white-space:nowrap;">
												<?=roundEx(DoubleVal($ORDER_TOTAL_WEIGHT/$WEIGHT_KOEF), SALE_WEIGHT_PRECISION)." ".$WEIGHT_UNIT;?>
											</td>
										</tr>
										<?endif;?>

										<tr class="itog">
											<td class="ileft"><div style="white-space:nowrap;"><?echo GetMessage("SOD_TOTAL_PRICE_TOTAL")?></div></td>
											<td class="iright" nowrap><div style="white-space:nowrap;"><?=SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"]);?></div></td>
										</tr>
										<?if (floatval($arOrder["SUM_PAID"]) > 0 && $arOrder["PAYED"] != "Y"):?>
											<tr class="price">
												<td class="title"><?echo GetMessage("SOD_TOTAL_PRICE_PAYED")?></td>
												<td nowrap style="white-space:nowrap;"><?=SaleFormatCurrency($arOrder["SUM_PAID"], $arOrder["CURRENCY"]);?></td>
											</tr>
										<?endif;?>
										<?if ($arOrder["PAYED"] == "Y"):?>
											<tr class="price">
												<td class="title"><?echo GetMessage("SOD_TOTAL_PRICE_PAYED")?></td>
												<td nowrap style="white-space:nowrap;"><?=SaleFormatCurrency($arOrder["PRICE"], $arOrder["CURRENCY"]);?></td>
											</tr>
										<?endif;?>
									</table>
								</div>
							</td>
						</tr>
						</table>

						<script>
								/*
								* click on recommendet More
								*/
								function fGetMoreProduct(type)
								{
									BX.showWait();
									productData = <? echo CUtil::PhpToJSObject($arFilterRecomendet); ?>;
									var userId = '<?=$arOrder["USER_ID"]?>';
									var fUserId = '<?=$arFuserItems["ID"]?>';
									var currency = '<?=$arOrder["CURRENCY"]?>';
									var lid = '<?=$arOrder["LID"]?>';

									BX.ajax.post('/bitrix/admin/sale_order_detail.php', '<?=CUtil::JSEscape(bitrix_sessid_get())?>&ORDER_AJAX=Y&type='+type+'&arProduct='+productData+'&currency='+currency+'&LID='+lid+'&userId='+userId+'&fUserId='+fUserId+'&ID=<?=$ID?>', fGetMoreProductResult);
								}

								function fGetMoreProductResult(res)
								{
									BX.closeWait();
									var rs = eval( '('+res+')' );

									if (rs["ITEMS"].length > 0)
									{
										if (rs["TYPE"] == 'basket')
											BX("buyer_basket").innerHTML = rs["ITEMS"];
										if (rs["TYPE"] == 'recom')
											BX("buyer_recmon").innerHTML = rs["ITEMS"];
										if (rs["TYPE"] == 'viewed')
											BX("buyer_viewed").innerHTML = rs["ITEMS"];
									}
								}
						</script>
					</td>
				</tr>
				<?
				$tabControl->EndCustomField("orders_itog");



		$tabControl->BeginNextFormTab();

			$tabControl->BeginCustomField("TRANSACT", GetMessage("SODN_TAB_TRANSACT"));
				?>
				<tr>
					<td colspan="2">
					<?
					$dbTransact = CSaleUserTransact::GetList(
							array("TRANSACT_DATE" => "DESC"),
							array("ORDER_ID" => $ID),
							false,
							false,
							array("ID", "USER_ID", "AMOUNT", "CURRENCY", "DEBIT", "ORDER_ID", "DESCRIPTION", "NOTES", "TIMESTAMP_X", "TRANSACT_DATE")
						);
					?>
					<table cellpadding="3" cellspacing="1" border="0" width="100%" class="adm-list-table" style="border:1px solid #CCC">
						<tr class="adm-list-table-header">
							<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?echo GetMessage("SOD_TRANS_DATE")?></div></td>
							<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?echo GetMessage("SOD_TRANS_USER")?></div></td>
							<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?echo GetMessage("SOD_TRANS_SUM")?></div></td>
							<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?echo GetMessage("SOD_TRANS_DESCR")?></div></td>
							<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?echo GetMessage("SOD_TRANS_COMMENT")?></div></td>
						</tr>
						<?
						$bNoTransact = True;
						while ($arTransact = $dbTransact->Fetch())
						{
							$bNoTransact = False;
							?>
							<tr class="adm-list-table-row">
								<td class="adm-list-table-cell"><?= $arTransact["TRANSACT_DATE"]; ?></td>
								<td class="adm-list-table-cell">
									<?echo GetFormatedUserName($arTransact["USER_ID"]);?>
								</td>
								<td class="adm-list-table-cell">
									<?
									echo (($arTransact["DEBIT"] == "Y") ? "+" : "-");
									echo SaleFormatCurrency($arTransact["AMOUNT"], $arTransact["CURRENCY"]);
									?>
								</td>
								<td class="adm-list-table-cell">
									<?
									if (array_key_exists($arTransact["DESCRIPTION"], $arTransactTypes))
										echo htmlspecialcharsEx($arTransactTypes[$arTransact["DESCRIPTION"]]);
									else
										echo htmlspecialcharsEx($arTransact["DESCRIPTION"]);
									?>
								</td>
								<td class="adm-list-table-cell" align="right">
									<?echo htmlspecialcharsEx($arTransact["NOTES"]) ?>
								</td>
							</tr>
							<?
						}

						if ($bNoTransact)
						{
							?>
							<tr>
								<td colspan="5" align="center">
									<?echo GetMessage("SOD_NO_TRANS")?>
								</td>
							</tr>
							<?
						}
						?>
					</table>
					</td>
				</tr>
				<?
			$tabControl->EndCustomField("TRANSACT", '');


		$tabControl->BeginNextFormTab();
			$tabControl->BeginCustomField("ORDER_HISTORY", GetMessage("SODN_TAB_HISTORY"));
			?>
			<tr>
				<td colspan="2" valign="top">
					<div id="trans-history"></div>
				</td>
			</tr>
			<?
			$tabControl->EndCustomField("ORDER_HISTORY", '');

		$tabControl->Show();
	}
}
?>

<div id="tr-sourse" style="display:none;">
	<form name="find_form5" method="GET" action="<? echo $APPLICATION->GetCurPage(); ?>?">
	<input type="hidden" name="ID" value="<?=$ID?>">

	<?
	$arFilterFieldsTmp = array(
		"filter_user" => GetMessage("SOA_ROW_BUYER"),
		"filter_date_history" => GetMessage("SALE_F_DATE"),
		"filter_status_id" => GetMessage("SALE_F_DATE_UPDATE"),
		"filter_payed" => GetMessage("SALE_F_ID"),
		"filter_allow_delivery" => GetMessage("SALE_F_LANG_CUR"),
		"filter_canceled" => GetMessage("SOA_F_PRICE"),
		"filter_deducted" => GetMessage("SOA_F_PRICE"),
		"filter_marked" => GetMessage("SOA_F_PRICE")
	);

	$oFilter = new CAdminFilter(
		$sTableID_tab5."_filters",
		$arFilterFieldsTmp
	);

	$oFilter->SetDefaultRows(array("filter_user"));

	$oFilter->Begin();
	?>
	<tr>
		<td><?=GetMessage('SOD_HIST_H_USER')?>:</td>
		<td>
			<?echo FindUserID("filter_user", $filter_user, "", "find_form5");?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage('SOD_HIST_H_DATE')?>:</td>
		<td>
			<?echo CalendarPeriod("filters_date_history_from", $filters_date_history_from, "filters_date_history_to", $filters_date_history_to, "find_form5", "Y")?>

		</td>
	</tr>
	<tr>
		<td><?=GetMessage('SOD_HIST_TYPE')?>:</td>
		<td>
			<select name="filter_type">
				<option value=""><?echo GetMessage("SOD_HIST_ALL")?></option>
				<? foreach ($arOperations as $type => $name)
				{ ?>
					<option value="<?=$type?>"<?if ($filter_type== $type) echo " selected"?>><?=$name?></option>
				<? } ?>
			</select>
		</td>
	</tr>
	<?
	$oFilter->Buttons(
		array(
			"table_id" => $sTableID_tab5,
			"url" => $APPLICATION->GetCurPage(),
			"form" => "find_form5"
		)
	);
	$oFilter->End();
	?>
	</form>
	<?$lAdmin_tab5->DisplayList(array("FIX_HEADER" => false, "FIX_FOOTER" => false));?>
</div>

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
</div>

	<script>
			var wind = new BX.PopupWindow('popup_sku', this, {
				offsetTop : 10,
				offsetLeft : 0,
				autoHide : true,
				closeByEsc : true,
				closeIcon : true,
				titleBar : true,
				draggable: {restrict:true},
				titleBar: {content: BX.create("span", {html: '', 'props': {'className': 'sale-popup-title-bar'}})},
				content : BX("popup_form_sku_order"),
				buttons: [
					new BX.PopupWindowButton({
						text : '<?=GetMessageJS('SOD_POPUP_CAN_BUY_NOT');?>',
						id : "popup_sku_save",
						events : {
							click : function() {
								if (BX('popup-params-product') && BX('popup-params-product').value.length > 0)
								{
									window.location = BX('popup-params-product').value;
									wind.close();
								}
							}
						}
					}),
					new BX.PopupWindowButton({
						text : '<?=GetMessageJS('SOD_POPUP_CLOSE');?>',
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
									addHtml(lastPropCode, arSKU, type);
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
							BX('popup-params-product').value = "/bitrix/admin/sale_order_new.php?lang=<? echo LANGUAGE_ID; ?>&user_id="+arSKU[i]["USER_ID"]+"&LID="+arSKU[i]["LID"]+"&product["+arSKU[i]["ID"]+"]=1";
							message = BX.message('PRODUCT_ADD');
						}
						else
						{
							BX('popup-params-product').value = '';
							message = BX.message('PRODUCT_NOT_ADD');
						}

						BX.findChild(BX('popup_sku_save'), {'className': 'popup-window-button-text' }, true, false).innerHTML = message;
					}

					if (arSKU[i]["ID"] == selectedSkuId)
						break;
				}
			}

		//BX.ready(function(){setTimeout(function(){BX('trans-history').appendChild(BX('tr-sourse'));  BX.show(BX('tr-sourse'));},300);})
		BX.ready(function(){
			BX('trans-history').appendChild(BX('tr-sourse'));
			BX.show(BX('tr-sourse'));
		})
	</script>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
