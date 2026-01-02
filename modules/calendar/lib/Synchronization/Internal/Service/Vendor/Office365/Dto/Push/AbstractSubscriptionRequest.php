<?php
declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\Push;

use Bitrix\Main\Type\Contract\Arrayable;

abstract class AbstractSubscriptionRequest implements Arrayable
{
	protected function getExpirationDateTime(): string
	{
		$time = time() + 70 * 60 * 60;

		return date('c', $time);
	}
}
