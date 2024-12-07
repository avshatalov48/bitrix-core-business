import { EventEmitter, BaseEvent } from 'main.core.events';
import { DateTimeFormat } from 'main.date';
import { Filter } from 'calendar.open-events.filter';
import { AppSettings } from '../../helpers/app-settings';
import { CategoryManager } from '../category-manager/category-manager';
import { PullRequests } from './pull-requests';
import { RecursionParser } from './recursion-parser';
import { EventApi, FilterApi } from '../../api/client';
import { EventModel } from '../../model/event/open-event';

const FILTER_CATEGORY_ID = -1;

class Manager extends EventEmitter
{
	#filter: Filter;

	#events: EventModel[] = [];
	#eventIds: {[categoryId: number]: number[]} = {};
	#shownRanges: {[categoryId: number]: DateRange} = {};
	#loadedRanges: {[categoryId: number]: DateRange} = {};
	#tsRanges: {[categoryId: number]: DateRange} = {};

	#eventPromises: {[categoryId: number]: {[datesKey: string]: Promise<EventDto[]>}} = {};
	#tsRangePromises: {[categoryId: number]: Promise<DateRange>} = {};

	constructor()
	{
		super();

		this.setEventNamespace('Calendar.OpenEvents.List.EventManager');

		this.#subscribeToPull();
	}

	setFilter(filter: Filter): void
	{
		this.#filter = filter;
	}

