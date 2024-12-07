import { ChatAvatar, AvatarSize, ChatTitle } from 'im.v2.component.elements';
import { AutoDelete } from '../../../elements/auto-delete/auto-delete';

// @vue/component
export const SupportPreview = {
	name: 'SupportPreview',
	components: { ChatAvatar, ChatTitle, AutoDelete },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
		{
			AvatarSize: () => AvatarSize,
		},
	template: `
		<div class="bx-im-sidebar-main-preview__scope">
			<div class="bx-im-sidebar-main-preview-group-chat__avatar-container">
				<div class="bx-im-sidebar-main-preview-group-chat__avatar">
					<ChatAvatar :size="AvatarSize.XXXL" :avatarDialogId="dialogId" :contextDialogId="dialogId" />
				</div>
				<ChatTitle :dialogId="dialogId" :twoLine="true" class="bx-im-sidebar-main-preview-group-chat__title" />
			</div>
			<div class="bx-im-sidebar-main-preview-group-chat__settings">
				<AutoDelete />
			</div>
		</div>
	`,
};
