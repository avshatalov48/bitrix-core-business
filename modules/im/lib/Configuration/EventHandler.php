<?php

namespace Bitrix\Im\Configuration;

use Bitrix\Im\Model\OptionAccessTable;
use Bitrix\Im\Model\OptionGroupTable;
use Bitrix\Im\Model\OptionUserTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Entity\Query\Filter\ConditionTree;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use Exception;

class EventHandler
{
	/**
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws Exception
	 */
	public static function onAfterUserAdd($fields): void
	{
		// failed registration or settings were not converted
		if (
			$fields['RESULT'] === false
			|| !Manager::isSettingsMigrated()
		)
		{
			return;
		}

		$externalAuthId = $fields['EXTERNAL_AUTH_ID'] ?? null;
		if (in_array($externalAuthId, UserTable::getExternalUserTypes(), true))
		{
			return;
		}

		$userId = (int)$fields['ID'];

		// there are no departments in BUS
		if (!isset($fields['UF_DEPARTMENT']) || !Loader::includeModule('intranet'))
		{
			self::setGroup($userId, Configuration::getDefaultPresetId());

			return;
		}

		if (!is_array($fields['UF_DEPARTMENT']))
		{
			$departmentId = $fields['UF_DEPARTMENT'];

			if (!is_numeric($departmentId))
			{
				self::setGroup($userId, Configuration::getDefaultPresetId());

				return;
			}

			$accessCodes = self::findAllAccessCodes((int)$departmentId);

			$topDepartmentId = Department::getTopDepartmentId();
			$baseAccessCode = $topDepartmentId ? 'DR' . $topDepartmentId : 'AU';

			$accessCodes = !empty($accessCodes) ? $accessCodes : [$baseAccessCode];

			$presetId = self::getTopSortGroupIdByAccessCodes($accessCodes);

			self::setGroup($userId, $presetId);

			return;
		}

		// no department selected
		if (!$fields['UF_DEPARTMENT'][0])
		{
			self::setGroup($userId, Configuration::getDefaultPresetId());

			return;
		}

		$presetId = self::findTopSortPresetId($fields['UF_DEPARTMENT']);

		self::setGroup($userId, $presetId);
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws SystemException
	 * @throws Exception
	 */
	public static function onAfterUserUpdate($fields): void
	{
		if (
			!isset($fields['UF_DEPARTMENT'])
			|| !Loader::includeModule('intranet')
			|| !Manager::isSettingsMigrated()
		)
		{
			return;
		}

		$userId = (int)$fields['ID'];

		if (!is_array($fields['UF_DEPARTMENT']))
		{
			$departmentId = $fields['UF_DEPARTMENT'];
			if (!is_numeric($departmentId))
			{
				return;
			}

			$accessCodes = self::findAllAccessCodes((int)$departmentId);

			$topDepartmentId = Department::getTopDepartmentId();
			$baseAccessCode = $topDepartmentId ? 'DR' . $topDepartmentId : 'AU';

			$accessCodes = !empty($accessCodes) ? $accessCodes : [$baseAccessCode];

			$presetId = self::getTopSortGroupIdByAccessCodes($accessCodes);

			self::updateUserGroups($presetId, $userId);

			return;
		}

		if (!$fields['UF_DEPARTMENT'][0])
		{
			return;
		}

		$presetId = self::findTopSortPresetId($fields['UF_DEPARTMENT']);
		self::updateUserGroups($presetId, $userId);
	}

	/**
	 * @param $userId
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SqlQueryException
	 * @throws SystemException
	 */
	public static function onAfterUserDelete($userId): void
	{
		$userId = (int)$userId;

		$row = OptionUserTable::getById($userId);
		if (!$row->fetch())
		{
			return;
		}

		$connection = Application::getConnection();
		$connection->query("DELETE FROM b_im_option_user where USER_ID = " . $userId);

		$userGroupId =
			OptionGroupTable::query()
				->addSelect('ID')
				->where('USER_ID', $userId)
				->fetch()['ID']
		;

		if (!$userGroupId)
		{
			return;
		}

		$userGroupId = (int)$userGroupId;
		$accessCode = 'U' . $userId;

		$connection->query("DELETE FROM b_im_option_access WHERE ACCESS_CODE = '$accessCode'");
		$connection->query("DELETE FROM b_im_option_state WHERE GROUP_ID = " . $userGroupId);
		$connection->query("DELETE FROM b_im_option_group WHERE ID = " . $userGroupId);
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	private static function getTopSortGroupIdByAccessCodes(array $accessCodes): int
	{
		$group =
			OptionAccessTable::query()
				->addSelect('GROUP_ID')
				->registerRuntimeField(
					'OPTION_GROUP',
					new Reference(
						'OPTION_GROUP',
						OptionGroupTable::class,
						Join::on('this.GROUP_ID', 'ref.ID')
					)
				)
				->whereIn('ACCESS_CODE', $accessCodes)
				->setOrder([
					'OPTION_GROUP.SORT' => 'DESC',
					'GROUP_ID' => 'DESC'
				])
				->setLimit(1)
				->fetch()
		;

		return $group ? (int)$group['GROUP_ID'] : Configuration::getDefaultPresetId();
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private static function isSelectedPersonalGroup(int $userId, ConditionTree $join): bool
	{
		$selectedGroup =
			OptionUserTable::query()
				->addSelect('USER_ID')
				->registerRuntimeField(
					'OPTION_ACCESS',
					new Reference(
						'OPTION_ACCESS',
						OptionAccessTable::class,
						$join,
						['join_type' => Join::TYPE_INNER]
					)
				)
				->where('USER_ID', $userId)
				->where('OPTION_ACCESS.ACCESS_CODE', 'U' . $userId)
				->fetch();

		return $selectedGroup !== false;
	}

	/**
	 * @param $groupId
	 * @param $userId
	 *
	 * @throws Exception
	 */
	private static function updateUserGroups($groupId, $userId): void
	{
		$notifyJoin = Join::on('this.NOTIFY_GROUP_ID', 'ref.GROUP_ID');
		if (!self::isSelectedPersonalGroup($userId, $notifyJoin))
		{
			OptionUserTable::update($userId, ['NOTIFY_GROUP_ID' => $groupId]);
		}

		$generalJoin = Join::on('this.GENERAL_GROUP_ID', 'ref.GROUP_ID');
		if (!self::isSelectedPersonalGroup($userId, $generalJoin))
		{
			OptionUserTable::update($userId, ['GENERAL_GROUP_ID' => $groupId]);
		}
	}

	private static function findAllAccessCodes(int $departmentId): array
	{
		$department = new Department($departmentId);
		$fullDepartmentsId = $department->getPathFromHeadToDepartment();

		if (empty($fullDepartmentsId))
		{
			return ['AU'];
		}

		return $department->getAccessCodes($fullDepartmentsId);
	}

	/**
	 * @throws Exception
	 */
	public static function setGroup(int $userId, int $groupId): void
	{
		$insertFields = [
			'USER_ID' => $userId,
			'GENERAL_GROUP_ID' => $groupId,
			'NOTIFY_GROUP_ID' => $groupId
		];
		$updateFields = [
			'GENERAL_GROUP_ID' => $groupId,
			'NOTIFY_GROUP_ID' => $groupId,
		];

		OptionUserTable::merge($insertFields, $updateFields);
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	private static function findTopSortPresetId($departmentIds): int
	{
		$accessCodes = [];

		foreach ($departmentIds as $departmentId)
		{
			$accessCodes = array_merge($accessCodes, self::findAllAccessCodes((int)$departmentId));
		}
		$accessCodes = array_unique($accessCodes);

		$topDepartmentId = Department::getTopDepartmentId();
		$baseAccessCode = $topDepartmentId ? 'DR' . $topDepartmentId : 'AU';

		$accessCodes = !empty($accessCodes) ? $accessCodes : [$baseAccessCode];

		return self::getTopSortGroupIdByAccessCodes($accessCodes);
	}
}