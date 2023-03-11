<?php

use Bitrix\Im\Call\VideoStrategyType;
use Bitrix\Im\Common;
use Bitrix\Im\Configuration\General;
use Bitrix\Im\Configuration\Manager;
use Bitrix\Im\Configuration\Notification;
use Bitrix\Pull\Event;

class CIMSettings
{
	public const SETTINGS = 'settings';
	public const NOTIFY = 'notify';

	public const CLIENT_SITE = 'site';
	public const CLIENT_XMPP = 'xmpp';
	public const CLIENT_MAIL = 'email';
	public const CLIENT_PUSH = 'push';

	public const START_MESSAGE_FIRST = 'first';
	public const START_MESSAGE_LAST = 'last';

	public const PRIVACY_MESSAGE = 'privacyMessage';
	public const PRIVACY_CHAT = 'privacyChat';
	public const PRIVACY_CALL = 'privacyCall';
	public const PRIVACY_SEARCH = 'privacySearch';
	public const PRIVACY_PROFILE = 'privacyProfile';
	public const PRIVACY_RESULT_ALL = 'all';
	public const PRIVACY_RESULT_CONTACT = 'contact';
	public const PRIVACY_RESULT_NOBODY = 'nobody';

	public const STATUS = 'status';

	public static function Get($userId = false)
	{
		$userId = $userId === false ? null : $userId;
		$userId = Common::getUserId($userId);
		if (!$userId)
		{
			return null;
		}

		if (Manager::isSettingsMigrated() || Manager::isUserMigrated($userId))
		{
			$result = Manager::getUserSettings($userId);
			if (!$result->isSuccess())
			{
				return null;
			}
			$settings = $result->getData();

			if(empty($settings['notify']) || empty($settings['general']))
			{
				return null;
			}

			return [
				self::NOTIFY => self::convertNotifySettingsToOldFormat($settings['notify']['settings']),
				self::SETTINGS => $settings['general']['settings'],
			];
		}

		$arSettings[self::SETTINGS] = CUserOptions::GetOption('im', self::SETTINGS, [], $userId);
		$arSettings[self::NOTIFY] = CUserOptions::GetOption('im', self::NOTIFY, [], $userId);

		// Check fields and add default values
		$arSettings[self::SETTINGS] = self::checkValues(self::SETTINGS, $arSettings[self::SETTINGS]);
		$arSettings[self::NOTIFY] = self::checkValues(self::NOTIFY, $arSettings[self::NOTIFY]);

		return $arSettings;
	}

	public static function Set($type, $value, $userId = false)
	{
		if (!in_array($type, [self::SETTINGS, self::NOTIFY], true))
		{
			return false;
		}

		global $USER_FIELD_MANAGER;

		$userId = $userId === false ? null : $userId;
		$userId = Common::getUserId($userId);
		if (!$userId)
		{
			return null;
		}

		if (Manager::isSettingsMigrated() || Manager::isUserMigrated($userId))
		{
			$newFormatSettings['notify'] =
				$type === self::NOTIFY
					? self::convertNotifySettingsToNewFormat($value)
					: []
			;

			$newFormatSettings['general'] =
				$type === self::SETTINGS
					? array_replace_recursive(General::getDefaultSettings(), $value)
					: []
			;
			return Manager::setUserSettings($userId, $newFormatSettings)->isSuccess();
		}

		if (isset($value[self::STATUS]))
		{
			CIMStatus::Set($userId, ['STATUS' => $value[self::STATUS]]);
		}
		if (isset($value['openDesktopFromPanel']) && CModule::IncludeModule('pull'))
		{
			Event::add($userId, [
				'module_id' => 'im',
				'command' => 'settingsUpdate',
				'expiry' => 5,
				'params' => [
					'openDesktopFromPanel' => $value['openDesktopFromPanel'],
				],
				'extra' => Common::getPullExtra()
			]);
		}

		$arDefault = self::GetDefaultSettings($type);
		foreach ($value as $key => $val)
		{
			if (isset($arDefault[$key]) && $arDefault[$key] == $val)
			{
				if ($key === self::PRIVACY_SEARCH)
				{
					$USER_FIELD_MANAGER->Update("USER", $userId, ['UF_IM_SEARCH' => '']);
				}
				unset($value[$key]);
			}
		}
		CUserOptions::SetOption('im', $type, $value, false, $userId);

		if (isset($value[self::PRIVACY_SEARCH]))
		{
			$USER_FIELD_MANAGER->Update("USER", $userId, ['UF_IM_SEARCH' => $value[self::PRIVACY_SEARCH]]);
		}

		return true;
	}

