<?php

namespace Bitrix\Mail;

use Bitrix\Mail\Helper\Mailbox;
use Bitrix\Mail\Internals\MailboxDirectoryTable;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Result;

class MailboxDirectory
{
	public static function fetchAllDirsTypes($mailboxId)
	{
		return MailboxDirectoryTable::getList([
			'filter' => [
				'LOGIC'       => 'AND',
				'=MAILBOX_ID' => $mailboxId,
				[
					'LOGIC'       => 'OR',
					'=IS_OUTCOME' => MailboxDirectoryTable::ACTIVE,
					'=IS_TRASH'   => MailboxDirectoryTable::ACTIVE,
					'=IS_SPAM'    => MailboxDirectoryTable::ACTIVE,
				]
			],
			'select' => ['*'],
			'order'  => ['LEVEL' => 'ASC']
		])->fetchCollection();
	}

	public static function fetchOneLevelByParentId($mailboxId, $id, $level)
	{
		$query = MailboxDirectoryTable::getList([
			'filter' => [
				'LOGIC'       => 'AND',
				'=MAILBOX_ID' => $mailboxId,
				'=PARENT_ID'  => $id,
				'=LEVEL'      => $level,
			],
			'select' => ['*']
		]);

		$result = [];

		while ($row = $query->fetchObject())
		{
			$result[$row->getPath()] = $row;
		}

		return $result;
	}

	public static function fetchAllLevelByParentId($mailboxId, $path, $level)
	{
		$query = MailboxDirectoryTable::getList([
			'filter' => [
				'LOGIC'       => 'AND',
				'=MAILBOX_ID' => $mailboxId,
				'%=PATH'      => $path,
				'>=LEVEL'     => $level,
			],
			'select' => ['*'],
			'order'  => ['LEVEL' => 'ASC']
		]);

		$result = [];

		while ($row = $query->fetchObject())
		{
			$result[$row->getPath()] = $row;
		}

		return $result;
	}

	public static function fetchOneByMailboxIdAndHash($mailboxId, $hash)
	{
		return MailboxDirectoryTable::getList([
			'filter' => [
				'=MAILBOX_ID' => $mailboxId,
				'=DIR_MD5'    => $hash
			]
		])->fetchObject();
	}

	public static function fetchOneOutcome($mailboxId)
	{
		return MailboxDirectoryTable::getList([
			'filter' => [
				'=MAILBOX_ID' => $mailboxId,
				'=IS_OUTCOME' => MailboxDirectoryTable::ACTIVE
			]
		])->fetchObject();
	}

	public static function fetchTrashAndSpamHash($mailboxId)
	{
		$query = MailboxDirectoryTable::getList([
			'filter' => [
				'LOGIC'       => 'AND',
				'=MAILBOX_ID' => $mailboxId,
				[
					'LOGIC'     => 'OR',
					'=IS_TRASH' => MailboxDirectoryTable::ACTIVE,
					'=IS_SPAM'  => MailboxDirectoryTable::ACTIVE,
				]
			]
		]);

		$result = [];

		while ($row = $query->fetch())
		{
			$result[] = $row['DIR_MD5'];
		}

		return $result;
	}

	public static function fetchOneById($id)
	{
		return MailboxDirectoryTable::getList([
			'filter' => [
				'=ID' => $id
			]
		])->fetchObject();
	}

	public static function fetchOneByHash($mailboxId, $hash)
	{
		return MailboxDirectoryTable::getList([
			'filter' => [
				'=MAILBOX_ID' => $mailboxId,
				'=DIR_MD5'    => $hash
			]
		])->fetchObject();
	}

	public static function updateSync($id, $val)
	{
		return MailboxDirectoryTable::update(
			$id,
			[
				'IS_SYNC' => $val
			]
		);
	}

