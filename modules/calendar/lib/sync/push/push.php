<?php

namespace Bitrix\Calendar\Sync\Push;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Sync\Dictionary;

class Push
{
	/**
	 * @var string
	 */
	private string $entityType;
	/**
	 * @var int
	 */
	private int $entityId;
	/**
	 * @var string
	 */
	private string $channelId;
	/**
	 * @var string
	 */
	private string $resourceId;
	/**
	 * @var Date
	 */
	private Date $expireDate;
	/**
	 * @var string|null
	 */
	private ?string $processStatus = null;
	/**
	 * @var ?Date
	 */
	private ?Date $firstPushDate = null;

	/**
	 * @return string
	 */
	public function getEntityType(): string
	{
		return $this->entityType;
	}

	/**
	 * @param string $entityType
	 * @return Push
	 */
	public function setEntityType(string $entityType): Push
	{
		$this->entityType = $entityType;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getEntityId(): int
	{
		return $this->entityId;
	}

	/**
	 * @param int $entityId
	 * @return Push
	 */
	public function setEntityId(int $entityId): Push
	{
		$this->entityId = $entityId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getChannelId(): string
	{
		return $this->channelId;
	}

	/**
	 * @param string $channelId
	 * @return Push
	 */
	public function setChannelId(string $channelId): Push
	{
		$this->channelId = $channelId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getResourceId(): string
	{
		return $this->resourceId;
	}

	/**
	 * @param string $resourceId
	 * @return Push
	 */
	public function setResourceId(string $resourceId): Push
	{
		$this->resourceId = $resourceId;
		return $this;
	}

	/**
	 * @return Date
	 */
	public function getExpireDate(): Date
	{
		return $this->expireDate;
	}

	/**
	 * @param Date $expireDate
	 * @return Push
	 */
	public function setExpireDate(Date $expireDate): Push
	{
		$this->expireDate = $expireDate;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isExpired(): bool
	{
		return ((int)$this->expireDate->format('U')) < time();
	}

	/**
	 * @return bool
	 */
	public function isProcessed(): bool
	{
		return in_array(
			$this->processStatus,
			[
				Dictionary::PUSH_STATUS_PROCESS['block'],
				Dictionary::PUSH_STATUS_PROCESS['unprocessed'],
			],
			true
		);
	}

	/**
	 * @return bool
	 */
	public function isBlocked(): bool
	{
		return $this->processStatus === Dictionary::PUSH_STATUS_PROCESS['block'];
	}

	/**
	 * @return bool
	 */
	public function isUnprocessed(): bool
	{
		return $this->processStatus === Dictionary::PUSH_STATUS_PROCESS['unprocessed'];
	}

	/**
	 * @return bool
	 */
	public function isUnblocked(): bool
	{
		return $this->processStatus === Dictionary::PUSH_STATUS_PROCESS['unblocked'];
	}

	/**
	 * @param bool $processed
	 * @return Push
	 */
	public function setProcessStatus(string $processStatus): Push
	{
		if (in_array($processStatus, Dictionary::PUSH_STATUS_PROCESS, true))
		{
			$this->processStatus = $processStatus;
		}

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getProcessStatus(): ?string
	{
		return $this->processStatus;
	}

	/**
	 * @return Date|null
	 */
	public function getFirstPushDate(): ?Date
	{
		return $this->firstPushDate;
	}

	/**
	 * @param Date|null $firstPushDate
	 * @return Push
	 */
	public function setFirstPushDate(?Date $firstPushDate): Push
	{
		$this->firstPushDate = $firstPushDate;
		return $this;
	}
}
