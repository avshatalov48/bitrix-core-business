import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Util } from 'calendar.util';
import { CalendarSettings } from './calendar-settings';
import { RuleModel } from './rule';

export type RangeParams = {
	weekdays: number[],
	from: number,
	to: number,
};

type Params = {
	id: number,
	range: RangeParams,
	calendarSettings: CalendarSettings,
	rule: RuleModel,
};

export class RangeModel extends EventEmitter
{
	#calendarSettings: CalendarSettings;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('Calendar.Sharing.Range');

		const { id, range, rule, calendarSettings, isNew } = params;

		this.id = id;
		this.rule = rule;
		this.from = range.from;
		this.to = range.to;
		this.new = isNew;
		this.deletable = false;

		this.#calendarSettings = calendarSettings;

		this.setWeekDays(range.weekdays);
	}

	toArray(): RangeParams
	{
		return {
			from: this.getFrom(),
			to: this.to,
			weekdays: this.getWeekDays(),
		};
	}

	getRule(): RuleModel
	{
		return this.rule;
	}

	getId(): number
	{
		return this.id;
	}

	getFromFormatted(): string
	{
		return this.formatMinutes(this.getFrom());
	}

	getFrom(): number
	{
		return this.from;
	}

	setFrom(value): void
	{
		this.from = parseInt(value, 10);

		if (this.from + this.#getSlotSize() > this.to)
		{
			this.to = this.from + this.#getSlotSize();
		}

		this.updated();
	}

	getToFormatted(): string
	{
		return this.formatMinutes(this.getTo());
	}

	getTo(): number
	{
		return this.to;
	}

	setTo(value): void
	{
		this.to = value;
		this.updated();
	}

	updateSlotSize(): void
	{
		const maxFrom = 24 * 60 - this.#getSlotSize();

		if (this.from > maxFrom)
		{
			this.from = maxFrom;
			this.to = this.from + this.#getSlotSize();
		}
		else if (this.from + this.#getSlotSize() > this.to)
		{
			this.to = this.from + this.#getSlotSize();
		}

		this.updated();
	}

	addWeekday(weekday: number): void
	{
		if (this.weekdays.includes(weekday))
		{
			return;
		}

		this.setWeekDays([...this.weekdays, weekday]);
	}

	removeWeekday(weekday: number): void
	{
		this.setWeekDays(this.weekdays.filter((w) => w !== weekday));
	}

	getWeekDays(): number[]
	{
		return this.weekdays;
	}

	setWeekDays(weekdays: number[]): void
	{
		this.weekdays = this.sortWeekdays(weekdays);
		this.updated();
	}

	getWeekdaysTitle(forceLong: boolean = false): string
	{
		if ([...this.weekdays].sort().join(',') === [1, 2, 3, 4, 5].sort().join(','))
		{
			return Loc.getMessage('CALENDAR_SHARING_SETTINGS_WORKDAYS_MSGVER_1');
		}

		return this.formatWeekdays(forceLong);
	}

	formatWeekdays(forceLong: boolean): string
	{
		const weekdaysLoc = Util.getWeekdaysLoc(forceLong || this.weekdays.length === 1);

		const weekdays = this.getWeekDays();

		if (weekdays.length === 0)
		{
			return '';
		}

		return weekdays.map((w) => weekdaysLoc[w]).reduce((a, b) => `${a}, ${b}`);
	}

	sortWeekdays(weekdays: number[]): number[]
	{
		return weekdays
			.map((w) => (w < this.#calendarSettings.weekStart ? w + 10 : w))
			.sort((a, b) => a - b)
			.map((w) => w % 10)
		;
	}

	getAvailableTimeFrom(): { value: number, name: string }[]
	{
		const timeStamps = [];

		const maxFrom = 24 * 60 - this.#getSlotSize();
		for (let hour = 0; hour <= 24; hour++)
		{
			if (hour * 60 <= maxFrom)
			{
				timeStamps.push({
					value: hour * 60,
					name: Util.formatTime(hour, 0),
				});
			}

			if (hour !== 24 && hour * 60 + 30 <= maxFrom)
			{
				timeStamps.push({
					value: hour * 60 + 30,
					name: Util.formatTime(hour, 30),
				});
			}
		}

		return timeStamps;
	}

	getAvailableTimeTo(): { value: number, name: string }[]
	{
		const timeStamps = [];

		for (let hour = 0; hour <= 24; hour++)
		{
			if (hour * 60 >= this.from + this.#getSlotSize())
			{
				timeStamps.push({
					value: hour * 60,
					name: Util.formatTime(hour, 0),
				});
			}

			if (hour !== 24 && hour * 60 + 30 >= this.from + this.#getSlotSize())
			{
				timeStamps.push({
					value: hour * 60 + 30,
					name: Util.formatTime(hour, 30),
				});
			}
		}

		return timeStamps;
	}

	isDeletable(): boolean
	{
		return this.deletable;
	}

	setDeletable(deletable: boolean): void
	{
		this.deletable = deletable;
	}

	isNew(): boolean
	{
		return this.new;
	}

	setNew(isNew: boolean): void
	{
		this.new = isNew;
	}

	#getSlotSize(): number
	{
		return this.rule.getSlotSize();
	}

	getWeekStart(): number
	{
		return this.#calendarSettings.weekStart;
	}

	formatMinutes(minutes: number): string
	{
		const date = new Date(Util.parseDate('01.01.2000').getTime() + minutes * 60 * 1000);

		return Util.formatTime(date);
	}

	updated(): void
	{
		this.emit('updated');
		this.getRule().updated();
	}
}