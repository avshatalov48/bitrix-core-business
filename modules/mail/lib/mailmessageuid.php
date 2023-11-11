<?php

namespace Bitrix\Mail;

use Bitrix\Mail\Helper\MessageEventManager;
use Bitrix\Mail\Internals\MessageUploadQueueTable;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\Entity;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class MailMessageUidTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MailMessageUid_Query query()
 * @method static EO_MailMessageUid_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MailMessageUid_Result getById($id)
 * @method static EO_MailMessageUid_Result getList(array $parameters = array())
 * @method static EO_MailMessageUid_Entity getEntity()
 * @method static \Bitrix\Mail\EO_MailMessageUid createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\EO_MailMessageUid_Collection createCollection()
 * @method static \Bitrix\Mail\EO_MailMessageUid wakeUpObject($row)
 * @method static \Bitrix\Mail\EO_MailMessageUid_Collection wakeUpCollection($rows)
 */
class MailMessageUidTable extends Entity\DataManager
{
	const OLD = 'Y';
	const RECENT = 'N';
	const DOWNLOADED = 'D';
	const MOVING = 'M';
	const REMOTE = 'R';

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_message_uid';
	}

	/**
	 * @param array $filter
	 * @param array $fields
	 * @param  array $eventData - optional, for compatibility reasons, should have the following structure:
	 * [ ['HEADER_MD5' => .., 'MESSAGE_ID' => .., 'MAILBOX_USER_ID' => ..], [..]]
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function updateList(array $filter, array $fields, array $eventData = [])
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		$result = $connection->query(sprintf(
			"UPDATE %s SET %s WHERE %s",
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			$connection->getSqlHelper()->prepareUpdate($entity->getDbTableName(), $fields)[0],
			Entity\Query::buildFilterSql($entity, $filter)
		));
		$eventManager = EventManager::getInstance();
		$eventKey = $eventManager->addEventHandler(
			'mail',
			'onMailMessageModified',
			array(MessageEventManager::class, 'onMailMessageModified')
		);
		$event = new \Bitrix\Main\Event('mail', 'onMailMessageModified', array(
			'MAIL_FIELDS_DATA' => $eventData,
			'UPDATED_FIELDS_VALUES' => $fields,
			'UPDATED_BY_FILTER' => $filter,
		));
		$event->send();
		EventManager::getInstance()->removeEventHandler('mail', 'onMailMessageModified', $eventKey);

		return $result;
	}

	/**
	 * @param array $filter
	 * @param array $messages
	 * @param int|false $limit
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function deleteList(array $filter, array $messages = [], $limit = false): bool
	{
		$eventName = MessageEventManager::EVENT_DELETE_MESSAGES;

		$filter = array_merge(
			$filter,
			[
				'!=MESSAGE_ID' => 0,
			]
		);

		$messages = static::selectMessagesToBeDeleted(
			MessageEventManager::getRequiredFieldNamesForEvent($eventName),
			$filter,
			$messages,
			$limit
		);

		if (empty($messages))
		{
			return false;
		}

		$entity = static::getEntity();
		$connection = $entity->getConnection();

		$portionLimit = 200;

		$messagesCount = count($messages);

		for ($i = 0; $i < $messagesCount; $i=$i+$portionLimit)
		{
			$portion = array_slice($messages, $i, $portionLimit);

			$query = sprintf(
				' FROM %s WHERE ID IN (\'' . join("','", array_column($portion, 'ID')) . '\')',
				$connection->getSqlHelper()->quote($entity->getDbTableName()),
			);

			self::insertIntoDeleteMessagesQueue($connection, $query);

			$connection->query(sprintf('DELETE %s', $query));
		}

		$remains=[];

		if($limit === false)
		{
			$remains = array_column(
				static::selectMessagesToBeDeleted(
					MessageEventManager::getRequiredFieldNamesForEvent($eventName),
					$filter,
					$messages
				),
				'MESSAGE_ID'
			);
		}
		else
		{
			if ($messagesIds = array_column($messages, 'MESSAGE_ID') )
			{
				$remains = array_column(
					static::getList(
						[
							'select' => [
								'MESSAGE_ID',
							],
							'filter' => [
								'@MESSAGE_ID' => $messagesIds,
							],
						]
					)->fetchAll(),
					'MESSAGE_ID'
				);
			}
		}

		//Checking that the values were actually deleted:
		$deletedMessages = array_filter(
			$messages,
			function ($item) use ($remains)
			{
				return !in_array($item['MESSAGE_ID'], $remains);
			}
		);

		$eventManager = EventManager::getInstance();
		$eventKey = $eventManager->addEventHandler(
			'mail',
			'onMailMessageDeleted',
			array(MessageEventManager::class, 'onMailMessageDeleted')
		);
		$event = new \Bitrix\Main\Event('mail', 'onMailMessageDeleted', array(
			'MAIL_FIELDS_DATA' => $deletedMessages,
			'DELETED_BY_FILTER' => $filter,
		));
		$event->send();
		EventManager::getInstance()->removeEventHandler('mail', 'onMailMessageDeleted', $eventKey);

		return true;
	}

	/**
	 * Insert into delete queue table
	 *
	 * @param Connection $connection DB Connection
	 * @param string $query Query from and where
	 *
	 * @return void
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 */
	private static function insertIntoDeleteMessagesQueue(Connection $connection, string $query): void
	{
		$sqlHelper = $connection->getSqlHelper();
		$messageDeleteTableName = $sqlHelper->quote(Internals\MessageDeleteQueueTable::getTableName());
		$insertFields = ' (ID, MAILBOX_ID, MESSAGE_ID) ';
		$fromSelect = sprintf('(SELECT ID, MAILBOX_ID, MESSAGE_ID %s)', $query);
		$insertQuery = $sqlHelper->getInsertIgnore($messageDeleteTableName, $insertFields, $fromSelect);
		$connection->query($insertQuery);
	}

	public static function getPresetRemoveFilters()
	{
		return [
			'==DELETE_TIME' => 0,
		];
	}

	/**
	 * @param array $filter
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function deleteListSoft(array $filter)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();
		$filter = array_merge($filter , static::getPresetRemoveFilters());

		//mark selected messages for deletion if there are no messages in the download queue
		$query = sprintf(
			'UPDATE %s SET %s WHERE %s AND NOT EXISTS (SELECT 1 FROM %s WHERE %s)',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			$connection->getSqlHelper()->prepareUpdate($entity->getDbTableName(), [
				'DELETE_TIME' => time(),
			])[0],
			Entity\Query::buildFilterSql(
				$entity,
				$filter
			),
			$connection->getSqlHelper()->quote(Internals\MessageUploadQueueTable::getTableName()),
			Entity\Query::buildFilterSql(
				$entity,
				[
					'=ID' => new \Bitrix\Main\DB\SqlExpression('?#', 'ID'),
					'=MAILBOX_ID' => new \Bitrix\Main\DB\SqlExpression('?#', 'MAILBOX_ID'),
				]
			)
		);

		$result = $connection->query($query);
		$count = $connection->getAffectedRowsCount();
		$result->setCount($count > 0 ? $count : 0);

		return $result;
	}

	/**@
	 * @param $fields
	 * @param $filter
	 * @param array $eventData
	 * @param int|false $limit
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function selectMessagesToBeDeleted($fields, $filter, array $eventData, $limit = false): array
	{
		$result = array();

		$primary = array('ID', 'MAILBOX_ID');

		$eventData = array_values($eventData);

		if (empty($eventData))
		{
			$select = $fields;
		}
		else
		{
			$select = array_diff($fields, array_intersect($fields, ...array_map('array_keys', $eventData)));

			if (empty($select))
			{
				return $eventData;
			}

			if (array_diff($primary, array_intersect($primary, ...array_map('array_keys', $eventData))))
			{
				$select = $fields;
			}
			else
			{
				foreach ($eventData as $item)
				{
					$key = sprintf('%u:%s', $item['MAILBOX_ID'], $item['ID']);
					$result[$key] = $item;
				}
			}
		}

		$select = array_unique(array_merge($primary, $select));

		$mailsFilter = $filter;
		$mailsFilter['==IS_IN_QUEUE'] = false;
		$queueSubquery = MessageUploadQueueTable::query();
		$queueSubquery->addFilter('=ID', new \Bitrix\Main\DB\SqlExpression('%s'));
		$queueSubquery->addFilter('=MAILBOX_ID', new \Bitrix\Main\DB\SqlExpression('%s'));
		$emailsForDeleteQuery = MailMessageUidTable::query()
			->registerRuntimeField(new Entity\ExpressionField(
				'IS_IN_QUEUE',
				sprintf('EXISTS(%s)', $queueSubquery->getQuery()),
				['ID', 'MAILBOX_ID']
			))
			->setFilter($mailsFilter);

		if($limit !== false)
		{
			$emailsForDeleteQuery->setLimit($limit);
		}

		foreach ($select as $index => $selectingField)
		{
			if (strncmp('MAILBOX_', $selectingField, 8) === 0 && !MailMessageUidTable::getEntity()->hasField($selectingField))
			{
				$emailsForDeleteQuery->addSelect('MAILBOX.'.mb_substr($selectingField, 8), $selectingField);
				continue;
			}
			$emailsForDeleteQuery->addSelect($selectingField);
		}

		$res = $emailsForDeleteQuery->exec();
		while ($item = $res->fetch())
		{
			$key = sprintf('%u:%s', $item['MAILBOX_ID'], $item['ID']);
			$result[$key] = array_merge((array) $result[$key], $item);
		}

		return array_values($result);
	}

	/**
	 * Merge data. Insert-update.
	 *
	 * @param array $insert Insert fields.
	 * @param array $update Update fields.
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function mergeData(array $insert, array $update)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();
		$helper = $connection->getSqlHelper();

		$sql = $helper->prepareMerge($entity->getDBTableName(), $entity->getPrimaryArray(), $insert, $update);

		$sql = current($sql);
		if($sql <> '')
		{
			$connection->queryExecute($sql);
			$entity->cleanCache();
		}
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'string',
				'primary'   => true,
			),
			'MAILBOX_ID' => array(
				'data_type' => 'integer',
				'primary'   => true,
			),
			'DIR_MD5' => array(
				'data_type' => 'string',
			),
			'DIR_UIDV' => array(
				'data_type' => 'integer',
			),
			'MSG_UID' => array(
				'data_type' => 'integer',
			),
			'INTERNALDATE' => array(
				'data_type' => 'datetime',
			),
			'HEADER_MD5' => array(
				'data_type' => 'string',
			),
			'IS_SEEN' => array(
				'data_type' => 'enum',
				'values'    => array('Y', 'N', 'S', 'U'),
			),
			'IS_OLD' => array(
				'data_type' => 'enum',
				'values'    => array(self::OLD, self::RECENT, self::DOWNLOADED, self::MOVING, self::REMOTE),
			),
			'SESSION_ID' => array(
				'data_type' => 'string',
				'required'  => true,
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'required'  => true,
			),
			'MESSAGE_ID' => array(
				'data_type' => 'integer',
				'required'  => true,
			),
			'MAILBOX' => array(
				'data_type' => 'Bitrix\Mail\Mailbox',
				'reference' => array('=this.MAILBOX_ID' => 'ref.ID'),
			),
			'MESSAGE' => array(
				'data_type' => 'Bitrix\Mail\MailMessage',
				'reference' => array('=this.MESSAGE_ID' => 'ref.ID'),
			),
			'DELETE_TIME' => array(
				'data_type' => 'integer',
				'default' => 0,
			),
		);
	}

}
