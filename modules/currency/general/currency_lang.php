<?php

use Bitrix\Main;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Currency;

/** @deprecated */
class CAllCurrencyLang {}

class CCurrencyLang extends CAllCurrencyLang
{
	/** @deprecated */
	public const SEP_EMPTY = Currency\CurrencyClassifier::SEPARATOR_EMPTY;
	/** @deprecated */
	public const SEP_DOT = Currency\CurrencyClassifier::SEPARATOR_DOT;
	/** @deprecated */
	public const SEP_COMMA = Currency\CurrencyClassifier::SEPARATOR_COMMA;
	/** @deprecated */
	public const SEP_SPACE = Currency\CurrencyClassifier::SEPARATOR_SPACE;
	/** @deprecated */
	public const SEP_NBSPACE = Currency\CurrencyClassifier::SEPARATOR_NBSPACE;

	protected static array $arSeparators = [
		Currency\CurrencyClassifier::SEPARATOR_EMPTY => '',
		Currency\CurrencyClassifier::SEPARATOR_DOT => '.',
		Currency\CurrencyClassifier::SEPARATOR_COMMA => ',',
		Currency\CurrencyClassifier::SEPARATOR_SPACE => ' ',
		Currency\CurrencyClassifier::SEPARATOR_NBSPACE => '&nbsp;',
	];

	protected static array $arDefaultValues = [
		'FORMAT_STRING' => '#',
		'DEC_POINT' => Currency\CurrencyClassifier::DECIMAL_POINT_DOT,
		'THOUSANDS_SEP' => ' ',
		'DECIMALS' => 2,
		'THOUSANDS_VARIANT' => Currency\CurrencyClassifier::SEPARATOR_SPACE,
		'HIDE_ZERO' => 'N',
	];

	protected static array $arCurrencyFormat = [];

	protected static int $useHideZero = 0;

	private static string $region;

	public static function enableUseHideZero(): void
	{
		if (defined('ADMIN_SECTION') && ADMIN_SECTION === true)
		{
			return;
		}
		self::$useHideZero++;
	}

	public static function disableUseHideZero(): void
	{
		if (defined('ADMIN_SECTION') && ADMIN_SECTION === true)
		{
			return;
		}
		self::$useHideZero--;
	}

	public static function isAllowUseHideZero(): bool
	{
		return (!(defined('ADMIN_SECTION') && ADMIN_SECTION === true) && self::$useHideZero >= 0);
	}

