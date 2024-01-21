<?php

namespace Bitrix\Calendar\Sharing\Link;

use Bitrix\Calendar\Sharing\Link\Joint\JointLink;

class CrmDealLink extends JointLink
{
	/** @var int $slotSize */
	private int $slotSize = 60;
	/** @var int $ownerId */
	private int $ownerId;
	/** @var int|null $contactId */
	private ?int $contactId = null;
	/** @var int|null $contactType */
	private ?int $contactType = null;
	/** @var string|null $channelId */
	private ?string $channelId = null;
	/** @var string|null $senderId */
	private ?string $senderId = null;
	/** @var string|null $lastStatus */
	private ?string $lastStatus = null;
	/** @var Rule\Rule|null $sharingRule */
	protected ?Rule\Rule $sharingRule = null;

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

	public function getChannelId(): ?string
	{
		return $this->channelId;
	}

	public function getSenderId(): ?string
	{
		return $this->senderId;
	}

	public function getLastStatus(): ?string
	{
		return $this->lastStatus;
	}

	public function setSlotSize(int $minutes): static
	{
		$this->slotSize = $minutes;

		return $this;
	}

	public function setEntityId(int $id): static
	{
		return $this->setObjectId($id);
	}

	public function setContactId(?int $contactId): static
	{
		$this->contactId = $contactId;

		return $this;
	}

	public function setContactType(?int $contactType): static
	{
		$this->contactType = $contactType;

		return $this;
	}

	public function setOwnerId(int $ownerId): static
	{
		$this->ownerId = $ownerId;

		return $this;
	}

	public function setChannelId(?string $channelId): static
	{
		$this->channelId = $channelId;

		return $this;
	}

	public function setSenderId(?string $senderId): static
	{
		$this->senderId = $senderId;

		return $this;
	}

	public function setLastStatus(?string $lastStatus): static
	{
		$this->lastStatus = $lastStatus;

		return $this;
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
}