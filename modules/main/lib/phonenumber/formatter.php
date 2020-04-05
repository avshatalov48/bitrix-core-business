<?php

namespace Bitrix\Main\PhoneNumber;

class Formatter
{
	public static function format(PhoneNumber $number, $formatType, $forceNationalPrefix = false)
	{
		if(!$number->isValid())
		{
			return $number->getRawNumber();
		}
		if($formatType === Format::E164)
		{
			return '+' . $number->getCountryCode() . $number->getNationalNumber();
		}

		$countryMetadata = MetadataProvider::getInstance()->getCountryMetadata($number->getCountry());
		$format = static::selectFormatForNumber($number, $formatType, $countryMetadata);

		if($format)
		{
			$formattedNationalNumber = static::formatNationalNumber(
				$number->getNationalNumber(),
				$formatType,
				$countryMetadata,
				$format,
				$forceNationalPrefix
			);
		}
		else
		{
			$formattedNationalNumber = $number->getNationalNumber();
		}

		if($number->hasExtension())
		{
			$formattedNationalNumber .= $number->getExtensionSeparator() ." ". $number->getExtension();
		}

		if($formatType == Format::INTERNATIONAL)
		{
			return '+' . $number->getCountryCode() . ' ' . $formattedNationalNumber;
		}
		else if($formatType == Format::NATIONAL)
		{
			return $formattedNationalNumber;
		}

		return $number->getRawNumber();
	}

	/**
	 * Formats number using original format. No digit or significant char should be added or removed.
	 * @param PhoneNumber $number Phone number.
	 * @return string
	 */
	public static function formatOriginal(PhoneNumber $number)
	{
		if(!$number->isValid())
			return $number->getRawNumber();

		$format = static::selectOriginalFormatForNumber($number);
		if(!$format)
			return $number->getRawNumber();

		$formattedNationalNumber = static::formatNationalNumberWithOriginalFormat(
			$number,
			$format
		);

		if($number->hasExtension())
		{
			$formattedNationalNumber .= $number->getExtensionSeparator() . " " . $number->getExtension();
		}

		if($number->isInternational())
		{
			return ($number->hasPlus() ? '+' : '') . $number->getCountryCode() . ' ' . $formattedNationalNumber;
		}
		else
		{
			return $formattedNationalNumber;
		}
	}

	protected static function selectFormatForNumber(PhoneNumber $number, $formatType, $countryMetadata)
	{
		$nationalNumber = $number->getNationalNumber();
		$isInternational = ($formatType === Format::INTERNATIONAL);
		$availableFormats = static::getAvailableFormats($countryMetadata);

		foreach ($availableFormats as $format)
		{
			if($isInternational && isset($format['intlFormat']) && $format['intlFormat'] === 'NA')
				continue;

			if(isset($format['leadingDigits']) && !static::matchLeadingDigits($nationalNumber, $format['leadingDigits']))
			{
				continue;
			}

			$formatPatternRegex = '/^' . $format['pattern'] . '$/';
			if(preg_match($formatPatternRegex, $nationalNumber))
			{
				return $format;
			}
		}
		return false;
	}

	protected static function selectOriginalFormatForNumber(PhoneNumber $number)
	{
		$nationalNumber = $number->getNationalNumber();
		$isInternational = $number->isInternational();
		$hasNationalPrefix = $number->getNationalPrefix() != '';
		$countryMetadata = MetadataProvider::getInstance()->getCountryMetadata($number->getCountry());
		$availableFormats = static::getAvailableFormats($countryMetadata);

		foreach ($availableFormats as $format)
		{
			if($isInternational)
			{
				if(isset($format['intlFormat']) && $format['intlFormat'] === 'NA')
				{
					continue;
				}
			}
			else
			{
				if($hasNationalPrefix && !static::_isNationalPrefixSupported($format, $countryMetadata))
				{
					continue;
				}
			}

			if(isset($format['leadingDigits']) && !static::matchLeadingDigits($nationalNumber, $format['leadingDigits']))
			{
				continue;
			}

			$formatPatternRegex = '/^' . $format['pattern'] . '$/';
			if(preg_match($formatPatternRegex, $nationalNumber))
			{
				return $format;
			}
		}
		return false;
	}

	/**
	 * Checks that number starts with specified leading digits regex. Return array of matches if matched or false otherwise
	 * @param string $phoneNumber Phone number.
	 * @param string|array $leadingDigits Leading digits to check (one pattern or array of patterns).
	 * @return array|false
	 */
	protected static function matchLeadingDigits($phoneNumber, $leadingDigits)
	{
		if(is_array($leadingDigits))
		{
			foreach ($leadingDigits as $leadingDigitsSample)
			{
				$re = '/^' . $leadingDigitsSample . '/';
				if(preg_match($re, $phoneNumber, $matches))
				{
					return $matches;
				}
			}
		}
		else
		{
			$re = '/^' . $leadingDigits . '/';
			if(preg_match($re, $phoneNumber, $matches))
			{
				return $matches;
			}
		}
		return false;
	}

