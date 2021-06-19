<?php

namespace Bitrix\Mail\Helper\Mailbox;

use Bitrix\Mail;
use Bitrix\Mail\Helper\MailboxDirectoryHelper;
use Bitrix\Mail\MailboxDirectory;
use Bitrix\Main;

class Imap extends Mail\Helper\Mailbox
{
	const MESSAGE_PARTS_TEXT = 1;
	const MESSAGE_PARTS_ATTACHMENT = 2;
	const MESSAGE_PARTS_ALL = -1;

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
					'=DELETE_TIME' => 'IS NULL',
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
					'=DELETE_TIME' => 'IS NULL',
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

	protected function syncInternal()
	{
		$count = $this->syncMailbox();
		if (false === $count)
		{
			$this->errors = new Main\ErrorCollection($this->client->getErrors()->toArray());
		}

		return $count;
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

		$count = 0;

		$this->cacheDirs();

		$currentDir = null;

		if (!empty($this->syncParams['currentDir']))
		{
			$currentDir = $this->syncParams['currentDir'];
		}

		$meta = $this->getDirsHelper()->getSyncDirsOrderByTime($currentDir);

		if (empty($meta))
		{
			return $count;
		}

		$lastDir = $this->getDirsHelper()->getLastSyncDirByDefault($currentDir);

		foreach ($meta as $item)
		{
			MailboxDirectoryHelper::setCurrentSyncDir($item->getPath());

			$count += $this->syncDir($item->getPath());

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
			//mark emails from unsynchronized folders (unchecked) for deletion
			$result = $this->unregisterMessages([
				'!@DIR_MD5' => array_map(
					'md5',
					$this->getDirsHelper()->getSyncDirsPath()
				),
			]);

			$countDeleted = $result ? $result->getCount() : 0;

			$this->lastSyncResult['deletedMessages'] += $countDeleted;

			if (!empty($this->syncParams['full']))
			{
				foreach ($meta as $item)
				{
					$this->resyncDir($item->getPath());

					if ($this->isTimeQuotaExceeded())
					{
						break;
					}
				}
			}
		}

		return $count;
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

		$pushParams = ['dir' => $dir->getPath()];

		$result = $this->syncDirInternal($dir);

		$dir->stopSyncLock();

		if (false === $result)
		{
			$pushParams['complete'] = -1;
			$pushParams['status'] = -1;
			$pushParams['errors'] = $this->client->getErrors()->toArray();
		}
		else
		{
			$pushParams['complete'] = $this->isTimeQuotaExceeded() ? -1 : $dir->getPath() !== $this->syncParams['currentDir'];
			$pushParams['new'] = $result;
		}

		$this->lastSyncResult['newMessages'] += $result;
		if (!$dir->isTrash() && !$dir->isSpam()) // && !$dir->isDraft() && !$dir->isOutcome()
		{
			$this->lastSyncResult['newMessagesNotify'] += $result;
		}

		return $result;
	}

