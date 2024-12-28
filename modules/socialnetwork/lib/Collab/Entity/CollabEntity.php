<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Entity;

use Bitrix\Main\ObjectException;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Entity\Type\EntityType;
use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;

/**
 * Describe an entity from collab
 */
abstract class CollabEntity
{
	protected CollabRegistry $collabRegistry;
	protected Collab $collab;

	protected int $id;

	/**
	 * @throws ObjectException
	 */
	public function __construct(int $id, mixed $internalObject = null)
	{
		if ($id <= 0)
		{
			throw new ObjectException("Entity {$this->getType()->value}:{$this->id} doesn't exist");
		}

		$this->id = $id;

		$this->init();

		if (!$this->checkInternalEntity())
		{
			throw new ObjectException("Entity {$this->getType()->value}:{$this->id} not found");
		}

		$collab = $this->fillCollab();
		if ($collab === null)
		{
			throw new ObjectException("Entity {$this->getType()->value}:{$this->id} is not in collab");
		}

		$this->collab = $collab;
	}

	abstract public function getType(): EntityType;

	abstract public function getData(): array;

	abstract protected function fillCollab(): ?Collab;

	abstract protected function checkInternalEntity(): bool;

	public function getCollab(): Collab
	{
		return $this->collab;
	}

	public function getId(): int
	{
		return $this->id;
	}

	protected function init(): void
	{
		$this->collabRegistry = CollabRegistry::getInstance();
	}
}