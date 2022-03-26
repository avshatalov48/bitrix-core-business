<?php

namespace Bitrix\Im\Configuration;

use Bitrix\Im\Call\VideoStrategyType;
use Bitrix\Im\Model\OptionStateTable;
use Bitrix\Im\Model\OptionUserTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;

class General extends Base
{
	public const ENTITY = 'se';

	public const PRIVACY_RESULT_ALL = 'all';
	public const PRIVACY_RESULT_CONTACT = 'contact';
	public const PRIVACY_RESULT_NOBODY = 'nobody';
	
	/** @var int  */
	protected $userId;

	/** @var array */
	protected $userSettings;

	/** @var General[] */
	protected static $instanceList = [];

	/**
	 * This class should not be instantiated directly. Use one of the named constructors.
	 */
	protected function __construct()
	{

	}

	public static function createWithUserId(int $userId): General
	{
		if (!isset(self::$instanceList[$userId]))
		{
			$instance = new static();
			$instance->setUserId($userId);
			$instance->fillUserSettings();

			self::$instanceList[$userId] = $instance;
		}

		return self::$instanceList[$userId];
	}

	/**
	 * @param $settingName
	 *
	 * @return mixed
	 */
	public function getValue($settingName)
	{
		return $this->userSettings[$settingName];
	}

	protected function setUserId(int $userId): void
	{
		$this->userId = $userId;
	}

