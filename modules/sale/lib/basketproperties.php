<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */

namespace Bitrix\Sale;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class BasketPropertiesCollection
 * @package Bitrix\Sale
 */
class BasketPropertiesCollection extends BasketPropertiesCollectionBase
{
	/**
	 * @return BasketPropertiesCollection
	 */
	protected static function createBasketPropertiesCollectionObject()
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$basketPropertiesCollectionClassName = $registry->getBasketPropertiesCollectionClassName();

		return new $basketPropertiesCollectionClassName();
	}

	/**
	 * Load basket item properties.
	 *
	 * @param array $parameters	orm getList parameters.
	 * @return \Bitrix\Main\DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		return Internals\BasketPropertyTable::getList($parameters);
	}

	/**
	 * Delete basket item properties.
	 *
	 * @param $primary
	 * @return Entity\DeleteResult
	 */
	protected static function delete($primary)
	{
		return Internals\BasketPropertyTable::delete($primary);
	}

	/**
	 * @return string
	 */
	protected function getBasketPropertiesCollectionElementClassName()
	{
		$registry  = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);

		return $registry->getBasketPropertyItemClassName();
	}

}