	public static function checkFields($action, &$fields, $currency = '', $language = '', $getErrors = false)
	{
		global $DB, $USER, $APPLICATION;

		$getErrors = ($getErrors === true);
		$action = mb_strtoupper($action);
		if ($action != 'ADD' && $action != 'UPDATE')
			return false;
		if (!is_array($fields))
			return false;
		if ($action == 'ADD')
		{
			if (isset($fields['CURRENCY']))
				$currency = $fields['CURRENCY'];
			if (isset($fields['LID']))
				$language = $fields['LID'];
		}
		$currency = Currency\CurrencyManager::checkCurrencyID($currency);
		$language = Currency\CurrencyManager::checkLanguage($language);
		if ($currency === false || $language === false)
			return false;

		$errorMessages = array();

		$clearFields = array(
			'~CURRENCY',
			'~LID',
			'TIMESTAMP_X',
			'DATE_CREATE',
			'~DATE_CREATE',
			'~MODIFIED_BY',
			'~CREATED_BY',
			'~FORMAT_STRING',
			'~FULL_NAME',
			'~DEC_POINT',
			'~THOUSANDS_SEP',
			'~DECIMALS',
			'~THOUSANDS_VARIANT',
			'~HIDE_ZERO'
		);
		if ($action == 'UPDATE')
		{
			$clearFields[] = 'CREATED_BY';
			$clearFields[] = 'CURRENCY';
			$clearFields[] = 'LID';
		}
		$fields = array_filter($fields, 'CCurrencyLang::clearFields');
		foreach ($clearFields as $fieldName)
		{
			if (isset($fields[$fieldName]))
				unset($fields[$fieldName]);
		}
		unset($fieldName, $clearFields);

		if ($action == 'ADD')
		{
			$defaultValues = self::$arDefaultValues;
			unset($defaultValues['FORMAT_STRING']);

			$fields = array_merge($defaultValues, $fields);
			unset($defaultValues);

			if (empty($fields['FORMAT_STRING']))
			{
				$errorMessages[] = array(
					'id' => 'FORMAT_STRING', 'text' => Loc::getMessage('BT_CUR_LANG_ERR_FORMAT_STRING_IS_EMPTY', array('#LANG#' => $language))
				);
			}

			if (empty($errorMessages))
			{
				$fields['CURRENCY'] = $currency;
				$fields['LID'] = $language;
			}
		}

		if (empty($errorMessages))
		{
			if (isset($fields['FORMAT_STRING']) && empty($fields['FORMAT_STRING']))
			{
				$errorMessages[] = array(
					'id' => 'FORMAT_STRING', 'text' => Loc::getMessage('BT_CUR_LANG_ERR_FORMAT_STRING_IS_EMPTY', array('#LANG#' => $language))
				);
			}
			if (isset($fields['DECIMALS']))
			{
				$fields['DECIMALS'] = (int)$fields['DECIMALS'];
				if ($fields['DECIMALS'] < 0)
					$fields['DECIMALS'] = self::$arDefaultValues['DECIMALS'];
			}
			$validateCustomSeparator = false;
			if (isset($fields['THOUSANDS_VARIANT']))
			{
				if (empty($fields['THOUSANDS_VARIANT']) || !isset(self::$arSeparators[$fields['THOUSANDS_VARIANT']]))
				{
					$fields['THOUSANDS_VARIANT'] = false;
					$validateCustomSeparator = true;
				}
				else
				{
					$fields['THOUSANDS_SEP'] = self::$arSeparators[$fields['THOUSANDS_VARIANT']];
				}
			}
			else
			{
				if (isset($fields['THOUSANDS_SEP']))
					$validateCustomSeparator = true;
			}

			if ($validateCustomSeparator)
			{
				if (!isset($fields['THOUSANDS_SEP']) || $fields['THOUSANDS_SEP'] == '')
				{
					$errorMessages[] = array(
						'id' => 'THOUSANDS_SEP',
						'text' => Loc::getMessage(
							'BT_CUR_LANG_ERR_THOUSANDS_SEP_IS_EMPTY',
							array('#LANG#' => $language)
						)
					);
				}
				else
				{
					if (!preg_match('/^&(#[x]?[0-9a-zA-Z]+|[a-zA-Z]+);$/', $fields['THOUSANDS_SEP']))
					{
						$errorMessages[] = array(
							'id' => 'THOUSANDS_SEP',
							'text' => Loc::getMessage(
								'BT_CUR_LANG_ERR_THOUSANDS_SEP_IS_NOT_VALID',
								array('#LANG#' => $language)
							)
						);
					}
				}
			}
			unset($validateCustomSeparator);

			if (isset($fields['HIDE_ZERO']))
				$fields['HIDE_ZERO'] = ($fields['HIDE_ZERO'] == 'Y' ? 'Y' : 'N');
		}
		$intUserID = 0;
		$boolUserExist = CCurrency::isUserExists();
		if ($boolUserExist)
			$intUserID = (int)$USER->GetID();
		$strDateFunction = $DB->GetNowFunction();
		$fields['~TIMESTAMP_X'] = $strDateFunction;
		if ($boolUserExist)
		{
			if (!isset($fields['MODIFIED_BY']))
				$fields['MODIFIED_BY'] = $intUserID;
			$fields['MODIFIED_BY'] = (int)$fields['MODIFIED_BY'];
			if ($fields['MODIFIED_BY'] <= 0)
				$fields['MODIFIED_BY'] = $intUserID;
		}
		if ($action == 'ADD')
		{
			$fields['~DATE_CREATE'] = $strDateFunction;
			if ($boolUserExist)
			{
				if (!isset($arFields['CREATED_BY']))
					$fields['CREATED_BY'] = $intUserID;
				$fields['CREATED_BY'] = (int)$fields['CREATED_BY'];
				if ($fields['CREATED_BY'] <= 0)
					$fields['CREATED_BY'] = $intUserID;
			}
		}

		if (empty($errorMessages))
		{
			if ($action == 'ADD')
			{
				if (!empty($fields['THOUSANDS_VARIANT']) && isset(self::$arSeparators[$fields['THOUSANDS_VARIANT']]))
				{
					if ($fields['DEC_POINT'] == self::$arSeparators[$fields['THOUSANDS_VARIANT']])
					{
						$errorMessages[] = array(
							'id' => 'DEC_POINT',
							'text' => Loc::getMessage(
								'BT_CUR_LANG_ERR_DEC_POINT_EQUAL_THOUSANDS_SEP',
								array('#LANG#' => $language)
							)
						);
					}
				}
			}
			else
			{
				if (
					isset($fields['DEC_POINT'])
					|| (isset($fields['THOUSANDS_VARIANT']) && isset(self::$arSeparators[$fields['THOUSANDS_VARIANT']]))
				)
				{
					$copyFields = $fields;
					$needFields = [];
					if (!isset($copyFields['DEC_POINT']))
						$needFields[] = 'DEC_POINT';
					if (!isset($copyFields['THOUSANDS_VARIANT']))
						$needFields[] = 'THOUSANDS_VARIANT';

					if (!empty($needFields))
					{
						$row = Currency\CurrencyLangTable::getList([
							'select' => $needFields,
							'filter' => ['=CURRENCY' => $currency, '=LID' => $language]
						])->fetch();
						if (!empty($row))
						{
							$copyFields = array_merge($copyFields, $row);
							$needFields = [];
						}
						unset($row);
					}
					if (
						empty($needFields)
						&& (!empty($copyFields['THOUSANDS_VARIANT']) && isset(self::$arSeparators[$copyFields['THOUSANDS_VARIANT']]))
						&& ($copyFields['DEC_POINT'] == self::$arSeparators[$copyFields['THOUSANDS_VARIANT']])
					)
					{
						$errorMessages[] = array(
							'id' => 'DEC_POINT',
							'text' => Loc::getMessage(
								'BT_CUR_LANG_ERR_DEC_POINT_EQUAL_THOUSANDS_SEP',
								array('#LANG#' => $language)
							)
						);
					}
					unset($needFields, $copyFields);
				}
			}
		}

		if (!empty($errorMessages))
		{
			if ($getErrors)
				return $errorMessages;

			$obError = new CAdminException($errorMessages);
			$APPLICATION->ResetException();
			$APPLICATION->ThrowException($obError);
			return false;
		}
		return true;
	}

