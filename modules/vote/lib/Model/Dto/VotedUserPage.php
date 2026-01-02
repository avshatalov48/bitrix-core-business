<?php

namespace Bitrix\Vote\Model\Dto;

class VotedUserPage
{
	/**
	 * @param int $pageSize
	 * @param int $page
	 * @param int $totalCount
	 * @param list<\JsonSerializable> $items
	 */
	public function __construct(
		public readonly int $pageSize,
		public readonly int $page,
		public readonly int $totalCount,
		public readonly array $items,
	) {}
}