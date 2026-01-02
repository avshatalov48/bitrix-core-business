<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Config;

enum WorkerRunMode: string
{
	case BackgroundInWeb = 'web';
	case Cli = 'cli';
}
