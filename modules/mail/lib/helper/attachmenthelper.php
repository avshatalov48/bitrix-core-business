<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Mail\Internals\MailboxDirectoryTable;
use Bitrix\Mail\Internals\MailMessageAttachmentTable;
use Bitrix\Mail\MailboxTable;
use Bitrix\Mail\MailMessageTable;
use Bitrix\Mail\MailMessageUidTable;
use Bitrix\Mail\Imap;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Web\MimeType;

class AttachmentStructure
{
	public function __construct(
		public ?string $name,
		public ?int $size,
		public ?string $type,
		public ?string $content = null,
		public ?int $diskId = null,
		public ?int $imageWidth = null,
		public ?int $imageHeight = null,
		public ?string $contentId = null,
		public ?int $attachmentId = null,
	)
	{}
}

class MessageStructure
{
	public function __construct(
		public int $mailboxId,
		public string $dirPath,
		public string $uid,
		public int $id,
		public ?int $attachmentsCount = null,
		public ?string $body = null,
	)
	{}
}

class AttachmentHelper
{
	private const MESSAGE_PARTS_TEXT = 1;
	private const MESSAGE_PARTS_ATTACHMENT = 2;
	private const MESSAGE_PARTS_ALL = -1;

	private const COMPARISON_ATTACHMENT_LEVEL_STRONG = 2;
	private const COMPARISON_ATTACHMENT_LEVEL_AVERAGE = 1;
	private const COMPARISON_ATTACHMENT_LEVEL_LOW = 0;

	private const MAILBOX_ENCODING = 'UTF-8';

	private ?Imap $client;
	private ?MessageStructure $message = null;

	private function getImapClient(int $mailboxId): ?Imap
	{
		$mailbox = MailboxTable::getRow([
			'select' => [
				'SERVER',
				'PORT',
				'USE_TLS',
				'LOGIN',
				'PASSWORD',
			],
			'filter' => [
				'=ID' => $mailboxId,
				'=ACTIVE' => 'Y',
			],
		]);

		if (is_null($mailbox))
		{
			return null;
		}

		$mailboxTls = $mailbox['USE_TLS'];

		return new Imap(
			$mailbox['SERVER'],
			(int) $mailbox['PORT'],
			($mailboxTls === 'Y' || $mailboxTls === 'S'),
			($mailboxTls === 'Y'),
			$mailbox['LOGIN'],
			$mailbox['PASSWORD'],
			'UTF-8'
		);
	}

	public static function generateMessageAttachmentPath(): string
	{
		return 'mail/message_attachment/'.date('Y-m-d');
	}

	public function __construct(int $mailboxId, int $messageId = null, int $messageUid = null)
	{
		$message = MailMessageUidTable::getMessage(
			$mailboxId,
			[
				'MSG_UID',
				'MESSAGE_ID',
				'DIR_MD5',
			],
			$messageId,
			$messageUid,
		);

		if (!is_null($message))
		{
			$dir = MailboxDirectoryTable::getRow([
				'select' => [
					'ID',
					'PATH',
				],
				'filter' => [
					'=DIR_MD5' => $message['DIR_MD5'],
					'=MAILBOX_ID' => $mailboxId,
				],
			]);

			if (!is_null($dir))
			{
				$this->message = new MessageStructure($mailboxId, $dir['PATH'], $message['MSG_UID'], (int) $message['MESSAGE_ID']);
			}
		}

		$this->client = $this->getImapClient($mailboxId);
	}

	private function downloadBodyStructure(MessageStructure $messageStructure): ?Imap\BodyStructure
	{
		$error = [];
		$structure = $this->client->fetch(true, $messageStructure->dirPath, $messageStructure->uid, '(BODYSTRUCTURE)', $error);

		if ($error || empty($structure))
		{
			return null;
		}

		return new Imap\BodyStructure($structure['BODYSTRUCTURE']);
	}

