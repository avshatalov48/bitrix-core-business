<?php
namespace Bitrix\Sale\Archive;

use Bitrix\Main,
	Bitrix\Sale,
	Bitrix\Main\Config\Option,
	Bitrix\Sale\Internals,
	Bitrix\Main\Type,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class manages of orders's archiving, restoring entries from database
 *
 * @package Bitrix\Sale\Archive
 */
class Manager
{
	const SALE_ARCHIVE_VERSION = 2;

	/**
	 * @return array
	 */
	public static function getOrderFieldNames()
	{
		return array(
			"ACCOUNT_NUMBER", "USER_ID", "PRICE", "SUM_PAID", "CURRENCY", "STATUS_ID", "PAYED", "DEDUCTED", "CANCELED",
			"LID", "PERSON_TYPE_ID", "XML_ID", "ID_1C", "DATE_INSERT", "RESPONSIBLE_ID", "COMPANY_ID"
		);
	}

	/**
	 * @return array
	 */
	public static function getBasketFieldNames()
	{
		return array(
			"PRODUCT_ID", "PRODUCT_PRICE_ID", "NAME", "PRICE", "MODULE", "QUANTITY", "WEIGHT", "DATE_INSERT",
			"CURRENCY", "PRODUCT_XML_ID", "MEASURE_NAME", "TYPE", "SET_PARENT_ID", "MEASURE_CODE", "BASKET_DATA"
		);
	}

	/**
	 * Archive orders by filter
	 *
	 * @param array $filter			Filter the selection.
	 * @param int $limit		Limit the selection orders.
	 * @param int $timeExecution		Limits the maximum execution time.
	 *
	 * @return Sale\Result $result
	 *
	 * @throws \Exception
	 */
	public static function archiveOrders($filter = array(), $limit = null, $timeExecution = null)
	{
		$result = new Sale\Result();
		$countArchived = 0;

		if ((int)$timeExecution)
		{
			@set_time_limit(0);
		}

		$params["filter"] = $filter;
		$params["order"] = array('ID' => "ASC");
		if ((int)$limit)
		{
			$params["limit"] = (int)$limit;
		}

		$orderArchiveCollection = new Process\OrderArchiveCollection();
		$fillResult = $orderArchiveCollection->loadFromDB($params);
		if ($fillResult->hasWarnings())
		{
			return $fillResult;
		}

		/** @var Process\OrderArchiveItem $item */
		foreach ($orderArchiveCollection as $index => $item)
		{
			$resultArchiving = $item->archive();
			if ($resultArchiving->isSuccess())
			{
				$countArchived++;
				$orderArchiveCollection->deleteItem($index);
			}
			else
			{
				$errorMessages = $resultArchiving->getErrorMessages();
				foreach ($errorMessages as $error)
				{
					$result->addError(new Main\Error(Loc::getMessage("ARCHIVE_ERROR_ORDER_MESSAGE", array("#ID#" => $item->getId())).": ".$error));
				}
			}

			if ((int)$timeExecution && (getmicrotime() - START_EXEC_TIME > $timeExecution))
			{
				break;
			}
		}

		$result->setData(array("count" => $countArchived));
		return $result;
	}

	/**
	 * Archive orders that are selected by module's settings.
	 * 
	 * Used in agents.
	 * 
	 * @param int $limit		Limit the selection orders.
	 * @param int $timeExecution		Limits the maximum execution time.
	 *
	 * @return Sale\Result
	 *
	 * @throws Main\SystemException
	 */
	public static function archiveByOptions($limit = null, $timeExecution = null)
	{
		$filter = Option::get('sale', 'archive_params');

		if ($filter == '')
		{
			throw new Main\SystemException("Settings of order's archiving are null or empty");
		}

		$filter = unserialize($filter);

		if (isset($filter['PERIOD']))
		{
			if ((int)$filter['PERIOD'] > 0)
			{
				$date = new Type\DateTime();
				$latestDate = $date->add('-'.(int)$filter['PERIOD'].' day');
				$filter['<=DATE_INSERT'] = $latestDate;
			}

			unset($filter['PERIOD']);
		}

		return static::archiveOrders($filter, $limit, $timeExecution);
	}

	/**
	 * Used in agents. Manage execution of agent.
	 *
	 * @param int $limit		Limit the selection orders.
	 * @param int $maxTime		Maximum execution time of agent.
	 *
	 * @return string
	 *
	 * @throws Main\ArgumentNullException
	 */
	public static function archiveOnAgent($limit, $maxTime = null)
	{
		global $USER;
		$agentId = null;

		$limit = (int)$limit ? (int)$limit : 10;
		$maxTime = (int)$maxTime ? (int)$maxTime : null;

		$agentsList = \CAgent::GetList(array("ID"=>"DESC"), array(
			"MODULE_ID" => "sale",
			"NAME" => "\\Bitrix\\Sale\\Archive\\Manager::archiveOnAgent(%",
		));
		while($agent = $agentsList->Fetch())
		{
			$agentId = $agent["ID"];
		}

		if ($agentId)
		{
			if (!(isset($USER) && $USER instanceof \CUser))
			{
				$USER = new \CUser();
			}

			$result = static::archiveByOptions($limit, $maxTime);

			$resultData = $result->getData();
			if ($resultData['count'])
			{
				\CAgent::Update($agentId, array("AGENT_INTERVAL" => 60*5));

			}
			else
			{
				\CAgent::Update($agentId, array("AGENT_INTERVAL" => 24*60*60));
			}
		}
		else
		{
			\CAgent::AddAgent("\\Bitrix\\Sale\\Archive\\Manager::archiveOnAgent(".$limit.",".$maxTime.");", "sale", "N", 24*60*60, "", "Y");
		}

		return "\\Bitrix\\Sale\\Archive\\Manager::archiveOnAgent(".$limit.",".$maxTime.");";
	}

	/**
	 * @param array $parameters
	 *
	 * @return Main\DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		return Internals\OrderArchiveTable::getList($parameters);
	}

	/**
	 * Get entry of order from archive by entry's id.
	 *
	 * @param int $id
	 *
	 * @return Main\DB\Result
	 */
	public static function getById($id)
	{
		return Internals\OrderArchiveTable::getById($id);
	}

	/**
	 * Get entries of basket items from archive.
	 * 
	 * @param array $parameters
	 *
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getBasketList(array $parameters = array())
	{
		return Internals\BasketArchiveTable::getList($parameters);
	}

	/**
	 * Get entry of basket item from archive by id.
	 * 
	 * @param int $id
	 *
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getBasketItemById($id)
	{
		return Internals\BasketArchiveTable::getById($id);
	}

	/**
	 * Delete archived order with archived basket items.
	 * 
	 * @param int $id
	 *
	 * @return Main\Entity\DeleteResult
	 * @throws \Exception
	 */
	public static function delete($id)
	{
		$basketItems = static::getBasketList(
			array(
				"filter" => array("ARCHIVE_ID" => $id),
				"select" => array("ID")
			)
		);
		while ($item = $basketItems->fetch())
		{
			Internals\BasketArchiveTable::delete($item['ID']);
		}

		return Internals\OrderArchiveTable::delete($id);
	}

	/**
	 * Return Archive\Order object restored from archive
	 *
	 * @param int $id		Entity's id.
	 *
	 * @return Sale\Order
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\ArgumentNullException
	 */
	public static function returnArchivedOrder($id)
	{
		$id = (int)$id;
		if ($id <= 0)
			throw new Main\ArgumentNullException("id");

		$restorer = Recovery\Restorer::load($id);
		if (!$restorer)
		{
			return null;
		}

		return $restorer->restoreOrder();
	}
}
