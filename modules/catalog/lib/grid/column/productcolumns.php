<?php

namespace Bitrix\Catalog\Grid\Column;

use Bitrix\Main\Grid\Column\Columns;

class ProductColumns extends Columns
{
	/**
	 * @inheritDoc
	 */
	public function prepareEditableColumnsValues(array $values): array
	{
		$result = parent::prepareEditableColumnsValues($values);
		$result = $this->appendProductFieldValue($result, $values);

		return $result;
	}

	/**
	 * Append product name field.
	 *
	 * For product columns `PRODUCT` it is `NAME` field.
	 *
	 * @param array $result
	 * @param array $values
	 *
	 * @return array
	 */
	private function appendProductFieldValue(array $result, array $values): array
	{
		if (isset($values['NAME']) && !isset($result['NAME']))
		{
			$result['NAME'] = $values['NAME'];
		}

		return $result;
	}
}
