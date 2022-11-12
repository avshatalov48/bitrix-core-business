import {CarouselUser} from './carousel-user';
import '../css/search.css';

const recentUsersLimit = 5;

// @vue/component
export const RecentUsersCarousel = {
	name: 'RecentUsersCarousel',
	components: {CarouselUser},
	props: {
		title: {
			type: String,
			required: false,
			default: ''
		}
	},
	computed:
	{
		users()
		{
			const recentUsers = [];
			this.$store.getters['recent/getSortedCollection'].forEach(recentItem => {
				const dialog = this.$store.getters['dialogues/get'](recentItem.dialogId, true);
				const user = this.$store.getters['users/get'](recentItem.dialogId, true);

				recentUsers.push({...recentItem, dialog, user});
			});

			const usersWithoutBotsAndCurrentUser = recentUsers.filter(item => {
				return item.dialog.type === 'user' && !item.user.bot && item.user.id !== this.currentUserId;
			});

			return usersWithoutBotsAndCurrentUser.slice(0, recentUsersLimit);
		},
		currentUserId(): number
		{
			return this.$store.state.application.common.userId;
		}
	},
	// language=Vue
	template: `
		<div v-if="title" class="bx-messenger-recent-users-carousel-title">{{title}}</div>
		<div class="bx-messenger-recent-users-carousel">
			<CarouselUser v-for="user in users" :key="user.dialogId" :user="user" />
		</div>
	`
};