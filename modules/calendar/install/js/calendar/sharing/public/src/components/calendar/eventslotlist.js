import '../../css/sharing.css'
import { EventSlotItem } from './eventslotitem';
import { Timer } from '../timer';

export const EventSlotList = {
	name: 'EventSlotList',
	props: {
		timezoneList: Object,
	},
	components: {
		EventSlotItem,
		Timer,
	},
	data()
	{
		return {
			itemList: [],
			isItemSelected: false,
			selectedTimezone: null,
			nowTime: new Date(),
			currentTimezoneName: Intl.DateTimeFormat().resolvedOptions().timeZone,
		};
	},
	created()
	{
		this.selectCurrentTimezone();

		this.$Bitrix.eventEmitter.subscribe('calendar:sharing:onTimezoneChange', (event) => {
			this.updateTimezoneParams(event);
		});
		this.$Bitrix.eventEmitter.subscribe('calendar:sharing:doOpenEventSlotList', (event) => {
			this.openEventSlotList(event);
		});
	},
	mounted()
	{
		this.DOM = {
			slotContainer: document.querySelector('.calendar-sharing-event-slot-container'),
		};
	},
	methods: {
		selectCurrentTimezone()
		{
			for (let [key, timezone] of Object.entries(this.timezoneList))
			{
				if (key === this.currentTimezoneName)
				{
					this.selectedTimezone = timezone;
					break;
				}
			}
		},
		openEventSlotList(event)
		{
			const data = event.getData();
			this.itemList = data.slots;
			this.isItemSelected = true;
		},
		updateTimezoneParams(event)
		{
			this.selectedTimezone = event.data.selectedTimezone;
		},
		handleSetEventButtonClick(event)
		{
			this.$Bitrix.eventEmitter.emit('calendar:sharing:onOpenEventAddForm');

			this.$Bitrix.eventEmitter.emit('calendar:sharing:changeEventData', {
				timeFrom: event.timeFrom,
				timeTo: event.timeTo,
				timezone: this.selectedTimezone,
			});

			this.$Bitrix.eventEmitter.emit('calendar:sharing:changeApplicationType', {type: 'addForm'});
		},
	},
	template: `
		<div class="calendar-sharing-event-slot-container" v-show="isItemSelected">
			<EventSlotItem
				v-for="(item, index) in itemList"
				:key="index"
				:item="item"
				:index="index + 1"
				@handleSetEventButtonClick="handleSetEventButtonClick"
			/>
			<div class="calendar-sharing-event-slot-empty" :class="{'calendar-sharing-event-slot-item-hidden': itemList.filter(a => a.available).length}">
				<Timer/>
			</div>
		</div>
	`
};
