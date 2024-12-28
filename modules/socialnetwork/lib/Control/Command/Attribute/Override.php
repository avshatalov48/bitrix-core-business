<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Override
{
	public function __construct(public readonly string $class)
	{

	}
}