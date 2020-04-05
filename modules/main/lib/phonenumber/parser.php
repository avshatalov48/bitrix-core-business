<?php

namespace Bitrix\Main\PhoneNumber;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

class Parser
{
	const MAX_LENGTH_COUNTRY_CODE = 3; // The maximum length of the country calling code.
	const MIN_LENGTH_FOR_NSN = 2; // The minimum length of the national significant number.
	const MAX_LENGTH_FOR_NSN = 17; // The ITU says the maximum length should be 15, but one can find longer numbers in Germany.

	/* We don't allow input strings for parsing to be longer than 250 chars. This prevents malicious input from consuming CPU.*/
	const MAX_INPUT_STRING_LENGTH = 250;

	protected $plusChar = '+';

	/* Digits accepted in phone numbers (ascii, fullwidth, arabic-indic, and eastern arabic digits). */
	protected $validDigits = '0-9';
	protected $dashes = '-';
	protected $slashes = '\/';
	protected $dot = '.';
	protected $whitespace = '\s';
	protected $brackets = '()\\[\\]';
	protected $tildes = '~';
	protected $extensionSeparators = ';#';
	protected $extensionSymbols = ',';

	protected $phoneNumberStartPattern;
	protected $afterPhoneNumberEndPattern;
	protected $minLengthPhoneNumberPattern;
	protected $validPunctuation;
	protected $validPhoneNumber;
	protected $validPhoneNumberPattern;

	const DEFAULT_COUNTRY_OPTION = 'phone_number_default_country';

	/** @var static */
	protected static $instance = null;

	/**
	 * This class is a singleton and should not be constructed directly.
	 * @see HtmlParser::getInstance
	 */
	protected function __construct()
	{
		$this->phoneNumberStartPattern = '[' . $this->plusChar . $this->validDigits . ']';
		$this->afterPhoneNumberEndPattern = '[^' . $this->validDigits . $this->extensionSeparators . $this->extensionSymbols . ']+$';
		$this->minLengthPhoneNumberPattern = '[' . $this->validDigits . ']{' . static::MIN_LENGTH_FOR_NSN . '}';
		$this->validPunctuation = $this->dashes . $this->slashes . $this->dot . $this->whitespace . $this->brackets . $this->tildes . $this->extensionSeparators . $this->extensionSymbols;
		$this->validPhoneNumber =
			'[' . $this->plusChar . ']{0,1}' .
			'(?:' .
				'[' . $this->validPunctuation . ']*' .
				'[' . $this->validDigits . ']' .
			'){3,}' .
			'[' .
				$this->validPunctuation .
				$this->validDigits .
			']*';

		$this->validPhoneNumberPattern =
			'^(?:'.
				// Either a short two-digit-only phone number
				'^' . $this->minLengthPhoneNumberPattern .'$' .
				// Or a longer fully parsed phone number (min 3 characters)
				'|' . '^' . $this->validPhoneNumber . '$' .
			')$';

	}

