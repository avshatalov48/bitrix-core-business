<?php

namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Intranet\Integration\Templates\Bitrix24\ThemePicker;
use Bitrix\Intranet\Internals\ThemeTable;
use Bitrix\Main\Context;
use Bitrix\Main\Engine;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\Collab\Integration\IM\Dialog;
use Bitrix\Socialnetwork\EO_UserToGroup;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Socialnetwork\Helper\AvatarManager;
use Bitrix\Socialnetwork\Integration\Im\Chat;
use Bitrix\Socialnetwork\Integration\Main\File;
use Bitrix\Socialnetwork\Integration\Pull\PushService;
use Bitrix\Socialnetwork\Internals\Counter;
use Bitrix\Socialnetwork\Internals\Counter\CounterDictionary;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Push\PushEventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Service;
use Bitrix\Socialnetwork\Item\Subscription;
use Bitrix\Socialnetwork\Item\WorkgroupFavorites;
use Bitrix\Socialnetwork\Provider\GroupProvider;
use Bitrix\Socialnetwork\Space\MembersManager;
use Bitrix\Socialnetwork\Space\Toolbar\Switcher\Option\Pin;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupPinTable;
use Bitrix\Socialnetwork\WorkgroupSiteTable;
use Bitrix\Socialnetwork\WorkgroupSubjectTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\WorkgroupTagTable;
use Bitrix\Tasks\Internals\Effective;
use CExtranet;
use Exception;

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
			'TYPE',
		];
	}

	public function getAction(array $params = []): ?array
	{
		$groupId = (int)($params['groupId'] ?? 0);

		if ($groupId <= 0)
		{
			$this->addEmptyGroupIdError();
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

			if (in_array('DATE_CREATE', $select, true))
			{
				$culture = Context::getCurrent()->getCulture();
				$longDateFormat = $culture->getLongDateFormat();
				$shortTimeFormat = $culture->getShortTimeFormat();

				$groupFields['DATE_CREATE'] = \CComponentUtil::getDateTimeFormatted([
					'TIMESTAMP' => MakeTimeStamp($groupFields['DATE_CREATE']),
					'TZ_OFFSET' => \CTimeZone::getOffset(),
				], "$longDateFormat, $shortTimeFormat");
			}
			if (in_array('AVATAR', $select, true))
			{
				$groupFields['AVATAR'] = File::getFileSource((int)$groupFields['IMAGE_ID'], 100, 100);
			}
			if (in_array('AVATAR_TYPES', $select, true))
			{
				$groupFields['AVATAR_TYPES'] = Helper\Workgroup::getAvatarTypes();
			}
			if (in_array('AVATAR_DATA', $select, true))
			{
				$imageId = (int) $groupFields['IMAGE_ID'];
				$avatarType = $groupFields['AVATAR_TYPE'] ?? '';
				$groupFields['AVATAR_DATA'] = $this->getAvatarData($imageId, $avatarType);
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
			if (in_array('PIN', $select, true))
			{
				$groupFields['IS_PIN'] = $this->isPin($groupId, $this->getCurrentUser()->getId());
			}
			if (in_array('PRIVACY_TYPE', $select, true))
			{
				$groupFields['PRIVACY_CODE'] = Helper\Workgroup::getConfidentialityTypeCodeByParams([
					'fields' => [
						'OPENED' => $groupFields['OPENED'],
						'VISIBLE' => $groupFields['VISIBLE'],
					],
				]);
			}
			if (in_array('LIST_OF_MEMBERS', $select, true))
			{
				$groupFields['LIST_OF_MEMBERS'] = $this->getListOfMembers(
					$groupId,
					$groupItem->getScrumMaster()
				);
			}
			if (in_array('FEATURES', $select, true))
			{
				$groupFields['FEATURES'] = $this->prepareFeatures($groupId);
			}
			$needListOfAwaiting = in_array('LIST_OF_MEMBERS_AWAITING_INVITE', $select, true);
			$needMembersList = in_array('GROUP_MEMBERS_LIST', $select, true);
			if ($needListOfAwaiting || $needMembersList)
			{
				$permissions = Helper\Workgroup::getPermissions(
					['groupId' => $groupId],
				);
			}
			if ($needListOfAwaiting)
			{
				$groupFields['LIST_OF_MEMBERS_AWAITING_INVITE'] = [];
				if ($permissions['UserCanModifyGroup'] || $permissions['UserCanInitiate'])
				{
					$groupFields['LIST_OF_MEMBERS_AWAITING_INVITE'] = $this->getListOfAwaitingMembers($groupId);
				}
			}
			if ($needMembersList)
			{
				$groupFields['GROUP_MEMBERS_LIST'] = [];
				if ($permissions['UserCanModifyGroup'] || $permissions['UserCanInitiate'])
				{
					$membersManager = new MembersManager();
					$groupFields['GROUP_MEMBERS_LIST'] = $membersManager->getGroupMembersList($groupId);
				}
			}

			if (in_array('COUNTERS', $select, true))
			{
				$groupFields['COUNTERS'] = $this->getCounters($groupId);
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

			$isScrum = !empty($groupFields['SCRUM_MASTER_ID']);
			if (!$isScrum && in_array('EFFICIENCY', $select, true) && Loader::includeModule('tasks'))
			{
				$efficiencies = Effective::getAverageEfficiencyForGroups(
					null,
					null,
					0,
					[$group['ID']],
				);

				$groupFields['EFFICIENCY'] = $efficiencies[$group['ID']] ?? null;
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

		if (($params['shouldSelectDialogId'] ?? 'N') === 'Y')
		{
			$chatData = Chat\Workgroup::getChatData([
				'group_id' => $ids,
				'skipAvailabilityCheck' => true,
			]);

			foreach ($workgroups as $id => $fields)
			{
				$workgroups[$id]['DIALOG_ID'] = Dialog::getDialogId($chatData[$id] ?? 0);
			}
		}

		$workgroups = $this->convertKeysToCamelCase($workgroups);

		return new Engine\Response\DataType\Page('workgroups', array_values($workgroups), $count);
	}

	/**
	 * @restMethod socialnetwork.api.workgroup.isExistingGroup
	 */
	public function isExistingGroupAction(string $name): array
	{
		return [
			'exists' => GroupProvider::getInstance()->isExistingGroup($name),
		];
	}

	private function prepareSelect(array $select = []): array
	{
		return array_filter($select, static function ($key) {
			return in_array(mb_strtoupper($key), static::getAllowedSelectFields(), true);
		});
	}

	private function getAvatarData(int $imageId, string $avatarType): array
	{
		$avatarManager = new AvatarManager();

		if ($imageId)
		{
			$avatarData = $avatarManager->getImageAvatar($imageId)->toArray();
		}
		else
		{
			$avatarData = $avatarManager->getIconAvatar($avatarType)->toArray();
		}

		return $avatarData;
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

		$canEditFeatures = $permissions['UserCanModifyGroup'];
		if (!\Bitrix\Socialnetwork\Helper\Workgroup::getEditFeaturesAvailability())
		{
			$canEditFeatures = false;
		}

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
			'FOLLOW' => $permissions['UserIsMember'],
			'PIN' => $permissions['UserIsMember'],
			'EDIT_FEATURES' => $canEditFeatures,
		];
	}

	private function getUserData(int $groupId): array
	{
		$permissions = Helper\Workgroup::getPermissions(['groupId' => $groupId]);

		return [
			'ROLE' => $permissions['UserRole'],
			'INITIATED_BY_TYPE' => $permissions['InitiatedByType'],
			'IS_SUBSCRIBED' => (
				in_array($permissions['UserRole'], UserToGroupTable::getRolesMember(), true)
				&& \CSocNetSubscription::isUserSubscribed($this->getCurrentUser()->getId(), 'SG' . $groupId)
			),
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

	private function isPin(int $groupId, int $currentUserId, string $context = ''): bool
	{
		$query = new Query(WorkgroupPinTable::getEntity());

		$query = $query
			->setSelect([
				'ID',
				'GROUP_ID',
				'USER_ID',
			])
			->where('GROUP_ID', $groupId)
			->where('USER_ID', $currentUserId)
		;
		if ($context === '')
		{
			$query = $query->where(Query::filter()
				->logic('or')
				->whereNull('CONTEXT')
				->where('CONTEXT', '')
			);
		}
		else
		{
			$query = $query->where('CONTEXT', $context);
		}

		$pin = $query->setLimit(1)->exec()->fetchObject();

		return (bool) $pin;
	}

	private function getListOfMembers(int $groupId, int $scrumMasterId): array
	{
		$list = [];

		$records = UserToGroupTable::query()
			->setSelect([
				'GROUP_ID',
				'USER_ID',
				'ROLE',
				'INITIATED_BY_TYPE',
				'AUTO_MEMBER',
				'NAME' => 'USER.NAME',
				'LAST_NAME' => 'USER.LAST_NAME',
				'SECOND_NAME' => 'USER.SECOND_NAME',
				'WORK_POSITION' => 'USER.WORK_POSITION',
				'LOGIN' => 'USER.LOGIN',
				'PERSONAL_PHOTO' => 'USER.PERSONAL_PHOTO',
			])
			->whereIn('GROUP_ID', $groupId)
			->whereIn('ROLE', UserToGroupTable::getRolesMember())
			->exec()->fetchCollection()
		;

		$members = [];
		$imageIdList = [];
		foreach ($records as $record)
		{
			$user = $record->get('USER');
			$imageIdList[$record->get('USER_ID')] = $user->get('PERSONAL_PHOTO');
			$members[] = $record;
		}
		$imageIdList = array_filter(
			$imageIdList,
			static function ($id) {
				return (int) $id > 0;
			}
		);
		$avatars = $this->getUserAvatars($imageIdList);

		foreach ($members as $member)
		{
			$memberId = (int) $member['USER_ID'];

			$isOwner = ($member['ROLE'] === UserToGroupTable::ROLE_OWNER);
			$isModerator = ($member['ROLE'] === UserToGroupTable::ROLE_MODERATOR);
			$isScrumMaster = ($scrumMasterId === $memberId);
			$memberUser = $member->getUser();

			$list[] = [
				'id' => $memberId,
				'isOwner' => $isOwner,
				'isModerator' => $isModerator,
				'isScrumMaster' => $isScrumMaster,
				'isAutoMember' => $member['AUTO_MEMBER'],
				'name' => $memberUser->getName(),
				'lastName' => $memberUser->getLastName(),
				'position' => $memberUser->getWorkPosition(),
				'photo' => ($avatars[($imageIdList[$memberId] ?? '')] ?? ''),
			];
		}

		return $list;
	}

	private function prepareFeatures(int $groupId): array
	{
		$features = [];

		$baseFeatures = $this->getBaseFeatures($groupId);

		foreach ($this->getAllowedFeatures() as $featureId => $feature)
		{
			if (array_key_exists($featureId, $baseFeatures))
			{
				$features[] = [
					'featureName' => $featureId,
					'name' => Loc::getMessage('SOCIALNETWORK_WORKGROUP_'.strtoupper($featureId)),
					'customName' => $baseFeatures[$featureId]['FEATURE_NAME'] ?? '',
					'id' => $baseFeatures[$featureId]['ID'],
					'active' => $baseFeatures[$featureId]['ACTIVE'] === 'Y',
				];
			}
		}

		return $features;
	}

	private function getBaseFeatures(int $groupId): array
	{
		$features = [];

		$queryObject = \CSocNetFeatures::getList(
			[],
			[
				'ENTITY_ID' => $groupId,
				'ENTITY_TYPE' => SONET_ENTITY_GROUP,
			]
		);
		while ($featureFields = $queryObject->fetch())
		{
			$features[$featureFields['FEATURE']]= $featureFields;
		}

		return $features;
	}

	private function getAllowedFeatures(): array
	{
		$allowedFeatures = \CSocNetAllowed::getAllowedFeatures();

		$sampleKeysList = [
			'tasks' => 1,
			'calendar' => 2,
			'files' => 3,
			'chat' => 4,
			'forum' => 5,
			'microblog' => 6,
			'blog' => 7,
			'photo' => 8,
			'group_lists' => 9,
			'wiki' => 10,
			'content_search' => 11,
			'marketplace' => 12,
		];

		uksort($allowedFeatures, static function($a, $b) use ($sampleKeysList) {

			$valA = ($sampleKeysList[$a] ?? 100);
			$valB = ($sampleKeysList[$b] ?? 100);

			if ($valA > $valB)
			{
				return 1;
			}

			if ($valA < $valB)
			{
				return -1;
			}

			return 0;
		});

		return array_filter($allowedFeatures, function($feature) {
			return (
				is_array($feature['allowed'])
				&& in_array(SONET_ENTITY_GROUP, $feature['allowed'], true)
			);
		});
	}

	private function getListOfAwaitingMembers(int $groupId, int $limit = 10, int $offset = 0): array
	{
		$list = [];

		$records = UserToGroupTable::query()
			->setSelect([
				'GROUP_ID',
				'USER_ID',
				'ROLE',
				'INITIATED_BY_TYPE',
				'NAME' => 'USER.NAME',
				'LAST_NAME' => 'USER.LAST_NAME',
				'SECOND_NAME' => 'USER.SECOND_NAME',
				'LOGIN' => 'USER.LOGIN',
				'PERSONAL_PHOTO' => 'USER.PERSONAL_PHOTO',
			])
			->whereIn('GROUP_ID', $groupId)
			->where('INITIATED_BY_TYPE', UserToGroupTable::INITIATED_BY_USER)
			->where('ROLE', UserToGroupTable::ROLE_REQUEST)
			->setLimit($limit)
			->setOffset($offset)
			->exec()->fetchCollection()
		;

		$members = [];
		$imageIdList = [];
		foreach ($records as $record)
		{
			$user = $record->get('USER');
			$imageIdList[$record->get('USER_ID')] = $user->get('PERSONAL_PHOTO');

			$members[] = $record;
		}
		$imageIdList = array_filter(
			$imageIdList,
			static function ($id) {
				return (int) $id > 0;
			}
		);
		$avatars = $this->getUserAvatars($imageIdList);

		foreach ($members as $member)
		{
			$memberId = (int) $member['USER_ID'];

			$userNameFormatted = \CUser::formatName(\CSite::getNameFormat(), [
				'NAME' => $member->get('USER')->get('NAME'),
				'LAST_NAME' => $member->get('USER')->get('LAST_NAME'),
				'SECOND_NAME' => $member->get('USER')->get('SECOND_NAME'),
				'LOGIN' => $member->get('USER')->get('LOGIN'),
			], ModuleManager::isModuleInstalled('intranet'));

			$list[] = [
				'id' => $memberId,
				'name' => $userNameFormatted,
				'photo' => ($avatars[($imageIdList[$memberId] ?? '')] ?? ''),
			];
		}

		return $list;
	}

	private function getCounters(int $groupId): array
	{
		$counters = [];

		$counterProvider = Counter::getInstance($this->getCurrentUser()->getId());

		$availableCounters = [
			CounterDictionary::COUNTER_WORKGROUP_REQUESTS_OUT,
			CounterDictionary::COUNTER_WORKGROUP_REQUESTS_IN,
		];
		foreach ($availableCounters as $counter)
		{
			$counters[$counter] = $counterProvider->get($counter, $groupId)['all'];
		}

		return $counters;
	}

	public function updateAction(int $groupId, array $fields = []): ?bool
	{
		if (!Helper\Workgroup\Access::canModify([
			'groupId' => $groupId,
			'checkAdminSession' => ($this->getScope() !== Controller::SCOPE_REST),
		]))
		{
			$this->addEmptyGroupIdError();
			return null;
		}

		$whiteList = [
			'NAME',
			'DESCRIPTION',
			'KEYWORDS',
			'VISIBLE',
			'OPENED',
			'EXTERNAL',
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
		catch (Exception $e)
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

		$this->sendPush(PushEventDictionary::EVENT_WORKGROUP_UPDATE, [
			'params' => [
				'GROUP_ID' => $groupId,
			],
		]);

		return true;
	}

	public function leaveAction(int $groupId)
	{
		if (!Helper\Workgroup\Access::canLeave([ 'groupId' => $groupId ]))
		{
			$this->addError(new Error('NO PERMISSION'));
			return null;
		}

		return \CSocNetUserToGroup::DeleteRelation($this->userId, $groupId);
	}

	public function deleteAction(int $groupId)
	{
		if (
			!Helper\Workgroup\Access::canModify([
				'groupId' => $groupId,
				'checkAdminSession' => ($this->getScope() !== Controller::SCOPE_REST),
			])
		)
		{
			$this->addEmptyGroupIdError();

			return null;
		}

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

	public function setFavoritesAction(array $params = []): ?array
	{
		$groupId = (int)($params['groupId'] ?? 0);
		$getAdditionalResultData = (bool)($params['getAdditionalResultData'] ?? false);

		if ($groupId <= 0)
		{
			$this->addEmptyGroupIdError();
			return null;
		}

		if (!in_array($params['value'] ?? null, WorkgroupFavorites::AVAILABLE_VALUES, true))
		{
			$this->addIncorrectValueError();
			return null;
		}

		try
		{
			$res = WorkgroupFavorites::set([
				'GROUP_ID' => $groupId,
				'USER_ID' => $this->getCurrentUser()->getId(),
				'VALUE' => $params['value'],
			]);
		}
		catch (Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		if (!$res)
		{
			$this->addError(new Error(Loc::getMessage('SONET_CONTROLLER_WORKGROUP_ACTION_FAILED'), 'SONET_CONTROLLER_WORKGROUP_ACTION_FAILED'));
			return null;
		}

		$result = [
			'ID' => $groupId,
			'RESULT' => $params['value'],
		];

		if ($getAdditionalResultData)
		{
			$groupItem = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupId);
			$groupFields = $groupItem->getFields();
			$groupUrlData = $groupItem->getGroupUrlData([
				'USER_ID' => $this->getCurrentUser()->getId(),
			]);

			$groupSiteList = [];
			$resSite = WorkgroupSiteTable::getList([
				'filter' => [
					'=GROUP_ID' => $groupId
				],
				'select' => [ 'SITE_ID' ],
			]);
			while ($groupSite = $resSite->fetch())
			{
				$groupSiteList[] = $groupSite['SITE_ID'];
			}

			$result['NAME'] = $groupFields['NAME'];
			$result['URL'] = $groupUrlData["URL"];
			$result['EXTRANET'] = (
			Loader::includeModule('extranet')
			&& CExtranet::isIntranetUser()
			&& in_array(CExtranet::getExtranetSiteId(), $groupSiteList, true)
				? 'Y'
				: 'N'
			);
		}

		$this->sendPush(PushEventDictionary::EVENT_WORKGROUP_FAVORITES_CHANGED, ['GROUP_ID' => $groupId]);

		return $result;
	}

	public function setSubscriptionAction(array $params = []): ?array
	{
		$groupId = (int)($params['groupId'] ?? 0);
		if ($groupId <= 0)
		{
			$this->addEmptyGroupIdError();
			return null;
		}

		if (!in_array($params['value'] ?? null, Subscription::AVAILABLE_VALUES, true))
		{
			$this->addIncorrectValueError();
			return null;
		}

		try
		{
			$result = Subscription::set([
				'GROUP_ID' => $groupId,
				'USER_ID' => $this->getCurrentUser()->getId(),
				'VALUE' => $params['value'],
			]);
		}
		catch (Exception $e)
		{
			$this->addError(new Error($e->getMessage(), $e->getCode()));
			return null;
		}

		$this->sendPush(PushEventDictionary::EVENT_WORKGROUP_SUBSCRIBE_CHANGED, ['GROUP_ID' => $groupId]);

		return [
			'RESULT' => $result ? 'Y' : 'N',
		];
	}

	public function updatePhotoAction(): bool
	{
		$groupId = $this->getRequest()->get('groupId');

		if (!Helper\Workgroup\Access::canUpdate([ 'groupId' => $groupId ]))
		{
			$this->addError(new Error('SOCIALNETWORK_GROUP_AJAX_NO_UPDATE_PERMS'));
			return false;
		}

		$workgroupData = WorkgroupTable::getList([
			'select' => [ 'ID', 'IMAGE_ID' ],
			'filter' => [
				'=ID' => $groupId,
			],
		])->fetch();

		$newPhotoFile = $this->getRequest()->getFile('newPhoto');

		if ($workgroupData['IMAGE_ID'])
		{
			$newPhotoFile['old_file'] = $workgroupData['IMAGE_ID'];
			$newPhotoFile['del'] = $workgroupData['IMAGE_ID'];
		}

		$res = \CSocNetGroup::update(
			$groupId,
			[ 'IMAGE_ID' => $newPhotoFile ],
			true,
			true,
			false
		);

		if (!$res)
		{
			$this->addError(new Error('SOCIALNETWORK_GROUP_AJAX_FAILED'));
			return false;
		}

		$this->sendPush(PushEventDictionary::EVENT_WORKGROUP_UPDATE, [
			'params' => [
				'GROUP_ID' => $groupId,
			],
		]);

		return true;
	}

	public function createGroupAction(): array
	{
		$groupName = $this->getRequest()->get('groupName');
		$viewMode = $this->getRequest()->get('viewMode');
		$groupImage = $this->getRequest()->getFile('groupImage');
		$avatarColor = $this->getRequest()->get('avatarColor');

		if (!Helper\Workgroup\Access::canCreate())
		{
			return ['groupId' => 0];
		}

		$ownerId = $this->getCurrentUser()->getId();

		$groupParams = [
			'SITE_ID' => [SITE_ID],
			'NAME' => $groupName,
			'SUBJECT_ID' => $this->getDefaultSubjectId(),
			'INITIATE_PERMS' => SONET_ROLES_USER,
			'SPAM_PERMS' => SONET_ROLES_USER,
			'VISIBLE' => $viewMode !== 'secret' ? 'Y' : 'N',
			'OPENED' => $viewMode === 'open' ? 'Y' : 'N',
		];

		if (is_array($groupImage))
		{
			try
			{
				$avatarManager = new AvatarManager();
				$result = $avatarManager->loadAvatar($groupImage);
				$groupParams['IMAGE_ID'] = $avatarManager->getAvatar($result['fileId']);
			}
			catch (\RuntimeException)
			{
				unset($groupParams['IMAGE_ID']);
			}
		}

		if (!isset($groupParams['IMAGE_ID']))
		{
			$groupParams['AVATAR_TYPE'] = $this->getColoredDefaultAvatar($avatarColor);
		}

		$groupId = (int)\CSocNetGroup::createGroup($ownerId, $groupParams);

		global $APPLICATION;
		if ($e = $APPLICATION->GetException())
		{
			$this->addError(new Error($e->msg, $e->id));
			return [];
		}

		$this->setDefaultGroupFeatures($groupId);

		return [
			'groupId' => $groupId,
		];
	}

	/**
	 * The method will enable trial mode for projects or scrum.
	 *
	 * @param bool $scrum If you need to create a trial for scrum.
	 * @return bool
	 */
	public function turnOnTrialAction(bool $scrum = false): bool
	{
		if (!Helper\Workgroup\Access::canCreate())
		{
			$this->addError(new Error('Access denied'));

			return false;
		}

		$feature = $scrum ? Helper\Feature::SCRUM_CREATE : Helper\Feature::PROJECTS_GROUPS;

		if (
			!Helper\Feature::isFeatureEnabled($feature)
			&& Helper\Feature::canTurnOnTrial($feature)
		)
		{
			Helper\Feature::turnOnTrial($feature);

			return true;
		}
		else
		{
			$this->addError(new Error('Already included'));

			return false;
		}
	}

	private function getColoredDefaultAvatar(string $color): string
	{
		if (in_array($color, Helper\Workgroup::getAvatarColors(), true))
		{
			return "space_$color";
		}

		return array_rand(Helper\Workgroup::getColoredAvatarTypes());
	}

	private function setDefaultGroupFeatures(int $groupId): void
	{
		$allowedFeatures = array_keys(\CSocNetAllowed::getAllowedFeatures());
		$inactiveFeaturesList = ['forum', 'photo', 'search', 'group_lists', 'wiki'];

		$features = [];
		foreach ($allowedFeatures as $featureName)
		{
			$features[$featureName] = !in_array($featureName, $inactiveFeaturesList, true);
		}

		foreach ($features as $featureName => $isActive)
		{
			\CSocNetFeatures::setFeature(
				SONET_ENTITY_GROUP,
				$groupId,
				$featureName,
				$isActive,
			);
		}
	}

	private function getDefaultSubjectId(): int
	{
		$subject = \CSocNetGroupSubject::GetList(
			["SORT"=>"ASC", "NAME" => "ASC"],
			["SITE_ID" => SITE_ID],
			false,
			false,
			["ID", "NAME"],
		)->fetch();

		return (int)($subject['ID'] ?? 0);
	}

	public function getCanCreateAction(): bool
	{
		return Helper\Workgroup\Access::canCreate([
			'checkAdminSession' => ($this->getScope() !== Controller::SCOPE_REST),
		]);
	}

	public function updateInvitedUsersAction(int $spaceId, array $users): void
	{
		$membersManager = new MembersManager();

		if (!$membersManager->canInviteUsers($spaceId))
		{
			return;
		}

		$membersManager->updateInvitedUsers($spaceId, array_map(static fn($userId) => (int)$userId, $users));
	}

	public function getGridPopupMembersAction(
		int $groupId,
		string $type,
		int $page,
		string $componentName = '',
		string $signedParameters = ''
	): ?array
	{
		if (
			$groupId <= 0
			|| !Helper\Workgroup\Access::canView([
				'groupId' => $groupId,
				'checkAdminSession' => ($this->getScope() !== Controller::SCOPE_REST),
			])
		)
		{
			$this->addEmptyGroupIdError();
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

		$query = UserToGroupTable::query();
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

	/**
	 * @restMethod socialnetwork.api.workgroup.changePin
	 */
	public function changePinAction(
		array $groupIdList,
		string $action,
		string $componentName = '',
		string $signedParameters = ''
	): ?bool
	{
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

			$pin = new Pin($this->userId, $groupId, $mode);
			$result = $pin->switch();

			if (!$result->isSuccess())
			{
				$this->addErrors($result->getErrors());
				return null;
			}

			$this->sendPush(PushEventDictionary::EVENT_WORKGROUP_PIN_CHANGED, [
					'GROUP_ID' => $groupId,
					'ACTION' => $action,
				]
			);
		}

		if ($counter <= 0)
		{
			$this->addEmptyGroupIdError();
			return null;
		}

		return true;
	}

	public function acceptIncomingRequestAction(int $groupId, array $userIds): ?array
	{
		try
		{
			$result = [];

			foreach ($userIds as $userId)
			{
				$result[$userId] = Helper\Workgroup::acceptIncomingRequest([
					'groupId' => $groupId,
					'userId' => $userId,
				]);
			}

			// re-calculte counters for the group moderators
			$moderators = UserToGroupTable::getGroupModerators($groupId);
			Service::addEvent(
				EventDictionary::EVENT_WORKGROUP_MEMBER_REQUEST_CONFIRM,
				[
					'GROUP_ID' => $groupId,
					'RECEPIENTS' => array_map(function ($row) { return $row['USER_ID']; }, $moderators),
				]
			);

			return $result;
		}
		catch (Exception $e)
		{
			$this->addError(new Error($e->getMessage()));

			return null;
		}
	}

	public function rejectIncomingRequestAction(int $groupId, array $userIds): ?array
	{
		try
		{
			$result = [];

			foreach ($userIds as $userId)
			{
				$result[$userId] = Helper\Workgroup::rejectIncomingRequest([
					'groupId' => $groupId,
					'userId' => $userId,
				]);
			}

			// re-calculte counters for the group moderators
			$moderators = UserToGroupTable::getGroupModerators($groupId);
			Service::addEvent(
				EventDictionary::EVENT_WORKGROUP_MEMBER_REQUEST_CONFIRM,
				[
					'GROUP_ID' => $groupId,
					'RECEPIENTS' => array_map(function ($row) { return $row['USER_ID']; }, $moderators),
				]
			);

			return $result;
		}
		catch (Exception $e)
		{
			$this->addError(new Error($e->getMessage()));

			return null;
		}
	}

	public function getListIncomingUsersAction(int $groupId, int $pageNum): ?array
	{
		$permissions = Helper\Workgroup::getPermissions(
			['groupId' => $groupId]
		);
		if (!$permissions['UserCanModifyGroup'] && !$permissions['UserCanInitiate'])
		{
			$this->addError(new Error('Access denied'));

			return null;
		}

		$limit = 10;
		$offset = ($pageNum - 1) * $limit;

		return $this->getListOfAwaitingMembers($groupId, $limit, $offset);
	}

	public function getChatIdAction(int $groupId): ?string
	{
		$chatId = '';

		if (!Loader::includeModule('im'))
		{
			return $chatId;
		}

		$chatData = \Bitrix\Socialnetwork\Integration\Im\Chat\Workgroup::getChatData(
			[
				'group_id' => $groupId,
				'skipAvailabilityCheck' => true,
			]
		);
		if (!empty($chatData[$groupId]) && intval($chatData[$groupId]) > 0)
		{
			$chatId = $chatData[$groupId];
		}
		else
		{
			$chatId = \Bitrix\Socialnetwork\Integration\Im\Chat\Workgroup::createChat(
				[
					'group_id' => $groupId,
				]
			);
		}

		return $chatId;
	}

	public function setFeatureAction(int $groupId, array $feature): bool
	{
		if (
			!Helper\Workgroup\Access::canModify([
				'groupId' => $groupId,
				'checkAdminSession' => ($this->getScope() !== Controller::SCOPE_REST),
			])
		)
		{
			$this->addEmptyGroupIdError();

			return false;
		}

		$allowedFeatures = array_keys(\CSocNetAllowed::getAllowedFeatures());

		$featureId = is_string($feature['featureName'] ?? null) ? $feature['featureName'] : '';
		$customName = is_string($feature['customName'] ?? null) ? $feature['customName'] : false;
		$featureActive = is_string($feature['active'] ?? null) && $feature['active'] === 'true';

		$activeFeatures = \CSocNetFeatures::GetActiveFeatures(SONET_ENTITY_GROUP, $groupId);

		if (in_array($featureId, $allowedFeatures, true))
		{
			\CSocNetFeatures::setFeature(
				SONET_ENTITY_GROUP,
				$groupId,
				$featureId,
				$featureActive,
				$customName,
			);
		}

		$action = '';

		if ($featureActive === in_array($featureId ,$activeFeatures))
		{
			$action = 'change';
		}

		if ($featureActive && !in_array($featureId ,$activeFeatures))
		{
			$action = 'add';
		}

		if (!$featureActive && in_array($featureId ,$activeFeatures))
		{
			$action = 'delete';
		}

		$this->sendPush(PushEventDictionary::EVENT_SPACE_FEATURE_CHANGE, [
				'GROUP_ID' => $groupId,
				'FEATURE' => $feature,
				'ACTION' => $action,
			]
		);

		return true;
	}

	private function getUserAvatars(array $imageIds): array
	{
		$result = [];
		if (empty($imageIds))
		{
			return $result;
		}

		$result = array_fill_keys($imageIds, '');

		$res = \CFile::getList([], ['@ID' => implode(',', $imageIds)]);
		while ($file = $res->fetch())
		{
			$file['SRC'] = \CFile::getFileSRC($file);
			$fileInfo = \CFile::resizeImageGet(
				$file,
				[
					'width' => 100,
					'height' => 100,
				],
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				true,
			);

			$result[$file['ID']] = $fileInfo['src'];
		}

		return $result;
	}

	private function sendPush(string $command, array $parameters = []): void
	{
		$parameters['USER_ID'] = $this->userId;
		PushService::addEvent(
			[$this->userId],
			[
				'module_id' => 'socialnetwork',
				'command' => $command,
				'params' => $parameters,
			]
		);
	}

	private function addEmptyGroupIdError(): void
	{
		$this->addError(
			new Error(
				Loc::getMessage('SONET_CONTROLLER_WORKGROUP_EMPTY'),
				'SONET_CONTROLLER_WORKGROUP_EMPTY'
			)
		);
	}

	private function addIncorrectValueError(): void
	{
		$this->addError(new Error(
			'SONET_CONTROLLER_WORKGROUP_INCORRECT_VALUE',
			'SONET_CONTROLLER_WORKGROUP_INCORRECT_VALUE'
		));
	}
}
