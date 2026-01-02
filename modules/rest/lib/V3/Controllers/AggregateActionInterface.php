<?php

namespace Bitrix\Rest\V3\Controllers;

use Bitrix\Rest\V3\Interaction\Request\AggregateRequest;
use Bitrix\Rest\V3\Interaction\Response\AggregateResponse;

interface AggregateActionInterface
{
	public function aggregateAction(AggregateRequest $request): AggregateResponse;
}
