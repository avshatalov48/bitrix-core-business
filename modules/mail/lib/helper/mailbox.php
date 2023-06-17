<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Mail;
use Bitrix\Mail\MailboxTable;
use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Mail\Helper;

abstract class Mailbox
{
	const SYNC_TIMEOUT = 300;
	const SYNC_TIME_QUOTA = 280;
	const MESSAGE_RESYNCHRONIZATION_TIME = 360;
	const MESSAGE_DELETION_LIMIT_AT_A_TIME = 1000;
	const NUMBER_OF_BROKEN_MESSAGES_TO_RESYNCHRONIZE = 2;

	protected $dirsMd5WithCounter;
	protected $mailbox;
	protected $dirsHelper;
	protected $filters;
	protected $session;
	protected $startTime, $syncTimeout, $checkpoint;
	protected $syncParams = [];
	protected $errors, $warnings;
	protected $lastSyncResult = [
		'newMessages' => 0,
		'newMessagesNotify' => 0,
		'deletedMessages' => 0,
		'updatedMessages' => 0,
		'newMessageId' => null,
	];

	/**
	 * Creates active mailbox helper instance by ID
	 *
	 * @param int $id Mailbox ID.
	 * @param bool $throw Throw exception on error.
	 * @return \Bitrix\Mail\Helper\Mailbox|false
	 * @throws \Exception
	 */
	public static function createInstance($id, $throw = true)
	{
		return static::rawInstance(array('=ID' => (int) $id, '=ACTIVE' => 'Y'), $throw);
	}

	public function getDirsMd5WithCounter($mailboxId)
	{
		if($this->dirsMd5WithCounter)
		{
			return $this->dirsMd5WithCounter;
		}

		$foldersWithCounter = Mail\Internals\MailCounterTable::getList([
			'runtime' => array(
				new ORM\Fields\Relations\Reference(
					'DIRECTORY',
					'Bitrix\Mail\Internals\MailboxDirectoryTable',
					[
						'=this.ENTITY_ID' => 'ref.ID',
					],
					[
						'join_type' => 'INNER',
					]
				),
			),
			'select' => [
				'UNSEEN' => 'VALUE',
				'DIR_MD5' => 'DIRECTORY.DIR_MD5'
			],
			'filter' => [
				'=DIRECTORY.MAILBOX_ID' => $mailboxId,
				'=ENTITY_TYPE' => 'DIR',
				'=MAILBOX_ID' => $mailboxId,
			],
		]);

		$directoriesWithCounter = [];

		while ($folderTable = $foldersWithCounter->fetch())
		{
			$directoriesWithCounter[$folderTable['DIR_MD5']] = $folderTable;
		}

		$this->dirsMd5WithCounter = $directoriesWithCounter;

		return $directoriesWithCounter;
	}

	public function sendCountersEvent()
	{
		\CPullWatch::addToStack(
			'mail_mailbox_' . $this->mailbox['ID'],
			[
				'params' => [
					'dirs' => $this->getDirsWithUnseenMailCounters(),
				],
				'module_id' => 'mail',
				'command' => 'counters_is_synchronized',
			]
		);
		\Bitrix\Pull\Event::send();
	}

	public function getDirsWithUnseenMailCounters()
	{
		global $USER;
		$mailboxId = $this->mailbox['ID'];

		if (!Helper\Message::isMailboxOwner($mailboxId, $USER->GetID()))
		{
			return false;
		}

		$syncDirs = $this->getDirsHelper()->getSyncDirs();
		$defaultDirPath = $this->getDirsHelper()->getDefaultDirPath();
		$dirs = [];

		$dirsMd5WithCountOfUnseenMails = $this->getDirsMd5WithCounter($mailboxId);

		$defaultDirPathId = null;

		foreach ($syncDirs as $dir)
		{
			$newDir = [];
			$newDir['path'] = $dir->getPath(true);
			$newDir['name'] = $dir->getName();
			$newDir['count'] = 0;
			$currentDirMd5WithCountsOfUnseenMails = $dirsMd5WithCountOfUnseenMails[$dir->getDirMd5()];

			if ($currentDirMd5WithCountsOfUnseenMails !== null)
			{
				$newDir['count'] = $currentDirMd5WithCountsOfUnseenMails['UNSEEN'];
			}

			if($newDir['path'] === $defaultDirPath)
			{
				$defaultDirPathId = count($dirs);
			}

			$dirs[] = $newDir;
		}

		if (empty($dirs))
		{
			$dirs = [
				[
					'count' => 0,
					'path' => $defaultDirPath,
					'name' => $defaultDirPath,
				],
			];
		}

		//inbox always on top
		array_unshift( $dirs, array_splice($dirs, $defaultDirPathId, 1)[0] );

		return $dirs;
	}

	/**
	 * Creates mailbox helper instance
	 *
	 * @param mixed $filter Filter.
	 * @param bool $throw Throw exception on error.
	 * @return \Bitrix\Mail\Helper\Mailbox|false
	 * @throws \Exception
	 */
	public static function rawInstance($filter, $throw = true)
	{
		try
		{
			$mailbox = static::prepareMailbox($filter);

			return static::instance($mailbox);
		}
		catch (\Exception $e)
		{
			if ($throw)
			{
				throw $e;
			}
			else
			{
				return false;
			}
		}
	}

	protected static function instance(array $mailbox)
	{
		// @TODO: other SERVER_TYPE
		$types = array(
			'imap' => 'Bitrix\Mail\Helper\Mailbox\Imap',
			'controller' => 'Bitrix\Mail\Helper\Mailbox\Imap',
			'domain' => 'Bitrix\Mail\Helper\Mailbox\Imap',
			'crdomain' => 'Bitrix\Mail\Helper\Mailbox\Imap',
		);

		if (empty($mailbox))
		{
			throw new Main\ObjectException('no mailbox');
		}

		if (empty($mailbox['SERVER_TYPE']) || !array_key_exists($mailbox['SERVER_TYPE'], $types))
		{
			throw new Main\ObjectException('unsupported mailbox type');
		}

		return new $types[$mailbox['SERVER_TYPE']]($mailbox);
	}

	public static function prepareMailbox($filter)
	{
		if (is_scalar($filter))
		{
			$filter = array('=ID' => (int) $filter);
		}

		$mailbox = Mail\MailboxTable::getList(array(
			'filter' => $filter,
			'select' => array('*', 'LANG_CHARSET' => 'SITE.CULTURE.CHARSET'),
			'limit' => 1,
		))->fetch() ?: array();

		if (!empty($mailbox))
		{
			if (in_array($mailbox['SERVER_TYPE'], array('controller', 'crdomain', 'domain')))
			{
				$result = \CMailDomain2::getImapData(); // @TODO: request controller for 'controller' and 'crdomain'

				$mailbox['SERVER']  = $result['server'];
				$mailbox['PORT']    = $result['port'];
				$mailbox['USE_TLS'] = $result['secure'];
			}

			Mail\MailboxTable::normalizeEmail($mailbox);
		}

		return $mailbox;
	}

