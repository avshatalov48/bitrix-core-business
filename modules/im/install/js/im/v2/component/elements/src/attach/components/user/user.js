import { AttachUserItem } from './user-item';

import './user.css';

import type { AttachUserConfig } from 'im.v2.const';

// @vue/component
export const AttachUser = {
	name: 'AttachUser',
	components: { AttachUserItem },
	props:
	{
		config: {
			type: Object,
			default: () => {},
		},
	},
	computed:
	{
		internalConfig(): AttachUserConfig
		{
			return this.config;
		},
	},
	template: `
		<div class="bx-im-attach-user__container">
			<AttachUserItem v-for="(user, index) in internalConfig.user" :config="user" :key="index" />
		</div>
	`,
};
