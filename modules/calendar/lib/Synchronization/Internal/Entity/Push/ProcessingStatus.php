<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Entity\Push;

/**
 * Values of b_calendar_push.NOT_PROCESSED field
 */
enum ProcessingStatus: string
{
	case Blocked = 'B';
	case Unblocked = 'N';
	case Unprocessed = 'U';
	case Process = 'Y';
}