	public function setSyncParams(array $params = array())
	{
		$this->syncParams = $params;
	}

	protected function __construct($mailbox)
	{
		$this->startTime = time();
		if (defined('START_EXEC_PROLOG_BEFORE_1'))
		{
			$startTime = 0;
			if (is_float(START_EXEC_PROLOG_BEFORE_1))
			{
				$startTime = START_EXEC_PROLOG_BEFORE_1;
			}
			elseif (preg_match('/ (\d+)$/', START_EXEC_PROLOG_BEFORE_1, $matches))
			{
				$startTime = $matches[1];
			}

			if ($startTime > 0 && $this->startTime > $startTime)
			{
				$this->startTime = $startTime;
			}
		}

		$this->syncTimeout = static::getTimeout();

		$this->mailbox = $mailbox;

		$this->normalizeMailboxOptions();

		$this->setCheckpoint();

		$this->session = md5(uniqid(''));
		$this->errors = new Main\ErrorCollection();
		$this->warnings = new Main\ErrorCollection();
	}

	protected function normalizeMailboxOptions()
	{
		if (empty($this->mailbox['OPTIONS']) || !is_array($this->mailbox['OPTIONS']))
		{
			$this->mailbox['OPTIONS'] = array();
		}
	}

	public function getMailbox()
	{
		return $this->mailbox;
	}

	/*
	Additional check that the quota has not been exceeded
	since the actual creation of the mailbox instance in php
	*/
	protected function isTimeQuotaExceeded()
	{
		return time() - $this->startTime > ceil(static::getTimeout() * 0.9);
	}

	public function setCheckpoint()
	{
		$this->checkpoint = time();
	}

	public function updateGlobalCounter($userId)
	{
		\CUserCounter::set(
			$userId,
			'mail_unseen',
			Message::getCountersForUserMailboxes($userId, true),
			$this->mailbox['LID']
		);
	}

	public function updateGlobalCounterForCurrentUser()
	{
		$this->updateGlobalCounter($this->mailbox['USER_ID']);
	}

	private function findMessagesWithAnEmptyBody(int $count, $mailboxId)
	{
		$reSyncTime = (new Main\Type\DateTime())->add('- '.static::MESSAGE_RESYNCHRONIZATION_TIME.' seconds');

		$ids = Mail\Internals\MailEntityOptionsTable::getList(
			[
				'select' => ['ENTITY_ID'],
				'filter' =>
				[
					'=MAILBOX_ID' => $mailboxId,
					'=ENTITY_TYPE' => 'MESSAGE',
					'=PROPERTY_NAME' => 'UNSYNC_BODY',
					'=VALUE' => 'Y',
					'<=DATE_INSERT' => $reSyncTime,
				]
				,
				'limit' => $count,
			]
		)->fetchAll();

		return array_map(
			function ($item)
			{
				return $item['ENTITY_ID'];
			},
			$ids
		);
	}

	//Finds completely missing messages
	private function findIncompleteMessages(int $count)
	{
		$resyncTime = new Main\Type\DateTime();
		$resyncTime->add('- '.static::MESSAGE_RESYNCHRONIZATION_TIME.' seconds');

		return Mail\MailMessageUidTable::getList([
			'select' => array(
				'MSG_UID',
				'DIR_MD5',
			),
			'filter' => array(
				'=MAILBOX_ID' => $this->mailbox['ID'],
				'=MESSAGE_ID' => '0',
				'=IS_OLD' => 'D',
				/*We give the message time to load.
				In order not to catch the message that are in the process of downloading*/
				'<=DATE_INSERT' => $resyncTime,
			),
			'limit' => $count,
		]);
	}

	private function syncIncompleteMessages($messages)
	{
		while ($item = $messages->fetch())
		{
			$dirPath = $this->getDirsHelper()->getDirPathByHash($item['DIR_MD5']);
			$this->syncMessages($this->mailbox['ID'], $dirPath, [$item['MSG_UID']]);
		}
	}

	public function reSyncStartPage()
	{
		$this->resyncDir($this->getDirsHelper()->getDefaultDirPath(),25);
	}

	public function restoringConsistency()
	{
		$this->syncIncompleteMessages($this->findIncompleteMessages(static::NUMBER_OF_BROKEN_MESSAGES_TO_RESYNCHRONIZE));
		\Bitrix\Mail\Helper\Message::reSyncBody($this->mailbox['ID'],$this->findMessagesWithAnEmptyBody(static::NUMBER_OF_BROKEN_MESSAGES_TO_RESYNCHRONIZE, $this->mailbox['ID']));
	}

	public function syncCounters()
	{
		Helper::setMailboxUnseenCounter($this->mailbox['ID'],Helper::updateMailCounters($this->mailbox));

		$usersWithAccessToMailbox = Mailbox\SharedMailboxesManager::getUserIdsWithAccessToMailbox($this->mailbox['ID']);

		foreach ($usersWithAccessToMailbox as $userId)
		{
			$this->updateGlobalCounter($userId);
		}
	}

