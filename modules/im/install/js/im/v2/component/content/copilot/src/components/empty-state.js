import { Messenger } from 'im.public';
import { Button as ChatButton, ButtonSize } from 'im.v2.component.elements';
import { Color } from 'im.v2.const';
import { CopilotService } from 'im.v2.provider.service';

import '../css/empty-state.css';

import type { JsonObject } from 'main.core';
import type { CustomColorScheme } from 'im.v2.component.elements';

const BUTTON_BACKGROUND_COLOR = '#fff';
const BUTTON_HOVER_COLOR = '#eee';
const BUTTON_TEXT_COLOR = 'rgba(82, 92, 105, 0.9)';

// @vue/component
export const EmptyState = {
	name: 'EmptyState',
	components: { ChatButton },
	data(): JsonObject
	{
		return {
			isLoading: false,
		};
	},
	computed:
	{
		ButtonSize: () => ButtonSize,
		preparedText(): string
		{
			return this.loc('IM_CONTENT_COPILOT_EMPTY_STATE_MESSAGE', {
				'#BR#': '\n',
			});
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
		async onButtonClick()
		{
			this.isLoading = true;

			const newDialogId = await this.getCopilotService().createChat()
				.catch(() => {
					this.isLoading = false;
					this.showCreateChatError();
				});

			this.isLoading = false;
			void Messenger.openCopilot(newDialogId);
		},
		showCreateChatError()
		{
			BX.UI.Notification.Center.notify({
				content: this.loc('IM_CONTENT_COPILOT_EMPTY_STATE_ERROR_CREATING_CHAT'),
			});
		},
		getCopilotService(): CopilotService
		{
			if (!this.copilotService)
			{
				this.copilotService = new CopilotService();
			}

			return this.copilotService;
		},
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-content-copilot-empty-state__container">
			<div class="bx-im-content-copilot-empty-state__content">
				<div class="bx-im-content-copilot-empty-state__icon"></div>
				<div class="bx-im-content-copilot-empty-state__text">{{ preparedText }}</div>
				<div class="bx-im-content-copilot-empty-state__button">
					<ChatButton
						class="--black-loader"
						:size="ButtonSize.XL"
						:customColorScheme="buttonColorScheme"
						:text="loc('IM_CONTENT_COPILOT_EMPTY_STATE_ASK_QUESTION')"
						:isRounded="true"
						:isLoading="isLoading"
						@click="onButtonClick"
					/>
				</div>
			</div>
		</div>
	`,
};
