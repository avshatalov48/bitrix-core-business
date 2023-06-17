<?php
namespace Bitrix\Im\Configuration;

use Bitrix\Iblock\ORM\Query;
use Bitrix\Im\Model\OptionAccessTable;
use Bitrix\Im\Model\OptionGroupTable;
use Bitrix\Im\Model\OptionStateTable;
use Bitrix\Im\Model\OptionUserTable;
use Bitrix\Im\V2\Settings\CacheManager;
use Bitrix\Im\V2\Settings\UserConfiguration;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Entity\Query\Filter\ConditionTree;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserAccessTable;
use Exception;

class Configuration
{
	public const DEFAULT_PRESET_NAME = 'default';
	public const DEFAULT_PRESET_SETTING_NAME = 'default_configuration_preset';
	protected const DEFAULT_SORT = 100;

	public const USER_PRESET_SORT = 1000000;

	public const NOTIFY_GROUP = 'notify';
	public const GENERAL_GROUP = 'general';

	protected const CACHE_TTL = 31536000; //one year
	protected const CACHE_NAME = 'user_preset';
	protected const CACHE_DIR = '/im/option/';

	protected static $defaultPresetId = null;

	public static function getDefaultPresetId(): int
	{
		if (self::$defaultPresetId)
		{
			return self::$defaultPresetId;
		}
		$row =
			OptionGroupTable::query()
				->addSelect('ID')
				->where('NAME', self::DEFAULT_PRESET_NAME)
				->fetch()
		;
		self::$defaultPresetId = (int)$row['ID'];

		return self::$defaultPresetId;
	}
	/**
	 *
	 * Gets the current preset of the user
	 *
	 * @deprecated
	 * @see \Bitrix\Im\V2\Settings\UserConfiguration
	 * @param int $userId
	 * @return array{notify: array, general: array}
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getUserPreset(int $userId): array
	{
		$preset = self::getUserPresetFromCache($userId);

		if (!empty($preset))
		{
			$preset['notify']['settings'] =
				array_replace_recursive(
					Notification::getDefaultSettings(),
					($preset['notify']['settings'] ?? [])
				)
			;

			$preset['general']['settings'] =
				array_replace_recursive(
					General::getDefaultSettings(),
					($preset['general']['settings'] ?? [])
				)
			;

			return $preset;
		}

		$query = OptionGroupTable::query()
			->setSelect([
				'ID',
				'NAME',
				'SORT',
				'USER_ID',
				'NOTIFY_GROUP_ID' => 'OPTION_USER.NOTIFY_GROUP_ID',
				'GENERAL_GROUP_ID' => 'OPTION_USER.GENERAL_GROUP_ID'
			])
			->registerRuntimeField(
				'OPTION_USER',
				new Reference(
					'OPTION_USER',
					OptionUserTable::class,
					Join::on('this.ID', 'ref.NOTIFY_GROUP_ID')
						->logic('or')
						->whereColumn('this.ID', 'ref.GENERAL_GROUP_ID'),
					['join_type' => Join::TYPE_INNER]
				)
			)
			->where('OPTION_USER.USER_ID', $userId)
			->setLimit(2)
		;

		$rows = $query->fetchAll();

		if (empty($rows))
		{
			return self::getDefaultUserPreset();
		}

		$notifyPreset = [];
		$generalPreset = [];
		foreach ($rows as $preset)
		{
			if ((int)$preset['ID'] === (int)$preset['NOTIFY_GROUP_ID'])
			{
				$notifyPreset = [
					'id' => $preset['ID'],
					'name' => self::getPresetName($preset),
					'sort' => $preset['SORT'],
					'userId' => $preset['USER_ID'],
					'settings' => Notification::getGroupSettings((int)$preset['ID'])
				];
			}

			if ((int)$preset['ID'] === (int)$preset['GENERAL_GROUP_ID'])
			{
				$generalPreset = [
					'id' => $preset['ID'],
					'name' => self::getPresetName($preset),
					'sort' => $preset['SORT'],
					'userId' => $preset['USER_ID'],
					'settings' => General::getGroupSettings((int)$preset['ID'])
				];
			}
		}

		//TODO extraordinary bag with not existing group from database
		if (empty($notifyPreset))
		{
			$notifyPreset = self::getDefaultUserPreset()['notify'];
		}
		if (empty($generalPreset))
		{
			$generalPreset = self::getDefaultUserPreset()['general'];
		}

		$userPreset = [
			'notify' => $notifyPreset,
			'general' => $generalPreset
		];
		self::setUserPresetInCache($userId, $userPreset);

		return $userPreset;
	}

	/**
	 * Gets a preset by its id
	 *
	 * @param int $id
	 * @return array{id: int, name: string, sort: int, settings: array}
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getPreset(int $id): array
	{
		$settings['notify'] = Notification::getGroupSettings($id);
		$settings['general'] = General::getGroupSettings($id);

		$row =
			OptionGroupTable::query()
				->setSelect([
					'NAME',
					'SORT',
					'USER_ID'
				])
				->where('ID', $id)
				->fetch()
		;

		return [
			'id' => $id,
			'name' => $row['NAME'],
			'sort' => (int)$row['SORT'],
			'userId' => $row['USER_ID'],
			'settings' => $settings,
		];
	}

	public static function getDefaultUserPreset(): array
	{
		$generalPreset = [
			'settings' => General::getDefaultSettings(),
			'id' => self::getDefaultPresetId(),
			'sort' => 0,
			'name' => self::getPresetName(['NAME' =>'default'])
		];

		$notifyPreset = [
			'settings' => Notification::getDefaultSettings(),
			'id' => self::getDefaultPresetId(),
			'sort' => 0,
			'name' => self::getPresetName(['NAME' =>'default'])
		];

		return [
			'notify' => $notifyPreset,
			'general' => $generalPreset
		];
	}

	/**
	 * @param int $userId
	 * @return array{notify: int, general: int}|null
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getUserPresetIds(int $userId): ?array
	{
		$ids =
			OptionUserTable::query()
			->setSelect(['NOTIFY_GROUP_ID', 'GENERAL_GROUP_ID'])
			->where('USER_ID', $userId)
			->fetch()
		;

		if ($ids === false)
		{
			return null;
		}

		return [
			'notify' => (int)$ids['NOTIFY_GROUP_ID'],
			'general' => (int)$ids['GENERAL_GROUP_ID']
		];
	}

	/**
	 * Gets a list of presets available to the user
	 *
	 * @param int $userId
	 * @return array
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getListAvailablePresets(int $userId): array
	{
		$query =
			OptionGroupTable::query()
				->setSelect(['ID', 'NAME'])
				->registerRuntimeField(
					'OPTION_ACCESS',
					new Reference(
						'OPTION_ACCESS',
						OptionAccessTable::class,
						Join::on('this.ID', 'ref.GROUP_ID'),
						['join_type' => Join::TYPE_INNER]
					)
				)
				->registerRuntimeField(
					'USER_ACCESS',
					new Reference(
						'USER_ACCESS',
						UserAccessTable::class,
						Join::on('this.OPTION_ACCESS.ACCESS_CODE', 'ref.ACCESS_CODE'),
						['join_type' => Join::TYPE_INNER]
					)
				)
				->where('USER_ACCESS.USER_ID', $userId)
		;
		$presets = [];
		foreach ($query->exec() as $row)
		{
			$presets[] = [
				'id' => $row['ID'],
				'name' => self::getPresetName($row),
			];
		}

		return $presets;
	}

	/**
	 * Creates a personal preset of the user with the maximum priority,
	 * sets the settings in the database and exposes the resulting preset to the user
	 *
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 * @throws Exception
	 */
	public static function createUserPreset(int $userId, array $settings = []): int
	{
		$groupId = self::createPersonalGroup($userId);

		if (empty($settings))
		{
			return $groupId;
		}

		Notification::setSettings($groupId, $settings['notify']);
		General::setSettings($groupId, $settings['general']);

		$bindingPresetToUser = [];
		if (!empty($settings['notify']))
		{
			$bindingPresetToUser['NOTIFY_GROUP_ID'] = $groupId;
		}
		if (!empty($settings['general']))
		{
			$bindingPresetToUser['GENERAL_GROUP_ID'] = $groupId;
		}

		if (!empty($bindingPresetToUser))
		{
			OptionUserTable::update($userId, $bindingPresetToUser);
		}


		return $groupId;
	}

