import { Util } from 'calendar.util';

export const EventSlotItem = {
	props: ['item', 'key'],
	data()
	{
		return {

		};
	},
	computed: {
		timeInput()
		{
			return Util.formatTimeInterval(this.item.timeFrom, this.item.timeTo);
		},
	},
	methods: {
		handleSetEventButtonClick()
		{
			this.$emit('handleSetEventButtonClick', {
				timeFrom: this.item.timeFrom,
				timeTo: this.item.timeTo,
			});
		},
	},
	template:`
		<div class="calendar-sharing-event-slot-item" :class="{'calendar-sharing-event-slot-item-hidden': !item.available}">
			<div class="calendar-sharing-event-slot-item-time">
				{{ timeInput }}
			</div>
			<button
				class="ui-btn ui-btn-success ui-btn-xs ui-btn-round"
				@click="handleSetEventButtonClick"
			>
				{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_CREATE_MEETING') }}
			</button>
		</div>
	`
};
