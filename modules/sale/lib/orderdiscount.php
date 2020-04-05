<?php
namespace Bitrix\Sale;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Internals;

Loc::loadMessages(__FILE__);

class OrderDiscount extends OrderDiscountBase
{
	/**
	 * Delete all data by order.
	 *
	 * @param int $order			Order id.
	 * @return void
	 */
	public static function deleteByOrder($order)
	{
		$order = (int)$order;
		if ($order <= 0)
			return;
		Internals\OrderRulesTable::clearByOrder($order);
		Internals\OrderDiscountDataTable::clearByOrder($order);
		Internals\OrderRoundTable::clearByOrder($order);
	}

	/**
	 * Return parent entity type.
	 * @internal
	 *
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * Validate coupon.
	 *
	 * @param array $fields		Coupon data.
	 * @return Result
	 */
	protected static function validateCoupon(array $fields)
	{
		if ($fields['TYPE'] == Internals\DiscountCouponTable::TYPE_ARCHIVED)
			return new Result();;

		return parent::validateCoupon($fields);
	}

	/* discounts */

	/**
	 * Discount getList.
	 *
	 * @param array $parameters		\Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	protected static function getDiscountIterator(array $parameters)
	{
		return Internals\DiscountTable::getList($parameters);
	}

	/* discounts end */

	/* coupons */

	/**
	 * Check coupon type.
	 *
	 * @param int $type		Coupon type.
	 * @return bool
	 */
	protected static function isValidCouponTypeInternal($type)
	{
		return Internals\DiscountCouponTable::isValidCouponType($type);
	}

	/* coupons end */

	/* order discounts */

	/**
	 * Order discount getList.
	 *
	 * @param array $parameters		\Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	protected static function getOrderDiscountIterator(array $parameters)
	{
		return Internals\OrderDiscountTable::getList($parameters);
	}

	/**
	 * Low-level method add new discount for order.
	 *
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\AddResult|null
	 */
	protected static function addOrderDiscountInternal(array $fields)
	{
		return Internals\OrderDiscountTable::add($fields);
	}

	/**
	 * Returns the list of missing discount fields.
	 *
	 * @param array $fields		Discount fields.
	 * @return array
	 */
	protected static function checkRequiredOrderDiscountFields(array $fields)
	{
		return Internals\OrderDiscountTable::getEmptyFields($fields);
	}

	/**
	 * Clear raw order discount data.
	 *
	 * @param array $rawFields	Discount information.
	 * @return array|null
	 */
	protected static function normalizeOrderDiscountFieldsInternal(array $rawFields)
	{
		$result = Internals\OrderDiscountTable::prepareDiscountData($rawFields);
		return (is_array($result) ? $result : null);
	}

	/**
	 * Calculate order discount hash.
	 *
	 * @param array $fields		Discount information.
	 * @return string|null
	 */
	protected static function calculateOrderDiscountHashInternal(array $fields)
	{
		$hash = Internals\OrderDiscountTable::calculateHash($fields);
		return ($hash === false ? null : $hash);
	}

	/* order discounts end */

	/* order coupons */

	/**
	 * Order coupons getList.
	 *
	 * @param array $parameters \Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	public static function getOrderCouponIterator(array $parameters)
	{
		return Internals\OrderCouponsTable::getList($parameters);
	}

	/**
	 * Low-level method add new coupon for order.
	 *
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\AddResult|null
	 */
	protected static function addOrderCouponInternal(array $fields)
	{
		return Internals\OrderCouponsTable::add($fields);
	}

	/* order coupons end */

	/* order discount modules */

	/**
	 * @param array $parameters		\Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	protected static function getOrderDiscountModuleIterator(array $parameters)
	{
		return Internals\OrderModulesTable::getList($parameters);
	}

	/**
	 * Low-level method save order discount modules.
	 *
	 * @param int $orderDiscountId
	 * @param array $modules
	 * @return bool
	 */
	protected static function saveOrderDiscountModulesInternal($orderDiscountId, array $modules)
	{
		$result = true;

		$resultModule = Internals\OrderModulesTable::saveOrderDiscountModules(
			$orderDiscountId,
			$modules
		);
		if (!$resultModule)
		{
			Internals\OrderDiscountTable::clearList($orderDiscountId);
			$result = false;
		}
		unset($resultModule);

		return $result;
	}

