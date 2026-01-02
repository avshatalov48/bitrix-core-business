<?php

namespace Bitrix\Rest\V3\Interaction\Response;

use Bitrix\Main\Type\Contract\Arrayable;

class ArrayResponse extends Response
{
	public function __construct(protected ?array $array = [])
	{
	}

	public function toArray(): array
	{
		$result = [];

		foreach ($this->array as $key => $value)
		{
			if ($value instanceof Arrayable)
			{
				$result[$key] = $value->toArray();
			}
			else
			{
				$result[$key] = $value;
			}
		}

		return $result;
	}
}