	public static function Add($arFields)
	{
		global $DB;

		if (!self::checkFields('ADD', $arFields))
			return false;

		$arInsert = $DB->PrepareInsert("b_catalog_currency_lang", $arFields);

		$strSql = "insert into b_catalog_currency_lang(".$arInsert[0].") values(".$arInsert[1].")";
		$DB->Query($strSql);

		Currency\CurrencyManager::clearCurrencyCache($arFields['LID']);
		Currency\CurrencyLangTable::cleanCache();

		return true;
	}

	public static function Update($currency, $lang, $arFields)
	{
		global $DB;

		$currency = Currency\CurrencyManager::checkCurrencyID($currency);
		$lang = Currency\CurrencyManager::checkLanguage($lang);
		if ($currency === false || $lang === false)
			return false;

		if (!self::checkFields('UPDATE', $arFields, $currency, $lang))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_catalog_currency_lang", $arFields);
		if (!empty($strUpdate))
		{
			$strSql = "update b_catalog_currency_lang set ".$strUpdate." where CURRENCY = '".$DB->ForSql($currency)."' and LID='".$DB->ForSql($lang)."'";
			$DB->Query($strSql);

			Currency\CurrencyManager::clearCurrencyCache($lang);
			Currency\CurrencyLangTable::cleanCache();
		}

		return true;
	}

	public static function Delete($currency, $lang)
	{
		global $DB;

		$currency = Currency\CurrencyManager::checkCurrencyID($currency);
		$lang = Currency\CurrencyManager::checkLanguage($lang);
		if ($currency === false || $lang === false)
			return false;

		Currency\CurrencyManager::clearCurrencyCache($lang);
		Currency\CurrencyLangTable::cleanCache();

		$strSql = "delete from b_catalog_currency_lang where CURRENCY = '".$DB->ForSql($currency)."' and LID = '".$DB->ForSql($lang)."'";
		$DB->Query($strSql);

		return true;
	}

	public static function GetByID($currency, $lang)
	{
		global $DB;

		$currency = Currency\CurrencyManager::checkCurrencyID($currency);
		$lang = Currency\CurrencyManager::checkLanguage($lang);
		if ($currency === false || $lang === false)
			return false;

		$strSql = "select * from b_catalog_currency_lang where CURRENCY = '".$DB->ForSql($currency)."' and LID = '".$DB->ForSql($lang)."'";
		$db_res = $DB->Query($strSql);

		if ($res = $db_res->Fetch())
			return $res;

		return false;
	}

	public static function GetCurrencyFormat($currency, $lang = LANGUAGE_ID)
	{
		/** @global CStackCacheManager $stackCacheManager */
		global $stackCacheManager;

		if (defined("CURRENCY_SKIP_CACHE") && CURRENCY_SKIP_CACHE)
		{
			$arCurrencyLang = CCurrencyLang::GetByID($currency, $lang);
		}
		else
		{
			$cacheTime = CURRENCY_CACHE_DEFAULT_TIME;
			if (defined("CURRENCY_CACHE_TIME"))
				$cacheTime = (int)CURRENCY_CACHE_TIME;

			$strCacheKey = $currency."_".$lang;

			$stackCacheManager->SetLength("currency_currency_lang", 20);
			$stackCacheManager->SetTTL("currency_currency_lang", $cacheTime);
			if ($stackCacheManager->Exist("currency_currency_lang", $strCacheKey))
			{
				$arCurrencyLang = $stackCacheManager->Get("currency_currency_lang", $strCacheKey);
			}
			else
			{
				$arCurrencyLang = CCurrencyLang::GetByID($currency, $lang);
				$stackCacheManager->Set("currency_currency_lang", $strCacheKey, $arCurrencyLang);
			}
		}

		return $arCurrencyLang;
	}

