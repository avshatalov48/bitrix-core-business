export const UserAvatar = {
	name: 'UserAvatar',
	props: {
		avatarSize: Number,
		user: Object,
	},
	// language=Vue
	template: `
		<div class="calendar-slider-sidebar-user-block-item">
			<div class="ui-icon ui-icon-common-user ui-icon-common-user-sharing" :style="'width:' + avatarSize + 'px'"
				 v-if="user.SHARING_USER">
				<i></i>
			</div>
			<div class="ui-icon ui-icon-common-user-mail" :style="'width:' + avatarSize + 'px'"
				 v-else-if="user.EMAIL_USER">
				<i></i>
			</div>
			<span v-else>
				<img :src="encodeURI(user.AVATAR)" :width="avatarSize" :height="avatarSize" v-if="user.AVATAR && user.AVATAR !== '/bitrix/images/1.gif'"/>
				<div class="ui-icon ui-icon-common-user" :style="'width:' + avatarSize + 'px'" v-else>
					<i></i>
				</div>
			</span>
		</div>
	`
}