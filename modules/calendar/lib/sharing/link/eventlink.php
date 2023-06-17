<?php
namespace Bitrix\Calendar\Sharing\Link;

class EventLink extends Link
{
	/** @var int|null  */
	private ?int $hostId = null;
	/** @var int|null  */
	private ?int $ownerId = null;
	/** @var string|null  */
	private ?string $conferenceId = null;
	/** @var string|null  */
	private ?string $parentLinkHash = null;
	/** @var int|null  */
	private ?int $canceledTimestamp = null;
	/** @var string|null  */
	private ?string $externalUserName = null;

	/**
	 * @param int $ownerId
	 * @return $this
	 */
	public function setOwnerId(int $ownerId): self
	{
		$this->ownerId = $ownerId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getOwnerId(): ?int
	{
		return $this->ownerId ?? null;
	}

	/**
	 * @param int|null $hostId
	 * @return $this
	 */
	public function setHostId(?int $hostId): self
	{
		$this->hostId = $hostId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getHostId(): ?int
	{
		return $this->hostId ?? null;
	}

	/**
	 * @param string|null $conferenceId
	 * @return $this
	 */
	public function setConferenceId(?string $conferenceId): self
	{
		$this->conferenceId = $conferenceId;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getConferenceId(): ?string
	{
		return $this->conferenceId;
	}

	/**
	 * @return string|null
	 */
	public function getParentLinkHash(): ?string
	{
		return $this->parentLinkHash;
	}

	/**
	 * @param string|null $parentLinkHash
	 * @return EventLink
	 */
	public function setParentLinkHash(?string $parentLinkHash): self
	{
		$this->parentLinkHash = $parentLinkHash;

		return $this;
	}

	/**
	 * @param int $id
	 * @return $this
	 */
	public function setEventId(int $id): self
	{
		return $this->setObjectId($id);
	}

	/**
	 * @return int
	 */
	public function getEventId(): int
	{
		return $this->getObjectId();
	}

	/**
	 * @param int $timestamp
	 * @return $this
	 */
	public function setCanceledTimestamp(?int $timestamp): self
	{
		$this->canceledTimestamp = $timestamp;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getCanceledTimestamp(): ?int
	{
		return $this->canceledTimestamp;
	}

	/**
	 * @param string|null $name
	 * @return $this
	 */
	public function setExternalUserName(?string $name)
	{
		$this->externalUserName = $name;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getExternalUserName()
	{
		return $this->externalUserName;
	}

	/**
	 * @return string
	 */
	public function getObjectType(): string
	{
		return Helper::EVENT_SHARING_TYPE;
	}
}