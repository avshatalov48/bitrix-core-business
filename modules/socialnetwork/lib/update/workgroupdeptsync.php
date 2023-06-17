<?php

namespace Bitrix\Socialnetwork\Update;

use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Item\UserToGroup;
use Bitrix\Socialnetwork\UserToGroupTable;

Loc::loadMessages(__FILE__);

final class WorkgroupDeptSync extends Stepper
{
	public const STEP_SIZE = 5;

	protected static $moduleId = 'socialnetwork';

	public static function getUsers($workgroupId)
	{
		static $cache = [];

		if (isset($cache[$workgroupId]))
		{
			return $cache[$workgroupId];
		}

		$result = [
			'PLUS' => [],
			'MINUS' => [],
			'OLD_RELATIONS' => [],
		];

		if (
			!Loader::includeModule('socialnetwork')
			|| !Loader::includeModule('intranet')
		)
		{
			return $result;
		}

		$newUserList = [];
		$oldUserList = [];
		$oldRelationList = [];

		$groupItem = \Bitrix\Socialnetwork\Item\Workgroup::getById($workgroupId);
		$groupFields = $groupItem->getFields();

		if (
			isset($groupFields['UF_SG_DEPT']['VALUE'])
			&& !empty($groupFields['UF_SG_DEPT']['VALUE'])
		)
		{
			$newDeptList = array_map('intval', $groupFields['UF_SG_DEPT']['VALUE']);

			$res = \CIntranetUtils::getDepartmentEmployees($newDeptList, true, false, 'Y', [ 'ID' ]);
			while ($departmentMember = $res->fetch())
			{
				if ((int)$departmentMember['ID'] !== (int)$groupFields['OWNER_ID'])
				{
					$newUserList[] = (int)$departmentMember['ID'];
				}
			}

			foreach ($newDeptList as $deptId)
			{
				$managerId = (int)\CIntranetUtils::getDepartmentManagerId($deptId);
				if ($managerId > 0)
				{
					$newUserList[] = $managerId;
				}
			}
		}

		$res = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => (int) ($groupFields['ID'] ?? 0),
				'@ROLE' => UserToGroupTable::getRolesMember(),
				'=AUTO_MEMBER' => 'Y',
			],
			'select' => [ 'ID', 'USER_ID' ]
		]);
		while ($relation = $res->fetch())
		{
			$oldUserList[] = (int)$relation['USER_ID'];
			$oldRelationList[$relation['USER_ID']] = $relation['ID'];
		}

		$membersList = [];
		$res = UserToGroupTable::getList([
			'filter' => [
				'=GROUP_ID' => (int)$groupFields['ID'],
				'@ROLE' => UserToGroupTable::getRolesMember(),
			],
			'select' => [ 'ID', 'USER_ID' ]
		]);
		while ($relation = $res->fetch())
		{
			$membersList[] = (int)$relation['USER_ID'];
		}

		$result = [
			'PLUS' => array_diff($newUserList, $membersList),
			'MINUS' => array_diff($oldUserList, $newUserList),
			'OLD_RELATIONS' => $oldRelationList,
		];

		$cache[$workgroupId] = $result;

		return $result;
	}

	protected static function getCount(): int
	{
		$result = 0;

		$workgroupsToSync = Option::get('socialnetwork', 'workgroupsToSync');
		$workgroupsToSync = ($workgroupsToSync !== '' ? @unserialize($workgroupsToSync, [ 'allowed_classes' => false ]) : []);

		if (
			is_array($workgroupsToSync)
			&& !empty($workgroupsToSync)
		)
		{
			$workgroupsToSync = self::reduceList($workgroupsToSync);

			$nonEmptyWorkgroupList = [];

			foreach ($workgroupsToSync as $workgroupData)
			{
				$workgroupId = $workgroupData['groupId'];
				$groupCounter = 0;

				$data = self::getUsers($workgroupId);

				if (
					isset($data['PLUS'])
					&& is_array($data['PLUS'])
				)
				{
					$groupCounter += count($data['PLUS']);
				}

				if (
					isset($data['MINUS'])
					&& is_array($data['MINUS'])
				)
				{
					foreach ($data['MINUS'] as $userId)
					{
						if (isset($data['OLD_RELATIONS'][$userId]))
						{
							$groupCounter++;
						}
					}
				}

				if ($groupCounter > 0)
				{
					$nonEmptyWorkgroupList[] = [
						'groupId' => $workgroupId,
						'initiatorId' => $workgroupData['initiatorId'],
						'exclude' => ($workgroupData['exclude'] ?? false),
					];
					$result += $groupCounter;
				}
			}

			Option::set('socialnetwork', 'workgroupsToSync', serialize($nonEmptyWorkgroupList));
		}

		return $result;
	}

	public function execute(array &$result)
	{
		if (!(
			Loader::includeModule('socialnetwork')
			&& Loader::includeModule('intranet')
		))
		{
			return false;
		}

		$return = false;

		$params = Option::get('socialnetwork', 'workgroupdeptsync');
		$params = ($params !== '' ? @unserialize($params, [ 'allowed_classes' => false ]) : []);
		$params = (is_array($params) ? $params : []);

		$countRemain = self::getCount();
		if (empty($params))
		{
			$params = [
				'number' => 0,
				'count' => $countRemain,
			];
		}

		if ($countRemain > 0)
		{
			$result['title'] = Loc::getMessage('FUPD_WORKGROUP_DEPT_SYNC_TITLE');
			$result['progress'] = 1;
			$result['steps'] = '';
			$result['count'] = $params['count'];

			$counter = 0;
			$breakFlag = false;

			$workgroupsToSync = Option::get('socialnetwork', 'workgroupsToSync');
			$workgroupsToSync = ($workgroupsToSync !== '' ? @unserialize($workgroupsToSync, [ 'allowed_classes' => false ]) : []);

			if (
				is_array($workgroupsToSync)
				&& !empty($workgroupsToSync)
			)
			{
				$workgroupsToSync = self::reduceList($workgroupsToSync);

				foreach ($workgroupsToSync as $workgroupData)
				{
					$workgroupId = $workgroupData['groupId'];
					if ($breakFlag)
					{
						break;
					}

					$data = self::getUsers($workgroupId);

					$userListPlus = (
						isset($data['PLUS'])
						&& is_array($data['PLUS'])
							? $data['PLUS']
							: []
					);

					$userListMinus = (
						isset($data['MINUS'])
						&& is_array($data['MINUS'])
							? $data['MINUS']
							: []
					);

					$oldRelationList = (
						isset($data['OLD_RELATIONS'])
						&& is_array($data['OLD_RELATIONS'])
							? $data['OLD_RELATIONS']
							: []
					);

					foreach ($userListMinus as $userId)
					{
						if (isset($oldRelationList[$userId]))
						{
							if ($counter >= self::STEP_SIZE)
							{
								$breakFlag = true;
								break;
							}

							if (
								isset($workgroupData['exclude'])
								&& $workgroupData['exclude']
							)
							{
								\CSocNetUserToGroup::delete($oldRelationList[$userId]);
							}
							else
							{
								UserToGroup::changeRelationAutoMembership([
									'RELATION_ID' => $oldRelationList[$userId],
									'VALUE' => 'N',
								]);
							}

							$counter++;
						}
					}

					$changeList = [];

					if (
						!$breakFlag
						&& !empty($userListPlus)
					)
					{
						$memberList = [];
						$res = UserToGroupTable::getList([
							'filter' => [
								'=GROUP_ID' => $workgroupId,
								'@USER_ID' => $userListPlus,
								'@ROLE' => UserToGroupTable::getRolesMember(),
							],
							'select' => [ 'ID', 'USER_ID' ],
						]);
						while ($relation = $res->fetch())
						{
							$memberList[] = $relation['USER_ID'];
						}
						$userListPlus = array_diff($userListPlus, $memberList);
						if (!empty($userListPlus))
						{
							$res = UserToGroupTable::getList([
								'filter' => [
									'=GROUP_ID' => $workgroupId,
									'@USER_ID' => $userListPlus,
									'@ROLE' => [ UserToGroupTable::ROLE_REQUEST, UserToGroupTable::ROLE_BAN ],
									'=AUTO_MEMBER' => 'N',
								],
								'select' => [ 'ID', 'USER_ID', 'GROUP_ID' ]
							]);
							while ($relation = $res->fetch())
							{
								if ($counter >= self::STEP_SIZE)
								{
									$breakFlag = true;
									break;
								}

								$changeList[] = (int)$relation['USER_ID'];
								UserToGroup::changeRelationAutoMembership([
									'RELATION_ID' => (int)$relation['ID'],
									'USER_ID' => (int)$relation['USER_ID'],
									'GROUP_ID' => (int)$relation['GROUP_ID'],
									'ROLE' => UserToGroupTable::ROLE_USER,
									'VALUE' => 'Y',
								]);

								$counter++;
							}

							$addList = array_diff($userListPlus, $changeList);

							if (!$breakFlag)
							{
								foreach ($addList as $addUserId)
								{
									if ($counter >= self::STEP_SIZE)
									{
										$breakFlag = true;
										break;
									}

									UserToGroup::addRelationAutoMembership([
										'CURRENT_USER_ID' => $workgroupData['initiatorId'],
										'USER_ID' => $addUserId,
										'GROUP_ID' => $workgroupId,
										'ROLE' => UserToGroupTable::ROLE_USER,
										'VALUE' => 'Y',
									]);

									$counter++;
								}
							}
						}
					}

					\CSocNetGroup::setStat($workgroupId);
				}

				$params['number'] += $counter;

				Option::set('socialnetwork', 'workgroupdeptsync', serialize($params));
				$return = true;
			}
			else
			{
				Option::delete('socialnetwork', [ 'name' => 'workgroupdeptsync' ]);
			}

			$result['progress'] = (int)($params['number'] * 100 / $params['count']);
			$result['steps'] = $params['number'];
		}
		else
		{
			Option::delete('socialnetwork', [ 'name' => 'workgroupdeptsync' ]);
		}

		return $return;
	}

	protected static function reduceList(array $workgroupsToSync = []): array
	{
		$result = [];

		foreach ($workgroupsToSync as $workgroupData)
		{
			$workgroupId = (int)$workgroupData['groupId'];
			$result[$workgroupId] = $workgroupData;
		}

		return array_values($result);
	}
}
