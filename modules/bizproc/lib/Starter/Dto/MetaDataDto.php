<?php

namespace Bitrix\Bizproc\Starter\Dto;

final class MetaDataDto
{
	public function __construct(
		public readonly ?int $timeToStart = null,
	)
	{}
}
