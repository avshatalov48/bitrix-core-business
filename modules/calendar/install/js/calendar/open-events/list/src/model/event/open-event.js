import { Loc, Type } from 'main.core';
import { DateTimeFormat } from 'main.date';
import { RecursionParser } from '../../data-manager/event-manager/recursion-parser';
import type { EventOptions } from './event-options';

export class EventModel
{
	#id: number;
	#name: string;
	#isFullDay: boolean;
	#dateFromTs: number;
	#dateToTs: number;
	#commentsCount: number;
	#isAttendee: boolean = false;
	#attendeesCount: number;
	#creatorId: number;
	#eventOptions: EventOptions;
	#categoryId: number;
	#categoryName: string;
	#color: string;
	#categoryChannelId: number;
	#threadId: number;
	#isNew: boolean;
	#rrule: ?any;
	#rruleDescription: ?string;
	#exdate: string;

	fields: {};

	constructor(fields = {})
	{
		this.#initFields(fields);
	}

	#initFields(fields): void
	{
		this.#id = parseInt(fields.id, 10);
		this.#name = fields.name;
		this.#isFullDay = fields.isFullDay;

		const fullDayOffset = this.isFullDay ? new Date().getTimezoneOffset() * 60 : 0;
		this.#dateFromTs = fields.dateFromTs + fullDayOffset;
		this.#dateToTs = fields.dateToTs + fullDayOffset;

		this.#commentsCount = fields.commentsCount;
		this.#isAttendee = fields.isAttendee;
		this.#attendeesCount = fields.attendeesCount;
		this.#creatorId = parseInt(fields.creatorId, 10);
		this.#eventOptions = {
			maxAttendees: fields.eventOptions?.maxAttendees || 0,
		};
		this.#categoryId = parseInt(fields.categoryId, 10);
		this.#categoryName = fields.categoryName;
		this.#color = fields.color;
		this.#categoryChannelId = fields.categoryChannelId;
		this.#threadId = fields.threadId;
		this.#isNew = fields.isNew;
		this.#rrule = RecursionParser.parseRrule(fields.rrule);
		this.#rruleDescription = fields.rruleDescription;
		if (Type.isNumber(fields.recursionAmount))
		{
			this.#rrule.amount = fields.recursionAmount;
		}
		if (Type.isNumber(fields.recursionNum))
		{
			this.#rrule.num = fields.recursionNum;
		}

