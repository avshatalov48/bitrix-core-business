<?php

namespace Bitrix\Im\V2\Recent\Initializer\Queue;

use Bitrix\Im\Model\RecentInitQueueTable;
use Bitrix\Im\V2\Recent\Initializer\SourceType;
use Bitrix\Im\V2\Recent\Initializer\StageType;
use Bitrix\Main\Type\DateTime;

class QueueItem
{
	public function __construct(
		public readonly int $id,
		public readonly int $userId,
		public readonly StageType $stageType,
		public readonly SourceType $sourceType,
		public readonly ?int $sourceId,
		public readonly string $pointer,
		public readonly string $status,
		public readonly bool $isLocked,
		public readonly DateTime $dateCreate,
		public readonly DateTime $dateUpdate,
		public readonly bool $isFirstInit
	){}

	public static function createFromRow(array $row): static
	{
		return new static(
			(int)$row['ID'],
			(int)$row['USER_ID'],
			StageType::tryFrom($row['STAGE'] ?? null) ?? StageType::Target,
			SourceType::tryFrom($row['SOURCE'] ?? null) ?? SourceType::Collab,
			isset($row['SOURCE_ID']) ? (int)$row['SOURCE_ID'] : null,
			$row['POINTER'],
			$row['STATUS'],
			$row['IS_LOCKED'] === 'Y',
			$row['DATE_CREATE'],
			$row['DATE_UPDATE'],
			false,
		);
	}

	public static function createFirstStep(int $userId, SourceType $sourceType, ?int $sourceId, bool $isFirstInit = false): static
	{
		$currentDate = new DateTime();

		return new static(
			0,
			$userId,
			StageType::getFirst(),
			$sourceType,
			$sourceId,
			'',
			'',
			false,
			$currentDate,
			$currentDate,
			$isFirstInit
		);
	}

	public function setId(int $id): self
	{
		return $this->copy(id: $id);
	}

	public function lock(): self
	{
		RecentInitQueueTable::update($this->id, ['IS_LOCKED' => true, 'DATE_UPDATE' => new DateTime()]);

		return $this->copy(isLocked: true);
	}

	public function unlock(bool $withSave = false): self
	{
		if ($withSave)
		{
			RecentInitQueueTable::update($this->id, ['IS_LOCKED' => false, 'DATE_UPDATE' => new DateTime()]);
		}

		return $this->copy(isLocked: false);
	}

	public function setErrorStatus(): self
	{
		return $this->copy(status: 'ERROR', dateUpdate: new DateTime());
	}

	public function updatePointer(string $pointer, ?StageType $stage = null): self
	{
		return $this->copy(stageType: $stage, pointer: $pointer, status: '', dateUpdate: new DateTime());
	}

	public function getFields(): array
	{
		return [
			'ID' => $this->id,
			'USER_ID' => $this->userId,
			'STAGE' => $this->stageType->value,
			'SOURCE' => $this->sourceType->value,
			'SOURCE_ID' => $this->sourceId,
			'POINTER' => $this->pointer,
			'STATUS' => $this->status,
			'IS_LOCKED' => $this->isLocked,
			'DATE_CREATE' => $this->dateCreate,
			'DATE_UPDATE' => $this->dateUpdate,
		];
	}

	protected function copy(
		?int $id = null,
		?int $userId = null,
		?StageType $stageType = null,
		?SourceType $sourceType = null,
		?int $sourceId = null,
		?string $pointer = null,
		?string $status = null,
		?bool $isLocked = null,
		?DateTime $dateCreate = null,
		?DateTime $dateUpdate = null,
		?bool $isFirstInit = null,
	): self
	{
		return new static(
			$id ?? $this->id,
			$userId ?? $this->userId,
			$stageType ?? $this->stageType,
			$sourceType ?? $this->sourceType,
			$sourceId ?? $this->sourceId,
			$pointer ?? $this->pointer,
			$status ?? $this->status,
			$isLocked ?? $this->isLocked,
			$dateCreate ?? $this->dateCreate,
			$dateUpdate ?? $this->dateUpdate,
			$isFirstInit ?? $this->isFirstInit,
		);
	}
}
