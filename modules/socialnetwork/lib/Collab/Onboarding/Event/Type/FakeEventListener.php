<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Event\Type;

use Bitrix\Main\EventResult;

class FakeEventListener extends AbstractEventListener
{
	public function __call(string $name, array $args): EventResult
	{
		return new EventResult(EventResult::SUCCESS);
	}
}