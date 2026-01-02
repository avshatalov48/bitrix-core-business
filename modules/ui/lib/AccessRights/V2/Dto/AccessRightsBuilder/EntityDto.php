<?php

namespace Bitrix\UI\AccessRights\V2\Dto\AccessRightsBuilder;

use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Configurator\RightSectionConfigurator;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Entity;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Permission;
use Bitrix\UI\AccessRights\V2\Options\RightSection;
use Closure;

final class EntityDto implements Entity, RightSectionConfigurator
{
	public function __construct(
		private readonly string $id,
		private readonly string $title,
		/** @var $permissions Permission[] */
		private array $permissions = [],
		private ?Closure $rightSectionConfigurator = null,
	)
	{
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function getPermissions(): array
	{
		return $this->permissions;
	}

	public function setPermissions(array $permissions): self
	{
		$this->permissions = $permissions;

		return $this;
	}

	public function configureRightSection(RightSection $rightSection): void
	{
		if ($this->rightSectionConfigurator === null)
		{
			return;
		}

		($this->rightSectionConfigurator)($rightSection);
	}

	public function setSectionConfigurator(?Closure $rightSectionConfigurator): self
	{
		$this->rightSectionConfigurator = $rightSectionConfigurator;

		return $this;
	}
}
