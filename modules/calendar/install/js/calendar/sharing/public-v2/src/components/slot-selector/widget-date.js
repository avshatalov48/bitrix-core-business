import {Tag} from "main.core";
import {DateTimeFormat} from "main.date";
import {Util} from "calendar.util";


type Data = {
	from: Date,
	to: Date,
	timezone: string,
}

export default class WidgetDate
{

	#layout;
	#value;
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
		};

		this.#value = {
			from: null,
			to: null,
			timezone: null,
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
		this.updateLayout();
	}

	updateLayout()
	{
		const timestampFrom = this.#value.from.getTime() / 1000;

		this.#getNodeCalendarPageTimeFrom().innerText = DateTimeFormat.format(Util.getTimeFormatShort(), timestampFrom);
		this.#getNodeCalendarPageMonth().innerText = DateTimeFormat.format('f', timestampFrom);
		this.#getNodeCalendarPageDay().innerText = DateTimeFormat.format('d', timestampFrom);

		this.#getNodeDayInfo().innerText = DateTimeFormat.format(Util.getDayOfWeekMonthFormat(), timestampFrom);
		this.#getNodeTimeInterval().innerText = Util.formatTimeInterval(this.#value.from, this.#value.to);
		this.#getNodeTimezone().innerText = Util.getFormattedTimezone(this.#value.timezone);
		this.#getNodeTimezone().setAttribute('title', Util.getFormattedTimezone(this.#value.timezone));
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
}