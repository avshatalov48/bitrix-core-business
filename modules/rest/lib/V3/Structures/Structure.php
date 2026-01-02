<?php

namespace Bitrix\Rest\V3\Structures;

use Bitrix\Rest\V3\Interaction\Request\Request;

abstract class Structure
{
	abstract public static function create(mixed $value, string $dtoClass, Request $request): self;
}