		this.#exdate = fields.exdate;
		this.fields = fields;
	}

	updateFields(fields)
	{
		if ('name' in fields)
		{
			this.#name = fields.name;
		}

		if (!Type.isBoolean(fields.isAttendee))
		{
			delete fields.isAttendee;
		}

		if (!Type.isNumber(fields.commentsCount))
		{
			delete fields.commentsCount;
		}

		if ('isAttendee' in fields)
		{
			if (!this.#isAttendee && fields.isAttendee)
			{
				this.incrementAttendeesCount();
			}
			if (this.#isAttendee && !fields.isAttendee)
			{
				this.decrementAttendeesCount();
			}

			this.#isAttendee = fields.isAttendee;
		}

		if ('attendeesCount' in fields)
		{
			this.#attendeesCount = fields.attendeesCount;
		}

		Object.assign(this.fields, fields);
	}

	get uniqueId(): string
	{
		return this.#id.toString() + '|' + this.#dateFromTs.toString();
	}

	get id(): number
	{
		return this.#id;
	}

	get name(): string
	{
		return this.#name;
	}

	get commentsCount(): number
	{
		return this.#commentsCount;
	}

	get isAttendee(): boolean
	{
		return this.#isAttendee;
	}

	set isAttendee(isAttendee: boolean): void
	{
		this.#isAttendee = isAttendee;
		this.updateFields({ isAttendee });
	}

	get attendeesCount(): number
	{
		return this.#attendeesCount;
	}

	set attendeesCount(attendeesCount: number): void
	{
		this.#attendeesCount = attendeesCount;
		this.updateFields({ attendeesCount });
	}

	incrementAttendeesCount(): void
	{
		this.attendeesCount = ++this.attendeesCount;
	}

	decrementAttendeesCount(): void
	{
		this.attendeesCount = --this.attendeesCount;
	}

	get creatorId(): number
	{
		return this.#creatorId;
	}

	get eventOptions(): EventOptions
	{
		return this.#eventOptions;
	}

	get categoryId(): number
	{
		return this.#categoryId;
	}

	get categoryName(): string
	{
		return this.#categoryName;
	}

	get duration(): number
	{
		return this.dateTo.getTime() - this.dateFrom.getTime();
	}

	get dateFrom(): Date
	{
		return new Date(this.#dateFromTs * 1000);
	}

	get dateTo(): Date
	{
		return new Date(this.#dateToTs * 1000);
	}

	get formattedDateTime(): string
	{
		const isSameDate = this.#getDateCode(this.#dateFromTs) === this.#getDateCode(this.#dateToTs);
		const startsInCurrentYear = this.dateFrom.getFullYear() === new Date().getFullYear();
		const endsInCurrentYear = this.dateTo.getFullYear() === new Date().getFullYear();

		if (isSameDate)
		{
			const dateFormat = startsInCurrentYear ? 'DAY_OF_WEEK_MONTH_FORMAT' : 'FULL_DATE_FORMAT';
			const date = DateTimeFormat.format(DateTimeFormat.getFormat(dateFormat), this.#dateFromTs);
			if (this.isFullDay)
			{
				return date;
			}

			const from = DateTimeFormat.format(DateTimeFormat.getFormat('SHORT_TIME_FORMAT'), this.#dateFromTs);
			const to = DateTimeFormat.format(DateTimeFormat.getFormat('SHORT_TIME_FORMAT'), this.#dateToTs);
			const time = Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_FORMAT_TIME_RANGE', {
				'#FROM#': from,
				'#TO#': to,
			});

			return Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_FORMAT_DATE_TIME', {
				'#DATE#': date,
				'#TIME#': time,
			});
		}

		const dateFromFormat = startsInCurrentYear ? 'DAY_MONTH_FORMAT' : 'LONG_DATE_FORMAT';
		const dateToFormat = endsInCurrentYear ? 'DAY_MONTH_FORMAT' : 'LONG_DATE_FORMAT';

		const dateFrom = DateTimeFormat.format(DateTimeFormat.getFormat(dateFromFormat), this.#dateFromTs);
		const dateTo = DateTimeFormat.format(DateTimeFormat.getFormat(dateToFormat), this.#dateToTs);

		if (this.isFullDay)
		{
			return Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_FORMAT_TIME_RANGE', {
				'#FROM#': dateFrom,
				'#TO#': dateTo,
			});
		}

		const timeFrom = DateTimeFormat.format(DateTimeFormat.getFormat('SHORT_TIME_FORMAT'), this.#dateFromTs);
		const timeTo = DateTimeFormat.format(DateTimeFormat.getFormat('SHORT_TIME_FORMAT'), this.#dateToTs);

		return Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_FORMAT_DATE_TIME_RANGE', {
			'#FROM_DATE#': dateFrom,
			'#FROM_TIME#': timeFrom,
			'#TO_DATE#': dateTo,
			'#TO_TIME#': timeTo,
		});
	}

	get color(): string
	{
		return this.#color;
	}

	get isFullDay(): boolean
	{
		return this.#isFullDay;
	}

	get threadId(): number
	{
		return this.#threadId;
	}

	get categoryChannelId(): number
	{
		return this.#categoryChannelId;
	}

	get isNew(): boolean
	{
		return this.#isNew;
	}

	set isNew(isNew: boolean): void
	{
		this.#isNew = isNew;
		this.updateFields({ isNew });
	}

	get exdate(): string
	{
		return this.#exdate;
	}

	get rrule(): ?RRule
	{
		return this.#rrule;
	}

	get rruleDescription(): ?string
	{
		return this.#rruleDescription;
	}

	#getDateCode(timestamp: number): string
	{
		return DateTimeFormat.format('d.m.Y', timestamp);
	}
}