	public function sync($syncCounters = true)
	{
		global $DB;

		/*
			Setting a new time for an attempt to synchronize the mailbox
			through the agent for users with a free tariff
		*/
		if (!LicenseManager::isSyncAvailable())
		{
			$this->mailbox['OPTIONS']['next_sync'] = time() + 3600 * 24;

			return 0;
		}

		/*
		Do not start synchronization if no more than static::getTimeout() have passed since the previous one
		*/
		if (time() - $this->mailbox['SYNC_LOCK'] < static::getTimeout())
		{
			return 0;
		}

		$this->mailbox['SYNC_LOCK'] = time();

		/*
		Additional check that the quota has not been exceeded
		since the actual creation of the mailbox instance in php
		*/
		if ($this->isTimeQuotaExceeded())
		{
			return 0;
		}

		$this->session = md5(uniqid(''));

		$this->syncOutgoing();
		$this->restoringConsistency();
		$this->reSyncStartPage();

		$lockSql = sprintf(
			'UPDATE b_mail_mailbox SET SYNC_LOCK = %u WHERE ID = %u AND (SYNC_LOCK IS NULL OR SYNC_LOCK < %u)',
			$this->mailbox['SYNC_LOCK'], $this->mailbox['ID'], $this->mailbox['SYNC_LOCK'] - static::getTimeout()
		);

		/*
		If the time record for blocking synchronization has not been added to the table,
		we will have to abort synchronization
		*/
		if (!$DB->query($lockSql)->affectedRowsCount())
		{
			return 0;
		}

		$mailboxSyncManager = new Mailbox\MailboxSyncManager($this->mailbox['USER_ID']);
		if ($this->mailbox['USER_ID'] > 0)
		{
			$mailboxSyncManager->setSyncStartedData($this->mailbox['ID']);
		}

		$syncReport = $this->syncInternal();
		$count = $syncReport['syncCount'];

		if($syncReport['reSyncStatus'])
		{
			/*
			When folders are successfully resynchronized,
			allow messages that were left to be moved to be deleted
			*/
			Mail\MailMessageUidTable::updateList(
				[
					'=MAILBOX_ID' => $this->mailbox['ID'],
					'=MSG_UID' => 0,
					'=IS_OLD' => 'M',
				],
				[
					'IS_OLD' => 'R',
				],
			);
		}

		$success = $count !== false && $this->errors->isEmpty();

		$syncUnlock = $this->isTimeQuotaExceeded() ? 0 : -1;

		$interval = max(1, (int) $this->mailbox['PERIOD_CHECK']) * 60;
		$syncErrors = max(0, (int) $this->mailbox['OPTIONS']['sync_errors']);

		if ($count === false)
		{
			$syncErrors++;

			$maxInterval = 3600 * 24 * 7;
			for ($i = 1; $i < $syncErrors && $interval < $maxInterval; $i++)
			{
				$interval = min($interval * ($i + 1), $maxInterval);
			}
		}
		else
		{
			$syncErrors = 0;

			$interval = $syncUnlock < 0 ? $interval : min($count > 0 ? 60 : 600, $interval);
		}

		$this->mailbox['OPTIONS']['sync_errors'] = $syncErrors;
		$this->mailbox['OPTIONS']['next_sync'] = time() + $interval;

		$optionsValue = $this->mailbox['OPTIONS'];

		$unlockSql = sprintf(
			"UPDATE b_mail_mailbox SET SYNC_LOCK = %d, OPTIONS = '%s' WHERE ID = %u AND SYNC_LOCK = %u",
			$syncUnlock,
			$DB->forSql(serialize($optionsValue)),
			$this->mailbox['ID'],
			$this->mailbox['SYNC_LOCK']
		);
		if ($DB->query($unlockSql)->affectedRowsCount())
		{
			$this->mailbox['SYNC_LOCK'] = $syncUnlock;
		}

		$lastSyncResult = $this->getLastSyncResult();

		$this->pushSyncStatus(
			array(
				'new' => $count,
				'updated' => $lastSyncResult['updatedMessages'],
				'deleted' => $lastSyncResult['deletedMessages'],
				'complete' => $this->mailbox['SYNC_LOCK'] < 0,
			),
			true
		);

		$this->notifyNewMessages();

		if ($this->mailbox['USER_ID'] > 0)
		{
			$mailboxSyncManager->setSyncStatus($this->mailbox['ID'], $success, time());
		}

		if($syncCounters)
		{
			$this->syncCounters();
		}

		return $count;
	}

	public function getSyncStatus()
	{
		return -1;
	}

	protected function pushSyncStatus($params, $force = false)
	{
		if (Main\Loader::includeModule('pull'))
		{
			$status = $this->getSyncStatus();

			\CPullWatch::addToStack(
				'mail_mailbox_' . $this->mailbox['ID'],
				array(
					'module_id' => 'mail',
					'command' => 'mailbox_sync_status',
					'params' => array_merge(
						array(
							'id' => $this->mailbox['ID'],
							'status' => sprintf('%.3f', $status),
							'sessid' => $this->syncParams['sessid'] ?? $this->session,
							'timestamp' => microtime(true),
						),
						$params
					),
				)
			);

			if ($force)
			{
				\Bitrix\Pull\Event::send();
			}
		}
	}

	public function dismissOldMessages()
	{
		global $DB;

		if (!Mail\Helper\LicenseManager::isCleanupOldEnabled())
		{
			return true;
		}

		$startTime = time();

		if (time() - $this->mailbox['SYNC_LOCK'] < static::getTimeout())
		{
			return false;
		}

		if ($this->isTimeQuotaExceeded())
		{
			return false;
		}

		$syncUnlock = $this->mailbox['SYNC_LOCK'];

		$lockSql = sprintf(
			'UPDATE b_mail_mailbox SET SYNC_LOCK = %u WHERE ID = %u AND (SYNC_LOCK IS NULL OR SYNC_LOCK < %u)',
			$startTime, $this->mailbox['ID'], $startTime - static::getTimeout()
		);
		if ($DB->query($lockSql)->affectedRowsCount())
		{
			$this->mailbox['SYNC_LOCK'] = $startTime;
		}
		else
		{
			return false;
		}

		$result = true;

		$entity = Mail\MailMessageUidTable::getEntity();
		$connection = $entity->getConnection();

		$whereConditionForOldMessages = sprintf(
			' (%s)',
			ORM\Query\Query::buildFilterSql(
				$entity,
				array(
					'=MAILBOX_ID' => $this->mailbox['ID'],
					'>MESSAGE_ID' => 0,
					'<INTERNALDATE' => Main\Type\Date::createFromTimestamp(strtotime(sprintf('-%u days', Mail\Helper\LicenseManager::getSyncOldLimit()))),
					'!=IS_OLD' => 'Y',
				)
			)
		);

		$where = sprintf(
			' (%s) AND NOT EXISTS (SELECT 1 FROM %s WHERE (%s) AND (%s)) ',
			ORM\Query\Query::buildFilterSql(
				$entity,
				array(
					'=MAILBOX_ID' => $this->mailbox['ID'],
					'>MESSAGE_ID' => 0,
					'<INTERNALDATE' => Main\Type\DateTime::createFromTimestamp(strtotime(sprintf('-%u days', Mail\Helper\LicenseManager::getSyncOldLimit()))),
				)
			),
			$connection->getSqlHelper()->quote(Mail\Internals\MessageAccessTable::getTableName()),
			ORM\Query\Query::buildFilterSql(
				$entity,
				array(
					'=MAILBOX_ID' => new Main\DB\SqlExpression('?#', 'MAILBOX_ID'),
					'=MESSAGE_ID' => new Main\DB\SqlExpression('?#', 'MESSAGE_ID'),
				)
			),
			ORM\Query\Query::buildFilterSql(
				Mail\Internals\MessageAccessTable::getEntity(),
				array(
					'=ENTITY_TYPE' => array(
						Mail\Internals\MessageAccessTable::ENTITY_TYPE_TASKS_TASK,
						Mail\Internals\MessageAccessTable::ENTITY_TYPE_BLOG_POST,
					),
				)
			)
		);

		do
		{
			$connection->query(sprintf(
				'INSERT IGNORE INTO %s (ID, MAILBOX_ID, MESSAGE_ID)
				(SELECT ID, MAILBOX_ID, MESSAGE_ID FROM %s WHERE %s ORDER BY ID LIMIT 1000)',
				$connection->getSqlHelper()->quote(Mail\Internals\MessageDeleteQueueTable::getTableName()),
				$connection->getSqlHelper()->quote($entity->getDbTableName()),
				$where
			));

			$connection->query(sprintf(
				"UPDATE %s SET IS_OLD = 'Y', IS_SEEN = 'Y' WHERE %s ORDER BY ID LIMIT 1000",
				$connection->getSqlHelper()->quote($entity->getDbTableName()),
				$whereConditionForOldMessages
			));

			$connection->query(sprintf(
				'UPDATE %s SET MESSAGE_ID = 0 WHERE %s ORDER BY ID LIMIT 1000',
				$connection->getSqlHelper()->quote($entity->getDbTableName()),
				$where
			));

			if ($this->isTimeQuotaExceeded() || time() - $this->checkpoint > 15)
			{
				$result = false;

				break;
			}
		}
		while ($connection->getAffectedRowsCount() >= 1000);

		$unlockSql = sprintf(
			"UPDATE b_mail_mailbox SET SYNC_LOCK = %d WHERE ID = %u AND SYNC_LOCK = %u",
			$syncUnlock, $this->mailbox['ID'], $this->mailbox['SYNC_LOCK']
		);
		if ($DB->query($unlockSql)->affectedRowsCount())
		{
			$this->mailbox['SYNC_LOCK'] = $syncUnlock;
		}

		return $result;
	}

