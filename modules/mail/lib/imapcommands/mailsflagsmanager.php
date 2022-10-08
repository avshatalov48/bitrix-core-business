<?php

namespace Bitrix\Mail\ImapCommands;

use Bitrix\Mail;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use CUserCounter;

/**
 * Class MailsFlagsManager
 * @package Bitrix\Mail\ImapCommands
 */
class MailsFlagsManager extends SyncInternalManager
{
	public function markMailsUnseen()
	{
		$result = $this->initData();
		if (!$result->isSuccess())
		{
			return $result;
		}
		$result = $this->setMessagesFlag(static::FLAG_UNSEEN);
		if ($result->isSuccess())
		{
			$this->updateLeftMenuCounter();
		}
		return $result;
	}

	public function markMailsSeen()
	{
		$result = $this->initData();
		if (!$result->isSuccess())
		{
			return $result;
		}
		$result = $this->setMessagesFlag(static::FLAG_SEEN);
		if ($result->isSuccess())
		{
			$this->updateLeftMenuCounter();
		}
		return $result;
	}

	private function setMessagesFlag($flag)
	{
		$result = new Main\Result();

		if ($flag === static::FLAG_SEEN)
		{
			$result = $this->mailboxHelper->markSeen($this->messages);
		}
		elseif ($flag === static::FLAG_UNSEEN)
		{
			$result = $this->mailboxHelper->markUnseen($this->messages);
		}

		if ($result->isSuccess())
		{
			if ($flag === static::FLAG_SEEN)
			{
				$this->repository->markMessagesSeen($this->messages, $this->mailbox);
			}
			elseif ($flag === static::FLAG_UNSEEN)
			{
				$this->repository->markMessagesUnseen($this->messages, $this->mailbox);
			}

			return new Main\Result();
		}
		return (new Main\Result())->addError(new Main\Error(Loc::getMessage('MAIL_CLIENT_SYNC_ERROR'), 'MAIL_CLIENT_SYNC_ERROR'));
	}

	private function updateLeftMenuCounter()
	{
		CUserCounter::set(
			Main\Engine\CurrentUser::get()->getId(),
			'mail_unseen',
			Mail\Helper\Message::getCountersForUserMailboxes(Main\Engine\CurrentUser::get()->getId(), true),
			$this->mailbox['LID']
		);
	}

	public function setMessages($messages)
	{
		$this->messages = $messages;
	}
}