<?php

IncludeModuleLangFile(__FILE__);

const SALE_TIME_LOCK_USER = 600;
$GLOBALS["SALE_USER_ACCOUNT"] = array();

/***********************************************************************/
/***********  CSaleUserAccount  ****************************************/
/***********************************************************************/
class CAllSaleUserAccount
{
	public static function DoPayOrderFromAccount($userId, $currency, $orderId, $orderSum, $arOptions, &$arErrors)
	{
		if (!array_key_exists("ONLY_FULL_PAY_FROM_ACCOUNT", $arOptions))
			$arOptions["ONLY_FULL_PAY_FROM_ACCOUNT"] = COption::GetOptionString("sale", "ONLY_FULL_PAY_FROM_ACCOUNT", "N");

		$dbUserAccount = CSaleUserAccount::GetList(
			array(),
			array(
				"USER_ID" => $userId,
				"CURRENCY" => $currency,
			)
		);
		$arUserAccount = $dbUserAccount->Fetch();

		if (!$arUserAccount)
			return false;
		if ($arUserAccount["CURRENT_BUDGET"] <= 0)
			return false;
		if (($arOptions["ONLY_FULL_PAY_FROM_ACCOUNT"] == "Y") && (doubleval($arUserAccount["CURRENT_BUDGET"]) < doubleval($orderSum)))
			return false;

		$withdrawSum = CSaleUserAccount::Withdraw(
			$userId,
			$orderSum,
			$currency,
			$orderId
		);

		if ($withdrawSum > 0)
		{
			$arFields = array(
				"SUM_PAID" => $withdrawSum,
				"USER_ID" => $userId
			);

			CSaleOrder::Update($orderId, $arFields);
			if ($withdrawSum == $orderSum)
				CSaleOrder::PayOrder($orderId, "Y", False, False);

			return true;
		}

		return false;
	}

