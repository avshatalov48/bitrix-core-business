<?php

use Bitrix\Main\Application;

IncludeModuleLangFile(__FILE__);

class CAllSaleUserCards
{
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "USER_ID") || $ACTION=="ADD") && intval($arFields["USER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty user field", "EMPTY_USER_ID");
			return false;
		}
		if ((is_set($arFields, "PAY_SYSTEM_ACTION_ID") || $ACTION=="ADD") && intval($arFields["PAY_SYSTEM_ACTION_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty pay system field", "EMPTY_PAY_SYSTEM_ACTION_ID");
			return false;
		}
		if ((is_set($arFields, "CARD_TYPE") || $ACTION=="ADD") && $arFields["CARD_TYPE"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty card type field", "EMPTY_CARD_TYPE");
			return false;
		}
		if ((is_set($arFields, "CARD_NUM") || $ACTION=="ADD") && $arFields["CARD_NUM"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty card number field", "EMPTY_CARD_NUM");
			return false;
		}
		if ((is_set($arFields, "CARD_EXP_MONTH") || $ACTION=="ADD") && (intval($arFields["CARD_EXP_MONTH"]) <= 0 || intval($arFields["CARD_EXP_MONTH"]) > 12))
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty card expiration month field", "EMPTY_CARD_EXP_MONTH");
			return false;
		}
		if ((is_set($arFields, "CARD_EXP_YEAR") || $ACTION=="ADD") && (intval($arFields["CARD_EXP_YEAR"]) <= 2000 || intval($arFields["CARD_EXP_YEAR"]) > 2100))
		{
			$GLOBALS["APPLICATION"]->ThrowException("Empty card expiration year field", "EMPTY_CARD_EXP_YEAR");
			return false;
		}

		if ((is_set($arFields, "SORT") || $ACTION=="ADD") && intval($arFields["SORT"]) <= 0)
			$arFields["SORT"] = 100;

		if ($ACTION != "ADD" && intval($ID) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGUC_NO_ID"), "NO_UC_ID");
			return false;
		}

		if (is_set($arFields, "SUM_MIN") && $arFields["SUM_MIN"] !== false)
		{
			$arFields["SUM_MIN"] = str_replace(",", ".", $arFields["SUM_MIN"]);
			$arFields["SUM_MIN"] = DoubleVal($arFields["SUM_MIN"]);
		}

		if (is_set($arFields, "SUM_MAX") && $arFields["SUM_MAX"] !== false)
		{
			$arFields["SUM_MAX"] = str_replace(",", ".", $arFields["SUM_MAX"]);
			$arFields["SUM_MAX"] = DoubleVal($arFields["SUM_MAX"]);
		}

		if ((is_set($arFields, "SUM_MIN") && $arFields["SUM_MIN"] !== false
			|| is_set($arFields, "SUM_MAX") && $arFields["SUM_MAX"] !== false))
		{
			if ((is_set($arFields, "SUM_CURRENCY") || $ACTION=="ADD") && $arFields["SUM_CURRENCY"] == '')
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGUC_NO_CURRENCY"), "EMPTY_SUM_CURRENCY");
				return false;
			}
			elseif (!is_set($arFields, "SUM_CURRENCY"))
			{
				$arUserCard = CSaleUserCard::GetByID($ID);
				if ($arUserCard["SUM_CURRENCY"] == '')
				{
					$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGUC_NO_CURRENCY"), "EMPTY_SUM_CURRENCY");
					return false;
				}
			}
		}

		if (is_set($arFields, "LAST_SUM") && $arFields["LAST_SUM"] !== false)
		{
			$arFields["LAST_SUM"] = str_replace(",", ".", $arFields["LAST_SUM"]);
			$arFields["LAST_SUM"] = DoubleVal($arFields["LAST_SUM"]);
		}

		if (is_set($arFields, "LAST_STATUS") && $arFields["LAST_STATUS"] != "Y")
			$arFields["LAST_STATUS"] = "N";

		if ((is_set($arFields, "ACTIVE") || $ACTION == "ADD") && $arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"] = "N";

		if (is_set($arFields, "USER_ID"))
		{
			$dbUser = CUser::GetByID($arFields["USER_ID"]);
			if (!$dbUser->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["USER_ID"], GetMessage("SKGUC_NO_USER")), "ERROR_NO_USER_ID");
				return false;
			}
		}

		if (is_set($arFields, "PAY_SYSTEM_ACTION_ID"))
		{
			if (!($arPaySysAction = CSalePaySystemAction::GetByID($arFields["PAY_SYSTEM_ACTION_ID"])))
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["PAY_SYSTEM_ACTION_ID"], GetMessage("SKGUC_NO_PS")), "ERROR_NO_PAY_SYSTEM_ACTION");
				return false;
			}
		}

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		unset($arFields['TIMESTAMP_X']);
		$arFields['~TIMESTAMP_X'] = $helper->getCurrentDateTimeFunction();

		return true;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return False;

		return $DB->Query("DELETE FROM b_sale_user_cards WHERE ID = ".$ID." ", true);
	}

	public static function OnUserDelete($UserID)
	{
		global $DB;
		$UserID = intval($UserID);

		return $DB->Query("DELETE FROM b_sale_user_cards WHERE USER_ID = ".$UserID." ", true);
	}

	public static function CheckPassword()
	{
		$strFileName = COption::GetOptionString("sale", "sale_data_file", "");

		$pwdString = "";
		if (file_exists($strFileName))
			include($strFileName);

		if ($pwdString == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException("Please enter valid password on Sale module global settings page", "EMPTY_PASSWORD");
			return False;
		}

		return True;
	}

	public static function CryptData($data, $type)
	{
		$type = mb_strtoupper($type);
		if ($type != "D")
			$type = "E";

		$res_data = "";

		$strFileName = COption::GetOptionString("sale", "sale_data_file", "");
		$pwdString = "";
		if (file_exists($strFileName))
			include($strFileName);

		if ($pwdString == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException("Please enter valid password on Sale module global settings page", "EMPTY_PASSWORD");
			return $data;
		}

		// The following two crypt algorithms give different output. It is imposible to switch between these algorithms!
		$cryptAlgorithm = COption::GetOptionString("sale", "crypt_algorithm", "RC4");

		if (($cryptAlgorithm == "AES" || $cryptAlgorithm == "3DES") && extension_loaded("mcrypt"))
		{
			if ($cryptAlgorithm == "AES")
				$rEncModule = mcrypt_module_open('rijndael-256', '', 'ofb', '');
			else
				$rEncModule = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');

			if ($type == "E")
			{
				$randomSource = preg_match("/^WIN/i", PHP_OS)? MCRYPT_RAND: MCRYPT_DEV_RANDOM;
				$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($rEncModule), $randomSource);
			}
			else
			{
				list($iv, $data) = explode(" ", $data);
				$iv = urldecode($iv);
				$data = urldecode($data);
			}

			$keySize = mcrypt_enc_get_key_size($rEncModule);
			$keyString = mb_substr(md5($pwdString), 0, $keySize);

			mcrypt_generic_init($rEncModule, $keyString, $iv);

			if ($type == "E")
				$res_data = mcrypt_generic($rEncModule, $data);
			else
				$res_data = mdecrypt_generic($rEncModule, $data);

			mcrypt_generic_deinit($rEncModule);

			mcrypt_module_close($rEncModule);

			if ($type == "E")
				$res_data = urlencode($iv)." ".urlencode($res_data);
		}
		else
		{
			if ($type == 'D')
				$data = urldecode($data);

			$key[] = "";
			$box[] = "";
			$temp_swap = "";
			$pwdLength = mb_strlen($pwdString);

			for ($i = 0; $i <= 255; $i++)
			{
				$key[$i] = ord(mb_substr($pwdString, ($i % $pwdLength), 1));
				$box[$i] = $i;
			}
			$x = 0;

			for ($i = 0; $i <= 255; $i++)
			{
				$x = ($x + $box[$i] + $key[$i]) % 256;
				$temp_swap = $box[$i];
				$box[$i] = $box[$x];
				$box[$x] = $temp_swap;
			}
			$temp = "";
			$k = "";
			$cipherby = "";
			$cipher = "";
			$a = 0;
			$j = 0;
			$countData = mb_strlen($data);
			for ($i = 0; $i < $countData; $i++)
			{
				$a = ($a + 1) % 256;
				$j = ($j + $box[$a]) % 256;
				$temp = $box[$a];
				$box[$a] = $box[$j];
				$box[$j] = $temp;
				$k = $box[(($box[$a] + $box[$j]) % 256)];
				$cipherby = ord(mb_substr($data, $i, 1)) ^ $k;
				$cipher .= chr($cipherby);
			}

			if ($type == 'D')
				$res_data = urldecode(urlencode($cipher));
			else
				$res_data = urlencode($cipher);
		}

		return $res_data;
	}

	public static function IdentifyCardType($ccNum)
	{
		//*CARD TYPES            *PREFIX           *WIDTH
		$ccNum = preg_replace('/[^0-9]+/', '', $ccNum);
		//Visa                   4                 13, 16
		if (preg_match('/^4(.{12}|.{15})$/', $ccNum))
			return 'VISA';
		//Master Card            51 to 55          16
		elseif (preg_match('/^5[1-5].{14}$/', $ccNum))
			return 'MASTERCARD';
		//American Express       34, 37            15
		elseif (preg_match('/^3[47].{13}$/', $ccNum))
			return 'AMEX';
		//Diners Club            300 to 305, 36    14
		//Carte Blanche          38                14
		elseif (preg_match('/^3(0[0-5].{11}|[68].{12})$/', $ccNum))
			return 'DINERS';
		//Discover               6011              16
		elseif (preg_match('/^6011.{12}$/', $ccNum))
			return 'DISCOVER';
		//JCB                    3                 16
		//JCB                    2131, 1800        15
		elseif (preg_match('/^3.{15}|(2131|1800).{11}$/', $ccNum))
			return 'JCB';
		//EnRoute                2014, 2149        15
		elseif (preg_match('/^2(014|149).{11}$/', $ccNum))
			return 'ENROUTE';
		else
			return "N";
	}

	public static function WithdrawByID($sum, $currency, $ID, $orderID = 0)
	{
		$sum = DoubleVal($sum);
		if ($sum <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGUC_EMPTY_SUM"), "EMPTY_SUM");
			return false;
		}

		$currency = Trim($currency);
		if ($currency == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGUC_EMPTY_CURRENCY"), "EMPTY_SUM_CURRENCY");
			return false;
		}

		$ID = intval($ID);
		if ($ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGUC_EMPTY_ID"), "EMPTY_ID");
			return false;
		}

		$orderID = intval($orderID);

		$arUserCard = CSaleUserCards::GetByID($ID);
		if (!$arUserCard)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $ID, GetMessage("SKGUC_NO_RECID")), "NO_RECORD");
			return false;
		}

		return CSaleUserCards::Withdraw($sum, $currency, $arUserCard, $orderID);
	}

	public static function Withdraw($sum, $currency, $arUserCard, $orderID = 0)
	{
		$sum = str_replace(",", ".", $sum);
		$sum = roundEx(DoubleVal($sum), SALE_VALUE_PRECISION);
		if ($sum <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGUC_EMPTY_SUM"), "EMPTY_SUM");
			return false;
		}

		$currency = Trim($currency);
		if ($currency == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGUC_EMPTY_CURRENCY"), "EMPTY_SUM_CURRENCY");
			return false;
		}

		if (!is_array($arUserCard) || count($arUserCard) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGUC_NO_PARAMS"), "EMPTY_CARD_ARRAY");
			return false;
		}

		$orderID = intval($orderID);

		if (DoubleVal($arUserCard["SUM_MAX"]) > 0)
		{
			$maxSum = roundEx(CCurrencyRates::ConvertCurrency($arUserCard["SUM_MAX"], $arUserCard["SUM_CURRENCY"], $currency), SALE_VALUE_PRECISION);
			if ($maxSum < $sum)
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#SUM1#", SaleFormatCurrency($arUserCard["SUM_MAX"], $arUserCard["SUM_CURRENCY"]), str_replace("#SUM2#", SaleFormatCurrency($sum, $currency), GetMessage("SKGUC_CROSS_BOUND"))), "MAX_SUM_LIMIT");
				return false;
			}
		}

		$arPSAction = CSalePaySystemAction::GetByID($arUserCard["PAY_SYSTEM_ACTION_ID"]);
		if (!$arPSAction)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arUserCard["PAY_SYSTEM_ACTION_ID"], GetMessage("SKGUC_NO_ACTION")), "NO_PAY_SYSTEM_ACTION");
			return false;
		}

		$psActionPath = $_SERVER["DOCUMENT_ROOT"].$arPSAction["ACTION_FILE"];
		if (!file_exists($psActionPath))
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#FILE#", $arPSAction["ACTION_FILE"], GetMessage("SKGUC_NO_PATH")), "NO_PS_PATH");
			return false;
		}

		if (is_file($psActionPath))
			$psActionPath = dirname($psActionPath);

		if (!file_exists($psActionPath."/action.php"))
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#FILE#", $psActionPath."/action.php", GetMessage("SKGUC_NO_SCRIPT")), "NO_PS_SCRIPT");
			return false;
		}

		$INPUT_CARD_TYPE = $arUserCard["CARD_TYPE"];
		$INPUT_CARD_NUM = CSaleUserCards::CryptData($arUserCard["CARD_NUM"], "D");
		$INPUT_CARD_EXP_MONTH = $arUserCard["CARD_EXP_MONTH"];
		$INPUT_CARD_EXP_YEAR = $arUserCard["CARD_EXP_YEAR"];
		$INPUT_CARD_CODE = $arUserCard["CARD_CODE"];
		$INPUT_SUM = $sum;
		if (DoubleVal($arUserCard["SUM_MIN"]) > 0)
		{
			$minSum = roundEx(CCurrencyRates::ConvertCurrency($arUserCard["SUM_MIN"], $arUserCard["SUM_CURRENCY"], $currency), SALE_VALUE_PRECISION);
			if ($minSum > $sum)
				$INPUT_SUM = $minSum;
		}
		$INPUT_CURRENCY = $currency;

		$GLOBALS["SALE_INPUT_PARAMS"] = array();

		$dbUser = CUser::GetByID(intval($arUserCard["USER_ID"]));
		if ($arUser = $dbUser->Fetch())
			$GLOBALS["SALE_INPUT_PARAMS"]["USER"] = $arUser;

		if ($orderID > 0)
		{
			if ($arOrder = CSaleOrder::GetByID($orderID))
			{
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"] = $arOrder;
				$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"] = DoubleVal($arOrder["PRICE"]) - DoubleVal($arOrder["SUM_PAID"]);

				$arDateInsert = explode(" ", $arOrder["DATE_INSERT"]);
				if (is_array($arDateInsert) && count($arDateInsert) > 0)
					$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT_DATE"] = $arDateInsert[0];
				else
					$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DATE_INSERT_DATE"] = $arOrder["DATE_INSERT"];
			}

			$arCurOrderProps = array();
			$dbOrderPropVals = CSaleOrderPropsValue::GetList(
					array(),
					array("ORDER_ID" => $ORDER_ID),
					false,
					false,
					array("ID", "CODE", "VALUE", "ORDER_PROPS_ID", "PROP_TYPE")
				);
			while ($arOrderPropVals = $dbOrderPropVals->Fetch())
			{
				$arCurOrderPropsTmp = CSaleOrderProps::GetRealValue(
						$arOrderPropVals["ORDER_PROPS_ID"],
						$arOrderPropVals["CODE"],
						$arOrderPropVals["PROP_TYPE"],
						$arOrderPropVals["VALUE"],
						LANGUAGE_ID
					);
				foreach ($arCurOrderPropsTmp as $key => $value)
				{
					$arCurOrderProps[$key] = $value;
				}
			}

			if (count($arCurOrderProps) > 0)
				$GLOBALS["SALE_INPUT_PARAMS"]["PROPERTY"] = $arCurOrderProps;
		}

		$GLOBALS["SALE_CORRESPONDENCE"] = CSalePaySystemAction::UnSerializeParams($arPSAction["PARAMS"]);

		include($psActionPath."/action.php");

		$INPUT_CARD_NUM = "";
		if ($OUTPUT_ERROR_MESSAGE <> '')
		{
			$GLOBALS["APPLICATION"]->ThrowException($OUTPUT_ERROR_MESSAGE, "ERROR_MESSAGE");
			return false;
		}

		$arFields = array(
				"LAST_STATUS" => $OUTPUT_STATUS,
				"LAST_STATUS_CODE" => $OUTPUT_STATUS_CODE,
				"LAST_STATUS_DESCRIPTION" => $OUTPUT_STATUS_DESCRIPTION,
				"LAST_STATUS_MESSAGE" => $OUTPUT_STATUS_MESSAGE,
				"LAST_SUM" => $OUTPUT_SUM,
				"LAST_CURRENCY" => $OUTPUT_CURRENCY,
				"LAST_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)))
			);
		CSaleUserCards::Update($arUserCard["ID"], $arFields);

		if ($OUTPUT_STATUS == "Y")
		{
			$OUTPUT_SUM = str_replace(",", ".", $OUTPUT_SUM);
			$OUTPUT_SUM = DoubleVal($OUTPUT_SUM);

			if ($OUTPUT_CURRENCY != $currency)
				$OUTPUT_SUM = roundEx(CCurrencyRates::ConvertCurrency($OUTPUT_SUM, $OUTPUT_CURRENCY, $currency), SALE_VALUE_PRECISION);

			return $OUTPUT_SUM;
		}

		$GLOBALS["APPLICATION"]->ThrowException($OUTPUT_STATUS_DESCRIPTION, "ERROR_PAY");
		return False;
	}
}
