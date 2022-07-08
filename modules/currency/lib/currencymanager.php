<?php
namespace Bitrix\Currency;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\LanguageTable;

/**
 * Class CurrencyTable
 *
 * @package Bitrix\Currency
 **/
class CurrencyManager
{
	public const CACHE_BASE_CURRENCY_ID = 'currency_base_currency';
	public const CACHE_CURRENCY_LIST_ID = 'currency_currency_list';
	public const CACHE_CURRENCY_SHORT_LIST_ID = 'currency_short_list_';
	public const CACHE_CURRENCY_SYMBOL_LIST_ID = 'currency_symbol_list_';
	public const CACHE_CURRENCY_NAME_LIST_ID = 'currency_name_list_';

	public const EVENT_ON_AFTER_UPDATE_BASE_RATE = 'onAfterUpdateCurrencyBaseRate';
	public const EVENT_ON_UPDATE_BASE_CURRENCY = 'onUpdateBaseCurrency';
	public const EVENT_ON_AFTER_UPDATE_BASE_CURRENCY = 'onAfterUpdateBaseCurrency';

	protected static ?string $baseCurrency = null;

	/**
	 * Check currency id.
	 *
	 * @param string $currency	Currency id.
	 * @return bool|string
	 */
	public static function checkCurrencyID($currency)
	{
		$currency = (string)$currency;
		return ($currency === '' || mb_strlen($currency) > 3 ? false : $currency);
	}

	/**
	 * Check language id.
	 *
	 * @param string $language	Language.
	 * @return bool|string
	 */
	public static function checkLanguage($language)
	{
		$language = (string)$language;
		return ($language === '' || mb_strlen($language) > 2 ? false : $language);
	}

	/**
	 * Return base currency.
	 *
	 * @return string
	 */
	public static function getBaseCurrency(): ?string
	{
		if (self::$baseCurrency === null)
		{
			/** @var \Bitrix\Main\Data\ManagedCache $managedCache */
			$skipCache = (defined('CURRENCY_SKIP_CACHE') && CURRENCY_SKIP_CACHE);
			$currencyFound = false;
			$currencyFromCache = false;
			if (!$skipCache)
			{
				$cacheTime = (int)(defined('CURRENCY_CACHE_TIME') ? CURRENCY_CACHE_TIME : CURRENCY_CACHE_DEFAULT_TIME);
				$managedCache = Application::getInstance()->getManagedCache();
				$currencyFromCache = $managedCache->read($cacheTime, self::CACHE_BASE_CURRENCY_ID, CurrencyTable::getTableName());
				if ($currencyFromCache)
				{
					$currencyFound = true;
					self::$baseCurrency = (string)$managedCache->get(self::CACHE_BASE_CURRENCY_ID);
				}
			}
			if ($skipCache || !$currencyFound)
			{
				$currencyIterator = CurrencyTable::getList([
					'select' => [
						'CURRENCY',
					],
					'filter' => [
						'=BASE' => 'Y',
						'=AMOUNT' => 1,
					],
				]);
				if ($currency = $currencyIterator->fetch())
				{
					$currencyFound = true;
					self::$baseCurrency = $currency['CURRENCY'];
				}
				unset($currency, $currencyIterator);
			}
			if (!$skipCache && $currencyFound && !$currencyFromCache)
			{
				$managedCache->set(self::CACHE_BASE_CURRENCY_ID, self::$baseCurrency);
			}
		}

		return self::$baseCurrency;
	}

	/**
	 * Return currency short list.
	 *
	 * @return array
	 */
	public static function getCurrencyList(): array
	{
		$currencyTableName = CurrencyTable::getTableName();
		$managedCache = Application::getInstance()->getManagedCache();

		$cacheTime = (int)(defined('CURRENCY_CACHE_TIME') ? CURRENCY_CACHE_TIME : CURRENCY_CACHE_DEFAULT_TIME);
		$cacheId = self::CACHE_CURRENCY_SHORT_LIST_ID.LANGUAGE_ID;

		if ($managedCache->read($cacheTime, $cacheId, $currencyTableName))
		{
			$currencyList = $managedCache->get($cacheId);
		}
		else
		{
			$currencyList = [];
			$currencyIterator = CurrencyTable::getList([
				'select' => [
					'CURRENCY',
					'FULL_NAME' => 'CURRENT_LANG_FORMAT.FULL_NAME',
					'SORT',
				],
				'order' => [
					'SORT' => 'ASC',
					'CURRENCY' => 'ASC',
				],
			]);
			while ($currency = $currencyIterator->fetch())
			{
				$currency['FULL_NAME'] = (string)$currency['FULL_NAME'];
				$currencyList[$currency['CURRENCY']] = $currency['CURRENCY']
					. ($currency['FULL_NAME'] !== '' ? ' (' . $currency['FULL_NAME'] . ')' : '')
				;
			}
			unset($currency, $currencyIterator);
			$managedCache->set($cacheId, $currencyList);
		}

		return $currencyList;
	}

