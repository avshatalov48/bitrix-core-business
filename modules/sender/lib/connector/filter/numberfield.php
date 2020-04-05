<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Connector\Filter;

use Bitrix\Main\UI\Filter\NumberType as FilterNumberType;

/**
 * Class DateField
 * @package Bitrix\Sender\Connector\Filter
 */
class NumberField extends AbstractField
{
	/**
	 * Apply filter.
	 *
	 * @param array $filter Filter.
	 * @return void
	 */
	public function applyFilter(array &$filter = array())
	{
		$filterKey = $this->getFilterKey();
		$data = $this->calcNumbers();

		switch ($data['op'])
		{
			case FilterNumberType::SINGLE:
				if (is_numeric($data['from']))
				{
					$filter["=$filterKey"] = $data['from'];
				}
				return;

			case FilterNumberType::MORE:
			case FilterNumberType::LESS:
				$opMore = '>';
				$opLess = '<';
				break;

			default:
				$opMore = '>=';
				$opLess = '<=';
				break;

		}

		if (is_numeric($data['from']))
		{
			$filter["{$opMore}$filterKey"] = $data['from'];
		}
		if (is_numeric($data['to']))
		{
			$filter["{$opLess}$filterKey"] = $data['to'];
		}
	}

	private function calcNumbers()
	{
		$result = array(
			'op' => FilterNumberType::SINGLE,
			'from' => null,
			'to' => null,
		);
		$value = $this->getValue();
		if (!is_array($value) || count($value) === 0)
		{
			return $result;
		}

		$id = $this->getId();
		if (isset($value["{$id}_numsel"]) && in_array($value["{$id}_numsel"], FilterNumberType::getList()))
		{
			$result['op'] = $value["{$id}_numsel"];
		}

		if (isset($value["{$id}_from"]))
		{
			$result['from'] = $value["{$id}_from"];
		}

		if (isset($value["{$id}_to"]))
		{
			$result['to'] = $value["{$id}_to"];
		}

		return $result;
	}
}