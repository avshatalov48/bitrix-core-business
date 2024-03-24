<?php

namespace Bitrix\Im\V2\Link\Reminder;

use Bitrix\Im\Model\LinkReminderTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Link\Push;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
use CIMNotify;

class ReminderService
{
	use ContextCustomer;

	public const ADD_REMINDERS_EVENT = 'reminderAdd';
	public const DELETE_REMINDERS_EVENT = 'reminderDelete';

	public static function remindAgent(): string
	{
		(new static())->remind();

		return __METHOD__. '();';
	}

	public function getCount(int $chatId): int
	{
		$filter = Query::filter()
			->where('CHAT_ID', $chatId)
			->where('AUTHOR_ID', $this->getContext()->getUserId())
		;

		return LinkReminderTable::getCount($filter);
	}

	public function addMessageToReminders(Message $message, DateTime $dateRemind): Result
	{
		$result = new  Result();

		if ($dateRemind->getTimestamp() < (new DateTime())->getTimestamp())
		{
			return $result->addError(new ReminderError(ReminderError::DATE_REMIND_PASSED));
		}

		$reminder = ReminderItem::createFromMessage($message, $this->getContext())->setDateRemind($dateRemind);
		$saveResult = $this->saveReminder($reminder);

		if (!$saveResult->isSuccess())
		{
			return $result->addErrors($saveResult->getErrors());
		}

		$pushRecipient = ['RECIPIENT' => [$this->getContext()->getUserId()]];

		Push::getInstance()
			->setContext($this->context)
			->sendFull($reminder, static::ADD_REMINDERS_EVENT, $pushRecipient)
		;

		return $result;
	}

	public function deleteRemindersByMessage(Message $message): Result
	{
		$result = new Result();

		$reminders = ReminderCollection::getByMessage($message);

		if ($reminders->count() === 0)
		{
			return $result;
		}

		$deleteResult = $reminders->delete();

		if (!$deleteResult->isSuccess())
		{
			return $result->addErrors($deleteResult->getErrors());
		}

		foreach ($reminders as $reminder)
		{
			$pushRecipient = ['RECIPIENT' => [$reminder->getAuthorId()]];
			Push::getInstance()
				->setContext((new Context())->setUserId($reminder->getAuthorId()))
				->sendIdOnly($reminder, static::DELETE_REMINDERS_EVENT, $pushRecipient)
			;
		}

		return $result;
	}

	public function deleteReminder(ReminderItem $reminder): Result
	{
		$result = new Result();

		$deleteResult = $reminder->delete();

		if (!$deleteResult->isSuccess())
		{
			return $result->addErrors($deleteResult->getErrors());
		}

		$deleteNotifyResult = $this->deleteNotify($reminder);

		if (!$deleteNotifyResult->isSuccess())
		{
			$result->addErrors($deleteNotifyResult->getErrors());
		}

		$pushRecipient = ['RECIPIENT' => [$reminder->getAuthorId()]];

		Push::getInstance()
			->setContext((new Context())->setUserId($reminder->getAuthorId()))
			->sendIdOnly($reminder, static::DELETE_REMINDERS_EVENT, $pushRecipient)
		;

		return $result;
	}

	public function remind(): Result
	{
		$result = new Result();
		$reminders = ReminderCollection::getNeedReminded();

		$reminders->getMessageCollection()->fillFiles();

		foreach ($reminders as $reminder)
		{
			$sendResult = $this->sendNotifyAboutReminder($reminder);
			if (!$sendResult->isSuccess())
			{
				continue;
			}
			$reminder->setIsReminded(true);
		}

		$saveResult = $reminders->save(true);

		if(!$saveResult->isSuccess())
		{
			return $result->addErrors($saveResult->getErrors());
		}

		return $result;
	}

	protected function sendNotifyAboutReminder(ReminderItem $reminder): Result
	{
		$result = new Result();

		$attach = new \CIMMessageParamAttach();

		$user = $reminder->getEntity()->getAuthor();

		if ($user !== null)
		{
			$attach->AddUser([
				'NAME' => $user->getFullName(),
				'AVATAR' => $user->getAvatar(),
			]);
		}

		$attach->AddMessage($reminder->getEntity()->getPreviewMessage());

		$notifyParams = [
			'TO_USER_ID' => $reminder->getAuthorId(),
			'FROM_USER_ID' => 0,
			'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
			'NOTIFY_MODULE' => 'im',
			'NOTIFY_SUB_TAG' => $this->getSubTag($reminder),
			'NOTIFY_MESSAGE' => $this->getNotifyMessageText($reminder, false),
			'NOTIFY_MESSAGE_OUT' => $this->getNotifyMessageText($reminder, true),
			'ATTACH' => $attach
		];

		$notifyId = CIMNotify::Add($notifyParams);

		if ($notifyId === false)
		{
			return $result->addError(new ReminderError(ReminderError::REMINDER_NOTIFY_ADD_ERROR));
		}

		return $result;
	}

	protected function deleteNotify(ReminderItem $reminder): Result
	{
		$isDeleteSuccess = CIMNotify::DeleteBySubTag($this->getSubTag($reminder));

		if ($isDeleteSuccess)
		{
			return new Result();
		}

		return (new Result())->addError(new ReminderError(ReminderError::REMINDER_NOTIFY_DELETE_ERROR));
	}

	protected function saveReminder(ReminderItem $reminder): Result
	{
		try
		{
			return $reminder->save();
		}
		catch (\Bitrix\Main\SystemException $exception)
		{
			return (new Result())->addError(new Message\MessageError(Message\MessageError::MESSAGE_IS_ALREADY_IN_REMINDERS));
		}
	}

	protected function getNotifyMessageText(ReminderItem $reminder, bool $isOut): callable
	{
		$chat = Chat::getInstance($reminder->getChatId())->setContext((new Context())->setUserId($reminder->getAuthorId()));

		$chatTitle = $isOut ? $chat->getDisplayedTitle() : "[CHAT={$chat->getChatId()}]{$chat->getDisplayedTitle()}[/CHAT]";

		return fn (?string $languageId = null) => Loc::getMessage(
			'IM_CHAT_REMINDER_REMIND_NOTIFICATION',
			['#CHAT_TITLE#' => $chatTitle],
			$languageId
		);
	}

	private function getSubTag(ReminderItem $reminder): string
	{
		return "MESSAGE_REMINDER_{$reminder->getId()}";
	}
}