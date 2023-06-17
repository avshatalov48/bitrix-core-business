<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Main\Localization\Loc;
use Bitrix\Im\Notify;
use Bitrix\Im\User;
use Bitrix\Im\Model\UserTable;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Im\V2\Message\Send\SendingConfig;
use Bitrix\Im\V2\Message\Send\SendingService;
use Bitrix\Im\V2\Message\Send\PushService;
use Bitrix\Im\V2\Message\Params;
use Bitrix\Im\V2\Message\ReadService;

class NotifyChat extends Chat
{
	protected function getDefaultType(): string
	{
		return self::IM_TYPE_SYSTEM;
	}

	protected function checkAccessWithoutCaching(int $userId): bool
	{
		return false;
	}

	/**
	 * Allows to send mention notification.
	 * @return bool
	 */
	public function allowMention(): bool
	{
		return false;
	}


	public function getStartId(?int $userId = null): int
	{
		return 0;
	}

	protected function prepareParams(array $params = []): Result
	{
		$result = new Result();

		if (!isset($params['AUTHOR_ID']))
		{
			if (!isset($params['TO_USER_ID']))
			{
				return $result->addError(new ChatError(ChatError::WRONG_RECIPIENT));
			}

			$params['AUTHOR_ID'] = $params['TO_USER_ID'];
		}

		$result->setResult($params);

		return $result;
	}

