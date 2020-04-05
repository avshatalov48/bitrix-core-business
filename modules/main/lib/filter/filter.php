<?php
namespace Bitrix\Main\Filter;

class Filter
{
	/** @var string */
	protected $ID = '';
	/** @var DataProvider|null */
	protected $entityDataProvider = null;
	/** @var DataProvider[]|null */
	protected $extraProviders = null;

	/** @var array|null  */
	protected $params = null;

	/** @var Field[]|null */
	protected $fields = null;

	function __construct($ID, DataProvider $entityDataProvider, array $extraDataProviders = null, array $params = null)
	{
		$this->ID = $ID;
		$this->entityDataProvider = $entityDataProvider;

		$this->extraProviders = [];
		if(is_array($extraDataProviders))
		{
			foreach($extraDataProviders as $dataProvider)
			{
				if($dataProvider instanceof DataProvider)
				{
					$this->extraProviders[] = $dataProvider;
				}
			}
		}

		$this->params = is_array($params) ? $params : [];
	}

	/**
	 * Get Filter ID.
	 * @return string
	 */
	function getID()
	{
		return $this->ID;
	}

	/**
	 * Get Default Field IDs.
	 * @return array
	 */
	public function getDefaultFieldIDs()
	{
		$results = [];
		foreach($this->getFields() as $fieldID => $field)
		{
			if($field->isDefault())
			{
				$results[] = $fieldID;
			}
		}
		return $results;
	}

	/**
	 * Get Field list.
	 * @return Field[]
	 */
	public function getFields()
	{
		if($this->fields === null)
		{
			$this->fields = $this->entityDataProvider->prepareFields();
			foreach($this->extraProviders as $dataProvider)
			{
				$fields = $dataProvider->prepareFields();
				if(!empty($fields))
				{
					$this->fields += $fields;
				}
			}
		}
		return $this->fields;
	}

	/**
	 * Get Fields converted to plain object (array).
	 *
	 * @param array $fieldMask
	 * @return array
	 */
	public function getFieldArrays(array $fieldMask = [])
	{
		$results = [];
		$allFields = true;
		if (!empty($fieldMask))
		{
			$fieldMask = array_fill_keys($fieldMask, true);
			$allFields = false;
		}
		$fields = $this->getFields();
		foreach($fields as $field)
		{
			if ($allFields || isset($fieldMask[$field->getId()]))
			{
				$results[] = $field->toArray();
			}
		}
		return $results;
	}

	/**
	 * Get Field by ID.
	 * @param string $fieldID Field ID.
	 * @return Field|null
	 */
	public function getField($fieldID)
	{
		$fields = $this->getFields();
		return isset($fields[$fieldID]) ? $fields[$fieldID] : null;
	}

	/**
	 * @return DataProvider|null
	 */
	public function getEntityDataProvider()
	{
		return $this->entityDataProvider;
	}

	/**
	 * Prepare list filter params.
	 * @param array $filter Source Filter.
	 * @return void
	 */
	public function prepareListFilterParams(array &$filter)
	{
		foreach ($filter as $k => $v)
		{
			$this->entityDataProvider->prepareListFilterParam($filter, $k);
		}
	}
}