	public static function GetList($by = 'lang', $order = 'asc', $currency = '')
	{
		global $DB;

		$strSql = "select CURL.* from b_catalog_currency_lang CURL ";

		if ('' != $currency)
			$strSql .= "where CURL.CURRENCY = '".$DB->ForSql($currency, 3)."' ";

		if (strtolower($by) == "currency") $strSqlOrder = " order by CURL.CURRENCY ";
		elseif (strtolower($by) == "name") $strSqlOrder = " order by CURL.FULL_NAME ";
		else
		{
			$strSqlOrder = " order BY CURL.LID ";
		}

		if ($order == "desc")
			$strSqlOrder .= " desc ";

		$strSql .= $strSqlOrder;

		return $DB->Query($strSql);
	}

	public static function GetDefaultValues(): array
	{
		return self::$arDefaultValues;
	}

	public static function GetSeparators(): array
	{
		return self::$arSeparators;
	}

	public static function GetSeparatorTypes($boolFull = false): array
	{
		$boolFull = (true == $boolFull);
		if ($boolFull)
		{
			return [
				Currency\CurrencyClassifier::SEPARATOR_EMPTY => Loc::getMessage('BT_CUR_LANG_SEP_VARIANT_EMPTY'),
				Currency\CurrencyClassifier::SEPARATOR_DOT => Loc::getMessage('BT_CUR_LANG_SEP_VARIANT_DOT'),
				Currency\CurrencyClassifier::SEPARATOR_COMMA => Loc::getMessage('BT_CUR_LANG_SEP_VARIANT_COMMA'),
				Currency\CurrencyClassifier::SEPARATOR_SPACE => Loc::getMessage('BT_CUR_LANG_SEP_VARIANT_SPACE'),
				Currency\CurrencyClassifier::SEPARATOR_NBSPACE => Loc::getMessage('BT_CUR_LANG_SEP_VARIANT_NBSPACE'),
			];
		}
		return [
			Currency\CurrencyClassifier::SEPARATOR_EMPTY,
			Currency\CurrencyClassifier::SEPARATOR_DOT,
			Currency\CurrencyClassifier::SEPARATOR_COMMA,
			Currency\CurrencyClassifier::SEPARATOR_SPACE,
			Currency\CurrencyClassifier::SEPARATOR_NBSPACE,
		];
	}

	public static function GetFormatTemplates(): array
	{
		$installCurrencies = Currency\CurrencyManager::getInstalledCurrencies();
		$templates = [];
		$templates[] = [
			'TEXT' => '$1.234,10',
			'FORMAT' => '$#',
			'DEC_POINT' => Currency\CurrencyClassifier::DECIMAL_POINT_COMMA,
			'THOUSANDS_VARIANT' => Currency\CurrencyClassifier::SEPARATOR_DOT,
			'DECIMALS' => '2',
		];
		$templates[] = [
			'TEXT' => '$1 234,10',
			'FORMAT' => '$#',
			'DEC_POINT' => Currency\CurrencyClassifier::DECIMAL_POINT_COMMA,
			'THOUSANDS_VARIANT' => Currency\CurrencyClassifier::SEPARATOR_SPACE,
			'DECIMALS' => '2',
		];
		$templates[] = [
			'TEXT' => '1.234,10 USD',
			'FORMAT' => '# USD',
			'DEC_POINT' => Currency\CurrencyClassifier::DECIMAL_POINT_COMMA,
			'THOUSANDS_VARIANT' => Currency\CurrencyClassifier::SEPARATOR_DOT,
			'DECIMALS' => '2',
		];
		$templates[] = [
			'TEXT' => '1 234,10 USD',
			'FORMAT' => '# USD',
			'DEC_POINT' => Currency\CurrencyClassifier::DECIMAL_POINT_COMMA,
			'THOUSANDS_VARIANT' => Currency\CurrencyClassifier::SEPARATOR_SPACE,
			'DECIMALS' => '2',
		];
		$templates[] = [
			'TEXT' => '&euro;2.345,20',
			'FORMAT' => '&euro;#',
			'DEC_POINT' => Currency\CurrencyClassifier::DECIMAL_POINT_COMMA,
			'THOUSANDS_VARIANT' => Currency\CurrencyClassifier::SEPARATOR_DOT,
			'DECIMALS' => '2',
		];
		$templates[] = [
			'TEXT' => '&euro;2 345,20',
			'FORMAT' => '&euro;#',
			'DEC_POINT' => Currency\CurrencyClassifier::DECIMAL_POINT_COMMA,
			'THOUSANDS_VARIANT' => Currency\CurrencyClassifier::SEPARATOR_SPACE,
			'DECIMALS' => '2',
		];
		$templates[] = [
			'TEXT' => '2.345,20 EUR',
			'FORMAT' => '# EUR',
			'DEC_POINT' => Currency\CurrencyClassifier::DECIMAL_POINT_COMMA,
			'THOUSANDS_VARIANT' => Currency\CurrencyClassifier::SEPARATOR_DOT,
			'DECIMALS' => '2',
		];
		$templates[] = [
			'TEXT' => '2 345,20 EUR',
			'FORMAT' => '# EUR',
			'DEC_POINT' => Currency\CurrencyClassifier::DECIMAL_POINT_COMMA,
			'THOUSANDS_VARIANT' => Currency\CurrencyClassifier::SEPARATOR_SPACE,
			'DECIMALS' => '2',
		];

		if (in_array('RUB', $installCurrencies))
		{
			$rubTitle = Loc::getMessage('BT_CUR_LANG_CURRENCY_RUBLE');
			$templates[] = [
				'TEXT' => '3.456,70 '.$rubTitle,
				'FORMAT' => '# '.$rubTitle,
				'DEC_POINT' => Currency\CurrencyClassifier::DECIMAL_POINT_COMMA,
				'THOUSANDS_VARIANT' => Currency\CurrencyClassifier::SEPARATOR_DOT,
				'DECIMALS' => '2',
			];
			$templates[] = [
				'TEXT' => '3 456,70 '.$rubTitle,
				'FORMAT' => '# '.$rubTitle,
				'DEC_POINT' => Currency\CurrencyClassifier::DECIMAL_POINT_COMMA,
				'THOUSANDS_VARIANT' => Currency\CurrencyClassifier::SEPARATOR_SPACE,
				'DECIMALS' => '2',
			];
		}

		return $templates;
	}

