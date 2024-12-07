import { ChatTextarea } from 'im.v2.component.textarea';
import { CopilotDraftManager } from 'im.v2.lib.draft';

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
	computed:
	{
		CopilotDraftManager: () => CopilotDraftManager,
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<ChatTextarea
			:dialogId="dialogId"
			:placeholder="this.loc('IM_CONTENT_COPILOT_TEXTAREA_PLACEHOLDER')"
			:withCreateMenu="false"
			:withMarket="false"
			:withEdit="false"
			:withUploadMenu="false"
			:withSmileSelector="false"
			:draftManagerClass="CopilotDraftManager"
		/>
	`,
};
