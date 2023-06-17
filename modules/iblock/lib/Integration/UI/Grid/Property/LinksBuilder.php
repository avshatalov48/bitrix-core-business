<?php

namespace Bitrix\Iblock\Integration\UI\Grid\Property;

/**
 * Link builder for the property grid provider.
 *
 * It is necessary to separate the grid provider and the endpoint of using the grid (page or component).
 *
 * @see \Bitrix\Iblock\Integration\UI\Grid\Property\PropertyGridProvider to view use cases.
 */
interface LinksBuilder
{
	/**
	 * Link for "open" action.
	 *
	 * @param int $propertyId
	 *
	 * @return string|null
	 */
	public function getActionOpenLink(int $propertyId): ?string;

	/**
	 * JS code of "click" event, for "open" action.
	 *
	 * @param int $propertyId
	 *
	 * @return string|null
	 */
	public function getActionOpenClick(int $propertyId): ?string;

	/**
	 * Link for "delete" action.
	 *
	 * @param int $propertyId
	 *
	 * @return string|null
	 */
	public function getActionDeleteLink(int $propertyId): ?string;

	/**
	 * JS code of "click" event, for "delete" action.
	 *
	 * @param int $propertyId
	 *
	 * @return string|null
	 */
	public function getActionDeleteClick(int $propertyId): ?string;
}
