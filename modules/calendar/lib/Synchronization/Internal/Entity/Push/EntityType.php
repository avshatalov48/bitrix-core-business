<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Entity\Push;

enum EntityType: string
{
	case Connection = 'CONNECTION';
	case SectionConnection = 'SECTION_CONNECTION';
}
