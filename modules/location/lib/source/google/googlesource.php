<?php

namespace Bitrix\Location\Source\Google;

use Bitrix\Location\Common\Pool;
use Bitrix\Location\Entity\Source;
use Bitrix\Location\Repository\Location\IRepository;
use Bitrix\Location\Common\CachedPool;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\EventManager;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Fileman;

/**
 * Class GoogleSource
 * @package Bitrix\Location\Source\Google
 * @internal
 */
class GoogleSource extends Source
{
	/**
	 * @inheritDoc
	 */
	public function makeRepository(): IRepository
	{
		static $result = null;

		if (!is_null($result))
		{
			return $result;
		}

		$httpClient = new HttpClient(
			[
				'version' => '1.1',
				'socketTimeout' => 30,
				'streamTimeout' => 30,
				'redirect' => true,
				'redirectMax' => 5,
			]
		);

		$cacheTTL = 2592000; //month
		$poolSize = 100;
		$pool = new Pool($poolSize);

		$cachePool = new CachedPool(
			$pool,
			$cacheTTL,
			'locationSourceGoogleRequester',
			Cache::createInstance(),
			EventManager::getInstance()
		);

		$result = new Repository(
			$this->getBackendKey(),
			$httpClient,
			$this,
			$cachePool
		);

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getJSParams(): array
	{
		return [
			'apiKey' => $this->getFrontendKey(),
			'showPhotos' => $this->config->getValue('SHOW_PHOTOS_ON_MAP'),
			'useGeocodingService' => $this->config->getValue('USE_GEOCODING_SERVICE'),
		];
	}

	/**
	 * @return string
	 */
	private function getBackendKey(): string
	{
		$configKey = $this->config->getValue('API_KEY_BACKEND');
		if ($configKey)
		{
			return (string)$configKey;
		}

		return (string)Option::get('location', 'google_map_api_key_backend', '');
	}

	/**
	 * @return string
	 */
	private function getFrontendKey(): string
	{
		$key = $this->config->getValue('API_KEY_FRONTEND');
		if ($key)
		{
			return (string)$key;
		}

		$key = Option::get('location', 'google_map_api_key', '');
		if ($key !== '')
		{
			return (string)$key;
		}

		if (Loader::includeModule('fileman'))
		{
			$key = Fileman\UserField\Types\AddressType::getApiKey();
			if ($key !== '' && !is_null($key))
			{
				return $key;
			}
		}

		return '';
	}

	/**
	 * @inheritDoc
	 *
	 * @see https://developers.google.com/maps/faq#languagesupport
	 */
	public function convertLang(string $bitrixLang): string
	{
		$langMap = [
			'br' => 'pt-BR',	// Portuguese (Brazil)
			'la' => 'es', 		// Spanish
			'sc' => 'zh-CN', 	// Chinese (Simplified)
			'tc' => 'zh-TW', 	// Chinese (Traditional)
			'vn' => 'vi', 		// Vietnamese
			'ua' => 'uk', 		// Ukrainian
		];

		return $langMap[$bitrixLang] ?? $bitrixLang;
	}
}
