<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Push;

use Bitrix\Calendar\Synchronization\Internal\Entity\Push\Push;

interface PushProcessorInterface
{
	public function processPush(Push $push): void;
}
