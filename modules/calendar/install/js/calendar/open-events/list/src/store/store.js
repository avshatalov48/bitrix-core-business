import { createStore } from 'ui.vue3.vuex';
import { CategoriesSearchStore } from './categories-search';
import { CategoriesStore } from './categories-store';
import { EventsStore } from './events-store';

export const Store = createStore({
	modules: {
		categories: CategoriesStore,
		categoriesSearch: CategoriesSearchStore,
		events: EventsStore,
	},
});