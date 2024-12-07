import { Loc } from 'main.core';
import { DateTimeFormat } from "main.date";
import { Util } from 'calendar.util';

export const StartInfo = {
	props: {
		event: {
			type: Object,
			required: true,
		},
		showClockIcon: Boolean,
	},
	data()
	{
		return {
			loc: {
				today: Loc.getMessage('CALENDAR_SHARING_TODAY'),
				tomorrow: Loc.getMessage('CALENDAR_SHARING_TOMORROW'),
			},
		};
	},
	methods: {
		getEventWeekDayShort()
		{
			return DateTimeFormat.format('D', this.event.timeFrom.getTime() / 1000).toLowerCase();
		},
		getEventMonthDay()
		{
			return this.event.timeFrom.getDate();
		},
		getEventDate()
		{
			let dayPhrase = '';
			const dateFormat = Util.getDayMonthFormat();
			const today = new Date();
			const eventDay = new Date(
				this.event.timeFrom.getFullYear(),
				this.event.timeFrom.getMonth(),
				this.event.timeFrom.getDate()
			);

			if (
				today.getTime() > eventDay.getTime()
				&& today.getTime() < eventDay.getTime() + 86000000
			)
			{
				dayPhrase = this.loc.today;
			}
			else if (
				today.getTime() < eventDay.getTime()
				&& today.getTime() > eventDay.getTime() - 86000000
			)
			{
				dayPhrase = this.loc.tomorrow;
			}
			else
			{
				dayPhrase = DateTimeFormat.format('l', this.event.timeFrom.getTime() / 1000).toLowerCase();
			}

			return DateTimeFormat.format(dateFormat, this.event.timeFrom.getTime() / 1000)
				+ ', '
				+ dayPhrase
			;
		},
		getEventTime()
		{
			return Util.formatTimeInterval(this.event.timeFrom, this.event.timeTo);
		},
		getEventTimezone()
		{
			return Util.getFormattedTimezone(this.event.timezone.timezone_id);
		},
		getEventName()
		{
			return BX.util.htmlspecialchars(this.event.name);
		},
	},
	template: `
		<div
			class="calendar-sharing-event-start__info_container" 
			:class="{'calendar-sharing--bg-gray': !this.event.name}"
		>
			<div class="calendar-sharing-event-start__info-icon" :class="{'--xl': this.event.name}">
				<div class="calendar-sharing-event-start__info-icon_status" v-if="showClockIcon"></div>
				<div class="calendar-sharing-event-start__info-icon_day" :class="{'--xl': this.event.name}">
					{{ getEventWeekDayShort() }}
				</div>
				<div class="calendar-sharing-event-start__info-icon_date" :class="{'--xl': this.event.name}">
					{{ getEventMonthDay() }}
				</div>
			</div>
			<div class="calendar-sharing-event-start__info_datetime">
				<div class="calendar-sharing-event-start__event-name" v-if="this.event.name">
					{{ getEventName() }}
				</div>
				<div class="calendar-sharing-event-start__info_date">
					{{ getEventDate() }}
				</div>
				<div class="calendar-sharing-event-start__info_time_box">
					<div class="calendar-sharing-event-start__info_time">
						{{ getEventTime() }}
					</div>
				</div>
				<div class="calendar-sharing-event-start__info_timezone">
					{{ getEventTimezone() }}
				</div>
			</div>
		</div>
	`,
}
