<?php

namespace Bitrix\Mail\Helper\Mailbox;

use Bitrix\Mail\Helper\MessageFolder;
use Bitrix\Main;
use Bitrix\Mail;

class Imap extends Mail\Helper\Mailbox
{
	const MESSAGE_PARTS_TEXT = 1;
	const MESSAGE_PARTS_ATTACHMENT = 2;
	const MESSAGE_PARTS_ALL = -1;

	protected $client;

	protected function __construct($mailbox)
	{
		parent::__construct($mailbox);

		$mailbox = &$this->mailbox;

		$this->client = new Mail\Imap(
			$mailbox['SERVER'],
			$mailbox['PORT'],
			$mailbox['USE_TLS'] == 'Y' || $mailbox['USE_TLS'] == 'S',
			$mailbox['USE_TLS'] == 'Y',
			$mailbox['LOGIN'],
			$mailbox['PASSWORD']
		);
	}

	protected function normalizeMailboxOptions()
	{
		$options = &$this->mailbox['OPTIONS'];

		if (empty($options['imap']) || !is_array($options['imap']))
		{
			$options['imap'] = array();
		}

		$imapOptions = &$options['imap'];
		if (empty($imapOptions[MessageFolder::INCOME]) || !is_array($imapOptions[MessageFolder::INCOME]))
		{
			$imapOptions[MessageFolder::INCOME] = array();
		}
		if (empty($imapOptions[MessageFolder::OUTCOME]) || !is_array($imapOptions[MessageFolder::OUTCOME]))
		{
			$imapOptions[MessageFolder::OUTCOME] = array();
		}
	}

