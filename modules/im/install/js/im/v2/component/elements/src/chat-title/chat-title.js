import { Text, Type } from 'main.core';

import { Core } from 'im.v2.application.core';
import { ChatType, Settings, UserType } from 'im.v2.const';

import './chat-title.css';

import type { ImModelChat, ImModelUser, ImModelBot } from 'im.v2.model';

const DialogSpecialType = {
	bot: 'bot',
	extranet: 'extranet',
	network: 'network',
	collaber: 'collaber',
	support24: 'support24',
};

const TitleIcons = {
	absent: 'absent',
	birthday: 'birthday',
};

export const ChatTitle = {
	name: 'ChatTitle',
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
		onlyFirstName: {
			type: Boolean,
			default: false,
		},
		twoLine: {
			type: Boolean,
			default: false,
		},
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		botType(): string
		{
			if (!this.isUser)
			{
				return '';
			}

			const { type }: ImModelBot = this.$store.getters['users/bots/getByUserId'](this.dialogId);

			return type;
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

				if ([ChatType.support24Notifier, ChatType.support24Question].includes(this.dialog.type))
				{
					return DialogSpecialType.support24;
				}

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

			if (this.isCollaber)
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
			if (this.isCollaber || this.isExtranet)
			{
				return false;
			}

			return Type.isStringFilled(this.dialogSpecialType);
		},
		leftIcon(): string
		{
			if (!this.withLeftIcon)
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
		isCollaber(): boolean
		{
			return this.user.type === UserType.collaber;
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
			if (this.isUser)
			{
				return false;
			}

			const isMuted = this.dialog.muteList.find((element) => {
				return element === Core.getUserId();
			});

			return Boolean(isMuted);
		},
		tooltipText(): string
		{
			if (this.isSelfChat && this.showItsYou)
			{
				return `${this.dialog.name} (${this.$Bitrix.Loc.getMessage('IM_LIST_RECENT_CHAT_SELF')})`;
			}

			return this.dialog.name;
		},
		showBirthdays(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showBirthday);
		},
	},
	template: `
		<div :class="containerClasses" class="bx-im-chat-title__scope bx-im-chat-title__container">
			<span class="bx-im-chat-title__content">
				<span v-if="leftIcon" :class="'--' + leftIcon" class="bx-im-chat-title__icon"></span>
				<span
					:class="[specialColor? '--' + specialColor : '']"
					:style="{color: color}"
					:title="tooltipText"
					class="bx-im-chat-title__text"
					v-html="dialogName"
				></span>
				<strong v-if="isSelfChat && showItsYou">
					<span class="bx-im-chat-title__text --self">({{ $Bitrix.Loc.getMessage('IM_LIST_RECENT_CHAT_SELF') }})</span>
				</strong>
				<span v-if="withMute && isChatMuted" class="bx-im-chat-title__muted-icon"></span>
			</span>
		</div>
	`,
};
