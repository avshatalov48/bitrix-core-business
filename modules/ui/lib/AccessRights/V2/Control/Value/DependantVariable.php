<?php

namespace Bitrix\UI\AccessRights\V2\Control\Value;

class DependantVariable extends Variable
{
	protected array $conflictsWith = [];
	protected array $requires = [];
	protected ?bool $isSecondary = null;
	protected ?string $hint = null;

	public function conflictsWith(): array
	{
		return $this->conflictsWith;
	}

	public function setConflictsWith(array $conflictsWith): static
	{
		$this->conflictsWith = $conflictsWith;

		return $this;
	}

	public function requires(): array
	{
		return $this->requires;
	}

	public function setRequires(array $requires): static
	{
		$this->requires = $requires;

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

	public function hint(): ?string
	{
		return $this->hint;
	}

	public function setHint(?string $hint): static
	{
		$this->hint = $hint;

		return $this;
	}
}
