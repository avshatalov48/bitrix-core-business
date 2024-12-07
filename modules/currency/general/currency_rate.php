<?php

use Bitrix\Currency;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ORM;
use Bitrix\Main\Type\DateTime;

IncludeModuleLangFile(__FILE__);

class CAllCurrencyRates
{
	protected static array $currentCache = [];

	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $APPLICATION, $DB;
		global $USER;

		$arMsg = [];

		if ('UPDATE' !== $ACTION && 'ADD' !== $ACTION)
		{
			return false;
		}
		if (!is_array($arFields))
		{
			return false;
		}
		if (array_key_exists('ID', $arFields))
		{
			unset($arFields['ID']);
		}

		if ('UPDATE' == $ACTION && 0 >= intval($ID))
			$arMsg[] = array('id' => 'ID','text' => GetMessage('BT_MOD_CURR_ERR_RATE_ID_BAD'));

		if (!isset($arFields["CURRENCY"]))
			$arMsg[] = array('id' => 'CURRENCY','text' => GetMessage('BT_MOD_CURR_ERR_RATE_CURRENCY_ABSENT'));
		else
			$arFields["CURRENCY"] = mb_substr($arFields["CURRENCY"], 0, 3);

		if (empty($arFields['DATE_RATE']))
			$arMsg[] = array('id' => 'DATE_RATE','text' => GetMessage('BT_MOD_CURR_ERR_RATE_DATE_ABSENT'));
		elseif (!$DB->IsDate($arFields['DATE_RATE']))
			$arMsg[] = array('id' => 'DATE_RATE','text' => GetMessage('BT_MOD_CURR_ERR_RATE_DATE_FORMAT_BAD'));

		if (is_set($arFields, 'RATE_CNT') || 'ADD' == $ACTION)
		{
			if (!isset($arFields['RATE_CNT']))
			{
				$arMsg[] = array('id' => 'RATE_CNT', 'text' => GetMessage('BT_MOD_CURR_ERR_RATE_RATE_CNT_ABSENT'));
			}
			else
			{
				$arFields['RATE_CNT'] = (int)$arFields['RATE_CNT'];
				if ($arFields['RATE_CNT'] <= 0)
					$arMsg[] = array('id' => 'RATE_CNT', 'text' => GetMessage('BT_MOD_CURR_ERR_RATE_RATE_CNT_BAD'));
			}
		}
		if (is_set($arFields['RATE']) || 'ADD' == $ACTION)
		{
			if (!isset($arFields['RATE']))
			{
				$arMsg[] = array('id' => 'RATE','text' => GetMessage('BT_MOD_CURR_ERR_RATE_RATE_ABSENT'));
			}
			else
			{
				$arFields['RATE'] = (float)$arFields['RATE'];
				if (!($arFields['RATE'] > 0))
				{
					$arMsg[] = array('id' => 'RATE','text' => GetMessage('BT_MOD_CURR_ERR_RATE_RATE_BAD'));
				}
			}
		}
		if ($ACTION == 'ADD')
		{
			if ($arFields['CURRENCY'] == Currency\CurrencyManager::getBaseCurrency())
			{
				$arMsg[] = array('id' => 'CURRENCY', 'text' => GetMessage('BT_MOD_CURR_ERR_RATE_FOR_BASE_CURRENCY'));
			} else
			{
				if (!isset($arFields['BASE_CURRENCY']) || !Currency\CurrencyManager::checkCurrencyID($arFields['BASE_CURRENCY']))
					$arFields['BASE_CURRENCY'] = Currency\CurrencyManager::getBaseCurrency();
			}
			if ($arFields['CURRENCY'] == $arFields['BASE_CURRENCY'])
			{
				$arMsg[] = array('id' => 'CURRENCY', 'text' => GetMessage('BT_MOD_CURR_ERR_RATE_FOR_SELF_CURRENCY'));
			}
		}

