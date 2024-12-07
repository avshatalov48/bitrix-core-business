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

	protected $uiFilterPostfixes = [
		'_datesel', '_month', '_quarter', '_year', '_days', // date
		'_numsel', // number
		'_from', '_to', // date and number ranges
		'_isEmpty', // has no value
		'_hasAnyValue', // has any value
		'_label', // custom entity title
	];

	protected $uiFilterServiceFields = [
		'FILTER_ID',
		'FILTER_APPLIED',
		'PRESET_ID',
		'FIND',
	];

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
		return $fields[$fieldID] ?? null;
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

	/**
	 * Get value suitable to use in getList
	 *
	 *If $rawValue is not set, will be used current main.ui.filter value
	 *
	 * @param array|null $rawValue
	 * @return array
	 */
	public function getValue(?array $rawValue = null): array
	{
		$gridId = $this->getEntityDataProvider()->getSettings()->getID();

		if (!isset($rawValue))
		{
			$options = new \Bitrix\Main\UI\Filter\Options($gridId);
			$rawValue =
				$options->getFilter()
				+ $options->getFilterLogic($this->getFieldArrays())
			;
		}

		$result = $rawValue;
		$this->removeNotUiFilterFields($result);
		$this->prepareListFilterParams($result);
		$this->prepareFilterValue($result);
		$this->removeServiceUiFilterFields($result);

		return $result;
	}

	/**
	 * Clear filter fields from main.ui.filter which are not actually needed for filter in getList
	 * @param array $filter
	 */
	protected function removeServiceUiFilterFields(array &$filter): void
	{
		foreach ($filter as $fieldId => $fieldValue)
		{
			if (in_array($fieldId, $this->uiFilterServiceFields, true))
			{
				unset($filter[$fieldId]);
				continue;
			}
			foreach ($this->uiFilterPostfixes as $postfix)
			{
				if (str_ends_with($fieldId, $postfix))
				{
					unset($filter[$fieldId]);
				}
			}
		}
	}

	/**
	 * Remove fields from $filter which are not really defined as filter fields
	 * @param array $filter
	 */
	protected function removeNotUiFilterFields(array &$filter): void
	{
		$filterFieldsIds = array_map(
			function(Field $filterField)
			{
				return $filterField->getId();
			},
			$this->getFields()
		);
		$sqlWhere = new \CSQLWhere();

		foreach ($filter as $fieldId => $fieldValue)
		{
			if (in_array($fieldId, $this->uiFilterServiceFields, true))
			{
				continue;
			}
			if (in_array($fieldId, $filterFieldsIds, true))
			{
				continue;
			}

			$fieldIdWithoutOperation = $sqlWhere->makeOperation($fieldId)['FIELD'];
			if (in_array($fieldIdWithoutOperation, $filterFieldsIds, true))
			{
				continue;
			}

			foreach ($this->uiFilterPostfixes as $postfix)
			{
				if (str_ends_with($fieldId, $postfix))
				{
					$realFieldId = substr($fieldId, 0, -strlen($postfix));
					if (in_array($realFieldId, $filterFieldsIds, true))
					{
						continue(2);
					}
				}
			}
			unset($filter[$fieldId]); // not a valid filter field
		}
	}

	protected function prepareFilterValue(array &$value): void
	{
		$dataProviders = array_merge(
			[
				$this->getEntityDataProvider(),
			],
			$this->extraProviders,
		);

		foreach ($dataProviders as $dataProvider)
		{
			$value = $dataProvider->prepareFilterValue($value);
		}
	}
}
