<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Connector\Filter;

/**
 * Class DateField
 * @package Bitrix\Sender\Connector\Filter
 */
class DestSelectorField extends AbstractField
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
		$filter[$filterKey] = $this->getValue();
		$data = $this->getData();
		$data['id'] = $filterKey;
		\Bitrix\Crm\UI\Filter\EntityHandler::internalize([$data], $filter);
	}

	/**
	 * Fetch field value.
	 *
	 * @param array $filterFields Filter fields.
	 * @return array
	 */
	public function fetchFieldValue($filterFields)
	{
		$id = $this->getId();
		$value = $filterFields[$id] ?? false;
		if (!array_key_exists($id . '_label', $filterFields))
		{
			return $value ? [$id => $value] : [];
		}
		$label = $filterFields[$id . '_label'];

		if (!$value || !$label)
			return [];

		return [
			'_value' => $value,
			'_label' => $label
		];
	}

	private function getData()
	{
		return $this->data;
	}
}