	protected function syncDirInternal($dir)
	{
		$count = 0;

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

		$time = time();
		$timeout = 5;

		while ($range = $this->getSyncRange($dir->getPath(), $uidtoken))
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

			if (empty($messages))
			{
				if (false === $messages)
				{
					$this->warnings->add($this->client->getErrors()->toArray());

					return false;
				}
				else
				{
					// @TODO: log
				}

				break;
			}

			$reverse ? krsort($messages) : ksort($messages);

			$this->parseHeaders($messages);

			$this->blacklistMessages($dir->getPath(), $messages);

			$this->prepareMessages($dir->getPath(), $uidtoken, $messages);

			$hashesMap = array();

			//To display new messages(grid reload) until synchronization is complete
			$numberOfMessagesInABatch = 1;
			$numberLeftToFillTheBatch = $numberOfMessagesInABatch;

			foreach ($messages as $id => $item)
			{
				if ($this->syncMessage($dir->getPath(), $uidtoken, $item, $hashesMap))
				{
					$this->lastSyncResult['newMessageId'] = end($hashesMap);
					$count++;

					if ($time < time() - $timeout)
					{
						$time = time();
					}

					$numberLeftToFillTheBatch--;
					if($numberLeftToFillTheBatch === 0 and Main\Loader::includeModule('pull'))
					{
						$numberOfMessagesInABatch *= 2;
						$numberLeftToFillTheBatch = $numberOfMessagesInABatch;
						\CPullWatch::addToStack(
							'mail_mailbox_' . $this->mailbox['ID'],
							array(
								'params' => array(
									'dir' => $dir->getPath()
								),
								'module_id' => 'mail',
								'command' => 'new_message_is_synchronized',
							)
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

		return $count;
	}

	public function resyncDir($dirPath)
	{
		$dir = $this->getDirsHelper()->getDirByPath($dirPath);

		if (!$dir || !$dir->isSync())
		{
			return false;
		}

		$pushParams = [
			'dir' => $dir->getPath(),
			'updated' => -$this->lastSyncResult['updatedMessages'],
			'deleted' => -$this->lastSyncResult['deletedMessages'],
		];

		$result = $this->resyncDirInternal($dir);

		$pushParams['updated'] += $this->lastSyncResult['updatedMessages'];
		$pushParams['deleted'] += $this->lastSyncResult['deletedMessages'];

		if (false === $result)
		{
			$pushParams['complete'] = -1;
			$pushParams['status'] = -1;
			$pushParams['errors'] = $this->client->getErrors()->toArray();
		}
		else
		{
			$pushParams['complete'] = $this->isTimeQuotaExceeded() ? -1 : 1;
		}
	}

	protected function resyncDirInternal($dir)
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
						'=DIR_MD5'  => md5($dir->getPath()),
						'<DIR_UIDV' => $uidtoken,
					),
					[]
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
						'=DIR_MD5' => md5($dir->getPath()),
					),
					[]
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
		$messages = $fetcher($messagesNumberInTheMailService > 10000 ? sprintf('1,%u', $messagesNumberInTheMailService) : '1:*');

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
				'=DIR_MD5' => md5($dir->getPath()),
				'>MSG_UID' => 0,
				array(
					'LOGIC'    => 'OR',
					'<MSG_UID' => $range[0],
					'>MSG_UID' => $range[1],
				),
			),
			[]
		);

		$countDeleted = $result ? $result->getCount() : 0;

		$this->lastSyncResult['deletedMessages'] += $countDeleted;

