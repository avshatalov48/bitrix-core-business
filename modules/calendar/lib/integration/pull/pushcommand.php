<?php

namespace Bitrix\Calendar\Integration\Pull;

enum PushCommand: string
{
	case EditEvent = 'edit_event';
	case EditEventLocation = 'edit_event_location';
	case DeleteEvent = 'delete_event';
	case DeleteEventLocation = 'delete_event_location';
	case SetMeetingStatus = 'set_meeting_status';

	case EditSection = 'edit_section';
	case DeleteSection = 'delete_section';
	case ChangeSectionSubscription = 'change_section_subscription';
	case HiddenSectionsUpdated = 'hidden_sections_updated';
	case ChangeSectionCustomization = 'change_section_customization';

	case DeleteRoom = 'delete_room';
	case CreateRoom = 'create_room';
	case UpdateRoom = 'update_room';

	case DeleteCategory = 'delete_category';
	case CreateCategory = 'create_category';
	case UpdateCategory = 'update_category';

	case RefreshSyncStatus = 'refresh_sync_status';
	case AddSyncConnection = 'add_sync_connection';
	case DeleteSyncConnection = 'delete_sync_connection';
	case HandleSuccessfulConnection = 'handle_successful_connection';
	case ProcessSyncConnection = 'process_sync_connection';

	case UpdateUserCounters = 'update_user_counters';
	case UpdateGroupCounters = 'update_group_counters';
}
