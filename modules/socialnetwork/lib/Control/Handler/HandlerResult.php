<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Handler;

use Bitrix\Socialnetwork\Control\GroupResult;

class HandlerResult extends GroupResult
{
	public function setGroupChanged(bool $changed = true): void
	{
		$this->data['changed'] = $changed;
	}

	public function isGroupChanged(): bool
	{
		return (bool)($this->data['changed'] ?? false);
	}
}