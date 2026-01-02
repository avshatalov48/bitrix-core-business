<?php
namespace Bitrix\Im;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;
use CUserOptions;

class Settings
{
	public static function getLoggerConfig(): array
	{
		$types = [
			'desktop' => true,
			'log' => false,
			'info' => false,
			'warn' => false,
			'error' => true,
			'trace' => true,
		];

		$settings = \Bitrix\Main\Config\Configuration::getValue('im');
		if (!isset($settings['logger']))
		{
			return $types;
		}

		foreach ($types as $type => $value)
		{
			if (isset($settings['logger'][$type]))
			{
				$types[$type] = (bool)$settings['logger'][$type];
			}
		}

		return $types;
	}

	public static function isBroadcastingEnabled(): bool
	{
		$broadcastingEnabled = false;

		$settings = \Bitrix\Main\Config\Configuration::getValue('im');

		if (!isset($settings['call']['broadcast_enabled']))
		{
			return $broadcastingEnabled;
		}

		return (bool)$settings['call']['broadcast_enabled'];
	}

	/**
	 * @deprecated
	 */
	public static function isCallBetaAvailable(): bool
	{
		$result = Option::get('im', 'call_beta_access', 'N');
		return $result === 'Y';
	}

	/**
	 * @deprecated
	 */
	public static function isAiBetaAvailable(): bool
	{
		$result = Option::get('im', 'ai_beta_access', 'N');
		return $result === 'Y';
	}

	public static function isV2Available(): bool
	{
		$userId = Common::getUserId();
		if (!$userId)
		{
			return false;
		}

		if (!\Bitrix\Main\Loader::includeModule('intranet'))
		{
			return false;
		}

		return true;
	}

	public static function isLegacyChatActivated($userId = false): bool
	{
		if (!self::isV2Available())
		{
			return true;
		}

		if (Option::get('im', 'legacy_chat_enabled', 'N') === 'Y')
		{
			return true;
		}

		if (CUserOptions::GetOption('im', 'legacy_chat_user_enabled', 'N', $userId) === 'Y')
		{
			return true;
		}

		$isLegacy = Context::getCurrent()?->getRequest()->getQuery('IM_LEGACY');

		return $isLegacy === 'Y';
	}

	public static function setLegacyChatActivity($active = true, $userId = false): bool
	{
		if (!self::isV2Available())
		{
			return false;
		}

		CUserOptions::SetOption('im', 'legacy_chat_user_enabled', $active ? 'Y' : 'N', false, $userId);
		\Bitrix\Intranet\Composite\CacheProvider::deleteUserCache();

		return true;
	}
}