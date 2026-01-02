<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger;

enum Queue: string
{
	case GoogleSectionSync = 'google_section_sync';
	case ICloudSectionSync = 'icloud_section_sync';
	case Office365SectionSync = 'office365_section_sync';

	case GoogleEventSync = 'google_event_sync';
	case ICloudEventSync = 'icloud_event_sync';
	case Office365EventSync = 'office365_event_sync';

	case GooglePush = 'google_push';

	case Office365Push = 'office365_push';
}
