import { mapGetters } from 'ui.vue3.vuex';
import { TitleMenu } from './parts/title-menu';

import './css/event-list-title.css';

export const EventListTitle = {
	computed: {
		...mapGetters({
			isFilterMode: 'isFilterMode',
			category: 'selectedCategory',
		}),
		title(): string
		{
			if (this.isFilterMode)
			{
				return this.$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_SEARCH_RESULT');
			}

			return this.category?.name;
		},
	},
	components: {
		TitleMenu,
	},
	template: `
		<div class="calendar__open-event__list-header">
			<div class="calendar__open-event__list-header__title" :title="title">
				{{ title }}
			</div>
			<div class="calendar__open-event__list-header__icon ui-icon-set --lock" v-if="category.closed"></div>
			<TitleMenu v-if="!isFilterMode && category.id" :category="category"/>
		</div>
	`,
};