	#subscribeToPull(): void
	{
		if (!BX.PULL)
		{
			console.info('BX.PULL not initialized');

			return;
		}

		const pullRequests = new PullRequests();
		pullRequests.subscribe('create', this.#createEventPull.bind(this));
		pullRequests.subscribe('update', this.#updateEventPull.bind(this));
		pullRequests.subscribe('delete', this.#deletePullEvent.bind(this));

		BX.PULL.subscribe(pullRequests);
	}

	#createEventPull(event: BaseEvent): void
	{
		const { fields: eventDto } = event.getData();

		const newEvent = new EventModel(eventDto);

		this.#events.push(newEvent);

		[0, newEvent.categoryId].forEach((categoryId) => this.#eventIds[categoryId]?.push(newEvent.id));

		if (newEvent.creatorId !== AppSettings.currentUserId)
		{
			newEvent.isNew = true;
			[0, newEvent.categoryId].forEach((categoryId) => CategoryManager.incrementNewCounter(categoryId));
		}

		CategoryManager.bubbleUp(newEvent.categoryId);

		this.emit('update', { eventId: newEvent.id });
	}

	#updateEventPull(event: BaseEvent): void
	{
		const { fields: eventDto, [AppSettings.pullEventUserFieldsKey]: userFields } = event.getData();
		Object.assign(eventDto, userFields || {});

		this.#updateEvent(eventDto.id, eventDto);
	}

	#deletePullEvent(event: BaseEvent): void
	{
		const { fields: { eventId } } = event.getData();

		this.#deleteEvent(eventId);
	}

	async setEventAttendee(eventId: number, isAttendee: boolean): void
	{
		this.#updateEvent(eventId, { isAttendee });

		try
		{
			await EventApi.setAttendeeStatus(eventId, isAttendee);
		}
		catch (e)
		{
			this.#updateEvent(eventId, { isAttendee: !isAttendee });
		}
	}

	async setEventWatched(eventId: number): void
	{
		const event = this.#getEvent(eventId);
		if (!event.isNew)
		{
			return;
		}

		this.#updateEvent(eventId, { isNew: false });

		try
		{
			await EventApi.setWatched([ eventId ]);

			CategoryManager.decrementNewCounter(event.categoryId);
		}
		catch
		{
			this.#updateEvent(eventId, { isNew: true });
		}
	}

	#updateEvent(eventId: number, fields: any): void
	{
		const event = this.#getEvent(eventId);
		if (!event)
		{
			return;
		}

		event.updateFields(fields);

		this.emit('update', { eventId });
	}

	#deleteEvent(eventId: number): void
	{
		this.#events = this.#events.filter(it => it.id !== eventId);

		this.emit('delete', { eventId });
	}

	#getEvent(eventId: number): ?EventModel
	{
		return this.#events.find((it) => it.id === eventId) ?? null;
	}

	async filterEvents(): Promise<EventModel[]>
	{
		const filterKey = this.#filter.getFilterFieldsKey();
		if (filterKey !== this.filterEvents.previousFilterKey)
		{
			delete this.#shownRanges[FILTER_CATEGORY_ID];
			delete this.#loadedRanges[FILTER_CATEGORY_ID];
			delete this.#eventIds[FILTER_CATEGORY_ID];
			delete this.#eventPromises[FILTER_CATEGORY_ID];
			delete this.#tsRanges[FILTER_CATEGORY_ID];
			delete this.#tsRangePromises[FILTER_CATEGORY_ID];
		}

		this.filterEvents.previousFilterKey = filterKey;

		return this.getEvents(FILTER_CATEGORY_ID);
	}

	filterNext(): Promise<EventModel[]>
	{
		return this.getNext(FILTER_CATEGORY_ID);
	}

	filterPrevious(): Promise<EventModel[]>
	{
		return this.getPrevious(FILTER_CATEGORY_ID);
	}

	async getNext(categoryId: number = 0): Promise<EventModel[]>
	{
		const everythingIsLoaded = this.#loadedRanges[categoryId].to >= this.#tsRanges[categoryId].to;
		const everythingIsShown = this.#shownRanges[categoryId].to >= this.#tsRanges[categoryId].to;
		const events = this.#prepareEvents(categoryId);

		if (everythingIsShown)
		{
			return events;
		}

		this.#shownRanges[categoryId].to = this.#getLastDayOfNextMonth(this.#shownRanges[categoryId].to);

		const eventsBeforeLoad = this.#prepareEvents(categoryId);

		if (everythingIsLoaded)
		{
			if (eventsBeforeLoad.length === events.length)
			{
				return this.getNext(categoryId);
			}

			return eventsBeforeLoad;
		}

		const loadedEvents = await this.getEvents(categoryId, {
			from: this.#shownRanges[categoryId].to,
			to: this.#shownRanges[categoryId].to,
		});

		if (loadedEvents.length === eventsBeforeLoad.length)
		{
			await this.getEvents(categoryId, {
				from: this.#shownRanges[categoryId].to,
				to: this.#tsRanges[categoryId].to,
			});

			return this.getNext(categoryId);
		}

		return loadedEvents;
	}

	async getPrevious(categoryId: number = 0): Promise<EventModel[]>
	{
		const everythingIsLoaded = this.#loadedRanges[categoryId].from <= this.#tsRanges[categoryId].from;
		const everythingIsShown = this.#shownRanges[categoryId].from <= this.#tsRanges[categoryId].from;
		const events = this.#prepareEvents(categoryId);

		if (everythingIsShown)
		{
			return events;
		}

		this.#shownRanges[categoryId].from = this.#getFirstDayOfPreviousMonth(this.#shownRanges[categoryId].from);

		const eventsBeforeLoad = this.#prepareEvents(categoryId);

		if (everythingIsLoaded)
		{
			if (eventsBeforeLoad.length === events.length)
			{
				return this.getPrevious(categoryId);
			}

			return eventsBeforeLoad;
		}

		const loadedEvents = await this.getEvents(categoryId, {
			from: this.#shownRanges[categoryId].from,
			to: this.#shownRanges[categoryId].from,
		});

		if (loadedEvents.length === eventsBeforeLoad.length)
		{
			await this.getEvents(categoryId, {
				from: this.#tsRanges[categoryId].from,
				to: this.#shownRanges[categoryId].from,
			});

			return this.getPrevious(categoryId);
		}

		return loadedEvents;
	}

	async getEvents(categoryId: number = 0, dateRange: DateRange = {}): Promise<EventModel[]>
	{
		this.#tsRanges[categoryId] ??= await this.#loadTsRange(categoryId);

		const today = new Date();
		let from = dateRange.from ?? this.#getFirstDayOfPreviousMonth(today);
		let to = dateRange.to ?? this.#getLastDayOfNextMonth(today);

		if (categoryId === FILTER_CATEGORY_ID && this.#filter.isDateFieldApplied())
		{
			from = this.#tsRanges[categoryId].from;
			to = this.#tsRanges[categoryId].to;
		}

		this.#loadedRanges[categoryId] ??= { from, to };
		this.#shownRanges[categoryId] ??= { from, to };

		this.#loadedRanges[categoryId].from = new Date(Math.min(from, this.#loadedRanges[categoryId].from));
		this.#loadedRanges[categoryId].to = new Date(Math.max(to, this.#loadedRanges[categoryId].to));

		const events = await this.#loadEvents(categoryId, { from, to });

		const alreadyLoadedIds = Object.values(this.#eventIds).flat();
		const newEvents = events.filter((it) => !alreadyLoadedIds.includes(it.id));

		this.#events.push(...newEvents);

		const alreadyLoadedCategoryIds = this.#eventIds[categoryId] ?? [];
		const newCategoryEvents = events.filter((it) => !alreadyLoadedCategoryIds.includes(it.id));

		this.#eventIds[categoryId] ??= [];
		this.#eventIds[categoryId].push(...newCategoryEvents.map((it) => it.id));

		return this.#prepareEvents(categoryId);
	}

	#getFirstDayOfPreviousMonth(date: Date): Date
	{
		return new Date(date.getFullYear(), date.getMonth() - 1, 1);
	}

	#getLastDayOfNextMonth(date: Date): Date
	{
		return new Date(date.getFullYear(), date.getMonth() + 2, 0, 23, 59, 59);
	}

	#prepareEvents(categoryId: number): EventModel[]
	{
		const fromLimit = this.#shownRanges[categoryId].from;
		const toLimit = this.#shownRanges[categoryId].to;

		return this.#events
			.filter((it) => this.#eventIds[categoryId].includes(it.id))
			.flatMap((it) => RecursionParser.parseRecursion(it, {
				fromLimit,
				toLimit,
			}))
			.filter((it) => it.dateFrom >= fromLimit && it.dateTo <= toLimit)
		;
	}

	async #loadEvents(categoryId: number, dateRange: DateRange): Promise<EventModel[]>
	{
		const datesKey = this.#getDateKey(dateRange);

		this.#eventPromises[categoryId] ??= {};
		this.#eventPromises[categoryId][datesKey] ??= this.#requestEvents(categoryId, dateRange);

		const response = await this.#eventPromises[categoryId][datesKey];

		return response.map((eventDto: EventDto): EventModel => new EventModel(eventDto));
	}

	#getDateKey(dateRange: DateRange): string
	{
		return `${this.#getDateCode(dateRange.from)}-${this.#getDateCode(dateRange.to)}`;
	}

	#getDateCode(date: Date): string
	{
		return DateTimeFormat.format('d.m.Y', date);
	}

	#requestEvents(categoryId: number, dateRange: DateRange): Promise<EventDto[]>
	{
		if (categoryId === FILTER_CATEGORY_ID)
		{
			return FilterApi.query({
				filterId: this.#filter.id,
				fromDate: dateRange.from.getDate(),
				fromMonth: dateRange.from.getMonth() + 1,
				fromYear: dateRange.from.getFullYear(),
				toDate: dateRange.to.getDate(),
				toMonth: dateRange.to.getMonth() + 1,
				toYear: dateRange.to.getFullYear(),
			});
		}

		return EventApi.list({
			categoryId,
			fromMonth: dateRange.from.getMonth() + 1,
			fromYear: dateRange.from.getFullYear(),
			toMonth: dateRange.to.getMonth() + 1,
			toYear: dateRange.to.getFullYear(),
		});
	}

	async #loadTsRange(categoryId: number): Promise<DateRange>
	{
		this.#tsRangePromises[categoryId] ??= this.#requestTsRange(categoryId);

		return this.#tsRangePromises[categoryId];
	}

	#requestTsRange(categoryId: number): Promise<DateRange>
	{
		if (categoryId === FILTER_CATEGORY_ID)
		{
			return FilterApi.getTsRange(this.#filter.id);
		}

		return EventApi.getTsRange(categoryId);
	}
}

export const EventManager = new Manager();