	public static function generateFileName(int $mailboxId, int $messageId, int $attachmentIndex, ?string $attachmentType): string
	{
		$fileName = $mailboxId . '-' . $messageId . '-' . $attachmentIndex;

		// @TODO: replace with "\Bitrix\Main\Web\MimeType::getExtensionByMimeType" when it comes out
		$extension = array_search($attachmentType, MimeType::getMimeTypeList(), true);

		if ($extension)
		{
			$fileName .= '.' . $extension;
		}

		return $fileName;
	}

	/**
	 * @param MessageStructure $messageStructure
	 * @return AttachmentStructure[]
	 */
	private function downloadAttachments(MessageStructure $messageStructure): array
	{
		$attachments = [];
		$bodyStructure = $this->downloadBodyStructure($messageStructure);

		if (is_null($bodyStructure))
		{
			return $attachments;
		}

		$parts = $this->downloadMessageParts($messageStructure->dirPath, $messageStructure->uid, $bodyStructure, self::MESSAGE_PARTS_ATTACHMENT);

		$bodyStructure->traverse(
			function (Imap\BodyStructure $item) use (&$parts, &$attachments, $messageStructure)
			{
				static $attachmentIndex = 0;

				if ($item->isMultipart() || $item->isBodyText())
				{
					return;
				}

				$attachmentIndex++;

				$attachment = \CMailMessage::decodeMessageBody(
					\CMailMessage::parseHeader(
						$parts[sprintf('BODY[%s.MIME]', $item->getNumber())],
						self::MAILBOX_ENCODING,
					),
					$parts[sprintf('BODY[%s]', $item->getNumber())],
					self::MAILBOX_ENCODING,
				);

				$fileName = $attachment['FILENAME'];

				if (is_null($fileName))
				{
					$fileName = self::generateFileName($messageStructure->mailboxId, $messageStructure->id, $attachmentIndex, $attachment['CONTENT-TYPE']);
				}

				$attachments[] = new AttachmentStructure(
					$fileName,
					strlen($attachment['BODY']),
					mb_strtolower($attachment['CONTENT-TYPE']),
					$attachment['BODY'],
					contentId: $attachment['CONTENT-ID'],
				);
			}
		);

		return $attachments;
	}

	private function saveAttachmentToDisk(AttachmentStructure $attachment): ?AttachmentStructure
	{
		if (empty($attachment->name))
		{
			return null;
		}

		$fileId = \CFile::saveFile(
			[
				'name' => md5($attachment->name),
				'size' => $attachment->size,
				'type' => $attachment->type,
				'content' => $attachment->content,
				'MODULE_ID' => 'mail'
			],
			self::generateMessageAttachmentPath(),
		);

		if (!is_int($fileId))
		{
			return null;
		}

		$extendedAttachment = clone $attachment;
		$extendedAttachment->diskId = $fileId;

		if (is_null($extendedAttachment->imageWidth) && is_null($extendedAttachment->imageHeight))
		{
			$file = \CFile::GetFileArray($fileId);

			if (is_set($file['WIDTH']))
			{
				$extendedAttachment->imageWidth = (int) $file['WIDTH'];
			}

			if (is_set($file['HEIGHT']))
			{
				$extendedAttachment->imageHeight = (int) $file['HEIGHT'];
			}

		}

		return $extendedAttachment;
	}

	/**
	 * @param AttachmentStructure[] $attachments
	 * @param bool $abortOnAnError
	 * @return Result
	 */
	public function saveAttachmentsToDisk(array $attachments, bool $abortOnAnError = false): Result
	{
		$allResult = new Result();
		$data = [];

		/** @var AttachmentStructure $attachment */
		foreach ($attachments as $attachment)
		{
			$extendedAttachment = $this->saveAttachmentToDisk($attachment);
			if (!is_null($extendedAttachment))
			{
				$data[] = $extendedAttachment;
			}
			else if ($abortOnAnError)
			{
				$allResult->addError(new Error('File upload error', 'FILE_UPLOAD_ERROR'));
				$allResult->setData($data);
				return $allResult;
			}
		}

		$allResult->setData($data);

		return $allResult;
	}