	public static function GetFormatDescription($currency)
	{
		$safeFormat = (
			Main\Context::getCurrent()->getRequest()->isAdminSection()
			|| ModuleManager::isModuleInstalled('bitrix24')
		);
		$currency = (string)$currency;

		if (!isset(self::$arCurrencyFormat[$currency]))
		{
			$arCurFormat = CCurrencyLang::GetCurrencyFormat($currency);
			if ($arCurFormat === false)
			{
				$arCurFormat = self::$arDefaultValues;
				$arCurFormat['FULL_NAME'] = $currency;
			}
			else
			{
				if (!isset($arCurFormat['DECIMALS']))
				{
					$arCurFormat['DECIMALS'] = self::$arDefaultValues['DECIMALS'];
				}
				$arCurFormat['DECIMALS'] = (int)$arCurFormat['DECIMALS'];
				if (!isset($arCurFormat['DEC_POINT']))
				{
					$arCurFormat['DEC_POINT'] = self::$arDefaultValues['DEC_POINT'];
				}
				if (
					!empty($arCurFormat['THOUSANDS_VARIANT'])
					&& isset(self::$arSeparators[$arCurFormat['THOUSANDS_VARIANT']])
				)
				{
					$arCurFormat['THOUSANDS_SEP'] = self::$arSeparators[$arCurFormat['THOUSANDS_VARIANT']];
				}
				elseif (!isset($arCurFormat['THOUSANDS_SEP']))
				{
					$arCurFormat['THOUSANDS_SEP'] = self::$arDefaultValues['THOUSANDS_SEP'];
				}
				if (!isset($arCurFormat['FORMAT_STRING']))
				{
					$arCurFormat['FORMAT_STRING'] = self::$arDefaultValues['FORMAT_STRING'];
				}

				$sanitizer = new \CBXSanitizer();
				$sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
				$sanitizer->ApplyDoubleEncode(false);
				$arCurFormat["FORMAT_STRING"] = $sanitizer->SanitizeHtml($arCurFormat["FORMAT_STRING"]);
				unset($sanitizer);

				if ($safeFormat)
				{
					$arCurFormat["FORMAT_STRING"] = strip_tags(preg_replace(
						'#<script[^>]*?>.*?</script[^>]*?>#is',
						'',
						$arCurFormat["FORMAT_STRING"]
					));
				}
				if (empty($arCurFormat['HIDE_ZERO']))
				{
					$arCurFormat['HIDE_ZERO'] = self::$arDefaultValues['HIDE_ZERO'];
				}
			}

			$arCurFormat['TEMPLATE'] = [
				'SINGLE' => $arCurFormat['FORMAT_STRING'],
				'PARTS' => [
					0 => $arCurFormat['FORMAT_STRING'],
				],
				'VALUE_INDEX' => 0,
			];
			$parts = static::explodeFormatTemplate($arCurFormat['FORMAT_STRING']);
			if (!empty($parts))
			{
				$arCurFormat['TEMPLATE']['PARTS'] = $parts;
				$arCurFormat['TEMPLATE']['VALUE_INDEX'] = (int)array_search('#', $parts);
			}
			unset($parts);

			$arCurFormat['CURRENCY'] = $currency;

			self::$arCurrencyFormat[$currency] = $arCurFormat;
		}
		else
		{
			$arCurFormat = self::$arCurrencyFormat[$currency];
		}

		return $arCurFormat;
	}