	public static function SetSetting($type, $value, $userId = false)
	{
		if (!in_array($type, [self::SETTINGS, self::NOTIFY], true))
		{
			return false;
		}

		global $USER_FIELD_MANAGER;

		$userId = $userId === false ? null : $userId;
		$userId = Common::getUserId($userId);
		if (!$userId)
		{
			return null;
		}

		if (Manager::isSettingsMigrated() || Manager::isUserMigrated($userId))
		{
			$newFormatSettings = [];
			if ($type === self::NOTIFY)
			{
				$newFormatSettings = self::convertNotifySettingsToNewFormat($value, false);
			}
			if ($type === self::SETTINGS)
			{
				$type = 'general';
				$newFormatSettings = $value;
			}

			return Manager::setUserSetting($userId, $type, $newFormatSettings)->isSuccess();
		}

		$arSettings = CUserOptions::GetOption('im', $type, [], $userId);
		foreach ($value as $key => $val)
		{
			$arSettings[$key] = $val;
		}

		if (isset($value[self::STATUS]))
		{
			CIMStatus::Set($userId, ['STATUS' => $value[self::STATUS]]);
		}
		if (isset($value['openDesktopFromPanel']) && CModule::IncludeModule('pull'))
		{
			Event::add(
				$userId,
				[
					'module_id' => 'im',
					'command' => 'settingsUpdate',
					'expiry' => 5,
					'params' => [
						'openDesktopFromPanel' => $value['openDesktopFromPanel'],
					],
					'extra' => Common::getPullExtra()
				]
			);
		}

		$arDefault = self::GetDefaultSettings($type);
		foreach ($arSettings as $key => $val)
		{
			if (isset($arDefault[$key]) && $arDefault[$key] == $val)
			{
				if ($key === self::PRIVACY_SEARCH)
				{
					$USER_FIELD_MANAGER->Update("USER", $userId, ['UF_IM_SEARCH' => '']);
				}
				unset($value[$key]);
			}
		}
		CUserOptions::SetOption('im', $type, $arSettings, false, $userId);
		if (isset($value[self::PRIVACY_SEARCH]))
		{
			$USER_FIELD_MANAGER->Update("USER", $userId, ['UF_IM_SEARCH' => $value[self::PRIVACY_SEARCH]]);
		}

		return true;
	}

	public static function GetSetting($type, $value, $userId = false)
	{
		if (!in_array($type, [self::SETTINGS, self::NOTIFY], true))
		{
			return null;
		}

		$userId = $userId === false ? null : $userId;
		$userId = Common::getUserId($userId);
		if (!$userId)
		{
			return null;
		}

		if (Manager::isSettingsMigrated() || Manager::isUserMigrated($userId))
		{
			if ($type === self::NOTIFY)
			{
				[$option, $module, $event] = explode('|', $value, 3);
				return (new Notification($module, $event))->getValue($userId, $option);
			}
			if ($type === self::SETTINGS)
			{
				return General::createWithUserId($userId)->getValue($value);
			}
		}

		$arSettings = self::Get($userId);

		return isset($arSettings[$type][$value]) ? $arSettings[$type][$value] : null;
	}

	public static function GetNotifyAccess($userId, $moduleId, $eventId, $clientId)
	{
		$userId = intval($userId);
		if ($userId <= 0 || $moduleId == '' || $eventId == '' || $clientId == '')
		{
			return false;
		}

		if (Manager::isSettingsMigrated() || Manager::isUserMigrated($userId))
		{
			$clientId = $clientId === self::CLIENT_MAIL ? Notification::MAIL : $clientId;

			return Manager::getNotifyAccess($userId, $moduleId, $eventId, $clientId);
		}

		$notifySettingName = $clientId.'|'.$moduleId.'|'.$eventId;
		$userSettings = self::Get($userId);

		if ($userSettings['settings']['notifyScheme'] === 'simple')
		{
			if ($clientId === self::CLIENT_SITE && !$userSettings['settings']['notifySchemeSendSite'])
			{
				return false;
			}

			if ($clientId === self::CLIENT_XMPP && !$userSettings['settings']['notifySchemeSendXmpp'])
			{
				return false;
			}

			if ($clientId === self::CLIENT_MAIL && !$userSettings['settings']['notifySchemeSendEmail'])
			{
				return false;
			}

			if ($clientId === self::CLIENT_PUSH && !$userSettings['settings']['notifySchemeSendPush'])
			{
				return false;
			}

			return
				isset($userSettings['notify'])
				&& array_key_exists($notifySettingName, $userSettings['notify'])
				&& $userSettings['notify'][$notifySettingName] === false
					? false
					: true;
		}
		else
		{
			if (isset($userSettings['notify']) && array_key_exists($notifySettingName, $userSettings['notify']))
			{
				return $userSettings['notify'][$notifySettingName];
			}

			if (isset($userSettings['notify']) && array_key_exists($clientId.'|im|default', $userSettings['notify']))
			{
				return $userSettings['notify'][$clientId.'|im|default'];
			}
		}

		return false;
	}

