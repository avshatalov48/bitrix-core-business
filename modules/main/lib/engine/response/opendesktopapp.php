<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\Response;

use Bitrix\Main\HttpResponse;

final class OpenDesktopApp extends HttpResponse
{
	public const DESKTOP_PROTOCOL = 'bx://';
	private string $url;

	public function __construct(string $url)
	{
		$this->url = ltrim($url, '/');

		parent::__construct();
	}

	protected function buildDesktopUrl(): string
	{
		return static::DESKTOP_PROTOCOL . $this->url;
	}

	public function send(): void
	{
		$this->addHeader('Location', $this->buildDesktopUrl());
		parent::send();
	}
}