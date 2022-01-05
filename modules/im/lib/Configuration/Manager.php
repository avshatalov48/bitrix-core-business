<?php

namespace Bitrix\Im\Configuration;

use Bitrix\Im\Common;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Pull\Event;
use CModule;
use COption;

class Manager
{
	private const GENERAL = 'general';
	private const NOTIFY = 'notify';

	private const PRIVACY_SEARCH = 'privacySearch';
	private const STATUS = 'status';

	public static function getUserSettings(int $userId): Result
	{
		$result = new Result();
		try
		{
			$preset = Configuration::getUserPreset($userId);
		}
		catch (ObjectPropertyException | ArgumentException | SystemException $exception)
		{
			$result->addError(new Error($exception->getMessage(), $exception->getCode()));
			return $result;
		}

		$result->setData($preset);
		return $result;
	}

	/**
	 * @param int $userId
	 * @param array{notify: array, general: array} $settings
	 * @return Result
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function setUserSettings(int $userId, array $settings): Result
	{
		$result = new Result();
		if (
			!array_key_exists(self::NOTIFY, $settings)
			|| !array_key_exists(self::GENERAL, $settings)
		)
		{
			$result->addError(new Error('Incorrect data when receiving chat settings', 400));
			return $result;
		}

		self::updateUserStatus($userId, $settings['general']);

		self::sendSettingsChangeEvent($userId, $settings['general']);

		self::disableUserSearch($userId, $settings['general']);

		$userPresetId =
			\Bitrix\Im\Model\OptionGroupTable::query()
				->addSelect('ID')
				->where('USER_ID', $userId)
				->fetch()['ID']
		;

		if (isset($settings['general']['notifyScheme']) && $settings['general']['notifyScheme'] === 'simple')
		{
			$settings['notify'] = self::getSimpleNotifySettings($settings['general']);
		}

		if (!$userPresetId)
		{
			$userPresetId = Configuration::createUserPreset($userId, $settings);
		}
		else
		{
			Configuration::updatePresetSettings($userPresetId, $userId, $settings);
			Configuration::chooseExistingPreset($userPresetId, $userId);
		}

		Configuration::cleanUserCache($userId);
		self::enableUserSearch($userId, $settings['general']);

		return $result;
	}

	public static function setUserSetting(int $userId, string $type, array $settings): Result
	{
		$result = new Result();
		if (!in_array($type, [self::NOTIFY, self::GENERAL], true))
		{
			$result->addError(new Error('Incorrect data when receiving chat settings', 400));
			return $result;
		}

		$userPresetId =
			\Bitrix\Im\Model\OptionGroupTable::query()
				->addSelect('ID')
				->where('USER_ID', $userId)
				->fetch()['ID']
		;

		if ($type === self::NOTIFY)
		{
			if (!$userPresetId)
			{
				$preset['notify'] = $settings;
				$preset['general'] = [];
				Configuration::createUserPreset($userId, $preset);

				return $result;
			}
			Notification::updateGroupSettings($userPresetId, $settings);
		}

		if ($type === self::GENERAL)
		{
			self::updateUserStatus($userId, $settings);

			self::sendSettingsChangeEvent($userId, $settings);

			self::disableUserSearch($userId, $settings);

			if (!$userPresetId)
			{
				$preset['general'] = array_replace_recursive(General::getDefaultSettings(), $settings);
				$preset['notify'] = [];
				Configuration::createUserPreset($userId, $preset);

				return $result;
			}
			General::updateGroupSettings($userPresetId, $settings);

			self::enableUserSearch($userId, $settings);
		}

		Configuration::cleanUserCache($userId);

		return $result;
	}

	public static function isSettingsMigrated(): bool
	{
		return COption::GetOptionString('im', 'migration_to_new_settings') === 'Y';
	}

	public static function isUserMigrated(int $userId): bool
	{
		$lastConvertedId = COption::GetOptionInt('im', 'last_converted_user');
		return $userId < $lastConvertedId;
	}

	private static function sendSettingsChangeEvent(int $userId, array $generalSettings): void
	{
		// TODO: refactoring required for the new interface
		if (isset($generalSettings['openDesktopFromPanel']) && CModule::IncludeModule('pull'))
		{
			Event::add($userId, [
				'module_id' => 'im',
				'command' => 'settingsUpdate',
				'expiry' => 5,
				'params' => [
					'openDesktopFromPanel' => $generalSettings['openDesktopFromPanel'],
				],
				'extra' => Common::getPullExtra()
			]);
		}
	}

	private static function disableUserSearch(int $userId, array $generalSettings): void
	{
		$defaultSettings = General::getDefaultSettings();
		if (
			array_key_exists(self::PRIVACY_SEARCH, $generalSettings)
			&& $defaultSettings[self::PRIVACY_SEARCH] === $generalSettings[self::PRIVACY_SEARCH]
		)
		{
			global $USER_FIELD_MANAGER;
			$USER_FIELD_MANAGER->Update("USER", $userId, ['UF_IM_SEARCH' => '']);
		}
	}

	private static function enableUserSearch(int $userId, array $generalSettings): void
	{
		if (isset($generalSettings[self::PRIVACY_SEARCH]))
		{
			global $USER_FIELD_MANAGER;
			$USER_FIELD_MANAGER->Update(
				"USER",
				$userId,
				[
					'UF_IM_SEARCH' => $generalSettings[self::PRIVACY_SEARCH],
				]
			);
		}
	}

	private static function updateUserStatus(int $userId, array $generalSettings): void
	{
		if (isset($generalSettings[self::STATUS]))
		{
			\CIMStatus::Set($userId, ['STATUS' => $generalSettings[self::STATUS]]);
		}
	}


	public static function getSimpleNotifySettings(array $generalSettings): array
	{
		$defaultGeneralSettings = General::getDefaultSettings();

		$send['SITE'] = $generalSettings['notifySchemeSendSite'] ?? $defaultGeneralSettings['notifySchemeSendSite'];
		$send['MAIL'] = $generalSettings['notifySchemeSendEmail'] ?? $defaultGeneralSettings['notifySchemeSendEmail'];
		$send['XMPP'] = $generalSettings['notifySchemeSendXmpp'] ?? $defaultGeneralSettings['notifySchemeSendXmpp'];
		$send['PUSH'] = $generalSettings['notifySchemeSendPush'] ?? $defaultGeneralSettings['notifySchemeSendPush'];

		$notifySettings = Notification::getDefaultSettings();

		foreach ($notifySettings as $moduleId => $moduleSchema)
		{
			foreach ($moduleSchema['NOTIFY'] as $eventName => $eventSchema)
			{
				foreach (['SITE', 'MAIL', 'XMPP', 'PUSH'] as $type)
				{
					$disableType = $eventSchema['DISABLED'][$type];

					$notifySettings[$moduleId]['NOTIFY'][$eventName][$type] =
						!$send[$type]
							? false
							: $eventSchema[$type]
					;
				}
			}
		}

		return $notifySettings;
	}

}