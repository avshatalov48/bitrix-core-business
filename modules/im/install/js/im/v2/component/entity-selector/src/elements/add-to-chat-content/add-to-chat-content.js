import { EventEmitter } from 'main.core.events';
import { TagSelector } from 'ui.entity-selector';

import { ChatType, EventType, UserType } from 'im.v2.const';
import { ChatSearch } from 'im.v2.component.search.chat-search';
import { Button as MessengerButton, ButtonSize, ButtonColor } from 'im.v2.component.elements';
import { ChannelManager } from 'im.v2.lib.channel';

import './add-to-chat-content.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelUser } from 'im.v2.model';

const searchConfig = Object.freeze({
	chats: false,
	users: true,
});

const SEARCH_ENTITY_ID = 'user';
const DEFAULT_CONTAINER_HEIGHT = 600;

// @vue/component
export const AddToChatContent = {
	name: 'AddToChatContent',
	components: { ChatSearch, MessengerButton },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		isLoading: {
			type: Boolean,
			required: false,
		},
		height: {
			type: Number,
			default: DEFAULT_CONTAINER_HEIGHT,
		},
	},
	emits: ['inviteMembers'],
	data(): JsonObject
	{
		return {
			searchQuery: '',
			showHistory: true,
			selectedItems: new Set(),
		};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		searchConfig: () => searchConfig,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isChat(): boolean
		{
			return this.dialog.type !== ChatType.user;
		},
		isCollab(): boolean
		{
			return this.dialog.type === ChatType.collab;
		},
		isOpenLines(): boolean
		{
			return this.dialog.type === ChatType.lines;
		},
		isChannel(): boolean
		{
			return ChannelManager.isChannel(this.dialogId);
		},
		showHistoryOption(): boolean
		{
			return !this.isCollab && this.isChat && !this.isChannel && !this.isOpenLines;
		},
		containerStyles(): {height: string}
		{
			return {
				height: `${this.height}px`,
			};
		},
	},
	created()
	{
		this.membersSelector = this.getTagSelector();
	},
	mounted()
	{
		this.membersSelector.renderTo(this.$refs['tag-selector']);
		this.membersSelector.focusTextBox();
	},
	activated()
	{
		this.membersSelector.hideAddButton();
		this.membersSelector.showTextBox();
		this.membersSelector.focusTextBox();
	},
	methods:
	{
		getTagSelector(): TagSelector
		{
			let timeoutId = null;

			return new TagSelector({
				maxHeight: 111,
				showAddButton: false,
				showTextBox: true,
				addButtonCaption: this.loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_ADD_MSGVER_1'),
				addButtonCaptionMore: this.loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_ADD_MORE'),
				showCreateButton: false,
				events: {
					onBeforeTagAdd: () => {
						clearTimeout(timeoutId);
					},
					onAfterTagAdd: (event) => {
						const { tag } = event.getData();
						this.selectedItems.add(tag.id);
						this.focusSelector();
					},
					onKeyUp: (event) => {
						const { event: keyboardEvent } = event.getData();
						EventEmitter.emit(EventType.search.keyPressed, { keyboardEvent });
					},
					onBeforeTagRemove: () => {
						clearTimeout(timeoutId);
					},
					onAfterTagRemove: (event) => {
						const { tag } = event.getData();
						this.selectedItems.delete(tag.id);
						this.focusSelector();
					},
					onInput: () => {
						this.searchQuery = this.membersSelector.getTextBoxValue();
					},
					onBlur: () => {
						const inputText = this.membersSelector.getTextBoxValue();
						if (inputText.length > 0)
						{
							return;
						}

						timeoutId = setTimeout(() => {
							this.membersSelector.hideTextBox();
							this.membersSelector.showAddButton();
						}, 200);
					},
					onContainerClick: () => {
						this.focusSelector();
					},
				},
			});
		},
		focusSelector()
		{
			this.membersSelector.hideAddButton();
			this.membersSelector.showTextBox();
			this.membersSelector.focusTextBox();
		},
		onSelectItem(event: {dialogId: string, service: Object, selectedStatus: boolean})
		{
			const { dialogId, nativeEvent } = event;
			if (this.selectedItems.has(dialogId))
			{
				const tag = {
					id: dialogId,
					entityId: SEARCH_ENTITY_ID,
				};

				this.membersSelector.removeTag(tag);
			}
			else
			{
				const tag = this.getTagByDialogId(dialogId);
				this.membersSelector.addTag(tag);
			}

			this.membersSelector.clearTextBox();
			if (!nativeEvent.altKey)
			{
				this.searchQuery = '';
			}
		},
		getTagByDialogId(dialogId: string): Object
		{
			const user: ImModelUser = this.$store.getters['users/get'](dialogId, true);
			const isExtranet = user.type === UserType.extranet;
			const entityType = isExtranet ? 'extranet' : 'employee';

			return {
				id: dialogId,
				entityId: SEARCH_ENTITY_ID,
				entityType,
				title: user.name,
				avatar: user.avatar.length > 0 ? user.avatar : null,
			};
		},
		onInviteClick()
		{
			const members = [...this.selectedItems];
			this.$emit('inviteMembers', { members, showHistory: this.showHistory });
		},
		loc(key: string): string
		{
			return this.$Bitrix.Loc.getMessage(key);
		},
	},
	template: `
		<div class="bx-im-entity-selector-add-to-chat__container" :style="containerStyles">
			<div class="bx-im-entity-selector-add-to-chat__input" ref="tag-selector"></div>
			<div v-if="showHistoryOption" class="bx-im-entity-selector-add-to-chat__show-history">
				<input type="checkbox" id="bx-im-entity-selector-add-to-chat-show-history" v-model="showHistory">
				<label for="bx-im-entity-selector-add-to-chat-show-history">
					{{ loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_SHOW_HISTORY')}}
				</label>
			</div>
			<div class="bx-im-entity-selector-add-to-chat__search-result-container">
				<ChatSearch
					:searchMode="true"
					:searchQuery="searchQuery"
					:selectMode="true"
					:searchConfig="searchConfig"
					:selectedItems="[...selectedItems]"
					:showMyNotes="false"
					@clickItem="onSelectItem"
				/>
			</div>
			<div class="bx-im-entity-selector-add-to-chat__buttons">
				<MessengerButton
					:size="ButtonSize.L"
					:color="ButtonColor.Primary"
					:isRounded="true"
					:isLoading="isLoading"
					:text="loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_INVITE_BUTTON')"
					:isDisabled="selectedItems.size === 0"
					@click="onInviteClick"
				/>
				<MessengerButton
					:size="ButtonSize.L"
					:color="ButtonColor.LightBorder"
					:isRounded="true"
					:text="loc('IM_ENTITY_SELECTOR_ADD_TO_CHAT_CANCEL_BUTTON')"
					@click="$emit('close')"
				/>
			</div>
		</div>
	`,
};
