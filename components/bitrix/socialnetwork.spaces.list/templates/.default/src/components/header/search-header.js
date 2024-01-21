import { Modes } from '../../const/mode';
import { EventTypes } from '../../const/event';
import { Runtime } from 'main.core';
import { mapGetters } from 'ui.vue3.vuex';
import { SearchService } from '../../api/load/search-service';

import type { SpaceModel } from '../../model/space-model';

export const SearchHeader = {
	data(): Object
	{
		return {
			searchQuery: '',
			isSpaceListScrolled: false,
		};
	},
	created()
	{
		this.startSearchDebounced = Runtime.debounce(this.startSearch, 500, this);
		this.$bitrix.eventEmitter.subscribe(EventTypes.spaceListScroll, this.handleListChanges);
		this.$bitrix.eventEmitter.subscribe(EventTypes.spaceListShown, this.handleListChanges);
	},
	beforeUnmount()
	{
		this.$bitrix.eventEmitter.unsubscribe(EventTypes.spaceListScroll, this.handleListChanges);
		this.$bitrix.eventEmitter.unsubscribe(EventTypes.spaceListShown, this.handleListChanges);
	},
	computed: {
		...mapGetters({
			spaces: 'spaces',
		}),
		scrollClass(): string
		{
			return this.isSpaceListScrolled ? '--scroll-content' : '';
		},
	},
	methods: {
		loc(message: string): string
		{
			return this.$bitrix.Loc.getMessage(message);
		},
		closeSearch()
		{
			this.searchQuery = '';
			this.$emit('changeMode', Modes.recent);
		},
		handleListChanges(event)
		{
			const isSpaceListScrolled = event.data.isSpaceListScrolled;
			const mode = event.data.mode;
			if ([Modes.search, Modes.recentSearch].includes(mode))
			{
				this.isSpaceListScrolled = isSpaceListScrolled;
			}
		},
		onInputChange()
		{
			SearchService.getInstance().searchString = this.searchQuery;
			if (this.searchQuery.length === 0)
			{
				this.$emit('changeMode', Modes.recentSearch);
			}
			else
			{
				this.startSearchDebounced();
			}
		},
		startSearch()
		{
			if (this.searchQuery.length > 0)
			{
				SearchService.getInstance().hasMoreSpacesToLoad = true;
				this.$store.dispatch('clearSpacesViewByMode', Modes.search);

				const searchResult = this.spaces.filter((space: SpaceModel) => {
					return space.name.toLowerCase().includes(this.searchQuery.toLowerCase());
				});

				const spaceIds = searchResult.map((space: SpaceModel) => space.id);
				this.$store.dispatch('setLocalSearchResult', spaceIds);
				this.$emit('changeMode', Modes.search);

				setTimeout(() => {
					this.$bitrix.eventEmitter.emit(EventTypes.tryToLoadSpacesIfHasNoScrollbar, Modes.search);
				}, 80);
			}
		},
	},
	mounted()
	{
		this.$refs.input.focus();
	},
	template: `
		<div class="sn-spaces__list-header" :class="scrollClass">
			<div class="sn-spaces__search ui-ctl ui-ctl-textbox ui-ctl-after-icon ui-ctl-w100 ui-ctl-sm">
				<input
					type="text"
					class="ui-ctl-element"
					:placeholder="loc('SOCIALNETWORK_SPACES_LIST_SEARCH_INPUT_PLACEHOLDER')"
					v-model.trim="searchQuery"
					ref="input"
					@input="onInputChange"
					data-id="spaces-search-input"
				>
				<button
					class="sn-spaces__search-clear ui-ctl-after"
					@click="closeSearch"
					data-id="spaces-close-search-button"
				>
					<div class="ui-icon-set --cross-circle-70"></div>
				</button>
			</div>
		</div>
	`,
};