		if (!($meta['exists'] > 10000))
		{
			$this->resyncMessages($dir->getPath(), $uidtoken, $messages);

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

			$this->resyncMessages($dir->getPath(), $uidtoken, $messages);

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

	protected function prepareMessages($dirPath, $uidtoken, &$messages)
	{
		$excerpt = array();

		$range = array(
			reset($messages)['UID'],
			end($messages)['UID'],
		);
		sort($range);

		$result = $this->listMessages(array(
			'select' => array('ID'),
			'filter' => array(
				'=DIR_MD5'  => md5($dirPath),
				'=DIR_UIDV' => $uidtoken,
				'>=MSG_UID' => $range[0],
				'<=MSG_UID' => $range[1],
			),
		), false);

		while ($item = $result->fetch())
		{
			$excerpt[] = $item['ID'];
		}

		$uids = array();
		$hashes = array();
		foreach ($messages as $id => $item)
		{
			$messageUid = md5(sprintf('%s:%u:%u', $dirPath, $uidtoken, $item['UID']));

			if (in_array($messageUid, $excerpt))
			{
				unset($messages[$id]);
				continue;
			}

			$excerpt[] = $uids[$id] = $messageUid;

			$hashes[$id] = md5(sprintf(
				'%s:%s:%u',
				trim($item['BODY[HEADER]']),
				$item['INTERNALDATE'],
				$item['RFC822.SIZE']
			));

			$messages[$id]['__internaldate'] = Main\Type\DateTime::createFromPhp(
				\DateTime::createFromFormat(
					'j-M-Y H:i:s O',
					ltrim(trim($item['INTERNALDATE']), '0')
				) ?: new \DateTime
			);

			$messages[$id]['__fields'] = array(
				'ID'           => $messageUid,
				'DIR_MD5'      => md5($dirPath),
				'DIR_UIDV'     => $uidtoken,
				'MSG_UID'      => $item['UID'],
				'INTERNALDATE' => $messages[$id]['__internaldate'],
				'IS_SEEN'      => preg_grep('/^ \x5c Seen $/ix', $item['FLAGS']) ? 'Y' : 'N',
				'HEADER_MD5'   => $hashes[$id],
				'MESSAGE_ID'   => 0,
			);

			if (preg_match('/X-Bitrix-Mail-Message-UID:\s*([a-f0-9]+)/i', $item['BODY[HEADER]'], $matches))
			{
				$messages[$id]['__replaces'] = $matches[1];
			}
		}

		$hashesMap = array();
		foreach ($hashes as $id => $hash)
		{
			if (!array_key_exists($hash, $hashesMap))
			{
				$hashesMap[$hash] = array();
			}

			$hashesMap[$hash][] = $id;
		}

		$result = $this->listMessages(array(
			'select' => array('HEADER_MD5', 'MESSAGE_ID', 'DATE_INSERT'),
			'filter' => array(
				'@HEADER_MD5' => array_keys($hashesMap),
			),
		), false);

		while ($item = $result->fetch())
		{
			foreach ((array)$hashesMap[$item['HEADER_MD5']] as $id)
			{
				$messages[$id]['__created'] = $item['DATE_INSERT'];
				$messages[$id]['__fields']['MESSAGE_ID'] = $item['MESSAGE_ID'];
			}
		}

		$result = $this->listMessages(array(
			'select' => array('ID', 'MESSAGE_ID', 'DATE_INSERT'),
			'filter' => array(
				'@ID' => array_values($uids),
				// DIR_MD5 can be empty in DB
			),
		), false);

		while ($item = $result->fetch())
		{
			$id = array_search($item['ID'], $uids);
			$messages[$id]['__created'] = $item['DATE_INSERT'];
			$messages[$id]['__fields']['MESSAGE_ID'] = $item['MESSAGE_ID'];
			$messages[$id]['__replaces'] = $item['ID'];
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
				array(
					'@ID' => array_keys($excerpt),
				),
				$excerpt
			);

			$countDeleted += $result ? $result->getCount() : 0;
		}

		$this->lastSyncResult['updatedMessages'] += $countUpdated;
		$this->lastSyncResult['deletedMessages'] += $countDeleted;
	}

	protected function syncMessage($dirPath, $uidtoken, $message, &$hashesMap = array())
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

		if (!$this->registerMessage($fields, isset($message['__replaces']) ? $message['__replaces'] : null))
		{
			return false;
		}

		if (Mail\Helper\LicenseManager::getSyncOldLimit() > 0)
		{
			if ($message['__internaldate']->getTimestamp() < strtotime(sprintf('-%u days', Mail\Helper\LicenseManager::getSyncOldLimit())))
			{
				return false;
			}
		}

		if (!empty($this->mailbox['OPTIONS']['sync_from']))
		{
			if ($message['__internaldate']->getTimestamp() < $this->mailbox['OPTIONS']['sync_from'])
			{
				return false;
			}
		}

		if (!empty($message['__created']) && !empty($this->mailbox['OPTIONS']['resync_from']))
		{
			if ($message['__created']->getTimestamp() < $this->mailbox['OPTIONS']['resync_from'])
			{
				return false;
			}
		}

