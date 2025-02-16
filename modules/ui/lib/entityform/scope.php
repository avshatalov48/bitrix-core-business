<?php

namespace Bitrix\Ui\EntityForm;

use Bitrix\HumanResources\Enum\DepthLevel;
use Bitrix\HumanResources\Service\Container;
use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Application;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DeleteResult;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\AccessRights\DataProvider;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\UI\Form\EntityEditorConfigScope;
use CUserOptions;

/**
 * Class Scope
 * @package Bitrix\Ui\EntityForm
 */
class Scope
{
	protected const CODE_USER = 'U';
	protected const CODE_PROJECT = 'SG';
	protected const CODE_DEPARTMENT   = 'DR';

	protected const TYPE_USER = 'user';
	protected const TYPE_PROJECT = 'project';
	protected const TYPE_DEPARTMENT = 'department';

	protected $user;
	protected static $instance = null;

	/**
	 * @return Scope
	 */
	public static function getInstance(): Scope
	{
		if (self::$instance === null)
		{
			Loader::includeModule('ui');
			self::$instance = ServiceLocator::getInstance()->get('ui.entityform.scope');
		}
		return self::$instance;
	}

	/**
	 * @param string $entityTypeId
	 * @param string|null $moduleId
	 * @return array
	 */
	public function getUserScopes(string $entityTypeId, ?string $moduleId = null, bool $loadMetadata = true): array
	{
		return $this->getScopes($entityTypeId, $moduleId, $loadMetadata);
	}

	public function getAllUserScopes(string $entityTypeId, ?string $moduleId = null, bool $loadMetadata = true): array
	{
		return $this->getScopes($entityTypeId, $moduleId, false, $loadMetadata);
	}

	private function getScopes(
		string $entityTypeId,
		?string $moduleId = null,
		bool $excludeEmptyAccessCode = true,
		bool $loadMetadata = true,
	): array
	{
		static $results = [];
		$key = $entityTypeId . '-' . $moduleId . '-' . ($loadMetadata ? 'Y' : 'N');

		if (!isset($results[$key]))
		{
			$result = [];
			$isAdminForEntity = $moduleId
				&& (
					($scopeAccess = ScopeAccess::getInstance($moduleId))
					&& $scopeAccess->isAdminForEntityTypeId($entityTypeId)
				);

			if (!$isAdminForEntity)
			{
				$filter['@ID'] = $this->getScopesIdByUser();
			}

			$filter['@ENTITY_TYPE_ID'] = ($this->getEntityTypeIdMap()[$entityTypeId] ?? [$entityTypeId]);

			if ($excludeEmptyAccessCode)
			{
				$filter['!=ACCESS_CODE'] = '';
			}

			if ($isAdminForEntity || !empty($filter['@ID']))
			{
				$scopes = EntityFormConfigTable::getList([
					'select' => [
						'ID',
						'NAME',
						'AUTO_APPLY_SCOPE',
						'ACCESS_CODE' => '\Bitrix\Ui\EntityForm\EntityFormConfigAcTable:CONFIG.ACCESS_CODE',
					],
					'filter' => $filter,
				]);

				foreach ($scopes as $scope)
				{
					$result[$scope['ID']]['NAME'] = HtmlFilter::encode($scope['NAME']);
					$result[$scope['ID']]['AUTO_APPLY_SCOPE'] = $scope['AUTO_APPLY_SCOPE'];
					if (
						$loadMetadata
						&& !isset($result[$scope['ID']]['ACCESS_CODES'][$scope['ACCESS_CODE']])
						&& isset($scope['ACCESS_CODE'])
					)
					{
						$accessCode = new AccessCode($scope['ACCESS_CODE']);
						$member = (new DataProvider())->getEntity(
							$accessCode->getEntityType(),
							$accessCode->getEntityId()
						);
						$result[$scope['ID']]['ACCESS_CODES'][$scope['ACCESS_CODE']] = $scope['ACCESS_CODE'];
						$result[$scope['ID']]['MEMBERS'][$scope['ACCESS_CODE']] = $member->getMetaData();
					}
				}
			}

			$results[$key] = $result;
		}

		return $results[$key];
	}

	/**
	 * This method must return entityTypeId values that correspond to a single CRM entity only.
	 */
	protected function getEntityTypeIdMap(): array
	{
		return [
			'lead_details' => ['lead_details', 'returning_lead_details'],
			'returning_lead_details' => ['lead_details', 'returning_lead_details'],
		];
	}

