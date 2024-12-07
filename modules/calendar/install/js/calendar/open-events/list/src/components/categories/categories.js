import { mapGetters } from 'ui.vue3.vuex';
import { CategoriesHeader } from './categories-header';
import { CategoryList } from './category-list';
import './categories.css';

export const Categories = {
	computed: {
		...mapGetters({
			isFilterMode: 'isFilterMode',
		}),
	},
	components: {
		CategoriesHeader,
		CategoryList,
	},
	template: `
		<div class="calendar-open-events-list-categories" :class="{ '--filter': isFilterMode }" >
			<CategoriesHeader/>
			<CategoryList/>
		</div>
	`,
};
