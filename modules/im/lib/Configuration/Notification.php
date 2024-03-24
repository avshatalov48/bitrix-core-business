<?php

namespace Bitrix\Im\Configuration;

use Bitrix\Im\Model\OptionStateTable;
use Bitrix\Im\Model\OptionUserTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\EventManager;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use Bitrix\Pull\Model\PushTable;

class Notification extends Base
{
	public const SITE = 'site';
	public const MAIL = 'mail';
	public const XMPP = 'xmpp';
	public const PUSH = 'push';

	protected const ENTITY = 'no';
	protected const MODULE = 1;
	protected const NAME = 2;
	protected const TYPE = 3;

	protected static $defaultSettings = [];

	private $module;
	private $name;

	private static $types = [
		'SITE' => 1,
		'MAIL' => 2,
		'XMPP' => 3,
		'PUSH' => 4,
	];

	/**
	 * @param string $module
	 * @param string $name
	 */
	public function __construct(string $module, string $name)
	{
		$this->module = $module;
		$this->name = $name;
	}

	/**
	 * @param string $module
	 */
	public function setModule(string $module): void
	{
		$this->module = $module;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * Determines whether a notification can be sent to the user
	 *
	 * @throws SqlQueryException
	 */
	public function isAllowed(int $userId, string $type): bool
	{
		if (!General::allowedUserBySimpleNotificationSettings($userId, $type))
		{
			return false;
		}

		$encodedSetting = self::encodeName($this->module, $this->name, $type);

		$defaultSettings = self::getDefaultSettings();

		$defaultSettings = self::encodeSettings($defaultSettings);

		if (!array_key_exists($encodedSetting, $defaultSettings))
		{
			$encodedSetting = self::encodeName('im', 'default', $type);
		}

		$value = $defaultSettings[$encodedSetting] === 'Y' ? 'Y' : 'N';

		$result =
			OptionUserTable::query()
				->addSelect('USER_ID')
				->registerRuntimeField(
					'USER',
					new Reference(
						'USER',
						UserTable::class,
						Join::on('this.USER_ID', 'ref.ID'),
						['join_type' => Join::TYPE_INNER]
					)
				)
				->registerRuntimeField(
					'OPTION_STATE',
					new Reference(
						'OPTION_STATE',
						OptionStateTable::class,
						Join::on('this.NOTIFY_GROUP_ID', 'ref.GROUP_ID')
							->where('ref.NAME', $encodedSetting),
						['join_type' => Join::TYPE_LEFT]
					)
				)
				->whereExpr("COALESCE(%s, '$value') = 'Y'", ['OPTION_STATE.VALUE'])
				->where('USER_ID', $userId)
				->where('USER.ACTIVE', 'Y')
				->where('USER.IS_REAL_USER', 'Y')
				->fetch()
		;

		$result = $result !== false;

		if ($type !== self::PUSH)
		{
			return $result;
		}
		//checked push

		if ($result)
		{
			$query =
				PushTable::query()
				->addSelect('ID')
				->where('USER_ID', $userId)
				->setLimit(1)
			;
			$pushId = $query->fetch();

			return $pushId !== false;
		}

		return false;
	}

	/**
	 * Filters the list of users to whom can send a notification
	 *
	 * @throws SqlQueryException
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function filterAllowedUsers(array $userList, string $type): array
	{
		if (empty($userList))
		{
			return [];
		}

		$userList = General::filterAllowedUsersBySimpleNotificationSettings($userList, $type);
		if (empty($userList))
		{
			return [];
		}

		$encodedSetting = self::encodeName($this->module, $this->name, $type);

		$defaultSettings = self::getDefaultSettings();

		$defaultSettings = self::encodeSettings($defaultSettings);

		if (!array_key_exists($encodedSetting, $defaultSettings))
		{
			$encodedSetting = self::encodeName('im', 'default', $type);
		}

		$value = $defaultSettings[$encodedSetting] === 'Y' ? 'Y' : 'N';

		$filteredUsers = [];
		if (count($userList) < 1000)
		{
			$filteredUsers = $this->filterChunk($userList, $encodedSetting, $value);
		}
		else
		{
			$chunkList = array_chunk($userList, static::CHUNK_LENGTH);
			foreach ($chunkList as $chunk)
			{
				$filteredUsers = array_merge($filteredUsers, $this->filterChunk($chunk, $encodedSetting, $value));
			}
		}

		if ($type !== self::PUSH)
		{
			return $filteredUsers;
		}

		//checked push
		if (empty($filteredUsers))
		{
			return $filteredUsers;
		}

		$rowFilteredPushUsers =
			PushTable::query()
			->addSelect('USER_ID')
			->whereIn('USER_ID', $filteredUsers)
		;

		$filteredUsers = [];
		foreach ($rowFilteredPushUsers->exec() as $user)
		{
			$filteredUsers[] = (int)$user['USER_ID'];
		}

		return array_unique($filteredUsers);
	}

	private function filterChunk(array $userListChunk, $encodedSettingName, $value): array
	{
		$query =
			OptionUserTable::query()
				->addSelect('USER_ID')
				->registerRuntimeField(
					'USER',
					new Reference(
						'USER',
						UserTable::class,
						Join::on('this.USER_ID', 'ref.ID'),
						['join_type' => Join::TYPE_INNER]
					)
				)
				->registerRuntimeField(
					'OPTION_STATE',
					new Reference(
						'OPTION_STATE',
						OptionStateTable::class,
						Join::on('this.NOTIFY_GROUP_ID', 'ref.GROUP_ID')
							->where('ref.NAME', $encodedSettingName),
						['join_type' => Join::TYPE_LEFT]
					)
				)
				->whereExpr("COALESCE(%s, '$value') = 'Y'", ['OPTION_STATE.VALUE'])
				->whereIn('USER_ID', $userListChunk)
				->where('USER.ACTIVE', 'Y')
				->where('USER.IS_REAL_USER', 'Y');

		$filteredUsers = [];
		foreach ($query->exec() as $user)
		{
			$filteredUsers[] = (int)$user['USER_ID'];
		}

		return $filteredUsers;
	}

	/**
	 * @param string $feature
	 * @return bool
	 */
	public function checkDisableFeature(string $feature): bool
	{
		$defaultSettings = self::getDefaultSettings();
		if (isset($defaultSettings[$this->module]['NOTIFY'][$this->name]['DISABLED'][mb_strtoupper($feature)]))
		{
			return (bool)$defaultSettings[$this->module]['NOTIFY'][$this->name]['DISABLED'][mb_strtoupper($feature)];
		}

		return false;
	}

	public function getDefaultFeature(string $feature): bool
	{
		return (bool)self::getDefaultSettings()[$this->module]['NOTIFY'][$this->name][mb_strtoupper($feature)];
	}

	public function getLifetime(): int
	{
		return (int)self::getDefaultSettings()[$this->module]['NOTIFY'][$this->name]['LIFETIME'];
	}

	public function getValue(int $userId, string $type): bool
	{
		$encodedName = self::encodeName($this->module, $this->name, $type);

		if (!$encodedName)
		{
			return false;
		}

		$defaultSettings = self::encodeSettings(self::getDefaultSettings());

		$cachedPreset = Configuration::getUserPresetFromCache($userId);
		if (
			!empty($cachedPreset)
			&& isset($cachedPreset[Configuration::NOTIFY_GROUP])
			&& is_array($cachedPreset[Configuration::NOTIFY_GROUP])
		)
		{
			$notifySettings = $cachedPreset[Configuration::NOTIFY_GROUP]['settings'];

			if (!$notifySettings)
			{
				return $defaultSettings[$encodedName];
			}

			$notifySettings = self::encodeSettings($notifySettings);

			if (is_null($notifySettings[$encodedName]))
			{
				return $defaultSettings[$encodedName] === 'Y';
			}

			return $notifySettings[$encodedName] === 'Y';
		}

		$query =
			OptionStateTable::query()
				->addSelect('VALUE')
				->registerRuntimeField(
					'OPTION_USER',
					new Reference(
						'OPTION_USER',
						OptionUserTable::class,
						Join::on('this.GROUP_ID', 'ref.NOTIFY_GROUP_ID'),
						['join_type' => Join::TYPE_INNER]
					)
				)
				->where('OPTION_USER.USER_ID', $userId)
				->where('NAME', $encodedName)
		;

		$value = $query->fetch()['VALUE'];

		if (!$value)
		{
			return $defaultSettings[$encodedName] === 'Y';
		}

		return $value === 'Y';
	}

	/**
	 * The method get the full notification scheme (from all modules)
	 *
	 * @return array
	 */
	public static function getDefaultSettings(): array
	{
		if (!empty(self::$defaultSettings))
		{
			return self::$defaultSettings;
		}

		foreach (EventManager::getInstance()->findEventHandlers("im", "OnGetNotifySchema") as $events)
		{
			$event = ExecuteModuleEventEx($events);

			if (!is_array($event))
			{
				continue;
			}

			foreach ($event as $moduleId => $notifyType)
			{
				self::$defaultSettings[$moduleId]['NAME'] =
					isset($notifyType['NOTIFY'], $notifyType['NAME'])
						? $notifyType['NAME']
						: '';

				$notify = $notifyType['NOTIFY'] ?? $notifyType;

				foreach ($notify as $notifyEvent => $config)
				{
					$config['DISABLED'] = $config['DISABLED'] ?? [];

					if (!isset($config['PUSH']) || $config['PUSH'] === 'NONE')
					{
						$config['DISABLED'][] = self::PUSH;
					}

					$disabled['SITE'] = in_array(self::SITE, $config['DISABLED'], true);
					$disabled['MAIL'] = in_array(self::MAIL, $config['DISABLED'], true);
					$disabled['XMPP'] = in_array(self::XMPP, $config['DISABLED'], true);
					$disabled['PUSH'] = in_array(self::PUSH, $config['DISABLED'], true);

					$config['DISABLED'] = $disabled;

					// backward compatibility
					$config['SITE'] = !isset($config['SITE']) || $config['SITE'] == 'Y';
					$config['MAIL'] = !isset($config['MAIL']) || $config['MAIL'] == 'Y';
					$config['XMPP'] = !isset($config['XMPP']) || $config['XMPP'] == 'Y';
					$config['PUSH'] = isset($config['PUSH']) && $config['PUSH'] == 'Y';

					$config['LIFETIME'] = isset($config['LIFETIME']) ? (int)$config['LIFETIME'] : 0;

					self::$defaultSettings[$moduleId]['NOTIFY'][$notifyEvent] = $config;
				}
			}
		}

		return self::$defaultSettings;
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
					if ($eventSchema['DISABLED'][$type])
					{
						continue;
					}

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

	/**
	 * Gets the user's notification settings
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getUserSettings(int $userId): array
	{
		$defaultSettings = self::getDefaultSettings();

		$query =
			OptionStateTable::query()
				->setSelect(['NAME','VALUE'])
				->registerRuntimeField(
					'OPTION_USER_TABLE',
					new Reference(
						'OPTION_USER_TABLE',
						OptionUserTable::class,
						Join::on('this.GROUP_ID', 'ref.NOTIFY_GROUP_ID'),
						['join_type' => Join::TYPE_INNER]
					)
				)
				->where('OPTION_USER_TABLE.USER_ID', $userId)
				->whereLike('NAME', static::ENTITY.'%')
		;

		$rowSettings = [];
		foreach ($query->exec() as $rowSetting)
		{
			$rowSettings[] = $rowSetting;
		}

		if (empty($rowSettings))
		{
			return $defaultSettings;
		}

		$decodedSettings = static::decodeSettings($rowSettings);

		return array_replace_recursive($defaultSettings, $decodedSettings);
	}

	/**
	 * Gets the group's notification settings
	 *
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public static function getGroupSettings(int $groupId): array
	{
		$defaultSettings = self::getDefaultSettings();

		$query =
			OptionStateTable::query()
				->setSelect(['NAME','VALUE'])
				->where('GROUP_ID', $groupId)
				->whereLike('NAME', static::ENTITY . '%')
		;

		$settings = [];
		foreach ($query->exec() as $rowSetting)
		{
			$settings[] = $rowSetting;
		}

		if (empty($settings))
		{
			return $defaultSettings;
		}

		$settings = static::decodeSettings($settings);

		return array_replace_recursive($defaultSettings, $settings);
	}

	/**
	 * Updates the group's notification settings
	 *
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public static function updateGroupSettings(int $groupId, array $settings): void
	{
		if (!$settings)
		{
			return;
		}
		$encodedSettings = self::encodeSettings($settings);
		$defaultSettings = self::encodeSettings(self::getDefaultSettings());

		$query =
			OptionStateTable::query()
				->setSelect(['NAME', 'VALUE'])
				->where('GROUP_ID', $groupId)
				->whereLike('NAME', self::ENTITY . '%')
		;
		$addedSettings = [];
		$enabledSettings = [];
		$disabledSettings = [];
		foreach ($query->exec() as $row)
		{
			if (array_key_exists($row['NAME'], $encodedSettings))
			{
				if ($row['VALUE'] === $encodedSettings[$row['NAME']])
				{
					unset($encodedSettings[$row['NAME']]);
					continue;
				}
				if ($encodedSettings[$row['NAME']] === 'Y')
				{
					$enabledSettings[] = [
						'GROUP_ID' => $groupId,
						'NAME' => $row['NAME']
					];
					unset($encodedSettings[$row['NAME']]);
					continue;
				}
				if ($encodedSettings[$row['NAME']] === 'N')
				{
					$disabledSettings[] = [
						'GROUP_ID' => $groupId,
						'NAME' => $row['NAME']
					];
					unset($encodedSettings[$row['NAME']]);
				}
			}
		}

		foreach ($encodedSettings as $name => $value)
		{
			if (!array_key_exists($name, $defaultSettings))
			{
				continue;
			}

			$addedSettings[] = [
				'GROUP_ID' => $groupId,
				'NAME' => $name,
				'VALUE' => $value
			];
		}
		if (!empty($addedSettings))
		{
			OptionStateTable::addMulti($addedSettings, true);
		}
		if (!empty($enabledSettings))
		{
			OptionStateTable::updateMulti($enabledSettings, ['VALUE' => 'Y'], true);
		}
		if (!empty($disabledSettings))
		{
			OptionStateTable::updateMulti($disabledSettings, ['VALUE' => 'N'], true);
		}

	}

	/**
	 * Sets the group's notification settings
	 *
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public static function setSettings(int $groupId, array $settings = [], bool $forInitialize = false): void
	{
		if (empty($settings) && !$forInitialize)
		{
			return;
		}

		$selectedSettings = [];
		$query =
			OptionStateTable::query()
				->setSelect(['NAME', 'VALUE'])
				->where('GROUP_ID', $groupId)
				->whereLike('NAME', self::ENTITY . '%')
		;
		foreach ($query->exec() as $row)
		{
			$selectedSettings[$row['NAME']] = $row['VALUE'];
		}
		$defaultSettings = self::encodeSettings(self::getDefaultSettings());
		$encodedSettings = self::encodeSettings($settings);
		$encodedSettings = array_merge($defaultSettings, $encodedSettings);

		$rows = [];
		foreach ($encodedSettings as $name => $value)
		{
			if (!array_key_exists($name, $defaultSettings) || isset($selectedSettings[$name]))
			{
				continue;
			}

			$rows[] = [
				'GROUP_ID' => $groupId,
				'NAME' => $name,
				'VALUE' => $value
			];
		}

		OptionStateTable::addMulti($rows, true);
	}

	public static function getEventNames(): array
	{
		$names = [];
		$defaultSettings = self::getDefaultSettings();
		foreach ($defaultSettings as $moduleId => $notifyTypes)
		{
			$names[$moduleId]['NAME'] = $notifyTypes['NAME'];
			if ($notifyTypes['NAME'] == '')
			{
				$moduleObject = \CModule::CreateModuleObject($moduleId);
				$names[$moduleId]['NAME'] = $moduleObject->MODULE_NAME;
			}
			foreach ($notifyTypes['NOTIFY'] as $eventId => $event)
			{
				$names[$moduleId]['NOTIFY'][$eventId] = $event['NAME'];
			}
		}

		return $names;
	}

	/**
	 * Converts a flat array of templates into an array of notification schemes
	 *
	 * @param array $rowSettings
	 *
	 * @return array
	 */
	public static function decodeSettings(array $rowSettings): array
	{
		$decodedSettings = [];

		foreach ($rowSettings as $rowSetting)
		{
			if (!$rowSetting['NAME'])
			{
				continue;
			}

			$setting = self::decodeName($rowSetting['NAME']);

			if ($setting === null)
			{
				continue;
			}
			$module = $setting[self::MODULE];
			$name = $setting[self::NAME];

			if(!in_array((int)$setting[self::TYPE], [1,2,3,4]))
			{
				continue;
			}

			$type = self::getType((int)$setting[self::TYPE]);

			$decodedSettings[$module]['NOTIFY'][$name][$type] = $rowSetting['VALUE'] === 'Y';
		}

		return $decodedSettings;
	}

	/**
	 * Converts notification settings into a flat array,
	 * in which the key is a template, and the value is the value of the setting
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function encodeSettings(array $settings): array
	{
		$encodedSettings = [];

		foreach ($settings as $moduleName => $notifies)
		{
			if (!is_array($notifies))
			{
				continue;
			}

			foreach ($notifies as $notify)
			{
				if (!is_array($notify))
				{
					continue;
				}

				foreach ($notify as $notifyName => $types)
				{
					foreach ($types as $type => $value)
					{
						$setting = self::encodeName($moduleName, $notifyName, $type);

						if (!$setting || mb_strlen($setting) > 64)
						{
							continue;
						}

						$encodedSettings[$setting] = $value ? 'Y' : 'N';
					}

				}
			}
		}

		return $encodedSettings;
	}

	/**
	 * Gets an array with the decoded template
	 *
	 * @param string $setting
	 *
	 * @return array|null
	 */
	private static function decodeName(string $setting): ?array
	{
		$row = explode(static::SEPARATOR, $setting);

		if (!array_key_exists(self::MODULE, $row)
			|| !array_key_exists(self::NAME, $row)
			|| !array_key_exists(self::TYPE, $row)
		)
		{
			return null;
		}

		return $row;
	}

	/**
	 * Gets a template string with encoded data: no|module_name|event_name|type
	 *
	 * @param string $module
	 * @param string $name
	 * @param string $type
	 *
	 * @return string|null
	 */
	private static function encodeName(string $module, string $name, string $type): ?string
	{
		if ($type === '')
		{
			return null;
		}

		$postfix = self::getPostfix($type);

		if ($postfix === null)
		{
			return null;
		}

		return implode(
			static::SEPARATOR,
			[
				static::ENTITY,
				$module,
				$name,
				$postfix,
			]
		);
	}

	/**
	 * @param string $type
	 *
	 * @return int|null
	 */
	private static function getPostfix(string $type): ?int
	{
		return self::$types[mb_strtoupper($type)] ?? null;
	}

	/**
	 * @param int $postfix
	 *
	 * @return string|null
	 */
	private static function getType(int $postfix): ?string
	{
		$arr = array_flip(self::$types);

		return $arr[$postfix];
	}

}