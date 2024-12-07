import { Utils } from 'im.v2.lib.utils';
import { ChatTitle } from 'im.v2.component.elements';

import type { JsonObject } from 'main.core';

const ONE_MINUTE = 60 * 1000;

// @vue/component
export const UserTitle = {
	name: 'UserTitle',
	components: { ChatTitle },
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
			userLastOnlineText: '',
		};
	},
	computed:
	{
		userPosition(): string
		{
			return this.$store.getters['users/getPosition'](this.dialogId);
		},
		userLastOnline(): string
		{
			return this.$store.getters['users/getLastOnline'](this.dialogId);
		},
		userLink(): string
		{
			return Utils.user.getProfileLink(this.dialogId);
		},
	},
	watch:
	{
		userLastOnline(value)
		{
			this.userLastOnlineText = value;
		},
	},
	created()
	{
		this.updateUserOnline();
		this.userLastOnlineInterval = setInterval(this.updateUserOnline, ONE_MINUTE);
	},
	beforeUnmount()
	{
		clearInterval(this.userLastOnlineInterval);
	},
	methods:
	{
		updateUserOnline(): void
		{
			this.userLastOnlineText = this.$store.getters['users/getLastOnline'](this.dialogId);
		},
	},
	template: `
		<div class="bx-im-chat-header__info">
			<div class="bx-im-chat-header__title --user">
				<a :href="userLink" target="_blank" class="bx-im-chat-header__title_container">
					<ChatTitle :dialogId="dialogId" />
				</a>
				<span class="bx-im-chat-header__user-status">{{ userLastOnlineText }}</span>
			</div>
			<div class="bx-im-chat-header__subtitle_container">
				<div class="bx-im-chat-header__subtitle_content">{{ userPosition }}</div>
			</div>
		</div>
	`,
};