	public function dismissDeletedUidMessages()
	{
		global $DB;

		$startTime = time();

		if (time() - $this->mailbox['SYNC_LOCK'] < static::getTimeout())
		{
			return false;
		}

		if ($this->isTimeQuotaExceeded())
		{
			return false;
		}

		$syncUnlock = $this->mailbox['SYNC_LOCK'];

		$lockSql = sprintf(
			'UPDATE b_mail_mailbox SET SYNC_LOCK = %u WHERE ID = %u AND (SYNC_LOCK IS NULL OR SYNC_LOCK < %u)',
			$startTime, $this->mailbox['ID'], $startTime - static::getTimeout()
		);
		if ($DB->query($lockSql)->affectedRowsCount())
		{
			$this->mailbox['SYNC_LOCK'] = $startTime;
		}
		else
		{
			return false;
		}

		$minSyncTime = Mail\MailboxDirectory::getMinSyncTime($this->mailbox['ID']);

		Mail\MailMessageUidTable::deleteList(
			[
				'=MAILBOX_ID'  => $this->mailbox['ID'],
				'>DELETE_TIME' => 0,
				/*The values in the tables are still used to delete related items (example: attachments):*/
				'<DELETE_TIME' => $minSyncTime,
			],
			[],
			static::MESSAGE_DELETION_LIMIT_AT_A_TIME
		);

		$unlockSql = sprintf(
			"UPDATE b_mail_mailbox SET SYNC_LOCK = %d WHERE ID = %u AND SYNC_LOCK = %u",
			$syncUnlock, $this->mailbox['ID'], $this->mailbox['SYNC_LOCK']
		);
		if ($DB->query($unlockSql)->affectedRowsCount())
		{
			$this->mailbox['SYNC_LOCK'] = $syncUnlock;
		}

		return true;
	}

	public function cleanup()
	{
		do
		{
			$res = Mail\Internals\MessageDeleteQueueTable::getList(array(
				'runtime' => array(
					new ORM\Fields\Relations\Reference(
						'MESSAGE_UID',
						'Bitrix\Mail\MailMessageUidTable',
						array(
							'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
							'=this.MESSAGE_ID' => 'ref.MESSAGE_ID',
						)
					),
				),
				'select' => array('MESSAGE_ID', 'UID' => 'MESSAGE_UID.ID'),
				'filter' => array(
					'=MAILBOX_ID' => $this->mailbox['ID'],
				),
				'limit' => 100,
			));

			$count = 0;
			while ($item = $res->fetch())
			{
				$count++;

				if (empty($item['UID']))
				{
					\CMailMessage::delete($item['MESSAGE_ID']);
				}

				Mail\Internals\MessageDeleteQueueTable::deleteList(array(
					'=MAILBOX_ID' => $this->mailbox['ID'],
					'=MESSAGE_ID' => $item['MESSAGE_ID'],
				));

				if ($this->isTimeQuotaExceeded() || time() - $this->checkpoint > 60)
				{
					return false;
				}
			}
		}
		while ($count > 0);

		return true;
	}

	protected function listMessages($params = array(), $fetch = true)
	{
		$filter = array(
			'=MAILBOX_ID' => $this->mailbox['ID'],
		);

		if (!empty($params['filter']))
		{
			$filter = array_merge((array) $params['filter'], $filter);
		}

		$params['filter'] = $filter;

		$result = Mail\MailMessageUidTable::getList($params);

		return $fetch ? $result->fetchAll() : $result;
	}

	protected function registerMessage(&$fields, $replaces = null, $isOutgoing = false)
	{
		$now = new Main\Type\DateTime();

		if (!empty($replaces))
		{
			/*
				To replace the temporary id of outgoing emails with a permanent one
				after receiving the uid from the original mail service.
			*/
			if($isOutgoing)
			{
				if (!is_array($replaces))
				{
					$replaces = [
						'=ID' => $replaces,
					];
				}

				$exists = Mail\MailMessageUidTable::getList([
					'select' => [
						'ID',
						'MESSAGE_ID',
					],
					'filter' => [
						$replaces,
						'=MAILBOX_ID' => $this->mailbox['ID'],
						'==DELETE_TIME' => 0,
					],
				])->fetch();
			}
			else
			{
				$exists = [
					'ID' => $replaces,
					'MESSAGE_ID' => $fields['MESSAGE_ID'],
				];
			}
		}

		if (!empty($exists))
		{
			$fields['MESSAGE_ID'] = $exists['MESSAGE_ID'];

			$result = (bool) Mail\MailMessageUidTable::updateList(
				array(
					'=ID' => $exists['ID'],
					'=MAILBOX_ID' => $this->mailbox['ID'],
					'==DELETE_TIME' => 0,
				),
				array_merge(
					$fields,
					array(
						'TIMESTAMP_X' => $now,
					)
				),
				array_merge(
					$exists,
					array(
						'MAILBOX_USER_ID' => $this->mailbox['USER_ID'],
					)
				)
			);
		}
		else
		{
			$checkResult = new ORM\Data\AddResult();
			$addFields = array_merge(
				[
					'MESSAGE_ID'  => 0,
				],
				$fields,
				[
					'IS_OLD' => 'D',
					'MAILBOX_ID'  => $this->mailbox['ID'],
					'SESSION_ID'  => $this->session,
					'TIMESTAMP_X' => $now,
					'DATE_INSERT' => $now,
				]
			);

			Mail\MailMessageUidTable::checkFields($checkResult, null, $addFields);
			if (!$checkResult->isSuccess())
			{
				return false;
			}

			Mail\MailMessageUidTable::mergeData($addFields, [
				'MSG_UID' => $addFields['MSG_UID'],
				'HEADER_MD5' => $addFields['HEADER_MD5'],
				'SESSION_ID' => $addFields['SESSION_ID'],
				'TIMESTAMP_X' => $addFields['TIMESTAMP_X'],
			]);

			return true;
		}

		return $result;
	}