	public function getSyncStatus()
	{
		$meta = Mail\MailMessageUidTable::getList(array(
			'select' => array(
				new Main\Entity\ExpressionField('TOTAL', 'COUNT(1)'),
			),
			'filter' => array(
				'=MAILBOX_ID' => $this->mailbox['ID'],
			),
		))->fetch();

		if ($meta['TOTAL'] > 0 && $this->mailbox['OPTIONS']['imap']['total'] > 0)
		{
			return $meta['TOTAL'] / $this->mailbox['OPTIONS']['imap']['total'];
		}
		else
		{
			return parent::getSyncStatus();
		}
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
		$dir = reset($this->mailbox['OPTIONS']['imap'][MessageFolder::OUTCOME]) ?: 'INBOX';

		$fields = array_merge(
			$fields,
			array(
				'DIR_MD5' => md5($dir),
				'DIR_UIDV' => 0,
				'MSG_UID' => 0,
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
		$dir = reset($this->mailbox['OPTIONS']['imap'][MessageFolder::OUTCOME]) ?: 'INBOX';

		$data = $this->client->select($dir, $error);

		if (false === $data)
		{
			$this->errors = new Main\ErrorCollection($this->client->getErrors()->toArray());

			return false;
		}

		if (!empty($excerpt['__unique_headers']))
		{
			if ($this->client->searchByHeader(false, $dir, $excerpt['__unique_headers'], $error))
			{
				return false;
			}
		}

		$flags = array('\Seen');
		if (!empty($excerpt['ID']) && preg_grep('/^ \x5c \* $/ix', $data['permanentflags']))
		{
			$flags[] = sprintf('bxuid%s', $excerpt['ID']);
		}

		$result = $this->client->append(
			$dir,
			$flags,
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

		$this->syncDir($dir);

		return $result;
	}

	public function downloadMessage(array &$excerpt)
	{
		if (empty($excerpt['MSG_UID']) || empty($excerpt['DIR_MD5']))
		{
			return false;
		}

		$dir = MessageFolder::getFolderNameByHash($excerpt['DIR_MD5'], $this->mailbox['OPTIONS']);
		if (empty($dir))
		{
			return false;
		}

		$body = $this->client->fetch(true, $dir, $excerpt['MSG_UID'], '(BODY.PEEK[])', $error);

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

		$dir = MessageFolder::getFolderNameByHash($excerpt['DIR_MD5'], $this->mailbox['OPTIONS']);
		if (empty($dir))
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

					$isTextItem = $item->isText() && !$item->isAttachment();
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
			$dir,
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
					$partMime .= sprintf("\r\nContent-Disposition: ", $item->getDisposition()[0]);
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
		$dirs = $this->client->listMailboxes('*', $error, true);
		if (false === $dirs)
		{
			$this->errors = new Main\ErrorCollection($this->client->getErrors()->toArray());

			return false;
		}

		$imapDirs = array();
		$disabledDirs = array();
		$availableDirs = array();

		$outcomeDirs = array();
		$draftsDirs = array();
		$trashDirs = array();
		$spamDirs = array();
		$inboxDirs = array();
		$inboxTrees = array();

		foreach ($dirs as $i => $item)
		{
			if (strtoupper($item['name']) === 'INBOX')
			{
				if (!$inboxDirs)
				{
					$inboxDirs = [$item['name']];
				}
				else
				{
					$disabledDirs[] = $item['name'];
				}
			}
		}

		foreach ($dirs as $i => $item)
		{
			$imapDirs[$item['name']] = $item['path'];

			if (in_array(reset($item['path']), $inboxDirs))
			{
				$inboxTrees[$item['name']] = $item['path'];
			}

			if (preg_grep('/^ \x5c Noselect $/ix', $item['flags']))
			{
				$disabledDirs[] = $item['name'];
			}

			if (preg_grep('/^ \x5c Sent $/ix', $item['flags']))
			{
				$outcomeDirs[] = $item['name'];
			}

			if (preg_grep('/^ \x5c Drafts $/ix', $item['flags']))
			{
				$draftsDirs[] = $item['name'];
			}

			if (preg_grep('/^ \x5c Trash $/ix', $item['flags']))
			{
				$trashDirs[] = $item['name'];
			}

			if (preg_grep('/^ \x5c ( Junk | Spam ) $/ix', $item['flags']))
			{
				$spamDirs[] = $item['name'];
			}
		}

		// @TODO: filter disabled from income, outcome etc.

		$this->reloadMailboxOptions();

		$options = &$this->mailbox['OPTIONS'];

		$options['imap']['dirs'] = $imapDirs = $inboxTrees + $imapDirs;
		$options['imap']['disabled'] = $disabledDirs;

		$availableDirs = array_diff(array_keys($imapDirs), $disabledDirs);

		$options['imap'][MessageFolder::INCOME] = $inboxDirs;

		$options['imap'][MessageFolder::OUTCOME] = array_intersect(
			(array) $options['imap'][MessageFolder::OUTCOME],
			$availableDirs
		) ?: $outcomeDirs;

		$options['imap'][MessageFolder::DRAFTS] = array_intersect(
			(array) $options['imap'][MessageFolder::DRAFTS],
			$availableDirs
		) ?: $draftsDirs;
		$options['imap'][MessageFolder::DRAFTS] = $draftsDirs; // @TODO: remove if drafts dir settings implemented

		$options['imap'][MessageFolder::TRASH] = array_intersect(
			(array) $options['imap'][MessageFolder::TRASH],
			$availableDirs
		) ?: $trashDirs;

		$options['imap'][MessageFolder::SPAM] = array_intersect(
			(array) $options['imap'][MessageFolder::SPAM],
			$availableDirs
		) ?: $spamDirs;

		if (!empty($options['imap']['!sync_dirs']))
		{
			$options['imap']['sync_dirs'] = $options['imap']['!sync_dirs'];
		}

		if (!empty($options['imap']['sync_dirs']))
		{
			$options['imap']['ignore'] = array_values(array_diff(
				array_keys($imapDirs),
				(array) $options['imap']['sync_dirs']
			));

			unset($options['imap']['sync_dirs']);
		}

		if (!array_key_exists('ignore', $options['imap']))
		{
			$options['imap']['ignore'] = array_merge(
				(array) $options['imap'][MessageFolder::DRAFTS],
				(array) $options['imap'][MessageFolder::TRASH],
				(array) $options['imap'][MessageFolder::SPAM]
			);
		}

		$options['imap']['ignore'] = array_unique(array_merge(
			(array) $options['imap']['ignore'],
			$disabledDirs
		));

		Mail\MailboxTable::update(
			$this->mailbox['ID'],
			array(
				'OPTIONS' => $options,
			)
		);

		return $dirs;
	}

	public function cacheMeta()
	{
		$dirs = $this->cacheDirs();
		if (false === $dirs)
		{
			return false;
		}

		$meta = array();

		foreach ($dirs as $i => $item)
		{
			if (!in_array($item['name'], (array) $this->mailbox['OPTIONS']['imap']['ignore']))
			{
				if (!preg_grep('/^ \x5c Noselect $/ix', $item['flags']))
				{
					$data = $this->client->examine($item['name'], $error);
					if (false === $data)
					{
						$this->warnings->add($this->client->getErrors()->toArray());
					}
					else
					{
						$item = array_merge($item, $data);
					}
				}
			}

			$meta[$item['name']] = $item;
		}

		$this->reloadMailboxOptions();

		$options = &$this->mailbox['OPTIONS'];

		$options['imap']['total'] = array_reduce(
			$meta,
			function ($sum, $item) use (&$options)
			{
				return $sum + (in_array($item['name'], (array) $options['imap']['ignore']) ? 0 : $item['exists']);
			},
			0
		);

		Mail\MailboxTable::update(
			$this->mailbox['ID'],
			array(
				'OPTIONS' => $options,
			)
		);

		return $meta;
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
			$folderFrom = MessageFolder::getFolderNameByHash($message['DIR_MD5'], $this->mailbox['OPTIONS']);
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

		$meta = $this->cacheMeta();
		if (false === $meta)
		{
			return false;
		}

		$count = 0;

		$queue = array(
			'inbox' => array(),
			MessageFolder::OUTCOME => array(),
			'other' => array(),
			MessageFolder::TRASH => array(),
			MessageFolder::SPAM => array(),
		);
		foreach ($meta as $dir => $item)
		{
			if (in_array($dir, (array) $this->mailbox['OPTIONS']['imap']['ignore']))
			{
				continue;
			}

			if ('inbox' == strtolower(reset($item['path'])))
			{
				$queue['inbox'][$dir] = $item;
			}
			else if (in_array($dir, (array) $this->mailbox['OPTIONS']['imap'][MessageFolder::OUTCOME]))
			{
				$queue[MessageFolder::OUTCOME][$dir] = $item;
			}
			else if (in_array($dir, (array) $this->mailbox['OPTIONS']['imap'][MessageFolder::TRASH]))
			{
				$queue[MessageFolder::TRASH][$dir] = $item;
			}
			else if (in_array($dir, (array) $this->mailbox['OPTIONS']['imap'][MessageFolder::SPAM]))
			{
				$queue[MessageFolder::SPAM][$dir] = $item;
			}
			else
			{
				$queue['other'][$dir] = $item;
			}
		}

		$meta = call_user_func_array('array_merge', $queue);

		foreach ($meta as $dir => $item)
		{
			if ($item['exists'] > 0)
			{
				$count += $this->syncDir($dir);

				if ($this->isTimeQuotaExceeded())
				{
					break;
				}
			}
		}

		if (!$this->isTimeQuotaExceeded())
		{
			$this->unregisterMessages(array(
				'!@DIR_MD5' => array_map(
					'md5',
					array_diff(
						array_keys($meta),
						(array) $this->mailbox['OPTIONS']['imap']['ignore']
					)
				),
			));

			if (!empty($this->syncParams['full']))
			{
				foreach ($meta as $dir => $item)
				{
					$this->resyncDir($dir);

					if ($this->isTimeQuotaExceeded())
					{
						break;
					}
				}
			}
		}

		return $count;
	}

	public function syncDir($dir)
	{
		$count = 0;

		if (in_array($dir, (array) $this->mailbox['OPTIONS']['imap']['ignore']))
		{
			return $count;
		}

		while ($range = $this->getSyncRange($dir, $uidtoken))
		{
			$reverse = $range[0] > $range[1];

			sort($range);

			$messages = $this->client->fetch(
				true,
				$dir,
				join(':', $range),
				'(UID FLAGS INTERNALDATE RFC822.SIZE BODYSTRUCTURE BODY.PEEK[HEADER])',
				$error
			);

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

				break;
			}

			$reverse ? krsort($messages) : ksort($messages);

			$this->parseHeaders($messages);

			$this->blacklistMessages($dir, $messages);

			$this->prepareMessages($dir, $uidtoken, $messages);

			$hashesMap = array();
			foreach ($messages as $id => $item)
			{
				if ($this->syncMessage($dir, $uidtoken, $item, $hashesMap))
				{
					$count++;
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
		}

		return $count;
	}

	public function resyncDir($dir)
	{
		if (in_array($dir, (array) $this->mailbox['OPTIONS']['imap']['ignore']))
		{
			$this->unregisterMessages(array(
				'=DIR_MD5' => md5($dir),
			));

			return;
		}

		$meta = $this->client->select($dir, $error);
		if (false === $meta)
		{
			$this->warnings->add($this->client->getErrors()->toArray());

			return;
		}

		$uidtoken = $meta['uidvalidity'];

		if ($meta['exists'] > 0)
		{
			if ($uidtoken > 0)
			{
				$this->unregisterMessages(array(
					'=DIR_MD5'  => md5($dir),
					'<DIR_UIDV' => $uidtoken,
				));
			}
		}
		else
		{
			if ($this->client->ensureEmpty($dir, $error))
			{
				$this->unregisterMessages(array(
					'=DIR_MD5' => md5($dir),
				));
			}

			return;
		}

		$fetcher = function ($range) use ($dir)
		{
			$messages = $this->client->fetch(false, $dir, $range, '(UID FLAGS)', $error);

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

				return;
			}

			krsort($messages);

			return $messages;
		};

		$messages = $fetcher($meta['exists'] > 10000 ? sprintf('1,%u', $meta['exists']) : '1:*');

		if (empty($messages))
		{
			return;
		}

		$range = array(
			reset($messages)['UID'],
			end($messages)['UID'],
		);
		sort($range);

		$this->unregisterMessages(array(
			'=DIR_MD5' => md5($dir),
			'>MSG_UID' => 0,
			array(
				'LOGIC' => 'OR',
				'<MSG_UID' => $range[0],
				'>MSG_UID' => $range[1],
			),
		));

		if (!($meta['exists'] > 10000))
		{
			$this->resyncMessages($dir, $uidtoken, $messages);

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

			$this->resyncMessages($dir, $uidtoken, $messages);

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
			$messages[$id]['__from'] = array_unique(array_map('strtolower', array_filter(array_merge(
				\CMailUtil::extractAllMailAddresses($messages[$id]['__header']->getHeader('FROM')),
				\CMailUtil::extractAllMailAddresses($messages[$id]['__header']->getHeader('REPLY-TO'))
			), 'trim')));
		}
	}

	protected function blacklistMessages($dir, &$messages)
	{
		$trashDirs = (array) $this->mailbox['OPTIONS']['imap'][MessageFolder::TRASH];
		$spamDirs = (array) $this->mailbox['OPTIONS']['imap'][MessageFolder::SPAM];

		$targetDir = reset($spamDirs) ?: reset($trashDirs) ?: null;

		if (empty($targetDir) || in_array($dir, array_merge($trashDirs, $spamDirs)))
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
					'LOGIC' => 'OR',
					'=MAILBOX_ID' => $this->mailbox['ID'],
					array(
						'=MAILBOX_ID' => 0,
						'@USER_ID' => array(0, $this->mailbox['USER_ID']),
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
					$domain = substr($email, strrpos($email, '@'));
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
			if ($this->client->moveMails($targetMessages, $dir, $targetDir)->isSuccess())
			{
				$messages = array_diff_key($messages, $targetMessages);
			}
		}
	}

	protected function prepareMessages($dir, $uidtoken, &$messages)
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
				'=DIR_MD5' => md5($dir),
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
			$messageUid = md5(sprintf('%s:%u:%u', $dir, $uidtoken, $item['UID']));

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
				'DIR_MD5'      => md5($dir),
				'DIR_UIDV'     => $uidtoken,
				'MSG_UID'      => $item['UID'],
				'INTERNALDATE' => $messages[$id]['__internaldate'],
				'IS_SEEN'      => preg_grep('/^ \x5c Seen $/ix', $item['FLAGS']) ? 'Y' : 'N',
				'HEADER_MD5'   => $hashes[$id],
				'MESSAGE_ID'   => 0,
			);

			if ($bxuid = preg_grep('/^bxuid:?[a-f0-9]+$/i', $item['FLAGS']))
			{
				$messages[$id]['__replaces'] = preg_replace('/^bxuid:?/i', '', end($bxuid));
			}
			else if (preg_match('/X-Bitrix-Mail-Message-UID:\s*([a-f0-9]+)/i', $item['BODY[HEADER]'], $matches))
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
			foreach ((array) $hashesMap[$item['HEADER_MD5']] as $id)
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

	protected function resyncMessages($dir, $uidtoken, &$messages)
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
				'=DIR_MD5' => md5($dir),
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
			$messageUid = md5(sprintf('%s:%u:%u', $dir, $uidtoken, $item['UID']));

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
						$this->mailbox['ID'], $dir, $uidtoken, $item['UID']
					),
					'mail', 0, false
				);
				*/
			}
		}

		foreach ($update as $seen => $items)
		{
			if (!empty($items))
			{
				if (in_array($seen, array('S', 'U')))
				{
					$method = 'S' == $seen ? 'seen' : 'unseen';
					$this->client->$method($items, $dir);
				}
				else
				{
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
			$this->unregisterMessages(
				array(
					'@ID' => array_keys($excerpt),
				),
				$excerpt
			);
		}
	}

	protected function syncMessage($dir, $uidtoken, $message, &$hashesMap = array())
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

		if (!$this->registerMessage($fields, $message['__replaces']))
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
			$messageId = $this->cacheMessage(
				$message,
				array(
					'timestamp' => $message['__internaldate']->getTimestamp(),
					'size' => $message['RFC822.SIZE'],
					'outcome' => in_array($this->mailbox['EMAIL'], $message['__from']),
					'draft' => in_array($dir, $this->mailbox['OPTIONS']['imap'][MessageFolder::DRAFTS]) || preg_grep('/^ \x5c Draft $/ix', $message['FLAGS']),
					'trash' => in_array($dir, $this->mailbox['OPTIONS']['imap'][MessageFolder::TRASH]),
					'spam' => in_array($dir, $this->mailbox['OPTIONS']['imap'][MessageFolder::SPAM]),
					'seen' => $fields['IS_SEEN'] == 'Y',
					'hash' => $fields['HEADER_MD5'],
					'lazy_attachments' => $this->isSupportLazyAttachments(),
					'excerpt' => $fields,
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

		$dir = MessageFolder::getFolderNameByHash($excerpt['DIR_MD5'], $this->mailbox['OPTIONS']);
		if (empty($dir))
		{
			return false;
		}

		$message = $this->client->fetch(true, $dir, $excerpt['MSG_UID'], '(BODYSTRUCTURE)', $error);
		if (empty($message['BODYSTRUCTURE']))
		{
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
				new Main\Error((string) $message['BODYSTRUCTURE'], -1),
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
				if ($item->isMultipart() || $item->isText() && !$item->isAttachment())
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

		list($bodyHtml, $bodyText, $attachments) = $message['__bodystructure']->traverse(
			function (Mail\Imap\BodyStructure $item, &$subparts) use (&$message)
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

						if ('' !== $html && '' === $text)
						{
							$text = html_entity_decode(
								htmlToTxt($html),
								ENT_QUOTES | ENT_HTML401,
								$this->mailbox['LANG_CHARSET']
							);
						}
						else if ('' === $html && '' !== $text)
						{
							$html = txtToHtml($text, false, 120);
						}
					}
					else
					{
						foreach ($subparts as $part)
						{
							$part = $part[0];

							if ('' !== $part[0] && '' === $part[1])
							{
								$part[1] = html_entity_decode(
									htmlToTxt($part[0]),
									ENT_QUOTES | ENT_HTML401,
									$this->mailbox['LANG_CHARSET']
								);
							}
							else if ('' === $part[0] && '' !== $part[1])
							{
								$part[0] = txtToHtml($part[1], false, 120);
							}

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

					if (!$item->isText() || $item->isAttachment())
					{
						$attachments[] = empty($part) ? $item->getNumber() : $part;
					}
					else if (!empty($part))
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

		$dummyBody;
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

	protected function getSyncRange($dir, &$uidtoken)
	{
		$meta = $this->client->select($dir, $error);
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

		$rangeGetter = function ($min, $max) use ($dir, $uidtoken, &$rangeGetter)
		{
			$size = $max - $min + 1;

			$set = array();
			$d = $size < 1000 ? 100 : pow(10, round(ceil(log10($size) - 0.7) / 2) * 2 - 2);
			for ($i = $min; $i <= $max; $i = $i + $d)
			{
				$set[] = $i;
			}

			if (count($set) > 1 && end($set) + 100 >= $max)
			{
				array_pop($set);
			}

			$set[] = $max;

			$set = $this->client->fetch(false, $dir, join(',', $set), '(UID)', $error);
			if (empty($set))
			{
				return false;
			}

			ksort($set);

			static $uidMin, $uidMax;

			if (!isset($uidMin, $uidMax))
			{
				$minmax = $this->getUidRange($dir, $uidtoken);

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
			else if (end($set)['UID'] > $uidMax)
			{
				$max = current($set)['id'];
				do
				{
					$exmax = $max;

					$max = current($set)['id'];
					$min = prev($set)['id'];
				}
				while (current($set)['UID'] > $uidMax && prev($set) && next($set));

				if ($max - $min > 200)
				{
					return $rangeGetter($min, $max);
				}
				else
				{
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
			else if (reset($set)['UID'] < $uidMin)
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

	protected function getUidRange($dir, $uidtoken)
	{
		$filter = array(
			'=DIR_MD5' => md5($dir),
			'=DIR_UIDV' => $uidtoken,
			'>MSG_UID' => 0,
		);

		$min = $this->listMessages(
			array(
				'select' => array(
					'MIN' => 'MSG_UID',
				),
				'filter' => $filter,
				'order' => array(
					'MSG_UID' => 'ASC',
				),
				'limit' => 1,
			),
			false
		)->fetch();

		$max = $this->listMessages(
			array(
				'select' => array(
					'MAX' => 'MSG_UID',
				),
				'filter' => $filter,
				'order' => array(
					'MSG_UID' => 'DESC',
				),
				'limit' => 1,
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