	/**
	 * @param string $nationalNumber
	 * @param string $formatType
	 * @param array $countryMetadata
	 * @param mixed $format
	 * @param bool $forceNationalPrefix
	 * @return mixed
	 */
	protected static function formatNationalNumber($nationalNumber, $formatType, $countryMetadata, $format, $forceNationalPrefix)
	{
		$isInternational = ($formatType === Format::INTERNATIONAL);
		$replaceFormat = (isset($format['intlFormat']) && $isInternational) ? $format['intlFormat'] : $format['format'];
		$patternRegex = '/' . $format['pattern'] . '/';

		if(!$isInternational)
		{
			$nationalPrefixFormattingRule = static::getNationalPrefixFormattingRule($format, $countryMetadata);
			if($nationalPrefixFormattingRule != '')
			{
				$nationalPrefixFormattingRule = str_replace(array('$NP', '$FG'), array($countryMetadata['nationalPrefix'], '$1'), $nationalPrefixFormattingRule);
				$replaceFormat = preg_replace('/(\\$\\d)/', $nationalPrefixFormattingRule, $replaceFormat, 1);
			}
			else
			{
				$replaceFormat = $countryMetadata['nationalPrefix'] . ' ' . $replaceFormat;
			}
		}

		return preg_replace($patternRegex, $replaceFormat, $nationalNumber);
	}

	protected static function formatNationalNumberWithOriginalFormat(PhoneNumber $number, $format)
	{
		$isInternational = $number->isInternational();
		$replaceFormat = (isset($format['intlFormat']) && $isInternational) ? $format['intlFormat'] : $format['format'];
		$patternRegex = '/' . $format['pattern'] . '/';
		$nationalNumber = $number->getNationalNumber();
		$countryMetadata = MetadataProvider::getInstance()->getCountryMetadata($number->getCountry());
		$hasNationalPrefix = $number->getNationalPrefix() != '';

		if(!$isInternational && $hasNationalPrefix)
		{
			$nationalPrefixFormattingRule = static::getNationalPrefixFormattingRule($format, $countryMetadata);
			if($nationalPrefixFormattingRule != '')
			{
				$nationalPrefixFormattingRule = str_replace(array('$NP', '$FG'), array($number->getNationalPrefix(), '$1'), $nationalPrefixFormattingRule);
				$replaceFormat = preg_replace('/(\\$\\d)/', $nationalPrefixFormattingRule, $replaceFormat, 1);
			}
			else
			{
				$replaceFormat = $number->getNationalPrefix() . ' ' . $replaceFormat;
			}
		}

		return preg_replace($patternRegex, $replaceFormat, $nationalNumber);
	}

	protected static function getNationalPrefixFormattingRule($format, $countryMetadata)
	{
		if(isset($format['nationalPrefixFormattingRule']))
		{
			return $format['nationalPrefixFormattingRule'];
		}
		else
		{
			$countryCode = $countryMetadata['countryCode'];
			$countriesForCode = MetadataProvider::getInstance()->getCountriesByCode($countryCode);
			$mainCountry = $countriesForCode[0];
			$mainCountryMetadata = MetadataProvider::getInstance()->getCountryMetadata($mainCountry);
			return isset($mainCountryMetadata['nationalPrefixFormattingRule']) ? $mainCountryMetadata['nationalPrefixFormattingRule'] : '';
		}
	}


	protected static function getNationalPrefixOptional($countryMetadata, $format)
	{
		if(is_array($format) && isset($format['nationalPrefixOptionalWhenFormatting']))
			return $format['nationalPrefixOptionalWhenFormatting'];
		else if(isset($countryMetadata['nationalPrefixOptionalWhenFormatting']))
			return $countryMetadata['nationalPrefixOptionalWhenFormatting'];
		else
			return false;
	}

	/**
	 * Returns array of available for country phone number formats.
	 * @param array $countryMetadata
	 * @return array
	 */
	protected static function getAvailableFormats($countryMetadata)
	{
		if(is_array($countryMetadata['availableFormats']))
			return $countryMetadata['availableFormats'];

		$countryCode = $countryMetadata['countryCode'];
		$countriesForCode = MetadataProvider::getInstance()->getCountriesByCode($countryCode);
		$mainCountry = $countriesForCode[0];
		$mainCountryMetadata = MetadataProvider::getInstance()->getCountryMetadata($mainCountry);
		return is_array($mainCountryMetadata['availableFormats']) ? $mainCountryMetadata['availableFormats'] : array();
	}

	/**
	 * National prefix is supported by the format if:
	 * 1.    Format and country metadata do not have nationalPrefixFormattingRule.
	 * 2. OR Format or country metadata contains nationalPrefixFormattingRule and this formatting rule contains "$NP"
	 * @param array $format
	 * @param array $countryMetadata
	 * @returns {boolean}
	 * @private
	 */
	protected static function _isNationalPrefixSupported($format, $countryMetadata)
	{
		$nationalPrefixFormattingRule = static::getNationalPrefixFormattingRule($format, $countryMetadata);

		$result = (!$nationalPrefixFormattingRule || preg_match('/\$NP/', $nationalPrefixFormattingRule));
		return $result;
	}
}