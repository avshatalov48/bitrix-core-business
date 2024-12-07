import { BitrixVue, VueCreateAppResult } from 'ui.vue3';
import { Filter } from 'calendar.open-events.filter';
import { mapGetters } from 'ui.vue3.vuex';
import { BaseComponent } from './components/base-component';
import { CategoryManager } from './data-manager/category-manager/category-manager';
import { EventManager } from './data-manager/event-manager/event-manager';
import { CategoryModel } from './model/category/category';
import { EventModel } from './model/event/open-event';
import { Store } from './store/store';

import './list.css';

type Params = {
	container: HTMLElement,
	filterId: string,
};

export class List
{
	#params: Params;
	#application: VueCreateAppResult;

	constructor(params: Params)
	{
		this.#params = params;

		this.#mountApplication();
	}

	#mountApplication(): void
	{
		this.#application = BitrixVue.createApp(
			{
				name: 'List',
				props: {
					filterId: String,
				},
				data(): Object
				{
					return {
						isLoading: true,
					};
				},
				computed: {
					...mapGetters({
						selectedCategoryId: 'selectedCategoryId',
					}),
				},
				async mounted(): void
				{
					this.bindFilter(this.filterId);

					const categories: CategoryModel[] = await CategoryManager.getCategories();
					const events: EventModel[] = await EventManager.getEvents(this.selectedCategoryId);

					this.isLoading = false;

					this.$store.dispatch('setCategories', categories);
					this.$store.dispatch('setEvents', events);
				},
				methods: {
					bindFilter(filterId: string): void
					{
						const filter = new Filter(filterId);

						EventManager.setFilter(filter);

						filter.subscribe('beforeApply', () => {
							this.isLoading = true;
						});

						filter.subscribe('apply', async () => {
							const events = await EventManager.filterEvents();

							this.$store.dispatch('setEvents', events);
							this.$store.dispatch('setFilterMode', true);

							this.isLoading = false;
						});

						filter.subscribe('clear', async () => {
							const events = await EventManager.getEvents(this.selectedCategoryId);

							this.$store.dispatch('setEvents', events);
							this.$store.dispatch('setFilterMode', false);

							this.isLoading = false;
						});
					},
				},
				components: {
					BaseComponent,
				},
				template: `
					<div class="calendar-open-events-list-loader" v-if="isLoading"></div>
					<BaseComponent v-else/>
				`,
			},
			{
				filterId: this.#params.filterId,
			},
		);
		this.#application.use(Store);
		this.#application.mount(this.#params.container);
	}
}
