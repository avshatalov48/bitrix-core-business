<?php
namespace Bitrix\Catalog\Model;

use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;

Loc::loadMessages(__FILE__);

abstract class Entity
{
	const PREFIX_OLD = 'OLD_';

	public const EVENT_ON_BUILD_CACHED_FIELD_LIST = 'OnBuildCachedFieldList';

	public const FIELDS_MAIN = 0x0001;
	public const FIELDS_UF = 0x0002;
	public const FIELDS_ALL = self::FIELDS_MAIN|self::FIELDS_UF;

	private static $entity = [];

	/** @var ORM\Data\DataManager Tablet object */
	private $tablet = null;
	/** @var array Table scalar fields list */
	private $tabletFields = [];
	/** @var array User fields list */
	private $tabletUserFields = [];
	/** @var null|Main\DB\Result Database result object */
	private $result;
	/** @var array Entity cache */
	private $cache = [];
	/** @var array internal */
	private $cacheModifyed = [];

	private $fields = [];
	private $fieldsCount = 0;
	private $aliases = [];
	private $fieldMask = [];
	private $fetchCutMask;

	public function __construct()
	{
		$this->initEntityTablet();
		$this->initEntityCache();

		$this->result = null;
		$this->fetchCutMask = [];
	}

	/**
	 * Returns entity class.
	 *
	 * @return Entity
	 */
	public static function getEntity(): Entity
	{
		$className = get_called_class();
		if (empty(self::$entity[$className]))
		{
			$entity = new static;
			self::$entity[$className] = $entity;
		}

		return self::$entity[$className];
	}

	/**
	 * Entity getList with change cache. Need for use before add/update/delete entity row.
	 *
	 * @param array $parameters
	 * @return Entity
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getList(array $parameters): Entity
	{
		$entity = static::getEntity();
		$parameters = $entity->prepareTabletQueryParameters($parameters);
		$entity->result = $entity->getTablet()->getList($parameters);

		return $entity;
	}

	/**
	 * Entity getRow with change cache. Wrapper for entity getList.
	 *
	 * @param array $parameters
	 * @return array|null
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getRow(array $parameters): ?array
	{
		$parameters['limit'] = 1;
		$result = static::getList($parameters);
		$row = $result->fetch();

		return (is_array($row) ? $row : null);
	}

	/**
	 * Entity fetch. Work with entity change cache.
	 *
	 * @param Main\Text\Converter|null $converter
	 * @return array|false
	 */
	public function fetch(Main\Text\Converter $converter = null)
	{
		if ($this->result === null)
			return false;
		$row = $this->result->fetch($converter);
		if (!$row)
		{
			$this->result = null;
			$this->fetchCutMask = [];
			return false;
		}
		if (empty($this->fields))
			return $row;
		if (!isset($row['ID']))
			return $row;

		$this->setEntityCacheItem((int)$row['ID'], $row, true);
		if (!empty($this->fetchCutMask))
			$row = array_diff_key($row, $this->fetchCutMask);
		return $row;
	}

	/**
	 * Clear all cache for entity.
	 *
	 * @return void
	 */
	public static function clearCache(): void
	{
		static::getEntity()->clearEntityCache();
	}

