<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seo
 * @copyright 2001-2013 Bitrix
 */
namespace Bitrix\Seo\Retargeting\Internals;

use Bitrix\Main\Application;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Type\DateTime;
use Bitrix\Seo\Retargeting\Service;

Loc::loadMessages(__FILE__);

class QueueTable extends Entity\DataManager
{
	const MODULE_ID = 'seo';
	const PORTION_QUANTITY = 50;
	const ACTION_IMPORT = 'IMP';
	const ACTION_IMPORT_AND_AUTO_REMOVE = 'IAR';
	const ACTION_REMOVE = 'REM';
	const ACTION_AUTO_REMOVE = 'ARM';

	protected static $isAgentAdded = array();

	public static function getTableName()
	{
		return 'b_seo_service_rtg_queue';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime()
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'ACCOUNT_ID' => array(
				'data_type' => 'string',
			),
			'AUDIENCE_ID' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'CONTACT_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'VALUE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'ACTION' => array(
				'data_type' => 'enum',
				'values' => array(
					self::ACTION_IMPORT,
					self::ACTION_REMOVE,
					self::ACTION_IMPORT_AND_AUTO_REMOVE,
					self::ACTION_AUTO_REMOVE,
				),
				'default_value' => self::ACTION_IMPORT_AND_AUTO_REMOVE,
				'required' => true,
			),
			'DATE_AUTO_REMOVE' => array(
				'data_type' => 'datetime',
			)
		);