	/**
	 * Returns currency symbol list.
	 *
	 * @return array
	 */
	public static function getSymbolList(): array
	{
		$currencyTableName = CurrencyTable::getTableName();
		$managedCache = Application::getInstance()->getManagedCache();

		$cacheTime = defined('CURRENCY_CACHE_TIME') ? (int)CURRENCY_CACHE_TIME : CURRENCY_CACHE_DEFAULT_TIME;
		$cacheId = self::CACHE_CURRENCY_SYMBOL_LIST_ID.LANGUAGE_ID;

		if ($managedCache->read($cacheTime, $cacheId, $currencyTableName))
		{
			$currencyList = $managedCache->get($cacheId);
		}
		else
		{
			$sanitizer = new \CBXSanitizer();
			$sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
			$sanitizer->ApplyDoubleEncode(false);

			$currencyList = [];
			$currencyIterator = CurrencyTable::getList([
				'select' => [
					'CURRENCY',
					'FORMAT_STRING' => 'CURRENT_LANG_FORMAT.FORMAT_STRING',
					'SORT',
				],
				'order' => [
					'SORT' => 'ASC',
					'CURRENCY' => 'ASC',
				],
			]);
			while ($currency = $currencyIterator->fetch())
			{
				$showValue = $currency['CURRENCY'];
				$currencyFormat = (string)$currency['FORMAT_STRING'];
				if ($currencyFormat !== '')
				{
					$symbol = \CCurrencyLang::applyTemplate('', $currencyFormat);
					if (is_string($symbol))
					{
						$symbol = trim($symbol);
						if ($symbol !== '')
						{
							$showValue = $symbol;
						}
					}
				}
				$currencyList[$currency['CURRENCY']] = $sanitizer->SanitizeHtml($showValue);
			}

			$managedCache->set($cacheId, $currencyList);
		}

		return $currencyList;
	}

	/**
	 * Returns currency name list.
	 *
	 * @return array
	 */
	public static function getNameList(): array
	{
		$currencyTableName = CurrencyTable::getTableName();
		$managedCache = Application::getInstance()->getManagedCache();

		$cacheTime = defined('CURRENCY_CACHE_TIME') ? (int)CURRENCY_CACHE_TIME : CURRENCY_CACHE_DEFAULT_TIME;
		$cacheId = self::CACHE_CURRENCY_NAME_LIST_ID.LANGUAGE_ID;

		if ($managedCache->read($cacheTime, $cacheId, $currencyTableName))
		{
			$currencyList = $managedCache->get($cacheId);
		}
		else
		{
			$currencyList = [];
			$currencyIterator = CurrencyTable::getList([
				'select' => [
					'CURRENCY',
					'FULL_NAME' => 'CURRENT_LANG_FORMAT.FULL_NAME',
					'SORT',
				],
				'order' => [
					'SORT' => 'ASC',
					'CURRENCY' => 'ASC',
				],
			]);
			while ($currency = $currencyIterator->fetch())
			{
				$fullName = (string)$currency['FULL_NAME'];
				if ($fullName === '')
				{
					$fullName = $currency['CURRENCY'];
				}

				$currencyList[$currency['CURRENCY']] = $fullName;
			}

			$managedCache->set($cacheId, $currencyList);
		}

		return $currencyList;
	}

	/**
	 * Verifying the existence of the currency by its code.
	 *
	 * @param string $currency		Currency code.
	 * @return bool
	 */
	public static function isCurrencyExist($currency): bool
	{
		$currency = static::checkCurrencyID($currency);
		if ($currency === false)
		{
			return false;
		}
		$currencyList = static::getCurrencyList();

		return isset($currencyList[$currency]);
	}