	/**
	 * Add entity item. Use instead of DataManager method.
	 *
	 * @param array $data
	 * @return ORM\Data\AddResult
	 */
	public static function add(array $data): ORM\Data\AddResult
	{
		$result = new ORM\Data\AddResult();

		$entity = static::getEntity();

		static::normalize($data);

		if (Event::existEventHandlers($entity, ORM\Data\DataManager::EVENT_ON_BEFORE_ADD))
		{
			$event = new Event(
				$entity,
				ORM\Data\DataManager::EVENT_ON_BEFORE_ADD,
				$data
			);
			$event->send();

			$event->mergeData($data);
			if ($event->getErrors($result))
				return $result;
		}

		static::prepareForAdd($result, null, $data);
		if (!$result->isSuccess())
			return $result;
		unset($result);

		if (Event::existEventHandlers($entity, ORM\Data\DataManager::EVENT_ON_ADD))
		{
			$event = new Event(
				$entity,
				ORM\Data\DataManager::EVENT_ON_ADD,
				$data
			);
			$event->send();
			unset($event);
		}

		$result = $entity->getTablet()->add($data['fields']);
		$success = $result->isSuccess();
		if ($success)
		{
			$data['fields'] = $result->getData();
			if ($entity->fieldsCount > 0)
				$entity->setEntityCacheItem((int)$result->getId(), $result->getData(), false);
		}

		if (Event::existEventHandlers($entity, ORM\Data\DataManager::EVENT_ON_AFTER_ADD))
		{
			$event = new Event(
				$entity,
				ORM\Data\DataManager::EVENT_ON_AFTER_ADD,
				[
					'id' => $result->getId(),
					'fields' => $data['fields'],
					'external_fields' => $data['external_fields'],
					'actions' => $data['actions'],
					'success' => $success
				]
			);
			$event->send();
			unset($event);
		}

		if ($success && !empty($data['actions']))
			static::runAddExternalActions($result->getId(), $data);

		unset($success, $entity);

		return $result;
	}

	/**
	 * Update entity item. Use instead of DataManager method.
	 *
	 * @param int $id
	 * @param array $data
	 * @return ORM\Data\UpdateResult
	 */
	public static function update($id, array $data): ORM\Data\UpdateResult
	{
		$result = new ORM\Data\UpdateResult();

		$entity = static::getEntity();

		static::normalize($data);

		if (Event::existEventHandlers($entity, ORM\Data\DataManager::EVENT_ON_BEFORE_UPDATE))
		{
			$event = new Event(
				$entity,
				ORM\Data\DataManager::EVENT_ON_BEFORE_UPDATE,
				[
					'id' => $id,
					'fields' => $data['fields'],
					'external_fields' => $data['external_fields'],
					'actions' => $data['actions']
				]
			);
			$event->send();

			$event->mergeData($data);
			if ($event->getErrors($result))
				return $result;
		}

		static::prepareForUpdate($result, $id, $data);
		if (!$result->isSuccess())
			return $result;
		unset($result);

		if (Event::existEventHandlers($entity, ORM\Data\DataManager::EVENT_ON_UPDATE))
		{
			$event = new Event(
				$entity,
				ORM\Data\DataManager::EVENT_ON_UPDATE,
				[
					'id' => $id,
					'fields' => $data['fields'],
					'external_fields' => $data['external_fields'],
					'actions' => $data['actions']
				]
			);
			$event->send();
			unset($event);
		}

		$result = $entity->getTablet()->update($id, $data['fields']);
		$success = $result->isSuccess();
		if ($success)
		{
			$data['fields'] = $result->getData();
			if ($entity->fieldsCount > 0)
				$entity->modifyEntityCacheItem($id, $data['fields']);
		}

		if (Event::existEventHandlers($entity, ORM\Data\DataManager::EVENT_ON_AFTER_UPDATE))
		{
			$event = new Event(
				$entity,
				ORM\Data\DataManager::EVENT_ON_AFTER_UPDATE,
				[
					'id' => $id,
					'fields' => $data['fields'],
					'external_fields' => $data['external_fields'],
					'actions' => $data['actions'],
					'success' => $success
				]
			);
			$event->send();
			unset($event);
		}

		if ($success && !empty($data['actions']))
			static::runUpdateExternalActions($id, $data);

		unset($success, $entity);

		return $result;
	}

