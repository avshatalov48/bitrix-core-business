<?php

namespace Bitrix\Im\V2\Message\Delete;

use Bitrix\Im\Model\MessageDisappearingTable;
use Bitrix\Im\V2\Analytics\ChatAnalytics;
use Bitrix\Im\V2\Application\Features;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Integration\Socialnetwork\Collab\Collab;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Permission;
use Bitrix\Im\V2\Pull\Event\MessagesAutoDeleteDelayChanged;
use Bitrix\Im\V2\Pull\Event\UpdateFeature;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Im\V2\Chat\MessagesAutoDelete\MessagesAutoDeleteConfigs;

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
			!$message->getChat()->getMessagesAutoDeleteDelay()
			|| $message->isSystem()
		)
		{
			return false;
		}

		$result = MessageDisappearingTable::add([
			'MESSAGE_ID' => $message->getId(),
			'DATE_CREATE' => new DateTime(),
			'DATE_REMOVE' => (new DateTime())->add($message->getChat()->getMessagesAutoDeleteDelay() . ' ' . self::DISAPPEARING_TIME_UNIT)
		]);

		return $result->isSuccess();
	}

	public static function disappearMessage(Message $message, int $delay): Result
	{
		$result = new Result();

		if ($delay === 0 || !self::isTimeValid($delay))
		{
			return $result
				->addError(new MessagesAutoDeleteError(MessagesAutoDeleteError::WRONG_MESSAGES_AUTO_DELETE_DELAY))
			;
		}

		if ($message->isDisappearing())
		{
			return $result
				->addError(new MessagesAutoDeleteError(MessagesAutoDeleteError::ALREADY_DISAPPEARING))
			;
		}

		$addResult = MessageDisappearingTable::add([
			'MESSAGE_ID' => $message->getId(),
			'DATE_CREATE' => new DateTime(),
			'DATE_REMOVE' => (new DateTime())->add($delay . ' ' . self::DISAPPEARING_TIME_UNIT)
		]);

		if (!$addResult->isSuccess())
		{
			$result->addErrors($addResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param Chat $chat
	 * @param int $delay
	 * @return Result<MessagesAutoDeleteConfigs>
	 */
	public static function disappearChat(Chat $chat, int $delay): Result
	{
		$result = self::formatResult($chat->getId() ?? 0);
		$prevDelay = $chat->getMessagesAutoDeleteDelay();

		if ($prevDelay === $delay)
		{
			return $result;
		}

		$availabilityResult = self::checkAvailabilityByOption();
		if ($delay !== 0 && !$availabilityResult->isSuccess())
		{
			return self::disappearChat($chat, 0);
		}

		if (!self::isTimeValid($delay))
		{
			return $result
				->addError(new MessagesAutoDeleteError(MessagesAutoDeleteError::WRONG_MESSAGES_AUTO_DELETE_DELAY))
			;
		}

		if (self::isPrivateChatWithBot($chat))
		{
			return $result
				->addError(new ChatError(ChatError::ACCESS_DENIED))
			;
		}

		$chat->setMessagesAutoDeleteDelay($delay);
		$saveResult = $chat->save();

		if (!$saveResult->isSuccess())
		{
			return $result
				->addErrors($saveResult->getErrors())
			;
		}

		self::sendPull($chat);
		self::sendMessage($chat, $prevDelay, $delay);
		self::sendAnalytics($chat, $prevDelay, $delay);

		return $result;
	}

	/**
	 * @param Chat\CollabChat $chat
	 * @param int $delay
	 * @return Result<MessagesAutoDeleteConfigs>
	 */
	public static function disappearCollab(Chat\CollabChat $chat, int $delay): Result
	{
		$chatId = $chat->getId() ?? 0;
		$result = Collab::updateCollabOption(
			$chat,
			'messagesAutoDeleteDelay',
			(string)$delay
		);

		if (!$result->isSuccess())
		{
			return self::formatResult($chatId)
				->addErrors($result->getErrors())
			;
		}

		return self::formatResult($chatId);
	}

	/**
	 * @param int $chatId
	 * @return Result<MessagesAutoDeleteConfigs>
	 */
	public static function formatResult(int $chatId): Result
	{
		$config = new MessagesAutoDeleteConfigs([$chatId]);
		return (new Result())->setResult($config);
	}

	protected static function isTimeValid(int $hours): bool
	{
		return in_array($hours, self::TIME_WHITELIST, true);
	}

	public static function checkAvailabilityByOption(): Result
	{
		$result = new Result();

		if (!Features::isMessagesAutoDeleteEnabled())
		{
			$result->addError(new MessagesAutoDeleteError(MessagesAutoDeleteError::MESSAGES_AUTO_DELETE_DISABLED));
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

	private static function getDisappearingMessage(int $prevDelay, int $currenDelay, int $userId): string
	{
		$timeUnitTextBefore = self::getTimeUnitTextFromDelay($prevDelay);
		$timeUnitTextAfter = self::getTimeUnitTextFromDelay($currenDelay);

		$replaces = [
			'#USER_ID#' => $userId,
			'#NEW_TIME_UNIT#' => $timeUnitTextAfter,
			'#OLD_TIME_UNIT#' => $timeUnitTextBefore,
		];

		$messageCode = match (true)
		{
			$currenDelay === 0 => 'DISAPPEAR_MESSAGES_OFF',
			$prevDelay > 0 => 'DISAPPEAR_MESSAGES_CHANGE',
			$prevDelay === 0 => 'DISAPPEAR_MESSAGES_ON',
			default => '',
		};

		return Loc::getMessage($messageCode, $replaces) ?? '';
	}

	protected static function getTimeUnitTextFromDelay(int $delay): string
	{
		$timeUnitText = match ($delay)
		{
			720 => Loc::getMessage('DISAPPEAR_TIME_UNIT_ONE_MONTH'),
			168 => Loc::getMessage('DISAPPEAR_TIME_UNIT_ONE_WEEK'),
			24 => Loc::getMessage('DISAPPEAR_TIME_UNIT_ONE_DAY'),
			1 => Loc::getMessage('DISAPPEAR_TIME_UNIT_ONE_HOUR'),
			default => '',
		};

		return $timeUnitText ?? '';
	}

	protected static function getBaseMessageFields(Chat $chat): array
	{
		return [
			'FROM_USER_ID' => $chat->getContext()->getUserId(),
			'MESSAGE_TYPE' => $chat->getType(),
			'TO_CHAT_ID' => $chat->getChatId(),
			'MESSAGE' => '',
			'SYSTEM' => 'Y',
			'PUSH' => 'N'
		];
	}

	private static function sendMessage(Chat $chat, int $prevDelay, int $currentDelay): void
	{
		if ($prevDelay < 0 || $currentDelay < 0)
		{
			return;
		}

		$message = self::getBaseMessageFields($chat);
		$message['MESSAGE'] = self::getDisappearingMessage($prevDelay, $currentDelay, $chat->getContext()->getUserId());

		\CIMMessage::Add($message);
	}

	private static function sendAnalytics(Chat $chat, int $prevAutoDeleteDelay, int $currentAutoDeleteDelay): void
	{
		if ($prevAutoDeleteDelay === 0 && $currentAutoDeleteDelay > 0)
		{
			(new ChatAnalytics($chat))->addAutoDeleteOn($currentAutoDeleteDelay);
		}
		elseif ($prevAutoDeleteDelay > 0 && $currentAutoDeleteDelay === 0)
		{
			(new ChatAnalytics($chat))->addAutoDeleteOff();
		}
	}

	public static function sendMessageAfterChatAdd(Chat $chat): void
	{
		$messagesAutoDeleteDelay = $chat->getMessagesAutoDeleteDelay();
		if ($messagesAutoDeleteDelay > 0)
		{
			self::sendMessage($chat, 0, $messagesAutoDeleteDelay);
		}
	}

	private static function sendPull(Chat $chat): Result
	{
		return (new MessagesAutoDeleteDelayChanged($chat))->send();
	}

	private static function isPrivateChatWithBot(Chat $chat): bool
	{
		return
			$chat instanceof Chat\PrivateChat
			&& $chat->getCompanion()->isBot()
		;
	}

	public static function onAutoDeleteOptionChanged(Event $event): void
	{
		$value = $event->getParameter('value');

		if (!isset($value) || !in_array($value, ['Y', 'N'], true))
		{
			return;
		}

		$value = $value === 'Y';
		(new UpdateFeature('messagesAutoDeleteEnabled', $value))->send();
	}

	public static function checkAvailability(string $chatType): Result
	{
		$role = Permission::getRoleForActionByType(
			$chatType,
			Permission\Action::ChangeMessagesAutoDeleteDelay
		);
		$result = self::checkAvailabilityByOption();

		if ($role === Chat::ROLE_NONE)
		{
			$result->addError(new ChatError(ChatError::WRONG_PARAMETER));
		}

		return $result;
	}
}