	/**
	 * @param MessageStructure $messageStructure
	 * @return AttachmentStructure[]
	 */
	private function getSynchronized(MessageStructure $messageStructure) : array
	{
		$attachments = [];

		if (empty($messageStructure->attachmentsCount))
		{
			return $attachments;
		}

		$list = MailMessageAttachmentTable::getList([
			'select' => [
				'ID',
				'FILE_ID',
				'FILE_NAME',
				'FILE_SIZE',
				'CONTENT_TYPE',
				'IMAGE_WIDTH',
				'IMAGE_HEIGHT',
			],
			'filter' => [
				'=MESSAGE_ID' => $messageStructure->id,
			],
		]);

		while ($item = $list->fetch())
		{
			$attachments[] = new AttachmentStructure(
				$item['FILE_NAME'],
				$item['FILE_SIZE'],
				$item['CONTENT_TYPE'],
				null,
				$item['FILE_ID'],
				$item['IMAGE_WIDTH'],
				$item['IMAGE_HEIGHT'],
				attachmentId: $item['ID'],
			);
		}

		return $attachments;
	}

	/**
	 * @param MessageStructure $messageStructure
	 * @param AttachmentStructure[] $attachments
	 * @return void
	 */
	private function deleteAttachedFromDB(MessageStructure $messageStructure, array $attachments) : void
	{
		$ids = [];

		/** @var AttachmentStructure $attachment */
		foreach ($attachments as $attachment)
		{
			$ids[] = $attachment->attachmentId;
		}

		if ($messageStructure->attachmentsCount > 0)
		{
			$messageId = $messageStructure->id;

			if ($messageId > 0 && count($ids) > 0)
			{
				MailMessageAttachmentTable::deleteByIds($messageId, $ids);
			}
		}
	}

	/**
	 * @param MessageStructure $messageStructure
	 * @param AttachmentStructure[] $attachments
	 * @return AttachmentStructure[]
	 */
	private function saveAttachmentsToDB(MessageStructure $messageStructure, array $attachments): array
	{
		$newAttachments = [];

		/** @var AttachmentStructure $attachment */
		foreach ($attachments as $attachment)
		{
			if (!is_null($attachment->diskId))
			{
				$result = MailMessageAttachmentTable::add(
					[
						'MESSAGE_ID' => $messageStructure->id,
						'FILE_ID' => $attachment->diskId,
						'FILE_NAME' => $attachment->name,
						'FILE_SIZE' => $attachment->size,
						'FILE_DATA' => null,
						'CONTENT_TYPE' => $attachment->type,
						'IMAGE_WIDTH' => $attachment->imageWidth,
						'IMAGE_HEIGHT' => $attachment->imageHeight,
					]
				);

				$primary = $result->getPrimary();

				if (isset($primary['ID']))
				{
					$newAttachment = clone $attachment;
					$newAttachment->attachmentId = $primary['ID'];
					$newAttachments[] = $newAttachment;
				}
			}
		}

		return $newAttachments;
	}

	private function createMessageWithBody(MessageStructure $messageStructure): MessageStructure
	{
		$extendedStructure = clone $messageStructure;

		$modelRow = MailMessageTable::getRow([
			'select' => [
				'BODY_HTML'
			],
			'filter' => [
				'ID' => $messageStructure->id,
			],
		]);

		if (
			isset($modelRow['BODY_HTML']) &&
			is_string($modelRow['BODY_HTML'])
		)
		{
			$extendedStructure->body = $modelRow['BODY_HTML'];
		}

		return $extendedStructure;
	}

