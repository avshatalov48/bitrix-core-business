<?
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
	const STEP_SIZE = 5;

	protected static $moduleId = "socialnetwork";

	final static function getUsers($workgroupId)
	{
		static $cache = array();

		if (isset($cache[$workgroupId]))
		{
			return $cache[$workgroupId];
		}

		$result = array(
			'PLUS' => array(),
			'MINUS' => array(),
			'OLD_RELATIONS' => array()
		);

		if (
			!Loader::includeModule('socialnetwork')
			|| !Loader::includeModule('intranet')
		)
		{
			return $result;
		}

		$newUserList = $oldUserList = $oldRelationList = array();

		$groupItem = \Bitrix\Socialnetwork\Item\Workgroup::getById($workgroupId);
		$groupFields = $groupItem->getFields();

		if (
			isset($groupFields['UF_SG_DEPT'])
			&& isset($groupFields['UF_SG_DEPT']['VALUE'])
			&& !empty($groupFields['UF_SG_DEPT']['VALUE'])
		)
		{
			$newDeptList = array_map('intval', $groupFields['UF_SG_DEPT']['VALUE']);
			$res = \CIntranetUtils::getDepartmentEmployees($newDeptList, true);
			while ($departmentMember = $res->fetch())
			{
				if ($departmentMember["ID"] != $groupFields["OWNER_ID"])
				{
					$newUserList[] = $departmentMember["ID"];
				}
			}

			foreach($newDeptList as $deptId)
			{
				$managerId = intval(\CIntranetUtils::getDepartmentManagerId($deptId));
				if ($managerId > 0)
				{
					$newUserList[] = $managerId;
				}
			}

			$newUserList = array_map('intval', array_unique($newUserList));
		}

		$res = UserToGroupTable::getList(array(
			'filter' => array(
				'=GROUP_ID' => intval($groupFields["ID"]),
				'@ROLE' => UserToGroupTable::getRolesMember(),
				'AUTO_MEMBER' => 'Y'
			),
			'select' => array('ID', 'USER_ID')
		));
		while($relation = $res->fetch())
		{
			$oldUserList[] = $relation['USER_ID'];
			$oldRelationList[$relation['USER_ID']] = $relation['ID'];
		}
		$oldUserList = array_map('intval', array_unique($oldUserList));

		$membersList = [];
		$res = UserToGroupTable::getList(array(
			'filter' => array(
				'=GROUP_ID' => intval($groupFields["ID"]),
				'@ROLE' => UserToGroupTable::getRolesMember(),
			),
			'select' => array('ID', 'USER_ID')
		));
		while($relation = $res->fetch())
		{
			$membersList[] = $relation['USER_ID'];
		}
		$membersList = array_map('intval', array_unique($membersList));

		$result = array(
			'PLUS' => array_diff($newUserList, $membersList),
			'MINUS' => array_diff($oldUserList, $newUserList),
			'OLD_RELATIONS' => $oldRelationList
		);

		$cache[$workgroupId] = $result;

		return $result;
	}

	final static function getCount()
	{
		$result = 0;

		$workgroupsToSync = Option::get('socialnetwork', 'workgroupsToSync', "");
		$workgroupsToSync = ($workgroupsToSync !== "" ? @unserialize($workgroupsToSync, [ 'allowed_classes' => false ]) : []);

		if (
			is_array($workgroupsToSync)
			&& !empty($workgroupsToSync)
		)
		{
			$nonEmptyWorkgroupList = [];

			foreach($workgroupsToSync as $workgroupData)
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
					foreach($data['MINUS'] as $userId)
					{
						if (
							isset($data['OLD_RELATIONS'])
							&& is_array($data['OLD_RELATIONS'])
							&& isset($data['OLD_RELATIONS'][$userId])
						)
						{
							$groupCounter++;
						}
					}
				}

				if ($groupCounter > 0)
				{
					$nonEmptyWorkgroupList[] = array(
						'groupId' => $workgroupId,
						'initiatorId' => $workgroupData['initiatorId'],
						'exclude' => (isset($workgroupData['exclude']) ? $workgroupData['exclude'] : false),
					);
					$result += $groupCounter;
				}
				\CSocNetGroup::setStat($workgroupId);
			}

			Option::set('socialnetwork', 'workgroupsToSync', serialize($nonEmptyWorkgroupList));
		}

		return $result;
	}

	public function execute(array &$result)
	{
		if (!(
			Loader::includeModule("socialnetwork")
			&& Loader::includeModule("intranet")
		))
		{
			return false;
		}

		$return = false;

		$params = Option::get("socialnetwork", "workgroupdeptsync", "");
		$params = ($params !== "" ? @unserialize($params, [ 'allowed_classes' => false ]) : array());
		$params = (is_array($params) ? $params : array());

		$countRemain = self::getCount();
		if (empty($params))
		{
			$params = [
				"number" => 0,
				"count" => $countRemain
			];
		}

		if ($countRemain > 0)
		{
			$result["title"] = Loc::getMessage("FUPD_WORKGROUP_DEPT_SYNC_TITLE");
			$result["progress"] = 1;
			$result["steps"] = "";
			$result["count"] = $params["count"];

			$counter = 0;
			$breakFlag = false;

			$workgroupsToSync = Option::get('socialnetwork', 'workgroupsToSync', "");
			$workgroupsToSync = ($workgroupsToSync !== "" ? @unserialize($workgroupsToSync, [ 'allowed_classes' => false ]) : []);

			if (
				is_array($workgroupsToSync)
				&& !empty($workgroupsToSync)
			)
			{
				foreach($workgroupsToSync as $workgroupData)
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
							: array()
					);

					$userListMinus = (
						isset($data['MINUS'])
						&& is_array($data['MINUS'])
							? $data['MINUS']
							: array()
					);

					$oldRelationList = (
						isset($data['OLD_RELATIONS'])
						&& is_array($data['OLD_RELATIONS'])
							? $data['OLD_RELATIONS']
							: array()
					);

					foreach($userListMinus as $userId)
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
								UserToGroup::changeRelationAutoMembership(array(
									'RELATION_ID' => $oldRelationList[$userId],
									'VALUE' => 'N'
								));
							}

							$counter++;
						}
					}

					$changeList = $addList = array();

					if (
						!$breakFlag
						&& !empty($userListPlus)
					)
					{
						$memberList = array();
						$res = UserToGroupTable::getList(array(
							'filter' => array(
								'=GROUP_ID' => $workgroupId,
								'@USER_ID' => $userListPlus,
								'@ROLE' => UserToGroupTable::getRolesMember(),
							),
							'select' => array('ID', 'USER_ID')
						));
						while($relation = $res->fetch())
						{
							$memberList[] = $relation['USER_ID'];
						}
						$userListPlus = array_diff($userListPlus, $memberList);
						if (!empty($userListPlus))
						{
							$res = UserToGroupTable::getList(array(
								'filter' => array(
									'=GROUP_ID' => $workgroupId,
									'@USER_ID' => $userListPlus,
									'@ROLE' => array(UserToGroupTable::ROLE_REQUEST, UserToGroupTable::ROLE_BAN),
									'AUTO_MEMBER' => 'N'
								),
								'select' => array('ID', 'USER_ID', 'GROUP_ID')
							));
							while($relation = $res->fetch())
							{
								if ($counter >= self::STEP_SIZE)
								{
									$breakFlag = true;
									break;
								}

								$changeList[] = intval($relation['USER_ID']);
								UserToGroup::changeRelationAutoMembership(array(
									'RELATION_ID' => intval($relation['ID']),
									'USER_ID' => intval($relation['USER_ID']),
									'GROUP_ID' => intval($relation['GROUP_ID']),
									'ROLE' => UserToGroupTable::ROLE_USER,
									'VALUE' => 'Y'
								));

								$counter++;
							}

							$addList = array_diff($userListPlus, $changeList);

							if (!$breakFlag)
							{
								foreach($addList as $addUserId)
								{
									if ($counter >= self::STEP_SIZE)
									{
										$breakFlag = true;
										break;
									}

									UserToGroup::addRelationAutoMembership(array(
										'CURRENT_USER_ID' => $workgroupData['initiatorId'],
										'USER_ID' => $addUserId,
										'GROUP_ID' => $workgroupId,
										'ROLE' => UserToGroupTable::ROLE_USER,
										'VALUE' => 'Y'
									));

									$counter++;
								}
							}
						}
					}
				}

				$params["number"] += $counter;

				Option::set("socialnetwork", "workgroupdeptsync", serialize($params));
				$return = true;
			}
			else
			{
				Option::delete("socialnetwork", array("name" => "workgroupdeptsync"));
			}

			$result["progress"] = intval($params["number"] * 100 / $params["count"]);
			$result["steps"] = $params["number"];
		}
		else
		{
			Option::delete("socialnetwork", array("name" => "workgroupdeptsync"));
		}

		return $return;
	}
}
?>