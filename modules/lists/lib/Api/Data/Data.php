<?php

namespace Bitrix\Lists\Api\Data;

abstract class Data
{
	abstract public static function createFromRequest($request): self;

	protected static function validateId(int $id): ?int
	{
		if ($id >= 0)
		{
			return $id;
		}

		return null;
	}
}
