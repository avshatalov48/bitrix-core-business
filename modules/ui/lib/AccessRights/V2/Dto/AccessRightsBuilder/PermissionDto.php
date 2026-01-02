<?php

namespace Bitrix\UI\AccessRights\V2\Dto\AccessRightsBuilder;

use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Action;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Configurator\RightItemConfigurator;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Control;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Permission;
use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem;

final class PermissionDto implements Permission, RightItemConfigurator
{
	private \Closure $rightItemConfigurator;

	public function __construct(
		private readonly Action $action,
		private readonly Control $control,
	)
	{
		$this->rightItemConfigurator = static fn (RightItem $rightItem) => $rightItem;
	}

	public function getAction(): Action
	{
		return $this->action;
	}

	public function getControl(): Control
	{
		return $this->control;
	}

	public function setRightItemConfigurator(callable $configurator): self
	{
		$this->rightItemConfigurator = $configurator;

		return $this;
	}

	public function configureRightItem(RightItem $rightItem): void
	{
		($this->rightItemConfigurator)($rightItem);
	}
}
