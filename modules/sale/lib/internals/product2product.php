<?php
namespace Bitrix\Sale\Internals;

use \Bitrix\Main;
use \Bitrix\Main\Config;
use \Bitrix\Sale;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class Product2ProductTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Product2Product_Query query()
 * @method static EO_Product2Product_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Product2Product_Result getById($id)
 * @method static EO_Product2Product_Result getList(array $parameters = array())
 * @method static EO_Product2Product_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_Product2Product createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_Product2Product_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_Product2Product wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_Product2Product_Collection wakeUpCollection($rows)
 */
class Product2ProductTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return "b_sale_product2product";
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'PRODUCT_ID' => array(
				'data_type' => 'integer'
			),
			'PARENT_PRODUCT_ID' => array(
				'data_type' => 'integer'
			),
			'CNT' => array(
				'data_type' => 'integer'
			)
		);
	}

	/**
	 *
	 * Remove old products from b_sale_product2product table.
	 * Used in agents.
	 *
	 * @param int $liveTime in days
	 *
	 * @return string
	 */
	public static function deleteOldProducts($liveTime = 10)
	{
		$liveTime = (int)$liveTime;
		$connection = Main\Application::getConnection();
		$type = $connection->getType();
		$helper = $connection->getSqlHelper();
		$liveTo = $helper->addSecondsToDateTime($liveTime * 24 * 3600, "o.DATE_INSERT");
		$now = $helper->getCurrentDateTimeFunction();

		// Update existing
		switch ($type)
		{
			case 'mysql':
				if ($connection->isTableExists('b_sale_order_product_stat'))
				{
					$liveTo = $helper->addSecondsToDateTime($liveTime * 24 * 3600, "ORDER_DATE");
					$sqlDelete = "DELETE FROM b_sale_order_product_stat WHERE $now > $liveTo";
					$connection->query($sqlDelete);
					$connection->query("TRUNCATE TABLE b_sale_product2product");
					$sqlUpdate = "INSERT INTO b_sale_product2product(PRODUCT_ID, PARENT_PRODUCT_ID, CNT) 
						SELECT ops.PRODUCT_ID, ops.RELATED_PRODUCT_ID, SUM(ops.CNT)
						FROM b_sale_order_product_stat ops
						GROUP BY PRODUCT_ID, RELATED_PRODUCT_ID
						ORDER BY NULL";
					$connection->query($sqlUpdate);
					unset($sqlUpdate);
				}
				break;
			case 'mssql':
				$sqlUpdate = "UPDATE b_sale_product2product
					SET  CNT = CNT - 1
					FROM b_sale_product2product p2p, b_sale_basket b, b_sale_basket b1, b_sale_order o, b_sale_order_processing op
					WHERE b.ORDER_ID = b1.ORDER_ID AND
					b.ID <> b1.ID AND
					$now > $liveTo AND
					o.ID = b.ORDER_ID AND
					o.ID = op.ORDER_ID AND
					op.PRODUCTS_REMOVED = 'N' AND
					p2p.PRODUCT_ID = b.PRODUCT_ID AND
					p2p.PARENT_PRODUCT_ID = b1.PRODUCT_ID";
				$connection->query($sqlUpdate);
				unset($sqlUpdate);
				break;
			case 'oracle':
				$sqlUpdate = "UPDATE b_sale_product2product
					SET CNT = CNT - 1
					WHERE ID IN (
						SELECT p2p.ID FROM b_sale_product2product p2p, b_sale_basket b, b_sale_basket b1, b_sale_order o, b_sale_order_processing op
						WHERE b.ORDER_ID = b1.ORDER_ID AND
						b.ID <> b1.ID AND
						$now > $liveTo AND
						o.ID = b.ORDER_ID AND
						o.ID = op.ORDER_ID AND
						op.PRODUCTS_REMOVED = 'N' AND
						p2p.PRODUCT_ID = b.PRODUCT_ID AND
						p2p.PARENT_PRODUCT_ID = b1.PRODUCT_ID
					)";
				$connection->query($sqlUpdate);
				unset($sqlUpdate);
				break;
			default:
				break;
		}

		// @deprecated update status, stayed for compatibility
		$updateRemStatusSql = "UPDATE b_sale_order_processing SET PRODUCTS_REMOVED = 'Y'";
		$connection->query($updateRemStatusSql);

		if ($type !== "mysql")
		{
			// Delete
			$deleteSql = "DELETE FROM b_sale_product2product WHERE CNT <= 0";
			$connection->query($deleteSql);
		}

		return "\\Bitrix\\Sale\\Product2ProductTable::deleteOldProducts(".$liveTime.");";
	}

	/**
	 * Refresh order statistic
	 *
	 * @param $liveTime.			Counting statistic period in days
	 *
	 * @return Main\DB\Result
	 */
	public static function refreshProductStatistic($liveTime = 10)
	{
		$liveTime = (int)$liveTime;
		$connection = Main\Application::getConnection();

		if (!$connection->isTableExists('b_sale_order_product_stat'))
			return;

		$sqlDelete = "TRUNCATE TABLE b_sale_order_product_stat";
		$connection->query($sqlDelete);
		$dateLimit = "";
		if ($liveTime > 0)
		{
			$helper = $connection->getSqlHelper();
			$liveTo = $helper->addSecondsToDateTime($liveTime * 24 * 3600, "b.DATE_INSERT");
			$dateLimit = " AND NOW() < $liveTo";
		}
		$sqlUpdate = "
			INSERT INTO b_sale_order_product_stat (PRODUCT_ID, RELATED_PRODUCT_ID, ORDER_DATE, CNT) 
				SELECT b.PRODUCT_ID as PRODUCT_ID, b1.PRODUCT_ID as RELATED_PRODUCT_ID, DATE(b.DATE_INSERT) as ORDER_DATE, COUNT(b.PRODUCT_ID)
				FROM b_sale_basket b, b_sale_basket b1
				WHERE b.ORDER_ID = b1.ORDER_ID 
					AND	b.ID <> b1.ID
					$dateLimit 
  				GROUP BY b.PRODUCT_ID, b1.PRODUCT_ID, ORDER_DATE
  				ORDER BY NULL";
		$connection->query($sqlUpdate);

		$sqlDelete = "TRUNCATE TABLE b_sale_product2product";
		$connection->query($sqlDelete);
		$sqlUpdate = "
				INSERT INTO b_sale_product2product (PRODUCT_ID, PARENT_PRODUCT_ID, CNT) 
					SELECT ops.PRODUCT_ID, ops.RELATED_PRODUCT_ID, SUM(ops.CNT)
					FROM b_sale_order_product_stat ops
					GROUP BY PRODUCT_ID, RELATED_PRODUCT_ID
					ORDER BY NULL
			";

		return $connection->query($sqlUpdate);
	}

	/**
	 * Add products from order or updates existing.
	 *
	 * @param $orderId
	 *
	 * @return void
	 */
	public static function addProductsFromOrder($orderId = 0)
	{
		$orderId = (int)$orderId;

		if (Sale\OrderProcessingTable::hasAddedProducts($orderId))
			return;

		$connection = Main\Application::getConnection();
		$type = $connection->getType();

		// Update existing
		if ($type == "mysql" && $connection->isTableExists('b_sale_order_product_stat'))
		{
			$sqlUpdate = "
				INSERT INTO b_sale_order_product_stat (PRODUCT_ID, RELATED_PRODUCT_ID, ORDER_DATE) 
				SELECT b.PRODUCT_ID, b1.PRODUCT_ID, DATE(b.DATE_INSERT)
				FROM b_sale_basket b, b_sale_basket b1
				WHERE b.ORDER_ID = b1.ORDER_ID AND
					b.ORDER_ID = $orderId AND
					b.ID <> b1.ID 
  				ON DUPLICATE KEY UPDATE  CNT = CNT + 1;
			";
			$connection->query($sqlUpdate);

			$sqlUpdate = "UPDATE b_sale_product2product p2p, b_sale_basket b, b_sale_basket b1
				SET  p2p.CNT = p2p.CNT + 1
				WHERE b.ORDER_ID = b1.ORDER_ID AND
					b.ID <> b1.ID AND
					b.ORDER_ID = $orderId AND
					p2p.PRODUCT_ID = b.PRODUCT_ID AND
					p2p.PARENT_PRODUCT_ID = b1.PRODUCT_ID";
		}
		elseif ($type == "mssql")
		{
			$sqlUpdate = "UPDATE b_sale_product2product
				SET CNT = CNT + 1
				FROM b_sale_product2product p2p, b_sale_basket b, b_sale_basket b1
				WHERE b.ORDER_ID = b1.ORDER_ID AND
					b.ID <> b1.ID AND
					b.ORDER_ID = $orderId AND
					p2p.PRODUCT_ID = b.PRODUCT_ID AND
					p2p.PARENT_PRODUCT_ID = b1.PRODUCT_ID";
		}
		else // Oracle
		{
			$sqlUpdate = "UPDATE b_sale_product2product
				SET CNT = CNT + 1
				WHERE ID IN (
					SELECT p2p.ID FROM b_sale_product2product p2p, b_sale_basket b, b_sale_basket b1
					WHERE b.ORDER_ID = b1.ORDER_ID AND
						b.ID <> b1.ID AND
						b.ORDER_ID = $orderId AND
						p2p.PRODUCT_ID = b.PRODUCT_ID AND
						p2p.PARENT_PRODUCT_ID = b1.PRODUCT_ID
					)";
		}

		$connection->query($sqlUpdate);

		// Insert new
		$sqlInsert = "INSERT INTO b_sale_product2product (PRODUCT_ID, PARENT_PRODUCT_ID, CNT)
			SELECT b.PRODUCT_ID, b1.PRODUCT_ID, 1
			FROM b_sale_basket b, b_sale_basket b1
			WHERE b.ORDER_ID = b1.ORDER_ID AND
				b.ORDER_ID = $orderId AND
				b.ID <> b1.ID AND
				NOT EXISTS (SELECT 1 FROM b_sale_product2product d WHERE d.PRODUCT_ID = b.PRODUCT_ID AND d.PARENT_PRODUCT_ID = b1.PRODUCT_ID)";

		$connection->query($sqlInsert);

		Sale\OrderProcessingTable::markProductsAdded($orderId);

		if (defined("BX_COMP_MANAGED_CACHE"))
		{
			$app = Main\Application::getInstance();
			$app->getTaggedCache()->clearByTag('sale_product_buy');
		}
	}

	/**
	 * Add products from order by an agent
	 *
	 * @param int $limit			Count of orders is added per hit.
	 * @return string
	 */
	public static function addProductsByAgent($limit = 100)
	{
		$limit = (int)$limit;
		$connection = Main\Application::getConnection();
		$type = $connection->getType();
		$isTableExists = $connection->isTableExists('b_sale_order_product_stat');
		if ($type == "mysql" && $isTableExists)
		{
			$params = array(
				"filter" => array("PRODUCTS_ADDED" => 'N'),
				"select" => array("ORDER_ID")
			);

			if ($limit > 0)
			{
				$params['limit'] = $limit;
			}

			$orderIds = array();
			$processingData = Sale\OrderProcessingTable::getList($params);
			while ($processingOrder = $processingData->fetch())
			{
				$orderIds[] = (int)$processingOrder['ORDER_ID'];
			}

			if (!empty($orderIds))
			{
				$sqlOrderIds = implode(',', $orderIds);
				Sale\OrderProcessingTable::markProductsAddedByList($orderIds);

				$sqlInsert = "
				INSERT INTO b_sale_order_product_stat (CNT, PRODUCT_ID, RELATED_PRODUCT_ID, ORDER_DATE) 
					SELECT SUMM, PRODUCT_ID, PARENT_PRODUCT_ID, TODAY 
					FROM (
						SELECT COUNT(1) as SUMM,
							b.PRODUCT_ID  as PRODUCT_ID, 
							b1.PRODUCT_ID as PARENT_PRODUCT_ID,
							CURDATE() as TODAY
						FROM b_sale_basket b, b_sale_basket b1
						WHERE 
							b1.ORDER_ID = b.ORDER_ID
							AND b1.ID <> b.ID
							AND b.ORDER_ID IN ($sqlOrderIds)
						GROUP BY b.PRODUCT_ID, b1.PRODUCT_ID
						ORDER BY NULL
					) cacl
				ON DUPLICATE KEY UPDATE CNT = CNT + cacl.SUMM;";
				$connection->query($sqlInsert);

				$sqlUpdate = "
				UPDATE b_sale_product2product p2p, 
					( 
						SELECT COUNT(1) as CNT,
							b.PRODUCT_ID  as PRODUCT_ID, 
							b1.PRODUCT_ID as PARENT_PRODUCT_ID
						FROM b_sale_basket b, b_sale_basket b1
						WHERE 
							b1.ORDER_ID = b.ORDER_ID
							AND b1.ID <> b.ID
							AND b.ORDER_ID IN ($sqlOrderIds)
						GROUP BY b.PRODUCT_ID, b1.PRODUCT_ID
						ORDER BY NULL
					) calc
				SET  p2p.CNT = p2p.CNT + calc.CNT
				WHERE p2p.PRODUCT_ID = calc.PRODUCT_ID AND	p2p.PARENT_PRODUCT_ID = calc.PARENT_PRODUCT_ID";

				$connection->query($sqlUpdate);

				$sqlInsert = "
				INSERT INTO b_sale_product2product (PRODUCT_ID, PARENT_PRODUCT_ID, CNT)
				SELECT b.PRODUCT_ID, b1.PRODUCT_ID, 1
				FROM b_sale_basket b, b_sale_basket b1
				WHERE b.ORDER_ID = b1.ORDER_ID AND
					b.ORDER_ID IN ($sqlOrderIds) AND
					b.ID <> b1.ID AND
					NOT EXISTS (SELECT 1 FROM b_sale_product2product d WHERE d.PRODUCT_ID = b.PRODUCT_ID AND d.PARENT_PRODUCT_ID = b1.PRODUCT_ID)";
				$connection->query($sqlInsert);

				if (defined("BX_COMP_MANAGED_CACHE"))
				{
					$app = Main\Application::getInstance();
					$app->getTaggedCache()->clearByTag('sale_product_buy');
				}
			}
		}

		$agentName = "\\Bitrix\\Sale\\Product2ProductTable::addProductsByAgent($limit);";
		$agentData = \CAgent::GetList(array(), array("NAME" => $agentName, "MODULE_ID" => "sale"));
		$agent = $agentData->Fetch();

		$processingData = Sale\OrderProcessingTable::getList(
			array(
				"filter" => array("PRODUCTS_ADDED" => 'N')
			)
		);

		if ($processingData->fetch())
		{
			if ($isTableExists && $agent['ID'] && $agent['ID'] > 60)
			{
				\CAgent::Delete($agent["ID"]);
				\CAgent::AddAgent("Bitrix\\Sale\\Product2ProductTable::addProductsByAgent($limit);", "sale", "N", 60, "", "Y");
			}
		}
		else
		{
			if ($agent['ID'])
			{
				\CAgent::Update($agent["ID"], array("AGENT_INTERVAL" => 60*60*24));
			}
		}

		return $agentName;
	}

	/**
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 */
	public static function onSaleOrderAddEvent(Main\Event $event)
	{
		$order = $event->getParameter('ENTITY');
		$isNew = $event->getParameter('IS_NEW');
		if ((!$order instanceof Sale\Order))
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_PRODUCT2PRODUCT_WRONG_ORDER'), 'SALE_EVENT_PRODUCT2PRODUCT_ON_SALE_ORDER_ADD_WRONG_ORDER'),
				'sale'
			);
		}

		$basket = $order->getBasket();

		if ($isNew && ($basket && count($basket) > 0))
		{
			static::onSaleOrderAdd($order->getId());
		}

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}

	/**
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 */
	public static function onSaleStatusOrderHandlerEvent(Main\Event $event)
	{
		$order = $event->getParameter('ENTITY');
		$value = $event->getParameter('VALUE');
		if ((!$order instanceof Sale\Order))
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_PRODUCT2PRODUCT_WRONG_ORDER'), 'SALE_EVENT_PRODUCT2PRODUCT_ON_SALE_ORDER_STATUS_WRONG_ORDER'),
				'sale'
			);
		}

		static::onSaleStatusOrderHandler($order->getId(), $value);

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}

	/**
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 */
	public static function onSaleDeliveryOrderHandlerEvent(Main\Event $event)
	{
		$shipment = $event->getParameter('ENTITY');
		if ((!$shipment instanceof Sale\Shipment))
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_PRODUCT2PRODUCT_WRONG_SHIPMENT'), 'SALE_EVENT_PRODUCT2PRODUCT_ON_SALE_DELIVERY_ORDER_WRONG_SHIPMENT'),
				'sale'
			);
		}

		if (!$shipmentCollection = $shipment->getCollection())
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_PRODUCT2PRODUCT_WRONG_SHIPMENTCOLLECTION'), 'SALE_EVENT_PRODUCT2PRODUCT_ON_SALE_DELIVERY_ORDER_WRONG_SHIPMENTCOLLECTION'),
				'sale'
			);

		}

		if (!$order = $shipmentCollection->getOrder())
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_PRODUCT2PRODUCT_WRONG_ORDER'), 'SALE_EVENT_PRODUCT2PRODUCT_ON_SALE_DELIVERY_ORDER_WRONG_ORDER'),
				'sale'
			);

		}

		static::onSaleDeliveryOrderHandler($order->getId(), $order->isAllowDelivery() ? 'Y' : 'N');

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}

	/**
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 */
	public static function onSaleDeductOrderHandlerEvent(Main\Event $event)
	{
		$shipment = $event->getParameter('ENTITY');
		if ((!$shipment instanceof Sale\Shipment))
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_PRODUCT2PRODUCT_WRONG_SHIPMENT'), 'SALE_EVENT_PRODUCT2PRODUCT_ON_SALE_DEDUCT_ORDER_WRONG_SHIPMENT'),
				'sale'
			);
		}

		if (!$shipmentCollection = $shipment->getCollection())
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_PRODUCT2PRODUCT_WRONG_SHIPMENTCOLLECTION'), 'SALE_EVENT_PRODUCT2PRODUCT_ON_SALE_DEDUCT_ORDER_WRONG_SHIPMENTCOLLECTION'),
				'sale'
			);

		}

		if (!$order = $shipmentCollection->getOrder())
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_PRODUCT2PRODUCT_WRONG_ORDER'), 'SALE_EVENT_PRODUCT2PRODUCT_ON_SALE_DEDUCT_ORDER_WRONG_ORDER'),
				'sale'
			);

		}


		static::onSaleDeductOrderHandler($order->getId(), $order->isShipped() ? 'Y' : 'N');

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}

	/**
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 */
	public static function onSaleCancelOrderHandlerEvent(Main\Event $event)
	{
		$order = $event->getParameter('ENTITY');
		if ((!$order instanceof Sale\Order))
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_PRODUCT2PRODUCT_WRONG_ORDER'), 'SALE_EVENT_PRODUCT2PRODUCT_ON_SALE_CANCELED_ORDER_WRONG_ORDER'),
				'sale'
			);
		}

		static::onSaleCancelOrderHandler($order->getId(), $order->isCanceled() ? 'Y' : 'N');

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}

	/**
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 */
	public static function onSalePayOrderHandlerEvent(Main\Event $event)
	{
		$order = $event->getParameter('ENTITY');
		if ((!$order instanceof Sale\Order))
		{
			return new Main\EventResult(
				Main\EventResult::ERROR,
				new Sale\ResultError(Main\Localization\Loc::getMessage('SALE_EVENT_PRODUCT2PRODUCT_WRONG_ORDER'), 'SALE_EVENT_PRODUCT2PRODUCT_ON_SALE_PAID_ORDER_WRONG_ORDER'),
				'sale'
			);
		}

		static::onSaleCancelOrderHandler($order->getId(), $order->isPaid() ? 'Y' : 'N');

		return new Main\EventResult( Main\EventResult::SUCCESS, null, 'sale');
	}

	/**
	 * Executes when order status added.
	 *
	 * @param $orderId
	 * @return void
	 */
	public static function onSaleOrderAdd($orderId)
	{
		$statusName = "N";
		static::addOrderProcessing($orderId, $statusName);
	}

	/**
	 * Executes when order status has changed.
	 *
	 * @param $orderId
	 * @param $status
	 * @return void
	 */
	public static function onSaleStatusOrderHandler($orderId, $status)
	{
		static::addOrderProcessing($orderId, $status);
	}

	/**
	 * Executes when order status Delivered.
	 *
	 * @param $orderId
	 * @param $status
	 * @return void
	 */
	public static function onSaleDeliveryOrderHandler($orderId, $status)
	{
		if ($status == 'Y')
		{
			$statusName = "F_DELIVERY";
			static::addOrderProcessing($orderId, $statusName);
		}
	}

	/**
	 * Executes when order status has deducted.
	 *
	 * @param $orderId
	 * @param $status
	 * @return void
	 */
	public static function onSaleDeductOrderHandler($orderId, $status)
	{
		if ($status == 'Y')
		{
			$statusName = "F_OUT";
			static::addOrderProcessing($orderId, $statusName);
		}
	}

	/**
	 * Executes when order status has canceled.
	 *
	 * @param $orderId
	 * @param $status
	 * @return void
	 */
	public static function onSaleCancelOrderHandler($orderId, $status)
	{
		if ($status == 'Y')
		{
			$statusName = "F_CANCELED";
			static::addOrderProcessing($orderId, $statusName);
		}
	}

	/**
	 * Executes when order status has canceled.
	 *
	 * @param $orderId
	 * @param $status
	 * @return void
	 */
	public static function onSalePayOrderHandler($orderId, $status)
	{
		if ($status == 'Y')
		{
			$statusName = "F_PAY";
			static::addOrderProcessing($orderId, $statusName);
		}
	}

	/**
	 * Add order id in order processing table.
	 *
	 * @param $orderId
	 * @param $statusName.		Handler status name.
	 * @return void
	 */
	protected static function addOrderProcessing($orderId, $statusName)
	{
		$allowStatuses = Config\Option::get("sale", "p2p_status_list", "");
		$allowCollecting = Config\Option::get("sale", "p2p_allow_collect_data");
		if ($allowStatuses != '')
			$allowStatuses = unserialize($allowStatuses, ['allowed_classes' => false]);
		else
			$allowStatuses = array();

		if ($allowCollecting == "Y" && !empty($allowStatuses) && is_array($allowStatuses) && in_array($statusName, $allowStatuses))
		{
			$orderInformation = Sale\OrderProcessingTable::getList(
				array(
					"filter" => array("ORDER_ID" => (int)$orderId),
					"limit" => 1
				)
			);
			$result = $orderInformation->fetch();
			if (!$result)
				Sale\OrderProcessingTable::add(array("ORDER_ID" => (int)$orderId));
		}
	}
}