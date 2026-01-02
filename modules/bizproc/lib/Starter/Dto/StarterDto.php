<?php

namespace Bitrix\Bizproc\Starter\Dto;

final class StarterDto
{
	public function __construct(
		public readonly ?StarterConfigDto $process = null,
		public readonly ?StarterConfigDto $automation = null,
	)
	{}
}
