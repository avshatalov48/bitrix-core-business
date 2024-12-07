import { Type } from 'main.core';
import { DateTimeFormat } from 'main.date';
import { EventModel } from '../../model/event/open-event';

const END_OF_TIME = 2038;

export class RecursionParser
{
	static parseRecursion(event: EventModel, { fromLimit, toLimit }): EventModel[]
	{
		if (event.rrule === null)
		{
			return new EventModel(event.fields);
		}

		const { timestamps } = this.parseTimestamps(event, { fromLimit, toLimit });
		const recursionAmount = this.getAmount(event);

		return timestamps.map(({ fromTs, num }) => new EventModel({
			...event.fields,
			dateFromTs: fromTs / 1000,
			dateToTs: fromTs / 1000 + event.duration / 1000,
			recursionAmount,
			recursionNum: num + 1,
		}));
	}

	static getAmount(event: EventModel): number
	{
		const rruleCount = parseInt(event.rrule.COUNT, 10) || 0;
		if (rruleCount > 0)
		{
			return rruleCount;
		}

		const toLimit = DateTimeFormat.parse(event.rrule.UNTIL);
		if (toLimit.getFullYear() === END_OF_TIME)
		{
			return Infinity;
		}

		const { count } = this.parseTimestamps(event, { fromLimit: null, toLimit });

		return count;
	}

	static parseTimestamps(event: EventModel, { fromLimit, toLimit }): number[]
	{
		const timestamps = [];

		const rrule = event.rrule;
		const exDate = event.exdate.split(';');

		const fullDayOffset = event.isFullDay ? new Date().getTimezoneOffset() * 60000 : 0;
		let from = new Date(event.dateFrom.getTime() - fullDayOffset);
		const to = new Date(Math.min(toLimit, DateTimeFormat.parse(rrule.UNTIL)));
		to.setHours(from.getHours(), from.getMinutes());

		const fromYear = from.getFullYear();
		const fromMonth = from.getMonth();
		const fromDate = from.getDate();
		const fromHour = from.getHours();
		const fromMinute = from.getMinutes();

		let count = 0;

		const FORMAT_DATE = DateTimeFormat.getFormat('FORMAT_DATE');

		while(from <= to)
		{
			if (rrule.COUNT > 0 && count >= rrule.COUNT)
			{
				break;
			}

			const exclude = exDate.includes(DateTimeFormat.format(FORMAT_DATE, from.getTime() / 1000));
			const include = !exclude
				&& (!fromLimit || from.getTime() >= fromLimit.getTime())
				&& (!toLimit || from.getTime() + event.duration <= toLimit.getTime())
			;

			if (rrule.FREQ === 'WEEKLY')
			{
				const weekDay = this.getWeekDayByInd(DateTimeFormat.format('w', from.getTime() / 1000));

				if (Type.isStringFilled(rrule.BYDAY[weekDay]))
				{
					if (include)
					{
						timestamps.push({
							fromTs: from.getTime(),
							num: count,
						});
					}
					count++;
				}

				const skipWeek = (rrule.INTERVAL - 1) * 7 + 1;
				const delta = weekDay === 'SU' ? skipWeek : 1;

				from = new Date(from.getFullYear(), from.getMonth(), from.getDate() + delta, fromHour, fromMinute);
			}

			if (['DAILY', 'MONTHLY', 'YEARLY'].includes(rrule.FREQ))
			{
				if (include)
				{
					timestamps.push({
						fromTs: from.getTime(),
						num: count,
					});
				}
				count++;

				switch (rrule.FREQ)
				{
					case 'DAILY':
						from = new Date(fromYear, fromMonth, fromDate + count * rrule.INTERVAL, fromHour, fromMinute, 0, 0);
						break;
					case 'MONTHLY':
						from = new Date(fromYear, fromMonth + count * rrule.INTERVAL, fromDate, fromHour, fromMinute, 0, 0);
						break;
					case 'YEARLY':
						from = new Date(fromYear + count * rrule.INTERVAL, fromMonth, fromDate, fromHour, fromMinute, 0, 0);
						break;
				}
			}
		}

		return { timestamps, count };
	}

	static getWeekDayByInd(index): string
	{
		return ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'][index];
	}

	static parseRrule(rule: string): ?RRule
	{
		if (!Type.isStringFilled(rule))
		{
			return null;
		}

		const res = {};
		const pairs = rule.split(';')
			.map((it) => it.split('='))
			.filter(([ field ]) => Type.isStringFilled(field))
		;

		for (const [ field, value ] of pairs)
		{
			if (field === 'FREQ' && ['DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY'].includes(value))
			{
				res.FREQ = value;
			}
			if (['COUNT', 'INTERVAL'].includes(field))
			{
				res[field] = Math.max(1, parseInt(value, 10) ?? 0);
			}
			if (field === 'UNTIL')
			{
				res.UNTIL = value;
			}
			if (field === 'BYDAY')
			{
				const regex = /(([-+])?\d+)?(MO|TU|WE|TH|FR|SA|SU)/;

				for (const day of value.split(',').filter((d) => regex.test(d)))
				{
					const matches = [...day.match(regex)];

					res.BYDAY ??= {};
					res.BYDAY[matches[3]] = matches[1] ?? matches[3];
				}

				res.BYDAY ??= { MO: 'MO' };
			}
		}

		return res;
	}
}