	/**
	 * Returns instance of Parser.
	 * @return Parser
	 */
	public static function getInstance()
	{
		if(is_null(static::$instance))
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Returns two-letter default country code, used for phone number parsing.
	 * @return string
	 */
	public static function getDefaultCountry()
	{
		$defaultCountryId = Option::get('main', static::DEFAULT_COUNTRY_OPTION);

		if(!$defaultCountryId)
		{
			$detectedCountry = static::detectCountry();
			$detectedCountryId = GetCountryIdByCode($detectedCountry);
			if($detectedCountryId > 0)
			{
				Option::set('main', static::DEFAULT_COUNTRY_OPTION, $detectedCountryId);
				$defaultCountryId = $detectedCountryId;
			}
		}

		return $defaultCountryId ? GetCountryCodeById($defaultCountryId) : "";
	}

	public static function getUserDefaultCountry()
	{
		$userSettings = \CUserOptions::GetOption('main', 'phone_number');
		return isset($userSettings['default_country']) ? $userSettings['default_country'] : '';
	}

	/**
	 * Tries to detect default country for parsing,
	 * @return string
	 */
	public static function detectCountry()
	{
		if(Loader::includeModule('bitrix24'))
		{
			$defaultCountry = Option::get("bitrix24", "REG_COUNTRY", "");

			if(!$defaultCountry)
			{
				$portalZone = \CBitrix24::getPortalZone();

				if(in_array($portalZone, array('br', 'cn', 'de', 'in', 'ru', 'ua', 'by', 'kz', 'fr', 'pl')))
				{
					$defaultCountry = $portalZone;
				}
			}
		}

		if(!$defaultCountry)
		{
			$currentLanguage = Context::getCurrent()->getLanguage();
			if(in_array($currentLanguage, array('br', 'cn', 'de', 'in', 'ru', 'ua', 'by', 'kz', 'fr', 'pl')))
			{
				$defaultCountry = $currentLanguage;
			}
		}

		if(!$defaultCountry)
		{
			// last hope, let's try geoip
			$defaultCountry = \Bitrix\Main\Service\GeoIp\Manager::getCountryCode();
		}

		return strtoupper($defaultCountry);
	}

	/**
	 * Return pattern string suitable to detect phone number in some string.
	 * @return string
	 */
	public function getValidNumberPattern()
	{
		return $this->validPhoneNumber;
	}

	/**
	 * Parses provided phone number.
	 * @param string $phoneNumber Phone number to be parsed.
	 * @param string $defaultCountry 2-symbol country code.
	 * @return PhoneNumber
	 */
	public function parse($phoneNumber, $defaultCountry = '')
	{
		if($defaultCountry == '')
		{
			$defaultCountry = static::getDefaultCountry();
		}
		$result = new PhoneNumber();
		$result->setRawNumber($phoneNumber);

		if(!$this->isViablePhoneNumber($phoneNumber))
		{
			return $result;
		}
		$formattedPhoneNumber = $this->extractFormattedPhoneNumber($phoneNumber);

		list($extensionSeparator, $extension) = $this->stripExtension($formattedPhoneNumber);
		$result->setNationalNumber($formattedPhoneNumber);
		$result->setExtensionSeparator($extensionSeparator);
		$result->setExtension($extension);

		$parseResult = $this->parsePhoneNumberAndCountryPhoneCode($formattedPhoneNumber);
		if($parseResult === false)
		{
			return $result;
		}

		$countryCode = $parseResult['countryCode'];
		$localNumber = $parseResult['localNumber'];
		$hasPlus = false;

		if($countryCode)
		{
			// Number in international format, starting with '+', thus we ignore $country parameter
			$isInternational = true;
			$hasPlus = true;
			$countryMetadata = $this->getMetadataByCountryCode($countryCode);

			/*
				$country will be set later, because, for example, for NANPA countries
				there are several countries corresponding to the same `1` country phone code.
			 	Therefore, to reliably determine the exact country, national number should be parsed first.
			*/
			$country = null;
		}
		else
		{
			// Number in national format or in international format without + sign.
			$country = $defaultCountry;
			$countryMetadata = $this->getCountryMetadata($country);
			$countryCode = $countryMetadata['countryCode'];
			$isInternational = $this->stripCountryCode($localNumber, $countryMetadata);
		}

		if(!$countryMetadata)
		{
			return $result;
		}

		$nationalPrefix = $this->stripNationalPrefix($localNumber, $countryMetadata);

		// Sometimes there are several countries corresponding to the same country phone code (e.g. NANPA countries all
		// having `1` country phone code). Therefore, to reliably determine the exact country, national (significant)
		// number should have been parsed first.
		if(!$country)
		{
			$country = $this->findCountry($countryCode, $localNumber);
			if(!$country)
			{
				return $result;
			}

			$countryMetadata = $this->getCountryMetadata($country);
		}

		// Validate local (significant) number length
		if(strlen($localNumber) > static::MAX_LENGTH_FOR_NSN)
		{
			return $result;
		}

		$nationalNumberRegex = '/^(?:' . $countryMetadata['generalDesc']['nationalNumberPattern'] . ')$/';
		if(!preg_match($nationalNumberRegex, $localNumber))
		{
			return $result;
		}

		$numberType = $this->getNumberType($localNumber, $country);
		$result->setHasPlus($hasPlus);
		$result->setCountry($country);
		$result->setCountryCode($countryCode);
		$result->setNationalNumber($localNumber);
		$result->setNumberType($numberType);
		$result->setInternational($isInternational);
		$result->setNationalPrefix($nationalPrefix);
		$result->setValid($numberType !== false);

		return $result;
	}

	/**
	 * Strips and returns extension and extension separator from the specified phone number.
	 * @param string $phoneNumber Phone number to be stripped.
	 * @return [$extenstionSeparator, $extension]
	 */
	public function stripExtension(&$phoneNumber)
	{
		$extension = "";
		$extensionSeparator = "";

		if(preg_match("/[" . $this->extensionSeparators ."]/", $phoneNumber, $matches, PREG_OFFSET_CAPTURE))
		{
			$extensionSeparator = $matches[0][0];
			$separatorPosition = $matches[0][1];
			$extension = substr($phoneNumber, $separatorPosition + 1);
			$phoneNumber = substr($phoneNumber, 0, $separatorPosition);
		}
		return [$extensionSeparator, $extension];
	}

	/**
	 * Extracts phone number from the input string.
	 * @param string $phoneNumber Phone number.
	 * @return string
	 */
	protected function extractFormattedPhoneNumber($phoneNumber)
	{
		if (!$phoneNumber || strlen($phoneNumber) > static::MAX_INPUT_STRING_LENGTH)
		{
			return '';
		}

		if(!preg_match('/'.$this->phoneNumberStartPattern.'/', $phoneNumber, $matches, PREG_OFFSET_CAPTURE))
		{
			return '';
		}

		// Attempt to extract a possible number from the string passed in
		$startsAt = $matches[0][1];
		if ($startsAt < 0)
		{
			return '';
		}

		$result = substr($phoneNumber, $startsAt);
		$result = preg_replace('/'.$this->afterPhoneNumberEndPattern.'/', '', $result);
		return $result;
	}

	/**
	 * Returns true if the specified string matches general phone number pattern.
	 * @param string $phoneNumber Phone number.
	 * @return bool
	 */
	protected function isViablePhoneNumber($phoneNumber)
	{
		return strlen($phoneNumber) >= static::MIN_LENGTH_FOR_NSN && preg_match('/'.$this->validPhoneNumberPattern.'/i', $phoneNumber);
	}

	/**
	 * Returns country code and local number for the provided international phone number.
	 * @param string $phoneNumber Phone number in international format.
	 * @return array|false
	 */
	protected function parsePhoneNumberAndCountryPhoneCode($phoneNumber)
	{
		$phoneNumber = $this->normalizePhoneNumber($phoneNumber);
		if(!$phoneNumber)
			return false;

		// If this is not an international phone number,
		// then don't extract country phone code.
		if ($phoneNumber[0] !== $this->plusChar)
		{
			return array(
				'countryCode' => '',
				'localNumber' => $phoneNumber
			);
		}

		// Strip the leading '+' sign
		$phoneNumber = substr($phoneNumber, 1);

		// Fast abortion: country codes do not begin with a '0'
		if ($phoneNumber[0] === '0')
		{
			return false;
		}

		for ($i = static::MAX_LENGTH_COUNTRY_CODE; $i > 0; $i--)
		{
			$countryCode = substr($phoneNumber, 0, $i);
			if(MetadataProvider::getInstance()->isValidCountryCode($countryCode))
			{
				return array(
					'countryCode' => $countryCode,
					'localNumber' => substr($phoneNumber, $i)
				);
			}
		}
		return false;
	}

	/**
	 * Strips letters from the phone number, except for the leading plus character.
	 * @param string $phoneNumber Phone number.
	 * @return string
	 */
	protected function normalizePhoneNumber($phoneNumber)
	{
		if (!$phoneNumber)
			return '';

		$isInternational = substr($phoneNumber, 0, 1) === $this->plusChar;

		// Remove non-digits (and strip the possible leading '+')
		$phoneNumber = static::stripLetters($phoneNumber);

		if ($isInternational)
			return $this->plusChar . $phoneNumber;
		else
			return $phoneNumber;
	}

	/**
	 * Returns metadata for the first country with specified $countryCode.
	 * @param string $countryCode Phone code of the country
	 * @return array | false
	 */
	protected function getMetadataByCountryCode($countryCode)
	{
		if(!MetadataProvider::getInstance()->isValidCountryCode($countryCode))
		{
			return false;
		}

		$countries = MetadataProvider::getInstance()->getCountriesByCode($countryCode);
		return $this->getCountryMetadata($countries[0]);
	}

	/**
	 * Returns 2-symbol country code by localNumber.
	 * @param string $countryCode Phone code of the country.
	 * @param string $localNumber Local phone number.
	 * @return string|false
	 */
	protected function findCountry($countryCode, $localNumber)
	{
		if(!$countryCode || !$localNumber)
			return false;

		$possibleCountries = MetadataProvider::getInstance()->getCountriesByCode($countryCode);
		if(count($possibleCountries) === 1)
		{
			return $possibleCountries[0];
		}

		foreach($possibleCountries as $possibleCountry)
		{
			$countryMetadata = $this->getCountryMetadata($possibleCountry);

			// Check leading digits first
			if(isset($countryMetadata['leadingDigits']))
			{
				$leadingDigitsRegex = '/^'.$countryMetadata['leadingDigits'].'/';
				if(preg_match($leadingDigitsRegex, $localNumber))
				{
					return $possibleCountry;
				}
			}
			// Else perform full validation with all of those bulky fixed-line/mobile/etc regular expressions.
			else if($this->getNumberType($localNumber, $possibleCountry))
			{
				return $possibleCountry;
			}
		}

		return false;
	}

	/**
	 * Returns type of the specified number.
	 * @param string $localNumber Local phone number.
	 * @param string $country 2-symbol country code.
	 * @return string|false
	 */
	protected function getNumberType($localNumber, $country)
	{
		// Check that the number is valid for this country
		$countryMetadata = $this->getCountryMetadata($country);
		if(!$countryMetadata)
			return false;

		if(isset($countryMetadata['generalDesc']['nationalNumberPattern']))
		{
			$nationalNumberRegex = '/^(?:' . $countryMetadata['generalDesc']['nationalNumberPattern'] . ')$/';
			if(!preg_match($nationalNumberRegex, $localNumber))
				return false;
		}

		$possibleTypes = array('noInternationalDialling', 'areaCodeOptional', 'fixedLine', 'mobile', 'pager', 'tollFree', 'premiumRate', 'sharedCost', 'personalNumber', 'voip', 'uan', 'voicemail');
		foreach ($possibleTypes as $possibleType)
		{
			if(isset($countryMetadata[$possibleType]['nationalNumberPattern']))
			{
				// skip checking possible lengths for now

				$numberTypeRegex = '/^' . $countryMetadata[$possibleType]['nationalNumberPattern'] . '$/';
				if(preg_match($numberTypeRegex, $localNumber))
				{
					return $possibleType;
				}
			}
		}
		return false;
 	}

	/**
	 * Strips national prefix from the specified phone number. Returns true if national prefix
	 * was stripped and false otherwise.
	 * @param string $phoneNumber Local phone number.
	 * @param array $countryMetadata Country metadata.
	 * @return string
	 */
	protected static function stripNationalPrefix(&$phoneNumber, $countryMetadata)
	{
		$nationalPrefixForParsing = isset($countryMetadata['nationalPrefixForParsing']) ? $countryMetadata['nationalPrefixForParsing']: $countryMetadata['nationalPrefix'];

		if($phoneNumber == '' || $nationalPrefixForParsing == '')
			return '';

		$nationalPrefixRegex = '/^(?:' . $nationalPrefixForParsing . ')/';
		if(!preg_match($nationalPrefixRegex, $phoneNumber, $nationalPrefixMatches))
		{
			//if national prefix is omitted, nothing to strip
			return '';
		}

		$nationalPrefixTransformRule = $countryMetadata['nationalPrefixTransformRule'];
		if($nationalPrefixTransformRule && count($nationalPrefixMatches) > 1)
		{
			$nationalSignificantNumber = preg_replace($nationalPrefixRegex, $nationalPrefixTransformRule, $phoneNumber);
		}
		else
		{
			// No transformation is required, just strip the prefix
			$nationalSignificantNumber = substr($phoneNumber,strlen($nationalPrefixMatches[0]));
		}
		$nationalPrefix = substr($phoneNumber, 0, strlen($phoneNumber) - strlen($nationalSignificantNumber));

		$nationalNumberRegex = '/^(?:' . $countryMetadata['generalDesc']['nationalNumberPattern'] . ')$/';
		if(preg_match($nationalNumberRegex, $phoneNumber) && !preg_match($nationalNumberRegex, $nationalSignificantNumber))
		{
			/*
			   If the original number (before stripping national prefix) was viable, and the resultant number is not,
			   then prefer the original phone number. This is because for some countries (e.g. Russia) the same digit
			   could be both a national prefix and a leading digit of a valid national phone number, like `8` is the
			   national prefix for Russia and both `8 800 555 35 35` and `800 555 35 35` are valid numbers.
			*/
			return '';
		}

		$phoneNumber = $nationalSignificantNumber;
		return $nationalPrefix;
	}

	/**
	 * Strips country code from the number. Returns true if country code was stripped or false otherwise.
	 * @param string $phoneNumber Phone number.
	 * @param array $countryMetadata Country metadata.
	 * @return bool
	 */
	protected static function stripCountryCode(&$phoneNumber, $countryMetadata)
	{
		$countryCode = $countryMetadata['countryCode'];
		if(strpos($phoneNumber, $countryCode) !== 0)
			return false;

		$possibleLocalNumber = substr($phoneNumber, strlen($countryCode));
		$nationalNumberRegex = '/^(?:' . $countryMetadata['generalDesc']['nationalNumberPattern'] . ')$/';

		if(!preg_match($nationalNumberRegex, $phoneNumber) && preg_match($nationalNumberRegex, $possibleLocalNumber))
		{
			/*
			   If the original number (before stripping national prefix) was viable, and the resultant number is not,
			   then prefer the original phone number. This is because for some countries (e.g. Russia) the same digit
			   could be both a national prefix and a leading digit of a valid national phone number, like `8` is the
			   national prefix for Russia and both `8 800 555 35 35` and `800 555 35 35` are valid numbers.
			*/
			$phoneNumber = $possibleLocalNumber;
			return true;
		}

		return false;
	}

	protected function getCountriesByCode($countryCode)
	{
		return MetadataProvider::getInstance()->getCountriesByCode($countryCode);
	}

	protected function getCountryMetadata($country)
	{
		return MetadataProvider::getInstance()->getCountryMetadata($country);
	}

	/**
	 * Strips all letters from the string.
	 * @param string $str Input string.
	 * @return string
	 */
	protected static function stripLetters($str)
	{
		return preg_replace("/[^\d]/", "", $str);
	}
}