import { UserAvatar } from './user-avatar';

// @vue/component
export const AvatarsRow = {
	components: {
		UserAvatar,
	},
	props: {
		users: {
			type: Array,
			default: () => [],
		},
		maxToShow: {
			type: Number,
			default: 5,
		},
		borderColor: {
			type: String,
			default: 'transparent',
		},
	},
	computed: {
		rowUsers(): Array<Object>
		{
			return this.users.slice(0, this.maxToShow);
		},
		notShownAmount(): number
		{
			return this.users.length - this.rowUsers.length;
		},
	},
	template: `
		<div class="socialnetwork-collab-converter-wizard-user-list-pill" :style="{'border-color': borderColor}">
			<UserAvatar v-for="user in rowUsers" :src="user.avatar"/>
			<div v-if="notShownAmount" class="socialnetwork-collab-converter-wizard-user-list-not-shown">+{{notShownAmount}}</div>
		</div>
	`,
};