	//********** ADD, UPDATE, DELETE **************//
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "USER_ID") || $ACTION=="ADD") && intval($arFields["USER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGU_EMPTY_USER_ID"), "EMPTY_USER_ID");
			return false;
		}
		if ((is_set($arFields, "CURRENCY") || $ACTION=="ADD") && $arFields["CURRENCY"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGU_EMPTY_CURRENCY"), "EMPTY_CURRENCY");
			return false;
		}

		if (is_set($arFields, "CURRENT_BUDGET") || $ACTION=="ADD")
		{
			$arFields["CURRENT_BUDGET"] = str_replace(",", ".", $arFields["CURRENT_BUDGET"]);
			$arFields["CURRENT_BUDGET"] = doubleval($arFields["CURRENT_BUDGET"]);
		}

		if ((is_set($arFields, "LOCKED") || $ACTION=="ADD") && $arFields["LOCKED"] != "Y")
			$arFields["LOCKED"] = "N";

		if (is_set($arFields, "USER_ID"))
		{
			$dbUser = CUser::GetByID($arFields["USER_ID"]);
			if (!$dbUser->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["USER_ID"], GetMessage("SKGU_NO_USER")), "ERROR_NO_USER_ID");
				return false;
			}
		}

		return true;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = (int)$ID;
		if ($ID <= 0)
			return False;

		foreach (GetModuleEvents('sale', 'OnBeforeUserAccountDelete', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($ID))===false)
			{
				return false;
			}
		}

		$arOldUserAccount = CSaleUserAccount::GetByID($ID);

		$dbTrans = CSaleUserTransact::GetList(
			array(),
			array("USER_ID" => $arOldUserAccount["USER_ID"], "CURRENCY" => $arOldUserAccount["CURRENCY"]),
			false,
			false,
			array("ID", "USER_ID")
		);
		while($arTrans = $dbTrans -> Fetch())
			CSaleUserTransact::Delete($arTrans["ID"]);

		unset($GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$ID]);
		unset($GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$arOldUserAccount["USER_ID"]."_".$arOldUserAccount["CURRENCY"]]);

		$res = $DB->Query("DELETE FROM b_sale_user_account WHERE ID = ".$ID." ", true);

		foreach (GetModuleEvents('sale', 'OnAfterUserAccountDelete', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, Array($ID));
		}

		return $res;
	}

	//********** LOCK **************//
	public static function Lock($userID, $payCurrency)
	{
		global $DB, $APPLICATION;

		$userID = (int)$userID;
		if ($userID <= 0)
			return false;

		$payCurrency = trim($payCurrency);
		if ($payCurrency == '')
			return false;

		CTimeZone::Disable();
		$dbUserAccount = CSaleUserAccount::GetList(
				array(),
				array("USER_ID" => $userID, "CURRENCY" => $payCurrency),
				false,
				false,
				array("ID", "LOCKED", "DATE_LOCKED")
			);
		CTimeZone::Enable();
		if ($arUserAccount = $dbUserAccount->Fetch())
		{
			$dateLocked = 0;
			if ($arUserAccount["LOCKED"] == "Y")
			{
				if (!($dateLocked = MakeTimeStamp($arUserAccount["DATE_LOCKED"], CSite::GetDateFormat("FULL", SITE_ID))))
					$dateLocked = mktime(0, 0, 0, 1, 1, 1990);
			}

			if (defined("SALE_TIME_LOCK_USER") && intval(SALE_TIME_LOCK_USER) > 0)
				$timeLockUser = intval(SALE_TIME_LOCK_USER);
			else
				$timeLockUser = 10 * 60;

			if (($arUserAccount["LOCKED"] != "Y")
				|| (($arUserAccount["LOCKED"] == "Y") && ((time() - $dateLocked) > $timeLockUser)))
			{
				$arFields = array(
						"LOCKED" => "Y",
						"=DATE_LOCKED" => $DB->GetNowFunction()
					);
				if (CSaleUserAccount::Update($arUserAccount["ID"], $arFields))
					return true;
				else
					return false;
			}
			else
			{
				$APPLICATION->ThrowException(GetMessage("SKGU_ACCOUNT_LOCKED"), "ACCOUNT_LOCKED");
				return false;
			}
		}
		else
		{
			$arFields = array(
					"USER_ID" => $userID,
					"CURRENT_BUDGET" => 0.0,
					"CURRENCY" => $payCurrency,
					"LOCKED" => "Y",
					"=TIMESTAMP_X" => $DB->GetNowFunction(),
					"=DATE_LOCKED" => $DB->GetNowFunction()
				);
			if (CSaleUserAccount::Add($arFields))
				return true;
			else
				return false;
		}
	}

	public static function UnLock($userID, $payCurrency)
	{
		$userID = (int)$userID;
		if ($userID <= 0)
			return false;

		$payCurrency = trim($payCurrency);
		if ($payCurrency == '')
			return false;

		CTimeZone::Disable();
		$dbUserAccount = CSaleUserAccount::GetList(
				array(),
				array("USER_ID" => $userID, "CURRENCY" => $payCurrency),
				false,
				false,
				array("ID", "LOCKED", "DATE_LOCKED")
			);
		CTimeZone::Enable();
		if ($arUserAccount = $dbUserAccount->Fetch())
		{
			if ($arUserAccount["LOCKED"] == "Y")
			{
				$arFields = array(
						"LOCKED" => "N",
						"DATE_LOCKED" => false
					);
				if (CSaleUserAccount::Update($arUserAccount["ID"], $arFields))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return true;
			}
		}
		else
		{
			$arFields = array(
					"USER_ID" => $userID,
					"CURRENT_BUDGET" => 0.0,
					"CURRENCY" => $payCurrency,
					"LOCKED" => "N",
					"DATE_LOCKED" => false
				);
			if (CSaleUserAccount::Add($arFields))
				return true;
			else
				return false;
		}
	}

	public static function UnLockByID($ID)
	{
		global $APPLICATION;

		$ID = (int)$ID;
		if ($ID <= 0)
			return false;

		if ($arUserAccount = CSaleUserAccount::GetByID($ID))
		{
			if ($arUserAccount["LOCKED"] == "Y")
			{
				$arFields = array(
						"LOCKED" => "N",
						"DATE_LOCKED" => false
					);
				if (CSaleUserAccount::Update($arUserAccount["ID"], $arFields))
					return true;
				else
					return false;
			}
			else
			{
				return true;
			}
		}
		else
		{
			$APPLICATION->ThrowException(GetMessage("SKGU_NO_ACCOUNT"), "NO_ACCOUNT");
			return false;
		}
	}

	//********** ACTIONS **************//

	// Pay money from the local user account. Increase the local user account if necessary.
	// $userID - ID of the user
	// $paySum - payment sum
	// $payCurrency - currency
	// $orderID - ID of order (if known)
	// $useCC - increase the local user account from credit card if necessary (default - True)
	// Return True if the necessary sum withdraw from an account or False in other way
	public static function Pay($userID, $paySum, $payCurrency, $orderID = 0, $useCC = true, $paymentId = null)
	{
		global $DB, $APPLICATION, $USER;

		$errorCode = "";

		$userID = (int)$userID;
		if ($userID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SKGU_EMPTY_USER_ID"), "EMPTY_USER_ID");
			return false;
		}

		$paySum = str_replace(",", ".", $paySum);
		$paySum = (float)$paySum;
		if ($paySum <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SKGU_EMPTY_SUM"), "EMPTY_SUM");
			return false;
		}

		$payCurrency = trim($payCurrency);
		if ($payCurrency == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGU_EMPTY_CURRENCY"), "EMPTY_CURRENCY");
			return false;
		}

		$orderID = (int)$orderID;
		$paymentId = (int)$paymentId;

		$useCC = ($useCC ? true : false);

		if (!CSaleUserAccount::Lock($userID, $payCurrency))
		{
			$APPLICATION->ThrowException(GetMessage("SKGU_ERROR_LOCK"), "ACCOUNT_NOT_LOCKED");
			return false;
		}

		$currentBudget = 0.0;

		// Check current user account budget
		$dbUserAccount = CSaleUserAccount::GetList(
				array(),
				array("USER_ID" => $userID, "CURRENCY" => $payCurrency)
			);
		if ($arUserAccount = $dbUserAccount->Fetch())
			$currentBudget = roundEx((float)$arUserAccount["CURRENT_BUDGET"], SALE_VALUE_PRECISION);

		$withdrawSum = 0;
		if (($currentBudget < $paySum) && $useCC)
		{
			$payOverdraft = $paySum - $currentBudget;

			// Try to get money from credit cards
			$bPayed = false;
			$dbUserCards = CSaleUserCards::GetList(
					array("SORT" => "ASC"),
					array("USER_ID" => $userID, "CURRENCY" => $payCurrency, "ACTIVE" => "Y")
				);
			while ($arUserCard = $dbUserCards->Fetch())
			{
				if ($withdrawSum = CSaleUserCards::Withdraw($payOverdraft, $payCurrency, $arUserCard, $orderID))
				{
					$bPayed = true;
					break;
				}
			}

			if (!$bPayed)
			{
				$dbUserCards = CSaleUserCards::GetList(
						array("SORT" => "ASC"),
						array("USER_ID" => $userID, "CURRENCY" => "", "ACTIVE" => "Y")
					);
				while ($arUserCard = $dbUserCards->Fetch())
				{
					if ($withdrawSum = CSaleUserCards::Withdraw($payOverdraft, $payCurrency, $arUserCard, $orderID))
					{
						$bPayed = true;
						break;
					}
				}
			}

			if ($bPayed)
			{
				$arFields = array(
						"USER_ID" => $userID,
						"=TIMESTAMP_X" => $DB->GetNowFunction(),
						"TRANSACT_DATE" => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID))),
						"AMOUNT" => $withdrawSum,
						"CURRENCY" => $payCurrency,
						"DEBIT" => "Y",
						"ORDER_ID" => ($orderID > 0 ? $orderID : false),
						"PAYMENT_ID" => ($paymentId > 0 ? $paymentId : false),
						"DESCRIPTION" => "CC_CHARGE_OFF",
						"EMPLOYEE_ID" => ($USER->IsAuthorized() ? $USER->GetID() : false)
					);
				CTimeZone::Disable();
				CSaleUserTransact::Add($arFields);
				CTimeZone::Enable();

				if ($arUserAccount)
				{
					$arFields = array(
							"CURRENT_BUDGET" => ($withdrawSum + $currentBudget)
						);
					CSaleUserAccount::Update($arUserAccount["ID"], $arFields);
				}
				else
				{
					$arFields = array(
							"USER_ID" => $userID,
							"CURRENT_BUDGET" => ($withdrawSum + $currentBudget),
							"CURRENCY" => $payCurrency
						);
					CSaleUserAccount::Add($arFields);
				}
			}
		}

		if ($withdrawSum + $currentBudget >= $paySum)
		{
			if ($arUserAccount)
			{
				$arFields = array(
						"CURRENT_BUDGET" => ($withdrawSum + $currentBudget - $paySum)
					);
				CSaleUserAccount::Update($arUserAccount["ID"], $arFields);
			}
			else
			{
				$arFields = array(
						"USER_ID" => $userID,
						"CURRENT_BUDGET" => ($withdrawSum + $currentBudget - $paySum),
						"CURRENCY" => $payCurrency
					);
				CSaleUserAccount::Add($arFields);
			}

			$arFields = array(
					"USER_ID" => $userID,
					"=TIMESTAMP_X" => $DB->GetNowFunction(),
					"TRANSACT_DATE" => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID))),
					"AMOUNT" => $paySum,
					"CURRENCY" => $payCurrency,
					"DEBIT" => "N",
					"ORDER_ID" => ($orderID > 0 ? $orderID : false),
					"PAYMENT_ID" => ($paymentId > 0 ? $paymentId : false),
					"DESCRIPTION" => "ORDER_PAY",
					"EMPLOYEE_ID" => ($USER->IsAuthorized() ? $USER->GetID() : False)
				);
			CTimeZone::Disable();
			CSaleUserTransact::Add($arFields);
			CTimeZone::Enable();

			CSaleUserAccount::UnLock($userID, $payCurrency);
			return true;
		}

		CSaleUserAccount::UnLock($userID, $payCurrency);
		$APPLICATION->ThrowException(GetMessage("SKGU_NO_ENOUGH"), "CANT_PAY");

		return false;
	}

	// Pay money from the local user account. If there is not enough money on the local user
	// account then withdraw the max available sum.
	// $userID - ID of the user
	// $paySum - payment sum
	// $payCurrency - currency
	// $orderID - ID of order (if known)
	// Return withdrawn sum or False
	public static function Withdraw($userID, $paySum, $payCurrency, $orderID = 0)
	{
		global $DB, $APPLICATION, $USER;

		$errorCode = "";

		$userID = (int)$userID;
		if ($userID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SKGU_EMPTYID"), "EMPTY_USER_ID");
			return false;
		}

		$paySum = str_replace(",", ".", $paySum);
		$paySum = (float)$paySum;
		if ($paySum <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SKGU_EMPTY_SUM"), "EMPTY_SUM");
			return false;
		}

		$payCurrency = trim($payCurrency);
		if ($payCurrency == '')
		{
			$APPLICATION->ThrowException(GetMessage("SKGU_EMPTY_CUR"), "EMPTY_CURRENCY");
			return false;
		}

		$orderID = (int)$orderID;

		if (!CSaleUserAccount::Lock($userID, $payCurrency))
		{
			$APPLICATION->ThrowException(GetMessage("SKGU_ACCOUNT_NOT_LOCKED"), "ACCOUNT_NOT_LOCKED");
			return false;
		}

		$currentBudget = 0.0;

		// Check current user account budget
		$dbUserAccount = CSaleUserAccount::GetList(
				array(),
				array("USER_ID" => $userID, "CURRENCY" => $payCurrency)
			);
		if ($arUserAccount = $dbUserAccount->Fetch())
		{
			$currentBudget = (float)$arUserAccount["CURRENT_BUDGET"];

			if ($orderID > 0)
			{
				$registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);

				/** @var \Bitrix\Sale\Order $orderClass */
				$orderClass = $registry->getOrderClassName();

				/** @var \Bitrix\Sale\Order $order */
				if ($order = $orderClass::load($orderID))
				{
					/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
					if (($paymentCollection = $order->getPaymentCollection()) && $paymentCollection->isExistsInnerPayment())
					{
						/** @var \Bitrix\Sale\Payment $payment */
						if (($payment = $paymentCollection->getInnerPayment()) && $payment->isPaid())
						{
							return 0;
						}
					}
				}
			}

			if ($currentBudget > 0)
			{
				$withdrawSum = $paySum;
				if ($withdrawSum > $currentBudget)
					$withdrawSum = $currentBudget;

				$arFields = array(
						"CURRENT_BUDGET" => ($currentBudget - $withdrawSum)
					);
				CSaleUserAccount::Update($arUserAccount["ID"], $arFields);

				$arFields = array(
						"USER_ID" => $userID,
						"=TIMESTAMP_X" => $DB->GetNowFunction(),
						"TRANSACT_DATE" => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID))),
						"AMOUNT" => $withdrawSum,
						"CURRENCY" => $payCurrency,
						"DEBIT" => "N",
						"ORDER_ID" => ($orderID > 0 ? $orderID : false),
						"DESCRIPTION" => "ORDER_PAY",
						"EMPLOYEE_ID" => ($USER->IsAuthorized() ? $USER->GetID() : false)
					);
				CTimeZone::Disable();
				CSaleUserTransact::Add($arFields);
				CTimeZone::Enable();

				CSaleUserAccount::UnLock($userID, $payCurrency);
				return $withdrawSum;
			}
		}

		CSaleUserAccount::UnLock($userID, $payCurrency);
		return false;
	}

	// Modify sum of the current local user account.
	// $userID - ID of the user
	// $sum - payment sum
	// $currency - currency
	// $description - reason of modification
	// Return True on success or False in other way
	public static function UpdateAccount($userID, $sum, $currency, $description = "", $orderID = 0, $notes = "", $paymentId = null)
	{
		global $DB, $APPLICATION, $USER;

		$userID = (int)$userID;
		if ($userID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SKGU_EMPTYID"), "EMPTY_USER_ID");
			return False;
		}
		$dbUser = CUser::GetByID($userID);
		if (!$dbUser->Fetch())
		{
			$APPLICATION->ThrowException(str_replace("#ID#", $userID, GetMessage("SKGU_NO_USER")), "ERROR_NO_USER_ID");
			return False;
		}

		$sum = (float)str_replace(",", ".", $sum);

		$currency = trim($currency);
		if ($currency === '')
		{
			$APPLICATION->ThrowException(GetMessage("SKGU_EMPTY_CUR"), "EMPTY_CURRENCY");
			return False;
		}

		$orderID = (int)$orderID;
		$paymentId = (int)$paymentId;
		if (!CSaleUserAccount::Lock($userID, $currency))
		{
			$APPLICATION->ThrowException(GetMessage("SKGU_ACCOUNT_NOT_WORK"), "ACCOUNT_NOT_LOCKED");
			return False;
		}

		$currentBudget = 0.0000;

		$result = false;

		$dbUserAccount = CSaleUserAccount::GetList(
				array(),
				array("USER_ID" => $userID, "CURRENCY" => $currency)
			);
		if ($arUserAccount = $dbUserAccount->Fetch())
		{
			$currentBudget = floatval($arUserAccount["CURRENT_BUDGET"]);
			$arFields = array(
					"=TIMESTAMP_X" => $DB->GetNowFunction(),
					"CURRENT_BUDGET" => $arUserAccount["CURRENT_BUDGET"] + $sum
				);

			if (!empty($notes))
			{
				$arFields['CHANGE_REASON'] = $notes;
			}

			$result = CSaleUserAccount::Update($arUserAccount["ID"], $arFields);
		}
		else
		{
			$currentBudget = floatval($sum);
			$arFields = array(
					"USER_ID" => $userID,
					"CURRENT_BUDGET" => $sum,
					"CURRENCY" => $currency,
					"LOCKED" => "Y",
					"=TIMESTAMP_X" => $DB->GetNowFunction(),
					"=DATE_LOCKED" => $DB->GetNowFunction()
				);

			if (!empty($notes))
			{
				$arFields['CHANGE_REASON'] = $notes;
			}
			$result = CSaleUserAccount::Add($arFields);
		}

		if ($result)
		{
			if (isset($GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$userID."_".$currency]))
				unset($GLOBALS["SALE_USER_ACCOUNT"]["SALE_USER_ACCOUNT_CACHE_".$userID."_".$currency]);

			$arFields = array(
					"USER_ID" => $userID,
					"=TIMESTAMP_X" => $DB->GetNowFunction(),
					"TRANSACT_DATE" => date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL", SITE_ID))),
					"CURRENT_BUDGET" => $currentBudget,
					"AMOUNT" => ($sum > 0 ? $sum : -$sum),
					"CURRENCY" => $currency,
					"DEBIT" => ($sum > 0 ? "Y" : "N"),
					"ORDER_ID" => ($orderID > 0 ? $orderID : false),
					"PAYMENT_ID" => ($paymentId > 0 ? $paymentId : false),
					"DESCRIPTION" => (($description <> '') ? $description : null),
					"NOTES" => (($notes <> '') ? $notes : False),
					"EMPLOYEE_ID" => ($USER->IsAuthorized() ? $USER->GetID() : false)
				);
			CTimeZone::Disable();
			CSaleUserTransact::Add($arFields);
			CTimeZone::Enable();
		}

		CSaleUserAccount::UnLock($userID, $currency);
		return $result;
	}

	//********** EVENTS **************//
	public static function OnBeforeCurrencyDelete($Currency)
	{
		$Currency = (string)$Currency;
		if ($Currency === '')
			return true;

		$cnt = CSaleUserAccount::GetList(array(), array("CURRENCY" => $Currency), array());
		if ($cnt > 0)
			return false;

		return true;
	}

	public static function OnUserDelete($userID)
	{
		$userID = (int)$userID;

		$bSuccess = true;

		$dbUserAccounts = CSaleUserAccount::GetList(array(), array("USER_ID" => $userID), false, false, array("ID"));
		while ($arUserAccount = $dbUserAccounts->Fetch())
		{
			if (!CSaleUserAccount::Delete($arUserAccount["ID"]))
				$bSuccess = false;
		}

		return $bSuccess;
	}

	public static function OnBeforeUserDelete($userID)
	{
		global $APPLICATION;

		$userID = (int)$userID;

		$bCanDelete = true;

		$dbUserAccounts = CSaleUserAccount::GetList(
				array(),
				array("USER_ID" => $userID, "!CURRENT_BUDGET" => 0),
				false,
				false,
				array("ID")
			);
		if ($arUserAccount = $dbUserAccounts->Fetch())
		{
			$APPLICATION->ThrowException(str_replace("#USER_ID#", $userID, GetMessage("UA_ERROR_USER")), "ERROR_UACCOUNT");
			return false;
		}

		return $bCanDelete;
	}
}