	protected function fillUserSettings(): void
	{
		$preset = Configuration::getUserPresetFromCache($this->userId);
		if (!empty($preset))
		{
			$preset['general']['settings'] =
				array_replace_recursive(self::getDefaultSettings(), $preset['general']['settings'])
			;

			$this->userSettings =  $preset['general']['settings'];
		}
		else
		{
			$this->userSettings = self::getUserSettings($this->userId);
		}
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function getDefaultSettings(): array
	{
		return [
			'status' => 'online',
			'backgroundImage' => false,
			'bxdNotify' => true,
			'sshNotify' => true,
			'generalNotify' => true,
			'trackStatus' => '',
			'nativeNotify' => true,
			'openDesktopFromPanel' => true,
			'viewOffline' => Option::get("im", "view_offline"),
			'viewGroup' => Option::get("im", "view_group"),
			'viewLastMessage' => true,
			'viewBirthday' => true,
			'viewCommonUsers' => true,
			'enableSound' => true,
			'enableBigSmile' => true,
			'enableDarkTheme' => 'auto',
			'isCurrentThemeDark' => false,
			'enableRichLink' => true,
			'linesTabEnable' => true,
			'linesNewGroupEnable' => false,
			'sendByEnter' => Option::get("im", "send_by_enter"),
			'correctText' => Option::get("im", "correct_text"),
			'panelPositionHorizontal' => Option::get("im", "panel_position_horizontal"),
			'panelPositionVertical' => Option::get("im", "panel_position_vertical"),
			'loadLastMessage' => true,
			'loadLastNotify' => Option::get("im", "load_last_notify"),
			'notifyAutoRead' => true,
			'notifyScheme' => 'simple',
			'notifySchemeLevel' => 'important',
			'notifySchemeSendSite' => true,
			'notifySchemeSendEmail' => !IsModuleInstalled('bitrix24'),
			'notifySchemeSendXmpp' => true,
			'notifySchemeSendPush' => true,
			'privacyMessage' => Option::get("im", "privacy_message"),
			'privacyChat' => Option::get("im", "privacy_chat"),
			'privacyCall' => Option::get("im", "privacy_call"),
			'privacySearch' => Option::get("im", "privacy_search"),
			'privacyProfile' => Option::get("im", "privacy_profile"),
			'callAcceptIncomingVideo' => VideoStrategyType::ALLOW_ALL,
			'next' => false,
		];
	}

	/**
	 * Encodes the received settings and enters them into the database and cache
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public static function setSettings(int $groupId, array $settings): void
	{
		if (!$settings)
		{
			return;
		}
		$settings = self::checkingValues($settings);
		$encodedSettings = self::encodeSettings($settings);

		$rows = [];
		foreach ($encodedSettings as $name => $value)
		{
			$rows[] = [
				'GROUP_ID' => $groupId,
				'NAME' => $name,
				'VALUE' => $value
			];
		}

		OptionStateTable::addMulti($rows, true);
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
		$groupId =
			OptionUserTable::query()
				->addSelect('GENERAL_GROUP_ID')
				->where('USER_ID', $userId)
				->fetch()['GENERAL_GROUP_ID']
		;

		if (!$groupId)
		{
			return $defaultSettings;
		}

		$query =
			OptionStateTable::query()
				->setSelect(['NAME', 'VALUE'])
				->where('GROUP_ID', $groupId)
				->whereLike('NAME', static::ENTITY.'%')
		;

		$settings = [];
		foreach ($query->exec() as $rowSetting)
		{
			$settings[$rowSetting['NAME']] = $rowSetting['VALUE'];
		}

		if(empty($settings))
		{
			return $defaultSettings;
		}

		$settings = static::decodeSettings($settings);

		return array_replace_recursive($defaultSettings, $settings);
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
			->setSelect(['NAME', 'VALUE'])
			->where('GROUP_ID', $groupId)
			->whereLike('NAME', static::ENTITY.'%')
		;

		$settings = [];
		foreach ($query->exec() as $rowSetting)
		{
			$settings[$rowSetting['NAME']] = $rowSetting['VALUE'];
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
	 * @param int $groupId
	 * @param array $settings
	 *
	 * @throws Exception
	 */
	public static function updateGroupSettings(int $groupId, array $settings): void
	{
		if ($settings === [])
		{
			return;
		}

		$settings = self::checkingValues($settings);
		$encodedSettings = self::encodeSettings($settings);

		$query =
			OptionStateTable::query()
				->setSelect(['NAME', 'VALUE'])
				->where('GROUP_ID', $groupId)
				->whereLike('NAME', self::ENTITY.'%')
		;

		foreach ($query->exec() as $row)
		{
			if (array_key_exists($row['NAME'], $encodedSettings))
			{
				if ($row['VALUE'] === $encodedSettings[$row['NAME']])
				{
					unset($encodedSettings[$row['NAME']]);
					continue;
				}
				OptionStateTable::update(
					[
						'GROUP_ID' => $groupId,
						'NAME' => $row['NAME']
					],
					['VALUE' => $encodedSettings[$row['NAME']]]
				);
				unset($encodedSettings[$row['NAME']]);
			}
		}

		$addedSettings = [];
		foreach ($encodedSettings as $name => $value)
		{
			$addedSettings[] = [
				'GROUP_ID' => $groupId,
				'NAME' => $name,
				'VALUE' => $value
			];
		}
		if ($addedSettings !== [])
		{
			OptionStateTable::addMulti($addedSettings, true);
		}
	}

	/**
	 * Converts general settings into a flat array,
	 * in which the key is a template, and the value is the value of the setting
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function encodeSettings(array $settings): array
	{
		$encodedSettings = [];
		foreach ($settings as $name => $value)
		{
			$encodeName = self::encodeName($name);

			if (mb_strlen($encodeName) > 64 || mb_strlen($value) > 255)
			{
				continue;
			}

			if ($value === true)
			{
				$encodedSettings[$encodeName] = 'Y';
			}
			elseif ($value === false)
			{
				$encodedSettings[$encodeName] = 'N';
			}
			else
			{
				$encodedSettings[$encodeName] = $value;
			}
		}

		return $encodedSettings;
	}

	/**
	 * Converts a flat array of templates into an array of general settings
	 *
	 * @param array $rowSettings
	 *
	 * @return array
	 */
	public static function decodeSettings(array $rowSettings): array
	{
		$decodedSettings = [];
		foreach ($rowSettings as $name => $value)
		{
			$decodedName = self::decodeName($name);
			if ($value === 'Y')
			{
				$decodedSettings[$decodedName] = true;
			}
			elseif ($value === 'N')
			{
				$decodedSettings[$decodedName] = false;
			}
			else
			{
				$decodedSettings[$decodedName] = $value;
			}
		}

		return $decodedSettings;
	}

	/**
	 * Gets a template string with encoded data: se|setting_name
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	private static function encodeName(string $name): string
	{
		return static::ENTITY . static::SEPARATOR . $name;
	}

	/**
	 * Gets an array with the decoded template
	 *
	 * @param string $setting
	 *
	 * @return string
	 */
	private static function decodeName(string $setting): string
	{
		return str_replace(static::ENTITY . static::SEPARATOR, '', $setting);
	}

	/**
	 *
	 * @param array $settings
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function checkingValues(array $settings): array
	{
		$verifiedSettings = [];

		$defaultSettings = self::getDefaultSettings();
		foreach($settings as $name => $value)
		{
			if (!array_key_exists($name , $defaultSettings))
			{
				continue;
			}

			switch ($name)
			{
				case 'status':
					$verifiedSettings[$name] =
						in_array($value, ['online', 'dnd', 'away'])
							? $value
							: $defaultSettings[$name]
					;

					break;
				case 'panelPositionHorizontal':
					$verifiedSettings[$name] =
						in_array($value, ['left', 'center', 'right'])
							? $value
							: $defaultSettings[$name]
					;

					break;
				case 'panelPositionVertical':
					$verifiedSettings[$name] =
						in_array($value, ['top', 'bottom'])
							? $value
							: $defaultSettings[$name]
					;

					break;
				case 'notifyScheme':
					$verifiedSettings[$name] =
						in_array($value, ['simple', 'expert'])
							? $value
							: $defaultSettings[$name]
					;

					break;
				case 'enableDarkTheme':
					$verifiedSettings[$name] =
						in_array($value, ['auto', 'light', 'dark'])
							? $value
							: $defaultSettings[$name]
					;

					break;
				case 'privacyMessage':
				case 'privacyChat':
				case 'privacyCall':
				case 'privacySearch':
					$verifiedSettings[$name] =
						in_array($value, [self::PRIVACY_RESULT_ALL, self::PRIVACY_RESULT_CONTACT], true)
							? $value
							: $defaultSettings[$name]
					;

					break;
				case 'privacyProfile':
					$verifiedSettings[$name] =
						in_array(
							$value,
							[
								self::PRIVACY_RESULT_ALL,
								self::PRIVACY_RESULT_CONTACT,
								self::PRIVACY_RESULT_NOBODY
							],
							true
						)
							? $value
							: $defaultSettings[$name];

					break;
				case 'backgroundImage':
					$verifiedSettings[$name] = $value;

					break;
				case 'notifySchemeLevel':
					$verifiedSettings[$name] =
						in_array($value, ['normal', 'important'])
							? $value
							: $defaultSettings[$name];

					break;
				case 'trackStatus':
					$status = explode(',', $value);
					foreach ($status as $key => $val)
					{
						if ($val !== 'all')
						{
							$status[$key] = (int)$val;
							if ($status[$key] === 0)
							{
								unset($status[$key]);
							}
						}
					}
					$verifiedSettings[$name] = implode(',', $status);

					break;
				case 'callAcceptIncomingVideo':
					$verifiedSettings[$name] =
						in_array($value, VideoStrategyType::getList())
							? $value
							: $defaultSettings[$name]
					;

					break;
				case 'sendByEnter': // for legacy
					if ($value === 'Y')
					{
						$verifiedSettings[$name] = true;

						break;
					}
					//'break' is missing  specially
				case 'enableSound': // for legacy
					if ($value === 'N')
					{
						$verifiedSettings[$name] = false;

						break;
					}
					//'break' is missing  specially
				default:
					if (array_key_exists($name, $defaultSettings))
					{
						$verifiedSettings[$name] = is_bool($value) ? $value : $defaultSettings[$name];
					}

					break;
			}
		}

		return $verifiedSettings;
	}

}