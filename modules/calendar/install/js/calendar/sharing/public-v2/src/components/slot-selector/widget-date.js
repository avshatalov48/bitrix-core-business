import { Event, Tag, Loc, Type } from 'main.core';
import { DateTimeFormat } from 'main.date';
import { Util } from 'calendar.util';
import { Member, MembersList } from '../layout/members-list';
import { Popup } from 'main.popup';
import 'ui.icon-set.actions';
import bindShowOnHover from '../layout/bind-show-on-hover';

type Data = {
	from: Date,
	to: Date,
	timezone: string,
	isFullDay: boolean,
	rruleDescription: string,
	members: Member[],
}

type WidgetDateProps = {
	allAttendees: boolean,
	filled: boolean,
	browserTimezone: boolean,
};

export default class WidgetDate
{
	#props: WidgetDateProps;
	#layout;
	#value;
	#members;

	static timezoneNoticeUnderstood = false;

	constructor(props: WidgetDateProps = {})
	{
		this.#props = props;

		this.#layout = {
			calendarPage: {
				month: null,
				day: null,
				timeFrom: null,
			},
			dayInfo: null,
			timeInterval: null,
			timezone: null,
			avatarsSection: null,
			timezoneNotice: null,
		};

		this.#value = {
			from: null,
			to: null,
			timezone: null,
			isFullDay: false,
			rruleDescription: '',
		};
	}

	updateValue(data: Data)
	{
		if (data.from)
		{
			this.#value.from = data.from;
		}

		if (data.to)
		{
			this.#value.to = data.to;
		}

		if (data.timezone)
		{
			this.#value.timezone = data.timezone;
		}

		if (Type.isBoolean(data.isFullDay))
		{
			this.#value.isFullDay = data.isFullDay;
		}

		if (data.members)
		{
			this.#members = data.members;
		}

		if (data.rruleDescription)
		{
			this.#value.rruleDescription = data.rruleDescription;
		}

		this.updateLayout();
	}

	updateLayout()
	{
		const timezone = this.#props.browserTimezone ?? this.#value.timezone;
		let offset = this.#getBrowserTimezoneOffset() * 60;
		const from = this.#value.from.getTime() / 1000 + offset;
		const to = this.#value.to.getTime() / 1000 + offset;
		const isFullDay = this.#value.isFullDay;

		const calendarMonthName = this.#formatMonthName(from);
		const calendarDay = this.#formatCalendarDay(from);

		const isSameDate = DateTimeFormat.format('j F Y', from) === DateTimeFormat.format('j F Y', to);
		let calendarTime, eventDate, eventTime, eventTimezone;
		if (isFullDay)
		{
			calendarTime = this.#formatWeekDay(from);
			eventDate = `${this.#formatDate(from)} - ${this.#formatDate(to)}`;
			eventTime = Loc.getMessage('CALENDAR_SHARING_WIDGET_DATE_FULL_DAY');
			eventTimezone = '';

			if (isSameDate)
			{
				eventDate = this.#formatWeekDate(from, '');
			}
		}
		else
		{
			calendarTime = this.#formatTime(from);
			eventDate = this.#formatWeekDate(from);
			eventTime = this.#formatTimeInterval(from, to);
			eventTimezone = Util.getFormattedTimezone(timezone);

			if (!isSameDate)
			{
				eventDate = Loc.getMessage('CALENDAR_SHARING_WIDGET_DATE_EVENT_START', {
					'#DATE#': this.#formatDateTime(from),
				});
				eventTime = Loc.getMessage('CALENDAR_SHARING_WIDGET_DATE_EVENT_END', {
					'#DATE#': this.#formatDateTime(to),
				});
			}
		}

		this.#getNodeCalendarPageMonth().innerText = calendarMonthName;
		this.#getNodeCalendarPageDay().innerText = calendarDay;
		this.#getNodeCalendarPageTimeFrom().innerText = calendarTime;
		this.#getNodeDayInfo().innerText = eventDate;
		this.#getNodeTimeInterval().innerText = eventTime;
		this.#getNodeTimezone().innerText = eventTimezone;
		this.#getNodeTimezone().title = eventTimezone;

		this.#renderAvatarsSection();
	}

	#formatDate(timestamp: number): string
	{
		const dayMonthFormat = DateTimeFormat.getFormat('DAY_MONTH_FORMAT');

		return DateTimeFormat.format(dayMonthFormat, timestamp);
	}

	#formatWeekDay(timestamp: number): string
	{
		return DateTimeFormat.format('D', timestamp);
	}

	#formatWeekDate(timestamp: number): string
	{
		const weekDateFormat = DateTimeFormat.getFormat('DAY_OF_WEEK_MONTH_FORMAT');

		return DateTimeFormat.format(weekDateFormat, timestamp);
	}

	#formatDateTime(timestamp: number): string
	{
		const dayMonthFormat = DateTimeFormat.getFormat('DAY_MONTH_FORMAT');
		const shortTimeFormat = DateTimeFormat.getFormat('SHORT_TIME_FORMAT');
		const format = `${dayMonthFormat} ${shortTimeFormat}`;

		return  DateTimeFormat.format(format, timestamp);
	}

	#formatTimeInterval(from: number, to: number): string
	{
		return `${this.#formatTime(from)} - ${this.#formatTime(to)}`;
	}

	#formatTime(timestamp: number): string
	{
		const shortTimeFormat = DateTimeFormat.getFormat('SHORT_TIME_FORMAT');

		return DateTimeFormat.format(shortTimeFormat, timestamp);
	}

	#formatMonthName(timestamp: number): string
	{
		return DateTimeFormat.format('f', timestamp);
	}

	#formatCalendarDay(timestamp: number): string
	{
		return DateTimeFormat.format('d', timestamp);
	}

	render(): HTMLElement
	{
		return Tag.render`
			<div class="calendar-pub__form-date ${this.#props.filled ? '--filled' : ''}">
				<div class="calendar-pub__form-date-main">
					<div class="calendar-pub__form-date-day">
						${this.#getNodeCalendarPageMonth()}
						<div class="calendar-pub__form-date-content">
							${this.#getNodeCalendarPageDay()}
							${this.#getNodeCalendarPageTimeFrom()}
						</div>
					</div>
					<div class="calendar-pub__form-date-info">
						${this.#getNodeDayInfo()}
						${this.#renderTime()}
						${this.#getNodeTimezone()}
						${this.#renderAvatarsSection()}
					</div>
				</div>
				${this.#renderTimezoneNotice()}
			</div>
		`;
	}

	#getNodeCalendarPageMonth()
	{
		if (!this.#layout.calendarPage.month)
		{
			this.#layout.calendarPage.month = Tag.render`
				<div class="calendar-pub__form-date-day_month"></div>
			`;
		}

		return this.#layout.calendarPage.month;
	}

	#getNodeCalendarPageDay()
	{
		if (!this.#layout.calendarPage.day)
		{
			this.#layout.calendarPage.day = Tag.render`
				<div class="calendar-pub__form-date-day_num"></div>
			`;
		}

		return this.#layout.calendarPage.day;
	}

	#getNodeCalendarPageTimeFrom()
	{
		if (!this.#layout.calendarPage.timeFrom)
		{
			this.#layout.calendarPage.timeFrom = Tag.render`
				<div class="calendar-pub__form-date-day_time">13:00</div>
			`;
		}

		return this.#layout.calendarPage.timeFrom;
	}

	#getNodeDayInfo()
	{
		if (!this.#layout.dayInfo)
		{
			this.#layout.dayInfo = Tag.render`
				<div class="calendar-pub__form-date-info_day"></div>
			`;
		}

		return this.#layout.dayInfo;
	}

	#renderTime(): HTMLElement
	{
		return Tag.render`
			<div class="calendar-pub__form-date-info_time-container">
				${this.#getNodeTimeInterval()}
				${this.#renderRrule()}
			</div>
		`;
	}

	#getNodeTimeInterval()
	{
		if (!this.#layout.timeInterval)
		{
			this.#layout.timeInterval = Tag.render`
				<div class="calendar-pub__form-date-info_time"></div>
			`;
		}

		return this.#layout.timeInterval;
	}

	#renderRrule(): HTMLElement|string
	{
		if (!Type.isStringFilled(this.#value.rruleDescription))
		{
			return '';
		}

		if (!this.#layout.rrule)
		{
			this.#layout.rrule = Tag.render`
				<div class="calendar-pub__form-date-rrule ui-icon-set --refresh-7"></div>
			`;

			const popup = new Popup({
				bindElement: this.#layout.rrule,
				content: this.#value.rruleDescription,
				darkMode: true,
				bindOptions: { position: 'top' },
				offsetTop: -10,
				angle: true,
				autoHide: true,
			});

			bindShowOnHover(popup);
		}

		return this.#layout.rrule;
	}

	#getNodeTimezone()
	{
		if (!this.#layout.timezone)
		{
			this.#layout.timezone = Tag.render`
				<div class="calendar-pub__form-date-info_time-zone"></div>
			`;
		}

		return this.#layout.timezone;
	}

	#renderAvatarsSection(): HTMLElement|string
	{
		if (this.#props.allAttendees)
		{
			return '';
		}

		const avatarsSection = new MembersList({
			className: 'calendar-pub__form-date-members',
			textClassName: 'calendar-pub-ui__typography-xs',
			avatarSize: 30,
			members: this.#members,
			allAttendees: this.#props.allAttendees,
		}).render();

		if (!this.#layout.avatarsSection)
		{
			this.#layout.avatarsSection = Tag.render`
				<div>${avatarsSection}</div>
			`;
		}
		else
		{
			this.#layout.avatarsSection.innerHTML = '';
			this.#layout.avatarsSection.append(avatarsSection);
		}

		return this.#layout.avatarsSection;
	}

	#renderTimezoneNotice(): HTMLElement|string
	{
		const offset = this.#getBrowserTimezoneOffset();
		if (WidgetDate.timezoneNoticeUnderstood || offset === 0)
		{
			return '';
		}

		const timezoneNoticeUnderstandButton = Tag.render`
			<div class="calendar-pub__link-button">
				${Loc.getMessage('CALENDAR_SHARING_UNDERSTAND')}
			</div>
		`;

		Event.bind(timezoneNoticeUnderstandButton, 'click', () => {
			this.#layout.timezoneNotice.remove();
			WidgetDate.timezoneNoticeUnderstood = true;
		});

		const noticeOffsetNode = Tag.render`
			<div class="calendar-pub-timezone-notice-offset">
				${this.#getTimezoneNoticeText(offset)}
			</div>
		`;

		const timezonePopup = new Popup({
			bindElement: noticeOffsetNode,
			content: Util.getFormattedTimezone(this.#value.timezone),
			darkMode: true,
			bindOptions: { position: 'top' },
			offsetTop: -10,
			angle: true,
			autoHide: true,
		});

		bindShowOnHover(timezonePopup);

		this.#layout.timezoneNotice = Tag.render`
			<div class="calendar-pub__event-timezone-notice calendar-pub-ui__typography-sm">
				<div>
					${Loc.getMessage('CALENDAR_SHARING_EVENT_TIMEZONE_NOTICE')}
				</div>
				<div class="calendar-pub__event-timezone-notice-bottom">
					${noticeOffsetNode}
					${timezoneNoticeUnderstandButton}
				</div>
			</div>
		`;

		return this.#layout.timezoneNotice;
	}

	#getTimezoneNoticeText(offset): string
	{
		const sign = (offset < 0) ? '+' : '-';

		return Loc.getMessage('CALENDAR_SHARING_EVENT_TIMEZONE_NOTICE_OFFSET', {
			'#OFFSET#': `${sign}${Util.formatDuration(Math.abs(offset))}`,
		});
	}

	#getBrowserTimezoneOffset(): number
	{
		if (!Type.isStringFilled(this.#props.browserTimezone) || !Type.isStringFilled(this.#value.timezone))
		{
			return 0;
		}

		const eventOffset = Util.getTimeZoneOffset(this.#props.browserTimezone);
		const browserOffset = Util.getTimeZoneOffset(this.#value.timezone);

		return browserOffset - eventOffset;
	}
}