	/**
	 * @param int $scopeId
	 * @return bool
	 */
	public function isHasScope(int $scopeId): bool
	{
		return in_array($scopeId, $this->getScopesIdByUser());
	}

	/**
	 * @return \CUser
	 */
	protected function getUser()
	{
		if ($this->user === null)
		{
			global $USER;
			$this->user = $USER;
		}
		return $this->user;
	}

	private function getScopesIdByUser(): array
	{
		$accessCodes = $this->getUser()->GetAccessCodes();
		$this->prepareAccessCodes($accessCodes);

		$params = [
			'select' => [
				'CONFIG_ID',
			],
			'filter' => [
				'@ACCESS_CODE' => $accessCodes,
			],
		];

		$scopes = EntityFormConfigAcTable::getList($params)->fetchAll();

		$result = [];
		if (count($scopes))
		{
			foreach ($scopes as $scope)
			{
				$result[] = $scope['CONFIG_ID'];
			}
		}

		return array_unique($result);
	}

	protected function prepareAccessCodes(array &$accessCodes): void
	{
		$accessCodes = array_filter($accessCodes, static fn($code) => mb_strpos($code, 'CHAT') !== 0);

		foreach ($accessCodes as &$accessCode)
		{
			$accessCode = preg_replace('|^(SG\d*?)(_[K,A,M])$|', '$1', $accessCode);
		}
		unset($accessCode);
	}

	/**
	 * @param int $scopeId
	 * @return array|null
	 */
	public function getScopeById(int $scopeId): ?array
	{
		if ($row = EntityFormConfigTable::getRowById($scopeId))
		{
			return (is_array($row['CONFIG']) ? $row['CONFIG'] : null);
		}
		return null;
	}

	/**
	 * @param int $scopeId
	 * @return array|null
	 */
	public function getById(int $scopeId): ?array
	{
		return EntityFormConfigTable::getRowById($scopeId);
	}

	/**
	 * @param iterable $ids
	 * @throws \Exception
	 */
	public function removeByIds(iterable $ids): void
	{
		foreach ($ids as $id)
		{
			$this->removeById($id);
		}
	}

	/**
	 * @param int $id
	 * @return DeleteResult
	 */
	private function removeById(int $id): DeleteResult
	{
		$this->removeScopeMembers($id);
		return EntityFormConfigTable::delete($id);
	}

	/**
	 * Set user option with config scope type and scopeId if selected custom scope
	 * @param string $categoryName
	 * @param string $guid
	 * @param string $scope
	 * @param int $userScopeId
	 */
	public function setScope(string $categoryName, string $guid, string $scope, int $userScopeId = 0): void
	{
		$this->setScopeToUser($categoryName, $guid, $scope, $userScopeId);
	}

	public function setScopeConfig(
		string $category,
		string $entityTypeId,
		string $name,
		array $accessCodes,
		array $config,
		array $params = []
	)
	{
		if (empty($name))
		{
			$errors['name'] = new Error(Loc::getMessage('FIELD_REQUIRED'));
		}
		if (empty($accessCodes))
		{
			$errors['accessCodes'] = new Error(Loc::getMessage('FIELD_REQUIRED'));
		}
		if (empty($params['categoryName']))
		{
			$errors['categoryName'] = new Error(Loc::getMessage('FIELD_REQUIRED'));
		}
		if (!empty($errors))
		{
			return $errors;
		}

		$this->formatAccessCodes($accessCodes);

		$result = EntityFormConfigTable::add([
			'CATEGORY' => $category,
			'ENTITY_TYPE_ID' => $entityTypeId,
			'NAME' => $name,
			'CONFIG' => $config,
			'COMMON' => ($params['common'] ?? 'Y'),
			'AUTO_APPLY_SCOPE' => ($params['forceSetToUsers'] ?? 'N'),
			'OPTION_CATEGORY' => $params['categoryName']
		]);

		if ($result->isSuccess())
		{
			$configId = $result->getId();
			foreach ($accessCodes as $ac)
			{
				EntityFormConfigAcTable::add([
					'ACCESS_CODE' => $ac['id'],
					'CONFIG_ID' => $configId,
				]);
			}

			$forceSetToUsers = ($params['forceSetToUsers'] ?? false);
			if (mb_strtoupper($forceSetToUsers) === 'FALSE')
			{
				$forceSetToUsers = false;
			}

			Application::getInstance()->addBackgroundJob(
				static fn() => Scope::getInstance()->forceSetScopeToUsers($accessCodes, [
					'forceSetToUsers' => $forceSetToUsers,
					'categoryName' => ($params['categoryName'] ?? ''),
					'entityTypeId' => $entityTypeId,
					'configId' => $configId,
				])
			);

			return $configId;
		}

		return $result->getErrors();
	}

