import { Runtime } from 'main.core';

export const SearchBox = {
	name: 'SearchBox',
	debouncedSetSearchQuery: null,
	created()
	{
		const setSearchQuery = (query) => {
			this.$store.dispatch('accessRights/search', { query });
		};

		this.debouncedSetSearchQuery = Runtime.debounce(setSearchQuery, 200);
	},
	computed: {
		searchQuery: {
			get(): string {
				return this.$store.state.accessRights.searchQuery;
			},
			set(query: string): void {
				this.debouncedSetSearchQuery(query);
			},
		},
	},
	template: `
		<div
			class="ui-ctl ui-ctl-after-icon ui-access-rights-v2-search">
			<input
				type="text"
				class="ui-ctl-element ui-ctl-textbox ui-access-rights-v2-search-input"
				:placeholder="$Bitrix.Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_SEARCH_PLACEHOLDER')"
				v-model="searchQuery"
				ref="searchInput"
			>
			<a 
				class="ui-icon-set --o-search ui-access-rights-v2-search-icon"
				@click="this.$refs.searchInput.focus()"
			></a>
		</div>
	`,
};
