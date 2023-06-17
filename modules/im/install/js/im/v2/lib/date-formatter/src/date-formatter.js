import {DateTimeFormat} from 'main.date';

import {Interval} from './date-template';

import type {DateTemplateType} from './types/date-template-type';

export {DateTemplate, DateCode} from './date-template';

export class DateFormatter
{
	#date: Date;
	#matchingFunctions: {[key: $Values<typeof Interval>]: Function};

	static formatByCode(date: Date, formatCode: string): string
	{
		return new DateFormatter(date).formatByCode(formatCode);
	}

	static formatByTemplate(date: Date, template: DateTemplateType = {}): string
	{
		return new DateFormatter(date).formatByTemplate(template);
	}

	formatByCode(formatCode: string): string
	{
		return DateTimeFormat.format(formatCode, this.#date);
	}

	formatByTemplate(template: DateTemplateType = {}): string
	{
		const intervals = Object.keys(Interval);

		const matchingInterval = intervals.find(interval => {
			const templateHasInterval = !!template[interval];
			if (!templateHasInterval)
			{
				return false;
			}

			const matchingFunction = this.#matchingFunctions[interval];
			const intervalIsMatching = matchingFunction();

			if (!intervalIsMatching)
			{
				return false;
			}

			// it's a matching code from provided template
			return true;
		});

		if (!matchingInterval)
		{
			console.error('DateFormatter: no matching intervals were found for', template);
			return;
		}

		const matchingCode = template[matchingInterval];

		return this.formatByCode(matchingCode);
	}

	constructor(date: Date)
	{
		this.#date = date;
		this.#matchingFunctions = {
			[Interval.tomorrow]: () => this.#isTomorrow(),
			[Interval.today]: () => this.#isToday(),
			[Interval.yesterday]: () => this.#isYesterday(),
			[Interval.week]: () => this.#isCurrentWeek(),
			[Interval.year]: () => this.#isCurrentYear(),
			[Interval.olderThanYear]: () => !this.#isCurrentYear()
		};
	}

	#isYesterday(): boolean
	{
		const yesterday = this.#shiftDate(-1);

		return this.#isSame(yesterday);
	}

	#isToday(): boolean
	{
		return this.#isSame(new Date());
	}

	#isTomorrow(): boolean
	{
		const tomorrow = this.#shiftDate(1);

		return this.#isSame(tomorrow);
	}

	#isCurrentWeek(): boolean
	{
		const date = new Date();
		const currentWeekNumber = +DateTimeFormat.format('W', date);
		const setWeekNumber = +DateTimeFormat.format('W', this.#date);
		const sameYear = this.#isCurrentYear();

		return currentWeekNumber === setWeekNumber && sameYear;
	}

	#isCurrentYear(): boolean
	{
		const date = new Date();
		const currentYear = date.getFullYear();
		const setYear = this.#date.getFullYear();

		return currentYear === setYear;
	}

	#isSame(date): boolean
	{
		const dateLocale = date.toLocaleDateString();
		const setDateLocale = this.#date.toLocaleDateString();

		return dateLocale === setDateLocale;
	}

	#shiftDate(shift): Date
	{
		const date = new Date();
		date.setDate(date.getDate() + shift);

		return date;
	}
}