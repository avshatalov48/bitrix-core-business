<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Session\Session;
use Bitrix\Currency;
use Bitrix\Sale;
use Bitrix\Sale\DiscountCouponsManager;

class CAllSaleBasket
{
	public const TYPE_SET = 1;

	protected static array $currencySiteList = [];
	protected static array $currencyList = [];

	/**
	 * Checks if the basket item has product provider class implementing IBXSaleProductProvider interface
	 *
	 * @param array $arBasketItem - array of basket item fields
	 * @return mixed
	 */
	public static function GetProductProvider($arBasketItem)
	{
		if (!is_array($arBasketItem)
			|| empty($arBasketItem)
			|| !isset($arBasketItem["MODULE"])
			|| !isset($arBasketItem["PRODUCT_PROVIDER_CLASS"])
			|| ($arBasketItem["PRODUCT_PROVIDER_CLASS"] == '')
		)
			return false;

		$productProviderClass = $arBasketItem["PRODUCT_PROVIDER_CLASS"];

		if (CModule::IncludeModule($arBasketItem["MODULE"])
			&& class_exists($productProviderClass)
			&& ((array_key_exists("IBXSaleProductProvider", class_implements($productProviderClass))))
		)
		{
			return $productProviderClass;
		}

		$providerClass = Sale\Internals\Catalog\Provider::getProviderEntity($arBasketItem["PRODUCT_PROVIDER_CLASS"]);
		if ($providerClass instanceof Sale\SaleProviderBase)
		{
			return '\Bitrix\Catalog\Product\CatalogProviderCompatibility';
		}

		return false;
	}

	/**
	 * Removes old product subscription
	 *
	 * @param string $LID - site for cleaning
	 * @return bool
	 */
	public static function ClearProductSubscribe($LID)
	{
		CSaleBasket::_ClearProductSubscribe($LID);

		return "CSaleBasket::ClearProductSubscribe('".$LID."');";
	}

	/**
	 * Sends product subscription letter
	 *
	 * @param integer $ID - code product
	 * @param string $MODULE - module product
	 * @return bool
	 */
	public static function ProductSubscribe($ID, $MODULE)
	{
		$ID = (int)$ID;
		$MODULE = trim($MODULE);

		if ($ID <= 0 || $MODULE  == '')
			return false;

		$arSubscribeProd = array();
		$subscribeProd = COption::GetOptionString("sale", "subscribe_prod", "");
		if ($subscribeProd != '')
			$arSubscribeProd = unserialize($subscribeProd, ['allowed_classes' => false]);

		$rsItemsBasket = CSaleBasket::GetList(
			array("USER_ID" => "DESC", "LID" => "ASC"),
			array(
				"PRODUCT_ID" => $ID,
				"SUBSCRIBE" => "Y",
				"CAN_BUY" => "N",
				"ORDER_ID" => "NULL",
				">USER_ID" => "0",
				"MODULE" => $MODULE
			),
			false,
			false,
			array(
				'ID',
				'FUSER_ID',
				'USER_ID',
				'MODULE', 'PRODUCT_ID', 'CURRENCY', 'DATE_INSERT', 'QUANTITY', 'LID', 'DELAY', 'CALLBACK_FUNC', 'SUBSCRIBE', 'PRODUCT_PROVIDER_CLASS')
		);
		while ($arItemsBasket = $rsItemsBasket->Fetch())
		{
			$LID = $arItemsBasket["LID"];

			if (isset($arSubscribeProd[$LID]) && $arSubscribeProd[$LID]["use"] == "Y")
			{
				$sendEmailList = array();
				$USER_ID = $arItemsBasket['USER_ID'];
				$arMailProp = array();
				$arPayerProp = array();

				// load user profiles
				$arUserProfiles = CSaleOrderUserProps::DoLoadProfiles($USER_ID);
				if (!empty($arUserProfiles))
				{
					// select person type
					$dbPersonType = CSalePersonType::GetList(array("SORT" => "ASC"), array("LID" => $LID), false, false, array('ID'));
					while ($arPersonType = $dbPersonType->Fetch())
					{
						// select ID props is mail
						$dbProperties = CSaleOrderProps::GetList(
							array(),
							array("PERSON_TYPE_ID" => $arPersonType["ID"], "IS_EMAIL" => "Y", "ACTIVE" => "Y"),
							false,
							false,
							array('ID', 'PERSON_TYPE_ID')
						);
						while ($arProperties = $dbProperties->Fetch())
							$arMailProp[$arProperties["PERSON_TYPE_ID"]] = $arProperties["ID"];

						// select ID props is name
						$dbProperties = CSaleOrderProps::GetList(
							array(),
							array("PERSON_TYPE_ID" => $arPersonType["ID"], "IS_PAYER" => "Y", "ACTIVE" => "Y"),
							false,
							false,
							array('ID', 'PERSON_TYPE_ID')
						);
						while ($arProperties = $dbProperties->Fetch())
							$arPayerProp[$arProperties["PERSON_TYPE_ID"]] = $arProperties["ID"];
					}//end while
				}

				$rsUser = CUser::GetByID($USER_ID);
				$arUser = $rsUser->Fetch();
				$userName = $arUser["LAST_NAME"];
				if ($userName != '')
					$userName .= " ";
				$userName .= $arUser["NAME"];

				// select of user name to be sent
				$arUserSendName = array();
				if (!empty($arUserProfiles) && !empty($arPayerProp))
				{
					foreach($arPayerProp as $personType => $namePropID)
					{
						if (isset($arUserProfiles[$personType]))
						{
							foreach($arUserProfiles[$personType] as $profiles)
							{
								if (isset($profiles["VALUES"][$namePropID]) && $profiles["VALUES"][$namePropID] != '')
								{
									$arUserSendName[$personType] = trim($profiles["VALUES"][$namePropID]);
									break;
								}
								else
								{
									$arUserSendName[$personType] = $userName;
								}
							}
						}
						else
						{
							$arUserSendName[$personType] = $userName;
						}
					}
				}
				else
				{
					$arUserSendName[] = $userName;
				}

				// select of e-mail to be sent
				$arUserSendMail = array();
				if (!empty($arUserProfiles) && !empty($arMailProp))
				{
					foreach($arMailProp as $personType => $mailPropID)
					{
						if (isset($arUserProfiles[$personType]))
						{
							foreach($arUserProfiles[$personType] as $profiles)
							{
								if (isset($profiles["VALUES"][$mailPropID]) && $profiles["VALUES"][$mailPropID] != '')
								{
									$arUserSendMail[$personType] = trim($profiles["VALUES"][$mailPropID]);
									break;
								}
								else
								{
									$arUserSendMail[$personType] = $arUser["EMAIL"];
								}
							}
						}
						else
						{
							$arUserSendMail[$personType] = $arUser["EMAIL"];
						}
					}
				}
				else
				{
					$arUserSendMail[] = $arUser["EMAIL"];
				}

				/** @var $productProvider IBXSaleProductProvider */
				if ($productProvider = CSaleBasket::GetProductProvider($arItemsBasket))
				{
					$arCallback = $productProvider::GetProductData(array(
						"PRODUCT_ID" => $ID,
						"QUANTITY" => 1,
						"RENEWAL" => "N",
						"USER_ID" => $USER_ID,
						"SITE_ID" => $LID,
						"BASKET_ID" => $arItemsBasket["ID"],
					));
				}
				elseif (isset($arItemsBasket["CALLBACK_FUNC"]) && !empty($arItemsBasket["CALLBACK_FUNC"]))
				{
					$arCallback = CSaleBasket::ExecuteCallbackFunction(
						trim($arItemsBasket["CALLBACK_FUNC"]),
						$MODULE,
						$ID,
						1,
						"N",
						$USER_ID,
						$LID
					);
				}

				if (!empty($arCallback))
				{
					$arCallback["QUANTITY"] = 1;
					$arCallback["DELAY"] = "N";
					$arCallback["SUBSCRIBE"] = "N";
					CSaleBasket::Update($arItemsBasket["ID"], $arCallback);
				}

				//send mail
				if (!empty($arUserSendMail) && !empty($arCallback))
				{
					$eventName = "SALE_SUBSCRIBE_PRODUCT";
					$event = new CEvent;

					foreach ($arUserSendMail as $personType => $mail)
					{
						$checkMail = mb_strtolower($mail);
						if (isset($sendEmailList[$checkMail]))
							continue;
						$sendName = $userName;
						if (isset($arUserSendName[$personType]) && $arUserSendName[$personType] != '')
							$sendName = $arUserSendName[$personType];

						$arFields = array(
							"EMAIL" => $mail,
							"USER_NAME" => $sendName,
							"NAME" => $arCallback["NAME"],
							"PAGE_URL" => CHTTP::URN2URI($arCallback["DETAIL_PAGE_URL"]),
							"SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$_SERVER["SERVER_NAME"]),
						);

						$event->Send($eventName, $LID, $arFields, "N");
						$sendEmailList[$checkMail] = true;
					}
				}
			}// end if bSend
		}// end while $arItemsBasket

		return true;
	}

