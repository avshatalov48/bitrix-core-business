<?php

namespace Bitrix\Rest\Infrastructure\Market;

use Bitrix\Main\Application;
use Bitrix\Main\License\UrlProvider;
use Bitrix\Main\Web\Uri;

class MarketUrl
{
	private const EULA_REGION_WHITELIST = ['ru', 'by'];
	private const TERMS_REGION_BLACKLIST = ['ru', 'ua', 'kz', 'by'];

	public function __construct(
		private readonly Uri $productUrl,
		private readonly ?string $region,
	)
	{}

	public static function createByDefault(): self
	{
		if (method_exists(UrlProvider::class, 'getProductDomain'))
		{
			return new self(
				(new UrlProvider())->getProductDomain(),
				Application::getInstance()->getLicense()->getRegion(),
			);
		}

		return new self(
			new Uri(self::getProductDomain()),
			Application::getInstance()->getLicense()->getRegion(),
		);
	}

	public function getEulaUrl(): ?Uri
	{
		if (!in_array($this->region, self::EULA_REGION_WHITELIST))
		{
			return null;
		}

		$url = clone $this->productUrl;
		$url->setPath("/download/files/manuals/$this->region/EULA_1C_Bitrix24_Market.html");

		return $url;
	}

	public function getTermsUrl(): ?Uri
	{
		if (in_array($this->region, self::TERMS_REGION_BLACKLIST))
		{
			return null;
		}

		$url = clone $this->productUrl;
		$url->setPath('/terms/apps24_terms_of_service.pdf');

		return $url;
	}

	protected static function getProductDomain(): string
	{
		$region = Application::getInstance()->getLicense()->getRegion();

		return match ($region)
		{
			'ru' => 'https://www.1c-bitrix.ru',
			'by' => 'https://www.1c-bitrix.by',
			default => 'https://www.bitrix24.com',
		};
	}
}
