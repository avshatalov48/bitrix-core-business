import { UserAvatar } from './user-avatar';
import { AvatarsRow } from './avatars-row';

// @vue/component
export const UserList = {
	components: {
		UserAvatar,
		AvatarsRow,
	},
	props: {
		hostUsers: {
			type: Array,
			default: () => [],
		},
		commonUsers: {
			type: Array,
			default: () => [],
		},
		maxToShow: {
			type: Number,
			default: 5,
		},
	},
	template: `
		<div class="socialnetwork-collab-converter-wizard-user-list">
			<AvatarsRow v-if="hostUsers.length > 0" :users="hostUsers" :borderColor="'var(--ui-color-accent-main-success, #1BCE7B)'"/>
			<AvatarsRow v-if="commonUsers.length > 0" :users="commonUsers" :borderColor="'var(--ui-color-divider-default, #F0F0F0)'"/>
		</div>
	`,
};
