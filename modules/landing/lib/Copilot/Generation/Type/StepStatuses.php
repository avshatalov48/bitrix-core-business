<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Type;

enum StepStatuses: int
{
	case New = 0;
	case Error = 5;
	case Started = 10;
	case Finished = 20;
}
