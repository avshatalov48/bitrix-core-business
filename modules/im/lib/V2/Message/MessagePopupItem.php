<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\V2\Chat\Comment\CommentPopupItem;
use Bitrix\Im\V2\Message\Reaction\ReactionPopupItem;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\PopupDataItem;

class MessagePopupItem implements PopupDataItem, PopupDataAggregatable
{
	/**
	 * @var int[]
	 */
	private array $messageIds;
	private ?MessageCollection $messages = null;
	private bool $shortInfo;

	public function __construct(array $messageIds, bool $shortInfo = false)
	{
		$this->messageIds = array_unique($messageIds);
		$this->shortInfo = $shortInfo;
	}

	public function merge(PopupDataItem $item): PopupDataItem
	{
		if ($item instanceof self)
		{
			$this->messageIds = array_unique(array_merge($this->messageIds, $item->messageIds));
		}

		return $this;
	}

	public static function getRestEntityName(): string
	{
		return 'messages';
	}

	public function toRestFormat(array $option = []): array
	{
		$option['MESSAGE_SHORT_INFO'] = $this->shortInfo;

		return $this->getMessages()->toRestFormat($option);
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		if ($this->shortInfo)
		{
			$excludedList[] = ReactionPopupItem::class;
			$excludedList[] = CommentPopupItem::class;
		}

		return $this->getMessages()->getPopupData($excludedList);
	}

	private function getMessages(): MessageCollection
	{
		$this->messages ??= new MessageCollection(array_unique($this->messageIds));

		return $this->messages;
	}
}