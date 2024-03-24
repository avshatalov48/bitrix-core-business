import { Event } from 'main.core';
import { SpacesListStates } from '../../const/spaces-list-state';
import { FilterModeTypes } from '../../const/filter-mode';
import { LinkManager } from '../../util/link-manager';
import { Client } from '../../api/client';
import { SpaceUserRoles } from '../../const/space';
import { Modes } from '../../const/mode';
import { PopupShortSpace } from '../popup-short-space/popup-short-space';
import { ContextMenu } from '../context-menu/context-menu';
import { SpaceContent } from './space-content';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { EventTypes } from '../../const/event';
import { Controller } from 'socialnetwork.controller';

import type { SpaceModel } from '../../model/space-model';

// @vue/component
export const Space = {
	components: {
		PopupShortSpace,
		SpaceContent,
	},
	props: {
		space: {
			type: Object,
			default: () => {},
		},
		mode: {
			type: String,
			required: true,
		},
		isInvitation: {
			type: Boolean,
			default: false,
		},
	},
	data(): Object
	{
		return {
			modes: Modes,
			spaceUserRoles: SpaceUserRoles,
			showModePopup: false,
		};
	},
	computed: {
		popupShortSpaceOptions(): Object
		{
			return {
				left: this.widthItem,
			};
		},
		selectedFilterModeType(): string
		{
			return this.$store.state.main.selectedFilterModeType;
		},
		classModifiers(): string
		{
			const isRecentMode = this.mode === this.modes.recent;
			const classModifiers = [];

			if (this.spaceModel.isPinned && this.selectedFilterModeType === FilterModeTypes.my && isRecentMode)
			{
				classModifiers.push('--pinned');
			}

			if (this.spaceModel.isSelected && isRecentMode)
			{
				classModifiers.push('--active');
			}

			if (this.isInvitation)
			{
				classModifiers.push('--invitation');
			}

			return classModifiers.join(' ');
		},
		link(): string
		{
			return LinkManager.getSpaceLink(this.spaceModel.id);
		},
		spaceModel(): SpaceModel
		{
			return this.space;
		},
		widthItem(): number
		{
			return this.$refs.link.getBoundingClientRect().width;
		},
		isCommon(): boolean
		{
			return this.spaceModel.id === 0;
		},
	},
	created()
	{
		EventEmitter.subscribe(EventTypes.openSpaceFromContextMenu, this.openSpaceFromContextMenu);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventTypes.openSpaceFromContextMenu, this.openSpaceFromContextMenu);
	},
	methods: {
		loc(message: string): string
		{
			return this.$bitrix.Loc.getMessage(message);
		},
		async openSpaceFromContextMenu(event: BaseEvent)
		{
			const spaceId = event.data.spaceId;
			if (this.spaceModel.id === spaceId)
			{
				await this.onSpaceClick();
			}
		},
		getPinMessage(): string
		{
			return this.spaceModel.isPinned
				? this.loc('SOCIALNETWORK_SPACES_LIST_SPACE_UNPIN')
				: this.loc('SOCIALNETWORK_SPACES_LIST_SPACE_PIN')
			;
		},
		getFollowMessage(): string
		{
			return this.spaceModel.follow
				? this.loc('SOCIALNETWORK_SPACES_LIST_SPACE_UNFOLLOW')
				: this.loc('SOCIALNETWORK_SPACES_LIST_SPACE_FOLLOW')
			;
		},
		getOpenMessage(): string
		{
			return this.loc('SOCIALNETWORK_SPACES_LIST_SPACE_OPEN');
		},
		getCopyLinkMessage(): string
		{
			return this.loc('SN_SPACES_LIST_SPACE_COPY_LINK');
		},
		getLogoutMessage(): string
		{
			return this.loc('SN_SPACES_LIST_SPACE_LOGOUT');
		},
		async onSpaceClick()
		{
			const modeBeforeClick = this.mode;

			if (this.mode !== Modes.recent)
			{
				this.$bitrix.eventEmitter.emit(EventTypes.changeMode, Modes.recent);
			}

			this.$store.dispatch('setSelectedSpace', this.spaceModel.id);

			BX.Socialnetwork.Spaces.space.reloadPageContent(
				LinkManager.getSpaceLink(this.spaceModel.id),
			);

			if ([Modes.recentSearch, Modes.search].includes(modeBeforeClick))
			{
				await Client.addSpaceToRecentSearch(this.spaceModel.id);
			}
		},
		async onSpaceContextMenuClick(event: PointerEvent)
		{
			event.preventDefault();
			if (this.isCommon || this.spaceModel.userRole !== SpaceUserRoles.member)
			{
				return;
			}

			const menu = new ContextMenu({
				spaceId: this.spaceModel.id,
				bindElement: event.currentTarget,
				path: this.link,
				isSelected: this.spaceModel.isSelected,
				permissions: this.spaceModel.permissions,
				listFilter: this.selectedFilterModeType,
				listMode: this.mode,
				pinMessage: this.getPinMessage(),
				followMessage: this.getFollowMessage(),
				openMessage: this.getOpenMessage(),
				copyLinkMessage: this.getCopyLinkMessage(),
				logoutMessage: this.getLogoutMessage(),
			});
			menu.subscribe('openCommonSpace', () => {
				Controller.openCommonSpace();
			});

			menu.toggle();
		},
		openPopup()
		{
			if (this.$store.getters.spacesListState === SpacesListStates.collapsed)
			{
				this.showModePopup = true;
			}
		},
		closePopup()
		{
			if (this.$store.getters.spacesListState === SpacesListStates.collapsed)
			{
				const bindElement = this.$refs.link;
				const popupContainer = this.$refs['popup-item']?.$refs['popup-content'];
				let hoverElement = null;

				Event.bind(document, 'mouseover', (event) => {
					hoverElement = event.target;
				});

				setTimeout(() => {
					if (
						!popupContainer
						|| (
							!bindElement.contains(hoverElement)
							&& !popupContainer.contains(hoverElement)
						)
					)
					{
						this.showModePopup = false;
					}
				}, 100);
			}
		},
	},
	template: `
		<a
			ref="link"
			class="sn-spaces__list-item"
			:class="classModifiers"
			data-id="spaces-list-element"
			@click="onSpaceClick"
			@contextmenu="onSpaceContextMenuClick"
			@mouseenter="openPopup"
			@mouseleave="closePopup"
		>
			<PopupShortSpace
				ref="popup-item"
				:options="popupShortSpaceOptions"
				:space="space"
				:mode="mode"
				:link="link"
				:is-invitation="isInvitation"
				context="popup-short-space"
				:bind-element="$refs['link'] || {}"
				v-if="showModePopup"
				@close="showModePopup = false"
				@closeSpacePopup="closePopup"
				@popupSpaceClick="onSpaceClick"
			/>
			<SpaceContent 
				:space="space" 
				:mode="mode"
				:is-invitation="isInvitation"
			/>
		</a>
	`,
};
