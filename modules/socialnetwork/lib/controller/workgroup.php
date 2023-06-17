<?php

namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Intranet\Internals\ThemeTable;
use Bitrix\Main\Engine;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Bitrix\Intranet\Integration\Templates\Bitrix24\ThemePicker;
use Bitrix\Socialnetwork\EO_UserToGroup;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Socialnetwork\Integration\Main\File;
use Bitrix\Socialnetwork\Integration\Pull\PushService;
use Bitrix\Socialnetwork\Internals\EventService\Push\PushEventDictionary;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupPinTable;
use Bitrix\Socialnetwork\WorkgroupSubjectTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\WorkgroupTagTable;
use Bitrix\Main\UI\PageNavigation;

class Workgroup extends Base
{
	private static function getAllowedSelectFields(): array
	{
		return [
			'ID', 'ACTIVE', 'SUBJECT_ID', 'NAME', 'DESCRIPTION', 'KEYWORDS',
			'CLOSED', 'VISIBLE', 'OPENED', 'PROJECT', 'LANDING',
			'DATE_CREATE', 'DATE_UPDATE', 'DATE_ACTIVITY',
			'IMAGE_ID', 'AVATAR_TYPE',
			'OWNER_ID',
			'NUMBER_OF_MEMBERS', 'NUMBER_OF_MODERATORS',
			'INITIATE_PERMS',
			'PROJECT_DATE_START', 'PROJECT_DATE_FINISH',
			'SCRUM_OWNER_ID', 'SCRUM_MASTER_ID', 'SCRUM_SPRINT_DURATION', 'SCRUM_TASK_RESPONSIBLE',
		];
	}

	public function getAction(array $params = []): ?array
	{
		$groupId = (int)($params['groupId'] ?? 0);

		if ($groupId <= 0)
		{
			$this->addError(
				new Error(
					Loc::getMessage('SONET_CONTROLLER_WORKGROUP_EMPTY'),
					'SONET_CONTROLLER_WORKGROUP_EMPTY'
				)
			);

			return null;
		}

		$select = ($params['select'] ?? []);
		$filter = ($params['filter'] ?? []);
		$filter['ID'] = $groupId;

		if (!\CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false))
		{
			$filter['CHECK_PERMISSIONS'] = $this->getCurrentUser()->getId();
		}

		$result = \CSocNetGroup::getList([], $filter, false, false, ['ID']);
		if ($group = $result->fetch())
		{
			$groupItem = \Bitrix\Socialnetwork\Item\Workgroup::getById($group['ID']);
			$groupFields = $groupItem->getFields();

			if (in_array('AVATAR', $select, true))
			{
				$groupFields['AVATAR'] = File::getFileSource((int)$groupFields['IMAGE_ID'], 100, 100);
			}
			if (in_array('AVATAR_TYPES', $select, true))
			{
				$groupFields['AVATAR_TYPES'] = Helper\Workgroup::getAvatarTypes();
			}
			if (in_array('OWNER_DATA', $select, true))
			{
				$groupFields['OWNER_DATA'] = $this->getOwnerData($groupFields['OWNER_ID']);
			}
			if (in_array('SUBJECT_DATA', $select, true))
			{
				$groupFields['SUBJECT_DATA'] = $this->getSubjectData($groupFields['SUBJECT_ID']);
			}
			if (in_array('TAGS', $select, true))
			{
				$groupFields['TAGS'] = $this->getTags($groupId);
			}
			if (in_array('THEME_DATA', $select, true))
			{
				$groupFields['THEME_DATA'] = $this->getThemeData($groupId);
			}
			if (in_array('ACTIONS', $select, true))
			{
				$groupFields['ACTIONS'] = $this->getActions($groupId);
			}
			if (in_array('USER_DATA', $select, true))
			{
				$groupFields['USER_DATA'] = $this->getUserData($groupId);
			}
			if (in_array('DEPARTMENTS', $select, true))
			{
				$groupFields['DEPARTMENTS'] = $this->getDepartments($groupFields['UF_SG_DEPT']['VALUE']);
			}

			if ($groupFields['NUMBER_OF_MEMBERS'])
			{
				$groupFields['NUMBER_OF_MEMBERS_PLURAL'] = Loc::getPluralForm($groupFields['NUMBER_OF_MEMBERS']);
			}
			if ($groupFields['PROJECT_DATE_START'] || $groupFields['PROJECT_DATE_FINISH'])
			{
				$culture = Context::getCurrent()->getCulture();
				$format = $culture->getDayMonthFormat();

				/** @var DateTime $dateStart */
				$dateStart = $groupFields['PROJECT_DATE_START'];
				/** @var DateTime $dateFinish */
				$dateFinish = $groupFields['PROJECT_DATE_FINISH'];

				if ($dateStart)
				{
					$groupFields['FORMATTED_PROJECT_DATE_START'] = FormatDate(
						$format,
						MakeTimeStamp(DateTime::createFromTimestamp($dateStart->getTimestamp()))
					);
				}
				if ($dateFinish)
				{
					$groupFields['FORMATTED_PROJECT_DATE_FINISH'] = FormatDate(
						$format,
						MakeTimeStamp(DateTime::createFromTimestamp($dateFinish->getTimestamp()))
					);
				}
			}

			if (
				isset($params['mode'])
				&& $params['mode'] === 'mobile'
			)
			{
				$additionalData = Helper\Workgroup::getAdditionalData([
					'ids' => [ $groupId ],
					'features' => ($params['features'] ?? []),
					'mandatoryFeatures' => ($params['mandatoryFeatures'] ?? []),
					'currentUserId' => (int)$this->getCurrentUser()->getId(),
				]);

				$groupFields['ADDITIONAL_DATA'] = ($additionalData[$groupId] ?? []) ;
			}

			return $groupFields;
		}

