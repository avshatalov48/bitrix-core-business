<?php

namespace Bitrix\Im\V2\Message\Delete\Strategy;

use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Chat\NullChat;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Delete\DeletionMode;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Sync\Event;
use Bitrix\Main\Localization\Loc;

class SoftDeletionStrategy extends DeletionStrategy
{
	protected function execute(): void
	{
		$result = $this->messages->save();
		$this->checkResult($result);
	}

	/**
	 * @throws InterruptedExecutionException
	 */
	protected function onBeforeDelete(): void
	{
		if ($this->chat instanceof NullChat)
		{
			throw new InterruptedExecutionException(
				(new Result())->addError(new ChatError(ChatError::NOT_FOUND))
			);
		}

		$this->messages->fillFiles();

		foreach ($this->messages as $message)
		{
			$message->setMessage(Loc::getMessage('IM_MESSAGE_DELETED'));
			$message->setMessageOut($this->getMessageOut($message));
		}

		$this->messages->resetParams([
			'IS_DELETED' => 'Y',
		]);
	}

	protected function onAfterDelete(): void
	{
		$this->logToSync(Event::DELETE_EVENT);
		$this->deleteFiles();
		$this->chat->onAfterMessagesDelete($this->messages, $this->getDeletionMode());
	}

	private function getMessageOut(Message $message): string
	{
		$date = $message->getDateCreate()?->toString();

		return Loc::getMessage('IM_MESSAGE_DELETED_OUT', ['#DATE#' => $date]) ?? '';
	}

	protected function getDeletionMode(): DeletionMode
	{
		return DeletionMode::Soft;
	}
}