<?php

namespace Bitrix\Im\V2\Entity\User;

use Bitrix\Im\V2\Rest\PopupDataItem;

class UserPopupItem implements PopupDataItem
{
	/**
	 * @var int[]
	 */
	private array $userIds;

	public function __construct(array $userIds = [])
	{
		$this->userIds = array_unique($userIds);
	}

	public function merge(PopupDataItem $item): self
	{
		if ($item instanceof self)
		{
			$this->userIds = array_unique(array_merge($this->userIds, $item->userIds));
		}

		return $this;
	}

	public static function getRestEntityName(): string
	{
		return UserCollection::getRestEntityName();
	}

	public function toRestFormat(array $option = []): array
	{
		return (new UserCollection(array_unique($this->userIds)))->getUnique()->toRestFormat($option);
	}
}