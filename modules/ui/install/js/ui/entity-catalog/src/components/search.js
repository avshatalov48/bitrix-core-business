import { BaseEvent } from 'main.core.events';
import { Loc, debounce, Type } from 'main.core';

export const Search = {
	emits: ['onSearch'],
	name: 'ui-entity-catalog-titlebar-search',
	data(): Object
	{
		return {
			opened: false,
			debounceSearchHandler: null,
			queryString: '',
			showClearSearch: false,
		};
	},
	watch:{
		queryString(newString)
		{
			this.showClearSearch = this.opened && this.$refs['search-input'] && Type.isStringFilled(newString);
		},
	},
	created()
	{
		this.debounceSearchHandler = debounce((event) => {
			this.onSearch(event.target.value);
		}, 255);
	},
	methods: {
		openSearch()
		{
			this.opened = true;
			this.$nextTick(() => {
				this.$refs['search-input'].focus();
			});
		},
		onSearch(queryString)
		{
			this.queryString = queryString;
			this.$emit(
				'onSearch',
				new BaseEvent({data: {queryString: queryString ? queryString.toString() : ''}})
			);
		},
		clearSearch()
		{
			if (this.showClearSearch)
			{
				this.$refs['search-input'].value = '';
				this.onSearch('');
			}
		}
	},
	template: `
		<div class="ui-ctl ui-ctl-after-icon ui-ctl-w100 ui-ctl-round" @click.once="openSearch">
			<a 
				:class="{
					'ui-ctl-after': true,
					'ui-ctl-icon-search': !showClearSearch,
					'ui-ctl-icon-clear': showClearSearch
				}"
				@click="clearSearch"
			/>
			<input
				type="text"
				class="ui-ctl-element ui-ctl-textbox"
				placeholder="${Loc.getMessage('UI_JS_ENTITY_CATALOG_GROUP_LIST_SEARCH_PLACEHOLDER')}"
				ref="search-input"
				v-if="opened"
				@input="debounceSearchHandler"
			/>
		</div>
	`,
};