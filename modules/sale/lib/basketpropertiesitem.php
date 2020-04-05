<?php
namespace Bitrix\Sale;

use Bitrix\Main\Entity;
use Bitrix\Sale\Internals;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class BasketPropertyItem
 * @package Bitrix\Sale
 */
class BasketPropertyItem extends BasketPropertyItemBase
{
	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @param array $data
	 * @return Entity\AddResult
	 */
	protected function addInternal(array $data)
	{
		return Internals\BasketPropertyTable::add($data);
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return Entity\UpdateResult
	 */
	protected function updateInternal($primary, array $data)
	{
		return Internals\BasketPropertyTable::update($primary, $data);
	}

	/**
	 * @return array
	 */
	protected static function getFieldsMap()
	{
		return Internals\BasketPropertyTable::getMap();
	}
}