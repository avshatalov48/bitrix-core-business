import { BaseEvent } from 'main.core.events';
import { Loader } from 'main.loader';
import { mapGetters } from 'ui.vue3.vuex';
import { EventListTitle } from './event-list-title';
import { EventList } from './event-list';
import { EventManager } from '../../data-manager/event-manager/event-manager';
import { EventModel } from '../../model/event/open-event';

export const Events = {
	computed: {
		...mapGetters({
			selectedCategoryId: 'selectedCategoryId',
			areEventsUpdating: 'areEventsUpdating',
			isFilterMode: 'isFilterMode',
			events: 'events',
		}),
	},
	mounted(): void
	{
		EventManager.subscribe('update', this.eventManagerUpdateHandler);
		EventManager.subscribe('delete', this.eventManagerDeleteHandler);

		new Loader().show(this.$refs.events);
	},
	beforeUnmount(): void
	{
		EventManager.unsubscribe('update', this.eventManagerUpdateHandler);
		EventManager.unsubscribe('delete', this.eventManagerDeleteHandler);
	},
	methods: {
		async eventManagerUpdateHandler(event: BaseEvent)
		{
			const { eventId } = event.getData();

			const events = await this.getEvents();

			if (!events.find((it) => it.id === eventId))
			{
				return;
			}

			this.$store.dispatch('setEvents', events);
		},

		async eventManagerDeleteHandler(event: BaseEvent): Promise<void>
		{
			const { eventId } = event.getData();

			if (!this.events.find(it => it.id === eventId))
			{
				return;
			}

			const events = await this.getEvents();
			this.$store.dispatch('setEvents', events);
		},

		async getEvents(): Promise<EventModel[]>
		{
			if (this.isFilterMode)
			{
				return EventManager.filterEvents();
			}

			return EventManager.getEvents(this.selectedCategoryId);
		},
	},
	components: {
		EventListTitle,
		EventList,
	},
	template: `
		<div
			class="calendar-open-events-list-events"
			:class="{ '--updating': areEventsUpdating }"
			ref="events"
		>
			<EventListTitle/>
			<EventList/>
		</div>
	`,
};
