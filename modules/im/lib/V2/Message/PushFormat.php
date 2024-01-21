<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\Text;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\PrivateChat;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Im\V2\Common\ContextCustomer;

class PushFormat
{
	use ContextCustomer;

	private const MAX_CHAT_MESSAGE_TIME = 600;
	private const DND_USER_STATUS = 'dnd';

	public function formatPrivateMessage(Message $message, PrivateChat $chat): array
	{
		$fromUserId = $message->getAuthorId();
		$toUserId = $chat->getCompanion()->getId();

		$users = \CIMContactList::GetUserData([
			'ID' =>  [$toUserId, $fromUserId],
			'PHONES' => 'Y',
		]);

		return [
			'chatId' => $chat->getChatId(),
			'dialogId' => 0,
			'chat' => [],
			'lines' => null,
			'userInChat' => [],
			'userBlockChat' => [],
			'users' => !empty($users['users']) ? $users['users'] : null,
			'message' => [
				'id' => $message->getMessageId(),
				'templateId' => $message->getUuid(),
				'templateFileId' => $message->getFileUuid(),
				'prevId' => $chat->getPrevMessageId(),
				'chatId' => $chat->getChatId(),
				'senderId' => $fromUserId,
				'recipientId' => $toUserId,
				'system' => ($message->isSystem() ? 'Y' : 'N'),
				'date' => $message->getDateCreate() ?? DateTime::createFromTimestamp(time()),// DATE_CREATE
				'text' => Text::parse($message->getMessage()),
				'textLegacy' => Text::parseLegacyFormat($message->getMessage()),
				'params' => $message->getParams()->toPullFormat(),
				'counter' => 0,
				'isImportant' => $message->isImportant(),
				'importantFor' => $message->getImportantFor(),
			],
			'files' => $message->getFilesDiskData(),
			'notify' => true,
		];
	}

	public function formatStartRecordVoice(Chat $chat): array
	{
		$userId = $this->getContext()->getUserId();
		return [
			'module_id' => 'im',
			'command' => 'startRecordVoice',
			'expiry' => 60,
			'params' => [
				'dialogId' => $chat instanceof PrivateChat ? (string)$userId : $chat->getDialogId(),
				'userId' => $userId,
				'userName' => $this->getContext()->getUser()->getName()
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];
	}

	public function formatMessageUpdate(Message $message): array
	{
		return [
			'module_id' => 'im',
			'command' => 'messageUpdate',
			'params' => [
				'id' => $message->getId(),
				'type' => $message->getChat()->getType() === Chat::IM_TYPE_PRIVATE ? 'private' : 'chat',
				'text' => $message->getParsedMessage(),
				'textLegacy' => Text::parseLegacyFormat($message->getMessage()),
				'chatId' => $message->getChatId(),
				'senderId' => $message->getAuthorId(),
				'params' => $message->getEnrichedParams()->toPullFormat(['IS_EDITED', 'URL_ID', 'ATTACH', 'DATE_TEXT', 'DATE_TS']),
			],
			'extra' => \Bitrix\Im\Common::getPullExtra()
		];
	}

	public function validateDataForInform(Message $message, PrivateChat $chat): Result
	{
		$result = new Result();

		$toUser = $chat->getCompanion();
		$toUserStatus = $toUser->getStatus(true);

		if (!($message->getAuthorId() === $this->getContext()->getUserId()))
		{
			$result->addError(new Message\MessageError(Message\MessageError::INFORM_USER_CONTEXT_ERROR));
		}

		if ($message->isViewedByOthers())
		{
			$result->addError(new Message\MessageError(Message\MessageError::INFORM_VIEWED_ERROR));
		}

		$timestampTimeNow = DateTime::createFromTimestamp(time())->getTimestamp();
		if (!($timestampTimeNow - $message->getDateCreate()->getTimestamp() <= self::MAX_CHAT_MESSAGE_TIME))
		{
			$result->addError(new Message\MessageError(Message\MessageError::INFORM_TIMEOUT_ERROR));
		}

		if (!($toUserStatus === self::DND_USER_STATUS))
		{
			$result->addError(new Message\MessageError(Message\MessageError::INFORM_USER_STATUS_ERROR));
		}

		return $result;
	}
}