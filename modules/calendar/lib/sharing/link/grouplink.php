<?php

namespace Bitrix\Calendar\Sharing\Link;

use Bitrix\Calendar\Sharing\Link\Joint\JointLink;

final class GroupLink extends JointLink
{
	private ?int $hostId = null;
	private int $slotSize = 60;
	protected ?Rule\Rule $sharingRule = null;

	public function getOwnerId(): int
	{
		return $this->getObjectId();
	}

	public function setGroupId(int $id): static
	{
		$this->setObjectId($id);

		return $this;
	}

	public function getGroupId(): int
	{
		return $this->getObjectId();
	}

	public function setHostId(int $hostId): self
	{
		$this->hostId = $hostId;

		return $this;
	}

	public function getHostId(): ?int
	{
		return $this->hostId ?? null;
	}

	public function getObjectType(): string
	{
		return Helper::GROUP_SHARING_TYPE;
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

	public function getSlotSize(): int
	{
		return $this->slotSize;
	}

	public function setSlotSize(int $minutes): static
	{
		$this->slotSize = $minutes;

		return $this;
	}
}
