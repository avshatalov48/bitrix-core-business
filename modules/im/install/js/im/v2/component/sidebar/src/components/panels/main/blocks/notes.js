import { ChatTitle, ChatTitleType } from 'im.v2.component.elements.chat-title';
import { ChatAvatar, AvatarSize, ChatAvatarType } from 'im.v2.component.elements.avatar';

import '../css/sidebar-notes-preview.css';

// @vue/component
export const NotesPreview = {
	name: 'NotesPreview',
	components: { ChatAvatar, ChatTitle },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		ChatAvatarType: () => ChatAvatarType,
		AvatarSize: () => AvatarSize,
		ChatTitleType: () => ChatTitleType,
	},
	methods: {
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-sidebar-notes-preview">
			<div class="bx-im-sidebar-notes-preview__avatar">
				<ChatAvatar 
					:avatarDialogId="dialogId"
					:contextDialogId="dialogId"
					:size="AvatarSize.XXXL"
					:customType="ChatAvatarType.notes"
				/>
			</div>
			<div class="bx-im-sidebar-notes-preview__head">
				<ChatTitle :dialogId="dialogId" :customType="ChatTitleType.notes" :showItsYou="false"/>
				<span class="bx-im-sidebar-notes-preview__description">
					{{ loc('IM_SIDEBAR_NOTES_PREVIEW_DESCRIPTION') }}
				</span>
			</div>
		</div>
	`,
};
