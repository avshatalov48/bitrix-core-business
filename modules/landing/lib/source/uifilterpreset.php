<?php
namespace Bitrix\Landing\Source;

class UiFilterPreset extends Filter
{
	/**
	 * UiFilterPreset constructor.
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param array $sourceFilter
	 * @return array
	 */
	public function create(array $sourceFilter)
	{
		$result = parent::create($sourceFilter);

		if (!empty($this->fields) && !empty($sourceFilter))
		{
			if ($this->getQuickSearchField() !== null && !isset($result['FIND']))
			{
				$result['FIND'] = '';
			}
		}

		return $result;
	}

	/**
	 * @return void
	 */
	protected function initFieldTypeHandlers()
	{
		$this->fieldTypeHandlers = [
			'string' => 'addStringValue',
			'number' => 'addNumberValue',
			'date' => 'addDateValue',
			'custom_date' => 'addCustomDateValue',
			'list' => 'addListValue',
			'checkbox' => 'addCheckboxValue',
			'dest_selector' => 'addDestSelectorValue',
			'custom_entity' => '',
			'custom' => ''
		];
	}

	/**
	 * @param array &$result
	 * @param string $fieldId
	 * @param array $field
	 * @param array $value
	 * @return void
	 */
	protected function addQuickSearchValue(array &$result, $fieldId, array $field, array $value)
	{
		if (isset($value['VALUE']) && is_string($value['VALUE']) && $value['VALUE'] !== '')
		{
			$result['FIND'] = $value['VALUE'];
		}
	}

	/**
	 * @param array &$result
	 * @param string $fieldId
	 * @param array $field
	 * @param array $value
	 * @return void
	 */
	protected function addStringValue(array &$result, $fieldId, array $field, array $value)
	{
		if (isset($value['VALUE']) && is_string($value['VALUE']) && $value['VALUE'] !== '')
		{
			$result[$fieldId] = $value['VALUE'];
		}
	}

	/**
	 * @param array &$result
	 * @param string $fieldId
	 * @param array $field
	 * @param array $value
	 * @return void
	 */
	protected function addNumberValue(array &$result, $fieldId, array $field, array $value)
	{
		if (isset($value['SUB_TYPE']))
		{
			$result[$fieldId.'_numsel'] = $value['SUB_TYPE'];
			$result[$fieldId.'_from'] = (isset($value['_from']) ? $value['_from'] : '');
			$result[$fieldId.'_to'] = (isset($value['_to']) ? $value['_to'] : '');
		}
	}

	/**
	 * @param array &$result
	 * @param string $fieldId
	 * @param array $field
	 * @param array $value
	 * @return void
	 */
	protected function addDateValue(array &$result, $fieldId, array $field, array $value)
	{
		if (isset($value['SUB_TYPE']))
		{
			$result[$fieldId.'_datesel'] = $value['SUB_TYPE'];
			$result[$fieldId.'_from'] = (isset($value['_from']) ? $value['_from'] : '');
			$result[$fieldId.'_to'] = (isset($value['_to']) ? $value['_to'] : '');
			$result[$fieldId.'_days'] = (isset($value['_days']) ? $value['_days'] : '');
			$result[$fieldId.'_month'] = (isset($value['_month']) ? $value['_month'] : '');
			$result[$fieldId.'_quarter'] = (isset($value['_quarter']) ? $value['_quarter'] : '');
			$result[$fieldId.'_year'] = (isset($value['_year']) ? $value['_year'] : '');
			$result[$fieldId.'_allow_year'] = (isset($value['_allow_year']) ? $value['_allow_year'] : '');
		}
	}

	/**
	 * @param array &$result
	 * @param string $fieldId
	 * @param array $field
	 * @param array $value
	 * @return void
	 */
	protected function addCustomDateValue(array &$result, $fieldId, array $field, array $value)
	{
		if (isset($value['days']) || isset($value['months']) || isset($value['years']))
		{
			$result[$fieldId.'days'] = (isset($value['days']) ? $value['days'] : '');
			$result[$fieldId.'months'] = (isset($value['months']) ? $value['months'] : '');
			$result[$fieldId.'years'] = (isset($value['years']) ? $value['years'] : '');
		}
	}

	/**
	 * @param array &$result
	 * @param string $fieldId
	 * @param array $field
	 * @param array $value
	 * @return void
	 */
	protected function addListValue(array &$result, $fieldId, array $field, array $value)
	{
		if (isset($field['params']['multiple']) && $field['params']['multiple'] = 'Y')
		{
			$list = [];

			foreach ($value as $row)
			{
				if (!empty($row) && is_array($row) && isset($row['VALUE']))
				{
					$list[] = $row['VALUE'];
				}
			}
			unset($row);

			if (!empty($list))
			{
				$result[$fieldId] = $list;
			}
			unset($list);
		}
		else
		{
			if (isset($value['VALUE']))
			{
				$result[$fieldId] = $value['VALUE'];
			}
		}
	}

	/**
	 * @param array &$result
	 * @param string $fieldId
	 * @param array $field
	 * @param array $value
	 * @return void
	 */
	protected function addCheckboxValue(array &$result, $fieldId, array $field, array $value)
	{
		if (
			isset($value['VALUE'])
			&& ($value['VALUE'] === 'Y' || $value['VALUE'] === 'N')
		)
		{
			$result[$fieldId] = $value['VALUE'];
		}
	}

	/**
	 * @param array &$result
	 * @param string $fieldId
	 * @param array $field
	 * @param array $value
	 * @return void
	 */
	protected function addDestSelectorValue(array &$result, $fieldId, array $field, array $value)
	{
		if (
			isset($value['_value'])
			&& (isset($value['_label']))
		)
		{
			$result[$fieldId] = $value['_value'];
			$result[$fieldId.'_label'] = $value['_label'];
		}
	}
}