	/**
	 * Return currency list, create to install module.
	 *
	 * @return array
	 */
	public static function getInstalledCurrencies(): array
	{
		$installedCurrencies = Option::get('currency', 'installed_currencies');
		if ($installedCurrencies === '')
		{
			$bitrix24 = Main\ModuleManager::isModuleInstalled('bitrix24');

			$languageID = '';
			$siteIterator = Main\SiteTable::getList([
				'select' => [
					'LID',
					'LANGUAGE_ID',
				],
				'filter' => [
					'=DEF' => 'Y',
					'=ACTIVE' => 'Y',
				],
			]);
			$site = $siteIterator->fetch();
			if (!empty($site))
			{
				$languageID = (string)$site['LANGUAGE_ID'];
			}
			unset($site, $siteIterator);

			if ($languageID === '')
			{
				$languageID = 'en';
			}

			if (!$bitrix24 && $languageID === 'ru')
			{
				$languageList = [];
				$languageIterator = LanguageTable::getList([
					'select' => [
						'ID',
					],
					'filter' => [
						'@ID' => [
							'kz',
							'by',
							'ua'
						],
						'=ACTIVE' => 'Y',
					],
				]);
				while ($language = $languageIterator->fetch())
				{
					$languageList[$language['ID']] = $language['ID'];
				}
				unset($language, $languageIterator);
				if (isset($languageList['kz']))
				{
					$languageID = 'kz';
				}
				elseif (isset($languageList['by']))
				{
					$languageID = 'by';
				}
				elseif (isset($languageList['ua']))
				{
					$languageID = 'ua';
				}
				unset($languageList);
			}
			unset($bitrix24);

			switch ($languageID)
			{
				case 'br':
					$currencyList = [
						'BYN',
						'RUB',
						'USD',
						'EUR',
					];
					break;
				case 'ua':
					$currencyList = [
						'UAH',
						'RUB',
						'USD',
						'EUR',
					];
					break;
				case 'kz':
					$currencyList = [
						'KZT',
						'RUB',
						'USD',
						'EUR',
					];
					break;
				case 'ru':
					$currencyList = [
						'RUB',
						'USD',
						'EUR',
						'UAH',
						'BYN',
					];
					break;
				case 'de':
				case 'en':
				case 'tc':
				case 'sc':
				case 'la':
				default:
					$currencyList = [
						'USD',
						'EUR',
						'CNY',
						'BRL',
						'INR',
					];
					break;
			}

			Option::set('currency', 'installed_currencies', implode(',', $currencyList), '');

			return $currencyList;
		}
		else
		{
			return explode(',', $installedCurrencies);
		}
	}

	/**
	 * Clear currency cache.
	 *
	 * @param string $language		Language id.
	 * @return void
	 */
	public static function clearCurrencyCache($language = '')
	{
		$language = static::checkLanguage($language);
		$currencyTableName = CurrencyTable::getTableName();

		$managedCache = Application::getInstance()->getManagedCache();
		$managedCache->clean(self::CACHE_CURRENCY_LIST_ID, $currencyTableName);
		if (empty($language))
		{
			$languageIterator = LanguageTable::getList([
				'select' => ['ID']
			]);
			while ($oneLanguage = $languageIterator->fetch())
			{
				$managedCache->clean(self::CACHE_CURRENCY_LIST_ID.'_'.$oneLanguage['ID'], $currencyTableName);
				$managedCache->clean(self::CACHE_CURRENCY_SHORT_LIST_ID.$oneLanguage['ID'], $currencyTableName);
				$managedCache->clean(self::CACHE_CURRENCY_SYMBOL_LIST_ID.$oneLanguage['ID'], $currencyTableName);
				$managedCache->clean(self::CACHE_CURRENCY_NAME_LIST_ID.$oneLanguage['ID'], $currencyTableName);
			}
			unset($oneLanguage, $languageIterator);
		}
		else
		{
			$managedCache->clean(self::CACHE_CURRENCY_LIST_ID.'_'.$language, $currencyTableName);
			$managedCache->clean(self::CACHE_CURRENCY_SHORT_LIST_ID.$language, $currencyTableName);
			$managedCache->clean(self::CACHE_CURRENCY_SYMBOL_LIST_ID.$language, $currencyTableName);
			$managedCache->clean(self::CACHE_CURRENCY_NAME_LIST_ID.$language, $currencyTableName);
		}
		$managedCache->clean(self::CACHE_BASE_CURRENCY_ID, $currencyTableName);

		/** @global \CStackCacheManager $stackCacheManager */
		global $stackCacheManager;
		$stackCacheManager->clear('currency_rate');
		$stackCacheManager->clear('currency_currency_lang');
	}

