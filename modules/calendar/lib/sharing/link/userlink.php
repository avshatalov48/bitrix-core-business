<?php
namespace Bitrix\Calendar\Sharing\Link;

class UserLink extends Link
{
	private int $slotSize = 60;

	public function getObjectType(): string
	{
		return Helper::USER_SHARING_TYPE;
	}

	public function getSlotSize(): int
	{
		return $this->slotSize;
	}

	public function getUserId(): int
	{
		return $this->getObjectId();
	}

	public function setSlotSize(int $minutes): self
	{
		$this->slotSize = $minutes;

		return $this;
	}

	public function setUserId(int $id): self
	{
		return $this->setObjectId($id);
	}
}