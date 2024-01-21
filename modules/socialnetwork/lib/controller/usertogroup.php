<?php

namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Main\Engine;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Query\Filter;
use Bitrix\Main\Search\Content;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Socialnetwork\Integration\Main\File;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Service;
use Bitrix\Socialnetwork\UserToGroupTable;

class UserToGroup extends Base
{
	public function listAction(
		PageNavigation $pageNavigation,
		$filter = [],
		$select = [],
		$groupBy = false,
		$order = ['ID' => 'DESC'],
		$params = []
	)
	{
		$relations = [];

		$query = UserToGroupTable::query();
		$query
			->setSelect($this->prepareQuerySelect($select))
			->setOrder($order)
			->setOffset($pageNavigation->getOffset())
			->setLimit(($pageNavigation->getLimit()))
			->countTotal(true)
		;
		$query = $this->processFilter($query, $filter);

		$res = $query->exec();
		while ($relation = $res->fetch())
		{
			$relation['FORMATTED_USER_NAME'] = \CUser::FormatName(
				\CSite::getNameFormat(),
				[
					'NAME' => $relation['USER_NAME'],
					'LAST_NAME' => $relation['USER_LAST_NAME'],
					'SECOND_NAME' => $relation['USER_SECOND_NAME'],
					'LOGIN' => $relation['USER_LOGIN'],
				],
				true
			);
			$relations[$relation['ID']] = $relation;
		}

		if (!empty($relations))
		{
			if (in_array('USER_PERSONAL_PHOTO', $select, true))
			{
				$relations = $this->fillUserAvatars($relations);
			}
			if (in_array('ACTIONS', $select, true))
			{
				$relations = $this->fillActions($relations);
			}
		}
		$relations = $this->convertKeysToCamelCase($relations);

		return new Engine\Response\DataType\Page('relations', array_values($relations), $res->getCount());
	}

	private function prepareQuerySelect(array $select): array
	{
		$userToGroupFields = [
			'ID',
			'USER_ID',
			'GROUP_ID',
			'ROLE',
			'AUTO_MEMBER',
			'DATE_CREATE',
			'DATE_UPDATE',
			'INITIATED_BY_TYPE',
			'INITIATED_BY_USER_ID',
			'MESSAGE',
		];
		$userFields = [
			'USER_ACTIVE',
			'USER_NAME',
			'USER_LAST_NAME',
			'USER_SECOND_NAME',
			'USER_WORK_POSITION',
			'USER_LOGIN',
			'USER_EMAIL',
			'USER_CONFIRM_CODE',
			'USER_PERSONAL_PHOTO',
			'USER_PERSONAL_GENDER',
			'USER_LID',
		];
		$allowedFields = array_merge($userToGroupFields, $userFields);
		$prepared = array_intersect($select, $allowedFields);

		foreach ($prepared as $field)
		{
			if (in_array($field, $userFields, true))
			{
				$prepared[$field] = 'USER.' . str_replace('USER_', '', $field);
				unset($prepared[array_search($field, $prepared, true)]);
			}
		}

		return $prepared;
	}

	private function processFilter(Query $query, array $filter): Query
	{
		if (array_key_exists('ID', $filter))
		{
			$ids = (is_array($filter['ID']) ? $filter['ID'] : [$filter['ID']]);
			$ids = array_map('intval', $ids);
			$ids = array_filter($ids);

			if (!empty($ids))
			{
				count($ids) > 1
					? $query->whereIn('ID', $ids)
					: $query->where('ID', $ids[0])
				;
			}
		}

		if (array_key_exists('GROUP_ID', $filter))
		{
			$query->where('GROUP_ID', (int)$filter['GROUP_ID']);
		}

		if (array_key_exists('ROLE', $filter))
		{
			$roles = (is_array($filter['ROLE']) ? $filter['ROLE'] : [$filter['ROLE']]);
			$roles = array_filter($roles);

			if (!empty($roles))
			{
				if (array_key_exists('INVITED_BY_ME', $filter) && $filter['INVITED_BY_ME'] === 'Y')
				{
					$query->where(
						Query::filter()
							->logic('or')
							->whereIn('ROLE', $roles)
							->where(
								Query::filter()
									->where('ROLE', UserToGroupTable::ROLE_REQUEST)
									->where('INITIATED_BY_TYPE', UserToGroupTable::INITIATED_BY_GROUP)
									->where('INITIATED_BY_USER_ID', $this->getCurrentUser()->getId())
							)
					);
				}
				else
				{
					$query->whereIn('ROLE', $roles);
				}
			}
			else if (array_key_exists('INVITED_BY_ME', $filter) && $filter['INVITED_BY_ME'] === 'Y')
			{
				$query->where(
					Query::filter()
						->where('ROLE', UserToGroupTable::ROLE_REQUEST)
						->where('INITIATED_BY_TYPE', UserToGroupTable::INITIATED_BY_GROUP)
						->where('INITIATED_BY_USER_ID', $this->getCurrentUser()->getId())
				);
			}
		}

		if (array_key_exists('INITIATED_BY_TYPE', $filter))
		{
			$query->where('INITIATED_BY_TYPE', $filter['INITIATED_BY_TYPE']);
		}

		if (array_key_exists('INITIATED_BY_USER_ID', $filter))
		{
			$query->where('INITIATED_BY_USER_ID', $filter['INITIATED_BY_USER_ID']);
		}

		if (array_key_exists('SEARCH_INDEX', $filter) && trim($filter['SEARCH_INDEX']) !== '')
		{
			$query->whereMatch(
				'USER.INDEX.SEARCH_ADMIN_CONTENT',
				Filter\Helper::matchAgainstWildcard(Content::prepareStringToken(trim($filter['SEARCH_INDEX'])))
			);
		}

		return $query;
	}