		return $fieldsMap;
	}

	protected static function processQueueAutoRemoveAgentName()
	{
		return __CLASS__ . '::processQueueAutoRemoveAgent();';
	}

	protected static function getProcessQueueAgentName($type)
	{
		return __CLASS__ . '::processQueueAgent("' . $type . '");';
	}

	public static function processQueueAutoRemoveAgent()
	{
		$queueDb = static::getList(array(
			'select' => array('ID'),
			'filter' => array('=ACTION' => self::ACTION_AUTO_REMOVE),
			'limit' => 1
		));
		if ($queueDb->fetch())
		{
			$connection = Application::getConnection();
			$sql = "UPDATE " . self::getTableName() . " " .
				"SET ACTION='" . self::ACTION_REMOVE . "' " .
				"WHERE ACTION='" . self::ACTION_AUTO_REMOVE . "' " .
				"AND DATE_AUTO_REMOVE <= " . $connection->getSqlHelper()->getCurrentDateTimeFunction();
			$connection->query($sql);
			if ($connection->getAffectedRowsCount() > 0)
			{
				$types = Service::getTypes();
				foreach ($types as $type)
				{
					static::addQueueAgent($type);
				}
			}

			return static::processQueueAutoRemoveAgentName();
		}
		else
		{
			return '';
		}
	}

	public static function processQueueAgent($type)
	{
		try
		{
			$hasQueue = static::processQueue($type);
		}
		catch(\Exception $e)
		{
			$hasQueue = false;
		}

		if (!$hasQueue)
		{
			return '';
		}
		else
		{
			return static::getProcessQueueAgentName($type);
		}
	}

	protected static function processQueue($type)
	{
		$hasQueue = false;
		$queryData = array();

		$audience = Service::getAudience($type);
		$maxQuantity = $audience->getMaxContactsPerPacket();
		$maxQuantity = $maxQuantity > 1000 ? 1000 : $maxQuantity;
		$queueDb = static::getList(array(
			'filter' => array(
				'=TYPE' => $type,
				'=ACTION' => array(
					self::ACTION_IMPORT,
					self::ACTION_REMOVE,
					self::ACTION_IMPORT_AND_AUTO_REMOVE,
				)
			),
			'limit' => $maxQuantity
		));
		while ($queueItem = $queueDb->fetch())
		{
			$hasQueue = true;

			$isRemove = $queueItem['ACTION'] == self::ACTION_REMOVE ? 'Y' : 'N';
			$queryId = $queueItem['TYPE'];
			$queryId .= '_' . $queueItem['ACCOUNT_ID'];
			$queryId .= '_' . $queueItem['AUDIENCE_ID'];
			$queryId .= '_' . $isRemove;

			if (!isset($queryData[$queryId]))
			{
				$queryData[$queryId] = array(
					'ACCOUNT_ID' => $queueItem['ACCOUNT_ID'],
					'AUDIENCE_ID' => $queueItem['AUDIENCE_ID'],
					'IS_REMOVE' => $isRemove,
					'CONTACTS' => array(),
					'DELETE_ID_LIST' => array(),
					'AUTO_REMOVE_ID_LIST' => array(),
				);
			}
			$contactType = $queueItem['CONTACT_TYPE'];
			if (!isset($queryData[$queryId]['CONTACTS'][$contactType]))
			{
				$queryData[$queryId]['CONTACTS'][$contactType] = array();
			}

			$queryData[$queryId]['CONTACTS'][$contactType][] = $queueItem['VALUE'];

			if ($queueItem['ACTION'] == self::ACTION_IMPORT_AND_AUTO_REMOVE)
			{
				$queryData[$queryId]['AUTO_REMOVE_ID_LIST'][] = $queueItem['ID'];
			}
			else
			{
				$queryData[$queryId]['DELETE_ID_LIST'][] = $queueItem['ID'];
			}
		}

		foreach ($queryData as $queryId => $query)
		{
			foreach ($query['CONTACTS'] as $contactType => $contacts)
			{
				$query['CONTACTS'][$contactType] = array_unique($contacts);
			}

			$audience->disableQueueMode();
			$audience->setAccountId($query['ACCOUNT_ID']);

			$contactTypes = $audience->isSupportMultiTypeContacts() ? array('') : array_keys($query['CONTACTS']);
			foreach ($contactTypes as $contactType)
			{
				if ($query['IS_REMOVE'] != 'Y')
				{
					$audienceImportResult = $audience->addContacts(
						$query['AUDIENCE_ID'],
						$query['CONTACTS'],
						array(
							'type' => $contactType
						)
					);
				}
				else
				{
					$audienceImportResult = $audience->deleteContacts(
						$query['AUDIENCE_ID'],
						$query['CONTACTS'],
						array(
							'type' => $contactType
						)
					);
				}

				if ($audienceImportResult->isSuccess())
				{
					if (!empty($query['DELETE_ID_LIST']))
					{
						$portions = self::divideListIntoPortions($query['DELETE_ID_LIST']);
						foreach ($portions as $portion)
						{
							Application::getConnection()->query(
								"DELETE FROM " . self::getTableName() . " WHERE ID IN (" . implode(',', $portion) . ")"
							);
						}
					}

					if (!empty($query['AUTO_REMOVE_ID_LIST']))
					{
						$portions = self::divideListIntoPortions($query['AUTO_REMOVE_ID_LIST']);
						foreach ($portions as $portion)
						{
							Application::getConnection()->query(
								"UPDATE " . self::getTableName() . " SET ACTION='" . self::ACTION_AUTO_REMOVE . "'" .
								"WHERE ID IN (" . implode(',', $portion) . ")"
							);
						}
						static::addQueueAutoRemoveAgent();
					}
				}
				else
				{
					Application::getConnection()->query(
						"DELETE FROM " . self::getTableName() .
						" WHERE TYPE = '" . Application::getConnection()->getSqlHelper()->forSql($type) . "'" .
						" AND ACTION in ('" . implode("', '", [self::ACTION_IMPORT, self::ACTION_IMPORT_AND_AUTO_REMOVE, self::ACTION_REMOVE]) . "')" .
						" AND DATE_INSERT < '" . (new DateTime())->add('-1 day')->format("Y-m-d H:i:s") . "'"
					);
				}
			}
		}

		return $hasQueue;
	}

	protected static function divideListIntoPortions($list)
	{
		$portions = array();

		$deleteCount = count($list);
		$portionCount = ceil($deleteCount / self::PORTION_QUANTITY);
		$deleteNum = 0;
		for ($portionNum = 0; $portionNum < $portionCount; $portionNum++)
		{
			$deleteList = array();
			$deletePortionCount = ($portionNum + 1) * self::PORTION_QUANTITY;
			for (; $deleteNum < $deletePortionCount; $deleteNum++)
			{
				if ($deleteNum >= $deleteCount)
				{
					break;
				}
				$deleteList[] = (int) $list[$deleteNum];
			}

			$portions[] = $deleteList;
		}

		return $portions;
	}

	protected static function addQueueAutoRemoveAgent()
	{
		if (isset(static::$isAgentAdded['sys.auto_remove']))
		{
			return;
		}

		$agentName = static::processQueueAutoRemoveAgentName();
		$agent = new \CAgent();
		$agentsDb = $agent->GetList(array("ID" => "DESC"), array(
			"MODULE_ID" => self::MODULE_ID,
			"NAME" => $agentName,
		));
		if (!$agentsDb->Fetch())
		{
			$agent->AddAgent($agentName, self::MODULE_ID, "N", 86400, null, "Y", "");
		}
	}

	protected static function addQueueAgent($type)
	{
		if (isset(static::$isAgentAdded[$type]))
		{
			return;
		}

		$agent = new \CAgent();
		if ($type)
		{
			$agentName = static::getProcessQueueAgentName($type);
			$agentsDb = $agent->GetList(array("ID" => "DESC"), array(
				"MODULE_ID" => self::MODULE_ID,
				"NAME" => $agentName,
			));
			if (!$agentsDb->Fetch())
			{
				$agent->AddAgent($agentName, self::MODULE_ID, "N", 300, null, "Y", "");
			}
		}

		static::$isAgentAdded[$type] = true;
	}

	public static function onAfterAdd(Entity\Event $event)
	{
		$fields = $event->getParameter('fields');
		$type = $fields['TYPE'];
		static::addQueueAgent($type);
	}
}
