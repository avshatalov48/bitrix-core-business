import {Core} from 'im.v2.application.core';
import {DialogType, Settings} from 'im.v2.const';

import './chat-title.css';

import type {ImModelDialog, ImModelUser} from 'im.v2.model';

const DialogSpecialType = {
	bot: 'bot',
	extranet: 'extranet',
	network: 'network',
	support24: 'support24'
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
			default: 0
		},
		text: {
			type: String,
			default: ''
		},
		showItsYou: {
			type: Boolean,
			default: true
		},
		withLeftIcon: {
			type: Boolean,
			default: true
		},
		withColor: {
			type: Boolean,
			default: false
		},
		withMute: {
			type: Boolean,
			default: false
		},
		onlyFirstName: {
			type: Boolean,
			default: false
		},
		twoLine: {
			type: Boolean,
			default: false
		}
	},
	computed:
	{
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
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

			return this.$store.getters['users/getBotType'](this.dialogId);
		},
		isUser(): boolean
		{
			return this.dialog.type === DialogType.user;
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
			if (!this.dialogId && this.text)
			{
				return this.text;
			}

			if (this.isUser)
			{
				if (this.onlyFirstName)
				{
					return this.user.firstName;
				}

				return this.user.name;
			}

			return this.dialog.name;
		},
		dialogSpecialType(): string
		{
			if (!this.isUser)
			{
				if (this.isExtranet)
				{
					return DialogSpecialType.extranet;
				}
				else if ([DialogType.support24Notifier, DialogType.support24Question].includes(this.dialog.type))
				{
					return DialogSpecialType.support24;
				}

				return '';
			}

			if (this.isBot)
			{
				return this.botType;
			}
			else if (this.isExtranet)
			{
				return DialogSpecialType.extranet;
			}
			else if (this.isNetwork)
			{
				return DialogSpecialType.network;
			}

			return '';
		},
		leftIcon(): string
		{
			if (!this.withLeftIcon)
			{
				return '';
			}

			if (this.dialogSpecialType)
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
			else if (this.user.isAbsent)
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
			if (this.isUser)
			{
				return this.user.bot;
			}

			return false;
		},
		isExtranet(): boolean
		{
			if (this.isUser)
			{
				return this.user.extranet;
			}

			return this.dialog.extranet;
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

			const isMuted = this.dialog.muteList.find(element => {
				return element === Core.getUserId();
			});

			return !!isMuted;
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
		}
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
				>
					{{ dialogName }}
				</span>
				<strong v-if="isSelfChat && showItsYou">
					<span class="bx-im-chat-title__text --self">({{ $Bitrix.Loc.getMessage('IM_LIST_RECENT_CHAT_SELF') }})</span>
				</strong>
				<div v-if="withMute && isChatMuted" class="bx-im-chat-title__muted-icon"></div>
			</span>
		</div>
	`
};