	public static function GetDefaultSettings($type)
	{
		$defaultSettings = [];
		if ($type === self::SETTINGS)
		{
			$defaultSettings = General::getDefaultSettings();
		}
		elseif ($type === self::NOTIFY)
		{
			$notificationSettings = Notification::getDefaultSettings();
			$defaultSettings = self::convertNotifySettingsToOldFormat($notificationSettings);
		}

		return $defaultSettings;
	}

	public static function CheckValues($type, $value)
	{
		$checkedValues = [];

		$defaultSettings = self::GetDefaultSettings($type);
		if ($type === self::SETTINGS)
		{
			foreach($defaultSettings as $key => $default)
			{
				if ($key === 'status')
				{
					$checkedValues[$key] = in_array($value[$key], ['online', 'dnd', 'away'])? $value[$key]: $default;
				}
				else if ($key === 'panelPositionHorizontal')
				{
					$checkedValues[$key] = in_array($value[$key], ['left', 'center', 'right'])? $value[$key]: $default;
				}
				else if ($key === 'panelPositionVertical')
				{
					$checkedValues[$key] = in_array($value[$key], ['top', 'bottom'])? $value[$key]: $default;
				}
				else if ($key === 'notifyScheme')
				{
					$checkedValues[$key] = in_array($value[$key], ['simple', 'expert'])? $value[$key]: $default;
				}
				else if ($key === 'enableDarkTheme')
				{
					$checkedValues[$key] = in_array($value[$key], ['auto', 'light', 'dark']) ? $value[$key] : $default;
				}
				else if (in_array($key, ['privacyMessage', 'privacyChat', 'privacyCall', 'privacySearch']))
				{
					$checkedValues[$key] =
						in_array($value[$key], [self::PRIVACY_RESULT_ALL, self::PRIVACY_RESULT_CONTACT])
							? $value[$key]
							: $default
					;
				}
				else if ($key === 'privacyProfile')
				{
					$checkedValues[$key] =
						in_array($value[$key], [
							self::PRIVACY_RESULT_ALL,
							self::PRIVACY_RESULT_CONTACT,
							self::PRIVACY_RESULT_NOBODY
						],
						true)
							? $value[$key]
							: $default
					;
				}
				else if ($key === 'sendByEnter' && $value[$key] === 'Y') // for legacy
				{
					$checkedValues[$key] = true;
				}
				else if ($key === 'enableSound' && $value[$key] === 'N') // for legacy
				{
					$checkedValues[$key] = false;
				}
				else if ($key === 'backgroundImage')
				{
					$checkedValues[$key] = $value[$key];
				}
				else if ($key === 'notifySchemeLevel')
				{
					$checkedValues[$key] = in_array($value[$key], ['normal', 'important'])? $value[$key]: $default;
				}
				else if ($key === 'trackStatus')
				{
					$value[$key] = explode(',', $value[$key]);
					foreach ($value[$key] as $k => $v)
					{
						if ($v !== 'all')
						{
							$value[$key][$k] = intval($v);
							if ($value[$key][$k] == 0)
							{
								unset($value[$key][$k]);
							}
						}
					}
					$checkedValues[$key] = implode(',', $value[$key]);

				}
				else if ($key === 'callAcceptIncomingVideo')
				{
					$checkedValues[$key] = in_array($value[$key], VideoStrategyType::getList())? $value[$key]: $default;
				}
				else if (array_key_exists($key, $value))
				{
					$checkedValues[$key] = is_bool($value[$key])? $value[$key]: $default;
				}
				else
				{
					$checkedValues[$key] = $default;
				}
			}
		}
		else if ($type === self::NOTIFY)
		{
			foreach($defaultSettings as $key => $default)
			{
				if (array_key_exists($key, $value))
				{
					$checkedValues[$key] = is_bool($value[$key]) ? $value[$key] : $default;
				}
				else
				{
					$checkedValues[$key] = $default;
				}
			}
		}

		return $checkedValues;
	}

	public static function GetNotifyNames()
	{
		return Notification::getEventNames();
	}

