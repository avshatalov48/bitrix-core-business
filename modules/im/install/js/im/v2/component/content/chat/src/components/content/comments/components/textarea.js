import { ChatTextarea } from 'im.v2.component.textarea';

// @vue/component
export const CommentsTextarea = {
	name: 'CommentsTextarea',
	components: { ChatTextarea },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
	},
	template: `
		<ChatTextarea
			:dialogId="dialogId"
			:withMarket="false"
			:withAudioInput="false"
			class="bx-im-comments-send-panel__container"
		/>
	`,
};
