<?php

namespace Bitrix\Socialnetwork\Control\Command\ValueObject;

interface CreateObjectInterface
{
	public static function create(mixed $data): static;
}