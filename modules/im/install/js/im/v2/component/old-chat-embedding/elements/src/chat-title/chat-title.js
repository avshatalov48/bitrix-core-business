import {DialogType, RecentSettings} from 'im.v2.const';

import './chat-title.css';
import {BitrixVue} from 'ui.vue3';

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
			type: String,
			default: '0'
		},
		withMute: {
			type: Boolean,
			default: false
		}
	},
	computed:
	{
		dialog()
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		user()
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		botType()
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
			return this.isUser && this.user.id === this.currentUserId;
		},
		dialogSpecialType()
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
		isNetwork()
		{
			if (this.isUser)
			{
				return this.user.network;
			}

			return false;
		},
		isChatMuted()
		{
			if (this.isUser)
			{
				return false;
			}

			const isMuted = this.dialog.muteList.find(element => {
				return element === this.currentUserId;
			});

			return !!isMuted;
		},
		currentUserId()
		{
			return this.$store.state.application.common.userId;
		},
		tooltipText()
		{
			if (this.isSelfChat)
			{
				return `${this.dialog.name} (${this.phrases['IM_RECENT_CHAT_SELF']})`;
			}

			return this.dialog.name;
		},
		showBirthdays()
		{
			return this.$store.getters['recent/getOption'](RecentSettings.showBirthday);
		},
		isDarkTheme()
		{
			return this.$store.state.application.options.darkTheme;
		},
		phrases()
		{
			return BitrixVue.getFilteredPhrases(this, 'IM_RECENT_');
		},
	},
	template: `
		<div class="bx-im-component-chat-title-wrap">
			<div v-if="leftIcon" :class="'bx-im-component-chat-name-left-icon bx-im-component-chat-name-left-icon-' + leftIcon"></div>
			<span :class="'bx-im-component-chat-name-text-' + color" :title="tooltipText" class="bx-im-component-chat-name-text" >
				{{ dialog.name }}
				<strong v-if="isSelfChat">
					<span class="bx-im-component-chat-name-text-self">({{ phrases['IM_RECENT_CHAT_SELF'] }})</span>
				</strong>
			</span>
			<div v-if="withMute && isChatMuted" class="bx-im-component-chat-name-muted-icon"></div>
		</div>
	`
};