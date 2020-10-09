<?php

namespace Bitrix\Ui\EntityForm;

use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DeleteResult;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\AccessRights\DataProvider;
use Bitrix\UI\Form\EntityEditorConfigScope;
use CUserOptions;

/**
 * Class Scope
 * @package Bitrix\Ui\EntityForm
 */
class Scope
{
	protected $user;
	protected static $instance = null;

	/**
	 * @return Scope
	 */
	public static function getInstance(): Scope
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function getUserScopes(string $entityTypeId, ?string $moduleId = null): array
	{
		$result = [];
		$scopeIds = $this->getScopesIdByUser($moduleId);
		if (count($scopeIds))
		{
			$scopes = EntityFormConfigTable::getList([
				'select' => [
					'ID',
					'NAME',
					'ACCESS_CODE' => '\Bitrix\Ui\EntityForm\EntityFormConfigAcTable:CONFIG.ACCESS_CODE'
				],
				'filter' => [
					'@ID' => $scopeIds,
					'=ENTITY_TYPE_ID' => $entityTypeId
				]
			]);
			foreach ($scopes as $scope)
			{
				$result[$scope['ID']]['NAME'] = HtmlFilter::encode($scope['NAME']);
				if (!isset($result[$scope['ID']]['ACCESS_CODES'][$scope['ACCESS_CODE']]))
				{
					$accessCode = new AccessCode($scope['ACCESS_CODE']);
					$member = (new DataProvider())->getEntity($accessCode->getEntityType(), $accessCode->getEntityId());
					$result[$scope['ID']]['ACCESS_CODES'][$scope['ACCESS_CODE']] = $scope['ACCESS_CODE'];
					$result[$scope['ID']]['MEMBERS'][$scope['ACCESS_CODE']] = $member->getMetaData();
				}
			}
		}
		return $result;
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
	 * @return \CAllUser|\CUser
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

	private function getScopesIdByUser(?string $moduleId = null): array
	{
		$accessCodes = $this->getUser()->GetAccessCodes();

		$params = [
			'select' => [
				'CONFIG_ID'
			]
		];

		if(
			!$moduleId
			||
			(
				($scopeAccess = ScopeAccess::getInstance($moduleId))
				&& !$scopeAccess->isAdmin()
			)
		)
		{
			$params['filter'] = ['@ACCESS_CODE' => $accessCodes];
		}

		$scopes = EntityFormConfigAcTable::getList($params)->fetchAll();

		$result = [];
		if (count($scopes))
		{
			foreach ($scopes as $scope)
			{
				$result[] = $scope['CONFIG_ID'];
			}
		}

		return $result;
	}

	/**
	 * @param int $scopeId
	 * @return array|null
	 */
	public function getScopeById(int $scopeId): ?array
	{
		if ($row = EntityFormConfigTable::getRowById($scopeId))
		{
			return $row['CONFIG'];
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
		$scope = (isset($scope) ? strtoupper($scope) : EntityEditorConfigScope::UNDEFINED);

		if (EntityEditorConfigScope::isDefined($scope))
		{
			if ($scope === EntityEditorConfigScope::CUSTOM && $userScopeId)
			{
				$value = [
					'scope' => $scope,
					'userScopeId' => $userScopeId
				];
			}
			else
			{
				$value = $scope;
			}
			CUserOptions::SetOption($categoryName, "{$guid}_scope", $value);
		}
	}

	public function setScopeConfig(
		string $category,
		string $entityTypeId,
		string $name,
		array $accessCodes,
		array $config,
		string $common
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
		if (!empty($errors))
		{
			return $errors;
		}

		$result = EntityFormConfigTable::add([
			'CATEGORY' => $category,
			'ENTITY_TYPE_ID' => $entityTypeId,
			'NAME' => $name,
			'CONFIG' => $config,
			'COMMON' => $common
		]);

		if ($result->isSuccess())
		{
			$configId = $result->getId();
			foreach ($accessCodes as $ac)
			{
				EntityFormConfigAcTable::add([
					'ACCESS_CODE' => $ac['ID'],
					'CONFIG_ID' => $configId,
				]);
			}
			return $configId;
		}

		return $result->getErrors();
	}

	public function updateScopeConfig(int $id, array $config)
	{
		return EntityFormConfigTable::update($id, [
			'CONFIG' => $config
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
			'filter' => ['=CONFIG_ID' => $configId]
		]);
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

}