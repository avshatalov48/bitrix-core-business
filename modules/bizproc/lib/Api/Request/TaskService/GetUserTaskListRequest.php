<?php

namespace Bitrix\Bizproc\Api\Request\TaskService;

final class GetUserTaskListRequest
{
	public function __construct(
		public /*readonly*/ array $additionalSelectFields = [],
		public /*readonly*/ array $sort = ['ID' => 'DESC'],
		public /*readonly*/ array $filter = [],
		public /*readonly*/ int $offset = 0,
		public /*readonly*/ int $limit = 10,
	) {}
}
