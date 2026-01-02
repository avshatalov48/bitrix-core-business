<?php

declare(strict_types=1);

namespace Bitrix\Landing\Mainpage;

enum Templates: string
{
	case Enterprise = 'vibe_enterprise';
	case Automation = 'vibe_automation';
	case Collaboration = 'vibe_collaboration';
	case Boards = 'vibe_boards';
	case Booking = 'vibe_booking';
}
