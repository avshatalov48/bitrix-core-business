<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\Response;

use Bitrix\Main\HttpResponse;

final class OpenMobileApp extends HttpResponse
{
	public const MOBILE_PROTOCOL = 'bitrix24://';
	private string $url;

	public function __construct(string $url)
	{
		$this->url = ltrim($url, '/');

		parent::__construct();
	}

	protected function buildMobileUrl(): string
	{
		return static::MOBILE_PROTOCOL . $this->url;
	}

	public function send(): void
	{
		$this->addHeader('Location', $this->buildMobileUrl());
		parent::send();
	}
}