import { MessengerMenu, MenuItem, MenuItemIcon } from 'im.v2.component.elements';
import { EntityCreator } from 'im.v2.lib.entity-creator';
import { Extension } from 'main.core';

import type { PopupOptions } from 'main.popup';
import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const CreateEntityMenu = {
	components:
	{
		MessengerMenu,
		MenuItem,
	},
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		textareaValue: {
			type: String,
			required: false,
			default: '',
		},
	},
	data(): Object
	{
		return {
			showMenu: false,
		};
	},
	computed:
	{
		MenuItemIcon: () => MenuItemIcon,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		menuConfig(): PopupOptions
		{
			return {
				width: 288,
				bindElement: this.$refs.createEntity || {},
				bindOptions: {
					position: 'top',
				},
				offsetTop: 30,
				offsetLeft: -139,
				padding: 0,
			};
		},
	},
	methods:
	{
		onCreateAiTextClick()
		{
			this.getEntityCreator().createAiTextForChat(this.textareaValue);
			this.showMenu = false;
		},
		onCreateAiImageClick()
		{
			//
		},
		onCreateTaskClick()
		{
			this.getEntityCreator().createTaskForChat();
			this.showMenu = false;
		},
		onCreateMeetingClick()
		{
			this.getEntityCreator().createMeetingForChat();
			this.showMenu = false;
		},
		onCreateSummaryClick()
		{
			//
		},
		getEntityCreator(): EntityCreator
		{
			if (!this.entityCreator)
			{
				this.entityCreator = new EntityCreator(this.chatId);
			}

			return this.entityCreator;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div
			@click="showMenu = true"
			:title="loc('IM_TEXTAREA_ICON_CREATE')"
			class="bx-im-textarea__icon --create"
			:class="{'--active': showMenu}"
			ref="createEntity"
		>
		</div>
		<MessengerMenu v-if="showMenu" :config="menuConfig" @close="showMenu = false">
			<MenuItem
				:icon="MenuItemIcon.task"
				:title="loc('IM_TEXTAREA_CREATE_TASK_TITLE')"
				:subtitle="loc('IM_TEXTAREA_CREATE_TASK_SUBTITLE')"
				@click="onCreateTaskClick"
			/>
			<MenuItem
				:icon="MenuItemIcon.meeting"
				:title="loc('IM_TEXTAREA_CREATE_MEETING_TITLE')"
				:subtitle="loc('IM_TEXTAREA_CREATE_MEETING_SUBTITLE')"
				@click="onCreateMeetingClick"
			/>
			<MenuItem
				v-if="false"
				:icon="MenuItemIcon.summary"
				:title="loc('IM_TEXTAREA_CREATE_SUMMARY_TITLE')"
				:subtitle="loc('IM_TEXTAREA_CREATE_SUMMARY_SUBTITLE')"
				:disabled="true"
			/>
			<MenuItem
				v-if="false"
				:icon="MenuItemIcon.vote"
				:title="loc('IM_TEXTAREA_CREATE_VOTE_TITLE')"
				:subtitle="loc('IM_TEXTAREA_CREATE_VOTE_SUBTITLE')"
				:disabled="true"
			/>
		</MessengerMenu>
	`,
};