	/**
	 * Delete entity item. Use instead of DataManager method.
	 *
	 * @param int $id
	 * @return ORM\Data\DeleteResult
	 */
	public static function delete($id): ORM\Data\DeleteResult
	{
		$result = new ORM\Data\DeleteResult();

		$entity = static::getEntity();

		if (Event::existEventHandlers($entity, ORM\Data\DataManager::EVENT_ON_BEFORE_DELETE))
		{
			$event = new Event(
				$entity,
				ORM\Data\DataManager::EVENT_ON_BEFORE_DELETE,
				['id' => $id]
			);
			$event->send();

			if ($event->getErrors($result))
				return $result;
		}

		if (Event::existEventHandlers($entity, ORM\Data\DataManager::EVENT_ON_DELETE))
		{
			$event = new Event(
				$entity,
				ORM\Data\DataManager::EVENT_ON_DELETE,
				['id' => $id]
			);
			$event->send();
			unset($event);
		}

		if ($entity->fieldsCount > 0 && !isset($entity->cache[$id]))
			$entity->loadEntityCacheItem($id);

		$result = $entity->getTablet()->delete($id);
		$success = $result->isSuccess();
		if ($success)
			$entity->expireEntityCacheItem((int)$id);

		if (Event::existEventHandlers($entity, ORM\Data\DataManager::EVENT_ON_AFTER_DELETE))
		{
			$event = new Event(
				$entity,
				ORM\Data\DataManager::EVENT_ON_AFTER_DELETE,
				['id' => $id, 'success' => $success]
			);
			$event->send();
			unset($event);
		}

		if ($success)
			static::runDeleteExternalActions($id);

		unset($success, $entity);

		return $result;
	}

	/**
	 * Fill item cache data. Do not use without good reason.
	 *
	 * @param int $id
	 * @param array $row
	 * @return void
	 */
	public static function setCacheItem($id, array $row): void
	{
		$id = (int)$id;
		if ($id <= 0 || empty($row))
			return;
		static::getEntity()->setEntityCacheItem($id, $row, false);
	}

	/**
	 * Returns item cache.
	 *
	 * @param int $id
	 * @param bool $load
	 * @return array|null
	 */
	public static function getCacheItem($id, bool $load = false): ?array
	{
		$id = (int)$id;
		if ($id <= 0)
			return null;
		return static::getEntity()->getEntityCacheItem($id, $load);
	}

	/**
	 * Clear item cache. Do not use without good reason.
	 *
	 * @param int $id
	 * @return void
	 * @noinspection PhpUnused
	 */
	public static function clearCacheItem($id): void
	{
		$id = (int)$id;
		if ($id <= 0)
			return;
		static::getEntity()->clearEntityCacheItem($id);
	}

	/**
	 * Returns entity tablet name.
	 *
	 * @return string
	 */
	public static function getTabletClassName(): string
	{
		return '';
	}

	/**
	 * Returns list of tablet field names, include user fields.
	 *
	 * @param int $fields
	 * @return array
	 */
	public static function getTabletFieldNames(int $fields = self::FIELDS_MAIN): array
	{
		$result = [];
		$entity = static::getEntity();
		if ($fields & self::FIELDS_MAIN)
		{
			$result = array_keys($entity->tabletFields);
		}
		if ($fields & self::FIELDS_UF)
		{
			$list = array_keys($entity->tabletUserFields);
			if (!empty($list))
			{
				$result = (empty($result)
					? $list
					: array_merge($result, $list)
				);
			}
			unset($list);
		}

		unset($entity);
		return $result;
	}

	/**
	 * Returns fields list in cache.
	 *
	 * @return array
	 */
	public static function getCachedFieldList(): array
	{
		$entity = static::getEntity();
		return $entity->fields;
	}

	/**
	 * Returns entity table object.
	 *
	 * @return ORM\Data\DataManager
	 * @throws Main\ObjectNotFoundException
	 */
	protected function getTablet(): ORM\Data\DataManager
	{
		if (!($this->tablet instanceof ORM\Data\DataManager))
			throw new Main\ObjectNotFoundException(sprintf(
				'Tablet not found in entity `%s`',
				get_class($this)
			));
		return $this->tablet;
	}

	/**
	 * Check and modify fields before add entity item. Need for entity automation.
	 *
	 * @param ORM\Data\AddResult $result
	 * @param int|null $id
	 * @param array &$data
	 * @return void
	 */
	protected static function prepareForAdd(ORM\Data\AddResult $result, $id, array &$data): void
	{
		$data = static::getEntity()->checkTabletWhiteList($data);
		if (empty($data))
		{
			$result->addError(new ORM\EntityError(sprintf(
				'Empty data for add in entity `%s`',
				get_called_class()
			)));
		}
	}

