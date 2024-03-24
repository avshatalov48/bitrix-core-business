import { SpacesListStates } from '../const/spaces-list-state';
import { EventTypes } from '../const/event';
import { Modes } from '../const/mode';
import { RecentHeader } from './header/recent-header';
import { SearchHeader } from './header/search-header';
import { SpaceList } from './space-list/space-list';
import { mapGetters } from 'ui.vue3.vuex';
import { RecentService } from '../api/load/recent-service';
import { RecentSearchService } from '../api/load/recent-search-service';
import { SearchService } from '../api/load/search-service';
import { CollapsedModeToggleBlock } from './collapsed-mode-toggle-block/collapsed-mode-toggle-block';
import { Dom } from 'main.core';
import { BaseEvent } from 'main.core.events';

// @vue/component
export const BaseComponent = {
	components: {
		RecentHeader,
		SpaceList,
		SearchHeader,
		CollapsedModeToggleBlock,
	},
	data(): Object
	{
		return {
			mode: 'recent',
			modes: Modes,
			listNode: document.getElementById('sn-spaces-list'),
			isSpaceAddFormShownInRecentList: false,
		};
	},
	computed: {
		...mapGetters({
			recentSpaces: 'recentSpaces',
			recentSpacesCountForLoad: 'recentSpacesCountForLoad',
			recentSearchSpaces: 'recentSearchSpaces',
			recentSearchSpacesCountForLoad: 'recentSearchSpacesCountForLoad',
			searchSpaces: 'searchSpaces',
			searchSpacesCountForLoad: 'searchSpacesCountForLoad',
			spacesLoadedByCurrentSearchQueryCount: 'spacesLoadedByCurrentSearchQueryCount',
			canCreateGroup: 'canCreateGroup',
			spaceInvitations: 'spaceInvitations',
		}),
		doExpandCollapsedList(): boolean
		{
			const isCollapsedState = this.$store.getters.spacesListState === SpacesListStates.collapsed;
			const isSearchMode = [this.modes.search, this.modes.recentSearch].includes(this.mode);

			return isCollapsedState && (isSearchMode || this.isSpaceAddFormShownInRecentList);
		},
		doCollapseExpandedList(): boolean
		{
			const isExpandedState = this.$store.getters.spacesListState === SpacesListStates.expanded;
			const isRecentMode = this.modes.recent === this.mode;

			return isExpandedState && isRecentMode && !this.isSpaceAddFormShownInRecentList;
		},
	},
	watch: {
		doExpandCollapsedList()
		{
			if (this.doExpandCollapsedList)
			{
				this.changeSpaceListState(SpacesListStates.expanded);
			}
		},
		doCollapseExpandedList()
		{
			if (this.doCollapseExpandedList)
			{
				this.changeSpaceListState(SpacesListStates.collapsed);
			}
		},
	},
	created()
	{
		this.$bitrix.eventEmitter.subscribe(EventTypes.changeSpaceListState, this.changeSpaceListStateHandler);
		this.$bitrix.eventEmitter.subscribe(EventTypes.changeMode, this.changeModeHandler);
	},
	beforeUnmount()
	{
		this.$bitrix.eventEmitter.unsubscribe(EventTypes.changeSpaceListState, this.changeSpaceListStateHandler);
		this.$bitrix.eventEmitter.unsubscribe(EventTypes.changeMode, this.changeModeHandler);
	},
	methods: {
		loc(message: string): string
		{
			return this.$bitrix.Loc.getMessage(message);
		},
		changeModeHandler(event: BaseEvent)
		{
			const newMode = event.data;
			this.setMode(newMode);
		},
		setMode(mode)
		{
			this.mode = mode;
		},
		getRecentService(): RecentService
		{
			return RecentService.getInstance();
		},
		getRecentSearchService(): RecentSearchService
		{
			return RecentSearchService.getInstance();
		},
		getSearchService(): SearchService
		{
			return SearchService.getInstance();
		},
		changeSpaceListStateHandler(event)
		{
			const state = event.data;

			this.changeSpaceListState(state);
		},
		changeSpaceListState(state)
		{
			if (state === SpacesListStates.expanded && !Dom.hasClass(this.listNode, '--fixed'))
			{
				Dom.addClass(this.listNode, '--fixed');
			}
			else if (Dom.hasClass(this.listNode, '--fixed'))
			{
				Dom.removeClass(this.listNode, '--fixed');
			}

			this.$store.dispatch('setSpacesListState', state);
		},
		isSpaceAddFormShownHandler(isSpaceAddFormShown: boolean)
		{
			this.isSpaceAddFormShownInRecentList = isSpaceAddFormShown;
		},
	},
	template: `
		<div class="sn-spaces__list-wrapper">
			<RecentHeader
				v-if="mode === modes.recent"
				:canCreateGroup="canCreateGroup"
				@changeMode="setMode"
			/>
			<SearchHeader
				v-if="mode === modes.search || mode === modes.recentSearch"
				@changeMode="setMode"
			/>
			<SpaceList
				v-show="mode === modes.recent"
				@isSpaceAddFormShown="isSpaceAddFormShownHandler"
				:isShown="mode === modes.recent"
				:mode="modes.recent"
				:spaces="recentSpaces"
				:spaceInvitations="spaceInvitations"
				:canCreateGroup="canCreateGroup"
				:spacesCountForLoad="recentSpacesCountForLoad"
				:serviceInstance="getRecentService()"
			/>
			<SpaceList
				v-show="mode === modes.recentSearch"
				:isShown="mode === modes.recentSearch"
				:mode="modes.recentSearch"
				:spaces="recentSearchSpaces"
				:canCreateGroup="canCreateGroup"
				:spacesCountForLoad="recentSearchSpacesCountForLoad"
				:serviceInstance="getRecentSearchService()"
				:subtitle="loc('SOCIALNETWORK_SPACES_LIST_RECENT_SEARCH_LIST_TITLE')"
			/>
			<SpaceList
				v-show="mode === modes.search"
				:isShown="mode === modes.search"
				:mode="modes.search"
				:spaces="searchSpaces"
				:canCreateGroup="canCreateGroup"
				:spacesCountForLoad="searchSpacesCountForLoad"
				:serviceInstance="getSearchService()"
				:subtitle="loc('SOCIALNETWORK_SPACES_LIST_SEARCH_LIST_TITLE')"
			/>
		</div>
		<CollapsedModeToggleBlock />
	`,
};
