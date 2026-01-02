<?php

namespace Bitrix\Rest\Integration\Bitrix24;

use Bitrix\Bitrix24\License\Market;
use Bitrix\Main\Loader;
use CBitrix24;

final class Bitrix24
{
	private static Bitrix24 $instance;
	private bool $available = false;

	private function __construct()
	{
		$this->available = Loader::includeModule('bitrix24');
	}

	public static function getInstance()
	{
		if (!isset(static::$instance))
		{
			static::$instance = new static();
		}
		return static::$instance;
	}

	private function __clone() { }

	public function isAvailable(): bool
	{
		return $this->available;
	}

	public function isAvailableForRuRegion(): bool
	{
		return $this->available && \Bitrix\Bitrix24\License::getCurrent()->getRegion() === 'ru';
	}

	public function getBuyPath(): ?string
	{
		if ($this->available)
		{
			return CBitrix24::isLicensePaid()? Market::getDefaultBuyPath() : Market::PATH_MARKET_BUT_WITHOUT_TARIFF;
		}

		return null;
	}
}