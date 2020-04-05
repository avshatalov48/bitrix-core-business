<?php
namespace Bitrix\Iblock\Component;

use Bitrix\Catalog;
/**
 * Class Filters
 * Provides various useful methods for sorted offers.
 *
 * @package Bitrix\Iblock\Component
 */
class Filters
{
	/**
	 * Return offers id by filter.
	 *
	 * @param array $filter				CIBlockElement::getList filter.
	 * @return array
	 */
	public static function getFilteredOffersId(array $filter)
	{
		$result = array();
		if (empty($filter) || !is_array($filter))
			return $result;

		$itemsIterator = \CIBlockElement::getList(array(), $filter, false, false, array('ID'));
		while ($item = $itemsIterator->fetch())
		{
			$item['ID'] = (int)$item['ID'];
			$result[$item['ID']] = $item['ID'];
		}
		unset($item, $itemsIterator);

		return $result;
	}

	/**
	 * Return offer id by filter group by product id.
	 *
	 * @param int $iblockId				Offers iblock id.
	 * @param int $propertyId			Sku property id.
	 * @param array $filter				CIBlockElement::getList filter.
	 * @return array
	 */
	public static function getFilteredOffersByProduct($iblockId, $propertyId, array $filter)
	{
		$result = array();
		$iblockId = (int)$iblockId;
		$propertyId = (int)$propertyId;
		if ($iblockId <= 0 || $propertyId <= 0)
			return $result;
		if (empty($filter) || !is_array($filter))
			return $result;

		$valuesIterator = \CIBlockElement::getPropertyValues($iblockId, $filter, false, array('ID' => $propertyId));
		while ($value = $valuesIterator->fetch())
		{
			$productId = (int)$value[$propertyId];
			$offerId = (int)$value['IBLOCK_ELEMENT_ID'];
			if (!isset($result[$productId]))
				$result[$productId] = array();
			$result[$productId][$offerId] = $offerId;
		}
		unset($value, $valuesIterator);

		return $result;
	}
}