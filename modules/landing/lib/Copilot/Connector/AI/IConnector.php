<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\AI;

use Bitrix\Main;

interface IConnector
{
	/**
	 * Send prompt to AI engine
	 * @param Prompt $prompt
	 * @return Main\Result
	 */
	public function request(Prompt $prompt): Main\Result;
}