	public function extractFileIdsFromMessageBody(MessageStructure $messageStructure): array
	{
		$attachmentIds = [];

		$body = $messageStructure->body;

		if (is_null($body))
		{
			return $attachmentIds;
		}

		$pattern = '/<img[^>]+src\s*=\s*(\'|\")?aid:(?P<id>\d+)\s*\1[^>]*>/is';

		if (preg_match_all($pattern, $body, $matches) !== false)
		{
			$attachmentIds = array_map('intval', $matches['id']);
		}

		return $attachmentIds;
	}

	/**
	 * @param MessageStructure $messageStructure
	 * @param int[] $attachmentIds
	 * @return AttachmentStructure[]
	 */
	private function getAttachmentStructuresByIds(MessageStructure $messageStructure, array $attachmentIds): array
	{
		$attachments = [];

		if (count($attachmentIds) === 0)
		{
			return $attachments;
		}

		$attachmentList = MailMessageAttachmentTable::getList([
			'select' => [
				'ID',
				'FILE_NAME',
				'FILE_SIZE',
				'FILE_ID',
				'CONTENT_TYPE',
				'IMAGE_WIDTH',
				'IMAGE_HEIGHT',
			],
			'filter' => [
				'@ID' => $attachmentIds,
				'=MESSAGE_ID' => $messageStructure->id,
			],
		]);

		while ($attachment = $attachmentList->fetch())
		{
			$attachments[] = new AttachmentStructure(
				$attachment['FILE_NAME'],
				$attachment['FILE_SIZE'],
				$attachment['CONTENT_TYPE'],
				diskId: (int) $attachment['FILE_ID'],
				imageWidth: (int) $attachment['IMAGE_WIDTH'],
				imageHeight: (int) $attachment['IMAGE_HEIGHT'],
				attachmentId: (int) $attachment['ID']
			);
		}

		return $attachments;
	}

	/**
	 * @param MessageStructure $messageStructure
	 * @return AttachmentStructure[]
	 */
	private function getAttachmentsEmbeddedInMessageBody(MessageStructure $messageStructure) : array
	{
		$attachments = [];

		$fileIds = $this->extractFileIdsFromMessageBody($messageStructure);

		if (!empty($fileIds))
		{
			$attachments = $this->getAttachmentStructuresByIds($messageStructure, $fileIds);
		}

		return $attachments;
	}

	private static function compareAttachment(AttachmentStructure $brokenAttachment, AttachmentStructure $fullAttachment, int $comparisonLevel = self::COMPARISON_ATTACHMENT_LEVEL_STRONG, bool $attachmentIsPicture = true): bool
	{
		if ($attachmentIsPicture && !\CFile::isImage($fullAttachment->name, $fullAttachment->type))
		{
			return false;
		}

		if (
			$comparisonLevel === self::COMPARISON_ATTACHMENT_LEVEL_STRONG &&
			$brokenAttachment->name === $fullAttachment->name &&
			$brokenAttachment->type === $fullAttachment->type
		)
		{
			return true;
		}

		if (
			$comparisonLevel === self::COMPARISON_ATTACHMENT_LEVEL_AVERAGE &&
			$brokenAttachment->name === $fullAttachment->name &&
			$brokenAttachment->size === $fullAttachment->size
		)
		{
			return true;
		}

		if (
			$comparisonLevel === self::COMPARISON_ATTACHMENT_LEVEL_LOW &&
			$brokenAttachment->name === $fullAttachment->name
		)
		{
			return true;
		}

		return false;
	}

