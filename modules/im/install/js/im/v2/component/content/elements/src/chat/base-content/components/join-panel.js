import { Button as ChatButton, ButtonColor, ButtonSize } from 'im.v2.component.elements';
import { ChatService } from 'im.v2.provider.service';

// @vue/component
export const JoinPanel = {
	components: { ChatButton },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): {}
	{
		return {};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
	},
	methods:
	{
		onButtonClick()
		{
			this.getChatService().joinChat(this.dialogId);
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-content-chat__textarea_placeholder">
			<ChatButton
				:size="ButtonSize.XL"
				:color="ButtonColor.Primary"
				:text="loc('IM_CONTENT_BLOCKED_TEXTAREA_JOIN_CHAT')"
				:isRounded="true"
				@click="onButtonClick"
			/>
		</div>
	`,
};
