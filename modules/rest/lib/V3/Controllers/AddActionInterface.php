<?php

namespace Bitrix\Rest\V3\Controllers;

use Bitrix\Rest\V3\Interaction\Request\AddRequest;
use Bitrix\Rest\V3\Interaction\Response\AddResponse;

interface AddActionInterface
{
	public function addAction(AddRequest $request): AddResponse;
}
