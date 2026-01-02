<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Type;

enum RequestStatus: int
{
	case New = 0;
	case Sent = 10;
	case Received = 20;
	case Applied = 30;
}
