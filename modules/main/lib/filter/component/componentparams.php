<?php

namespace Bitrix\Main\Filter\Component;

use Bitrix\Main\Filter\Filter;
use Bitrix\Main\UI\Filter\Theme;

/**
 * Params for `main.ui.filter` component.
 */
final class ComponentParams
{
	private Filter $filter;

	public function __construct(Filter $filter)
	{
		$this->filter = $filter;
	}

	public static function get(Filter $filter, array $additionalParams = []): array
	{
		return (new self($filter))->getParams($additionalParams);
	}

	/**
	 * Get component parameters.
	 *
	 * @param array $additionalParams if filter is used with grid, it must contain `GRID_ID`
	 *
	 * @return array
	 */
	public function getParams(array $additionalParams = []): array
	{
		return $additionalParams + [
			'FILTER_ID' => $this->filter->getID(),
			'FILTER' => $this->filter->getFieldArrays(),
			'FILTER_PRESETS' => [],
			'ENABLE_LABEL' => true,
			'THEME' => Theme::LIGHT,
			'CONFIG' => [
				'AUTOFOCUS' => false,
			],
			// LAZY_LOAD
			// VALUE_REQUIRED
			// ENABLE_ADDITIONAL_FILTERS
			// ENABLE_FIELDS_SEARCH
			// HEADERS_SECTIONS
			// MESSAGES
			// RESET_TO_DEFAULT_MODE
			// VALUE_REQUIRED_MODE
			// DISABLE_SEARCH
			// ENABLE_LIVE_SEARCH
			// COMPACT_STATE
			// ENABLE_LABEL
			// FILTER_PRESETS
			// FILTER_ROWS
			// COMMON_PRESETS_ID
			// RENDER_FILTER_INTO_VIEW
			// RENDER_FILTER_INTO_VIEW_SORT
		];
	}
}