	/**
	 * @param array $accessCodes
	 */
	protected function formatAccessCodes(array &$accessCodes): void
	{
		foreach ($accessCodes as $key => $item)
		{
			if ($item['entityId'] === self::TYPE_USER)
			{
			$accessCodes[$key]['id'] = self::CODE_USER . (int)$accessCodes[$key]['id'];
			}
			elseif ($item['entityId'] === self::TYPE_DEPARTMENT)
			{
				$accessCodes[$key]['id'] = self::CODE_DEPARTMENT . (int)$accessCodes[$key]['id'];
			}
			elseif ($item['entityId'] === self::TYPE_PROJECT)
			{
				$accessCodes[$key]['id'] = self::CODE_PROJECT . (int)$accessCodes[$key]['id'];
			}
			else{
				unset($accessCodes[$key]);
			}
		}
	}

	/**
	 * @param array $accessCodes
	 * @param array $params
	 */
	protected function forceSetScopeToUsers(array $accessCodes = [], array $params = []): void
	{
		if ($params['forceSetToUsers'] && $params['categoryName'])
		{
			$codes = [];
			foreach ($accessCodes as $ac)
			{
				$codes[] = $ac['id'];
			}
			$this->setScopeByAccessCodes(
				$params['categoryName'],
				$params['entityTypeId'],
				EntityEditorConfigScope::CUSTOM,
				(int)$params['configId'],
				$codes
			);
		}
	}

	/**
	 * @param string $categoryName
	 * @param string $guid
	 * @param string $scope
	 * @param int $userScopeId
	 * @param int|null $userId
	 */
	protected function setScopeToUser(
		string $categoryName,
		string $guid,
		string $scope,
		int $userScopeId,
		?int $userId = null
	): void
	{
		$scope = (isset($scope) ? strtoupper($scope) : EntityEditorConfigScope::UNDEFINED);

		if (EntityEditorConfigScope::isDefined($scope))
		{
			if ($scope === EntityEditorConfigScope::CUSTOM && $userScopeId)
			{
				$value = [
					'scope' => $scope,
					'userScopeId' => $userScopeId,
				];
			}
			else
			{
				$value = $scope;
			}

			$userId = ($userId ?? false);
			CUserOptions::SetOption($categoryName, "{$guid}_scope", $value, false, $userId);
		}
	}

	public function updateScopeConfig(int $id, array $config)
	{
		return EntityFormConfigTable::update($id, [
			'CONFIG' => $config,
		]);
	}

	public function updateScopeName(int $id, string $name): UpdateResult
	{
		return EntityFormConfigTable::update($id, [
			'NAME' => $name,
		]);
	}

	/**
	 * @param int $configId
	 * @param array $accessCodes
	 * @return array
	 */
	public function updateScopeAccessCodes(int $configId, array $accessCodes = []): array
	{
		$this->removeScopeMembers($configId);

		foreach ($accessCodes as $ac => $type)
		{
			EntityFormConfigAcTable::add([
				'ACCESS_CODE' => $ac,
				'CONFIG_ID' => $configId,
			]);
		}

		return $this->getScopeMembers($configId);
	}

	/**
	 * @param int $configId
	 * @return array
	 */
	public function getScopeMembers(int $configId): array
	{
		$accessCodes = EntityFormConfigAcTable::getList([
			'select' => ['ACCESS_CODE'],
			'filter' => ['=CONFIG_ID' => $configId],
		])->fetchAll();
		$result = [];
		if (count($accessCodes))
		{
			foreach ($accessCodes as $accessCodeEntity)
			{
				$accessCode = new AccessCode($accessCodeEntity['ACCESS_CODE']);
				$member = (new DataProvider())->getEntity($accessCode->getEntityType(), $accessCode->getEntityId());
				$result[$accessCodeEntity['ACCESS_CODE']] = $member->getMetaData();
			}
		}
		return $result;
	}

