<?php

namespace Bitrix\Im\V2\Message\Update;

use Bitrix\Im\Bot;
use Bitrix\Im\Common;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\Text;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Link\Url\UrlService;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Params;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Application;
use Bitrix\Main\Type\DateTime;
use Bitrix\Pull\Event;

class UpdateService
{
	use ContextCustomer;

	public const EVENT_AFTER_MESSAGE_UPDATE = 'OnAfterMessagesUpdate';

	private Message $message;
	private ?array $chatLastMessage = null;
	private bool $urlPreview = true;
	private bool $byEvent = false;


	public function __construct(Message $message)
	{
		$this->message = $message;
	}

	public function setMessage(Message $message): self
	{
		$this->message = $message;

		return $this;
	}

	public function setUrlPreview(bool $urlPreview): self
	{
		$this->urlPreview = $urlPreview;

		return $this;
	}

	public function setByEvent(bool $byEvent): self
	{
		$this->byEvent = $byEvent;

		return $this;
	}

	public function update(?string $messageText): Result
	{
		if (!$this->canUpdate())
		{
			return (new Result())->addError(new Message\MessageError(Message\MessageError::MESSAGE_ACCESS_ERROR));
		}

		if (!$messageText)
		{
			$deleteService = new Message\Delete\DeleteService($this->message);
			return $deleteService->delete();
		}

		$this->message->setMessage($messageText);

		$result = $this->message->save();
		if (!$result->isSuccess())
		{
			return $result;
		}

		$this->updateParams();

		(Application::getConnection())->queryExecute("
			UPDATE b_im_recent
			SET DATE_UPDATE = NOW()
			WHERE ITEM_MID = " . $this->message->getId()
		);

		MessageTable::indexRecord($this->message->getId());

		(new UrlService())->updateUrlsFromMessage($this->message);

		$this->sendEvents();

		return $result;
	}

	private function updateParams(): void
	{
		$this->message->getParams()->get(Params::URL_ID)->unsetValue();
		$this->message->getParams()->get(Params::URL_ONLY)->unsetValue();
		$this->message->getParams()->get(Params::LARGE_FONT)->unsetValue();
		$this->message->getParams()->get(Params::DATE_TEXT)->unsetValue();
		$this->message->getParams()->get(Params::DATE_TS)->unsetValue();

		if ($this->message->isViewedByOthers())
		{
			$this->message->getParams()->get(Params::IS_EDITED)->setValue(true);
		}

		if (Text::isOnlyEmoji($this->message->getMessage()))
		{
			$this->message->getParams()->get(Params::LARGE_FONT)->setValue(true);
		}

		if ($this->urlPreview)
		{
			$results = Text::getDateConverterParams($this->message->getMessage());
			foreach ($results as $result)
			{
				$dateText = $result->getText();
				$dateTs = $result->getDate()->getTimestamp();
			}

			$link = new \CIMMessageLink();
			$urlPrepare = $link->prepareInsert($this->message->getMessage());
			if ($urlPrepare['RESULT'])
			{
				if ($urlPrepare['URL_ID'])
				{
					$this->message->getParams()->get(Params::URL_ID)->setValue($urlPrepare['URL_ID']);
				}
				if ($urlPrepare['MESSAGE_IS_LINK'])
				{
					$this->message->getParams()->get(Params::URL_ONLY)->setValue(true);
				}

				$this->message->getParams()->get(Params::DATE_TEXT)->setValue($dateText);
				$this->message->getParams()->get(Params::DATE_TS)->setValue($dateTs);
			}

		}

		$this->message->getParams()->save();
		$this->message->save();
	}