	protected function updateMessagesRegistry(array $filter, array $fields, $mailData = array())
	{
		return Mail\MailMessageUidTable::updateList(
			array_merge(
				$filter,
				array(
					'!=IS_OLD' => 'Y',
					'=MAILBOX_ID' => $this->mailbox['ID'],
				)
			),
			$fields,
			$mailData
		);
	}

	protected function unregisterMessages($filter, $eventData = [], $ignoreDeletionCheck = false)
	{
		$messageExistInTheOriginalMailbox = false;
		$messagesForRemove = [];
		$filterForCheck = [];

		if(!$ignoreDeletionCheck)
		{
			$filterForCheck = array_merge(
				$filter,
				Mail\MailMessageUidTable::getPresetRemoveFilters(),
				[
					'=MAILBOX_ID' => $this->mailbox['ID'],
					/*
						We check illegally deleted messages,
						the disappearance of which the user may notice.
						According to such data, it is easier to find a message
						in the original mailbox for diagnostics.
					*/
					'!=MESSAGE_ID'  => 0,
				]
			);

			$messagesForRemove = Mail\MailMessageUidTable::getList([
				'select' => [
					'ID',
					'MAILBOX_ID',
					'DIR_MD5',
					'DIR_UIDV',
					'MSG_UID',
					'INTERNALDATE',
					'IS_SEEN',
					'DATE_INSERT',
					'MESSAGE_ID',
					'IS_OLD',
				],
				'filter' => $filterForCheck,
				'limit' => 100,
			])->fetchAll();


			if (!empty($messagesForRemove))
			{
				if (isset($messagesForRemove[0]['DIR_MD5']))
				{
					$dirMD5 = $messagesForRemove[0]['DIR_MD5'];
					$dirPath = $this->getDirsHelper()->getDirPathByHash($dirMD5);
					$UIDs = array_map(
						function ($item) {
							return $item['MSG_UID'];
						},
						$messagesForRemove
					);

					$messageExistInTheOriginalMailbox = $this->checkMessagesForExistence($dirPath, $UIDs);
				}
			}
		}

		if($messageExistInTheOriginalMailbox === false)
		{
			return Mail\MailMessageUidTable::deleteListSoft(
				array_merge(
					$filter,
					[
						'=MAILBOX_ID' => $this->mailbox['ID'],
					]
				)
			);
		}
		else
		{
			$messageForLog = isset($messagesForRemove[0]) ? $messagesForRemove[0] : [];

			/*
				For the log, we take a message from the entire sample,
				which was definitely deleted by mistake.
			*/
			foreach($messagesForRemove as $message)
			{
				if(isset($message['MSG_UID']) && (int)$message['MSG_UID'] === (int)$messageExistInTheOriginalMailbox)
				{
					$messageForLog = $message;
					break;
				}
			}

			if(isset($messageForLog['INTERNALDATE']) && $messageForLog['INTERNALDATE'] instanceof Main\Type\DateTime)
			{
				$messageForLog['INTERNALDATE'] = $messageForLog['INTERNALDATE']->getTimestamp();
			}
			if(isset($messageForLog['DATE_INSERT']) && $messageForLog['DATE_INSERT'] instanceof Main\Type\DateTime)
			{
				$messageForLog['DATE_INSERT'] = $messageForLog['DATE_INSERT']->getTimestamp();
			}

			if(isset($filterForCheck['@ID']))
			{
				$filterForCheck['@ID'] = '[hidden for the log]';
			}

			AddMessage2Log(array_merge($eventData,[
				'filter' => $filterForCheck,
				'message-data' => $messageForLog,
			]));

			return false;
		}
	}

	protected function linkMessage($uid, $id)
	{
		$result = Mail\MailMessageUidTable::update(
			array(
				'ID' => $uid,
				'MAILBOX_ID' => $this->mailbox['ID'],
			),
			array(
				'MESSAGE_ID' => $id,
			)
		);

		return $result->isSuccess();
	}

	protected function cacheMessage(&$body, $params = array())
	{
		if (empty($params['origin']) && empty($params['replaces']))
		{
			$params['lazy_attachments'] = $this->isSupportLazyAttachments();
		}

		return \CMailMessage::addMessage(
			$this->mailbox['ID'],
			$body,
			$this->mailbox['CHARSET'] ?: $this->mailbox['LANG_CHARSET'],
			$params
		);
	}

	public function mail(array $params)
	{
		class_exists('Bitrix\Mail\Helper');

		$message = new Mail\DummyMail($params);

		$messageUid = $this->createMessage($message);

		Mail\Internals\MessageUploadQueueTable::add(array(
			'ID' => $messageUid,
			'MAILBOX_ID' => $this->mailbox['ID'],
		));

		\CAgent::addAgent(
			sprintf(
				'Bitrix\Mail\Helper::syncOutgoingAgent(%u);',
				$this->mailbox['ID']
			),
			'mail', 'N', 60
		);
	}

	protected function createMessage(Main\Mail\Mail $message, array $fields = array())
	{
		$messageUid = sprintf('%x%x', time(), rand(0, 0xffffffff));
		$body = sprintf(
			'%1$s%3$s%3$s%2$s',
			$message->getHeaders(),
			$message->getBody(),
			$message->getMailEol()
		);

		$messageId = $this->cacheMessage(
			$body,
			array(
				'outcome' => true,
				'draft' => false,
				'trash' => false,
				'spam' => false,
				'seen' => true,
				'trackable' => true,
				'origin' => true,
			)
		);

		$fields = array_merge(
			$fields,
			array(
				'ID' => $messageUid,
				'INTERNALDATE' => new Main\Type\DateTime,
				'IS_SEEN' => 'Y',
				'MESSAGE_ID' => $messageId,
			)
		);

		$this->registerMessage($fields);

		return $messageUid;
	}

