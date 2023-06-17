<?php

namespace Bitrix\Im\V2\Message\Send;

use Bitrix\Main;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Im;
use Bitrix\Im\User;
use Bitrix\Im\Message\Uuid;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Common\ContextCustomer;

class SendingService
{
	use ContextCustomer;

	private SendingConfig $sendingConfig;

	protected ?Uuid $uuidService;

	public const
		EVENT_BEFORE_MESSAGE_ADD = 'OnBeforeMessageAdd',//new
		EVENT_AFTER_MESSAGE_ADD = 'OnAfterMessagesAdd',
		EVENT_BEFORE_CHAT_MESSAGE_ADD = 'OnBeforeChatMessageAdd',
		EVENT_BEFORE_NOTIFY_ADD = 'OnBeforeMessageNotifyAdd',
		EVENT_AFTER_NOTIFY_ADD = 'OnBeforeMessageNotifyAdd'
	;

	/**
	 * @param SendingConfig|null $sendingConfig
	 */
	public function __construct(?SendingConfig $sendingConfig = null)
	{
		if ($sendingConfig === null)
		{
			$sendingConfig = new SendingConfig();
		}
		$this->sendingConfig = $sendingConfig;
	}

	//region UUID

	/**
	 * @param Message $message
	 * @return Result
	 */
	public function checkDuplicateByUuid(Message $message): Result
	{
		$result = new Result;

		if (
			$message->getUuid()
			&& !$message->isSystem()
			&& Uuid::validate($message->getUuid())
		)
		{
			$this->uuidService = new Uuid($message->getUuid());
			$uuidAddResult = $this->uuidService->add();
			// if it is false, then UUID already exists
			if (!$uuidAddResult)
			{
				$messageIdByUuid = $this->uuidService->getMessageId();

				// if we got message_id, then message already exists, and we don't need to add it, so return with ID.
				if (!is_null($messageIdByUuid))
				{
					return $result->setResult(['messageId' => $messageIdByUuid]);
				}

				// if there is no message_id and entry date is expired,
				// then update date_create and return false to delay next sending on the client.
				if (!$this->uuidService->updateIfExpired())
				{
					return $result->addError(new MessageError(MessageError::MESSAGE_DUPLICATED_BY_UUID));
				}
			}
		}

		return $result;
	}

	/**
	 * @param Message $message
	 * @return void
	 */
	public function updateMessageUuid(Message $message): void
	{
		if (isset($this->uuidService))
		{
			$this->uuidService->updateMessageId($message->getMessageId());
		}
	}

	//endregion

	//region Events

	/**
	 * Fires event `im:OnBeforeMessageAdd` on before message send.
	 *
	 * @event im:OnBeforeMessageAdd
	 * @param Chat $chat
	 * @param Message $message
	 * @return Result
	 */
	public function fireEventBeforeSend(Chat $chat, Message $message): Result
	{
		$result = new Result;

		$event = new Event('im', static::EVENT_BEFORE_MESSAGE_ADD, [
			'message' => $message->toArray(),
			'parameters' => $this->sendingConfig->toArray(),
		]);
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			$eventParams = $eventResult->getParameters();
			if ($eventResult->getType() === EventResult::SUCCESS)
			{
				if ($eventParams)
				{
					if (isset($eventParams['message']) && is_array($eventParams['message']))
					{
						unset(
							$eventParams['message']['MESSAGE_ID'],
							$eventParams['message']['CHAT_ID'],
							$eventParams['message']['AUTHOR_ID'],
							$eventParams['message']['FROM_USER_ID']
						);
						$message->fill($eventParams['message']);
					}
					if (isset($eventParams['parameters']) && is_array($eventParams['parameters']))
					{
						$this->sendingConfig->fill($eventParams['parameters']);
					}
				}
			}
			elseif ($eventResult->getType() === EventResult::ERROR)
			{
				if ($eventParams && isset($eventParams['error']))
				{
					if ($eventParams['error'] instanceof Main\Error)
					{
						$result->addError($eventParams['error']);
					}
					elseif (is_string($eventParams['error']))
					{
						$result->addError(new ChatError(ChatError::BEFORE_SEND_EVENT, $eventParams['error']));
					}
				}
				else
				{
					$result->addError(new ChatError(ChatError::BEFORE_SEND_EVENT));
				}
			}
		}

