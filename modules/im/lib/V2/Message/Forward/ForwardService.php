<?php

namespace Bitrix\Im\V2\Message\Forward;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\File\FileItem;
use Bitrix\Im\V2\Integration\AI\RoleManager;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Result;
use Bitrix\Imbot\Bot\CopilotChatBot;
use Bitrix\Main\Loader;

class ForwardService
{
	use ContextCustomer;

	private const PARAMS_TO_COPY_WHITELIST = [
		Message\Params::ATTACH => Message\Params::ATTACH,
		Message\Params::URL_ID => Message\Params::URL_ID,
		Message\Params::IS_DELETED => Message\Params::IS_DELETED,
		Message\Params::URL_ONLY => Message\Params::URL_ONLY,
		Message\Params::LARGE_FONT => Message\Params::LARGE_FONT,
		Message\Params::FORWARD_CONTEXT_ID => Message\Params::FORWARD_CONTEXT_ID,
		Message\Params::FORWARD_ID => Message\Params::FORWARD_ID,
		Message\Params::FORWARD_USER_ID => Message\Params::FORWARD_USER_ID,
		Message\Params::FORWARD_CHAT_TITLE => Message\Params::FORWARD_CHAT_TITLE,
		Message\Params::REPLY_ID => Message\Params::REPLY_ID,
	];

	private Chat $toChat;

	public function __construct(Chat $toChat)
	{
		$this->toChat = $toChat;
	}

	/**
	 * @param MessageCollection $forwardingMessages
	 * @return Result<MessageCollection>
	 */
	public function createMessages(MessageCollection $forwardingMessages): Result
	{
		$result = new Result();

		$uuidMap = [];
		foreach ($forwardingMessages as $forwardingMessage)
		{
			if ($this->canForward($forwardingMessage))
			{
				$forwardMessageResult = $this->createForwardMessage($forwardingMessage);
				if ($forwardMessageResult->hasResult())
				{
					$messageMap = $forwardMessageResult->getResult();
					$uuidMap[$messageMap['uuid']] = $messageMap['id'];
				}

				$result->addErrors($forwardMessageResult->getErrors());
			}
		}

		return $result->setResult($uuidMap);
	}

	public static function getChatTypeByContextId(string $contextId): string
	{
		$dialogId = Chat::getDialogIdByContextId($contextId);

		if (!str_starts_with($dialogId, 'chat'))
		{
			return \Bitrix\Im\Chat::getType(['TYPE' => Chat::IM_TYPE_PRIVATE]);
		}

		$chatId = mb_substr($dialogId, 4);

		return Chat::getInstance($chatId)->getExtendedType();
	}

	/**
	 * @param Message $forwardingMessage
	 * @return Result<Message>
	 */
	private function createForwardMessage(Message $forwardingMessage): Result
	{
		$paramsResult = $this->createParamsForForwardMessage($forwardingMessage);
		if (!$paramsResult->hasResult())
		{
			return $paramsResult;
		}

		$messageConfig = [
			'MESSAGE_TYPE' => $this->toChat->getType(),
			'MESSAGE' => $forwardingMessage->getMessage() !== '' ? $forwardingMessage->getMessage() : null,
			'PARAMS' => $paramsResult->getResult()['PARAMS'] ?? [],
			'TO_CHAT_ID' =>  $this->toChat->getChatId(),
			'FROM_USER_ID' => $this->getContext()->getUserId(),
			'URL_PREVIEW' => 'N',
			'TEMPLATE_ID' => $forwardingMessage->getForwardUuid() ?? '',
			'FILE_MODELS' => $paramsResult->getResult()['FILE_MODELS'] ?? [],
			'WAIT_FULL_EXECUTION' => 'N',
		];

		$result = new Result();

		$messageId = \CIMMessenger::Add($messageConfig); //TODO replace with $chat->sendMessage
		if (!$messageId)
		{
			$result->addError(new Message\MessageError(Message\MessageError::SENDING_FAILED));
		}

		return $result->setResult([
			'uuid' => $forwardingMessage->getForwardUuid(),
			'id' => $messageId
		]);
	}

	/**
	 * @param Message $forwardingMessage
	 * @return Result<array>
	 */
	private function createParamsForForwardMessage(Message $forwardingMessage): Result
	{
		$result = new Result();

		$newParams = $this->getForwardingMessageParams($forwardingMessage);

		if ($this->isOriginalMessage($newParams))
		{
			$userId = $forwardingMessage->isSystem() ? 0 : $forwardingMessage->getAuthorId();
			$newParams[Message\Params::FORWARD_ID] = $forwardingMessage->getId();
			$newParams[Message\Params::FORWARD_CONTEXT_ID] = $forwardingMessage->getContextId();
			$newParams[Message\Params::FORWARD_USER_ID] = $userId;
			if ($forwardingMessage->getChat() instanceof Chat\OpenChannelChat)
			{
				$newParams[Message\Params::FORWARD_CHAT_TITLE] = $forwardingMessage->getChat()->getDisplayedTitle();
			}
		}

		$diskFiles = [];

		if ($forwardingMessage->getParams()->isSet(Message\Params::FILE_ID))
		{
			$newFileIds = [];
			foreach ($forwardingMessage->getFiles() as $file)
			{
				$copy = $file->getCopyToChat($this->toChat);
				if ($copy instanceof FileItem)
				{
					$newFileIds[] = $copy->getId();
					$diskFiles[] = $copy->getDiskFile();
				}
			}

			$newParams[Message\Params::FILE_ID] = $newFileIds;
		}

		if (Loader::includeModule('imbot') && $forwardingMessage->getAuthorId() === CopilotChatBot::getBotId())
		{
			if ($forwardingMessage->getParams()->isSet(Message\Params::COPILOT_ROLE))
			{
				$copilotRole = $forwardingMessage->getParams()->get(Message\Params::COPILOT_ROLE)->getValue();
			}

			$newParams[Message\Params::COPILOT_ROLE] = $copilotRole ?? RoleManager::getDefaultRoleCode();
		}

		return $result->setResult(['PARAMS' => $newParams, 'FILE_MODELS' => $diskFiles]);
	}

	/**
	 * If this message is already forwarded, then the params are already available
	 * @param array $messageParams
	 * @return bool
	 */
	private function isOriginalMessage(array $messageParams): bool
	{
		return !isset(
			$messageParams[Message\Params::FORWARD_CONTEXT_ID],
			$messageParams[Message\Params::FORWARD_USER_ID]
		);
	}

	/**
	 * @param Message $message
	 * @return array
	 */
	private function getForwardingMessageParams(Message $message): array
	{
		$result = [];
		foreach ($message->getParams() as $param)
		{
			if (isset(self::PARAMS_TO_COPY_WHITELIST[$param->getName()]))
			{
				$value = $param->getValue();
				if (is_bool($value))
				{
					$value = $value ? 'Y' : 'N';
				}
				$result[$param->getName()] = $value;
			}
		}

		return $result;
	}

	private function canForward(Message $message): bool
	{
		$chat = $message->getChat();

		return $chat->checkAccess($this->getContext()->getUserId())->isSuccess() && !($chat instanceof Chat\CommentChat);
	}
}