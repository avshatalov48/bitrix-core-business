<?php

namespace Bitrix\Calendar\Sharing\Link;

class CrmDealLink extends Link
{
	private int $slotSize = 60;
	private int $ownerId;
	private ?int $contactId = null;
	private ?int $contactType = null;

	public function getObjectType(): string
	{
		return Helper::CRM_DEAL_SHARING_TYPE;
	}

	public function getSlotSize(): int
	{
		return $this->slotSize;
	}

	public function getEntityId(): int
	{
		return $this->getObjectId();
	}

	public function getContactId(): ?int
	{
		return $this->contactId;
	}

	public function getContactType(): ?int
	{
		return $this->contactType;
	}

	public function getOwnerId(): int
	{
		return $this->ownerId;
	}

	public function setSlotSize(int $minutes): self
	{
		$this->slotSize = $minutes;

		return $this;
	}

	public function setEntityId(int $id): self
	{
		return $this->setObjectId($id);
	}

	public function setContactId(?int $contactId): self
	{
		$this->contactId = $contactId;

		return $this;
	}

	public function setContactType(?int $contactType): self
	{
		$this->contactType = $contactType;

		return $this;
	}

	public function setOwnerId(int $ownerId): self
	{
		$this->ownerId = $ownerId;

		return $this;
	}
}