	/**
	 * @param int $configId
	 */
	private function removeScopeMembers(int $configId): void
	{
		$entity = EntityFormConfigAcTable::getEntity();
		$connection = $entity->getConnection();

		$filter = ['CONFIG_ID' => $configId];

		$connection->query(sprintf(
			'DELETE FROM %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDBTableName()),
			Query::buildFilterSql($entity, $filter)
		));
	}

	public function updateScopeAutoApplyScope(int $id, bool $autoApplyScope): UpdateResult
	{
		return EntityFormConfigTable::update($id, [
			'AUTO_APPLY_SCOPE' => $autoApplyScope ? 'Y' : 'N',
		]);
	}

	private function setScopeToDepartment(
		string $categoryName,
		string $guid,
		string $scope,
		int $userScopeId,
		int $departmentId
	): void
	{
		$userIds = $this->getUserIdsByDepartment($departmentId);
		foreach ($userIds as $userId)
		{
			$this->setScopeToUser($categoryName, $guid, $scope, $userScopeId, $userId);
		}
	}

	private function setScopeToSocialGroup(
		string $categoryName,
		string $guid,
		string $scope,
		int $userScopeId,
		int $socialGroupId
	): void
	{
		$userIds = $this->getUserIdsBySocialGroup($socialGroupId);
		foreach ($userIds as $userId)
		{
			$this->setScopeToUser($categoryName, $guid, $scope, $userScopeId, $userId);
		}
	}

	public static function handleMemberAddedToDepartment(Event $event): void
	{
		Application::getInstance()->addBackgroundJob(static function() use ($event)
		{
			$member = $event->getParameter('member');

			$memberId = $member->entityId;
			$departmentId = $member->nodeId;
			$scopeType = EntityEditorConfigScope::CUSTOM;
			$scopes = Scope::getInstance()->getScopesByDepartment($departmentId, true);

			$appliedEntities = [];
			foreach ($scopes as $scope)
			{
				if (!in_array($scope->getEntityTypeId(), $appliedEntities))
				{
					$appliedEntities[] = $scope->getEntityTypeId();
					Scope::getInstance()->setScopeToUser(
						$scope->getOptionCategory(),
						$scope->getEntityTypeId(),
						$scopeType,
						$scope->getId(),
						$memberId
					);
				}
			}
		});
	}

	public static function handleMemberAddedToSocialGroup(int $id, array $fields): void
	{
		Application::getInstance()->addBackgroundJob(static function() use ($id, $fields)
		{
			if (!\Bitrix\Main\Loader::includeModule('socialnetwork'))
			{
				return;
			}

			if (empty($fields['ROLE']) && $fields['ROLE'] !== UserToGroupTable::ROLE_USER)
			{
				return;
			}

			if (empty($fields['USER_ID']) || empty($fields['GROUP_ID']))
			{
				$userToGroup = UserToGroupTable::getById($id)->fetchObject();

				if (!$userToGroup)
				{
					return;
				}

				$memberId = $userToGroup->getUserId();
				$socialGroupId = $userToGroup->getGroupId();
			}
			else
			{
				$memberId = $fields['USER_ID'];
				$socialGroupId = $fields['GROUP_ID'];
			}

			$scopeType = EntityEditorConfigScope::CUSTOM;
			$scopes = Scope::getInstance()->getScopesBySocialGroupId($socialGroupId, true);

			$appliedEntities = [];
			foreach ($scopes as $scope)
			{
				if (!in_array($scope->getEntityTypeId(), $appliedEntities))
				{
					$appliedEntities[] = $scope->getEntityTypeId();
					Scope::getInstance()->setScopeToUser(
						$scope->getOptionCategory(),
						$scope->getEntityTypeId(),
						$scopeType,
						$scope->getId(),
						$memberId
					);
				}
			}
		});
	}

	private function getScopesByDepartment(int $departmentId, bool $onlyAutoApplyView = false): array
	{
		$accessCodes = [];
		$nodeRepository = Container::getNodeRepository();
		$node = $nodeRepository->getById($departmentId);
		if (!$node)
		{
			return $accessCodes;
		}

		$parentNodes = $nodeRepository->getParentOf($node, DepthLevel::FULL);
		foreach ($parentNodes as $node)
		{
			$accessCode = str_replace('D', 'DR', $node->accessCode);
			$accessCodes = array_merge($accessCodes, $this->getScopesByAccessCode($accessCode, $onlyAutoApplyView));
		}

		return $accessCodes;
	}

