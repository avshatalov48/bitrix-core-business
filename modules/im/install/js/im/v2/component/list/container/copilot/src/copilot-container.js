import { Messenger } from 'im.public';
import { CopilotList } from 'im.v2.component.list.items.copilot';
import { Layout } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { CopilotService } from 'im.v2.provider.service';

import type { JsonObject } from 'main.core';

// import { HeaderMenu } from './components/header-menu';
// import { CreateChatMenu } from './components/create-chat-menu/create-chat-menu';

import './css/copilot-container.css';

// @vue/component
export const CopilotListContainer = {
	name: 'CopilotListContainer',
	components: { CopilotList },
	emits: ['selectEntity'],
	data(): JsonObject
	{
		return {
			isCreatingChat: false,
		};
	},
	created()
	{
		Logger.warn('List: Copilot container created');
	},
	methods:
	{
		async onCreateChatClick()
		{
			this.isCreatingChat = true;

			const newDialogId = await this.getCopilotService().createChat()
				.catch(() => {
					this.isCreatingChat = false;
					this.showCreateChatError();
				});

			this.isCreatingChat = false;
			void Messenger.openCopilot(newDialogId);
		},
		onChatClick(dialogId)
		{
			this.$emit('selectEntity', { layoutName: Layout.copilot.name, entityId: dialogId });
		},
		showCreateChatError()
		{
			BX.UI.Notification.Center.notify({
				content: this.loc('IM_LIST_CONTAINER_COPILOT_CREATE_CHAT_ERROR'),
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
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-list-container-copilot__scope bx-im-list-container-copilot__container">
			<div class="bx-im-list-container-copilot__header_container">
				<div class="bx-im-list-container-copilot__header_title">CoPilot</div>
				<div
					class="bx-im-list-container-copilot__create-chat"
					:class="{'--loading': isCreatingChat}"
					@click="onCreateChatClick"
				>
					<div class="bx-im-list-container-copilot__create-chat_icon"></div>
				</div>
			</div>
			<div class="bx-im-list-container-copilot__elements_container">
				<div class="bx-im-list-container-copilot__elements">
					<CopilotList @chatClick="onChatClick" />
				</div>
			</div>
		</div>
	`,
};
