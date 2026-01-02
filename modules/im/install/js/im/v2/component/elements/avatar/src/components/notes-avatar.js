import { AvatarSize, AvatarSizeMap } from '../const/const';

import '../css/chat-notes-avatar.css';

// @vue/component
export const NotesAvatar = {
	name: 'NotesAvatar',
	inheritAttrs: false,
	props: {
		size: {
			type: String,
			default: AvatarSize.M,
		},
	},
	computed: {
		sizeStyles(): { width: string, height: string }
		{
			return {
				width: `${AvatarSizeMap[this.size]}px`,
				height: `${AvatarSizeMap[this.size]}px`,
			};
		},
	},
	methods: {
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-notes-avatar__container" :style="sizeStyles" :title="loc('IM_ELEMENTS_CHAT_MY_NOTES')"></div>
	`,
};
