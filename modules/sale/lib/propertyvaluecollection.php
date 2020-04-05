<?php
/**
 * Created by PhpStorm.
 * User: Alexey
 * Date: 09.01.2015
 * Time: 17:39
 */

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Sale\Internals\Input;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\OrderPropsValueTable;

Loc::loadMessages(__FILE__);

/**
 * Class PropertyValueCollection
 * @package Bitrix\Sale
 */
class PropertyValueCollection extends PropertyValueCollectionBase
{
	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @return Entity\Result
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectNotFoundException
	 * @throws \Exception
	 */
	public function save()
	{
		$isChanged = $this->isChanged();

		/** @var Order $order */
		if (!$order = $this->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$result = parent::save();

		if ($order->getId() > 0 && $isChanged)
		{
			$registry = Registry::getInstance(static::getRegistryType());
			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();

			if ($result->isSuccess())
			{
				$orderHistory::addAction(
					'PROPERTY',
					$order->getId(),
					"PROPERTY_SAVED",
					null,
					null,
					array(),
					OrderHistory::SALE_ORDER_HISTORY_ACTION_LOG_LEVEL_1
				);
			}

			$orderHistory::collectEntityFields('PROPERTY', $order->getId());
		}

		return $result;
	}

	/**
	 * @param $values
	 * @throws Main\ObjectNotFoundException
	 */
	protected function callEventOnSalePropertyValueDeleted($values)
	{
		parent::callEventOnSalePropertyValueDeleted($values);

		/** @var Order $order */
		if (!$order = $this->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		if ($order->getId() > 0)
		{
			$registry = Registry::getInstance(static::getRegistryType());

			/** @var OrderHistory $orderHistory */
			$orderHistory = $registry->getOrderHistoryClassName();
			$orderHistory::addAction(
				'PROPERTY',
				$order->getId(),
				'PROPERTY_REMOVE',
				$values['ID'],
				null,
				array(
					"NAME" => $values['NAME'],
					"CODE" => $values['CODE'],
					"VALUE" => $values['VALUE'],
				)
			);
		}
	}

	/**
	 * @param $primary
	 * @return Entity\DeleteResult
	 */
	protected static function deleteInternal($primary)
	{
		return Internals\OrderPropsValueTable::delete($primary);
	}

	/**
	 * @param array $parameters
	 * @return Main\DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		return OrderPropsValueTable::getList($parameters);
	}
	/**
	 * @return void
	 */
	public static function initJs()
	{
		Input\Manager::initJs();
		\CJSCore::RegisterExt('SaleOrderProperties', array(
			'js'   => '/bitrix/js/sale/orderproperties.js',
			'lang' => '/bitrix/modules/sale/lang/'.LANGUAGE_ID.'/lib/propertyvaluecollection.php',
			'rel'  => array('input'),
		));
		\CJSCore::Init(array('SaleOrderProperties'));
	}

}
