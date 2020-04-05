<?php
namespace Bitrix\Sale\Internals;
use \Bitrix\Main\Entity\DataManager as DataManager;
use \Bitrix\Main\Type\DateTime as DateTime;
use \Bitrix\Main\Application as Application;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);


class OrderProcessingTable extends DataManager
{
	protected $orderProcessedCache = array();

	public static function getTableName()
	{
		return "b_sale_order_processing";
	}

	public static function getMap()
	{
		return array(
			'ORDER_ID' => array(
				'primary' => true,
				'data_type' => 'integer',
			),
			'PRODUCTS_ADDED' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'PRODUCTS_REMOVED' =>array (
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'ORDER' => array(
				'data_type' => "Bitrix\\Sale\\OrderTable",
				'reference' => array('=this.ORDER_ID' => 'ref.ID')
			)
		);
	}

	/**
	 * Wether order was processed
	 *
	 * @param int $orderId
	 *
	 * @return bool
	 */
	public static function hasAddedProducts($orderId = 0)
	{
		$orderId = (int)$orderId;
		$iterator = static::getList(array(
			"filter" => array("ORDER_ID" => $orderId)
		));

		$row = $iterator->fetch();
		return $row &&  $row['PRODUCTS_ADDED'] == "Y";
	}

	/**
	 * Wether order was processed
	 *
	 * @param int $orderId
	 *
	 * @return bool|null
	 */
	public static function hasRemovedProducts($orderId = 0)
	{
		$orderId = (int)$orderId;
		$iterator = static::getList(array(
			"filter" => array("ORDER_ID" => $orderId)
		));

		$row = $iterator->fetch();
		return $row &&  $row['PRODUCTS_REMOVED'] == "Y";
	}

	/**
	 * Mark order as processed
	 *
	 * @param int $orderId
	 */
	public static function markProductsAdded($orderId = 0)
	{
		$orderId = (int)$orderId;
		$iterator = static::getList(array(
			"filter" => array("ORDER_ID" => $orderId)
		));
		if($row = $iterator->fetch())
		{
			static::update($orderId, array("PRODUCTS_ADDED" => 'Y'));
		}
		else
		{
			static::add(array("ORDER_ID" => $orderId, "PRODUCTS_ADDED" => 'Y'));
		}
	}

	/**
	 * Mark orders as processed
	 *
	 * @param array $orderIds
	 */
	public static function markProductsAddedByList(array $orderIds)
	{
		$preparedIds = array();
		foreach( $orderIds as $orderId)
		{
			if ((int)$orderId > 0)
				$preparedIds[] = (int)$orderId;
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$type = $connection->getType();
		if ($type == "mysql" && !empty($preparedIds))
		{
			$sqlUpdate = "UPDATE ". static::getTableName() ." SET PRODUCTS_ADDED = 'Y' WHERE ORDER_ID IN (".implode(',', $preparedIds).")";
			$connection->query($sqlUpdate);
		}
	}

	/**
	 * Mark order as processed
	 *
	 * @param int $orderId
	 */
	public static function markProductsRemoved($orderId = 0)
	{
		$orderId = (int)$orderId;
		$iterator = static::getList(array(
			"filter" => array("ORDER_ID" => $orderId)
		));
		if($row = $iterator->fetch())
		{
			static::update($orderId, array("PRODUCTS_REMOVED" => 'Y'));
		}
		else
		{
			static::add(array("ORDER_ID" => $orderId, "PRODUCTS_REMOVED" => 'Y'));
		}
	}

	/**
	 * @param $orderId
	 *
	 * @return bool
	 */
	public static function deleteByOrderId($orderId)
	{
		if((int)($orderId) <= 0)
			return false;

		$con = \Bitrix\Main\Application::getConnection();
		$con->queryExecute("DELETE FROM ". static::getTableName() ." WHERE ORDER_ID=".(int)($orderId));
		return true;
	}

	/**
	 * Clear table
	 *
	 */
	public static function clear()
	{
		$connection = Application::getConnection();
		$sql = "DELETE FROM " . static::getTableName() . "
				WHERE ORDER_ID NOT IN (SELECT ID FROM b_sale_order)";
		$connection->query($sql);
	}
}

?>