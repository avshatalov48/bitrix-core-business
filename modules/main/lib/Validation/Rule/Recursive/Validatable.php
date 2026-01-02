<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule\Recursive;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Validatable
{
	public function __construct(
		public readonly bool $iterable = false,
	)
	{

	}
}