import { Loc } from 'main.core';
import { DateTimeFormat } from "main.date";
import { Util } from 'calendar.util';

export const EventInfo = {
	props: {
		eventInfo: Object,
		currentMeetingStatus: String,
		isDeclined: Boolean,
		isDeleted: Boolean,
		showHost: Boolean,
	},
	data()
	{
		return {
			loc: {
				today: Loc.getMessage('CALENDAR_SHARING_EVENT_TODAY'),
				tomorrow: Loc.getMessage('CALENDAR_SHARING_EVENT_TOMORROW'),
			},
		};
	},
	computed: {
		ownerStatusText()
		{
			const key = 'CALENDAR_SHARING_EVENT_OWNER_STATUS_' + this.currentMeetingStatus.toUpperCase();

			return Loc.getMessage(key);
		},
	},
	methods: {
		getEventWeekDayShort()
		{
			return DateTimeFormat.format('D', this.eventInfo.dateFrom.getTime() / 1000).toLowerCase();
		},
		getEventDate()
		{
			let dayPhrase = '';
			const dateFormat = Util.getDayMonthFormat();
			const today = new Date();
			const eventDay = new Date(
				this.eventInfo.dateFrom.getFullYear(),
				this.eventInfo.dateFrom.getMonth(),
				this.eventInfo.dateFrom.getDate()
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
				dayPhrase = DateTimeFormat.format('l', this.eventInfo.dateFrom.getTime() / 1000).toLowerCase();
			}

			return DateTimeFormat.format(dateFormat, this.eventInfo.dateFrom.getTime() / 1000)
				+ ', '
				+ dayPhrase
			;
		},
		getEventTime()
		{
			return Util.formatTimeInterval(this.eventInfo.dateFrom, this.eventInfo.dateTo);
		},
		downloadIcsFile()
		{
			this.$Bitrix.eventEmitter.emit('calendar:sharing:downloadIcsFile');
		},
	},
	template: `
		<div class="calendar-shared-event__head">
			<div class="calendar-shared-event__icon" :class="{'--cancel': this.isDeclined && !this.isDeleted}">
				<div
					class="calendar-shared-event__icon_status" 
					:class="
					{
						'--approved': this.currentMeetingStatus === 'Y',
						'--cancel': this.currentMeetingStatus === 'N',
					}"
					v-if="!isDeleted"
				></div>
				<div class="calendar-shared-event__icon_text">{{ this.getEventWeekDayShort() }}</div>
				<div class="calendar-shared-event__icon_num">{{ this.eventInfo.dateFrom.getDate() }}</div>
			</div>
			<div class="calendar-shared-event__head_data">
				<div class="calendar-shared-event_title">{{ eventInfo.name }}</div>
				<div class="calendar-shared-event_start">{{ this.getEventDate() }}</div>
				<div class="calendar-shared-event_time-container">
					<div class="calendar-shared-event_end">{{ this.getEventTime() }}</div>
				</div>
				<div class="calendar-shared-event_timezone">{{ eventInfo.timezone }}</div>
				<div
					class="calendar-shared-event_owner-status"
					:class="
					{
						'--accepted': this.currentMeetingStatus === 'Y',
						'--declined': this.currentMeetingStatus === 'N',
					}"
					v-if="!isDeleted"
				>
					{{ ownerStatusText }}
				</div>
				<div
					class="calendar-shared-event_ics"
					@click="downloadIcsFile"
					v-if="!isDeclined && !isDeleted"
				>
					{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_EVENT_ICS') }}
				</div>
				<div class="calendar-shared-event-host-info calendar-shared-event__list_item calendar-shared-event__list_item&#45;&#45;extranet" v-if="showHost">
					<div class="ui-icon ui-icon-common-user ui-icon-common-user-sharing"><i></i></div>
					<a :href="'/company/personal/user/' + eventInfo.hostId + '/'" target="_blank" class="calendar-shared-event__list_name">{{ eventInfo.hostName }}</a>
				</div>
			</div>
		</div>
	`
};