	/**
	 * @param MessageStructure $messageStructure
	 * @param AttachmentStructure[] $oldAttachments
	 * @param AttachmentStructure[] $newAttachments
	 * @return MessageStructure
	 */
	private function createMessageWithReplacedAttachmentsInBody(MessageStructure $messageStructure, array $oldAttachments, array $newAttachments) : MessageStructure
	{
		$extendedStructure = clone $messageStructure;

		foreach ([
			self::COMPARISON_ATTACHMENT_LEVEL_STRONG,
			self::COMPARISON_ATTACHMENT_LEVEL_AVERAGE,
			self::COMPARISON_ATTACHMENT_LEVEL_LOW
		] as $level)
		{
			$remainingOldAttachments = [];
			$remainingNewAttachments = $newAttachments;

			/** @var AttachmentStructure $oldAttachment */
			foreach ($oldAttachments as $oldAttachment)
			{
				$found = false;

				/** @var AttachmentStructure $newAttachment */
				foreach ($remainingNewAttachments as $newKey => $newAttachment)
				{
					if ($this->compareAttachment($oldAttachment, $newAttachment, $level))
					{
						$oldId = $oldAttachment->attachmentId;
						$newId = $newAttachment->attachmentId;

						if ($oldId !== null && $newId !== null && !is_null($extendedStructure->body))
						{
							$pattern = '/(src\s*=\s*["\']?aid:)' . $oldId . '(["\']?)/i';
							$replacement = 'src="aid:'.$newId.'"';
							$newBody = preg_replace($pattern, $replacement, $extendedStructure->body);

							if (!is_null($newBody))
							{
								$extendedStructure->body = $newBody;
							}

							unset($remainingNewAttachments[$newKey]);
							$found = true;
							break;
						}
					}
				}

				if (!$found)
				{
					$remainingOldAttachments[] = $oldAttachment;
				}
			}

			$oldAttachments = $remainingOldAttachments;
			$newAttachments = $remainingNewAttachments;
		}

		return $extendedStructure;
	}

	public function update(): bool
	{
		if (is_null($this->message) || is_null($this->client))
		{
			return false;
		}

		$attachmentStructures = $this->downloadAttachments($this->message);
		$savingToDiskResult = $this->saveAttachmentsToDisk($attachmentStructures, true);
		$attachmentStructures = $savingToDiskResult->getData();

		if ($savingToDiskResult->isSuccess() === false)
		{
			/** @var AttachmentStructure $attachment */
			foreach ($attachmentStructures as $attachment)
			{
				if (is_int($attachment->diskId))
				{
					\CFile::Delete($attachment->diskId);
				}
			}

			return false;
		}

		$this->message = $this->createMessageWithAttachmentCount($this->message);

		$oldAttachments = $this->getSynchronized($this->message);

		/** @var AttachmentStructure $attachment */
		foreach ($oldAttachments as $attachment)
		{
			$diskId = $attachment->diskId;

			if (is_int($diskId))
			{
				\CFile::Delete($diskId);
			}
		}

		$newAttachments = $savingToDiskResult->getData();
		$newAttachments = $this->saveAttachmentsToDB($this->message, $newAttachments);

		$this->message = $this->createMessageWithBody($this->message);

		$attachmentsEmbeddedInMessageBody = $this->getAttachmentsEmbeddedInMessageBody($this->message);

		$messageWithUpdatedBody = $this->createMessageWithReplacedAttachmentsInBody($this->message, $attachmentsEmbeddedInMessageBody, $newAttachments);

		if ($this->message->body !== $messageWithUpdatedBody->body)
		{
			$this->message->body = $messageWithUpdatedBody->body;

			MailMessageTable::update(
				$this->message->id,
				[
					'BODY_HTML' => $this->message->body,
				]
			);
		}

		$this->deleteAttachedFromDB($this->message, $oldAttachments);

		$messageWithNewAttachmentCount = $this->createMessageWithAttachmentCount($this->message, count($newAttachments));

		if ($messageWithNewAttachmentCount->attachmentsCount !== $this->message->attachmentsCount)
		{
			$this->message->attachmentsCount = $messageWithNewAttachmentCount->attachmentsCount;

			MailMessageTable::update(
				$this->message->id,
				[
					'ATTACHMENTS' => $this->message->attachmentsCount,
				]
			);
		}

		return true;
	}