	private function fillUserAvatars(array $relations): array
	{
		foreach (array_keys($relations) as $id)
		{
			$relations[$id]['IMAGE'] = '';
		}

		$imageIds = array_filter(
			array_column($relations, 'USER_PERSONAL_PHOTO', 'ID'),
			static function ($id) {
				return (int)$id > 0;
			}
		);

		$avatars = File::getFilesSources($imageIds);
		$imageIds = array_flip($imageIds);

		foreach ($imageIds as $imageId => $relationId)
		{
			$relations[$relationId]['IMAGE'] = $avatars[$imageId];
		}

		return $relations;
	}

	private function fillActions(array $relations): array
	{
		$userId = (int)$this->getCurrentUser()->getId();
		$permissions = [];

		foreach ($relations as $id => $relation)
		{
			$projectId = (int)$relation['GROUP_ID'];

			if (!array_key_exists($projectId, $permissions))
			{
				$permissions[$projectId] = Helper\Workgroup::getPermissions(['groupId' => $projectId]);
			}

			$projectPermissions = $permissions[$projectId];
			$canModifyGroup = $projectPermissions['UserCanModifyGroup'];
			$canInitiate = $projectPermissions['UserCanInitiate'];
			$canProcessRequestsIn = $projectPermissions['UserCanProcessRequestsIn'];

			$role = $relation['ROLE'];
			$memberId = (int)$relation['USER_ID'];
			$isAutoMember = ($relation['AUTO_MEMBER'] === 'Y');
			$initiatedByType = $relation['INITIATED_BY_TYPE'];

			$relations[$id]['ACTIONS'] = [
				'SET_OWNER' => (
					$canModifyGroup
					&& $projectPermissions['UserIsOwner']
					&& in_array($role, [UserToGroupTable::ROLE_MODERATOR, UserToGroupTable::ROLE_USER], true)
				),
				'SET_MODERATOR' => (
					$canModifyGroup
					&& $role === UserToGroupTable::ROLE_USER
				),
				'REMOVE_MODERATOR' => (
					$canModifyGroup
					&& $role === UserToGroupTable::ROLE_MODERATOR
				),
				'EXCLUDE' => (
					$canModifyGroup
					&& !$isAutoMember
					&& $memberId !== $userId
					&& in_array($role, [UserToGroupTable::ROLE_MODERATOR, UserToGroupTable::ROLE_USER], true)
				),
				'REPEAT_INVITE' => (
					$canInitiate
					&& $role === UserToGroupTable::ROLE_REQUEST
					&& $initiatedByType === UserToGroupTable::INITIATED_BY_GROUP
				),
				'CANCEL_INVITE' => (
					$canInitiate
					&& $role === UserToGroupTable::ROLE_REQUEST
					&& $initiatedByType === UserToGroupTable::INITIATED_BY_GROUP
				),
				'ACCEPT_REQUEST' => (
					$canProcessRequestsIn
					&& $role === UserToGroupTable::ROLE_REQUEST
					&& $initiatedByType === UserToGroupTable::INITIATED_BY_USER
				),
				'DENY_REQUEST' => (
					$canProcessRequestsIn
					&& $role === UserToGroupTable::ROLE_REQUEST
					&& $initiatedByType === UserToGroupTable::INITIATED_BY_USER
				),
			];
		}

		return $relations;
	}

