<?php

namespace Bitrix\Im\V2\Message\Delete\Strategy;

use Bitrix\Im\V2\Message\Delete\DeletionMode;

class NoneDeletionStrategy extends DeletionStrategy
{
	protected function execute(): void {}

	protected function onBeforeDelete(): void {}

	protected function onAfterDelete(): void {}

	protected function getDeletionMode(): DeletionMode
	{
		return DeletionMode::None;
	}
}