<?php
namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Application;

/**
 * Class OrderProcessingTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OrderProcessing_Query query()
 * @method static EO_OrderProcessing_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_OrderProcessing_Result getById($id)
 * @method static EO_OrderProcessing_Result getList(array $parameters = [])
 * @method static EO_OrderProcessing_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_OrderProcessing createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_OrderProcessing_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_OrderProcessing wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_OrderProcessing_Collection wakeUpCollection($rows)
 */
class OrderProcessingTable extends DataManager
{
	protected array $orderProcessedCache = [];

	public static function getTableName(): string
	{
		return "b_sale_order_processing";
	}

	public static function getMap(): array
	{
		return [
			'ORDER_ID' => [
				'primary' => true,
				'data_type' => 'integer',
			],
			'PRODUCTS_ADDED' => [
				'data_type' => 'boolean',
				'values' => ['N','Y'],
			],
			'PRODUCTS_REMOVED' => [
				'data_type' => 'boolean',
				'values' => ['N','Y'],
			],
			'ORDER' => [
				'data_type' => "Bitrix\\Sale\\OrderTable",
				'reference' => ['=this.ORDER_ID' => 'ref.ID'],
			],
		];
	}

	/**
	 * Wether order was processed
	 *
	 * @param int $orderId
	 *
	 * @return bool
	 */
	public static function hasAddedProducts($orderId = 0): bool
	{
		$orderId = (int)$orderId;
		$row = static::getRow([
			'filter' => [
				'=ORDER_ID' => $orderId,
			],
		]);

		return ($row['PRODUCTS_ADDED'] ?? null) === 'Y';
	}

	/**
	 * Wether order was processed
	 *
	 * @param int $orderId
	 *
	 * @return bool
	 */
	public static function hasRemovedProducts($orderId = 0): bool
	{
		$orderId = (int)$orderId;
		$row = static::getRow([
			'filter' => [
				'=ORDER_ID' => $orderId,
			],
		]);

		return ($row['PRODUCTS_REMOVED'] ?? null) === 'Y';
	}

	/**
	 * Mark order as processed
	 *
	 * @param int $orderId
	 *
	 * @return void
	 */
	public static function markProductsAdded($orderId = 0): void
	{
		$orderId = (int)$orderId;
		if ($orderId <= 0)
		{
			return;
		}
		$row = static::getRow([
			'filter' => [
				'=ORDER_ID' => $orderId,
			],
		]);
		if ($row)
		{
			static::update($orderId, ['PRODUCTS_ADDED' => 'Y']);
		}
		else
		{
			static::add([
				'ORDER_ID' => $orderId,
				'PRODUCTS_ADDED' => 'Y',
			]);
		}
	}

	/**
	 * Mark orders as processed
	 *
	 * @param array $orderIds
	 *
	 * @return void
	 */
	public static function markProductsAddedByList(array $orderIds): void
	{
		Main\Type\Collection::normalizeArrayValuesByInt($orderIds);
		if (empty($orderIds))
		{
			return;
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$sqlUpdate = "UPDATE ". static::getTableName() ." SET PRODUCTS_ADDED = 'Y' WHERE ORDER_ID IN (".implode(',', $orderIds).")";
		$connection->queryExecute($sqlUpdate);
	}

	/**
	 * Mark order as processed
	 *
	 * @param int $orderId
	 *
	 * @return void
	 */
	public static function markProductsRemoved($orderId = 0): void
	{
		$orderId = (int)$orderId;
		if ($orderId <= 0)
		{
			return;
		}
		$row = static::getRow([
			'filter' => [
				'=ORDER_ID' => $orderId,
			],
		]);
		if ($row)
		{
			static::update($orderId, ['PRODUCTS_REMOVED' => 'Y']);
		}
		else
		{
			static::add([
				'ORDER_ID' => $orderId,
				'PRODUCTS_REMOVED' => 'Y',
			]);
		}
	}

	/**
	 * @param $orderId
	 *
	 * @return bool
	 */
	public static function deleteByOrderId($orderId)
	{
		$orderId = (int)$orderId;
		if ($orderId <= 0)
		{
			return false;
		}

		$con = \Bitrix\Main\Application::getConnection();
		$con->queryExecute("DELETE FROM ". static::getTableName() ." WHERE ORDER_ID=".$orderId);

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
		$connection->queryExecute($sql);
	}
}
