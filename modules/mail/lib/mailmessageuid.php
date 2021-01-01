<?php

namespace Bitrix\Mail;

use Bitrix\Mail\Helper\MessageEventManager;
use Bitrix\Mail\Internals\MessageUploadQueueTable;
use Bitrix\Main\Entity;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class MailMessageUidTable extends Entity\DataManager
{
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
	 * @param array $eventData
	 * @return \Bitrix\Main\DB\Result|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function deleteList(array $filter, array $eventData = [])
	{
		$eventName = MessageEventManager::EVENT_DELETE_MESSAGES;

		$eventData = static::selectMailsToBeDeleted(
			MessageEventManager::getRequiredFieldNamesForEvent($eventName),
			$filter,
			$eventData
		);

		if (empty($eventData))
		{
			return null;
		}

		$entity = static::getEntity();
		$connection = $entity->getConnection();

		$query = sprintf(
			' FROM %s WHERE (%s) AND NOT EXISTS (SELECT 1 FROM %s WHERE %s)',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			Entity\Query::buildFilterSql(
				$entity,
				$filter
			),
			$connection->getSqlHelper()->quote(Internals\MessageUploadQueueTable::getTableName()),
			Entity\Query::buildFilterSql(
				$entity,
				array(
					'=ID' => new \Bitrix\Main\DB\SqlExpression('?#', 'ID'),
					'=MAILBOX_ID' => new \Bitrix\Main\DB\SqlExpression('?#', 'MAILBOX_ID'),
				)
			)
		);

		$connection->query(sprintf(
			'INSERT IGNORE INTO %s (ID, MAILBOX_ID, MESSAGE_ID) (SELECT ID, MAILBOX_ID, MESSAGE_ID %s)',
			$connection->getSqlHelper()->quote(Internals\MessageDeleteQueueTable::getTableName()),
			$query
		));

		$result = $connection->query(sprintf('DELETE %s', $query));
		$count = $connection->getAffectedRowsCount();
		$result->setCount($count > 0 ? $count : 0);

		if ($messagesIds = array_column($eventData, 'MESSAGE_ID'))
		{
			$remains = array_column(
				static::getList(array(
					'select' => array('MESSAGE_ID'),
					'filter' => array(
						'@MESSAGE_ID' => $messagesIds,
					),
				))->fetchAll(),
				'MESSAGE_ID'
			);

			$eventData = array_filter(
				$eventData,
				function ($item) use (&$remains)
				{
					return !in_array($item['MESSAGE_ID'], $remains);
				}
			);
		}

		$eventManager = EventManager::getInstance();
		$eventKey = $eventManager->addEventHandler(
			'mail',
			'onMailMessageDeleted',
			array(MessageEventManager::class, 'onMailMessageDeleted')
		);
		$event = new \Bitrix\Main\Event('mail', 'onMailMessageDeleted', array(
			'MAIL_FIELDS_DATA' => $eventData,
			'DELETED_BY_FILTER' => $filter,
		));
		$event->send();
		EventManager::getInstance()->removeEventHandler('mail', 'onMailMessageDeleted', $eventKey);

		return $result;
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

		// @TODO: make a log optional
		/*$queryToLog = sprintf(
			'SELECT
				b_mail_message_uid.ID,
				b_mail_message_uid.MESSAGE_ID,
				b_mail_message_uid.MAILBOX_ID,
				b_mail_message_uid.DIR_MD5,
				b_mail_message_uid.DIR_UIDV,
				b_mail_message_uid.MSG_UID,
				b_mail_message_uid.INTERNALDATE,
				b_mail_message_uid.HEADER_MD5,
				b_mail_message_uid.SESSION_ID,
				b_mail_message_uid.TIMESTAMP_X,
				b_mail_message_uid.DATE_INSERT,
				b_mail_message.DATE_INSERT,
				b_mail_message.FIELD_DATE,
				b_mail_message.FIELD_FROM,
				b_mail_message.SUBJECT,
				b_mail_message.MSG_ID
			FROM b_mail_message_uid JOIN b_mail_message ON b_mail_message_uid.MESSAGE_ID = b_mail_message.id  WHERE %s AND ( b_mail_message_uid.DELETE_TIME IS NULL OR b_mail_message_uid.DELETE_TIME = 0) AND NOT EXISTS (SELECT 1 FROM %s WHERE %s)',
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

		$messagesForRemove = $connection->query($queryToLog)->fetchAll();

		for($i=0; $i < count($messagesForRemove); $i++)
		{
			foreach ($messagesForRemove[$i] as $key => $value)
			{
				if ($messagesForRemove[$i][$key] instanceof \Bitrix\Main\Type\DateTime)
				{
					$messagesForRemove[$i][$key] = $messagesForRemove[$i][$key]->toString();
				}
			}
		}

		if(count($messagesForRemove)>0)
		{
			$toLog = [
				'cause' => 'deleteListSoft',
				'filter'=>$filter,
				'removedMessages'=>$messagesForRemove,
			];
			AddMessage2Log($toLog);
		}*/

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

	private static function selectMailsToBeDeleted($fields, $filter, array $eventData)
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
			),
		);
	}

}
