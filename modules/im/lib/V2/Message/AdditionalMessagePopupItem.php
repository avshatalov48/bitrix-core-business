<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Rest\PopupDataItem;

class AdditionalMessagePopupItem implements PopupDataItem
{
	/**
	 * @var int[]
	 */
	private array $messageIds;

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
		return (new MessageCollection(array_unique($this->messageIds)))->toRestFormat($option);
	}
}