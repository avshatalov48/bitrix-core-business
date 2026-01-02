<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Integration\Dav\Service;

use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;
use CDav;

class HttpClientProxyConfigurator
{
	public function apply(HttpClient $client): HttpClient
	{
		if (!self::isAvailable() || !CDav::useProxy())
		{
			return $client;
		}

		$proxy = CDav::getProxySettings();

		$client->setProxy(
			$proxy['PROXY_HOST'] ?? '',
			$proxy['PROXY_PORT'] ?? '',
			$proxy['PROXY_USERNAME'] ?? '',
			$proxy['PROXY_PASSWORD'] ?? '',
		);

		return $client;
	}

	private static function isAvailable(): bool
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return Loader::includeModule('dav');
	}
}
