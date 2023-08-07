<?php

namespace Bitrix\Im\V2\Message\Reaction;

use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\PopupDataItem;

class ReactionPopupItem implements PopupDataItem, PopupDataAggregatable
{
	private ReactionMessages $reactions;

	/**
	 * @param ReactionMessage|ReactionMessages|null $reactions
	 */
	public function __construct($reactions = null)
	{
		$reactions ??= new ReactionMessages([], false);

		if ($reactions instanceof ReactionMessages)
		{
			$this->reactions = $reactions;
		}
		if ($reactions instanceof ReactionMessage)
		{
			$this->reactions = ReactionMessages::initFromArray([$reactions]);
		}
	}

	public function merge(PopupDataItem $item): self
	{
		if ($item instanceof self)
		{
			foreach ($item->reactions as $reaction)
			{
				$this->reactions->addReactionMessage($reaction);
			}
		}

		return $this;
	}

	public static function getRestEntityName(): string
	{
		return 'reactions';
	}

	public function toRestFormat(array $option = []): array
	{
		return $this->reactions->toRestFormat($option);
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		return $this->reactions->getPopupData($excludedList);
	}
}