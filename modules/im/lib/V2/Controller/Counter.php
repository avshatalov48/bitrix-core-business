<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\V2\Message\CounterService;

class Counter extends BaseController
{
	/**
	 * @restMethod im.v2.Counter.get
	 */
	public function getAction(): ?array
	{
		$counters = (new CounterService())->get();

		return $this->convertKeysToCamelCase($counters);
	}
}