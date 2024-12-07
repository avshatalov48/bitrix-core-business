import { Event, Runtime, Type } from 'main.core';
import { CategoryManager } from '../../../data-manager/category-manager/category-manager';
import 'ui.icon-set.actions';
import './header.css';

export const CategoriesSearch = {
	created(): void
	{
		this.searchDebounced = Runtime.debounce(this.search, 500, this);
	},
	mounted(): void
	{
		this.$refs.input.focus();
		Event.bind(document, 'click', this.handleAutoHide, true);
	},
	unmounted(): void
	{
		Event.unbind(document, 'click', this.handleAutoHide, true);
	},
	methods: {
		handleAutoHide(event): void
		{
			if (this.shouldHideForm(event))
			{
				void this.closeSearch();
			}
		},
		shouldHideForm(event): boolean
		{
			const queryIsEmpty = !Type.isStringFilled(this.getSearchQuery());
			const clickOnSelf = this.$refs.search.contains(event.target);

			return queryIsEmpty && !clickOnSelf;
		},
		onCloseSearchClick(): void
		{
			void this.closeSearch();
		},
		async closeSearch(): Promise<void>
		{
			const categories = await CategoryManager.getCategories();

			await this.$store.dispatch('setCategories', categories);
			await this.$store.dispatch('setSearchMode', false);
		},
		async onSearchInput(): Promise<void>
		{
			const query = this.getSearchQuery();
			if (Type.isStringFilled(query))
			{
				this.searchDebounced(query);
			}
			else
			{
				const categories = await CategoryManager.getCategories();

				this.$store.dispatch('setCategories', categories);
			}
		},
		async search(query: string): Promise<void>
		{
			await this.$store.dispatch('setCategoriesQuery', query);

			const categories = await CategoryManager.searchCategories(query);

			if (query === this.getSearchQuery())
			{
				this.$store.dispatch('setCategories', categories);
			}
		},
		getSearchQuery(): string
		{
			return this.$refs.input.value.trim();
		},
	},
	template: `
		<div class="calendar-open-events-list-categories-search" ref="search">
			<input
				ref="input"
				class="calendar-open-events-list-categories-search-input"
				type="text"
				:placeholder="$Bitrix.Loc.getMessage('CALENDAR_OPEN_EVENTS_LIST_SEARCH_CATEGORY')"
				@input="onSearchInput()"
			>
			<div class="calendar-open-events-list-categories-close-search-button" @click="onCloseSearchClick()">
				<div class="ui-icon-set --cross-circle-70"></div>
			</div>
		</div>
	`,
};
