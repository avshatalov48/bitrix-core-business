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
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
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

}