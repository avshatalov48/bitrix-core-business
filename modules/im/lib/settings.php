<?php
namespace Bitrix\Im;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\Json;

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

	public static function isCallBetaAvailable(): bool
	{
		$result = \Bitrix\Main\Config\Option::get('im', 'call_beta_access', 'N');
		return $result === 'Y';
	}

	public static function isAiBetaAvailable(): bool
	{
		$result = \Bitrix\Main\Config\Option::get('im', 'ai_beta_access', 'N');
		return $result === 'Y';
	}

	public static function isBetaAvailable(): bool
	{
		$userId = Common::getUserId();
		if (!$userId)
		{
			return false;
		}

		if (
			\Bitrix\Main\Loader::includeModule('bitrix24')
			&& \CBitrix24::IsNfrLicense()
		)
		{
			return true;
		}

		$result = \Bitrix\Main\Config\Option::get('im', 'beta_access', 'N');
		if ($result === 'N')
		{
			return false;
		}

		if ($result === 'Y')
		{
			return true;
		}

		try
		{
			$users = Json::decode($result);
			if (in_array($userId, $users, true))
			{
				return true;
			}
		}
		catch (ArgumentException $exception)
		{
			return false;
		}

		return false;
	}

	public static function isBetaActivated($userId = false): bool
	{
		if (!self::isBetaAvailable())
		{
			return false;
		}

		$isLegacy = \Bitrix\Main\Context::getCurrent()->getRequest()->getQuery('IM_LEGACY');
		$isIframe = \Bitrix\Main\Context::getCurrent()->getRequest()->getQuery('IFRAME');
		if ($isLegacy === 'Y' || $isIframe === 'Y')
		{
			return false;
		}

		if (self::isForceBetaActivatedForCurrentUser())
		{
			return true;
		}

		if (\CUserOptions::GetOption('im', 'v2_enabled', 'N', $userId) === 'Y')
		{
			return true;
		}

		return false;
	}

	public static function isForceBetaActivatedForCurrentUser()
	{
		$settings = \Bitrix\Main\Config\Configuration::getValue('im');
		if (!isset($settings['force_beta']) || $settings['force_beta'] === false)
		{
			return false;
		}

		if (isset($settings['force_beta_disabled']) && is_array($settings['force_beta_disabled']))
		{
			global $USER;
			if (in_array($USER->GetId(), $settings['force_beta_disabled'], true))
			{
				return false;
			}
		}

		return true;
	}

	public static function setBetaActive($active = true): bool
	{
		if (!\Bitrix\Im\Settings::isBetaAvailable())
		{
			return false;
		}

		\CUserOptions::SetOption('im', 'v2_enabled', $active ? 'Y' : 'N');

		if (\Bitrix\Main\Loader::includeModule("intranet"))
		{
			\Bitrix\Intranet\Composite\CacheProvider::deleteUserCache();
		}

		return true;
	}
}