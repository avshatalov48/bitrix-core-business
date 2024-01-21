<?php

namespace Bitrix\Im\V2\Message\Forward;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\File\FileError;
use Bitrix\Im\V2\Entity\File\FileItem;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Result;

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
			if ($forwardingMessage->getChat()->hasAccess($this->getContext()->getUserId()))
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
			'PARAMS' => $paramsResult->getResult(),
			'TO_CHAT_ID' =>  $this->toChat->getChatId(),
			'FROM_USER_ID' => $this->getContext()->getUserId(),
			'URL_PREVIEW' => 'N',
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
		}

		if ($forwardingMessage->getParams()->isSet(Message\Params::FILE_ID))
		{
			$newFileIds = [];
			foreach ($forwardingMessage->getFiles() as $file)
			{
				$copy = $file->getCopyToChat($this->toChat);
				if ($copy instanceof FileItem)
				{
					$newFileIds[] = $copy->getId();
				}
			}

			$newParams[Message\Params::FILE_ID] = $newFileIds;
		}

		return $result->setResult($newParams);
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

	/**
	 * @param int $fileId
	 * @return Result<FileItem>
	 */
	private function getFileCopy(int $fileId): Result
	{
		$result = new Result();

		$fileItem = FileItem::initByDiskFileId($fileId, $this->toChat->getChatId());
		if (!$fileItem)
		{
			return $result->addError(new FileError(FileError::NOT_FOUND));
		}

		$copyFile = $fileItem->getCopyToChat($this->toChat);
		if (!$copyFile)
		{
			return $result->addError(new FileError(FileError::CREATE_SYMLINK));
		}

		return $result->setResult($copyFile);
	}
}