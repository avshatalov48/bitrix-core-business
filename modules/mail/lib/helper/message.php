<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Mail\Internals\MessageAccessTable;
use Bitrix\Mail\Internals\MessageClosureTable;
use Bitrix\Mail\MailboxTable;
use Bitrix\Main;
use Bitrix\Main\Security;
use Bitrix\Mail\Internals;
use Bitrix\Mail\Helper\MessageAccess as AccessHelper;

class Message
{

	// entity types with special access rules (group tokens)
	public const ENTITY_TYPE_IM_CHAT = MessageAccessTable::ENTITY_TYPE_IM_CHAT;
	public const ENTITY_TYPE_CALENDAR_EVENT = MessageAccessTable::ENTITY_TYPE_CALENDAR_EVENT;

	/**
	 * Adapts a message(result of Mail\MailMessageTable::getList) for output in the public interface.
	 *
	 * @param array &$message(result of Mail\MailMessageTable::getList. Changes the data in a variable!).
	 *
	 * @return array(modified $message).
	 */
	public static function prepare(&$message)
	{
		$message['__email'] = null;
		foreach (array($message['MAILBOX_EMAIL'], $message['MAILBOX_NAME'], $message['MAILBOX_LOGIN']) as $item)
		{
			$address = new Main\Mail\Address($item);
			if ($address->validate())
			{
				$message['__email'] = $address->getEmail();
				break;
			}
		}

		$fieldsMap = array(
			'__from' => 'FIELD_FROM',
			'__reply_to' => 'FIELD_REPLY_TO',
			'__to' => 'FIELD_TO',
			'__cc' => 'FIELD_CC',
			'__bcc' => 'FIELD_BCC',
			'__rcpt' => 'FIELD_RCPT',
		);

		if ('' != $message['HEADER'])
		{
			foreach ($fieldsMap as $field)
			{
				if (mb_strlen($message[$field]) == 255)
				{
					$parsedHeader = \CMailMessage::parseHeader($message['HEADER'], LANG_CHARSET);

					$message['FIELD_FROM'] = $parsedHeader->getHeader('FROM');
					$message['FIELD_REPLY_TO'] = $parsedHeader->getHeader('REPLY-TO');
					$message['FIELD_TO'] = $parsedHeader->getHeader('TO');
					$message['FIELD_CC'] = $parsedHeader->getHeader('CC');
					$message['FIELD_BCC'] = join(', ', array_merge(
						(array) $parsedHeader->getHeader('X-Original-Rcpt-to'),
						(array) $parsedHeader->getHeader('BCC')
					));

					break;
				}
			}
		}

		foreach ($fieldsMap as $extField => $field)
		{
			$isFromField = in_array($extField, array('__from', '__reply_to'));

			$message[$extField] = array();
			foreach (explode(',', $message[$field]) as $item)
			{
				if (trim($item))
				{
					$address = new Main\Mail\Address($item);
					if ($address->validate())
					{
						if ($isFromField && $address->getEmail() == $message['__email'])
						{
							$message['__is_outcome'] = true;
						}

						$message[$extField][] = array(
							'name' => $address->getName(),
							'email' => $address->getEmail(),
							'formated' => ($address->getName() ? $address->get() : $address->getEmail()),
						);
					}
					else
					{
						$message[$extField][] = array(
							'name'  => $item,
						);
					}
				}
			}
		}

		if (empty($message['__reply_to']))
		{
			$message['__reply_to'] = $message['__from'];
		}

		// @TODO: path
		$message['__href'] = sprintf('/mail/message/%u', $message['ID']);

		$urlManager = Attachment\Storage::getUrlManager();

		if (!empty($message['__files']) && is_array($message['__files']))
		{
			$urlParams = array();

			if (isset($_REQUEST['mail_uf_message_token']) && is_string($_REQUEST['mail_uf_message_token']))
			{
				$urlParams['mail_uf_message_token'] = $_REQUEST['mail_uf_message_token'];
			}

			foreach ($message['__files'] as $k => $item)
			{
				if ($diskFile = Attachment\Storage::getObjectByAttachment($item, true))
				{
					$message['__files'][$k] = array(
						'id' => sprintf('n%u', $diskFile->getId()),
						'name' => $item['FILE_NAME'],
						'url' => $urlManager->getUrlForShowFile($diskFile, $urlParams),
						'size' => \CFile::formatSize($diskFile->getSize()),
						'fileId' => $diskFile->getFileId(),
						'objectId' => $diskFile->getId(),
						'bytes' => $diskFile->getSize(),
					);

					if (\Bitrix\Disk\TypeFile::isImage($diskFile))
					{
						$message['__files'][$k]['preview'] = $urlManager->getUrlForShowFile(
							$diskFile,
							array_merge(
								array('width' => 80, 'height' => 80, 'exact' => 'Y'),
								$urlParams
							)
						);
					}

					$inlineParams = array_merge(
						array('__bxacid' => sprintf('n%u', $diskFile->getId())),
						$urlParams
					);
					$message['BODY_HTML'] = preg_replace(
						sprintf('/("|\')\s*aid:%u\s*\1/i', $item['ID']),
						sprintf('\1%s\1', $urlManager->getUrlForShowFile($diskFile, $inlineParams)),
						$message['BODY_HTML']
					);
				}
				else
				{
					$file = \CFile::getFileArray($item['FILE_ID']);
					if (!empty($file) && is_array($file))
					{
						$message['__files'][$k] = array(
							'id' => $file['ID'],
							'name' => $item['FILE_NAME'],
							'url' => $file['SRC'],
							'size' => \CFile::formatSize($file['FILE_SIZE']),
							'fileId' => $file['ID'],
							'bytes' => $file['FILE_SIZE'],
						);

						if (\CFile::isImage($item['FILE_NAME'], $item['CONTENT_TYPE']))
						{
							$preview = \CFile::resizeImageGet(
								$file, array('width' => 80, 'height' => 80),
								BX_RESIZE_IMAGE_EXACT, false
							);

							if (!empty($preview['src']))
							{
								$message['__files'][$k]['preview'] = $preview['src'];
							}
						}

						$message['BODY_HTML'] = preg_replace(
							sprintf('/("|\')\s*aid:%u\s*\1/i', $item['ID']),
							sprintf('\1%s\1', $file['SRC']),
							$message['BODY_HTML']
						);
					}
					else
					{
						unset($message['__files'][$k]);
					}
				}
			}
		}

		return $message;
	}

