import { mapGetters } from 'ui.vue3.vuex';
import { Categories } from './categories/categories';
import { Events } from './events/events';

import './components.css';

export const BaseComponent = {
	computed: {
		...mapGetters({
			areEventsLoading: 'areEventsLoading',
		}),
	},
	components: {
		Categories,
		Events,
	},
	template: `
		<Categories/>
		<div class="calendar-open-events-list-events-loader" v-if="areEventsLoading"></div>
		<Events v-else/>
	`,
};