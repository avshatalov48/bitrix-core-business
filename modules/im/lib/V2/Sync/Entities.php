<?php

namespace Bitrix\Im\V2\Sync;

use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Rest\RestAdapter;
use Bitrix\Im\V2\Sync\Entity\Chats;
use Bitrix\Im\V2\Sync\Entity\DialogIds;
use Bitrix\Im\V2\Sync\Entity\Messages;
use Bitrix\Im\V2\Sync\Entity\PinMessages;
use Bitrix\Im\V2\Chat\Comment\CommentPopupItem;
use Bitrix\Im\V2\TariffLimit\TariffLimitPopupItem;

class Entities
{
	protected array $rest = [];

	public function __construct(
		protected Chats $chats,
		protected Messages $messages,
		protected PinMessages $pinMessages,
		protected DialogIds $dialogIds,
	)
	{}

	public function getRestData(): array
	{
		$this->loadRestData();
		$this->rest['dialogIds'] = $this->fillRestDialogData();

		return $this->rest;
	}

	protected function loadRestData(): void
	{
		$this->rest[Chats::getRestEntityName()] = $this->chats->toRestFormat();
		$this->rest[Messages::getRestEntityName()] = $this->messages->toRestFormat();
		$this->rest[PinMessages::getRestEntityName()] = $this->pinMessages->toRestFormat();

		$messageCollection = $this->getMessageCollection();
		$pinCollection = $this->pinMessages->getPinCollection();
		$chatItems = $this->chats->getChatItems();

		$restData = (new RestAdapter($messageCollection, $pinCollection, $chatItems))->toRestFormat($this->getOption());
		$this->rest = array_merge($this->rest, $restData);
	}

	protected function getOption(): array
	{
		return [
			'POPUP_DATA_EXCLUDE' => [CommentPopupItem::class, TariffLimitPopupItem::class]
		];
	}

	protected function getMessageCollection(): MessageCollection
	{
		$messageIds = $this->messages->getMessageIds() + $this->chats->getMessageIds();

		return (new MessageCollection($messageIds));
	}

	protected function fillRestDialogData(): array
	{
		return $this->dialogIds->load($this->rest)->toRestFormat();
	}
}