	/**
	 * Looks for notification channel for user
	 *
	 * @param array $params
	 * <pre>
	 * [
	 * 	(int) TO_USER_ID
	 * ]
	 * </pre>
	 * @return Result
	 */
	public static function find(array $params = [], ?Context $context = null): Result
	{
		$result = new Result;

		if (empty($params['TO_USER_ID']))
		{
			$context = $context ?? Locator::getContext();
			$params['TO_USER_ID'] = $context->getUserId();
		}

		$params['TO_USER_ID'] = (int)$params['TO_USER_ID'];
		if ($params['TO_USER_ID'] <= 0)
		{
			return $result->addError(new ChatError(ChatError::WRONG_RECIPIENT));
		}

		$blockedExternalAuthId = \Bitrix\Im\Model\UserTable::filterExternalUserTypes(['replica']);
		$res = \Bitrix\Im\Model\UserTable::getById($params['TO_USER_ID']);
		if (
			!($userData = $res->fetch())
			|| $userData['ACTIVE'] == 'N'
			|| in_array($userData['EXTERNAL_AUTH_ID'], $blockedExternalAuthId, true)
		)
		{
			return $result->addError(new ChatError(ChatError::WRONG_RECIPIENT));
		}

		$connection = \Bitrix\Main\Application::getConnection();

		$res = $connection->query("
			SELECT *
			FROM b_im_chat
			WHERE AUTHOR_ID = " . $params['TO_USER_ID'] . " AND TYPE = '" . self::IM_TYPE_SYSTEM . "'
			ORDER BY ID ASC
		");
		if ($row = $res->fetch())
		{
			$result->setResult($row);
		}

		return $result;
	}

	public function add(array $params, ?Context $context = null): Result
	{
		$result = new Result;

		$paramsResult = $this->prepareParams($params);
		if ($paramsResult->isSuccess())
		{
			$params = $paramsResult->getResult();
		}
		else
		{
			return $result->addErrors($paramsResult->getErrors());
		}

		$blockedExternalAuthId = \Bitrix\Im\Model\UserTable::filterExternalUserTypes(['replica']);
		$res = \Bitrix\Im\Model\UserTable::getById($params['AUTHOR_ID']);
		if (
			!($userData = $res->fetch())
			|| $userData['ACTIVE'] == 'N'
			|| in_array($userData['EXTERNAL_AUTH_ID'], $blockedExternalAuthId, true)
		)
		{
			return $result->addError(new ChatError(ChatError::WRONG_RECIPIENT));
		}

		$chat = new NotifyChat($params);
		$chat->save();

		if ($chat->getChatId() <= 0)
		{
			return $result->addError(new ChatError(ChatError::CREATION_ERROR));
		}

		\Bitrix\Im\Model\RelationTable::add([
			'CHAT_ID' => $chat->getChatId(),
			'MESSAGE_TYPE' => \IM_MESSAGE_SYSTEM,
			'USER_ID' => $params['AUTHOR_ID'],
		]);

		return $result->setResult([
			'CHAT_ID' => $chat->getChatId(),
			'CHAT' => $chat,
		]);
	}

	/**
	 * Provides message sending process.
	 *
	 * @param Message|string|array $message
	 * @param SendingConfig|array|null $sendingConfig
	 * @return Result
	 */
	public function sendMessage($message, $sendingConfig = null): Result
	{
		$result = new Result;

		if (!$this->getChatId())
		{
			return $result->addError(new ChatError(ChatError::WRONG_TARGET_CHAT));
		}

		if (!$message instanceof Message)
		{
			$message = new Message($message);
		}
		$message
			->setRegistry($this->messageRegistry)
			->setContext($this->context)
			->setChatId($this->getChatId())
		;

		if (!$message->getNotifyModule())
		{
			$message->setNotifyModule('im');
		}
		if (!$message->getNotifyEvent())
		{
			$message->setNotifyEvent(Notify::EVENT_DEFAULT);
		}
		if (!$message->getNotifyType())
		{
			if ($message->getAuthorId())
			{
				$message->setNotifyType(\IM_NOTIFY_FROM);
			}
			else
			{
				$message->setNotifyType(\IM_NOTIFY_SYSTEM);
			}
		}
		if ($message->allowNotifyAnswer())
		{
			$message->getParams()->get(Params::CAN_ANSWER)->setValue(true);
		}

		// config for sending process
		if ($sendingConfig instanceof SendingConfig)
		{
			$sendingServiceConfig = $sendingConfig;
		}
		else
		{
			$sendingServiceConfig = new SendingConfig();
			if (is_array($sendingConfig))
			{
				$sendingServiceConfig->fill($sendingConfig);
			}
		}
		// sending process
		$sendService = new SendingService($sendingServiceConfig);
		$sendService->setContext($this->context);


		// fire event `im:OnBeforeMessageNotifyAdd` before message send
		$eventResult = $sendService->fireEventBeforeNotifySend($this, $message);
		if (!$eventResult->isSuccess())
		{
			// cancel sending by event
			return $result->addErrors($eventResult->getErrors());
		}

		$checkResult = $this->validateMessage($message, $sendingServiceConfig);
		if (!$checkResult->isSuccess())
		{
			return $result->addErrors($checkResult->getErrors());
		}

		$skipAdd = false;
		$skipFlash = false;
		if ($message->getNotifyType() != \IM_NOTIFY_CONFIRM)
		{
			$skipAdd = !\CIMSettings::GetNotifyAccess($this->getAuthorId(), $message->getNotifyModule(), $message->getNotifyEvent(), \CIMSettings::CLIENT_SITE);
			$skipFlash = $skipAdd;
		}
		if (!$skipAdd && $message->isNotifyFlash() === true)
		{
			$skipAdd = true;
		}
		if ($skipAdd)
		{
			$message
				->markNotifyRead(true)
				->markNotifyFlash(true)
			;
		}

		// fill message param USERS with authorIds and drop other notify by tag
		$this->dropOtherUserNotificationByTag($message);

		if ($message->getNotifyType() == \IM_NOTIFY_CONFIRM)
		{
			$this->prepareConfirm($message);
			$this->dropAllConfirmByTag($message);
		}

		$counter = 0;
		if ($skipAdd)
		{
			$message->setMessageId(time());
		}
		else
		{
			// Save + Save Params
			$saveResult = $message->save();
			if (!$saveResult->isSuccess())
			{
				return $result->addErrors($saveResult->getErrors());
			}

			$messageCount = MessageTable::getCount(['=CHAT_ID' => $this->getChatId()]);

			$this
				->setMessageCount($messageCount)
				->setLastMessageId($message->getMessageId())
				->save()
			;

			// Unread
			$readService = new ReadService($this->getAuthorId());
			$readService->markNotificationUnread($message, $this->getRelations());

			$counter = $readService->getCounterService()->getByChat($this->getChatId());
		}

		// fire event `im:OnAfterNotifyAdd`
		$sendService->fireEventAfterNotifySend($this, $message);

		// send Push
		if ($sendingServiceConfig->sendPush())
		{
			$pushService = new PushService($sendingServiceConfig);
			$pushService->sendPushNotification($this, $message, $counter, !$skipFlash);
		}

		// search
		if (!$skipAdd)
		{
			$message->updateSearchIndex();
		}

		$result->setResult(['messageId' => $message->getMessageId()]);

		return $result;
	}

	/**
	 * @param Message $message
	 * @param SendingConfig $sendingServiceConfig
	 * @return Result
	 */
	public function validateMessage(Message $message, SendingConfig $sendingServiceConfig): Result
	{
		$result = new Result;

		if (!$this->getAuthorId())
		{
			return $result->addError(new ChatError(ChatError::WRONG_RECIPIENT));
		}

		$blockedExternalAuthId = UserTable::filterExternalUserTypes(['replica']);
		$recipient = User::getInstance($this->getAuthorId());
		if (
			!$recipient->isActive()
			|| in_array($recipient->getExternalAuthId(), $blockedExternalAuthId, true)
		)
		{
			return $result->addError(new ChatError(ChatError::WRONG_RECIPIENT));
		}

		if (
			!$message->getMessage()
			&& !$message->getParams()->isSet(Params::ATTACH)
		)
		{
			return $result->addError(new MessageError(MessageError::EMPTY_MESSAGE));
		}

		if (
			!$message->getNotifyType()
			|| !in_array($message->getNotifyType(), [\IM_NOTIFY_CONFIRM, \IM_NOTIFY_SYSTEM, \IM_NOTIFY_FROM], true)
		)
		{
			$result->addError(new MessageError(MessageError::NOTIFY_TYPE));
		}
		if (!$message->getNotifyModule())
		{
			$result->addError(new MessageError(MessageError::NOTIFY_MODULE));
		}
		if (!$message->getNotifyEvent())
		{
			$result->addError(new MessageError(MessageError::NOTIFY_EVENT));
		}
		if(
			$message->getNotifyType() === \IM_NOTIFY_CONFIRM
			&& !$message->getNotifyButtons()
		)
		{
			$result->addError(new MessageError(MessageError::NOTIFY_BUTTONS));
		}
		if(
			$message->getNotifyType() === \IM_NOTIFY_FROM
			&& !$message->getAuthorId()
		)
		{
			$result->addError(new MessageError(MessageError::WRONG_SENDER));
		}

		return $result;
	}

	/**
	 * If we have other notifications with the same tag, we need to get USERS from the old notifications,
	 * then merge it with AUTHOR_ID or create new USERS array with AUTHOR_ID then delete old notifications.
	 *
	 * @param Message $message
	 * @return void
	 */
	protected function dropOtherUserNotificationByTag(Message $message): void
	{
		if (
			$this->getChatId()
			&& $message->getAuthorId()
			&& $message->getNotifyTag()
		)
		{
			$lastMessages = MessageTable::getList([
				'select' => ['ID', 'AUTHOR_ID'],
				'filter' => [
					'=NOTIFY_TAG' => $message->getNotifyTag(),
					'=CHAT_ID' => $this->getChatId(),
				]
			]);
			$users = [];
			while ($lastMessage = $lastMessages->fetch())
			{
				$lastMessageParams = new Params();
				$lastMessageParams->loadByMessageId($lastMessage['ID']);

				if ($lastMessageParams->isSet(Params::USERS))
				{
					$users = array_merge($users, $lastMessageParams->get(Params::USERS)->getValue());
				}
				$users[] = (int)$lastMessage['AUTHOR_ID'];

				\CIMNotify::Delete($lastMessage['ID']);
			}
			$message->getParams()
				->get(Params::USERS)
				->setValue(array_unique($users))
				->unsetValue($message->getAuthorId())
			;
		}
	}

	protected function prepareConfirm(Message $message): void
	{
		if ($message->getNotifyType() == \IM_NOTIFY_CONFIRM)
		{
			if (!empty($message->getNotifyButtons()))
			{
				$buttons = $message->getNotifyButtons();
				foreach ($buttons as $index => $button)
				{
					if (
						is_array($button)
						&& !empty($button['TITLE'])
						&& !empty($button['VALUE'])
						&& !empty($button['TYPE'])
					)
					{
						$button['TITLE'] = htmlspecialcharsbx($button['TITLE']);
						$button['VALUE'] = htmlspecialcharsbx($button['VALUE']);
						$button['TYPE'] = htmlspecialcharsbx($button['TYPE']);
						$buttons[$index] = $button;
					}
					else
					{
						unset($buttons[$index]);
					}
				}
			}
			else
			{
				$buttons = [
					[
						'TITLE' => Loc::getMessage('IM_NOTIFY_CONFIRM_BUTTON_ACCEPT'),
						'VALUE' => 'Y',
						'TYPE' => 'accept'
					],
					[
						'TITLE' => Loc::getMessage('IM_NOTIFY_CONFIRM_BUTTON_CANCEL'),
						'VALUE' => 'N',
						'TYPE' => 'cancel'
					],
				];
			}

			$message->setNotifyButtons($buttons);
		}
	}

	protected function dropAllConfirmByTag(Message $message): void
	{
		if (
			$message->getNotifyType() == \IM_NOTIFY_CONFIRM
			&& !empty($message->getNotifyTag())
		)
		{
			\CIMNotify::DeleteByTag($message->getNotifyTag());
		}
	}
}
