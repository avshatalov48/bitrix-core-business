<?php

namespace Bitrix\Rest\V3\Attributes;

#[\Attribute]
class DtoType extends AbstractAttribute
{
	public function __construct(public readonly string $type)
	{
	}
}
