import { Tag, Loc, Type, Dom } from 'main.core';
import { DateTimeFormat } from 'main.date';
import { Util } from 'calendar.util';
import { Member, MembersList } from '../layout/members-list';

type Data = {
	from: Date,
	to: Date,
	timezone: string,
	isFullDay: boolean,
	members: Member[],
}

export default class WidgetDate
{
	#layout;
	#value;
	#members;

	constructor()
	{
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
		};

		this.#value = {
			from: null,
			to: null,
			timezone: null,
			isFullDay: false,
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

		this.updateLayout();
	}

	updateLayout()
	{
		const timestampFrom = this.#value.from.getTime() / 1000;
		const timestampTo = this.#value.to.getTime() / 1000;

		const isStartAndEndInDifferentDays = DateTimeFormat.format('j F Y', timestampFrom)
			!== DateTimeFormat.format('j F Y', timestampTo)
		;
		const isFullDay = this.#value.isFullDay;

		const dayOfWeekMonthFormat = DateTimeFormat.getFormat('DAY_OF_WEEK_MONTH_FORMAT');
		const shortTimeFormat = DateTimeFormat.getFormat('SHORT_TIME_FORMAT');

		if (isFullDay)
		{
			this.#getNodeCalendarPageTimeFrom().innerText = Loc.getMessage('CALENDAR_SHARING_WIDGET_DATE_FULL_DAY');
		}
		else
		{
			this.#getNodeCalendarPageTimeFrom().innerText = DateTimeFormat.format(shortTimeFormat, timestampFrom);
		}

		this.#getNodeCalendarPageMonth().innerText = DateTimeFormat.format('f', timestampFrom);
		this.#getNodeCalendarPageDay().innerText = DateTimeFormat.format('d', timestampFrom);

		if (isFullDay)
		{
			this.#getNodeDayInfo().innerText = DateTimeFormat.format(dayOfWeekMonthFormat, timestampFrom);
			this.#getNodeTimeInterval().innerText = Loc.getMessage('CALENDAR_SHARING_WIDGET_DATE_FULL_DAY');
		}
		else if (isStartAndEndInDifferentDays)
		{
			this.#getNodeDayInfo().innerText = Loc.getMessage(
				'CALENDAR_SHARING_WIDGET_DATE_EVENT_START',
				{ '#DATE#': Util.formatDayMonthShortTime(timestampFrom) },
			);
			this.#getNodeTimeInterval().innerText = Loc.getMessage(
				'CALENDAR_SHARING_WIDGET_DATE_EVENT_END',
				{ '#DATE#': Util.formatDayMonthShortTime(timestampTo) },
			);
		}
		else
		{
			this.#getNodeDayInfo().innerText = DateTimeFormat.format(dayOfWeekMonthFormat, timestampFrom);
			this.#getNodeTimeInterval().innerText = Util.formatTimeInterval(this.#value.from, this.#value.to);
		}

		if (isFullDay)
		{
			this.#getNodeTimezone().innerText = 'no timezone';
			this.#getNodeTimezone().setAttribute('title', '');
			Dom.addClass(this.#getNodeTimezone(), '--hidden');
		}
		else
		{
			this.#getNodeTimezone().innerText = Util.getFormattedTimezone(this.#value.timezone);
			this.#getNodeTimezone().setAttribute('title', Util.getFormattedTimezone(this.#value.timezone));
			Dom.removeClass(this.#getNodeTimezone(), '--hidden');
		}

		this.#renderAvatarsSection();
	}

	render(): HTMLElement
	{
		return Tag.render`
			<div class="calendar-pub__form-date">
				<div class="calendar-pub__form-date-day">
					${this.#getNodeCalendarPageMonth()}
					<div class="calendar-pub__form-date-content">
						${this.#getNodeCalendarPageDay()}
						${this.#getNodeCalendarPageTimeFrom()}
					</div>
				</div>
				<div class="calendar-pub__form-date-info">
					${this.#getNodeDayInfo()}
					${this.#getNodeTimeInterval()}
					${this.#getNodeTimezone()}
					${this.#renderAvatarsSection()}
				</div>
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

	#renderAvatarsSection()
	{
		const avatarsSection = new MembersList({
			className: 'calendar-pub__form-date-members',
			textClassName: 'calendar-pub-ui__typography-xs',
			avatarSize: 30,
			members: this.#members,
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
}
