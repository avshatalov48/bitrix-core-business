<?php

namespace Bitrix\Vote\Model\Dto;

class VotedIdPage
{
	/**
	 * @param int $pageSize
	 * @param int $page
	 * @param int $totalCount
	 * @param array<int,int> $userIds
	 */
	public function __construct(
		public readonly int $pageSize,
		public readonly int $page,
		public readonly int $totalCount,
		public readonly array $userIds,
	) {}
}