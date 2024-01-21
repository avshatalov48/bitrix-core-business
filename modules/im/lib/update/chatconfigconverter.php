<?php
namespace Bitrix\Im\Update;

use Bitrix\Im\Configuration\Configuration;
use Bitrix\Im\Configuration\Department;
use Bitrix\Im\Configuration\General;
use Bitrix\Im\Configuration\Manager;
use Bitrix\Im\Configuration\Notification;
use Bitrix\Im\Model\OptionAccessTable;
use Bitrix\Im\Model\OptionGroupTable;
use Bitrix\Im\Model\OptionStateTable;
use Bitrix\Im\Model\OptionUserTable;
use Bitrix\Im\Model\StatusTable;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\UserTable;

class ChatConfigConverter extends Stepper
{
	public const OPTION_NAME = 'im_chat_config_converter';
	protected static $moduleId = 'im';
	private const ITERATION_LIMIT = 500;

	private static $notifyDefaultSettings = [];
	private static $generalDefaultSettings = [];

	public function execute(array &$option)
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return false;
		}

		$configTruncate = Option::get(self::$moduleId, 'config_truncate', 'N');
		if ($configTruncate !== 'Y')
		{
			Option::set(self::$moduleId, 'migration_to_new_settings', 'N');

			$connection = Application::getConnection();
			$connection->query("TRUNCATE TABLE b_im_option_user");
			$connection->query("TRUNCATE TABLE b_im_option_state");
			$connection->query("TRUNCATE TABLE b_im_option_access");
			$connection->query("TRUNCATE TABLE b_im_option_group");

			Option::set(self::$moduleId, 'config_truncate', 'Y');
		}

		$params = Option::get(self::$moduleId, self::OPTION_NAME, '');
		$params = ($params !== '' ? @unserialize($params, ['allowed_classes' => false]) : []);
		$params = is_array($params) ? $params : [];

		$unconvertedUsers = Option::get(self::$moduleId, 'unconverted_settings_users', '');
		$unconvertedUsers = $unconvertedUsers !== '' ? @unserialize($unconvertedUsers, ['allowed_classes' => false]) : [];
		$unconvertedUsers = is_array($unconvertedUsers) ? $unconvertedUsers : [];

		if (empty($params))
		{
			Option::set(self::$moduleId, 'migration_to_new_settings', 'N');
			Configuration::cleanAllCache();
			$unconvertedUsers = [];

			$isIntranetIncluded = Loader::includeModule('intranet');

			$defaultGroupId = $this->getDefaultGroupId();
			if (!$defaultGroupId)
			{
				$defaultGroupId = Configuration::createDefaultPreset();
			}
			elseif ($isIntranetIncluded)
			{
				$this->updateDefaultAccessCode($defaultGroupId);
			}

			$userCount = UserTable::getCount([
				'=IS_REAL_USER' => 'Y'
			]);
			$convertedUserCount = OptionUserTable::getCount();

			$unconvertedUserCount = $userCount - $convertedUserCount;

			if ($unconvertedUserCount < 1)
			{
				if (empty($unconvertedUsers))
				{
					Option::delete(self::$moduleId, ['name' => 'unconverted_settings_users']);
				}

				Option::set(self::$moduleId, 'migration_to_new_settings', 'Y');
				Option::delete(self::$moduleId, ['name' => self::OPTION_NAME]);

				return false;
			}

			$params = [
				'lastId' => 0,
				'number' => 0,
				'defaultGroup' => $defaultGroupId,
				'includeIntranet' => $isIntranetIncluded,
				'count' => $unconvertedUserCount
			];
		}

		$query =
			UserTable::query()
				->addSelect('ID')
				->registerRuntimeField(
					'OPTION_USER',
					new Reference(
						'OPTION_USER',
						OptionUserTable::class,
						Join::on('this.ID', '=', 'ref.USER_ID'),
						['join_type' => Join::TYPE_LEFT]
					)
				)
				->where('IS_REAL_USER', 'Y')
				->where('OPTION_USER.USER_ID', null)
				->where('ID', '>', $params['lastId'])
				->setOrder(['ID' => 'ASC'])
				->setLimit(self::ITERATION_LIMIT)
		;

		$userIds = [];
		foreach ($query->exec() as $row)
		{
			$userIds[] = (int)$row['ID'];
		}

		if (empty($userIds))
		{
			if (empty($unconvertedUsers))
			{
				Option::delete(self::$moduleId, ['name' => 'unconverted_settings_users']);
			}

			Option::set(self::$moduleId, 'migration_to_new_settings', 'Y');
			Option::delete(self::$moduleId, ['name' => 'last_converted_user']);
			Option::delete(self::$moduleId, ['name' => self::OPTION_NAME]);

			return false;
		}

		$lastKeyId = array_key_last($userIds);
		$params['lastId']  = $userIds[$lastKeyId];

		foreach ($userIds as $userId)
		{
			try
			{
				$groupId = $this->isUserGroupExist($userId);
				if ($groupId)
				{
					$this->bindExistingGroupToUser($userId, $groupId, $params['defaultGroup']);

					continue;
				}
				$notifySettings = \CUserOptions::GetOption('im', 'notify', [], $userId);
				$generalSettings = \CUserOptions::GetOption('im', 'settings', [], $userId);

				if (empty($notifySettings) && empty($generalSettings))
				{
					OptionUserTable::add([
						'USER_ID' => $userId,
						'NOTIFY_GROUP_ID' => $params['defaultGroup'],
						'GENERAL_GROUP_ID' => $params['defaultGroup']
					]);
				}
				else
				{
					$this->createPersonalPreset($userId, $notifySettings, $generalSettings, $params['defaultGroup']);
				}
				$params['lastConvertedUser'] = $userId;
			}
			catch (\Exception $e)
			{
				$unconvertedUsers[] = $userId;
			}
			$params['number']++;
		}

		$option['count'] = $params['count'];
		$option['progress'] = ($params['number'] * 100) / (int)$params['count'];
		$option['steps'] = $params['number'];

		Option::set(self::$moduleId, 'last_converted_user', $params['lastConvertedUser']);
		Option::set(self::$moduleId, 'unconverted_settings_users', serialize($unconvertedUsers));
		Option::set(self::$moduleId, self::OPTION_NAME, serialize($params));

		return true;

	}

	private function convertNotifySettings(array $oldUserSettings): array
	{
		if (empty(self::$notifyDefaultSettings))
		{
			self::$notifyDefaultSettings = Notification::getSimpleNotifySettings(General::getDefaultSettings());
		}

		$newFormatSettings = [];
		foreach ($oldUserSettings as $name => $value)
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

		$newSettings = \Bitrix\Im\Configuration\Notification::decodeSettings($newFormatSettings);
		return array_replace_recursive(self::$notifyDefaultSettings, $newSettings);

	}

	private function convertGeneralSettings(array $oldUserSettings): array
	{
		if (empty(self::$generalDefaultSettings))
		{
			self::$generalDefaultSettings = General::getDefaultSettings();
		}

		return array_replace_recursive(self::$generalDefaultSettings, $oldUserSettings);
	}

	private function createDefaultPreset($includeIntranet): int
	{
		$defaultGroupId =
			OptionGroupTable::add([
				'NAME' => Configuration::DEFAULT_PRESET_NAME,
				'SORT' => 0,
				'CREATE_BY_ID' => 0,
			])
				->getId()
		;
		$generalDefaultSettings = General::getDefaultSettings();
		General::setSettings($defaultGroupId, $generalDefaultSettings);

		$notifySettings = Notification::getSimpleNotifySettings($generalDefaultSettings);
		Notification::setSettings($defaultGroupId, $notifySettings);


		if ($includeIntranet)
		{
			$topDepartmentId = Department::getTopDepartmentId();
			OptionAccessTable::add([
				'GROUP_ID' => $defaultGroupId,
				'ACCESS_CODE' => $topDepartmentId ? 'DR' . $topDepartmentId : 'AU'
			]);
		}

		return (int)$defaultGroupId;
	}

	private function createPersonalPreset($userId, $notifySettings, $generalSettings, $defaultGroupId): void
	{
		$userGroupId =
			OptionGroupTable::add([
				'USER_ID' => $userId,
				'SORT' => Configuration::USER_PRESET_SORT,
				'CREATE_BY_ID' => 0
			])
				->getId()
		;

		$isSettingsChanged = false;

		try
		{
			if (!empty($generalSettings))
			{
				$generalSettings = $this->convertGeneralSettings($generalSettings);
				$row =
					StatusTable::query()
						->addSelect('STATUS')
						->where('USER_ID', $userId)
						->fetch()
				;
				if ($row)
				{
					$generalSettings['status'] = $row['STATUS'];
				}

				General::setSettings($userGroupId, $generalSettings);

				if ($generalSettings['notifyScheme'] === 'simple' || empty($notifySettings))
				{
					$notifySettings = Notification::getSimpleNotifySettings($generalSettings);
				}
				else
				{
					$notifySettings = $this->convertNotifySettings($notifySettings);
				}

				Notification::setSettings($userGroupId, $notifySettings);
				$isSettingsChanged = true;
			}
		}
		catch (\Exception $exception) {}

		OptionUserTable::add([
			'USER_ID' => $userId,
			'NOTIFY_GROUP_ID' => $isSettingsChanged ? $userGroupId : $defaultGroupId,
			'GENERAL_GROUP_ID' => $isSettingsChanged ? $userGroupId : $defaultGroupId
		]);

		OptionAccessTable::add([
			'GROUP_ID' => $userGroupId,
			'ACCESS_CODE' => 'U' . $userId
		]);
	}

	private function getDefaultGroupId()
	{
		$defaultGroupId =
			OptionGroupTable::query()
				->addSelect('ID')
				->where('NAME', Configuration::DEFAULT_PRESET_NAME)
				->fetch();

		return $defaultGroupId ? $defaultGroupId['ID'] : false;
	}

	private function updateDefaultAccessCode($defaultGroupId): void
	{
		$topDepartmentId = Department::getTopDepartmentId();
		$accessCode = $topDepartmentId ? 'DR' . $topDepartmentId : 'AU';

		OptionAccessTable::update($defaultGroupId, [
			'ACCESS_CODE' => $accessCode
		]);
	}

	private function isUserGroupExist($userId)
	{
		$query =
			OptionGroupTable::query()
				->addSelect('ID')
				->where('USER_ID', $userId);

		$row = $query->fetch();
		if (!$row)
		{
			return false;
		}

		return (int)$row['ID'];
	}

	private function bindExistingGroupToUser($userId, $groupId, $defaultGroupId): void
	{
		$notifyCount = OptionStateTable::getCount([
			'=GROUP_ID' => $groupId,
			'%=NAME' => 'no%'
		]);

		$generalCount = OptionStateTable::getCount([
			'=GROUP_ID' => $groupId,
			'%=NAME' => 'se%'
		]);

		$notifyGroupId = $notifyCount > 0 ? $groupId : $defaultGroupId;
		$generalGroupId = $generalCount > 0 ? $groupId : $defaultGroupId;

		if ($notifyGroupId === $groupId && $generalGroupId === $groupId)
		{
			$insertFields = [
				'USER_ID' => $userId,
				'GENERAL_GROUP_ID' => $generalGroupId,
				'NOTIFY_GROUP_ID' => $notifyGroupId
			];
			$updateFields = [
				'GENERAL_GROUP_ID' => $generalGroupId,
				'NOTIFY_GROUP_ID' => $notifyGroupId
			];

			OptionUserTable::merge($insertFields, $updateFields);

			return;
		}

		$generalSettings = \CUserOptions::GetOption('im', 'settings', [], $userId);

		if ($generalGroupId === $defaultGroupId && !empty($generalSettings))
		{
			$generalGroupId = $groupId;
			$generalSettings = $this->convertGeneralSettings($generalSettings);

			General::setSettings($generalGroupId, $generalSettings);
		}

		if ($notifyGroupId === $defaultGroupId)
		{
			$notifySettings = \CUserOptions::GetOption('im', 'notify', [], $userId);

			if ($generalSettings['notifyScheme'] === 'simple')
			{
				$generalSettings = $this->convertGeneralSettings($generalSettings);
				$notifySettings = Notification::getSimpleNotifySettings($generalSettings);
			}

			if (!empty($notifySettings))
			{
				$notifyGroupId = $groupId;
				$notifySettings = $this->convertNotifySettings($notifySettings);
				Notification::setSettings($notifyGroupId, $notifySettings);
			}
		}

		$insertFields = [
			'USER_ID' => $userId,
			'GENERAL_GROUP_ID' => $generalGroupId,
			'NOTIFY_GROUP_ID' => $notifyGroupId
		];
		$updateFields = [
			'GENERAL_GROUP_ID' => $generalGroupId,
			'NOTIFY_GROUP_ID' => $notifyGroupId
		];

		OptionUserTable::merge($insertFields, $updateFields);
	}

}