	private function getScopesBySocialGroupId(int $socialGroupId, bool $onlyAutoApplyView = false): array
	{
		$accessCode = 'SG' . $socialGroupId;

		return $this->getScopesByAccessCode($accessCode, $onlyAutoApplyView);
	}

	private function getScopesByAccessCode(string $accessCode, bool $onlyAutoApplyView = false)
	{
		$filter = ['=ACCESS_CODE' => $accessCode];
		if ($onlyAutoApplyView)
		{
			$filter['=CONFIG.AUTO_APPLY_SCOPE'] = 'Y';
		}

		$scopes = EntityFormConfigAcTable::query()
			->setSelect(['ACCESS_CODE', 'CONFIG'])
			->setFilter($filter)
			->setOrder(['CONFIG.ID' => 'DESC'])
			->fetchCollection();

		return $scopes->getConfigList();
	}

	public function setScopeForEligibleUsers(int $scopeId): void
	{
		$scope = EntityFormConfigTable::getById($scopeId)->fetchObject();

		if (!$scope)
		{
			return;
		}

		$accessCodes = $this->getScopeAccessCodesByScopeId($scopeId);

		$this->setScopeByAccessCodes(
			$scope->getOptionCategory(),
			$scope->getEntityTypeId(),
			EntityEditorConfigScope::CUSTOM,
			$scopeId,
			$accessCodes
		);
	}

	private function getScopeAccessCodesByScopeId(int $scopeId): array
	{
		$accessCodes = EntityFormConfigAcTable::query()
			->setSelect(['ACCESS_CODE'])
			->setFilter(['=CONFIG_ID' => $scopeId])
			->fetchCollection();
		$result = [];
		foreach ($accessCodes as $code)
		{
			$result[] = $code->getAccessCode();
		}

		return $result;
	}

	private function setScopeByAccessCodes(
		string $categoryName,
		string $entityTypeId,
		string $scope,
		int    $scopeId,
		array  $accessCodes
	): void
	{
		$userIdPattern = '/^U(\d+)$/';
		$departmentIdPattern = '/^DR(\d+)$/';
		$socialGroupIdPattern = '/^SG(\d+)$/';
		foreach ($accessCodes as $accessCode)
		{
			$matches = [];
			if (preg_match($userIdPattern, $accessCode, $matches))
			{
				$this->setScopeToUser(
					$categoryName,
					$entityTypeId,
					$scope,
					$scopeId,
					$matches[1]
				);
			}
			elseif (preg_match($departmentIdPattern, $accessCode, $matches))
			{
				$this->setScopeToDepartment(
					$categoryName,
					$entityTypeId,
					$scope,
					$scopeId,
					$matches[1]
				);
			}
			elseif (preg_match($socialGroupIdPattern, $accessCode, $matches))
			{
				$this->setScopeToSocialGroup(
					$categoryName,
					$entityTypeId,
					$scope,
					$scopeId,
					$matches[1]
				);
			}
		}
	}

	private function getUserIdsBySocialGroup(int $socialGroupId): array
	{
		if (!\Bitrix\Main\Loader::includeModule('socialnetwork'))
		{
			return [];
		}


		$userCollection = UserToGroupTable::query()
			->setSelect(['USER_ID'])
			->setFilter([
				'=GROUP_ID' => $socialGroupId,
				'@ROLE' => [
					UserToGroupTable::ROLE_MODERATOR,
					UserToGroupTable::ROLE_USER,
					UserToGroupTable::ROLE_OWNER,
				]
			])
			->fetchCollection();

		$userIds = [];
		foreach ($userCollection as $user)
		{
			$userIds[] = $user->getUserId();
		}

		return $userIds;
	}

	private function getUserIdsByDepartment(int $departmentId): array
	{
		$userIds = [];
		if (!\Bitrix\Main\Loader::includeModule('humanresources'))
		{
			return $userIds;
		}

		$hrServiceLocator = Container::instance();
		$accessCode = 'DR' . $departmentId;
		$node = $hrServiceLocator::getNodeRepository()->getByAccessCode($accessCode);
		if (!$node)
		{
			return $userIds;
		}

		$allEmp = $hrServiceLocator::getNodeMemberService()->getAllEmployees($node->id, true);
		foreach ($allEmp->getIterator() as $emp)
		{
			$userIds[] = $emp->entityId;
		}

		return $userIds;
	}
}
