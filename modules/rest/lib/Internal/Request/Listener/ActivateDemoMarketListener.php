<?php

namespace Bitrix\Rest\Internal\Request\Listener;

use Bitrix\Main\HttpRequest;

class ActivateDemoMarketListener implements Listener
{
	protected const PARAM_NAME = 'activate_demo_market';

	public function handle(HttpRequest $request): void
	{
		if ($request->getQuery(static::PARAM_NAME) === 'Y')
		{
			$subscription = new \Bitrix\Rest\Internal\Integration\Market\Subscription();
			$subscription->activateDemo();
		}
	}
}