	public static function DoGetUserShoppingCart($siteId, $userId, $shoppingCart, &$arErrors, $arCoupons = array(), $orderId = 0, $enableCustomCurrency = false)
	{
		$isOrderConverted = Option::get("main", "~sale_converted_15", 'Y');

		$siteId = trim($siteId);
		if (empty($siteId))
		{
			$arErrors[] = array("CODE" => "PARAM", "TEXT" => Loc::getMessage('SKGB_PARAM_SITE_ERROR'));
			return null;
		}

		$userId = intval($userId);

		if (!is_array($shoppingCart))
		{
			if (intval($shoppingCart)."|" != $shoppingCart."|")
			{
				$arErrors[] = array("CODE" => "PARAM", "TEXT" => Loc::getMessage('SKGB_PARAM_SK_ERROR'));
				return null;
			}
			$shoppingCart = intval($shoppingCart);

			$dbShoppingCartItems = CSaleBasket::GetList(
				array("NAME" => "ASC"),
				array(
					"FUSER_ID" => $shoppingCart,
					"LID" => $siteId,
					"ORDER_ID" => "NULL",
					"DELAY" => "N",
				),
				false,
				false,
				array(
					"ID", "LID", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY",
					"CAN_BUY", "PRICE", "WEIGHT", "NAME", "CURRENCY", "CATALOG_XML_ID",
					"VAT_RATE", "NOTES", "DISCOUNT_PRICE", "DETAIL_PAGE_URL", "PRODUCT_PROVIDER_CLASS",
					"RESERVED", "DEDUCTED", "RESERVE_QUANTITY", "DIMENSIONS", "TYPE", "SET_PARENT_ID"
				)
			);
			$arTmp = array();
			while ($arShoppingCartItem = $dbShoppingCartItems->Fetch())
				$arTmp[] = $arShoppingCartItem;

			$shoppingCart = $arTmp;
		}

		$arOldShoppingCart = array();
		if ($orderId != 0) // for existing basket we need old data to calculate quantity delta for availability checking
		{
			$dbs = CSaleBasket::GetList(
				array("NAME" => "ASC"),
				array(
					"LID" => $siteId,
					"ORDER_ID" => $orderId,
					"DELAY" => "N",
				),
				false,
				false,
				array(
					"ID", "LID", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "PRODUCT_PRICE_ID", "PRICE",
					"QUANTITY", "DELAY", "CAN_BUY", "PRICE", "WEIGHT", "NAME", "CURRENCY",
					"CATALOG_XML_ID", "VAT_RATE", "NOTES", "DISCOUNT_PRICE", "DETAIL_PAGE_URL", "PRODUCT_PROVIDER_CLASS",
					"RESERVED", "DEDUCTED", "BARCODE_MULTI", "DIMENSIONS", "TYPE", "SET_PARENT_ID"
				)
			);
			while ($arOldShoppingCartItem = $dbs->Fetch())
				$arOldShoppingCart[$arOldShoppingCartItem["ID"]] = $arOldShoppingCartItem;
		}

		if (CSaleHelper::IsAssociativeArray($shoppingCart))
			$shoppingCart = array($shoppingCart);

		if (!empty($arCoupons))
		{
			if (!is_array($arCoupons))
				$arCoupons = array($arCoupons);
			foreach(GetModuleEvents("sale", "OnSetCouponList", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($userId, $arCoupons, array()));
			foreach ($arCoupons as &$coupon)
				$couponResult = DiscountCouponsManager::add($coupon);
			unset($coupon, $couponResult);
		}

		if(!is_bool($enableCustomCurrency))
		{
			$enableCustomCurrency = false;
		}

		$arResult = array();
		$emptyID = 1;

		foreach ($shoppingCart as $itemIndex => $arShoppingCartItem)
		{
			if ((array_key_exists("CALLBACK_FUNC", $arShoppingCartItem) && !empty($arShoppingCartItem["CALLBACK_FUNC"]))
				|| (array_key_exists("PRODUCT_PROVIDER_CLASS", $arShoppingCartItem) && !empty($arShoppingCartItem["PRODUCT_PROVIDER_CLASS"])))
			{
				// get quantity difference to check its availability

				if ($orderId != 0)
					$quantity = $arShoppingCartItem["QUANTITY"] - $arOldShoppingCart[$arShoppingCartItem["ID_TMP"]]["QUANTITY"];
				else
					$quantity = $arShoppingCartItem["QUANTITY"];

				$customPrice = (isset($arShoppingCartItem['CUSTOM_PRICE']) && $arShoppingCartItem['CUSTOM_PRICE'] == 'Y');
				$existBasketID = (isset($arShoppingCartItem['ID']) && (int)$arShoppingCartItem['ID'] > 0);
				/** @var $productProvider IBXSaleProductProvider */
				if ($productProvider = CSaleBasket::GetProductProvider($arShoppingCartItem))
				{
					if ($existBasketID)
					{
						$basketID = $arShoppingCartItem['ID'];
					}
					elseif (isset($arShoppingCartItem["ID_TMP"]))
					{
						$basketID = $arShoppingCartItem["ID_TMP"];
					}
					else
					{
						$basketID = 'tmp_'.$emptyID;
						$emptyID++;
					}
					$providerParams = array(
						"PRODUCT_ID" => $arShoppingCartItem["PRODUCT_ID"],
						"QUANTITY"   => ($quantity > 0) ? $quantity : $arShoppingCartItem["QUANTITY"],
						"RENEWAL"    => "N",
						"USER_ID"    => $userId,
						"SITE_ID"    => $siteId,
						"BASKET_ID" => $basketID,
						"CHECK_QUANTITY" => ($quantity > 0) ? "Y" : "N",
						"CHECK_COUPONS" => ('Y' == $arShoppingCartItem['CAN_BUY'] && (!array_key_exists('DELAY', $arShoppingCartItem) || 'Y' != $arShoppingCartItem['DELAY']) ? 'Y' : 'N'),
						"CHECK_PRICE" => ($customPrice ? "N" : "Y")
					);
					if (isset($arShoppingCartItem['NOTES']))
						$providerParams['NOTES'] = $arShoppingCartItem['NOTES'];
					$arFieldsTmp = $productProvider::GetProductData($providerParams);
					unset($providerParams);
				}
				else
				{
					$arFieldsTmp = CSaleBasket::ExecuteCallbackFunction(
						$arShoppingCartItem["CALLBACK_FUNC"],
						$arShoppingCartItem["MODULE"],
						$arShoppingCartItem["PRODUCT_ID"],
						$quantity,
						"N",
						$userId,
						$siteId
					);
					if (!empty($arFieldsTmp) && is_array($arFieldsTmp))
					{
						if ($customPrice)
							unset($arFieldsTmp['PRICE'], $arFieldsTmp['CURRENCY']);
					}
				}

				if (!empty($arFieldsTmp) && is_array($arFieldsTmp))
				{
					$arFieldsTmp["CAN_BUY"] = "Y";
					$arFieldsTmp["SUBSCRIBE"] = "N";
					$arFieldsTmp['TYPE'] = (int)$arShoppingCartItem['TYPE'];
					$arFieldsTmp['SET_PARENT_ID'] = $arShoppingCartItem['SET_PARENT_ID'];
					$arFieldsTmp['LID'] = $siteId;
				}
				else
				{
					$arFieldsTmp = array("CAN_BUY" => "N");
				}

				if ($isOrderConverted != 'N')
				{
					if (!Sale\Compatible\DiscountCompatibility::isInited())
						Sale\Compatible\DiscountCompatibility::init();
					$basketCode = (Sale\Compatible\DiscountCompatibility::usedByClient() ? $arShoppingCartItem['ID'] : $itemIndex);
					Sale\Compatible\DiscountCompatibility::setBasketItemData($basketCode, $arFieldsTmp);
				}

				if ($existBasketID)
				{
					$arFieldsTmp["IGNORE_CALLBACK_FUNC"] = "Y";

					CSaleBasket::Update($arShoppingCartItem["ID"], $arFieldsTmp);

					$dbTmp = CSaleBasket::GetList(
						array(),
						array("ID" => $arShoppingCartItem["ID"]),
						false,
						false,
						array(
							"ID", "CALLBACK_FUNC", "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY", "CAN_BUY", "PRICE", "TYPE", "SET_PARENT_ID",
							"WEIGHT", "NAME", "CURRENCY", "CATALOG_XML_ID", "VAT_RATE", "NOTES", "DISCOUNT_PRICE", "DETAIL_PAGE_URL", "PRODUCT_PROVIDER_CLASS", "DIMENSIONS"
						)
					);
					$arTmp = $dbTmp->Fetch();

					foreach ($arTmp as $key => $val)
						$arShoppingCartItem[$key] = $val;
				}
				else
				{
					foreach ($arFieldsTmp as $key => $val)
					{
						// update returned quantity for the product if quantity difference is available
						if ($orderId != 0 && $key == "QUANTITY" && $arOldShoppingCart[$arShoppingCartItem["ID_TMP"]]["RESERVED"] == "Y" && $quantity > 0)
						{
							$arShoppingCartItem[$key] = $val + $arOldShoppingCart[$arShoppingCartItem["ID_TMP"]]["QUANTITY"];
						}
						else
						{
							$arShoppingCartItem[$key] = $val;
						}
					}
				}
			}

			if ($arShoppingCartItem["CAN_BUY"] == "Y")
			{
				if(!$enableCustomCurrency)
				{
					$baseLangCurrency = CSaleLang::GetLangCurrency($siteId);
					if ($baseLangCurrency != $arShoppingCartItem["CURRENCY"])
					{
						$arShoppingCartItem["PRICE"] = CCurrencyRates::ConvertCurrency($arShoppingCartItem["PRICE"], $arShoppingCartItem["CURRENCY"], $baseLangCurrency);
						if (is_set($arShoppingCartItem, "DISCOUNT_PRICE"))
							$arShoppingCartItem["DISCOUNT_PRICE"] = CCurrencyRates::ConvertCurrency($arShoppingCartItem["DISCOUNT_PRICE"], $arShoppingCartItem["CURRENCY"], $baseLangCurrency);
						$arShoppingCartItem["CURRENCY"] = $baseLangCurrency;
					}
				}

				$arShoppingCartItem["PRICE"] = \Bitrix\Sale\PriceMaths::roundPrecision($arShoppingCartItem["PRICE"]);

				$arShoppingCartItem["QUANTITY"] = floatval($arShoppingCartItem["QUANTITY"]);
				$arShoppingCartItem["WEIGHT"] = floatval($arShoppingCartItem["WEIGHT"]);
				$arShoppingCartItem["DIMENSIONS"] = unserialize($arShoppingCartItem["DIMENSIONS"], ['allowed_classes' => false]);
				$arShoppingCartItem["VAT_RATE"] = floatval($arShoppingCartItem["VAT_RATE"]);
				$arShoppingCartItem["DISCOUNT_PRICE"] = roundEx($arShoppingCartItem["DISCOUNT_PRICE"], SALE_VALUE_PRECISION);

				if ($arShoppingCartItem["VAT_RATE"] > 0)
					$arShoppingCartItem["VAT_VALUE"] = \Bitrix\Sale\PriceMaths::roundPrecision(($arShoppingCartItem["PRICE"] / ($arShoppingCartItem["VAT_RATE"] + 1)) * $arShoppingCartItem["VAT_RATE"]);
				//$arShoppingCartItem["VAT_VALUE"] = roundEx((($arShoppingCartItem["PRICE"] / ($arShoppingCartItem["VAT_RATE"] + 1)) * $arShoppingCartItem["VAT_RATE"]), SALE_VALUE_PRECISION);

				if ($arShoppingCartItem["DISCOUNT_PRICE"] > 0)
					$arShoppingCartItem["DISCOUNT_PRICE_PERCENT"] = $arShoppingCartItem["DISCOUNT_PRICE"] * 100 / ($arShoppingCartItem["DISCOUNT_PRICE"] + $arShoppingCartItem["PRICE"]);
				$arResult[$itemIndex] = $arShoppingCartItem;
			}
		}
		if (isset($arShoppingCartItem))
			unset($arShoppingCartItem);

		if (!empty($arCoupons) && is_array($arCoupons))
		{
			foreach(GetModuleEvents("sale", "OnClearCouponList", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($userId, $arCoupons, array()));
		}

		return $arResult;
	}

	/**
	 * Changes product quantity in the catalog.
	 * Used in the DoSaveOrderBasket to actualize basket items quantity
	 * after some operations with the order are made in the order_new form
	 *
	 * Depending on the state of the order (reserved/deducted)
	 * and the state of the product (reserved/deducted) calls appropriate provider methods
	 *
	 * If the quantity is 0 and CHECK_QUANTITY is N, this method is used only to call OrderProduct method to actualize coupon data
	 *
	 * @param array $arBasketItem - basket item data array
	 * @param int $deltaQuantity - quantity to be changed. Can be zero, in this case CHECK_QUANTITY should be N
	 * @param bool $isOrderReserved - order reservation flag
	 * @param bool $isOrderDeducted - order deduction flag
	 * @param array $arStoreBarcodeOrderFormData - array of barcode and stores from order_new form to be used for deduction
	 * @param array $arAdditionalParams - user id, site id, check_quantity flag
	 * @return bool
	 */
	public static function DoChangeProductQuantity($arBasketItem, $deltaQuantity, $isOrderReserved = false, $isOrderDeducted = false, $arStoreBarcodeOrderFormData = array(), $arAdditionalParams = array())
	{
		global $APPLICATION;

		if (!array_key_exists("CHECK_QUANTITY", $arAdditionalParams) || $arAdditionalParams["CHECK_QUANTITY"] != "N")
			$arAdditionalParams["CHECK_QUANTITY"] = "Y";

		if (defined("SALE_DEBUG") && SALE_DEBUG)
		{
			CSaleHelper::WriteToLog(
				"DoChangeProductQuantity - Started",
				array(
					"arBasketItem" => $arBasketItem,
					"deltaQuantity" => $deltaQuantity,
					"isOrderReserved" => intval($isOrderReserved),
					"isOrderDeducted" => intval($isOrderDeducted),
					"arStoreBarcodeOrderFormData" => $arStoreBarcodeOrderFormData,
					"checkQuantity" => $arAdditionalParams["CHECK_QUANTITY"]
				),
				"DCPQ1"
			);
		}

		/** @var $productProvider IBXSaleProductProvider */
		if ($productProvider = CSaleBasket::GetProductProvider($arBasketItem))
		{
			$productProvider::OrderProduct(
				array(
					"PRODUCT_ID" => $arBasketItem["PRODUCT_ID"],
					"QUANTITY"   => ($deltaQuantity <= 0 ? $arBasketItem['QUANTITY'] : $deltaQuantity),
					"RENEWAL"    => "N",
					"USER_ID"    => $arAdditionalParams["USER_ID"],
					"SITE_ID"    => $arAdditionalParams["SITE_ID"],
					"CHECK_QUANTITY" => $arAdditionalParams["CHECK_QUANTITY"],
					"BASKET_ID" => $arBasketItem['ID']
				)
			);
			if ($deltaQuantity == 0 && $arAdditionalParams["CHECK_QUANTITY"] == 'N')
				return true;

			if ($isOrderDeducted) // we need to reserve and deduct product
			{
				$quantityPreviouslyLeftToReserve = ($arBasketItem["RESERVED"] == "Y") ? floatval($arBasketItem["RESERVE_QUANTITY"]) : 0;

				if (defined("SALE_DEBUG") && SALE_DEBUG)
				{
					CSaleHelper::WriteToLog(
						"Call ::ReserveBasketProduct",
						array(
							"arBasketItemID" => $arBasketItem["ID"],
							"deltaQuantity" => $deltaQuantity,
							"quantityPreviouslyLeftToReserve" => $quantityPreviouslyLeftToReserve,
							"isOrderDeducted" => $isOrderDeducted
						),
						"DCPQ2"
					);
				}

				$arRes = CSaleBasket::ReserveBasketProduct($arBasketItem["ID"], $deltaQuantity + $quantityPreviouslyLeftToReserve, $isOrderDeducted);
				if (array_key_exists("ERROR", $arRes))
				{
					CSaleOrder::SetMark($arAdditionalParams["ORDER_ID"], Loc::getMessage("SKGB_RESERVE_ERROR", array("#MESSAGE#" => $arRes["ERROR"]["MESSAGE"])));
					return false;
				}

				if (defined("SALE_DEBUG") && SALE_DEBUG)
				{
					CSaleHelper::WriteToLog(
						"Call ::DeductBasketProduct",
						array(
							"arBasketItemID" => $arBasketItem["ID"],
							"deltaQuantity" => $deltaQuantity,
							"arStoreBarcodeOrderFormData" => $arStoreBarcodeOrderFormData
						),
						"DCPQ3"
					);
				}

				$arDeductResult = CSaleBasket::DeductBasketProduct($arBasketItem["ID"], $deltaQuantity, $arStoreBarcodeOrderFormData);
				if (array_key_exists("ERROR", $arDeductResult))
				{
					CSaleOrder::SetMark($arAdditionalParams["ORDER_ID"], Loc::getMessage("SKGB_DEDUCT_ERROR", array("#MESSAGE#" => $arDeductResult["ERROR"]["MESSAGE"])));
					$APPLICATION->ThrowException(Loc::getMessage("SKGB_DEDUCT_ERROR", array("#MESSAGE#" => $arDeductResult["ERROR"]["MESSAGE"])), "DEDUCTION_ERROR");
					return false;
				}
			}
			else if ($isOrderReserved && !$isOrderDeducted) // we need to reserve product
			{
				if ($arBasketItem["RESERVED"] == "Y")
				{
					$quantityPreviouslyLeftToReserve = floatval($arBasketItem["RESERVE_QUANTITY"]);

					if (defined("SALE_DEBUG") && SALE_DEBUG)
					{
						CSaleHelper::WriteToLog(
							"Call ::ReserveBasketProduct",
							array(
								"arBasketItemID" => $arBasketItem["ID"],
								"deltaQuantity" => $deltaQuantity,
								"quantityPreviouslyLeftToReserve" => $quantityPreviouslyLeftToReserve
							),
							"DCPQ4"
						);
					}

					$arRes = CSaleBasket::ReserveBasketProduct($arBasketItem["ID"], $deltaQuantity + $quantityPreviouslyLeftToReserve);
					if (array_key_exists("ERROR", $arRes))
					{
						CSaleOrder::SetMark($arAdditionalParams["ORDER_ID"], Loc::getMessage("SKGB_RESERVE_ERROR", array("#MESSAGE#" => $arRes["ERROR"]["MESSAGE"])));
						return false;
					}
				}
				else
				{
					if (defined("SALE_DEBUG") && SALE_DEBUG)
					{
						CSaleHelper::WriteToLog(
							"Call ::ReserveBasketProduct",
							array(
								"arBasketItemID" => $arBasketItem["ID"],
								"deltaQuantity" => $deltaQuantity
							),
							"DCPQ5"
						);
					}

					$arRes = CSaleBasket::ReserveBasketProduct($arBasketItem["ID"], $deltaQuantity);
					if (array_key_exists("ERROR", $arRes))
					{
						CSaleOrder::SetMark($arAdditionalParams["ORDER_ID"], Loc::getMessage("SKGB_RESERVE_ERROR", array("#MESSAGE#" => $arRes["ERROR"]["MESSAGE"])));
						return false;
					}
				}
			}
			else // order not reserved, not deducted
			{
				if (defined("SALE_DEBUG") && SALE_DEBUG)
				{
					CSaleHelper::WriteToLog(
						"Call ::ReserveBasketProduct",
						array(
							"arBasketItemID" => $arBasketItem["ID"],
							"deltaQuantity" => $deltaQuantity
						),
						"DCPQ6"
					);
				}

				if ($arBasketItem["RESERVED"] == "Y") // we undo product reservation
				{
					$quantityPreviouslyLeftToReserve = floatval($arBasketItem["RESERVE_QUANTITY"]);

					$arRes = CSaleBasket::ReserveBasketProduct($arBasketItem["ID"], $deltaQuantity + $quantityPreviouslyLeftToReserve);
					if (array_key_exists("ERROR", $arRes))
					{
						CSaleOrder::SetMark($arAdditionalParams["ORDER_ID"], Loc::getMessage("SKGB_RESERVE_ERROR", array("#MESSAGE#" => $arRes["ERROR"]["MESSAGE"])));
						return false;
					}
				}
			}
		}
		else // provider is not used. old logic without reservation
		{
			if ($deltaQuantity < 0)
			{
				CSaleBasket::ExecuteCallbackFunction(
					$arBasketItem["CANCEL_CALLBACK_FUNC"],
					$arBasketItem["MODULE"],
					$arBasketItem["PRODUCT_ID"],
					abs($deltaQuantity),
					true
				);
			}
			else if ($deltaQuantity > 0)
			{
				CSaleBasket::ExecuteCallbackFunction(
					$arBasketItem["ORDER_CALLBACK_FUNC"],
					$arBasketItem["MODULE"],
					$arBasketItem["PRODUCT_ID"],
					$deltaQuantity,
					"N",
					$arAdditionalParams["USER_ID"],
					$arAdditionalParams["SITE_ID"]
				);
			}
			else if ($deltaQuantity == 0)
			{
				CSaleBasket::ExecuteCallbackFunction(
					$arBasketItem["ORDER_CALLBACK_FUNC"],
					$arBasketItem["MODULE"],
					$arBasketItem["PRODUCT_ID"],
					$arBasketItem['QUANTITY'],
					"N",
					$arAdditionalParams["USER_ID"],
					$arAdditionalParams["SITE_ID"]
				);
			}
		}
		return true;
	}

	/**
	 * Updates information about basket products after changes have been made in the order_new form
	 * (saves newly added basket items, changes their quantity, saves barcodes, etc)
	 *
	 * @param int $orderId - order ID
	 * @param string $siteId - site ID
	 * @param bool $userId - user ID
	 * @param array $arShoppingCart - array of basket items
	 * @param array $arErrors
	 * @param array $arCoupons
	 * @param array $arStoreBarcodeOrderFormData - array of stores and barcodes for deduction (from order_new form)
	 * @param bool $bSaveBarcodes - flat to save given barcode data. Used if the order is already deducted or at least has saved other barcodes
	 * @return bool
	 */
	public static function DoSaveOrderBasket($orderId, $siteId, $userId, &$arShoppingCart, &$arErrors, $arCoupons = array(), $arStoreBarcodeOrderFormData = array(), $bSaveBarcodes = false)
	{
		global $USER, $APPLICATION;

		$currentUserID = 0;
		if (isset($USER) && $USER instanceof CUser)
			$currentUserID = (int)$USER->GetID();

		if (defined("SALE_DEBUG") && SALE_DEBUG)
		{
			CSaleHelper::WriteToLog("DoSaveOrderBasket - Started",
									array(
										"orderId" => $orderId,
										"siteId" => $siteId,
										"userId" => $userId,
										"arShoppingCart" => $arShoppingCart,
										"bSaveBarcodes" => $bSaveBarcodes,
										"arStoreBarcodeOrderFormData" => $arStoreBarcodeOrderFormData
									),
									"DSOB1"
			);
		}

		$isOrderConverted = Option::get("main", "~sale_converted_15", 'Y');

		$orderId = (int)$orderId;
		if ($orderId <= 0)
			return false;

		if (empty($arShoppingCart) || !is_array($arShoppingCart))
		{
			$arErrors[] = array("CODE" => "PARAM", "TEXT" => Loc::getMessage('SKGB_SHOPPING_CART_EMPTY'));
			return false;
		}

		$isOrderReserved = false;
		$isOrderDeducted = false;
		$dbOrderTmp = CSaleOrder::GetList(
			array(),
			array("ID" => $orderId),
			false,
			false,
			array("ID", "RESERVED", "DEDUCTED")
		);
		if ($arOrder = $dbOrderTmp->Fetch())
		{
			if ($arOrder["RESERVED"] == "Y")
				$isOrderReserved = true;
			if ($arOrder["DEDUCTED"] == "Y")
				$isOrderDeducted = true;
		}

		$arOldItems = array();
		$dbItems = CSaleBasket::GetList(
			array(),
			array("ORDER_ID" => $orderId),
			false,
			false,
			array(
				"ID",
				"QUANTITY",
				"CANCEL_CALLBACK_FUNC",
				"MODULE",
				"PRODUCT_ID",
				"PRODUCT_PROVIDER_CLASS",
				"RESERVED",
				"RESERVE_QUANTITY",
				"TYPE",
				"SET_PARENT_ID"
			)
		);
		while ($arItem = $dbItems->Fetch())
		{
			$arOldItems[$arItem["ID"]] = array(
				"QUANTITY"               => $arItem["QUANTITY"],
				"CANCEL_CALLBACK_FUNC"   => $arItem["CANCEL_CALLBACK_FUNC"],
				"PRODUCT_PROVIDER_CLASS" => $arItem["PRODUCT_PROVIDER_CLASS"],
				"MODULE"                 => $arItem["MODULE"],
				"PRODUCT_ID"             => $arItem["PRODUCT_ID"],
				"RESERVED"               => $arItem["RESERVED"],
				"RESERVE_QUANTITY"       => $arItem["RESERVE_QUANTITY"],
				"TYPE"                   => $arItem["TYPE"],
				"SET_PARENT_ID"          => $arItem["SET_PARENT_ID"]
			);
		}

		if (!empty($arCoupons))
		{
			if (!is_array($arCoupons))
				$arCoupons = array($arCoupons);
			foreach (GetModuleEvents("sale", "OnSetCouponList", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($userId, $arCoupons, array()));
			foreach ($arCoupons as &$coupon)
				$couponResult = DiscountCouponsManager::add($coupon);
			unset($coupon, $couponResult);
		}

		$FUSER_ID = Sale\Fuser::getIdByUserId((int)$userId);

		$arTmpSetParentId = array();

		//TODO: is orders converted?
		if ($isOrderConverted == 'N')
		{
			// re-sort basket data so newly added Set parents come before Set items (used to correctly add Set items to the table)
			usort($arShoppingCart, array("CSaleBasketHelper", "cmpSetData"));

			foreach ($arShoppingCart as &$arItem)
			{
				$arItemKeys = array_keys($arItem);

				foreach ($arItemKeys as $fieldName)
				{
					if(array_key_exists("~".$fieldName, $arItem))
					{
						if  ((is_array($arItem["~".$fieldName]) && !empty($arItem["~".$fieldName]))
							|| (!is_array($arItem["~".$fieldName]) && $arItem["~".$fieldName] <> ''))
						{
							$arItem[$fieldName] = $arItem["~".$fieldName];
						}
						unset($arItem["~".$fieldName]);
					}
				}

				$arItem = array_filter($arItem, array("CSaleBasketHelper", "filterFields"));
			}
			unset($arItem);


			foreach ($arShoppingCart as $arItem)
			{
				if (mb_strpos($arItem["SET_PARENT_ID"], "tmp") !== false)
					$arTmpSetParentId[$arItem["SET_PARENT_ID"]] = $arItem["SET_PARENT_ID"];
			}
		}

		$orderBasketPool = array();

		// iterate over basket data to save it to basket or change quantity (and reserve/deduct accordingly)
		foreach ($arShoppingCart as &$arItem)
		{

			foreach ($arItem as $tmpKey => $tmpVal)
			{
				if (is_array($tmpVal) && !in_array($tmpKey, array("STORES", "CATALOG", "PROPS")))
					$arItem[$tmpKey] = serialize($tmpVal);
			}

			if (defined("SALE_DEBUG") && SALE_DEBUG)
				CSaleHelper::WriteToLog("DoSaveOrderBasket - Item", array("arItem" => $arItem), "DSOB2");


			if (array_key_exists("ID", $arItem) && (int)$arItem["ID"] > 0)
			{
				$arItem["ID"] = (int)$arItem["ID"];

				if (defined("SALE_DEBUG") && SALE_DEBUG)
					CSaleHelper::WriteToLog("DoSaveOrderBasket - Product #".$arItem["ID"]." already in the basket", array(), "DSOB3");

				// product already in the basket, change quantity
				if (array_key_exists($arItem["ID"], $arOldItems))
				{
					//TODO: is order converted?
					if ($isOrderConverted == 'N')
					{
						if (!CSaleBasketHelper::isSetParent($arItem))
						{
							$arAdditionalParams = array(
								"ORDER_ID" => $orderId,
								"USER_ID" => $userId,
								"SITE_ID" => $siteId
							);

							$quantity = $arItem["QUANTITY"] - $arOldItems[$arItem["ID"]]["QUANTITY"];

							$arAdditionalParams["CHECK_QUANTITY"] = ($quantity > 0) ? "Y" : "N";

							if ($quantity != 0)
							{
								self::DoChangeProductQuantity(
									$arItem,
									$quantity,
									$isOrderReserved,
									$isOrderDeducted,
									$arStoreBarcodeOrderFormData[$arItem["ID"]],
									$arAdditionalParams
								);
							}
							else
							{
								$arAdditionalParams['CHECK_QUANTITY'] = 'N';
								self::DoChangeProductQuantity(
									$arItem,
									$quantity,
									$isOrderReserved,
									$isOrderDeducted,
									$arStoreBarcodeOrderFormData[$arItem["ID"]],
									$arAdditionalParams
								);
							}

						}
					}
					unset($arOldItems[$arItem["ID"]]);
				}
				else
				{
					//TODO: is order converted?
					if ($isOrderConverted == 'N')
					{
						if ($arItem["QUANTITY"] != 0 && !CSaleBasketHelper::isSetParent($arItem))
						{
							self::DoChangeProductQuantity(
								$arItem,
								$arItem["QUANTITY"],
								$isOrderReserved,
								$isOrderDeducted,
								$arStoreBarcodeOrderFormData[$arItem["ID"]],
								array("ORDER_ID" => $orderId, "USER_ID" => $userId, "SITE_ID" => $siteId)
							);
						}
					}
				}

				if(intval($arItem["FUSER_ID"]) <= 0)
				{
					$arFuserItems = CSaleUser::GetList(array("USER_ID" => intval($userId)));
					$arItem["FUSER_ID"] = $arFuserItems["ID"];
				}

				if (CSaleBasketHelper::isSetItem($arItem)) // quantity for set items will be changed when parent item is updated
					unset($arItem["QUANTITY"]);


				//TODO: is order converted?
				if ($isOrderConverted != 'N')
				{
					$fields = array("IGNORE_CALLBACK_FUNC" => "Y") + $arItem;

					$orderBasketPool[$arItem["ID"]] = array("ORDER_ID" => $orderId);

					foreach(GetModuleEvents("sale", "OnBeforeBasketUpdateAfterCheck", true) as $event)
					{
						if (ExecuteModuleEventEx($event, array($arItem["ID"], &$fields)) === false)
						{
							return false;
						}
					}

					/** @var \Bitrix\Sale\Result $r */
					$r = \Bitrix\Sale\Compatible\BasketCompatibility::update($arItem["ID"], $fields);

					if (!$r->isSuccess(true))
					{
						foreach($r->getErrorMessages() as $error)
						{
							$APPLICATION->ThrowException($error);
						}

						return false;
					}
				}
				else
				{
					CSaleBasket::Update($arItem["ID"], array("ORDER_ID" => $orderId, "IGNORE_CALLBACK_FUNC" => "Y") + $arItem);
				}

			}
			else // new product in the basket
			{
				if (defined("SALE_DEBUG") && SALE_DEBUG)
					CSaleHelper::WriteToLog("DoSaveOrderBasket - new product in the basket", array(), "DSOB4");

				unset($arItem["ID"]);

				/** @var $productProvider IBXSaleProductProvider */
				if ($productProvider = CSaleBasket::GetProductProvider($arItem)) //if we need to use new logic
				{
					$oldSetParentId = -1;
					if (CSaleBasketHelper::isSetParent($arItem) && array_key_exists($arItem["SET_PARENT_ID"], $arTmpSetParentId))
					{
						$oldSetParentId = $arItem["SET_PARENT_ID"];
						$arItem["MANUAL_SET_ITEMS_INSERTION"] = "Y";
					}

					if (CSaleBasketHelper::isSetItem($arItem) && array_key_exists($arItem["SET_PARENT_ID"], $arTmpSetParentId))
					{
						$arItem["SET_PARENT_ID"] = $arTmpSetParentId[$arItem["SET_PARENT_ID"]];
					}

					$arItem["ID"] = CSaleBasket::Add(array("ORDER_ID" => $orderId, "IGNORE_CALLBACK_FUNC" => "Y") + $arItem);
					if($APPLICATION->GetException())
					{
						return false;
					}

					if (isset($arItem["MANUAL_SET_ITEMS_INSERTION"]))
						$arTmpSetParentId[$oldSetParentId] = $arItem["ID"];

					if ($bSaveBarcodes)
					{
						if ($arItem["BARCODE_MULTI"] == "N") //saving only store quantity info
						{
							if (is_array($arItem["STORES"]))
							{
								foreach ($arItem["STORES"] as $arStore)
								{
									$arStoreBarcodeFields = array(
										"BASKET_ID"   => $arItem["ID"],
										"BARCODE"     => "",
										"STORE_ID"    => $arStore["STORE_ID"],
										"QUANTITY"    => $arStore["QUANTITY"],
										"CREATED_BY"  => ($currentUserID > 0  ? $currentUserID : ''),
										"MODIFIED_BY" => ($currentUserID > 0 ? $currentUserID : '')
									);

									CSaleStoreBarcode::Add($arStoreBarcodeFields);
								}
							}
						}
						else  // BARCODE_MULTI = Y
						{
							if (!empty($arItem["STORES"]) && is_array($arItem["STORES"]))
							{
								foreach ($arItem["STORES"] as $arStore)
								{
									if (isset($arStore["BARCODE"]) && isset($arStore["BARCODE_FOUND"]))
									{
										foreach ($arStore["BARCODE"] as $barcodeId => $barcodeValue)
										{
											// save only non-empty and valid barcodes TODO - if errors?
											if ($barcodeValue <> '' &&  $arStore["BARCODE_FOUND"][$barcodeId] == "Y")
											{
												$arStoreBarcodeFields = array(
													"BASKET_ID"   => $arItem["ID"],
													"BARCODE"     => $barcodeValue,
													"STORE_ID"    => $arStore["STORE_ID"],
													"QUANTITY"    => 1,
													"CREATED_BY"  => ($currentUserID > 0  ? $currentUserID : ''),
													"MODIFIED_BY" => ($currentUserID > 0  ? $currentUserID : '')
												);

												CSaleStoreBarcode::Add($arStoreBarcodeFields);
											}
										}
									}
								}
							}
						}
					}

					if ($arItem["QUANTITY"] != 0 && !CSaleBasketHelper::isSetParent($arItem))
					{
						self::DoChangeProductQuantity(
							$arItem,
							$arItem["QUANTITY"],
							$isOrderReserved,
							$isOrderDeducted,
							$arItem["STORES"],
							array("ORDER_ID" => $orderId, "USER_ID" => $userId, "SITE_ID" => $siteId)
						);
					}

					if ($FUSER_ID > 0)
						$arItem["FUSER_ID"] = $FUSER_ID;
				}
				else
				{
					if ($arItem["QUANTITY"] != 0 && !CSaleBasketHelper::isSetParent($arItem))
					{
						self::DoChangeProductQuantity(
							$arItem,
							$arItem["QUANTITY"],
							$isOrderReserved,
							$isOrderDeducted,
							$arItem["STORES"],
							array("ORDER_ID" => $orderId, "USER_ID" => $userId, "SITE_ID" => $siteId)
						);
					}

					if ($FUSER_ID > 0)
						$arItem["FUSER_ID"] = $FUSER_ID;

					$arItem["ID"] = CSaleBasket::Add(array("ORDER_ID" => $orderId, "IGNORE_CALLBACK_FUNC" => "Y") + $arItem);
					//$arItem["ID"] = CSaleBasket::Add(array("CALLBACK_FUNC" => false, "ORDER_ID" => $orderId, "IGNORE_CALLBACK_FUNC" => "Y") + $arItem);
				}
			}
		}
		unset($arItem);

		if ($isOrderConverted != 'N' && !empty($orderBasketPool))
		{
			/** @var Sale\Result $r */
			$r = Sale\Compatible\BasketCompatibility::setBasketFields($orderBasketPool);
			if (!$r->isSuccess(true))
			{
				foreach($r->getErrorMessages() as $error)
				{
					$APPLICATION->ThrowException($error);
				}

				return false;
			}
		}

		if (defined("SALE_DEBUG") && SALE_DEBUG)
			CSaleHelper::WriteToLog("Items left in the old basket:", array("arOldItems" => $arOldItems), "DSOB5");

		// if some items left in the table which are not present in the updated basket, delete them
		$arSetParentsIDs = array();
		foreach ($arOldItems as $key => $arOldItem)
		{
			$arOldItem["ID"] = $key;

			if (CSaleBasketHelper::isSetParent($arOldItem))
			{
				$arSetParentsIDs[] = $arOldItem["ID"];
				continue;
			}
			else
			{
				if ($isOrderConverted == 'N')
				{
					// the quantity is negative, so the product is canceled
					self::DoChangeProductQuantity(
						$arOldItem,
						-$arOldItem["QUANTITY"],
						$isOrderReserved,
						$isOrderDeducted,
						$arStoreBarcodeOrderFormData[$arOldItem["ID"]],
						array("ORDER_ID" => $orderId, "USER_ID" => $userId, "SITE_ID" => $siteId)
					);
				}
			}

			CSaleBasket::Delete($key);
		}

		foreach ($arSetParentsIDs as $setParentID)
			CSaleBasket::Delete($setParentID);

		foreach(GetModuleEvents("sale", "OnDoBasketOrder", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($orderId));

		return true;
	}

	//************** ADD, UPDATE, DELETE ********************//
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $APPLICATION;
		static $orderList = array();

		$ACTION = mb_strtoupper($ACTION);

		if (array_key_exists('ID', $arFields))
			unset($arFields['ID']);

		if ($ACTION != "ADD" && (int)$ID <=0)
		{
			$APPLICATION->ThrowException(Loc::getMessage('BT_MOD_SALE_BASKET_ERR_ID_ABSENT'), "ID");
			return false;
		}

		if ('ADD' == $ACTION)
		{
			if (!array_key_exists('CUSTOM_PRICE', $arFields))
				$arFields['CUSTOM_PRICE'] = '';
		}

		if (array_key_exists('CUSTOM_PRICE', $arFields) && 'Y' != $arFields['CUSTOM_PRICE'])
			$arFields['CUSTOM_PRICE'] = 'N';

		if (is_set($arFields, "PRODUCT_ID"))
			$arFields["PRODUCT_ID"] = intval($arFields["PRODUCT_ID"]);
		if ((is_set($arFields, "PRODUCT_ID") || $ACTION=="ADD") && intval($arFields["PRODUCT_ID"])<=0)
		{
			$APPLICATION->ThrowException(Loc::getMessage('BT_MOD_SALE_BASKET_ERR_PRODUCT_ID_ABSENT'), "PRODUCT_ID");
			return false;
		}

		if (!array_key_exists('IGNORE_CALLBACK_FUNC', $arFields) || 'Y' != $arFields['IGNORE_CALLBACK_FUNC'])
		{
			if ((array_key_exists("CALLBACK_FUNC", $arFields) && !empty($arFields["CALLBACK_FUNC"]))
				|| (array_key_exists("PRODUCT_PROVIDER_CLASS", $arFields) && !empty($arFields["PRODUCT_PROVIDER_CLASS"]))
			)
			{
				/** @var $productProvider IBXSaleProductProvider */
				if ($productProvider = CSaleBasket::GetProductProvider(array("MODULE" => $arFields["MODULE"], "PRODUCT_PROVIDER_CLASS" => $arFields["PRODUCT_PROVIDER_CLASS"])))
				{

					if (array_key_exists("IBXSaleProductProvider", class_implements($productProvider)))
					{
						$providerParams = array(
							"PRODUCT_ID" => $arFields["PRODUCT_ID"],
							"QUANTITY" => $arFields["QUANTITY"],
							"RENEWAL" => $arFields["RENEWAL"],
							"USER_ID" => (isset($arFields["USER_ID"]) ? $arFields["USER_ID"] : 0),
							"SITE_ID" => (isset($arFields["LID"]) ? $arFields["LID"] : false),
							"BASKET_ID" => $ID
						);
						if (isset($arFields['NOTES']))
							$providerParams['NOTES'] = $arFields['NOTES'];
						$arPrice = $productProvider::GetProductData($providerParams);
						unset($providerParams);
					}
					elseif (get_parent_class($productProvider) == 'Bitrix\Sale\SaleProviderBase')
					{
//						$productProvider::getCatalogData();
//						$productProvider::getProviderData();
//						$productProvider::getProviderData();
					}




				}
				else
				{
					$arPrice = CSaleBasket::ExecuteCallbackFunction(
						$arFields["CALLBACK_FUNC"],
						$arFields["MODULE"],
						$arFields["PRODUCT_ID"],
						$arFields["QUANTITY"],
						$arFields["RENEWAL"],
						$arFields["USER_ID"],
						$arFields["LID"]
					);
				}

				if (!empty($arPrice) && is_array($arPrice))
				{
					if (isset($arPrice['BASE_PRICE']))
						$arPrice['BASE_PRICE'] = roundEx($arPrice['BASE_PRICE'], SALE_VALUE_PRECISION);

					if (isset($arPrice['DISCOUNT_PRICE']))
						$arPrice['DISCOUNT_PRICE'] = roundEx($arPrice['DISCOUNT_PRICE'], SALE_VALUE_PRECISION);

					if (isset($arPrice['PRICE']))
						$arPrice['PRICE'] = roundEx($arPrice['PRICE'], SALE_VALUE_PRECISION);

					$arFields["PRICE"] = $arPrice["PRICE"];
					$arFields["CURRENCY"] = $arPrice["CURRENCY"];
					$arFields["CAN_BUY"] = "Y";
					$arFields["PRODUCT_PRICE_ID"] = $arPrice["PRODUCT_PRICE_ID"];
					$arFields["NOTES"] = $arPrice["NOTES"];
					if (!isset($arFields["NAME"]))
						$arFields["NAME"] = $arPrice["NAME"];
					if (isset($arPrice['DISCOUNT_LIST']))
						$arFields['DISCOUNT_LIST'] = $arPrice['DISCOUNT_LIST'];
				}
				else
				{
					if ($ACTION == 'ADD')
						return false;
					$arFields["CAN_BUY"] = "N";
				}
			}
		}

		if (is_set($arFields, "PRICE") || $ACTION=="ADD")
		{
			$arFields["PRICE"] = str_replace(",", ".", $arFields["PRICE"]);
			$arFields["PRICE"] = floatval($arFields["PRICE"]);
		}

		if (is_set($arFields, "DISCOUNT_PRICE") || $ACTION=="ADD")
		{
			$arFields["DISCOUNT_PRICE"] = str_replace(",", ".", $arFields["DISCOUNT_PRICE"]);
			$arFields["DISCOUNT_PRICE"] = floatval($arFields["DISCOUNT_PRICE"]);
		}

		if (is_set($arFields, "VAT_RATE") || $ACTION=="ADD")
		{
			$arFields["VAT_RATE"] = str_replace(",", ".", $arFields["VAT_RATE"]);
			$arFields["VAT_RATE"] = floatval($arFields["VAT_RATE"]);
		}

		if ((is_set($arFields, "CURRENCY") || $ACTION=="ADD") && $arFields["CURRENCY"] == '')
		{
			$APPLICATION->ThrowException(Loc::getMessage('BT_MOD_SALE_BASKET_ERR_CURRENCY_ABSENT'), "CURRENCY");
			return false;
		}

		if ((is_set($arFields, "LID") || $ACTION=="ADD") && $arFields["LID"] == '')
		{
			$APPLICATION->ThrowException(Loc::getMessage('BT_MOD_SALE_BASKET_ERR_SITE_ID_ABSENT'), "LID");
			return false;
		}

		if (is_set($arFields, "ORDER_ID"))
		{
			if (!isset($orderList[$arFields["ORDER_ID"]]))
			{
				$rsOrders = CSaleOrder::GetList(
					array(),
					array('ID' => $arFields["ORDER_ID"]),
					false,
					false,
					array('ID')
				);
				if ($arOrder = $rsOrders->Fetch())
				{
					$orderList[$arFields["ORDER_ID"]] = true;
				}
			}
			if (!isset($orderList[$arFields["ORDER_ID"]]))
			{
				$APPLICATION->ThrowException(str_replace("#ID#", $arFields["ORDER_ID"], Loc::getMessage("SKGB_NO_ORDER")), "ORDER_ID");
				return false;
			}
		}

		if (is_set($arFields, 'CURRENCY'))
		{
			$arFields['CURRENCY'] = (string)$arFields['CURRENCY'];
			if (empty($arFields['CURRENCY']))
			{
				$APPLICATION->ThrowException(str_replace("#ID#", $arFields["CURRENCY"], Loc::getMessage("SKGB_NO_CURRENCY")), "CURRENCY");
				return false;
			}
			else
			{
				if (empty(self::$currencyList))
				{
					$currencyIterator = Currency\CurrencyTable::getList(array(
																			'select' => array('CURRENCY'),
																		));
					while ($currency = $currencyIterator->fetch())
						self::$currencyList[$currency['CURRENCY']] = $currency['CURRENCY'];
				}
				if (!isset(self::$currencyList[$arFields['CURRENCY']]))
				{
					$APPLICATION->ThrowException(str_replace("#ID#", $arFields["CURRENCY"], Loc::getMessage("SKGB_NO_CURRENCY")), "CURRENCY");
					return false;
				}
			}
		}

		if (is_set($arFields, "LID"))
		{
			$dbSite = CSite::GetByID($arFields["LID"]);
			if (!$dbSite->Fetch())
			{
				$APPLICATION->ThrowException(str_replace("#ID#", $arFields["LID"], Loc::getMessage("SKGB_NO_SITE")), "LID");
				return false;
			}
		}

		if ($ACTION != 'ADD')
		{
			$existPrice = array_key_exists('PRICE', $arFields);
			$existCurrency = array_key_exists('CURRENCY', $arFields) && (string)$arFields['CURRENCY'] != '';
			if (!$existPrice || !$existCurrency)
			{
				$existSiteId = isset($arFields['LID']) && (string)$arFields['LID'] != '';
				if (!$existSiteId)
				{
					$select = array('ID', 'LID');
					if (!$existPrice)
						$select[] = 'PRICE';
					if (!$existCurrency)
						$select[] = 'CURRENCY';
					$basketIterator = CSaleBasket::GetList(
						array(),
						array('ID' => $ID),
						false,
						false,
						$select
					);
					if ($basket = $basketIterator->Fetch())
					{
						if (!$existSiteId)
							$arFields['LID'] = $basket['LID'];
						if (!$existPrice)
							$arFields['PRICE'] = $basket['PRICE'];
						if (!$existCurrency)
							$arFields['CURRENCY'] = $basket['CURRENCY'];
					}
					unset($basket, $basketIterator, $select);
				}
				unset($existSiteId);
			}
			unset($existCurrency, $existPrice);
		}

		if (!empty($arFields['LID']) && !empty($arFields['CURRENCY']))
		{
			if (!isset(self::$currencySiteList[$arFields['LID']]))
				self::$currencySiteList[$arFields['LID']] = CSaleLang::GetLangCurrency($arFields['LID']);
			$siteCurrency = self::$currencySiteList[$arFields['LID']];
			if ($siteCurrency != $arFields['CURRENCY'])
			{
				$arFields["PRICE"] = roundEx(CCurrencyRates::ConvertCurrency($arFields["PRICE"], $arFields["CURRENCY"], $siteCurrency), SALE_VALUE_PRECISION);
				if (is_set($arFields, "DISCOUNT_PRICE"))
					$arFields["DISCOUNT_PRICE"] = roundEx(CCurrencyRates::ConvertCurrency($arFields["DISCOUNT_PRICE"], $arFields["CURRENCY"], $siteCurrency), SALE_VALUE_PRECISION);
				$arFields["CURRENCY"] = $siteCurrency;
			}
			unset($siteCurrency);
		}

		// Changed by Sigurd, 2007-08-16
		if (is_set($arFields, "QUANTITY"))
			$arFields["QUANTITY"] = floatval($arFields["QUANTITY"]);
		if ((is_set($arFields, "QUANTITY") || $ACTION=="ADD") && floatval($arFields["QUANTITY"]) <= 0)
			$arFields["QUANTITY"] = 1;

		if (is_set($arFields, "DELAY") && $arFields["DELAY"]!="Y")
			$arFields["DELAY"]="N";
		if (is_set($arFields, "CAN_BUY") && $arFields["CAN_BUY"]!="Y")
			$arFields["CAN_BUY"]="N";

		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && $arFields["NAME"] == '')
		{
			$APPLICATION->ThrowException(Loc::getMessage('BT_MOD_SALE_BASKET_ERR_NAME_ABSENT'), "NAME");
			return false;
		}

		if ($ACTION=="ADD" && !is_set($arFields, "FUSER_ID"))
			$arFields["FUSER_ID"] = CSaleBasket::GetBasketUserID(false);

		if ((is_set($arFields, "FUSER_ID") || $ACTION=="ADD") && intval($arFields["FUSER_ID"])<=0)
		{
			$APPLICATION->ThrowException(Loc::getMessage('BT_MOD_SALE_BASKET_ERR_FUSER_ID_ABSENT'), "FUSER_ID");
			return false;
		}

		if (array_key_exists("TYPE", $arFields))
		{
			$arFields["TYPE"] = (int)$arFields["TYPE"];
			if ($arFields["TYPE"] != CSaleBasket::TYPE_SET)
				unset($arFields["TYPE"]);
		}

		if (array_key_exists('~TYPE', $arFields))
		{
			unset($arFields['~TYPE']);
		}

		if (array_key_exists('CATALOG_XML_ID', $arFields))
		{
			$arFields['CATALOG_XML_ID'] = (string)$arFields['CATALOG_XML_ID'];
			if ($arFields['CATALOG_XML_ID'] === '')
			{
				unset($arFields['CATALOG_XML_ID']);

				if (array_key_exists('~CATALOG_XML_ID', $arFields))
				{
					unset($arFields['~CATALOG_XML_ID']);
				}
			}
		}

		if (array_key_exists('PROPS', $arFields))
		{
			if (empty($arFields['PROPS']) || !is_array($arFields['PROPS']))
			{
				unset($arFields['PROPS']);
			}
			else
			{
				$clearPropList = array();
				foreach ($arFields['PROPS'] as $basketProperty)
				{
					if (empty($basketProperty) || !is_array($basketProperty) || !isset($basketProperty['NAME']))
						continue;
					$basketProperty['NAME'] = (string)$basketProperty['NAME'];
					if ($basketProperty['NAME'] == '')
						continue;
					$propCode = (isset($basketProperty['CODE']) ? (string)$basketProperty['CODE'] : '');
					$propValue = (isset($basketProperty['VALUE']) ? (string)$basketProperty['VALUE'] : '');
					$clearProp = array(
						'NAME' => $basketProperty['NAME'],
						'SORT' => (isset($basketProperty['SORT']) ? (int)$basketProperty['SORT'] : 100)
					);
					if ($propCode != '')
						$clearProp['CODE'] = $propCode;
					if ($propValue != '')
						$clearProp['VALUE'] = $propValue;
					$clearPropList[] = $clearProp;
					unset($clearProp, $propValue, $propCode);
				}
				unset($basketProperty);
				if (!empty($clearPropList))
					$arFields['PROPS'] = $clearPropList;
				else
					unset($arFields['PROPS']);
				unset($clearPropList);
			}
		}

		return true;
	}

	public static function _Update($ID, &$arFields)
	{
		global $DB;

		$ID = (int)$ID;
		//CSaleBasket::Init();

		if (!CSaleBasket::CheckFields("UPDATE", $arFields, $ID))
			return false;

		foreach(GetModuleEvents("sale", "OnBeforeBasketUpdateAfterCheck", true) as $arEvent)
			if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields))===false)
				return false;

