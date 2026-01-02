<?php

namespace Bitrix\Rest\V3\Structures;

use Bitrix\Rest\V3\Exceptions\InvalidPaginationException;
use Bitrix\Rest\V3\Interaction\Request\Request;

final class PaginationStructure extends Structure
{
	public const MAX_LIMIT = 1000;

	public const DEFAULT_LIMIT = 50;

	protected int $limit = self::DEFAULT_LIMIT;

	protected int $offset = 0;

	public static function create(mixed $value, string $dtoClass = null, Request $request = null): self
	{
		$structure = new self();

		if (isset($value['limit']))
		{
			if (!is_numeric($value['limit']) || $value['limit'] == 0)
			{
				throw new InvalidPaginationException(['limit' => $value['limit']]);
			}

			$structure->limit = min((int)$value['limit'], self::MAX_LIMIT);
		}

		if (isset($value['offset']))
		{
			if (!is_numeric($value['offset']))
			{
				throw new InvalidPaginationException(['offset' => $value['offset']]);
			}

			$structure->offset = (int)$value['offset'];
		}

		if (isset($value['page']))
		{
			if (!is_numeric($value['page']))
			{
				throw new InvalidPaginationException(['page' => $value['page']]);
			}

			$structure->offset = (int)($value['page'] > 0 ? $value['page'] - 1 : 0) * $structure->limit;
		}

		return $structure;
	}

	public function getLimit(): int
	{
		return $this->limit;
	}

	public function getOffset(): int
	{
		return $this->offset;
	}
}
