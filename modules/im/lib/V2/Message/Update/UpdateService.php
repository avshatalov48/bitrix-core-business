<?php

namespace Bitrix\Im\V2\Message\Update;

use Bitrix\Im\Bot;
use Bitrix\Im\Model\MessageTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Link\File\FileService;
use Bitrix\Im\V2\Link\Url\UrlService;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Delete\DeleteService;
use Bitrix\Im\V2\Message\Params;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Im\V2\Sync;

class UpdateService
{
	use ContextCustomer;

	public const EVENT_AFTER_MESSAGE_UPDATE = 'OnAfterMessagesUpdate';

	private Message $message;
	private Message\Send\SendingConfig $sendingConfig;
	private ?array $chatLastMessage = null;
	private bool $byEvent = false;
	private bool $withCheckAccess = true;


	public function __construct(Message $message)
	{
		$this->message = $message;
		$this->sendingConfig = new Message\Send\SendingConfig();
	}

	public function setMessage(Message $message): self
	{
		$this->message = $message;

		return $this;
	}

	public function setUrlPreview(bool $urlPreview): self
	{
		if (!$urlPreview)
		{
			$this->sendingConfig->disableUrlPreview();
		}

		return $this;
	}

	public function setByEvent(bool $byEvent): self
	{
		$this->byEvent = $byEvent;

		return $this;
	}

	public function withoutCheckAccess(): self
	{
		$this->withCheckAccess = false;

		return $this;
	}

	public function update(array $fieldsToUpdate): Result
	{
		if ($this->withCheckAccess && !$this->canUpdate())
		{
			return (new Result())->addError(new Message\MessageError(Message\MessageError::ACCESS_DENIED));
		}

		$this->message->fill($fieldsToUpdate);

		if ($this->message->isCompletelyEmpty())
		{
			return (new Message\Delete\DeleteService($this->message))->delete();
		}

		if ($this->message->isViewedByOthers())
		{
			$this->message->getParams()->get(Params::IS_EDITED)->setValue(true);
		}

		$filesFromText = $this->message->autocompleteParams($this->sendingConfig)->uploadFileFromText();
		$result = $this->message->save();
		if (!$result->isSuccess())
		{
			return $result;
		}

		Application::getConnection()->queryExecute("
			UPDATE b_im_recent
			SET DATE_UPDATE = NOW()
			WHERE ITEM_MID = " . $this->message->getId()
		);

		$this->message->getChat()->sendPushUpdateMessage($this->message);
		(new Message\Param\PushService())->sendPull($this->message, ['KEYBOARD', 'ATTACH', 'MENU']);

		MessageTable::indexRecord($this->message->getId());

		(new UrlService())->updateUrlsFromMessage($this->message);
		(new FileService())->saveFilesFromMessage($filesFromText, $this->message);

		$this->fireEventAfterMessageUpdate();

		return $result;
	}

	public function canUpdate(): bool
	{
		$isMessageDelete = $this->message->getParams()->get(Params::IS_DELETED)->getValue() === true;
		$isForward = $this->message->getParams()->isSet(Params::FORWARD_ID);

		if ($isMessageDelete || $isForward)
		{
			return false;
		}

		$user = $this->getContext()->getUser();
		$chat = $this->message->getChat();

		if ($chat instanceof Chat\OpenLineChat && Loader::includeModule('imopenlines'))
		{
			if ($user->isBot())
			{
				return true;
			}

			if ($user->getId() === $this->message->getAuthorId())
			{
				return $chat->canUpdateOwnMessage();
			}

			return false;
		}

		if ($this->message->getAuthorId() === $user->getId())
		{
			return true;
		}

		return false;
	}

	private function getBotInChat(): array
	{
		$result = [];
		$users = $this->message->getChat()->getRelations()->getUsers();

		foreach ($users as $user)
		{
			if ($user->isBot())
			{
				$result[$user->getId()] = $user->getId();
			}
		}

		return $result;
	}

	private function fireEventAfterMessageUpdate(): void
	{
		$chat = $this->message->getChat();
		$messageFields = [
			'ID' => $this->message->getId(),
			'CHAT_ID' => $this->message->getChatId(),
			'AUTHOR_ID' => $this->message->getAuthorId(),
			'MESSAGE' => $this->message->getMessage(),
			'MESSAGE_OUT' => $this->message->getMessageOut(),
			'DATE_CREATE' => $this->message->getDateCreate()->getTimestamp(),
			'EMAIL_TEMPLATE' => $this->message->getEmailTemplate(),
			'NOTIFY_TYPE' => $this->message->getNotifyType(),
			'NOTIFY_MODULE' => $this->message->getNotifyModule(),
			'NOTIFY_EVENT' => $this->message->getNotifyEvent(),
			'NOTIFY_TAG' => $this->message->getNotifyTag(),
			'NOTIFY_SUB_TAG' => $this->message->getNotifySubTag(),
			'NOTIFY_TITLE' => $this->message->getNotifyTitle(),
			'NOTIFY_BUTTONS' => $this->message->getNotifyButtons(),
			'NOTIFY_READ' => $this->message->isNotifyRead(),
			'IMPORT_ID' => $this->message->getImportId(),
			'MESSAGE_TYPE' => $chat->getType(),
			'CHAT_AUTHOR_ID' => $chat->getAuthorId(),
			'CHAT_ENTITY_TYPE' => $chat->getEntityType(),
			'CHAT_ENTITY_ID' => $chat->getEntityId(),
			'CHAT_PARENT_ID' => $chat->getParentChatId(),
			'CHAT_PARENT_MID' => $chat->getParentMessageId(),
			'CHAT_ENTITY_DATA_1' => $chat->getEntityData1(),
			'CHAT_ENTITY_DATA_2' => $chat->getEntityData2(),
			'CHAT_ENTITY_DATA_3' => $chat->getEntityData3(),
			'PARAMS' => $this->message->getParams()->toRestFormat(),
			'DATE_MODIFY' => new DateTime()
		];

		if ($chat instanceof Chat\PrivateChat)
		{
			$messageFields['FROM_USER_ID'] = $this->message->getAuthorId();
			$messageFields['TO_USER_ID'] = $chat->getCompanion($this->message->getAuthorId())->getId();
		}
		else
		{
			$messageFields['BOT_IN_CHAT'] = $this->getBotInChat();
		}

		$updateFlags = [
			'ID' => $this->message->getId(),
			'TEXT' => $this->message->getMessage(),
			'URL_PREVIEW' => $this->urlPreview,
			'EDIT_FLAG' => $this->message->getParams()->get(Params::IS_EDITED)->getValue(),
			'USER_ID' => $this->message->getAuthorId(),
			'BY_EVENT' => $this->byEvent,
		];

		foreach(GetModuleEvents('im', self::EVENT_AFTER_MESSAGE_UPDATE, true) as $event)
		{
			ExecuteModuleEventEx($event, [$this->message->getId(), $messageFields, $updateFlags]);
		}

		Bot::onMessageUpdate($this->message->getId(), $messageFields);

		Sync\Logger::getInstance()->add(
			new Sync\Event(Sync\Event::ADD_EVENT, Sync\Event::UPDATED_MESSAGE_ENTITY, $this->message->getId()),
			static fn () => $chat->getRelations()->getUserIds(),
			$chat->getType()
		);
	}
}
