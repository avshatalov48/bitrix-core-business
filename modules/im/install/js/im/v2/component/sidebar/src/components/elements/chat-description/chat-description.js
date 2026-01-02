import { Loc } from 'main.core';

import { ChatType, UserType } from 'im.v2.const';
import { CopilotManager } from 'im.v2.lib.copilot';
import { SidebarManager } from 'im.v2.lib.sidebar';

import './chat-description.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelUser } from 'im.v2.model';

const MAX_DESCRIPTION_SYMBOLS = 50;
const VISIBLE_DESCRIPTION_LINES = 2;
const NEW_LINE_SYMBOL = '\n';

const DescriptionByChatType = {
	[ChatType.user]: Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_USER'),
	[ChatType.channel]: Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_CHANNEL'),
	[ChatType.openChannel]: Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_CHANNEL'),
	[ChatType.generalChannel]: Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_CHANNEL'),
	[ChatType.comment]: Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_COMMENTS'),
	default: Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_GROUP_V2'),
};

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

			return user.type === UserType.bot;
		},
		isCollabChat(): boolean
		{
			return this.dialog.type === ChatType.collab;
		},
		customDescription(): string
		{
			const sidebarConfig = SidebarManager.getInstance().getConfig(this.dialogId);

			return sidebarConfig.getCustomDescription();
		},
		isCopilotChat(): boolean
		{
			return (new CopilotManager()).isCopilotChat(this.dialogId);
		},
		isLongDescription(): boolean
		{
			const lineBreakCount = this.dialog.description.split(NEW_LINE_SYMBOL).length - 1;
			const hasSeveralLines = lineBreakCount > VISIBLE_DESCRIPTION_LINES;

			return (this.dialog.description.length > MAX_DESCRIPTION_SYMBOLS) || hasSeveralLines;
		},
		previewDescription(): string
		{
			if (this.dialog.description.length === 0)
			{
				return this.chatTypeText;
			}

			if (this.isLongDescription)
			{
				return `${this.dialog.description.slice(0, MAX_DESCRIPTION_SYMBOLS)}...`;
			}

			return this.dialog.description;
		},
		descriptionToShow(): string
		{
			return this.expanded ? this.dialog.description : this.previewDescription;
		},
		chatTypeText(): string
		{
			if (this.customDescription.length > 0)
			{
				return this.customDescription;
			}

			if (this.isCopilotChat)
			{
				return (new CopilotManager()).getAIModelName(this.dialogId);
			}

			if (this.isBot)
			{
				return this.loc('IM_SIDEBAR_CHAT_TYPE_BOT');
			}

			if (this.isCollabChat)
			{
				return this.loc('IM_SIDEBAR_CHAT_TYPE_COLLAB');
			}

			return DescriptionByChatType[this.dialog.type] ?? DescriptionByChatType.default;
		},
		showExpandButton(): boolean
		{
			if (this.expanded)
			{
				return false;
			}

			return this.isLongDescription;
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
				<div
					class="bx-im-sidebar-chat-description__text"
					:class="{ '--long-description': isLongDescription }"
				>
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
