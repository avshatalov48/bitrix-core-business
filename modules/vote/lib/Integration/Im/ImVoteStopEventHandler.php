<?php

namespace Bitrix\Vote\Integration\Im;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message;
use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\UserTable;
use Bitrix\Vote\VoteTable;
class ImVoteStopEventHandler
{
	public function __construct(
		private readonly int $voteId,
		private readonly int $messageId,
	) {}

	public function handle(): Result
	{
		if ($this->voteId <= 0 || $this->messageId <= 0)
		{
			return (new Result())->addError(new Error('Incorrect input params'));
		}

		if (!$this->obtainLock())
		{
			return (new Result())->addError(new Error('Another process already handling stop event'));
		}

		if ($this->isVoteStopEventsAlreadyHandled())
		{
			return (new Result())->addError(new Error('Event already handled'));
		}

		$this->setStopEventHandled(true);
		$this->releaseLock();

		$result = $this->sendMessage();
		if (!$result->isSuccess())
		{
			$this->setStopEventHandled(false);
		}

		return $result;
	}

	private function releaseLock(): void
	{
		Application::getConnection()->unlock($this->getLockName());
	}

	private function obtainLock(): bool
	{
		return Application::getConnection()->lock($this->getLockName());
	}

	private function getLockName(): string
	{
		return "vote_stop_{$this->voteId}";
	}

	private function isVoteStopEventsAlreadyHandled(): bool
	{
		$row = VoteTable::query()
			->where('ID', $this->voteId)
			->where('STOP_EVENT_HANDLED', 1)
			->setSelect(['ID'])
			->setLimit(1)
			->exec()
			->fetch()
		;

		return !empty($row);
	}

	private function setStopEventHandled(bool $handled): Result
	{
		return VoteTable::update($this->voteId, ['STOP_EVENT_HANDLED' => (int)$handled]);
	}

	private function sendMessage(): Result
	{
		if (!Loader::includeModule('im'))
		{
			return (new Result())->addError(new Error('No module im'));
		}

		$message = new Message($this->messageId);
		$chat = $message->getChat();
		if ($chat->getChatId() <= 0)
		{
			return (new Result())->addError(new Error('Chat not found'));
		}

		$result = \CIMMessenger::Add([
			'MESSAGE_TYPE' => IM_MESSAGE_CHAT,
			'TO_CHAT_ID' => $chat->getChatId(),
			'FROM_USER_ID' => $message->getAuthorId(),
			'MESSAGE' => $this->getMessageText($message->getAuthorId(), $chat),
			'SYSTEM' => 'Y',
		]);

		if (!$result)
		{
			return (new Result())->addError(new Error('Message not sended'));
		}

		return new Result();
	}

	private function getMessageText(int $userId, Chat $chat): ?string
	{
		$replaces = [
			'#MESSAGE_ID#' => $this->messageId,
			'#USER_ID#' => $userId,
			'#DIALOG_ID#' => $chat->getDialogContextId(),
		];
		$user = UserTable::query()
			->setSelect(['ID', 'PERSONAL_GENDER'])
			->where('ID', $userId)
			->fetchObject()
		;

		return $user?->getPersonalGender() === 'F'
			? Loc::getMessage('VOTE_INTEGRATION_IM_EVENT_HANDLER_MESSAGE_STOP_F', $replaces)
			: Loc::getMessage('VOTE_INTEGRATION_IM_EVENT_HANDLER_MESSAGE_STOP', $replaces)
		;
	}
}