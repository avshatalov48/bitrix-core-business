import { Text, Type } from 'main.core';
import { BIcon, Outline as OutlineIcons } from 'ui.icon-set.api.vue';

import { Core } from 'im.v2.application.core';
import { ChatType, Settings, UserType, Color, BotCode } from 'im.v2.const';

import { DialogSpecialType, ChatTitleType, TitleIcons } from './const/const';

import './css/chat-title.css';

import type { ImModelChat, ImModelUser, ImModelBot } from 'im.v2.model';

const ICON_SIZE = 18;

export const ChatTitle = {
	name: 'ChatTitle',
	components: { BIcon },
	props: {
		dialogId: {
			type: [Number, String],
			default: 0,
		},
		text: {
			type: String,
			default: '',
		},
		showItsYou: {
			type: Boolean,
			default: true,
		},
		withLeftIcon: {
			type: Boolean,
			default: true,
		},
		withColor: {
			type: Boolean,
			default: false,
		},
		withMute: {
			type: Boolean,
			default: false,
		},
		withAutoDelete: {
			type: Boolean,
			default: false,
		},
		onlyFirstName: {
			type: Boolean,
			default: false,
		},
		twoLine: {
			type: Boolean,
			default: false,
		},
		customType: {
			type: String,
			default: '',
		},
	},
	computed:
	{
		Color: () => Color,
		ICON_SIZE: () => ICON_SIZE,
		OutlineIcons: () => OutlineIcons,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		botType(): string
		{
			const EXCLUDED_BOT_CODES = [BotCode.aiAssistant];

			if (!this.isUser)
			{
				return '';
			}

			const bot: ?ImModelBot = this.$store.getters['users/bots/getByUserId'](this.dialogId);
			if (!bot || EXCLUDED_BOT_CODES.includes(bot.code))
			{
				return '';
			}

			return bot.type;
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		isSelfChat(): boolean
		{
			return this.isUser && this.user.id === Core.getUserId();
		},
		containerClasses(): string[]
		{
			const classes = [];

			if (this.twoLine)
			{
				classes.push('--twoline');
			}

			return classes;
		},
		dialogName(): string
		{
			if (this.customType === ChatTitleType.notes)
			{
				return this.loc('IM_CHAT_TITLE_MY_NOTES');
			}

			if (this.text)
			{
				return Text.encode(this.text);
			}

			let resultText = this.dialog.name;
			if (this.isUser)
			{
				resultText = this.onlyFirstName ? this.user.firstName : this.user.name;
			}

			return Text.encode(resultText);
		},
		dialogSpecialType(): string
		{
			if (!this.isUser)
			{
				if (this.isCollabChat)
				{
					return '';
				}

				if (this.isExtranet)
				{
					return DialogSpecialType.extranet;
				}

				if (this.isCollaberChatOrUser)
				{
					return DialogSpecialType.collaber;
				}

				if ([ChatType.support24Notifier, ChatType.support24Question].includes(this.dialog.type))
				{
					return DialogSpecialType.support24;
				}

				return '';
			}

			if (this.isSelfChat)
			{
				return '';
			}

			if (this.isBot)
			{
				return this.botType;
			}

			if (this.isExtranet)
			{
				return DialogSpecialType.extranet;
			}

			if (this.isCollaberChatOrUser)
			{
				return DialogSpecialType.collaber;
			}

			if (this.isNetwork)
			{
				return DialogSpecialType.network;
			}

			return '';
		},
		isDialogSpecialTypeWithLeftIcon(): boolean
		{
			if (this.isCollaberChatOrUser || this.isExtranet)
			{
				return false;
			}

			return Type.isStringFilled(this.dialogSpecialType);
		},
		leftIcon(): string
		{
			if (!this.withLeftIcon || this.isSelfChat)
			{
				return '';
			}

			if (this.isDialogSpecialTypeWithLeftIcon)
			{
				return this.dialogSpecialType;
			}

			if (!this.isUser)
			{
				return '';
			}

			if (this.showBirthdays && this.user.isBirthday)
			{
				return TitleIcons.birthday;
			}

			if (this.user.isAbsent)
			{
				return TitleIcons.absent;
			}

			return '';
		},
		color(): string
		{
			if (!this.withColor || this.specialColor)
			{
				return '';
			}

			return this.dialog.color;
		},
		specialColor(): string
		{
			return this.dialogSpecialType;
		},
		isBot(): boolean
		{
			if (!this.isUser)
			{
				return false;
			}

			return this.user.type === UserType.bot;
		},
		isExtranet(): boolean
		{
			if (this.isUser)
			{
				return this.user.type === UserType.extranet;
			}

			return this.dialog.extranet;
		},
		isCollaberChatOrUser(): boolean
		{
			if (this.isUser)
			{
				return this.user.type === UserType.collaber;
			}

			return this.dialog.containsCollaber;
		},
		isCollabChat(): boolean
		{
			return this.dialog.type === ChatType.collab;
		},
		isNetwork(): boolean
		{
			if (this.isUser)
			{
				return this.user.network;
			}

			return false;
		},
		isChatMuted(): boolean
		{
			const isMuted = this.dialog.muteList.find((element) => {
				return element === Core.getUserId();
			});

			return Boolean(isMuted);
		},
		isAutoDeleteEnabled(): boolean
		{
			if (!this.withAutoDelete)
			{
				return false;
			}

			return this.$store.getters['chats/autoDelete/isEnabled'](this.chatId);
		},
		tooltipText(): string
		{
			if (this.customType === ChatTitleType.notes)
			{
				return this.loc('IM_CHAT_TITLE_MY_NOTES');
			}

			if (this.isSelfChat && this.showItsYou)
			{
				return `${this.dialog.name} (${this.loc('IM_CHAT_TITLE_SELF')})`;
			}

			return this.dialog.name;
		},
		showBirthdays(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showBirthday);
		},
	},
	methods: {
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div :class="containerClasses" class="bx-im-chat-title__scope bx-im-chat-title__container">
			<span class="bx-im-chat-title__content">
				<span v-if="leftIcon" :class="'--' + leftIcon" class="bx-im-chat-title__icon"></span>
				<span
					:class="[specialColor ? '--' + specialColor : '']"
					:style="{color: color}"
					:title="tooltipText"
					class="bx-im-chat-title__text"
					v-html="dialogName"
				></span>
				<strong v-if="isSelfChat && showItsYou">
					<span class="bx-im-chat-title__text --self">({{ loc('IM_CHAT_TITLE_SELF') }})</span>
				</strong>
				<span v-if="withMute && isChatMuted" class="bx-im-chat-title__muted-icon"></span>
				<BIcon
					v-if="isAutoDeleteEnabled"
					:name="OutlineIcons.TIMER_DOT"
					:color="Color.accentBlue"
					:size="ICON_SIZE"
					:hoverable="false"
					:title="loc('IM_CHAT_TITLE_AUTO_DELETE_TITLE')"
					class="bx-im-chat-title__auto-delete-icon"
				/>
			</span>
		</div>
	`,
};