	/**
	 * Check and modify fields before update entity item. Need for entity automation.
	 *
	 * @param ORM\Data\UpdateResult $result
	 * @param int $id
	 * @param array &$data
	 * @return void
	 */
	protected static function prepareForUpdate(ORM\Data\UpdateResult $result, $id, array &$data): void
	{
		$data = static::getEntity()->checkTabletWhiteList($data);
		if (empty($data))
		{
			$result->addError(new ORM\EntityError(sprintf(
				'Empty data for update in entity `%s`',
				get_called_class()
			)));
		}
	}

	/**
	 * Delete entity item without entity events (tablet events only).
	 *
	 * @param int $id
	 * @return ORM\Data\DeleteResult
	 * @throws Main\ObjectNotFoundException
	 */
	protected static function deleteNoDemands($id): ORM\Data\DeleteResult
	{
		$entity = static::getEntity();

		if ($entity->fieldsCount > 0 && !isset($entity->cache[$id]))
			$entity->loadEntityCacheItem($id);

		$result = $entity->getTablet()->delete($id);
		if ($result->isSuccess())
		{
			if ($entity->fieldsCount > 0)
				$entity->expireEntityCacheItem((int)$id);
			static::runDeleteExternalActions($id);
		}

		unset($entity);

		return $result;
	}

	/**
	 * Normalize data before prepare. Convert fields list into complex structure.
	 *
	 * @param array &$data
	 * @return void
	 */
	protected static function normalize(array &$data): void
	{
		$result = [
			'fields' => [],
			'external_fields' => [],
			'actions' => []
		];

		if (isset($data['fields']) && is_array($data['fields']))
		{
			$result['fields'] = $data['fields'];
			if (isset($data['external_fields']) && is_array($data['external_fields']))
				$result['external_fields'] = $data['external_fields'];
			if (isset($data['actions']) && is_array($data['actions']))
				$result['actions'] = $data['actions'];
		}
		else
		{
			$result['fields'] = $data;
		}

		$data = $result;
		unset($result);
	}

	/**
	 * Run core automation after add entity item.
	 *
	 * @param int $id
	 * @param array $data
	 * @return void
	 */
	protected static function runAddExternalActions($id, array $data): void {}

	/**
	 * Run core automation after update entity item.
	 *
	 * @param int $id
	 * @param array $data
	 * @return void
	 */
	protected static function runUpdateExternalActions($id, array $data): void {}

	/**
	 * Run core automation after delete entity item.
	 *
	 * @param int $id
	 * @return void
	 */
	protected static function runDeleteExternalActions($id): void {}

	/**
	 * Returns entity default fields list for caching.
	 *
	 * @return array
	 */
	protected static function getDefaultCachedFieldList(): array
	{
		return [];
	}

	/**
	 * Init entity table object.
	 * @internal
	 *
	 * @return void
	 */
	private function initEntityTablet(): void
	{
		$tabletClassName = static::getTabletClassName();
		$this->tablet = new $tabletClassName;
		$this->tabletFields = [];
		$this->tabletUserFields = [];

		$entity = $this->tablet->getEntity();
		$checkUseFields = $entity->getUfId() !== null;
		$list = $entity->getFields();
		foreach ($list as $field)
		{
			if ($field instanceof ORM\Fields\ScalarField)
			{
				$this->tabletFields[$field->getName()] = true;
			}
			elseif ($checkUseFields && $field instanceof ORM\Fields\UserTypeField)
			{
				$this->tabletUserFields[$field->getName()] = true;
			}
		}
		unset($field, $list);
		unset($checkUseFields);
		unset($entity, $tabletClassName);
	}

