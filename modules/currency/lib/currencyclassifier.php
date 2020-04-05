<?php

namespace Bitrix\Currency;

use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Type\Collection;

Loc::loadMessages(__FILE__);

/**
 * Class CurrencyClassifier
 */
final class CurrencyClassifier
{
	const SEPARATOR_COMMA = 'C';

	private static $lastSortLanguage;
	private static $separators = array();
	private static $separatorsTypes = array();

	/**
	 * Returns currency description with language settings.
	 *
	 * @param string $currency		Currency identifier.
	 * @param array $languages		Language id list.
	 * @return array|null
	 */
	public static function getCurrency($currency, array $languages)
	{
		$currency = CurrencyManager::checkCurrencyID($currency);
		if (!$currency)
			return null;
		self::prepare($languages, '');
		return (isset(self::$currencyClassifier[$currency]) ? self::$currencyClassifier[$currency] : null);
	}

	/**
	 * Return classifier
	 *
	 * @param array $languageIds - Array of languages
	 * @param string $baseLanguageId - Base language
	 * @return array
	 */
	public static function get(array $languageIds, $baseLanguageId)
	{
		self::prepare($languageIds, $baseLanguageId);
		return self::$currencyClassifier;
	}

	/**
	 * Preparing of classifier
	 *
	 * @param array $languageIds - Array of languages
	 * @param string $baseLanguageId - Base language
	 */
	private static function prepare($languageIds, $baseLanguageId)
	{
		self::fillSeparatorsData();
		self::fill($languageIds);
		self::sort($baseLanguageId);
	}

	/**
	 * Fill classifier with missing languages
	 *
	 * @param array $languageIds - Array of languages
	 */
	private static function fill($languageIds)
	{
		foreach ($languageIds as $languageId)
		{
			reset(self::$currencyClassifier);
			$currentElement = current(self::$currencyClassifier);
			$upperLanguageId = strtoupper($languageId);

			if (isset($currentElement[$upperLanguageId]))
				continue;

			foreach (self::$currencyClassifier as $key => $value)
			{
				$currencyName = Loc::getMessage('CURRENCY_CLASSIFIER_'.$value['SYM_CODE'].'_FULL_NAME', null, $languageId);
				$formatString = Loc::getMessage('CURRENCY_CLASSIFIER_'.$value['SYM_CODE'].'_FORMAT_STRING', null, $languageId);
				$decimalPoint = Loc::getMessage('CURRENCY_CLASSIFIER_'.$value['SYM_CODE'].'_DEC_POINT', null, $languageId);
				$thousandsVariant = Loc::getMessage('CURRENCY_CLASSIFIER_'.$value['SYM_CODE'].'_THOUSANDS_VARIANT', null, $languageId);
				if (!isset(self::$separators[$thousandsVariant]))
					$thousandsVariant = null;
				$decimals = Loc::getMessage('CURRENCY_CLASSIFIER_'.$value['SYM_CODE'].'_DECIMALS', null, $languageId);

				$defaultProperties = $value['DEFAULT'];

				self::$currencyClassifier[$key][$upperLanguageId] = array(
					'FULL_NAME' => !is_null($currencyName) ? $currencyName : $defaultProperties['FULL_NAME'],
					'FORMAT_STRING' => !is_null($formatString) ? $formatString : $defaultProperties['FORMAT_STRING'],
					'DEC_POINT' => !is_null($decimalPoint) ? $decimalPoint : $defaultProperties['DEC_POINT'],
					'THOUSANDS_VARIANT' => !is_null($thousandsVariant) ? $thousandsVariant : $defaultProperties['THOUSANDS_VARIANT'],
					'DECIMALS' => !is_null($decimals) ? $decimals : $defaultProperties['DECIMALS']
				);

				$addedThousandsVariant = self::$currencyClassifier[$key][$upperLanguageId]['THOUSANDS_VARIANT'];

				self::$currencyClassifier[$key][$upperLanguageId]['THOUSANDS_SEP'] = self::$separators[$addedThousandsVariant];
				self::$currencyClassifier[$key][$upperLanguageId]['THOUSANDS_SEP_DESCR'] = self::$separatorsTypes[$addedThousandsVariant];
			}
		}
	}

	/**
	 * Sort classifier
	 *
	 * @param string $baseLanguageId - Base language
	 */
	private static function sort($baseLanguageId)
	{
		$baseLanguageId = strtoupper(trim($baseLanguageId));
		if ($baseLanguageId === '')
			return;
		if (self::$lastSortLanguage == $baseLanguageId)
			return;

		Collection::sortByColumn(
			self::$currencyClassifier,
			$baseLanguageId,
			array(
				$baseLanguageId => function($row)
				{
					return $row['FULL_NAME'];
				}
			),
			null,
			true
		);

		self::$lastSortLanguage = $baseLanguageId;
	}

	/**
	 * Fill arrays with separators data
	 */
	private static function fillSeparatorsData()
	{
		if (empty(self::$separators))
			self::$separators = \CCurrencyLang::GetSeparators();

		if (empty(self::$separatorsTypes))
			self::$separatorsTypes = \CCurrencyLang::GetSeparatorTypes(true);
	}

