<?
use Bitrix\Main,
	Bitrix\Main\ModuleManager,
	Bitrix\Main\Localization\Loc,
	Bitrix\Currency;

Loc::loadMessages(__FILE__);

class CAllCurrency
{
	protected static $currencyCache = array();

/*
* @deprecated deprecated since currency 9.0.0
* @see CCurrency::GetByID()
*/
	public static function GetCurrency($currency)
	{
		return CCurrency::GetByID($currency);
	}

	public function CheckFields($ACTION, &$arFields, $strCurrencyID = false)
	{
		global $APPLICATION, $DB, $USER;

		$arMsg = array();

		$ACTION = mb_strtoupper($ACTION);
		if ($ACTION != 'UPDATE' && $ACTION != 'ADD')
			return false;
		if (!is_array($arFields))
			return false;

		$defaultValues = array(
			'SORT' => 100,
			'BASE' => 'N'
		);

		$clearFields = array(
			'~CURRENCY',
			'~NUMCODE',
			'~AMOUNT_CNT',
			'~AMOUNT',
			'~BASE',
			'DATE_UPDATE',
			'DATE_CREATE',
			'~DATE_CREATE',
			'~MODIFIED_BY',
			'~CREATED_BY',
			'CURRENT_BASE_RATE',
			'~CURRENT_BASE_RATE'
		);
		if ($ACTION == 'UPDATE')
		{
			$clearFields[] = 'CREATED_BY';
			$clearFields[] = '~CURRENCY';
		}
		$arFields = array_filter($arFields, 'CCurrency::clearFields');
		foreach ($clearFields as &$fieldName)
		{
			if (array_key_exists($fieldName, $arFields))
				unset($arFields[$fieldName]);
		}
		unset($fieldName, $clearFields);

		if ($ACTION == 'ADD')
		{
			if (!isset($arFields['CURRENCY']))
			{
				$arMsg[] = array('id' => 'CURRENCY', 'text' => Loc::getMessage('BT_MOD_CURR_ERR_CURR_CUR_ID_ABSENT'));
			}
			elseif (!preg_match("~^[a-z]{3}$~i", $arFields['CURRENCY']))
			{
				$arMsg[] = array('id' => 'CURRENCY','text' => Loc::getMessage('BT_MOD_CURR_ERR_CURR_CUR_ID_LAT_EXT'));
			}
			else
			{
				$arFields['CURRENCY'] = mb_strtoupper($arFields['CURRENCY']);
				$currencyExist = Currency\CurrencyTable::getList(array(
					'select' => array('CURRENCY'),
					'filter' => array('=CURRENCY' => $arFields['CURRENCY'])
				))->fetch();
				if (!empty($currencyExist))
					$arMsg[] = array('id' => 'CURRENCY','text' => Loc::getMessage('BT_MOD_CURR_ERR_CURR_CUR_ID_EXISTS'));
				unset($currencyExist);
			}
			$arFields = array_merge($defaultValues, $arFields);
			if (!isset($arFields['AMOUNT_CNT']))
				$arMsg[] = array('id' => 'AMOUNT_CNT','text' => Loc::getMessage('BT_MOD_CURR_ERR_CURR_AMOUNT_CNT_ABSENT'));

			if (!isset($arFields['AMOUNT']))
				$arMsg[] = array('id' => 'AMOUNT','text' => Loc::getMessage('BT_MOD_CURR_ERR_CURR_AMOUNT_ABSENT'));
		}

		if ($ACTION == 'UPDATE')
		{
			$strCurrencyID = Currency\CurrencyManager::checkCurrencyID($strCurrencyID);
			if ($strCurrencyID === false)
				$arMsg[] = array('id' => 'CURRENCY','text' => Loc::getMessage('BT_MOD_CURR_ERR_CURR_CUR_ID_BAD'));
			$iterator = Currency\CurrencyTable::getList(array(
				'select' => array('*'),
				'filter' => array('=CURRENCY' => $strCurrencyID)
			));
			$row = $iterator->fetch();
			unset($iterator);
			if (!empty($row) && $row['BASE'] == 'Y')
			{
				if (array_key_exists('AMOUNT_CNT', $arFields))
				{
					$arFields['AMOUNT_CNT'] = (int)$arFields['AMOUNT_CNT'];
					if ($arFields['AMOUNT_CNT'] != 1)
						$arMsg[] = array('id' => 'AMOUNT_CNT','text' => Loc::getMessage('BX_CURRENCY_ERR_CURR_BASE_AMOUNT_CNT_BAD'));
				}
				if (array_key_exists('AMOUNT', $arFields))
				{
					$arFields['AMOUNT'] = (float)$arFields['AMOUNT'];
					if ($arFields['AMOUNT'] != 1)
						$arMsg[] = array('id' => 'AMOUNT','text' => Loc::getMessage('BX_CURRENCY_ERR_CURR_BASE_AMOUNT_BAD'));
				}
			}
			unset($row);
		}

		if (empty($arMsg))
		{
			if (isset($arFields['AMOUNT_CNT']))
			{
				$arFields['AMOUNT_CNT'] = (int)$arFields['AMOUNT_CNT'];
				if ($arFields['AMOUNT_CNT'] <= 0)
					$arMsg[] = array('id' => 'AMOUNT_CNT','text' => Loc::getMessage('BT_MOD_CURR_ERR_CURR_AMOUNT_CNT_BAD'));
			}
			if (isset($arFields['AMOUNT']))
			{
				$arFields['AMOUNT'] = (float)$arFields['AMOUNT'];
				if ($arFields['AMOUNT'] <= 0)
					$arMsg[] = array('id' => 'AMOUNT','text' => Loc::getMessage('BT_MOD_CURR_ERR_CURR_AMOUNT_BAD'));
			}
			if (isset($arFields['SORT']))
			{
				$arFields['SORT'] = (int)$arFields['SORT'];
				if ($arFields['SORT'] <= 0)
					$arFields['SORT'] = 100;
			}
			if (isset($arFields['BASE']))
				$arFields['BASE'] = ((string)$arFields['BASE'] === 'Y' ? 'Y' : 'N');

			if (isset($arFields['NUMCODE']))
			{
				$arFields['NUMCODE'] = (string)$arFields['NUMCODE'];
				if ($arFields['NUMCODE'] === '')
					unset($arFields['NUMCODE']);
				elseif (!preg_match("~^[0-9]{3}$~", $arFields['NUMCODE']))
					$arMsg[] = array('id' => 'NUMCODE','text' => Loc::getMessage('BT_MOD_CURR_ERR_CURR_NUMCODE_IS_BAD'));
			}
		}

		$boolUserExist = self::isUserExists();
		$intUserID = ($boolUserExist ? (int)$USER->GetID() : 0);
		$strDateFunction = $DB->GetNowFunction();
		$arFields['~DATE_UPDATE'] = $strDateFunction;
		if ($boolUserExist)
		{
			if (!isset($arFields['MODIFIED_BY']))
				$arFields['MODIFIED_BY'] = $intUserID;
			$arFields['MODIFIED_BY'] = (int)$arFields['MODIFIED_BY'];
			if ($arFields['MODIFIED_BY'] <= 0)
				$arFields['MODIFIED_BY'] = $intUserID;
		}
		if ($ACTION == 'ADD')
		{
			$arFields['~DATE_CREATE'] = $strDateFunction;
			if ($boolUserExist)
			{
				if (!isset($arFields['CREATED_BY']))
					$arFields['CREATED_BY'] = $intUserID;
				$arFields['CREATED_BY'] = (int)$arFields['CREATED_BY'];
				if ($arFields['CREATED_BY'] <= 0)
					$arFields['CREATED_BY'] = $intUserID;
			}
		}

		if (isset($arFields['LANG']))
		{
			if (empty($arFields['LANG']) || !is_array($arFields['LANG']))
			{
				$arMsg[] = array('id' => 'LANG','text' => Loc::getMessage('BT_MOD_CURR_ERR_CURR_LANG_BAD'));
			}
			else
			{
				$langSettings = array();
				$currency = ($ACTION == 'ADD' ? $arFields['CURRENCY'] : $strCurrencyID);
				foreach ($arFields['LANG'] as $lang => $settings)
				{
					if (empty($settings) || !is_array($settings))
						continue;
					$langAction = 'ADD';
					if ($ACTION == 'UPDATE')
					{
						$langAction = (CCurrencyLang::isExistCurrencyLanguage($currency, $lang) ? 'UPDATE' : 'ADD');
					}
					$checkLang = CCurrencyLang::checkFields($langAction, $settings, $currency, $lang, true);
					$settings['CURRENCY'] = $currency;
					$settings['LID'] = $lang;
					$settings['IS_EXIST'] = ($langAction == 'ADD' ? 'N' : 'Y');
					$langSettings[$lang] = $settings;
					if (is_array($checkLang))
						$arMsg = array_merge($arMsg, $checkLang);
				}
				$arFields['LANG'] = $langSettings;
				unset($settings, $lang, $currency, $langSettings);
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

		foreach (GetModuleEvents("currency", "OnBeforeCurrencyAdd", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array(&$arFields))===false)
				return false;
		}

		if (!CCurrency::CheckFields('ADD', $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_catalog_currency", $arFields);

		$strSql = "insert into b_catalog_currency(".$arInsert[0].") values(".$arInsert[1].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if (isset($arFields['LANG']))
		{
			foreach ($arFields['LANG'] as $lang => $settings)
			{
				if ($settings['IS_EXIST'] == 'N')
					CCurrencyLang::Add($settings);
				else
					CCurrencyLang::Update($arFields['CURRENCY'], $lang, $settings);
			}
			unset($settings, $lang);
		}

		Currency\CurrencyManager::updateBaseRates($arFields['CURRENCY']);
		Currency\CurrencyManager::clearCurrencyCache();

		foreach (GetModuleEvents("currency", "OnCurrencyAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($arFields['CURRENCY'], $arFields));

		if (isset(self::$currencyCache[$arFields['CURRENCY']]))
			unset(self::$currencyCache[$arFields['CURRENCY']]);
		return $arFields["CURRENCY"];
	}

	public static function Update($currency, $arFields)
	{
		global $DB;

		foreach (GetModuleEvents("currency", "OnBeforeCurrencyUpdate", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($currency, &$arFields))===false)
				return false;
		}

		$currency = Currency\CurrencyManager::checkCurrencyID($currency);
		if (!CCurrency::CheckFields('UPDATE', $arFields, $currency))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_catalog_currency", $arFields);
		if (!empty($strUpdate))
		{
			$strSql = "update b_catalog_currency set ".$strUpdate." where CURRENCY = '".$DB->ForSql($currency)."'";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			Currency\CurrencyManager::updateBaseRates($currency);
			Currency\CurrencyManager::clearTagCache($currency);
			if (isset(self::$currencyCache[$currency]))
				unset(self::$currencyCache[$currency]);
		}
		if (isset($arFields['LANG']))
		{
			foreach ($arFields['LANG'] as $lang => $settings)
			{
				if ($settings['IS_EXIST'] == 'N')
					CCurrencyLang::Add($settings);
				else
					CCurrencyLang::Update($currency, $lang, $settings);
			}
			unset($settings, $lang);
		}
		if (!empty($strUpdate) || isset($arFields['LANG']))
			Currency\CurrencyManager::clearCurrencyCache();

		foreach (GetModuleEvents("currency", "OnCurrencyUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($currency, $arFields));

		return $currency;
	}

	public static function Delete($currency)
	{
		global $DB;

		$currency = Currency\CurrencyManager::checkCurrencyID($currency);
		if ($currency === false)
			return false;

		foreach(GetModuleEvents("currency", "OnBeforeCurrencyDelete", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array($currency))===false)
				return false;
		}

		$sqlCurrency = $DB->ForSQL($currency);

		$query = "select CURRENCY, BASE from b_catalog_currency where CURRENCY = '".$sqlCurrency."'";
		$currencyIterator = $DB->Query($query, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);
		if ($existCurrency = $currencyIterator->Fetch())
		{
			if ($existCurrency['BASE'] == 'Y')
				return false;
		}
		else
		{
			return false;
		}

		foreach(GetModuleEvents("currency", "OnCurrencyDelete", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($currency));
		}

		Currency\CurrencyManager::clearCurrencyCache();

		$DB->Query("delete from b_catalog_currency_lang where CURRENCY = '".$sqlCurrency."'", true);
		$DB->Query("delete from b_catalog_currency_rate where CURRENCY = '".$sqlCurrency."'", true);

		Currency\CurrencyManager::clearTagCache($currency);

		if (isset(self::$currencyCache[$currency]))
			unset(self::$currencyCache[$currency]);
		return $DB->Query("delete from b_catalog_currency where CURRENCY = '".$sqlCurrency."'", true);
	}

	public static function GetByID($currency)
	{
		$currency = Currency\CurrencyManager::checkCurrencyID($currency);
		if ($currency === false)
			return false;

		if (!isset(self::$currencyCache[$currency]))
		{
			self::$currencyCache[$currency] = false;
			$currencyIterator = Currency\CurrencyTable::getById($currency);
			if ($currencyData = $currencyIterator->fetch())
			{
				$currencyData['DATE_UPDATE_FORMAT'] = (
					$currencyData['DATE_UPDATE'] instanceof Main\Type\DateTime
					? $currencyData['DATE_UPDATE']->toString()
					: null
				);
				$currencyData['DATE_CREATE_FORMAT'] = (
					$currencyData['DATE_CREATE'] instanceof Main\Type\DateTime
					? $currencyData['DATE_CREATE']->toString()
					: null
				);
				unset($currencyData['DATE_UPDATE'], $currencyData['DATE_CREATE']);
				self::$currencyCache[$currency] = $currencyData;
			}
			unset($currencyData, $currencyIterator);
		}
		return self::$currencyCache[$currency];
	}

	/**
	 * @deprecated deprecated since currency 16.0.0
	 * @see \Bitrix\Currency\CurrencyManager::getBaseCurrency
	 *
	 * @return string
	 */
	public static function GetBaseCurrency()
	{
		return Currency\CurrencyManager::getBaseCurrency();
	}

	public static function SetBaseCurrency($currency)
	{
		$currency = Currency\CurrencyManager::checkCurrencyID($currency);
		if ($currency === false)
			return false;

		$existCurrency = Currency\CurrencyTable::getList(array(
			'select' => array('CURRENCY', 'BASE'),
			'filter' => array('=CURRENCY' => $currency)
		))->fetch();
		if (!empty($existCurrency))
		{
			if ($existCurrency['BASE'] == 'Y')
				return true;
			$result = Currency\CurrencyManager::updateBaseCurrency($currency);
			if ($result)
				Currency\CurrencyManager::clearCurrencyCache();
			return $result;
		}
		return false;
	}

	public static function SelectBox($sFieldName, $sValue, $sDefaultValue = '', $bFullName = true, $JavaFunc = '', $sAdditionalParams = '')
	{
		$s = '<select name="'.$sFieldName.'"';
		if ('' != $JavaFunc) $s .= ' onchange="'.$JavaFunc.'"';
		if ('' != $sAdditionalParams) $s .= ' '.$sAdditionalParams.' ';
		$s .= '>';
		$s1 = '';
		$found = false;

		$currencyList = Currency\CurrencyManager::getCurrencyList();
		if (!empty($currencyList) && is_array($currencyList))
		{
			foreach ($currencyList as $currency => $title)
			{
				$found = ($currency == $sValue);
				$s1 .= '<option value="'.$currency.'"'.($found ? ' selected' : '').'>'.($bFullName ? htmlspecialcharsbx($title) : $currency).'</option>';
			}
		}
		if ('' != $sDefaultValue)
			$s .= '<option value=""'.($found ? '' : ' selected').'>'.htmlspecialcharsbx($sDefaultValue).'</option>';
		return $s.$s1.'</select>';
	}

	/**
	 * @deprecated deprecated since currency 16.5.0
	 * @see \Bitrix\Currency\CurrencyTable::getList
	 *
	 * @param string &$by
	 * @param string &$order
	 * @param string $lang
	 * @return CDBResult
	 */
	public static function GetList(&$by, &$order, $lang = LANGUAGE_ID)
	{
		global $CACHE_MANAGER;

		if (defined("CURRENCY_SKIP_CACHE") && CURRENCY_SKIP_CACHE
			|| mb_strtolower($by) == "name"
			|| mb_strtolower($by) == "currency"
			|| mb_strtolower($order) == "desc")
		{
			/** @noinspection PhpDeprecationInspection */
			$dbCurrencyList = static::__GetList($by, $order, $lang);
		}
		else
		{
			$cacheTime = (int)CURRENCY_CACHE_DEFAULT_TIME;
			if (defined("CURRENCY_CACHE_TIME"))
				$cacheTime = (int)CURRENCY_CACHE_TIME;

			if ($CACHE_MANAGER->Read($cacheTime, "currency_currency_list_".$lang, 'b_catalog_currency'))
			{
				$arCurrencyList = $CACHE_MANAGER->Get("currency_currency_list_".$lang);
				$dbCurrencyList = new CDBResult();
				$dbCurrencyList->InitFromArray($arCurrencyList);
			}
			else
			{
				$arCurrencyList = array();
				/** @noinspection PhpDeprecationInspection */
				$dbCurrencyList = static::__GetList($by, $order, $lang);
				while ($arCurrency = $dbCurrencyList->Fetch())
					$arCurrencyList[] = $arCurrency;

				$CACHE_MANAGER->Set("currency_currency_list_".$lang, $arCurrencyList);

				$dbCurrencyList = new CDBResult();
				$dbCurrencyList->InitFromArray($arCurrencyList);
			}
		}

		return $dbCurrencyList;
	}

	/**
	 * @deprecated deprecated since currency 16.5.0
	 * @see \Bitrix\Currency\CurrencyTable::getList
	 *
	 * @param string &$by
	 * @param string &$order
	 * @param string $lang
	 * @return CDBResult
	 */
	public static function __GetList(&$by, &$order, $lang = LANGUAGE_ID)
	{
		$lang = mb_substr((string)$lang, 0, 2);
		$normalBy = mb_strtolower($by);
		if ($normalBy != 'currency' && $normalBy != 'name')
		{
			$normalBy = 'sort';
			$by = 'sort';
		}
		$normalOrder = mb_strtoupper($order);
		if ($normalOrder != 'DESC')
		{
			$normalOrder = 'ASC';
			$order = 'asc';
		}
		switch($normalBy)
		{
			case 'currency':
				$currencyOrder = array('CURRENCY' => $normalOrder);
				break;
			case 'name':
				$currencyOrder = array('FULL_NAME' => $normalOrder);
				break;
			case 'sort':
			default:
				$currencyOrder = array('SORT' => $normalOrder);
				break;
		}
		unset($normalOrder, $normalBy);

		/** @noinspection PhpInternalEntityUsedInspection */
		$datetimeField = Currency\Compatible\Tools::getDatetimeExpressionTemplate();
		$currencyIterator = Currency\CurrencyTable::getList(array(
			'select' => array(
				'CURRENCY', 'AMOUNT_CNT', 'AMOUNT', 'SORT', 'BASE', 'NUMCODE', 'CREATED_BY', 'MODIFIED_BY',
				new Main\Entity\ExpressionField('DATE_UPDATE_FORMAT', $datetimeField,  array('DATE_UPDATE'), array('data_type' => 'datetime')),
				new Main\Entity\ExpressionField('DATE_CREATE_FORMAT', $datetimeField,  array('DATE_CREATE'), array('data_type' => 'datetime')),
				'FULL_NAME' => 'RT_LANG.FULL_NAME', 'LID' => 'RT_LANG.LID', 'FORMAT_STRING' => 'RT_LANG.FORMAT_STRING',
				'DEC_POINT' => 'RT_LANG.DEC_POINT', 'THOUSANDS_SEP' => 'RT_LANG.THOUSANDS_SEP',
				'DECIMALS' => 'RT_LANG.DECIMALS', 'HIDE_ZERO' => 'RT_LANG.HIDE_ZERO'
			),
			'order' => $currencyOrder,
			'runtime' => array(
				'RT_LANG' => array(
					'data_type' => 'Bitrix\Currency\CurrencyLang',
					'reference' => array(
						'=this.CURRENCY' => 'ref.CURRENCY',
						'=ref.LID' => new Main\DB\SqlExpression('?', $lang)
					)
				)
			)
		));
		unset($datetimeField);
		$currencyList = array();
		while ($currency = $currencyIterator->fetch())
		{
			$currency['DATE_UPDATE'] = $currency['DATE_UPDATE_FORMAT'];
			$currency['DATE_CREATE'] = $currency['DATE_CREATE_FORMAT'];
			$currencyList[] = $currency;
		}
		unset($currency, $currencyIterator);
		$result = new CDBResult();
		$result->InitFromArray($currencyList);
		return $result;
	}

	public static function isUserExists()
	{
		global $USER;
		return (isset($USER) && $USER instanceof CUser);
	}

	/**
	 * @deprecated deprecated since currency 16.5.0
	 * @see \Bitrix\Currency\CurrencyManager::getInstalledCurrencies
	 *
	 * @return array
	 */
	public static function getInstalledCurrencies()
	{
		return Currency\CurrencyManager::getInstalledCurrencies();
	}

	/**
	 * @deprecated deprecated since currency 16.5.0
	 * @see \Bitrix\Currency\CurrencyManager::clearCurrencyCache
	 *
	 * @return void
	 */
	public static function clearCurrencyCache()
	{
		Currency\CurrencyManager::clearCurrencyCache();
	}

	/**
	 * @deprecated deprecated since currency 16.5.0
	 * @see \Bitrix\Currency\CurrencyManager::clearTagCache
	 *
	 * @param string $currency
	 * @return void
	 */
	public static function clearTagCache($currency)
	{
		Currency\CurrencyManager::clearTagCache($currency);
	}

	public static function checkCurrencyID($currency)
	{
		return Currency\CurrencyManager::checkCurrencyID($currency);
	}

	/**
	 * @deprecated deprecated since currency 16.0.0
	 * @see \Bitrix\Currency\CurrencyManager::updateBaseRates
	 *
	 * @param string $currency
	 * @return void
	 */
	public static function updateCurrencyBaseRate($currency)
	{
		Currency\CurrencyManager::updateBaseRates($currency);
	}

	/**
	 * @deprecated deprecated since currency 16.0.0
	 * @see \Bitrix\Currency\CurrencyManager::updateBaseRates
	 *
	 * @return void
	 */
	public static function updateAllCurrencyBaseRate()
	{
		Currency\CurrencyManager::updateBaseRates();
	}

	public static function initCurrencyBaseRateAgent()
	{
		if (!ModuleManager::isModuleInstalled('bitrix24'))
		{
			$agentIterator = CAgent::GetList(
				array(),
				array('MODULE_ID' => 'currency','=NAME' => '\Bitrix\Currency\CurrencyManager::currencyBaseRateAgent();')
			);
			if ($agentIterator)
			{
				if (!($currencyAgent = $agentIterator->Fetch()))
				{
					Currency\CurrencyManager::updateBaseRates();
					$checkDate = Main\Type\DateTime::createFromTimestamp(strtotime('tomorrow 00:01:00'));
					CAgent::AddAgent('\Bitrix\Currency\CurrencyManager::currencyBaseRateAgent();', 'currency', 'Y', 86400, '', 'Y', $checkDate->toString(), 100, false, true);
				}
			}
		}
		return '';
	}

	/**
	 * @deprecated deprecated since currency 16.0.0
	 * @see \Bitrix\Currency\CurrencyManager::updateBaseCurrency
	 *
	 * @param string $currency
	 * @return bool
	 */
	protected static function updateBaseCurrency($currency)
	{
		return Currency\CurrencyManager::updateBaseCurrency($currency);
	}

	/**
	 * @deprecated deprecated since currency 16.0.0
	 * @see \Bitrix\Currency\CurrencyManager::updateBaseRates
	 *
	 * @param string $currency
	 * @param string $updateCurrency
	 */
	protected static function updateBaseRates($currency = '', $updateCurrency = '')
	{
		Currency\CurrencyManager::updateBaseRates($updateCurrency);
	}

	protected static function clearFields($value)
	{
		return ($value !== null);
	}
}

class CCurrency extends CAllCurrency
{

}