<?php

namespace Bitrix\Im\V2\Message\Send;

use Bitrix\Im\V2\Message\Param\ParamError;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Main;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Im\Message\Uuid;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Common\ContextCustomer;
use CIMMessageParamAttach;

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

	public function prepareFields(
		Chat $chat,
		array $fieldsToSend,
		?MessageCollection $forwardMessages,
		?\CRestServer $server
	): Result
	{
		if (isset($forwardMessages))
		{
			if (!isset($fieldsToSend['MESSAGE']) && !isset($fieldsToSend['ATTACH']))
			{
				return (new Result())->setResult([]);
			}
		}

		$result = $this->checkMessage($fieldsToSend);
		if(!$result->isSuccess())
		{
			return $result;
		}
		$fieldsToSend = $result->getResult();

		$chatData = $this->getChatData($chat, $fieldsToSend, $server);
		$fieldsToSend = array_merge($fieldsToSend, $chatData);

		if (isset($fieldsToSend['ATTACH']))
		{
			$result = $this->checkAttach($fieldsToSend);
			if (!$result->isSuccess())
			{
				return $result;
			}

			$fieldsToSend = $result->getResult();
		}

		if (!empty($fieldsToSend['KEYBOARD']))
		{
			$result = $this->checkKeyboard($fieldsToSend);
			if (!$result->isSuccess())
			{
				return $result;
			}

			$fieldsToSend = $result->getResult();
		}

		if (!empty($fieldsToSend['MENU']))
		{
			$result = $this->checkMenu($fieldsToSend);
			if (!$result->isSuccess())
			{
				return $result;
			}

			$fieldsToSend = $result->getResult();
		}

		if (isset($fieldsToSend['REPLY_ID']) && (int)$fieldsToSend['REPLY_ID'] > 0)
		{
			$result = $this->checkReply($fieldsToSend, $chat);
			if (!$result->isSuccess())
			{
				return $result;
			}

			$fieldsToSend = $result->getResult();
		}

		$fieldsToSend = $this->checkParams($fieldsToSend, $server);
		$fieldsToSend = isset($fieldsToSend['COPILOT']) ?$this->checkCopilotParams($fieldsToSend) : $fieldsToSend;

		return $result->setResult($fieldsToSend);
	}

	private function checkCopilotParams(array $fieldsToSend): array
	{
		$copilotData = [];

		if (isset($fieldsToSend['COPILOT']) && is_array($fieldsToSend['COPILOT']))
		{
			foreach ($fieldsToSend['COPILOT'] as $key => $item)
			{
				if ($key === 'promptCode' && is_string($item))
				{
					$copilotData[Message\Params::COPILOT_PROMPT_CODE] = $item;
				}
			}
		}

		$fieldsToSend['COPILOT'] = $copilotData;

		return $fieldsToSend;
	}

	private function checkMessage(array $fieldsToSend): Result
	{
		$result = new Result();
		if(isset($fieldsToSend['MESSAGE']))
		{
			if (!is_string($fieldsToSend['MESSAGE']))
			{
				return $result->addError(new MessageError(MessageError::EMPTY_MESSAGE,'Wrong message type'));
			}

			$fieldsToSend['MESSAGE'] = trim($fieldsToSend['MESSAGE']);

			if ($fieldsToSend['MESSAGE'] === '' && empty($arguments['ATTACH']))
			{
				return $result->addError(new MessageError(
					MessageError::EMPTY_MESSAGE,
					"Message can't be empty"
				));
			}
		}
		elseif (!isset($fieldsToSend['ATTACH']))
		{
			return $result->addError(new MessageError(MessageError::EMPTY_MESSAGE,"Message can't be empty"));
		}

		return $result->setResult($fieldsToSend);
	}

	private function getChatData(Chat $chat, array $fieldsToSend, ?\CRestServer $server): ?array
	{
		$userId = $chat->getContext()->getUserId();

		if ($chat->getType() === Chat::IM_TYPE_PRIVATE)
		{
			return [
				"MESSAGE_TYPE" => IM_MESSAGE_PRIVATE,
				"FROM_USER_ID" => $userId,
				"DIALOG_ID" => $chat->getDialogId(),
			];
		}

		if (isset($fieldsToSend['SYSTEM'], $server) && $fieldsToSend['SYSTEM'] === 'Y')
		{
			$fieldsToSend['MESSAGE'] = $this->prepareSystemMessage($server, $fieldsToSend['MESSAGE']);
		}

		return [
			'MESSAGE' => $fieldsToSend['MESSAGE'],
			"MESSAGE_TYPE" => IM_MESSAGE_CHAT,
			"FROM_USER_ID" => $userId,
			"DIALOG_ID" => $chat->getDialogId(),
		];
	}

	private function prepareSystemMessage(\CRestServer $server, string $message): string
	{
		$clientId = $server->getClientId();

		if (!$clientId)
		{
			return $message;
		}

		$result = \Bitrix\Rest\AppTable::getList(
			array(
				'filter' => array(
					'=CLIENT_ID' => $clientId
				),
				'select' => array(
					'CODE',
					'APP_NAME',
					'APP_NAME_DEFAULT' => 'LANG_DEFAULT.MENU_NAME',
				)
			)
		);
		$result = $result->fetch();
		$moduleName = !empty($result['APP_NAME'])
			? $result['APP_NAME']
			: (!empty($result['APP_NAME_DEFAULT'])
				? $result['APP_NAME_DEFAULT']
				: $result['CODE']
			)
		;

		return "[b]" . $moduleName . "[/b]\n" . $message;
	}

	private function checkAttach(array $fieldsToSend): Result
	{
		$result = new Result();

		$attach = CIMMessageParamAttach::GetAttachByJson($fieldsToSend['ATTACH']);
		if ($attach)
		{
			if ($attach->IsAllowSize())
			{
				$fieldsToSend['ATTACH'] = $attach;

				return $result->setResult($fieldsToSend);
			}

			return $result->addError(new ParamError(
				ParamError::ATTACH_ERROR,
				'You have exceeded the maximum allowable size of attach'
			));
		}

		return $result->addError(new ParamError(ParamError::ATTACH_ERROR, 'Incorrect attach params'));
	}

	private function checkKeyboard(array $fieldsToSend): Result
	{
		$result = new Result();

		$keyboard = [];
		$keyboardField = $fieldsToSend['KEYBOARD'];

		if (is_string($keyboardField))
		{
			$keyboardField = \CUtil::JsObjectToPhp($keyboardField);
		}
		if (!isset($keyboardField['BUTTONS']))
		{
			$keyboard['BUTTONS'] = $keyboardField;
		}
		else
		{
			$keyboard = $keyboardField;
		}

		$keyboard['BOT_ID'] = $fieldsToSend['BOT_ID'];
		$keyboard = \Bitrix\Im\Bot\Keyboard::getKeyboardByJson($keyboard);

		if ($keyboard)
		{
			$fieldsToSend['KEYBOARD'] = $keyboard;

			return $result->setResult($fieldsToSend);
		}

		return $result->addError(new ParamError(ParamError::KEYBOARD_ERROR,'Incorrect keyboard params'));
	}

	private function checkMenu(array $fieldsToSend): Result
	{
		$result = new Result();

		$menu = [];
		$menuField = $fieldsToSend['MENU'];

		if (is_string($menuField))
		{
			$menuField = \CUtil::JsObjectToPhp($menuField);
		}

		if (!isset($menuField['ITEMS']))
		{
			$menu['ITEMS'] = $menuField;
		}
		else
		{
			$menu = $menuField;
		}

		$menu['BOT_ID'] = $fieldsToSend['BOT_ID'];
		$menu = \Bitrix\Im\Bot\ContextMenu::getByJson($menu);

		if ($menu)
		{
			$fieldsToSend['MENU'] = $menu;

			return $result->setResult($fieldsToSend);
		}

		return $result->addError(new ParamError(ParamError::MENU_ERROR, 'Incorrect menu params'));
	}

	private function checkReply(array $fieldsToSend, Chat $chat): Result
	{
		$result = new Result();

		$message = new \Bitrix\Im\V2\Message((int)$fieldsToSend['REPLY_ID']);
		if (!$message->hasAccess())
		{
			return $result->addError(new MessageError(MessageError::REPLY_ERROR, 'Action unavailable'));
		}

		if ($message->getChat()->getId() !== $chat->getId())
		{
			return $result->addError(new MessageError(
				MessageError::REPLY_ERROR,
				'You can only reply to a message within the same chat')
			);
		}

		$fieldsToSend['PARAMS']['REPLY_ID'] = $message->getId();

		return $result->setResult($fieldsToSend);
	}

	private function checkParams(array $fieldsToSend, ?\CRestServer $server): array
	{
		$checkAuth = isset($server) ? $server->getAuthType() !== \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE : true;

		if (
			isset($fieldsToSend['SYSTEM']) && $fieldsToSend['SYSTEM'] === 'Y'
			&& $checkAuth
			&& \Bitrix\Im\Dialog::hasAccess($fieldsToSend['DIALOG_ID'])
		)
		{
			$fieldsToSend['SYSTEM'] = 'Y';
		}

		if (isset($fieldsToSend['URL_PREVIEW']) && $fieldsToSend['URL_PREVIEW'] === 'N')
		{
			$fieldsToSend['URL_PREVIEW'] = 'N';
		}

		if (isset($fieldsToSend['SKIP_CONNECTOR']) && mb_strtoupper($fieldsToSend['SKIP_CONNECTOR']) === 'Y')
		{
			$fieldsToSend['SKIP_CONNECTOR'] = 'Y';
			$fieldsToSend['SILENT_CONNECTOR'] = 'Y';
		}

		if (!empty($fieldsToSend['TEMPLATE_ID']))
		{
			$fieldsToSend['TEMPLATE_ID'] = mb_substr((string)$fieldsToSend['TEMPLATE_ID'], 0, 255);
		}

		return $fieldsToSend;
	}
}