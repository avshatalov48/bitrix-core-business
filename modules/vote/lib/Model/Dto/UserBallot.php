<?php

namespace Bitrix\Vote\Model\Dto;

class UserBallot
{
	public function __construct(
		public readonly array $stat,
		public readonly array $extras = [],
		public readonly ?int $userId = null,
	) {}
}