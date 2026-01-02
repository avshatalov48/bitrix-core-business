<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

namespace Bitrix\Main\Service\Version;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\License\UrlProvider;

class BitrixVm
{
	protected const LAST_KNOWN_VERSION = '9.0.6';

	public function getVersion(): ?string
	{
		$ver = getenv('BITRIX_VA_VER');

		return $ver ?: null;
	}

	public function getAvailableVersion(): string
	{
		$http = new HttpClient([
			'socketTimeout' => 5,
			'streamTimeout' => 5,
		]);

		$domain = (new UrlProvider())->getTechDomain();
		$uri = "https://repo.{$domain}/vm/bitrix-env.version";

		$ver = $http->get($uri);

		if ($ver)
		{
			return str_replace('-', '.', $ver);
		}

		return self::LAST_KNOWN_VERSION;
	}
}
