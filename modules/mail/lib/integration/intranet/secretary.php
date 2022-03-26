<?php
namespace Bitrix\Mail\Integration\Intranet;

use Bitrix\Mail\Helper\Message;
use Bitrix\Mail\Helper\MessageAccess;
use Bitrix\Mail\Internals\MessageAccessTable;
use Bitrix\Mail\MailMessageTable;
use Bitrix\Mail\IMessageStorage;
use Bitrix\Main\Loader;

class Secretary
{
	public static function getDirectMessageUrl(int $messageId): string
	{
		return sprintf('/mail/message/%u', $messageId);
	}

	public static function getMessage(int $id): \Bitrix\Mail\Item\Message
	{
		return self::getMessageStorage()->getMessage($id);
	}

	public static function getMessageUrlForChat(int $messageId, int $chatId): ?string
	{
		return self::getMessageUrl($messageId, Message::ENTITY_TYPE_IM_CHAT, $chatId);
	}

	public static function getMessageUrlForCalendarEvent(int $messageId, int $eventId): ?string
	{
		return self::getMessageUrl($messageId, Message::ENTITY_TYPE_CALENDAR_EVENT, $eventId);
	}

	// public static function getMessageUrlForUser(int $messageId, int $userId): ?string
	// {
	// 	return self::getMessageUrl($messageId, null, null, $userId);
	// }

	public static function getMailboxIdForMessage(int $messageId)
	{
		$mailboxId = MailMessageTable::getList([
			'select' => [
				'MAILBOX_ID'
			],
			'filter' => [
				'=ID' => $messageId,
			],
		])->fetchAll();

		if(isset($mailboxId[0]['MAILBOX_ID']))
		{
			return (int)$mailboxId[0]['MAILBOX_ID'];
		}

		return false;
	}

	private static function getMessageUrl(int $messageId, string $entityType, int $entityId, ?int $userId = null): ?string
	{
		$message = MailMessageTable::getList([
			'select' => [
				'*',
				'MAILBOX_EMAIL' => 'MAILBOX.EMAIL',
				'MAILBOX_NAME' => 'MAILBOX.NAME',
				'MAILBOX_LOGIN' => 'MAILBOX.LOGIN',
			],
			'filter' => [
				'=ID' => $messageId,
			],
		])->fetch();

		if (!$message)
		{
			return null;
		}

		$filter = [
			'=MAILBOX_ID' => $message['MAILBOX_ID'],
			'=MESSAGE_ID' => $message['ID'],
			// '=ENTITY_UF_ID' => $userField['ID'],
		];

		if ($entityType)
		{
			$filter['=ENTITY_TYPE'] = $entityType;
			$filter['=ENTITY_ID'] = $entityId;
		}

		$access = MessageAccessTable::getList([
			'filter' => $filter,
			'limit' => 1,
		])->fetch();

		if (!$access)
		{
			return null;
		}

		\Bitrix\Mail\Helper\Message::prepare($message);

		$signer = new \Bitrix\Main\Security\Sign\Signer(new \Bitrix\Main\Security\Sign\HmacAlgorithm('md5'));

		$message['__href'] = \CHTTP::urlAddParams(
			$message['__href'],
			array(
				'mail_uf_message_token' => sprintf(
					'%s:%s',
					$access['TOKEN'],
					$signer->getSignature($access['SECRET'], Message::getSaltByEntityType($entityType, $entityId, $userId))
				),
			),
			array(
				'encode' => true,
			)
		);

		return $message['__href'];
	}

	public static function provideAccessToMessage(int $mailMessageId, string $entityType, int $entityId, int $userId): bool
	{
		$message = MailMessageTable::getList([
			'select' => [
				'ID', 'MAILBOX_ID',
			],
			'filter' => [
				'=ID' => $mailMessageId,
			],
		])->fetch();

		if (MessageAccess::isMailboxOwner($message['MAILBOX_ID'], $userId))
		{
			/** @see \Bitrix\Mail\MessageUserType */
			MessageAccessTable::add([
				'TOKEN' => MessageAccess::createToken($message['MAILBOX_ID'], $mailMessageId, $entityType, $entityId, '0'),
				'MAILBOX_ID' => $message['MAILBOX_ID'],
				'MESSAGE_ID' => $mailMessageId,
				'ENTITY_UF_ID' => '0',
				'ENTITY_TYPE' => $entityType,
				'ENTITY_ID' => $entityId,
				'SECRET' => MessageAccess::createSecret(),
				'OPTIONS' => [],
			]);

			return true;
		}

		return false;
	}

	public static function onTaskDelete($taskId)
	{
		$messageAccessQuery = MessageAccessTable::query()
			->setSelect([
				'TOKEN',
				'MESSAGE_ID',
				'MAILBOX_ID',
				])
			->setFilter([
				'=ENTITY_TYPE' => MessageAccessTable::ENTITY_TYPE_TASKS_TASK,
				'=ENTITY_ID' => $taskId,
			]);;

		while ($messageAccess = $messageAccessQuery->fetch())
		{
			$messageId = $messageAccess['MESSAGE_ID'];
			$mailboxId = $messageAccess['MAILBOX_ID'];
			MessageAccessTable::delete(['TOKEN' => $messageAccess['TOKEN']]);

			if (Loader::includeModule('pull'))
			{
				if($mailboxId)
				{
					\CPullWatch::addToStack(
						'mail_mailbox_' . $mailboxId,
						[
							'module_id' => 'mail',
							'command' => 'messageBindingDeleted',
							'params' => [
								'messageId' => $messageId,
								'mailboxId' => $mailboxId,
								'entityType' => MessageAccessTable::ENTITY_TYPE_TASKS_TASK,
								'entityId' => $taskId,
							],
						]
					);
				}
			}
		}
	}

	private static function getMessageStorage(): IMessageStorage
	{
		return new \Bitrix\Mail\Storage\Message();
	}

}