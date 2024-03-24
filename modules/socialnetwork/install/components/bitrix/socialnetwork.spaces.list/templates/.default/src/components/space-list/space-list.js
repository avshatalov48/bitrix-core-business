import { SpacesListStates } from '../../const/spaces-list-state';
import { ContextMenuCollection } from '../context-menu/context-menu-collection';
import { Space } from '../space/space';
import { SpaceAddForm } from '../space-add-form/space-add-form';
import { LoadServiceInterface } from '../../api/load/load-service-interface';
import { SpaceListAddButton } from './space-list-add-button';
import { EventTypes } from '../../const/event';
import { Loader } from '../loader/loader';
import { Modes } from '../../const/mode';
import { BaseEvent } from 'main.core.events';

// @vue/component

const PAGINATION_OFFSET = 20;

export const SpaceList = {
	components: {
		Space,
		Loader,
		SpaceListAddButton,
		SpaceAddForm,
	},
	emits: ['isSpaceAddFormShown'],
	data(): Object
	{
		return {
			isScrollLoading: false,
			isLoading: false,
			modes: Modes,
			doShowLowerSpaceAddForm: false,
			doShowUpperSpaceAddForm: false,
			skeletonItemsAmount: 25,
		};
	},
	props: {
		mode: {
			type: String,
			required: true,
		},
		spaces: {
			type: Array,
			required: true,
			default: [],
		},
		spaceInvitations: {
			type: Array,
			required: false,
			default: [],
		},
		canCreateGroup: Boolean,
		spacesCountForLoad: {
			type: Number,
			required: true,
		},
		serviceInstance: {
			type: Object,
			required: true,
		},
		subtitle: {
			type: String,
			required: false,
			default: '',
		},
		isShown: {
			type: Boolean,
			required: true,
		},
	},
	created()
	{
		this.$bitrix.eventEmitter.subscribe(
			EventTypes.tryToLoadSpacesIfHasNoScrollbar,
			this.tryToLoadSpacesIfHasNoScrollbarHandler,
		);
		this.$bitrix.eventEmitter.subscribe(EventTypes.showLoader, this.showLoaderHandler);
		this.$bitrix.eventEmitter.subscribe(EventTypes.hideLoader, this.hideLoaderHandler);
		this.$bitrix.eventEmitter.subscribe(EventTypes.showUpperSpaceAddForm, this.showUpperSpaceAddFormHandler);
		this.$bitrix.eventEmitter.subscribe(EventTypes.hideSpaceAddForm, this.hideSpaceAddFormHandler);
	},
	beforeUnmount()
	{
		this.$bitrix.eventEmitter.unsubscribe(
			EventTypes.tryToLoadSpacesIfHasNoScrollbar,
			this.tryToLoadSpacesIfHasNoScrollbarHandler,
		);
		this.$bitrix.eventEmitter.unsubscribe(EventTypes.showLoader, this.showLoaderHandler);
		this.$bitrix.eventEmitter.unsubscribe(EventTypes.hideLoader, this.hideLoaderHandler);
		this.$bitrix.eventEmitter.unsubscribe(EventTypes.showUpperSpaceAddForm, this.showUpperSpaceAddFormHandler);
		this.$bitrix.eventEmitter.unsubscribe(EventTypes.hideSpaceAddForm, this.hideSpaceAddFormHandler);
	},
	computed: {
		service(): LoadServiceInterface
		{
			return this.serviceInstance;
		},
		hasScrollbar(): boolean
		{
			return this.$refs.list.scrollHeight > this.$refs.list.clientHeight;
		},
		loadingClass(): string
		{
			return this.isLoading ? '--loading' : '';
		},
		filterMode(): string
		{
			return this.$store.state.main.selectedFilterModeType;
		},
		doShowSubtitle(): boolean
		{
			return this.subtitle.length > 0;
		},
		doShowSpaceListAddButton(): boolean
		{
			return this.canCreateGroup
				&& this.mode === this.modes.recent
				&& this.spaces.length <= 5
				&& !this.isSpaceAddFormShown
			;
		},
		doShowInvitations(): boolean
		{
			return this.mode === this.modes.recent && this.spaceInvitations.length > 0;
		},
		isSpaceAddFormShown(): boolean
		{
			return this.doShowLowerSpaceAddForm || this.doShowUpperSpaceAddForm;
		},
		listState(): string
		{
			return this.$store.getters.spacesListState;
		},
	},
	watch: {
		filterMode()
		{
			this.scrollToTop();
			this.isLoading = false;
		},
		isShown()
		{
			if (this.isShown === true)
			{
				const isSpaceListScrolled = this.$refs.list.scrollTop > 0;
				this.$bitrix.eventEmitter.emit(EventTypes.spaceListShown, { isSpaceListScrolled, mode: this.mode });
			}
		},
		doShowUpperSpaceAddForm()
		{
			if (this.doShowUpperSpaceAddForm === true)
			{
				this.doShowLowerSpaceAddForm = false;
			}
		},
		isSpaceAddFormShown()
		{
			this.$emit('isSpaceAddFormShown', this.isSpaceAddFormShown);
		},
	},
	methods: {
		tryToLoadSpacesIfHasNoScrollbarHandler(event: BaseEvent)
		{
			const mode = event.data;
			if (this.isProperMode(mode) && !this.hasScrollbar && this.service.canLoadSpaces())
			{
				this.loadSpaces();
			}
		},
		showLoaderHandler(event: BaseEvent)
		{
			const mode = event.data;
			if (this.isProperMode(mode))
			{
				this.isLoading = true;
			}
		},
		hideLoaderHandler(event: BaseEvent)
		{
			const mode = event.data;
			if (this.isProperMode(mode))
			{
				this.isLoading = false;
			}
		},
		showUpperSpaceAddFormHandler()
		{
			if (this.mode === this.modes.recent)
			{
				this.doShowUpperSpaceAddForm = true;
			}
		},
		showLowerSpaceAddForm()
		{
			this.doShowLowerSpaceAddForm = true;
		},
		hideSpaceAddFormHandler()
		{
			this.doShowLowerSpaceAddForm = false;
			this.doShowUpperSpaceAddForm = false;

			if (this.listState === SpacesListStates.expanded)
			{
				this.$bitrix.eventEmitter.emit(EventTypes.changeSpaceListState, SpacesListStates.collapsed);
			}
		},
		isProperMode(mode: string): boolean
		{
			return this.mode === mode;
		},
		scrollToTop()
		{
			this.$refs.list.scrollTop = 0;
		},
		onScroll(event)
		{
			const target = event.target;
			const isSpaceListScrolled = target.scrollTop > 0;
			this.$bitrix.eventEmitter.emit(EventTypes.spaceListScroll, { isSpaceListScrolled, mode: this.mode });

			const listRemainingSpace = target.scrollHeight - target.offsetHeight;
			const listScroll = target.scrollTop;

			const isScrolledToBottom = listScroll > (listRemainingSpace - PAGINATION_OFFSET);

			if (!this.isScrollLoading && this.service.canLoadSpaces() && isScrolledToBottom)
			{
				this.loadSpaces();
			}

			ContextMenuCollection.getInstance().destroy();
		},
		loadSpaces()
		{
			this.isScrollLoading = true;
			this.service.loadSpaces({
				loadedSpacesCount: this.spacesCountForLoad,
				filterMode: this.filterMode,
			}).then((result) => {
				this.$store.dispatch('addSpacesToView', { mode: this.mode, spaces: result.spaces });
				this.isScrollLoading = false;
			}).catch(() => {
				setTimeout(() => {
					this.isScrollLoading = false;
				}, 5000);
			});
		},
	},
	template: `
		<span>
			<Loader v-if="isLoading" :config="{offset: {left: '0px', top: '40vh'}}"/>
			<div
				@scroll="onScroll"
				class="sn-spaces__list-content"
				:class="loadingClass"
				ref="list"
				data-id="spaces-list-content"
			>
				<div v-show="doShowSubtitle" class="sn-spaces__list-subtitle">
					{{subtitle}}
				</div>
				<SpaceAddForm v-if="doShowUpperSpaceAddForm"/>
				<div class="sn-spaces__list-item_invitation" v-if="doShowInvitations">
					<Space
						v-for="spaceInvitation in spaceInvitations"
						:key="spaceInvitation.id"
						:space="spaceInvitation"
						:mode="mode"
						:isInvitation="true"
					/>
				</div>
				<Space 
					v-for="space in spaces"
					:key="space.id"
					:space="space"
					:mode="mode"
				/>
				<SpaceListAddButton
					v-if="doShowSpaceListAddButton"
					@click="showLowerSpaceAddForm"
					data-id="spaces-list-add-space-button"
				/>
				<SpaceAddForm v-if="doShowLowerSpaceAddForm"/>
				<span v-show="isScrollLoading">
					<div v-for="index in skeletonItemsAmount" :key="index" class="sn-spaces__list-skeleton-item"></div>
				</span>
			</div>
		</span>
	`,
};