	/**
	 * Clear tag currency cache.
	 *
	 * @param string $currency	Currency id.
	 * @return void
	 */
	public static function clearTagCache($currency)
	{
		if (!defined('BX_COMP_MANAGED_CACHE'))
			return;
		$currency = static::checkCurrencyID($currency);
		if ($currency === false)
			return;
		Application::getInstance()->getTaggedCache()->clearByTag('currency_id_'.$currency);
	}

	/**
	 * Agent for update current currencies rates to base currency.
	 *
	 * @return string
	 */
	public static function currencyBaseRateAgent(): string
	{
		static::updateBaseRates();

		return '\Bitrix\Currency\CurrencyManager::currencyBaseRateAgent();';
	}

	/**
	 * Update current currencies rates to base currency.
	 *
	 * @param string $updateCurrency		Update currency id.
	 * @return void
	 * @throws Main\ArgumentException
	 * @throws \Exception
	 */
	public static function updateBaseRates($updateCurrency = '')
	{
		$currency = static::getBaseCurrency();
		if ($currency === '')
			return;

		$currencyIterator = CurrencyTable::getList([
			'select' => [
				'CURRENCY',
				'CURRENT_BASE_RATE',
			],
			'filter' => ($updateCurrency == '' ? [] : ['=CURRENCY' => $updateCurrency])
		]);
		while ($existCurrency = $currencyIterator->fetch())
		{
			$baseRate = ($existCurrency['CURRENCY'] != $currency
				? \CCurrencyRates::getConvertFactorEx($existCurrency['CURRENCY'], $currency)
				: 1
			);
			$updateResult = CurrencyTable::update($existCurrency['CURRENCY'], array('CURRENT_BASE_RATE' => $baseRate));
			if ($updateResult->isSuccess())
			{
				$event = new Main\Event(
					'currency',
					self::EVENT_ON_AFTER_UPDATE_BASE_RATE,
					[
						'OLD_BASE_RATE' => (float)$existCurrency['CURRENT_BASE_RATE'],
						'CURRENT_BASE_RATE' => $baseRate,
						'BASE_CURRENCY' => $currency,
						'CURRENCY' => $existCurrency['CURRENCY'],
					]
				);
				$event->send();
			}
			unset($updateResult);
			unset($baseRate);
		}
		unset($existCurrency, $currencyIterator);
	}

	/**
	 * Update base currency.
	 *
	 * @param string $currency			Currency id.
	 * @return bool
	 */
	public static function updateBaseCurrency($currency): bool
	{
		/** @global \CUser $USER */
		global $USER;
		$currency = CurrencyManager::checkCurrencyID($currency);
		if ($currency === false)
			return false;

		$event = new Main\Event(
			'currency',
			self::EVENT_ON_UPDATE_BASE_CURRENCY,
			[
				'NEW_BASE_CURRENCY' => $currency,
			]
		);
		$event->send();
		unset($event);

		$conn = Main\Application::getConnection();
		$helper = $conn->getSqlHelper();

		$userID = (isset($USER) && $USER instanceof \CUser ? (int)$USER->getID() : 0);

		$tableName = $helper->quote(CurrencyTable::getTableName());
		$baseField = $helper->quote('BASE');
		$dateUpdateField = $helper->quote('DATE_UPDATE');
		$modifiedByField = $helper->quote('MODIFIED_BY');
		$amountField = $helper->quote('AMOUNT');
		$amountCntField = $helper->quote('AMOUNT_CNT');
		$currencyField = $helper->quote('CURRENCY');
		$query = 'update '.$tableName.' set '.$baseField.' = \'N\', '.
			$dateUpdateField.' = '.$helper->getCurrentDateTimeFunction().', '.
			$modifiedByField.' = '.($userID == 0 ? 'NULL' : $userID).
			' where '.$currencyField.' <> \''.$helper->forSql($currency).'\' and '.$baseField.' = \'Y\'';
		$conn->queryExecute($query);
		$query = 'update '.$tableName.' set '.$baseField.' = \'Y\', '.
			$dateUpdateField.' = '.$helper->getCurrentDateTimeFunction().', '.
			$modifiedByField.' = '.($userID == 0 ? 'NULL' : $userID).', '.
			$amountField.' = 1, '.$amountCntField.' = 1 where '.$currencyField.' = \''.$helper->forSql($currency).'\'';
		$conn->queryExecute($query);

		static::updateBaseRates();

		$event = new Main\Event(
			'currency',
			self::EVENT_ON_AFTER_UPDATE_BASE_CURRENCY,
			[
				'NEW_BASE_CURRENCY' => $currency,
			]
		);
		$event->send();
		unset($event);
		self::$baseCurrency = null;

		return true;
	}
}
