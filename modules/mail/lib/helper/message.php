<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Mail\Integration\Calendar\ICal\ICalMailManager;
use Bitrix\Mail\Internals\MailMessageAttachmentTable;
use Bitrix\Mail\Internals\MessageAccessTable;
use Bitrix\Mail\Internals\MessageClosureTable;
use Bitrix\Mail\MailboxTable;
use Bitrix\Mail\MailMessageTable;
use Bitrix\Main;
use Bitrix\Main\Security;
use Bitrix\Mail\Internals;
use Bitrix\Mail\Helper\MessageAccess as AccessHelper;
use Bitrix\Main\Config\Option;

class Message
{
	// entity types with special access rules (group tokens)
	public const ENTITY_TYPE_IM_CHAT = MessageAccessTable::ENTITY_TYPE_IM_CHAT;
	public const ENTITY_TYPE_CALENDAR_EVENT = MessageAccessTable::ENTITY_TYPE_CALENDAR_EVENT;
	private const MAX_FILE_SIZE_MAIL_ATTACHMENT = 20000000;

	public static function getMaxAttachedFilesSize()
	{
		// @TODO: Until the 'Main' module update of is released with the reset of the option for the cloud
		if (IsModuleInstalled('bitrix24'))
		{
			return self::MAX_FILE_SIZE_MAIL_ATTACHMENT;
		}
		else
		{
			return (int)Option::get('main', 'max_file_size');
		}
	}

	public static function getMaxAttachedFilesSizeAfterEncoding()
	{
		return floor(static::getMaxAttachedFilesSize()/4)*3;
	}

	/**
	 * Returns a whitelist of attributes for the html sanitizer
	 * (\CBXSanitizer::SanitizeHtml)
	 *
	 * @return array
	 */
	public static function getWhitelistTagAttributes()
	{
		$validTagAttributes = [
			'colspan',
			'border',
			'bgcolor',
			'width',
			'background',
			'style',
			'align',
			'height',
			'background-color',
			'border',
			'ltr',
			'rtl',
			'class',
		];

		$tableAttributes = array_merge(
			$validTagAttributes,
			[
				'cellpadding',
				'cellspacing',
			]
		);

		$tdAttributes = array_merge(
			$validTagAttributes,
			[
				'rowspan',
			]
		);

		return [
			'style' => [],
			'colgroup' => [],
			'col' => ['width'],
			'table' => $tableAttributes,
			'center' => $validTagAttributes,
			'div' => $validTagAttributes,
			'td' =>$tdAttributes,
		];
	}

	private static function prepareField($value)
	{
		if (trim($value))
		{
			$address = new Main\Mail\Address($value);

			if ($address->validate())
			{
				return [
					'name' => $address->getName(),
					'email' => $address->getEmail(),
					'formated' => ($address->getName() ? $address->get() : $address->getEmail()),
				];
			}
			else
			{
				return [
					'name'  => $value,
				];
			}
		}

		return false;
	}

	private static function checkMessageIsOutcomeByField($message,$keyField,$value)
	{
		if (trim($value))
		{
			$isFromField = in_array($keyField, ['__from', '__reply_to']);
			$address = new Main\Mail\Address($value);

			if ($address->validate())
			{
				if ($isFromField && $address->getEmail() == $message['__email'])
				{
					return true;
				}
			}
		}

		return false;
	}

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

