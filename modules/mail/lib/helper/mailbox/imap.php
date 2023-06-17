<?php

namespace Bitrix\Mail\Helper\Mailbox;

use Bitrix\Mail;
use Bitrix\Mail\Helper\MailboxDirectoryHelper;
use Bitrix\Mail\MailboxDirectory;
use Bitrix\Main;
use Bitrix\Main\Text\Emoji;

class Imap extends Mail\Helper\Mailbox
{
	const MESSAGE_PARTS_TEXT = 1;
	const MESSAGE_PARTS_ATTACHMENT = 2;
	const MESSAGE_PARTS_ALL = -1;
	const MAXIMUM_SYNCHRONIZATION_LENGTHS_OF_INTERVALS = [
		100,
		50,
		25,
		12,
		6,
		3,
		1
	];

	protected function getMaximumSynchronizationLengthsOfIntervals($num)
	{
		if(isset(self::MAXIMUM_SYNCHRONIZATION_LENGTHS_OF_INTERVALS[$num]))
		{
			return self::MAXIMUM_SYNCHRONIZATION_LENGTHS_OF_INTERVALS[$num];
		}
		else
		{
			return self::MAXIMUM_SYNCHRONIZATION_LENGTHS_OF_INTERVALS[count(self::MAXIMUM_SYNCHRONIZATION_LENGTHS_OF_INTERVALS)-1];
		}
	}

	protected $client;

	protected function __construct($mailbox)
	{
		parent::__construct($mailbox);

		$this->client = new Mail\Imap(
			$mailbox['SERVER'],
			$mailbox['PORT'],
			$mailbox['USE_TLS'] == 'Y' || $mailbox['USE_TLS'] == 'S',
			$mailbox['USE_TLS'] == 'Y',
			$mailbox['LOGIN'],
			$mailbox['PASSWORD']
		);
	}

	public function getSyncStatusTotal()
	{
		$currentDir = null;

		if (!empty($this->syncParams['currentDir']))
		{
			$currentDir = $this->syncParams['currentDir'];
		}

		$totalSyncDirs = count($this->getDirsHelper()->getSyncDirs());
		$currentSyncDirPath = MailboxDirectoryHelper::getCurrentSyncDir();
		$currentSyncDir = $this->getDirsHelper()->getDirByPath($currentSyncDirPath);

		if ($totalSyncDirs > 0 && $currentSyncDir != null)
		{
			$currentSyncDirMessages = Mail\MailMessageUidTable::getList([
				'select' => [
					new Main\Entity\ExpressionField('TOTAL', 'COUNT(1)'),
				],
				'filter' => [
					'=MAILBOX_ID'  => $this->mailbox['ID'],
					'=DIR_MD5'     => $currentSyncDir->getDirMd5(),
					'==DELETE_TIME' => 0,
				],
			])->fetch();

			$currentSyncDirMessagesCount = (int)$currentSyncDirMessages['TOTAL'];
			$currentSyncDirMessagesAll = (int)$currentSyncDir->getMessageCount();
			$currentSyncDirPosition = $this->getDirsHelper()->getCurrentSyncDirPositionByDefault(
				$currentSyncDir->getPath(),
				$currentDir
			);

			if ($currentDir != null) {
				$totalSyncDirs--;
			}

			if ($currentSyncDirMessagesAll <= 0)
			{
				$progress = ($currentSyncDirPosition + 1) / $totalSyncDirs;
			}
			else
			{
				$progress = ($currentSyncDirMessagesCount / $currentSyncDirMessagesAll + $currentSyncDirPosition) / $totalSyncDirs;
			}

			return $progress;
		}
		else
		{
			return parent::getSyncStatus();
		}
	}

	public function getSyncStatus()
	{
		if (!empty($this->syncParams['currentDir']))
		{
			$currentSyncDir = $this->getDirsHelper()->getDirByPath($this->syncParams['currentDir']);
		}

		if (!empty($currentSyncDir))
		{
			$currentSyncDirMessages = Mail\MailMessageUidTable::getList([
				'select' => [
					new Main\Entity\ExpressionField('TOTAL', 'COUNT(1)'),
				],
				'filter' => [
					'=MAILBOX_ID'  => $this->mailbox['ID'],
					'=DIR_MD5'     => $currentSyncDir->getDirMd5(),
					'==DELETE_TIME' => 0,
				],
			])->fetch();

			$currentSyncDirMessagesCount = (int) $currentSyncDirMessages['TOTAL'];
			$currentSyncDirMessagesAll = (int) $currentSyncDir->getMessageCount();

			if ($currentSyncDirMessagesAll > 0)
			{
				return ($currentSyncDirMessagesCount / $currentSyncDirMessagesAll);
			}
		}

		return 1;
	}

	public function checkMessagesForExistence($dirPath ='INBOX',$UIDs = [])
	{
		if(!empty($UIDs))
		{
			/*
				If a non-existing id gets among the existing ones,
				some mailers may issue an error (instead of issuing existing messages),
				then we will think that the letters disappeared on the mail service,
				although in fact there were existing messages among them.
				But the messages can be deleted legally,
				it's just that the mail has not been resynchronized for a long time.
				In this case, small samples are needed in order to catch existing messages in any of them.
			*/
			$chunks = array_chunk($UIDs, 5);

			$existingMessage = NULL;

			foreach ($chunks as $chunk)
			{
				$messages = $this->client->fetch(
					true,
					$dirPath,
					join(',', $chunk),
					'(UID FLAGS)',
					$error,
					'list'
				);

				if(!($messages === false || empty($messages)))
				{
					foreach ($messages as $item)
					{
						if(!isset($item['FLAGS']))
						{
							continue;
						}

						$messageDeleted = preg_grep('/^ \x5c Deleted $/ix', $item['FLAGS']) ? true : false;

						if(!$messageDeleted)
						{
							$existingMessage = $item;
							break;
						}
					}
				}
			}

			if(!is_null($existingMessage))
			{
				if(isset($existingMessage['UID']))
				{
					return $existingMessage['UID'];
				}
			}
		}

		return false;
	}

