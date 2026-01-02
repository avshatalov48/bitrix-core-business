<?php

namespace Bitrix\Bizproc\Starter\Dto;

use Bitrix\Bizproc\Starter\Enum\Face;

final class ContextDto
{
	public function __construct(
		public readonly string $moduleId,
		public readonly Face $face = Face::WEB,
	)
	{}
}
