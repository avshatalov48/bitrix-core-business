import { ChatAvatar, AvatarSize } from 'im.v2.component.elements';

import type { ImModelUser } from 'im.v2.model';

// @vue/component
export const ReactionUser = {
	components: { ChatAvatar },
	props:
	{
		userId: {
			type: Number,
			required: true,
		},
		contextDialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.userId);
		},
		avatarStyle(): Object
		{
			if (!this.user.avatar)
			{
				return {};
			}

			return {
				backgroundImage: `url('${this.user.avatar}')`,
			};
		},
	},
	template: `
		<div class="bx-im-reaction-list__user_avatar">
			<ChatAvatar 
				:avatarDialogId="userId" 
				:contextDialogId="contextDialogId" 
				:size="AvatarSize.XS" 
				:withAvatarLetters="false"
				:withTooltip="false"
			/>
		</div>
	`,
};
