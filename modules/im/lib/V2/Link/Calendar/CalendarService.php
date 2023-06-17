<?php

namespace Bitrix\Im\V2\Link\Calendar;

use Bitrix\Im\Dialog;
use Bitrix\Im\Model\LinkCalendarIndexTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\Calendar\CalendarError;
use Bitrix\Im\V2\Link\Push;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class CalendarService
{
	use ContextCustomer;

	protected const ADD_CALENDAR_EVENT = 'calendarAdd';
	protected const UPDATE_CALENDAR_EVENT = 'calendarUpdate';
	protected const DELETE_CALENDAR_EVENT = 'calendarDelete';

	public function registerCalendar(int $chatId, ?int $messageId, \Bitrix\Im\V2\Entity\Calendar\CalendarItem $calendar): Result
	{
		$result = new Result();

		$userId = $this->getContext()->getUserId();

		$calendarLink = new CalendarItem();
		$calendarLink->setEntity($calendar)->setChatId($chatId)->setAuthorId($userId);

		if (isset($messageId))
		{
			$calendarLink->setMessageId($messageId);
		}

		$sendMessageResult = $this->sendMessageAboutCalendar($calendarLink, $chatId);

		if (!$sendMessageResult->isSuccess())
		{
			$result->addErrors($sendMessageResult->getErrors());
		}

		$systemMessageId = $sendMessageResult->getResult();

		$calendarLink->setMessageId($messageId ?: $systemMessageId);
		$saveResult = $calendarLink->save();

		if (!$saveResult->isSuccess())
		{
			return $result->addErrors($saveResult->getErrors());
		}

		Push::getInstance()
			->setContext($this->context)
			->sendFull($calendarLink, self::ADD_CALENDAR_EVENT, ['RECIPIENT' => $calendar->getMembersIds()])
		;

		return $result;
	}

	public function unregisterCalendar(CalendarItem $calendar): Result
	{
		$calendar->delete();
		Push::getInstance()
			->setContext($this->context)
			->sendIdOnly($calendar, self::DELETE_CALENDAR_EVENT, ['CHAT_ID' => $calendar->getChatId()])
		;

		return new Result();
	}

	public function updateCalendar(CalendarItem $calendarLink): Result
	{
		$result = new Result();

		LinkCalendarIndexTable::delete($calendarLink->getPrimaryId());
		$saveResult = $calendarLink->save();

		if (!$saveResult->isSuccess())
		{
			return $result->addErrors($saveResult->getErrors());
		}

		Push::getInstance()
			->setContext($this->context)
			->sendFull($calendarLink, self::UPDATE_CALENDAR_EVENT, ['RECIPIENT' => $calendarLink->getEntity()->getMembersIds()])
		;

		return new Result();
	}

	public function prepareDataForCreateSlider(Chat $chat, ?Message $message = null): Result
	{
		$result = new Result();

		if (!Loader::includeModule('calendar'))
		{
			return $result->addError(new CalendarError(CalendarError::CALENDAR_NOT_INSTALLED));
		}

		$chat->setContext($this->context);

		$chatTypeModifier = ($chat->getType() === \IM_MESSAGE_PRIVATE) ? 'PRIVATE_' : '';
		$from = isset($message) ? 'MESSAGE' : 'CHAT';
		$from = $chatTypeModifier . $from;

		$data['params']['entryName'] = Loc::getMessage(
			"IM_CHAT_CALENDAR_SERVICE_FROM_{$from}_NEW_TITLE",
			["#CHAT_TITLE#" => $chat->getTitle()]
		);

		$randomPostfix = mt_rand() & 1000; // get random number from 0 to 1000
		$data['params']['sliderId'] = "im:chat{$chat->getChatId()}{$randomPostfix}";

		$userIds = $chat->getRelations(
			[
				'SELECT' => ['ID', 'USER_ID', 'CHAT_ID'],
				'FILTER' => ['ACTIVE' => true, 'ONLY_INTERNAL_TYPE' => true],
				'LIMIT' => 50,
			]
		)->getUsers()->filterExtranet()->getIds();
		$users = array_values(array_map(static fn($item) => ['id' => (int)$item, 'entityId' => 'user'], $userIds));
		$data['params']['participantsEntityList'] = $users;

		if (isset($message))
		{
			$message->setContext($this->context);
			$data['params']['entryDescription'] = \CIMShare::PrepareText([
				'CHAT_ID' => $chat->getChatId(),
				'MESSAGE_ID' => $message->getMessageId(),
				'MESSAGE_TYPE' => $chat->getType(),
				'MESSAGE' => $message->getMessage(),
				'AUTHOR_ID' => $message->getAuthorId(),
				'FILES' => $this->getFilesForPrepareText($message)
			]);
		}

		return $result->setResult($data);
	}

	protected function sendMessageAboutCalendar(CalendarItem $calendarLink, int $chatId): Result
	{
		//todo: Replace with new API
		$dialogId = Dialog::getDialogId($chatId);
		$authorId = $this->getContext()->getUserId();

		$messageId = \CIMChat::AddMessage([
			'DIALOG_ID' => $dialogId,
			'SYSTEM' => 'Y',
			'MESSAGE' => $this->getMessageText($calendarLink),
			'FROM_USER_ID' => $authorId,
			'PARAMS' => ['CLASS' => "bx-messenger-content-item-system"],
			'URL_PREVIEW' => 'N',
			'SKIP_CONNECTOR' => 'Y',
			'SKIP_COMMAND' => 'Y',
			'SILENT_CONNECTOR' => 'Y',
			'SKIP_URL_INDEX' => 'Y',
		]);

		$result = new Result();

		if ($messageId === false)
		{
			return $result->addError(new CalendarError(CalendarError::ADD_CALENDAR_MESSAGE_FAILED));
		}

		return $result->setResult($messageId);
	}

	protected function getFilesForPrepareText(Message $message): array
	{
		$files = $message->getFiles();
		$filesForPrepare = [];

		foreach ($files as $file)
		{
			$filesForPrepare[] = ['name' => $file->getDiskFile()->getName()];
		}

		return $filesForPrepare;
	}

	protected function getMessageText(CalendarItem $calendar): string
	{
		$genderModifier = ($this->getContext()->getUser()->getGender() === 'F') ? '_F' : '';

		if ($calendar->getMessageId() !== null)
		{
			$text = (new Message($calendar->getMessageId()))->getQuotedMessage() . "\n";
			$text .= Loc::getMessage(
				'IM_CHAT_CALENDAR_REGISTER_FROM_MESSAGE_NOTIFICATION' . $genderModifier,
				[
					'#LINK#' => $calendar->getEntity()->getUrl(),
					'#USER_ID#' => $this->getContext()->getUserId(),
					'#MESSAGE_ID#' => $calendar->getMessageId(),
					'#DIALOG_ID#' => Chat::getInstance($calendar->getChatId())->getDialogContextId(),
				]
			);

			return $text;
		}
		return Loc::getMessage(
			'IM_CHAT_CALENDAR_REGISTER_FROM_CHAT_NOTIFICATION' . $genderModifier,
			[
				'#LINK#' => $calendar->getEntity()->getUrl(),
				'#USER_ID#' => $this->getContext()->getUserId(),
				'#EVENT_TITLE#' => $calendar->getEntity()->getTitle(),
			]
		);
	}
}