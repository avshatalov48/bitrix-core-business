<?php

namespace Bitrix\Im\V2\Message\Delete;

use Bitrix\Im\Model\MessageDisappearingTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;

class DisappearService
{
	public const TIME_WHITELIST = [
		0, //disable
		1, //hour
		24, //day
		168, //week
		720, //month
	];
	private const DISAPPEARING_TIME_UNIT = 'hours';
	private const TIME_UNIT_HOUR = 'HOUR';
	private const TIME_UNIT_DAY = 'DAY';
	private const TIME_UNIT_WEEK = 'WEEK';
	private const TIME_UNIT_MONTH = 'MONTH';

	/**
	 * Handler for event `im:OnAfterMessagesAdd` fired in \CIMMessenger::Add.
	 * @see \CIMMessenger::Add
	 * @param $messageId
	 * @param $messageFields
	 * @return bool
	 */
	public static function checkDisappearing($messageId, $messageFields): bool
	{
		$message = new Message($messageId);
		if (
			!$message->getChat()->getDisappearingTime()
			|| $message->isSystem()
		)
		{
			return false;
		}

		$result = MessageDisappearingTable::add([
			'MESSAGE_ID' => $message->getId(),
			'DATE_CREATE' => new DateTime(),
			'DATE_REMOVE' => (new DateTime())->add($message->getChat()->getDisappearingTime() . ' ' . self::DISAPPEARING_TIME_UNIT)
		]);

		return $result->isSuccess();
	}

	public static function disappearMessage(Message $message, int $hours): Result
	{
		if (
			$message->isDisappearing()
		)
		{
			return (new Result())->addError(new Chat\ChatError(Chat\ChatError::ALREADY_DISAPPEARING));
		}

		return MessageDisappearingTable::add([
			'MESSAGE_ID' => $message->getId(),
			'DATE_CREATE' => new DateTime(),
			'DATE_REMOVE' => (new DateTime())->add($hours . ' ' . self::DISAPPEARING_TIME_UNIT)
		]);
	}

	public static function disappearChat(Chat $chat, int $hours): Result
	{
		$prevDisappearingTime = $chat->getDisappearingTime();
		if ((int)$prevDisappearingTime === $hours)
		{
			return new Result();
		}

		$chat->setDisappearingTime($hours);
		$result = $chat->save();

		if (!$result->isSuccess())
		{
			return $result;
		}

		if ((int)$prevDisappearingTime === 0 && $hours > 0)
		{
			\CIMMessage::Add([
				'MESSAGE_TYPE' => $chat->getType(),
				'TO_CHAT_ID' => $chat->getChatId(),
				'MESSAGE' => self::getDisappearingMessage($hours),
				'SYSTEM' => 'Y',
				'PUSH' => 'N'
			]);
		}
		elseif ($prevDisappearingTime > 0 && $hours === 0)
		{
			\CIMMessage::Add([
				'MESSAGE_TYPE' => $chat->getType(),
				'TO_CHAT_ID' => $chat->getChatId(),
				'MESSAGE' => Loc::getMessage('DISAPPEAR_MESSAGES_OFF'),
				'SYSTEM' => 'Y',
				'PUSH' => 'N'
			]);
		}
		elseif ($prevDisappearingTime > 0 && $hours > 0)
		{
			\CIMMessage::Add([
				'MESSAGE_TYPE' => $chat->getType(),
				'TO_CHAT_ID' => $chat->getChatId(),
				'MESSAGE' => self::getDisappearingMessage($hours, true),
				'SYSTEM' => 'Y',
				'PUSH' => 'N'
			]);
		}

		return $result;
	}

	public static function getMessagesDisappearingTime(array $messageIds): array
	{
		$rows = MessageDisappearingTable::getList([
			'filter' => [
				'MESSAGE_ID' => $messageIds
			]
		]);
		$result = [];
		foreach ($rows as $row)
		{
			$result[$row['MESSAGE_ID']] = $row;
		}

		return $result;
	}

	private static function getDisappearingMessage(int $hours, bool $change = false): string
	{
		switch ($hours)
		{
			case 720:
				$timeUnit = self::TIME_UNIT_MONTH;
				break;
			case 168:
				$timeUnit = self::TIME_UNIT_WEEK;
				break;
			case 24:
				$timeUnit = self::TIME_UNIT_DAY;
				break;
			default:
				$timeUnit = self::TIME_UNIT_HOUR;
		}

		$messageParts = [
			'DISAPPEAR_MESSAGES',
			$change ? 'CHANGE' : 'ON',
			$timeUnit,
		];

		return Loc::getMessage(implode('_', $messageParts));
	}
}
