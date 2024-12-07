import { Core } from 'im.v2.application.core';
import { Button as ChatButton, ButtonSize } from 'im.v2.component.elements';
import { Color } from 'im.v2.const';
import { ChatService } from 'im.v2.provider.service';

import type { CustomColorScheme } from 'im.v2.component.elements';
import type { ImModelChat } from 'im.v2.model';

const BUTTON_BACKGROUND_COLOR = 'rgba(0, 0, 0, 0.1)';
const BUTTON_HOVER_COLOR = 'rgba(0, 0, 0, 0.2)';
const BUTTON_TEXT_COLOR = '#fff';

// @vue/component
export const MutePanel = {
	components: { ChatButton },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data()
	{
		return {};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isMuted(): boolean
		{
			return this.dialog.muteList.includes(Core.getUserId());
		},
		buttonText(): string
		{
			const mutedCode = this.loc('IM_CONTENT_BLOCKED_TEXTAREA_ENABLE_NOTIFICATIONS');
			const unmutedCode = this.loc('IM_CONTENT_BLOCKED_TEXTAREA_DISABLE_NOTIFICATIONS');

			return this.isMuted ? mutedCode : unmutedCode;
		},
		buttonColorScheme(): CustomColorScheme
		{
			return {
				borderColor: Color.transparent,
				backgroundColor: BUTTON_BACKGROUND_COLOR,
				iconColor: BUTTON_TEXT_COLOR,
				textColor: BUTTON_TEXT_COLOR,
				hoverColor: BUTTON_HOVER_COLOR,
			};
		},
	},
	methods:
	{
		onButtonClick()
		{
			if (this.isMuted)
			{
				this.getChatService().unmuteChat(this.dialogId);

				return;
			}

			this.getChatService().muteChat(this.dialogId);
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
				:customColorScheme="buttonColorScheme"
				:text="buttonText"
				:isRounded="true"
				@click="onButtonClick"
			/>
		</div>
	`,
};
