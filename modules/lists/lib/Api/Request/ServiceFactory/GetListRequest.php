<?php

namespace Bitrix\Lists\Api\Request\ServiceFactory;

use Bitrix\Lists\Api\Data\ServiceFactory\ListsToGetFilter;

final class GetListRequest
{
	public function __construct(
		public /*readonly*/ array $sort = ['ID' => 'desc'],
		public /*readonly*/ ?ListsToGetFilter $filter = null,
		public /*readonly*/ int $offset = 0,
		public /*readonly*/ int $limit = 10,
		public /*readonly*/ array $additionalSelectFields = [],
	)
	{}
}
