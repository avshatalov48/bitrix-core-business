import {TagSelector} from 'ui.entity-selector';

import {Messenger} from 'im.public';
import {Core} from 'im.v2.application.core';
import {ChatService} from 'im.v2.provider.service';
import {DialogType, Layout, SearchEntityIdTypes} from 'im.v2.const';
import {SearchResult} from 'im.v2.component.search.search-result';
import {Button, ButtonSize, ButtonColor} from 'im.v2.component.elements';

import './add-to-chat-content.css';

import type {ImModelDialog} from 'im.v2.model';

const searchConfig = {
	currentUser: false,
	chats: false,
	network: false,
};

// @vue/component
export const AddToChatContent = {
	name: 'AddToChatContent',
	components: {SearchResult, Button},
	props: {
		chatId: {
			type: Number,
			required: true
		},
		dialogId: {
			type: String,
			required: true
		}
	},
	data() {
		return {
			searchQuery: '',
			showHistory: true,
			isLoading: false,
			selectedItems: new Set(),
			needTopShadow: false,
			needBottomShadow: true,
		};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		searchConfig: () => searchConfig,
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		isChat(): boolean
		{
			return this.dialog.type !== DialogType.user;
		}
	},
	created()
	{
		this.chatService = new ChatService();
		this.membersSelector = this.getTagSelector();
	},
	mounted()
	{
		this.membersSelector.renderTo(this.$refs['tag-selector']);
		this.membersSelector.focusTextBox();
	},
	methods:
	{
		getTagSelector(): TagSelector
		{
			let timeoutId;

			return new TagSelector({
				maxHeight: 111,
				showAddButton: false,
				showTextBox: true,
				addButtonCaption: this.$Bitrix.Loc.getMessage('IM_ENTITY_SELECTOR_ADD_TO_CHAT_ADD'),
				addButtonCaptionMore: this.$Bitrix.Loc.getMessage('IM_ENTITY_SELECTOR_ADD_TO_CHAT_ADD_MORE'),
				showCreateButton: false,
				events: {
					onBeforeTagAdd: () => {
						clearTimeout(timeoutId);
					},
					onAfterTagAdd: (event) => {
						const {tag} = event.getData();
						const itemUniqId = `${tag.entityId}|${tag.id}`;
						this.selectedItems.add(itemUniqId);
						this.focusSelector();
					},
					onBeforeTagRemove: () => {
						clearTimeout(timeoutId);
					},
					onAfterTagRemove: (event) => {
						const {tag} = event.getData();
						const itemUniqId = `${tag.entityId}|${tag.id}`;
						this.selectedItems.delete(itemUniqId);
						this.focusSelector();
					},
					onInput: () => {
						this.searchQuery = this.membersSelector.getTextBoxValue();
					},
					onBlur: () => {
						timeoutId = setTimeout(() => {
							this.membersSelector.hideTextBox();
							this.membersSelector.showAddButton();
						}, 200);
					},
					onContainerClick: () => {
						this.focusSelector();
					}
				}
			});
		},
		focusSelector()
		{
			this.membersSelector.hideAddButton();
			this.membersSelector.showTextBox();
			this.membersSelector.focusTextBox();
		},
		prepareMembers(members: Set<string>): string[]
		{
			const preparedMembers = [];
			[...members].forEach(item => {
				const [type, id] = item.split('|');
				if (type === SearchEntityIdTypes.user || type === SearchEntityIdTypes.bot)
				{
					preparedMembers.push(id);
				}
				else if (type === SearchEntityIdTypes.department)
				{
					preparedMembers.push(`${type}${id}`);
				}
			});

			return preparedMembers;
		},
		onSelectItem(event: {selectedItem: Object, selectedStatus: boolean})
		{
			const {selectedItem, selectedStatus, nativeEvent} = event;
			if (selectedStatus)
			{
				this.membersSelector.addTag({
					id: selectedItem.getId(),
					entityId: selectedItem.getEntityId(),
					entityType: selectedItem.getEntityType(),
					title: selectedItem.getTitle(),
					avatar: selectedItem.getAvatar()
				});
			}
			else
			{
				this.membersSelector.removeTag({
					id: selectedItem.getId(),
					entityId: selectedItem.getEntityId(),
				});
			}

			this.membersSelector.clearTextBox();
			if (!nativeEvent.altKey)
			{
				this.searchQuery = '';
			}
		},
		onInviteClick()
		{
			const members = this.prepareMembers(this.selectedItems);
			if (this.isChat)
			{
				this.extendChat(members);
			}
			else
			{
				members.push(this.dialogId, Core.getUserId());
				this.createChat(members);
			}
		},
		extendChat(members: Array<string | number>)
		{
			this.isLoading = true;

			this.chatService.addToChat({
				chatId: this.chatId,
				members: members,
				showHistory: this.showHistory
			}).then(() => {
				this.isLoading = false;
				this.$emit('close');
			}).catch(error => {
				console.error(error);
				this.isLoading = false;
				this.$emit('close');
			});
		},
		createChat(members: number[])
		{
			this.isLoading = true;
			this.chatService.createChat({
				title: null,
				description: null,
				members: members,
				ownerId: Core.getUserId(),
				isPrivate: true,
			}).then((newDialogId: string) => {
				this.isLoading = false;
				Messenger.openChat(newDialogId);
			});
		},
		onListScroll(event: Event)
		{
			this.needBottomShadow = event.target.scrollTop + event.target.clientHeight !== event.target.scrollHeight;

			if (event.target.scrollTop === 0)
			{
				this.needTopShadow = false;
				return;
			}

			this.needTopShadow = true;
		},
	},
	template: `
		<div class="bx-im-entity-selector-add-to-chat__container bx-im-entity-selector-add-to-chat__scope">
			<div class="bx-im-entity-selector-add-to-chat__input" ref="tag-selector"></div>
			<div v-if="isChat" class="bx-im-entity-selector-add-to-chat__show-history">
				<input type="checkbox" id="bx-im-entity-selector-add-to-chat-show-history" v-model="showHistory">
				<label for="bx-im-entity-selector-add-to-chat-show-history">
					{{ $Bitrix.Loc.getMessage('IM_ENTITY_SELECTOR_ADD_TO_CHAT_SHOW_HISTORY')}}
				</label>
			</div>
			<div class="bx-im-entity-selector-add-to-chat__search-result-container">
				<div v-if="needTopShadow" class="bx-im-entity-selector-add-to-chat__shadow --top">
					<div class="bx-im-entity-selector-add-to-chat__shadow-inner"></div>
				</div>
				<SearchResult
					:searchMode="true"
					:searchQuery="searchQuery"
					:searchConfig="searchConfig"
					:selectMode="true"
					:selectedItems="[...selectedItems]"
					@selectItem="onSelectItem"
					@scroll="onListScroll"
				/>
				<div v-if="needBottomShadow" class="bx-im-entity-selector-add-to-chat__shadow --bottom">
					<div class="bx-im-entity-selector-add-to-chat__shadow-inner"></div>
				</div>
			</div>
			<div class="bx-im-entity-selector-add-to-chat__buttons">
				<Button
					:size="ButtonSize.L"
					:color="ButtonColor.Primary"
					:isRounded="true"
					:isLoading="isLoading"
					:text="$Bitrix.Loc.getMessage('IM_ENTITY_SELECTOR_ADD_TO_CHAT_INVITE_BUTTON')"
					:isDisabled="selectedItems.size === 0"
					@click="onInviteClick"
				/>
				<Button
					:size="ButtonSize.L"
					:color="ButtonColor.LightBorder"
					:isRounded="true"
					:text="$Bitrix.Loc.getMessage('IM_ENTITY_SELECTOR_ADD_TO_CHAT_CANCEL_BUTTON')"
					@click="$emit('close')"
				/>
			</div>
		</div>
	`
};