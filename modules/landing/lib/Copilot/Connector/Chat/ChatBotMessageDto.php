<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\Chat;

use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;

class ChatBotMessageDto
{
	public function __construct(
		public int $chatId,
		public ?int $generationId = null,
		public ?GenerationErrors $errorCode = null,
		public ?array $params = null,
	)
	{
	}
}