	/**
	 * Creates a general preset for a department or a list of users by access codes with the selected priority,
	 * enters the settings into the database and sets the resulting group to users
	 * if the priority of their current group is not greater than this one.
	 * If force is true, the priority of the current presets is not taken into account
	 *
	 * @param array $accessCodes
	 * @param array $settings
	 * @param string $presetName
	 * @param int $creatorId
	 * @param int $sort
	 * @param bool $force
	 *
	 * @return int|null
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function createSharedPreset(
		array $accessCodes,
		string $presetName,
		int $creatorId,
		array $settings = [],
		int $sort = self::DEFAULT_SORT,
		bool $force = false
	): ?int
	{
		if ($sort >= self::USER_PRESET_SORT)
		{
			return null;
		}

		$newGroupId = self::createSharedGroup($presetName, $accessCodes, $creatorId, $sort);

		if (empty($settings))
		{
			return $newGroupId;
		}

		Notification::setSettings($newGroupId, $settings['notify']);
		General::setSettings($newGroupId, $settings['general']);

		$rowCandidates =
			UserAccessTable::query()
				->addSelect('USER_ID')
				->registerRuntimeField(
				   'OPTION_USER_TABLE',
				   new Reference(
					   'OPTION_USER_TABLE',
					   OptionUserTable::class,
					   Join::on('this.USER_ID', 'ref.USER_ID'),
					   ['join_type' => Join::TYPE_INNER]
				   )
				)
				->whereIn('ACCESS_CODE', $accessCodes)
		;
		$candidates = [];
		foreach ($rowCandidates->exec() as $rowCandidate)
		{
			$candidates[] = $rowCandidate['USER_ID'];
		}

		if ($force)
		{
			OptionUserTable::updateMulti(
				$candidates,
				[
					'NOTIFY_GROUP_ID' => $newGroupId,
					'GENERAL_GROUP_ID' => $newGroupId
				],
				true
			);

			return $newGroupId;
		}
		//the priority of the group must be taken into account
		self::updateGroupForUsers($newGroupId, $candidates, $sort, self::NOTIFY_GROUP);
		self::updateGroupForUsers($newGroupId, $candidates, $sort, self::GENERAL_GROUP);
		self::cleanUsersCache($candidates);

		return $newGroupId;
	}

	/**
	 * Updates the name of the shared preset by its ID, also updates the date of change and who changed the preset
	 *
	 * @throws Exception
	 */
	public static function updateNameSharedPreset(int $presetId, int $modifyId, string $newName): void
	{
		OptionGroupTable::update(
			$presetId,
			[
				'NAME' => $newName,
				'MODIFY_BY_ID' => $modifyId
			]
		);

		$query =
			OptionUserTable::query()
				->addSelect('USER_ID')
				->where(\Bitrix\Main\ORM\Query\Query::filter()
					->logic('or')
					->where('GENERAL_GROUP_ID', $presetId)
					->where('NOTIFY_GROUP_ID', $presetId)
				)
		;
		$usersId = [];
		foreach($query->exec() as $row)
		{
			$usersId[] = (int)$row['USER_ID'];
		}

		self::cleanUsersCache($usersId);
	}

