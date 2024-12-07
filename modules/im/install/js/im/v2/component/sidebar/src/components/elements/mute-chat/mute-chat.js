import { hint } from 'ui.vue3.directives.hint';

import { ChatActionType, Layout } from 'im.v2.const';
import { Core } from 'im.v2.application.core';
import { ChatService } from 'im.v2.provider.service';
import { PermissionManager } from 'im.v2.lib.permission';
import { Toggle, ToggleSize } from 'im.v2.component.elements';

import type { ImModelChat } from 'im.v2.model';

import './css/mute-chat.css';

// @vue/component
export const MuteChat = {
	name: 'MuteChat',
	directives: { hint },
	components: { Toggle },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		ToggleSize: () => ToggleSize,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isGroupChat(): boolean
		{
			return this.dialogId.startsWith('chat');
		},
		canBeMuted(): boolean
		{
			return PermissionManager.getInstance().canPerformAction(ChatActionType.mute, this.dialogId);
		},
		isChatMuted(): boolean
		{
			const isMuted = this.dialog.muteList.find((element) => {
				return element === Core.getUserId();
			});

			return Boolean(isMuted);
		},
		hintMuteNotAvailable(): ?Object
		{
			if (this.canBeMuted)
			{
				return null;
			}

			return {
				text: this.$Bitrix.Loc.getMessage('IM_SIDEBAR_MUTE_NOT_AVAILABLE'),
				popupOptions: {
					angle: true,
					targetContainer: document.body,
					offsetLeft: 141,
					offsetTop: -10,
					bindOptions: {
						position: 'top',
					},
				},
			};
		},
		isCopilotLayout(): boolean
		{
			const { name: currentLayoutName } = this.$store.getters['application/getLayout'];

			return currentLayoutName === Layout.copilot.name;
		},
	},
	methods:
	{
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
		muteActionHandler()
		{
			if (!this.canBeMuted)
			{
				return;
			}

			if (this.isChatMuted)
			{
				this.getChatService().unmuteChat(this.dialogId);
			}
			else
			{
				this.getChatService().muteChat(this.dialogId);
			}
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div
			v-if="isGroupChat"
			class="bx-im-sidebar-mute-chat__container"
			:class="{'--not-active': !canBeMuted, '--copilot': isCopilotLayout}"
			v-hint="hintMuteNotAvailable"
		>
			<div class="bx-im-sidebar-mute-chat__title">
				<div class="bx-im-sidebar-mute-chat__title-text bx-im-sidebar-mute-chat__icon">
					{{ loc('IM_SIDEBAR_ENABLE_NOTIFICATION_TITLE_2') }}
				</div>
				<Toggle :size="ToggleSize.M" :isEnabled="!isChatMuted" @click="muteActionHandler" />
			</div>
		</div>
	`,
};
