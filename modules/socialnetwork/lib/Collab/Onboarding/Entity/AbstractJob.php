<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Entity;

use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Validation\Rule\PositiveNumber;
use Bitrix\Socialnetwork\Collab\Onboarding\Execution\Result\JobResult;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;
use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;

abstract class AbstractJob implements Arrayable
{
	#[PositiveNumber]
	protected int $collabId;
	#[PositiveNumber]
	protected int $userId;
	#[PositiveNumber]
	protected ?int $id;

	protected Type $type;
	protected ?DateTime $createdDate = null;
	protected DateTime $nextExecution;

	protected const SECONDS_PER_DAY = 60 * 60 * 24;

	public function __construct(int $collabId, int $userId)
	{
		$this->collabId = $collabId;
		$this->userId = $userId;
		$this->type = $this->getType();
		$this->nextExecution = $this->createNextExecutionDate();
	}

	// Can be executed immediately upon the execution date.
	// (Immediately) example: sending a system message
	// (Not immediately) example: showing the aha-moment when a person enters the chat
	abstract public function isImmediatelyExecuted(): bool;
	abstract public function getType(): Type;
	abstract protected function createNextExecutionDate(): DateTime;

	public function __invoke(): JobResult
	{
		return new JobResult();
	}

	public function getId(): ?int
	{
		return $this->id ?? null;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function getChatId(): ?int
	{
		$collab = CollabRegistry::getInstance()->get($this->collabId);

		return $collab?->getChatId();
	}

	public function getCollabId(): int
	{
		return $this->collabId;
	}

	public function getCreatedDate(): ?DateTime
	{
		return $this->createdDate;
	}

	public function getNextExecution(): DateTime
	{
		return $this->nextExecution;
	}

	public function setId(int $id): static
	{
		$this->id = $id;

		return $this;
	}

	public function setCreatedDate(DateTime $createdDate): static
	{
		$this->createdDate = $createdDate;

		return $this;
	}

	public function setNextExecution(DateTime $nextExecution): static
	{
		$this->nextExecution = $nextExecution;

		return $this;
	}

	public function toArray(): array
	{
		return [
			'collabId' => $this->getCollabId(),
			'userId' => $this->getUserId(),
			'id' => $this->getId(),
			'type' => $this->getType()->value,
			'createdDate' => $this->getCreatedDate(),
			'nextExecution' => $this->getNextExecution(),
		];
	}
}
