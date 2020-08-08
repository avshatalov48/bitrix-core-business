<?
namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupTable;

class UserToGroup extends Base
{
	public function joinAction(array $params = [])
	{
		global $APPLICATION;

		$result = [
			'success' => false
		];

		$userId = (isset($params['userId']) && intval($params['userId']) > 0 ? intval($params['userId']) : $this->getCurrentUser()->getId());
		$groupId = (isset($params['groupId']) && intval($params['groupId']) > 0 ? intval($params['groupId']) : 0);

		if ($userId <= 0)
		{
			$this->addError(new Error('No User Id', 'SONET_CONTROLLER_USERTOGROUP_NO_USER_ID'));
			return null;
		}

		if ($groupId <= 0)
		{
			$this->addError(new Error('No Workgroup', 'SONET_CONTROLLER_USERTOGROUP_NO_GROUP'));
			return null;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('Cannot include Socialnetwork module', 'SONET_CONTROLLER_USERTOGROUP_NO_SOCIALNETWORK_MODULE'));
			return null;
		}

		if (
			$userId != $this->getCurrentUser()->getId()
			&& !\CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false)
		)
		{
			$this->addError(new Error('No permissions', 'SONET_CONTROLLER_USERTOGROUP_NO_PERMISSIONS'));
			return null;
		}

		$res = WorkgroupTable::getList([
			'filter' => [
				'ID' => $groupId
			],
			'select' => [ 'ID', 'OPENED', 'VISIBLE' ]
		]);
		$workgroupFields = $res->fetch();
		if (!$workgroupFields)
		{
			$this->addError(new Error('No Workgroup', 'SONET_CONTROLLER_USERTOGROUP_NO_GROUP'));
			return null;
		}

		$res = UserToGroupTable::getList([
			'filter' => [
				'USER_ID' => $userId,
				'GROUP_ID' => $groupId
			],
			'select' => [ 'ID', 'ROLE' ]
		]);
		if ($relationFields = $res->fetch())
		{
			if (in_array($relationFields['ROLE'], UserToGroupTable::getRolesMember()))
			{
				$this->addError(new Error('User is already a member of the group', 'SONET_CONTROLLER_USERTOGROUP_ALREADY_MEMBER'));
				return null;
			}
			elseif (in_array($relationFields['ROLE'], [ UserToGroupTable::ROLE_BAN ]))
			{
				$this->addError(new Error('User cannot join the group', 'SONET_CONTROLLER_USERTOGROUP_BANNED'));
				return null;
			}
			elseif (in_array($relationFields['ROLE'], [ UserToGroupTable::ROLE_REQUEST ]))
			{
				if (!\CSocNetUserToGroup::userConfirmRequestToBeMember($userId, $relationFields['ID'], false))
				{
					$this->addError(new Error((($e = $APPLICATION->getException()) ? $e->getString() : 'Cannot join the group'), 'SONET_CONTROLLER_USERTOGROUP_JOIN_ERROR'));
					return null;
				}

				$result = [
					'success' => true
				];
			}
		}
		elseif (
			$workgroupFields['OPENED'] == 'Y'
			&& $workgroupFields['VISIBLE'] == 'Y'
		)
		{
			if (!\CSocNetUserToGroup::sendRequestToBeMember($userId, $groupId, '', '', false))
			{
				$this->addError(new Error((($e = $APPLICATION->getException()) ? $e->getString() : 'Cannot join the group'), 'SONET_CONTROLLER_USERTOGROUP_JOIN_ERROR'));
				return null;
			}

			$result = [
				'success' => true
			];
		}
		else
		{
			$this->addError(new Error('User should request first', 'SONET_CONTROLLER_USERTOGROUP_JOIN_ERROR'));
			return null;
		}

		return $result;
	}
}

