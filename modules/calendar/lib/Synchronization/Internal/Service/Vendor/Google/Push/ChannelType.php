<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Push;

enum ChannelType: string
{
	case Connection = 'BX_CONNECTION';
	case SectionConnection = 'BX_SC';
}
