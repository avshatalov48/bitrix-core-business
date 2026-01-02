<?php

namespace Bitrix\Rest\Infrastructure\Integration;

use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;

class DocUrl
{
	public function __construct(
		private readonly ?string $region,
	)
	{}

	public static function createByDefault(): self
	{
		return new self(
			Application::getInstance()->getLicense()->getRegion()
		);
	}

	public function getDocUrl(): ?Uri
	{
		return new Uri(
			$this->getDocUrlByRegion($this->region)
		);
	}

	private function getDocUrlByRegion(string $region): string
	{
		return match ($region)
		{
			'ru', 'by', 'kz' => 'https://apidocs.bitrix24.ru/',
			default => 'https://apidocs.bitrix24.com/',
		};
	}
}