	public function resyncIsOldStatus()
	{
		$mailboxID = $this->mailbox['ID'];
		$directoryHelper = new Mail\Helper\MailboxDirectoryHelper($mailboxID);
		$syncDirs = $directoryHelper->getSyncDirs();

		$numberOfUnSynchronizedDirs = count($syncDirs);

		foreach ($syncDirs as $dir)
		{
			$dirPath = $dir->getPath();
			$dirId = $dir->getId();

			$internalDate = \Bitrix\Mail\Helper::getLastDeletedOldMessageInternaldate($mailboxID, $dirPath);

			$keyRow = [
				'MAILBOX_ID' => $mailboxID,
				'ENTITY_TYPE' => 'DIR',
				'ENTITY_ID' => $dirId,
				'PROPERTY_NAME' => 'SYNC_IS_OLD_STATUS',
			];

			$filter = [
				'=MAILBOX_ID' => $keyRow['MAILBOX_ID'],
				'=ENTITY_TYPE' => $keyRow['ENTITY_TYPE'],
				'=ENTITY_ID' => $keyRow['ENTITY_ID'],
				'=PROPERTY_NAME' => $keyRow['PROPERTY_NAME'],
			];

			$startValue = 'started_for_date_'.$internalDate;

			if(Mail\Internals\MailEntityOptionsTable::getCount($filter))
			{
				if(Mail\Internals\MailEntityOptionsTable::getList([
						'select' => [
							'VALUE',
						],
						'filter' => $filter,
					])->fetchAll()[0]['VALUE'] !== 'completed')
				{
					Mail\Internals\MailEntityOptionsTable::update(
						$keyRow,
						['VALUE' => $startValue]
					);

					$synchronizationSuccess = $this->setIsOldStatusesLowerThan($internalDate,$dirPath,$mailboxID);

					if($synchronizationSuccess)
					{
						Mail\Internals\MailEntityOptionsTable::update(
							$keyRow,
							['VALUE' => 'completed']
						);
						$numberOfUnSynchronizedDirs--;
					}
				}
				else
				{
					$numberOfUnSynchronizedDirs--;
				}
			}
			else
			{
				$fields = $keyRow;
				$fields['VALUE'] = $startValue;
				Mail\Internals\MailEntityOptionsTable::add(
					$fields
				);

				$synchronizationSuccess = $this->setIsOldStatusesLowerThan($internalDate,$dirPath,$mailboxID);

				if($synchronizationSuccess)
				{
					\Bitrix\Mail\Internals\MailEntityOptionsTable::update(
						$keyRow,
						['VALUE' => 'completed']
					);
					$numberOfUnSynchronizedDirs--;
				}
			}
		}

		if($numberOfUnSynchronizedDirs === 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function syncFirstDay()
	{
		$mailboxID = $this->mailbox['ID'];
		$directoryHelper = new Mail\Helper\MailboxDirectoryHelper($mailboxID);
		$syncDirs = $directoryHelper->getSyncDirs();

		$numberOfUnSynchronizedDirs = count($syncDirs);

		foreach ($syncDirs as $dir)
		{
			$dirPath = $dir->getPath();
			$dirId = $dir->getId();

			$internalDate = \Bitrix\Mail\Helper::getStartInternalDateForDir($mailboxID,$dirPath);

			$keyRow = [
				'MAILBOX_ID' => $mailboxID,
				'ENTITY_TYPE' => 'DIR',
				'ENTITY_ID' => $dirId,
				'PROPERTY_NAME' => 'SYNC_FIRST_DAY',
			];

			$filter = [
				'=MAILBOX_ID' => $keyRow['MAILBOX_ID'],
				'=ENTITY_TYPE' => $keyRow['ENTITY_TYPE'],
				'=ENTITY_ID' => $keyRow['ENTITY_ID'],
				'=PROPERTY_NAME' => $keyRow['PROPERTY_NAME'],
			];

			$startValue = 'started_for_date_'.$internalDate;

			if(Mail\Internals\MailEntityOptionsTable::getCount($filter))
			{
				if(Mail\Internals\MailEntityOptionsTable::getList([
						'select' => [
							'VALUE',
						],
						'filter' => $filter,
					])->fetchAll()[0]['VALUE'] !== 'completed')
				{
					Mail\Internals\MailEntityOptionsTable::update(
						$keyRow,
						['VALUE' => $startValue]
					);

					\CTimeZone::Disable();
					$synchronizationSuccess = $this->syncDirForSpecificDay($dirPath,$internalDate);
					\CTimeZone::Enable();

					if($synchronizationSuccess)
					{
						Mail\Internals\MailEntityOptionsTable::update(
							$keyRow,
							['VALUE' => 'completed']
						);
						$numberOfUnSynchronizedDirs--;
					}
				}
				else
				{
					$numberOfUnSynchronizedDirs--;
				}
			}
			else
			{
				$fields = $keyRow;
				$fields['VALUE'] = $startValue;
				Mail\Internals\MailEntityOptionsTable::add(
					$fields
				);

				\CTimeZone::Disable();
				$synchronizationSuccess = $this->syncDirForSpecificDay($dirPath,$internalDate);
				\CTimeZone::Enable();

				if($synchronizationSuccess)
				{
					\Bitrix\Mail\Internals\MailEntityOptionsTable::update(
						$keyRow,
						['VALUE' => 'completed']
					);
					$numberOfUnSynchronizedDirs--;
				}
			}
		}

		if($numberOfUnSynchronizedDirs === 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	protected function syncInternal()
	{
		$syncReport = $this->syncMailbox();
		if (false === $syncReport['syncCount'])
		{
			$this->errors = new Main\ErrorCollection($this->client->getErrors()->toArray());
		}

		return $syncReport;
	}

	protected function createMessage(Main\Mail\Mail $message, array $fields = array())
	{
		$dirPath = $this->getDirsHelper()->getOutcomePath() ?: 'INBOX';

		$fields = array_merge(
			$fields,
			array(
				'DIR_MD5'  => md5($dirPath),
				'DIR_UIDV' => 0,
				'MSG_UID'  => 0,
			)
		);

		return parent::createMessage($message, $fields);
	}

	public function syncOutgoing()
	{
		$this->cacheDirs();

		parent::syncOutgoing();
	}

	public function uploadMessage(Main\Mail\Mail $message, array &$excerpt = null)
	{
		$dirPath = $this->getDirsHelper()->getOutcomePath() ?: 'INBOX';

		$data = $this->client->select($dirPath, $error);

		if (false === $data)
		{
			$this->errors = new Main\ErrorCollection($this->client->getErrors()->toArray());

			return false;
		}

		if (!empty($excerpt['__unique_headers']))
		{
			if ($this->client->searchByHeader(false, $dirPath, $excerpt['__unique_headers'], $error))
			{
				return false;
			}
		}

		if (!empty($excerpt['ID']))
		{
			class_exists('Bitrix\Mail\Helper');

			Mail\DummyMail::overwriteMessageHeaders(
				$message,
				array(
					'X-Bitrix-Mail-Message-UID' => $excerpt['ID'],
				)
			);
		}

		$result = $this->client->append(
			$dirPath,
			array('\Seen'),
			new \DateTime,
			sprintf(
				'%1$s%3$s%3$s%2$s',
				$message->getHeaders(),
				$message->getBody(),
				$message->getMailEol()
			),
			$error
		);

		if (false === $result)
		{
			$this->errors = new Main\ErrorCollection($this->client->getErrors()->toArray());

			return false;
		}

		$this->syncDir($dirPath);

		return $result;
	}

	public function downloadMessage(array &$excerpt)
	{
		if (empty($excerpt['MSG_UID']) || empty($excerpt['DIR_MD5']))
		{
			return false;
		}

		$dirPath = $this->getDirsHelper()->getDirPathByHash($excerpt['DIR_MD5']);
		if (empty($dirPath))
		{
			return false;
		}

		$body = $this->client->fetch(true, $dirPath, $excerpt['MSG_UID'], '(BODY.PEEK[])', $error);

		if (false === $body)
		{
			$this->errors = new Main\ErrorCollection($this->client->getErrors()->toArray());

			return false;
		}

		return empty($body['BODY[]']) ? null : $body['BODY[]'];
	}

	public function downloadMessageParts(array &$excerpt, Mail\Imap\BodyStructure $bodystructure, $flags = Imap::MESSAGE_PARTS_ALL)
	{
		if (empty($excerpt['MSG_UID']) || empty($excerpt['DIR_MD5']))
		{
			return false;
		}

		$dirPath = $this->getDirsHelper()->getDirPathByHash($excerpt['DIR_MD5']);
		if (empty($dirPath))
		{
			return false;
		}

		$rfc822Parts = array();

		$select = array_filter(
			$bodystructure->traverse(
				function (Mail\Imap\BodyStructure $item) use ($flags, &$rfc822Parts)
				{
					if ($item->isMultipart())
					{
						return;
					}

					$isTextItem = $item->isBodyText();
					if ($flags & ($isTextItem ? Imap::MESSAGE_PARTS_TEXT : Imap::MESSAGE_PARTS_ATTACHMENT))
					{
						// due to yandex bug
						if ('message' === $item->getType() && 'rfc822' === $item->getSubtype())
						{
							$rfc822Parts[] = $item;

							return sprintf('BODY.PEEK[%1$s.HEADER] BODY.PEEK[%1$s.TEXT]', $item->getNumber());
						}

						return sprintf('BODY.PEEK[%1$s.MIME] BODY.PEEK[%1$s]', $item->getNumber());
					}
				},
				true
			)
		);

		if (empty($select))
		{
			return array();
		}

		$parts = $this->client->fetch(
			true,
			$dirPath,
			$excerpt['MSG_UID'],
			sprintf('(%s)', join(' ', $select)),
			$error
		);

		if (false === $parts)
		{
			$this->errors = new Main\ErrorCollection($this->client->getErrors()->toArray());

			return false;
		}

		foreach ($rfc822Parts as $item)
		{
			$headerKey = sprintf('BODY[%s.HEADER]', $item->getNumber());
			$bodyKey = sprintf('BODY[%s.TEXT]', $item->getNumber());

			if (array_key_exists($headerKey, $parts) || array_key_exists($bodyKey, $parts))
			{
				$partMime = 'Content-Type: message/rfc822';
				if (!empty($item->getParams()['name']))
				{
					$partMime .= sprintf('; name="%s"', $item->getParams()['name']);
				}

				if (!empty($item->getDisposition()[0]))
				{
					$partMime .= sprintf("\r\nContent-Disposition: %s", $item->getDisposition()[0]);
					if (!empty($item->getDisposition()[1]) && is_array($item->getDisposition()[1]))
					{
						foreach ($item->getDisposition()[1] as $name => $value)
						{
							$partMime .= sprintf('; %s="%s"', $name, $value);
						}
					}
				}

				$parts[sprintf('BODY[%1$s.MIME]', $item->getNumber())] = $partMime;
				$parts[sprintf('BODY[%1$s]', $item->getNumber())] = sprintf(
					"%s\r\n\r\n%s",
					rtrim($parts[$headerKey], "\r\n"),
					ltrim($parts[$bodyKey], "\r\n")
				);

				unset($parts[$headerKey], $parts[$bodyKey]);
			}
		}

		return $parts;
	}

	public function cacheDirs()
	{
		static $lastCacheSession;

		if ($this->session === $lastCacheSession)
		{
			return;
		}

		$dirs = $this->client->listex('', '%', $error);
		if (false === $dirs)
		{
			$this->errors = new Main\ErrorCollection($this->client->getErrors()->toArray());

			return false;
		}

		$list = [];
		foreach ($dirs as $item)
		{
			$parts = explode($item['delim'], $item['name']);

			$item['path'] = $item['name'];
			$item['name'] = end($parts);

			$list[$item['name']] = $item;
		}

		$this->getDirsHelper()->syncDbDirs($list);

		$lastCacheSession = $this->session;
	}

	public function listDirs($pattern, $useDb = false)
	{
		$dirs = $this->client->listex('', $pattern, $error);
		if (false === $dirs)
		{
			$this->errors = new Main\ErrorCollection($this->client->getErrors()->toArray());

			return false;
		}

		$list = [];

		foreach ($dirs as $dir)
		{
			$parts = explode($dir['delim'], $dir['name']);

			$dir['path'] = $dir['name'];
			$dir['name'] = end($parts);
			$list[$dir['path']] = $dir;
		}

		return $list;
	}

	public function cacheMeta()
	{
		return $this->getDirsHelper()->getSyncDirs();
	}

	protected function getFolderToMessagesMap($messages)
	{
		if (isset($messages['MSG_UID']))
		{
			$messages = [$messages];
		}
		$data = [];
		$result = new Main\Result();
		foreach ($messages as $message)
		{
			$id = $message['MSG_UID'];
			$folderFrom = $this->getDirsHelper()->getDirPathByHash($message['DIR_MD5']);
			$data[$folderFrom][] = $id;
			$results[$folderFrom][] = $message;
		}
		return $result->setData($data);
	}

	public function markUnseen($messages)
	{
		$result = $this->getFolderToMessagesMap($messages);
		foreach ($result->getData() as $folderFrom => $ids)
		{
			$result = $this->client->unseen($ids, $folderFrom);
			if (!$result->isSuccess() || !$this->client->getErrors()->isEmpty())
			{
				break;
			}
		}
		return $result;
	}

	public function markSeen($messages)
	{
		$result = $this->getFolderToMessagesMap($messages);
		foreach ($result->getData() as $folderFrom => $ids)
		{
			$result = $this->client->seen($ids, $folderFrom);
			if (!$result->isSuccess() || !$this->client->getErrors()->isEmpty())
			{
				break;
			}
		}
		return $result;
	}

	public function moveMailsToFolder($messages, $folderTo)
	{
		$result = $this->getFolderToMessagesMap($messages);
		$moveResult = new Main\Result();
		foreach ($result->getData() as $folderFrom => $ids)
		{
			$moveResult = $this->client->moveMails($ids, $folderFrom, $folderTo);
			if (!$moveResult->isSuccess() || !$this->client->getErrors()->isEmpty())
			{
				break;
			}
		}

		return $moveResult;
	}

	public function deleteMails($messages)
	{
		$result = $this->getFolderToMessagesMap($messages);

		foreach ($result->getData() as $folderName => $messageId)
		{
			$result = $this->client->delete($messageId, $folderName);
		}

		return $result;
	}

	public function syncMailbox()
	{
		if (!$this->client->authenticate($error))
		{
			return false;
		}

		$syncReport = [
			'syncCount'=>0,
			'reSyncCount' => 0,
			'reSyncStatus' => false,
		];

		$this->cacheDirs();

		$currentDir = null;

		if (!empty($this->syncParams['currentDir']))
		{
			$currentDir = $this->syncParams['currentDir'];
		}

		$dirsSync = $this->getDirsHelper()->getSyncDirsOrderByTime($currentDir);

		if (empty($dirsSync))
		{
			return $syncReport;
		}

		$lastDir = $this->getDirsHelper()->getLastSyncDirByDefault($currentDir);

		foreach ($dirsSync as $item)
		{
			MailboxDirectoryHelper::setCurrentSyncDir($item->getPath());

			$syncReport['syncCount'] += $this->syncDir($item->getPath());

			if ($this->isTimeQuotaExceeded())
			{
				break;
			}

			MailboxDirectory::updateSyncTime($item->getId(), time());

			if ($lastDir != null && $item->getPath() == $lastDir->getPath())
			{
				MailboxDirectoryHelper::setCurrentSyncDir('');
				break;
			}
		}

		$this->setLastSyncResult(['updatedMessages' => 0, 'deletedMessages' => 0]);

		if (!$this->isTimeQuotaExceeded())
		{
			/*	Mark emails from unsynchronized folders (unchecked) for deletion

				It is impossible to check these filters for the legality of deleting messages,
				since:
				1) messages do not disappear from the original mailbox
				2) messages that fall under filters are in different folders,
				and the check goes through one folder.
			*/
			$result = $this->unregisterMessages([
				'!@DIR_MD5' => array_map(
					'md5',
					$this->getDirsHelper()->getSyncDirsPath(true)
				),
			],
			[
				'info' => 'disabled directory synchronization in Bitrix',
			],
			true);

			$countDeleted = $result ? $result->getCount() : 0;

			$this->lastSyncResult['deletedMessages'] += $countDeleted;

			$successfulReSyncCount = 0;

			if (!empty($this->syncParams['full']))
			{
				foreach ($dirsSync as $item)
				{
					$reSyncReport = $this->resyncDir($item->getPath());

					if($reSyncReport['complete'])
					{
						$syncReport['reSyncCount']++;
					}
					if ($this->isTimeQuotaExceeded())
					{
						break;
					}
				}

				if($syncReport['reSyncCount'] === count($dirsSync))
				{
					$syncReport['reSyncStatus'] = true;
				}
			}
		}

		return $syncReport;
	}

	public function syncDir($dirPath)
	{
		$dir = $this->getDirsHelper()->getDirByPath($dirPath);

		if (!$dir || !$dir->isSync())
		{
			return false;
		}

		if ($dir->isSyncLock() || !$dir->startSyncLock())
		{
			return null;
		}

		$result = $this->syncDirInternal($dir);

		$dir->stopSyncLock();

		$this->lastSyncResult['newMessages'] += $result;
		if (!$dir->isTrash() && !$dir->isSpam() && !$dir->isDraft() && !$dir->isOutcome())
		{
			$this->lastSyncResult['newMessagesNotify'] += $result;
		}

		return $result;
	}

	protected function setIsOldStatusesLowerThan($internalDate, $dirPath, $mailboxId)
	{
		if($internalDate === false)
		{
			return true;
		}

		$dirsHelper = new Mail\Helper\MailboxDirectoryHelper($mailboxId);
		$dir = $dirsHelper->getDirByPath($dirPath);

		$entity = \Bitrix\Mail\MailMessageUidTable::getEntity();
		$connection = $entity->getConnection();

		$where = sprintf(
			'(%s)',
			Main\Entity\Query::buildFilterSql(
					$entity,
					[
						'<=INTERNALDATE' => $internalDate,
						'=DIR_MD5'	=>	$dir->getDirMd5(),
						'=MAILBOX_ID'	=>	$mailboxId,
						'!=IS_OLD' => 'Y',
					]
				)
			);

		$connection->query(sprintf(
			'UPDATE %s SET IS_OLD = "Y", IS_SEEN = "Y" WHERE %s LIMIT 1000',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			$where
		));

		if($connection->getAffectedRowsCount() === 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $mailboxID
	 * @param $dirPath
	 * @param $UIDs
	 * @return bool - success status
	 * @throws Main\DB\SqlQueryException
	 * @throws Main\SystemException
	 */
	public function syncMessages($mailboxID, $dirPath, $UIDs)
	{
		$meta = $this->client->select($dirPath, $error);
		$uidtoken = $meta['uidvalidity'];

		//checking the dir for existence or authentication failed
		if (false === $meta)
		{
			return true;
		}

		$dirsHelper = new Mail\Helper\MailboxDirectoryHelper($mailboxID);

		$dir = $dirsHelper->getDirByPath($dirPath);

		$chunks = array_chunk($UIDs, 10);

		$entity = Mail\MailMessageUidTable::getEntity();
		$connection = $entity->getConnection();

		foreach ($chunks as $chunk)
		{
			$connection->query(sprintf(
				'DELETE FROM %s WHERE %s',
				Mail\MailMessageUidTable::getTableName(),
				Main\Entity\Query::buildFilterSql(
					$entity,
					[
						'@MSG_UID' => $chunk,
						'=MESSAGE_ID' => 0,
						'=MAILBOX_ID' => $mailboxID,
						'=DIR_MD5'	=>	$dir->getDirMd5()
					]
				)
			));

			$messages = $this->client->fetch(
				true,
				$dirPath,
				join(',', $chunk),
				'(UID FLAGS INTERNALDATE RFC822.SIZE BODYSTRUCTURE BODY.PEEK[HEADER])',
				$error,
				'list'
			);

			if (empty($messages))
			{
				if (false === $messages)
				{
					$this->warnings->add($this->client->getErrors()->toArray());
					return true;
				}
				break;
			}

			$this->parseHeaders($messages);

			$this->blacklistMessages($dir->getPath(), $messages);

			$this->removeExistingMessagesFromSynchronizationList($dir->getPath(), $uidtoken, $messages);

			foreach ($messages as &$message)
			{
				$this->fillMessageFields($message, $dir->getPath(), $uidtoken);
			}

			$this->linkWithExistingMessages($messages);

			foreach ($messages as $item)
			{
				$isOutgoing = false;

				if(empty($item['__replaces']))
				{
					$outgoingMessageId = $this->selectOutgoingMessageIdFromHeader($item);

					if($outgoingMessageId)
					{
						$item['__replaces'] = $outgoingMessageId;
						$isOutgoing = true;
					}
				}

				$hashesMap = [];
				$this->syncMessage($dir->getPath(), $item, $hashesMap, true, $isOutgoing);

				if ($this->isTimeQuotaExceeded())
				{
					return false;
				}
			}

		}
		return true;
	}

	public function isAuthenticated(): bool
	{
		if (\Bitrix\Mail\Helper::getImapUnseen($this->mailbox, 'inbox') === false)
		{
			return false;
		}

		return true;
	}

	public function syncDirForSpecificDay($dirPath, $internalDate)
	{
		if($internalDate === false)
		{
			return true;
		}

		$mailboxID = $this->mailbox['ID'];

		$UIDsOnService = \Bitrix\Mail\Helper::getImapUIDsForSpecificDay($mailboxID, $dirPath, $internalDate);

		return $this->syncMessages($mailboxID, $dirPath, $UIDsOnService);
	}

	protected function syncDirInternal($dir)
	{
		$messagesSynced = 0;

		$meta = $this->client->select($dir->getPath(), $error);

		if (false === $meta)
		{
			$this->warnings->add($this->client->getErrors()->toArray());

			if ($this->client->isExistsDir($dir->getPath(), $error) === false)
			{
				$this->getDirsHelper()->removeDirsLikePath([$dir]);
			}

			return false;
		}

		$this->getDirsHelper()->updateMessageCount($dir->getId(), $meta['exists']);

		$intervalSynchronizationAttempts = 0;

		while ($range = $this->getSyncRange($dir->getPath(), $uidtoken, $intervalSynchronizationAttempts))
		{
			$reverse = $range[0] > $range[1];

			sort($range);

			$messages = $this->client->fetch(
				true,
				$dir->getPath(),
				join(':', $range),
				'(UID FLAGS INTERNALDATE RFC822.SIZE BODYSTRUCTURE BODY.PEEK[HEADER])',
				$error
			);

			$fetchErrors=$this->client->getErrors();
			$errorReceivingMessages = $fetchErrors->getErrorByCode(210) !== null;
			$failureDueToDataVolume = $fetchErrors->getErrorByCode(104) !== null;

			if (empty($messages))
			{
				if (false === $messages)
				{
					if($errorReceivingMessages && !$failureDueToDataVolume)
					{
						/*
						 	 Skip the intervals where all the messages were broken
						*/
						return $messagesSynced;
					}
					elseif($failureDueToDataVolume && $intervalSynchronizationAttempts < count(self::MAXIMUM_SYNCHRONIZATION_LENGTHS_OF_INTERVALS) - 1 )
					{
						$intervalSynchronizationAttempts++;
						continue;
						/*
							Trying to resynchronize by reducing the interval
						*/
					}
					else
					{
						/*
							Fatal errors in which we cannot perform synchronization
						*/
						$this->warnings->add($fetchErrors->toArray());
						return false;
					}
				}
				break;
			}
			else
			{
				$intervalSynchronizationAttempts = 0;
			}

			$reverse ? krsort($messages) : ksort($messages);

			$this->parseHeaders($messages);

			$this->blacklistMessages($dir->getPath(), $messages);

			$this->removeExistingMessagesFromSynchronizationList($dir->getPath(), $uidtoken, $messages);

			foreach ($messages as &$message)
			{
				$this->fillMessageFields($message, $dir->getPath(), $uidtoken);
			}

			$this->linkWithExistingMessages($messages);

			$hashesMap = [];

			//To display new messages(grid reload) until synchronization is complete
			$numberOfMessagesInABatch = 1;
			$numberLeftToFillTheBatch = $numberOfMessagesInABatch;

			foreach ($messages as $item)
			{
				$isOutgoing = false;

				if(empty($item['__replaces']))
				{
					$outgoingMessageId = $this->selectOutgoingMessageIdFromHeader($item);

					if($outgoingMessageId)
					{
						$item['__replaces'] = $outgoingMessageId;
						$isOutgoing = true;
					}
				}

				if ($this->syncMessage($dir->getPath(), $item, $hashesMap, false, $isOutgoing))
				{
					$this->lastSyncResult['newMessageId'] = end($hashesMap);
					$messagesSynced++;

					$numberLeftToFillTheBatch--;
					if($numberLeftToFillTheBatch === 0 and Main\Loader::includeModule('pull'))
					{
						$numberOfMessagesInABatch *= 2;
						$numberLeftToFillTheBatch = $numberOfMessagesInABatch;
						\CPullWatch::addToStack(
							'mail_mailbox_' . $this->mailbox['ID'],
							[
								'params' => [
									'dir' => $dir->getPath(),
									'mailboxId' => $this->mailbox['ID'],
								],
								'module_id' => 'mail',
								'command' => 'new_message_is_synchronized',
							]
						);
						\Bitrix\Pull\Event::send();
					}
				}

				if ($this->isTimeQuotaExceeded())
				{
					break 2;
				}
			}
		}

		if (false === $range)
		{
			$this->warnings->add($this->client->getErrors()->toArray());

			return false;
		}

		return $messagesSynced;
	}

	public function resyncDir($dirPath, $numberForResync = false)
	{
		$dir = $this->getDirsHelper()->getDirByPath($dirPath);

		if (!$dir || !$dir->isSync())
		{
			return false;
		}

		$report = [
			'complete' => false,
			'dir' => $dir->getPath(),
			'updated' => -$this->lastSyncResult['updatedMessages'],
			'deleted' => -$this->lastSyncResult['deletedMessages'],
		];

		$result = $this->resyncDirInternal($dir,$numberForResync);

		$report['updated'] += $this->lastSyncResult['updatedMessages'];
		$report['deleted'] += $this->lastSyncResult['deletedMessages'];

		if (false === $result)
		{
			$report['errors'] = $this->client->getErrors()->toArray();
		}
		else
		{
			if($this->isTimeQuotaExceeded())
			{
				$report['errors'] = [
					'isTimeQuotaExceeded'
				];
			}
			else
			{
				$report['complete'] = true;
			}
		}

		return $report;
	}

	protected function resyncDirInternal($dir, $numberForResync = false)
	{
		$meta = $this->client->select($dir->getPath(), $error);
		if (false === $meta)
		{
			$this->warnings->add($this->client->getErrors()->toArray());

			return false;
		}

		$uidtoken = $meta['uidvalidity'];

		if ($meta['exists'] > 0)
		{
			if ($uidtoken > 0)
			{
				$result = $this->unregisterMessages(
					array(
						'=DIR_MD5'  => md5($dir->getPath(true)),
						'<DIR_UIDV' => $uidtoken,
					),
					[
						'info' => 'the directory has been deleted',
					]
				);

				$countDeleted = $result ? $result->getCount() : 0;

				$this->lastSyncResult['deletedMessages'] += $countDeleted;
			}
		}
		else
		{
			if ($this->client->ensureEmpty($dir->getPath(), $error))
			{
				$result = $this->unregisterMessages(
					array(
						'=DIR_MD5' => md5($dir->getPath(true)),
					),
					[
						'info' => 'all messages in the directory have been deleted ',
					]
				);

				$countDeleted = $result ? $result->getCount() : 0;

				$this->lastSyncResult['deletedMessages'] += $countDeleted;
			}

			return;
		}

		$fetcher = function ($range) use ($dir)
		{
			$messages = $this->client->fetch(false, $dir->getPath(), $range, '(UID FLAGS)', $error);

			if (empty($messages))
			{
				if (false === $messages)
				{
					$this->warnings->add($this->client->getErrors()->toArray());
				}
				else
				{
					// @TODO: log
				}

				return false;
			}

			krsort($messages);

			return $messages;
		};

		$messagesNumberInTheMailService = $meta['exists'];
		$messages = $fetcher(($messagesNumberInTheMailService > 10000 || $numberForResync !== false) ? sprintf('1,%u', $messagesNumberInTheMailService) : '1:*');

		if (empty($messages))
		{
			return (false === $messages ? false : null);
		}

		//interval of messages in the directory
		$range = array(
			reset($messages)['UID'],
			end($messages)['UID'],
		);
		sort($range);

		if($range[0]===$range[1] and $messagesNumberInTheMailService > 1)
		{
			return false;
		}

		//deleting non-existent messages in the service ( not included in the message interval on the service )
		$result = $this->unregisterMessages(
			array(
				'=DIR_MD5' => md5($dir->getPath(true)),
				'>MSG_UID' => 0,
				array(
					'LOGIC'    => 'OR',
					'<MSG_UID' => $range[0],
					'>MSG_UID' => $range[1],
				),
			),
			[
				'info' => 'optimized deletion of non-existent messages',
			]
		);

		$countDeleted = $result ? $result->getCount() : 0;

		$this->lastSyncResult['deletedMessages'] += $countDeleted;

		//resynchronizing a certain number of messages
		if($numberForResync !== false)
		{
			$range1 = $meta['exists'];
			$range0 = max($range1 - ($numberForResync - 1), 1);
			$messages = $fetcher(sprintf('%u:%u', $range0, $range1));

			if (empty($messages))
			{
				return;
			}

			$this->resyncMessages($dir->getPath(true), $uidtoken, $messages);

			return;
		}

		if (!($meta['exists'] > 10000))
		{
			$this->resyncMessages($dir->getPath(true), $uidtoken, $messages);

			return;
		}

		$range1 = $meta['exists'];
		while ($range1 > 0)
		{
			$rangeSize = $range1 > 10000 ? 8000 : $range1;
			$range0 = max($range1 - $rangeSize, 1);

			$messages = $fetcher(sprintf('%u:%u', $range0, $range1));

			if (empty($messages))
			{
				return;
			}

			$this->resyncMessages($dir->getPath(true), $uidtoken, $messages);

			if ($this->isTimeQuotaExceeded())
			{
				return;
			}

			$range1 -= $rangeSize;
		}
	}

	protected function parseHeaders(&$messages)
	{
		foreach ($messages as $id => $item)
		{
			$messages[$id]['__header'] = \CMailMessage::parseHeader($item['BODY[HEADER]'], $this->mailbox['LANG_CHARSET']);
			$messages[$id]['__from'] = array_unique(array_map(
				'mb_strtolower',
				array_filter(
					array_merge(
						\CMailUtil::extractAllMailAddresses($messages[$id]['__header']->getHeader('FROM')),
						\CMailUtil::extractAllMailAddresses($messages[$id]['__header']->getHeader('REPLY-TO'))
					),
					'trim'
				)
			));
		}
	}

	protected function blacklistMessages($dirPath, &$messages)
	{
		$trashDir = $this->getDirsHelper()->getTrashPath();
		$spamDir = $this->getDirsHelper()->getSpamPath();

		$targetDir = $spamDir ?: $trashDir ?: null;
		$dir = $this->getDirsHelper()->getDirByPath($dirPath);

		if (empty($targetDir) || ($dir && ($dir->isTrash() || $dir->isSpam())))
		{
			return;
		}

		$blacklist = array(
			'email'  => array(),
			'domain' => array(),
		);

		$blacklistEmails = Mail\BlacklistTable::query()
			->addSelect('*')
			->setFilter(array(
				'=SITE_ID' => $this->mailbox['LID'],
				array(
					'LOGIC'       => 'OR',
					'=MAILBOX_ID' => $this->mailbox['ID'],
					array(
						'=MAILBOX_ID' => 0,
						'@USER_ID'    => array(0, $this->mailbox['USER_ID']),
					),
				),
			))
			->exec()
			->fetchCollection();
		foreach ($blacklistEmails as $blacklistEmail)
		{
			if ($blacklistEmail->isDomainType())
			{
				$blacklist['domain'][] = $blacklistEmail;
			}
			else
			{
				$blacklist['email'][] = $blacklistEmail;
			}
		}

		if (empty($blacklist['email']) && empty($blacklist['domain']))
		{
			return;
		}

		$targetMessages = [];
		$emailAddresses = array_map(function ($element)
		{
			/** @var Mail\Internals\Entity\BlacklistEmail $element */
			return $element->getItemValue();
		}, $blacklist['email']);
		$domains = array_map(function ($element)
		{
			/** @var Mail\Internals\Entity\BlacklistEmail $element */
			return $element->getItemValue();
		}, $blacklist['domain']);

		foreach ($messages as $id => $item)
		{
			if (!empty($blacklist['email']))
			{
				if (array_intersect($messages[$id]['__from'], $emailAddresses))
				{
					$targetMessages[$id] = $item['UID'];

					continue;
				}
				else
				{
					foreach ($blacklist['email'] as $blacklistMail)
					{
						/** @var Mail\Internals\Entity\BlacklistEmail $blacklistMail */
						if (array_intersect($messages[$id]['__from'], [$blacklistMail->convertDomainToPunycode()]))
						{
							$targetMessages[$id] = $item['UID'];
							continue;
						}
					}
				}
			}

			if (!empty($blacklist['domain']))
			{
				foreach ($messages[$id]['__from'] as $email)
				{
					$domain = mb_substr($email, mb_strrpos($email, '@'));
					if (in_array($domain, $domains))
					{
						$targetMessages[$id] = $item['UID'];

						continue 2;
					}
				}
			}
		}

		if (!empty($targetMessages))
		{
			if ($this->client->moveMails($targetMessages, $dirPath, $targetDir)->isSuccess())
			{
				$messages = array_diff_key($messages, $targetMessages);
			}
		}
	}

	protected function buildMessageIdForDataBase($dirPath, $uidToken, $UID): string
	{
		return md5(sprintf('%s:%u:%u', $dirPath, $uidToken, $UID));
	}

	protected function buildMessageHeaderHashForDataBase($message): string
	{
		return md5(sprintf(
			'%s:%s:%u',
			trim($message['BODY[HEADER]']),
			$message['INTERNALDATE'],
			$message['RFC822.SIZE']
		));
	}


	protected function removeExistingMessagesFromSynchronizationList($dirPath, $uidToken, &$messages)
	{
		$existingMessagesId = [];

		$range = array(
			reset($messages)['UID'],
			end($messages)['UID'],
		);
		sort($range);

		$result = $this->listMessages(array(
			'select' => [
				'ID'
			],
			'filter' => array(
				'=DIR_MD5'  => md5(Emoji::encode($dirPath)),
				'=DIR_UIDV' => $uidToken,
				'>=MSG_UID' => $range[0],
				'<=MSG_UID' => $range[1],
			),
		), false);

		while ($item = $result->fetch())
		{
			$existingMessagesId[] = $item['ID'];
		}

		foreach ($messages as $id => $item)
		{
			$messageUid = $this->buildMessageIdForDataBase($dirPath, $uidToken, $item['UID']);

			if (in_array($messageUid, $existingMessagesId))
			{
				unset($messages[$id]);
				continue;
			}

			//We also remove duplicate messages
			$existingMessagesId[] = $messageUid;
		}
	}

	protected function searchExistingMessagesByHeaderInDataBase($headerHashes)
	{
		return $this->listMessages([
			'select' => ['HEADER_MD5', 'MESSAGE_ID', 'DATE_INSERT'],
			'filter' => [
				'@HEADER_MD5' => $headerHashes,
			],
		], false);
	}

	protected function searchExistingMessagesByIdInDataBase($idsForDataBase)
	{
		return $this->listMessages(array(
			'select' => array('ID', 'MESSAGE_ID', 'DATE_INSERT'),
			'filter' => array(
				'@ID' => array_values($idsForDataBase),
			),
		), false);
	}

	protected function linkWithExistingMessages(&$messages)
	{
		$hashes = [];
		$idsForDataBase = [];

		foreach ($messages as $id => $item)
		{
			$hashes[$id] = $item['__fields']['HEADER_MD5'];
			$idsForDataBase[$id] = $item['__fields']['ID'];
		}

		$hashesMap = [];

		foreach ($hashes as $id => $hash)
		{
			if (!array_key_exists($hash, $hashesMap))
			{
				$hashesMap[$hash] = [];
			}

			$hashesMap[$hash][] = $id;
		}

		$existingMessages = $this->searchExistingMessagesByHeaderInDataBase(array_keys($hashesMap));

		/*
			For example, Gmail's labels act like "tags".
			Any individual email message can have multiple labels,
			and thus appear under multiple dirs.
		*/
		while ($item = $existingMessages->fetch())
		{
			foreach ((array)$hashesMap[$item['HEADER_MD5']] as $id)
			{
				$messages[$id]['__created'] = $item['DATE_INSERT'];
				$messages[$id]['__fields']['MESSAGE_ID'] = $item['MESSAGE_ID'];
			}
		}

		$existingMessages = $this->searchExistingMessagesByIdInDataBase($idsForDataBase);

		/*
			To restore messages stored with "broken" directories.
			For example, previously, data for messages in directories containing emojis were stored incorrectly in the database.
		*/
		while ($item = $existingMessages->fetch())
		{
			$id = array_search($item['ID'], $idsForDataBase);
			$messages[$id]['__created'] = $item['DATE_INSERT'];
			$messages[$id]['__fields']['MESSAGE_ID'] = $item['MESSAGE_ID'];
			$messages[$id]['__replaces'] = $item['ID'];
		}
	}

	protected function fillMessageFields(&$message, $dirPath, $uidToken)
	{
		$message['__internaldate'] = Main\Type\DateTime::createFromPhp(
			\DateTime::createFromFormat(
				'j-M-Y H:i:s O',
				ltrim(trim($message['INTERNALDATE']), '0')
			) ?: new \DateTime
		);

		$message['__fields'] = [
			'ID'           => $this->buildMessageIdForDataBase($dirPath, $uidToken, $message['UID']),
			'DIR_MD5'      => md5(Emoji::encode($dirPath)),
			'DIR_UIDV'     => $uidToken,
			'MSG_UID'      => $message['UID'],
			'INTERNALDATE' => $message['__internaldate'],
			'IS_SEEN'      => (isset($message['FLAGS']) && preg_grep('/^ \x5c Seen $/ix', $message['FLAGS'])) ? 'Y' : 'N',
			'HEADER_MD5'   => $this->buildMessageHeaderHashForDataBase($message),
			'MESSAGE_ID'   => 0,
		];
	}

	protected function selectOutgoingMessageIdFromHeader($message)
	{
		if (preg_match('/X-Bitrix-Mail-Message-UID:\s*([a-f0-9]+)/i', $message['BODY[HEADER]'], $matches))
		{
			return $matches[1];
		}
		else
		{
			return false;
		}
	}

	protected function resyncMessages($dirPath, $uidtoken, &$messages)
	{
		$excerpt = array();

		$range = array(
			reset($messages)['UID'],
			end($messages)['UID'],
		);
		sort($range);

		$result = $this->listMessages(array(
			'select' => array('ID', 'MESSAGE_ID', 'IS_SEEN'),
			'filter' => array(
				'=DIR_MD5'  => md5($dirPath),
				'=DIR_UIDV' => $uidtoken,
				'>=MSG_UID' => $range[0],
				'<=MSG_UID' => $range[1],
			),
		), false);

		while ($item = $result->fetch())
		{
			$item['MAILBOX_USER_ID'] = $this->mailbox['USER_ID'];
			$excerpt[$item['ID']] = $item;
		}

		$update = array(
			'Y' => array(),
			'N' => array(),
			'S' => array(),
			'U' => array(),
		);
		foreach ($messages as $id => $item)
		{
			$messageUid = md5(sprintf('%s:%u:%u', $dirPath, $uidtoken, $item['UID']));

			if (array_key_exists($messageUid, $excerpt))
			{
				$excerptSeen = $excerpt[$messageUid]['IS_SEEN'];
				$excerptSeenYN = in_array($excerptSeen, array('Y', 'S')) ? 'Y' : 'N';
				$messageSeen = preg_grep('/^ \x5c Seen $/ix', $item['FLAGS']) ? 'Y' : 'N';

				if ($messageSeen != $excerptSeen)
				{
					if (in_array($excerptSeen, array('S', 'U')))
					{
						$excerpt[$messageUid]['IS_SEEN'] = $excerptSeenYN;
						$update[$excerptSeenYN][$messageUid] = $excerpt[$messageUid];

						if ($messageSeen != $excerptSeenYN)
						{
							$update[$excerptSeen][] = $item['UID'];
						}
					}
					else
					{
						$excerpt[$messageUid]['IS_SEEN'] = $messageSeen;
						$update[$messageSeen][$messageUid] = $excerpt[$messageUid];
					}
				}

				unset($excerpt[$messageUid]);
			}
			else
			{
				/*
				addMessage2Log(
					sprintf(
						'IMAP: message lost (%u:%s:%u:%s)',
						$this->mailbox['ID'], $dirPath, $uidtoken, $item['UID']
					),
					'mail', 0, false
				);
				*/
			}
		}

		$countUpdated = 0;
		$countDeleted = count($excerpt);

		foreach ($update as $seen => $items)
		{
			if (!empty($items))
			{
				if (in_array($seen, array('S', 'U')))
				{
					$method = 'S' == $seen ? 'seen' : 'unseen';
					$this->client->$method($items, $dirPath);
				}
				else
				{
					$countUpdated += count($items);

					$this->updateMessagesRegistry(
						array(
							'@ID' => array_keys($items),
						),
						array(
							'IS_SEEN' => $seen,
						),
						$items = array() // @TODO: fix lazyload in MessageEventManager::processOnMailMessageModified()
					);
				}
			}
		}

		if (!empty($excerpt))
		{
			$result = $this->unregisterMessages(
				[
					'@ID' => array_keys($excerpt),
					'=DIR_MD5'  => md5($dirPath),
				],
				[
					'info' => 'deletion of non-existent messages',
				]
			);

			$countDeleted += $result ? $result->getCount() : 0;
		}

		$this->lastSyncResult['updatedMessages'] += $countUpdated;
		$this->lastSyncResult['deletedMessages'] += $countDeleted;
	}

	protected function completeMessageSync($uid)
	{
		$result = Mail\MailMessageUidTable::update(
			[
				'ID' => $uid,
				'MAILBOX_ID' => $this->mailbox['ID'],
			],
			[
				'IS_OLD' => 'N',
			]
		);

		return $result->isSuccess();
	}

	protected function syncMessage($dirPath, $message, &$hashesMap = [], $ignoreSyncFrom = false, $isOutgoing = false)
	{
		$fields = $message['__fields'];

		if ($fields['MESSAGE_ID'] > 0)
		{
			$hashesMap[$fields['HEADER_MD5']] = $fields['MESSAGE_ID'];
		}
		else
		{
			if (array_key_exists($fields['HEADER_MD5'], $hashesMap) && $hashesMap[$fields['HEADER_MD5']] > 0)
			{
				$fields['MESSAGE_ID'] = $hashesMap[$fields['HEADER_MD5']];
			}
		}

		if (!$this->registerMessage($fields, ($message['__replaces'] ?? null), $isOutgoing))
		{
			return false;
		}

		$minimumSyncDate = $this->getMinimumSyncDate();

		if($minimumSyncDate !== false && !$ignoreSyncFrom && $message['__internaldate']->getTimestamp() < $this->getMinimumSyncDate())
		{
			$this->completeMessageSync($fields['ID']);
			return false;
		}

		if (!empty($message['__created']) && !empty($this->mailbox['OPTIONS']['resync_from']))
		{
			if ($message['__created']->getTimestamp() < $this->mailbox['OPTIONS']['resync_from'])
			{
				$this->completeMessageSync($fields['ID']);
				return false;
			}
		}

		if ($fields['MESSAGE_ID'] > 0)
		{
			$this->completeMessageSync($fields['ID']);
			return true;
		}

		$messageId = 0;

		if (!empty($message['BODYSTRUCTURE']) && !empty($message['BODY[HEADER]']))
		{
			$message['__bodystructure'] = new Mail\Imap\BodyStructure($message['BODYSTRUCTURE']);

			$message['__parts'] = $this->downloadMessageParts(
				$message['__fields'],
				$message['__bodystructure'],
				$this->isSupportLazyAttachments() ? self::MESSAGE_PARTS_TEXT : self::MESSAGE_PARTS_ALL
			);

			// #119474
			if (!$message['__bodystructure']->isMultipart())
			{
				if (is_array($message['__parts']) && !empty($message['__parts']['BODY[1]']))
				{
					$message['__parts']['BODY[1.MIME]'] = $message['BODY[HEADER]'];
				}
			}
		}
		else
		{
			// fallback
			$message['__parts'] = $this->downloadMessage($message['__fields']) ?: false;
		}

		if (false !== $message['__parts'])
		{
			$dir = $this->getDirsHelper()->getDirByPath($dirPath);

			$messageId = $this->cacheMessage(
				$message,
				array(
					'timestamp'        => $message['__internaldate']->getTimestamp(),
					'size'             => $message['RFC822.SIZE'],
					'outcome'          => in_array($this->mailbox['EMAIL'], $message['__from']),
					'draft'            => $dir != null && $dir->isDraft() || preg_grep('/^ \x5c Draft $/ix', $message['FLAGS']),
					'trash'            => $dir != null && $dir->isTrash(),
					'spam'             => $dir != null && $dir->isSpam(),
					'seen'             => $fields['IS_SEEN'] == 'Y',
					'hash'             => $fields['HEADER_MD5'],
					'lazy_attachments' => $this->isSupportLazyAttachments(),
					'excerpt'          => $fields,
				)
			);
		}

		if ($messageId > 0)
		{
			$hashesMap[$fields['HEADER_MD5']] = $messageId;

			$this->linkMessage($fields['ID'], $messageId);
		}

		$this->completeMessageSync($fields['ID']);

		return $messageId > 0;
	}

	public function downloadAttachments(array &$excerpt)
	{
		if (empty($excerpt['MSG_UID']) || empty($excerpt['DIR_MD5']))
		{
			return false;
		}

		$dirPath = $this->getDirsHelper()->getDirPathByHash($excerpt['DIR_MD5']);
		if (empty($dirPath))
		{
			return false;
		}

		$message = $this->client->fetch(true, $dirPath, $excerpt['MSG_UID'], '(BODYSTRUCTURE)', $error);
		if (empty($message['BODYSTRUCTURE']))
		{
			// @TODO: fallback

			if (false === $message)
			{
				$this->errors = new Main\ErrorCollection($this->client->getErrors()->toArray());
			}

			return false;
		}

		if (!is_array($message['BODYSTRUCTURE']))
		{
			$this->errors = new Main\ErrorCollection(array(
				new Main\Error('Helper\Mailbox\Imap: Invalid BODYSTRUCTURE', 0),
				new Main\Error((string)$message['BODYSTRUCTURE'], -1),
			));
			return false;
		}

		$message['__bodystructure'] = new Mail\Imap\BodyStructure($message['BODYSTRUCTURE']);

		$parts = $this->downloadMessageParts(
			$excerpt,
			$message['__bodystructure'],
			self::MESSAGE_PARTS_ATTACHMENT
		);

		$attachments = array();

		$message['__bodystructure']->traverse(
			function (Mail\Imap\BodyStructure $item) use (&$parts, &$attachments)
			{
				if ($item->isMultipart() || $item->isBodyText())
				{
					return;
				}

				$attachments[] = \CMailMessage::decodeMessageBody(
					\CMailMessage::parseHeader(
						$parts[sprintf('BODY[%s.MIME]', $item->getNumber())],
						$this->mailbox['LANG_CHARSET']
					),
					$parts[sprintf('BODY[%s]', $item->getNumber())],
					$this->mailbox['LANG_CHARSET']
				);
			}
		);

		return $attachments;
	}

	protected function cacheMessage(&$message, $params = array())
	{
		if (!is_array($message))
		{
			return parent::cacheMessage($message, $params);
		}

		if (!is_array($message['__parts']))
		{
			return parent::cacheMessage($message['__parts'], $params);
		}

		if (empty($message['__header']))
		{
			return false;
		}

		if (empty($message['__bodystructure']) || !($message['__bodystructure'] instanceof Mail\Imap\BodyStructure))
		{
			return false;
		}

		$complete = function (&$html, &$text)
		{
			if ('' !== $html && '' === $text)
			{
				$text = html_entity_decode(
					htmlToTxt($html),
					ENT_QUOTES | ENT_HTML401,
					$this->mailbox['LANG_CHARSET']
				);
			}
			elseif ('' === $html && '' !== $text)
			{
				$html = txtToHtml($text, false, 120);
			}
		};

		[$bodyHtml, $bodyText, $attachments] = $message['__bodystructure']->traverse(
			function (Mail\Imap\BodyStructure $item, &$subparts) use (&$message, &$complete)
			{
				$parts = &$message['__parts'];

				$html = '';
				$text = '';
				$attachments = array();

				if ($item->isMultipart())
				{
					if ('alternative' === $item->getSubtype())
					{
						foreach ($subparts as $part)
						{
							$part = $part[0];

							if ('' !== $part[0])
							{
								$html = $part[0];
							}

							if ('' !== $part[1])
							{
								$text = $part[1];
							}

							if (!empty($part[2]))
							{
								$attachments = array_merge($attachments, $part[2]);
							}
						}

						$complete($html, $text);
					}
					else
					{
						foreach ($subparts as $part)
						{
							$part = $part[0];

							$complete($part[0], $part[1]);

							if ('' !== $part[0] || '' !== $part[1])
							{
								$html .= $part[0] . "\r\n\r\n";
								$text .= $part[1] . "\r\n\r\n";
							}

							$attachments = array_merge($attachments, $part[2]);
						}
					}

					$html = trim($html);
					$text = trim($text);
				}
				else
				{
					if (array_key_exists(sprintf('BODY[%s]', $item->getNumber()), $parts))
					{
						$part = \CMailMessage::decodeMessageBody(
							\CMailMessage::parseHeader(
								$parts[sprintf('BODY[%s.MIME]', $item->getNumber())],
								$this->mailbox['LANG_CHARSET']
							),
							$parts[sprintf('BODY[%s]', $item->getNumber())],
							$this->mailbox['LANG_CHARSET']
						);
					}
					else
					{
						$part = [
							'CONTENT-TYPE' => $item->getType() . '/' . $item->getSubtype(),
							'CONTENT-ID'   => $item->getId(),
							'BODY'         => '',
							'FILENAME'     => $item->getParams()['name']
						];
					}

					if (!$item->isBodyText())
					{
						$attachments[] = $part;
					}
					elseif (!empty($part))
					{
						if ('html' === $item->getSubtype())
						{
							$html = $part['BODY'];
						}
						else
						{
							$text = $part['BODY'];
						}
					}
				}

				return array($html, $text, $attachments);
			}
		)[0];

		$complete($bodyHtml, $bodyText);

		return \CMailMessage::saveMessage(
			$this->mailbox['ID'],
			$dummyBody,
			$message['__header'],
			$bodyHtml,
			$bodyText,
			$attachments,
			$params
		);
	}

	public function getMinimumSyncDate()
	{
		$minimumDate = false;

		if(!empty($this->mailbox['OPTIONS']['sync_from']))
		{
			$minimumDate = $this->mailbox['OPTIONS']['sync_from'];
		}

		$syncOldLimit = Mail\Helper\LicenseManager::getSyncOldLimit();

		if($syncOldLimit > 0)
		{
			$syncOldLimit = strtotime(sprintf('-%u days', $syncOldLimit));

			/*
				Checking in case of changes in tariff limits
			*/
			if($minimumDate === false || $minimumDate < $syncOldLimit)
			{
				$minimumDate = $syncOldLimit;
			}
		}
		return $minimumDate;
	}

	protected function getSyncRange($dirPath, &$uidtoken, $intervalSynchronizationAttempts = 0)
	{
		$meta = $this->client->select($dirPath, $error);
		if (false === $meta)
		{
			$this->warnings->add($this->client->getErrors()->toArray());

			return null;
		}

		if (!($meta['exists'] > 0))
		{
			return null;
		}

		$uidtoken = $meta['uidvalidity'];

		/*
			The interval may be smaller if the uid of the last message
			in the database is close to the split point in the set of intervals
		*/
		$maximumLengthSynchronizationInterval = $this->getMaximumSynchronizationLengthsOfIntervals($intervalSynchronizationAttempts);

		$rangeGetter = function ($min, $max) use ($dirPath, $uidtoken, &$rangeGetter, $maximumLengthSynchronizationInterval)
		{

			$size = $max - $min + 1;

			$set = [];
			$d = $size < 1000 ? $maximumLengthSynchronizationInterval : pow(10, round(ceil(log10($size) - 0.7) / 2) * 2 - 2);

			//Take intermediate interval values
			for ($i = $min; $i <= $max; $i = $i + $d)
			{
				$set[] = $i;
			}

			/*
				The end of the expected interval should not exceed the identifier of the last message
			*/
			if (count($set) > 1 && end($set) >= $max)
			{
				array_pop($set);
			}

			//The last item in the set must match the last item on the service
			$set[] = $max;

			//Return messages starting from the 1st existing one
			$set = $this->client->fetch(false, $dirPath, join(',', $set), '(UID)', $error);

			if (empty($set))
			{
				return false;
			}

			ksort($set);

			static $uidMinInDatabase, $uidMaxInDatabase, $takeFromDown;

			if (!isset($uidMinInDatabase, $uidMaxInDatabase, $takeFromDown))
			{
				$messagesUidBoundariesIntervalInDatabase = $this->getUidRange($dirPath, $uidtoken);

				if ($messagesUidBoundariesIntervalInDatabase)
				{
					$uidMinInDatabase = $messagesUidBoundariesIntervalInDatabase['MIN'];
					$uidMaxInDatabase = $messagesUidBoundariesIntervalInDatabase['MAX'];
					$takeFromDown = $messagesUidBoundariesIntervalInDatabase['TAKE_FROM_DOWN'];
				}
				else
				{
					$takeFromDown = true;
					$uidMinInDatabase = $uidMaxInDatabase = (end($set)['UID'] + 1);
				}
			}

			if (count($set) == 1)
			{
				$uid = reset($set)['UID'];

				if ($uid > $uidMaxInDatabase || $uid < $uidMinInDatabase)
				{
					return array($uid, $uid);
				}
			}
			elseif (end($set)['UID'] > $uidMaxInDatabase)
			{
				/*
					Select the closest element with the largest uid
					from the set of messages on the service (every hundredth)
					to a message from the database (synchronized) with the maximum uid.
				*/
				do
				{
					$max = current($set)['id'];
					$min = prev($set)['id'];
				}
				while (current($set)['UID'] > $uidMaxInDatabase && prev($set) && next($set));

				if ($max - $min > $maximumLengthSynchronizationInterval)
				{
					return $rangeGetter($min, $max);
				}
				else
				{
					/*
						Since we upload messages "up",
						we know the upper ones and there is no point in "capturing" existing messages in the interval.
						The selection is made within the interval, so the presence of extreme messages with the specified identifiers is not necessary.
						+ 1 / - do not include an already uploaded message
					*/
					return array(
						max($set[$min]['UID'], $uidMaxInDatabase + 1),
						$set[$max]['UID'],
					);
				}
			}
			elseif (reset($set)['UID'] < $uidMinInDatabase && $takeFromDown)
			{
				do
				{
					$min = current($set)['id'];
					$max = next($set)['id'];
				}
				while (current($set)['UID'] < $uidMinInDatabase && next($set) && prev($set));

				if ($max - $min > $maximumLengthSynchronizationInterval)
				{
					return $rangeGetter($min, $max);
				}
				else
				{
					/*
						Since we upload messages "down",
						we know the upper ones and there is no point in "capturing" existing messages in the interval.
						The selection is made within the interval, so the presence of extreme messages with the specified identifiers is not necessary.
						- 1 / - do not include an already uploaded message
					*/
					return array(
						min($set[$max]['UID'], $uidMinInDatabase - 1),
						$set[$min]['UID'],
					);
				}
			}

			return null;
		};

		return $rangeGetter(1, $meta['exists']);
	}

	protected function getUidRange($dirPath, $uidtoken)
	{
		$filter = array(
			'=DIR_MD5'  => md5(Emoji::encode($dirPath)),
			'=DIR_UIDV' => $uidtoken,
			'>MSG_UID'  => 0,
		);

		$minimumSyncDate = $this->getMinimumSyncDate();

		$takeFromDown = true;

		$min = $this->listMessages(
			array(
				'select' => array(
					'MIN' => 'MSG_UID','INTERNALDATE'
				),
				'filter' => $filter,
				'order'  => array(
					'MSG_UID' => 'ASC',
				),
				'limit'  => 1,
			),
			false
		)->fetch();

		if(isset($min['INTERNALDATE']) && $minimumSyncDate !== false && $min['INTERNALDATE']->getTimestamp() < $minimumSyncDate)
		{
			$takeFromDown = false;
		}

		$max = $this->listMessages(
			array(
				'select' => array(
					'MAX' => 'MSG_UID',
				),
				'filter' => $filter,
				'order'  => array(
					'MSG_UID' => 'DESC',
				),
				'limit'  => 1,
			),
			false
		)->fetch();

		if ($min && $max)
		{
			return array(
				'MIN' => $min['MIN'],
				'MAX' => $max['MAX'],
				'TAKE_FROM_DOWN' => $takeFromDown,
			);
		}

		return null;
	}

}
