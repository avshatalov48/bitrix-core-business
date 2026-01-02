<?php

namespace Bitrix\Vote\Config;

use Bitrix\Main\Config\Option;

class Feature
{
	private static ?self $instance = null;

	public static function instance(): self
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function getInstance(): self
	{
		return self::instance();
	}

	public function isImIntegrationEnabled(): bool
	{
		// disabled by default in development period
		return Option::get('vote', 'is_im_integration_enabled', 'Y') === 'Y';
	}

}