	private function sendEvents()
	{
		$pullMessage = [
			'id' => (int)$this->message->getId(),
			'type' => $this->message->getChat()->getType() == Chat::IM_TYPE_PRIVATE ? 'private' : 'chat',
			'text' => Text::parse($this->message->getMessage()),
			'params' => $this->message->getParams()->toRestFormat(),
		];

		$relations = $this->message->getChat()->getRelations();
		$relationIds = [];
		$botInChat = [];

		if ($pullMessage['type'] == Chat::IM_TYPE_PRIVATE)
		{
			foreach ($relations as $relation)
			{
				$relationIds[] = $relation->getUserId();
				if ($relation->getUserId() !== $this->message->getAuthorId())
				{
					$recipientId = $relation->getUserId();
				}
			}

			$pullMessage['fromUserId'] = $this->message->getAuthorId();
			$pullMessage['toUserId'] = $recipientId;
			$pullMessage['senderId'] = $this->message->getAuthorId();
			$pullMessage['chatId'] = $this->message->getChatId();
		}
		else
		{
			$pullMessage['chatId'] = $this->message->getChatId();
			$pullMessage['dialogId'] = 'chat' . $pullMessage['chatId'];
			$pullMessage['senderId'] = $this->message->getAuthorId();

			foreach ($relations as $relation)
			{
				$relationIds[] = $relation->getUserId();
				if ($this->message->getChat()->getEntityType() === Chat::ENTITY_TYPE_LINE)
				{
					if ($relation->getUser()->getExternalAuthId() === 'imconnector')
					{
						unset($relationIds[$relation->getUserId()]);
						continue;
					}
				}
				if ($relation->getUser()->getExternalAuthId() === Bot::EXTERNAL_AUTH_ID)
				{
					$botInChat[$relation->getUserId()] = $relation->getUserId();
					unset($relationIds[$relation->getUserId()]);
				}
			}
		}

		if ($pullMessage['type'] == Chat::IM_TYPE_PRIVATE)
		{
			Event::add($pullMessage['toUserId'], [
				'module_id' => 'im',
				'command' => 'messageUpdate',
				'params' => $pullMessage,
				'extra' => Common::getPullExtra()
			]);

			$pullMessage['fromUserId'] = $pullMessage['toUserId'];
			$pullMessage['toUserId'] = $pullMessage['fromUserId'];
			$pullMessage['senderId'] = $pullMessage['toUserId'];

			Event::add($pullMessage['toUserId'], [
				'module_id' => 'im',
				'command' => 'messageUpdate',
				'params' => $pullMessage,
				'extra' => Common::getPullExtra()
			]);
		}
		else
		{
			Event::add($relationIds, [
				'module_id' => 'im',
				'command' => 'messageUpdate',
				'params' => $pullMessage,
				'extra' => Common::getPullExtra()
			]);
		}

		if (in_array($this->message->getChat()->getType(), [IM_MESSAGE_OPEN, IM_MESSAGE_OPEN_LINE], true))
		{
			\CPullWatch::AddToStack('IM_PUBLIC_' . $this->message->getChatId(), [
				'module_id' => 'im',
				'command' => 'messageUpdate',
				'params' => $pullMessage,
				'extra' => Common::getPullExtra()
			]);
		}

		$this->fireEventAfterMessageUpdate($botInChat);
	}

	public function canUpdate(): bool
	{
		global $USER;
		if ($USER->IsAdmin())
		{
			return true;
		}

		$chat = $this->message->getChat();
		$userId = $this->getContext()->getUserId();

		if (!$chat->hasAccess($userId))
		{
			return false;
		}

		if ($this->message->getAuthorId() === $userId)
		{
			return true;
		}

		return false;
	}

	private function fireEventAfterMessageUpdate(array $botInChat = []): Result
	{
		$result = new Result;

		$messageFields = $this->message->toArray();

		$param = \CIMMessageParam::Get($messageFields['ID']);
		$messageFields['PARAMS'] = $param ?: [];
		if ($messageFields && ($params['WITH_FILES'] ?? null) === 'Y')
		{
			$files = [];
			if (isset($messageFields['PARAMS']['FILE_ID']))
			{
				foreach ($messageFields['PARAMS']['FILE_ID'] as $fileId)
				{
					$files[$fileId] = $fileId;
				}
			}
			$messageFields['FILES'] = \CIMDisk::GetFiles($messageFields['CHAT_ID'], $files, false);
		}

		$messageFields['DATE_MODIFY'] = new DateTime();
		if ($this->message->getChat()->getType() != Chat::IM_TYPE_PRIVATE)
		{
			$messageFields['BOT_IN_CHAT'] = $botInChat;
		}

		foreach(GetModuleEvents('im', self::EVENT_AFTER_MESSAGE_UPDATE, true) as $event)
		{
			$updateFlags = [
				'ID' => $this->message->getId(),
				'TEXT' => $this->message->getMessage(),
				'URL_PREVIEW' => $this->urlPreview,
				'EDIT_FLAG' => $this->message->isViewedByOthers(),
				'USER_ID' => $this->getContext()->getUserId(),
				'BY_EVENT' => false,
			];

			ExecuteModuleEventEx($event, [$this->message->getId(), $messageFields, $updateFlags]);

			Bot::onMessageUpdate($this->message->getId(), $messageFields);
		}

		return $result;
	}
}
