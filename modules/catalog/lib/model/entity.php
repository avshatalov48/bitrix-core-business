<?php
namespace Bitrix\Catalog\Model;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

abstract class Entity
{
	const PREFIX_OLD = 'OLD_';

	private static $entity = null;

	/** @var null|Main\Entity\DataManager Tablet object */
	private $tablet = null;
	/** @var array Table scalar fields list */
	private $tabletFields = array();
	/** @var null|Main\DB\Result Database result object */
	private $result = null;
	/** @var array Entity cache */
	private $cache = array();
	/** @var array internal */
	private $cacheModifyed = array();

	private $fields = array();
	private $fieldsCount = 0;
	private $aliases = array();
	private $fieldMask = array();
	private $fetchCutMask = array();

	public function __construct()
	{
		$this->createEntityTablet();
		$this->initEntityCache();

		$this->result = null;
		$this->fetchCutMask = array();
	}

	/**
	 * @return Entity
	 */
	public static function getEntity()
	{
		$className = get_called_class();
		if (empty(self::$entity[$className]))
		{
			/** @var Entity $entity */
			$entity = new static;
			self::$entity[$className] = $entity;
		}

		return self::$entity[$className];
	}

	public static function getList(array $parameters)
	{
		$entity = static::getEntity();
		$parameters = $entity->prepareTabletQueryParameters($parameters);
		$entity->result = $entity->getTablet()->getList($parameters);
		return $entity;
	}

