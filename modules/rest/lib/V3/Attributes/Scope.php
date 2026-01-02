<?php

namespace Bitrix\Rest\V3\Attributes;

#[\Attribute]
class Scope extends AbstractAttribute
{
	public function __construct(public readonly string $value)
	{
	}
}
