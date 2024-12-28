<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper\Default;

use Attribute;
use Bitrix\Socialnetwork\ValueObjectInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DefaultValueBy
{
	public function __construct(protected ValueObjectInterface $defaultValue)
	{

	}

	public function getValue(): mixed
	{
		return $this->defaultValue->getValue();
	}
}