import { Parser } from 'im.v2.lib.parser';
import { ChatType } from 'im.v2.const';

import './chat-description.css';

import type { JsonObject } from 'main.core';
import type { ImModelUser, ImModelChat } from 'im.v2.model';

const MAX_DESCRIPTION_SYMBOLS = 25;

// @vue/component
export const ChatDescription = {
	name: 'ChatDescription',
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			expanded: false,
		};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		isBot(): boolean
		{
			const user: ImModelUser = this.$store.getters['users/get'](this.dialogId, true);

			return user.bot === true;
		},
		previewDescription(): string
		{
			if (this.dialog.description.length === 0)
			{
				return this.chatTypeText;
			}

			if (this.dialog.description.length > MAX_DESCRIPTION_SYMBOLS)
			{
				return `${this.dialog.description.slice(0, MAX_DESCRIPTION_SYMBOLS)}...`;
			}

			return this.dialog.description;
		},
		descriptionToShow(): string
		{
			const rawText = this.expanded ? this.dialog.description : this.previewDescription;

			return Parser.purifyText(rawText);
		},
		chatTypeText(): string
		{
			if (this.isBot)
			{
				return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_BOT');
			}

			if (this.isUser)
			{
				return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_USER');
			}

			return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_GROUP_V2');
		},
		showExpandButton(): boolean
		{
			if (this.expanded)
			{
				return false;
			}

			return this.dialog.description.length >= MAX_DESCRIPTION_SYMBOLS;
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-sidebar-chat-description__container">
			<div class="bx-im-sidebar-chat-description__text-container" :class="[expanded ? '--expanded' : '']">
				<div class="bx-im-sidebar-chat-description__icon"></div>
				<div class="bx-im-sidebar-chat-description__text">
					{{ descriptionToShow }}
				</div>
			</div>
			<button
				v-if="showExpandButton"
				class="bx-im-sidebar-chat-description__show-more-button"
				@click="expanded = !expanded"
			>
				{{ loc('IM_SIDEBAR_CHAT_DESCRIPTION_SHOW') }}
			</button>
		</div>
	`,
};