	/**
	 * If the attachment counter is not transmitted, it will be loaded from the database, if available.
	 *
	 * @param MessageStructure $messageStructure
	 * @param int|null $attachmentCount
	 * @return MessageStructure
	 */
	private function createMessageWithAttachmentCount(MessageStructure $messageStructure, ?int $attachmentCount = null): MessageStructure
	{
		$extendedStructure = clone $messageStructure;

		if (is_null($attachmentCount))
		{
			$modelRow = MailMessageTable::getRow([
				'select' => [
					'ATTACHMENTS'
				],
				'filter' => [
					'ID' => $messageStructure->id,
				],
			]);

			if (isset($modelRow['ATTACHMENTS']))
			{
				$extendedStructure->attachmentsCount = (int) $modelRow['ATTACHMENTS'];
			}
			else
			{
				$extendedStructure->attachmentsCount = 0;
			}
		}
		else
		{
			$extendedStructure->attachmentsCount = $attachmentCount;
		}

		return $extendedStructure;
	}

	private function downloadMessageParts(string $dirPath, string $uid, Imap\BodyStructure $bodyStructure, int $type = self::MESSAGE_PARTS_ALL): array
	{
		$messagePartsMetadata = [];

		$fetchCommands = array_filter(
			$bodyStructure->traverse(
				function (Imap\BodyStructure $item) use ($type, &$messagePartsMetadata)
				{
					if ($item->isMultipart())
					{
						return null;
					}

					$isTextItem = $item->isBodyText();

					if ($type === ($isTextItem ? self::MESSAGE_PARTS_TEXT : self::MESSAGE_PARTS_ATTACHMENT))
					{
						//Due to yandex bug
						if ($item->getType() === 'message' && $item->getSubtype() === 'rfc822')
						{
							$messagePartsMetadata[] = $item;

							return sprintf('BODY.PEEK[%1$s.HEADER] BODY.PEEK[%1$s.TEXT]', $item->getNumber());
						}

						return sprintf('BODY.PEEK[%1$s.MIME] BODY.PEEK[%1$s]', $item->getNumber());
					}
					return null;
				},
				true
			)
		);

		if (empty($fetchCommands))
		{
			return [];
		}

		$error = [];

		$fetchedParts = $this->client->fetch(
			true,
			$dirPath,
			$uid,
			sprintf('(%s)', join(' ', $fetchCommands)),
			$error
		);

		if ($fetchedParts === false)
		{
			return [];
		}

		return $this->combineMessageParts($fetchedParts, $messagePartsMetadata);
	}

	/**
	 * Combines the header and body parts of the message into a single structure.
	 *
	 * This function takes the fetched parts of the message and the message parts metadata,
	 * and combines the header and body parts into a complete message structure.
	 *
	 * @param array $fetchedParts The fetched parts of the message.
	 * @param array $messagePartsMetadata Metadata about the message parts.
	 * @return array The combined message parts.
	 */
	private function combineMessageParts(array $fetchedParts, array $messagePartsMetadata): array
	{
		/** @var Imap\BodyStructure $item */
		foreach ($messagePartsMetadata as $item)
		{
			$headerKey = sprintf('BODY[%s.HEADER]', $item->getNumber());
			$bodyKey = sprintf('BODY[%s.TEXT]', $item->getNumber());

			if (array_key_exists($headerKey, $fetchedParts) || array_key_exists($bodyKey, $fetchedParts))
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

				$fetchedParts[sprintf('BODY[%1$s.MIME]', $item->getNumber())] = $partMime;

				$fetchedParts[sprintf('BODY[%1$s]', $item->getNumber())] = sprintf(
					"%s\r\n\r\n%s",
					rtrim($fetchedParts[$headerKey], "\r\n"),
					ltrim($fetchedParts[$bodyKey], "\r\n")
				);

				unset($fetchedParts[$headerKey], $fetchedParts[$bodyKey]);
			}
		}

		return $fetchedParts;
	}

}