	public static function CurrencyFormat($price, $currency, $useTemplate = true)
	{
		static $eventExists = null;

		$useTemplate = (bool)$useTemplate;
		if ($useTemplate)
		{
			if ($eventExists === true || $eventExists === null)
			{
				foreach (GetModuleEvents('currency', 'CurrencyFormat', true) as $arEvent)
				{
					$eventExists = true;
					$result = ExecuteModuleEventEx($arEvent, array($price, $currency));
					if ((string)$result !== '')
					{
						return $result;
					}
				}
				if ($eventExists === null)
				{
					$eventExists = false;
				}
			}
		}

		if (!isset($price) || $price === '')
		{
			return '';
		}

		$currency = Currency\CurrencyManager::checkCurrencyID($currency);
		if ($currency === false)
		{
			return '';
		}

		$format = self::$arCurrencyFormat[$currency] ?? self::GetFormatDescription($currency);

		return static::formatValue($price, $format, $useTemplate);
	}

	public static function formatValue($value, array $format, $useTemplate = true): string
	{
		if (!isset(self::$region))
		{
			self::$region = Main\Application::getInstance()->getLicense()->getRegion();
		}

		$format['DECIMALS'] = (int)($format['DECIMALS'] ?? self::$arDefaultValues['DECIMALS']);
		$format['HIDE_ZERO'] ??= self::$arDefaultValues['HIDE_ZERO'];
		$format['DEC_POINT'] = (string)($format['DEC_POINT'] ?? self::$arDefaultValues['DEC_POINT']);
		$format['THOUSANDS_SEP'] = (string)($format['THOUSANDS_SEP'] ?? self::$arDefaultValues['THOUSANDS_SEP']);
		$format['FORMAT_STRING'] = (string)($format['FORMAT_STRING'] ?? self::$arDefaultValues['FORMAT_STRING']);
		$format['CURRENCY'] = $format['CURRENCY'] ?? null;

		if (is_string($value))
		{
			$value = str_replace(',', '.', $value);
		}

		if (
			$format['CURRENCY'] === 'INR'
			&& (self::$region === 'hi' || self::$region === 'in')
		)
		{
			if (self::useSimpleFormat($value, $format['DECIMALS']))
			{
				$value = round((float)$value, $format['DECIMALS']);
			}
			$result = self::extendedInrFormatValue((string)$value, $format);
		}
		else
		{
			if (self::useSimpleFormat($value, $format['DECIMALS']))
			{
				$result = self::simpleFormatValue((float)$value, $format);
			}
			else
			{
				$result = self::extendedFormatValue((string)$value, $format);
			}
		}

		return (
			$useTemplate
				? static::applyTemplate($result, $format['FORMAT_STRING'])
				: $result
		);
	}

	public static function formatEditValue(int|float|string|null $value, array $format): string
	{
		$format['THOUSANDS_VARIANT'] = (string)($format['THOUSANDS_VARIANT'] ?? self::$arDefaultValues['THOUSANDS_VARIANT']);
		$format['THOUSANDS_SEP'] = (string)($format['THOUSANDS_SEP'] ?? self::$arDefaultValues['THOUSANDS_SEP']);
		if (
			$format['THOUSANDS_VARIANT'] === Currency\CurrencyClassifier::SEPARATOR_NBSPACE
			|| $format['THOUSANDS_SEP'] === self::$arSeparators[Currency\CurrencyClassifier::SEPARATOR_NBSPACE]
		)
		{
			$format['THOUSANDS_VARIANT'] = Currency\CurrencyClassifier::SEPARATOR_SPACE;
			$format['THOUSANDS_SEP'] = self::$arSeparators[Currency\CurrencyClassifier::SEPARATOR_SPACE];
		}

		return static::formatValue($value, $format, false);
	}

	public static function applyTemplate($value, $template): string
	{
		return (string)preg_replace('/(^|[^&])#/', '${1}'.$value, (string)$template);
	}

	/**
	 * @deprecated
	 * @see Currency\CurrencyManager::checkLanguage
	 */
	public static function checkLanguage($language): bool|string
	{
		return Currency\CurrencyManager::checkLanguage($language);
	}

	public static function isExistCurrencyLanguage($currency, $language): bool
	{
		global $DB;
		$currency = Currency\CurrencyManager::checkCurrencyID($currency);
		$language = Currency\CurrencyManager::checkLanguage($language);
		if ($currency === false || $language === false)
		{
			return false;
		}
		$query = "select LID from b_catalog_currency_lang where CURRENCY = '".$DB->ForSql($currency)."' and LID = '".$DB->ForSql($language)."'";
		$searchIterator = $DB->Query($query);
		$result = $searchIterator->Fetch();
		unset($searchIterator);

		return !empty($result);
	}