		foreach ($fieldsMap as $fieldKey => $fieldName)
		{
			$message[$fieldKey] = [];

			if($fieldName === 'FIELD_FROM')
			{
				if(static::checkMessageIsOutcomeByField($message,$fieldKey,$message[$fieldName]))
				{
					$message['__is_outcome'] = true;
				}

				$filed = static::prepareField($message[$fieldName]);

				if($filed !== false)
				{
					$message[$fieldKey][] = $filed;
				}

				continue;
			}

			foreach (explode(',', $message[$fieldName]) as $item)
			{
				if(static::checkMessageIsOutcomeByField($message,$fieldKey,$item))
				{
					$message['__is_outcome'] = true;
				}

				$filed = static::prepareField($item);

				if($filed !== false)
				{
					$message[$fieldKey][] = $filed;
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

		if (
			!($userId > 0 || is_object($USER) && $USER->isAuthorized()) ||
			//If message id = 0 , the message is deleted or not loaded:
			is_null($message['ID']) || $message['ID'] === 0 || $message['ID'] === '0'
		)
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
				[$token, $signature] = explode(':', $_REQUEST['mail_uf_message_token'], 2);
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
				self::isolateBase64Files((string)$fields['BODY']),
			)
		));
	}

	public static function prepareSearchString($string)
	{
		return str_rot13($string);
	}

	/**
	 * @param $userId
	 * @param $onlyGeneralCounter
	 * @return array|int
	 */
	public static function getCountersForUserMailboxes($userId, $onlyGeneralCounter = false)
	{
		static $countersForUsers;

		if (empty($countersForUsers))
		{
			$countersForUsers = [];
		}

		if (!isset($countersForUsers[$userId]))
		{
			$mailboxes = MailboxTable::getUserMailboxes($userId);

			if (empty($mailboxes))
			{
				return array();
			}

			$mailboxIds = array_column($mailboxes, 'ID');

			$totalUnseen = Internals\MailCounterTable::getList([
				'select' => [
					new \Bitrix\Main\Entity\ExpressionField(
						'UNSEEN',
						'SUM(VALUE)'
					),
					'VALUE',
					'ENTITY_ID',
				],
				'filter' => [
					'=ENTITY_TYPE' => 'MAILBOX',
					'@ENTITY_ID' => $mailboxIds,
				],
			])->fetchAll();

			$counters = [];

			$totalCounter = 0;

			foreach ($totalUnseen as $index => $item)
			{
				$totalCounter += $item['VALUE'];

				$counters[$item['ENTITY_ID']] = [
					'UNSEEN' => $item['VALUE'],
				];
			}

			$countersForUsers[$userId] = [
				'mailboxesWithCounters' => $counters,
				'totalCounter' => $totalCounter,
			];
		}

		if($onlyGeneralCounter)
		{
			return $countersForUsers[$userId]['totalCounter'];
		}
		else
		{
			return $countersForUsers[$userId]['mailboxesWithCounters'];
		}
	}

	public static function ensureAttachments(&$message)
	{
		if ($message['ATTACHMENTS'] > 0 || !($message['OPTIONS']['attachments'] > 0))
		{
			return false;
		}

		if (Option::get('mail', 'save_attachments', B_MAIL_SAVE_ATTACHMENTS) !== 'Y')
		{
			return false;
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

		$originalBody = $message['BODY_HTML'] ?? null;
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

				if (isset($message['BODY_HTML']) && mb_strlen($message['BODY_HTML']) > 0)
				{
					$bodyWithReplaced = self::replaceBodyInlineImgContentId(
						(string)$message['BODY_HTML'],
						(string)$item['CONTENT-ID'],
						$attachmentId
					);
					if ($bodyWithReplaced)
					{
						$message['BODY_HTML'] = $bodyWithReplaced;
					}
				}
			}
		}

		if ($message['ATTACHMENTS'] > 0)
		{
			if ($originalBody !== $message['BODY_HTML'])
			{
				\CMailMessage::update($message['ID'], ['BODY_HTML' => $message['BODY_HTML']], $message['MAILBOX_ID']);
			}

			return $message['ID'];
		}
	}

	public static function parseAddressList($column)
	{
		$column = trim($column);
		//'email@email' => 'email@domain'
		//'Name <email@domain>, Name2 <email2@domain2>' => 'Name <email@domain'
		$columns = preg_split("/>,?/", $column);
		$validColumns = [];

		foreach ($columns as $value)
		{
			//'email@email' => 'email@domain'
			//'Name <email@domain' => 'Name <email@domain>'
			if(preg_match("/</", $value))
			{
				$value.='>';
			}

			if($value !== '')
			{
				$validColumns[] = $value;
			}
		}

		return $validColumns;
	}

	public static function isolateSelector($matches)
	{
		$head = $matches['head'];
		$body = $matches['body'];
		$prefix = 'mail-message-';
		$wrapper = '#mail-message-wrapper ';
		if(substr($head,0,1)==='@') $wrapper ='';
		$closure = $matches['closure'];
		$head = preg_replace('%([\.#])([a-z][-_a-z0-9]+)%msi', '$1'.$prefix.'$2', $head);
		return $wrapper.$head.$body.$closure;
	}

	public static function isolateStylesInTheTag($matches)
	{
		$wrapper = '#mail-message-wrapper ';
		$openingTag = $matches['openingTag'];
		$closingTag = $matches['closingTag'];
		$styles = $matches['styles'];
		$bodySelectorPattern = '#(.*?)(^|\s)(body)\s*((?:\{)(?:.*?)(?:\}))(.*)#msi';
		$bodySelector = preg_replace($bodySelectorPattern, '$2'.$wrapper.'$4', $styles);
		//cut off body selector
		$styles = preg_replace($bodySelectorPattern, '$1$5', $styles);
		$styles = preg_replace('#(^|\s)(body)\s*({)#isU', '$1mail-msg-view-body$3', $styles);
		$styles = preg_replace_callback('%(?:^|\s)(?<head>[@#\.]?[a-z].*?\{)(?<body>.*?)(?<closure>\})%msi', 'static::isolateSelector', $styles);
		return  $openingTag.$bodySelector.$styles.$closingTag;
	}

	public static function isolateStylesInTheBody($html)
	{
		$prefix = 'mail-message-';
		$html = preg_replace('%((?:^|\s)(?:class|id)(?:^|\s*)(?:=)(?:^|\s*)(\"|\'))((?:.*?)(?:\2))%', '$1'.$prefix.'$3', $html);
		return $html;
	}

	public static function isolateMessageStyles($messageHtml)
	{
		//isolates the positioning of the element
		$messageHtml = preg_replace('%((?:^|\s)position(?:^|\s)?:(?:^|\s)?)(absolute|fixed|inherit)%', '$1relative', $messageHtml);
		//remove media queries
		$messageHtml = preg_replace('%@media\b[^{]*({((?:[^{}]+|(?1))*)})%msi', '', $messageHtml);
		//remove loading fonts
		$messageHtml = preg_replace('%@font-face\b[^{]*({(?>[^{}]++|(?1))*})%msi', '', $messageHtml);
		$messageHtml = static::isolateStylesInTheBody($messageHtml);
		$messageHtml = preg_replace_callback('|(?<openingTag><style[^>]*>)(?<styles>.*)(?<closingTag><\/style>)|isU', 'static::isolateStylesInTheTag',$messageHtml);
		return $messageHtml;
	}

	public static function sanitizeHtml($html, $isolateStyles = false)
	{
		$cleared = preg_replace('/<!--.*?-->/is', '', $html);
		$cleared = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $cleared);
		$cleared = preg_replace('/<title[^>]*>.*?<\/title>/is', '', $cleared);
		$sanitizer = new \CBXSanitizer();
		$sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
		$sanitizer->applyDoubleEncode(false);
		$sanitizer->addTags(static::getWhitelistTagAttributes());
		$cleared = $sanitizer->sanitizeHtml($cleared);

		if($isolateStyles)
		{
			$cleared = static::isolateMessageStyles($cleared);
		}

		return $cleared;
	}

	public static function reSyncBody($mailboxId, $messageIds)
	{
		if (empty($messageIds) || !is_array($messageIds))
		{
			return false;
		}

		$messages = \Bitrix\Mail\MailMessageUidTable::getList([
			'select' => [
				'*'
			],
			'filter' => [
				'=MAILBOX_ID' => $mailboxId,
				'@MESSAGE_ID' => $messageIds,
			],
		]);

		$notProcessed = array_combine($messageIds, $messageIds);

		$mailboxHelper = Mailbox::createInstance($mailboxId, false);

		if(!empty($mailboxHelper))
		{
			$mailbox = $mailboxHelper->getMailbox();

			while ($message = $messages->fetch())
			{
				$technicalTitle = $mailboxHelper->downloadMessage($message);
				if ($technicalTitle)
				{
					$charset = $mailbox['CHARSET'] ?: $mailbox['LANG_CHARSET'];
					[$header, $html, $text, $attachments] = \CMailMessage::parseMessage($technicalTitle, $charset);

					if (\CMailMessage::isLongMessageBody($text))
					{
						[$text, $html] = \CMailMessage::prepareLongMessage($text, $html);
					}

					if (rtrim($text) || $html)
					{
						\CMailMessage::update(
							$message['MESSAGE_ID'],
							[
								'BODY' => rtrim($text),
								'BODY_HTML' => $html,
								MailMessageTable::FIELD_SANITIZE_ON_VIEW => 1,
							]
						);
					}
				}
				self::updateMailEntityOptionsRow($mailboxId, (int)$message['MESSAGE_ID']);
				unset($notProcessed[$message['MESSAGE_ID']]);
			}
		}

		foreach ($notProcessed as $messageId)
		{
			self::updateMailEntityOptionsRow($mailboxId, (int)$messageId);
		}

		return true;
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

	/**
	 * @param $mailboxId
	 * @param $messageId
	 * @return void
	 */
	public static function updateMailEntityOptionsRow($mailboxId, $messageId): void
	{
		Internals\MailEntityOptionsTable::update([
				'MAILBOX_ID' => $mailboxId,
				'ENTITY_ID' => $messageId,
				'ENTITY_TYPE' => 'MESSAGE',
				'PROPERTY_NAME' => 'UNSYNC_BODY',
			],
			[
				'VALUE' => 'N',
			]
		);
	}

	/**
	 * Is message body contains link to attachment
	 *
	 * @param string $body HTML body of message
	 *
	 * @return bool
	 */
	public static function isBodyNeedUpdateAfterLoadAttachments(string $body): bool
	{
		return preg_match('/<img([^>]+)src\s*=\s*([\'"])?\s*((?:http:\/\/)?cid:.+)\s*\2([^>]*)>/is', $body);
	}

	/**
	 * Replace html body inline image content id with attachment id
	 *
	 * @param string $body HTML string
	 * @param string $contentId Content Id in img tag (with or without http://)
	 * @param int $attachmentId Attachment ID in DB
	 *
	 * @return string
	 */
	public static function replaceBodyInlineImgContentId(string $body, string $contentId, int $attachmentId): string
	{
		return (string)preg_replace(
			sprintf('/<img([^>]+)src\s*=\s*(\'|\")?\s*((?:http:\/\/)?cid:%s)\s*\2([^>]*)>/is', preg_quote($contentId, '/')),
			sprintf('<img\1src="aid:%u"\4>', $attachmentId),
			$body
		);
	}

	public static function isolateBase64Files(string $text): string
	{
		$pattern = '/\[\s*data:(?!text\b)[^;]+;base64,\S+ \]/';

		return (string)preg_replace($pattern, '', $text);
	}

	public static function isIcalMessage(\Bitrix\Mail\Item\Message $message)
	{
		$attachments = MailMessageAttachmentTable::getList([
			'select' => [
				'ID',
				'FILE_ID',
				'FILE_NAME',
				'FILE_SIZE',
				'CONTENT-TYPE' => 'CONTENT_TYPE',
			],
			'filter' => [
				'=MESSAGE_ID'   => $message->getId(),
				'@CONTENT_TYPE' => ICalMailManager::CONTENT_TYPES
			],
		])->fetchAll();

		return ICalMailManager::hasICalAttachments($attachments);

	}
}
