<?php
namespace Bitrix\Landing\Source;

abstract class Filter
{
	/** @var array Filter field list */
	protected $fields = [];

	protected $fieldTypeHandlers = [];

	/**
	 * Filter constructor.
	 * @return void
	 */
	public function __construct()
	{
		$this->initFieldTypeHandlers();
	}

	/**
	 * @param array $fields
	 * @return void
	 */
	public function setFields(array $fields)
	{
		$this->fields = $fields;
	}

	/**
	 * @param array $sourceFilter
	 * @return array
	 */
	public function create(array $sourceFilter)
	{
		$result = [];
		if (empty($this->fields) || empty($sourceFilter))
		{
			return $result;
		}

		$quickSearchField = $this->getQuickSearchField();

		foreach ($sourceFilter as $row)
		{
			if (!BlockFilter::checkPreparedRow($row))
			{
				continue;
			}

			$index = $row['key'];
			$value = $row['value'];

			if (!isset($this->fields[$index]))
			{
				continue;
			}

			if (
				$index === $quickSearchField
				&& isset($value['QUICK_SEARCH'])
				&& $value['QUICK_SEARCH'] == 'Y'
			)
			{
				$this->addQuickSearchValue($result, $index, $this->fields[$index], $value);
			}
			else
			{
				$handler = $this->getFieldHandler($this->fields[$index]);
				if ($handler !== '' && is_callable([$this, $handler]))
				{
					call_user_func_array(
						[$this, $handler],
						[&$result, $index, $this->fields[$index], $value]
					);
				}
			}
		}
		unset($row);

		return $result;
	}

	/**
	 * @return void
	 */
	protected function initFieldTypeHandlers()
	{
		$this->fieldTypeHandlers = [];
	}

	/**
	 * @param array $field
	 * @return string
	 */
	protected function getFieldHandler(array $field)
	{
		$result = '';
		if (!isset($field['type']))
		{
			$field['type'] = 'string';
		}
		if (isset($this->fieldTypeHandlers[$field['type']]))
		{
			$result = $this->fieldTypeHandlers[$field['type']];
		}

		return $result;
	}

	/**
	 * @return string|null
	 */
	protected function getQuickSearchField()
	{
		$result = null;
		if (!empty($this->fields))
		{
			foreach (array_keys($this->fields) as $index)
			{
				$row = $this->fields[$index];
				if (
					(!isset($row['quickSearch']) && !isset($row['quickSearchOnly']))
					|| (isset($row['entity']) && $row['entity'] != 'master')
				)
				{
					continue;
				}
				$result = $row['id'];
			}
			unset($row, $index);
		}
		return $result;
	}

	/**
	 * @param array &$result
	 * @param string $fieldId
	 * @param array $field
	 * @param array $value
	 * @return void
	 */
	protected function addQuickSearchValue(array &$result, $fieldId, array $field, array $value) {}
}