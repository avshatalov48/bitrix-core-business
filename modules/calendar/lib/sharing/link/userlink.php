<?php
namespace Bitrix\Calendar\Sharing\Link;

use Bitrix\Calendar\Sharing\Link\Joint\JointLink;

class UserLink extends JointLink
{
	private int $slotSize = 60;
	protected ?Rule\Rule $sharingRule = null;

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

	public function setSlotSize(int $minutes): static
	{
		$this->slotSize = $minutes;

		return $this;
	}

	public function setUserId(int $id): static
	{
		return $this->setObjectId($id);
	}

	public function getSharingRule(): ?Rule\Rule
	{
		return $this->sharingRule;
	}

	public function setSharingRule(?Rule\Rule $sharingRule): self
	{
		$this->sharingRule = $sharingRule;

		return $this;
	}

	public function getOwnerId(): int
	{
		return $this->getObjectId();
	}
}