	public static function hasAccess(&$message, $userId = null)
	{
		global $USER;

		if (!($userId > 0 || is_object($USER) && $USER->isAuthorized()))
		{
			return false;
		}

		if (!($userId > 0))
		{
			$userId = $USER->getId();
		}

		$messageAccess = \Bitrix\Mail\MessageAccess::createByMessageId($message['ID'], $userId);
		$access = $messageAccess->isOwner();

		$message['__access_level'] = $access ? 'full' : false;

		// check access by tokens
		if (!$access && isset($_REQUEST['mail_uf_message_token']))
		{
			$token = $signature = '';
			if (is_string($_REQUEST['mail_uf_message_token']) && mb_strpos($_REQUEST['mail_uf_message_token'], ':') > 0)
			{
				list($token, $signature) = explode(':', $_REQUEST['mail_uf_message_token'], 2);
			}

			if ($token <> '' && $signature <> '')
			{
				$excerpt = MessageAccessTable::getList(array(
					'select' => array('SECRET', 'MESSAGE_ID', 'ENTITY_TYPE', 'ENTITY_ID'),
					'filter' => array(
						'=TOKEN' => $token,
						'=MAILBOX_ID' => $message['MAILBOX_ID'],
					),
					'limit' => 1,
				))->fetch();

				if (!empty($excerpt['SECRET']))
				{
					if (self::checkAccessForEntityToken($excerpt['ENTITY_TYPE'], (int)$excerpt['ENTITY_ID'], $userId))
					{
						$salt = self::getSaltByEntityType($excerpt['ENTITY_TYPE'], (int)$excerpt['ENTITY_ID'], $userId);

						$signer = new Security\Sign\Signer(new Security\Sign\HmacAlgorithm('md5'));

						if ($signer->validate($excerpt['SECRET'], $signature, $salt))
						{
							$access = $message['ID'] == $excerpt['MESSAGE_ID'];

							if (!$access) // check parent access
							{
								$access = (bool) MessageClosureTable::getList(array(
									'select' => array('PARENT_ID'),
									'filter' => array(
										'=MESSAGE_ID' => $message['ID'],
										'=PARENT_ID' => $excerpt['MESSAGE_ID'],
									),
								))->fetch();
							}
						}
					}
				}
			}
		}

		// check access by direct links
		if (!$access)
		{
			$access = $messageAccess->canViewMessage();
		}

		if (false === $message['__access_level'])
		{
			$message['__access_level'] = $access ? 'read' : false;
		}

		return $access;
	}

	public static function prepareSearchContent(&$fields)
	{
		// @TODO: filter short words, filter duplicates, str_rot13?
		return str_rot13(join(
			' ',
			array(
				$fields['FIELD_FROM'],
				$fields['FIELD_REPLY_TO'],
				$fields['FIELD_TO'],
				$fields['FIELD_CC'],
				$fields['FIELD_BCC'],
				$fields['SUBJECT'],
				$fields['BODY'],
			)
		));
	}

	public static function prepareSearchString($string)
	{
		return str_rot13($string);
	}

	public static function getTotalUnseenCount($userId)
	{
		$mailboxes = MailboxTable::getUserMailboxes($userId);

		if (empty($mailboxes))
		{
			return 0;
		}

		$mailboxIds = array_column($mailboxes, 'ID');

		$totalUnseen = (int)Internals\MailCounterTable::getList([
			'select' => [
				new \Bitrix\Main\Entity\ExpressionField(
					'UNSEEN',
					'SUM(VALUE)'
				),
			],
			'filter' => [
				'=ENTITY_TYPE' => 'MAILBOX',
				'@ENTITY_ID' => $mailboxIds,
			],
		])->fetchAll()[0]['UNSEEN'];

		return $totalUnseen;
	}