		$arOldFields = false;
		$updateHistory = isset($arFields["ORDER_ID"]) && (int)$arFields["ORDER_ID"] > 0;

		$strUpdate = $DB->PrepareUpdate("b_sale_basket", $arFields);
		if (!empty($strUpdate))
		{
			if ($updateHistory)
			{
				$oldOrderIterator = CSaleBasket::GetList(
					array(),
					array('ID' => $ID),
					false,
					false,
					array_keys($arFields)
				);
				$arOldFields = $oldOrderIterator->Fetch();
			}

			$strSql = "update b_sale_basket set ".$strUpdate.", DATE_UPDATE = ".$DB->GetNowFunction()." where ID = ".$ID;
			$DB->Query($strSql);
		}
		else
		{
			$updateHistory = false;
		}

		if (isset($arFields["PROPS"]) && !empty($arFields["PROPS"]) && is_array($arFields["PROPS"]))
		{
			$sql = "delete from b_sale_basket_props where BASKET_ID = ".$ID;

			$bProductXml = false;
			$bCatalogXml = false;
			foreach($arFields["PROPS"] as $prop)
			{
				if (!isset($prop['CODE']))
					continue;
				if ($prop["CODE"] == "PRODUCT.XML_ID")
					$bProductXml = true;

				if ($prop["CODE"] == "CATALOG.XML_ID")
					$bCatalogXml = true;

				if ($bProductXml && $bCatalogXml)
					break;
			}
			if (!$bProductXml)
				$sql .= " and CODE <> 'PRODUCT.XML_ID'";
			if (!$bCatalogXml)
				$sql .= " and CODE <> 'CATALOG.XML_ID'";
			$DB->Query($sql);
			if (!$bProductXml || !$bCatalogXml)
			{
				$sql = "delete from b_sale_basket_props where BASKET_ID = ".$ID." and CODE IS NULL";
				$DB->Query($sql);
			}

			foreach($arFields["PROPS"] as $prop)
			{
				if (!isset($prop["NAME"]))
					continue;
				$prop["NAME"] = (string)$prop["NAME"];
				if($prop["NAME"] != '')
				{
					$arInsert = $DB->PrepareInsert("b_sale_basket_props", $prop);
					$strSql = "INSERT INTO b_sale_basket_props(BASKET_ID, ".$arInsert[0].") VALUES(".$ID.", ".$arInsert[1].")";
					$DB->Query($strSql);
				}
			}
		}