	public static function getParsedCurrencyFormat(string $currency): array
	{
		$result = self::$arCurrencyFormat[$currency] ?? self::GetFormatDescription($currency);

		return $result['TEMPLATE']['PARTS'];
	}

	protected static function explodeFormatTemplate(string $template): ?array
	{
		$result = preg_split('/(?<!&)(#)/', $template, -1, PREG_SPLIT_DELIM_CAPTURE);
		if (!is_array($result))
		{
			return null;
		}
		$resultCount = count($result);
		if ($resultCount > 1)
		{
			$needSlice = false;
			$offset = 0;
			$count = $resultCount;
			if ($result[0] == '')
			{
				$needSlice = true;
				$offset = 1;
				$count--;
			}
			if ($result[$resultCount-1] == '')
			{
				$needSlice = true;
				$count--;
			}
			if ($needSlice)
			{
				$result = array_slice($result, $offset, $count);
			}
			unset($count, $offset, $needSlice);
		}
		unset($resultCount);

		return $result;
	}

	public static function getPriceControl(string $control, string $currency): string
	{
		if ($control === '')
		{
			return '';
		}
		if (!Currency\CurrencyManager::checkCurrencyID($currency))
		{
			return $control;
		}
		$format = static::getParsedCurrencyFormat($currency);
		if (empty($format))
		{
			return $control;
		}
		$index = array_search('#', $format);
		if ($index === false)
		{
			return $control;
		}
		$format[$index] = $control;

		return implode('', $format);
	}

	protected static function clearFields($value): bool
	{
		return ($value !== null);
	}

	public static function getUnFormattedValue(string $formattedValue, string $currency, string $lang = LANGUAGE_ID): string
	{
		$format = static::GetCurrencyFormat($currency, $lang);

		return static::unFormatValue($formattedValue, (string)$format['THOUSANDS_SEP'], (string)$format['DEC_POINT']);
	}

	protected static function unFormatValue(string $formattedValue, string $thousandsSeparator, string $decPoint): string
	{
		$result = $formattedValue;

		if($thousandsSeparator !== '')
		{
			$result = str_replace($thousandsSeparator, '', $result);
		}

		if($decPoint !== '.' && $decPoint !== '')
		{
			$result = str_replace($decPoint, '.', $result);
		}

		return $result;
	}

	private static function useSimpleFormat(string|float|int|null $value, int $decimals): bool
	{
		static $floatPrecision = null;
		if ($floatPrecision === null)
		{
			$floatPrecision = (int)ini_get('precision');
		}
		if (is_int($value) || is_float($value))
		{
			return true;
		}
		if ($value === '' || $value === null || $value === '0')
		{
			return true;
		}

		if ($value !== (string)((float)$value))
		{
			return false;
		}

		$parsedValue = Currency\Helpers\Editor::parseValue($value);
		if ($parsedValue === null)
		{
			return true;
		}

		$flatValue = ($parsedValue[2] === '' ? '0' : $parsedValue[2]);
		$parsedValue[3] ??= '';
		if ($parsedValue[3] !== '' && $parsedValue[3] !== '.')
		{
			$fraction = str_pad($parsedValue[3], $decimals + 1, '0', STR_PAD_RIGHT);
			if ($fraction !== '.')
			{
				$flatValue .= $fraction;
			}
		}

		// empirical condition based on the precision of floating point values
		return (strlen($flatValue) + 2) < $floatPrecision;
	}

	private static function simpleFormatValue(float $value, array $format): string
	{
		$decimals = $format['DECIMALS'];
		if (static::isAllowUseHideZero() && $format['HIDE_ZERO'] === 'Y')
		{
			if (round($value, $format['DECIMALS']) === round($value, 0))
			{
				$decimals = 0;
			}
		}

		return number_format($value, $decimals, $format['DEC_POINT'], $format['THOUSANDS_SEP']);
	}

	private static function extendedFormatValue(string $value, array $format): string
	{
		$triadSep = $format['THOUSANDS_SEP'];

		$value = str_replace(',', '.', $value);
		$parcedValue = explode('.', $value, 2);
		$wholePart = $parcedValue[0] ?? '';
		$fraction = $parcedValue[1] ?? '';
		unset($parcedValue);
		$result = '';
		if ($wholePart[0] === '-')
		{
			$result = '-';
			$wholePart = substr($wholePart, 1);
		}

		$normalizedFraction = self::normalizeFraction($fraction, $format);
		$fraction = $normalizedFraction['FRACTION'];
		if ($normalizedFraction['FIX_UP'] !== '')
		{
			$wholePart = self::roundWholePart($wholePart, $normalizedFraction['FIX_UP']);
		}
		unset($normalizedFraction['FIX_UP']);

		$leadLen = strlen($wholePart) % 3;
		if ($leadLen === 0)
		{
			$leadLen = 3; //take a first triad
		}

		$lead = substr($wholePart, 0, $leadLen);
		$triads = substr($wholePart, $leadLen);

		$result .=
			$triads !== ''
				? $lead . preg_replace('/(\\d{3})/', $triadSep.'\\1', $triads)
				: ($lead !== '' ? $lead : '0')
		;

		if ($fraction !== '')
		{
			$result .= $format['DEC_POINT'] . $fraction;
		}

		return $result;
	}

