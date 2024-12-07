import '../css/attendee-counter.css';

export const AttendeeCounter = {
	props: {
		attendeesCount: Number,
		maxAttendees: Number|null,
	},
	computed: {
		attendeesValue(): string
		{
			if (this.maxAttendees)
			{
				return this.$Bitrix.Loc.getMessage(
					'CALENDAR_OPEN_EVENTS_LIST_EVENT_ATTENDEE_VALUE',
					{
						'#COUNT#': this.attendeesCount,
						'#COUNT_MAX#': this.maxAttendees,
					}
				);
			}
			else
			{
				return this.attendeesCount;
			}
		},
	},
	template: `
		<div class="calendar-open-events-list-item-attendee-counter">
			<div class="ui-icon-set --persons-2"></div>
			<div v-html="attendeesValue"></div>
		</div>
	`,
};
