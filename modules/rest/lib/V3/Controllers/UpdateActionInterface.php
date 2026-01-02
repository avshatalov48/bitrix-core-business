<?php

namespace Bitrix\Rest\V3\Controllers;

use Bitrix\Rest\V3\Interaction\Request\UpdateRequest;
use Bitrix\Rest\V3\Interaction\Response\UpdateResponse;

interface UpdateActionInterface
{
	public function updateAction(UpdateRequest $request): UpdateResponse;
}