	public static function getTotalUnseenForMailboxes($userId)
	{
		$mailboxes = MailboxTable::getUserMailboxes($userId);

		if (empty($mailboxes))
		{
			return array();
		}

		$mailboxIds = array_column($mailboxes, 'ID');

		$totalUnseen = Internals\MailCounterTable::getList([
			'select' => [
				'VALUE',
				'ENTITY_ID',
			],
			'filter' => [
				'ENTITY_TYPE' => 'MAILBOX',
				'@ENTITY_ID' => $mailboxIds,
			],
		])->fetchAll();

		$result = [];

		foreach ($totalUnseen as $index => $item)
		{
			$result[$item['ENTITY_ID']] = [
				'UNSEEN' => $item['VALUE'],
			];
		}

		return $result;
	}

	public static function ensureAttachments(&$message)
	{
		if ($message['ATTACHMENTS'] > 0 || !($message['OPTIONS']['attachments'] > 0))
		{
			return;
		}

		if (Main\Config\Option::get('mail', 'save_attachments', B_MAIL_SAVE_ATTACHMENTS) !== 'Y')
		{
			return;
		}

		$mailboxHelper = Mailbox::createInstance($message['MAILBOX_ID'], false);

		$attachments = empty($mailboxHelper) ? false : $mailboxHelper->downloadAttachments($message);

		if (false === $attachments)
		{
			$logEntry = sprintf(
				'Helper\Message: Attachments downloading failed (%u:%s:%u)',
				$message['MAILBOX_ID'],
				$message['DIR_MD5'],
				$message['MSG_UID']
			);

			if (!empty($mailboxHelper) && !$mailboxHelper->getErrors()->isEmpty())
			{
				$logEntry .= PHP_EOL . join(PHP_EOL, $mailboxHelper->getErrors()->toArray());
			}

			addMessage2Log($logEntry, 'mail', 2);

			return false;
		}

		foreach ($attachments as $i => $item)
		{
			$attachFields = array(
				'MESSAGE_ID'   => $message['ID'],
				'FILE_NAME'    => $item['FILENAME'],
				'CONTENT_TYPE' => $item['CONTENT-TYPE'],
				'FILE_DATA'    => $item['BODY'],
				'CONTENT_ID'   => $item['CONTENT-ID'],
			);

			$attachmentId = \CMailMessage::addAttachment($attachFields);

			if ($attachmentId > 0)
			{
				$message['ATTACHMENTS']++;

				$message['BODY_HTML'] = preg_replace(
					sprintf(
						'/<img([^>]+)src\s*=\s*(\'|\")?\s*(http:\/\/cid:%s)\s*\2([^>]*)>/is',
						preg_quote($item['CONTENT-ID'], '/')
					),
					sprintf('<img\1src="aid:%u"\4>', $attachmentId),
					$message['BODY_HTML']
				);
			}
		}

		if ($message['ATTACHMENTS'] > 0)
		{
			\CMailMessage::update($message['ID'], array('BODY_HTML' => $message['BODY_HTML']));

			return $message['ID'];
		}
	}

	public static function resync(&$message)
	{
		$mailboxHelper = Mailbox::createInstance($message['MAILBOX_ID'], false);

		$result = empty($mailboxHelper) ? false : $mailboxHelper->resyncMessage($message);

		if (false === $result)
		{
			$logEntry = sprintf(
				'Helper\Message: Message resync failed (%u:%s:%u)',
				$message['MAILBOX_ID'],
				$message['DIR_MD5'],
				$message['MSG_UID']
			);

			if (!empty($mailboxHelper) && !$mailboxHelper->getErrors()->isEmpty())
			{
				$logEntry .= PHP_EOL . join(PHP_EOL, $mailboxHelper->getErrors()->toArray());
			}

			addMessage2Log($logEntry, 'mail', 3);
		}

		return $result;
	}

	public static function getSaltByEntityType(string $entityType, int $entityId, ?int $userId = null): string
	{
		switch ($entityType)
		{
			case Message::ENTITY_TYPE_IM_CHAT:
				return sprintf('chat%u', $entityId);
			case Message::ENTITY_TYPE_CALENDAR_EVENT:
				return sprintf('event'.'%u', $entityId);
			default:
				// per-user tokens for entity types like TASKS_TASK, CRM_ACTIVITY, ...
				return sprintf('user'.'%u', $userId);
		}
	}

	public static function isMailboxOwner(int $mailboxId, int $userId): bool
	{
		return (bool)MailboxTable::getUserMailbox($mailboxId, $userId);
	}

	private static function checkAccessForEntityToken(?string $entityType, int $entityId, int $userId): bool
	{
		switch ($entityType)
		{
			case Message::ENTITY_TYPE_IM_CHAT:
				return AccessHelper::checkAccessForChat($entityId, $userId);
			case Message::ENTITY_TYPE_CALENDAR_EVENT:
				return AccessHelper::checkAccessForCalendarEvent($entityId, $userId);
			default:
				return true; // tasks, crm creates per-user tokens
		}
	}
}
