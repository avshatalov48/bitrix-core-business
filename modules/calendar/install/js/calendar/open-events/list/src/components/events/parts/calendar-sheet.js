import { DateTimeFormat } from 'main.date';
import { AppSettings } from '../../../helpers/app-settings';
import { EventModel } from '../../../model/event/open-event';
import 'ui.icon-set.main';
import '../css/calendar-sheet.css';

export const CalendarSheet = {
	props: {
		event: EventModel,
	},
	computed: {
		calendarDate(): string
		{
			return this.event.dateFrom.getDate();
		},
		calendarMonth(): string
		{
			return DateTimeFormat.format('f', this.event.dateFrom);
		},
		calendarTime(): string
		{
			if (this.event.isFullDay)
			{
				return this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_ALL_DAY');
			}

			const timeFormat = DateTimeFormat.getFormat('SHORT_TIME_FORMAT');
			const time = DateTimeFormat.format(timeFormat, this.event.dateFrom);
			const dayOfWeek = DateTimeFormat.format('D', this.event.dateFrom);

			return this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_FORMAT_WEEKDAY_TIME', {
				'#WEEKDAY#': dayOfWeek,
				'#TIME#': time,
			});
		},
		isCreator()
		{
			return this.event.creatorId === AppSettings.currentUserId;
		},
	},
	template: `
		<div class="calendar-open-events-list-calendar-sheet" :style="{ borderColor: event.color }">
			<div class="calendar-open-events-list-calendar-sheet-header" :style="{ backgroundColor: event.color }">
				<div class="calendar-open-events-list-calendar-sheet-header-hole"></div>
				<div class="calendar-open-events-list-calendar-sheet-header-hole"></div>
			</div>
			<div class="calendar-open-events-list-calendar-sheet-content">
				<div class="calendar-open-events-list-calendar-sheet-date">
					{{ calendarDate }}
				</div>
				<div class="calendar-open-events-list-calendar-sheet-month">
					{{ calendarMonth }}
				</div>
				<div class="calendar-open-events-list-calendar-sheet-time" :style="{ color: event.color }">
					{{ calendarTime }}
				</div>
			</div>
			<div
				class="calendar-open-events-list-calendar-sheet-crown"
				v-if="isCreator"
				:title="$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_EVENT_YOU_ARE_OWNER')"
			>
				<div class="ui-icon-set --crown-2"></div>
			</div>
		</div>
	`,
}
