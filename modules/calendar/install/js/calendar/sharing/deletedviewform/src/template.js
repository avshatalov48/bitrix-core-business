import { EventInfo } from "calendar.sharing.publicevent";

export const DeletedViewFormTemplate = {
	props: {
		eventInfo: Object,
		hostName: String,
	},
	components: {
		EventInfo,
	},
	template: `
		<div class="calendar-shared-event-container calendar-sharing--subtract calendar-sharing--error">
			<div class="calendar-shared-event_icon"></div>
			<div class="calendar-shared-event_deleted-title">{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_EVENT_IS_DELETED') }}</div>
			<EventInfo
				:event-info="eventInfo"
				:current-meeting-status="'N'"
				:is-deleted="true"
				:show-host="true"
			/>
		</div>
	`
};