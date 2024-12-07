import { mapGetters } from 'ui.vue3.vuex';
import { CategoriesTitle } from './header/categories-title';
import { CategoriesSearch } from './header/categories-search';

export const CategoriesHeader = {
	computed: {
		...mapGetters({
			isSearchMode: 'isSearchMode',
		}),
	},
	components: {
		CategoriesTitle,
		CategoriesSearch,
	},
	template: `
		<div class="calendar-open-events-list-categories-title-container">
			<CategoriesSearch v-if="isSearchMode"/>
			<CategoriesTitle v-else/>
		</div>
	`,
};