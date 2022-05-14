<?php

namespace Bitrix\Catalog\Integration\Report\Filter;

use Bitrix\Report\VisualConstructor\Helper\Filter;

class BaseFilter extends Filter
{
	public function getFilterParameters()
	{
		return [
			'FILTER_ID' => $this->filterId,
			'COMMON_PRESETS_ID' => $this->filterId . '_presets',
			'FILTER' => static::getFieldsList(),
			'DISABLE_SEARCH' => true,
			'FILTER_PRESETS' => static::getPresetsList(),
			'ENABLE_LABEL' => true,
			'ENABLE_LIVE_SEARCH' => false,
			'RESET_TO_DEFAULT_MODE' => false,
			'VALUE_REQUIRED_MODE' => false,
		];
	}
}