		$userId = 0;
		if (isset($USER) && $USER instanceof CUser)
		{
			$userId = (int)$USER->GetID();
		}

		$arFields['~TIMESTAMP_X'] = $DB->GetNowFunction();
		$arFields['MODIFIED_BY'] = (int)($arFields['MODIFIED_BY'] ?? $userId);
		if ($arFields['MODIFIED_BY'] < 0)
		{
			$arFields['MODIFIED_BY'] = $userId;
		}
		if ($ACTION === 'ADD')
		{
			$arFields['~DATE_CREATE'] = $arFields['~TIMESTAMP_X'];
				$arFields['CREATED_BY'] = (int)($arFields['CREATED_BY'] ?? $userId);
				if ($arFields['CREATED_BY'] < 0)
				{
					$arFields['CREATED_BY'] = $userId;
				}
		}

		if (!empty($arMsg))
		{
			$obError = new CAdminException($arMsg);
			$APPLICATION->ResetException();
			$APPLICATION->ThrowException($obError);

			return false;
		}

		return true;
	}

	public static function Add($arFields)
	{
		global $DB;
		global $APPLICATION;
		/** @global CStackCacheManager $stackCacheManager */
		global $stackCacheManager;

		$arMsg = [];

		foreach (GetModuleEvents("currency", "OnBeforeCurrencyRateAdd", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields))===false)
				return false;
		}

		if (!CCurrencyRates::CheckFields('ADD', $arFields))
		{
			return false;
		}

		CTimeZone::Disable();
		$existRate = Currency\CurrencyRateTable::getRow([
			'select' => ['ID'],
			'filter' => [
				'=CURRENCY' => $arFields['CURRENCY'],
				'=BASE_CURRENCY' => $arFields['BASE_CURRENCY'],
				'=DATE_RATE' => DateTime::createFromUserTime($arFields['DATE_RATE']),
			],
		]);
		CTimeZone::Enable();
		if ($existRate)
		{
			$arMsg[] = array("id"=>"DATE_RATE", "text"=> GetMessage("ERROR_ADD_REC2"));
			$e = new CAdminException($arMsg);
			$APPLICATION->ThrowException($e);

			return false;
		}

		$stackCacheManager->Clear('currency_rate');

		$ID = $DB->Add('b_catalog_currency_rate', $arFields);

		Currency\CurrencyManager::updateBaseRates($arFields['CURRENCY']);
		Currency\CurrencyManager::clearTagCache($arFields['CURRENCY']);
		Currency\CurrencyRateTable::cleanCache();
		self::$currentCache = [];

		foreach (GetModuleEvents('currency', 'OnCurrencyRateAdd', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$ID, $arFields]);
		}

		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;
		global $APPLICATION;
		/** @global CStackCacheManager $stackCacheManager */
		global $stackCacheManager;

		$ID = (int)$ID;
		if ($ID <= 0)
		{
			return false;
		}
		$arMsg = [];

		foreach (GetModuleEvents("currency", "OnBeforeCurrencyRateUpdate", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, [$ID, &$arFields]) === false)
			{
				return false;
			}
		}

		if (!CCurrencyRates::CheckFields('UPDATE', $arFields, $ID))
		{
			return false;
		}

		CTimeZone::Disable();
		$existRate = Currency\CurrencyRateTable::getRow([
			'select' => ['ID'],
			'filter' => [
				'=CURRENCY' => $arFields['CURRENCY'],
				'=BASE_CURRENCY' => $arFields['BASE_CURRENCY'],
				'=DATE_RATE' => DateTime::createFromUserTime($arFields['DATE_RATE']),
				'!=ID' => $ID,
			],
		]);
		CTimeZone::Enable();

		if ($existRate)
		{
			$arMsg[] = [
				'id' => 'DATE_RATE',
				'text' => GetMessage('ERROR_ADD_REC2'),
			];
			$e = new CAdminException($arMsg);
			$APPLICATION->ThrowException($e);

			return false;
		}

		$strUpdate = $DB->PrepareUpdate('b_catalog_currency_rate', $arFields);
		if (!empty($strUpdate))
		{
			$strSql = "UPDATE b_catalog_currency_rate SET ".$strUpdate." WHERE ID = ".$ID;
			$DB->Query($strSql);

			$stackCacheManager->Clear('currency_rate');
			Currency\CurrencyManager::updateBaseRates($arFields['CURRENCY']);
			Currency\CurrencyManager::clearTagCache($arFields['CURRENCY']);
			Currency\CurrencyRateTable::cleanCache();
			self::$currentCache = [];
		}
		foreach (GetModuleEvents('currency', 'OnCurrencyRateUpdate', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$ID, $arFields]);
		}

		return true;
	}

	public static function Delete($ID)
	{
		global $APPLICATION;
		/** @global CStackCacheManager $stackCacheManager */
		global $stackCacheManager;

		$ID = (int)$ID;
		if ($ID <= 0)
		{
			return false;
		}

		foreach (GetModuleEvents('currency', 'OnBeforeCurrencyRateDelete', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, [$ID]) === false)
			{
				return false;
			}
		}

		$arFields = Currency\CurrencyRateTable::getRow([
			'select' => [
				'ID',
				'CURRENCY',
			],
			'filter' => ['=ID' => $ID],
		]);
		if (!is_array($arFields))
		{
			$arMsg = array('id' => 'ID', 'text' => GetMessage('BT_MOD_CURR_ERR_RATE_CANT_DELETE_ABSENT_ID'));
			$e = new CAdminException($arMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}

		$result = Currency\CurrencyRateTable::delete($ID);
		if (!$result->isSuccess())
		{
			self::convertErrors($result);

			return false;
		}

		$stackCacheManager->Clear('currency_rate');
		Currency\CurrencyManager::updateBaseRates($arFields['CURRENCY']);
		Currency\CurrencyManager::clearTagCache($arFields['CURRENCY']);
		Currency\CurrencyRateTable::cleanCache();
		self::$currentCache = [];

		foreach(GetModuleEvents('currency', 'OnCurrencyRateDelete', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$ID]);
		}

		return true;
	}

	public static function GetByID($ID)
	{
		global $DB;

		$ID = (int)$ID;
		if ($ID <= 0)
			return false;
		$strSql = "SELECT C.*, ".$DB->DateToCharFunction("C.DATE_RATE", "SHORT")." as DATE_RATE FROM b_catalog_currency_rate C WHERE ID = ".$ID;
		$db_res = $DB->Query($strSql);

		if ($res = $db_res->Fetch())
			return $res;

		return false;
	}

	public static function GetList($by = 'date', $order = 'asc', $arFilter = [])
	{
		global $DB;

		$mysqlEdition = $DB->type === 'MYSQL';
		$arSqlSearch = array();

		if(!is_array($arFilter))
			$filter_keys = array();
		else
			$filter_keys = array_keys($arFilter);

		for ($i=0, $intCount = count($filter_keys); $i < $intCount; $i++)
		{
			$val = (string)$DB->ForSql($arFilter[$filter_keys[$i]]);
			if ($val === '')
				continue;

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			switch(strtoupper($key))
			{
				case "CURRENCY":
					$arSqlSearch[] = "C.CURRENCY = '".$val."'";
					break;
				case "DATE_RATE":
					$arSqlSearch[] = "(C.DATE_RATE ".($bInvert? "<" : ">=")." ".($mysqlEdition? "CAST(" : "").$DB->CharToDateFunction($DB->ForSql($val), "SHORT").($mysqlEdition? " AS DATE)" : "").($bInvert? "" : " OR C.DATE_RATE IS NULL").")";
					break;
			}
		}

		$strSqlSearch = "";
		for($i=0, $intCount = count($arSqlSearch); $i < $intCount; $i++)
		{
			if($i>0)
				$strSqlSearch .= " AND ";
			else
				$strSqlSearch = " WHERE ";

			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql = "SELECT C.ID, C.CURRENCY, C.RATE_CNT, C.RATE, ".$DB->DateToCharFunction("C.DATE_RATE", "SHORT")." as DATE_RATE FROM b_catalog_currency_rate C ".
			$strSqlSearch;

		if (strtolower($by) == "curr") $strSqlOrder = " ORDER BY C.CURRENCY ";
		elseif (strtolower($by) == "rate") $strSqlOrder = " ORDER BY C.RATE ";
		else
		{
			$strSqlOrder = " ORDER BY C.DATE_RATE ";
		}

		if (strtolower($order) == "desc")
			$strSqlOrder .= " desc ";

		$strSql .= $strSqlOrder;
		$res = $DB->Query($strSql);

		return $res;
	}

	public static function ConvertCurrency($valSum, $curFrom, $curTo, $valDate = "")
	{
		return (float)$valSum * static::GetConvertFactorEx($curFrom, $curTo, $valDate);
	}

	/**
	 * @deprecated deprecated since currency 16.0.0
	 * @see CCurrencyRates::GetConvertFactorEx
	 *
	 * @param float|int $curFrom
	 * @param float|int $curTo
	 * @param string $valDate
	 * @return float|int
	 */
	public static function GetConvertFactor($curFrom, $curTo, $valDate = "")
	{
		return static::GetConvertFactorEx($curFrom, $curTo, $valDate);
	}

	/**
	 * @param float|int $curFrom
	 * @param float|int $curTo
	 * @param string $valDate
	 * @return float|int
	 */
	public static function GetConvertFactorEx($curFrom, $curTo, $valDate = "")
	{
		/** @global CStackCacheManager $stackCacheManager */
		global $stackCacheManager;

		$curFrom = (string)$curFrom;
		$curTo = (string)$curTo;
		if($curFrom === '' || $curTo === '')
			return 0;
		if ($curFrom == $curTo)
			return 1;

		$valDate = (string)$valDate;
		if ($valDate === '')
			$valDate = date("Y-m-d");
		list($dpYear, $dpMonth, $dpDay) = explode("-", $valDate, 3);
		$dpDay += 1;
		if($dpYear < 2038 && $dpYear > 1970)
			$valDate = date("Y-m-d", mktime(0, 0, 0, $dpMonth, $dpDay, $dpYear));
		else
			$valDate = date("Y-m-d");

		$curFromRate = 0;
		$curFromRateCnt = 0;
		$curToRate = 1;
		$curToRateCnt = 1;

		if (defined("CURRENCY_SKIP_CACHE") && CURRENCY_SKIP_CACHE)
		{
			if ($res = static::_get_last_rates($valDate, $curFrom))
			{
				$curFromRate = (float)$res["RATE"];
				$curFromRateCnt = (int)$res["RATE_CNT"];
				if ($curFromRate <= 0)
				{
					$curFromRate = (float)$res["AMOUNT"];
					$curFromRateCnt = (int)$res["AMOUNT_CNT"];
				}
			}

			if ($res = static::_get_last_rates($valDate, $curTo))
			{
				$curToRate = (float)$res["RATE"];
				$curToRateCnt = (int)$res["RATE_CNT"];
				if ($curToRate <= 0)
				{
					$curToRate = (float)$res["AMOUNT"];
					$curToRateCnt = (int)$res["AMOUNT_CNT"];
				}
			}
		}
		else
		{
			$cacheTime = CURRENCY_CACHE_DEFAULT_TIME;
			if (defined("CURRENCY_CACHE_TIME"))
				$cacheTime = (int)CURRENCY_CACHE_TIME;

			$cacheKey = 'C_R_'.$valDate.'_'.$curFrom.'_'.$curTo;

			$stackCacheManager->SetLength("currency_rate", 10);
			$stackCacheManager->SetTTL("currency_rate", $cacheTime);
			if ($stackCacheManager->Exist("currency_rate", $cacheKey))
			{
				$arResult = $stackCacheManager->Get("currency_rate", $cacheKey);
			}
			else
			{
				if (!isset(self::$currentCache[$cacheKey]))
				{
					if ($res = static::_get_last_rates($valDate, $curFrom))
					{
						$curFromRate = (float)$res["RATE"];
						$curFromRateCnt = (int)$res["RATE_CNT"];
						if ($curFromRate <= 0)
						{
							$curFromRate = (float)$res["AMOUNT"];
							$curFromRateCnt = (int)$res["AMOUNT_CNT"];
						}
					}

					if ($res = static::_get_last_rates($valDate, $curTo))
					{
						$curToRate = (float)$res["RATE"];
						$curToRateCnt = (int)$res["RATE_CNT"];
						if ($curToRate <= 0)
						{
							$curToRate = (float)$res["AMOUNT"];
							$curToRateCnt = (int)$res["AMOUNT_CNT"];
						}
					}

					self::$currentCache[$cacheKey] = array(
						"curFromRate" => $curFromRate,
						"curFromRateCnt" => $curFromRateCnt,
						"curToRate" => $curToRate,
						"curToRateCnt" => $curToRateCnt
					);

					$stackCacheManager->Set("currency_rate", $cacheKey, self::$currentCache[$cacheKey]);
				}
				$arResult = self::$currentCache[$cacheKey];
			}
			$curFromRate = $arResult["curFromRate"];
			$curFromRateCnt = $arResult["curFromRateCnt"];
			$curToRate = $arResult["curToRate"];
			$curToRateCnt = $arResult["curToRateCnt"];
		}

		if ($curFromRate == 0 || $curToRateCnt == 0 || $curToRate == 0 || $curFromRateCnt == 0)
			return 0;

		return $curFromRate*$curToRateCnt/$curToRate/$curFromRateCnt;
	}

	public static function _get_last_rates($valDate, $cur)
	{
		$baseCurrency = Currency\CurrencyManager::getBaseCurrency();
		if ($baseCurrency === null)
		{
			return null;
		}
		$valDate = trim((string)$valDate);
		$cur = trim((string)$cur);

		if ($valDate === '' || !Currency\CurrencyManager::isCurrencyExist($cur))
		{
			return null;
		}

		$result = Currency\CurrencyTable::getRow([
			'select' => [
				'AMOUNT',
				'AMOUNT_CNT',
				'RATE' => 'RATE_TB.RATE',
				'RATE_CNT' => 'RATE_TB.RATE_CNT',
				'DATE_RATE' => 'RATE_TB.DATE_RATE',
			],
			'filter' => [
				'=CURRENCY' => $cur,
			],
			'order' => [
				'DATE_RATE' => 'DESC',
			],
			'runtime' => [
				'RATE_TB' => new ORM\Fields\Relations\Reference(
					'RATE_TB',
					Currency\CurrencyRateTable::class,
					[
						'=this.CURRENCY' => 'ref.CURRENCY',
						'=ref.BASE_CURRENCY' => new SqlExpression('?s', $baseCurrency),
						'<ref.DATE_RATE' => new SqlExpression('?s', $valDate),
					],
					['join' => ORM\Query\Join::TYPE_LEFT]
				),
			]
		]);

		if ($result !== null)
		{
			unset($result['DATE_RATE']);
		}

		return $result;
	}

	private static function convertErrors(ORM\Data\Result $result): void
	{
		global $APPLICATION;

		$oldMessages = [];
		foreach ($result->getErrorMessages() as $errorText)
		{
			$oldMessages[] = ['text' => $errorText];
		}
		unset($errorText);

		if (!empty($oldMessages))
		{
			$error = new CAdminException($oldMessages);
			$APPLICATION->ThrowException($error);
			unset($error);
		}
		unset($oldMessages);
	}
}

class CCurrencyRates extends CAllCurrencyRates
{

}
