<?php

namespace Bitrix\Socialnetwork\Control\Mapper\Field;

interface ValueMapperInterface
{
	public function getValue(mixed $value): mixed;
}