	public static function GetSimpleNotifyBlocked($byModule = false)
	{
		$arNotifyBlocked = [];

		$arSettings = self::Get();

		if ($arSettings[self::SETTINGS]['notifyScheme'] === 'expert')
		{
			foreach ($arSettings[self::NOTIFY] as $key => $value)
			{
				if ($value === false)
				{
					[$clientId, $moduleId, $notifyId] = explode('|', $key, 3);
					if ($clientId === self::CLIENT_SITE)
					{
						if (CIMNotifySchema::CheckDisableFeature($moduleId, $notifyId, $clientId))
						{
							continue;
						}
						if ($byModule)
						{
							$arNotifyBlocked[$moduleId][$notifyId] = false;
						}
						else
						{
							$arNotifyBlocked[$moduleId . '|' . $notifyId] = false;
						}
					}
				}
			}
		}
		else
		{
			foreach ($arSettings[self::NOTIFY] as $key => $value)
			{
				if ($value === false)
				{
					[$clientId, $moduleId, $notifyId] = explode('|', $key, 3);
					if (in_array($clientId, ['push', 'important', 'disabled']))
					{
						continue;
					}

					if ($clientId === self::CLIENT_SITE)
					{
						if (CIMNotifySchema::CheckDisableFeature($moduleId, $notifyId, $clientId))
						{
							continue;
						}
						if ($byModule)
						{
							$arNotifyBlocked[$moduleId][$notifyId] = false;
						}
						else
						{
							$arNotifyBlocked[$moduleId . '|' . $notifyId] = false;
						}
					}
				}
			}
		}

		return $arNotifyBlocked;
	}

	public static function GetPrivacy($type, $userId = false)
	{
		$userId = $userId === false ? null : $userId;
		$userId = Common::getUserId($userId);

		if (!$userId)
		{
			return null;
		}

		if (Manager::isSettingsMigrated() || Manager::isUserMigrated($userId))
		{
			return General::createWithUserId($userId)->getValue($type);
		}

		$ar = CIMSettings::Get($userId);

		return array_key_exists($type, $ar[CIMSettings::SETTINGS])? $ar[CIMSettings::SETTINGS][$type]: false;
	}

	public static function GetStartChatMessage()
	{
		return COption::GetOptionString("im", 'start_chat_message');
	}

	public static function ClearCache($userId = false)
	{
		return true;
	}

	private static function convertNotifySettingsToNewFormat(array $settings, $needReplace = true): array
	{
		$defaultSettings = Notification::getDefaultSettings();

		$newFormatSettings = [];
		foreach ($settings as $name => $value)
		{
			[$type, $module, $event] = explode('|', $name, 3);

			switch ($type)
			{
				case 'site':
					$type = 1;

					break;
				case 'email':
					$type = 2;

					break;
				case 'xmpp':
					$type = 3;

					break;
				case 'push':
					$type = 4;

					break;
			}
			$newName = implode('|', ['no', $module, $event, $type]);

			$newFormatSettings[] = [
				'NAME' => $newName,
				'VALUE' => $value ? 'Y' : 'N'
			];
		}
		$newSettings = Notification::decodeSettings($newFormatSettings);

		return $needReplace
			? array_replace_recursive($defaultSettings, $newSettings)
			: $newSettings
		;
	}

	public static function convertNotifySettingsToOldFormat(array $settings): array
	{
		$formattedSettings = [];
		foreach ($settings as $moduleId => $notifyTypes)
		{
			foreach ($notifyTypes['NOTIFY'] as $eventName => $eventValue)
			{
				$siteName = self::CLIENT_SITE.'|'.$moduleId.'|'.$eventName;
				$mailName = self::CLIENT_MAIL.'|'.$moduleId.'|'.$eventName;
				$xmppName = self::CLIENT_XMPP.'|'.$moduleId.'|'.$eventName;
				$pushName = self::CLIENT_PUSH.'|'.$moduleId.'|'.$eventName;

				$formattedSettings[$siteName] = $eventValue['SITE'];
				$formattedSettings[$mailName] = $eventValue['MAIL'];
				$formattedSettings[$xmppName] = $eventValue['XMPP'];
				$formattedSettings[$pushName] = $eventValue['PUSH'];

				if (isset($eventValue['DISABLED']))
				{
					$formattedSettings['disabled|'.$siteName] = $eventValue['DISABLED']['SITE'];
					$formattedSettings['disabled|'.$mailName] = $eventValue['DISABLED']['MAIL'];
					$formattedSettings['disabled|'.$xmppName] = $eventValue['DISABLED']['XMPP'];
					$formattedSettings['disabled|'.$pushName] = $eventValue['DISABLED']['PUSH'];
				}

				$formattedSettings['important|'.$moduleId.'|'.$eventName] =
					isset($eventValue['IMPORTANT']) && is_bool($eventValue['IMPORTANT'])
						? $eventValue['IMPORTANT']
						: true;
			}
		}
		return $formattedSettings;
	}

}