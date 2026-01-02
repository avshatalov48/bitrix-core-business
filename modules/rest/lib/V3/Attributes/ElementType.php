<?php

namespace Bitrix\Rest\V3\Attributes;

#[\Attribute]
class ElementType extends AbstractAttribute
{
	public function __construct(public readonly string $type)
	{
	}
}
