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
			required: true
		}
	},
	computed:
		{
			users()
			{
				const recentUsers = [];
				this.$store.state.recent.collection.forEach(recentItem => {
					const dialog = this.$store.getters['dialogues/get'](recentItem.dialogId, true);
					const user = this.$store.getters['users/get'](recentItem.dialogId, true);

					recentUsers.push({...recentItem, dialog, user});
				});

				return recentUsers.filter(item => item.dialog.type === 'user' && !item.user.bot).slice(0, recentUsersLimit);
			}
		},
	// language=Vue
	template: `
		<div class="bx-messenger-recent-users-carousel-title">{{title}}</div>
		<div class="bx-messenger-recent-users-carousel">
			<div class="bx-messenger-recent-users-carousel-inner">
				<CarouselUser v-for="user in users" :key="user.dialogId" :user="user" />
			</div>
		</div>
	`
};