<?php

namespace Bitrix\Main\License;

use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;

class UrlProvider
{
	private const STORE_DOMAINS = [
		'ru' => 'https://www.1c-bitrix.ru',
		'by' => 'https://www.1c-bitrix.by',
		'kz' => 'https://www.1c-bitrix.kz',
		'en' => 'https://store.bitrix24.com',
		'de' => 'https://store.bitrix24.de',
		'eu' => 'https://store.bitrix24.eu',
	];
	private const PRODUCTS_DOMAINS = [
		'ru' => 'https://www.1c-bitrix.ru',
		'by' => 'https://www.1c-bitrix.by',
		'kz' => 'https://www.1c-bitrix.kz',
		'en' => 'https://www.bitrix24.com',
		'de' => 'https://www.bitrix24.de',
		'eu' => 'https://www.bitrix24.eu',
		'in' => 'https://www.bitrix24.in',
	];

	public function getPriceTableUrl(): Uri
	{
		$license = Application::getInstance()->getLicense();
		$url = new Uri(self::PRODUCTS_DOMAINS[$license->getRegion() ?? 'en'] ?? self::PRODUCTS_DOMAINS['en']);

		if (in_array($license->getRegion(), ['ru', 'by', 'kz']))
		{
			$url->setPath('/buy/products/b24.php');
		}
		else
		{
			$url->setPath('/prices/self-hosted.php');
		}

		return $url;
	}

	public function getPurchaseHistoryUrl(): Uri
	{
		$license = Application::getInstance()->getLicense();
		$url = new Uri(self::STORE_DOMAINS[$license->getRegion() ?? 'en'] ?? self::STORE_DOMAINS['en']);

		if (in_array($license->getRegion(), ['ru', 'by', 'kz']))
		{
			$url->setPath('/support/key_info.php');
		}
		else
		{
			$url->setPath('/profile/license-keys.php');
		}

		return $url;
	}
}