	/* discount results */

	/**
	 * Converts the discount result entity identifier to the database table format.
	 *
	 * @param string $entity
	 * @return null|int
	 */
	protected static function getResultEntityInternal($entity)
	{
		$result = null;

		/** @var Discount $discountClassName */
		$discountClassName = static::getDiscountClassName();

		switch ($entity)
		{
			case $discountClassName::ENTITY_BASKET_ITEM:
				$result = Internals\OrderRulesTable::ENTITY_TYPE_BASKET_ITEM;
				break;
			case $discountClassName::ENTITY_DELIVERY:
				$result = Internals\OrderRulesTable::ENTITY_TYPE_DELIVERY;
				break;
		}

		unset($discountClassName);

		return $result;
	}

	/**
	 * Converts the discount result entity identifier from the database table format.
	 *
	 * @param int $entity
	 * @return null|string
	 */
	protected static function getResultEntityFromInternal($entity)
	{
		$result = null;

		/** @var Discount $discountClassName */
		$discountClassName = static::getDiscountClassName();

		switch ($entity)
		{
			case Internals\OrderRulesTable::ENTITY_TYPE_BASKET_ITEM:
				$result = $discountClassName::ENTITY_BASKET_ITEM;
				break;
			case Internals\OrderRulesTable::ENTITY_TYPE_DELIVERY:
				$result = $discountClassName::ENTITY_DELIVERY;
				break;
		}

		unset($discountClassName);

		return $result;
	}

	/**
	 * @param array $parameters		\Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	protected static function getResultIterator(array $parameters)
	{
		if (!isset($parameters['select']))
			$parameters['select'] = ['*', 'RULE_DESCR' => 'DESCR.DESCR', 'RULE_DESCR_ID' => 'DESCR.ID'];
		if (!isset($parameters['order']))
			$parameters['order'] = ['ID' => 'ASC'];
		return Internals\OrderRulesTable::getList($parameters);
	}

	/**
	 * @param array $parameters		\Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	protected static function getResultDescriptionIterator(array $parameters)
	{
		return Internals\OrderRulesDescrTable::getList($parameters);
	}

	/**
	 * Low-level method returns result table name.
	 *
	 * @return string|null
	 */
	protected static function getResultTableNameInternal()
	{
		return Internals\OrderRulesTable::getTableName();
	}

	/**
	 * Low-level method returns result description table name.
	 *
	 * @return string|null
	 */
	protected static function getResultDescriptionTableNameInternal()
	{
		return Internals\OrderRulesDescrTable::getTableName();
	}

	/**
	 * Low-level method returns only those fields that are in the result table.
	 *
	 * @param array $fields
	 * @return array|null
	 */
	protected static function checkResultTableWhiteList(array $fields)
	{
		$fields = array_intersect_key($fields, Internals\OrderRulesTable::getEntity()->getScalarFields());
		return (!empty($fields) ? $fields : null);
	}

	/**
	 * Low-level method returns only those fields that are in the result description table.
	 *
	 * @param array $fields
	 * @return array|null
	 */
	protected static function checkResultDescriptionTableWhiteList(array $fields)
	{
		$fields = array_intersect_key($fields, Internals\OrderRulesDescrTable::getEntity()->getScalarFields());
		return (!empty($fields) ? $fields : null);
	}

	/**
	 * Low-level method add new result discount for order.
	 *
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\AddResult|null
	 */
	protected static function addResultInternal(array $fields)
	{
		return Internals\OrderRulesTable::add($fields);
	}

	/**
	 * Low-level method add new result description for order.
	 *
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\AddResult|null
	 */
	protected static function addResultDescriptionInternal(array $fields)
	{
		return Internals\OrderRulesDescrTable::add($fields);
	}

	/**
	 * Low-level method update result discount for order.
	 *
	 * @param int $id			Primary key.
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\UpdateResult|null
	 */
	protected static function updateResultInternal($id, array $fields)
	{
		return Internals\OrderRulesTable::update($id, $fields);
	}

	/**
	 * Low-level method update result description for order.
	 *
	 * @param int $id			Primary key.
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\UpdateResult|null
	 */
	protected static function updateResultDescriptionInternal($id, array $fields)
	{
		return Internals\OrderRulesDescrTable::update($id, $fields);
	}

	/* discount results end */

	/* round result */

