import {lazyload} from 'ui.vue3.directives.lazyload';

import {Color} from 'im.v2.const';

import type {AttachUserItemConfig} from 'im.v2.const';

const AVATAR_TYPE = {
	user: 'user',
	chat: 'chat',
	bot: 'bot'
};

// @vue/component
export const AttachUserItem = {
	name: 'AttachUserItem',
	directives: {lazyload},
	props:
	{
		config: {
			type: Object,
			default: () => {}
		},
		color: {
			type: String,
			default: Color.transparent
		}
	},
	computed:
	{
		internalConfig(): AttachUserItemConfig
		{
			return this.config;
		},
		name(): string
		{
			return this.internalConfig.name;
		},
		avatar(): string
		{
			return this.internalConfig.avatar;
		},
		avatarType(): string
		{
			return this.internalConfig.avatarType;
		},
		link(): string
		{
			return this.internalConfig.link;
		},
		avatarTypeClass()
		{
			if (this.avatar)
			{
				return '';
			}

			let avatarType = AVATAR_TYPE.user;

			if (this.avatarType === AVATAR_TYPE.chat)
			{
				avatarType = AVATAR_TYPE.chat;
			}
			else if (this.avatarType === AVATAR_TYPE.bot)
			{
				avatarType = AVATAR_TYPE.bot;
			}

			return `--${avatarType}`;
		},
		avatarTypeStyle(): {backgroundColor: string}
		{
			return {backgroundColor: !this.avatar ? this.color : ''};
		}
	},
	template: `
		<div class="bx-im-attach-user__item">
			<div class="bx-im-attach-user__avatar" :class="avatarTypeClass" :style="avatarTypeStyle">
				<img v-if="avatar" v-lazyload :data-lazyload-src="avatar" class="bx-im-attach-user__source" alt="name" />
			</div>
			<a v-if="link" :href="link" class="bx-im-attach-user__name" target="_blank">
				{{ name }}
			</a>
			<span class="bx-im-attach-user__name" v-else>
				{{ name }}
			</span>
		</div>
	`
};