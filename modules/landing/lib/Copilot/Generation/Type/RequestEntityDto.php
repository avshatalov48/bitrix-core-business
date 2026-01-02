<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Type;

class RequestEntityDto
{
	public function __construct(
		public ?int $landingId,
		public ?int $blockId,
		public ?string $nodeCode,
		public ?int $position,
		public ?string $prompt,
		public ?int $requestId = null,
	)
	{
	}
}