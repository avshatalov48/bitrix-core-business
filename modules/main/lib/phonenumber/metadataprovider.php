<?php

namespace Bitrix\Main\PhoneNumber;

use Bitrix\Main\IO\File;
use Bitrix\Main\SystemException;

class MetadataProvider
{
	protected $metadata;
	protected $codeToCountries;

	protected static $instance;

	const PARSED_METADATA_FILENAME = 'metadata.php';

	protected function __construct()
	{
		$this->loadMetadata();
	}

	/**
	 * Returns instance of MetadataProvider.
	 * @return MetadataProvider
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
	 * Returns array of 2-letter country codes of the countries, sharing the specified phone country code.
	 * @param string $countryCode Phone country code.
	 * @return array
	 */
	public function getCountriesByCode($countryCode)
	{
		return is_array($this->codeToCountries[$countryCode]) ? $this->codeToCountries[$countryCode] : array();
	}

	public function isValidCountryCode($countryCode)
	{
		return isset($this->codeToCountries[$countryCode]);
	}

	/**
	 * Returns metadata record for the country.
	 * @param string $country 2-letter country code.
	 * @return array|false
	 */
	public function getCountryMetadata($country)
	{
		$country = strtoupper($country);
		return isset($this->metadata[$country]) ?  $this->metadata[$country] : false;
	}

	public function toArray()
	{
		return array(
			'codeToCountries' => $this->codeToCountries,
			'metadata' => $this->metadata
		);
	}

	/**
	 * Parses google metadata from the PhoneNumberMetadata.xml
	 * @see https://github.com/googlei18n/libphonenumber/blob/master/resources/
	 * @params string $fileName Metadata file.
	 * @return array Returns parsed metadata.
	 */
	public static function parseGoogleMetadata($fileName)
	{
		$metadataBuilder = new \Bitrix\Main\PhoneNumber\Tools\MetadataBuilder($fileName);

		$metadata = $metadataBuilder->build();
		$codeToCountries = array();

		foreach ($metadata as $metadataRecord)
		{
			$country = strtoupper($metadataRecord['id']);
			if(!is_array($codeToCountries[$metadataRecord['countryCode']]))
			{
				$codeToCountries[$metadataRecord['countryCode']] = array();
			}

			if($metadataRecord['mainCountryForCode'])
				array_unshift($codeToCountries[$metadataRecord['countryCode']], $country);
			else
				$codeToCountries[$metadataRecord['countryCode']][] = $country;
		}

		return array(
			'codeToCountries' => $codeToCountries,
			'metadata' => $metadata
		);
	}

	/**
	 * Loads parsed metadata.
	 * @return void
	 * @throws SystemException
	 */
	protected function loadMetadata()
	{
		if(File::isFileExists(static::PARSED_METADATA_FILENAME))
			throw new SystemException("Metadata file is not found");

		$parsedMetadata = include(static::PARSED_METADATA_FILENAME);

		$this->codeToCountries = $parsedMetadata['codeToCountries'];
		foreach ($parsedMetadata['metadata'] as $metadataRecord)
		{
			$this->metadata[$metadataRecord['id']] = $metadataRecord;
		}
	}
}