<?php

namespace Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem;

use Bitrix\Main\Type\Contract\Arrayable;
use JsonSerializable;

class Variable implements JsonSerializable, Arrayable
{
	protected ?string $entityId = null;
	protected ?string $supertitle = null;
	protected ?string $avatar = null;
	protected array $avatarOptions = [];
	protected array $conflictsWith = [];
	protected array $requires = [];
	protected ?bool $isSecondary = null;
	protected ?string $hint = null;

	public function __construct(
		protected int|string $id,
		protected string $title,
	)
	{
	}

	public function getId(): int|string
	{
		return $this->id;
	}

	public function setId(int|string $id): static
	{
		$this->id = $id;

		return $this;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title): static
	{
		$this->title = $title;

		return $this;
	}

	public function getEntityId(): ?string
	{
		return $this->entityId;
	}

	public function setEntityId(?string $entityId): static
	{
		$this->entityId = $entityId;

		return $this;
	}

	public function getSupertitle(): ?string
	{
		return $this->supertitle;
	}

	public function setSupertitle(?string $supertitle): static
	{
		$this->supertitle = $supertitle;

		return $this;
	}

	public function getAvatar(): ?string
	{
		return $this->avatar;
	}

	public function setAvatar(?string $avatar): static
	{
		$this->avatar = $avatar;

		return $this;
	}

	public function getAvatarOptions(): array
	{
		return $this->avatarOptions;
	}

	public function setAvatarOptions(array $avatarOptions): static
	{
		$this->avatarOptions = $avatarOptions;

		return $this;
	}

	public function getConflictsWith(): array
	{
		return $this->conflictsWith;
	}

	public function setConflictsWith(array $variableIds): static
	{
		$this->conflictsWith = $variableIds;

		return $this;
	}

	public function addConflictsWith(string $variableId): static
	{
		$this->conflictsWith[] = $variableId;

		return $this;
	}

	public function getRequires(): array
	{
		return $this->requires;
	}

	public function setRequires(array $variableIds): static
	{
		$this->requires = $variableIds;

		return $this;
	}

	public function addRequires(string $variableId): static
	{
		$this->requires[] = $variableId;

		return $this;
	}

	public function isSecondary(): ?bool
	{
		return $this->isSecondary;
	}

	public function setIsSecondary(?bool $isSecondary): static
	{
		$this->isSecondary = $isSecondary;

		return $this;
	}

	public function getHint(): ?string
	{
		return $this->hint;
	}

	public function setHint(?string $hint): static
	{
		$this->hint = $hint;

		return $this;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'title' => $this->getTitle(),
			'entityId' => $this->getEntityId(),
			'supertitle' => $this->getSupertitle(),
			'avatar' => $this->getAvatar(),
			'avatarOptions' => $this->getAvatarOptions(),
			'conflictsWith' => $this->getConflictsWith(),
			'requires' => $this->getRequires(),
			'secondary' => $this->isSecondary(),
			'hint' => $this->getHint(),
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
