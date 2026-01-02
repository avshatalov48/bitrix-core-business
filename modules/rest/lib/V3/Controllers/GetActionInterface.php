<?php

namespace Bitrix\Rest\V3\Controllers;

use Bitrix\Rest\V3\Interaction\Request\GetRequest;
use Bitrix\Rest\V3\Interaction\Response\GetResponse;

interface GetActionInterface
{
	public function getAction(GetRequest $request): GetResponse;
}
