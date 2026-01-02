<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\Push;

use JetBrains\PhpStorm\ArrayShape;

class PushUpdateRequest extends AbstractSubscriptionRequest
{
	#[ArrayShape(['expirationDateTime' => 'string'])]
	public function toArray(): array
	{
		return [
			'expirationDateTime' => $this->getExpirationDateTime(),
		];
	}
}
