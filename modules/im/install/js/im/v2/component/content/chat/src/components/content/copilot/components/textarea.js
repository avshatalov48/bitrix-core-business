import { ChatTextarea } from 'im.v2.component.textarea';

// @vue/component
export const CopilotTextarea = {
	name: 'CopilotTextarea',
	components: { ChatTextarea },
	props: {
		dialogId: {
			type: String,
			default: '',
		},
	},
	methods: {
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<ChatTextarea
			:dialogId="dialogId"
			:placeholder="loc('IM_CONTENT_COPILOT_TEXTAREA_PLACEHOLDER')"
			:withMarket="false"
			:withEdit="false"
			:withUploadMenu="false"
			:withSmileSelector="false"
		/>
	`,
};