	/**
	 * Build entity cache environment.
	 * @internal
	 *
	 * @return void
	 */
	private function initEntityCache(): void
	{
		$this->clearEntityCache();

		$this->aliases = [];
		$this->fieldMask = [];
		$fieldList = static::getDefaultCachedFieldList();
		if (Event::existEventHandlers($this, self::EVENT_ON_BUILD_CACHED_FIELD_LIST))
		{
			$event = new Event(
				$this,
				self::EVENT_ON_BUILD_CACHED_FIELD_LIST
			);
			$event->send();

			foreach($event->getResults() as $eventResult)
			{
				if ($eventResult->getType() == Main\EventResult::SUCCESS)
				{
					$addFields = $eventResult->getParameters();
					if (!empty($addFields) && is_array($addFields))
					{
						foreach ($addFields as $alias => $field)
						{
							if (!isset($this->tabletFields[$field]))
							{
								continue;
							}
							$index = array_search($field, $fieldList);
							if (is_int($alias))
							{
								if ($index === false || !is_int($index))
								{
									$fieldList[] = $field;
								}
							}
							else
							{
								if ($index !== $alias)
								{
									$fieldList[$alias] = $field;
								}
							}
						}
					}
				}
			}
			unset($eventResult, $event);
		}

		$this->fields = $fieldList;
		unset($fieldList);
		if (!empty($this->fields))
		{
			foreach ($this->fields as $alias => $field)
			{
				if (is_int($alias))
				{
					$this->fieldMask[$field] = true;
				}
				else
				{
					$this->fieldMask[$alias] = true;
					$this->aliases[$alias] = $field;
				}
			}
			unset($alias, $field);
		}
		$this->fieldsCount = count($this->fields);
	}

	/**
	 * Remove all entity cache.
	 * @internal
	 *
	 * @return void
	 */
	private function clearEntityCache(): void
	{
		$this->cache = [];
		$this->cacheModifyed = [];
	}

	/**
	 * Add cached fields to entity getList parameters.
	 * @internal
	 *
	 * @param array $parameters
	 * @return array
	 */
	private function prepareTabletQueryParameters(array $parameters): array
	{
		$this->fetchCutMask = [];

		if (empty($this->fields))
			return $parameters;
		if (!isset($parameters['select']))
			return $parameters;
		if (in_array('*', $parameters['select']))
			return $parameters;
		if (isset($parameters['group']))
			return $parameters;

		$select = $parameters['select'];
		foreach ($this->fields as $field)
		{
			$existField = false;
			$index = array_search($field, $select);
			if ($index !== false && is_int($index))
				$existField = true;
			if ($existField)
				continue;

			$parameters['select'][] = $field;
			$this->fetchCutMask[$field] = true;
		}
		unset($index, $existField, $field);

		return $parameters;
	}

	/**
	 * If exists fields alias, result row is modified.
	 * @internal
	 *
	 * @param array &$row
	 * @return void
	 */
	private function replaceFieldToAlias(array &$row): void
	{
		if (empty($this->aliases))
			return;

		foreach ($this->aliases as $alias => $field)
		{
			$row[$alias] = $row[$field];
			unset($row[$field]);
		}
		unset($alias, $field);
	}

	/**
	 * Filter row by tablet fields (include uf fields, if exists).
	 * @internal
	 *
	 * @param array $fields
	 * @return array
	 */
	private function checkTabletWhiteList(array $fields): array
	{
		$baseFields = array_intersect_key($fields, $this->tabletFields);
		if (!empty($this->tabletUserFields))
		{
			$userFields = array_intersect_key($fields, $this->tabletUserFields);
			if (!empty($userFields))
			{
				$baseFields = $baseFields + $userFields;
			}
			unset($userFields);
		}
		return $baseFields;
	}

	static public function getCallbackRestEvent(): array
	{
		return [Main\Rest\Event::class, 'processItemEvent'];
	}

	/* entity cache item tools */

	/**
	 * Load cached fields for entity item.
	 * @internal
	 *
	 * @param int $id
	 * @return void
	 */
	private function loadEntityCacheItem($id): void
	{
		if (isset($this->cache[$id]))
			return;
		if (empty($this->fields))
			return;

		$iterator = $this->getTablet()->getList([
			'select' => array_values($this->fields),
			'filter' => ['=ID' => $id]
		]);
		$row = $iterator->fetch();
		unset($iterator);
		if (!empty($row))
			$this->setEntityCacheItem($id, $row, true);
		unset($row);
	}

