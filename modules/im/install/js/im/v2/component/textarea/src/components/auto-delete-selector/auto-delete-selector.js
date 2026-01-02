import { BIcon, Outline as OutlineIcons } from 'ui.icon-set.api.vue';

import { AutoDeletePopup, AutoDeleteHint } from 'im.v2.component.elements.auto-delete';
import { ChatService } from 'im.v2.provider.service.chat';
import { AutoDeleteManager } from 'im.v2.lib.auto-delete';
import { Color } from 'im.v2.const';

import type { ImModelChat } from 'im.v2.model';
import type { JsonObject } from 'main.core';

const ICON_SIZE = 24;

export const AutoDeleteSelector = {
	name: 'AutoDeleteSelector',
	components: { BIcon, AutoDeleteHint, AutoDeletePopup },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			showAutoDeleteMessagesPopup: false,
			showAutoDeleteMessagesHintPopup: false,
			hintConfig: {
				width: 316,
				offsetLeft: -106,
				offsetTop: 5,
				angle: {
					offset: 141,
				},
			},
		};
	},
	computed:
	{
		OutlineIcons: () => OutlineIcons,
		ICON_SIZE: () => ICON_SIZE,
		Color: () => Color,
		isAutoDeleteAllowed(): boolean
		{
			return AutoDeleteManager.isAutoDeleteAllowed(this.dialogId);
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		autoDeleteDelayInHours(): number
		{
			return this.$store.getters['chats/autoDelete/getDelay'](this.dialog.chatId);
		},
	},
	methods:
	{
		onIconClick(): void
		{
			if (!this.isAutoDeleteAllowed)
			{
				this.showAutoDeleteMessagesHintPopup = true;

				return;
			}
			this.showAutoDeleteMessagesPopup = true;
		},
		closePopup(): void
		{
			this.showAutoDeleteMessagesPopup = false;
		},
		hideAutoDeleteMessagesHintPopup(): void
		{
			this.showAutoDeleteMessagesHintPopup = false;
		},
		onAutoDeleteDelayChange(delay: number): void
		{
			this.getChatService().setMessagesAutoDeleteDelay(this.dialogId, delay);
			this.$emit('close');
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatService = new ChatService();
			}

			return this.chatService;
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div ref="autoDeleteIcon" class="bx-im-textarea__icon-container">
			<BIcon 
				:name="OutlineIcons.TIMER_DOT"
				:color="Color.accentBlue"
				:title="loc('IM_TEXTAREA_AUTO_DELETE_TITLE')"
				:size="ICON_SIZE"
				class="bx-im-textarea__icon"
				@click="onIconClick"
			/>
		</div>
		<AutoDeletePopup
			v-if="showAutoDeleteMessagesPopup"
			:autoDeleteDelay="autoDeleteDelayInHours"
			@close="closePopup"
			@autoDeleteDelayChange="onAutoDeleteDelayChange"
		/>
		<AutoDeleteHint
			v-if="showAutoDeleteMessagesHintPopup"
			:bindElement="$refs['autoDeleteIcon']"
			:config="hintConfig"
			@close="hideAutoDeleteMessagesHintPopup"
		/>
	`,
};