		return $result;
	}

	/**
	 * Fires event `im:OnBeforeChatMessageAdd` on before message send.
	 *
	 * @event im:OnBeforeChatMessageAdd
	 * @param Chat $chat
	 * @param Message $message
	 * @return Result
	 */
	public function fireEventBeforeMessageSend(Chat $chat, Message $message): Result
	{
		$result = new Result;

		$compatibleFields = array_merge(
			$message->toArray(),
			$this->sendingConfig->toArray(),
		);
		$compatibleChatFields = $chat->toArray();

		foreach (\GetModuleEvents('im', self::EVENT_BEFORE_CHAT_MESSAGE_ADD, true) as $event)
		{
			$eventResult = \ExecuteModuleEventEx($event, [$compatibleFields, $compatibleChatFields]);
			if ($eventResult === false || isset($eventResult['result']) && $eventResult['result'] === false)
			{
				$reason = $this->detectReasonSendError($chat->getType(), $eventResult['reason'] ?? '');
				return $result->addError(new ChatError(ChatError::FROM_OTHER_MODULE, $reason));
			}

			if (isset($eventResult['fields']) && is_array($eventResult['fields']))
			{
				unset(
					$eventResult['fields']['MESSAGE_ID'],
					$eventResult['fields']['CHAT_ID'],
					$eventResult['fields']['AUTHOR_ID'],
					$eventResult['fields']['FROM_USER_ID']
				);
				$message->fill($eventResult['fields']);
				$this->sendingConfig->fill($eventResult['fields']);
			}
		}

		return $result;
	}

	/**
	 * Fires event `im:OnAfterMessagesAdd` on before message send.
	 *
	 * @event im:OnAfterMessagesAdd
	 * @param Chat $chat
	 * @param Message $message
	 * @return Result
	 */
	public function fireEventAfterMessageSend(Chat $chat, Message $message): Result
	{
		$result = new Result;

		$compatibleFields = array_merge(
			$message->toArray(),
			$chat->toArray(),
			[
				'FILES' => [], //todo: Move it into Message
				'EXTRA_PARAMS' => [],
				'URL_ATTACH' => [],
				'BOT_IN_CHAT' => [],
			],
			$this->sendingConfig->toArray(),
		);

		foreach (\GetModuleEvents('im', static::EVENT_AFTER_MESSAGE_ADD, true) as $event)
		{
			\ExecuteModuleEventEx($event, [$message->getMessageId(), $compatibleFields]);
		}

		return $result;
	}

	/**
	 * Fires event `im:OnBeforeMessageNotifyAdd` on before message send.
	 *
	 * @event im:OnBeforeMessageNotifyAdd
	 * @param Chat $chat
	 * @param Message $message
	 * @return Result
	 */
	public function fireEventBeforeNotifySend(Chat $chat, Message $message): Result
	{
		$result = new Result;

		$compatibleFields = array_merge(
			$message->toArray(),
			$chat->toArray(),
			$this->sendingConfig->toArray(),
		);

		foreach (\GetModuleEvents('im', self::EVENT_BEFORE_NOTIFY_ADD, true) as $arEvent)
		{
			$eventResult = \ExecuteModuleEventEx($arEvent, [&$compatibleFields]);
			if ($eventResult === false || isset($eventResult['result']) && $eventResult['result'] === false)
			{
				$reason = $this->detectReasonSendError($chat->getType(), $eventResult['reason'] ?? '');
				return $result->addError(new ChatError(ChatError::FROM_OTHER_MODULE, $reason));
			}
		}

		return $result;
	}

	/**
	 * Fires event `im:OnAfterNotifyAdd` on before message send.
	 *
	 * @event im:OnAfterNotifyAdd
	 * @param Chat $chat
	 * @param Message $message
	 * @return Result
	 */
	public function fireEventAfterNotifySend(Chat $chat, Message $message): Result
	{
		$result = new Result;

		$compatibleFields = array_merge(
			$message->toArray(),
			$chat->toArray(),
			$this->sendingConfig->toArray(),
		);

		foreach(\GetModuleEvents('im', self::EVENT_AFTER_NOTIFY_ADD, true) as $event)
		{
			\ExecuteModuleEventEx($event, [(int)$message->getMessageId(), $compatibleFields]);
		}

		return $result;
	}

	private function detectReasonSendError($type, $reason = ''): string
	{
		if (!empty($reason))
		{
			$sanitizer = new \CBXSanitizer;
			$sanitizer->addTags([
				'a' => ['href','style', 'target'],
				'b' => [],
				'u' => [],
				'i' => [],
				'br' => [],
				'span' => ['style'],
			]);
			$reason = $sanitizer->sanitizeHtml($reason);
		}
		else
		{
			if ($type == Chat::IM_TYPE_PRIVATE)
			{
				$reason = Loc::getMessage('IM_ERROR_MESSAGE_CANCELED');
			}
			else if ($type == Chat::IM_TYPE_SYSTEM)
			{
				$reason = Loc::getMessage('IM_ERROR_NOTIFY_CANCELED');
			}
			else
			{
				$reason = Loc::getMessage('IM_ERROR_GROUP_CANCELED');
			}
		}

		return $reason;
	}
	//endregion
}