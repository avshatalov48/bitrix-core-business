<?php

namespace Bitrix\Rest\Internal\Request\Listener;

use Bitrix\Main\HttpRequest;

interface Listener
{
	public function handle(HttpRequest $request): void;
}
