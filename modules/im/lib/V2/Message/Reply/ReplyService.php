<?php

namespace Bitrix\Im\V2\Message\Reply;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Result;

class ReplyService
{
	use ContextCustomer;

	/**
	 * @param Message $replyingMessage
	 * @param string $comment
	 * @return Result<Message>
	 */
	public function createMessage(Message $replyingMessage, string $comment): Result
	{
		$result = new Result();
		if (!$replyingMessage->getChat()->hasAccess($this->getContext()->getUserId()))
		{
			return $result->addError(new Chat\ChatError(Chat\ChatError::ACCESS_DENIED));
		}

		$messageId = \CIMMessenger::Add([
			"MESSAGE_TYPE" => $replyingMessage->getChat()->getType(),
			"MESSAGE" => $comment,
			"PARAMS" => [
				"REPLY_ID" => $replyingMessage->getMessageId(),
			],
			"TO_CHAT_ID" =>  $replyingMessage->getChat()->getChatId(),
			"FROM_USER_ID" => $this->getContext()->getUserId(),
		]);

		if (!$messageId)
		{
			return $result->addError(new Message\MessageError(Message\MessageError::SENDING_FAILED));
		}

		return $result->setResult(new Message($messageId));
	}

}