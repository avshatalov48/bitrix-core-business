import { ChatService } from 'im.v2.provider.service';
import { PopupOptions } from 'main.popup';

import { Messenger } from 'im.public';
import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';
import { MessengerPopup } from 'im.v2.component.elements';

import { AddToChatContent } from '../elements/add-to-chat-content/add-to-chat-content';

import type { ImModelChat } from 'im.v2.model';
import type { JsonObject } from 'main.core';

const POPUP_ID = 'im-add-to-chat-popup';

// @vue/component
export const AddToChat = {
	name: 'AddToChat',
	components: { MessengerPopup, AddToChatContent },
	props:
	{
		bindElement: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
		popupConfig: {
			type: Object,
			required: true,
		},
	},
	emits: ['close'],
	data(): JsonObject
	{
		return {
			isLoading: false,
		};
	},
	computed:
	{
		POPUP_ID: () => POPUP_ID,
		config(): PopupOptions
		{
			return {
				titleBar: this.$Bitrix.Loc.getMessage('IM_ENTITY_SELECTOR_ADD_TO_CHAT_ADD_MEMBERS_TITLE'),
				closeIcon: true,
				bindElement: this.bindElement,
				offsetTop: this.popupConfig.offsetTop,
				offsetLeft: this.popupConfig.offsetLeft,
				padding: 0,
				contentPadding: 0,
				contentBackground: '#fff',
				className: 'bx-im-entity-selector-add-to-chat__scope',
			};
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isChat(): boolean
		{
			return this.dialog.type !== ChatType.user;
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
	},
	created()
	{
		this.chatService = new ChatService();
	},
	methods:
	{
		inviteMembers(event: {members: Array<string | number>, showHistory: boolean})
		{
			const { members, showHistory } = event;

			if (this.isChat)
			{
				this.extendChat(members, showHistory);
			}
			else
			{
				members.push(this.dialogId, Core.getUserId());
				void this.createChat(members);
			}
		},
		extendChat(members: Array<string | number>, showHistory: boolean)
		{
			this.isLoading = true;
			this.chatService.addToChat({
				chatId: this.chatId,
				members,
				showHistory,
			}).then(() => {
				this.isLoading = false;
				this.$emit('close');
			}).catch((error) => {
				console.error(error);
				this.isLoading = false;
				this.$emit('close');
			});
		},
		async createChat(members: number[])
		{
			this.isLoading = true;
			const { newDialogId } = await this.chatService.createChat({
				title: null,
				description: null,
				members,
				ownerId: Core.getUserId(),
				isPrivate: true,
			}).catch((error) => {
				console.error(error);
				this.isLoading = false;
			});
			this.isLoading = false;
			this.$emit('close');
			void Messenger.openChat(newDialogId);
		},
	},
	template: `
		<MessengerPopup
			:config="config"
			:id="POPUP_ID"
			@close="$emit('close')"
		>
			<AddToChatContent 
				:dialogId="dialogId" 
				:isLoading="isLoading"
				@close="$emit('close')"
				@inviteMembers="inviteMembers"
			/>
		</MessengerPopup>
	`,
};
