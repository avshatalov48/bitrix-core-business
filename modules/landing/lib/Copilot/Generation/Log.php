<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation;

/**
 * Manage generation errors and system messages
 */
class Log
{
	private int $generationId;

	public function __construct($generationId)
	{

	}

	public function add(string $message)
	{
		// save to any logger
	}
}