<?php
namespace Bitrix\Im;

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

		if (!isset($settings['broadcastingEnabled']))
		{
			return $broadcastingEnabled;
		}

		return (bool)$settings['broadcastingEnabled'];
	}
}