	public function syncOutgoing()
	{
		$res = $this->listMessages(
			array(
				'runtime' => array(
					new \Bitrix\Main\Entity\ReferenceField(
						'UPLOAD_QUEUE',
						'Bitrix\Mail\Internals\MessageUploadQueueTable',
						array(
							'=this.ID' => 'ref.ID',
							'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
						),
						array(
							'join_type' => 'INNER',
						)
					),
				),
				'select' => array(
					'*',
					'__' => 'MESSAGE.*',
					'UPLOAD_LOCK' => 'UPLOAD_QUEUE.SYNC_LOCK',
					'UPLOAD_STAGE' => 'UPLOAD_QUEUE.SYNC_STAGE',
					'UPLOAD_ATTEMPTS' => 'UPLOAD_QUEUE.ATTEMPTS',
				),
				'filter' => array(
					'>=UPLOAD_QUEUE.SYNC_STAGE' => 0,
					'<UPLOAD_QUEUE.SYNC_LOCK' => time() - static::getTimeout(),
					'<UPLOAD_QUEUE.ATTEMPTS' => 5,
				),
				'order' => array(
					'UPLOAD_QUEUE.SYNC_LOCK' => 'ASC',
					'UPLOAD_QUEUE.SYNC_STAGE' => 'ASC',
					'UPLOAD_QUEUE.ATTEMPTS' => 'ASC',
				),
			),
			false
		);

		while ($excerpt = $res->fetch())
		{
			$n = $excerpt['UPLOAD_ATTEMPTS'] + 1;
			$interval = min(static::getTimeout() * pow($n, $n), 3600 * 24 * 7);

			if ($excerpt['UPLOAD_LOCK'] > time() - $interval)
			{
				continue;
			}

			$this->syncOutgoingMessage($excerpt);

			if ($this->isTimeQuotaExceeded())
			{
				break;
			}
		}
	}

	protected function syncOutgoingMessage($excerpt)
	{
		global $DB;

		$lockSql = sprintf(
			"UPDATE b_mail_message_upload_queue SET SYNC_LOCK = %u, SYNC_STAGE = %u, ATTEMPTS = ATTEMPTS + 1
				WHERE ID = '%s' AND MAILBOX_ID = %u AND SYNC_LOCK < %u",
			$syncLock = time(),
			max(1, $excerpt['UPLOAD_STAGE']),
			$DB->forSql($excerpt['ID']),
			$excerpt['MAILBOX_ID'],
			$syncLock - static::getTimeout()
		);
		if (!$DB->query($lockSql)->affectedRowsCount())
		{
			return;
		}

		$outgoingBody = $excerpt['__BODY_HTML'];

		$excerpt['__files'] = Mail\Internals\MailMessageAttachmentTable::getList(array(
			'select' => array(
				'ID', 'FILE_ID', 'FILE_NAME',
			),
			'filter' => array(
				'=MESSAGE_ID' => $excerpt['__ID'],
			),
		))->fetchAll();

		$attachments = array();
		if (!empty($excerpt['__files']) && is_array($excerpt['__files']))
		{
			$hostname = \COption::getOptionString('main', 'server_name', 'localhost');
			if (defined('BX24_HOST_NAME') && BX24_HOST_NAME != '')
			{
				$hostname = BX24_HOST_NAME;
			}
			else if (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME != '')
			{
				$hostname = SITE_SERVER_NAME;
			}

			foreach ($excerpt['__files'] as $item)
			{
				$file = \CFile::makeFileArray($item['FILE_ID']);

				$contentId = sprintf(
					'bxacid.%s@%s.mail',
					hash('crc32b', $file['external_id'].$file['size'].$file['name']),
					hash('crc32b', $hostname)
				);

				$attachments[] = array(
					'ID'           => $contentId,
					'NAME'         => $item['FILE_NAME'],
					'PATH'         => $file['tmp_name'],
					'CONTENT_TYPE' => $file['type'],
				);

				$outgoingBody = preg_replace(
					sprintf('/aid:%u/i', $item['ID']),
					sprintf('cid:%s', $contentId),
					$outgoingBody
				);
			}
		}

		foreach (array('FROM', 'REPLY_TO', 'TO', 'CC', 'BCC') as $field)
		{
			$field = sprintf('__FIELD_%s', $field);

			if (mb_strlen($excerpt[$field]) == 255 && '' != $excerpt['__HEADER'] && empty($parsedHeader))
			{
				$parsedHeader = \CMailMessage::parseHeader($excerpt['__HEADER'], LANG_CHARSET);

				$excerpt['__FIELD_FROM'] = $parsedHeader->getHeader('FROM');
				$excerpt['__FIELD_REPLY_TO'] = $parsedHeader->getHeader('REPLY-TO');
				$excerpt['__FIELD_TO'] = $parsedHeader->getHeader('TO');
				$excerpt['__FIELD_CC'] = $parsedHeader->getHeader('CC');
				$excerpt['__FIELD_BCC'] = join(', ', array_merge(
					(array) $parsedHeader->getHeader('X-Original-Rcpt-to'),
					(array) $parsedHeader->getHeader('BCC')
				));
			}

			$excerpt[$field] = explode(',', $excerpt[$field]);

			foreach ($excerpt[$field] as $k => $item)
			{
				unset($excerpt[$field][$k]);

				$address = new Main\Mail\Address($item);

				if ($address->validate())
				{
					if ($address->getName())
					{
						$excerpt[$field][] = sprintf(
							'%s <%s>',
							sprintf('=?%s?B?%s?=', SITE_CHARSET, base64_encode($address->getName())),
							$address->getEmail()
						);
					}
					else
					{
						$excerpt[$field][] = $address->getEmail();
					}
				}
			}

			$excerpt[$field] = join(', ', $excerpt[$field]);
		}

		$outgoingParams = [
			'CHARSET'      => LANG_CHARSET,
			'CONTENT_TYPE' => 'html',
			'ATTACHMENT'   => $attachments,
			'TO'           => $excerpt['__FIELD_TO'],
			'SUBJECT'      => $excerpt['__SUBJECT'],
			'BODY'         => $outgoingBody,
			'HEADER'       => [
				'From'       => $excerpt['__FIELD_FROM'],
				'Reply-To'   => $excerpt['__FIELD_REPLY_TO'],
				'Cc'         => $excerpt['__FIELD_CC'],
				'Bcc'        => $excerpt['__FIELD_BCC'],
				'Message-Id' => sprintf('<%s>', $excerpt['__MSG_ID']),
				'X-Bitrix-Mail-Message-UID' => $excerpt['ID'],
			],
		];

		if(isset($excerpt['__IN_REPLY_TO']))
		{
			$outgoingParams['HEADER']['In-Reply-To'] = sprintf('<%s>', $excerpt['__IN_REPLY_TO']);
		}

		$context = new Main\Mail\Context();
		$context->setCategory(Main\Mail\Context::CAT_EXTERNAL);
		$context->setPriority(Main\Mail\Context::PRIORITY_NORMAL);

		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventKey = $eventManager->addEventHandler(
			'main',
			'OnBeforeMailSend',
			function () use (&$excerpt)
			{
				if ($excerpt['UPLOAD_STAGE'] >= 2)
				{
					return new Main\EventResult(Main\EventResult::ERROR);
				}
			}
		);

		$success = Main\Mail\Mail::send(array_merge(
			$outgoingParams,
			array(
				'TRACK_READ' => array(
					'MODULE_ID' => 'mail',
					'FIELDS'    => array('msgid' => $excerpt['__MSG_ID']),
					'URL_PAGE' => '/pub/mail/read.php',
				),
				//'TRACK_CLICK' => array(
				//	'MODULE_ID' => 'mail',
				//	'FIELDS'    => array('msgid' => $excerpt['__MSG_ID']),
				//),
				'CONTEXT' => $context,
			)
		));

		$eventManager->removeEventHandler('main', 'OnBeforeMailSend', $eventKey);

		if ($excerpt['UPLOAD_STAGE'] < 2 && !$success)
		{
			return false;
		}

		$needUpload = true;
		if ($context->getSmtp() && $context->getSmtp()->getFrom() == $this->mailbox['EMAIL'])
		{
			$needUpload = !in_array('deny_upload', (array) $this->mailbox['OPTIONS']['flags']);
		}

		if ($needUpload)
		{
			if ($excerpt['UPLOAD_STAGE'] < 2)
			{
				Mail\Internals\MessageUploadQueueTable::update(
					array(
						'ID' => $excerpt['ID'],
						'MAILBOX_ID' => $excerpt['MAILBOX_ID'],
					),
					array(
						'SYNC_STAGE' => 2,
						'ATTEMPTS' => 1,
					)
				);
			}

			class_exists('Bitrix\Mail\Helper');

			$message = new Mail\DummyMail(array_merge(
				$outgoingParams,
				array(
					'HEADER' => array_merge(
						$outgoingParams['HEADER'],
						array(
							'To'      => $outgoingParams['TO'],
							'Subject' => $outgoingParams['SUBJECT'],
						)
					),
				)
			));

			if ($this->uploadMessage($message, $excerpt))
			{
				Mail\Internals\MessageUploadQueueTable::delete(array(
					'ID' => $excerpt['ID'],
					'MAILBOX_ID' => $excerpt['MAILBOX_ID'],
				));
			}
		}
		else
		{
			Mail\Internals\MessageUploadQueueTable::update(
				array(
					'ID' => $excerpt['ID'],
					'MAILBOX_ID' => $excerpt['MAILBOX_ID'],
				),
				array(
					'SYNC_STAGE' => -1,
					'SYNC_LOCK' => 0,
				)
			);
		}

		return;
	}

