<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Integration\Dav;

use Bitrix\Dav\Public\WebDavMethod;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class WebDavMethodProvider
{
	public function getMkcolMethod(): string
	{
		$this->checkIsAvailable();

		return WebDavMethod::MKCOL->value;
	}

	public function getProppatchMethod(): string
	{
		$this->checkIsAvailable();

		return WebDavMethod::PROPPATCH->value;
	}

	public function getPropfindMethod(): string
	{
		$this->checkIsAvailable();

		return WebDavMethod::PROPFIND->value;
	}

	public function getReportMethod(): string
	{
		$this->checkIsAvailable();

		return WebDavMethod::REPORT->value;
	}

	private function checkIsAvailable(): void
	{
		if (!Loader::includeModule('dav'))
		{
			throw new SystemException('Module dav is not installed');
		}
	}
}
