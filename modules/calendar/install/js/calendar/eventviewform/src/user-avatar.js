import { AvatarRoundGuest } from 'ui.avatar';

export const UserAvatar = {
	name: 'UserAvatar',
	props: {
		avatarSize: Number,
		user: {
			COLLAB_USER: Boolean,
			SHARING_USER: Boolean,
			EMAIL_USER: Boolean,
			AVATAR: String | null,
			DISPLAY_NAME: String,
		},
	},
	methods: {
		renderAvatar(): void
		{
			new AvatarRoundGuest(
				{
					size: this.avatarSize,
					userName: this.user.DISPLAY_NAME,
					userpicPath: this.getAvatar(),
					baseColor: '#19cc45',
				},
			).renderTo(this.$refs.collabAvatar);
		},

		getAvatar(): string | null
		{
			if (this.user.AVATAR && this.user.AVATAR !== '/bitrix/images/1.gif')
			{
				return this.user.AVATAR;
			}

			return null;
		},
	},
	mounted(): void
	{
		if (this.user.COLLAB_USER)
		{
			this.renderAvatar();
		}
	},
	// language=Vue
	template: `
		<div class="calendar-slider-sidebar-user-block-item">
			<div ref="collabAvatar" v-if="user.COLLAB_USER" :style="'width:' + avatarSize + 'px'"></div>
			<div class="ui-icon ui-icon-common-user ui-icon-common-user-sharing" :style="'width:' + avatarSize + 'px'"
				 v-else-if="user.SHARING_USER">
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
	`,
};