	/**
	 * Updates the preset settings by its ID, also updates the date of change and who changed the preset
	 *
	 * @throws SystemException
	 * @throws ArgumentException
	 * @throws Exception
	 */
	public static function updatePresetSettings(int $presetId, int $modifyId, array $settings): void
	{
		Notification::updateGroupSettings($presetId, $settings['notify']);
		General::updateGroupSettings($presetId, $settings['general']);

		$query =
			OptionUserTable::query()
				->addSelect('USER_ID')
				->where(\Bitrix\Main\ORM\Query\Query::filter()
					->logic('or')
					->where('GENERAL_GROUP_ID', $presetId)
					->where('NOTIFY_GROUP_ID', $presetId)
				)
		;
		$usersId = [];
		foreach($query->exec() as $row)
		{
			$usersId[] = (int)$row['USER_ID'];
		}

		self::cleanUsersCache($usersId);

		OptionGroupTable::update(
			$presetId,
			[
				'MODIFY_BY_ID' => $modifyId
			]
		);
	}

	/**
	 * Deletes the selected preset
	 *
	 * @throws SystemException
	 */
	public static function deletePreset(int $presetId): bool
	{
		if ($presetId === self::getDefaultPresetId())
		{
			return false;
		}

		self::replaceGroupForUsers($presetId, self::NOTIFY_GROUP);
		self::replaceGroupForUsers($presetId, self::GENERAL_GROUP);

		self::deleteGroup($presetId);

		return true;
	}

