<?php

namespace Bitrix\Main;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\Date;

final class License
{
	private $isLicenseClientIncluded = false;

	private function includeUpdateClient(): void
	{
		if (!$this->isLicenseClientIncluded)
		{
			$this->isLicenseClientIncluded = true;
			require_once(Application::getDocumentRoot() . "/bitrix/modules/main/classes/general/update_client.php");
		}
	}

	public function getKey(): ?string
	{
		$this->includeUpdateClient();
		$key = \CUpdateClient::GetLicenseKey();
		if (is_string($key))
		{
			return $key;
		}

		return null;
	}

	public function isDemo(): bool
	{
		return defined("DEMO") && DEMO === "Y";
	}

	public function isTimeBound(): bool
	{
		return defined("TIMELIMIT_EDITION") && TIMELIMIT_EDITION === "Y";
	}

	public function isEncoded(): bool
	{
		return defined("ENCODE") && ENCODE === "Y";
	}

	public function getExpireDate(): ?Date
	{
		$date = (int)$GLOBALS["SiteExpireDate"];
		if ($date > 0)
		{
			return Date::createFromTimestamp($date);
		}

		return null;
	}

	public function getRegion(): ?string
	{
		if (Loader::includeModule('bitrix24'))
		{
			return \CBitrix24::getPortalZone();
		}

		$region = Option::get('main', '~PARAM_CLIENT_LANG');
		if (!empty($region))
		{
			return $region;
		}

		$region = $this->getRegionByVendor();
		if (!empty($region))
		{
			return $region;
		}

		return $this->getRegionByLanguage();
	}

	private function getRegionByVendor(): ?string
	{
		$vendor = Option::get("main", "vendor");
		if ($vendor === 'ua_bitrix_portal')
		{
			return 'ua';
		}
		if ($vendor === 'bitrix_portal')
		{
			return 'en';
		}
		if ($vendor === '1c_bitrix_portal')
		{
			return 'ru';
		}

		return null;
	}

	private function getRegionByLanguage(): ?string
	{
		if (file_exists(Application::getDocumentRoot() . "/bitrix/modules/main/lang/ua"))
		{
			return 'ua';
		}
		if (file_exists(Application::getDocumentRoot() . "/bitrix/modules/main/lang/ru"))
		{
			return 'ru';
		}

		return null;
	}
}