	/**
	 * Converts the rounded entity identifier to the database table format.
	 *
	 * @param string $entity
	 * @return null|int
	 */
	protected static function getRoundEntityInternal($entity)
	{
		$result = null;

		/** @var Discount $discountClassName */
		$discountClassName = static::getDiscountClassName();

		switch ($entity)
		{
			case $discountClassName::ENTITY_BASKET_ITEM:
				$result = Internals\OrderRoundTable::ENTITY_TYPE_BASKET_ITEM;
				break;
		}

		unset($discountClassName);

		return $result;
	}

	/**
	 * Converts the rounded entity identifier from the database table format.
	 *
	 * @param int $entity
	 * @return null|string
	 */
	protected static function getRoundEntityFromInternal($entity)
	{
		$result = null;

		/** @var Discount $discountClassName */
		$discountClassName = static::getDiscountClassName();

		switch ($entity)
		{
			case Internals\OrderRoundTable::ENTITY_TYPE_BASKET_ITEM:
				$result = $discountClassName::ENTITY_BASKET_ITEM;
				break;
		}

		unset($discountClassName);

		return $result;
	}

	/**
	 * @param array $parameters		\Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	protected static function getRoundResultIterator(array $parameters)
	{
		if (empty($parameters['select']))
			$parameters['select'] = ['*'];
		return Internals\OrderRoundTable::getList($parameters);
	}

	/**
	 * Low-level method add new round result for order.
	 *
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\AddResult|null
	 */
	protected static function addRoundResultInternal(array $fields)
	{
		return Internals\OrderRoundTable::add($fields);
	}

	/**
	 * Low-level method update round result for order.
	 *
	 * @param int $id			Tablet row id.
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\UpdateResult|null
	 */
	protected static function updateRoundResultInternal($id, array $fields)
	{
		return Internals\OrderRoundTable::update($id, $fields);
	}

	/**
	 * Low-level method returns round result table name.
	 *
	 * @return string|null
	 */
	protected static function getRoundTableNameInternal()
	{
		return Internals\OrderRoundTable::getTableName();
	}

	/* round result end */

	/* data storage */

	/**
	 * Low-level method for convert storage types to internal format.
	 *
	 * @param string $storageType	Abstract storage type.
	 * @return int|null
	 */
	protected static function getStorageTypeInternal($storageType)
	{
		$result = null;

		switch ($storageType)
		{
			case OrderDiscountBase::STORAGE_TYPE_DISCOUNT_ACTION_DATA:
				$result = Internals\OrderDiscountDataTable::ENTITY_TYPE_DISCOUNT_STORED_DATA;
				break;
			case OrderDiscountBase::STORAGE_TYPE_ORDER_CONFIG:
				$result = Internals\OrderDiscountDataTable::ENTITY_TYPE_ORDER;
				break;
			case OrderDiscountBase::STORAGE_TYPE_ROUND_CONFIG:
				$result = Internals\OrderDiscountDataTable::ENTITY_TYPE_ROUND;
				break;
			case OrderDiscountBase::STORAGE_TYPE_BASKET_ITEM:
				$result = Internals\OrderDiscountDataTable::ENTITY_TYPE_BASKET_ITEM;
		}

		return $result;
	}

	/**
	 * @param array $parameters		\Bitrix\Main\Entity\DataManager::getList parameters.
	 * @return Main\DB\Result|null
	 */
	protected static function getStoredDataIterator(array $parameters)
	{
		return Internals\OrderDiscountDataTable::getList($parameters);
	}

	/**
	 * Low-level method add stored data for order.
	 *
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\AddResult|null
	 */
	protected static function addStoredDataInternal(array $fields)
	{
		return Internals\OrderDiscountDataTable::add($fields);
	}

	/**
	 * Low-level method update stored data for order.
	 *
	 * @param int $id			Tablet row id.
	 * @param array $fields		Tablet fields.
	 * @return Main\Entity\UpdateResult|null
	 */
	protected static function updateStoredDataInternal($id, array $fields)
	{
		return Internals\OrderDiscountDataTable::update($id, $fields);
	}

	/**
	 * Low-level method returns the order stored data table name.
	 *
	 * @return string|null
	 */
	protected static function getStoredDataTableInternal()
	{
		return Internals\OrderDiscountDataTable::getTableName();
	}

	/* data storage end */
}