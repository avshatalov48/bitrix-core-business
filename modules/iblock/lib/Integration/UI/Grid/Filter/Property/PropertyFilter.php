<?php

namespace Bitrix\Iblock\Integration\UI\Grid\Filter\Property;

use Bitrix\Iblock\Integration\UI\Grid\Property\PropertyGridProvider;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\UI\Filter\Theme;

class PropertyFilter extends Filter
{
	/**
	 * Factory method for creating a filter with provider filling.
	 *
	 * @param int $iblockId
	 * @param PropertyGridProvider $gridProvider
	 *
	 * @return self
	 */
	public static function create(int $iblockId, PropertyGridProvider $gridProvider): self
	{
		$id = $gridProvider->getId();
		$provider = new PropertyFilterProvider($iblockId, $gridProvider);

		return new static($id, $provider);
	}

	/**
	 * Data of filter as array.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'FILTER_ID' => $this->getID(),
			'FILTER' => $this->getFieldArrays(),
			'FILTER_PRESETS' => [],
			'ENABLE_LABEL' => true,
			'THEME' => Theme::LIGHT,
			'CONFIG' => [
				'AUTOFOCUS' => false,
			],
		];
	}
}
