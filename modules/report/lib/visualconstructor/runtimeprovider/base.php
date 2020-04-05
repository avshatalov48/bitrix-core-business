<?php
namespace Bitrix\Report\VisualConstructor\RuntimeProvider;


use Bitrix\Report\VisualConstructor\Internal\Error\Error;
use Bitrix\Report\VisualConstructor\Internal\Error\IErrorable;
use Bitrix\Report\VisualConstructor\IProvidable;

/**
 * Class Base
 * @package Bitrix\Report\VisualConstructor\RuntimeProvider
 */
abstract class Base implements IErrorable
{
	private $results = array();
	private $filters = array();
	private $errors = array();
	private $relations = array();

	/**
	 * @return array
	 */
	abstract protected function availableFilterKeys();

	/**
	 * @return \Bitrix\Report\VisualConstructor\Internal\Manager\Base
	 */
	abstract protected function getManagerInstance();

	/**
	 * @return IProvidable[]
	 */
	abstract protected function getEntitiesList();

	/**
	 * @return mixed
	 */
	abstract protected function getIndices();

	/**
	 * Check is available filter in filters list of provider.
	 *
	 * @param string $filterKey Needle filter key.
	 * @return bool
	 */
	private function isAvailableFilter($filterKey)
	{
		$availableFilterKeys = $this->availableFilterKeys();

		return in_array($filterKey, $availableFilterKeys);
	}

	/**
	 * @param string $key Filter key.
	 * @param mixed $value Filter value.
	 * @return bool
	 */
	public function addFilter($key, $value)
	{
		if ($this->isAvailableFilter($key))
		{
			$this->filters[$key] = $this->normaliseFilterValue($value);
			return true;
		}
		else
		{
			$this->errors[] = new Error('Filter with key:' . $key . ' not available for this provider');
			return false;
		}
	}

	/**
	 * @param string $key Key of relation.
	 * @return void
	 */
	public function addRelation($key)
	{
		$this->relations[] = $key;
	}

	/**
	 * @return array
	 */
	public function getFilters()
	{
		return $this->filters;
	}

	/**
	 * @return array
	 */
	public function getRelations()
	{
		return $this->relations;
	}

	/**
	 * @return $this
	 */
	public function execute()
	{
		$this->callManager();
		$indices = $this->getIndices();
		$filteredEntityIds = $this->getFilteredValues($indices);
		$entities = $this->getEntitiesList();
		$result = array();
		$filters = $this->getFilters();
		if (!empty($filters))
		{
			foreach ($entities as $key => $entity)
			{
				if (in_array($key, $filteredEntityIds))
				{
					$this->processAvailableRelations($entity);
					$result[] = $entity;
				}
			}
		}
		else 
		{
			foreach ($entities as $key => $entity)
			{
					$this->processAvailableRelations($entity);
					$result[] = $entity;
			}
		}
		$this->setResults($result);

		return $this;
	}

	/**
	 * @param array $indices Indices for search by filters.
	 * @return array
	 */
	private function getFilteredValues($indices)
	{
		$filteredEntityIds = array();
		foreach ($this->getFilters() as $filterType => $filterValues)
		{
			if ($filterType !== 'primary')
			{
				$newFilterEntityIds = array();
				foreach ($filterValues as $filterValue)
				{
					if (isset($indices[$filterType][$filterValue]))
					{
						$newFilterEntityIds = array_merge($newFilterEntityIds, $indices[$filterType][$filterValue]);
					}
				}
				if (!empty($filteredEntityIds))
				{
					$filteredEntityIds = array_intersect($filteredEntityIds, $newFilterEntityIds);
				}
				else
				{
					$filteredEntityIds = $newFilterEntityIds;
				}
			}
			else
			{
				if (!empty($filteredEntityIds))
				{
					$filteredEntityIds = array_intersect($filteredEntityIds, $filterValues);
				}
				else
				{
					$filteredEntityIds = $filterValues;
				}
			}
		}
		return array_unique($filteredEntityIds);
	}


	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * @param array|mixed $value Value to normalise.
	 * @return array
	 */
	private function normaliseFilterValue($value)
	{
		$result = is_array($value) ? array_unique($value) : array($value);
		return $result;
	}

	/**
	 * @param object $entity Entity passed to processor.
	 * @return void
	 */
	protected function processAvailableRelations($entity)
	{
		foreach ($this->getRelations() as $relationName)
		{
			$processMethodName = 'processWith' . ucfirst($relationName);
			call_user_func_array(array($this, $processMethodName), array($entity));
		}
	}


	protected function callManager()
	{
		$this->getManagerInstance()->call();
	}


	/**
	 * @return mixed
	 */
	public function getFirstResult()
	{
		$results = $this->getResults();
		return array_shift($results);
	}


	/**
	 * @return array
	 */
	public function getResults()
	{
		return $this->results;
	}

	/**
	 * @param array $results Result for setting as result of provider.
	 * @return void
	 */
	public function setResults($results)
	{
		$this->results = $results;
	}

}