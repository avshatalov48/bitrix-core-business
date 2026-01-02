<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Entity\Push;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Main\Entity\EntityInterface;
use Bitrix\Main\Type\Contract\Arrayable;

class Push implements EntityInterface, Arrayable
{
	private PushId $id;

	private string $channelId;

	private string $resourceId;

	private Date $expireDate;

	private ProcessingStatus $processingStatus = ProcessingStatus::Unblocked;

	private ?Date $firstPushDate = null;

	public function getId(): PushId
	{
		return $this->id;
	}

	public function setId(PushId $id): self
	{
		$this->id = $id;

		return $this;
	}

	public function getEntityType(): string
	{
		return $this->id->entityType;
	}

	public function getEntityId(): int
	{
		return $this->id->entityId;
	}

	public function getChannelId(): string
	{
		return $this->channelId;
	}

	public function setChannelId(string $channelId): self
	{
		$this->channelId = $channelId;

		return $this;
	}

	public function getResourceId(): string
	{
		return $this->resourceId;
	}

	public function setResourceId(string $resourceId): self
	{
		$this->resourceId = $resourceId;

		return $this;
	}

	public function getExpireDate(): Date
	{
		return $this->expireDate;
	}

	public function setExpireDate(Date $expireDate): self
	{
		$this->expireDate = $expireDate;

		return $this;
	}

	public function isExpired(): bool
	{
		return ((int)$this->expireDate->format('U')) < time();
	}

	public function isProcessed(): bool
	{
		return in_array(
			$this->processingStatus,
			[
				ProcessingStatus::Blocked,
				ProcessingStatus::Unprocessed,
			],
			true
		);
	}

	public function isBlocked(): bool
	{
		return $this->processingStatus === ProcessingStatus::Blocked;
	}

	public function isUnprocessed(): bool
	{
		return $this->processingStatus === ProcessingStatus::Unprocessed;
	}

	public function isUnblocked(): bool
	{
		return $this->processingStatus === ProcessingStatus::Unblocked;
	}

	public function setProcessingStatus(ProcessingStatus $processingStatus): self
	{
		$this->processingStatus = $processingStatus;

		return $this;
	}

	public function getProcessingStatus(): ProcessingStatus
	{
		return $this->processingStatus;
	}

	public function getFirstPushDate(): ?Date
	{
		return $this->firstPushDate;
	}

	public function setFirstPushDate(?Date $firstPushDate): self
	{
		$this->firstPushDate = $firstPushDate;

		return $this;
	}

	public function toArray(): array
	{
		return [
			'ENTITY_TYPE' => $this->getEntityType(),
			'ENTITY_ID' => $this->getEntityId(),
			'CHANNEL_ID' => $this->channelId,
			'RESOURCE_ID' => $this->resourceId,
			'EXPIRES' => $this->expireDate->getDate(),
			'NOT_PROCESSED' => $this->processingStatus->value,
			'FIRST_PUSH_DATE' => $this->firstPushDate?->getDate()
		];
	}
}