	private static function extendedInrFormatValue(string $value, array $format): string
	{
		$blockSep = $format['THOUSANDS_SEP'];

		$value = str_replace(',', '.', $value);
		$parcedValue = explode('.', $value, 2);
		$wholePart = $parcedValue[0] ?? '';
		$fraction = $parcedValue[1] ?? '';
		unset($parcedValue);
		$result = '';
		if ($wholePart[0] === '-')
		{
			$result = '-';
			$wholePart = substr($wholePart, 1);
		}

		$normalizedFraction = self::normalizeFraction($fraction, $format);
		$fraction = $normalizedFraction['FRACTION'];
		if ($normalizedFraction['FIX_UP'] !== '')
		{
			$wholePart = self::roundWholePart($wholePart, $normalizedFraction['FIX_UP']);
		}
		unset($normalizedFraction['FIX_UP']);

		if (strlen($wholePart) <= 3)
		{
			$result .= $wholePart;
		}
		else
		{
			$rightTriad = substr($wholePart, -3);
			$wholePart = substr($wholePart, 0, -3);

			$leadLen = strlen($wholePart) % 2;
			if ($leadLen === 0)
			{
				$leadLen = 2; //take a first block
			}

			$lead = substr($wholePart, 0, $leadLen);
			$blocks = substr($wholePart, $leadLen);

			$result .=
				$blocks !== ''
					? $lead . preg_replace('/(\\d{2})/', $blockSep.'\\1', $blocks)
					: ($lead !== '' ? $lead : '')
			;

			$result .= $blockSep . $rightTriad;
		}

		if ($fraction !== '')
		{
			$result .= $format['DEC_POINT'] . $fraction;
		}

		return $result;
	}

	private static function normalizeFraction(string $fraction, array $format): ?array
	{
		$decimals = $format['DECIMALS'];
		if (static::isAllowUseHideZero() && $format['HIDE_ZERO'] === 'Y')
		{
			if ($fraction === '')
			{
				$decimals = 0;
			}
			else
			{
				$prepared = [];
				if (preg_match('/^0+$/', $fraction, $prepared))
				{
					$decimals = 0;
				}
				unset($prepared);
			}
		}

		$result = [
			'FRACTION' => '',
			'FIX_UP' => '',
		];

		if ($fraction === '')
		{
			$result['FRACTION'] = $decimals > 0 ? str_repeat('0', $decimals) : '';

			return $result;
		}

		$fractionLength = strlen($fraction);
		if ($fractionLength > $decimals)
		{
			$carry = 0;
			for ($i = $fractionLength - 1; $i >=$decimals; $i--)
			{
				$value = (int)$fraction[$i] + $carry;
				if ($value >= 5)
				{
					$carry = 1;
				}
			}

			$roundFraction = '';
			for ($i = $decimals - 1; $i >= 0; $i--)
			{
				$value = (int)$fraction[$i] + $carry;
				$carry = 0;

				if ($value > 9)
				{
					$carry = 1;
					$value -= 10;
				}

				$roundFraction = $value . $roundFraction;
			}
			$result['FRACTION'] = $roundFraction;
			if ($carry === 1)
			{
				$result['FIX_UP'] = '1';
			}
		}
		elseif ($fractionLength < $decimals)
		{
			$result['FRACTION'] = str_pad($fraction, $decimals, '0', STR_PAD_RIGHT);
		}
		else
		{
			$result['FRACTION'] = $fraction;
		}

		return $result;
	}

	private static function roundWholePart(string $wholePart, string $fixUp): string
	{
		$length = strlen($wholePart);
		$carry = 0;

		$value = (int)$wholePart[$length - 1] + (int)$fixUp;
		if ($value > 9)
		{
			$carry = 1;
			$value -= 10;
		}
		$result = $value;

		for ($i = $length - 2; $i >= 0; $i--)
		{
			$value = (int)$wholePart[$i] + $carry;
			$carry = 0;

			if ($value > 9)
			{
				$carry = 1;
				$value -= 10;
			}

			$result = $value . $result;
		}

		if ($carry)
		{
			$result = '1' . $result;
		}

		return $result;
	}
}
