<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Message;

/**
 * Содержит комментарии
 */
class CommentChat extends GroupChat
{
	protected ?Chat $parentChat;

	protected function getDefaultType(): string
	{
		return self::IM_TYPE_COMMENT;
	}

	public function setParentChatId(int $parentId): self
	{
		if (!$parentId)
		{
			return $this;
		}

		$parentChat = ChatFactory::getInstance()->getChat($parentId);

		if ($parentChat->getType() !== self::IM_TYPE_CHAT)
		{
			return $this;
		}

		$this->parentChat = $parentChat;

		return parent::setParentChatId($parentId);
	}

	public function setParentMessageId(int $messageId): self
	{
		if ($messageId)
		{
			$message = new Message($messageId);
			if ($message->getChatId() && $message->getChatId() == $this->getParentChatId())
			{
				return parent::setParentMessageId($messageId);
			}
		}

		return $this;
	}

	public function getParent(): ?Chat
	{
		return $this->parentChat;
	}

	public function add(array $params, ?Context $context = null): Result
	{
		$result = new Result;

		$paramsResult = $this->prepareParams($params);
		if ($paramsResult->isSuccess())
		{
			$params = $paramsResult->getResult();
		}
		else
		{
			return $result->addErrors($paramsResult->getErrors());
		}

		$chat = new CommentChat($params);
		$chat->setParentChatId($params['PARENT_ID']);
		if (!$chat->getParent())
		{
			return $result->addError(new ChatError(ChatError::WRONG_PARENT_CHAT));
		}
		$chat
			->setExtranet($chat->getParent()->getExtranet())
			->setManageUsers($chat->getParent()->getManageUsers())
			->setManageUI($chat->getParent()->getManageUI())
			->setParentMessageId($params['PARENT_MID'])
		;
		if (!$chat->getParentMessageId())
		{
			return $result->addError(new ChatError(ChatError::WRONG_PARENT_MESSAGE));
		}
		$chat->save();

		if (!$chat->getChatId())
		{
			return $result->addError(new ChatError(ChatError::CREATION_ERROR));
		}

		$result->setResult([
			'CHAT_ID' => $chat->getChatId(),
			'CHAT' => $chat,
		]);

		return $result;
	}

	protected function prepareParams(array $params = []): Result
	{
		$result = new Result();

		if (!isset($params['PARENT_ID']) || !(int)$params['PARENT_ID'])
		{
			return $result->addError(new ChatError(ChatError::WRONG_PARENT_CHAT));
		}

		if (!isset($params['PARENT_MID']) || !(int)$params['PARENT_MID'])
		{
			return $result->addError(new ChatError(ChatError::WRONG_PARENT_MESSAGE));
		}

		return parent::prepareParams($params);
	}

	public function hasAccess($user = null): bool
	{
		$parent = $this->getParent();
		if ($parent)
		{
			return $parent->hasAccess($user);
		}

		return false;
	}
}
