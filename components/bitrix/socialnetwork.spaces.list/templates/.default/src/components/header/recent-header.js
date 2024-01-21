import { EventTypes } from '../../const/event';
import { FilterModes } from '../../const/filter-mode';
import { PopupMenu } from '../popup-menu/popup-menu';
import { Modes } from '../../const/mode';
import { RecentService } from '../../api/load/recent-service';

// @vue/component
export const RecentHeader = {
	components: {
		PopupMenu,
	},
	props: {
		canCreateGroup: Boolean,
	},
	emits: ['changeMode'],
	data(): Object
	{
		return {
			showModePopup: false,
			isSpaceListScrolled: false,
			filterModes: FilterModes,
		};
	},
	computed: {
		scrollClass(): string
		{
			return this.isSpaceListScrolled ? '--scroll-content' : '';
		},
		selectedFilterModeType(): string
		{
			return this.$store.state.main.selectedFilterModeType;
		},
		title(): string
		{
			const selectedType = this.selectedFilterModeType;
			const selectedMode = this.filterModes.find((mode) => mode.type === selectedType);

			return selectedMode ? this.loc(selectedMode.nameMessageId) : this.loc('SOCIALNETWORK_SPACES_TITLE');
		},
		selectFilterModeButtonIconModifier(): string
		{
			return `--${this.selectedFilterModeType}`;
		},
		popupMenuOptions(): Array
		{
			return this.filterModes.map((filterMode) => ({
				type: filterMode.type,
				name: this.loc(filterMode.nameMessageId),
				description: this.loc(filterMode.descriptionMessageId),
			}));
		},
		popupMenuButton(): Object
		{
			if (!this.canCreateGroup)
			{
				return null;
			}

			return {
				text: this.loc('SOCIALNETWORK_SPACES_LIST_MODE_POPUP_NEW_SPACE_BUTTON'),
				class: '--plus-30',
			};
		},
	},
	created()
	{
		this.$bitrix.eventEmitter.subscribe(EventTypes.spaceListScroll, this.handleListChanges);
		this.$bitrix.eventEmitter.subscribe(EventTypes.spaceListShown, this.handleListChanges);
	},
	beforeUnmount()
	{
		this.$bitrix.eventEmitter.unsubscribe(EventTypes.spaceListScroll, this.handleListChanges);
		this.$bitrix.eventEmitter.unsubscribe(EventTypes.spaceListShown, this.handleListChanges);
	},
	methods: {
		loc(phraseCode): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
		openPopup()
		{
			this.showModePopup = true;
		},
		handleListChanges(event)
		{
			const isSpaceListScrolled = event.data.isSpaceListScrolled;
			const mode = event.data.mode;
			if (mode === Modes.recent)
			{
				this.isSpaceListScrolled = isSpaceListScrolled;
			}
		},
		enableSearch()
		{
			this.$emit('changeMode', Modes.recentSearch);
		},
		async onChangeSelectedFilterMode(filterMode)
		{
			if (this.selectedFilterModeType !== filterMode)
			{
				const recentService = RecentService.getInstance();

				this.$bitrix.eventEmitter.emit(EventTypes.showLoader, Modes.recent);
				const result = await recentService.reloadSpaces({
					filterMode,
				});
				this.$store.dispatch('setSelectedFilterModeType', filterMode);
				this.$store.dispatch('clearSpacesViewByMode', Modes.recent);
				this.$store.dispatch('addSpacesToView', { mode: Modes.recent, spaces: result.spaces });
				this.$bitrix.eventEmitter.emit(EventTypes.hideLoader, Modes.recent);
			}
		},
		onCreateSpaceButtonClick()
		{
			this.$bitrix.eventEmitter.emit(EventTypes.showUpperSpaceAddForm);
		},
		showBtnArrow()
		{
			this.$bitrix.eventEmitter.emit(EventTypes.showBtnToggleBlock);
		},
	},
	template: `
		<div 
			class="sn-spaces__list-header" 
			:class="scrollClass"
			@mouseenter="showBtnArrow"
		>
			<div
				@click="openPopup"
				class="sn-spaces__list-header_name"
				ref="spaces-list-header-name"
				data-id="spaces-header-title"
			>
				<div class="sn-spaces__list-header_name-block">
					<div class="sn-spaces__list-header_title">
						{{ title }}
					</div>
					<div class="ui-icon-set --chevron-down" style='--ui-icon-set__icon-size: 15px;'>
					</div>
				</div>
				<div class="sn-spaces__list-header_btn-spaces" :class="selectFilterModeButtonIconModifier"></div>
			</div>
			<PopupMenu
				:options="popupMenuOptions"
				context="space-recent-header"
				:bind-element="$refs['spaces-list-header-name'] || {}"
				:selectedOption="selectedFilterModeType"
				:hint="loc('SOCIALNETWORK_SPACES_LIST_MODE_POPUP_BOTTOM_DESCRIPTION')"
				@changeSelectedOption="onChangeSelectedFilterMode"
				@popupMenuButtonClick="onCreateSpaceButtonClick"
				v-if="showModePopup"
				@close="showModePopup = false"
				:button="popupMenuButton"
			/>
			<button
				class="ui-btn ui-btn-light ui-btn-round ui-btn-xs sn-spaces__list-header_btn-search"
				@click="enableSearch"
				data-id="spaces-search-button"
			>
				<div class="ui-icon-set --search-2"></div>
			</button>
			<button
				v-if="canCreateGroup"
				class="ui-btn ui-btn-light ui-btn-round ui-btn-xs sn-spaces__list-header_btn-add"
				@click="onCreateSpaceButtonClick"
				data-id="spaces-header-add-space-button"
			>
				<div class="ui-icon-set --plus-30"></div>
			</button>
		</div>
	`,
};
