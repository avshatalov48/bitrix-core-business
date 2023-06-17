import {Avatar, AvatarSize} from 'im.v2.component.elements';

import type {ImModelUser} from 'im.v2.model';

// @vue/component
export const ReactionUser = {
	components: {Avatar},
	props:
	{
		userId: {
			type: Number,
			required: true
		},
	},
	data()
	{
		return {};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.userId);
		},
		avatarStyle()
		{
			if (!this.user.avatar)
			{
				return;
			}

			return {
				backgroundImage: `url('${this.user.avatar}')`
			};
		}
	},
	template: `
		<div class="bx-im-reaction-list__user_avatar">
			<Avatar :dialogId="userId" :size="AvatarSize.XS" :withAvatarLetters="false" :withStatus="false" />
		</div>
	`
};