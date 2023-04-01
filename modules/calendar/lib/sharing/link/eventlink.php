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
	private ?string $userLinkHash = null;

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
	 * @param int $hostId
	 * @return $this
	 */
	public function setHostId(int $hostId): self
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
	 * @param string $conferenceId
	 * @return $this
	 */
	public function setConferenceId(string $conferenceId): self
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
	public function getUserLinkHash(): ?string
	{
		return $this->userLinkHash;
	}

	/**
	 * @param string|null $userLinkHash
	 * @return EventLink
	 */
	public function setUserLinkHash(?string $userLinkHash): self
	{
		$this->userLinkHash = $userLinkHash;

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
	 * @return string
	 */
	public function getObjectType(): string
	{
		return 'event';
	}
}