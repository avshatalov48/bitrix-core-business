<?php
namespace Bitrix\Landing\Source;

class DataFilter extends Filter
{
	/**
	 * DataFilter constructor.
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
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
			'list' => 'addListValue',
			'checkbox' => 'addCheckboxValue',
			'custom_date' => '',
			'dest_selector' => 'addDestSelectorValue',
			'custom_entity' => '',
			'custom' => ''
		];
	}

	/**
	 * @param array $field
	 * @param string $operator
	 * @return string
	 */
	protected static function getFilterOperator(array $field, $operator)
	{
		$result = '';
		if ($operator === '')
		{
			$operator = 'default';
		}
		if (!empty($field['operators']) && is_array($field['operators']))
		{
			if (isset($field['operators'][$operator]))
			{
				$result = $field['operators'][$operator];
			}
			elseif (isset($field['operators']['default']))
			{
				$result = $field['operators']['default'];
			}
		}
		return $result;
	}

	/**
	 * @param array &$result
	 * @param array $items
	 * @param array $field
	 * @return void
	 */
	protected function addRows(array &$result, array $items, array $field)
	{
		if (empty($items))
		{
			return;
		}

		$entity = (isset($field['entity']) ? $field['entity'] : 'master');
		if ($entity !== '')
		{
			if (!isset($result[$entity]))
			{
				$result[$entity] = [];
			}
			$result[$entity] = array_merge($result[$entity], $items);
		}
		unset($entity);
	}

	/**
	 * @param array &$filter
	 * @param string $fieldId
	 * @param array $field
	 * @param array $value
	 * @return void
	 */
	protected function addQuickSearchValue(array &$filter, $fieldId, array $field, array $value)
	{
		if (isset($value['VALUE']) && is_string($value['VALUE']))
		{
			$findValue = trim($value['VALUE']);
			if ($findValue !== '')
			{
				$operator = $this->getFilterOperator($field, 'quickSearch');
				if (is_string($operator))
				{
					$fieldId = $operator.$fieldId;
				}
				unset($operator);
				$this->addRows($filter, [$fieldId => $findValue], $field);
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
	protected function addStringValue(array &$result, $fieldId, array $field, array $value)
	{
		if (isset($value['VALUE']) && is_string($value['VALUE']) && $value['VALUE'] !== '')
		{
			$operator = $this->getFilterOperator($field, 'default');
			if (is_string($operator))
			{
				$fieldId = $operator.$fieldId;
			}
			unset($operator);
			$this->addRows($result, [$fieldId => $value['VALUE']], $field);
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
		if (isset($value['SUB_TYPE'])
			&& isset($value['_from']) && is_string($value['_from'])
			&& isset($value['_to']) && is_string($value['_to'])
		)
		{
			$minValue = trim($value['_from']);
			$maxValue = trim($value['_to']);
			if ($minValue === '' && $maxValue === '')
			{
				return;
			}

			$items = [];
			switch ($value['SUB_TYPE'])
			{
				case 'exact':
					if ($minValue !== '')
					{
						$operator = $this->getFilterOperator($field, 'exact');
						if (is_string($operator))
						{
							$fieldId = $operator.$fieldId;
						}
						unset($operator);
						$items[$fieldId] = $minValue;
					}
					break;
				case 'range':
					if ($minValue !== '' && $maxValue !== '')
					{
						$operator = $operator = $this->getFilterOperator($field, 'range');
						if (is_string($operator))
						{
							$fieldId = $operator.$fieldId;
						}
						unset($operator);
						$items[$fieldId] = [$minValue, $maxValue];
					}
					break;
				case 'more':
					if ($minValue !== '')
					{
						$operator = $this->getFilterOperator($field, 'more');
						if (is_string($operator))
						{
							$fieldId = $operator.$fieldId;
						}
						unset($operator);
						$items[$fieldId] = $minValue;
					}
					break;
				case 'less':
					if ($maxValue !== '')
					{
						$operator = $this->getFilterOperator($field, 'less');
						if (is_string($operator))
						{
							$fieldId = $operator.$fieldId;
						}
						unset($operator);
						$items[$fieldId] = $maxValue;
					}
					break;
			}
			unset($maxValue, $minValue);

			$this->addRows($result, $items, $field);
			unset($items);
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
		if (isset($value['SUB_TYPE'])
			&& isset($value['_from']) && is_string($value['_from'])
			&& isset($value['_to']) && is_string($value['_to']))
		{
			$minValue = trim($value['_from']);
			$maxValue = trim($value['_to']);
			if ($minValue === '' && $maxValue === '')
			{
				return;
			}

			$items = [];
			switch ($value['SUB_TYPE'])
			{
				case 'EXACT':
					if ($minValue !== '')
					{
						$operator = $this->getFilterOperator($field, 'default');
						if (is_string($operator))
						{
							$fieldId = $operator.$fieldId;
						}
						unset($operator);
						$items[$fieldId] = $minValue;
					}
					break;
				case 'RANGE':
				default:
					if ($minValue !== '' && $maxValue !== '')
					{
						$operator = $this->getFilterOperator($field, 'range');
						if (is_string($operator))
						{
							$fieldId = $operator.$fieldId;
						}
						unset($operator);
						$items[$fieldId] = [$minValue, $maxValue];
					}
					break;
			}
			unset($maxValue, $minValue);

			$this->addRows($result, $items, $field);
			unset($items);
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
		$check = false;
		$id = null;
		$multiple = isset($field['params']['multiple']) && $field['params']['multiple'] == 'Y';
		if ($multiple)
			$validRawValue = !empty($value);
		else
			$validRawValue = isset($value['VALUE']);
		if ($validRawValue)
		{
			if ($multiple)
			{
				$id = [];
				foreach ($value as $row)
				{
					if (is_array($row) && isset($row['VALUE']))
					{
						$id[] = $row['VALUE'];
					}
				}
				unset($row);
				$check = !empty($id);
			}
			else
			{
				$id = $value['VALUE'];
				$check = true;
			}
		}

		if ($check)
		{
			$operator = $this->getFilterOperator($field, ($multiple ? 'enum' : 'exact'));
			if (is_string($operator))
			{
				$fieldId = $operator.$fieldId;
			}
			unset($operator);
			$this->addRows($result, [$fieldId => $id], $field);
		}
		unset($check, $id);
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
			$operator = $this->getFilterOperator($field, 'exact');
			if (is_string($operator))
			{
				$fieldId = $operator.$fieldId;
			}
			unset($operator);
			$this->addRows($result, [$fieldId => $value['VALUE']], $field);
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
		if (isset($value['_value']))
		{
			$multiple = isset($field['params']['multiple']) && $field['params']['multiple'] == 'Y';
			if ($multiple)
				$validRawValue = !empty($value['_value']) && is_array($value['_value']);
			else
				$validRawValue = is_string($value['_value']) || is_int($value['_value']);
			if ($validRawValue)
			{
				$operator = $this->getFilterOperator($field, ($multiple ? 'enum' : 'exact'));
				if (is_string($operator))
				{
					$fieldId = $operator.$fieldId;
				}
				unset($operator);
				$this->addRows($result, [$fieldId => $value['VALUE']], $field);
			}
		}
	}
}
