<?php

namespace Bitrix\Location\Source\Osm;

use Bitrix\Location\Entity\Source;
use Bitrix\Location\Repository\Location\IRepository;
use Bitrix\Location\Source\Osm\Api\Api;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Context;

/**
 * Class OsmSource
 * @package Bitrix\Location\Source\Osm
 * @internal
 */
final class OsmSource extends Source
{
	public const API_PATH = '/api';

	/** @var TokenRequester */
	private $tokenRequester;

	/**
	 * OsmSource constructor.
	 */
	public function __construct()
	{
		$this->tokenRequester = (new TokenRequester())->setSource($this);
	}

	/**
	 * @inheritDoc
	 */
	public function makeRepository(): IRepository
	{
		static $result = null;

		if ($result !== null)
		{
			return $result;
		}

		$result = new Repository(
			new Api($this),
			$this
		);

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getJSParams(): array
	{
		$token = $this->getOsmToken();

		return [
			'serviceUrl' => $this->getOsmApiUrl(),
			'mapServiceUrl' => $this->getOsmMapServiceUrl(),
			'token' => $token ? $token->getToken() : null,
			'useGeocodingService' => true,
			'hostName' => $this->getOsmHostName()
		];
	}

	/**
	 * @inheritDoc
	 */
	public function convertLang(string $bitrixLang): string
	{
		$langMap = [
			'br' => 'pt',	// Portuguese (Brazil)
			'la' => 'es', 	// Spanish
			'sc' => 'zh', 	// Chinese (Simplified)
			'tc' => 'zh', 	// Chinese (Traditional)
			'vn' => 'vi', 	// Vietnamese
			'ua' => 'uk',	// Ukrainian
		];

		return $langMap[$bitrixLang] ?? $bitrixLang;
	}

	/**
	 * @return string
	 */
	public function getOsmHostName(): string
	{
		if(defined('BX24_HOST_NAME') && ModuleManager::isModuleInstalled('bitrix24'))
		{
			$result = BX24_HOST_NAME;
		}
		else
		{
			$result = Context::getCurrent()->getServer()->get('HTTP_HOST');

			if(strpos($result, ':') !== false)
			{
				$result = explode(':', $result)[0];
			}
		}

		return $result;
	}

	/**
	 * @return string|null
	 */
	public function getOsmApiUrl(): ?string
	{
		$serviceUrl = $this->getOsmServiceUrl();
		if (!$serviceUrl)
		{
			return null;
		}

		return $serviceUrl . static::API_PATH;
	}

	/**
	 * @return string|null
	 */
	public function getOsmServiceUrl(): ?string
	{
		if (defined('LOCATION_OSM_SERVICE_URL') && LOCATION_OSM_SERVICE_URL)
		{
			return (string)LOCATION_OSM_SERVICE_URL;
		}

		return $this->getConfig()
			? $this->getConfig()->getValue('SERVICE_URL')
			: null;
	}

	/**
	 * @return string|null
	 */
	public function getOsmMapServiceUrl(): ?string
	{
		if (defined('LOCATION_OSM_MAP_SERVICE_URL') && LOCATION_OSM_MAP_SERVICE_URL)
		{
			return (string)LOCATION_OSM_MAP_SERVICE_URL;
		}

		return $this->getConfig()
			? $this->getConfig()->getValue('MAP_SERVICE_URL')
			: null;
	}

	/**
	 * @return Token|null
	 */
	public function getOsmToken(): ?Token
	{
		return $this->tokenRequester->getToken();
	}

	public function isAvailable(): bool
	{
		$token = $this->tokenRequester->getToken();
		if ($token)
		{
			return true;
		}

		return !$this->tokenRequester->hasLicenseIssues();
	}
}
