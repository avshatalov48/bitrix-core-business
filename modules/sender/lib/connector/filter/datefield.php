<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Connector\Filter;

use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\UI\Filter\AdditionalDateType;

/**
 * Class DateField
 * @package Bitrix\Sender\Connector\Filter
 */
class DateField extends AbstractField
{
	/**
	 * Fetch field value.
	 *
	 * @return array
	 */
	public function fetchFieldValue($filterFields)
	{
		$result = FilterOptions::fetchDateFieldValue(
			$this->getId() . '_datesel',
			$filterFields
		);

		if (!empty($result))
		{
			return $result;
		}

		if (empty($this->data['include']))
		{
			return [];
		}

		if (!in_array(AdditionalDateType::CUSTOM_DATE, $this->data['include']))
		{
			return [];
		}

		$result = [];
		foreach(['days', 'month', 'year'] as $key)
		{
			$fieldKey = $this->getId() . '_' . $key;
			if (empty($filterFields[$fieldKey]))
			{
				continue;
			}

			$result[$fieldKey] = $filterFields[$fieldKey];
		}

		return $result;
	}

	/**
	 * Apply filter.
	 *
	 * @param array $filter Filter.
	 * @return void
	 */
	public function applyFilter(array &$filter = array())
	{
		$filterKey = $this->getFilterKey();
		$from = $this->getFrom();
		$to = $this->getTo();

		if ($from)
		{
			$filter[">=$filterKey"] = $from;
		}
		if ($to)
		{
			$filter["<=$filterKey"] = $to;
		}
		if ($this->getDays())
		{
			$fieldId = $this->getId();
			$filterKey = $this->getId() . '_EXPR_DAYS';
			$filter[] = (new RuntimeFilter())
				->setFilter(
					'=' . $filterKey,
					$this->getDays()
				)
				->addRuntime([
					'name' => $filterKey,
					'expression' => "DAY(%s)",
					'buildFrom' => [$fieldId],
					'parameters' => []
				]);
		}
		if ($this->getMonths())
		{
			$fieldId = $this->getId();
			$filterKey = $this->getId() . '_EXPR_MONTHS';
			$filter[] = (new RuntimeFilter())
				->setFilter(
					'=' . $filterKey,
					$this->getMonths()
				)
				->addRuntime([
					'name' => $filterKey,
					'expression' => "MONTH(%s)",
					'buildFrom' => [$fieldId],
					'parameters' => []
				]);
		}
		if ($this->getYears())
		{
			$fieldId = $this->getId();
			$filterKey = $this->getId() . '_EXPR_YEARS';
			$filter[] = (new RuntimeFilter())
				->setFilter(
					'=' . $filterKey,
					$this->getYears()
				)
				->addRuntime([
					'name' => $filterKey,
					'expression' => "YEAR(%s)",
					'buildFrom' => [$fieldId],
					'parameters' => []
				]);
		}
	}

	/**
	 * Get date from.
	 *
	 * @param string|null $defaultValue Default value.
	 * @return null|string
	 */
	public function getFrom($defaultValue = null)
	{
		return $this->getDate($defaultValue, true);
	}

	/**
	 * Get date to.
	 *
	 * @param string|null $defaultValue Default value.
	 * @return null|string
	 */
	public function getTo($defaultValue = null)
	{
		return $this->getDate($defaultValue, false);
	}

	/**
	 * Get days.
	 *
	 * @return int[]
	 */
	public function getDays()
	{
		return $this->getCustomDateData('days');
	}

	/**
	 * Get months.
	 *
	 * @return int[]
	 */
	public function getMonths()
	{
		return $this->getCustomDateData('month');
	}

	/**
	 * Get years.
	 *
	 * @return int[]
	 */
	public function getYears()
	{
		return $this->getCustomDateData('year');
	}

	private function getCustomDateData($key)
	{
		$key = $this->getId() . '_' . $key;
		$value = $this->getValue();
		if (!is_array($value) || count($value) === 0)
		{
			return [];
		}

		if (empty($value[$key]))
		{
			return [];
		}

		if (!is_array($value[$key]))
		{
			return [];
		}

		return array_map(
			function ($item)
			{
				return (int) $item;
			},
			$value[$key]
		);
	}

	private function getDate($defaultValue = null, $isFrom = true)
	{
		$name = $this->getId();
		$value = $this->getValue();
		if (!is_array($value) || count($value) === 0)
		{
			return $defaultValue;
		}

		$calcData = array();
		FilterOptions::calcDates($name, $value, $calcData);

		if ($isFrom)
		{
			return isset($calcData[$name . '_from']) ? $calcData[$name . '_from'] : $defaultValue;
		}
		else
		{
			return isset($calcData[$name . '_to']) ? $calcData[$name . '_to'] : $defaultValue;
		}
	}
}