		$this->addError(
			new Error(
				Loc::getMessage('SONET_CONTROLLER_WORKGROUP_NOT_FOUND'),
				'SONET_CONTROLLER_WORKGROUP_NOT_FOUND'
			)
		);

		return null;
	}

	public function listAction(
		PageNavigation $pageNavigation,
		array $filter = [],
		array $select = [],
		array $order = [],
		array $params = []
	)
	{
		if (
			empty($select)
			|| !is_array($select)
		)
		{
			$select = [ 'ID' ];
		}

		if (!in_array('ID', $select, true))
		{
			$select[] = 'ID';
		}

		$originalSelect = $select;

		if (
			$params['IS_ADMIN'] === 'Y'
			&& !\CSocNetUser::isCurrentUserModuleAdmin(SITE_ID, false)
		)
		{
			unset($params['IS_ADMIN']);
		}

		if ($params['IS_ADMIN'] !== 'Y')
		{
			$filter['CHECK_PERMISSIONS'] = $this->getCurrentUser()->getId();
		}

		$extranetSiteId = \CSocNetLogRestService::getExtranetSiteId();

		if (
			$extranetSiteId
			&& $params['IS_ADMIN'] !== 'Y'
			&& \CSocNetLogRestService::getCurrentUserType() === 'extranet'
		)
		{
			$filter['SITE_ID'] = $extranetSiteId;
		}
		else
		{
			$filter['SITE_ID'] = (string)($params['siteId'] ?? SITE_ID);
		}

		if (($key = array_search('AVATAR', $select, true)) !== false)
		{
			$select[] = 'IMAGE_ID';
			$select[] = 'AVATAR_TYPE';
			unset($select[$key]);
		}

		$workgroups = [];
		$count = 0;

		$queryIdFilter = [];

		$res = \CSocNetGroup::getList([], $filter, false, false, [ 'ID' ]);
		while ($groupFields = $res->fetch())
		{
			$queryIdFilter[] = (int)$groupFields['ID'];
		}

		if (!empty($queryIdFilter))
		{
			$select = $this->prepareSelect($select);

			$query = WorkgroupTable::query();
			$query
				->setSelect($select)
				->setOrder($order)
				->setOffset($pageNavigation->getOffset())
				->setLimit(($pageNavigation->getLimit()))
				->setFilter([
					'ID' => $queryIdFilter,
				])
				->countTotal(true);

			$res = $query->exec();

			$avatarTypes = Helper\Workgroup::getAvatarTypes();

			while ($groupFields = $res->fetch())
			{
				if (in_array('AVATAR', $originalSelect, true))
				{
					if ((int)$groupFields['IMAGE_ID'] > 0)
					{
						$groupFields['AVATAR'] = File::getFileSource((int)$groupFields['IMAGE_ID'], 100, 100);
					}
					elseif (
						!empty($groupFields['AVATAR_TYPE'])
						&& isset($params['mode'])
						&& $params['mode'] === 'mobile'
					)
					{
						$groupFields['AVATAR'] = $avatarTypes[$groupFields['AVATAR_TYPE']]['mobileUrl'];
					}
					else
					{
						$groupFields['AVATAR'] = '';
					}
				}

				$workgroups[(int)$groupFields['ID']] = $groupFields;
			}

			$count = $res->getCount();
		}

		$ids = array_keys($workgroups);

		if (
			isset($params['mode'])
			&& $params['mode'] === 'mobile'
		)
		{
			$additionalData = Helper\Workgroup::getAdditionalData([
				'ids' => $ids,
				'features' => ($params['features'] ?? []),
				'mandatoryFeatures' => ($params['mandatoryFeatures'] ?? []),
				'currentUserId' => (int)$this->getCurrentUser()->getId(),
			]);

			foreach (array_keys($workgroups) as $id)
			{
				if (!isset($additionalData[$id]))
				{
					continue;
				}

				$workgroups[$id]['ADDITIONAL_DATA'] = ($additionalData[$id] ?? []) ;
			}
		}

		$workgroups = $this->convertKeysToCamelCase($workgroups);

		return new Engine\Response\DataType\Page('workgroups', array_values($workgroups), $count);
	}

	private function prepareSelect(array $select = []): array
	{
		return array_filter($select, static function ($key) {
			return in_array(mb_strtoupper($key), static::getAllowedSelectFields(), true);
		});
	}

	private function getOwnerData(int $ownerId): array
	{
		$owner = UserTable::getList([
			'select' => ['NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'PERSONAL_PHOTO'],
			'filter' => ['ID' => $ownerId],
		])->fetch();

		return [
			'ID' => $ownerId,
			'PHOTO' => ($owner['PERSONAL_PHOTO'] ? File::getFileSource($owner['PERSONAL_PHOTO']) : null),
			'FORMATTED_NAME' => htmlspecialcharsback(
				\CUser::FormatName(
					\CSite::getNameFormat(),
					[
						'NAME' => $owner['NAME'],
						'LAST_NAME' => $owner['LAST_NAME'],
						'SECOND_NAME' => $owner['SECOND_NAME'],
						'LOGIN' => $owner['LOGIN'],
					],
					true
				)
			),
		];
	}

	private function getSubjectData(int $subjectId): array
	{
		$subject = WorkgroupSubjectTable::getList([
			'select' => ['NAME'],
			'filter' => ['ID' => $subjectId],
		])->fetch();

		return [
			'ID' => $subjectId,
			'NAME' => $subject['NAME'],
		];
	}

	private function getTags(int $groupId): array
	{
		$tags = WorkgroupTagTable::getList([
			'select' => ['NAME'],
			'filter' => ['GROUP_ID' => $groupId],
		])->fetchAll();

		return array_map(
			static function($tag) {
				return htmlspecialcharsback($tag);
			},
			array_column($tags, 'NAME')
		);
	}

	private function getThemeData(int $groupId): ?array
	{
		if (!Loader::includeModule('intranet'))
		{
			return [];
		}

		$themePicker = new ThemePicker(
			SITE_TEMPLATE_ID,
			false,
			$this->getCurrentUser()->getId(),
			ThemePicker::ENTITY_TYPE_SONET_GROUP,
			$groupId
		);

		$themeUserId = false;
		$themeId = $themePicker->getCurrentThemeId();
		if ($themeId)
		{
			$res = ThemeTable::getList([
				'select' => ['USER_ID'],
				'filter' => [
					'=ENTITY_TYPE' => $themePicker->getEntityType(),
					'ENTITY_ID' => $themePicker->getEntityId(),
					'=CONTEXT' => $themePicker->getContext(),
				],
			]);
			if (($themeFields = $res->fetch()) && (int)$themeFields['USER_ID'] > 0)
			{
				$themeUserId = (int)$themeFields['USER_ID'];
			}
		}

		return $themePicker->getTheme($themeId, $themeUserId);
	}

	private function getActions(int $groupId): array
	{
		$permissions = Helper\Workgroup::getPermissions(['groupId' => $groupId]);

		return [
			'EDIT' => $permissions['UserCanModifyGroup'],
			'DELETE' => $permissions['UserCanModifyGroup'],
			'INVITE' => $permissions['UserCanInitiate'],
			'JOIN' => (
				!$permissions['UserIsMember']
				&& !$permissions['UserRole']
			),
			'LEAVE' => (
				$permissions['UserIsMember']
				&& !$permissions['UserIsAutoMember']
				&& !$permissions['UserIsOwner']
				&& !$permissions['UserIsScrumMaster']
			),
		];
	}

	private function getUserData(int $groupId): array
	{
		$permissions = Helper\Workgroup::getPermissions(['groupId' => $groupId]);

		return [
			'ROLE' => $permissions['UserRole'],
			'INITIATED_BY_TYPE' => $permissions['InitiatedByType'],
		];
	}

	private function getDepartments($ufDepartments): array
	{
		$departments = [];

		if (
			empty($ufDepartments)
			|| !is_array($ufDepartments)
			|| !Loader::includeModule('intranet')
		)
		{
			return $departments;
		}

		$departmentsList = \CIntranetUtils::getDepartmentsData($ufDepartments);
		if (empty($departmentsList))
		{
			return $departments;
		}

		foreach ($departmentsList as $id => $name)
		{
			if (($id = (int)$id) <= 0)
			{
				continue;
			}

			$departments[] = [
				'ID' => $id,
				'NAME' => $name,
			];
		}

		return $departments;
	}

	public function updateAction(int $groupId, array $fields = []): ?bool
	{
		if (!Helper\Workgroup\Access::canModify([
			'groupId' => $groupId,
			'checkAdminSession' => ($this->getScope() !== Controller::SCOPE_REST),
		]))
		{
			$this->addError(new Error(
				Loc::getMessage('SONET_CONTROLLER_WORKGROUP_EMPTY'),
				'SONET_CONTROLLER_WORKGROUP_EMPTY')
			);
			return null;
		}

		$whiteList = [
			'KEYWORDS',
		];

		foreach ($fields as $key => $value)
		{
			if (!in_array($key, $whiteList, true))
			{
				unset($fields[$key]);
			}
		}

		if (
			empty($fields)
		)
		{
			$this->addError(new Error(
				Loc::getMessage('SONET_CONTROLLER_WORKGROUP_ACTION_FAILED'),
				'SONET_CONTROLLER_WORKGROUP_ACTION_FAILED')
			);
			return null;
		}

		try
		{
			$result = \CSocNetGroup::update($groupId, $fields);
		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		if (!$result)
		{
			$this->addError(new Error(
				Loc::getMessage('SONET_CONTROLLER_WORKGROUP_ACTION_FAILED'),
				'SONET_CONTROLLER_WORKGROUP_ACTION_FAILED')
			);
			return null;
		}

		return true;
	}

	public function deleteAction(int $groupId)
	{
		global $APPLICATION;

		$deleteResult = \CSocNetGroup::Delete($groupId);
		if (!$deleteResult && ($e = $APPLICATION->GetException()))
		{
			return $e->GetString();
		}

		return true;
	}

	public function getAvatarTypesAction(): array
	{
		return Helper\Workgroup::getAvatarTypes();
	}

	public function disconnectDepartmentsAction(int $groupId, array $departmentIds)
	{
		foreach ($departmentIds as $id)
		{
			Helper\Workgroup::disconnectDepartment([
				'groupId' => $groupId,
				'departmentId' => $id,
			]);
		}
	}

	public function setFavoritesAction(array $params = [])
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$value = (isset($params['value']) && in_array($params['value'], [ 'Y', 'N' ]) ? $params['value'] : false);
		$getAdditionalResultData = (bool)($params['getAdditionalResultData'] ?? false);

		if ($groupId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_WORKGROUP_EMPTY'), 'SONET_CONTROLLER_WORKGROUP_EMPTY'));
			return null;
		}

		if ($value === false)
		{
			$this->addError(new Error('SONET_CONTROLLER_WORKGROUP_INCORRECT_VALUE', 'SONET_CONTROLLER_WORKGROUP_INCORRECT_VALUE'));
			return null;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('SONET_CONTROLLER_MODULE_NOT_INSTALLED', 'SONET_CONTROLLER_MODULE_NOT_INSTALLED'));
			return null;
		}

		try
		{
			$res = \Bitrix\Socialnetwork\Item\WorkgroupFavorites::set([
				'GROUP_ID' => $groupId,
				'USER_ID' => $this->getCurrentUser()->getId(),
				'VALUE' => $value,
			]);
		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		if (!$res)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_WORKGROUP_ACTION_FAILED'), 'SONET_CONTROLLER_WORKGROUP_ACTION_FAILED'));
			return null;
		}

		if ($getAdditionalResultData)
		{
			$groupItem = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupId);
			$groupFields = $groupItem->getFields();
			$groupUrlData = $groupItem->getGroupUrlData([
				'USER_ID' => $this->getCurrentUser()->getId(),
			]);

			$groupSiteList = [];
			$resSite = \Bitrix\Socialnetwork\WorkgroupSiteTable::getList([
				'filter' => [
					'=GROUP_ID' => $groupId
				],
				'select' => [ 'SITE_ID' ],
			]);
			while ($groupSite = $resSite->fetch())
			{
				$groupSiteList[] = $groupSite['SITE_ID'];
			}
		}

		$result = [
			'ID' => $groupId,
			'RESULT' => $value,
		];

		if ($getAdditionalResultData)
		{
			$result['NAME'] = $groupFields['NAME'];
			$result['URL'] = $groupUrlData["URL"];
			$result['EXTRANET'] = (
				Loader::includeModule('extranet')
				&& \CExtranet::isIntranetUser()
				&& in_array(\CExtranet::getExtranetSiteId(), $groupSiteList)
					? 'Y'
					: 'N'
			);
		}

		$currentUserId = $this->getCurrentUser()->getId();

		PushService::addEvent(
			[ $currentUserId ],
			[
				'module_id' => 'socialnetwork',
				'command' => PushEventDictionary::EVENT_WORKGROUP_FAVORITES_CHANGED,
				'params' => [
					'GROUP_ID' => $groupId,
					'USER_ID' => $currentUserId,
				],
			]
		);

		return $result;
	}

	public function setSubscriptionAction(array $params = [])
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$value = (isset($params['value']) && in_array($params['value'], [ 'Y', 'N' ]) ? $params['value'] : false);

		if ($groupId <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_WORKGROUP_EMPTY'), 'SONET_CONTROLLER_WORKGROUP_EMPTY'));
			return null;
		}

		if ($value === false)
		{
			$this->addError(new Error('SONET_CONTROLLER_WORKGROUP_INCORRECT_VALUE', 'SONET_CONTROLLER_WORKGROUP_INCORRECT_VALUE'));
			return null;
		}

		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('SONET_CONTROLLER_MODULE_NOT_INSTALLED', 'SONET_CONTROLLER_MODULE_NOT_INSTALLED'));
			return null;
		}

		try
		{
			$res = \Bitrix\Socialnetwork\Item\Subscription::set([
				'GROUP_ID' => $groupId,
				'USER_ID' => $this->getCurrentUser()->getId(),
				'VALUE' => $value,
			]);
		}
		catch (\Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		return [
			'RESULT' => ($res ? 'Y' : 'N'),
		];
	}

	public function getCanCreateAction(): bool
	{
		return Helper\Workgroup\Access::canCreate([
			'checkAdminSession' => ($this->getScope() !== Controller::SCOPE_REST),
		]);
	}

	public function getGridPopupMembersAction(
		int $groupId,
		string $type,
		int $page,
		string $componentName = '',
		string $signedParameters = ''
	): ?array
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('SONET_CONTROLLER_MODULE_NOT_INSTALLED', 'SONET_CONTROLLER_MODULE_NOT_INSTALLED'));
			return null;
		}

		if (
			$groupId <= 0
			|| !Helper\Workgroup\Access::canView([
				'groupId' => $groupId,
				'checkAdminSession' => ($this->getScope() !== Controller::SCOPE_REST),
			])
		)
		{
			$this->addError(
				new Error(
					Loc::getMessage('SONET_CONTROLLER_WORKGROUP_EMPTY'),
					'SONET_CONTROLLER_WORKGROUP_EMPTY'
				)
			);

			return null;
		}

		$unsignedParameters = [];
		if (
			$componentName !== ''
			&& $signedParameters !== ''
		)
		{
			$unsignedParameters = \Bitrix\Main\Component\ParameterSigner::unsignParameters(
				$componentName,
				$signedParameters
			);
			if (!is_array($unsignedParameters))
			{
				$unsignedParameters = [];
			}
		}

		$rolesMap = [
			'all' => [
				UserToGroupTable::ROLE_OWNER,
				UserToGroupTable::ROLE_MODERATOR,
				UserToGroupTable::ROLE_USER,
			],
			'heads' => [
				UserToGroupTable::ROLE_OWNER,
				UserToGroupTable::ROLE_MODERATOR,
			],
			'members' => [
				UserToGroupTable::ROLE_USER,
			],
			'scrumTeam' => [
				UserToGroupTable::ROLE_OWNER,
				UserToGroupTable::ROLE_MODERATOR,
			],
		];
		$limit = 10;

		$query = new Query(UserToGroupTable::getEntity());
		$records = $query
			->setSelect([
				'GROUP_ID',
				'GROUP',
				'USER_ID',
				'ROLE',
				'INITIATED_BY_TYPE',
				'AUTO_MEMBER',
				'NAME' => 'USER.NAME',
				'LAST_NAME' => 'USER.LAST_NAME',
				'SECOND_NAME' => 'USER.SECOND_NAME',
				'LOGIN' => 'USER.LOGIN',
				'PERSONAL_PHOTO' => 'USER.PERSONAL_PHOTO',
			])
			->where('GROUP_ID', $groupId)
			->whereIn('ROLE', $rolesMap[$type])
			->setLimit($limit)
			->setOffset(($page - 1) * $limit)
			->exec()->fetchCollection();

		$isScrumMembers = ($type === 'scrumTeam');
		if ($isScrumMembers)
		{
			$query->addSelect('GROUP.SCRUM_MASTER_ID', 'SCRUM_MASTER_ID');
		}

		$imageIds = [];
		$resultMembers = [];

		foreach ($records as $member)
		{
			$imageIds[$member->get('USER_ID')] = $member->get('USER')->get('PERSONAL_PHOTO');
			$resultMembers[] = $member;
		}

		$imageIds = array_filter(
			$imageIds,
			static function ($id) { return (int)$id > 0; }
		);
		$avatars = Helper\UI\Grid\Workgroup\Members::getUserAvatars($imageIds);
		$pathToUser = ($unsignedParameters['PATH_TO_USER'] ?? Helper\Path::get('user_profile'));
		$userNameTemplate = ($unsignedParameters['NAME_TEMPLATE'] ?? \CSite::getNameFormat());
		$isIntranetInstalled = ModuleManager::isModuleInstalled('intranet');

		$members = [];

		foreach ($resultMembers as $member)
		{
			$id = $member->get('USER_ID');
			$userNameFormatted = \CUser::formatName($userNameTemplate, [
				'NAME' => $member->get('USER')->get('NAME'),
				'LAST_NAME' => $member->get('USER')->get('LAST_NAME'),
				'SECOND_NAME' => $member->get('USER')->get('SECOND_NAME'),
				'LOGIN' => $member->get('USER')->get('LOGIN'),
			], $isIntranetInstalled);

			$userUrl = \CComponentEngine::makePathFromTemplate($pathToUser, [
				'user_id' => $id,
				'id' => $id,
			]);

			$members[] = [
				'ID' => $id,
				'PHOTO' => $avatars[$imageIds[$id] ?? null] ?? '',
				'HREF' => $userUrl,
				'FORMATTED_NAME' => $userNameFormatted,
				'ROLE' => ($isScrumMembers ? $this->getScrumRole($member) : $member->get('ROLE')),
			];

			if ($isScrumMembers)
			{
				if (
					$member->get('ROLE') === UserToGroupTable::ROLE_OWNER
					&& $member->get('USER_ID') === $member->get('GROUP')->get('SCRUM_MASTER_ID')
				)
				{
					$members[] = [
						'ID' => $id,
						'PHOTO' => $avatars[$imageIds[$id] ?? null] ?? '',
						'HREF' => $userUrl,
						'FORMATTED_NAME' => $userNameFormatted,
						'ROLE' => 'M',
					];
				}
			}
		}

		return $members;
	}

	private function getScrumRole(EO_UserToGroup $member): string
	{
		if (
			$member->get('USER_ID') === $member->get('GROUP')->get('SCRUM_MASTER_ID')
			&& $member->get('ROLE') !== UserToGroupTable::ROLE_OWNER
		)
		{
			return 'M';
		}

		return $member->get('ROLE');
	}

	public function changePinAction(
		array $groupIdList,
		string $action,
		string $componentName = '',
		string $signedParameters = ''
	): ?bool
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			$this->addError(new Error('SONET_CONTROLLER_MODULE_NOT_INSTALLED', 'SONET_CONTROLLER_MODULE_NOT_INSTALLED'));
			return null;
		}

		$unsignedParameters = [];
		if (
			$componentName !== ''
			&& $signedParameters !== ''
		)
		{
			$unsignedParameters = \Bitrix\Main\Component\ParameterSigner::unsignParameters($componentName, $signedParameters);
			if (!is_array($unsignedParameters))
			{
				$unsignedParameters = [];
			}
		}

		$mode = ($unsignedParameters['MODE'] ?? '');

		$currentUserId = $this->getCurrentUser()->getId();
		$counter = 0;

		foreach ($groupIdList as $groupId)
		{
			$groupId = (int)$groupId;

			if (
				$groupId <= 0
				|| !Helper\Workgroup\Access::canView([
					'groupId' => $groupId,
				])
			)
			{
				continue;
			}

			$counter++;

			$query = new Query(WorkgroupPinTable::getEntity());
			$query = $query
				->setSelect([
					'ID',
					'GROUP_ID',
					'USER_ID',
				])
				->where('GROUP_ID', $groupId)
				->where('USER_ID', $currentUserId);

			if ($mode === '')
			{
				$query = $query->where(Query::filter()
					->logic('or')
					->whereNull('CONTEXT')
					->where('CONTEXT', '')
				);
			}
			else
			{
				$query = $query->where('CONTEXT', $mode);
			}

			$pin = $query
				->setLimit(1)
				->exec()->fetchObject();

			$pinResult = false;
			if (
				$action === 'pin'
				&& !$pin
			)
			{
				try
				{
					WorkgroupPinTable::add([
						'GROUP_ID' => $groupId,
						'USER_ID' => $currentUserId,
						'CONTEXT' => $mode,
					]);
				}
				catch (\Exception $e)
				{
					$this->addError(
						new Error(
							Loc::getMessage('SONET_CONTROLLER_WORKGROUP_ACTION_FAILED'),
							'SONET_CONTROLLER_WORKGROUP_ACTION_FAILED'
						)
					);

					return null;
				}

				$pinResult = true;
			}
			elseif (
				$action === 'unpin'
				&& $pin
			)
			{
				$tableDeleteResult = WorkgroupPinTable::delete($pin->get('ID'));
				if (!$tableDeleteResult->isSuccess())
				{
					$this->addError(new Error(
						Loc::getMessage('SONET_CONTROLLER_WORKGROUP_ACTION_FAILED'),
						'SONET_CONTROLLER_WORKGROUP_ACTION_FAILED'
					));
					return null;
				}

				$pinResult = true;
			}

			if ($pinResult === true)
			{
				PushService::addEvent(
					[ $currentUserId ],
					[
						'module_id' => 'socialnetwork',
						'command' => 'workgroup_pin_changed',
						'params' => [
							'GROUP_ID' => $groupId,
							'USER_ID' => $currentUserId,
							'ACTION' => $action,
						],
					]
				);
			}
		}

		if ($counter <= 0)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_WORKGROUP_EMPTY'), 'SONET_CONTROLLER_WORKGROUP_EMPTY'));
			return null;
		}

		return true;
	}

}
