<?php

namespace Bitrix\Main\License;

use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\License;

class UrlProvider
{
	private const STORE_DOMAINS = [
		'ru' => 'www.1c-bitrix.ru',
		'by' => 'www.1c-bitrix.by',
		'kz' => 'www.1c-bitrix.kz',
		'en' => 'store.bitrix24.com',
		'de' => 'store.bitrix24.de',
		'eu' => 'store.bitrix24.eu',
	];
	private const PRODUCTS_DOMAINS = [
		'ru' => 'www.1c-bitrix.ru',
		'by' => 'www.1c-bitrix.by',
		'kz' => 'www.1c-bitrix.kz',
		'en' => 'www.bitrix24.com',
		'de' => 'www.bitrix24.de',
		'eu' => 'www.bitrix24.eu',
		'in' => 'www.bitrix24.in',
	];
	private const FEEDBACK_DOMAINS = [
		'ru' => 'product-feedback.bitrix24.ru',
		'en' => 'product-feedback.bitrix24.com',
	];
	private const PRIVACY_DOMAINS = [
		'ru' => 'https://www.bitrix24.ru',
		'kz' => 'https://www.bitrix24.kz',
		'en' => 'https://www.bitrix24.com',
		'de' => 'https://www.bitrix24.de',
	];

	protected License $license;

	public function __construct()
	{
		$this->license = Application::getInstance()->getLicense();
	}

	public function getPriceTableUrl(): Uri
	{
		$region = $this->license->getRegion();
		$domain = self::PRODUCTS_DOMAINS[$region ?? 'en'] ?? self::PRODUCTS_DOMAINS['en'];
		$url = new Uri('https://' . $domain);

		if (in_array($region, ['ru', 'by', 'kz']))
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
		$region = $this->license->getRegion();
		$domain = self::STORE_DOMAINS[$region ?? 'en'] ?? self::STORE_DOMAINS['en'];
		$url = new Uri('https://' . $domain);

		if (in_array($region, ['ru', 'by', 'kz']))
		{
			$url->setPath('/support/key_info.php');
		}
		else
		{
			$url->setPath('/profile/license-keys.php');
		}

		return $url;
	}

	public function getPrivacyPolicyUrl(): Uri
	{
		$region = $this->license->getRegion();
		$url = new Uri(self::PRIVACY_DOMAINS[$region ?? 'en'] ?? self::PRIVACY_DOMAINS['en']);

		if (in_array($region, ['ru', 'kz']))
		{
			$url->setPath('/about/privacy.php');
		}
		else
		{
			$url->setPath('/privacy/');
		}

		return $url;
	}

	public function getMailingAgreementUrl(): ?Uri
	{
		$region = $this->license->getRegion();

		if (in_array($region, ['ru', 'by', 'kz']))
		{
			return new Uri("https://www.bitrix24.$region/about/advertising.php");
		}

		return null;
	}

	public function getProductDomain(?string $region = null): Uri
	{
		$region ??= $this->license->getRegion();
		$domain = self::PRODUCTS_DOMAINS[$region ?? 'en'] ?? self::PRODUCTS_DOMAINS['en'];

		return new Uri('https://' . $domain);
	}

	public function getTechDomain(): string
	{
		return $this->license->isCis() ? 'bitrix24.tech' : 'bitrix.info';
	}

	public function getFeedbackDomain(?string $region = null): string
	{
		$region ??= $this->license->getRegion();

		return self::FEEDBACK_DOMAINS[$region ?? 'en'] ?? self::FEEDBACK_DOMAINS['en'];
	}
}
