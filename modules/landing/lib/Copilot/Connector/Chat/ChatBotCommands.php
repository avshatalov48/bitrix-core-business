<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\Chat;

enum ChatBotCommands: string
{
	case startGeneration = 'startGeneration';
	case restartGeneration = 'restartGeneration';
	case startOver = 'startOver';
}