	public function fetch(Main\Text\Converter $converter = null)
	{
		if ($this->result === null)
			return false;
		$row = $this->result->fetch($converter);
		if (!$row)
		{
			$this->result = null;
			$this->fetchCutMask = array();
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

	public static function clearCache()
	{
		static::getEntity()->clearEntityCache();
	}

	public static function add(array $data)
	{
		$result = new Main\Entity\AddResult();

		$entity = static::getEntity();

		static::normalize($data);

		if (Event::existEventHandlers($entity, Main\Entity\DataManager::EVENT_ON_BEFORE_ADD))
		{
			$event = new Event(
				$entity,
				Main\Entity\DataManager::EVENT_ON_BEFORE_ADD,
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

		if (Event::existEventHandlers($entity, Main\Entity\DataManager::EVENT_ON_ADD))
		{
			$event = new Event(
				$entity,
				Main\Entity\DataManager::EVENT_ON_ADD,
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

		if (Event::existEventHandlers($entity, Main\Entity\DataManager::EVENT_ON_AFTER_ADD))
		{
			$event = new Event(
				$entity,
				Main\Entity\DataManager::EVENT_ON_AFTER_ADD,
				array(
					'id' => $result->getId(),
					'fields' => $data['fields'],
					'external_fields' => $data['external_fields'],
					'actions' => $data['actions'],
					'success' => $success
				)
			);
			$event->send();
			unset($event);
		}

		if ($success && !empty($data['actions']))
			static::runAddExternalActions($result->getId(), $data);

		unset($success, $entity);

		return $result;
	}

	public static function update($id, array $data)
	{
		$result = new Main\Entity\UpdateResult();

		$entity = static::getEntity();

		static::normalize($data);

		if (Event::existEventHandlers($entity, Main\Entity\DataManager::EVENT_ON_BEFORE_UPDATE))
		{
			$event = new Event(
				$entity,
				Main\Entity\DataManager::EVENT_ON_BEFORE_UPDATE,
				array(
					'id' => $id,
					'fields' => $data['fields'],
					'external_fields' => $data['external_fields'],
					'actions' => $data['actions']
				)
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

		if (Event::existEventHandlers($entity, Main\Entity\DataManager::EVENT_ON_UPDATE))
		{
			$event = new Event(
				$entity,
				Main\Entity\DataManager::EVENT_ON_UPDATE,
				array(
					'id' => $id,
					'fields' => $data['fields'],
					'external_fields' => $data['external_fields'],
					'actions' => $data['actions']
				)
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

		if (Event::existEventHandlers($entity, Main\Entity\DataManager::EVENT_ON_AFTER_UPDATE))
		{
			$event = new Event(
				$entity,
				Main\Entity\DataManager::EVENT_ON_AFTER_UPDATE,
				array(
					'id' => $id,
					'fields' => $data['fields'],
					'external_fields' => $data['external_fields'],
					'actions' => $data['actions'],
					'success' => $success
				)
			);
			$event->send();
			unset($event);
		}

		if ($success && !empty($data['actions']))
			static::runUpdateExternalActions($id, $data);

		unset($success, $entity);

		return $result;
	}

	public static function delete($id)
	{
		$result = new Main\Entity\DeleteResult();

		$entity = static::getEntity();

		if (Event::existEventHandlers($entity, Main\Entity\DataManager::EVENT_ON_BEFORE_DELETE))
		{
			$event = new Event(
				$entity,
				Main\Entity\DataManager::EVENT_ON_BEFORE_DELETE,
				array('id' => $id)
			);
			$event->send();

			if ($event->getErrors($result))
				return $result;
		}

		if (Event::existEventHandlers($entity, Main\Entity\DataManager::EVENT_ON_DELETE))
		{
			$event = new Event(
				$entity,
				Main\Entity\DataManager::EVENT_ON_DELETE,
				array('id' => $id)
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

		if (Event::existEventHandlers($entity, Main\Entity\DataManager::EVENT_ON_AFTER_DELETE))
		{
			$event = new Event(
				$entity,
				Main\Entity\DataManager::EVENT_ON_AFTER_DELETE,
				array('id' => $id, 'success' => $success)
			);
			$event->send();
			unset($event);
		}

		if ($success)
			static::runDeleteExternalActions($id);

		unset($success, $entity);

		return $result;
	}

	public static function setCacheItem($id, array $row)
	{
		$id = (int)$id;
		if ($id <= 0 || empty($row))
			return;
		static::getEntity()->setEntityCacheItem($id, $row, false);
	}

	public static function getCacheItem($id, $load = false)
	{
		$id = (int)$id;
		if ($id <= 0)
			return null;
		return static::getEntity()->getEntityCacheItem($id, $load);
	}

	public static function clearCacheItem($id)
	{
		$id = (int)$id;
		if ($id <= 0)
			return;
		static::getEntity()->clearEntityCacheItem($id);
	}

	/**
	 * @return string
	 */
	public static function getTabletClassName()
	{
		return '';
	}

	/**
	 * @return array
	 */
	public static function getCachedFieldList()
	{
		return array();
	}

	protected function getTablet()
	{
		if (!($this->tablet instanceof Main\Entity\DataManager))
			throw new Main\ObjectNotFoundException(sprintf(
				'Tablet not found in entity `%s`',
				get_class($this)
			));
		return $this->tablet;
	}

	protected static function prepareForAdd(Main\Entity\AddResult $result, $id, array &$data)
	{
		$data = static::getEntity()->checkTabletWhiteList($data);
		if (empty($data))
		{
			$result->addError(new Main\Entity\EntityError(sprintf(
				'Empty data fo add in entity `%s`',
				get_called_class()
			)));
		}
	}

	protected static function prepareForUpdate(Main\Entity\UpdateResult $result, $id, array &$data)
	{
		$data = static::getEntity()->checkTabletWhiteList($data);
		if (empty($data))
		{
			$result->addError(new Main\Entity\EntityError(sprintf(
				'Empty data fo update in entity `%s`',
				get_called_class()
			)));
		}
	}

	protected static function deleteNoDemands($id)
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
	 * @param array $data
	 * @return void
	 */
	protected static function normalize(array &$data)
	{
		$result = array(
			'fields' => array(),
			'external_fields' => array(),
			'actions' => array()
		);

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

	protected static function runAddExternalActions($id, array $data){}

	protected static function runUpdateExternalActions($id, array $data){}

	protected static function runDeleteExternalActions($id){}

	/**
	 * @return void
	 */
	private function createEntityTablet()
	{
		$tabletClassName = static::getTabletClassName();
		$this->tablet = new $tabletClassName;
		$this->tabletFields = $this->tablet->getEntity()->getScalarFields();
	}

	private function initEntityCache()
	{
		$this->clearEntityCache();

		$this->aliases = array();
		$this->fieldMask = array();
		$this->fields = static::getCachedFieldList();
		if (!empty($this->fields))
		{
			foreach ($this->fields as $alias => $field)
			{
				if (is_numeric($alias))
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

	private function clearEntityCache()
	{
		$this->cache = array();
		$this->cacheModifyed = array();
	}

	private function prepareTabletQueryParameters(array $parameters)
	{
		$this->fetchCutMask = array();

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
			if ($index !== false && is_numeric($index))
				$existField = true;
			if ($existField)
				continue;

			$parameters['select'][] = $field;
			$this->fetchCutMask[$field] = true;
		}
		unset($index, $existField, $field);

		return $parameters;
	}

	private function replaceFieldToAlias(array &$row)
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

	private function checkTabletWhiteList(array $fields)
	{
		return array_intersect_key($fields, $this->tabletFields);
	}

	/* entity cache item tools */

	private function loadEntityCacheItem($id)
	{
		if (isset($this->cache[$id]))
			return;
		if (empty($this->fields))
			return;

		$row = $this->getTablet()->getList(array(
			'select' => array_values($this->fields),
			'filter' => array('=ID' => $id)
		))->fetch();
		if (!empty($row))
			$this->setEntityCacheItem($id, $row, true);
		unset($row);
	}

	private function getEntityCacheItem($id, $load = false)
	{
		$load = ($load === true);

		$result = array();
		if (!isset($this->cache[$id]) && $load && !empty($this->fields))
			$this->loadEntityCacheItem($id);
		if (isset($this->cache[$id]))
			$result = $this->cache[$id];

		return $result;
	}

	private function setEntityCacheItem($id, array $row, $replaceAliases = false)
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

	private function modifyEntityCacheItem($id, array $row)
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

	private function expireEntityCacheItem($id, $copy = false)
	{
		if (empty($this->fields))
			return;

		if (!isset($this->cache[$id]))
			return;
		if (isset($this->cacheModifyed[$id]))
			return;

		$oldData = array();
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

	private function clearEntityCacheItem($id)
	{
		if (isset($this->cache[$id]))
			unset($this->cache[$id]);
		if (isset($this->cacheModifyed[$id]))
			unset($this->cacheModifyed[$id]);
	}

	/* entity cache item tools end */
}