		if ($updateHistory)
			CSaleOrderChange::AddRecordsByFields($arFields["ORDER_ID"], $arOldFields, $arFields, array('PROPS'), "BASKET");

		foreach(GetModuleEvents("sale", "OnBasketUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, Array($ID, $arFields));

		return true;
	}

	public static function Update($ID, $arFields)
	{
		global $APPLICATION;

		$isOrderConverted = Option::get("main", "~sale_converted_15", 'Y');

		if (isset($arFields["ID"]))
			unset($arFields["ID"]);

		$ID = (int)$ID;
		CSaleBasket::Init();

		if ($isOrderConverted == 'N')
		{
			foreach(GetModuleEvents("sale", "OnBeforeBasketUpdate", true) as $arEvent)
				if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields))===false)
					return false;
		}

		if (is_set($arFields, "QUANTITY") && floatval($arFields["QUANTITY"])<=0)
		{
			return CSaleBasket::Delete($ID);
		}
		else
		{

			if ($isOrderConverted != 'N')
			{
				foreach(GetModuleEvents("sale", "OnBeforeBasketUpdateAfterCheck", true) as $event)
				{
					if (ExecuteModuleEventEx($event, array($ID, &$arFields)) === false)
					{
						return false;
					}
				}

				/** @var \Bitrix\Sale\Result $r */
				$r = \Bitrix\Sale\Compatible\BasketCompatibility::update($ID, $arFields);
				if (!$r->isSuccess())
				{
					foreach($r->getErrorMessages() as $error)
					{
						$APPLICATION->ThrowException($error);
					}

					return false;
				}
				return true;
			}
			else
			{
				if (is_set($arFields, "QUANTITY")) // if quantity updated and is set parent item, update all set items' quantity
				{
					$arBasket = $arFields;
					$oldQuantity = false;
					if (!isset($arBasket['TYPE']) || !isset($arBasket['SET_PARENT_ID']))
					{
						$basketIterator = CSaleBasket::GetList(
							array(),
							array('ID' => $ID),
							false,
							false,
							array('ID', 'TYPE', 'SET_PARENT_ID', 'QUANTITY')
						);
						if (!($basket = $basketIterator->Fetch()))
							return false;
						$arBasket['TYPE'] = (int)$basket['TYPE'];
						$arBasket['SET_PARENT_ID'] = (int)$basket['SET_PARENT_ID'];
						$arBasket['QUANTITY'] = $basket['QUANTITY'];
						$oldQuantity = $basket['QUANTITY'];
						unset($basket, $basketIterator);
					}
					if (CSaleBasketHelper::isSetParent($arBasket))
					{
						if ($oldQuantity === false)
						{
							$basketIterator = CSaleBasket::GetList(
								array(),
								array('ID' => $ID),
								false,
								false,
								array('ID', 'QUANTITY')
							);
							if (!($basket = $basketIterator->Fetch()))
								return false;
							$arBasket['QUANTITY'] = $basket['QUANTITY'];
							$oldQuantity = $basket['QUANTITY'];
							unset($basket, $basketIterator);
						}
						if ($oldQuantity != $arFields['QUANTITY'])
						{
							$dbSetItems = CSaleBasket::GetList(
								array(),
								array("SET_PARENT_ID" => $ID, 'TYPE' => false),
								false,
								false,
								array('ID', 'QUANTITY', 'SET_PARENT_ID', 'TYPE')
							);
							while ($arItem = $dbSetItems->Fetch())
							{
								$newQuantity = $arItem['QUANTITY'] / $arBasket['QUANTITY'] * $arFields['QUANTITY'];
								CSaleBasket::Update(
									$arItem['ID'],
									array('QUANTITY' => $newQuantity, 'SET_PARENT_ID' => (int)$arItem['SET_PARENT_ID'], 'TYPE' => (int)$arItem['TYPE'])
								);
							}
							unset($arItem, $dbSetItems);
						}
					}
				}

				return CSaleBasket::_Update($ID, $arFields);
			}
		}
	}


	//************** BASKET USER ********************//
	public static function Init($bVar = false, $bSkipFUserInit = false)
	{
		$bSkipFUserInit = ($bSkipFUserInit !== false);

		if (Sale\Fuser::refreshSessionCurrentId() === null)
		{
			Sale\Fuser::getId($bSkipFUserInit);
		}
	}

	/**
	 * @deprecated
	 * @see Sale\Fuser::getId
	 *
	 * @param bool $bSkipFUserInit
	 * @return int
	 */
	public static function GetBasketUserID($bSkipFUserInit = false)
	{
		$bSkipFUserInit = ($bSkipFUserInit !== false);

		return (int)Sale\Fuser::getId($bSkipFUserInit);
	}


	//************** SELECT ********************//
	public static function GetByID($ID)
	{
		global $DB;

		$ID = (int)$ID;
		if ($ID <= 0)
			return false;
		$strSql = "SELECT * FROM b_sale_basket WHERE ID = ".$ID;
		$dbBasket = $DB->Query($strSql);

		if ($arBasket = $dbBasket->Fetch())
			return $arBasket;

		return false;
	}

	//************** CALLBACK FUNCTIONS ********************//
	public static function ExecuteCallbackFunction($callbackFunc = "", $module = "", $productID = 0)
	{
		$callbackFunc = trim((string)$callbackFunc);
		$module = trim((string)$module);
		$productID = intval($productID);

		$result = False;

		if ($callbackFunc <> '')
		{
			if ($module <> '' && $module != "main")
				CModule::IncludeModule($module);

			$arArgs = array($productID);
			$numArgs = func_num_args();
			if ($numArgs > 3)
				for ($i = 3; $i < $numArgs; $i++)
					$arArgs[] = func_get_arg($i);

			$result = call_user_func_array($callbackFunc, $arArgs);
		}

		return $result;

		/*
		$callbackFunc = trim($callbackFunc);
		$productID = IntVal($productID);
		$module = Trim($module);
		$quantity = IntVal($quantity);

		$result = False;
		if (strlen($callbackFunc) > 0)
		{
			if (strlen($module)>0 && $module != "main")
				CModule::IncludeModule($module);

			$result = $callbackFunc($PRODUCT_ID, $QUANTITY, $arParams);
		}
		return $result;
		*/
	}

	public static function ReReadPrice($callbackFunc = "", $module = "", $productID = 0, $quantity = 0, $renewal = "N", $productProvider = "")
	{
		if (CSaleBasket::GetProductProvider(array("MODULE" => $module, "PRODUCT_PROVIDER_CLASS" => $productProvider)))
		{
			return $productProvider::GetProductData(array(
														"PRODUCT_ID" => $productID,
														"QUANTITY"   => $quantity,
														"RENEWAL"    => $renewal
													));
		}
		else
			return CSaleBasket::ExecuteCallbackFunction($callbackFunc, $module, $productID, $quantity, $renewal);
	}

	public static function OnOrderProduct($callbackFunc = "", $module = "", $productID = 0, $quantity = 0, $productProvider = "")
	{
		if (CSaleBasket::GetProductProvider(array("MODULE" => $module, "PRODUCT_PROVIDER_CLASS" => $productProvider)))
		{
			$productProvider::GetProductData(array(
				"PRODUCT_ID" => $productID,
				"QUANTITY"   => $quantity
			));
		}
		else
			CSaleBasket::ExecuteCallbackFunction($callbackFunc, $module, $productID, $quantity);

		return True;
	}

	public static function UpdatePrice($ID, $callbackFunc = '', $module = '', $productID = 0, $quantity = 0, $renewal = 'N', $productProvider = '', $notes = '')
	{
		$ID = (int)$ID;
		if ($ID <= 0)
			return;
		$callbackFunc = trim((string)$callbackFunc);
		$productID = (int)$productID;
		$module = trim((string)$module);
		$quantity = (float)$quantity;
		$renewal = ((string)$renewal == 'Y' ? 'Y' : 'N');
		$productProvider = trim((string)$productProvider);
		$notes = trim((string)$notes);
		$getQuantity = false;

		$select = array();
		if ($callbackFunc == '' && $productProvider == '')
		{
			$getQuantity = true;
			$select['CALLBACK_FUNC'] = true;
			$select['PRODUCT_PROVIDER_CLASS'] = true;
		}
		if ($productID <= 0)
		{
			$getQuantity = true;
			$select['PRODUCT_ID'] = true;
		}
		if ($notes == '')
			$select['NOTES'] = true;
		if ($getQuantity)
		{
			$select['QUANTITY'] = true;
			$select['MODULE'] = true;
		}
		unset($getQuantity);

		if (!empty($select))
		{
			$basketIterator = CSaleBasket::GetList(
				array(),
				array('ID' => $ID),
				false,
				false,
				array_keys($select)
			);
			$basket = $basketIterator->Fetch();
			if (empty($basket))
				return;
			if (isset($select['CALLBACK_FUNC']))
				$callbackFunc = trim((string)$basket['CALLBACK_FUNC']);
			if (isset($select['PRODUCT_PROVIDER_CLASS']))
				$productProvider = trim((string)$basket['PRODUCT_PROVIDER_CLASS']);
			if (isset($select['MODULE']))
				$module = trim((string)$basket['MODULE']);
			if (isset($select['PRODUCT_ID']))
				$productID = (int)$basket['PRODUCT_ID'];
			if (isset($select['QUANTITY']))
				$quantity = (float)$basket['QUANTITY'];
			if (isset($select['NOTES']))
				$notes = $basket['NOTES'];
			unset($basket, $basketIterator);
		}

		$providerName = CSaleBasket::GetProductProvider([
			"MODULE" => $module,
			"PRODUCT_PROVIDER_CLASS" => $productProvider,
		]);
		if ($providerName)
		{
			$arFields = $providerName::GetProductData([
				"PRODUCT_ID" => $productID,
				"QUANTITY"   => $quantity,
				"RENEWAL"    => $renewal,
				"BASKET_ID" => $ID,
				"NOTES" => $notes,
			]);
		}
		else
		{
			$arFields = CSaleBasket::ExecuteCallbackFunction($callbackFunc, $module, $productID, $quantity, $renewal);
		}

		if (!empty($arFields) && is_array($arFields))
		{
			$arFields["CAN_BUY"] = "Y";
			CSaleBasket::Update($ID, $arFields);
		}
		else
		{
			$arFields = array(
				"CAN_BUY" => "N"
			);
			CSaleBasket::Update($ID, $arFields);
		}
	}

	/**
	 * @deprecated deprecated since sale 14.0.6
	 * @see CSaleBasket::DoSaveOrderBasket
	 *
	 * @param int $orderID
	 * @param int $fuserID
	 * @param mixed|string $strLang
	 * @param bool $arDiscounts
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function OrderBasket($orderID, $fuserID = 0, $strLang = SITE_ID, $arDiscounts = False)
	{
		$orderID = (int)$orderID;
		if ($orderID <= 0)
			return false;

		$fuserID = (int)$fuserID;
		if ($fuserID <= 0)
			$fuserID = (int)CSaleBasket::GetBasketUserID(true);
		if ($fuserID <= 0)
			return false;

		$isOrderConverted = Option::get("main", "~sale_converted_15", 'Y');

		$arOrder = array();

		if (empty($arOrder))
		{
			$rsOrders = CSaleOrder::GetList(
				array(),
				array('ID' => $orderID),
				false,
				false,
				array('ID', 'USER_ID', 'RECURRING_ID', 'LID', 'RESERVED')
			);
			if (!($arOrder = $rsOrders->Fetch()))
				return false;
			$arOrder['RECURRING_ID'] = (int)$arOrder['RECURRING_ID'];
		}
		$boolRecurring = $arOrder['RECURRING_ID'] > 0;

		$found = false;
		$dbBasketList = CSaleBasket::GetList(
			array("PRICE" => "DESC"),
			array("FUSER_ID" => $fuserID, "LID" => $strLang, "ORDER_ID" => 0),
			false,
			false,
			array(
				'ID', 'ORDER_ID', 'PRODUCT_ID', 'MODULE',
				'CAN_BUY', 'DELAY', 'ORDER_CALLBACK_FUNC', 'PRODUCT_PROVIDER_CLASS',
				'QUANTITY', 'CUSTOM_PRICE'
			)
		);
		while ($arBasket = $dbBasketList->Fetch())
		{
			$arFields = array();
			if ($arBasket["DELAY"] == "N" && $arBasket["CAN_BUY"] == "Y")
			{
				if (!empty($arBasket["ORDER_CALLBACK_FUNC"]) || !empty($arBasket["PRODUCT_PROVIDER_CLASS"]))
				{
					/** @var $productProvider IBXSaleProductProvider */
					if ($productProvider = CSaleBasket::GetProductProvider($arBasket))
					{
						$arQuery = array(
							"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
							"QUANTITY"   => $arBasket["QUANTITY"],
							'BASKET_ID' => $arBasket['ID']
						);
						if ($boolRecurring)
						{
							$arQuery['RENEWAL'] = 'Y';
							$arQuery['USER_ID'] = $arOrder['USER_ID'];
							$arQuery['SITE_ID'] = $strLang;
						}
						$arFields = $productProvider::OrderProduct($arQuery);
					}
					else
					{
						if ($boolRecurring)
						{
							$arFields = CSaleBasket::ExecuteCallbackFunction(
								$arBasket["ORDER_CALLBACK_FUNC"],
								$arBasket["MODULE"],
								$arBasket["PRODUCT_ID"],
								$arBasket["QUANTITY"],
								'Y',
								$arOrder['USER_ID'],
								$strLang
							);
						}
						else
						{
							$arFields = CSaleBasket::ExecuteCallbackFunction(
								$arBasket["ORDER_CALLBACK_FUNC"],
								$arBasket["MODULE"],
								$arBasket["PRODUCT_ID"],
								$arBasket["QUANTITY"]
							);
						}
					}

					if (!empty($arFields) && is_array($arFields))
					{
						$arFields["CAN_BUY"] = "Y";
						$arFields["ORDER_ID"] = $orderID;
						$arBasket['CUSTOM_PRICE'] = (string)$arBasket['CUSTOM_PRICE'];
						if ($arBasket['CUSTOM_PRICE'] == 'Y')
						{
							if (array_key_exists('PRICE', $arFields))
								unset($arFields['PRICE']);
							if (array_key_exists('DISCOUNT_PRICE', $arFields))
								unset($arFields['DISCOUNT_PRICE']);
							if (array_key_exists('CURRENCY', $arFields))
								unset($arFields['CURRENCY']);
							if (array_key_exists('DISCOUNT_VALUE', $arFields))
								unset($arFields['DISCOUNT_VALUE']);
							if (array_key_exists('DISCOUNT_NAME', $arFields))
								unset($arFields['DISCOUNT_NAME']);
							if (array_key_exists('DISCOUNT_COUPON', $arFields))
								unset($arFields['DISCOUNT_COUPON']);
							if (array_key_exists('DISCOUNT_LIST', $arFields))
								unset($arFields['DISCOUNT_LIST']);
							if (array_key_exists('DISCOUNT', $arFields))
								unset($arFields['DISCOUNT']);
						}
					}
					else
					{
						$arFields = array(
							'CAN_BUY' => 'N'
						);
						$removeCoupon = DiscountCouponsManager::deleteApplyByProduct(array(
							'MODULE' => $arBasket['MODULE'],
							'PRODUCT_ID' => $arBasket['PRODUCT_ID'],
							'BASKET_ID' => $arBasket['ID']
						));
					}
				}
				else
				{
					$arFields["ORDER_ID"] = $orderID;
				}

				if (!empty($arFields))
				{
					if ($isOrderConverted != 'N')
					{
						$found = true;
						if (!\Bitrix\Sale\Compatible\DiscountCompatibility::isInited())
							\Bitrix\Sale\Compatible\DiscountCompatibility::init();
						if (\Bitrix\Sale\Compatible\DiscountCompatibility::isInited())
							\Bitrix\Sale\Compatible\DiscountCompatibility::setRepeatSave(true);
					}
					CSaleBasket::Update($arBasket["ID"], $arFields);
				}
			}
		}//end of while

		foreach(GetModuleEvents("sale", "OnBasketOrder", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($orderID, $fuserID, $strLang, $arDiscounts));
		}

		if ($isOrderConverted != 'N' && $found)
		{
			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

			/** @var Sale\Order $orderClass */
			$orderClass = $registry->getOrderClassName();

			if ($order = $orderClass::load($orderID))
			{
				if (\Bitrix\Sale\Compatible\DiscountCompatibility::isInited())
					\Bitrix\Sale\Compatible\DiscountCompatibility::setRepeatSave(false);
				$discounts = $order->getDiscount();
				$discounts->save();
				unset($discounts);
			}
			unset($order);
		}
		//reservation
		if ($arOrder['RESERVED'] != "Y" && COption::GetOptionString("sale", "product_reserve_condition") == "O")
		{
			if (!CSaleOrder::ReserveOrder($orderID, "Y"))
				return false;
		}
		return true;
	}

	public static function OrderPayment($orderID, $bPaid, $recurringID = 0)
	{
		CSaleBasket::OrderDelivery($orderID, $bPaid, $recurringID);
	}

	public static function OrderDelivery($orderID, $bPaid, $recurringID = 0)
	{
		global $DB, $APPLICATION;

		$orderID = intval($orderID);
		if ($orderID <= 0)
			return False;

		$bPaid = ($bPaid ? True : False);

		$recurringID = intval($recurringID);

		$arOrder = CSaleOrder::GetByID($orderID);
		if ($arOrder)
		{
			$dbBasketList = CSaleBasket::GetList(
				array("NAME" => "ASC"),
				array("ORDER_ID" => $orderID)
			);

			while ($arBasket = $dbBasketList->Fetch())
			{
				if ($arBasket["PAY_CALLBACK_FUNC"] <> '' || $arBasket["PRODUCT_PROVIDER_CLASS"] <> '')
				{
					if ($bPaid)
					{
						/** @var $productProvider IBXSaleProductProvider */
						if ($productProvider = CSaleBasket::GetProductProvider($arBasket))
						{
							$arFields = $productProvider::DeliverProduct(array(
								"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
								"USER_ID" => $arOrder["USER_ID"],
								"PAID" => $bPaid,
								"ORDER_ID" => $orderID,
								'BASKET_ID' => $arBasket['ID']
							));
						}
						else
						{
							$arFields = CSaleBasket::ExecuteCallbackFunction(
								$arBasket["PAY_CALLBACK_FUNC"],
								$arBasket["MODULE"],
								$arBasket["PRODUCT_ID"],
								$arOrder["USER_ID"],
								$bPaid,
								$orderID,
								$arBasket["QUANTITY"]
							);
						}

						if ($arFields && is_array($arFields) && count($arFields) > 0)
						{
							$arFields["ORDER_ID"] = $orderID;
							$arFields["REMAINING_ATTEMPTS"] = (Defined("SALE_PROC_REC_ATTEMPTS") ? SALE_PROC_REC_ATTEMPTS : 3);
							$arFields["SUCCESS_PAYMENT"] = "Y";

							if ($recurringID > 0)
								CSaleRecurring::Update($recurringID, $arFields);
							else
								CSaleRecurring::Add($arFields);
						}
						elseif ($recurringID > 0)
						{
							CSaleRecurring::Delete($recurringID);
						}
					}
					else
					{
						/** @var $productProvider IBXSaleProductProvider */
						if ($productProvider = CSaleBasket::GetProductProvider($arBasket))
						{
							$productProvider::DeliverProduct(array(
								"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
								"USER_ID" => $arOrder["USER_ID"],
								"PAID" => $bPaid,
								"ORDER_ID" => $orderID,
								'BASKET_ID' => $arBasket['ID']
							));
						}
						else
						{
							CSaleBasket::ExecuteCallbackFunction(
								$arBasket["PAY_CALLBACK_FUNC"],
								$arBasket["MODULE"],
								$arBasket["PRODUCT_ID"],
								$arOrder["USER_ID"],
								$bPaid,
								$orderID,
								$arBasket["QUANTITY"]
							);
						}

						$dbRecur = CSaleRecurring::GetList(
							array(),
							array(
								"USER_ID" => $arOrder["USER_ID"],
								"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
								"MODULE" => $arBasket["MODULE"]
							)
						);
						while ($arRecur = $dbRecur->Fetch())
						{
							CSaleRecurring::Delete($arRecur["ID"]);
						}
					}
				}
			}
		}
	}

	public static function OrderCanceled($orderID, $bCancel)
	{
		global $DB;

		$orderID = intval($orderID);
		if ($orderID <= 0)
			return False;

		$bCancel = (bool)$bCancel;

		$isOrderConverted = Option::get("main", "~sale_converted_15", 'Y');

		if ($isOrderConverted != 'N')
		{
			\Bitrix\Sale\Compatible\OrderCompatibility::cancel($orderID, $bCancel?'Y':'N');
		}
		else
		{
			$arOrder = CSaleOrder::GetByID($orderID);
			if ($arOrder)
			{
				$dbBasketList = CSaleBasket::GetList(
					array("NAME" => "ASC"),
					array("ORDER_ID" => $orderID)
				);
				while ($arBasket = $dbBasketList->Fetch())
				{
					if ($arBasket["CANCEL_CALLBACK_FUNC"] <> '' && $arBasket["PRODUCT_PROVIDER_CLASS"] == '')
					{
						$arFields = CSaleBasket::ExecuteCallbackFunction(
							$arBasket["CANCEL_CALLBACK_FUNC"],
							$arBasket["MODULE"],
							$arBasket["PRODUCT_ID"],
							$arBasket["QUANTITY"],
							$bCancel
						);
					}
				}
			}
		}
	}

	/**
	 * Method is called to reserve all products in the order basket
	 *
	 * @param int $orderID
	 * @param bool $bUndoReservation
	 * @return mixed array
	 */
	public static function OrderReservation($orderID, $bUndoReservation = false)
	{
		global $APPLICATION;

		if (defined("SALE_DEBUG") && SALE_DEBUG)
		{
			if ($bUndoReservation)
				CSaleHelper::WriteToLog("OrderReservation: undo started", array("orderId" => $orderID), "OR1");
			else
				CSaleHelper::WriteToLog("OrderReservation: started", array("orderId" => $orderID), "OR1");
		}

		$orderID = (int)$orderID;
		if ($orderID <= 0)
			return false;

		$arResult = array();
		$arSetData = array();

		$arOrder = CSaleOrder::GetByID($orderID);
		if ($arOrder)
		{
			$obStackExp = $APPLICATION->GetException();
			if (is_object($obStackExp))
			{
				$APPLICATION->ResetException();
			}

			$dbBasketList = CSaleBasket::GetList(
				array(),
				array("ORDER_ID" => $orderID)
			);
			while ($arBasket = $dbBasketList->Fetch())
			{
				if ($bUndoReservation && $arBasket["RESERVED"] == "N" && COption::GetOptionString("catalog", "enable_reservation") != "N")
					continue;

				if (CSaleBasketHelper::isSetParent($arBasket))
					continue;

				if (CSaleBasketHelper::isSetItem($arBasket))
					$arSetData[$arBasket["PRODUCT_ID"]] = $arBasket["SET_PARENT_ID"];

				if (defined("SALE_DEBUG") && SALE_DEBUG)
					CSaleHelper::WriteToLog("Reserving product #".$arBasket["PRODUCT_ID"], array(), "OR2");

				/** @var $productProvider IBXSaleProductProvider */
				if ($productProvider = CSaleBasket::GetProductProvider($arBasket))
				{
					if (defined("SALE_DEBUG") && SALE_DEBUG)
					{
						CSaleHelper::WriteToLog(
							"Call ::ReserveProduct",
							array(
								"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
								"QUANTITY_ADD" => $arBasket["QUANTITY"],
								"UNDO_RESERVATION" => ($bUndoReservation) ? "Y" : "N"
							),
							"OR3"
						);
					}

					if ($arOrder["DEDUCTED"] == "Y") // order already deducted, don't reserve it
					{
						$res = array("RESULT" => true, "QUANTITY_RESERVED" => 0);

						if (defined("SALE_DEBUG") && SALE_DEBUG)
							CSaleHelper::WriteToLog("Order already deducted. Product won't be reserved.", array(), "OR5");
					}
					else
					{
						$res = $productProvider::ReserveProduct(array(
																	"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
																	"QUANTITY_ADD" => $arBasket["QUANTITY"],
																	"UNDO_RESERVATION" => ($bUndoReservation) ? "Y" : "N",
																));
					}

					if ($res["RESULT"])
					{
						$arResult[$arBasket["PRODUCT_ID"]] = $res["QUANTITY_RESERVED"];

						$arUpdateFields = array("RESERVED" => ($bUndoReservation) ? "N" : "Y");

						if (!$bUndoReservation && isset($res["QUANTITY_NOT_RESERVED"]))
							$arUpdateFields["RESERVE_QUANTITY"] = $res["QUANTITY_NOT_RESERVED"];

						if (defined("SALE_DEBUG") && SALE_DEBUG)
							CSaleHelper::WriteToLog("Product #".$arBasket["PRODUCT_ID"]." reserved successfully", array("arUpdateFields" => $arUpdateFields), "OR4");

						if (!isset($res["QUANTITY_RESERVED"]) || (isset($res["QUANTITY_RESERVED"]) && $res["QUANTITY_RESERVED"] != 0))
							CSaleBasket::Update($arBasket["ID"], $arUpdateFields);
					}
					else
					{
						if (defined("SALE_DEBUG") && SALE_DEBUG)
							CSaleHelper::WriteToLog("Product #".$arBasket["PRODUCT_ID"]." reservation error", array(), "OR4");

						CSaleBasket::Update($arBasket["ID"], array("RESERVED" => "N"));
					}

					if ($ex = $APPLICATION->GetException())
					{
						if (defined("SALE_DEBUG") && SALE_DEBUG)
						{
							CSaleHelper::WriteToLog(
								"Call ::ReserveProduct - Exception",
								array(
									"ID" => $arBasket["PRODUCT_ID"],
									"MESSAGE" => $ex->GetString(),
									"CODE" => $ex->GetID(),
								),
								"OR4"
							);
						}

						$arResult["ERROR"][$arBasket["PRODUCT_ID"]]["ID"] = $arBasket["PRODUCT_ID"];
						$arResult["ERROR"][$arBasket["PRODUCT_ID"]]["MESSAGE"] = $ex->GetString();
						$arResult["ERROR"][$arBasket["PRODUCT_ID"]]["CODE"] = $ex->GetID();
					}
				}
			}
			if (is_object($obStackExp))
			{
				$APPLICATION->ResetException();
				$APPLICATION->ThrowException($obStackExp);
			}
		}

		if (defined("SALE_DEBUG") && SALE_DEBUG)
			CSaleHelper::WriteToLog("OrderReservation result", array("arResult" => $arResult), "OR6");

		return $arResult;
	}

	/**
	 * Method is called to reserve one product in the basket
	 * (it's a wrapper around product provider ReserveProduct method to use for the single product)
	 *
	 * @param int $basketID
	 * @param float $deltaQuantity - quantity to reserve
	 * @param bool $isOrderDeducted
	 * @return mixed array
	 */
	public static function ReserveBasketProduct($basketID, $deltaQuantity, $isOrderDeducted = false)
	{
		global $APPLICATION;

		if (defined("SALE_DEBUG") && SALE_DEBUG)
		{
			CSaleHelper::WriteToLog(
				"ReserveBasketProduct: reserving product #".$basketID,
				array(
					"basketId" => $basketID,
					"deltaQuantity" => $deltaQuantity
				),
				"RBP1"
			);
		}

		$arResult = array();

		$basketID = (int)$basketID;
		if ($basketID <= 0)
		{
			$arResult["RESULT"] = false;
			return $arResult;
		}

		$deltaQuantity = (float)$deltaQuantity;
		if ($deltaQuantity < 0)
		{
			$deltaQuantity = abs($deltaQuantity);
			$bUndoReservation = true;
		}
		else
		{
			$bUndoReservation = false;
		}

		$arBasket = CSaleBasket::GetByID($basketID);

		if ($arBasket)
		{
			/** @var $productProvider IBXSaleProductProvider */
			if ($productProvider = CSaleBasket::GetProductProvider($arBasket))
			{
				if (defined("SALE_DEBUG") && SALE_DEBUG)
				{
					CSaleHelper::WriteToLog(
						"Call ::ReserveProduct",
						array(
							"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
							"QUANTITY_ADD" => $deltaQuantity,
							"UNDO_RESERVATION" => ($bUndoReservation) ? "Y" : "N",
							"ORDER_DEDUCTED" => ($isOrderDeducted) ? "Y" : "N"
						),
						"RBP2"
					);
				}

				$res = $productProvider::ReserveProduct(array(
															"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
															"QUANTITY_ADD" => $deltaQuantity,
															"UNDO_RESERVATION" => ($bUndoReservation) ? "Y" : "N",
															"ORDER_DEDUCTED" => ($isOrderDeducted) ? "Y" : "N"
														));

				$updateResult = true;
				$arResult["RESULT"] = $res["RESULT"];
				if ($res["RESULT"])
				{
					$arResult[$arBasket["ID"]] = $res["QUANTITY_RESERVED"];

					if (defined("SALE_DEBUG") && SALE_DEBUG)
						CSaleHelper::WriteToLog("Product #".$arBasket["PRODUCT_ID"]." reserved successfully", array(), "RBP3");

					if ($bUndoReservation)
					{
						$updateResult = CSaleBasket::Update($arBasket["ID"], array("RESERVED" => "N"));
					}
					elseif (!isset($res["QUANTITY_RESERVED"]) || (isset($res["QUANTITY_RESERVED"]) && $res["QUANTITY_RESERVED"] != 0))
					{
						$updateResult = CSaleBasket::Update($arBasket["ID"], array("RESERVED" => "Y"));
					}
				}
				else
				{
					$arResult["ERROR"]["PRODUCT_ID"] = $arBasket["PRODUCT_ID"];
					$updateResult = false;

					if (defined("SALE_DEBUG") && SALE_DEBUG)
						CSaleHelper::WriteToLog("Product #".$arBasket["PRODUCT_ID"]." reservation error", array(), "RBP3");

					if (isset($res["QUANTITY_NOT_RESERVED"]))
					{
						CSaleBasket::Update($arBasket["ID"], array("RESERVE_QUANTITY" => $res["QUANTITY_NOT_RESERVED"]));
					}
				}

				if (!$updateResult && $ex = $APPLICATION->GetException())
				{
					$arResult["ERROR"]["MESSAGE"] = $ex->GetString();
					$arResult["ERROR"]["CODE"] = $ex->GetID();
				}
			}
		}

		if (defined("SALE_DEBUG") && SALE_DEBUG)
			CSaleHelper::WriteToLog("ReserveBasketProduct result", array("arResult" => $arResult), "RBP5");

		return $arResult;
	}

	/**
	 * Method is called to deduct one product in the basket
	 * (it's a wrapper around product provider DeductProduct method to use for the single product)
	 *
	 * @param int $basketID
	 * @param float $deltaQuantity - quantity to reserve
	 * @param array $arStoreBarcodeData
	 * @return mixed array
	 */
	public static function DeductBasketProduct($basketID, $deltaQuantity, $arStoreBarcodeData = array())
	{
		if (defined("SALE_DEBUG") && SALE_DEBUG)
		{
			CSaleHelper::WriteToLog("DeductBasketProduct",
									array(
										"basketId" => $basketID,
										"deltaQuantity" => $deltaQuantity,
										"storeBarcodeData" => $arStoreBarcodeData
									),
									"DBP1"
			);
		}

		global $APPLICATION;
		$arResult = array();

		$basketID = (int)$basketID;
		if ($basketID <= 0)
		{
			$arResult["RESULT"] = false;
			return $arResult;
		}

		$deltaQuantity = (float)$deltaQuantity;
		if ($deltaQuantity < 0)
		{
			$deltaQuantity = abs($deltaQuantity);
			$bUndoDeduction = true;
		}
		else
		{
			$bUndoDeduction = false;
		}

		$arBasket = CSaleBasket::GetByID($basketID);
		if ($arBasket)
		{
			/** @var $productProvider IBXSaleProductProvider */
			if ($productProvider = CSaleBasket::GetProductProvider($arBasket))
			{
				if (defined("SALE_DEBUG") && SALE_DEBUG)
				{
					CSaleHelper::WriteToLog(
						"Call ::DeductProduct",
						array(
							"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
							"QUANTITY" => (empty($arStoreBarcodeData)) ? $deltaQuantity : 0,
							"UNDO_DEDUCTION" => ($bUndoDeduction) ? "Y" : "N",
							"EMULATE" => "N",
							"PRODUCT_RESERVED" => $arBasket["RESERVED"],
							"STORE_DATA" => $arStoreBarcodeData
						),
						"DBP2"
					);
				}

				if ($bUndoDeduction)
				{
					$dbStoreBarcode = CSaleStoreBarcode::GetList(
						array(),
						array("BASKET_ID" => $arBasket["ID"]),
						false,
						false,
						array("ID", "BASKET_ID", "BARCODE", "QUANTITY", "STORE_ID")
					);
					while ($arRes = $dbStoreBarcode->GetNext())
						$arStoreBarcodeData[] = $arRes;
				}

				$res = $productProvider::DeductProduct(array(
					"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
					"QUANTITY" => (empty($arStoreBarcodeData)) ? $deltaQuantity : 0,
					"UNDO_DEDUCTION" => ($bUndoDeduction) ? "Y" : "N",
					"EMULATE" => "N",
					"PRODUCT_RESERVED" => $arBasket["RESERVED"],
					"STORE_DATA" => $arStoreBarcodeData
				));

				$arResult["RESULT"] = $res["RESULT"];

				if ($res["RESULT"])
				{
					if (defined("SALE_DEBUG") && SALE_DEBUG)
						CSaleHelper::WriteToLog("Product #".$arBasket["PRODUCT_ID"]." deducted successfully", array(), "DBP3");
				}
				else
				{
					$arResult["ERROR"]["PRODUCT_ID"] = $arBasket["PRODUCT_ID"];

					if ($ex = $APPLICATION->GetException())
					{
						$arResult["ERROR"]["MESSAGE"] = $ex->GetString();
						$arResult["ERROR"]["CODE"] = $ex->GetID();
					}

					if (defined("SALE_DEBUG") && SALE_DEBUG)
						CSaleHelper::WriteToLog("Product #".$arBasket["PRODUCT_ID"]." deduction error", array(), "DBP4");
				}
			}
		}

		if (defined("SALE_DEBUG") && SALE_DEBUG)
			CSaleHelper::WriteToLog("DeductBasketProduct result", array("arResult" => $arResult), "DBP5");

		return $arResult;
	}

	/**
	 * Method is called to deduct all products of the order or undo deduction
	 *
	 * @param int $orderID
	 * @param bool $bUndoDeduction
	 * @param int $recurringID
	 * @param bool $bAutoDeduction
	 * @param array $arStoreBarcodeOrderFormData
	 * @return mixed array
	 */
	public static function OrderDeduction($orderID, $bUndoDeduction = false, $recurringID = 0, $bAutoDeduction = true, $arStoreBarcodeOrderFormData  = array())
	{
		global $APPLICATION;
		static $storesCount = NULL;
		static $bAutoDeductionAllowed = NULL;
		$bRealDeductionAllowed = true;
		$defaultDeductionStore = 0;
		$arSavedStoreBarcodeData = array();
		$arItems = array();
		$arResult = array();

		$isOrderConverted = Option::get("main", "~sale_converted_15", 'Y');

		if (defined("SALE_DEBUG") && SALE_DEBUG)
		{
			CSaleHelper::WriteToLog(
				"OrderDeduction: started",
				array(
					"orderID" => $orderID,
					"bUndoDeduction" => intval($bUndoDeduction),
					"bAutoDeduction" => intval($bAutoDeduction),
					"arStoreBarcodeOrderFormData" => $arStoreBarcodeOrderFormData
				),
				"OD1"
			);
		}

		//TODO - recurringID - ?
		$orderID = intval($orderID);
		if ($orderID <= 0)
		{
			$arResult["RESULT"] = false;
			return $arResult;
		}

		if ($isOrderConverted != 'N')
		{
			$ship = !$bUndoDeduction;
			/** @var \Bitrix\Sale\Result $r */
			$r = \Bitrix\Sale\Compatible\OrderCompatibility::shipment($orderID, $ship, $arStoreBarcodeOrderFormData);
			if (!$r->isSuccess(true))
			{
				foreach($r->getErrorMessages() as $error)
				{
//					$APPLICATION->ThrowException($error);

					$arResult["ERROR"]["MESSAGE"] = $error;
//					$arResult["ERROR"]["CODE"] = $ex->GetID();
					break;
				}

				$arResult["RESULT"] = false;
				return $arResult;
			}

			$arResult["RESULT"] = true;

			return $arResult;
		}

		$dbBasketList = CSaleBasket::GetList(
			array(),
			array("ORDER_ID" => $orderID),
			false,
			false,
			array('ID', 'LID', 'PRODUCT_ID', 'PRODUCT_PROVIDER_CLASS', 'MODULE', 'BARCODE_MULTI', 'QUANTITY', 'RESERVED', 'TYPE', 'SET_PARENT_ID')
		);

		//check basket items and emulate deduction
		while ($arBasket = $dbBasketList->Fetch())
		{
			if (CSaleBasketHelper::isSetParent($arBasket))
				continue;

			if (defined("SALE_DEBUG") && SALE_DEBUG)
				CSaleHelper::WriteToLog("Deducting product #".$arBasket["PRODUCT_ID"], array(), "OD2");

			/** @var $productProvider IBXSaleProductProvider */
			if ($productProvider = CSaleBasket::GetProductProvider($arBasket))
			{
				if (is_null($storesCount))
					$storesCount = intval($productProvider::GetStoresCount(array("SITE_ID" => $arBasket["LID"])));

				if (defined("SALE_DEBUG") && SALE_DEBUG)
					CSaleHelper::WriteToLog("stores count: ".$storesCount, array(), "OD3");

				if (is_null($bAutoDeductionAllowed))
				{
					$defaultDeductionStore = COption::GetOptionString("sale", "deduct_store_id", "", $arBasket["LID"]);

					if ($storesCount == 1 || $storesCount == -1 || intval($defaultDeductionStore) > 0) // if stores' count = 1 or stores aren't used or default deduction store is defined
						$bAutoDeductionAllowed = true;
					else
						$bAutoDeductionAllowed = false;
				}

				if (defined("SALE_DEBUG") && SALE_DEBUG)
					CSaleHelper::WriteToLog("auto deduction allowed: ".intval($bAutoDeductionAllowed), array(), "OD4");

				if ($bAutoDeduction && !$bAutoDeductionAllowed && !$bUndoDeduction)
				{
					if (defined("SALE_DEBUG") && SALE_DEBUG)
						CSaleHelper::WriteToLog("DDCT_AUTO_DEDUCT_WRONG_STORES_QUANTITY", array(), "OD5");

					$APPLICATION->ThrowException(Loc::getMessage("DDCT_AUTO_DEDUCT_WRONG_STORES_QUANTITY"), "DDCT_WRONG_STORES_QUANTITY");
					$bRealDeductionAllowed = false;
				}
				else if ($bAutoDeduction && $arBasket["BARCODE_MULTI"] == "Y" && !$bUndoDeduction)
				{
					if (defined("SALE_DEBUG") && SALE_DEBUG)
						CSaleHelper::WriteToLog("DDCT_AUTO_DEDUCT_BARCODE_MULTI", array(), "OD6");

					$APPLICATION->ThrowException(Loc::getMessage("DDCT_AUTO_DEDUCT_BARCODE_MULTI", array("#PRODUCT_ID#" => $arBasket["PRODUCT_ID"])), "DDCT_CANT_DEDUCT_BARCODE_MULTI");
					$bRealDeductionAllowed = false;
				}
				else
				{
					//get saved store & barcode data if stores are used to know where to return products
					if ($bUndoDeduction && $storesCount > 0)
					{
						$dbStoreBarcode = CSaleStoreBarcode::GetList(
							array(),
							array("BASKET_ID" => $arBasket["ID"]),
							false,
							false,
							array("ID", "BASKET_ID", "BARCODE", "QUANTITY", "STORE_ID")
						);
						while ($arStoreBarcode = $dbStoreBarcode->Fetch())
						{
							$arSavedStoreBarcodeData[$arBasket["ID"]][] = $arStoreBarcode;
						}

						if (defined("SALE_DEBUG") && SALE_DEBUG)
						{
							CSaleHelper::WriteToLog(
								"OrderDeduction: CSaleStoreBarcode data (stores) to return products to",
								array(
									"arSavedStoreBarcodeData" => $arSavedStoreBarcodeData
								),
								"OD7"
							);
						}
					}

					$arFields = array(
						"PRODUCT_ID"	 => $arBasket["PRODUCT_ID"],
						"EMULATE"		 => "Y",
						"PRODUCT_RESERVED" => $arBasket["RESERVED"],
						"UNDO_DEDUCTION" => ($bUndoDeduction) ? "Y" : "N"
					);

					if ($bUndoDeduction)
					{
						if ($storesCount > 0)
						{
							$arFields["QUANTITY"] = 0; //won't be used during deduction
							$arFields["STORE_DATA"] = $arSavedStoreBarcodeData[$arBasket["ID"]];
						}
						else
						{
							$arFields["QUANTITY"] = $arBasket["QUANTITY"];
							$arFields["STORE_DATA"] = array();
						}
					}
					else
					{
						if ($storesCount == 1)
						{
							$arFields["QUANTITY"] = 0;

							if ($bAutoDeduction) //get the only possible store to deduct from it
							{
								$arProductStore = $productProvider::GetProductStores(array(
									"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
									"SITE_ID" => $arBasket["LID"],
									'BASKET_ID' => $arBasket['ID']
								));
								if ($arProductStore)
								{
									$arFields["STORE_DATA"] = array(
										"0" => array(
											"STORE_ID" => $arProductStore[0]["STORE_ID"],
											"QUANTITY" => $arBasket["QUANTITY"],
											"AMOUNT"   => $arProductStore[0]["AMOUNT"]
										)
									);
								}
								else
								{
									$arFields["STORE_DATA"] = array();
								}
							}
							else
							{
								$arFields["STORE_DATA"] = $arStoreBarcodeOrderFormData[$arBasket["ID"]];
							}
						}
						else if (intval($defaultDeductionStore) > 0) // if default deduction store is defined
						{
							$arFields["QUANTITY"] = 0;

							if ($bAutoDeduction)
							{
								$arProductStore = $productProvider::GetProductStores(array(
									"PRODUCT_ID" => $arBasket["PRODUCT_ID"],
									"SITE_ID" => $arBasket["LID"],
									'BASKET_ID' => $arBasket['ID']
								));
								if ($arProductStore)
								{
									foreach ($arProductStore as $storeData)
									{
										if ($storeData["STORE_ID"] == intval($defaultDeductionStore))
										{
											$arFields["STORE_DATA"] = array(
												"0" => array(
													"STORE_ID" => $storeData["STORE_ID"],
													"QUANTITY" => $arBasket["QUANTITY"],
													"AMOUNT"   => $storeData["AMOUNT"]
												)
											);
											break;
										}
									}
								}
								else
								{
									$arFields["STORE_DATA"] = array();
								}
							}
							else
							{
								$arFields["STORE_DATA"] = $arStoreBarcodeOrderFormData[$arBasket["ID"]];
							}
						}
						else if ($storesCount > 1)
						{
							$arFields["QUANTITY"] = 0; //won't be used during deduction
							$arFields["STORE_DATA"] = $arStoreBarcodeOrderFormData[$arBasket["ID"]];
						}
						else //store control not used
						{
							$arFields["QUANTITY"] = $arBasket["QUANTITY"];
							$arFields["STORE_DATA"] = array();
						}
					}

					if (defined("SALE_DEBUG") && SALE_DEBUG)
						CSaleHelper::WriteToLog("Emulating ::DeductProduct call", array("arFields" => $arFields), "OD7");

					$eventParams = array(
						'ORDER_ID' => $orderID,
						'RECURRING_ID' => $recurringID,
						'AUTO_DEDUCTION' => $bAutoDeduction,
						'STORE_DATA' => $arStoreBarcodeOrderFormData
					);

					foreach (GetModuleEvents('sale', 'OnBeforeBasketDeductProduct', true) as $event)
					{
						if (ExecuteModuleEventEx($event, array($eventParams, $arBasket, &$arFields)) === false)
						{
							if (defined("SALE_DEBUG") && SALE_DEBUG)
								CSaleHelper::WriteToLog("Emulating ::DeductProduct call - error", array(), "OD7-1");
							$arResult["RESULT"] = false;
							return $arResult;
						}
					}
					unset($eventParams);

					//emulate deduction
					$res = $productProvider::DeductProduct($arFields);

					if ($res["RESULT"])
					{
						$arBasket["FIELDS"] = $arFields;
						$arItems[] = $arBasket;

						if (defined("SALE_DEBUG") && SALE_DEBUG)
							CSaleHelper::WriteToLog("Emulating ::DeductProduct call - success", array(), "OD8");
					}
					else
					{
						$bRealDeductionAllowed = false;

						if (defined("SALE_DEBUG") && SALE_DEBUG)
							CSaleHelper::WriteToLog("Emulating ::DeductProduct call - error", array(), "OD9");
					}
				}

				if ($ex = $APPLICATION->GetException())
				{
					$arResult["ERROR"]["MESSAGE"] = $ex->GetString();
					$arResult["ERROR"]["CODE"] = $ex->GetID();
				}

				if (!$bRealDeductionAllowed)
					break;
			}
		}

		// real deduction
		if ($bRealDeductionAllowed)
		{
			$bProductsDeductedSuccessfully = true;
			$arDeductedItems = array();
			foreach ($arItems as $arItem)
			{
				/** @var $productProvider IBXSaleProductProvider */
				if ($productProvider = CSaleBasket::GetProductProvider($arItem))
				{
					$arItem["FIELDS"]["EMULATE"] = "N";

					if (defined("SALE_DEBUG") && SALE_DEBUG)
						CSaleHelper::WriteToLog("Call ::DeductProduct", array("fields" => $arItem["FIELDS"]), "OD10");

					// finally real deduction
					$res = $productProvider::DeductProduct($arItem["FIELDS"]);

					if ($res["RESULT"])
					{
						$arDeductedItems[] = $arItem;

						if (!$bUndoDeduction && $storesCount > 0)
						{
							if ($bAutoDeduction)
							{
								$storeList = array_keys($res["STORES"]);
								$storeId = array_pop($storeList);
								$arStoreBarcodeFields = array(
									"BASKET_ID"   => $arItem["ID"],
									"BARCODE"     => "",
									"STORE_ID"    => $storeId,
									"QUANTITY"    => $arItem["QUANTITY"],
									"CREATED_BY"  => ((intval($GLOBALS["USER"]->GetID())>0) ? intval($GLOBALS["USER"]->GetID()) : ""),
									"MODIFIED_BY" => ((intval($GLOBALS["USER"]->GetID())>0) ? intval($GLOBALS["USER"]->GetID()) : ""),
								);

								if (defined("SALE_DEBUG") && SALE_DEBUG)
									CSaleHelper::WriteToLog("Call CSaleStoreBarcode::Add (auto deduction = true)", array("arStoreBarcodeFields" => $arStoreBarcodeFields), "OD11");

								CSaleStoreBarcode::Add($arStoreBarcodeFields);
							}
						}

						if ($bUndoDeduction)
						{
							$dbStoreBarcode = CSaleStoreBarcode::GetList(array(), array("BASKET_ID" => $arItem["ID"]), false, false, array("ID", "BASKET_ID"));
							while ($arStoreBarcode = $dbStoreBarcode->GetNext())
								CSaleStoreBarcode::Delete($arStoreBarcode["ID"]);
						}

						$tmpRes = ($bUndoDeduction) ? "N" : "Y";
						CSaleBasket::Update($arItem["ID"], array("DEDUCTED" => $tmpRes));

						// set parent deducted status
						if ($bUndoDeduction)
						{
							if (CSaleBasketHelper::isSetItem($arItem))
								CSaleBasket::Update($arItem["SET_PARENT_ID"], array("DEDUCTED" => "N"));
						}
						else
						{
							if (CSaleBasketHelper::isSetItem($arItem) && CSaleBasketHelper::isSetDeducted($arItem["SET_PARENT_ID"]))
								CSaleBasket::Update($arItem["SET_PARENT_ID"], array("DEDUCTED" => "Y"));
						}

						if (defined("SALE_DEBUG") && SALE_DEBUG)
							CSaleHelper::WriteToLog("Call ::DeductProduct - Success (DEDUCTED = ".$tmpRes.")", array(), "OD11");
					}
					else
					{
						CSaleBasket::Update($arItem["ID"], array("DEDUCTED" => "N"));
						$bProductsDeductedSuccessfully = false;

						if ($ex = $APPLICATION->GetException())
						{
							$arResult["ERROR"]["MESSAGE"] = $ex->GetString();
							$arResult["ERROR"]["CODE"] = $ex->GetID();
						}

						if (defined("SALE_DEBUG") && SALE_DEBUG)
							CSaleHelper::WriteToLog("Call ::DeductProduct - Error (DEDUCTED = N)", array(), "OD12");

						break;
					}
				}
			}

			if ($bProductsDeductedSuccessfully)
			{
				$arResult["RESULT"] = true;
			}
			else //revert real deduction if error happened
			{
				$arFields = array();
				foreach ($arDeductedItems as $arItem)
				{
					/** @var $productProvider IBXSaleProductProvider */
					if ($productProvider = CSaleBasket::GetProductProvider($arItem))
					{
						if ($storesCount > 0)
						{
							$arFields = array(
								"PRODUCT_ID"     => $arItem["PRODUCT_ID"],
								"QUANTITY"       => $arItem["QUANTITY"],
								"UNDO_DEDUCTION" => "Y",
								"EMULATE"        => "N",
								"PRODUCT_RESERVED" => $arItem["FIELDS"]["PRODUCT_RESERVED"],
								"STORE_DATA"     => $arItem["FIELDS"]["STORE_DATA"] //during auto deduction
							);
						}
						else
						{
							$arFields = array(
								"PRODUCT_ID"     => $arItem["PRODUCT_ID"],
								"QUANTITY"       => $arItem["QUANTITY"],
								"UNDO_DEDUCTION" => "Y",
								"PRODUCT_RESERVED" => $arItem["FIELDS"]["PRODUCT_RESERVED"],
								"EMULATE"        => "N",
							);
						}

						if (defined("SALE_DEBUG") && SALE_DEBUG)
						{
							CSaleHelper::WriteToLog(
								"Call ::DeductProduct - Revert deduction", array(
								"storesCount" => $storesCount,
								"arFields" => $arFields
							),
								"OD13"
							);
						}

						$res = $productProvider::DeductProduct($arFields);

						if ($res["RESULT"])
						{
							CSaleBasket::Update($arItem["ID"], array("DEDUCTED" => "N"));

							if (CSaleBasketHelper::isSetItem($arItem)) // todo - possibly not all the time, but once
								CSaleBasket::Update($arItem["SET_PARENT_ID"], array("DEDUCTED" => "N"));
						}
					}
				}

				$arResult["RESULT"] = false;
			}
		}
		else
		{
			$arResult["RESULT"] = false;
		}

		if (defined("SALE_DEBUG") && SALE_DEBUG)
			CSaleHelper::WriteToLog("OrderDeduction - result", array("arResult" => $arResult), "OD14");

		return $arResult;
	}

	public static function TransferBasket($FROM_FUSER_ID, $TO_FUSER_ID)
	{
		$FROM_FUSER_ID = (int)$FROM_FUSER_ID;
		$TO_FUSER_ID = (int)$TO_FUSER_ID;

		if ($TO_FUSER_ID > 0 && $FROM_FUSER_ID > 0)
		{
			$dbTmp = CSaleUser::GetList(array("ID" => $TO_FUSER_ID));
			if (!empty($dbTmp))
			{
				$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

				/** @var Sale\Basket $basketClass */
				$basketClass = $registry->getBasketClassName();

				$basketToFUser = $basketClass::loadItemsForFUser($TO_FUSER_ID, SITE_ID);
				$basketFromFUser = $basketClass::loadItemsForFUser($FROM_FUSER_ID, SITE_ID);

				if ($basketFromFUser->count() > 0)
				{
					/** @var Sale\BasketItem $basketItemFrom */
					foreach ($basketFromFUser as $basketItemFrom)
					{
						/** @var Sale\BasketItem $basketItem */
						$basketItemFromProperties =
							($tmp = $basketItemFrom->getPropertyCollection())
								? $tmp->getPropertyValues()
								: []
						;
						$basketItems = $basketToFUser->getExistsItems(
							$basketItemFrom->getField('MODULE'),
							$basketItemFrom->getField('PRODUCT_ID'),
							$basketItemFromProperties
						);

						if (count($basketItems) === 1)
						{
							$basketItem = current($basketItems);
							$basketItem->setField('QUANTITY', $basketItem->getQuantity() + $basketItemFrom->getQuantity());
							$basketItemFrom->delete();
						}
						/*
						 * if there is no such product in the 'toFUser' basket
						 * or there are several of them, then add a new basket item with the same product.
						 */
						else
						{
							$basketItemFrom->setFieldNoDemand('FUSER_ID', $TO_FUSER_ID);
						}
					}

					$basketToFUser->save();
					$basketFromFUser->save();
				}
				Sale\BasketComponentHelper::updateFUserBasket($TO_FUSER_ID, SITE_ID);
				Sale\BasketComponentHelper::updateFUserBasket($FROM_FUSER_ID, SITE_ID);

				Sale\Discount\RuntimeCache\FuserCache::getInstance()->clean();

				return true;
			}
		}
		return false;
	}

	public static function UpdateBasketPrices($fuserID, $siteID, array $options = array())
	{
		$fuserID = (int)$fuserID;
		if ($fuserID <= 0)
			return false;
		$siteID = (string)$siteID;
		if ($siteID == '')
			$siteID = SITE_ID;

		$isOrderConverted = Option::get("main", "~sale_converted_15", 'Y');

		if ($isOrderConverted != 'N')
		{
			self::refreshFUserBasket($fuserID, $siteID, $options);
		}
		else
		{

			$dbBasketItems = CSaleBasket::GetList(
				array("ALL_PRICE" => "DESC"),
				array(
					"FUSER_ID" => $fuserID,
					"LID" => $siteID,
					"ORDER_ID" => "NULL",
					"SUBSCRIBE" => "N"
				),
				false,
				false,
				array(
					"ID", "MODULE", "PRODUCT_ID", "QUANTITY",
					"CALLBACK_FUNC", "PRODUCT_PROVIDER_CLASS",
					"CAN_BUY", "DELAY", "NOTES",
					"TYPE", "SET_PARENT_ID"
				)
			);
			while ($arItem = $dbBasketItems->Fetch())
			{
				$basketItems[] = $arItem;
			}

			if (!empty($basketItems) && is_array($basketItems))
			{
				$basketItems = getRatio($basketItems);

				foreach ($basketItems as $basketItem)
				{
					$fields = array();
					$basketItem['CALLBACK_FUNC'] = (string)$basketItem['CALLBACK_FUNC'];
					$basketItem['PRODUCT_PROVIDER_CLASS'] = (string)$basketItem['PRODUCT_PROVIDER_CLASS'];

					if (strval(trim($basketItem['PRODUCT_PROVIDER_CLASS'])) !== '' || strval(trim($basketItem['CALLBACK_FUNC'])) !== '')
					{
						$basketItem['MODULE'] = (string)$basketItem['MODULE'];
						$basketItem['PRODUCT_ID'] = (int)$basketItem['PRODUCT_ID'];
						$basketItem['QUANTITY'] = (float)$basketItem['QUANTITY'];

						if ($productProvider = CSaleBasket::GetProductProvider($basketItem))
						{
							$fields = $productProvider::GetProductData(array(
								"PRODUCT_ID" => $basketItem["PRODUCT_ID"],
								"QUANTITY" => $basketItem["QUANTITY"],
								"RENEWAL" => "N",
								"CHECK_COUPONS" => ('Y' == $basketItem['CAN_BUY'] && 'N' == $basketItem['DELAY'] ? 'Y' : 'N'),
								"CHECK_DISCOUNT" => (CSaleBasketHelper::isSetItem($basketItem) ? 'N' : 'Y'),
								"BASKET_ID" => $basketItem["ID"],
								"NOTES" => $basketItem["NOTES"]
							));
						}
						else
						{
							$fields = CSaleBasket::ExecuteCallbackFunction(
								$basketItem["CALLBACK_FUNC"],
								$basketItem["MODULE"],
								$basketItem["PRODUCT_ID"],
								$basketItem["QUANTITY"],
								"N"
							);
						}

						if (!empty($fields) && is_array($fields))
						{
							if ($isOrderConverted != 'N' && $basketItem['DELAY'] == 'N')
							{
								if (!Sale\Compatible\DiscountCompatibility::isInited())
									Sale\Compatible\DiscountCompatibility::init();
								if (Sale\Compatible\DiscountCompatibility::usedByClient())
									Sale\Compatible\DiscountCompatibility::setBasketItemData($basketItem['ID'], $fields);
							}
							$fields['CAN_BUY'] = 'Y';
							$fields['TYPE'] = (int)$basketItem['TYPE'];
							$fields['SET_PARENT_ID'] = (int)$basketItem['SET_PARENT_ID'];
						}
						else
						{
							$fields = array('CAN_BUY' => 'N');
						}


					}

					if (array_key_exists('MEASURE_RATIO', $basketItem))
					{
						$basketItemQuantity = floatval($basketItem['QUANTITY']);
						$basketItemRatio = floatval($basketItem['MEASURE_RATIO']);

						$mod = roundEx(($basketItemQuantity / $basketItemRatio - round($basketItemQuantity / $basketItemRatio)), 6);

						if ($mod != 0)
						{
							$fields['QUANTITY'] = floor(ceil($basketItemQuantity) / $basketItemRatio) * $basketItemRatio;
						}
					}

					if (!empty($fields) && is_array($fields))
					{
						CSaleBasket::Update($basketItem['ID'], $fields);
					}
				}
			}
		}
		return true;
	}

	/**
	 * @internal
	 * @param $fuserID
	 * @param $siteID
	 * @param array $options
	 *
	 * @return Sale\Result
	 */
	public static function refreshFUserBasket($fuserID, $siteID, array $options = array())
	{
		$result = new Sale\Result();

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClass */
		$basketClass = $registry->getBasketClassName();

		/** @var Sale\Basket $basket */
		$basket = $basketClass::loadItemsForFUser($fuserID, $siteID);
		if ($basket->count() > 0)
		{
			if (!Sale\Compatible\DiscountCompatibility::isInited())
				Sale\Compatible\DiscountCompatibility::init();

			$select = array("PRICE", 'QUANTITY', "COUPONS");
			$basket->refreshData($select);
			$warnings = array();

			if (!empty($options) && array_key_exists('CORRECT_RATIO', $options) && $options['CORRECT_RATIO'] === 'Y')
			{
				$r = Sale\BasketComponentHelper::correctQuantityRatio($basket);
				if (!$r->isSuccess())
				{
					foreach ($r->getErrors() as $error)
					{
						if ($error instanceof Sale\ResultWarning)
						{
							$warnings[] = $error;
						}
						elseif ($error instanceof Sale\ResultError)
						{
							$result->addError($error);
						}
					}
				}
			}

			if ($result->isSuccess())
			{
				$r = $basket->save();
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
				}
			}

			$basketList = array();
			/** @var Sale\BasketItem $basketItem */
			foreach ($basket as $basketItem)
			{
				$basketList[$basketItem->getId()] = $basketItem->getFieldValues();
			}

			$result->addData(array('BASKET_LIST' => $basketList));

			if (!empty($warnings))
			{
				$result->addWarnings($warnings);
			}
		}

		return $result;
	}

	/**
	 * @param array $newProperties
	 * @param array $oldProperties
	 * @return bool|null
	 */
	public static function compareBasketProps($newProperties, $oldProperties)
	{
		$result = null;
		if (!is_array($newProperties) || !is_array($oldProperties))
			return $result;
		$result = true;
		if (empty($newProperties) && empty($oldProperties))
			return $result;
		$compareNew = array();
		$compareOld = array();

		foreach ($newProperties as &$property)
		{
			if (!isset($property['VALUE']))
				continue;
			$property['VALUE'] = (string)$property['VALUE'];
			if ($property['VALUE'] == '')
				continue;

			$propertyID = '';
			if (isset($property['CODE']))
			{
				$property['CODE'] = (string)$property['CODE'];
				if ($property['CODE'] != '')
					$propertyID = $property['CODE'];
			}
			if ($propertyID == '' && isset($property['NAME']))
			{
				$property['NAME'] = (string)$property['NAME'];
				if ($property['NAME'] != '')
					$propertyID = $property['NAME'];
			}
			if ($propertyID == '')
				continue;
			$compareNew[$propertyID] = $property['VALUE'];
		}
		unset($property);

		foreach ($oldProperties as &$property)
		{
			if (!isset($property['VALUE']))
				continue;
			$property['VALUE'] = (string)$property['VALUE'];
			if ($property['VALUE'] == '')
				continue;

			$propertyID = '';
			if (isset($property['CODE']))
			{
				$property['CODE'] = (string)$property['CODE'];
				if ($property['CODE'] != '')
					$propertyID = $property['CODE'];
			}
			if ($propertyID == '' && isset($property['NAME']))
			{
				$property['NAME'] = (string)$property['NAME'];
				if ($property['NAME'] != '')
					$propertyID = $property['NAME'];
			}
			if ($propertyID == '')
				continue;
			$compareOld[$propertyID] = $property['VALUE'];
		}
		unset($property);

		$result = false;
		if (count($compareNew) == count($compareOld))
		{
			$result = true;
			foreach($compareNew as $key => $val)
			{
				if (!isset($compareOld[$key]) || $compareOld[$key] != $val)
				{
					$result = false;
					break;
				}
			}
		}
		unset($compareOld, $compareNew);

		return $result;
	}

	/**
	 * @internal
	 * @return array
	 */
	public static function getRoundFields()
	{
		$isOrderConverted = Option::get("main", "~sale_converted_15", 'Y');
		if ($isOrderConverted != 'N')
		{
			$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
			/** @var Sale\BasketItemBase $basketItemClassName */
			$basketItemClassName = $registry->getBasketItemClassName();
			return $basketItemClassName::getRoundFields();
		}

		return array(
			'DISCOUNT_PRICE',
			'DISCOUNT_PRICE_PERCENT',
		);
	}
}

