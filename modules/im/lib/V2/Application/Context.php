<?php

namespace Bitrix\Im\V2\Application;

use Bitrix\Main\Application;

class Context
{
	private const DESKTOP_API_VERSION_GET_PARAMETER = 'BXD_API_VERSION';
	private const DESKTOP_USER_AGENT = 'BitrixDesktop';
	private const MOBILE_USER_AGENT = 'BitrixMobile';

	private \Bitrix\Main\Context $context;

	public function __construct(\Bitrix\Main\Context $context)
	{
		$this->context = $context;
	}

	public static function getCurrent(): static
	{
		return new static(Application::getInstance()->getContext());
	}

	public function isDesktop(): bool
	{
		return
			$this->context->getRequest()->getQuery(self::DESKTOP_API_VERSION_GET_PARAMETER)
			|| $this->containInUserAgent(self::DESKTOP_USER_AGENT)
		;
	}

	public function isMobile(): bool
	{
		return $this->containInUserAgent(self::MOBILE_USER_AGENT);
	}

	private function containInUserAgent(string $userAgent): bool
	{
		return stripos($this->context->getRequest()->getUserAgent() ?? '', $userAgent) !== false;
	}
}