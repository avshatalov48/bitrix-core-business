<?php
namespace Bitrix\Calendar\Sharing\Link;

class UserLink extends Link
{
	private int $slotSize;

	public function setSlotSize(int $minutes): self
	{
		$this->slotSize = $minutes;

		return $this;
	}

	public function getSlotSize(): int
	{
		return $this->slotSize;
	}

	public function setUserId(int $id): self
	{
		return $this->setObjectId($id);
	}

	public function getUserId(): int
	{
		return $this->getObjectId();
	}

	public function getObjectType(): string
	{
		return 'user';
	}
}