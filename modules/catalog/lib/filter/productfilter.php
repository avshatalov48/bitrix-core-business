<?php

namespace Bitrix\Catalog\Filter;

use Bitrix\Main;

class ProductFilter extends Main\Filter\Filter
{
	/**
	 * Clear filter fields from main.ui.filter which are not actually needed for filter in getList
	 * @param array $filter
	 */
	protected function removeServiceUiFilterFields(array &$filter): void
	{
		parent::removeServiceUiFilterFields($filter);
		if (
			!empty($filter['SUBQUERY']['FILTER'])
			&& is_array($filter['SUBQUERY']['FILTER'])
		)
		{
			parent::removeServiceUiFilterFields($filter['SUBQUERY']['FILTER']);
		}
	}
}
