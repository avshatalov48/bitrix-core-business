<?php

namespace Bitrix\Bizproc\Result;

final class ResultDto
{
	public function __construct(
		public readonly string $activity,
		public readonly array $data,
	) {}
}
