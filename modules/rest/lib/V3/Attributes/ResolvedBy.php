<?php

namespace Bitrix\Rest\V3\Attributes;

use Bitrix\Rest\V3\Controllers\RestController;
use Bitrix\Rest\V3\Exceptions\InvalidClassInstanceProvidedException;

#[\Attribute]
class ResolvedBy extends AbstractAttribute
{
	public function __construct(public readonly string $controller)
	{
		if (!is_subclass_of($this->controller, RestController::class))
		{
			throw new InvalidClassInstanceProvidedException($this->controller, RestController::class);
		}
	}

}
