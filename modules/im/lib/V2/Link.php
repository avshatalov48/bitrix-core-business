<?php

namespace Bitrix\Im\V2;

interface Link
{
	/**
	 * Associates the entity with the passed message by filling in the messageId, chatId and authorId
	 * @param Message $message
	 * @return static
	 */
	public function setMessageInfo(Message $message): self;
}