	/**
	 * Internal method for get entity item cache.
	 * @internal
	 *
	 * @param int $id
	 * @param bool $load
	 * @return array
	 */
	private function getEntityCacheItem($id, bool $load = false): array
	{
		$result = [];
		if (!isset($this->cache[$id]) && $load && !empty($this->fields))
			$this->loadEntityCacheItem($id);
		if (isset($this->cache[$id]))
			$result = $this->cache[$id];

		return $result;
	}

	/**
	 * Internal method for setting entity item cache.
	 * @internal
	 *
	 * @param int $id
	 * @param array $row
	 * @param bool $replaceAliases
	 * @return void
	 */
	private function setEntityCacheItem($id, array $row, bool $replaceAliases = false): void
	{
		if (empty($this->fieldMask))
			return;
		if (isset($this->cache[$id]))
			return;

		if ($replaceAliases)
			$this->replaceFieldToAlias($row);
		$data = array_intersect_key($row, $this->fieldMask);
		if (!empty($data) && count($data) == $this->fieldsCount)
			$this->cache[$id] = $data;
		unset($data);
	}

	/**
	 * Internal method for modify entity item cache.
	 * @internal
	 *
	 * @param int $id
	 * @param array $row
	 * @return void
	 */
	private function modifyEntityCacheItem($id, array $row): void
	{
		if (empty($this->fieldMask))
			return;

		$data = array_intersect_key($row, $this->fieldMask);
		if (!empty($data))
		{
			if (!isset($this->cache[$id]))
				$this->loadEntityCacheItem($id);
			if (isset($this->cache[$id]))
			{
				$this->expireEntityCacheItem($id, true);
				$this->cache[$id] = array_merge($this->cache[$id], $data);
			}
		}
		unset($data);
	}

	/**
	 * Internal method for marked entity item cache as modified.
	 * @internal
	 *
	 * @param int $id
	 * @param bool $copy
	 * @return void
	 */
	private function expireEntityCacheItem($id, bool $copy = false): void
	{
		if (empty($this->fields))
			return;

		if (!isset($this->cache[$id]))
			return;
		if (isset($this->cacheModifyed[$id]))
			return;

		$oldData = [];
		foreach (array_keys($this->fieldMask) as $field)
			$oldData[self::PREFIX_OLD.$field] = $this->cache[$id][$field];
		unset($field);
		if ($copy)
			$this->cache[$id] = array_merge($oldData, $this->cache[$id]);
		else
			$this->cache[$id] = $oldData;
		unset($oldData);

		$this->cacheModifyed[$id] = true;
	}

	/**
	 * Clear entity cache for item.
	 * @internal
	 *
	 * @param int $id
	 * @return void
	 */
	private function clearEntityCacheItem($id): void
	{
		if (isset($this->cache[$id]))
			unset($this->cache[$id]);
		if (isset($this->cacheModifyed[$id]))
			unset($this->cacheModifyed[$id]);
	}

	public static function clearSettings(): void {}

	/* entity cache item tools end */

	protected static function prepareFloatValue($value): ?float
	{
		if ($value === null)
		{
			return null;
		}

		$result = null;
		if (is_string($value))
		{
			if ($value !== '' && is_numeric($value))
			{
				$value = (float)$value;
				if (is_finite($value))
				{
					$result = $value;
				}
			}
		}
		else
		{
			if (is_int($value))
			{
				$value = (float)$value;
			}
			if (
				is_float($value) && is_finite($value)
			)
			{
				$result = $value;
			}
		}

		return $result;
	}

	protected static function prepareIntValue($value): ?int
	{
		if ($value === null)
		{
			return null;
		}

		$result = null;
		if (is_string($value))
		{
			if ($value !== '' && is_numeric($value))
			{
				$result = (int)$value;
			}
		}
		elseif (is_int($value))
		{
			$result = $value;
		}

		return $result;
	}

	protected static function prepareStringValue($value): ?string
	{
		if (is_string($value))
		{
			return trim($value) ?: null;
		}

		return null;
	}
}
