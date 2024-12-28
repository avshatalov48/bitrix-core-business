<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Decorator;

use Bitrix\Socialnetwork\Control\AbstractGroupService;

abstract class AbstractGroupServiceDecorator extends AbstractGroupService
{
	public function __construct(
		protected AbstractGroupService $source
	)
	{

	}

	protected function getAddHandlers(): array
	{
		return $this->source->getAddHandlers();
	}

	protected function getUpdateHandlers(): array
	{
		return $this->source->getUpdateHandlers();
	}

	protected function getDeleteHandlers(): array
	{
		return $this->source->getDeleteHandlers();
	}
}