		if ($fields['MESSAGE_ID'] > 0)
		{
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

		list($bodyHtml, $bodyText, $attachments) = $message['__bodystructure']->traverse(
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

	protected function getSyncRange($dirPath, &$uidtoken)
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

		$rangeGetter = function ($min, $max) use ($dirPath, $uidtoken, &$rangeGetter)
		{
			$size = $max - $min + 1;

			$set = array();
			$d = $size < 1000 ? 100 : pow(10, round(ceil(log10($size) - 0.7) / 2) * 2 - 2);

			//take every $d (usually 100) id starting from the first one
			for ($i = $min; $i <= $max; $i = $i + $d)
			{
				$set[] = $i;
			}

			/*if the interval from the last message id(we will add it later)
			to the penultimate id in the set is less than one hundred,
			we will delete the last one to increase the interval.
				Example: 5000, 5100, 5200... 13900... 14000... 14023.
			*/
			if (count($set) > 1 && end($set) + 100 >= $max)
			{
				array_pop($set);
			}

			//the last item in the set must match the last item on the service
			$set[] = $max;

			//returns messages starting from the 1st existing one
			$set = $this->client->fetch(false, $dirPath, join(',', $set), '(UID)', $error);

			if (empty($set))
			{
				return false;
			}

			ksort($set);

			static $uidMin, $uidMax;

			if (!isset($uidMin, $uidMax))
			{
				$minmax = $this->getUidRange($dirPath, $uidtoken);

				if ($minmax)
				{
					$uidMin = $minmax['MIN'];
					$uidMax = $minmax['MAX'];
				}
				else
				{
					$uidMin = $uidMax = (end($set)['UID'] + 1);
				}
			}

			if (count($set) == 1)
			{
				$uid = reset($set)['UID'];

				if ($uid > $uidMax || $uid < $uidMin)
				{
					return array($uid, $uid);
				}
			}
			elseif (end($set)['UID'] > $uidMax)
			{
				$max = current($set)['id'];

				/*select the closest element with the largest uid
				from the set of messages on the service (every hundredth)
				to a message from the database (synchronized) with the maximum uid.*/
				do
				{
					$exmax = $max;

					$max = current($set)['id'];
					$min = prev($set)['id'];
				}
				while (current($set)['UID'] > $uidMax && prev($set) && next($set));

				//if the interval of messages for downloading is more than 200 - we repeat the splitting.
				if ($max - $min > 200)
				{
					return $rangeGetter($min, $max);
				}
				else
				{
					/*if the synchronization interval turned out to be too small,
					we take the nearest largest to the end of the interval from the set (every 100).
					Thus the interval will increase by a hundred
					(or another value, if at the end of the set).*/
					if ($set[$max]['UID'] - $uidMax < 100)
					{
						$max = $exmax;
					}

					return array(
						max($set[$min]['UID'], $uidMax + 1),
						$set[$max]['UID'],
					);
				}
			}
			elseif (reset($set)['UID'] < $uidMin)
			{
				$min = current($set)['id'];
				do
				{
					$exmin = $min;

					$min = current($set)['id'];
					$max = next($set)['id'];
				}
				while (current($set)['UID'] < $uidMin && next($set) && prev($set));

				if ($max - $min > 200)
				{
					return $rangeGetter($min, $max);
				}
				else
				{
					if ($uidMin - $set[$min]['UID'] < 100)
					{
						$min = $exmin;
					}

					return array(
						min($set[$max]['UID'], $uidMin - 1),
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
			'=DIR_MD5'  => md5($dirPath),
			'=DIR_UIDV' => $uidtoken,
			'>MSG_UID'  => 0,
		);

		$min = $this->listMessages(
			array(
				'select' => array(
					'MIN' => 'MSG_UID',
				),
				'filter' => $filter,
				'order'  => array(
					'MSG_UID' => 'ASC',
				),
				'limit'  => 1,
			),
			false
		)->fetch();

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
			);
		}

		return null;
	}

}