	private static $currencyClassifier = array(
		'ALL' =>
			array(
				'NUM_CODE' => '008',
				'SYM_CODE' => 'ALL',
				'DEFAULT' => array(
					'FULL_NAME' => 'Lek',
					'FORMAT_STRING' => 'L#VALUE# ',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'DZD' =>
			array(
				'NUM_CODE' => '012',
				'SYM_CODE' => 'DZD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Algerian Dinar',
					'FORMAT_STRING' => 'DA#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'ARS' =>
			array(
				'NUM_CODE' => '032',
				'SYM_CODE' => 'ARS',
				'DEFAULT' => array(
					'FULL_NAME' => 'Argentine Peso',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'AUD' =>
			array(
				'NUM_CODE' => '036',
				'SYM_CODE' => 'AUD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Australian Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BSD' =>
			array(
				'NUM_CODE' => '044',
				'SYM_CODE' => 'BSD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Bahamian Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BHD' =>
			array(
				'NUM_CODE' => '048',
				'SYM_CODE' => 'BHD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Bahraini Dinar',
					'FORMAT_STRING' => 'BD#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 3,
				),
			),
		'BDT' =>
			array(
				'NUM_CODE' => '050',
				'SYM_CODE' => 'BDT',
				'DEFAULT' => array(
					'FULL_NAME' => 'Taka',
					'FORMAT_STRING' => '&#2547;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'AMD' =>
			array(
				'NUM_CODE' => '051',
				'SYM_CODE' => 'AMD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Armenian Dram',
					'FORMAT_STRING' => 'AMD#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BBD' =>
			array(
				'NUM_CODE' => '052',
				'SYM_CODE' => 'BBD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Barbados Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BMD' =>
			array(
				'NUM_CODE' => '060',
				'SYM_CODE' => 'BMD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Bermudian Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BTN' =>
			array(
				'NUM_CODE' => '064',
				'SYM_CODE' => 'BTN',
				'DEFAULT' => array(
					'FULL_NAME' => 'Ngultrum',
					'FORMAT_STRING' => 'Nu#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BOB' =>
			array(
				'NUM_CODE' => '068',
				'SYM_CODE' => 'BOB',
				'DEFAULT' => array(
					'FULL_NAME' => 'Boliviano',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BWP' =>
			array(
				'NUM_CODE' => '072',
				'SYM_CODE' => 'BWP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Pula',
					'FORMAT_STRING' => 'P#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BZD' =>
			array(
				'NUM_CODE' => '084',
				'SYM_CODE' => 'BZD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Belize Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'SBD' =>
			array(
				'NUM_CODE' => '090',
				'SYM_CODE' => 'SBD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Solomon Islands Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BND' =>
			array(
				'NUM_CODE' => '096',
				'SYM_CODE' => 'BND',
				'DEFAULT' => array(
					'FULL_NAME' => 'Brunei Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'MMK' =>
			array(
				'NUM_CODE' => '104',
				'SYM_CODE' => 'MMK',
				'DEFAULT' => array(
					'FULL_NAME' => 'Kyat',
					'FORMAT_STRING' => 'K#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BIF' =>
			array(
				'NUM_CODE' => '108',
				'SYM_CODE' => 'BIF',
				'DEFAULT' => array(
					'FULL_NAME' => 'Burundi Franc',
					'FORMAT_STRING' => '&#8355;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'KHR' =>
			array(
				'NUM_CODE' => '116',
				'SYM_CODE' => 'KHR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Riel',
					'FORMAT_STRING' => '&#6107;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'CAD' =>
			array(
				'NUM_CODE' => '124',
				'SYM_CODE' => 'CAD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Canadian Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'CVE' =>
			array(
				'NUM_CODE' => '132',
				'SYM_CODE' => 'CVE',
				'DEFAULT' => array(
					'FULL_NAME' => 'Cabo Verde Escudo',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'KYD' =>
			array(
				'NUM_CODE' => '136',
				'SYM_CODE' => 'KYD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Cayman Islands Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'LKR' =>
			array(
				'NUM_CODE' => '144',
				'SYM_CODE' => 'LKR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Sri Lanka Rupee',
					'FORMAT_STRING' => '&#8360;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'CLP' =>
			array(
				'NUM_CODE' => '152',
				'SYM_CODE' => 'CLP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Chilean Peso',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'CNY' =>
			array(
				'NUM_CODE' => '156',
				'SYM_CODE' => 'CNY',
				'DEFAULT' => array(
					'FULL_NAME' => 'Yuan Renminbi',
					'FORMAT_STRING' => '&#165;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'COP' =>
			array(
				'NUM_CODE' => '170',
				'SYM_CODE' => 'COP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Colombian Peso',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'KMF' =>
			array(
				'NUM_CODE' => '174',
				'SYM_CODE' => 'KMF',
				'DEFAULT' => array(
					'FULL_NAME' => 'Comorian Franc',
					'FORMAT_STRING' => '&#8355;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'CRC' =>
			array(
				'NUM_CODE' => '188',
				'SYM_CODE' => 'CRC',
				'DEFAULT' => array(
					'FULL_NAME' => 'Costa Rican Coln',
					'FORMAT_STRING' => '&#8353;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'HRK' =>
			array(
				'NUM_CODE' => '191',
				'SYM_CODE' => 'HRK',
				'DEFAULT' => array(
					'FULL_NAME' => 'Kuna',
					'FORMAT_STRING' => 'Kn#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'CUP' =>
			array(
				'NUM_CODE' => '192',
				'SYM_CODE' => 'CUP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Cuban Peso',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'CZK' =>
			array(
				'NUM_CODE' => '203',
				'SYM_CODE' => 'CZK',
				'DEFAULT' => array(
					'FULL_NAME' => 'Czech Koruna',
					'FORMAT_STRING' => 'CZK#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'DKK' =>
			array(
				'NUM_CODE' => '208',
				'SYM_CODE' => 'DKK',
				'DEFAULT' => array(
					'FULL_NAME' => 'Danish Krone',
					'FORMAT_STRING' => 'kr#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'DOP' =>
			array(
				'NUM_CODE' => '214',
				'SYM_CODE' => 'DOP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Dominican Peso',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'SVC' =>
			array(
				'NUM_CODE' => '222',
				'SYM_CODE' => 'SVC',
				'DEFAULT' => array(
					'FULL_NAME' => 'El Salvador Colon',
					'FORMAT_STRING' => '&#8353;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'ETB' =>
			array(
				'NUM_CODE' => '230',
				'SYM_CODE' => 'ETB',
				'DEFAULT' => array(
					'FULL_NAME' => 'Ethiopian Birr',
					'FORMAT_STRING' => 'Br#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'ERN' =>
			array(
				'NUM_CODE' => '232',
				'SYM_CODE' => 'ERN',
				'DEFAULT' => array(
					'FULL_NAME' => 'Nakfa',
					'FORMAT_STRING' => 'Nfk#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'FKP' =>
			array(
				'NUM_CODE' => '238',
				'SYM_CODE' => 'FKP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Falkland Islands Pound',
					'FORMAT_STRING' => '&pound;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'FJD' =>
			array(
				'NUM_CODE' => '242',
				'SYM_CODE' => 'FJD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Fiji Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'DJF' =>
			array(
				'NUM_CODE' => '262',
				'SYM_CODE' => 'DJF',
				'DEFAULT' => array(
					'FULL_NAME' => 'Djibouti Franc',
					'FORMAT_STRING' => '&#8355;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'GMD' =>
			array(
				'NUM_CODE' => '270',
				'SYM_CODE' => 'GMD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Dalasi',
					'FORMAT_STRING' => 'D#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'GIP' =>
			array(
				'NUM_CODE' => '292',
				'SYM_CODE' => 'GIP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Gibraltar Pound',
					'FORMAT_STRING' => '&pound;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'GTQ' =>
			array(
				'NUM_CODE' => '320',
				'SYM_CODE' => 'GTQ',
				'DEFAULT' => array(
					'FULL_NAME' => 'Quetzal',
					'FORMAT_STRING' => 'Q#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'GNF' =>
			array(
				'NUM_CODE' => '324',
				'SYM_CODE' => 'GNF',
				'DEFAULT' => array(
					'FULL_NAME' => 'Guinean Franc',
					'FORMAT_STRING' => '&#8355;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'GYD' =>
			array(
				'NUM_CODE' => '328',
				'SYM_CODE' => 'GYD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Guyana Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'HTG' =>
			array(
				'NUM_CODE' => '332',
				'SYM_CODE' => 'HTG',
				'DEFAULT' => array(
					'FULL_NAME' => 'Gourde',
					'FORMAT_STRING' => 'G#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'HNL' =>
			array(
				'NUM_CODE' => '340',
				'SYM_CODE' => 'HNL',
				'DEFAULT' => array(
					'FULL_NAME' => 'Lempira',
					'FORMAT_STRING' => 'L#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'HKD' =>
			array(
				'NUM_CODE' => '344',
				'SYM_CODE' => 'HKD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Hong Kong Dollar',
					'FORMAT_STRING' => 'HK$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'HUF' =>
			array(
				'NUM_CODE' => '348',
				'SYM_CODE' => 'HUF',
				'DEFAULT' => array(
					'FULL_NAME' => 'Forint',
					'FORMAT_STRING' => '&#402;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'ISK' =>
			array(
				'NUM_CODE' => '352',
				'SYM_CODE' => 'ISK',
				'DEFAULT' => array(
					'FULL_NAME' => 'Iceland Krona',
					'FORMAT_STRING' => 'kr#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'INR' =>
			array(
				'NUM_CODE' => '356',
				'SYM_CODE' => 'INR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Indian Rupee',
					'FORMAT_STRING' => '&#8377;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'IDR' =>
			array(
				'NUM_CODE' => '360',
				'SYM_CODE' => 'IDR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Rupiah',
					'FORMAT_STRING' => '&#8377;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'IRR' =>
			array(
				'NUM_CODE' => '364',
				'SYM_CODE' => 'IRR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Iranian Rial',
					'FORMAT_STRING' => '&#65020;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'IQD' =>
			array(
				'NUM_CODE' => '368',
				'SYM_CODE' => 'IQD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Iraqi Dinar',
					'FORMAT_STRING' => 'ID#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 3,
				),
			),
		'ILS' =>
			array(
				'NUM_CODE' => '376',
				'SYM_CODE' => 'ILS',
				'DEFAULT' => array(
					'FULL_NAME' => 'New Israeli Sheqel',
					'FORMAT_STRING' => '&#8362;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'JMD' =>
			array(
				'NUM_CODE' => '388',
				'SYM_CODE' => 'JMD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Jamaican Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'JPY' =>
			array(
				'NUM_CODE' => '392',
				'SYM_CODE' => 'JPY',
				'DEFAULT' => array(
					'FULL_NAME' => 'Yen',
					'FORMAT_STRING' => '&#165;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'KZT' =>
			array(
				'NUM_CODE' => '398',
				'SYM_CODE' => 'KZT',
				'DEFAULT' => array(
					'FULL_NAME' => 'Tenge',
					'FORMAT_STRING' => '&#8376;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'JOD' =>
			array(
				'NUM_CODE' => '400',
				'SYM_CODE' => 'JOD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Jordanian Dinar',
					'FORMAT_STRING' => 'JD#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 3,
				),
			),
		'KES' =>
			array(
				'NUM_CODE' => '404',
				'SYM_CODE' => 'KES',
				'DEFAULT' => array(
					'FULL_NAME' => 'Kenyan Shilling',
					'FORMAT_STRING' => 'KShs#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'KPW' =>
			array(
				'NUM_CODE' => '408',
				'SYM_CODE' => 'KPW',
				'DEFAULT' => array(
					'FULL_NAME' => 'North Korean Won',
					'FORMAT_STRING' => '&#8361;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'KRW' =>
			array(
				'NUM_CODE' => '410',
				'SYM_CODE' => 'KRW',
				'DEFAULT' => array(
					'FULL_NAME' => 'Won',
					'FORMAT_STRING' => '&#8361;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'KWD' =>
			array(
				'NUM_CODE' => '114',
				'SYM_CODE' => 'KWD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Kuwaiti Dinar',
					'FORMAT_STRING' => 'KD#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 3,
				),
			),
		'KGS' =>
			array(
				'NUM_CODE' => '417',
				'SYM_CODE' => 'KGS',
				'DEFAULT' => array(
					'FULL_NAME' => 'Som',
					'FORMAT_STRING' => 'c#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'LAK' =>
			array(
				'NUM_CODE' => '418',
				'SYM_CODE' => 'LAK',
				'DEFAULT' => array(
					'FULL_NAME' => 'Lao Kip',
					'FORMAT_STRING' => '&#8365;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'LBP' =>
			array(
				'NUM_CODE' => '422',
				'SYM_CODE' => 'LBP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Lebanese Pound',
					'FORMAT_STRING' => 'LBP#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'LSL' =>
			array(
				'NUM_CODE' => '426',
				'SYM_CODE' => 'LSL',
				'DEFAULT' => array(
					'FULL_NAME' => 'Loti',
					'FORMAT_STRING' => 'M#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'LRD' =>
			array(
				'NUM_CODE' => '430',
				'SYM_CODE' => 'LRD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Liberian Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'LYD' =>
			array(
				'NUM_CODE' => '434',
				'SYM_CODE' => 'LYD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Libyan Dinar',
					'FORMAT_STRING' => 'LD#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 3,
				),
			),
		'MOP' =>
			array(
				'NUM_CODE' => '446',
				'SYM_CODE' => 'MOP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Pataca',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'MWK' =>
			array(
				'NUM_CODE' => '454',
				'SYM_CODE' => 'MWK',
				'DEFAULT' => array(
					'FULL_NAME' => 'Malawi Kwacha',
					'FORMAT_STRING' => 'MK#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'MYR' =>
			array(
				'NUM_CODE' => '458',
				'SYM_CODE' => 'MYR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Malaysian Ringgit',
					'FORMAT_STRING' => 'RM#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'MVR' =>
			array(
				'NUM_CODE' => '462',
				'SYM_CODE' => 'MVR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Rufiyaa',
					'FORMAT_STRING' => 'Rf#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'MRO' =>
			array(
				'NUM_CODE' => '478',
				'SYM_CODE' => 'MRO',
				'DEFAULT' => array(
					'FULL_NAME' => 'Ouguiya',
					'FORMAT_STRING' => 'UM#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'MUR' =>
			array(
				'NUM_CODE' => '480',
				'SYM_CODE' => 'MUR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Mauritius Rupee',
					'FORMAT_STRING' => '&#8360;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'MXN' =>
			array(
				'NUM_CODE' => '484',
				'SYM_CODE' => 'MXN',
				'DEFAULT' => array(
					'FULL_NAME' => 'Mexican Peso',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'MNT' =>
			array(
				'NUM_CODE' => '496',
				'SYM_CODE' => 'MNT',
				'DEFAULT' => array(
					'FULL_NAME' => 'Tugrik',
					'FORMAT_STRING' => '&#8376;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'MDL' =>
			array(
				'NUM_CODE' => '498',
				'SYM_CODE' => 'MDL',
				'DEFAULT' => array(
					'FULL_NAME' => 'Moldovan Leu',
					'FORMAT_STRING' => 'L#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'MAD' =>
			array(
				'NUM_CODE' => '504',
				'SYM_CODE' => 'MAD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Moroccan Dirham',
					'FORMAT_STRING' => 'Dh#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'OMR' =>
			array(
				'NUM_CODE' => '512',
				'SYM_CODE' => 'OMR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Rial Omani',
					'FORMAT_STRING' => '&#65020;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 3,
				),
			),
		'NAD' =>
			array(
				'NUM_CODE' => '516',
				'SYM_CODE' => 'NAD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Namibia Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'NPR' =>
			array(
				'NUM_CODE' => '524',
				'SYM_CODE' => 'NPR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Nepalese Rupee',
					'FORMAT_STRING' => '&#8360;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'ANG' =>
			array(
				'NUM_CODE' => '532',
				'SYM_CODE' => 'ANG',
				'DEFAULT' => array(
					'FULL_NAME' => 'Netherlands Antillean Guilder',
					'FORMAT_STRING' => '&#402;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'AWG' =>
			array(
				'NUM_CODE' => '533',
				'SYM_CODE' => 'AWG',
				'DEFAULT' => array(
					'FULL_NAME' => 'Aruban Florin',
					'FORMAT_STRING' => '&#402;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'VUV' =>
			array(
				'NUM_CODE' => '548',
				'SYM_CODE' => 'VUV',
				'DEFAULT' => array(
					'FULL_NAME' => 'Vatu',
					'FORMAT_STRING' => 'Vt#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'NZD' =>
			array(
				'NUM_CODE' => '554',
				'SYM_CODE' => 'NZD',
				'DEFAULT' => array(
					'FULL_NAME' => 'New Zealand Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'NIO' =>
			array(
				'NUM_CODE' => '558',
				'SYM_CODE' => 'NIO',
				'DEFAULT' => array(
					'FULL_NAME' => 'Cordoba Oro',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'NGN' =>
			array(
				'NUM_CODE' => '566',
				'SYM_CODE' => 'NGN',
				'DEFAULT' => array(
					'FULL_NAME' => 'Naira',
					'FORMAT_STRING' => '&#8358;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'NOK' =>
			array(
				'NUM_CODE' => '578',
				'SYM_CODE' => 'NOK',
				'DEFAULT' => array(
					'FULL_NAME' => 'Norwegian Krone',
					'FORMAT_STRING' => 'kr#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'PKR' =>
			array(
				'NUM_CODE' => '586',
				'SYM_CODE' => 'PKR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Pakistan Rupee',
					'FORMAT_STRING' => '&#8360;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'PAB' =>
			array(
				'NUM_CODE' => '590',
				'SYM_CODE' => 'PAB',
				'DEFAULT' => array(
					'FULL_NAME' => 'Balboa',
					'FORMAT_STRING' => 'B#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'PGK' =>
			array(
				'NUM_CODE' => '598',
				'SYM_CODE' => 'PGK',
				'DEFAULT' => array(
					'FULL_NAME' => 'Kina',
					'FORMAT_STRING' => 'K#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'PYG' =>
			array(
				'NUM_CODE' => '600',
				'SYM_CODE' => 'PYG',
				'DEFAULT' => array(
					'FULL_NAME' => 'Guarani',
					'FORMAT_STRING' => '&#8370;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'PEN' =>
			array(
				'NUM_CODE' => '604',
				'SYM_CODE' => 'PEN',
				'DEFAULT' => array(
					'FULL_NAME' => 'Sol',
					'FORMAT_STRING' => 'PEN#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'PHP' =>
			array(
				'NUM_CODE' => '608',
				'SYM_CODE' => 'PHP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Philippine Peso',
					'FORMAT_STRING' => '&#8369;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'QAR' =>
			array(
				'NUM_CODE' => '634',
				'SYM_CODE' => 'QAR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Qatari Rial',
					'FORMAT_STRING' => '&#65020;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'RUB' =>
			array(
				'NUM_CODE' => '643',
				'SYM_CODE' => 'RUB',
				'DEFAULT' => array(
					'FULL_NAME' => 'Russian Ruble',
					'FORMAT_STRING' => '&#8381;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'RWF' =>
			array(
				'NUM_CODE' => '646',
				'SYM_CODE' => 'RWF',
				'DEFAULT' => array(
					'FULL_NAME' => 'Rwanda Franc',
					'FORMAT_STRING' => '&#8355;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'SHP' =>
			array(
				'NUM_CODE' => '654',
				'SYM_CODE' => 'SHP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Saint Helena Pound',
					'FORMAT_STRING' => '&pound;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'STD' =>
			array(
				'NUM_CODE' => '678',
				'SYM_CODE' => 'STD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Dobra',
					'FORMAT_STRING' => 'Db#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'SAR' =>
			array(
				'NUM_CODE' => '682',
				'SYM_CODE' => 'SAR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Saudi Riyal',
					'FORMAT_STRING' => '&#65020;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'SCR' =>
			array(
				'NUM_CODE' => '690',
				'SYM_CODE' => 'SCR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Seychelles Rupee',
					'FORMAT_STRING' => '&#8360;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'SLL' =>
			array(
				'NUM_CODE' => '694',
				'SYM_CODE' => 'SLL',
				'DEFAULT' => array(
					'FULL_NAME' => 'Leone',
					'FORMAT_STRING' => 'Le#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'SGD' =>
			array(
				'NUM_CODE' => '702',
				'SYM_CODE' => 'SGD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Singapore Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'VND' =>
			array(
				'NUM_CODE' => '704',
				'SYM_CODE' => 'VND',
				'DEFAULT' => array(
					'FULL_NAME' => 'Dong',
					'FORMAT_STRING' => '&#8363;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'SOS' =>
			array(
				'NUM_CODE' => '706',
				'SYM_CODE' => 'SOS',
				'DEFAULT' => array(
					'FULL_NAME' => 'Somali Shilling',
					'FORMAT_STRING' => 'So.#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'ZAR' =>
			array(
				'NUM_CODE' => '710',
				'SYM_CODE' => 'ZAR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Rand',
					'FORMAT_STRING' => 'R#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'SSP' =>
			array(
				'NUM_CODE' => '728',
				'SYM_CODE' => 'SSP',
				'DEFAULT' => array(
					'FULL_NAME' => 'South Sudanese Pound',
					'FORMAT_STRING' => 'SSP#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'SZL' =>
			array(
				'NUM_CODE' => '748',
				'SYM_CODE' => 'SZL',
				'DEFAULT' => array(
					'FULL_NAME' => 'Lilangeni',
					'FORMAT_STRING' => 'E#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'SEK' =>
			array(
				'NUM_CODE' => '752',
				'SYM_CODE' => 'SEK',
				'DEFAULT' => array(
					'FULL_NAME' => 'Swedish Krona',
					'FORMAT_STRING' => 'kr#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'CHF' =>
			array(
				'NUM_CODE' => '756',
				'SYM_CODE' => 'CHF',
				'DEFAULT' => array(
					'FULL_NAME' => 'Swiss Franc',
					'FORMAT_STRING' => '&#8355;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'SYP' =>
			array(
				'NUM_CODE' => '760',
				'SYM_CODE' => 'SYP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Syrian Pound',
					'FORMAT_STRING' => 'SP#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'THB' =>
			array(
				'NUM_CODE' => '764',
				'SYM_CODE' => 'THB',
				'DEFAULT' => array(
					'FULL_NAME' => 'Baht',
					'FORMAT_STRING' => '&#3647;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'TOP' =>
			array(
				'NUM_CODE' => '776',
				'SYM_CODE' => 'TOP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Paanga',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'TTD' =>
			array(
				'NUM_CODE' => '780',
				'SYM_CODE' => 'TTD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Trinidad and Tobago Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'AED' =>
			array(
				'NUM_CODE' => '784',
				'SYM_CODE' => 'AED',
				'DEFAULT' => array(
					'FULL_NAME' => 'UAE Dirham',
					'FORMAT_STRING' => 'Dh#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'TND' =>
			array(
				'NUM_CODE' => '788',
				'SYM_CODE' => 'TND',
				'DEFAULT' => array(
					'FULL_NAME' => 'Tunisian Dinar',
					'FORMAT_STRING' => 'TD#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 3,
				),
			),
		'UGX' =>
			array(
				'NUM_CODE' => '800',
				'SYM_CODE' => 'UGX',
				'DEFAULT' => array(
					'FULL_NAME' => 'Uganda Shilling',
					'FORMAT_STRING' => 'USh#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'MKD' =>
			array(
				'NUM_CODE' => '807',
				'SYM_CODE' => 'MKD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Denar',
					'FORMAT_STRING' => 'MDen#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'EGP' =>
			array(
				'NUM_CODE' => '818',
				'SYM_CODE' => 'EGP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Egyptian Pound',
					'FORMAT_STRING' => 'LE#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'GBP' =>
			array(
				'NUM_CODE' => '826',
				'SYM_CODE' => 'GBP',
				'DEFAULT' => array(
					'FULL_NAME' => 'Pound Sterling',
					'FORMAT_STRING' => '&pound;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'TZS' =>
			array(
				'NUM_CODE' => '834',
				'SYM_CODE' => 'TZS',
				'DEFAULT' => array(
					'FULL_NAME' => 'Tanzanian Shilling',
					'FORMAT_STRING' => 'TSh#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'USD' =>
			array(
				'NUM_CODE' => '840',
				'SYM_CODE' => 'USD',
				'DEFAULT' => array(
					'FULL_NAME' => 'US Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'UYU' =>
			array(
				'NUM_CODE' => '858',
				'SYM_CODE' => 'UYU',
				'DEFAULT' => array(
					'FULL_NAME' => 'Peso Uruguayo',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'UZS' =>
			array(
				'NUM_CODE' => '860',
				'SYM_CODE' => 'UZS',
				'DEFAULT' => array(
					'FULL_NAME' => 'Uzbekistan Sum',
					'FORMAT_STRING' => 'UZS#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'WST' =>
			array(
				'NUM_CODE' => '882',
				'SYM_CODE' => 'WST',
				'DEFAULT' => array(
					'FULL_NAME' => 'Tala',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'YER' =>
			array(
				'NUM_CODE' => '886',
				'SYM_CODE' => 'YER',
				'DEFAULT' => array(
					'FULL_NAME' => 'Yemeni Rial',
					'FORMAT_STRING' => '&#65020;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'TWD' =>
			array(
				'NUM_CODE' => '901',
				'SYM_CODE' => 'TWD',
				'DEFAULT' => array(
					'FULL_NAME' => 'New Taiwan Dollar',
					'FORMAT_STRING' => 'NT$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'CUC' =>
			array(
				'NUM_CODE' => '931',
				'SYM_CODE' => 'CUC',
				'DEFAULT' => array(
					'FULL_NAME' => 'Peso Convertible',
					'FORMAT_STRING' => 'CUC#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'ZWL' =>
			array(
				'NUM_CODE' => '932',
				'SYM_CODE' => 'ZWL',
				'DEFAULT' => array(
					'FULL_NAME' => 'Zimbabwe Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BYN' =>
			array(
				'NUM_CODE' => '933',
				'SYM_CODE' => 'BYN',
				'DEFAULT' => array(
					'FULL_NAME' => 'Belarusian Ruble',
					'FORMAT_STRING' => 'Br#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'TMT' =>
			array(
				'NUM_CODE' => '934',
				'SYM_CODE' => 'TMT',
				'DEFAULT' => array(
					'FULL_NAME' => 'Turkmenistan New Manat',
					'FORMAT_STRING' => 'm#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'GHS' =>
			array(
				'NUM_CODE' => '936',
				'SYM_CODE' => 'GHS',
				'DEFAULT' => array(
					'FULL_NAME' => 'Ghana Cedi',
					'FORMAT_STRING' => '&#8373;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'VEF' =>
			array(
				'NUM_CODE' => '937',
				'SYM_CODE' => 'VEF',
				'DEFAULT' => array(
					'FULL_NAME' => 'Bolivar',
					'FORMAT_STRING' => 'Bs#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'SDG' =>
			array(
				'NUM_CODE' => '938',
				'SYM_CODE' => 'SDG',
				'DEFAULT' => array(
					'FULL_NAME' => 'Sudanese Pound',
					'FORMAT_STRING' => '&pound;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'UYI' =>
			array(
				'NUM_CODE' => '940',
				'SYM_CODE' => 'UYI',
				'DEFAULT' => array(
					'FULL_NAME' => 'Uruguay Peso en Unidades Indexadas (URUIURUI)',
					'FORMAT_STRING' => 'UYI#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'RSD' =>
			array(
				'NUM_CODE' => '941',
				'SYM_CODE' => 'RSD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Serbian Dinar',
					'FORMAT_STRING' => 'din.#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'MZN' =>
			array(
				'NUM_CODE' => '943',
				'SYM_CODE' => 'MZN',
				'DEFAULT' => array(
					'FULL_NAME' => 'Mozambique Metical',
					'FORMAT_STRING' => 'MT#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'AZN' =>
			array(
				'NUM_CODE' => '944',
				'SYM_CODE' => 'AZN',
				'DEFAULT' => array(
					'FULL_NAME' => 'Azerbaijan Manat',
					'FORMAT_STRING' => '&#8380;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'RON' =>
			array(
				'NUM_CODE' => '946',
				'SYM_CODE' => 'RON',
				'DEFAULT' => array(
					'FULL_NAME' => 'Romanian Leu',
					'FORMAT_STRING' => 'L#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'CHE' =>
			array(
				'NUM_CODE' => '947',
				'SYM_CODE' => 'CHE',
				'DEFAULT' => array(
					'FULL_NAME' => 'WIR Euro',
					'FORMAT_STRING' => 'CHE#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'CHW' =>
			array(
				'NUM_CODE' => '948',
				'SYM_CODE' => 'CHW',
				'DEFAULT' => array(
					'FULL_NAME' => 'WIR Franc',
					'FORMAT_STRING' => 'CHW#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'TRY' =>
			array(
				'NUM_CODE' => '949',
				'SYM_CODE' => 'TRY',
				'DEFAULT' => array(
					'FULL_NAME' => 'Turkish Lira',
					'FORMAT_STRING' => '&#8378;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'XAF' =>
			array(
				'NUM_CODE' => '950',
				'SYM_CODE' => 'XAF',
				'DEFAULT' => array(
					'FULL_NAME' => 'CFA Franc BEAC',
					'FORMAT_STRING' => '&#8355;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'XCD' =>
			array(
				'NUM_CODE' => '951',
				'SYM_CODE' => 'XCD',
				'DEFAULT' => array(
					'FULL_NAME' => 'East Caribbean Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'XOF' =>
			array(
				'NUM_CODE' => '952',
				'SYM_CODE' => 'XOF',
				'DEFAULT' => array(
					'FULL_NAME' => 'CFA Franc BCEAO',
					'FORMAT_STRING' => '&#8355;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'XPF' =>
			array(
				'NUM_CODE' => '953',
				'SYM_CODE' => 'XPF',
				'DEFAULT' => array(
					'FULL_NAME' => 'CFP Franc',
					'FORMAT_STRING' => '&#8355;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'XBA' =>
			array(
				'NUM_CODE' => '955',
				'SYM_CODE' => 'XBA',
				'DEFAULT' => array(
					'FULL_NAME' => 'Bond Markets Unit European Composite Unit (EURCO)',
					'FORMAT_STRING' => 'XBA#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'XBB' =>
			array(
				'NUM_CODE' => '956',
				'SYM_CODE' => 'XBB',
				'DEFAULT' => array(
					'FULL_NAME' => 'Bond Markets Unit European Monetary Unit (E.M.U.-6)',
					'FORMAT_STRING' => 'XBB#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'XBC' =>
			array(
				'NUM_CODE' => '957',
				'SYM_CODE' => 'XBC',
				'DEFAULT' => array(
					'FULL_NAME' => 'Bond Markets Unit European Unit of Account 9 (E.U.A.-9)',
					'FORMAT_STRING' => 'XBC#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'XBD' =>
			array(
				'NUM_CODE' => '958',
				'SYM_CODE' => 'XBD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Bond Markets Unit European Unit of Account 17 (E.U.A.-17)',
					'FORMAT_STRING' => 'XBD#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'XAU' =>
			array(
				'NUM_CODE' => '959',
				'SYM_CODE' => 'XAU',
				'DEFAULT' => array(
					'FULL_NAME' => 'Gold',
					'FORMAT_STRING' => 'XAU#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'XDR' =>
			array(
				'NUM_CODE' => '960',
				'SYM_CODE' => 'XDR',
				'DEFAULT' => array(
					'FULL_NAME' => 'SDR (Special Drawing Right)',
					'FORMAT_STRING' => 'SDR#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'XAG' =>
			array(
				'NUM_CODE' => '961',
				'SYM_CODE' => 'XAG',
				'DEFAULT' => array(
					'FULL_NAME' => 'Silver',
					'FORMAT_STRING' => 'XAG#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'XPT' =>
			array(
				'NUM_CODE' => '962',
				'SYM_CODE' => 'XPT',
				'DEFAULT' => array(
					'FULL_NAME' => 'Platinum',
					'FORMAT_STRING' => 'XPT#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'XPD' =>
			array(
				'NUM_CODE' => '964',
				'SYM_CODE' => 'XPD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Palladium',
					'FORMAT_STRING' => 'XPD#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'XUA' =>
			array(
				'NUM_CODE' => '965',
				'SYM_CODE' => 'XUA',
				'DEFAULT' => array(
					'FULL_NAME' => 'ADB Unit of Account',
					'FORMAT_STRING' => 'XUA#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'ZMW' =>
			array(
				'NUM_CODE' => '967',
				'SYM_CODE' => 'ZMW',
				'DEFAULT' => array(
					'FULL_NAME' => 'Zambian Kwacha',
					'FORMAT_STRING' => 'K#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'SRD' =>
			array(
				'NUM_CODE' => '968',
				'SYM_CODE' => 'SRD',
				'DEFAULT' => array(
					'FULL_NAME' => 'Surinam Dollar',
					'FORMAT_STRING' => '$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'MGA' =>
			array(
				'NUM_CODE' => '969',
				'SYM_CODE' => 'MGA',
				'DEFAULT' => array(
					'FULL_NAME' => 'Malagasy Ariary',
					'FORMAT_STRING' => 'Ar.#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'COU' =>
			array(
				'NUM_CODE' => '970',
				'SYM_CODE' => 'COU',
				'DEFAULT' => array(
					'FULL_NAME' => 'Unidad de Valor Real',
					'FORMAT_STRING' => 'COU#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'AFN' =>
			array(
				'NUM_CODE' => '971',
				'SYM_CODE' => 'AFN',
				'DEFAULT' => array(
					'FULL_NAME' => 'Afghani',
					'FORMAT_STRING' => '&#1547;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'TJS' =>
			array(
				'NUM_CODE' => '972',
				'SYM_CODE' => 'TJS',
				'DEFAULT' => array(
					'FULL_NAME' => 'Somoni',
					'FORMAT_STRING' => 'c.#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'AOA' =>
			array(
				'NUM_CODE' => '973',
				'SYM_CODE' => 'AOA',
				'DEFAULT' => array(
					'FULL_NAME' => 'Kwanza',
					'FORMAT_STRING' => 'Kz#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BGN' =>
			array(
				'NUM_CODE' => '975',
				'SYM_CODE' => 'BGN',
				'DEFAULT' => array(
					'FULL_NAME' => 'Bulgarian Lev',
					'FORMAT_STRING' => 'BGN#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'CDF' =>
			array(
				'NUM_CODE' => '976',
				'SYM_CODE' => 'CDF',
				'DEFAULT' => array(
					'FULL_NAME' => 'Congolese Franc',
					'FORMAT_STRING' => '&#8355;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BAM' =>
			array(
				'NUM_CODE' => '977',
				'SYM_CODE' => 'BAM',
				'DEFAULT' => array(
					'FULL_NAME' => 'Convertible Mark',
					'FORMAT_STRING' => 'KM#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'EUR' =>
			array(
				'NUM_CODE' => '978',
				'SYM_CODE' => 'EUR',
				'DEFAULT' => array(
					'FULL_NAME' => 'Euro',
					'FORMAT_STRING' => '&euro;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'MXV' =>
			array(
				'NUM_CODE' => '979',
				'SYM_CODE' => 'MXV',
				'DEFAULT' => array(
					'FULL_NAME' => 'Mexican Unidad de Inversion (UDI)',
					'FORMAT_STRING' => 'MXV#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'UAH' =>
			array(
				'NUM_CODE' => '980',
				'SYM_CODE' => 'UAH',
				'DEFAULT' => array(
					'FULL_NAME' => 'Hryvnia',
					'FORMAT_STRING' => '&#8372;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'GEL' =>
			array(
				'NUM_CODE' => '981',
				'SYM_CODE' => 'GEL',
				'DEFAULT' => array(
					'FULL_NAME' => 'Lari',
					'FORMAT_STRING' => '&#8382;#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BOV' =>
			array(
				'NUM_CODE' => '984',
				'SYM_CODE' => 'BOV',
				'DEFAULT' => array(
					'FULL_NAME' => 'Mvdol',
					'FORMAT_STRING' => 'BOV#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'PLN' =>
			array(
				'NUM_CODE' => '985',
				'SYM_CODE' => 'PLN',
				'DEFAULT' => array(
					'FULL_NAME' => 'Zloty',
					'FORMAT_STRING' => '#VALUE# z&#322;',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'BRL' =>
			array(
				'NUM_CODE' => '986',
				'SYM_CODE' => 'BRL',
				'DEFAULT' => array(
					'FULL_NAME' => 'Brazilian Real',
					'FORMAT_STRING' => 'R$#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			),
		'CLF' =>
			array(
				'NUM_CODE' => '990',
				'SYM_CODE' => 'CLF',
				'DEFAULT' => array(
					'FULL_NAME' => 'Unidad de Fomento',
					'FORMAT_STRING' => 'CLF#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 4,
				),
			),
		'XSU' =>
			array(
				'NUM_CODE' => '994',
				'SYM_CODE' => 'XSU',
				'DEFAULT' => array(
					'FULL_NAME' => 'Sucre',
					'FORMAT_STRING' => 'XSU#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 0,
				),
			),
		'USN' =>
			array(
				'NUM_CODE' => '997',
				'SYM_CODE' => 'USN',
				'DEFAULT' => array(
					'FULL_NAME' => 'US Dollar (Next day)',
					'FORMAT_STRING' => 'USN#VALUE#',
					'DEC_POINT' => '.',
					'THOUSANDS_VARIANT' => self::SEPARATOR_COMMA,
					'DECIMALS' => 2,
				),
			)
	);
}