class CAllSaleUser
{
	/**
	 * Creates new anonymous user with e-mail 'anonymous_some_number@example.com' and returns his ID
	 * Used mainly in CRM
	 *
	 * @return int|string|false - new user ID or ID of already existing anonymous user, 0 if error
	 */
	public static function GetAnonymousUserID()
	{
		$bUserExists = false;

		$anonUserID = intval(COption::GetOptionInt("sale", "anonymous_user_id", 0));

		if ($anonUserID > 0)
		{
			$dbUser = CUser::GetList('id', 'asc', array("ID_EQUAL_EXACT"=>$anonUserID), array("FIELDS"=>array("ID")));
			if ($arUser = $dbUser->Fetch())
				$bUserExists = true;
		}

		if (!$bUserExists)
		{
			$anonUserEmail = "anonymous_".randString(9)."@example.com";
			$arErrors = array();
			$anonUserID = CSaleUser::DoAutoRegisterUser(
				$anonUserEmail,
				array("NAME" => Loc::getMessage("SU_ANONYMOUS_USER_NAME")),
				SITE_ID,
				$arErrors,
				array(
					'ACTIVE' => 'N',
					'EXTERNAL_AUTH_ID' => 'saleanonymous',
				)
			);

			if ($anonUserID > 0)
			{
				COption::SetOptionInt("sale", "anonymous_user_id", $anonUserID);
			}
			else
			{
				$errorMessage = "";
				if (!empty($arErrors))
				{
					$errorMessage = " ";
					foreach ($arErrors as $value)
					{
						$errorMessage .= $value["TEXT"]."<br>";
					}
				}

				$GLOBALS["APPLICATION"]->ThrowException(Loc::getMessage("SU_ANONYMOUS_USER_CREATE", array("#ERROR#" => $errorMessage)), "ANONYMOUS_USER_CREATE_ERROR");
				return 0;
			}
		}

		return $anonUserID;
	}

