<?php

namespace Bitrix\Iblock\Component\Property;

use Bitrix\Iblock\Integration\UI\Grid\Property\LinksBuilder;

/**
 * Links builder for using in component `iblock.property.grid`.
 */
class ComponentLinksBuilder implements LinksBuilder
{
	/**
	 * @inheritDoc
	 */
	public function getActionOpenLink(int $propertyId): ?string
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getActionOpenClick(int $propertyId): ?string
	{
		return "BX.Iblock.PropertyListGrid.Instance.openDetailSlider({$propertyId});";
	}

	/**
	 * @inheritDoc
	 */
	public function getActionDeleteLink(int $propertyId): ?string
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getActionDeleteClick(int $propertyId): ?string
	{
		return "BX.Iblock.PropertyListGrid.Instance.delete({$propertyId});";
	}

	// custom

	/**
	 * JS code of "click" event, for "create" action.
	 *
	 * @return string
	 */
	public function getActionCreateClick(): string
	{
		return 'BX.Iblock.PropertyListGrid.openCreateSliderStatic';
	}

	/**
	 * Link for "create" action.
	 *
	 * @param int $iblockId
	 *
	 * @return string
	 */
	public function getActionCreateUrl(int $iblockId): string
	{
		return "/shop/settings/menu_catalog_attributes_{$iblockId}/details/0/";
	}

	/**
	 * Link to grid.
	 *
	 * @param int $iblockId
	 *
	 * @return string
	 */
	public function getListUrl(int $iblockId): string
	{
		return "/shop/settings/menu_catalog_attributes_{$iblockId}/";
	}
}