	public function resyncMessage(array &$excerpt)
	{
		$body = $this->downloadMessage($excerpt);
		if (!empty($body))
		{
			return $this->cacheMessage(
				$body,
				array(
					'replaces' => $excerpt['ID'],
				)
			);
		}

		return false;
	}

	public function downloadAttachments(array &$excerpt)
	{
		$body = $this->downloadMessage($excerpt);
		if (!empty($body))
		{
			[,,, $attachments] = \CMailMessage::parseMessage($body, $this->mailbox['LANG_CHARSET']);

			return $attachments;
		}

		return false;
	}

	public function isSupportLazyAttachments()
	{
		foreach ($this->getFilters() as $filter)
		{
			foreach ($filter['__actions'] as $action)
			{
				if (empty($action['LAZY_ATTACHMENTS']))
				{
					return false;
				}
			}
		}

		return true;
	}

	public function getFilters($force = false)
	{
		if (is_null($this->filters) || $force)
		{
			$this->filters = Mail\MailFilterTable::getList(array(
				'filter' => ORM\Query\Query::filter()
					->where('ACTIVE', 'Y')
					->where(
						ORM\Query\Query::filter()->logic('or')
							->where('MAILBOX_ID', $this->mailbox['ID'])
							->where('MAILBOX_ID', null)
					),
				'order' => array(
					'SORT' => 'ASC',
					'ID' => 'ASC',
				),
			))->fetchAll();

			foreach ($this->filters as $k => $item)
			{
				$this->filters[$k]['__actions'] = array();

				$res = \CMailFilter::getFilterList($item['ACTION_TYPE']);
				while ($row = $res->fetch())
				{
					$this->filters[$k]['__actions'][] = $row;
				}
			}
		}

		return $this->filters;
	}

	/**
	 * @deprecated
	 */
	public function resortTree($message = null)
	{
		global $DB;

		$worker = function ($id, $msgId, &$i)
		{
			global $DB;

			$stack = array(
				array(
					array($id, $msgId, false),
				),
			);

			$excerpt = array();

			do
			{
				$level = array_pop($stack);

				while ($level)
				{
					[$id, $msgId, $skip] = array_shift($level);

					if (!$skip)
					{
						$excerpt[] = $id;

						$DB->query(sprintf(
							'UPDATE b_mail_message SET LEFT_MARGIN = %2$u, RIGHT_MARGIN = %3$u WHERE ID = %1$u',
							$id, ++$i, ++$i
						));

						if (!empty($msgId))
						{
							$replies = array();

							$res = Mail\MailMessageTable::getList(array(
								'select' => array(
									'ID',
									'MSG_ID',
								),
								'filter' => array(
									'=MAILBOX_ID' => $this->mailbox['ID'],
									'=IN_REPLY_TO' => $msgId,
								),
								'order' => array(
									'FIELD_DATE' => 'ASC',
								),
							));

							while ($item = $res->fetch())
							{
								if (!in_array($item['ID'], $excerpt))
								{
									$replies[] = array($item['ID'], $item['MSG_ID'], false);
								}
							}

							if ($replies)
							{
								array_unshift($level, array($id, $msgId, true));

								array_push($stack, $level, $replies);
								$i--;

								continue 2;
							}
						}
					}
					else
					{
						$DB->query(sprintf(
							'UPDATE b_mail_message SET RIGHT_MARGIN = %2$u WHERE ID = %1$u',
							$id, ++$i
						));
					}
				}
			}
			while ($stack);
		};

		if (!empty($message))
		{
			if (empty($message['ID']))
			{
				throw new Main\ArgumentException("Argument 'message' is not valid");
			}

			$item = $DB->query(sprintf(
				'SELECT GREATEST(M1, M2) AS I FROM (SELECT
					(SELECT RIGHT_MARGIN FROM b_mail_message WHERE MAILBOX_ID = %1$u AND RIGHT_MARGIN > 0 ORDER BY LEFT_MARGIN ASC LIMIT 1) M1,
					(SELECT RIGHT_MARGIN FROM b_mail_message WHERE MAILBOX_ID = %1$u AND RIGHT_MARGIN > 0 ORDER BY LEFT_MARGIN DESC LIMIT 1) M2
				) M',
				$this->mailbox['ID']
			))->fetch();

			$i = empty($item['I']) ? 0 : $item['I'];

			$worker($message['ID'], $message['MSG_ID'], $i);
		}
		else
		{
			$DB->query(sprintf(
				'UPDATE b_mail_message SET LEFT_MARGIN = 0, RIGHT_MARGIN = 0 WHERE MAILBOX_ID = %u',
				$this->mailbox['ID']
			));

			$i = 0;

			$res = $DB->query(sprintf(
				"SELECT ID, MSG_ID FROM b_mail_message M WHERE MAILBOX_ID = %u AND (
					IN_REPLY_TO IS NULL OR IN_REPLY_TO = '' OR NOT EXISTS (
						SELECT 1 FROM b_mail_message WHERE MAILBOX_ID = M.MAILBOX_ID AND MSG_ID = M.IN_REPLY_TO
					)
				)",
				$this->mailbox['ID']
			));