	public static function DoAutoRegisterUser($autoEmail, $payerName, $siteId, &$arErrors, $arOtherFields = null)
	{
		if ($siteId == null)
			$siteId = SITE_ID;

		$autoEmail = trim($autoEmail);

		$autoName = "";
		$autoLastName = "";
		$autoSecondName = "";

		if (!is_array($payerName) && ($payerName <> ''))
		{
			$arNames = explode(" ", $payerName);
			$autoName = $arNames[1];
			$autoLastName = $arNames[0];
			$autoSecondName = (!empty($arNames[2]) ? trim($arNames[2]) : false);
		}
		elseif (is_array($payerName))
		{
			$autoName = $payerName["NAME"] ?? '';
			$autoLastName = $payerName["LAST_NAME"] ?? '';
			$autoSecondName = $payerName["SECOND_NAME"] ?? '';
		}

		if (!empty($autoEmail))
		{
			$autoLogin = $autoEmail;

			$pos = mb_strpos($autoLogin, "@");
			if ($pos !== false)
				$autoLogin = mb_substr($autoLogin, 0, $pos);

			if (mb_strlen($autoLogin) > 47)
				$autoLogin = mb_substr($autoLogin, 0, 47);

			while (mb_strlen($autoLogin) < 3)
				$autoLogin .= "_";
		}
		else
		{
			$autoLogin = "buyer";
		}

		$idx = 0;
		$loginTmp = $autoLogin;
		$dbUserLogin = CUser::GetByLogin($autoLogin);
		while ($arUserLogin = $dbUserLogin->Fetch())
		{
			$idx++;

			if ($idx <= 10)
				$autoLogin = $loginTmp.$idx;
			else
				$autoLogin = "buyer".time().GetRandomCode(2);

			$dbUserLogin = CUser::GetByLogin($autoLogin);
		}

		$groups = [];

		if (\Bitrix\Main\ModuleManager::isModuleInstalled('intranet') && \Bitrix\Main\Loader::includeModule('crm'))
		{
			$groups = \Bitrix\Crm\Order\BuyerGroup::getDefaultGroups();
		}
		else
		{
			$defaultGroup = COption::GetOptionString("main", "new_user_registration_def_group", "");

			if (!empty($defaultGroup))
			{
				$groups = explode(",", $defaultGroup);
			}
		}

		$autoPassword = \CUser::GeneratePasswordByPolicy($groups);

		$arFields = array(
			"LOGIN" => $autoLogin,
			"PASSWORD" => $autoPassword,
			"PASSWORD_CONFIRM" => $autoPassword,
			"GROUP_ID" => $groups,
			"LID" => $siteId,
			// reset department for intranet
			'UF_DEPARTMENT' => []
		);

		if($autoName <> '')
			$arFields["NAME"] = $autoName;

		if($autoLastName <> '')
			$arFields["LAST_NAME"] = $autoLastName;

		if($autoSecondName <> '')
			$arFields["SECOND_NAME"] = $autoSecondName;

		if($autoEmail <> '')
			$arFields["EMAIL"] = $autoEmail;

		$arFields["ACTIVE"] = (isset($arOtherFields["ACTIVE"]) && $arOtherFields["ACTIVE"] == "N") ? "N" : "Y";
		if (isset($arOtherFields["ACTIVE"]))
			unset($arOtherFields["ACTIVE"]);

		if (is_array($arOtherFields))
		{
			foreach ($arOtherFields as $key => $value)
			{
				if (!array_key_exists($key, $arFields))
					$arFields[$key] = $value;
			}
		}

		$user = new CUser;
		$userId = $user->Add($arFields);

		if (intval($userId) <= 0)
		{
			$arErrors[] = array("TEXT" => Loc::getMessage("STOF_ERROR_REG").(($user->LAST_ERROR <> '') ? ": ".$user->LAST_ERROR : ""));
			return 0;
		}

		return $userId;
	}

	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		return true;
	}

	/**
	 * @deprecated
	 * @see Sale\Fuser::getId
	 *
	 * @param bool $bSkipFUserInit
	 * @return int
	 */
	public static function GetID($bSkipFUserInit = false)
	{
		$bSkipFUserInit = ($bSkipFUserInit !== false);

		return (int)Sale\Fuser::getId($bSkipFUserInit);
	}

	/**
	 * @deprecated
	 * @see Sale\Fuser::update
	 *
	 * @param $ID
	 * @param $allowUpdate
	 * @return true
	 */
	public static function Update($ID, $allowUpdate = true)
	{
		Sale\Fuser::update(
			(int)$ID,
			[
				'update' => (bool)$allowUpdate,
			]
		);

		return true;
	}

	/**
	 * @deprecated
	 *
	 * @param $ID
	 * @param $arFields
	 * @return false|int
	 */
	public static function _Update($ID, $arFields)
	{
		global $DB;

		$DB->StartUsingMasterOnly();

		$ID = intval($ID);
		if ($ID <= 0)
			return False;

		$arFields1 = array();
		foreach ($arFields as $key => $value)
		{
			if (mb_substr($key, 0, 1) == "=")
			{
				$arFields1[mb_substr($key, 1)] = $value;
				unset($arFields[$key]);
			}
		}

		if (!CSaleUser::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_fuser", $arFields);

		foreach ($arFields1 as $key => $value)
		{
			if ($strUpdate <> '') $strUpdate .= ", ";
			$strUpdate .= $key."=".$value." ";
		}

		$strSql = "UPDATE b_sale_fuser SET ".$strUpdate." WHERE ID = ".$ID." ";
		$DB->Query($strSql);

		$DB->StopUsingMasterOnly();

		return $ID;
	}

	public static function GetList($arFilter)
	{
		global $DB;
		$arSqlSearch = Array();

		if (!is_array($arFilter))
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		$countarFilter = count($filter_keys);
		for ($i=0; $i < $countarFilter; $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = mb_substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			if (strval($val) == "")
				$val = 0;

			switch(mb_strtoupper($key))
			{
				case "ID":
					$arSqlSearch[] = "ID ".($bInvert?"<>":"=")." ".intval($val)." ";
					break;
				case "USER_ID":
					$arSqlSearch[] = "USER_ID ".($bInvert?"<>":"=")." ".intval($val)." ";
					break;
				case "CODE":
					$arSqlSearch[] = "CODE ".($bInvert?"<>":"=")." '".$DB->ForSql($val)."' ";
					break;
			}
		}

		$strSqlSearch = "";
		$countSqlSearch = count($arSqlSearch);
		for($i=0; $i < $countSqlSearch; $i++)
		{
			$strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql = "SELECT ID, DATE_INSERT, DATE_UPDATE, USER_ID, CODE FROM b_sale_fuser WHERE 1 = 1 ".$strSqlSearch." ORDER BY ID DESC";
		$db_res = $DB->Query($strSql);
		return $db_res->Fetch();
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		foreach(GetModuleEvents("sale", "OnSaleUserDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, Array($ID));

		$DB->Query("DELETE FROM b_sale_fuser WHERE ID = ".$ID." ", true);

		return true;
	}

	public static function OnUserLogin($new_user_id, array $params = [])
	{
		Sale\Fuser::handlerOnUserLogin($new_user_id, $params);
	}

	/**
	 * @deprecated
	 * @see Sale\Fuser::refreshSessionCurrentId
	 *
	 * @return int
	 */
	public static function UpdateSessionSaleUserID()
	{
		return (int)Sale\Fuser::refreshSessionCurrentId();
	}

	/**
	 * @deprecated
	 * @see Sale\Fuser::getRegeneratedId()
	 *
	 * @return int
	 */
	public static function getFUserCode()
	{
		$result = Sale\Fuser::getRegeneratedId();

		return $result ?? 0;
	}

	public static function OnUserLogout($userID)
	{
		Sale\Fuser::handlerOnUserLogout($userID);
	}

	public static function DeleteOldAgent($nDays, $speed = 0)
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		/** @var Sale\Basket $basketClass */
		$basketClass = $registry->getBasketClassName();

		return $basketClass::deleteOldAgent($nDays, $speed);
	}

	public static function OnUserDelete($userID)
	{
		if($userID<=0)
			return false;
		$arSUser = CSaleUser::GetList(array("USER_ID" => $userID));
		if(!empty($arSUser))
		{
			if(!(CSaleBasket::DeleteAll($arSUser["ID"])))
				return false;
			if(!(CSaleUser::Delete($arSUser["ID"])))
				return false;
		}
		return true;
	}
}