	public function joinAction(array $params = [])
	{
		$userId = (int)(isset($params['userId']) && (int)$params['userId'] > 0 ? $params['userId'] : $this->getCurrentUser()->getId());
		$groupId = (int)(isset($params['groupId']) && (int)$params['groupId'] > 0 ? $params['groupId'] : 0);

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
			!\CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false)
			&& $userId !== (int)$this->getCurrentUser()->getId()
		)
		{
			$this->addError(new Error('No permissions', 'SONET_CONTROLLER_USERTOGROUP_NO_PERMISSIONS'));
			return null;
		}

		try
		{
			$confirmationNeeded = Helper\Workgroup::join([
				'groupId' => $groupId,
			]);
		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		if ($confirmationNeeded)
		{
			// re-calculte counters for the group moderators
			$moderators = UserToGroupTable::getGroupModerators($groupId);
			Service::addEvent(
				EventDictionary::EVENT_WORKGROUP_MEMBER_REQUEST_CONFIRM,
				[
					'GROUP_ID' => $groupId,
					'RECEPIENTS' => array_map(function ($row) { return $row['USER_ID']; }, $moderators),
				]
			);
		}

		return [
			'success' => true,
			'confirmationNeeded' => $confirmationNeeded,
		];
	}

	/* use Helper::exclude() then */
	public function leaveAction(array $params = [])
	{
		global $APPLICATION;

		$userId = $this->getCurrentUser()->getId();
		$groupId = (int)(isset($params['groupId']) && (int)$params['groupId'] > 0 ? $params['groupId'] : 0);

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

		try
		{
			Helper\Workgroup::leave([
				'groupId' => $groupId,
				'userId' => $userId,
			]);
		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		return [
			'success' => true,
		];
	}

	public function setOwnerAction(int $userId, int $groupId): bool
	{
		return Helper\Workgroup::setOwner([
			'userId' => $userId,
			'groupId' => $groupId,
		]);
	}

	public function setModeratorAction(int $userId, int $groupId): bool
	{
		return Helper\Workgroup::setModerator([
			'userId' => $userId,
			'groupId' => $groupId,
		]);
	}

	public function removeModeratorAction(int $userId, int $groupId): bool
	{
		return Helper\Workgroup::removeModerator([
			'userId' => $userId,
			'groupId' => $groupId,
		]);
	}

	public function setModeratorsAction(array $userIds, int $groupId): bool
	{
		return Helper\Workgroup::setModerators([
			'userIds' => $userIds,
			'groupId' => $groupId,
		]);
	}

	public function excludeAction(int $userId, int $groupId): bool
	{
		return Helper\Workgroup::exclude([
			'userId' => $userId,
			'groupId' => $groupId,
		]);
	}

	public function repeatInviteAction(int $userId, int $groupId): bool
	{
		return \CSocNetUserToGroup::SendRequestToJoinGroup(
			$this->getCurrentUser()->getId(),
			$userId,
			$groupId,
			''
		);
	}

	public function cancelInviteAction(int $userId, int $groupId): bool
	{
		return Helper\Workgroup::deleteOutgoingRequest([
			'userId' => $userId,
			'groupId' => $groupId,
		]);
	}

	public function cancelIncomingRequestAction(int $userId, int $groupId): bool
	{
		return Helper\Workgroup::deleteIncomingRequest([
			'userId' => $userId,
			'groupId' => $groupId,
		]);
	}

	public function acceptOutgoingRequestAction(int $groupId): bool
	{
		$userId = $this->getCurrentUser()->getId();

		try
		{
			return Helper\Workgroup::acceptOutgoingRequest([
				'groupId' => $groupId,
				'userId' => $userId,
			]);
		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage()));

			return false;
		}
	}

	public function rejectOutgoingRequestAction(int $groupId): bool
	{
		$userId = $this->getCurrentUser()->getId();

		try
		{
			return Helper\Workgroup::rejectOutgoingRequest([
				'groupId' => $groupId,
				'userId' => $userId,
			]);
		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage()));

			return false;
		}
	}

	public function acceptRequestAction(int $relationId, int $groupId): bool
	{
		return \CSocNetUserToGroup::ConfirmRequestToBeMember(
			$this->getCurrentUser()->getId(),
			$groupId,
			[ $relationId ]
		);
	}

	public function denyRequestAction(int $relationId, int $groupId): bool
	{
		return \CSocNetUserToGroup::RejectRequestToBeMember(
			$this->getCurrentUser()->getId(),
			$groupId,
			[$relationId]
		);
	}

	public function setHideRequestPopupAction(int $groupId): ?bool
	{
		$result = Helper\UserToGroup\RequestPopup::setHideRequestPopup([
			'groupId' => $groupId,
			'userId' => (int)$this->getCurrentUser()->getId(),
		]);

		if (!$result)
		{
			$this->addError(new Error('Cannot process operation', 'SONET_CONTROLLER_USERTOGROUP_SET_HIDE_REQUEST_POPUP_ERROR'));
			return null;
		}

		return true;
	}
}
