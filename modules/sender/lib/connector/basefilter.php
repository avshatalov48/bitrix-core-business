<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Connector;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options as FilterOptions;

Loc::loadMessages(__FILE__);

/**
 * Class BaseFilter
 * @package Bitrix\Sender\Connector
 */
abstract class BaseFilter extends Base
{
	const FIELD_FOR_PRESET_ALL = 'SENDER_SELECT_ALL';
	const FIELD_PRESET_ID = 'BX_PRESET_ID';

	/** @var string	$filterSettingsUri Filter settings uri. */
	protected $filterSettingsUri = '';
	
	/**
	 * Get form html.
	 *
	 * @return string
	 */
	final public function getForm()
	{
		$presets = $this->getUiFilterPresets();
		$currentPresetId = $this->getCurrentPresetId();
		if ($currentPresetId && isset($presets[$currentPresetId]))
		{
			$preset[$currentPresetId]['default'] = true;
		}

		ob_start();
		/** @var \CAllMain $GLOBALS['APPLICATION'] Application. */
		$GLOBALS['APPLICATION']->includeComponent(
			"bitrix:main.ui.filter",
			"",
			array(
				"FILTER_ID" => $this->getUiFilterId(),
				"CURRENT_PRESET" => $currentPresetId,
				"FILTER" => array_filter(
					static::getFilterFields(),
					function ($field)
					{
						return empty($field['sender_internal']);
					}
				),
				"FILTER_PRESETS" => $presets,
				"DISABLE_SEARCH" => true,
				"ENABLE_LABEL" => true,
			)
		);

		foreach ($this->getUiFilterFields() as $field)
		{
			if (!isset($field['sender_segment_callback']))
			{
				continue;
			}

			if (!is_callable($field['sender_segment_callback']))
			{
				continue;
			}

			echo $field['sender_segment_callback']($field);
		}

		return ob_get_clean();
	}

	/**
	 * Get date-from field value.
	 *
	 * @param string $name Field name.
	 * @param string|null $defaultValue Default value.
	 * @return null|string
	 */
	protected function getFieldDateFrom($name, $defaultValue = null)
	{
		$field = static::getUiFilterField($name);
		if (!$field)
		{
			return null;
		}
		$field['value'] = $this->getFieldValue($name, $defaultValue);

		return Filter\DateField::create($field)->getFrom();
	}

	/**
	 * Get date-to field value.
	 *
	 * @param string $name Field name.
	 * @param string|null $defaultValue Default value.
	 * @return null|string
	 */
	protected function getFieldDateTo($name, $defaultValue = null)
	{
		$field = static::getUiFilterField($name);
		if (!$field)
		{
			return null;
		}
		$field['value'] = $this->getFieldValue($name, $defaultValue);

		return Filter\DateField::create($field)->getTo();
	}

	public function getUiFilterId()
	{
		return $this->getId()  . '_%CONNECTOR_NUM%';
	}

	public function getCurrentPresetId()
	{
		return $this->getFieldValue(self::FIELD_PRESET_ID, null);
	}

	public function setFieldValues(array $fieldValues = null)
	{
		if (is_array($fieldValues) && count($fieldValues) > 0)
		{
			$values = array();
			$fields = $this->getFilterFields();

			$systemFields = array(self::FIELD_PRESET_ID, self::FIELD_FOR_PRESET_ALL);
			foreach ($systemFields as $fieldId)
			{
				if (!isset($fieldValues[$fieldId]) || !$fieldValues[$fieldId])
				{
					continue;
				}

				$values[$fieldId] = $fieldValues[$fieldId];
			}

			foreach ($fields as $field)
			{
				if (!isset($fieldValues[$field['id']]) && !in_array($field['id'], $systemFields))
				{
					continue;
				}

				$values[$field['id']] = $fieldValues[$field['id']];
			}

			$fieldValues = $values;
		}

		parent::setFieldValues($fieldValues);
	}

	/**
	 * Get filters.
	 * Return array of field filters \Bitrix\Main\UI\Filter\Field
	 *
	 * @return \Bitrix\Main\UI\Filter\Field[]
	 */
	public static function getUiFilterFields()
	{
		return array();
	}

	/**
	 * Get UI filter fields.
	 *
	 * @param string $id ID.
	 * @return array|null
	 */
	public static function getUiFilterField($id)
	{
		foreach (static::getFilterFields() as $field)
		{
			if ($field['id'] === $id)
			{
				return $field;
			}
		}

		return null;
	}

	/**
	 * Get Ui filter data.
	 *
	 * @param string $filterId Filter ID.
	 * @return array
	 */
	public static function getUiFilterData($filterId)
	{
		$filterFields = static::getFilterFields();
		$filterOptions = new FilterOptions($filterId, static::getUiFilterPresets());
		$filterRequest = $filterOptions->getFilter($filterFields);

		$filterData = array();
		foreach ($filterFields as $field)
		{
			$fieldId = $field['id'];
			if (isset($filterRequest[$fieldId]))
			{
				$filterData[$fieldId] = $filterRequest[$fieldId];
			}
			elseif ($field['type'] === 'date')
			{
				$dateData = Filter\DateField::create($field)->fetchFieldValue($filterRequest);
				if (is_array($dateData) && count($dateData))
				{
					$filterData[$fieldId] = $dateData;
				}
			}
			elseif ($field['type'] === 'number')
			{
				$numberData = FilterOptions::fetchNumberFieldValue(
					$fieldId . '_numsel',
					$filterRequest
				);
				if (is_array($numberData) && count($numberData))
				{
					if (count($numberData) > 1 || $numberData[$fieldId . '_numsel'] !== null)
					{
						$filterData[$fieldId] = $numberData;
					}
				}
			}
		}

		if (isset($filterRequest['PRESET_ID']) && array_key_exists($filterRequest['PRESET_ID'], static::getUiFilterPresets()))
		{
			$filterData[self::FIELD_PRESET_ID] = $filterRequest['PRESET_ID'];
		}

		return $filterData;
	}

	/**
	 * Get Ui filter presets.
	 *
	 * @return array
	 */
	protected static function getUiFilterPresets()
	{
		return array();
	}

	/**
	 * Get Ui filter presets.
	 *
	 * @return array
	 */
	private static function getFilterFields()
	{
		$fields = static::getUiFilterFields();
		$fields = is_array($fields) ? $fields : array();
		$fields[] = array(
			"id" => self::FIELD_FOR_PRESET_ALL,
			"name" => Loc::getMessage('SENDER_CONNECTOR_BASE_FILTER'),
			'type' => 'checkbox',
			"default" => false,
			"sender_segment_filter" => false,
		);

		return $fields;
	}
}