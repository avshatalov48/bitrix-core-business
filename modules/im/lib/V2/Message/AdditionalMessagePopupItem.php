<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\PopupDataItem;

class AdditionalMessagePopupItem implements PopupDataItem, PopupDataAggregatable
{
	/**
	 * @var int[]
	 */
	private array $messageIds;
	private ?MessageCollection $messages = null;

	public function __construct(array $messageIds)
	{
		$this->messageIds = array_unique($messageIds);
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
		return 'additionalMessages';
	}

	public function toRestFormat(array $option = []): array
	{
		return $this->getMessages()->toRestFormat($option);
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		return $this->getMessages()->getPopupData($excludedList);
	}

	private function getMessages(): MessageCollection
	{
		$this->messages ??= new MessageCollection(array_unique($this->messageIds));

		return $this->messages;
	}
}