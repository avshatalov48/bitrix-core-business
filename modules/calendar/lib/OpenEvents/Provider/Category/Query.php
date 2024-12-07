<?php

namespace Bitrix\Calendar\OpenEvents\Provider\Category;

use Bitrix\Calendar\OpenEvents\Provider\Category\Enum\CategoryOrderEnum;

final class Query
{
	private const DEFAULT_LIMIT = 50;
	private const DEFAULT_ORDER = CategoryOrderEnum::BY_ACTIVITY;

	public function __construct(
		public readonly ?Filter $filter = null,
		public readonly ?int $limit = self::DEFAULT_LIMIT,
		public readonly ?int $page = 0,
		public readonly ?CategoryOrderEnum $order = self::DEFAULT_ORDER,
		public readonly ?bool $requireDefault = null,
	)
	{
	}
}