	/**
	 * Sets an existing preset to the users taking into account or not the priority of their current preset
	 *
	 * @throws ObjectPropertyException
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public static function setExistingPresetToUsers(int $presetId, array $userList, bool $force = false): void
	{
		//the priority of the group must be taken into account
		if (!$force)
		{
			$sort =
				OptionGroupTable::query()
					->addSelect('SORT')
					->where('ID', $presetId)
					->fetch()['SORT'];

			$query =
				OptionUserTable::query()
					->addSelect('USER_ID')
					->registerRuntimeField(
						'OPTION_GROUP',
						new Reference(
							'OPTION_GROUP',
							OptionGroupTable::class,
							Join::on('this.GROUP_ID', 'ref.ID'),
							['join_type' => Join::TYPE_INNER]
						)
					)
					->whereIn('USER_ID', $userList)
					->where('OPTION_GROUP.SORT', '>=', (int)$sort)
			;

			$users = [];
			foreach ($query->exec() as $user)
			{
				$users = $user['USER_ID'];
			}
			$userList = $users;
		}

		OptionUserTable::updateMulti(
			$userList,
			[
				'NOTIFY_GROUP_ID' => $presetId,
				'GENERAL_GROUP_ID' => $presetId
			],
			true
		);

		self::cleanUsersCache($userList);
	}

	/**
	 * Sets a different preset for the user
	 *
	 * @throws Exception
	 */
	public static function chooseExistingPreset(int $presetId, int $userId): void
	{
		OptionUserTable::update(
			$userId,
			[
				'NOTIFY_GROUP_ID' => $presetId,
				'GENERAL_GROUP_ID' => $presetId
			]
		);

		CacheManager::getUserCache($userId)->clearCache();
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private static function replaceGroupForUsers(int $groupId, string $groupType): void
	{
		$rowUsers =
			OptionUserTable::query()
				->addSelect('USER_ID')
		;

		if ($groupType === self::NOTIFY_GROUP)
		{
			$rowUsers->where('NOTIFY_GROUP_ID', $groupId);
		}
		elseif ($groupType === self::GENERAL_GROUP)
		{
			$rowUsers->where('GENERAL_GROUP_ID', $groupId);
		}

		$usersId = [];
		foreach ($rowUsers->exec() as $user)
		{
			$usersId[] = (int)$user['USER_ID'];
			self::replaceGroupForUser((int)$user['USER_ID'], $groupId, $groupType);
		}

		self::cleanUsersCache($usersId);
	}


	/**
	 * Finds an available group with the highest priority without taking into account the selected group and replaces it
	 *
	 * @throws SystemException
	 * @throws Exception
	 */
	private static function replaceGroupForUser(int $userId, int $groupId, string $groupType): void
	{
		$query =
			OptionGroupTable::query()
				->addSelect('ID')
				->registerRuntimeField(
					'OPTION_ACCESS',
					new Reference(
						'OPTION_ACCESS',
						OptionAccessTable::class,
						Join::on('this.ID', 'ref.GROUP_ID'),
						['join_type' => Join::TYPE_INNER]
					)
				)
				->registerRuntimeField(
					'USER_ACCESS',
					new Reference(
						'USER_ACCESS',
						UserAccessTable::class,
						Join::on('this.OPTION_ACCESS.ACCESS_CODE', 'ref.ACCESS_CODE'),
						['join_type' => Join::TYPE_INNER]
					)
				)
				->registerRuntimeField(
					'OPTION_USER',
					new Reference(
						'OPTION_USER',
						OptionUserTable::class,
						Join::on('this.USER_ACCESS.USER_ID', 'ref.USER_ID'),
						['join_type' => Join::TYPE_INNER]
					)
				)
				->registerRuntimeField(
					'OPTION_STATE',
					new Reference(
						'OPTION_STATE',
						OptionStateTable::class,
						Join::on('this.ID', 'ref.GROUP_ID'),
						['join_type' => Join::TYPE_INNER]
					)
				)
				->where('OPTION_USER.USER_ID', $userId)
				->where('ID', '!=', $groupId)
				->where(Query::expr()->count('OPTION_STATE.NAME'), '>', 0)
				->setOrder(['SORT' => 'DESC', 'ID' => 'DESC'])
				->setLimit(1)
		;
		$replacedGroup = $query->fetch()['ID'];

		if ($groupType === self::NOTIFY_GROUP)
		{
			OptionUserTable::update($userId, ['NOTIFY_GROUP_ID' => $replacedGroup]);
		}
		elseif ($groupType === self::GENERAL_GROUP)
		{
			OptionUserTable::update($userId, ['GENERAL_GROUP_ID' => $replacedGroup]);
		}
	}

	/**
	 * Deletes all rows associated with this group
	 *
	 * @throws SqlQueryException
	 */
	protected static function deleteGroup(int $groupId): void
	{
		$connection = Application::getConnection();

		$connection->query(
			"DELETE FROM b_im_option_state WHERE GROUP_ID = $groupId"
		);

		$connection->query(
			"DELETE FROM b_im_option_access WHERE GROUP_ID = $groupId"
		);

		$connection->query(
			"DELETE FROM b_im_option_group WHERE ID = $groupId"
		);
	}

	/**
	 * Creates records about a shared group in the database
	 *
	 * @throws SystemException
	 * @throws ArgumentException
	 * @throws Exception
	 */
	protected static function createSharedGroup(
		string $name,
		array $accessCodes,
		int $creator,
		int $sort = self::DEFAULT_SORT
	): int
	{
		$newGroupId =
			OptionGroupTable::add([
				'NAME' => $name,
				'SORT' => $sort,
		  		'CREATE_BY_ID' => $creator
	  		])->getId()
		;

		$rows = [];
		foreach ($accessCodes as $accessCode)
		{
			$rows[] = [
				'GROUP_ID' => $newGroupId,
				'ACCESS_CODE' => $accessCode
			];
		}
		OptionAccessTable::addMulti($rows, true);

		return $newGroupId;
	}

	/**
	 * Creates records about a personal group in the database
	 *
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 * @throws Exception
	 */
	protected static function createPersonalGroup(int $creator)
	{
		$userAccessCode =
			UserAccessTable::query()
				->addSelect('ACCESS_CODE')
				->where('USER_ID', $creator)
				->whereLike('ACCESS_CODE', 'U%')
				->fetch()['ACCESS_CODE']
		;

		$newGroupId =
			OptionGroupTable::add([
				'USER_ID' => $creator,
			  	'SORT' => self::USER_PRESET_SORT,
				'CREATE_BY_ID' => $creator
			])->getId()
		;
		OptionAccessTable::add([
		   'GROUP_ID' => $newGroupId,
		   'ACCESS_CODE' => $userAccessCode
		]);

		return $newGroupId;
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	private static function updateGroupForUsers(int $groupId, array $candidates, int $sort, string $groupType): void
	{
		$join = new ConditionTree();

		if ($groupType === self::GENERAL_GROUP)
		{
			$join = Join::on('this.GENERAL_GROUP_ID', 'ref.ID');
		}
		elseif ($groupType === self::NOTIFY_GROUP)
		{
			$join = Join::on('this.NOTIFY_GROUP_ID', 'ref.ID');
		}

		$query =
			OptionUserTable::query()
				->addSelect('USER_ID')
				->registerRuntimeField(
					'OPTION_GROUP',
					new Reference(
						'OPTION_GROUP',
						OptionGroupTable::class,
						$join,
						['join_type' => Join::TYPE_INNER]
					)
				)
				->whereIn('USER_ID', $candidates)
				->where('OPTION_GROUP.SORT', '<=', $sort)
		;

		$users = [];
		foreach ($query->exec() as $row)
		{
			$users[] = $row["USER_ID"];
		}


		if ($groupType === self::GENERAL_GROUP)
		{
			OptionUserTable::updateMulti($users, ['GENERAL_GROUP_ID' => $groupId]);
		}
		elseif ($groupType === self::NOTIFY_GROUP)
		{
			OptionUserTable::updateMulti($users, ['NOTIFY_GROUP_ID' => $groupId]);
		}
	}

	private static function getPresetName($preset): string
	{
		switch ($preset['NAME'])
		{
			case '':
				return Loc::getMessage("IM_CONFIGURATION_PERSONAL_PRESET_NAME");
			case 'default':
				return Loc::getMessage("IM_CONFIGURATION_DEFAULT_PRESET_NAME");
			default:
				return $preset['NAME'];
		}
	}


	public static function getUserPresetFromCache(int $userId): array
	{
		$result = [];
		$userCache = CacheManager::getUserCache($userId);
		$currentUserPresets = $userCache->getValue();

		if (isset($currentUserPresets['notifyPreset']))
		{
			$notifyPresetCache = CacheManager::getPresetCache($currentUserPresets['notifyPreset']);
			$notifyPreset = $notifyPresetCache->getValue();
			if (!empty($notifyPreset))
			{
				$result['notify'] = [
					'id' => $notifyPreset['id'],
					'name' => $notifyPreset['name'],
					'sort' => $notifyPreset['sort'],
					'settings' => $notifyPreset['notify']
				];
			}
		}

		if (isset($currentUserPresets['generalPreset']))
		{
			$generalPresetCache = CacheManager::getPresetCache($currentUserPresets['generalPreset']);
			$generalPreset = $generalPresetCache->getValue();
			if (!empty($generalPreset))
			{
				$result['general'] = [
					'id' => $generalPreset['id'],
					'name' => $generalPreset['name'],
					'sort' => $generalPreset['sort'],
					'settings' => $generalPreset['general']
				];
			}
		}

		return $result;
	}

	private static function setUserPresetInCache(int $userId, array $preset): void
	{
		CacheManager::getUserCache($userId)->clearCache();
		CacheManager::getPresetCache($preset['general']['id'])->clearCache();
		CacheManager::getPresetCache($preset['notify']['id'])->clearCache();

		CacheManager::getUserCache($userId)->setValue([
			CacheManager::GENERAL_PRESET => $preset['general']['id'],
			CacheManager::NOTIFY_PRESET => $preset['notify']['id'],
		]);

		if ($preset['general']['id'] === $preset['notify']['id'])
		{
			CacheManager::getPresetCache($preset['general']['id'])->setValue([
				'id' => $preset['general']['id'],
				'name' => $preset['general']['name'],
				'sort' => $preset['general']['sort'],
				'general' => $preset['general']['settings'],
				'notify' => $preset['notify']['settings'],
			]);

			return;
		}

		CacheManager::getPresetCache($preset['general']['id'])->setValue([
			'id' => $preset['general']['id'],
			'name' => $preset['general']['name'],
			'sort' => $preset['general']['sort'],
			'general' => $preset['general']['settings'],
		]);

		CacheManager::getPresetCache($preset['notify']['id'])->setValue([
			'id' => $preset['notify']['id'],
			'name' => $preset['notify']['name'],
			'sort' => $preset['notify']['sort'],
			'notify' => $preset['notify']['settings'],
		]);
	}

	/**
	 * @deprecated
	 * @see CacheManager
	 * @param array $usersId
	 * @return void
	 */
	public static function cleanUsersCache(array $usersId): void
	{
		$cache = Cache::createInstance();
		foreach ($usersId as $userId)
		{
			$cacheName = self::CACHE_NAME."_$userId";
			$cache->clean($cacheName, self::CACHE_DIR);
		}
	}

	/**
	 * @deprecated
	 * @see CacheManager::getUserCache()
	 * @param int $userId
	 * @return void
	 */
	public static function cleanUserCache(int $userId): void
	{
		$cache = Cache::createInstance();
		$cacheName = self::CACHE_NAME."_$userId";
		$cache->clean($cacheName, self::CACHE_DIR);
	}

	/**
	 * @deprecated
	 * @see CacheManager
	 * @return void
	 */
	public static function cleanAllCache(): void
	{
		$cache = Cache::createInstance();
		$cache->cleanDir(self::CACHE_DIR);
	}

}