<?php

namespace Bitrix\Socialnetwork\Internals\Registry;

class FeaturePermRegistry
{
	private static $instance;

	private array $storage = [];

	/**
	 * @return static
	 */
	public static function getInstance(): self
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * FeaturePermRegistry constructor.
	 */
	private function __construct()
	{

	}

	/**
	 * @param int $entityId
	 * @param string $feature
	 * @param string $operation
	 * @param int $userId
	 * @param string $entityType
	 * @return bool
	 */
	public function get(int $entityId, string $feature, string $operation, int $userId = 0, string $entityType = SONET_ENTITY_GROUP): bool
	{
		if (
			!$entityId
			|| empty($feature)
			|| empty($operation)
			|| !in_array($entityType, [ SONET_ENTITY_GROUP, SONET_ENTITY_USER ], true)
		)
		{
			return false;
		}

		$storageKey = $this->getStorageKey($userId, $entityType, $feature, $operation);

		if (!isset($this->storage[$storageKey][$entityId]))
		{
			$this->load([$entityId], $feature, $operation, $userId, $entityType);
		}

		if (!isset($this->storage[$storageKey][$entityId]))
		{
			return false;
		}

		return $this->storage[$storageKey][$entityId];
	}

	/**
	 * @param array $entityIdList
	 * @param string $feature
	 * @param string $operation
	 * @param int $userId
	 * @param string $entityType
	 * @return $this
	 */
	public function load(array $entityIdList, string $feature, string $operation, int $userId = 0, string $entityType = SONET_ENTITY_GROUP): self
	{
		if (
			empty($entityIdList)
			|| empty($feature)
			|| empty($operation)
			|| !in_array($entityType, [ SONET_ENTITY_GROUP, SONET_ENTITY_USER ], true)
		)
		{
			return $this;
		}

		$storageKey = $this->getStorageKey($userId, $entityType, $feature, $operation);

		if (!isset($this->storage[$storageKey]))
		{
			$this->storage[$storageKey] = [];
		}

		$entityIdList = array_diff(array_unique($entityIdList), array_keys($this->storage[$storageKey]));
		if (empty($entityIdList))
		{
			return $this;
		}

		$permissionData = \CSocNetFeaturesPerms::canPerformOperation($userId, $entityType, $entityIdList, $feature, $operation);
		if (!is_array($permissionData))
		{
			return $this;
		}

		foreach ($entityIdList as $id)
		{
			$this->storage[$storageKey][$id] = false;
		}

		foreach ($permissionData as $id => $hasAccess)
		{
			$this->storage[$storageKey][$id] = $hasAccess;
		}

		return $this;
	}

	private function getStorageKey(int $userId = 0, string $entityType = SONET_ENTITY_GROUP, $feature = '', string $operation = ''): string
	{
		return implode(' ', [ (string)$userId, $entityType, $feature, $operation ]);
	}
}