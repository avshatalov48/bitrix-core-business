<?php

namespace Bitrix\UI\AccessRights\V2\Control\Value;

class MultiVariable extends Variable
{
	protected ?string $entityId = null;
	protected ?string $supertitle = null;
	protected ?string $avatar = null;
	protected array $avatarOptions = [];

	public function entityId(): ?string
	{
		return $this->entityId;
	}

	public function setEntityId(?string $entityId): static
	{
		$this->entityId = $entityId;

		return $this;
	}

	public function supertitle(): ?string
	{
		return $this->supertitle;
	}

	public function setSupertitle(?string $supertitle): static
	{
		$this->supertitle = $supertitle;

		return $this;
	}

	public function avatar(): ?string
	{
		return $this->avatar;
	}

	public function setAvatar(?string $avatar): MultiVariable
	{
		$this->avatar = $avatar;

		return $this;
	}

	public function avatarOptions(): array
	{
		return $this->avatarOptions;
	}

	public function setAvatarOptions(array $avatarOptions): static
	{
		$this->avatarOptions = $avatarOptions;

		return $this;
	}
}