	public static function resetDirsTypes($mailboxId, $type)
	{
		$entity = MailboxDirectoryTable::getEntity();
		$connection = $entity->getConnection();

		$query = sprintf(
			'UPDATE %s SET %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			$connection->getSqlHelper()->prepareUpdate($entity->getDbTableName(), [
				$type => MailboxDirectoryTable::INACTIVE,
			])[0],
			Query::buildFilterSql(
				$entity,
				[
					'MAILBOX_ID' => $mailboxId,
					$type        => MailboxDirectoryTable::ACTIVE,
				]
			)
		);

		return $connection->query($query);
	}

	public static function update($id, $data)
	{
		return MailboxDirectoryTable::update($id, $data);
	}

	public static function add(array $data)
	{
		return MailboxDirectoryTable::add($data);
	}

	public static function addMulti($rows, $ignoreEvents = false)
	{
		return MailboxDirectoryTable::addMulti($rows, $ignoreEvents);
	}

	public static function deleteList(array $filter)
	{
		$entity = MailboxDirectoryTable::getEntity();
		$connection = $entity->getConnection();

		return $connection->query(sprintf(
			'DELETE FROM %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			Query::buildFilterSql($entity, $filter)
		));
	}

	public static function updateSyncDirs(array $values, $val, $mailboxId)
	{
		$entity = MailboxDirectoryTable::getEntity();
		$connection = $entity->getConnection();

		$totalValues = count($values);
		$batchSize = 100;
		$offset = 0;

		while ($offset < $totalValues)
		{
			$batchValues = array_slice($values, $offset, $batchSize);
			$offset += $batchSize;

			$result = $connection->query(sprintf(
				"UPDATE %s SET %s WHERE %s",
				$connection->getSqlHelper()->quote($entity->getDbTableName()),
				$connection->getSqlHelper()->prepareUpdate($entity->getDbTableName(), [
					'IS_SYNC' => $val,
				])[0],
				Query::buildFilterSql(
					$entity,
					[
						'=MAILBOX_ID' => $mailboxId,
						'@DIR_MD5'    => $batchValues,
						'IS_DISABLED' => MailboxDirectoryTable::INACTIVE,
					]
				)
			));
		}

		return $result ?? new Result();
	}

	public static function fetchAll($mailboxId)
	{
		$query = MailboxDirectoryTable::getList([
			'filter' => [
				'=MAILBOX_ID' => $mailboxId
			],
			'select' => ['*'],
			/*
				When assembling directories, we look for their parents.
				Sorting ensures that for a directories that parents are processed first,
				and for children, matching parents are always found from those processed.
			 */
			'order'  => ['LEVEL' => 'ASC']
		]);

		$result = [];

		while ($row = $query->fetchObject())
		{
			$result[$row->getPath()] = $row;
		}

		return $result;
	}

	public static function fetchAllSyncDirs($mailboxId)
	{
		return MailboxDirectoryTable::getList([
			'filter' => [
				'=MAILBOX_ID'  => $mailboxId,
				'=IS_SYNC'     => MailboxDirectoryTable::ACTIVE,
				'=IS_DISABLED' => MailboxDirectoryTable::INACTIVE
			],
			'select' => ['*'],
			'order'  => ['LEVEL' => 'ASC']
		])->fetchCollection();
	}

	public static function fetchAllDisabledDirs($mailboxId)
	{
		return MailboxDirectoryTable::getList([
			'filter' => [
				'=MAILBOX_ID'  => $mailboxId,
				'=IS_DISABLED' => MailboxDirectoryTable::ACTIVE
			],
			'select' => ['*'],
			'order'  => ['ID' => 'ASC']
		])->fetchCollection();
	}

	public static function countMessagesSyncDirs($mailboxId)
	{
		$counter = MailboxDirectoryTable::getList([
			'filter'  => [
				'=MAILBOX_ID'  => $mailboxId,
				'=IS_SYNC'     => MailboxDirectoryTable::ACTIVE,
				'=IS_DISABLED' => MailboxDirectoryTable::INACTIVE
			],
			'select'  => ['CNT'],
			'runtime' => [
				new ExpressionField('CNT', 'SUM(%s)', 'MESSAGE_COUNT'),
			]
		])->fetch();

		return (int)$counter['CNT'];
	}

	public static function getMinSyncTime($mailboxId)
	{
		$res = MailboxDirectoryTable::getList([
			'filter'  => [
				'=MAILBOX_ID' => $mailboxId,
				'=IS_SYNC'    => MailboxDirectoryTable::ACTIVE,
			],
			'select'  => ['MIN_SYNC_TIME'],
			'runtime' => [
				new ExpressionField('MIN_SYNC_TIME', 'MIN(COALESCE(%s, 0))', 'SYNC_TIME'),
			]
		])->fetch();

		return (int)$res['MIN_SYNC_TIME'];
	}

	public static function countSyncDirs($mailboxId)
	{
		$counter = MailboxDirectoryTable::getList([
			'filter'  => [
				'=MAILBOX_ID'  => $mailboxId,
				'=IS_SYNC'     => MailboxDirectoryTable::ACTIVE,
				'=IS_DISABLED' => MailboxDirectoryTable::INACTIVE
			],
			'select'  => ['CNT'],
			'runtime' => [
				new ExpressionField('CNT', 'COUNT(*)'),
			]
		])->fetch();

		return (int)$counter['CNT'];
	}

	public static function updateMessageCount($id, $val)
	{
		return MailboxDirectoryTable::update(
			$id,
			[
				'MESSAGE_COUNT' => $val
			]
		);
	}

	public static function updateFlags($id, $flags)
	{
		return MailboxDirectoryTable::update(
			$id,
			[
				'FLAGS' => $flags
			]
		);
	}

	public static function updateSyncTime($id, $val)
	{
		return MailboxDirectoryTable::update(
			$id,
			[
				'SYNC_TIME' => $val
			]
		);
	}

	public static function setSyncLock(int $id, int $time)
	{
		$entity = MailboxDirectoryTable::getEntity();
		$connection = $entity->getConnection();

		$query = sprintf(
			"UPDATE %s SET %s WHERE %s",
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			$connection->getSqlHelper()->prepareUpdate($entity->getDbTableName(), [
				'SYNC_LOCK' => $time,
			])[0],
			Query::buildFilterSql(
				$entity,
				[
					'=ID' => $id,
					[
						'LOGIC'      => 'OR',
						'=SYNC_LOCK' => 'IS NULL',
						'<SYNC_LOCK' => time() - Mailbox::getTimeout(),
					]
				]
			)
		);

		$connection->query($query);
		$count = $connection->getAffectedRowsCount();

		return $count;
	}
}
