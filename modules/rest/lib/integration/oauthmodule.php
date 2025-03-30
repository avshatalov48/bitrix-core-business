<?php

namespace Bitrix\Rest\Integration;

use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;

final class OAuthModule
{
	/**
	 * Does the current environment support working with `oauth` module?
	 *
	 * @return bool
	 */
	public static function isSupported(): bool
	{
		return
			ModuleManager::isModuleInstalled('oauth')
			&& Option::get('rest', 'oauth_module_supported') === 'Y'
		;
	}
}
