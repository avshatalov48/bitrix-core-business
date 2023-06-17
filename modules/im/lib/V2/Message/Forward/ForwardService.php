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

	private Chat $toChat;

	public function __construct(Chat $toChat)
	{
		$this->toChat = $toChat;
	}

	/**
	 * @param MessageCollection $forwardingMessages
	 * @param string|null $comment
	 * @return Result<MessageCollection>
	 */
	public function createMessages(MessageCollection $forwardingMessages, ?string $comment = null): Result
	{
		$result = new Result();
		if (!$this->toChat->hasAccess($this->getContext()->getUserId()))
		{
			return $result->addError(new Chat\ChatError(Chat\ChatError::ACCESS_DENIED));
		}

		$messages = new MessageCollection();
		foreach ($forwardingMessages as $forwardingMessage)
		{
			if ($forwardingMessage->getChat()->hasAccess($this->getContext()->getUserId()))
			{
				$forwardMessageResult = $this->createForwardMessage($forwardingMessage);
				if ($forwardMessageResult->hasResult())
				{
					$messages->add($forwardMessageResult->getResult());
				}
				$result->addErrors($forwardMessageResult->getErrors());
			}
		}

		if ($comment !== null)
		{
			$commentMessageResult = $this->createCommentMessage($comment);
			if ($commentMessageResult->hasResult())
			{
				$messages->add($commentMessageResult->getResult());
			}
			$result->addErrors($commentMessageResult->getErrors());
		}

		return $result->setResult($messages);
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
			'MESSAGE' => $forwardingMessage->getMessage(),
			'PARAMS' => $paramsResult->getResult(),
			'TO_CHAT_ID' =>  $this->toChat->getChatId(),
			'FROM_USER_ID' => $this->getContext()->getUserId(),
		];

		$result = new Result();

		$messageId = \CIMMessenger::Add($messageConfig); //TODO replace with $chat->sendMessage
		if (!$messageId)
		{
			$result->addError(new Message\MessageError(Message\MessageError::SENDING_FAILED));
		}

		return $result->setResult(new Message($messageId));
	}

	/**
	 * @param string $comment
	 * @return Result<Message>
	 */
	private function createCommentMessage(string $comment): Result
	{
		$result = new Result();


		$messageConfig = [
			"MESSAGE_TYPE" => $this->toChat->getType(),
			"MESSAGE" => $comment,
			"TO_CHAT_ID" =>  $this->toChat->getChatId(),
			"FROM_USER_ID" => $this->getContext()->getUserId(),
		];

		$messageId = \CIMMessenger::Add($messageConfig); //TODO replace with $chat->sendMessage

		if (!$messageId)
		{
			return $result->addError(new Message\MessageError(Message\MessageError::SENDING_FAILED));
		}

		return $result->setResult(new Message($messageId));
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
			$newParams[Message\Params::FORWARD_ID] = $forwardingMessage->getMessageId();
			$newParams[Message\Params::FORWARD_CHAT_ID] = $forwardingMessage->getChatId();
			$newParams[Message\Params::FORWARD_USER_ID] = $forwardingMessage->getAuthorId();

			if ($forwardingMessage->getChat()->getType() === Chat::IM_TYPE_OPEN)
			{
				$newParams[Message\Params::FORWARD_TITLE] = $forwardingMessage->getChat()->getTitle();
			}
		}

		if (isset($newParams[Message\Params::REPLY_ID]))
		{
			unset($newParams[Message\Params::REPLY_ID]);
		}

		if (isset($newParams[Message\Params::FILE_ID]))
		{
			$newLinkResult = $this->getFileLink($newParams[Message\Params::FILE_ID]);
			if (!$newLinkResult->hasResult())
			{
				return $result->addErrors($newLinkResult->getErrors());
			}

			$newParams[Message\Params::FILE_ID] = $newLinkResult->getResult()->getId();
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
			$messageParams[Message\Params::FORWARD_ID],
			$messageParams[Message\Params::FORWARD_CHAT_ID],
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
			$result[$param->getName()] = $param->getValue();
		}

		return $result;
	}

	/**
	 * @param int $fileId
	 * @return Result<FileItem>
	 */
	private function getFileLink(int $fileId): Result
	{
		$result = new Result();

		$fileItem = FileItem::initByDiskFileId($fileId, $this->toChat->getChatId());
		if (!$fileItem)
		{
			return $result->addError(new FileError(FileError::NOT_FOUND));
		}

		$newSymLink = $fileItem->getSymLink();
		if (!$newSymLink)
		{
			return $result->addError(new FileError(FileError::CREATE_SYMLINK));
		}

		return $result->setResult($newSymLink);
	}
}