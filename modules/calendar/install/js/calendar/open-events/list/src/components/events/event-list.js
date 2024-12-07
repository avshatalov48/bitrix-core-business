import 'main.polyfill.intersectionobserver';
import { mapGetters } from 'ui.vue3.vuex';
import { EventManager } from '../../data-manager/event-manager/event-manager';
import { EventModel } from '../../model/event/open-event';
import { Event } from './event';
import { EmptyState } from './empty-state';

const WATCH_EVENT_MS = 2000;

export const EventList = {
	data(): Object
	{
		return {
			observedEvents: new Map(),
			eventRefs: [],
		};
	},
	computed: {
		...mapGetters({
			events: 'events',
			selectedCategoryId: 'selectedCategoryId',
			isFilterMode: 'isFilterMode',
		}),
		sortedEvents(): Array<EventModel>
		{
			return [...this.events].sort((a: EventModel, b: EventModel) => {
				if (a.dateFrom.getTime() === b.dateFrom.getTime())
				{
					if (a.dateTo.getTime() === b.dateTo.getTime())
					{
						return parseInt(a.id) - parseInt(b.id);
					}

					return a.dateTo.getTime() - b.dateTo.getTime();
				}

				return a.dateFrom.getTime() - b.dateFrom.getTime();
			});
		},
	},
	methods: {
		initObserver(): void
		{
			this.observer = new IntersectionObserver(this.observerCallback, {
				root: this.$refs.eventList,
				threshold: 0.9,
			});
		},
		observerCallback(entries)
		{
			entries.forEach((entry) => {
				if (entry.isIntersecting)
				{
					this.processIntersectedElement(entry.target);
				}
			});
		},
		processIntersectedElement(element)
		{
			const eventId = parseInt(element.dataset.eventId, 10);

			if (this.observedEvents.has(eventId))
			{
				return;
			}

			this.observedEvents.set(eventId, eventId);

			setTimeout(() => {
				this.observer.unobserve(element);
				EventManager.setEventWatched(eventId);
			}, WATCH_EVENT_MS);
		},
		scrollToUpcomingEvent(): void
		{
			const today = new Date();
			today.setHours(0, 0, 0, 0);

			const upcomingEvent = this.sortedEvents.find((event) => event.dateFrom >= today);
			if (!upcomingEvent)
			{
				return;
			}

			this.$refs.eventList.scrollTop = this.eventRefs[upcomingEvent.uniqueId].offsetTop;
		},
		async loadOnScroll(): Promise
		{
			const scrollTop = this.$refs.eventList.scrollTop;
			const scrollHeight = this.$refs.eventList.scrollHeight;
			const offsetHeight = this.$refs.eventList.offsetHeight;

			if (scrollTop + 1 >= scrollHeight - offsetHeight)
			{
				await this.$store.dispatch('setEventsUpdating', true);
				const events = await this.getNext();

				await this.$store.dispatch('setEvents', events);
				await this.$store.dispatch('setEventsUpdating', false);
			}

			if (scrollTop <= 0)
			{
				await this.$store.dispatch('setEventsUpdating', true);
				const events = await this.getPrevious();

				await this.$store.dispatch('setEvents', events);
				await this.$store.dispatch('setEventsUpdating', false);

				this.$refs.eventList.scrollTop += this.$refs.eventList.scrollHeight - scrollHeight;
			}
		},
		getNext(): Promise<EventModel[]>
		{
			if (this.isFilterMode)
			{
				return EventManager.filterNext();
			}

			return EventManager.getNext(this.selectedCategoryId);
		},
		getPrevious(): Promise<EventModel[]>
		{
			if (this.isFilterMode)
			{
				return EventManager.filterPrevious();
			}

			return EventManager.getPrevious(this.selectedCategoryId);
		},
		setEventRef(ref: ?{ event: EventModel, $el: HTMLElement }): void
		{
			if (!ref)
			{
				return;
			}

			const { event, $el } = ref;

			this.eventRefs[event.uniqueId] = $el;
			if (event.isNew)
			{
				this.observer.observe($el);
			}
		},
	},
	created(): void
	{
		this.initObserver();
	},
	mounted(): void
	{
		this.scrollToUpcomingEvent();
		void this.loadOnScroll();
		this.$refs.eventList.addEventListener('scroll', this.loadOnScroll);
	},
	beforeUnmount(): void
	{
		this.observer.disconnect();
		this.$refs.eventList.removeEventListener('scroll', this.loadOnScroll);
	},
	components: {
		Event,
		EmptyState,
	},
	template: `
		<div class="calendar-open-events-list-events-list --calendar-scroll-bar" ref="eventList">
			<Event
				v-for="event of sortedEvents"
				:event="event"
				:data-event-id="event.id"
				:ref="setEventRef"
			/>
			<EmptyState v-if="events.length === 0"/>
		</div>
	`,
};