			while ($item = $res->fetch())
			{
				$worker($item['ID'], $item['MSG_ID'], $i);
			}

			// crosslinked messages
			$query = sprintf(
				'SELECT ID, MSG_ID FROM b_mail_message
					WHERE MAILBOX_ID = %u AND LEFT_MARGIN = 0
					ORDER BY FIELD_DATE ASC LIMIT 1',
				$this->mailbox['ID']
			);
			while ($item = $DB->query($query)->fetch())
			{
				$worker($item['ID'], $item['MSG_ID'], $i);
			}
		}
	}

	/**
	 * @deprecated
	 */
	public function incrementTree($message)
	{
		if (empty($message['ID']))
		{
			throw new Main\ArgumentException("Argument 'message' is not valid");
		}

		if (!empty($message['IN_REPLY_TO']))
		{
			$item = Mail\MailMessageTable::getList(array(
				'select' => array(
					'ID', 'MSG_ID', 'LEFT_MARGIN', 'RIGHT_MARGIN',
				),
				'filter' => array(
					'=MAILBOX_ID' => $this->mailbox['ID'],
					'=MSG_ID' => $message['IN_REPLY_TO'],
				),
				'order' => array(
					'LEFT_MARGIN' => 'ASC',
				),
			))->fetch();

			if (!empty($item))
			{
				$message = $item;

				$item = Mail\MailMessageTable::getList(array(
					'select' => array(
						'ID', 'MSG_ID',
					),
					'filter' => array(
						'=MAILBOX_ID' => $this->mailbox['ID'],
						'<LEFT_MARGIN' => $item['LEFT_MARGIN'],
						'>RIGHT_MARGIN' => $item['RIGHT_MARGIN'],
					),
					'order' => array(
						'LEFT_MARGIN' => 'ASC',
					),
					'limit' => 1,
				))->fetch();

				if (!empty($item))
				{
					$message = $item;
				}
			}
		}

		$this->resortTree($message);
	}

	abstract public function checkMessagesForExistence($dirPath ='INBOX',$UIDs = []);
	abstract public function resyncIsOldStatus();
	abstract public function syncFirstDay();
	abstract protected function syncInternal();
	abstract public function listDirs($pattern, $useDb = false);
	abstract public function uploadMessage(Main\Mail\Mail $message, array &$excerpt);
	abstract public function downloadMessage(array &$excerpt);
	abstract public function syncMessages($mailboxID, $dirPath, $UIDs);
	abstract public function isAuthenticated();

	public function getErrors()
	{
		return $this->errors;
	}

	public function getWarnings()
	{
		return $this->warnings;
	}

	public function getLastSyncResult()
	{
		return $this->lastSyncResult;
	}

	protected function setLastSyncResult(array $data)
	{
		$this->lastSyncResult = array_merge($this->lastSyncResult, $data);
	}

	public function getDirsHelper()
	{
		if (!$this->dirsHelper)
		{
			$this->dirsHelper = new Mail\Helper\MailboxDirectoryHelper($this->mailbox['ID']);
		}

		return $this->dirsHelper;
	}

	public function activateSync()
	{
		$options = $this->mailbox['OPTIONS'];

		if (!isset($options['activateSync']) || $options['activateSync'] === true)
		{
			return false;
		}

		$entity = MailboxTable::getEntity();
		$connection = $entity->getConnection();

		$options['activateSync'] = true;

		$query = sprintf(
			'UPDATE %s SET %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			$connection->getSqlHelper()->prepareUpdate($entity->getDbTableName(), [
				'SYNC_LOCK' => 0,
				'OPTIONS'   => serialize($options),
			])[0],
			Query::buildFilterSql(
				$entity,
				[
					'ID' => $this->mailbox['ID']
				]
			)
		);

		return $connection->query($query);
	}

	public function notifyNewMessages()
	{
		if (Main\Loader::includeModule('im'))
		{
			$lastSyncResult = $this->getLastSyncResult();
			$count = $lastSyncResult['newMessagesNotify'];
			$newMessageId = $lastSyncResult['newMessageId'];
			$message = null;

			if ($count < 1)
			{
				return;
			}

			if ($newMessageId > 0 && $count === 1)
			{
				$message = Mail\MailMessageTable::getByPrimary($newMessageId)->fetch();

				if (!empty($message))
				{
					Mail\Helper\Message::prepare($message);
				}
			}

			Mail\Integration\Im\Notification::add(
				$this->mailbox['USER_ID'],
				'new_message',
				array(
					'mailboxOwnerId' => $this->mailbox['USER_ID'],
					'mailboxId' => $this->mailbox['ID'],
					'count' => $count,
					'message' => $message,
				)
			);
		}
	}

	/*
	Returns the minimum time between possible re-synchronization
	The time is taken from the option 'max_execution_time', but no more than static::SYNC_TIMEOUT
	*/
	final public static function getTimeout()
	{
		return min(max(0, ini_get('max_execution_time')) ?: static::SYNC_TIMEOUT, static::SYNC_TIMEOUT);
	}
}
