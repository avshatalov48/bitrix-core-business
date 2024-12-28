import { Extension } from 'main.core';

import { MessageComponent } from 'im.v2.const';

import type { ImModelMessage } from 'im.v2.model';

import '../../css/message-select-button.css';

const forwardMessageComponents = new Set([
	MessageComponent.default,
	MessageComponent.copilotMessage,
	MessageComponent.checkIn,
	MessageComponent.FeedbackFormMessage,
	MessageComponent.ImOpenLinesMessage,
	MessageComponent.ImOpenLinesForm,
]);

// @vue/component
export const MessageSelectButton = {
	name: 'MessageSelectButton',
	props:
	{
		message: {
			type: Object,
			required: true,
		},
	},
	computed:
	{
		messageItem(): ImModelMessage
		{
			return this.message;
		},
		selectedMessages(): Set<number>
		{
			return this.$store.getters['messages/select/getCollection'];
		},
		bulkActionMessageLimit(): number
		{
			const settings = Extension.getSettings('im.v2.component.message-list');

			return settings.get('multipleActionMessageLimit');
		},
		isMessageSelected(): boolean
		{
			return this.$store.getters['messages/select/isMessageSelected'](this.messageItem.id);
		},
		isSelectionLimitReached(): boolean
		{
			return this.selectedMessages.size === this.bulkActionMessageLimit && !this.isMessageSelected;
		},
		isRealMessage(): boolean
		{
			return this.$store.getters['messages/isRealMessage'](this.messageItem.id);
		},
		canSelectMessage(): boolean
		{
			if (this.messageItem.isDeleted || !this.isRealMessage)
			{
				return false;
			}

			return forwardMessageComponents.has(this.messageItem.componentId);
		},
	},
	methods:
	{
		onSelectMessage()
		{
			if (!this.canSelectMessage)
			{
				return;
			}

			if (this.isSelectionLimitReached)
			{
				this.showNotification(this.loc('IM_MESSAGE_LIST_MAX_LIMIT_SELECTED_MESSAGES'));

				return;
			}

			this.$store.dispatch('messages/select/toggle', this.messageItem.id);
		},
		showNotification(text: string)
		{
			BX.UI.Notification.Center.notify({ content: text });
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div @click="onSelectMessage" class="bx-im-message-list-select-button__container">
			<div
				v-if="canSelectMessage"
				class="bx-im-message-list-select-button__checkbox-circle"
				:class="{'--selected': isMessageSelected}"
			/>
		</div>
	`,
};
